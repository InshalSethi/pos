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
        if (Schema::hasTable('fbr_settings')) {
            Schema::table('fbr_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('fbr_settings', 'authority_type')) {
                    $table->string('authority_type')->default('fbr')->after('company_id');
                    $table->index(['company_id', 'authority_type']);
                }
            });
        }

        if (Schema::hasTable('fbr_entries')) {
            Schema::table('fbr_entries', function (Blueprint $table) {
                if (!Schema::hasColumn('fbr_entries', 'authority_type')) {
                    $table->string('authority_type')->default('fbr')->after('company_id');
                    $table->index(['company_id', 'authority_type']);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('fbr_settings')) {
            Schema::table('fbr_settings', function (Blueprint $table) {
                if (Schema::hasColumn('fbr_settings', 'authority_type')) {
                    $table->dropIndex(['company_id', 'authority_type']);
                    $table->dropColumn('authority_type');
                }
            });
        }

        if (Schema::hasTable('fbr_entries')) {
            Schema::table('fbr_entries', function (Blueprint $table) {
                if (Schema::hasColumn('fbr_entries', 'authority_type')) {
                    $table->dropIndex(['company_id', 'authority_type']);
                    $table->dropColumn('authority_type');
                }
            });
        }
    }
};
