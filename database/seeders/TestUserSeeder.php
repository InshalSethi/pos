<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserSettings;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if test users already exist
        if (User::whereIn('email', ['admin@test.com', 'manager@test.com'])->exists()) {
            $this->command->info('Test users already exist. Skipping test user seeding.');
            return;
        }

        // Create a test admin user
        $adminUser = User::firstOrCreate([
            'email' => 'admin@test.com'
        ], [
            'name' => 'Test Admin',
            'password' => Hash::make('password123'),
        ]);

        // Assign admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && !$adminUser->hasRole('admin')) {
            $adminUser->assignRole($adminRole);
        }

        // Create user settings
        UserSettings::firstOrCreate([
            'user_id' => $adminUser->id
        ], [
            'email_notifications' => true,
            'sales_alerts' => true,
            'low_stock_alerts' => true,
            'theme' => 'light',
            'items_per_page' => 15,
            'default_payment_method' => 'cash',
            'auto_print_receipts' => false,
            'sound_effects' => true,
            'session_timeout' => 60,
            'two_factor_auth' => false,
        ]);

        // Create a test manager user
        $managerUser = User::firstOrCreate([
            'email' => 'manager@test.com'
        ], [
            'name' => 'Test Manager',
            'password' => Hash::make('password123'),
        ]);

        // Assign manager role
        $managerRole = Role::where('name', 'manager')->first();
        if ($managerRole && !$managerUser->hasRole('manager')) {
            $managerUser->assignRole($managerRole);
        }

        // Create user settings for manager
        UserSettings::firstOrCreate([
            'user_id' => $managerUser->id
        ], [
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

        echo "Created test users:\n";
        echo "Admin: admin@test.com / password123\n";
        echo "Manager: manager@test.com / password123\n";
    }
}
