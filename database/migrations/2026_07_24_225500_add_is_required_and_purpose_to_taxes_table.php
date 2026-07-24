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
        Schema::table('taxes', function (Blueprint $table) {
            if (!Schema::hasColumn('taxes', 'is_required')) {
                $table->boolean('is_required')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('taxes', 'purpose')) {
                $table->string('purpose', 20)->default('both')->after('is_required');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            if (Schema::hasColumn('taxes', 'is_required')) {
                $table->dropColumn('is_required');
            }
            if (Schema::hasColumn('taxes', 'purpose')) {
                $table->dropColumn('purpose');
            }
        });
    }
};
