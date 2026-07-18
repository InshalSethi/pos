<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Services\WarehouseInventoryService;
use App\Services\DoubleEntryAccountingService;
use App\Models\JournalEntry;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:sales.view')->only(['index', 'show', 'getStatusCounts']);
        $this->middleware('permission:sales.create')->only(['store']);
        $this->middleware('permission:sales.edit')->only(['update']);
        $this->middleware('permission:sales.delete')->only(['destroy']);
        $this->middleware('permission:sales.refund')->only(['refund']);
    }

    /**
     * Get the count of sales grouped by status.
     */
    public function getStatusCounts(): JsonResponse
    {
        $all = Sale::count();
        $draft = Sale::where('status', 'draft')->count();
        $paid = Sale::where('status', 'completed')->count();
        $due = Sale::where('status', 'pending')->count();
        $recurring = Sale::where('status', 'recurring')->count();
        $overdue = Sale::where('status', 'pending')->where('due_date', '<', today())->count();

        return response()->json([
            'all' => $all,
            'draft' => $draft,
            'paid' => $paid,
            'due' => $due,
            'recurring' => $recurring,
            'overdue' => $overdue,
        ]);
    }

    /**
     * Get the next available sale/invoice number.
     */
    public function getNextSaleNumber(): JsonResponse
    {
        $saleNumber = 'SALE-' . date('Ymd') . '-' . str_pad(Sale::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        return response()->json([
            'success' => true,
            'sale_number' => $saleNumber
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Sale::with(['customer', 'user', 'saleItems.product']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sale_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        // Legacy support for start_date and end_date
        if ($request->has('start_date')) {
            $query->whereDate('sale_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('sale_date', '<=', $request->end_date);
        }

        // Filter by status (supports array, comma-separated string, and multi-select with overdue)
        if ($request->has('status') && $request->status) {
            $statusInput = $request->status;
            $statuses = is_array($statusInput) ? $statusInput : explode(',', $statusInput);
            
            $query->where(function ($q) use ($statuses) {
                foreach ($statuses as $st) {
                    $st = trim($st);
                    if ($st === 'overdue') {
                        $q->orWhere(function ($sub) {
                            $sub->where('status', 'pending')
                                ->where('due_date', '<', today());
                        });
                    } elseif ($st === 'paid' || $st === 'completed') {
                        $q->orWhere('status', 'completed');
                    } elseif ($st === 'due' || $st === 'pending') {
                        $q->orWhere('status', 'pending');
                    } else {
                        $q->orWhere('status', $st);
                    }
                }
            });
        } elseif ($request->has('overdue') && $request->boolean('overdue')) {
            // Fallback for single overdue check
            $query->where('status', 'pending')->where('due_date', '<', today());
        }

        // Filter by customer
        if ($request->has('customer_id') && $request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by refund status
        if ($request->has('is_refund')) {
            $query->where('is_refund', $request->boolean('is_refund'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'sale_date');
        $sortOrder = $request->get('sort_order', 'desc');

        // Validate sort fields
        $allowedSortFields = ['sale_date', 'sale_number', 'total_amount', 'status', 'created_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('sale_date', 'desc');
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $sales = $query->paginate($perPage);

        return response()->json($sales);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
            'sale_number' => 'nullable|string|max:100',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'category_id' => 'nullable|exists:categories,id',
            'due_date' => 'nullable|date',
            'order_number' => 'nullable|string|max:100',
            'footer' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'attachments' => 'nullable|array',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variation_id' => 'nullable|exists:product_variations,id',
            'items.*.warehouse_id' => 'required|exists:warehouses,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'items.*.tax_id' => 'nullable|exists:taxes,id',
            'items.*.description' => 'nullable|string',
            'grand_discount_amount' => 'nullable|numeric|min:0',
            'grand_tax_rate' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,card,bank_transfer,mobile_payment,mixed',
            'paid_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $companyId = auth()->user()->current_company_id;

            // Resolve selected or default warehouse
            $warehouseId = $request->warehouse_id;
            if (!$warehouseId) {
                if (count($request->items) > 0) {
                    $warehouseId = $request->items[0]['warehouse_id'];
                } else {
                    $warehouse = Warehouse::where('company_id', $companyId)->where('is_default', true)->first()
                        ?? Warehouse::where('company_id', $companyId)->first();
                    
                    if (!$warehouse) {
                        $warehouse = Warehouse::create([
                            'company_id' => $companyId,
                            'name' => 'Default Warehouse',
                            'code' => 'DEFAULT',
                            'is_default' => true,
                            'is_active' => true
                        ]);
                    }
                    $warehouseId = $warehouse->id;
                }
            }

            // Calculate totals and verify stock
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $variationId = $item['product_variation_id'] ?? null;
                $itemWarehouseId = $item['warehouse_id'];

                // Verify stock availability in specific warehouse if tracking is enabled
                if ($product->track_inventory) {
                    $inventory = Inventory::where('warehouse_id', $itemWarehouseId)
                        ->where('product_id', $product->id)
                        ->where('product_variation_id', $variationId)
                        ->first();
                    
                    $availableQty = $inventory ? $inventory->stock_qty : 0;
                    if ($availableQty < $item['quantity']) {
                        return response()->json([
                            'message' => "Insufficient stock for '{$product->name}' in the selected warehouse. Available: {$availableQty}, Requested: {$item['quantity']}."
                        ], 422);
                    }
                }

                // Determine tax rate: manual override or fallback
                $taxPercentage = 0;
                if (isset($item['tax_rate']) && $item['tax_rate'] !== null) {
                    $taxPercentage = (float)$item['tax_rate'];
                } else {
                    $taxIds = [];
                    $fallbackTaxRate = 0;

                    if ($variationId) {
                        $variation = \App\Models\ProductVariation::find($variationId);
                        if ($variation) {
                            $taxIds = $variation->taxes ?? [];
                            $fallbackTaxRate = $variation->tax_rate ?? 0;
                        }
                    } else {
                        $taxIds = $product->taxes ?? [];
                        $fallbackTaxRate = $product->tax_rate ?? 0;
                    }

                    if (!empty($taxIds) && is_array($taxIds)) {
                        $taxPercentage = \App\Models\Tax::whereIn('id', $taxIds)
                            ->where('is_active', true)
                            ->sum('value');
                    } else {
                        $taxPercentage = $fallbackTaxRate;
                    }
                }

                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $itemDiscount = $item['discount_amount'] ?? 0;
                $itemTax = ($itemSubtotal - $itemDiscount) * ($taxPercentage / 100);

                $subtotal += $itemSubtotal;
                $totalDiscount += $itemDiscount;
                $totalTax += $itemTax;
            }

            // Apply grand total discount and tax overrides if provided
            if ($request->has('grand_discount_amount')) {
                $totalDiscount += (float)$request->grand_discount_amount;
            }
            if ($request->has('grand_tax_rate')) {
                $grandTaxPercentage = (float)$request->grand_tax_rate;
                $totalTax += ($subtotal - $totalDiscount) * ($grandTaxPercentage / 100);
            }

            $totalAmount = $subtotal - $totalDiscount + $totalTax;
            $changeAmount = max(0, $request->paid_amount - $totalAmount);

            // Generate sale number
            $saleNumber = $request->sale_number;
            
            // If sale number is provided but already exists, clear it to auto-generate
            if ($saleNumber && Sale::withoutGlobalScopes()->where('sale_number', $saleNumber)->exists()) {
                $saleNumber = null;
            }
            
            if (!$saleNumber) {
                $counter = Sale::withoutGlobalScopes()->whereDate('created_at', today())->count() + 1;
                do {
                    $saleNumber = 'SALE-' . date('Ymd') . '-' . str_pad($counter, 4, '0', STR_PAD_LEFT);
                    $counter++;
                } while (Sale::withoutGlobalScopes()->where('sale_number', $saleNumber)->exists());
            }

            // Create sale
            $sale = Sale::create([
                'company_id' => $companyId,
                'sale_number' => $saleNumber,
                'customer_id' => $request->customer_id,
                'category_id' => $request->category_id,
                'warehouse_id' => $warehouseId,
                'user_id' => auth()->id(),
                'sale_date' => $request->sale_date ?? today()->toDateString(),
                'due_date' => $request->due_date,
                'order_number' => $request->order_number,
                'status' => $request->paid_amount < $totalAmount ? 'pending' : 'completed',
                'color' => $request->color,
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'discount_amount' => $totalDiscount,
                'total_amount' => $totalAmount,
                'paid_amount' => $request->paid_amount,
                'change_amount' => $changeAmount,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'footer' => $request->footer,
                'attachments' => $request->attachments ? json_encode($request->attachments) : null,
            ]);

            // Create sale items and update inventory
            $inventoryService = new WarehouseInventoryService();
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $variationId = $item['product_variation_id'] ?? null;
                $itemWarehouseId = $item['warehouse_id'];

                // Determine tax rate: manual override or fallback
                $taxPercentage = 0;
                if (isset($item['tax_rate']) && $item['tax_rate'] !== null) {
                    $taxPercentage = (float)$item['tax_rate'];
                } else {
                    $taxIds = [];
                    $fallbackTaxRate = 0;

                    if ($variationId) {
                        $variation = \App\Models\ProductVariation::find($variationId);
                        if ($variation) {
                            $taxIds = $variation->taxes ?? [];
                            $fallbackTaxRate = $variation->tax_rate ?? 0;
                        }
                    } else {
                        $taxIds = $product->taxes ?? [];
                        $fallbackTaxRate = $product->tax_rate ?? 0;
                    }

                    if (!empty($taxIds) && is_array($taxIds)) {
                        $taxPercentage = \App\Models\Tax::whereIn('id', $taxIds)
                            ->where('is_active', true)
                            ->sum('value');
                    } else {
                        $taxPercentage = $fallbackTaxRate;
                    }
                }

                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $itemDiscount = $item['discount_amount'] ?? 0;
                $itemTax = ($itemSubtotal - $itemDiscount) * ($taxPercentage / 100);
                $itemTotal = $itemSubtotal - $itemDiscount + $itemTax;

                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'product_variation_id' => $variationId,
                    'warehouse_id' => $itemWarehouseId,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $itemDiscount,
                    'tax_amount' => $itemTax,
                    'total_amount' => $itemTotal,
                    'description' => $item['description'] ?? null,
                    'tax_id' => $item['tax_id'] ?? null,
                ]);

                // Update warehouse inventory
                if ($product->track_inventory) {
                    $inventoryService->adjustStock($itemWarehouseId, $product->id, $variationId, -$item['quantity'], $companyId, 'Invoice', $sale->sale_number);
                    try {
                        $this->verifyStockThresholds($product->id, $variationId);
                    } catch (\Throwable $th) {
                        \Illuminate\Support\Facades\Log::warning('verifyStockThresholds failed in SaleController: ' . $th->getMessage());
                    }
                }
            }

            // Update customer total purchases
            if ($request->customer_id) {
                $customer = Customer::find($request->customer_id);
                if ($customer) {
                    $customer->increment('total_purchases', $totalAmount);
                }
            }

            // Create accounting entries (non-blocking)
            try {
                $accountingService = new DoubleEntryAccountingService();
                $accountingService->createSalesInvoiceEntry($sale);
            } catch (\Throwable $accountingError) {
                \Illuminate\Support\Facades\Log::warning('Accounting entry creation failed (non-blocking): ' . $accountingError->getMessage());
            }

            DB::commit();

            $sale->load(['customer', 'user', 'saleItems.product', 'saleItems.variation', 'saleItems.warehouse']);

            return response()->json([
                'message' => 'Sale completed successfully',
                'sale' => $sale
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            \Illuminate\Support\Facades\Log::error('Failed to process sale: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'Failed to process sale: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale): JsonResponse
    {
        $sale->load(['customer', 'user', 'saleItems.product']);

        return response()->json($sale);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
            'sale_number' => 'nullable|string|max:100',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'category_id' => 'nullable|exists:categories,id',
            'due_date' => 'nullable|date',
            'order_number' => 'nullable|string|max:100',
            'footer' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'attachments' => 'nullable|array',
            'status' => 'nullable|string|in:draft,completed,pending,cancelled',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variation_id' => 'nullable|exists:product_variations,id',
            'items.*.warehouse_id' => 'required|exists:warehouses,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'items.*.tax_id' => 'nullable|exists:taxes,id',
            'items.*.description' => 'nullable|string',
            'grand_discount_amount' => 'nullable|numeric|min:0',
            'grand_tax_rate' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,card,bank_transfer,mobile_payment,mixed',
            'paid_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $companyId = auth()->user()->current_company_id;

            // Resolve selected or default warehouse
            $warehouseId = $request->warehouse_id;
            if (!$warehouseId) {
                if (count($request->items) > 0) {
                    $warehouseId = $request->items[0]['warehouse_id'];
                } else {
                    $warehouseId = $sale->warehouse_id;
                }
            }

            // 1. Inventory Quantity Reconciliation Logic (Stock Level Adjustments)
            // Build maps for old and new items to compare variation-wise stocks
            $oldItems = $sale->saleItems;
            $oldMap = [];
            foreach ($oldItems as $item) {
                $key = "{$item->product_id}_" . ($item->product_variation_id ?? 'null') . "_{$item->warehouse_id}";
                if (!isset($oldMap[$key])) {
                    $oldMap[$key] = 0;
                }
                $oldMap[$key] += $item->quantity;
            }

            $newMap = [];
            foreach ($request->items as $item) {
                $key = "{$item['product_id']}_" . ($item['product_variation_id'] ?? 'null') . "_{$item['warehouse_id']}";
                if (!isset($newMap[$key])) {
                    $newMap[$key] = 0;
                }
                $newMap[$key] += $item['quantity'];
            }

            // Validate stock availability BEFORE applying any adjustments
            $allKeys = array_unique(array_merge(array_keys($oldMap), array_keys($newMap)));
            foreach ($allKeys as $key) {
                $parts = explode('_', $key);
                $productId = (int)$parts[0];
                $variationId = $parts[1] === 'null' ? null : (int)$parts[1];
                $warehouseId = (int)$parts[2];

                $oldQty = $oldMap[$key] ?? 0;
                $newQty = $newMap[$key] ?? 0;
                $diff = $newQty - $oldQty;

                if ($diff > 0) {
                    $product = Product::find($productId);
                    if ($product && $product->track_inventory) {
                        $inventory = Inventory::where('warehouse_id', $warehouseId)
                            ->where('product_id', $productId)
                            ->where('product_variation_id', $variationId)
                            ->first();
                        
                        $availableQty = $inventory ? $inventory->stock_qty : 0;
                        if ($availableQty < $diff) {
                            return response()->json([
                                'message' => "Insufficient stock for '{$product->name}' in the selected warehouse. Available: {$availableQty}, Requested additional: {$diff}."
                            ], 422);
                        }
                    }
                }
            }

            // Perform inventory adjustments based on the difference
            $inventoryService = new WarehouseInventoryService();
            foreach ($allKeys as $key) {
                $parts = explode('_', $key);
                $productId = (int)$parts[0];
                $variationId = $parts[1] === 'null' ? null : (int)$parts[1];
                $warehouseId = (int)$parts[2];

                $oldQty = $oldMap[$key] ?? 0;
                $newQty = $newMap[$key] ?? 0;
                $diff = $newQty - $oldQty;

                if ($diff === 0) {
                    continue;
                }

                $product = Product::find($productId);
                if ($product && $product->track_inventory) {
                    if ($diff > 0) {
                        // New Qty > Old Qty: deduct the exact difference (New - Old)
                        $inventoryService->adjustStock(
                            $warehouseId,
                            $productId,
                            $variationId,
                            -$diff,
                            $companyId,
                            'Invoice Update (Deduction)',
                            $sale->sale_number
                        );
                    } else {
                        // New Qty < Old Qty: automatically add/restock the difference (Old - New)
                        $inventoryService->adjustStock(
                            $warehouseId,
                            $productId,
                            $variationId,
                            abs($diff),
                            $companyId,
                            'Invoice Update (Restock)',
                            $sale->sale_number
                        );
                    }

                    try {
                        $this->verifyStockThresholds($productId, $variationId);
                    } catch (\Throwable $th) {
                        \Illuminate\Support\Facades\Log::warning('verifyStockThresholds failed in SaleController@update: ' . $th->getMessage());
                    }
                }
            }

            // 2. Decrement original purchase amount from old customer's total (Wipe out old financial counter)
            if ($sale->customer_id) {
                $oldCustomer = Customer::find($sale->customer_id);
                if ($oldCustomer) {
                    $oldCustomer->decrement('total_purchases', $sale->total_amount);
                }
            }

            // 3. Delete old sale items
            $sale->saleItems()->delete();

            // 4. Calculate new totals and recreate Sale Items based on the edited state
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $variationId = $item['product_variation_id'] ?? null;
                $itemWarehouseId = $item['warehouse_id'];

                // Determine tax rate
                $taxPercentage = 0;
                if (isset($item['tax_rate']) && $item['tax_rate'] !== null) {
                    $taxPercentage = (float)$item['tax_rate'];
                } else {
                    $taxIds = [];
                    $fallbackTaxRate = 0;

                    if ($variationId) {
                        $variation = \App\Models\ProductVariation::find($variationId);
                        if ($variation) {
                            $taxIds = $variation->taxes ?? [];
                            $fallbackTaxRate = $variation->tax_rate ?? 0;
                        }
                    } else {
                        $taxIds = $product->taxes ?? [];
                        $fallbackTaxRate = $product->tax_rate ?? 0;
                    }

                    if (!empty($taxIds) && is_array($taxIds)) {
                        $taxPercentage = \App\Models\Tax::whereIn('id', $taxIds)
                            ->where('is_active', true)
                            ->sum('value');
                    } else {
                        $taxPercentage = $fallbackTaxRate;
                    }
                }

                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $itemDiscount = $item['discount_amount'] ?? 0;
                $itemTax = ($itemSubtotal - $itemDiscount) * ($taxPercentage / 100);
                $itemTotal = $itemSubtotal - $itemDiscount + $itemTax;

                $subtotal += $itemSubtotal;
                $totalDiscount += $itemDiscount;
                $totalTax += $itemTax;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'product_variation_id' => $variationId,
                    'warehouse_id' => $itemWarehouseId,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $itemDiscount,
                    'tax_amount' => $itemTax,
                    'total_amount' => $itemTotal,
                    'description' => $item['description'] ?? null,
                    'tax_id' => $item['tax_id'] ?? null,
                ]);
            }

            // Apply grand total discount and tax overrides
            if ($request->has('grand_discount_amount')) {
                $totalDiscount += (float)$request->grand_discount_amount;
            }
            if ($request->has('grand_tax_rate')) {
                $grandTaxPercentage = (float)$request->grand_tax_rate;
                $totalTax += ($subtotal - $totalDiscount) * ($grandTaxPercentage / 100);
            }

            $totalAmount = $subtotal - $totalDiscount + $totalTax;
            $changeAmount = max(0, $request->paid_amount - $totalAmount);

            // Sale number stays the same, or updates if a unique one is provided
            $saleNumber = $request->sale_number ?? $sale->sale_number;
            if ($saleNumber !== $sale->sale_number && Sale::withoutGlobalScopes()->where('sale_number', $saleNumber)->exists()) {
                return response()->json([
                    'message' => 'The invoice number already exists.'
                ], 422);
            }

            // Update Sale details with new computed financial values
            $sale->update([
                'sale_number' => $saleNumber,
                'customer_id' => $request->customer_id,
                'category_id' => $request->category_id,
                'warehouse_id' => $warehouseId,
                'sale_date' => $request->sale_date ?? $sale->sale_date,
                'due_date' => $request->due_date,
                'order_number' => $request->order_number,
                'color' => $request->color,
                'status' => $request->paid_amount < $totalAmount ? 'pending' : (($request->status ?? $sale->status) === 'pending' ? 'completed' : ($request->status ?? $sale->status)),
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'discount_amount' => $totalDiscount,
                'total_amount' => $totalAmount,
                'paid_amount' => $request->paid_amount,
                'change_amount' => $changeAmount,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'footer' => $request->footer,
                'attachments' => $request->attachments ? json_encode($request->attachments) : null,
            ]);

            // Increment customer total purchases with the new updated total (financial reporting ledger update)
            if ($request->customer_id) {
                $customer = Customer::find($request->customer_id);
                if ($customer) {
                    $customer->increment('total_purchases', $totalAmount);
                }
            }

            // Re-generate double-entry accounting entries (update the journal ledger)
            try {
                // Delete old journal entries
                $existingEntries = JournalEntry::where('source_type', 'sale')->where('source_id', $sale->id)->get();
                $affectedAccountIds = [];
                foreach ($existingEntries as $entry) {
                    $affectedAccountIds = array_merge($affectedAccountIds, $entry->journalEntryLines->pluck('account_id')->toArray());
                    $entry->journalEntryLines()->delete();
                    $entry->delete();
                }
                foreach (array_unique($affectedAccountIds) as $accountId) {
                    $account = Account::find($accountId);
                    if ($account) {
                        $account->updateCurrentBalance();
                    }
                }

                // Create new journal entry
                $accountingService = new DoubleEntryAccountingService();
                $accountingService->createSalesInvoiceEntry($sale);
            } catch (\Throwable $accountingError) {
                \Illuminate\Support\Facades\Log::warning('Accounting entry update failed (non-blocking): ' . $accountingError->getMessage());
            }

            DB::commit();

            $sale->load(['customer', 'user', 'saleItems.product', 'saleItems.variation', 'saleItems.warehouse']);

            return response()->json([
                'message' => 'Invoice updated successfully',
                'sale' => $sale
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();

            \Illuminate\Support\Facades\Log::error('Failed to update sale: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'Failed to update sale: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process a refund
     */
    public function refund(Request $request, Sale $sale): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($sale->status !== 'completed') {
            return response()->json([
                'message' => 'Can only refund completed sales'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $refundAmount = 0;
            $refundItems = [];

            foreach ($request->items as $item) {
                $saleItem = SaleItem::find($item['sale_item_id']);

                if ($saleItem->sale_id !== $sale->id) {
                    throw new \Exception('Sale item does not belong to this sale');
                }

                if ($item['quantity'] > $saleItem->quantity) {
                    throw new \Exception('Refund quantity cannot exceed sold quantity');
                }

                $refundItemAmount = ($saleItem->total_amount / $saleItem->quantity) * $item['quantity'];
                $refundAmount += $refundItemAmount;

                $refundItems[] = [
                    'product_id' => $saleItem->product_id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $saleItem->unit_price,
                    'discount_amount' => ($saleItem->discount_amount / $saleItem->quantity) * $item['quantity'],
                    'tax_amount' => ($saleItem->tax_amount / $saleItem->quantity) * $item['quantity'],
                    'total_amount' => $refundItemAmount,
                ];

                // Update inventory
                $product = Product::find($saleItem->product_id);
                if ($product->track_inventory) {
                    $product->increment('stock_quantity', $item['quantity']);
                }
            }

            // Create refund sale
            $refundSaleNumber = 'REFUND-' . date('Ymd') . '-' . str_pad(Sale::whereDate('created_at', today())->where('is_refund', true)->count() + 1, 4, '0', STR_PAD_LEFT);

            $refundSale = Sale::create([
                'sale_number' => $refundSaleNumber,
                'customer_id' => $sale->customer_id,
                'user_id' => Auth::id(),
                'sale_date' => now(),
                'status' => 'completed',
                'subtotal' => -$refundAmount,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => -$refundAmount,
                'paid_amount' => -$refundAmount,
                'change_amount' => 0,
                'payment_method' => $sale->payment_method,
                'notes' => $request->reason,
                'is_refund' => true,
                'original_sale_id' => $sale->id,
            ]);

            // Create refund sale items
            foreach ($refundItems as $refundItem) {
                SaleItem::create(array_merge($refundItem, [
                    'sale_id' => $refundSale->id,
                    'quantity' => -$refundItem['quantity'], // Negative quantity for refunds
                    'total_amount' => -$refundItem['total_amount'], // Negative amount for refunds
                ]));
            }

            DB::commit();

            $refundSale->load(['customer', 'user', 'saleItems.product', 'originalSale']);

            return response()->json([
                'message' => 'Refund processed successfully',
                'refund_sale' => $refundSale
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to process refund',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process a sales return
     */
    public function processReturn(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'original_sale_id' => 'required|exists:sales,id',
            'return_date' => 'required|date',
            'return_reason' => 'required|string',
            'refund_method' => 'required|in:cash,card,store_credit,exchange',
            'return_items' => 'required|array|min:1',
            'return_items.*.original_item_id' => 'required|exists:sale_items,id',
            'return_items.*.quantity' => 'required|integer|min:1',
            'return_items.*.return_amount' => 'required|numeric|min:0',
            'return_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $originalSale = Sale::with('saleItems.product')->find($request->original_sale_id);

        if ($originalSale->status !== 'completed') {
            return response()->json([
                'message' => 'Can only process returns for completed sales'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $totalReturnAmount = 0;
            $returnItems = [];

            // Validate and prepare return items
            foreach ($request->return_items as $returnItem) {
                $originalItem = SaleItem::find($returnItem['original_item_id']);

                if ($originalItem->sale_id !== $originalSale->id) {
                    throw new \Exception('Sale item does not belong to the original sale');
                }

                if ($returnItem['quantity'] > $originalItem->quantity) {
                    throw new \Exception('Return quantity cannot exceed original quantity');
                }

                $totalReturnAmount += $returnItem['return_amount'];

                $returnItems[] = [
                    'product_id' => $originalItem->product_id,
                    'quantity' => -$returnItem['quantity'], // Negative for returns
                    'unit_price' => $originalItem->unit_price,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'total_amount' => -$returnItem['return_amount'], // Negative for returns
                ];

                // Update inventory
                $product = Product::find($originalItem->product_id);
                if ($product && $product->track_inventory) {
                    $product->increment('stock_quantity', $returnItem['quantity']);
                }
            }

            // Create return sale
            $returnSaleNumber = 'RETURN-' . date('Ymd') . '-' . str_pad(
                Sale::whereDate('created_at', today())->where('is_refund', true)->count() + 1,
                4,
                '0',
                STR_PAD_LEFT
            );

            $returnSale = Sale::create([
                'sale_number' => $returnSaleNumber,
                'customer_id' => $originalSale->customer_id,
                'user_id' => Auth::id(),
                'sale_date' => $request->return_date,
                'status' => 'completed',
                'subtotal' => -$totalReturnAmount,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => -$totalReturnAmount,
                'paid_amount' => -$totalReturnAmount,
                'change_amount' => 0,
                'payment_method' => $request->refund_method,
                'notes' => $request->return_notes,
                'is_refund' => true,
                'original_sale_id' => $originalSale->id,
            ]);

            // Create return sale items
            foreach ($returnItems as $returnItem) {
                SaleItem::create(array_merge($returnItem, [
                    'sale_id' => $returnSale->id,
                ]));
            }

            // Update customer total purchases
            if ($originalSale->customer_id) {
                $customer = Customer::find($originalSale->customer_id);
                $customer->decrement('total_purchases', $totalReturnAmount);
            }

            // Create accounting entries for return
            $accountingService = new DoubleEntryAccountingService();
            $accountingService->createSalesReturnEntry($returnSale);

            DB::commit();

            $returnSale->load(['customer', 'user', 'saleItems.product', 'originalSale']);

            return response()->json([
                'message' => 'Return processed successfully',
                'return_sale' => $returnSale
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to process return',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sales statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', today()->toDateString());
        $endDate = $request->get('end_date', today()->toDateString());

        $stats = [
            'total_sales' => Sale::whereBetween('sale_date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->where('is_refund', false)
                ->sum('total_amount'),
            'total_transactions' => Sale::whereBetween('sale_date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->where('is_refund', false)
                ->count(),
            'total_refunds' => Sale::whereBetween('sale_date', [$startDate, $endDate])
                ->where('is_refund', true)
                ->sum('total_amount'),
            'average_sale' => Sale::whereBetween('sale_date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->where('is_refund', false)
                ->avg('total_amount'),
        ];

        return response()->json($stats);
    }

    /**
     * Invoked immediately whenever items are checked out via POS or processed through stock adjustments.
     */
    public function verifyStockThresholds($productId, $variationId = null)
    {
        $product = \Illuminate\Support\Facades\DB::table('products')->where('id', $productId)->first();

        if (!$product)
            return;

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
            if (!$variant)
                return;
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
                        productName: $product->name,
                        currentStock: $currentStock,
                        minAlert: $minAlertLimit,
                        variantLabel: $variant->variation_name_string ?? '',
                        productId: $product->id,
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
     * Retrieve all sellable products and variations in a flat list with multi-warehouse stocks.
     */
    public function productsWithStock(Request $request): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        // Fetch warehouses
        $warehouses = Warehouse::where('company_id', $companyId)->where('is_active', true)->get();

        // Fetch active taxes
        $taxes = \App\Models\Tax::where('company_id', $companyId)->where('is_active', true)->get();

        // Fetch products
        $products = Product::where('company_id', $companyId)
            ->where('status', '!=', 'draft')
            ->where('is_active', true)
            ->with(['category', 'unit', 'variations'])
            ->get();

        // Fetch all inventory records for this company
        $inventories = \App\Models\Inventory::where('company_id', $companyId)->get();

        // Group inventory by product_id and product_variation_id for fast lookup
        $stockMap = [];
        foreach ($inventories as $inv) {
            $key = $inv->product_id . '-' . ($inv->product_variation_id ?? 'null');
            if (!isset($stockMap[$key])) {
                $stockMap[$key] = [];
            }
            $stockMap[$key][$inv->warehouse_id] = $inv->stock_qty;
        }

        $flatItems = [];

        foreach ($products as $product) {
            $productTaxes = $product->taxes ?? [];
            
            // Calculate combined tax rate from product taxes array, fallback to tax_rate
            $productTaxPercentage = 0;
            if (!empty($productTaxes) && is_array($productTaxes)) {
                $productTaxPercentage = $taxes->whereIn('id', $productTaxes)->sum('value');
            } else {
                $productTaxPercentage = $product->tax_rate ?? 0;
            }

            if ($product->has_variations && count($product->variations) > 0) {
                foreach ($product->variations as $variation) {
                    $varKey = $product->id . '-' . $variation->id;
                    
                    // Stock per warehouse
                    $warehouseStocks = [];
                    $totalStock = 0;
                    foreach ($warehouses as $wh) {
                        $qty = $stockMap[$varKey][$wh->id] ?? 0;
                        $warehouseStocks[$wh->id] = $qty;
                        $totalStock += $qty;
                    }

                    // Variation taxes mapping
                    $varTaxes = $variation->taxes ?? [];
                    $varTaxPercentage = 0;
                    if (!empty($varTaxes) && is_array($varTaxes)) {
                        $varTaxPercentage = $taxes->whereIn('id', $varTaxes)->sum('value');
                    } else {
                        $varTaxPercentage = $variation->tax_rate ?? $productTaxPercentage;
                    }

                    $flatItems[] = [
                        'key' => 'var-' . $variation->id,
                        'product_id' => $product->id,
                        'product_variation_id' => $variation->id,
                        'name' => $product->name . ' (' . $variation->variation_name_string . ')',
                        'parent_name' => $product->name,
                        'variant_name' => $variation->variation_name_string,
                        'sku' => $variation->sku,
                        'barcode' => $variation->barcode,
                        'image' => $product->image ?? '/images/product-placeholder.png',
                        'price' => (float)($variation->retail_price ?? $variation->selling_price ?? $product->selling_price ?? 0),
                        'wholesale_price' => (float)($variation->wholesale_price ?? $product->wholesale_price ?? 0),
                        'cost_price' => (float)($variation->cost_price ?? $product->cost_price ?? 0),
                        'tax_rate' => (float)$varTaxPercentage,
                        'tax_ids' => $varTaxes,
                        'warehouse_stocks' => $warehouseStocks,
                        'total_stock' => $totalStock,
                        'track_inventory' => (bool)$product->track_inventory,
                        'unit' => $product->unit?->short_name ?? $product->unit_of_measure ?? 'pcs',
                        'category' => $product->category?->name ?? 'Uncategorized',
                        'category_id' => $product->category_id,
                    ];
                }
            } else {
                $prodKey = $product->id . '-null';
                
                // Stock per warehouse
                $warehouseStocks = [];
                $totalStock = 0;
                foreach ($warehouses as $wh) {
                    $qty = $stockMap[$prodKey][$wh->id] ?? 0;
                    $warehouseStocks[$wh->id] = $qty;
                    $totalStock += $qty;
                }

                $flatItems[] = [
                    'key' => 'prod-' . $product->id,
                    'product_id' => $product->id,
                    'product_variation_id' => null,
                    'name' => $product->name,
                    'parent_name' => $product->name,
                    'variant_name' => null,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'image' => $product->image ?? '/images/product-placeholder.png',
                    'price' => (float)($product->selling_price ?? 0),
                    'wholesale_price' => (float)($product->wholesale_price ?? 0),
                    'cost_price' => (float)($product->cost_price ?? 0),
                    'tax_rate' => (float)$productTaxPercentage,
                    'tax_ids' => $productTaxes,
                    'warehouse_stocks' => $warehouseStocks,
                    'total_stock' => $totalStock,
                    'track_inventory' => (bool)$product->track_inventory,
                    'unit' => $product->unit?->short_name ?? $product->unit_of_measure ?? 'pcs',
                    'category' => $product->category?->name ?? 'Uncategorized',
                    'category_id' => $product->category_id,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'items' => $flatItems,
            'warehouses' => $warehouses,
            'taxes' => $taxes,
        ]);
    }
}
