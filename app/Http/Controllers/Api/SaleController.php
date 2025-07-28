<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:sales.view')->only(['index', 'show']);
        $this->middleware('permission:sales.create')->only(['store']);
        $this->middleware('permission:sales.edit')->only(['update']);
        $this->middleware('permission:sales.delete')->only(['destroy']);
        $this->middleware('permission:sales.refund')->only(['refund']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Sale::with(['customer', 'user', 'saleItems.product']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sale_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        // Legacy support for start_date and end_date
        if ($request->has('start_date')) {
            $query->whereDate('sale_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('sale_date', '<=', $request->end_date);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by customer
        if ($request->has('customer_id') && $request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by refund status
        if ($request->has('is_refund')) {
            $query->where('is_refund', $request->boolean('is_refund'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'sale_date');
        $sortOrder = $request->get('sort_order', 'desc');

        // Validate sort fields
        $allowedSortFields = ['sale_date', 'sale_number', 'total_amount', 'status', 'created_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('sale_date', 'desc');
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $sales = $query->paginate($perPage);

        return response()->json($sales);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,card,bank_transfer,mobile_payment,mixed',
            'paid_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Calculate totals
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $itemDiscount = $item['discount_amount'] ?? 0;
                $itemTax = ($itemSubtotal - $itemDiscount) * ($product->tax_rate / 100);

                $subtotal += $itemSubtotal;
                $totalDiscount += $itemDiscount;
                $totalTax += $itemTax;
            }

            $totalAmount = $subtotal - $totalDiscount + $totalTax;
            $changeAmount = max(0, $request->paid_amount - $totalAmount);

            // Generate sale number
            $saleNumber = 'SALE-' . date('Ymd') . '-' . str_pad(Sale::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Create sale
            $sale = Sale::create([
                'sale_number' => $saleNumber,
                'customer_id' => $request->customer_id,
                'user_id' => auth()->id(),
                'sale_date' => now(),
                'status' => 'completed',
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'discount_amount' => $totalDiscount,
                'total_amount' => $totalAmount,
                'paid_amount' => $request->paid_amount,
                'change_amount' => $changeAmount,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);

            // Create sale items and update inventory
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $itemDiscount = $item['discount_amount'] ?? 0;
                $itemTax = ($itemSubtotal - $itemDiscount) * ($product->tax_rate / 100);
                $itemTotal = $itemSubtotal - $itemDiscount + $itemTax;

                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $itemDiscount,
                    'tax_amount' => $itemTax,
                    'total_amount' => $itemTotal,
                ]);

                // Update product inventory
                if ($product->track_inventory) {
                    $product->decrement('stock_quantity', $item['quantity']);
                }
            }

            // Update customer total purchases
            if ($request->customer_id) {
                $customer = Customer::find($request->customer_id);
                $customer->increment('total_purchases', $totalAmount);
            }

            DB::commit();

            $sale->load(['customer', 'user', 'saleItems.product']);

            return response()->json([
                'message' => 'Sale completed successfully',
                'sale' => $sale
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to process sale',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale): JsonResponse
    {
        $sale->load(['customer', 'user', 'saleItems.product']);

        return response()->json($sale);
    }

    /**
     * Process a refund
     */
    public function refund(Request $request, Sale $sale): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($sale->status !== 'completed') {
            return response()->json([
                'message' => 'Can only refund completed sales'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $refundAmount = 0;
            $refundItems = [];

            foreach ($request->items as $item) {
                $saleItem = SaleItem::find($item['sale_item_id']);

                if ($saleItem->sale_id !== $sale->id) {
                    throw new \Exception('Sale item does not belong to this sale');
                }

                if ($item['quantity'] > $saleItem->quantity) {
                    throw new \Exception('Refund quantity cannot exceed sold quantity');
                }

                $refundItemAmount = ($saleItem->total_amount / $saleItem->quantity) * $item['quantity'];
                $refundAmount += $refundItemAmount;

                $refundItems[] = [
                    'product_id' => $saleItem->product_id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $saleItem->unit_price,
                    'discount_amount' => ($saleItem->discount_amount / $saleItem->quantity) * $item['quantity'],
                    'tax_amount' => ($saleItem->tax_amount / $saleItem->quantity) * $item['quantity'],
                    'total_amount' => $refundItemAmount,
                ];

                // Update inventory
                $product = Product::find($saleItem->product_id);
                if ($product->track_inventory) {
                    $product->increment('stock_quantity', $item['quantity']);
                }
            }

            // Create refund sale
            $refundSaleNumber = 'REFUND-' . date('Ymd') . '-' . str_pad(Sale::whereDate('created_at', today())->where('is_refund', true)->count() + 1, 4, '0', STR_PAD_LEFT);

            $refundSale = Sale::create([
                'sale_number' => $refundSaleNumber,
                'customer_id' => $sale->customer_id,
                'user_id' => auth()->id(),
                'sale_date' => now(),
                'status' => 'completed',
                'subtotal' => -$refundAmount,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => -$refundAmount,
                'paid_amount' => -$refundAmount,
                'change_amount' => 0,
                'payment_method' => $sale->payment_method,
                'notes' => $request->reason,
                'is_refund' => true,
                'original_sale_id' => $sale->id,
            ]);

            // Create refund sale items
            foreach ($refundItems as $refundItem) {
                SaleItem::create(array_merge($refundItem, [
                    'sale_id' => $refundSale->id,
                    'quantity' => -$refundItem['quantity'], // Negative quantity for refunds
                    'total_amount' => -$refundItem['total_amount'], // Negative amount for refunds
                ]));
            }

            DB::commit();

            $refundSale->load(['customer', 'user', 'saleItems.product', 'originalSale']);

            return response()->json([
                'message' => 'Refund processed successfully',
                'refund_sale' => $refundSale
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to process refund',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sales statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', today()->toDateString());
        $endDate = $request->get('end_date', today()->toDateString());

        $stats = [
            'total_sales' => Sale::whereBetween('sale_date', [$startDate, $endDate])
                                ->where('status', 'completed')
                                ->where('is_refund', false)
                                ->sum('total_amount'),
            'total_transactions' => Sale::whereBetween('sale_date', [$startDate, $endDate])
                                      ->where('status', 'completed')
                                      ->where('is_refund', false)
                                      ->count(),
            'total_refunds' => Sale::whereBetween('sale_date', [$startDate, $endDate])
                                 ->where('is_refund', true)
                                 ->sum('total_amount'),
            'average_sale' => Sale::whereBetween('sale_date', [$startDate, $endDate])
                                ->where('status', 'completed')
                                ->where('is_refund', false)
                                ->avg('total_amount'),
        ];

        return response()->json($stats);
    }
}
