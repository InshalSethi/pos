<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
            // User credentials
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone' => 'nullable|string',
            'is_active' => 'nullable|boolean',

            // Company & Onboarding profile fields
            'company_name' => 'required|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:100',
            'owner_role' => 'nullable|string',
            'team_size' => 'nullable|string',
            'tax_number' => 'nullable|string|max:100',
            'business_address' => 'nullable|string|max:255',
            'intended_tasks' => 'nullable|array',
            'business_type' => 'nullable|string',
            'business_scale' => 'nullable|string',
            'country' => 'nullable|string',
            'system_language' => 'nullable|string',
            'base_currency' => 'nullable|string',
            'timezone_offset' => 'nullable|string',
            'fiscal_year_start' => 'nullable|date',
        ]);

        $user = DB::transaction(function () use ($request) {
            // 1. Create User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : true,
                'onboarding_completed' => true,
            ]);

            // 2. Create Active Company for the User
            $company = Company::create([
                'user_id' => $user->id,
                'company_name' => $request->company_name,
                'company_email' => $request->filled('company_email') ? $request->company_email : $user->email,
                'company_phone' => $request->company_phone ?? $user->phone ?? '',
                'registration_number' => $request->registration_number ?? '',
                'tax_number' => $request->tax_number ?? '',
                'business_address' => $request->business_address ?? '',
                'owner_role' => $request->owner_role ?? 'Owner/CEO',
                'team_size' => $request->team_size ?? 'Just Me',
                'intended_tasks' => $request->intended_tasks ?? [],
                'business_type' => $request->business_type ?? 'Retail Store',
                'business_scale' => $request->business_scale ?? 'Single Outlet',
                'country' => $request->country ?? 'United States',
                'system_language' => $request->system_language ?? 'en',
                'base_currency' => $request->base_currency ?? 'USD',
                'timezone_offset' => $request->timezone_offset ?? 'UTC',
                'fiscal_year_start' => $request->fiscal_year_start ?? date('Y-01-01'),
                'status' => 'active',
                'draft_step' => null,
            ]);

            // 3. Update User current company ID
            $user->current_company_id = $company->id;
            $user->save();

            // 4. Attach company pivot relation
            $user->companies()->attach($company->id, ['role' => 'owner']);

            // 5. Seed baseline company data
            // Default Warehouse
            $defaultWarehouse = \App\Models\Warehouse::firstOrCreate([
                'company_id' => $company->id,
                'is_default' => true,
            ], [
                'name' => 'Main Warehouse',
                'code' => 'MWH-001',
                'email' => $company->company_email ?: $user->email,
                'phone' => $company->company_phone ?: '+1 (555) 019-2834',
                'address' => $company->business_address ?: '100 Central Logistics Parkway, Industrial Zone',
                'city' => 'New York',
                'state' => 'NY',
                'zip_code' => '10001',
                'country' => $company->country ?: 'United States',
                'is_active' => true,
                'is_saleable' => true,
            ]);

            // Sales Counter
            \App\Models\Counter::firstOrCreate([
                'company_id' => $company->id,
                'warehouse_id' => $defaultWarehouse->id,
                'name' => 'First Sales Counter',
            ], [
                'counter_number' => 'C-01',
                'status' => 'active',
            ]);

            // Chart of Accounts
            if (class_exists('\App\Services\ChartOfAccountService')) {
                \App\Services\ChartOfAccountService::ensureDefaultAccountsForCompany($company->id);
            }

            // Default Category
            \App\Models\Category::firstOrCreate([
                'company_id' => $company->id,
                'name' => 'General',
            ], [
                'slug' => 'general',
                'description' => 'Default Category',
                'is_active' => true,
            ]);

            // Default Cash Bank Account
            $cashAccount = \App\Models\Account::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->where(function ($query) {
                    $query->where('account_code', '1010')
                        ->orWhere('account_name', 'Cash Account')
                        ->orWhere('account_name', 'Cash')
                        ->orWhere('account_name', 'Cash on Hand');
                })
                ->first();

            if (!$cashAccount) {
                $cashAccount = \App\Models\Account::withoutGlobalScopes()
                    ->where('company_id', $company->id)
                    ->first();
            }

            if ($cashAccount) {
                \App\Models\BankAccount::firstOrCreate([
                    'company_id' => $company->id,
                    'is_default' => true,
                ], [
                    'account_name' => 'Cash Account',
                    'bank_name' => 'Cash',
                    'account_number' => 'CASH-001',
                    'account_type' => 'checking',
                    'chart_account_id' => $cashAccount->id,
                    'currency' => $company->base_currency ?: 'USD',
                    'is_active' => true,
                    'is_default' => true,
                    'opening_balance' => 0.00,
                    'opening_date' => now()->format('Y-m-d'),
                ]);
            }

            return $user;
        });

        return response()->json(['message' => 'User and company created successfully.', 'data' => $user], 201);
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
        DB::transaction(function () use ($user) {
            // Get all companies owned by this user
            $ownedCompanyIds = Company::withTrashed()->where('user_id', $user->id)->pluck('id')->toArray();

            if (!empty($ownedCompanyIds)) {
                // List of all tables that may reference company_id
                $tablesWithCompanyId = [
                    'units', 'warehouses', 'bank_accounts', 'accounts', 'accounting_settings',
                    'attributes', 'attribute_values', 'brands', 'categories', 'counters',
                    'employees', 'inventories', 'inventory_histories', 'invoice_purchase_settings',
                    'journal_entries', 'products', 'product_variations', 'purchase_returns',
                    'sales', 'system_notifications', 'tags', 'transactions', 'transfer_orders',
                    'customers', 'suppliers', 'purchases', 'expenses', 'payment_outs', 'payment_ins',
                    'quotes', 'sales_returns', 'stock_adjustments', 'user_companies'
                ];

                foreach ($tablesWithCompanyId as $tableName) {
                    if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'company_id')) {
                        DB::table($tableName)->whereIn('company_id', $ownedCompanyIds)->delete();
                    }
                }

                // Delete pivot table records
                if (Schema::hasTable('company_user')) {
                    DB::table('company_user')->whereIn('company_id', $ownedCompanyIds)->delete();
                }

                // Force delete the companies
                Company::withTrashed()->whereIn('id', $ownedCompanyIds)->forceDelete();
            }

            // Detach user from any pivot companies
            if (Schema::hasTable('company_user')) {
                DB::table('company_user')->where('user_id', $user->id)->delete();
            }

            // Delete the user
            $user->delete();
        });

        return response()->json(['message' => 'User and all related company data deleted permanently.']);
    }
}

