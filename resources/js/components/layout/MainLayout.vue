<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <div
      :class="[
        'fixed inset-y-0 left-0 z-50 bg-white border-r border-gray-200 transform transition-all duration-300 ease-in-out flex flex-col',
        sidebarCollapsed ? 'w-20' : 'w-[260px]',
        showMobileSidebar ? 'translate-x-0' : '-translate-x-full sm:translate-x-0'
      ]"
    >
      <!-- Sidebar Header -->
      <div class="relative flex items-center justify-between h-16 px-4 border-b border-gray-200">
        <!-- Company Switcher -->
        <div class="relative flex-1 w-full min-w-0" v-if="activeCompany">
          <div class="flex items-center gap-3.5 p-1 min-w-0">
            <!-- AVATAR TRIGGER BUTTON -->
            <button
              type="button"
              @click="showLogoLightbox = true"
              :class="[
                'relative flex h-11 w-11 shrink-0 items-center justify-center rounded-xl overflow-hidden bg-gradient-to-tr from-indigo-600 to-purple-500 text-white font-bold text-lg shadow-md border border-white/10 hover:scale-105 active:scale-95 transition-all duration-150 cursor-zoom-in focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 group',
                sidebarCollapsed ? 'opacity-0 hidden' : 'opacity-100'
              ]"
              :title="'Preview ' + activeCompany.company_name + ' logo'"
              :aria-label="'Preview ' + activeCompany.company_name + ' logo'"
            >
              <!-- Logo image if available -->
              <img
                v-if="activeCompany.company_logo"
                :src="`/storage/${activeCompany.company_logo}`"
                :alt="activeCompany.company_name + ' logo'"
                class="h-full w-full object-cover group-hover:brightness-90 transition-all duration-150"
              >
              <!-- Initial letter fallback -->
              <span v-else class="select-none" aria-hidden="true">
                {{ activeCompany.company_name.charAt(0).toUpperCase() }}
              </span>

              <!-- Zoom hover overlay -->
              <div class="absolute inset-0 bg-black/15 opacity-0 group-hover:opacity-100 transition-opacity duration-150 flex items-center justify-center" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-white drop-shadow-sm">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.604 10.604z" />
                </svg>
              </div>
            </button>

            <!-- COMPANY NAME & WORKSPACE LABEL (Dropdown Trigger) -->
            <button
              type="button"
              @click="showCompanySwitcher = !showCompanySwitcher"
              data-company-switcher-button
              :class="[
                'flex-1 flex items-center min-w-0 text-left focus:outline-none transition-opacity duration-300',
                sidebarCollapsed ? 'opacity-0 hidden' : 'opacity-100'
              ]"
            >
              <div class="flex flex-col min-w-0 text-left w-full">
                <h2 class="text-sm font-bold text-gray-900 truncate tracking-wide leading-tight">
                  {{ activeCompany.company_name }}
                </h2>
                <span class="text-[10px] text-gray-500 font-medium truncate">
                  Enterprise Workspace
                </span>
              </div>
              <svg class="ml-1 flex-shrink-0 h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
              </svg>
            </button>
          </div>

          <!-- Dropdown menu -->
          <div
            v-if="showCompanySwitcher && !sidebarCollapsed"
            ref="companySwitcherRef"
            class="absolute left-0 right-0 w-full mt-3 bg-white border border-gray-200 rounded-xl shadow-2xl divide-y divide-gray-100 focus:outline-none z-50 overflow-hidden"
          >
            <div class="py-1 max-h-64 overflow-y-auto custom-scrollbar">
              <button
                v-for="company in companies"
                :key="company.id"
                @click="switchCompany(company.id); showCompanySwitcher = false"
                class="w-full text-left px-4 py-3 text-[13px] font-medium transition-colors flex items-center justify-between cursor-pointer"
                :class="company.id === activeCompany.id ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
              >
                <div class="flex items-center overflow-hidden">
                  <div class="mr-3 flex-shrink-0 h-5 w-5 rounded bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-[10px] shadow-sm overflow-hidden">
                    <img v-if="company.company_logo" :src="`/storage/${company.company_logo}`" class="w-full h-full object-cover" />
                    <span v-else>{{ company.company_name.charAt(0).toUpperCase() }}</span>
                  </div>
                  <span class="truncate">{{ company.company_name }}</span>
                </div>
                <svg v-if="company.id === activeCompany.id" class="ml-2 flex-shrink-0 h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </button>
            </div>
            <div class="py-1">
              <router-link to="/companies" class="flex items-center px-4 py-3 text-[13px] font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 dark:bg-indigo-600 dark:hover:bg-indigo-700 dark:text-white transition-colors w-full text-left" @click="showCompanySwitcher = false">
                <svg class="mr-3 flex-shrink-0 h-4 w-4 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Manage Company
              </router-link>
            </div>
          </div>
        </div>

        <!-- Collapse Trigger -->
        <button
          @click="toggleSidebar"
          class="absolute -right-3.5 top-6 w-7 h-7 bg-gray-900 text-white border border-gray-700 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 active:scale-95 z-50 group shadow-md"
        >
          <svg :class="['w-3.5 h-3.5 transition-transform duration-300', sidebarCollapsed ? 'rotate-180 group-hover:translate-x-0.5' : 'group-hover:-translate-x-0.5']" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
      </div>

      <!-- Sidebar Navigation -->
      <nav class="mt-6 px-4 space-y-1 overflow-y-auto flex-1 custom-scrollbar pb-20">
        <router-link
          to="/"
          :class="[
            'group flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl transition-all duration-200 relative',
            $route.path === '/'
              ? 'text-indigo-700 bg-indigo-50'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Dashboard' : ''"
        >
          <div v-if="$route.path === '/'" class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-indigo-500 rounded-r-full shadow-[0_0_10px_rgba(99,102,241,0.5)]"></div>
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z" />
          </svg>
          <span :class="['ml-3.5 transition-opacity duration-300 tracking-wide', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
            Dashboard
          </span>
        </router-link>
        <router-link
          to="/products"
          :class="[
            'group flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl transition-all duration-200 relative',
            $route.path === '/products'
              ? 'text-indigo-700 bg-indigo-50'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Products' : ''"
        >
          <div v-if="$route.path === '/products'" class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-indigo-500 rounded-r-full shadow-[0_0_10px_rgba(99,102,241,0.5)]"></div>
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
          </svg>
          <span :class="['ml-3.5 transition-opacity duration-300 tracking-wide', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
            Products
          </span>
        </router-link>
        <div class="space-y-1 mt-1 relative">
          <button
            @click="showSidebarSalesMenu = !showSidebarSalesMenu"
            :class="[
              'group w-full flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl transition-all duration-200 relative',
              $route.path.startsWith('/sales')
                ? 'text-indigo-700 bg-indigo-50'
                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
            ]"
            :title="sidebarCollapsed ? 'Sales' : ''"
          >
            <div v-if="$route.path.startsWith('/sales')" class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-indigo-500 rounded-r-full shadow-[0_0_10px_rgba(99,102,241,0.5)]"></div>
            <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span :class="['ml-3.5 transition-opacity duration-300 tracking-wide', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
              Sales
            </span>
            <div class="relative group ml-auto">
              <svg
                v-if="!sidebarCollapsed"
                :class="[
                  'h-4 w-4 transition-transform duration-300 transform',
                  showSidebarSalesMenu ? 'rotate-180 text-gray-900' : 'text-gray-400 group-hover:text-gray-600'
                ]"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
              >
                <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>
              </svg>
            </div>
          </button>

          <transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-2 max-h-0"
            enter-to-class="opacity-100 translate-y-0 max-h-60"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0 max-h-60"
            leave-to-class="opacity-0 -translate-y-2 max-h-0"
          >
            <div
              v-show="showSidebarSalesMenu && !sidebarCollapsed"
              class="pl-10 pr-2 py-1 space-y-0.5 relative overflow-hidden"
            >
              <!-- Timeline accent line -->
              <div class="absolute left-[22px] top-0 bottom-0 w-[1.5px] bg-gray-200"></div>
              <router-link
                to="/sales/invoices"
                :class="[
                  'group flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all duration-200 relative',
                  $route.path === '/sales/invoices'
                    ? 'text-indigo-700 bg-indigo-50'
                    : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50'
                ]"
              >
                <div v-if="$route.path === '/sales/invoices'" class="absolute -left-[18.5px] top-1/2 -translate-y-1/2 w-[1.5px] h-4 bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.6)]"></div>
                Sales Invoices
              </router-link>
              <router-link
                to="/sales/returns"
                :class="[
                  'group flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all duration-200 relative',
                  $route.path === '/sales/returns'
                    ? 'text-indigo-700 bg-indigo-50'
                    : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50'
                ]"
              >
                <div v-if="$route.path === '/sales/returns'" class="absolute -left-[18.5px] top-1/2 -translate-y-1/2 w-[1.5px] h-4 bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.6)]"></div>
                Sales Returns
              </router-link>
            </div>
          </transition>
        </div>
        <div class="space-y-1 mt-1 relative">
          <button
            @click="showSidebarPurchaseMenu = !showSidebarPurchaseMenu"
            :class="[
              'group w-full flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl transition-all duration-200 relative',
              $route.path.startsWith('/purchase')
                ? 'text-indigo-700 bg-indigo-50'
                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
            ]"
            :title="sidebarCollapsed ? 'Purchase' : ''"
          >
            <div v-if="$route.path.startsWith('/purchase')" class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-indigo-500 rounded-r-full shadow-[0_0_10px_rgba(99,102,241,0.5)]"></div>
            <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <span :class="['ml-3.5 transition-opacity duration-300 tracking-wide', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
              Purchase
            </span>
            <div class="relative group ml-auto">
              <svg
                v-if="!sidebarCollapsed"
                :class="[
                  'h-4 w-4 transition-transform duration-300 transform',
                  showSidebarPurchaseMenu ? 'rotate-180 text-gray-900' : 'text-gray-400 group-hover:text-gray-600'
                ]"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
              >
                <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>
              </svg>
            </div>
          </button>

          <transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-2 max-h-0"
            enter-to-class="opacity-100 translate-y-0 max-h-60"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0 max-h-60"
            leave-to-class="opacity-0 -translate-y-2 max-h-0"
          >
            <div
              v-show="showSidebarPurchaseMenu && !sidebarCollapsed"
              class="pl-10 pr-2 py-1 space-y-0.5 relative overflow-hidden"
            >
              <!-- Timeline accent line -->
              <div class="absolute left-[22px] top-0 bottom-0 w-[1.5px] bg-gray-200"></div>
              <router-link
                to="/purchase/orders"
                :class="[
                  'group flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all duration-200 relative',
                  $route.path === '/purchase/orders'
                    ? 'text-indigo-700 bg-indigo-50'
                    : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50'
                ]"
              >
                <div v-if="$route.path === '/purchase/orders'" class="absolute -left-[18.5px] top-1/2 -translate-y-1/2 w-[1.5px] h-4 bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.6)]"></div>
                Purchase Orders
              </router-link>
              <router-link
                to="/purchase/returns"
                :class="[
                  'group flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all duration-200 relative',
                  $route.path === '/purchase/returns'
                    ? 'text-indigo-700 bg-indigo-50'
                    : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50'
                ]"
              >
                <div v-if="$route.path === '/purchase/returns'" class="absolute -left-[18.5px] top-1/2 -translate-y-1/2 w-[1.5px] h-4 bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.6)]"></div>
                Purchase Returns
              </router-link>
            </div>
          </transition>
        </div>
        <router-link
          to="/inventory"
          :class="[
            'group flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl transition-all duration-200 relative',
            $route.path === '/inventory'
              ? 'text-indigo-700 bg-indigo-50'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Inventory' : ''"
        >
          <div v-if="$route.path === '/inventory'" class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-indigo-500 rounded-r-full shadow-[0_0_10px_rgba(99,102,241,0.5)]"></div>
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
          <span :class="['ml-3.5 transition-opacity duration-300 tracking-wide', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
            Inventory
          </span>
        </router-link>
        <router-link
          to="/accounting"
          :class="[
            'group flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl transition-all duration-200 relative',
            $route.path === '/accounting'
              ? 'text-indigo-700 bg-indigo-50'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Accounting' : ''"
        >
          <div v-if="$route.path === '/accounting'" class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-indigo-500 rounded-r-full shadow-[0_0_10px_rgba(99,102,241,0.5)]"></div>
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
          </svg>
          <span :class="['ml-3.5 transition-opacity duration-300 tracking-wide', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
            Accounting
          </span>
        </router-link>
        <router-link
          to="/transactions"
          :class="[
            'group flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl transition-all duration-200 relative',
            $route.path === '/transactions'
              ? 'text-indigo-700 bg-indigo-50'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Transactions' : ''"
        >
          <div v-if="$route.path === '/transactions'" class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-indigo-500 rounded-r-full shadow-[0_0_10px_rgba(99,102,241,0.5)]"></div>
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
          </svg>
          <span :class="['ml-3.5 transition-opacity duration-300 tracking-wide', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
            Transactions
          </span>
        </router-link>
        <router-link v-if="authStore.hasPermission('expenses.view')"
          to="/expenses"
          :class="[
            'group flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl transition-all duration-200 relative',
            $route.path === '/expenses'
              ? 'text-indigo-700 bg-indigo-50'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Expenses' : ''"
        >
          <div v-if="$route.path === '/expenses'" class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-indigo-500 rounded-r-full shadow-[0_0_10px_rgba(99,102,241,0.5)]"></div>
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2z" />
          </svg>
          <span :class="['ml-3.5 transition-opacity duration-300 tracking-wide', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
            Expenses
          </span>
        </router-link>
        <!-- Payments Section -->
        <div v-if="(authStore.hasPermission('payments.view') || authStore.hasPermission('payment_receipts.view')) && !sidebarCollapsed" class="px-3 pt-4 pb-1">
          <h3 class="text-[10px] tracking-widest text-gray-400 font-bold uppercase">
            Payments
          </h3>
        </div>
        <router-link v-if="authStore.hasPermission('payments.view')"
          to="/payments"
          :class="[
            'group flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl transition-all duration-200 relative',
            $route.path === '/payments'
              ? 'text-indigo-700 bg-indigo-50'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Payments Out' : ''"
        >
          <div v-if="$route.path === '/payments'" class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-indigo-500 rounded-r-full shadow-[0_0_10px_rgba(99,102,241,0.5)]"></div>
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <span :class="['ml-3.5 transition-opacity duration-300 tracking-wide', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
            Payments Out
          </span>
        </router-link>
        <router-link v-if="authStore.hasPermission('payment_receipts.view')"
          to="/payment-receipts"
          :class="[
            'group flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl transition-all duration-200 relative',
            $route.path === '/payment-receipts'
              ? 'text-indigo-700 bg-indigo-50'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Payment Receipts' : ''"
        >
          <div v-if="$route.path === '/payment-receipts'" class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-indigo-500 rounded-r-full shadow-[0_0_10px_rgba(99,102,241,0.5)]"></div>
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
          </svg>
          <span :class="['ml-3.5 transition-opacity duration-300 tracking-wide', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
            Payment Receipts
          </span>
        </router-link>
        <!-- People & Entities Section -->
        <div v-if="(authStore.hasPermission('employees.view') || authStore.hasPermission('customers.view') || authStore.hasPermission('suppliers.view')) && !sidebarCollapsed" class="px-3 pt-4 pb-1">
          <h3 class="text-[10px] tracking-widest text-gray-400 font-bold uppercase">
            People & Entities
          </h3>
        </div>
        <router-link v-if="authStore.hasPermission('employees.view')"
          to="/employees"
          :class="[
            'group flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl transition-all duration-200 relative',
            $route.path === '/employees'
              ? 'text-indigo-700 bg-indigo-50'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Employees' : ''"
        >
          <div v-if="$route.path === '/employees'" class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-indigo-500 rounded-r-full shadow-[0_0_10px_rgba(99,102,241,0.5)]"></div>
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
          </svg>
          <span :class="['ml-3.5 transition-opacity duration-300 tracking-wide', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
            Employees
          </span>
        </router-link>
        <router-link v-if="authStore.hasPermission('customers.view')"
          to="/customers"
          :class="[
            'group flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl transition-all duration-200 relative',
            $route.path === '/customers'
              ? 'text-indigo-700 bg-indigo-50'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Customers' : ''"
        >
          <div v-if="$route.path === '/customers'" class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-indigo-500 rounded-r-full shadow-[0_0_10px_rgba(99,102,241,0.5)]"></div>
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <span :class="['ml-3.5 transition-opacity duration-300 tracking-wide', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
            Customers
          </span>
        </router-link>
        <router-link v-if="authStore.hasPermission('suppliers.view')"
          to="/suppliers"
          :class="[
            'group flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl transition-all duration-200 relative',
            $route.path === '/suppliers'
              ? 'text-indigo-700 bg-indigo-50'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Suppliers' : ''"
        >
          <div v-if="$route.path === '/suppliers'" class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-indigo-500 rounded-r-full shadow-[0_0_10px_rgba(99,102,241,0.5)]"></div>
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
          </svg>
          <span :class="['ml-3.5 transition-opacity duration-300 tracking-wide', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
            Suppliers
          </span>
        </router-link>
        <!-- Analytics Section -->
        <div v-if="!sidebarCollapsed" class="px-3 pt-4 pb-1">
          <h3 class="text-[10px] tracking-widest text-gray-400 font-bold uppercase">
            Analytics
          </h3>
        </div>
        <router-link
          to="/reports"
          :class="[
            'group flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl transition-all duration-200 relative',
            $route.path === '/reports'
              ? 'text-indigo-700 bg-indigo-50'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          :title="sidebarCollapsed ? 'Reports' : ''"
        >
          <div v-if="$route.path === '/reports'" class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-indigo-500 rounded-r-full shadow-[0_0_10px_rgba(99,102,241,0.5)]"></div>
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          <span :class="['ml-3.5 transition-opacity duration-300 tracking-wide', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
            Reports
          </span>
        </router-link>

        <!-- Mobile User Profile Section (visible only on mobile when sidebar is open) -->
        <div class="sm:hidden mt-8 pt-4 border-t border-gray-200">
          <div class="flex items-center px-3 py-2">
            <div class="flex-shrink-0">
              <div class="h-9 w-9 rounded-full overflow-hidden bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                <img
                  v-if="authStore.user?.profile_image"
                  :src="getProfileImageUrl(authStore.user.profile_image)"
                  :alt="authStore.user?.name"
                  class="h-full w-full object-cover"
                />
                <span v-else class="text-white font-bold text-sm">
                  {{ authStore.user?.name?.charAt(0).toUpperCase() }}
                </span>
              </div>
            </div>
            <div class="ml-3">
              <div class="text-[13px] font-bold text-gray-900">{{ authStore.user?.name }}</div>
              <div class="text-[11px] text-gray-500">{{ authStore.user?.email }}</div>
            </div>
          </div>

          <div class="mt-2 space-y-1 px-1">
            <router-link
              to="/profile"
              class="group flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-all"
              @click="showMobileSidebar = false"
            >
              <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <span class="ml-3.5 tracking-wide">Your Profile</span>
            </router-link>

            <router-link
              to="/settings"
              class="group flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-all"
              @click="showMobileSidebar = false"
            >
              <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span class="ml-3.5 tracking-wide">Settings</span>
            </router-link>

            <button
              @click="handleLogout"
              class="group w-full flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-all"
            >
              <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              <span class="ml-3.5 tracking-wide">Sign out</span>
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
      'bg-white shadow-sm border-b border-gray-200 transition-all duration-300 sticky top-0 z-40 h-16 flex flex-col justify-center',
      sidebarCollapsed ? 'sm:ml-16' : 'sm:ml-64'
    ]">
      <div class="w-full">
        <div class="flex items-center justify-between px-6 w-full">
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
          <div class="flex items-center gap-4 ml-auto">
            <!-- Quick Add Dropdown -->
            <div class="relative">
              <button
                @click="showQuickAdd = !showQuickAdd"
                data-quick-add-button
                class="bg-green-500 hover:bg-green-600 p-1.5 rounded-full text-white focus:outline-none focus-visible:outline-none focus:ring-0 relative transition-colors duration-200"
                title="Quick Add"
              >
                <span class="sr-only">Quick Add</span>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </button>

              <!-- Quick Add dropdown -->
              <div
                v-if="showQuickAdd"
                ref="quickAddRef"
                class="origin-top-right absolute right-0 mt-3 w-80 rounded-2xl shadow-2xl bg-white/95 backdrop-blur-xl border border-white/20 ring-1 ring-black/5 focus:outline-none z-50 overflow-hidden transform transition-all duration-300 animate-in fade-in zoom-in-95"
              >
                <div class="py-0">
                  <div class="px-5 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                    <h3 class="text-[11px] font-black text-gray-400 uppercase tracking-[0.2em]">Quick Access Panel</h3>
                  </div>
                  <div class="p-4 bg-white/50">
                    <div class="grid grid-cols-3 gap-2">
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

            <!-- Theme Toggle Dropdown -->
            <div class="relative inline-block">
              <button
                @click="showThemeMenu = !showThemeMenu"
                data-theme-menu-button
                class="w-9 h-9 flex items-center justify-center rounded-xl bg-transparent border border-transparent focus:outline-none focus-visible:outline-none focus:ring-0 relative transition-all duration-300 hover:scale-105"
                title="Theme Settings"
              >
                <span class="sr-only">Theme Settings</span>
                <!-- Sun Icon for Light Mode (shown when NOT dark) -->
                <svg v-if="!isDarkMode" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="w-5 h-5 text-yellow-400">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <!-- Moon Icon for Dark Mode (shown when dark) -->
                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-5 h-5 text-indigo-200">
                  <path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
              </button>

              <!-- Theme Dropdown -->
              <div
                v-if="showThemeMenu"
                ref="themeMenuRef"
                class="origin-top-right absolute right-0 top-full mt-3 w-40 rounded-2xl shadow-2xl bg-white/95 backdrop-blur-xl border border-white/40 ring-1 ring-black/5 focus:outline-none z-50 overflow-hidden transform transition-all duration-300 animate-in fade-in zoom-in-95"
              >
                <div class="py-1">
                  <button @click="setTheme('light')" :class="['flex w-full items-center px-4 py-2 text-sm transition-colors', currentThemeSetting === 'light' ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:bg-gray-50']">
                    <svg class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    Light
                  </button>
                  <button @click="setTheme('dark')" :class="['flex w-full items-center px-4 py-2 text-sm transition-colors', currentThemeSetting === 'dark' ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:bg-gray-50']">
                    <svg class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                    Dark
                  </button>
                  <button @click="setTheme('system')" :class="['flex w-full items-center px-4 py-2 text-sm transition-colors', currentThemeSetting === 'system' ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:bg-gray-50']">
                    <svg class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    Match system
                  </button>
                </div>
              </div>
            </div>

            <!-- Notifications -->
            <div class="relative">
              <button
                @click="showNotifications = !showNotifications"
                data-notification-button
                class="bg-white p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus-visible:outline-none focus:ring-0 relative"
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
                class="origin-top-right absolute right-0 mt-3 w-96 rounded-[28px] shadow-[0_20px_50px_rgba(0,0,0,0.15)] bg-white/95 backdrop-blur-xl border border-white/40 ring-1 ring-black/5 focus:outline-none z-50 overflow-hidden transform transition-all duration-300 animate-in fade-in zoom-in-95"
              >
                <div class="py-0">
                  <div class="px-6 py-5 bg-gradient-to-br from-gray-50/80 to-white/80 border-b border-gray-100/50 flex justify-between items-center">
                    <div>
                      <h3 class="text-[10px] font-black text-indigo-600 uppercase tracking-[0.25em]">Intelligence Center</h3>
                      <p class="text-[11px] text-gray-400 font-medium">Real-time system activities and alerts</p>
                    </div>
                    <button @click="markAllAsRead" class="text-[9px] font-black text-gray-400 hover:text-indigo-600 uppercase tracking-widest bg-gray-100 hover:bg-indigo-50 px-2 py-1 rounded-full transition-all">
                      Clear All
                    </button>
                  </div>
                  <div v-if="notifications.length === 0" class="px-6 py-10 flex flex-col items-center justify-center space-y-3 opacity-40">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 17h5l-5 5v-5zM10.5 3.75a6 6 0 0 1 6 6v2.25l2.25 2.25v2.25H2.25v-2.25L4.5 12V9.75a6 6 0 0 1 6-6z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></svg>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">No active alerts</p>
                  </div>
                  <div v-else class="max-h-80 overflow-y-auto custom-scrollbar">
                    <div
                      v-for="notification in notifications"
                      :key="notification.id"
                      class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 transition-colors"
                      :class="{'opacity-60': notification.read_at}"
                      @click="markAsRead(notification.id)"
                    >
                      <!-- Low-stock alert style -->
                      <template v-if="notification.data && notification.data.type === 'low_stock'">
                        <div class="flex items-start gap-2.5">
                          <div class="mt-0.5 shrink-0 w-7 h-7 rounded-full bg-rose-100 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                          </div>
                          <div class="flex-1 min-w-0">
                            <p class="text-[11px] font-black text-rose-700 uppercase tracking-wide">{{ notification.data.title }}</p>
                            <p class="text-[12px] text-gray-700 mt-0.5 leading-snug">{{ notification.data.message }}</p>
                            <div class="flex items-center gap-2 mt-1.5">
                              <span class="px-1.5 py-0.5 bg-rose-50 text-rose-600 border border-rose-200 text-[9px] font-bold rounded">
                                Stock: {{ notification.data.current_stock }} / Min: {{ notification.data.min_alert }}
                              </span>
                              <span class="text-[10px] text-gray-400">{{ formatDate(notification.created_at) }}</span>
                            </div>
                          </div>
                          <div v-if="!notification.read_at" class="w-1.5 h-1.5 rounded-full bg-rose-500 mt-1.5 shrink-0 animate-pulse"></div>
                        </div>
                      </template>
                      <!-- Generic notification style -->
                      <template v-else>
                        <div class="flex items-start">
                          <div class="flex-shrink-0">
                            <div :class="['w-2 h-2 rounded-full mt-2', notification.read_at ? 'bg-gray-300' : 'bg-blue-500']"></div>
                          </div>
                          <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ notification.data?.title }}</p>
                            <p class="text-sm text-gray-500">{{ notification.data?.message }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ formatDate(notification.created_at) }}</p>
                          </div>
                        </div>
                      </template>
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
                  class="bg-white flex text-sm rounded-full focus:outline-none focus-visible:outline-none focus:ring-0"
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
                class="origin-top-right absolute right-0 mt-3 w-64 rounded-xl shadow-2xl bg-white border border-gray-200 focus:outline-none z-50 overflow-hidden"
              >
                <!-- User Header -->
                <div class="px-4 py-4 border-b border-gray-200 flex items-center space-x-3">
                  <div class="flex-shrink-0 h-10 w-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-black text-lg shadow-lg">
                    {{ authStore.user?.name?.charAt(0).toUpperCase() }}
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest mb-0.5">Operator</p>
                    <div class="text-[13px] font-bold text-gray-900 truncate tracking-wide leading-tight">{{ authStore.user?.name }}</div>
                    <div class="text-[11px] text-gray-400 font-medium truncate">{{ authStore.user?.email }}</div>
                  </div>
                </div>

                <!-- Menu Items -->
                <div class="py-1">
                  <router-link
                    to="/profile"
                    class="group flex items-center px-3 py-2.5 text-[13px] font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-all duration-200"
                    @click="showUserMenu = false"
                  >
                    <svg class="flex-shrink-0 h-5 w-5 mr-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></svg>
                    <span>Account Profile</span>
                  </router-link>
                  <router-link
                    to="/settings"
                    class="group flex items-center px-3 py-2.5 text-[13px] font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-all duration-200"
                    @click="showUserMenu = false"
                  >
                    <svg class="flex-shrink-0 h-5 w-5 mr-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/><circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></svg>
                    <span>System Settings</span>
                  </router-link>
                </div>

                <!-- Sign Out -->
                <div class="border-t border-gray-200 py-1">
                  <button
                    @click="handleLogout"
                    class="group w-full flex items-center px-3 py-2.5 text-[13px] font-medium text-red-500 hover:bg-red-50 transition-all duration-200"
                  >
                    <svg class="flex-shrink-0 h-5 w-5 mr-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></svg>
                    <span>Secure Sign Out</span>
                  </button>
                </div>
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
        <div v-if="renderError" class="bg-red-50 border-l-4 border-red-500 p-6 rounded-2xl shadow-md my-4 max-w-4xl mx-auto">
          <div class="flex items-start">
            <div class="flex-shrink-0 mt-1">
              <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div class="ml-4 flex-1">
              <h3 class="text-sm font-bold text-red-800 uppercase tracking-widest">Frontend Component Error Captured</h3>
              <div class="mt-2 text-xs font-semibold text-red-700">
                <p>The application encountered an error while rendering this page. The stack trace was logged to the console.</p>
                <div class="mt-3 bg-red-100/50 p-4 rounded-xl border border-red-200/50 font-mono text-[11px] overflow-x-auto text-red-900 select-all leading-relaxed whitespace-pre-wrap">
                  {{ renderErrorMessage }}
                </div>
              </div>
              <div class="mt-4 flex items-center space-x-3">
                <button @click="resetError" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-all duration-200 active:scale-95 shadow-sm">
                  Retry View
                </button>
                <button @click="$router.back()" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 font-bold rounded-xl text-xs uppercase tracking-wider transition-all duration-200 active:scale-95 shadow-sm">
                  Go Back
                </button>
              </div>
            </div>
          </div>
        </div>
        <router-view v-else />
      </div>
    </main>

    <!-- LOGO LIGHTBOX OVERLAY -->
    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-show="showLogoLightbox"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-950/80 backdrop-blur-md"
        role="dialog"
        aria-modal="true"
        :aria-label="activeCompany?.company_name + ' logo preview'"
        @keydown.esc.window="showLogoLightbox = false"
      >
        <!-- Backdrop click-to-dismiss -->
        <div class="absolute inset-0 cursor-zoom-out" @click="showLogoLightbox = false" aria-hidden="true"></div>

        <!-- Lightbox Card -->
        <div class="relative z-10 max-w-xl max-h-[80vh] p-3 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-2xl scale-100">
          <!-- Close Button -->
          <button
            @click="showLogoLightbox = false"
            type="button"
            class="absolute -top-3 -right-3 p-1.5 rounded-full bg-red-500 hover:bg-red-600 active:bg-red-700 text-white shadow-md active:scale-95 transition-all duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-400"
            aria-label="Close logo preview"
          >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <!-- Logo Preview Content -->
          <div class="relative group mt-2 mx-auto inline-block text-center flex justify-center">
            <img
              v-if="activeCompany?.company_logo"
              :src="`/storage/${activeCompany.company_logo}`"
              :alt="activeCompany?.company_name + ' full logo preview'"
              class="max-w-full max-h-[70vh] rounded-xl object-contain shadow-inner transition duration-300 group-hover:brightness-75"
            >
            <!-- Fallback state in lightbox -->
            <div v-else class="h-48 w-48 flex flex-col items-center justify-center bg-gradient-to-tr from-indigo-600 to-purple-500 rounded-xl text-white group-hover:brightness-75 transition duration-300">
              <span class="font-extrabold text-5xl select-none">{{ activeCompany?.company_name?.charAt(0).toUpperCase() }}</span>
              <p class="text-[11px] font-medium tracking-wide text-indigo-200 mt-2">No logo configured</p>
            </div>

            <!-- Quick Upload Overlay Button -->
            <button 
              type="button"
              @click="$refs.lightboxLogoInput.click()"
              class="absolute inset-0 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-white font-medium drop-shadow-md z-20 cursor-pointer w-full h-full rounded-xl"
            >
              <div class="bg-black/60 p-3 rounded-full mb-2 backdrop-blur-sm border border-white/20 hover:scale-105 active:scale-95 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
              </div>
              <span class="text-sm tracking-wide font-semibold text-white/90 bg-black/40 px-3 py-1 rounded-full backdrop-blur-sm">Change Logo</span>
            </button>

            <!-- Loading Spinner Overlay -->
            <div v-if="uploadingLogo" class="absolute inset-0 z-30 bg-gray-900/60 backdrop-blur-sm flex flex-col items-center justify-center rounded-xl">
              <svg class="animate-spin h-8 w-8 text-white mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span class="text-sm font-semibold text-white">Uploading...</span>
            </div>
          </div>
          
          <input type="file" ref="lightboxLogoInput" class="hidden" accept="image/*" @change="handleLightboxLogoUpload">

          <!-- Company name caption below image -->
          <p class="mt-2.5 text-center text-xs font-semibold text-gray-500 dark:text-zinc-400 truncate">
            {{ activeCompany?.company_name }}
          </p>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, onErrorCaptured } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const router = useRouter();
