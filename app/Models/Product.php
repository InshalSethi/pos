<?php

namespace App\Models;

use App\Traits\BelongsToCompany;

use App\Traits\HasUtcDatabaseTimezones;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use BelongsToCompany;
    use HasUtcDatabaseTimezones;
    use HasFactory;

    protected static function booted()
    {
        static::updated(function ($product) {
            if ($product->track_inventory && $product->isLowStock && $product->supplier_id) {
                self::generateDraftPurchaseOrder($product);
            }
        });
    }

    protected static function generateDraftPurchaseOrder($product)
    {
        // Find existing draft PO for this supplier
        $existingPO = PurchaseOrder::where('supplier_id', $product->supplier_id)
            ->where('status', 'draft')
            ->first();

        if (!$existingPO) {
            $existingPO = PurchaseOrder::create([
                'po_number' => 'PO-' . strtoupper(bin2hex(random_bytes(4))),
                'supplier_id' => $product->supplier_id,
                'user_id' => 1, // System generated
                'order_date' => now(),
                'status' => 'draft',
                'notes' => 'Automatically generated due to low stock.',
                'subtotal' => 0,
                'total_amount' => 0,
            ]);
        }

        // Check if item already in PO
        $itemExists = $existingPO->purchaseOrderItems()->where('product_id', $product->id)->exists();
        if (!$itemExists) {
            $existingPO->purchaseOrderItems()->create([
                'product_id' => $product->id,
                'quantity_ordered' => $product->max_stock_level > $product->stock_quantity 
                    ? $product->max_stock_level - $product->stock_quantity 
                    : 10,
                'unit_cost' => $product->cost_price,
                'total_cost' => $product->cost_price * 10,
            ]);
            
            // Re-calculate PO total
            $total = $existingPO->purchaseOrderItems()->sum('total_cost');
            $existingPO->update([
                'total_amount' => $total,
                'subtotal' => $total,
            ]);
        }
    }

    protected $fillable = [
        'name',
        'description',
        'sku',
        'barcode',
        'cost_price',
        'selling_price',
        'wholesale_price',
        'markup_percentage',
        'stock_quantity',
        'min_stock_level',
        'max_stock_level',
        'unit_of_measure',
        'track_inventory',
        'is_active',
        'image',
        'images',
        'weight',
        'dimensions',
        'tax_rate',
        'category_id',
        'supplier_id',
        'batch_number',
        'expiry_date',
        'discount_type',
        'discount_value',
        'tags',
        'status',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'weight' => 'decimal:3',
        'track_inventory' => 'boolean',
        'is_active' => 'boolean',
        'images' => 'array',
        'tags' => 'array',
        'expiry_date' => 'date',
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function inventoryAdjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock_level');
    }

    // Accessors & Mutators
    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_quantity <= $this->min_stock_level;
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format((float) ($this->selling_price ?? 0), 2);
    }
}
