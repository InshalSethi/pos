<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-zinc-100 tracking-tight">Bank Reconciliations</h1>
        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">Reconcile system transactions against your official bank statements.</p>
      </div>
    </div>

    <!-- Account Selection & Statement Period Bar -->
    <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-sm space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div>
          <label class="block text-[10px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider mb-1">Select Bank Account *</label>
          <select
            v-model="selectedAccountId"
            @change="onAccountChange"
            class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-xs text-slate-800 dark:text-zinc-200 focus:outline-none"
          >
            <option :value="null">-- Select Account --</option>
            <option v-for="acc in bankAccounts" :key="acc.id" :value="acc.id">
              {{ acc.account_name }} ({{ acc.bank_name }})
            </option>
          </select>
        </div>

        <div>
          <label class="block text-[10px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider mb-1">Statement Date</label>
          <input
            v-model="statementDate"
            type="date"
            class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-xs text-slate-800 dark:text-zinc-200 focus:outline-none"
          />
        </div>

        <div>
          <label class="block text-[10px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider mb-1">Statement Ending Balance ($)</label>
          <input
            v-model.number="statementBalance"
            type="number"
            step="0.01"
            placeholder="0.00"
            class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-xs text-slate-800 dark:text-zinc-200 focus:outline-none"
          />
        </div>

        <div class="flex items-end">
          <button
            @click="fetchUnreconciledTransactions"
            :disabled="!selectedAccountId"
            class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl text-xs font-semibold shadow-sm transition-all"
          >
            Load Transactions
          </button>
        </div>
      </div>
    </div>

    <!-- Summary Metrics Card -->
    <div v-if="selectedAccountId && summary" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
      <div class="p-4 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-sm">
        <p class="text-[10px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">System Balance</p>
        <p class="text-xl font-extrabold text-slate-900 dark:text-zinc-100 mt-1">${{ formatNumber(summary.account_balance) }}</p>
      </div>

      <div class="p-4 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-sm">
        <p class="text-[10px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">Statement Balance</p>
        <p class="text-xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1">${{ formatNumber(statementBalance) }}</p>
      </div>

      <div class="p-4 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-sm">
        <p class="text-[10px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">Selected Cleared</p>
        <p class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">${{ formatNumber(selectedClearedTotal) }}</p>
      </div>

      <div class="p-4 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-sm">
        <p class="text-[10px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">Difference</p>
        <p class="text-xl font-extrabold mt-1" :class="difference === 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-500'">
          ${{ formatNumber(difference) }}
        </p>
      </div>
    </div>

    <!-- Unreconciled Transactions Table -->
    <div v-if="selectedAccountId" class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">
      <div class="p-4 border-b border-slate-200 dark:border-zinc-800 flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <input
            type="checkbox"
            :checked="isAllSelected"
            @change="toggleSelectAll"
            class="rounded border-slate-300 dark:border-zinc-700 text-indigo-600 focus:ring-indigo-500"
          />
          <span class="text-xs font-semibold text-slate-700 dark:text-zinc-300">Select All Unreconciled Transactions ({{ selectedTxIds.length }}/{{ transactions.length }} selected)</span>
        </div>

        <button
          @click="reconcileSelected"
          :disabled="submitting || selectedTxIds.length === 0"
          class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-xl text-xs font-semibold shadow-sm transition-all"
        >
          {{ submitting ? 'Reconciling...' : `Reconcile Selected (${selectedTxIds.length})` }}
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-200 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-950/50 text-[11px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">
              <th class="py-3 px-4 w-10 text-center">Match</th>
              <th class="py-3 px-4">Date</th>
              <th class="py-3 px-4">Reference</th>
              <th class="py-3 px-4">Description</th>
              <th class="py-3 px-4 text-center">Type</th>
              <th class="py-3 px-4 text-right">Amount ($)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-xs">
            <tr v-if="loading" class="text-center py-6">
              <td colspan="6" class="py-8 text-slate-400 dark:text-zinc-500">Loading unreconciled transactions...</td>
            </tr>
            <tr v-else-if="transactions.length === 0" class="text-center py-6">
              <td colspan="6" class="py-8 text-slate-400 dark:text-zinc-500">All transactions are reconciled for this account!</td>
            </tr>
            <tr
              v-for="tx in transactions"
              :key="tx.id"
              class="hover:bg-slate-50/80 dark:hover:bg-zinc-800/40 transition-colors"
            >
              <td class="py-3.5 px-4 text-center">
                <input
                  type="checkbox"
                  :value="tx.id"
                  v-model="selectedTxIds"
                  class="rounded border-slate-300 dark:border-zinc-700 text-indigo-600 focus:ring-indigo-500"
                />
              </td>
              <td class="py-3.5 px-4 font-medium text-slate-600 dark:text-zinc-300">{{ tx.transaction_date }}</td>
              <td class="py-3.5 px-4 text-slate-500 dark:text-zinc-400 font-mono text-[11px]">{{ tx.reference_number || '-' }}</td>
              <td class="py-3.5 px-4 text-slate-700 dark:text-zinc-300 max-w-xs truncate">{{ tx.description || '-' }}</td>
              <td class="py-3.5 px-4 text-center">
                <span
                  :class="tx.transaction_type === 'debit' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400'"
                  class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"
                >
                  {{ tx.transaction_type }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-right font-bold" :class="tx.transaction_type === 'debit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                {{ tx.transaction_type === 'debit' ? '+' : '-' }}${{ formatNumber(tx.amount) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';

export default {
  name: 'BankingReconciliations',
  setup() {
    const route = useRoute();
    const { showToast } = useToast();
    const bankAccounts = ref([]);
    const selectedAccountId = ref(null);
    const statementDate = ref(new Date().toISOString().split('T')[0]);
    const statementBalance = ref(0);
    const summary = ref(null);
    const transactions = ref([]);
    const selectedTxIds = ref([]);
    const loading = ref(false);
    const submitting = ref(false);

    const fetchAccounts = async () => {
      try {
        const res = await api.banking.accounts();
        bankAccounts.value = res.data || [];
        if (route.query.account_id) {
          selectedAccountId.value = parseInt(route.query.account_id);
          onAccountChange();
        } else if (bankAccounts.value.length > 0) {
          selectedAccountId.value = bankAccounts.value[0].id;
          onAccountChange();
        }
      } catch (e) {
        // ignore
      }
    };

    const onAccountChange = async () => {
      if (!selectedAccountId.value) return;
      fetchSummary();
      fetchUnreconciledTransactions();
    };

    const fetchSummary = async () => {
      if (!selectedAccountId.value) return;
      try {
        const res = await api.banking.reconciliationSummary(selectedAccountId.value);
        summary.value = res.data;
        if (!statementBalance.value && res.data.account_balance) {
          statementBalance.value = res.data.account_balance;
        }
      } catch (e) {
        // ignore
      }
    };

    const fetchUnreconciledTransactions = async () => {
      if (!selectedAccountId.value) return;
      loading.value = true;
      try {
        const res = await api.banking.accountTransactions(selectedAccountId.value, { reconciled: false });
        transactions.value = res.data?.data || res.data || [];
        selectedTxIds.value = transactions.value.map(t => t.id);
      } catch (error) {
        showToast('Failed to load transactions', 'error');
      } finally {
        loading.value = false;
      }
    };

    const selectedClearedTotal = computed(() => {
      return transactions.value
        .filter(t => selectedTxIds.value.includes(t.id))
        .reduce((sum, t) => {
          const amt = parseFloat(t.amount) || 0;
          return t.transaction_type === 'debit' ? sum + amt : sum - amt;
        }, 0);
    });

    const difference = computed(() => {
      const target = parseFloat(statementBalance.value) || 0;
      return target - ((summary.value?.reconciled_balance || 0) + selectedClearedTotal.value);
    });

    const isAllSelected = computed(() => {
      return transactions.value.length > 0 && selectedTxIds.value.length === transactions.value.length;
    });

    const toggleSelectAll = () => {
      if (isAllSelected.value) {
        selectedTxIds.value = [];
      } else {
        selectedTxIds.value = transactions.value.map(t => t.id);
      }
    };

    const reconcileSelected = async () => {
      if (!selectedAccountId.value || selectedTxIds.value.length === 0) return;
      submitting.value = true;
      try {
        await api.banking.reconcile(selectedAccountId.value, {
          statement_date: statementDate.value,
          statement_balance: statementBalance.value,
          transaction_ids: selectedTxIds.value,
        });
        showToast('Transactions reconciled successfully');
        fetchSummary();
        fetchUnreconciledTransactions();
      } catch (err) {
        const msg = err.response?.data?.message || 'Reconciliation failed';
        showToast(msg, 'error');
      } finally {
        submitting.value = false;
      }
    };

    const formatNumber = (val) => {
      return Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    onMounted(() => {
      fetchAccounts();
    });

    return {
      bankAccounts,
      selectedAccountId,
      statementDate,
      statementBalance,
      summary,
      transactions,
      selectedTxIds,
      loading,
      submitting,
      selectedClearedTotal,
      difference,
      isAllSelected,
      onAccountChange,
      fetchUnreconciledTransactions,
      toggleSelectAll,
      reconcileSelected,
      formatNumber,
    };
  },
};
</script>