const authStore = useAuthStore();

const renderError = ref(false);
const renderErrorMessage = ref('');

onErrorCaptured((err, instance, info) => {
  console.error('[Error Boundary] Captured error in layout:', err);
  console.error('Component Instance:', instance);
  console.error('Error Info:', info);
  console.error('Stack trace:\n', err?.stack || err);
  renderError.value = true;
  renderErrorMessage.value = err?.stack || err?.message || String(err);
  return false;
});

const resetError = () => {
  renderError.value = false;
  renderErrorMessage.value = '';
};

router.afterEach(() => {
  resetError();
});

// Theme Management
const currentThemeSetting = ref(localStorage.getItem('theme') || 'system');
const isDarkMode = ref(false);

const applyTheme = (setting) => {
  if (setting === 'dark') {
    isDarkMode.value = true;
    document.documentElement.classList.add('dark');
  } else if (setting === 'light') {
    isDarkMode.value = false;
    document.documentElement.classList.remove('dark');
  } else {
    // system
    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
      isDarkMode.value = true;
      document.documentElement.classList.add('dark');
    } else {
      isDarkMode.value = false;
      document.documentElement.classList.remove('dark');
    }
  }
};

const setTheme = (setting) => {
  currentThemeSetting.value = setting;
  if (setting === 'system') {
    localStorage.removeItem('theme');
  } else {
    localStorage.setItem('theme', setting);
  }
  applyTheme(setting);
  showThemeMenu.value = false;
};
// Reactive data
const showLogoLightbox = ref(false);
const showThemeMenu = ref(false);
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
const themeMenuRef = ref(null);
const userMenuRef = ref(null);
const notificationsRef = ref(null);
const quickAddRef = ref(null);
const salesMenuRef = ref(null);
const purchaseMenuRef = ref(null);
const notifications = ref([]);
const companies = ref([]);
const activeCompany = ref(null);
const showCompanySwitcher = ref(false);
const companySwitcherRef = ref(null);
const uploadingLogo = ref(false);
const lightboxLogoInput = ref(null);

