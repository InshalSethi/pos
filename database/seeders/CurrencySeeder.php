<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'name'          => 'US Dollar',
                'code'          => 'USD',
                'symbol'        => '$',
                'exchange_rate' => 1.0000,
                'is_active'     => true,
            ],
            [
                'name'          => 'Pakistani Rupee',
                'code'          => 'PKR',
                'symbol'        => 'Rs.',
                'exchange_rate' => 278.5000,
                'is_active'     => true,
            ],
            [
                'name'          => 'British Pound',
                'code'          => 'GBP',
                'symbol'        => '£',
                'exchange_rate' => 0.7800,
                'is_active'     => true,
            ],
            [
                'name'          => 'Euro',
                'code'          => 'EUR',
                'symbol'        => '€',
                'exchange_rate' => 0.9200,
                'is_active'     => true,
            ],
            [
                'name'          => 'UAE Dirham',
                'code'          => 'AED',
                'symbol'        => 'د.إ',
                'exchange_rate' => 3.6700,
                'is_active'     => true,
            ],
            [
                'name'          => 'Saudi Riyal',
                'code'          => 'SAR',
                'symbol'        => 'ر.س',
                'exchange_rate' => 3.7500,
                'is_active'     => true,
            ],
            [
                'name'          => 'Canadian Dollar',
                'code'          => 'CAD',
                'symbol'        => 'C$',
                'exchange_rate' => 1.3600,
                'is_active'     => true,
            ],
            [
                'name'          => 'Australian Dollar',
                'code'          => 'AUD',
                'symbol'        => 'A$',
                'exchange_rate' => 1.5000,
                'is_active'     => true,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }

        $this->command->info('Currencies seeded successfully!');
    }
}
