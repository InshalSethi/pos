<template>
  <div class="max-w-full mx-auto bg-gray-50 min-h-screen">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center h-64">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-md p-4 m-6">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Error loading purchase order</h3>
          <div class="mt-2 text-sm text-red-700">{{ error }}</div>
        </div>
      </div>
    </div>

    <!-- Edit Form -->
    <div v-else-if="purchaseOrder" class="flex h-screen bg-gray-50">
      <!-- Left Panel - Product Selection -->
      <div class="w-2/3 p-6 overflow-y-auto max-h-screen">
        <!-- Purchase Order Header -->
        <div class="p-4 border-b border-gray-200 bg-gray-50">
          <div class="flex justify-between items-center mb-2">
            <h3 class="text-lg font-medium text-gray-900">Edit Purchase Order</h3>
            <span class="text-sm text-gray-500">{{ purchaseOrder.po_number }}</span>
          </div>
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <label class="block text-gray-600">Order Date</label>
              <input
                v-model="orderForm.order_date"
                type="date"
                class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
              />
            </div>
            <div>
              <label class="block text-gray-600">Expected Delivery</label>
              <input
                v-model="orderForm.expected_delivery_date"
                type="date"
                class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
              />
            </div>
          </div>
        </div>

        <!-- Supplier Selection -->
        <div class="p-4 border-b border-gray-200">
          <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
          <div class="flex space-x-2">
            <div class="flex-1 relative">
              <input
                v-model="supplierSearch"
                type="text"
                placeholder="Search supplier..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                @input="debouncedSupplierSearch"
              />
              <!-- Supplier Dropdown -->
              <div v-if="supplierSearchResults.length > 0" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto">
                <div
                  v-for="supplier in supplierSearchResults"
                  :key="supplier.id"
                  @click="selectSupplier(supplier)"
                  class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white"
                >
                  <div class="flex items-center">
                    <span class="font-normal block truncate">{{ supplier.name }}</span>
                  </div>
                  <span class="text-xs text-gray-500">{{ supplier.phone || supplier.email }}</span>
                </div>
              </div>
            </div>
          </div>
          <div v-if="selectedSupplier" class="mt-2 p-2 bg-gray-50 rounded">
            <div class="flex justify-between items-center">
              <span class="text-sm font-medium">{{ selectedSupplier.name }}</span>
              <button @click="clearSupplier" class="text-red-600 hover:text-red-800 text-sm">Change</button>
            </div>
            <span class="text-xs text-gray-500">{{ selectedSupplier.phone || selectedSupplier.email }}</span>
          </div>
        </div>

        <!-- Search and Categories -->
        <div class="mb-6">
          <div class="flex space-x-4 mb-6">
            <div class="flex-1 relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
              <input
                v-model="productSearch"
                type="text"
                placeholder="Search products by name, SKU, or barcode... (Press Enter to add first result)"
                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent shadow-sm transition-all duration-200"
                @input="debouncedProductSearch"
                @keydown.enter="addFirstFilteredProduct"
              />
            </div>
            <button
              @click="clearSearch"
              class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors"
            >
              Clear
            </button>
          </div>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
          <div
            v-for="product in filteredProducts"
            :key="product.id"
            @click="addToOrder(product)"
            class="bg-white rounded-lg shadow hover:shadow-md transition-shadow cursor-pointer border border-gray-200 hover:border-indigo-300"
          >
            <!-- Product Image -->
            <div class="h-32 bg-gray-100 rounded-t-lg flex items-center justify-center">
              <img
                v-if="product.image"
                :src="product.image"
                :alt="product.name"
                class="h-full w-full object-cover rounded-t-lg"
              />
              <div v-else class="text-gray-400">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
              </div>
            </div>

            <!-- Product Info -->
            <div class="p-3">
              <h3 class="font-medium text-gray-900 text-sm truncate">{{ product.name }}</h3>
              <p class="text-xs text-gray-500 mb-1">{{ product.sku }}</p>
              <div class="flex justify-between items-center">
                <span class="text-lg font-bold text-blue-600">${{ product.cost_price || product.selling_price }}</span>
                <span class="text-xs text-gray-500">{{ product.stock_quantity }} in stock</span>
              </div>
              <div v-if="product.is_low_stock" class="mt-1">
                <span class="text-xs text-red-600 bg-red-100 px-2 py-1 rounded">Low Stock</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loadingProducts" class="flex justify-center items-center py-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>

        <!-- No Products Found -->
        <div v-if="!loadingProducts && filteredProducts.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
          <p class="mt-1 text-sm text-gray-500">Try adjusting your search or category filter.</p>
        </div>
      </div>

      <!-- Right Panel - Purchase Order Details -->
      <div class="w-1/3 bg-white border-l border-gray-200 flex flex-col max-h-screen">
        <!-- Order Items -->
        <div class="flex-1 overflow-y-auto p-4 min-h-0 order-items-container" style="min-height: 350px;">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Purchase Order Items</h3>

          <div v-if="orderItems.length === 0" class="text-center py-8 text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="mt-2">No items added</p>
            <p class="text-sm">Add products to update purchase order</p>
          </div>

          <div v-else class="space-y-3 max-h-96 overflow-y-auto pr-2">
            <div
              v-for="(item, index) in orderItems"
              :key="index"
              class="bg-gray-50 rounded-lg p-3 border border-gray-200 hover:bg-gray-100 transition-colors order-item"
            >
              <div class="flex justify-between items-start mb-2">
                <div class="flex-1">
                  <h4 class="font-medium text-gray-900 text-sm">{{ item.product.name }}</h4>
                  <p class="text-xs text-gray-500">{{ item.product.sku }}</p>
                </div>
                <button
                  @click="removeFromOrder(index)"
                  class="text-red-600 hover:text-red-800 ml-2"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>

              <div class="grid grid-cols-2 gap-2 text-sm">
                <div>
                  <label class="block text-gray-600 text-xs">Qty</label>
                  <input
                    v-model.number="item.quantity_ordered"
                    type="number"
                    min="1"
                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    @input="updateItemTotal(index)"
                  />
                </div>
                <div>
                  <label class="block text-gray-600 text-xs">Unit Cost</label>
                  <input
                    v-model.number="item.unit_cost"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    @input="updateItemTotal(index)"
                  />
                </div>
              </div>

              <div class="mt-2 text-right">
                <span class="text-sm font-medium text-gray-900">Total: ${{ item.total_cost.toFixed(2) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Summary and Actions -->
        <div class="p-4 border-t border-gray-200 bg-gray-50">
          <!-- Totals -->
          <div class="space-y-2 mb-4">
            <div class="flex justify-between text-sm">
              <span>Subtotal:</span>
              <span>${{ orderSubtotal.toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span>Tax:</span>
              <input
                v-model.number="orderForm.tax_amount"
                type="number"
                step="0.01"
                min="0"
                class="w-20 px-2 py-1 border border-gray-300 rounded text-sm text-right focus:outline-none focus:ring-1 focus:ring-indigo-500"
                @input="calculateTotal"
              />
            </div>
            <div class="flex justify-between text-sm">
              <span>Shipping:</span>
              <input
                v-model.number="orderForm.shipping_cost"
                type="number"
                step="0.01"
                min="0"
                class="w-20 px-2 py-1 border border-gray-300 rounded text-sm text-right focus:outline-none focus:ring-1 focus:ring-indigo-500"
                @input="calculateTotal"
              />
            </div>
            <div class="flex justify-between text-lg font-bold border-t pt-2">
              <span>Total:</span>
              <span>${{ orderTotal.toFixed(2) }}</span>
            </div>
          </div>

          <!-- Status -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              v-model="orderForm.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="draft">Draft</option>
              <option value="sent">Sent</option>
              <option value="confirmed">Confirmed</option>
              <option value="partially_received">Partially Received</option>
              <option value="received">Received</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <!-- Notes -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
            <textarea
              v-model="orderForm.notes"
              rows="2"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              placeholder="Additional notes..."
            ></textarea>
          </div>

          <!-- Action Buttons -->
          <div class="space-y-2">
            <button
              @click="updateOrder"
              :disabled="orderItems.length === 0 || saving || !selectedSupplier"
              class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ saving ? 'Updating...' : 'Update Purchase Order' }}
            </button>

            <div class="grid grid-cols-2 gap-2">
              <button
                @click="goBack"
                class="bg-gray-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-gray-700"
              >
                Cancel
              </button>
              <button
                @click="clearOrder"
                :disabled="orderItems.length === 0"
                class="bg-red-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-red-700 disabled:opacity-50"
              >
                Clear
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Notifications -->
    <div class="fixed top-4 right-4 z-50 space-y-2">
      <div
        v-for="(notification, index) in notifications"
        :key="index"
        :class="[
          'px-4 py-3 rounded-lg shadow-lg transition-all duration-300',
          notification.type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        ]"
      >
        {{ notification.message }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

// Reactive data
const purchaseOrder = ref(null);
const products = ref([]);
const suppliers = ref([]);
const orderItems = ref([]);
const selectedSupplier = ref(null);
const productSearch = ref('');
const supplierSearch = ref('');
const supplierSearchResults = ref([]);
const loading = ref(true);
const loadingProducts = ref(false);
const saving = ref(false);
const error = ref(null);
const notifications = ref([]);

// Form data
const orderForm = ref({
  supplier_id: '',
  order_date: '',
  expected_delivery_date: '',
  status: 'draft',
  tax_amount: 0,
  shipping_cost: 0,
  notes: ''
});

// Computed properties
const filteredProducts = computed(() => {
  if (!productSearch.value) return products.value;

  const search = productSearch.value.toLowerCase();
  return products.value.filter(product =>
    product.name.toLowerCase().includes(search) ||
    product.sku.toLowerCase().includes(search) ||
    (product.barcode && product.barcode.toLowerCase().includes(search))
  );
});

const orderSubtotal = computed(() => {
  if (!orderItems.value || orderItems.value.length === 0) return 0;
  return orderItems.value.reduce((sum, item) => {
    const itemTotal = parseFloat(item.total_cost) || 0;
    return sum + itemTotal;
  }, 0);
});

const orderTotal = computed(() => {
  const subtotal = orderSubtotal.value || 0;
  const tax = parseFloat(orderForm.value.tax_amount) || 0;
  const shipping = parseFloat(orderForm.value.shipping_cost) || 0;
  return subtotal + tax + shipping;
});

// Methods
const fetchPurchaseOrder = async () => {
  try {
    loading.value = true;
    const response = await api.get(`/purchase-orders/${route.params.id}`);
    purchaseOrder.value = response.data;

    // Populate form data
    orderForm.value = {
      supplier_id: purchaseOrder.value.supplier_id,
      order_date: purchaseOrder.value.order_date,
      expected_delivery_date: purchaseOrder.value.expected_delivery_date,
      status: purchaseOrder.value.status,
      tax_amount: parseFloat(purchaseOrder.value.tax_amount) || 0,
      shipping_cost: parseFloat(purchaseOrder.value.shipping_cost) || 0,
      notes: purchaseOrder.value.notes || ''
    };

    // Set selected supplier
    selectedSupplier.value = purchaseOrder.value.supplier;
    supplierSearch.value = purchaseOrder.value.supplier?.name || '';

    // Populate order items
    orderItems.value = purchaseOrder.value.purchase_order_items.map(item => ({
      product: item.product,
      product_id: item.product_id,
      quantity_ordered: item.quantity_ordered,
      unit_cost: parseFloat(item.unit_cost),
      total_cost: parseFloat(item.total_cost)
    }));

  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load purchase order';
  } finally {
    loading.value = false;
  }
};

const loadProducts = async () => {
  try {
    loadingProducts.value = true;
    const response = await api.get('/products');
    products.value = response.data.data || response.data;
  } catch (error) {
    showNotification('Error loading products', 'error');
    console.error('Error:', error);
  } finally {
    loadingProducts.value = false;
  }
};

const loadSuppliers = async () => {
  try {
    const response = await api.get('/suppliers');
    suppliers.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error loading suppliers:', error);
  }
};

const debouncedProductSearch = debounce(() => {
  // Product search is handled by computed property
}, 300);

const debouncedSupplierSearch = debounce(() => {
  if (supplierSearch.value.length > 0) {
    const search = supplierSearch.value.toLowerCase();
    supplierSearchResults.value = suppliers.value.filter(supplier =>
      supplier.name.toLowerCase().includes(search) ||
      (supplier.phone && supplier.phone.includes(search)) ||
      (supplier.email && supplier.email.toLowerCase().includes(search))
    ).slice(0, 5);
  } else {
    supplierSearchResults.value = [];
  }
}, 300);

const selectSupplier = (supplier) => {
  selectedSupplier.value = supplier;
  orderForm.value.supplier_id = supplier.id;
  supplierSearch.value = supplier.name;
  supplierSearchResults.value = [];
};

const clearSupplier = () => {
  selectedSupplier.value = null;
  orderForm.value.supplier_id = '';
  supplierSearch.value = '';
  supplierSearchResults.value = [];
};

const clearSearch = () => {
  productSearch.value = '';
};

const addFirstFilteredProduct = () => {
  if (filteredProducts.value.length > 0) {
    const product = filteredProducts.value[0];
    addToOrder(product);
    productSearch.value = '';
    showNotification(`Added "${product.name}" to order`, 'success');
  } else if (productSearch.value.trim()) {
    showNotification('No products found matching your search', 'error');
  }
};

const addToOrder = (product) => {
  const existingItem = orderItems.value.find(item => item.product.id === product.id);

  if (existingItem) {
    existingItem.quantity_ordered += 1;
    updateItemTotal(orderItems.value.indexOf(existingItem));
  } else {
    orderItems.value.push({
      product: product,
      product_id: product.id,
      quantity_ordered: 1,
      unit_cost: parseFloat(product.cost_price || product.selling_price || 0),
      total_cost: parseFloat(product.cost_price || product.selling_price || 0)
    });
  }

  calculateTotal();
};

const removeFromOrder = (index) => {
  orderItems.value.splice(index, 1);
  calculateTotal();
};

const updateItemTotal = (index) => {
  const item = orderItems.value[index];
  const quantity = parseFloat(item.quantity_ordered) || 0;
  const unitCost = parseFloat(item.unit_cost) || 0;
  item.total_cost = quantity * unitCost;
  calculateTotal();
};

const calculateTotal = () => {
  // Total is calculated by computed property
};

const updateOrder = async () => {
  if (!selectedSupplier.value) {
    showNotification('Please select a supplier', 'error');
    return;
  }

  if (orderItems.value.length === 0) {
    showNotification('Please add at least one item', 'error');
    return;
  }

  saving.value = true;

  try {
    const orderData = {
      supplier_id: orderForm.value.supplier_id,
      order_date: orderForm.value.order_date,
      expected_delivery_date: orderForm.value.expected_delivery_date,
      status: orderForm.value.status,
      tax_amount: orderForm.value.tax_amount || 0,
      shipping_cost: orderForm.value.shipping_cost || 0,
      notes: orderForm.value.notes,
      items: orderItems.value.map(item => ({
        product_id: item.product_id,
        quantity_ordered: item.quantity_ordered,
        unit_cost: item.unit_cost
      }))
    };

    await api.put(`/purchase-orders/${route.params.id}`, orderData);

    showNotification('Purchase order updated successfully', 'success');

    // Redirect to purchase orders list
    setTimeout(() => {
      router.push('/purchase/orders');
    }, 1500);

  } catch (error) {
    showNotification('Error updating purchase order', 'error');
    console.error('Error:', error);
  } finally {
    saving.value = false;
  }
};

const clearOrder = () => {
  orderItems.value = [];
  orderForm.value.tax_amount = 0;
  orderForm.value.shipping_cost = 0;
  orderForm.value.notes = '';
  calculateTotal();
};

const goBack = () => {
  router.push('/purchase/orders');
};

const showNotification = (message, type = 'info') => {
  const notification = { message, type };
  notifications.value.push(notification);

  setTimeout(() => {
    const index = notifications.value.indexOf(notification);
    if (index > -1) {
      notifications.value.splice(index, 1);
    }
  }, 3000);
};

// Utility function
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Lifecycle
onMounted(() => {
  fetchPurchaseOrder();
  loadProducts();
  loadSuppliers();
});
</script>
