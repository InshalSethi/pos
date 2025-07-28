<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalaryAdjustment;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SalaryAdjustmentController extends Controller
{
    /**
     * Display a listing of salary adjustments
     */
    public function index(Request $request): JsonResponse
    {
        $query = SalaryAdjustment::with([
            'employee',
            'oldSalary',
            'newSalary',
            'requestedBy',
            'approvedBy',
            'rejectedBy'
        ]);

        // Filter by employee
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by adjustment type
        if ($request->has('adjustment_type')) {
            $query->where('adjustment_type', $request->adjustment_type);
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
            $query->where(function ($q) use ($search) {
                $q->where('adjustment_number', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($empQuery) use ($search) {
                      $empQuery->where('first_name', 'like', "%{$search}%")
                               ->orWhere('last_name', 'like', "%{$search}%")
                               ->orWhere('employee_number', 'like', "%{$search}%");
                  });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $adjustments = $query->paginate($perPage);

        return response()->json($adjustments);
    }

    /**
     * Display the specified salary adjustment
     */
    public function show(SalaryAdjustment $salaryAdjustment): JsonResponse
    {
        $salaryAdjustment->load([
            'employee',
            'oldSalary',
            'newSalary',
            'requestedBy',
            'approvedBy',
            'rejectedBy'
        ]);

        return response()->json($salaryAdjustment);
    }

    /**
     * Update the specified salary adjustment
     */
    public function update(Request $request, SalaryAdjustment $salaryAdjustment): JsonResponse
    {
        if ($salaryAdjustment->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending adjustments can be updated'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'adjustment_type' => 'sometimes|in:promotion,increment,bonus,deduction,correction,other',
            'new_amount' => 'sometimes|numeric|min:0',
            'effective_date' => 'sometimes|date|after_or_equal:today',
            'reason' => 'sometimes|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        return DB::transaction(function () use ($request, $salaryAdjustment) {
            // Update adjustment
            $salaryAdjustment->update($request->only([
                'adjustment_type',
                'new_amount',
                'effective_date',
                'reason',
                'notes'
            ]));

            // Update new salary if amount changed
            if ($request->has('new_amount')) {
                $salaryAdjustment->newSalary->update([
                    'basic_salary' => $request->new_amount
                ]);
                $salaryAdjustment->newSalary->updateCalculatedFields();

                // Recalculate adjustment amount and percentage
                $salaryAdjustment->adjustment_amount = $request->new_amount - $salaryAdjustment->old_amount;
                $salaryAdjustment->percentage_change = $salaryAdjustment->calculatePercentageChange();
                $salaryAdjustment->save();
            }

            $salaryAdjustment->load([
                'employee',
                'oldSalary',
                'newSalary',
                'requestedBy'
            ]);

            return response()->json([
                'message' => 'Salary adjustment updated successfully',
                'adjustment' => $salaryAdjustment
            ]);
        });
    }

    /**
     * Approve salary adjustment
     */
    public function approve(Request $request, SalaryAdjustment $salaryAdjustment): JsonResponse
    {
        if (!$salaryAdjustment->can_approve) {
            return response()->json([
                'message' => 'This adjustment cannot be approved'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $salaryAdjustment->approve(auth()->id(), $request->approval_notes);

        $salaryAdjustment->load([
            'employee',
            'oldSalary',
            'newSalary',
            'requestedBy',
            'approvedBy'
        ]);

        return response()->json([
            'message' => 'Salary adjustment approved successfully',
            'adjustment' => $salaryAdjustment
        ]);
    }

    /**
     * Reject salary adjustment
     */
    public function reject(Request $request, SalaryAdjustment $salaryAdjustment): JsonResponse
    {
        if (!$salaryAdjustment->can_reject) {
            return response()->json([
                'message' => 'This adjustment cannot be rejected'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $salaryAdjustment->reject(auth()->id(), $request->rejection_reason);

        $salaryAdjustment->load([
            'employee',
            'oldSalary',
            'newSalary',
            'requestedBy',
            'rejectedBy'
        ]);

        return response()->json([
            'message' => 'Salary adjustment rejected successfully',
            'adjustment' => $salaryAdjustment
        ]);
    }

    /**
     * Implement approved salary adjustment
     */
    public function implement(SalaryAdjustment $salaryAdjustment): JsonResponse
    {
        if (!$salaryAdjustment->can_implement) {
            return response()->json([
                'message' => 'This adjustment cannot be implemented'
            ], 422);
        }

        try {
            $salaryAdjustment->implement();

            $salaryAdjustment->load([
                'employee',
                'oldSalary',
                'newSalary',
                'requestedBy',
                'approvedBy'
            ]);

            return response()->json([
                'message' => 'Salary adjustment implemented successfully',
                'adjustment' => $salaryAdjustment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to implement adjustment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete salary adjustment
     */
    public function destroy(SalaryAdjustment $salaryAdjustment): JsonResponse
    {
        if ($salaryAdjustment->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending adjustments can be deleted'
            ], 422);
        }

        return DB::transaction(function () use ($salaryAdjustment) {
            // Delete the new salary record if it exists
            if ($salaryAdjustment->newSalary) {
                $salaryAdjustment->newSalary->delete();
            }

            $salaryAdjustment->delete();

            return response()->json([
                'message' => 'Salary adjustment deleted successfully'
            ]);
        });
    }

    /**
     * Get pending adjustments for approval
     */
    public function getPendingApprovals(): JsonResponse
    {
        $adjustments = SalaryAdjustment::pending()
                                     ->with([
                                         'employee',
                                         'oldSalary',
                                         'newSalary',
                                         'requestedBy'
                                     ])
                                     ->orderBy('created_at', 'asc')
                                     ->get();

        return response()->json($adjustments);
    }

    /**
     * Get adjustments ready for implementation
     */
    public function getReadyForImplementation(): JsonResponse
    {
        $adjustments = SalaryAdjustment::approved()
                                     ->where('effective_date', '<=', now())
                                     ->with([
                                         'employee',
                                         'oldSalary',
                                         'newSalary',
                                         'requestedBy',
                                         'approvedBy'
                                     ])
                                     ->orderBy('effective_date', 'asc')
                                     ->get();

        return response()->json($adjustments);
    }

    /**
     * Get adjustment statistics
     */
    public function getStatistics(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', now()->startOfYear());
        $endDate = $request->get('end_date', now()->endOfYear());

        $stats = [
            'total_adjustments' => SalaryAdjustment::whereBetween('created_at', [$startDate, $endDate])->count(),
            'pending_adjustments' => SalaryAdjustment::pending()->count(),
            'approved_adjustments' => SalaryAdjustment::approved()->count(),
            'implemented_adjustments' => SalaryAdjustment::implemented()->count(),
            'rejected_adjustments' => SalaryAdjustment::rejected()->count(),
            'adjustments_by_type' => SalaryAdjustment::whereBetween('created_at', [$startDate, $endDate])
                                                   ->selectRaw('adjustment_type, count(*) as count')
                                                   ->groupBy('adjustment_type')
                                                   ->pluck('count', 'adjustment_type'),
            'average_adjustment_amount' => SalaryAdjustment::whereBetween('created_at', [$startDate, $endDate])
                                                         ->avg('adjustment_amount'),
            'total_adjustment_value' => SalaryAdjustment::implemented()
                                                      ->whereBetween('created_at', [$startDate, $endDate])
                                                      ->sum('adjustment_amount'),
        ];

        return response()->json($stats);
    }
}
