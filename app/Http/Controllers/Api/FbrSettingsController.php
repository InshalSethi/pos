<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\FbrSetting;
use App\Models\FbrEntry;
use App\Services\FbrService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FbrSettingsController extends Controller
{
    protected FbrService $fbrService;

    public function __construct(FbrService $fbrService)
    {
        $this->fbrService = $fbrService;
    }

    /**
     * Get FBR settings for the active company or specified company_id.
     */
    public function getSettings(Request $request): JsonResponse
    {
        $companyId = $request->input('company_id') ?: auth()->user()->current_company_id;
        $setting = FbrSetting::getSettings($companyId);

        $companies = Company::where(function ($q) {
            $user = auth()->user();
            if ($user->hasRole(['admin', 'owner', 'super-admin'])) {
                return;
            }
            $q->where('user_id', $user->id)
              ->orWhereHas('users', fn($uq) => $uq->where('users.id', $user->id));
        })->get(['id', 'company_name as name']);

        return response()->json([
            'success' => true,
            'setting' => $setting,
            'company_id' => $companyId,
            'companies' => $companies,
        ]);
    }

    /**
     * Update FBR settings for the specified company.
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'nullable|exists:companies,id',
            'is_enabled' => 'required|boolean',
            'environment' => 'required|in:sandbox,production',
            'pos_id' => 'nullable|string|max:100',
            'ntn' => 'nullable|string|max:100',
            'strn' => 'nullable|string|max:100',
            'business_name' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'api_token' => 'nullable|string',
            'base_url' => 'nullable|string|max:255',
            'auto_sync' => 'required|boolean',
            'sync_sales' => 'required|boolean',
            'sync_purchases' => 'required|boolean',
            'sync_transactions' => 'required|boolean',
            'sync_payments' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $companyId = $request->input('company_id') ?: auth()->user()->current_company_id;

        $setting = FbrSetting::where('company_id', $companyId)->first();

        if (!$setting) {
            $setting = new FbrSetting();
            $setting->company_id = $companyId;
        }

        $setting->fill([
            'is_enabled' => $request->boolean('is_enabled'),
            'environment' => $request->input('environment', 'sandbox'),
            'pos_id' => $request->input('pos_id'),
            'ntn' => $request->input('ntn'),
            'strn' => $request->input('strn'),
            'business_name' => $request->input('business_name'),
            'branch_name' => $request->input('branch_name'),
            'api_token' => $request->input('api_token'),
            'base_url' => $request->input('base_url'),
            'auto_sync' => $request->boolean('auto_sync'),
            'sync_sales' => $request->boolean('sync_sales'),
            'sync_purchases' => $request->boolean('sync_purchases'),
            'sync_transactions' => $request->boolean('sync_transactions'),
            'sync_payments' => $request->boolean('sync_payments'),
        ]);

        $setting->save();

        return response()->json([
            'success' => true,
            'message' => 'FBR configuration saved successfully for company!',
            'setting' => $setting,
        ]);
    }

    /**
     * Test connection to FBR API.
     */
    public function testConnection(Request $request): JsonResponse
    {
        $companyId = $request->input('company_id') ?: auth()->user()->current_company_id;
        $setting = FbrSetting::getSettings($companyId);

        if ($request->has('pos_id')) {
            $setting->pos_id = $request->input('pos_id');
            $setting->environment = $request->input('environment', 'sandbox');
            $setting->api_token = $request->input('api_token');
            $setting->base_url = $request->input('base_url');
        }

        $result = $this->fbrService->testConnection($setting);

        return response()->json($result);
    }

    /**
     * Get list of FBR entries (logs) for company.
     */
    public function getEntries(Request $request): JsonResponse
    {
        $companyId = $request->input('company_id') ?: auth()->user()->current_company_id;

        $query = FbrEntry::where('company_id', $companyId);

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('buyer_name', 'like', "%{$search}%")
                  ->orWhere('buyer_ntn', 'like', "%{$search}%")
                  ->orWhere('fbr_invoice_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        $summary = [
            'total' => (clone $query)->count(),
            'synced' => (clone $query)->where('status', 'synced')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'failed' => (clone $query)->where('status', 'failed')->count(),
        ];

        $entries = $query->orderBy('id', 'desc')->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'entries' => $entries,
        ]);
    }

    /**
     * Manually sync an FBR entry.
     */
    public function syncEntry(FbrEntry $fbrEntry): JsonResponse
    {
        $success = $this->fbrService->syncEntry($fbrEntry);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'FBR entry synced successfully!' : 'Failed to sync FBR entry: ' . $fbrEntry->error_message,
            'entry' => $fbrEntry->fresh(),
        ]);
    }

    /**
     * Sync all pending or failed entries.
     */
    public function syncAllPending(Request $request): JsonResponse
    {
        $companyId = $request->input('company_id') ?: auth()->user()->current_company_id;

        $pendingEntries = FbrEntry::where('company_id', $companyId)
            ->whereIn('status', ['pending', 'failed'])
            ->get();

        $syncedCount = 0;
        $failedCount = 0;

        foreach ($pendingEntries as $entry) {
            if ($this->fbrService->syncEntry($entry)) {
                $syncedCount++;
            } else {
                $failedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Synced {$syncedCount} entries successfully. {$failedCount} failed.",
            'synced_count' => $syncedCount,
            'failed_count' => $failedCount,
        ]);
    }
}
