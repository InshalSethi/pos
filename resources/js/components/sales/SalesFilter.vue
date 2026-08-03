<template>
  <teleport to="body">
    <!-- Backdrop -->
    <transition
      enter-active-class="transition-opacity ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen"
        @click="close"
        class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs"
      ></div>
    </transition>

    <!-- Slide-over Drawer -->
    <transition
      enter-active-class="transition transform ease-out duration-300"
      enter-from-class="translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transition transform ease-in duration-200"
      leave-from-class="translate-x-0"
      leave-to-class="translate-x-full"
    >
      <div
        v-if="isOpen"
        class="fixed inset-y-0 right-0 z-50 w-full max-w-md bg-white dark:bg-zinc-900 shadow-2xl border-l border-slate-200 dark:border-zinc-800 flex flex-col justify-between overflow-hidden"
      >
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between bg-slate-50/50 dark:bg-zinc-900/50">
          <div class="flex items-center space-x-2.5">
            <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 8.293A1 1 0 013 7.586V4z" />
              </svg>
            </div>
            <div>
              <h2 class="text-base font-bold text-slate-900 dark:text-zinc-100 tracking-tight">Advanced Filter</h2>
              <p class="text-xs text-slate-500 dark:text-zinc-400">Refine invoices by product, warehouse, sales rep, counter & status</p>
            </div>
          </div>
          <button
            @click="close"
            class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors focus:outline-none cursor-pointer"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Body Form -->
        <div class="flex-1 overflow-y-auto p-6 space-y-5 custom-scrollbar" @click="closeAllPopovers">
          <!-- Active Filters Count Badge -->
          <div v-if="activeFilterCount > 0" class="flex items-center justify-between px-3 py-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/40 rounded-lg text-xs">
            <span class="text-blue-700 dark:text-blue-300 font-medium">
              <strong class="font-bold">{{ activeFilterCount }}</strong> active filter{{ activeFilterCount > 1 ? 's' : '' }} applied
            </span>
            <button
              @click="resetFilters"
              class="text-blue-600 dark:text-blue-400 hover:underline font-semibold cursor-pointer"
            >
              Reset All
            </button>
          </div>

          <!-- 1. Top 20 Interactive Product / Item Dropdown -->
          <div class="space-y-1.5 relative" @click.stop>
            <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
              Product / Line Item (Top 20)
            </label>
            <div class="relative">
              <button
                type="button"
                @click="togglePopover('product')"
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer"
              >
                <div class="flex items-center space-x-2 truncate pr-2">
                  <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                  </svg>
                  <span class="truncate" :class="{ 'text-slate-400 dark:text-zinc-500': !selectedProductLabel }">
                    {{ selectedProductLabel || 'Select a Top Selling Product' }}
                  </span>
                </div>
                <div class="flex items-center space-x-1">
                  <button
                    v-if="localFilters.product_id || localFilters.product_search"
                    @click.stop="clearProduct"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 p-0.5"
                    title="Clear selected product"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                  </button>
                  <svg class="w-4 h-4 text-slate-400 transition-transform" :class="{ 'rotate-180': activePopover === 'product' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </div>
              </button>

              <!-- Floating Product Popover Dropdown (No Search Field, Top 20) -->
              <div
                v-if="activePopover === 'product'"
                class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-xl py-1 max-h-64 overflow-y-auto custom-scrollbar animate-fade-in"
              >
                <div class="px-3 py-1.5 border-b border-slate-100 dark:border-zinc-800 text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider flex justify-between items-center">
                  <span>Top 20 Frequently Sold Items</span>
                  <span v-if="topProducts.length > 0" class="text-blue-600 dark:text-blue-400">{{ topProducts.length }} available</span>
                </div>

                <div v-if="loadingTopProducts" class="py-6 text-center text-slate-400 text-xs">
                  <div class="animate-spin rounded-full h-5 w-5 border-2 border-slate-300 border-t-blue-600 mx-auto mb-1"></div>
                  Loading top products...
                </div>
                <div v-else-if="topProducts.length === 0" class="py-4 text-center text-slate-400 text-xs italic">
                  No products available.
                </div>
                <div v-else>
                  <button
                    type="button"
                    @click="selectProduct(null)"
                    class="w-full px-3 py-2 text-left text-xs font-semibold hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center justify-between text-slate-500 dark:text-zinc-400"
                    :class="{ 'bg-blue-50/50 dark:bg-blue-900/30 text-blue-600': !localFilters.product_id && !localFilters.product_search }"
                  >
                    <span>All Products / No Filter</span>
                    <svg v-if="!localFilters.product_id && !localFilters.product_search" class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                  </button>
                  <button
                    v-for="p in topProducts"
                    :key="p.id"
                    type="button"
                    @click="selectProduct(p)"
                    class="w-full px-3 py-2 text-left text-xs hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center justify-between space-x-2 border-t border-slate-50 dark:border-zinc-800/60 cursor-pointer"
                    :class="{ 'bg-blue-50/80 dark:bg-blue-900/40 font-semibold text-blue-700 dark:text-blue-300': String(localFilters.product_id) === String(p.id) }"
                  >
                    <div class="flex items-center space-x-2.5 truncate">
                      <div class="w-6 h-6 rounded bg-slate-100 dark:bg-zinc-800 flex items-center justify-center shrink-0 text-slate-400 font-bold text-[10px]">
                        <img v-if="p.image" :src="p.image" class="w-6 h-6 rounded object-cover" />
                        <span v-else>{{ p.name ? p.name.charAt(0).toUpperCase() : 'P' }}</span>
                      </div>
                      <div class="truncate">
                        <div class="font-medium text-slate-800 dark:text-zinc-100 truncate">{{ p.name }}</div>
                        <div class="text-[10px] text-slate-400 dark:text-zinc-500">SKU: {{ p.sku || 'N/A' }}</div>
                      </div>
                    </div>
                    <span v-if="p.selling_price" class="text-[11px] font-bold text-slate-600 dark:text-zinc-300 shrink-0">
                      ${{ parseFloat(p.selling_price).toFixed(2) }}
                    </span>
                  </button>
                </div>
              </div>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-zinc-500">Select from the top 20 items to isolate invoices containing that product.</p>
          </div>

          <!-- 2. Multi-Select Floating Dropdown: Warehouse / Shop Location -->
          <div class="space-y-1.5 relative" @click.stop>
            <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
              Warehouse / Shop Location (Multi-Select)
            </label>
            <div class="relative">
              <button
                type="button"
                @click="togglePopover('warehouse')"
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer"
              >
                <div class="flex items-center space-x-2 truncate pr-2">
                  <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5"/></svg>
                  <span class="truncate" :class="{ 'text-slate-400 dark:text-zinc-500': localFilters.warehouse_ids.length === 0 }">
                    {{ warehouseSummaryLabel }}
                  </span>
                </div>
                <div class="flex items-center space-x-1">
                  <span v-if="localFilters.warehouse_ids.length > 0" class="text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 px-1.5 py-0.2 rounded-full">
                    {{ localFilters.warehouse_ids.length }}
                  </span>
                  <svg class="w-4 h-4 text-slate-400 transition-transform" :class="{ 'rotate-180': activePopover === 'warehouse' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </div>
              </button>

              <!-- Floating Multi-Select Popover (No Search Field) -->
              <div
                v-if="activePopover === 'warehouse'"
                class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-xl py-1 max-h-60 overflow-y-auto custom-scrollbar animate-fade-in"
              >
                <div class="px-3 py-1.5 border-b border-slate-100 dark:border-zinc-800 flex justify-between items-center text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase">
                  <span>Select Warehouses</span>
                  <button @click="localFilters.warehouse_ids = []" class="text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">Clear</button>
                </div>
                <div v-if="warehouses.length === 0" class="py-4 text-center text-slate-400 text-xs italic">
                  No warehouses found.
                </div>
                <label
                  v-for="wh in warehouses"
                  :key="wh.id"
                  class="px-3 py-2 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center space-x-2.5 cursor-pointer text-xs select-none border-b border-slate-50 dark:border-zinc-800/40"
                >
                  <input
                    type="checkbox"
                    :value="wh.id"
                    v-model="localFilters.warehouse_ids"
                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-4 h-4"
                  />
                  <span class="font-medium text-slate-800 dark:text-zinc-200">
                    {{ wh.name }} {{ wh.code ? `(${wh.code})` : '' }} {{ wh.is_default ? '★' : '' }}
                  </span>
                </label>
              </div>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-zinc-500">Multi-warehouse isolation; filters line items and counter options below.</p>
          </div>

          <!-- 3. Multi-Select Floating Dropdown: POS Counter (Cascading) -->
          <div class="space-y-1.5 relative" @click.stop>
            <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
              POS Counter (Multi-Select)
            </label>
            <div class="relative">
              <button
                type="button"
                @click="togglePopover('counter')"
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer"
              >
                <div class="flex items-center space-x-2 truncate pr-2">
                  <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                  <span class="truncate" :class="{ 'text-slate-400 dark:text-zinc-500': localFilters.counter_ids.length === 0 }">
                    {{ counterSummaryLabel }}
                  </span>
                </div>
                <div class="flex items-center space-x-1">
                  <span v-if="localFilters.counter_ids.length > 0" class="text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 px-1.5 py-0.2 rounded-full">
                    {{ localFilters.counter_ids.length }}
                  </span>
                  <svg class="w-4 h-4 text-slate-400 transition-transform" :class="{ 'rotate-180': activePopover === 'counter' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </div>
              </button>

              <!-- Floating Multi-Select Popover (No Search Field, Cascaded) -->
              <div
                v-if="activePopover === 'counter'"
                class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-xl py-1 max-h-60 overflow-y-auto custom-scrollbar animate-fade-in"
              >
                <div class="px-3 py-1.5 border-b border-slate-100 dark:border-zinc-800 flex justify-between items-center text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase">
                  <span>
                    {{ localFilters.warehouse_ids.length > 0 ? 'Filtered Counters' : 'All Counters' }}
                  </span>
                  <button @click="localFilters.counter_ids = []" class="text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">Clear</button>
                </div>
                <div v-if="filteredCounters.length === 0" class="py-4 text-center text-slate-400 text-xs italic px-3">
                  No counters available for the selected warehouse(s).
                </div>
                <label
                  v-for="c in filteredCounters"
                  :key="c.id"
                  class="px-3 py-2 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center space-x-2.5 cursor-pointer text-xs select-none border-b border-slate-50 dark:border-zinc-800/40"
                >
                  <input
                    type="checkbox"
                    :value="c.id"
                    v-model="localFilters.counter_ids"
                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-4 h-4"
                  />
                  <span class="font-medium text-slate-800 dark:text-zinc-200">
                    {{ c.name }} {{ c.counter_number ? `(#${c.counter_number})` : '' }}
                    <span v-if="c.warehouse?.name" class="text-slate-400 text-[10px]">({{ c.warehouse.name }})</span>
                  </span>
                </label>
              </div>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-zinc-500">Cascades based on selected warehouse location above.</p>
          </div>

          <!-- 4. Multi-Select Floating Dropdown: Sales Representative -->
          <div class="space-y-1.5 relative" @click.stop>
            <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
              Sales Representative / Salesman (Multi-Select)
            </label>
            <div class="relative">
              <button
                type="button"
                @click="togglePopover('salesman')"
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer"
              >
                <div class="flex items-center space-x-2 truncate pr-2">
                  <svg class="w-4 h-4 text-purple-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                  <span class="truncate" :class="{ 'text-slate-400 dark:text-zinc-500': localFilters.salesman_ids.length === 0 }">
                    {{ salesmanSummaryLabel }}
                  </span>
                </div>
                <div class="flex items-center space-x-1">
                  <span v-if="localFilters.salesman_ids.length > 0" class="text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 px-1.5 py-0.2 rounded-full">
                    {{ localFilters.salesman_ids.length }}
                  </span>
                  <svg class="w-4 h-4 text-slate-400 transition-transform" :class="{ 'rotate-180': activePopover === 'salesman' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </div>
              </button>

              <!-- Floating Multi-Select Popover (No Search Field) -->
              <div
                v-if="activePopover === 'salesman'"
                class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-xl py-1 max-h-60 overflow-y-auto custom-scrollbar animate-fade-in"
              >
                <div class="px-3 py-1.5 border-b border-slate-100 dark:border-zinc-800 flex justify-between items-center text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase">
                  <span>Select Sales Representatives</span>
                  <button @click="localFilters.salesman_ids = []" class="text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">Clear</button>
                </div>
                <div v-if="salesmen.length === 0" class="py-4 text-center text-slate-400 text-xs italic">
                  No sales reps found.
                </div>
                <label
                  v-for="emp in salesmen"
                  :key="emp.id"
                  class="px-3 py-2 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center space-x-2.5 cursor-pointer text-xs select-none border-b border-slate-50 dark:border-zinc-800/40"
                >
                  <input
                    type="checkbox"
                    :value="emp.id"
                    v-model="localFilters.salesman_ids"
                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-4 h-4"
                  />
                  <span class="font-medium text-slate-800 dark:text-zinc-200">
                    {{ emp.full_name }}
                  </span>
                </label>
              </div>
            </div>
          </div>

          <!-- 5. Multi-Select Floating Dropdown: Status Filter Addition -->
          <div class="space-y-1.5 relative" @click.stop>
            <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
              Invoice Status (Multi-Select)
            </label>
            <div class="relative">
              <button
                type="button"
                @click="togglePopover('status')"
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer"
              >
                <div class="flex items-center space-x-2 truncate pr-2">
                  <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  <span class="truncate" :class="{ 'text-slate-400 dark:text-zinc-500': localFilters.statuses.length === 0 }">
                    {{ statusSummaryLabel }}
                  </span>
                </div>
                <div class="flex items-center space-x-1">
                  <span v-if="localFilters.statuses.length > 0" class="text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 px-1.5 py-0.2 rounded-full">
                    {{ localFilters.statuses.length }}
                  </span>
                  <svg class="w-4 h-4 text-slate-400 transition-transform" :class="{ 'rotate-180': activePopover === 'status' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </div>
              </button>

              <!-- Floating Multi-Select Popover (No Search Field) -->
              <div
                v-if="activePopover === 'status'"
                class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-xl py-1 max-h-60 overflow-y-auto custom-scrollbar animate-fade-in"
              >
                <div class="px-3 py-1.5 border-b border-slate-100 dark:border-zinc-800 flex justify-between items-center text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase">
                  <span>Select Invoice Statuses</span>
                  <button @click="localFilters.statuses = []" class="text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">Clear</button>
                </div>
                <label
                  v-for="st in availableStatuses"
                  :key="st.id"
                  class="px-3 py-2 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center space-x-2.5 cursor-pointer text-xs select-none border-b border-slate-50 dark:border-zinc-800/40"
                >
                  <input
                    type="checkbox"
                    :value="st.id"
                    v-model="localFilters.statuses"
                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-4 h-4"
                  />
                  <span class="font-semibold" :class="st.colorClass">
                    {{ st.label }}
                  </span>
                </label>
              </div>
            </div>
          </div>

          <!-- Date Range Section -->
          <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-zinc-800">
            <div class="flex items-center justify-between">
              <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
                Date Range
              </label>
              <div class="flex gap-1.5">
                <button
                  type="button"
                  @click="setQuickDate('today')"
                  class="px-2 py-0.5 text-[10px] font-semibold bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-600 dark:text-zinc-300 rounded transition-colors"
                >
                  Today
                </button>
                <button
                  type="button"
                  @click="setQuickDate('this_month')"
                  class="px-2 py-0.5 text-[10px] font-semibold bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-600 dark:text-zinc-300 rounded transition-colors"
                >
                  This Month
                </button>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <span class="block text-[10px] text-slate-400 dark:text-zinc-500 font-semibold mb-1">From Date</span>
                <input
                  v-model="localFilters.date_from"
                  type="date"
                  class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                />
              </div>
              <div>
                <span class="block text-[10px] text-slate-400 dark:text-zinc-500 font-semibold mb-1">To Date</span>
                <input
                  v-model="localFilters.date_to"
                  type="date"
                  class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="p-6 border-t border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50 flex items-center justify-between space-x-3">
          <button
            type="button"
            @click="resetFilters"
            class="px-4 py-2.5 text-xs font-semibold text-slate-600 dark:text-zinc-400 hover:text-slate-800 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
          >
            Reset Filters
          </button>
          <div class="flex items-center space-x-2">
            <button
              type="button"
              @click="close"
              class="px-4 py-2.5 text-xs font-semibold text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="button"
              @click="applyFilters"
              class="px-5 py-2.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 active:scale-95 rounded-lg shadow-sm transition-all flex items-center space-x-1.5 cursor-pointer"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <span>Apply Filters</span>
            </button>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  filters: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['update:isOpen', 'apply', 'reset']);

