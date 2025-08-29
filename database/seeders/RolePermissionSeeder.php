<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if roles and permissions already exist
        if (Role::count() > 0 || Permission::count() > 0) {
            $this->command->info('Roles and permissions already exist. Skipping role/permission seeding.');
            return;
        }

        // Create permissions
        $permissions = [
            // POS permissions
            'pos.access',
            'pos.create_sale',
            'pos.refund',

            // Product permissions
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'products.import',
            'products.export',

            // Sales permissions
            'sales.view',
            'sales.create',
            'sales.edit',
            'sales.delete',
            'sales.refund',

            // Purchase permissions
            'purchases.view',
            'purchases.create',
            'purchases.edit',
            'purchases.delete',
            'purchases.approve',

            // User permissions
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Role permissions
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            // Inventory permissions
            'inventory.view',
            'inventory.create',
            'inventory.edit',
            'inventory.delete',
            'inventory.adjust',

            // Accounting permissions
            'accounting.view',
            'accounting.create',
            'accounting.edit',
            'accounting.delete',
            'accounting.reconcile',

            // Chart of Accounts permissions
            'chart_accounts.view',
            'chart_accounts.create',
            'chart_accounts.edit',
            'chart_accounts.delete',

            // Journal Entry permissions
            'journal_entries.view',
            'journal_entries.create',
            'journal_entries.edit',
            'journal_entries.delete',
            'journal_entries.post',

            // Banking permissions
            'banking.view',
            'banking.create',
            'banking.edit',
            'banking.delete',
            'banking.reconcile',

            // Expense permissions
            'expenses.view',
            'expenses.create',
            'expenses.edit',
            'expenses.delete',
            'expenses.approve',
            'expenses.pay',

            // Employee permissions
            'employees.view',
            'employees.create',
            'employees.edit',
            'employees.delete',

            // Reports permissions
            'reports.view',
            'reports.sales',
            'reports.inventory',
            'reports.financial',
            'reports.export',

            // Customer permissions
            'customers.view',
            'customers.create',
            'customers.edit',
            'customers.delete',

            // Supplier permissions
            'suppliers.view',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',

            // Settings permissions
            'settings.view',
            'settings.manage',
            'settings.payment_gateways',

            // System permissions
            'system.settings',
            'system.backup',
            'system.notifications',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $cashierRole = Role::firstOrCreate(['name' => 'cashier']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Assign permissions to roles (sync to avoid duplicates)
        $adminRole->syncPermissions(Permission::all());

        $managerRole->syncPermissions([
            // POS & Sales
            'pos.access', 'pos.create_sale', 'pos.refund',
            'sales.view', 'sales.create', 'sales.edit', 'sales.refund',

            // Products & Inventory
            'products.view', 'products.create', 'products.edit', 'products.import', 'products.export',
            'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.adjust',

            // Purchases
            'purchases.view', 'purchases.create', 'purchases.edit', 'purchases.approve',

            // Customers & Suppliers
            'customers.view', 'customers.create', 'customers.edit',
            'suppliers.view', 'suppliers.create', 'suppliers.edit',

            // Accounting (limited)
            'accounting.view', 'chart_accounts.view', 'journal_entries.view',
            'banking.view',

            // Expenses
            'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.approve', 'expenses.pay',

            // Employees
            'employees.view', 'employees.create', 'employees.edit',

            // Reports
            'reports.view', 'reports.sales', 'reports.inventory', 'reports.financial', 'reports.export',

            // Settings (limited)
            'settings.view',
        ]);

        $cashierRole->syncPermissions([
            // POS & Sales
            'pos.access', 'pos.create_sale', 'pos.refund',
            'sales.view', 'sales.create',

            // Products (view only)
            'products.view',

            // Customers
            'customers.view', 'customers.create', 'customers.edit',

            // Inventory (view only)
            'inventory.view',

            // Reports (limited)
            'reports.view', 'reports.sales',
        ]);

        // Employee role - basic employee access
        $employeeRole->syncPermissions([
            // Expense management
            'expenses.view', 'expenses.create',

            // Basic product viewing
            'products.view',

            // Basic inventory viewing
            'inventory.view',

            // Basic reports
            'reports.view',
        ]);

        $userRole->syncPermissions([
            // Basic POS access
            'pos.access',

            // View only permissions
            'products.view',
            'inventory.view',
            'sales.view',
            'customers.view',
        ]);
    }
}
