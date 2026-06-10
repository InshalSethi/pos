<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class SetUserLocalizationContext
{
    /**
     * Intercept every authenticated web request and hydrate the runtime
     * localization context from the user's active company configuration:
     *
     *  - App::setLocale()          → Blade trans() / __()'s translation layer
     *  - date_default_timezone_set → PHP's native date/time functions
     *  - Config::set('app.*')      → config() helper in Blade & PHP
     *  - session('app_currency')   → legacy helper key
     *  - session('tenant_currency') → preferred Aura global key
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->currentCompany) {
                $company = $user->currentCompany;

                // 1. Language / Locale
                if (!empty($company->system_language)) {
                    App::setLocale($company->system_language);
                    Config::set('app.locale', $company->system_language);
                }

                // 2. Timezone
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

                // 3. Currency — seed both session keys so all views resolve correctly
                if (!empty($company->base_currency)) {
                    Config::set('app.currency', $company->base_currency);
                    session([
                        'app_currency'    => $company->base_currency, // legacy
                        'tenant_currency' => $company->base_currency, // preferred
                    ]);
                }
            }
        }

        return $next($request);
    }
}
