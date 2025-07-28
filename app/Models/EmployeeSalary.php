<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class EmployeeSalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'basic_salary',
        'salary_type',
        'hourly_rate',
        'overtime_rate',
        'allowances',
        'deductions',
        'gross_salary',
        'net_salary',
        'effective_date',
        'end_date',
        'adjustment_reason',
        'status',
        'allowance_breakdown',
        'deduction_breakdown',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'effective_date' => 'date',
        'end_date' => 'date',
        'allowance_breakdown' => 'array',
        'deduction_breakdown' => 'array',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payrollRecords(): HasMany
    {
        return $this->hasMany(PayrollRecord::class);
    }

    public function salaryAdjustments(): HasMany
    {
        return $this->hasMany(SalaryAdjustment::class, 'new_salary_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCurrent($query)
    {
        return $query->where('status', 'active')
                    ->where('effective_date', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                    });
    }

    public function scopeEffectiveAsOf($query, $date)
    {
        return $query->where('effective_date', '<=', $date)
                    ->where(function ($q) use ($date) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', $date);
                    });
    }

    // Accessors
    public function getFormattedBasicSalaryAttribute(): string
    {
        return '$' . number_format($this->basic_salary, 2);
    }

    public function getFormattedGrossSalaryAttribute(): string
    {
        return '$' . number_format($this->gross_salary, 2);
    }

    public function getFormattedNetSalaryAttribute(): string
    {
        return '$' . number_format($this->net_salary, 2);
    }

    public function getSalaryTypeDisplayAttribute(): string
    {
        return match($this->salary_type) {
            'monthly' => 'Monthly',
            'hourly' => 'Hourly',
            'daily' => 'Daily',
            default => ucfirst($this->salary_type)
        };
    }

    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'active' => 'Active',
            'inactive' => 'Inactive',
            'superseded' => 'Superseded',
            default => ucfirst($this->status)
        };
    }

    public function getIsCurrentAttribute(): bool
    {
        return $this->status === 'active' &&
               $this->effective_date <= now() &&
               ($this->end_date === null || $this->end_date >= now());
    }

    // Methods
    public function calculateGrossSalary(): float
    {
        return $this->basic_salary + $this->allowances;
    }

    public function calculateNetSalary(): float
    {
        return $this->calculateGrossSalary() - $this->deductions;
    }

    public function updateCalculatedFields(): void
    {
        $this->gross_salary = $this->calculateGrossSalary();
        $this->net_salary = $this->calculateNetSalary();
        $this->save();
    }

    public function supersede($endDate = null): void
    {
        $this->update([
            'status' => 'superseded',
            'end_date' => $endDate ?? now()->toDateString()
        ]);
    }

    public function approve($approvedBy = null): void
    {
        $this->update([
            'approved_by' => $approvedBy ?? auth()->id(),
            'approved_at' => now()
        ]);
    }

    public function calculateMonthlyEquivalent(): float
    {
        return match($this->salary_type) {
            'monthly' => $this->basic_salary,
            'daily' => $this->basic_salary * 22, // Assuming 22 working days per month
            'hourly' => $this->hourly_rate * 8 * 22, // 8 hours/day, 22 days/month
            default => $this->basic_salary
        };
    }

    public function calculateAnnualSalary(): float
    {
        return $this->calculateMonthlyEquivalent() * 12;
    }

    // Static methods
    public static function getCurrentSalaryForEmployee($employeeId)
    {
        return static::where('employee_id', $employeeId)
                    ->current()
                    ->first();
    }

    public static function getSalaryAsOf($employeeId, $date)
    {
        return static::where('employee_id', $employeeId)
                    ->effectiveAsOf($date)
                    ->orderBy('effective_date', 'desc')
                    ->first();
    }
}
