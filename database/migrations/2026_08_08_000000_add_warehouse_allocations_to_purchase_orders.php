<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'warehouse_ids')) {
                $table->json('warehouse_ids')->nullable()->after('warehouse_id');
            }
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_order_items', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->after('product_id')->constrained('warehouses')->onDelete('set null');
            }
            if (!Schema::hasColumn('purchase_order_items', 'warehouse_allocations')) {
                $table->json('warehouse_allocations')->nullable()->after('warehouse_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'warehouse_ids')) {
                $table->dropColumn('warehouse_ids');
            }
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_order_items', 'warehouse_allocations')) {
                $table->dropColumn('warehouse_allocations');
            }
            if (Schema::hasColumn('purchase_order_items', 'warehouse_id')) {
                $table->dropForeign(['warehouse_id']);
                $table->dropColumn('warehouse_id');
            }
        });
    }
};
