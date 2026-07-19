<template>
  <div class="min-h-screen bg-slate-55 dark:bg-zinc-950 p-4 sm:p-6 lg:p-8">
    <div class="max-w-4xl mx-auto">
      
      <!-- Back Link & Title -->
      <div class="flex items-center justify-between mb-6">
        <button
          @click="goBack"
          class="flex items-center space-x-2 text-xs font-semibold text-slate-500 dark:text-zinc-400 hover:text-slate-700 dark:hover:text-zinc-200 transition-colors bg-transparent border-0 cursor-pointer"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          <span>Back to Orders</span>
        </button>

        <h1 class="text-xl font-black text-slate-800 dark:text-zinc-100 uppercase tracking-wider">
          Receive Goods
        </h1>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col justify-center items-center h-64 space-y-4">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
        <p class="text-xs text-slate-400 dark:text-zinc-500 font-semibold uppercase tracking-wider animate-pulse">Loading order details...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/30 rounded-2xl p-5 text-left">
        <div class="flex items-start space-x-3">
          <svg class="h-5 w-5 text-rose-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <div>
            <h3 class="text-sm font-bold text-rose-800 dark:text-rose-450 uppercase tracking-wider">Error loading purchase order</h3>
            <p class="text-xs text-rose-600 dark:text-rose-400/80 mt-1 font-medium">{{ error }}</p>
            <button
              @click="fetchPurchaseOrder"
              class="mt-4 px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-all border-0 cursor-pointer"
            >
              Retry
            </button>
          </div>
        </div>
      </div>

      <!-- Main Receipt Sheet -->
      <div v-else-if="purchaseOrder" class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-3xl shadow-xl overflow-hidden text-left">
        
        <!-- Header Info Card -->
        <div class="bg-slate-50 dark:bg-zinc-900/50 border-b border-slate-250 dark:border-zinc-800 p-6 sm:p-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <span class="text-[9px] font-extrabold uppercase text-slate-400 dark:text-zinc-500 tracking-wider">Purchase Order Number</span>
              <h2 class="text-xl font-black text-indigo-600 dark:text-indigo-400 mt-1 uppercase">{{ purchaseOrder.po_number }}</h2>
              
              <div class="mt-4 space-y-1">
                <p class="text-xs text-slate-500 dark:text-zinc-400">
                  <span class="font-bold text-slate-400 dark:text-zinc-500">Order Date:</span> {{ formatDate(purchaseOrder.order_date) }}
                </p>
                <p v-if="purchaseOrder.expected_delivery_date" class="text-xs text-slate-500 dark:text-zinc-400">
                  <span class="font-bold text-slate-400 dark:text-zinc-500">Expected Delivery:</span> {{ formatDate(purchaseOrder.expected_delivery_date) }}
                </p>
              </div>
            </div>

            <div class="bg-white dark:bg-zinc-950 border border-slate-100 dark:border-zinc-900 rounded-2xl p-4">
              <span class="text-[9px] font-extrabold uppercase text-slate-400 dark:text-zinc-500 tracking-wider">Supplier details</span>
              <h3 class="text-sm font-bold text-slate-800 dark:text-zinc-200 mt-1">{{ purchaseOrder.supplier?.name }}</h3>
              <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">{{ purchaseOrder.supplier?.company_name || 'N/A' }}</p>
              <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5">{{ purchaseOrder.supplier?.phone || purchaseOrder.supplier?.email || 'No contact details available' }}</p>
            </div>
          </div>
        </div>

        <!-- Table of items -->
        <div class="p-6 sm:p-8">
          <h3 class="text-xs font-bold uppercase text-slate-400 dark:text-zinc-500 tracking-wider mb-4">Items to Receive</h3>
          
          <div class="border border-slate-200 dark:border-zinc-800 rounded-2xl overflow-hidden">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50 dark:bg-zinc-900 border-b border-slate-200 dark:border-zinc-800 text-[10px] font-bold uppercase text-slate-400 dark:text-zinc-500 tracking-wider">
                  <th class="py-3.5 px-4 font-bold">Product / SKU</th>
                  <th class="py-3.5 px-4 text-center font-bold">Ordered</th>
                  <th class="py-3.5 px-4 text-center font-bold">Prev Received</th>
                  <th class="py-3.5 px-4 text-center font-bold">Remaining</th>
                  <th class="py-3.5 px-4 text-right font-bold w-40">Qty to Receive</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-zinc-800 text-xs">
                <tr v-for="item in receiveItems" :key="item.id" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/10 transition-colors">
                  <td class="py-4 px-4 align-middle">
                    <div class="font-bold text-slate-800 dark:text-zinc-200">{{ item.name }}</div>
                    <div class="text-[10px] text-slate-400 dark:text-zinc-500 font-mono mt-0.5">{{ item.sku || 'No SKU' }}</div>
                  </td>
                  <td class="py-4 px-4 text-center font-semibold text-slate-700 dark:text-zinc-300 align-middle">
                    {{ item.quantity_ordered }}
                  </td>
                  <td class="py-4 px-4 text-center font-semibold text-slate-500 dark:text-zinc-400 align-middle">
                    {{ item.quantity_received_prev }}
                  </td>
                  <td class="py-4 px-4 text-center align-middle">
                    <span class="px-2 py-1 rounded-md text-[10px] font-bold" :class="item.remaining > 0 ? 'bg-indigo-50 dark:bg-indigo-950/30 text-indigo-650 dark:text-indigo-400' : 'bg-slate-100 dark:bg-zinc-800 text-slate-400'">
                      {{ item.remaining }}
                    </span>
                  </td>
                  <td class="py-4 px-4 text-right align-middle">
                    <input
                      v-model.number="item.quantity_to_receive"
                      type="number"
                      min="0"
                      :max="item.remaining"
                      :disabled="item.remaining === 0"
                      class="w-full max-w-[120px] px-2.5 py-1.5 text-right border border-slate-300 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 font-semibold bg-white dark:bg-zinc-950 text-slate-800 dark:text-zinc-200 disabled:opacity-50 disabled:bg-slate-50 dark:disabled:bg-zinc-900"
                      @input="validateQty(item)"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Bottom Summary Actions -->
          <div class="flex items-center justify-between mt-8 pt-6 border-t border-slate-150 dark:border-zinc-800">
            <span class="text-xs font-semibold text-slate-400 dark:text-zinc-500">
              Total items being received in this batch: <span class="font-bold text-slate-700 dark:text-zinc-300">{{ totalBatchQty }}</span>
            </span>

            <div class="flex space-x-3">
              <button
                type="button"
                @click="goBack"
                class="px-5 py-2.5 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 rounded-xl text-xs font-semibold transition-all border-0 cursor-pointer"
              >
                Cancel
              </button>
              <button
                type="button"
                :disabled="saving || totalBatchQty === 0"
                @click="submitReceipt"
                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-md transition-all border-0 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2"
              >
                <svg v-if="saving" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ saving ? 'Receiving Goods...' : 'Confirm Receipt' }}</span>
              </button>
            </div>
          </div>

        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from '@/composables/useToast';
