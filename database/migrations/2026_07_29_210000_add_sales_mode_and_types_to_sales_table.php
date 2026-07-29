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
            if (!Schema::hasColumn('sales', 'sales_mode')) {
                $table->string('sales_mode')->default('retail')->after('status');
            }
            if (!Schema::hasColumn('sales', 'tax_type')) {
                $table->string('tax_type')->default('percentage')->after('tax_amount');
            }
            if (!Schema::hasColumn('sales', 'discount_type')) {
                $table->string('discount_type')->default('percentage')->after('discount_amount');
            }
        });

        Schema::table('sale_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_items', 'discount_type')) {
                $table->string('discount_type')->default('percentage')->after('discount_amount');
            }
            if (!Schema::hasColumn('sale_items', 'is_wholesale')) {
                $table->boolean('is_wholesale')->default(false)->after('unit_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'sales_mode')) {
                $table->dropColumn(['sales_mode', 'tax_type', 'discount_type']);
            }
        });

        Schema::table('sale_items', function (Blueprint $table) {
            if (Schema::hasColumn('sale_items', 'discount_type')) {
                $table->dropColumn(['discount_type', 'is_wholesale']);
            }
        });
    }
};
