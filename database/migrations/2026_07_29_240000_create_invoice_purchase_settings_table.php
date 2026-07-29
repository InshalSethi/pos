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
        Schema::create('invoice_purchase_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            
            // Section A: Invoice Settings (Sales)
            $table->string('invoice_prefix')->default('INV-');
            $table->string('default_pricing_mode')->default('retail'); // 'retail' or 'wholesale'
            $table->integer('default_due_period_days')->default(30);
            $table->text('default_terms_conditions')->nullable();
            $table->boolean('show_item_wholesale_toggle')->default(true);

            // Section B: Purchase Order Settings
            $table->string('po_prefix')->default('PO-');
            $table->foreignId('default_purchase_warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->boolean('auto_update_product_cost')->default(true);

            // Section C: Taxes & Discount Defaults
            $table->json('default_system_tax_ids')->nullable();
            $table->boolean('allow_manual_taxes_discounts')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_purchase_settings');
    }
};
