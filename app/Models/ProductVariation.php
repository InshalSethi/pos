<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'product_id', 'company_id', 'combination_key', 'variation_name_string', 
        'sku', 'barcode', 'retail_price', 'wholesale_price', 'cost_price', 'on_sale', 'sale_price', 
        'tax_rate', 'tags', 'taxes', 'discount_type', 'discount_value',
        'stock_qty', 'min_stock_alert', 'unit_of_measure', 'supplier_id', 'batch_number', 'expiry_date'
    ];

    protected $casts = [
        'tags' => 'array',
        'taxes' => 'array',
        'retail_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
    ];

    public function inventories() {
        return $this->hasMany(Inventory::class, 'product_variation_id');
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }

    // Helper to fetch the active price based on checkout context (pos vs web)
    public function getActivePrice($context = 'pos') {
        if ($this->on_sale && !is_null($this->sale_price)) {
            return $this->sale_price;
        }
        return $context === 'ecommerce' ? $this->wholesale_price : $this->retail_price;
    }
}

