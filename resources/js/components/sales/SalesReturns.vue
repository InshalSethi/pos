<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Sales Returns</h1>
        <button
          @click="showCreateModal = true"
          class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium"
        >
          Process Return
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
              placeholder="Search returns..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              @input="debouncedSearch"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Original Invoice</label>
            <input
              v-model="originalInvoice"
              type="text"
              placeholder="Original invoice number..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              @input="debouncedSearch"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
            <input
              v-model="dateFrom"
              type="date"
              @change="fetchReturns"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
            <input
              v-model="dateTo"
              type="date"
              @change="fetchReturns"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>
        </div>
      </div>

      <!-- Returns Table -->
      <DataTable
        title="Sales Returns"
        subtitle="Manage and track all sales returns"
        :columns="tableColumns"
        :data="returns"
        :loading="loading"
        :pagination="pagination"
        :initial-search="searchQuery"
        :initial-per-page="perPage"
        :default-per-page="15"
        storage-key="sales-returns-table-state"
        empty-message="No returns found"
        empty-sub-message="Try adjusting your search or filter criteria."
        @search="handleTableSearch"
        @sort="handleSort"
        @page-change="handlePageChange"
        @per-page-change="handlePerPageChange"
      >
        <!-- Custom column content -->
        <template #column-return_amount="{ item }">
          ${{ Math.abs(item.total_amount) }}
        </template>

        <template #column-status="{ item }">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
            Returned
          </span>
        </template>

        <template #column-actions="{ item }">
          <div class="flex space-x-2">
            <button @click="viewReturn(item)" class="text-indigo-600 hover:text-indigo-900" title="View Return">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </button>
            <button @click="printReturn(item)" class="text-green-600 hover:text-green-900" title="Print Return">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
              </svg>
            </button>
            <button @click="deleteReturn(item.id)" class="text-red-600 hover:text-red-900" title="Delete Return">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </template>

        <!-- Action buttons in header -->
        <template #actions>
          <button
            @click="createReturn"
            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
            </svg>
            Process Return
          </button>
        </template>
      </DataTable>
    </div>

    <!-- Sales Return Modal -->
    <SalesReturnModal
      :show="showCreateModal"
      :original-sale-id="selectedOriginalSaleId"
      @close="closeModal"
      @saved="handleReturnSaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { debounce } from '@/utils/debounce';
import SalesReturnModal from './SalesReturnModal.vue';
import DataTable from '@/components/common/DataTable.vue';
import axios from 'axios';

const authStore = useAuthStore();
const route = useRoute();

// Reactive data
const returns = ref([]);
const searchQuery = ref('');
const originalInvoice = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const showCreateModal = ref(false);
const selectedOriginalSaleId = ref(null);
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
    key: 'sale_number',
    label: 'Return #',
    sortable: true,
    align: 'left',
    class: 'text-gray-500 font-mono text-xs'
  },
  {
    key: 'original_sale.sale_number',
    label: 'Original Invoice',
    sortable: true,
    align: 'left'
  },
  {
    key: 'customer.name',
    label: 'Customer',
    sortable: true,
    align: 'left'
  },
  {
    key: 'sale_date',
    label: 'Date',
    sortable: true,
    type: 'date',
    align: 'left'
  },
  {
    key: 'return_amount',
    label: 'Return Amount',
    sortable: true,
    align: 'right'
  },
  {
    key: 'status',
    label: 'Status',
    sortable: false,
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
const fetchReturns = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      search: searchQuery.value,
      original_invoice: originalInvoice.value,
      date_from: dateFrom.value,
      date_to: dateTo.value,
      is_refund: true,
      ...filters.value
    };

    // Remove empty parameters
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null) {
        delete params[key];
      }
    });

    const response = await axios.get('/api/sales', { params });
    returns.value = response.data.data;
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
    console.error('Error fetching returns:', error);
  } finally {
    loading.value = false;
  }
};

const debouncedSearch = debounce(() => {
  currentPage.value = 1;
  fetchReturns();
}, 300);

// DataTable event handlers
const handleTableSearch = (searchQuery) => {
  filters.value.search = searchQuery;
  fetchReturns(1);
};

const handleSort = (sortData) => {
  filters.value.sort_field = sortData.field;
  filters.value.sort_order = sortData.order;
  fetchReturns(1);
};

const handlePageChange = (page) => {
  fetchReturns(page);
};

const handlePerPageChange = (perPage) => {
  pagination.value.per_page = perPage;
  fetchReturns(1);
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString();
};

const viewReturn = (returnItem) => {
  // Navigate to return view
  window.open(`/sales/returns/${returnItem.id}`, '_blank');
};

const printReturn = (returnItem) => {
  // Print return receipt
  window.open(`/sales/returns/${returnItem.id}/print`, '_blank');
};

const deleteReturn = async (returnId) => {
  if (confirm('Are you sure you want to delete this return?')) {
    try {
      await axios.delete(`/api/sales/${returnId}`);
      fetchReturns();
    } catch (error) {
      console.error('Error deleting return:', error);
    }
  }
};

// Pagination methods
const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
    fetchReturns();
  }
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
    fetchReturns();
  }
};

const goToPage = (page) => {
  currentPage.value = page;
  fetchReturns();
};

// Create return method
const createReturn = () => {
  showCreateModal.value = true;
};

const closeModal = () => {
  showCreateModal.value = false;
  selectedOriginalSaleId.value = null;
};

const handleReturnSaved = () => {
  closeModal();
  fetchReturns();
};

// Check for original_invoice query parameter
const checkQueryParams = () => {
  if (route.query.original_invoice) {
    selectedOriginalSaleId.value = route.query.original_invoice;
    showCreateModal.value = true;
  }
};

// Lifecycle
onMounted(() => {
  fetchReturns();
  checkQueryParams();
});
</script>
