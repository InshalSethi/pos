<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Api\GoogleAuthController;

// 1. Google Auth Routes (Inhein uupar hona chahiye)
Route::get('/auth/google/redirect', [GoogleAuthController::class , 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class , 'handleGoogleCallback']);
Route::match(['get', 'post'], '/company-setup/cancel', [GoogleAuthController::class, 'cancelSetup'])
    ->name('company.setup.cancel');

// Magic Sync Route to bridge SPA Token -> Web Session
Route::get('/sync-session', [\App\Http\Controllers\Api\AuthController::class, 'syncSession'])
    ->middleware(['web', 'auth:sanctum']);

// 2. Password reset routes
Route::get('/reset-password/{token}', function (string $token) {
    return view('app', ['token' => $token]);
})->middleware('guest')->name('password.reset');

// Authentication & Registration routes
Route::get('/login', function (\Illuminate\Http\Request $request) {
    if ($request->has('token')) {
        return view('app');
    }
    if (Auth::check()) {
        $user = Auth::user();
        if (is_null($user->company_id) || !$user->is_setup_completed) {
            return redirect()->route('company.setup');
        }
        return redirect()->to('/');
    }
    return view('app');
})->name('login');

Route::get('/register', function (\Illuminate\Http\Request $request) {
    if ($request->has('token')) {
        return view('app');
    }
    if (Auth::check()) {
        $user = Auth::user();
        if (is_null($user->company_id) || !$user->is_setup_completed) {
            return redirect()->route('company.setup');
        }
        return redirect()->to('/');
    }
    return view('app');
})->name('register');
Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register'])->middleware('guest');

// Protected routes using auth and EnsureCompanySetup middleware
Route::middleware(['auth', \App\Http\Middleware\EnsureCompanySetup::class])->group(function () {
    // Wizard entry point — handles fresh start and draft resume via query params
    Route::get('/company-setup', [\App\Http\Controllers\CompanySetupController::class, 'index'])
        ->name('company.setup');

    // Permanently delete a specific draft by ID
    Route::delete('/onboarding/draft/purge/{id}', [\App\Http\Controllers\CompanySetupController::class, 'purgeIndividualDraft'])
        ->name('onboarding.purge-individual-draft');

    // Permanently bulk delete selected draft workspaces
    Route::delete('/onboarding/drafts/bulk-purge', [\App\Http\Controllers\CompanySetupController::class, 'purgeBulkDrafts'])
        ->name('onboarding.purge-bulk-drafts');

    // Permanently delete a draft-only company record
    Route::post('/onboarding/discard', [\App\Http\Controllers\CompanySetupController::class, 'discardActiveSetup'])
        ->name('onboarding.discard');

    // Persist current wizard progress as a resumable draft
    Route::post('/onboarding/save-draft', [\App\Http\Controllers\CompanySetupController::class, 'saveSetupAsDraft'])
        ->name('onboarding.save-draft');

    // Soft delete a company workspace (AJAX-compatible)
    Route::delete('/company/{id}/destroy', [\App\Http\Controllers\CompanySetupController::class, 'destroyCompany'])
        ->name('company.destroy');

    // 3. Root route
    Route::get('/', function () {
        return view('app');
    })->name('dashboard');

    // Switch Company Route (Web)
    Route::get('/company/switch/{id}', function ($id) {
        $user = Auth::user();

        // Verify user belongs to requested company
        if (!$user->companies()->where('companies.id', $id)->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $user->current_company_id = $id;
        $user->save();

        // Re-fetch company to update session settings
        $company = $user->companies()->where('companies.id', $id)->first();
        Session::put('app_currency', $company->base_currency);

        // Flash success using standard session flash
        Session::flash('success', 'Workspace successfully switched to ' . $company->company_name);

        return redirect()->to('/');
    });

    Route::post('/onboarding/abort-registration',
        [\App\Http\Controllers\CompanySetupController::class, 'abortRegistration'])
        ->name('onboarding.abort-registration');

    // New Company Initiation — stamps a session flag then sends the user into the wizard.
    Route::get('/initiate-new-company', function () {
        Session::put('creating_subsequent_company', true);
        return redirect()->to('/company-setup?start_fresh_flow=true');
    })->name('company.initiate-new');

    Route::get('/sales-invoices/{id}/edit', [\App\Http\Controllers\SalesInvoiceController::class, 'edit'])
        ->name('sales.invoices.edit');

});

// 4. Vue.js SPA Catch-all
Route::fallback(function () {
    return view('app');
});

// Admin Routes
require __DIR__.'/admin.php';
