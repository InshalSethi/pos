<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
      <!-- Header Bar -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-extrabold text-zinc-950 dark:text-white tracking-tight">Dashboard</h1>
          <p class="text-xs text-zinc-500 dark:text-zinc-400 font-medium mt-1">Real-time overview of performance and key metrics</p>
        </div>

        <!-- Date Range Filter -->
        <div class="flex flex-wrap items-center gap-3 bg-white dark:bg-zinc-900 p-2.5 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
          <div class="flex items-center space-x-2">
            <label class="text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider pl-1">From:</label>
            <input
              type="date"
              v-model="dateRange.from"
              @change="loadDashboardData"
              class="border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white rounded-xl px-3 py-1.5 text-xs font-medium focus:outline-none focus:border-black dark:focus:border-white shadow-inner"
            />
          </div>
          <div class="flex items-center space-x-2">
            <label class="text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">To:</label>
            <input
              type="date"
              v-model="dateRange.to"
              @change="loadDashboardData"
              class="border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 text-zinc-900 dark:text-white rounded-xl px-3 py-1.5 text-xs font-medium focus:outline-none focus:border-black dark:focus:border-white shadow-inner"
            />
          </div>
          <div class="flex space-x-1.5 pl-2 border-l border-zinc-200 dark:border-zinc-800">
            <button
              @click="setToday"
              :class="[
                'px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer',
                activePreset === 'today'
                  ? 'bg-black text-white dark:bg-white dark:text-black shadow-sm'
                  : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700'
              ]"
            >
              Today
            </button>
            <button
              @click="setThisWeek"
              :class="[
                'px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer',
                activePreset === 'week'
                  ? 'bg-black text-white dark:bg-white dark:text-black shadow-sm'
                  : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700'
              ]"
            >
              This Week
            </button>
            <button
              @click="setThisMonth"
              :class="[
                'px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer',
                activePreset === 'month'
                  ? 'bg-black text-white dark:bg-white dark:text-black shadow-sm'
                  : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700'
              ]"
            >
              This Month
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-16 bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-2 border-black border-t-transparent dark:border-white dark:border-t-transparent"></div>
        <p class="mt-3 text-xs font-bold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">Loading dashboard telemetry...</p>
      </div>

      <!-- Dashboard Stats -->
      <div v-else>
        <!-- Primary Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <!-- Total Sales Card -->
          <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div class="w-full">
                  <div class="flex items-center justify-between">
                    <div class="w-11 h-11 bg-black text-white dark:bg-white dark:text-black rounded-xl flex items-center justify-center shadow-sm">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                      </svg>
                    </div>
                    <span class="text-[11px] font-extrabold bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100 px-2.5 py-1 rounded-full border border-zinc-200 dark:border-zinc-700">+2.5%</span>
                  </div>
                  <div class="mt-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Total Sales</p>
                    <p class="text-2xl font-black text-zinc-950 dark:text-white mt-0.5 tracking-tight">{{ formatAmount(dashboardData.sales?.total_amount || 0) }}</p>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1">Vs previous period</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Total Expenses Card -->
          <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div class="w-full">
                  <div class="flex items-center justify-between">
                    <div class="w-11 h-11 bg-zinc-800 text-white dark:bg-zinc-200 dark:text-black rounded-xl flex items-center justify-center shadow-sm">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                      </svg>
                    </div>
                    <span class="text-[11px] font-extrabold bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100 px-2.5 py-1 rounded-full border border-zinc-200 dark:border-zinc-700">-2.1%</span>
                  </div>
                  <div class="mt-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Total Expenses</p>
                    <p class="text-2xl font-black text-zinc-950 dark:text-white mt-0.5 tracking-tight">{{ formatAmount(dashboardData.expenses?.total_amount || 0) }}</p>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1">Vs previous period</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Total Payments Card -->
          <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div class="w-full">
                  <div class="flex items-center justify-between">
                    <div class="w-11 h-11 bg-zinc-900 text-white dark:bg-zinc-100 dark:text-black rounded-xl flex items-center justify-center shadow-sm">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                      </svg>
                    </div>
                    <span class="text-[11px] font-extrabold bg-black text-white dark:bg-white dark:text-black px-2.5 py-1 rounded-full">
                      {{ dashboardData.payments?.total_payments || 0 }} TXNS
                    </span>
                  </div>
                  <div class="mt-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Total Payments</p>
                    <p class="text-2xl font-black text-zinc-950 dark:text-white mt-0.5 tracking-tight">{{ formatAmount(dashboardData.payments?.total_amount || 0) }}</p>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1">
                      {{ formatPercentage(dashboardData.payments?.payment_sent?.change_percentage || 0) }}% change
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Pending Payments Card -->
          <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div class="w-full">
                  <div class="flex items-center justify-between">
                    <div class="w-11 h-11 bg-black text-white dark:bg-white dark:text-black rounded-xl flex items-center justify-center shadow-sm">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </div>
                    <span class="text-[11px] font-extrabold bg-zinc-200 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100 px-2.5 py-1 rounded-full border border-zinc-300 dark:border-zinc-700">
                      {{ dashboardData.payments?.pending_payments || 0 }} PENDING
                    </span>
                  </div>
                  <div class="mt-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Pending Payments</p>
                    <p class="text-2xl font-black text-zinc-950 dark:text-white mt-0.5 tracking-tight">{{ formatAmount(dashboardData.payments?.pending_amount || 0) }}</p>
                    <div class="mt-2">
                      <router-link
                        to="/payments?status=pending"
                        class="inline-flex items-center text-xs text-black dark:text-white font-extrabold hover:underline group"
                      >
                        View Pending
                        <svg class="w-3.5 h-3.5 ml-1 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                      </router-link>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Inventory Valuation Cards (Clean Light Cards in Light Mode, Dark Obsidian in Dark Mode) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <!-- Total Cost Value -->
          <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Total Inventory Value (Cost)</p>
                <p class="text-3xl font-black text-zinc-950 dark:text-white mt-2 tracking-tight">{{ formatAmount(dashboardData.inventory_valuation?.total_cost_value || 0) }}</p>
              </div>
              <div class="w-12 h-12 bg-black text-white dark:bg-white dark:text-black rounded-xl flex items-center justify-center shadow-sm shrink-0 ml-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
              </div>
            </div>
          </div>

          <!-- Total Retail Value -->
          <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Total Inventory Value (Retail)</p>
                <p class="text-3xl font-black text-zinc-950 dark:text-white mt-2 tracking-tight">{{ formatAmount(dashboardData.inventory_valuation?.total_retail_value || 0) }}</p>
              </div>
              <div class="w-12 h-12 bg-zinc-900 text-white dark:bg-zinc-100 dark:text-black rounded-xl flex items-center justify-center shadow-sm shrink-0 ml-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path></svg>
              </div>
            </div>
          </div>

          <!-- Potential Profit -->
          <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Potential Profit</p>
                <p class="text-3xl font-black text-zinc-950 dark:text-white mt-2 tracking-tight">{{ formatAmount(dashboardData.inventory_valuation?.potential_profit || 0) }}</p>
              </div>
              <div class="w-12 h-12 bg-zinc-800 text-white dark:bg-zinc-200 dark:text-black rounded-xl flex items-center justify-center shadow-sm shrink-0 ml-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Product Intelligence & Expiry alerts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          <!-- Fast Moving Items -->
          <div class="bg-white dark:bg-zinc-900 shadow-sm rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800">
            <div class="px-6 py-4 bg-zinc-100 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
              <h3 class="font-extrabold text-zinc-900 dark:text-white uppercase tracking-wider text-xs flex items-center">
                <svg class="w-4 h-4 mr-2 text-black dark:text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                Fast-Moving Items
              </h3>
              <span class="text-[10px] font-extrabold bg-black text-white dark:bg-white dark:text-black px-2.5 py-0.5 rounded-full">TOP 5</span>
            </div>
            <div class="p-4 space-y-2.5">
              <div v-for="item in dashboardData.product_intelligence?.fast_moving" :key="item.name" class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-950/60 rounded-xl border border-zinc-200/80 dark:border-zinc-800">
                <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 truncate mr-2">{{ item.name }}</span>
                <span class="text-[11px] font-black text-white bg-black dark:bg-white dark:text-black px-2.5 py-1 rounded-lg shadow-xs">{{ item.total_sold }} sold</span>
              </div>
              <div v-if="!dashboardData.product_intelligence?.fast_moving?.length" class="text-center py-6 text-zinc-400 text-xs italic">
                No sales data available.
              </div>
            </div>
          </div>

          <!-- Slow Moving Items -->
          <div class="bg-white dark:bg-zinc-900 shadow-sm rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800">
            <div class="px-6 py-4 bg-zinc-100 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
              <h3 class="font-extrabold text-zinc-900 dark:text-white uppercase tracking-wider text-xs flex items-center">
                <svg class="w-4 h-4 mr-2 text-black dark:text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V7h2v10z"></path></svg>
                Slow-Moving Items
              </h3>
              <span class="text-[10px] font-extrabold bg-black text-white dark:bg-white dark:text-black px-2.5 py-0.5 rounded-full">IN STOCK</span>
            </div>
            <div class="p-4 space-y-2.5">
              <div v-for="item in dashboardData.product_intelligence?.slow_moving" :key="item.name" class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-950/60 rounded-xl border border-zinc-200/80 dark:border-zinc-800">
                <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 truncate mr-2">{{ item.name }}</span>
                <span class="text-[11px] font-black text-black bg-zinc-200 dark:bg-zinc-800 dark:text-white px-2.5 py-1 rounded-lg border border-zinc-300 dark:border-zinc-700">{{ item.stock_quantity }} left</span>
              </div>
              <div v-if="!dashboardData.product_intelligence?.slow_moving?.length" class="text-center py-6 text-zinc-400 text-xs italic">
                No slow moving items found.
              </div>
            </div>
          </div>

          <!-- Expiry Management -->
          <div class="bg-white dark:bg-zinc-900 shadow-sm rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800">
            <div class="px-6 py-4 bg-zinc-100 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
              <h3 class="font-extrabold text-zinc-900 dark:text-white uppercase tracking-wider text-xs flex items-center">
                <svg class="w-4 h-4 mr-2 text-black dark:text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V7h2v10z"></path></svg>
                Expiring Soon
              </h3>
              <span class="text-[10px] font-extrabold bg-black text-white dark:bg-white dark:text-black px-2.5 py-0.5 rounded-full">{{ dashboardData.expiry_alerts?.count || 0 }} ALERTS</span>
            </div>
            <div class="p-4 space-y-2.5">
              <div v-for="item in dashboardData.expiry_alerts?.items" :key="item.name" class="p-3 bg-zinc-50 dark:bg-zinc-950/60 rounded-xl border border-zinc-200/80 dark:border-zinc-800 flex flex-col">
                <div class="flex justify-between items-center mb-1">
                  <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 truncate mr-2">{{ item.name }}</span>
                  <span class="text-[10px] font-black px-2 py-0.5 rounded-full uppercase bg-black text-white dark:bg-white dark:text-black">{{ item.status }}</span>
                </div>
                <div class="flex justify-between text-[11px] font-medium text-zinc-500 italic">
                  <span>Expires: {{ item.expiry_date }}</span>
                  <span class="text-black dark:text-white font-extrabold">{{ item.days_to_expire < 0 ? 'Expired' : item.days_to_expire + ' days' }}</span>
                </div>
              </div>
              <div v-if="!dashboardData.expiry_alerts?.items?.length" class="text-center py-6 text-zinc-400 text-xs italic">
                No items nearing expiry.
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <!-- Sales & Purchases Chart -->
          <SalesPurchasesChart :data="dashboardData.sales_purchases_chart || []" />

          <!-- Devices Breakdown Chart -->
          <DevicesPieChart :data="dashboardData.devices_breakdown || []" />
        </div>

        <!-- Recent Invoices and Stock History -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          <!-- Recent Invoices Table -->
          <div class="lg:col-span-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-sm rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
              <h3 class="text-sm font-extrabold text-zinc-950 dark:text-white uppercase tracking-wider">Recent Invoices</h3>
              <div class="flex items-center space-x-2">
                <div class="relative group">
                  <select class="pl-3 pr-8 py-1.5 bg-zinc-100 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-xl focus:outline-none focus:border-black dark:focus:border-white transition-all font-bold text-xs text-zinc-900 dark:text-white appearance-none cursor-pointer uppercase tracking-tight">
                    <option>Sales Invoice</option>
                    <option>Purchase Invoice</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none">
                    <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/></svg>
                  </div>
                </div>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="bg-zinc-100 dark:bg-zinc-800/80">
                  <tr>
                    <th class="px-6 py-3 text-left text-[11px] font-extrabold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Invoice ID</th>
                    <th class="px-6 py-3 text-left text-[11px] font-extrabold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-[11px] font-extrabold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Sales Date</th>
                    <th class="px-6 py-3 text-left text-[11px] font-extrabold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Paid Amount</th>
                    <th class="px-6 py-3 text-left text-[11px] font-extrabold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Sales Status</th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-800">
                  <tr v-for="invoice in dashboardData.recent_invoices" :key="invoice.invoice_id" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-xs font-extrabold text-zinc-950 dark:text-white">
                      {{ invoice.invoice_id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold text-zinc-800 dark:text-zinc-200">
                      {{ invoice.customer }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-xs text-zinc-500 dark:text-zinc-400">
                      {{ formatDate(invoice.sales_date) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-zinc-950 dark:text-white">
                      {{ formatAmount(invoice.paid_amount) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="inline-flex px-2.5 py-0.5 text-[10px] font-extrabold rounded-full bg-black text-white dark:bg-white dark:text-black uppercase tracking-wider">
                        {{ invoice.sales_status }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Stock History Card -->
          <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-sm rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
              <h3 class="text-sm font-extrabold text-zinc-950 dark:text-white uppercase tracking-wider">Stock History</h3>
              <div class="relative group">
                <select class="pl-3 pr-8 py-1.5 bg-zinc-100 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-xl focus:outline-none focus:border-black dark:focus:border-white transition-all font-bold text-xs text-zinc-900 dark:text-white appearance-none cursor-pointer uppercase tracking-tight">
                  <option>7 Days</option>
                  <option>30 Days</option>
                  <option>90 Days</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none">
                  <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/></svg>
                </div>
              </div>
            </div>
            <div class="p-6 space-y-6">
              <!-- Total Sales Items -->
              <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                <div>
                  <p class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Total Sales Items</p>
                  <p class="text-2xl font-black text-zinc-950 dark:text-white mt-0.5 tracking-tight">{{ dashboardData.stock_history?.total_sales_items?.count || 0 }}</p>
                </div>
                <div class="flex items-center">
                  <span class="text-xs font-extrabold bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100 px-2.5 py-1 rounded-full border border-zinc-300 dark:border-zinc-700">
                    {{ formatPercentage(dashboardData.stock_history?.total_sales_items?.change_percentage || 0) }}%
                  </span>
                </div>
              </div>

              <!-- Total Purchase Items -->
              <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                <div>
                  <p class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Purchase Items</p>
                  <p class="text-2xl font-black text-zinc-950 dark:text-white mt-0.5 tracking-tight">{{ dashboardData.stock_history?.total_purchase_items?.count || 0 }}</p>
                </div>
                <div class="flex items-center">
                  <span class="text-xs font-extrabold bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100 px-2.5 py-1 rounded-full border border-zinc-300 dark:border-zinc-700">
                    {{ formatPercentage(dashboardData.stock_history?.total_purchase_items?.change_percentage || 0) }}%
                  </span>
                </div>
              </div>

              <!-- Total Return Items -->
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Purchase Returns Items</p>
                  <p class="text-2xl font-black text-zinc-950 dark:text-white mt-0.5 tracking-tight">{{ dashboardData.stock_history?.total_return_items?.count || 0 }}</p>
                </div>
                <div class="flex items-center">
                  <span class="text-xs font-extrabold bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100 px-2.5 py-1 rounded-full border border-zinc-300 dark:border-zinc-700">
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
          <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-sm rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
              <h3 class="text-sm font-extrabold text-zinc-950 dark:text-white uppercase tracking-wider">Payment Telemetry</h3>
              <div class="relative group">
                <select class="pl-3 pr-8 py-1.5 bg-zinc-100 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-xl focus:outline-none focus:border-black dark:focus:border-white transition-all font-bold text-xs text-zinc-900 dark:text-white appearance-none cursor-pointer uppercase tracking-tight">
                  <option>15 Days</option>
                  <option>30 Days</option>
                  <option>90 Days</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none">
                  <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/></svg>
                </div>
              </div>
            </div>
            <div class="p-6">
              <div class="h-64">
                <canvas ref="paymentTrendsCanvas"></canvas>
              </div>
              <!-- Legend -->
              <div class="flex items-center justify-center space-x-8 mt-6">
                <div class="flex items-center">
                  <div class="w-3 h-3 bg-black dark:bg-white rounded-full mr-2"></div>
                  <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300">Payment Sent</span>
                </div>
                <div class="flex items-center">
                  <div class="w-3 h-3 bg-zinc-400 dark:bg-zinc-500 rounded-full mr-2"></div>
                  <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300">Payment Received</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Stock Alert -->
          <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-sm rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800">
              <h3 class="text-sm font-extrabold text-zinc-950 dark:text-white uppercase tracking-wider">Stock Alert Thresholds</h3>
            </div>
            <div class="p-6">
              <div class="space-y-3 max-h-64 overflow-y-auto custom-scrollbar">
                <div v-for="alert in dashboardData.stock_alerts" :key="alert.product" class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-950/60 rounded-xl border border-zinc-200/80 dark:border-zinc-800">
                  <div>
                    <p class="text-xs font-bold text-zinc-900 dark:text-zinc-100">{{ alert.product }}</p>
                  </div>
                  <div class="text-right">
                    <span class="text-xs font-black bg-black text-white dark:bg-white dark:text-black px-2.5 py-1 rounded-lg">
                      {{ alert.quantity }} LEFT
                    </span>
                  </div>
                </div>
                <div v-if="!dashboardData.stock_alerts?.length" class="text-center py-8 text-zinc-400 text-xs italic">
                  All inventory items are within healthy operational thresholds.
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment Breakdown Section -->
        <div v-if="dashboardData.payments?.by_type?.length > 0" class="mt-8">
          <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-sm rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
              <h3 class="text-sm font-extrabold text-zinc-950 dark:text-white uppercase tracking-wider">Payment Breakdown</h3>
              <router-link
                to="/payments"
                class="text-xs text-black dark:text-white font-extrabold hover:underline"
              >
                View All →
              </router-link>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- By Type -->
                <div>
                  <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-4">By Type</h4>
                  <div class="space-y-3">
                    <div v-for="type in dashboardData.payments.by_type" :key="type.payment_type" class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-950/60 rounded-xl border border-zinc-200/70 dark:border-zinc-800">
                      <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">{{ getPaymentTypeDisplay(type.payment_type) }}</span>
                      <div class="text-right">
                        <span class="text-xs font-extrabold text-zinc-950 dark:text-white">{{ formatAmount(type.total_amount) }}</span>
                        <span class="text-[10px] text-zinc-400 ml-1">({{ type.count }})</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- By Status -->
                <div>
                  <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-4">By Status</h4>
                  <div class="space-y-3">
                    <div v-for="status in dashboardData.payments.by_status" :key="status.status" class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-950/60 rounded-xl border border-zinc-200/70 dark:border-zinc-800">
                      <div class="flex items-center">
                        <span class="inline-flex px-2.5 py-0.5 text-[10px] font-extrabold rounded-full uppercase tracking-wider bg-black text-white dark:bg-white dark:text-black">
                          {{ status.status }}
                        </span>
                      </div>
                      <div class="text-right">
                        <span class="text-xs font-extrabold text-zinc-950 dark:text-white">{{ formatAmount(status.total_amount) }}</span>
                        <span class="text-[10px] text-zinc-400 ml-1">({{ status.count }})</span>
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
  Legend,
  LineController
} from 'chart.js';

// Register Chart.js components
Chart.register(CategoryScale, LinearScale, PointElement, LineElement, LineController, Title, Tooltip, Legend);

// Reactive data
const loading = ref(false);
const activePreset = ref('today');
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
  activePreset.value = 'today';
  const today = new Date().toISOString().split('T')[0];
  dateRange.value.from = today;
  dateRange.value.to = today;
  loadDashboardData();
};

const setThisWeek = () => {
  activePreset.value = 'week';
  const today = new Date();
  const firstDayOfWeek = new Date(today.setDate(today.getDate() - today.getDay()));
  const lastDayOfWeek = new Date(today.setDate(today.getDate() - today.getDay() + 6));

  dateRange.value.from = firstDayOfWeek.toISOString().split('T')[0];
  dateRange.value.to = lastDayOfWeek.toISOString().split('T')[0];
  loadDashboardData();
};

const setThisMonth = () => {
  activePreset.value = 'month';
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

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  });
};

const formatPercentage = (percentage) => {
  return Math.abs(percentage || 0).toFixed(1);
};

const createPaymentTrendsChart = () => {
  if (!paymentTrendsCanvas.value || !dashboardData.value.payment_trends?.length) return;

  const ctx = paymentTrendsCanvas.value.getContext('2d');
  const isDark = document.documentElement.classList.contains('dark');

  // Destroy existing chart if it exists
  if (paymentTrendsChart) {
    paymentTrendsChart.destroy();
  }

  const labels = dashboardData.value.payment_trends.map(item => item.date);
  const paymentSentData = dashboardData.value.payment_trends.map(item => item.payment_sent);
  const paymentReceivedData = dashboardData.value.payment_trends.map(item => item.payment_received);

  const mainColor = isDark ? '#ffffff' : '#000000';
  const secondaryColor = isDark ? '#a1a1aa' : '#71717a';

  paymentTrendsChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Payment Sent',
          data: paymentSentData,
          borderColor: mainColor,
          backgroundColor: isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)',
          borderWidth: 2.5,
          fill: true,
          tension: 0.35,
          pointBackgroundColor: mainColor,
          pointBorderColor: isDark ? '#09090b' : '#ffffff',
          pointBorderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6
        },
        {
          label: 'Payment Received',
          data: paymentReceivedData,
          borderColor: secondaryColor,
          backgroundColor: 'transparent',
          borderWidth: 2,
          borderDash: [4, 4],
          fill: false,
          tension: 0.35,
          pointBackgroundColor: secondaryColor,
          pointBorderColor: isDark ? '#09090b' : '#ffffff',
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
          backgroundColor: isDark ? '#18181b' : '#000000',
          titleColor: '#ffffff',
          bodyColor: '#ffffff',
          borderColor: isDark ? '#27272a' : '#27272a',
          borderWidth: 1,
          cornerRadius: 10,
          padding: 12,
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
            color: isDark ? '#a1a1aa' : '#71717a',
            font: {
              size: 11,
              weight: 'bold'
            }
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            color: isDark ? '#27272a' : '#e4e4e7',
            borderDash: [3, 3]
          },
          ticks: {
            color: isDark ? '#a1a1aa' : '#71717a',
            font: {
              size: 11
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
