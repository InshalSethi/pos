<?php

namespace App\Models;

use App\Traits\BelongsToCompany;

use App\Traits\HasUtcDatabaseTimezones;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use App\Services\EmployeeUserService;

use Illuminate\Support\Facades\Storage;

class Employee extends Model
{
    use BelongsToCompany;
    use HasUtcDatabaseTimezones;
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
        'is_manager',
        'avatar',
        'profile_photo_path',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'probation_end_date' => 'date',
        'termination_date' => 'date',
        'basic_salary' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'is_manager' => 'boolean',
    ];

    protected $appends = ['full_name', 'avatar_url', 'profile_photo_path'];

    /**
     * Scope query to only include managers.
     */
    public function scopeManagers($query)
    {
        return $query->where('is_manager', true);
    }

    /**
     * Get full_name attribute.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . ($this->middle_name ? $this->middle_name . ' ' : '') . $this->last_name);
    }

    /**
     * Get avatar mapping to profile_image.
     */
    public function getAvatarAttribute()
    {
        return $this->profile_image;
    }

    /**
     * Set avatar mapping to profile_image.
     */
    public function setAvatarAttribute($value)
    {
        $this->attributes['profile_image'] = $value;
        $this->profile_image = $value;
    }

    /**
     * Get profile_photo_path mapping to profile_image.
     */
    public function getProfilePhotoPathAttribute()
    {
        return $this->profile_image;
    }

    /**
     * Set profile_photo_path mapping to profile_image.
     */
    public function setProfilePhotoPathAttribute($value)
    {
        $this->attributes['profile_image'] = $value;
        $this->profile_image = $value;
    }

    /**
     * Get full public URL for avatar/profile image.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->profile_image) {
            return null;
        }

        if (str_starts_with($this->profile_image, 'http://') || str_starts_with($this->profile_image, 'https://')) {
            return $this->profile_image;
        }

        return Storage::disk('public')->url($this->profile_image);
    }

    /**
     * Helper to sync basic details to linked User account.
     */
    public function syncToUser(): void
    {
        if (!$this->user_id && !$this->user) {
            return;
        }

        $user = $this->user ?: User::find($this->user_id);
        if ($user) {
            $user->update([
                'name' => $this->full_name,
                'email' => $this->email,
                'phone' => $this->phone ?: $this->mobile,
                'address' => $this->address,
                'profile_image' => $this->profile_image,
                'is_active' => $this->is_active,
            ]);
        }
    }

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

    public function managedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'manager_id');
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

    /**
     * Scope a query to strictly exclude company owners/admins and account creators dynamically.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNonAdmin($query)
    {
        $user = auth()->user();
        $companyId = $user ? ($user->current_company_id ?? $user->company_id) : null;
        $company = $companyId ? \App\Models\Company::find($companyId) : ($user ? $user->currentCompany : null);
        $ownerId = $company ? $company->user_id : null;
        $ownerEmail = ($company && $company->owner) ? $company->owner->email : null;

        // 1. Exclude direct owner user_id & email if known
        if ($ownerId) {
            $query->where(function ($q) use ($ownerId) {
                $q->whereNull('user_id')->orWhere('user_id', '!=', $ownerId);
            });
        }

        if ($ownerEmail) {
            $query->where('email', '!=', $ownerEmail);
        }

        // 2. Exclude any employee whose linked user has admin / owner roles or is owner/admin
        $query->whereDoesntHave('user', function ($uq) use ($ownerId, $companyId) {
            $uq->where(function ($sq) use ($ownerId, $companyId) {
                if ($ownerId) {
                    $sq->orWhere('id', $ownerId);
                }
                $sq->orWhereHas('roles', function ($rq) {
                    $rq->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(name)'), ['admin', 'owner', 'super-admin', 'company admin', 'company owner']);
                });
                if ($companyId) {
                    $sq->orWhereHas('companies', function ($cq) use ($companyId) {
                        $cq->where('company_id', $companyId)
                           ->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(company_user.role)'), ['admin', 'owner', 'super-admin']);
                    });
                }
            });
        });

        return $query;
    }
}
