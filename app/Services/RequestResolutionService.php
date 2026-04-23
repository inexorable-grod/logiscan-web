<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RequestResolutionService
{
    /**
     * Resolve a pending client request (approve or reject).
     *
     * Uses lockForUpdate() to prevent double-resolution race conditions.
     * Only resolves requests with status = 'pending'.
     * Records resolution in audit_log with the resolver's role.
     *
     * @param  string       $requestId  UUID of the client_request.
     * @param  string       $userId     UUID of the resolving user (supervisor or ti_admin).
     * @param  string       $status     'approved' or 'rejected'.
     * @param  string|null  $comment    Admin comment (required if rejecting).
     * @return array{success: bool, message?: string}
     *
     * @throws \Illuminate\Database\QueryException
     */
    public function resolve(
        string $requestId,
        string $userId,
        string $status,
        ?string $comment = null
    ): array {
        return DB::transaction(function () use ($requestId, $userId, $status, $comment) {
            $request = ClientRequest::where('id', $requestId)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$request) {
                $resolved = ClientRequest::with('resolvedBy')->find($requestId);
                $resolverName = $resolved?->resolvedBy?->name ?? 'otro usuario';

                return [
                    'success' => false,
                    'message' => "Ya {$resolved?->status} por {$resolverName}.",
                ];
            }

            $request->update([
                'status'           => $status,
                'admin_comment'    => $comment,
                'resolved_by'      => $userId,
                'resolved_by_role' => auth()->user()->role,
                'resolved_at'      => now(),
            ]);

            // If approved new_client, auto-create the client
            if ($status === 'approved' && $request->request_type === 'new_client') {
                $data = $request->request_data;
                Client::create([
                    'route_id'    => $request->route_id,
                    'client_code' => $data['client_code'] ?? '',
                    'name'        => $data['name'] ?? '',
                    'address'     => $data['address'] ?? null,
                    'phone'       => $data['phone'] ?? null,
                    'created_by'  => $userId,
                ]);
                Cache::forget("clients:route:{$request->route_id}");
            }

            // Notify the requesting operario
            NotificationService::sendPush($request->requested_by, [
                'title' => $status === 'approved' ? 'Solicitud aprobada' : 'Solicitud rechazada',
                'body'  => 'Resuelta por ' . auth()->user()->name,
                'data'  => ['request_id' => $requestId, 'status' => $status],
            ]);

            // Audit trail
            AuditService::log(
                strtoupper($status) . '_REQUEST',
                'client_requests',
                $requestId,
                ['comment' => $comment, 'type' => $request->request_type]
            );

            return ['success' => true];
        });
    }
}
