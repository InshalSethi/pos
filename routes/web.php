<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GoogleAuthController;

// 1. Google Auth Routes (Inhein uupar hona chahiye)
Route::get('/auth/google/redirect', [GoogleAuthController::class , 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class , 'handleGoogleCallback']);

// 2. Password reset routes
Route::get('/reset-password/{token}', function (string $token) {
    return view('app', ['token' => $token]);
})->middleware('guest')->name('password.reset');

// 3. Root route
Route::get('/', function () {
    return view('app');
});

// 4. Vue.js SPA Catch-all (Ye hamesha LAST mein hona chahiye)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
