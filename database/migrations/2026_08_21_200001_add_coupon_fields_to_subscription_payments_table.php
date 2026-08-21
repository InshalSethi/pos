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
        Schema::table('subscription_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('subscription_payments', 'coupon_code')) {
                $table->string('coupon_code')->nullable()->after('amount');
                $table->decimal('discount_amount', 10, 2)->default(0.00)->after('coupon_code');
                $table->decimal('original_amount', 10, 2)->nullable()->after('discount_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->dropColumn(['coupon_code', 'discount_amount', 'original_amount']);
        });
    }
};
