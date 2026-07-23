<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'accepted',
        ]);

        return DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'onboarding_completed' => false,
            ]);

            if (\Spatie\Permission\Models\Permission::where('guard_name', 'web')->count() === 0) {
                (new \Database\Seeders\RolePermissionSeeder())->run();
            }

            $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
            $adminRole->syncPermissions(\Spatie\Permission\Models\Permission::where('guard_name', 'web')->get());
            $user->assignRole($adminRole);

            UserSettings::create([
                'user_id' => $user->id,
                'email_notifications' => true,
                'sales_alerts' => false,
                'low_stock_alerts' => false,
                'theme' => 'light',
                'items_per_page' => 15,
                'default_payment_method' => 'cash',
                'auto_print_receipts' => false,
                'sound_effects' => true,
                'session_timeout' => 60,
                'two_factor_auth' => false,
            ]);

            Auth::login($user);

            return redirect()->route('company.setup');
        });
    }
}
