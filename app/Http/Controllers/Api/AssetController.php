<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AssetController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:accounting.view')->only(['index', 'show', 'summary']);
        $this->middleware('permission:accounting.create')->only(['store']);
        $this->middleware('permission:accounting.edit')->only(['update']);
        $this->middleware('permission:accounting.delete')->only(['destroy']);
    }

    /**
     * Display listing of assets.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        $query = Asset::with([
            'product:id,name,sku,cost_price',
            'chartAccount:id,account_code,account_name',
            'supplier:id,name,company_name'
        ])->where('company_id', $companyId);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('asset_code', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category') && $request->get('category') !== 'all') {
            $query->where('category', $request->get('category'));
        }

        if ($request->filled('status') && $request->get('status') !== 'all') {
            $query->where('status', $request->get('status'));
        }

        $perPage = $request->get('per_page', 15);
        $assets = $query->latest()->paginate($perPage);

        // Recalculate current depreciation info dynamically for each asset
        $assets->getCollection()->transform(function ($asset) {
            $dep = $asset->calculateDepreciation();
            $asset->calculated_accumulated_depreciation = $dep['accumulated_depreciation'];
            $asset->calculated_current_value = $dep['current_value'];
            $asset->annual_depreciation = $dep['annual_depreciation'];
            $asset->age_years = $dep['age_years'];
            $asset->asset_name = $asset->name ?: ($asset->product ? $asset->product->name : 'Fixed Asset');
            $asset->asset_category = $asset->category ?: 'Fixed Asset';
            $asset->current_valuation = $dep['current_value'];
            return $asset;
        });

        return response()->json($assets);
    }

    /**
     * Store a new asset.
     */
    public function store(Request $request): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        if (!$request->filled('asset_code')) {
            $request->merge(['asset_code' => 'AST-' . strtoupper(uniqid())]);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'name' => 'nullable|string|max:255',
            'asset_code' => 'nullable|string|unique:assets,asset_code,NULL,id,company_id,' . $companyId,
            'category' => 'nullable|string',
            'serial_number' => 'nullable|string|max:100',
            'quantity' => 'nullable|integer|min:1',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'salvage_value' => 'nullable|numeric|min:0',
            'useful_life_years' => 'required|integer|min:1',
            'depreciation_method' => 'required|in:straight_line,declining_balance,none',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:active,in_use,in_maintenance,disposed,written_off',
            'chart_account_id' => 'nullable|exists:chart_of_accounts,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $product = Product::find($request->product_id);
            $assetName = $request->name ?: ($product ? $product->name : 'Fixed Asset Item');
            $categoryName = $request->category ?: 'Fixed Asset';

            $purchaseCost = (float) $request->purchase_cost;
            $salvage = (float) ($request->salvage_value ?? 0);

            $asset = Asset::create([
                'company_id' => $companyId,
                'product_id' => $request->product_id,
                'asset_code' => $request->asset_code,
                'name' => $assetName,
                'category' => $categoryName,
                'serial_number' => $request->serial_number,
                'quantity' => $request->quantity ?? 1,
                'purchase_date' => $request->purchase_date,
                'purchase_cost' => $purchaseCost,
                'current_value' => $purchaseCost,
                'salvage_value' => $salvage,
                'useful_life_years' => $request->useful_life_years,
                'depreciation_method' => $request->depreciation_method,
                'accumulated_depreciation' => 0.00,
                'location' => $request->location,
                'status' => $request->status,
                'chart_account_id' => $request->chart_account_id,
                'supplier_id' => $request->supplier_id,
                'notes' => $request->notes,
            ]);

            // Update calculated values
            $dep = $asset->calculateDepreciation();
            $asset->update([
                'accumulated_depreciation' => $dep['accumulated_depreciation'],
                'current_value' => $dep['current_value'],
            ]);

            // Create Double-Entry Journal Entry for Fixed Asset Acquisition
            $this->createFixedAssetJournalEntry($asset);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Asset recorded & journal entry created successfully',
                'asset' => $asset->fresh(['chartAccount', 'supplier', 'product'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper: Post double-entry accounting journal entry for Fixed Asset addition
     */
    protected function createFixedAssetJournalEntry(Asset $asset): void
    {
        $companyId = $asset->company_id;
        $cost = (float) $asset->purchase_cost;

        if ($cost <= 0) return;

        // 1. Resolve Fixed Asset COA (Account Code 1500 or Account Name 'Fixed Assets')
        $fixedAssetAccount = Account::where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('account_code', '1500')
                  ->orWhere('account_code', '1510')
                  ->orWhere('account_name', 'LIKE', '%Fixed Asset%');
            })
            ->whereDoesntHave('children')
            ->first();

        if (!$fixedAssetAccount) {
            $fixedAssetAccount = Account::create([
                'company_id' => $companyId,
                'account_code' => '1500',
                'account_name' => 'Fixed Assets',
                'account_type' => 'asset',
                'account_subtype' => 'fixed_asset',
                'opening_balance' => 0,
                'current_balance' => 0,
                'is_active' => true,
                'is_system_account' => true,
            ]);
        }

        // 2. Resolve Cash / Owner's Equity Credit Account (Account Code 1010 or 3010)
        $creditAccount = Account::where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('account_code', '1010')
                  ->orWhere('account_code', '3010')
                  ->orWhere('account_name', 'LIKE', '%Cash%')
                  ->orWhere('account_name', 'LIKE', '%Opening Balance Equity%');
            })
            ->whereDoesntHave('children')
            ->first();

        if (!$creditAccount) {
            $creditAccount = Account::create([
                'company_id' => $companyId,
                'account_code' => '3010',
                'account_name' => 'Opening Balance Equity',
                'account_type' => 'equity',
                'account_subtype' => 'owner_equity',
                'opening_balance' => 0,
                'current_balance' => 0,
                'is_active' => true,
                'is_system_account' => true,
            ]);
        }

        // 3. Create Journal Entry
        $entryNumber = 'JE-FA-' . strtoupper(uniqid());
        $journalEntry = JournalEntry::create([
            'company_id' => $companyId,
            'entry_number' => $entryNumber,
            'entry_date' => $asset->purchase_date ?? now()->toDateString(),
            'reference' => "Fixed Asset #{$asset->asset_code}",
            'description' => "Fixed Asset Registration - {$asset->name}",
            'entry_type' => 'automatic',
            'status' => 'posted',
            'total_debit' => $cost,
            'total_credit' => $cost,
            'created_by' => auth()->id() ?: 1,
            'posted_by' => auth()->id() ?: 1,
            'posted_at' => now(),
            'source_type' => 'asset',
            'source_id' => $asset->id,
        ]);

        // Debit: Fixed Assets Account (Increases Asset)
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $fixedAssetAccount->id,
            'description' => "Fixed Asset Addition - {$asset->name}",
            'debit_amount' => $cost,
            'credit_amount' => 0,
        ]);

        // Credit: Cash / Equity Account (Decreases Cash or Increases Equity)
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $creditAccount->id,
            'description' => "Fixed Asset Acquisition Credit - {$asset->name}",
            'debit_amount' => 0,
            'credit_amount' => $cost,
        ]);

        // Recalculate exact ground-truth balances
        $fixedAssetAccount->updateCurrentBalance();
        $creditAccount->updateCurrentBalance();
    }

    /**
     * Show specified asset.
     */
    public function show(Asset $asset): JsonResponse
    {
        $asset->load(['product', 'chartAccount', 'supplier']);
        $dep = $asset->calculateDepreciation();

        $assetArray = $asset->toArray();
        $assetArray['calculated_accumulated_depreciation'] = $dep['accumulated_depreciation'];
        $assetArray['calculated_current_value'] = $dep['current_value'];
        $assetArray['annual_depreciation'] = $dep['annual_depreciation'];
        $assetArray['age_years'] = $dep['age_years'];
        $assetArray['asset_name'] = $asset->name;
        $assetArray['asset_category'] = $asset->category;

        return response()->json($assetArray);
    }

    /**
     * Update asset.
     */
    public function update(Request $request, Asset $asset): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'name' => 'nullable|string|max:255',
            'asset_code' => 'required|string|unique:assets,asset_code,' . $asset->id . ',id,company_id,' . $companyId,
            'category' => 'nullable|string',
            'serial_number' => 'nullable|string|max:100',
            'quantity' => 'nullable|integer|min:1',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'salvage_value' => 'nullable|numeric|min:0',
            'useful_life_years' => 'required|integer|min:1',
            'depreciation_method' => 'required|in:straight_line,declining_balance,none',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:active,in_use,in_maintenance,disposed,written_off',
            'chart_account_id' => 'nullable|exists:chart_of_accounts,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $product = Product::find($request->product_id);
            $assetName = $request->name ?: ($product ? $product->name : 'Fixed Asset Item');

            $asset->update([
                'product_id' => $request->product_id,
                'asset_code' => $request->asset_code,
                'name' => $assetName,
                'category' => $request->category ?: ($asset->category ?: 'Fixed Asset'),
                'serial_number' => $request->serial_number,
                'quantity' => $request->quantity ?? 1,
                'purchase_date' => $request->purchase_date,
                'purchase_cost' => $request->purchase_cost,
                'salvage_value' => $request->salvage_value ?? 0,
                'useful_life_years' => $request->useful_life_years,
                'depreciation_method' => $request->depreciation_method,
                'location' => $request->location,
                'status' => $request->status,
                'chart_account_id' => $request->chart_account_id,
                'supplier_id' => $request->supplier_id,
                'notes' => $request->notes,
            ]);

            $dep = $asset->calculateDepreciation();
            $asset->update([
                'accumulated_depreciation' => $dep['accumulated_depreciation'],
                'current_value' => $dep['current_value'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Asset updated successfully',
                'asset' => $asset->fresh(['chartAccount', 'supplier', 'product'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete asset.
     */
    public function destroy(Asset $asset): JsonResponse
    {
        $asset->delete();
        return response()->json(['success' => true, 'message' => 'Asset deleted successfully']);
    }

    /**
     * Asset Valuation & Summary Statistics.
     */
    public function summary(): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        $assets = Asset::where('company_id', $companyId)->get();

        $totalPurchaseCost = 0;
        $totalCurrentValuation = 0;
        $totalAccumulatedDepreciation = 0;

        $byCategory = [];

        foreach ($assets as $asset) {
            $dep = $asset->calculateDepreciation();

            $totalPurchaseCost += (float) $asset->purchase_cost;
            $totalCurrentValuation += $dep['current_value'];
            $totalAccumulatedDepreciation += $dep['accumulated_depreciation'];

            $cat = $asset->category ?: 'Fixed Asset';
            if (!isset($byCategory[$cat])) {
                $byCategory[$cat] = [
                    'category' => $cat,
                    'count' => 0,
                    'total_cost' => 0,
                    'current_value' => 0,
                ];
            }
            $byCategory[$cat]['count'] += $asset->quantity;
            $byCategory[$cat]['total_cost'] += (float) $asset->purchase_cost;
            $byCategory[$cat]['current_value'] += $dep['current_value'];
        }

        return response()->json([
            'total_assets_count' => $assets->sum('quantity'),
            'total_purchase_cost' => round($totalPurchaseCost, 2),
            'total_current_valuation' => round($totalCurrentValuation, 2),
            'total_accumulated_depreciation' => round($totalAccumulatedDepreciation, 2),
            'categories' => array_values($byCategory),
        ]);
    }
}
