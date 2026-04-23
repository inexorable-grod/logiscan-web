<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\OperationCenter;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CenterSelectorController extends Controller
{
    /**
     * List all active centers for supervisor selection.
     */
    public function index(): JsonResponse
    {
        return response()->json(
            OperationCenter::where('is_active', true)
                ->select('id', 'name', 'code')
                ->orderBy('name')
                ->get()
        );
    }

    /**
     * Select the active center for the current session.
     */
    public function select(Request $request): JsonResponse
    {
        $request->validate(['center_id' => 'required|uuid|exists:operation_centers,id']);

        session(['active_center_id' => $request->center_id]);

        AuditService::log('SELECT_CENTER', 'operation_centers', $request->center_id);

        $center = OperationCenter::find($request->center_id);

        return response()->json([
            'message' => "Centro activo: {$center->name}",
            'center'  => $center->only('id', 'name', 'code'),
        ]);
    }
}
