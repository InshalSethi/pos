<template>
  <div class="employee-list">
    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search employees..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @input="debouncedSearch"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="filters.employment_status"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @change="fetchEmployees"
          >
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="terminated">Terminated</option>
            <option value="on_leave">On Leave</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
          <select
            v-model="filters.department_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @change="fetchEmployees"
          >
            <option value="">All Departments</option>
            <option v-for="department in departments" :key="department.id" :value="department.id">
              {{ department.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Employment Type</label>
          <select
            v-model="filters.employment_type"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @change="fetchEmployees"
          >
            <option value="">All Types</option>
            <option value="full_time">Full Time</option>
            <option value="part_time">Part Time</option>
            <option value="contract">Contract</option>
            <option value="intern">Intern</option>
          </select>
        </div>
      </div>
    </div>



    <!-- Employee Table -->
    <DataTable
      title="Employees"
      subtitle="Manage your workforce and employee information"
      :columns="tableColumns"
      :data="employees.data || employees"
      :loading="loading"
      :pagination="pagination"
      :initial-search="filters.search"
      :initial-per-page="25"
      :default-per-page="25"
      storage-key="employees-table-state"
      empty-message="No employees found"
      empty-sub-message="Get started by adding your first employee."
      @search="handleTableSearch"
      @sort="handleSort"
      @page-change="handlePageChange"
      @per-page-change="handlePerPageChange"
    >
      <!-- Custom column content -->
      <template #column-employee="{ item }">
        <div class="flex items-center">
          <div class="flex-shrink-0 h-10 w-10">
            <img
              v-if="item.profile_image"
              :src="`/storage/${item.profile_image}`"
              :alt="item.full_name"
              class="h-10 w-10 rounded-full object-cover"
            />
            <div v-else class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
              <span class="text-sm font-medium text-gray-700">
                {{ getInitials(item.first_name, item.last_name) }}
              </span>
            </div>
          </div>
          <div class="ml-4">
            <div class="text-sm font-medium text-gray-900">{{ item.full_name }}</div>
            <div class="text-sm text-gray-500">{{ item.email }}</div>
          </div>
        </div>
      </template>

      <template #column-department="{ item }">
        {{ item.department?.name || '-' }}
      </template>

      <template #column-position="{ item }">
        {{ item.position?.title || '-' }}
      </template>

      <template #column-status="{ item }">
        <span :class="getStatusClass(item.employment_status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
          {{ getStatusText(item.employment_status) }}
        </span>
      </template>

      <template #column-salary="{ item }">
        <div>
          <div class="text-sm font-medium text-gray-900">${{ parseFloat(item.basic_salary).toFixed(2) }}</div>
          <div class="text-sm text-gray-500">{{ item.salary_type }}</div>
        </div>
      </template>

      <template #column-actions="{ item }">
        <div class="flex justify-end space-x-2">
          <button
            @click="$emit('view-employee', item)"
            class="text-blue-600 hover:text-blue-900"
            title="View"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
          </button>
          <button
            v-if="canEdit"
            @click="$emit('edit-employee', item)"
            class="text-indigo-600 hover:text-indigo-900"
            title="Edit"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
          </button>
          <button
            v-if="canDelete"
            @click="deleteEmployee(item)"
            class="text-red-600 hover:text-red-900"
            title="Delete"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
          </button>
        </div>
      </template>
    </DataTable>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { debounce } from '@/utils/debounce';
import DataTable from '@/components/common/DataTable.vue';
import axios from 'axios';

const authStore = useAuthStore();

// Props and Emits
const emit = defineEmits(['edit-employee', 'view-employee', 'refresh']);

// Reactive data
const employees = ref({ data: [], total: 0, current_page: 1, last_page: 1 });
const departments = ref([]);
const loading = ref(false);
const filters = ref({
  search: '',
  employment_status: '',
  department_id: '',
  employment_type: '',
  page: 1,
  sort_field: '',
  sort_order: ''
});

// DataTable pagination
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
    key: 'employee',
    label: 'Employee',
    sortable: true,
    align: 'left'
  },
  {
    key: 'employee_number',
    label: 'Employee #',
    sortable: true,
    align: 'left',
    class: 'text-gray-500 font-mono text-xs'
  },
  {
    key: 'department',
    label: 'Department',
    sortable: true,
    align: 'left'
  },
  {
    key: 'position',
    label: 'Position',
    sortable: true,
    align: 'left'
  },
  {
    key: 'hire_date',
    label: 'Hire Date',
    sortable: true,
    type: 'date',
    align: 'left'
  },
  {
    key: 'status',
    label: 'Status',
    sortable: true,
    align: 'center'
  },
  {
    key: 'salary',
    label: 'Salary',
    sortable: true,
    align: 'left'
  },
  {
    key: 'actions',
    label: 'Actions',
    sortable: false,
    align: 'right'
  }
]);

