<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 space-y-6">
    <!-- Header with Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-zinc-100 tracking-tight">Bank Accounts</h1>
        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">Manage bank and credit card accounts, opening balances, and bank metadata.</p>
      </div>

      <div class="flex items-center space-x-3">
        <!-- Search Input -->
        <div class="relative w-64">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search accounts..."
            @input="debouncedSearch"
            class="w-full pl-9 pr-4 py-2 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl text-xs font-normal text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
          />
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>

        <!-- View Switcher Toggle (Grid vs List) -->
        <div class="inline-flex p-1 bg-slate-100 dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 shrink-0">
          <button
            type="button"
            @click="viewMode = 'grid'"
            :class="viewMode === 'grid' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-800 dark:hover:text-zinc-200'"
            class="p-1.5 rounded-lg transition-all cursor-pointer"
            title="Grid View"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
          </button>
          <button
            type="button"
            @click="viewMode = 'list'"
            :class="viewMode === 'list' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-800 dark:hover:text-zinc-200'"
            class="p-1.5 rounded-lg transition-all cursor-pointer"
            title="List View"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>

        <button
          @click="openAddPage"
          class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all cursor-pointer shrink-0"
        >
          + Add Account
        </button>
      </div>
    </div>

    <!-- Accounts Cards Summary Grid -->
    <div v-if="!loading && accounts.length > 0 && viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div
        v-for="acc in accounts"
        :key="acc.id"
        class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between relative overflow-hidden"
      >
        <!-- Card Top: Name & Badges -->
        <div>
          <div class="flex items-start justify-between gap-3 mb-2">
            <div class="flex items-center gap-2.5">
              <div
                :class="acc.account_type === 'credit_card' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400'"
                class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
              >
                <!-- Credit Card Icon -->
                <svg v-if="acc.account_type === 'credit_card'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <!-- Bank Icon -->
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 0V8m0 3h4m-4 0H7" />
                </svg>
              </div>

              <div>
                <h3 class="font-bold text-slate-900 dark:text-zinc-100 text-sm leading-snug flex items-center gap-2">
                  {{ acc.account_name }}
                  <span v-if="acc.is_default" class="px-2 py-0.5 text-[9px] font-bold tracking-wider uppercase rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                    Default
                  </span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-zinc-400">{{ acc.bank_name || acc.account_name }}</p>
              </div>
            </div>

            <!-- Type badge -->
            <span
              :class="acc.account_type === 'credit_card' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border-amber-200 dark:border-amber-800/40' : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800/40'"
              class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize border shrink-0"
            >
              {{ acc.account_type === 'credit_card' ? 'Credit Card' : 'Bank' }}
            </span>
          </div>

          <!-- Details -->
          <div class="mt-4 pt-3 border-t border-slate-100 dark:border-zinc-800/60 space-y-1.5 text-xs text-slate-600 dark:text-zinc-400">
            <div class="flex justify-between items-center">
              <span class="text-slate-400 dark:text-zinc-500">Account Number:</span>
              <span class="font-mono font-semibold text-slate-700 dark:text-zinc-300">{{ formatMaskedNumber(acc.account_number) }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-400 dark:text-zinc-500">Currency:</span>
              <span class="font-medium text-slate-700 dark:text-zinc-300">{{ acc.currency || 'PKR' }}</span>
            </div>
          </div>
        </div>

        <!-- Card Bottom: Balance & Actions -->
        <div class="mt-5 pt-3 border-t border-slate-100 dark:border-zinc-800/60 flex items-end justify-between">
          <div>
            <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-zinc-500 tracking-wider">Current Balance</span>
            <div class="text-lg font-extrabold text-slate-900 dark:text-zinc-100">
              {{ formatCurrency(acc.current_balance ?? acc.calculateBalance, acc.currency) }}
            </div>
          </div>

          <div class="flex items-center space-x-2">
            <button 
              type="button" 
              @click="toggleAccountStatus(acc)"
              :class="[
                'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none',
                acc.is_active ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-zinc-700'
              ]"
              role="switch"
              :aria-checked="Boolean(acc.is_active)"
              :title="acc.is_active ? 'Active (Click to disable)' : 'Inactive (Click to enable)'"
            >
              <span 
                :class="[
                  'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                  acc.is_active ? 'translate-x-5' : 'translate-x-0'
                ]"
              />
            </button>
            <button
              @click="openEditPage(acc)"
              title="Edit Account"
              class="p-2 text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 rounded-xl transition-colors cursor-pointer"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>
            <button
              @click="deleteAccount(acc)"
              title="Delete Account"
              class="p-2 text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-colors cursor-pointer"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Accounts List View Table -->
    <div v-else-if="!loading && accounts.length > 0 && viewMode === 'list'" class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-200 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-950/50 text-[11px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">
              <th class="py-3.5 px-5">Name</th>
              <th class="py-3.5 px-5 text-center">Type</th>
              <th class="py-3.5 px-5">Account Number</th>
              <th class="py-3.5 px-5 text-center">Currency</th>
              <th class="py-3.5 px-5 text-right">Current Balance</th>
              <th class="py-3.5 px-5 text-center">Status</th>
              <th class="py-3.5 px-5 text-center">Default</th>
              <th class="py-3.5 px-5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-xs">
            <tr
              v-for="acc in accounts"
              :key="acc.id"
              class="hover:bg-slate-50/80 dark:hover:bg-zinc-800/40 transition-colors"
            >
              <!-- Name & Subtitle -->
              <td class="py-4 px-5">
                <div class="font-bold text-slate-900 dark:text-zinc-100 flex items-center gap-2">
                  {{ acc.account_name }}
                  <span v-if="acc.is_default" class="px-2 py-0.5 text-[9px] font-bold tracking-wider uppercase rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                    Default
                  </span>
                </div>
                <div class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5">
                  {{ acc.bank_name || acc.account_name }}
                </div>
              </td>

              <!-- Type Badge -->
              <td class="py-4 px-5 text-center">
                <span
                  :class="acc.account_type === 'credit_card' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border-amber-200 dark:border-amber-800/40' : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800/40'"
                  class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize border shrink-0"
                >
                  {{ acc.account_type === 'credit_card' ? 'Credit Card' : 'Bank' }}
                </span>
              </td>

              <!-- Account Number -->
              <td class="py-4 px-5 font-mono font-semibold text-slate-700 dark:text-zinc-300">
                {{ formatMaskedNumber(acc.account_number) }}
              </td>

              <!-- Currency -->
              <td class="py-4 px-5 text-center font-medium text-slate-700 dark:text-zinc-300">
                {{ acc.currency || 'PKR' }}
              </td>

              <!-- Current Balance -->
              <td class="py-4 px-5 text-right font-extrabold text-slate-900 dark:text-zinc-100 text-sm">
                {{ formatCurrency(acc.current_balance ?? acc.calculateBalance, acc.currency) }}
              </td>

              <!-- Status Toggle Badge -->
              <td class="py-4 px-5 text-center">
                <button 
                  type="button" 
                  @click="toggleAccountStatus(acc)"
                  :class="[
                    'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none',
                    acc.is_active ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-zinc-700'
                  ]"
                  role="switch"
                  :aria-checked="Boolean(acc.is_active)"
                  :title="acc.is_active ? 'Active (Click to disable)' : 'Inactive (Click to enable)'"
                >
                  <span 
                    :class="[
                      'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                      acc.is_active ? 'translate-x-5' : 'translate-x-0'
                    ]"
                  />
                </button>
              </td>

              <!-- Default Badge -->
              <td class="py-4 px-5 text-center">
                <span v-if="acc.is_default" class="inline-flex px-2.5 py-0.5 text-[10px] font-bold tracking-wider uppercase rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
                  Default
                </span>
                <span v-else class="text-slate-400 dark:text-zinc-600 text-xs">-</span>
              </td>

              <!-- Actions -->
              <td class="py-4 px-5 text-right space-x-1">
                <button
                  @click="openEditPage(acc)"
                  title="Edit Account"
                  class="p-1.5 inline-flex items-center justify-center text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 rounded-lg transition-colors cursor-pointer"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <button
                  @click="deleteAccount(acc)"
                  title="Delete Account"
                  class="p-1.5 inline-flex items-center justify-center text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors cursor-pointer"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!loading && accounts.length === 0" class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl p-12 text-center shadow-sm">
      <div class="w-16 h-16 bg-slate-100 dark:bg-zinc-800 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-400 dark:text-zinc-500">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 0V8m0 3h4m-4 0H7" />
        </svg>
      </div>
      <h3 class="text-base font-bold text-slate-800 dark:text-zinc-200 mb-1">No Bank Accounts Found</h3>
      <p class="text-xs text-slate-500 dark:text-zinc-400 max-w-sm mx-auto mb-6">Get started by creating your first bank or credit card account to track financial transactions and balances.</p>
      <button
        @click="openAddPage"
        class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all cursor-pointer"
      >
        + Add Bank Account
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl p-12 text-center shadow-sm">
      <p class="text-xs text-slate-400 dark:text-zinc-500">Loading bank accounts...</p>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import { useCurrencyStore } from '@/stores/currency';

