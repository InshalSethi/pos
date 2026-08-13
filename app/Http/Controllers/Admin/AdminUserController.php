<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'API Endpoint']);
    }

    public function data(Request $request)
    {
        // Simple manual datatable server-side logic
        $query = User::query();

        if ($request->has('search') && !empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $totalRecords = User::count();
        $filteredRecords = $query->count();

        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        $columns = $request->input('columns');
        
        if (isset($columns[$orderColumnIndex]['data'])) {
            $orderColumn = $columns[$orderColumnIndex]['data'];
            // basic column mapping
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

    public function show(User $user)
    {
        $user->load(['currentCompany', 'roles']);
        $companies = Company::where(function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereHas('users', function($uq) use ($user) {
                  $uq->where('users.id', $user->id);
              });
        })->get();

        $data = $user->toArray();
        $data['all_companies'] = $companies;

        return response()->json(['data' => $data]);
    }

    public function userCompaniesData(Request $request, User $user)
    {
        $query = Company::where(function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereHas('users', function($uq) use ($user) {
                  $uq->where('users.id', $user->id);
              });
        });

        if ($request->has('search') && !empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('company_email', 'like', "%{$search}%")
                  ->orWhere('business_type', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        $totalRecords = Company::where(function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereHas('users', function($uq) use ($user) {
                  $uq->where('users.id', $user->id);
              });
        })->count();

        $filteredRecords = $query->count();

        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);

        $companies = $query->latest()->offset($start)->limit($limit)->get();

        $data = [];
        foreach ($companies as $company) {
            $data[] = [
                'id' => $company->id,
                'company_name' => $company->company_name,
                'company_email' => $company->company_email,
                'company_phone' => $company->company_phone,
                'business_type' => $company->business_type ?? '-',
                'business_scale' => $company->business_scale ?? '-',
                'country' => $company->country ?? '-',
                'status' => $company->status ?? 'active',
                'created_at' => $company->created_at ? $company->created_at->format('Y-m-d H:i') : '-',
            ];
        }

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $filteredRecords,
            "data" => $data
        ]);
    }

    public function companyShow(Company $company)
    {
        $company->load(['owner']);
        return response()->json(['data' => $company]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : true,
        ]);

        return response()->json(['message' => 'User created successfully.', 'data' => $user], 201);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        
        if ($request->has('is_active')) {
            $user->is_active = (bool) $request->input('is_active');
        }

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6']);
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json(['message' => 'User updated successfully.', 'data' => $user]);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully.']);
    }
}

