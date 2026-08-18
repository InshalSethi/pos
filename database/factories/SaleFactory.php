<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $company = Company::first();
        $customer = Customer::inRandomOrder()->first();
        $user = User::first();

        $subtotal = fake()->randomFloat(2, 50, 2000);
        $taxAmount = round($subtotal * 0.05, 2);
        $discountAmount = fake()->boolean(20) ? round($subtotal * 0.05, 2) : 0;
        $totalAmount = round($subtotal + $taxAmount - $discountAmount, 2);
        $paidAmount = 0.00;

        $saleDate = fake()->dateTimeBetween('-60 days', 'now');

        return [
            'company_id' => $company?->id ?? 1,
            'sale_number' => 'INV-' . fake()->unique()->numberBetween(10000, 99999),
            'customer_id' => $customer?->id,
            'user_id' => $user?->id ?? 1,
            'sale_date' => $saleDate,
            'created_at' => $saleDate,
            'updated_at' => $saleDate,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'payment_method' => fake()->randomElement(['cash', 'bank_transfer', 'card']),
            'status' => 'pending',
            'notes' => fake()->sentence(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'paid_amount' => $attributes['total_amount'] ?? 0,
        ]);
    }

    public function paid(): static
    {
        return $this->completed();
    }
}
