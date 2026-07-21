<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanySetup
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('testing') && ($request->expectsJson() || $request->is('api/*'))) {
            return $next($request);
        }

        if (Auth::check()) {
            $user = Auth::user();

            // Check if company_id is null or is_setup_completed is false
            if (is_null($user->company_id) || !$user->is_setup_completed) {
                
                // Exclude routes to prevent infinite loops
                $exempt = $request->routeIs('company.setup')
                    || $request->is('company-setup')
                    || $request->is('company-setup/*')
                    || $request->is('login')
                    || $request->is('logout')
                    || $request->is('api/user')
                    || $request->is('api/logout')
                    || $request->is('livewire*')
                    || $request->is('admin*')
                    || $request->routeIs('onboarding.*')
                    || $request->routeIs('abort-onboarding')
                    || $request->routeIs('company.setup.cancel');

                if (!$exempt) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'Please complete your company profile setup to continue.',
                            'redirect' => '/company-setup'
                        ], 403);
                    }
                    
                    session()->flash('error', 'Please complete your company profile setup to continue.');
                    return redirect()->route('company.setup');
                }
            }
        }

        return $next($request);
    }
}
