<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomForm;
use App\Models\BusinessType;
use Illuminate\Http\Request;

class AdminCustomFormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CustomForm::with('businessType');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('businessType', function ($bq) use ($search) {
                      $bq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('business_type_id') && $request->business_type_id !== 'all') {
            $query->where('business_type_id', $request->business_type_id);
        }

        if ($request->filled('area_of_use') && $request->area_of_use !== 'all') {
            $query->where('area_of_use', $request->area_of_use);
        }

        if ($request->has('status') && $request->status !== '' && $request->status !== 'all') {
            $query->where('is_active', filter_var($request->status, FILTER_VALIDATE_BOOLEAN));
        }

        // Sorting
        $allowedSortColumns = ['id', 'name', 'area_of_use', 'sort_order', 'created_at'];
        $sortBy = in_array($request->get('sort_by'), $allowedSortColumns) ? $request->get('sort_by') : 'id';
        $sortDir = strtolower($request->get('sort_dir')) === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sortBy, $sortDir);

        $perPage = max(1, min(100, (int) $request->get('per_page', 10)));
        $forms = $query->paginate($perPage);

        return response()->json($forms);
    }

    /**
     * Store a newly created custom form in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_type_id' => 'nullable|exists:business_types,id',
            'area_of_use' => 'required|string|in:' . implode(',', array_keys(CustomForm::$areaOfUseOptions)),
            'description' => 'nullable|string|max:1000',
            'fields' => 'required|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $customForm = CustomForm::create($validated);

        return response()->json([
            'message' => 'Custom Form layout created successfully.',
            'data' => $customForm->load('businessType'),
        ], 201);
    }

    /**
     * Display the specified custom form.
     */
    public function show(CustomForm $customForm)
    {
        return response()->json([
            'data' => $customForm->load('businessType')
        ]);
    }

    /**
     * Update the specified custom form in storage.
     */
    public function update(Request $request, CustomForm $customForm)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_type_id' => 'nullable|exists:business_types,id',
            'area_of_use' => 'required|string|in:' . implode(',', array_keys(CustomForm::$areaOfUseOptions)),
            'description' => 'nullable|string|max:1000',
            'fields' => 'required|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $customForm->update($validated);

        return response()->json([
            'message' => 'Custom Form layout updated successfully.',
            'data' => $customForm->load('businessType'),
        ]);
    }

    /**
     * Soft delete the specified custom form.
     */
    public function destroy(CustomForm $customForm)
    {
        $customForm->delete();

        return response()->json([
            'message' => 'Custom Form deleted successfully.'
        ]);
    }

    /**
     * Return metadata options for Form Builder UI (Area of Use & Business Types list).
     */
    public function metaOptions()
    {
        $areas = [];
        foreach (CustomForm::$areaOfUseOptions as $key => $label) {
            $areas[] = ['value' => $key, 'label' => $label];
        }

        $businessTypes = BusinessType::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->select('id', 'name', 'slug', 'icon')
            ->get();

        return response()->json([
            'areas_of_use' => $areas,
            'business_types' => $businessTypes
        ]);
    }

    /**
     * Fetch active custom form fields for a specific business_type and area_of_use.
     */
    public function getFormForArea(Request $request)
    {
        $request->validate([
            'area_of_use' => 'required|string',
            'business_type_id' => 'nullable|integer',
            'business_type_slug' => 'nullable|string',
        ]);

        $query = CustomForm::where('area_of_use', $request->area_of_use)
            ->where('is_active', true);

        if ($request->filled('business_type_id')) {
            $query->where('business_type_id', $request->business_type_id);
        } elseif ($request->filled('business_type_slug')) {
            $slug = $request->business_type_slug;
            $query->whereHas('businessType', function ($q) use ($slug) {
                $q->where('slug', $slug);
            });
        }

        $form = $query->latest()->first();

        return response()->json([
            'data' => $form ? $form->fields : []
        ]);
    }
}