import api from '@/services/api';

const route = useRoute();
const router = useRouter();
const { showToast } = useToast();

const purchaseOrder = ref(null);
const receiveItems = ref([]);
const loading = ref(true);
const saving = ref(false);
const error = ref(null);

const fetchPurchaseOrder = async () => {
  try {
    loading.value = true;
    error.value = null;
    const response = await api.get(`/purchase-orders/${route.params.id}`);
    purchaseOrder.value = response.data;
    
    // Map items for receiving
    const items = response.data.purchase_order_items || response.data.purchase_order_items_items || [];
    receiveItems.value = items.map(item => {
      const remaining = item.quantity_ordered - (item.quantity_received || 0);
      return {
        id: item.id,
        name: item.product?.name || 'Unknown Product',
        sku: item.product?.sku || '',
        quantity_ordered: item.quantity_ordered,
        quantity_received_prev: item.quantity_received || 0,
        remaining: Math.max(0, remaining),
        quantity_to_receive: Math.max(0, remaining)
      };
    });
  } catch (err) {
    console.error('Error:', err);
    error.value = err.response?.data?.message || 'Failed to load purchase order details';
    showToast('Error loading purchase order', 'error');
  } finally {
    loading.value = false;
  }
};

const validateQty = (item) => {
  if (item.quantity_to_receive < 0) {
    item.quantity_to_receive = 0;
  }
  if (item.quantity_to_receive > item.remaining) {
    item.quantity_to_receive = item.remaining;
  }
};

const totalBatchQty = computed(() => {
  return receiveItems.value.reduce((total, item) => total + (item.quantity_to_receive || 0), 0);
});

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const goBack = () => {
  router.push('/purchase/orders');
};

const submitReceipt = async () => {
  // Filter only items that are being received in this batch
  const itemsToSubmit = receiveItems.value
    .filter(item => item.quantity_to_receive > 0)
    .map(item => ({
      purchase_order_item_id: item.id,
      quantity_received: item.quantity_to_receive
    }));

  if (itemsToSubmit.length === 0) {
    showToast('Please enter quantity to receive for at least one item', 'warning');
    return;
  }

  saving.value = true;
  try {
    const payload = {
      items: itemsToSubmit
    };
    await api.post(`/purchase-orders/${route.params.id}/receive`, payload);
    showToast('Goods received and stock adjusted successfully', 'success');
    
    setTimeout(() => {
      router.push('/purchase/orders');
    }, 1500);
  } catch (err) {
    console.error('Error submitting goods receipt:', err);
    showToast(err.response?.data?.message || 'Failed to receive goods', 'error');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchPurchaseOrder();
});
</script>

<style scoped>
/* Custom styled styling to match */
button {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

button:hover:not(:disabled) {
  transform: translateY(-0.5px);
}

button:active:not(:disabled) {
  transform: translateY(0.5px);
}

input:focus {
  border-color: #6366f1 !important;
  box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1) !important;
}
</style>
