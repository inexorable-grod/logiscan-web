<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditService
{
    /**
     * Create an audit log entry.
     *
     * @param  string       $action    The action performed (e.g. 'LOGIN', 'APPROVED_REQUEST')
     * @param  string       $entity    The entity type affected (e.g. 'users', 'client_requests')
     * @param  string|null  $entityId  The ID of the affected entity
     * @param  array|null   $payload   Additional contextual data
     * @return AuditLog
     */
    public static function log(
        string $action,
        string $entity,
        ?string $entityId = null,
        ?array $payload = null,
        ?string $userId = null
    ): AuditLog {
        return AuditLog::create([
            'user_id'    => $userId ?? auth()->id(),
            'action'     => $action,
            'entity'     => $entity,
            'entity_id'  => $entityId,
            'payload'    => $payload,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
