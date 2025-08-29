<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentReceipt;
use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Sale;
use App\Services\PaymentReceiptService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class PaymentReceiptController extends Controller
{
    protected $paymentReceiptService;

    public function __construct(PaymentReceiptService $paymentReceiptService)
    {
        $this->paymentReceiptService = $paymentReceiptService;
        
        // Apply permission middleware
        $this->middleware('permission:payment_receipts.view')->only(['index', 'show', 'statistics']);
        $this->middleware('permission:payment_receipts.create')->only(['store']);
        $this->middleware('permission:payment_receipts.edit')->only(['update']);
        $this->middleware('permission:payment_receipts.verify')->only(['verify']);
        $this->middleware('permission:payment_receipts.deposit')->only(['markAsDeposited']);
        $this->middleware('permission:payment_receipts.delete')->only(['destroy']);
        $this->middleware('permission:payment_receipts.cancel')->only(['cancel']);
    }

    /**
     * Get paginated list of payment receipts
     */
    public function index(Request $request): JsonResponse
    {
        $query = PaymentReceipt::with([
            'bankAccount',
            'createdBy:id,name',
            'verifiedBy:id,name',
            'depositedBy:id,name',
            'journalEntry',
            'bankTransaction'
        ]);

        // Apply filters
        if ($request->filled('receipt_type')) {
            $query->where('receipt_type', $request->receipt_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payer_type')) {
            $query->where('payer_type', $request->payer_type);
        }

        if ($request->filled('bank_account_id')) {
            $query->where('bank_account_id', $request->bank_account_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('receipt_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                  ->orWhere('payer_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('transaction_reference', 'like', "%{$search}%");
            });
        }

        // Sorting - handle both old format and DataTable format
        $sortBy = $request->get('sort_by', $request->get('sort_field', 'receipt_date'));
        $sortOrder = $request->get('sort_order', $request->get('sort_direction', 'desc'));
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $receipts = $query->paginate($perPage);

        return response()->json($receipts);
    }

    /**
     * Store a new payment receipt
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'receipt_type' => ['required', Rule::in([
                'customer_payment',
                'customer_advance',
                'supplier_refund',
                'supplier_rebate',
                'interest_income',
                'rental_income',
                'commission_income',
                'asset_sale',
                'bank_transfer_in',
                'cash_deposit',
                'miscellaneous_income',
                'other_receipt'
            ])],
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
            'reference_number' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'receipt_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,bank_transfer,check,card,online',
            'transaction_reference' => 'nullable|string|max:255',
            'description' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payer_type' => 'nullable|string|in:customer,supplier,other',
            'payer_id' => 'nullable|integer',
            'payer_name' => 'required|string|max:255',
            'status' => 'sometimes|string|in:draft,pending,verified',
            'invoice_allocations' => 'nullable|array',
            'invoice_allocations.*.invoice_id' => 'required_with:invoice_allocations|integer',
            'invoice_allocations.*.amount' => 'required_with:invoice_allocations|numeric|min:0.01',
            'additional_data' => 'nullable|array',
        ]);

        // Validate payer exists if payer_type and payer_id are provided
        if ($validated['payer_type'] && $validated['payer_id']) {
            $this->validatePayerExists($validated['payer_type'], $validated['payer_id']);
        }

        // Validate invoice allocations don't exceed receipt amount
        if (isset($validated['invoice_allocations'])) {
            $totalAllocated = collect($validated['invoice_allocations'])->sum('amount');
            if ($totalAllocated > $validated['amount']) {
                return response()->json([
                    'message' => 'Total allocated amount cannot exceed receipt amount',
                    'errors' => ['invoice_allocations' => ['Total allocation exceeds receipt amount']]
                ], 422);
            }
        }

        // Set default values
        $validated['created_by'] = Auth::id();
        $validated['status'] = $validated['status'] ?? 'draft';

        try {
            $receipt = $this->paymentReceiptService->createPaymentReceipt($validated);

            $receipt->load([
                'bankAccount',
                'createdBy:id,name',
                'journalEntry',
                'bankTransaction'
            ]);

            return response()->json([
                'message' => 'Payment receipt created successfully',
                'receipt' => $receipt
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create payment receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show specific payment receipt
     */
    public function show(PaymentReceipt $paymentReceipt): JsonResponse
    {
        $paymentReceipt->load([
            'bankAccount',
            'createdBy:id,name',
            'verifiedBy:id,name',
            'depositedBy:id,name',
            'journalEntry.journalEntryLines.account',
            'bankTransaction',
            'reference',
            'payer'
        ]);

        return response()->json($paymentReceipt);
    }

    /**
     * Update payment receipt
     */
    public function update(Request $request, PaymentReceipt $paymentReceipt): JsonResponse
    {
        // Check if receipt can be edited
        if (!$paymentReceipt->canBeEdited()) {
            return response()->json([
                'message' => 'Payment receipt cannot be edited in its current status'
            ], 422);
        }

        $validated = $request->validate([
            'receipt_type' => ['sometimes', Rule::in([
                'customer_payment',
                'customer_advance',
                'supplier_refund',
                'supplier_rebate',
                'interest_income',
                'rental_income',
                'commission_income',
                'asset_sale',
                'bank_transfer_in',
                'cash_deposit',
                'miscellaneous_income',
                'other_receipt'
            ])],
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
            'reference_number' => 'nullable|string|max:255',
            'amount' => 'sometimes|numeric|min:0.01',
            'receipt_date' => 'sometimes|date',
            'payment_method' => 'sometimes|string|in:cash,bank_transfer,check,card,online',
            'transaction_reference' => 'nullable|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'bank_account_id' => 'sometimes|exists:bank_accounts,id',
            'payer_type' => 'nullable|string|in:customer,supplier,other',
            'payer_id' => 'nullable|integer',
            'payer_name' => 'sometimes|string|max:255',
            'status' => 'sometimes|string|in:draft,pending,verified',
            'invoice_allocations' => 'nullable|array',
            'invoice_allocations.*.invoice_id' => 'required_with:invoice_allocations|integer',
            'invoice_allocations.*.amount' => 'required_with:invoice_allocations|numeric|min:0.01',
            'additional_data' => 'nullable|array',
        ]);

        // Validate payer exists if payer_type and payer_id are provided
        if (isset($validated['payer_type']) && isset($validated['payer_id'])) {
            $this->validatePayerExists($validated['payer_type'], $validated['payer_id']);
        }

        // Validate invoice allocations don't exceed receipt amount
        if (isset($validated['invoice_allocations'])) {
            $amount = $validated['amount'] ?? $paymentReceipt->amount;
            $totalAllocated = collect($validated['invoice_allocations'])->sum('amount');
            if ($totalAllocated > $amount) {
                return response()->json([
                    'message' => 'Total allocated amount cannot exceed receipt amount',
                    'errors' => ['invoice_allocations' => ['Total allocation exceeds receipt amount']]
                ], 422);
            }
        }

        try {
            $receipt = $this->paymentReceiptService->updatePaymentReceipt($paymentReceipt, $validated);

            $receipt->load([
                'bankAccount',
                'createdBy:id,name',
                'verifiedBy:id,name',
                'depositedBy:id,name',
                'journalEntry',
                'bankTransaction'
            ]);

            return response()->json([
                'message' => 'Payment receipt updated successfully',
                'receipt' => $receipt
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update payment receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify payment receipt
     */
    public function verify(Request $request, PaymentReceipt $paymentReceipt): JsonResponse
    {
        if (!$paymentReceipt->canBeVerified()) {
            return response()->json([
                'message' => 'Payment receipt cannot be verified in its current status'
            ], 422);
        }

        $validated = $request->validate([
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $receipt = $this->paymentReceiptService->verifyPaymentReceipt(
                $paymentReceipt,
                Auth::id(),
                $validated['verification_notes'] ?? null
            );

            $receipt->load([
                'bankAccount',
                'createdBy:id,name',
                'verifiedBy:id,name',
                'journalEntry',
                'bankTransaction'
            ]);

            return response()->json([
                'message' => 'Payment receipt verified successfully',
                'receipt' => $receipt
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to verify payment receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark payment receipt as deposited
     */
    public function markAsDeposited(PaymentReceipt $paymentReceipt): JsonResponse
    {
        if (!$paymentReceipt->canBeDeposited()) {
            return response()->json([
                'message' => 'Payment receipt cannot be marked as deposited in its current status'
            ], 422);
        }

        try {
            $receipt = $this->paymentReceiptService->markReceiptAsDeposited($paymentReceipt, Auth::id());

            $receipt->load([
                'bankAccount',
                'createdBy:id,name',
                'verifiedBy:id,name',
                'depositedBy:id,name',
                'journalEntry',
                'bankTransaction'
            ]);

            return response()->json([
                'message' => 'Payment receipt marked as deposited successfully',
                'receipt' => $receipt
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to mark payment receipt as deposited',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel payment receipt
     */
    public function cancel(PaymentReceipt $paymentReceipt): JsonResponse
    {
        if (!$paymentReceipt->canBeDeleted()) {
            return response()->json([
                'message' => 'Payment receipt cannot be cancelled in its current status'
            ], 422);
        }

        try {
            $receipt = $this->paymentReceiptService->updatePaymentReceipt($paymentReceipt, ['status' => 'cancelled']);

            return response()->json([
                'message' => 'Payment receipt cancelled successfully',
                'receipt' => $receipt
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to cancel payment receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete payment receipt
     */
    public function destroy(PaymentReceipt $paymentReceipt): JsonResponse
    {
        if (!$paymentReceipt->canBeDeleted()) {
            return response()->json([
                'message' => 'Payment receipt cannot be deleted in its current status'
            ], 422);
        }

        try {
            DB::transaction(function () use ($paymentReceipt) {
                // If receipt has accounting entries, reverse them first
                if ($paymentReceipt->journal_entry_id) {
                    $this->paymentReceiptService->updatePaymentReceipt($paymentReceipt, ['status' => 'cancelled']);
                }

                $paymentReceipt->delete();
            });

            return response()->json([
                'message' => 'Payment receipt deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete payment receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment receipt statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        $stats = [
            'total_receipts' => PaymentReceipt::whereBetween('receipt_date', [$startDate, $endDate])->count(),
            'total_amount' => PaymentReceipt::whereBetween('receipt_date', [$startDate, $endDate])
                                           ->where('status', 'deposited')
                                           ->sum('amount'),
            'pending_receipts' => PaymentReceipt::whereBetween('receipt_date', [$startDate, $endDate])
                                              ->where('status', 'pending')
                                              ->count(),
            'pending_amount' => PaymentReceipt::whereBetween('receipt_date', [$startDate, $endDate])
                                            ->where('status', 'pending')
                                            ->sum('amount'),
            'undeposited_receipts' => PaymentReceipt::whereBetween('receipt_date', [$startDate, $endDate])
                                                  ->whereIn('status', ['verified', 'pending'])
                                                  ->count(),
            'undeposited_amount' => PaymentReceipt::whereBetween('receipt_date', [$startDate, $endDate])
                                                 ->whereIn('status', ['verified', 'pending'])
                                                 ->sum('amount'),
            'by_type' => PaymentReceipt::whereBetween('receipt_date', [$startDate, $endDate])
                                     ->selectRaw('receipt_type, COUNT(*) as count, SUM(amount) as total_amount')
                                     ->groupBy('receipt_type')
                                     ->get(),
            'by_status' => PaymentReceipt::whereBetween('receipt_date', [$startDate, $endDate])
                                       ->selectRaw('status, COUNT(*) as count, SUM(amount) as total_amount')
                                       ->groupBy('status')
                                       ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Get payment receipt options for dropdowns
     */
    public function getReceiptOptions(): JsonResponse
    {
        $bankAccounts = BankAccount::where('is_active', true)
                                  ->select('id', 'account_name', 'bank_name', 'account_number')
                                  ->get();

        $customers = Customer::where('is_active', true)
                            ->select('id', 'name')
                            ->get();

        $suppliers = Supplier::where('is_active', true)
                            ->select('id', 'name', 'company_name')
                            ->get()
                            ->map(function ($supplier) {
                                $supplier->name = $supplier->company_name ?: $supplier->name;
                                return $supplier;
                            });

        return response()->json([
            'bank_accounts' => $bankAccounts,
            'customers' => $customers,
            'suppliers' => $suppliers,
            'receipt_types' => [
                ['value' => 'customer_payment', 'label' => 'Customer Payment'],
                ['value' => 'customer_advance', 'label' => 'Customer Advance'],
                ['value' => 'supplier_refund', 'label' => 'Supplier Refund'],
                ['value' => 'supplier_rebate', 'label' => 'Supplier Rebate'],
                ['value' => 'interest_income', 'label' => 'Interest Income'],
                ['value' => 'rental_income', 'label' => 'Rental Income'],
                ['value' => 'commission_income', 'label' => 'Commission Income'],
                ['value' => 'asset_sale', 'label' => 'Asset Sale'],
                ['value' => 'bank_transfer_in', 'label' => 'Bank Transfer In'],
                ['value' => 'cash_deposit', 'label' => 'Cash Deposit'],
                ['value' => 'miscellaneous_income', 'label' => 'Miscellaneous Income'],
                ['value' => 'other_receipt', 'label' => 'Other Receipt'],
            ],
            'payment_methods' => [
                ['value' => 'cash', 'label' => 'Cash'],
                ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
                ['value' => 'check', 'label' => 'Check'],
                ['value' => 'card', 'label' => 'Card'],
                ['value' => 'online', 'label' => 'Online Payment'],
            ],
            'statuses' => [
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'verified', 'label' => 'Verified'],
                ['value' => 'deposited', 'label' => 'Deposited'],
                ['value' => 'cancelled', 'label' => 'Cancelled'],
            ],
        ]);
    }

    /**
     * Get customer invoices for allocation
     */
    public function getCustomerInvoices(Request $request): JsonResponse
    {
        $customerId = $request->get('customer_id');

        if (!$customerId) {
            return response()->json(['invoices' => []]);
        }

        // Get outstanding invoices for the customer
        $invoices = Sale::where('customer_id', $customerId)
                       ->where('status', 'completed')
                       ->where('is_refund', false)
                       ->whereColumn('total_amount', '>', 'paid_amount')
                       ->select('id', 'sale_number', 'sale_date', 'total_amount', 'paid_amount')
                       ->orderBy('sale_date', 'asc')
                       ->get()
                       ->map(function ($invoice) {
                           $invoice->outstanding_amount = $invoice->total_amount - $invoice->paid_amount;
                           return $invoice;
                       });

        return response()->json(['invoices' => $invoices]);
    }

    /**
     * Validate that payer exists
     */
    protected function validatePayerExists(string $payerType, int $payerId): void
    {
        $models = [
            'customer' => Customer::class,
            'supplier' => Supplier::class,
        ];

        if (isset($models[$payerType])) {
            $model = $models[$payerType];
            if (!$model::find($payerId)) {
                throw new \InvalidArgumentException("Invalid {$payerType} ID: {$payerId}");
            }
        }
    }
}
