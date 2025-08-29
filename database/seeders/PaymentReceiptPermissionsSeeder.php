<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PaymentReceiptPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create payment receipt permissions
        $permissions = [
            'payment_receipts.view' => 'View payment receipts',
            'payment_receipts.create' => 'Create payment receipts',
            'payment_receipts.edit' => 'Edit payment receipts',
            'payment_receipts.verify' => 'Verify payment receipts',
            'payment_receipts.deposit' => 'Mark receipts as deposited',
            'payment_receipts.delete' => 'Delete payment receipts',
            'payment_receipts.cancel' => 'Cancel payment receipts',
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

        // Assign permissions to manager role
        $managerRole = Role::where('name', 'manager')->first();
        if ($managerRole) {
            $managerRole->givePermissionTo([
                'payment_receipts.view',
                'payment_receipts.create',
                'payment_receipts.edit',
                'payment_receipts.verify',
                'payment_receipts.deposit'
            ]);
        }

        // Assign view permission to employee role
        $employeeRole = Role::where('name', 'employee')->first();
        if ($employeeRole) {
            $employeeRole->givePermissionTo(['payment_receipts.view']);
        }

        $this->command->info('Payment receipt permissions created and assigned successfully.');
    }
}
