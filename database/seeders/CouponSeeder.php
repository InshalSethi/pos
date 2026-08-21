<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Coupon::firstOrCreate(
            ['code' => 'SAVE20'],
            [
                'name' => '20% Special Discount',
                'type' => 'percentage',
                'value' => 20.00,
                'min_order_amount' => 0.00,
                'is_active' => true,
            ]
        );

        Coupon::firstOrCreate(
            ['code' => 'WELCOME10'],
            [
                'name' => '$10 Flat Welcome Discount',
                'type' => 'fixed',
                'value' => 10.00,
                'min_order_amount' => 15.00,
                'is_active' => true,
            ]
        );
    }
}
