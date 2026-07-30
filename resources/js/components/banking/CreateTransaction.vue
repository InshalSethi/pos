<template>
  <div class="max-w-5xl mx-auto py-6 sm:px-6 lg:px-8 space-y-6">
    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-zinc-400 mb-2">
          <router-link to="/banking/transactions" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Banking</router-link>
          <span>/</span>
          <router-link to="/banking/transactions" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Transactions</router-link>
          <span>/</span>
          <span class="text-slate-800 dark:text-zinc-200 font-semibold">{{ isIncome ? 'New Income' : 'New Expense' }}</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-zinc-100 tracking-tight">
          {{ isIncome ? 'New Income' : 'New Expense' }}
        </h1>
        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
          Enter transaction details to update your bank & cash ledger balances.
        </p>
      </div>

      <div>
        <button
          type="button"
          @click="handleCancel"
          class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-zinc-800 text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer"
        >
          Back to Transactions
        </button>
      </div>
    </div>

    <form @submit.prevent="saveTransaction" class="space-y-6">
      <!-- CARD 1: GENERAL -->
      <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm space-y-6">
        <div>
          <h3 class="text-base font-bold text-slate-900 dark:text-zinc-100">General</h3>
          <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
            Here you can enter the general information of transaction such as date, amount, account, description, etc.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Date -->
          <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Date *</label>
            <input
              v-model="form.transaction_date"
              type="date"
              required
              class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-normal"
            />
          </div>

          <!-- Payment Method -->
          <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Payment Method *</label>
            <CustomFloatingSelect
              v-model="form.payment_method"
              :options="paymentMethodOptions"
              placeholder="Select Payment Method"
            />
          </div>

          <!-- Account -->
          <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Account *</label>
            <select
              v-model="form.bank_account_id"
              required
              class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-normal"
            >
              <option value="" disabled>- Select Account -</option>
              <option v-for="acc in bankAccounts" :key="acc.id" :value="acc.id">
                {{ acc.account_name }} ({{ getCurrencySymbol(acc.currency) }})
              </option>
            </select>
          </div>

          <!-- Amount -->
          <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Amount *</label>
            <div class="relative flex items-center">
              <span class="absolute left-3.5 text-xs font-semibold text-slate-400">
                {{ selectedAccountCurrencySymbol }}
              </span>
              <input
                v-model.number="form.amount"
                type="number"
                step="0.01"
                min="0.01"
                required
                placeholder="0.00"
                class="w-full pl-10 pr-4 py-2.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-normal"
              />
            </div>
          </div>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Description</label>
          <textarea
            v-model="form.description"
            rows="3"
            placeholder="Enter Description / Notes"
            class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-normal resize-none"
          ></textarea>
        </div>
      </div>

      <!-- CARD 2: ASSIGN -->
      <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm space-y-6">
        <div>
          <h3 class="text-base font-bold text-slate-900 dark:text-zinc-100">Assign</h3>
          <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
            Select a category and contact to make your reports more detailed.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Category -->
          <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Category *</label>
            <select
              v-model="form.category_id"
              class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-normal"
            >
              <option value="">- Select Category -</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.account_code ? cat.account_code + ' - ' : '' }}{{ cat.account_name }}
              </option>
            </select>
          </div>

          <!-- Contact Selection (Customer for Income, Vendor for Expense) -->
          <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
              {{ isIncome ? 'Customer' : 'Vendor' }}
            </label>
            <select
              v-model="form.contact_id"
              class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-normal"
            >
              <option value="">{{ isIncome ? '- Select Customer -' : '- Select Vendor -' }}</option>
              <option v-for="contact in contacts" :key="contact.id" :value="contact.id">
                {{ contact.name || contact.company_name || (contact.first_name ? contact.first_name + ' ' + (contact.last_name || '') : 'Contact #' + contact.id) }}
              </option>
            </select>
          </div>

          <!-- Tax (Optional) -->
          <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Tax</label>
            <select
              v-model="form.tax_id"
              class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-normal"
            >
              <option value="">- Select Tax -</option>
              <option v-for="tax in taxes" :key="tax.id" :value="tax.id">
                {{ tax.name }} ({{ tax.rate }}%)
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- CARD 3: OTHER & ATTACHMENT -->
      <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm space-y-6">
        <div>
          <h3 class="text-base font-bold text-slate-900 dark:text-zinc-100">Other</h3>
          <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
            Enter a number and reference to keep the transaction linked to your records.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Number -->
          <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Number *</label>
            <input
              v-model="form.number"
              type="text"
              required
              placeholder="e.g. TRA-00001"
              class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-normal"
            />
          </div>

          <!-- Reference -->
          <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Reference</label>
            <input
              v-model="form.reference"
              type="text"
              placeholder="Enter Reference"
              class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-normal"
            />
          </div>
        </div>

        <!-- File Attachment Dropzone -->
        <div>
          <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Attachment</label>
          <div
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleFileDrop"
            :class="isDragging ? 'border-indigo-500 bg-indigo-50/20 dark:bg-indigo-950/20' : 'border-slate-200 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-950/50'"
            class="border-2 border-dashed rounded-2xl p-6 text-center transition-all cursor-pointer"
            @click="$refs.fileInput.click()"
          >
            <input
              ref="fileInput"
              type="file"
              class="hidden"
              @change="handleFileSelect"
            />
            <div class="w-10 h-10 bg-slate-100 dark:bg-zinc-800 text-slate-400 dark:text-zinc-500 rounded-xl flex items-center justify-center mx-auto mb-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
              </svg>
            </div>
            <p v-if="selectedFileName" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">
              Selected: {{ selectedFileName }}
            </p>
            <template v-else>
              <p class="text-xs font-medium text-slate-700 dark:text-zinc-300">
                Drop files here to upload or <span class="text-indigo-600 dark:text-indigo-400 font-semibold underline">browse</span>
              </p>
              <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-1">Supports PDF, PNG, JPG up to 10MB</p>
            </template>
          </div>
        </div>
      </div>

      <!-- FOOTER ACTIONS -->
      <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-zinc-800">
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
          :class="isIncome ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-slate-800 hover:bg-slate-900 dark:bg-indigo-600 dark:hover:bg-indigo-700'"
          class="px-8 py-2.5 text-xs font-semibold text-white rounded-xl shadow-sm transition-all cursor-pointer disabled:opacity-50"
        >
          {{ submitting ? 'Saving...' : 'Save' }}
        </button>
      </div>

    </form>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import CustomFloatingSelect from '../common/CustomFloatingSelect.vue';

