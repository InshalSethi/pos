<template>
  <div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-zinc-100 tracking-tight">Sales Returns</h1>
        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
          Manage sales return requests, supplier debit notes & inventory refund tracking.
        </p>
      </div>

      <div class="flex items-center space-x-3">
        <!-- Process Return Button -->
        <button
          @click="createReturn"
          class="bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-bold px-4 py-2 rounded-lg shadow-sm transition-all flex items-center space-x-1.5 cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
          </svg>
          <span>Process Return</span>
        </button>
      </div>
    </div>

    <!-- Tabs Bar -->
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 dark:border-zinc-800 pb-3">
      <div class="flex items-center space-x-1 overflow-x-auto custom-scrollbar pb-1 sm:pb-0">
        <button
          v-for="tab in visibleTabs"
          :key="tab.id"
          @click="setActiveTab(tab.id)"
          class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all whitespace-nowrap cursor-pointer flex items-center space-x-1.5"
          :class="isTabActive(tab.id) 
            ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 shadow-xs' 
            : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 hover:text-slate-900 dark:hover:text-zinc-200'"
        >
          <span>{{ tab.label }}</span>
          <span
            class="px-1.5 py-0.2 text-[10px] rounded-full font-bold"
            :class="isTabActive(tab.id) 
              ? 'bg-slate-700 text-slate-100 dark:bg-zinc-300 dark:text-zinc-900' 
              : 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400'"
          >
            {{ counts[tab.id] || 0 }}
          </span>
          <span
            v-if="tab.id !== 'all'"
            @click.stop="removeFilterOption(tab.id)"
            class="ml-0.5 text-slate-400 hover:text-rose-500 rounded-full p-0.5 transition-colors"
            title="Remove filter"
          >
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </span>
        </button>
      </div>

      <div class="flex items-center space-x-2">
        <!-- Sort Button -->
        <button 
          @click="handleSort('sale_date')"
          class="inline-flex items-center px-3 py-1.5 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs font-semibold text-slate-600 dark:text-zinc-300 bg-white dark:bg-zinc-900 hover:bg-slate-50 dark:hover:bg-zinc-800 shadow-sm transition-all cursor-pointer"
        >
          <svg class="w-3.5 h-3.5 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
          </svg>
          Sort Date
        </button>

        <!-- Advanced Filter Button -->
        <button
          @click="openFilterDrawer"
          class="inline-flex items-center px-3.5 py-1.5 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs font-semibold text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-900 hover:bg-slate-50 dark:hover:bg-zinc-800 shadow-sm transition-all focus:outline-none cursor-pointer"
          :class="{ 'border-slate-900 text-slate-900 bg-slate-100/50 dark:bg-zinc-800 dark:border-zinc-100 dark:text-zinc-100 font-bold': totalActiveFilterCount > 0 }"
        >
          <svg class="w-3.5 h-3.5 mr-1.5 text-slate-400" :class="{ 'text-slate-900 dark:text-zinc-100': totalActiveFilterCount > 0 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 8.293A1 1 0 013 7.586V4z"/>
          </svg>
          <span>Filter</span>
          <!-- Selected Filter Indicator Badge -->
          <span v-if="totalActiveFilterCount > 0" class="ml-1.5 text-[10px] font-extrabold bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 px-1.5 py-0.2 rounded-full">
            {{ totalActiveFilterCount }}
          </span>
        </button>
      </div>
    </div>

    <!-- Active Filters Pill Bar -->
    <div v-if="totalActiveFilterCount > 0" class="flex flex-wrap items-center gap-2 mb-3 p-3 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl shadow-soft animate-fade-in">
      <span class="text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mr-1">Active Filters:</span>

      <!-- Product Pill -->
      <span v-if="advancedFilters.product_name || advancedFilters.product_search" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
        Product: {{ advancedFilters.product_name || advancedFilters.product_search }}
        <button @click="removeSingleFilter('product')" class="ml-1.5 hover:text-blue-900 dark:hover:text-white cursor-pointer"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
      </span>

      <!-- Original Invoice Pill -->
      <span v-if="advancedFilters.original_invoice" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
        Invoice: {{ advancedFilters.original_invoice }}
        <button @click="removeSingleFilter('invoice')" class="ml-1.5 hover:text-amber-900 dark:hover:text-white cursor-pointer"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
      </span>

      <!-- Customers Pill -->
      <span v-if="advancedFilters.customer_ids?.length > 0" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
        Clients: {{ advancedFilters.customer_ids.length }} selected
        <button @click="removeSingleFilter('customer')" class="ml-1.5 hover:text-blue-900 dark:hover:text-white cursor-pointer"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
      </span>

      <!-- Salesmen Pill -->
      <span v-if="advancedFilters.salesman_ids?.length > 0" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
        Reps: {{ advancedFilters.salesman_ids.length }} selected
        <button @click="removeSingleFilter('salesman')" class="ml-1.5 hover:text-purple-900 dark:hover:text-white cursor-pointer"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
      </span>

      <!-- Warehouses Pill -->
      <span v-if="advancedFilters.warehouse_ids?.length > 0" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
        Warehouses: {{ advancedFilters.warehouse_ids.length }} selected
        <button @click="removeSingleFilter('warehouse')" class="ml-1.5 hover:text-emerald-900 dark:hover:text-white cursor-pointer"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
      </span>

      <!-- Counters Pill -->
      <span v-if="advancedFilters.counter_ids?.length > 0" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
        Counters: {{ advancedFilters.counter_ids.length }} selected
        <button @click="removeSingleFilter('counter')" class="ml-1.5 hover:text-amber-900 dark:hover:text-white cursor-pointer"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
      </span>

      <!-- Return Reasons Pill -->
      <span v-if="advancedFilters.return_reasons?.length > 0" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
        Reasons: {{ advancedFilters.return_reasons.join(', ') }}
        <button @click="removeSingleFilter('reason')" class="ml-1.5 hover:text-indigo-900 dark:hover:text-white cursor-pointer"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
      </span>

      <!-- Date Range Pill -->
      <span v-if="advancedFilters.date_from || advancedFilters.date_to" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border border-slate-200 dark:border-zinc-700">
        Date: {{ advancedFilters.date_from }} to {{ advancedFilters.date_to }}
        <button @click="removeSingleFilter('date')" class="ml-1.5 hover:text-slate-900 dark:hover:text-white cursor-pointer"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
      </span>

      <button @click="handleResetAdvancedFilters" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline ml-auto cursor-pointer">
        Clear All
      </button>
    </div>

    <!-- Collapsible Date & Invoice Filter Bar -->
    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="transform -translate-y-2 opacity-0"
      enter-to-class="transform translate-y-0 opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="transform translate-y-0 opacity-100"
      leave-to-class="transform -translate-y-2 opacity-0"
    >
      <div v-if="showFilterDropdown || dateFrom !== '' || dateTo !== '' || originalInvoice !== '' || selectedFilters.length > 0" class="bg-slate-50 dark:bg-zinc-900/50 border border-slate-200 dark:border-zinc-800 rounded-xl p-3.5 mb-3 flex flex-wrap gap-4 items-center justify-between shadow-sm">
        <div class="flex flex-wrap gap-4 items-center">
          <div class="flex items-center space-x-2">
            <label class="text-xs font-semibold text-slate-500 dark:text-zinc-400">Original Invoice:</label>
            <input
              v-model="originalInvoice"
              type="text"
              placeholder="Original Invoice #..."
              class="px-3 py-1.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 text-slate-700 dark:text-zinc-200"
              @input="debouncedSearch"
            />
          </div>
          <div class="flex items-center space-x-2">
            <label class="text-xs font-semibold text-slate-500 dark:text-zinc-400">Date From:</label>
            <input
              v-model="dateFrom"
              type="date"
              class="px-3 py-1.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 text-slate-700 dark:text-zinc-200"
              @change="fetchReturns(1)"
            />
          </div>
          <div class="flex items-center space-x-2">
            <label class="text-xs font-semibold text-slate-500 dark:text-zinc-400">Date To:</label>
            <input
              v-model="dateTo"
              type="date"
              class="px-3 py-1.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 text-slate-700 dark:text-zinc-200"
              @change="fetchReturns(1)"
            />
          </div>
        </div>
      </div>
    </transition>

    <!-- Table Container -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-soft">
      <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-zinc-800">
        <!-- Search -->
        <div class="relative w-96">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg class="w-4 h-4 text-slate-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </span>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search by return number or customer name"
            class="w-full pl-9 pr-4 py-1.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs focus:outline-none focus:ring-0 focus:bg-white dark:focus:bg-zinc-800 transition-all text-slate-700 dark:text-zinc-200 dark:placeholder-zinc-500"
            @input="debouncedSearch"
          />
        </div>

        <!-- Showing selection counts -->
        <div class="flex items-center space-x-2 text-xs text-slate-500 dark:text-zinc-400">
          <span>Showing</span>
          <select
            v-model="perPage"
            @change="handlePerPageChange"
            class="border border-slate-200 dark:border-zinc-700 rounded px-1.5 py-0.5 focus:outline-none focus:ring-1 focus:ring-blue-500 cursor-pointer bg-white dark:bg-zinc-800 dark:text-zinc-200"
          >
            <option :value="10">10</option>
            <option :value="15">15</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
          <span>of {{ totalItems }} results</span>
        </div>
      </div>

      <!-- Returns Table -->
      <div class="overflow-x-auto custom-scrollbar min-h-[400px]">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-200 dark:border-zinc-700 text-slate-500 dark:text-zinc-400 uppercase font-bold tracking-wider">
              <th class="py-2.5 px-4 w-[40px] text-center bg-slate-50 dark:bg-zinc-800/50">
                <input type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-3.5 h-3.5" />
              </th>
              <th class="py-2.5 px-4 cursor-pointer hover:bg-slate-100/50 bg-slate-50 dark:bg-zinc-800/50" @click="handleSort('sale_number')">
                <div class="flex items-center space-x-1">
                  <span>Return #</span>
                  <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                  </svg>
                </div>
              </th>
              <th class="py-2.5 px-4 bg-slate-50 dark:bg-zinc-800/50">Original Invoice</th>
              <th class="py-2.5 px-4 bg-slate-50 dark:bg-zinc-800/50">Client/Customer</th>
              <th class="py-2.5 px-4 bg-slate-50 dark:bg-zinc-800/50">Salesman</th>
              <th class="py-2.5 px-4 text-right cursor-pointer hover:bg-slate-100/50 bg-slate-50 dark:bg-zinc-800/50" @click="handleSort('total_amount')">
                <div class="flex items-center justify-end space-x-1">
                  <span>Refund Amount</span>
                  <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                  </svg>
                </div>
              </th>
              <th class="py-2.5 px-4 bg-slate-50 dark:bg-zinc-800/50">Refund Method</th>
              <th class="py-2.5 px-4 cursor-pointer hover:bg-slate-100/50 bg-slate-50 dark:bg-zinc-800/50" @click="handleSort('sale_date')">
                <div class="flex items-center space-x-1">
                  <span>Return Date</span>
                  <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                  </svg>
                </div>
              </th>
              <th class="py-2.5 px-4 text-center bg-slate-50 dark:bg-zinc-800/50">Status</th>
              <th class="py-2.5 px-4 text-center bg-slate-50 dark:bg-zinc-800/50 w-[80px]">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100/70 dark:divide-zinc-800">
            <tr v-if="loading" class="bg-white dark:bg-zinc-900">
              <td colspan="10" class="h-[340px] text-center text-slate-400 dark:text-zinc-500 align-middle">
                <div class="flex flex-col items-center justify-center space-y-2">
                  <div class="animate-spin rounded-full h-7 w-7 border-2 border-slate-300 dark:border-zinc-600 border-t-blue-600"></div>
                  <span class="text-xs font-semibold">Loading returns...</span>
                </div>
              </td>
            </tr>
            <tr v-else-if="returns.length === 0" class="bg-white dark:bg-zinc-900">
              <td colspan="10" class="h-[340px] text-center text-slate-400 dark:text-zinc-500 italic align-middle">
                <svg class="mx-auto h-10 w-10 text-slate-300 dark:text-zinc-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
                <span>No returns found. Create your first sales return.</span>
              </td>
            </tr>
            <tr v-else v-for="item in returns" :key="item.id" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/50 transition-colors">
              <!-- Checkbox -->
              <td class="py-2.5 px-4 text-center align-middle bg-white dark:bg-zinc-900">
                <input type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-3.5 h-3.5" />
              </td>

              <!-- Return Number -->
              <td class="py-2.5 px-4 align-middle bg-white dark:bg-zinc-900 font-mono text-xs">
                <div class="font-bold text-slate-800 dark:text-zinc-100 text-sm hover:text-blue-600 cursor-pointer" @click="viewReturn(item)">
                  {{ item.sale_number }}
                </div>
                <div class="text-[10px] text-slate-400 dark:text-zinc-500 mt-0.5">
                  Created: {{ formatDate(item.created_at) }}
                </div>
              </td>

              <!-- Original Invoice -->
              <td class="py-2.5 px-4 align-middle bg-white dark:bg-zinc-900 font-semibold text-slate-700 dark:text-zinc-300">
                {{ item.original_sale?.sale_number || 'N/A' }}
              </td>

              <!-- Client/Customer -->
              <td class="py-2.5 px-4 align-middle bg-white dark:bg-zinc-900">
                <div class="font-semibold text-slate-900 dark:text-zinc-100 text-sm">
                  {{ item.customer?.name || 'Walk-in Customer' }}
                </div>
                <div class="flex flex-col gap-0.5 items-start mt-1">
                  <span v-if="item.counter?.name" class="inline-flex items-center text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30 px-2 py-0.5 rounded text-[11px] font-medium">
                    🖥️ Counter: {{ item.counter.name }}
                  </span>
                  <span v-if="item.warehouse?.name" class="inline-flex items-center text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded text-[11px] font-medium">
                    🏢 {{ item.warehouse.name }}
                  </span>
                </div>
              </td>

              <!-- Salesman Column -->
              <td class="py-2.5 px-4 align-middle bg-white dark:bg-zinc-900">
                <div v-if="getSalesmanName(item) !== 'N/A'" class="flex items-center space-x-1.5 text-slate-800 dark:text-zinc-200 font-semibold text-xs">
                  <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 text-xs">
                    👤
                  </span>
                  <span>{{ getSalesmanName(item) }}</span>
                </div>
                <div v-else class="text-slate-400 dark:text-zinc-500 text-xs italic">
                  Unassigned
                </div>
              </td>

              <!-- Refund Amount -->
              <td class="py-2.5 px-4 text-right font-bold text-slate-900 dark:text-zinc-100 text-sm align-middle bg-white dark:bg-zinc-900">
                {{ formatCurrency(Math.abs(item.total_amount)) }}
              </td>

              <!-- Refund Method -->
              <td class="py-2.5 px-4 align-middle bg-white dark:bg-zinc-900 text-slate-600 dark:text-zinc-300 font-medium">
                <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 text-[11px]">
                  {{ formatRefundLabel(item.payment_method) }}
                </span>
              </td>

              <!-- Return Date -->
              <td class="py-2.5 px-4 text-slate-600 dark:text-zinc-300 text-sm align-middle bg-white dark:bg-zinc-900">
                {{ formatDate(item.sale_date) }}
              </td>

              <!-- Status Badge -->
              <td class="py-2.5 px-4 text-center align-middle bg-white dark:bg-zinc-900">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/40">
                  Returned
                </span>
              </td>

              <!-- Action Menu Dropdown -->
              <td class="py-2.5 px-4 text-center relative align-middle bg-white dark:bg-zinc-900">
                <button
                  @click.stop="toggleActionDropdown(item.id)"
                  class="text-slate-400 dark:text-zinc-500 hover:text-slate-600 dark:hover:text-zinc-300 p-1 rounded-full hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all focus:outline-none cursor-pointer"
                >
                  <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" d="M12 5a2 2 0 100-4 2 2 0 000 4zm0 9a2 2 0 100-4 2 2 0 000 4zm0 9a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                  </svg>
                </button>
                
                <!-- Action Dropdown Overlay -->
                <div
                  v-if="openActionDropdown === item.id"
                  class="absolute right-4 mt-1 w-32 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-lg shadow-lg py-1 z-50 animate-fade-in"
                >
                  <button @click="viewReturn(item)" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span>View</span>
                  </button>
                  <button @click="editReturn(item)" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span>Edit</span>
                  </button>
                  <button @click="printReturn(item)" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Print</span>
                  </button>
                  <div class="border-t border-slate-100 dark:border-zinc-800 my-1"></div>
                  <button @click="deleteReturn(item.id)" class="w-full text-left px-3 py-1.5 text-xs text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span>Delete</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex items-center justify-between p-4 border-t border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900">
        <div class="flex-1 flex justify-between sm:hidden">
          <button
            @click="previousPage"
            :disabled="currentPage === 1"
            class="relative inline-flex items-center px-4 py-2 border border-slate-200 dark:border-zinc-700 text-xs font-semibold rounded-lg text-slate-700 dark:text-zinc-300 bg-white dark:bg-zinc-900 hover:bg-slate-50 dark:hover:bg-zinc-800 disabled:opacity-50"
          >
            Previous
          </button>
          <button
            @click="nextPage"
            :disabled="currentPage === pagination.last_page"
            class="ml-3 relative inline-flex items-center px-4 py-2 border border-slate-200 dark:border-zinc-700 text-xs font-semibold rounded-lg text-slate-700 dark:text-zinc-300 bg-white dark:bg-zinc-900 hover:bg-slate-50 dark:hover:bg-zinc-800 disabled:opacity-50"
          >
            Next
          </button>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-center">
          <nav class="relative z-0 inline-flex rounded-lg shadow-sm -space-x-px" aria-label="Pagination">
            <!-- Prev -->
            <button
              @click="previousPage"
              :disabled="currentPage === 1"
              class="relative inline-flex items-center px-2.5 py-2 rounded-l-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-xs font-semibold text-slate-500 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800 disabled:opacity-50 cursor-pointer"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            
            <!-- Page Numbers -->
            <template v-for="(page, idx) in paginationRange" :key="idx">
              <span
                v-if="page === '...'"
                class="relative inline-flex items-center px-3.5 py-2 border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-xs font-bold text-slate-400 dark:text-zinc-500 select-none"
              >
                ...
              </span>
              <button
                v-else
                @click="goToPage(page)"
                class="relative inline-flex items-center px-3.5 py-2 border text-xs font-bold transition-all cursor-pointer"
                :class="currentPage === page ? 'z-10 bg-slate-100 dark:bg-zinc-800 border-slate-300 dark:border-zinc-600 text-slate-800 dark:text-zinc-100' : 'bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-700 text-slate-500 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800'"
              >
                {{ page }}
              </button>
            </template>
            
            <!-- Next -->
            <button
              @click="nextPage"
              :disabled="currentPage === pagination.last_page"
              class="relative inline-flex items-center px-2.5 py-2 rounded-r-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-xs font-semibold text-slate-500 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800 disabled:opacity-50 cursor-pointer"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
          </nav>
        </div>
      </div>
    </div>

    <!-- Sales Return Modal -->
    <SalesReturnModal
      :show="showCreateModal"
      :original-sale-id="selectedOriginalSaleId"
      @close="closeModal"
      @saved="handleReturnSaved"
    />

    <!-- Advanced Filter Drawer Component -->
    <SalesReturnFilter
      v-model:isOpen="isFilterDrawerOpen"
      :filters="advancedFilters"
      @apply="handleApplyAdvancedFilters"
      @reset="handleResetAdvancedFilters"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useCurrencyStore } from '@/stores/currency';
