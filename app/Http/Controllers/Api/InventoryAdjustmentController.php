<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryAdjustment;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Services\WarehouseInventoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InventoryAdjustmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:inventory.view')->only(['index', 'show']);
        $this->middleware('permission:inventory.adjust')->only(['store']);
    }

    public function index(Request $request): JsonResponse
    {
        $query = InventoryAdjustment::with(['product.variations', 'user', 'warehouse', 'variation']);

        // Filter by product
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by adjustment type
        if ($request->has('adjustment_type')) {
            $query->where('adjustment_type', $request->adjustment_type);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('adjustment_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('adjustment_date', '<=', $request->end_date);
        }

        // Search by adjustment number
        if ($request->has('search')) {
            $query->where('adjustment_number', 'like', '%' . $request->search . '%');
        }

        $adjustments = $query->orderBy('adjustment_date', 'desc')
                            ->paginate($request->get('per_page', 15));

        return response()->json($adjustments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $hasBulk = $request->has('adjustments') && is_array($request->adjustments);

        if ($hasBulk) {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,id',
                'reason' => 'required|string|max:255',
                'notes' => 'nullable|string',
                'batch_number' => 'nullable|string|max:100',
                'expiry_date' => 'nullable|date',
                'attachment' => 'nullable|file|max:5120',
                'adjustments' => 'required|array|min:1',
                'adjustments.*.warehouse_id' => 'required|exists:warehouses,id',
                'adjustments.*.product_variation_id' => 'nullable|exists:product_variations,id',
                'adjustments.*.adjustment_type' => 'required|in:increase,decrease,recount',
                'adjustments.*.quantity_adjusted' => 'required|integer|min:0',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,id',
                'warehouse_id' => 'nullable|exists:warehouses,id',
                'product_variation_id' => 'nullable|exists:product_variations,id',
                'adjustment_type' => 'required|in:increase,decrease,recount',
                'quantity_adjusted' => 'required|integer|min:0',
                'reason' => 'required|string|max:255',
                'notes' => 'nullable|string',
                'batch_number' => 'nullable|string|max:100',
                'expiry_date' => 'nullable|date',
                'attachment' => 'nullable|file|max:5120',
            ]);
        }

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $companyId = auth()->user()->current_company_id;
            $product = Product::find($request->product_id);

            // Setup attachment
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $path = $request->file('attachment')->store('inventory-adjustments', 'public');
                $attachmentPath = '/storage/' . $path;
            }

            $adjustmentsData = [];
            if ($hasBulk) {
                $adjustmentsData = $request->adjustments;
            } else {
                $adjustmentsData = [[
                    'warehouse_id' => $request->warehouse_id,
                    'product_variation_id' => $request->product_variation_id,
                    'adjustment_type' => $request->adjustment_type,
                    'quantity_adjusted' => $request->quantity_adjusted,
                ]];
            }

            $createdAdjustments = [];
            $whService = new WarehouseInventoryService();

            foreach ($adjustmentsData as $adj) {
                $warehouseId = $adj['warehouse_id'] ?? null;
                $variationId = $adj['product_variation_id'] ?? null;
                $adjType = $adj['adjustment_type'];
                $quantityAdjusted = (int)$adj['quantity_adjusted'];

                // Get fallback warehouse if not provided
                if (!$warehouseId) {
                    $warehouse = Warehouse::where('company_id', $companyId)->where('is_default', true)->first();
                    if (!$warehouse) {
                        $warehouse = Warehouse::where('company_id', $companyId)->first();
                    }
                    if (!$warehouse) {
                        $warehouse = Warehouse::create([
                            'company_id' => $companyId,
                            'name' => 'Main Warehouse',
                            'is_default' => true,
                            'is_active' => true,
                        ]);
                    }
                    $warehouseId = $warehouse->id;
                }

                // Get current stock
                $inventory = Inventory::where('warehouse_id', $warehouseId)
                    ->where('product_id', $product->id)
                    ->where('product_variation_id', $variationId)
                    ->first();

                $quantityBefore = $inventory ? $inventory->stock_qty : 0;

                // Calculate quantity after
                switch ($adjType) {
                    case 'increase':
                        $quantityAfter = $quantityBefore + $quantityAdjusted;
                        break;
                    case 'decrease':
                        $quantityAfter = max(0, $quantityBefore - $quantityAdjusted);
                        $quantityAdjusted = $quantityBefore - $quantityAfter; // actual change
                        break;
                    case 'recount':
                        $quantityAfter = $quantityAdjusted;
                        $quantityAdjusted = $quantityAfter - $quantityBefore; // difference
                        break;
                }

                // Generate adjustment number securely
                $datePrefix = 'ADJ-' . date('Ymd') . '-';
                $lastAdjustment = InventoryAdjustment::where('adjustment_number', 'like', $datePrefix . '%')
                    ->orderBy('adjustment_number', 'desc')
                    ->lockForUpdate()
                    ->first();

                $newNumber = $lastAdjustment ? ((int) substr($lastAdjustment->adjustment_number, -4)) + 1 : 1;
                $adjustmentNumber = $datePrefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

                // Cost impact
                $costImpact = $quantityAdjusted * $product->cost_price;

                // Create adjustment record
                $adjustment = InventoryAdjustment::create([
                    'adjustment_number' => $adjustmentNumber,
                    'product_id' => $product->id,
                    'product_variation_id' => $variationId,
                    'warehouse_id' => $warehouseId,
                    'adjustment_type' => $adjType,
                    'quantity_before' => $quantityBefore,
                    'quantity_adjusted' => $quantityAdjusted,
                    'quantity_after' => $quantityAfter,
                    'reason' => $request->reason,
                    'user_id' => $request->user()?->id ?? auth()->id() ?? 1,
                    'adjustment_date' => now(),
                    'cost_impact' => $costImpact,
                    'notes' => $request->notes,
                    'batch_number' => $request->batch_number,
                    'expiry_date' => $request->expiry_date,
                    'attachment' => $attachmentPath,
                ]);

                // Update product stock in warehouse
                $whService->setStock($warehouseId, $product->id, $variationId, $quantityAfter, $companyId, 'Manual Adjustment', $adjustment->adjustment_number);

                $createdAdjustments[] = $adjustment;
            }

            DB::commit();

            // Load relations on first adjustment for response compatibility
            if (!empty($createdAdjustments)) {
                $createdAdjustments[0]->load(['product', 'user', 'warehouse', 'variation']);
            }

            return response()->json([
                'message' => 'Inventory adjustment created successfully',
                'adjustment' => $createdAdjustments[0] ?? null
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Adjustment Error: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());

            return response()->json([
                'message' => 'Failed to create inventory adjustment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryAdjustment $inventoryAdjustment): JsonResponse
    {
        $inventoryAdjustment->load(['product', 'user', 'warehouse', 'variation']);

        return response()->json($inventoryAdjustment);
    }

    /**
     * Get inventory summary
     */
    public function summary(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', today()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', today()->toDateString());

        $summary = [
            'total_adjustments' => InventoryAdjustment::whereBetween('adjustment_date', [$startDate, $endDate])->count(),
            'total_increases' => InventoryAdjustment::whereBetween('adjustment_date', [$startDate, $endDate])
                                                  ->where('adjustment_type', 'increase')
                                                  ->sum('quantity_adjusted'),
            'total_decreases' => InventoryAdjustment::whereBetween('adjustment_date', [$startDate, $endDate])
                                                  ->where('adjustment_type', 'decrease')
                                                  ->sum('quantity_adjusted'),
            'total_cost_impact' => InventoryAdjustment::whereBetween('adjustment_date', [$startDate, $endDate])
                                                    ->sum('cost_impact'),
            'low_stock_products' => Product::where('track_inventory', true)
                                          ->whereColumn('stock_quantity', '<=', 'min_stock_level')
                                          ->count(),
        ];

        return response()->json($summary);
    }

    /**
     * Get low stock products
     */
    public function lowStock(Request $request): JsonResponse
    {
        $products = Product::with('category')
            ->where('track_inventory', true)
            ->where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'min_stock_level')
            ->orderBy('stock_quantity', 'asc')
            ->paginate($request->get('per_page', 15));

        return response()->json($products);
    }

    /**
     * Import inventory adjustments from CSV/Excel
     */
    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,xlsx,xls,txt|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $handle = fopen($file->getPathname(), 'r');
            fgetcsv($handle); // Skip header

            DB::beginTransaction();
            $imported = 0;
            $errors = [];

            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) >= 2) {
                    $sku = trim($data[0]);
                    $quantity = (int) trim($data[1]);
                    $adjustmentType = isset($data[2]) && in_array(strtolower(trim($data[2])), ['increase', 'decrease', 'recount']) 
                        ? strtolower(trim($data[2])) 
                        : 'increase';
                    $reason = !empty($data[3]) ? trim($data[3]) : 'Bulk Import';

                    $product = Product::where('sku', $sku)->orWhere('barcode', $sku)->first();

                    if (!$product) {
                        $errors[] = "Product with SKU/Barcode {$sku} not found.";
                        continue;
                    }

                    $quantityBefore = $product->stock_quantity;
                    $quantityAdjusted = $quantity;

                    switch ($adjustmentType) {
                        case 'increase':
                            $quantityAfter = $quantityBefore + $quantityAdjusted;
                            break;
                        case 'decrease':
                            $quantityAfter = max(0, $quantityBefore - $quantityAdjusted);
                            $quantityAdjusted = $quantityBefore - $quantityAfter;
                            break;
                        case 'recount':
                            $quantityAfter = $quantityAdjusted;
                            $quantityAdjusted = abs($quantityAfter - $quantityBefore);
                            break;
                        default:
                            $quantityAfter = $quantityBefore + $quantityAdjusted;
                            $adjustmentType = 'increase';
                    }

                    $datePrefix = 'ADJ-' . date('Ymd') . '-';
                    $lastAdjustment = InventoryAdjustment::where('adjustment_number', 'like', $datePrefix . '%')
                        ->orderBy('adjustment_number', 'desc')
                        ->lockForUpdate()
                        ->first();
                        
                    $newNumber = $lastAdjustment ? ((int) substr($lastAdjustment->adjustment_number, -4)) + 1 : 1;
                    $adjustmentNumber = $datePrefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
                    
                    $costImpact = $quantityAdjusted * $product->cost_price;

                    InventoryAdjustment::create([
                        'adjustment_number' => $adjustmentNumber,
                        'product_id' => $product->id,
                        'adjustment_type' => $adjustmentType,
                        'quantity_before' => $quantityBefore,
                        'quantity_adjusted' => $quantityAdjusted,
                        'quantity_after' => $quantityAfter,
                        'reason' => $reason,
                        'user_id' => auth()->id() ?? 1,
                        'adjustment_date' => now(),
                        'cost_impact' => $costImpact,
                    ]);

                    $product->update(['stock_quantity' => $quantityAfter]);
                    try {
                        $this->verifyStockThresholds($product->id);
                    } catch (\Throwable $th) {
                        \Illuminate\Support\Facades\Log::warning('verifyStockThresholds failed in InventoryAdjustmentController import: ' . $th->getMessage());
                    }
                    $imported++;
                }
            }

            fclose($handle);
            DB::commit();

            return response()->json([
                'message' => "Successfully imported {$imported} inventory records.",
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Import failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Invoked immediately whenever items are checked out via POS or processed through stock adjustments.
     */
    public function verifyStockThresholds($productId, $variationId = null)
    {
        $product = \Illuminate\Support\Facades\DB::table('products')->where('id', $productId)->first();
        
        if (!$product) return;

        // Calculate total or specific local variant stock qty available
        if (is_null($variationId)) {
            // Simple Product Stock Calculation
            if (\Illuminate\Support\Facades\Schema::hasTable('inventories')) {
                $currentStock = \Illuminate\Support\Facades\DB::table('inventories')->where('product_id', $productId)->sum('stock_qty');
            } else {
                $currentStock = \Illuminate\Support\Facades\DB::table('products')->where('id', $productId)->value('stock_quantity') ?? 0;
            }
            $minAlertLimit = $product->min_stock_alert ?? ($product->min_stock_level ?? 0);
            $msg = "Product '{$product->name}' is running low! Only {$currentStock} items remaining.";
        } else {
            // Variant Specific Stock Calculation
            $variant = \Illuminate\Support\Facades\DB::table('product_variations')->where('id', $variationId)->first();
            if (!$variant) return;
            if (\Illuminate\Support\Facades\Schema::hasTable('inventories')) {
                $currentStock = \Illuminate\Support\Facades\DB::table('inventories')->where('product_variation_id', $variationId)->sum('stock_qty');
            } else {
                $currentStock = $variant->stock_qty ?? 0;
            }
            // Fallback to product alert if variant specific alert is unassigned
            $minAlertLimit = $variant->min_stock_alert ?? ($variant->min_stock_level ?? ($product->min_stock_alert ?? ($product->min_stock_level ?? 0)));
            $msg = "Variant '{$product->name} ({$variant->variation_name_string})' is low! Only {$currentStock} items left.";
        }

        // Trigger Notification insertion if bounds are breached
        if ($currentStock <= $minAlertLimit) {
            // Avoid inserting repetitive duplicate unread alerts for the same item
            $exists = \Illuminate\Support\Facades\DB::table('system_notifications')
                ->where('product_id', $productId)
                ->where('type', 'low_stock')
                ->where('is_read', false)
                ->exists();

            if (!$exists) {
                \Illuminate\Support\Facades\DB::table('system_notifications')->insert([
                    'company_id' => $product->company_id,
                    'product_id' => $productId,
                    'type' => 'low_stock',
                    'message' => $msg,
                    'is_read' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Also trigger LowStockNotification Laravel notification
                try {
                    $users = \App\Models\User::where('current_company_id', $product->company_id)->get();
                    $notification = new \App\Notifications\LowStockNotification(
                        productName:  $product->name,
                        currentStock: $currentStock,
                        minAlert:     $minAlertLimit,
                        variantLabel: $variant->variation_name_string ?? '',
                        productId:    $product->id,
                    );
                    foreach ($users as $user) {
                        $user->notify($notification);
                    }
                } catch (\Throwable $th) {
                    \Illuminate\Support\Facades\Log::warning('Failed sending low stock notification: ' . $th->getMessage());
                }
            }
        }
    }
}
