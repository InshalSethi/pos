<template>
  <div class="w-full mx-auto py-8 px-4 sm:px-6 lg:px-8 bg-slate-50/50 dark:bg-zinc-950 min-h-screen font-sans">
    <div class="w-full max-w-4xl mx-auto">
      
      <!-- Breadcrumb & Title -->
      <div class="mb-6">
        <router-link to="/inventory/transfer-orders" class="text-xs font-bold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center gap-1 mb-2 dark:text-slate-400">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
          </svg>
          Back to Stock Transfers
        </router-link>
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Create Stock Transfer</h1>
      </div>

      <!-- Error Banner -->
      <div v-if="errorMsg" class="mb-6 p-4 bg-rose-50 text-rose-800 border border-rose-200 rounded-xl flex items-center justify-between text-xs font-semibold">
        <span class="flex items-center gap-2">
          <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          {{ errorMsg }}
        </span>
        <button @click="errorMsg = ''" class="text-rose-600 hover:text-rose-900 font-bold text-base">&times;</button>
      </div>

      <form @submit.prevent="submitTransfer" class="space-y-6 text-xs font-semibold text-slate-700 dark:text-slate-300">
        
        <!-- Transfer Configuration Card -->
        <div class="bg-white dark:bg-[#1E1E1E] rounded-3xl border border-slate-200 dark:border-[#2E2E2E] shadow-sm p-6 space-y-4">
          <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider">Transfer Details</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Source Warehouse -->
            <div>
              <label class="text-[10px] font-bold text-slate-400 block mb-1 uppercase tracking-wider dark:text-slate-400">Source Warehouse *</label>
              <select 
                v-model="form.source_warehouse_id"
                @change="onSourceWarehouseChange"
                class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-950 dark:text-slate-100 border border-slate-200 dark:border-[#2E2E2E] rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 font-semibold cursor-pointer"
                required
              >
                <option value="" disabled>Select Source</option>
                <option v-for="wh in warehouses" :key="wh.id" :value="wh.id" :disabled="wh.id === form.destination_warehouse_id">
                  {{ wh.name }}
                </option>
              </select>
            </div>

            <!-- Destination Warehouse -->
            <div>
              <label class="text-[10px] font-bold text-slate-400 block mb-1 uppercase tracking-wider dark:text-slate-400">Destination Warehouse *</label>
              <select 
                v-model="form.destination_warehouse_id"
                class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-950 dark:text-slate-100 border border-slate-200 dark:border-[#2E2E2E] rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 font-semibold cursor-pointer"
                required
              >
                <option value="" disabled>Select Destination</option>
                <option v-for="wh in warehouses" :key="wh.id" :value="wh.id" :disabled="wh.id === form.source_warehouse_id">
                  {{ wh.name }}
                </option>
              </select>
            </div>

            <!-- Transfer Date -->
            <div>
              <label class="text-[10px] font-bold text-slate-400 block mb-1 uppercase tracking-wider dark:text-slate-400">Transfer Date *</label>
              <input 
                v-model="form.transfer_date"
                type="date" 
                class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-950 dark:text-slate-100 border border-slate-200 dark:border-[#2E2E2E] rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 font-semibold"
                required
              />
            </div>
          </div>

          <!-- Notes -->
          <div>
            <label class="text-[10px] font-bold text-slate-400 block mb-1 uppercase tracking-wider dark:text-slate-400">Notes / Purpose of Transfer</label>
            <textarea 
              v-model="form.notes"
              rows="2"
              placeholder="Describe reason for this transfer order..." 
              class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-950 dark:text-slate-100 border border-slate-200 dark:border-[#2E2E2E] rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 font-medium"
            ></textarea>
          </div>
        </div>

        <!-- Add Items & Items Table Card -->
        <div class="bg-white dark:bg-[#1E1E1E] rounded-3xl border border-slate-200 dark:border-[#2E2E2E] shadow-sm p-6 space-y-4">
          <div class="flex justify-between items-center">
            <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider">Items to Transfer</h3>
            <span class="text-xs text-slate-400 dark:text-slate-400">Add products from dropdown to configure transfer amounts</span>
          </div>

          <!-- Add Item Selector -->
          <div class="flex gap-2">
            <div class="relative flex-1">
              <select 
                v-model="selectedProductKey"
                @change="onProductSelected"
                class="w-full px-3 py-2.5 bg-slate-50 dark:bg-zinc-950 dark:text-slate-100 border border-slate-200 dark:border-[#2E2E2E] rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 font-semibold text-xs cursor-pointer"
                :disabled="!form.source_warehouse_id"
              >
                <option value="">{{ form.source_warehouse_id ? 'Choose a product to transfer...' : 'Please select source warehouse first' }}</option>
                <option v-for="opt in productOptions" :key="opt.key" :value="opt.key">
                  {{ opt.label }} (Available: {{ opt.stock_qty }})
                </option>
              </select>
            </div>
            <button 
              type="button" 
              @click="addItem"
              :disabled="!selectedProductKey"
              class="px-4 py-2.5 bg-slate-800 dark:bg-indigo-600 hover:bg-slate-900 dark:hover:bg-indigo-700 text-white rounded-xl font-bold transition-all disabled:opacity-50 cursor-pointer"
            >
              Add
            </button>
          </div>

          <!-- Selected Items Table -->
          <div class="border border-slate-100 dark:border-[#2E2E2E] rounded-2xl overflow-hidden">
            <table class="w-full table-auto border-collapse text-xs">
              <thead>
                <tr class="bg-slate-50 dark:bg-[#252525] border-b border-slate-200 dark:border-[#2E2E2E] text-[10px] uppercase text-slate-500 dark:text-slate-400 font-extrabold">
                  <th class="px-4 py-3 text-left">Product Name</th>
                  <th class="px-4 py-3 text-center w-28">Available Stock</th>
                  <th class="px-4 py-3 text-center w-36">Transfer Qty</th>
                  <th class="px-4 py-3 text-center w-16">Remove</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-[#2E2E2E]">
                <tr v-if="form.items.length === 0">
                  <td colspan="4" class="px-4 py-8 text-center text-slate-400 italic dark:text-slate-400">No products added yet. Choose a source warehouse and add items to list.</td>
                </tr>
                <tr v-for="(item, idx) in form.items" :key="idx" class="hover:bg-slate-50/40 dark:hover:bg-[#2D2D2D]/80">
                  <!-- Product Details -->
                  <td class="px-4 py-3">
                    <div class="font-bold text-slate-900 dark:text-slate-100">{{ item.product_name }}</div>
                    <div v-if="item.variation_name" class="text-[10px] text-slate-400 font-semibold mt-0.5 dark:text-slate-400">{{ item.variation_name }}</div>
                    <div class="text-[9px] text-slate-400 mt-0.5 font-medium dark:text-slate-400">SKU: {{ item.sku }}</div>
                  </td>
                  <!-- Available Stock -->
                  <td class="px-4 py-3 text-center font-bold text-slate-600 dark:text-slate-300">
                    {{ item.available_qty }}
                  </td>
                  <!-- Transfer Qty Input with dynamic warning -->
                  <td class="px-4 py-3 text-center">
                    <div class="flex flex-col items-center">
                      <input 
                        v-model.number="item.quantity"
                        type="number" 
                        min="1"
                        :max="item.available_qty"
                        class="w-24 px-2 py-1 text-center bg-slate-50 dark:bg-zinc-950 dark:text-slate-100 border border-slate-200 dark:border-[#2E2E2E] rounded-lg focus:outline-none font-bold text-xs"
                        :class="item.quantity > item.available_qty ? 'border-rose-400 text-rose-600 focus:ring-1 focus:ring-rose-400' : 'focus:ring-1 focus:ring-indigo-500'"
                      />
                      <span v-if="item.quantity > item.available_qty" class="text-[9px] text-rose-550 font-bold mt-1 tracking-tight">Exceeds Stock!</span>
                    </div>
                  </td>
                  <!-- Remove Button -->
                  <td class="px-4 py-3 text-center">
                    <button 
                      type="button" 
                      @click="removeItem(idx)"
                      class="p-1 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-all focus:outline-none cursor-pointer dark:text-slate-400"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between items-center">
          <span class="text-[11px] text-slate-400 dark:text-slate-400">Fields marked with (*) are required.</span>
          
          <div class="flex gap-2">
            <button 
              type="button" 
              @click="$router.push('/inventory/transfer-orders')"
              class="px-5 py-2.5 text-slate-500 dark:text-slate-400 font-bold hover:text-slate-700 dark:hover:text-slate-200 cursor-pointer"
            >
              Cancel
            </button>
            
            <button 
              type="submit" 
              :disabled="submitting || isInvalid" 
              class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full font-bold shadow-md hover:shadow-lg transition-all flex items-center gap-1.5 disabled:opacity-50 cursor-pointer"
            >
              <svg v-if="submitting" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Create Draft
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();

const warehouses = ref([]);
const sourceStock = ref([]);
const productOptions = ref([]);
const selectedProductKey = ref('');

const submitting = ref(false);
const errorMsg = ref('');

const form = ref({
  source_warehouse_id: '',
  destination_warehouse_id: '',
  transfer_date: new Date().toISOString().substr(0, 10),
  notes: '',
  items: []
});

const isInvalid = computed(() => {
  if (form.value.items.length === 0) return true;
  return form.value.items.some(item => !item.quantity || item.quantity <= 0 || item.quantity > item.available_qty);
});

const fetchWarehouses = async () => {
  try {
    const res = await axios.get('/api/warehouses');
    warehouses.value = res.data || [];
    // Pre-populate source and dest if possible
    if (warehouses.value.length > 0) {
      const defaultWh = warehouses.value.find(w => w.is_default) || warehouses.value[0];
      form.value.source_warehouse_id = defaultWh.id;
      onSourceWarehouseChange();
    }
  } catch (err) {
    console.error('Failed to load warehouses:', err);
  }
};

const onSourceWarehouseChange = async () => {
  if (!form.value.source_warehouse_id) return;
  
  // Clear currently selected items since they belong to old source warehouse limits
  form.value.items = [];
  selectedProductKey.value = '';
  
  try {
    const res = await axios.get(`/api/warehouses/${form.value.source_warehouse_id}`);
    sourceStock.value = res.data.inventories || [];
    buildProductOptions();
  } catch (err) {
    console.error('Failed to fetch source warehouse stock:', err);
    sourceStock.value = [];
    productOptions.value = [];
  }
};

const buildProductOptions = () => {
  productOptions.value = sourceStock.value.map(item => {
    let label = item.product?.name || 'Unknown Product';
    if (item.variation) {
      label += ` (${item.variation.variation_name_string})`;
    }
    
    // Key will uniquely identify product + variation — always use String to avoid type issues
    const key = String(item.product_id) + (item.product_variation_id ? `_v_${item.product_variation_id}` : '');
    
    return {
      key,
      product_id: item.product_id,
      product_variation_id: item.product_variation_id,
      product_name: item.product?.name || 'Unknown Product',
      variation_name: item.variation?.variation_name_string || null,
      sku: item.variation?.sku || item.product?.sku || 'N/A',
      stock_qty: item.stock_qty,
      label
    };
  }).filter(opt => opt.stock_qty > 0); // Only permit transferring items with positive stock
};

const onProductSelected = () => {
  if (selectedProductKey.value) {
    addItem();
  }
};

const addItem = () => {
  if (!selectedProductKey.value) return;
  
  const selectedOpt = productOptions.value.find(opt => String(opt.key) === String(selectedProductKey.value));
  if (!selectedOpt) return;
  
  // Check if item is already added to lists
  const exists = form.value.items.some(item => 
    item.product_id === selectedOpt.product_id && 
    item.product_variation_id === selectedOpt.product_variation_id
  );
  
  if (exists) {
    errorMsg.value = 'Product is already added to transfer list.';
    selectedProductKey.value = '';
    return;
  }
  
  form.value.items.push({
    product_id: selectedOpt.product_id,
    product_variation_id: selectedOpt.product_variation_id,
    product_name: selectedOpt.product_name,
    variation_name: selectedOpt.variation_name,
    sku: selectedOpt.sku,
    available_qty: selectedOpt.stock_qty,
    quantity: 1
  });
  
  selectedProductKey.value = '';
  errorMsg.value = '';
};

const removeItem = (idx) => {
  form.value.items.splice(idx, 1);
};

const submitTransfer = async () => {
  if (isInvalid.value) return;
  
  submitting.value = true;
  errorMsg.value = '';
  
  try {
    await axios.post('/api/transfer-orders', {
      source_warehouse_id: form.value.source_warehouse_id,
      destination_warehouse_id: form.value.destination_warehouse_id,
      transfer_date: form.value.transfer_date,
      notes: form.value.notes,
      items: form.value.items.map(item => ({
        product_id: item.product_id,
        product_variation_id: item.product_variation_id,
        quantity: item.quantity
      }))
    });
    
    router.push('/inventory/transfer-orders');
  } catch (err) {
    console.error(err);
    errorMsg.value = err.response?.data?.message || 'Failed to create transfer order.';
  } finally {
    submitting.value = false;
  }
};

onMounted(() => {
  fetchWarehouses();
});
</script>

<style scoped>
</style>
