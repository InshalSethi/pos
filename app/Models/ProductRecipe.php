<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\HasUtcDatabaseTimezones;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductRecipe extends Model
{
    use SoftDeletes, BelongsToCompany, HasUtcDatabaseTimezones, HasFactory;

    protected $fillable = [
        'company_id',
        'product_id',
        'product_variation_id',
        'name',
        'yield_quantity',
        'unit_id',
        'instructions',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'yield_quantity' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class, 'recipe_id');
    }

    /**
     * Calculate total cost of recipe ingredients based on current raw material unit costs
     */
    public function calculateTotalCost(): float
    {
        $this->loadMissing(['ingredients.rawMaterial', 'ingredients.variation']);
        
        $totalCost = 0;
        foreach ($this->ingredients as $ingredient) {
            $unitCost = 0;
            if ($ingredient->variation) {
                $unitCost = (float) ($ingredient->variation->cost_price ?? 0);
            } elseif ($ingredient->rawMaterial) {
                $unitCost = (float) ($ingredient->rawMaterial->cost_price ?? 0);
            }

            $effectiveQty = (float) $ingredient->quantity * (1 + ((float) $ingredient->waste_percentage / 100));
            $totalCost += $effectiveQty * $unitCost;
        }

        return round($totalCost, 2);
    }
}
