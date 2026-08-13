<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with essential initial configurations.
     */
    public function run(): void
    {
        $this->command->info('Starting database seeding...');

        // 1. Seed roles and permissions
        $this->command->info('Seeding roles and permissions...');
        $this->call(RolePermissionSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(BusinessTypeSeeder::class);

        // 2. Create default admin user
        $this->command->info('Creating default admin user...');
        $admin = User::firstOrCreate([
            'email' => 'admin@gmail.com'
        ], [
            'name' => 'Admin User',
            'password' => Hash::make('password123'),
            'onboarding_completed' => true,
        ]);

        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // 3. Link admin user to sample company
        $this->call(SampleCompanySeeder::class);

        // 4. Seed system accounts and global currencies
        $this->command->info('Seeding system accounts and currencies...');
        $this->call(SystemAccountsSeeder::class);
        $this->call(CurrencySeeder::class);

        // 5. Seed chart of accounts, default bank account, and default warehouse
        $this->command->info('Seeding chart of accounts, default cash account, and default warehouse...');
        $this->call(ChartOfAccountsSeeder::class);
        $this->call(DefaultBankAccountSeeder::class);
        $this->call(DefaultWarehouseSeeder::class);

        // 6. Seed departments and positions
        $this->command->info('Seeding departments and positions...');
        $this->call(DepartmentPositionSeeder::class);

        // 7. Seed categories
        $this->command->info('Seeding categories...');
        $this->call(CategorySeeder::class);

        $this->command->info('Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('Default Admin Credentials:');
        $this->command->info('Email: admin@gmail.com');
        $this->command->info('Password: password123');
    }
}
