<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\BankAccount;
use App\Models\Supplier;
use App\Models\Employee;
use App\Models\Customer;
use App\Models\ExpenseCategory;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;

        // Apply permission middleware
        $this->middleware('permission:payments.view')->only(['index', 'show', 'statistics', 'downloadAttachment']);
        $this->middleware('permission:payments.create')->only(['store']);
        $this->middleware('permission:payments.edit')->only(['update', 'approve', 'markAsPaid']);
        $this->middleware('permission:payments.delete')->only(['destroy']);
    }

    /**
     * Display listing of payments
     */
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with([
            'bankAccount',
            'expenseCategory',
            'createdBy:id,name',
            'approvedBy:id,name',
            'paidBy:id,name',
            'journalEntry.journalEntryLines.account',
            'bankTransaction'
        ]);

        // Filter by payment type
        if ($request->has('payment_type') && $request->payment_type) {
            $query->where('payment_type', $request->payment_type);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by payment method
        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by bank account
        if ($request->has('bank_account_id') && $request->bank_account_id) {
            $query->where('bank_account_id', $request->bank_account_id);
        }

        // Filter by payee type
        if ($request->has('payee_type') && $request->payee_type) {
            $query->where('payee_type', $request->payee_type);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('payment_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }

        // Search in payee name, payment number, reference number, description
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payee_name', 'like', "%{$search}%")
                    ->orWhere('payment_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_dir', 'desc');
        $query->orderBy($sortField, $sortDirection);

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
            'payment_type' => [
                'required',
                Rule::in([
                    'supplier_payment',
                    'expense_payment',
                    'salary_payment',
                    'sale_return_payment',
                    'purchase_invoice_payment',
                    'other_payment'
                ])
            ],
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
            'expense_category_id' => [
                Rule::requiredIf(fn() => $request->payment_type === 'expense_payment'),
                'nullable',
                'exists:expense_categories,id'
            ],
            'status' => 'required|string|in:draft,pending,process,rejected,completed,paid',
            'additional_data' => 'nullable|array',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
        ]);

        // Validate payee exists if payee_type and payee_id are provided
        if (!empty($validated['payee_type']) && !empty($validated['payee_id'])) {
            $this->validatePayeeExists($validated['payee_type'], $validated['payee_id']);
        }

        // Handle attachment file upload
        $uploadedPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file && $file->isValid()) {
                    $uploadedPaths[] = $this->storeAttachmentFile($file, 'payments/attachments');
                }
            }
        } elseif ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            if ($file && $file->isValid()) {
                $uploadedPaths[] = $this->storeAttachmentFile($file, 'payments/attachments');
            }
        }

        if (!empty($uploadedPaths)) {
            $validated['attachments'] = $uploadedPaths;
            $validated['attachment'] = $uploadedPaths[0];
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
            $statusCode = str_contains($e->getMessage(), 'Insufficient balance') ? 422 : 500;
            return response()->json([
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ], $statusCode);
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
        // Check if payment can be edited (Only draft status)
        if (strtolower($payment->status) !== 'draft') {
            return response()->json([
                'message' => 'Payment cannot be edited in its current status. Only draft records can be edited.'
            ], 422);
        }

        $validated = $request->validate([
            'payment_type' => [
                'sometimes',
                Rule::in([
                    'supplier_payment',
                    'expense_payment',
                    'salary_payment',
                    'sale_return_payment',
                    'purchase_invoice_payment',
                    'other_payment'
                ])
            ],
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
            'expense_category_id' => [
                Rule::requiredIf(fn() => $request->payment_type === 'expense_payment'),
                'nullable',
                'exists:expense_categories,id'
            ],
            'status' => 'sometimes|string|in:draft,pending,process,rejected,completed,approved,paid,cancelled',
            'additional_data' => 'nullable|array',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
            'existing_attachments' => 'nullable|array',
        ]);

        // Validate payee exists if payee_type and payee_id are provided
        if (isset($validated['payee_type']) && isset($validated['payee_id'])) {
            $this->validatePayeeExists($validated['payee_type'], $validated['payee_id']);
        }

        // Keep existing attachments that user didn't remove
        $currentAttachments = $payment->attachments ?? ($payment->attachment ? [$payment->attachment] : []);
        $keptAttachments = [];
        if ($request->has('existing_attachments')) {
            $existing = (array) $request->input('existing_attachments');
            $keptAttachments = array_values(array_filter($currentAttachments, function ($path) use ($existing) {
                return in_array($path, $existing);
            }));
            foreach ($currentAttachments as $path) {
                if (!in_array($path, $existing) && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        } else {
            $keptAttachments = $currentAttachments;
        }

        // Upload new files
        $newPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file && $file->isValid()) {
                    $newPaths[] = $this->storeAttachmentFile($file, 'payments/attachments');
                }
            }
        } elseif ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            if ($file && $file->isValid()) {
                $newPaths[] = $this->storeAttachmentFile($file, 'payments/attachments');
            }
        }

        $allAttachments = array_values(array_merge($keptAttachments, $newPaths));
        $validated['attachments'] = $allAttachments;
        $validated['attachment'] = !empty($allAttachments) ? $allAttachments[0] : null;

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
     * Helper to store attachment preserving original extension
     */
    private function storeAttachmentFile($file, string $directory): string
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension() ?: ($file->guessExtension() ?: 'bin');
        $baseName = \Illuminate\Support\Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        if (empty($baseName)) {
            $baseName = 'attachment';
        }
        $filename = $baseName . '_' . time() . '_' . \Illuminate\Support\Str::random(6) . '.' . strtolower($extension);
        return $file->storeAs($directory, $filename, 'public');
    }

    /**
     * Download payment attachment
     */
    public function downloadAttachment(Request $request, Payment $payment)
    {
        $list = $payment->attachments ?? [];
        if (empty($list) && $payment->attachment) {
            $list = [$payment->attachment];
        }

        $index = (int) $request->get('index', 0);
        $targetPath = $list[$index] ?? ($list[0] ?? $payment->attachment);

        if (!$targetPath || !Storage::disk('public')->exists($targetPath)) {
            return response()->json([
                'message' => 'Attachment file not found'
            ], 404);
        }

        $fileName = basename($targetPath);
        return Storage::disk('public')->download($targetPath, $fileName);
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
     * Update payment status via state machine workflow
     */
    public function updateStatus(Request $request, Payment $payment): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:draft,process,processing,pending,rejected,cancelled,completed,paid,void',
        ]);

        $targetStatus = strtolower($validated['status']);
        if ($targetStatus === 'processing') {
            $targetStatus = 'process';
        }
        if ($targetStatus === 'void') {
            $targetStatus = 'cancelled';
        }
        if ($targetStatus === 'completed') {
            $targetStatus = 'paid';
        }

        $currentStatus = strtolower($payment->status);

        // Define strict state machine transition matrix
        $allowedTransitions = [
            'draft' => ['process', 'rejected', 'cancelled'],
            'process' => ['pending'],
            'pending' => ['rejected', 'completed', 'paid', 'cancelled'],
        ];

        // Terminal / Final states cannot transition to anything
        if (in_array($currentStatus, ['completed', 'rejected', 'cancelled', 'paid', 'void'])) {
            return response()->json([
                'message' => "This payment record is locked in a final state ({$currentStatus}) and cannot undergo any further status transitions."
            ], 422);
        }

        if (!isset($allowedTransitions[$currentStatus]) || !in_array($targetStatus, $allowedTransitions[$currentStatus])) {
            return response()->json([
                'message' => "Invalid status transition from {$currentStatus} to {$targetStatus}."
            ], 422);
        }

        try {
            $updateData = ['status' => $targetStatus];
            if ($targetStatus === 'paid' || $targetStatus === 'completed') {
                $updateData['paid_at'] = now();
                $updateData['paid_by'] = Auth::id();
            }

            $payment = $this->paymentService->updatePayment($payment, $updateData);

            $statusLabels = [
                'process' => 'Processing',
                'pending' => 'Pending',
                'rejected' => 'Rejected',
                'completed' => 'Paid',
                'paid' => 'Paid',
                'cancelled' => 'Cancelled',
            ];
            $label = $statusLabels[$targetStatus] ?? ucfirst($targetStatus);

            return response()->json([
                'message' => "Status updated to {$label} successfully!",
                'payment' => $payment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update payment status',
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
                // Reverse supplier allocation & accounting entries first
                $this->paymentService->reverseSupplierPaymentAllocation($payment);

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
        $bankAccounts = BankAccount::select('id', 'account_name', 'bank_name', 'account_number', 'account_type', 'is_default', 'is_active', 'current_balance', 'opening_balance')
            ->orderByDesc('is_default')
            ->orderBy('account_name')
            ->get();

        $suppliers = Supplier::where('is_active', true)
            ->select('id', 'name', 'company_name')
            ->get()
            ->map(function ($supplier) {
                $name = trim($supplier->name ?? '');
                $company = trim($supplier->company_name ?? '');
                if ($name && $company && strtolower($name) !== strtolower($company)) {
                    $supplier->name = "{$name} ({$company})";
                } else {
                    $supplier->name = $company ?: $name;
                }
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

        $expenseCategories = ExpenseCategory::where('is_active', true)
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        return response()->json([
            'bank_accounts' => $bankAccounts,
            'suppliers' => $suppliers,
            'employees' => $employees,
            'customers' => $customers,
            'expense_categories' => $expenseCategories,
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
                ['value' => 'check', 'label' => 'Cheque'],
                ['value' => 'card', 'label' => 'Card'],
            ],
            'statuses' => [
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'process', 'label' => 'Process'],
                ['value' => 'rejected', 'label' => 'Rejected'],
                ['value' => 'paid', 'label' => 'Paid'],
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
