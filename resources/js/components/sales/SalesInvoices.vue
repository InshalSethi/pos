<template>
  <div class="w-full max-w-full py-8 px-4 sm:px-6 lg:px-8 dark:bg-zinc-950">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-extrabold text-slate-900 dark:text-zinc-100 tracking-tight">Invoices</h1>
      <button
        @click="createInvoice"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center space-x-1.5 active:scale-95 animate-button"
      >
        <span>Create Invoice</span>
      </button>
    </div>

    <!-- Tabs Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-slate-200 dark:border-zinc-800 mb-6 pb-0.5 space-y-4 sm:space-y-0">
      <div class="flex flex-wrap gap-x-6 gap-y-2">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          v-show="tab.id === 'all' || selectedFilters.includes(tab.id)"
          @click="setActiveTab(tab.id)"
          class="pb-3 px-1 text-sm font-semibold border-b-2 transition-all flex items-center space-x-2 focus:outline-none relative animate-fade-in"
          :class="isTabActive(tab.id) ? 'border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-400' : 'border-transparent text-slate-500 dark:text-zinc-400 hover:text-slate-700 dark:hover:text-zinc-200 hover:border-slate-300 dark:hover:border-zinc-600'"
        >
          <span>{{ tab.label }}</span>
          <span
            class="text-[10px] px-1.5 py-0.5 rounded-full font-bold"
            :class="isTabActive(tab.id) ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-400'"
          >
            {{ counts[tab.id] || 0 }}
          </span>
        </button>
      </div>

      <div class="flex items-center space-x-2">
        <!-- Sort Button -->
        <button class="inline-flex items-center px-3 py-1.5 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs font-semibold text-slate-600 dark:text-zinc-300 bg-white dark:bg-zinc-900 hover:bg-slate-50 dark:hover:bg-zinc-800 shadow-sm transition-all">
          <svg class="w-3.5 h-3.5 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
          Sort
        </button>
        <!-- Filter Button -->
        <div class="relative">
          <button
            @click.stop="toggleFilterDropdown"
            class="inline-flex items-center px-3 py-1.5 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs font-semibold text-slate-600 dark:text-zinc-300 bg-white dark:bg-zinc-900 hover:bg-slate-50 dark:hover:bg-zinc-800 shadow-sm transition-all focus:outline-none cursor-pointer"
            :class="{ 'border-blue-600 text-blue-600 bg-blue-50/10': selectedFilters.length > 0 }"
          >
            <svg class="w-3.5 h-3.5 mr-1 text-slate-400" :class="{ 'text-blue-600': selectedFilters.length > 0 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 8.293A1 1 0 013 7.586V4z"/></svg>
            <span>Filter</span>
            <!-- Selected Filter Indicator -->
            <span v-if="selectedFilters.length > 0" class="ml-1.5 text-[10px] font-bold bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded-full">
              {{ selectedFilters.length }}
            </span>
          </button>

          <!-- Filter Dropdown List -->
          <div
            v-if="showFilterDropdown"
            class="absolute right-0 mt-1 w-36 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-lg shadow-lg py-1.5 z-50 animate-fade-in"
          >
            <button
              v-for="option in ['draft', 'paid', 'due', 'recurring', 'overdue']"
              :key="option"
              @click.stop="toggleFilterOption(option)"
              class="w-full text-left px-3 py-1.5 text-xs hover:bg-slate-50 dark:hover:bg-zinc-800 flex items-center justify-between transition-colors"
              :class="selectedFilters.includes(option) ? 'text-blue-600 dark:text-blue-400 font-bold bg-blue-50/20 dark:bg-blue-900/20' : 'text-slate-700 dark:text-zinc-300'"
            >
              <span>{{ option.charAt(0).toUpperCase() + option.slice(1) }}</span>
              <svg v-if="selectedFilters.includes(option)" class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            </button>
          </div>
        </div>

        <!-- Clear Button (shows when any filter applies) -->
        <button
          v-if="selectedFilters.length > 0 || searchQuery !== ''"
          @click="clearAllFilters"
          class="inline-flex items-center px-3 py-1.5 border border-rose-200 dark:border-rose-800 text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg text-xs font-semibold shadow-sm transition-all focus:outline-none cursor-pointer"
        >
          <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          Clear
        </button>
      </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-soft">
      <div class="flex items-center justify-between p-4 border-b border-slate-100 dark:border-zinc-800">
        <!-- Search -->
        <div class="relative w-64">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg class="w-4 h-4 text-slate-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </span>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search invoice"
            class="w-full pl-9 pr-4 py-1.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-zinc-800 transition-all text-slate-700 dark:text-zinc-200 dark:placeholder-zinc-500"
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

      <!-- Invoices List Table -->
      <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-200 dark:border-zinc-700 text-slate-500 dark:text-zinc-400 uppercase font-bold tracking-wider">
              <th class="py-3.5 px-4 w-[40px] text-center bg-slate-50 dark:bg-zinc-800/50">
                <input type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-3.5 h-3.5" />
              </th>
              <th class="py-3.5 px-4 cursor-pointer hover:bg-slate-100/50 bg-slate-50 dark:bg-zinc-800/50" @click="handleSort('sale_number')">
                <div class="flex items-center space-x-1">
                  <span>Invoice</span>
                  <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                </div>
              </th>
              <th class="py-3.5 px-4 bg-slate-50 dark:bg-zinc-800/50">Client/Customer</th>
              <th class="py-3.5 px-4 text-right cursor-pointer hover:bg-slate-100/50 bg-slate-50 dark:bg-zinc-800/50" @click="handleSort('total_amount')">
                <div class="flex items-center justify-end space-x-1">
                  <span>Total Amount</span>
                  <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                </div>
              </th>
              <th class="py-3.5 px-4 text-right bg-slate-50 dark:bg-zinc-800/50">Paid Amount</th>
              <th class="py-3.5 px-4 text-right bg-slate-50 dark:bg-zinc-800/50">Due Amount</th>
              <th class="py-3.5 px-4 cursor-pointer hover:bg-slate-100/50 bg-slate-50 dark:bg-zinc-800/50" @click="handleSort('due_date')">
                <div class="flex items-center space-x-1">
                  <span>Due Date</span>
                  <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                </div>
              </th>
              <th class="py-3.5 px-4 text-center cursor-pointer hover:bg-slate-100/50 bg-slate-50 dark:bg-zinc-800/50" @click="handleSort('status')">
                <div class="flex items-center justify-center space-x-1">
                  <span>Status</span>
                  <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                </div>
              </th>
              <th class="py-3.5 px-4 text-center bg-slate-50 dark:bg-zinc-800/50 w-[80px]">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100/70 dark:divide-zinc-800">
            <tr v-if="loading" class="bg-white dark:bg-zinc-900">
              <td colspan="9" class="py-12 text-center text-slate-400 dark:text-zinc-500">
                <div class="flex flex-col items-center justify-center space-y-2">
                  <div class="animate-spin rounded-full h-7 w-7 border-2 border-slate-300 dark:border-zinc-600 border-t-blue-600"></div>
                  <span class="text-xs font-semibold">Loading invoices...</span>
                </div>
              </td>
            </tr>
            <tr v-else-if="invoices.length === 0" class="bg-white dark:bg-zinc-900">
              <td colspan="9" class="py-16 text-center text-slate-400 dark:text-zinc-500 italic">
                <svg class="mx-auto h-10 w-10 text-slate-300 dark:text-zinc-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <span>No invoices found. Get started by creating your first sales invoice.</span>
              </td>
            </tr>
            <tr v-else v-for="item in invoices" :key="item.id" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/50 transition-colors">
              <!-- Checkbox -->
              <td class="py-4 px-4 text-center align-middle bg-white dark:bg-zinc-900">
                <input type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-3.5 h-3.5" />
              </td>

              <!-- Invoice ID -->
              <td class="py-4 px-4 align-middle bg-white dark:bg-zinc-900">
                <div class="font-bold text-slate-800 dark:text-zinc-100 text-sm hover:text-blue-600 cursor-pointer" @click="viewInvoice(item)">
                  {{ item.sale_number }}
                </div>
                <div class="text-[10px] text-slate-400 dark:text-zinc-500 mt-0.5">
                  Created on: {{ formatLongDate(item.created_at) }}
                </div>
              </td>

              <!-- Client/Customer -->
              <td class="py-4 px-4 align-middle bg-white dark:bg-zinc-900">
                <div class="font-semibold text-slate-700 dark:text-zinc-200 text-sm">
                  {{ item.customer?.name || 'Walk-in Customer' }}
                </div>
                <div class="text-[10px] text-slate-400 dark:text-zinc-500 flex items-center mt-0.5 space-x-1">
                  <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5" />
                  </svg>
                  <span>{{ item.customer?.city || 'Default Branch' }}</span>
                </div>
              </td>

              <!-- Total Amount -->
              <td class="py-4 px-4 text-right font-bold text-slate-800 dark:text-zinc-100 text-sm align-middle bg-white dark:bg-zinc-900">
                {{ formatCurrency(item.total_amount) }}
              </td>

              <!-- Paid Amount -->
              <td class="py-4 px-4 text-right font-semibold text-emerald-600 dark:text-emerald-400 text-sm align-middle bg-white dark:bg-zinc-900">
                {{ parseFloat(item.paid_amount) > 0 ? formatCurrency(item.paid_amount) : '-' }}
              </td>

              <!-- Due Amount -->
              <td class="py-4 px-4 text-right font-semibold text-rose-500 dark:text-rose-400 text-sm align-middle bg-white dark:bg-zinc-900">
                {{ getDueAmount(item) > 0 ? formatCurrency(getDueAmount(item)) : '-' }}
              </td>

              <!-- Due Date -->
              <td class="py-4 px-4 text-slate-600 dark:text-zinc-300 text-sm align-middle bg-white dark:bg-zinc-900">
                {{ item.status === 'recurring' ? 'Recurring' : (item.due_date ? formatShortDate(item.due_date) : '-') }}
              </td>

              <!-- Status Badge -->
              <td class="py-4 px-4 text-center align-middle bg-white dark:bg-zinc-900">
                <span
                  :class="getStatusBadgeClass(item)"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold transition-all"
                >
                  {{ getStatusLabel(item) }}
                </span>
              </td>

              <!-- Action Menu Dropdown -->
              <td class="py-4 px-4 text-center relative align-middle bg-white dark:bg-zinc-900">
                <button
                  @click.stop="toggleActionDropdown(item.id)"
                  class="text-slate-400 dark:text-zinc-500 hover:text-slate-600 dark:hover:text-zinc-300 p-1 rounded-full hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all focus:outline-none"
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
                  <button @click="viewInvoice(item)" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>View</span>
                  </button>
                  <button @click="editInvoice(item)" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit</span>
                  </button>
                  <button @click="printInvoice(item)" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print</span>
                  </button>
                  <div class="border-t border-slate-100 dark:border-zinc-800 my-1"></div>
                  <button @click="deleteInvoice(item.id)" class="w-full text-left px-3 py-1.5 text-xs text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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

  </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { debounce } from '@/utils/debounce';
