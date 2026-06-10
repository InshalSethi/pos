<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $tables = [
        'products',
        'sales',
        'inventory_adjustments',
        'journal_entries',
        'bank_transactions',
        'purchase_orders',
        'expenses',
        'customers',
        'suppliers',
        'employees'
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
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
                Schema::table($tableName, function (Blueprint $table) {
                    if (Schema::hasColumn($table->getTable(), 'company_id')) {
                        $table->dropColumn('company_id');
                    }
                });
            }
        }
    }
};
