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
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'status')) {
                $table->string('status')->default('active')->after('employment_status');
            }
            if (!Schema::hasColumn('employees', 'has_system_access')) {
                $table->boolean('has_system_access')->default(false)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'has_system_access')) {
                $table->dropColumn('has_system_access');
            }
            if (Schema::hasColumn('employees', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
