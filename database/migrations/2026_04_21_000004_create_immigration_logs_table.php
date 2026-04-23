<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('immigration_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('mode', 20)->default('realtime'); // realtime, delayed
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->string('status', 30)->default('pending'); // pending, verified, rejected, failed
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('immigration_logs');
    }
};
