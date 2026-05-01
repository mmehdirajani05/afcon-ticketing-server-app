<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'caf_hold_expires_at')) {
                $table->dropIndex(['caf_hold_expires_at']);
                $table->dropColumn('caf_hold_expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'caf_hold_expires_at')) {
                $table->timestamp('caf_hold_expires_at')->nullable()->after('ticket_pdf_url');
                $table->index('caf_hold_expires_at');
            }
        });
    }
};

