<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fan ID application lives entirely on the users table.
 * A user can apply exactly once; the columns are null until they apply.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Identity document submitted during Fan ID application
            $table->string('fan_id_full_name')->nullable()->after('fan_id');
            $table->string('fan_id_identity_type', 30)->nullable()->after('fan_id_full_name');   // nic, permit, special_pass, visa
            $table->string('fan_id_identity_number', 100)->nullable()->after('fan_id_identity_type');
            $table->string('fan_id_nationality', 10)->nullable()->after('fan_id_identity_number'); // ISO 3166-1 alpha-2
            $table->date('fan_id_date_of_birth')->nullable()->after('fan_id_nationality');

            // Verification lifecycle
            $table->string('fan_id_status', 30)->nullable()->after('fan_id_date_of_birth');      // pending | verified | rejected
            $table->string('fan_id_rejection_reason')->nullable()->after('fan_id_status');
            $table->timestamp('fan_id_verified_at')->nullable()->after('fan_id_rejection_reason');

            $table->index('fan_id_status');
            $table->index('fan_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
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
};
