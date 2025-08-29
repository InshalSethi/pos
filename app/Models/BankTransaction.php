<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BankTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_account_id',
        'transaction_date',
        'reference_number',
        'description',
        'transaction_type',
        'amount',
        'running_balance',
        'status',
        'journal_entry_id',
        'partner_type',
        'partner_id',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'running_balance' => 'decimal:2',
    ];

    // Relationships
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function partner()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeReconciled($query)
    {
        return $query->where('status', 'reconciled');
    }

    public function scopeUnreconciled($query)
    {
        return $query->whereIn('status', ['pending', 'cleared']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCleared($query)
    {
        return $query->where('status', 'cleared');
    }

    public function scopeDebits($query)
    {
        return $query->where('transaction_type', 'debit');
    }

    public function scopeCredits($query)
    {
        return $query->where('transaction_type', 'credit');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    // Accessors
    public function getFormattedAmountAttribute(): string
    {
        $sign = $this->transaction_type === 'debit' ? '-' : '+';
        return $sign . '$' . number_format($this->amount, 2);
    }

    public function getTransactionTypeDisplayAttribute(): string
    {
        return match($this->transaction_type) {
            'debit' => 'Debit',
            'credit' => 'Credit',
            default => ucfirst($this->transaction_type)
        };
    }

    public function getIsDebitAttribute(): bool
    {
        return $this->transaction_type === 'debit';
    }

    public function getIsCreditAttribute(): bool
    {
        return $this->transaction_type === 'credit';
    }

    public function getCategoryDisplayAttribute(): string
    {
        return match($this->category) {
            'deposit' => 'Deposit',
            'withdrawal' => 'Withdrawal',
            'transfer' => 'Transfer',
            'fee' => 'Bank Fee',
            'interest' => 'Interest',
            'check' => 'Check',
            'ach' => 'ACH Transfer',
            'wire' => 'Wire Transfer',
            'card' => 'Card Transaction',
            'other' => 'Other',
            default => ucfirst($this->category ?? 'Other')
        };
    }

    // Methods
    public function markAsReconciled(): void
    {
        $this->update(['status' => 'reconciled']);
    }

    public function markAsCleared(): void
    {
        $this->update(['status' => 'cleared']);
    }

    public function markAsPending(): void
    {
        $this->update(['status' => 'pending']);
    }

    public function calculateRunningBalance(): float
    {
        // Calculate running balance up to this transaction
        $previousTransactions = BankTransaction::where('bank_account_id', $this->bank_account_id)
                                              ->where('transaction_date', '<=', $this->transaction_date)
                                              ->where('id', '<=', $this->id)
                                              ->orderBy('transaction_date')
                                              ->orderBy('id')
                                              ->get();

        $balance = $this->bankAccount->opening_balance;

        foreach ($previousTransactions as $transaction) {
            if ($transaction->transaction_type === 'credit') {
                $balance += $transaction->amount;
            } else {
                $balance -= $transaction->amount;
            }
        }

        return $balance;
    }

    // Accessor for running balance
    public function getRunningBalanceAttribute(): float
    {
        return $this->attributes['running_balance'] ?? $this->calculateRunningBalance();
    }
}
