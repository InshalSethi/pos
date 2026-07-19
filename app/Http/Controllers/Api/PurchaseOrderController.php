<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
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
        $this->middleware('permission:purchases.delete')->only(['destroy']);
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
            'expected_delivery_date' => 'nullable|date|after:today',
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

            // Calculate totals
            $subtotal = 0;
            $taxAmount = 0;
            $shippingCost = $request->get('shipping_cost', 0);

            foreach ($request->items as $item) {
                $itemTotal = $item['quantity_ordered'] * $item['unit_cost'];
                $subtotal += $itemTotal;
            }

            $totalAmount = $subtotal + $taxAmount + $shippingCost;

            // Generate PO number
            if ($request->has('po_number') && $request->po_number) {
                $poNumber = $request->po_number;
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
            $dueAmount = max(0, $grandTotal - $amountPaid);

            // Create purchase order
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $request->supplier_id,
                'user_id' => auth()->id(),
                'order_date' => now(),
                'expected_delivery_date' => $request->expected_delivery_date,
                'status' => 'draft',
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

            // Create purchase order items
            foreach ($request->items as $item) {
                $totalCost = $item['quantity_ordered'] * $item['unit_cost'];

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $item['quantity_ordered'],
                    'quantity_received' => 0,
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $totalCost,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            $purchaseOrder->load(['supplier', 'user', 'purchaseOrderItems.product']);

            return response()->json([
                'message' => 'Purchase order created successfully',
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
        if ($purchaseOrder->status !== 'draft') {
            return response()->json([
                'message' => 'Can only edit draft purchase orders'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'expected_delivery_date' => 'nullable|date|after:today',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
            'notes' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'status' => 'nullable|in:draft,sent,confirmed',
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

            // Update purchase order
            $purchaseOrder->update([
                'supplier_id' => $request->supplier_id,
                'expected_delivery_date' => $request->expected_delivery_date,
                'status' => $request->get('status', $purchaseOrder->status),
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

            // Delete existing items and recreate
            $purchaseOrder->purchaseOrderItems()->delete();

            foreach ($request->items as $item) {
                $totalCost = $item['quantity_ordered'] * $item['unit_cost'];

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $item['quantity_ordered'],
                    'quantity_received' => 0,
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $totalCost,
                    'notes' => $item['notes'] ?? null,
                ]);
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
        if ($purchaseOrder->status !== 'draft') {
            return response()->json([
                'message' => 'Can only delete draft purchase orders'
            ], 422);
        }

        $purchaseOrder->delete();

        return response()->json([
            'message' => 'Purchase order deleted successfully'
        ]);
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
                    $inventoryService->adjustStock($warehouseId, $product->id, null, $item['quantity_received'], $companyId, 'Bill', $purchaseOrder->po_number);

                    // Update cost price if different
                    if ($product->cost_price != $poItem->unit_cost) {
                        $product->update(['cost_price' => $poItem->unit_cost]);
                    }
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
