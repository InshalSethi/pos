<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\DoubleEntryAccountingService;
use App\Services\WarehouseInventoryService;
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
     * Get the count of purchase orders grouped by status.
     */
    public function getStatusCounts(): JsonResponse
    {
        $all = PurchaseOrder::count();
        $draft = PurchaseOrder::where('status', 'draft')->count();
        $sent = PurchaseOrder::where('status', 'sent')->count();
        $confirmed = PurchaseOrder::where('status', 'confirmed')->count();
        $partiallyReceived = PurchaseOrder::where('status', 'partially_received')->count();
        $received = PurchaseOrder::where('status', 'received')->count();
        $cancelled = PurchaseOrder::where('status', 'cancelled')->count();

        return response()->json([
            'all' => $all,
            'draft' => $draft,
            'sent' => $sent,
            'confirmed' => $confirmed,
            'partially_received' => $partiallyReceived,
            'received' => $received,
            'cancelled' => $cancelled,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseOrder::with(['supplier', 'user', 'purchaseOrderItems.product']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by supplier
        if ($request->has('supplier_id') && $request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }

        // Legacy support for start_date and end_date
        if ($request->has('start_date')) {
            $query->whereDate('order_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('order_date', '<=', $request->end_date);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'order_date');
        $sortOrder = $request->get('sort_order', 'desc');

        // Validate sort fields
        $allowedSortFields = ['order_date', 'po_number', 'total_amount', 'due_amount', 'status', 'expected_delivery_date', 'created_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('order_date', 'desc');
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
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

            $amountPaid = $request->get('amount_paid', 0);
            $grandTotal = $totalAmount;

            // --- ADVANCE BALANCE APPLICATION ---
            $advanceApplied = 0;
            $supplier = Supplier::findOrFail($request->supplier_id);
            if ($supplier && $request->boolean('use_advance_balance') && (float) $supplier->advance_balance > 0) {
                $requestedAdvance = (float) ($request->advance_applied ?? $supplier->advance_balance);
                $advanceApplied = min($requestedAdvance, (float) $supplier->advance_balance, $grandTotal);
            }

            $effectivePaid = $amountPaid + $advanceApplied;
            $dueAmount = max(0, $grandTotal - $effectivePaid);

            // Check for overpayment → store as advance
            $overpayment = max(0, $effectivePaid - $grandTotal);

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
                'amount_paid' => $effectivePaid - $overpayment,
                'due_amount' => $dueAmount,
                'notes' => $request->notes,
                'terms_and_conditions' => $request->terms_and_conditions,
            ]);

            $companyId = auth()->user()->current_company_id ?? 1;
            $defaultWh = \App\Models\Warehouse::where('company_id', $companyId)->where('is_default', true)->first()
                ?? \App\Models\Warehouse::where('company_id', $companyId)->first();
            $warehouseId = $request->warehouse_id ?? ($defaultWh ? $defaultWh->id : 1);

            $inventoryService = new WarehouseInventoryService();

            // Create purchase order items and auto-stock inventory
            foreach ($request->items as $item) {
                $totalCost = $item['quantity_ordered'] * $item['unit_cost'];
                $qtyOrdered = (int) $item['quantity_ordered'];

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $qtyOrdered,
                    'quantity_received' => $qtyOrdered,
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $totalCost,
                    'notes' => $item['notes'] ?? null,
                ]);

                // Auto-stock inventory immediately upon creation
                $product = Product::find($item['product_id']);
                if ($product && $product->track_inventory) {
                    // Calculate Weighted Average Cost (WAC) before adjusting stock
                    $currentStock = (float) $product->stock_quantity;
                    $currentCost = (float) $product->cost_price;
                    $poUnitCost = (float) $item['unit_cost'];

                    $newTotalStock = $currentStock + $qtyOrdered;
                    if ($newTotalStock > 0) {
                        $wac = (($currentStock * $currentCost) + ($qtyOrdered * $poUnitCost)) / $newTotalStock;
                        $product->update(['cost_price' => round($wac, 2)]);
                    }

                    $inventoryService->adjustStock(
                        $warehouseId,
                        $product->id,
                        $item['product_variation_id'] ?? null,
                        $qtyOrdered,
                        $companyId,
                        'Bill',
                        $purchaseOrder->po_number
                    );
                }
            }

            // Create double-entry accounting entries
            $accountingService = new DoubleEntryAccountingService();
            $accountingService->createPurchaseInvoiceEntry($purchaseOrder);

            // --- DEBIT ADVANCE if advance was applied ---
            if ($advanceApplied > 0 && $supplier) {
                $supplier->debitAdvance($advanceApplied);
            }

            // --- CAPTURE OVERPAYMENT into advance balance ---
            if ($overpayment > 0 && $supplier) {
                $supplier->creditAdvance($overpayment);
            }

            DB::commit();

            $purchaseOrder->load(['supplier', 'user', 'purchaseOrderItems.product']);

            return response()->json([
                'message' => 'Purchase order created successfully and items added to inventory',
                'purchase_order' => $purchaseOrder
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

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
        $purchaseOrder->load(['supplier', 'user', 'purchaseOrderItems.product']);
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

        try {
            DB::beginTransaction();

            $companyId = auth()->user()->current_company_id ?? 1;
            $defaultWh = \App\Models\Warehouse::where('company_id', $companyId)->where('is_default', true)->first()
                ?? \App\Models\Warehouse::where('company_id', $companyId)->first();
            $warehouseId = $request->warehouse_id ?? ($purchaseOrder->warehouse_id ?? ($defaultWh ? $defaultWh->id : 1));

            $inventoryService = new WarehouseInventoryService();

            // Reverse previous stock adjustments and WAC if previously received
            foreach ($purchaseOrder->purchaseOrderItems as $oldItem) {
                if ($oldItem->quantity_received > 0) {
                    $product = Product::find($oldItem->product_id);
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
                        // If remaining stock is 0, retain existing cost_price as base

                        $inventoryService->adjustStock(
                            $warehouseId,
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

            // Calculate new totals
            $subtotal = 0;
            $taxAmount = 0;
            $shippingCost = $request->get('shipping_cost', 0);

            foreach ($request->items as $item) {
                $itemTotal = $item['quantity_ordered'] * $item['unit_cost'];
                $subtotal += $itemTotal;
            }

            $totalAmount = $subtotal + $taxAmount + $shippingCost;

            $amountPaid = $request->get('amount_paid', 0);
            $grandTotal = $totalAmount;
            $dueAmount = max(0, $grandTotal - $amountPaid);

            $supplier = Supplier::findOrFail($request->supplier_id);

            // Update purchase order
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

            // Delete existing items and recreate with new stock adjustments
            $purchaseOrder->purchaseOrderItems()->delete();

            foreach ($request->items as $item) {
                $totalCost = $item['quantity_ordered'] * $item['unit_cost'];
                $qtyOrdered = (int) $item['quantity_ordered'];

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $qtyOrdered,
                    'quantity_received' => $qtyOrdered,
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $totalCost,
                    'notes' => $item['notes'] ?? null,
                ]);

                // Adjust stock for new item with WAC
                $product = Product::find($item['product_id']);
                if ($product && $product->track_inventory) {
                    // Refresh product to get latest stock/cost after reversal
                    $product->refresh();

                    // Calculate WAC before adjusting stock
                    $currentStock = (float) $product->stock_quantity;
                    $currentCost = (float) $product->cost_price;
                    $poUnitCost = (float) $item['unit_cost'];

                    $newTotalStock = $currentStock + $qtyOrdered;
                    if ($newTotalStock > 0) {
                        $wac = (($currentStock * $currentCost) + ($qtyOrdered * $poUnitCost)) / $newTotalStock;
                        $product->update(['cost_price' => round($wac, 2)]);
                    }

                    $inventoryService->adjustStock(
                        $warehouseId,
                        $product->id,
                        $item['product_variation_id'] ?? null,
                        $qtyOrdered,
                        $companyId,
                        'Bill',
                        $purchaseOrder->po_number
                    );
                }
            }

            DB::commit();

            $purchaseOrder->load(['supplier', 'user', 'purchaseOrderItems.product']);

            return response()->json([
                'message' => 'Purchase order updated successfully',
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

                        $inventoryService->adjustStock(
                            $warehouseId,
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

            // Reverse stock and WAC for all received items
            foreach ($purchaseOrder->purchaseOrderItems as $poItem) {
                if ($poItem->quantity_received > 0) {
                    $product = Product::find($poItem->product_id);
                    if ($product && $product->track_inventory) {
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
                        // If remaining stock is 0 or less, retain existing cost_price as base

                        // Deduct stock
                        $inventoryService->adjustStock(
                            $warehouseId,
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

            // Mark PO as voided
            $purchaseOrder->update([
                'status' => 'voided',
            ]);

            DB::commit();

            $purchaseOrder->load(['supplier', 'user', 'purchaseOrderItems.product']);

            return response()->json([
                'message' => 'Purchase order voided and inventory reversed successfully',
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
     * Get the next available purchase order number.
     */
    public function getNextPurchaseOrderNumber(): JsonResponse
    {
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
        return response()->json([
            'success' => true,
            'po_number' => $poNumber
        ]);
    }
}
