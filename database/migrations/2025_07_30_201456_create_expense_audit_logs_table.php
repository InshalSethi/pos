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
        Schema::create('expense_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Who made the change
            $table->string('action'); // 'created', 'updated', 'deleted', 'approved', 'rejected', 'paid'
            $table->string('old_status')->nullable(); // Previous status
            $table->string('new_status')->nullable(); // New status
            $table->json('old_values')->nullable(); // Previous values
            $table->json('new_values')->nullable(); // New values
            $table->json('changed_fields')->nullable(); // List of fields that changed
            $table->text('notes')->nullable(); // Additional notes about the change
            $table->string('ip_address')->nullable(); // IP address of the user
            $table->string('user_agent')->nullable(); // User agent
            $table->boolean('affects_accounting')->default(false); // Whether this change affects accounting
            $table->timestamps();

            $table->index(['expense_id']);
            $table->index(['user_id']);
            $table->index(['action']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_audit_logs');
    }
};
