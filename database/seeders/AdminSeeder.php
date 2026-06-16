<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Default Admin Permissions
        $permissions = [
            'view dashboard',
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'admin']);
        }

        // Create Roles
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'admin']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'admin']);

        // Give all permissions to Super Admin
        $superAdminRole->syncPermissions(Permission::where('guard_name', 'admin')->get());

        // Give specific permissions to regular Admin
        $adminRole->syncPermissions([
            'view dashboard',
            'view users',
            'create users',
            'edit users',
            // Omitted delete permissions and role management for basic admin
        ]);

        // Create a default Super Admin User
        $adminUser = Admin::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'phone' => '1234567890',
                'is_active' => true,
            ]
        );

        // Assign Role
        if (!$adminUser->hasRole('Super Admin')) {
            $adminUser->assignRole('Super Admin');
        }
    }
}
