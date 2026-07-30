<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-zinc-100 tracking-tight">Bank Transfers</h1>
        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">Transfer funds between bank/cash accounts and auto-generate COA journal entries.</p>
      </div>
      <button
        @click="openTransferModal"
        class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all cursor-pointer"
      >
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
        </svg>
        New Transfer
      </button>
    </div>

    <!-- Transfers Table Card -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">
      <!-- Search Toolbar -->
      <div class="p-4 border-b border-slate-200 dark:border-zinc-800 flex items-center justify-between gap-4">
        <div class="relative flex-1 max-w-md">
          <input
            v-model="search"
            type="text"
            placeholder="Search transfer reference, description..."
            class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none"
          />
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-200 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-950/50 text-[11px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">
              <th class="py-3 px-4">Date</th>
              <th class="py-3 px-4">From Account</th>
              <th class="py-3 px-4">To Account</th>
              <th class="py-3 px-4">Reference</th>
              <th class="py-3 px-4">Description</th>
              <th class="py-3 px-4 text-right">Amount ($)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-xs">
            <tr v-if="loading" class="text-center py-6">
              <td colspan="6" class="py-8 text-slate-400 dark:text-zinc-500">Loading transfers...</td>
            </tr>
            <tr v-else-if="filteredTransfers.length === 0" class="text-center py-6">
              <td colspan="6" class="py-8 text-slate-400 dark:text-zinc-500">No transfers recorded.</td>
            </tr>
            <tr
              v-for="trf in filteredTransfers"
              :key="trf.id"
              class="hover:bg-slate-50/80 dark:hover:bg-zinc-800/40 transition-colors"
            >
              <td class="py-3.5 px-4 font-medium text-slate-600 dark:text-zinc-300">{{ trf.transaction_date }}</td>
              <td class="py-3.5 px-4 font-semibold text-rose-600 dark:text-rose-400">{{ trf.from_account_name }}</td>
              <td class="py-3.5 px-4 font-semibold text-emerald-600 dark:text-emerald-400">{{ trf.to_account_name }}</td>
              <td class="py-3.5 px-4 text-slate-500 dark:text-zinc-400 font-mono text-[11px]">{{ trf.reference_number || '-' }}</td>
              <td class="py-3.5 px-4 text-slate-700 dark:text-zinc-300 max-w-xs truncate">{{ trf.description || '-' }}</td>
              <td class="py-3.5 px-4 text-right font-bold text-slate-900 dark:text-zinc-100">{{ companyCurrencySymbol }} {{ formatNumber(trf.amount) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- New Transfer Modal -->
    <Teleport to="body" v-if="showModal">
      <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full transition-all duration-200" style="background-color: rgba(0, 0, 0, 0.6) !important; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
        <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-md shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-900 dark:text-zinc-100 text-left transition-all duration-300 flex flex-col max-h-[90vh] overflow-hidden z-10">
          <div class="p-6 pb-4 border-b border-slate-200 dark:border-zinc-800 flex justify-between items-center">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-zinc-100">New Inter-Account Transfer</h3>
            <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <form @submit.prevent="executeTransfer" class="flex flex-col flex-1 min-h-0">
            <div class="flex-1 overflow-y-auto p-6 space-y-4 pr-4">
              <div>
                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">From Account (Credit) *</label>
                <select v-model="form.from_bank_account_id" required class="w-full px-3 py-2 border border-slate-300 dark:border-zinc-800 rounded-lg bg-slate-50 dark:bg-zinc-950 text-xs text-slate-900 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                  <option :value="null">-- Select Source Bank Account --</option>
                  <option v-for="acc in bankAccounts" :key="acc.id" :value="acc.id">
                    {{ acc.account_name }} (Balance: {{ companyCurrencySymbol }} {{ formatNumber(acc.current_balance) }})
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">To Account (Debit) *</label>
                <select v-model="form.to_bank_account_id" required class="w-full px-3 py-2 border border-slate-300 dark:border-zinc-800 rounded-lg bg-slate-50 dark:bg-zinc-950 text-xs text-slate-900 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                  <option :value="null">-- Select Destination Bank Account --</option>
                  <option v-for="acc in bankAccounts" :key="acc.id" :value="acc.id">
                    {{ acc.account_name }} (Balance: {{ companyCurrencySymbol }} {{ formatNumber(acc.current_balance) }})
                  </option>
                </select>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Amount ({{ companyCurrencySymbol }}) *</label>
                  <input v-model.number="form.amount" type="number" step="0.01" min="0.01" required placeholder="0.00" class="w-full px-3 py-2 border border-slate-300 dark:border-zinc-800 rounded-lg bg-slate-50 dark:bg-zinc-950 text-xs text-slate-900 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                </div>
                <div>
                  <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Transfer Date *</label>
                  <input v-model="form.transfer_date" type="date" required class="w-full px-3 py-2 border border-slate-300 dark:border-zinc-800 rounded-lg bg-slate-50 dark:bg-zinc-950 text-xs text-slate-900 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                </div>
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Reference Number</label>
                <input v-model="form.reference_number" type="text" placeholder="e.g. TRF-1002" class="w-full px-3 py-2 border border-slate-300 dark:border-zinc-800 rounded-lg bg-slate-50 dark:bg-zinc-950 text-xs text-slate-900 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Description / Reason</label>
                <textarea v-model="form.description" rows="2" placeholder="Fund transfer description..." class="w-full px-3 py-2 border border-slate-300 dark:border-zinc-800 rounded-lg bg-slate-50 dark:bg-zinc-950 text-xs text-slate-900 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
              </div>
            </div>

            <div class="flex justify-end space-x-3 p-6 border-t border-slate-200 dark:border-zinc-800 shrink-0">
              <button type="button" @click="showModal = false" class="px-4 h-9 bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-200 rounded-lg text-xs font-semibold">Cancel</button>
              <button type="submit" :disabled="submitting" class="px-4 h-9 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-sm">
                {{ submitting ? 'Transferring...' : 'Execute Transfer' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useCurrencyStore } from '@/stores/currency';

export default {
  name: 'BankingTransfers',
  setup() {
    const { showToast } = useToast();
    const currencyStore = useCurrencyStore();

    const companyCurrencySymbol = computed(() => {
      return currencyStore.symbol || currencyStore.tenantCurrencyCode || 'PKR';
    });

    const bankAccounts = ref([]);
    const transfers = ref([]);
    const loading = ref(true);
    const submitting = ref(false);
    const search = ref('');
    const showModal = ref(false);

    const form = ref({
      from_bank_account_id: null,
      to_bank_account_id: null,
      amount: 0,
      transfer_date: new Date().toISOString().split('T')[0],
      reference_number: '',
      description: '',
      payment_method: 'bank_transfer',
    });

    const fetchAccounts = async () => {
      try {
        const res = await api.banking.accounts();
        bankAccounts.value = res.data || [];
      } catch (e) {
        // ignore
      }
    };

    const fetchTransfers = async () => {
      loading.value = true;
      try {
        const response = await api.banking.transactions({ search: 'Transfer' });
        const raw = response.data?.data || response.data || [];
        // Map raw transactions into grouped transfer pairs if possible
        transfers.value = raw.map(tx => {
          return {
            id: tx.id,
            transaction_date: tx.transaction_date,
            from_account_name: tx.transaction_type === 'credit' ? (tx.bank_account?.account_name || 'Bank') : 'Inter-Account',
            to_account_name: tx.transaction_type === 'debit' ? (tx.bank_account?.account_name || 'Bank') : 'Inter-Account',
            reference_number: tx.reference_number,
            description: tx.description,
            amount: tx.amount,
          };
        });
      } catch (error) {
        showToast('Failed to load transfers', 'error');
      } finally {
        loading.value = false;
      }
    };

    const filteredTransfers = computed(() => {
      if (!search.value.trim()) return transfers.value;
      const q = search.value.toLowerCase();
      return transfers.value.filter(t =>
        (t.reference_number && t.reference_number.toLowerCase().includes(q)) ||
        (t.description && t.description.toLowerCase().includes(q)) ||
        t.from_account_name.toLowerCase().includes(q) ||
        t.to_account_name.toLowerCase().includes(q)
      );
    });

    const openTransferModal = () => {
      form.value = {
        from_bank_account_id: null,
        to_bank_account_id: null,
        amount: 0,
        transfer_date: new Date().toISOString().split('T')[0],
        reference_number: '',
        description: '',
        payment_method: 'bank_transfer',
      };
      showModal.value = true;
    };

    const executeTransfer = async () => {
      if (form.value.from_bank_account_id === form.value.to_bank_account_id) {
        showToast('Source and destination accounts must be different', 'error');
        return;
      }
      submitting.value = true;
      try {
        await api.banking.transfer(form.value);
        showToast('Transfer completed & COA journal created');
        showModal.value = false;
        fetchTransfers();
        fetchAccounts();
      } catch (err) {
        const msg = err.response?.data?.message || 'Transfer execution failed';
        showToast(msg, 'error');
      } finally {
        submitting.value = false;
      }
    };

    const formatNumber = (val) => {
      return Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    onMounted(() => {
      currencyStore.fetchCurrencies();
      fetchAccounts();
      fetchTransfers();
    });

    return {
      companyCurrencySymbol,
      bankAccounts,
      transfers,
      loading,
      submitting,
      search,
      showModal,
      form,
      filteredTransfers,
      openTransferModal,
      executeTransfer,
      formatNumber,
    };
  },
};
</script>
