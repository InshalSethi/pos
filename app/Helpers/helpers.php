<?php

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;

if (!function_exists('format_price')) {
    /**
     * Format a price according to the active system currency.
     *
     * @param float|int $amount Base amount in USD
     * @param int $decimals Number of decimal places
     * @return string Converted and formatted price (e.g. "Rs. 278.50")
     */
    function format_price($amount, $decimals = 2)
    {
        $activeCurrencyId = Cache::get('system_currency_id');
        
        $currency = null;
        if ($activeCurrencyId) {
            $currency = Cache::remember("currency_{$activeCurrencyId}", 3600, function () use ($activeCurrencyId) {
                return Currency::find($activeCurrencyId);
            });
        }
        
        if (!$currency) {
            $currency = Cache::remember('currency_default_usd', 3600, function () {
                return Currency::where('code', 'USD')->first() ?? (object) [
                    'symbol' => '$',
                    'exchange_rate' => 1.0000
                ];
            });
        }
        
        $rate = floatval($currency->exchange_rate ?? 1.0000);
        $symbol = $currency->symbol ?? '$';
        
        $converted = floatval($amount) * $rate;
        
        return $symbol . ' ' . number_format($converted, $decimals, '.', ',');
    }
}

if (!function_exists('setting')) {
    /**
     * Get an application setting from cache.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function setting($key, $default = null)
    {
        return Cache::get($key, $default);
    }
}

