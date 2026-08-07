<template>
  <div class="space-y-6 w-full min-w-0">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-zinc-100 tracking-tight">Purchase Returns</h1>
        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
          Manage purchase return requests, supplier debit notes & inventory refund tracking.
        </p>
      </div>

      <div class="flex items-center space-x-3">
        <!-- New Purchase Return Button -->
        <button
          @click="createPurchaseReturn"
          class="bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-bold px-4 py-2 rounded-lg shadow-sm transition-all flex items-center space-x-1.5 cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Create Purchase Return</span>
        </button>
      </div>
    </div>

    <!-- Status Tabs & Quick Controls -->
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 dark:border-zinc-800 pb-3">
      <!-- Dynamic Status Tabs -->
      <div class="flex items-center space-x-1 overflow-x-auto custom-scrollbar pb-1 sm:pb-0">
        <button
          v-for="tab in visibleTabs"
          :key="tab.id"
          @click="switchTab(tab.id)"
          class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all whitespace-nowrap cursor-pointer flex items-center space-x-1.5"
          :class="isTabActive(tab.id) 
            ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 shadow-xs' 
            : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 hover:text-slate-900 dark:hover:text-zinc-200'"
        >
          <span>{{ tab.label }}</span>
          <span
            v-if="counts[tab.id] !== undefined"
            class="px-1.5 py-0.2 text-[10px] rounded-full font-bold"
            :class="isTabActive(tab.id)
              ? 'bg-slate-700 text-slate-100 dark:bg-zinc-300 dark:text-zinc-900'
              : 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400'"
          >
            {{ counts[tab.id] }}
          </span>
        </button>
      </div>

      <!-- Sorting & Filter Controls -->
      <div class="flex items-center space-x-2 shrink-0">
        <!-- Sort Direction Toggle -->
        <button
          @click="toggleSortOrder"
          class="px-3 py-1.5 text-xs font-semibold text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors border border-slate-200 dark:border-zinc-700 cursor-pointer"
        >
          Sort: {{ sortOrder === 'desc' ? 'Newest' : 'Oldest' }}
        </button>

        <!-- Filter Drawer Trigger Button -->
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
    <div v-if="totalActiveFilterCount > 0" class="flex flex-wrap items-center gap-2 mb-6 p-3 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl shadow-soft animate-fade-in">
      <span class="text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mr-1">Active Filters:</span>

      <!-- Product Pill -->
      <span v-if="advancedFilters.product_name || advancedFilters.product_search" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
        Item: {{ advancedFilters.product_name || advancedFilters.product_search }}
        <button @click="removeSingleFilter('product')" class="ml-1.5 hover:text-blue-900 dark:hover:text-white cursor-pointer"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
      </span>

      <!-- Original PO / Bill Pill -->
      <span v-if="advancedFilters.po_number" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-violet-50 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 border border-violet-200 dark:border-violet-800">
        Original PO: {{ advancedFilters.po_number }}
        <button @click="removeSingleFilter('po_number')" class="ml-1.5 hover:text-violet-900 dark:hover:text-white cursor-pointer"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
      </span>

      <!-- Supplier Pill -->
      <span v-if="advancedFilters.supplier_ids?.length > 0" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
        Supplier: {{ getSupplierSummary() }}
        <button @click="removeSingleFilter('supplier')" class="ml-1.5 hover:text-teal-900 dark:hover:text-white cursor-pointer"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
      </span>

      <!-- Warehouse Pill -->
      <span v-if="advancedFilters.warehouse_ids?.length > 0" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
        Location: {{ getWarehouseSummary() }}
        <button @click="removeSingleFilter('warehouse')" class="ml-1.5 hover:text-emerald-900 dark:hover:text-white cursor-pointer"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
      </span>

      <!-- Status Pill -->
      <span v-if="advancedFilters.statuses?.length > 0" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
        Status: {{ getStatusSummary() }}
        <button @click="removeSingleFilter('status')" class="ml-1.5 hover:text-indigo-900 dark:hover:text-white cursor-pointer"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
      </span>

      <!-- Reason Pill -->
      <span v-if="advancedFilters.return_reasons?.length > 0" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
        Reason: {{ getReasonSummary() }}
        <button @click="removeSingleFilter('reason')" class="ml-1.5 hover:text-amber-900 dark:hover:text-white cursor-pointer"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
      </span>

      <!-- Date Range Pill -->
      <span v-if="activeDateRangeLabel" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-cyan-50 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800">
        Date: {{ activeDateRangeLabel }}
        <button @click="removeSingleFilter('date')" class="ml-1.5 hover:text-cyan-900 dark:hover:text-white cursor-pointer"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
      </span>

      <!-- Search Query Pill -->
      <span v-if="searchQuery" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border border-slate-200 dark:border-zinc-700">
        Search: "{{ searchQuery }}"
        <button @click="searchQuery = ''; fetchPurchaseReturns(1)" class="ml-1.5 hover:text-slate-900 dark:hover:text-white cursor-pointer"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
      </span>

      <button
        @click="clearAllFilters"
        class="text-xs text-rose-600 dark:text-rose-400 hover:underline font-semibold ml-auto cursor-pointer"
      >
        Clear All
      </button>
    </div>

    <!-- Table Container -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-soft">
      <div class="flex items-center justify-between p-4 border-b border-slate-100 dark:border-zinc-800">
        <!-- Global Search -->
        <div class="relative w-96">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg class="w-4 h-4 text-slate-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </span>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search by return #, supplier name..."
            class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-100 dark:focus:border-zinc-600 dark:focus:ring-zinc-800 transition-all placeholder:text-slate-400 dark:placeholder:text-zinc-500"
          />
        </div>

        <div class="text-xs text-slate-500 dark:text-zinc-400 font-medium">
          Showing <span class="font-bold text-slate-900 dark:text-zinc-100">{{ returns.length }}</span> of <span class="font-bold text-slate-900 dark:text-zinc-100">{{ pagination.total }}</span> purchase returns
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/50 dark:bg-zinc-800/50 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400 border-b border-slate-200 dark:border-zinc-800">
              <th class="py-3 px-4 text-center w-10">
                <input type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-3.5 h-3.5" />
              </th>
              <th class="py-3 px-4">Return #</th>
              <th class="py-3 px-4">PO / Bill Ref</th>
              <th class="py-3 px-4">Supplier</th>
              <th class="py-3 px-4">Warehouse</th>
              <th class="py-3 px-4 text-right">Total Refund</th>
              <th class="py-3 px-4">Return Date</th>
              <th class="py-3 px-4 text-center">Status</th>
              <th class="py-3 px-4 text-center w-16">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-zinc-800 text-xs">
            <tr v-if="loading" class="bg-white dark:bg-zinc-900">
              <td colspan="9" class="py-12 text-center text-slate-400 dark:text-zinc-500">
                <div class="animate-spin rounded-full h-6 w-6 border-2 border-slate-300 border-t-slate-800 mx-auto mb-2"></div>
                Loading purchase returns...
              </td>
            </tr>
            <tr v-else-if="returns.length === 0" class="bg-white dark:bg-zinc-900">
              <td colspan="9" class="py-16 text-center text-slate-400 dark:text-zinc-500 italic">
                <svg class="mx-auto h-10 w-10 text-slate-300 dark:text-zinc-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <span>No purchase returns found matching selected filter criteria.</span>
              </td>
            </tr>
            <tr v-else v-for="item in returns" :key="item.id" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/50 transition-colors">
              <!-- Checkbox -->
              <td class="py-4 px-4 text-center align-middle bg-white dark:bg-zinc-900">
                <input type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-3.5 h-3.5" />
              </td>

              <!-- Return # -->
              <td class="py-4 px-4 align-middle bg-white dark:bg-zinc-900">
                <div class="font-bold text-slate-800 dark:text-zinc-100 text-sm hover:text-blue-600 cursor-pointer flex items-center space-x-1.5" @click="viewPurchaseReturn(item)">
                  <span>{{ item.return_number }}</span>
                </div>
                <div class="text-[10px] text-slate-400 dark:text-zinc-500 mt-0.5">
                  Created: {{ formatShortDate(item.created_at) }}
                </div>
              </td>

              <!-- PO / Bill Ref -->
              <td class="py-4 px-4 align-middle bg-white dark:bg-zinc-900">
                <div class="font-medium text-slate-700 dark:text-zinc-200">
                  {{ item.original_purchase_order?.po_number || item.po_number || '-' }}
                </div>
              </td>

              <!-- Supplier -->
              <td class="py-4 px-4 align-middle bg-white dark:bg-zinc-900">
                <div class="font-semibold text-slate-700 dark:text-zinc-200 text-sm">
                  {{ item.supplier?.name || 'Walk-in Supplier' }}
                </div>
                <div v-if="item.supplier?.company_name" class="text-[10px] text-purple-600 dark:text-purple-400 font-medium">
                  {{ item.supplier.company_name }}
                </div>
              </td>

              <!-- Warehouse -->
              <td class="py-4 px-4 align-middle bg-white dark:bg-zinc-900 text-slate-700 dark:text-zinc-300 font-medium">
                {{ item.warehouse?.name || 'Default Warehouse' }}
              </td>

              <!-- Total Refund -->
              <td class="py-4 px-4 text-right font-bold text-slate-800 dark:text-zinc-100 text-sm align-middle bg-white dark:bg-zinc-900">
                {{ formatCurrency(item.total_amount) }}
              </td>

              <!-- Return Date -->
              <td class="py-4 px-4 text-slate-600 dark:text-zinc-300 text-sm align-middle bg-white dark:bg-zinc-900">
                {{ formatShortDate(item.return_date || item.created_at) }}
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
                  class="text-slate-400 dark:text-zinc-500 hover:text-slate-600 dark:hover:text-zinc-300 p-1 rounded-full hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all focus:outline-none cursor-pointer"
                >
                  <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" d="M12 5a2 2 0 100-4 2 2 0 000 4zm0 9a2 2 0 100-4 2 2 0 000 4zm0 9a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                  </svg>
                </button>
                
                <!-- Action Dropdown Overlay -->
                <div
                  v-if="openActionDropdown === item.id"
                  class="absolute right-4 mt-1 w-36 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-lg shadow-lg py-1 z-50 animate-fade-in"
                >
                  <button @click="viewPurchaseReturn(item)" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>View</span>
                  </button>
                  <button v-if="item.status !== 'completed' && item.status !== 'cancelled'" @click="editPurchaseReturn(item)" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit</span>
                  </button>
                  <button @click="printPurchaseReturn(item)" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print</span>
                  </button>
                  <div class="border-t border-slate-100 dark:border-zinc-800 my-1"></div>
                  <button @click="deletePurchaseReturn(item.id)" class="w-full text-left px-3 py-1.5 text-xs text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Delete</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div class="px-4 py-3 bg-slate-50/50 dark:bg-zinc-800/50 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between">
        <div class="text-xs text-slate-500 dark:text-zinc-400">
          Showing <span class="font-bold text-slate-700 dark:text-zinc-200">{{ pagination.from }}</span> to <span class="font-bold text-slate-700 dark:text-zinc-200">{{ pagination.to }}</span> of <span class="font-bold text-slate-700 dark:text-zinc-200">{{ pagination.total }}</span> results
        </div>
        <div class="flex items-center space-x-1">
          <button
            @click="fetchPurchaseReturns(pagination.current_page - 1)"
            :disabled="pagination.current_page <= 1"
            class="px-2.5 py-1 text-xs font-semibold rounded border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-slate-600 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 disabled:opacity-40 cursor-pointer"
          >
            Prev
          </button>
          <template v-for="page in paginationRange" :key="page">
            <span v-if="page === '...'" class="px-2 text-xs text-slate-400">...</span>
            <button
              v-else
              @click="fetchPurchaseReturns(page)"
              class="px-2.5 py-1 text-xs font-semibold rounded transition-colors cursor-pointer"
              :class="page === pagination.current_page 
                ? 'bg-blue-600 text-white font-bold' 
                : 'border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-slate-600 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800'"
            >
              {{ page }}
            </button>
          </template>
          <button
            @click="fetchPurchaseReturns(pagination.current_page + 1)"
            :disabled="pagination.current_page >= pagination.last_page"
            class="px-2.5 py-1 text-xs font-semibold rounded border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-slate-600 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 disabled:opacity-40 cursor-pointer"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Slide-over Filter Drawer Component -->
    <PurchaseReturnFilter
      v-model:isOpen="isFilterDrawerOpen"
      :filters="advancedFilters"
      @apply="handleApplyAdvancedFilters"
      @reset="handleResetAdvancedFilters"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useCurrencyStore } from '@/stores/currency';
