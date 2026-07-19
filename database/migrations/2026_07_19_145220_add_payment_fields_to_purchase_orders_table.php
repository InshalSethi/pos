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
            if (!Schema::hasColumn('purchase_orders', 'grand_total')) {
                $table->decimal('grand_total', 12, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('purchase_orders', 'amount_paid')) {
                $table->decimal('amount_paid', 12, 2)->default(0)->after('grand_total');
            }
            if (!Schema::hasColumn('purchase_orders', 'due_amount')) {
                $table->decimal('due_amount', 12, 2)->default(0)->after('amount_paid');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['grand_total', 'amount_paid', 'due_amount']);
        });
    }
};
