<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $costPrice = fake()->randomFloat(2, 5, 300);
        $markup = fake()->numberBetween(20, 80);
        $sellingPrice = round($costPrice * (1 + ($markup / 100)), 2);
        $wholesalePrice = round($sellingPrice * 0.85, 2);

        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(10),
            'sku' => 'SKU-' . Str::upper(Str::random(6)) . '-' . fake()->unique()->numberBetween(1000, 99999),
            'barcode' => fake()->unique()->ean13(),
            'cost_price' => $costPrice,
            'selling_price' => $sellingPrice,
            'wholesale_price' => $wholesalePrice,
            'markup_percentage' => $markup,
            'stock_quantity' => fake()->numberBetween(10, 200),
            'min_stock_level' => 5,
            'max_stock_level' => 300,
            'unit_of_measure' => fake()->randomElement(['pcs', 'box', 'pack', 'set', 'kg']),
            'track_inventory' => true,
            'is_active' => true,
            'has_variations' => false,
            'status' => 'active',
            'category_id' => Category::inRandomOrder()->first()?->id ?? 1,
            'weight' => fake()->randomFloat(2, 0.1, 5.0),
            'tax_rate' => fake()->randomElement([0, 5, 8.25, 10, 18]),
            'tags' => fake()->randomElements(['New Arrival', 'Featured', 'Best Seller', 'On Sale', 'Trending'], 2),
            'taxes' => [
                ['name' => 'Standard Tax', 'rate' => 8.25]
            ]
        ];
    }

    /**
     * Indicate that the product has variations.
     */
    public function withVariations(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_variations' => true,
        ]);
    }
}
