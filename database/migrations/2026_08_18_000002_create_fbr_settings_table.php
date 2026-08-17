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
        Schema::create('fbr_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->boolean('is_enabled')->default(false);
            $table->enum('environment', ['sandbox', 'production'])->default('sandbox');
            $table->string('pos_id')->nullable();
            $table->string('ntn')->nullable();
            $table->string('strn')->nullable();
            $table->string('business_name')->nullable();
            $table->string('branch_name')->nullable();
            $table->text('api_token')->nullable();
            $table->string('base_url')->nullable();
            $table->boolean('auto_sync')->default(true);
            $table->boolean('sync_sales')->default(true);
            $table->boolean('sync_purchases')->default(true);
            $table->boolean('sync_transactions')->default(true);
            $table->boolean('sync_payments')->default(true);
            $table->timestamps();

            $table->unique('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fbr_settings');
    }
};
