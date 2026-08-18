<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query()->select('customers.*');

        $query->addSelect([
            'due_amount' => \App\Models\Sale::selectRaw('COALESCE(SUM(total_amount - paid_amount), 0)')
                ->whereColumn('customer_id', 'customers.id')
                ->where('status', 'pending')
        ]);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('state', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->has('is_active') && $request->get('is_active') !== '' && $request->get('is_active') !== null) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by customer type (registered vs walk_in)
        if ($request->has('type') && $request->get('type') !== '' && $request->get('type') !== null) {
            $query->where('type', $request->get('type'));
        }

        $customers = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return response()->json($customers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('customers', 'email')->where('company_id', $companyId),
            ],
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:50',
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
            'type' => 'nullable|in:registered,walk_in',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:5120',
            'existing_attachments' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except(['profile_image', 'attachments', 'existing_attachments']);
        $data['type'] = $request->input('type') ?? ($request->input('source') === 'modal' ? 'registered' : 'registered');

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = Storage::disk('public')->put('customers/avatars', $request->file('profile_image'));
        }

        $uploadedAttachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $uploadedAttachments[] = Storage::disk('public')->put('customers/attachments', $file);
            }
        }
        $data['attachments'] = $uploadedAttachments;

        $customer = Customer::create($data);

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
        $companyId = auth()->user()->current_company_id;
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('customers', 'email')->ignore($customer->id)->where('company_id', $companyId),
            ],
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:50',
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
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:5120',
            'existing_attachments' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except(['profile_image', 'attachments', 'existing_attachments']);

        if ($request->hasFile('profile_image')) {
            if ($customer->profile_image) {
                Storage::disk('public')->delete($customer->profile_image);
            }
            $data['profile_image'] = Storage::disk('public')->put('customers/avatars', $request->file('profile_image'));
        }

        $currentAttachments = $customer->attachments ?? [];
        if (!is_array($currentAttachments)) {
            $currentAttachments = json_decode($currentAttachments, true) ?? [];
        }

        $retainedAttachments = [];
        if ($request->has('existing_attachments')) {
            $rawExisting = $request->input('existing_attachments');
            if (is_string($rawExisting)) {
                $rawExisting = json_decode($rawExisting, true) ?? [];
            }
            $existing = (array)$rawExisting;
            foreach ($existing as $item) {
                $path = is_array($item) ? ($item['path'] ?? '') : (string)$item;
                if ($path && in_array($path, $currentAttachments)) {
                    $retainedAttachments[] = $path;
                }
            }
        }

        $newAttachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $newAttachments[] = Storage::disk('public')->put('customers/attachments', $file);
            }
        }

        $data['attachments'] = array_values(array_unique(array_merge($retainedAttachments, $newAttachments)));

        $customer->update($data);

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
        $type = $request->get('type', 'registered');

        $query = Customer::active();

        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        $customers = $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        })
            ->select('id', 'name', 'email', 'phone', 'total_purchases', 'type')
            ->limit($limit)
            ->get();

        return response()->json($customers);
    }

    /**
     * Get customer statistics
     */
    public function getStatistics(): JsonResponse
    {
        $total = Customer::count();
        $active = Customer::active()->count();

        $stats = [
            'total_customers' => $total,
            'active_customers' => $active,
            'inactive_customers' => $total - $active,
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