// Active Popover Name: 'product' | 'warehouse' | 'counter' | 'salesman' | 'status' | null
const activePopover = ref(null);

const togglePopover = (name) => {
  if (activePopover.value === name) {
    activePopover.value = null;
  } else {
    activePopover.value = name;
  }
};

const closeAllPopovers = () => {
  activePopover.value = null;
};

// Local state for multi-select & popover filters
const localFilters = ref({
  product_id: '',
  product_name: '',
  product_search: '',
  warehouse_ids: [],
  counter_ids: [],
  salesman_ids: [],
  statuses: [],
  date_from: '',
  date_to: ''
});

// Lookups Data
const topProducts = ref([]);
const warehouses = ref([]);
const salesmen = ref([]);
const counters = ref([]);
const loadingTopProducts = ref(false);
const loadingDropdowns = ref(false);

const availableStatuses = [
  { id: 'completed', label: 'Paid / Completed', colorClass: 'text-emerald-600 dark:text-emerald-400' },
  { id: 'pending', label: 'Due / Pending', colorClass: 'text-orange-600 dark:text-orange-400' },
  { id: 'overdue', label: 'Overdue', colorClass: 'text-rose-600 dark:text-rose-400' },
  { id: 'draft', label: 'Draft', colorClass: 'text-slate-600 dark:text-zinc-400' },
  { id: 'recurring', label: 'Recurring', colorClass: 'text-blue-600 dark:text-blue-400' },
  { id: 'void', label: 'Void / Cancelled', colorClass: 'text-slate-500 dark:text-zinc-500' },
];

