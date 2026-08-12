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
        // Ensure users table has profile_image column
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'profile_image')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('profile_image')->nullable()->after('email');
            });
        }

        // Ensure employees table has user_id and profile_image columns
        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                if (!Schema::hasColumn('employees', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->after('employee_number')->constrained('users')->onDelete('set null');
                }
                if (!Schema::hasColumn('employees', 'profile_image')) {
                    $table->string('profile_image')->nullable()->after('passport_number');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destruct on rollback
    }
};