import { debounce } from '@/utils/debounce';
import SalesReturnModal from './SalesReturnModal.vue';
import SalesReturnFilter from './SalesReturnFilter.vue';
import axios from 'axios';

const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();

// Reactive data
const returns = ref([]);
const searchQuery = ref('');
const originalInvoice = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const showCreateModal = ref(false);
const selectedOriginalSaleId = ref(null);
const loading = ref(false);
const openActionDropdown = ref(null);
const showFilterDropdown = ref(false);
const currentTab = ref('all');

// Advanced Filter Drawer State
const isFilterDrawerOpen = ref(false);
const advancedFilters = ref({
  product_id: '',
  product_name: '',
  product_search: '',
  original_invoice: '',
  customer_ids: [],
  salesman_ids: [],
  warehouse_ids: [],
  counter_ids: [],
  return_reasons: [],
  date_from: '',
  date_to: ''
});

// Advanced Filter Count Computed
const totalActiveFilterCount = computed(() => {
  let count = 0;
  if (advancedFilters.value.product_id || advancedFilters.value.product_search) count++;
  if (advancedFilters.value.original_invoice) count++;
  if (advancedFilters.value.customer_ids?.length > 0) count++;
  if (advancedFilters.value.salesman_ids?.length > 0) count++;
  if (advancedFilters.value.warehouse_ids?.length > 0) count++;
  if (advancedFilters.value.counter_ids?.length > 0) count++;
  if (advancedFilters.value.return_reasons?.length > 0) count++;
  if (advancedFilters.value.date_from || advancedFilters.value.date_to) count++;
  return count;
});

