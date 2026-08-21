<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class AdminCouponController extends Controller
{
    /**
     * Display a listing of the coupons.
     */
    public function index(Request $request)
    {
        $query = Coupon::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== '' && $request->status !== 'all') {
            $query->where('is_active', filter_var($request->status, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $allowedSortColumns = ['code', 'name', 'type', 'value', 'used_count', 'expires_at', 'created_at'];
        $sortBy = in_array($request->get('sort_by'), $allowedSortColumns) ? $request->get('sort_by') : 'created_at';
        $sortDir = strtolower($request->get('sort_dir')) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortDir)->orderBy('id', 'desc');

        $perPage = max(1, min(100, (int) $request->get('per_page', 15)));
        $coupons = $query->paginate($perPage);

        return response()->json($coupons);
    }

    /**
     * Store a newly created coupon.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'                => 'required|string|max:50|unique:coupons,code',
            'name'                => 'nullable|string|max:255',
            'type'                => 'required|in:percentage,fixed',
            'value'               => 'required|numeric|min:0.01',
            'min_order_amount'    => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit'         => 'nullable|integer|min:1',
            'starts_at'           => 'nullable|date',
            'expires_at'          => 'required|date',
            'is_active'           => 'boolean',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        if (!empty($validated['expires_at'])) {
            $validated['expires_at'] = \Carbon\Carbon::parse($validated['expires_at'])->endOfDay();
        }

        $coupon = Coupon::create($validated);

        return response()->json([
            'message' => 'Coupon created successfully.',
            'data'    => $coupon,
        ], 201);
    }

    /**
     * Display the specified coupon.
     */
    public function show(Coupon $coupon)
    {
        return response()->json([
            'data' => $coupon
        ]);
    }

    /**
     * Update the specified coupon.
     */
    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code'                => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'name'                => 'nullable|string|max:255',
            'type'                => 'required|in:percentage,fixed',
            'value'               => 'required|numeric|min:0.01',
            'min_order_amount'    => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit'         => 'nullable|integer|min:1',
            'starts_at'           => 'nullable|date',
            'expires_at'          => 'required|date',
            'is_active'           => 'boolean',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        if (!empty($validated['expires_at'])) {
            $validated['expires_at'] = \Carbon\Carbon::parse($validated['expires_at'])->endOfDay();
        }

        $coupon->update($validated);

        return response()->json([
            'message' => 'Coupon updated successfully.',
            'data'    => $coupon,
        ]);
    }

    /**
     * Remove the specified coupon.
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return response()->json([
            'message' => 'Coupon deleted successfully.'
        ]);
    }

    /**
     * Validate a coupon code for a given amount / plan & cycle.
     */
    public function validateCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'amount' => 'nullable|numeric|min:0',
            'plan' => 'nullable|string',
            'billing_cycle' => 'nullable|string',
        ]);

        $code = strtoupper(trim($request->code));
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid coupon code.'
            ], 422);
        }

        $originalAmount = (float) ($request->amount ?? 0);

        // If plan is provided but amount is missing or zero, resolve plan price
        if ($originalAmount <= 0 && $request->filled('plan')) {
            $planObj = SubscriptionPlan::where('slug', strtolower($request->plan))
                ->orWhere('id', $request->plan)
                ->first();
            
            if ($planObj) {
                $isYearly = in_array(strtolower($request->billing_cycle), ['yearly', 'annual']);
                $originalAmount = (float) ($isYearly ? $planObj->yearly_price : $planObj->monthly_price);
            }
        }

        $validation = $coupon->isValidForAmount($originalAmount);

        if (!$validation['valid']) {
            return response()->json([
                'valid' => false,
                'message' => $validation['message']
            ], 422);
        }

        $discount = $coupon->calculateDiscount($originalAmount);
        $finalAmount = max(0, round($originalAmount - $discount, 2));

        return response()->json([
            'valid' => true,
            'message' => 'Coupon code applied successfully!',
            'coupon' => [
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => (float) $coupon->value,
            ],
            'original_amount' => $originalAmount,
            'discount_amount' => $discount,
            'final_amount' => $finalAmount,
        ]);
    }
}
