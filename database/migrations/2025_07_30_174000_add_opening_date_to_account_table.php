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
        // Check if the account table exists (old system)
        if (Schema::hasTable('account')) {
            Schema::table('account', function (Blueprint $table) {
                if (!Schema::hasColumn('account', 'opening_date')) {
                    $table->date('opening_date')->nullable()->after('opening_balance');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('account')) {
            Schema::table('account', function (Blueprint $table) {
                if (Schema::hasColumn('account', 'opening_date')) {
                    $table->dropColumn('opening_date');
                }
            });
        }
    }
};
