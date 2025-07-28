<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Reports & Analytics</h1>

        <!-- Report Categories -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
          <!-- Sales Reports -->
          <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center mb-4">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-medium text-gray-900">Sales Reports</h3>
                <p class="text-sm text-gray-500">Revenue and sales analytics</p>
              </div>
            </div>
            <div class="space-y-2">
              <button
                @click="generateReport('sales-summary')"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded transition-colors"
              >
                Daily Sales Summary
              </button>
              <button
                @click="generateReport('monthly-revenue')"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded transition-colors"
              >
                Monthly Revenue Report
              </button>
              <button
                @click="generateReport('top-products')"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded transition-colors"
              >
                Top Selling Products
              </button>
              <button
                @click="generateReport('customer-analysis')"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded transition-colors"
              >
                Customer Sales Analysis
              </button>
            </div>
          </div>

          <!-- Inventory Reports -->
          <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center mb-4">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-medium text-gray-900">Inventory Reports</h3>
                <p class="text-sm text-gray-500">Stock and inventory analytics</p>
              </div>
            </div>
            <div class="space-y-2">
              <button
                @click="generateReport('inventory-summary')"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded transition-colors"
              >
                Current Stock Levels
              </button>
              <button
                @click="generateReport('low-stock')"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded transition-colors"
              >
                Low Stock Alert Report
              </button>
              <button
                @click="generateReport('inventory-valuation')"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded transition-colors"
              >
                Inventory Valuation
              </button>
              <button
                @click="generateReport('stock-movement')"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded transition-colors"
              >
                Stock Movement History
              </button>
            </div>
          </div>

          <!-- Financial Reports -->
          <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center mb-4">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-medium text-gray-900">Financial Reports</h3>
                <p class="text-sm text-gray-500">Accounting and financial analytics</p>
              </div>
            </div>
            <div class="space-y-2">
              <button
                @click="generateReport('profit-loss')"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded transition-colors"
              >
                Profit & Loss Statement
              </button>
              <button
                @click="generateReport('balance-sheet')"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded transition-colors"
              >
                Balance Sheet
              </button>
              <button
                @click="generateReport('cash-flow')"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded transition-colors"
              >
                Cash Flow Statement
              </button>
              <button
                @click="generateReport('trial-balance')"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded transition-colors"
              >
                Trial Balance
              </button>
            </div>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Today's Sales</p>
                <p class="text-2xl font-semibold text-gray-900">$0.00</p>
              </div>
            </div>
          </div>

          <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Products</p>
                <p class="text-2xl font-semibold text-gray-900">{{ totalProducts }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Low Stock Items</p>
                <p class="text-2xl font-semibold text-gray-900">{{ lowStockCount }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Customers</p>
                <p class="text-2xl font-semibold text-gray-900">{{ totalCustomers }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Report Generation -->
        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Generate Custom Report</h2>
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
              <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                <option>Sales Report</option>
                <option>Inventory Report</option>
                <option>Financial Report</option>
                <option>Customer Report</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
              <input
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
              <input
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
            <div class="flex items-end">
              <button
                @click="generateCustomReport"
                :disabled="generatingReport"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="generatingReport">Generating...</span>
                <span v-else>Generate Report</span>
              </button>
            </div>
          </div>
      </div>

      <!-- Report Modal -->
      <div v-if="showReportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
          <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-medium text-gray-900">{{ currentReportTitle }}</h3>
              <button
                @click="closeReportModal"
                class="text-gray-400 hover:text-gray-600"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>

            <!-- Loading State -->
            <div v-if="loadingReport" class="flex justify-center items-center py-8">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
              <span class="ml-3 text-gray-600">Loading report...</span>
            </div>

            <!-- Report Content -->
            <div v-else-if="reportData" class="space-y-6">
              <!-- Sales Reports -->
              <div v-if="currentReportType.includes('sales')">
                <div v-if="currentReportType === 'sales-summary'" class="space-y-4">
                  <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                      <div class="text-sm font-medium text-blue-600">Total Sales</div>
                      <div class="text-2xl font-bold text-blue-900">{{ reportData.total_sales || 0 }}</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                      <div class="text-sm font-medium text-green-600">Total Revenue</div>
                      <div class="text-2xl font-bold text-green-900">${{ formatCurrency(reportData.total_revenue) }}</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                      <div class="text-sm font-medium text-purple-600">Total Paid</div>
                      <div class="text-2xl font-bold text-purple-900">${{ formatCurrency(reportData.total_paid) }}</div>
                    </div>
                    <div class="bg-orange-50 p-4 rounded-lg">
                      <div class="text-sm font-medium text-orange-600">Average Sale</div>
                      <div class="text-2xl font-bold text-orange-900">${{ formatCurrency(reportData.average_sale) }}</div>
                    </div>
                  </div>
                </div>

                <div v-if="currentReportType === 'top-products'" class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Sold</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Times Sold</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="product in reportData" :key="product.id">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ product.name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.sku }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product.total_quantity }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ formatCurrency(product.total_revenue) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product.times_sold }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Inventory Reports -->
              <div v-if="currentReportType.includes('inventory') || currentReportType === 'low-stock'">
                <div v-if="currentReportType === 'inventory-summary'" class="space-y-4">
                  <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                      <div class="text-sm font-medium text-blue-600">Total Products</div>
                      <div class="text-2xl font-bold text-blue-900">{{ reportData.total_products || 0 }}</div>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg">
                      <div class="text-sm font-medium text-red-600">Low Stock Items</div>
                      <div class="text-2xl font-bold text-red-900">{{ reportData.low_stock_items || 0 }}</div>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg">
                      <div class="text-sm font-medium text-yellow-600">Out of Stock</div>
                      <div class="text-2xl font-bold text-yellow-900">{{ reportData.out_of_stock_items || 0 }}</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                      <div class="text-sm font-medium text-green-600">Inventory Value</div>
                      <div class="text-2xl font-bold text-green-900">${{ formatCurrency(reportData.total_inventory_value) }}</div>
                    </div>
                  </div>
                </div>

                <div v-if="currentReportType === 'low-stock'" class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="product in reportData" :key="product.id">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ product.name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.sku }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product.stock_quantity }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product.min_stock_level }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span :class="getStockStatusClass(product.stock_quantity)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                            {{ getStockStatus(product.stock_quantity) }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Financial Reports -->
              <div v-if="currentReportType.includes('profit-loss') || currentReportType.includes('balance-sheet') || currentReportType.includes('cash-flow') || currentReportType.includes('trial-balance')">
                <!-- Profit & Loss Statement -->
                <div v-if="currentReportType === 'profit-loss'" class="space-y-6">
                  <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Revenue</h4>
                    <div class="space-y-2">
                      <div v-for="item in reportData.revenue" :key="item.account_name" class="flex justify-between">
                        <span class="text-gray-700">{{ item.account_name }}</span>
                        <span class="font-medium">${{ formatCurrency(item.amount) }}</span>
                      </div>
                      <div class="border-t border-gray-200 pt-2 flex justify-between font-semibold">
                        <span>Total Revenue</span>
                        <span>${{ formatCurrency(reportData.total_revenue) }}</span>
                      </div>
                    </div>
                  </div>

                  <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Expenses</h4>
                    <div class="space-y-2">
                      <div v-for="item in reportData.expenses" :key="item.account_name" class="flex justify-between">
                        <span class="text-gray-700">{{ item.account_name }}</span>
                        <span class="font-medium">${{ formatCurrency(item.amount) }}</span>
                      </div>
                      <div class="border-t border-gray-200 pt-2 flex justify-between font-semibold">
                        <span>Total Expenses</span>
                        <span>${{ formatCurrency(reportData.total_expenses) }}</span>
                      </div>
                    </div>
                  </div>

                  <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                    <div class="flex justify-between items-center">
                      <span class="text-xl font-bold text-gray-900">Net Income</span>
                      <span :class="[
                        'text-xl font-bold',
                        reportData.net_income >= 0 ? 'text-green-600' : 'text-red-600'
                      ]">
                        ${{ formatCurrency(reportData.net_income) }}
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Balance Sheet -->
                <div v-if="currentReportType === 'balance-sheet'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                  <div class="space-y-6">
                    <!-- Assets -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                      <h4 class="text-lg font-semibold text-gray-900 mb-4">Assets</h4>

                      <div class="mb-4">
                        <h5 class="font-medium text-gray-800 mb-2">Current Assets</h5>
                        <div class="space-y-1 ml-4">
                          <div v-for="asset in reportData.current_assets" :key="asset.account_name" class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ asset.account_name }}</span>
                            <span>${{ formatCurrency(asset.amount) }}</span>
                          </div>
                        </div>
                        <div class="border-t border-gray-200 mt-2 pt-2 flex justify-between font-medium">
                          <span>Total Current Assets</span>
                          <span>${{ formatCurrency(reportData.total_current_assets) }}</span>
                        </div>
                      </div>

                      <div class="mb-4">
                        <h5 class="font-medium text-gray-800 mb-2">Fixed Assets</h5>
                        <div class="space-y-1 ml-4">
                          <div v-for="asset in reportData.fixed_assets" :key="asset.account_name" class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ asset.account_name }}</span>
                            <span>${{ formatCurrency(asset.amount) }}</span>
                          </div>
                        </div>
                        <div class="border-t border-gray-200 mt-2 pt-2 flex justify-between font-medium">
                          <span>Total Fixed Assets</span>
                          <span>${{ formatCurrency(reportData.total_fixed_assets) }}</span>
                        </div>
                      </div>

                      <div class="border-t border-gray-300 pt-4 flex justify-between font-bold text-lg">
                        <span>Total Assets</span>
                        <span>${{ formatCurrency(reportData.total_assets) }}</span>
                      </div>
                    </div>
                  </div>

                  <div class="space-y-6">
                    <!-- Liabilities & Equity -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                      <h4 class="text-lg font-semibold text-gray-900 mb-4">Liabilities & Equity</h4>

                      <div class="mb-4">
                        <h5 class="font-medium text-gray-800 mb-2">Current Liabilities</h5>
                        <div class="space-y-1 ml-4">
                          <div v-for="liability in reportData.current_liabilities" :key="liability.account_name" class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ liability.account_name }}</span>
                            <span>${{ formatCurrency(liability.amount) }}</span>
                          </div>
                        </div>
                        <div class="border-t border-gray-200 mt-2 pt-2 flex justify-between font-medium">
                          <span>Total Current Liabilities</span>
                          <span>${{ formatCurrency(reportData.total_current_liabilities) }}</span>
                        </div>
                      </div>

                      <div class="mb-4">
                        <h5 class="font-medium text-gray-800 mb-2">Long-term Liabilities</h5>
                        <div class="space-y-1 ml-4">
                          <div v-for="liability in reportData.long_term_liabilities" :key="liability.account_name" class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ liability.account_name }}</span>
                            <span>${{ formatCurrency(liability.amount) }}</span>
                          </div>
                        </div>
                        <div class="border-t border-gray-200 mt-2 pt-2 flex justify-between font-medium">
                          <span>Total Long-term Liabilities</span>
                          <span>${{ formatCurrency(reportData.total_long_term_liabilities) }}</span>
                        </div>
                      </div>

                      <div class="mb-4">
                        <h5 class="font-medium text-gray-800 mb-2">Equity</h5>
                        <div class="space-y-1 ml-4">
                          <div v-for="equity in reportData.equity" :key="equity.account_name" class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ equity.account_name }}</span>
                            <span>${{ formatCurrency(equity.amount) }}</span>
                          </div>
                        </div>
                        <div class="border-t border-gray-200 mt-2 pt-2 flex justify-between font-medium">
                          <span>Total Equity</span>
                          <span>${{ formatCurrency(reportData.total_equity) }}</span>
                        </div>
                      </div>

                      <div class="border-t border-gray-300 pt-4 flex justify-between font-bold text-lg">
                        <span>Total Liabilities & Equity</span>
                        <span>${{ formatCurrency(reportData.total_liabilities_equity) }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Trial Balance -->
                <div v-if="currentReportType === 'trial-balance'" class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Credit</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="account in reportData.accounts" :key="account.account_code">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                          {{ account.account_name }}
                          <div class="text-xs text-gray-500">{{ account.account_code }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                          {{ account.debit_balance > 0 ? formatCurrency(account.debit_balance) : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                          {{ account.credit_balance > 0 ? formatCurrency(account.credit_balance) : '-' }}
                        </td>
                      </tr>
                    </tbody>
                    <tfoot class="bg-gray-50">
                      <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">Total</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">
                          ${{ formatCurrency(reportData.total_debits) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">
                          ${{ formatCurrency(reportData.total_credits) }}
                        </td>
                      </tr>
                    </tfoot>
                  </table>
                </div>

                <!-- Cash Flow Statement -->
                <div v-if="currentReportType === 'cash-flow'" class="space-y-6">
                  <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Operating Activities</h4>
                    <div class="space-y-2">
                      <div v-for="item in reportData.operating_activities" :key="item.description" class="flex justify-between">
                        <span class="text-gray-700">{{ item.description }}</span>
                        <span class="font-medium">${{ formatCurrency(item.amount) }}</span>
                      </div>
                      <div class="border-t border-gray-200 pt-2 flex justify-between font-semibold">
                        <span>Net Cash from Operating Activities</span>
                        <span>${{ formatCurrency(reportData.net_operating_cash) }}</span>
                      </div>
                    </div>
                  </div>

                  <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Investing Activities</h4>
                    <div class="space-y-2">
                      <div v-for="item in reportData.investing_activities" :key="item.description" class="flex justify-between">
                        <span class="text-gray-700">{{ item.description }}</span>
                        <span class="font-medium">${{ formatCurrency(item.amount) }}</span>
                      </div>
                      <div class="border-t border-gray-200 pt-2 flex justify-between font-semibold">
                        <span>Net Cash from Investing Activities</span>
                        <span>${{ formatCurrency(reportData.net_investing_cash) }}</span>
                      </div>
                    </div>
                  </div>

                  <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Financing Activities</h4>
                    <div class="space-y-2">
                      <div v-for="item in reportData.financing_activities" :key="item.description" class="flex justify-between">
                        <span class="text-gray-700">{{ item.description }}</span>
                        <span class="font-medium">${{ formatCurrency(item.amount) }}</span>
                      </div>
                      <div class="border-t border-gray-200 pt-2 flex justify-between font-semibold">
                        <span>Net Cash from Financing Activities</span>
                        <span>${{ formatCurrency(reportData.net_financing_cash) }}</span>
                      </div>
                    </div>
                  </div>

                  <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                    <div class="space-y-2">
                      <div class="flex justify-between">
                        <span class="font-medium">Net Change in Cash</span>
                        <span class="font-medium">${{ formatCurrency(reportData.net_change_in_cash) }}</span>
                      </div>
                      <div class="flex justify-between">
                        <span class="text-gray-700">Cash at Beginning of Period</span>
                        <span>${{ formatCurrency(reportData.beginning_cash) }}</span>
                      </div>
                      <div class="border-t border-gray-200 pt-2 flex justify-between font-bold text-lg">
                        <span>Cash at End of Period</span>
                        <span>${{ formatCurrency(reportData.ending_cash) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Export Button -->
              <div class="flex justify-end pt-4 border-t border-gray-200">
                <button
                  @click="exportReport"
                  class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                >
                  Export to CSV
                </button>
              </div>
            </div>

            <!-- Error State -->
            <div v-else-if="reportError" class="text-center py-8">
              <div class="text-red-600 mb-2">
                <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <p class="text-gray-600">{{ reportError }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Reactive data
const totalProducts = ref(0);
const lowStockCount = ref(0);
const totalCustomers = ref(0);
const showReportModal = ref(false);
const loadingReport = ref(false);
const generatingReport = ref(false);
const reportData = ref(null);
const reportError = ref(null);
const currentReportType = ref('');
const currentReportTitle = ref('');

// Report form data
const reportForm = ref({
  type: 'sales',
  startDate: '',
  endDate: ''
});

// Methods
const fetchStats = async () => {
  try {
    // These would be real API calls in a production app
    totalProducts.value = 7; // Sample data
    lowStockCount.value = 2; // Sample data
    totalCustomers.value = 6; // Sample data
  } catch (error) {
    console.error('Error fetching stats:', error);
  }
};

const generateReport = async (reportType) => {
  console.log('Generating report:', reportType);
  currentReportType.value = reportType;
  currentReportTitle.value = getReportTitle(reportType);
  showReportModal.value = true;
  loadingReport.value = true;
  reportData.value = null;
  reportError.value = null;

  try {
    let endpoint = '';
    const params = {};

    switch (reportType) {
      case 'sales-summary':
        endpoint = '/api/reports/sales/summary';
        break;
      case 'monthly-revenue':
        endpoint = '/api/reports/sales/monthly-revenue';
        break;
      case 'top-products':
        endpoint = '/api/reports/sales/top-products';
        break;
      case 'customer-analysis':
        endpoint = '/api/reports/sales/customer-analysis';
        break;
      case 'inventory-summary':
        endpoint = '/api/reports/inventory/summary';
        break;
      case 'low-stock':
        endpoint = '/api/reports/inventory/low-stock';
        break;
      case 'inventory-valuation':
        endpoint = '/api/reports/inventory/valuation';
        break;
      case 'stock-movement':
        endpoint = '/api/reports/inventory/stock-movement';
        break;
      case 'profit-loss':
        endpoint = '/api/reports/financial/profit-loss';
        break;
      case 'balance-sheet':
        endpoint = '/api/reports/financial/balance-sheet';
        break;
      case 'cash-flow':
        endpoint = '/api/reports/financial/cash-flow';
        break;
      case 'trial-balance':
        endpoint = '/api/reports/financial/trial-balance';
        break;
      default:
        throw new Error('Report type not implemented yet');
    }

    console.log('Making request to:', endpoint);
    const response = await axios.get(endpoint, { params });
    console.log('Report response:', response.data);
    reportData.value = response.data;

  } catch (error) {
    console.error('Error generating report:', error);
    reportError.value = error.response?.data?.message || error.message || 'Failed to generate report';
  } finally {
    loadingReport.value = false;
  }
};

const generateCustomReport = async () => {
  generatingReport.value = true;

  try {
    const params = {
      start_date: reportForm.value.startDate,
      end_date: reportForm.value.endDate
    };

    let endpoint = '';
    switch (reportForm.value.type) {
      case 'sales':
        endpoint = '/api/reports/sales/summary';
        break;
      case 'inventory':
        endpoint = '/api/reports/inventory/summary';
        break;
      case 'financial':
        // This would be implemented later
        throw new Error('Financial reports not implemented yet');
      default:
        endpoint = '/api/reports/sales/summary';
    }

    const response = await axios.get(endpoint, { params });

    currentReportType.value = reportForm.value.type + '-custom';
    currentReportTitle.value = `Custom ${reportForm.value.type.charAt(0).toUpperCase() + reportForm.value.type.slice(1)} Report`;
    reportData.value = response.data;
    showReportModal.value = true;

  } catch (error) {
    console.error('Error generating custom report:', error);
    alert(error.response?.data?.message || error.message || 'Failed to generate report');
  } finally {
    generatingReport.value = false;
  }
};

const closeReportModal = () => {
  showReportModal.value = false;
  reportData.value = null;
  reportError.value = null;
  currentReportType.value = '';
  currentReportTitle.value = '';
};

const getReportTitle = (reportType) => {
  const titles = {
    'sales-summary': 'Daily Sales Summary',
    'monthly-revenue': 'Monthly Revenue Report',
    'top-products': 'Top Selling Products',
    'customer-analysis': 'Customer Sales Analysis',
    'inventory-summary': 'Current Stock Levels',
    'low-stock': 'Low Stock Alert Report',
    'inventory-valuation': 'Inventory Valuation',
    'stock-movement': 'Stock Movement History',
    'profit-loss': 'Profit & Loss Statement',
    'balance-sheet': 'Balance Sheet',
    'cash-flow': 'Cash Flow Statement',
    'trial-balance': 'Trial Balance'
  };
  return titles[reportType] || 'Report';
};

const formatCurrency = (amount) => {
  if (!amount) return '0.00';
  return parseFloat(amount).toFixed(2);
};

const getStockStatus = (quantity) => {
  if (quantity === 0) return 'Out of Stock';
  if (quantity <= 10) return 'Low Stock';
  return 'In Stock';
};

const getStockStatusClass = (quantity) => {
  if (quantity === 0) return 'bg-red-100 text-red-800';
  if (quantity <= 10) return 'bg-yellow-100 text-yellow-800';
  return 'bg-green-100 text-green-800';
};

const exportReport = () => {
  if (!reportData.value) return;

  // Convert report data to CSV
  let csvContent = '';

  if (Array.isArray(reportData.value)) {
    // Handle array data (like products, customers)
    if (reportData.value.length > 0) {
      const headers = Object.keys(reportData.value[0]);
      csvContent = headers.join(',') + '\n';

      reportData.value.forEach(row => {
        const values = headers.map(header => {
          const value = row[header];
          return typeof value === 'string' ? `"${value}"` : value;
        });
        csvContent += values.join(',') + '\n';
      });
    }
  } else {
    // Handle object data (like summary reports)
    csvContent = 'Metric,Value\n';
    Object.entries(reportData.value).forEach(([key, value]) => {
      if (typeof value !== 'object') {
        csvContent += `"${key}","${value}"\n`;
      }
    });
  }

  // Download CSV
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  link.setAttribute('href', url);
  link.setAttribute('download', `${currentReportType.value}-${new Date().toISOString().split('T')[0]}.csv`);
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

// Initialize dates
const today = new Date().toISOString().split('T')[0];
reportForm.value.startDate = today;
reportForm.value.endDate = today;

// Lifecycle
onMounted(() => {
  fetchStats();
});
</script>
