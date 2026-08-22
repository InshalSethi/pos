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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('set null');
            
            $table->string('log_type', 50)->index(); // 'auth', 'company', 'sales', 'inventory', 'finance', 'security', 'settings'
            $table->string('event', 50)->index(); // 'login', 'logout', 'failed_login', 'created', 'updated', 'deleted', 'voided', etc.
            
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_title')->nullable();
            
            $table->string('description', 500);
            $table->json('properties')->nullable(); // Stores diffs: { old: {}, new: {}, metadata: {} }
            
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->index(['company_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['employee_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
