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
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();

            // Stripe settings
            $table->boolean('stripe_enabled')->default(false);
            $table->string('stripe_public_key')->nullable();
            $table->text('stripe_secret_key')->nullable();

            // Square settings
            $table->boolean('square_enabled')->default(false);
            $table->string('square_application_id')->nullable();
            $table->text('square_access_token')->nullable();

            // Google Pay settings
            $table->boolean('googlepay_enabled')->default(false);
            $table->string('googlepay_merchant_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
