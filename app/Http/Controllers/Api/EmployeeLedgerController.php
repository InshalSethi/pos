<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\PayrollRecord;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class EmployeeLedgerController extends Controller
{
    /**
     * Get overall Employee Ledger summary.
     */
    public function getLedger(Request $request, Employee $employee)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $openingBalance = $this->calculateOpeningBalance($employee, $startDate);
        $transactionsData = $this->getEmployeeTransactions($employee, $startDate, $endDate, $openingBalance);

        $totalDebits = collect($transactionsData['transactions'])->sum('debit');
        $totalCredits = collect($transactionsData['transactions'])->sum('credit');
        $closingBalance = $transactionsData['closing_balance'];

        return response()->json([
            'employee' => [
                'id' => $employee->id,
                'employee_number' => $employee->employee_number,
                'name' => $employee->full_name,
                'email' => $employee->email,
                'phone' => $employee->phone ?: $employee->mobile,
                'department' => $employee->department ? $employee->department->name : 'N/A',
                'position' => $employee->position ? $employee->position->name : 'N/A',
                'basic_salary' => (float)$employee->basic_salary,
                'hire_date' => $employee->hire_date ? $employee->hire_date->format('Y-m-d') : null,
                'status' => $employee->employment_status,
                'bank_name' => $employee->bank_name,
                'bank_account_number' => $employee->bank_account_number,
            ],
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'opening_balance' => (float)$openingBalance,
            'closing_balance' => (float)$closingBalance,
            'stats' => [
                'total_debits' => (float)$totalDebits,
                'total_credits' => (float)$totalCredits,
                'net_balance' => (float)$closingBalance,
            ],
        ]);
    }

    /**
     * Get paginated Employee Transactions.
     */
    public function getTransactions(Request $request, Employee $employee)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $search = $request->query('search');
        $page = (int)$request->query('page', 1);
        $perPage = (int)$request->query('per_page', 10);

        $openingBalance = $this->calculateOpeningBalance($employee, $startDate);
        $allTx = $this->getEmployeeTransactions($employee, $startDate, $endDate, $openingBalance)['transactions'];

        // Filter search
        if (!empty($search)) {
            $searchLower = strtolower($search);
            $allTx = array_values(array_filter($allTx, function ($tx) use ($searchLower) {
                return str_contains(strtolower($tx['reference']), $searchLower) ||
                       str_contains(strtolower($tx['description']), $searchLower) ||
                       str_contains(strtolower($tx['type']), $searchLower);
            }));
        }

        $total = count($allTx);
        $lastPage = (int)ceil($total / $perPage) ?: 1;
        $offset = ($page - 1) * $perPage;
        $paginatedItems = array_slice($allTx, $offset, $perPage);

        return response()->json([
            'data' => array_values($paginatedItems),
            'current_page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
            'from' => $total > 0 ? $offset + 1 : 0,
            'to' => $total > 0 ? min($offset + $perPage, $total) : 0,
        ]);
    }

    /**
     * Export Employee General Ledger PDF.
     */
    public function exportPdf(Request $request, Employee $employee)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $openingBalance = $this->calculateOpeningBalance($employee, $startDate);
        $transactionsData = $this->getEmployeeTransactions($employee, $startDate, $endDate, $openingBalance);

        $transactions = $transactionsData['transactions'];
        $closingBalance = $transactionsData['closing_balance'];
        $totalDebits = collect($transactions)->sum('debit');
        $totalCredits = collect($transactions)->sum('credit');

        $pdf = Pdf::loadView('pdf.employee_ledger', [
            'employee' => $employee,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'openingBalance' => $openingBalance,
            'closingBalance' => $closingBalance,
            'totalDebits' => $totalDebits,
            'totalCredits' => $totalCredits,
            'transactions' => $transactions,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $filename = 'employee_ledger_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $employee->full_name)) . '_' . now()->format('Y_m_d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Calculate opening balance prior to start date.
     */
    private function calculateOpeningBalance(Employee $employee, ?string $asOfDate): float
    {
        if (!$asOfDate) {
            return 0.00;
        }

        $userId = $employee->user_id;

        // Prior Banking Transactions
        $priorTxExpense = Transaction::where(function ($q) use ($employee, $userId) {
                $q->where('employee_id', $employee->id);
                if ($userId) {
                    $q->orWhere('customer_id', $userId)->orWhere('vendor_id', $userId);
                }
            })
            ->whereDate('paid_at', '<', $asOfDate)
            ->where('type', 'expense')
            ->sum('amount');

        $priorTxIncome = Transaction::where(function ($q) use ($employee, $userId) {
                $q->where('employee_id', $employee->id);
                if ($userId) {
                    $q->orWhere('customer_id', $userId)->orWhere('vendor_id', $userId);
                }
            })
            ->whereDate('paid_at', '<', $asOfDate)
            ->where('type', 'income')
            ->sum('amount');

        // Tracked transaction numbers to avoid duplicate counting
        $trackedTxNumbers = Transaction::where(function ($q) use ($employee, $userId) {
                $q->where('employee_id', $employee->id);
                if ($userId) {
                    $q->orWhere('customer_id', $userId)->orWhere('vendor_id', $userId);
                }
            })
            ->whereNotNull('number')
            ->pluck('number')
            ->toArray();

        // Prior Paid Expenses
        $priorExpenses = Expense::where('employee_id', $employee->id)
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereDate('paid_at', '<', $asOfDate)
            ->get();

        $priorExpenseTotal = 0;
        foreach ($priorExpenses as $exp) {
            if ($exp->payment_reference && in_array($exp->payment_reference, $trackedTxNumbers)) {
                continue;
            }
            $priorExpenseTotal += (float)$exp->amount;
        }

        // Prior Payroll Payments
        $priorPayrollRecords = PayrollRecord::where('employee_id', $employee->id)
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereDate('paid_at', '<', $asOfDate)
            ->get();

        $priorPayrollTotal = 0;
        foreach ($priorPayrollRecords as $pay) {
            $priorPayrollTotal += (float)($pay->net_salary ?? 0);
        }

        // Opening Balance calculation: Payments (Expenses + Payroll + Tx Expense) - Credits (Tx Income)
        return (float)(($priorTxExpense + $priorExpenseTotal + $priorPayrollTotal) - $priorTxIncome);
    }

    /**
     * Get aggregated transactions with running balances.
     */
    private function getEmployeeTransactions(Employee $employee, ?string $startDate, ?string $endDate, float $openingBalance): array
    {
        $userId = $employee->user_id;
        $transactions = [];

        // 1. Banking Transactions
        $bankingQuery = Transaction::where(function ($q) use ($employee, $userId) {
            $q->where('employee_id', $employee->id);
            if ($userId) {
                $q->orWhere('customer_id', $userId)->orWhere('vendor_id', $userId);
            }
        });

        if ($startDate) {
            $bankingQuery->whereDate('paid_at', '>=', $startDate);
        }
        if ($endDate) {
            $bankingQuery->whereDate('paid_at', '<=', $endDate);
        }

        $bankingTx = $bankingQuery->orderBy('paid_at', 'asc')->get();
        $trackedNumbers = [];

        foreach ($bankingTx as $tx) {
            if ($tx->number) {
                $trackedNumbers[] = $tx->number;
            }
            $isExpense = strtolower($tx->type) === 'expense';
            $transactions[] = [
                'date' => $tx->paid_at ? $tx->paid_at->format('Y-m-d') : null,
                'reference' => $tx->number ?: $tx->reference ?: 'TX-' . $tx->id,
                'description' => $tx->description ?: ($isExpense ? 'Payment to employee' : 'Receipt from employee'),
                'type' => strtoupper($tx->type),
                'debit' => $isExpense ? (float)$tx->amount : 0,
                'credit' => !$isExpense ? (float)$tx->amount : 0,
                'raw_date' => $tx->paid_at ? $tx->paid_at->timestamp : 0,
            ];
        }

        // 2. Paid Expenses
        $expenseQuery = Expense::where('employee_id', $employee->id)
            ->where('status', 'paid')
            ->whereNotNull('paid_at');

        if ($startDate) {
            $expenseQuery->whereDate('paid_at', '>=', $startDate);
        }
        if ($endDate) {
            $expenseQuery->whereDate('paid_at', '<=', $endDate);
        }

        $expenses = $expenseQuery->orderBy('paid_at', 'asc')->get();
        foreach ($expenses as $exp) {
            if ($exp->payment_reference && in_array($exp->payment_reference, $trackedNumbers)) {
                continue;
            }
            $transactions[] = [
                'date' => $exp->paid_at ? $exp->paid_at->format('Y-m-d') : null,
                'reference' => $exp->expense_number ?: $exp->payment_reference ?: 'EXP-' . $exp->id,
                'description' => 'Expense Reimbursement: ' . ($exp->title ?: $exp->description ?: 'Employee Expense'),
                'type' => 'EXPENSE',
                'debit' => (float)$exp->amount,
                'credit' => 0,
                'raw_date' => $exp->paid_at ? $exp->paid_at->timestamp : 0,
            ];
        }

        // 3. Paid Payroll Records
        $payrollQuery = PayrollRecord::where('employee_id', $employee->id)
            ->where('status', 'paid')
            ->whereNotNull('paid_at');

        if ($startDate) {
            $payrollQuery->whereDate('paid_at', '>=', $startDate);
        }
        if ($endDate) {
            $payrollQuery->whereDate('paid_at', '<=', $endDate);
        }

        $payrolls = $payrollQuery->orderBy('paid_at', 'asc')->get();
        foreach ($payrolls as $pay) {
            $transactions[] = [
                'date' => $pay->paid_at ? Carbon::parse($pay->paid_at)->format('Y-m-d') : null,
                'reference' => 'PAY-' . $pay->id,
                'description' => 'Salary Disbursement: ' . ($pay->pay_period ? $pay->pay_period : 'Payroll Record'),
                'type' => 'PAYROLL',
                'debit' => (float)($pay->net_salary ?? 0),
                'credit' => 0,
                'raw_date' => $pay->paid_at ? Carbon::parse($pay->paid_at)->timestamp : 0,
            ];
        }

        // Sort chronologically
        usort($transactions, function ($a, $b) {
            return $a['raw_date'] <=> $b['raw_date'];
        });

        // Compute running balance
        $currentBalance = $openingBalance;
        foreach ($transactions as &$item) {
            $currentBalance += ($item['debit'] - $item['credit']);
            $item['running_balance'] = (float)$currentBalance;
        }

        return [
            'transactions' => $transactions,
            'closing_balance' => (float)$currentBalance,
        ];
    }
}
