<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingIsCompleted
{
    /**
     * Handle an incoming request.
     *
     * Priority order:
     *  1. Unauthenticated  → pass through (login route handles them).
     *  2. Session flag 'creating_subsequent_company' is present
     *     → already-onboarded user explicitly opened the wizard to add a
     *       second tenant, grant immediate passage.
     *  3. Onboarding NOT completed + not on an exempt path
     *     → force redirect to /company-setup.
     *  4. Onboarding completed + on /company-setup WITHOUT the session flag
     *     → redirect back to the dashboard (/) to prevent accidental re-setup.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        // ── CASE 2: Explicit subsequent-company creation bypass ───────────────
        // The /initiate-new-company route stamps this flag before redirecting
        // to /company-setup, so it is always present during wizard navigation.
        if (session()->has('creating_subsequent_company')) {
            return $next($request);
        }

        $user = Auth::user();

        // ── CASE 3: First-time onboarding still incomplete ────────────────────
        if (!$user->onboarding_completed) {
            $exempt = $request->routeIs('company.setup')
                   || $request->routeIs('onboarding.wizard')
                   || $request->routeIs('abort-onboarding')
                   || $request->is('livewire*')
                   || $request->is('login');

            if (!$exempt) {
                return redirect()->route('company.setup');
            }

            return $next($request);
        }

        // ── CASE 4: Fully onboarded user hitting /company-setup without flag ──
        // Guard against accidental visits — send them back to the dashboard,
        // EXCEPT if they are explicitly passing a parameter to start/resume.
        if ($request->is('company-setup*')) {
            if ($request->filled('continue_draft_id') || $request->filled('start_fresh_flow')) {
                return $next($request);
            }
            return redirect('/');
        }

        return $next($request);
    }
}
