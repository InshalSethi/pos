<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryAdjustment;
use App\Models\Product;
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

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = InventoryAdjustment::with(['product', 'user']);

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
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'adjustment_type' => 'required|in:increase,decrease,recount',
            'quantity_adjusted' => 'required|integer',
            'reason' => 'required|string|max:255',
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

            $product = Product::find($request->product_id);
            $quantityBefore = $product->stock_quantity;
            $quantityAdjusted = $request->quantity_adjusted;

            // Calculate new quantity based on adjustment type
            switch ($request->adjustment_type) {
                case 'increase':
                    $quantityAfter = $quantityBefore + $quantityAdjusted;
                    break;
                case 'decrease':
                    $quantityAfter = max(0, $quantityBefore - $quantityAdjusted);
                    $quantityAdjusted = $quantityBefore - $quantityAfter; // Actual adjustment
                    break;
                case 'recount':
                    $quantityAfter = $quantityAdjusted;
                    $quantityAdjusted = $quantityAfter - $quantityBefore; // Difference
                    break;
            }

            // Generate adjustment number
            $adjustmentNumber = 'ADJ-' . date('Ymd') . '-' . str_pad(InventoryAdjustment::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Calculate cost impact
            $costImpact = $quantityAdjusted * $product->cost_price;

            // Create adjustment record
            $adjustment = InventoryAdjustment::create([
                'adjustment_number' => $adjustmentNumber,
                'product_id' => $request->product_id,
                'adjustment_type' => $request->adjustment_type,
                'quantity_before' => $quantityBefore,
                'quantity_adjusted' => $quantityAdjusted,
                'quantity_after' => $quantityAfter,
                'reason' => $request->reason,
                'user_id' => auth()->id(),
                'adjustment_date' => now(),
                'cost_impact' => $costImpact,
                'notes' => $request->notes,
            ]);

            // Update product stock
            $product->update(['stock_quantity' => $quantityAfter]);

            DB::commit();

            $adjustment->load(['product', 'user']);

            return response()->json([
                'message' => 'Inventory adjustment created successfully',
                'adjustment' => $adjustment
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

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
        $inventoryAdjustment->load(['product', 'user']);

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
}
