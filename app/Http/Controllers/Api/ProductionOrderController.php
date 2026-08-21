<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryHistory;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\ProductRecipe;
use App\Models\Warehouse;
use App\Services\WarehouseInventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductionOrderController extends Controller
{
    protected WarehouseInventoryService $warehouseService;

    public function __construct()
    {
        $this->middleware('permission:products.view')->only(['index', 'show']);
        $this->middleware('permission:products.create')->only(['store']);
        $this->middleware('permission:products.edit')->only(['update', 'start', 'complete', 'cancel']);
        $this->middleware('permission:products.delete')->only(['destroy']);

        $this->warehouseService = new WarehouseInventoryService();
    }

    /**
     * Display a listing of production orders.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        $query = ProductionOrder::with([
            'recipe:id,name,yield_quantity',
            'product:id,name,sku,cost_price',
            'variation:id,combination_key,variation_name_string',
            'warehouse:id,name',
            'user:id,name',
            'items.rawMaterial:id,name,sku,cost_price',
            'items.variation:id,combination_key,variation_name_string'
        ])->where('company_id', $companyId);

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('production_number', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->get('warehouse_id'));
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = strtolower($request->get('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['production_number', 'production_date', 'status', 'total_cost', 'quantity_to_produce', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }

        $perPage = (int) $request->get('per_page', 15);
        $orders = $query->paginate($perPage);

        return response()->json($orders);
    }

    /**
     * Store a new production order.
     */
    public function store(Request $request): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        $validator = Validator::make($request->all(), [
            'recipe_id' => 'required|exists:product_recipes,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity_to_produce' => 'required|numeric|min:0.0001',
            'production_date' => 'required|date',
            'expiry_date' => 'nullable|date',
            'status' => 'nullable|string|in:draft,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
            'auto_complete' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $recipe = ProductRecipe::with(['ingredients.rawMaterial', 'ingredients.variation'])->findOrFail($request->recipe_id);

        DB::beginTransaction();
        try {
            $prodNumber = 'PROD-' . strtoupper(uniqid());

            $initialStatus = $request->input('status', 'draft');

            $productionOrder = ProductionOrder::create([
                'company_id' => $companyId,
                'production_number' => $prodNumber,
                'recipe_id' => $recipe->id,
                'product_id' => $recipe->product_id,
                'product_variation_id' => $recipe->product_variation_id,
                'warehouse_id' => $request->warehouse_id,
                'quantity_to_produce' => $request->quantity_to_produce,
                'status' => $initialStatus,
                'production_date' => $request->production_date,
                'expiry_date' => $request->expiry_date,
                'user_id' => auth()->id(),
                'notes' => $request->notes,
            ]);

            $yieldQty = (float) ($recipe->yield_quantity ?: 1);
            $multiplier = (float) $request->quantity_to_produce / $yieldQty;
            $totalOrderCost = 0;

            foreach ($recipe->ingredients as $ingredient) {
                $rawMat = $ingredient->rawMaterial;
                $unitCost = 0;
                if ($ingredient->variation) {
                    $unitCost = (float) ($ingredient->variation->cost_price ?? 0);
                } elseif ($rawMat) {
                    $unitCost = (float) ($rawMat->cost_price ?? 0);
                }

                $wasteMultiplier = 1 + ((float) $ingredient->waste_percentage / 100);
                $reqQty = (float) $ingredient->quantity * $multiplier * $wasteMultiplier;
                $itemTotalCost = round($reqQty * $unitCost, 2);

                $totalOrderCost += $itemTotalCost;

                ProductionOrderItem::create([
                    'production_order_id' => $productionOrder->id,
                    'raw_material_id' => $ingredient->raw_material_id,
                    'raw_material_variation_id' => $ingredient->raw_material_variation_id,
                    'unit_id' => $ingredient->unit_id,
                    'quantity_used' => $reqQty,
                    'unit_cost' => $unitCost,
                    'total_cost' => $itemTotalCost,
                ]);
            }

            $unitCost = $request->quantity_to_produce > 0 ? round($totalOrderCost / (float) $request->quantity_to_produce, 2) : 0;

            $productionOrder->update([
                'total_cost' => $totalOrderCost,
                'unit_cost' => $unitCost,
            ]);

            DB::commit();

            if ($request->boolean('auto_complete') || $initialStatus === 'completed') {
                return $this->completeOrderInternal($productionOrder->fresh());
            }

            return response()->json([
                'success' => true,
                'message' => 'Production order created successfully',
                'production_order' => $productionOrder->load('items.rawMaterial', 'product', 'warehouse')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show specified production order.
     */
    public function show(ProductionOrder $productionOrder): JsonResponse
    {
        $productionOrder->load([
            'recipe', 'product', 'variation', 'warehouse', 'user',
            'items.rawMaterial', 'items.variation'
        ]);

        return response()->json($productionOrder);
    }

    /**
     * Update specified production order.
     */
    public function update(Request $request, ProductionOrder $productionOrder): JsonResponse
    {
        if ($productionOrder->status === 'completed') {
            return response()->json(['success' => false, 'message' => 'Completed production orders cannot be modified.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'recipe_id' => 'required|exists:product_recipes,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity_to_produce' => 'required|numeric|min:0.0001',
            'production_date' => 'required|date',
            'expiry_date' => 'nullable|date',
            'status' => 'required|string|in:draft,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $recipe = ProductRecipe::with(['ingredients.rawMaterial', 'ingredients.variation'])->findOrFail($request->recipe_id);

        DB::beginTransaction();
        try {
            $newStatus = $request->input('status', $productionOrder->status);

            $productionOrder->update([
                'recipe_id' => $recipe->id,
                'product_id' => $recipe->product_id,
                'product_variation_id' => $recipe->product_variation_id,
                'warehouse_id' => $request->warehouse_id,
                'quantity_to_produce' => $request->quantity_to_produce,
                'status' => $newStatus,
                'production_date' => $request->production_date,
                'expiry_date' => $request->expiry_date,
                'notes' => $request->notes,
            ]);

            // Re-calculate items
            $productionOrder->items()->delete();
            $yieldQty = (float) ($recipe->yield_quantity ?: 1);
            $multiplier = (float) $request->quantity_to_produce / $yieldQty;
            $totalOrderCost = 0;

            foreach ($recipe->ingredients as $ingredient) {
                $rawMat = $ingredient->rawMaterial;
                $unitCost = 0;
                if ($ingredient->variation) {
                    $unitCost = (float) ($ingredient->variation->cost_price ?? 0);
                } elseif ($rawMat) {
                    $unitCost = (float) ($rawMat->cost_price ?? 0);
                }

                $wasteMultiplier = 1 + ((float) $ingredient->waste_percentage / 100);
                $reqQty = (float) $ingredient->quantity * $multiplier * $wasteMultiplier;
                $itemTotalCost = round($reqQty * $unitCost, 2);

                $totalOrderCost += $itemTotalCost;

                ProductionOrderItem::create([
                    'production_order_id' => $productionOrder->id,
                    'raw_material_id' => $ingredient->raw_material_id,
                    'raw_material_variation_id' => $ingredient->raw_material_variation_id,
                    'unit_id' => $ingredient->unit_id,
                    'quantity_used' => $reqQty,
                    'unit_cost' => $unitCost,
                    'total_cost' => $itemTotalCost,
                ]);
            }

            $unitCost = $request->quantity_to_produce > 0 ? round($totalOrderCost / (float) $request->quantity_to_produce, 2) : 0;
            $productionOrder->update([
                'total_cost' => $totalOrderCost,
                'unit_cost' => $unitCost,
            ]);

            DB::commit();

            if ($newStatus === 'completed') {
                return $this->completeOrderInternal($productionOrder->fresh());
            }

            return response()->json([
                'success' => true,
                'message' => 'Production order updated successfully',
                'production_order' => $productionOrder->fresh(['items.rawMaterial', 'product', 'warehouse'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete production order.
     */
    public function destroy(ProductionOrder $productionOrder): JsonResponse
    {
        if ($productionOrder->status === 'completed') {
            return response()->json(['success' => false, 'message' => 'Completed production orders cannot be deleted.'], 422);
        }

        $productionOrder->items()->delete();
        $productionOrder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Production order deleted successfully'
        ]);
    }

    /**
     * Complete production order: Deduct raw materials, Add finished goods to stock.
     */
    public function complete(ProductionOrder $productionOrder): JsonResponse
    {
        if ($productionOrder->status === 'completed') {
            return response()->json(['success' => false, 'message' => 'Production order is already completed.'], 422);
        }

        if ($productionOrder->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Cancelled production orders cannot be completed.'], 422);
        }

        return $this->completeOrderInternal($productionOrder);
    }

    protected function completeOrderInternal(ProductionOrder $productionOrder): JsonResponse
    {
        DB::beginTransaction();
        try {
            $companyId = $productionOrder->company_id;
            $warehouseId = $productionOrder->warehouse_id;
            $qtyToProduce = (float) $productionOrder->quantity_to_produce;

            // 1. Deduct raw materials from inventory
            $productionOrder->loadMissing('items.rawMaterial');

            foreach ($productionOrder->items as $item) {
                $rawMat = $item->rawMaterial;
                if (!$rawMat) {
                    continue;
                }

                $qtyUsed = (float) $item->quantity_used;

                $this->warehouseService->adjustStock(
                    $warehouseId,
                    $rawMat->id,
                    $item->raw_material_variation_id,
                    -$qtyUsed,
                    $companyId,
                    'Production Order Consumption',
                    (string) $productionOrder->id
                );
            }

            // 2. Add finished good product to inventory
            $product = $productionOrder->product;
            if ($product) {
                // Ensure product has track_inventory enabled if adding stock
                if (!$product->track_inventory) {
                    $product->update(['track_inventory' => true]);
                }

                $this->warehouseService->adjustStock(
                    $warehouseId,
                    $product->id,
                    $productionOrder->product_variation_id,
                    $qtyToProduce,
                    $companyId,
                    'Production Order Output',
                    (string) $productionOrder->id
                );

                // Update product cost price if calculated production unit cost is higher/different
                if ($productionOrder->unit_cost > 0 && !$productionOrder->product_variation_id) {
                    $product->update(['cost_price' => $productionOrder->unit_cost]);
                }
            }

            // 3. Mark production order completed
            $productionOrder->update([
                'status' => 'completed',
                'quantity_produced' => $qtyToProduce,
                'completed_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Production order completed and stock updated successfully',
                'production_order' => $productionOrder->fresh(['product', 'warehouse', 'items'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancel production order.
     */
    public function cancel(ProductionOrder $productionOrder): JsonResponse
    {
        if ($productionOrder->status === 'completed') {
            return response()->json(['success' => false, 'message' => 'Completed production orders cannot be cancelled.'], 422);
        }

        $productionOrder->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Production order cancelled successfully',
            'production_order' => $productionOrder
        ]);
    }
}
