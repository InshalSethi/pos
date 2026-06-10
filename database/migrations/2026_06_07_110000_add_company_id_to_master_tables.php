<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $tables = [
        'categories',
        'expense_categories',
        'departments',
        'positions',
        'chart_of_accounts',
        'roles',
        'payment_settings',
        'accounting_settings',
        'bank_accounts',
        'employee_salaries',
        'payroll_records',
        'salary_adjustments',
        'purchase_invoices',
        'payment_receipts',
        'payments'
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($table->getTable(), 'company_id')) {
                        $table->unsignedBigInteger('company_id')->nullable()->index();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($table->getTable(), 'company_id')) {
                        $table->dropColumn('company_id');
                    }
                });
            }
        }
    }
};
