<template>
  <div class="max-w-5xl mx-auto py-6 sm:px-6 lg:px-8 space-y-6">
    <!-- Top Header & Breadcrumb -->
    <div>
      <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-zinc-400 mb-2">
        <router-link to="/banking/accounts" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Banking</router-link>
        <span>/</span>
        <router-link to="/banking/accounts" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Accounts</router-link>
        <span>/</span>
        <span class="text-slate-800 dark:text-zinc-200 font-semibold">{{ isEditMode ? 'Edit' : 'Create' }}</span>
      </div>
      <h1 class="text-2xl font-bold text-slate-900 dark:text-zinc-100 tracking-tight">
        {{ isEditMode ? 'Edit Bank Account' : 'Add Bank Account' }}
      </h1>
      <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
        Configure bank or credit card details and opening balance.
      </p>
    </div>

    <!-- Main Full Page Form Card -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl p-8 shadow-sm">
      <form @submit.prevent="saveBankAccount" class="space-y-8">
        
        <!-- SECTION 1: Type Selector & General Fields -->
        <div class="space-y-6">
          <!-- TYPE SELECTOR -->
          <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">TYPE *</label>
            <div class="inline-flex p-1 bg-slate-100 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl">
              <button
                type="button"
                @click="form.account_type = 'bank'"
                :class="form.account_type === 'bank' || form.account_type === 'checking' ? 'bg-indigo-600 text-white shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                class="px-6 py-2 text-xs rounded-lg transition-all cursor-pointer"
              >
                Bank
              </button>
              <button
                type="button"
                @click="form.account_type = 'credit_card'"
                :class="form.account_type === 'credit_card' ? 'bg-indigo-600 text-white shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                class="px-6 py-2 text-xs rounded-lg transition-all cursor-pointer"
              >
                Credit Card
              </button>
            </div>
            <p class="text-xs italic text-slate-400 dark:text-zinc-500 mt-2 font-normal">
              Use credit card type for negative opening balance. The number is essential to reconcile accounts correctly.
            </p>
          </div>

          <!-- GENERAL FIELDS GRID -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">NAME *</label>
              <input
                v-model="form.account_name"
                type="text"
                required
                placeholder="e.g. Meezan Bank / Corporate Card"
                class="w-full px-4 py-2.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-normal"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">NUMBER *</label>
              <input
                v-model="form.account_number"
                type="text"
                required
                placeholder="Enter Account Number"
                class="w-full px-4 py-2.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-normal"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">CURRENCY *</label>
              <CustomFloatingSelect
                v-model="form.currency"
                :options="currencyOptions"
                placeholder="Select Currency"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">OPENING BALANCE *</label>
              <div class="relative flex items-center">
                <span class="absolute left-4 text-xs font-semibold text-slate-400">
                  {{ getCurrencySymbol(form.currency) }}
                </span>
                <input
                  v-model.number="form.opening_balance"
                  type="number"
                  step="0.01"
                  required
                  placeholder="0.00"
                  class="w-full pl-10 pr-4 py-2.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-normal"
                />
              </div>
            </div>
          </div>

          <!-- DEFAULT ACCOUNT TOGGLE -->
          <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">DEFAULT ACCOUNT</label>
            <div class="inline-flex rounded-xl p-1 bg-slate-100 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800">
              <button
                type="button"
                @click="form.is_default = true"
                :class="form.is_default ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-800 dark:hover:text-zinc-200 font-medium'"
                class="px-5 py-1.5 text-xs rounded-lg transition-all cursor-pointer"
              >
                Yes
              </button>
              <button
                type="button"
                @click="form.is_default = false"
                :class="!form.is_default ? 'bg-rose-600 text-white font-semibold' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-800 dark:hover:text-zinc-200 font-medium'"
                class="px-5 py-1.5 text-xs rounded-lg transition-all cursor-pointer"
              >
                No
              </button>
            </div>
          </div>
        </div>

        <hr class="border-slate-100 dark:border-zinc-800/80" />

        <!-- SECTION 2: BANK METADATA -->
        <div class="space-y-6">
          <div>
            <h3 class="text-base font-semibold text-slate-900 dark:text-zinc-100">Bank</h3>
            <p class="text-xs text-slate-400 dark:text-zinc-500 mt-0.5">
              You may have multiple bank accounts in more than one bank. Recording information about your bank will make it easier to match transactions.
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">BANK NAME</label>
              <input
                v-model="form.bank_name"
                type="text"
                placeholder="Enter Bank Name (e.g. Meezan Bank)"
                class="w-full px-4 py-2.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-normal"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">BANK PHONE</label>
              <input
                v-model="form.bank_phone"
                type="text"
                placeholder="Enter Bank Phone"
                class="w-full px-4 py-2.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-normal"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">BANK ADDRESS</label>
            <textarea
              v-model="form.bank_address"
              rows="3"
              placeholder="Enter Bank Address"
              class="w-full px-4 py-2.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-normal resize-none"
            ></textarea>
          </div>
        </div>

        <!-- FOOTER ACTIONS -->
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-200 dark:border-zinc-800">
          <button
            type="button"
            @click="handleCancel"
            class="px-6 py-2.5 text-xs font-semibold rounded-xl border border-slate-200 dark:border-zinc-800 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="submitting"
            class="px-8 py-2.5 text-xs font-semibold rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white shadow-sm transition-all cursor-pointer"
          >
            {{ submitting ? 'Saving...' : 'Save' }}
          </button>
        </div>

      </form>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import { useCurrencyStore } from '@/stores/currency';
