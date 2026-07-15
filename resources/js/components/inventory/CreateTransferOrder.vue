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

      <!-- Floating Error Toast Notification -->
      <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="transform translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="transform translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-show="errorMsg" class="fixed top-20 right-5 max-w-sm w-full bg-[#0f172a] text-slate-50 shadow-2xl rounded-2xl pointer-events-auto overflow-hidden z-[100] border border-white/5">
          <div class="px-5 py-4">
            <div class="flex items-center justify-between gap-3">
              <div class="flex items-center gap-3 flex-1 min-w-0">
                <div class="flex-shrink-0">
                  <svg class="w-5 h-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <p class="text-xs font-semibold leading-relaxed truncate select-none text-white dark:text-white" style="color: #ffffff !important;">{{ errorMsg }}</p>
              </div>
              <button type="button" @click="errorMsg = ''" class="flex-shrink-0 p-1 rounded-md text-slate-400 hover:text-white hover:bg-white/10 transition-all focus:outline-none cursor-pointer">
                <span class="sr-only">Close</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </transition>

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
            <div class="relative flex-1" id="product-dropdown-container">
              <!-- Dropdown Trigger Button -->
              <button 
                type="button"
                @click.stop="toggleDropdown"
                :disabled="!form.source_warehouse_id"
                class="w-full flex items-center justify-between px-3 py-2.5 bg-slate-50 dark:bg-zinc-950 dark:text-slate-100 border border-slate-200 dark:border-[#2E2E2E] rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 font-semibold text-xs cursor-pointer disabled:opacity-50 text-left"
              >
                <span class="truncate">
                  <template v-if="selectedProductKeys.length > 0">{{ selectedProductKeys.length }} product{{ selectedProductKeys.length > 1 ? 's' : '' }} selected</template>
                  <template v-else>{{ form.source_warehouse_id ? 'Choose a product to transfer...' : 'Please select source warehouse first' }}</template>
                </span>
                <svg 
                  class="w-4 h-4 text-slate-400 dark:text-slate-500 transition-transform duration-200"
                  :class="{ 'rotate-180': isDropdownOpen }"
                  fill="none" 
                  stroke="currentColor" 
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <!-- Floating Dropdown Panel -->
              <transition 
                enter-active-class="transition ease-out duration-100" 
                enter-from-class="opacity-0 translate-y-1" 
                enter-to-class="opacity-100 translate-y-0" 
                leave-active-class="transition ease-in duration-75" 
                leave-from-class="opacity-100 translate-y-0" 
                leave-to-class="opacity-0 translate-y-1"
              >
                <div 
                  v-show="isDropdownOpen" 
                  class="dropdown-options-scroll absolute left-0 right-0 bottom-full mb-1.5 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl shadow-xl py-1 z-50 max-h-64 overflow-y-auto"
                >
                  <!-- Search Filter inside dropdown -->
                  <div class="px-2 py-1.5 border-b border-slate-100 dark:border-[#2E2E2E] sticky top-0 bg-white dark:bg-[#1E1E1E] z-10">
                    <input 
                      v-model="productSearchQuery"
                      type="text" 
                      placeholder="Search products..." 
                      @click.stop
                      class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-zinc-950 dark:text-slate-100 border border-slate-200 dark:border-[#2E2E2E] rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500 placeholder-gray-400 dark:placeholder-slate-450"
                    />
                  </div>

                  <!-- Options List -->
                  <div class="py-1">
                    <div 
                      v-if="filteredProductOptions.length === 0" 
                      class="px-4 py-3 text-xs text-slate-400 dark:text-slate-500 text-center italic"
                    >
                      No items available
                    </div>
                    <div 
                      v-else
                      v-for="opt in filteredProductOptions" 
                      :key="opt.key"
                      @click.stop="toggleSelectOption(opt)"
                      class="px-4 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#2D2D2D]/85 cursor-pointer flex items-center justify-between gap-4 transition-colors"
                      :class="isOptionPending(opt) ? 'bg-indigo-50/60 dark:bg-indigo-950/30' : ''"
                    >
                      <div class="flex flex-col min-w-0">
                        <span class="truncate text-slate-900 dark:text-slate-100">{{ opt.product_name }}</span>
                        <span v-if="opt.variation_name" class="text-[9px] text-slate-400 dark:text-slate-500 font-bold mt-0.5">{{ opt.variation_name }}</span>
                        <span class="text-[9px] text-slate-400 dark:text-slate-500 mt-0.5">SKU: {{ opt.sku }}</span>
                      </div>
                      <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-zinc-800/80 px-2 py-0.5 rounded-md">
                          Qty: {{ opt.stock_qty }}
                        </span>
                        <!-- Already in table -->
                        <span v-if="isOptionSelected(opt)" class="text-emerald-500 dark:text-emerald-400">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                          </svg>
                        </span>
                        <!-- Pending selection checkbox -->
                        <span v-else-if="isOptionPending(opt)" class="text-indigo-600 dark:text-indigo-400">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4" />
                          </svg>
                        </span>
                        <span v-else class="w-4 h-4 border border-slate-300 dark:border-slate-600 rounded"></span>
                      </div>
                    </div>
                  </div>
                </div>
              </transition>
            </div>
            <button 
              type="button" 
              @click="addSelectedItems"
              :disabled="selectedProductKeys.length === 0"
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
            </table>
            <div class="items-table-scroll" :class="{ 'max-h-[440px] overflow-y-auto': form.items.length > 10 }">
              <table class="w-full table-auto border-collapse text-xs">
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
                    <td class="px-4 py-3 text-center font-bold text-slate-600 dark:text-slate-300 w-28">
                      {{ item.available_qty }}
                    </td>
                    <!-- Transfer Qty Input with dynamic warning -->
                    <td class="px-4 py-3 text-center w-36">
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
                    <td class="px-4 py-3 text-center w-16">
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
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();

const warehouses = ref([]);
const sourceStock = ref([]);
const productOptions = ref([]);
const selectedProductKey = ref('');
const selectedProductKeys = ref([]);
const submitting = ref(false);
const errorMsg = ref('');

const form = ref({
  source_warehouse_id: '',
  destination_warehouse_id: '',
  transfer_date: new Date().toISOString().substr(0, 10),
  notes: '',
  items: []
});

// Auto-hide watcher for errorMsg
watch(errorMsg, (newVal) => {
  if (newVal) {
    setTimeout(() => {
      if (errorMsg.value === newVal) {
        errorMsg.value = '';
      }
    }, 4000);
  }
});

const isDropdownOpen = ref(false);
const productSearchQuery = ref('');

const toggleDropdown = () => {
  if (!form.value.source_warehouse_id) return;
  isDropdownOpen.value = !isDropdownOpen.value;
};

const toggleSelectOption = (opt) => {
  // Don't allow selecting items already in the table
  if (isOptionSelected(opt)) {
    errorMsg.value = 'Product is already added to transfer list.';
    return;
  }
  const idx = selectedProductKeys.value.indexOf(opt.key);
  if (idx === -1) {
    selectedProductKeys.value.push(opt.key);
  } else {
    selectedProductKeys.value.splice(idx, 1);
  }
};

const isOptionPending = (opt) => {
  return selectedProductKeys.value.includes(opt.key);
};

const isOptionSelected = (opt) => {
  return form.value.items.some(item => 
    item.product_id === opt.product_id && 
    item.product_variation_id === opt.product_variation_id
  );
};

const selectedOptionLabel = computed(() => {
  if (selectedProductKeys.value.length === 0) return '';
  if (selectedProductKeys.value.length === 1) {
    const opt = productOptions.value.find(o => String(o.key) === String(selectedProductKeys.value[0]));
    return opt ? opt.label : '';
  }
  return `${selectedProductKeys.value.length} products selected`;
});

const filteredProductOptions = computed(() => {
  if (!productSearchQuery.value) return productOptions.value;
  const q = productSearchQuery.value.toLowerCase();
  return productOptions.value.filter(opt => {
    return opt.product_name.toLowerCase().includes(q) || 
           (opt.variation_name && opt.variation_name.toLowerCase().includes(q)) ||
           opt.sku.toLowerCase().includes(q);
  });
});

const handleClickOutside = (event) => {
  const container = document.getElementById('product-dropdown-container');
  if (container && !container.contains(event.target)) {
    isDropdownOpen.value = false;
  }
};





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
  selectedProductKeys.value = [];
  isDropdownOpen.value = false;
  productSearchQuery.value = '';
  
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
    addSelectedItems();
  }
};

