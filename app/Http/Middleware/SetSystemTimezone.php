<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSystemTimezone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $rawTimezone = setting('system_timezone', 'Asia/Karachi');

        // Map short-codes to valid IANA identifiers
        $timezoneMap = [
            'UTC' => 'UTC',
            'EST' => 'America/New_York',
            'PST' => 'America/Los_Angeles',
            'GMT' => 'Europe/London',
            'PKT' => 'Asia/Karachi',
        ];
        $ianaTimezone = $timezoneMap[$rawTimezone] ?? $rawTimezone;

        // Ensure it is valid before applying
        if (in_array($ianaTimezone, \DateTimeZone::listIdentifiers())) {
            config(['app.timezone' => $ianaTimezone]);
            date_default_timezone_set($ianaTimezone);
        } else {
            // Fallback to UTC if somehow the setting is completely invalid
            config(['app.timezone' => 'UTC']);
            date_default_timezone_set('UTC');
        }

        return $next($request);
    }
}
