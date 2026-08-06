<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            if (!Schema::hasColumn('brands', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('logo');
                $table->foreign('parent_id')->references('id')->on('brands')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            if (Schema::hasColumn('brands', 'parent_id')) {
                // Drop foreign key if it exists before dropping column
                try {
                    $table->dropForeign(['parent_id']);
                } catch (\Throwable $e) {
                    // Ignore if foreign key was not named default or doesn't exist
                }
                $table->dropColumn('parent_id');
            }
        });
    }
};