// Active Filter Count
const activeFilterCount = computed(() => {
  let count = 0;
  if (localFilters.value.product_id || localFilters.value.product_search) count++;
  if (localFilters.value.warehouse_ids && localFilters.value.warehouse_ids.length > 0) count++;
  if (localFilters.value.counter_ids && localFilters.value.counter_ids.length > 0) count++;
  if (localFilters.value.salesman_ids && localFilters.value.salesman_ids.length > 0) count++;
  if (localFilters.value.statuses && localFilters.value.statuses.length > 0) count++;
  if (localFilters.value.date_from || localFilters.value.date_to) count++;
  return count;
});

// Cascading Warehouses -> Counters filter
const filteredCounters = computed(() => {
  const list = Array.isArray(counters.value) ? counters.value : [];
  if (!localFilters.value.warehouse_ids || localFilters.value.warehouse_ids.length === 0) {
    return list;
  }
  const selectedWhs = localFilters.value.warehouse_ids.map(id => String(id));
  return list.filter(c => c.warehouse_id && selectedWhs.includes(String(c.warehouse_id)));
});

const fetchCounters = async (whIds = []) => {
  try {
    const params = {};
    if (whIds && whIds.length > 0) {
      params.warehouse_ids = whIds;
      params.warehouse_id = whIds.join(',');
    }
    const cntRes = await axios.get('/api/counters', { params });
    counters.value = extractArray(cntRes.data);
  } catch (err) {
    console.error('Failed to fetch counters for selected warehouses:', err);
  }
};

