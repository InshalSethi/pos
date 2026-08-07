<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SalesReturnController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:sales.view')->only(['index', 'show']);
    }

    /**
     * Display a listing of sales returns with dynamic filter query scopes.
     */
    public function index(Request $request): JsonResponse
    {
        // Force is_refund filter for sales return listings
        $request->merge(['is_refund' => true]);

        return app(SaleController::class)->index($request);
    }
}