export default {
  name: 'BankAccounts',
  setup() {
    const router = useRouter();
    const { showToast } = useToast();
    const currencyStore = useCurrencyStore();

    const accounts = ref([]);
    const loading = ref(true);
    const searchQuery = ref('');
    const viewMode = ref('list'); // 'list' | 'grid'

    const companyCurrencySymbol = computed(() => {
      return currencyStore.symbol || currencyStore.tenantCurrencyCode || 'PKR';
    });

    const getCurrencySymbol = (code) => {
      if (!code) return companyCurrencySymbol.value;
      const upper = String(code).trim().toUpperCase();
      const map = {
        PKR: companyCurrencySymbol.value,
        USD: '$',
        EUR: '€',
        GBP: '£',
        AED: 'AED',
        SAR: 'SAR',
        CAD: 'CA$',
        AUD: 'A$',
        INR: '₹',
      };
      return map[upper] || upper || companyCurrencySymbol.value;
    };

    let searchTimeout;
    const debouncedSearch = () => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        fetchAccounts();
      }, 300);
    };

    const fetchAccounts = async () => {
      loading.value = true;
      try {
        const params = {};
        if (searchQuery.value) params.search = searchQuery.value;
        const res = await axios.get('/api/bank-accounts', { params });
        accounts.value = Array.isArray(res.data) ? res.data : (res.data?.data || []);
      } catch (err) {
        showToast('Failed to load bank accounts', 'error');
      } finally {
        loading.value = false;
      }
    };

    const toggleAccountStatus = async (acc) => {
      const newStatus = !acc.is_active;
      acc.is_active = newStatus;
      try {
        await axios.put(`/api/bank-accounts/${acc.id}`, { is_active: newStatus });
        showToast(`Account set to ${newStatus ? 'Active' : 'Inactive'}`);
      } catch (err) {
        acc.is_active = !newStatus;
        const msg = err.response?.data?.message || 'Failed to update account status';
        showToast(msg, 'error');
      }
    };

    const openAddPage = () => {
      router.push('/banking/accounts/create');
    };

    const openEditPage = (acc) => {
      router.push(`/banking/accounts/${acc.id}/edit`);
    };

    const deleteAccount = async (acc) => {
      if (!confirm(`Are you sure you want to delete ${acc.account_name}?`)) return;
      try {
        await axios.delete(`/api/bank-accounts/${acc.id}`);
        showToast('Bank account deleted successfully');
        fetchAccounts();
      } catch (err) {
        const msg = err.response?.data?.message || 'Failed to delete bank account';
        showToast(msg, 'error');
      }
    };

    const formatCurrency = (amount, currencySymbol = 'PKR') => {
      const val = Number(amount || 0);
      const symbol = getCurrencySymbol(currencySymbol);
      return `${symbol} ${val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    };

    const formatMaskedNumber = (num) => {
      if (!num) return '-';
      const str = String(num);
      return str.length > 4 ? `****${str.slice(-4)}` : str;
    };

    onMounted(() => {
      fetchAccounts();
    });

    return {
      accounts,
      loading,
      searchQuery,
      viewMode,
      debouncedSearch,
      fetchAccounts,
      toggleAccountStatus,
      openAddPage,
      openEditPage,
      deleteAccount,
      formatCurrency,
      getCurrencySymbol,
      formatMaskedNumber
    };
  }
};
</script>
