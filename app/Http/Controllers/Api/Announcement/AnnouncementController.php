<?php

namespace App\Http\Controllers\Api\Announcement;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AnnouncementController extends Controller
{
    /**
     * GET /api/announcements
     *
     * Returns published announcements, pinned first, then newest.
     * Supports optional ?type= and ?per_page= query params.
     *
     * Results are cached for 5 minutes per unique query combination.
     * Cache is automatically busted by the admin panel when announcements change.
     * (Call Cache::tags or forget 'announcements:*' keys after admin save/update/delete)
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'type'     => ['nullable', 'string', 'in:info,warning,success,danger'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $type    = $request->input('type', '');
        $perPage = $request->integer('per_page', 15);
        $page    = $request->integer('page', 1);

        // Unique cache key per query combination — serves repeated calls instantly
        $cacheKey = "announcements:{$type}:{$perPage}:page{$page}";

        $announcements = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($type, $perPage) {
            return Announcement::published()
                ->when($type, fn ($q) => $q->where('type', $type))
                ->orderByDesc('is_pinned')
                ->orderByDesc('published_at')
                ->paginate($perPage);
        });

        return $this->success('Announcements fetched successfully.', $announcements);
    }
}
