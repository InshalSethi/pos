<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessType;
use Illuminate\Http\Request;

class AdminBusinessTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = BusinessType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== '' && $request->status !== 'all') {
            $query->where('is_active', filter_var($request->status, FILTER_VALIDATE_BOOLEAN));
        }

        $businessTypes = $query->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->paginate($request->get('per_page', 15));

        return response()->json($businessTypes);
    }

    /**
     * Return formatted paginated data for frontend Vue datatable.
     */
    public function data(Request $request)
    {
        $query = BusinessType::query();

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->status !== '' && $request->status !== 'all') {
            $query->where('is_active', filter_var($request->status, FILTER_VALIDATE_BOOLEAN));
        }

        // Sorting
        $allowedSortColumns = ['sort_order', 'name', 'slug', 'is_active', 'created_at'];
        $sortBy = in_array($request->get('sort_by'), $allowedSortColumns) ? $request->get('sort_by') : 'sort_order';
        $sortDir = strtolower($request->get('sort_dir')) === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sortBy, $sortDir);

        if ($sortBy !== 'name') {
            $query->orderBy('name', 'asc');
        }

        $perPage = max(1, min(100, (int) $request->get('per_page', 10)));

        $types = $query->paginate($perPage);

        return response()->json($types);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:business_types,name',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        if (empty($validated['icon'])) {
            $validated['icon'] = 'fas fa-store';
        }

        $businessType = BusinessType::create($validated);

        return response()->json([
            'message' => 'Business type created successfully.',
            'data' => $businessType,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(BusinessType $businessType)
    {
        return response()->json([
            'data' => $businessType
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BusinessType $businessType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:business_types,name,' . $businessType->id,
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        if (empty($validated['icon'])) {
            $validated['icon'] = 'fas fa-store';
        }

        $businessType->update($validated);

        return response()->json([
            'message' => 'Business type updated successfully.',
            'data' => $businessType,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BusinessType $businessType)
    {
        $businessType->delete();

        return response()->json([
            'message' => 'Business type deleted successfully.'
        ]);
    }

    /**
     * Get active business types for select dropdowns.
     */
    public function options()
    {
        $types = BusinessType::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->select('id', 'name', 'slug', 'description', 'icon')
            ->get();

        return response()->json($types);
    }
}
