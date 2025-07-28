<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:employees.view')->only(['index', 'show']);
        $this->middleware('permission:employees.create')->only(['store']);
        $this->middleware('permission:employees.edit')->only(['update']);
        $this->middleware('permission:employees.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Department::with(['manager', 'parent', 'children', 'employees']);

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $departments = $query->orderBy('name')->get();

        return response()->json($departments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:employees,id',
            'parent_department_id' => 'nullable|exists:departments,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $department = Department::create($request->all());
        $department->load(['manager', 'parent', 'children']);

        return response()->json([
            'message' => 'Department created successfully',
            'department' => $department
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department): JsonResponse
    {
        $department->load(['manager', 'parent', 'children', 'employees', 'positions']);

        return response()->json($department);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code,' . $department->id,
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:employees,id',
            'parent_department_id' => 'nullable|exists:departments,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Prevent circular reference
        if ($request->has('parent_department_id') && $request->parent_department_id) {
            if ($request->parent_department_id == $department->id) {
                return response()->json([
                    'message' => 'Department cannot be its own parent'
                ], 422);
            }

            $children = $department->getAllChildren();
            if ($children->contains('id', $request->parent_department_id)) {
                return response()->json([
                    'message' => 'Cannot set a child department as parent'
                ], 422);
            }
        }

        $department->update($request->all());
        $department->load(['manager', 'parent', 'children']);

        return response()->json([
            'message' => 'Department updated successfully',
            'department' => $department
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department): JsonResponse
    {
        // Check if department has employees
        if ($department->employees()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete department that has employees'
            ], 422);
        }

        // Check if department has children
        if ($department->children()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete department that has sub-departments'
            ], 422);
        }

        $department->delete();

        return response()->json([
            'message' => 'Department deleted successfully'
        ]);
    }

    /**
     * Get department tree structure
     */
    public function tree(): JsonResponse
    {
        $departments = Department::with(['children' => function ($query) {
            $query->active()->orderBy('name');
        }])
        ->whereNull('parent_department_id')
        ->active()
        ->orderBy('name')
        ->get();

        return response()->json($departments);
    }
}