import { useToast } from '@/composables/useToast';
import { debounce } from '@/utils/debounce';
import PurchaseReturnFilter from './PurchaseReturnFilter.vue';
import axios from 'axios';

const authStore = useAuthStore();
const currencyStore = useCurrencyStore();
const router = useRouter();
const { showToast } = useToast();

// Reactive Data
const returns = ref([]);
const searchQuery = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const currentTab = ref('all');
const loading = ref(false);
const openActionDropdown = ref(null);
const isFilterDrawerOpen = ref(false);

// Advanced Filter State
const advancedFilters = ref({
  product_id: '',
  product_name: '',
  product_search: '',
  po_number: '',
  supplier_ids: [],
  warehouse_ids: [],
  statuses: [],
  return_reasons: [],
  date_from: '',
  date_to: ''
});

// Lookup data for active filter badges
const supplierList = ref([]);
const warehouseList = ref([]);

const extractArray = (resData) => {
  if (Array.isArray(resData)) return resData;
  if (resData && Array.isArray(resData.data)) return resData.data;
  return [];
};

const loadFilterLookups = async () => {
  try {
    const [suppRes, whRes] = await Promise.all([
      axios.get('/api/suppliers').catch(() => ({ data: [] })),
      axios.get('/api/warehouses').catch(() => ({ data: [] }))
    ]);
    supplierList.value = extractArray(suppRes.data);
    warehouseList.value = extractArray(whRes.data);
  } catch (e) {
    console.error('Error loading filter lookups:', e);
  }
};

