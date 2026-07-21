<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        // 1. Ensure composite unique keys on parent tables so they can be referenced
        Schema::table('categories', function (Blueprint $table) {
            $table->unique(['id', 'company_id'], 'uq_category_company');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unique(['id', 'company_id'], 'uq_product_company');
        });

        Schema::table('attributes', function (Blueprint $table) {
            $table->unique(['id', 'company_id'], 'uq_attribute_company');
        });

        // 2. Add company_id to child tables that don't have it
        Schema::table('attribute_values', function (Blueprint $table) {
            if (!Schema::hasColumn('attribute_values', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('attribute_id');
            }
        });

        Schema::table('product_variations', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variations', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('product_id');
            }
        });

        // 3. Backfill company_id for existing records
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('
                UPDATE attribute_values 
                SET company_id = (SELECT company_id FROM attributes WHERE attributes.id = attribute_values.attribute_id)
                WHERE company_id IS NULL
            ');
            DB::statement('
                UPDATE product_variations 
                SET company_id = (SELECT company_id FROM products WHERE products.id = product_variations.product_id)
                WHERE company_id IS NULL
            ');
        } else {
            DB::statement('
                UPDATE attribute_values av 
                JOIN attributes a ON av.attribute_id = a.id 
                SET av.company_id = a.company_id 
                WHERE av.company_id IS NULL
            ');

            DB::statement('
                UPDATE product_variations pv 
                JOIN products p ON pv.product_id = p.id 
                SET pv.company_id = p.company_id 
                WHERE pv.company_id IS NULL
            ');
        }

        // 4. Enforce NOT NULL on backfilled columns and index them
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable(false)->change()->index();
        });

        Schema::table('product_variations', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable(false)->change()->index();
        });

        // 5. Add unique keys on child tables to support further nesting if needed
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->unique(['id', 'company_id'], 'uq_attribute_value_company');
        });

        Schema::table('product_variations', function (Blueprint $table) {
            $table->unique(['id', 'company_id'], 'uq_variation_company');
        });

        // 6. Drop existing simple foreign keys so they can be replaced
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        Schema::table('attribute_values', function (Blueprint $table) {
            $table->dropForeign(['attribute_id']);
        });

        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        // 7. Enforce composite foreign keys
        Schema::table('products', function (Blueprint $table) {
            $table->foreign(['category_id', 'company_id'])
                ->references(['id', 'company_id'])
                ->on('categories')
                ->onDelete('set null');
        });

        Schema::table('attribute_values', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign(['attribute_id', 'company_id'])
                ->references(['id', 'company_id'])
                ->on('attributes')
                ->onDelete('cascade');
        });

        Schema::table('product_variations', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign(['product_id', 'company_id'])
                ->references(['id', 'company_id'])
                ->on('products')
                ->onDelete('cascade');
        });
    }

    public function down(): void {
        // Rollback constraints
        // Drop composite foreign keys
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id', 'company_id']);
        });

        Schema::table('attribute_values', function (Blueprint $table) {
            $table->dropForeign(['attribute_id', 'company_id']);
            $table->dropForeign(['company_id']);
        });

        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropForeign(['product_id', 'company_id']);
            $table->dropForeign(['company_id']);
        });

        // Restore simple foreign keys
        Schema::table('products', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });

        Schema::table('attribute_values', function (Blueprint $table) {
            $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');
        });

        Schema::table('product_variations', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        // Drop unique constraints
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('uq_category_company');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('uq_product_company');
        });

        Schema::table('attributes', function (Blueprint $table) {
            $table->dropUnique('uq_attribute_company');
        });

        Schema::table('attribute_values', function (Blueprint $table) {
            $table->dropUnique('uq_attribute_value_company');
            $table->dropColumn('company_id');
        });

        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropUnique('uq_variation_company');
            $table->dropColumn('company_id');
        });
    }
};
