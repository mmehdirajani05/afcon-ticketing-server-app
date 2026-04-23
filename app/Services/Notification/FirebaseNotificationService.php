<?php

namespace App\Services\Notification;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseNotificationService
{
    private ?Messaging $messaging;

    public function __construct()
    {
        // Resolve Messaging from container if Firebase is configured; otherwise null
        try {
            $this->messaging = app(Messaging::class);
        } catch (\Throwable) {
            $this->messaging = null;
        }
    }

    /**
     * Send a push notification to all active devices for a user.
     */
    public function send(User $user, string $title, string $body, array $data = []): void
    {
        $tokens = DeviceToken::where('user_id', $user->id)
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return;
        }

        if ($this->messaging) {
            $this->sendViaFirebaseSdk($tokens, $title, $body, $data);
        } else {
            $this->sendViaFcmHttpV1($tokens, $title, $body, $data);
        }
    }

    /**
     * Send via kreait/firebase-php SDK.
     */
    private function sendViaFirebaseSdk(array $tokens, string $title, string $body, array $data): void
    {
        $notification = Notification::create($title, $body);

        $stringData = array_map('strval', $data);

        foreach ($tokens as $token) {
            try {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification($notification)
                    ->withData($stringData);

                $this->messaging->send($message);
            } catch (\Kreait\Firebase\Exception\Messaging\NotFound $e) {
                // Token is stale — deactivate it
                DeviceToken::where('token', $token)->update(['is_active' => false]);
                Log::info('FirebaseNotification: stale token deactivated', ['token' => substr($token, 0, 20)]);
            } catch (\Throwable $e) {
                Log::error('FirebaseNotification: send error', [
                    'error' => $e->getMessage(),
                    'token' => substr($token, 0, 20),
                ]);
            }
        }
    }

    /**
     * Fallback: send directly via FCM HTTP v1 API (no SDK).
     */
    private function sendViaFcmHttpV1(array $tokens, string $title, string $body, array $data): void
    {
        $serverKey = config('services.firebase.server_key');

        if (! $serverKey) {
            Log::debug('FirebaseNotificationService: no server key configured, skipping push.', compact('title'));

            return;
        }

        $stringData = array_map('strval', $data);

        foreach (array_chunk($tokens, 500) as $chunk) {
            try {
                Http::withHeaders([
                    'Authorization' => 'key=' . $serverKey,
                    'Content-Type'  => 'application/json',
                ])->post('https://fcm.googleapis.com/fcm/send', [
                    'registration_ids' => $chunk,
                    'notification'     => ['title' => $title, 'body' => $body],
                    'data'             => $stringData,
                ]);
            } catch (\Throwable $e) {
                Log::error('FirebaseNotification HTTP fallback error', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Broadcast a topic notification (e.g. all AFCON2027 fans).
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): void
    {
        if (! $this->messaging) {
            return;
        }

        try {
            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification(Notification::create($title, $body))
                ->withData(array_map('strval', $data));

            $this->messaging->send($message);
        } catch (\Throwable $e) {
            Log::error('FirebaseNotification topic send error', ['topic' => $topic, 'error' => $e->getMessage()]);
        }
    }
}
