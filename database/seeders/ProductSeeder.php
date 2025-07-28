<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create categories
        $electronics = Category::create([
            'name' => 'Electronics',
            'description' => 'Electronic devices and accessories',
            'is_active' => true,
        ]);

        $clothing = Category::create([
            'name' => 'Clothing',
            'description' => 'Apparel and fashion items',
            'is_active' => true,
        ]);

        $books = Category::create([
            'name' => 'Books',
            'description' => 'Books and educational materials',
            'is_active' => true,
        ]);

        $food = Category::create([
            'name' => 'Food & Beverages',
            'description' => 'Food items and drinks',
            'is_active' => true,
        ]);

        // Create subcategories
        $smartphones = Category::create([
            'name' => 'Smartphones',
            'description' => 'Mobile phones and accessories',
            'parent_id' => $electronics->id,
            'is_active' => true,
        ]);

        $laptops = Category::create([
            'name' => 'Laptops',
            'description' => 'Laptop computers',
            'parent_id' => $electronics->id,
            'is_active' => true,
        ]);

        // Create sample products
        $products = [
            [
                'name' => 'iPhone 15 Pro',
                'description' => 'Latest iPhone with advanced features',
                'sku' => 'IPH15PRO001',
                'barcode' => '1234567890123',
                'cost_price' => 800.00,
                'selling_price' => 999.00,
                'markup_percentage' => 24.88,
                'stock_quantity' => 25,
                'min_stock_level' => 5,
                'max_stock_level' => 100,
                'unit_of_measure' => 'pcs',
                'track_inventory' => true,
                'is_active' => true,
                'category_id' => $smartphones->id,
                'weight' => 0.221,
                'tax_rate' => 8.25,
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'description' => 'Premium Android smartphone',
                'sku' => 'SAM24001',
                'barcode' => '1234567890124',
                'cost_price' => 700.00,
                'selling_price' => 899.00,
                'markup_percentage' => 28.43,
                'stock_quantity' => 30,
                'min_stock_level' => 5,
                'max_stock_level' => 80,
                'unit_of_measure' => 'pcs',
                'track_inventory' => true,
                'is_active' => true,
                'category_id' => $smartphones->id,
                'weight' => 0.196,
                'tax_rate' => 8.25,
            ],
            [
                'name' => 'MacBook Pro 16"',
                'description' => 'Professional laptop for creative work',
                'sku' => 'MBP16001',
                'barcode' => '1234567890125',
                'cost_price' => 2000.00,
                'selling_price' => 2499.00,
                'markup_percentage' => 24.95,
                'stock_quantity' => 10,
                'min_stock_level' => 2,
                'max_stock_level' => 20,
                'unit_of_measure' => 'pcs',
                'track_inventory' => true,
                'is_active' => true,
                'category_id' => $laptops->id,
                'weight' => 2.1,
                'tax_rate' => 8.25,
            ],
            [
                'name' => 'Dell XPS 13',
                'description' => 'Compact and powerful ultrabook',
                'sku' => 'DELLXPS001',
                'barcode' => '1234567890126',
                'cost_price' => 900.00,
                'selling_price' => 1199.00,
                'markup_percentage' => 33.22,
                'stock_quantity' => 15,
                'min_stock_level' => 3,
                'max_stock_level' => 30,
                'unit_of_measure' => 'pcs',
                'track_inventory' => true,
                'is_active' => true,
                'category_id' => $laptops->id,
                'weight' => 1.27,
                'tax_rate' => 8.25,
            ],
            [
                'name' => 'Classic T-Shirt',
                'description' => 'Comfortable cotton t-shirt',
                'sku' => 'TSHIRT001',
                'barcode' => '1234567890127',
                'cost_price' => 8.00,
                'selling_price' => 19.99,
                'markup_percentage' => 149.88,
                'stock_quantity' => 100,
                'min_stock_level' => 20,
                'max_stock_level' => 200,
                'unit_of_measure' => 'pcs',
                'track_inventory' => true,
                'is_active' => true,
                'category_id' => $clothing->id,
                'weight' => 0.2,
                'tax_rate' => 6.00,
            ],
            [
                'name' => 'Programming Book',
                'description' => 'Learn programming fundamentals',
                'sku' => 'BOOK001',
                'barcode' => '1234567890128',
                'cost_price' => 25.00,
                'selling_price' => 49.99,
                'markup_percentage' => 99.96,
                'stock_quantity' => 50,
                'min_stock_level' => 10,
                'max_stock_level' => 100,
                'unit_of_measure' => 'pcs',
                'track_inventory' => true,
                'is_active' => true,
                'category_id' => $books->id,
                'weight' => 0.8,
                'tax_rate' => 0.00,
            ],
            [
                'name' => 'Energy Drink',
                'description' => 'Refreshing energy drink',
                'sku' => 'DRINK001',
                'barcode' => '1234567890129',
                'cost_price' => 1.50,
                'selling_price' => 2.99,
                'markup_percentage' => 99.33,
                'stock_quantity' => 3, // Low stock for testing
                'min_stock_level' => 10,
                'max_stock_level' => 200,
                'unit_of_measure' => 'pcs',
                'track_inventory' => true,
                'is_active' => true,
                'category_id' => $food->id,
                'weight' => 0.5,
                'tax_rate' => 5.00,
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }
    }
}
