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
        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->decimal('basic_salary', 12, 2);
            $table->enum('salary_type', ['monthly', 'hourly', 'daily'])->default('monthly');
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->decimal('overtime_rate', 8, 2)->nullable();
            $table->decimal('allowances', 12, 2)->default(0); // Housing, transport, etc.
            $table->decimal('deductions', 12, 2)->default(0); // Tax, insurance, etc.
            $table->decimal('gross_salary', 12, 2); // Basic + allowances
            $table->decimal('net_salary', 12, 2); // Gross - deductions
            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->text('adjustment_reason')->nullable();
            $table->enum('status', ['active', 'inactive', 'superseded'])->default('active');
            $table->json('allowance_breakdown')->nullable(); // Detailed allowances
            $table->json('deduction_breakdown')->nullable(); // Detailed deductions
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'effective_date'], 'emp_salary_emp_date_idx');
            $table->index(['status'], 'emp_salary_status_idx');
            $table->index(['effective_date'], 'emp_salary_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_salaries');
    }
};
