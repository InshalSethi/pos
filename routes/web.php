<?php

use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return view('app');
});

// Serve the Vue.js SPA for all routes
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
