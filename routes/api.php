<?php

use App\Http\Controllers\Api\Ticket\BookingController;
use Illuminate\Support\Facades\Route;

// Health check
Route::get('health', fn () => response()->json(['status' => 'ok', 'timestamp' => now()->toIso8601String()]));

// NMB payment callback (public — verified by checksum inside controller)
Route::post('payments/nmb/callback', [BookingController::class, 'nmbCallback'])
    ->middleware('throttle:payment-callback')
    ->name('payments.nmb.callback');

require __DIR__ . '/api/user.php';
