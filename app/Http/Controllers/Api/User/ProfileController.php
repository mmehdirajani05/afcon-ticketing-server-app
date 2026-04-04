<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\UpdateProfileRequest;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function show(Request $request): JsonResponse
    {
        return $this->success('Profile fetched.', $this->userService->getProfile($request->user()));
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $profile = $this->userService->updateProfile($request->user(), $request->validated());

        return $this->success('Profile updated.', $profile);
    }
}
