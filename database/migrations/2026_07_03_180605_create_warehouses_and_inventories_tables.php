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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id']);
        });

        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_variation_id')->nullable()->constrained('product_variations')->onDelete('cascade');
            $table->integer('stock_qty')->default(0);
            $table->integer('min_stock_level')->default(0);
            $table->timestamps();

            $table->index(['company_id']);
            $table->index(['warehouse_id']);
            $table->index(['product_id']);
            $table->index(['product_variation_id']);
            $table->unique(['warehouse_id', 'product_id', 'product_variation_id'], 'warehouse_product_variant_unique');
        });

        Schema::create('transfer_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->string('transfer_number')->unique();
            $table->foreignId('source_warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('destination_warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->date('transfer_date');
            $table->enum('status', ['draft', 'sent', 'received', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['company_id']);
            $table->index(['source_warehouse_id']);
            $table->index(['destination_warehouse_id']);
        });

        Schema::create('transfer_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_order_id')->constrained('transfer_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_variation_id')->nullable()->constrained('product_variations')->onDelete('cascade');
            $table->integer('quantity');
            $table->timestamps();

            $table->index(['transfer_order_id']);
            $table->index(['product_id']);
            $table->index(['product_variation_id']);
        });

        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('product_id')->constrained('warehouses')->onDelete('set null');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('customer_id')->constrained('warehouses')->onDelete('set null');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('supplier_id')->constrained('warehouses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });

        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });

        Schema::dropIfExists('transfer_order_items');
        Schema::dropIfExists('transfer_orders');
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('warehouses');
    }
};
