<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PositionController extends Controller
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
        $query = Position::with(['department', 'employees']);

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('department_id')) {
            $query->where('department_id', $request->get('department_id'));
        }

        if ($request->has('level')) {
            $query->where('level', $request->get('level'));
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $positions = $query->orderBy('title')->get();

        return response()->json($positions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:positions,code',
            'description' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'level' => 'required|in:entry,junior,mid,senior,lead,manager,director,executive',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gte:min_salary',
            'requirements' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $position = Position::create($request->all());
        $position->load(['department']);

        return response()->json([
            'message' => 'Position created successfully',
            'position' => $position
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Position $position): JsonResponse
    {
        $position->load(['department', 'employees']);

        return response()->json($position);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Position $position): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:positions,code,' . $position->id,
            'description' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'level' => 'required|in:entry,junior,mid,senior,lead,manager,director,executive',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gte:min_salary',
            'requirements' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $position->update($request->all());
        $position->load(['department']);

        return response()->json([
            'message' => 'Position updated successfully',
            'position' => $position
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Position $position): JsonResponse
    {
        // Check if position has employees
        if ($position->employees()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete position that has employees assigned to it'
            ], 422);
        }

        $position->delete();

        return response()->json([
            'message' => 'Position deleted successfully'
        ]);
    }
}
