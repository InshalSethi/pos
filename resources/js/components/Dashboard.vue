<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>

        <!-- Date Range Filter -->
        <div class="flex items-center space-x-4">
          <div class="flex items-center space-x-2">
            <label class="text-sm font-medium text-gray-700">From:</label>
            <input
              type="date"
              v-model="dateRange.from"
              @change="loadDashboardData"
              class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>
          <div class="flex items-center space-x-2">
            <label class="text-sm font-medium text-gray-700">To:</label>
            <input
              type="date"
              v-model="dateRange.to"
              @change="loadDashboardData"
              class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>
          <div class="flex space-x-2">
            <button
              @click="setToday"
              class="bg-indigo-600 text-white px-3 py-2 rounded-md text-sm hover:bg-indigo-700"
            >
              Today
            </button>
            <button
              @click="setThisWeek"
              class="bg-green-600 text-white px-3 py-2 rounded-md text-sm hover:bg-green-700"
            >
              This Week
            </button>
            <button
              @click="setThisMonth"
              class="bg-blue-600 text-white px-3 py-2 rounded-md text-sm hover:bg-blue-700"
            >
              This Month
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        <p class="mt-2 text-gray-600">Loading dashboard data...</p>
      </div>

      <!-- Dashboard Stats -->
      <div v-else>
        <!-- Enhanced Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <!-- Total Sales Card -->
          <div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-purple-500">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div>
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                      <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                      </svg>
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-gray-500">Total Sales</p>
                      <p class="text-2xl font-bold text-gray-900">${{ formatAmount(dashboardData.sales?.total_amount || 0) }}</p>
                    </div>
                  </div>
                  <div class="mt-2 flex items-center text-sm">
                    <span class="text-green-600 font-medium">+2.5%</span>
                    <span class="text-gray-500 ml-1">Than Last Month</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Total Expenses Card -->
          <div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-orange-500">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div>
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                      <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                      </svg>
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-gray-500">Total Expenses</p>
                      <p class="text-2xl font-bold text-gray-900">${{ formatAmount(dashboardData.expenses?.total_amount || 0) }}</p>
                    </div>
                  </div>
                  <div class="mt-2 flex items-center text-sm">
                    <span class="text-red-600 font-medium">-2.1%</span>
                    <span class="text-gray-500 ml-1">Than Last Month</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Total Payments Card -->
          <div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-blue-500">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div>
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                      <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                      </svg>
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-gray-500">Total Payments</p>
                      <p class="text-2xl font-bold text-gray-900">${{ formatAmount(dashboardData.payments?.total_amount || 0) }}</p>
                      <p class="text-xs text-gray-500">{{ dashboardData.payments?.total_payments || 0 }} payments</p>
                    </div>
                  </div>
                  <div class="mt-2 flex items-center text-sm">
                    <span :class="getChangeClass(dashboardData.payments?.payment_sent?.change_percentage || 0)">
                      {{ formatPercentage(dashboardData.payments?.payment_sent?.change_percentage || 0) }}%
                    </span>
                    <span class="text-gray-500 ml-1">Than Last Month</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Pending Payments Card -->
          <div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-yellow-500">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div>
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                      <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-gray-500">Pending Payments</p>
                      <p class="text-2xl font-bold text-gray-900">${{ formatAmount(dashboardData.payments?.pending_amount || 0) }}</p>
                      <p class="text-xs text-gray-500">{{ dashboardData.payments?.pending_payments || 0 }} pending</p>
                    </div>
                  </div>
                  <div class="mt-2">
                    <router-link
                      to="/payments?status=pending"
                      class="text-sm text-yellow-600 hover:text-yellow-800 font-medium"
                    >
                      View Pending →
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>



        <!-- Enhanced Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <!-- Sales & Purchases Chart -->
          <SalesPurchasesChart :data="dashboardData.sales_purchases_chart || []" />

          <!-- Devices Breakdown Chart -->
          <DevicesPieChart :data="dashboardData.devices_breakdown || []" />
        </div>

        <!-- Bottom Section with Recent Invoices and Stock History -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          <!-- Recent Invoices -->
          <div class="lg:col-span-2 bg-white overflow-hidden shadow-lg rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">Recent Invoice</h3>
              <div class="flex items-center space-x-2">
                <select class="text-sm border border-gray-300 rounded-md px-3 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                  <option>Sales Invoice</option>
                  <option>Purchase Invoice</option>
                </select>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales Status</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="invoice in dashboardData.recent_invoices" :key="invoice.invoice_id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ invoice.invoice_id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ invoice.customer }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatDate(invoice.sales_date) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      ${{ formatAmount(invoice.paid_amount) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="getStatusBadgeClass(invoice.status_color)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                        {{ invoice.sales_status }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Stock History -->
          <div class="bg-white overflow-hidden shadow-lg rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">Stock History</h3>
              <select class="text-sm border border-gray-300 rounded-md px-3 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option>7 Days</option>
                <option>30 Days</option>
                <option>90 Days</option>
              </select>
            </div>
            <div class="p-6 space-y-6">
              <!-- Total Sales Items -->
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-500">Total Sales Items</p>
                  <p class="text-2xl font-bold text-gray-900">{{ dashboardData.stock_history?.total_sales_items?.count || 0 }}</p>
                </div>
                <div class="flex items-center">
                  <span :class="getChangeClass(dashboardData.stock_history?.total_sales_items?.change_percentage || 0)" class="text-sm font-medium">
                    {{ formatPercentage(dashboardData.stock_history?.total_sales_items?.change_percentage || 0) }}%
                  </span>
                </div>
              </div>

              <!-- Total Purchase Items -->
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-500">Purchase Items</p>
                  <p class="text-2xl font-bold text-gray-900">{{ dashboardData.stock_history?.total_purchase_items?.count || 0 }}</p>
                </div>
                <div class="flex items-center">
                  <span :class="getChangeClass(dashboardData.stock_history?.total_purchase_items?.change_percentage || 0)" class="text-sm font-medium">
                    {{ formatPercentage(dashboardData.stock_history?.total_purchase_items?.change_percentage || 0) }}%
                  </span>
                </div>
              </div>

              <!-- Total Return Items -->
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-500">Purchase Returns Items</p>
                  <p class="text-2xl font-bold text-gray-900">{{ dashboardData.stock_history?.total_return_items?.count || 0 }}</p>
                </div>
                <div class="flex items-center">
                  <span :class="getChangeClass(dashboardData.stock_history?.total_return_items?.change_percentage || 0)" class="text-sm font-medium">
                    {{ formatPercentage(dashboardData.stock_history?.total_return_items?.change_percentage || 0) }}%
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment Trends and Stock Alert -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Payment Trends Chart -->
          <div class="bg-white overflow-hidden shadow-lg rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">Payments</h3>
              <select class="text-sm border border-gray-300 rounded-md px-3 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option>15 Days</option>
                <option>30 Days</option>
                <option>90 Days</option>
              </select>
            </div>
            <div class="p-6">
              <div class="h-64">
                <canvas ref="paymentTrendsCanvas"></canvas>
              </div>
              <!-- Payment Trends Legend -->
              <div class="flex items-center justify-center space-x-6 mt-4">
                <div class="flex items-center">
                  <div class="w-3 h-3 bg-red-500 rounded mr-2"></div>
                  <span class="text-sm text-gray-600">Payment Sent</span>
                </div>
                <div class="flex items-center">
                  <div class="w-3 h-3 bg-blue-500 rounded mr-2"></div>
                  <span class="text-sm text-gray-600">Payment Received</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Stock Alert -->
          <div class="bg-white overflow-hidden shadow-lg rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Stock Alert</h3>
            </div>
            <div class="p-6">
              <div class="space-y-4 max-h-64 overflow-y-auto">
                <div v-for="alert in dashboardData.stock_alerts" :key="alert.product" class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                  <div>
                    <p class="text-sm font-medium text-gray-900">{{ alert.product }}</p>
                  </div>
                  <div class="text-right">
                    <p class="text-sm font-bold text-red-600">{{ alert.quantity }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment Breakdown Section -->
        <div v-if="dashboardData.payments?.by_type?.length > 0" class="mt-6">
          <div class="bg-white overflow-hidden shadow-lg rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">Payment Breakdown</h3>
              <router-link
                to="/payments"
                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium"
              >
                View All →
              </router-link>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- By Type -->
                <div>
                  <h4 class="text-sm font-medium text-gray-900 mb-3">By Type</h4>
                  <div class="space-y-2">
                    <div v-for="type in dashboardData.payments.by_type" :key="type.payment_type" class="flex items-center justify-between">
                      <span class="text-sm text-gray-600">{{ getPaymentTypeDisplay(type.payment_type) }}</span>
                      <div class="text-right">
                        <span class="text-sm font-medium text-gray-900">${{ formatAmount(type.total_amount) }}</span>
                        <span class="text-xs text-gray-500 ml-1">({{ type.count }})</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- By Status -->
                <div>
                  <h4 class="text-sm font-medium text-gray-900 mb-3">By Status</h4>
                  <div class="space-y-2">
                    <div v-for="status in dashboardData.payments.by_status" :key="status.status" class="flex items-center justify-between">
                      <div class="flex items-center">
                        <span :class="getPaymentStatusBadgeClass(status.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full mr-2">
                          {{ status.status.charAt(0).toUpperCase() + status.status.slice(1) }}
                        </span>
                      </div>
                      <div class="text-right">
                        <span class="text-sm font-medium text-gray-900">${{ formatAmount(status.total_amount) }}</span>
                        <span class="text-xs text-gray-500 ml-1">({{ status.count }})</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue';
import axios from 'axios';
import SalesPurchasesChart from '@/components/charts/SalesPurchasesChart.vue';
import DevicesPieChart from '@/components/charts/DevicesPieChart.vue';
import {
  Chart,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js';

// Register Chart.js components
Chart.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend);

// Reactive data
const loading = ref(false);
const dateRange = ref({
  from: new Date().toISOString().split('T')[0],
  to: new Date().toISOString().split('T')[0]
});
const paymentTrendsCanvas = ref(null);
let paymentTrendsChart = null;
const dashboardData = ref({
  sales: { total_amount: 0, count: 0 },
  returns: { total_amount: 0, count: 0 },
  purchases: { total_amount: 0, count: 0 },
  expenses: { total_amount: 0, count: 0 },
  payments: {
    payment_sent: { total_amount: 0, change_percentage: 0 },
    payment_received: { total_amount: 0, change_percentage: 0 }
  },
  low_stock: { count: 0 },
  sales_trend: [],
  sales_purchases_chart: [],
  devices_breakdown: [],
  recent_invoices: [],
  stock_history: {},
  payment_trends: [],
  stock_alerts: [],
  expense_categories: [],
  recent_transactions: []
});

// Computed properties
const maxSalesAmount = computed(() => {
  if (!dashboardData.value.sales_trend?.length) return 1;
  return Math.max(...dashboardData.value.sales_trend.map(day => day.amount));
});

// Methods
const loadDashboardData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/dashboard/statistics', {
      params: {
        date_from: dateRange.value.from,
        date_to: dateRange.value.to
      }
    });
    dashboardData.value = response.data;

    // Create payment trends chart after data is loaded
    nextTick(() => {
      createPaymentTrendsChart();
    });
  } catch (error) {
    console.error('Error loading dashboard data:', error);
  } finally {
    loading.value = false;
  }
};

