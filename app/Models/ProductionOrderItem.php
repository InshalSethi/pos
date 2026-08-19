<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_order_id',
        'raw_material_id',
        'raw_material_variation_id',
        'quantity_required',
        'quantity_used',
        'unit_cost',
        'total_cost',
    ];

    protected $casts = [
        'quantity_required' => 'decimal:4',
        'quantity_used' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:2',
    ];

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'raw_material_id');
    }

    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'raw_material_variation_id');
    }
}
