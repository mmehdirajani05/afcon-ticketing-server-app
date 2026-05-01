<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'match_date_at')) {
                $table->dropIndex(['match_date_at']);
                $table->dropColumn('match_date_at');
            }

            if (Schema::hasColumn('bookings', 'booking_reference')) {
                $table->dropIndex(['booking_reference']);
                $table->dropColumn('booking_reference');
            }

            if (Schema::hasColumn('bookings', 'currency')) {
                $table->dropColumn('currency');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'currency')) {
                $table->string('currency', 10)->default('TZS')->after('amount');
            }

            if (! Schema::hasColumn('bookings', 'booking_reference')) {
                $table->string('booking_reference', 100)->nullable()->after('id');
                $table->index('booking_reference');
            }

            if (! Schema::hasColumn('bookings', 'match_date_at')) {
                $table->timestamp('match_date_at')->nullable()->after('match_date');
                $table->index('match_date_at');
            }
        });
    }
};

