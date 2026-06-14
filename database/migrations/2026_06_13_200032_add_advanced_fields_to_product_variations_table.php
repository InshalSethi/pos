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
        Schema::table('product_variations', function (Blueprint $table) {
            $table->string('barcode')->nullable()->after('sku');
            $table->decimal('tax_rate', 5, 2)->nullable()->after('sale_price');
            $table->string('discount_type')->nullable()->after('tax_rate');
            $table->decimal('discount_value', 10, 2)->nullable()->after('discount_type');
            $table->string('unit_of_measure')->nullable()->after('min_stock_alert');
            $table->unsignedBigInteger('supplier_id')->nullable()->after('unit_of_measure');
            $table->string('batch_number')->nullable()->after('supplier_id');
            $table->date('expiry_date')->nullable()->after('batch_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropColumn([
                'barcode',
                'tax_rate',
                'discount_type',
                'discount_value',
                'unit_of_measure',
                'supplier_id',
                'batch_number',
                'expiry_date'
            ]);
        });
    }
};
