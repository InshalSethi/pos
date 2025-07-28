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
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('code')->unique()->nullable(); // For accounting integration
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('parent_category_id')->nullable();
            $table->timestamps();

            $table->foreign('parent_category_id')->references('id')->on('expense_categories')->onDelete('set null');
            $table->index(['is_active']);
            $table->index(['parent_category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_categories');
    }
};