const getSupplierSummary = () => {
  const ids = advancedFilters.value.supplier_ids || [];
  if (ids.length === 0) return '';
  const list = Array.isArray(supplierList.value) ? supplierList.value : [];
  const first = list.find(s => String(s.id) === String(ids[0]));
  const name = first ? first.name : `Supplier #${ids[0]}`;
  return ids.length > 1 ? `${name} (+${ids.length - 1})` : name;
};

const getWarehouseSummary = () => {
  const ids = advancedFilters.value.warehouse_ids || [];
  if (ids.length === 0) return '';
  const list = Array.isArray(warehouseList.value) ? warehouseList.value : [];
  const first = list.find(w => String(w.id) === String(ids[0]));
  const name = first ? first.name : `Location #${ids[0]}`;
  return ids.length > 1 ? `${name} (+${ids.length - 1})` : name;
};

const getStatusSummary = () => {
  const list = advancedFilters.value.statuses || [];
  if (list.length === 0) return '';
  const map = {
    draft: 'Draft',
    pending: 'Pending Approval',
    approved: 'Approved',
    completed: 'Completed',
    cancelled: 'Cancelled'
  };
  const firstLabel = map[list[0]] || list[0];
  return list.length > 1 ? `${firstLabel} (+${list.length - 1})` : firstLabel;
};

