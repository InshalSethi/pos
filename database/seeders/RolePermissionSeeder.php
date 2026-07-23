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
        // Clear cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
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

            // Taxes permissions
            'taxes.view',
            'taxes.create',
            'taxes.edit',
            'taxes.delete',

            // System permissions
            'system.settings',
            'system.backup',
            'system.notifications',

            // Payment permissions
            'payments.view',
            'payments.create',
            'payments.edit',
            'payments.delete',
            'payments.approve',
            'payments.pay',

            // Payment Receipt permissions
            'payment_receipts.view',
            'payment_receipts.create',
            'payment_receipts.edit',
            'payment_receipts.verify',
            'payment_receipts.deposit',
            'payment_receipts.delete',
            'payment_receipts.cancel',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $subAdminRole = Role::firstOrCreate(['name' => 'sub-admin']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $cashierRole = Role::firstOrCreate(['name' => 'cashier']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Assign permissions to roles (sync to avoid duplicates)
        $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        // Sub-admin has same permissions as manager
        $subAdminRole->syncPermissions([
            // Sales
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

            // Taxes
            'taxes.view', 'taxes.create', 'taxes.edit', 'taxes.delete',

            // Payments
            'payments.view', 'payments.create', 'payments.edit', 'payments.approve', 'payments.pay',
            'payment_receipts.view', 'payment_receipts.create', 'payment_receipts.edit', 'payment_receipts.verify', 'payment_receipts.deposit',
        ]);

        $managerRole->syncPermissions([
            // Sales
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

            // Taxes
            'taxes.view', 'taxes.create', 'taxes.edit', 'taxes.delete',

            // Payments
            'payments.view', 'payments.create', 'payments.edit', 'payments.approve', 'payments.pay',
            'payment_receipts.view', 'payment_receipts.create', 'payment_receipts.edit', 'payment_receipts.verify', 'payment_receipts.deposit',
        ]);

        $cashierRole->syncPermissions([
            // Sales
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

            // Payments
            'payments.view',
            'payment_receipts.view',
        ]);

        $userRole->syncPermissions([
            // View only permissions
            'products.view',
            'inventory.view',
            'sales.view',
            'customers.view',
        ]);
    }
}
