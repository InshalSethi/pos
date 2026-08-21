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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'item_type')) {
                $table->string('item_type')->default('standard')->after('unit_id'); // 'standard', 'raw_material', 'finished_good', 'fixed_asset', 'service'
            }
            if (!Schema::hasColumn('products', 'can_be_sold')) {
                $table->boolean('can_be_sold')->default(true)->after('item_type');
            }
            if (!Schema::hasColumn('products', 'can_be_purchased')) {
                $table->boolean('can_be_purchased')->default(true)->after('can_be_sold');
            }
            if (!Schema::hasColumn('products', 'auto_deduct_ingredients')) {
                $table->boolean('auto_deduct_ingredients')->default(true)->after('can_be_purchased');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['item_type', 'can_be_sold', 'can_be_purchased', 'auto_deduct_ingredients']);
        });
    }
};