const getReasonSummary = () => {
  const list = advancedFilters.value.return_reasons || [];
  if (list.length === 0) return '';
  return list.length > 1 ? `${list[0]} (+${list.length - 1})` : list[0];
};

const activeDateRangeLabel = computed(() => {
  const from = advancedFilters.value.date_from || dateFrom.value;
  const to = advancedFilters.value.date_to || dateTo.value;
  if (from && to) return `${from} to ${to}`;
  if (from) return `From ${from}`;
  if (to) return `Until ${to}`;
  return '';
});

const totalActiveFilterCount = computed(() => {
  let count = 0;
  if (searchQuery.value) count++;
  if (advancedFilters.value.product_id || advancedFilters.value.product_search) count++;
  if (advancedFilters.value.po_number) count++;
  if (advancedFilters.value.supplier_ids?.length > 0) count++;
  if (advancedFilters.value.warehouse_ids?.length > 0) count++;
  if (advancedFilters.value.statuses?.length > 0) count++;
  if (advancedFilters.value.return_reasons?.length > 0) count++;
  if (advancedFilters.value.date_from || advancedFilters.value.date_to || dateFrom.value || dateTo.value) count++;
  return count;
});

const allStatusTabs = [
  { id: 'draft', label: 'Draft' },
  { id: 'pending', label: 'Pending Approval' },
  { id: 'approved', label: 'Approved' },
  { id: 'completed', label: 'Completed' },
  { id: 'cancelled', label: 'Cancelled' }
];

