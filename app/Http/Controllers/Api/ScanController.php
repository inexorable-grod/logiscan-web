<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\ScanValidationService;
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
     */
    public function batch(Request $request): JsonResponse
    {
        $request->validate([
            'scans'              => 'required|array|max:50',
            'scans.*.localId'    => 'required|string',
            'scans.*.barcode'    => 'required|string',
            'scans.*.scanType'   => 'required|string',
            'scans.*.scannedAt'  => 'required|string',
        ]);

        $results = [];

        foreach ($request->scans as $scan) {
            $validation = $this->scanValidator->validate($scan['barcode']);

            $results[] = [
                'localId' => $scan['localId'],
                'status'  => $validation['valid'] ? 'synced' : 'failed',
                'error'   => $validation['error'],
            ];
        }

        return response()->json(['results' => $results]);
    }
}