// Computed
const unreadNotifications = computed(() => {
  return notifications.value.filter(n => !n.read_at).length;
});

// Methods
const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};

const fetchCompanies = async () => {
  try {
    const response = await axios.get('/api/companies/my-companies');
    companies.value = response.data.companies;
    if (companies.value.length > 0) {
      activeCompany.value = companies.value.find(c => c.id == response.data.active_company_id) || companies.value[0];
    }
  } catch (error) {
    console.error('Failed to load companies', error);
  }
};

const switchCompany = async (companyId) => {
  try {
    await axios.post('/api/companies/switch', { company_id: companyId });
    window.location.href = '/';
  } catch (error) {
    console.error('Failed to switch company', error);
  }
};

const handleLightboxLogoUpload = async (e) => {
  const file = e.target.files[0];
  if (!file || !activeCompany.value) return;

  uploadingLogo.value = true;
  
  const formData = new FormData();
  formData.append('company_logo', file);
  formData.append('company_name', activeCompany.value.company_name);

  try {
    const response = await axios.post(`/api/companies/${activeCompany.value.id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    if (response.data && response.data.company) {
      activeCompany.value = response.data.company;
      const index = companies.value.findIndex(c => c.id === activeCompany.value.id);
      if (index !== -1) {
        companies.value[index] = response.data.company;
      }
      // Re-fetch slightly delayed to ensure state matches across app
      setTimeout(fetchCompanies, 500);
    }
  } catch (err) {
    console.error('Failed to update logo', err);
    alert('Failed to upload logo. Please try again.');
  } finally {
    uploadingLogo.value = false;
    if (lightboxLogoInput.value) {
      lightboxLogoInput.value.value = '';
    }
  }
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

  // Check if click is outside theme menu
  if (showThemeMenu.value && themeMenuRef.value && !themeMenuRef.value.contains(event.target)) {
    const themeBtn = document.querySelector('[data-theme-menu-button]');
    if (!themeBtn || !themeBtn.contains(event.target)) {
      showThemeMenu.value = false;
    }
  }

  // Check if click is outside notifications
  if (showNotifications.value && notificationsRef.value && !notificationsRef.value.contains(event.target)) {
    const notifButton = document.querySelector('[data-notifications-button]');
    if (!notifButton || !notifButton.contains(event.target)) {
      showNotifications.value = false;
    }
  }

  // Check if click is outside company switcher
  if (showCompanySwitcher.value && companySwitcherRef.value && !companySwitcherRef.value.contains(event.target)) {
    const switcherBtn = document.querySelector('[data-company-switcher-button]');
    if (!switcherBtn || !switcherBtn.contains(event.target)) {
      showCompanySwitcher.value = false;
    }
  }
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
  window.addEventListener('company-switched-globally', fetchCompanies);
  fetchNotifications();
  fetchCompanies();

  // Initialize Theme
  applyTheme(currentThemeSetting.value);
  
  // Listen for system theme changes
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if (currentThemeSetting.value === 'system') {
      applyTheme('system');
    }
  });

  // Poll for new notifications every 30 seconds
  setInterval(fetchNotifications, 30000);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  window.removeEventListener('company-switched-globally', fetchCompanies);
});
</script>
