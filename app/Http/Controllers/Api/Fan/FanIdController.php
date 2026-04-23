<?php

namespace App\Http\Controllers\Api\Fan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Fan\StoreFanIdRequest;
use App\Services\Fan\FanIdService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FanIdController extends Controller
{
    public function __construct(private FanIdService $fanIdService) {}

    /**
     * GET /user/fan-id
     * Return the authenticated user's Fan ID status and details.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->fan_id_status) {
            return $this->error('No Fan ID application found. Please apply first.', 404);
        }

        return $this->success('Fan ID details fetched.', $this->fanIdData($user));
    }

    /**
     * POST /user/fan-id
     * Submit a Fan ID application (only allowed once per user).
     */
    public function store(StoreFanIdRequest $request): JsonResponse
    {
        $user = $this->fanIdService->apply($request->user(), $request->validated());

        $message = $user->hasFanId()
            ? 'Fan ID issued successfully.'
            : 'Application submitted. You will be notified within 24 hours.';

        return $this->success($message, $this->fanIdData($user), 201);
    }

    /**
     * Shape the Fan ID portion of the user record for API responses.
     */
    private function fanIdData(\App\Models\User $user): array
    {
        return [
            'fan_id'              => $user->fan_id,
            'fan_id_status'       => $user->fan_id_status,
            'fan_id_full_name'    => $user->fan_id_full_name,
            'fan_id_identity_type'=> $user->fan_id_identity_type,
            'fan_id_nationality'  => $user->fan_id_nationality,
            'fan_id_date_of_birth'=> $user->fan_id_date_of_birth?->format('Y-m-d'),
            'fan_id_verified_at'  => $user->fan_id_verified_at?->toIso8601String(),
            'fan_id_rejection_reason' => $user->fan_id_rejection_reason,
            'immigration_logs'    => $user->immigrationLogs()->latest()->get(),
        ];
    }
}
