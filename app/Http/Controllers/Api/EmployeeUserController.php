<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\EmployeeUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeUserController extends Controller
{
    private EmployeeUserService $employeeUserService;

    public function __construct(EmployeeUserService $employeeUserService)
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:employees.edit');
        $this->employeeUserService = $employeeUserService;
    }

    /**
     * Get employees without user accounts
     */
    public function employeesWithoutAccounts(): JsonResponse
    {
        $employees = $this->employeeUserService->getEmployeesWithoutUserAccounts();

        return response()->json($employees);
    }

    /**
     * Create user account for an employee
     */
    public function createUserAccount(Request $request, Employee $employee): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'nullable|string|min:8',
            'send_credentials' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $this->employeeUserService->createUserAccountForEmployee(
                $employee,
                $request->only(['password'])
            );

            return response()->json([
                'message' => 'User account created successfully',
                'user' => $user,
                'employee' => $employee->fresh(['user'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create user account',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync user account with employee information
     */
    public function syncUserAccount(Employee $employee): JsonResponse
    {
        if (!$employee->user) {
            return response()->json([
                'message' => 'Employee does not have a user account'
            ], 422);
        }

        try {
            $this->employeeUserService->syncUserWithEmployee($employee);

            return response()->json([
                'message' => 'User account synced successfully',
                'employee' => $employee->fresh(['user'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to sync user account',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, Employee $employee): JsonResponse
    {
        if (!$employee->user) {
            return response()->json([
                'message' => 'Employee does not have a user account'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'new_password' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $newPassword = $this->employeeUserService->resetUserPassword(
                $employee->user,
                $request->get('new_password')
            );

            return response()->json([
                'message' => 'Password reset successfully',
                'new_password' => $newPassword // In production, you might want to send this via email instead
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reset password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deactivate user account
     */
    public function deactivateUserAccount(Employee $employee): JsonResponse
    {
        if (!$employee->user) {
            return response()->json([
                'message' => 'Employee does not have a user account'
            ], 422);
        }

        try {
            $this->employeeUserService->deactivateUserAccount($employee);

            return response()->json([
                'message' => 'User account deactivated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to deactivate user account',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reactivate user account
     */
    public function reactivateUserAccount(Employee $employee): JsonResponse
    {
        if (!$employee->user) {
            return response()->json([
                'message' => 'Employee does not have a user account'
            ], 422);
        }

        try {
            $this->employeeUserService->reactivateUserAccount($employee);

            return response()->json([
                'message' => 'User account reactivated successfully',
                'employee' => $employee->fresh(['user'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reactivate user account',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk create user accounts
     */
    public function bulkCreateUserAccounts(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $results = $this->employeeUserService->bulkCreateUserAccounts(
                $request->get('employee_ids')
            );

            return response()->json([
                'message' => 'Bulk user account creation completed',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create user accounts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get audit information about user-employee relationships
     */
    public function auditRelationships(): JsonResponse
    {
        $audit = $this->employeeUserService->auditUserEmployeeRelationships();

        return response()->json($audit);
    }
}
