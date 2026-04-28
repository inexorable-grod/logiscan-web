<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Scan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScanSummaryController extends Controller
{
    /**
     * Get scans grouped by pedido_number for a route.
     * Returns order summary with scanned count, inferred expected, packages list, and missing packages.
     */
    public function byOrder(Request $request): JsonResponse
    {
        $request->validate([
            'route_id' => 'required|string',
            'date'     => 'nullable|date',
        ]);

        $query = Scan::where('route_id', $request->route_id)
            ->whereNotNull('pedido_number');

        if ($request->filled('date')) {
            $query->whereDate('scanned_at', $request->date);
        }

        $scans = $query->orderBy('pedido_number')
            ->orderBy('package_number')
            ->get(['id', 'barcode', 'pedido_number', 'package_number', 'scan_type', 'scanned_at', 'client_id']);

        // Group by pedido_number
        $grouped = $scans->groupBy('pedido_number');

        $orders = $grouped->map(function ($orderScans, $pedidoNumber) {
            // Get all package numbers as integers for analysis
            $packageNumbers = $orderScans
                ->pluck('package_number')
                ->filter()
                ->map(fn ($p) => (int) $p)
                ->sort()
                ->values();

            $maxPackage = $packageNumbers->max() ?? 0;
            $scannedCount = $packageNumbers->count();

            // Infer expected from max package number
            $expectedCount = max($maxPackage, $scannedCount);

            // Find missing packages (gaps between 1 and max)
            $scannedSet = $packageNumbers->flip();
            $missing = [];
            for ($i = 1; $i <= $maxPackage; $i++) {
                if (!$scannedSet->has($i)) {
                    $missing[] = str_pad($i, strlen($orderScans->first()->package_number ?? '3'), '0', STR_PAD_LEFT);
                }
            }

            // Sort package list for display
            $packages = $orderScans
                ->pluck('package_number')
                ->filter()
                ->sort()
                ->values()
                ->toArray();

            // Determine scan types present
            $scanTypes = $orderScans->pluck('scan_type')->unique()->values()->toArray();

            return [
                'pedido_number'  => $pedidoNumber,
                'scanned_count'  => $scannedCount,
                'expected_count' => $expectedCount,
                'packages'       => $packages,
                'missing'        => $missing,
                'scan_types'     => $scanTypes,
                'is_complete'    => empty($missing) && $scannedCount > 0,
            ];
        })->values();

        // Count unmatched scans (no pedido_number parsed)
        $unmatchedCount = Scan::where('route_id', $request->route_id)
            ->whereNull('pedido_number')
            ->when($request->filled('date'), fn ($q) => $q->whereDate('scanned_at', $request->date))
            ->count();

        return response()->json([
            'orders'          => $orders,
            'total_orders'    => $orders->count(),
            'total_scanned'   => $scans->count(),
            'complete_orders' => $orders->where('is_complete', true)->count(),
            'missing_orders'  => $orders->where('is_complete', false)->count(),
            'unmatched_scans' => $unmatchedCount,
        ]);
    }
}