const openFilterDrawer = () => {
  isFilterDrawerOpen.value = true;
};

const handleApplyAdvancedFilters = (newFilters) => {
  advancedFilters.value = { ...newFilters };
  if (newFilters.original_invoice !== undefined) {
    originalInvoice.value = newFilters.original_invoice;
  }
  if (newFilters.date_from !== undefined) {
    dateFrom.value = newFilters.date_from;
  }
  if (newFilters.date_to !== undefined) {
    dateTo.value = newFilters.date_to;
  }
  fetchReturns(1);
};

const handleResetAdvancedFilters = () => {
  advancedFilters.value = {
    product_id: '',
    product_name: '',
    product_search: '',
    original_invoice: '',
    customer_ids: [],
    salesman_ids: [],
    warehouse_ids: [],
    counter_ids: [],
    return_reasons: [],
    date_from: '',
    date_to: ''
  };
  originalInvoice.value = '';
  dateFrom.value = '';
  dateTo.value = '';
  fetchReturns(1);
};

const removeSingleFilter = (key) => {
  if (key === 'product') {
    advancedFilters.value.product_id = '';
    advancedFilters.value.product_name = '';
    advancedFilters.value.product_search = '';
  } else if (key === 'invoice') {
    advancedFilters.value.original_invoice = '';
    originalInvoice.value = '';
  } else if (key === 'customer') {
    advancedFilters.value.customer_ids = [];
  } else if (key === 'salesman') {
    advancedFilters.value.salesman_ids = [];
  } else if (key === 'warehouse') {
    advancedFilters.value.warehouse_ids = [];
    advancedFilters.value.counter_ids = [];
  } else if (key === 'counter') {
    advancedFilters.value.counter_ids = [];
  } else if (key === 'reason') {
    advancedFilters.value.return_reasons = [];
  } else if (key === 'date') {
    advancedFilters.value.date_from = '';
    advancedFilters.value.date_to = '';
    dateFrom.value = '';
    dateTo.value = '';
  }
  fetchReturns(1);
};

