<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and accessories',
                'is_active' => true,
            ],
            [
                'name' => 'Clothing',
                'description' => 'Apparel and fashion items',
                'is_active' => true,
            ],
            [
                'name' => 'Home & Garden',
                'description' => 'Home improvement and garden supplies',
                'is_active' => true,
            ],
            [
                'name' => 'Sports & Outdoors',
                'description' => 'Sports equipment and outdoor gear',
                'is_active' => true,
            ],
            [
                'name' => 'Books & Media',
                'description' => 'Books, movies, music, and digital media',
                'is_active' => true,
            ],
            [
                'name' => 'Health & Beauty',
                'description' => 'Health, beauty, and personal care products',
                'is_active' => true,
            ],
            [
                'name' => 'Toys & Games',
                'description' => 'Toys, games, and entertainment for all ages',
                'is_active' => true,
            ],
            [
                'name' => 'Automotive',
                'description' => 'Car parts, accessories, and automotive supplies',
                'is_active' => true,
            ],
            [
                'name' => 'Food & Beverages',
                'description' => 'Food items, snacks, and beverages',
                'is_active' => true,
            ],
            [
                'name' => 'Office Supplies',
                'description' => 'Office equipment, stationery, and business supplies',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['name' => $categoryData['name']],
                $categoryData
            );
        }

        // Create subcategories for Electronics
        $electronicsCategory = Category::where('name', 'Electronics')->first();
        if ($electronicsCategory) {
            $subcategories = [
                [
                    'name' => 'Smartphones',
                    'description' => 'Mobile phones and smartphones',
                    'parent_id' => $electronicsCategory->id,
                    'is_active' => true,
                ],
                [
                    'name' => 'Laptops',
                    'description' => 'Laptop computers and notebooks',
                    'parent_id' => $electronicsCategory->id,
                    'is_active' => true,
                ],
                [
                    'name' => 'Accessories',
                    'description' => 'Electronic accessories and peripherals',
                    'parent_id' => $electronicsCategory->id,
                    'is_active' => true,
                ],
            ];

            foreach ($subcategories as $subcategoryData) {
                Category::firstOrCreate(
                    ['name' => $subcategoryData['name'], 'parent_id' => $subcategoryData['parent_id']],
                    $subcategoryData
                );
            }
        }

        // Create subcategories for Clothing
        $clothingCategory = Category::where('name', 'Clothing')->first();
        if ($clothingCategory) {
            $subcategories = [
                [
                    'name' => 'Men\'s Clothing',
                    'description' => 'Clothing for men',
                    'parent_id' => $clothingCategory->id,
                    'is_active' => true,
                ],
                [
                    'name' => 'Women\'s Clothing',
                    'description' => 'Clothing for women',
                    'parent_id' => $clothingCategory->id,
                    'is_active' => true,
                ],
                [
                    'name' => 'Children\'s Clothing',
                    'description' => 'Clothing for children',
                    'parent_id' => $clothingCategory->id,
                    'is_active' => true,
                ],
            ];

            foreach ($subcategories as $subcategoryData) {
                Category::firstOrCreate(
                    ['name' => $subcategoryData['name'], 'parent_id' => $subcategoryData['parent_id']],
                    $subcategoryData
                );
            }
        }

        $this->command->info('Categories seeded successfully!');
    }
}
