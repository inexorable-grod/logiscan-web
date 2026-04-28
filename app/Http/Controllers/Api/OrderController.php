<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Scan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * List orders for a route, optionally filtered by date.
     */
    public function index(Request $request, string $routeId): JsonResponse
    {
        $query = Order::where('route_id', $routeId)
            ->with('client:id,name,client_code');

        if ($request->filled('date')) {
            $query->whereHas('closure', fn($q) => $q->where('operation_date', $request->date));
        }

        $orders = $query->get();

        // Attach scan counts per order
        $orders->each(function ($order) {
            $scanCounts = Scan::where('order_id', $order->id)
                ->selectRaw('scan_type, count(*) as total')
                ->groupBy('scan_type')
                ->pluck('total', 'scan_type');

            $order->scanned = [
                'cubetas'     => $scanCounts->get('cubeta', 0),
                'cajas_bolsa' => $scanCounts->get('bulto', 0),
                'refrigerado' => $scanCounts->get('refrigerado', 0),
                'controlado'  => $scanCounts->get('controlado', 0),
                'total'       => $scanCounts->sum(),
            ];
        });

        return response()->json($orders);
    }

    /**
     * Generate orders from verified manifest items.
     */
    public function generateFromManifest(Request $request, string $closureId): JsonResponse
    {
        $closure = \App\Models\RouteClosure::findOrFail($closureId);

        $items = $closure->manifestItems()
            ->whereNull('order_id')
            ->get();

        $created = 0;
        foreach ($items as $item) {
            $order = Order::create([
                'route_id'             => $closure->route_id,
                'route_closure_id'     => $closure->id,
                'pedido_number'        => $item->pedido_number,
                'client_name_ocr'      => $item->nombre_local,
                'expected_cubetas'     => $item->cubetas,
                'expected_cajas_bolsa' => $item->cajas_bolsa,
                'expected_refrigerado' => $item->refrigerado,
                'expected_controlado'  => $item->controlado,
                'expected_total'       => $item->cubetas + $item->cajas_bolsa + $item->refrigerado + $item->controlado,
                'source'               => $item->is_manually_corrected ? 'manual' : 'ocr',
                'is_verified'          => true,
            ]);

            $item->update(['order_id' => $order->id]);
            $created++;
        }

        return response()->json([
            'orders_created' => $created,
        ]);
    }
}
