<?php

use App\Http\Controllers\Api\Device\DeviceTokenController;
use App\Http\Controllers\Api\Fan\FanIdController;
use App\Http\Controllers\Api\Ticket\BookingController;
use App\Http\Controllers\Api\Ticket\RefundController;
use App\Http\Controllers\Api\Ticket\TicketController;
use App\Http\Controllers\Api\User\AuthController;
use App\Http\Controllers\Api\User\ProfileController;
use App\Http\Controllers\Api\User\SocialAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('user')->group(function () {

    // ── Public Auth Routes ────────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('register',     [AuthController::class, 'register']);
        Route::post('login',        [AuthController::class, 'login']);
        Route::post('verify-email', [AuthController::class, 'verifyEmail'])->middleware('throttle:otp');
        Route::post('resend-otp',   [AuthController::class, 'resendOtp'])->middleware('throttle:otp');

        Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:otp');
        Route::post('reset-password',  [AuthController::class, 'resetPassword'])->middleware('throttle:otp');

        // Social login (Google / Apple)
        Route::post('social', [SocialAuthController::class, 'login'])->middleware('throttle:social-login');
    });

    // ── Authenticated Routes ──────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me',      [AuthController::class, 'me']);

        // Profile
        Route::get('profile',  [ProfileController::class, 'show']);
        Route::put('profile',  [ProfileController::class, 'update']);

        // Fan ID
        Route::get('fan-id',   [FanIdController::class, 'show']);
        Route::post('fan-id',  [FanIdController::class, 'store']);

        // Ticket search & booking
        Route::get('tickets',                              [TicketController::class, 'index']);
        Route::get('tickets/{matchId}',                    [TicketController::class, 'matchTickets']);
        Route::post('tickets/book',                        [TicketController::class, 'book']);
        Route::get('tickets/my',                           [TicketController::class, 'myTickets']);
        Route::get('tickets/{booking}/download',           [TicketController::class, 'download'])
            ->whereNumber('booking');

        // Bookings
        Route::get('bookings',                             [BookingController::class, 'index']);
        Route::post('bookings',                            [BookingController::class, 'store']);
        Route::get('bookings/{booking}',                   [BookingController::class, 'show']);
        Route::get('bookings/revenue',                     [BookingController::class, 'revenue']);

        // Refunds
        Route::post('bookings/{booking}/refund',           [RefundController::class, 'request']);
        Route::get('bookings/{booking}/refund-status',     [RefundController::class, 'status']);

        // Device tokens (Firebase FCM)
        Route::post('device-tokens',    [DeviceTokenController::class, 'store']);
        Route::delete('device-tokens',  [DeviceTokenController::class, 'destroy']);
    });
});
