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
            if (!Schema::hasColumn('sales', 'manual_tax_type')) {
                $table->string('manual_tax_type')->default('percentage')->after('tax_amount');
            }
            if (!Schema::hasColumn('sales', 'manual_tax_value')) {
                $table->decimal('manual_tax_value', 12, 2)->default(0)->after('manual_tax_type');
            }
            if (!Schema::hasColumn('sales', 'manual_discount_type')) {
                $table->string('manual_discount_type')->default('percentage')->after('discount_amount');
            }
            if (!Schema::hasColumn('sales', 'manual_discount_value')) {
                $table->decimal('manual_discount_value', 12, 2)->default(0)->after('manual_discount_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['manual_tax_type', 'manual_tax_value', 'manual_discount_type', 'manual_discount_value']);
        });
    }
};