export default {
  name: 'CreateTransaction',
  components: {
    CustomFloatingSelect
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const { showToast } = useToast();

    const submitting = ref(false);
    const isDragging = ref(false);
    const selectedFileName = ref('');

    const isIncome = computed(() => route.path.includes('create-income'));

    const bankAccounts = ref([]);
    const categories = ref([]);
    const contacts = ref([]);
    const taxes = ref([]);

    const paymentMethodOptions = [
      { label: 'Cash', value: 'Cash' },
      { label: 'Bank Transfer', value: 'Bank Transfer' },
      { label: 'Cheque', value: 'Cheque' },
      { label: 'Credit Card', value: 'Credit Card' },
    ];

    const todayStr = new Date().toISOString().slice(0, 10);
    const defaultNum = (isIncome.value ? 'INC-' : 'EXP-') + String(Math.floor(10000 + Math.random() * 90000));

    const form = ref({
      transaction_date: todayStr,
      payment_method: 'Cash',
      bank_account_id: '',
      amount: 0.00,
      description: '',
      category_id: '',
      contact_id: '',
      tax_id: '',
      number: defaultNum,
      reference: '',
      file: null
    });

    const selectedAccountCurrencySymbol = computed(() => {
      if (!form.value.bank_account_id) return 'Rs';
      const acc = bankAccounts.value.find(a => a.id === form.value.bank_account_id);
      return getCurrencySymbol(acc?.currency);
    });

    const fetchDropdownData = async () => {
      try {
        // Fetch Bank Accounts
        const accRes = await axios.get('/api/bank-accounts');
        bankAccounts.value = Array.isArray(accRes.data) ? accRes.data : (accRes.data?.data || []);
        if (bankAccounts.value.length > 0) {
          form.value.bank_account_id = bankAccounts.value[0].id;
        }

        // Fetch Categories (Chart of Accounts)
        try {
          const chartRes = await axios.get('/api/accounts');
          const allAccounts = Array.isArray(chartRes.data) ? chartRes.data : (chartRes.data?.data || []);
          if (isIncome.value) {
            categories.value = allAccounts.filter(a =>
              a.account_type === 'revenue' || a.account_type === 'income' || a.category === 'revenue'
            );
          } else {
            categories.value = allAccounts.filter(a =>
              a.account_type === 'expense' || a.category === 'expense'
            );
          }
          if (categories.value.length === 0) categories.value = allAccounts;
        } catch (e) {
          categories.value = [];
        }

        // Fetch Contacts (Customers for Income, Suppliers for Expense)
        try {
          const endpoint = isIncome.value ? '/api/customers' : '/api/suppliers';
          const contactRes = await axios.get(endpoint);
          contacts.value = Array.isArray(contactRes.data) ? contactRes.data : (contactRes.data?.data || []);
        } catch (e) {
          contacts.value = [];
        }

        // Fetch Taxes
        try {
          const taxRes = await axios.get('/api/taxes');
          taxes.value = Array.isArray(taxRes.data) ? taxRes.data : (taxRes.data?.data || []);
        } catch (e) {
          taxes.value = [];
        }

      } catch (err) {
        showToast('Error loading form options', 'error');
      }
    };

    const handleFileSelect = (e) => {
      const file = e.target.files[0];
      if (file) {
        form.value.file = file;
        selectedFileName.value = file.name;
      }
    };

    const handleFileDrop = (e) => {
      isDragging.value = false;
      const file = e.dataTransfer.files[0];
      if (file) {
        form.value.file = file;
        selectedFileName.value = file.name;
      }
    };

    const handleCancel = () => {
      router.push('/banking/transactions');
    };

    const saveTransaction = async () => {
      if (!form.value.bank_account_id) {
        showToast('Please select a bank or cash account', 'error');
        return;
      }

      submitting.value = true;
      try {
        const formData = new FormData();
        formData.append('type', isIncome.value ? 'income' : 'expense');
        formData.append('paid_at', form.value.transaction_date);
        formData.append('payment_method', form.value.payment_method || 'Cash');
        formData.append('account_id', form.value.bank_account_id);
        formData.append('amount', form.value.amount || 0);
        formData.append('number', form.value.number);
        if (form.value.description) formData.append('description', form.value.description);
        if (form.value.category_id) formData.append('category_id', form.value.category_id);
        if (isIncome.value && form.value.contact_id) formData.append('customer_id', form.value.contact_id);
        if (!isIncome.value && form.value.contact_id) formData.append('vendor_id', form.value.contact_id);
        if (form.value.tax_id) formData.append('tax_id', form.value.tax_id);
        if (form.value.reference) formData.append('reference', form.value.reference);
        if (form.value.file) formData.append('attachment', form.value.file);

        await axios.post('/api/transactions', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });

        showToast(`${isIncome.value ? 'Income' : 'Expense'} transaction recorded successfully`);
        router.push('/banking/transactions');
      } catch (err) {
        const msg = err.response?.data?.message || `Failed to record ${isIncome.value ? 'income' : 'expense'}`;
        showToast(msg, 'error');
      } finally {
        submitting.value = false;
      }
    };

    const getCurrencySymbol = (code) => {
      switch (code) {
        case 'USD': return '$';
        case 'EUR': return '€';
        case 'GBP': return '£';
        case 'AED': return 'AED';
        case 'SAR': return 'SAR';
        default: return 'Rs';
      }
    };

    onMounted(() => {
      fetchDropdownData();
    });

    return {
      isIncome,
      submitting,
      isDragging,
      selectedFileName,
      form,
      bankAccounts,
      categories,
      contacts,
      taxes,
      paymentMethodOptions,
      selectedAccountCurrencySymbol,
      handleFileSelect,
      handleFileDrop,
      handleCancel,
      saveTransaction,
      getCurrencySymbol
    };
  }
};
</script>
