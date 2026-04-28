<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ManifestItem;
use App\Models\Order;
use App\Models\RouteClosure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManifestItemController extends Controller
{
    /**
     * List manifest items for a closure.
     */
    public function index(string $closureId): JsonResponse
    {
        $items = ManifestItem::where('route_closure_id', $closureId)
            ->with('order:id,pedido_number,client_id')
            ->orderBy('row_index')
            ->get();

        return response()->json($items);
    }

    /**
     * Update a manifest item (OCR correction).
     */
    public function update(Request $request, string $closureId, string $itemId): JsonResponse
    {
        $request->validate([
            'pedido_number' => 'sometimes|string|max:50',
            'nombre_local'  => 'sometimes|string|max:255',
            'cubetas'       => 'sometimes|integer|min:0',
            'cajas_bolsa'   => 'sometimes|integer|min:0',
            'refrigerado'   => 'sometimes|integer|min:0',
            'controlado'    => 'sometimes|integer|min:0',
            'forma_pago'    => 'nullable|string|max:50',
            'observaciones' => 'nullable|string',
        ]);

        $item = ManifestItem::where('route_closure_id', $closureId)
            ->findOrFail($itemId);

        $item->update(array_merge(
            $request->only([
                'pedido_number', 'nombre_local', 'cubetas',
                'cajas_bolsa', 'refrigerado', 'controlado',
                'forma_pago', 'observaciones',
            ]),
            ['is_manually_corrected' => true]
        ));

        // Also update the linked order if exists
        if ($item->order_id) {
            $item->order->update([
                'pedido_number'        => $item->pedido_number,
                'client_name_ocr'      => $item->nombre_local,
                'expected_cubetas'     => $item->cubetas,
                'expected_cajas_bolsa' => $item->cajas_bolsa,
                'expected_refrigerado' => $item->refrigerado,
                'expected_controlado'  => $item->controlado,
                'expected_total'       => $item->cubetas + $item->cajas_bolsa + $item->refrigerado + $item->controlado,
                'is_verified'          => true,
            ]);
        }

        return response()->json($item);
    }

    /**
     * Manually add a manifest item.
     */
    public function store(Request $request, string $closureId): JsonResponse
    {
        $request->validate([
            'pedido_number' => 'required|string|max:50',
            'nombre_local'  => 'required|string|max:255',
            'cubetas'       => 'required|integer|min:0',
            'cajas_bolsa'   => 'required|integer|min:0',
            'refrigerado'   => 'required|integer|min:0',
            'controlado'    => 'required|integer|min:0',
        ]);

        $closure = RouteClosure::findOrFail($closureId);

        // Get the first document (or create a placeholder) for the closure_document_id FK
        $document = $closure->documents()->first();
        if (!$document) {
            return response()->json([
                'message' => 'Debe subir al menos un documento antes de agregar items manualmente.',
            ], 422);
        }

        // Determine next row_index
        $maxIndex = ManifestItem::where('route_closure_id', $closureId)->max('row_index') ?? -1;

        // Create the order first
        $total = $request->cubetas + $request->cajas_bolsa + $request->refrigerado + $request->controlado;
        $order = Order::create([
            'route_id'             => $closure->route_id,
            'route_closure_id'     => $closure->id,
            'pedido_number'        => $request->pedido_number,
            'client_name_ocr'      => $request->nombre_local,
            'expected_cubetas'     => $request->cubetas,
            'expected_cajas_bolsa' => $request->cajas_bolsa,
            'expected_refrigerado' => $request->refrigerado,
            'expected_controlado'  => $request->controlado,
            'expected_total'       => $total,
            'source'               => 'manual',
            'is_verified'          => true,
        ]);

        $item = ManifestItem::create([
            'closure_document_id'  => $document->id,
            'route_closure_id'     => $closureId,
            'order_id'             => $order->id,
            'row_index'            => $maxIndex + 1,
            'pedido_number'        => $request->pedido_number,
            'nombre_local'         => $request->nombre_local,
            'cubetas'              => $request->cubetas,
            'cajas_bolsa'          => $request->cajas_bolsa,
            'refrigerado'          => $request->refrigerado,
            'controlado'           => $request->controlado,
            'confidence_score'     => 1.00,
            'is_manually_corrected' => true,
        ]);

        return response()->json($item, 201);
    }

    /**
     * Delete a manifest item.
     */
    public function destroy(string $closureId, string $itemId): JsonResponse
    {
        $item = ManifestItem::where('route_closure_id', $closureId)
            ->findOrFail($itemId);

        // Also delete the linked order if it has no other manifest items
        if ($item->order_id) {
            $otherItems = ManifestItem::where('order_id', $item->order_id)
                ->where('id', '!=', $item->id)
                ->exists();

            if (!$otherItems) {
                $item->order?->delete();
            }
        }

        $item->delete();

        return response()->json(null, 204);
    }
}