const setToday = () => {
  const today = new Date().toISOString().split('T')[0];
  dateRange.value.from = today;
  dateRange.value.to = today;
  loadDashboardData();
};

const setThisWeek = () => {
  const today = new Date();
  const firstDayOfWeek = new Date(today.setDate(today.getDate() - today.getDay()));
  const lastDayOfWeek = new Date(today.setDate(today.getDate() - today.getDay() + 6));

  dateRange.value.from = firstDayOfWeek.toISOString().split('T')[0];
  dateRange.value.to = lastDayOfWeek.toISOString().split('T')[0];
  loadDashboardData();
};

const setThisMonth = () => {
  const today = new Date();
  const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
  const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

  dateRange.value.from = firstDayOfMonth.toISOString().split('T')[0];
  dateRange.value.to = lastDayOfMonth.toISOString().split('T')[0];
  loadDashboardData();
};

const formatAmount = (amount) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount || 0);
};

const getPaymentTypeDisplay = (type) => {
  const types = {
    supplier_payment: 'Supplier Payment',
    expense_payment: 'Expense Payment',
    salary_payment: 'Salary Payment',
    sale_return_payment: 'Sale Return Payment',
    purchase_invoice_payment: 'Purchase Invoice Payment',
    other_payment: 'Other Payment',
  };
  return types[type] || type;
};

const getPaymentStatusBadgeClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-blue-100 text-blue-800',
    paid: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  });
};

const formatDateTime = (datetime) => {
  return new Date(datetime).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getTransactionIconClass = (type) => {
  const classes = {
    'sale': 'bg-green-500',
    'return': 'bg-red-500',
    'purchase': 'bg-orange-500',
    'expense': 'bg-purple-500'
  };
  return classes[type] || 'bg-gray-500';
};

const formatPercentage = (percentage) => {
  return Math.abs(percentage).toFixed(1);
};

const getChangeClass = (percentage) => {
  if (percentage > 0) {
    return 'text-green-600 font-medium';
  } else if (percentage < 0) {
    return 'text-red-600 font-medium';
  }
  return 'text-gray-600 font-medium';
};

const getStatusBadgeClass = (statusColor) => {
  const classes = {
    'green': 'bg-green-100 text-green-800',
    'red': 'bg-red-100 text-red-800',
    'yellow': 'bg-yellow-100 text-yellow-800',
    'blue': 'bg-blue-100 text-blue-800'
  };
  return classes[statusColor] || 'bg-gray-100 text-gray-800';
};

const createPaymentTrendsChart = () => {
  if (!paymentTrendsCanvas.value || !dashboardData.value.payment_trends?.length) return;

  const ctx = paymentTrendsCanvas.value.getContext('2d');

  // Destroy existing chart if it exists
  if (paymentTrendsChart) {
    paymentTrendsChart.destroy();
  }

  const labels = dashboardData.value.payment_trends.map(item => item.date);
  const paymentSentData = dashboardData.value.payment_trends.map(item => item.payment_sent);
  const paymentReceivedData = dashboardData.value.payment_trends.map(item => item.payment_received);

  paymentTrendsChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Payment Sent',
          data: paymentSentData,
          borderColor: '#EF4444',
          backgroundColor: 'rgba(239, 68, 68, 0.1)',
          borderWidth: 2,
          fill: false,
          tension: 0.4,
          pointBackgroundColor: '#EF4444',
          pointBorderColor: '#ffffff',
          pointBorderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6
        },
        {
          label: 'Payment Received',
          data: paymentReceivedData,
          borderColor: '#3B82F6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          borderWidth: 2,
          fill: false,
          tension: 0.4,
          pointBackgroundColor: '#3B82F6',
          pointBorderColor: '#ffffff',
          pointBorderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          mode: 'index',
          intersect: false,
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          titleColor: '#fff',
          bodyColor: '#fff',
          borderColor: '#374151',
          borderWidth: 1,
          cornerRadius: 8,
          callbacks: {
            label: function(context) {
              return `${context.dataset.label}: $${context.parsed.y.toLocaleString()}`;
            }
          }
        }
      },
      scales: {
        x: {
          grid: {
            display: false
          },
          ticks: {
            color: '#6B7280',
            font: {
              size: 12
            }
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            color: '#F3F4F6',
            borderDash: [2, 2]
          },
          ticks: {
            color: '#6B7280',
            font: {
              size: 12
            },
            callback: function(value) {
              return '$' + value.toLocaleString();
            }
          }
        }
      },
      interaction: {
        mode: 'index',
        intersect: false
      }
    }
  });
};

// Lifecycle
onMounted(() => {
  loadDashboardData();
});
</script>
