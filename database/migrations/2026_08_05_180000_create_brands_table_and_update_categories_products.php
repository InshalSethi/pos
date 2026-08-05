<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create brands table
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->string('slug');
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unique(['company_id', 'slug'], 'uq_brands_company_slug');
            $table->unique(['company_id', 'name'], 'uq_brands_company_name');
            $table->unique(['id', 'company_id'], 'uq_brand_company');
        });

        // 2. Add columns to categories table: slug and softDeletes
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
            if (!Schema::hasColumn('categories', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // Backfill categories slug based on their names
        $categories = DB::table('categories')->get();
        foreach ($categories as $cat) {
            $slug = Str::slug($cat->name);
            // Ensure unique slug per company
            $baseSlug = $slug;
            $counter = 1;
            while (DB::table('categories')->where('company_id', $cat->company_id)->where('slug', $slug)->where('id', '!=', $cat->id)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            DB::table('categories')->where('id', $cat->id)->update(['slug' => $slug]);
        }

        // Change slug to not nullable and add unique constraint
        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
            $table->unique(['company_id', 'slug'], 'uq_categories_company_slug');
        });

        // 3. Add brand_id to products table
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'brand_id')) {
                $table->unsignedBigInteger('brand_id')->nullable()->after('category_id');
            }
            $table->foreign(['brand_id', 'company_id'])
                ->references(['id', 'company_id'])
                ->on('brands')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'brand_id')) {
                $table->dropForeign(['brand_id', 'company_id']);
                $table->dropColumn('brand_id');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('uq_categories_company_slug');
            $table->dropColumn(['slug', 'deleted_at']);
        });

        Schema::dropIfExists('brands');
    }
};
