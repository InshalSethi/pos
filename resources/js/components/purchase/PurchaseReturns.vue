<template>
  <div class="w-full max-w-full py-8 px-4 sm:px-6 lg:px-8 dark:bg-zinc-950 min-h-screen">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-zinc-100 tracking-tight">Purchase Returns</h1>
        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">Manage and track vendor return notes, refunds, and stock inventory credits</p>
      </div>
      <router-link
        to="/purchase/returns/create"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center space-x-1.5 active:scale-95 cursor-pointer"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span>Create Purchase Return</span>
      </router-link>
    </div>

    <!-- Tabs Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-slate-200 dark:border-zinc-800 mb-6 pb-0.5 space-y-4 sm:space-y-0">
      <div class="flex flex-wrap gap-x-6 gap-y-2">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="setActiveTab(tab.id)"
          class="pb-3 px-1 text-sm font-semibold border-b-2 transition-all flex items-center space-x-2 focus:outline-none relative cursor-pointer"
          :class="activeTab === tab.id ? 'border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-400' : 'border-transparent text-slate-500 dark:text-zinc-400 hover:text-slate-700 dark:hover:text-zinc-200 hover:border-slate-300 dark:hover:border-zinc-600'"
        >
          <span>{{ tab.label }}</span>
          <span
            class="text-[10px] px-1.5 py-0.5 rounded-full font-bold"
            :class="activeTab === tab.id ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-400'"
          >
            {{ counts[tab.id] || 0 }}
          </span>
        </button>
      </div>

      <div class="flex items-center space-x-2">
        <!-- Clear Filters Button -->
        <button
          v-if="searchQuery !== '' || dateFrom !== '' || dateTo !== '' || selectedSupplier !== '' || activeTab !== 'all'"
          @click="clearAllFilters"
          class="inline-flex items-center px-3 py-1.5 border border-rose-200 dark:border-rose-800 text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg text-xs font-semibold shadow-sm transition-all focus:outline-none cursor-pointer"
        >
          <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          Clear Filters
        </button>
      </div>
    </div>

    <!-- Filters Card -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-4 mb-6 shadow-sm grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div>
        <label class="block text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Supplier</label>
        <select
          v-model="selectedSupplier"
          @change="fetchReturns(1)"
          class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 text-slate-700 dark:text-zinc-200"
        >
          <option value="">All Suppliers</option>
          <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
            {{ supplier.name }}
          </option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Date From</label>
        <input
          v-model="dateFrom"
          type="date"
          @change="fetchReturns(1)"
          class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 text-slate-700 dark:text-zinc-200"
        />
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Date To</label>
        <input
          v-model="dateTo"
          type="date"
          @change="fetchReturns(1)"
          class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 text-slate-700 dark:text-zinc-200"
        />
      </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm">
      <div class="flex flex-col sm:flex-row items-center justify-between p-4 border-b border-slate-100 dark:border-zinc-800 gap-4">
        <!-- Search input -->
        <div class="relative w-full sm:w-96">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg class="w-4 h-4 text-slate-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </span>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search return #, PO #, or supplier..."
            class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 text-slate-700 dark:text-zinc-200"
            @input="debouncedSearch"
          />
        </div>

        <!-- Showing selection counts -->
        <div class="flex items-center space-x-2 text-xs text-slate-500 dark:text-zinc-400">
          <span>Showing</span>
          <select
            v-model="perPage"
            @change="handlePerPageChange"
            class="border border-slate-200 dark:border-zinc-700 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-500 cursor-pointer bg-white dark:bg-zinc-800 dark:text-zinc-200"
          >
            <option :value="10">10</option>
            <option :value="15">15</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
          <span>of {{ totalItems }} results</span>
        </div>
      </div>

      <!-- Purchase Returns Table -->
      <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-200 dark:border-zinc-700 text-slate-500 dark:text-zinc-400 uppercase font-bold tracking-wider">
              <th class="py-3 px-4">Return #</th>
              <th class="py-3 px-4">PO Reference</th>
              <th class="py-3 px-4">Supplier</th>
              <th class="py-3 px-4">Return Date</th>
              <th class="py-3 px-4 text-right">Grand Total</th>
              <th class="py-3 px-4 text-center">Status</th>
              <th class="py-3 px-4 text-center">Refund</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
            <tr v-if="loading">
              <td colspan="8" class="py-12 text-center text-slate-400 dark:text-zinc-500">
                <div class="flex justify-center items-center space-x-2">
                  <svg class="animate-spin h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                  <span>Loading purchase returns...</span>
                </div>
              </td>
            </tr>

            <tr v-else-if="returns.length === 0">
              <td colspan="8" class="py-12 text-center text-slate-400 dark:text-zinc-500">
                No purchase returns found. Try adjusting your search filters or create a new return.
              </td>
            </tr>

            <tr
              v-else
              v-for="item in returns"
              :key="item.id"
              class="hover:bg-slate-50/80 dark:hover:bg-zinc-800/40 transition-colors"
            >
              <td class="py-3.5 px-4 font-mono font-bold text-blue-600 dark:text-blue-400">
                <router-link :to="`/purchase/returns/${item.id}`" class="hover:underline">
                  {{ item.return_number }}
                </router-link>
              </td>
              <td class="py-3.5 px-4 font-mono text-slate-600 dark:text-zinc-400">
                <span v-if="item.original_purchase_order || item.purchase_order">
                  {{ (item.original_purchase_order || item.purchase_order).po_number }}
                </span>
                <span v-else class="text-slate-400 italic">Standalone Return</span>
              </td>
              <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-zinc-200">
                {{ item.supplier?.name || 'N/A' }}
              </td>
              <td class="py-3.5 px-4 text-slate-600 dark:text-zinc-400">
                {{ formatDate(item.return_date) }}
              </td>
              <td class="py-3.5 px-4 text-right font-black text-slate-900 dark:text-zinc-100">
                {{ currencySymbol }}{{ formatCurrency(item.total_amount) }}
              </td>
              <td class="py-3.5 px-4 text-center">
                <span :class="getStatusBadgeClass(item.status)" class="px-2.5 py-1 rounded-full text-[11px] font-bold capitalize inline-block">
                  {{ item.status }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-center">
                <span :class="getRefundBadgeClass(item.refund_status)" class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider inline-block">
                  {{ item.refund_status || 'pending' }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-right space-x-2">
                <!-- View -->
                <router-link
                  :to="`/purchase/returns/${item.id}`"
                  class="inline-flex items-center p-1.5 text-slate-500 hover:text-blue-600 dark:text-zinc-400 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded transition-colors"
                  title="View Debit Note"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </router-link>

                <!-- Edit (only for draft/pending) -->
                <router-link
                  v-if="['draft', 'pending'].includes(item.status)"
                  :to="`/purchase/returns/${item.id}/edit`"
                  class="inline-flex items-center p-1.5 text-slate-500 hover:text-emerald-600 dark:text-zinc-400 dark:hover:text-emerald-400 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded transition-colors"
                  title="Edit Return"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </router-link>

                <!-- Approve (if pending/draft) -->
                <button
                  v-if="['draft', 'pending'].includes(item.status)"
                  @click="approveReturn(item)"
                  class="inline-flex items-center p-1.5 text-slate-500 hover:text-blue-600 dark:text-zinc-400 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded transition-colors cursor-pointer"
                  title="Approve & Post Ledger"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </button>

                <!-- Delete (only for draft/pending) -->
                <button
                  v-if="['draft', 'pending'].includes(item.status)"
                  @click="deleteReturn(item.id)"
                  class="inline-flex items-center p-1.5 text-slate-500 hover:text-rose-600 dark:text-zinc-400 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded transition-colors cursor-pointer"
                  title="Delete Return"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div v-if="totalPages > 1" class="flex justify-between items-center p-4 border-t border-slate-100 dark:border-zinc-800">
        <span class="text-xs text-slate-500 dark:text-zinc-400">Page {{ currentPage }} of {{ totalPages }}</span>
        <div class="flex space-x-1">
          <button
            @click="fetchReturns(currentPage - 1)"
            :disabled="currentPage === 1"
            class="px-3 py-1 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 disabled:opacity-50 text-xs font-semibold rounded text-slate-700 dark:text-zinc-200 transition-colors"
          >
            Prev
          </button>
          <button
            @click="fetchReturns(currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="px-3 py-1 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 disabled:opacity-50 text-xs font-semibold rounded text-slate-700 dark:text-zinc-200 transition-colors"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { debounce } from '@/utils/debounce';
import axios from 'axios';

const authStore = useAuthStore();
const currencySymbol = computed(() => authStore.currencySymbol || '$');

// Tabs definition
const tabs = [
  { id: 'all', label: 'All Returns' },
  { id: 'draft', label: 'Draft' },
  { id: 'pending', label: 'Pending' },
  { id: 'approved', label: 'Approved' },
  { id: 'completed', label: 'Completed' },
  { id: 'cancelled', label: 'Cancelled' },
];

const activeTab = ref('all');
const counts = ref({});
const returns = ref([]);
const suppliers = ref([]);
const searchQuery = ref('');
const selectedSupplier = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const loading = ref(false);

const currentPage = ref(1);
const perPage = ref(15);
const totalItems = ref(0);
const totalPages = computed(() => Math.ceil(totalItems.value / perPage.value));

const setActiveTab = (tabId) => {
  activeTab.value = tabId;
  fetchReturns(1);
};

const clearAllFilters = () => {
  searchQuery.value = '';
  selectedSupplier.value = '';
  dateFrom.value = '';
  dateTo.value = '';
  activeTab.value = 'all';
  fetchReturns(1);
};

const debouncedSearch = debounce(() => {
  fetchReturns(1);
}, 300);

const handlePerPageChange = () => {
  fetchReturns(1);
};

const fetchReturns = async (page = 1) => {
  currentPage.value = page;
  loading.value = true;
  try {
    const params = {
      page,
      per_page: perPage.value,
      search: searchQuery.value,
      supplier_id: selectedSupplier.value,
      date_from: dateFrom.value,
      date_to: dateTo.value,
      status: activeTab.value !== 'all' ? activeTab.value : undefined,
    };

    const response = await axios.get('/api/purchase-returns', { params });
    returns.value = response.data.returns?.data || response.data.data || [];
    totalItems.value = response.data.returns?.total || response.data.total || 0;
    counts.value = response.data.status_counts || {};

  } catch (error) {
    console.error('Error fetching purchase returns:', error);
  } finally {
    loading.value = false;
  }
};

const fetchSuppliers = async () => {
  try {
    const response = await axios.get('/api/suppliers');
    suppliers.value = response.data.data || response.data || [];
  } catch (error) {
    console.error('Error fetching suppliers:', error);
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const formatCurrency = (val) => {
  return parseFloat(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'draft':
      return 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300';
    case 'pending':
      return 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50';
    case 'approved':
      return 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border border-blue-200 dark:border-blue-900/50';
    case 'completed':
      return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50';
    case 'cancelled':
      return 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200 dark:border-rose-900/50';
    default:
      return 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300';
  }
};

const getRefundBadgeClass = (status) => {
  switch (status) {
    case 'refunded':
      return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300';
    case 'partial':
      return 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300';
    default:
      return 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400';
  }
};

const approveReturn = async (returnItem) => {
  if (confirm(`Approve purchase return ${returnItem.return_number}? This will adjust warehouse inventory stock and record general ledger entries.`)) {
    try {
      await axios.post(`/api/purchase-returns/${returnItem.id}/approve`);
      fetchReturns(currentPage.value);
    } catch (error) {
      alert(error.response?.data?.message || 'Failed to approve return');
    }
  }
};

const deleteReturn = async (id) => {
  if (confirm('Are you sure you want to delete this purchase return?')) {
    try {
      await axios.delete(`/api/purchase-returns/${id}`);
      fetchReturns(currentPage.value);
    } catch (error) {
      alert(error.response?.data?.message || 'Failed to delete purchase return');
    }
  }
};

onMounted(() => {
  fetchReturns();
  fetchSuppliers();
});
</script>