const addSelectedItems = () => {
  if (selectedProductKeys.value.length === 0) return;
  
  let addedCount = 0;
  let skippedCount = 0;
  
  for (const key of selectedProductKeys.value) {
    const selectedOpt = productOptions.value.find(opt => String(opt.key) === String(key));
    if (!selectedOpt) continue;
    
    // Check if item is already added to list
    const exists = form.value.items.some(item => 
      item.product_id === selectedOpt.product_id && 
      item.product_variation_id === selectedOpt.product_variation_id
    );
    
    if (exists) {
      skippedCount++;
      continue;
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
    addedCount++;
  }
  
  // Clear pending selections
  selectedProductKeys.value = [];
  selectedProductKey.value = '';
  isDropdownOpen.value = false;
  productSearchQuery.value = '';
  
  if (skippedCount > 0 && addedCount === 0) {
    errorMsg.value = 'All selected products are already in the transfer list.';
  } else if (skippedCount > 0) {
    errorMsg.value = `${addedCount} added, ${skippedCount} skipped (already in list).`;
  } else {
    errorMsg.value = '';
  }
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
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
/* Custom thin premium scrollbar */
.dropdown-options-scroll::-webkit-scrollbar,
.items-table-scroll::-webkit-scrollbar {
  width: 4px;
}
.dropdown-options-scroll::-webkit-scrollbar-track,
.items-table-scroll::-webkit-scrollbar-track {
  background: transparent;
}
.dropdown-options-scroll::-webkit-scrollbar-thumb,
.items-table-scroll::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.4);
  border-radius: 9999px;
}
.dropdown-options-scroll::-webkit-scrollbar-thumb:hover,
.items-table-scroll::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.65);
}

/* Firefox compatibility */
.dropdown-options-scroll,
.items-table-scroll {
  scrollbar-width: thin;
  scrollbar-color: rgba(156, 163, 175, 0.4) transparent;
}
</style>
