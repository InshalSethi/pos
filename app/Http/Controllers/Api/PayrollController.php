<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PayrollRecord;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * Display a listing of payroll records
     */
    public function index(Request $request): JsonResponse
    {
        $query = PayrollRecord::with(['employee', 'employeeSalary', 'createdBy', 'approvedBy', 'paidBy']);

        // Filter by employee
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by pay period
        if ($request->has('pay_period_start')) {
            $query->where('pay_period_start', '>=', $request->pay_period_start);
        }

        if ($request->has('pay_period_end')) {
            $query->where('pay_period_end', '<=', $request->pay_period_end);
        }

        // Filter by pay date range
        if ($request->has('pay_date_from')) {
            $query->where('pay_date', '>=', $request->pay_date_from);
        }

        if ($request->has('pay_date_to')) {
            $query->where('pay_date', '<=', $request->pay_date_to);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payroll_number', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($empQuery) use ($search) {
                      $empQuery->where('first_name', 'like', "%{$search}%")
                               ->orWhere('last_name', 'like', "%{$search}%")
                               ->orWhere('employee_number', 'like', "%{$search}%");
                  });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'pay_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $payrolls = $query->paginate($perPage);

        return response()->json($payrolls);
    }

    /**
     * Generate payroll for a single employee
     */
    public function generateForEmployee(Request $request, Employee $employee): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pay_period_start' => 'required|date',
            'pay_period_end' => 'required|date|after_or_equal:pay_period_start',
            'pay_date' => 'nullable|date|after_or_equal:pay_period_end',
            'overtime_hours' => 'nullable|numeric|min:0',
            'bonuses' => 'nullable|numeric|min:0',
            'commissions' => 'nullable|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'insurance_deductions' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:bank_transfer,cash,check',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $payPeriodStart = Carbon::parse($request->pay_period_start);
            $payPeriodEnd = Carbon::parse($request->pay_period_end);

            // Check if payroll already exists for this period
            $existingPayroll = PayrollRecord::where('employee_id', $employee->id)
                                          ->where('pay_period_start', $payPeriodStart)
                                          ->where('pay_period_end', $payPeriodEnd)
                                          ->first();

            if ($existingPayroll) {
                return response()->json([
                    'message' => 'Payroll already exists for this employee and period'
                ], 422);
            }

            $additionalData = $request->only([
                'pay_date',
                'overtime_hours',
                'bonuses',
                'commissions',
                'allowances',
                'insurance_deductions',
                'other_deductions',
                'payment_method',
                'notes'
            ]);

            $payroll = $this->payrollService->generatePayrollForEmployee(
                $employee,
                $payPeriodStart,
                $payPeriodEnd,
                $additionalData
            );

            $payroll->load(['employee', 'employeeSalary', 'createdBy']);

            return response()->json([
                'message' => 'Payroll generated successfully',
                'payroll' => $payroll
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate payroll',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate payroll for all employees
     */
    public function generateForAllEmployees(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pay_period_start' => 'required|date',
            'pay_period_end' => 'required|date|after_or_equal:pay_period_start',
            'employee_overrides' => 'nullable|array',
            'employee_overrides.*.employee_id' => 'required|exists:employees,id',
            'employee_overrides.*.overtime_hours' => 'nullable|numeric|min:0',
            'employee_overrides.*.bonuses' => 'nullable|numeric|min:0',
            'employee_overrides.*.commissions' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $payPeriodStart = Carbon::parse($request->pay_period_start);
            $payPeriodEnd = Carbon::parse($request->pay_period_end);

            // Prepare employee overrides
            $employeeOverrides = [];
            if ($request->has('employee_overrides')) {
                foreach ($request->employee_overrides as $override) {
                    $employeeOverrides[$override['employee_id']] = $override;
                }
            }

            $payrolls = $this->payrollService->generatePayrollForAllEmployees(
                $payPeriodStart,
                $payPeriodEnd,
                $employeeOverrides
            );

            return response()->json([
                'message' => 'Payroll generated for all employees',
                'payrolls_generated' => count($payrolls),
                'payrolls' => $payrolls
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate payroll for all employees',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified payroll record
     */
    public function show(PayrollRecord $payroll): JsonResponse
    {
        $payroll->load([
            'employee',
            'employeeSalary',
            'createdBy',
            'approvedBy',
            'paidBy',
            'journalEntry.journalEntryLines.account'
        ]);

        return response()->json($payroll);
    }

    /**
     * Update the specified payroll record
     */
    public function update(Request $request, PayrollRecord $payroll): JsonResponse
    {
        if (!$payroll->can_edit) {
            return response()->json([
                'message' => 'This payroll record cannot be edited'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'overtime_hours' => 'nullable|numeric|min:0',
            'bonuses' => 'nullable|numeric|min:0',
            'commissions' => 'nullable|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'insurance_deductions' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:bank_transfer,cash,check',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $payroll->update($request->only([
            'overtime_hours',
            'bonuses',
            'commissions',
            'allowances',
            'insurance_deductions',
            'other_deductions',
            'payment_method',
            'notes'
        ]));

        // Recalculate overtime pay if overtime hours changed
        if ($request->has('overtime_hours')) {
            $salary = $payroll->employeeSalary;
            $overtimeRate = $salary->overtime_rate ?? ($salary->hourly_rate * 1.5);
            $payroll->overtime_pay = $payroll->overtime_hours * $overtimeRate;
        }

        // Update calculated fields
        $payroll->updateCalculatedFields();

        $payroll->load(['employee', 'employeeSalary', 'createdBy']);

        return response()->json([
            'message' => 'Payroll record updated successfully',
            'payroll' => $payroll
        ]);
    }

    /**
     * Approve payroll record
     */
    public function approve(PayrollRecord $payroll): JsonResponse
    {
        if (!$payroll->can_approve) {
            return response()->json([
                'message' => 'This payroll record cannot be approved'
            ], 422);
        }

        $payroll->approve();

        return response()->json([
            'message' => 'Payroll record approved successfully',
            'payroll' => $payroll
        ]);
    }

    /**
     * Mark payroll as paid and create journal entry
     */
    public function markAsPaid(Request $request, PayrollRecord $payroll): JsonResponse
    {
        if (!$payroll->can_pay) {
            return response()->json([
                'message' => 'This payroll record cannot be marked as paid'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'payment_reference' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::transaction(function () use ($payroll, $request) {
                // Create journal entry
                $journalEntry = $this->payrollService->createPayrollJournalEntry($payroll);

                // Mark as paid
                $payroll->markAsPaid(auth()->id(), $request->payment_reference);
            });

            $payroll->load(['employee', 'employeeSalary', 'paidBy', 'journalEntry']);

            return response()->json([
                'message' => 'Payroll marked as paid successfully',
                'payroll' => $payroll
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to mark payroll as paid',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel payroll record
     */
    public function cancel(PayrollRecord $payroll): JsonResponse
    {
        if (!$payroll->can_cancel) {
            return response()->json([
                'message' => 'This payroll record cannot be cancelled'
            ], 422);
        }

        $payroll->cancel();

        return response()->json([
            'message' => 'Payroll record cancelled successfully',
            'payroll' => $payroll
        ]);
    }

    /**
     * Get payroll statistics
     */
    public function getStatistics(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $stats = [
            'total_payrolls' => PayrollRecord::whereBetween('pay_date', [$startDate, $endDate])->count(),
            'total_gross_pay' => PayrollRecord::whereBetween('pay_date', [$startDate, $endDate])->sum('gross_pay'),
            'total_net_pay' => PayrollRecord::whereBetween('pay_date', [$startDate, $endDate])->sum('net_pay'),
            'total_deductions' => PayrollRecord::whereBetween('pay_date', [$startDate, $endDate])->sum('total_deductions'),
            'average_gross_pay' => PayrollRecord::whereBetween('pay_date', [$startDate, $endDate])->avg('gross_pay'),
            'payrolls_by_status' => PayrollRecord::whereBetween('pay_date', [$startDate, $endDate])
                                                ->selectRaw('status, count(*) as count')
                                                ->groupBy('status')
                                                ->pluck('count', 'status'),
        ];

        return response()->json($stats);
    }
}