import axios from 'axios';

const authStore = useAuthStore();
const router = useRouter();

// Reactive data
const invoices = ref([]);
const searchQuery = ref('');
const selectedFilters = ref([]);
const currentTab = ref('all');
const showEditModal = ref(false);
const selectedInvoice = ref(null);
const loading = ref(false);
const openActionDropdown = ref(null);
const showFilterDropdown = ref(false);

const isTabActive = (tabId) => {
  return currentTab.value === tabId;
};

const toggleFilterDropdown = () => {
  showFilterDropdown.value = !showFilterDropdown.value;
};

const toggleFilterOption = (option) => {
  const index = selectedFilters.value.indexOf(option);
  if (index > -1) {
    selectedFilters.value.splice(index, 1);
    if (currentTab.value === option) {
      currentTab.value = 'all';
      fetchInvoices(1);
    }
  } else {
    selectedFilters.value.push(option);
  }
};

const clearAllFilters = () => {
  selectedFilters.value = [];
  currentTab.value = 'all';
  searchQuery.value = '';
  fetchInvoices(1);
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
  draft: 0,
  paid: 0,
  due: 0,
  recurring: 0,
  overdue: 0
});

const tabs = [
  { id: 'all', label: 'All Invoices' },
  { id: 'draft', label: 'Draft' },
  { id: 'paid', label: 'Paid' },
  { id: 'due', label: 'Due' },
  { id: 'recurring', label: 'Recurring' },
  { id: 'overdue', label: 'Overdue' }
];

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
const fetchStatusCounts = async () => {
  try {
    const response = await axios.get('/api/sales/status-counts');
    counts.value = response.data;
  } catch (error) {
    console.error('Error fetching counts:', error);
  }
};

