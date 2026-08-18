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
        Schema::table('invoice_purchase_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_purchase_settings', 'sale_invoice_template')) {
                $table->string('sale_invoice_template')->default('classic')->after('show_item_wholesale_toggle');
            }
            if (!Schema::hasColumn('invoice_purchase_settings', 'thermal_receipt_template')) {
                $table->string('thermal_receipt_template')->default('classic')->after('sale_invoice_template');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_purchase_settings', function (Blueprint $table) {
            $table->dropColumn(['sale_invoice_template', 'thermal_receipt_template']);
        });
    }
};
