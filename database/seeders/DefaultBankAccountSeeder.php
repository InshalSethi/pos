<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Account;
use App\Models\BankAccount;

class DefaultBankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();

        if ($companies->isEmpty()) {
            $this->command->info('No companies found. Skipping DefaultBankAccountSeeder.');
            return;
        }

        foreach ($companies as $company) {
            // 1. Check if a COA account for Cash already exists for this company
            $coaAccount = Account::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->where(function ($query) {
                    $query->where('account_code', '1010')
                        ->orWhere('account_name', 'Cash Account')
                        ->orWhere('account_name', 'Cash')
                        ->orWhere('account_name', 'Cash on Hand');
                })
                ->first();

            if (!$coaAccount) {
                $coaAccount = Account::create([
                    'company_id' => $company->id,
                    'account_code' => '1010',
                    'account_name' => 'Cash Account',
                    'account_type' => 'asset',
                    'account_subtype' => 'cash_and_bank',
                    'description' => 'Default system Cash Account for daily transactions and POS payments.',
                    'is_active' => true,
                    'is_system_account' => true,
                    'opening_balance' => 0.00,
                    'current_balance' => 0.00,
                ]);
                $this->command->info("Created Cash COA account for company: {$company->company_name}");
            } else {
                $this->command->info("Cash COA account already exists for company: {$company->company_name}");
            }

            // 2. Check if a default Cash BankAccount already exists for this company
            $bankAccount = BankAccount::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->where(function ($query) {
                    $query->where('is_default', true)
                        ->orWhere('account_name', 'Cash Account')
                        ->orWhere('bank_name', 'Cash');
                })
                ->first();

            if (!$bankAccount) {
                BankAccount::create([
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
                    'chart_account_id' => $coaAccount->id,
                ]);
                $this->command->info("Created default Cash Bank Account for company: {$company->company_name}");
            } else {
                $this->command->info("Default Cash Bank Account already exists for company: {$company->company_name}");
            }
        }
    }
}
