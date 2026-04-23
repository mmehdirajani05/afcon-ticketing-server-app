<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('fan_id', 60)->nullable();           // AFCON27-TZ-... string reference
            $table->string('caf_ticket_ref', 100)->nullable()->unique();
            $table->string('match_id', 100)->nullable();
            $table->string('match_name')->nullable();           // e.g. "Morocco vs Egypt"
            $table->string('match_date')->nullable();
            $table->string('venue')->nullable();
            $table->string('ticket_category', 50)->nullable();  // CAT1, CAT2, VIP, etc.
            $table->string('seat_info', 100)->nullable();       // Row A, Seat 12 (optional)
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 10)->default('TZS');

            // Payment lifecycle
            $table->string('payment_status', 30)->default('pending'); // pending, paid, failed
            $table->string('transaction_id', 100)->nullable()->unique();
            $table->json('payment_metadata')->nullable();        // NMB raw response

            // Booking lifecycle
            $table->string('booking_status', 30)->default('pending'); // pending, confirmed, cancelled, refunded

            // Refund lifecycle
            $table->string('refund_status', 30)->nullable();     // refund_requested, refund_processing, refunded, failed
            $table->string('refund_transaction_id', 100)->nullable();
            $table->timestamp('refund_requested_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('fan_id');
            $table->index('payment_status');
            $table->index('booking_status');
            $table->index('match_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
