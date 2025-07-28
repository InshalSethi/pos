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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sales_alerts')->default(true);
            $table->boolean('low_stock_alerts')->default(true);
            $table->enum('theme', ['light', 'dark', 'auto'])->default('light');
            $table->integer('items_per_page')->default(15);
            $table->enum('default_payment_method', ['cash', 'card', 'digital'])->default('cash');
            $table->boolean('auto_print_receipts')->default(false);
            $table->boolean('sound_effects')->default(true);
            $table->integer('session_timeout')->default(60);
            $table->boolean('two_factor_auth')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
