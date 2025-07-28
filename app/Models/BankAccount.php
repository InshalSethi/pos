<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_name',
        'bank_name',
        'account_number',
        'account_type',
        'chart_account_id',
        'routing_number',
        'swift_code',
        'iban',
        'opening_balance',
        'opening_date',
        'description',
        'is_active',
        'last_reconciled_date',
        'last_statement_balance',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'opening_balance' => 'decimal:2',
        'last_statement_balance' => 'decimal:2',
        'opening_date' => 'date',
        'last_reconciled_date' => 'date',
    ];

    // Relationships
    public function chartAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'chart_account_id');
    }

    public function bankTransactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeChecking($query)
    {
        return $query->where('account_type', 'checking');
    }

    public function scopeSavings($query)
    {
        return $query->where('account_type', 'savings');
    }

    // Accessors
    public function getFormattedAccountNumberAttribute(): string
    {
        $number = $this->account_number;
        if (strlen($number) > 4) {
            return '****' . substr($number, -4);
        }
        return $number;
    }

    public function getAccountTypeDisplayAttribute(): string
    {
        return match($this->account_type) {
            'checking' => 'Checking Account',
            'savings' => 'Savings Account',
            'credit_card' => 'Credit Card',
            'line_of_credit' => 'Line of Credit',
            'other' => 'Other',
            default => ucfirst($this->account_type)
        };
    }

    public function getFormattedOpeningBalanceAttribute(): string
    {
        return '$' . number_format($this->opening_balance, 2);
    }

    // Methods
    public function calculateBalance(): float
    {
        $totalDebits = $this->bankTransactions()
                           ->where('transaction_type', 'debit')
                           ->sum('amount');

        $totalCredits = $this->bankTransactions()
                            ->where('transaction_type', 'credit')
                            ->sum('amount');

        // For asset accounts (checking, savings): Credits increase, Debits decrease
        // For liability accounts (credit cards): Debits increase, Credits decrease
        if (in_array($this->account_type, ['checking', 'savings'])) {
            return $this->opening_balance + $totalCredits - $totalDebits;
        } else {
            return $this->opening_balance + $totalDebits - $totalCredits;
        }
    }

    public function calculateReconciledBalance(): float
    {
        $totalDebits = $this->bankTransactions()
                           ->where('transaction_type', 'debit')
                           ->where('is_reconciled', true)
                           ->sum('amount');

        $totalCredits = $this->bankTransactions()
                            ->where('transaction_type', 'credit')
                            ->where('is_reconciled', true)
                            ->sum('amount');

        if (in_array($this->account_type, ['checking', 'savings'])) {
            return $this->opening_balance + $totalCredits - $totalDebits;
        } else {
            return $this->opening_balance + $totalDebits - $totalCredits;
        }
    }

    public function getUnreconciledTransactionsCount(): int
    {
        return $this->bankTransactions()->where('is_reconciled', false)->count();
    }

    public function getFormattedBalanceAttribute(): string
    {
        return '$' . number_format($this->calculateBalance(), 2);
    }

    public function isOverdrawn(): bool
    {
        return $this->calculateBalance() < 0 && in_array($this->account_type, ['checking', 'savings']);
    }
}
