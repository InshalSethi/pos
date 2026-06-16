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
     * Wizard Entry Point
     * Routes to the correct context: resume draft, start fresh, or block access.
     */
    public function index(Request $request): View|RedirectResponse
    {
        abort_unless(auth()->check(), 302, redirect('/login'));

        $user = auth()->user();

        $hasExistingActiveCompany = Company::where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        // ── Context A: Resume a specific saved draft ──────────────────
        if ($request->filled('continue_draft_id')) {
            $company = Company::where('id', $request->query('continue_draft_id'))
                ->where('user_id', $user->id)
                ->where('status', 'draft')
                ->firstOrFail();

            session(['creating_subsequent_company' => true]);

            return view('company-setup', [
                'company'                  => $company,
                'currentStep'              => $company->draft_step ?? 1,
                'hasExistingActiveCompany' => $hasExistingActiveCompany,
            ]);
        }

        // ── Context B: Start fresh (explicit flag OR no active company) ─
        if ($request->filled('start_fresh_flow') || !$hasExistingActiveCompany) {
            $company = Company::create([
                'user_id'           => $user->id,
                'company_name'      => 'Untitled Draft Workspace',
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

            session(['creating_subsequent_company' => true]);

            return view('company-setup', [
                'company'                  => $company,
                'currentStep'              => 1,
                'hasExistingActiveCompany' => $hasExistingActiveCompany,
            ]);
        }

        // ── Fallback: Block unparameterized direct access ─────────────
        return redirect('/');
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

        $hasActiveCompany = Company::where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        // ── PATH A: Fresh User — Full Account Teardown ────────────────
        if (!$hasActiveCompany) {
            try {
                \Illuminate\Support\Facades\DB::transaction(function () use ($user, $request) {
                    \Illuminate\Support\Facades\Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    Company::where('user_id', $user->id)->delete(); // Wipe all drafts
                    $user->delete();                                  // Drop user record
                });

            } catch (\Throwable $e) {
                \Log::error('abortRegistration teardown failed', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);

                return redirect('/register')->withErrors([
                    'abort' => 'Teardown failed. Please try again or contact support.'
                ]);
            }

            return redirect('/register')->with(
                'status', 'Registration cancelled. All your data has been permanently removed.'
            );
        }

        // ── PATH B: Existing Tenant — Purge Single Draft Only ─────────
        $validated = $request->validate([
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
        ]);

        if (!empty($validated['company_id'])) {
            Company::where('id', $validated['company_id'])
                ->where('user_id', $user->id)
                ->where('status', 'draft')  // Hard guard: active records are immutable
                ->delete();
        }

        session()->forget('creating_subsequent_company');

        return redirect('/')->with('info', 'Sub-company setup discarded safely.');
    }

    /**
     * Save Setup Progress as Draft
     * Persists current step index and marks record as resumable.
     */
    public function saveSetupAsDraft(Request $request): RedirectResponse
    {
        abort_unless(auth()->check(), 403);

        $validated = $request->validate([
            'company_id'   => ['required', 'integer', 'exists:companies,id'],
            'current_step' => ['required', 'integer', 'between:1,4'],
        ]);

        Company::where('id', $validated['company_id'])
            ->where('user_id', auth()->id())
            ->update([
                'status'     => 'draft',
                'draft_step' => $validated['current_step'],
            ]);

        session()->forget('creating_subsequent_company');

        return redirect('/')->with('status', 'Progress saved as draft.');
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

        session()->forget('creating_subsequent_company');

        return redirect('/')->with('status', 'Company setup discarded.');
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
