<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\HasUtcDatabaseTimezones;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePurchaseSetting extends Model
{
    use SoftDeletes;

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
        'sale_invoice_template',
        'template_color',
        'default_printer',
        'thermal_receipt_template',
        'po_prefix',
        'default_purchase_warehouse_id',
        'auto_update_product_cost',
        'default_system_tax_ids',
        'allow_manual_taxes_discounts',
        'invoice_title',
        'invoice_subheading',
        'logo_width',
        'logo_height',
        'default_notes',
        'default_footer',
        'column_item_name',
        'column_price_name',
        'column_quantity_name',
        'hide_item_description',
        'hide_amount',
        'thermal_title',
        'thermal_subheading',
        'thermal_logo_width',
        'thermal_logo_height',
        'thermal_notes',
        'thermal_footer',
        'thermal_column_item_name',
        'thermal_column_price_name',
        'thermal_column_quantity_name',
        'thermal_hide_item_description',
        'thermal_hide_amount',
    ];

    protected $casts = [
        'default_due_period_days' => 'integer',
        'show_item_wholesale_toggle' => 'boolean',
        'auto_update_product_cost' => 'boolean',
        'default_system_tax_ids' => 'array',
        'allow_manual_taxes_discounts' => 'boolean',
        'logo_width' => 'integer',
        'logo_height' => 'integer',
        'hide_item_description' => 'boolean',
        'hide_amount' => 'boolean',
        'thermal_logo_width' => 'integer',
        'thermal_logo_height' => 'integer',
        'thermal_hide_item_description' => 'boolean',
        'thermal_hide_amount' => 'boolean',
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
                'sale_invoice_template' => 'default',
                'template_color' => 'slate-400',
                'default_printer' => 'standard',
                'thermal_receipt_template' => 'classic',
                'po_prefix' => 'PO-',
                'auto_update_product_cost' => true,
                'default_system_tax_ids' => [],
                'allow_manual_taxes_discounts' => true,
                'invoice_title' => 'Invoice',
                'invoice_subheading' => null,
                'logo_width' => 128,
                'logo_height' => 128,
                'default_notes' => null,
                'default_footer' => null,
                'column_item_name' => 'Items',
                'column_price_name' => 'Price',
                'column_quantity_name' => 'Quantity',
                'hide_item_description' => false,
                'hide_amount' => false,
                'thermal_title' => 'Receipt',
                'thermal_subheading' => null,
                'thermal_logo_width' => 64,
                'thermal_logo_height' => 64,
                'thermal_notes' => null,
                'thermal_footer' => null,
                'thermal_column_item_name' => 'Items',
                'thermal_column_price_name' => 'Price',
                'thermal_column_quantity_name' => 'Quantity',
                'thermal_hide_item_description' => false,
                'thermal_hide_amount' => false,
            ]);
        }
        return $setting;
    }
}
