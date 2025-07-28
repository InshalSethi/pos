<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:inventory.view')->only(['index', 'show']);
        $this->middleware('permission:inventory.create')->only(['store']);
        $this->middleware('permission:inventory.edit')->only(['update']);
        $this->middleware('permission:inventory.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Supplier::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->has('is_active') && $request->get('is_active') !== '' && $request->get('is_active') !== null) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $suppliers = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return response()->json($suppliers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:suppliers,email',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'website' => 'nullable|url',
            'notes' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $supplier = Supplier::create($request->all());

        return response()->json([
            'message' => 'Supplier created successfully',
            'supplier' => $supplier
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier): JsonResponse
    {
        $supplier->load('purchaseOrders');

        return response()->json($supplier);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:suppliers,email,' . $supplier->id,
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'website' => 'nullable|url',
            'notes' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $supplier->update($request->all());

        return response()->json([
            'message' => 'Supplier updated successfully',
            'supplier' => $supplier
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier): JsonResponse
    {
        // Check if supplier has purchase orders
        if ($supplier->purchaseOrders()->exists()) {
            return response()->json([
                'message' => 'Cannot delete supplier with existing purchase orders'
            ], 422);
        }

        $supplier->delete();

        return response()->json([
            'message' => 'Supplier deleted successfully'
        ]);
    }

    /**
     * Quick search for suppliers (for dropdowns, etc.)
     */
    public function quickSearch(Request $request): JsonResponse
    {
        $search = $request->get('search', '');
        $limit = $request->get('limit', 10);

        $suppliers = Supplier::active()
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
            })
            ->select('id', 'name', 'company_name', 'email', 'phone', 'payment_terms_days')
            ->limit($limit)
            ->get();

        return response()->json($suppliers);
    }

    /**
     * Get supplier statistics
     */
    public function getStatistics(): JsonResponse
    {
        try {
            // Basic counts
            $totalSuppliers = Supplier::count();
            $activeSuppliers = Supplier::where('is_active', true)->count();
            $suppliersWithOrders = Supplier::whereHas('purchaseOrders')->count();

            // Calculate total supplier value more reliably
            $totalSupplierValue = DB::table('purchase_orders')
                ->join('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
                ->sum('purchase_orders.total_amount') ?? 0;

            // Average payment terms
            $averagePaymentTerms = Supplier::where('is_active', true)
                ->whereNotNull('payment_terms_days')
                ->avg('payment_terms_days') ?? 30;

            // Top suppliers by purchase value
            $topSuppliers = DB::table('suppliers')
                ->leftJoin('purchase_orders', 'suppliers.id', '=', 'purchase_orders.supplier_id')
                ->select(
                    'suppliers.id',
                    'suppliers.name',
                    'suppliers.company_name',
                    DB::raw('COALESCE(SUM(purchase_orders.total_amount), 0) as total_value')
                )
                ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.company_name')
                ->orderBy('total_value', 'desc')
                ->limit(5)
                ->get();

            $stats = [
                'total_suppliers' => $totalSuppliers,
                'active_suppliers' => $activeSuppliers,
                'suppliers_with_orders' => $suppliersWithOrders,
                'total_supplier_value' => round($totalSupplierValue, 2),
                'average_payment_terms' => round($averagePaymentTerms, 1),
                'top_suppliers' => $topSuppliers,
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Error calculating supplier statistics: ' . $e->getMessage());

            // Return default values if there's an error
            return response()->json([
                'total_suppliers' => 0,
                'active_suppliers' => 0,
                'suppliers_with_orders' => 0,
                'total_supplier_value' => 0,
                'average_payment_terms' => 30,
                'top_suppliers' => [],
            ]);
        }
    }

    /**
     * Get supplier purchase history
     */
    public function getPurchaseHistory(Supplier $supplier): JsonResponse
    {
        $purchaseOrders = $supplier->purchaseOrders()
                                  ->with(['user', 'purchaseOrderItems.product'])
                                  ->orderBy('order_date', 'desc')
                                  ->paginate(15);

        return response()->json($purchaseOrders);
    }

    /**
     * Update supplier credit limit
     */
    public function updateCreditLimit(Request $request, Supplier $supplier): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'credit_limit' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldLimit = $supplier->credit_limit;
        $supplier->update(['credit_limit' => $request->credit_limit]);

        // Log the credit limit change
        Log::info("Supplier credit limit updated", [
            'supplier_id' => $supplier->id,
            'old_limit' => $oldLimit,
            'new_limit' => $request->credit_limit,
            'reason' => $request->reason,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Credit limit updated successfully',
            'supplier' => $supplier
        ]);
    }

    /**
     * Update supplier payment terms
     */
    public function updatePaymentTerms(Request $request, Supplier $supplier): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_terms_days' => 'required|integer|min:0|max:365',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldTerms = $supplier->payment_terms_days;
        $supplier->update(['payment_terms_days' => $request->payment_terms_days]);

        // Log the payment terms change
        Log::info("Supplier payment terms updated", [
            'supplier_id' => $supplier->id,
            'old_terms' => $oldTerms,
            'new_terms' => $request->payment_terms_days,
            'reason' => $request->reason,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Payment terms updated successfully',
            'supplier' => $supplier
        ]);
    }

    /**
     * Deactivate supplier
     */
    public function deactivate(Supplier $supplier): JsonResponse
    {
        $supplier->update(['is_active' => false]);

        return response()->json([
            'message' => 'Supplier deactivated successfully',
            'supplier' => $supplier
        ]);
    }

    /**
     * Reactivate supplier
     */
    public function reactivate(Supplier $supplier): JsonResponse
    {
        $supplier->update(['is_active' => true]);

        return response()->json([
            'message' => 'Supplier reactivated successfully',
            'supplier' => $supplier
        ]);
    }


}
