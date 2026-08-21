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
        if (!Schema::hasTable('sync_queues')) {
            Schema::create('sync_queues', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->string('terminal_id', 50)->nullable()->index();
                $table->string('entity_type', 100)->index();
                $table->string('entity_id', 100)->index();
                $table->string('action', 20)->default('CREATE');
                $table->longText('payload');
                $table->string('status', 20)->default('pending')->index();
                $table->integer('attempts')->default(0);
                $table->text('error_message')->nullable();
                $table->timestamp('synced_at')->nullable();
                $table->timestamps();
            });
        }

        $transactionalTables = ['sales', 'payments', 'customers', 'inventory_adjustments'];

        foreach ($transactionalTables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'sync_status')) {
                        $table->string('sync_status', 20)->default('synced')->nullable()->index();
                    }
                    if (!Schema::hasColumn($tableName, 'synced_at')) {
                        $table->timestamp('synced_at')->nullable();
                    }
                    if (!Schema::hasColumn($tableName, 'terminal_id')) {
                        $table->string('terminal_id', 50)->nullable()->index();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_queues');

        $transactionalTables = ['sales', 'payments', 'customers', 'inventory_adjustments'];
        foreach ($transactionalTables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'sync_status')) {
                        $table->dropColumn('sync_status');
                    }
                    if (Schema::hasColumn($tableName, 'synced_at')) {
                        $table->dropColumn('synced_at');
                    }
                    if (Schema::hasColumn($tableName, 'terminal_id')) {
                        $table->dropColumn('terminal_id');
                    }
                });
            }
        }
    }
};
