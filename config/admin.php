<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Configuration
    |--------------------------------------------------------------------------
    | Configurable theme and branding settings. Override via .env to avoid
    | hardcoding colors or names in Blade templates.
    */

    'app_name'      => env('ADMIN_APP_NAME', 'AFCON Admin'),
    'app_subtitle'  => env('ADMIN_APP_SUBTITLE', 'Africa Cup of Nations 2027'),

    /*
    |--------------------------------------------------------------------------
    | Theme Colors
    |--------------------------------------------------------------------------
    | Primary color drives buttons, active nav items, badges, and gradients.
    | All Blade templates reference config('admin.primary_color') — never
    | hardcoded hex values — so a single .env change rebrands the panel.
    */

    'primary_color'       => env('ADMIN_PRIMARY_COLOR', '#008EC0'),
    'primary_color_dark'  => env('ADMIN_PRIMARY_COLOR_DARK', '#006B91'),
    'primary_color_light' => env('ADMIN_PRIMARY_COLOR_LIGHT', '#40BADF'),

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    'per_page' => env('ADMIN_PER_PAGE', 20),

    /*
    |--------------------------------------------------------------------------
    | Allowed Admin Roles
    |--------------------------------------------------------------------------
    */

    'roles' => ['admin', 'sub_admin'],

    /*
    |--------------------------------------------------------------------------
    | Permission Definitions
    |--------------------------------------------------------------------------
    | Master list used when building the role permission matrix UI.
    */

    'permissions' => [
        'users'         => ['view', 'edit', 'delete', 'suspend'],
        'orders'        => ['view', 'refund', 'cancel'],
        'announcements' => ['view', 'create', 'edit', 'delete'],
        'chat'          => ['view', 'reply'],
        'roles'         => ['view', 'create', 'edit', 'delete'],
        'dashboard'     => ['view'],
    ],
];
