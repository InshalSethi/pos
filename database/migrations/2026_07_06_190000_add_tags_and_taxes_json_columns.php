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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'taxes')) {
                $table->json('taxes')->nullable()->after('tax_rate');
            }
        });

        Schema::table('product_variations', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variations', 'tags')) {
                $table->json('tags')->nullable()->after('tax_rate');
            }
            if (!Schema::hasColumn('product_variations', 'taxes')) {
                $table->json('taxes')->nullable()->after('tags');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('taxes');
        });

        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropColumn(['tags', 'taxes']);
        });
    }
};
