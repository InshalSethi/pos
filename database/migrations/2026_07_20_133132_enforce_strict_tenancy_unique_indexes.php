<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add company_id to purchase_returns if not exists
        if (Schema::hasTable('purchase_returns')) {
            Schema::table('purchase_returns', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_returns', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
                }
            });

            // Backfill company_id from purchase_orders
            if (Schema::hasTable('purchase_orders')) {
                if (DB::getDriverName() === 'sqlite') {
                    DB::statement('
                        UPDATE purchase_returns 
                        SET company_id = (SELECT company_id FROM purchase_orders WHERE purchase_orders.id = purchase_returns.purchase_order_id)
                        WHERE company_id IS NULL
                    ');
                } else {
                    DB::statement('
                        UPDATE purchase_returns pr 
                        JOIN purchase_orders po ON pr.purchase_order_id = po.id 
                        SET pr.company_id = po.company_id 
                        WHERE pr.company_id IS NULL
                    ');
                }
            }
        }

        // Helper to safely drop unique index if exists
        $dropUniqueSafely = function (string $tableName, string $indexName) {
            try {
                Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                    $table->dropUnique($indexName);
                });
            } catch (\Throwable $e) {
                // Index may not exist or already dropped
            }
        };

        // Helper to safely add unique index if not exists
        $addUniqueSafely = function (string $tableName, array $columns, string $indexName) {
            try {
                Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
                    $table->unique($columns, $indexName);
                });
            } catch (\Throwable $e) {
                // Key may already exist
            }
        };

        // 2. Drop simple unique constraints and add composite unique constraints

        // Products
        $dropUniqueSafely('products', 'products_sku_unique');
        $dropUniqueSafely('products', 'products_barcode_unique');
        $addUniqueSafely('products', ['company_id', 'sku'], 'uq_products_company_sku');
        $addUniqueSafely('products', ['company_id', 'barcode'], 'uq_products_company_barcode');

        // Sales
        $dropUniqueSafely('sales', 'sales_sale_number_unique');
        $addUniqueSafely('sales', ['company_id', 'sale_number'], 'uq_sales_company_sale_number');

        // Chart of Accounts
        $dropUniqueSafely('chart_of_accounts', 'chart_of_accounts_account_code_unique');
        $addUniqueSafely('chart_of_accounts', ['company_id', 'account_code'], 'uq_accounts_company_account_code');

        // Journal Entries
        $dropUniqueSafely('journal_entries', 'journal_entries_entry_number_unique');
        $addUniqueSafely('journal_entries', ['company_id', 'entry_number'], 'uq_journal_entries_company_entry_number');

        // Inventory Adjustments
        $dropUniqueSafely('inventory_adjustments', 'inventory_adjustments_adjustment_number_unique');
        $addUniqueSafely('inventory_adjustments', ['company_id', 'adjustment_number'], 'uq_adjustments_company_adjustment_number');

        // Purchase Orders
        $dropUniqueSafely('purchase_orders', 'purchase_orders_po_number_unique');
        $addUniqueSafely('purchase_orders', ['company_id', 'po_number'], 'uq_purchase_orders_company_po_number');

        // Purchase Returns
        $dropUniqueSafely('purchase_returns', 'purchase_returns_return_number_unique');
        $addUniqueSafely('purchase_returns', ['company_id', 'return_number'], 'uq_purchase_returns_company_return_number');

        // Expense Categories
        $dropUniqueSafely('expense_categories', 'expense_categories_code_unique');
        $addUniqueSafely('expense_categories', ['company_id', 'code'], 'uq_expense_categories_company_code');

        // Expenses
        $dropUniqueSafely('expenses', 'expenses_expense_number_unique');
        $addUniqueSafely('expenses', ['company_id', 'expense_number'], 'uq_expenses_company_expense_number');

        // Employees
        $dropUniqueSafely('employees', 'employees_email_unique');
        $dropUniqueSafely('employees', 'employees_employee_number_unique');
        $addUniqueSafely('employees', ['company_id', 'employee_number'], 'uq_employees_company_employee_number');
        $addUniqueSafely('employees', ['company_id', 'email'], 'uq_employees_company_email');

        // Departments
        $dropUniqueSafely('departments', 'departments_code_unique');
        $addUniqueSafely('departments', ['company_id', 'code'], 'uq_departments_company_code');

        // Positions
        $dropUniqueSafely('positions', 'positions_code_unique');
        $addUniqueSafely('positions', ['company_id', 'code'], 'uq_positions_company_code');

        // Payroll Records
        $dropUniqueSafely('payroll_records', 'payroll_records_payroll_number_unique');
        $addUniqueSafely('payroll_records', ['company_id', 'payroll_number'], 'uq_payroll_records_company_payroll_number');

        // Salary Adjustments
        $dropUniqueSafely('salary_adjustments', 'salary_adjustments_adjustment_number_unique');
        $addUniqueSafely('salary_adjustments', ['company_id', 'adjustment_number'], 'uq_salary_adjustments_company_adj_number');

        // Payments
        $dropUniqueSafely('payments', 'payments_payment_number_unique');
        $addUniqueSafely('payments', ['company_id', 'payment_number'], 'uq_payments_company_payment_number');

        // Payment Receipts
        $dropUniqueSafely('payment_receipts', 'payment_receipts_receipt_number_unique');
        $addUniqueSafely('payment_receipts', ['company_id', 'receipt_number'], 'uq_payment_receipts_company_rec_number');

        // Product Variations
        $dropUniqueSafely('product_variations', 'product_variations_sku_unique');
        $addUniqueSafely('product_variations', ['company_id', 'sku'], 'uq_product_variations_company_sku');

        // Transfer Orders
        $dropUniqueSafely('transfer_orders', 'transfer_orders_transfer_number_unique');
        $addUniqueSafely('transfer_orders', ['company_id', 'transfer_number'], 'uq_transfer_orders_company_transfer_number');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse Transfer Orders
        Schema::table('transfer_orders', function (Blueprint $table) {
            $table->dropUnique('uq_transfer_orders_company_transfer_number');
            $table->unique('transfer_number', 'transfer_orders_transfer_number_unique');
        });

        // Reverse Product Variations
        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropUnique('uq_product_variations_company_sku');
            $table->unique('sku', 'product_variations_sku_unique');
        });

        // Reverse Payment Receipts
        Schema::table('payment_receipts', function (Blueprint $table) {
            $table->dropUnique('uq_payment_receipts_company_rec_number');
            $table->unique('receipt_number', 'payment_receipts_receipt_number_unique');
        });

        // Reverse Payments
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('uq_payments_company_payment_number');
            $table->unique('payment_number', 'payments_payment_number_unique');
        });

        // Reverse Salary Adjustments
        Schema::table('salary_adjustments', function (Blueprint $table) {
            $table->dropUnique('uq_salary_adjustments_company_adj_number');
            $table->unique('adjustment_number', 'salary_adjustments_adjustment_number_unique');
        });

        // Reverse Payroll Records
        Schema::table('payroll_records', function (Blueprint $table) {
            $table->dropUnique('uq_payroll_records_company_payroll_number');
            $table->unique('payroll_number', 'payroll_records_payroll_number_unique');
        });

        // Reverse Positions
        Schema::table('positions', function (Blueprint $table) {
            $table->dropUnique('uq_positions_company_code');
            $table->unique('code', 'positions_code_unique');
        });

        // Reverse Departments
        Schema::table('departments', function (Blueprint $table) {
            $table->dropUnique('uq_departments_company_code');
            $table->unique('code', 'departments_code_unique');
        });

        // Reverse Employees
        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique('uq_employees_company_email');
            $table->dropUnique('uq_employees_company_employee_number');
            $table->unique('employee_number', 'employees_employee_number_unique');
            $table->unique('email', 'employees_email_unique');
        });

        // Reverse Expenses
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropUnique('uq_expenses_company_expense_number');
            $table->unique('expense_number', 'expenses_expense_number_unique');
        });

        // Reverse Expense Categories
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->dropUnique('uq_expense_categories_company_code');
            $table->unique('code', 'expense_categories_code_unique');
        });

        // Reverse Purchase Returns
        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->dropUnique('uq_purchase_returns_company_return_number');
            $table->unique('return_number', 'purchase_returns_return_number_unique');
            $table->dropColumn('company_id');
        });

        // Reverse Purchase Orders
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropUnique('uq_purchase_orders_company_po_number');
            $table->unique('po_number', 'purchase_orders_po_number_unique');
        });

        // Reverse Inventory Adjustments
        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->dropUnique('uq_adjustments_company_adjustment_number');
            $table->unique('adjustment_number', 'inventory_adjustments_adjustment_number_unique');
        });

        // Reverse Journal Entries
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropUnique('uq_journal_entries_company_entry_number');
            $table->unique('entry_number', 'journal_entries_entry_number_unique');
        });

        // Reverse Chart of Accounts
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->dropUnique('uq_accounts_company_account_code');
            $table->unique('account_code', 'chart_of_accounts_account_code_unique');
        });

        // Reverse Sales
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique('uq_sales_company_sale_number');
            $table->unique('sale_number', 'sales_sale_number_unique');
        });

        // Reverse Products
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('uq_products_company_barcode');
            $table->dropUnique('uq_products_company_sku');
            $table->unique('barcode', 'products_barcode_unique');
            $table->unique('sku', 'products_sku_unique');
        });
    }
};