const fetchInvoices = async (page = 1) => {
  loading.value = true;
  currentPage.value = page;
  try {
    const params = {
      page,
      per_page: perPage.value,
      search: searchQuery.value,
      sort_by: sortBy.value,
      sort_order: sortOrder.value,
    };

    if (currentTab.value !== 'all') {
      let statusParam = currentTab.value;
      if (statusParam === 'paid') statusParam = 'completed';
      if (statusParam === 'due') statusParam = 'pending';
      params.status = statusParam;
    }

    const response = await axios.get('/api/sales', { params });
    invoices.value = response.data.data;
    
    // Update pagination
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
      from: response.data.from,
      to: response.data.to
    };
    totalItems.value = response.data.total;
    
    await fetchStatusCounts();
  } catch (error) {
    console.error('Error fetching invoices:', error);
  } finally {
    loading.value = false;
  }
};

const debouncedSearch = debounce(() => {
  fetchInvoices(1);
}, 300);

const handleSort = (field) => {
  if (sortBy.value === field) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = field;
    sortOrder.value = 'desc';
  }
  fetchInvoices(1);
};

const handlePerPageChange = () => {
  fetchInvoices(1);
};

const setActiveTab = (tabId) => {
  currentTab.value = tabId;
  fetchInvoices(1);
};

