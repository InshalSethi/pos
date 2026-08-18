<?php

namespace Database\Factories;

use App\Models\JournalEntry;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JournalEntryFactory extends Factory
{
    protected $model = JournalEntry::class;

    public function definition(): array
    {
        $company = Company::first();
        $user = User::first();
        $amount = fake()->randomFloat(2, 100, 5000);
        $entryDate = fake()->dateTimeBetween('-60 days', 'now');

        return [
            'company_id' => $company?->id ?? 1,
            'entry_number' => 'JE-' . fake()->unique()->numberBetween(10000, 99999),
            'entry_date' => $entryDate,
            'reference' => 'REF-' . fake()->numberBetween(1000, 9999),
            'description' => fake()->sentence(4),
            'entry_type' => fake()->randomElement(['automatic', 'manual']),
            'status' => 'posted',
            'total_debit' => $amount,
            'total_credit' => $amount,
            'created_by' => $user?->id ?? 1,
            'posted_by' => $user?->id ?? 1,
            'posted_at' => $entryDate,
        ];
    }
}