// Watch warehouse selection to query counters and purge non-matching counter IDs
watch(() => [...localFilters.value.warehouse_ids], async (newWhs) => {
  await fetchCounters(newWhs);
  if (localFilters.value.counter_ids && localFilters.value.counter_ids.length > 0) {
    const validCounterIds = counters.value.map(c => String(c.id));
    localFilters.value.counter_ids = localFilters.value.counter_ids.filter(id => validCounterIds.includes(String(id)));
  }
}, { deep: true, immediate: true });

// UI Labels for Button Triggers
const selectedProductLabel = computed(() => {
  if (localFilters.value.product_name) return localFilters.value.product_name;
  if (localFilters.value.product_search) return localFilters.value.product_search;
  if (localFilters.value.product_id) {
    const p = topProducts.value.find(item => String(item.id) === String(localFilters.value.product_id));
    return p ? p.name : `Product #${localFilters.value.product_id}`;
  }
  return '';
});

const warehouseSummaryLabel = computed(() => {
  const ids = localFilters.value.warehouse_ids || [];
  if (ids.length === 0) return 'All Warehouses / Shops';
  if (ids.length === 1) {
    const wh = warehouses.value.find(w => String(w.id) === String(ids[0]));
    return wh ? wh.name : `Warehouse #${ids[0]}`;
  }
  const firstWh = warehouses.value.find(w => String(w.id) === String(ids[0]));
  const firstName = firstWh ? firstWh.name : `Shop #${ids[0]}`;
  return `${firstName} (+${ids.length - 1} more)`;
});

