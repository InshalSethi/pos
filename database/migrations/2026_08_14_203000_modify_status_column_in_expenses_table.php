<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE expenses MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'draft'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE expenses MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'rejected', 'paid') NOT NULL DEFAULT 'draft'");
        }
    }
};
