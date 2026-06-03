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
                      <p class="text-2xl font-bold text-gray-900">{{ formatAmount(dashboardData.sales?.total_amount || 0) }}</p>
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
                      <p class="text-2xl font-bold text-gray-900">{{ formatAmount(dashboardData.expenses?.total_amount || 0) }}</p>
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
                      <p class="text-2xl font-bold text-gray-900">{{ formatAmount(dashboardData.payments?.total_amount || 0) }}</p>
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
                      <p class="text-2xl font-bold text-gray-900">{{ formatAmount(dashboardData.payments?.pending_amount || 0) }}</p>
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

        <!-- Smart Inventory Valuation cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div class="bg-gradient-to-br from-blue-500 to-indigo-600 overflow-hidden shadow-lg rounded-2xl p-6 text-white">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-indigo-100 text-sm font-medium uppercase tracking-wider">Total Inventory Value (Cost)</p>
                <p class="text-3xl font-black mt-1">{{ formatAmount(dashboardData.inventory_valuation?.total_cost_value || 0) }}</p>
              </div>
              <div class="bg-white/20 p-3 rounded-xl">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
              </div>
            </div>
          </div>
          <div class="bg-gradient-to-br from-emerald-500 to-teal-600 overflow-hidden shadow-lg rounded-2xl p-6 text-white">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-emerald-100 text-sm font-medium uppercase tracking-wider">Total Inventory Value (Retail)</p>
                <p class="text-3xl font-black mt-1">{{ formatAmount(dashboardData.inventory_valuation?.total_retail_value || 0) }}</p>
              </div>
              <div class="bg-white/20 p-3 rounded-xl">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path></svg>
              </div>
            </div>
          </div>
          <div class="bg-gradient-to-br from-purple-500 to-pink-600 overflow-hidden shadow-lg rounded-2xl p-6 text-white">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-purple-100 text-sm font-medium uppercase tracking-wider">Potential Profit</p>
                <p class="text-3xl font-black mt-1">{{ formatAmount(dashboardData.inventory_valuation?.potential_profit || 0) }}</p>
              </div>
              <div class="bg-white/20 p-3 rounded-xl">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Product Intelligence & Expiry alerts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          <!-- Fast Moving Items -->
          <div class="bg-white shadow-lg rounded-2xl overflow-hidden border border-gray-100">
            <div class="px-6 py-4 bg-indigo-50 border-b border-indigo-100 flex items-center justify-between">
              <h3 class="font-black text-indigo-900 uppercase tracking-wider text-sm flex items-center">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                Fast-Moving Items
              </h3>
              <span class="text-[10px] font-bold bg-indigo-600 text-white px-2 py-0.5 rounded-full">TOP 5</span>
            </div>
            <div class="p-4 space-y-3">
              <div v-for="item in dashboardData.product_intelligence?.fast_moving" :key="item.name" class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                <span class="text-sm font-bold text-gray-700 truncate mr-2">{{ item.name }}</span>
                <span class="text-xs font-black text-indigo-600 bg-white px-2 py-1 rounded-lg shadow-sm">{{ item.total_sold }} sold</span>
              </div>
              <div v-if="!dashboardData.product_intelligence?.fast_moving?.length" class="text-center py-6 text-gray-400 text-xs italic">
                No sales data available.
              </div>
            </div>
          </div>

          <!-- Slow Moving Items -->
          <div class="bg-white shadow-lg rounded-2xl overflow-hidden border border-gray-100">
            <div class="px-6 py-4 bg-orange-50 border-b border-orange-100 flex items-center justify-between">
              <h3 class="font-black text-orange-900 uppercase tracking-wider text-sm flex items-center">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V7h2v10z"></path></svg>
                Slow-Moving Items
              </h3>
              <span class="text-[10px] font-bold bg-orange-600 text-white px-2 py-0.5 rounded-full">IN STOCK</span>
            </div>
            <div class="p-4 space-y-3">
              <div v-for="item in dashboardData.product_intelligence?.slow_moving" :key="item.name" class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                <span class="text-sm font-bold text-gray-700 truncate mr-2">{{ item.name }}</span>
                <span class="text-xs font-black text-orange-600 bg-white px-2 py-1 rounded-lg shadow-sm">{{ item.stock_quantity }} left</span>
              </div>
              <div v-if="!dashboardData.product_intelligence?.slow_moving?.length" class="text-center py-6 text-gray-400 text-xs italic">
                No slow moving items found.
              </div>
            </div>
          </div>

          <!-- Expiry Management -->
          <div class="bg-white shadow-lg rounded-2xl overflow-hidden border border-gray-100">
            <div class="px-6 py-4 bg-red-50 border-b border-red-100 flex items-center justify-between">
              <h3 class="font-black text-red-900 uppercase tracking-wider text-sm flex items-center">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V7h2v10z"></path></svg>
                Expiring Soon
              </h3>
              <span class="text-[10px] font-bold bg-red-600 text-white px-2 py-0.5 rounded-full">{{ dashboardData.expiry_alerts?.count }} ALERTS</span>
            </div>
            <div class="p-4 space-y-3">
              <div v-for="item in dashboardData.expiry_alerts?.items" :key="item.name" class="p-3 bg-gray-50 rounded-xl border border-gray-100 flex flex-col">
                <div class="flex justify-between items-center mb-1">
                  <span class="text-sm font-bold text-gray-700 truncate mr-2">{{ item.name }}</span>
                  <span :class="[
                    'text-[10px] font-black px-2 py-0.5 rounded-full uppercase',
                    item.status === 'Expired' ? 'bg-red-600 text-white' : 
                    item.status === 'Critical' ? 'bg-orange-500 text-white' : 'bg-yellow-400 text-gray-900'
                  ]">{{ item.status }}</span>
                </div>
                <div class="flex justify-between text-[11px] font-medium text-gray-500 italic">
                  <span>Expires: {{ item.expiry_date }}</span>
                  <span class="text-red-500 font-bold">{{ item.days_to_expire < 0 ? 'Expired' : item.days_to_expire + ' days' }}</span>
                </div>
              </div>
              <div v-if="!dashboardData.expiry_alerts?.items?.length" class="text-center py-6 text-gray-400 text-xs italic">
                No items nearing expiry.
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
                <div class="relative group">
                  <select class="pl-4 pr-10 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all font-black text-[11px] text-gray-700 shadow-inner appearance-none cursor-pointer uppercase tracking-tight">
                    <option>Sales Invoice</option>
                    <option>Purchase Invoice</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/></svg>
                  </div>
                </div>
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
                      {{ formatAmount(invoice.paid_amount) }}
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
              <div class="relative group">
                <select class="pl-4 pr-10 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all font-black text-[11px] text-gray-700 shadow-inner appearance-none cursor-pointer uppercase tracking-tight">
                  <option>7 Days</option>
                  <option>30 Days</option>
                  <option>90 Days</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                  <svg class="w-4 h-4 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/></svg>
                </div>
              </div>
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
              <div class="relative group">
                <select class="pl-4 pr-10 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all font-black text-[11px] text-gray-700 shadow-inner appearance-none cursor-pointer uppercase tracking-tight">
                  <option>15 Days</option>
                  <option>30 Days</option>
                  <option>90 Days</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                  <svg class="w-4 h-4 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/></svg>
                </div>
              </div>
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
                        <span class="text-sm font-medium text-gray-900">{{ formatAmount(type.total_amount) }}</span>
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
                        <span class="text-sm font-medium text-gray-900">{{ formatAmount(status.total_amount) }}</span>
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
import { useCurrencyStore } from '@/stores/currency';

const currencyStore = useCurrencyStore();
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
  return currencyStore.formatPrice(amount || 0);
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
              return `${context.dataset.label}: ${currencyStore.formatPrice(context.parsed.y)}`;
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
              return (currencyStore.activeCurrency?.symbol || '$') + value.toLocaleString();
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
