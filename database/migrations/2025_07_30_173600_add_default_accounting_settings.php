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
        $salesRevenueAccountId = DB::table('chart_of_accounts')->where('account_code', '4010')->value('id');
        $accountsReceivableAccountId = DB::table('chart_of_accounts')->where('account_code', '1030')->value('id');
        $salesTaxPayableAccountId = DB::table('chart_of_accounts')->where('account_code', '2020')->value('id');
        $accountsPayableAccountId = DB::table('chart_of_accounts')->where('account_code', '2010')->value('id');
        $costOfGoodsSoldAccountId = DB::table('chart_of_accounts')->where('account_code', '5000')->value('id');
        $inventoryAccountId = DB::table('chart_of_accounts')->where('account_code', '1040')->value('id');
        $cashAccountId = DB::table('chart_of_accounts')->where('account_code', '1010')->value('id');
        $bankAccountId = DB::table('chart_of_accounts')->where('account_code', '1020')->value('id');
        $operatingExpensesAccountId = DB::table('chart_of_accounts')->where('account_code', '6000')->value('id');

        // Create default accounting settings if not exists
        if (!DB::table('accounting_settings')->where('id', 1)->exists()) {
            DB::table('accounting_settings')->insert([
                'id' => 1,
                // Sales Invoice Accounts
                'sales_invoice_revenue_account_id' => $salesRevenueAccountId,
                'sales_invoice_receivable_account_id' => $accountsReceivableAccountId,
                'sales_invoice_tax_account_id' => $salesTaxPayableAccountId,

                // Sales Return Accounts
                'sales_return_revenue_account_id' => $salesRevenueAccountId,
                'sales_return_receivable_account_id' => $accountsReceivableAccountId,
                'sales_return_tax_account_id' => $salesTaxPayableAccountId,

                // Purchase Invoice Accounts
                'purchase_invoice_expense_account_id' => $operatingExpensesAccountId,
                'purchase_invoice_payable_account_id' => $accountsPayableAccountId,
                'purchase_invoice_tax_account_id' => $salesTaxPayableAccountId,

                // Purchase Return Accounts
                'purchase_return_expense_account_id' => $operatingExpensesAccountId,
                'purchase_return_payable_account_id' => $accountsPayableAccountId,
                'purchase_return_tax_account_id' => $salesTaxPayableAccountId,

                // Expense Accounts
                'expense_default_account_id' => $operatingExpensesAccountId,
                'expense_payable_account_id' => $accountsPayableAccountId,

                // Cash and Bank Accounts
                'cash_account_id' => $cashAccountId,
                'bank_account_id' => $bankAccountId,

                // Inventory Accounts
                'inventory_asset_account_id' => $inventoryAccountId,
                'cost_of_goods_sold_account_id' => $costOfGoodsSoldAccountId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
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
            $exists = DB::table('chart_of_accounts')->where('account_code', $accountData['account_code'])->exists();
            if (!$exists) {
                DB::table('chart_of_accounts')->insert(array_merge($accountData, [
                    'description' => $accountData['account_name'],
                    'is_active' => true,
                    'is_system_account' => true,
                    'opening_balance' => 0,
                    'current_balance' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the default accounting settings
        DB::table('accounting_settings')->where('id', 1)->delete();
    }
};
