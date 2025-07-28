<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity_ordered',
        'quantity_received',
        'unit_cost',
        'total_cost',
        'notes',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    // Relationships
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getQuantityPendingAttribute(): int
    {
        return $this->quantity_ordered - $this->quantity_received;
    }

    public function getIsFullyReceivedAttribute(): bool
    {
        return $this->quantity_received >= $this->quantity_ordered;
    }

    public function getReceivePercentageAttribute(): float
    {
        return $this->quantity_ordered > 0 ?
               ($this->quantity_received / $this->quantity_ordered) * 100 : 0;
    }

    public function getFormattedTotalCostAttribute(): string
    {
        return '$' . number_format($this->total_cost, 2);
    }
}
