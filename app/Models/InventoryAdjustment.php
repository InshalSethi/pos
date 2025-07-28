<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'adjustment_number',
        'product_id',
        'adjustment_type',
        'quantity_before',
        'quantity_adjusted',
        'quantity_after',
        'reason',
        'user_id',
        'adjustment_date',
        'cost_impact',
        'notes',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'cost_impact' => 'decimal:2',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeIncrease($query)
    {
        return $query->where('adjustment_type', 'increase');
    }

    public function scopeDecrease($query)
    {
        return $query->where('adjustment_type', 'decrease');
    }

    public function scopeRecount($query)
    {
        return $query->where('adjustment_type', 'recount');
    }

    // Accessors
    public function getFormattedCostImpactAttribute(): string
    {
        return '$' . number_format(abs($this->cost_impact), 2);
    }

    public function getAdjustmentTypeDisplayAttribute(): string
    {
        return match($this->adjustment_type) {
            'increase' => 'Stock Increase',
            'decrease' => 'Stock Decrease',
            'recount' => 'Stock Recount',
            default => ucfirst($this->adjustment_type)
        };
    }
}
