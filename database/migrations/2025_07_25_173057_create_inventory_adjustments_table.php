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
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number')->unique();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->enum('adjustment_type', ['increase', 'decrease', 'recount']);
            $table->integer('quantity_before');
            $table->integer('quantity_adjusted');
            $table->integer('quantity_after');
            $table->text('reason');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('adjustment_date');
            $table->decimal('cost_impact', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id']);
            $table->index(['adjustment_date']);
            $table->index(['adjustment_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};
