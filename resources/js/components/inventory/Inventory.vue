<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Inventory Management</h1>
          <div class="flex space-x-3">
            <button
              @click="activeTab = 'adjustments'"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium',
                activeTab === 'adjustments'
                  ? 'bg-indigo-600 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50'
              ]"
            >
              Stock Adjustments
            </button>
            <button
              @click="activeTab = 'purchase-orders'"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium',
                activeTab === 'purchase-orders'
                  ? 'bg-indigo-600 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50'
              ]"
            >
              Purchase Orders
            </button>
            <button
              @click="activeTab = 'suppliers'"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium',
                activeTab === 'suppliers'
                  ? 'bg-indigo-600 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50'
              ]"
            >
              Suppliers
            </button>
            <button
              @click="activeTab = 'low-stock'"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium',
                activeTab === 'low-stock'
                  ? 'bg-red-600 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50'
              ]"
            >
              Low Stock Alert
            </button>
          </div>
        </div>

        <!-- Inventory Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Adjustments</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ summary.total_adjustments || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Stock Increases</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ summary.total_increases || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Stock Decreases</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ summary.total_decreases || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Low Stock Items</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ summary.low_stock_products || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab Content -->
        <div class="bg-white shadow rounded-lg">
          <!-- Stock Adjustments Tab -->
          <div v-if="activeTab === 'adjustments'" class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-lg font-medium text-gray-900">Stock Adjustments</h2>
              <button
                @click="showAdjustmentModal = true"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium"
              >
                New Adjustment
              </button>
            </div>

            <!-- Adjustments List -->
            <div v-if="loadingAdjustments" class="text-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
              <p class="mt-2 text-gray-600">Loading adjustments...</p>
            </div>

            <div v-else-if="adjustments.length === 0" class="text-center py-8 text-gray-500">
              No adjustments found.
            </div>

            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Adjustment #
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Product
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Type
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Quantity
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Reason
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Date
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="adjustment in adjustments" :key="adjustment.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ adjustment.adjustment_number }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ adjustment.product?.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        :class="[
                          'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                          adjustment.adjustment_type === 'increase'
                            ? 'bg-green-100 text-green-800'
                            : adjustment.adjustment_type === 'decrease'
                            ? 'bg-red-100 text-red-800'
                            : 'bg-blue-100 text-blue-800'
                        ]"
                      >
                        {{ adjustment.adjustment_type_display }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ adjustment.quantity_adjusted }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ adjustment.reason }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatDate(adjustment.adjustment_date) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Low Stock Tab -->
          <div v-if="activeTab === 'low-stock'" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Low Stock Alert</h2>

            <div v-if="loadingLowStock" class="text-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
              <p class="mt-2 text-gray-600">Loading low stock products...</p>
            </div>

            <div v-else-if="lowStockProducts.length === 0" class="text-center py-8 text-gray-500">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <p class="mt-2">All products are well stocked!</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div
                v-for="product in lowStockProducts"
                :key="product.id"
                class="bg-red-50 border border-red-200 rounded-lg p-4"
              >
                <div class="flex items-center justify-between mb-2">
                  <h3 class="font-medium text-gray-900">{{ product.name }}</h3>
                  <span class="text-xs text-red-600 bg-red-100 px-2 py-1 rounded">
                    Low Stock
                  </span>
                </div>
                <p class="text-sm text-gray-600 mb-2">SKU: {{ product.sku }}</p>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">
                    Current: {{ product.stock_quantity }}
                  </span>
                  <span class="text-sm text-gray-600">
                    Min: {{ product.min_stock_level }}
                  </span>
                </div>
                <button
                  @click="quickAdjustStock(product)"
                  class="mt-2 w-full bg-red-600 text-white py-1 px-3 rounded text-sm hover:bg-red-700"
                >
                  Quick Adjust
                </button>
              </div>
            </div>
          </div>

          <!-- Other tabs content will be added here -->
          <div v-if="activeTab === 'purchase-orders'" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Purchase Orders</h2>
            <p class="text-gray-600">Purchase orders management will be implemented here.</p>
          </div>

          <div v-if="activeTab === 'suppliers'" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Suppliers</h2>
            <p class="text-gray-600">Supplier management will be implemented here.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Stock Adjustment Modal -->
    <div v-if="showAdjustmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Stock Adjustment</h3>

          <form @submit.prevent="createAdjustment">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Product *</label>
              <select
                v-model="adjustmentForm.product_id"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="">Select Product</option>
                <option v-for="product in products" :key="product.id" :value="product.id">
                  {{ product.name }} (Current: {{ product.stock_quantity }})
                </option>
              </select>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Adjustment Type *</label>
              <select
                v-model="adjustmentForm.adjustment_type"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="">Select Type</option>
                <option value="increase">Stock Increase</option>
                <option value="decrease">Stock Decrease</option>
                <option value="recount">Stock Recount</option>
              </select>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                {{ adjustmentForm.adjustment_type === 'recount' ? 'New Quantity *' : 'Quantity *' }}
              </label>
              <input
                v-model.number="adjustmentForm.quantity_adjusted"
                type="number"
                min="0"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Reason *</label>
              <select
                v-model="adjustmentForm.reason"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="">Select Reason</option>
                <option value="Damaged goods">Damaged goods</option>
                <option value="Expired products">Expired products</option>
                <option value="Theft/Loss">Theft/Loss</option>
                <option value="Supplier return">Supplier return</option>
                <option value="Physical count correction">Physical count correction</option>
                <option value="System error correction">System error correction</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
              <textarea
                v-model="adjustmentForm.notes"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              ></textarea>
            </div>

            <div v-if="adjustmentFormErrors.length > 0" class="mb-4">
              <div class="bg-red-50 border border-red-200 rounded-md p-3">
                <ul class="text-sm text-red-600">
                  <li v-for="error in adjustmentFormErrors" :key="error">{{ error }}</li>
                </ul>
              </div>
            </div>

            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="closeAdjustmentModal"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="creatingAdjustment"
                class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ creatingAdjustment ? 'Creating...' : 'Create Adjustment' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Reactive data
