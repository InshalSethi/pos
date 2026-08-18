<?php

namespace Database\Factories;

use App\Models\SaleItem;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleItemFactory extends Factory
{
    protected $model = SaleItem::class;

    public function definition(): array
    {
        $product = Product::inRandomOrder()->first();
        $qty = fake()->numberBetween(1, 5);
        $unitPrice = $product ? $product->selling_price : fake()->randomFloat(2, 10, 500);

        return [
            'sale_id' => Sale::factory(),
            'product_id' => $product?->id ?? 1,
            'quantity' => $qty,
            'unit_price' => $unitPrice,
            'total_amount' => round($qty * $unitPrice, 2),
        ];
    }
}
