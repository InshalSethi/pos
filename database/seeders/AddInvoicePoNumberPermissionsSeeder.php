<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddInvoicePoNumberPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'edit_invoice_number',
            'edit_po_number',
        ];

        foreach (['web', 'admin'] as $guard) {
            foreach ($permissions as $permissionName) {
                Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => $guard]);
            }
        }

        // Assign permissions to admin and owner roles if they exist
        $roles = Role::whereIn('name', ['admin', 'owner', 'Admin', 'Owner'])->get();
        foreach ($roles as $role) {
            $guardPermissions = Permission::where('guard_name', $role->guard_name)->whereIn('name', $permissions)->get();
            $role->givePermissionTo($guardPermissions);
        }
    }
}