const visibleTabs = computed(() => {
  const selectedStatuses = advancedFilters.value.statuses || [];
  const allTab = { id: 'all', label: 'All Returns' };

  if (selectedStatuses.length === 0) {
    return [allTab];
  }

  const activeIds = selectedStatuses.map(st => {
    if (st === 'void' || st === 'voided') return 'cancelled';
    return st;
  });

  const filtered = allStatusTabs.filter(t => activeIds.includes(t.id));
  return [allTab, ...filtered];
});

watch(visibleTabs, (newTabs) => {
  const isCurrentStillVisible = newTabs.some(t => t.id === currentTab.value);
  if (!isCurrentStillVisible) {
    currentTab.value = 'all';
  }
});

const isTabActive = (tabId) => {
  return currentTab.value === tabId;
};

const openFilterDrawer = () => {
  isFilterDrawerOpen.value = true;
};

const handleApplyAdvancedFilters = (newFilters) => {
  advancedFilters.value = { ...newFilters };
  if (newFilters.date_from) dateFrom.value = newFilters.date_from;
  if (newFilters.date_to) dateTo.value = newFilters.date_to;
  fetchPurchaseReturns(1);
};

const handleResetAdvancedFilters = () => {
  advancedFilters.value = {
    product_id: '',
    product_name: '',
    product_search: '',
    po_number: '',
    supplier_ids: [],
    warehouse_ids: [],
    statuses: [],
    return_reasons: [],
    date_from: '',
    date_to: ''
  };
  dateFrom.value = '';
  dateTo.value = '';
  fetchPurchaseReturns(1);
};

const removeSingleFilter = (key) => {
  if (key === 'date') {
    advancedFilters.value.date_from = '';
    advancedFilters.value.date_to = '';
    dateFrom.value = '';
    dateTo.value = '';
  } else if (key === 'product') {
    advancedFilters.value.product_id = '';
    advancedFilters.value.product_name = '';
    advancedFilters.value.product_search = '';
  } else if (key === 'po_number') {
    advancedFilters.value.po_number = '';
  } else if (key === 'supplier') {
    advancedFilters.value.supplier_ids = [];
  } else if (key === 'warehouse') {
    advancedFilters.value.warehouse_ids = [];
  } else if (key === 'status') {
    advancedFilters.value.statuses = [];
  } else if (key === 'reason') {
    advancedFilters.value.return_reasons = [];
  }
  fetchPurchaseReturns(1);
};

const clearAllFilters = () => {
  currentTab.value = 'all';
  searchQuery.value = '';
  dateFrom.value = '';
  dateTo.value = '';
  advancedFilters.value = {
    product_id: '',
    product_name: '',
    product_search: '',
    po_number: '',
    supplier_ids: [],
    warehouse_ids: [],
    statuses: [],
    return_reasons: [],
    date_from: '',
    date_to: ''
  };
  fetchPurchaseReturns(1);
};

// Sorting
const sortBy = ref('created_at');
const sortOrder = ref('desc');

const toggleSortOrder = () => {
  sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  fetchPurchaseReturns(1);
};

// Pagination
const currentPage = ref(1);
const perPage = ref(15);

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
});

// Counts for status tabs
const counts = ref({
  all: 0,
  draft: 0,
  pending: 0,
  approved: 0,
  completed: 0,
  cancelled: 0
});

// Computed Pagination Range
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

