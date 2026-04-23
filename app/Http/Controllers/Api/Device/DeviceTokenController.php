<?php

namespace App\Http\Controllers\Api\Device;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Device\DeviceTokenRequest;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    /**
     * POST /user/device-tokens
     * Register or update a device FCM token.
     */
    public function store(DeviceTokenRequest $request): JsonResponse
    {
        $token = DeviceToken::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'token'   => $request->token,
            ],
            [
                'platform'  => $request->platform,
                'is_active' => true,
            ]
        );

        return $this->success('Device token registered.', $token, 201);
    }

    /**
     * DELETE /user/device-tokens
     * Deactivate a device token (on logout/app uninstall).
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        DeviceToken::where('user_id', $request->user()->id)
            ->where('token', $request->token)
            ->update(['is_active' => false]);

        return $this->success('Device token deactivated.');
    }
}
