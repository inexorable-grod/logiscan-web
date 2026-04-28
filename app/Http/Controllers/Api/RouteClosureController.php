<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RouteClosure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RouteClosureController extends Controller
{
    /**
     * List route closures.
     */
    public function index(Request $request): JsonResponse
    {
        $query = RouteClosure::with(['route:id,route_number,description', 'closedBy:id,name']);

        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        if ($request->filled('date')) {
            $query->where('operation_date', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Operarios only see their own closures
        $user = auth()->user();
        if ($user->role === 'operario') {
            $query->where('closed_by', $user->id);
        }

        $closures = $query->orderByDesc('operation_date')->paginate(20);

        return response()->json($closures);
    }

    /**
     * Initiate a route closure.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'route_id'       => 'required|uuid|exists:routes,id',
            'operation_date' => 'required|date',
            'notes'          => 'nullable|string|max:1000',
        ]);

        // Check for existing closure on same date
        $existing = RouteClosure::where('route_id', $request->route_id)
            ->where('operation_date', $request->operation_date)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Ya existe un cierre para esta ruta en la fecha indicada.',
                'closure' => $existing,
            ], 422);
        }

        $closure = RouteClosure::create([
            'route_id'       => $request->route_id,
            'closed_by'      => auth()->id(),
            'operation_date' => $request->operation_date,
            'status'         => 'pending_documents',
            'notes'          => $request->notes,
            'closed_at'      => now(),
        ]);

        $closure->load(['route:id,route_number,description', 'closedBy:id,name']);

        return response()->json($closure, 201);
    }

    /**
     * Get closure details with documents, orders, and manifest items.
     */
    public function show(string $id): JsonResponse
    {
        $closure = RouteClosure::with([
            'route:id,route_number,description',
            'closedBy:id,name',
            'approvedBy:id,name',
            'documents',
            'orders.client:id,name,client_code',
        ])->findOrFail($id);

        return response()->json($closure);
    }

    /**
     * Approve a route closure.
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        $closure = RouteClosure::findOrFail($id);

        if (!in_array($closure->status, ['pending_approval', 'pending_review'])) {
            return response()->json([
                'message' => 'El cierre no esta en estado de aprobacion.',
            ], 422);
        }

        $closure->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Set as current closure on the route
        $closure->route()->update(['current_closure_id' => $closure->id]);

        return response()->json($closure);
    }

    /**
     * Reject a route closure.
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $request->validate(['notes' => 'required|string|max:1000']);

        $closure = RouteClosure::findOrFail($id);

        $closure->update([
            'status' => 'rejected',
            'notes'  => $request->notes,
        ]);

        return response()->json($closure);
    }
}
