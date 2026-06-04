import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { api } from '@/services/api';

export const useCurrencyStore = defineStore('currency', () => {
    // State
    const currencies = ref([]);
    const activeCurrencyId = ref(null);
    const loading = ref(false);

    // Getters
    const activeCurrency = computed(() => {
        if (!activeCurrencyId.value) return null;
        return currencies.value.find(c => c.id === activeCurrencyId.value) || null;
    });

    const symbol = computed(() => activeCurrency.value?.symbol ?? '$');
    const exchangeRate = computed(() => parseFloat(activeCurrency.value?.exchange_rate ?? 1));

    // Actions
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
     * Format a base-USD amount according to the currently active currency.
     * @param {number|string} baseAmount – the raw value stored in DB (USD base)
     * @param {number} decimals
     * @returns {string} e.g. "Rs. 27,850.00"
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
        symbol,
        exchangeRate,
        loading,
        fetchCurrencies,
        setActiveCurrency,
        formatPrice,
    };
});
