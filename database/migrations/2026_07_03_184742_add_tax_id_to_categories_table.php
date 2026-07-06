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
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('tax_id')->nullable()->after('parent_id');
            $table->string('discount_type')->nullable()->after('tax_id'); // e.g., percentage, flat, markup_percentage
            $table->decimal('discount_value', 10, 2)->nullable()->after('discount_type');

            $table->foreign('tax_id')->references('id')->on('taxes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['tax_id']);
            $table->dropColumn(['tax_id', 'discount_type', 'discount_value']);
        });
    }
};
