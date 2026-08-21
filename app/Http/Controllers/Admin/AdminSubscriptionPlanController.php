<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminSubscriptionPlanController extends Controller
{
    /**
     * Display a listing of the subscription plans.
     */
    public function index(Request $request)
    {
        $query = SubscriptionPlan::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== '' && $request->status !== 'all') {
            $query->where('is_active', filter_var($request->status, FILTER_VALIDATE_BOOLEAN));
        }

        $allowedSortColumns = ['sort_order', 'name', 'monthly_price', 'yearly_price', 'created_at'];
        $sortBy = in_array($request->get('sort_by'), $allowedSortColumns) ? $request->get('sort_by') : 'sort_order';
        $sortDir = strtolower($request->get('sort_dir')) === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sortBy, $sortDir)->orderBy('id', 'asc');

        $perPage = max(1, min(100, (int) $request->get('per_page', 15)));
        $plans = $query->paginate($perPage);

        return response()->json($plans);
    }

    /**
     * Store a newly created subscription plan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255|unique:subscription_plans,name',
            'slug'                  => 'nullable|string|max:255|unique:subscription_plans,slug',
            'description'           => 'nullable|string',
            'monthly_price'         => 'required|numeric|min:0',
            'yearly_price'          => 'required|numeric|min:0',
            'trial_days'            => 'integer|min:0',
            'max_companies'         => 'required|integer|min:1',
            'max_users_per_company' => 'required|integer|min:1',
            'is_popular'            => 'boolean',
            'is_custom'             => 'boolean',
            'is_active'             => 'boolean',
            'sort_order'            => 'integer|min:0',
            'features'              => 'nullable|array',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $plan = SubscriptionPlan::create($validated);

        return response()->json([
            'message' => 'Subscription plan created successfully.',
            'data'    => $plan,
        ], 201);
    }

    /**
     * Display the specified subscription plan.
     */
    public function show(SubscriptionPlan $subscriptionPlan)
    {
        return response()->json([
            'data' => $subscriptionPlan
        ]);
    }

    /**
     * Update the specified subscription plan.
     */
    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255|unique:subscription_plans,name,' . $subscriptionPlan->id,
            'slug'                  => 'nullable|string|max:255|unique:subscription_plans,slug,' . $subscriptionPlan->id,
            'description'           => 'nullable|string',
            'monthly_price'         => 'required|numeric|min:0',
            'yearly_price'          => 'required|numeric|min:0',
            'trial_days'            => 'integer|min:0',
            'max_companies'         => 'required|integer|min:1',
            'max_users_per_company' => 'required|integer|min:1',
            'is_popular'            => 'boolean',
            'is_custom'             => 'boolean',
            'is_active'             => 'boolean',
            'sort_order'            => 'integer|min:0',
            'features'              => 'nullable|array',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $subscriptionPlan->update($validated);

        return response()->json([
            'message' => 'Subscription plan updated successfully.',
            'data'    => $subscriptionPlan,
        ]);
    }

    /**
     * Remove the specified subscription plan.
     */
    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->delete();

        return response()->json([
            'message' => 'Subscription plan deleted successfully.'
        ]);
    }

    /**
     * Get list of active subscription plans for public website & registration.
     */
    public function publicPlans()
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('monthly_price', 'asc')
            ->get();

        return response()->json($plans);
    }
}
