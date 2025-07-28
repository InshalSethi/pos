<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use App\Events\ExpenseApproved;
use App\Events\ExpensePaid;
use App\Events\ExpenseRejected;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_number',
        'category_id',
        'employee_id',
        'user_id',
        'amount',
        'expense_date',
        'title',
        'description',
        'receipt_image',
        'receipt_images',
        'status',
        'payment_method',
        'reference_number',
        'vendor_name',
        'notes',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'paid_by',
        'paid_at',
        'payment_reference',
        'journal_entry_id',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'receipt_images' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
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

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['submitted', 'approved']);
    }

    // Methods
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'rejected']);
    }

    public function canBeSubmitted(): bool
    {
        return $this->status === 'draft';
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'submitted';
    }

    public function canBeRejected(): bool
    {
        return $this->status === 'submitted';
    }

    public function canBePaid(): bool
    {
        return $this->status === 'approved';
    }

    public function submit($userId = null): bool
    {
        if (!$this->canBeSubmitted()) {
            return false;
        }

        $this->update([
            'status' => 'submitted',
            'submitted_by' => $userId ?? auth()->id(),
            'submitted_at' => now(),
        ]);

        return true;
    }

    public function approve($userId = null, $notes = null): bool
    {
        if (!$this->canBeApproved()) {
            return false;
        }

        $this->update([
            'status' => 'approved',
            'approved_by' => $userId ?? auth()->id(),
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);

        // Fire event for accounting integration
        event(new ExpenseApproved($this));

        return true;
    }

    public function reject($userId = null, $reason = null): bool
    {
        if (!$this->canBeRejected()) {
            return false;
        }

        $this->update([
            'status' => 'rejected',
            'rejected_by' => $userId ?? auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        // Fire event for accounting integration
        event(new ExpenseRejected($this));

        return true;
    }

    public function markAsPaid($userId = null, $paymentReference = null): bool
    {
        if (!$this->canBePaid()) {
            return false;
        }

        $this->update([
            'status' => 'paid',
            'paid_by' => $userId ?? auth()->id(),
            'paid_at' => now(),
            'payment_reference' => $paymentReference,
        ]);

        // Fire event for accounting integration
        event(new ExpensePaid($this));

        return true;
    }

    // Generate expense number
    public static function generateExpenseNumber(): string
    {
        $prefix = 'EXP';
        $year = Carbon::now()->year;
        $month = Carbon::now()->format('m');

        $lastExpense = static::whereYear('created_at', $year)
                           ->whereMonth('created_at', $month)
                           ->orderBy('id', 'desc')
                           ->first();

        $sequence = $lastExpense ? (int) substr($lastExpense->expense_number, -4) + 1 : 1;

        return sprintf('%s%s%s%04d', $prefix, $year, $month, $sequence);
    }
}
