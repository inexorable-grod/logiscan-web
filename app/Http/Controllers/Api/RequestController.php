<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientRequest;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    /**
     * Create a new client request (operario).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'request_type' => 'required|in:new_client,update_client,scan_reset',
            'route_id'     => 'nullable|uuid|exists:routes,id',
            'request_data' => 'required|array',
        ]);

        $clientRequest = ClientRequest::create([
            'request_type' => $request->request_type,
            'status'       => 'pending',
            'requested_by' => $request->user()->id,
            'center_id'    => $request->active_center_id,
            'route_id'     => $request->route_id,
            'request_data' => $request->request_data,
        ]);

        $typeLabels = [
            'new_client'    => 'Nuevo cliente',
            'update_client' => 'Cambio de datos',
            'scan_reset'    => 'Reinicio de escaneo',
        ];

        NotificationService::notifyApprovers($clientRequest->id, [
            'operator_name' => $request->user()->name,
            'type_label'    => $typeLabels[$request->request_type] ?? $request->request_type,
        ]);

        AuditService::log('CREATE_REQUEST', 'client_requests', $clientRequest->id, [
            'type' => $request->request_type,
        ]);

        return response()->json($clientRequest, 201);
    }

    /**
     * List the authenticated operario's requests.
     */
    public function index(Request $request): JsonResponse
    {
        $requests = ClientRequest::where('requested_by', $request->user()->id)
            ->with('resolvedBy:id,name,role')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($requests);
    }
}