const activeTab = ref('adjustments');
const summary = ref({});
const adjustments = ref([]);
const lowStockProducts = ref([]);
const products = ref([]);
const loadingAdjustments = ref(false);
const loadingLowStock = ref(false);
const showAdjustmentModal = ref(false);
const creatingAdjustment = ref(false);
const adjustmentFormErrors = ref([]);

// Adjustment form
const adjustmentForm = ref({
  product_id: '',
  adjustment_type: '',
  quantity_adjusted: '',
  reason: '',
  notes: ''
});

// Methods
const fetchSummary = async () => {
  try {
    const response = await axios.get('/api/inventory/summary');
    summary.value = response.data;
  } catch (error) {
    console.error('Error fetching inventory summary:', error);
  }
};

const fetchAdjustments = async () => {
  loadingAdjustments.value = true;
  try {
    const response = await axios.get('/api/inventory-adjustments');
    adjustments.value = response.data.data;
  } catch (error) {
    console.error('Error fetching adjustments:', error);
  } finally {
    loadingAdjustments.value = false;
  }
};

const fetchLowStockProducts = async () => {
  loadingLowStock.value = true;
  try {
    const response = await axios.get('/api/inventory/low-stock');
    lowStockProducts.value = response.data.data;
  } catch (error) {
    console.error('Error fetching low stock products:', error);
  } finally {
    loadingLowStock.value = false;
  }
};

const fetchProducts = async () => {
  try {
    const response = await axios.get('/api/products', {
      params: { per_page: 100, is_active: true }
    });
    products.value = response.data.data;
  } catch (error) {
    console.error('Error fetching products:', error);
  }
};

const createAdjustment = async () => {
  creatingAdjustment.value = true;
  adjustmentFormErrors.value = [];

  try {
    await axios.post('/api/inventory-adjustments', adjustmentForm.value);

    closeAdjustmentModal();
    fetchAdjustments();
    fetchSummary();
    fetchLowStockProducts();

    // Refresh products to show updated stock
    fetchProducts();

  } catch (error) {
    if (error.response?.data?.errors) {
      adjustmentFormErrors.value = Object.values(error.response.data.errors).flat();
    } else {
      adjustmentFormErrors.value = [error.response?.data?.message || 'An error occurred'];
    }
  } finally {
    creatingAdjustment.value = false;
  }
};

const closeAdjustmentModal = () => {
  showAdjustmentModal.value = false;
  adjustmentForm.value = {
    product_id: '',
    adjustment_type: '',
    quantity_adjusted: '',
    reason: '',
    notes: ''
  };
  adjustmentFormErrors.value = [];
};

const quickAdjustStock = (product) => {
  adjustmentForm.value = {
    product_id: product.id,
    adjustment_type: 'increase',
    quantity_adjusted: product.min_stock_level - product.stock_quantity,
    reason: 'Low stock replenishment',
    notes: `Quick adjustment to bring stock to minimum level`
  };
  showAdjustmentModal.value = true;
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString();
};

// Lifecycle
onMounted(() => {
  fetchSummary();
  fetchAdjustments();
  fetchLowStockProducts();
  fetchProducts();
});
</script>
