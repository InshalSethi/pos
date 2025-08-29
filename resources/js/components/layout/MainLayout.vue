<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <div
      :class="[
        'fixed inset-y-0 left-0 z-50 bg-white shadow-lg transform transition-transform duration-300 ease-in-out',
        sidebarCollapsed ? 'w-16' : 'w-64',
        showMobileSidebar ? 'translate-x-0' : '-translate-x-full sm:translate-x-0'
      ]"
    >
      <!-- Sidebar Header -->
      <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200">
        <router-link
          to="/"
          :class="[
            'text-xl font-bold text-indigo-600 transition-opacity duration-300',
            sidebarCollapsed ? 'opacity-0' : 'opacity-100'
          ]"
        >
          <span v-show="!sidebarCollapsed">POS System</span>
        </router-link>
        <button
          @click="toggleSidebar"
          class="p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              :d="sidebarCollapsed ? 'M13 5l7 7-7 7M5 5l7 7-7 7' : 'M11 19l-7-7 7-7M5 12h14'"
            />
          </svg>
        </button>
      </div>

      <!-- Sidebar Navigation -->
      <nav class="mt-4 px-2 space-y-1 overflow-y-auto h-full pb-20">
        <!-- Dashboard -->
        <router-link
          to="/"
          :class="[
            'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
            $route.path === '/'
              ? 'bg-indigo-100 text-indigo-900'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Dashboard' : ''"
        >
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z" />
          </svg>
          <span :class="['ml-3 transition-opacity duration-300', sidebarCollapsed ? 'opacity-0 w-0' : 'opacity-100']">
            Dashboard
          </span>
        </router-link>

        <!-- POS -->
        <router-link
          to="/pos"
          :class="[
            'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
            $route.path === '/pos'
              ? 'bg-indigo-100 text-indigo-900'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'POS' : ''"
        >
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
          </svg>
          <span :class="['ml-3 transition-opacity duration-300', sidebarCollapsed ? 'opacity-0 w-0' : 'opacity-100']">
            POS
          </span>
        </router-link>

        <!-- Products -->
        <router-link
          to="/products"
          :class="[
            'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
            $route.path === '/products'
              ? 'bg-indigo-100 text-indigo-900'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Products' : ''"
        >
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
          </svg>
          <span :class="['ml-3 transition-opacity duration-300', sidebarCollapsed ? 'opacity-0 w-0' : 'opacity-100']">
            Products
          </span>
        </router-link>

        <!-- Sales Dropdown -->
        <div class="space-y-1">
          <button
            @click="showSidebarSalesMenu = !showSidebarSalesMenu"
            :class="[
              'group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
              $route.path.startsWith('/sales')
                ? 'bg-indigo-100 text-indigo-900'
                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
            ]"
            :title="sidebarCollapsed ? 'Sales' : ''"
          >
            <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span :class="['ml-3 transition-opacity duration-300', sidebarCollapsed ? 'opacity-0 w-0' : 'opacity-100']">
              Sales
            </span>
            <svg
              v-if="!sidebarCollapsed"
              :class="[
                'ml-auto h-4 w-4 transition-transform duration-200',
                showSidebarSalesMenu ? 'rotate-90' : ''
              ]"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>

          <div
            v-if="showSidebarSalesMenu && !sidebarCollapsed"
            class="ml-6 space-y-1"
          >
            <router-link
              to="/sales/invoices"
              :class="[
                'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
                $route.path === '/sales/invoices'
                  ? 'bg-indigo-50 text-indigo-700'
                  : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'
              ]"
            >
              Sales Invoices
            </router-link>
            <router-link
              to="/sales/returns"
              :class="[
                'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
                $route.path === '/sales/returns'
                  ? 'bg-indigo-50 text-indigo-700'
                  : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'
              ]"
            >
              Sales Returns
            </router-link>
          </div>
        </div>

        <!-- Purchase Dropdown -->
        <div class="space-y-1">
          <button
            @click="showSidebarPurchaseMenu = !showSidebarPurchaseMenu"
            :class="[
              'group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
              $route.path.startsWith('/purchase')
                ? 'bg-indigo-100 text-indigo-900'
                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
            ]"
            :title="sidebarCollapsed ? 'Purchase' : ''"
          >
            <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <span :class="['ml-3 transition-opacity duration-300', sidebarCollapsed ? 'opacity-0 w-0' : 'opacity-100']">
              Purchase
            </span>
            <svg
              v-if="!sidebarCollapsed"
              :class="[
                'ml-auto h-4 w-4 transition-transform duration-200',
                showSidebarPurchaseMenu ? 'rotate-90' : ''
              ]"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>

          <div
            v-if="showSidebarPurchaseMenu && !sidebarCollapsed"
            class="ml-6 space-y-1"
          >
            <router-link
              to="/purchase/orders"
              :class="[
                'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
                $route.path.startsWith('/purchase/orders')
                  ? 'bg-indigo-50 text-indigo-700'
                  : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'
              ]"
            >
              Purchase Orders
            </router-link>
            <router-link
              to="/purchase/returns"
              :class="[
                'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
                $route.path === '/purchase/returns'
                  ? 'bg-indigo-50 text-indigo-700'
                  : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'
              ]"
            >
              Purchase Returns
            </router-link>
          </div>
        </div>

        <!-- Continue with more navigation items -->
        <!-- Inventory -->
        <router-link
          to="/inventory"
          :class="[
            'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
            $route.path === '/inventory'
              ? 'bg-indigo-100 text-indigo-900'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Inventory' : ''"
        >
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
          <span :class="['ml-3 transition-opacity duration-300', sidebarCollapsed ? 'opacity-0 w-0' : 'opacity-100']">
            Inventory
          </span>
        </router-link>

        <!-- Accounting -->
        <router-link
          to="/accounting"
          :class="[
            'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
            $route.path === '/accounting'
              ? 'bg-indigo-100 text-indigo-900'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Accounting' : ''"
        >
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
          </svg>
          <span :class="['ml-3 transition-opacity duration-300', sidebarCollapsed ? 'opacity-0 w-0' : 'opacity-100']">
            Accounting
          </span>
        </router-link>

        <!-- Transactions -->
        <router-link
          to="/transactions"
          :class="[
            'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
            $route.path === '/transactions'
              ? 'bg-indigo-100 text-indigo-900'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Transactions' : ''"
        >
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
          </svg>
          <span :class="['ml-3 transition-opacity duration-300', sidebarCollapsed ? 'opacity-0 w-0' : 'opacity-100']">
            Transactions
          </span>
        </router-link>

        <!-- Expenses -->
        <router-link
          v-if="authStore.hasPermission('expenses.view')"
          to="/expenses"
          :class="[
            'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
            $route.path === '/expenses'
              ? 'bg-indigo-100 text-indigo-900'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Expenses' : ''"
        >
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2z" />
          </svg>
          <span :class="['ml-3 transition-opacity duration-300', sidebarCollapsed ? 'opacity-0 w-0' : 'opacity-100']">
            Expenses
          </span>
        </router-link>

        <!-- Payments Section -->
        <div v-if="authStore.hasPermission('payments.view') || authStore.hasPermission('payment_receipts.view')" class="space-y-1">
          <!-- Section Header -->
          <div v-if="!sidebarCollapsed" class="px-2 py-2">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
              Payments
            </h3>
          </div>

          <!-- Payments Out -->
          <router-link
            v-if="authStore.hasPermission('payments.view')"
            to="/payments"
            :class="[
              'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
              $route.path === '/payments'
                ? 'bg-indigo-100 text-indigo-900'
                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
            ]"
            :title="sidebarCollapsed ? 'Payments Out' : ''"
          >
            <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span :class="['ml-3 transition-opacity duration-300', sidebarCollapsed ? 'opacity-0 w-0' : 'opacity-100']">
              Payments Out
            </span>
          </router-link>

          <!-- Payment Receipts -->
          <router-link
            v-if="authStore.hasPermission('payment_receipts.view')"
            to="/payment-receipts"
            :class="[
              'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
              $route.path === '/payment-receipts'
                ? 'bg-indigo-100 text-indigo-900'
                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
            ]"
            :title="sidebarCollapsed ? 'Payment Receipts' : ''"
          >
            <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            <span :class="['ml-3 transition-opacity duration-300', sidebarCollapsed ? 'opacity-0 w-0' : 'opacity-100']">
              Payment Receipts
            </span>
          </router-link>
        </div>

        <!-- Divider -->
        <div v-if="!sidebarCollapsed && (authStore.hasPermission('payments.view') || authStore.hasPermission('payment_receipts.view'))" class="border-t border-gray-200 my-4"></div>

        <!-- Employees -->
        <router-link
          v-if="authStore.hasPermission('employees.view')"
          to="/employees"
          :class="[
            'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
            $route.path === '/employees'
              ? 'bg-indigo-100 text-indigo-900'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Employees' : ''"
        >
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
          </svg>
          <span :class="['ml-3 transition-opacity duration-300', sidebarCollapsed ? 'opacity-0 w-0' : 'opacity-100']">
            Employees
          </span>
        </router-link>

        <!-- Customers -->
        <router-link
          v-if="authStore.hasPermission('customers.view')"
          to="/customers"
          :class="[
            'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
            $route.path === '/customers'
              ? 'bg-indigo-100 text-indigo-900'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Customers' : ''"
        >
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <span :class="['ml-3 transition-opacity duration-300', sidebarCollapsed ? 'opacity-0 w-0' : 'opacity-100']">
            Customers
          </span>
        </router-link>

        <!-- Suppliers -->
        <router-link
          v-if="authStore.hasPermission('suppliers.view')"
          to="/suppliers"
          :class="[
            'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
            $route.path === '/suppliers'
              ? 'bg-indigo-100 text-indigo-900'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Suppliers' : ''"
        >
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
          </svg>
          <span :class="['ml-3 transition-opacity duration-300', sidebarCollapsed ? 'opacity-0 w-0' : 'opacity-100']">
            Suppliers
          </span>
        </router-link>

        <!-- Reports -->
        <router-link
          to="/reports"
          :class="[
            'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
            $route.path === '/reports'
              ? 'bg-indigo-100 text-indigo-900'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Reports' : ''"
        >
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          <span :class="['ml-3 transition-opacity duration-300', sidebarCollapsed ? 'opacity-0 w-0' : 'opacity-100']">
            Reports
          </span>
        </router-link>

        <!-- Users -->
        <router-link
          v-if="authStore.hasPermission('users.view')"
          to="/users"
          :class="[
            'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
            $route.path === '/users'
              ? 'bg-indigo-100 text-indigo-900'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Users' : ''"
        >
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
          </svg>
          <span :class="['ml-3 transition-opacity duration-300', sidebarCollapsed ? 'opacity-0 w-0' : 'opacity-100']">
            Users
          </span>
        </router-link>

        <!-- Roles -->
        <router-link
          v-if="authStore.hasPermission('users.view')"
          to="/roles"
          :class="[
            'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
            $route.path === '/roles'
              ? 'bg-indigo-100 text-indigo-900'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Roles' : ''"
        >
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
          <span :class="['ml-3 transition-opacity duration-300', sidebarCollapsed ? 'opacity-0 w-0' : 'opacity-100']">
            Roles
          </span>
        </router-link>

        <!-- Mobile User Profile Section (visible only on mobile when sidebar is open) -->
        <div class="sm:hidden mt-8 pt-4 border-t border-gray-200">
          <div class="flex items-center px-2 py-2">
            <div class="flex-shrink-0">
              <div class="h-8 w-8 rounded-full overflow-hidden bg-indigo-500 flex items-center justify-center">
                <img
                  v-if="authStore.user?.profile_image"
                  :src="getProfileImageUrl(authStore.user.profile_image)"
                  :alt="authStore.user?.name"
                  class="h-full w-full object-cover"
                />
                <span v-else class="text-white font-medium text-sm">
                  {{ authStore.user?.name?.charAt(0).toUpperCase() }}
                </span>
              </div>
            </div>
            <div class="ml-3">
              <div class="text-sm font-medium text-gray-800">{{ authStore.user?.name }}</div>
              <div class="text-xs text-gray-500">{{ authStore.user?.email }}</div>
            </div>
          </div>

          <div class="mt-2 space-y-1">
            <router-link
              to="/profile"
              class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900"
              @click="showMobileSidebar = false"
            >
              <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <span class="ml-3">Your Profile</span>
            </router-link>

            <router-link
              to="/settings"
              class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900"
              @click="showMobileSidebar = false"
            >
              <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span class="ml-3">Settings</span>
            </router-link>

            <button
              @click="handleLogout"
              class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900"
            >
              <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              <span class="ml-3">Sign out</span>
            </button>
          </div>
        </div>
      </nav>
    </div>

    <!-- Mobile sidebar overlay -->
    <div
      v-if="showMobileSidebar"
      class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 sm:hidden"
      @click="showMobileSidebar = false"
    ></div>

    <!-- Navigation -->
    <nav :class="[
      'bg-white shadow-sm border-b border-gray-200 transition-all duration-300 relative z-10',
      sidebarCollapsed ? 'sm:ml-16' : 'sm:ml-64'
    ]">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <!-- Left side - Mobile menu button and logo -->
          <div class="flex items-center">
            <!-- Mobile menu button -->
            <button
              @click="showMobileSidebar = !showMobileSidebar"
              class="sm:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <span class="sr-only">Open sidebar</span>
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
          </div>

          <!-- Right side - User menu -->
          <!-- Right side - User menu -->
          <div class="flex items-center space-x-4">
            <!-- Quick Add Dropdown -->
            <div class="relative">
              <button
                @click="showQuickAdd = !showQuickAdd"
                data-quick-add-button
                class="bg-green-500 hover:bg-green-600 p-2 rounded-full text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 relative transition-colors duration-200"
                title="Quick Add"
              >
                <span class="sr-only">Quick Add</span>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </button>

              <!-- Quick Add dropdown -->
              <div
                v-if="showQuickAdd"
                ref="quickAddRef"
                class="origin-top-right absolute right-0 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
              >
                <div class="py-1">
                  <div class="px-4 py-2 text-sm font-medium text-gray-900 border-b border-gray-200">
                    Quick Actions
                  </div>
                  <div class="p-4">
                    <div class="grid grid-cols-3 gap-3">
                      <!-- Sale Invoice -->
                      <router-link
                        v-if="authStore.hasPermission('sales.create')"
                        to="/sales/invoices/create"
                        @click="showQuickAdd = false"
                        class="flex flex-col items-center p-3 rounded-lg hover:bg-blue-50 transition-colors duration-200 group"
                      >
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mb-2 group-hover:bg-blue-200">
                          <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                          </svg>
                        </div>
                        <span class="text-xs text-center text-gray-700 group-hover:text-blue-700">Sale Invoice</span>
                      </router-link>

                      <!-- Sale Return -->
                      <router-link
                        v-if="authStore.hasPermission('sales.refund')"
                        to="/sales/returns/create"
                        @click="showQuickAdd = false"
                        class="flex flex-col items-center p-3 rounded-lg hover:bg-blue-50 transition-colors duration-200 group"
                      >
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mb-2 group-hover:bg-blue-200">
                          <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                          </svg>
                        </div>
                        <span class="text-xs text-center text-gray-700 group-hover:text-blue-700">Sale Return</span>
                      </router-link>

                      <!-- Purchase Order -->
                      <router-link
                        v-if="authStore.hasPermission('purchases.create')"
                        to="/purchase/orders/create"
                        @click="showQuickAdd = false"
                        class="flex flex-col items-center p-3 rounded-lg hover:bg-green-50 transition-colors duration-200 group"
                      >
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mb-2 group-hover:bg-green-200">
                          <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                          </svg>
                        </div>
                        <span class="text-xs text-center text-gray-700 group-hover:text-green-700">Purchase</span>
                      </router-link>

                      <!-- Expense -->
                      <router-link
                        v-if="authStore.hasPermission('expenses.create')"
                        to="/expenses/create"
                        @click="showQuickAdd = false"
                        class="flex flex-col items-center p-3 rounded-lg hover:bg-yellow-50 transition-colors duration-200 group"
                      >
                        <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center mb-2 group-hover:bg-yellow-200">
                          <svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                          </svg>
                        </div>
                        <span class="text-xs text-center text-gray-700 group-hover:text-yellow-700">Expense</span>
                      </router-link>

                      <!-- Employee -->
                      <router-link
                        v-if="authStore.hasPermission('employees.create')"
                        to="/employees/create"
                        @click="showQuickAdd = false"
                        class="flex flex-col items-center p-3 rounded-lg hover:bg-purple-50 transition-colors duration-200 group"
                      >
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mb-2 group-hover:bg-purple-200">
                          <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                          </svg>
                        </div>
                        <span class="text-xs text-center text-gray-700 group-hover:text-purple-700">Employee</span>
                      </router-link>

                      <!-- Customer -->
                      <router-link
                        v-if="authStore.hasPermission('customers.create')"
                        to="/customers/create"
                        @click="showQuickAdd = false"
                        class="flex flex-col items-center p-3 rounded-lg hover:bg-indigo-50 transition-colors duration-200 group"
                      >
                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mb-2 group-hover:bg-indigo-200">
                          <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                          </svg>
                        </div>
                        <span class="text-xs text-center text-gray-700 group-hover:text-indigo-700">Customer</span>
                      </router-link>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Notifications -->
            <div class="relative">
              <button
                @click="showNotifications = !showNotifications"
                data-notification-button
                class="bg-white p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 relative"
              >
                <span class="sr-only">View notifications</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM10.5 3.75a6 6 0 0 1 6 6v2.25l2.25 2.25v2.25H2.25v-2.25L4.5 12V9.75a6 6 0 0 1 6-6z" />
                </svg>
                <!-- Notification badge -->
                <span v-if="unreadNotifications > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                  {{ unreadNotifications > 9 ? '9+' : unreadNotifications }}
                </span>
              </button>

              <!-- Notifications dropdown -->
              <div
                v-if="showNotifications"
                ref="notificationsRef"
                class="origin-top-right absolute right-0 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
              >
                <div class="py-1">
                  <div class="px-4 py-2 text-sm font-medium text-gray-900 border-b border-gray-200">
                    Notifications
                  </div>
                  <div v-if="notifications.length === 0" class="px-4 py-3 text-sm text-gray-500 text-center">
                    No notifications
                  </div>
                  <div v-else class="max-h-64 overflow-y-auto">
                    <div
                      v-for="notification in notifications"
                      :key="notification.id"
                      class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100"
                      @click="markAsRead(notification.id)"
                    >
                      <div class="flex items-start">
                        <div class="flex-shrink-0">
                          <div :class="[
                            'w-2 h-2 rounded-full mt-2',
                            notification.read_at ? 'bg-gray-300' : 'bg-blue-500'
                          ]"></div>
                        </div>
                        <div class="ml-3 flex-1">
                          <p class="text-sm font-medium text-gray-900">{{ notification.data.title }}</p>
                          <p class="text-sm text-gray-500">{{ notification.data.message }}</p>
                          <p class="text-xs text-gray-400 mt-1">{{ formatDate(notification.created_at) }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="px-4 py-2 border-t border-gray-200">
                    <button
                      @click="markAllAsRead"
                      class="text-sm text-indigo-600 hover:text-indigo-500"
                    >
                      Mark all as read
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Profile dropdown -->
            <div class="ml-3 relative">
              <div>
                <button
                  @click="showUserMenu = !showUserMenu"
                  data-user-menu-button
                  class="bg-white flex text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  <span class="sr-only">Open user menu</span>
                  <div class="h-8 w-8 rounded-full overflow-hidden bg-indigo-500 flex items-center justify-center">
                    <img
                      v-if="authStore.user?.profile_image"
                      :src="getProfileImageUrl(authStore.user.profile_image)"
                      :alt="authStore.user?.name"
                      class="h-full w-full object-cover"
                    />
                    <span v-else class="text-white font-medium text-sm">
                      {{ authStore.user?.name?.charAt(0).toUpperCase() }}
                    </span>
                  </div>
                </button>
              </div>

              <!-- User dropdown menu -->
              <div
                v-if="showUserMenu"
                ref="userMenuRef"
                class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
              >
                <div class="px-4 py-2 text-sm text-gray-700 border-b border-gray-200">
                  <div class="font-medium">{{ authStore.user?.name }}</div>
                  <div class="text-gray-500">{{ authStore.user?.email }}</div>
                </div>
                
                <router-link
                  to="/profile"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  @click="showUserMenu = false"
                >
                  Your Profile
                </router-link>
                <router-link
                  to="/settings"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  @click="showUserMenu = false"
                >
                  Settings
                </router-link>
                <button
                  @click="handleLogout"
                  class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                  Sign out
                </button>
              </div>
            </div>
          </div>

        </div>
      </div>
    </nav>

    <!-- Main content -->
    <main :class="[
      'transition-all duration-300 min-h-screen',
      sidebarCollapsed ? 'sm:ml-16' : 'sm:ml-64'
    ]">
      <div class="p-4">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const router = useRouter();
