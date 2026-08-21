<?php

namespace App\Models;

use App\Traits\BelongsToCompany;

use App\Traits\HasUtcDatabaseTimezones;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use SoftDeletes;

    use BelongsToCompany;
    use HasUtcDatabaseTimezones;
    use HasFactory;

    protected static function booted()
    {
        static::creating(function ($product) {
            if (!isset($product->attributes['selling_price'])) {
                $product->attributes['selling_price'] = 0;
            }
        });

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
        'company_id',
        'name',
        'short_description',
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
        'brand_id',
        'unit_id',
        'supplier_id',
        'batch_number',
        'expiry_date',
        'discount_type',
        'discount_value',
        'tags',
        'taxes',
        'status',
        'has_variations',
        'item_type',
        'can_be_sold',
        'can_be_purchased',
        'auto_deduct_ingredients',
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
        'has_variations' => 'boolean',
        'can_be_sold' => 'boolean',
        'can_be_purchased' => 'boolean',
        'auto_deduct_ingredients' => 'boolean',
        'images' => 'array',
        'tags' => 'array',
        'taxes' => 'array',
        'expiry_date' => 'date',
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
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

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(ProductRecipe::class);
    }

    public function activeRecipe()
    {
        return $this->hasOne(ProductRecipe::class)->where('is_active', true);
    }

    public function ingredientIn(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class, 'raw_material_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
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

    public function scopeRawMaterials($query)
    {
        return $query->where('item_type', 'raw_material');
    }

    public function scopeFinishedGoods($query)
    {
        return $query->where('item_type', 'finished_good');
    }

    public function scopeFixedAssets($query)
    {
        return $query->where('item_type', 'fixed_asset');
    }

    public function scopeSaleable($query)
    {
        return $query->where('can_be_sold', true);
    }

    public function scopePurchasable($query)
    {
        return $query->where('can_be_purchased', true);
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

    public function getPurchasePriceAttribute()
    {
        return $this->attributes['cost_price'] ?? 0;
    }

    public function setPurchasePriceAttribute($value)
    {
        $this->attributes['cost_price'] = $value;
    }

    public function getStockQtyAttribute()
    {
        return $this->attributes['stock_quantity'] ?? 0;
    }

    public function setStockQtyAttribute($value)
    {
        $this->attributes['stock_quantity'] = $value;
    }
}
