<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class CurrencyController extends Controller
{
    /**
     * Get all active currencies.
     */
    public function index(): JsonResponse
    {
        $currencies = Currency::active()->orderBy('name')->get();

        // Also return the currently selected currency id from cache
        $activeCurrencyId = Cache::get('system_currency_id', null);
        if (!$activeCurrencyId) {
            $usd = Currency::where('code', 'USD')->first();
            $activeCurrencyId = $usd?->id;
        }

        return response()->json([
            'currencies'         => $currencies,
            'active_currency_id' => $activeCurrencyId,
        ]);
    }

    /**
     * Update the active system currency.
     */
    public function setActive(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'currency_id' => 'required|exists:currencies,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $currency = Currency::findOrFail($request->currency_id);

        // Store in cache indefinitely (cleared only when changed)
        Cache::forever('system_currency_id', $currency->id);

        return response()->json([
            'message'  => 'System currency updated successfully',
            'currency' => $currency,
        ]);
    }

    /**
     * Get the currently active currency.
     */
    public function getActive(): JsonResponse
    {
        $activeCurrencyId = Cache::get('system_currency_id');
        $currency = $activeCurrencyId
            ? Currency::find($activeCurrencyId)
            : Currency::where('code', 'USD')->first();

        if (!$currency) {
            $currency = Currency::active()->first();
        }

        return response()->json($currency);
    }
}