// Fetch Data
const fetchPurchaseReturns = async (page = 1) => {
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
      params.status = currentTab.value;
    }

    if (advancedFilters.value.product_id) {
      params.product_id = advancedFilters.value.product_id;
    }

    if (advancedFilters.value.po_number) {
      params.po_number = advancedFilters.value.po_number;
    }

    if (advancedFilters.value.supplier_ids?.length > 0) {
      params.supplier_ids = advancedFilters.value.supplier_ids.join(',');
    }

    if (advancedFilters.value.warehouse_ids?.length > 0) {
      params.warehouse_ids = advancedFilters.value.warehouse_ids.join(',');
    }

    if (advancedFilters.value.statuses?.length > 0 && currentTab.value === 'all') {
      params.status = advancedFilters.value.statuses.join(',');
    }

    if (advancedFilters.value.return_reasons?.length > 0) {
      params.reasons = advancedFilters.value.return_reasons.join(',');
    }

    const dateFromVal = advancedFilters.value.date_from || dateFrom.value;
    const dateToVal = advancedFilters.value.date_to || dateTo.value;
    if (dateFromVal) params.date_from = dateFromVal;
    if (dateToVal) params.date_to = dateToVal;

    const response = await axios.get('/api/purchase-returns', { params });
    const resData = response.data;
    
    if (resData.returns) {
      returns.value = resData.returns.data || [];
      pagination.value = {
        current_page: resData.returns.current_page || 1,
        last_page: resData.returns.last_page || 1,
        per_page: resData.returns.per_page || 15,
        total: resData.returns.total || 0,
        from: resData.returns.from || 0,
        to: resData.returns.to || 0,
      };
    }

    if (resData.status_counts) {
      counts.value = resData.status_counts;
    }
  } catch (error) {
    console.error('Error fetching purchase returns:', error);
    showToast('Failed to load purchase returns', 'error');
  } finally {
    loading.value = false;
  }
};

const debouncedSearch = debounce(() => {
  fetchPurchaseReturns(1);
}, 300);

watch(searchQuery, () => {
  debouncedSearch();
});

const switchTab = (tabId) => {
  currentTab.value = tabId;
  fetchPurchaseReturns(1);
};

const createPurchaseReturn = () => {
  router.push('/purchase/returns/create');
};

const viewPurchaseReturn = (item) => {
  openActionDropdown.value = null;
  router.push(`/purchase/returns/${item.id}`);
};

const editPurchaseReturn = (item) => {
  openActionDropdown.value = null;
  router.push(`/purchase/returns/${item.id}/edit`);
};

const printPurchaseReturn = (item) => {
  openActionDropdown.value = null;
  window.open(`/purchase/returns/${item.id}/print`, '_blank');
};

const deletePurchaseReturn = async (id) => {
  openActionDropdown.value = null;
  if (confirm('Are you sure you want to delete this purchase return? This operation cannot be undone.')) {
    try {
      await axios.delete(`/api/purchase-returns/${id}`);
      showToast('Purchase return deleted successfully', 'success');
      fetchPurchaseReturns(currentPage.value);
    } catch (error) {
      console.error('Error deleting purchase return:', error);
      showToast(error.response?.data?.message || 'Failed to delete purchase return', 'error');
    }
  }
};

const toggleActionDropdown = (id) => {
  if (openActionDropdown.value === id) {
    openActionDropdown.value = null;
  } else {
    openActionDropdown.value = id;
  }
};

const handleClickOutside = () => {
  if (openActionDropdown.value !== null) {
    openActionDropdown.value = null;
  }
};

const formatCurrency = (val) => {
  if (currencyStore && typeof currencyStore.formatPrice === 'function') {
    return currencyStore.formatPrice(val);
  }
  const amt = parseFloat(val) || 0;
  return `$${amt.toFixed(2)}`;
};

const formatShortDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const getStatusBadgeClass = (item) => {
  const st = item.status;
  if (st === 'completed') return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300';
  if (st === 'pending') return 'bg-orange-100 text-orange-800 dark:bg-orange-950/60 dark:text-orange-300';
  if (st === 'draft') return 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300';
  if (st === 'approved') return 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300';
  if (st === 'cancelled' || st === 'void' || st === 'voided') return 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300';
  return 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300';
};

const getStatusLabel = (item) => {
  const st = item.status;
  if (st === 'pending') return 'Pending Approval';
  if (st === 'completed') return 'Completed';
  if (st === 'approved') return 'Approved';
  if (st === 'draft') return 'Draft';
  if (st === 'cancelled' || st === 'void') return 'Cancelled';
  return st ? st.charAt(0).toUpperCase() + st.slice(1) : '-';
};

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  fetchPurchaseReturns(1);
  loadFilterLookups();
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.4);
  border-radius: 4px;
}
.shadow-soft {
  box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
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
</style>
