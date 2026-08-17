<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * List of all core system entity tables to equip with softDeletes.
     */
    protected array $entityTables = [
        'products',
        'product_variations',
        'product_attributes',
        'units',
        'suppliers',
        'customers',
        'purchase_orders',
        'purchase_order_items',
        'purchase_returns',
        'purchase_return_items',
        'sales',
        'sale_items',
        'expenses',
        'expense_categories',
        'inventories',
        'inventory_adjustments',
        'journal_entries',
        'journal_entry_lines',
        'chart_of_accounts',
        'bank_accounts',
        'bank_transactions',
        'payments',
        'payment_receipts',
        'payroll_records',
        'employees',
        'employee_salaries',
        'salary_adjustments',
        'departments',
        'positions',
        'users',
        'tasks',
        'task_boards',
        'task_columns',
        'task_comments',
        'task_attachments',
        'warehouses',
        'transfer_orders',
        'transfer_order_items',
        'taxes',
        'tags',
        'attributes',
        'attribute_values',
        'transactions',
        'accounting_settings',
        'invoice_purchase_settings',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->entityTables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->entityTables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
