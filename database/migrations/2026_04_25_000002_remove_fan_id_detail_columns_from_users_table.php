<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Removes all fan_id_* detail columns from the users table.
 * These have been moved to the dedicated user_immigration_details table.
 *
 * The only fan_id column kept on users is `fan_id` itself —
 * the final generated Fan ID string written once after verification.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes first (MySQL requires this before dropping columns)
            $table->dropIndex(['fan_id_status']);
            $table->dropIndex(['fan_id']);

            $table->dropColumn([
                'fan_id_full_name',
                'fan_id_identity_type',
                'fan_id_identity_number',
                'fan_id_nationality',
                'fan_id_date_of_birth',
                'fan_id_status',
                'fan_id_rejection_reason',
                'fan_id_verified_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('fan_id_full_name')->nullable()->after('fan_id');
            $table->string('fan_id_identity_type', 30)->nullable()->after('fan_id_full_name');
            $table->string('fan_id_identity_number', 100)->nullable()->after('fan_id_identity_type');
            $table->string('fan_id_nationality', 10)->nullable()->after('fan_id_identity_number');
            $table->date('fan_id_date_of_birth')->nullable()->after('fan_id_nationality');
            $table->string('fan_id_status', 30)->nullable()->after('fan_id_date_of_birth');
            $table->string('fan_id_rejection_reason')->nullable()->after('fan_id_status');
            $table->timestamp('fan_id_verified_at')->nullable()->after('fan_id_rejection_reason');

            $table->index('fan_id_status');
            $table->index('fan_id');
        });
    }
};
