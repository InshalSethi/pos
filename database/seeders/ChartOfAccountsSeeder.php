<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            // ASSETS
            ['account_code' => '1000', 'account_name' => 'Current Assets', 'account_type' => 'asset', 'account_subtype' => 'current_asset', 'is_system_account' => true],
            ['account_code' => '1010', 'account_name' => 'Cash', 'account_type' => 'asset', 'account_subtype' => 'current_asset', 'is_system_account' => true],
            ['account_code' => '1020', 'account_name' => 'Bank Account', 'account_type' => 'asset', 'account_subtype' => 'current_asset', 'is_system_account' => true],
            ['account_code' => '1030', 'account_name' => 'Accounts Receivable', 'account_type' => 'asset', 'account_subtype' => 'current_asset', 'is_system_account' => true],
            ['account_code' => '1040', 'account_name' => 'Inventory', 'account_type' => 'asset', 'account_subtype' => 'current_asset', 'is_system_account' => true],
            ['account_code' => '1050', 'account_name' => 'Prepaid Expenses', 'account_type' => 'asset', 'account_subtype' => 'current_asset', 'is_system_account' => false],

            ['account_code' => '1500', 'account_name' => 'Fixed Assets', 'account_type' => 'asset', 'account_subtype' => 'fixed_asset', 'is_system_account' => true],
            ['account_code' => '1510', 'account_name' => 'Equipment', 'account_type' => 'asset', 'account_subtype' => 'fixed_asset', 'is_system_account' => false],
            ['account_code' => '1520', 'account_name' => 'Furniture & Fixtures', 'account_type' => 'asset', 'account_subtype' => 'fixed_asset', 'is_system_account' => false],
            ['account_code' => '1530', 'account_name' => 'Accumulated Depreciation', 'account_type' => 'asset', 'account_subtype' => 'fixed_asset', 'is_system_account' => false],

            // LIABILITIES
            ['account_code' => '2000', 'account_name' => 'Current Liabilities', 'account_type' => 'liability', 'account_subtype' => 'current_liability', 'is_system_account' => true],
            ['account_code' => '2010', 'account_name' => 'Accounts Payable', 'account_type' => 'liability', 'account_subtype' => 'current_liability', 'is_system_account' => true],
            ['account_code' => '2020', 'account_name' => 'Sales Tax Payable', 'account_type' => 'liability', 'account_subtype' => 'current_liability', 'is_system_account' => true],
            ['account_code' => '2030', 'account_name' => 'Accrued Expenses', 'account_type' => 'liability', 'account_subtype' => 'current_liability', 'is_system_account' => false],

            ['account_code' => '2500', 'account_name' => 'Long-term Liabilities', 'account_type' => 'liability', 'account_subtype' => 'long_term_liability', 'is_system_account' => true],
            ['account_code' => '2510', 'account_name' => 'Long-term Debt', 'account_type' => 'liability', 'account_subtype' => 'long_term_liability', 'is_system_account' => false],

            // EQUITY
            ['account_code' => '3000', 'account_name' => 'Owner\'s Equity', 'account_type' => 'equity', 'account_subtype' => 'equity', 'is_system_account' => true],
            ['account_code' => '3010', 'account_name' => 'Capital', 'account_type' => 'equity', 'account_subtype' => 'equity', 'is_system_account' => false],
            ['account_code' => '3020', 'account_name' => 'Retained Earnings', 'account_type' => 'equity', 'account_subtype' => 'equity', 'is_system_account' => true],

            // REVENUE
            ['account_code' => '4000', 'account_name' => 'Revenue', 'account_type' => 'revenue', 'account_subtype' => 'operating_revenue', 'is_system_account' => true],
            ['account_code' => '4010', 'account_name' => 'Sales Revenue', 'account_type' => 'revenue', 'account_subtype' => 'operating_revenue', 'is_system_account' => true],
            ['account_code' => '4020', 'account_name' => 'Service Revenue', 'account_type' => 'revenue', 'account_subtype' => 'operating_revenue', 'is_system_account' => false],
            ['account_code' => '4900', 'account_name' => 'Other Revenue', 'account_type' => 'revenue', 'account_subtype' => 'other_revenue', 'is_system_account' => false],

            // EXPENSES
            ['account_code' => '5000', 'account_name' => 'Cost of Goods Sold', 'account_type' => 'expense', 'account_subtype' => 'cost_of_goods_sold', 'is_system_account' => true],
            ['account_code' => '5010', 'account_name' => 'Product Costs', 'account_type' => 'expense', 'account_subtype' => 'cost_of_goods_sold', 'is_system_account' => true],

            ['account_code' => '6000', 'account_name' => 'Operating Expenses', 'account_type' => 'expense', 'account_subtype' => 'operating_expense', 'is_system_account' => true],
            ['account_code' => '6010', 'account_name' => 'Rent Expense', 'account_type' => 'expense', 'account_subtype' => 'operating_expense', 'is_system_account' => false],
            ['account_code' => '6020', 'account_name' => 'Utilities Expense', 'account_type' => 'expense', 'account_subtype' => 'operating_expense', 'is_system_account' => false],
            ['account_code' => '6030', 'account_name' => 'Salaries Expense', 'account_type' => 'expense', 'account_subtype' => 'operating_expense', 'is_system_account' => false],
            ['account_code' => '6040', 'account_name' => 'Office Supplies', 'account_type' => 'expense', 'account_subtype' => 'operating_expense', 'is_system_account' => false],
            ['account_code' => '6050', 'account_name' => 'Marketing Expense', 'account_type' => 'expense', 'account_subtype' => 'operating_expense', 'is_system_account' => false],
            ['account_code' => '6060', 'account_name' => 'Insurance Expense', 'account_type' => 'expense', 'account_subtype' => 'operating_expense', 'is_system_account' => false],
            ['account_code' => '6070', 'account_name' => 'Depreciation Expense', 'account_type' => 'expense', 'account_subtype' => 'operating_expense', 'is_system_account' => false],
        ];

        foreach ($accounts as $account) {
            DB::table('chart_of_accounts')->insert(array_merge($account, [
                'description' => $account['account_name'],
                'is_active' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
