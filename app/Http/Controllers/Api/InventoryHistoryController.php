<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryHistory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InventoryHistoryController extends Controller
{
    /**
     * Display a listing of the inventory histories.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        $query = InventoryHistory::with(['product', 'variation', 'warehouse'])
            ->where('company_id', $companyId);

        // Filter by warehouse
        if ($request->has('warehouse_id') && $request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by product (Search item name, SKU, or barcode)
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $perPage = $request->get('per_page', 15);
        $history = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($history);
    }
}