import CustomFloatingSelect from '../common/CustomFloatingSelect.vue';

export default {
  name: 'CreateBankAccount',
  components: {
    CustomFloatingSelect
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const { showToast } = useToast();

    const submitting = ref(false);
    const isEditMode = computed(() => Boolean(route.params.id));

    const currencyOptions = [
      { label: 'Pakistan Rupee (PKR)', value: 'PKR' },
      { label: 'US Dollar (USD)', value: 'USD' },
      { label: 'Euro (EUR)', value: 'EUR' },
      { label: 'British Pound (GBP)', value: 'GBP' },
      { label: 'UAE Dirham (AED)', value: 'AED' },
      { label: 'Saudi Riyal (SAR)', value: 'SAR' },
    ];

    const form = ref({
      account_name: '',
      account_number: '',
      account_type: 'bank',
      currency: 'PKR',
      opening_balance: 0.00,
      is_default: false,
      bank_name: '',
      bank_phone: '',
      bank_address: ''
    });

    const fetchAccountDetail = async (id) => {
      try {
        const res = await axios.get(`/api/bank-accounts/${id}`);
        const acc = res.data;
        form.value = {
          account_name: acc.account_name || '',
          account_number: acc.account_number || '',
          account_type: acc.account_type === 'credit_card' ? 'credit_card' : 'bank',
          currency: acc.currency || 'PKR',
          opening_balance: acc.opening_balance || 0.00,
          is_default: Boolean(acc.is_default),
          bank_name: acc.bank_name || '',
          bank_phone: acc.bank_phone || '',
          bank_address: acc.bank_address || ''
        };
      } catch (err) {
        showToast('Failed to load bank account details', 'error');
        router.push('/banking/accounts');
      }
    };

    const handleCancel = () => {
      router.push('/banking/accounts');
    };

    const saveBankAccount = async () => {
      submitting.value = true;
      try {
        const payload = {
          ...form.value,
          account_type: form.value.account_type === 'credit_card' ? 'credit_card' : 'checking',
          bank_name: form.value.bank_name || form.value.account_name
        };

        if (isEditMode.value) {
          await axios.put(`/api/bank-accounts/${route.params.id}`, payload);
          showToast('Bank account updated successfully');
        } else {
          await axios.post('/api/bank-accounts', payload);
          showToast('Bank account created successfully');
        }
        router.push('/banking/accounts');
      } catch (err) {
        const msg = err.response?.data?.message || 'Failed to save bank account';
        showToast(msg, 'error');
      } finally {
        submitting.value = false;
      }
    };

    const currencyStore = useCurrencyStore();

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

    onMounted(() => {
      currencyStore.fetchCurrencies();
      if (route.params.id) {
        fetchAccountDetail(route.params.id);
      }
    });

    return {
      form,
      submitting,
      isEditMode,
      currencyOptions,
      handleCancel,
      saveBankAccount,
      getCurrencySymbol
    };
  }
};
</script>
