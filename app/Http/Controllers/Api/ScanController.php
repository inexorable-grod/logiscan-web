<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Scan;
use App\Services\AuditService;
use App\Services\ScanValidationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function __construct(
        protected ScanValidationService $scanValidator,
    ) {}

    /**
     * Validate and classify a single barcode scan.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate(['barcode' => 'required|string']);

        $result = $this->scanValidator->validate($request->barcode);

        if (!$result['valid']) {
            AuditService::log('INVALID_SCAN', 'scans', null, [
                'barcode' => $request->barcode,
                'error'   => $result['error'],
            ]);
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    /**
     * Process a batch of offline scans (sync endpoint).
     * Persists each valid scan to the database.
     */
    public function batch(Request $request): JsonResponse
    {
        $request->validate([
            'scans'              => 'required|array|max:50',
            'scans.*.localId'    => 'required|string',
            'scans.*.barcode'    => 'required|string',
            'scans.*.scanType'   => 'required|string',
            'scans.*.scannedAt'  => 'required|string',
            'scans.*.routeId'    => 'nullable|string',
            'scans.*.clientId'   => 'nullable|string',
        ]);

        $results = [];
        $userId = auth()->id();

        foreach ($request->scans as $scan) {
            $validation = $this->scanValidator->validate($scan['barcode']);

            if (!$validation['valid']) {
                $results[] = [
                    'localId' => $scan['localId'],
                    'status'  => 'failed',
                    'error'   => $validation['error'],
                ];
                continue;
            }

            // Duplicate check: same barcode on the same route
            $routeId = $scan['routeId'] ?? null;
            if ($routeId && Scan::where('barcode', $scan['barcode'])->where('route_id', $routeId)->exists()) {
                $results[] = [
                    'localId' => $scan['localId'],
                    'status'  => 'duplicate',
                    'error'   => 'Este codigo ya fue escaneado en esta ruta.',
                ];
                continue;
            }

            Scan::create([
                'user_id'    => $userId,
                'route_id'   => $routeId,
                'client_id'  => $scan['clientId'] ?? null,
                'barcode'    => $scan['barcode'],
                'scan_type'  => $scan['scanType'],
                'local_id'   => $scan['localId'],
                'scanned_at' => Carbon::parse($scan['scannedAt']),
            ]);

            $results[] = [
                'localId' => $scan['localId'],
                'status'  => 'synced',
                'error'   => null,
            ];
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Get scan history for the authenticated operator.
     */
    public function history(Request $request): JsonResponse
    {
        $query = Scan::where('user_id', auth()->id())
            ->with('client:id,client_code,name');

        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        if ($request->filled('date')) {
            $date = Carbon::parse($request->date);
            $query->whereDate('scanned_at', $date);
        }

        $scans = $query->orderByDesc('scanned_at')->paginate(50);

        return response()->json($scans);
    }
}
