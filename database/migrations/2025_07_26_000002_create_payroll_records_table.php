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
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_number')->unique();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_salary_id')->constrained('employee_salaries')->onDelete('cascade');
            $table->date('pay_period_start');
            $table->date('pay_period_end');
            $table->date('pay_date');
            $table->decimal('basic_pay', 12, 2);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->decimal('bonuses', 12, 2)->default(0);
            $table->decimal('commissions', 12, 2)->default(0);
            $table->decimal('gross_pay', 12, 2);
            $table->decimal('tax_deductions', 12, 2)->default(0);
            $table->decimal('insurance_deductions', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2);
            $table->enum('status', ['draft', 'approved', 'paid', 'cancelled'])->default('draft');
            $table->enum('payment_method', ['bank_transfer', 'cash', 'check'])->default('bank_transfer');
            $table->string('payment_reference')->nullable();
            $table->json('allowance_breakdown')->nullable();
            $table->json('deduction_breakdown')->nullable();
            $table->json('bonus_breakdown')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('journal_entry_id')->nullable()->constrained()->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'pay_period_start'], 'payroll_employee_period_idx');
            $table->index(['status'], 'payroll_status_idx');
            $table->index(['pay_date'], 'payroll_pay_date_idx');
            $table->index(['payroll_number'], 'payroll_number_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
    }
};
