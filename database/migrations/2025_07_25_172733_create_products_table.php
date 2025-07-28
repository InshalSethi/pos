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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sku')->unique();
            $table->string('barcode')->unique()->nullable();
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2);
            $table->decimal('markup_percentage', 5, 2)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock_level')->default(0);
            $table->integer('max_stock_level')->nullable();
            $table->string('unit_of_measure')->default('pcs');
            $table->boolean('track_inventory')->default(true);
            $table->boolean('is_active')->default(true);
            $table->string('image')->nullable();
            $table->json('images')->nullable(); // Multiple images
            $table->decimal('weight', 8, 3)->nullable();
            $table->string('dimensions')->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();

            $table->index(['is_active', 'track_inventory']);
            $table->index(['stock_quantity']);
            $table->index(['category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
