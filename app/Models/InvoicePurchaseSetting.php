<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\HasUtcDatabaseTimezones;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePurchaseSetting extends Model
{
    use BelongsToCompany;
    use HasUtcDatabaseTimezones;
    use HasFactory;

    protected $table = 'invoice_purchase_settings';

    protected $fillable = [
        'company_id',
        'invoice_prefix',
        'default_pricing_mode',
        'default_due_period_days',
        'default_terms_conditions',
        'show_item_wholesale_toggle',
        'po_prefix',
        'default_purchase_warehouse_id',
        'auto_update_product_cost',
        'default_system_tax_ids',
        'allow_manual_taxes_discounts',
    ];

    protected $casts = [
        'default_due_period_days' => 'integer',
        'show_item_wholesale_toggle' => 'boolean',
        'auto_update_product_cost' => 'boolean',
        'default_system_tax_ids' => 'array',
        'allow_manual_taxes_discounts' => 'boolean',
    ];

    public function defaultWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'default_purchase_warehouse_id');
    }

    /**
     * Get or create setting record for active company context.
     */
    public static function getSettings(): self
    {
        $companyId = auth()->user()?->company_id;
        $setting = static::first();
        if (!$setting) {
            $setting = static::create([
                'company_id' => $companyId,
                'invoice_prefix' => 'INV-',
                'default_pricing_mode' => 'retail',
                'default_due_period_days' => 30,
                'default_terms_conditions' => 'Thank you for your business!',
                'show_item_wholesale_toggle' => true,
                'po_prefix' => 'PO-',
                'auto_update_product_cost' => true,
                'default_system_tax_ids' => [],
                'allow_manual_taxes_discounts' => true,
            ]);
        }
        return $setting;
    }
}
