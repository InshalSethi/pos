<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\BankAccount;
use App\Models\Supplier;
use App\Models\Employee;
use App\Models\Customer;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
        
        // Apply permission middleware
        $this->middleware('permission:payments.view')->only(['index', 'show', 'statistics']);
        $this->middleware('permission:payments.create')->only(['store']);
        $this->middleware('permission:payments.edit')->only(['update', 'approve', 'markAsPaid']);
        $this->middleware('permission:payments.delete')->only(['destroy']);
    }

    /**
     * Get paginated list of payments
     */
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with([
            'bankAccount',
            'createdBy:id,name',
            'approvedBy:id,name',
            'paidBy:id,name',
            'journalEntry',
            'bankTransaction'
        ]);

        // Apply filters
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payee_type')) {
            $query->where('payee_type', $request->payee_type);
        }

        if ($request->filled('bank_account_id')) {
            $query->where('bank_account_id', $request->bank_account_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('payment_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('payee_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        // Sorting - handle both old format and DataTable format
        $sortBy = $request->get('sort_by', $request->get('sort_field', 'payment_date'));
        $sortOrder = $request->get('sort_order', $request->get('sort_direction', 'desc'));
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $payments = $query->paginate($perPage);

        return response()->json($payments);
    }

    /**
     * Store a new payment
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_type' => ['required', Rule::in([
                'supplier_payment',
                'expense_payment',
                'salary_payment',
                'sale_return_payment',
                'purchase_invoice_payment',
                'other_payment'
            ])],
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,bank_transfer,check,card',
            'reference_number' => 'nullable|string|max:255',
            'description' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payee_type' => 'nullable|string|in:supplier,employee,customer,other',
            'payee_id' => 'nullable|integer',
            'payee_name' => 'required|string|max:255',
            'status' => 'sometimes|string|in:draft,pending,approved',
            'additional_data' => 'nullable|array',
        ]);

        // Validate payee exists if payee_type and payee_id are provided
        if ($validated['payee_type'] && $validated['payee_id']) {
            $this->validatePayeeExists($validated['payee_type'], $validated['payee_id']);
        }

        // Set default values
        $validated['created_by'] = Auth::id();
        $validated['status'] = $validated['status'] ?? 'draft';

        try {
            $payment = $this->paymentService->createPayment($validated);

            $payment->load([
                'bankAccount',
                'createdBy:id,name',
                'journalEntry',
                'bankTransaction'
            ]);

            return response()->json([
                'message' => 'Payment created successfully',
                'payment' => $payment
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show specific payment
     */
    public function show(Payment $payment): JsonResponse
    {
        $payment->load([
            'bankAccount',
            'createdBy:id,name',
            'approvedBy:id,name',
            'paidBy:id,name',
            'journalEntry.journalEntryLines.account',
            'bankTransaction',
            'reference',
            'payee'
        ]);

        return response()->json($payment);
    }

    /**
     * Update payment
     */
    public function update(Request $request, Payment $payment): JsonResponse
    {
        // Check if payment can be edited
        if (!$payment->canBeEdited()) {
            return response()->json([
                'message' => 'Payment cannot be edited in its current status'
            ], 422);
        }

        $validated = $request->validate([
            'payment_type' => ['sometimes', Rule::in([
                'supplier_payment',
                'expense_payment',
                'salary_payment',
                'sale_return_payment',
                'purchase_invoice_payment',
                'other_payment'
            ])],
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
            'amount' => 'sometimes|numeric|min:0.01',
            'payment_date' => 'sometimes|date',
            'payment_method' => 'sometimes|string|in:cash,bank_transfer,check,card',
            'reference_number' => 'nullable|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'bank_account_id' => 'sometimes|exists:bank_accounts,id',
            'payee_type' => 'nullable|string|in:supplier,employee,customer,other',
            'payee_id' => 'nullable|integer',
            'payee_name' => 'sometimes|string|max:255',
            'status' => 'sometimes|string|in:draft,pending,approved',
            'additional_data' => 'nullable|array',
        ]);

        // Validate payee exists if payee_type and payee_id are provided
        if (isset($validated['payee_type']) && isset($validated['payee_id'])) {
            $this->validatePayeeExists($validated['payee_type'], $validated['payee_id']);
        }

        try {
            $payment = $this->paymentService->updatePayment($payment, $validated);

            $payment->load([
                'bankAccount',
                'createdBy:id,name',
                'approvedBy:id,name',
                'paidBy:id,name',
                'journalEntry',
                'bankTransaction'
            ]);

            return response()->json([
                'message' => 'Payment updated successfully',
                'payment' => $payment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve payment
     */
    public function approve(Request $request, Payment $payment): JsonResponse
    {
        if (!$payment->canBeApproved()) {
            return response()->json([
                'message' => 'Payment cannot be approved in its current status'
            ], 422);
        }

        $validated = $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $payment = $this->paymentService->approvePayment(
                $payment,
                Auth::id(),
                $validated['approval_notes'] ?? null
            );

            $payment->load([
                'bankAccount',
                'createdBy:id,name',
                'approvedBy:id,name',
                'journalEntry',
                'bankTransaction'
            ]);

            return response()->json([
                'message' => 'Payment approved successfully',
                'payment' => $payment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to approve payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark payment as paid
     */
    public function markAsPaid(Payment $payment): JsonResponse
    {
        if (!$payment->canBePaid()) {
            return response()->json([
                'message' => 'Payment cannot be marked as paid in its current status'
            ], 422);
        }

        try {
            $payment = $this->paymentService->markPaymentAsPaid($payment, Auth::id());

            $payment->load([
                'bankAccount',
                'createdBy:id,name',
                'approvedBy:id,name',
                'paidBy:id,name',
                'journalEntry',
                'bankTransaction'
            ]);

            return response()->json([
                'message' => 'Payment marked as paid successfully',
                'payment' => $payment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to mark payment as paid',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel payment
     */
    public function cancel(Payment $payment): JsonResponse
    {
        if (!$payment->canBeDeleted()) {
            return response()->json([
                'message' => 'Payment cannot be cancelled in its current status'
            ], 422);
        }

        try {
            $payment = $this->paymentService->updatePayment($payment, ['status' => 'cancelled']);

            return response()->json([
                'message' => 'Payment cancelled successfully',
                'payment' => $payment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to cancel payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete payment
     */
    public function destroy(Payment $payment): JsonResponse
    {
        if (!$payment->canBeDeleted()) {
            return response()->json([
                'message' => 'Payment cannot be deleted in its current status'
            ], 422);
        }

        try {
            DB::transaction(function () use ($payment) {
                // If payment has accounting entries, reverse them first
                if ($payment->journal_entry_id) {
                    $this->paymentService->updatePayment($payment, ['status' => 'cancelled']);
                }

                $payment->delete();
            });

            return response()->json([
                'message' => 'Payment deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        $stats = [
            'total_payments' => Payment::whereBetween('payment_date', [$startDate, $endDate])->count(),
            'total_amount' => Payment::whereBetween('payment_date', [$startDate, $endDate])
                                   ->where('status', 'paid')
                                   ->sum('amount'),
            'pending_payments' => Payment::whereBetween('payment_date', [$startDate, $endDate])
                                        ->where('status', 'pending')
                                        ->count(),
            'pending_amount' => Payment::whereBetween('payment_date', [$startDate, $endDate])
                                      ->where('status', 'pending')
                                      ->sum('amount'),
            'by_type' => Payment::whereBetween('payment_date', [$startDate, $endDate])
                               ->selectRaw('payment_type, COUNT(*) as count, SUM(amount) as total_amount')
                               ->groupBy('payment_type')
                               ->get(),
            'by_status' => Payment::whereBetween('payment_date', [$startDate, $endDate])
                                 ->selectRaw('status, COUNT(*) as count, SUM(amount) as total_amount')
                                 ->groupBy('status')
                                 ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Get payment options for dropdowns
     */
    public function getPaymentOptions(): JsonResponse
    {
        $bankAccounts = BankAccount::where('is_active', true)
                                  ->select('id', 'account_name', 'bank_name', 'account_number')
                                  ->get();

        $suppliers = Supplier::where('is_active', true)
                            ->select('id', 'name', 'company_name')
                            ->get()
                            ->map(function ($supplier) {
                                $supplier->name = $supplier->company_name ?: $supplier->name;
                                return $supplier;
                            });

        $employees = Employee::active()
                            ->select('id', 'first_name', 'last_name')
                            ->get()
                            ->map(function ($employee) {
                                $employee->name = $employee->first_name . ' ' . $employee->last_name;
                                return $employee;
                            });

        $customers = Customer::where('is_active', true)
                            ->select('id', 'name')
                            ->get();

        return response()->json([
            'bank_accounts' => $bankAccounts,
            'suppliers' => $suppliers,
            'employees' => $employees,
            'customers' => $customers,
            'payment_types' => [
                ['value' => 'supplier_payment', 'label' => 'Supplier Payment'],
                ['value' => 'expense_payment', 'label' => 'Expense Payment'],
                ['value' => 'salary_payment', 'label' => 'Salary Payment'],
                ['value' => 'sale_return_payment', 'label' => 'Sale Return Payment'],
                ['value' => 'purchase_invoice_payment', 'label' => 'Purchase Invoice Payment'],
                ['value' => 'other_payment', 'label' => 'Other Payment'],
            ],
            'payment_methods' => [
                ['value' => 'cash', 'label' => 'Cash'],
                ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
                ['value' => 'check', 'label' => 'Check'],
                ['value' => 'card', 'label' => 'Card'],
            ],
            'statuses' => [
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'approved', 'label' => 'Approved'],
                ['value' => 'paid', 'label' => 'Paid'],
                ['value' => 'cancelled', 'label' => 'Cancelled'],
            ],
        ]);
    }

    /**
     * Validate that payee exists
     */
    protected function validatePayeeExists(string $payeeType, int $payeeId): void
    {
        $models = [
            'supplier' => Supplier::class,
            'employee' => Employee::class,
            'customer' => Customer::class,
        ];

        if (isset($models[$payeeType])) {
            $model = $models[$payeeType];
            if (!$model::find($payeeId)) {
                throw new \InvalidArgumentException("Invalid {$payeeType} ID: {$payeeId}");
            }
        }
    }
}
