<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminRoleController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'API Endpoint']);
    }

    public function data(Request $request)
    {
        $query = Role::where('guard_name', 'admin')->with('permissions');
        
        $totalRecords = Role::where('guard_name', 'admin')->count();

        if ($request->has('search') && !empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where('name', 'like', "%{$search}%");
        }

        $filteredRecords = $query->count();

        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        $columns = $request->input('columns');
        
        if (isset($columns[$orderColumnIndex]['data'])) {
            $orderColumn = $columns[$orderColumnIndex]['data'];
            if (in_array($orderColumn, ['id', 'name'])) {
                $query->orderBy($orderColumn, $orderDir);
            }
        }

        $roles = $query->offset($start)->limit($limit)->get();

        $data = [];
        foreach ($roles as $role) {
            $data[] = [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
                'is_core' => in_array($role->name, ['Super Admin', 'Admin']),
            ];
        }

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $filteredRecords,
            "data" => $data
        ]);
    }

    public function show(Role $role)
    {
        $role->load('permissions');
        $role->permissions_list = $role->permissions->pluck('name');
        return response()->json(['data' => $role]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'admin']);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json(['message' => 'Role created successfully.', 'data' => $role], 201);
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,'.$role->id,
            'permissions' => 'nullable|array',
        ]);

        $role->name = $request->name;
        $role->save();

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        } else {
            $role->syncPermissions([]);
        }

        return response()->json(['message' => 'Role updated successfully.', 'data' => $role]);
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, ['Super Admin', 'Admin'])) {
            return response()->json(['message' => 'Cannot delete core system roles.'], 403);
        }

        $role->delete();
        return response()->json(['message' => 'Role deleted successfully.']);
    }
}
