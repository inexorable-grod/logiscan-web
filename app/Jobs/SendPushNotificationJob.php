<?php

namespace App\Jobs;

use App\Models\DeviceToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int> */
    public array $backoff = [10, 30, 60];

    /**
     * @param  array<string>  $tokens        Expo push tokens.
     * @param  array          $notification  Payload: {title, body, data}.
     */
    public function __construct(
        protected array $tokens,
        protected array $notification,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        if (empty($this->tokens)) {
            return;
        }

        $messages = collect($this->tokens)->map(fn (string $token) => [
            'to'    => $token,
            'sound' => 'default',
            'title' => $this->notification['title'] ?? '',
            'body'  => $this->notification['body'] ?? '',
            'data'  => $this->notification['data'] ?? [],
        ])->all();

        $response = Http::withHeaders([
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('https://exp.host/--/api/v2/push/send', $messages);

        if ($response->failed()) {
            Log::error('Expo push API request failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return;
        }

        $results = $response->json('data') ?? [];

        foreach ($results as $index => $result) {
            $token = $this->tokens[$index] ?? null;
            if (!$token) {
                continue;
            }

            if (($result['status'] ?? 'error') === 'ok') {
                DeviceToken::where('token', $token)->update(['last_used' => now()]);
            }

            if (($result['status'] ?? '') === 'error') {
                $errorType = $result['details']['error'] ?? 'unknown';
                Log::warning('Expo push failed', ['token' => $token, 'error' => $errorType]);

                if (in_array($errorType, ['DeviceNotRegistered', 'InvalidCredentials'])) {
                    DeviceToken::where('token', $token)->update(['is_active' => false]);
                }
            }
        }
    }
}
