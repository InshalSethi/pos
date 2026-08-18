<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoicePurchaseSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoicePurchaseSettingsController extends Controller
{
    /**
     * Get Invoice & Purchase settings.
     */
    public function index(): JsonResponse
    {
        $settings = InvoicePurchaseSetting::getSettings();
        return response()->json($settings);
    }

    /**
     * Update Invoice & Purchase settings.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_prefix' => 'nullable|string|max:20',
            'default_pricing_mode' => 'nullable|string|in:retail,wholesale',
            'default_due_period_days' => 'nullable|integer|min:0|max:365',
            'default_terms_conditions' => 'nullable|string',
            'show_item_wholesale_toggle' => 'nullable|boolean',
            'sale_invoice_template' => 'nullable|string|in:default,classic,modern',
            'template_color' => 'nullable|string|max:50',
            'default_printer' => 'nullable|string|in:standard,thermal',
            'thermal_receipt_template' => 'nullable|string|in:classic,modern',
            'po_prefix' => 'nullable|string|max:20',
            'default_purchase_warehouse_id' => 'nullable|exists:warehouses,id',
            'auto_update_product_cost' => 'nullable|boolean',
            'default_system_tax_ids' => 'nullable|array',
            'allow_manual_taxes_discounts' => 'nullable|boolean',
            'invoice_title' => 'nullable|string|max:255',
            'invoice_subheading' => 'nullable|string|max:255',
            'logo_width' => 'nullable|integer|min:20|max:1000',
            'logo_height' => 'nullable|integer|min:20|max:1000',
            'default_notes' => 'nullable|string',
            'default_footer' => 'nullable|string',
            'column_item_name' => 'nullable|string|max:100',
            'column_price_name' => 'nullable|string|max:100',
            'column_quantity_name' => 'nullable|string|max:100',
            'hide_item_description' => 'nullable|boolean',
            'hide_amount' => 'nullable|boolean',
            'thermal_title' => 'nullable|string|max:255',
            'thermal_subheading' => 'nullable|string|max:255',
            'thermal_logo_width' => 'nullable|integer|min:20|max:1000',
            'thermal_logo_height' => 'nullable|integer|min:20|max:1000',
            'thermal_notes' => 'nullable|string',
            'thermal_footer' => 'nullable|string',
            'thermal_column_item_name' => 'nullable|string|max:100',
            'thermal_column_price_name' => 'nullable|string|max:100',
            'thermal_column_quantity_name' => 'nullable|string|max:100',
            'thermal_hide_item_description' => 'nullable|boolean',
            'thermal_hide_amount' => 'nullable|boolean',
        ]);

        $settings = InvoicePurchaseSetting::getSettings();
        $settings->update($validated);

        return response()->json([
            'message' => 'Invoice & Purchase settings updated successfully',
            'settings' => $settings->fresh()
        ]);
    }
}
