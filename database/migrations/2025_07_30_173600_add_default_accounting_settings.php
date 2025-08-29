<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Account;
use App\Models\AccountingSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure required accounts exist first
        $this->ensureRequiredAccountsExist();

        // Get account IDs by account codes
        $salesRevenueAccount = Account::where('account_code', '4010')->first();
        $accountsReceivableAccount = Account::where('account_code', '1030')->first();
        $salesTaxPayableAccount = Account::where('account_code', '2020')->first();
        $accountsPayableAccount = Account::where('account_code', '2010')->first();
        $costOfGoodsSoldAccount = Account::where('account_code', '5000')->first();
        $inventoryAccount = Account::where('account_code', '1040')->first();
        $cashAccount = Account::where('account_code', '1010')->first();
        $bankAccount = Account::where('account_code', '1020')->first();
        $operatingExpensesAccount = Account::where('account_code', '6000')->first();

        // Create default accounting settings
        AccountingSetting::firstOrCreate(
            ['id' => 1], // Singleton pattern
            [
                // Sales Invoice Accounts
                'sales_invoice_revenue_account_id' => $salesRevenueAccount?->id,
                'sales_invoice_receivable_account_id' => $accountsReceivableAccount?->id,
                'sales_invoice_tax_account_id' => $salesTaxPayableAccount?->id,

                // Sales Return Accounts
                'sales_return_revenue_account_id' => $salesRevenueAccount?->id,
                'sales_return_receivable_account_id' => $accountsReceivableAccount?->id,
                'sales_return_tax_account_id' => $salesTaxPayableAccount?->id,

                // Purchase Invoice Accounts
                'purchase_invoice_expense_account_id' => $operatingExpensesAccount?->id,
                'purchase_invoice_payable_account_id' => $accountsPayableAccount?->id,
                'purchase_invoice_tax_account_id' => $salesTaxPayableAccount?->id,

                // Purchase Return Accounts
                'purchase_return_expense_account_id' => $operatingExpensesAccount?->id,
                'purchase_return_payable_account_id' => $accountsPayableAccount?->id,
                'purchase_return_tax_account_id' => $salesTaxPayableAccount?->id,

                // Expense Accounts
                'expense_default_account_id' => $operatingExpensesAccount?->id,
                'expense_payable_account_id' => $accountsPayableAccount?->id,

                // Cash and Bank Accounts
                'cash_account_id' => $cashAccount?->id,
                'bank_account_id' => $bankAccount?->id,

                // Inventory Accounts
                'inventory_asset_account_id' => $inventoryAccount?->id,
                'cost_of_goods_sold_account_id' => $costOfGoodsSoldAccount?->id,
            ]
        );
    }

    /**
     * Ensure required accounts exist in the chart of accounts
     */
    private function ensureRequiredAccountsExist(): void
    {
        $requiredAccounts = [
            ['account_code' => '1010', 'account_name' => 'Cash', 'account_type' => 'asset', 'account_subtype' => 'current_asset'],
            ['account_code' => '1020', 'account_name' => 'Bank Account', 'account_type' => 'asset', 'account_subtype' => 'current_asset'],
            ['account_code' => '1030', 'account_name' => 'Accounts Receivable', 'account_type' => 'asset', 'account_subtype' => 'current_asset'],
            ['account_code' => '1040', 'account_name' => 'Inventory', 'account_type' => 'asset', 'account_subtype' => 'current_asset'],
            ['account_code' => '2010', 'account_name' => 'Accounts Payable', 'account_type' => 'liability', 'account_subtype' => 'current_liability'],
            ['account_code' => '2020', 'account_name' => 'Sales Tax Payable', 'account_type' => 'liability', 'account_subtype' => 'current_liability'],
            ['account_code' => '4010', 'account_name' => 'Sales Revenue', 'account_type' => 'revenue', 'account_subtype' => 'operating_revenue'],
            ['account_code' => '5000', 'account_name' => 'Cost of Goods Sold', 'account_type' => 'expense', 'account_subtype' => 'cost_of_goods_sold'],
            ['account_code' => '6000', 'account_name' => 'Operating Expenses', 'account_type' => 'expense', 'account_subtype' => 'operating_expense'],
        ];

        foreach ($requiredAccounts as $accountData) {
            Account::firstOrCreate(
                ['account_code' => $accountData['account_code']],
                array_merge($accountData, [
                    'description' => $accountData['account_name'],
                    'is_active' => true,
                    'is_system_account' => true,
                    'opening_balance' => 0,
                    'current_balance' => 0,
                ])
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the default accounting settings
        AccountingSetting::where('id', 1)->delete();
    }
};
