<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::created(function ($company) {
            // Seed default measurement units
            $defaultUnits = [
                ['name' => 'Pieces', 'short_name' => 'PCS'],
                ['name' => 'Kilograms', 'short_name' => 'KG'],
                ['name' => 'Liters', 'short_name' => 'LTR'],
                ['name' => 'Boxes', 'short_name' => 'BOX'],
            ];

            foreach ($defaultUnits as $unit) {
                \App\Models\Unit::create([
                    'company_id' => $company->id,
                    'name' => $unit['name'],
                    'short_name' => $unit['short_name'],
                    'is_active' => true,
                ]);
            }

            // Seed default Chart of Accounts for the new company
            $defaultAccounts = [
                ['account_code' => '1010', 'account_name' => 'Cash',                'account_type' => 'asset',     'account_subtype' => 'cash_and_bank',      'is_system_account' => true],
                ['account_code' => '1020', 'account_name' => 'Bank Account',         'account_type' => 'asset',     'account_subtype' => 'cash_and_bank',      'is_system_account' => true],
                ['account_code' => '1030', 'account_name' => 'Accounts Receivable',  'account_type' => 'asset',     'account_subtype' => 'current_asset',      'is_system_account' => true],
                ['account_code' => '1040', 'account_name' => 'Inventory',            'account_type' => 'asset',     'account_subtype' => 'current_asset',      'is_system_account' => true],
                ['account_code' => '2010', 'account_name' => 'Accounts Payable',     'account_type' => 'liability', 'account_subtype' => 'current_liability',  'is_system_account' => true],
                ['account_code' => '3010', 'account_name' => "Owner's Equity",       'account_type' => 'equity',    'account_subtype' => 'equity',             'is_system_account' => true],
                ['account_code' => '4010', 'account_name' => 'Sales Revenue',        'account_type' => 'revenue',   'account_subtype' => 'operating_income',   'is_system_account' => true],
            ];

            $cashCoaAccount = null;
            foreach ($defaultAccounts as $account) {
                $createdAccount = \App\Models\Account::create(array_merge($account, [
                    'company_id' => $company->id,
                    'is_active' => true,
                    'opening_balance' => 0,
                    'current_balance' => 0,
                ]));

                if ($account['account_code'] === '1010') {
                    $cashCoaAccount = $createdAccount;
                }
            }

            if ($cashCoaAccount) {
                \App\Models\BankAccount::create([
                    'company_id' => $company->id,
                    'account_name' => 'Cash Account',
                    'bank_name' => 'Cash',
                    'account_number' => 'CASH-001',
                    'account_type' => 'checking',
                    'currency' => $company->base_currency ?: 'USD',
                    'opening_balance' => 0.00,
                    'current_balance' => 0.00,
                    'opening_date' => date('Y-01-01'),
                    'description' => 'Default system Cash Account for daily transactions and POS payments.',
                    'is_active' => true,
                    'is_default' => true,
                    'chart_account_id' => $cashCoaAccount->id,
                ]);
            }

            // Seed default Warehouse for the new company
            \App\Models\Warehouse::create([
                'company_id' => $company->id,
                'name' => 'Main Warehouse',
                'code' => 'MWH-001',
                'email' => $company->company_email ?: 'warehouse@example.com',
                'phone' => $company->company_phone ?: '+1 (555) 019-2834',
                'address' => $company->business_address ?: '100 Central Logistics Parkway, Industrial Zone',
                'city' => 'New York',
                'state' => 'NY',
                'zip_code' => '10001',
                'country' => $company->country ?: 'United States',
                'is_default' => true,
                'is_active' => true,
                'is_saleable' => true,
            ]);
        });
    }

    protected $fillable = [
        'user_id',
        'company_name',
        'company_email',
        'company_phone',
        'company_logo',
        'registration_number',
        'tax_number',
        'business_address',
        'owner_role',
        'team_size',
        'intended_tasks',
        'business_type',
        'business_scale',
        'country',
        'system_language',
        'base_currency',
        'timezone_offset',
        'fiscal_year_start',
        'status',
        'draft_step',
    ];

    protected $casts = [
        'intended_tasks' => 'array',
        'deleted_at' => 'datetime',
    ];

    protected $appends = [
        'fiscal_year_end',
        'can_edit_fiscal_year',
        'formatted_fiscal_year_cycle',
    ];

    /**
     * Get calculated fiscal year end date (12 months minus 1 day from fiscal_year_start).
     */
    public function getFiscalYearEndAttribute(): ?string
    {
        if (!$this->fiscal_year_start) {
            return null;
        }
        return \Carbon\Carbon::parse($this->fiscal_year_start)
            ->addMonths(12)
            ->subDay()
            ->format('Y-m-d');
    }

    /**
     * Check if fiscal year start can be edited (true ONLY if journal_entries table is empty for company).
     */
    public function getCanEditFiscalYearAttribute(): bool
    {
        return !\App\Models\JournalEntry::where('company_id', $this->id)->exists();
    }

    /**
     * Get formatted fiscal year cycle string (e.g., 'Jan 1, 2026 - Dec 31, 2026').
     */
    public function getFormattedFiscalYearCycleAttribute(): ?string
    {
        if (!$this->fiscal_year_start) {
            return null;
        }
        $start = \Carbon\Carbon::parse($this->fiscal_year_start);
        $end = $start->copy()->addMonths(12)->subDay();
        return $start->format('M j, Y') . ' - ' . $end->format('M j, Y');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }
}
