<template>
  <div class="employee-reports">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Total Employees</dt>
                <dd class="text-lg font-medium text-gray-900">{{ statistics.total_employees || 0 }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Active Employees</dt>
                <dd class="text-lg font-medium text-gray-900">{{ statistics.active_employees || 0 }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Recent Hires (30 days)</dt>
                <dd class="text-lg font-medium text-gray-900">{{ statistics.recent_hires || 0 }}</dd>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Probation Ending Soon</dt>
                <dd class="text-lg font-medium text-gray-900">{{ statistics.upcoming_probation_ends || 0 }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- By Department -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Employees by Department</h3>
        <div v-if="statistics.by_department && statistics.by_department.length > 0" class="space-y-3">
          <div v-for="item in statistics.by_department" :key="item.department_id" class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
              <span class="text-sm text-gray-900">{{ item.department?.name || 'No Department' }}</span>
            </div>
            <div class="text-right">
              <div class="text-sm font-medium text-gray-900">{{ item.count }} employees</div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          No data available
        </div>
      </div>

      <!-- By Employment Type -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Employees by Employment Type</h3>
        <div v-if="statistics.by_employment_type && statistics.by_employment_type.length > 0" class="space-y-3">
          <div v-for="item in statistics.by_employment_type" :key="item.employment_type" class="flex items-center justify-between">
            <div class="flex items-center">
              <div :class="getEmploymentTypeColor(item.employment_type)" class="w-3 h-3 rounded-full mr-3"></div>
              <span class="text-sm text-gray-900">{{ getEmploymentTypeText(item.employment_type) }}</span>
            </div>
            <div class="text-right">
              <div class="text-sm font-medium text-gray-900">{{ item.count }} employees</div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          No data available
        </div>
      </div>
    </div>

    <!-- Employment Status Chart -->
    <div class="bg-white p-6 rounded-lg shadow">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Employees by Status</h3>
      <div v-if="statistics.by_employment_status && statistics.by_employment_status.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div v-for="item in statistics.by_employment_status" :key="item.employment_status" class="text-center">
          <div :class="getStatusColor(item.employment_status)" class="w-16 h-16 rounded-full mx-auto mb-2 flex items-center justify-center">
            <span class="text-lg font-bold text-white">{{ item.count }}</span>
          </div>
          <div class="text-sm font-medium text-gray-900">{{ getStatusText(item.employment_status) }}</div>
        </div>
      </div>
      <div v-else class="text-center py-8 text-gray-500">
        No data available
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-sm text-gray-500">Loading statistics...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Reactive data
const statistics = ref({});
const loading = ref(false);

// Methods
const fetchStatistics = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/employees/statistics/summary');
    statistics.value = response.data;
  } catch (error) {
    console.error('Error fetching statistics:', error);
  } finally {
    loading.value = false;
  }
};

const getEmploymentTypeText = (type) => {
  const texts = {
    full_time: 'Full Time',
    part_time: 'Part Time',
    contract: 'Contract',
    intern: 'Intern'
  };
  return texts[type] || type;
};

const getEmploymentTypeColor = (type) => {
  const colors = {
    full_time: 'bg-green-500',
    part_time: 'bg-blue-500',
    contract: 'bg-yellow-500',
    intern: 'bg-purple-500'
  };
  return colors[type] || 'bg-gray-500';
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

const getStatusColor = (status) => {
  const colors = {
    active: 'bg-green-500',
    inactive: 'bg-gray-500',
    terminated: 'bg-red-500',
    on_leave: 'bg-yellow-500'
  };
  return colors[status] || 'bg-gray-500';
};

// Lifecycle
onMounted(() => {
  fetchStatistics();
});
</script>
