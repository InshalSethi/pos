<template>
  <div class="max-w-full mx-auto bg-gray-50 min-h-screen">
      <!-- Header -->
      <div class="bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg px-6 py-4">
        <div class="flex justify-between items-center">
          <div class="flex items-center space-x-3">
            <div class="bg-white bg-opacity-20 rounded-lg p-2">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5-6M20 13v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6"></path>
              </svg>
            </div>
            <div>
              <h1 class="text-2xl font-bold text-white">Point of Sale</h1>
              <p class="text-indigo-100 text-sm">Fast & Efficient Checkout</p>
            </div>
          </div>
          <div class="flex items-center space-x-6">
            <div class="text-right">
              <div class="text-white font-medium">{{ authStore.user?.name }}</div>
              <div class="text-indigo-200 text-sm">Cashier</div>
            </div>
            <div class="text-right">
              <div class="text-white font-medium">{{ currentDateTime.split(',')[1] }}</div>
              <div class="text-indigo-200 text-sm">{{ currentDateTime.split(',')[0] }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex h-screen bg-gray-50">
        <!-- Left Panel - Product Selection -->
        <div class="w-2/3 p-6 overflow-y-auto">
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
                  placeholder="Search products by name, SKU, or barcode..."
                  class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent shadow-sm transition-all duration-200"
                  @input="debouncedProductSearch"
                />
              </div>
              <button
                @click="scanBarcode"
                class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 flex items-center shadow-lg transition-all duration-200 transform hover:scale-105"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h2M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z"></path>
                </svg>
                Scan
              </button>
            </div>

            <!-- Category Filter -->
            <div class="flex space-x-2 overflow-x-auto pb-2">
              <button
                @click="selectedCategory = ''"
                :class="[
                  'px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap',
                  selectedCategory === ''
                    ? 'bg-indigo-600 text-white'
                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                ]"
              >
                All
              </button>
              <button
                v-for="category in categories"
                :key="category.id"
                @click="selectedCategory = category.id"
                :class="[
                  'px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap',
                  selectedCategory === category.id
                    ? 'bg-indigo-600 text-white'
                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                ]"
              >
                {{ category.name }}
              </button>
            </div>
          </div>

          <!-- Products Grid -->
          <div v-if="loadingProducts" class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
            <p class="mt-2 text-gray-600">Loading products...</p>
          </div>

          <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <div
              v-for="product in filteredProducts"
              :key="product.id"
              @click="addToCart(product)"
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
        </div>

        <!-- Right Panel - Cart and Checkout -->
        <div class="w-1/3 bg-white border-l border-gray-200 flex flex-col">
          <!-- Customer Selection -->
          <div class="p-4 border-b border-gray-200">
            <label class="block text-sm font-medium text-gray-700 mb-2">Customer</label>
            <div class="flex space-x-2">
              <div class="flex-1">
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

          <!-- Cart Items -->
          <div class="flex-1 overflow-y-auto p-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Cart Items</h3>

            <div v-if="cartItems.length === 0" class="text-center py-8 text-gray-500">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5-6m0 0h15M17 21a2 2 0 100-4 2 2 0 000 4zM9 21a2 2 0 100-4 2 2 0 000 4z"></path>
              </svg>
              <p class="mt-2">Cart is empty</p>
              <p class="text-sm">Add products to start a sale</p>
            </div>

            <div v-else class="space-y-3">
              <div
                v-for="item in cartItems"
                :key="item.product.id"
                class="bg-gray-50 rounded-lg p-3"
              >
                <div class="flex justify-between items-start mb-2">
                  <div class="flex-1">
                    <h4 class="font-medium text-gray-900 text-sm">{{ item.product.name }}</h4>
                    <p class="text-xs text-gray-500">{{ item.product.sku }}</p>
                  </div>
                  <button
                    @click="removeFromCart(item.product.id)"
                    class="text-red-600 hover:text-red-800"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                  </button>
                </div>

                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-2">
                    <button
                      @click="updateQuantity(item.product.id, item.quantity - 1)"
                      class="w-6 h-6 rounded-full bg-gray-300 flex items-center justify-center hover:bg-gray-400"
                    >
                      <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                      </svg>
                    </button>
                    <input
                      :value="item.quantity"
                      @input="updateQuantity(item.product.id, parseInt($event.target.value) || 1)"
                      type="number"
                      min="1"
                      class="w-16 px-2 py-1 text-center border border-gray-300 rounded text-sm"
                    />
                    <button
                      @click="updateQuantity(item.product.id, item.quantity + 1)"
                      class="w-6 h-6 rounded-full bg-gray-300 flex items-center justify-center hover:bg-gray-400"
                    >
                      <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                    </button>
                  </div>
                  <div class="text-right">
                    <div class="text-sm font-medium">${{ (item.quantity * item.unit_price).toFixed(2) }}</div>
                    <div class="text-xs text-gray-500">${{ item.unit_price }} each</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Cart Summary and Checkout -->
          <div class="border-t border-gray-200 p-4">
            <!-- Cart Totals -->
            <div class="space-y-2 mb-4">
              <div class="flex justify-between text-sm">
                <span>Subtotal:</span>
                <span>${{ cartSubtotal.toFixed(2) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span>Tax:</span>
                <span>${{ cartTax.toFixed(2) }}</span>
              </div>
              <div class="flex justify-between text-lg font-bold border-t pt-2">
                <span>Total:</span>
                <span>${{ cartTotal.toFixed(2) }}</span>
              </div>
            </div>

            <!-- Payment Method -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
              <select
                v-model="paymentMethod"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="mobile_payment">Mobile Payment</option>
              </select>
            </div>

            <!-- Amount Paid -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Amount Paid</label>
              <input
                v-model.number="amountPaid"
                type="number"
                step="0.01"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                :placeholder="cartTotal.toFixed(2)"
              />
              <div v-if="changeAmount > 0" class="mt-1 text-sm text-green-600">
                Change: ${{ changeAmount.toFixed(2) }}
              </div>
              <div v-if="amountPaid < cartTotal" class="mt-1 text-sm text-red-600">
                Insufficient payment
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-2">
              <button
                @click="processSale"
                :disabled="cartItems.length === 0 || amountPaid < cartTotal || processing"
                class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ processing ? 'Processing...' : 'Complete Sale' }}
              </button>

              <div class="grid grid-cols-2 gap-2">
                <button
                  @click="holdSale"
                  :disabled="cartItems.length === 0"
                  class="bg-yellow-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-yellow-700 disabled:opacity-50"
                >
                  Hold
                </button>
                <button
                  @click="clearCart"
                  :disabled="cartItems.length === 0"
                  class="bg-red-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-red-700 disabled:opacity-50"
                >
                  Clear
                </button>
              </div>
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

            <div v-if="customerFormErrors.length > 0" class="mb-4">
              <div class="bg-red-50 border border-red-200 rounded-md p-3">
                <ul class="text-sm text-red-600">
                  <li v-for="error in customerFormErrors" :key="error">{{ error }}</li>
                </ul>
              </div>
            </div>

            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="closeCustomerModal"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="creatingCustomer"
                class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ creatingCustomer ? 'Creating...' : 'Create Customer' }}
              </button>
            </div>
          </form>
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

            <div v-if="customerFormErrors.length > 0" class="mb-4">
              <div class="bg-red-50 border border-red-200 rounded-md p-3">
                <ul class="text-sm text-red-600">
                  <li v-for="error in customerFormErrors" :key="error">{{ error }}</li>
                </ul>
              </div>
            </div>

            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="closeCustomerModal"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="creatingCustomer"
                class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ creatingCustomer ? 'Creating...' : 'Create Customer' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Notifications -->
    <div class="fixed top-4 right-4 z-50 space-y-2">
      <div
        v-for="notification in notifications"
        :key="notification.id"
        :class="[
          'max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden transform transition-all duration-300',
          notification.show ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0'
        ]"
      >
        <div class="p-4">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg
                v-if="notification.type === 'success'"
                class="h-6 w-6 text-green-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <svg
                v-else-if="notification.type === 'error'"
                class="h-6 w-6 text-red-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <svg
                v-else
                class="h-6 w-6 text-blue-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-3 w-0 flex-1 pt-0.5">
              <p class="text-sm font-medium text-gray-900">{{ notification.message }}</p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
              <button
                @click="removeNotification(notification.id)"
                class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                <span class="sr-only">Close</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const authStore = useAuthStore();

