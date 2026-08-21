<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPayment;
use App\Models\User;

class SubscriptionPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();

        // Seed initial enterprise payment for admin
        SubscriptionPayment::firstOrCreate(
            ['transaction_id' => 'TXN-20260821-ENT01'],
            [
                'user_id'        => $admin ? $admin->id : 1,
                'user_name'      => $admin ? $admin->name : 'Admin User',
                'user_email'     => 'admin@gmail.com',
                'plan_name'      => 'Enterprise',
                'billing_cycle'  => 'yearly',
                'amount'         => 960.00,
                'currency'       => 'USD',
                'payment_method' => 'Credit Card',
                'card_last_four' => '4242',
                'status'         => 'paid',
                'paid_at'        => now()->subDays(5),
            ]
        );

        // Seed basic plan payment for demo user
        SubscriptionPayment::firstOrCreate(
            ['transaction_id' => 'TXN-20260815-BSC02'],
            [
                'user_id'        => null,
                'user_name'      => 'John Doe Store',
                'user_email'     => 'john@store.com',
                'plan_name'      => 'Basic',
                'billing_cycle'  => 'monthly',
                'amount'         => 20.00,
                'currency'       => 'USD',
                'payment_method' => 'Credit Card',
                'card_last_four' => '8888',
                'status'         => 'paid',
                'paid_at'        => now()->subDays(12),
            ]
        );

        // Seed advance plan payment for demo user 2
        SubscriptionPayment::firstOrCreate(
            ['transaction_id' => 'TXN-20260810-ADV03'],
            [
                'user_id'        => null,
                'user_name'      => 'Tech POS Ltd',
                'user_email'     => 'billing@techpos.com',
                'plan_name'      => 'Advance',
                'billing_cycle'  => 'yearly',
                'amount'         => 480.00,
                'currency'       => 'USD',
                'payment_method' => 'Credit Card',
                'card_last_four' => '1234',
                'status'         => 'paid',
                'paid_at'        => now()->subDays(20),
            ]
        );
    }
}
