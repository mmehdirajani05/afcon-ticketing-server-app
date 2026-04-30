<?php

use App\Http\Controllers\Api\Announcement\AnnouncementController;
use App\Http\Controllers\Api\Ticket\BookingController;
use Illuminate\Support\Facades\Route;

// Health check
Route::get('health', fn () => response()->json(['status' => 'ok', 'timestamp' => now()->toIso8601String()]));

// Public announcements (published only)
Route::get('announcements', [AnnouncementController::class, 'index']);

// NMB payment callback (public — verified by checksum inside controller)
Route::post('payments/nmb/callback', [BookingController::class, 'nmbCallback'])
    ->middleware('throttle:payment-callback')
    ->name('payments.nmb.callback');

require __DIR__ . '/api/user.php';