// Computed
const canEdit = computed(() => authStore.hasPermission('employees.edit'));
const canDelete = computed(() => authStore.hasPermission('employees.delete'));

const hasEmployees = computed(() => {
  // Handle both paginated response (employees.data) and simple array (employees)
  const employeeList = employees.value.data || employees.value;
  return Array.isArray(employeeList) && employeeList.length > 0;
});

// Methods
const fetchEmployees = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      ...filters.value
    };

    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null) {
        delete params[key];
      }
    });

    const response = await axios.get('/api/employees', { params });
    employees.value = response.data;

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
    console.error('Error fetching employees:', error);

    // Fallback to test endpoint if auth fails
    try {
      console.log('Trying fallback endpoint...');
      const fallbackResponse = await axios.get('/api/test-employee-list');
      employees.value = fallbackResponse.data;
      console.log('Used fallback endpoint, fetched employees:', employees.value);
      console.log('Fallback employees.data array:', employees.value.data);
      console.log('Fallback employees.data length:', employees.value.data?.length);
    } catch (fallbackError) {
      console.error('Fallback also failed:', fallbackError);
      // Set empty structure to prevent template errors
      employees.value = { data: [], total: 0, current_page: 1, last_page: 1 };
    }
  } finally {
    loading.value = false;
  }
};

const fetchDepartments = async () => {
  try {
    const response = await axios.get('/api/departments');
    departments.value = response.data;
    console.log('Fetched departments:', departments.value);
  } catch (error) {
    console.error('Error fetching departments:', error);

    // Fallback to test endpoint if auth fails
    try {
      const fallbackResponse = await axios.get('/api/test-departments');
      departments.value = fallbackResponse.data;
      console.log('Used fallback for departments:', departments.value);
    } catch (fallbackError) {
      console.error('Department fallback also failed:', fallbackError);
      departments.value = [];
    }
  }
};

const deleteEmployee = async (employee) => {
  if (!confirm('Are you sure you want to delete this employee?')) {
    return;
  }

  try {
    await axios.delete(`/api/employees/${employee.id}`);
    await fetchEmployees();
  } catch (error) {
    console.error('Error deleting employee:', error);
    if (error.response?.data?.message) {
      alert(error.response.data.message);
    }
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= employees.value.last_page) {
    filters.value.page = page;
    fetchEmployees();
  }
};

const debouncedSearch = debounce(() => {
  filters.value.page = 1;
  fetchEmployees();
}, 300);

// DataTable event handlers
const handleTableSearch = (searchQuery) => {
  filters.value.search = searchQuery;
  fetchEmployees(1);
};

const handleSort = (sortData) => {
  filters.value.sort_field = sortData.field;
  filters.value.sort_order = sortData.order;
  fetchEmployees(1);
};

const handlePageChange = (page) => {
  fetchEmployees(page);
};

const handlePerPageChange = (perPage) => {
  pagination.value.per_page = perPage;
  fetchEmployees(1);
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const getStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
    terminated: 'bg-red-100 text-red-800',
    on_leave: 'bg-yellow-100 text-yellow-800'
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusText = (status) => {
  const texts = {
    active: 'Active',
    inactive: 'Inactive',
    terminated: 'Terminated',
    on_leave: 'On Leave'
  };
  return texts[status] || status;
};

const getInitials = (firstName, lastName) => {
  return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase();
};



// Lifecycle
onMounted(async () => {
  // Wait a bit for auth to initialize if needed
  if (!authStore.isAuthenticated && localStorage.getItem('auth_token')) {
    await new Promise(resolve => setTimeout(resolve, 1000));
  }

  fetchEmployees();
  fetchDepartments();
});
</script>
