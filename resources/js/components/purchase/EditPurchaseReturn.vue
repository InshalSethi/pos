<template>
  <div class="w-full bg-slate-50 dark:bg-zinc-950 min-h-screen">
    <!-- Header bar -->
    <div class="bg-white dark:bg-[#1E1E1E] border-b border-slate-200 dark:border-[#2E2E2E] px-6 py-4 shadow-sm">
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
          <router-link
            to="/purchase/returns"
            class="text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 font-bold text-xs transition-colors flex items-center space-x-1.5"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Returns</span>
          </router-link>
          <span class="text-slate-300 dark:text-slate-600">|</span>
          <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Edit Purchase Return ({{ form.return_number }})</h1>
        </div>
      </div>
    </div>

    <!-- Main Workspace Layout -->
    <div class="w-full bg-white dark:bg-[#1E1E1E] flex flex-col lg:flex-row min-h-[calc(100vh-66px)] border-t border-slate-200 dark:border-[#2E2E2E]">
      
      <!-- Left Panel: Return Form & Line Items (3/4 width) -->
      <div class="w-full lg:w-3/4 p-6 sm:p-8 flex flex-col">

        <!-- Top Setup Grid (Supplier, PO Link, Warehouse, Date, Reason) -->
        <div class="bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-800 rounded-2xl p-5 mb-6 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
          
          <!-- Return Number -->
          <div>
            <label class="block text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Return #</label>
            <input
              v-model="form.return_number"
              type="text"
              readonly
              class="w-full px-3 py-2 bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs font-mono font-bold text-slate-700 dark:text-zinc-300 cursor-not-allowed"
            />
          </div>

          <!-- Supplier -->
          <div>
            <label class="block text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Supplier *</label>
            <select
              v-model="form.supplier_id"
              required
              class="w-full px-3 py-2 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-zinc-100"
            >
              <option v-for="sup in suppliers" :key="sup.id" :value="sup.id">
                {{ sup.name }}
              </option>
            </select>
          </div>

          <!-- PO Reference -->
          <div>
            <label class="block text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">PO Reference</label>
            <select
              v-model="form.purchase_order_id"
              class="w-full px-3 py-2 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-zinc-100"
            >
              <option value="">Standalone / None</option>
              <option v-for="po in purchaseOrders" :key="po.id" :value="po.id">
                {{ po.po_number }}
              </option>
            </select>
          </div>

          <!-- Warehouse -->
          <div>
            <label class="block text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Warehouse</label>
            <select
              v-model="form.warehouse_id"
              class="w-full px-3 py-2 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-zinc-100"
            >
              <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">
                {{ wh.name }}
              </option>
            </select>
          </div>

          <!-- Return Date -->
          <div>
            <label class="block text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Return Date *</label>
            <input
              v-model="form.return_date"
              type="date"
              required
              class="w-full px-3 py-2 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-zinc-100"
            />
          </div>
        </div>

        <!-- Catalog Search & Product Selection -->
        <div class="mb-6">
          <div class="relative w-full">
            <input
              v-model="productSearch"
              type="text"
              placeholder="Search products to add additional items..."
              class="w-full pl-4 pr-10 py-2.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-medium text-slate-800 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-blue-500/50 shadow-sm"
              @focus="isProductDropdownOpen = true"
            />
            <div
              v-show="isProductDropdownOpen && filteredProducts.length > 0"
              class="absolute left-0 right-0 mt-1 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-xl z-50 max-h-60 overflow-y-auto py-2"
            >
              <div
                v-for="product in filteredProducts"
                :key="product.id"
                @click="addProductToReturn(product)"
                class="px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-zinc-800 cursor-pointer flex justify-between items-center text-xs border-b border-slate-100 dark:border-zinc-800/60 last:border-0"
              >
                <div>
                  <div class="font-bold text-slate-800 dark:text-zinc-200">{{ product.name }}</div>
                  <div class="text-[10px] text-slate-400">SKU: {{ product.sku }}</div>
                </div>
                <div class="text-right">
                  <div class="font-black text-blue-600 dark:text-blue-400">{{ currencySymbol }}{{ formatCurrency(product.cost_price || product.selling_price) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Dynamic Items Table -->
        <div class="grow bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-sm flex flex-col mb-6">
          <div class="p-4 border-b border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-800/30 flex justify-between items-center">
            <h3 class="text-sm font-bold text-slate-800 dark:text-zinc-200">Returned Items Breakdown</h3>
            <span class="text-xs text-slate-400">{{ form.items.length }} {{ form.items.length === 1 ? 'Item' : 'Items' }}</span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead>
                <tr class="bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-200 dark:border-zinc-700 text-slate-400 dark:text-zinc-500 uppercase font-bold tracking-wider">
                  <th class="py-3 px-4 w-12 text-center">#</th>
                  <th class="py-3 px-4">Item Description</th>
                  <th class="py-3 px-4 text-center w-28">Unit Cost</th>
                  <th class="py-3 px-4 text-center w-28">Qty Returned</th>
                  <th class="py-3 px-4 text-center w-24">Tax</th>
                  <th class="py-3 px-4 text-center w-24">Discount</th>
                  <th class="py-3 px-4 text-right w-32">Total</th>
                  <th class="py-3 px-4 text-center w-12"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                <tr v-if="form.items.length === 0">
                  <td colspan="8" class="py-12 text-center text-slate-400 dark:text-zinc-500">
                    No return items.
                  </td>
                </tr>

                <tr v-for="(item, idx) in form.items" :key="idx" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/30">
                  <td class="py-3 px-4 text-center text-slate-400 font-bold">{{ idx + 1 }}</td>
                  <td class="py-3 px-4">
                    <div class="font-bold text-slate-800 dark:text-zinc-200">{{ item.product_name || item.product?.name }}</div>
                    <div class="text-[10px] text-slate-400 font-mono" v-if="item.product_sku || item.product?.sku">SKU: {{ item.product_sku || item.product?.sku }}</div>
                  </td>
                  <td class="py-3 px-4 text-center">
                    <input
                      v-model.number="item.unit_cost"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-24 text-center px-2 py-1 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded text-xs focus:ring-1 focus:ring-blue-500"
                    />
                  </td>
                  <td class="py-3 px-4 text-center">
                    <input
                      v-model.number="item.quantity"
                      type="number"
                      min="1"
                      class="w-20 text-center px-2 py-1 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded text-xs font-bold focus:ring-1 focus:ring-blue-500"
                    />
                  </td>
                  <td class="py-3 px-4 text-center">
                    <input
                      v-model.number="item.tax_amount"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-20 text-center px-2 py-1 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded text-xs focus:ring-1 focus:ring-blue-500"
                    />
                  </td>
                  <td class="py-3 px-4 text-center">
                    <input
                      v-model.number="item.discount_amount"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-20 text-center px-2 py-1 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded text-xs focus:ring-1 focus:ring-blue-500"
                    />
                  </td>
                  <td class="py-3 px-4 text-right font-black text-slate-900 dark:text-zinc-100">
                    {{ currencySymbol }}{{ formatCurrency(calculateLineTotal(item)) }}
                  </td>
                  <td class="py-3 px-4 text-center">
                    <button
                      @click="removeItem(idx)"
                      class="text-rose-500 hover:text-rose-700 p-1 rounded hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors"
                      title="Remove Item"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Right Panel: Summary, Status & Actions (1/4 width) -->
      <div class="w-full lg:w-1/4 p-6 sm:p-8 bg-slate-50/50 dark:bg-zinc-900/40 border-l border-slate-200 dark:border-[#2E2E2E] flex flex-col justify-between">
        
        <div class="space-y-6">
          <h3 class="text-sm font-extrabold uppercase text-slate-400 dark:text-zinc-500 tracking-wider">Return Summary</h3>

          <!-- Subtotal / Tax / Discount breakdown -->
          <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-4 space-y-3">
            <div class="flex justify-between text-xs text-slate-600 dark:text-zinc-400">
              <span>Subtotal</span>
              <span class="font-bold">{{ currencySymbol }}{{ formatCurrency(computedSubtotal) }}</span>
            </div>
            <div class="flex justify-between text-xs text-slate-600 dark:text-zinc-400">
              <span>Tax Total</span>
              <span class="font-bold text-amber-600">+{{ currencySymbol }}{{ formatCurrency(computedTax) }}</span>
            </div>
            <div class="flex justify-between text-xs text-slate-600 dark:text-zinc-400">
              <span>Discount Total</span>
              <span class="font-bold text-emerald-600">-{{ currencySymbol }}{{ formatCurrency(computedDiscount) }}</span>
            </div>
            <div class="border-t border-slate-100 dark:border-zinc-800 pt-3 flex justify-between text-sm font-black text-slate-900 dark:text-zinc-100">
              <span>Grand Total</span>
              <span class="text-blue-600 dark:text-blue-400">{{ currencySymbol }}{{ formatCurrency(computedGrandTotal) }}</span>
            </div>
          </div>

          <!-- Reason -->
          <div>
            <label class="block text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Reason for Return *</label>
            <select
              v-model="form.reason"
              required
              class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-zinc-100 font-semibold"
            >
              <option value="Damaged Goods">Damaged Goods</option>
              <option value="Defective / Quality Issue">Defective / Quality Issue</option>
              <option value="Wrong Item Shipped">Wrong Item Shipped</option>
              <option value="Overstocked / Excess">Overstocked / Excess</option>
              <option value="Expired Product">Expired Product</option>
              <option value="Other">Other Reason</option>
            </select>
          </div>

          <!-- Status -->
          <div>
            <label class="block text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Return Status</label>
            <select
              v-model="form.status"
              class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-zinc-100 font-semibold"
            >
              <option value="draft">Draft</option>
              <option value="pending">Pending Approval</option>
              <option value="approved">Approved (Deduct Inventory &amp; Post Ledger)</option>
              <option value="completed">Completed</option>
            </select>
          </div>

          <!-- Refund Status -->
          <div>
            <label class="block text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Refund Status</label>
            <select
              v-model="form.refund_status"
              class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-zinc-100 font-semibold"
            >
              <option value="pending">Pending Refund</option>
              <option value="partial">Partial Refund</option>
              <option value="refunded">Refunded / Credited</option>
            </select>
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Notes &amp; Remarks</label>
            <textarea
              v-model="form.notes"
              rows="3"
              class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-zinc-100"
            ></textarea>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="pt-6 border-t border-slate-200 dark:border-zinc-800 space-y-2 mt-6">
          <button
            @click="updateReturn"
            :disabled="submitting || form.items.length === 0"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl text-xs shadow-md transition-all disabled:opacity-50 flex items-center justify-center space-x-2 cursor-pointer"
          >
            <span>Update Purchase Return</span>
          </button>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const currencySymbol = computed(() => authStore.currencySymbol || '$');

const suppliers = ref([]);
const purchaseOrders = ref([]);
const warehouses = ref([]);
const products = ref([]);
const productSearch = ref('');
const isProductDropdownOpen = ref(false);
const submitting = ref(false);

const form = reactive({
  return_number: '',
  supplier_id: '',
  purchase_order_id: '',
  warehouse_id: 1,
  return_date: '',
  reason: 'Damaged Goods',
  status: 'draft',
  refund_status: 'pending',
  notes: '',
  items: [],
});

const filteredProducts = computed(() => {
  if (!productSearch.value) return [];
  const q = productSearch.value.toLowerCase();
  return products.value.filter(p =>
    (p.name && p.name.toLowerCase().includes(q)) ||
    (p.sku && p.sku.toLowerCase().includes(q))
  ).slice(0, 30);
});

const computedSubtotal = computed(() => {
  return form.items.reduce((sum, item) => sum + ((item.unit_cost || 0) * (item.quantity || 0)), 0);
});

const computedTax = computed(() => {
  return form.items.reduce((sum, item) => sum + (parseFloat(item.tax_amount) || 0), 0);
});

const computedDiscount = computed(() => {
  return form.items.reduce((sum, item) => sum + (parseFloat(item.discount_amount) || 0), 0);
});

const computedGrandTotal = computed(() => {
  return Math.max(0, (computedSubtotal.value + computedTax.value) - computedDiscount.value);
});

const fetchReturnDetails = async () => {
  try {
    const res = await axios.get(`/api/purchase-returns/${route.params.id}`);
    const data = res.data;

    form.return_number = data.return_number;
    form.supplier_id = data.supplier_id;
    form.purchase_order_id = data.purchase_order_id || '';
    form.warehouse_id = data.warehouse_id || 1;
    form.return_date = data.return_date ? data.return_date.split('T')[0] : '';
    form.reason = data.reason || 'Damaged Goods';
    form.status = data.status || 'draft';
    form.refund_status = data.refund_status || 'pending';
    form.notes = data.notes || '';

    form.items = (data.items || []).map(i => ({
      product_id: i.product_id,
      product_name: i.product?.name || 'Product',
      product_sku: i.product?.sku || '',
      unit_cost: parseFloat(i.unit_cost || 0),
      quantity: parseInt(i.quantity || 1),
      tax_amount: parseFloat(i.tax_amount || 0),
      discount_amount: parseFloat(i.discount_amount || 0),
    }));

  } catch (err) {
    console.error('Error fetching return details:', err);
  }
};

const fetchSuppliers = async () => {
  try {
    const res = await axios.get('/api/suppliers');
    suppliers.value = res.data.data || res.data || [];
  } catch (err) {
    console.error('Error fetching suppliers:', err);
  }
};

const fetchPurchaseOrders = async () => {
  try {
    const res = await axios.get('/api/purchase-orders?per_page=100');
    purchaseOrders.value = res.data.purchase_orders?.data || res.data.data || [];
  } catch (err) {
    console.error('Error fetching POs:', err);
  }
};

const fetchWarehouses = async () => {
  try {
    const res = await axios.get('/api/warehouses');
    warehouses.value = res.data.data || res.data || [];
  } catch (err) {
    console.error('Error fetching warehouses:', err);
  }
};

const fetchProducts = async () => {
  try {
    const res = await axios.get('/api/products?per_page=500');
    products.value = res.data.data || res.data || [];
  } catch (err) {
    console.error('Error fetching products:', err);
  }
};

const addProductToReturn = (product) => {
  const existing = form.items.find(i => i.product_id === product.id);
  if (existing) {
    existing.quantity += 1;
  } else {
    form.items.push({
      product_id: product.id,
      product_name: product.name,
      product_sku: product.sku,
      unit_cost: parseFloat(product.cost_price || product.selling_price || 0),
      quantity: 1,
      tax_amount: 0,
      discount_amount: 0,
    });
  }
  productSearch.value = '';
  isProductDropdownOpen.value = false;
};

const calculateLineTotal = (item) => {
  const sub = (parseFloat(item.unit_cost) || 0) * (parseFloat(item.quantity) || 0);
  const tax = parseFloat(item.tax_amount) || 0;
  const disc = parseFloat(item.discount_amount) || 0;
  return Math.max(0, (sub + tax) - disc);
};

const removeItem = (idx) => {
  form.items.splice(idx, 1);
};

const formatCurrency = (val) => {
  return parseFloat(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const updateReturn = async () => {
  if (form.items.length === 0) {
    alert('Please add at least one line item to return.');
    return;
  }

  submitting.value = true;
  try {
    await axios.put(`/api/purchase-returns/${route.params.id}`, form);
    router.push('/purchase/returns');
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to update purchase return');
  } finally {
    submitting.value = false;
  }
};

onMounted(() => {
  fetchSuppliers();
  fetchPurchaseOrders();
  fetchWarehouses();
  fetchProducts();
  fetchReturnDetails();
});
</script>
