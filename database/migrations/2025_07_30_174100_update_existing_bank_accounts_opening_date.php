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
        // Update existing bank accounts with opening dates if they don't have one
        DB::table('bank_accounts')
            ->whereNull('opening_date')
            ->update(['opening_date' => now()->toDateString()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally revert the opening dates
        // DB::table('bank_accounts')->update(['opening_date' => null]);
    }
};
