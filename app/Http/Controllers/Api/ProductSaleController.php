<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductSaleController extends Controller
{
    public function applyBulkSale(Request $request)
    {
        $request->validate([
            'discount_type' => 'required|in:fixed,percentage',
            'discount_value' => 'required|numeric|min:0',
            'apply_to' => 'required|in:all,selected',
            'product_ids' => 'required_if:apply_to,selected|array',
            'product_ids.*' => 'integer|exists:products,id'
        ]);

        $companyId = auth()->user()->current_company_id;

        $query = Product::where('company_id', $companyId);

        if ($request->apply_to === 'selected') {
            $query->whereIn('id', $request->product_ids);
        }

        $query->update([
            'discount_type' => $request->discount_value > 0 ? $request->discount_type : null,
            'discount_value' => $request->discount_value > 0 ? $request->discount_value : 0,
        ]);

        return response()->json([
            'message' => 'Sale applied successfully'
        ]);
    }
}
