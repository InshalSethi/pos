<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransferOrder;
use App\Models\TransferOrderItem;
use App\Models\Inventory;
use App\Models\Product;
use App\Services\WarehouseInventoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransferOrderController extends Controller
{
    protected $inventoryService;

    public function __construct(WarehouseInventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
        $this->middleware('permission:inventory.view')->only(['index', 'show']);
        $this->middleware('permission:inventory.edit')->only(['store', 'update', 'destroy', 'send', 'receive', 'cancel']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;
        $query = TransferOrder::with(['sourceWarehouse', 'destinationWarehouse', 'user', 'items.product', 'items.variation'])
            ->where('company_id', $companyId);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('transfer_number', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $transfers = $query->orderBy('transfer_date', 'desc')->get();

        return response()->json($transfers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        $validator = Validator::make($request->all(), [
            'source_warehouse_id' => 'required|exists:warehouses,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id|different:source_warehouse_id',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variation_id' => 'nullable|exists:product_variations,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Generate transfer number
            $transferNumber = 'TO-' . date('Ymd') . '-' . str_pad(TransferOrder::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            $transferOrder = TransferOrder::create([
                'company_id' => $companyId,
                'transfer_number' => $transferNumber,
                'source_warehouse_id' => $request->source_warehouse_id,
                'destination_warehouse_id' => $request->destination_warehouse_id,
                'transfer_date' => $request->transfer_date,
                'status' => 'draft',
                'notes' => $request->notes,
                'user_id' => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                TransferOrderItem::create([
                    'transfer_order_id' => $transferOrder->id,
                    'product_id' => $item['product_id'],
                    'product_variation_id' => $item['product_variation_id'] ?? null,
                    'quantity' => $item['quantity'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Transfer order created successfully as draft',
                'transfer_order' => $transferOrder->load(['sourceWarehouse', 'destinationWarehouse', 'items.product'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create transfer order: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TransferOrder $transferOrder): JsonResponse
    {
        $transferOrder->load(['sourceWarehouse', 'destinationWarehouse', 'user', 'items.product', 'items.variation']);
        return response()->json($transferOrder);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TransferOrder $transferOrder): JsonResponse
    {
        if ($transferOrder->status !== 'draft') {
            return response()->json(['message' => 'Only draft transfer orders can be updated.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'source_warehouse_id' => 'required|exists:warehouses,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id|different:source_warehouse_id',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variation_id' => 'nullable|exists:product_variations,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $transferOrder->update([
                'source_warehouse_id' => $request->source_warehouse_id,
                'destination_warehouse_id' => $request->destination_warehouse_id,
                'transfer_date' => $request->transfer_date,
                'notes' => $request->notes,
            ]);

            // Recreate items
            $transferOrder->items()->delete();

            foreach ($request->items as $item) {
                TransferOrderItem::create([
                    'transfer_order_id' => $transferOrder->id,
                    'product_id' => $item['product_id'],
                    'product_variation_id' => $item['product_variation_id'] ?? null,
                    'quantity' => $item['quantity'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Transfer order updated successfully',
                'transfer_order' => $transferOrder->load(['sourceWarehouse', 'destinationWarehouse', 'items.product'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update transfer order: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TransferOrder $transferOrder): JsonResponse
    {
        if ($transferOrder->status !== 'draft') {
            return response()->json(['message' => 'Only draft transfer orders can be deleted.'], 422);
        }

        $transferOrder->delete();

        return response()->json(['message' => 'Transfer order deleted successfully.']);
    }

    /**
     * Mark transfer order as Sent (deducts stock from source warehouse).
     */
    public function send(TransferOrder $transferOrder): JsonResponse
    {
        if ($transferOrder->status !== 'draft') {
            return response()->json(['message' => 'Transfer order must be in draft status to send.'], 422);
        }

        DB::beginTransaction();
        try {
            // Check stock availability in source warehouse
            foreach ($transferOrder->items as $item) {
                $inventory = Inventory::where('warehouse_id', $transferOrder->source_warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->where('product_variation_id', $item->product_variation_id)
                    ->first();

                $availableStock = $inventory ? $inventory->stock_qty : 0;

                if ($availableStock < $item->quantity) {
                    $prodName = $item->product->name;
                    if ($item->variation) {
                        $prodName .= ' (' . $item->variation->variation_name_string . ')';
                    }
                    throw new \Exception("Insufficient stock for product '{$prodName}' in source warehouse. Available: {$availableStock}, Required: {$item->quantity}");
                }
            }

            // Deduct stock from source warehouse
            foreach ($transferOrder->items as $item) {
                $this->inventoryService->adjustStock(
                    $transferOrder->source_warehouse_id,
                    $item->product_id,
                    $item->product_variation_id,
                    -$item->quantity,
                    $transferOrder->company_id,
                    'Transfer Order',
                    $transferOrder->transfer_number
                );
            }

            $transferOrder->update(['status' => 'sent']);

            DB::commit();

            return response()->json([
                'message' => 'Transfer order marked as Sent. Stock has been deducted from source warehouse.',
                'transfer_order' => $transferOrder->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Mark transfer order as Received (adds stock to destination warehouse).
     */
    public function receive(TransferOrder $transferOrder): JsonResponse
    {
        if ($transferOrder->status !== 'sent') {
            return response()->json(['message' => 'Transfer order must be in Sent status to receive.'], 422);
        }

        DB::beginTransaction();
        try {
            // Add stock to destination warehouse
            foreach ($transferOrder->items as $item) {
                $this->inventoryService->adjustStock(
                    $transferOrder->destination_warehouse_id,
                    $item->product_id,
                    $item->product_variation_id,
                    $item->quantity,
                    $transferOrder->company_id,
                    'Transfer Order',
                    $transferOrder->transfer_number
                );
            }

            $transferOrder->update(['status' => 'received']);

            DB::commit();

            return response()->json([
                'message' => 'Transfer order marked as Received. Stock has been added to destination warehouse.',
                'transfer_order' => $transferOrder->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to receive stock: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cancel transfer order (refunds stock to source warehouse if status was sent).
     */
    public function cancel(TransferOrder $transferOrder): JsonResponse
    {
        if (in_array($transferOrder->status, ['received', 'cancelled'])) {
            return response()->json(['message' => 'Cannot cancel a received or already cancelled transfer order.'], 422);
        }

        DB::beginTransaction();
        try {
            // If it was already sent, we return the stock to source warehouse
            if ($transferOrder->status === 'sent') {
                foreach ($transferOrder->items as $item) {
                    $this->inventoryService->adjustStock(
                        $transferOrder->source_warehouse_id,
                        $item->product_id,
                        $item->product_variation_id,
                        $item->quantity,
                        $transferOrder->company_id,
                        'Transfer Order',
                        $transferOrder->transfer_number
                    );
                }
            }

            $transferOrder->update(['status' => 'cancelled']);

            DB::commit();

            return response()->json([
                'message' => 'Transfer order cancelled successfully.',
                'transfer_order' => $transferOrder->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to cancel transfer order: ' . $e->getMessage()], 500);
        }
    }
}
