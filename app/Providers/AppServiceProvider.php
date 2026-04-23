<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureRateLimiters();
    }

    private function configureRateLimiters(): void
    {
        // General API: 60 requests/minute per user or IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // OTP & auth: 5 requests/minute per IP (prevents brute-force / OTP spamming)
        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())
                ->response(fn () => response()->json([
                    'status'  => 0,
                    'message' => 'Too many OTP requests. Please wait before trying again.',
                ], 429));
        });

        // Social login: 10 attempts/minute per IP
        RateLimiter::for('social-login', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip())
                ->response(fn () => response()->json([
                    'status'  => 0,
                    'message' => 'Too many login attempts. Please try again later.',
                ], 429));
        });

        // Payment callbacks: 30/minute per IP (NMB may send retries)
        RateLimiter::for('payment-callback', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });
    }
}
