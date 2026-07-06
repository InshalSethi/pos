<template>
  <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 font-sans">
    
    <!-- Breadcrumb & Back -->
    <div class="mb-6">
      <router-link to="/inventory/transfer-orders" class="text-xs font-bold text-slate-400 hover:text-slate-650 flex items-center gap-1">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
        </svg>
        Back to Stock Transfers
      </router-link>
    </div>

    <!-- Error/Success Banner -->
    <div v-if="feedbackMsg" :class="feedbackClass" class="mb-6 p-4 rounded-xl border flex items-center justify-between text-xs font-semibold">
      <span class="flex items-center gap-2">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ feedbackMsg }}
      </span>
      <button @click="feedbackMsg = ''" class="text-current opacity-70 hover:opacity-100">&times;</button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-20 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600 mx-auto"></div>
      <p class="mt-3 text-slate-500 text-sm font-medium">Loading transfer order details...</p>
    </div>

    <div v-else-if="transfer" class="space-y-6">
      
      <!-- Transfer Header Box -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <div class="flex items-center gap-3">
            <span class="text-xs font-bold text-slate-400">STOCK TRANSFER</span>
            <span 
              :class="[
                'px-2.5 py-0.5 text-[9px] font-extrabold uppercase rounded-full tracking-wide',
                getStatusClass(transfer.status)
              ]"
            >
              {{ transfer.status }}
            </span>
          </div>
          <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">#{{ transfer.transfer_number }}</h2>
          <p class="text-xs text-slate-400 mt-0.5">Created on {{ transfer.transfer_date }} by {{ transfer.user?.name || 'Administrator' }}</p>
        </div>

        <!-- Action Bar depending on Status -->
        <div class="flex items-center gap-2">
          <!-- Ship Button -->
          <button 
            v-if="transfer.status === 'draft'"
            type="button" 
            @click="sendTransfer"
            :disabled="actionLoading"
            class="px-5 py-2.5 bg-amber-650 hover:bg-amber-700 text-white rounded-full font-bold text-xs shadow hover:shadow-md transition-all flex items-center gap-1.5 active:scale-95 disabled:opacity-50"
          >
            Ship / Dispatch Stock
          </button>

          <!-- Receive Button -->
          <button 
            v-if="transfer.status === 'sent'"
            type="button" 
            @click="receiveTransfer"
            :disabled="actionLoading"
            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full font-bold text-xs shadow hover:shadow-md transition-all flex items-center gap-1.5 active:scale-95 disabled:opacity-50"
          >
            Mark as Received
          </button>

          <!-- Cancel Button -->
          <button 
            v-if="['draft', 'sent'].includes(transfer.status)"
            type="button" 
            @click="cancelTransfer"
            :disabled="actionLoading"
            class="px-5 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-full font-bold text-xs shadow hover:shadow-md transition-all flex items-center gap-1.5 active:scale-95 disabled:opacity-50"
          >
            Cancel Order
          </button>
        </div>
      </div>

      <!-- Route Mapping & Info -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- From Warehouse -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-2">
          <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Source Location (From)</div>
          <h4 class="text-sm font-extrabold text-slate-900 dark:text-white">{{ transfer.source_warehouse?.name }}</h4>
          <div class="text-xs text-slate-500 font-medium space-y-1">
            <p v-if="transfer.source_warehouse?.address">Address: {{ transfer.source_warehouse?.address }}</p>
            <p v-if="transfer.source_warehouse?.phone">Phone: {{ transfer.source_warehouse?.phone }}</p>
            <p v-if="transfer.source_warehouse?.email">Email: {{ transfer.source_warehouse?.email }}</p>
          </div>
        </div>

        <!-- To Warehouse -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-2">
          <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Destination Location (To)</div>
          <h4 class="text-sm font-extrabold text-slate-900 dark:text-white">{{ transfer.destination_warehouse?.name }}</h4>
          <div class="text-xs text-slate-500 font-medium space-y-1">
            <p v-if="transfer.destination_warehouse?.address">Address: {{ transfer.destination_warehouse?.address }}</p>
            <p v-if="transfer.destination_warehouse?.phone">Phone: {{ transfer.destination_warehouse?.phone }}</p>
            <p v-if="transfer.destination_warehouse?.email">Email: {{ transfer.destination_warehouse?.email }}</p>
          </div>
        </div>
      </div>

      <!-- Items Details Table -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-6 space-y-4">
        <h3 class="text-sm font-extrabold text-slate-800 dark:text-white uppercase tracking-wider">Transferred Items</h3>
        
        <div class="border border-slate-100 dark:border-slate-800 rounded-2xl overflow-hidden">
          <table class="w-full table-auto border-collapse text-xs">
            <thead>
              <tr class="bg-slate-50 dark:bg-slate-850 border-b border-slate-150 dark:border-slate-800 text-[10px] uppercase text-slate-500 font-extrabold">
                <th class="px-6 py-3.5 text-left">Product / Variation Name</th>
                <th class="px-6 py-3.5 text-left w-48">SKU</th>
                <th class="px-6 py-3.5 text-center w-36">Transfer Quantity</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-350">
              <tr v-for="(item, idx) in transfer.items" :key="idx" class="hover:bg-slate-50/40">
                <td class="px-6 py-4">
                  <div class="font-bold text-slate-900 dark:text-white">{{ item.product?.name }}</div>
                  <div v-if="item.variation" class="text-[10px] text-slate-400 font-semibold mt-0.5">{{ item.variation?.variation_name_string }}</div>
                </td>
                <td class="px-6 py-4 font-medium text-slate-500">
                  {{ item.variation?.sku || item.product?.sku || 'N/A' }}
                </td>
                <td class="px-6 py-4 text-center font-extrabold text-slate-900 dark:text-white text-sm">
                  {{ item.quantity }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Notes Section -->
        <div v-if="transfer.notes" class="bg-slate-50 dark:bg-slate-850 rounded-2xl p-4 border border-slate-100 dark:border-slate-800 text-xs text-slate-500 font-medium">
          <span class="font-extrabold uppercase tracking-wider text-[9px] text-slate-400 block mb-1">Notes / Instructions</span>
          {{ transfer.notes }}
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();

const transfer = ref(null);
const loading = ref(false);
const actionLoading = ref(false);

const feedbackMsg = ref('');
const feedbackClass = ref('');

const getStatusClass = (status) => {
  switch (status) {
    case 'draft':
      return 'bg-slate-150 text-slate-800 dark:bg-slate-800 dark:text-slate-400';
    case 'sent':
      return 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200/10';
    case 'received':
      return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/10';
    case 'cancelled':
      return 'bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-400';
    default:
      return 'bg-slate-100 text-slate-850';
  }
};

const fetchTransferDetails = async () => {
  loading.value = true;
  try {
    const res = await axios.get(`/api/transfer-orders/${route.params.id}`);
    transfer.value = res.data;
  } catch (err) {
    console.error('Failed to load transfer order:', err);
    showFeedback('Failed to load transfer details.', 'bg-rose-50 text-rose-800 border-rose-200');
  } finally {
    loading.value = false;
  }
};

const sendTransfer = async () => {
  if (!confirm('Ship this transfer order? This will deduct the stock from the source warehouse immediately.')) return;
  actionLoading.value = true;
  try {
    const res = await axios.post(`/api/transfer-orders/${transfer.value.id}/send`);
    showFeedback(res.data.message || 'Transfer order shipped.', 'bg-emerald-50 text-emerald-800 border-emerald-200');
    fetchTransferDetails();
  } catch (err) {
    console.error(err);
    const msg = err.response?.data?.message || 'Failed to ship transfer order.';
    showFeedback(msg, 'bg-rose-50 text-rose-800 border-rose-200');
  } finally {
    actionLoading.value = false;
  }
};

const receiveTransfer = async () => {
  if (!confirm('Mark this transfer order as RECEIVED? This will add the items to the destination warehouse.')) return;
  actionLoading.value = true;
  try {
    const res = await axios.post(`/api/transfer-orders/${transfer.value.id}/receive`);
    showFeedback(res.data.message || 'Transfer order received.', 'bg-emerald-50 text-emerald-800 border-emerald-200');
    fetchTransferDetails();
  } catch (err) {
    console.error(err);
    const msg = err.response?.data?.message || 'Failed to receive stock.';
    showFeedback(msg, 'bg-rose-50 text-rose-800 border-rose-200');
  } finally {
    actionLoading.value = false;
  }
};

const cancelTransfer = async () => {
  if (!confirm('Cancel this transfer order? If it was already sent, stock will be refunded back to the source warehouse.')) return;
  actionLoading.value = true;
  try {
    const res = await axios.post(`/api/transfer-orders/${transfer.value.id}/cancel`);
    showFeedback(res.data.message || 'Transfer order cancelled.', 'bg-emerald-50 text-emerald-800 border-emerald-200');
    fetchTransferDetails();
  } catch (err) {
    console.error(err);
    const msg = err.response?.data?.message || 'Failed to cancel transfer order.';
    showFeedback(msg, 'bg-rose-50 text-rose-800 border-rose-200');
  } finally {
    actionLoading.value = false;
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
  fetchTransferDetails();
});
</script>
