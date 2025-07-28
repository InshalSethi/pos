<template>
  <div class="expense-reports">
    <!-- Date Range Filter -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
      <div class="flex items-center space-x-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
          <input
            v-model="dateRange.start_date"
            type="date"
            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @change="fetchStatistics"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
          <input
            v-model="dateRange.end_date"
            type="date"
            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @change="fetchStatistics"
          />
        </div>
        <div class="pt-6">
          <button
            @click="setQuickRange('thisMonth')"
            class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 mr-2"
          >
            This Month
          </button>
          <button
            @click="setQuickRange('lastMonth')"
            class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 mr-2"
          >
            Last Month
          </button>
          <button
            @click="setQuickRange('thisYear')"
            class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
          >
            This Year
          </button>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Total Expenses</dt>
                <dd class="text-lg font-medium text-gray-900">{{ statistics.total_expenses || 0 }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Total Amount</dt>
                <dd class="text-lg font-medium text-gray-900">${{ formatAmount(statistics.total_amount) }}</dd>
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
                <dt class="text-sm font-medium text-gray-500 truncate">Pending Approval</dt>
                <dd class="text-lg font-medium text-gray-900">{{ statistics.pending_approval || 0 }}</dd>
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
                <dt class="text-sm font-medium text-gray-500 truncate">Paid Amount</dt>
                <dd class="text-lg font-medium text-gray-900">${{ formatAmount(statistics.paid_amount) }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- By Category -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Expenses by Category</h3>
        <div v-if="statistics.by_category && statistics.by_category.length > 0" class="space-y-3">
          <div v-for="item in statistics.by_category" :key="item.category_id" class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
              <span class="text-sm text-gray-900">{{ item.category?.name || 'Unknown' }}</span>
            </div>
            <div class="text-right">
              <div class="text-sm font-medium text-gray-900">${{ formatAmount(item.total_amount) }}</div>
              <div class="text-xs text-gray-500">{{ item.count }} expenses</div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          No data available
        </div>
      </div>

      <!-- By Status -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Expenses by Status</h3>
        <div v-if="statistics.by_status && statistics.by_status.length > 0" class="space-y-3">
          <div v-for="item in statistics.by_status" :key="item.status" class="flex items-center justify-between">
            <div class="flex items-center">
              <div :class="getStatusColor(item.status)" class="w-3 h-3 rounded-full mr-3"></div>
              <span class="text-sm text-gray-900">{{ getStatusText(item.status) }}</span>
            </div>
            <div class="text-right">
              <div class="text-sm font-medium text-gray-900">${{ formatAmount(item.total_amount) }}</div>
              <div class="text-xs text-gray-500">{{ item.count }} expenses</div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          No data available
        </div>
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
const dateRange = ref({
  start_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
  end_date: new Date().toISOString().split('T')[0]
});

// Methods
const fetchStatistics = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/expenses/statistics/summary', {
      params: dateRange.value
    });
    statistics.value = response.data;
  } catch (error) {
    console.error('Error fetching statistics:', error);
  } finally {
    loading.value = false;
  }
};

const setQuickRange = (range) => {
  const now = new Date();
  
  switch (range) {
    case 'thisMonth':
      dateRange.value.start_date = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
      dateRange.value.end_date = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0];
      break;
    case 'lastMonth':
      dateRange.value.start_date = new Date(now.getFullYear(), now.getMonth() - 1, 1).toISOString().split('T')[0];
      dateRange.value.end_date = new Date(now.getFullYear(), now.getMonth(), 0).toISOString().split('T')[0];
      break;
    case 'thisYear':
      dateRange.value.start_date = new Date(now.getFullYear(), 0, 1).toISOString().split('T')[0];
      dateRange.value.end_date = new Date(now.getFullYear(), 11, 31).toISOString().split('T')[0];
      break;
  }
  
  fetchStatistics();
};

const formatAmount = (amount) => {
  return parseFloat(amount || 0).toFixed(2);
};

const getStatusText = (status) => {
  const texts = {
    draft: 'Draft',
    submitted: 'Submitted',
    approved: 'Approved',
    rejected: 'Rejected',
    paid: 'Paid'
  };
  return texts[status] || status;
};

const getStatusColor = (status) => {
  const colors = {
    draft: 'bg-gray-500',
    submitted: 'bg-yellow-500',
    approved: 'bg-green-500',
    rejected: 'bg-red-500',
    paid: 'bg-blue-500'
  };
  return colors[status] || 'bg-gray-500';
};

// Lifecycle
onMounted(() => {
  fetchStatistics();
});
</script>
