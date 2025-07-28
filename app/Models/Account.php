<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    use HasFactory;

    protected $table = 'chart_of_accounts';

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

    // Alias attributes for consistency with controller
    public function getCodeAttribute()
    {
        return $this->account_code;
    }

    public function getNameAttribute()
    {
        return $this->account_name;
    }

    public function getIsSystemAttribute()
    {
        return $this->is_system_account;
    }

    public function getParentIdAttribute()
    {
        return $this->parent_account_id;
    }

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_account_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_account_id');
    }

    public function journalEntries(): HasMany
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

    // Methods
    public function calculateBalance($asOfDate = null): float
    {
        $query = $this->journalEntries()
                     ->whereHas('journalEntry', function ($q) use ($asOfDate) {
                         $q->where('status', 'posted');
                         if ($asOfDate) {
                             $q->where('entry_date', '<=', $asOfDate);
                         }
                     });

        $totalDebits = $query->sum('debit_amount');
        $totalCredits = $query->sum('credit_amount');

        // Calculate balance based on account type
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

    public function getFormattedBalanceAttribute(): string
    {
        return '$' . number_format($this->current_balance, 2);
    }
}
