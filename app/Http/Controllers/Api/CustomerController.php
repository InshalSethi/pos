<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->has('is_active') && $request->get('is_active') !== '' && $request->get('is_active') !== null) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $customers = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return response()->json($customers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'tax_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $customer = Customer::create($request->all());

        return response()->json([
            'message' => 'Customer created successfully',
            'customer' => $customer
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer): JsonResponse
    {
        $customer->load('sales');

        return response()->json($customer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'tax_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $customer->update($request->all());

        return response()->json([
            'message' => 'Customer updated successfully',
            'customer' => $customer
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer): JsonResponse
    {
        // Check if customer has sales
        if ($customer->sales()->exists()) {
            return response()->json([
                'message' => 'Cannot delete customer with existing sales'
            ], 422);
        }

        $customer->delete();

        return response()->json([
            'message' => 'Customer deleted successfully'
        ]);
    }

    /**
     * Quick search for customers (for dropdowns, etc.)
     */
    public function quickSearch(Request $request): JsonResponse
    {
        $search = $request->get('search', '');
        $limit = $request->get('limit', 10);

        $customers = Customer::active()
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
            })
            ->select('id', 'name', 'email', 'phone', 'total_purchases')
            ->limit($limit)
            ->get();

        return response()->json($customers);
    }

    /**
     * Get customer statistics
     */
    public function getStatistics(): JsonResponse
    {
        $stats = [
            'total_customers' => Customer::count(),
            'active_customers' => Customer::active()->count(),
            'customers_with_purchases' => Customer::whereHas('sales')->count(),
            'total_customer_value' => Customer::sum('total_purchases'),
            'average_customer_value' => Customer::avg('total_purchases'),
            'top_customers' => Customer::orderBy('total_purchases', 'desc')
                                    ->limit(5)
                                    ->select('id', 'name', 'total_purchases')
                                    ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Get customer purchase history
     */
    public function getPurchaseHistory(Customer $customer): JsonResponse
    {
        $sales = $customer->sales()
                         ->with(['user', 'saleItems.product'])
                         ->orderBy('sale_date', 'desc')
                         ->paginate(15);

        return response()->json($sales);
    }

    /**
     * Update customer credit limit
     */
    public function updateCreditLimit(Request $request, Customer $customer): JsonResponse
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

        $oldLimit = $customer->credit_limit;
        $customer->update(['credit_limit' => $request->credit_limit]);

        // Log the credit limit change (you could create a separate model for this)
        \Log::info("Customer credit limit updated", [
            'customer_id' => $customer->id,
            'old_limit' => $oldLimit,
            'new_limit' => $request->credit_limit,
            'reason' => $request->reason,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Credit limit updated successfully',
            'customer' => $customer
        ]);
    }

    /**
     * Add loyalty points to customer
     */
    public function addLoyaltyPoints(Request $request, Customer $customer): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'points' => 'required|numeric',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $customer->increment('loyalty_points', $request->points);

        return response()->json([
            'message' => 'Loyalty points added successfully',
            'customer' => $customer->fresh()
        ]);
    }

    /**
     * Deactivate customer
     */
    public function deactivate(Customer $customer): JsonResponse
    {
        $customer->update(['is_active' => false]);

        return response()->json([
            'message' => 'Customer deactivated successfully',
            'customer' => $customer
        ]);
    }

    /**
     * Reactivate customer
     */
    public function reactivate(Customer $customer): JsonResponse
    {
        $customer->update(['is_active' => true]);

        return response()->json([
            'message' => 'Customer reactivated successfully',
            'customer' => $customer
        ]);
    }


}
