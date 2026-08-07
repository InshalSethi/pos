<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Services\DoubleEntryAccountingService;
use App\Services\WarehouseInventoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    /**
     * Display a listing of purchase returns with search & filters.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = auth()->user()?->current_company_id ?? 1;

        $query = PurchaseReturn::where('company_id', $companyId)
            ->with(['supplier', 'originalPurchaseOrder', 'warehouse', 'user']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('originalPurchaseOrder', function ($poq) use ($search) {
                      $poq->where('po_number', 'like', "%{$search}%");
                  });
            });
        }

        // Specific Original PO / Bill Number search
        if ($request->filled('original_po') || $request->filled('po_number')) {
            $poSearch = $request->input('original_po') ?? $request->input('po_number');
            $query->whereHas('originalPurchaseOrder', function ($poq) use ($poSearch) {
                $poq->where('po_number', 'like', "%{$poSearch}%");
            });
        }

        // Filter by status (supports array, comma-separated, or scalar)
        if ($request->filled('status') && $request->status !== 'all') {
            $statusInput = $request->status;
            $statuses = is_array($statusInput) ? $statusInput : explode(',', $statusInput);

            $query->where(function ($q) use ($statuses) {
                foreach ($statuses as $st) {
                    $st = trim($st);
                    if ($st === 'cancelled' || $st === 'void') {
                        $q->orWhereIn('status', ['cancelled', 'void', 'voided']);
                    } else {
                        $q->orWhere('status', $st);
                    }
                }
            });
        }

        // Filter by refund status
        if ($request->filled('refund_status') && $request->refund_status !== 'all') {
            $query->where('refund_status', $request->refund_status);
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
                $query->whereIn('warehouse_id', $whIds);
            }
        }

        // Filter by return reason (supports array, comma-separated, or scalar)
        if ($request->filled('reason') || $request->filled('reasons') || $request->filled('return_reasons')) {
            $rawReasons = $request->input('reasons') ?? $request->input('return_reasons') ?? $request->input('reason');
            $reasons = is_array($rawReasons) ? $rawReasons : array_filter(explode(',', $rawReasons));
            if (!empty($reasons)) {
                $query->where(function ($q) use ($reasons) {
                    foreach ($reasons as $r) {
                        $q->orWhere('reason', 'like', '%' . trim($r) . '%');
                    }
                });
            }
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->whereHas('purchaseReturnItems', function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('return_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('return_date', '<=', $request->date_to);
        }

        // Status counts for status tabs
        $statusCounts = [
            'all'       => PurchaseReturn::where('company_id', $companyId)->count(),
            'draft'     => PurchaseReturn::where('company_id', $companyId)->where('status', 'draft')->count(),
            'pending'   => PurchaseReturn::where('company_id', $companyId)->where('status', 'pending')->count(),
            'approved'  => PurchaseReturn::where('company_id', $companyId)->where('status', 'approved')->count(),
            'completed' => PurchaseReturn::where('company_id', $companyId)->where('status', 'completed')->count(),
            'cancelled' => PurchaseReturn::where('company_id', $companyId)->whereIn('status', ['cancelled', 'void', 'voided'])->count(),
        ];

        // Sorting
        $sortField = $request->get('sort_by', $request->get('sort_field', 'created_at'));
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSorts = ['return_number', 'return_date', 'total_amount', 'status', 'refund_status', 'created_at'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, strtolower($sortOrder) === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = (int) $request->get('per_page', 15);
        $returns = $query->paginate($perPage);

        return response()->json([
            'status_counts' => $statusCounts,
            'returns'       => $returns,
        ]);
    }

    /**
     * Get next sequential Purchase Return number.
     */
    public function getNextReturnNumber(): JsonResponse
    {
        $companyId = auth()->user()?->current_company_id ?? 1;
        $prefix = 'PR-' . date('Y');
        $lastReturn = PurchaseReturn::where('company_id', $companyId)
            ->where('return_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastReturn) {
            $parts = explode('-', $lastReturn->return_number);
            $lastSeq = (int) end($parts);
            $nextSeq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        return response()->json([
            'next_number' => "{$prefix}-{$nextSeq}"
        ]);
    }

    /**
     * Get items of a Purchase Order with remaining returnable quantity limits.
     */
    public function getPoItems(int $poId): JsonResponse
    {
        $companyId = auth()->user()?->current_company_id ?? 1;

        $po = PurchaseOrder::where('company_id', $companyId)
            ->with(['supplier', 'items.product'])
            ->findOrFail($poId);

        // Calculate total previously returned quantities for each product on this PO
        $previousReturns = PurchaseReturnItem::whereHas('purchaseReturn', function ($q) use ($poId) {
                $q->where('purchase_order_id', $poId)->whereIn('status', ['draft', 'pending', 'approved', 'completed']);
            })
            ->select('product_id', DB::raw('SUM(quantity) as returned_qty'))
            ->groupBy('product_id')
            ->pluck('returned_qty', 'product_id');

        $poItems = $po->items->map(function ($item) use ($previousReturns) {
            $receivedQty = $item->quantity_received ?? $item->quantity;
            $alreadyReturned = $previousReturns[$item->product_id] ?? 0;
            $maxReturnable = max(0, $receivedQty - $alreadyReturned);

            return [
                'product_id'        => $item->product_id,
                'product_name'      => $item->product?->name ?? 'Unknown Product',
                'product_sku'       => $item->product?->sku ?? '',
                'unit_cost'         => (float) ($item->unit_cost ?? $item->unit_price ?? 0),
                'quantity_ordered'  => (int) $item->quantity,
                'quantity_received' => (int) $receivedQty,
                'already_returned'  => (int) $alreadyReturned,
                'max_returnable'    => (int) $maxReturnable,
                'tax_amount'        => (float) ($item->tax_amount ?? 0),
                'discount_amount'   => (float) ($item->discount_amount ?? 0),
            ];
        });

        return response()->json([
            'purchase_order' => $po,
            'items'          => $poItems,
        ]);
    }

    /**
     * Store a newly created Purchase Return.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'supplier_id'       => 'required|exists:suppliers,id',
            'warehouse_id'      => 'nullable|exists:warehouses,id',
            'return_date'       => 'required|date',
            'reason'            => 'required|string|max:255',
            'status'            => 'nullable|in:draft,pending,approved,completed',
            'refund_status'     => 'nullable|in:pending,partial,refunded',
            'notes'             => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:1',
            'items.*.unit_cost'  => 'required|numeric|min:0',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        $companyId = auth()->user()?->current_company_id ?? 1;

        // If PO-linked, validate max returnable quantity limits
        if ($request->filled('purchase_order_id')) {
            $poId = $request->purchase_order_id;
            $po = PurchaseOrder::find($poId);

            if ($po) {
                $previousReturns = PurchaseReturnItem::whereHas('purchaseReturn', function ($q) use ($poId) {
                        $q->where('purchase_order_id', $poId)->whereIn('status', ['draft', 'pending', 'approved', 'completed']);
                    })
                    ->select('product_id', DB::raw('SUM(quantity) as returned_qty'))
                    ->groupBy('product_id')
                    ->pluck('returned_qty', 'product_id');

                foreach ($request->items as $item) {
                    $poItem = $po->items->firstWhere('product_id', $item['product_id']);
                    if ($poItem) {
                        $receivedQty = $poItem->quantity_received ?? $poItem->quantity;
                        $alreadyReturned = $previousReturns[$item['product_id']] ?? 0;
                        $maxReturnable = max(0, $receivedQty - $alreadyReturned);

                        if ($item['quantity'] > $maxReturnable) {
                            $productName = Product::find($item['product_id'])?->name ?? 'Product';
                            return response()->json([
                                'message' => "Returned quantity for '{$productName}' ({$item['quantity']}) exceeds maximum returnable quantity ({$maxReturnable}).",
                                'errors'  => ['items' => ["Returned quantity exceeds PO received limit for {$productName}."]]
                            ], 422);
                        }
                    }
                }
            }
        }

        try {
            DB::beginTransaction();

            // Generate return number if not provided
            $returnNumber = $request->return_number;
            if (!$returnNumber) {
                $prefix = 'PR-' . date('Y');
                $lastReturn = PurchaseReturn::where('company_id', $companyId)
                    ->where('return_number', 'like', "{$prefix}%")
                    ->orderBy('id', 'desc')
                    ->first();
                $lastSeq = $lastReturn ? (int) end(explode('-', $lastReturn->return_number)) : 0;
                $returnNumber = "{$prefix}-" . str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
            }

            // Calculate totals
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;

            foreach ($request->items as $item) {
                $qty = (float) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];
                $itemSubtotal = $qty * $unitCost;
                $itemTax = (float) ($item['tax_amount'] ?? 0);
                $itemDiscount = (float) ($item['discount_amount'] ?? 0);

                $subtotal += $itemSubtotal;
                $totalTax += $itemTax;
                $totalDiscount += $itemDiscount;
            }

            $grandTotal = max(0, ($subtotal + $totalTax) - $totalDiscount);
            $status = $request->status ?? 'pending';

            // Create purchase return
            $purchaseReturn = PurchaseReturn::create([
                'company_id'        => $companyId,
                'return_number'     => $returnNumber,
                'purchase_order_id' => $request->purchase_order_id,
                'supplier_id'       => $request->supplier_id,
                'warehouse_id'      => $request->warehouse_id,
                'user_id'           => auth()->id(),
                'return_date'       => $request->return_date,
                'reason'            => $request->reason,
                'subtotal'          => $subtotal,
                'tax_amount'        => $totalTax,
                'discount_amount'   => $totalDiscount,
                'total_amount'      => $grandTotal,
                'status'            => $status,
                'refund_status'     => $request->refund_status ?? 'pending',
                'notes'             => $request->notes,
            ]);

            // Save items
            foreach ($request->items as $item) {
                $qty = (float) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];
                $itemSubtotal = $qty * $unitCost;
                $itemTax = (float) ($item['tax_amount'] ?? 0);
                $itemDiscount = (float) ($item['discount_amount'] ?? 0);
                $itemTotalCost = max(0, ($itemSubtotal + $itemTax) - $itemDiscount);

                $purchaseReturn->items()->create([
                    'product_id'      => $item['product_id'],
                    'warehouse_id'    => $request->warehouse_id,
                    'quantity'        => $qty,
                    'unit_cost'       => $unitCost,
                    'tax_amount'      => $itemTax,
                    'discount_amount' => $itemDiscount,
                    'subtotal'        => $itemSubtotal,
                    'total_cost'      => $itemTotalCost,
                    'notes'           => $item['notes'] ?? null,
                ]);
            }

            // Deduct stock & create double-entry journal if approved/completed
            if (in_array($status, ['approved', 'completed'])) {
                $this->processStockDeductionAndAccounting($purchaseReturn);
            }

            DB::commit();

            $purchaseReturn->load(['supplier', 'originalPurchaseOrder', 'warehouse', 'items.product', 'user']);

            return response()->json([
                'message'         => 'Purchase return created successfully',
                'purchase_return' => $purchaseReturn
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create purchase return',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified purchase return.
     */
    public function show($id): JsonResponse
    {
        $companyId = auth()->user()?->current_company_id ?? 1;

        $purchaseReturn = PurchaseReturn::where('company_id', $companyId)
            ->with([
                'supplier',
                'originalPurchaseOrder',
                'warehouse',
                'items.product',
                'user'
            ])
            ->findOrFail($id);

        return response()->json($purchaseReturn);
    }

    /**
     * Update the specified purchase return.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $companyId = auth()->user()?->current_company_id ?? 1;
        $purchaseReturn = PurchaseReturn::where('company_id', $companyId)->findOrFail($id);

        if (in_array($purchaseReturn->status, ['completed', 'cancelled'])) {
            return response()->json([
                'message' => 'Completed or cancelled purchase returns cannot be edited.'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'supplier_id'       => 'sometimes|exists:suppliers,id',
            'warehouse_id'      => 'nullable|exists:warehouses,id',
            'return_date'       => 'sometimes|date',
            'reason'            => 'sometimes|string|max:255',
            'status'            => 'nullable|in:draft,pending,approved,completed,cancelled',
            'refund_status'     => 'nullable|in:pending,partial,refunded',
            'notes'             => 'nullable|string',
            'items'             => 'sometimes|array|min:1',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity'   => 'required_with:items|numeric|min:1',
            'items.*.unit_cost'  => 'required_with:items|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $oldStatus = $purchaseReturn->status;
            $newStatus = $request->status ?? $oldStatus;

            // Update basic fields
            $purchaseReturn->fill($request->only([
                'purchase_order_id',
                'supplier_id',
                'warehouse_id',
                'return_date',
                'reason',
                'status',
                'refund_status',
                'notes',
            ]));

            // Update line items if provided
            if ($request->has('items')) {
                // Delete existing items
                $purchaseReturn->items()->delete();

                $subtotal = 0;
                $totalTax = 0;
                $totalDiscount = 0;

                foreach ($request->items as $item) {
                    $qty = (float) $item['quantity'];
                    $unitCost = (float) $item['unit_cost'];
                    $itemSubtotal = $qty * $unitCost;
                    $itemTax = (float) ($item['tax_amount'] ?? 0);
                    $itemDiscount = (float) ($item['discount_amount'] ?? 0);
                    $itemTotalCost = max(0, ($itemSubtotal + $itemTax) - $itemDiscount);

                    $subtotal += $itemSubtotal;
                    $totalTax += $itemTax;
                    $totalDiscount += $itemDiscount;

                    $purchaseReturn->items()->create([
                        'product_id'      => $item['product_id'],
                        'warehouse_id'    => $request->warehouse_id ?? $purchaseReturn->warehouse_id,
                        'quantity'        => $qty,
                        'unit_cost'       => $unitCost,
                        'tax_amount'      => $itemTax,
                        'discount_amount' => $itemDiscount,
                        'subtotal'        => $itemSubtotal,
                        'total_cost'      => $itemTotalCost,
                        'notes'           => $item['notes'] ?? null,
                    ]);
                }

                $purchaseReturn->subtotal = $subtotal;
                $purchaseReturn->tax_amount = $totalTax;
                $purchaseReturn->discount_amount = $totalDiscount;
                $purchaseReturn->total_amount = max(0, ($subtotal + $totalTax) - $totalDiscount);
            }

            $purchaseReturn->save();

            // Trigger stock deduction and accounting if status changed from draft/pending to approved/completed
            if (!in_array($oldStatus, ['approved', 'completed']) && in_array($newStatus, ['approved', 'completed'])) {
                $this->processStockDeductionAndAccounting($purchaseReturn);
            }

            DB::commit();

            $purchaseReturn->load(['supplier', 'originalPurchaseOrder', 'warehouse', 'items.product', 'user']);

            return response()->json([
                'message'         => 'Purchase return updated successfully',
                'purchase_return' => $purchaseReturn
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update purchase return',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified purchase return from storage.
     */
    public function destroy($id): JsonResponse
    {
        $companyId = auth()->user()?->current_company_id ?? 1;
        $purchaseReturn = PurchaseReturn::where('company_id', $companyId)->findOrFail($id);

        if (in_array($purchaseReturn->status, ['approved', 'completed'])) {
            return response()->json([
                'message' => 'Approved or completed purchase returns cannot be deleted directly.'
            ], 422);
        }

        try {
            DB::beginTransaction();
            $purchaseReturn->items()->delete();
            $purchaseReturn->delete();
            DB::commit();

            return response()->json([
                'message' => 'Purchase return deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete purchase return',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve purchase return.
     */
    public function approve($id): JsonResponse
    {
        $companyId = auth()->user()?->current_company_id ?? 1;
        $purchaseReturn = PurchaseReturn::where('company_id', $companyId)
            ->with(['items.product'])
            ->findOrFail($id);

        if (in_array($purchaseReturn->status, ['approved', 'completed'])) {
            return response()->json([
                'message' => 'Purchase return is already approved or completed.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $purchaseReturn->status = 'approved';
            $purchaseReturn->save();

            $this->processStockDeductionAndAccounting($purchaseReturn);

            DB::commit();

            return response()->json([
                'message'         => 'Purchase return approved and inventory adjusted successfully',
                'purchase_return' => $purchaseReturn
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to approve purchase return',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject/Cancel purchase return.
     */
    public function reject($id): JsonResponse
    {
        $companyId = auth()->user()?->current_company_id ?? 1;
        $purchaseReturn = PurchaseReturn::where('company_id', $companyId)->findOrFail($id);

        if ($purchaseReturn->status === 'completed') {
            return response()->json([
                'message' => 'Completed purchase returns cannot be rejected.'
            ], 400);
        }

        $purchaseReturn->update(['status' => 'cancelled']);

        return response()->json([
            'message'         => 'Purchase return cancelled successfully',
            'purchase_return' => $purchaseReturn
        ]);
    }

    /**
     * Helper method to deduct stock & record double-entry accounting entry.
     */
    protected function processStockDeductionAndAccounting(PurchaseReturn $purchaseReturn): void
    {
        $inventoryService = new WarehouseInventoryService();
        $accountingService = new DoubleEntryAccountingService();
        $companyId = $purchaseReturn->company_id ?: (auth()->user()?->current_company_id ?? 1);
        $warehouseId = $purchaseReturn->warehouse_id ?? 1;

        foreach ($purchaseReturn->items as $item) {
            $product = $item->product ?: Product::find($item->product_id);
            if ($product && $product->track_inventory) {
                $inventoryService->adjustStock(
                    $item->warehouse_id ?? $warehouseId,
                    $product->id,
                    null,
                    -$item->quantity,
                    $companyId,
                    'Purchase Return',
                    $purchaseReturn->return_number
                );
            }
        }

        // Post accounting entry
        $accountingService->createPurchaseReturnEntry($purchaseReturn);
    }
}
