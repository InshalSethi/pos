<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\InventoryHistory;
use Illuminate\Support\Facades\DB;

class WarehouseInventoryService
{
    /**
     * Adjust stock for a product/variation in a specific warehouse.
     */
    public function adjustStock(
        int $warehouseId,
        int $productId,
        ?int $variationId,
        int $quantityChange,
        int $companyId,
        string $type = 'Manual Adjustment',
        ?string $referenceId = null
    ): void {
        DB::transaction(function () use ($warehouseId, $productId, $variationId, $quantityChange, $companyId, $type, $referenceId) {
            // Find or create inventory record
            $inventory = Inventory::firstOrCreate(
                [
                    'warehouse_id' => $warehouseId,
                    'product_id' => $productId,
                    'product_variation_id' => $variationId,
                ],
                [
                    'company_id' => $companyId,
                    'stock_qty' => 0,
                    'min_stock_level' => 0,
                ]
            );

            $inventory->stock_qty += $quantityChange;
            $inventory->save();

            // Log history
            InventoryHistory::create([
                'company_id' => $companyId,
                'product_id' => $productId,
                'product_variation_id' => $variationId,
                'warehouse_id' => $warehouseId,
                'quantity_changed' => $quantityChange,
                'type' => $type,
                'reference_id' => $referenceId,
            ]);

            // Sync total stock cache in products table
            $this->syncProductStockCache($productId, $variationId);
        });
    }

    /**
     * Set stock level for a product/variation in a specific warehouse (Recount).
     */
    public function setStock(
        int $warehouseId,
        int $productId,
        ?int $variationId,
        int $newQuantity,
        int $companyId,
        string $type = 'Manual Adjustment',
        ?string $referenceId = null
    ): void {
        DB::transaction(function () use ($warehouseId, $productId, $variationId, $newQuantity, $companyId, $type, $referenceId) {
            // Find current stock before update
            $inventory = Inventory::where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->where('product_variation_id', $variationId)
                ->first();

            $oldQuantity = $inventory ? $inventory->stock_qty : 0;
            $quantityChange = $newQuantity - $oldQuantity;

            // Find or create inventory record
            $inventory = Inventory::updateOrCreate(
                [
                    'warehouse_id' => $warehouseId,
                    'product_id' => $productId,
                    'product_variation_id' => $variationId,
                ],
                [
                    'company_id' => $companyId,
                    'stock_qty' => $newQuantity,
                ]
            );

            // Log history if there was a change
            if ($quantityChange !== 0) {
                InventoryHistory::create([
                    'company_id' => $companyId,
                    'product_id' => $productId,
                    'product_variation_id' => $variationId,
                    'warehouse_id' => $warehouseId,
                    'quantity_changed' => $quantityChange,
                    'type' => $type,
                    'reference_id' => $referenceId,
                ]);
            }

            // Sync total stock cache in products table
            $this->syncProductStockCache($productId, $variationId);
        });
    }

    /**
     * Sync the product/variation cached stock quantities.
     */
    public function syncProductStockCache(int $productId, ?int $variationId = null): void
    {
        $product = Product::find($productId);
        if (!$product) {
            return;
        }

        // 1. Sync variation stock if variationId is set
        if ($variationId) {
            $totalVarStock = Inventory::where('product_variation_id', $variationId)->sum('stock_qty');
            ProductVariation::where('id', $variationId)->update(['stock_qty' => $totalVarStock]);
        }

        // 2. Sync product total stock based on whether it has variations active
        if ($product->has_variations) {
            $totalProductStock = Inventory::where('product_id', $productId)
                ->whereNotNull('product_variation_id')
                ->sum('stock_qty');
        } else {
            $totalProductStock = Inventory::where('product_id', $productId)
                ->whereNull('product_variation_id')
                ->sum('stock_qty');
        }
        
        $product->update(['stock_quantity' => $totalProductStock]);

        // Evaluate thresholds
        try {
            (new StockThresholdService())->evaluate($product->fresh());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to evaluate stock threshold for product {$productId}: " . $e->getMessage());
        }
    }
}
