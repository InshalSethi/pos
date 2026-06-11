<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add soft delete tracking column to the companies table.
     * Appends a nullable 'deleted_at' timestamp column.
     * Rows with a non-null deleted_at are excluded from all standard queries.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->softDeletes(); // Adds: deleted_at TIMESTAMP NULL DEFAULT NULL
        });
    }

    /**
     * Reverse: drop the soft deletes column on rollback.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropSoftDeletes(); // Removes the deleted_at column cleanly
        });
    }
};
