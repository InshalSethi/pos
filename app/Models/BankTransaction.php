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
        'journal_entry_line_id',
        'transaction_date',
        'transaction_type',
        'amount',
        'description',
        'reference',
        'check_number',
        'is_reconciled',
        'reconciled_date',
        'bank_reference',
        'category',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'reconciled_date' => 'date',
        'amount' => 'decimal:2',
        'is_reconciled' => 'boolean',
    ];

    // Relationships
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function journalEntryLine(): BelongsTo
    {
        return $this->belongsTo(JournalEntryLine::class);
    }

    // Scopes
    public function scopeReconciled($query)
    {
        return $query->where('is_reconciled', true);
    }

    public function scopeUnreconciled($query)
    {
        return $query->where('is_reconciled', false);
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
    public function markAsReconciled($reconciledDate = null): void
    {
        $this->update([
            'is_reconciled' => true,
            'reconciled_date' => $reconciledDate ?? now()
        ]);
    }

    public function markAsUnreconciled(): void
    {
        $this->update([
            'is_reconciled' => false,
            'reconciled_date' => null
        ]);
    }

    public function getRunningBalance(): float
    {
        // Calculate running balance up to this transaction
        $previousTransactions = BankTransaction::where('bank_account_id', $this->bank_account_id)
                                              ->where('transaction_date', '<=', $this->transaction_date)
                                              ->where('id', '<=', $this->id)
                                              ->get();

        $balance = $this->bankAccount->opening_balance;

        foreach ($previousTransactions as $transaction) {
            if ($transaction->is_credit) {
                $balance += $transaction->amount;
            } else {
                $balance -= $transaction->amount;
            }
        }

        return $balance;
    }
}
