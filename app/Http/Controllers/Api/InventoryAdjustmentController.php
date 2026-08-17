<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryAdjustment;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
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
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by adjustment type
        if ($request->filled('adjustment_type')) {
            $query->where('adjustment_type', $request->adjustment_type);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('adjustment_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('adjustment_date', '<=', $request->end_date);
        }

        // Search by adjustment number or product name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('adjustment_number', 'like', '%' . $search . '%')
                  ->orWhereHas('product', function($pq) use ($search) {
                      $pq->where('name', 'like', '%' . $search . '%');
                  });
            });
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

                // Cost impact calculation supporting purchase_price / cost_price / unit_cost
                $rawCostVal = $request->input('purchase_price') ?? $request->input('cost_price') ?? $request->input('unit_cost') ?? $adj['purchase_price'] ?? $adj['cost_price'] ?? null;
                if ($rawCostVal !== null && $rawCostVal !== '' && floatval($rawCostVal) > 0) {
                    $unitCost = floatval($rawCostVal);
                    $product->update(['cost_price' => $unitCost]);
                } else {
                    $unitCost = floatval($product->cost_price ?? $product->purchase_price ?? 0);
                }

                $costImpact = round($quantityAdjusted * $unitCost, 2);

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

                // Perform Double-Entry Posting for COA 1040 Sync
                if ($costImpact != 0) {
                    $assetAccount = Account::where('company_id', $companyId)->where('account_code', '1040')->first()
                        ?? Account::where('company_id', $companyId)->where('account_code', '1500')->first();
                    $gainAccount = Account::where('company_id', $companyId)->where('account_code', '5010')->first();

                    if ($assetAccount) {
                        $absAmt = abs($costImpact);
                        $je = JournalEntry::create([
                            'company_id' => $companyId,
                            'entry_number' => 'JE-ADJ-' . date('YmdHis') . '-' . rand(100, 999),
                            'entry_date' => now()->toDateString(),
                            'reference' => $adjustment->adjustment_number,
                            'description' => "Inventory Adjustment Ledger Post ({$request->reason})",
                            'entry_type' => 'adjustment',
                            'status' => 'posted',
                            'total_debit' => $absAmt,
                            'total_credit' => $absAmt,
                            'created_by' => auth()->id() ?? 1,
                        ]);

                        JournalEntryLine::create([
                            'journal_entry_id' => $je->id,
                            'account_id' => $assetAccount->id,
                            'debit_amount' => $costImpact > 0 ? $absAmt : 0,
                            'credit_amount' => $costImpact < 0 ? $absAmt : 0,
                            'description' => 'Inventory Asset Valuation Update',
                        ]);

                        if ($gainAccount) {
                            JournalEntryLine::create([
                                'journal_entry_id' => $je->id,
                                'account_id' => $gainAccount->id,
                                'debit_amount' => $costImpact < 0 ? $absAmt : 0,
                                'credit_amount' => $costImpact > 0 ? $absAmt : 0,
                                'description' => 'Inventory Adjustment Gain/Loss',
                            ]);
                            $gainAccount->increment('current_balance', $costImpact > 0 ? $absAmt : -$absAmt);
                        }

                        $assetAccount->increment('current_balance', $costImpact > 0 ? $absAmt : -$absAmt);
                        $assetAccount->updateCurrentBalance();
                    }
                }

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
        $warehouseId = $request->get('warehouse_id');

        $lowStockQuery = \App\Models\Inventory::whereHas('product', function ($q) {
            $q->where('track_inventory', true)
              ->where('is_active', true);
        })
        ->where(function ($q) {
            $q->whereRaw('inventories.stock_qty <= COALESCE(inventories.min_stock_level, 0)')
              ->orWhere(function ($sub) {
                  $sub->whereNull('inventories.min_stock_level')
                      ->whereHas('product', function ($pq) {
                          $pq->whereRaw('inventories.stock_qty <= COALESCE(products.min_stock_level, 0)');
                      });
              });
        });

        if ($warehouseId) {
            $lowStockQuery->where('warehouse_id', $warehouseId);
        }

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
            'low_stock_products' => $lowStockQuery->count(),
        ];

        return response()->json($summary);
    }

    /**
     * Get low stock products
     */
    public function lowStock(Request $request): JsonResponse
    {
        $warehouseId = $request->get('warehouse_id');

        $query = \App\Models\Inventory::with(['product.category', 'variation', 'warehouse'])
            ->whereHas('product', function ($q) {
                $q->where('track_inventory', true)
                  ->where('is_active', true);
            })
            ->where(function ($q) {
                $q->whereRaw('inventories.stock_qty <= COALESCE(inventories.min_stock_level, 0)')
                  ->orWhere(function ($sub) {
                      $sub->whereNull('inventories.min_stock_level')
                          ->whereHas('product', function ($pq) {
                              $pq->whereRaw('inventories.stock_qty <= COALESCE(products.min_stock_level, 0)');
                          });
                  });
            });

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%");
                })->orWhereHas('variation', function ($vq) use ($search) {
                    $vq->where('sku', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%")
                      ->orWhere('variation_name_string', 'like', "%{$search}%");
                });
            });
        }

        $inventories = $query->orderBy('stock_qty', 'asc')
            ->paginate($request->get('per_page', 15));

        // Format for frontend response
        $formattedItems = $inventories->getCollection()->map(function ($inv) {
            $product = $inv->product;
            $variation = $inv->variation;
            
            $name = $product->name;
            if ($variation && $variation->variation_name_string && $variation->variation_name_string !== 'Default') {
                $name .= ' (' . $variation->variation_name_string . ')';
            }
            
            return [
                'id' => $product->id, // keep it for compatibility
                'product_id' => $product->id,
                'product_variation_id' => $inv->product_variation_id,
                'name' => $name,
                'sku' => $variation ? $variation->sku : $product->sku,
                'category' => $product->category,
                'warehouse_name' => $inv->warehouse->name ?? 'N/A',
                'warehouse_id' => $inv->warehouse_id,
                'min_stock_level' => $inv->min_stock_level ?? $product->min_stock_level ?? 0,
                'stock_quantity' => $inv->stock_qty,
            ];
        });

        // We replace the paginated collection with formatted items
        $inventories->setCollection($formattedItems);

        return response()->json($inventories);
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

    /**
     * Preview products for bulk price & tax adjustments
     */
    public function previewProducts(Request $request): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;
        $query = Product::with(['category', 'category.tax', 'brand', 'unit', 'variations'])
            ->where('company_id', $companyId)
            ->where('status', '!=', 'draft');

        $targetType = $request->get('target_type', 'all');
        $targetIds = $request->get('target_ids', $request->get('target_id'));
        if (is_string($targetIds)) {
            $targetIds = array_filter(explode(',', $targetIds));
        } elseif ($targetIds !== null && !is_array($targetIds)) {
            $targetIds = [$targetIds];
        } else {
            $targetIds = $targetIds ?? [];
        }

        if ($targetType === 'category' && !empty($targetIds)) {
            $categoryIds = [];
            foreach ($targetIds as $tid) {
                $categoryIds[] = (int) $tid;
                if ($request->boolean('apply_to_subcategories')) {
                    $category = \App\Models\Category::find($tid);
                    if ($category) {
                        $categoryIds = array_merge($categoryIds, $this->getCategoryDescendants($category));
                    }
                }
            }
            $query->whereIn('category_id', array_unique($categoryIds));
        } elseif ($targetType === 'brand' && !empty($targetIds)) {
            $query->whereIn('brand_id', array_map('intval', $targetIds));
        } elseif ($targetType === 'warehouse' && !empty($targetIds)) {
            $query->whereHas('inventories', function ($q) use ($targetIds) {
                $q->whereIn('warehouse_id', array_map('intval', $targetIds));
            });
        } elseif ($targetType === 'products' && ($request->filled('product_ids') || !empty($targetIds))) {
            $pIds = $request->filled('product_ids') ? $request->product_ids : $targetIds;
            $productIds = is_array($pIds) ? $pIds : explode(',', $pIds);
            $query->whereIn('id', array_map('intval', $productIds));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->get();

        // Calculate preview metrics
        $actionType = $request->get('action_type');
        $val = floatval($request->get('value', 0));
        $taxId = $request->get('tax_id');
        $targetTax = $taxId ? \App\Models\Tax::find($taxId) : null;

        $formatted = $products->map(function ($p) use ($actionType, $val, $targetTax) {
            $currentPrice = floatval($p->selling_price);
            $costPrice = floatval($p->cost_price);
            $newPrice = $currentPrice;

            if ($actionType === 'markup_percentage') {
                $base = $costPrice > 0 ? $costPrice : $currentPrice;
                $newPrice = round($base * (1 + $val / 100), 2);
            } elseif ($actionType === 'discount_percentage') {
                $newPrice = round($currentPrice * (1 - $val / 100), 2);
            } elseif ($actionType === 'fixed_price_override') {
                $newPrice = round($val, 2);
            }

            return [
                'id' => $p->id,
                'sku' => $p->sku,
                'name' => $p->name,
                'category_name' => $p->category->name ?? 'N/A',
                'brand_name' => $p->brand->name ?? 'N/A',
                'cost_price' => $costPrice,
                'current_price' => $currentPrice,
                'new_price' => $newPrice,
                'current_tax_rate' => floatval($p->tax_rate),
                'current_tax_name' => $p->category->tax->name ?? ($p->tax_rate > 0 ? "Standard ({$p->tax_rate}%)" : 'None'),
                'new_tax_rate' => $targetTax ? floatval($targetTax->value) : 0,
                'new_tax_name' => $targetTax ? $targetTax->name : 'None',
                'stock_quantity' => $p->stock_quantity,
            ];
        });

        return response()->json([
            'total' => $formatted->count(),
            'products' => $formatted,
        ]);
    }

    /**
     * Execute bulk price adjustment
     */
    public function bulkPriceAdjustment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'target_type' => 'required|string|in:all,category,brand,warehouse,products',
            'target_id' => 'nullable|integer',
            'product_ids' => 'nullable|array',
            'action_type' => 'required|string|in:markup_percentage,discount_percentage,fixed_price_override',
            'value' => 'required|numeric|min:0',
            'apply_to_subcategories' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $companyId = auth()->user()->current_company_id;
        $actionType = $request->input('action_type');
        $val = floatval($request->input('value', 0));
        $targetType = $request->input('target_type');
        $targetIds = $request->input('target_ids', $request->input('target_id'));
        if (is_string($targetIds)) {
            $targetIds = array_filter(explode(',', $targetIds));
        } elseif ($targetIds !== null && !is_array($targetIds)) {
            $targetIds = [$targetIds];
        } else {
            $targetIds = $targetIds ?? [];
        }

        $query = Product::where('company_id', $companyId)->where('status', '!=', 'draft');

        if ($targetType === 'category' && !empty($targetIds)) {
            $categoryIds = [];
            foreach ($targetIds as $tid) {
                $categoryIds[] = (int) $tid;
                if ($request->boolean('apply_to_subcategories')) {
                    $category = \App\Models\Category::find($tid);
                    if ($category) {
                        $categoryIds = array_merge($categoryIds, $this->getCategoryDescendants($category));
                    }
                }
            }
            $query->whereIn('category_id', array_unique($categoryIds));
        } elseif ($targetType === 'brand' && !empty($targetIds)) {
            $query->whereIn('brand_id', array_map('intval', $targetIds));
        } elseif ($targetType === 'warehouse' && !empty($targetIds)) {
            $query->whereHas('inventories', function ($q) use ($targetIds) {
                $q->whereIn('warehouse_id', array_map('intval', $targetIds));
            });
        } elseif ($targetType === 'products' && ($request->filled('product_ids') || !empty($targetIds))) {
            $pIds = $request->filled('product_ids') ? $request->product_ids : $targetIds;
            $productIds = is_array($pIds) ? $pIds : explode(',', $pIds);
            $query->whereIn('id', array_map('intval', $productIds));
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            return response()->json(['message' => 'No matching products found to update.'], 422);
        }

        DB::beginTransaction();
        try {
            $updatedCount = 0;
            foreach ($products as $product) {
                $currentSelling = floatval($product->selling_price);
                $costPrice = floatval($product->cost_price);

                if ($actionType === 'markup_percentage') {
                    $base = $costPrice > 0 ? $costPrice : $currentSelling;
                    $newSelling = round($base * (1 + $val / 100), 2);
                    $product->update([
                        'selling_price' => $newSelling,
                        'markup_percentage' => $val
                    ]);
                } elseif ($actionType === 'discount_percentage') {
                    $newSelling = round($currentSelling * (1 - $val / 100), 2);
                    $product->update([
                        'selling_price' => $newSelling,
                        'discount_type' => 'percentage',
                        'discount_value' => $val
                    ]);
                } elseif ($actionType === 'fixed_price_override') {
                    $newSelling = round($val, 2);
                    $product->update([
                        'selling_price' => $newSelling
                    ]);
                }

                // Also update variations retail price if present
                DB::table('product_variations')
                    ->where('product_id', $product->id)
                    ->update(['retail_price' => $product->selling_price]);

                $updatedCount++;
            }

            // Log entry in inventory_adjustments
            $datePrefix = 'ADJ-PRC-' . date('Ymd') . '-';
            $lastAdj = InventoryAdjustment::where('adjustment_number', 'like', $datePrefix . '%')
                ->orderBy('adjustment_number', 'desc')
                ->first();
            $newNum = $lastAdj ? ((int) substr($lastAdj->adjustment_number, -4)) + 1 : 1;
            $adjNum = $datePrefix . str_pad($newNum, 4, '0', STR_PAD_LEFT);

            $firstProd = $products->first();
            $defaultWh = Warehouse::where('company_id', $companyId)->where('is_default', true)->first()
                ?? Warehouse::where('company_id', $companyId)->first();

            InventoryAdjustment::create([
                'adjustment_number' => $adjNum,
                'product_id' => $firstProd->id,
                'warehouse_id' => $defaultWh->id ?? null,
                'adjustment_type' => 'recount',
                'quantity_before' => $firstProd->stock_quantity,
                'quantity_adjusted' => 0,
                'quantity_after' => $firstProd->stock_quantity,
                'reason' => "Bulk Price Adjustment ({$actionType}: {$val}) on {$updatedCount} products",
                'user_id' => auth()->id() ?? 1,
                'adjustment_date' => now(),
                'cost_impact' => 0,
                'notes' => "Updated selling prices for target [{$targetType}] via Bulk Price Adjustment rule.",
            ]);

            DB::commit();

            return response()->json([
                'message' => "Successfully updated prices for {$updatedCount} products.",
                'updated_count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to apply bulk price adjustment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Execute bulk tax adjustment
     */
    public function bulkTaxAdjustment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'target_type' => 'required|string|in:all,category,brand,products',
            'target_id' => 'nullable|integer',
            'product_ids' => 'nullable|array',
            'tax_id' => 'nullable|exists:taxes,id',
            'apply_to_subcategories' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $companyId = auth()->user()->current_company_id;
        $targetType = $request->input('target_type');
        $targetIds = $request->input('target_ids', $request->input('target_id'));
        if (is_string($targetIds)) {
            $targetIds = array_filter(explode(',', $targetIds));
        } elseif ($targetIds !== null && !is_array($targetIds)) {
            $targetIds = [$targetIds];
        } else {
            $targetIds = $targetIds ?? [];
        }
        $taxId = $request->input('tax_id');

        $tax = $taxId ? \App\Models\Tax::where('company_id', $companyId)->find($taxId) : null;
        $taxRate = $tax ? floatval($tax->value) : 0;

        $query = Product::where('company_id', $companyId)->where('status', '!=', 'draft');

        if ($targetType === 'category' && !empty($targetIds)) {
            $categoryIds = [];
            foreach ($targetIds as $tid) {
                $categoryIds[] = (int) $tid;
                if ($request->boolean('apply_to_subcategories')) {
                    $category = \App\Models\Category::find($tid);
                    if ($category) {
                        $categoryIds = array_merge($categoryIds, $this->getCategoryDescendants($category));
                    }
                }
            }
            $query->whereIn('category_id', array_unique($categoryIds));

            // Also update category default tax_id
            \App\Models\Category::whereIn('id', array_unique($categoryIds))->update(['tax_id' => $taxId]);
        } elseif ($targetType === 'brand' && !empty($targetIds)) {
            $query->whereIn('brand_id', array_map('intval', $targetIds));
        } elseif ($targetType === 'products' && ($request->filled('product_ids') || !empty($targetIds))) {
            $pIds = $request->filled('product_ids') ? $request->product_ids : $targetIds;
            $productIds = is_array($pIds) ? $pIds : explode(',', $pIds);
            $query->whereIn('id', array_map('intval', $productIds));
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            return response()->json(['message' => 'No matching products found to update.'], 422);
        }

        DB::beginTransaction();
        try {
            $updatedCount = 0;
            foreach ($products as $product) {
                $product->update([
                    'tax_id' => $taxId,
                    'tax_rate' => $taxRate,
                ]);

                // Update product variations
                DB::table('product_variations')
                    ->where('product_id', $product->id)
                    ->update(['tax_rate' => $taxRate]);

                $updatedCount++;
            }

            // Log entry in inventory_adjustments
            $datePrefix = 'ADJ-TAX-' . date('Ymd') . '-';
            $lastAdj = InventoryAdjustment::where('adjustment_number', 'like', $datePrefix . '%')
                ->orderBy('adjustment_number', 'desc')
                ->first();
            $newNum = $lastAdj ? ((int) substr($lastAdj->adjustment_number, -4)) + 1 : 1;
            $adjNum = $datePrefix . str_pad($newNum, 4, '0', STR_PAD_LEFT);

            $firstProd = $products->first();
            $defaultWh = Warehouse::where('company_id', $companyId)->where('is_default', true)->first()
                ?? Warehouse::where('company_id', $companyId)->first();

            InventoryAdjustment::create([
                'adjustment_number' => $adjNum,
                'product_id' => $firstProd->id,
                'warehouse_id' => $defaultWh->id ?? null,
                'adjustment_type' => 'recount',
                'quantity_before' => $firstProd->stock_quantity,
                'quantity_adjusted' => 0,
                'quantity_after' => $firstProd->stock_quantity,
                'reason' => "Bulk Tax Adjustment (" . ($tax ? $tax->name : 'None') . " - {$taxRate}%) on {$updatedCount} products",
                'user_id' => auth()->id() ?? 1,
                'adjustment_date' => now(),
                'cost_impact' => 0,
                'notes' => "Updated assigned tax group to " . ($tax ? $tax->name : 'Unassigned') . " for target [{$targetType}].",
            ]);

            DB::commit();

            return response()->json([
                'message' => "Successfully updated tax rate for {$updatedCount} products.",
                'updated_count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to apply bulk tax adjustment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle single item adjustment API request (/api/inventory/adjustments)
     */
    public function singleAdjustment(Request $request): JsonResponse
    {
        $itemId = $request->input('item_id') ?? $request->input('product_id');
        $adjQty = $request->input('adj_qty') ?? $request->input('quantity_adjusted', 0);
        $price = $request->input('purchase_price') ?? $request->input('cost_price');
        $type = $request->input('adjustment_type', 'increase');
        $warehouseId = $request->input('warehouse_id');

        $userCompanyId = auth()->user()->current_company_id ?? 1;

        if (!$warehouseId) {
            $warehouse = Warehouse::where('company_id', $userCompanyId)->where('is_default', true)->first()
                ?? Warehouse::where('company_id', $userCompanyId)->first()
                ?? Warehouse::first();
            $warehouseId = $warehouse->id ?? 1;
        }

        $transformedRequest = new Request([
            'reason' => $request->input('reason', 'Single Item Adjustment'),
            'items' => [
                [
                    'product_id' => $itemId,
                    'warehouse_id' => $warehouseId,
                    'adjustment_type' => $type,
                    'quantity_adjusted' => floatval($adjQty),
                    'cost_price' => floatval($price),
                ]
            ]
        ]);

        return $this->batchMatrixAdjustment($transformedRequest);
    }

    /**
     * Unified Matrix Batch Inventory Adjustment
     */
    public function batchMatrixAdjustment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.product_variation_id' => 'nullable|integer',
            'items.*.warehouse_id' => 'required|integer',
            'items.*.adjustment_type' => 'nullable|string|in:increase,decrease,recount',
            'items.*.quantity_adjusted' => 'nullable|numeric',
            'items.*.min_stock_level' => 'nullable|numeric',
            'items.*.selling_price' => 'nullable|numeric',
            'items.*.wholesale_price' => 'nullable|numeric',
            'items.*.cost_price' => 'nullable|numeric',
            'items.*.tax_id' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $companyId = auth()->user()->current_company_id;
        $userId = auth()->id() ?? 1;
        $reason = $request->input('reason', 'Unified Matrix Adjustment');
        $notes = $request->input('notes', '');
        $items = $request->input('items', []);

        DB::beginTransaction();
        try {
            $createdAdjustments = [];
            $totalCostImpact = 0;
            $updatedProductCount = 0;
            $previousAuditData = [];
            $updatedAuditData = [];

            // Get or initialize Chart of Accounts (COA 1040 / 1500 Inventory Asset)
            $assetAccount = Account::where('company_id', $companyId)->where('account_code', '1040')->first();
            if (!$assetAccount) {
                $assetAccount = Account::where('company_id', $companyId)
                    ->where(function($q) {
                        $q->where('account_code', '1500')
                          ->orWhere('account_name', 'LIKE', '%Inventory Asset%')
                          ->orWhere('account_name', 'LIKE', '%Inventory%');
                    })->first();
            }
            if (!$assetAccount) {
                $assetAccount = Account::create([
                    'company_id' => $companyId,
                    'account_code' => '1040',
                    'account_name' => 'Inventory Asset',
                    'account_type' => 'asset',
                    'account_subtype' => 'current_asset',
                    'is_active' => true,
                    'is_system_account' => true,
                    'opening_balance' => 0,
                    'current_balance' => 0
                ]);
            }

            $gainAccount = Account::where('company_id', $companyId)
                ->where(function($q) {
                    $q->where('account_code', '5010')->orWhere('account_name', 'LIKE', '%Inventory Adjustment Gain%');
                })->first() ?? Account::create([
                    'company_id' => $companyId,
                    'account_code' => '5010',
                    'account_name' => 'Inventory Adjustment Gain',
                    'account_type' => 'revenue',
                    'account_subtype' => 'other_revenue',
                    'is_active' => true,
                    'is_system_account' => true,
                    'opening_balance' => 0,
                    'current_balance' => 0
                ]);

            $lossAccount = Account::where('company_id', $companyId)
                ->where(function($q) {
                    $q->where('account_code', '5020')->orWhere('account_name', 'LIKE', '%Inventory Loss%')->orWhere('account_name', 'LIKE', '%Shrinkage%');
                })->first() ?? Account::create([
                    'company_id' => $companyId,
                    'account_code' => '5020',
                    'account_name' => 'Inventory Loss / Shrinkage',
                    'account_type' => 'expense',
                    'account_subtype' => 'operating_expense',
                    'is_active' => true,
                    'is_system_account' => true,
                    'opening_balance' => 0,
                    'current_balance' => 0
                ]);

            foreach ($items as $item) {
                $productId = $item['product_id'];
                $variationId = $item['product_variation_id'] ?? null;
                $warehouseId = $item['warehouse_id'];

                $product = Product::where('company_id', $companyId)->find($productId);
                if (!$product) continue;

                $variation = $variationId ? ProductVariation::find($variationId) : null;
                $targetEntity = $variation ? $variation : $product;

                // Audit capture
                $previousAuditData[] = [
                    'product_id' => $product->id,
                    'variation_id' => $variationId,
                    'selling_price' => $targetEntity->selling_price ?? $targetEntity->retail_price ?? 0,
                    'wholesale_price' => $targetEntity->wholesale_price ?? 0,
                    'cost_price' => $targetEntity->cost_price ?? 0,
                    'min_stock_level' => $targetEntity->min_stock_level ?? 0,
                    'stock_quantity' => $targetEntity->stock_quantity ?? 0,
                ];

                $updatedFields = [];

                // 1. Price Updates
                if (isset($item['selling_price']) && $item['selling_price'] !== '' && $item['selling_price'] !== null) {
                    $val = floatval($item['selling_price']);
                    if ($variation) {
                        $variation->update(['retail_price' => $val]);
                    } else {
                        $product->update(['selling_price' => $val]);
                    }
                    $updatedFields['selling_price'] = $val;
                }

                if (isset($item['wholesale_price']) && $item['wholesale_price'] !== '' && $item['wholesale_price'] !== null) {
                    $val = floatval($item['wholesale_price']);
                    if ($variation) {
                        $variation->update(['wholesale_price' => $val]);
                    } else {
                        $product->update(['wholesale_price' => $val]);
                    }
                    $updatedFields['wholesale_price'] = $val;
                }

                // Capture initial cost price before update
                $oldCostUnit = floatval($targetEntity->cost_price ?? $targetEntity->purchase_price ?? $product->cost_price ?? $product->purchase_price ?? 0);
                $rawCostInput = $item['cost_price'] ?? $item['purchase_price'] ?? $item['unit_cost'] ?? null;

                if ($rawCostInput !== null && $rawCostInput !== '') {
                    $newCostUnit = floatval($rawCostInput);
                    if ($variation) {
                        $variation->update(['cost_price' => $newCostUnit]);
                    } else {
                        $product->update(['cost_price' => $newCostUnit]);
                    }
                    $updatedFields['cost_price'] = $newCostUnit;
                } else {
                    $newCostUnit = $oldCostUnit;
                }

                // 2. Alert Limit Update
                if (isset($item['min_stock_level']) && $item['min_stock_level'] !== '' && $item['min_stock_level'] !== null) {
                    $val = (int)$item['min_stock_level'];
                    if ($variation) {
                        $variation->update(['min_stock_level' => $val]);
                    } else {
                        $product->update(['min_stock_level' => $val]);
                    }
                    $updatedFields['min_stock_level'] = $val;
                }

                // 3. Tax Assignment Update
                if (array_key_exists('tax_id', $item) && $item['tax_id'] !== '' && $item['tax_id'] !== null) {
                    $taxVal = $item['tax_id'] ? (int)$item['tax_id'] : null;
                    if ($product->category_id && $taxVal) {
                        \App\Models\Category::where('id', $product->category_id)->update(['tax_id' => $taxVal]);
                    }
                    $updatedFields['tax_id'] = $taxVal;
                }

                // 4. Stock Adjustment Update & Inventory Allocation
                $qtyInput = isset($item['quantity_adjusted']) && $item['quantity_adjusted'] !== '' && $item['quantity_adjusted'] !== null
                    ? floatval($item['quantity_adjusted'])
                    : null;

                // Get current stock record in inventory
                $inv = Inventory::where('company_id', $companyId)
                    ->where('warehouse_id', $warehouseId)
                    ->where('product_id', $productId)
                    ->first();

                $qtyBefore = $inv ? (int)$inv->stock_qty : (int)($targetEntity->stock_quantity ?? 0);

                if ($qtyInput !== null) {
                    $adjType = $item['adjustment_type'] ?? 'increase';
                    if ($adjType === 'increase') {
                        $qtyAfter = $qtyBefore + (int)$qtyInput;
                    } elseif ($adjType === 'decrease') {
                        $qtyAfter = max(0, $qtyBefore - (int)$qtyInput);
                    } else { // recount
                        $qtyAfter = (int)$qtyInput;
                    }
                } else {
                    $qtyAfter = $qtyBefore;
                    $adjType = 'recount';
                }

                // Valuation Impact Calculation (Valuation Matrix Rules: Case A, B, and C)
                // Valuation Impact = New Valuation (New Stock * New Cost) - Old Valuation (Old Stock * Old Cost)
                $oldValuation = round($qtyBefore * $oldCostUnit, 2);
                $newValuation = round($qtyAfter * $newCostUnit, 2);
                $costImpact = round($newValuation - $oldValuation, 2);

                if ($costImpact != 0 || $qtyInput !== null) {
                    if (!$inv) {
                        $inv = Inventory::create([
                            'company_id' => $companyId,
                            'warehouse_id' => $warehouseId,
                            'product_id' => $productId,
                            'stock_qty' => $qtyAfter,
                            'min_stock_level' => $targetEntity->min_stock_level ?? 0,
                        ]);
                    } else {
                        $inv->update(['stock_qty' => $qtyAfter]);
                    }

                    // Recalculate total product stock
                    $totalWhStock = Inventory::where('company_id', $companyId)
                        ->where('product_id', $productId)
                        ->sum('stock_qty');
                    $product->update(['stock_quantity' => $totalWhStock]);

                    if ($variation) {
                        $variation->update(['stock_quantity' => $totalWhStock]);
                    }

                    $totalCostImpact += $costImpact;

                    // Generate unique adjustment number
                    $datePrefix = 'ADJ-MX-' . date('Ymd') . '-';
                    $lastAdj = InventoryAdjustment::where('adjustment_number', 'like', $datePrefix . '%')
                        ->orderBy('adjustment_number', 'desc')
                        ->first();
                    $newNum = $lastAdj ? ((int) substr($lastAdj->adjustment_number, -4)) + 1 : 1;
                    $adjNum = $datePrefix . str_pad($newNum, 4, '0', STR_PAD_LEFT);

                    $adjRecord = InventoryAdjustment::create([
                        'company_id' => $companyId,
                        'adjustment_number' => $adjNum,
                        'product_id' => $productId,
                        'product_variation_id' => $variationId,
                        'warehouse_id' => $warehouseId,
                        'adjustment_type' => $adjType,
                        'quantity_before' => $qtyBefore,
                        'quantity_adjusted' => (int)($qtyInput ?? abs($qtyAfter - $qtyBefore)),
                        'quantity_after' => $qtyAfter,
                        'reason' => $reason,
                        'user_id' => $userId,
                        'adjustment_date' => now(),
                        'cost_impact' => $costImpact,
                        'notes' => $notes,
                    ]);

                    $createdAdjustments[] = $adjRecord;
                }

                $updatedAuditData[] = array_merge([
                    'product_id' => $product->id,
                    'variation_id' => $variationId,
                ], $updatedFields);

                $updatedProductCount++;
            }

            // 5. DOUBLE-ENTRY ACCOUNTING POSTING (COA Ledger Sync)
            if ($totalCostImpact != 0) {
                $absAmount = abs($totalCostImpact);
                $entryNumber = 'JE-ADJ-' . date('YmdHis') . '-' . rand(100, 999);

                $journalEntry = JournalEntry::create([
                    'company_id' => $companyId,
                    'entry_number' => $entryNumber,
                    'entry_date' => now()->toDateString(),
                    'reference' => $createdAdjustments[0]->adjustment_number ?? 'ADJ-MATRIX',
                    'description' => "Inventory Adjustment Ledger Post ({$reason})",
                    'entry_type' => 'adjustment',
                    'status' => 'posted',
                    'total_debit' => $absAmount,
                    'total_credit' => $absAmount,
                    'created_by' => $userId,
                    'posted_by' => $userId,
                    'posted_at' => now(),
                    'source_type' => 'inventory_adjustment',
                    'source_id' => $createdAdjustments[0]->id ?? null,
                ]);

                if ($totalCostImpact > 0) {
                    // Net Gain / Stock Increase
                    // DEBIT: Inventory Asset (1500)
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $assetAccount->id,
                        'description' => 'Inventory Asset Increase via Stock Recount',
                        'debit_amount' => $absAmount,
                        'credit_amount' => 0,
                    ]);
                    // CREDIT: Inventory Gain (5010)
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $gainAccount->id,
                        'description' => 'Inventory Adjustment Gain',
                        'debit_amount' => 0,
                        'credit_amount' => $absAmount,
                    ]);

                    $assetAccount->updateCurrentBalance();
                    $gainAccount->updateCurrentBalance();
                } else {
                    // Net Loss / Stock Decrease
                    // DEBIT: Inventory Shrinkage (5020)
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $lossAccount->id,
                        'description' => 'Inventory Loss / Shrinkage Expense',
                        'debit_amount' => $absAmount,
                        'credit_amount' => 0,
                    ]);
                    // CREDIT: Inventory Asset (1500)
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $assetAccount->id,
                        'description' => 'Inventory Asset Reduction',
                        'debit_amount' => 0,
                        'credit_amount' => $absAmount,
                    ]);

                    $lossAccount->updateCurrentBalance();
                    $assetAccount->updateCurrentBalance();
                }

                // Link journal_entry_id back to created adjustment records
                foreach ($createdAdjustments as $adj) {
                    $adj->update(['journal_entry_id' => $journalEntry->id]);
                }
            }

            // 6. HISTORY AUDIT TRAIL LOGGING
            if (DB::getSchemaBuilder()->hasTable('adjustment_logs')) {
                DB::table('adjustment_logs')->insert([
                    'company_id' => $companyId,
                    'inventory_adjustment_id' => $createdAdjustments[0]->id ?? null,
                    'user_id' => $userId,
                    'action_type' => 'matrix_adjustment',
                    'description' => "Executed Unified Adjustment Matrix for {$updatedProductCount} items.",
                    'previous_data' => json_encode($previousAuditData),
                    'updated_data' => json_encode($updatedAuditData),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => "Successfully processed unified matrix adjustment for {$updatedProductCount} products.",
                'updated_count' => $updatedProductCount,
                'total_cost_impact' => $totalCostImpact,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to process matrix adjustment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing inventory adjustment (with strict upsert guardrail on Journal Entry)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $adjustment = InventoryAdjustment::where('company_id', auth()->user()->current_company_id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'quantity_adjusted' => 'nullable|numeric',
            'adjustment_type' => 'nullable|string|in:increase,decrease,recount',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $companyId = auth()->user()->current_company_id;
            $userId = auth()->id() ?? 1;

            $oldQtyAdjusted = $adjustment->quantity_adjusted;
            $oldCostImpact = floatval($adjustment->cost_impact);

            $product = $adjustment->product;
            $warehouseId = $adjustment->warehouse_id;
            $inv = Inventory::where('company_id', $companyId)
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $product->id)
                ->first();

            $newQtyAdjusted = $request->has('quantity_adjusted') && $request->quantity_adjusted !== null
                ? floatval($request->quantity_adjusted)
                : $adjustment->quantity_adjusted;

            $adjType = $request->input('adjustment_type', $adjustment->adjustment_type);
            $qtyBefore = $adjustment->quantity_before;

            if ($adjType === 'increase') {
                $qtyAfter = $qtyBefore + (int)$newQtyAdjusted;
                $delta = (int)$newQtyAdjusted;
            } elseif ($adjType === 'decrease') {
                $qtyAfter = max(0, $qtyBefore - (int)$newQtyAdjusted);
                $delta = -$newQtyAdjusted;
            } else {
                $qtyAfter = (int)$newQtyAdjusted;
                $delta = $qtyAfter - $qtyBefore;
            }

            if ($inv) {
                $inv->update(['stock_qty' => $qtyAfter]);
            }

            $totalWhStock = Inventory::where('company_id', $companyId)
                ->where('product_id', $product->id)
                ->sum('stock_qty');
            $product->update(['stock_quantity' => $totalWhStock]);

            $unitCost = floatval($product->cost_price ?? 0);
            $newCostImpact = round($delta * $unitCost, 2);

            $previousData = [
                'quantity_adjusted' => $adjustment->quantity_adjusted,
                'quantity_after' => $adjustment->quantity_after,
                'cost_impact' => $adjustment->cost_impact,
                'reason' => $adjustment->reason,
            ];

            $adjustment->update([
                'adjustment_type' => $adjType,
                'quantity_adjusted' => (int)$newQtyAdjusted,
                'quantity_after' => $qtyAfter,
                'cost_impact' => $newCostImpact,
                'reason' => $request->input('reason', $adjustment->reason),
                'notes' => $request->input('notes', $adjustment->notes),
            ]);

            // FINANCIAL ZERO-DUPLICATION GUARDRAIL (UPSERT EXISTING JOURNAL ENTRY)
            $journalEntry = null;
            if ($adjustment->journal_entry_id) {
                $journalEntry = JournalEntry::find($adjustment->journal_entry_id);
            }
            if (!$journalEntry) {
                $journalEntry = JournalEntry::where('company_id', $companyId)
                    ->where('source_type', 'inventory_adjustment')
                    ->where('source_id', $adjustment->id)
                    ->first();
            }

            $assetAccount = Account::where('company_id', $companyId)->where('account_code', '1040')->first();
            if (!$assetAccount) {
                $assetAccount = Account::where('company_id', $companyId)
                    ->where(function($q) {
                        $q->where('account_code', '1500')
                          ->orWhere('account_name', 'LIKE', '%Inventory Asset%')
                          ->orWhere('account_name', 'LIKE', '%Inventory%');
                    })->first();
            }

            $gainAccount = Account::where('company_id', $companyId)
                ->where(function($q) {
                    $q->where('account_code', '5010')->orWhere('account_name', 'LIKE', '%Inventory Adjustment Gain%');
                })->first();

            $lossAccount = Account::where('company_id', $companyId)
                ->where(function($q) {
                    $q->where('account_code', '5020')->orWhere('account_name', 'LIKE', '%Inventory Loss%')->orWhere('account_name', 'LIKE', '%Shrinkage%');
                })->first();

            if ($journalEntry && $assetAccount) {
                // Reverse previous ledger balances
                if ($oldCostImpact > 0 && $gainAccount) {
                    $assetAccount->decrement('current_balance', abs($oldCostImpact));
                    $gainAccount->decrement('current_balance', abs($oldCostImpact));
                } elseif ($oldCostImpact < 0 && $lossAccount) {
                    $lossAccount->decrement('current_balance', abs($oldCostImpact));
                    $assetAccount->increment('current_balance', abs($oldCostImpact));
                }

                // Update existing JournalEntry without creating a new row
                $absNewAmount = abs($newCostImpact);
                $journalEntry->update([
                    'total_debit' => $absNewAmount,
                    'total_credit' => $absNewAmount,
                    'description' => "Updated Inventory Adjustment Ledger Post ({$adjustment->reason})",
                    'posted_at' => now(),
                ]);

                // Delete old lines of existing JournalEntry & recreate under SAME entry ID
                JournalEntryLine::where('journal_entry_id', $journalEntry->id)->delete();

                if ($newCostImpact > 0 && $gainAccount) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $assetAccount->id,
                        'description' => 'Updated Inventory Asset Increase',
                        'debit_amount' => $absNewAmount,
                        'credit_amount' => 0,
                    ]);
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $gainAccount->id,
                        'description' => 'Updated Inventory Adjustment Gain',
                        'debit_amount' => 0,
                        'credit_amount' => $absNewAmount,
                    ]);
                    $assetAccount->increment('current_balance', $absNewAmount);
                    $gainAccount->increment('current_balance', $absNewAmount);
                } elseif ($newCostImpact < 0 && $lossAccount) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $lossAccount->id,
                        'description' => 'Updated Inventory Loss / Shrinkage',
                        'debit_amount' => $absNewAmount,
                        'credit_amount' => 0,
                    ]);
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $assetAccount->id,
                        'description' => 'Updated Inventory Asset Reduction',
                        'debit_amount' => 0,
                        'credit_amount' => $absNewAmount,
                    ]);
                    $lossAccount->increment('current_balance', $absNewAmount);
                    $assetAccount->decrement('current_balance', $absNewAmount);
                }
            }

            // Audit Trail Log
            if (DB::getSchemaBuilder()->hasTable('adjustment_logs')) {
                DB::table('adjustment_logs')->insert([
                    'company_id' => $companyId,
                    'inventory_adjustment_id' => $adjustment->id,
                    'user_id' => $userId,
                    'action_type' => 'edit',
                    'description' => "Updated adjustment #{$adjustment->adjustment_number} from {$oldQtyAdjusted} to {$newQtyAdjusted} units.",
                    'previous_data' => json_encode($previousData),
                    'updated_data' => json_encode([
                        'quantity_adjusted' => $adjustment->quantity_adjusted,
                        'quantity_after' => $adjustment->quantity_after,
                        'cost_impact' => $adjustment->cost_impact,
                        'reason' => $adjustment->reason,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Inventory adjustment updated successfully.',
                'adjustment' => $adjustment
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update adjustment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper to get category descendants
     */
    private function getCategoryDescendants(\App\Models\Category $category): array
    {
        $descendants = [];
        foreach ($category->children as $child) {
            $descendants[] = $child->id;
            $descendants = array_merge($descendants, $this->getCategoryDescendants($child));
        }
        return $descendants;
    }
}