// Sorting
const sortBy = ref('sale_date');
const sortOrder = ref('desc');

// Pagination
const currentPage = ref(1);
const perPage = ref(15);
const totalItems = ref(0);

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
});

// Counts for each tab state
const counts = ref({
  all: 0,
  cash: 0,
  card: 0,
  store_credit: 0,
  exchange: 0
});

const tabs = [
  { id: 'all', label: 'All Returns' },
  { id: 'cash', label: 'Cash' },
  { id: 'card', label: 'Card Refund' },
  { id: 'store_credit', label: 'Store Credit' },
  { id: 'exchange', label: 'Exchange' }
];

const selectedFilters = ref([]);

const visibleTabs = computed(() => {
  if (selectedFilters.value.length === 0) {
    return tabs.filter(tab => tab.id === 'all');
  }
  return tabs.filter(tab => tab.id === 'all' || selectedFilters.value.includes(tab.id));
});

const isTabActive = (tabId) => {
  return currentTab.value === tabId;
};

const setActiveTab = (tabId) => {
  if (tabId === 'all') {
    selectedFilters.value = [];
    currentTab.value = 'all';
  } else {
    currentTab.value = tabId;
    if (!selectedFilters.value.includes(tabId)) {
      selectedFilters.value.push(tabId);
    }
  }
  fetchReturns(1);
};

