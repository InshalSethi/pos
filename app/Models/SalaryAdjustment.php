<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class SalaryAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'adjustment_number',
        'employee_id',
        'old_salary_id',
        'new_salary_id',
        'adjustment_type',
        'old_amount',
        'new_amount',
        'adjustment_amount',
        'percentage_change',
        'effective_date',
        'reason',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'old_amount' => 'decimal:2',
        'new_amount' => 'decimal:2',
        'adjustment_amount' => 'decimal:2',
        'percentage_change' => 'decimal:2',
        'effective_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function oldSalary(): BelongsTo
    {
        return $this->belongsTo(EmployeeSalary::class, 'old_salary_id');
    }

    public function newSalary(): BelongsTo
    {
        return $this->belongsTo(EmployeeSalary::class, 'new_salary_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeImplemented($query)
    {
        return $query->where('status', 'implemented');
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    // Accessors
    public function getFormattedOldAmountAttribute(): string
    {
        return $this->old_amount ? '$' . number_format($this->old_amount, 2) : 'N/A';
    }

    public function getFormattedNewAmountAttribute(): string
    {
        return '$' . number_format($this->new_amount, 2);
    }

    public function getFormattedAdjustmentAmountAttribute(): string
    {
        $prefix = $this->adjustment_amount >= 0 ? '+' : '';
        return $prefix . '$' . number_format($this->adjustment_amount, 2);
    }

    public function getFormattedPercentageChangeAttribute(): string
    {
        if (!$this->percentage_change) {
            return 'N/A';
        }
        $prefix = $this->percentage_change >= 0 ? '+' : '';
        return $prefix . number_format($this->percentage_change, 2) . '%';
    }

    public function getAdjustmentTypeDisplayAttribute(): string
    {
        return match($this->adjustment_type) {
            'promotion' => 'Promotion',
            'increment' => 'Annual Increment',
            'bonus' => 'Bonus',
            'deduction' => 'Deduction',
            'correction' => 'Correction',
            'other' => 'Other',
            default => ucfirst($this->adjustment_type)
        };
    }

    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'implemented' => 'Implemented',
            default => ucfirst($this->status)
        };
    }

    public function getCanApproveAttribute(): bool
    {
        return $this->status === 'pending';
    }

    public function getCanRejectAttribute(): bool
    {
        return $this->status === 'pending';
    }

    public function getCanImplementAttribute(): bool
    {
        return $this->status === 'approved' && $this->effective_date <= now();
    }

    public function getIsEffectiveAttribute(): bool
    {
        return $this->status === 'implemented' && $this->effective_date <= now();
    }

    // Methods
    public function calculatePercentageChange(): float
    {
        if (!$this->old_amount || $this->old_amount == 0) {
            return 0;
        }
        return (($this->new_amount - $this->old_amount) / $this->old_amount) * 100;
    }

    public function approve($approvedBy = null, $approvalNotes = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy ?? auth()->id(),
            'approved_at' => now(),
            'approval_notes' => $approvalNotes
        ]);
    }

    public function reject($rejectedBy = null, $rejectionReason = null): void
    {
        $this->update([
            'status' => 'rejected',
            'rejected_by' => $rejectedBy ?? auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $rejectionReason
        ]);
    }

    public function implement(): void
    {
        if ($this->status !== 'approved') {
            throw new \Exception('Only approved adjustments can be implemented');
        }

        if ($this->effective_date > now()) {
            throw new \Exception('Cannot implement adjustment before effective date');
        }

        // Supersede old salary if exists
        if ($this->oldSalary) {
            $this->oldSalary->supersede($this->effective_date);
        }

        // Activate new salary
        $this->newSalary->update(['status' => 'active']);

        // Mark adjustment as implemented
        $this->update(['status' => 'implemented']);
    }

    // Static methods
    public static function generateAdjustmentNumber(): string
    {
        $prefix = 'ADJ';
        $year = Carbon::now()->year;

        $lastAdjustment = static::whereYear('created_at', $year)
                               ->orderBy('id', 'desc')
                               ->first();

        $sequence = $lastAdjustment ? (int) substr($lastAdjustment->adjustment_number, -4) + 1 : 1;

        return sprintf('%s%s%04d', $prefix, $year, $sequence);
    }
}
