<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\SocialLoginRequest;
use App\Services\User\SocialAuthService;
use Illuminate\Http\JsonResponse;

class SocialAuthController extends Controller
{
    public function __construct(private SocialAuthService $socialAuthService) {}

    /**
     * POST /user/auth/social
     *
     * Mobile client sends provider + id_token after completing OAuth on device.
     * Backend verifies token, finds or creates user, returns Sanctum token.
     */
    public function login(SocialLoginRequest $request): JsonResponse
    {
        $result = $this->socialAuthService->loginWithProvider(
            $request->provider,
            $request->id_token
        );

        return $this->success('Social login successful.', $result);
    }
}
