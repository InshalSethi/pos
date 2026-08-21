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
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users',
            'password'   => 'required|string|min:8|confirmed',
            'terms'      => 'accepted',
        ];

        if ($request->has('name') && !$request->has('first_name') && !$request->has('last_name')) {
            $rules['name'] = 'required|string|max:255';
            unset($rules['first_name'], $rules['last_name']);
        }

        $request->validate($rules);

        $fullName = trim($request->input('first_name', '') . ' ' . $request->input('last_name', ''));
        if (empty($fullName)) {
            $fullName = trim($request->input('name', ''));
        }

        return DB::transaction(function () use ($request, $fullName) {
            $user = User::create([
                'name' => $fullName,
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

            return redirect('/owner/companies');
        });
    }
}
