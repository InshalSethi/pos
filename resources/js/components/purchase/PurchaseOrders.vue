<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Purchase Orders</h1>
        <router-link
          to="/purchase/orders/create"
          class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Create Purchase Order
        </router-link>
      </div>

      <!-- Filters -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
      <DataTable
        title="Purchase Orders"
        subtitle="Manage and track all purchase orders"
        :columns="tableColumns"
        :data="purchaseOrders"
        :loading="loading"
        :pagination="pagination"
        :initial-search="searchQuery"
        :initial-per-page="perPage"
        :default-per-page="15"
        storage-key="purchase-orders-table-state"
        empty-message="No purchase orders found"
        empty-sub-message="Get started by creating your first purchase order."
        @search="handleTableSearch"
        @sort="handleSort"
        @page-change="handlePageChange"
        @per-page-change="handlePerPageChange"
      >
        <!-- Custom column content -->
        <template #column-expected_delivery="{ item }">
          {{ item.expected_delivery_date ? formatDate(item.expected_delivery_date) : 'N/A' }}
        </template>

        <template #column-status="{ item }">
          <span :class="getStatusClass(item.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
            {{ formatStatus(item.status) }}
          </span>
        </template>

        <template #column-actions="{ item }">
          <div class="flex space-x-2">
            <button @click="viewOrder(item)" class="text-indigo-600 hover:text-indigo-900" title="View Order">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </button>
            <button @click="editOrder(item)" class="text-green-600 hover:text-green-900" title="Edit Order">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>
            <button v-if="item.status === 'confirmed'" @click="receiveOrder(item)" class="text-blue-600 hover:text-blue-900" title="Receive Order">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </button>
            <button @click="deleteOrder(item.id)" class="text-red-600 hover:text-red-900" title="Delete Order">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </template>

        <!-- Action buttons in header -->
        <template #actions>
          <router-link
            to="/purchase/orders/create"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Create Purchase Order
          </router-link>
        </template>
      </DataTable>
      </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { debounce } from '@/utils/debounce';
import DataTable from '@/components/common/DataTable.vue';
import axios from 'axios';

const router = useRouter();
const authStore = useAuthStore();

// Reactive data
const purchaseOrders = ref([]);
const searchQuery = ref('');
const selectedStatus = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const loading = ref(false);

// Pagination
const currentPage = ref(1);
const perPage = ref(15);
const totalItems = ref(0);
const totalPages = computed(() => Math.ceil(totalItems.value / perPage.value));

// DataTable pagination
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
});

// Table columns configuration
const tableColumns = ref([
  {
    key: 'po_number',
    label: 'PO #',
    sortable: true,
    align: 'left',
    class: 'text-gray-500 font-mono text-xs'
  },
  {
    key: 'supplier.name',
    label: 'Supplier',
    sortable: true,
    align: 'left'
  },
  {
    key: 'order_date',
    label: 'Order Date',
    sortable: true,
    type: 'date',
    align: 'left'
  },
  {
    key: 'expected_delivery',
    label: 'Expected Delivery',
    sortable: true,
    align: 'left'
  },
  {
    key: 'total_amount',
    label: 'Total Amount',
    sortable: true,
    type: 'currency',
    align: 'right'
  },
  {
    key: 'status',
    label: 'Status',
    sortable: true,
    align: 'center'
  },
  {
    key: 'actions',
    label: 'Actions',
    sortable: false,
    align: 'left'
  }
]);

// Table filters
const filters = ref({
  search: '',
  sort_field: '',
  sort_order: ''
});

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
const fetchPurchaseOrders = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      search: searchQuery.value,
      status: selectedStatus.value,
      date_from: dateFrom.value,
      date_to: dateTo.value,
      ...filters.value
    };

    // Remove empty parameters
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null) {
        delete params[key];
      }
    });

    const response = await axios.get('/api/purchase-orders', { params });
    purchaseOrders.value = response.data.data;
    totalItems.value = response.data.total;

    // Update pagination
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
      from: response.data.from,
      to: response.data.to
    };
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

// DataTable event handlers
const handleTableSearch = (searchQuery) => {
  filters.value.search = searchQuery;
  fetchPurchaseOrders(1);
};

const handleSort = (sortData) => {
  filters.value.sort_field = sortData.field;
  filters.value.sort_order = sortData.order;
  fetchPurchaseOrders(1);
};

const handlePageChange = (page) => {
  fetchPurchaseOrders(page);
};

const handlePerPageChange = (perPage) => {
  pagination.value.per_page = perPage;
  fetchPurchaseOrders(1);
};

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
  router.push(`/purchase/orders/${order.id}`);
};

const editOrder = (order) => {
  router.push(`/purchase/orders/${order.id}/edit`);
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





// Lifecycle
onMounted(() => {
  fetchPurchaseOrders();
});
</script>
