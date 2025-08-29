<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;

class PaymentReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'receipt_type',
        'reference_type',
        'reference_id',
        'reference_number',
        'amount',
        'receipt_date',
        'payment_method',
        'transaction_reference',
        'description',
        'notes',
        'bank_account_id',
        'payer_type',
        'payer_id',
        'payer_name',
        'status',
        'created_by',
        'verified_by',
        'verified_at',
        'verification_notes',
        'deposited_by',
        'deposited_at',
        'journal_entry_id',
        'bank_transaction_id',
        'invoice_allocations',
        'additional_data',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'amount' => 'decimal:2',
        'verified_at' => 'datetime',
        'deposited_at' => 'datetime',
        'invoice_allocations' => 'array',
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

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function depositedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deposited_by');
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

    public function payer(): MorphTo
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

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopeDeposited($query)
    {
        return $query->where('status', 'deposited');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('receipt_type', $type);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('receipt_date', [$startDate, $endDate]);
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
            'verified' => 'bg-blue-100 text-blue-800',
            'deposited' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
        ];

        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getReceiptTypeDisplayAttribute(): string
    {
        $types = [
            'customer_payment' => 'Customer Payment',
            'customer_advance' => 'Customer Advance',
            'supplier_refund' => 'Supplier Refund',
            'supplier_rebate' => 'Supplier Rebate',
            'interest_income' => 'Interest Income',
            'rental_income' => 'Rental Income',
            'commission_income' => 'Commission Income',
            'asset_sale' => 'Asset Sale',
            'bank_transfer_in' => 'Bank Transfer In',
            'cash_deposit' => 'Cash Deposit',
            'miscellaneous_income' => 'Miscellaneous Income',
            'other_receipt' => 'Other Receipt',
        ];

        return $types[$this->receipt_type] ?? 'Unknown';
    }

    // Methods
    public function verify($userId, $notes = null): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->update([
            'status' => 'verified',
            'verified_by' => $userId,
            'verified_at' => now(),
            'verification_notes' => $notes,
        ]);

        return true;
    }

    public function markAsDeposited($userId, $journalEntryId = null, $bankTransactionId = null): bool
    {
        if (!in_array($this->status, ['verified', 'pending'])) {
            return false;
        }

        $this->update([
            'status' => 'deposited',
            'deposited_by' => $userId,
            'deposited_at' => now(),
            'journal_entry_id' => $journalEntryId,
            'bank_transaction_id' => $bankTransactionId,
        ]);

        return true;
    }

    public function cancel(): bool
    {
        if (in_array($this->status, ['deposited', 'cancelled'])) {
            return false;
        }

        $this->update(['status' => 'cancelled']);
        return true;
    }

    // Generate receipt number
    public static function generateReceiptNumber(): string
    {
        $prefix = 'RCP';
        $year = Carbon::now()->year;
        $month = Carbon::now()->format('m');

        $lastReceipt = static::whereYear('created_at', $year)
                           ->whereMonth('created_at', $month)
                           ->orderBy('id', 'desc')
                           ->first();

        $sequence = $lastReceipt ? (int) substr($lastReceipt->receipt_number, -4) + 1 : 1;

        return sprintf('%s%s%s%04d', $prefix, $year, $month, $sequence);
    }

    // Check if receipt can be edited
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'pending']);
    }

    // Check if receipt can be deleted
    public function canBeDeleted(): bool
    {
        return in_array($this->status, ['draft', 'pending']);
    }

    // Check if receipt can be verified
    public function canBeVerified(): bool
    {
        return $this->status === 'pending';
    }

    // Check if receipt can be deposited
    public function canBeDeposited(): bool
    {
        return in_array($this->status, ['verified', 'pending']);
    }

    // Get allocated amount for specific invoice
    public function getAllocatedAmountForInvoice($invoiceId): float
    {
        if (!$this->invoice_allocations) {
            return 0;
        }

        $allocation = collect($this->invoice_allocations)
            ->firstWhere('invoice_id', $invoiceId);

        return $allocation ? (float) $allocation['amount'] : 0;
    }

    // Get total allocated amount
    public function getTotalAllocatedAmount(): float
    {
        if (!$this->invoice_allocations) {
            return 0;
        }

        return collect($this->invoice_allocations)
            ->sum('amount');
    }

    // Get unallocated amount
    public function getUnallocatedAmount(): float
    {
        return $this->amount - $this->getTotalAllocatedAmount();
    }
}
