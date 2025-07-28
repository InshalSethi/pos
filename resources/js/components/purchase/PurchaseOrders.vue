<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Purchase Orders</h1>
        <button
          @click="showCreateModal = true"
          class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium"
        >
          Create Purchase Order
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search purchase orders..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              @input="debouncedSearch"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              v-model="selectedStatus"
              @change="fetchPurchaseOrders"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="">All Status</option>
              <option value="draft">Draft</option>
              <option value="sent">Sent</option>
              <option value="confirmed">Confirmed</option>
              <option value="partially_received">Partially Received</option>
              <option value="received">Received</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
            <input
              v-model="dateFrom"
              type="date"
              @change="fetchPurchaseOrders"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
            <input
              v-model="dateTo"
              type="date"
              @change="fetchPurchaseOrders"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>
        </div>
      </div>

      <!-- Purchase Orders Table -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO #</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected Delivery</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="order in purchaseOrders" :key="order.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ order.po_number }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ order.supplier?.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(order.order_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ order.expected_delivery_date ? formatDate(order.expected_delivery_date) : 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  ${{ order.total_amount }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(order.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ formatStatus(order.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button @click="viewOrder(order)" class="text-indigo-600 hover:text-indigo-900 mr-3">View</button>
                  <button @click="editOrder(order)" class="text-green-600 hover:text-green-900 mr-3">Edit</button>
                  <button v-if="order.status === 'confirmed'" @click="receiveOrder(order)" class="text-blue-600 hover:text-blue-900 mr-3">Receive</button>
                  <button @click="deleteOrder(order.id)" class="text-red-600 hover:text-red-900">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
              @click="previousPage"
              :disabled="currentPage === 1"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
            >
              Previous
            </button>
            <button
              @click="nextPage"
              :disabled="currentPage === totalPages"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
            >
              Next
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Showing {{ ((currentPage - 1) * perPage) + 1 }} to {{ Math.min(currentPage * perPage, totalItems) }} of {{ totalItems }} results
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <button
                  @click="previousPage"
                  :disabled="currentPage === 1"
                  class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                >
                  Previous
                </button>
                <button
                  v-for="page in visiblePages"
                  :key="page"
                  @click="goToPage(page)"
                  :class="[
                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                    page === currentPage
                      ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                      : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                  ]"
                >
                  {{ page }}
                </button>
                <button
                  @click="nextPage"
                  :disabled="currentPage === totalPages"
                  class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                >
                  Next
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Purchase Order Modal -->
    <PurchaseOrderModal
      :show="showCreateModal || showEditModal"
      :order="selectedOrder"
      :is-edit="showEditModal"
      @close="closeModal"
      @saved="handleOrderSaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import PurchaseOrderModal from './PurchaseOrderModal.vue';
import axios from 'axios';

const authStore = useAuthStore();

// Reactive data
const purchaseOrders = ref([]);
const searchQuery = ref('');
const selectedStatus = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const selectedOrder = ref(null);
const loading = ref(false);

// Pagination
const currentPage = ref(1);
const perPage = ref(15);
const totalItems = ref(0);
const totalPages = computed(() => Math.ceil(totalItems.value / perPage.value));

// Computed
const visiblePages = computed(() => {
  const pages = [];
  const start = Math.max(1, currentPage.value - 2);
  const end = Math.min(totalPages.value, currentPage.value + 2);
  
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  
  return pages;
});

// Methods
const fetchPurchaseOrders = async () => {
  loading.value = true;
  try {
    const params = {
      page: currentPage.value,
      per_page: perPage.value,
      search: searchQuery.value,
      status: selectedStatus.value,
      date_from: dateFrom.value,
      date_to: dateTo.value,
    };

    const response = await axios.get('/api/purchase-orders', { params });
    purchaseOrders.value = response.data.data;
    totalItems.value = response.data.total;
  } catch (error) {
    console.error('Error fetching purchase orders:', error);
  } finally {
    loading.value = false;
  }
};

const debouncedSearch = debounce(() => {
  currentPage.value = 1;
  fetchPurchaseOrders();
}, 300);

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString();
};

const formatStatus = (status) => {
  return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    sent: 'bg-blue-100 text-blue-800',
    confirmed: 'bg-yellow-100 text-yellow-800',
    partially_received: 'bg-orange-100 text-orange-800',
    received: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800'
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const viewOrder = (order) => {
  // Navigate to order view
  window.open(`/purchase/orders/${order.id}`, '_blank');
};

const editOrder = (order) => {
  selectedOrder.value = order;
  showEditModal.value = true;
};

const receiveOrder = (order) => {
  // Navigate to receive order page
  window.location.href = `/purchase/orders/${order.id}/receive`;
};

const deleteOrder = async (orderId) => {
  if (confirm('Are you sure you want to delete this purchase order?')) {
    try {
      await axios.delete(`/api/purchase-orders/${orderId}`);
      fetchPurchaseOrders();
    } catch (error) {
      console.error('Error deleting purchase order:', error);
    }
  }
};

// Pagination methods
const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
    fetchPurchaseOrders();
  }
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
    fetchPurchaseOrders();
  }
};

const goToPage = (page) => {
  currentPage.value = page;
  fetchPurchaseOrders();
};

// Modal functions
const closeModal = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  selectedOrder.value = null;
};

const handleOrderSaved = () => {
  closeModal();
  fetchPurchaseOrders();
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
  fetchPurchaseOrders();
});
</script>
