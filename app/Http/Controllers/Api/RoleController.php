<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:roles.view|users.view')->only(['index', 'show']);
        $this->middleware('permission:roles.create|users.create')->only(['store']);
        $this->middleware('permission:roles.edit|users.edit')->only(['update']);
        $this->middleware('permission:roles.delete|users.delete')->only(['destroy']);
    }

    /**
     * Display a listing of roles for current selected company.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        // Ensure default admin role exists for web guard and has full permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        if ($adminRole->permissions()->count() === 0) {
            $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());
        }

        // Only standard system default roles are shared across companies
        $systemRoleNames = ['admin', 'manager', 'cashier', 'employee', 'user'];

        $query = Role::with('permissions')
            ->where('guard_name', 'web')
            ->where(function ($q) use ($companyId, $systemRoleNames) {
                $q->where('company_id', $companyId)
                  ->orWhere(function ($sq) use ($systemRoleNames) {
                      $sq->whereNull('company_id')
                         ->whereIn('name', $systemRoleNames);
                  });
            });

        // Search functionality
        if ($request->has('search') && $request->get('search') != '') {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $roles = $query->orderBy('name')->get();

        return response()->json($roles);
    }

    /**
     * Store a newly created role for current selected company.
     */
    public function store(Request $request): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->where(function ($q) use ($companyId) {
                    return $q->where('company_id', $companyId);
                }),
            ],
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $role = Role::create([
            'name' => $request->name,
            'company_id' => $companyId,
            'guard_name' => 'web',
        ]);

        if ($request->has('permissions') && !empty($request->permissions)) {
            $permissionModels = Permission::where('guard_name', 'web')
                ->whereIn('name', (array) $request->permissions)
                ->get();
            $role->syncPermissions($permissionModels);
        }

        $role->load('permissions');

        return response()->json([
            'message' => 'Role created successfully',
            'role' => $role
        ], 201);
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        if (!is_null($role->company_id) && $role->company_id != $companyId) {
            return response()->json(['message' => 'Unauthorized access to role.'], 403);
        }

        $role->load('permissions');
        return response()->json($role);
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        if (!is_null($role->company_id) && $role->company_id != $companyId) {
            return response()->json(['message' => 'Unauthorized to update this role.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->ignore($role->id)->where(function ($q) use ($companyId) {
                    return $q->where('company_id', $companyId);
                }),
            ],
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $permissionModels = Permission::where('guard_name', 'web')
                ->whereIn('name', (array) $request->permissions)
                ->get();
            $role->syncPermissions($permissionModels);
        }

        $role->load('permissions');

        return response()->json([
            'message' => 'Role updated successfully',
            'role' => $role
        ]);
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        // Prevent deleting system default roles
        $systemRoles = ['admin', 'manager', 'cashier', 'user'];
        if (is_null($role->company_id) || in_array(strtolower($role->name), $systemRoles)) {
            return response()->json([
                'message' => 'Cannot delete system default role'
            ], 422);
        }

        if ($role->company_id != $companyId) {
            return response()->json(['message' => 'Unauthorized to delete this role.'], 403);
        }

        // Check if role is assigned to users
        if ($role->users()->exists()) {
            return response()->json([
                'message' => 'Cannot delete role that is assigned to users'
            ], 422);
        }

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully'
        ]);
    }

    /**
     * Get all available permissions.
     */
    public function permissions(): JsonResponse
    {
        $permissions = Permission::where('guard_name', 'web')->get()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });

        return response()->json($permissions);
    }
}
