<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Route::with('center:id,name');

        if ($request->filled('center_id')) {
            $query->where('center_id', $request->center_id);
        }

        return response()->json($query->orderBy('route_number')->paginate(50));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'center_id'    => 'required|uuid|exists:operation_centers,id',
            'route_number' => 'required|string|max:20',
            'description'  => 'nullable|string|max:255',
        ]);

        $route = Route::create([
            ...$request->only('center_id', 'route_number', 'description'),
            'created_by' => $request->user()->id,
        ]);

        AuditService::log('CREATE_ROUTE', 'routes', $route->id);

        return response()->json($route, 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $route = Route::findOrFail($id);

        $request->validate([
            'route_number' => 'sometimes|string|max:20',
            'description'  => 'nullable|string|max:255',
            'is_active'    => 'sometimes|boolean',
        ]);

        $route->update($request->only('route_number', 'description', 'is_active'));

        AuditService::log('UPDATE_ROUTE', 'routes', $route->id);

        return response()->json($route);
    }
}
