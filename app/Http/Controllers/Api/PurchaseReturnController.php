<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseReturn;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseReturn::with(['supplier', 'originalPurchaseOrder']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by supplier
        if ($request->has('supplier_id') && $request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('return_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('return_date', '<=', $request->date_to);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $returns = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($returns);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'return_date' => 'required|date',
            'reason' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
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

            // Generate return number
            $returnNumber = 'PR-' . date('Ymd') . '-' . str_pad(PurchaseReturn::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Calculate total amount
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['unit_cost'];
            }

            // Create purchase return
            $purchaseReturn = PurchaseReturn::create([
                'return_number' => $returnNumber,
                'purchase_order_id' => $request->purchase_order_id,
                'supplier_id' => $request->supplier_id,
                'user_id' => auth()->id(),
                'return_date' => $request->return_date,
                'reason' => $request->reason,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            // Create return items
            foreach ($request->items as $item) {
                $purchaseReturn->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['quantity'] * $item['unit_cost'],
                ]);
            }

            DB::commit();

            $purchaseReturn->load(['supplier', 'originalPurchaseOrder', 'items.product']);

            return response()->json([
                'message' => 'Purchase return created successfully',
                'purchase_return' => $purchaseReturn
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create purchase return',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseReturn $purchaseReturn): JsonResponse
    {
        $purchaseReturn->load(['supplier', 'originalPurchaseOrder', 'items.product', 'user']);

        return response()->json($purchaseReturn);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseReturn $purchaseReturn): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'return_date' => 'sometimes|date',
            'reason' => 'sometimes|string',
            'status' => 'sometimes|in:pending,approved,rejected,processed',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $purchaseReturn->update($request->only([
            'return_date', 'reason', 'status', 'notes'
        ]));

        $purchaseReturn->load(['supplier', 'originalPurchaseOrder', 'items.product']);

        return response()->json([
            'message' => 'Purchase return updated successfully',
            'purchase_return' => $purchaseReturn
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseReturn $purchaseReturn): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Delete return items
            $purchaseReturn->items()->delete();

            // Delete the return
            $purchaseReturn->delete();

            DB::commit();

            return response()->json([
                'message' => 'Purchase return deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to delete purchase return',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve purchase return
     */
    public function approve(PurchaseReturn $purchaseReturn): JsonResponse
    {
        if ($purchaseReturn->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending returns can be approved'
            ], 400);
        }

        $purchaseReturn->update(['status' => 'approved']);

        return response()->json([
            'message' => 'Purchase return approved successfully',
            'purchase_return' => $purchaseReturn
        ]);
    }

    /**
     * Reject purchase return
     */
    public function reject(PurchaseReturn $purchaseReturn): JsonResponse
    {
        if ($purchaseReturn->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending returns can be rejected'
            ], 400);
        }

        $purchaseReturn->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Purchase return rejected successfully',
            'purchase_return' => $purchaseReturn
        ]);
    }
}
