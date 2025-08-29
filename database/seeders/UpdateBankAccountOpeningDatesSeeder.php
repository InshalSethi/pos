<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BankAccount;

class UpdateBankAccountOpeningDatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update existing bank accounts with opening dates
        $bankAccounts = BankAccount::whereNull('opening_date')->get();
        
        foreach ($bankAccounts as $account) {
            $account->update([
                'opening_date' => now()->subDays(rand(30, 365))->toDateString()
            ]);
        }
        
        $this->command->info('Updated ' . $bankAccounts->count() . ' bank accounts with opening dates.');
    }
}
