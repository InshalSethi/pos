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

        // 2. Drop simple unique constraints and add composite unique constraints

        // Products
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_sku_unique');
            $table->dropUnique('products_barcode_unique');
            $table->unique(['company_id', 'sku'], 'uq_products_company_sku');
            $table->unique(['company_id', 'barcode'], 'uq_products_company_barcode');
        });

        // Sales
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique('sales_sale_number_unique');
            $table->unique(['company_id', 'sale_number'], 'uq_sales_company_sale_number');
        });

        // Chart of Accounts
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->dropUnique('chart_of_accounts_account_code_unique');
            $table->unique(['company_id', 'account_code'], 'uq_accounts_company_account_code');
        });

        // Journal Entries
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropUnique('journal_entries_entry_number_unique');
            $table->unique(['company_id', 'entry_number'], 'uq_journal_entries_company_entry_number');
        });

        // Inventory Adjustments
        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->dropUnique('inventory_adjustments_adjustment_number_unique');
            $table->unique(['company_id', 'adjustment_number'], 'uq_adjustments_company_adjustment_number');
        });

        // Purchase Orders
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropUnique('purchase_orders_po_number_unique');
            $table->unique(['company_id', 'po_number'], 'uq_purchase_orders_company_po_number');
        });

        // Purchase Returns
        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->dropUnique('purchase_returns_return_number_unique');
            $table->unique(['company_id', 'return_number'], 'uq_purchase_returns_company_return_number');
        });

        // Expense Categories
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->dropUnique('expense_categories_code_unique');
            $table->unique(['company_id', 'code'], 'uq_expense_categories_company_code');
        });

        // Expenses
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropUnique('expenses_expense_number_unique');
            $table->unique(['company_id', 'expense_number'], 'uq_expenses_company_expense_number');
        });

        // Employees
        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique('employees_email_unique');
            $table->dropUnique('employees_employee_number_unique');
            $table->unique(['company_id', 'employee_number'], 'uq_employees_company_employee_number');
            $table->unique(['company_id', 'email'], 'uq_employees_company_email');
        });

        // Departments
        Schema::table('departments', function (Blueprint $table) {
            $table->dropUnique('departments_code_unique');
            $table->unique(['company_id', 'code'], 'uq_departments_company_code');
        });

        // Positions
        Schema::table('positions', function (Blueprint $table) {
            $table->dropUnique('positions_code_unique');
            $table->unique(['company_id', 'code'], 'uq_positions_company_code');
        });

        // Payroll Records
        Schema::table('payroll_records', function (Blueprint $table) {
            $table->dropUnique('payroll_records_payroll_number_unique');
            $table->unique(['company_id', 'payroll_number'], 'uq_payroll_records_company_payroll_number');
        });

        // Salary Adjustments
        Schema::table('salary_adjustments', function (Blueprint $table) {
            $table->dropUnique('salary_adjustments_adjustment_number_unique');
            $table->unique(['company_id', 'adjustment_number'], 'uq_salary_adjustments_company_adj_number');
        });

        // Payments
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('payments_payment_number_unique');
            $table->unique(['company_id', 'payment_number'], 'uq_payments_company_payment_number');
        });

        // Payment Receipts
        Schema::table('payment_receipts', function (Blueprint $table) {
            $table->dropUnique('payment_receipts_receipt_number_unique');
            $table->unique(['company_id', 'receipt_number'], 'uq_payment_receipts_company_rec_number');
        });

        // Product Variations
        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropUnique('product_variations_sku_unique');
            $table->unique(['company_id', 'sku'], 'uq_product_variations_company_sku');
        });

        // Transfer Orders
        Schema::table('transfer_orders', function (Blueprint $table) {
            $table->dropUnique('transfer_orders_transfer_number_unique');
            $table->unique(['company_id', 'transfer_number'], 'uq_transfer_orders_company_transfer_number');
        });
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
