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
        if (Schema::hasTable('licenses')) {
            try {
                DB::statement('ALTER TABLE licenses DROP INDEX licenses_license_key_unique');
            } catch (\Exception $e) {}

            DB::statement('ALTER TABLE licenses MODIFY license_key TEXT NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('licenses')) {
            DB::statement('ALTER TABLE licenses MODIFY license_key VARCHAR(255) NOT NULL');
        }
    }
};
