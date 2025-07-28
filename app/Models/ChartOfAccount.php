<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_code',
        'account_name',
        'account_type',
        'account_subtype',
        'description',
        'is_active',
        'is_system_account',
        'opening_balance',
        'current_balance',
        'parent_account_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system_account' => 'boolean',
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_account_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_account_id');
    }

    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class, 'chart_account_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('account_type', $type);
    }

    public function scopeAssets($query)
    {
        return $query->where('account_type', 'asset');
    }

    public function scopeLiabilities($query)
    {
        return $query->where('account_type', 'liability');
    }

    public function scopeEquity($query)
    {
        return $query->where('account_type', 'equity');
    }

    public function scopeRevenue($query)
    {
        return $query->where('account_type', 'revenue');
    }

    public function scopeExpenses($query)
    {
        return $query->where('account_type', 'expense');
    }

    // Accessors
    public function getFormattedBalanceAttribute(): string
    {
        return '$' . number_format($this->current_balance, 2);
    }

    public function getAccountTypeDisplayAttribute(): string
    {
        return match($this->account_type) {
            'asset' => 'Asset',
            'liability' => 'Liability',
            'equity' => 'Equity',
            'revenue' => 'Revenue',
            'expense' => 'Expense',
            default => ucfirst($this->account_type)
        };
    }

    public function getAccountSubtypeDisplayAttribute(): string
    {
        return match($this->account_subtype) {
            'current_asset' => 'Current Asset',
            'fixed_asset' => 'Fixed Asset',
            'other_asset' => 'Other Asset',
            'current_liability' => 'Current Liability',
            'long_term_liability' => 'Long-term Liability',
            'other_liability' => 'Other Liability',
            'equity' => 'Equity',
            'operating_revenue' => 'Operating Revenue',
            'other_revenue' => 'Other Revenue',
            'cost_of_goods_sold' => 'Cost of Goods Sold',
            'operating_expense' => 'Operating Expense',
            'other_expense' => 'Other Expense',
            default => ucfirst(str_replace('_', ' ', $this->account_subtype))
        };
    }

    // Methods
    public function calculateBalance($asOfDate = null): float
    {
        $query = $this->journalEntryLines()
                     ->whereHas('journalEntry', function ($q) use ($asOfDate) {
                         $q->where('status', 'posted');
                         if ($asOfDate) {
                             $q->where('entry_date', '<=', $asOfDate);
                         }
                     });

        $totalDebits = $query->sum('debit_amount');
        $totalCredits = $query->sum('credit_amount');

        // Calculate balance based on account type
        // Assets and Expenses: Debit increases, Credit decreases
        // Liabilities, Equity, Revenue: Credit increases, Debit decreases
        if (in_array($this->account_type, ['asset', 'expense'])) {
            return $this->opening_balance + $totalDebits - $totalCredits;
        } else {
            return $this->opening_balance + $totalCredits - $totalDebits;
        }
    }

    public function updateCurrentBalance(): void
    {
        $this->current_balance = $this->calculateBalance();
        $this->save();
    }

    public function getFullCodeAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->full_code . '.' . $this->account_code;
        }
        return $this->account_code;
    }

    public function isDebitAccount(): bool
    {
        return in_array($this->account_type, ['asset', 'expense']);
    }

    public function isCreditAccount(): bool
    {
        return in_array($this->account_type, ['liability', 'equity', 'revenue']);
    }
}
