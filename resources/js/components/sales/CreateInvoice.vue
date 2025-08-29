<template>
  <div class="max-w-full mx-auto bg-gray-50 min-h-screen">
    <!-- Header -->
    <!-- <div class="bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg px-6 py-4">
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-3">
          <div class="bg-white bg-opacity-20 rounded-lg p-2">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-bold text-white">Create Sales Invoice</h1>
            <p class="text-indigo-100 text-sm">Professional Invoice Creation</p>
          </div>
        </div>
        <div class="flex items-center space-x-6">
          <button
            @click="goBack"
            class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition-all duration-200 flex items-center space-x-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Invoices</span>
          </button>
          <div class="text-right">
            <div class="text-white font-medium">{{ authStore.user?.name }}</div>
            <div class="text-indigo-200 text-sm">{{ currentTime }}</div>
            <div class="text-indigo-200 text-sm">{{ currentDate }}</div>
          </div>
        </div>
      </div>
    </div> -->

    <div class="flex h-screen bg-gray-50">
      <!-- Left Panel - Product Selection -->
      <div class="w-2/3 p-6 overflow-y-auto max-h-screen">
        <!-- Invoice Header -->
        <div class="p-4 border-b border-gray-200 bg-gray-50">
          <h3 class="text-lg font-medium text-gray-900 mb-2">Invoice Details</h3>
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <label class="block text-gray-600">Invoice Date</label>
              <input
                v-model="invoiceForm.sale_date"
                type="date"
                class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
              />
            </div>
            <div>
              <label class="block text-gray-600">Status</label>
              <select
                v-model="invoiceForm.status"
                class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
              >
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
              </select>
            </div>
          </div>
        </div>
        <!-- Customer Selection -->
        <div class="p-4 border-b border-gray-200">
          <label class="block text-sm font-medium text-gray-700 mb-2">Customer</label>
          <div class="flex space-x-2">
            <div class="flex-1 relative">
              <input
                v-model="customerSearch"
                type="text"
                placeholder="Search customer..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                @input="debouncedCustomerSearch"
              />
              <!-- Customer Dropdown -->
              <div v-if="customerSearchResults.length > 0" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto">
                <div
                  v-for="customer in customerSearchResults"
                  :key="customer.id"
                  @click="selectCustomer(customer)"
                  class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white"
                >
                  <div class="flex items-center">
                    <span class="font-normal block truncate">{{ customer.name }}</span>
                  </div>
                  <span class="text-xs text-gray-500">{{ customer.phone || customer.email }}</span>
                </div>
              </div>
            </div>
            <button
              @click="showCustomerModal = true"
              class="px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm"
            >
              New
            </button>
          </div>
          <div v-if="selectedCustomer" class="mt-2 p-2 bg-gray-50 rounded">
            <div class="flex justify-between items-center">
              <span class="text-sm font-medium">{{ selectedCustomer.name }}</span>
              <button @click="clearCustomer" class="text-red-600 hover:text-red-800 text-sm">Remove</button>
            </div>
            <span class="text-xs text-gray-500">{{ selectedCustomer.phone || selectedCustomer.email }}</span>
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

          <!-- Category Filter -->
          <!-- <div class="flex flex-wrap gap-2 mb-4">
            <button
              @click="selectedCategory = ''"
              :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                selectedCategory === '' 
                  ? 'bg-indigo-600 text-white' 
                  : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
              ]"
            >
              All Categories
            </button>
            <button
              v-for="category in categories"
              :key="category.id"
              @click="selectedCategory = category.id"
              :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                selectedCategory === category.id 
                  ? 'bg-indigo-600 text-white' 
                  : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
              ]"
            >
              {{ category.name }}
            </button>
          </div> -->
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
          <div
            v-for="product in filteredProducts"
            :key="product.id"
            @click="addToInvoice(product)"
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
                <span class="text-lg font-bold text-green-600">${{ product.selling_price }}</span>
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

      <!-- Right Panel - Invoice Details -->
      <div class="w-1/3 bg-white border-l border-gray-200 flex flex-col max-h-screen">
        

        

        <!-- Invoice Items -->
        <div class="flex-1 overflow-y-auto p-4 min-h-0 invoice-items-container" style="min-height: 350px;">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Items</h3>

          <div v-if="invoiceItems.length === 0" class="text-center py-8 text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="mt-2">No items added</p>
            <p class="text-sm">Add products to create invoice</p>
          </div>

          <div v-else class="space-y-3 max-h-96 overflow-y-auto pr-2">
            <div
              v-for="(item, index) in invoiceItems"
              :key="index"
              class="bg-gray-50 rounded-lg p-3 border border-gray-200 hover:bg-gray-100 transition-colors invoice-item"
            >
              <div class="flex justify-between items-start mb-2">
                <div class="flex-1">
                  <h4 class="font-medium text-gray-900 text-sm">{{ item.product.name }}</h4>
                  <p class="text-xs text-gray-500">{{ item.product.sku }}</p>
                </div>
                <button
                  @click="removeFromInvoice(index)"
                  class="text-red-600 hover:text-red-800 ml-2"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>

              <div class="grid grid-cols-3 gap-2 text-sm">
                <div>
                  <label class="block text-gray-600 text-xs">Qty</label>
                  <input
                    v-model.number="item.quantity"
                    type="number"
                    min="1"
                    :max="item.product.stock_quantity"
                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    @input="updateItemTotal(index)"
                  />
                </div>
                <div>
                  <label class="block text-gray-600 text-xs">Price</label>
                  <input
                    v-model.number="item.unit_price"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    @input="updateItemTotal(index)"
                  />
                </div>
                <div>
                  <label class="block text-gray-600 text-xs">Discount</label>
                  <input
                    v-model.number="item.discount_amount"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    @input="updateItemTotal(index)"
                  />
                </div>
              </div>

              <div class="mt-2 text-right">
                <span class="text-sm font-medium text-gray-900">Total: ${{ item.total.toFixed(2) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Invoice Summary and Actions -->
        <div class="p-4 border-t border-gray-200 bg-gray-50">
          <!-- Totals -->
          <div class="space-y-2 mb-4">
            <div class="flex justify-between text-sm">
              <span>Subtotal:</span>
              <span>${{ invoiceSubtotal.toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span>Discount:</span>
              <span>-${{ totalDiscount.toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span>Tax:</span>
              <input
                v-model.number="invoiceForm.tax_amount"
                type="number"
                step="0.01"
                min="0"
                class="w-20 px-2 py-1 border border-gray-300 rounded text-sm text-right focus:outline-none focus:ring-1 focus:ring-indigo-500"
                @input="calculateTotal"
              />
            </div>
            <div class="flex justify-between text-lg font-bold border-t pt-2">
              <span>Total:</span>
              <span>${{ invoiceTotal.toFixed(2) }}</span>
            </div>
          </div>

          <!-- Payment Method -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
            <select
              v-model="invoiceForm.payment_method"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="cash">Cash</option>
              <option value="card">Card</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="mobile_payment">Mobile Payment</option>
              <option value="mixed">Mixed</option>
            </select>
          </div>

          <!-- Amount Paid -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Amount Paid</label>
            <input
              v-model.number="invoiceForm.paid_amount"
              type="number"
              step="0.01"
              min="0"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              :placeholder="invoiceTotal.toFixed(2)"
            />
            <div v-if="changeAmount > 0" class="mt-1 text-sm text-green-600">
              Change: ${{ changeAmount.toFixed(2) }}
            </div>
          </div>

          <!-- Notes -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
            <textarea
              v-model="invoiceForm.notes"
              rows="2"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              placeholder="Additional notes..."
            ></textarea>
          </div>

          <!-- Action Buttons -->
          <div class="space-y-2">
            <button
              @click="saveInvoice"
              :disabled="invoiceItems.length === 0 || saving"
              class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ saving ? 'Saving...' : 'Save Invoice' }}
            </button>

            <div class="grid grid-cols-2 gap-2">
              <button
                @click="saveAsDraft"
                :disabled="invoiceItems.length === 0 || saving"
                class="bg-yellow-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-yellow-700 disabled:opacity-50"
              >
                Save as Draft
              </button>
              <button
                @click="clearInvoice"
                :disabled="invoiceItems.length === 0"
                class="bg-red-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-red-700 disabled:opacity-50"
              >
                Clear
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Customer Creation Modal -->
    <div v-if="showCustomerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Customer</h3>

          <form @submit.prevent="createCustomer">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
              <input
                v-model="newCustomer.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
              <input
                v-model="newCustomer.phone"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
              <input
                v-model="newCustomer.email"
                type="email"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>

            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="closeCustomerModal"
                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="!newCustomer.name || creatingCustomer"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ creatingCustomer ? 'Creating...' : 'Create' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Success/Error Notifications -->
    <div v-if="notifications.length > 0" class="fixed top-4 right-4 z-50 space-y-2">
      <div
        v-for="notification in notifications"
        :key="notification.id"
        :class="[
          'p-4 rounded-lg shadow-lg max-w-sm',
          notification.type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        ]"
      >
        <div class="flex justify-between items-center">
          <span>{{ notification.message }}</span>
          <button @click="removeNotification(notification.id)" class="ml-2 text-white hover:text-gray-200">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useToast } from '@/composables/useToast';
import api from '@/services/api';

const router = useRouter();
const authStore = useAuthStore();
const { showToast } = useToast();

// Reactive data
const products = ref([]);
const categories = ref([]);
const customers = ref([]);
const invoiceItems = ref([]);
const selectedCustomer = ref(null);
const selectedCategory = ref('');
const productSearch = ref('');
const customerSearch = ref('');
const customerSearchResults = ref([]);
const loadingProducts = ref(false);
const saving = ref(false);
const creatingCustomer = ref(false);
const showCustomerModal = ref(false);
const notifications = ref([]);

// Form data
const invoiceForm = ref({
  customer_id: '',
  sale_date: new Date().toISOString().split('T')[0],
  payment_method: 'cash',
  status: 'completed',
  tax_amount: 0,
  paid_amount: 0,
  notes: ''
});

const newCustomer = ref({
  name: '',
  phone: '',
  email: ''
});

// Current date time
const currentDateTime = ref('');

// Computed properties
const filteredProducts = computed(() => {
  let filtered = products.value;
  
  if (selectedCategory.value) {
    filtered = filtered.filter(product => product.category_id === selectedCategory.value);
  }
  
  if (productSearch.value) {
    const search = productSearch.value.toLowerCase();
    filtered = filtered.filter(product => 
      product.name.toLowerCase().includes(search) ||
      product.sku.toLowerCase().includes(search) ||
      (product.barcode && product.barcode.toLowerCase().includes(search))
    );
  }
  
  return filtered;
});

const invoiceSubtotal = computed(() => {
  return invoiceItems.value.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
});

const totalDiscount = computed(() => {
  return invoiceItems.value.reduce((sum, item) => sum + (item.discount_amount || 0), 0);
});

const invoiceTotal = computed(() => {
  return invoiceSubtotal.value - totalDiscount.value + (invoiceForm.value.tax_amount || 0);
});

const changeAmount = computed(() => {
  return Math.max(0, (invoiceForm.value.paid_amount || 0) - invoiceTotal.value);
});

const currentDate = computed(() => {
  return currentDateTime.value.split(',')[0] || '';
});

const currentTime = computed(() => {
  return currentDateTime.value.split(',')[1] || '';
});

// Methods
const updateDateTime = () => {
  const now = new Date();
  const date = now.toLocaleDateString();
  const time = now.toLocaleTimeString();
  currentDateTime.value = `${date}, ${time}`;
};

const loadProducts = async () => {
  try {
    loadingProducts.value = true;
    const response = await api.get('/products');
    products.value = response.data.data || response.data;
  } catch (error) {
    showNotification('Error loading products', 'error');
  } finally {
    loadingProducts.value = false;
  }
};

const loadCategories = async () => {
  try {
    const response = await api.get('/categories');
    categories.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error loading categories:', error);
  }
};

const searchCustomers = async (query) => {
  if (query.length < 2) {
    customerSearchResults.value = [];
    return;
  }

  try {
    const response = await api.get('/customers', {
      params: { search: query, per_page: 10 }
    });
    customerSearchResults.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error searching customers:', error);
  }
};

const debouncedProductSearch = debounce(() => {
  // Product search is handled by computed property
}, 300);

const debouncedCustomerSearch = debounce(() => {
  searchCustomers(customerSearch.value);
}, 300);

const clearSearch = () => {
  productSearch.value = '';
  selectedCategory.value = '';
};

const addFirstFilteredProduct = () => {
  if (filteredProducts.value.length > 0) {
    const product = filteredProducts.value[0];
    addToInvoice(product);
    // Clear search after adding product
    productSearch.value = '';
    // Show notification
    showNotification(`Added "${product.name}" to invoice`, 'success');
  } else if (productSearch.value.trim()) {
    showNotification('No products found matching your search', 'error');
  }
};

const addToInvoice = (product) => {
  const existingItem = invoiceItems.value.find(item => item.product.id === product.id);

  if (existingItem) {
    existingItem.quantity += 1;
    updateItemTotal(invoiceItems.value.indexOf(existingItem));
  } else {
    invoiceItems.value.push({
      product: product,
      quantity: 1,
      unit_price: parseFloat(product.selling_price),
      discount_amount: 0,
      total: parseFloat(product.selling_price)
    });
  }

  calculateTotal();
};

const removeFromInvoice = (index) => {
  invoiceItems.value.splice(index, 1);
  calculateTotal();
};

const updateItemTotal = (index) => {
  const item = invoiceItems.value[index];
  item.total = (item.quantity * item.unit_price) - (item.discount_amount || 0);
  calculateTotal();
};

const calculateTotal = () => {
  if (!invoiceForm.value.paid_amount) {
    invoiceForm.value.paid_amount = invoiceTotal.value;
  }
};

const selectCustomer = (customer) => {
  selectedCustomer.value = customer;
  invoiceForm.value.customer_id = customer.id;
  customerSearch.value = customer.name;
  customerSearchResults.value = [];
};

const clearCustomer = () => {
  selectedCustomer.value = null;
  invoiceForm.value.customer_id = '';
  customerSearch.value = '';
  customerSearchResults.value = [];
};

const createCustomer = async () => {
  try {
    creatingCustomer.value = true;
    const response = await api.post('/customers', newCustomer.value);

    selectCustomer(response.data);
    closeCustomerModal();
    showNotification('Customer created successfully', 'success');
  } catch (error) {
    showNotification('Error creating customer', 'error');
  } finally {
    creatingCustomer.value = false;
  }
};

const closeCustomerModal = () => {
  showCustomerModal.value = false;
  newCustomer.value = { name: '', phone: '', email: '' };
};

const saveInvoice = async () => {
  try {
    saving.value = true;

    const invoiceData = {
      customer_id: invoiceForm.value.customer_id || null,
      sale_date: invoiceForm.value.sale_date,
      payment_method: invoiceForm.value.payment_method,
      status: invoiceForm.value.status,
      tax_amount: invoiceForm.value.tax_amount || 0,
      paid_amount: invoiceForm.value.paid_amount || invoiceTotal.value,
      notes: invoiceForm.value.notes,
      items: invoiceItems.value.map(item => ({
        product_id: item.product.id,
        quantity: item.quantity,
        unit_price: item.unit_price,
        discount_amount: item.discount_amount || 0
      }))
    };

    const response = await api.sales.create(invoiceData);

    showNotification('Invoice created successfully', 'success');

    // Redirect to invoice view
    setTimeout(() => {
      router.push(`/sales/invoices/${response.data.sale.id}`);
    }, 1500);

  } catch (error) {
    showNotification('Error creating invoice', 'error');
    console.error('Error:', error);
  } finally {
    saving.value = false;
  }
};

const saveAsDraft = async () => {
  const originalStatus = invoiceForm.value.status;
  invoiceForm.value.status = 'pending';
  await saveInvoice();
  invoiceForm.value.status = originalStatus;
};

const clearInvoice = () => {
  if (confirm('Are you sure you want to clear all items?')) {
    invoiceItems.value = [];
    clearCustomer();
    invoiceForm.value = {
      customer_id: '',
      sale_date: new Date().toISOString().split('T')[0],
      payment_method: 'cash',
      status: 'completed',
      tax_amount: 0,
      paid_amount: 0,
      notes: ''
    };
  }
};

const goBack = () => {
  router.push('/sales/invoices');
};

const showNotification = (message, type = 'info') => {
  const notification = {
    id: Date.now(),
    message,
    type
  };
  notifications.value.push(notification);

  setTimeout(() => {
    removeNotification(notification.id);
  }, 5000);
};

const removeNotification = (id) => {
  const index = notifications.value.findIndex(n => n.id === id);
  if (index > -1) {
    notifications.value.splice(index, 1);
  }
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
  updateDateTime();
  setInterval(updateDateTime, 1000);
  loadProducts();
  loadCategories();
});

// Watchers
watch(() => invoiceTotal.value, (newTotal) => {
  if (!invoiceForm.value.paid_amount || invoiceForm.value.paid_amount === 0) {
    invoiceForm.value.paid_amount = newTotal;
  }
});
</script>

<style scoped>
/* Custom scrollbar for webkit browsers */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Smooth transitions */
.transition-all {
  transition: all 0.2s ease-in-out;
}

/* Product card hover effects */
.product-card {
  transition: all 0.2s ease-in-out;
}

.product-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

/* Button hover effects */
button {
  transition: all 0.2s ease-in-out;
}

button:hover:not(:disabled) {
  transform: translateY(-1px);
}

/* Input focus effects */
input:focus,
select:focus,
textarea:focus {
  transform: scale(1.02);
  transition: all 0.2s ease-in-out;
}

/* Loading animation */
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Notification animations */
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from,
.notification-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

/* Grid responsive adjustments */
@media (max-width: 1536px) {
  .grid-cols-5 {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

@media (max-width: 1280px) {
  .grid-cols-4 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (max-width: 1024px) {
  .w-2\/3 {
    width: 60%;
  }

  .w-1\/3 {
    width: 40%;
  }

  .grid-cols-3 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 768px) {
  .flex {
    flex-direction: column;
  }

  .w-2\/3,
  .w-1\/3 {
    width: 100%;
  }

  .h-screen {
    height: auto;
    min-height: calc(100vh - 80px);
  }

  .grid-cols-2 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
  }

  .space-x-4 > * + * {
    margin-left: 0;
    margin-top: 1rem;
  }

  .space-x-6 > * + * {
    margin-left: 0;
    margin-top: 1rem;
  }
}

/* Print styles */
@media print {
  .no-print {
    display: none !important;
  }
}

/* Custom utility classes */
.shadow-soft {
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
}

.border-soft {
  border-color: rgba(0, 0, 0, 0.1);
}

.bg-gradient-soft {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Invoice item styling */
.invoice-item {
  transition: all 0.2s ease;
}

.invoice-item:hover {
  background-color: #f8fafc;
  border-color: #e2e8f0;
}

/* Invoice items container improvements */
.invoice-items-container {
  min-height: 300px;
  max-height: calc(100vh - 500px);
}

/* Ensure invoice items section has adequate height */
.flex-1 {
  min-height: 0;
}

/* Customer dropdown styling */
.customer-dropdown {
  max-height: 200px;
  overflow-y: auto;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  background: white;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.customer-dropdown-item {
  padding: 0.75rem;
  cursor: pointer;
  border-bottom: 1px solid #f3f4f6;
  transition: background-color 0.2s ease;
}

.customer-dropdown-item:hover {
  background-color: #f9fafb;
}

.customer-dropdown-item:last-child {
  border-bottom: none;
}

/* Success/Error states */
.success-state {
  background-color: #f0fdf4;
  border-color: #22c55e;
  color: #15803d;
}

.error-state {
  background-color: #fef2f2;
  border-color: #ef4444;
  color: #dc2626;
}

/* Loading states */
.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
}

/* Enhanced button styles */
.btn-primary {
  background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
  border: none;
  color: white;
  font-weight: 600;
  transition: all 0.2s ease;
}

.btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
}

.btn-secondary {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  color: #475569;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-secondary:hover:not(:disabled) {
  background: #f1f5f9;
  border-color: #cbd5e1;
  color: #334155;
}

.btn-success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  border: none;
  color: white;
  font-weight: 600;
  transition: all 0.2s ease;
}

.btn-success:hover:not(:disabled) {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.btn-danger {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  border: none;
  color: white;
  font-weight: 600;
  transition: all 0.2s ease;
}

.btn-danger:hover:not(:disabled) {
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

.btn-warning {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  border: none;
  color: white;
  font-weight: 600;
  transition: all 0.2s ease;
}

.btn-warning:hover:not(:disabled) {
  background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}
</style>
