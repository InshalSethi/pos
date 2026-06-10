<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Api\GoogleAuthController;

// 1. Google Auth Routes (Inhein uupar hona chahiye)
Route::get('/auth/google/redirect', [GoogleAuthController::class , 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class , 'handleGoogleCallback']);

// 2. Password reset routes
Route::get('/reset-password/{token}', function (string $token) {
    return view('app', ['token' => $token]);
})->middleware('guest')->name('password.reset');

// Authentication & Registration routes
Route::get('/login', function () {
    return view('app');
})->middleware('guest')->name('login');

Route::get('/register', function () {
    return view('app');
})->middleware('guest')->name('register');
Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register'])->middleware('guest');

// Onboarding and Company Management routes
Route::middleware(['auth'])->group(function () {
    // Wizard entry point — handles fresh start and draft resume via query params
    Route::get('/company-setup', [\App\Http\Controllers\CompanySetupController::class, 'index'])
        ->name('company.setup');

    // Permanently delete a specific draft by ID
    Route::delete('/onboarding/draft/purge/{id}', [\App\Http\Controllers\CompanySetupController::class, 'purgeIndividualDraft'])
        ->name('onboarding.purge-individual-draft');

    // Permanently delete a draft-only company record
    Route::post('/onboarding/discard', [\App\Http\Controllers\CompanySetupController::class, 'discardActiveSetup'])
        ->name('onboarding.discard');

    // Persist current wizard progress as a resumable draft
    Route::post('/onboarding/save-draft', [\App\Http\Controllers\CompanySetupController::class, 'saveSetupAsDraft'])
        ->name('onboarding.save-draft');
});

// 3. Root route
Route::get('/', function () {
    // If user is completely logged out (Guest), force send them to login page
    if (!Auth::check()) {
        return redirect()->to('/login');
    }
    
    // If user has not completed onboarding, push them to the company-setup setup
    if (!auth()->user()->onboarding_completed) {
        return redirect()->route('company.setup');
    }
    
    // If fully onboarded, serve the SPA application dashboard
    return view('app');
});

// Switch Company Route (Web)
Route::get('/company/switch/{id}', function ($id) {
    if (!Auth::check()) {
        return redirect()->to('/login');
    }

    $user = Auth::user();

    // Verify user belongs to requested company
    if (!$user->companies()->where('company_id', $id)->exists()) {
        abort(403, 'Unauthorized action.');
    }

    $user->current_company_id = $id;
    $user->save();

    // Re-fetch company to update session settings
    $company = $user->companies()->where('company_id', $id)->first();
    Session::put('app_currency', $company->base_currency);

    // Flash success using standard session flash
    Session::flash('success', 'Workspace successfully switched to ' . $company->company_name);

    return redirect()->to('/');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/company-setup',
        [\App\Http\Controllers\CompanySetupController::class, 'index'])
        ->name('company.setup');

    Route::post('/onboarding/abort-registration',
        [\App\Http\Controllers\CompanySetupController::class, 'abortRegistration'])
        ->name('onboarding.abort-registration');

    Route::post('/onboarding/save-draft',
        [\App\Http\Controllers\CompanySetupController::class, 'saveSetupAsDraft'])
        ->name('onboarding.save-draft');

});

// New Company Initiation — stamps a session flag then sends the user into the wizard.
// Using a dedicated route (vs. a query param) means the flag survives browser-level
// redirects and Livewire AJAX roundtrips without polluting the URL permanently.
Route::get('/initiate-new-company', function () {
    if (!Auth::check()) {
        return redirect()->to('/login');
    }
    Session::put('creating_subsequent_company', true);
    return redirect()->to('/company-setup?start_fresh_flow=true');
})->name('company.initiate-new');

// 4. Vue.js SPA Catch-all
Route::fallback(function () {
    return view('app');
});
