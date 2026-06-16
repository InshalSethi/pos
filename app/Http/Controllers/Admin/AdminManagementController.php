<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminManagementController extends Controller
{
    public function index()
    {
        // Not used in SPA, fallback
        return response()->json(['message' => 'API Endpoint']);
    }

    public function data(Request $request)
    {
        // Query admins
        $query = Admin::query()->with('roles');

        if ($request->has('search') && !empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $totalRecords = Admin::count();
        $filteredRecords = $query->count();

        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        $columns = $request->input('columns');
        
        if (isset($columns[$orderColumnIndex]['data'])) {
            $orderColumn = $columns[$orderColumnIndex]['data'];
            if (in_array($orderColumn, ['id', 'name', 'email', 'phone', 'is_active', 'created_at'])) {
                $query->orderBy($orderColumn, $orderDir);
            }
        }

        $users = $query->offset($start)->limit($limit)->get();

        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '-',
                'roles' => $user->roles->pluck('name'),
                'is_active' => (bool)$user->is_active,
            ];
        }

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $filteredRecords,
            "data" => $data
        ]);
    }

    public function show(Admin $admin)
    {
        $admin->load('roles');
        $admin->roles_list = $admin->roles->pluck('name');
        return response()->json(['data' => $admin]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|min:6',
            'phone' => 'nullable|string',
            'roles' => 'required|array|min:1',
        ]);

        $user = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        $user->syncRoles($request->roles);

        return response()->json(['message' => 'Admin created successfully.', 'data' => $user], 201);
    }

    public function update(Request $request, Admin $admin)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,'.$admin->id,
            'phone' => 'nullable|string',
            'roles' => 'required|array|min:1',
        ]);

        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->phone = $request->phone;
        $admin->is_active = $request->has('is_active') ? $request->boolean('is_active') : $admin->is_active;

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6']);
            $admin->password = Hash::make($request->password);
        }

        $admin->save();
        $admin->syncRoles($request->roles);

        return response()->json(['message' => 'Admin updated successfully.', 'data' => $admin]);
    }

    public function destroy(Admin $admin)
    {
        if ($admin->id === auth('admin')->id()) {
            return response()->json(['message' => 'You cannot delete yourself.'], 403);
        }

        $admin->delete();
        return response()->json(['message' => 'Admin removed successfully.']);
    }
}
