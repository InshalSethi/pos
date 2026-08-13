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
        try {
            DB::statement("ALTER TABLE payments MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'draft'");
        } catch (\Throwable $e) {
            // Fallback for non-MySQL or DB dbal
        }

        try {
            DB::statement("ALTER TABLE payment_receipts MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'draft'");
        } catch (\Throwable $e) {
            // Fallback for non-MySQL or DB dbal
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
