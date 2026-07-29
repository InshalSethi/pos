<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 space-y-6">
    <!-- Header with Views & Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-zinc-100 tracking-tight">Chart of Accounts</h1>
        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">Manage account hierarchy, balances, and cash & bank asset structures.</p>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <div class="inline-flex p-1 bg-slate-100 dark:bg-zinc-950 rounded-xl border border-slate-200 dark:border-zinc-800">
          <button
            @click="activeView = 'tree'"
            :class="[
              'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer',
              activeView === 'tree'
                ? 'bg-indigo-600 text-white shadow-sm'
                : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-200'
            ]"
          >
            Tree View
          </button>
          <button
            @click="activeView = 'list'"
            :class="[
              'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer',
              activeView === 'list'
                ? 'bg-indigo-600 text-white shadow-sm'
                : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-200'
            ]"
          >
            List View
          </button>
          <button
            @click="activeView = 'balances'"
            :class="[
              'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer',
              activeView === 'balances'
                ? 'bg-indigo-600 text-white shadow-sm'
                : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-200'
            ]"
          >
            Account Balances
          </button>
        </div>

        <button
          @click="openNewAccountModal"
          class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all cursor-pointer"
        >
          + New Account
        </button>
      </div>
    </div>

    <!-- Account Type Filters -->
    <div class="flex items-center space-x-2 overflow-x-auto pb-1">
      <button
        @click="selectTypeFilter('')"
        :class="[
          'px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all cursor-pointer',
          selectedAccountType === ''
            ? 'bg-indigo-600 text-white shadow-sm'
            : 'bg-white dark:bg-zinc-900 text-slate-700 dark:text-zinc-300 border border-slate-200 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-800'
        ]"
      >
        All Types
      </button>
      <button
        v-for="type in accountTypes"
        :key="type.value"
        @click="selectTypeFilter(type.value)"
        :class="[
          'px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all cursor-pointer',
          selectedAccountType === type.value
            ? 'bg-indigo-600 text-white shadow-sm'
            : 'bg-white dark:bg-zinc-900 text-slate-700 dark:text-zinc-300 border border-slate-200 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-800'
        ]"
      >
        {{ type.label }}
      </button>
    </div>

    <!-- Main Content Container -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden p-6">
      <!-- TREE VIEW -->
      <div v-if="activeView === 'tree'" class="space-y-4">
        <h2 class="text-base font-bold text-slate-900 dark:text-zinc-100">Account Hierarchy</h2>

        <div v-if="loadingTree" class="text-center py-10">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
          <p class="mt-2 text-xs text-slate-400">Loading account hierarchy tree...</p>
        </div>

        <div v-else-if="accountTree.length === 0" class="text-center py-10 text-slate-400 text-xs">
          No accounts found for selected criteria.
        </div>

        <div v-else class="space-y-2">
          <div v-for="account in accountTree" :key="account.id">
            <AccountTreeNode
              :account="account"
              :level="0"
              @edit="editAccount"
              @delete="deleteAccount"
            />
          </div>
        </div>
      </div>

      <!-- LIST VIEW -->
      <div v-else-if="activeView === 'list'" class="space-y-4">
        <div class="flex items-center justify-between gap-4 mb-2">
          <h2 class="text-base font-bold text-slate-900 dark:text-zinc-100">All Accounts</h2>
          <div class="relative w-64">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search code or name..."
              @input="debouncedSearch"
              class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none"
            />
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>

        <div class="overflow-x-auto border border-slate-200 dark:border-zinc-800 rounded-xl">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="border-b border-slate-200 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-950/50 font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider text-[11px]">
                <th class="py-3 px-4">Code</th>
                <th class="py-3 px-4">Account Name</th>
                <th class="py-3 px-4">Type</th>
                <th class="py-3 px-4">Subtype</th>
                <th class="py-3 px-4 text-right">Balance</th>
                <th class="py-3 px-4 text-center">Status</th>
                <th class="py-3 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60">
              <tr v-if="loadingAccounts" class="text-center py-6">
                <td colspan="7" class="py-8 text-slate-400">Loading accounts list...</td>
              </tr>
              <tr v-else-if="accounts.length === 0" class="text-center py-6">
                <td colspan="7" class="py-8 text-slate-400">No accounts found.</td>
              </tr>
              <tr
                v-for="acc in accounts"
                :key="acc.id"
                class="group hover:bg-slate-50/80 dark:hover:bg-zinc-800/40 transition-colors"
              >
                <td class="py-3.5 px-4 font-mono font-bold text-slate-800 dark:text-zinc-100">{{ acc.account_code }}</td>
                <td class="py-3.5 px-4 font-semibold text-slate-900 dark:text-zinc-100">
                  {{ acc.account_name }}
                  <span v-if="acc.is_system_account" class="ml-1 px-1.5 py-0.5 rounded text-[10px] bg-slate-100 dark:bg-zinc-800 text-slate-500 font-normal">System</span>
                </td>
                <td class="py-3.5 px-4">
                  <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase" :class="getAccountTypeBadgeClass(acc.account_type)">
                    {{ acc.account_type }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-slate-600 dark:text-zinc-400 capitalize">{{ formatAccountSubtype(acc.account_subtype) }}</td>
                <td class="py-3.5 px-4 text-right font-extrabold text-slate-900 dark:text-zinc-100">${{ formatNumber(acc.current_balance ?? acc.opening_balance) }}</td>
                <td class="py-3.5 px-4 text-center">
                  <span
                    :class="acc.is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400' : 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400'"
                    class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold"
                  >
                    {{ acc.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-right">
                  <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-150 inline-flex items-center bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl shadow-sm divide-x divide-slate-200 dark:divide-zinc-800 overflow-hidden shrink-0">
                    <!-- Edit Button -->
                    <button
                      @click="editAccount(acc)"
                      type="button"
                      title="Edit Account"
                      class="p-1.5 px-2.5 text-slate-500 dark:text-zinc-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                      </svg>
                    </button>

                    <!-- Conditional Delete OR Lock Icon -->
                    <span
                      v-if="acc.is_system_account || acc.account_code === '1010' || acc.account_name === 'Cash'"
                      title="System Account (Cannot be deleted)"
                      class="p-1.5 px-2.5 text-slate-300 dark:text-zinc-600 bg-slate-50 dark:bg-zinc-950 cursor-not-allowed inline-flex items-center justify-center"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                      </svg>
                    </span>
                    <button
                      v-else
                      @click="deleteAccount(acc)"
                      type="button"
                      title="Delete Account"
                      class="p-1.5 px-2.5 text-slate-500 dark:text-zinc-400 hover:text-rose-600 dark:hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ACCOUNT BALANCES VIEW -->
      <div v-else-if="activeView === 'balances'" class="space-y-6">
        <h2 class="text-base font-bold text-slate-900 dark:text-zinc-100">Account Summary Balances</h2>

        <div v-if="loadingBalances" class="text-center py-10">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
          <p class="mt-2 text-xs text-slate-400">Loading balances summary...</p>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div class="p-5 rounded-2xl bg-blue-50/50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-900/40">
            <p class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Total Assets</p>
            <p class="text-2xl font-extrabold text-blue-950 dark:text-blue-100 mt-1">${{ formatNumber(balances.assets) }}</p>
          </div>

          <div class="p-5 rounded-2xl bg-rose-50/50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/40">
            <p class="text-xs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Total Liabilities</p>
            <p class="text-2xl font-extrabold text-rose-950 dark:text-rose-100 mt-1">${{ formatNumber(balances.liabilities) }}</p>
          </div>

          <div class="p-5 rounded-2xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/40">
            <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Total Equity</p>
            <p class="text-2xl font-extrabold text-emerald-950 dark:text-emerald-100 mt-1">${{ formatNumber(balances.equity) }}</p>
          </div>

          <div class="p-5 rounded-2xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-900/40">
            <p class="text-xs font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider">Total Revenue</p>
            <p class="text-2xl font-extrabold text-purple-950 dark:text-purple-100 mt-1">${{ formatNumber(balances.revenue) }}</p>
          </div>

          <div class="p-5 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/40">
            <p class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Total Expenses</p>
            <p class="text-2xl font-extrabold text-amber-950 dark:text-amber-100 mt-1">${{ formatNumber(balances.expenses) }}</p>
          </div>

          <div class="p-5 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-900/40">
            <p class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Net Income</p>
            <p class="text-2xl font-extrabold text-indigo-950 dark:text-indigo-100 mt-1">${{ formatNumber(balances.net_income) }}</p>
          </div>
        </div>

        <!-- Accounting Equation Check Card -->
        <div v-if="!loadingBalances && balances.equation_check" class="p-5 rounded-2xl bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 space-y-2">
          <h3 class="text-xs font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider">Accounting Equation Verification</h3>
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs">
            <div>
              <p class="text-slate-500">Assets: <strong class="text-slate-800 dark:text-zinc-200">${{ formatNumber(balances.equation_check.assets) }}</strong></p>
              <p class="text-slate-500">Liabilities + Equity + Net Income: <strong class="text-slate-800 dark:text-zinc-200">${{ formatNumber(balances.equation_check.liabilities_equity_income) }}</strong></p>
            </div>
            <div>
              <span
                :class="balances.equation_check.balanced ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400 border border-rose-200 dark:border-rose-800'"
                class="inline-flex px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider"
              >
                {{ balances.equation_check.balanced ? 'Equation Balanced' : 'Not Balanced' }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create / Edit Account Modal -->
    <Teleport to="body">
      <div v-if="showAccountModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full transition-all duration-200" style="background-color: rgba(0, 0, 0, 0.6) !important; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
        <div class="relative mx-auto border border-slate-200 dark:border-slate-800 w-full max-w-lg shadow-2xl rounded-2xl bg-white dark:bg-[#12141a] text-slate-900 dark:text-slate-100 text-left transition-all duration-300 flex flex-col max-h-[90vh] overflow-hidden z-10">
          <div class="p-6 pb-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-slate-100">{{ editingAccount ? 'Edit Account' : 'Create New Account' }}</h3>
            <button @click="showAccountModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <form @submit.prevent="saveAccount" class="flex flex-col flex-1 min-h-0">
            <div class="flex-1 overflow-y-auto p-6 space-y-4 pr-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Account Code *</label>
                  <input
                    v-model="accountForm.account_code"
                    type="text"
                    required
                    :disabled="!!editingAccount"
                    placeholder="e.g. 1010"
                    class="w-full px-3 py-2 border border-slate-300 dark:border-slate-800 rounded-lg bg-slate-50 dark:bg-zinc-950 text-xs text-slate-900 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:opacity-50"
                  />
                </div>
                <div>
                  <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Account Name *</label>
                  <input
                    v-model="accountForm.account_name"
                    type="text"
                    required
                    placeholder="e.g. Meezan Bank Account"
                    class="w-full px-3 py-2 border border-slate-300 dark:border-slate-800 rounded-lg bg-slate-50 dark:bg-zinc-950 text-xs text-slate-900 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  />
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Account Type *</label>
                  <select
                    v-model="accountForm.account_type"
                    required
                    @change="updateSubtypeOptions"
                    class="w-full px-3 py-2 border border-slate-300 dark:border-slate-800 rounded-lg bg-slate-50 dark:bg-zinc-950 text-xs text-slate-900 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  >
                    <option value="">Select Type</option>
                    <option value="asset">Asset</option>
                    <option value="liability">Liability</option>
                    <option value="equity">Equity</option>
                    <option value="revenue">Revenue</option>
                    <option value="expense">Expense</option>
                  </select>
                </div>
                <div>
                  <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Account Subtype *</label>
                  <select
                    v-model="accountForm.account_subtype"
                    required
                    class="w-full px-3 py-2 border border-slate-300 dark:border-slate-800 rounded-lg bg-slate-50 dark:bg-zinc-950 text-xs text-slate-900 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  >
                    <option value="">Select Subtype</option>
                    <option v-for="st in availableSubtypes" :key="st.value" :value="st.value">
                      {{ st.label }}
                    </option>
                  </select>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Parent Account</label>
                  <select
                    v-model="accountForm.parent_account_id"
                    class="w-full px-3 py-2 border border-slate-300 dark:border-slate-800 rounded-lg bg-slate-50 dark:bg-zinc-950 text-xs text-slate-900 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  >
                    <option value="">No Parent (Top Level)</option>
                    <option v-for="acc in parentAccountOptions" :key="acc.id" :value="acc.id">
                      {{ acc.account_code }} - {{ acc.account_name }}
                    </option>
                  </select>
                </div>
                <div>
                  <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Opening Balance ($)</label>
                  <input
                    v-model.number="accountForm.opening_balance"
                    type="number"
                    step="0.01"
                    placeholder="0.00"
                    class="w-full px-3 py-2 border border-slate-300 dark:border-slate-800 rounded-lg bg-slate-50 dark:bg-zinc-950 text-xs text-slate-900 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  />
                </div>
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Description</label>
                <textarea
                  v-model="accountForm.description"
                  rows="2"
                  placeholder="Optional account description..."
                  class="w-full px-3 py-2 border border-slate-300 dark:border-slate-800 rounded-lg bg-slate-50 dark:bg-zinc-950 text-xs text-slate-900 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                ></textarea>
              </div>

              <div class="flex items-center space-x-4">
                <label class="flex items-center space-x-2 text-xs text-slate-700 dark:text-slate-300">
                  <input v-model="accountForm.is_active" type="checkbox" class="rounded border-slate-300 dark:border-slate-800 text-indigo-600 focus:ring-indigo-500" />
                  <span>Active</span>
                </label>
                <label class="flex items-center space-x-2 text-xs text-slate-700 dark:text-slate-300">
                  <input v-model="accountForm.is_system_account" type="checkbox" :disabled="!!editingAccount" class="rounded border-slate-300 dark:border-slate-800 text-indigo-600 focus:ring-indigo-500 disabled:opacity-50" />
                  <span>System Account</span>
                </label>
              </div>
            </div>

            <div class="flex justify-end space-x-3 p-6 border-t border-slate-200 dark:border-slate-800 shrink-0">
              <button type="button" @click="showAccountModal = false" class="px-4 h-9 bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-200 rounded-lg text-xs font-semibold">Cancel</button>
              <button type="submit" :disabled="savingAccount" class="px-4 h-9 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-sm">
                {{ savingAccount ? 'Saving...' : (editingAccount ? 'Update Account' : 'Create Account') }}
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
import axios from 'axios';
import AccountTreeNode from '../accounting/AccountTreeNode.vue';
import { useToast } from '@/composables/useToast';

export default {
  name: 'BankingAccounts',
  components: { AccountTreeNode },
  setup() {
    const { showToast } = useToast();
    const activeView = ref('tree');
    const selectedAccountType = ref('');
    const searchQuery = ref('');
    const accounts = ref([]);
    const accountTree = ref([]);
    const balances = ref({});
    const loadingAccounts = ref(false);
    const loadingTree = ref(false);
    const loadingBalances = ref(false);
    const showAccountModal = ref(false);
    const editingAccount = ref(null);
    const savingAccount = ref(false);

    const accountTypes = [
      { value: 'asset', label: 'Assets' },
      { value: 'liability', label: 'Liabilities' },
      { value: 'equity', label: 'Equity' },
      { value: 'revenue', label: 'Revenue' },
      { value: 'expense', label: 'Expenses' }
    ];

    const accountSubtypes = {
      asset: [
        { value: 'current_asset', label: 'Current Asset' },
        { value: 'fixed_asset', label: 'Fixed Asset' },
        { value: 'other_asset', label: 'Other Asset' }
      ],
      liability: [
        { value: 'current_liability', label: 'Current Liability' },
        { value: 'long_term_liability', label: 'Long-term Liability' }
      ],
      equity: [
        { value: 'equity', label: 'Equity' }
      ],
      revenue: [
        { value: 'revenue', label: 'Revenue' }
      ],
      expense: [
        { value: 'expense', label: 'Expense' },
        { value: 'cost_of_goods_sold', label: 'Cost of Goods Sold' }
      ]
    };

    const availableSubtypes = ref([]);

    const accountForm = ref({
      account_code: '',
      account_name: '',
      account_type: '',
      account_subtype: '',
      parent_account_id: '',
      opening_balance: 0,
      description: '',
      is_active: true,
      is_system_account: false
    });

    const parentAccountOptions = computed(() => {
      return accounts.value.filter(account =>
        account.account_type === accountForm.value.account_type &&
        account.id !== editingAccount.value?.id
      );
    });

    let searchTimeout;
    const debouncedSearch = () => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        fetchAccounts();
      }, 300);
    };

    const selectTypeFilter = (typeVal) => {
      selectedAccountType.value = typeVal;
      if (activeView.value === 'tree') fetchAccountTree();
      else if (activeView.value === 'list') fetchAccounts();
    };

    const fetchAccountTree = async () => {
      loadingTree.value = true;
      try {
        const params = {};
        if (selectedAccountType.value) params.account_type = selectedAccountType.value;
        const res = await axios.get('/api/accounts/tree/structure', { params });
        accountTree.value = res.data || [];
      } catch (err) {
        showToast('Failed to load account hierarchy tree', 'error');
      } finally {
        loadingTree.value = false;
      }
    };

    const fetchAccounts = async () => {
      loadingAccounts.value = true;
      try {
        const params = { flat: true, per_page: 100 };
        if (selectedAccountType.value) params.account_type = selectedAccountType.value;
        if (searchQuery.value) params.search = searchQuery.value;

        const res = await axios.get('/api/accounts', { params });
        accounts.value = res.data?.data || res.data || [];
      } catch (err) {
        showToast('Failed to load accounts list', 'error');
      } finally {
        loadingAccounts.value = false;
      }
    };

    const fetchBalances = async () => {
      loadingBalances.value = true;
      try {
        const res = await axios.get('/api/accounts/balances/summary');
        balances.value = res.data || {};
      } catch (err) {
        showToast('Failed to load account balances summary', 'error');
      } finally {
        loadingBalances.value = false;
      }
    };

    const openNewAccountModal = () => {
      editingAccount.value = null;
      accountForm.value = {
        account_code: '',
        account_name: '',
        account_type: 'asset',
        account_subtype: 'current_asset',
        parent_account_id: '',
        opening_balance: 0,
        description: '',
        is_active: true,
        is_system_account: false
      };
      updateSubtypeOptions();
      showAccountModal.value = true;
    };

    const editAccount = (account) => {
      editingAccount.value = account;
      accountForm.value = {
        account_code: account.account_code || account.code,
        account_name: account.account_name || account.name,
        account_type: account.account_type,
        account_subtype: account.account_subtype || '',
        parent_account_id: account.parent_account_id || '',
        opening_balance: account.opening_balance || 0,
        description: account.description || '',
        is_active: account.is_active,
        is_system_account: account.is_system_account || account.is_system || false
      };
      updateSubtypeOptions();
      showAccountModal.value = true;
    };

    const updateSubtypeOptions = () => {
      const type = accountForm.value.account_type;
      if (type && accountSubtypes[type]) {
        availableSubtypes.value = accountSubtypes[type];
      } else {
        availableSubtypes.value = [];
      }
    };

    const saveAccount = async () => {
      savingAccount.value = true;
      try {
        if (editingAccount.value) {
          await axios.put(`/api/accounts/${editingAccount.value.id}`, accountForm.value);
          showToast('Account updated successfully');
        } else {
          await axios.post('/api/accounts', accountForm.value);
          showToast('Account created successfully');
        }
        showAccountModal.value = false;
        if (activeView.value === 'tree') fetchAccountTree();
        else fetchAccounts();
        fetchBalances();
      } catch (err) {
        const msg = err.response?.data?.message || 'Failed to save account';
        showToast(msg, 'error');
      } finally {
        savingAccount.value = false;
      }
    };

    const deleteAccount = async (account) => {
      if (confirm(`Are you sure you want to delete account "${account.account_name || account.name}"?`)) {
        try {
          await axios.delete(`/api/accounts/${account.id}`);
          showToast('Account deleted');
          if (activeView.value === 'tree') fetchAccountTree();
          else fetchAccounts();
          fetchBalances();
        } catch (err) {
          const msg = err.response?.data?.message || 'Failed to delete account';
          showToast(msg, 'error');
        }
      }
    };

    const getAccountTypeBadgeClass = (type) => {
      const classes = {
        asset: 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400 border border-blue-200 dark:border-blue-800/40',
        liability: 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40',
        equity: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40',
        revenue: 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-400 border border-purple-200 dark:border-purple-800/40',
        expense: 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40'
      };
      return classes[type] || 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300';
    };

    const formatAccountSubtype = (st) => {
      if (!st) return '-';
      return st.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    };

    const formatNumber = (val) => {
      return Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    onMounted(() => {
      fetchAccountTree();
      fetchAccounts();
      fetchBalances();
    });

    return {
      activeView,
      selectedAccountType,
      searchQuery,
      accounts,
      accountTree,
      balances,
      loadingAccounts,
      loadingTree,
      loadingBalances,
      showAccountModal,
      editingAccount,
      savingAccount,
      accountTypes,
      availableSubtypes,
      accountForm,
      parentAccountOptions,
      debouncedSearch,
      selectTypeFilter,
      fetchAccountTree,
      fetchAccounts,
      fetchBalances,
      openNewAccountModal,
      editAccount,
      updateSubtypeOptions,
      saveAccount,
      deleteAccount,
      getAccountTypeBadgeClass,
      formatAccountSubtype,
      formatNumber,
    };
  },
};
</script>
