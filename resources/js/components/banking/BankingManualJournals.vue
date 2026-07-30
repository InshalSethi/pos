<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 space-y-6">
    <!-- Header with Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <h1 class="text-2xl font-bold text-slate-900 dark:text-zinc-100 tracking-tight">Journal Entries</h1>
      
      <div class="flex items-center space-x-3">
        <div class="relative w-64">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search entries..."
            @input="debouncedJournalSearch"
            class="w-full pl-9 pr-4 py-2 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl text-xs font-normal text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
          />
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>

        <div class="w-40">
          <CustomFloatingSelect
            v-model="selectedStatus"
            :options="statusOptions"
            placeholder="All Status"
            @change="handleStatusChange"
          />
        </div>

        <button
          v-if="isFilterActive"
          type="button"
          @click="handleClearFilters"
          class="px-3 py-2 text-xs font-normal text-slate-600 dark:text-zinc-300 border border-slate-200 dark:border-zinc-800 rounded-xl hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 dark:hover:bg-rose-950/40 transition-all flex items-center gap-1.5 shadow-sm cursor-pointer shrink-0"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          <span>Clear</span>
        </button>

        <button
          @click="openNewJournalModal"
          class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all cursor-pointer shrink-0"
        >
          + New Entry
        </button>
      </div>
    </div>

    <!-- Journal Entries Table Card -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-200 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-950/50 text-[11px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">
              <th class="py-3.5 px-5">Entry #</th>
              <th class="py-3.5 px-5">Date</th>
              <th class="py-3.5 px-5">Description</th>
              <th class="py-3.5 px-5 text-center">Type</th>
              <th class="py-3.5 px-5 text-right">Amount</th>
              <th class="py-3.5 px-5 text-center">Status</th>
              <th class="py-3.5 px-5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-xs">
            <tr v-if="loading" class="text-center py-6">
              <td colspan="7" class="py-8 text-slate-400 dark:text-zinc-500">Loading journal entries...</td>
            </tr>
            <tr v-else-if="journalEntries.length === 0" class="text-center py-6">
              <td colspan="7" class="py-8 text-slate-400 dark:text-zinc-500">No journal entries found.</td>
            </tr>
            <tr
              v-for="entry in journalEntries"
              :key="entry.id"
              class="hover:bg-slate-50/80 dark:hover:bg-zinc-800/40 transition-colors"
            >
              <td class="py-4 px-5 font-mono font-bold text-slate-800 dark:text-zinc-100">{{ entry.entry_number }}</td>
              <td class="py-4 px-5 text-slate-600 dark:text-zinc-300">{{ formatDate(entry.entry_date) }}</td>
              <td class="py-4 px-5 text-slate-800 dark:text-zinc-200 max-w-sm">
                <div class="font-semibold">{{ entry.description }}</div>
                <div v-if="entry.reference" class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5">Ref: {{ entry.reference }}</div>
              </td>
              <td class="py-4 px-5 text-center">
                <span
                  :class="entry.entry_type === 'automatic' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40' : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/40'"
                  class="inline-flex px-3 py-0.5 rounded-full text-[10px] font-bold capitalize"
                >
                  {{ entry.entry_type === 'automatic' ? 'Automatic' : 'Manual' }}
                </span>
              </td>
              <td class="py-4 px-5 text-right font-extrabold text-slate-900 dark:text-zinc-100">{{ companyCurrencySymbol }} {{ formatNumber(entry.total_debit) }}</td>
              <td class="py-4 px-5 text-center">
                <span
                  :class="{
                    'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40': entry.status === 'posted',
                    'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40': entry.status === 'draft',
                    'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40': entry.status === 'reversed'
                  }"
                  class="inline-flex px-3 py-0.5 rounded-full text-[10px] font-bold capitalize"
                >
                  {{ entry.status }}
                </span>
              </td>
              <td class="py-4 px-5 text-right space-x-1 font-medium">
                <button
                  @click="viewJournalEntry(entry)"
                  title="View"
                  class="p-1.5 inline-flex items-center justify-center text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 rounded-lg transition-colors cursor-pointer"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
                <button
                  v-if="entry.status === 'posted'"
                  @click="reverseJournalEntry(entry)"
                  title="Reverse"
                  class="p-1.5 inline-flex items-center justify-center text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors cursor-pointer"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination & Per-Page Footer -->
      <div v-if="!loading && pagination.total > 0" class="px-6 py-3.5 border-t border-slate-200 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-950/50 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
        <div class="flex flex-wrap items-center gap-4 text-slate-500 dark:text-zinc-400">
          <span>
            Showing <span class="font-bold text-slate-800 dark:text-zinc-200">{{ pagination.from || 0 }}</span> to
            <span class="font-bold text-slate-800 dark:text-zinc-200">{{ pagination.to || 0 }}</span> of
            <span class="font-bold text-slate-800 dark:text-zinc-200">{{ pagination.total || 0 }}</span> entries
          </span>

          <div class="flex items-center space-x-2">
            <span class="text-slate-500 dark:text-zinc-400 font-medium">Rows:</span>
            <div class="w-24">
              <CustomFloatingSelect
                v-model="perPage"
                :options="perPageOptions"
                placement="top"
                @change="handlePerPageChange"
              />
            </div>
          </div>
        </div>

        <div class="flex items-center space-x-1.5" v-if="pagination.last_page > 1">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="px-2.5 py-1.5 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer"
            title="Previous Page"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>

          <button
            v-for="page in visiblePages"
            :key="page"
            @click="changePage(page)"
            :class="[
              'px-3 py-1.5 rounded-xl font-semibold transition-colors cursor-pointer',
              page === pagination.current_page
                ? 'bg-indigo-600 text-white shadow-sm'
                : 'border border-slate-200 dark:border-zinc-800 text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800'
            ]"
          >
            {{ page }}
          </button>

          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="px-2.5 py-1.5 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer"
            title="Next Page"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- New / Edit Journal Entry Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full transition-all duration-200" style="background-color: rgba(0, 0, 0, 0.75) !important; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
        <div class="relative mx-auto border border-slate-800 w-full max-w-3xl shadow-2xl rounded-2xl bg-[#12141a] text-slate-100 text-left transition-all duration-300 flex flex-col max-h-[90vh] overflow-hidden z-10">
          <div class="p-6 pb-4 border-b border-slate-800 flex justify-between items-center">
            <h3 class="text-sm font-bold uppercase tracking-wider">{{ editingEntry ? 'Edit Journal Entry' : 'New Journal Entry' }}</h3>
            <button @click="showModal = false" class="text-slate-400 hover:text-slate-200 p-1 rounded-lg">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <form @submit.prevent="saveJournalEntry" class="flex flex-col flex-1 min-h-0">
            <div class="flex-1 overflow-y-auto p-6 space-y-4 pr-4">
              <div class="grid grid-cols-3 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Entry Date *</label>
                  <input v-model="form.entry_date" type="date" required class="w-full px-3 py-2 border border-slate-800 rounded-lg bg-zinc-950 text-xs text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                </div>
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Reference Number</label>
                  <input v-model="form.reference" type="text" placeholder="e.g. REF-1002" class="w-full px-3 py-2 border border-slate-800 rounded-lg bg-zinc-950 text-xs text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                </div>
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Entry Type</label>
                  <select v-model="form.entry_type" class="w-full px-3 py-2 border border-slate-800 rounded-lg bg-zinc-950 text-xs text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="manual">Manual Entry</option>
                    <option value="adjustment">Adjustment Entry</option>
                    <option value="closing">Closing Entry</option>
                  </select>
                </div>
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Description *</label>
                <input v-model="form.description" type="text" required placeholder="Description of the journal entry..." class="w-full px-3 py-2 border border-slate-800 rounded-lg bg-zinc-950 text-xs text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
              </div>

              <!-- Lines Table -->
              <div>
                <div class="flex items-center justify-between mb-2">
                  <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Journal Lines (Debits & Credits)</label>
                  <button type="button" @click="addLine" class="text-xs text-emerald-400 hover:text-emerald-300 font-semibold">+ Add Line</button>
                </div>
                <div class="border border-slate-800 rounded-xl overflow-hidden">
                  <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-950 text-slate-400 border-b border-slate-800 text-[10px] uppercase font-bold">
                      <tr>
                        <th class="p-2.5">COA Account *</th>
                        <th class="p-2.5">Line Description</th>
                        <th class="p-2.5 w-28 text-right">Debit ({{ companyCurrencySymbol }})</th>
                        <th class="p-2.5 w-28 text-right">Credit ({{ companyCurrencySymbol }})</th>
                        <th class="p-2.5 w-8"></th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                      <tr v-for="(line, idx) in form.lines" :key="idx">
                        <td class="p-2">
                          <select v-model="line.account_id" required class="w-full px-2 py-1.5 border border-slate-800 rounded bg-zinc-950 text-xs text-zinc-200">
                            <option :value="null">-- Select Account --</option>
                            <option v-for="acc in coaAccounts" :key="acc.id" :value="acc.id">
                              {{ acc.label }}
                            </option>
                          </select>
                        </td>
                        <td class="p-2">
                          <input v-model="line.description" type="text" placeholder="Line note..." class="w-full px-2 py-1.5 border border-slate-800 rounded bg-zinc-950 text-xs text-zinc-200" />
                        </td>
                        <td class="p-2">
                          <input v-model.number="line.debit_amount" type="number" step="0.01" min="0" class="w-full px-2 py-1.5 border border-slate-800 rounded bg-zinc-950 text-xs text-zinc-200 text-right" @input="line.credit_amount = 0" />
                        </td>
                        <td class="p-2">
                          <input v-model.number="line.credit_amount" type="number" step="0.01" min="0" class="w-full px-2 py-1.5 border border-slate-800 rounded bg-zinc-950 text-xs text-zinc-200 text-right" @input="line.debit_amount = 0" />
                        </td>
                        <td class="p-2 text-center">
                          <button v-if="form.lines.length > 2" type="button" @click="removeLine(idx)" class="text-rose-400 hover:text-rose-300 font-bold">&times;</button>
                        </td>
                      </tr>
                    </tbody>
                    <tfoot class="bg-zinc-950 border-t border-slate-800 font-bold text-xs">
                      <tr>
                        <td colspan="2" class="p-2.5 text-slate-400">Total</td>
                        <td class="p-2.5 text-right" :class="isBalanced ? 'text-emerald-400' : 'text-rose-400'">{{ companyCurrencySymbol }} {{ formatNumber(totalDebits) }}</td>
                        <td class="p-2.5 text-right" :class="isBalanced ? 'text-emerald-400' : 'text-rose-400'">{{ companyCurrencySymbol }} {{ formatNumber(totalCredits) }}</td>
                        <td></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
                <p v-if="!isBalanced" class="text-[11px] text-rose-400 mt-1 font-medium">Total Debits must equal Total Credits to post entry.</p>
              </div>
            </div>

            <div class="flex justify-end space-x-3 p-6 border-t border-slate-800 shrink-0">
              <button type="button" @click="showModal = false" class="px-4 h-9 bg-zinc-800 hover:bg-zinc-700 text-zinc-200 rounded-lg text-xs font-semibold">Cancel</button>
              <button type="submit" :disabled="submitting || !isBalanced" class="px-4 h-9 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-lg text-xs font-semibold">
                {{ submitting ? 'Saving...' : 'Post Entry' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- View Journal Entry Detail Modal -->
    <Teleport to="body">
      <div v-if="showViewModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full transition-all duration-200" style="background-color: rgba(0, 0, 0, 0.6) !important; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
        <div class="relative mx-auto border border-slate-200 dark:border-slate-800 w-full max-w-2xl shadow-2xl rounded-2xl bg-white dark:bg-[#12141a] text-slate-900 dark:text-slate-100 text-left transition-all duration-300 p-6 space-y-4">
          <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-800 pb-3">
            <div>
              <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Journal Entry #{{ selectedEntry?.entry_number }}</h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Date: {{ formatDate(selectedEntry?.entry_date) }}</p>
            </div>
            <button @click="showViewModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <div class="space-y-2 text-xs">
            <p><span class="font-semibold text-slate-500 dark:text-slate-400">Description:</span> <span class="text-slate-800 dark:text-slate-200">{{ selectedEntry?.description }}</span></p>
            <p v-if="selectedEntry?.reference"><span class="font-semibold text-slate-500 dark:text-slate-400">Reference:</span> <span class="text-slate-800 dark:text-slate-200">{{ selectedEntry?.reference }}</span></p>
          </div>

          <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-50 dark:bg-zinc-950 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 text-[10px] uppercase font-bold">
                <tr>
                  <th class="p-2.5">COA Account</th>
                  <th class="p-2.5 text-right">Debit ({{ companyCurrencySymbol }})</th>
                  <th class="p-2.5 text-right">Credit ({{ companyCurrencySymbol }})</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="line in (selectedEntry?.journal_entry_lines || selectedEntry?.lines || [])" :key="line.id">
                  <td class="p-2.5 font-medium text-slate-800 dark:text-slate-200">
                    {{ line.account ? `${line.account.account_code} - ${line.account.account_name}` : `Account #${line.account_id}` }}
                  </td>
                  <td class="p-2.5 text-right font-semibold text-emerald-600 dark:text-emerald-400">{{ companyCurrencySymbol }} {{ formatNumber(line.debit || line.debit_amount) }}</td>
                  <td class="p-2.5 text-right font-semibold text-indigo-600 dark:text-indigo-400">{{ companyCurrencySymbol }} {{ formatNumber(line.credit || line.credit_amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex justify-end pt-2">
            <button @click="showViewModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-200 rounded-lg text-xs font-semibold">Close</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useCurrencyStore } from '@/stores/currency';
import CustomFloatingSelect from '../common/CustomFloatingSelect.vue';

export default {
  name: 'BankingManualJournals',
  components: {
    CustomFloatingSelect,
  },
  setup() {
    const { showToast } = useToast();
    const currencyStore = useCurrencyStore();

    const companyCurrencySymbol = computed(() => {
      return currencyStore.symbol || currencyStore.tenantCurrencyCode || 'PKR';
    });

    const journalEntries = ref([]);
    const coaAccounts = ref([]);
    const loading = ref(true);
    const submitting = ref(false);
    const selectedStatus = ref('ALL');
    const searchQuery = ref('');
    const showModal = ref(false);
    const showViewModal = ref(false);
    const editingEntry = ref(null);
    const selectedEntry = ref(null);

    const statusOptions = [
      { label: 'All Status', value: 'ALL' },
      { label: 'Draft', value: 'draft' },
      { label: 'Posted', value: 'posted' },
      { label: 'Reversed', value: 'reversed' },
    ];

    const isFilterActive = computed(() => {
      const hasSearch = searchQuery.value && searchQuery.value.trim() !== '';
      const hasStatus = selectedStatus.value && selectedStatus.value !== 'ALL' && selectedStatus.value !== '';
      return hasSearch || hasStatus;
    });

    const handleClearFilters = () => {
      searchQuery.value = '';
      selectedStatus.value = 'ALL';
      fetchJournalEntries(1);
    };

    const handleStatusChange = () => {
      fetchJournalEntries(1);
    };

    const perPage = ref(10);
    const perPageOptions = [
      { label: '10', value: 10 },
      { label: '50', value: 50 },
      { label: '100', value: 100 },
    ];

    const pagination = ref({
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0,
      from: 0,
      to: 0,
    });

    const form = ref({
      entry_date: new Date().toISOString().split('T')[0],
      reference: '',
      description: '',
      entry_type: 'manual',
      lines: [
        { account_id: null, description: '', debit_amount: 0, credit_amount: 0 },
        { account_id: null, description: '', debit_amount: 0, credit_amount: 0 },
      ]
    });

    let searchTimeout;
    const debouncedJournalSearch = () => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        fetchJournalEntries(1);
      }, 300);
    };

    const fetchJournalEntries = async (page = 1) => {
      loading.value = true;
      try {
        const params = {
          page: page,
          per_page: perPage.value,
        };
        if (selectedStatus.value && selectedStatus.value !== 'ALL') {
          params.status = selectedStatus.value.toLowerCase();
        }
        if (searchQuery.value && searchQuery.value.trim() !== '') {
          params.search = searchQuery.value.trim();
        }

        const response = await axios.get('/api/journal-entries', { params });
        const resData = response.data;

        if (resData && Array.isArray(resData.data)) {
          journalEntries.value = resData.data;
          pagination.value = {
            current_page: resData.current_page || page,
            last_page: resData.last_page || 1,
            per_page: resData.per_page || perPage.value,
            total: resData.total || resData.data.length,
            from: resData.from || ((page - 1) * perPage.value + 1),
            to: resData.to || Math.min(page * perPage.value, resData.total || resData.data.length)
          };
        } else {
          journalEntries.value = Array.isArray(resData) ? resData : [];
          pagination.value = {
            current_page: 1,
            last_page: 1,
            per_page: perPage.value,
            total: journalEntries.value.length,
            from: journalEntries.value.length ? 1 : 0,
            to: journalEntries.value.length
          };
        }
      } catch (error) {
        showToast('Failed to load journal entries', 'error');
      } finally {
        loading.value = false;
      }
    };

    const changePage = (page) => {
      if (page < 1 || page > pagination.value.last_page || page === pagination.value.current_page) return;
      fetchJournalEntries(page);
    };

    const handlePerPageChange = (val) => {
      perPage.value = Number(val);
      fetchJournalEntries(1);
    };

    const visiblePages = computed(() => {
      const current = pagination.value.current_page || 1;
      const last = pagination.value.last_page || 1;
      const pages = [];
      const start = Math.max(1, current - 2);
      const end = Math.min(last, current + 2);
      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      return pages;
    });

    const fetchCoaAccounts = async () => {
      try {
        const res = await axios.get('/api/accounts', { params: { flat: true, per_page: 200 } });
        let rawList = res.data?.data || res.data || [];
        if (!Array.isArray(rawList)) rawList = [];

        coaAccounts.value = rawList
          .map(acc => {
            const code = String(acc.account_code || acc.code || acc.account_number || '').trim();
            const name = String(acc.account_name || acc.name || acc.title || 'Unnamed Account').trim();
            const type = String(acc.account_type || acc.type || 'Asset').trim();
            return {
              id: acc.id,
              code,
              name,
              type,
              label: code ? `${code} - ${name} (${type.toUpperCase()})` : `${name} (${type.toUpperCase()})`
            };
          })
          .sort((a, b) => a.code.localeCompare(b.code, undefined, { numeric: true }));
      } catch (e) {
        // ignore
      }
    };

    const totalDebits = computed(() => form.value.lines.reduce((sum, l) => sum + (parseFloat(l.debit_amount) || 0), 0));
    const totalCredits = computed(() => form.value.lines.reduce((sum, l) => sum + (parseFloat(l.credit_amount) || 0), 0));
    const isBalanced = computed(() => Math.abs(totalDebits.value - totalCredits.value) < 0.01 && totalDebits.value > 0);

    const router = useRouter();

    const openNewJournalModal = () => {
      router.push('/banking/manual-journals/create');
    };

    const addLine = () => {
      form.value.lines.push({ account_id: null, description: '', debit_amount: 0, credit_amount: 0 });
    };

    const removeLine = (idx) => {
      if (form.value.lines.length > 2) {
        form.value.lines.splice(idx, 1);
      }
    };

    const saveJournalEntry = async () => {
      if (!isBalanced.value) return;
      submitting.value = true;
      try {
        const data = {
          ...form.value,
          status: 'posted',
          total_debit: totalDebits.value,
          total_credit: totalCredits.value,
        };
        if (editingEntry.value) {
          await axios.put(`/api/journal-entries/${editingEntry.value.id}`, data);
          showToast('Journal Entry updated');
        } else {
          await axios.post('/api/journal-entries', data);
          showToast('Journal Entry posted successfully');
        }
        showModal.value = false;
        fetchJournalEntries();
      } catch (err) {
        const msg = err.response?.data?.message || 'Failed to save journal entry';
        showToast(msg, 'error');
      } finally {
        submitting.value = false;
      }
    };

    const viewJournalEntry = async (entry) => {
      try {
        const res = await axios.get(`/api/journal-entries/${entry.id}`);
        selectedEntry.value = res.data;
        showViewModal.value = true;
      } catch (e) {
        selectedEntry.value = entry;
        showViewModal.value = true;
      }
    };

    const reverseJournalEntry = async (entry) => {
      const reason = prompt('Enter reason for reversal:');
      if (reason) {
        try {
          await axios.post(`/api/journal-entries/${entry.id}/reverse`, {
            reason,
            reversal_date: new Date().toISOString().split('T')[0]
          });
          showToast('Journal entry reversed');
          fetchJournalEntries();
        } catch (err) {
          const msg = err.response?.data?.message || 'Reversal failed';
          showToast(msg, 'error');
        }
      }
    };

    const formatDate = (d) => {
      if (!d) return '-';
      return new Date(d).toLocaleDateString('en-US');
    };

    const formatNumber = (val) => {
      return Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    onMounted(() => {
      currencyStore.fetchCurrencies();
      fetchJournalEntries();
      fetchCoaAccounts();
    });

    return {
      companyCurrencySymbol,
      journalEntries,
      coaAccounts,
      loading,
      submitting,
      selectedStatus,
      statusOptions,
      searchQuery,
      isFilterActive,
      handleClearFilters,
      handleStatusChange,
      showModal,
      showViewModal,
      editingEntry,
      selectedEntry,
      form,
      totalDebits,
      totalCredits,
      isBalanced,
      debouncedJournalSearch,
      fetchJournalEntries,
      openNewJournalModal,
      addLine,
      removeLine,
      saveJournalEntry,
      viewJournalEntry,
      reverseJournalEntry,
      formatDate,
      formatNumber,
      perPage,
      perPageOptions,
      pagination,
      changePage,
      handlePerPageChange,
      visiblePages,
    };
  },
};
</script>