const authStore = useAuthStore();

// Reactive data
const showUserMenu = ref(false);
const showMobileMenu = ref(false);
const showNotifications = ref(false);
const showQuickAdd = ref(false);
const showSalesMenu = ref(false);
const showPurchaseMenu = ref(false);
const sidebarCollapsed = ref(false);
const showMobileSidebar = ref(false);
const showSidebarSalesMenu = ref(false);
const showSidebarPurchaseMenu = ref(false);
const userMenuRef = ref(null);
const notificationsRef = ref(null);
const quickAddRef = ref(null);
const salesMenuRef = ref(null);
const purchaseMenuRef = ref(null);
const notifications = ref([]);

// Computed
const unreadNotifications = computed(() => {
  return notifications.value.filter(n => !n.read_at).length;
});

// Methods
const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};

const getProfileImageUrl = (imagePath) => {
  if (!imagePath) return null;
  if (imagePath.startsWith('http')) return imagePath;
  return `/storage/${imagePath}`;
};

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value;
  // Close sidebar submenus when collapsing
  if (sidebarCollapsed.value) {
    showSidebarSalesMenu.value = false;
    showSidebarPurchaseMenu.value = false;
  }
};

const handleClickOutside = (event) => {
  // Check if click is outside user menu
  if (showUserMenu.value && userMenuRef.value && !userMenuRef.value.contains(event.target)) {
    // Also check if the click is not on the user menu button
    const userMenuButton = document.querySelector('[data-user-menu-button]');
    if (!userMenuButton || !userMenuButton.contains(event.target)) {
      showUserMenu.value = false;
    }
  }

  // Check if click is outside notifications
  if (showNotifications.value && notificationsRef.value && !notificationsRef.value.contains(event.target)) {
    // Also check if the click is not on the notifications button
    const notificationButton = document.querySelector('[data-notification-button]');
    if (!notificationButton || !notificationButton.contains(event.target)) {
      showNotifications.value = false;
    }
  }

  // Check if click is outside quick add
  if (showQuickAdd.value && quickAddRef.value && !quickAddRef.value.contains(event.target)) {
    // Also check if the click is not on the quick add button
    const quickAddButton = document.querySelector('[data-quick-add-button]');
    if (!quickAddButton || !quickAddButton.contains(event.target)) {
      showQuickAdd.value = false;
    }
  }

  // Check if click is outside sales menu
  if (showSalesMenu.value && salesMenuRef.value && !salesMenuRef.value.contains(event.target)) {
    showSalesMenu.value = false;
  }

  // Check if click is outside purchase menu
  if (showPurchaseMenu.value && purchaseMenuRef.value && !purchaseMenuRef.value.contains(event.target)) {
    showPurchaseMenu.value = false;
  }
};

