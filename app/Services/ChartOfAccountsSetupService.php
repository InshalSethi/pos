<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountingSetting;
use App\Models\Scopes\CompanyScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChartOfAccountsSetupService
{
    /**
     * Standard GAAP/IFRS default account definitions for POS ERP system.
     */
    protected static function getStandardAccounts(): array
    {
        return [
            // ASSETS (1000 - 1999)
            'current_assets' => [
                'account_code'      => '1000',
                'account_name'      => 'Current Assets',
                'account_type'      => 'asset',
                'account_subtype'   => 'current_asset',
                'description'       => 'Header for short-term liquid assets',
                'is_system_account' => true,
                'parent_code'       => null,
            ],
            'cash' => [
                'account_code'      => '1010',
                'account_name'      => 'Cash on Hand',
                'account_type'      => 'asset',
                'account_subtype'   => 'cash_and_bank',
                'description'       => 'Main counter cash and vault balance',
                'is_system_account' => true,
                'parent_code'       => '1000',
            ],
            'bank' => [
                'account_code'      => '1020',
                'account_name'      => 'Main Bank Account',
                'account_type'      => 'asset',
                'account_subtype'   => 'cash_and_bank',
                'description'       => 'Operating bank account for business transactions',
                'is_system_account' => true,
                'parent_code'       => '1000',
            ],
            'receivable' => [
                'account_code'      => '1030',
                'account_name'      => 'Accounts Receivable',
                'account_type'      => 'asset',
                'account_subtype'   => 'current_asset',
                'description'       => 'Trade receivables from credit sales',
                'is_system_account' => true,
                'parent_code'       => '1000',
            ],
            'inventory' => [
                'account_code'      => '1040',
                'account_name'      => 'Inventory on Hand',
                'account_type'      => 'asset',
                'account_subtype'   => 'current_asset',
                'description'       => 'Asset value of merchandise stock on hand',
                'is_system_account' => true,
                'parent_code'       => '1000',
            ],
            'input_tax' => [
                'account_code'      => '1070',
                'account_name'      => 'Input Sales Tax / VAT Recoverable',
                'account_type'      => 'asset',
                'account_subtype'   => 'current_asset',
                'description'       => 'Sales tax paid on purchases eligible for input credit',
                'is_system_account' => true,
                'parent_code'       => '1000',
            ],
            'supplier_advance' => [
                'account_code'      => '1310',
                'account_name'      => 'Advance to Suppliers',
                'account_type'      => 'asset',
                'account_subtype'   => 'current_asset',
                'description'       => 'Advance payments and overpayments made to suppliers for future merchandise orders',
                'is_system_account' => true,
                'parent_code'       => '1000',
            ],

            // LIABILITIES (2000 - 2999)
            'current_liabilities' => [
                'account_code'      => '2000',
                'account_name'      => 'Current Liabilities',
                'account_type'      => 'liability',
                'account_subtype'   => 'current_liability',
                'description'       => 'Header for short-term obligations',
                'is_system_account' => true,
                'parent_code'       => null,
            ],
            'payable' => [
                'account_code'      => '2010',
                'account_name'      => 'Accounts Payable',
                'account_type'      => 'liability',
                'account_subtype'   => 'current_liability',
                'description'       => 'Trade payables due to suppliers',
                'is_system_account' => true,
                'parent_code'       => '2000',
            ],
            'output_tax' => [
                'account_code'      => '2020',
                'account_name'      => 'Output Sales Tax / VAT Payable',
                'account_type'      => 'liability',
                'account_subtype'   => 'current_liability',
                'description'       => 'Sales tax collected from customers due to tax authority',
                'is_system_account' => true,
                'parent_code'       => '2000',
            ],

            // EQUITY (3000 - 3999)
            'equity_header' => [
                'account_code'      => '3000',
                'account_name'      => "Owner's Equity",
                'account_type'      => 'equity',
                'account_subtype'   => 'equity',
                'description'       => 'Header for capital and retained earnings',
                'is_system_account' => true,
                'parent_code'       => null,
            ],
            'capital' => [
                'account_code'      => '3010',
                'account_name'      => "Owner's Capital",
                'account_type'      => 'equity',
                'account_subtype'   => 'owner_equity',
                'description'       => 'Capital invested by business owner',
                'is_system_account' => false,
                'parent_code'       => '3000',
            ],
            'retained_earnings' => [
                'account_code'      => '3020',
                'account_name'      => 'Retained Earnings',
                'account_type'      => 'equity',
                'account_subtype'   => 'equity',
                'description'       => 'Accumulated net profit carried forward',
                'is_system_account' => true,
                'parent_code'       => '3000',
            ],

            // REVENUE (4000 - 4999)
            'revenue_header' => [
                'account_code'      => '4000',
                'account_name'      => 'Operating Revenue',
                'account_type'      => 'revenue',
                'account_subtype'   => 'operating_revenue',
                'description'       => 'Header for primary sales income',
                'is_system_account' => true,
                'parent_code'       => null,
            ],
            'sales_revenue' => [
                'account_code'      => '4010',
                'account_name'      => 'Sales Revenue',
                'account_type'      => 'revenue',
                'account_subtype'   => 'operating_revenue',
                'description'       => 'Gross sales revenue from POS and store orders',
                'is_system_account' => true,
                'parent_code'       => '4000',
            ],
            'sales_discounts' => [
                'account_code'      => '4050',
                'account_name'      => 'Sales Discounts Allowed',
                'account_type'      => 'revenue',
                'account_subtype'   => 'operating_revenue',
                'description'       => 'Contra-revenue account for checkout price cuts',
                'is_system_account' => false,
                'parent_code'       => '4000',
            ],
            'sales_returns' => [
                'account_code'      => '4060',
                'account_name'      => 'Sales Returns & Allowances',
                'account_type'      => 'revenue',
                'account_subtype'   => 'operating_revenue',
                'description'       => 'Contra-revenue account for refunded customer sales',
                'is_system_account' => true,
                'parent_code'       => '4000',
            ],

            // EXPENSES (5000 - 6999)
            'cogs_header' => [
                'account_code'      => '5000',
                'account_name'      => 'Cost of Goods Sold',
                'account_type'      => 'expense',
                'account_subtype'   => 'cost_of_goods_sold',
                'description'       => 'Direct product procurement and inventory costs',
                'is_system_account' => true,
                'parent_code'       => null,
            ],
            'cogs' => [
                'account_code'      => '5010',
                'account_name'      => 'Cost of Goods Sold - Finished Goods',
                'account_type'      => 'expense',
                'account_subtype'   => 'cost_of_goods_sold',
                'description'       => 'Direct cost of inventory sold during transactions',
                'is_system_account' => true,
                'parent_code'       => '5000',
            ],
            'operating_expenses' => [
                'account_code'      => '6000',
                'account_name'      => 'Operating Expenses',
                'account_type'      => 'expense',
                'account_subtype'   => 'operating_expense',
                'description'       => 'Header for general store operational costs',
                'is_system_account' => true,
                'parent_code'       => null,
            ],
            'salaries_expense' => [
                'account_code'      => '6030',
                'account_name'      => 'Salaries & Wages Expense',
                'account_type'      => 'expense',
                'account_subtype'   => 'operating_expense',
                'description'       => 'Monthly staff wages and payroll expense',
                'is_system_account' => true,
                'parent_code'       => '6000',
            ],
        ];
    }

    /**
     * Setup default Chart of Accounts and map them to Accounting Settings for a company.
     * Uses firstOrCreate for idempotent, enterprise-safe setup.
     *
     * @param int $companyId
     * @return AccountingSetting
     */
    public static function setupForCompany(int $companyId): AccountingSetting
    {
        return DB::transaction(function () use ($companyId) {
            // Also delegate to master ChartOfAccountService to seed full GAAP accounts if available
            ChartOfAccountService::ensureDefaultAccountsForCompany($companyId);

            $accountDefs = static::getStandardAccounts();
            $createdAccounts = [];

            // 1. First pass: Root accounts (parent_code === null)
            foreach ($accountDefs as $key => $def) {
                if ($def['parent_code'] !== null) {
                    continue;
                }

                $account = Account::withoutGlobalScope(CompanyScope::class)->firstOrCreate(
                    [
                        'company_id'   => $companyId,
                        'account_code' => $def['account_code'],
                    ],
                    [
                        'account_name'      => $def['account_name'],
                        'account_type'      => $def['account_type'],
                        'account_subtype'   => $def['account_subtype'],
                        'description'       => $def['description'],
                        'is_active'         => true,
                        'is_system_account' => $def['is_system_account'],
                        'opening_balance'   => 0.00,
                        'current_balance'   => 0.00,
                        'parent_account_id' => null,
                    ]
                );

                $createdAccounts[$def['account_code']] = $account;
            }

            // 2. Second pass: Child accounts linked to parent IDs
            foreach ($accountDefs as $key => $def) {
                if ($def['parent_code'] === null) {
                    continue;
                }

                $parentAccount = $createdAccounts[$def['parent_code']] 
                    ?? Account::withoutGlobalScope(CompanyScope::class)
                        ->where('company_id', $companyId)
                        ->where('account_code', $def['parent_code'])
                        ->first();

                $account = Account::withoutGlobalScope(CompanyScope::class)->firstOrCreate(
                    [
                        'company_id'   => $companyId,
                        'account_code' => $def['account_code'],
                    ],
                    [
                        'account_name'      => $def['account_name'],
                        'account_type'      => $def['account_type'],
                        'account_subtype'   => $def['account_subtype'],
                        'description'       => $def['description'],
                        'is_active'         => true,
                        'is_system_account' => $def['is_system_account'],
                        'opening_balance'   => 0.00,
                        'current_balance'   => 0.00,
                        'parent_account_id' => $parentAccount?->id,
                    ]
                );

                $createdAccounts[$def['account_code']] = $account;
            }

            // 3. Map account IDs to AccountingSettings
            $settings = AccountingSetting::withoutGlobalScope(CompanyScope::class)->firstOrCreate(
                ['company_id' => $companyId]
            );

            // Fetch lookup map by account code for this company
            $allCompanyAccounts = Account::withoutGlobalScope(CompanyScope::class)
                ->where('company_id', $companyId)
                ->get()
                ->keyBy('account_code');

            $codeToSettingKey = [
                '4010' => 'sales_invoice_revenue_account_id',
                '1030' => 'sales_invoice_receivable_account_id',
                '2020' => 'sales_invoice_tax_account_id',
                '4060' => 'sales_return_revenue_account_id',
                '5010' => 'purchase_invoice_expense_account_id',
                '2010' => 'purchase_invoice_payable_account_id',
                '1070' => 'purchase_invoice_tax_account_id',
                '6000' => 'expense_default_account_id',
                '1010' => 'cash_account_id',
                '1020' => 'bank_account_id',
                '1040' => 'inventory_asset_account_id',
            ];

            $updates = [];
            foreach ($codeToSettingKey as $code => $settingKey) {
                if (empty($settings->$settingKey)) {
                    $acc = $allCompanyAccounts->get($code);
                    if ($acc) {
                        $updates[$settingKey] = $acc->id;
                    }
                }
            }

            // Also fill inverse fields for returns & payables if missing
            if (empty($settings->sales_return_receivable_account_id)) {
                $updates['sales_return_receivable_account_id'] = $allCompanyAccounts->get('1030')?->id;
            }
            if (empty($settings->sales_return_tax_account_id)) {
                $updates['sales_return_tax_account_id'] = $allCompanyAccounts->get('2020')?->id;
            }
            if (empty($settings->purchase_return_expense_account_id)) {
                $updates['purchase_return_expense_account_id'] = $allCompanyAccounts->get('5010')?->id;
            }
            if (empty($settings->purchase_return_payable_account_id)) {
                $updates['purchase_return_payable_account_id'] = $allCompanyAccounts->get('2010')?->id;
            }
            if (empty($settings->purchase_return_tax_account_id)) {
                $updates['purchase_return_tax_account_id'] = $allCompanyAccounts->get('1070')?->id;
            }
            if (empty($settings->expense_payable_account_id)) {
                $updates['expense_payable_account_id'] = $allCompanyAccounts->get('2010')?->id;
            }
            if (empty($settings->cost_of_goods_sold_account_id)) {
                $updates['cost_of_goods_sold_account_id'] = $allCompanyAccounts->get('5010')?->id;
            }

            if (!empty($updates)) {
                $settings->update($updates);
            }

            return $settings->refresh();
        });
    }
}
