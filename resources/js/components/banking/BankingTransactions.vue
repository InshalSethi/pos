<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 space-y-6">
    <!-- Header with Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-zinc-100 tracking-tight">Banking Transactions</h1>
        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">View all income, expense, and transfer records across your bank & cash accounts.</p>
      </div>

      <div class="flex items-center space-x-3">
        <button
          @click="navigateToCreateIncome"
          class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all cursor-pointer shrink-0"
        >
          + New Income
        </button>
        <button
          @click="navigateToCreateExpense"
          class="inline-flex items-center justify-center px-4 py-2 bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white rounded-xl text-xs font-semibold shadow-sm transition-all cursor-pointer shrink-0"
        >
          + New Expense
        </button>
      </div>
    </div>

    <!-- Filter Tabs & Options -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">
      <div class="p-4 border-b border-slate-200 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <!-- Type Tabs -->
        <div class="flex items-center space-x-1 bg-slate-100 dark:bg-zinc-950 p-1 rounded-xl">
          <button
            @click="activeTab = 'all'"
            :class="[
              'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer',
              activeTab === 'all'
                ? 'bg-white dark:bg-zinc-800 text-indigo-600 dark:text-indigo-400 shadow-sm'
                : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-200'
            ]"
          >
            All Transactions
          </button>
          <button
            @click="activeTab = 'income'"
            :class="[
              'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer',
              activeTab === 'income'
                ? 'bg-white dark:bg-zinc-800 text-emerald-600 dark:text-emerald-400 shadow-sm'
                : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-200'
            ]"
          >
            Income
          </button>
          <button
            @click="activeTab = 'expense'"
            :class="[
              'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer',
              activeTab === 'expense'
                ? 'bg-white dark:bg-zinc-800 text-rose-600 dark:text-rose-400 shadow-sm'
                : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-200'
            ]"
          >
            Expense
          </button>
        </div>

        <!-- Account Filter & Search -->
        <div class="flex items-center gap-3">
          <select
            v-model="selectedAccount"
            class="px-3 py-2 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-xs text-slate-800 dark:text-zinc-200 focus:outline-none font-normal"
          >
            <option value="">All Bank Accounts</option>
            <option v-for="acc in bankAccounts" :key="acc.id" :value="acc.id">
              {{ acc.account_name }} ({{ acc.bank_name || acc.account_name }})
            </option>
          </select>

          <div class="relative w-64">
            <input
              v-model="search"
              type="text"
              placeholder="Search reference, description..."
              class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none font-normal"
            />
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Transactions Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-200 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-950/50 text-[11px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">
              <th class="py-3.5 px-4">Date</th>
              <th class="py-3.5 px-4">Bank Account</th>
              <th class="py-3.5 px-4">Reference</th>
              <th class="py-3.5 px-4">Description</th>
              <th class="py-3.5 px-4 text-center">Type</th>
              <th class="py-3.5 px-4 text-right">Amount</th>
              <th class="py-3.5 px-4 text-right">Running Balance</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-xs">
            <tr v-if="loading" class="text-center py-6">
              <td colspan="7" class="py-8 text-slate-400 dark:text-zinc-500">Loading transactions...</td>
            </tr>
            <tr v-else-if="filteredTransactions.length === 0" class="text-center py-6">
              <td colspan="7" class="py-8 text-slate-400 dark:text-zinc-500">No transactions found.</td>
            </tr>
            <tr
              v-for="tx in filteredTransactions"
              :key="tx.id"
              class="hover:bg-slate-50/80 dark:hover:bg-zinc-800/40 transition-colors"
            >
              <td class="py-3.5 px-4 text-slate-600 dark:text-zinc-300 font-medium">{{ tx.transaction_date }}</td>
              <td class="py-3.5 px-4 text-slate-800 dark:text-zinc-100 font-semibold">{{ tx.bank_account ? tx.bank_account.account_name : 'Bank' }}</td>
              <td class="py-3.5 px-4 text-slate-500 dark:text-zinc-400 font-mono text-[11px]">{{ tx.reference_number || '-' }}</td>
              <td class="py-3.5 px-4 text-slate-700 dark:text-zinc-300 max-w-xs truncate">{{ tx.description || '-' }}</td>
              <td class="py-3.5 px-4 text-center">
                <span
                  :class="isIncomeType(tx.transaction_type) ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40'"
                  class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"
                >
                  {{ isIncomeType(tx.transaction_type) ? 'Income' : 'Expense' }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-right font-bold" :class="isIncomeType(tx.transaction_type) ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                {{ isIncomeType(tx.transaction_type) ? '+' : '-' }}Rs {{ formatNumber(tx.amount) }}
              </td>
              <td class="py-3.5 px-4 text-right font-semibold text-slate-900 dark:text-zinc-100">Rs {{ formatNumber(tx.running_balance) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';

export default {
  name: 'BankingTransactions',
  setup() {
    const router = useRouter();
    const { showToast } = useToast();
    const transactions = ref([]);
    const bankAccounts = ref([]);
    const loading = ref(true);
    const activeTab = ref('all');
    const selectedAccount = ref('');
    const search = ref('');

    const isIncomeType = (type) => {
      return type === 'credit' || type === 'income';
    };

    const fetchTransactions = async () => {
      loading.value = true;
      try {
        const response = await api.banking.transactions();
        transactions.value = response.data?.data || response.data || [];
      } catch (error) {
        showToast('Failed to load banking transactions', 'error');
      } finally {
        loading.value = false;
      }
    };

    const fetchBankAccounts = async () => {
      try {
        const res = await api.banking.accounts();
        bankAccounts.value = res.data || [];
      } catch (e) {
        // ignore
      }
    };

    const navigateToCreateIncome = () => {
      router.push('/banking/transactions/create-income');
    };

    const navigateToCreateExpense = () => {
      router.push('/banking/transactions/create-expense');
    };

    const filteredTransactions = computed(() => {
      return transactions.value.filter(tx => {
        const isInc = isIncomeType(tx.transaction_type);
        // Tab filter
        if (activeTab.value === 'income' && !isInc) return false;
        if (activeTab.value === 'expense' && isInc) return false;

        // Account filter
        if (selectedAccount.value && tx.bank_account_id != selectedAccount.value) return false;

        // Search filter
        if (search.value.trim()) {
          const q = search.value.toLowerCase();
          const refNum = (tx.reference_number || '').toLowerCase();
          const desc = (tx.description || '').toLowerCase();
          if (!refNum.includes(q) && !desc.includes(q)) return false;
        }

        return true;
      });
    });

    const formatNumber = (val) => {
      return Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    onMounted(() => {
      fetchTransactions();
      fetchBankAccounts();
    });

    return {
      transactions,
      bankAccounts,
      loading,
      activeTab,
      selectedAccount,
      search,
      filteredTransactions,
      isIncomeType,
      navigateToCreateIncome,
      navigateToCreateExpense,
      formatNumber,
    };
  },
};
</script>
