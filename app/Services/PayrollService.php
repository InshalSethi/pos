<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\PayrollRecord;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollService
{
    /**
     * Generate payroll for a specific employee for a given period
     */
    public function generatePayrollForEmployee(
        Employee $employee,
        Carbon $payPeriodStart,
        Carbon $payPeriodEnd,
        array $additionalData = []
    ): PayrollRecord {
        return DB::transaction(function () use ($employee, $payPeriodStart, $payPeriodEnd, $additionalData) {
            // Get current salary
            $salary = EmployeeSalary::getCurrentSalaryForEmployee($employee->id);
            
            if (!$salary) {
                throw new \Exception("No active salary found for employee {$employee->full_name}");
            }

            // Calculate basic pay based on salary type
            $basicPay = $this->calculateBasicPay($salary, $payPeriodStart, $payPeriodEnd);

            // Calculate overtime pay
            $overtimeHours = $additionalData['overtime_hours'] ?? 0;
            $overtimePay = $this->calculateOvertimePay($salary, $overtimeHours);

            // Get allowances and deductions
            $allowances = $additionalData['allowances'] ?? $salary->allowances;
            $bonuses = $additionalData['bonuses'] ?? 0;
            $commissions = $additionalData['commissions'] ?? 0;

            // Calculate deductions
            $taxDeductions = $this->calculateTaxDeductions($basicPay + $overtimePay + $allowances + $bonuses);
            $insuranceDeductions = $additionalData['insurance_deductions'] ?? 0;
            $otherDeductions = $additionalData['other_deductions'] ?? 0;

            // Create payroll record
            $payroll = PayrollRecord::create([
                'payroll_number' => PayrollRecord::generatePayrollNumber(),
                'employee_id' => $employee->id,
                'employee_salary_id' => $salary->id,
                'pay_period_start' => $payPeriodStart,
                'pay_period_end' => $payPeriodEnd,
                'pay_date' => $additionalData['pay_date'] ?? $payPeriodEnd->copy()->addDays(3),
                'basic_pay' => $basicPay,
                'overtime_hours' => $overtimeHours,
                'overtime_pay' => $overtimePay,
                'allowances' => $allowances,
                'bonuses' => $bonuses,
                'commissions' => $commissions,
                'tax_deductions' => $taxDeductions,
                'insurance_deductions' => $insuranceDeductions,
                'other_deductions' => $otherDeductions,
                'payment_method' => $additionalData['payment_method'] ?? 'bank_transfer',
                'allowance_breakdown' => $additionalData['allowance_breakdown'] ?? $salary->allowance_breakdown,
                'deduction_breakdown' => $additionalData['deduction_breakdown'] ?? $salary->deduction_breakdown,
                'bonus_breakdown' => $additionalData['bonus_breakdown'] ?? null,
                'created_by' => auth()->id() ?? 1,
                'notes' => $additionalData['notes'] ?? null,
            ]);

            // Update calculated fields
            $payroll->updateCalculatedFields();

            return $payroll;
        });
    }

    /**
     * Generate payroll for all active employees
     */
    public function generatePayrollForAllEmployees(
        Carbon $payPeriodStart,
        Carbon $payPeriodEnd,
        array $employeeOverrides = []
    ): array {
        $employees = Employee::active()->get();
        $payrolls = [];

        foreach ($employees as $employee) {
            try {
                $additionalData = $employeeOverrides[$employee->id] ?? [];
                $payroll = $this->generatePayrollForEmployee($employee, $payPeriodStart, $payPeriodEnd, $additionalData);
                $payrolls[] = $payroll;
            } catch (\Exception $e) {
                // Log error and continue with next employee
                \Log::error("Failed to generate payroll for employee {$employee->id}: " . $e->getMessage());
            }
        }

        return $payrolls;
    }

    /**
     * Create journal entry for payroll payment
     */
    public function createPayrollJournalEntry(PayrollRecord $payroll): JournalEntry
    {
        return DB::transaction(function () use ($payroll) {
            // Get accounts
            $salaryExpenseAccount = $this->getSalaryExpenseAccount();
            $taxPayableAccount = $this->getTaxPayableAccount();
            $insurancePayableAccount = $this->getInsurancePayableAccount();
            $salaryPayableAccount = $this->getSalaryPayableAccount();

            // Create journal entry
            $journalEntry = JournalEntry::create([
                'entry_number' => $this->generateJournalEntryNumber(),
                'entry_date' => $payroll->pay_date,
                'reference' => $payroll->payroll_number,
                'description' => "Payroll for {$payroll->employee->full_name} - {$payroll->pay_period_display}",
                'entry_type' => 'automatic',
                'status' => 'draft',
                'total_debit' => $payroll->gross_pay,
                'total_credit' => $payroll->gross_pay,
                'created_by' => auth()->id() ?? 1,
            ]);

            // Debit: Salary Expense
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $salaryExpenseAccount->id,
                'description' => 'Salary expense',
                'debit_amount' => $payroll->gross_pay,
                'credit_amount' => 0,
                'partner_type' => 'App\Models\Employee',
                'partner_id' => $payroll->employee_id,
            ]);

            // Credit: Tax Payable (if any)
            if ($payroll->tax_deductions > 0) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $taxPayableAccount->id,
                    'description' => 'Tax deductions',
                    'debit_amount' => 0,
                    'credit_amount' => $payroll->tax_deductions,
                    'partner_type' => 'App\Models\Employee',
                    'partner_id' => $payroll->employee_id,
                ]);
            }

            // Credit: Insurance Payable (if any)
            if ($payroll->insurance_deductions > 0) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $insurancePayableAccount->id,
                    'description' => 'Insurance deductions',
                    'debit_amount' => 0,
                    'credit_amount' => $payroll->insurance_deductions,
                    'partner_type' => 'App\Models\Employee',
                    'partner_id' => $payroll->employee_id,
                ]);
            }

            // Credit: Salary Payable (net amount)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $salaryPayableAccount->id,
                'description' => 'Net salary payable',
                'debit_amount' => 0,
                'credit_amount' => $payroll->net_pay,
                'partner_type' => 'App\Models\Employee',
                'partner_id' => $payroll->employee_id,
            ]);

            // Post the journal entry
            $this->postJournalEntry($journalEntry);

            // Update payroll with journal entry reference
            $payroll->update(['journal_entry_id' => $journalEntry->id]);

            return $journalEntry;
        });
    }

    /**
     * Calculate basic pay based on salary type and period
     */
    private function calculateBasicPay(EmployeeSalary $salary, Carbon $start, Carbon $end): float
    {
        $days = $start->diffInDays($end) + 1;

        return match($salary->salary_type) {
            'monthly' => ($salary->basic_salary / 30) * $days,
            'daily' => $salary->basic_salary * $days,
            'hourly' => $salary->hourly_rate * 8 * $days, // Assuming 8 hours per day
            default => $salary->basic_salary
        };
    }

    /**
     * Calculate overtime pay
     */
    private function calculateOvertimePay(EmployeeSalary $salary, float $overtimeHours): float
    {
        if ($overtimeHours <= 0) {
            return 0;
        }

        $overtimeRate = $salary->overtime_rate ?? ($salary->hourly_rate * 1.5);
        return $overtimeHours * $overtimeRate;
    }

    /**
     * Calculate tax deductions (simplified calculation)
     */
    private function calculateTaxDeductions(float $grossPay): float
    {
        // This is a simplified tax calculation
        // In a real system, you would implement proper tax brackets and rules
        if ($grossPay <= 1000) {
            return 0;
        } elseif ($grossPay <= 3000) {
            return $grossPay * 0.10;
        } elseif ($grossPay <= 5000) {
            return $grossPay * 0.15;
        } else {
            return $grossPay * 0.20;
        }
    }

    /**
     * Get or create salary expense account
     */
    private function getSalaryExpenseAccount(): Account
    {
        return Account::firstOrCreate(
            ['account_code' => '6100'],
            [
                'account_name' => 'Salary Expense',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Employee salary expenses',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ]
        );
    }

    /**
     * Get or create tax payable account
     */
    private function getTaxPayableAccount(): Account
    {
        return Account::firstOrCreate(
            ['account_code' => '2300'],
            [
                'account_name' => 'Tax Payable',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Employee tax deductions payable',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ]
        );
    }

    /**
     * Get or create insurance payable account
     */
    private function getInsurancePayableAccount(): Account
    {
        return Account::firstOrCreate(
            ['account_code' => '2310'],
            [
                'account_name' => 'Insurance Payable',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Employee insurance deductions payable',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ]
        );
    }

    /**
     * Get or create salary payable account
     */
    private function getSalaryPayableAccount(): Account
    {
        return Account::firstOrCreate(
            ['account_code' => '2200'],
            [
                'account_name' => 'Salary Payable',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Employee salaries payable',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ]
        );
    }

    /**
     * Generate journal entry number
     */
    private function generateJournalEntryNumber(): string
    {
        $prefix = 'JE';
        $year = Carbon::now()->year;

        $lastEntry = JournalEntry::whereYear('created_at', $year)
                                ->orderBy('id', 'desc')
                                ->first();

        $sequence = $lastEntry ? (int) substr($lastEntry->entry_number, -6) + 1 : 1;

        return sprintf('%s%s%06d', $prefix, $year, $sequence);
    }

    /**
     * Post journal entry and update account balances
     */
    private function postJournalEntry(JournalEntry $journalEntry): void
    {
        $journalEntry->update([
            'status' => 'posted',
            'posted_by' => auth()->id() ?? 1,
            'posted_at' => now(),
        ]);

        // Update account balances
        foreach ($journalEntry->journalEntryLines as $line) {
            $account = Account::find($line->account_id);
            $account->updateCurrentBalance();
        }
    }
}
