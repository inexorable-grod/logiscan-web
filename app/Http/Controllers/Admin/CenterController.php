<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CenterUser;
use App\Models\OperationCenter;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CenterController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            OperationCenter::withCount(['assignments' => fn ($q) => $q->where('is_active', true)])
                ->orderBy('name')
                ->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'code'    => 'required|string|max:20|unique:operation_centers,code',
            'address' => 'nullable|string',
        ]);

        $center = OperationCenter::create([
            ...$request->only('name', 'code', 'address'),
            'created_by' => $request->user()->id,
        ]);

        AuditService::log('CREATE_CENTER', 'operation_centers', $center->id);

        return response()->json($center, 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $center = OperationCenter::findOrFail($id);

        $request->validate([
            'name'      => 'sometimes|string|max:255',
            'code'      => 'sometimes|string|max:20|unique:operation_centers,code,' . $center->id,
            'address'   => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $center->update($request->only('name', 'code', 'address', 'is_active'));

        AuditService::log('UPDATE_CENTER', 'operation_centers', $center->id);

        return response()->json($center);
    }

    /**
     * Assign an operario to a center.
     */
    public function assign(Request $request, string $centerId): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
        ]);

        // Deactivate any previous assignment for this operario
        CenterUser::where('user_id', $request->user_id)->update(['is_active' => false]);

        CenterUser::updateOrCreate(
            ['center_id' => $centerId, 'user_id' => $request->user_id],
            ['is_active' => true, 'assigned_by' => $request->user()->id]
        );

        AuditService::log('ASSIGN_OPERARIO', 'center_users', null, [
            'center_id' => $centerId,
            'user_id'   => $request->user_id,
        ]);

        return response()->json(['message' => 'Operario asignado al centro.']);
    }
}
