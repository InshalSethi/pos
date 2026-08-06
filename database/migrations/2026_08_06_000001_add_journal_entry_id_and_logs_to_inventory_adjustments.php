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
        if (Schema::hasTable('inventory_adjustments') && !Schema::hasColumn('inventory_adjustments', 'journal_entry_id')) {
            Schema::table('inventory_adjustments', function (Blueprint $table) {
                $table->foreignId('journal_entry_id')->nullable()->after('user_id')->constrained('journal_entries')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('adjustment_logs')) {
            Schema::create('adjustment_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
                $table->foreignId('inventory_adjustment_id')->nullable()->constrained('inventory_adjustments')->onDelete('set null');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('action_type')->default('create'); // create, edit, bulk_update
                $table->text('description')->nullable();
                $table->json('previous_data')->nullable();
                $table->json('updated_data')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('adjustment_logs')) {
            Schema::dropIfExists('adjustment_logs');
        }

        if (Schema::hasTable('inventory_adjustments') && Schema::hasColumn('inventory_adjustments', 'journal_entry_id')) {
            Schema::table('inventory_adjustments', function (Blueprint $table) {
                $table->dropForeign(['journal_entry_id']);
                $table->dropColumn('journal_entry_id');
            });
        }
    }
};