const counterSummaryLabel = computed(() => {
  const ids = localFilters.value.counter_ids || [];
  if (ids.length === 0) return 'All POS Counters';
  if (ids.length === 1) {
    const c = counters.value.find(item => String(item.id) === String(ids[0]));
    return c ? c.name : `Counter #${ids[0]}`;
  }
  const firstC = counters.value.find(item => String(item.id) === String(ids[0]));
  const firstName = firstC ? firstC.name : `Counter #${ids[0]}`;
  return `${firstName} (+${ids.length - 1} more)`;
});

const salesmanSummaryLabel = computed(() => {
  const ids = localFilters.value.salesman_ids || [];
  if (ids.length === 0) return 'All Sales Representatives';
  if (ids.length === 1) {
    const emp = salesmen.value.find(item => String(item.id) === String(ids[0]));
    return emp ? emp.full_name : `Rep #${ids[0]}`;
  }
  const firstEmp = salesmen.value.find(item => String(item.id) === String(ids[0]));
  const firstName = firstEmp ? firstEmp.full_name : `Rep #${ids[0]}`;
  return `${firstName} (+${ids.length - 1} more)`;
});

const statusSummaryLabel = computed(() => {
  const list = localFilters.value.statuses || [];
  if (list.length === 0) return 'All Statuses';
  if (list.length === 1) {
    const st = availableStatuses.find(item => item.id === list[0]);
    return st ? st.label : list[0];
  }
  const firstSt = availableStatuses.find(item => item.id === list[0]);
  const firstName = firstSt ? firstSt.label : list[0];
  return `${firstName} (+${list.length - 1} more)`;
});

