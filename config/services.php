<?php

return [

    // ── Third-party mail services ─────────────────────────────────────────────
    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // ── Social Auth ───────────────────────────────────────────────────────────
    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    ],

    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'),   // Bundle ID / Service ID
        'team_id'   => env('APPLE_TEAM_ID'),
        'key_id'    => env('APPLE_KEY_ID'),
        'key_file'  => env('APPLE_KEY_FILE'),    // Path to .p8 private key
    ],

    // ── Firebase ──────────────────────────────────────────────────────────────
    'firebase' => [
        'credentials' => env('FIREBASE_CREDENTIALS'),  // Path to service-account.json
        'project_id'  => env('FIREBASE_PROJECT_ID'),
        'server_key'  => env('FIREBASE_SERVER_KEY'),   // Legacy FCM server key (fallback)
    ],

    // ── Immigration Verification ──────────────────────────────────────────────
    'immigration' => [
        'skip' => env('IMMIGRATION_SKIP', false),      // true = bypass in dev, Fan ID issued instantly
        'mode' => env('IMMIGRATION_MODE', 'delayed'),  // realtime | delayed
        'url'  => env('IMMIGRATION_API_URL'),
        'key'  => env('IMMIGRATION_API_KEY'),
    ],

    // ── CAF Ticketing ─────────────────────────────────────────────────────────
    'caf' => [
        'url'     => env('CAF_API_URL'),
        'key'     => env('CAF_API_KEY'),
        'timeout' => env('CAF_API_TIMEOUT', 15),
    ],

    // ── NMB Payment Gateway ───────────────────────────────────────────────────
    'nmb' => [
        'url'          => env('NMB_API_URL'),
        'merchant_id'  => env('NMB_MERCHANT_ID'),
        'secret_key'   => env('NMB_SECRET_KEY'),
        'redirect_url' => env('NMB_REDIRECT_URL'),
        'timeout'      => env('NMB_API_TIMEOUT', 15),
    ],

];
