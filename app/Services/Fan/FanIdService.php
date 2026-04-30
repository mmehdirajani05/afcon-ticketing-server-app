<?php

namespace App\Services\Fan;

use App\Constants\AppConstant;
use App\Models\User;
use App\Models\UserImmigrationDetail;
use App\Services\Email\EmailTemplateService;
use App\Services\Notification\FirebaseNotificationService;
use Illuminate\Validation\ValidationException;

class FanIdService
{
    public function __construct(
        private ImmigrationVerificationService $immigrationService,
        private FirebaseNotificationService    $notificationService,
        private EmailTemplateService           $emailService,
    ) {}

    /**
     * Submit a new Fan ID application.
     *
     * Flow:
     *   1. Block if user already has a verified Fan ID or a pending application.
     *   2. Create a row in user_immigration_details (status = pending).
     *   3. Send to ImmigrationVerificationService:
     *        - IMMIGRATION_SKIP=true  → instant 'verified' (dev / smooth-flow mode)
     *        - realtime mode          → calls live immigration API (TODO: wire up)
     *        - delayed mode           → stays pending; processed by admin/cron later
     *   4. If verified → generate Fan ID, save to users.fan_id, update detail status.
     *   5. If rejected → save rejection reason on the detail row.
     *
     * TODO: Disable IMMIGRATION_SKIP in .env and configure IMMIGRATION_API_URL +
     *       IMMIGRATION_API_KEY once the immigration department provides credentials.
     *
     * TODO: Send push notification and email on verify/reject once Firebase is configured.
     *       Placeholders are marked below in markVerified() and markRejected().
     *
     * @return UserImmigrationDetail
     */
    public function apply(User $user, array $data): UserImmigrationDetail
    {
        // ── Guard: one active application at a time ────────────────────────────
        $existing = UserImmigrationDetail::where('user_id', $user->id)
            ->whereIn('status', [
                AppConstant::FAN_ID_STATUS_PENDING,
                AppConstant::FAN_ID_STATUS_VERIFIED,
            ])
            ->latest()
            ->first();

        if ($existing?->isVerified()) {
            throw ValidationException::withMessages([
                'fan_id' => ['You already have a verified Fan ID: ' . $user->fan_id],
            ]);
        }

        if ($existing?->isPending()) {
            throw ValidationException::withMessages([
                'fan_id' => ['Your Fan ID application is currently under review.'],
            ]);
        }

        // ── Step 1: Persist identity data to dedicated table ───────────────────
        //    All proof/document details live here, not on the users table.
        //    Previous rejected rows are kept as audit history.
        $detail = UserImmigrationDetail::create([
            'user_id'               => $user->id,
            'full_name'             => $data['full_name'],
            'gender'                => $data['gender'],
            'date_of_birth'         => $data['date_of_birth'],
            'nationality'           => $data['nationality'],
            'identity_type'         => $data['identity_type'],
            'identity_number'       => $data['identity_number'],
            'identity_expiry_date'  => $data['identity_expiry_date'],
            'status'                => AppConstant::FAN_ID_STATUS_PENDING,
            'submitted_at'          => now(),
        ]);

        // ── Step 2: Send to immigration verification ───────────────────────────
        //
        // Current behaviour (IMMIGRATION_SKIP=true in .env):
        //   Returns 'verified' immediately so Fan IDs are issued on the spot.
        //   Keeps the flow smooth while the immigration API is not yet available.
        //
        // TODO: Set IMMIGRATION_SKIP=false and configure IMMIGRATION_API_URL /
        //       IMMIGRATION_API_KEY in .env when immigration department API is ready.
        $result = $this->immigrationService->verify($user, $detail);

        if ($result['status'] === AppConstant::FAN_ID_STATUS_VERIFIED) {
            return $this->markVerified($user, $detail);
        }

        if ($result['status'] === AppConstant::FAN_ID_STATUS_REJECTED) {
            return $this->markRejected($detail, $result['message'] ?? '');
        }

        // Application stays pending — admin or cron job calls markVerified()/markRejected() later.
        return $detail->fresh();
    }

