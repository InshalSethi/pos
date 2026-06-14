<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    protected $fillable = [
        'product_id', 'combination_key', 'variation_name_string', 
        'sku', 'barcode', 'retail_price', 'wholesale_price', 'cost_price', 'on_sale', 'sale_price', 
        'tax_rate', 'discount_type', 'discount_value',
        'stock_qty', 'min_stock_alert', 'unit_of_measure', 'supplier_id', 'batch_number', 'expiry_date'
    ];

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
