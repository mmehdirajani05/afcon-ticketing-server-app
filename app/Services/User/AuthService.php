<?php

namespace App\Services\User;

use App\Constants\AppConstant;
use App\Exceptions\EmailNotVerifiedException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(private OTPService $otpService) {}

    public function register(array $data): array
    {
        $user = User::create([
            'name'                => $data['name'],
            'email'               => $data['email'],
            'phone'               => $data['phone'] ?? null,
            'password'            => $data['password'],
            'registration_source' => $data['registration_source'] ?? AppConstant::SOURCE_EMAIL,
            'global_role'         => $data['global_role'] ?? AppConstant::ROLE_CUSTOMER,
        ]);

        $user->refresh();

        $this->otpService->send($user, AppConstant::OTP_TYPE_EMAIL_VERIFICATION);

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

        $this->otpService->verify($user, $otp, AppConstant::OTP_TYPE_EMAIL_VERIFICATION);

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
            $this->otpService->send($user, AppConstant::OTP_TYPE_EMAIL_VERIFICATION);
            throw new EmailNotVerifiedException();
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated. Please contact support.'],
            ]);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return array_merge($user->toArray(), ['token' => $token]);
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function forgotPassword(string $email): void
    {
        $user = User::where('email', $email)->first();

        // Always return success to prevent email enumeration
        if ($user && $user->email_verified_at) {
            $this->otpService->send($user, AppConstant::OTP_TYPE_PASSWORD_RESET);
        }
    }

    public function resetPassword(string $email, string $otp, string $newPassword): void
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['No account found with this email.'],
            ]);
        }

        $this->otpService->verify($user, $otp, AppConstant::OTP_TYPE_PASSWORD_RESET);

        $user->update(['password' => $newPassword]);
    }

    public function resendOtp(string $email, string $type): void
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['No account found with this email.'],
            ]);
        }

        $this->otpService->resend($user, $type);
    }
}
