<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;
use App\Traits\HasUtcDatabaseTimezones;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tax extends Model
{
    use BelongsToCompany;
    use HasUtcDatabaseTimezones;
    use HasFactory;

    protected $fillable = [
        'name',
        'value',
        'type',
        'is_active',
        'sale_invoice_required',
        'purchase_order_required',
    ];

    protected $casts = [
        'value' => 'float',
        'is_active' => 'boolean',
        'sale_invoice_required' => 'boolean',
        'purchase_order_required' => 'boolean',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSaleInvoiceRequired($query)
    {
        return $query->where('sale_invoice_required', true);
    }

    public function scopePurchaseOrderRequired($query)
    {
        return $query->where('purchase_order_required', true);
    }
}
