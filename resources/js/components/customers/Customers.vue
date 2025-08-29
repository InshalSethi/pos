<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div class="flex items-center space-x-4">
            <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Customer Management</h1>
              <p class="text-sm text-gray-500">Manage your customer database and relationships</p>
            </div>
          </div>
          <button
            @click="handleCreateCustomer"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Customer
          </button>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Customers</p>
              <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(statistics.total_customers || 0) }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Active Customers</p>
              <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(statistics.active_customers || 0) }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">With Purchases</p>
              <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(statistics.customers_with_purchases || 0) }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Value</p>
              <p class="text-2xl font-semibold text-gray-900">${{ formatNumber(statistics.total_customer_value || 0) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters and Search -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Search Customers</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input
                v-model="searchQuery"
                type="text"
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                placeholder="Search by name, email, phone..."
                @input="debouncedSearch"
              />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              v-model="statusFilter"
              @change="loadCustomers"
              class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All Status</option>
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Per Page</label>
            <select
              v-model="perPage"
              @change="loadCustomers"
              class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="15">15 per page</option>
              <option value="25">25 per page</option>
              <option value="50">50 per page</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Customer Cards/Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <!-- Desktop Table View -->
        <div class="hidden lg:block">
          <DataTable
            title="Customers"
            subtitle="Manage your customer database and relationships"
            :columns="tableColumns"
            :data="customers.data || []"
            :loading="loading"
            :pagination="pagination"
            :initial-search="searchQuery"
            :initial-per-page="perPage"
            :default-per-page="25"
            storage-key="customers-table-state"
            empty-message="No customers found"
            empty-sub-message="Get started by creating your first customer."
            @search="handleTableSearch"
            @sort="handleSort"
            @page-change="handlePageChange"
            @per-page-change="handlePerPageChange"
          >
            <!-- Custom column content -->
            <template #column-customer="{ item }">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10">
                  <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <span class="text-sm font-medium text-blue-600">{{ getInitials(item.name) }}</span>
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium">
                    <button
                      @click="viewLedger(item)"
                      class="text-indigo-600 hover:text-indigo-900 hover:underline cursor-pointer"
                      title="View Customer Ledger"
                    >
                      {{ item.name }}
                    </button>
                  </div>
                  <div class="text-sm text-gray-500">ID: #{{ item.id }}</div>
                </div>
              </div>
            </template>

            <template #column-contact="{ item }">
              <div>
                <div class="text-sm text-gray-900">{{ item.email || '-' }}</div>
                <div class="text-sm text-gray-500">{{ item.phone || '-' }}</div>
              </div>
            </template>

            <template #column-location="{ item }">
              <div>
                <div class="text-sm text-gray-900">{{ item.city || '-' }}</div>
                <div class="text-sm text-gray-500">{{ item.state || '-' }}</div>
              </div>
            </template>

            <template #column-purchases="{ item }">
              <div>
                <div class="text-sm font-medium text-gray-900">${{ formatNumber(item.total_purchases) }}</div>
                <div class="text-sm text-gray-500">{{ formatNumber(item.loyalty_points) }} points</div>
              </div>
            </template>

            <template #column-status="{ item }">
              <span :class="item.is_active
                ? 'inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800'
                : 'inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800'">
                {{ item.is_active ? 'Active' : 'Inactive' }}
              </span>
            </template>

            <template #column-actions="{ item }">
              <div class="flex items-center justify-end space-x-2">
                <button
                  @click="viewCustomer(item)"
                  class="text-blue-600 hover:text-blue-900 p-1 rounded-md hover:bg-blue-50"
                  title="View Details"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
                <button
                  @click="editCustomer(item)"
                  class="text-yellow-600 hover:text-yellow-900 p-1 rounded-md hover:bg-yellow-50"
                  title="Edit Customer"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <button
                  @click="deleteCustomer(item)"
                  class="text-red-600 hover:text-red-900 p-1 rounded-md hover:bg-red-50"
                  title="Delete Customer"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </template>

            <!-- Action buttons in header -->
            <template #actions>
              <button
                @click="handleCreateCustomer"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Customer
              </button>
            </template>
          </DataTable>
        </div>

        <!-- Mobile Card View -->
        <div class="lg:hidden">
          <div class="space-y-4 p-4">
            <div
              v-for="customer in customers.data"
              :key="customer.id"
              class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm"
            >
              <div class="flex items-start justify-between">
                <div class="flex items-center space-x-3">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                      <span class="text-sm font-medium text-blue-600">{{ getInitials(customer.name) }}</span>
                    </div>
                  </div>
                  <div>
                    <h3 class="text-sm font-medium">
                      <button
                        @click="viewLedger(customer)"
                        class="text-indigo-600 hover:text-indigo-900 hover:underline cursor-pointer"
                        title="View Customer Ledger"
                      >
                        {{ customer.name }}
                      </button>
                    </h3>
                    <p class="text-sm text-gray-500">{{ customer.email || 'No email' }}</p>
                  </div>
                </div>
                <span :class="customer.is_active
                  ? 'inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800'
                  : 'inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800'">
                  {{ customer.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>

              <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-gray-500">Phone:</span>
                  <span class="ml-1 text-gray-900">{{ customer.phone || '-' }}</span>
                </div>
                <div>
                  <span class="text-gray-500">City:</span>
                  <span class="ml-1 text-gray-900">{{ customer.city || '-' }}</span>
                </div>
                <div>
                  <span class="text-gray-500">Purchases:</span>
                  <span class="ml-1 text-gray-900">${{ formatNumber(customer.total_purchases) }}</span>
                </div>
                <div>
                  <span class="text-gray-500">Credit:</span>
                  <span class="ml-1 text-gray-900">${{ formatNumber(customer.credit_limit) }}</span>
                </div>
              </div>

              <div class="mt-4 flex justify-end space-x-2">
                <button
                  @click="viewCustomer(customer)"
                  class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100"
                >
                  View
                </button>
                <button
                  @click="editCustomer(customer)"
                  class="inline-flex items-center px-3 py-1 text-xs font-medium text-yellow-600 bg-yellow-50 rounded-md hover:bg-yellow-100"
                >
                  Edit
                </button>
                <button
                  @click="deleteCustomer(customer)"
                  class="inline-flex items-center px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="customers.last_page > 1" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
              <button
                @click="changePage(customers.current_page - 1)"
                :disabled="customers.current_page === 1"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Previous
              </button>
              <button
                @click="changePage(customers.current_page + 1)"
                :disabled="customers.current_page === customers.last_page"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Next
              </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Showing
                  <span class="font-medium">{{ ((customers.current_page - 1) * customers.per_page) + 1 }}</span>
                  to
                  <span class="font-medium">{{ Math.min(customers.current_page * customers.per_page, customers.total) }}</span>
                  of
                  <span class="font-medium">{{ customers.total }}</span>
                  results
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                  <button
                    @click="changePage(customers.current_page - 1)"
                    :disabled="customers.current_page === 1"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>

                  <button
                    v-for="page in visiblePages"
                    :key="page"
                    @click="changePage(page)"
                    :class="[
                      'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                      page === customers.current_page
                        ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                    ]"
                  >
                    {{ page }}
                  </button>

                  <button
                    @click="changePage(customers.current_page + 1)"
                    :disabled="customers.current_page === customers.last_page"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Customer Modals -->
    <CustomerModalSimple
      :show="showCreateModal || showEditModal"
      :customer="selectedCustomer"
      :is-edit="showEditModal"
      @close="closeModal"
      @saved="handleCustomerSaved"
    />

    <CustomerViewModalSimple
      :show="showViewModal"
      :customer="selectedCustomer"
      @close="closeModal"
    />

    <CustomerLedger
      :show="showLedgerModal"
      :customer="selectedCustomer"
      @close="closeModal"
    />
  </div>
</template>

<script>
import { ref, reactive, onMounted, computed } from 'vue';
import { debounce } from '@/utils/debounce';
import CustomerModalSimple from './CustomerModalSimple.vue';
import CustomerViewModalSimple from './CustomerViewModalSimple.vue';
import CustomerLedger from './CustomerLedger.vue';
import DataTable from '@/components/common/DataTable.vue';
import { useToast } from '@/composables/useToast';
import api from '@/services/api';

export default {
  name: 'Customers',
  components: {
    CustomerModalSimple,
    CustomerViewModalSimple,
    CustomerLedger,
    DataTable
  },
  setup() {
    const { showToast } = useToast();

    const loading = ref(false);
    const customers = ref({ data: [], current_page: 1, last_page: 1 });
    const statistics = ref({});
    const searchQuery = ref('');
    const statusFilter = ref('');
    const perPage = ref(15);

    // Pagination for DataTable
    const pagination = ref({
      current_page: 1,
      last_page: 1,
      per_page: 25,
      total: 0,
      from: 0,
      to: 0
    });

    // Table columns configuration
    const tableColumns = ref([
      {
        key: 'customer',
        label: 'Customer',
        sortable: true,
        align: 'left'
      },
      {
        key: 'contact',
        label: 'Contact',
        sortable: false,
        align: 'left'
      },
      {
        key: 'location',
        label: 'Location',
        sortable: true,
        align: 'left'
      },
      {
        key: 'purchases',
        label: 'Purchases',
        sortable: true,
        align: 'left'
      },
      {
        key: 'credit_limit',
        label: 'Credit',
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
        align: 'right'
      }
    ]);

    // DataTable filters
    const filters = ref({
      search: '',
      sort_field: '',
      sort_order: ''
    });

    const selectedCustomer = ref(null);
    const showCreateModal = ref(false);
    const showEditModal = ref(false);
    const showViewModal = ref(false);
    const showLedgerModal = ref(false);

    const loadCustomers = async (page = 1) => {
      loading.value = true;
      try {
        const params = {
          page,
          per_page: pagination.value.per_page,
          search: searchQuery.value,
          is_active: statusFilter.value,
          ...filters.value
        };

        // Remove empty parameters
        Object.keys(params).forEach(key => {
          if (params[key] === '' || params[key] === null) {
            delete params[key];
          }
        });

        const response = await api.get('/customers', { params });
        customers.value = response.data;

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
        showToast('Error loading customers: ' + (error.response?.data?.message || error.message), 'error');
      } finally {
        loading.value = false;
      }
    };

    const loadStatistics = async () => {
      try {
        const response = await api.get('/customers/statistics');
        statistics.value = response.data;
      } catch (error) {
        console.error('Error loading statistics:', error);
      }
    };

    const debouncedSearch = debounce(() => {
      loadCustomers(1);
    }, 300);

    // DataTable event handlers
    const handleTableSearch = (searchQuery) => {
      filters.value.search = searchQuery;
      loadCustomers(1);
    };

    const handleSort = (sortData) => {
      filters.value.sort_field = sortData.field;
      filters.value.sort_order = sortData.order;
      loadCustomers(1);
    };

    const handlePageChange = (page) => {
      loadCustomers(page);
    };

    const handlePerPageChange = (perPage) => {
      pagination.value.per_page = perPage;
      loadCustomers(1);
    };

    const changePage = (page) => {
      if (page >= 1 && page <= customers.value.last_page) {
        loadCustomers(page);
      }
    };

    const visiblePages = computed(() => {
      const current = customers.value.current_page;
      const last = customers.value.last_page;
      const pages = [];
      
      for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
        pages.push(i);
      }
      
      return pages;
    });

    const handleCreateCustomer = () => {
      selectedCustomer.value = null;
      showCreateModal.value = true;
    };

    const viewCustomer = (customer) => {
      selectedCustomer.value = customer;
      showViewModal.value = true;
    };

    const editCustomer = (customer) => {
      selectedCustomer.value = { ...customer };
      showEditModal.value = true;
    };

    const viewLedger = (customer) => {
      selectedCustomer.value = customer;
      showLedgerModal.value = true;
    };

    const closeModal = () => {
      showCreateModal.value = false;
      showEditModal.value = false;
      showViewModal.value = false;
      showLedgerModal.value = false;
      selectedCustomer.value = null;
    };

    const handleCustomerSaved = () => {
      loadCustomers();
      closeModal();
    };

    const deleteCustomer = async (customer) => {
      if (confirm(`Are you sure you want to delete customer "${customer.name}"?`)) {
        try {
          await api.delete(`/customers/${customer.id}`);
          showToast('Customer deleted successfully', 'success');
          loadCustomers(customers.value.current_page);
          loadStatistics();
        } catch (error) {
          showToast(error.response?.data?.message || 'Error deleting customer', 'error');
        }
      }
    };

    // Modal functions now handled by Vue Final Modal

    const formatNumber = (value) => {
      return new Intl.NumberFormat().format(value || 0);
    };

    const getInitials = (name) => {
      if (!name) return '?';
      return name
        .split(' ')
        .map(word => word.charAt(0).toUpperCase())
        .slice(0, 2)
        .join('');
    };

    onMounted(() => {
      loadCustomers();
      loadStatistics();
    });

    return {
      loading,
      customers,
      statistics,
      searchQuery,
      statusFilter,
      perPage,
      pagination,
      tableColumns,
      filters,
      selectedCustomer,
      showCreateModal,
      showEditModal,
      showViewModal,
      showLedgerModal,
      visiblePages,
      loadCustomers,
      debouncedSearch,
      changePage,
      handleTableSearch,
      handleSort,
      handlePageChange,
      handlePerPageChange,
      handleCreateCustomer,
      viewCustomer,
      editCustomer,
      viewLedger,
      deleteCustomer,
      closeModal,
      handleCustomerSaved,
      formatNumber,
      getInitials
    };
  }
};
</script>

<style scoped>
/* Custom animations and transitions */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Custom scrollbar for tables */
.overflow-x-auto::-webkit-scrollbar {
  height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Hover effects for interactive elements */
.hover-lift {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-lift:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Focus styles for accessibility */
.focus-ring:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
}

/* Loading animation */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Mobile responsive adjustments */
@media (max-width: 640px) {
  .mobile-padding {
    padding-left: 1rem;
    padding-right: 1rem;
  }
}

/* Print styles */
@media print {
  .no-print {
    display: none !important;
  }

  .print-full-width {
    width: 100% !important;
  }
}
</style>
