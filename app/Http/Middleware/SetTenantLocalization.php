<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

/**
 * SetTenantLocalization
 *
 * Canonical middleware for per-tenant runtime localization.
 * Reads the active Company's regional configuration and pushes it into:
 *
 *  - Laravel's App locale (setLocale)
 *  - PHP's native timezone (date_default_timezone_set)
 *  - Laravel Config layer: config('app.timezone'), config('app.currency')
 *  - Session: session('tenant_currency'), session('app_currency')
 *
 * This middleware is registered in the `web` stack via bootstrap/app.php.
 * It runs on every authenticated page load, ensuring the locale/timezone/currency
 * context is always in sync with the user's active company settings.
 */
class SetTenantLocalization
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->currentCompany) {
                $company = $user->currentCompany;

                // 1. Enforce Selected App Language Runtime
                if (!empty($company->system_language)) {
                    App::setLocale($company->system_language);
                    Config::set('app.locale', $company->system_language);
                }

                // 2. Enforce Selected Timezone Anchor Lifecycle
                if (!empty($company->timezone_offset)) {
                    // Map short-codes from onboarding wizard to IANA timezone strings
                    $timezoneMap = [
                        'UTC' => 'UTC',
                        'EST' => 'America/New_York',
                        'PST' => 'America/Los_Angeles',
                        'GMT' => 'Europe/London',
                        'PKT' => 'Asia/Karachi',
                    ];
                    $ianaTimezone = $timezoneMap[$company->timezone_offset] ?? $company->timezone_offset;

                    // Ensure it is a valid identifier before trying to set it
                    if (in_array($ianaTimezone, \DateTimeZone::listIdentifiers())) {
                        date_default_timezone_set($ianaTimezone);
                        Config::set('app.timezone', $ianaTimezone);
                    }
                }

                // 3. Enforce Selected Ledger Currency in Session state for POS/ERP helpers
                if (!empty($company->base_currency)) {
                    Config::set('app.currency', $company->base_currency);
                    session([
                        'tenant_currency' => $company->base_currency, // preferred Aura key
                        'app_currency'    => $company->base_currency, // legacy key
                    ]);
                }
            }
        }

        return $next($request);
    }
}
