<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'code',
        'description',
        'department_id',
        'level',
        'min_salary',
        'max_salary',
        'requirements',
        'responsibilities',
        'is_active',
    ];

    protected $casts = [
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    // Accessors
    public function getFullTitleAttribute(): string
    {
        if ($this->department) {
            return $this->title . ' (' . $this->department->name . ')';
        }
        return $this->title;
    }

    public function getSalaryRangeAttribute(): string
    {
        if ($this->min_salary && $this->max_salary) {
            return '$' . number_format($this->min_salary, 2) . ' - $' . number_format($this->max_salary, 2);
        } elseif ($this->min_salary) {
            return 'From $' . number_format($this->min_salary, 2);
        } elseif ($this->max_salary) {
            return 'Up to $' . number_format($this->max_salary, 2);
        }
        return 'Not specified';
    }

    // Methods
    public function getEmployeeCount(): int
    {
        return $this->employees()->count();
    }

    public function isWithinSalaryRange($salary): bool
    {
        if ($this->min_salary && $salary < $this->min_salary) {
            return false;
        }
        if ($this->max_salary && $salary > $this->max_salary) {
            return false;
        }
        return true;
    }
}
