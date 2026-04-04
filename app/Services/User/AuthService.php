<?php

namespace App\Services\User;

use App\Constants\AppConstant;
use App\Exceptions\EmailNotVerifiedException;
use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{

    public function register(array $data): array
    {
        $user = User::create([
            'name'                => $data['name'],
            'email'               => $data['email'],
            'phone'               => $data['phone'] ?? null,
            'password'            => $data['password'],
            'registration_source' => $data['registration_source'] ?? 'email',
            'global_role'         => $data['global_role'],
        ]);

        $user->refresh();

        $this->generateOtp($user, AppConstant::OTP_TYPE_EMAIL_VERIFICATION);

        // token is not returned until email is verified
        return $user->toArray();
    }

    public function verifyEmail(string $email, string $otp): array
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['No account found with this email.'],
            ]);
        }

        if ($user->email_verified_at) {
            throw ValidationException::withMessages([
                'email' => ['Email is already verified.'],
            ]);
        }

        $record = UserOtp::where('user_id', $user->id)
            ->where('type', AppConstant::OTP_TYPE_EMAIL_VERIFICATION)
            ->where('is_used', false)
            ->latest()
            ->first();

        if (! $record || ! $record->isValid() || $record->otp !== $otp) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP.'],
            ]);
        }

        $record->update(['is_used' => true]);
        $user->update(['email_verified_at' => now()]);
        $user->refresh();

        $token = $user->createToken('auth_token')->plainTextToken;

        return array_merge($user->toArray(), ['token' => $token]);
    }

    public function login(array $data): array
    {
        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        if (! $user->email_verified_at) {
            $this->generateOtp($user, AppConstant::OTP_TYPE_EMAIL_VERIFICATION);

            throw new EmailNotVerifiedException();
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Account is deactivated.'],
            ]);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return array_merge($user->toArray(), ['token' => $token]);
    }

    public function logout(User $user): void
    {
        // revoke only the current token used to make this request
        $user->currentAccessToken()->delete();
    }

    private function generateOtp(User $user, string $type): void
    {
        // invalidate any previous unused OTPs of same type
        UserOtp::where('user_id', $user->id)
            ->where('type', $type)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        UserOtp::create([
            'user_id'    => $user->id,
            'otp'        => AppConstant::OTP_HARDCODED,
            'type'       => $type,
            'expires_at' => now()->addMinutes(AppConstant::OTP_EXPIRY_MINUTES),
        ]);
    }
}
