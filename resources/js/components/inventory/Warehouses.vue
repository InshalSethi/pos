<template>
  <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 font-sans">
    
    <!-- 1. WAREHOUSE LIST VIEW -->
    <div v-if="currentView === 'list'">
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
          <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Warehouses</h1>
          <span class="bg-indigo-100 text-indigo-800 dark:bg-indigo-900/55 dark:text-indigo-300 text-xs px-2.5 py-1 rounded-full font-bold">
            {{ warehouses.length }} Locations
          </span>
        </div>

        <div class="flex items-center gap-3">
          <button 
            type="button" 
            @click="openCreateModal" 
            class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-full shadow-sm hover:shadow transition-all flex items-center gap-2 active:scale-95"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            Add Warehouse
          </button>
        </div>
      </div>

      <!-- Feedback Banner -->
      <div v-if="feedbackMsg" :class="feedbackClass" class="mb-6 p-4 rounded-xl border flex items-center justify-between text-sm font-medium transition-all">
        <span class="flex items-center gap-2">
          <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ feedbackMsg }}
        </span>
        <button @click="feedbackMsg = ''" class="text-current opacity-70 hover:opacity-100">&times;</button>
      </div>

      <!-- Main Content Layout (Full Width Warehouses Table) -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div v-if="loading" class="text-center py-20">
          <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600 mx-auto"></div>
          <p class="mt-3 text-slate-500 text-sm font-medium">Loading warehouses...</p>
        </div>

        <div v-else-if="warehouses.length === 0" class="text-center py-16 px-4">
          <div class="w-16 h-16 bg-slate-50 dark:bg-slate-850 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100 dark:border-slate-800">
            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <h3 class="text-base font-bold text-slate-800 dark:text-white">No warehouses configured</h3>
          <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto">Create multiple warehouses to track stock levels separately across various physical locations.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full table-auto border-collapse">
            <thead>
              <tr class="bg-slate-50 dark:bg-slate-850 border-b border-slate-200 dark:border-slate-800 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                <th class="px-6 py-4 text-left">Warehouse Details</th>
                <th class="px-6 py-4 text-left">Address</th>
                <th class="px-6 py-4 text-center">Status</th>
                <th class="px-6 py-4 text-center w-40">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr 
                v-for="wh in warehouses" 
                :key="wh.id"
                class="hover:bg-slate-50/60 dark:hover:bg-slate-850/30 transition-colors"
              >
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                      </svg>
                    </div>
                    <div>
                      <div class="flex items-center gap-1.5 font-bold text-slate-900 dark:text-white text-sm">
                        {{ wh.name }}
                        <span v-if="wh.is_default" class="px-2 py-0.5 text-[9px] font-extrabold uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-400 rounded-full">Default</span>
                      </div>
                      <div class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Code: {{ wh.code || 'N/A' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-xs font-medium text-slate-600 dark:text-slate-300">
                  <span v-if="wh.address">{{ wh.address }}</span>
                  <span v-else class="text-slate-400 italic">No address provided</span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span 
                    :class="[
                      'px-2 py-1 text-[9px] font-extrabold uppercase rounded-full',
                      wh.is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400'
                    ]"
                  >
                    {{ wh.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center align-middle">
                  <div class="flex items-center justify-center gap-1.5">
                    <!-- View Details Modal Button -->
                    <button 
                      type="button" 
                      @click="showWarehouseDetails(wh)"
                      class="p-1.5 text-slate-450 hover:text-emerald-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-all focus:outline-none"
                      title="View Details"
                    >
                      <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                    <!-- List Inventory View Button -->
                    <button 
                      type="button" 
                      @click="showInventoryView(wh)"
                      class="p-1.5 text-slate-450 hover:text-indigo-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-all focus:outline-none"
                      title="List Inventory"
                    >
                      <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                      </svg>
                    </button>
                    <!-- Edit Button -->
                    <button 
                      type="button" 
                      @click="openEditModal(wh)"
                      class="p-1.5 text-slate-455 hover:text-indigo-650 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-all focus:outline-none"
                      title="Edit Warehouse"
                    >
                      <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <!-- Delete Button -->
                    <button 
                      v-if="!wh.is_default"
                      type="button" 
                      @click="deleteWarehouse(wh.id)"
                      class="p-1.5 text-slate-455 hover:text-rose-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-all focus:outline-none"
                      title="Delete Warehouse"
                    >
                      <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- 2. WAREHOUSE INVENTORY DEDICATED VIEW -->
    <div v-if="currentView === 'inventory'">
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
          <button 
            type="button" 
            @click="currentView = 'list'"
            class="p-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-350 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-full transition-all"
            title="Back to Warehouses"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </button>
          <div>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
              {{ activeWarehouse?.name }}
            </h1>
            <p class="text-xs text-slate-450 dark:text-slate-500 font-bold uppercase tracking-wider mt-0.5">
              Warehouse Inventory
            </p>
          </div>
        </div>
      </div>

      <!-- Controls and Table -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-6 space-y-4">
        <!-- Controls Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <!-- Show entries selection -->
          <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
            <span>Show</span>
            <select 
              v-model="inventoryPerPage"
              @change="fetchInventory(1)"
              class="px-2.5 py-1.5 bg-slate-50 dark:bg-slate-850 dark:text-white border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer"
            >
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
            <span>entries</span>
          </div>

          <!-- Live search input -->
          <div class="relative w-full sm:max-w-xs">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </span>
            <input 
              v-model="inventorySearch"
              @input="onSearchInput"
              type="text" 
              placeholder="Search code, name, category, variation..."
              class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-850 dark:text-white border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold focus:outline-none focus:border-slate-350 focus:bg-white focus:ring-1 focus:ring-indigo-500 transition-all placeholder:text-slate-400"
            />
          </div>
        </div>

        <!-- Inventory List Table -->
        <div class="border border-slate-100 dark:border-slate-800 rounded-2xl overflow-hidden">
          <div v-if="inventoryLoading" class="text-center py-20">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600 mx-auto"></div>
            <p class="mt-3 text-slate-500 text-xs font-bold uppercase tracking-wider">Loading Inventory Data...</p>
          </div>

          <div v-else-if="inventoryItems.length === 0" class="text-center py-16 px-4">
            <div class="w-16 h-16 bg-slate-50 dark:bg-slate-850 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100 dark:border-slate-800">
              <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
            <h3 class="text-base font-bold text-slate-800 dark:text-white">No items found</h3>
            <p class="text-xs text-slate-450 mt-1 max-w-xs mx-auto">There are no inventory items matching the current search parameters.</p>
          </div>

          <table v-else class="w-full table-auto border-collapse text-xs font-medium">
            <thead>
              <tr class="bg-slate-50 dark:bg-slate-850 border-b border-slate-150 dark:border-slate-800 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                <th class="px-6 py-4 text-left">Item Code</th>
                <th class="px-6 py-4 text-left">Item Name</th>
                <th class="px-6 py-4 text-left">Category</th>
                <th class="px-6 py-4 text-left">Variation / SKU</th>
                <th class="px-6 py-4 text-center">Available Quantity</th>
                <th class="px-6 py-4 text-right">Price</th>
                <th class="px-6 py-4 text-center">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr v-for="item in inventoryItems" :key="item.id" class="hover:bg-slate-50/40">
                <!-- Item Code -->
                <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                  {{ item.variation?.barcode || item.product?.barcode || item.variation?.sku || item.product?.sku || 'N/A' }}
                </td>
                <!-- Item Name -->
                <td class="px-6 py-4 font-bold text-slate-800 dark:text-slate-200">
                  {{ item.product?.name }}
                </td>
                <!-- Category -->
                <td class="px-6 py-4 text-slate-600 dark:text-slate-350">
                  {{ item.product?.category?.name || 'Uncategorized' }}
                </td>
                <!-- Variation / SKU -->
                <td class="px-6 py-4">
                  <span v-if="item.variation" class="font-bold text-indigo-650 bg-indigo-50 dark:bg-indigo-950/20 dark:text-indigo-400 px-2 py-0.5 rounded text-[10px]">
                    {{ item.variation.variation_name_string }}
                  </span>
                  <div class="text-[10px] text-slate-400 mt-0.5">SKU: {{ item.variation?.sku || item.product?.sku || 'N/A' }}</div>
                </td>
                <!-- Available Qty -->
                <td class="px-6 py-4 text-center">
                  <span class="font-bold px-2.5 py-1 rounded-full text-xs" :class="item.stock_qty <= item.min_stock_level ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400' : 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200'">
                    {{ item.stock_qty }}
                  </span>
                </td>
                <!-- Price -->
                <td class="px-6 py-4 text-right font-bold text-slate-900 dark:text-white">
                  ${{ parseFloat(item.variation?.retail_price || item.product?.selling_price || 0).toFixed(2) }}
                </td>
                <!-- Status -->
                <td class="px-6 py-4 text-center">
                  <span v-if="item.stock_qty <= 0" class="px-2 py-0.5 text-[9px] font-extrabold uppercase bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400 rounded-full">
                    Out of Stock
                  </span>
                  <span v-else-if="item.stock_qty <= item.min_stock_level" class="px-2 py-0.5 text-[9px] font-extrabold uppercase bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 rounded-full">
                    Low Stock
                  </span>
                  <span v-else class="px-2 py-0.5 text-[9px] font-extrabold uppercase bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 rounded-full">
                    In Stock
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Dynamic Pagination Controls -->
        <div v-if="inventoryPagination.total > 0" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-4 border-t border-slate-100 dark:border-slate-850">
          <div class="text-xs font-bold text-slate-400">
            Showing {{ (inventoryPagination.current_page - 1) * inventoryPagination.per_page + 1 }} to {{ Math.min(inventoryPagination.current_page * inventoryPagination.per_page, inventoryPagination.total) }} of {{ inventoryPagination.total }} entries
          </div>

          <div class="flex items-center gap-1.5 self-center sm:self-auto">
            <!-- Prev page -->
            <button 
              type="button"
              :disabled="inventoryPagination.current_page === 1"
              @click="fetchInventory(inventoryPagination.current_page - 1)"
              class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-850 text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:hover:bg-transparent text-xs font-bold transition-all focus:outline-none cursor-pointer"
            >
              Previous
            </button>

            <!-- Page numbers -->
            <button 
              v-for="p in inventoryPagination.last_page"
              :key="p"
              type="button"
              @click="fetchInventory(p)"
              class="w-8 h-8 rounded-lg text-xs font-bold transition-all focus:outline-none cursor-pointer"
              :class="inventoryPagination.current_page === p ? 'bg-indigo-650 text-white shadow-sm' : 'border border-slate-200 dark:border-slate-850 text-slate-600 hover:bg-slate-50'"
            >
              {{ p }}
            </button>

            <!-- Next page -->
            <button 
              type="button"
              :disabled="inventoryPagination.current_page === inventoryPagination.last_page"
              @click="fetchInventory(inventoryPagination.current_page + 1)"
              class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-850 text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:hover:bg-transparent text-xs font-bold transition-all focus:outline-none cursor-pointer"
            >
              Next
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- Warehouse Details View Modal -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="showDetailsModal = false"></div>

        <div class="relative bg-white dark:bg-slate-900 w-full max-w-lg p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10 animate-in fade-in zoom-in-95 duration-200 text-xs">
          <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-extrabold text-slate-800 dark:text-white uppercase tracking-wider">
              Warehouse Details
            </h3>
            <button @click="showDetailsModal = false" class="text-slate-400 hover:text-slate-650 dark:hover:text-slate-200 font-bold text-lg focus:outline-none">&times;</button>
          </div>

          <div class="space-y-4 divide-y divide-slate-100 dark:divide-slate-850">
            <!-- Basic Details -->
            <div class="grid grid-cols-2 gap-4 pb-3">
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">Warehouse Name</label>
                <div class="font-bold text-slate-900 dark:text-white text-sm">{{ detailWarehouse?.name }}</div>
              </div>
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">Warehouse Code</label>
                <div class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ detailWarehouse?.code || 'N/A' }}</div>
              </div>
            </div>

            <!-- Address -->
            <div class="py-3">
              <label class="text-[10px] font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">Physical Address</label>
              <div class="font-medium text-slate-700 dark:text-slate-300 leading-relaxed">{{ detailWarehouse?.address || 'No address provided.' }}</div>
            </div>

            <!-- Contact Details -->
            <div class="grid grid-cols-2 gap-4 py-3">
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">Phone</label>
                <div class="font-bold text-slate-800 dark:text-slate-200">{{ detailWarehouse?.phone || 'N/A' }}</div>
              </div>
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">Email</label>
                <div class="font-bold text-slate-800 dark:text-slate-200">{{ detailWarehouse?.email || 'N/A' }}</div>
              </div>
            </div>

            <!-- Status Configuration -->
            <div class="grid grid-cols-2 gap-4 py-3">
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">Operational Status</label>
                <div>
                  <span 
                    :class="[
                      'px-2 py-0.5 text-[9px] font-extrabold uppercase rounded-full inline-block mt-1',
                      detailWarehouse?.is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400'
                    ]"
                  >
                    {{ detailWarehouse?.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </div>
              </div>
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">Default Status</label>
                <div>
                  <span 
                    :class="[
                      'px-2 py-0.5 text-[9px] font-extrabold uppercase rounded-full inline-block mt-1',
                      detailWarehouse?.is_default ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-450'
                    ]"
                  >
                    {{ detailWarehouse?.is_default ? 'Primary (Default)' : 'Secondary' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- System Info -->
            <div class="grid grid-cols-2 gap-4 pt-3 text-[11px]">
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">Created At</label>
                <div class="font-medium text-slate-500">{{ detailWarehouse?.created_at ? new Date(detailWarehouse.created_at).toLocaleString() : 'N/A' }}</div>
              </div>
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">Last Modified</label>
                <div class="font-medium text-slate-500">{{ detailWarehouse?.updated_at ? new Date(detailWarehouse.updated_at).toLocaleString() : 'N/A' }}</div>
              </div>
            </div>
          </div>

          <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
            <button 
              type="button" 
              @click="showDetailsModal = false" 
              class="px-5 py-2.5 bg-slate-850 hover:bg-slate-900 text-white font-bold rounded-xl shadow transition-all cursor-pointer"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Warehouse Create/Edit Modal -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="closeModal"></div>

        <div class="relative bg-white dark:bg-slate-900 w-full max-w-md p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10 animate-in fade-in zoom-in-95 duration-200">
          <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-extrabold text-slate-800 dark:text-white uppercase tracking-wider">
              {{ isEditing ? 'Edit Warehouse' : 'New Warehouse' }}
            </h3>
            <button @click="closeModal" class="text-slate-400 hover:text-slate-650 dark:hover:text-slate-200 font-bold text-lg focus:outline-none">&times;</button>
          </div>

          <div v-if="modalError" class="p-3 bg-red-50 text-red-700 text-xs rounded-xl border border-red-200 font-medium">
            {{ modalError }}
          </div>

          <form @submit.prevent="submitWarehouse" class="space-y-4 text-xs font-medium">
            <div>
              <label class="text-[10px] font-bold text-slate-400 block mb-1 uppercase tracking-wider">Warehouse Name *</label>
              <input 
                v-model="modalForm.name"
                type="text" 
                placeholder="e.g., Secondary Outlet, Online Depot" 
                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-850 dark:text-white border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-slate-350 focus:bg-white focus:ring-1 focus:ring-indigo-500"
                required
              />
            </div>

            <div>
              <label class="text-[10px] font-bold text-slate-400 block mb-1 uppercase tracking-wider">Code / Short Code</label>
              <input 
                v-model="modalForm.code"
                type="text" 
                placeholder="e.g., WH2" 
                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-850 dark:text-white border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-slate-350 focus:bg-white focus:ring-1 focus:ring-indigo-500"
              />
            </div>

            <div>
              <label class="text-[10px] font-bold text-slate-400 block mb-1 uppercase tracking-wider">Physical Address</label>
              <textarea 
                v-model="modalForm.address"
                rows="2"
                placeholder="123 Storage Road, Suite A" 
                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-850 dark:text-white border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-slate-350 focus:bg-white focus:ring-1 focus:ring-indigo-500"
              ></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-1 uppercase tracking-wider">Phone</label>
                <input 
                  v-model="modalForm.phone"
                  type="text" 
                  placeholder="+1 (555) 123-4567" 
                  class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-850 dark:text-white border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-slate-350 focus:bg-white focus:ring-1 focus:ring-indigo-500"
                />
              </div>
              <div>
                <label class="text-[10px] font-bold text-slate-400 block mb-1 uppercase tracking-wider">Email</label>
                <input 
                  v-model="modalForm.email"
                  type="email" 
                  placeholder="wh2@company.com" 
                  class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-850 dark:text-white border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-slate-350 focus:bg-white focus:ring-1 focus:ring-indigo-500"
                />
              </div>
            </div>

            <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-850 p-3 rounded-2xl border border-slate-200/50 dark:border-slate-800/80">
              <div>
                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-200">Default Warehouse</span>
                <p class="text-[9px] text-slate-400 mt-0.5">Use as primary selection on POS checkouts</p>
              </div>
              <input 
                v-model="modalForm.is_default"
                type="checkbox" 
                class="w-4.5 h-4.5 rounded border-slate-300 text-indigo-650 focus:ring-indigo-500 cursor-pointer"
              />
            </div>

            <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-850 p-3 rounded-2xl border border-slate-200/50 dark:border-slate-800/80">
              <div>
                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-200">Is Active</span>
                <p class="text-[9px] text-slate-400 mt-0.5">Deactivate to temporarily lock warehouse stock changes</p>
              </div>
              <input 
                v-model="modalForm.is_active"
                type="checkbox" 
                class="w-4.5 h-4.5 rounded border-slate-300 text-indigo-650 focus:ring-indigo-500 cursor-pointer"
              />
            </div>

            <div class="flex justify-end gap-2 pt-2">
              <button 
                type="button" 
                @click="closeModal" 
                class="px-4 py-2.5 text-slate-500 dark:text-slate-400 font-bold hover:text-slate-700 dark:hover:text-slate-200 cursor-pointer"
              >
                Cancel
              </button>
              <button 
                type="submit" 
                :disabled="submitting" 
                class="px-5 py-2.5 bg-indigo-650 hover:bg-indigo-700 text-white font-bold rounded-xl shadow transition-all flex items-center gap-1.5 disabled:opacity-50 cursor-pointer"
              >
                <svg v-if="submitting" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Save Warehouse
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// View State toggling
const currentView = ref('list'); // 'list' or 'inventory'

const warehouses = ref([]);
const loading = ref(false);
const submitting = ref(false);

// Dedicated Warehouse Inventory state
const activeWarehouse = ref(null);
const inventoryItems = ref([]);
const inventoryPagination = ref({ current_page: 1, last_page: 1, total: 0, per_page: 10 });
const inventorySearch = ref('');
const inventoryPerPage = ref(10);
const inventoryLoading = ref(false);

// View details Modal
const showDetailsModal = ref(false);
const detailWarehouse = ref(null);

// Feedback notifications
const feedbackMsg = ref('');
const feedbackClass = ref('');

// Modal Configuration for Create/Edit
const showModal = ref(false);
const isEditing = ref(false);
const editId = ref(null);
const modalError = ref('');
const modalForm = ref({
  name: '',
  code: '',
  address: '',
  phone: '',
  email: '',
  is_default: false,
  is_active: true
});

const fetchWarehouses = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/warehouses');
    warehouses.value = res.data || [];
  } catch (err) {
    console.error('Failed to load warehouses:', err);
    showFeedback('Failed to load warehouses.', 'bg-rose-50 text-rose-800 border-rose-200');
  } finally {
    loading.value = false;
  }
};

// Open dynamic warehouse details modal
const showWarehouseDetails = (wh) => {
  detailWarehouse.value = wh;
  showDetailsModal.value = true;
};

// Switch view to dedicated inventory view
const showInventoryView = (wh) => {
  activeWarehouse.value = wh;
  inventorySearch.value = '';
  inventoryPerPage.value = 10;
  currentView.value = 'inventory';
  fetchInventory(1);
};

// AJAX load inventory with pagination & live search
const fetchInventory = async (page = 1) => {
  if (!activeWarehouse.value) return;
  inventoryLoading.value = true;
  try {
    const res = await axios.get(`/api/warehouses/${activeWarehouse.value.id}/inventory`, {
      params: {
        page,
        per_page: inventoryPerPage.value,
        search: inventorySearch.value
      }
    });
    inventoryItems.value = res.data.data || [];
    inventoryPagination.value = {
      current_page: res.data.current_page || 1,
      last_page: res.data.last_page || 1,
      total: res.data.total || 0,
      per_page: res.data.per_page || 10
    };
  } catch (err) {
    console.error('Failed to load warehouse inventory:', err);
    inventoryItems.value = [];
  } finally {
    inventoryLoading.value = false;
  }
};

// Live search debouncing to prevent spamming requests
let searchTimeout = null;
const onSearchInput = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchInventory(1);
  }, 350);
};