const toggleActionDropdown = (id) => {
  openActionDropdown.value = openActionDropdown.value === id ? null : id;
};

const closeAllDropdowns = () => {
  openActionDropdown.value = null;
  showFilterDropdown.value = false;
};

// Date Format Helpers
const formatLongDate = (dateString) => {
  if (!dateString) return '-';
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('en-US', options);
};

const formatShortDate = (dateString) => {
  if (!dateString) return '-';
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('en-US', options);
};

const formatCurrency = (val) => {
  const num = parseFloat(val);
  if (isNaN(num)) return '$0.00';
  return '$' + num.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
};

const getDueAmount = (item) => {
  return Math.max(0, parseFloat(item.total_amount) - parseFloat(item.paid_amount || 0));
};

const getStatusLabel = (item) => {
  if (item.status === 'completed') return 'Paid';
  if (item.status === 'pending') {
    const dueDate = item.due_date ? new Date(item.due_date) : null;
    if (dueDate && dueDate < new Date().setHours(0,0,0,0)) {
      return 'Overdue';
    }
    return 'Due';
  }
  if (item.status === 'draft') return 'Draft';
  if (item.status === 'recurring') return 'Recurring';
  return item.status.charAt(0).toUpperCase() + item.status.slice(1);
};

const getStatusBadgeClass = (item) => {
  const label = getStatusLabel(item);
  if (label === 'Paid') return 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400';
  if (label === 'Due') return 'bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400';
  if (label === 'Overdue') return 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400';
  if (label === 'Draft') return 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-400';
  return 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400';
};

// Actions
const createInvoice = () => {
  router.push('/sales/invoices/create');
};

const viewInvoice = (invoice) => {
  router.push(`/sales/invoices/${invoice.id}`);
};

const printInvoice = (invoice) => {
  const printUrl = router.resolve(`/sales/invoices/${invoice.id}/print`).href;
  window.open(printUrl, '_blank');
};

const editInvoice = (invoice) => {
  router.push(`/sales/invoices/${invoice.id}/edit`);
};

const deleteInvoice = async (invoiceId) => {
  if (confirm('Are you sure you want to delete this invoice?')) {
    try {
      await axios.delete(`/api/sales/${invoiceId}`);
      fetchInvoices(currentPage.value);
    } catch (error) {
      console.error('Error deleting invoice:', error);
    }
  }
};

// Pagination Methods
const previousPage = () => {
  if (currentPage.value > 1) {
    fetchInvoices(currentPage.value - 1);
  }
};

const nextPage = () => {
  if (currentPage.value < pagination.value.last_page) {
    fetchInvoices(currentPage.value + 1);
  }
};

const goToPage = (page) => {
  fetchInvoices(page);
};

const closeModal = () => {
  showEditModal.value = false;
  selectedInvoice.value = null;
};

const handleInvoiceSaved = () => {
  closeModal();
  fetchInvoices(currentPage.value);
};

// Lifecycle
onMounted(() => {
  fetchInvoices(1);
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
