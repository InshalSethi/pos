<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 space-y-6">
    <!-- Header with Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <h1 class="text-2xl font-bold text-slate-900 dark:text-zinc-100 tracking-tight">Journal Entries</h1>
      
      <div class="flex items-center space-x-3">
        <select
          v-model="selectedStatus"
          @change="fetchJournalEntries"
          class="px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl text-xs font-medium text-slate-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
        >
          <option value="">All Status</option>
          <option value="draft">Draft</option>
          <option value="posted">Posted</option>
          <option value="reversed">Reversed</option>
        </select>

        <div class="relative w-64">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search entries..."
            @input="debouncedJournalSearch"
            class="w-full pl-9 pr-4 py-2 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
          />
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>

        <button
          @click="openNewJournalModal"
          class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all cursor-pointer"
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
              <td class="py-4 px-5 text-right font-extrabold text-slate-900 dark:text-zinc-100">${{ formatNumber(entry.total_debit) }}</td>
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
              <td class="py-4 px-5 text-right space-x-2 font-medium">
                <button @click="viewJournalEntry(entry)" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">View</button>
                <button
                  v-if="entry.status === 'posted'"
                  @click="reverseJournalEntry(entry)"
                  class="text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 ml-2"
                >
                  Reverse
                </button>
              </td>
            </tr>
          </tbody>
        </table>
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
                        <th class="p-2.5 w-28 text-right">Debit ($)</th>
                        <th class="p-2.5 w-28 text-right">Credit ($)</th>
                        <th class="p-2.5 w-8"></th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                      <tr v-for="(line, idx) in form.lines" :key="idx">
                        <td class="p-2">
                          <select v-model="line.account_id" required class="w-full px-2 py-1.5 border border-slate-800 rounded bg-zinc-950 text-xs text-zinc-200">
                            <option :value="null">-- Select Account --</option>
                            <option v-for="acc in coaAccounts" :key="acc.id" :value="acc.id">
                              {{ acc.account_code }} - {{ acc.account_name }} ({{ acc.account_type }})
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
                        <td class="p-2.5 text-right" :class="isBalanced ? 'text-emerald-400' : 'text-rose-400'">${{ formatNumber(totalDebits) }}</td>
                        <td class="p-2.5 text-right" :class="isBalanced ? 'text-emerald-400' : 'text-rose-400'">${{ formatNumber(totalCredits) }}</td>
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
      <div v-if="showViewModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full transition-all duration-200" style="background-color: rgba(0, 0, 0, 0.75) !important; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
        <div class="relative mx-auto border border-slate-800 w-full max-w-2xl shadow-2xl rounded-2xl bg-[#12141a] text-slate-100 text-left transition-all duration-300 p-6 space-y-4">
          <div class="flex justify-between items-center border-b border-slate-800 pb-3">
            <div>
              <h3 class="text-base font-bold text-slate-100">Journal Entry #{{ selectedEntry?.entry_number }}</h3>
              <p class="text-xs text-slate-400 mt-0.5">Date: {{ formatDate(selectedEntry?.entry_date) }}</p>
            </div>
            <button @click="showViewModal = false" class="text-slate-400 hover:text-slate-200 p-1">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <div class="space-y-2 text-xs">
            <p><span class="font-semibold text-slate-400">Description:</span> {{ selectedEntry?.description }}</p>
            <p v-if="selectedEntry?.reference"><span class="font-semibold text-slate-400">Reference:</span> {{ selectedEntry?.reference }}</p>
          </div>

          <div class="border border-slate-800 rounded-xl overflow-hidden">
            <table class="w-full text-left text-xs">
              <thead class="bg-zinc-950 text-slate-400 border-b border-slate-800 text-[10px] uppercase font-bold">
                <tr>
                  <th class="p-2.5">COA Account</th>
                  <th class="p-2.5 text-right">Debit ($)</th>
                  <th class="p-2.5 text-right">Credit ($)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800">
                <tr v-for="line in (selectedEntry?.journal_entry_lines || selectedEntry?.lines || [])" :key="line.id">
                  <td class="p-2.5 font-medium text-slate-200">
                    {{ line.account ? `${line.account.account_code} - ${line.account.account_name}` : `Account #${line.account_id}` }}
                  </td>
                  <td class="p-2.5 text-right font-semibold text-emerald-400">${{ formatNumber(line.debit || line.debit_amount) }}</td>
                  <td class="p-2.5 text-right font-semibold text-indigo-400">${{ formatNumber(line.credit || line.credit_amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex justify-end pt-2">
            <button @click="showViewModal = false" class="px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-200 rounded-lg text-xs font-semibold">Close</button>
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

export default {
  name: 'BankingManualJournals',
  setup() {
    const { showToast } = useToast();
    const journalEntries = ref([]);
    const coaAccounts = ref([]);
    const loading = ref(true);
    const submitting = ref(false);
    const selectedStatus = ref('');
    const searchQuery = ref('');
    const showModal = ref(false);
    const showViewModal = ref(false);
    const editingEntry = ref(null);
    const selectedEntry = ref(null);

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
        fetchJournalEntries();
      }, 300);
    };

    const fetchJournalEntries = async () => {
      loading.value = true;
      try {
        const params = { per_page: 50 };
        if (selectedStatus.value) params.status = selectedStatus.value;
        if (searchQuery.value) params.search = searchQuery.value;

        const response = await axios.get('/api/journal-entries', { params });
        journalEntries.value = response.data?.data || response.data || [];
      } catch (error) {
        showToast('Failed to load journal entries', 'error');
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
      fetchJournalEntries();
      fetchCoaAccounts();
    });

    return {
      journalEntries,
      coaAccounts,
      loading,
      submitting,
      selectedStatus,
      searchQuery,
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
    };
  },
};
</script>