const toggleFilterDropdown = () => {
  showFilterDropdown.value = !showFilterDropdown.value;
};

const toggleFilterOption = (option) => {
  const index = selectedFilters.value.indexOf(option);
  if (index > -1) {
    selectedFilters.value.splice(index, 1);
    if (currentTab.value === option) {
      if (selectedFilters.value.length > 0) {
        currentTab.value = selectedFilters.value[selectedFilters.value.length - 1];
      } else {
        currentTab.value = 'all';
      }
    }
  } else {
    selectedFilters.value.push(option);
    currentTab.value = option;
  }
  fetchReturns(1);
};

const removeFilterOption = (option) => {
  toggleFilterOption(option);
};

const getSalesmanName = (item) => {
  if (!item) return 'N/A';

  // 1. Direct salesman relation
  if (item.salesman) {
    if (item.salesman.full_name) return item.salesman.full_name;
    if (item.salesman.first_name) {
      return `${item.salesman.first_name} ${item.salesman.last_name || ''}`.trim();
    }
    if (item.salesman.name) return item.salesman.name;
  }

  // 2. Salesman on original sale
  if (item.original_sale?.salesman) {
    const orig = item.original_sale.salesman;
    if (orig.full_name) return orig.full_name;
    if (orig.first_name) {
      return `${orig.first_name} ${orig.last_name || ''}`.trim();
    }
    if (orig.name) return orig.name;
  }

  // 3. User relation
  if (item.user?.name) {
    return item.user.name;
  }

  // 4. User on original sale
  if (item.original_sale?.user?.name) {
    return item.original_sale.user.name;
  }

  return 'N/A';
};

