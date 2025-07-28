<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use App\Services\EmployeeUserService;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_number',
        'user_id',
        'first_name',
        'last_name',
        'middle_name',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'date_of_birth',
        'gender',
        'marital_status',
        'national_id',
        'passport_number',
        'profile_image',
        'department_id',
        'position_id',
        'manager_id',
        'hire_date',
        'probation_end_date',
        'termination_date',
        'employment_type',
        'employment_status',
        'termination_reason',
        'basic_salary',
        'salary_type',
        'hourly_rate',
        'bank_account_number',
        'bank_name',
        'bank_branch',
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_phone',
        'emergency_contact_email',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'probation_end_date' => 'date',
        'termination_date' => 'date',
        'basic_salary' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(EmployeeSalary::class);
    }

    public function payrollRecords(): HasMany
    {
        return $this->hasMany(PayrollRecord::class);
    }

    public function salaryAdjustments(): HasMany
    {
        return $this->hasMany(SalaryAdjustment::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }



    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('employment_status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false)->orWhere('employment_status', '!=', 'active');
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByPosition($query, $positionId)
    {
        return $query->where('position_id', $positionId);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        $name = $this->first_name;
        if ($this->middle_name) {
            $name .= ' ' . $this->middle_name;
        }
        $name .= ' ' . $this->last_name;
        return $name;
    }

    public function getFullAddressAttribute(): string
    {
        $address = collect([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country
        ])->filter()->implode(', ');

        return $address;
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    public function getYearsOfServiceAttribute(): ?int
    {
        return $this->hire_date ? $this->hire_date->diffInYears(Carbon::now()) : null;
    }

    // Methods
    public function isActive(): bool
    {
        return $this->is_active && $this->employment_status === 'active';
    }

    public function canBeTerminated(): bool
    {
        return $this->isActive() && !$this->termination_date;
    }

    public function terminate($reason = null, $terminationDate = null): bool
    {
        if (!$this->canBeTerminated()) {
            return false;
        }

        $this->update([
            'employment_status' => 'terminated',
            'termination_date' => $terminationDate ?? Carbon::now()->toDateString(),
            'termination_reason' => $reason,
            'is_active' => false,
        ]);

        // Deactivate user account if exists
        if ($this->user) {
            $userService = app(EmployeeUserService::class);
            $userService->deactivateUserAccount($this);
        }

        return true;
    }

    public function reactivate(): bool
    {
        if ($this->isActive()) {
            return false;
        }

        $this->update([
            'employment_status' => 'active',
            'termination_date' => null,
            'termination_reason' => null,
            'is_active' => true,
        ]);

        // Reactivate user account if exists
        if ($this->user) {
            $userService = app(EmployeeUserService::class);
            $userService->reactivateUserAccount($this);
        }

        return true;
    }

    // Generate employee number
    public static function generateEmployeeNumber(): string
    {
        $prefix = 'EMP';
        $year = Carbon::now()->year;

        $lastEmployee = static::whereYear('created_at', $year)
                             ->orderBy('id', 'desc')
                             ->first();

        $sequence = $lastEmployee ? (int) substr($lastEmployee->employee_number, -4) + 1 : 1;

        return sprintf('%s%s%04d', $prefix, $year, $sequence);
    }
}
