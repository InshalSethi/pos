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
        Schema::table('purchase_returns', function (Blueprint $table) {
            // Make purchase_order_id nullable for standalone returns
            if (Schema::hasColumn('purchase_returns', 'purchase_order_id')) {
                $table->foreignId('purchase_order_id')->nullable()->change();
            }

            if (!Schema::hasColumn('purchase_returns', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0)->after('reason');
            }

            if (!Schema::hasColumn('purchase_returns', 'tax_amount')) {
                $table->decimal('tax_amount', 12, 2)->default(0)->after('subtotal');
            }

            if (!Schema::hasColumn('purchase_returns', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('tax_amount');
            }

            if (!Schema::hasColumn('purchase_returns', 'refund_status')) {
                $table->string('refund_status')->default('pending')->after('status');
            }

            if (!Schema::hasColumn('purchase_returns', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->after('supplier_id')->constrained()->nullOnDelete();
            }
        });

        Schema::table('purchase_return_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_return_items', 'tax_amount')) {
                $table->decimal('tax_amount', 10, 2)->default(0)->after('unit_cost');
            }

            if (!Schema::hasColumn('purchase_return_items', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('tax_amount');
            }

            if (!Schema::hasColumn('purchase_return_items', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0)->after('discount_amount');
            }

            if (!Schema::hasColumn('purchase_return_items', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_returns', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_returns', 'subtotal')) {
                $table->dropColumn(['subtotal', 'tax_amount', 'discount_amount', 'refund_status']);
            }
            if (Schema::hasColumn('purchase_returns', 'warehouse_id')) {
                $table->dropForeign(['warehouse_id']);
                $table->dropColumn('warehouse_id');
            }
        });

        Schema::table('purchase_return_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_return_items', 'tax_amount')) {
                $table->dropColumn(['tax_amount', 'discount_amount', 'subtotal']);
            }
            if (Schema::hasColumn('purchase_return_items', 'warehouse_id')) {
                $table->dropForeign(['warehouse_id']);
                $table->dropColumn('warehouse_id');
            }
        });
    }
};