const clearFilterSelection = () => {
  selectedFilters.value = [];
  currentTab.value = 'all';
  showFilterDropdown.value = false;
  fetchReturns(1);
};

const clearAllFilters = () => {
  selectedFilters.value = [];
  currentTab.value = 'all';
  searchQuery.value = '';
  originalInvoice.value = '';
  dateFrom.value = '';
  dateTo.value = '';
  showFilterDropdown.value = false;
  handleResetAdvancedFilters();
};

// Computed
const paginationRange = computed(() => {
  const range = [];
  const lastPage = pagination.value.last_page || 1;
  const current = currentPage.value;

  if (lastPage <= 6) {
    for (let i = 1; i <= lastPage; i++) range.push(i);
  } else {
    if (current <= 3) {
      range.push(1, 2, 3, '...', lastPage);
    } else if (current >= lastPage - 2) {
      range.push(1, '...', lastPage - 2, lastPage - 1, lastPage);
    } else {
      range.push(1, '...', current, '...', lastPage);
    }
  }
  return range;
});

// Methods
const fetchReturnStatusCounts = async () => {
  try {
    const response = await axios.get('/api/sales/returns/status-counts');
    counts.value = response.data;
  } catch (error) {
    console.error('Error fetching return counts:', error);
  }
};

const fetchReturns = async (page = 1) => {
  loading.value = true;
  currentPage.value = page;
  try {
    const params = {
      page,
      per_page: perPage.value,
      search: searchQuery.value,
      original_invoice: advancedFilters.value.original_invoice || originalInvoice.value,
      date_from: advancedFilters.value.date_from || dateFrom.value,
      date_to: advancedFilters.value.date_to || dateTo.value,
      product_id: advancedFilters.value.product_id,
      product_search: advancedFilters.value.product_search,
      customer_ids: advancedFilters.value.customer_ids?.join(','),
      salesman_ids: advancedFilters.value.salesman_ids?.join(','),
      warehouse_ids: advancedFilters.value.warehouse_ids?.join(','),
      counter_ids: advancedFilters.value.counter_ids?.join(','),
      return_reasons: advancedFilters.value.return_reasons?.join(','),
      is_refund: true,
      sort_by: sortBy.value,
      sort_order: sortOrder.value,
    };

    // Apply active tab filter
    if (currentTab.value !== 'all') {
      params.payment_method = currentTab.value;
    }

    // Apply multiple filters if selected in dropdown
    if (selectedFilters.value.length > 0) {
      params.payment_method = selectedFilters.value.join(',');
    }

    const response = await axios.get('/api/sales', { params });
    returns.value = response.data.data;
    totalItems.value = response.data.total;

    // Update pagination
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
      from: response.data.from,
      to: response.data.to
    };

    await fetchReturnStatusCounts();
  } catch (error) {
    console.error('Error fetching returns:', error);
  } finally {
    loading.value = false;
  }
};

