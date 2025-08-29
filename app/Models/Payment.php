<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'payment_type',
        'reference_type',
        'reference_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'description',
        'notes',
        'bank_account_id',
        'payee_type',
        'payee_id',
        'payee_name',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'approval_notes',
        'paid_by',
        'paid_at',
        'journal_entry_id',
        'bank_transaction_id',
        'additional_data',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'additional_data' => 'array',
    ];

    // Relationships
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
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

    public function bankTransaction(): BelongsTo
    {
        return $this->belongsTo(BankTransaction::class);
    }

    // Polymorphic relationships
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function payee(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('payment_type', $type);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    // Accessors
    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->amount, 2);
    }

    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'draft' => 'bg-gray-100 text-gray-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-blue-100 text-blue-800',
            'paid' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
        ];

        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getPaymentTypeDisplayAttribute(): string
    {
        $types = [
            'supplier_payment' => 'Supplier Payment',
            'expense_payment' => 'Expense Payment',
            'salary_payment' => 'Salary Payment',
            'sale_return_payment' => 'Sale Return Payment',
            'purchase_invoice_payment' => 'Purchase Invoice Payment',
            'other_payment' => 'Other Payment',
        ];

        return $types[$this->payment_type] ?? 'Unknown';
    }

    // Methods
    public function approve($userId, $notes = null): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);

        return true;
    }

    public function markAsPaid($userId, $journalEntryId = null, $bankTransactionId = null): bool
    {
        if (!in_array($this->status, ['approved', 'pending'])) {
            return false;
        }

        $this->update([
            'status' => 'paid',
            'paid_by' => $userId,
            'paid_at' => now(),
            'journal_entry_id' => $journalEntryId,
            'bank_transaction_id' => $bankTransactionId,
        ]);

        return true;
    }

    public function cancel(): bool
    {
        if (in_array($this->status, ['paid', 'cancelled'])) {
            return false;
        }

        $this->update(['status' => 'cancelled']);
        return true;
    }

    // Generate payment number
    public static function generatePaymentNumber(): string
    {
        $prefix = 'PAY';
        $year = Carbon::now()->year;
        $month = Carbon::now()->format('m');

        $lastPayment = static::whereYear('created_at', $year)
                           ->whereMonth('created_at', $month)
                           ->orderBy('id', 'desc')
                           ->first();

        $sequence = $lastPayment ? (int) substr($lastPayment->payment_number, -4) + 1 : 1;

        return sprintf('%s%s%s%04d', $prefix, $year, $month, $sequence);
    }

    // Check if payment can be edited
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'pending']);
    }

    // Check if payment can be deleted
    public function canBeDeleted(): bool
    {
        return in_array($this->status, ['draft', 'pending']);
    }

    // Check if payment can be approved
    public function canBeApproved(): bool
    {
        return $this->status === 'pending';
    }

    // Check if payment can be paid
    public function canBePaid(): bool
    {
        return in_array($this->status, ['approved', 'pending']);
    }
}
