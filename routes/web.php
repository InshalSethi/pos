<?php

use Illuminate\Support\Facades\Route;

// Password reset routes (required by Laravel's password reset functionality)
Route::get('/reset-password/{token}', function (string $token) {
    return view('app', ['token' => $token]);
})->middleware('guest')->name('password.reset');

// Redirect root to login
Route::get('/', function () {
    return view('app');
});

// Serve the Vue.js SPA for all routes
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
