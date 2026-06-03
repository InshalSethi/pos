<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class TimezoneController extends Controller
{
    /**
     * Get the currently configured system timezone.
     */
    public function getTimezone(): JsonResponse
    {
        $timezone = setting('system_timezone', 'Asia/Karachi');
        return response()->json([
            'timezone' => $timezone
        ]);
    }

    /**
     * Set the system timezone.
     */
    public function setTimezone(Request $request): JsonResponse
    {
        $validTimezones = \DateTimeZone::listIdentifiers();

        $validator = Validator::make($request->all(), [
            'timezone' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($validTimezones) {
                    if (!in_array($value, $validTimezones)) {
                        $fail('The selected timezone is invalid.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $timezone = $request->input('timezone');

        // Store the selected timezone in the cache indefinitely
        Cache::forever('system_timezone', $timezone);

        // Also update configuration for the current request context
        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);

        return response()->json([
            'message' => 'System timezone updated successfully',
            'timezone' => $timezone
        ]);
    }
}
