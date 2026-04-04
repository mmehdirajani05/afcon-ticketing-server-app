<?php

use Illuminate\Support\Facades\Route;

Route::get('health', fn() => response()->json(['status' => 'ok']));

require __DIR__ . '/api/user.php';
// require __DIR__ . '/api/hotel.php';
// require __DIR__ . '/api/admin.php';
