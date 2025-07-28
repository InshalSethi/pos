<template>
  <div class="employee-user-management">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h2 class="text-xl font-bold text-gray-900">User Account Management</h2>
        <p class="text-gray-600">Manage system access for employees</p>
      </div>
      <div class="flex space-x-3">
        <button
          @click="fetchAuditData"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
          Audit
        </button>
        <button
          @click="showBulkCreateModal = true"
          class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Bulk Create Accounts
        </button>
      </div>
    </div>

    <!-- Audit Summary Cards -->
    <div v-if="auditData" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Employees Without Accounts</dt>
                <dd class="text-lg font-medium text-gray-900">{{ auditData.employees_without_users || 0 }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Users Without Employees</dt>
                <dd class="text-lg font-medium text-gray-900">{{ auditData.users_without_employees || 0 }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Inactive with Active Users</dt>
                <dd class="text-lg font-medium text-gray-900">{{ auditData.inactive_employees_with_active_users || 0 }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Mismatched Emails</dt>
                <dd class="text-lg font-medium text-gray-900">{{ auditData.mismatched_emails || 0 }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Employees Without User Accounts -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Employees Without User Accounts</h3>
        <p class="text-sm text-gray-500">Active employees who don't have system access</p>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <input
                  type="checkbox"
                  v-model="selectAll"
                  @change="toggleSelectAll"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Employee
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
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="employee in employeesWithoutAccounts" :key="employee.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <input
                  type="checkbox"
                  v-model="selectedEmployees"
                  :value="employee.id"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ employee.full_name }}</div>
                <div class="text-sm text-gray-500">{{ employee.email }}</div>
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
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button
                  @click="createUserAccount(employee)"
                  class="text-green-600 hover:text-green-900"
                  title="Create User Account"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && employeesWithoutAccounts.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">All employees have user accounts</h3>
        <p class="mt-1 text-sm text-gray-500">Every active employee has been assigned a system user account.</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-sm text-gray-500">Loading employees...</p>
      </div>
    </div>

    <!-- Bulk Create Modal -->
    <div v-if="showBulkCreateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Create User Accounts</h3>
        <p class="text-sm text-gray-600 mb-4">
          Create user accounts for {{ selectedEmployees.length }} selected employees?
        </p>
        <div class="flex justify-end space-x-3">
          <button
            @click="showBulkCreateModal = false"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="bulkCreateUserAccounts"
            :disabled="selectedEmployees.length === 0 || bulkCreating"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50"
          >
            {{ bulkCreating ? 'Creating...' : 'Create Accounts' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

// Reactive data
const employeesWithoutAccounts = ref([]);
const auditData = ref(null);
const loading = ref(false);
const selectedEmployees = ref([]);
const selectAll = ref(false);
const showBulkCreateModal = ref(false);
const bulkCreating = ref(false);

// Methods
const fetchEmployeesWithoutAccounts = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/employees/without-accounts');
    employeesWithoutAccounts.value = response.data;
  } catch (error) {
    console.error('Error fetching employees without accounts:', error);
  } finally {
    loading.value = false;
  }
};

const fetchAuditData = async () => {
  try {
    const response = await axios.get('/api/employees/audit-user-relationships');
    auditData.value = response.data;
  } catch (error) {
    console.error('Error fetching audit data:', error);
  }
};

const createUserAccount = async (employee) => {
  if (!confirm(`Create user account for ${employee.full_name}?`)) {
    return;
  }

  try {
    await axios.post(`/api/employees/${employee.id}/create-user-account`);
    await fetchEmployeesWithoutAccounts();
    await fetchAuditData();
    alert('User account created successfully!');
  } catch (error) {
    console.error('Error creating user account:', error);
    if (error.response?.data?.message) {
      alert(error.response.data.message);
    }
  }
};

const bulkCreateUserAccounts = async () => {
  bulkCreating.value = true;
  try {
    const response = await axios.post('/api/employees/bulk-create-user-accounts', {
      employee_ids: selectedEmployees.value
    });

    const results = response.data.results;
    const successful = results.filter(r => r.success).length;
    const failed = results.filter(r => !r.success).length;

    alert(`Created ${successful} user accounts successfully. ${failed} failed.`);
    
    selectedEmployees.value = [];
    selectAll.value = false;
    showBulkCreateModal.value = false;
    
    await fetchEmployeesWithoutAccounts();
    await fetchAuditData();
  } catch (error) {
    console.error('Error bulk creating user accounts:', error);
    if (error.response?.data?.message) {
      alert(error.response.data.message);
    }
  } finally {
    bulkCreating.value = false;
  }
};

const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedEmployees.value = employeesWithoutAccounts.value.map(emp => emp.id);
  } else {
    selectedEmployees.value = [];
  }
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

// Lifecycle
onMounted(() => {
  fetchEmployeesWithoutAccounts();
  fetchAuditData();
});
</script>