// Reactive data
const products = ref([]);
const categories = ref([]);
const cartItems = ref([]);
const selectedCustomer = ref(null);
const customerSearchResults = ref([]);
const loadingProducts = ref(false);
const processing = ref(false);
const showCustomerModal = ref(false);
const creatingCustomer = ref(false);
const customerFormErrors = ref([]);
const notifications = ref([]);

// Search and filters
const productSearch = ref('');
const customerSearch = ref('');
const selectedCategory = ref('');

// Payment
const paymentMethod = ref('cash');
const amountPaid = ref(0);

// New customer form
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
    filtered = filtered.filter(product => product.category_id == selectedCategory.value);
  }

  if (productSearch.value) {
    const search = productSearch.value.toLowerCase();
    filtered = filtered.filter(product =>
      product.name.toLowerCase().includes(search) ||
      product.sku.toLowerCase().includes(search) ||
      (product.barcode && product.barcode.toLowerCase().includes(search))
    );
  }

  return filtered.filter(product => product.is_active && product.stock_quantity > 0);
});

const cartSubtotal = computed(() => {
  return cartItems.value.reduce((total, item) => {
    return total + (item.quantity * item.unit_price);
  }, 0);
});

const cartTax = computed(() => {
  return cartItems.value.reduce((total, item) => {
    const itemSubtotal = item.quantity * item.unit_price;
    const itemTax = itemSubtotal * (item.product.tax_rate / 100);
    return total + itemTax;
  }, 0);
});

