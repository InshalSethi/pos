<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CompanySetupController extends Controller
{
    /**
     * Wizard Entry Point
     *
     * Handles three routing contexts:
     *   A) continue_draft_id  → Resume a specific saved draft at its stored step
     *   B) start_fresh_flow   → Immediately scaffold a new blank draft record
     *   C) Fallback           → Block arbitrary manual URL access
     */
    /**
     * Check if user reached their active subscription company limit
     */
    public static function hasReachedCompanyLimit($user): bool
    {
        if (!$user) return false;

        $planLimits = [
            'standard'   => 1,
            'starter'    => 1,
            'free'       => 1,
            'basic'      => 1,
            'advance'    => 2,
            'enterprise' => 10,
            'elite'      => 10,
            'custom'     => 999,
        ];

        $payment = \App\Models\SubscriptionPayment::where('user_id', $user->id)->orderBy('id', 'desc')->first();
        $license = \App\Models\License::first();

        $planSlug = 'standard';
        if ($payment && $payment->plan_name) {
            $planSlug = strtolower(trim($payment->plan_name));
        } elseif ($license && $license->plan) {
            $planSlug = strtolower(trim($license->plan));
        }

        $dbPlan = \App\Models\SubscriptionPlan::whereRaw('LOWER(slug) = ?', [$planSlug])
            ->orWhereRaw('LOWER(name) = ?', [$planSlug])
            ->first();

        $maxCompanies = $dbPlan ? (int) $dbPlan->max_companies : (int) ($planLimits[$planSlug] ?? 1);
        $activeCompaniesCount = Company::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();

        return $activeCompaniesCount >= $maxCompanies;
    }

    /**
     * Wizard Entry Point
     * Routes to the correct context: resume draft, start fresh, or block access.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = auth()->user() ?? \Illuminate\Support\Facades\Auth::guard('sanctum')->user();

        if (!$user && ($request->filled('token') || $request->filled('auth_token'))) {
            $rawToken = $request->query('token') ?? $request->query('auth_token');
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($rawToken);
            if ($accessToken && $accessToken->tokenable) {
                $user = $accessToken->tokenable;
            }
        }

        if (!$user) {
            return redirect('/login');
        }

        if (!\Illuminate\Support\Facades\Auth::guard('web')->check()) {
            \Illuminate\Support\Facades\Auth::guard('web')->login($user);
        }

        $hasExistingActiveCompany = Company::where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        $isCreateMode = $request->query('mode') === 'create_new'
            || $request->boolean('start_fresh_flow')
            || session('creating_new_company')
            || session('creating_subsequent_company');

        // Prevent direct URL access if not creating a new company or resuming a draft
        if (!$isCreateMode && !$request->filled('continue_draft_id')) {
            return redirect()->to('/owner/companies');
        }

        // ── Check Plan Limit for fresh creations ───────────────────────
        if ($isCreateMode && !$request->filled('continue_draft_id')) {
            if (self::hasReachedCompanyLimit($user)) {
                return redirect()->to('/owner/companies?limit_reached=1')
                    ->with('error', 'Company limit reached for your current plan. Please upgrade your subscription.');
            }
        }

        if ($isCreateMode) {
            session([
                'creating_new_company' => true,
                'creating_subsequent_company' => true
            ]);
        }

        // ── Context A: Resume a specific saved draft ──────────────────
        $draftId = $request->query('continue_draft_id') ?: $request->query('draft_id');
        if (!empty($draftId)) {
            $company = Company::where('id', $draftId)
                ->where('user_id', $user->id)
                ->where('status', 'draft')
                ->first();

            if ($company) {
                session([
                    'creating_new_company' => true,
                    'creating_subsequent_company' => true
                ]);

                return view('company-setup', [
                    'company'                  => $company,
                    'currentStep'              => $company->draft_step ?? 1,
                    'hasExistingActiveCompany' => $hasExistingActiveCompany,
                ]);
            }
        }

        // ── Context B: Start fresh flow for new company ────────────────
        $company = Company::create([
            'user_id'           => $user->id,
            'company_name'      => '',
            'company_email'     => $user->email,
            'company_phone'     => '',
            'owner_role'        => 'Owner/CEO',
            'team_size'         => 'Just Me',
            'intended_tasks'    => [],
            'business_type'     => '',
            'business_scale'    => 'Single Outlet',
            'country'           => 'United States',
            'system_language'   => 'en',
            'base_currency'     => 'USD',
            'timezone_offset'   => 'UTC',
            'fiscal_year_start' => date('Y-01-01'),
            'status'            => 'draft',
            'draft_step'        => 1,
        ]);

        session([
            'creating_new_company' => true,
            'creating_subsequent_company' => true
        ]);

        return view('company-setup', [
            'company'                  => $company,
            'currentStep'              => 1,
            'hasExistingActiveCompany' => $hasExistingActiveCompany,
        ]);
    }

    /**
     * New Company Initiation Endpoint
     * Sets session flags and redirects to company wizard with token support.
     */
    public function initiateNewCompany(Request $request): RedirectResponse
    {
        $user = auth()->user() ?? \Illuminate\Support\Facades\Auth::guard('sanctum')->user();

        if (!$user && ($request->filled('token') || $request->filled('auth_token'))) {
            $rawToken = $request->query('token') ?? $request->query('auth_token');
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($rawToken);
            if ($accessToken && $accessToken->tokenable) {
                $user = $accessToken->tokenable;
            }
        }

        if (!$user) {
            return redirect('/login');
        }

        if (!\Illuminate\Support\Facades\Auth::guard('web')->check()) {
            \Illuminate\Support\Facades\Auth::guard('web')->login($user);
        }

        // Enforce plan quota limit
        if (self::hasReachedCompanyLimit($user)) {
            return redirect()->to('/owner/companies?limit_reached=1')
                ->with('error', 'Company limit reached for your current plan. Please upgrade your subscription.');
        }

        session([
            'creating_new_company' => true,
            'creating_subsequent_company' => true
        ]);

        $tokenQuery = $request->query('token') ? '&token=' . urlencode($request->query('token')) : '';
        return redirect()->to('/company-setup?mode=create_new&start_fresh_flow=true' . $tokenQuery);
    }

    /**
     * Abort Registration Handler
     *
     * Path A (Fresh User):   Full atomic teardown — session, drafts, user record wiped.
     * Path B (Existing Tenant): Single draft purged — active companies untouched.
     */
    public function abortRegistration(Request $request): RedirectResponse
    {
        abort_unless(auth()->check(), 302, redirect('/register'));

        $user = auth()->user();
        $companyId = $request->input('company_id');

        if (!empty($companyId)) {
            Company::withTrashed()
                ->where('id', $companyId)
                ->where('user_id', $user->id)
                ->where('status', 'draft')
                ->forceDelete();
        } else {
            Company::withTrashed()
                ->where('user_id', $user->id)
                ->where('status', 'draft')
                ->orderBy('id', 'desc')
                ->first()
                ?->forceDelete();
        }

        session()->forget(['creating_new_company', 'creating_subsequent_company']);

        return redirect('/owner/companies')->with('info', 'Company setup discarded.');
    }

    /**
     * Save Setup Progress as Draft
     * Persists current step index and marks record as resumable.
     */
    public function saveSetupAsDraft(Request $request): RedirectResponse
    {
        abort_unless(auth()->check(), 403);

        $user = auth()->user();
        $companyId = $request->input('company_id') ?: $request->input('draft_id');
        $companyName = $request->input('company_name') ?: 'Draft Company';

        $updateData = [
            'company_name'        => $companyName,
            'company_email'       => $request->input('company_email') ?: $user->email,
            'company_phone'       => $request->input('company_phone', ''),
            'registration_number' => $request->input('registration_number', ''),
            'owner_role'          => $request->input('owner_role', 'Owner/CEO'),
            'team_size'           => $request->input('team_size', 'Just Me'),
            'tax_number'          => $request->input('tax_number', ''),
            'business_address'    => $request->input('business_address', ''),
            'business_type'       => $request->input('business_type', ''),
            'business_scale'      => $request->input('business_scale', 'Single Outlet'),
            'country'             => $request->input('country', 'United States'),
            'system_language'     => $request->input('system_language', 'en'),
            'base_currency'       => $request->input('base_currency', 'USD'),
            'timezone_offset'     => $request->input('timezone_offset', 'UTC'),
            'fiscal_year_start'   => $request->input('fiscal_year_start', date('Y-01-01')),
            'status'              => 'draft',
            'draft_step'          => (int) ($request->input('current_step', 1)),
        ];

        if ($companyId) {
            Company::where('id', $companyId)
                ->where('user_id', $user->id)
                ->update($updateData);
        } else {
            $updateData['user_id'] = $user->id;
            Company::create($updateData);
        }

        session()->forget(['creating_new_company', 'creating_subsequent_company']);

        return redirect('/owner/companies')->with('status', 'Progress saved as draft.');
    }

    /**
     * API: Get Draft Details
     */
    public function getDraftApi(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user() ?? \Illuminate\Support\Facades\Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $draft = Company::where('id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'draft')
            ->first();

        if (!$draft) {
            return response()->json(['message' => 'Draft not found.'], 404);
        }

        return response()->json([
            'draft_id'     => $draft->id,
            'current_step' => $draft->draft_step ?? 1,
            'step_data'    => [
                'company_name'        => $draft->company_name,
                'registration_number' => $draft->registration_number,
                'company_email'       => $draft->company_email,
                'company_phone'       => $draft->company_phone,
                'owner_role'          => $draft->owner_role ?? 'Owner/CEO',
                'team_size'           => $draft->team_size ?? 'Just Me',
                'tax_number'          => $draft->tax_number,
                'business_address'    => $draft->business_address,
                'business_type'       => $draft->business_type,
                'business_scale'      => $draft->business_scale ?? 'Single Outlet',
                'country'             => $draft->country ?? 'United States',
                'country_code'        => $draft->country === 'Pakistan' ? 'PK' : ($draft->country === 'United Kingdom' ? 'GB' : 'US'),
                'system_language'     => $draft->system_language ?? 'en',
                'base_currency'       => $draft->base_currency ?? 'USD',
                'currency'            => $draft->base_currency ?? 'USD',
                'timezone_offset'     => $draft->timezone_offset ?? 'UTC',
                'fiscal_year_start'   => $draft->fiscal_year_start ?? date('Y-01-01'),
                'intended_tasks'      => is_string($draft->intended_tasks) ? json_decode($draft->intended_tasks, true) : ($draft->intended_tasks ?? []),
                'logo_path'           => $draft->company_logo ?? $draft->logo_url,
            ],
        ]);
    }

    /**
     * API: Save Draft Payload
     */
    public function saveDraftApi(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user() ?? \Illuminate\Support\Facades\Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $draftId = $request->input('draft_id') ?: $request->input('company_id');
        $stepData = $request->input('step_data', []);
        $currentStep = $request->input('current_step', $request->input('step', 1));

        $companyName = $stepData['company_name'] ?? $request->input('company_name', 'Draft Company');

        $payload = [
            'company_name'        => $companyName ?: 'Draft Company',
            'company_email'       => $stepData['company_email'] ?? $request->input('company_email', $user->email),
            'company_phone'       => $stepData['company_phone'] ?? $request->input('company_phone', ''),
            'registration_number' => $stepData['registration_number'] ?? $request->input('registration_number', ''),
            'owner_role'          => $stepData['owner_role'] ?? $request->input('owner_role', 'Owner/CEO'),
            'team_size'           => $stepData['team_size'] ?? $request->input('team_size', 'Just Me'),
            'tax_number'          => $stepData['tax_number'] ?? $request->input('tax_number', ''),
            'business_address'    => $stepData['business_address'] ?? $request->input('business_address', ''),
            'business_type'       => $stepData['business_type'] ?? $request->input('business_type', ''),
            'business_scale'      => $stepData['business_scale'] ?? $request->input('business_scale', 'Single Outlet'),
            'country'             => $stepData['country'] ?? $request->input('country', 'United States'),
            'system_language'     => $stepData['system_language'] ?? $request->input('system_language', 'en'),
            'base_currency'       => $stepData['base_currency'] ?? $stepData['currency'] ?? $request->input('base_currency', 'USD'),
            'timezone_offset'     => $stepData['timezone_offset'] ?? $request->input('timezone_offset', 'UTC'),
            'fiscal_year_start'   => $stepData['fiscal_year_start'] ?? $request->input('fiscal_year_start', date('Y-01-01')),
            'status'              => 'draft',
            'draft_step'          => (int) $currentStep,
        ];

        if (isset($stepData['intended_tasks'])) {
            $payload['intended_tasks'] = is_array($stepData['intended_tasks']) ? $stepData['intended_tasks'] : [];
        }

        if ($draftId) {
            $draft = Company::where('id', $draftId)
                ->where('user_id', $user->id)
                ->first();

            if ($draft) {
                $draft->update($payload);
            } else {
                $payload['user_id'] = $user->id;
                $draft = Company::create($payload);
            }
        } else {
            $payload['user_id'] = $user->id;
            $draft = Company::create($payload);
        }

        return response()->json([
            'message'  => 'Draft saved successfully.',
            'draft_id' => $draft->id,
            'draft'    => $draft,
        ]);
    }

    /**
     * Purge a specific draft record.
     */
    public function purgeIndividualDraft(Request $request, int $id): RedirectResponse
    {
        abort_unless(auth()->check(), 403);

        Company::where('id', $id)
            ->where('user_id', auth()->id())
            ->where('status', 'draft')  // Immutability guard: active records are untouchable
            ->firstOrFail()
            ->delete();

        return redirect()->back()->with('success', 'Draft workspace permanently removed.');
    }

    /**
     * Safely processes array matrices to wipe multiple selected draft workspaces synchronously.
     * Guarantees zero leakage into authenticated production active database records.
     */
    public function purgeBulkDrafts(Request $request): RedirectResponse
    {
        $request->validate([
            'draft_ids' => 'required|string' // Validates the passed JSON matrix payload string
        ]);

        if (auth()->check()) {
            // Parse the payload back to standard integer arrays
            $idsArray = json_decode($request->input('draft_ids'), true);

            if (is_array($idsArray) && count($idsArray) > 0) {
                // Run selective massive erasure inside rigid ownership context constraints
                Company::whereIn('id', $idsArray)
                    ->where('user_id', auth()->id())
                    ->where('status', 'draft') // Double security layer insulation
                    ->delete();

                return redirect()->back()->with('success', 'Selected custom workspace drafts cleared out successfully.');
            }
        }

        return redirect('/login');
    }

    public function discardActiveSetup(Request $request): RedirectResponse
    {
        abort_unless(auth()->check(), 403);

        $company = Company::where('id', $request->input('company_id'))
            ->where('user_id', auth()->id())
            ->where('status', 'draft')
            ->firstOrFail();

        $company->delete();

        session()->forget(['creating_new_company', 'creating_subsequent_company']);

        return redirect('/owner/companies')->with('status', 'Company setup discarded.');
    }

    /**
     * SOFT DELETE COMPANY WORKSPACE
     *
     * Archives a company record by writing a deleted_at timestamp.
     * The row is excluded from all standard Eloquent queries but
     * remains fully recoverable via withTrashed() or restore().
     *
     * Security enforcements:
     *   1. Authentication check — unauthenticated calls return 401
     *   2. Active workspace guard — cannot soft-delete your own current context
     *   3. Ownership check — only the tenant owner can archive their own companies
     *   4. Record existence check — 404 if not found or not owned
     *
     * Returns JSON for AJAX table row removal without full page reload.
     */
    public function destroyCompany(int $id): \Illuminate\Http\JsonResponse
    {
        // ── Guard 1: Authentication ───────────────────────────────────
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please log in again.',
            ], 401);
        }

        $user = auth()->user();

        // ── Guard 2: Active Workspace Self-Destruction Block ──────────
        // A user cannot soft-delete the company they are currently operating inside
        if ((int) $user->current_company_id === (int) $id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot archive your currently active workspace. Switch to a different workspace first, then retry.',
            ], 422);
        }

        // ── Guard 3 & 4: Ownership Verification ───────────────────────
        $company = Company::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Workspace not found or you do not have permission to archive it.',
            ], 404);
        }

        // ── Soft Delete Execution ─────────────────────────────────────
        // Sets deleted_at = now(). Row is preserved in DB but hidden from queries.
        $company->delete();

        // ── Audit Log ─────────────────────────────────────────────────
        \Illuminate\Support\Facades\Log::info('Company soft-deleted', [
            'company_id'   => $company->id,
            'company_name' => $company->company_name,
            'deleted_by'   => $user->id,
            'deleted_at'   => now()->toDateTimeString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Workspace '{$company->company_name}' has been archived successfully. It can be restored if needed.",
        ], 200);
    }
}
