<?php

namespace App\Services;

use App\Jobs\SendPushNotificationJob;
use App\Models\DeviceToken;
use App\Models\User;

class NotificationService
{
    /**
     * Send a push notification to a specific user.
     *
     * Retrieves all active device tokens and dispatches a queued job.
     *
     * @param  string  $userId        UUID of the user to notify.
     * @param  array   $notification  Payload: {title, body, data}.
     * @return void
     */
    public static function sendPush(string $userId, array $notification): void
    {
        $tokens = DeviceToken::where('user_id', $userId)
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return;
        }

        SendPushNotificationJob::dispatch($tokens, $notification);
    }

    /**
     * Notify all active approvers (supervisor + ti_admin) about a new request.
     *
     * @param  string  $requestId    UUID of the pending client_request.
     * @param  array   $requestData  Context: {operator_name, type_label}.
     * @return void
     */
    public static function notifyApprovers(string $requestId, array $requestData): void
    {
        User::whereIn('role', ['supervisor', 'ti_admin'])
            ->where('is_active', true)
            ->get()
            ->each(fn ($approver) => self::sendPush($approver->id, [
                'title' => 'Nueva solicitud pendiente',
                'body'  => "Operario {$requestData['operator_name']}: {$requestData['type_label']}",
                'data'  => ['request_id' => $requestId, 'screen' => 'solicitudes'],
            ]));
    }
}
