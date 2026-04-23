<?php

namespace App\Services\User;

use App\Constants\AppConstant;
use App\Models\User;
use App\Models\UserOtp;
use App\Services\Email\EmailTemplateService;
use Illuminate\Validation\ValidationException;

class OTPService
{
    public function __construct(private EmailTemplateService $emailService) {}

    /**
     * Generate and send a fresh OTP for the given user and purpose.
     */
    public function send(User $user, string $type): void
    {
        // Invalidate any previous unused OTPs of the same type
        UserOtp::where('user_id', $user->id)
            ->where('type', $type)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        $code = $this->generateCode();

        UserOtp::create([
            'user_id'    => $user->id,
            'otp'        => $code,
            'type'       => $type,
            'expires_at' => now()->addMinutes((int) config('otp.expiry_minutes', AppConstant::OTP_EXPIRY_MINUTES)),
        ]);

        $this->emailService->sendOtp($user, $code, $type);
    }

    /**
     * Validate OTP and mark it as used on success.
     *
     * @throws ValidationException
     */
    public function verify(User $user, string $code, string $type): void
    {
        $record = UserOtp::where('user_id', $user->id)
            ->where('type', $type)
            ->where('is_used', false)
            ->latest()
            ->first();

        if (! $record || ! $record->isValid() || $record->otp !== $code) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP. Please request a new one.'],
            ]);
        }

        $record->update(['is_used' => true]);
    }

    /**
     * Resend OTP — same as send(), exposed separately for clarity in controllers.
     */
    public function resend(User $user, string $type): void
    {
        $this->send($user, $type);
    }

    private function generateCode(): string
    {
        return str_pad(
            (string) random_int(0, 10 ** AppConstant::OTP_DIGITS - 1),
            AppConstant::OTP_DIGITS,
            '0',
            STR_PAD_LEFT
        );
    }
}
