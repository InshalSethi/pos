<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseAuditLog;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Services\ExpenseAccountingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    private ExpenseAccountingService $accountingService;

    public function __construct(ExpenseAccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
        $this->middleware('auth:sanctum');
        $this->middleware('permission:expenses.view')->only(['index', 'show', 'statistics']);
        $this->middleware('permission:expenses.create')->only(['store']);
        $this->middleware('permission:expenses.edit')->only(['update']);
        $this->middleware('permission:expenses.delete')->only(['destroy']);
        $this->middleware('permission:expenses.approve')->only(['approve', 'reject']);
        $this->middleware('permission:expenses.pay')->only(['markAsPaid']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Expense::with([
            'category',
            'employee',
            'user',
            'submittedBy',
            'approvedBy',
            'rejectedBy',
            'paidBy'
        ]);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('expense_number', 'like', "%{$search}%")
                  ->orWhere('vendor_name', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        // Filter by employee
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->get('employee_id'));
        }

        // Filter by user (creator)
        if ($request->has('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('expense_date', '>=', $request->get('start_date'));
        }
        if ($request->has('end_date')) {
            $query->where('expense_date', '<=', $request->get('end_date'));
        }

        // Filter by amount range
        if ($request->has('min_amount')) {
            $query->where('amount', '>=', $request->get('min_amount'));
        }
        if ($request->has('max_amount')) {
            $query->where('amount', '<=', $request->get('max_amount'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'expense_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $expenses = $query->paginate($request->get('per_page', 15));

        return response()->json($expenses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:expense_categories,id',
            'employee_id' => 'nullable|exists:employees,id',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'receipt_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'payment_method' => 'nullable|in:cash,bank_transfer,credit_card,check,petty_cash',
            'reference_number' => 'nullable|string|max:255',
            'vendor_name' => 'nullable|string|max:255',
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

            $expenseData = $request->except(['receipt_image', 'receipt_images']);
            $expenseData['expense_number'] = Expense::generateExpenseNumber();
            $expenseData['user_id'] = auth()->id();

            // Handle single receipt image
            if ($request->hasFile('receipt_image')) {
                $expenseData['receipt_image'] = $request->file('receipt_image')->store('expenses/receipts', 'public');
            }

            // Handle multiple receipt images
            if ($request->hasFile('receipt_images')) {
                $receiptImages = [];
                foreach ($request->file('receipt_images') as $image) {
                    $receiptImages[] = $image->store('expenses/receipts', 'public');
                }
                $expenseData['receipt_images'] = $receiptImages;
            }

            $expense = Expense::create($expenseData);
            $expense->load(['category', 'employee', 'user']);

            // Log the creation
            ExpenseAuditLog::logExpenseChange(
                $expense,
                'created',
                [],
                $expenseData,
                'Expense created'
            );

            DB::commit();

            return response()->json([
                'message' => 'Expense created successfully',
                'expense' => $expense
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense): JsonResponse
    {
        $expense->load([
            'category',
            'employee',
            'user',
            'submittedBy',
            'approvedBy',
            'rejectedBy',
            'paidBy',
            'journalEntry'
        ]);

        return response()->json($expense);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense): JsonResponse
    {
        // Store original expense data for accounting reversal if needed
        $originalExpense = $expense->replicate();
        $wasApprovedOrPaid = in_array($expense->status, ['approved', 'paid']);

        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:expense_categories,id',
            'employee_id' => 'nullable|exists:employees,id',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'receipt_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'payment_method' => 'nullable|in:cash,bank_transfer,credit_card,check,petty_cash',
            'reference_number' => 'nullable|string|max:255',
            'vendor_name' => 'nullable|string|max:255',
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

            $expenseData = $request->except(['receipt_image', 'receipt_images']);

            // Handle single receipt image
            if ($request->hasFile('receipt_image')) {
                // Delete old image if exists
                if ($expense->receipt_image) {
                    Storage::disk('public')->delete($expense->receipt_image);
                }
                $expenseData['receipt_image'] = $request->file('receipt_image')->store('expenses/receipts', 'public');
            }

            // Handle multiple receipt images
            if ($request->hasFile('receipt_images')) {
                // Delete old images if exist
                if ($expense->receipt_images) {
                    foreach ($expense->receipt_images as $image) {
                        Storage::disk('public')->delete($image);
                    }
                }
                $receiptImages = [];
                foreach ($request->file('receipt_images') as $image) {
                    $receiptImages[] = $image->store('expenses/receipts', 'public');
                }
                $expenseData['receipt_images'] = $receiptImages;
            }

            // If expense was approved or paid, reverse accounting entries before updating
            if ($wasApprovedOrPaid) {
                $this->reverseExpenseAccounting($originalExpense);
            }

            $expense->update($expenseData);

            // If the updated expense should be approved or paid, recreate accounting entries
            if (in_array($expense->status, ['approved', 'paid'])) {
                $this->recreateExpenseAccounting($expense);
            }

            // Log the update
            ExpenseAuditLog::logExpenseChange(
                $expense,
                'updated',
                $originalExpense->toArray(),
                $expenseData,
                $wasApprovedOrPaid ? 'Expense updated with accounting reversal' : 'Expense updated'
            );

            $expense->load(['category', 'employee', 'user']);

            DB::commit();

            return response()->json([
                'message' => 'Expense updated successfully',
                'expense' => $expense
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Store expense data for audit log before deletion
            $expenseData = $expense->toArray();
            $affectedAccounting = in_array($expense->status, ['approved', 'paid']);

            // Clean up accounting records if expense was approved or paid
            if ($affectedAccounting) {
                $this->cleanupExpenseAccounting($expense);
            }

            // Log the deletion before actually deleting
            ExpenseAuditLog::logExpenseChange(
                $expense,
                'deleted',
                $expenseData,
                [],
                $affectedAccounting ? 'Expense deleted with accounting cleanup' : 'Expense deleted'
            );

            // Delete receipt images
            if ($expense->receipt_image) {
                Storage::disk('public')->delete($expense->receipt_image);
            }
            if ($expense->receipt_images) {
                foreach ($expense->receipt_images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            $expense->delete();

            DB::commit();

            return response()->json([
                'message' => 'Expense deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to delete expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit expense for approval
     */
    public function submit(Expense $expense): JsonResponse
    {
        if (!$expense->canBeSubmitted()) {
            return response()->json([
                'message' => 'Expense cannot be submitted in its current status'
            ], 422);
        }

        $expense->submit();
        $expense->load(['category', 'employee', 'user', 'submittedBy']);

        return response()->json([
            'message' => 'Expense submitted for approval successfully',
            'expense' => $expense
        ]);
    }

    /**
     * Approve expense
     */
    public function approve(Request $request, Expense $expense): JsonResponse
    {
        if (!$expense->canBeApproved()) {
            return response()->json([
                'message' => 'Expense cannot be approved in its current status'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'approval_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldStatus = $expense->status;
        $expense->approve(auth()->id(), $request->get('approval_notes'));

        // Log the approval
        ExpenseAuditLog::logExpenseChange(
            $expense,
            'approved',
            ['status' => $oldStatus],
            ['status' => 'approved'],
            'Expense approved: ' . ($request->get('approval_notes') ?: 'No notes provided')
        );

        $expense->load(['category', 'employee', 'user', 'approvedBy']);

        return response()->json([
            'message' => 'Expense approved successfully',
            'expense' => $expense
        ]);
    }

    /**
     * Reject expense
     */
    public function reject(Request $request, Expense $expense): JsonResponse
    {
        if (!$expense->canBeRejected()) {
            return response()->json([
                'message' => 'Expense cannot be rejected in its current status'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldStatus = $expense->status;
        $expense->reject(auth()->id(), $request->get('rejection_reason'));

        // Log the rejection
        ExpenseAuditLog::logExpenseChange(
            $expense,
            'rejected',
            ['status' => $oldStatus],
            ['status' => 'rejected'],
            'Expense rejected: ' . ($request->get('rejection_reason') ?: 'No reason provided')
        );

        $expense->load(['category', 'employee', 'user', 'rejectedBy']);

        return response()->json([
            'message' => 'Expense rejected successfully',
            'expense' => $expense
        ]);
    }

    /**
     * Mark expense as paid
     */
    public function markAsPaid(Request $request, Expense $expense): JsonResponse
    {
        if (!$expense->canBePaid()) {
            return response()->json([
                'message' => 'Expense cannot be marked as paid in its current status'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payment_reference' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Mark expense as paid (without firing event yet)
            $oldStatus = $expense->status;
            $expense->update([
                'status' => 'paid',
                'paid_by' => auth()->id(),
                'paid_at' => now(),
                'payment_reference' => $request->get('payment_reference'),
            ]);

            // Create bank transaction
            $this->createBankTransaction($expense, $request->get('bank_account_id'), $request->get('payment_reference'));

            // Log the payment
            ExpenseAuditLog::logExpenseChange(
                $expense,
                'paid',
                ['status' => $oldStatus],
                ['status' => 'paid', 'bank_account_id' => $request->get('bank_account_id')],
                'Expense marked as paid with bank account ID: ' . $request->get('bank_account_id')
            );

            // Fire event with bank account information
            event(new \App\Events\ExpensePaid($expense, $request->get('bank_account_id')));

            DB::commit();

            $expense->load(['category', 'employee', 'user', 'paidBy']);

            return response()->json([
                'message' => 'Expense marked as paid successfully',
                'expense' => $expense
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to mark expense as paid',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get expense statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        $stats = [
            'total_expenses' => Expense::whereBetween('expense_date', [$startDate, $endDate])->count(),
            'total_amount' => Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount'),
            'pending_approval' => Expense::whereBetween('expense_date', [$startDate, $endDate])->where('status', 'submitted')->count(),
            'approved_amount' => Expense::whereBetween('expense_date', [$startDate, $endDate])->where('status', 'approved')->sum('amount'),
            'paid_amount' => Expense::whereBetween('expense_date', [$startDate, $endDate])->where('status', 'paid')->sum('amount'),
            'by_category' => Expense::with('category')
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->selectRaw('category_id, SUM(amount) as total_amount, COUNT(*) as count')
                ->groupBy('category_id')
                ->get(),
            'by_status' => Expense::whereBetween('expense_date', [$startDate, $endDate])
                ->selectRaw('status, SUM(amount) as total_amount, COUNT(*) as count')
                ->groupBy('status')
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Create bank transaction for expense payment
     */
    private function createBankTransaction(Expense $expense, int $bankAccountId, ?string $paymentReference): BankTransaction
    {
        $bankAccount = BankAccount::findOrFail($bankAccountId);

        // Calculate running balance
        $lastTransaction = BankTransaction::where('bank_account_id', $bankAccountId)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $currentBalance = $lastTransaction ? $lastTransaction->running_balance : $bankAccount->opening_balance;
        $newBalance = $currentBalance - $expense->amount; // Debit reduces balance

        return BankTransaction::create([
            'bank_account_id' => $bankAccountId,
            'transaction_date' => $expense->paid_at ? $expense->paid_at->toDateString() : now()->toDateString(),
            'transaction_type' => 'debit', // Money going out of bank account
            'amount' => $expense->amount,
            'description' => "Payment for expense: {$expense->title}",
            'reference_number' => $paymentReference ?: $expense->expense_number,
            'running_balance' => $newBalance,
            'status' => 'cleared',
            'partner_type' => 'App\Models\Expense',
            'partner_id' => $expense->id,
        ]);
    }

    /**
     * Reverse accounting entries for an expense
     */
    private function reverseExpenseAccounting(Expense $expense): void
    {
        $this->accountingService->removeExpenseAccounting($expense);
    }

    /**
     * Recreate accounting entries for an updated expense
     */
    private function recreateExpenseAccounting(Expense $expense): void
    {
        if ($expense->status === 'approved') {
            // Fire approval event to recreate journal entry
            event(new \App\Events\ExpenseApproved($expense));
        } elseif ($expense->status === 'paid') {
            // Fire approval event first, then paid event
            event(new \App\Events\ExpenseApproved($expense));
            event(new \App\Events\ExpensePaid($expense));
        }
    }

    /**
     * Clean up all accounting records for an expense being deleted
     */
    private function cleanupExpenseAccounting(Expense $expense): void
    {
        $this->accountingService->removeExpenseAccounting($expense);
    }
}