const cartTotal = computed(() => {
  return cartSubtotal.value + cartTax.value;
});

const changeAmount = computed(() => {
  return Math.max(0, amountPaid.value - cartTotal.value);
});

// Methods
const fetchProducts = async () => {
  loadingProducts.value = true;
  try {
    const response = await axios.get('/api/products', {
      params: { per_page: 100, is_active: true }
    });
    products.value = response.data.data;
  } catch (error) {
    console.error('Error fetching products:', error);
  } finally {
    loadingProducts.value = false;
  }
};

const fetchCategories = async () => {
  try {
    const response = await axios.get('/api/categories', {
      params: { is_active: true }
    });
    categories.value = response.data;
  } catch (error) {
    console.error('Error fetching categories:', error);
  }
};

const addToCart = (product) => {
  const existingItem = cartItems.value.find(item => item.product.id === product.id);

  if (existingItem) {
    if (existingItem.quantity < product.stock_quantity) {
      existingItem.quantity++;
    } else {
      showNotification('Insufficient stock for this product', 'error');
    }
  } else {
    cartItems.value.push({
      product: product,
      quantity: 1,
      unit_price: parseFloat(product.selling_price),
      discount_amount: 0
    });
  }
};

const removeFromCart = (productId) => {
  const index = cartItems.value.findIndex(item => item.product.id === productId);
  if (index > -1) {
    cartItems.value.splice(index, 1);
  }
};

const updateQuantity = (productId, newQuantity) => {
  const item = cartItems.value.find(item => item.product.id === productId);
  if (item) {
    if (newQuantity <= 0) {
      removeFromCart(productId);
    } else if (newQuantity <= item.product.stock_quantity) {
      item.quantity = newQuantity;
    } else {
      showNotification('Insufficient stock for this quantity', 'error');
    }
  }
};

const clearCart = () => {
  if (confirm('Are you sure you want to clear the cart?')) {
    cartItems.value = [];
    selectedCustomer.value = null;
    customerSearch.value = '';
    amountPaid.value = 0;
  }
};

const selectCustomer = (customer) => {
  selectedCustomer.value = customer;
  customerSearch.value = customer.name;
  customerSearchResults.value = [];
};

const clearCustomer = () => {
  selectedCustomer.value = null;
  customerSearch.value = '';
};

const searchCustomers = async (query) => {
  if (query.length < 2) {
    customerSearchResults.value = [];
    return;
  }

  try {
    const response = await axios.get('/api/customers/search/quick', {
      params: { q: query }
    });
    customerSearchResults.value = response.data;
  } catch (error) {
    console.error('Error searching customers:', error);
  }
};

