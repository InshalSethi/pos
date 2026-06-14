<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1. ATTRIBUTES TABLE (e.g., Color, Size, Toppings)
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->index()->constrained()->onDelete('cascade'); // Tenant Isolation
            $table->string('name'); // e.g., "Size", "Color"
            $table->timestamps();
        });

        // 2. ATTRIBUTE VALUES TABLE (e.g., Small, Large, Red, Black)
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained()->onDelete('cascade');
            $table->string('value'); // e.g., "XL", "Crimson Red"
            $table->timestamps();
        });

        // 3. PRODUCT VARIATIONS COMBINATIONS TABLE (The Core Stock/Price Matrix)
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Stores sorted value IDs combination as a unique key descriptor string, e.g., "1-5" (Red-XL)
            $table->string('combination_key')->index(); 
            $table->string('variation_name_string')->nullable(); // e.g., "Red / XL" for quick receipt prints
            
            $table->string('sku')->nullable()->unique();
            
            // Dual Pricing Pipeline for POS vs Online E-commerce Storefronts
            $table->decimal('retail_price', 12, 2)->default(0.00); // POS Price
            $table->decimal('online_price', 12, 2)->default(0.00); // E-commerce Price
            
            // Promotional Sale Tags Layer
            $table->boolean('on_sale')->default(false);
            $table->decimal('sale_price', 12, 2)->nullable();
            
            // Unified Stock Monitoring
            $table->integer('stock_qty')->default(0);
            $table->integer('min_stock_alert')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('product_variations');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');
    }
};
