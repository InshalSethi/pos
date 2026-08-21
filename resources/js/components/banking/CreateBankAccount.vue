<template>
  <div class="space-y-6">
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
      <form @submit.prevent="saveBankAccount" autocomplete="off" data-lpignore="true" class="space-y-8">
        
        <!-- SECTION 1: Type Selector & General Fields -->
        <div class="space-y-6">
          <!-- TYPE SELECTOR -->
          <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">TYPE *</label>
            <div class="inline-flex p-1 bg-slate-100 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl">
              <button
                type="button"
                @click="form.account_type = 'bank'"
                :class="form.account_type === 'bank' || form.account_type === 'checking' ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                class="px-6 py-2 text-xs rounded-lg transition-all cursor-pointer"
              >
                Bank
              </button>
              <button
                type="button"
                @click="form.account_type = 'credit_card'"
                :class="form.account_type === 'credit_card' ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
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
              <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                {{ form.account_type === 'credit_card' ? 'CARD HOLDER NAME *' : 'ACCOUNT NAME *' }}
              </label>
              <input
                v-model="form.account_name"
                type="text"
                required
                :placeholder="form.account_type === 'credit_card' ? 'Enter Card Holder Name' : 'Enter Account Name'"
                class="w-full px-4 py-2.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-normal"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                {{ form.account_type === 'credit_card' ? 'CARD NUMBER *' : 'BANK ACCOUNT NUMBER *' }}
              </label>
              <input
                v-model="form.account_number"
                type="text"
                required
                :placeholder="form.account_type === 'credit_card' ? 'Enter Card Number' : 'Enter Bank Account Number'"
                class="w-full px-4 py-2.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-normal"
              />
            </div>

            <!-- Credit Card Specific Fields: Expiry Date & CVV -->
            <template v-if="form.account_type === 'credit_card'">
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                  EXPIRY DATE (MONTH/YEAR) *
                </label>
                <input
                  :value="form.expiry_date"
                  @input="handleExpiryInput"
                  @keypress="onlyDigits"
                  type="text"
                  name="card_exp_date"
                  autocomplete="off"
                  data-lpignore="true"
                  data-form-type="other"
                  maxlength="7"
                  placeholder="MM/YYYY (e.g. 07/2026)"
                  class="w-full px-4 py-2.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-normal"
                />
                <p v-if="expiryError" class="text-xs text-rose-500 font-semibold mt-1">{{ expiryError }}</p>
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                  CVV *
                </label>
                <input
                  :value="form.cvv"
                  @input="handleCvvInput"
                  @keypress="onlyDigits"
                  type="text"
                  name="card_security_code"
                  autocomplete="off"
                  data-lpignore="true"
                  data-form-type="other"
                  maxlength="3"
                  placeholder="e.g. 123"
                  class="w-full px-4 py-2.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-normal"
                />
                <p v-if="cvvError" class="text-xs text-rose-500 font-semibold mt-1">{{ cvvError }}</p>
              </div>
            </template>

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

          <!-- DEFAULT ACCOUNT TOGGLE & ACCOUNT STATUS TOGGLE -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

            <div>
              <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">ACCOUNT STATUS</label>
              <div class="flex items-center gap-3 pt-1">
                <button 
                  type="button" 
                  @click="form.is_active = !form.is_active"
                  :class="[
                    'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none',
                    form.is_active ? 'bg-slate-900 dark:bg-emerald-500' : 'bg-slate-300 dark:bg-zinc-700'
                  ]"
                  role="switch"
                  :aria-checked="form.is_active"
                >
                  <span 
                    :class="[
                      'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                      form.is_active ? 'translate-x-5' : 'translate-x-0'
                    ]"
                  />
                </button>
                <span class="text-xs font-semibold text-slate-700 dark:text-zinc-300">
                  {{ form.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <hr class="border-slate-100 dark:border-zinc-800/80" />

        <!-- SECTION 2: BANK / CARD METADATA -->
        <div class="space-y-6">
          <div>
            <h3 class="text-base font-semibold text-slate-900 dark:text-zinc-100 uppercase tracking-wider">
              {{ form.account_type === 'credit_card' ? 'CARD DETAILS' : 'BANK' }}
            </h3>
            <p class="text-xs text-slate-400 dark:text-zinc-500 mt-0.5">
              {{ form.account_type === 'credit_card' 
                ? 'Recording information about your card issuer and bank will make it easier to match transactions.'
                : 'You may have multiple bank accounts in more than one bank. Recording information about your bank will make it easier to match transactions.' }}
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                {{ form.account_type === 'credit_card' ? 'CARD TITLE / NAME *' : 'BANK NAME *' }}
              </label>
              <input
                v-model="form.bank_name"
                type="text"
                required
                :placeholder="form.account_type === 'credit_card' ? 'Enter Card Title / Name' : 'Enter Bank Name (e.g. Meezan Bank)'"
                class="w-full px-4 py-2.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-normal"
              />
            </div>

            <div>
              <CustomPhoneInput
                :label="form.account_type === 'credit_card' ? 'CARD BANK PHONE' : 'BANK PHONE'"
                v-model="form.bank_phone"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
              {{ form.account_type === 'credit_card' ? 'CARD BANK ADDRESS' : 'BANK ADDRESS' }}
            </label>
            <textarea
              v-model="form.bank_address"
              rows="3"
              :placeholder="form.account_type === 'credit_card' ? 'Enter Card Bank Address' : 'Enter Bank Address'"
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
            class="px-8 py-2.5 text-xs font-semibold rounded-xl bg-slate-900 hover:bg-black text-white dark:bg-zinc-100 dark:hover:bg-white dark:text-zinc-900 disabled:opacity-50 shadow-sm transition-all cursor-pointer"
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
import CustomPhoneInput from '../common/CustomPhoneInput.vue';

export default {
  name: 'CreateBankAccount',
  components: {
    CustomFloatingSelect,
    CustomPhoneInput
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
      expiry_date: '',
      cvv: '',
      account_type: 'bank',
      currency: 'PKR',
      opening_balance: 0.00,
      is_active: true,
      is_default: false,
      bank_name: '',
      bank_phone: '',
      bank_address: '',
      chart_account_id: null
    });

    const fetchAccountDetail = async (id) => {
      try {
        const res = await axios.get(`/api/bank-accounts/${id}`);
        const acc = res.data;
        form.value = {
          account_name: acc.account_name || '',
          account_number: acc.account_number || '',
          expiry_date: acc.expiry_date || '',
          cvv: acc.cvv || '',
          account_type: acc.account_type === 'credit_card' ? 'credit_card' : 'bank',
          currency: acc.currency || 'PKR',
          opening_balance: acc.opening_balance || 0.00,
          is_active: acc.is_active !== undefined ? Boolean(acc.is_active) : true,
          is_default: Boolean(acc.is_default),
          bank_name: acc.bank_name || '',
          bank_phone: acc.bank_phone || '',
          bank_address: acc.bank_address || '',
          chart_account_id: acc.chart_account_id || null
        };
      } catch (err) {
        showToast('Failed to load bank account details', 'error');
        router.push('/banking/accounts');
      }
    };

    const expiryError = ref('');
    const cvvError = ref('');

    const handleCancel = () => {
      router.push('/banking/accounts');
    };

    const onlyDigits = (e) => {
      const char = String.fromCharCode(e.which || e.keyCode);
      if (!/[0-9]/.test(char)) {
        e.preventDefault();
      }
    };

    const handleCvvInput = (e) => {
      let val = e.target.value.replace(/\D/g, '');
      if (val.length > 3) {
        val = val.slice(0, 3);
      }
      form.value.cvv = val;
      if (val.length > 0 && val.length < 3) {
        cvvError.value = 'CVV requires 3 digits';
      } else {
        cvvError.value = '';
      }
    };

    const handleExpiryInput = (e) => {
      const inputType = e.inputType;
      let raw = e.target.value;

      if (inputType === 'deleteContentBackward') {
        form.value.expiry_date = raw;
        expiryError.value = '';
        return;
      }

      let digits = raw.replace(/\D/g, '');

      if (!digits) {
        form.value.expiry_date = '';
        expiryError.value = '';
        return;
      }

      if (digits.length === 1) {
        const firstNum = parseInt(digits[0], 10);
        if (firstNum >= 2) {
          form.value.expiry_date = `0${firstNum}/`;
          expiryError.value = '';
          return;
        }
        form.value.expiry_date = digits;
        expiryError.value = '';
        return;
      }

      if (digits.length > 6) {
        digits = digits.slice(0, 6);
      }

      let mm = digits.slice(0, 2);
      let mmNum = parseInt(mm, 10);
      if (mmNum > 12) mm = '12';
      if (mm === '00') mm = '01';

      let yy = digits.slice(2);

      let formatted = '';
      if (digits.length >= 2) {
        if (yy.length > 0) {
          formatted = `${mm}/${yy}`;
        } else {
          formatted = `${mm}/`;
        }
      } else {
        formatted = digits;
      }

      form.value.expiry_date = formatted;

      if (formatted.length === 7) {
        const [mStr, yStr] = formatted.split('/');
        const expM = parseInt(mStr, 10);
        const expY = parseInt(yStr, 10);
        const now = new Date();
        const curY = now.getFullYear();
        const curM = now.getMonth() + 1;

        if (expY < curY || (expY === curY && expM < curM)) {
          expiryError.value = 'This card is expired';
          showToast('This card is expired', 'error');
        } else {
          expiryError.value = '';
        }
      } else {
        expiryError.value = '';
      }
    };

    const saveBankAccount = async () => {
      if (!form.value.bank_name || !form.value.bank_name.trim()) {
        const titleLabel = form.value.account_type === 'credit_card' ? 'Card Title / Name' : 'Bank Name';
        showToast(`${titleLabel} is required`, 'error');
        return;
      }

      if (form.value.account_type === 'credit_card') {
        const expVal = (form.value.expiry_date || '').trim();
        if (!expVal || expVal.length < 7) {
          expiryError.value = 'Please enter a valid Expiry Date (MM/YYYY)';
          showToast('Please enter a valid Expiry Date (e.g. 07/2026)', 'error');
          return;
        }

        const [mStr, yStr] = expVal.split('/');
        const expM = parseInt(mStr, 10);
        const expY = parseInt(yStr, 10);
        const now = new Date();
        const curY = now.getFullYear();
        const curM = now.getMonth() + 1;

        if (expY < curY || (expY === curY && expM < curM)) {
          expiryError.value = 'This card is expired';
          showToast('This card is expired', 'error');
          return;
        }

        const cvvVal = (form.value.cvv || '').trim();
        if (!cvvVal || cvvVal.length !== 3) {
          cvvError.value = 'CVV requires 3 digits';
          showToast('CVV requires 3 digits', 'error');
          return;
        }
      }

      submitting.value = true;
      try {
        const payload = {
          ...form.value,
          account_type: form.value.account_type === 'credit_card' ? 'credit_card' : 'checking',
          bank_name: form.value.bank_name.trim()
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

    onMounted(async () => {
      await currencyStore.fetchCurrencies();
      if (route.params.id) {
        fetchAccountDetail(route.params.id);
      } else {
        if (currencyStore.tenantCurrencyCode) {
          form.value.currency = currencyStore.tenantCurrencyCode;
        }
      }
    });

    return {
      form,
      submitting,
      isEditMode,
      currencyOptions,
      expiryError,
      cvvError,
      onlyDigits,
      handleCancel,
      saveBankAccount,
      getCurrencySymbol,
      handleCvvInput,
      handleExpiryInput
    };
  }
};
</script>
