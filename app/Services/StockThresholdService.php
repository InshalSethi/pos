<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\DB;

class StockThresholdService
{
    /**
     * Evaluate a product (and optionally its variations) against min_stock_level.
     * Fires a LowStockNotification to every company user if threshold is breached
     * and no unread duplicate already exists.
     */
    public function evaluate(Product $product): void
    {
        // Reload fresh values in case the model was just saved
        $product->refresh();

        $minAlert = (int) ($product->min_stock_level ?? 0);

        if ($product->variations()->exists()) {
            // ── Variant product ─────────────────────────────────────────────
            foreach ($product->variations as $variation) {
                $varStock    = (int) ($variation->stock_qty ?? 0);
                $varMinAlert = (int) ($variation->min_stock_alert ?? $minAlert);

                if ($varMinAlert > 0 && $varStock <= $varMinAlert) {
                    $label = $variation->name_string
                          ?? $variation->variation_name_string
                          ?? "Variant #{$variation->id}";

                    $this->dispatchIfNeeded(
                        $product,
                        $varStock,
                        $varMinAlert,
                        $label,
                    );
                }
            }
        } else {
            // ── Simple product ───────────────────────────────────────────────
            $stock = (int) ($product->stock_quantity ?? 0);

            if ($minAlert > 0 && $stock <= $minAlert) {
                $this->dispatchIfNeeded($product, $stock, $minAlert);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function dispatchIfNeeded(
        Product $product,
        int     $currentStock,
        int     $minAlert,
        string  $variantLabel = '',
    ): void {
        // Deduplicate: skip if an unread low_stock notification for this product
        // (and variant combo) already exists for any company user
        $alreadyNotified = DB::table('notifications')
            ->where('type', 'App\\Notifications\\LowStockNotification')
            ->whereNull('read_at')
            ->whereRaw("JSON_EXTRACT(data, '$.product_id') = ?", [$product->id])
            ->when($variantLabel !== '', function ($q) use ($variantLabel) {
                $q->whereRaw("JSON_EXTRACT(data, '$.variant_label') = ?", [$variantLabel]);
            })
            ->exists();

        if ($alreadyNotified) {
            return;
        }

        $notification = new LowStockNotification(
            productName:  $product->name,
            currentStock: $currentStock,
            minAlert:     $minAlert,
            variantLabel: $variantLabel,
            productId:    $product->id,
        );

        // Notify all users belonging to the same company
        $users = User::where('current_company_id', $product->company_id)->get();

        foreach ($users as $user) {
            $user->notify($notification);
        }
    }
}
