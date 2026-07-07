<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:inventory.view')->only(['index', 'show', 'inventory']);
        $this->middleware('permission:inventory.edit')->only(['store', 'update', 'destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;
        $query = Warehouse::where('company_id', $companyId);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $warehouses = $query->orderBy('name')->get();

        return response()->json($warehouses);
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
                Rule::unique('warehouses')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })
            ],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $isDefault = $request->boolean('is_default');

            if ($isDefault) {
                // Remove default flag from other warehouses in the company
                Warehouse::where('company_id', $companyId)->update(['is_default' => false]);
            }

            // Check if this is the first warehouse, if so force it to be default
            $count = Warehouse::where('company_id', $companyId)->count();
            if ($count === 0) {
                $isDefault = true;
            }

            $warehouse = new Warehouse();
            $warehouse->fill($request->all());
            $warehouse->company_id = $companyId;
            $warehouse->is_default = $isDefault;
            $warehouse->save();

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'message' => 'Warehouse created successfully',
                'warehouse' => $warehouse
            ], 201);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['message' => 'Failed to create warehouse: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Warehouse $warehouse): JsonResponse
    {
        $warehouse->load(['inventories' => function ($query) {
            $query->whereHas('product', function ($pQuery) {
                $pQuery->where('status', '!=', 'draft');
            })->with(['product.category', 'variation']);
        }]);

        return response()->json($warehouse);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Warehouse $warehouse): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('warehouses')->ignore($warehouse->id)->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })
            ],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $isDefault = $request->boolean('is_default');

            if ($isDefault) {
                // Remove default flag from other warehouses in the company
                Warehouse::where('company_id', $companyId)->where('id', '!=', $warehouse->id)->update(['is_default' => false]);
            } else {
                // If we are unsetting default, make sure we have at least one default warehouse
                $otherDefaultExists = Warehouse::where('company_id', $companyId)
                    ->where('id', '!=', $warehouse->id)
                    ->where('is_default', true)
                    ->exists();

                if (!$otherDefaultExists) {
                    $isDefault = true; // force keep default
                }
            }

            $warehouse->update(array_merge($request->all(), ['is_default' => $isDefault]));

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'message' => 'Warehouse updated successfully',
                'warehouse' => $warehouse
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['message' => 'Failed to update warehouse: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warehouse $warehouse): JsonResponse
    {
        // Prevent deleting the default warehouse
        if ($warehouse->is_default) {
            return response()->json([
                'message' => 'Cannot delete the default warehouse. Set another warehouse as default first.'
            ], 422);
        }

        // Check if there is stock in this warehouse
        $hasStock = Inventory::where('warehouse_id', $warehouse->id)->where('stock_qty', '>', 0)->exists();
        if ($hasStock) {
            return response()->json([
                'message' => 'Cannot delete warehouse with active stock. Transfer or adjust stock first.'
            ], 422);
        }

        $warehouse->delete();

        return response()->json([
            'message' => 'Warehouse deleted successfully'
        ]);
    }

    /**
     * Get paginated inventory list for a specific warehouse.
     */
    public function inventory(Request $request, Warehouse $warehouse): JsonResponse
    {
        $companyId = auth()->user() ? auth()->user()->current_company_id : 'none';
        \Log::info("Warehouse Inventory API Request", [
            'warehouse_id' => $warehouse->id,
            'company_id' => $companyId,
            'user_id' => auth()->id(),
            'request_params' => $request->all(),
        ]);

        $query = Inventory::where('warehouse_id', $warehouse->id)
            ->whereHas('product', function ($pQuery) {
                $pQuery->where('status', '!=', 'draft');
            })
            ->with(['product.category', 'variation']);

        \Log::info("Warehouse Inventory SQL:", [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($pQuery) use ($search) {
                    $pQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($catQuery) use ($search) {
                            $catQuery->where('name', 'like', "%{$search}%");
                        });
                })->orWhereHas('variation', function ($vQuery) use ($search) {
                    $vQuery->where('variation_name_string', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            });
        }

        $perPage = $request->get('per_page', 10);
        $inventories = $query->orderBy('id', 'desc')->paginate($perPage);

        \Log::info("Warehouse Inventory Count:", [
            'total' => $inventories->total(),
            'item_ids' => collect($inventories->items())->pluck('id')->toArray()
        ]);

        return response()->json($inventories);
    }
}
