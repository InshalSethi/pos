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
        if (Schema::hasTable('customers') && !Schema::hasColumn('customers', 'fax')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('fax')->nullable()->after('mobile');
            });
        }

        if (Schema::hasTable('suppliers') && !Schema::hasColumn('suppliers', 'fax')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->string('fax')->nullable()->after('mobile');
            });
        }

        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'fax')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('fax')->nullable()->after('mobile');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'fax')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('fax');
            });
        }

        if (Schema::hasTable('suppliers') && Schema::hasColumn('suppliers', 'fax')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropColumn('fax');
            });
        }

        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'fax')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('fax');
            });
        }
    }
};
