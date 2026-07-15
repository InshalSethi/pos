<template>
  <div class="w-full mx-auto py-8 px-4 sm:px-6 lg:px-8 bg-slate-50/50 dark:bg-zinc-950 min-h-screen font-sans">
    <div class="w-full max-w-7xl mx-auto space-y-6">
      
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-xl font-extrabold text-slate-800 dark:text-slate-100 flex items-center gap-2">
            <span class="p-2 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-xl">📜</span>
            Inventory Histories
          </h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Master transaction log tracking stock fluctuations across all items, warehouses, and actions.
          </p>
        </div>
      </div>

      <!-- Filters Section -->
      <div class="bg-white dark:bg-[#1E1E1E] border border-slate-100/80 dark:border-[#2E2E2E] rounded-[24px] p-5 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Search & Filter Movements</h3>
          <button @click="resetFilters" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer">
            Reset Filters
          </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
          <!-- Item Search -->
          <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 dark:text-slate-400">Item Search</label>
            <div class="relative">
              <input
                v-model="filters.search"
                type="text"
                placeholder="Name, SKU, or Barcode..."
                class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50/50 dark:bg-zinc-950 border border-slate-200/60 dark:border-[#2E2E2E] focus:border-indigo-500 dark:focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 rounded-xl outline-none text-slate-800 dark:text-slate-200 transition-all duration-150 font-semibold"
                @input="debouncedFetch"
              />
              <span class="absolute left-2.5 top-2 text-gray-400 dark:text-slate-400">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
              </span>
            </div>
          </div>

          <!-- Warehouse Filter -->
          <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 dark:text-slate-400">Warehouse</label>
            <select
              v-model="filters.warehouse_id"
              class="w-full px-3 py-1.5 text-xs bg-slate-50/50 dark:bg-zinc-950 border border-slate-200/60 dark:border-[#2E2E2E] focus:border-indigo-500 dark:focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 rounded-xl outline-none text-slate-800 dark:text-slate-200 transition-all duration-150 font-semibold cursor-pointer"
              @change="fetchHistory"
            >
              <option value="">All Warehouses</option>
              <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">
                {{ wh.name }}
              </option>
            </select>
          </div>

          <!-- Transaction Type -->
          <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 dark:text-slate-400">Movement Type</label>
            <select
              v-model="filters.type"
              class="w-full px-3 py-1.5 text-xs bg-slate-50/50 dark:bg-zinc-950 border border-slate-200/60 dark:border-[#2E2E2E] focus:border-indigo-500 dark:focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 rounded-xl outline-none text-slate-800 dark:text-slate-200 transition-all duration-150 font-semibold cursor-pointer"
              @change="fetchHistory"
            >
              <option value="">All Types</option>
              <option value="Invoice">Invoice (Sale)</option>
              <option value="Bill">Bill (PO Delivery)</option>
              <option value="Transfer Order">Transfer Order</option>
              <option value="Manual Adjustment">Manual Adjustment</option>
            </select>
          </div>

          <!-- Start Date -->
          <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 dark:text-slate-400">Start Date</label>
            <input
              v-model="filters.start_date"
              type="date"
              class="w-full px-3 py-1.5 text-xs bg-slate-50/50 dark:bg-zinc-950 border border-slate-200/60 dark:border-[#2E2E2E] focus:border-indigo-500 dark:focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 rounded-xl outline-none text-slate-800 dark:text-slate-200 transition-all duration-150 font-semibold"
              @change="fetchHistory"
            />
          </div>

          <!-- End Date -->
          <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 dark:text-slate-400">End Date</label>
            <input
              v-model="filters.end_date"
              type="date"
              class="w-full px-3 py-1.5 text-xs bg-slate-50/50 dark:bg-zinc-950 border border-slate-200/60 dark:border-[#2E2E2E] focus:border-indigo-500 dark:focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 rounded-xl outline-none text-slate-800 dark:text-slate-200 transition-all duration-150 font-semibold"
              @change="fetchHistory"
            />
          </div>
        </div>
      </div>

      <!-- Master Timeline Log -->
      <div class="bg-white dark:bg-[#1E1E1E] border border-slate-100/80 dark:border-[#2E2E2E] rounded-[24px] overflow-hidden shadow-xs">
        <div class="p-5 border-b border-slate-100 dark:border-[#2E2E2E] flex items-center justify-between">
          <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">All-in-One Activity Timeline</h3>
          <span class="px-2.5 py-0.5 bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold rounded-full">
            {{ pagination.total }} Actions Tracked
          </span>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="p-16 text-center space-y-3">
          <div class="inline-block w-8 h-8 rounded-full border-4 border-indigo-600 border-t-transparent animate-spin"></div>
          <p class="text-xs text-gray-400 font-medium dark:text-slate-400">Retrieving master inventory logs...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="logs.length === 0" class="p-16 text-center">
          <div class="text-4xl mb-3">📜</div>
          <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300">No Inventory Movements</h3>
          <p class="text-[11px] text-gray-400 mt-1 max-w-xs mx-auto dark:text-slate-400">
            Stock levels haven't fluctuated yet. Make a Sale, approve a Bill, or adjust inventory to record movements.
          </p>
        </div>

        <!-- Log Table -->
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-100 dark:divide-[#2E2E2E]">
            <thead class="bg-slate-50 dark:bg-[#252525]">
              <tr>
                <th class="px-6 py-3.5 text-left text-[9px] font-black text-slate-400 uppercase tracking-wider dark:text-slate-400">Date & Time</th>
                <th class="px-6 py-3.5 text-left text-[9px] font-black text-slate-400 uppercase tracking-wider dark:text-slate-400">Item Name</th>
                <th class="px-6 py-3.5 text-left text-[9px] font-black text-slate-400 uppercase tracking-wider dark:text-slate-400">Warehouse</th>
                <th class="px-6 py-3.5 text-center text-[9px] font-black text-slate-400 uppercase tracking-wider dark:text-slate-400">Qty Change</th>
                <th class="px-6 py-3.5 text-center text-[9px] font-black text-slate-400 uppercase tracking-wider dark:text-slate-400">Type / Reason</th>
                <th class="px-6 py-3.5 text-left text-[9px] font-black text-slate-400 uppercase tracking-wider dark:text-slate-400">Reference ID</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-[#2E2E2E]/60 bg-white dark:bg-[#1E1E1E] text-xs">
              <tr v-for="log in logs" :key="log.id" class="hover:bg-slate-50/50 dark:hover:bg-[#2D2D2D]/80 transition-colors">
                <!-- Timestamp -->
                <td class="px-6 py-3.5 whitespace-nowrap text-slate-500 dark:text-slate-400 font-medium">
                  {{ formatDateTime(log.created_at) }}
                </td>
                <!-- Item Details -->
                <td class="px-6 py-3.5">
                  <div class="min-w-[150px]">
                    <h4 class="font-bold text-slate-800 dark:text-slate-100">{{ log.product?.name }}</h4>
                    <p class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider mt-0.5 dark:text-slate-400">
                      SKU: {{ log.product?.sku }}
                      <span v-if="log.variation"> | {{ log.variation.variation_name_string }}</span>
                    </p>
                  </div>
                </td>
                <!-- Warehouse -->
                <td class="px-6 py-3.5 text-slate-700 dark:text-slate-300 font-bold">
                  {{ log.warehouse?.name || 'Main Warehouse' }}
                </td>
                <!-- Qty Changed -->
                <td class="px-6 py-3.5 text-center whitespace-nowrap">
                  <span
                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black"
                    :class="log.quantity_changed >= 0
                      ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400'
                      : 'bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400'"
                  >
                    {{ log.quantity_changed >= 0 ? '+' : '' }}{{ log.quantity_changed }}
                  </span>
                </td>
                <!-- Type / Reason -->
                <td class="px-6 py-3.5 text-center whitespace-nowrap">
                  <span
                    class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider"
                    :class="getTypeBadgeClass(log.type)"
                  >
                    {{ log.type }}
                  </span>
                </td>
                <!-- Reference ID -->
                <td class="px-6 py-3.5 text-slate-500 dark:text-slate-400 font-bold whitespace-nowrap">
                  {{ log.reference_id || 'Manual Update' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Control -->
        <div v-if="pagination.last_page > 1" class="px-6 py-4 bg-slate-50 dark:bg-[#252525] border-t border-slate-100 dark:border-[#2E2E2E] flex items-center justify-between text-xs">
          <span class="text-gray-400 dark:text-slate-400">
            Showing page {{ pagination.current_page }} of {{ pagination.last_page }}
          </span>
          
          <div class="flex items-center gap-1.5">
            <button
              @click="goToPage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="px-3 py-1.5 rounded-lg border border-slate-200/60 dark:border-[#2E2E2E] bg-white dark:bg-[#1E1E1E] disabled:opacity-50 text-slate-700 dark:text-slate-300 font-bold cursor-pointer transition-all hover:bg-slate-50"
            >
              Prev
            </button>
            
            <button
              @click="goToPage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="px-3 py-1.5 rounded-lg border border-slate-200/60 dark:border-[#2E2E2E] bg-white dark:bg-[#1E1E1E] disabled:opacity-50 text-slate-700 dark:text-slate-300 font-bold cursor-pointer transition-all hover:bg-slate-50"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Filters & Directory Lists
const warehouses = ref([]);
const logs = ref([]);
const loading = ref(false);

const filters = ref({
  search: '',
  warehouse_id: '',
  type: '',
  start_date: '',
  end_date: ''
});

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0
});

// Reset filters
const resetFilters = () => {
  filters.value = {
    search: '',
    warehouse_id: '',
    type: '',
    start_date: '',
    end_date: ''
  };
  pagination.value.current_page = 1;
  fetchHistory();
};

// Fetch data
const fetchHistory = async () => {
  loading.value = true;
  try {
    const params = {
      ...filters.value,
      page: pagination.value.current_page
    };
    const res = await axios.get('/api/inventory/histories', { params });
    logs.value = res.data.data;
    
    // Set pagination parameters
    pagination.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      total: res.data.total
    };
  } catch (error) {
    console.error('Failed to load inventory logs:', error);
  } finally {
    loading.value = false;
  }
};

const goToPage = (page) => {
  pagination.value.current_page = page;
  fetchHistory();
};

// Debouncing for keyword search
let debounceTimeout = null;
const debouncedFetch = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    pagination.value.current_page = 1;
    fetchHistory();
  }, 350);
};

// Date Format
const formatDateTime = (val) => {
  if (!val) return '';
  const dt = new Date(val);
  return dt.toLocaleString();
};

// Badge Colors
const getTypeBadgeClass = (type) => {
  switch (type) {
    case 'Invoice':
      return 'bg-purple-55 text-purple-600 dark:bg-purple-950/20 dark:text-purple-400';
    case 'Bill':
      return 'bg-amber-50 text-amber-600 dark:bg-amber-950/20 dark:text-amber-400';
    case 'Transfer Order':
      return 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/20 dark:text-indigo-400';
    case 'Manual Adjustment':
      return 'bg-blue-50 text-blue-600 dark:bg-blue-950/20 dark:text-blue-400';
    default:
      return 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400';
  }
};

onMounted(async () => {
  // Load warehouses first
  try {
    const whRes = await axios.get('/api/warehouses');
    warehouses.value = whRes.data || [];
  } catch (error) {
    console.error('Failed to load warehouses:', error);
  }

  fetchHistory();
});
</script>

<style scoped>
</style>
