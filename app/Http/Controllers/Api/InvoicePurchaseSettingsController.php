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
            'po_prefix' => 'nullable|string|max:20',
            'default_purchase_warehouse_id' => 'nullable|exists:warehouses,id',
            'auto_update_product_cost' => 'nullable|boolean',
            'default_system_tax_ids' => 'nullable|array',
            'allow_manual_taxes_discounts' => 'nullable|boolean',
        ]);

        $settings = InvoicePurchaseSetting::getSettings();
        $settings->update($validated);

        return response()->json([
            'message' => 'Invoice & Purchase settings updated successfully',
            'settings' => $settings->fresh()
        ]);
    }
}
