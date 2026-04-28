<?php

namespace App\Services;

use App\Models\Order;
use App\Models\RouteClosure;
use App\Models\Scan;

class MachComparisonService
{
    /**
     * Generate a MACH comparison for a route closure.
     *
     * Compares expected package counts (from orders/manifest) against
     * actual scanned packages, matching by client assignment.
     */
    public function compare(RouteClosure $closure): array
    {
        $orders = $closure->orders()
            ->with('client:id,name,client_code')
            ->get();

        // Get all scans for this route on the operation date
        $scans = Scan::where('route_id', $closure->route_id)
            ->whereDate('scanned_at', $closure->operation_date)
            ->get();

        // Group scans by client_id and scan_type
        $scansByClient = $scans->groupBy('client_id');

        $orderResults = [];
        $matchedScanIds = collect();

        foreach ($orders as $order) {
            $clientScans = $order->client_id
                ? ($scansByClient->get($order->client_id) ?? collect())
                : collect();

            $matchedScanIds = $matchedScanIds->merge($clientScans->pluck('id'));

            $scannedByType = $clientScans->groupBy('scan_type')->map->count();

            $scanned = [
                'cubetas'     => $scannedByType->get('cubeta', 0),
                'cajas_bolsa' => $scannedByType->get('bulto', 0),
                'refrigerado' => $scannedByType->get('refrigerado', 0),
                'controlado'  => $scannedByType->get('controlado', 0),
            ];
            $scanned['total'] = array_sum($scanned);

            $expected = [
                'cubetas'     => $order->expected_cubetas,
                'cajas_bolsa' => $order->expected_cajas_bolsa,
                'refrigerado' => $order->expected_refrigerado,
                'controlado'  => $order->expected_controlado,
                'total'       => $order->expected_total,
            ];

            // Determine status
            if ($scanned['total'] === 0 && $expected['total'] > 0) {
                $status = 'pending';
            } elseif ($scanned['total'] >= $expected['total'] && $expected['total'] > 0) {
                $status = $scanned['total'] > $expected['total'] ? 'over' : 'complete';
            } elseif ($expected['total'] === 0) {
                $status = 'complete';
            } else {
                $status = 'incomplete';
            }

            $orderResults[] = [
                'order_id'      => $order->id,
                'pedido_number' => $order->pedido_number,
                'client_name'   => $order->client?->name ?? $order->client_name_ocr,
                'client_code'   => $order->client?->client_code,
                'expected'      => $expected,
                'scanned'       => $scanned,
                'status'        => $status,
            ];
        }

        // Collect unmatched scans (not linked to any order's client)
        $unmatchedScans = $scans
            ->whereNotIn('id', $matchedScanIds->toArray())
            ->map(fn(Scan $s) => [
                'id'        => $s->id,
                'barcode'   => $s->barcode,
                'scan_type' => $s->scan_type,
                'client_id' => $s->client_id,
                'scanned_at' => $s->scanned_at->toIso8601String(),
            ])
            ->values();

        // Summary
        $totalOrders = count($orderResults);
        $complete = collect($orderResults)->where('status', 'complete')->count();
        $incomplete = collect($orderResults)->where('status', 'incomplete')->count();
        $pending = collect($orderResults)->where('status', 'pending')->count();
        $over = collect($orderResults)->where('status', 'over')->count();

        return [
            'summary' => [
                'total_orders' => $totalOrders,
                'complete'     => $complete,
                'incomplete'   => $incomplete,
                'pending'      => $pending,
                'over'         => $over,
            ],
            'orders'          => $orderResults,
            'unmatched_scans' => $unmatchedScans->toArray(),
        ];
    }
}
