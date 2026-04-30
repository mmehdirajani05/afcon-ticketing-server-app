<?php

namespace App\Services\Fan;

use App\Constants\AppConstant;
use App\Models\ImmigrationLog;
use App\Models\User;
use App\Models\UserImmigrationDetail;
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
     * Entry point — decides real-time vs. delayed based on .env config.
     *
     * IMMIGRATION_SKIP=true (current dev setting):
     *   Returns 'verified' instantly. Fan ID is issued on the spot.
     *   No API call is made. Use this until immigration API credentials are provided.
     *
     * IMMIGRATION_MODE=realtime + IMMIGRATION_API_URL set:
     *   Calls the live immigration API and processes the response immediately.
     *
     * IMMIGRATION_MODE=delayed (or API not configured):
     *   Stores a log record and returns 'pending'. Admin or a scheduled Artisan
     *   command processes pending records within 24 h.
     *
     * Returns: ['status' => 'verified' | 'pending' | 'rejected', 'message' => '...']
     *
     * TODO: Set IMMIGRATION_SKIP=false in .env and configure IMMIGRATION_API_URL /
     *       IMMIGRATION_API_KEY once the immigration department provides their endpoint.
     */
    public function verify(User $user, UserImmigrationDetail $detail): array
    {
        if (config('services.immigration.skip', false)) {
            Log::info('ImmigrationVerificationService: SKIP mode — auto-verifying', [
                'user_id'   => $user->id,
                'detail_id' => $detail->id,
            ]);

            return ['status' => 'verified', 'message' => 'Dev mode: immigration check skipped.'];
        }

        if ($this->mode === AppConstant::IMMIGRATION_MODE_REALTIME && $this->apiUrl) {
            return $this->verifyRealtime($user, $detail);
        }

        return $this->queueDelayed($user, $detail);
    }

    /**
     * REAL-TIME MODE: call the immigration department API synchronously.
     *
     * TODO: Verify the exact endpoint, authentication scheme, request/response format
     *       with the immigration department before enabling this path in production.
     */
    private function verifyRealtime(User $user, UserImmigrationDetail $detail): array
    {
        $payload = $this->buildPayload($detail);

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

            // Unexpected API response — fall back to delayed mode
            Log::warning('Immigration real-time call failed, falling back to delayed', [
                'user_id'     => $user->id,
                'http_status' => $response->status(),
            ]);
            $log->update(['status' => 'pending', 'notes' => 'Fell back to delayed after API error.']);

            return $this->queueDelayed($user, $detail, $log);

        } catch (\Throwable $e) {
            Log::error('Immigration API exception', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
            $log->update(['status' => 'pending', 'notes' => 'Exception: ' . $e->getMessage()]);

            return $this->queueDelayed($user, $detail, $log);
        }
    }

    /**
     * DELAYED MODE: store the verification request and return pending.
     * Admin panel or a scheduled Artisan command processes the queue within 24 h.
     *
     * TODO: Build an Artisan command (e.g. app:process-pending-fan-ids) that loops
     *       pending UserImmigrationDetail rows, calls processPending(), and marks results.
     */
    private function queueDelayed(User $user, UserImmigrationDetail $detail, ?ImmigrationLog $existingLog = null): array
    {
        if (! $existingLog) {
            ImmigrationLog::create([
                'user_id'         => $user->id,
                'mode'            => AppConstant::IMMIGRATION_MODE_DELAYED,
                'request_payload' => $this->buildPayload($detail),
                'status'          => 'pending',
            ]);
        }

        return [
            'status'  => 'pending',
            'message' => 'Your Fan ID application is under review. You will be notified within 24 hours.',
        ];
    }

    /**
     * Re-trigger verification for a specific pending detail record.
     * Called by admin panel or Artisan command to process delayed queue.
     */
    public function processPending(User $user, UserImmigrationDetail $detail): array
    {
        if (! $detail->isPending()) {
            return ['status' => 'not_applicable', 'message' => 'This application is not pending.'];
        }

        if ($this->apiUrl) {
            return $this->verifyRealtime($user, $detail);
        }

        return ['status' => 'pending', 'message' => 'Immigration API not configured.'];
    }

    /**
     * Build the request payload for the immigration department API.
     *
     * TODO: Align field names and values with the immigration API specification
     *       once the department shares their integration documentation.
     */
    private function buildPayload(UserImmigrationDetail $detail): array
    {
        return [
            'user_reference'        => $detail->user_id,
            'full_name'             => $detail->full_name,
            'gender'                => $detail->gender,
            'date_of_birth'         => $detail->date_of_birth?->format('Y-m-d'),
            'nationality'           => $detail->nationality,
            'identity_type'         => $detail->identity_type,
            'identity_number'       => $detail->identity_number,
            'identity_expiry_date'  => $detail->identity_expiry_date?->format('Y-m-d'),
            'submitted_at'          => $detail->submitted_at?->toIso8601String(),
        ];
    }
}
