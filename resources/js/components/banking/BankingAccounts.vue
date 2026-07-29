<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-zinc-100 tracking-tight">Bank Accounts</h1>
        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">Manage bank and cash accounts synced with your Chart of Accounts.</p>
      </div>
      <button
        @click="openCreateModal"
        class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all cursor-pointer"
      >
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Bank Account
      </button>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-sm flex items-center justify-between">
        <div>
          <p class="text-[11px] font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">Total Balance</p>
          <p class="text-2xl font-extrabold text-slate-900 dark:text-zinc-100 mt-1">${{ formatNumber(totalBalance) }}</p>
        </div>
        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
          </svg>
        </div>
      </div>

      <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-sm flex items-center justify-between">
        <div>
          <p class="text-[11px] font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">Active Accounts</p>
          <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">{{ activeAccountsCount }}</p>
        </div>
        <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>

      <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-sm flex items-center justify-between">
        <div>
          <p class="text-[11px] font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">Total Accounts</p>
          <p class="text-2xl font-extrabold text-slate-900 dark:text-zinc-100 mt-1">{{ accounts.length }}</p>
        </div>
        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 flex items-center justify-center">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Search & List Table Card -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">
      <!-- Search toolbar -->
      <div class="p-4 border-b border-slate-200 dark:border-zinc-800 flex items-center justify-between gap-4">
        <div class="relative flex-1 max-w-md">
          <input
            v-model="search"
            type="text"
            placeholder="Search bank name, account number..."
            class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
          />
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>

      <!-- Accounts Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-200 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-950/50 text-[11px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">
              <th class="py-3 px-4">Account Name</th>
              <th class="py-3 px-4">Bank Name</th>
              <th class="py-3 px-4">Account Number</th>
              <th class="py-3 px-4">Type</th>
              <th class="py-3 px-4">COA Account</th>
              <th class="py-3 px-4 text-right">Balance</th>
              <th class="py-3 px-4 text-center">Status</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-xs">
            <tr v-if="loading" class="text-center py-6">
              <td colspan="8" class="py-8 text-slate-400 dark:text-zinc-500">Loading bank accounts...</td>
            </tr>
            <tr v-else-if="filteredAccounts.length === 0" class="text-center py-6">
              <td colspan="8" class="py-8 text-slate-400 dark:text-zinc-500">No bank accounts found.</td>
            </tr>
            <tr
              v-for="acc in filteredAccounts"
              :key="acc.id"
              class="hover:bg-slate-50/80 dark:hover:bg-zinc-800/40 transition-colors"
            >
              <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-zinc-100">{{ acc.account_name }}</td>
              <td class="py-3.5 px-4 text-slate-600 dark:text-zinc-300">{{ acc.bank_name }}</td>
              <td class="py-3.5 px-4 text-slate-500 dark:text-zinc-400 font-mono text-[11px]">{{ acc.account_number }}</td>
              <td class="py-3.5 px-4 text-slate-600 dark:text-zinc-300 capitalize">{{ acc.account_type.replace('_', ' ') }}</td>
              <td class="py-3.5 px-4 text-indigo-600 dark:text-indigo-400 font-medium">
                {{ acc.chart_account ? `${acc.chart_account.account_code} - ${acc.chart_account.account_name}` : '-' }}
              </td>
              <td class="py-3.5 px-4 text-right font-bold text-slate-900 dark:text-zinc-100">${{ formatNumber(acc.current_balance ?? acc.opening_balance) }}</td>
              <td class="py-3.5 px-4 text-center">
                <span
                  :class="acc.is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400' : 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400'"
                  class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold"
                >
                  {{ acc.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-right space-x-2">
                <button
                  @click="openEditModal(acc)"
                  class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 font-medium"
                >
                  Edit
                </button>
                <router-link
                  :to="`/banking/reconciliations?account_id=${acc.id}`"
                  class="text-slate-600 hover:text-slate-800 dark:text-zinc-400 font-medium ml-2"
                >
                  Reconcile
                </router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full transition-all duration-200" style="background-color: rgba(0, 0, 0, 0.75) !important; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
        <div class="relative mx-auto border border-slate-800 w-full max-w-lg shadow-2xl rounded-2xl bg-[#12141a] text-slate-100 text-left transition-all duration-300 flex flex-col max-h-[90vh] overflow-hidden z-10">
          <div class="p-6 pb-4 border-b border-slate-800 flex justify-between items-center">
            <h3 class="text-sm font-bold uppercase tracking-wider">{{ isEdit ? 'Edit Bank Account' : 'Add New Bank Account' }}</h3>
            <button @click="showModal = false" class="text-slate-400 hover:text-slate-200 p-1 rounded-lg">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <form @submit.prevent="saveAccount" class="flex flex-col flex-1 min-h-0">
            <div class="flex-1 overflow-y-auto p-6 space-y-4 pr-4">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Account Name *</label>
                <input v-model="form.account_name" type="text" required placeholder="e.g. Primary Checking" class="w-full px-3 py-2 border border-slate-800 rounded-lg bg-zinc-950 text-xs text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Bank Name *</label>
                  <input v-model="form.bank_name" type="text" required placeholder="e.g. Chase Bank" class="w-full px-3 py-2 border border-slate-800 rounded-lg bg-zinc-950 text-xs text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                </div>
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Account Number *</label>
                  <input v-model="form.account_number" type="text" required placeholder="e.g. 1234567890" class="w-full px-3 py-2 border border-slate-800 rounded-lg bg-zinc-950 text-xs text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Account Type *</label>
                  <select v-model="form.account_type" required class="w-full px-3 py-2 border border-slate-800 rounded-lg bg-zinc-950 text-xs text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="checking">Checking</option>
                    <option value="savings">Savings</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="line_of_credit">Line of Credit</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Opening Balance ($)</label>
                  <input v-model="form.opening_balance" type="number" step="0.01" placeholder="0.00" class="w-full px-3 py-2 border border-slate-800 rounded-lg bg-zinc-950 text-xs text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                </div>
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Link Chart of Account (COA)</label>
                <select v-model="form.chart_account_id" class="w-full px-3 py-2 border border-slate-800 rounded-lg bg-zinc-950 text-xs text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                  <option :value="null">-- Auto-create Asset Account in COA --</option>
                  <option v-for="coa in coaAccounts" :key="coa.id" :value="coa.id">
                    {{ coa.account_code }} - {{ coa.account_name }} ({{ coa.account_type }})
                  </option>
                </select>
                <p class="text-[10px] text-slate-400 mt-1">If left empty, a new Cash & Bank asset account will be automatically generated in Chart of Accounts.</p>
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Description</label>
                <textarea v-model="form.description" rows="2" placeholder="Optional notes..." class="w-full px-3 py-2 border border-slate-800 rounded-lg bg-zinc-950 text-xs text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
              </div>
            </div>

            <div class="flex justify-end space-x-3 p-6 border-t border-slate-800 shrink-0">
              <button type="button" @click="showModal = false" class="px-4 h-9 bg-zinc-800 hover:bg-zinc-700 text-zinc-200 rounded-lg text-xs font-semibold">Cancel</button>
              <button type="submit" :disabled="submitting" class="px-4 h-9 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold">
                {{ submitting ? 'Saving...' : (isEdit ? 'Update Account' : 'Create Account') }}
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

export default {
  name: 'BankingAccounts',
  setup() {
    const { showToast } = useToast();
    const accounts = ref([]);
    const coaAccounts = ref([]);
    const loading = ref(true);
    const submitting = ref(false);
    const search = ref('');
    const showModal = ref(false);
    const isEdit = ref(false);
    const editingId = ref(null);

    const form = ref({
      account_name: '',
      bank_name: '',
      account_number: '',
      account_type: 'checking',
      opening_balance: 0,
      chart_account_id: null,
      description: '',
      is_active: true,
    });

    const fetchAccounts = async () => {
      loading.value = true;
      try {
        const response = await api.banking.accounts();
        accounts.value = response.data || [];
      } catch (error) {
        showToast('Failed to load bank accounts', 'error');
      } finally {
        loading.value = false;
      }
    };

    const fetchCoaAccounts = async () => {
      try {
        const res = await api.accounting.accounts();
        coaAccounts.value = res.data || [];
      } catch (e) {
        // ignore
      }
    };

    const totalBalance = computed(() => {
      return accounts.value.reduce((sum, acc) => sum + (parseFloat(acc.current_balance ?? acc.opening_balance) || 0), 0);
    });

    const activeAccountsCount = computed(() => {
      return accounts.value.filter(a => a.is_active).length;
    });

    const filteredAccounts = computed(() => {
      if (!search.value.trim()) return accounts.value;
      const q = search.value.toLowerCase();
      return accounts.value.filter(a =>
        a.account_name.toLowerCase().includes(q) ||
        a.bank_name.toLowerCase().includes(q) ||
        a.account_number.toLowerCase().includes(q)
      );
    });

    const openCreateModal = () => {
      isEdit.value = false;
      editingId.value = null;
      form.value = {
        account_name: '',
        bank_name: '',
        account_number: '',
        account_type: 'checking',
        opening_balance: 0,
        chart_account_id: null,
        description: '',
        is_active: true,
      };
      showModal.value = true;
    };

    const openEditModal = (acc) => {
      isEdit.value = true;
      editingId.value = acc.id;
      form.value = {
        account_name: acc.account_name,
        bank_name: acc.bank_name,
        account_number: acc.account_number,
        account_type: acc.account_type,
        opening_balance: acc.opening_balance,
        chart_account_id: acc.chart_account_id,
        description: acc.description || '',
        is_active: acc.is_active,
      };
      showModal.value = true;
    };

    const saveAccount = async () => {
      submitting.value = true;
      try {
        if (isEdit.value) {
          await api.banking.updateAccount(editingId.value, form.value);
          showToast('Bank account updated successfully');
        } else {
          await api.banking.createAccount(form.value);
          showToast('Bank account created and synced with COA');
        }
        showModal.value = false;
        fetchAccounts();
      } catch (err) {
        const msg = err.response?.data?.message || 'Failed to save bank account';
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
      fetchCoaAccounts();
    });

    return {
      accounts,
      coaAccounts,
      loading,
      submitting,
      search,
      showModal,
      isEdit,
      form,
      totalBalance,
      activeAccountsCount,
      filteredAccounts,
      openCreateModal,
      openEditModal,
      saveAccount,
      formatNumber,
    };
  },
};
</script>
