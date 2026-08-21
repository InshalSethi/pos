<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryHistory;
use App\Models\Product;
use App\Models\ProductRecipe;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Log;

class RecipeDeductionService
{
    protected WarehouseInventoryService $warehouseService;

    public function __construct()
    {
        $this->warehouseService = new WarehouseInventoryService();
    }

    /**
     * Auto-deduct raw materials for sold finished goods that have recipes configured.
     */
    public function deductForSale(Sale $sale): void
    {
        $sale->loadMissing(['saleItems.product', 'saleItems.productVariation']);

        $companyId = $sale->company_id;
        $warehouseId = $sale->warehouse_id ?: $this->getDefaultWarehouseId($companyId);

        if (!$warehouseId) {
            Log::warning("RecipeDeductionService: No warehouse ID found for sale #{$sale->id}");
            return;
        }

        foreach ($sale->saleItems as $saleItem) {
            $product = $saleItem->product;
            if (!$product || !$product->auto_deduct_ingredients) {
                continue;
            }

            // Check if recipe exists for product or variation
            $recipeQuery = ProductRecipe::where('company_id', $companyId)
                ->where('product_id', $product->id)
                ->where('is_active', true);

            if ($saleItem->product_variation_id) {
                $recipeQuery->where(function ($q) use ($saleItem) {
                    $q->where('product_variation_id', $saleItem->product_variation_id)
                        ->orWhereNull('product_variation_id');
                })->orderByRaw('product_variation_id IS NULL ASC'); // Prefer variation-specific recipe
            } else {
                $recipeQuery->whereNull('product_variation_id');
            }

            $recipe = $recipeQuery->with('ingredients.rawMaterial', 'ingredients.variation')->first();
            if (!$recipe || $recipe->ingredients->isEmpty()) {
                continue;
            }

            $soldQty = (float) $saleItem->quantity;
            $yieldQty = (float) ($recipe->yield_quantity ?: 1);
            $multiplier = $soldQty / $yieldQty;

            foreach ($recipe->ingredients as $ingredient) {
                $rawMaterial = $ingredient->rawMaterial;
                if (!$rawMaterial || !$rawMaterial->track_inventory) {
                    continue;
                }

                $wasteMultiplier = 1 + ((float) $ingredient->waste_percentage / 100);
                $requiredIngredientQty = (float) $ingredient->quantity * $multiplier * $wasteMultiplier;

                // Deduct stock from target warehouse
                $rawVarId = $ingredient->raw_material_variation_id;

                $this->warehouseService->adjustStock(
                    $warehouseId,
                    $rawMaterial->id,
                    $rawVarId,
                    -$requiredIngredientQty,
                    $companyId
                );

                // Record inventory history log
                $currentQty = $this->warehouseService->getStock($warehouseId, $rawMaterial->id, $rawVarId);

                InventoryHistory::create([
                    'company_id' => $companyId,
                    'product_id' => $rawMaterial->id,
                    'product_variation_id' => $rawVarId,
                    'warehouse_id' => $warehouseId,
                    'type' => 'sale',
                    'quantity' => -$requiredIngredientQty,
                    'new_stock_qty' => $currentQty,
                    'reference_type' => 'App\Models\Sale',
                    'reference_id' => $sale->id,
                    'notes' => "Recipe auto-deduction for sale item: {$product->name} (Sale #{$sale->invoice_number})",
                    'user_id' => auth()->id() ?: 1,
                ]);
            }
        }
    }

    protected function getDefaultWarehouseId($companyId): ?int
    {
        $defaultWh = \App\Models\Warehouse::where('company_id', $companyId)->where('is_default', true)->first();
        return $defaultWh ? $defaultWh->id : \App\Models\Warehouse::where('company_id', $companyId)->value('id');
    }
}
