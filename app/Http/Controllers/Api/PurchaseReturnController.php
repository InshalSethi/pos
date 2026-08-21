<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\Inventory;
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

        $withRelations = ['supplier', 'originalPurchaseOrder', 'user', 'bankAccount'];
        if (\Schema::hasColumn('purchase_returns', 'warehouse_id')) {
            $withRelations[] = 'warehouse';
        }
        if (\Schema::hasColumn('purchase_returns', 'counter_id')) {
            $withRelations[] = 'counter';
        }

        $query = PurchaseReturn::where('company_id', $companyId)->with($withRelations);

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
    public function getPoItems(Request $request, int $poId): JsonResponse
    {
        $companyId = auth()->user()?->current_company_id ?? 1;

        $po = PurchaseOrder::where('company_id', $companyId)
            ->with(['supplier', 'items.product', 'items.warehouse', 'warehouse', 'returns'])
            ->findOrFail($poId);

        // Optional: exclude a specific return (used in Edit mode so the current
        // return's own quantities are not counted as "previously returned")
        $excludeReturnId = $request->query('exclude_return_id');

        // Calculate total previously returned quantities for each product/variation on this PO
        $previousReturns = PurchaseReturnItem::whereHas('purchaseReturn', function ($q) use ($poId, $excludeReturnId) {
                $q->where('purchase_order_id', $poId)->whereIn('status', ['draft', 'pending', 'approved', 'completed']);
                if ($excludeReturnId) {
                    $q->where('purchase_returns.id', '!=', $excludeReturnId);
                }
            })
            ->select('product_id', 'product_variation_id', DB::raw('SUM(quantity) as returned_qty'))
            ->groupBy('product_id', 'product_variation_id')
            ->get()
            ->keyBy(function ($row) {
                return $row->product_id . '_' . ($row->product_variation_id ?? '0');
            });

        $poItems = $po->items->map(function ($item) use ($previousReturns, $po, $companyId) {
            $receivedQty = $item->quantity_received ?? $item->quantity_ordered ?? $item->quantity;
            $retKey = $item->product_id . '_' . ($item->product_variation_id ?? '0');
            $alreadyReturned = isset($previousReturns[$retKey]) ? (int) $previousReturns[$retKey]->returned_qty : 0;
            $poLimit = max(0, $receivedQty - $alreadyReturned);

            $targetWhId = $item->warehouse_id ?: $po->warehouse_id;
            if (!$targetWhId && !empty($po->warehouse_ids) && is_array($po->warehouse_ids)) {
                $targetWhId = $po->warehouse_ids[0];
            }
            if (!$targetWhId) {
                $targetWhId = \App\Models\Warehouse::where('company_id', $companyId)->value('id') ?? 1;
            }

            // Physical available stock in warehouse
            $invQuery = Inventory::where('warehouse_id', $targetWhId)
                ->where('product_id', $item->product_id);

            if (!empty($item->product_variation_id)) {
                $invQuery->where('product_variation_id', $item->product_variation_id);
            } else {
                $invQuery->where(function ($q) {
                    $q->whereNull('product_variation_id')->orWhere('product_variation_id', 0);
                });
            }

            $invStock = $invQuery->value('stock_qty');

            if ($invStock === null) {
                if (!empty($item->product_variation_id)) {
                    $invStock = \App\Models\ProductVariation::where('id', $item->product_variation_id)->value('stock_qty') ?? 0;
                } else {
                    $invStock = $item->product?->stock_quantity ?? 0;
                }
            }
            $availableStock = max(0, (float) $invStock);
            $maxReturnable = max(0, min($poLimit, $availableStock));
            $maxAllowedQty = $maxReturnable;

            $allocations = [];
            if (!empty($item->warehouse_allocations) && is_array($item->warehouse_allocations)) {
                foreach ($item->warehouse_allocations as $alloc) {
                    $name = $alloc['warehouse_name'] ?? $alloc['name'] ?? null;
                    $qty = $alloc['quantity'] ?? $alloc['qty'] ?? 0;
                    if ($name) {
                        $allocations[] = [
                            'name'     => $name,
                            'quantity' => (int) $qty,
                        ];
                    }
                }
            } elseif ($item->warehouse) {
                $allocations[] = [
                    'name'     => $item->warehouse->name,
                    'quantity' => (int) ($item->quantity_ordered ?? $item->quantity ?? 0),
                ];
            } elseif ($po->warehouse) {
                $allocations[] = [
                    'name'     => $po->warehouse->name,
                    'quantity' => (int) ($item->quantity_ordered ?? $item->quantity ?? 0),
                ];
            }

            return [
                'product_id'            => $item->product_id,
                'product_variation_id'  => $item->product_variation_id ?? null,
                'product_name'          => $item->product?->name ?? 'Unknown Product',
                'product_sku'           => $item->product?->sku ?? '',
                'warehouse_id'          => $targetWhId,
                'warehouse_name'        => $item->warehouse?->name ?? $po->warehouse?->name ?? 'Main Warehouse',
                'unit_cost'             => (float) ($item->unit_cost ?? $item->unit_price ?? 0),
                'quantity_ordered'      => (int) ($item->quantity_ordered ?? $item->quantity),
                'quantity_received'     => (int) $receivedQty,
                'already_returned'      => (int) $alreadyReturned,
                'po_limit'              => (int) $poLimit,
                'max_returnable'        => (int) $maxReturnable,
                'available_stock'       => (int) $availableStock,
                'max_allowed_qty'       => (int) $maxAllowedQty,
                'tax_amount'            => (float) ($item->tax_amount ?? 0),
                'discount_amount'       => (float) ($item->discount_amount ?? 0),
                'warehouse_allocations' => $allocations,
            ];
        });

        return response()->json([
            'purchase_order' => $po,
            'items'          => $poItems,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'supplier_id'       => 'required|exists:suppliers,id',
            'warehouse_id'      => 'nullable|exists:warehouses,id',
            'return_date'       => 'required|date',
            'reason'            => 'nullable|string|max:255',
            'status'            => 'nullable|in:draft,pending,approved,completed',
            'refund_status'     => 'nullable|in:pending,partial,refunded',
            'payment_method'    => 'nullable|string',
            'bank_account_id'   => 'nullable',
            'reference_number'  => 'nullable|string',
            'amount_received'   => 'nullable|numeric',
            'refund_splits'     => 'nullable|array',
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
            $po = PurchaseOrder::with(['purchaseOrderItems', 'items'])->find($poId);

            if ($po) {
                $poItemsCollection = $po->purchaseOrderItems->isNotEmpty() ? $po->purchaseOrderItems : $po->items;
                $previousReturns = PurchaseReturnItem::whereHas('purchaseReturn', function ($q) use ($poId) {
                        $q->where('purchase_order_id', $poId)->whereIn('status', ['draft', 'pending', 'approved', 'completed']);
                    })
                    ->select('product_id', DB::raw('SUM(quantity) as returned_qty'))
                    ->groupBy('product_id')
                    ->pluck('returned_qty', 'product_id');

                foreach ($request->items as $item) {
                    $poItem = $poItemsCollection ? $poItemsCollection->firstWhere('product_id', $item['product_id']) : null;

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

                // Validate AP Credit amount against PO due amount
                $apCreditAmount = 0;
                if ($request->payment_method === 'mixed' && is_array($request->refund_splits)) {
                    foreach ($request->refund_splits as $split) {
                        if (($split['type'] ?? '') === 'ap_credit' || ($split['type'] ?? '') === 'ap') {
                            $apCreditAmount += (float) ($split['amount'] ?? 0);
                        }
                    }
                } elseif ($request->payment_method === 'ap_credit') {
                    // Calculate total from items since grandTotal isn't computed yet
                    foreach ($request->items as $item) {
                        $apCreditAmount += max(0, ((float)$item['quantity'] * (float)$item['unit_cost']) + (float)($item['tax_amount'] ?? 0) - (float)($item['discount_amount'] ?? 0));
                    }
                }

                $poDueAmount = (float) $po->due_amount;
                // Add a small epsilon to avoid floating point precision issues
                if ($apCreditAmount > ($poDueAmount + 0.01)) {
                    return response()->json([
                        'message' => 'AP Credit amount exceeds the outstanding PO due amount.',
                        'errors'  => ['payment_method' => ["AP Credit ({$apCreditAmount}) cannot be greater than outstanding Udhaar ({$poDueAmount})."]]
                    ], 422);
                }
            }
        }

        // Hard validation: Check physical available stock in warehouse
        foreach ($request->items as $item) {
            $whId = $item['warehouse_id'] ?? $request->warehouse_id ?? 1;
            $productId = $item['product_id'];
            $varId = $item['product_variation_id'] ?? null;
            $product = Product::find($productId);

            if ($product && $product->track_inventory) {
                $invStock = Inventory::where('warehouse_id', $whId)
                    ->where('product_id', $productId)
                    ->where('product_variation_id', $varId)
                    ->value('stock_qty');

                if ($invStock === null) {
                    $invStock = $product->stock_quantity ?? 0;
                }

                $availableStock = (int) $invStock;
                $reqQty = (float) $item['quantity'];

                if ($reqQty > $availableStock) {
                    $productName = $product->name ?? 'Product';
                    return response()->json([
                        'message' => "Insufficient physical warehouse stock for '{$productName}'.",
                        'errors'  => [
                            'items' => ["Cannot return {$reqQty} units of {$productName}. Only {$availableStock} units currently available in stock in selected warehouse."]
                        ]
                    ], 422);
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
                if ($lastReturn) {
                    $returnParts = explode('-', $lastReturn->return_number);
                    $lastSeq = (int) end($returnParts);
                } else {
                    $lastSeq = 0;
                }
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
                'reason'            => $request->input('reason') ?: ($request->input('notes') ?: 'Defective / Return items'),
                'subtotal'          => $subtotal,
                'tax_amount'        => $totalTax,
                'discount_amount'   => $totalDiscount,
                'total_amount'      => $grandTotal,
                'status'            => $status,
                'refund_status'     => $request->refund_status ?? 'pending',
                'payment_method'    => $request->payment_method ?? 'cash',
                'bank_account_id'   => is_numeric($request->bank_account_id) ? (int)$request->bank_account_id : null,
                'reference_number'  => $request->reference_number,
                'refund_splits'     => $request->refund_splits,
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
                    'product_variation_id' => $item['product_variation_id'] ?? null,
                    'warehouse_id'    => $item['warehouse_id'] ?? $request->warehouse_id,
                    'quantity'        => $qty,
                    'unit_cost'       => $unitCost,
                    'tax_amount'      => $itemTax,
                    'discount_amount' => $itemDiscount,
                    'subtotal'        => $itemSubtotal,
                    'total_cost'      => $itemTotalCost,
                    'notes'           => $item['notes'] ?? null,
                ]);
            }

            $purchaseReturn->update([
                'subtotal'       => $subtotal,
                'tax_amount'     => $totalTax,
                'discount_amount'=> $totalDiscount,
                'total_amount'   => $grandTotal,
            ]);

            // Auto deduct inventory and post GL accounting entry if status is approved/completed
            if (in_array($status, ['approved', 'completed'])) {
                $this->processStockDeductionAndAccounting($purchaseReturn->fresh());
            }

            DB::commit();

            $purchaseReturn->load(['supplier', 'warehouse', 'items.product']);

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
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        $companyId = auth()->user()?->current_company_id ?? 1;
        $purchaseReturn = PurchaseReturn::where('company_id', $companyId)
            ->with(['supplier', 'warehouse', 'originalPurchaseOrder', 'user', 'items.product'])
            ->findOrFail($id);

        return response()->json([
            'purchase_return' => $purchaseReturn
        ]);
    }

    /**
     * Update the specified resource in storage.
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
            'reason'            => 'nullable|string|max:255',
            'status'            => 'nullable|in:draft,pending,approved,completed,cancelled',
            'refund_status'     => 'nullable|in:pending,partial,refunded',
            'payment_method'    => 'nullable|string',
            'bank_account_id'   => 'nullable',
            'reference_number'  => 'nullable|string',
            'amount_received'   => 'nullable|numeric',
            'refund_splits'     => 'nullable|array',
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

        $newPoId = $request->input('purchase_order_id', $purchaseReturn->purchase_order_id);
        $wasApproved = in_array($purchaseReturn->status, ['approved', 'completed']);
        $oldItemsMap = $purchaseReturn->items->keyBy(function ($i) {
            return $i->product_id . '_' . ($i->warehouse_id ?? 1) . '_' . ($i->product_variation_id ?? 0);
        });

        if ($request->has('items')) {
            if ($newPoId) {
                $po = PurchaseOrder::with(['purchaseOrderItems', 'items'])->find($newPoId);
                if ($po) {
                    $poItemsCollection = $po->purchaseOrderItems->isNotEmpty() ? $po->purchaseOrderItems : $po->items;
                    $previousReturns = PurchaseReturnItem::whereHas('purchaseReturn', function ($q) use ($newPoId, $id) {
                            $q->where('purchase_order_id', $newPoId)
                              ->where('purchase_returns.id', '!=', $id)
                              ->whereIn('status', ['draft', 'pending', 'approved', 'completed']);
                        })
                        ->select('product_id', DB::raw('SUM(quantity) as returned_qty'))
                        ->groupBy('product_id')
                        ->pluck('returned_qty', 'product_id');

                    foreach ($request->items as $item) {
                        $poItem = $poItemsCollection ? $poItemsCollection->firstWhere('product_id', $item['product_id']) : null;
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

            // Physical warehouse stock validation
            foreach ($request->items as $item) {
                $whId = $item['warehouse_id'] ?? $request->warehouse_id ?? $purchaseReturn->warehouse_id ?? 1;
                $productId = $item['product_id'];
                $varId = $item['product_variation_id'] ?? null;
                $product = Product::find($productId);

                if ($product && $product->track_inventory) {
                    $invStock = Inventory::where('warehouse_id', $whId)
                        ->where('product_id', $productId)
                        ->where('product_variation_id', $varId)
                        ->value('stock_qty');

                    if ($invStock === null) {
                        $invStock = $product->stock_quantity ?? 0;
                    }

                    $availableStock = (int) $invStock;
                    if ($wasApproved) {
                        $key = $productId . '_' . $whId . '_' . ($varId ?? 0);
                        if (isset($oldItemsMap[$key])) {
                            $availableStock += (int) $oldItemsMap[$key]->quantity;
                        }
                    }

                    $reqQty = (float) $item['quantity'];
                    if ($reqQty > $availableStock) {
                        $productName = $product->name ?? 'Product';
                        return response()->json([
                            'message' => "Insufficient physical warehouse stock for '{$productName}'.",
                            'errors'  => [
                                'items' => ["Cannot return {$reqQty} units of {$productName}. Only {$availableStock} units currently available in stock in selected warehouse."]
                            ]
                        ], 422);
                    }
                }
            }
        }

        try {
            DB::beginTransaction();

            $oldStatus = $purchaseReturn->status;
            $newStatus = $request->status ?? $oldStatus;
            $wasApproved = in_array($oldStatus, ['approved', 'completed']);

            $inventoryService = new WarehouseInventoryService();
            $accountingService = new DoubleEntryAccountingService();
            $warehouseId = $purchaseReturn->warehouse_id ?? 1;

            // Step A: If return was previously approved/completed, reverse old stock deduction & old GL journal entry
            if ($wasApproved) {
                foreach ($purchaseReturn->items as $oldItem) {
                    $product = $oldItem->product ?: Product::find($oldItem->product_id);
                    if ($product && $product->track_inventory) {
                        $inventoryService->adjustStock(
                            $oldItem->warehouse_id ?? $warehouseId,
                            $product->id,
                            $oldItem->product_variation_id ?? null,
                            $oldItem->quantity, // Reverse previous deduction by adding stock back
                            $companyId,
                            'Purchase Return Reversal',
                            $purchaseReturn->return_number
                        );
                    }
                }
                
                // Reverse Vendor Store Credit if it was issued
                if (is_array($purchaseReturn->refund_splits)) {
                    foreach ($purchaseReturn->refund_splits as $split) {
                        if (($split['type'] ?? '') === 'vendor_credit') {
                            $amt = (float)($split['amount'] ?? 0);
                            if ($amt > 0 && $purchaseReturn->supplier) {
                                $purchaseReturn->supplier->debitAdvance($amt);
                            }
                        }
                    }
                }

                $accountingService->reverseJournalEntryBySource('purchase_return', $purchaseReturn->id);
            }

            if ($request->has('bank_account_id')) {
                $purchaseReturn->bank_account_id = is_numeric($request->bank_account_id) ? (int)$request->bank_account_id : null;
            }
            if ($request->has('reason')) {
                $purchaseReturn->reason = $request->input('reason') ?: ($request->input('notes') ?: 'Defective / Return items');
            }

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
                        'product_variation_id' => $item['product_variation_id'] ?? null,
                        'warehouse_id'    => $item['warehouse_id'] ?? $request->warehouse_id ?? $purchaseReturn->warehouse_id,
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

            // Step B & C: Apply new stock deduction & re-post GL journal entry if new status is approved or completed
            if (in_array($newStatus, ['approved', 'completed'])) {
                $this->processStockDeductionAndAccounting($purchaseReturn->fresh());
            }

            DB::commit();

            $purchaseReturn->load(['supplier', 'originalPurchaseOrder', 'warehouse', 'items.product', 'user']);

            return response()->json([
                'message'         => 'Purchase return updated, inventory re-calculated, and GL journal re-posted successfully',
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
    
    /**
     * Quick status update endpoint
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $companyId = auth()->user()?->current_company_id ?? 1;
        $purchaseReturn = PurchaseReturn::where('company_id', $companyId)->findOrFail($id);

        $request->validate([
            'status' => 'required|in:draft,pending,approved,completed,cancelled'
        ]);

        $newStatus = $request->status;

        // Redirect to existing complex logic if approving or rejecting
        if ($newStatus === 'approved' && !in_array($purchaseReturn->status, ['approved', 'completed'])) {
            return $this->approve($id);
        }

        if ($newStatus === 'cancelled' && $purchaseReturn->status !== 'cancelled') {
            return $this->reject($id);
        }

        if ($newStatus === 'completed') {
            if (!in_array($purchaseReturn->status, ['approved'])) {
                return response()->json([
                    'message' => 'Return must be approved before it can be marked as completed.'
                ], 400);
            }
            $purchaseReturn->status = 'completed';
            $purchaseReturn->save();

            return response()->json([
                'message' => 'Purchase return marked as completed',
                'purchase_return' => $purchaseReturn
            ]);
        }

        $purchaseReturn->status = $newStatus;
        $purchaseReturn->save();

        return response()->json([
            'message' => 'Status updated successfully',
            'purchase_return' => $purchaseReturn
        ]);
    }

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

        try {
            DB::beginTransaction();

            if ($purchaseReturn->status === 'approved') {
                $inventoryService = new WarehouseInventoryService();
                $accountingService = new DoubleEntryAccountingService();
                $warehouseId = $purchaseReturn->warehouse_id ?? 1;

                foreach ($purchaseReturn->items as $item) {
                    $product = $item->product ?: Product::find($item->product_id);
                    if ($product && $product->track_inventory) {
                        $inventoryService->adjustStock(
                            $item->warehouse_id ?? $warehouseId,
                            $product->id,
                            $item->product_variation_id ?? null,
                            $item->quantity, // Add back stock
                            $companyId,
                            'Purchase Return Cancelled',
                            $purchaseReturn->return_number
                        );
                    }
                }

                // Reverse Vendor Store Credit if it was issued
                if (is_array($purchaseReturn->refund_splits)) {
                    foreach ($purchaseReturn->refund_splits as $split) {
                        if (($split['type'] ?? '') === 'vendor_credit') {
                            $amt = (float)($split['amount'] ?? 0);
                            if ($amt > 0 && $purchaseReturn->supplier) {
                                $purchaseReturn->supplier->debitAdvance($amt);
                            }
                        }
                    }
                }

                $accountingService->reverseJournalEntryBySource('purchase_return', $purchaseReturn->id);
            }

            $purchaseReturn->update(['status' => 'rejected']);

            DB::commit();

            return response()->json([
                'message'         => 'Purchase return cancelled successfully and inventory/GL entries reversed',
                'purchase_return' => $purchaseReturn
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to cancel purchase return',
                'error'   => $e->getMessage()
            ], 500);
        }
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
                    $item->product_variation_id ?? null,
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
