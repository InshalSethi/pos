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
        $query = Employee::nonAdmin()
            ->with(['department', 'position', 'manager', 'subordinates.department', 'subordinates.position', 'managedDepartments', 'user'])
            ->withCount('subordinates');

        // Search functionality
        if ($request->filled('search')) {
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
        if ($request->filled('employment_status')) {
            $query->where('employment_status', $request->get('employment_status'));
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->get('department_id'));
        }

        // Filter by position
        if ($request->filled('position_id')) {
            $query->where('position_id', $request->get('position_id'));
        }

        // Filter by employment type
        if ($request->filled('employment_type')) {
            $query->where('employment_type', $request->get('employment_type'));
        }

        // Filter by manager status or tab
        $isManagerFilter = function ($q) {
            $q->where('is_manager', true)
              ->orWhereHas('user', function ($uq) {
                  $uq->whereHas('roles', function ($rq) {
                      $rq->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(name)'), ['manager', 'managerial']);
                  });
              })
              ->orWhereHas('position', function ($pq) {
                  $pq->whereIn('level', ['manager', 'director', 'executive'])
                    ->orWhereRaw("LOWER(title) REGEXP 'manager'");
              });
        };

        $isNonManagerFilter = function ($q) {
            $q->where(function ($nq) {
                $nq->where('is_manager', false)
                   ->orWhereNull('is_manager');
            })
            ->whereDoesntHave('user', function ($uq) {
                $uq->whereHas('roles', function ($rq) {
                    $rq->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(name)'), ['manager', 'managerial']);
                });
            })
            ->where(function ($nq) {
                $nq->whereNull('position_id')
                  ->orWhereDoesntHave('position', function ($pq) {
                      $pq->whereIn('level', ['manager', 'director', 'executive'])
                        ->orWhereRaw("LOWER(title) REGEXP 'manager'");
                  });
            });
        };

        if ($request->has('tab')) {
            $tab = $request->get('tab');
            if ($tab === 'managers') {
                $query->where($isManagerFilter);
            } elseif ($tab === 'employees') {
                $query->where($isNonManagerFilter);
            }
        } elseif ($request->has('is_manager')) {
            $isManager = filter_var($request->get('is_manager'), FILTER_VALIDATE_BOOLEAN);
            if ($isManager) {
                $query->where($isManagerFilter);
            } else {
                $query->where($isNonManagerFilter);
            }
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

        // Cast boolean inputs before running validator
        if ($request->has('is_manager')) {
            $request->merge(['is_manager' => filter_var($request->get('is_manager'), FILTER_VALIDATE_BOOLEAN)]);
        }
        if ($request->has('create_user_account')) {
            $request->merge(['create_user_account' => filter_var($request->get('create_user_account'), FILTER_VALIDATE_BOOLEAN)]);
        }
        if ($request->has('is_active')) {
            $request->merge(['is_active' => filter_var($request->get('is_active'), FILTER_VALIDATE_BOOLEAN)]);
        }

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
            'gender' => 'required|in:male,female,other',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'national_id' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:50',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'department_id' => 'nullable|exists:departments,id',
            'department_ids' => 'nullable|array',
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
            'is_manager' => 'nullable|boolean',
            // User account & company options
            'company_id' => 'nullable|exists:companies,id',
            'create_user_account' => 'nullable|boolean',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'nullable|string|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $companyId = $request->get('company_id') ?: auth()->user()->current_company_id;

        try {
            DB::beginTransaction();

            $employeeData = $request->except(['profile_image', 'avatar', 'create_user_account', 'password', 'password_confirmation', 'role']);
            $employeeData['employee_number'] = Employee::generateEmployeeNumber();
            $employeeData['company_id'] = $companyId;

            // Auto-determine is_manager status from request or position level
            $isManager = filter_var($request->get('is_manager', false), FILTER_VALIDATE_BOOLEAN);
            if (!$isManager && $request->filled('position_id')) {
                $position = \App\Models\Position::find($request->position_id);
                if ($position && in_array($position->level, ['lead', 'manager', 'director', 'executive'])) {
                    $isManager = true;
                }
            }
            $employeeData['is_manager'] = $isManager;

            // Handle profile photo upload
            $avatarPath = null;
            if ($request->hasFile('profile_image')) {
                $avatarPath = Storage::disk('public')->put('avatars', $request->file('profile_image'));
            } elseif ($request->hasFile('avatar')) {
                $avatarPath = Storage::disk('public')->put('avatars', $request->file('avatar'));
            }

            if ($avatarPath) {
                $employeeData['profile_image'] = $avatarPath;
            }

            // SINGLE-TABLE INHERITANCE: Save employee profile directly in users table
            $createUserAccount = filter_var($request->get('create_user_account', false), FILTER_VALIDATE_BOOLEAN) || $request->filled('password');
            $fullName = trim($request->first_name . ' ' . ($request->middle_name ? $request->middle_name . ' ' : '') . $request->last_name);
            $userType = $createUserAccount ? 'user' : 'employee';
            $passwordHash = ($createUserAccount && $request->filled('password'))
                ? \Illuminate\Support\Facades\Hash::make($request->password)
                : null;

            // Check if User record already exists for this email or create a new single-table record
            $user = User::where('email', $request->email)->first();
            if ($user) {
                $user->update([
                    'type' => $userType,
                    'name' => $fullName,
                    'phone' => $request->phone ?: $request->mobile ?: $user->phone,
                    'address' => $request->address ?: $user->address,
                    'profile_image' => $avatarPath ?: $user->profile_image,
                    'is_active' => filter_var($request->get('is_active', true), FILTER_VALIDATE_BOOLEAN),
                    'current_company_id' => $companyId ?: $user->current_company_id,
                ]);
                if ($passwordHash) {
                    $user->update(['password' => $passwordHash]);
                }
            } else {
                $user = User::create([
                    'type' => $userType,
                    'name' => $fullName,
                    'email' => $request->email,
                    'password' => $passwordHash,
                    'phone' => $request->phone ?: $request->mobile,
                    'address' => $request->address,
                    'profile_image' => $avatarPath,
                    'is_active' => filter_var($request->get('is_active', true), FILTER_VALIDATE_BOOLEAN),
                    'current_company_id' => $companyId,
                    'company_id' => $companyId,
                    'onboarding_completed' => true,
                ]);
            }

            if ($createUserAccount) {
                $roleName = $request->role ?: ($isManager ? 'manager' : 'employee');
                if ($isManager && $roleName === 'employee') {
                    $roleName = 'manager';
                }

                if ($companyId) {
                    $user->companies()->syncWithoutDetaching([$companyId => ['role' => $roleName]]);
                }
                $user->assignRole($roleName);
            }

            $employeeData['user_id'] = $user->id;

            $employee = Employee::create($employeeData);

            if ($request->has('department_ids')) {
                $deptIds = is_array($request->input('department_ids')) 
                    ? $request->input('department_ids') 
                    : array_filter(explode(',', (string)$request->input('department_ids')));
                
                \App\Models\Department::where('manager_id', $employee->id)
                    ->whereNotIn('id', $deptIds)
                    ->update(['manager_id' => null]);

                if (!empty($deptIds)) {
                    \App\Models\Department::whereIn('id', $deptIds)->update(['manager_id' => $employee->id]);
                }
            }

            $employee->load(['department', 'position', 'manager', 'managedDepartments', 'user']);

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
     * Display manager profile details and eager-loaded direct report subordinates.
     */
    public function getSubordinates($id): JsonResponse
    {
        $manager = Employee::with([
            'department',
            'position',
            'managedDepartments',
            'user.roles'
        ])->findOrFail($id);

        $subordinates = Employee::where('manager_id', $manager->id)
            ->orWhere('manager_id', $manager->user_id)
            ->with(['department', 'position', 'user'])
            ->get();

        return response()->json([
            'success' => true,
            'manager' => $manager,
            'subordinates' => $subordinates,
            'count' => $subordinates->count()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        // Cast boolean inputs before running validator
        if ($request->has('is_manager')) {
            $request->merge(['is_manager' => filter_var($request->get('is_manager'), FILTER_VALIDATE_BOOLEAN)]);
        }
        if ($request->has('create_user_account')) {
            $request->merge(['create_user_account' => filter_var($request->get('create_user_account'), FILTER_VALIDATE_BOOLEAN)]);
        }
        if ($request->has('is_active')) {
            $request->merge(['is_active' => filter_var($request->get('is_active'), FILTER_VALIDATE_BOOLEAN)]);
        }

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
            'gender' => 'required|in:male,female,other',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'national_id' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:50',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
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
            'is_manager' => 'nullable|boolean',
            // User account options
            'create_user_account' => 'nullable|boolean',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'nullable|string|exists:roles,name',
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

            $employeeData = $request->except(['profile_image', 'avatar', 'create_user_account', 'password', 'password_confirmation', 'role']);

            if ($request->has('is_manager')) {
                $employeeData['is_manager'] = filter_var($request->get('is_manager'), FILTER_VALIDATE_BOOLEAN);
            } elseif ($request->filled('position_id')) {
                $position = \App\Models\Position::find($request->position_id);
                if ($position && in_array($position->level, ['lead', 'manager', 'director', 'executive'])) {
                    $employeeData['is_manager'] = true;
                }
            }

            // Handle profile photo upload
            if ($request->hasFile('profile_image')) {
                if ($employee->profile_image) {
                    Storage::disk('public')->delete($employee->profile_image);
                }
                $employeeData['profile_image'] = Storage::disk('public')->put('avatars', $request->file('profile_image'));
            } elseif ($request->hasFile('avatar')) {
                if ($employee->profile_image) {
                    Storage::disk('public')->delete($employee->profile_image);
                }
                $employeeData['profile_image'] = Storage::disk('public')->put('avatars', $request->file('avatar'));
            }

            $employee->update($employeeData);

            // SINGLE-TABLE INHERITANCE: Sync employee profile details to users table
            $fullName = trim($request->first_name . ' ' . ($request->middle_name ? $request->middle_name . ' ' : '') . $request->last_name);
            $roleName = $request->role ?: ($employee->is_manager ? 'manager' : 'employee');
            $wantsUserAccount = filter_var($request->get('create_user_account', false), FILTER_VALIDATE_BOOLEAN) || $request->filled('password');

            $user = $employee->user ?: User::find($employee->user_id);
            if (!$user && $employee->email) {
                $user = User::where('email', $employee->email)->first();
            }

            if ($user) {
                $userData = [
                    'name' => $fullName,
                    'email' => $employee->email,
                    'phone' => $employee->phone ?: $employee->mobile,
                    'address' => $employee->address,
                    'profile_image' => $employee->profile_image,
                    'is_active' => $employee->is_active,
                ];

                if ($wantsUserAccount) {
                    $userData['type'] = 'user';
                    if ($request->filled('password')) {
                        $userData['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
                    }
                    if ($companyId) {
                        $user->companies()->syncWithoutDetaching([$companyId => ['role' => $roleName]]);
                    }
                    if ($request->filled('role')) {
                        $user->syncRoles([$request->role]);
                    }
                }

                $user->update($userData);

                if ($employee->user_id !== $user->id) {
                    $employee->update(['user_id' => $user->id]);
                }
            } else {
                $userType = $wantsUserAccount ? 'user' : 'employee';
                $passwordHash = ($wantsUserAccount && $request->filled('password'))
                    ? \Illuminate\Support\Facades\Hash::make($request->password)
                    : null;

                $user = User::create([
                    'type' => $userType,
                    'name' => $fullName,
                    'email' => $employee->email,
                    'password' => $passwordHash,
                    'phone' => $employee->phone ?: $employee->mobile,
                    'address' => $employee->address,
                    'profile_image' => $employee->profile_image,
                    'is_active' => $employee->is_active,
                    'current_company_id' => $companyId,
                    'company_id' => $companyId,
                    'onboarding_completed' => true,
                ]);

                if ($wantsUserAccount) {
                    if ($companyId) {
                        $user->companies()->syncWithoutDetaching([$companyId => ['role' => $roleName]]);
                    }
                    $user->assignRole($roleName);
                }

                $employee->update(['user_id' => $user->id]);
            }

            if ($request->has('department_ids')) {
                $deptIds = is_array($request->input('department_ids')) 
                    ? $request->input('department_ids') 
                    : array_filter(explode(',', (string)$request->input('department_ids')));
                
                \App\Models\Department::where('manager_id', $employee->id)
                    ->whereNotIn('id', $deptIds)
                    ->update(['manager_id' => null]);

                if (!empty($deptIds)) {
                    \App\Models\Department::whereIn('id', $deptIds)->update(['manager_id' => $employee->id]);
                }
            }

            $employee->load(['department', 'position', 'manager', 'managedDepartments', 'user']);

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
                ->where(function ($q) {
                    $q->where('is_manager', true)
                      ->orWhereHas('position', function ($pq) {
                          $pq->whereIn('level', ['lead', 'manager', 'director', 'executive']);
                      });
                })
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