const createCustomer = async () => {
  creatingCustomer.value = true;
  customerFormErrors.value = [];

  try {
    const response = await axios.post('/api/customers', newCustomer.value);

    // Select the newly created customer
    selectCustomer(response.data.customer);

    // Close modal and reset form
    closeCustomerModal();

  } catch (error) {
    if (error.response?.data?.errors) {
      customerFormErrors.value = Object.values(error.response.data.errors).flat();
    } else {
      customerFormErrors.value = [error.response?.data?.message || 'An error occurred'];
    }
  } finally {
    creatingCustomer.value = false;
  }
};

const closeCustomerModal = () => {
  showCustomerModal.value = false;
  newCustomer.value = {
    name: '',
    phone: '',
    email: ''
  };
  customerFormErrors.value = [];
};

const processSale = async () => {
  if (cartItems.value.length === 0) {
    showNotification('Cart is empty. Please add items before processing sale.', 'error');
    return;
  }

  if (amountPaid.value < cartTotal.value) {
    showNotification('Insufficient payment amount. Please enter the correct amount.', 'error');
    return;
  }

  processing.value = true;

  try {
    const saleData = {
      customer_id: selectedCustomer.value?.id || null,
      items: cartItems.value.map(item => ({
        product_id: item.product.id,
        quantity: item.quantity,
        unit_price: item.unit_price,
        discount_amount: item.discount_amount || 0
      })),
      payment_method: paymentMethod.value,
      paid_amount: amountPaid.value,
      notes: ''
    };

    const response = await axios.post('/api/sales', saleData);

    // Show success message
    showNotification(`Sale completed successfully! Sale Number: ${response.data.sale.sale_number}`, 'success');

    // Clear cart and reset form
    clearCart();

    // Refresh products to update stock quantities
    await fetchProducts();

  } catch (error) {
    console.error('Error processing sale:', error);
    showNotification(error.response?.data?.message || 'Failed to process sale', 'error');
  } finally {
    processing.value = false;
  }
};

const holdSale = () => {
  // TODO: Implement hold sale functionality
  showNotification('Hold sale functionality will be implemented', 'info');
};

const scanBarcode = () => {
  // TODO: Implement barcode scanning
  const barcode = prompt('Enter barcode:');
  if (barcode) {
    const product = products.value.find(p => p.barcode === barcode);
    if (product) {
      addToCart(product);
    } else {
      showNotification('Product not found with this barcode', 'error');
    }
  }
};

const updateDateTime = () => {
  const now = new Date();
  currentDateTime.value = now.toLocaleString();
};

const showNotification = (message, type = 'info') => {
  const notification = {
    id: Date.now(),
    message,
    type,
    show: true
  };
  notifications.value.push(notification);

  setTimeout(() => {
    const index = notifications.value.findIndex(n => n.id === notification.id);
    if (index > -1) {
      notifications.value.splice(index, 1);
    }
  }, 5000);
};

const removeNotification = (id) => {
  const index = notifications.value.findIndex(n => n.id === id);
  if (index > -1) {
    notifications.value.splice(index, 1);
  }
};

// Debounced search functions
let productSearchTimeout;
const debouncedProductSearch = () => {
  clearTimeout(productSearchTimeout);
  productSearchTimeout = setTimeout(() => {
    // Product search is handled by computed property
  }, 300);
};

let customerSearchTimeout;
const debouncedCustomerSearch = () => {
  clearTimeout(customerSearchTimeout);
  customerSearchTimeout = setTimeout(() => {
    searchCustomers(customerSearch.value);
  }, 300);
};

// Lifecycle
let dateTimeInterval;

onMounted(() => {
  fetchProducts();
  fetchCategories();
  updateDateTime();
  dateTimeInterval = setInterval(updateDateTime, 1000);

  // Set default amount paid to cart total when cart changes
  const unwatch = computed(() => cartTotal.value).watch((newTotal) => {
    if (amountPaid.value === 0 || amountPaid.value < newTotal) {
      amountPaid.value = newTotal;
    }
  });
});

onUnmounted(() => {
  if (dateTimeInterval) {
    clearInterval(dateTimeInterval);
  }
});
</script>
