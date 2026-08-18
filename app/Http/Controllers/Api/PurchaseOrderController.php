<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Payment;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Warehouse;
use App\Services\DoubleEntryAccountingService;
use App\Services\WarehouseInventoryService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:purchases.view')->only(['index', 'show', 'getStatusCounts', 'getNextPurchaseOrderNumber']);
        $this->middleware('permission:purchases.create')->only(['store']);
        $this->middleware('permission:purchases.edit')->only(['update', 'receive']);
        $this->middleware('permission:purchases.delete')->only(['destroy', 'void']);
        $this->middleware('permission:purchases.approve')->only(['approve']);
    }

    /**
     * Get next sequential Purchase Order number.
     */
    public function getNextPurchaseOrderNumber(): JsonResponse
    {
        try {
            $companyId = auth()->user()?->current_company_id ?? 1;
            $prefix = 'BILL-';

            $lastOrder = PurchaseOrder::where('company_id', $companyId)
                ->where('po_number', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->first();

            $nextNum = 1;
            if ($lastOrder && preg_match('/' . preg_quote($prefix, '/') . '(\d+)/', $lastOrder->po_number, $matches)) {
                $nextNum = (int)$matches[1] + 1;
            } else {
                $count = PurchaseOrder::where('company_id', $companyId)->count();
                $nextNum = $count + 1;
            }

            $poNumber = $prefix . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

            return response()->json([
                'success' => true,
                'po_number' => $poNumber,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'po_number' => 'BILL-' . str_pad(time() % 100000, 5, '0', STR_PAD_LEFT),
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get the count of purchase orders grouped by status.
     */
    public function getStatusCounts(): JsonResponse
    {
        try {
            $hasDueCol = \Schema::hasColumn('purchase_orders', 'due_amount');
            $hasPaidCol = \Schema::hasColumn('purchase_orders', 'amount_paid');

            $all = PurchaseOrder::count();
            $draft = PurchaseOrder::where('status', 'draft')->count();
            $approved = PurchaseOrder::whereIn('status', ['approved', 'confirmed', 'sent'])->count();
            
            $paid = PurchaseOrder::where(function ($q) use ($hasDueCol) {
                $q->where('status', 'received');
                if ($hasDueCol) {
                    $q->orWhere(function ($sub) {
                        $sub->where('due_amount', '<=', 0)->where('total_amount', '>', 0);
                    });
                }
            })->count();

            $partial = PurchaseOrder::where(function ($q) use ($hasDueCol, $hasPaidCol) {
                $q->where('status', 'partially_received');
                if ($hasDueCol && $hasPaidCol) {
                    $q->orWhere(function ($sub) {
                        $sub->where('amount_paid', '>', 0)->where('due_amount', '>', 0);
                    });
                }
            })->count();

            $overdue = PurchaseOrder::whereNotIn('status', ['received', 'cancelled', 'void'])
                ->where('expected_delivery_date', '<', today())->count();
            $void = PurchaseOrder::whereIn('status', ['cancelled', 'void', 'voided'])->count();

            return response()->json([
                'all' => $all,
                'draft' => $draft,
                'approved' => $approved,
                'paid' => $paid,
                'partial' => $partial,
                'overdue' => $overdue,
                'void' => $void,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'all' => 0,
                'draft' => 0,
                'approved' => 0,
                'paid' => 0,
                'partial' => 0,
                'overdue' => 0,
                'void' => 0,
            ]);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $withRelations = ['supplier', 'user', 'purchaseOrderItems.product', 'purchaseOrderItems.warehouse'];
        if (\Schema::hasColumn('purchase_orders', 'warehouse_id')) {
            $withRelations[] = 'warehouse';
        }
        if (\Schema::hasColumn('purchase_orders', 'counter_id')) {
            $withRelations[] = 'counter';
        }

        $query = PurchaseOrder::with($withRelations)->withSum('returns', 'total_amount');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Specific PO Number search
        if ($request->filled('po_number')) {
            $query->where('po_number', 'like', '%' . $request->input('po_number') . '%');
        }

        // Filter by status (supports array, comma-separated, or scalar)
        if ($request->filled('status')) {
            $statusInput = $request->status;
            $statuses = is_array($statusInput) ? $statusInput : explode(',', $statusInput);

            $query->where(function ($q) use ($statuses) {
                foreach ($statuses as $st) {
                    $st = trim($st);
                    if ($st === 'overdue') {
                        $q->orWhere(function ($sub) {
                            $sub->whereNotIn('status', ['received', 'cancelled', 'void'])
                                ->where('expected_delivery_date', '<', today());
                        });
                    } elseif ($st === 'paid' || $st === 'completed') {
                        $q->orWhere('status', 'received')
                          ->orWhere(function ($sub) {
                              $sub->where('due_amount', '<=', 0)->where('total_amount', '>', 0);
                          });
                    } elseif ($st === 'partial' || $st === 'partially_paid' || $st === 'partially_received') {
                        $q->orWhere('status', 'partially_received')
                          ->orWhere(function ($sub) {
                              $sub->where('amount_paid', '>', 0)->where('due_amount', '>', 0);
                          });
                    } elseif ($st === 'approved' || $st === 'confirmed') {
                        $q->orWhereIn('status', ['approved', 'confirmed', 'sent']);
                    } elseif ($st === 'void' || $st === 'cancelled') {
                        $q->orWhereIn('status', ['void', 'voided', 'cancelled']);
                    } else {
                        $q->orWhere('status', $st);
                    }
                }
            });
        }

        // Filter by supplier (supports array, comma-separated, or scalar)
        if ($request->filled('supplier_id') || $request->filled('supplier_ids')) {
            $rawSupp = $request->input('supplier_ids') ?? $request->input('supplier_id');
            $suppIds = is_array($rawSupp) ? $rawSupp : array_filter(explode(',', $rawSupp));
            if (!empty($suppIds)) {
                $query->whereIn('supplier_id', $suppIds);
            }
        }

        // Filter by warehouse (supports array, comma-separated, or scalar)
        if ($request->filled('warehouse_id') || $request->filled('warehouse_ids')) {
            $rawWh = $request->input('warehouse_ids') ?? $request->input('warehouse_id');
            $whIds = is_array($rawWh) ? $rawWh : array_filter(explode(',', $rawWh));
            if (!empty($whIds)) {
                if (\Schema::hasColumn('purchase_orders', 'warehouse_id')) {
                    $query->whereIn('warehouse_id', $whIds);
                }
            }
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->whereHas('purchaseOrderItems', function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }

        // Legacy support for start_date and end_date
        if ($request->filled('start_date')) {
            $query->whereDate('order_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('order_date', '<=', $request->end_date);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'order_date');
        $sortOrder = $request->input('sort_order', 'desc');

        // Validate sort fields
        $allowedSortFields = ['order_date', 'po_number', 'total_amount', 'due_amount', 'status', 'expected_delivery_date', 'created_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('order_date', 'desc');
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        $purchaseOrders = $query->paginate($perPage);

        return response()->json($purchaseOrders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'expected_delivery_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
            'notes' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'amount_paid' => 'nullable|numeric|min:0',
            'use_advance_balance' => 'nullable|boolean',
            'advance_applied' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate payment balance availability against bank accounts / cash vault
        $balanceErrorResponse = $this->validatePaymentBalances($request);
        if ($balanceErrorResponse) {
            return $balanceErrorResponse;
        }

        try {
            DB::beginTransaction();

            // Calculate totals
            $subtotal = 0;
            $taxAmount = 0;
            $shippingCost = $request->get('shipping_cost', 0);

            foreach ($request->items as $item) {
                $itemTotal = $item['quantity_ordered'] * $item['unit_cost'];
                $subtotal += $itemTotal;
            }

            $totalAmount = $subtotal + $taxAmount + $shippingCost;

            // Generate or validate custom PO number
            $providedPoNumber = trim($request->po_number ?? '');
            if ($providedPoNumber !== '') {
                $user = auth()->user();
                $hasPermission = $user->hasRole(['admin', 'owner', 'Admin', 'Owner']) || $user->can('edit_po_number') || $user->can('purchases.edit_po_number');
                if (!$hasPermission) {
                    return response()->json([
                        'message' => 'Permission denied: You do not have permission to manually edit PO numbers.',
                        'errors' => ['po_number' => ['You do not have permission to manually edit PO numbers.']]
                    ], 403);
                }

                if (PurchaseOrder::where('po_number', $providedPoNumber)->exists()) {
                    return response()->json([
                        'message' => "The PO number '{$providedPoNumber}' is already in use.",
                        'errors' => ['po_number' => ["The PO number '{$providedPoNumber}' is already in use."]]
                    ], 422);
                }
                $poNumber = $providedPoNumber;
            } else {
                $lastOrder = PurchaseOrder::orderBy('id', 'desc')->first();
                $nextNumber = 1;
                if ($lastOrder) {
                    if (preg_match('/BIll-(\d+)/i', $lastOrder->po_number, $matches)) {
                        $nextNumber = (int)$matches[1] + 1;
                    } else {
                        $nextNumber = PurchaseOrder::count() + 1;
                    }
                }
                $poNumber = 'BIll-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            }

            $requestedPaid = (float) $request->get('amount_paid', 0);
            $grandTotal = $totalAmount;

            // --- ADVANCE BALANCE APPLICATION ---
            $advanceApplied = 0;
            $supplier = Supplier::findOrFail($request->supplier_id);
            if ($supplier && $request->boolean('use_advance_balance') && (float) $supplier->advance_balance > 0 && $grandTotal > 0) {
                $requestedAdvance = (float) ($request->advance_applied ?? $supplier->advance_balance);
                $advanceApplied = min($requestedAdvance, (float) $supplier->advance_balance, $grandTotal);
            }

            // Initially, only advance is considered paid on the PO record.
            // The actual payments will be processed and added by PaymentService.
            $initialAmountPaid = $advanceApplied;
            $initialDueAmount = max(0, $grandTotal - $initialAmountPaid);

            // Create purchase order with status received
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $supplier->id,
                'is_walkin_supplier' => false,
                'supplier_name' => $supplier->name,
                'supplier_phone' => $supplier->phone,
                'supplier_email' => $supplier->email,
                'user_id' => auth()->id(),
                'order_date' => now(),
                'expected_delivery_date' => $request->expected_delivery_date,
                'status' => 'received',
                'actual_delivery_date' => now(),
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'grand_total' => $grandTotal,
                'amount_paid' => $initialAmountPaid,
                'due_amount' => $initialDueAmount,
                'notes' => $request->notes,
                'terms_and_conditions' => $request->terms_and_conditions,
            ]);

            $companyId = auth()->user()->current_company_id ?? 1;
            $warehouse = Warehouse::where('company_id', $companyId)->where('id', $request->warehouse_id)->first()
                ?? Warehouse::where('company_id', $companyId)->first();
            $warehouseId = $warehouse?->id;

            $rawWhIds = $request->warehouse_ids ?: ($warehouseId ? [$warehouseId] : []);
            $warehouseIds = array_map('intval', (array) $rawWhIds);

            $purchaseOrder->update([
                'warehouse_id' => $warehouseId,
                'warehouse_ids' => $warehouseIds,
            ]);

            $inventoryService = new WarehouseInventoryService();

            // Create purchase order items and auto-stock inventory
            foreach ($request->items as $item) {
                $totalCost = $item['quantity_ordered'] * $item['unit_cost'];
                $qtyOrdered = (int) $item['quantity_ordered'];

                $rawAllocations = $item['allocations'] ?? $item['warehouse_allocations'] ?? null;
                $allocations = [];
                if (is_array($rawAllocations) && !empty($rawAllocations)) {
                    foreach ($rawAllocations as $alloc) {
                        $allocWhId = isset($alloc['warehouse_id']) ? (int) $alloc['warehouse_id'] : $warehouseId;
                        $validWh = Warehouse::where('company_id', $companyId)->where('id', $allocWhId)->first();
                        $allocations[] = [
                            'warehouse_id' => $validWh ? $validWh->id : $warehouseId,
                            'quantity' => (int) ($alloc['quantity'] ?? $qtyOrdered)
                        ];
                    }
                } else {
                    $allocations = [
                        ['warehouse_id' => $warehouseId, 'quantity' => $qtyOrdered]
                    ];
                }

                $itemWarehouseId = $allocations[0]['warehouse_id'] ?? $warehouseId;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'product_variation_id' => $item['product_variation_id'] ?? null,
                    'warehouse_id' => $itemWarehouseId,
                    'warehouse_allocations' => $allocations,
                    'quantity_ordered' => $qtyOrdered,
                    'quantity_received' => $qtyOrdered,
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $totalCost,
                    'notes' => $item['notes'] ?? null,
                ]);

                // Auto-stock inventory & record purchase price history
                $product = Product::find($item['product_id']);
                if ($product) {
                    $oldPrice = $product->purchase_price ?? $product->cost_price ?? 0;
                    \App\Models\ProductPriceHistory::create([
                        'company_id' => $companyId,
                        'product_id' => $product->id,
                        'source_type' => 'purchase_order',
                        'source_id' => $purchaseOrder->id,
                        'purchase_price' => $item['unit_cost'],
                        'old_purchase_price' => $oldPrice,
                    ]);

                    $prodUpdates = [];
                    if (isset($item['sale_price']) && floatval($item['sale_price']) > 0) {
                        $prodUpdates['selling_price'] = (float) $item['sale_price'];
                    } elseif (isset($item['selling_price']) && floatval($item['selling_price']) > 0) {
                        $prodUpdates['selling_price'] = (float) $item['selling_price'];
                    }
                    if (!empty($prodUpdates)) {
                        $product->update($prodUpdates);
                    }

                    if (!empty($item['product_variation_id'])) {
                        $var = \App\Models\ProductVariation::find($item['product_variation_id']);
                        if ($var && isset($prodUpdates['selling_price'])) {
                            $var->update([
                                'retail_price' => $prodUpdates['selling_price'],
                                'sale_price' => $prodUpdates['selling_price'],
                            ]);
                        }
                    }

                    if ($product->track_inventory) {
                        // Calculate Weighted Average Cost (WAC) before adjusting stock
                        $currentStock = (float) $product->stock_quantity;
                        $currentCost = (float) $product->cost_price;
                        $poUnitCost = (float) $item['unit_cost'];

                        $newTotalStock = $currentStock + $qtyOrdered;
                        if ($newTotalStock > 0) {
                            $wac = (($currentStock * $currentCost) + ($qtyOrdered * $poUnitCost)) / $newTotalStock;
                            $product->update(['cost_price' => round($wac, 2)]);
                        }

                        foreach ($allocations as $alloc) {
                            $allocWhId = $alloc['warehouse_id'] ?? $warehouseId;
                            $allocQty = (int) ($alloc['quantity'] ?? 0);
                            if ($allocQty > 0) {
                                $inventoryService->adjustStock(
                                    $allocWhId,
                                    $product->id,
                                    $item['product_variation_id'] ?? null,
                                    $allocQty,
                                    $companyId,
                                    'Bill',
                                    $purchaseOrder->po_number
                                );
                            }
                        }
                    }
                }
            }

            // Create double-entry accounting entries (Entry #1: Merchandise Inventory Asset & Accounts Payable)
            $accountingService = new DoubleEntryAccountingService();
            $accountingService->createPurchaseInvoiceEntry($purchaseOrder);

            // Process upfront payments (Entry #2: Accounts Payable Debit & Cash/Bank Credit + BankTransaction Subledger)
            $this->processPurchaseOrderPayments($purchaseOrder, $request, $supplier);

            // --- DEBIT ADVANCE if advance was applied ---
            if ($advanceApplied > 0 && $supplier) {
                $supplier->debitAdvance($advanceApplied);
                $accountingService->createVendorAdvanceApplicationEntry($purchaseOrder, $advanceApplied);
            }

            DB::commit();

            $purchaseOrder->load(['supplier', 'user', 'purchaseOrderItems.product']);

            return response()->json([
                'message' => 'Purchase order created successfully and items added to inventory',
                'purchase_order' => $purchaseOrder
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Failed to create purchase order: " . $e->getMessage() . " \n " . $e->getTraceAsString());

            return response()->json([
                'message' => 'Failed to create purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $purchaseOrder->load(['supplier', 'user', 'purchaseOrderItems.product', 'payments.bankAccount']);
        return response()->json($purchaseOrder);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'expected_delivery_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
            'notes' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'amount_paid' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate payment balance availability against bank accounts / cash vault
        $balanceErrorResponse = $this->validatePaymentBalances($request);
        if ($balanceErrorResponse) {
            return $balanceErrorResponse;
        }

        // Validate that proposed PO total is not less than already settled/paid amount
        $proposedSubtotal = 0;
        foreach ($request->items as $item) {
            $proposedSubtotal += $item['quantity_ordered'] * $item['unit_cost'];
        }
        $proposedTotal = $proposedSubtotal + (float) $request->get('tax_amount', 0) + (float) $request->get('shipping_cost', 0);
        $alreadyPaid = (float) $purchaseOrder->amount_paid;

        if ($proposedTotal < $alreadyPaid) {
            return response()->json([
                'message' => "Cannot reduce Purchase Order total (" . number_format($proposedTotal, 2) . ") below total payments already settled (" . number_format($alreadyPaid, 2) . ").",
                'errors' => [
                    'items' => ["Cannot reduce Purchase Order total below total payments already settled ($" . number_format($alreadyPaid, 2) . ")."]
                ]
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Lock Purchase Order record for update
            $purchaseOrder = PurchaseOrder::where('id', $purchaseOrder->id)->lockForUpdate()->firstOrFail();

            $companyId = auth()->user()->current_company_id ?? 1;
            $defaultWh = \App\Models\Warehouse::where('company_id', $companyId)->where('is_default', true)->first()
                ?? \App\Models\Warehouse::where('company_id', $companyId)->first();
            $warehouseId = $request->warehouse_id ?? ($purchaseOrder->warehouse_id ?? ($defaultWh ? $defaultWh->id : 1));

            $inventoryService = new WarehouseInventoryService();
            $accountingService = new DoubleEntryAccountingService();

            // 1. Reverse original GL Accounting Entry (Storno mechanism)
            $accountingService->reverseJournalEntryBySource('purchase_order', $purchaseOrder->id);

            // 2. Reverse previous physical stock adjustments and WAC if previously received
            foreach ($purchaseOrder->purchaseOrderItems as $oldItem) {
                if ($oldItem->quantity_received > 0) {
                    $product = Product::where('id', $oldItem->product_id)->lockForUpdate()->first();
                    if ($product && $product->track_inventory) {
                        // Reverse WAC: revert cost_price before deducting stock
                        $currentStock = (float) $product->stock_quantity;
                        $currentCost = (float) $product->cost_price;
                        $voidQty = (int) $oldItem->quantity_received;
                        $voidUnitCost = (float) $oldItem->unit_cost;

                        $remainingStock = $currentStock - $voidQty;
                        if ($remainingStock > 0) {
                            $revertedCost = (($currentStock * $currentCost) - ($voidQty * $voidUnitCost)) / $remainingStock;
                            $product->update(['cost_price' => round(max(0, $revertedCost), 2)]);
                        }

                        $oldAllocations = $oldItem->warehouse_allocations;
                        if (is_array($oldAllocations) && !empty($oldAllocations)) {
                            foreach ($oldAllocations as $alloc) {
                                $allocWhId = $alloc['warehouse_id'] ?? $warehouseId;
                                $allocQty = (int) ($alloc['quantity'] ?? 0);
                                if ($allocQty > 0) {
                                    $inventoryService->adjustStock(
                                        $allocWhId,
                                        $product->id,
                                        $oldItem->product_variation_id ?? null,
                                        -$allocQty,
                                        $companyId,
                                        'Purchase Order Reversal',
                                        $purchaseOrder->po_number
                                    );
                                }
                            }
                        } else {
                            $inventoryService->adjustStock(
                                $oldItem->warehouse_id ?? $warehouseId,
                                $product->id,
                                $oldItem->product_variation_id ?? null,
                                -$voidQty,
                                $companyId,
                                'Purchase Order Reversal',
                                $purchaseOrder->po_number
                            );
                        }
                    }
                }
            }

            // 3. Calculate new totals
            $subtotal = 0;
            $taxAmount = 0;
            $shippingCost = $request->get('shipping_cost', 0);

            foreach ($request->items as $item) {
                $itemTotal = $item['quantity_ordered'] * $item['unit_cost'];
                $subtotal += $itemTotal;
            }

            $totalAmount = $subtotal + $taxAmount + $shippingCost;

            $grandTotal = $totalAmount;
            // Leave amount_paid as alreadyPaid, the PaymentService will increment it when it creates new payments
            $amountPaid = $alreadyPaid; 
            $dueAmount = max(0, $grandTotal - $amountPaid);

            $supplier = Supplier::findOrFail($request->supplier_id);

            // 4. Update purchase order record
            $purchaseOrder->update([
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name,
                'supplier_phone' => $supplier->phone,
                'supplier_email' => $supplier->email,
                'expected_delivery_date' => $request->expected_delivery_date,
                'status' => 'received',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'grand_total' => $grandTotal,
                'amount_paid' => $amountPaid,
                'due_amount' => $dueAmount,
                'notes' => $request->notes,
                'terms_and_conditions' => $request->terms_and_conditions,
            ]);

            // 5. Delete existing items and recreate with new stock adjustments & WAC
            $purchaseOrder->purchaseOrderItems()->delete();

            foreach ($request->items as $item) {
                $totalCost = $item['quantity_ordered'] * $item['unit_cost'];
                $qtyOrdered = (int) $item['quantity_ordered'];

                $allocations = $item['allocations'] ?? $item['warehouse_allocations'] ?? null;
                if (!is_array($allocations) || empty($allocations)) {
                    $allocations = [
                        ['warehouse_id' => $warehouseId, 'quantity' => $qtyOrdered]
                    ];
                }

                $itemWarehouseId = $allocations[0]['warehouse_id'] ?? $warehouseId;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'product_variation_id' => $item['product_variation_id'] ?? null,
                    'warehouse_id' => $itemWarehouseId,
                    'warehouse_allocations' => $allocations,
                    'quantity_ordered' => $qtyOrdered,
                    'quantity_received' => $qtyOrdered,
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $totalCost,
                    'notes' => $item['notes'] ?? null,
                ]);

                // Adjust stock for new item with WAC calculation & price history
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->first();
                if ($product) {
                    $product->refresh();

                    $oldPrice = $product->purchase_price ?? $product->cost_price ?? 0;
                    \App\Models\ProductPriceHistory::create([
                        'company_id' => $companyId,
                        'product_id' => $product->id,
                        'source_type' => 'purchase_order',
                        'source_id' => $purchaseOrder->id,
                        'purchase_price' => $item['unit_cost'],
                        'old_purchase_price' => $oldPrice,
                    ]);

                    $prodUpdates = [];
                    if (isset($item['sale_price']) && floatval($item['sale_price']) > 0) {
                        $prodUpdates['selling_price'] = (float) $item['sale_price'];
                    } elseif (isset($item['selling_price']) && floatval($item['selling_price']) > 0) {
                        $prodUpdates['selling_price'] = (float) $item['selling_price'];
                    }
                    if (!empty($prodUpdates)) {
                        $product->update($prodUpdates);
                    }

                    if (!empty($item['product_variation_id'])) {
                        $var = \App\Models\ProductVariation::find($item['product_variation_id']);
                        if ($var && isset($prodUpdates['selling_price'])) {
                            $var->update([
                                'retail_price' => $prodUpdates['selling_price'],
                                'sale_price' => $prodUpdates['selling_price'],
                            ]);
                        }
                    }

                    if ($product->track_inventory) {
                        $currentStock = (float) $product->stock_quantity;
                        $currentCost = (float) $product->cost_price;
                        $poUnitCost = (float) $item['unit_cost'];

                        $newTotalStock = $currentStock + $qtyOrdered;
                        if ($newTotalStock > 0) {
                            $wac = (($currentStock * $currentCost) + ($qtyOrdered * $poUnitCost)) / $newTotalStock;
                            $product->update(['cost_price' => round($wac, 2)]);
                        }

                        foreach ($allocations as $alloc) {
                            $allocWhId = $alloc['warehouse_id'] ?? $warehouseId;
                            $allocQty = (int) ($alloc['quantity'] ?? 0);
                            if ($allocQty > 0) {
                                $inventoryService->adjustStock(
                                    $allocWhId,
                                    $product->id,
                                    $item['product_variation_id'] ?? null,
                                    $allocQty,
                                    $companyId,
                                    'Bill',
                                    $purchaseOrder->po_number
                                );
                            }
                        }
                    }
                }
            }

            // 6. Post fresh GL Accounting Entry for updated PO total
            $accountingService->createPurchaseInvoiceEntry($purchaseOrder->fresh());

            // 7. Process upfront payments for updated PO
            $supplier = Supplier::find($purchaseOrder->supplier_id);
            if ($supplier) {
                $this->processPurchaseOrderPayments($purchaseOrder->fresh(), $request, $supplier);
            }

            DB::commit();

            $purchaseOrder->load(['supplier', 'user', 'purchaseOrderItems.product']);

            return response()->json([
                'message' => 'Purchase order updated, inventory re-calculated, and GL journal re-posted successfully',
                'purchase_order' => $purchaseOrder
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder): JsonResponse
    {
        try {
            DB::beginTransaction();

            $companyId = auth()->user()->current_company_id ?? 1;
            $defaultWh = \App\Models\Warehouse::where('company_id', $companyId)->where('is_default', true)->first()
                ?? \App\Models\Warehouse::where('company_id', $companyId)->first();
            $warehouseId = $purchaseOrder->warehouse_id ?? ($defaultWh ? $defaultWh->id : 1);
            $inventoryService = new WarehouseInventoryService();
            $accountingService = new DoubleEntryAccountingService();
            $paymentService = new PaymentService();

            // Reverse GL accounting entries for this Purchase Order
            $accountingService->reverseJournalEntryBySource('purchase_order', $purchaseOrder->id);

            // Reverse attached payments, bank transactions, and payment GL entries
            $payments = Payment::where('reference_type', PurchaseOrder::class)
                ->where('reference_id', $purchaseOrder->id)
                ->where('status', '!=', 'cancelled')
                ->get();
            foreach ($payments as $payment) {
                $paymentService->cancelPayment($payment);
            }

            // Reverse stock and WAC for all received items before deletion
            foreach ($purchaseOrder->purchaseOrderItems as $poItem) {
                if ($poItem->quantity_received > 0) {
                    $product = Product::find($poItem->product_id);
                    if ($product && $product->track_inventory) {
                        $currentStock = (float) $product->stock_quantity;
                        $currentCost = (float) $product->cost_price;
                        $voidQty = (int) $poItem->quantity_received;
                        $voidUnitCost = (float) $poItem->unit_cost;

                        $remainingStock = $currentStock - $voidQty;
                        if ($remainingStock > 0) {
                            $revertedCost = (($currentStock * $currentCost) - ($voidQty * $voidUnitCost)) / $remainingStock;
                            $product->update(['cost_price' => round(max(0, $revertedCost), 2)]);
                        }

                        $oldAllocations = $poItem->warehouse_allocations;
                        if (is_array($oldAllocations) && !empty($oldAllocations)) {
                            foreach ($oldAllocations as $alloc) {
                                $allocWhId = $alloc['warehouse_id'] ?? $warehouseId;
                                $allocQty = (int) ($alloc['quantity'] ?? 0);
                                if ($allocQty > 0) {
                                    $inventoryService->adjustStock(
                                        $allocWhId,
                                        $product->id,
                                        $poItem->product_variation_id ?? null,
                                        -$allocQty,
                                        $companyId,
                                        'Purchase Order Deleted',
                                        $purchaseOrder->po_number
                                    );
                                }
                            }
                        } else {
                            $inventoryService->adjustStock(
                                $poItem->warehouse_id ?? $warehouseId,
                                $product->id,
                                $poItem->product_variation_id ?? null,
                                -$voidQty,
                                $companyId,
                                'Purchase Order Deleted',
                                $purchaseOrder->po_number
                            );
                        }
                    }
                }
            }

            $purchaseOrder->purchaseOrderItems()->delete();
            $purchaseOrder->delete();

            DB::commit();

            return response()->json([
                'message' => 'Purchase order deleted and inventory reversed successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Void a purchase order — cancels the PO and reverses inventory + WAC.
     */
    public function void(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status === 'voided') {
            return response()->json([
                'message' => 'This purchase order has already been voided'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $companyId = auth()->user()->current_company_id ?? 1;
            $defaultWh = \App\Models\Warehouse::where('company_id', $companyId)->where('is_default', true)->first()
                ?? \App\Models\Warehouse::where('company_id', $companyId)->first();
            $warehouseId = $purchaseOrder->warehouse_id ?? ($defaultWh ? $defaultWh->id : 1);
            $inventoryService = new WarehouseInventoryService();
            $accountingService = new DoubleEntryAccountingService();
            $paymentService = new PaymentService();

            // 1. Reverse GL Accounting Entry (Storno mechanism) for Purchase Order
            $accountingService->reverseJournalEntryBySource('purchase_order', $purchaseOrder->id);

            // 2. Reverse physical stock and WAC for all received items & revert product purchase price
            foreach ($purchaseOrder->purchaseOrderItems as $poItem) {
                $product = Product::find($poItem->product_id);
                if ($product) {
                    // Revert to prior active cost
                    $previousCost = \App\Models\ProductPriceHistory::where('product_id', $product->id)
                        ->where('source_id', '!=', $purchaseOrder->id)
                        ->latest('id')
                        ->value('purchase_price') ?? $product->opening_cost ?? $product->purchase_price;

                    $product->update([
                        'purchase_price' => $previousCost,
                    ]);
                }

                if ($poItem->quantity_received > 0 && $product && $product->track_inventory) {
                    // Reverse WAC calculation
                    $currentStock = (float) $product->stock_quantity;
                    $currentCost = (float) $product->cost_price;
                    $voidQty = (int) $poItem->quantity_received;
                    $voidUnitCost = (float) $poItem->unit_cost;

                    $remainingStock = $currentStock - $voidQty;
                    if ($remainingStock > 0) {
                        $revertedCost = (($currentStock * $currentCost) - ($voidQty * $voidUnitCost)) / $remainingStock;
                        $product->update(['cost_price' => round(max(0, $revertedCost), 2)]);
                    }

                        // Deduct stock per warehouse allocation
                        $oldAllocations = $poItem->warehouse_allocations;
                        if (is_array($oldAllocations) && !empty($oldAllocations)) {
                            foreach ($oldAllocations as $alloc) {
                                $allocWhId = $alloc['warehouse_id'] ?? $warehouseId;
                                $allocQty = (int) ($alloc['quantity'] ?? 0);
                                if ($allocQty > 0) {
                                    $inventoryService->adjustStock(
                                        $allocWhId,
                                        $product->id,
                                        $poItem->product_variation_id ?? null,
                                        -$allocQty,
                                        $companyId,
                                        'Purchase Order Voided',
                                        $purchaseOrder->po_number
                                    );
                                }
                            }
                        } else {
                            $inventoryService->adjustStock(
                                $poItem->warehouse_id ?? $warehouseId,
                                $product->id,
                                $poItem->product_variation_id ?? null,
                                -$voidQty,
                                $companyId,
                                'Purchase Order Voided',
                                $purchaseOrder->po_number
                            );
                        }
                    }
                }

            // 3. Cancel attached payments & reverse bank transactions / accounting entries
            $payments = Payment::whereIn('reference_type', [PurchaseOrder::class, 'App\\Models\\PurchaseOrder', 'PurchaseOrder', 'purchase_order'])
                ->where('reference_id', $purchaseOrder->id)
                ->where('status', '!=', 'cancelled')
                ->get();
            foreach ($payments as $payment) {
                $paymentService->cancelPayment($payment);
            }


            // 4. Mark PO as voided/cancelled and reset paid / due amounts
            $purchaseOrder->update([
                'status' => 'cancelled',
                'amount_paid' => 0,
                'due_amount' => 0,
            ]);


            DB::commit();

            $purchaseOrder->load(['supplier', 'user', 'purchaseOrderItems.product']);

            return response()->json([
                'message' => 'Purchase order voided, GL journal reversed, and inventory adjusted successfully',
                'purchase_order' => $purchaseOrder
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to void purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Receive items from purchase order
     */
    public function receive(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (!in_array($purchaseOrder->status, ['sent', 'confirmed', 'partially_received'])) {
            return response()->json([
                'message' => 'Purchase order must be sent or confirmed to receive items'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity_received' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            foreach ($request->items as $item) {
                $poItem = PurchaseOrderItem::find($item['purchase_order_item_id']);

                if ($poItem->purchase_order_id !== $purchaseOrder->id) {
                    throw new \Exception('Purchase order item does not belong to this purchase order');
                }

                $newQuantityReceived = $poItem->quantity_received + $item['quantity_received'];

                if ($newQuantityReceived > $poItem->quantity_ordered) {
                    throw new \Exception('Cannot receive more than ordered quantity for ' . $poItem->product->name);
                }

                // Update received quantity
                $poItem->update(['quantity_received' => $newQuantityReceived]);

                // Update product inventory
                $product = Product::find($poItem->product_id);
                if ($product->track_inventory) {
                    $companyId = $purchaseOrder->company_id ?? auth()->user()->current_company_id ?? 1;
                    $warehouseId = $purchaseOrder->warehouse_id;
                    if (!$warehouseId) {
                        $defaultWh = \App\Models\Warehouse::where('company_id', $companyId)->where('is_default', true)->first();
                        $warehouseId = $defaultWh ? $defaultWh->id : 1;
                    }

                    $inventoryService = new WarehouseInventoryService();

                    // Calculate WAC BEFORE adjusting stock (use pre-adjustment values)
                    $currentStock = (float) $product->stock_quantity;
                    $currentCost = (float) $product->cost_price;
                    $rcvQty = (int) $item['quantity_received'];
                    $rcvUnitCost = (float) $poItem->unit_cost;

                    $newTotalStock = $currentStock + $rcvQty;
                    if ($newTotalStock > 0) {
                        $wac = (($currentStock * $currentCost) + ($rcvQty * $rcvUnitCost)) / $newTotalStock;
                        $product->update(['cost_price' => round($wac, 2)]);
                    }

                    // Now adjust stock
                    $inventoryService->adjustStock($warehouseId, $product->id, null, $item['quantity_received'], $companyId, 'Bill', $purchaseOrder->po_number);
                }
            }

            // Update purchase order status
            $allItems = $purchaseOrder->purchaseOrderItems;
            $fullyReceived = $allItems->every(function ($item) {
                return $item->quantity_received >= $item->quantity_ordered;
            });

            $partiallyReceived = $allItems->some(function ($item) {
                return $item->quantity_received > 0;
            });

            if ($fullyReceived) {
                $purchaseOrder->update([
                    'status' => 'received',
                    'actual_delivery_date' => now()
                ]);

                // Create accounting entries when fully received
                $accountingService = new DoubleEntryAccountingService();
                $accountingService->createPurchaseInvoiceEntry($purchaseOrder);
            } elseif ($partiallyReceived) {
                $purchaseOrder->update(['status' => 'partially_received']);
            }

            DB::commit();

            $purchaseOrder->load(['supplier', 'user', 'purchaseOrderItems.product']);

            return response()->json([
                'message' => 'Items received successfully',
                'purchase_order' => $purchaseOrder
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to receive items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate payment balances against bank accounts / cash vaults with pessimistic row locking.
     */
    protected function validatePaymentBalances(Request $request): ?JsonResponse
    {
        // 1. Check array of payment_details if provided
        if ($request->has('payment_details') && is_array($request->payment_details)) {
            foreach ($request->payment_details as $payDetail) {
                $payAmt = (float) ($payDetail['amount'] ?? 0);
                if ($payAmt > 0 && !empty($payDetail['bank_account_id'])) {
                    $bankAcc = \App\Models\BankAccount::where('id', $payDetail['bank_account_id'])->lockForUpdate()->first();
                    if ($bankAcc) {
                        $available = (float) $bankAcc->current_balance;
                        if ($payAmt > $available) {
                            $accountName = $bankAcc->account_name ?: ($bankAcc->bank_name ?: 'Selected Account');
                            return response()->json([
                                'message' => "Payment failed due to insufficient balance in {$accountName}. Available: $" . number_format($available, 2) . ", Required: $" . number_format($payAmt, 2) . ".",
                                'errors' => [
                                    'payment_details' => ["Insufficient balance in {$accountName}. Available: $" . number_format($available, 2) . ", Required: $" . number_format($payAmt, 2) . "."]
                                ]
                            ], 422);
                        }
                    }
                }
            }
        }

        // 2. Check direct amount_paid & bank_account_id if provided
        $amountPaid = (float) $request->get('amount_paid', 0);
        if ($amountPaid > 0 && $request->filled('bank_account_id')) {
            $bankAcc = \App\Models\BankAccount::where('id', $request->bank_account_id)->lockForUpdate()->first();
            if ($bankAcc) {
                $available = (float) $bankAcc->current_balance;
                if ($amountPaid > $available) {
                    $accountName = $bankAcc->account_name ?: ($bankAcc->bank_name ?: 'Selected Account');
                    return response()->json([
                        'message' => "Payment failed due to insufficient balance in {$accountName}. Available: $" . number_format($available, 2) . ", Required: $" . number_format($amountPaid, 2) . ".",
                        'errors' => [
                            'payment_details' => ["Insufficient balance in {$accountName}. Available: $" . number_format($available, 2) . ", Required: $" . number_format($amountPaid, 2) . "."]
                        ]
                    ], 422);
                }
            }
        }

        return null;
    }

    /**
     * Process upfront payments for purchase order.
     */
    protected function processPurchaseOrderPayments(PurchaseOrder $purchaseOrder, Request $request, Supplier $supplier): void
    {
        $paymentService = new PaymentService();

        // 1. Process split payments if payment_details array is provided
        if ($request->has('payment_details') && is_array($request->payment_details)) {
            foreach ($request->payment_details as $payDetail) {
                $payAmt = (float) ($payDetail['amount'] ?? 0);
                if ($payAmt <= 0) continue;

                $bankAccId = $payDetail['bank_account_id'] ?? null;
                if (!$bankAccId && ($payDetail['payment_method'] ?? 'cash') === 'cash') {
                    $companyId = auth()->user()?->current_company_id ?? 1;
                    $defaultCashAcc = BankAccount::where('company_id', $companyId)
                        ->where(function ($q) {
                            $q->where('account_type', 'cash')
                              ->orWhere('account_name', 'LIKE', '%Cash%');
                        })
                        ->first();
                    $bankAccId = $defaultCashAcc ? $defaultCashAcc->id : null;
                }

                if ($bankAccId) {
                    $payment = Payment::create([
                        'payment_number' => Payment::generatePaymentNumber(),
                        'payment_type' => 'supplier_payment',
                        'payee_type' => Supplier::class,
                        'payee_id' => $supplier->id,
                        'payee_name' => $supplier->name,
                        'bank_account_id' => $bankAccId,
                        'amount' => $payAmt,
                        'payment_date' => now()->toDateString(),
                        'payment_method' => $payDetail['payment_method'] ?? 'cash',
                        'reference_type' => PurchaseOrder::class,
                        'reference_id' => $purchaseOrder->id,
                        'reference_number' => $purchaseOrder->po_number,
                        'description' => "Upfront Payment for PO #{$purchaseOrder->po_number}",
                        'status' => 'pending',
                        'created_by' => auth()->id() ?? 1,
                        'paid_by' => auth()->id() ?? 1,
                        'paid_at' => now(),
                    ]);

                    $paymentService->markPaymentAsPaid($payment, auth()->id() ?? 1);
                }
            }
        }
        // 2. Fallback to single payment if amount_paid > 0 and no payment_details array
        else {
            $existingPaid = Payment::whereIn('reference_type', [PurchaseOrder::class, 'App\\Models\\PurchaseOrder', 'PurchaseOrder'])
                ->where('reference_id', $purchaseOrder->id)
                ->where('status', 'paid')
                ->sum('amount');
            $requestedPaid = (float) $request->get('amount_paid', 0);
            $newPaymentAmount = max(0, $requestedPaid - $existingPaid);

            if ($newPaymentAmount > 0) {
                $bankAccId = $request->bank_account_id;
                if (!$bankAccId) {
                    $companyId = auth()->user()?->current_company_id ?? 1;
                    $defaultCashAcc = BankAccount::where('company_id', $companyId)
                        ->where(function ($q) {
                            $q->where('account_type', 'cash')
                              ->orWhere('account_name', 'LIKE', '%Cash%');
                        })
                        ->first();
                    $bankAccId = $defaultCashAcc ? $defaultCashAcc->id : null;
                }

                if ($bankAccId) {
                    $payment = Payment::create([
                        'payment_number' => Payment::generatePaymentNumber(),
                        'payment_type' => 'supplier_payment',
                        'payee_type' => Supplier::class,
                        'payee_id' => $supplier->id,
                        'payee_name' => $supplier->name,
                        'bank_account_id' => $bankAccId,
                        'amount' => $newPaymentAmount,
                        'payment_date' => now()->toDateString(),
                        'payment_method' => $request->payment_method ?? 'cash',
                        'reference_type' => PurchaseOrder::class,
                        'reference_id' => $purchaseOrder->id,
                        'reference_number' => $purchaseOrder->po_number,
                        'description' => "Upfront Payment for PO #{$purchaseOrder->po_number}",
                        'status' => 'pending',
                        'created_by' => auth()->id() ?? 1,
                        'paid_by' => auth()->id() ?? 1,
                        'paid_at' => now(),
                    ]);

                    $paymentService->markPaymentAsPaid($payment, auth()->id() ?? 1);

                }
            }
        }
    }
}
