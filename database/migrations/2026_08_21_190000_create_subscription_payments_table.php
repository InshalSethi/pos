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
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('user_name');
            $table->string('user_email');
            $table->string('plan_name');
            $table->string('billing_cycle')->default('monthly'); // monthly, yearly
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->string('currency', 10)->default('USD');
            $table->string('payment_method')->default('Credit Card');
            $table->string('card_last_four', 4)->nullable();
            $table->string('transaction_id')->unique();
            $table->string('status')->default('paid'); // paid, pending, failed, refunded
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
