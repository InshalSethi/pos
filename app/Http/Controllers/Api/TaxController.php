<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TaxController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:taxes.view')->only(['index', 'show']);
        $this->middleware('permission:taxes.create')->only(['store']);
        $this->middleware('permission:taxes.edit')->only(['update']);
        $this->middleware('permission:taxes.delete')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tax::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $taxes = $query->orderBy('name')->get();

        return response()->json($taxes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('taxes', 'name')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })
            ],
            'value' => 'required|numeric|min:0',
            'type' => 'required|string|in:percentage,flat',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $tax = new Tax();
        $tax->fill($request->all());
        $tax->company_id = $companyId;
        $tax->save();

        return response()->json([
            'message' => 'Tax created successfully',
            'tax' => $tax
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tax $tax): JsonResponse
    {
        return response()->json($tax);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tax $tax): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('taxes', 'name')->ignore($tax->id)->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })
            ],
            'value' => 'required|numeric|min:0',
            'type' => 'required|string|in:percentage,flat',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $tax->update($request->all());

        return response()->json([
            'message' => 'Tax updated successfully',
            'tax' => $tax
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tax $tax): JsonResponse
    {
        $tax->delete();

        return response()->json([
            'message' => 'Tax deleted successfully'
        ]);
    }
}
