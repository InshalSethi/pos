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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->change();
            if (!Schema::hasColumn('purchase_orders', 'is_walkin_supplier')) {
                $table->boolean('is_walkin_supplier')->default(false)->after('supplier_id');
            }
            if (!Schema::hasColumn('purchase_orders', 'supplier_name')) {
                $table->string('supplier_name')->nullable()->after('is_walkin_supplier');
            }
            if (!Schema::hasColumn('purchase_orders', 'supplier_phone')) {
                $table->string('supplier_phone')->nullable()->after('supplier_name');
            }
            if (!Schema::hasColumn('purchase_orders', 'supplier_email')) {
                $table->string('supplier_email')->nullable()->after('supplier_phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['is_walkin_supplier', 'supplier_name', 'supplier_phone', 'supplier_email']);
        });
    }
};
