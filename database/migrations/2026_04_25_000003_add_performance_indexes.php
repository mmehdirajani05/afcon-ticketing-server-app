<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance indexes for frequently queried columns.
 *
 * Profiling revealed that every OTP verification, email send, and
 * announcement listing was doing full-table scans. These composite
 * indexes make those queries use index seeks instead.
 *
 * The up() blocks are written idempotently — each is wrapped so that a
 * "Duplicate key name" error (index already exists) is silently ignored.
 * This makes it safe to re-run if a previous migration attempt partially
 * succeeded before rolling back.
 */
return new class extends Migration
{
    public function up(): void
    {
        // OTP lookup: WHERE user_id = ? AND type = ? AND is_used = 0 ORDER BY created_at DESC
        // Every register, login, forgot-password, resend-otp hits this query.
        $this->safely('user_otps', function (Blueprint $table) {
            $table->index(['user_id', 'type', 'is_used'], 'idx_user_otps_user_type_used');
            $table->index('expires_at', 'idx_user_otps_expires_at');
        });

        // Immigration detail lookup: WHERE user_id = ? ORDER BY created_at DESC + status filter
        $this->safely('user_immigration_details', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'idx_immigration_user_status');
        });

        // Announcements: published() scope + ORDER BY is_pinned DESC, published_at DESC
        $this->safely('announcements', function (Blueprint $table) {
            $table->index(['status', 'is_pinned', 'published_at'], 'idx_announcements_status_pinned_pub');
        });

        // social_accounts already has a UNIQUE index on (provider, provider_user_id)
        // from migration 2026_04_21_000001 — no extra index needed.
    }

    public function down(): void
    {
        $this->safelyDrop('user_otps', function (Blueprint $table) {
            $table->dropIndex('idx_user_otps_user_type_used');
            $table->dropIndex('idx_user_otps_expires_at');
        });

        $this->safelyDrop('user_immigration_details', function (Blueprint $table) {
            $table->dropIndex('idx_immigration_user_status');
        });

        $this->safelyDrop('announcements', function (Blueprint $table) {
            $table->dropIndex('idx_announcements_status_pinned_pub');
        });

        // social_accounts index not owned by this migration — nothing to drop.
    }

    /** Add indexes, silently skipping any that already exist. */
    private function safely(string $table, \Closure $callback): void
    {
        try {
            Schema::table($table, $callback);
        } catch (QueryException $e) {
            // 1061 = Duplicate key name — index already exists, safe to skip
            if (! str_contains($e->getMessage(), 'Duplicate key name')) {
                throw $e;
            }
        }
    }

    /** Drop indexes, silently skipping any that do not exist. */
    private function safelyDrop(string $table, \Closure $callback): void
    {
        try {
            Schema::table($table, $callback);
        } catch (QueryException $e) {
            // 1091 = Can't DROP; check index/key exists — safe to skip
            if (! str_contains($e->getMessage(), "Can't DROP")) {
                throw $e;
            }
        }
    }
};
