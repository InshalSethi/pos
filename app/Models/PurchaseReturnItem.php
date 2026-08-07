<?php

namespace App\Models;

use App\Traits\HasUtcDatabaseTimezones;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReturnItem extends Model
{
    use HasUtcDatabaseTimezones;
    use HasFactory;

    protected $fillable = [
        'purchase_return_id',
        'product_id',
        'warehouse_id',
        'quantity',
        'unit_cost',
        'tax_amount',
        'discount_amount',
        'subtotal',
        'total_cost',
        'notes',
    ];

    protected $casts = [
        'unit_cost'       => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal'        => 'decimal:2',
        'total_cost'      => 'decimal:2',
        'quantity'        => 'integer',
    ];

    // Relationships
    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
