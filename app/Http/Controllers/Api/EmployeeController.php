<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            // Grant full access to Company Admin / Owner
            $company = $user->currentCompany ?? ($user->current_company_id ? \App\Models\Company::find($user->current_company_id) : null);
            $isOwner = $company && (int)$user->id === (int)$company->user_id;
            $isAdmin = $user->hasRole(['admin', 'owner', 'super-admin']) || (int)$user->id === 1;

            if ($isOwner || $isAdmin) {
                return $next($request);
            }

            // Check dedicated manage_employees permission
            if ($user->hasPermissionTo('manage_employees') || $user->can('manage_employees')) {
                return $next($request);
            }

            // Specific granular permission check fallback
            $action = $request->route() ? $request->route()->getActionMethod() : '';
            $permissionMap = [
                'index' => 'employees.view',
                'show' => 'employees.view',
                'statistics' => 'employees.view',
                'store' => 'employees.create',
                'update' => 'employees.edit',
                'destroy' => 'employees.delete',
                'terminate' => 'employees.edit',
                'reactivate' => 'employees.edit',
            ];

            $requiredPermission = $permissionMap[$action] ?? 'manage_employees';
            if ($user->hasPermissionTo($requiredPermission) || $user->can($requiredPermission)) {
                return $next($request);
            }

            return response()->json(['message' => 'Unauthorized access to Employee Management.'], 403);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Employee::nonAdmin()->with(['department', 'position', 'manager', 'user']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        // Filter by employment status
        if ($request->has('employment_status')) {
            $query->where('employment_status', $request->get('employment_status'));
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by department
        if ($request->has('department_id')) {
            $query->where('department_id', $request->get('department_id'));
        }

        // Filter by position
        if ($request->has('position_id')) {
            $query->where('position_id', $request->get('position_id'));
        }

        // Filter by employment type
        if ($request->has('employment_type')) {
            $query->where('employment_type', $request->get('employment_type'));
        }

        // Filter by hire date range
        if ($request->has('hire_date_from')) {
            $query->where('hire_date', '>=', $request->get('hire_date_from'));
        }
        if ($request->has('hire_date_to')) {
            $query->where('hire_date', '<=', $request->get('hire_date_to'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'first_name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        if ($request->has('per_page')) {
            $employees = $query->paginate($request->get('per_page', 15));
        } else {
            $employees = $query->get();
        }

        return response()->json($employees);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('employees', 'email')->where('company_id', $companyId),
            ],
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'national_id' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:50',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'manager_id' => 'nullable|exists:employees,id',
            'hire_date' => 'required|date',
            'probation_end_date' => 'nullable|date|after:hire_date',
            'employment_type' => 'required|in:full_time,part_time,contract,intern',
            'basic_salary' => 'required|numeric|min:0',
            'salary_type' => 'required|in:monthly,hourly,daily',
            'hourly_rate' => 'nullable|numeric|min:0',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'bank_branch' => 'nullable|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_email' => 'nullable|email',
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

            $employeeData = $request->except(['profile_image']);
            $employeeData['employee_number'] = Employee::generateEmployeeNumber();

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $employeeData['profile_image'] = $request->file('profile_image')->store('employees/profiles', 'public');
            }

            $employee = Employee::create($employeeData);
            $employee->load(['department', 'position', 'manager']);

            DB::commit();

            return response()->json([
                'message' => 'Employee created successfully',
                'employee' => $employee
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee): JsonResponse
    {
        $employee->load([
            'department',
            'position',
            'manager',
            'subordinates',
            'user',
            'expenses',
            'salaryRecords',
            'payrollRecords'
        ]);

        return response()->json($employee);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('employees', 'email')->ignore($employee->id)->where('company_id', $companyId),
            ],
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'national_id' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:50',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'manager_id' => 'nullable|exists:employees,id',
            'hire_date' => 'required|date',
            'probation_end_date' => 'nullable|date|after:hire_date',
            'termination_date' => 'nullable|date|after:hire_date',
            'employment_type' => 'required|in:full_time,part_time,contract,intern',
            'employment_status' => 'required|in:active,inactive,terminated,on_leave',
            'termination_reason' => 'nullable|string',
            'basic_salary' => 'required|numeric|min:0',
            'salary_type' => 'required|in:monthly,hourly,daily',
            'hourly_rate' => 'nullable|numeric|min:0',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'bank_branch' => 'nullable|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_email' => 'nullable|email',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Prevent circular manager reference
        if ($request->has('manager_id') && $request->manager_id) {
            if ($request->manager_id == $employee->id) {
                return response()->json([
                    'message' => 'Employee cannot be their own manager'
                ], 422);
            }

            // Check if the new manager is a subordinate
            $subordinateIds = $employee->subordinates()->pluck('id')->toArray();
            if (in_array($request->manager_id, $subordinateIds)) {
                return response()->json([
                    'message' => 'Cannot set a subordinate as manager'
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            $employeeData = $request->except(['profile_image']);

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                // Delete old image if exists
                if ($employee->profile_image) {
                    Storage::disk('public')->delete($employee->profile_image);
                }
                $employeeData['profile_image'] = $request->file('profile_image')->store('employees/profiles', 'public');
            }

            $employee->update($employeeData);
            $employee->load(['department', 'position', 'manager']);

            DB::commit();

            return response()->json([
                'message' => 'Employee updated successfully',
                'employee' => $employee
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee): JsonResponse
    {
        // Check if employee has subordinates
        if ($employee->subordinates()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete employee who has subordinates. Please reassign subordinates first.'
            ], 422);
        }

        // Check if employee has expenses
        if ($employee->expenses()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete employee who has expense records. Consider deactivating instead.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Delete profile image
            if ($employee->profile_image) {
                Storage::disk('public')->delete($employee->profile_image);
            }

            $employee->delete();

            DB::commit();

            return response()->json([
                'message' => 'Employee deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to delete employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Terminate employee
     */
    public function terminate(Request $request, Employee $employee): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'termination_date' => 'required|date',
            'termination_reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!$employee->canBeTerminated()) {
            return response()->json([
                'message' => 'Employee cannot be terminated'
            ], 422);
        }

        $employee->terminate($request->termination_reason, $request->termination_date);
        $employee->load(['department', 'position', 'manager']);

        return response()->json([
            'message' => 'Employee terminated successfully',
            'employee' => $employee
        ]);
    }

    /**
     * Reactivate employee
     */
    public function reactivate(Employee $employee): JsonResponse
    {
        if ($employee->isActive()) {
            return response()->json([
                'message' => 'Employee is already active'
            ], 422);
        }

        $employee->reactivate();
        $employee->load(['department', 'position', 'manager']);

        return response()->json([
            'message' => 'Employee reactivated successfully',
            'employee' => $employee
        ]);
    }

    /**
     * Get employee statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_employees' => Employee::nonAdmin()->count(),
            'active_employees' => Employee::nonAdmin()->where('is_active', true)->count(),
            'inactive_employees' => Employee::nonAdmin()->where('is_active', false)->count(),
            'by_department' => Employee::nonAdmin()
                ->with('department')
                ->selectRaw('department_id, COUNT(*) as count')
                ->groupBy('department_id')
                ->get(),
            'by_employment_type' => Employee::nonAdmin()
                ->selectRaw('employment_type, COUNT(*) as count')
                ->groupBy('employment_type')
                ->get(),
            'by_employment_status' => Employee::nonAdmin()
                ->selectRaw('employment_status, COUNT(*) as count')
                ->groupBy('employment_status')
                ->get(),
            'recent_hires' => Employee::nonAdmin()->where('hire_date', '>=', now()->subDays(30))->count(),
            'upcoming_probation_ends' => Employee::nonAdmin()->whereBetween('probation_end_date', [now(), now()->addDays(30)])->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get non-admin employees for dropdown/select options.
     */
    public function forDropdown(): JsonResponse
    {
        try {
            $employees = Employee::nonAdmin()
                ->where('is_active', true)
                ->with('user')
                ->orderBy('first_name')
                ->get()
                ->map(function ($employee) {
                    $displayName = trim($employee->first_name . ' ' . $employee->last_name) ?: ($employee->user->name ?? 'Employee #' . $employee->id);
                    return [
                        'id' => $employee->id,
                        'full_name' => $displayName,
                        'employee_number' => $employee->employee_number,
                        'is_owner' => false,
                    ];
                });

            return response()->json($employees);
        } catch (\Exception $e) {
            Log::error('Error in forDropdown method:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