const openCreateModal = () => {
  isEditing.value = false;
  editId.value = null;
  modalForm.value = {
    name: '',
    code: '',
    address: '',
    phone: '',
    email: '',
    is_default: false,
    is_active: true
  };
  modalError.value = '';
  showModal.value = true;
};

const openEditModal = (wh) => {
  isEditing.value = true;
  editId.value = wh.id;
  modalForm.value = {
    name: wh.name,
    code: wh.code || '',
    address: wh.address || '',
    phone: wh.phone || '',
    email: wh.email || '',
    is_default: !!wh.is_default,
    is_active: !!wh.is_active
  };
  modalError.value = '';
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const submitWarehouse = async () => {
  if (!modalForm.value.name.trim()) {
    modalError.value = 'Name is required.';
    return;
  }

  submitting.value = true;
  modalError.value = '';

  try {
    if (isEditing.value) {
      await axios.put(`/api/warehouses/${editId.value}`, modalForm.value);
      showFeedback('Warehouse updated successfully.', 'bg-emerald-50 text-emerald-800 border-emerald-200');
    } else {
      await axios.post('/api/warehouses', modalForm.value);
      showFeedback('Warehouse created successfully.', 'bg-emerald-50 text-emerald-800 border-emerald-200');
    }
    closeModal();
    fetchWarehouses();
  } catch (err) {
    console.error(err);
    if (err.response?.status === 422) {
      modalError.value = err.response.data.message || 'Validation error.';
    } else {
      modalError.value = 'Failed to save warehouse information.';
    }
  } finally {
    submitting.value = false;
  }
};

const deleteWarehouse = async (id) => {
  if (!confirm('Are you sure you want to delete this warehouse? This action cannot be undone if it has no stock historical logs.')) return;
  try {
    const res = await axios.delete(`/api/warehouses/${id}`);
    showFeedback(res.data.message || 'Warehouse deleted.', 'bg-emerald-50 text-emerald-800 border-emerald-200');
    fetchWarehouses();
  } catch (err) {
    console.error(err);
    const msg = err.response?.data?.message || 'Failed to delete warehouse.';
    showFeedback(msg, 'bg-rose-50 text-rose-800 border-rose-200');
  }
};

const showFeedback = (msg, cls) => {
  feedbackMsg.value = msg;
  feedbackClass.value = cls;
  setTimeout(() => {
    if (feedbackMsg.value === msg) {
      feedbackMsg.value = '';
    }
  }, 4000);
};

onMounted(() => {
  fetchWarehouses();
});
</script>
