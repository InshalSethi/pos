<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Get user permissions and roles
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        $roles = $user->getRoleNames()->toArray();

        return response()->json([
            'token' => $token,
            'user' => $user,
            'permissions' => $permissions,
            'roles' => $roles,
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign default role (you can customize this)
        $user->assignRole('user');

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Get user permissions and roles
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        $roles = $user->getRoleNames()->toArray();

        return response()->json([
            'token' => $token,
            'user' => $user,
            'permissions' => $permissions,
            'roles' => $roles,
        ], 201);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        
        // Get user permissions and roles
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        $roles = $user->getRoleNames()->toArray();

        return response()->json([
            'user' => $user,
            'permissions' => $permissions,
            'roles' => $roles,
        ]);
    }

    public function logout(Request $request)
    {
        // Revoke the current access token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // If changing password, verify current password
        if ($request->filled('new_password')) {
            if (!$request->filled('current_password') || !Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'Current password is incorrect'
                ], 422);
            }
        }

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('new_password')) {
            $updateData['password'] = Hash::make($request->new_password);
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh()
        ]);
    }

    public function uploadProfileImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Delete old profile image if exists
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        // Store new image
        $path = $request->file('profile_image')->store('profile-images', 'public');

        $user->update(['profile_image' => $path]);

        return response()->json([
            'message' => 'Profile image uploaded successfully',
            'profile_image_url' => asset('storage/' . $path)
        ]);
    }

    public function getSettings(Request $request)
    {
        $user = $request->user();
        $settings = $user->settings;

        if (!$settings) {
            // Create default settings if they don't exist
            $settings = UserSettings::create(['user_id' => $user->id]);
        }

        return response()->json($settings);
    }

    public function updateSettings(Request $request)
    {
        $user = $request->user();
        $settings = $user->settings;

        if (!$settings) {
            $settings = UserSettings::create(['user_id' => $user->id]);
        }

        $validator = Validator::make($request->all(), [
            'email_notifications' => 'boolean',
            'sales_alerts' => 'boolean',
            'low_stock_alerts' => 'boolean',
            'theme' => 'in:light,dark,auto',
            'items_per_page' => 'integer|min:5|max:100',
            'default_payment_method' => 'in:cash,card,digital',
            'auto_print_receipts' => 'boolean',
            'sound_effects' => 'boolean',
            'session_timeout' => 'integer|min:5|max:480',
            'two_factor_auth' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $settings->update($request->only([
            'email_notifications',
            'sales_alerts',
            'low_stock_alerts',
            'theme',
            'items_per_page',
            'default_payment_method',
            'auto_print_receipts',
            'sound_effects',
            'session_timeout',
            'two_factor_auth',
        ]));

        return response()->json([
            'message' => 'Settings updated successfully',
            'settings' => $settings->fresh()
        ]);
    }
}
