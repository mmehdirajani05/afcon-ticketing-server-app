<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\ForgotPasswordRequest;
use App\Http\Requests\Api\User\LoginRequest;
use App\Http\Requests\Api\User\RegisterRequest;
use App\Http\Requests\Api\User\ResetPasswordRequest;
use App\Http\Requests\Api\User\VerifyEmailRequest;
use App\Services\User\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->success('Registration successful. Please verify your email.', $result, 201);
    }

    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        $result = $this->authService->verifyEmail($request->email, $request->otp);

        return $this->success('Email verified successfully.', $result);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return $this->success('Login successful.', $result);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->success('Logged out successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success('User fetched.', $request->user());
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->forgotPassword($request->email);

        return $this->success('If this email exists, an OTP has been sent.');
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword(
            $request->email,
            $request->otp,
            $request->password
        );

        return $this->success('Password reset successfully. Please log in with your new password.');
    }

    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email'   => ['required', 'email'],
            'purpose' => ['required', 'string', 'in:email_verification,password_reset'],
        ]);

        $this->authService->resendOtp($request->email, $request->purpose);

        return $this->success('OTP resent successfully.');
    }
}