// Notification methods
const fetchNotifications = async () => {
  try {
    const response = await axios.get('/api/notifications');
    notifications.value = response.data;
  } catch (error) {
    console.error('Error fetching notifications:', error);
  }
};

const markAsRead = async (notificationId) => {
  try {
    await axios.post(`/api/notifications/${notificationId}/read`);
    const notification = notifications.value.find(n => n.id === notificationId);
    if (notification) {
      notification.read_at = new Date().toISOString();
    }
  } catch (error) {
    console.error('Error marking notification as read:', error);
  }
};

const markAllAsRead = async () => {
  try {
    await axios.post('/api/notifications/mark-all-read');
    notifications.value.forEach(n => {
      if (!n.read_at) {
        n.read_at = new Date().toISOString();
      }
    });
  } catch (error) {
    console.error('Error marking all notifications as read:', error);
  }
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  const now = new Date();
  const diffInHours = (now - date) / (1000 * 60 * 60);

  if (diffInHours < 1) {
    return 'Just now';
  } else if (diffInHours < 24) {
    return `${Math.floor(diffInHours)}h ago`;
  } else {
    return date.toLocaleDateString();
  }
};

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  fetchNotifications();

  // Poll for new notifications every 30 seconds
  setInterval(fetchNotifications, 30000);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