const debouncedSearch = debounce(() => {
  fetchReturns(1);
}, 300);

const handleSort = (field) => {
  if (sortBy.value === field) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = field;
    sortOrder.value = 'desc';
  }
  fetchReturns(1);
};

const handlePerPageChange = () => {
  fetchReturns(1);
};

const toggleActionDropdown = (id) => {
  openActionDropdown.value = openActionDropdown.value === id ? null : id;
};

const closeAllDropdowns = () => {
  openActionDropdown.value = null;
  showFilterDropdown.value = false;
};

const currencyStore = useCurrencyStore();

const currencySymbol = computed(() => {
  return currencyStore.symbol || authStore.user?.company?.currency_symbol || authStore.user?.company?.currency || '$';
});

const formatDate = (dateString) => {
  if (!dateString) return '-';
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('en-US', options);
};

const formatCurrency = (val) => {
  const num = parseFloat(val);
  if (isNaN(num)) return currencySymbol.value + '0.00';
  return currencySymbol.value + ' ' + num.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
};

const formatRefundLabel = (method) => {
  if (!method) return 'N/A';
  if (method === 'store_credit') return 'Store Credit';
  return method.charAt(0).toUpperCase() + method.slice(1);
};

// Actions
const viewReturn = (returnItem) => {
  router.push(`/sales/returns/${returnItem.id}`);
};

