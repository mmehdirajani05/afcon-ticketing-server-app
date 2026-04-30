<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stores the identity/document details submitted by a user when applying for a Fan ID.
 *
 * Why a separate table instead of columns on users?
 *  - Keeps the users table lean (auth concerns only).
 *  - Preserves full history: a user can be rejected and re-apply — every submission
 *    is a separate row, providing a complete audit trail / proof of identity.
 *  - Sensitive document numbers are isolated; easier to encrypt or purge if required.
 *  - The final fan_id is stored only on users.fan_id once verified.
 *
 * Relationship: one user → many submissions (only one can be pending or verified at a time).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_immigration_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // ── Personal details ─────────────────────────────────────────────
            $table->string('full_name', 150);
            $table->string('gender', 10);                       // male | female
            $table->date('date_of_birth');
            $table->string('nationality', 2);                   // ISO 3166-1 alpha-2 e.g. TZ, MA

            // ── Identity document ────────────────────────────────────────────
            $table->string('identity_type', 30);                // nic | permit | special_pass | visa
            $table->string('identity_number', 100);             // sensitive — consider encrypting at rest
            $table->date('identity_expiry_date');

            // ── Verification lifecycle ────────────────────────────────────────
            // status: pending → verified | rejected
            $table->string('status', 30)->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();

            // ── Timestamps ───────────────────────────────────────────────────
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_immigration_details');
    }
};
