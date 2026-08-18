<?php

namespace Database\Factories;

use App\Models\ExpenseCategory;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseCategoryFactory extends Factory
{
    protected $model = ExpenseCategory::class;

    public function definition(): array
    {
        $company = Company::first();

        return [
            'company_id' => $company?->id ?? 1,
            'name' => fake()->unique()->words(2, true),
            'code' => (string) fake()->unique()->numberBetween(6100, 6999),
            'is_active' => true,
        ];
    }
}
