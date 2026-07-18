<template>
  <div class="min-h-screen bg-gray-100 dark:bg-[#121212]">
    <!-- Sidebar -->
    <div
      :class="[
        'fixed inset-y-0 left-0 z-50 bg-[#f0f2fa] dark:bg-[#1E1E1E] border-r border-gray-200/50 dark:border-[#2E2E2E] transform transition-all duration-300 ease-in-out flex flex-col',
        sidebarCollapsed ? 'w-20' : 'w-[260px]',
        showMobileSidebar ? 'translate-x-0' : '-translate-x-full sm:translate-x-0'
      ]"
    >
      <!-- Sidebar Header -->
      <div class="relative flex items-center justify-between h-16 px-4 border-b border-gray-200/50 dark:border-[#2E2E2E]">
        <!-- Company Switcher -->
        <div class="relative flex-1 w-full min-w-0" v-if="activeCompany">
          <div class="flex items-center gap-3.5 p-1 min-w-0">
            <!-- AVATAR TRIGGER BUTTON -->
            <button
              type="button"
              @click="showLogoLightbox = true"
              :class="[
                'relative flex h-11 w-11 shrink-0 items-center justify-center rounded-xl overflow-hidden shadow-sm transition-all duration-150 cursor-zoom-in focus:outline-none focus-visible:ring-1 focus-visible:ring-slate-350 dark:focus-visible:ring-slate-700 hover:scale-105 active:scale-95 group',
                activeCompany.company_logo 
                  ? 'bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-neutral-800' 
                  : 'bg-gradient-to-tr from-indigo-600 to-purple-500 text-white border border-white/10 font-bold text-lg',
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
                <h2 class="text-sm font-bold text-gray-900 dark:text-slate-100 truncate tracking-wide leading-tight">
                  {{ activeCompany.company_name }}
                </h2>
                <span class="text-[10px] text-gray-500 dark:text-slate-400 font-medium truncate">
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
            class="absolute left-0 right-0 w-full mt-3 bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#2E2E2E] rounded-xl shadow-2xl divide-y divide-gray-100 dark:divide-[#2E2E2E] focus:outline-none z-50 overflow-hidden"
          >
            <div class="py-1 max-h-64 overflow-y-auto custom-scrollbar">
              <button
                v-for="company in companies"
                :key="company.id"
                @click="switchCompany(company.id); showCompanySwitcher = false"
                class="w-full text-left px-4 py-3 text-[13px] font-medium transition-colors flex items-center justify-between cursor-pointer"
                :class="company.id === activeCompany.id ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-slate-400 dark:hover:bg-[#2D2D2D]/80 dark:hover:text-slate-200'"
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
              <router-link to="/companies" class="flex items-center px-4 py-3 text-[13px] font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 dark:bg-[#252525] dark:hover:bg-[#2D2D2D]/85 dark:text-indigo-400 transition-colors w-full text-left" @click="showCompanySwitcher = false">
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
          class="absolute -right-3.5 top-6 w-7 h-7 bg-white dark:bg-[#1E1E1E] text-gray-700 dark:text-slate-300 border border-gray-200 dark:border-[#2E2E2E] rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 active:scale-95 z-50 group shadow-sm cursor-pointer"
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
            'group flex items-center px-3 py-2.5 text-[13px] rounded-xl transition-all duration-200 relative',
            $route.path === '/'
              ? 'text-indigo-700 bg-indigo-50/80 border-l-4 border-indigo-600 dark:text-indigo-400 dark:bg-indigo-600/15 dark:border-l-4 dark:border-indigo-500 font-semibold rounded-l-none'
              : 'border-l-4 border-transparent text-slate-600 dark:text-slate-100 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800/40 dark:hover:text-slate-200 transition-all duration-200 font-medium rounded-l-none'
          ]"
          :title="sidebarCollapsed ? 'Dashboard' : ''"
        >

          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z" />
          </svg>
          <span :class="['ml-3.5 transition-opacity duration-300 tracking-wide', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
            Dashboard
          </span>
        </router-link>

        <!-- Favorites Section -->
        <div v-if="favorites.length > 0" class="mt-1">
          <div v-if="!sidebarCollapsed" class="px-3 pt-3 pb-1">
            <h3 class="text-[10px] tracking-widest text-amber-500 dark:text-amber-400 font-bold uppercase flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5 text-amber-500 fill-amber-500 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
              Favorites
            </h3>
          </div>
          <div class="space-y-0.5">
            <router-link
              v-for="fav in favorites"
              :key="fav.path"
              :to="fav.path"
              :class="[
                'group flex items-center px-3 py-2 text-[13px] font-medium rounded-xl transition-all duration-200 relative',
                $route.path === fav.path
                  ? 'text-indigo-700 bg-indigo-50/80 border-l-4 border-indigo-600 dark:text-indigo-400 dark:bg-indigo-600/15 dark:border-l-4 dark:border-indigo-500 font-semibold rounded-l-none'
                  : 'border-l-4 border-transparent text-slate-600 dark:text-slate-100 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800/40 dark:hover:text-slate-200 transition-all duration-200 font-medium rounded-l-none'
              ]"
              :title="sidebarCollapsed ? fav.name : ''"
            >
              <div v-if="$route.path === fav.path" class="absolute left-0 top-0 bottom-0 w-1 bg-amber-500"></div>
              <svg class="flex-shrink-0 h-4.5 w-4.5 text-amber-500 fill-amber-500" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.41l8.2-1.192z" />
              </svg>
              <span :class="['ml-3.5 transition-opacity duration-300 tracking-wide truncate', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
                {{ fav.name }}
              </span>
            </router-link>
          </div>
        </div>

        <div class="space-y-1 mt-1 relative">
          <button
            @click="showSidebarSalesMenu = !showSidebarSalesMenu"
            :class="[
              'group w-full flex items-center px-3 py-2.5 text-[13px] rounded-xl transition-all duration-200 relative',
              $route.path.startsWith('/sales')
                ? 'text-indigo-700 bg-indigo-50/80 border-l-4 border-indigo-600 dark:text-indigo-400 dark:bg-indigo-600/15 dark:border-l-4 dark:border-indigo-500 font-semibold rounded-l-none'
                : 'border-l-4 border-transparent text-slate-600 dark:text-slate-100 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800/40 dark:hover:text-slate-200 transition-all duration-200 font-medium rounded-l-none'
            ]"
            :title="sidebarCollapsed ? 'Sales' : ''"
          >

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
                  showSidebarSalesMenu ? 'rotate-180 text-gray-900' : 'text-slate-400 group-hover:text-slate-600'
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
              <div class="absolute left-[22px] top-0 bottom-0 w-[1.5px] bg-gray-200/60 dark:bg-[#2E2E2E]"></div>
              <router-link
                to="/sales/invoices"
                :class="[
                  'group flex items-center px-3 py-2 text-[13px] rounded-lg transition-all duration-200 relative',
                  $route.path === '/sales/invoices'
                    ? 'text-indigo-600 bg-indigo-50/40 dark:text-indigo-400 dark:bg-indigo-600/10 font-semibold'
                    : 'text-slate-500 dark:text-slate-100 hover:text-slate-950 dark:hover:text-slate-300 hover:bg-slate-100/50 dark:hover:bg-slate-800/30 transition-all duration-200 font-medium'
                ]"
              >
                <div v-if="$route.path === '/sales/invoices'" class="absolute -left-[18.5px] top-0 bottom-0 w-[1.5px] bg-indigo-600"></div>
                Sales Invoices
              </router-link>
              <router-link
                to="/sales/returns"
                :class="[
                  'group flex items-center px-3 py-2 text-[13px] rounded-lg transition-all duration-200 relative',
                  $route.path === '/sales/returns'
                    ? 'text-indigo-600 bg-indigo-50/40 dark:text-indigo-400 dark:bg-indigo-600/10 font-semibold'
                    : 'text-slate-500 dark:text-slate-100 hover:text-slate-950 dark:hover:text-slate-300 hover:bg-slate-100/50 dark:hover:bg-slate-800/30 transition-all duration-200 font-medium'
                ]"
              >
                <div v-if="$route.path === '/sales/returns'" class="absolute -left-[18.5px] top-0 bottom-0 w-[1.5px] bg-indigo-600"></div>
                Sales Returns
              </router-link>
            </div>
          </transition>
        </div>
        <div class="space-y-1 mt-1 relative">
          <button
            @click="showSidebarPurchaseMenu = !showSidebarPurchaseMenu"
            :class="[
              'group w-full flex items-center px-3 py-2.5 text-[13px] rounded-xl transition-all duration-200 relative',
              $route.path.startsWith('/purchase')
                ? 'text-indigo-700 bg-indigo-50/80 border-l-4 border-indigo-600 dark:text-indigo-400 dark:bg-indigo-600/15 dark:border-l-4 dark:border-indigo-500 font-semibold rounded-l-none'
                : 'border-l-4 border-transparent text-slate-600 dark:text-slate-100 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800/40 dark:hover:text-slate-200 transition-all duration-200 font-medium rounded-l-none'
            ]"
            :title="sidebarCollapsed ? 'Purchase' : ''"
          >

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
                  showSidebarPurchaseMenu ? 'rotate-180 text-gray-900' : 'text-slate-400 group-hover:text-slate-600'
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
              <div class="absolute left-[22px] top-0 bottom-0 w-[1.5px] bg-gray-200/60 dark:bg-[#2E2E2E]"></div>
              <router-link
                to="/purchase/orders"
                :class="[
                  'group flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all duration-200 relative',
                  $route.path === '/purchase/orders'
                    ? 'text-indigo-600 bg-indigo-50/40 dark:text-indigo-400 dark:bg-indigo-600/10 font-semibold'
                    : 'text-slate-500 dark:text-slate-100 hover:text-slate-950 dark:hover:text-slate-300 hover:bg-slate-100/50 dark:hover:bg-slate-800/30 transition-all duration-200 font-medium'
                ]"
              >
                <div v-if="$route.path === '/purchase/orders'" class="absolute -left-[18.5px] top-0 bottom-0 w-[1.5px] bg-indigo-600"></div>
                Purchase Orders
              </router-link>
              <router-link
                to="/purchase/returns"
                :class="[
                  'group flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all duration-200 relative',
                  $route.path === '/purchase/returns'
                    ? 'text-indigo-600 bg-indigo-50/40 dark:text-indigo-400 dark:bg-indigo-600/10 font-semibold'
                    : 'text-slate-500 dark:text-slate-100 hover:text-slate-950 dark:hover:text-slate-300 hover:bg-slate-100/50 dark:hover:bg-slate-800/30 transition-all duration-200 font-medium'
                ]"
              >
                <div v-if="$route.path === '/purchase/returns'" class="absolute -left-[18.5px] top-0 bottom-0 w-[1.5px] bg-indigo-600"></div>
                Purchase Returns
              </router-link>
            </div>
          </transition>
        </div>
        <div class="space-y-1 mt-1 relative">
          <button
            @click="showSidebarInventoryMenu = !showSidebarInventoryMenu"
            :class="[
              'group w-full flex items-center px-3 py-2.5 text-[13px] rounded-xl transition-all duration-200 relative',
              $route.path.startsWith('/inventory') || $route.path.startsWith('/products') || $route.path === '/settings/tax-tags'
                ? 'text-indigo-700 bg-indigo-50/80 border-l-4 border-indigo-600 dark:text-indigo-400 dark:bg-indigo-600/15 dark:border-l-4 dark:border-indigo-500 font-semibold rounded-l-none'
                : 'border-l-4 border-transparent text-slate-600 dark:text-slate-100 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800/40 dark:hover:text-slate-200 transition-all duration-200 font-medium rounded-l-none'
            ]"
            :title="sidebarCollapsed ? 'Inventory' : ''"
          >

            <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <span :class="['ml-3.5 transition-opacity duration-300 tracking-wide', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
              Inventory
            </span>
            <div class="relative group ml-auto">
              <svg
                v-if="!sidebarCollapsed"
                :class="[
                  'h-4 w-4 transition-transform duration-300 transform',
                  showSidebarInventoryMenu ? 'rotate-180 text-gray-900' : 'text-slate-400 group-hover:text-slate-600'
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
              v-show="showSidebarInventoryMenu && !sidebarCollapsed"
              class="pl-10 pr-2 py-1 space-y-0.5 relative overflow-hidden"
            >
              <!-- Timeline accent line -->
              <div class="absolute left-[22px] top-0 bottom-0 w-[1.5px] bg-gray-200/60 dark:bg-[#2E2E2E]"></div>
              <router-link
                to="/products"
                :class="[
                  'group flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all duration-200 relative',
                  $route.path === '/products' || $route.path.startsWith('/products/')
                    ? 'text-indigo-600 bg-indigo-50/40 dark:text-indigo-400 dark:bg-indigo-600/10 font-semibold'
                    : 'text-slate-500 dark:text-slate-100 hover:text-slate-950 dark:hover:text-slate-300 hover:bg-slate-100/50 dark:hover:bg-slate-800/30 transition-all duration-200 font-medium'
                ]"
              >
                <div v-if="$route.path === '/products' || $route.path.startsWith('/products/')" class="absolute -left-[18.5px] top-0 bottom-0 w-[1.5px] bg-indigo-600"></div>
                Items
              </router-link>
              <router-link
                to="/inventory/groups"
                :class="[
                  'group flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all duration-200 relative',
                  $route.path === '/inventory/groups'
                    ? 'text-indigo-600 bg-indigo-50/40 dark:text-indigo-400 dark:bg-indigo-600/10 font-semibold'
                    : 'text-slate-500 dark:text-slate-100 hover:text-slate-950 dark:hover:text-slate-300 hover:bg-slate-100/50 dark:hover:bg-slate-800/30 transition-all duration-200 font-medium'
                ]"
              >
                <div v-if="$route.path === '/inventory/groups'" class="absolute -left-[18.5px] top-0 bottom-0 w-[1.5px] bg-indigo-600"></div>
                Groups
              </router-link>
              <router-link
                to="/inventory/product-variations"
                :class="[
                  'group flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all duration-200 relative',
                  $route.path === '/inventory/product-variations'
                    ? 'text-indigo-600 bg-indigo-50/40 dark:text-indigo-400 dark:bg-indigo-600/10 font-semibold'
                    : 'text-slate-500 dark:text-slate-100 hover:text-slate-950 dark:hover:text-slate-300 hover:bg-slate-100/50 dark:hover:bg-slate-800/30 transition-all duration-200 font-medium'
                ]"
              >
                <div v-if="$route.path === '/inventory/product-variations'" class="absolute -left-[18.5px] top-0 bottom-0 w-[1.5px] bg-indigo-600"></div>
                Variations
              </router-link>
              <router-link
                to="/settings/tax-tags"
                :class="[
                  'group flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all duration-200 relative',
                  $route.path === '/settings/tax-tags'
                    ? 'text-indigo-600 bg-indigo-50/40 dark:text-indigo-400 dark:bg-indigo-600/10 font-semibold'
                    : 'text-slate-500 dark:text-slate-100 hover:text-slate-950 dark:hover:text-slate-300 hover:bg-slate-100/50 dark:hover:bg-slate-800/30 transition-all duration-200 font-medium'
                ]"
              >
                <div v-if="$route.path === '/settings/tax-tags'" class="absolute -left-[18.5px] top-0 bottom-0 w-[1.5px] bg-indigo-600"></div>
                Tax and Tags
              </router-link>
              <router-link
                to="/inventory/warehouses"
                :class="[
                  'group flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all duration-200 relative',
                  $route.path === '/inventory/warehouses'
                    ? 'text-indigo-600 bg-indigo-50/40 dark:text-indigo-400 dark:bg-indigo-600/10 font-semibold'
                    : 'text-slate-500 dark:text-slate-100 hover:text-slate-950 dark:hover:text-slate-300 hover:bg-slate-100/50 dark:hover:bg-slate-800/30 transition-all duration-200 font-medium'
                ]"
              >
                <div v-if="$route.path === '/inventory/warehouses'" class="absolute -left-[18.5px] top-0 bottom-0 w-[1.5px] bg-indigo-600"></div>
                Warehouses
              </router-link>
              <router-link
                to="/inventory/transfer-orders"
                :class="[
                  'group flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all duration-200 relative',
                  $route.path.startsWith('/inventory/transfer-orders')
                    ? 'text-indigo-600 bg-indigo-50/40 dark:text-indigo-400 dark:bg-indigo-600/10 font-semibold'
                    : 'text-slate-500 dark:text-slate-100 hover:text-slate-950 dark:hover:text-slate-300 hover:bg-slate-100/50 dark:hover:bg-slate-800/30 transition-all duration-200 font-medium'
                ]"
              >
                <div v-if="$route.path.startsWith('/inventory/transfer-orders')" class="absolute -left-[18.5px] top-0 bottom-0 w-[1.5px] bg-indigo-600"></div>
                Stock Transfers
              </router-link>
              <router-link
                to="/inventory"
                :class="[
                  'group flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all duration-200 relative',
                  $route.path === '/inventory'
                    ? 'text-indigo-600 bg-indigo-50/40 dark:text-indigo-400 dark:bg-indigo-600/10 font-semibold'
                    : 'text-slate-500 dark:text-slate-100 hover:text-slate-950 dark:hover:text-slate-300 hover:bg-slate-100/50 dark:hover:bg-slate-800/30 transition-all duration-200 font-medium'
                ]"
              >
                <div v-if="$route.path === '/inventory'" class="absolute -left-[18.5px] top-0 bottom-0 w-[1.5px] bg-indigo-600"></div>
                Adjustment
              </router-link>
              <router-link
                to="/inventory/histories"
                :class="[
                  'group flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all duration-200 relative',
                  $route.path === '/inventory/histories'
                    ? 'text-indigo-600 bg-indigo-50/40 dark:text-indigo-400 dark:bg-indigo-600/10 font-semibold'
                    : 'text-slate-500 dark:text-slate-100 hover:text-slate-950 dark:hover:text-slate-300 hover:bg-slate-100/50 dark:hover:bg-slate-800/30 transition-all duration-200 font-medium'
                ]"
              >
                <div v-if="$route.path === '/inventory/histories'" class="absolute -left-[18.5px] top-0 bottom-0 w-[1.5px] bg-indigo-600"></div>
                Histories
              </router-link>
            </div>
          </transition>
        </div>
        <router-link
          to="/accounting"
          :class="[
            'group flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl transition-all duration-200 relative',
            $route.path === '/accounting'
              ? 'text-indigo-700 bg-indigo-50/80 border-l-4 border-indigo-600 dark:text-indigo-400 dark:bg-indigo-600/15 dark:border-l-4 dark:border-indigo-500 font-semibold rounded-l-none'
              : 'border-l-4 border-transparent text-slate-600 dark:text-slate-100 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800/40 dark:hover:text-slate-200 transition-all duration-200 font-medium rounded-l-none'
          ]"
          :title="sidebarCollapsed ? 'Accounting' : ''"
        >

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
              ? 'text-indigo-700 bg-indigo-50/80 border-l-4 border-indigo-600 dark:text-indigo-400 dark:bg-indigo-600/15 dark:border-l-4 dark:border-indigo-500 font-semibold rounded-l-none'
              : 'border-l-4 border-transparent text-slate-600 dark:text-slate-100 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800/40 dark:hover:text-slate-200 transition-all duration-200 font-medium rounded-l-none'
          ]"
          :title="sidebarCollapsed ? 'Transactions' : ''"
        >

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
              ? 'text-indigo-700 bg-indigo-50/80 border-l-4 border-indigo-600 dark:text-indigo-400 dark:bg-indigo-600/15 dark:border-l-4 dark:border-indigo-500 font-semibold rounded-l-none'
              : 'border-l-4 border-transparent text-slate-600 dark:text-slate-100 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800/40 dark:hover:text-slate-200 transition-all duration-200 font-medium rounded-l-none'
          ]"
          :title="sidebarCollapsed ? 'Expenses' : ''"
        >

          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2z" />
          </svg>
          <span :class="['ml-3.5 transition-opacity duration-300 tracking-wide', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
            Expenses
          </span>
        </router-link>
        <!-- Payments Section -->
        <div v-if="(authStore.hasPermission('payments.view') || authStore.hasPermission('payment_receipts.view')) && !sidebarCollapsed" class="px-3 pt-4 pb-1">
          <h3 class="text-[10px] tracking-widest text-gray-400 dark:text-slate-550 font-bold uppercase">
            Payments
          </h3>
        </div>
        <router-link v-if="authStore.hasPermission('payments.view')"
          to="/payments"
          :class="[
            'group flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl transition-all duration-200 relative',
            $route.path === '/payments'
              ? 'text-indigo-700 bg-indigo-50/80 border-l-4 border-indigo-600 dark:text-indigo-400 dark:bg-indigo-600/15 dark:border-l-4 dark:border-indigo-500 font-semibold rounded-l-none'
              : 'border-l-4 border-transparent text-slate-600 dark:text-slate-100 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800/40 dark:hover:text-slate-200 transition-all duration-200 font-medium rounded-l-none'
          ]"
          :title="sidebarCollapsed ? 'Payments Out' : ''"
        >

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
              ? 'text-indigo-700 bg-indigo-50/80 border-l-4 border-indigo-600 dark:text-indigo-400 dark:bg-indigo-600/15 dark:border-l-4 dark:border-indigo-500 font-semibold rounded-l-none'
              : 'border-l-4 border-transparent text-slate-600 dark:text-slate-100 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800/40 dark:hover:text-slate-200 transition-all duration-200 font-medium rounded-l-none'
          ]"
          :title="sidebarCollapsed ? 'Payment Receipts' : ''"
        >

          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
          </svg>
          <span :class="['ml-3.5 transition-opacity duration-300 tracking-wide', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
            Payment Receipts
          </span>
        </router-link>
        <!-- People & Entities Section -->
        <div v-if="(authStore.hasPermission('employees.view') || authStore.hasPermission('customers.view') || authStore.hasPermission('suppliers.view')) && !sidebarCollapsed" class="px-3 pt-4 pb-1">
          <h3 class="text-[10px] tracking-widest text-gray-400 dark:text-slate-550 font-bold uppercase">
            People & Entities
          </h3>
        </div>
        <router-link v-if="authStore.hasPermission('employees.view')"
          to="/employees"
          :class="[
            'group flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl transition-all duration-200 relative',
            $route.path === '/employees'
              ? 'text-indigo-700 bg-indigo-50/80 border-l-4 border-indigo-600 dark:text-indigo-400 dark:bg-indigo-600/15 dark:border-l-4 dark:border-indigo-500 font-semibold rounded-l-none'
              : 'border-l-4 border-transparent text-slate-600 dark:text-slate-100 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800/40 dark:hover:text-slate-200 transition-all duration-200 font-medium rounded-l-none'
          ]"
          :title="sidebarCollapsed ? 'Employees' : ''"
        >

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
              ? 'text-indigo-700 bg-indigo-50/80 border-l-4 border-indigo-600 dark:text-indigo-400 dark:bg-indigo-600/15 dark:border-l-4 dark:border-indigo-500 font-semibold rounded-l-none'
              : 'border-l-4 border-transparent text-slate-600 dark:text-slate-100 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800/40 dark:hover:text-slate-200 transition-all duration-200 font-medium rounded-l-none'
          ]"
          :title="sidebarCollapsed ? 'Customers' : ''"
        >

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
              ? 'text-indigo-700 bg-indigo-50/80 border-l-4 border-indigo-600 dark:text-indigo-400 dark:bg-indigo-600/15 dark:border-l-4 dark:border-indigo-500 font-semibold rounded-l-none'
              : 'border-l-4 border-transparent text-slate-600 dark:text-slate-100 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800/40 dark:hover:text-slate-200 transition-all duration-200 font-medium rounded-l-none'
          ]"
          :title="sidebarCollapsed ? 'Suppliers' : ''"
        >

          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
          </svg>
          <span :class="['ml-3.5 transition-opacity duration-300 tracking-wide', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
            Suppliers
          </span>
        </router-link>
        <!-- Analytics Section -->
        <div v-if="!sidebarCollapsed" class="px-3 pt-4 pb-1">
          <h3 class="text-[10px] tracking-widest text-gray-400 dark:text-slate-550 font-bold uppercase">
            Analytics
          </h3>
        </div>
        <router-link
          to="/reports"
          :class="[
            'group flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl transition-all duration-200 relative',
            $route.path === '/reports'
              ? 'text-indigo-700 bg-indigo-50/80 border-l-4 border-indigo-600 dark:text-indigo-400 dark:bg-indigo-600/15 dark:border-l-4 dark:border-indigo-500 font-semibold rounded-l-none'
              : 'border-l-4 border-transparent text-slate-600 dark:text-slate-100 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800/40 dark:hover:text-slate-200 transition-all duration-200 font-medium rounded-l-none'
          ]"
          :title="sidebarCollapsed ? 'Reports' : ''"
        >

          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          <span :class="['ml-3.5 transition-opacity duration-300 tracking-wide', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
            Reports
          </span>
        </router-link>

        <!-- Settings & Support Section -->
        <div v-if="!sidebarCollapsed" class="px-3 pt-4 pb-1">
          <h3 class="text-[10px] tracking-widest text-gray-400 dark:text-slate-550 font-bold uppercase">
            System
          </h3>
        </div>
        <router-link
          to="/settings"
          :class="[
            'group flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl transition-all duration-200 relative',
            $route.path === '/settings'
              ? 'text-indigo-700 bg-indigo-50/80 border-l-4 border-indigo-600 dark:text-indigo-400 dark:bg-indigo-600/15 dark:border-l-4 dark:border-indigo-500 font-semibold rounded-l-none'
              : 'border-l-4 border-transparent text-slate-600 dark:text-slate-100 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800/40 dark:hover:text-slate-200 transition-all duration-200 font-medium rounded-l-none'
          ]"
          :title="sidebarCollapsed ? 'Settings' : ''"
        >

          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          <span :class="['ml-3.5 transition-opacity duration-300 tracking-wide', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
            Settings
          </span>
        </router-link>

        <button
          type="button"
          @click="showSupportModal = true"
          :class="[
            'group w-full flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl transition-all duration-200 relative cursor-pointer text-left',
            'border-l-4 border-transparent text-slate-600 dark:text-slate-100 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800/40 dark:hover:text-slate-200 transition-all duration-200 font-medium rounded-l-none'
          ]"
          :title="sidebarCollapsed ? 'Support & Help' : ''"
        >
          <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          <span :class="['ml-3.5 text-left transition-opacity duration-300 tracking-wide', sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100']">
            Support & Help
          </span>
        </button>

        <!-- Mobile User Profile Section (visible only on mobile when sidebar is open) -->
        <div class="sm:hidden mt-8 pt-4 border-t border-gray-200 dark:border-[#2E2E2E]">
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
              <div class="text-[13px] font-bold text-gray-900 dark:text-slate-100">{{ authStore.user?.name }}</div>
              <div class="text-[11px] text-gray-500 dark:text-slate-400">{{ authStore.user?.email }}</div>
            </div>
          </div>

          <div class="mt-2 space-y-1 px-1">
            <router-link
              to="/profile"
              class="group flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-[#2D2D2D]/80 hover:text-gray-900 dark:hover:text-slate-200 transition-all"
              @click="showMobileSidebar = false"
            >
              <svg class="flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <span class="ml-3.5 tracking-wide">Your Profile</span>
            </router-link>

            <router-link
              to="/settings"
              class="group flex items-center px-3 py-2.5 text-[13px] font-medium rounded-xl text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-[#2D2D2D]/80 hover:text-gray-900 dark:hover:text-slate-200 transition-all"
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
      'bg-white dark:bg-[#1E1E1E] shadow-sm border-b border-gray-200 dark:border-[#2E2E2E] transition-all duration-300 sticky top-0 z-40 h-16 flex flex-col justify-center',
      sidebarCollapsed ? 'sm:ml-16' : 'sm:ml-64'
    ]">
      <div class="w-full">
        <div class="flex items-center justify-between px-6 w-full">
          <!-- Left side - Mobile menu button, Page Title, Favorite toggle and Search -->
          <div class="flex items-center gap-3.5">
            <!-- Mobile menu button -->
            <button
              @click="showMobileSidebar = !showMobileSidebar"
              class="sm:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <span class="sr-only">Open sidebar</span>
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>

            <!-- Page Title & Favorite Star Toggle -->
            <div class="hidden sm:flex items-center gap-3 pl-4">
              <h1 class="text-base font-extrabold text-gray-800 dark:text-slate-100 tracking-wide select-none">
                {{ currentMenuItem?.name || 'POS System' }}
              </h1>
              
              <!-- Star Toggle -->
              <button
                v-if="currentMenuItem"
                @click="toggleCurrentRouteFavorite"
                class="p-1 rounded-lg text-gray-400 dark:text-slate-500 hover:text-amber-500 dark:hover:text-amber-400 transition-colors focus:outline-none cursor-pointer"
                :title="isCurrentRouteFavorited ? 'Remove from Favorites' : 'Add to Favorites'"
              >
                <!-- Solid gold star if favorited -->
                <svg v-if="isCurrentRouteFavorited" class="w-5 h-5 text-amber-500 fill-amber-500" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <!-- Empty outline star if not favorited -->
                <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.977-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
              </button>
            </div>

            <!-- Search bar -->
            <div class="relative ml-2 max-w-[200px] sm:max-w-xs w-48 sm:w-64 hidden md:block">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input
                v-model="searchQuery"
                @focus="showSearchResults = true"
                type="text"
                placeholder="Search features (e.g. Invoices...)"
                class="block w-full pl-9 pr-3 py-1.5 text-xs bg-gray-100 dark:bg-[#252525] border border-transparent dark:border-[#2E2E2E] rounded-xl focus:bg-white dark:focus:bg-[#1E1E1E] focus:outline-none focus:ring-1 focus:ring-slate-200 focus:border-slate-300 dark:focus:ring-slate-700 dark:focus:border-slate-700 dark:text-slate-200 placeholder-gray-400 dark:placeholder-slate-550 transition-all outline-none"
              />
              
              <!-- Search Results Dropdown -->
              <div
                v-if="showSearchResults && searchQuery"
                ref="searchResultsRef"
                class="origin-top-left absolute left-0 mt-2 w-72 rounded-xl shadow-2xl bg-white dark:bg-[#1E1E1E] border border-gray-250 dark:border-[#2E2E2E] z-50 overflow-hidden py-1 max-h-60 overflow-y-auto custom-scrollbar"
              >
                <div class="px-3 py-1.5 border-b border-gray-100 dark:border-[#2E2E2E] bg-gray-50/50 dark:bg-[#252525] flex justify-between">
                  <span class="text-[9px] font-black text-gray-400 dark:text-slate-550 uppercase tracking-widest">Menu Features</span>
                  <span class="text-[9px] text-gray-400 dark:text-slate-550 font-medium">{{ filteredMenuItems.length }} found</span>
                </div>
                <div v-if="filteredMenuItems.length === 0" class="px-4 py-6 text-center text-xs text-gray-400 dark:text-slate-500 font-medium">
                  No features found
                </div>
                <button
                  v-for="item in filteredMenuItems"
                  :key="item.path"
                  @click="navigateToSearchItem(item)"
                  class="w-full text-left px-3 py-2 text-xs hover:bg-indigo-50 dark:hover:bg-[#2D2D2D]/80 flex items-center gap-2.5 transition-colors cursor-pointer"
                >
                  <span class="text-indigo-500 dark:text-indigo-400 font-bold">⚡</span>
                  <div class="flex-1 min-w-0">
                    <p class="font-bold text-gray-700 dark:text-slate-200 truncate">{{ item.name }}</p>
                    <p class="text-[9px] text-gray-400 dark:text-slate-500 truncate">{{ item.category }}</p>
                  </div>
                </button>
              </div>
            </div>
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
                class="origin-top-right absolute right-0 mt-3 w-80 rounded-2xl shadow-2xl bg-white/95 dark:bg-[#1E1E1E]/95 backdrop-blur-xl border border-white/20 dark:border-[#2E2E2E] ring-1 ring-black/5 focus:outline-none z-50 overflow-hidden transform transition-all duration-300 animate-in fade-in zoom-in-95"
              >
                <div class="py-0">
                  <div class="px-5 py-4 bg-gradient-to-r from-gray-50 to-white dark:from-[#252525] dark:to-[#1E1E1E] border-b border-gray-100 dark:border-[#2E2E2E]">
                    <h3 class="text-[11px] font-black text-gray-400 uppercase tracking-[0.2em]">Quick Access Panel</h3>
                  </div>
                  <div class="p-4 bg-white/50 dark:bg-[#1E1E1E]/50">
                    <div class="grid grid-cols-3 gap-2">
                      <!-- Sale Invoice -->
                      <router-link
                        v-if="authStore.hasPermission('sales.create')"
                        to="/sales/invoices/create"
                        @click="showQuickAdd = false"
                        class="flex flex-col items-center p-3 rounded-lg hover:bg-blue-50 dark:hover:bg-[#2D2D2D]/80 transition-colors duration-200 group"
                      >
                        <div class="w-10 h-10 bg-blue-100 dark:bg-[#252525] rounded-full flex items-center justify-center mb-2 group-hover:bg-blue-200 dark:group-hover:bg-[#2D2D2D]/90">
                          <svg class="w-5 h-5 text-blue-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                          </svg>
                        </div>
                        <span class="text-xs text-center text-gray-700 dark:text-slate-400 group-hover:text-blue-700 dark:group-hover:text-indigo-400">Sale Invoice</span>
                      </router-link>
                      
                      <!-- Sale Return -->
                      <router-link
                        v-if="authStore.hasPermission('sales.refund')"
                        to="/sales/returns/create"
                        @click="showQuickAdd = false"
                        class="flex flex-col items-center p-3 rounded-lg hover:bg-blue-50 dark:hover:bg-[#2D2D2D]/80 transition-colors duration-200 group"
                      >
                        <div class="w-10 h-10 bg-blue-100 dark:bg-[#252525] rounded-full flex items-center justify-center mb-2 group-hover:bg-blue-200 dark:group-hover:bg-[#2D2D2D]/90">
                          <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                          </svg>
                        </div>
                        <span class="text-xs text-center text-gray-700 dark:text-slate-400 group-hover:text-blue-700 dark:group-hover:text-blue-400">Sale Return</span>
                      </router-link>

                      <!-- Purchase Order -->
                      <router-link
                        v-if="authStore.hasPermission('purchases.create')"
                        to="/purchase/orders/create"
                        @click="showQuickAdd = false"
                        class="flex flex-col items-center p-3 rounded-lg hover:bg-green-50 dark:hover:bg-[#2D2D2D]/80 transition-colors duration-200 group"
                      >
                        <div class="w-10 h-10 bg-green-100 dark:bg-[#252525] rounded-full flex items-center justify-center mb-2 group-hover:bg-green-200 dark:group-hover:bg-[#2D2D2D]/90">
                          <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                          </svg>
                        </div>
                        <span class="text-xs text-center text-gray-700 dark:text-slate-400 group-hover:text-green-700 dark:group-hover:text-green-400">Purchase</span>
                      </router-link>

                      <!-- Expense -->
                      <router-link
                        v-if="authStore.hasPermission('expenses.create')"
                        to="/expenses/create"
                        @click="showQuickAdd = false"
                        class="flex flex-col items-center p-3 rounded-lg hover:bg-yellow-50 dark:hover:bg-[#2D2D2D]/80 transition-colors duration-200 group"
                      >
                        <div class="w-10 h-10 bg-yellow-100 dark:bg-[#252525] rounded-full flex items-center justify-center mb-2 group-hover:bg-yellow-200 dark:group-hover:bg-[#2D2D2D]/90">
                          <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                          </svg>
                        </div>
                        <span class="text-xs text-center text-gray-700 dark:text-slate-400 group-hover:text-yellow-700 dark:group-hover:text-yellow-400">Expense</span>
                      </router-link>

                      <!-- Employee -->
                      <router-link
                        v-if="authStore.hasPermission('employees.create')"
                        to="/employees/create"
                        @click="showQuickAdd = false"
                        class="flex flex-col items-center p-3 rounded-lg hover:bg-purple-50 dark:hover:bg-[#2D2D2D]/80 transition-colors duration-200 group"
                      >
                        <div class="w-10 h-10 bg-purple-100 dark:bg-[#252525] rounded-full flex items-center justify-center mb-2 group-hover:bg-purple-200 dark:group-hover:bg-[#2D2D2D]/90">
                          <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                          </svg>
                        </div>
                        <span class="text-xs text-center text-gray-700 dark:text-slate-400 group-hover:text-purple-700 dark:group-hover:text-purple-400">Employee</span>
                      </router-link>

                      <!-- Customer -->
                      <router-link
                        v-if="authStore.hasPermission('customers.create')"
                        to="/customers/create"
                        @click="showQuickAdd = false"
                        class="flex flex-col items-center p-3 rounded-lg hover:bg-indigo-50 dark:hover:bg-[#2D2D2D]/80 transition-colors duration-200 group"
                      >
                        <div class="w-10 h-10 bg-indigo-100 dark:bg-[#252525] rounded-full flex items-center justify-center mb-2 group-hover:bg-indigo-200 dark:group-hover:bg-[#2D2D2D]/90">
                          <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                          </svg>
                        </div>
                        <span class="text-xs text-center text-gray-700 dark:text-slate-400 group-hover:text-indigo-700 dark:group-hover:text-indigo-450">Customer</span>
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
              </button>              <!-- Theme Dropdown -->
              <div
                v-if="showThemeMenu"
                ref="themeMenuRef"
                class="origin-top-right absolute right-0 top-full mt-3 w-40 rounded-2xl shadow-2xl bg-white/95 dark:bg-[#1E1E1E]/95 backdrop-blur-xl border border-white/40 dark:border-[#2E2E2E] ring-1 ring-black/5 focus:outline-none z-50 overflow-hidden transform transition-all duration-300 animate-in fade-in zoom-in-95"
              >
                <div class="py-1">
                  <button @click="setTheme('light')" :class="['flex w-full items-center px-4 py-2 text-sm transition-colors', currentThemeSetting === 'light' ? 'text-indigo-600 bg-indigo-50 dark:text-indigo-400 dark:bg-indigo-950/40' : 'text-gray-700 hover:bg-gray-50 dark:text-slate-400 dark:hover:bg-[#2D2D2D]/80 dark:hover:text-slate-200']">
                    <svg class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    Light
                  </button>
                  <button @click="setTheme('dark')" :class="['flex w-full items-center px-4 py-2 text-sm transition-colors', currentThemeSetting === 'dark' ? 'text-indigo-600 bg-indigo-50 dark:text-indigo-400 dark:bg-indigo-950/40' : 'text-gray-700 hover:bg-gray-50 dark:text-slate-400 dark:hover:bg-[#2D2D2D]/80 dark:hover:text-slate-200']">
                    <svg class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                    Dark
                  </button>
                  <button @click="setTheme('system')" :class="['flex w-full items-center px-4 py-2 text-sm transition-colors', currentThemeSetting === 'system' ? 'text-indigo-600 bg-indigo-50 dark:text-indigo-400 dark:bg-indigo-950/40' : 'text-gray-700 hover:bg-gray-50 dark:text-slate-400 dark:hover:bg-[#2D2D2D]/80 dark:hover:text-slate-200']">
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
                class="bg-white dark:bg-[#1E1E1E] p-1 rounded-full text-gray-400 dark:text-slate-450 hover:text-gray-500 dark:hover:text-slate-350 focus:outline-none focus-visible:outline-none focus:ring-0 relative"
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
                class="origin-top-right absolute right-0 mt-3 w-96 rounded-[28px] shadow-[0_20px_50px_rgba(0,0,0,0.15)] bg-white/95 dark:bg-[#1E1E1E]/95 backdrop-blur-xl border border-white/40 dark:border-[#2E2E2E] ring-1 ring-black/5 focus:outline-none z-50 overflow-hidden transform transition-all duration-300 animate-in fade-in zoom-in-95"
              >
                <div class="py-0">
                  <div class="px-6 py-5 bg-gradient-to-br from-gray-50/80 to-white/80 dark:from-[#252525]/85 dark:to-[#1E1E1E]/85 border-b border-gray-100/50 dark:border-[#2E2E2E] flex justify-between items-center">
                    <div>
                      <h3 class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-[0.25em]">Intelligence Center</h3>
                      <p class="text-[11px] text-gray-400 dark:text-slate-550 font-medium">Real-time system activities and alerts</p>
                    </div>
                    <button @click="markAllAsRead" class="text-[9px] font-black text-gray-400 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 uppercase tracking-widest bg-gray-100 dark:bg-[#252525] hover:bg-indigo-50 dark:hover:bg-[#2D2D2D]/80 px-2 py-1 rounded-full transition-all">
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
              </div>              <!-- User dropdown menu -->
              <div
                v-if="showUserMenu"
                ref="userMenuRef"
                class="origin-top-right absolute right-0 mt-3 w-64 rounded-xl shadow-2xl bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#2E2E2E] focus:outline-none z-50 overflow-hidden"
              >
                <!-- User Header -->
                <div class="px-4 py-4 border-b border-gray-200 dark:border-[#2E2E2E] flex items-center space-x-3">
                  <div class="flex-shrink-0 h-10 w-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-black text-lg shadow-lg">
                    {{ authStore.user?.name?.charAt(0).toUpperCase() }}
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mb-0.5">Operator</p>
                    <div class="text-[13px] font-bold text-gray-900 dark:text-slate-100 truncate tracking-wide leading-tight">{{ authStore.user?.name }}</div>
                    <div class="text-[11px] text-gray-400 dark:text-slate-500 font-medium truncate">{{ authStore.user?.email }}</div>
                  </div>
                </div>

                <!-- Menu Items -->
                <div class="py-1">
                  <router-link
                    to="/profile"
                    class="group flex items-center px-3 py-2.5 text-[13px] font-medium text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-[#2D2D2D]/80 hover:text-gray-900 dark:hover:text-slate-200 transition-all duration-200"
                    @click="showUserMenu = false"
                  >
                    <svg class="flex-shrink-0 h-5 w-5 mr-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></svg>
                    <span>Account Profile</span>
                  </router-link>
                  <router-link
                    to="/settings"
                    class="group flex items-center px-3 py-2.5 text-[13px] font-medium text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-[#2D2D2D]/80 hover:text-gray-900 dark:hover:text-slate-200 transition-all duration-200"
                    @click="showUserMenu = false"
                  >
                    <svg class="flex-shrink-0 h-5 w-5 mr-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/><circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></svg>
                    <span>System Settings</span>
                  </router-link>
                </div>

                <!-- Sign Out -->
                <div class="border-t border-gray-250 dark:border-[#2E2E2E] py-1">
                  <button
                    @click="handleLogout"
                    class="group w-full flex items-center px-3 py-2.5 text-[13px] font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 transition-all duration-200"
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
      <div class="">
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
        <div class="relative z-10 max-w-xl max-h-[80vh] p-3 bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#2E2E2E] rounded-2xl shadow-2xl scale-100">
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

    <!-- SUPPORT & HELP MODAL -->
    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="showSupportModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/60 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        aria-labelledby="support-modal-title"
      >
        <!-- Modal Card -->
        <div class="relative bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#2E2E2E] rounded-[28px] max-w-2xl w-full overflow-hidden shadow-2xl flex flex-col max-h-[85vh]">
          <!-- Close Button -->
          <button
            @click="showSupportModal = false"
            class="absolute top-4 right-4 p-2 rounded-full hover:bg-gray-100 dark:hover:bg-[#2D2D2D]/80 text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 transition-colors cursor-pointer"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <!-- Modal Header -->
          <div class="p-6 border-b border-gray-100 dark:border-[#2E2E2E] bg-gradient-to-r from-indigo-50 to-white dark:from-[#252525] dark:to-[#1E1E1E]">
            <h2 id="support-modal-title" class="text-lg font-extrabold text-gray-900 dark:text-slate-100 flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 animate-pulse"></span>
              Support & Help Desk
            </h2>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Submit support tickets, read documentation, or view your history.</p>
          </div>          <!-- Modal Content -->
          <div class="p-6 overflow-y-auto flex-1 space-y-6 custom-scrollbar">
            <!-- Documentation View -->
            <div v-if="showGroupsDoc" class="space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-300">
              <!-- Back Button -->
              <button
                @click="showGroupsDoc = false"
                class="flex items-center gap-2 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors focus:outline-none cursor-pointer"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Support Desk
              </button>

              <div class="prose dark:prose-invert max-w-none">
                <div class="flex items-center gap-2.5 mb-2">
                  <div class="text-xl">📦</div>
                  <h3 class="text-base font-black text-gray-800 dark:text-slate-100 my-0">Inventory: Groups Module Manual</h3>
                </div>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-0">Classification, Hierarchy, and Bulk Application Settings</p>
                <div class="w-full h-px bg-gray-100 dark:bg-slate-800 my-4"></div>

                <!-- Section 1 -->
                <div class="space-y-3">
                  <h4 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">1. Group Creation & Management</h4>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-50 dark:bg-[#252525] rounded-2xl border border-gray-100 dark:border-[#2E2E2E]/60">
                      <h5 class="text-xs font-bold text-gray-700 dark:text-slate-200 mb-1 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Group Name
                      </h5>
                      <p class="text-[11px] leading-relaxed text-gray-550 dark:text-slate-400">
                        Create customizable category groups based on product types or lines (e.g., <strong>Electronics</strong>, <strong>Clothing</strong>, <strong>Furniture</strong>, or <strong>Raw Materials</strong>).
                      </p>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-[#252525] rounded-2xl border border-gray-100 dark:border-[#2E2E2E]/60">
                      <h5 class="text-xs font-bold text-gray-700 dark:text-slate-200 mb-1 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Parent/Child Groups
                      </h5>
                      <p class="text-[11px] leading-relaxed text-gray-550 dark:text-slate-400">
                        Organize catalog nesting using multi-level hierarchies (e.g., <em>Clothing &rarr; Men's Wear &rarr; Shirts</em>) for cleaner structural management.
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Section 2 -->
                <div class="space-y-3 mt-6">
                  <h4 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">2. Bulk Actions & Settings</h4>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-50 dark:bg-[#252525] rounded-2xl border border-gray-100 dark:border-[#2E2E2E]/60">
                      <h5 class="text-xs font-bold text-gray-700 dark:text-slate-200 mb-1 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Mass Pricing & Discounts
                      </h5>
                      <p class="text-[11px] leading-relaxed text-gray-550 dark:text-slate-400">
                        Apply batch rules, pricing overrides, markups, or seasonal discounts to all products under a group in a single step instead of editing individual items.
                      </p>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-[#252525] rounded-2xl border border-gray-100 dark:border-[#2E2E2E]/60">
                      <h5 class="text-xs font-bold text-gray-700 dark:text-slate-200 mb-1 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Tax Assignment
                      </h5>
                      <p class="text-[11px] leading-relaxed text-gray-550 dark:text-slate-400">
                        Link a group directly to standard tax tiers (e.g. 18% GST). Inherited rules automatically apply tax codes on checkout when items from this group are selected.
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Section 3 -->
                <div class="space-y-3 mt-6">
                  <h4 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">3. Benefits & Value</h4>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 bg-indigo-50/30 dark:bg-indigo-950/10 rounded-2xl border border-indigo-100/50 dark:border-indigo-950/40">
                      <h5 class="text-xs font-bold text-gray-700 dark:text-slate-200 mb-1 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Filtered Searching
                      </h5>
                      <p class="text-[11px] leading-relaxed text-gray-550 dark:text-slate-400">
                        Accelerate sales invoicing and billing. Quickly filter list views or search results by group tags when generating invoices or purchase bills.
                      </p>
                    </div>
                    <div class="p-4 bg-indigo-50/30 dark:bg-indigo-950/10 rounded-2xl border border-indigo-100/50 dark:border-indigo-950/40">
                      <h5 class="text-xs font-bold text-gray-700 dark:text-slate-200 mb-1 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Group-Wise Reporting
                      </h5>
                      <p class="text-[11px] leading-relaxed text-gray-550 dark:text-slate-400">
                        Execute category-based audits to determine which groups drive the highest sales revenues, incur the most expenses, or have low stock turn rates.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Histories Documentation View -->
            <div v-else-if="showHistoriesDoc" class="space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-300">
              <!-- Back Button -->
              <button
                @click="showHistoriesDoc = false"
                class="flex items-center gap-2 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors focus:outline-none cursor-pointer"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Support Desk
              </button>

              <div class="prose dark:prose-invert max-w-none">
                <div class="flex items-center gap-2.5 mb-2">
                  <div class="text-xl">📜</div>
                  <h3 class="text-base font-black text-gray-800 dark:text-slate-100 my-0">Inventory: Histories Module Manual</h3>
                </div>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-0">Global Activity Logging and Stock Movement Tracking</p>
                <div class="w-full h-px bg-gray-100 dark:bg-slate-800 my-4"></div>

                <!-- Section 1 -->
                <div class="space-y-3">
                  <h4 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">1. Global Inventory Activity Log</h4>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-50 dark:bg-[#252525] rounded-2xl border border-gray-100 dark:border-[#2E2E2E]/60">
                      <h5 class="text-xs font-bold text-gray-700 dark:text-slate-200 mb-1 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> All-in-One Timeline
                      </h5>
                      <p class="text-[11px] leading-relaxed text-gray-550 dark:text-slate-400">
                        Displays the transaction history of every single item in the system in a centralized, line-by-line master timeline.
                      </p>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-[#252525] rounded-2xl border border-gray-100 dark:border-[#2E2E2E]/60">
                      <h5 class="text-xs font-bold text-gray-700 dark:text-slate-200 mb-1 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Stock In and Out
                      </h5>
                      <p class="text-[11px] leading-relaxed text-gray-550 dark:text-slate-400">
                        Whenever any product's stock levels fluctuate (whether via an Invoice, Bill, Transfer Order, or Manual Adjustment), the entry is automatically logged here.
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Section 2 -->
                <div class="space-y-3 mt-6">
                  <h4 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">2. Detailed Tracking Fields</h4>
                  <p class="text-[11px] text-gray-550 dark:text-slate-400 mb-2">The history logs are organized in a detailed table containing the following key attributes:</p>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-50 dark:bg-[#252525] rounded-2xl border border-gray-100 dark:border-[#2E2E2E]/60">
                      <ul class="text-[11px] space-y-2 text-gray-550 dark:text-slate-400 pl-0 list-none">
                        <li class="flex items-start gap-2">
                          <strong class="text-gray-700 dark:text-slate-200 shrink-0 min-w-[100px]">Date & Time:</strong>
                          <span>The exact timestamp when the inventory modification took place.</span>
                        </li>
                        <li class="flex items-start gap-2">
                          <strong class="text-gray-700 dark:text-slate-200 shrink-0 min-w-[100px]">Item Name:</strong>
                          <span>The specific product that experienced a stock level change.</span>
                        </li>
                        <li class="flex items-start gap-2">
                          <strong class="text-gray-700 dark:text-slate-200 shrink-0 min-w-[100px]">Warehouse:</strong>
                          <span>The specific storage location or warehouse affected by the update.</span>
                        </li>
                      </ul>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-[#252525] rounded-2xl border border-gray-100 dark:border-[#2E2E2E]/60">
                      <ul class="text-[11px] space-y-2 text-gray-550 dark:text-slate-400 pl-0 list-none">
                        <li class="flex items-start gap-2">
                          <strong class="text-gray-700 dark:text-slate-200 shrink-0 min-w-[100px]">Qty Changed:</strong>
                          <span>The precise amount of quantity added (+) or removed (-).</span>
                        </li>
                        <li class="flex items-start gap-2">
                          <strong class="text-gray-700 dark:text-slate-200 shrink-0 min-w-[100px]">Type / Reason:</strong>
                          <span>The source transaction or event causing the change (e.g. Invoice, Bill, Transfer Order, Manual Adjustment).</span>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>

                <!-- Section 3 -->
                <div class="space-y-3 mt-6">
                  <h4 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">3. Purpose & Benefits</h4>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 bg-indigo-50/30 dark:bg-indigo-950/10 rounded-2xl border border-indigo-100/50 dark:border-indigo-950/40">
                      <h5 class="text-xs font-bold text-gray-700 dark:text-slate-200 mb-1 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Discrepancy Checking
                      </h5>
                      <p class="text-[11px] leading-relaxed text-gray-550 dark:text-slate-400">
                        Allows administrators to filter through the master logs to pinpoint and resolve errors if stock discrepancies occur.
                      </p>
                    </div>
                    <div class="p-4 bg-indigo-50/30 dark:bg-indigo-950/10 rounded-2xl border border-indigo-100/50 dark:border-indigo-950/40">
                      <h5 class="text-xs font-bold text-gray-700 dark:text-slate-200 mb-1 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Complete Accountability
                      </h5>
                      <p class="text-[11px] leading-relaxed text-gray-550 dark:text-slate-400">
                        Ensures total transparency by tracking exactly how, when, and through which transaction the company's inventory changed.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Standard Support View -->
            <div v-else class="space-y-6">
              <!-- Documentation Quick Links -->
              <div>
                <h3 class="text-xs font-bold text-gray-400 dark:text-slate-550 uppercase tracking-widest mb-3">Knowledge Base</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <a href="https://akaunting.com/hc/docs" target="_blank" class="flex items-center gap-3 p-3.5 bg-gray-50 dark:bg-[#252525] rounded-xl hover:bg-indigo-50/50 dark:hover:bg-indigo-950/20 transition-all border border-transparent hover:border-indigo-100 dark:hover:border-indigo-950">
                    <div class="w-9 h-9 bg-indigo-100 dark:bg-indigo-950/50 rounded-lg flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold shrink-0">📚</div>
                    <div class="min-w-0">
                      <h4 class="text-xs font-bold text-gray-700 dark:text-slate-200 truncate">Akaunting Docs</h4>
                      <p class="text-[10px] text-gray-400 truncate">Help center manuals</p>
                    </div>
                  </a>
                  <button @click="showGroupsDoc = true" class="flex items-center text-left gap-3 p-3.5 bg-gray-50 dark:bg-[#252525] rounded-xl hover:bg-indigo-50/50 dark:hover:bg-indigo-950/20 transition-all border border-transparent hover:border-indigo-100 dark:hover:border-indigo-950 cursor-pointer">
                    <div class="w-9 h-9 bg-amber-100 dark:bg-amber-950/50 rounded-lg flex items-center justify-center text-amber-600 dark:text-amber-400 font-bold shrink-0">📦</div>
                    <div class="min-w-0">
                      <h4 class="text-xs font-bold text-gray-700 dark:text-slate-200 truncate">Groups Manual</h4>
                      <p class="text-[10px] text-gray-400 truncate">Category management guide</p>
                    </div>
                  </button>
                  <button @click="showHistoriesDoc = true" class="flex items-center text-left gap-3 p-3.5 bg-gray-50 dark:bg-[#252525] rounded-xl hover:bg-indigo-50/50 dark:hover:bg-indigo-950/20 transition-all border border-transparent hover:border-indigo-100 dark:hover:border-indigo-950 cursor-pointer">
                    <div class="w-9 h-9 bg-emerald-100 dark:bg-emerald-950/50 rounded-lg flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-bold shrink-0">📜</div>
                    <div class="min-w-0">
                      <h4 class="text-xs font-bold text-gray-700 dark:text-slate-200 truncate">Histories Manual</h4>
                      <p class="text-[10px] text-gray-400 truncate">Stock tracking guide</p>
                    </div>
                  </button>
                </div>
              </div>

              <!-- Submit Ticket Form -->
              <div>
                <h3 class="text-xs font-bold text-gray-400 dark:text-slate-550 uppercase tracking-widest mb-3">Create New Ticket</h3>
                <form @submit.prevent="submitSupportTicket" class="space-y-4">
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-[10px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Department</label>
                      <select v-model="ticketForm.department" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#252525] border border-gray-250 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-200">
                        <option value="General">General Inquiries</option>
                        <option value="Sales">Sales & Invoices</option>
                        <option value="Inventory">Inventory & Warehouses</option>
                        <option value="Accounting">Accounting Ledger</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-[10px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Priority</label>
                      <select v-model="ticketForm.priority" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#252525] border border-gray-250 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-200">
                        <option value="low">Low Priority</option>
                        <option value="medium">Medium Priority</option>
                        <option value="high">High Priority</option>
                      </select>
                    </div>
                  </div>
                  <div>
                    <label class="block text-[10px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Subject</label>
                    <input v-model="ticketForm.subject" required type="text" placeholder="Explain the issue briefly..." class="w-full px-3 py-2 text-xs bg-slate-55 dark:bg-[#252525] border border-gray-250 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-200" />
                  </div>
                  <div>
                    <label class="block text-[10px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Details & Description</label>
                    <textarea v-model="ticketForm.description" required rows="3" placeholder="Provide background information, replication steps, etc..." class="w-full px-3 py-2 text-xs bg-slate-55 dark:bg-[#252525] border border-gray-250 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-200 resize-none"></textarea>
                  </div>
                  <div class="flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-all duration-150 shadow-md cursor-pointer">
                      Submit Ticket
                    </button>
                  </div>
                </form>
              </div>

              <!-- List of My Tickets -->
              <div v-if="submittedTickets.length > 0">
                <h3 class="text-xs font-bold text-gray-400 dark:text-slate-550 uppercase tracking-widest mb-3">Submitted Tickets</h3>
                <div class="space-y-3">
                  <div v-for="ticket in submittedTickets" :key="ticket.id" class="p-3.5 bg-slate-50 dark:bg-[#252525] rounded-xl border border-gray-200/50 dark:border-[#2E2E2E] flex items-start justify-between">
                    <div>
                      <h4 class="text-xs font-bold text-gray-800 dark:text-slate-200">{{ ticket.subject }}</h4>
                      <p class="text-[10px] text-gray-500 dark:text-slate-400 mt-0.5">{{ ticket.description }}</p>
                      <div class="flex items-center gap-2 mt-2">
                        <span class="px-1.5 py-0.5 bg-indigo-50 dark:bg-indigo-950/45 text-indigo-600 dark:text-indigo-400 text-[8px] font-black rounded uppercase tracking-wider">Dept: {{ ticket.department }}</span>
                        <span class="px-1.5 py-0.5 bg-amber-50 dark:bg-amber-950/45 text-amber-600 dark:text-amber-400 text-[8px] font-black rounded uppercase tracking-wider">Priority: {{ ticket.priority }}</span>
                        <span class="text-[9px] text-gray-400">Created: {{ formatDate(ticket.created_at) }}</span>
                      </div>
                    </div>
                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 dark:bg-blue-950/45 dark:text-blue-400 text-[8px] font-black rounded uppercase tracking-widest animate-pulse">Open</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, onErrorCaptured, watch } from 'vue';
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
const currentThemeSetting = ref(
  localStorage.getItem('theme') === 'auto' ? 'system' : (localStorage.getItem('theme') || 'system')
);
const isDarkMode = ref(false);

const applyTheme = (setting) => {
  const html = document.documentElement;
  const normalizedSetting = (setting === 'auto' || setting === 'match system') ? 'system' : setting;

  if (normalizedSetting === 'dark') {
    isDarkMode.value = true;
    html.classList.add('dark');
  } else if (normalizedSetting === 'light') {
    isDarkMode.value = false;
    html.classList.remove('dark');
  } else {
    // system
    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
      isDarkMode.value = true;
      html.classList.add('dark');
    } else {
      isDarkMode.value = false;
      html.classList.remove('dark');
    }
  }
};

const setTheme = async (setting) => {
  currentThemeSetting.value = setting;
  localStorage.setItem('theme', setting);
  document.cookie = `theme=${setting}; path=/; max-age=31536000`; // 1 year
  
  applyTheme(setting);
  showThemeMenu.value = false;

  try {
    const backendTheme = setting === 'system' ? 'auto' : setting;
    await axios.put('/api/user/settings', {
      theme: backendTheme
    });
  } catch (error) {
    console.error('Failed to update theme in settings database:', error);
  }
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
const isSidebarHovered = ref(false);
const isSidebarCollapsedEffective = computed(() => sidebarCollapsed.value && !isSidebarHovered.value);
const showMobileSidebar = ref(false);
const showSidebarSalesMenu = ref(router.currentRoute.value.path.startsWith('/sales'));
const showSidebarPurchaseMenu = ref(router.currentRoute.value.path.startsWith('/purchase'));
const showSidebarInventoryMenu = ref(
  router.currentRoute.value.path.startsWith('/inventory') || router.currentRoute.value.path.startsWith('/products') || router.currentRoute.value.path === '/settings/tax-tags'
);

watch(() => router.currentRoute.value.path, (newPath) => {
  if (newPath.startsWith('/inventory') || newPath.startsWith('/products') || newPath === '/settings/tax-tags') {
    showSidebarInventoryMenu.value = true;
  }
  if (newPath.startsWith('/sales')) {
    showSidebarSalesMenu.value = true;
  }
  if (newPath.startsWith('/purchase')) {
    showSidebarPurchaseMenu.value = true;
  }
});

// Favorites System
const favorites = ref(JSON.parse(localStorage.getItem('pos_favorites') || '[]'));

const menuItems = [
  { path: '/', name: 'Dashboard', category: 'General', icon: 'home' },
  { path: '/sales/invoices', name: 'Sales Invoices', category: 'Sales', icon: 'invoice' },
  { path: '/sales/returns', name: 'Sales Returns', category: 'Sales', icon: 'return' },
  { path: '/purchase/orders', name: 'Purchase Orders', category: 'Purchases', icon: 'purchase' },
  { path: '/purchase/returns', name: 'Purchase Returns', category: 'Purchases', icon: 'return' },
  { path: '/inventory', name: 'Adjustment', category: 'Inventory', icon: 'inventory' },
  { path: '/products', name: 'Items', category: 'Inventory', icon: 'product' },
  { path: '/inventory/groups', name: 'Groups', category: 'Inventory', icon: 'groups' },
  { path: '/inventory/product-variations', name: 'Variations', category: 'Inventory', icon: 'variations' },
  { path: '/inventory/warehouses', name: 'Warehouses', category: 'Inventory', icon: 'warehouses' },
  { path: '/inventory/histories', name: 'Histories', category: 'Inventory', icon: 'histories' },
  { path: '/inventory/transfer-orders', name: 'Stock Transfers', category: 'Inventory', icon: 'transfers' },
  { path: '/accounting', name: 'Accounting', category: 'Accounting', icon: 'accounting' },
  { path: '/transactions', name: 'Transactions', category: 'Accounting', icon: 'transactions' },
  { path: '/expenses', name: 'Expenses', category: 'Accounting', icon: 'expenses' },
  { path: '/payments', name: 'Payments Out', category: 'Payments', icon: 'payments' },
  { path: '/payment-receipts', name: 'Payment Receipts', category: 'Payments', icon: 'receipts' },
  { path: '/employees', name: 'Employees', category: 'People', icon: 'employees' },
  { path: '/customers', name: 'Customers', category: 'People', icon: 'customers' },
  { path: '/suppliers', name: 'Suppliers', category: 'People', icon: 'suppliers' },
  { path: '/reports', name: 'Reports', category: 'Analytics', icon: 'reports' },
  { path: '/profile', name: 'Account Profile', category: 'System', icon: 'profile' },
  { path: '/settings/tax-tags', name: 'Tax and Tags', category: 'Inventory', icon: 'taxes' },
  { path: '/settings', name: 'System Settings', category: 'System', icon: 'settings' }
];

const toggleFavorite = (item) => {
  const index = favorites.value.findIndex(f => f.path === item.path);
  if (index !== -1) {
    favorites.value.splice(index, 1);
  } else {
    favorites.value.push(item);
  }
  localStorage.setItem('pos_favorites', JSON.stringify(favorites.value));
};

const isCurrentRouteFavorited = computed(() => {
  return favorites.value.some(f => f.path === router.currentRoute.value.path);
});

const currentMenuItem = computed(() => {
  const currentPath = router.currentRoute.value.path;
  let item = menuItems.find(m => m.path === currentPath);
  if (!item) {
    if (currentPath.startsWith('/products')) {
      item = menuItems.find(m => m.path === '/products');
    } else if (currentPath.startsWith('/sales/invoices')) {
      item = menuItems.find(m => m.path === '/sales/invoices');
    } else if (currentPath.startsWith('/purchase/orders')) {
      item = menuItems.find(m => m.path === '/purchase/orders');
    } else if (currentPath.startsWith('/inventory/transfer-orders')) {
      item = menuItems.find(m => m.path === '/inventory/transfer-orders');
    }
  }
  return item;
});

const toggleCurrentRouteFavorite = () => {
  const item = currentMenuItem.value;
  if (item) {
    toggleFavorite(item);
  }
};

// Search / Command Palette
const searchQuery = ref('');
const showSearchResults = ref(false);
const searchResultsRef = ref(null);

const filteredMenuItems = computed(() => {
  if (!searchQuery.value) return [];
  const query = searchQuery.value.toLowerCase().trim();
  return menuItems.filter(item => 
    item.name.toLowerCase().includes(query) || 
    item.category.toLowerCase().includes(query)
  );
});

const navigateToSearchItem = (item) => {
  router.push(item.path);
  searchQuery.value = '';
  showSearchResults.value = false;
};

// Support Modal & Ticket System
const showSupportModal = ref(false);
const showGroupsDoc = ref(false);
const showHistoriesDoc = ref(false);
const ticketForm = ref({
  department: 'General',
  priority: 'medium',
  subject: '',
  description: ''
});
const submittedTickets = ref(JSON.parse(localStorage.getItem('pos_support_tickets') || '[]'));

const submitSupportTicket = () => {
  const newTicket = {
    id: Date.now(),
    department: ticketForm.value.department,
    priority: ticketForm.value.priority,
    subject: ticketForm.value.subject,
    description: ticketForm.value.description,
    created_at: new Date().toISOString()
  };
  submittedTickets.value.unshift(newTicket);
  localStorage.setItem('pos_support_tickets', JSON.stringify(submittedTickets.value));
  
  ticketForm.value = {
    department: 'General',
    priority: 'medium',
    subject: '',
    description: ''
  };

  alert('Ticket submitted successfully! An administrator will review your ticket shortly.');
};

watch(showSupportModal, (val) => {
  if (!val) {
    showGroupsDoc.value = false;
    showHistoriesDoc.value = false;
  }
});

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
    showSidebarInventoryMenu.value = false;
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

  // Check if click is outside search results
  if (showSearchResults.value && searchResultsRef.value && !searchResultsRef.value.contains(event.target)) {
    const searchInput = document.querySelector('input[type="text"][placeholder*="Search features"]');
    if (!searchInput || !searchInput.contains(event.target)) {
      showSearchResults.value = false;
    }
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
