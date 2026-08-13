<?php

namespace App\Models;

use App\Traits\BelongsToCompany;

use App\Traits\HasUtcDatabaseTimezones;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BankAccount extends Model
{
    use BelongsToCompany;
    use HasUtcDatabaseTimezones;
    use HasFactory;

    protected static function booted()
    {
        static::creating(function ($bankAccount) {
            if ($bankAccount->current_balance === null || $bankAccount->current_balance === '') {
                $bankAccount->current_balance = $bankAccount->opening_balance ?? 0;
            }
        });

        static::saved(function ($bankAccount) {
            if ($bankAccount->chart_account_id) {
                $balanceToSync = round((float)($bankAccount->current_balance ?? $bankAccount->opening_balance ?? 0), 2);
                $openingToSync = round((float)($bankAccount->opening_balance ?? 0), 2);
                
                $bankName = trim($bankAccount->bank_name ?? '');
                $accountName = trim($bankAccount->account_name ?? '');

                if ($bankName !== '' && $accountName !== '' && strcasecmp($bankName, $accountName) !== 0) {
                    $formattedName = "{$accountName} ({$bankName})";
                } else {
                    $formattedName = $accountName ?: $bankName;
                }

                \Illuminate\Support\Facades\DB::table('chart_of_accounts')
                    ->where('id', $bankAccount->chart_account_id)
                    ->update([
                        'account_name' => $formattedName,
                        'opening_balance' => $openingToSync,
                        'current_balance' => $balanceToSync,
                    ]);
            }
        });
    }

    protected $fillable = [
        'account_name',
        'bank_name',
        'bank_phone',
        'bank_address',
        'account_number',
        'account_type',
        'chart_account_id',
        'routing_number',
        'swift_code',
        'iban',
        'currency',
        'opening_balance',
        'current_balance',
        'opening_date',
        'description',
        'is_active',
        'is_default',
        'last_reconciled_date',
        'last_statement_balance',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'opening_balance' => 'decimal:2',
        'last_statement_balance' => 'decimal:2',
        'opening_date' => 'date:Y-m-d',
        'last_reconciled_date' => 'date:Y-m-d',
    ];

    protected $appends = [
        'masked_account_number',
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
    public function getMaskedAccountNumberAttribute(): string
    {
        if (!$this->account_number) return '';
        $last4 = substr((string)$this->account_number, -4);
        return '****' . $last4;
    }

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
        $hasTransactions = $this->bankTransactions()->exists();
        if (!$hasTransactions) {
            return round((float) ($this->opening_balance ?? 0), 2);
        }

        $totalDebits = (float) $this->bankTransactions()
                           ->where('transaction_type', 'debit')
                           ->sum('amount');

        $totalCredits = (float) $this->bankTransactions()
                            ->where('transaction_type', 'credit')
                            ->sum('amount');

        // Asset Accounts (checking, savings, cash, vault, etc.): Credits (money in) increase balance, Debits (money out) decrease balance.
        // Liability Accounts (credit cards): Debits (charges) increase owed balance, Credits (payments) decrease balance.
        $isLiability = in_array(strtolower($this->account_type ?? ''), ['credit_card', 'card', 'liability', 'loan']);
        if (!$isLiability) {
            $bal = (float) $this->opening_balance + $totalCredits - $totalDebits;
        } else {
            $bal = (float) $this->opening_balance + $totalDebits - $totalCredits;
        }

        return round($bal, 2);
    }

    public function calculateReconciledBalance(): float
    {
        $totalDebits = (float) $this->bankTransactions()
                           ->where('transaction_type', 'debit')
                           ->where('status', 'reconciled')
                           ->sum('amount');

        $totalCredits = (float) $this->bankTransactions()
                            ->where('transaction_type', 'credit')
                            ->where('status', 'reconciled')
                            ->sum('amount');

        $isLiability = in_array(strtolower($this->account_type ?? ''), ['credit_card', 'card', 'liability', 'loan']);
        if (!$isLiability) {
            $bal = (float) $this->opening_balance + $totalCredits - $totalDebits;
        } else {
            $bal = (float) $this->opening_balance + $totalDebits - $totalCredits;
        }

        return round($bal, 2);
    }

    public function getUnreconciledTransactionsCount(): int
    {
        return $this->bankTransactions()->whereIn('status', ['pending', 'cleared'])->count();
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
