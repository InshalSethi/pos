<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AccountingPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create accounting permissions
        $accountingPermissions = [
            'accounting.view',
            'accounting.create',
            'accounting.edit',
            'accounting.delete',
        ];

        foreach ($accountingPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create reports permissions
        $reportsPermissions = [
            'reports.view',
            'reports.create',
            'reports.export',
        ];

        foreach ($reportsPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign all permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $allPermissions = array_merge($accountingPermissions, $reportsPermissions);
            $adminRole->syncPermissions($allPermissions);
        }

        // Assign view permissions to manager role
        $managerRole = Role::where('name', 'manager')->first();
        if ($managerRole) {
            $existingPermissions = $managerRole->permissions->pluck('name')->toArray();
            $newPermissions = array_merge($existingPermissions, [
                'accounting.view',
                'accounting.create',
                'accounting.edit',
                'reports.view',
                'reports.export',
            ]);
            $managerRole->syncPermissions(array_unique($newPermissions));
        }

        // Assign limited permissions to cashier role
        $cashierRole = Role::where('name', 'cashier')->first();
        if ($cashierRole) {
            $existingPermissions = $cashierRole->permissions->pluck('name')->toArray();
            $newPermissions = array_merge($existingPermissions, [
                'reports.view',
            ]);
            $cashierRole->syncPermissions(array_unique($newPermissions));
        }
    }
}
