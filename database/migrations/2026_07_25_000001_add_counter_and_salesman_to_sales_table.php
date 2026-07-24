<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'counter_id')) {
                $table->foreignId('counter_id')->nullable()->after('warehouse_id')->constrained('counters')->onDelete('set null');
            }
            if (!Schema::hasColumn('sales', 'salesman_id')) {
                $table->foreignId('salesman_id')->nullable()->after('counter_id')->constrained('employees')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'counter_id')) {
                $table->dropForeign(['counter_id']);
                $table->dropColumn('counter_id');
            }
            if (Schema::hasColumn('sales', 'salesman_id')) {
                $table->dropForeign(['salesman_id']);
                $table->dropColumn('salesman_id');
            }
        });
    }
};
