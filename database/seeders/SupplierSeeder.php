<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Tech Solutions Inc.',
                'company_name' => 'Tech Solutions Inc.',
                'email' => 'orders@techsolutions.com',
                'phone' => '+1-555-0201',
                'mobile' => '+1-555-0201',
                'address' => '1234 Technology Drive',
                'city' => 'San Francisco',
                'state' => 'CA',
                'postal_code' => '94105',
                'country' => 'USA',
                'tax_number' => 'TAX123456789',
                'website' => 'https://www.techsolutions.com',
                'notes' => 'Primary supplier for electronics and tech products',
                'is_active' => true,
                'credit_limit' => 50000.00,
                'payment_terms_days' => 30,
            ],
            [
                'name' => 'Fashion Forward Ltd.',
                'company_name' => 'Fashion Forward Ltd.',
                'email' => 'wholesale@fashionforward.com',
                'phone' => '+1-555-0202',
                'mobile' => '+1-555-0202',
                'address' => '5678 Fashion Avenue',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'country' => 'USA',
                'tax_number' => 'TAX987654321',
                'website' => 'https://www.fashionforward.com',
                'notes' => 'Clothing and apparel supplier',
                'is_active' => true,
                'credit_limit' => 25000.00,
                'payment_terms_days' => 45,
            ],
            [
                'name' => 'Book World Distributors',
                'company_name' => 'Book World Distributors',
                'email' => 'orders@bookworld.com',
                'phone' => '+1-555-0203',
                'mobile' => '+1-555-0203',
                'address' => '9012 Literary Lane',
                'city' => 'Chicago',
                'state' => 'IL',
                'postal_code' => '60601',
                'country' => 'USA',
                'tax_number' => 'TAX456789123',
                'website' => 'https://www.bookworld.com',
                'notes' => 'Books and educational materials supplier',
                'is_active' => true,
                'credit_limit' => 15000.00,
                'payment_terms_days' => 30,
            ],
            [
                'name' => 'Fresh Foods Supply Co.',
                'company_name' => 'Fresh Foods Supply Co.',
                'email' => 'orders@freshfoods.com',
                'phone' => '+1-555-0204',
                'mobile' => '+1-555-0204',
                'address' => '3456 Market Street',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'postal_code' => '90210',
                'country' => 'USA',
                'tax_number' => 'TAX789123456',
                'website' => 'https://www.freshfoods.com',
                'notes' => 'Food and beverage supplier',
                'is_active' => true,
                'credit_limit' => 20000.00,
                'payment_terms_days' => 15,
            ],
            [
                'name' => 'Global Electronics',
                'company_name' => 'Global Electronics Corp.',
                'email' => 'sales@globalelectronics.com',
                'phone' => '+1-555-0205',
                'mobile' => '+1-555-0205',
                'address' => '7890 Innovation Blvd',
                'city' => 'Austin',
                'state' => 'TX',
                'postal_code' => '73301',
                'country' => 'USA',
                'tax_number' => 'TAX321654987',
                'website' => 'https://www.globalelectronics.com',
                'notes' => 'Secondary electronics supplier with competitive pricing',
                'is_active' => true,
                'credit_limit' => 75000.00,
                'payment_terms_days' => 60,
            ],
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::create($supplierData);
        }
    }
}
