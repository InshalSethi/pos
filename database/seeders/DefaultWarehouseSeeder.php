<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Warehouse;

class DefaultWarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();

        if ($companies->isEmpty()) {
            $this->command->info('No companies found. Skipping DefaultWarehouseSeeder.');
            return;
        }

        foreach ($companies as $company) {
            // Check if default warehouse already exists for this company
            $defaultWarehouse = Warehouse::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->where('is_default', true)
                ->first();

            if (!$defaultWarehouse) {
                Warehouse::create([
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
                $this->command->info("Created default warehouse 'Main Warehouse' for company: {$company->company_name}");
            } else {
                $this->command->info("Default warehouse already exists for company: {$company->company_name}");
            }
        }
    }
}
