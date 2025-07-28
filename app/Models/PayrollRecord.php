<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class PayrollRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_number',
        'employee_id',
        'employee_salary_id',
        'pay_period_start',
        'pay_period_end',
        'pay_date',
        'basic_pay',
        'overtime_hours',
        'overtime_pay',
        'allowances',
        'bonuses',
        'commissions',
        'gross_pay',
        'tax_deductions',
        'insurance_deductions',
        'other_deductions',
        'total_deductions',
        'net_pay',
        'status',
        'payment_method',
        'payment_reference',
        'allowance_breakdown',
        'deduction_breakdown',
        'bonus_breakdown',
        'created_by',
        'approved_by',
        'approved_at',
        'paid_by',
        'paid_at',
        'journal_entry_id',
        'notes',
    ];

    protected $casts = [
        'pay_period_start' => 'date',
        'pay_period_end' => 'date',
        'pay_date' => 'date',
        'basic_pay' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'allowances' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'commissions' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'tax_deductions' => 'decimal:2',
        'insurance_deductions' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'allowance_breakdown' => 'array',
        'deduction_breakdown' => 'array',
        'bonus_breakdown' => 'array',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function employeeSalary(): BelongsTo
    {
        return $this->belongsTo(EmployeeSalary::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('pay_period_start', [$startDate, $endDate])
                    ->orWhereBetween('pay_period_end', [$startDate, $endDate]);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    // Accessors
    public function getFormattedGrossPayAttribute(): string
    {
        return '$' . number_format($this->gross_pay, 2);
    }

    public function getFormattedNetPayAttribute(): string
    {
        return '$' . number_format($this->net_pay, 2);
    }

    public function getFormattedTotalDeductionsAttribute(): string
    {
        return '$' . number_format($this->total_deductions, 2);
    }

    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'approved' => 'Approved',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status)
        };
    }

    public function getPaymentMethodDisplayAttribute(): string
    {
        return match($this->payment_method) {
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
            'check' => 'Check',
            default => ucfirst($this->payment_method)
        };
    }

    public function getPayPeriodDisplayAttribute(): string
    {
        return $this->pay_period_start->format('M d, Y') . ' - ' . $this->pay_period_end->format('M d, Y');
    }

    public function getCanEditAttribute(): bool
    {
        return $this->status === 'draft';
    }

    public function getCanApproveAttribute(): bool
    {
        return $this->status === 'draft';
    }

    public function getCanPayAttribute(): bool
    {
        return $this->status === 'approved';
    }

    public function getCanCancelAttribute(): bool
    {
        return in_array($this->status, ['draft', 'approved']);
    }

    // Methods
    public function calculateGrossPay(): float
    {
        return $this->basic_pay + $this->overtime_pay + $this->allowances + $this->bonuses + $this->commissions;
    }

    public function calculateTotalDeductions(): float
    {
        return $this->tax_deductions + $this->insurance_deductions + $this->other_deductions;
    }

    public function calculateNetPay(): float
    {
        return $this->calculateGrossPay() - $this->calculateTotalDeductions();
    }

    public function updateCalculatedFields(): void
    {
        $this->gross_pay = $this->calculateGrossPay();
        $this->total_deductions = $this->calculateTotalDeductions();
        $this->net_pay = $this->calculateNetPay();
        $this->save();
    }

    public function approve($approvedBy = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy ?? auth()->id(),
            'approved_at' => now()
        ]);
    }

    public function markAsPaid($paidBy = null, $paymentReference = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_by' => $paidBy ?? auth()->id(),
            'paid_at' => now(),
            'payment_reference' => $paymentReference
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    // Static methods
    public static function generatePayrollNumber(): string
    {
        $prefix = 'PAY';
        $year = Carbon::now()->year;
        $month = Carbon::now()->format('m');

        $lastPayroll = static::whereYear('created_at', $year)
                            ->whereMonth('created_at', Carbon::now()->month)
                            ->orderBy('id', 'desc')
                            ->first();

        $sequence = $lastPayroll ? (int) substr($lastPayroll->payroll_number, -4) + 1 : 1;

        return sprintf('%s%s%s%04d', $prefix, $year, $month, $sequence);
    }
}
