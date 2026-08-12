<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->boolean('is_manager')->default(false)->after('is_active');
            $table->index('is_manager');
        });

        // Auto-set is_manager = true for existing employees with managerial positions
        DB::statement("
            UPDATE employees
            SET is_manager = 1
            WHERE position_id IN (
                SELECT id FROM positions WHERE level IN ('lead', 'manager', 'director', 'executive')
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['is_manager']);
            $table->dropColumn('is_manager');
        });
    }
};
