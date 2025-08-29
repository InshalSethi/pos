<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PaymentPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create payment permissions
        $permissions = [
            'payments.view' => 'View payments',
            'payments.create' => 'Create payments',
            'payments.edit' => 'Edit payments',
            'payments.delete' => 'Delete payments',
            'payments.approve' => 'Approve payments',
            'payments.pay' => 'Mark payments as paid',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        // Assign permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(array_keys($permissions));
        }

        // Assign basic permissions to manager role
        $managerRole = Role::where('name', 'manager')->first();
        if ($managerRole) {
            $managerRole->givePermissionTo([
                'payments.view',
                'payments.create',
                'payments.edit',
                'payments.approve',
                'payments.pay'
            ]);
        }

        // Assign view permission to employee role
        $employeeRole = Role::where('name', 'employee')->first();
        if ($employeeRole) {
            $employeeRole->givePermissionTo(['payments.view']);
        }

        $this->command->info('Payment permissions created and assigned successfully.');
    }
}
