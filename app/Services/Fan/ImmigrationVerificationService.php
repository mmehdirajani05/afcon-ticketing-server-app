<?php

namespace App\Services\Fan;

use App\Constants\AppConstant;
use App\Models\ImmigrationLog;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImmigrationVerificationService
{
    private string $mode;
    private string $apiUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->mode   = config('services.immigration.mode', AppConstant::IMMIGRATION_MODE_DELAYED);
        $this->apiUrl = config('services.immigration.url', '');
        $this->apiKey = config('services.immigration.key', '');
    }

    /**
     * Entry point — decides real-time vs. delayed based on config.
     *
     * When IMMIGRATION_SKIP=true (dev mode), bypasses all checks and
     * returns 'verified' immediately so Fan IDs are issued instantly.
     *
     * Returns:
     *   ['status' => 'verified' | 'pending' | 'rejected', 'message' => '...']
     */
    public function verify(User $user): array
    {
        if (config('services.immigration.skip', false)) {
            Log::info('ImmigrationVerificationService: SKIP mode — auto-verifying', ['user_id' => $user->id]);

            return ['status' => 'verified', 'message' => 'Dev mode: immigration skipped.'];
        }

        if ($this->mode === AppConstant::IMMIGRATION_MODE_REALTIME && $this->apiUrl) {
            return $this->verifyRealtime($user);
        }

        return $this->queueDelayed($user);
    }

    /**
     * REAL-TIME MODE: call the immigration API and process response immediately.
     */
    private function verifyRealtime(User $user): array
    {
        $payload = $this->buildPayload($user);

        $log = ImmigrationLog::create([
            'user_id'         => $user->id,
            'mode'            => AppConstant::IMMIGRATION_MODE_REALTIME,
            'request_payload' => $payload,
            'status'          => 'pending',
        ]);

        try {
            $response     = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept'        => 'application/json',
            ])->timeout(15)->post($this->apiUrl . '/verify', $payload);

            $responseData = $response->json();

            if ($response->successful() && ($responseData['verified'] ?? false)) {
                $log->update(['response_payload' => $responseData, 'status' => 'verified']);

                return ['status' => 'verified', 'message' => 'Identity verified successfully.'];
            }

            if ($response->status() === 422 || ($responseData['rejected'] ?? false)) {
                $log->update(['response_payload' => $responseData, 'status' => 'rejected']);

                return [
                    'status'  => 'rejected',
                    'message' => $responseData['reason'] ?? 'Identity could not be verified.',
                ];
            }

            // API error — fall back to delayed mode
            Log::warning('Immigration real-time call failed; falling back to delayed', [
                'user_id' => $user->id, 'http_status' => $response->status(),
            ]);
            $log->update(['status' => 'pending', 'notes' => 'Fell back to delayed after API error.']);

            return $this->queueDelayed($user, $log);

        } catch (\Throwable $e) {
            Log::error('Immigration API exception', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            $log->update(['status' => 'pending', 'notes' => 'Exception: ' . $e->getMessage()]);

            return $this->queueDelayed($user, $log);
        }
    }

    /**
     * DELAYED MODE: persist the request and return pending.
     * Admin or scheduled artisan command processes within 24 h.
     */
    private function queueDelayed(User $user, ?ImmigrationLog $existingLog = null): array
    {
        if (! $existingLog) {
            ImmigrationLog::create([
                'user_id'         => $user->id,
                'mode'            => AppConstant::IMMIGRATION_MODE_DELAYED,
                'request_payload' => $this->buildPayload($user),
                'status'          => 'pending',
            ]);
        }

        return [
            'status'  => 'pending',
            'message' => 'Your Fan ID application is under review. You will be notified within 24 hours.',
        ];
    }

    /**
     * Manually re-trigger verification for a pending user (admin / artisan command).
     */
    public function processPending(User $user): array
    {
        if ($user->fan_id_status !== AppConstant::FAN_ID_STATUS_PENDING) {
            return ['status' => 'not_applicable', 'message' => 'User has no pending Fan ID application.'];
        }

        if ($this->apiUrl) {
            return $this->verifyRealtime($user);
        }

        return ['status' => 'pending', 'message' => 'Immigration API not configured.'];
    }

    private function buildPayload(User $user): array
    {
        return [
            'user_reference'  => $user->id,
            'full_name'       => $user->fan_id_full_name,
            'identity_type'   => $user->fan_id_identity_type,
            'identity_number' => $user->fan_id_identity_number,
            'nationality'     => $user->fan_id_nationality,
            'date_of_birth'   => $user->fan_id_date_of_birth?->format('Y-m-d'),
            'submitted_at'    => now()->toIso8601String(),
        ];
    }
}
