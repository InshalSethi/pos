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
        Schema::create('product_recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_variation_id')->nullable()->constrained('product_variations')->onDelete('cascade');
            $table->string('name')->default('Main Recipe');
            $table->decimal('yield_quantity', 15, 4)->default(1.0000);
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('product_recipes')->onDelete('cascade');
            $table->foreignId('raw_material_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('raw_material_variation_id')->nullable()->constrained('product_variations')->onDelete('cascade');
            $table->decimal('quantity', 15, 4);
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            $table->decimal('waste_percentage', 8, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
        Schema::dropIfExists('product_recipes');
    }
};
