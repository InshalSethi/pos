import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { api } from '@/services/api';

/**
 * ISO 4217 currency code → display symbol map.
 * Used to render the correct symbol instantly from the company_context
 * payload without waiting for the /api/currencies round-trip.
 */
const CURRENCY_SYMBOL_MAP = {
    PKR: '₨',  USD: '$',  GBP: '£',  EUR: '€',  AED: 'د.إ',
    SAR: 'ر.س', CAD: 'CA$', AUD: 'A$', INR: '₹',  CNY: '¥',
    TRY: '₺',  KWD: 'د.ك', QAR: 'ر.ق', OMR: 'ر.ع.', BHD: '.د.ب',
    JPY: '¥',  SGD: 'S$',  NZD: 'NZ$', CHF: 'Fr',  MYR: 'RM',
};

export const useCurrencyStore = defineStore('currency', () => {
    // State
    const currencies      = ref([]);
    const activeCurrencyId = ref(null);
    const loading          = ref(false);

    // Tenant currency code seeded directly from company_context (e.g. 'PKR', 'USD')
    const tenantCurrencyCode = ref(localStorage.getItem('tenant_currency_code') || null);

    // ─── Getters ────────────────────────────────────────────────────────────────

    const activeCurrency = computed(() => {
        if (!activeCurrencyId.value) return null;
        return currencies.value.find(c => c.id === activeCurrencyId.value) || null;
    });

    /**
     * Currency symbol — resolved in priority order:
     *  1. Active currency object from /api/currencies (most accurate)
     *  2. Tenant code from company_context seed (instant, no API wait)
     *  3. Hard-coded '$' fallback
     */
    const symbol = computed(() => {
        if (activeCurrency.value?.symbol) return activeCurrency.value.symbol;
        if (tenantCurrencyCode.value)     return CURRENCY_SYMBOL_MAP[tenantCurrencyCode.value] ?? tenantCurrencyCode.value;
        return '$';
    });

    /** The active ISO 4217 code (e.g. 'PKR') */
    const currencyCode = computed(() => {
        if (activeCurrency.value?.code) return activeCurrency.value.code;
        return tenantCurrencyCode.value ?? 'USD';
    });

    const exchangeRate = computed(() => parseFloat(activeCurrency.value?.exchange_rate ?? 1));

    // ─── Actions ────────────────────────────────────────────────────────────────

    /**
     * Seed the currency context directly from the company_context API response
     * field so the correct symbol renders before /api/currencies resolves.
     *
     * @param {string} code  ISO 4217 code, e.g. 'PKR'
     */
    function seedFromCompany(code) {
        if (!code) return;
        tenantCurrencyCode.value = code;
        localStorage.setItem('tenant_currency_code', code);
    }

    async function fetchCurrencies() {
        loading.value = true;
        try {
            const response = await api.get('/currencies');
            currencies.value = response.data.currencies;
            activeCurrencyId.value = response.data.active_currency_id;

            // Persist the selection locally so it survives page refreshes
            if (activeCurrencyId.value) {
                localStorage.setItem('pos_currency_id', String(activeCurrencyId.value));
            }

            // Sync tenant code from resolved currency object if not already seeded
            if (activeCurrency.value?.code && !tenantCurrencyCode.value) {
                seedFromCompany(activeCurrency.value.code);
            }
        } catch (error) {
            console.error('Failed to fetch currencies:', error);

            // Fallback: restore from local storage if API fails
            const stored = localStorage.getItem('pos_currency_id');
            if (stored) activeCurrencyId.value = parseInt(stored, 10);
        } finally {
            loading.value = false;
        }
    }

    async function setActiveCurrency(currencyId) {
        try {
            const response = await api.post('/currencies/set-active', {
                currency_id: currencyId,
            });
            activeCurrencyId.value = currencyId;
            localStorage.setItem('pos_currency_id', String(currencyId));
            return response.data;
        } catch (error) {
            console.error('Failed to set active currency:', error);
            throw error;
        }
    }

    /**
     * Format an amount using the active currency symbol.
     * @param {number|string} baseAmount
     * @param {number} decimals
     * @returns {string}  e.g. "₨ 27,850.00"
     */
    function formatPrice(baseAmount, decimals = 2) {
        const amount = parseFloat(baseAmount) || 0;
        return `${symbol.value} ${amount.toLocaleString('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        })}`;
    }

    // Initialise from local storage immediately (before the API responds)
    const stored = localStorage.getItem('pos_currency_id');
    if (stored) activeCurrencyId.value = parseInt(stored, 10);

    return {
        currencies,
        activeCurrencyId,
        activeCurrency,
        tenantCurrencyCode,
        symbol,
        currencyCode,
        exchangeRate,
        loading,
        fetchCurrencies,
        setActiveCurrency,
        seedFromCompany,
        formatPrice,
    };
});
