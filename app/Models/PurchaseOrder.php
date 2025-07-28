<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'user_id',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'status',
        'subtotal',
        'tax_amount',
        'shipping_cost',
        'total_amount',
        'notes',
        'terms_and_conditions',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // Relationships
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['sent', 'confirmed']);
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    // Accessors
    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total_amount, 2);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->expected_delivery_date &&
               $this->expected_delivery_date->isPast() &&
               !in_array($this->status, ['received', 'cancelled']);
    }

    public function getCompletionPercentageAttribute(): float
    {
        $totalOrdered = $this->purchaseOrderItems->sum('quantity_ordered');
        $totalReceived = $this->purchaseOrderItems->sum('quantity_received');

        return $totalOrdered > 0 ? ($totalReceived / $totalOrdered) * 100 : 0;
    }
}
