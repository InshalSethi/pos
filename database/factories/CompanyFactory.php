<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_name' => fake()->company() . ' POS',
            'company_email' => fake()->companyEmail(),
            'company_phone' => fake()->phoneNumber(),
            'registration_number' => 'REG-' . fake()->unique()->numerify('#####'),
            'tax_number' => 'TAX-' . fake()->unique()->numerify('#####'),
            'business_address' => fake()->address(),
            'owner_role' => fake()->randomElement(['CEO', 'Owner', 'Managing Director']),
            'team_size' => fake()->randomElement(['1-5', '5-20', '20-50']),
            'business_type' => fake()->randomElement(['Retail', 'Wholesale', 'Services']),
            'business_scale' => fake()->randomElement(['Single Outlet', 'Multiple Outlets']),
            'intended_tasks' => ['POS'],
            'country' => fake()->country(),
            'system_language' => 'en',
            'base_currency' => 'USD',
            'timezone_offset' => 'UTC',
            'fiscal_year_start' => date('Y-01-01'),
            'status' => 'active',
        ];
    }
}
