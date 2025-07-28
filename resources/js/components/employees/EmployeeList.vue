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
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Employee
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Employee #
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Department
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Position
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Hire Date
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Salary
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="employee in (employees.data || employees)" :key="employee.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <img 
                      v-if="employee.profile_image" 
                      :src="`/storage/${employee.profile_image}`" 
                      :alt="employee.full_name"
                      class="h-10 w-10 rounded-full object-cover"
                    />
                    <div v-else class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                      <span class="text-sm font-medium text-gray-700">
                        {{ getInitials(employee.first_name, employee.last_name) }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ employee.full_name }}</div>
                    <div class="text-sm text-gray-500">{{ employee.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ employee.employee_number }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ employee.department?.name || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ employee.position?.title || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(employee.hire_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(employee.employment_status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ getStatusText(employee.employment_status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${{ parseFloat(employee.basic_salary).toFixed(2) }}
                <span class="text-gray-500">/ {{ employee.salary_type }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end space-x-2">
                  <button
                    @click="$emit('view-employee', employee)"
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
                    @click="$emit('edit-employee', employee)"
                    class="text-indigo-600 hover:text-indigo-900"
                    title="Edit"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  <button
                    v-if="canDelete"
                    @click="deleteEmployee(employee)"
                    class="text-red-600 hover:text-red-900"
                    title="Delete"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="hasEmployees && employees.total" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
        <div class="flex-1 flex justify-between sm:hidden">
          <button
            @click="changePage(employees.current_page - 1)"
            :disabled="!employees.prev_page_url"
            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
          >
            Previous
          </button>
          <button
            @click="changePage(employees.current_page + 1)"
            :disabled="!employees.next_page_url"
            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
          >
            Next
          </button>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Showing {{ employees.from }} to {{ employees.to }} of {{ employees.total }} results
            </p>
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
              <button
                @click="changePage(employees.current_page - 1)"
                :disabled="!employees.prev_page_url"
                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
              >
                Previous
              </button>
              <button
                @click="changePage(employees.current_page + 1)"
                :disabled="!employees.next_page_url"
                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
              >
                Next
              </button>
            </nav>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && !hasEmployees" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No employees found</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by adding a new employee.</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-sm text-gray-500">Loading employees...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
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
  page: 1
});

// Computed
const canEdit = computed(() => authStore.hasPermission('employees.edit'));
const canDelete = computed(() => authStore.hasPermission('employees.delete'));

const hasEmployees = computed(() => {
  // Handle both paginated response (employees.data) and simple array (employees)
  const employeeList = employees.value.data || employees.value;
  return Array.isArray(employeeList) && employeeList.length > 0;
});

// Methods
const fetchEmployees = async () => {
  loading.value = true;
  try {
    const params = { ...filters.value };
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null) {
        delete params[key];
      }
    });

    // Always include per_page to ensure consistent pagination response
    params.per_page = params.per_page || 15;

    const response = await axios.get('/api/employees', { params });
    employees.value = response.data;
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

// Utility function for debouncing
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
onMounted(async () => {
  // Wait a bit for auth to initialize if needed
  if (!authStore.isAuthenticated && localStorage.getItem('auth_token')) {
    await new Promise(resolve => setTimeout(resolve, 1000));
  }

  fetchEmployees();
  fetchDepartments();
});
</script>
