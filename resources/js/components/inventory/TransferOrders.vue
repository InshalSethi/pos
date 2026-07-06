<template>
  <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 font-sans">
    
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div class="flex items-center gap-3">
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Stock Transfers</h1>
        <span class="bg-indigo-100 text-indigo-800 dark:bg-indigo-900/55 dark:text-indigo-300 text-xs px-2.5 py-1 rounded-full font-bold">
          {{ transfers.length }} Orders
        </span>
      </div>

      <div class="flex items-center gap-3">
        <router-link 
          to="/inventory/transfer-orders/create" 
          class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-full shadow-sm hover:shadow transition-all flex items-center gap-2 active:scale-95"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
          </svg>
          New Stock Transfer
        </router-link>
      </div>
    </div>

    <!-- Search Input & Status Filter -->
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
      <div class="relative flex-1">
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="Search by transfer number..." 
          class="w-full pl-12 pr-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm placeholder-slate-400 font-medium"
        />
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>
      <div class="w-full sm:w-48">
        <select 
          v-model="statusFilter"
          class="w-full px-3 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm font-medium text-slate-600 dark:text-slate-300"
        >
          <option value="">All Statuses</option>
          <option value="draft">Draft</option>
          <option value="sent">Sent (In Transit)</option>
          <option value="received">Received (Completed)</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
    </div>

    <!-- Feedback Banner -->
    <div v-if="feedbackMsg" :class="feedbackClass" class="mb-6 p-4 rounded-xl border flex items-center justify-between text-sm font-medium transition-all">
      <span class="flex items-center gap-2">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ feedbackMsg }}
      </span>
      <button @click="feedbackMsg = ''" class="text-current opacity-70 hover:opacity-100">&times;</button>
    </div>

    <!-- Transfers List Container -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
      <div v-if="loading" class="text-center py-20">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600 mx-auto"></div>
        <p class="mt-3 text-slate-500 text-sm font-medium">Loading stock transfers...</p>
      </div>

      <div v-else-if="filteredTransfers.length === 0" class="text-center py-16 px-4">
        <div class="w-16 h-16 bg-slate-50 dark:bg-slate-850 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100 dark:border-slate-800">
          <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
          </svg>
        </div>
        <h3 class="text-base font-bold text-slate-800 dark:text-white">No transfer orders found</h3>
        <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto">Create stock transfers to record and track stock moving between warehouses and physical store branches.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full table-auto border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-slate-850 border-b border-slate-200 dark:border-slate-800 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">
              <th class="px-6 py-4 text-left">Transfer Number</th>
              <th class="px-6 py-4 text-left">Route (Source &rarr; Dest)</th>
              <th class="px-6 py-4 text-center">Date</th>
              <th class="px-6 py-4 text-center">Items</th>
              <th class="px-6 py-4 text-center">Status</th>
              <th class="px-6 py-4 text-center w-40">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-355">
            <tr 
              v-for="to in filteredTransfers" 
              :key="to.id"
              class="hover:bg-slate-50/60 dark:hover:bg-slate-850/30 transition-colors cursor-pointer"
              @click="$router.push(`/inventory/transfer-orders/${to.id}`)"
            >
              <td class="px-6 py-4">
                <span class="font-extrabold text-indigo-600 dark:text-indigo-400 text-sm">#{{ to.transfer_number }}</span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <span class="font-bold text-slate-900 dark:text-white">{{ to.source_warehouse?.name }}</span>
                  <span class="text-slate-400">&rarr;</span>
                  <span class="font-bold text-slate-900 dark:text-white">{{ to.destination_warehouse?.name }}</span>
                </div>
              </td>
              <td class="px-6 py-4 text-center text-slate-500 dark:text-slate-400">
                {{ to.transfer_date }}
              </td>
              <td class="px-6 py-4 text-center font-bold text-slate-900 dark:text-white">
                {{ to.items?.length || 0 }} items
              </td>
              <td class="px-6 py-4 text-center">
                <span 
                  :class="[
                    'px-2.5 py-1 text-[9px] font-extrabold uppercase rounded-full tracking-wide',
                    getStatusClass(to.status)
                  ]"
                >
                  {{ to.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-center" @click.stop>
                <div class="flex items-center justify-center gap-1">
                  <!-- Send Action -->
                  <button 
                    v-if="to.status === 'draft'"
                    type="button" 
                    @click="sendTransfer(to.id)"
                    class="px-2 py-1 text-[10px] font-extrabold text-amber-700 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/30 dark:text-amber-400 rounded-lg transition-all"
                    title="Mark as Sent"
                  >
                    Ship
                  </button>

                  <!-- Receive Action -->
                  <button 
                    v-if="to.status === 'sent'"
                    type="button" 
                    @click="receiveTransfer(to.id)"
                    class="px-2 py-1 text-[10px] font-extrabold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/30 dark:text-emerald-400 rounded-lg transition-all"
                    title="Mark as Received"
                  >
                    Receive
                  </button>

                  <!-- Cancel Action -->
                  <button 
                    v-if="['draft', 'sent'].includes(to.status)"
                    type="button" 
                    @click="cancelTransfer(to.id)"
                    class="px-2 py-1 text-[10px] font-extrabold text-rose-700 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/30 dark:text-rose-400 rounded-lg transition-all"
                    title="Cancel Transfer"
                  >
                    Cancel
                  </button>

                  <!-- Delete Action -->
                  <button 
                    v-if="to.status === 'draft'"
                    type="button" 
                    @click="deleteTransfer(to.id)"
                    class="p-1 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-all focus:outline-none"
                    title="Delete Draft"
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

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const transfers = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const statusFilter = ref('');

// Feedback banners
const feedbackMsg = ref('');
const feedbackClass = ref('');

const filteredTransfers = computed(() => {
  let list = transfers.value;

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim();
    list = list.filter(t => t.transfer_number.toLowerCase().includes(q));
  }

  if (statusFilter.value) {
    list = list.filter(t => t.status === statusFilter.value);
  }

  return list;
});

const getStatusClass = (status) => {
  switch (status) {
    case 'draft':
      return 'bg-slate-100 text-slate-800 dark:bg-slate-850 dark:text-slate-400';
    case 'sent':
      return 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-400 border border-amber-200/20';
    case 'received':
      return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-200/20';
    case 'cancelled':
      return 'bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-400';
    default:
      return 'bg-slate-100 text-slate-850';
  }
};

const fetchTransfers = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/transfer-orders');
    transfers.value = res.data || [];
  } catch (err) {
    console.error('Failed to fetch transfers:', err);
    showFeedback('Failed to load transfer history.', 'bg-rose-50 text-rose-800 border-rose-200');
  } finally {
    loading.value = false;
  }
};

