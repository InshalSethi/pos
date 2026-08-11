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
            if (!Schema::hasColumn('purchase_returns', 'payment_method')) {
                $table->string('payment_method')->default('cash')->after('refund_status');
            }

            if (!Schema::hasColumn('purchase_returns', 'bank_account_id')) {
                $table->foreignId('bank_account_id')->nullable()->after('payment_method')->constrained('bank_accounts')->nullOnDelete();
            }

            if (!Schema::hasColumn('purchase_returns', 'reference_number')) {
                $table->string('reference_number')->nullable()->after('bank_account_id');
            }

            if (!Schema::hasColumn('purchase_returns', 'refund_splits')) {
                $table->json('refund_splits')->nullable()->after('reference_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_returns', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_returns', 'refund_splits')) {
                $table->dropColumn('refund_splits');
            }
            if (Schema::hasColumn('purchase_returns', 'reference_number')) {
                $table->dropColumn('reference_number');
            }
            if (Schema::hasColumn('purchase_returns', 'bank_account_id')) {
                $table->dropForeign(['bank_account_id']);
                $table->dropColumn('bank_account_id');
            }
            if (Schema::hasColumn('purchase_returns', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
        });
    }
};
