<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\ClientRequest;
use App\Models\OperationCenter;
use App\Models\Route;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard statistics for the supervisor's active center.
     */
    public function index(Request $request): JsonResponse
    {
        $centerId = session('active_center_id');

        if (!$centerId) {
            return response()->json(['error' => 'Centro no seleccionado.'], 403);
        }

        $center = OperationCenter::find($centerId);

        $pendingRequests = ClientRequest::where('center_id', $centerId)
            ->where('status', 'pending')
            ->count();

        $resolvedToday = ClientRequest::where('center_id', $centerId)
            ->whereIn('status', ['approved', 'rejected'])
            ->whereDate('resolved_at', today())
            ->count();

        $routesCount = Route::where('center_id', $centerId)
            ->where('is_active', true)
            ->count();

        return response()->json([
            'center'            => $center->only('id', 'name', 'code'),
            'pending_requests'  => $pendingRequests,
            'resolved_today'    => $resolvedToday,
            'active_routes'     => $routesCount,
        ]);
    }
}
