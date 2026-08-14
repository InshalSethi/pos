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
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'attachments')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('attachments')->nullable()->after('profile_image');
            });
        }

        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'attachments')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->json('attachments')->nullable()->after('profile_image');
            });
        }

        if (Schema::hasTable('customers') && !Schema::hasColumn('customers', 'attachments')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->json('attachments')->nullable();
            });
        }

        if (Schema::hasTable('suppliers') && !Schema::hasColumn('suppliers', 'attachments')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->json('attachments')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'attachments')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('attachments');
            });
        }

        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'attachments')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('attachments');
            });
        }

        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'attachments')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('attachments');
            });
        }

        if (Schema::hasTable('suppliers') && Schema::hasColumn('suppliers', 'attachments')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropColumn('attachments');
            });
        }
    }
};
