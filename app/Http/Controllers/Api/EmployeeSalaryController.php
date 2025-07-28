<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\SalaryAdjustment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class EmployeeSalaryController extends Controller
{
    /**
     * Display a listing of employee salaries
     */
    public function index(Request $request): JsonResponse
    {
        $query = EmployeeSalary::with(['employee', 'createdBy', 'approvedBy']);

        // Filter by employee
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by effective date range
        if ($request->has('effective_date_from')) {
            $query->where('effective_date', '>=', $request->effective_date_from);
        }

        if ($request->has('effective_date_to')) {
            $query->where('effective_date', '<=', $request->effective_date_to);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_number', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'effective_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $salaries = $query->paginate($perPage);

        return response()->json($salaries);
    }

    /**
     * Store a newly created salary record
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'basic_salary' => 'required|numeric|min:0',
            'salary_type' => 'required|in:monthly,hourly,daily',
            'hourly_rate' => 'nullable|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'effective_date' => 'required|date',
            'adjustment_reason' => 'nullable|string|max:1000',
            'allowance_breakdown' => 'nullable|array',
            'deduction_breakdown' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        return DB::transaction(function () use ($request) {
            // Check if employee already has an active salary
            $existingSalary = EmployeeSalary::where('employee_id', $request->employee_id)
                                          ->where('status', 'active')
                                          ->first();

            // Create new salary record
            $salary = EmployeeSalary::create([
                'employee_id' => $request->employee_id,
                'basic_salary' => $request->basic_salary,
                'salary_type' => $request->salary_type,
                'hourly_rate' => $request->hourly_rate,
                'overtime_rate' => $request->overtime_rate,
                'allowances' => $request->allowances ?? 0,
                'deductions' => $request->deductions ?? 0,
                'effective_date' => $request->effective_date,
                'adjustment_reason' => $request->adjustment_reason,
                'allowance_breakdown' => $request->allowance_breakdown,
                'deduction_breakdown' => $request->deduction_breakdown,
                'created_by' => auth()->id(),
                'notes' => $request->notes,
            ]);

            // Update calculated fields
            $salary->updateCalculatedFields();

            // If this is effective immediately and there's an existing salary, supersede it
            if ($existingSalary && $request->effective_date <= now()->toDateString()) {
                $existingSalary->supersede($request->effective_date);
                $salary->update(['status' => 'active']);
            }

            $salary->load(['employee', 'createdBy']);

            return response()->json([
                'message' => 'Salary record created successfully',
                'salary' => $salary
            ], 201);
        });
    }

    /**
     * Display the specified salary record
     */
    public function show(EmployeeSalary $employeeSalary): JsonResponse
    {
        $employeeSalary->load(['employee', 'createdBy', 'approvedBy', 'payrollRecords']);

        return response()->json($employeeSalary);
    }

    /**
     * Update the specified salary record
     */
    public function update(Request $request, EmployeeSalary $employeeSalary): JsonResponse
    {
        if ($employeeSalary->status !== 'active') {
            return response()->json([
                'message' => 'Only active salary records can be updated'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'basic_salary' => 'sometimes|numeric|min:0',
            'salary_type' => 'sometimes|in:monthly,hourly,daily',
            'hourly_rate' => 'nullable|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'allowance_breakdown' => 'nullable|array',
            'deduction_breakdown' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $employeeSalary->update($request->only([
            'basic_salary',
            'salary_type',
            'hourly_rate',
            'overtime_rate',
            'allowances',
            'deductions',
            'allowance_breakdown',
            'deduction_breakdown',
            'notes',
        ]));

        // Update calculated fields
        $employeeSalary->updateCalculatedFields();

        $employeeSalary->load(['employee', 'createdBy', 'approvedBy']);

        return response()->json([
            'message' => 'Salary record updated successfully',
            'salary' => $employeeSalary
        ]);
    }

    /**
     * Remove the specified salary record
     */
    public function destroy(EmployeeSalary $employeeSalary): JsonResponse
    {
        if ($employeeSalary->payrollRecords()->exists()) {
            return response()->json([
                'message' => 'Cannot delete salary record that has associated payroll records'
            ], 422);
        }

        $employeeSalary->delete();

        return response()->json([
            'message' => 'Salary record deleted successfully'
        ]);
    }

    /**
     * Get current salary for an employee
     */
    public function getCurrentSalary(Employee $employee): JsonResponse
    {
        $salary = EmployeeSalary::getCurrentSalaryForEmployee($employee->id);

        if (!$salary) {
            return response()->json([
                'message' => 'No active salary found for this employee'
            ], 404);
        }

        $salary->load(['employee', 'createdBy', 'approvedBy']);

        return response()->json($salary);
    }

    /**
     * Get salary history for an employee
     */
    public function getSalaryHistory(Employee $employee): JsonResponse
    {
        $salaries = EmployeeSalary::where('employee_id', $employee->id)
                                 ->with(['createdBy', 'approvedBy'])
                                 ->orderBy('effective_date', 'desc')
                                 ->get();

        return response()->json($salaries);
    }

    /**
     * Create salary adjustment request
     */
    public function createAdjustment(Request $request, Employee $employee): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'adjustment_type' => 'required|in:promotion,increment,bonus,deduction,correction,other',
            'new_basic_salary' => 'required|numeric|min:0',
            'new_allowances' => 'nullable|numeric|min:0',
            'new_deductions' => 'nullable|numeric|min:0',
            'effective_date' => 'required|date|after_or_equal:today',
            'reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        return DB::transaction(function () use ($request, $employee) {
            // Get current salary
            $currentSalary = EmployeeSalary::getCurrentSalaryForEmployee($employee->id);
            
            if (!$currentSalary) {
                return response()->json([
                    'message' => 'No active salary found for this employee'
                ], 404);
            }

            // Create new salary record (inactive until approved)
            $newSalary = EmployeeSalary::create([
                'employee_id' => $employee->id,
                'basic_salary' => $request->new_basic_salary,
                'salary_type' => $currentSalary->salary_type,
                'hourly_rate' => $currentSalary->hourly_rate,
                'overtime_rate' => $currentSalary->overtime_rate,
                'allowances' => $request->new_allowances ?? $currentSalary->allowances,
                'deductions' => $request->new_deductions ?? $currentSalary->deductions,
                'effective_date' => $request->effective_date,
                'status' => 'inactive',
                'created_by' => auth()->id(),
                'notes' => $request->notes,
            ]);

            $newSalary->updateCalculatedFields();

            // Create salary adjustment record
            $adjustment = SalaryAdjustment::create([
                'adjustment_number' => SalaryAdjustment::generateAdjustmentNumber(),
                'employee_id' => $employee->id,
                'old_salary_id' => $currentSalary->id,
                'new_salary_id' => $newSalary->id,
                'adjustment_type' => $request->adjustment_type,
                'old_amount' => $currentSalary->basic_salary,
                'new_amount' => $request->new_basic_salary,
                'adjustment_amount' => $request->new_basic_salary - $currentSalary->basic_salary,
                'effective_date' => $request->effective_date,
                'reason' => $request->reason,
                'requested_by' => auth()->id(),
                'notes' => $request->notes,
            ]);

            // Calculate percentage change
            $adjustment->percentage_change = $adjustment->calculatePercentageChange();
            $adjustment->save();

            $adjustment->load(['employee', 'oldSalary', 'newSalary', 'requestedBy']);

            return response()->json([
                'message' => 'Salary adjustment request created successfully',
                'adjustment' => $adjustment
            ], 201);
        });
    }

    /**
     * Approve salary record
     */
    public function approve(EmployeeSalary $employeeSalary): JsonResponse
    {
        $employeeSalary->approve();

        return response()->json([
            'message' => 'Salary record approved successfully',
            'salary' => $employeeSalary
        ]);
    }

    /**
     * Get salary statistics
     */
    public function getStatistics(): JsonResponse
    {
        $stats = [
            'total_employees_with_salary' => EmployeeSalary::distinct('employee_id')->count(),
            'total_active_salaries' => EmployeeSalary::active()->count(),
            'average_salary' => EmployeeSalary::active()->avg('basic_salary'),
            'highest_salary' => EmployeeSalary::active()->max('basic_salary'),
            'lowest_salary' => EmployeeSalary::active()->min('basic_salary'),
            'total_monthly_payroll' => EmployeeSalary::active()->sum('net_salary'),
        ];

        return response()->json($stats);
    }
}
