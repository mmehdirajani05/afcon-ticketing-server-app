<?php

namespace App\Http\Controllers\Api\Fan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Fan\StoreFanIdRequest;
use App\Models\User;
use App\Models\UserImmigrationDetail;
use App\Services\Fan\FanIdService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FanIdController extends Controller
{
    public function __construct(private FanIdService $fanIdService) {}

    /**
     * GET /api/user/fan-id
     *
     * Returns the authenticated user's latest Fan ID application status and details.
     * Includes the generated fan_id from the users table once verified.
     */
    public function show(Request $request): JsonResponse
    {
        $user   = $request->user();

        // Eager-load immigration logs in the same query to avoid N+1
        $detail = $user->immigrationDetails()
            ->with('user.immigrationLogs')
            ->latest()
            ->first();

        if (! $detail) {
            return $this->error('No Fan ID application found. Please apply first.', 404);
        }

        return $this->success('Fan ID details fetched.', $this->formatDetail($user, $detail));
    }

    /**
     * POST /api/user/fan-id
     *
     * Submit a Fan ID application.
     *
     * Required fields:
     *   - full_name             string
     *   - gender                male | female
     *   - date_of_birth         date (Y-m-d), past date
     *   - nationality           2-letter ISO country code (e.g. TZ, MA, EG)
     *   - identity_type         nic | permit | special_pass | visa
     *   - identity_number       string (document number)
     *   - identity_expiry_date  date (Y-m-d), must be a future date
     *
     * With IMMIGRATION_SKIP=true (current dev config), Fan ID is generated instantly.
     * In production, status will be 'pending' until immigration approves.
     */
    public function store(StoreFanIdRequest $request): JsonResponse
    {
        $user   = $request->user();
        $detail = $this->fanIdService->apply($user, $request->validated());

        $message = $detail->isVerified()
            ? 'Fan ID issued successfully.'
            : 'Application submitted. You will be notified once verified.';

        // Refresh user to get the fan_id that markVerified() just wrote
        $user->refresh();

        return $this->success($message, $this->formatDetail($user, $detail), 201);
    }

    /**
     * Shape the API response for a Fan ID application.
     * identity_number is intentionally excluded (sensitive document data).
     * $user is passed explicitly to avoid loading it again via $detail->user (N+1).
     */
    private function formatDetail(User $user, UserImmigrationDetail $detail): array
    {
        // Use already-loaded relation if present, otherwise query directly
        $logs = $detail->relationLoaded('user')
            ? $detail->user->immigrationLogs->sortByDesc('created_at')->values()
            : $user->immigrationLogs()->latest()->get();

        return [
            // Final Fan ID on the user (populated only after verification)
            'fan_id'                => $user->fan_id,

            // Application status
            'status'                => $detail->status,
            'verified_at'           => $detail->verified_at?->toIso8601String(),
            'rejection_reason'      => $detail->rejection_reason,
            'submitted_at'          => $detail->submitted_at?->toIso8601String(),

            // Personal details
            'full_name'             => $detail->full_name,
            'gender'                => $detail->gender,
            'date_of_birth'         => $detail->date_of_birth?->format('Y-m-d'),
            'nationality'           => $detail->nationality,

            // Document details (number excluded)
            'identity_type'         => $detail->identity_type,
            'identity_expiry_date'  => $detail->identity_expiry_date?->format('Y-m-d'),

            // Immigration API call history (for transparency)
            'immigration_logs'      => $logs,
        ];
    }
}
