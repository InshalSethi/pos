<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminManagementController;
use Illuminate\Http\Request;

Route::prefix('admin')->name('admin.')->middleware(['web'])->group(function () {
    
    // API Routes for Vue SPA
    Route::prefix('api')->group(function() {
        Route::post('/login', function(Request $request) {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            if (auth()->guard('admin')->attempt($credentials, $request->boolean('remember'))) {
                $user = auth()->guard('admin')->user();
                if (!$user->is_active) {
                    auth()->guard('admin')->logout();
                    return response()->json(['message' => 'Your admin account is currently inactive.'], 403);
                }
                $request->session()->regenerate();
                return response()->json(['user' => $user]);
            }

            return response()->json([
                'message' => 'The provided credentials do not match our records.'
            ], 401);
        });
        
        Route::middleware('auth:admin')->group(function () {
            Route::post('/logout', function(Request $request) {
                auth()->guard('admin')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return response()->json(['message' => 'Logged out successfully']);
            });

            Route::get('/dashboard', function() {
                return response()->json([
                    'stats' => [
                        'total_admins' => \App\Models\Admin::count(),
                        'total_users' => \App\Models\User::count(),
                        'active_users' => \App\Models\User::where('is_active', true)->count(),
                        'new_users_month' => \App\Models\User::whereMonth('created_at', now()->month)->count(),
                    ]
                ]);
            });
            
            // These will still be used by DataTables if needed, or by Vue
            Route::get('/admins-data', [AdminManagementController::class, 'data']);
            Route::get('/users-data', [AdminUserController::class, 'data']);
            Route::get('/roles-data', [AdminRoleController::class, 'data']);
            
            // API Resource endpoints for CRUD forms
            Route::apiResource('admins', AdminManagementController::class);
            Route::apiResource('users', AdminUserController::class);
            Route::apiResource('roles', AdminRoleController::class);

    // Profile Settings
    Route::get('/profile', [AdminProfileController::class, 'show']);
    Route::put('/profile', [AdminProfileController::class, 'update']);

    // System Settings
    Route::get('/settings', [AdminSettingsController::class, 'show']);
    Route::put('/settings', [AdminSettingsController::class, 'update']);
            
            // Options endpoints for dropdowns
            Route::get('/options/roles', function() {
                return response()->json(\Spatie\Permission\Models\Role::where('guard_name', 'admin')->select('id', 'name')->get());
            });
            Route::get('/options/permissions', function() {
                return response()->json(\Spatie\Permission\Models\Permission::where('guard_name', 'admin')->select('id', 'name')->get());
            });
        });
    });

    // Catch-all Route for Vue Router
    Route::get('{any?}', function () {
        return view('admin.app');
    })->where('any', '.*');

});