const editReturn = (returnItem) => {
  router.push(`/sales/returns/${returnItem.id}/edit`);
};

const printReturn = (returnItem) => {
  router.push(`/sales/returns/${returnItem.id}/print`);
};

const deleteReturn = async (returnId) => {
  if (confirm('Are you sure you want to delete this sales return? Stock reversal and journal entry removal will take effect.')) {
    try {
      await axios.delete(`/api/sales/returns/${returnId}`);
      fetchReturns(currentPage.value);
    } catch (error) {
      console.error('Error deleting return:', error);
    }
  }
};

const previousPage = () => {
  if (currentPage.value > 1) {
    fetchReturns(currentPage.value - 1);
  }
};

const nextPage = () => {
  if (currentPage.value < pagination.value.last_page) {
    fetchReturns(currentPage.value + 1);
  }
};

const goToPage = (page) => {
  fetchReturns(page);
};

// Create return methods
const createReturn = () => {
  router.push('/sales/returns/create');
};

const closeModal = () => {
  showCreateModal.value = false;
  selectedOriginalSaleId.value = null;
};

const handleReturnSaved = () => {
  closeModal();
  fetchReturns(currentPage.value);
};

// Check for original_invoice query parameter
const checkQueryParams = () => {
  if (route.query.original_invoice) {
    selectedOriginalSaleId.value = route.query.original_invoice;
    showCreateModal.value = true;
  }
};

// Lifecycle
onMounted(() => {
  fetchReturns(1);
  checkQueryParams();
  document.addEventListener('click', closeAllDropdowns);
});

onUnmounted(() => {
  document.removeEventListener('click', closeAllDropdowns);
});
</script>

<style scoped>
.shadow-soft {
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03);
}
.animate-fade-in {
  animation: fadeIn 0.15s ease-out;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
.animate-button {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.animate-button:hover {
  transform: translateY(-0.5px);
}
.animate-button:active {
  transform: translateY(0.5px);
}
</style>