const syncPropsToLocal = () => {
  const p = props.filters || {};
  
  // Convert incoming scalar / array parameters
  let whs = p.warehouse_ids || p.warehouse_id || [];
  if (!Array.isArray(whs)) whs = String(whs).split(',').filter(Boolean);
  
  let cnts = p.counter_ids || p.counter_id || [];
  if (!Array.isArray(cnts)) cnts = String(cnts).split(',').filter(Boolean);

  let sales = p.salesman_ids || p.salesman_id || [];
  if (!Array.isArray(sales)) sales = String(sales).split(',').filter(Boolean);

  let stList = p.statuses || p.status || [];
  if (!Array.isArray(stList)) stList = String(stList).split(',').filter(Boolean);

  localFilters.value = {
    product_id: p.product_id || '',
    product_name: p.product_name || '',
    product_search: p.product_search || '',
    warehouse_ids: whs.map(id => String(id)),
    counter_ids: cnts.map(id => String(id)),
    salesman_ids: sales.map(id => String(id)),
    statuses: stList,
    date_from: p.date_from || '',
    date_to: p.date_to || ''
  };
};

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    syncPropsToLocal();
    fetchDropdownData();
    fetchTopProducts();
  }
}, { immediate: true });

watch(() => props.filters, () => {
  syncPropsToLocal();
}, { deep: true });

