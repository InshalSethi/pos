<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class SubAdminController extends Controller
{
    public function __construct()
    {
    // $this->middleware('permission:users.view')->only(['index', 'show', 'statistics']);
    // $this->middleware('permission:users.create')->only(['store']);
    // $this->middleware('permission:users.edit')->only(['update']);
    // $this->middleware('permission:users.delete')->only(['destroy']);
    }

    /**
     * Display a listing of sub-admins.
     */
    public function index(Request $request): JsonResponse
    {
        // Define all management/operational roles to include
        $rolesToShow = ['admin', 'sub-admin', 'manager', 'cashier', 'employee', 'user'];

        // Build query excluding user ID 9 (Asad Sethi) and filtering by all relevant roles
        $query = User::role($rolesToShow)
            ->where('id', '!=', 9)
            ->with('roles');

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by') ?? $request->get('sort_field', 'id');
        $sortOrder = $request->get('sort_order', 'desc');

        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $users = $query->paginate($perPage);

        return response()->json($users);
    }

    /**
     * Get statistics for sub-admins.
     */
    public function statistics(): JsonResponse
    {
        // Define all roles to count for the dashboard
        $allRoles = ['admin', 'sub-admin', 'manager', 'cashier', 'employee', 'user'];

        // Base query for ALL management users (including ID 9)
        $baseQuery = User::role($allRoles);

        return response()->json([
            'total_sub_admins' => (clone $baseQuery)->count(),
            'active_sub_admins' => (clone $baseQuery)->where('is_active', true)->count(),
            'inactive_sub_admins' => (clone $baseQuery)->where('is_active', false)->count(),
        ]);
    }

    /**
     * Store a new sub-admin.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'role' => 'required|string|in:admin,sub-admin,manager,cashier,employee,user'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'notes' => $request->notes,
            'is_active' => $request->get('is_active', true)
        ]);

        $user->syncRoles($request->role);
        $user->load('roles');

        return response()->json([
            'message' => 'Sub Admin created successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Display the specified sub-admin.
     */
    public function show($id): JsonResponse
    {

        $subAdmin = User::with('roles')->findOrFail($id);
        return response()->json($subAdmin);
    }

    /**
     * Update the specified sub-admin.
     */
    public function update(Request $request, $id): JsonResponse
    {
        if ($id == 9) {
            return response()->json(['message' => 'Super Admin cannot be modified here.'], 403);
        }
        $subAdmin = User::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $subAdmin->id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'role' => 'required|string|in:admin,sub-admin,manager,cashier,employee,user'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only(['name', 'email', 'phone', 'address', 'notes', 'is_active']);

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $subAdmin->update($updateData);

        // Dynamic role synchronization
        $subAdmin->syncRoles([$request->role]);

        return response()->json([
            'message' => 'Sub Admin updated successfully',
            'user' => $subAdmin->load('roles')
        ]);
    }

    /**
     * Remove the specified sub-admin.
     */
    public function destroy($id): JsonResponse
    {
        // 1. No one delete super admin
        if ($id == 9) {
            return response()->json([
                'message' => 'Super Admin account is permanent and cannot be deleted.'
            ], 403);
        }

        $subAdmin = User::findOrFail($id);

        // 2. Self-deletion check 
        if ($subAdmin->id === auth()->id()) {
            return response()->json([
                'message' => 'Security Protocol: You cannot delete your own account.'
            ], 422);
        }

        // 3. Delete the user
        $subAdmin->delete();

        return response()->json([
            'message' => 'Sub Admin deleted successfully'
        ]);
    }
}
