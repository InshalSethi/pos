<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;

class AdminSubscriptionPaymentController extends Controller
{
    /**
     * Display a listing of user subscription payments.
     */
    public function index(Request $request)
    {
        $query = SubscriptionPayment::with('user:id,name,email');

        // Search by user name, email, or transaction ID
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%")
                  ->orWhere('plan_name', 'like', "%{$search}%")
                  ->orWhere('coupon_code', 'like', "%{$search}%");
            });
        }

        // Filter by plan
        if ($request->filled('plan') && $request->input('plan') !== 'all') {
            $query->whereRaw('LOWER(plan_name) = ?', [strtolower($request->input('plan'))]);
        }

        // Filter by status
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Calculate summary statistics
        $stats = [
            'total_revenue'      => (float) SubscriptionPayment::where('status', 'paid')->sum('amount'),
            'total_transactions' => SubscriptionPayment::count(),
            'paid_count'         => SubscriptionPayment::where('status', 'paid')->count(),
            'monthly_revenue'    => (float) SubscriptionPayment::where('status', 'paid')
                                       ->whereMonth('paid_at', now()->month)
                                       ->whereYear('paid_at', now()->year)
                                       ->sum('amount'),
        ];

        $payments = $query->orderBy('created_at', 'desc')
                          ->paginate($request->input('per_page', 15));

        return response()->json([
            'success'  => true,
            'stats'    => $stats,
            'payments' => $payments,
        ]);
    }

    /**
     * Display single payment details.
     */
    public function show($id)
    {
        $payment = SubscriptionPayment::with('user:id,name,email')->findOrFail($id);
        return response()->json([
            'success' => true,
            'payment' => $payment
        ]);
    }
}