const extractArray = (resData) => {
  if (Array.isArray(resData)) return resData;
  if (resData && Array.isArray(resData.data)) return resData.data;
  return [];
};

const fetchTopProducts = async () => {
  if (topProducts.value.length > 0) return;
  loadingTopProducts.value = true;
  try {
    const res = await axios.get('/api/products', { params: { per_page: 20 } });
    topProducts.value = extractArray(res.data);
  } catch (err) {
    console.error('Failed fetching top products:', err);
  } finally {
    loadingTopProducts.value = false;
  }
};

const fetchDropdownData = async () => {
  if (warehouses.value.length > 0 && salesmen.value.length > 0 && counters.value.length > 0) {
    return;
  }
  loadingDropdowns.value = true;
  try {
    const [whRes, salesRes] = await Promise.all([
      axios.get('/api/warehouses').catch(() => ({ data: [] })),
      axios.get('/api/employees/for-dropdown').catch(() => ({ data: [] })),
    ]);
    warehouses.value = extractArray(whRes.data);
    salesmen.value = extractArray(salesRes.data);
    await fetchCounters(localFilters.value.warehouse_ids);
  } catch (err) {
    console.error('Failed to load filter dropdown data:', err);
  } finally {
    loadingDropdowns.value = false;
  }
};

const selectProduct = (p) => {
  if (!p) {
    localFilters.value.product_id = '';
    localFilters.value.product_name = '';
    localFilters.value.product_search = '';
  } else {
    localFilters.value.product_id = p.id;
    localFilters.value.product_name = p.name;
    localFilters.value.product_search = p.name;
  }
  activePopover.value = null;
};

const clearProduct = () => {
  localFilters.value.product_id = '';
  localFilters.value.product_name = '';
  localFilters.value.product_search = '';
};

const setQuickDate = (preset) => {
  const now = new Date();
  if (preset === 'today') {
    const todayStr = now.toISOString().split('T')[0];
    localFilters.value.date_from = todayStr;
    localFilters.value.date_to = todayStr;
  } else if (preset === 'this_month') {
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
    const todayStr = now.toISOString().split('T')[0];
    localFilters.value.date_from = firstDay;
    localFilters.value.date_to = todayStr;
  }
};

const close = () => {
  closeAllPopovers();
  emit('update:isOpen', false);
};

const applyFilters = () => {
  closeAllPopovers();
  emit('apply', { ...localFilters.value });
  close();
};

const resetFilters = () => {
  closeAllPopovers();
  localFilters.value = {
    product_id: '',
    product_name: '',
    product_search: '',
    warehouse_ids: [],
    counter_ids: [],
    salesman_ids: [],
    statuses: [],
    date_from: '',
    date_to: ''
  };
  emit('reset');
  close();
};

const handleDocumentClick = (e) => {
  if (!props.isOpen) return;
  activePopover.value = null;
};

onMounted(() => {
  fetchDropdownData();
  fetchTopProducts();
  document.addEventListener('click', handleDocumentClick);
});

onUnmounted(() => {
  document.removeEventListener('click', handleDocumentClick);
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.4);
  border-radius: 4px;
}
.animate-fade-in {
  animation: fadeIn 0.15s ease-out;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
