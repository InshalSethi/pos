<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\HasUtcDatabaseTimezones;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    use SoftDeletes;

    use BelongsToCompany;
    use HasUtcDatabaseTimezones;
    use HasFactory;

    protected $table = 'chart_of_accounts';

    protected static function booted()
    {
        static::saved(function ($account) {
            if ($account->wasChanged(['opening_balance', 'current_balance'])) {
                \Illuminate\Support\Facades\DB::table('bank_accounts')
                    ->where('chart_account_id', $account->id)
                    ->update([
                        'opening_balance' => round((float)$account->opening_balance, 2),
                        'current_balance' => round((float)$account->current_balance, 2),
                    ]);
            }
        });
    }

    protected $fillable = [
        'company_id',
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

    protected $appends = ['calculated_balance'];

    public function getCalculatedBalanceAttribute()
    {
        if ($this->relationLoaded('children') && $this->children && $this->children->count() > 0) {
            return (float) $this->children->sum(function ($child) {
                return $child->calculated_balance;
            });
        }

        return (float) ($this->current_balance ?? $this->opening_balance ?? 0);
    }

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
        // If this account is a parent with child accounts, sum the children's balances
        if ($this->children()->exists()) {
            return (float) $this->children->sum(function ($child) use ($asOfDate) {
                return $child->calculateBalance($asOfDate);
            });
        }

        $query = $this->journalEntries()
                     ->whereHas('journalEntry', function ($q) use ($asOfDate) {
                         $q->where(function ($sub) {
                             $sub->where('status', 'posted')
                                 ->orWhere(function ($r) {
                                     $r->where('status', 'reversed')
                                       ->whereExists(function ($ex) {
                                           $ex->select(\Illuminate\Support\Facades\DB::raw(1))
                                              ->from('journal_entries as rev')
                                              ->whereColumn('rev.source_type', 'journal_entries.source_type')
                                              ->whereColumn('rev.source_id', 'journal_entries.source_id')
                                              ->where('rev.is_reversal', true)
                                              ->where('rev.status', 'posted');
                                       });
                                 });
                         });
                         if ($asOfDate) {
                             $q->where('entry_date', '<=', $asOfDate);
                         }
                     });

        $totalDebits = (float) $query->sum('debit_amount');
        $totalCredits = (float) $query->sum('credit_amount');

        // Calculate balance based on account type
        if (in_array($this->account_type, ['asset', 'expense'])) {
            $bal = (float) $this->opening_balance + $totalDebits - $totalCredits;
        } else {
            $bal = (float) $this->opening_balance + $totalCredits - $totalDebits;
        }

        return round($bal, 2);
    }

    public function updateCurrentBalance(): void
    {
        $this->current_balance = $this->calculateBalance();
        $this->save();

        // Recursively update and persist all parent accounts in the hierarchy chain
        $parent = $this->parent;
        while ($parent) {
            $parent->current_balance = $parent->calculateBalance();
            $parent->save();
            $parent = $parent->parent;
        }

        // Direct COA Hard-Sync with Banking Module (Single Source of Truth)
        \Illuminate\Support\Facades\DB::table('bank_accounts')
            ->where('chart_account_id', $this->id)
            ->update([
                'opening_balance' => round((float)$this->opening_balance, 2),
                'current_balance' => round((float)$this->current_balance, 2)
            ]);
    }

    public function getCurrentBalance(): float
    {
        return (float) ($this->current_balance ?? $this->calculateBalance());
    }

    public function getFormattedBalanceAttribute(): string
    {
        $val = (float) ($this->current_balance ?? 0);
        return $val < 0 ? '- Rs ' . number_format(abs($val), 2) : 'Rs ' . number_format($val, 2);
    }
}
