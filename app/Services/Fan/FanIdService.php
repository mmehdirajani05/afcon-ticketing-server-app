<?php

namespace App\Services\Fan;

use App\Constants\AppConstant;
use App\Models\User;
use App\Services\Notification\FirebaseNotificationService;
use Illuminate\Validation\ValidationException;

class FanIdService
{
    public function __construct(
        private ImmigrationVerificationService $immigrationService,
        private FirebaseNotificationService    $notificationService,
    ) {}

    /**
     * Submit a Fan ID application for the given user.
     * Can only be done once — blocked if a pending or verified application exists.
     */
    public function apply(User $user, array $data): User
    {
        if ($user->fan_id_status === AppConstant::FAN_ID_STATUS_VERIFIED) {
            throw ValidationException::withMessages([
                'fan_id' => ['You already have a verified Fan ID: ' . $user->fan_id],
            ]);
        }

        if ($user->fan_id_status === AppConstant::FAN_ID_STATUS_PENDING) {
            throw ValidationException::withMessages([
                'fan_id' => ['Your Fan ID application is currently under review.'],
            ]);
        }

        // Save identity data and set status to pending
        $user->update([
            'fan_id_full_name'       => $data['full_name'],
            'fan_id_identity_type'   => $data['identity_type'],
            'fan_id_identity_number' => $data['identity_number'],
            'fan_id_nationality'     => $data['nationality'] ?? null,
            'fan_id_date_of_birth'   => $data['date_of_birth'] ?? null,
            'fan_id_status'          => AppConstant::FAN_ID_STATUS_PENDING,
            'fan_id_rejection_reason'=> null,
        ]);

        $user->refresh();

        // Attempt verification — may resolve immediately (dev skip / real-time) or stay pending (delayed)
        $result = $this->immigrationService->verify($user);

        if ($result['status'] === AppConstant::FAN_ID_STATUS_VERIFIED) {
            return $this->markVerified($user);
        }

        if ($result['status'] === AppConstant::FAN_ID_STATUS_REJECTED) {
            return $this->markRejected($user, $result['message'] ?? '');
        }

        return $user->fresh();
    }

    /**
     * Called when immigration verification succeeds (real-time response or admin approval).
     */
    public function markVerified(User $user): User
    {
        $fanId = $this->generateFanId($user);

        $user->update([
            'fan_id'          => $fanId,
            'fan_id_status'   => AppConstant::FAN_ID_STATUS_VERIFIED,
            'fan_id_verified_at' => now(),
            'fan_id_rejection_reason' => null,
        ]);

        $this->notificationService->send(
            $user,
            'Fan ID Verified ✅',
            'Your Fan ID has been approved: ' . $fanId,
            ['type' => 'fan_id_verified', 'fan_id' => $fanId]
        );

        return $user->fresh();
    }

    /**
     * Called when immigration verification is rejected (real-time or admin decision).
     */
    public function markRejected(User $user, string $reason = ''): User
    {
        $user->update([
            'fan_id_status'           => AppConstant::FAN_ID_STATUS_REJECTED,
            'fan_id_rejection_reason' => $reason ?: 'Identity could not be verified.',
        ]);

        $this->notificationService->send(
            $user,
            'Fan ID Not Approved',
            'Your Fan ID application was not approved. ' . ($reason ?: 'Please re-apply with correct documents.'),
            ['type' => 'fan_id_rejected']
        );

        return $user->fresh();
    }

    /**
     * Generate a unique Fan ID string: AFCON27-TZ-{USERID5}-{RANDOM6}-{CHECK1}
     * Example: AFCON27-TZ-00042-K9PZ3M-4
     */
    public function generateFanId(User $user): string
    {
        for ($i = 0; $i < 10; $i++) {
            $userId  = str_pad((string) $user->id, 5, '0', STR_PAD_LEFT);
            $random6 = $this->randomAlphanumeric(6);
            $base    = AppConstant::FAN_ID_PREFIX . '-' . AppConstant::FAN_ID_COUNTRY . '-' . $userId . '-' . $random6;
            $fullId  = $base . '-' . $this->checkDigit($base);

            // Uniqueness check against users.fan_id
            if (! User::where('fan_id', $fullId)->exists()) {
                return $fullId;
            }
        }

        // Fallback — practically unreachable
        $userId  = str_pad((string) $user->id, 5, '0', STR_PAD_LEFT);
        $random6 = $this->randomAlphanumeric(6);
        $base    = AppConstant::FAN_ID_PREFIX . '-' . AppConstant::FAN_ID_COUNTRY . '-' . $userId . '-' . $random6;

        return $base . '-' . $this->checkDigit($base);
    }

    private function randomAlphanumeric(int $length): string
    {
        $chars  = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // No ambiguous 0/O/1/I
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $result;
    }

    private function checkDigit(string $base): string
    {
        $sum = array_sum(array_map('ord', str_split($base)));

        return (string) ($sum % 10);
    }
}