const sendTransfer = async (id) => {
  if (!confirm('Mark this transfer order as SENT? This will deduct the stock from the source warehouse immediately.')) return;
  try {
    const res = await axios.post(`/api/transfer-orders/${id}/send`);
    showFeedback(res.data.message || 'Transfer order shipped.', 'bg-emerald-50 text-emerald-800 border-emerald-200');
    fetchTransfers();
  } catch (err) {
    console.error(err);
    const msg = err.response?.data?.message || 'Failed to ship transfer order.';
    showFeedback(msg, 'bg-rose-50 text-rose-800 border-rose-200');
  }
};

const receiveTransfer = async (id) => {
  if (!confirm('Mark this transfer order as RECEIVED? This will add the items to the destination warehouse.')) return;
  try {
    const res = await axios.post(`/api/transfer-orders/${id}/receive`);
    showFeedback(res.data.message || 'Transfer order received.', 'bg-emerald-50 text-emerald-800 border-emerald-200');
    fetchTransfers();
  } catch (err) {
    console.error(err);
    const msg = err.response?.data?.message || 'Failed to receive stock.';
    showFeedback(msg, 'bg-rose-50 text-rose-800 border-rose-200');
  }
};

const cancelTransfer = async (id) => {
  if (!confirm('Are you sure you want to cancel this transfer order? If it was already sent, stock will be refunded back to the source warehouse.')) return;
  try {
    const res = await axios.post(`/api/transfer-orders/${id}/cancel`);
    showFeedback(res.data.message || 'Transfer order cancelled.', 'bg-emerald-50 text-emerald-800 border-emerald-200');
    fetchTransfers();
  } catch (err) {
    console.error(err);
    const msg = err.response?.data?.message || 'Failed to cancel transfer order.';
    showFeedback(msg, 'bg-rose-50 text-rose-850 border-rose-200');
  }
};

const deleteTransfer = async (id) => {
  if (!confirm('Delete this draft transfer order permanently?')) return;
  try {
    await axios.delete(`/api/transfer-orders/${id}`);
    showFeedback('Draft transfer order deleted.', 'bg-emerald-50 text-emerald-800 border-emerald-200');
    fetchTransfers();
  } catch (err) {
    console.error(err);
    showFeedback('Failed to delete draft transfer order.', 'bg-rose-50 text-rose-800 border-rose-200');
  }
};

const showFeedback = (msg, cls) => {
  feedbackMsg.value = msg;
  feedbackClass.value = cls;
  setTimeout(() => {
    if (feedbackMsg.value === msg) {
      feedbackMsg.value = '';
    }
  }, 5000);
};

onMounted(() => {
  fetchTransfers();
});
</script>
