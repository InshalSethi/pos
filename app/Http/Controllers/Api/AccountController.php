<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    public function __construct()
    {
        // Temporarily disabled for debugging
        // $this->middleware('permission:accounting.view')->only(['index', 'show', 'tree']);
        // $this->middleware('permission:accounting.create')->only(['store']);
        // $this->middleware('permission:accounting.edit')->only(['update']);
        // $this->middleware('permission:accounting.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Account::with(['parent', 'children']);

        // Filter by account type
        if ($request->has('account_type')) {
            $query->where('account_type', $request->account_type);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('account_name', 'like', "%{$search}%")
                  ->orWhere('account_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Get flat list or hierarchical
        if ($request->boolean('flat')) {
            $accounts = $query->orderBy('account_code')->paginate($request->get('per_page', 50));
        } else {
            // Get root accounts with children
            $accounts = $query->whereNull('parent_account_id')
                             ->orderBy('account_code')
                             ->get();
        }

        return response()->json($accounts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_code' => 'required|string|max:20|unique:chart_of_accounts,account_code',
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
            'account_subtype' => 'required|in:current_asset,fixed_asset,other_asset,current_liability,long_term_liability,other_liability,equity,operating_revenue,other_revenue,cost_of_goods_sold,operating_expense,other_expense',
            'parent_account_id' => 'nullable|exists:chart_of_accounts,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_system_account' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate parent account type consistency
        if ($request->parent_account_id) {
            $parent = Account::find($request->parent_account_id);
            if ($parent->account_type !== $request->account_type) {
                return response()->json([
                    'message' => 'Child account type must match parent account type'
                ], 422);
            }
        }

        $account = Account::create($request->all());

        return response()->json([
            'message' => 'Account created successfully',
            'account' => $account->load(['parent', 'children'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account): JsonResponse
    {
        $account->load(['parent', 'children', 'journalEntries']);

        // Calculate account balance
        $balance = $account->calculateBalance();
        $account->current_balance = $balance;

        return response()->json($account);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Account $account): JsonResponse
    {
        // Prevent updating system accounts
        if ($account->is_system_account) {
            return response()->json([
                'message' => 'Cannot modify system accounts'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'account_code' => 'required|string|max:20|unique:chart_of_accounts,account_code,' . $account->id,
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
            'account_subtype' => 'required|in:current_asset,fixed_asset,other_asset,current_liability,long_term_liability,other_liability,equity,operating_revenue,other_revenue,cost_of_goods_sold,operating_expense,other_expense',
            'parent_account_id' => 'nullable|exists:chart_of_accounts,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate parent account type consistency
        if ($request->parent_account_id) {
            $parent = Account::find($request->parent_account_id);
            if ($parent->account_type !== $request->account_type) {
                return response()->json([
                    'message' => 'Child account type must match parent account type'
                ], 422);
            }
        }

        // Prevent circular references
        if ($request->parent_account_id && $this->wouldCreateCircularReference($account->id, $request->parent_account_id)) {
            return response()->json([
                'message' => 'Cannot create circular reference in account hierarchy'
            ], 422);
        }

        $account->update($request->all());

        return response()->json([
            'message' => 'Account updated successfully',
            'account' => $account->load(['parent', 'children'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account): JsonResponse
    {
        // Prevent deleting system accounts
        if ($account->is_system_account) {
            return response()->json([
                'message' => 'Cannot delete system accounts'
            ], 422);
        }

        // Check if account has children
        if ($account->children()->exists()) {
            return response()->json([
                'message' => 'Cannot delete account with child accounts'
            ], 422);
        }

        // Check if account has journal entries
        if ($account->journalEntries()->exists()) {
            return response()->json([
                'message' => 'Cannot delete account with journal entries'
            ], 422);
        }

        $account->delete();

        return response()->json([
            'message' => 'Account deleted successfully'
        ]);
    }

    /**
     * Get chart of accounts tree structure
     */
    public function tree(Request $request): JsonResponse
    {
        $accountType = $request->get('account_type');

        $query = Account::with(['children' => function ($query) {
            $query->orderBy('account_code');
        }])->whereNull('parent_account_id');

        if ($accountType) {
            $query->where('account_type', $accountType);
        }

        $accounts = $query->orderBy('account_code')->get();

        // Build tree structure with balances
        $tree = $accounts->map(function ($account) {
            return $this->buildAccountTree($account);
        });

        return response()->json($tree);
    }

    /**
     * Get account balances summary
     */
    public function balances(Request $request): JsonResponse
    {
        $asOfDate = $request->get('as_of_date', now()->toDateString());

        $balances = [
            'assets' => $this->getAccountTypeBalance('asset', $asOfDate),
            'liabilities' => $this->getAccountTypeBalance('liability', $asOfDate),
            'equity' => $this->getAccountTypeBalance('equity', $asOfDate),
            'revenue' => $this->getAccountTypeBalance('revenue', $asOfDate),
            'expenses' => $this->getAccountTypeBalance('expense', $asOfDate),
        ];

        // Calculate net income
        $balances['net_income'] = $balances['revenue'] - $balances['expenses'];

        // Verify accounting equation: Assets = Liabilities + Equity + Net Income
        $balances['equation_check'] = [
            'assets' => $balances['assets'],
            'liabilities_equity_income' => $balances['liabilities'] + $balances['equity'] + $balances['net_income'],
            'balanced' => abs($balances['assets'] - ($balances['liabilities'] + $balances['equity'] + $balances['net_income'])) < 0.01
        ];

        return response()->json($balances);
    }

    /**
     * Check if creating a parent relationship would create a circular reference
     */
    private function wouldCreateCircularReference($accountId, $parentId): bool
    {
        $parent = Account::find($parentId);

        while ($parent) {
            if ($parent->id === $accountId) {
                return true;
            }
            $parent = $parent->parent;
        }

        return false;
    }

    /**
     * Build account tree with balances
     */
    private function buildAccountTree($account): array
    {
        $balance = $account->calculateBalance();

        $tree = [
            'id' => $account->id,
            'code' => $account->account_code,
            'name' => $account->account_name,
            'account_type' => $account->account_type,
            'account_subtype' => $account->account_subtype,
            'description' => $account->description,
            'is_active' => $account->is_active,
            'is_system' => $account->is_system_account,
            'balance' => $balance,
            'formatted_balance' => '$' . number_format($balance, 2),
            'children' => []
        ];

        if ($account->children->isNotEmpty()) {
            $tree['children'] = $account->children->map(function ($child) {
                return $this->buildAccountTree($child);
            })->toArray();
        }

        return $tree;
    }

    /**
     * Get total balance for account type
     */
    private function getAccountTypeBalance($accountType, $asOfDate): float
    {
        $accounts = Account::where('account_type', $accountType)
                          ->where('is_active', true)
                          ->get();

        return $accounts->sum(function ($account) use ($asOfDate) {
            return $account->calculateBalance($asOfDate);
        });
    }
}
