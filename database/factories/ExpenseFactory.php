<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        $company = Company::first();
        $category = ExpenseCategory::inRandomOrder()->first();
        $user = User::first();
        $amount = fake()->randomFloat(2, 50, 5000);
        $expenseDate = fake()->dateTimeBetween('-60 days', 'now');

        return [
            'company_id' => $company?->id ?? 1,
            'expense_number' => 'EXP-' . fake()->unique()->numberBetween(1000, 9999),
            'title' => fake()->sentence(3),
            'expense_category_id' => $category?->id,
            'amount' => $amount,
            'expense_date' => $expenseDate,
            'payment_method' => fake()->randomElement(['cash', 'bank_transfer', 'credit_card']),
            'status' => 'approved',
            'user_id' => $user?->id ?? 1,
            'notes' => fake()->sentence(),
        ];
    }
}
