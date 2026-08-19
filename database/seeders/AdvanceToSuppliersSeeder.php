<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\Company;

class AdvanceToSuppliersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::withoutGlobalScopes()->get();

        if ($companies->isEmpty()) {
            // If no companies exist, seed globally or for company_id 1
            $this->seedAccountForCompany(1);
            $this->command->info('Seeded Advance to Suppliers account for default company.');
            return;
        }

        foreach ($companies as $company) {
            $this->seedAccountForCompany($company->id);
        }

        $this->command->info('Advance to Suppliers (1310) seeded successfully for all companies!');
    }

    /**
     * Seed or ensure the 1310 account exists for a specific company.
     */
    public function seedAccountForCompany(int $companyId): Account
    {
        $parentAccount = Account::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('account_code', '1000')
            ->first();

        return Account::withoutGlobalScopes()->firstOrCreate(
            [
                'company_id'   => $companyId,
                'account_code' => '1310',
            ],
            [
                'account_name'      => 'Advance to Suppliers',
                'account_type'      => 'asset',
                'account_subtype'   => 'current_asset',
                'description'       => 'Advance payments and overpayments made to suppliers for future merchandise orders',
                'is_active'         => true,
                'is_system_account' => true,
                'opening_balance'   => 0.00,
                'current_balance'   => 0.00,
                'parent_account_id' => $parentAccount?->id,
            ]
        );
    }
}
