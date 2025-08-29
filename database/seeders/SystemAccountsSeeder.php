<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;

class SystemAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if system accounts already exist
        if (Account::where('is_system_account', true)->count() > 0) {
            $this->command->info('System accounts already exist. Skipping system accounts seeding.');
            return;
        }

        // System accounts required for POS operations
        $systemAccounts = [
            // Assets
            [
                'account_code' => '1000',
                'account_name' => 'Cash',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Cash on hand',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 10000.00,
                'current_balance' => 10000.00,
            ],
            [
                'account_code' => '1010',
                'account_name' => 'Bank Account',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Bank checking account',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 50000.00,
                'current_balance' => 50000.00,
            ],
            [
                'account_code' => '1200',
                'account_name' => 'Accounts Receivable',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Customer accounts receivable',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'account_code' => '1300',
                'account_name' => 'Inventory',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Inventory assets',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 25000.00,
                'current_balance' => 25000.00,
            ],

            // Liabilities
            [
                'account_code' => '2000',
                'account_name' => 'Accounts Payable',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Supplier accounts payable',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'account_code' => '2100',
                'account_name' => 'Sales Tax Payable',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Sales tax collected',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'account_code' => '2200',
                'account_name' => 'Salary Payable',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Employee salaries payable',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'account_code' => '2300',
                'account_name' => 'Tax Payable',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Employee tax deductions payable',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'account_code' => '2310',
                'account_name' => 'Insurance Payable',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Employee insurance deductions payable',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ],

            // Equity
            [
                'account_code' => '3000',
                'account_name' => 'Owner\'s Equity',
                'account_type' => 'equity',
                'account_subtype' => 'owner_equity',
                'description' => 'Owner\'s equity in the business',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 85000.00,
                'current_balance' => 85000.00,
            ],

            // Revenue
            [
                'account_code' => '4000',
                'account_name' => 'Sales Revenue',
                'account_type' => 'revenue',
                'account_subtype' => 'operating_revenue',
                'description' => 'Revenue from sales',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ],

            // Expenses
            [
                'account_code' => '5000',
                'account_name' => 'Cost of Goods Sold',
                'account_type' => 'expense',
                'account_subtype' => 'cost_of_sales',
                'description' => 'Cost of goods sold',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'account_code' => '6100',
                'account_name' => 'Salary Expense',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Employee salary expenses',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'account_code' => '6200',
                'account_name' => 'Rent Expense',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Office and store rent',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'account_code' => '6300',
                'account_name' => 'Utilities Expense',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Electricity, water, internet, etc.',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'account_code' => '6400',
                'account_name' => 'Office Supplies Expense',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Office supplies and materials',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'account_code' => '6500',
                'account_name' => 'Marketing Expense',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Marketing and advertising costs',
                'is_active' => true,
                'is_system_account' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ],
        ];

        foreach ($systemAccounts as $accountData) {
            Account::firstOrCreate(
                ['account_code' => $accountData['account_code']],
                $accountData
            );
        }

        $this->command->info('System accounts seeded successfully!');
    }
}
