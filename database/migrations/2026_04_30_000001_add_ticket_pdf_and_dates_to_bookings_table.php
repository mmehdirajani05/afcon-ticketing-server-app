<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'booking_reference')) {
                $table->string('booking_reference', 100)->nullable()->after('id');
                $table->index('booking_reference');
            }

            if (! Schema::hasColumn('bookings', 'ticket_pdf_url')) {
                $table->string('ticket_pdf_url')->nullable()->after('caf_ticket_ref');
            }

            if (! Schema::hasColumn('bookings', 'caf_hold_expires_at')) {
                $table->timestamp('caf_hold_expires_at')->nullable()->after('ticket_pdf_url');
                $table->index('caf_hold_expires_at');
            }

            if (! Schema::hasColumn('bookings', 'caf_booking_payload')) {
                $table->json('caf_booking_payload')->nullable()->after('caf_hold_expires_at');
            }

            if (! Schema::hasColumn('bookings', 'caf_confirmation_payload')) {
                $table->json('caf_confirmation_payload')->nullable()->after('caf_booking_payload');
            }

            if (! Schema::hasColumn('bookings', 'match_city')) {
                $table->string('match_city')->nullable()->after('venue');
            }

            if (! Schema::hasColumn('bookings', 'booked_at')) {
                $table->timestamp('booked_at')->nullable()->after('booking_status');
            }

            if (! Schema::hasColumn('bookings', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('booked_at');
            }

            if (! Schema::hasColumn('bookings', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('paid_at');
            }

            // Keep legacy string `match_date` but add a query-friendly datetime column.
            if (! Schema::hasColumn('bookings', 'match_date_at')) {
                $table->timestamp('match_date_at')->nullable()->after('match_date');
                $table->index('match_date_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'match_city')) {
                $table->dropColumn('match_city');
            }

            if (Schema::hasColumn('bookings', 'caf_confirmation_payload')) {
                $table->dropColumn('caf_confirmation_payload');
            }

            if (Schema::hasColumn('bookings', 'caf_booking_payload')) {
                $table->dropColumn('caf_booking_payload');
            }

            if (Schema::hasColumn('bookings', 'caf_hold_expires_at')) {
                $table->dropIndex(['caf_hold_expires_at']);
                $table->dropColumn('caf_hold_expires_at');
            }

            if (Schema::hasColumn('bookings', 'ticket_pdf_url')) {
                $table->dropColumn('ticket_pdf_url');
            }

            if (Schema::hasColumn('bookings', 'booking_reference')) {
                $table->dropIndex(['booking_reference']);
                $table->dropColumn('booking_reference');
            }

            if (Schema::hasColumn('bookings', 'confirmed_at')) {
                $table->dropColumn('confirmed_at');
            }

            if (Schema::hasColumn('bookings', 'paid_at')) {
                $table->dropColumn('paid_at');
            }

            if (Schema::hasColumn('bookings', 'booked_at')) {
                $table->dropColumn('booked_at');
            }

            if (Schema::hasColumn('bookings', 'match_date_at')) {
                $table->dropIndex(['match_date_at']);
                $table->dropColumn('match_date_at');
            }
        });
    }
};

