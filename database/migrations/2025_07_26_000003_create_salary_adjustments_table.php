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
        Schema::create('salary_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number')->unique();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('old_salary_id')->nullable()->constrained('employee_salaries')->onDelete('set null');
            $table->foreignId('new_salary_id')->constrained('employee_salaries')->onDelete('cascade');
            $table->enum('adjustment_type', ['promotion', 'increment', 'bonus', 'deduction', 'correction', 'other']);
            $table->decimal('old_amount', 12, 2)->nullable();
            $table->decimal('new_amount', 12, 2);
            $table->decimal('adjustment_amount', 12, 2);
            $table->decimal('percentage_change', 8, 2)->nullable();
            $table->date('effective_date');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected', 'implemented'])->default('pending');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'effective_date'], 'salary_adj_emp_date_idx');
            $table->index(['status'], 'salary_adj_status_idx');
            $table->index(['adjustment_type'], 'salary_adj_type_idx');
            $table->index(['effective_date'], 'salary_adj_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_adjustments');
    }
};
