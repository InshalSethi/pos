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
    public function index(Request $request): View|RedirectResponse
    {
        abort_unless(auth()->check(), 302, redirect('/login'));

        $user = auth()->user();

        // ── CONTEXT A: Resume a specific draft ──────────────────────────
        if ($request->filled('continue_draft_id')) {
            $company = Company::where('id', $request->query('continue_draft_id'))
                ->where('user_id', $user->id)
                ->where('status', 'draft')
                ->firstOrFail();

            session(['creating_subsequent_company' => true]);

            return view('company-setup', [
                'company'     => $company,
                'currentStep' => $company->draft_step ?? 1,
            ]);
        }

        // ── CONTEXT B: Start a completely fresh setup ────────────────────
        if ($request->filled('start_fresh_flow')) {
            $company = Company::create([
                'user_id'    => $user->id,
                'company_name' => 'Untitled Draft Workspace',
                'company_email' => $user->email ?? '',
                'company_phone' => '',
                'registration_number' => '',
                'owner_role' => 'Owner/CEO',
                'team_size' => 'Just Me',
                'intended_tasks' => [],
                'business_type' => '',
                'business_scale' => 'Single Outlet',
                'country' => 'United States',
                'system_language' => 'en',
                'base_currency' => 'USD',
                'timezone_offset' => 'UTC',
                'fiscal_year_start' => date('Y-01-01'),
                'status'     => 'draft',
                'draft_step' => 1,
            ]);

            session(['creating_subsequent_company' => true]);

            return view('company-setup', [
                'company'     => $company,
                'currentStep' => 1,
            ]);
        }

        // ── CONTEXT D: First-time onboarding (No query parameters) ───────
        if (!$user->onboarding_completed) {
            $draft = Company::where('user_id', $user->id)->where('status', 'draft')->first();
            
            if ($draft) {
                return view('company-setup', [
                    'company'     => $draft,
                    'currentStep' => $draft->draft_step ?? 1,
                ]);
            }
            
            $company = Company::create([
                'user_id'    => $user->id,
                'company_name' => 'Untitled Draft Workspace',
                'company_email' => $user->email ?? '',
                'company_phone' => '',
                'registration_number' => '',
                'owner_role' => 'Owner/CEO',
                'team_size' => 'Just Me',
                'intended_tasks' => [],
                'business_type' => '',
                'business_scale' => 'Single Outlet',
                'country' => 'United States',
                'system_language' => 'en',
                'base_currency' => 'USD',
                'timezone_offset' => 'UTC',
                'fiscal_year_start' => date('Y-01-01'),
                'status'     => 'draft',
                'draft_step' => 1,
            ]);

            return view('company-setup', [
                'company'     => $company,
                'currentStep' => 1,
            ]);
        }

        // ── CONTEXT C: Fallback — block unparameterized direct access ────
        return redirect('/');
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
}
