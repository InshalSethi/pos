<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeIngredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_id',
        'raw_material_id',
        'raw_material_variation_id',
        'quantity',
        'unit_id',
        'waste_percentage',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'waste_percentage' => 'decimal:2',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(ProductRecipe::class, 'recipe_id');
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'raw_material_id');
    }

    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'raw_material_variation_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
