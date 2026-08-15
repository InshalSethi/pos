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
        if (Schema::hasTable('customers') && !Schema::hasColumn('customers', 'profile_image')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('profile_image')->nullable()->after('notes');
            });
        }

        if (Schema::hasTable('suppliers') && !Schema::hasColumn('suppliers', 'profile_image')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->string('profile_image')->nullable()->after('notes');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'profile_image')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('profile_image');
            });
        }

        if (Schema::hasTable('suppliers') && Schema::hasColumn('suppliers', 'profile_image')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropColumn('profile_image');
            });
        }
    }
};
