<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Starting database seeding...');

        // Seed roles and permissions first
        $this->command->info('Seeding roles and permissions...');
        $this->call(RolePermissionSeeder::class);

        // Seed chart of accounts
        $this->command->info('Seeding chart of accounts...');
        $this->call(ChartOfAccountsSeeder::class);

        // Seed categories first (required for products)
        $this->command->info('Seeding categories...');
        $this->call(CategorySeeder::class);

        // Seed products
        $this->command->info('Seeding products...');
        $this->call(ProductSeeder::class);

        // Seed departments and positions (required for employees)
        $this->command->info('Seeding departments and positions...');
        $this->call(DepartmentPositionSeeder::class);

        // Create admin user first (required for other seeders)
        $this->command->info('Creating admin user...');
        $admin = User::firstOrCreate([
            'email' => 'admin@example.com'
        ], [
            'name' => 'Admin User',
            'password' => Hash::make('password123'),
        ]);

        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Create test user
        $this->command->info('Creating test user...');
        $user = User::firstOrCreate([
            'email' => 'test@example.com'
        ], [
            'name' => 'Test User',
            'password' => Hash::make('password123'),
        ]);

        if (!$user->hasRole('user')) {
            $user->assignRole('user');
        }

        // Seed employees (requires users, departments, positions)
        $this->command->info('Seeding employees...');
        $this->call(EmployeeSeeder::class);

        // Seed customers and suppliers
        $this->command->info('Seeding customers...');
        $this->call(CustomerSeeder::class);

        $this->command->info('Seeding suppliers...');
        $this->call(SupplierSeeder::class);

        // Seed payroll data (requires employees)
        $this->command->info('Seeding payroll data...');
        $this->call(PayrollSeeder::class);

        // Seed sales data (requires customers, products, users)
        $this->command->info('Seeding sales data...');
        $this->call(SalesSeeder::class);

        // Create additional test users
        $this->command->info('Creating additional test users...');
        $this->call(TestUserSeeder::class);

        $this->command->info('Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info('Admin: admin@example.com / password123');
        $this->command->info('Test User: test@example.com / password123');
        $this->command->info('Manager: manager@test.com / password123');
        $this->command->info('Admin Test: admin@test.com / password123');
    }
}
