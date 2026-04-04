<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\LoginRequest;
use App\Http\Requests\Api\User\RegisterRequest;
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

        return $this->success('Registration successful.', $result, 201);
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

    public function forgotPassword(): JsonResponse
    {
        return $this->error('Not implemented yet.', 501);
    }

    public function resetPassword(): JsonResponse
    {
        return $this->error('Not implemented yet.', 501);
    }
}
