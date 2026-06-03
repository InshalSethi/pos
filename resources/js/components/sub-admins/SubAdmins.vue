<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div class="flex items-center space-x-4">
            <div class="flex items-center justify-center w-12 h-12 bg-indigo-100 rounded-lg">
              <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A10.003 10.003 0 0012 20a10.003 10.003 0 006.206-2.13l.054.09A9.99 9.99 0 0112 22a9.99 9.99 0 01-6.206-2.13zM12 11c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
              </svg>
            </div>
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Admin Management</h1>
              <p class="text-sm text-gray-500">Manage your admin  and their permissions</p>
            </div>
          </div>
          <button
            @click="handleCreateSubAdmin"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Admin
          </button>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Admins</p>
              <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(statistics.total_sub_admins || 0) }}</p>
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
              <p class="text-sm font-medium text-gray-500">Active Admins</p>
              <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(statistics.active_sub_admins || 0) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters and Search -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Search Admins</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input
                v-model="searchQuery"
                type="text"
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Search by name, email..."
                @input="debouncedSearch"
              />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              v-model="statusFilter"
              @change="loadSubAdmins"
              class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
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
              @change="loadSubAdmins"
              class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="15">15 per page</option>
              <option value="25">25 per page</option>
              <option value="50">50 per page</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Sub Admin Cards/Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <!-- Desktop Table View -->
        <div class="hidden lg:block">
          <DataTable
            title="Admins"
            subtitle="Manage your admin team"
            :columns="tableColumns"
            :data="subAdmins.data || []"
            :loading="loading"
            :pagination="pagination"
            :initial-search="searchQuery"
            :initial-per-page="perPage"
            :default-per-page="25"
            storage-key="sub-admins-table-state"
            empty-message="No admins found"
            empty-sub-message="Get started by creating your first admin."
            @search="handleTableSearch"
            @sort="handleSort"
            @page-change="handlePageChange"
            @per-page-change="handlePerPageChange"
          >
            <!-- Custom column content -->
            <template #column-subAdmin="{ item }">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10">
                  <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                    <span class="text-sm font-medium text-indigo-600">{{ getInitials(item.name) }}</span>
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">
                    {{ item.name }}
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

            <template #column-role="{ item }">
              <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                {{ item.role_name || 'Sub Admin' }}
              </span>
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
                  @click="viewSubAdmin(item)"
                  class="text-blue-600 hover:text-blue-900 p-1 rounded-md hover:bg-blue-50"
                  title="View Details"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
                <button
                  @click="editSubAdmin(item)"
                  class="text-yellow-600 hover:text-yellow-900 p-1 rounded-md hover:bg-yellow-50"
                  title="Edit Sub Admin"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <button
                  @click="deleteSubAdmin(item)"
                  class="text-red-600 hover:text-red-900 p-1 rounded-md hover:bg-red-50"
                  title="Delete Sub Admin"
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
                @click="handleCreateSubAdmin"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Admin
              </button>
            </template>
          </DataTable>
        </div>

        <!-- Mobile Card View -->
        <div class="lg:hidden">
          <div class="space-y-4 p-4">
            <div
              v-for="subAdmin in subAdmins.data"
              :key="subAdmin.id"
              class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm"
            >
              <div class="flex items-start justify-between">
                <div class="flex items-center space-x-3">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                      <span class="text-sm font-medium text-indigo-600">{{ getInitials(subAdmin.name) }}</span>
                    </div>
                  </div>
                  <div>
                    <h3 class="text-sm font-medium text-gray-900">{{ subAdmin.name }}</h3>
                    <p class="text-sm text-gray-500">{{ subAdmin.email || 'No email' }}</p>
                  </div>
                </div>
                <span :class="subAdmin.is_active
                  ? 'inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800'
                  : 'inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800'">
                  {{ subAdmin.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>

              <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-gray-500">Phone:</span>
                  <span class="ml-1 text-gray-900">{{ subAdmin.phone || '-' }}</span>
                </div>
                <div>
                  <span class="text-gray-500">Role:</span>
                  <span class="ml-1 text-gray-900">{{ subAdmin.role_name || 'Sub Admin' }}</span>
                </div>
              </div>

              <div class="mt-4 flex justify-end space-x-2">
                <button
                  @click="viewSubAdmin(subAdmin)"
                  class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100"
                >
                  View
                </button>
                <button
                  @click="editSubAdmin(subAdmin)"
                  class="inline-flex items-center px-3 py-1 text-xs font-medium text-yellow-600 bg-yellow-50 rounded-md hover:bg-yellow-100"
                >
                  Edit
                </button>
                <button
                  @click="deleteSubAdmin(subAdmin)"
                  class="inline-flex items-center px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>


        <!-- Pagination -->
        <div v-if="subAdmins.last_page > 1" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
              <button
                @click="changePage(subAdmins.current_page - 1)"
                :disabled="subAdmins.current_page === 1"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Previous
              </button>
              <button
                @click="changePage(subAdmins.current_page + 1)"
                :disabled="subAdmins.current_page === subAdmins.last_page"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Next
              </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Showing
                  <span class="font-medium">{{ ((subAdmins.current_page - 1) * subAdmins.per_page) + 1 }}</span>
                  to
                  <span class="font-medium">{{ Math.min(subAdmins.current_page * subAdmins.per_page, subAdmins.total) }}</span>
                  of
                  <span class="font-medium">{{ subAdmins.total }}</span>
                  results
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                  <button
                    @click="changePage(subAdmins.current_page - 1)"
                    :disabled="subAdmins.current_page === 1"
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
                      page === subAdmins.current_page
                        ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                    ]"
                  >
                    {{ page }}
                  </button>

                  <button
                    @click="changePage(subAdmins.current_page + 1)"
                    :disabled="subAdmins.current_page === subAdmins.last_page"
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

    <!-- Sub Admin Create/Edit Modal -->
    <SubAdminsCreateForm
      :show="showCreateModal || showEditModal"
      :sub-admin="selectedSubAdmin"
      :is-edit="showEditModal"
      @close="closeModal"
      @saved="handleSubAdminSaved"
    />
    <SubAdminViewModal 
      :show="showViewModal" 
      :sub-admin="selectedSubAdmin" 
      @close="closeModal" 
    />
  </div>
</template>

<script>
import { ref, reactive, onMounted, computed } from 'vue';
import { debounce } from '@/utils/debounce';
import SubAdminsCreateForm from './SubAdminsCreateForm.vue';
import DataTable from '@/components/common/DataTable.vue';
import { useToast } from '@/composables/useToast';
import api from '@/services/api';
import SubAdminViewModal from './SubAdminViewModal.vue';

export default {
  name: 'SubAdmins',
  components: {
    SubAdminsCreateForm,
    DataTable,
    SubAdminViewModal,
  },
  setup() {
    const { showToast } = useToast();

    const loading = ref(false);
    const subAdmins = ref({ data: [], current_page: 1, last_page: 1 });
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
        key: 'subAdmin',
        label: 'Sub Admin',
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
        key: 'role',
        label: 'Role',
        sortable: true,
        align: 'left'
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

    const selectedSubAdmin = ref(null);
    const showCreateModal = ref(false);
    const showEditModal = ref(false);
    const showViewModal = ref(false);

    const loadSubAdmins = async (page = 1) => {
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

        const response = await api.get('/sub-admins', { params });
        subAdmins.value = response.data;

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
        showToast('Error loading sub admins: ' + (error.response?.data?.message || error.message), 'error');
      } finally {
        loading.value = false;
      }
    };

    const loadStatistics = async () => {
      try {
        const response = await api.get('/sub-admins/statistics');
        statistics.value = response.data;
      } catch (error) {
        console.error('Error loading statistics:', error);
      }
    };

    const debouncedSearch = debounce(() => {
      loadSubAdmins(1);
    }, 300);

    // DataTable event handlers
    const handleTableSearch = (query) => {
      searchQuery.value = query;
      loadSubAdmins(1);
    };

    const handleSort = (sortData) => {
      filters.value.sort_field = sortData.field;
      filters.value.sort_order = sortData.order;
      loadSubAdmins(1);
    };

    const handlePageChange = (page) => {
      loadSubAdmins(page);
    };

    const handlePerPageChange = (size) => {
      pagination.value.per_page = size;
      loadSubAdmins(1);
    };

    const changePage = (page) => {
      if (page >= 1 && page <= subAdmins.value.last_page) {
        loadSubAdmins(page);
      }
    };

    const visiblePages = computed(() => {
      const current = subAdmins.value.current_page;
      const last = subAdmins.value.last_page;
      const pages = [];
      
      for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
        pages.push(i);
      }
      
      return pages;
    });

    const handleCreateSubAdmin = () => {
      selectedSubAdmin.value = null;
      showCreateModal.value = true;
    };

    const viewSubAdmin = (subAdmin) => {
      selectedSubAdmin.value = subAdmin;
      showViewModal.value = true;
    };

    const editSubAdmin = (subAdmin) => {
      selectedSubAdmin.value = { ...subAdmin };
      showEditModal.value = true;
    };

    const closeModal = () => {
      showCreateModal.value = false;
      showEditModal.value = false;
      showViewModal.value = false;
      selectedSubAdmin.value = null;
    }; 

    const handleSubAdminSaved = async () => {
      // 1. Refresh Table Data
      await loadSubAdmins(pagination.value.current_page);
      
      // 2. Refresh Dashboard Statistics
      await loadStatistics();
      
      // 3. Update Selected Admin (View Sync)
      if (selectedSubAdmin.value) {
        const freshData = subAdmins.value.data.find(u => u.id === selectedSubAdmin.value.id);
        if (freshData) {
          selectedSubAdmin.value = { ...freshData };
        }
      }
      
      // 4. Reset UI
      closeModal();
      
      // 5. User Feedback
      showToast('Administrative data synchronized successfully', 'success');
      
      // 6. Permission & Session Sync (Critical for Sidebar Menu)
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    };

const deleteSubAdmin = async (subAdmin) => {
    if (confirm(`Are you sure you want to delete sub admin "${subAdmin.name}"?`)) {
        try {
            await api.delete(`/sub-admins/${subAdmin.id}`);
            showToast('Admin deleted successfully', 'success');
            
            
            await loadSubAdmins(pagination.value.current_page);
            await loadStatistics();
        } catch (error) {
            showToast(error.response?.data?.message || 'Error deleting sub admin', 'error');
        }
    }
};
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
      loadSubAdmins();
      loadStatistics();
    });

    return {
      loading,
      subAdmins,
      statistics,
      searchQuery,
      statusFilter,
      perPage,
      pagination,
      tableColumns,
      filters,
      selectedSubAdmin,
      showCreateModal,
      showEditModal,
      showViewModal,
      visiblePages,
      loadSubAdmins,
      debouncedSearch,
      changePage,
      handleTableSearch,
      handleSort,
      handlePageChange,
      handlePerPageChange,
      handleCreateSubAdmin,
      viewSubAdmin,
      editSubAdmin,
      deleteSubAdmin,
      closeModal,
      handleSubAdminSaved,
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

/* Hover effects */
.hover-lift {
  transition: transform 0.2s ease;
}

.hover-lift:hover {
  transform: translateY(-2px);
}
</style>