    /**
     * Mark an application as verified, generate the Fan ID and assign it to the user.
     *
     * Called from:
     *   - apply()           when immigration returns 'verified' immediately
     *   - Admin panel       when an admin manually approves a pending application
     *   - Artisan command   when the delayed-mode batch job processes pending records
     */
    public function markVerified(User $user, UserImmigrationDetail $detail): UserImmigrationDetail
    {
        $fanId = $this->generateFanId($user);

        // Update the detail record
        $detail->update([
            'status'      => AppConstant::FAN_ID_STATUS_VERIFIED,
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);

        // Write the final Fan ID to the users table — this is the only fan_id field on users
        $user->update(['fan_id' => $fanId]);

        // Send Fan ID approval email (same design as OTP email, different content)
        $this->emailService->sendFanIdApproved($user, $fanId);

        // TODO: Send push notification once Firebase credentials are configured.
        //       Uncomment after setting FIREBASE_CREDENTIALS + FIREBASE_PROJECT_ID in .env.
        // $this->notificationService->send(
        //     $user,
        //     'Fan ID Verified ✅',
        //     'Your Fan ID has been approved: ' . $fanId,
        //     ['type' => 'fan_id_verified', 'fan_id' => $fanId]
        // );

        return $detail->fresh();
    }

    /**
     * Mark an application as rejected and store the reason.
     *
     * Called from:
     *   - apply()           when immigration returns 'rejected' immediately
     *   - Admin panel       when an admin manually rejects a pending application
     */
    public function markRejected(UserImmigrationDetail $detail, string $reason = ''): UserImmigrationDetail
    {
        $detail->update([
            'status'           => AppConstant::FAN_ID_STATUS_REJECTED,
            'rejection_reason' => $reason ?: 'Identity could not be verified.',
        ]);

        // TODO: Send push notification once Firebase is configured.
        // $this->notificationService->send(
        //     $detail->user,
        //     'Fan ID Not Approved',
        //     'Your application was not approved. ' . ($reason ?: 'Please re-apply with correct documents.'),
        //     ['type' => 'fan_id_rejected']
        // );

        // TODO: Send rejection email with reason and re-apply instructions.

        return $detail->fresh();
    }

    /**
     * Generate a unique Fan ID string.
     *
     * Format:  {PREFIX}-{COUNTRY}-{USERID5}-{RANDOM6}-{CHECK1}
     * Example: AFCON27-TZ-00042-K9PZ3M-4
     *
     * - PREFIX  : AFCON27
     * - COUNTRY : TZ (ISO country code, configurable)
     * - USERID5 : zero-padded user ID (guarantees per-user uniqueness)
     * - RANDOM6 : random alphanumeric (excludes ambiguous chars 0/O/1/I)
     * - CHECK1  : single-digit checksum (sum of ASCII values mod 10)
     */
    public function generateFanId(User $user): string
    {
        for ($i = 0; $i < 10; $i++) {
            $userId  = str_pad((string) $user->id, 5, '0', STR_PAD_LEFT);
            $random6 = $this->randomAlphanumeric(6);
            $base    = AppConstant::FAN_ID_PREFIX . '-' . AppConstant::FAN_ID_COUNTRY . '-' . $userId . '-' . $random6;
            $fullId  = $base . '-' . $this->checkDigit($base);

            if (! User::where('fan_id', $fullId)->exists()) {
                return $fullId;
            }
        }

        // Fallback — practically unreachable given the random space
        $userId  = str_pad((string) $user->id, 5, '0', STR_PAD_LEFT);
        $random6 = $this->randomAlphanumeric(6);
        $base    = AppConstant::FAN_ID_PREFIX . '-' . AppConstant::FAN_ID_COUNTRY . '-' . $userId . '-' . $random6;

        return $base . '-' . $this->checkDigit($base);
    }

    private function randomAlphanumeric(int $length): string
    {
        $chars  = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // No ambiguous: 0/O/1/I
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $result;
    }

    private function checkDigit(string $base): string
    {
        return (string) (array_sum(array_map('ord', str_split($base))) % 10);
    }
}
