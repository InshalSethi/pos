<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'John Smith',
                'email' => 'john.smith@email.com',
                'phone' => '+1-555-0101',
                'mobile' => '+1-555-0101',
                'address' => '123 Main Street',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'country' => 'USA',
                'date_of_birth' => '1985-06-15',
                'gender' => 'male',
                'is_active' => true,
                'credit_limit' => 1000.00,
                'total_purchases' => rand(500, 5000),
                'loyalty_points' => rand(50, 500),
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@email.com',
                'phone' => '+1-555-0102',
                'mobile' => '+1-555-0102',
                'address' => '456 Oak Avenue',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'postal_code' => '90210',
                'country' => 'USA',
                'date_of_birth' => '1990-03-22',
                'gender' => 'female',
                'is_active' => true,
                'credit_limit' => 1500.00,
                'total_purchases' => rand(800, 8000),
                'loyalty_points' => rand(80, 800),
            ],
            [
                'name' => 'Michael Brown',
                'email' => 'michael.brown@email.com',
                'phone' => '+1-555-0103',
                'mobile' => '+1-555-0103',
                'address' => '789 Pine Street',
                'city' => 'Chicago',
                'state' => 'IL',
                'postal_code' => '60601',
                'country' => 'USA',
                'date_of_birth' => '1978-11-08',
                'gender' => 'male',
                'is_active' => true,
                'credit_limit' => 2000.00,
                'total_purchases' => rand(1200, 12000),
                'loyalty_points' => rand(120, 1200),
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emily.davis@email.com',
                'phone' => '+1-555-0104',
                'mobile' => '+1-555-0104',
                'address' => '321 Elm Street',
                'city' => 'Houston',
                'state' => 'TX',
                'postal_code' => '77001',
                'country' => 'USA',
                'date_of_birth' => '1992-09-14',
                'gender' => 'female',
                'is_active' => true,
                'credit_limit' => 800.00,
                'total_purchases' => rand(300, 3000),
                'loyalty_points' => rand(30, 300),
            ],
            [
                'name' => 'David Wilson',
                'email' => 'david.wilson@email.com',
                'phone' => '+1-555-0105',
                'mobile' => '+1-555-0105',
                'address' => '654 Maple Drive',
                'city' => 'Phoenix',
                'state' => 'AZ',
                'postal_code' => '85001',
                'country' => 'USA',
                'date_of_birth' => '1987-12-03',
                'gender' => 'male',
                'is_active' => true,
                'credit_limit' => 1200.00,
                'total_purchases' => rand(600, 6000),
                'loyalty_points' => rand(60, 600),
            ],
            [
                'name' => 'Walk-in Customer',
                'email' => null,
                'phone' => null,
                'mobile' => null,
                'address' => null,
                'city' => null,
                'state' => null,
                'postal_code' => null,
                'country' => null,
                'date_of_birth' => null,
                'gender' => null,
                'is_active' => true,
                'credit_limit' => 0,
                'total_purchases' => 0,
                'loyalty_points' => 0,
                'notes' => 'Default customer for walk-in sales',
            ],
        ];

        foreach ($customers as $customerData) {
            Customer::create($customerData);
        }
    }
}
