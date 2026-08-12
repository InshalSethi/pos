<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

use App\Models\Employee;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:users.view')->only(['index', 'show', 'statistics']);
        $this->middleware('permission:users.create')->only(['store']);
        $this->middleware('permission:users.edit')->only(['update']);
        $this->middleware('permission:users.delete')->only(['destroy']);
    }

    /**
     * Get statistics count for users.
     */
    public function statistics(): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        $baseQuery = User::where(function ($q) use ($companyId) {
            $q->where('current_company_id', $companyId)
              ->orWhereHas('companies', function ($cq) use ($companyId) {
                  $cq->where('companies.id', $companyId);
              });
        });

        $total = (clone $baseQuery)->count();
        $active = (clone $baseQuery)->where('is_active', true)->count();

        return response()->json([
            'total_users' => $total,
            'active_users' => $active,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        $query = User::with('roles')
            ->where(function ($q) use ($companyId) {
                $q->where('current_company_id', $companyId)
                  ->orWhereHas('companies', function ($cq) use ($companyId) {
                      $cq->where('companies.id', $companyId);
                  });
            });

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->role) {
            $query->role($request->get('role'));
        }

        // Filter by status (is_active)
        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', filter_var($request->get('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        // Sorting
        $sortBy = $request->get('sort_by') ?? $request->get('sort_field', 'name');
        $sortOrder = $request->get('sort_order', 'asc');

        // Validate sort fields
        $allowedSortFields = ['id', 'name', 'email', 'created_at', 'is_active'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }
        else {
            $query->orderBy('name', 'asc');
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $users = $query->paginate($perPage);

        // Mark main owner
        $company = \App\Models\Company::find($companyId);
        $mainOwnerId = $company ? $company->user_id : null;
        $users->getCollection()->transform(function ($u) use ($mainOwnerId) {
            $u->is_main_owner = ($mainOwnerId && (int)$u->id === (int)$mainOwnerId) || (int)$u->id === 1;
            return $u;
        });

        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
            'company_id' => 'nullable|exists:companies,id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $companyId = $request->get('company_id') ?: auth()->user()->current_company_id;

        // Handle profile photo upload
        $avatarPath = null;
        if ($request->hasFile('profile_image')) {
            $avatarPath = Storage::disk('public')->put('avatars', $request->file('profile_image'));
        } elseif ($request->hasFile('avatar')) {
            $avatarPath = Storage::disk('public')->put('avatars', $request->file('avatar'));
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'notes' => $request->notes,
            'profile_image' => $avatarPath,
            'is_active' => filter_var($request->get('is_active', true), FILTER_VALIDATE_BOOLEAN),
            'current_company_id' => $companyId,
            'company_id' => $companyId,
            'onboarding_completed' => true,
        ]);

        if ($companyId) {
            $user->companies()->syncWithoutDetaching([$companyId => ['role' => $request->role]]);
        }

        $user->assignRole($request->role);
        $user->load('roles');

        // Automatically link or create Employee profile
        $nameParts = explode(' ', trim($request->name), 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        $employee = Employee::where('email', $request->email)
            ->where('company_id', $companyId)
            ->first();

        if ($employee) {
            $employee->update([
                'user_id' => $user->id,
                'company_id' => $companyId,
                'profile_image' => $avatarPath ?: $employee->profile_image,
                'phone' => $request->phone ?: $employee->phone,
                'address' => $request->address ?: $employee->address,
                'is_active' => $user->is_active,
            ]);
        } else {
            Employee::create([
                'user_id' => $user->id,
                'company_id' => $companyId,
                'employee_number' => Employee::generateEmployeeNumber(),
                'first_name' => $firstName,
                'last_name' => $lastName ?: $firstName,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'profile_image' => $avatarPath,
                'hire_date' => now()->toDateString(),
                'employment_type' => 'full_time',
                'employment_status' => $user->is_active ? 'active' : 'inactive',
                'basic_salary' => 0,
                'salary_type' => 'monthly',
                'is_active' => $user->is_active,
            ]);
        }

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        if ($user->current_company_id !== $companyId && !$user->companies()->where('companies.id', $companyId)->exists()) {
            return response()->json(['message' => 'Unauthorized access to user.'], 403);
        }

        $user->load('roles', 'permissions', 'employee');

        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        if ($user->current_company_id !== $companyId && !$user->companies()->where('companies.id', $companyId)->exists()) {
            return response()->json(['message' => 'Unauthorized to update this user.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'notes' => $request->notes,
            'is_active' => filter_var($request->get('is_active', true), FILTER_VALIDATE_BOOLEAN),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        // Handle profile photo upload
        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $updateData['profile_image'] = Storage::disk('public')->put('avatars', $request->file('profile_image'));
        } elseif ($request->hasFile('avatar')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $updateData['profile_image'] = Storage::disk('public')->put('avatars', $request->file('avatar'));
        }

        $user->update($updateData);
        $user->syncRoles([$request->role]);
        $user->load('roles');

        // Automatically sync changes to linked Employee
        $employee = Employee::where('user_id', $user->id)
            ->orWhere(function ($q) use ($user, $companyId) {
                $q->where('email', $user->email)->where('company_id', $companyId);
            })->first();

        if ($employee) {
            $nameParts = explode(' ', trim($request->name), 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            $employee->update([
                'user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName ?: $firstName,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'profile_image' => $user->profile_image,
                'is_active' => $user->is_active,
                'employment_status' => $user->is_active ? 'active' : 'inactive',
            ]);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;
        $company = \App\Models\Company::find($companyId);
        $mainOwnerId = $company ? $company->user_id : null;

        // Prevent deleting main company owner / account registrant
        if (($mainOwnerId && (int)$user->id === (int)$mainOwnerId) || (int)$user->id === 1) {
            return response()->json([
                'message' => 'The main company owner account cannot be deleted.'
            ], 422);
        }

        // Prevent deleting the current user
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'Cannot delete your own account'
            ], 422);
        }

        if ($user->current_company_id !== $companyId && !$user->companies()->where('companies.id', $companyId)->exists()) {
            return response()->json(['message' => 'Unauthorized to delete this user.'], 403);
        }

        // Safely update linked Employee status without deleting historical HR data
        $employee = Employee::where('user_id', $user->id)->first();
        if ($employee) {
            $employee->update([
                'is_active' => false,
                'employment_status' => 'inactive',
                'user_id' => null,
            ]);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get all available roles for current company
     */
    public function roles(): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;
        $systemRoleNames = ['admin', 'manager', 'cashier', 'employee', 'user'];

        $roles = Role::where('guard_name', 'web')
            ->where(function ($q) use ($companyId, $systemRoleNames) {
                $q->where('company_id', $companyId)
                  ->orWhere(function ($sq) use ($systemRoleNames) {
                      $sq->whereNull('company_id')
                         ->whereIn('name', $systemRoleNames);
                  });
            })->orderBy('name')->get();

        return response()->json($roles);
    }
}
