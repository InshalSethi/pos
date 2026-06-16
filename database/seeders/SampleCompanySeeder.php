<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;

class SampleCompanySeeder extends Seeder
{
    public function run(): void
    {
        $admins = User::role('admin')->get();

        if ($admins->isEmpty()) {
            $this->command->info('No admin users found. Please seed admins first.');
            return;
        }

        $mainAdmin = $admins->firstWhere('email', 'admin@gmail.com') ?? $admins->first();

        $company = Company::firstOrCreate([
            'company_name' => 'Aura Enterprise (Sample)',
        ], [
            'user_id' => $mainAdmin->id,
            'company_email' => 'contact@aura-enterprise.test',
            'company_phone' => '+1234567890',
            'owner_role' => 'CEO',
            'team_size' => '10-50',
            'intended_tasks' => ['accounting', 'sales', 'inventory'],
            'business_type' => 'Retail',
            'business_scale' => 'Multiple Outlets',
            'country' => 'United States',
            'system_language' => 'en',
            'base_currency' => 'USD',
            'timezone_offset' => 'UTC',
            'fiscal_year_start' => date('Y-01-01'),
            'status' => 'active',
            'draft_step' => null,
            'tax_number' => 'TAX-999-888',
            'business_address' => '123 Admin Boulevard, Cloud City',
        ]);

        foreach ($admins as $admin) {
            // Link user to company
            if (!$admin->companies()->where('company_id', $company->id)->exists()) {
                $admin->companies()->attach($company->id, ['role' => 'owner']);
            }
            
            // Set as current company
            $admin->current_company_id = $company->id;
            $admin->onboarding_completed = true;
            $admin->save();
        }

        $this->command->info('Sample company created and assigned to all admin users.');
    }
}
