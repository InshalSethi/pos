<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\HasUtcDatabaseTimezones;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionOrder extends Model
{
    use SoftDeletes, BelongsToCompany, HasUtcDatabaseTimezones, HasFactory;

    protected $fillable = [
        'company_id',
        'production_number',
        'recipe_id',
        'product_id',
        'product_variation_id',
        'warehouse_id',
        'quantity_to_produce',
        'quantity_produced',
        'status',
        'production_date',
        'expiry_date',
        'completed_at',
        'total_cost',
        'unit_cost',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'quantity_to_produce' => 'decimal:4',
        'quantity_produced' => 'decimal:4',
        'total_cost' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'production_date' => 'date',
        'expiry_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(ProductRecipe::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductionOrderItem::class);
    }
}
