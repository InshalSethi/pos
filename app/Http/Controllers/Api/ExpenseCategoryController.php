<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\ExpenseCategory;
use App\Models\Scopes\CompanyScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ExpenseCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:expenses.view')->only(['index', 'show']);
        $this->middleware('permission:expenses.create')->only(['store']);
        $this->middleware('permission:expenses.edit')->only(['update']);
        $this->middleware('permission:expenses.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ExpenseCategory::with(['parent', 'children']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by parent category
        if ($request->has('parent_id')) {
            if ($request->get('parent_id') === 'null') {
                $query->whereNull('parent_category_id');
            } else {
                $query->where('parent_category_id', $request->get('parent_id'));
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        if ($request->has('per_page')) {
            $categories = $query->paginate($request->get('per_page', 15));
        } else {
            $categories = $query->get();
        }

        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $companyId = auth()->user()->current_company_id ?: auth()->user()->company_id;
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('expense_categories', 'code')->where('company_id', $companyId),
            ],
            'parent_category_id' => 'nullable|exists:expense_categories,id',
            'is_active' => 'boolean',
        ], [
            'code.required' => 'Ledger Code is required.',
            'code.unique' => 'This ledger/category code is already in use. Please choose a unique code.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'This ledger/category code is already in use. Please choose a unique code.',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['company_id'] = $companyId;

        $category = ExpenseCategory::create($data);
        $category->load(['parent', 'children']);

        // Auto-sync with Chart of Accounts
        $this->syncCategoryWithChartOfAccounts($category);

        return response()->json([
            'message' => 'Expense category created successfully',
            'category' => $category
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ExpenseCategory $expenseCategory): JsonResponse
    {
        $expenseCategory->load(['parent', 'children', 'expenses']);

        return response()->json($expenseCategory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExpenseCategory $expenseCategory): JsonResponse
    {
        $companyId = auth()->user()->current_company_id ?: auth()->user()->company_id;
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('expense_categories', 'code')->ignore($expenseCategory->id)->where('company_id', $companyId),
            ],
            'parent_category_id' => 'nullable|exists:expense_categories,id',
            'is_active' => 'boolean',
        ], [
            'code.required' => 'Ledger Code is required.',
            'code.unique' => 'This ledger/category code is already in use. Please choose a unique code.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'This ledger/category code is already in use. Please choose a unique code.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Prevent circular reference
        if ($request->has('parent_category_id') && $request->parent_category_id) {
            $parentCategory = ExpenseCategory::find($request->parent_category_id);
            if ($parentCategory && $this->wouldCreateCircularReference($expenseCategory, $parentCategory)) {
                return response()->json([
                    'message' => 'Cannot set parent category as it would create a circular reference'
                ], 422);
            }
        }

        $oldCode = $expenseCategory->code;
        $expenseCategory->update($request->all());
        $expenseCategory->load(['parent', 'children']);

        // Auto-sync with Chart of Accounts
        $this->syncCategoryWithChartOfAccounts($expenseCategory, $oldCode);

        return response()->json([
            'message' => 'Expense category updated successfully',
            'category' => $expenseCategory
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExpenseCategory $expenseCategory): JsonResponse
    {
        // Check if category has expenses
        if ($expenseCategory->expenses()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category that has expenses associated with it'
            ], 422);
        }

        // Check if category has children
        if ($expenseCategory->children()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category that has subcategories'
            ], 422);
        }

        // Delete COA account if exists and has no journal entries
        if ($expenseCategory->code) {
            $companyId = $expenseCategory->company_id ?: auth()->user()->current_company_id;
            $account = Account::withoutGlobalScope(CompanyScope::class)
                ->where('company_id', $companyId)
                ->where('account_code', $expenseCategory->code)
                ->first();

            if ($account && !$account->is_system_account && $account->journalEntries()->count() === 0) {
                $account->delete();
            }
        }

        $expenseCategory->delete();

        return response()->json([
            'message' => 'Expense category deleted successfully'
        ]);
    }

    /**
     * Get category tree structure
     */
    public function tree(): JsonResponse
    {
        $categories = ExpenseCategory::with(['children' => function ($query) {
            $query->active()->orderBy('name');
        }])
        ->whereNull('parent_category_id')
        ->active()
        ->orderBy('name')
        ->get();

        return response()->json($categories);
    }

    /**
     * Check if setting a parent would create circular reference
     */
    private function wouldCreateCircularReference(ExpenseCategory $category, ExpenseCategory $potentialParent): bool
    {
        if ($category->id === $potentialParent->id) {
            return true;
        }

        $children = $category->getAllChildren();
        return $children->contains('id', $potentialParent->id);
    }

    /**
     * Create or update corresponding Account in Chart of Accounts (chart_of_accounts table)
     */
    private function syncCategoryWithChartOfAccounts(ExpenseCategory $category, ?string $oldCode = null): void
    {
        $companyId = $category->company_id ?: (auth()->user()?->current_company_id ?: auth()->user()?->company_id);
        if (!$companyId) return;

        $searchCode = $oldCode ?: $category->code;

        $account = Account::withoutGlobalScope(CompanyScope::class)
            ->where('company_id', $companyId)
            ->where(function ($q) use ($searchCode, $category) {
                if ($searchCode) {
                    $q->where('account_code', $searchCode);
                }
                $q->orWhere('account_code', $category->code)
                  ->orWhere('account_name', $category->name);
            })
            ->first();

        // Find parent account in COA if parent_category_id is specified
        $parentAccountId = null;
        if ($category->parent_category_id) {
            $parentCategory = ExpenseCategory::find($category->parent_category_id);
            if ($parentCategory) {
                $parentCOA = Account::withoutGlobalScope(CompanyScope::class)
                    ->where('company_id', $companyId)
                    ->where(function ($q) use ($parentCategory) {
                        if ($parentCategory->code) {
                            $q->where('account_code', $parentCategory->code);
                        }
                        $q->orWhere('account_name', $parentCategory->name);
                    })
                    ->first();
                if ($parentCOA) {
                    $parentAccountId = $parentCOA->id;
                }
            }
        }

        // Fallback to main Operating Expenses header (6000)
        if (!$parentAccountId) {
            $headerAccount = Account::withoutGlobalScope(CompanyScope::class)
                ->where('company_id', $companyId)
                ->where('account_code', '6000')
                ->first();
            if ($headerAccount) {
                $parentAccountId = $headerAccount->id;
            }
        }

        if ($account) {
            $account->update([
                'account_code'      => $category->code ?: $account->account_code,
                'account_name'      => $category->name,
                'account_type'      => 'expense',
                'account_subtype'   => 'operating_expense',
                'description'       => $category->description,
                'is_active'         => $category->is_active,
                'parent_account_id' => $parentAccountId,
            ]);
        } else {
            Account::create([
                'company_id'        => $companyId,
                'account_code'      => $category->code,
                'account_name'      => $category->name,
                'account_type'      => 'expense',
                'account_subtype'   => 'operating_expense',
                'description'       => $category->description,
                'is_active'         => $category->is_active,
                'is_system_account' => false,
                'opening_balance'   => 0.00,
                'current_balance'   => 0.00,
                'parent_account_id' => $parentAccountId,
            ]);
        }
    }
}
