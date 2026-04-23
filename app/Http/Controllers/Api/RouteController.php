<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    /**
     * List active routes for the operario's assigned center.
     */
    public function index(Request $request): JsonResponse
    {
        $routes = Route::where('center_id', $request->active_center_id)
            ->where('is_active', true)
            ->select('id', 'center_id', 'route_number', 'description')
            ->orderBy('route_number')
            ->get();

        return response()->json($routes);
    }
}
