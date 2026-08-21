<template>
  <div class="w-full mx-auto p-3 sm:p-4 lg:p-5 bg-slate-50/50 dark:bg-zinc-950 min-h-screen">
    <div class="w-full max-w-full mx-auto">
        <!-- Header -->
        <div class="mb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div class="flex items-center gap-3 flex-wrap">
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-slate-100 tracking-tight">Items</h1>

            <!-- Total Inventory Items Message -->
            <div
              v-if="tablePagination && tablePagination.total !== undefined"
              class="inline-flex items-center gap-2 px-3.5 py-1 bg-emerald-500/10 dark:bg-emerald-500/15 border border-emerald-500/30 text-emerald-700 dark:text-emerald-400 rounded-full text-xs font-bold shadow-xs tracking-wide ml-1"
            >
              <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
              </span>
              <span>{{ tablePagination.total.toLocaleString() }} Items in Inventory</span>
            </div>
          </div>
          
          <div class="flex items-center gap-3 self-end sm:self-auto">
            <!-- Sales/Purchase Orders Pill Button -->
            <div class="relative">
              <button
                @click.stop="showSalesPurchaseDropdown = !showSalesPurchaseDropdown"
                class="inline-flex items-center px-4 py-2 bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#2E2E2E] hover:bg-gray-50 dark:hover:bg-[#2D2D2D]/80 text-gray-700 dark:text-slate-300 font-medium rounded-full shadow-xs transition-all duration-200 text-sm cursor-pointer"
              >
                <span>Sales/Purchase Orders</span>
                <svg class="w-4 h-4 ml-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <transition enter-active-class="transition ease-out duration-100" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-75" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
                <div v-show="showSalesPurchaseDropdown" class="absolute right-0 mt-1.5 w-48 bg-white dark:bg-[#1E1E1E] border border-gray-100 dark:border-[#2E2E2E] rounded-2xl shadow-xl py-1.5 z-50">
                  <router-link :to="{ name: 'SalesInvoices' }" class="block px-4 py-2 text-xs text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#2D2D2D]/80 transition-colors font-medium">Sales Invoices</router-link>
                  <router-link :to="{ name: 'PurchaseOrders' }" class="block px-4 py-2 text-xs text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#2D2D2D]/80 transition-colors font-medium">Purchase Orders</router-link>
                </div>
              </transition>
            </div>

            <!-- New Item Button -->
            <router-link
              v-if="authStore.hasPermission('products.create')"
              :to="{ name: 'CreateProduct' }"
              class="inline-flex items-center px-5 py-2 bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-semibold rounded-full shadow-sm transition-all duration-200 text-sm cursor-pointer"
            >
              + New Item
            </router-link>

            <!-- Muted Triple-Dot Action Trigger -->
            <div class="relative">
              <button
                @click.stop="showOptionsDropdown = !showOptionsDropdown"
                class="inline-flex items-center justify-center w-9 h-9 bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#2E2E2E] hover:bg-gray-50 dark:hover:bg-[#2D2D2D]/80 text-gray-500 dark:text-slate-400 rounded-full shadow-xs transition-all duration-200 cursor-pointer focus:outline-none"
              >
                <span class="text-sm font-bold tracking-widest leading-none mt-[-4px]">•••</span>
              </button>
              <transition enter-active-class="transition ease-out duration-100" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-75" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
                <div v-show="showOptionsDropdown" class="absolute right-0 mt-1.5 w-56 bg-white dark:bg-[#1E1E1E] border border-gray-100 dark:border-[#2E2E2E] rounded-2xl shadow-xl py-2 z-50">
                  <button
                    v-if="selectedProducts.length > 0 || products.length > 0"
                    @click="showBulkSaleModal = true; showOptionsDropdown = false"
                    class="w-full text-left px-4 py-2 text-xs text-rose-600 dark:text-rose-450 hover:bg-rose-50 dark:hover:bg-rose-955/20 transition-colors flex items-center gap-2 cursor-pointer font-semibold"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM12 8V7m0 1v1m0-1c0-1.657-1.343-3-3-3h0M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    Apply Sale
                  </button>
                  <button
                    v-if="authStore.hasPermission('products.import')"
                    @click="showImportModal = true; showOptionsDropdown = false"
                    class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#2D2D2D]/80 transition-colors flex items-center gap-2 cursor-pointer font-medium"
                  >
                    <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Import Products
                  </button>
                  <button
                    v-if="authStore.hasPermission('products.export')"
                    @click="exportProducts(); showOptionsDropdown = false"
                    class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#2D2D2D]/80 transition-colors flex items-center gap-2 cursor-pointer font-medium"
                  >
                    <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Export Products
                  </button>
                  <button
                    @click="router.push('/inventory/categories-brands'); showOptionsDropdown = false"
                    class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#2D2D2D]/80 transition-colors flex items-center gap-2 cursor-pointer font-medium"
                  >
                    <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Manage Categories
                  </button>
                </div>
              </transition>
            </div>
          </div>
        </div>

        <!-- Action & Filter Bar (Above Table) -->
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4 px-1">
          <!-- Left side: Active Filter summary pills if active -->
          <div class="flex flex-wrap items-center gap-2">
            <span 
              v-for="tVal in (Array.isArray(tableFilters.item_type) ? tableFilters.item_type : (tableFilters.item_type ? [tableFilters.item_type] : []))" 
              :key="tVal"
              class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/40 rounded-full text-xs font-medium"
            >
              Type: {{ getItemTypeName(tVal, true) }}
              <button @click="toggleItemType(tVal)" class="hover:text-emerald-900 dark:hover:text-emerald-200 ml-0.5 cursor-pointer">&times;</button>
            </span>

            <span v-if="tableFilters.category_id" class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/40 rounded-full text-xs font-medium">
              Cat: {{ getCategoryName(tableFilters.category_id) }}
              <button @click="selectCategory('')" class="hover:text-emerald-900 dark:hover:text-emerald-200 ml-0.5 cursor-pointer">&times;</button>
            </span>

            <span v-if="tableFilters.brand_id" class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/40 rounded-full text-xs font-medium">
              Brand: {{ getBrandName(tableFilters.brand_id) }}
              <button @click="selectBrand('')" class="hover:text-emerald-900 dark:hover:text-emerald-200 ml-0.5 cursor-pointer">&times;</button>
            </span>

            <span v-if="tableFilters.price_sort" class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/40 rounded-full text-xs font-medium">
              Sort: {{ getPriceSortName(tableFilters.price_sort) }}
              <button @click="selectPriceSort('')" class="hover:text-emerald-900 dark:hover:text-emerald-200 ml-0.5 cursor-pointer">&times;</button>
            </span>

            <span v-if="tableFilters.on_sale" class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/40 rounded-full text-xs font-medium">
              On Sale
              <button @click="toggleOnSaleFilter" class="hover:text-emerald-900 dark:hover:text-emerald-200 ml-0.5 cursor-pointer">&times;</button>
            </span>

            <span v-if="tableFilters.show_inactive" class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 dark:bg-amber-955/20 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-900/40 rounded-full text-xs font-medium">
              Inactive
              <button @click="toggleInactiveFilter" class="hover:text-amber-900 dark:hover:text-amber-200 ml-0.5 cursor-pointer">&times;</button>
            </span>
          </div>

          <!-- Right side: Clear All, Filter & Draft Items buttons -->
          <div class="flex items-center gap-2 ml-auto">
            <!-- Clear Filters button -->
            <button
              v-show="hasActiveFilters"
              @click="clearFilters"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 dark:bg-rose-955/20 border border-rose-200 dark:border-rose-900/30 text-rose-700 dark:text-rose-400 hover:bg-rose-100/50 dark:hover:bg-rose-955/35 rounded-lg text-xs font-semibold cursor-pointer transition-colors shadow-xs"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
              <span>Clear All</span>
            </button>

            <!-- Filter Drawer Trigger Button -->
            <button
              @click="showFilterDrawer = true"
              class="inline-flex items-center gap-1.5 px-3.5 py-1.5 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs font-semibold text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-900 hover:bg-slate-50 dark:hover:bg-zinc-800 shadow-xs transition-all focus:outline-none cursor-pointer"
              :class="{ 'border-slate-900 text-slate-900 bg-slate-100/50 dark:bg-zinc-800 dark:border-zinc-100 dark:text-zinc-100 font-bold': totalActiveFilterCount > 0 }"
            >
              <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" :class="{ 'text-slate-900 dark:text-zinc-100': totalActiveFilterCount > 0 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 8.293A1 1 0 013 7.586V4z"/>
              </svg>
              <span>Filter</span>
              <span v-if="totalActiveFilterCount > 0" class="ml-1 text-[10px] font-extrabold bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 px-1.5 py-0.2 rounded-full">
                {{ totalActiveFilterCount }}
              </span>
            </button>

            <!-- Draft Items Chip -->
            <button
              @click="openDraftsModal"
              class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#2E2E2E] hover:bg-gray-50 dark:hover:bg-[#2D2D2D]/80 text-gray-600 dark:text-slate-300 rounded-lg text-xs font-semibold cursor-pointer transition-colors shadow-xs"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-amber-500">
                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
              </svg>
              <span>Draft Items ({{ draftsCount }})</span>
            </button>
          </div>
        </div>

        <!-- Custom Products Data Table -->
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2E2E2E] overflow-hidden shadow-sm mb-8 min-h-[400px] flex flex-col justify-between">
          <!-- Card Header Bar (Search & View Mode Toggle on Left, Row Selector & Count on Right) -->
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-100 dark:border-[#2E2E2E]">
            <!-- Left Side: Search Bar & View Mode Toggle -->
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
              <!-- Compact Search Bar -->
              <div class="relative w-full sm:w-72">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                  </svg>
                </span>
                <input
                  type="text"
                  v-model="tableFilters.search"
                  @input="handleSearchInput"
                  placeholder="Search by product name, SKU, barcode..."
                  class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 dark:bg-[#252525] border border-gray-200 dark:border-[#2E2E2E] rounded-lg text-gray-900 dark:text-slate-200 focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-100 dark:focus:border-slate-700 transition-all placeholder:text-gray-400 dark:placeholder:text-slate-500"
                />
              </div>

              <!-- View Mode Toggle (Table / Grid) -->
              <div class="flex items-center gap-1 bg-slate-100 dark:bg-[#252525] p-1 rounded-xl border border-gray-200 dark:border-[#2E2E2E]">
                <button 
                  @click="viewMode = 'table'"
                  :class="viewMode === 'table' ? 'bg-white dark:bg-[#1E1E1E] text-indigo-600 dark:text-indigo-400 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 font-medium'"
                  class="px-2.5 py-1 rounded-lg text-xs flex items-center gap-1.5 transition-all cursor-pointer"
                  title="Table View"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                  </svg>
                  <span class="text-[11px]">Table</span>
                </button>
                
                <button 
                  @click="viewMode = 'grid'"
                  :class="viewMode === 'grid' ? 'bg-white dark:bg-[#1E1E1E] text-indigo-600 dark:text-indigo-400 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 font-medium'"
                  class="px-2.5 py-1 rounded-lg text-xs flex items-center gap-1.5 transition-all cursor-pointer"
                  title="Grid View (Max 5 per row)"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                  </svg>
                  <span class="text-[11px]">Grid</span>
                </button>
              </div>
            </div>

            <!-- Right Side: Row Selector (15, 30, 50) & Count -->
            <div class="flex items-center gap-4 justify-between md:justify-end w-full md:w-auto">
              <!-- Floating Row Selector Custom Dropdown -->
              <div class="relative inline-block text-left" @click.stop>
                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 font-semibold">
                  <span class="text-[11px] uppercase tracking-wider text-slate-400 font-bold">Rows:</span>
                  
                  <!-- Trigger Button displaying current selected value -->
                  <button
                    type="button"
                    @click="showRowsDropdown = !showRowsDropdown"
                    class="inline-flex items-center justify-between gap-2 px-3 py-1.5 bg-slate-50 dark:bg-[#252525] hover:bg-slate-100 dark:hover:bg-[#2D2D2D] border border-gray-200 dark:border-[#2E2E2E] rounded-xl text-xs font-bold text-slate-800 dark:text-slate-200 shadow-xs transition-all cursor-pointer focus:outline-none focus:border-indigo-500"
                  >
                    <span>{{ tablePagination.per_page || 15 }} per page</span>
                    <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': showRowsDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>

                  <!-- Floating Dropdown Menu -->
                  <transition enter-active-class="transition ease-out duration-100" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                    <div 
                      v-if="showRowsDropdown"
                      class="absolute right-0 top-full mt-1.5 w-36 bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#2E2E2E] rounded-xl shadow-2xl py-1 z-50 overflow-hidden"
                    >
                      <button
                        v-for="option in [15, 30, 50]"
                        :key="option"
                        type="button"
                        @click="handlePerPageChange(option); showRowsDropdown = false"
                        class="w-full px-3 py-2 text-left text-xs font-semibold flex items-center justify-between hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer"
                        :class="tablePagination.per_page === option ? 'bg-indigo-50/70 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-700 dark:text-slate-300'"
                      >
                        <span>{{ option }} per page</span>
                        <svg v-if="tablePagination.per_page === option" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                      </button>
                    </div>
                  </transition>
                </div>
              </div>

              <!-- Page / Total Items Summary -->
              <div v-if="tablePagination && tablePagination.total !== undefined" class="text-xs text-gray-500 dark:text-slate-400 font-medium whitespace-nowrap">
                Showing <span class="font-bold text-gray-900 dark:text-slate-200">{{ products.length }}</span> of <span class="font-bold text-gray-900 dark:text-slate-200">{{ tablePagination.total }}</span> items
              </div>
            </div>
          </div>
          <!-- Table View Container -->
          <div v-if="viewMode === 'table'" class="w-full overflow-x-auto min-h-[400px]">
            <table class="w-full table-auto align-middle border-collapse">
              <thead>
                <tr class="border-b border-gray-100 dark:border-[#2E2E2E] bg-slate-50/50 dark:bg-[#252525] text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-400">
                  <th class="px-3 py-3 w-10 text-center">
                    <input
                      type="checkbox"
                      :checked="isAllSelected"
                      @change="toggleSelectAll"
                      class="w-4 h-4 text-emerald-600 border border-gray-300 dark:border-[#2E2E2E] dark:bg-[#1E1E1E] focus:ring-0 rounded-none cursor-pointer"
                    />
                  </th>
                  <th class="px-3 sm:px-4 py-3 text-left font-bold">Item Name &amp; Description</th>
                  <th class="px-3 py-3 text-center font-bold">SKU</th>
                  <th class="px-3 py-3 text-center font-bold">Category</th>
                  <th class="px-3 py-3 text-center font-bold">Status</th>
                  <th class="px-3 py-3 text-center font-bold">Stock</th>
                  <th class="px-3 py-3 text-center font-bold">Price Matrix</th>
                  <th class="px-3 py-3 text-center font-bold w-16">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-[#2E2E2E]">
                <!-- Loading State -->
                <tr v-if="loading" class="dark:bg-[#1E1E1E]">
                  <td colspan="8" class="px-4 py-16 text-center text-gray-400">
                    <div class="flex justify-center items-center gap-2">
                      <svg class="animate-spin h-5 w-5 text-emerald-600" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      <span class="text-sm font-semibold text-gray-500 dark:text-slate-400">Fetching products...</span>
                    </div>
                  </td>
                </tr>

                <!-- Empty State -->
                <tr v-else-if="products.length === 0" class="dark:bg-[#1E1E1E]">
                  <td colspan="8" class="px-4 py-20 text-center text-gray-500">
                    <div class="flex flex-col items-center max-w-sm mx-auto">
                      <div class="w-12 h-12 rounded-2xl bg-gray-50 dark:bg-[#252525] flex items-center justify-center mb-4 text-gray-400 dark:text-slate-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                      </div>
                      <p class="text-base font-bold text-gray-900 dark:text-slate-200 mb-1">No products found</p>
                      <p class="text-xs text-gray-400 dark:text-slate-500 font-medium mb-3">Get started by adding your first product, or adjusting your filters.</p>
                      <button 
                        v-if="hasActiveFilters" 
                        @click="clearFilters" 
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow transition-all cursor-pointer"
                      >
                        Reset Filters
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Data Rows -->
                <tr v-else v-for="item in products" :key="item.id" class="group hover:bg-slate-50/20 dark:hover:bg-[#2D2D2D]/80 dark:bg-transparent transition-colors relative">
                  <!-- Checkbox -->
                  <td class="px-3 py-3 text-center align-middle w-10">
                    <input
                      type="checkbox"
                      :value="item.id"
                      v-model="selectedProducts"
                      class="w-4 h-4 text-emerald-600 border border-gray-300 dark:border-[#2E2E2E] dark:bg-[#1E1E1E] focus:ring-0 rounded-none cursor-pointer"
                    />
                  </td>

                  <!-- Name & Description -->
                  <td class="px-3 sm:px-4 py-3 align-middle">
                    <div class="flex items-center gap-2.5">
                      <!-- Product Image Thumbnail -->
                      <div 
                        @click.stop="openLightbox(item)"
                        class="relative h-9 w-9 shrink-0 rounded-xl border border-gray-100 dark:border-[#2E2E2E] overflow-hidden bg-slate-50 dark:bg-[#1E1E1E] flex items-center justify-center cursor-pointer hover:scale-110 transition-all group/thumb shadow-xs select-none"
                        title="Click to view image gallery"
                      >
                        <div v-if="Number(item.discount_value) > 0" class="absolute top-0 right-0 z-10 pointer-events-none select-none">
                          <div class="absolute transform rotate-45 bg-rose-600 text-white text-[6px] font-black uppercase text-center tracking-widest py-0.5 w-[50px] -right-[15px] top-[4px] shadow-xs border-b border-white/20">Sale</div>
                        </div>
                        <img
                          v-if="getProductDisplayImage(item)"
                          :src="getProductDisplayImage(item)"
                          :alt="item.name"
                          class="h-full w-full object-cover cursor-pointer"
                          @click.stop="openLightbox(item)"
                        />
                        <span 
                          v-else 
                          @click.stop="openLightbox(item)"
                          class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase select-none cursor-pointer"
                        >
                          {{ item.name ? item.name.substring(0, 1) : 'P' }}
                        </span>
                      </div>

                      <div class="flex flex-col min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                          <span class="font-extrabold text-gray-950 dark:text-slate-200 text-sm truncate max-w-[200px] sm:max-w-xs">{{ item.name }}</span>
                          <span 
                            v-if="item.variations_count > 0 || (item.variations && item.variations.length > 0)"
                            class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-black tracking-wide bg-red-50 text-red-700 border border-red-100 dark:bg-red-500 dark:text-black dark:border-transparent uppercase shrink-0"
                          >
                            Variants
                          </span>
                          <span 
                            v-if="item.brand" 
                            class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-slate-100 dark:bg-[#252525] text-slate-700 dark:text-slate-300 border border-slate-200/60 dark:border-[#2E2E2E] uppercase shrink-0"
                          >
                            {{ item.brand.name }}
                          </span>
                          <span 
                            class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-md text-[9px] font-bold border uppercase shrink-0"
                            :class="getItemTypeBadgeClass(item.item_type || 'standard')"
                          >
                            {{ getItemTypeName(item.item_type || 'standard', true) }}
                          </span>
                        </div>
                        <div v-if="parseItemTags(item).length > 0" class="flex items-center gap-1 mt-0.5 flex-wrap">
                          <span 
                            v-for="(tag, tIdx) in parseItemTags(item)" 
                            :key="tIdx"
                            class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-900/30"
                          >
                            #{{ tag }}
                          </span>
                        </div>
                        <span class="text-xs text-gray-400 dark:text-slate-500 mt-0.5 font-medium truncate max-w-[220px] sm:max-w-xs">{{ stripHtmlTags(item.description) || item.sku || 'No description' }}</span>
                      </div>
                    </div>
                  </td>

                  <!-- SKU -->
                  <td class="px-3 py-3 align-middle text-center whitespace-nowrap">
                    <div v-if="item.variations && item.variations.length > 0" class="flex flex-col items-center gap-0.5">
                      <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-[#252525] text-slate-650 dark:text-slate-300 border border-slate-200/60 dark:border-[#2E2E2E] uppercase">
                        {{ item.variations[0].sku || '-' }}
                      </span>
                      <span 
                        v-if="item.variations.length > 1" 
                        class="text-[9px] font-semibold text-slate-400 dark:text-slate-500 mt-0.5"
                      >
                        +{{ item.variations.length - 1 }} more
                      </span>
                    </div>
                    <span v-else class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-[#252525] text-slate-650 dark:text-slate-300 border border-slate-200/60 dark:border-[#2E2E2E] uppercase">
                      {{ item.sku || '-' }}
                    </span>
                  </td>

                  <!-- Category (Category, Sub Category, Child Category in same column) -->
                  <td class="px-3 py-3 align-middle text-center">
                    <div class="flex flex-col items-center gap-1 justify-center">
                      <div v-if="getCategoryHierarchy(item.category).length > 0" class="contents">
                        <span 
                          v-for="(catName, index) in getCategoryHierarchy(item.category)" 
                          :key="index"
                          :class="[
                            'inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-semibold',
                            index === 0 ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-100/50 dark:border-blue-900/30' :
                            index === 1 ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-100/50 dark:border-indigo-900/30 text-[10px]' :
                            'bg-slate-100 dark:bg-[#252525] text-slate-600 dark:text-slate-400 border border-slate-200/60 dark:border-[#2E2E2E] text-[9px]'
                          ]"
                        >
                          <span class="w-1.5 h-1.5 rounded-full mr-1 shrink-0" :class="index === 0 ? 'bg-blue-500' : index === 1 ? 'bg-indigo-500' : 'bg-slate-400'"></span>
                          <span>{{ catName }}</span>
                        </span>
                      </div>
                      <span v-else class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-[#252525] text-slate-500 dark:text-slate-400">
                        General
                      </span>
                    </div>
                  </td>

                  <!-- Status Toggle Switch Column -->
                  <td class="px-3 py-3 align-middle text-center whitespace-nowrap">
                    <!-- Draft Badge (if status is draft) -->
                    <div v-if="item.status === 'draft'" class="inline-flex items-center gap-1.5 justify-center">
                      <span
                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/40 shadow-xs cursor-default"
                        title="Draft items must be edited from the edit page to publish or change status"
                      >
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        <span>Draft</span>
                      </span>
                    </div>

                    <!-- Active / Inactive Toggle Switch -->
                    <div v-else class="inline-flex items-center gap-1.5 justify-center">
                      <button
                        type="button"
                        @click.stop="toggleProductStatus(item)"
                        :disabled="togglingStatusId === item.id"
                        :class="[
                          'relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 disabled:opacity-50 disabled:cursor-not-allowed',
                          item.is_active ? 'bg-emerald-500 dark:bg-emerald-600' : 'bg-gray-300 dark:bg-gray-700'
                        ]"
                        role="switch"
                        :aria-checked="item.is_active"
                        :title="item.is_active ? 'Click to deactivate product' : 'Click to activate product'"
                      >
                        <span class="sr-only">Toggle product status</span>
                        <span
                          :class="[
                            'pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out flex items-center justify-center',
                            item.is_active ? 'translate-x-4' : 'translate-x-0'
                          ]"
                        >
                          <svg v-if="togglingStatusId === item.id" class="animate-spin h-2.5 w-2.5 text-emerald-600" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                          </svg>
                        </span>
                      </button>
                      <span
                        class="text-xs font-bold shrink-0"
                        :class="item.is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-slate-500'"
                      >
                        {{ item.is_active ? 'Active' : 'Inactive' }}
                      </span>
                    </div>
                  </td>

                  <!-- Stock Status -->
                  <td class="px-3 py-3 align-middle text-center whitespace-nowrap">
                    <span class="text-sm font-extrabold text-gray-900 dark:text-slate-200" v-if="item.stock_quantity !== null && item.stock_quantity !== undefined">
                      {{ item.stock_quantity }}
                    </span>
                    <span class="text-sm font-semibold text-gray-300 dark:text-slate-600" v-else>N/A</span>
                  </td>

                  <!-- Price Matrix (Center Aligned) -->
                  <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                    <div class="flex flex-col items-center justify-center text-center">
                      <!-- Main Sale Price -->
                      <span class="text-sm font-extrabold text-gray-950 dark:text-slate-200">
                        {{ currencyStore.formatPrice(getItemSellingPrice(item)) }}
                      </span>
                      <!-- Conditional Wholesale Price Display -->
                      <span v-if="getItemWholesalePrice(item) !== null" class="text-xs text-gray-400 dark:text-slate-500 mt-0.5 font-semibold">
                        {{ currencyStore.formatPrice(getItemWholesalePrice(item)) }}
                      </span>
                    </div>
                  </td>

                  <!-- Actions Dropdown Column -->
                  <td class="px-3 py-3 align-middle text-center w-16 relative">
                    <div class="relative inline-block text-left">
                      <button
                        @click.stop="toggleActionDropdown(item.id)"
                        class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#2A2A2A] border border-transparent hover:border-slate-200 dark:hover:border-[#333] transition-all focus:outline-none cursor-pointer"
                        title="Actions"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                        </svg>
                      </button>

                      <!-- Dropdown Menu -->
                      <div
                        v-if="activeActionDropdownId === item.id"
                        class="origin-top-right absolute right-0 mt-1 w-36 rounded-xl bg-white dark:bg-[#1E1E1E] shadow-xl ring-1 ring-black/5 dark:ring-white/10 divide-y divide-gray-100 dark:divide-[#2E2E2E] z-50 focus:outline-none py-1 border border-gray-100 dark:border-[#2E2E2E]"
                      >
                        <button
                          @click.stop="viewProduct(item); activeActionDropdownId = null"
                          class="w-full group flex items-center px-3 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer"
                        >
                          <svg class="mr-2 h-4 w-4 text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                          </svg>
                          View
                        </button>

                        <button
                          @click.stop="editProduct(item); activeActionDropdownId = null"
                          class="w-full group flex items-center px-3 py-2 text-xs font-semibold text-gray-700 dark:text-slate-300 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors cursor-pointer"
                        >
                          <svg class="mr-2 h-4 w-4 text-gray-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                          Update
                        </button>

                        <button
                          @click.stop="deleteProduct(item); activeActionDropdownId = null"
                          class="w-full group flex items-center px-3 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
                        >
                          <svg class="mr-2 h-4 w-4 text-rose-500 group-hover:text-rose-600 dark:group-hover:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                          Delete
                        </button>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Grid View Layout (Max 5 items per row) -->
          <div v-else-if="viewMode === 'grid'" class="p-4 min-h-[400px]">
            <!-- Loading State -->
            <div v-if="loading" class="py-16 text-center text-gray-400">
              <div class="flex justify-center items-center gap-2">
                <svg class="animate-spin h-5 w-5 text-emerald-600" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-500 dark:text-slate-400">Fetching products...</span>
              </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="products.length === 0" class="py-20 text-center text-gray-500">
              <div class="flex flex-col items-center max-w-sm mx-auto">
                <div class="w-12 h-12 rounded-2xl bg-gray-50 dark:bg-[#252525] flex items-center justify-center mb-4 text-gray-400 dark:text-slate-500">
                  <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                </div>
                <p class="text-base font-bold text-gray-900 dark:text-slate-200 mb-1">No products found</p>
                <p class="text-xs text-gray-400 dark:text-slate-500 font-medium mb-3">Get started by adding your first product, or adjusting your filters.</p>
                <button 
                  v-if="hasActiveFilters" 
                  @click="clearFilters" 
                  class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow transition-all cursor-pointer"
                >
                  Reset Filters
                </button>
              </div>
            </div>

            <!-- Grid Items (Max 5 per row) -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3.5">
              <div 
                v-for="item in products" 
                :key="item.id"
                class="group bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#2E2E2E] rounded-xl overflow-hidden hover:shadow-md transition-all duration-200 flex flex-col justify-between relative"
              >
                <!-- Top Image Area (Compact h-32 height) -->
                <div class="relative w-full h-32 bg-slate-50 dark:bg-[#252525] overflow-hidden group/img">
                  <!-- Sale Banner Ribbon -->
                  <div v-if="Number(item.discount_value) > 0" class="absolute top-2 right-2 z-10">
                    <span class="px-1.5 py-0.5 bg-rose-600 text-white text-[9px] font-black uppercase tracking-wider rounded-md shadow-xs">
                      Sale
                    </span>
                  </div>

                  <!-- Checkbox Selection -->
                  <div class="absolute top-2 left-2 z-10">
                    <input
                      type="checkbox"
                      :value="item.id"
                      v-model="selectedProducts"
                      class="w-4 h-4 text-emerald-600 border border-gray-300 dark:border-[#2E2E2E] dark:bg-[#1E1E1E] focus:ring-0 rounded-md cursor-pointer shadow-xs"
                    />
                  </div>

                  <!-- Product Image -->
                  <img
                    v-if="getProductDisplayImage(item)"
                    :src="getProductDisplayImage(item)"
                    :alt="item.name"
                    class="w-full h-full object-cover group-hover/img:scale-105 transition-transform duration-300 cursor-pointer"
                    @click.stop="openLightbox(item)"
                  />
                  <!-- Image Fallback Placeholder -->
                  <div 
                    v-else 
                    @click.stop="openLightbox(item)"
                    class="w-full h-full flex flex-col items-center justify-center text-slate-300 dark:text-slate-600 cursor-pointer"
                  >
                    <span class="text-2xl font-black uppercase tracking-wider">
                      {{ item.name ? item.name.substring(0, 1) : 'P' }}
                    </span>
                    <span class="text-[9px] font-bold text-slate-400 mt-0.5 uppercase">No Image</span>
                  </div>
                </div>

                <!-- Card Content Area -->
                <div class="p-2.5 flex-1 flex flex-col justify-between space-y-2">
                  <div>
                    <!-- Title & Variants Count Badge -->
                    <div class="flex items-start justify-between gap-1 mb-1">
                      <h3 class="font-extrabold text-xs text-gray-900 dark:text-slate-100 line-clamp-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors" :title="item.name">
                        {{ item.name }}
                      </h3>
                      <span 
                        v-if="item.variations_count > 0 || (item.variations && item.variations.length > 0)"
                        class="px-1 py-0.5 rounded text-[8px] font-black bg-red-50 text-red-700 dark:bg-red-500/20 dark:text-red-400 shrink-0 uppercase"
                      >
                        Variants
                      </span>
                    </div>

                    <!-- Category Hierarchy Path (Main > Sub > Child) -->
                    <div class="mb-1.5">
                      <div v-if="getCategoryHierarchy(item.category).length > 0" class="flex flex-wrap gap-1 items-center">
                        <span 
                          v-for="(catName, index) in getCategoryHierarchy(item.category)" 
                          :key="index"
                          :class="[
                            'px-1.5 py-0.5 rounded-full text-[9px] font-semibold',
                            index === 0 ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-100 dark:border-blue-900/30' :
                            index === 1 ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-900/30' :
                            'bg-slate-100 dark:bg-[#252525] text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-[#2E2E2E]'
                          ]"
                        >
                          {{ catName }}
                        </span>
                      </div>
                      <span v-else class="px-1.5 py-0.5 rounded-full text-[9px] font-semibold bg-slate-100 dark:bg-[#252525] text-slate-500 dark:text-slate-400">
                        General
                      </span>
                    </div>

                    <!-- Brand & Tags -->
                    <div class="flex items-center gap-1 flex-wrap mb-1.5">
                      <!-- Brand Badge -->
                      <span 
                        v-if="item.brand" 
                        class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 dark:bg-[#252525] text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-[#2E2E2E] uppercase"
                      >
                        🏷️ {{ item.brand.name }}
                      </span>

                      <!-- Item Type Badge -->
                      <span 
                        class="px-1.5 py-0.5 rounded text-[9px] font-bold border uppercase shrink-0"
                        :class="getItemTypeBadgeClass(item.item_type || 'standard')"
                      >
                        {{ getItemTypeName(item.item_type || 'standard', true) }}
                      </span>

                      <!-- Tag Badges -->
                      <span 
                        v-for="(tag, tIdx) in parseItemTags(item)" 
                        :key="tIdx"
                        class="px-1 py-0.5 rounded text-[8px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-900/30"
                      >
                        #{{ tag }}
                      </span>
                    </div>

                    <!-- SKU & Barcode Row -->
                    <div class="grid grid-cols-2 gap-1 text-[9px] bg-slate-50 dark:bg-[#252525] p-1.5 rounded-lg border border-slate-100 dark:border-[#2E2E2E] mb-2">
                      <div>
                        <span class="block text-slate-400 dark:text-slate-500 font-bold uppercase text-[8px]">SKU</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200 truncate block">
                          {{ item.sku || (item.variations && item.variations[0] ? item.variations[0].sku : '-') }}
                        </span>
                      </div>
                      <div>
                        <span class="block text-slate-400 dark:text-slate-500 font-bold uppercase text-[8px]">Barcode</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200 truncate block">
                          {{ item.barcode || '-' }}
                        </span>
                      </div>
                    </div>

                    <!-- Selling Price & Wholesale Price Row -->
                    <div class="flex items-baseline justify-between border-t border-slate-100 dark:border-[#2E2E2E] pt-1.5 mb-1">
                      <div>
                        <span class="text-[8px] font-bold text-slate-400 dark:text-slate-500 uppercase block">Selling Price</span>
                        <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">
                          {{ currencyStore.formatPrice(getItemSellingPrice(item)) }}
                        </span>
                      </div>

                      <div v-if="getItemWholesalePrice(item) !== null" class="text-right">
                        <span class="text-[8px] font-bold text-slate-400 dark:text-slate-500 uppercase block">Wholesale</span>
                        <span class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300">
                          {{ currencyStore.formatPrice(getItemWholesalePrice(item)) }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Card Footer Actions & Status Toggle -->
                  <div class="border-t border-slate-100 dark:border-[#2E2E2E] pt-1.5 flex items-center justify-between gap-1.5">
                    <!-- Active/Inactive Status Toggle Switch -->
                    <div v-if="item.status === 'draft'" class="inline-flex items-center gap-1 text-[9px] font-bold text-amber-600 dark:text-amber-400">
                      <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Draft
                    </div>
                    <div v-else class="flex items-center gap-1">
                      <button
                        type="button"
                        @click.stop="toggleProductStatus(item)"
                        :disabled="togglingStatusId === item.id"
                        :class="[
                          'relative inline-flex h-4.5 w-8 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none disabled:opacity-50',
                          item.is_active ? 'bg-emerald-500 dark:bg-emerald-600' : 'bg-gray-300 dark:bg-gray-700'
                        ]"
                        role="switch"
                        :aria-checked="item.is_active"
                        :title="item.is_active ? 'Deactivate product' : 'Activate product'"
                      >
                        <span
                          :class="[
                            'pointer-events-none inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out flex items-center justify-center',
                            item.is_active ? 'translate-x-3.5' : 'translate-x-0'
                          ]"
                        >
                          <svg v-if="togglingStatusId === item.id" class="animate-spin h-2 w-2 text-emerald-600" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                          </svg>
                        </span>
                      </button>
                      <span class="text-[9px] font-bold" :class="item.is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500'">
                        {{ item.is_active ? 'Active' : 'Inactive' }}
                      </span>
                    </div>

                    <!-- Action Buttons (View, Edit, Delete) -->
                    <div class="flex items-center gap-0.5">
                      <button
                        @click.stop="viewProduct(item)"
                        class="p-1.5 text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 rounded-lg transition-colors cursor-pointer"
                        title="View Product Details"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                      </button>
                      <button
                        @click.stop="editProduct(item)"
                        class="p-1.5 text-slate-500 hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 rounded-lg transition-colors cursor-pointer"
                        title="Edit Product"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                      <button
                        @click.stop="deleteProduct(item)"
                        class="p-1.5 text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors cursor-pointer"
                        title="Delete Product"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination Footer -->
          <div v-if="tablePagination && !loading" class="px-6 py-4 bg-white dark:bg-[#1E1E1E] border-t border-gray-100 dark:border-[#2E2E2E] flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div class="flex items-center gap-3">
              <div class="text-xs text-gray-500 dark:text-slate-400 font-semibold">
                Showing {{ tablePagination.from || 0 }} to {{ tablePagination.to || 0 }} of {{ tablePagination.total || 0 }} results
              </div>

              <!-- Floating Row Selector Dropdown (Bottom) -->
              <div class="relative inline-block text-left border-l border-slate-200 dark:border-[#2E2E2E] pl-3" @click.stop>
                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 font-semibold">
                  <span class="text-[11px] uppercase tracking-wider text-slate-400 font-bold">Rows:</span>
                  
                  <button
                    type="button"
                    @click="showBottomRowsDropdown = !showBottomRowsDropdown"
                    class="inline-flex items-center justify-between gap-1.5 px-2.5 py-1 bg-slate-50 dark:bg-[#252525] hover:bg-slate-100 dark:hover:bg-[#2D2D2D] border border-gray-200 dark:border-[#2E2E2E] rounded-lg text-xs font-bold text-slate-800 dark:text-slate-200 shadow-xs transition-all cursor-pointer focus:outline-none"
                  >
                    <span>{{ tablePagination.per_page || 15 }}</span>
                    <svg class="w-3 h-3 text-slate-400 dark:text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': showBottomRowsDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>

                  <transition enter-active-class="transition ease-out duration-100" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                    <div 
                      v-if="showBottomRowsDropdown"
                      class="absolute left-0 bottom-full mb-1.5 w-32 bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#2E2E2E] rounded-xl shadow-2xl py-1 z-50 overflow-hidden"
                    >
                      <button
                        v-for="option in [15, 30, 50]"
                        :key="option"
                        type="button"
                        @click="handlePerPageChange(option); showBottomRowsDropdown = false"
                        class="w-full px-3 py-1.5 text-left text-xs font-semibold flex items-center justify-between hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer"
                        :class="tablePagination.per_page === option ? 'bg-indigo-50/70 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-700 dark:text-slate-300'"
                      >
                        <span>{{ option }} rows</span>
                        <svg v-if="tablePagination.per_page === option" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                      </button>
                    </div>
                  </transition>
                </div>
              </div>
            </div>

            <!-- Page Selection Controls -->
            <div class="flex items-center gap-1.5 self-center sm:self-auto">
              <button
                @click="goToPage(1)"
                :disabled="tablePagination.current_page === 1"
                class="px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-[#2E2E2E] text-xs font-semibold text-gray-500 dark:text-slate-400 bg-white dark:bg-[#1E1E1E] hover:bg-gray-50 dark:hover:bg-[#2D2D2D]/80 disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer"
              >
                First
              </button>
              <button
                @click="goToPage(tablePagination.current_page - 1)"
                :disabled="tablePagination.current_page === 1"
                class="px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-[#2E2E2E] text-xs font-semibold text-gray-500 dark:text-slate-400 bg-white dark:bg-[#1E1E1E] hover:bg-gray-50 dark:hover:bg-[#2D2D2D]/80 disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer"
              >
                Prev
              </button>
              
              <button
                v-for="page in visiblePages"
                :key="page"
                @click="goToPage(page)"
                :class="[
                  'px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer',
                  page === tablePagination.current_page
                    ? 'bg-emerald-600 text-white shadow-xs'
                    : 'border border-gray-200 dark:border-[#2E2E2E] text-gray-600 dark:text-slate-400 bg-white dark:bg-[#1E1E1E] hover:bg-gray-50 dark:hover:bg-[#2D2D2D]/80'
                ]"
              >
                {{ page }}
              </button>
              
              <button
                @click="goToPage(tablePagination.current_page + 1)"
                :disabled="tablePagination.current_page === tablePagination.last_page"
                class="px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-[#2E2E2E] text-xs font-semibold text-gray-500 dark:text-slate-450 bg-white dark:bg-[#1E1E1E] hover:bg-gray-50 dark:hover:bg-[#2D2D2D]/80 disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer"
              >
                Next
              </button>
              <button
                @click="goToPage(tablePagination.last_page)"
                :disabled="tablePagination.current_page === tablePagination.last_page"
                class="px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-[#2E2E2E] text-xs font-semibold text-gray-500 dark:text-slate-450 bg-white dark:bg-[#1E1E1E] hover:bg-gray-50 dark:hover:bg-[#2D2D2D]/80 disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer"
              >
                Last
              </button>
            </div>
          </div>
        </div>

    <!-- Product Details View Modal Component -->
    <ProductViewModal
      :show="showViewModal"
      :product="viewingProduct"
      :loading="isLoadingViewProduct"
      @close="showViewModal = false"
      @edit="editProduct"
    />

    <!-- Drafts Workbench Modal Component -->
    <ProductDraftsModal
      :show="isDraftsModalOpen"
      :drafts="draftProducts"
      :loading="isLoadingDrafts"
      @close="isDraftsModalOpen = false"
      @edit="editProduct"
      @delete-selected="deleteSelectedDrafts"
    />

    <!-- Import Products Modal -->
    <div v-if="showImportModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200" style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);" @click.self="closeImportModal">
      <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-lg shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 p-6 transition-all duration-300 z-10 max-h-[90vh] overflow-y-auto my-auto">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Import Products</h3>
            <button @click="closeImportModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <div class="space-y-4">
            <!-- Download Template -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <h4 class="text-sm font-medium text-blue-900 mb-2">Download Template</h4>
              <p class="text-sm text-blue-700 mb-3">Download the CSV template to see the required format for importing products.</p>
              <button
                @click="downloadTemplate"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
              >
                Download Template
              </button>
            </div>

            <!-- File Upload -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Select File</label>
              <input
                ref="fileInput"
                type="file"
                accept=".csv,.xlsx,.xls"
                @change="handleFileSelect"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
              />
              <p class="text-xs text-gray-500 mt-1">Supported formats: CSV, XLSX, XLS (Max: 10MB)</p>
            </div>

            <!-- Selected File Info -->
            <div v-if="selectedFile" class="bg-gray-50 border border-gray-200 rounded-lg p-3">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="text-sm text-gray-700">{{ selectedFile.name }}</span>
                <span class="text-xs text-gray-500 ml-2">({{ formatFileSize(selectedFile.size) }})</span>
              </div>
            </div>

            <!-- Import Results -->
            <div v-if="importResults" class="space-y-3">
              <div v-if="importResults.imported > 0" class="bg-green-50 border border-green-200 rounded-lg p-3">
                <p class="text-sm text-green-800">
                  ✓ Successfully imported {{ importResults.imported }} products
                </p>
              </div>

              <div v-if="importResults.errors && importResults.errors.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-3">
                <p class="text-sm text-red-800 font-medium mb-2">Import Errors:</p>
                <div class="max-h-32 overflow-y-auto">
                  <div v-for="error in importResults.errors" :key="error.row" class="text-xs text-red-700 mb-1">
                    Row {{ error.row }}: {{ error.errors.join(', ') }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 pt-4">
              <button
                @click="closeImportModal"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                @click="importProducts"
                :disabled="!selectedFile || importing"
                class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ importing ? 'Importing...' : 'Import Products' }}
              </button>
            </div>
        </div>
      </div>
    </div>
  </div>





    <!-- Bulk Sale Modal -->
    <div v-if="showBulkSaleModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200" style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);" @click.self="closeBulkSaleModal">
      <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-md shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 p-6 transition-all duration-300 z-10 max-h-[90vh] overflow-y-auto my-auto">
        <div class="mt-2">
          <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-3">
               <div class="bg-indigo-100 p-2 rounded-xl">
                 <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM12 8V7m0 1v1m0-1c0-1.657-1.343-3-3-3h0M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
               </div>
               <h3 class="text-xl font-semibold text-gray-900">Apply Bulk Sale</h3>
            </div>
            <button @click="closeBulkSaleModal" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-xl transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
          </div>
          <form @submit.prevent="submitBulkSale" class="space-y-5">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Apply To</label>
              <div class="relative">
                <select v-model="bulkSaleForm.apply_to" class="appearance-none block w-full px-4 py-3 border border-gray-300 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent sm:text-sm bg-white shadow-sm transition-all pr-10">
                  <option value="all">All Products in Stock</option>
                  <option value="selected" :disabled="selectedProducts.length === 0">Selected Products ({{ selectedProducts.length }})</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                </div>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Discount Type</label>
              <div class="relative">
                <select v-model="bulkSaleForm.discount_type" required class="appearance-none block w-full px-4 py-3 border border-gray-300 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent sm:text-sm bg-white shadow-sm transition-all pr-10">
                  <option value="percentage">Percentage (%)</option>
                  <option value="fixed">Fixed Amount ({{ currencyStore.symbol }})</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                </div>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Discount Value</label>
              <div class="relative">
                <input v-model="bulkSaleForm.discount_value" type="number" step="0.01" required min="0" class="block w-full px-4 py-3 border border-gray-300 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent sm:text-sm bg-white shadow-sm transition-all pr-10">
                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                  <span class="text-sm font-medium text-gray-500">{{ bulkSaleForm.discount_type === 'percentage' ? '%' : currencyStore.symbol }}</span>
                </div>
              </div>
            </div>
            <div class="flex justify-end space-x-3 pt-6 border-t mt-6 border-gray-200">
              <button type="button" @click="closeBulkSaleModal" class="px-5 py-2.5 rounded-xl text-sm font-medium border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 shadow-sm transition-all">Cancel</button>
              <button type="submit" :disabled="applyingBulkSale" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium shadow-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                <svg v-if="applyingBulkSale" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                {{ applyingBulkSale ? 'Applying...' : 'Apply Sale' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>




    <!-- Category Management Modal -->
    <div v-if="showCategoryModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200" style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);" @click.self="closeCategoryModal">
      <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-4xl shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 p-6 transition-all duration-300 z-10 max-h-[90vh] overflow-y-auto my-auto text-left">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-medium text-gray-900">Manage Categories</h3>
              <button
                @click="closeCategoryModal"
                class="text-gray-400 hover:text-gray-600"
              >
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Create/Edit Category -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="text-md font-medium text-gray-900 mb-4">
                {{ editingCategoryData ? 'Edit Category' : 'Create New Category' }}
              </h4>
              <form @submit.prevent="editingCategoryData ? updateCategory() : createCategory()">
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Category Name</label>
                  <input
                    v-model="categoryForm.name"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Enter category name"
                  />
                </div>
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                  <textarea
                    v-model="categoryForm.description"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Enter category description"
                  ></textarea>
                </div>
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Parent Category</label>
                  <select
                    v-model="categoryForm.parent_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                  >
                    <option value="">No Parent (Root Category)</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                      {{ category.name }}
                    </option>
                  </select>
                </div>
                <div class="flex justify-end space-x-3">
                  <button
                    v-if="editingCategoryData"
                    type="button"
                    @click="cancelEdit"
                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    :disabled="creatingCategory || editingCategory"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium disabled:opacity-50"
                  >
                    {{ editingCategoryData
                        ? (editingCategory ? 'Updating...' : 'Update Category')
                        : (creatingCategory ? 'Creating...' : 'Create Category')
                    }}
                  </button>
                </div>
              </form>
            </div>

            <!-- Categories List -->
            <div>
              <h4 class="text-md font-medium text-gray-900 mb-4">Existing Categories</h4>
              <div class="max-h-96 overflow-y-auto">
                <div v-if="loadingCategories" class="text-center py-4">
                  <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600 mx-auto"></div>
                </div>
                <div v-else-if="categories.length === 0" class="text-center py-4 text-gray-500">
                  No categories found.
                </div>
                <div v-else class="space-y-2">
                  <div
                    v-for="category in categories"
                    :key="category.id"
                    class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg hover:bg-gray-50"
                  >
                    <div>
                      <div class="font-medium text-gray-900">{{ category.name }}</div>
                      <div v-if="category.description" class="text-sm text-gray-500">{{ category.description }}</div>
                      <div class="text-xs text-gray-400">
                        {{ category.products_count || 0 }} products
                      </div>
                    </div>
                    <div class="flex space-x-2">
                      <button
                        @click="editCategory(category)"
                        class="text-indigo-600 hover:text-indigo-900 text-sm"
                      >
                        Edit
                      </button>
                      <button
                        @click="deleteCategory(category)"
                        class="text-red-600 hover:text-red-900 text-sm"
                      >
                        Delete
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        </div>
      </div>
    </div>

    <!-- Barcode Printer Modal -->
    <BarcodePrinter v-if="showBarcodeModal" :product="printingProduct" @close="showBarcodeModal = false" />

    <!-- Variations Modal -->
    <div v-if="showVariationsModal && selectedVariationsProduct" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200" style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);" @click.self="showVariationsModal = false">
        <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-6xl shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 p-6 transition-all duration-300 z-10 max-h-[90vh] overflow-y-auto my-auto flex flex-col">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-[#2E2E2E] pb-3 mb-4">
                <div>
                    <span class="text-[10px] font-black uppercase text-indigo-600 tracking-wider">Product Variations View</span>
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">{{ selectedVariationsProduct.name }}</h3>
                </div>
                <button type="button" @click="showVariationsModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 font-bold text-lg focus:outline-none">&times;</button>
            </div>

            <div class="w-full overflow-y-auto border border-slate-200/60 dark:border-[#2E2E2E] rounded-xl overflow-x-auto shadow-inner custom-scrollbar">
                <table class="w-full min-w-max table-auto align-middle divide-y divide-slate-100 dark:divide-[#2E2E2E] text-xs">
                    <thead class="bg-slate-50 dark:bg-[#252525]/40 text-[10px] font-bold uppercase tracking-wider text-slate-500 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-2.5 text-left bg-slate-50 dark:bg-[#252525]/40">Variant Combination</th>
                            <th class="px-4 py-2.5 text-left bg-slate-50 dark:bg-[#252525]/40">SKU Code</th>
                            <th class="px-4 py-2.5 text-left bg-slate-50 dark:bg-[#252525]/40">Barcode</th>
                            <th class="px-4 py-2.5 text-left bg-slate-50 dark:bg-[#252525]/40">Cost Price</th>
                            <th class="px-4 py-2.5 text-left bg-slate-50 dark:bg-[#252525]/40">Retail Price</th>
                            <th class="px-4 py-2.5 text-left bg-slate-50 dark:bg-[#252525]/40">Wholesale Price</th>
                            <th class="px-4 py-2.5 text-left bg-slate-50 dark:bg-[#252525]/40">Sale Price</th>
                            <th class="px-4 py-2.5 text-center bg-slate-50 dark:bg-[#252525]/40">Tax Rate</th>
                            <th class="px-4 py-2.5 text-center bg-slate-50 dark:bg-[#252525]/40">Current Stock</th>
                            <th class="px-4 py-2.5 text-center bg-slate-50 dark:bg-[#252525]/40">Min Alert</th>
                            <th class="px-4 py-2.5 text-center bg-slate-50 dark:bg-[#252525]/40">Unit</th>
                            <th class="px-4 py-2.5 text-left bg-slate-50 dark:bg-[#252525]/40">Batch Number</th>
                            <th class="px-4 py-2.5 text-left bg-slate-50 dark:bg-[#252525]/40">Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-[#2E2E2E]/60 text-[11px]">
                        <tr v-if="!selectedVariationsProduct.variations || selectedVariationsProduct.variations.length === 0">
                            <td colspan="13" class="text-center py-6 text-xs text-slate-400 dark:text-zinc-500 italic">No variations found.</td>
                        </tr>
                        <tr v-else v-for="(variant, idx) in selectedVariationsProduct.variations" :key="idx" class="hover:bg-slate-50/60 dark:hover:bg-[#2D2D2D]/30 transition-colors">
                            <!-- Variant Combination -->
                            <td class="px-4 py-2.5 font-bold text-slate-800 dark:text-zinc-200">{{ variant.name_string || variant.variation_name_string || variant.combination_key || '-' }}</td>
                            
                            <!-- SKU -->
                            <td class="px-4 py-2.5 font-mono text-slate-500 tracking-tight">{{ variant.sku || '-' }}</td>
                            
                            <!-- Barcode -->
                            <td class="px-4 py-2.5 font-mono text-slate-500 tracking-tight">{{ variant.barcode || '-' }}</td>
                            
                            <!-- Cost Price -->
                            <td class="px-4 py-2.5 font-semibold text-slate-600 dark:text-zinc-400">
                                {{ currencyStore.symbol }} {{ parseFloat(variant.cost_price || 0).toFixed(2) }}
                            </td>
                            
                            <!-- Retail Price -->
                            <td class="px-4 py-2.5 font-semibold text-emerald-600">
                                {{ currencyStore.symbol }} {{ parseFloat(variant.retail_price || variant.selling_price || 0).toFixed(2) }}
                            </td>

                            <!-- Wholesale Price -->
                            <td class="px-4 py-2.5 font-semibold text-indigo-500">
                                {{ currencyStore.symbol }} {{ parseFloat(variant.wholesale_price || 0).toFixed(2) }}
                            </td>

                            <!-- Sale Price -->
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-1.5">
                                    <span v-if="variant.on_sale" class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">Sale</span>
                                    <span class="font-semibold text-amber-600">
                                        {{ currencyStore.symbol }} {{ parseFloat(variant.sale_price || 0).toFixed(2) }}
                                    </span>
                                </div>
                            </td>

                            <!-- Tax Rate -->
                            <td class="px-4 py-2.5 text-center font-medium text-slate-600 dark:text-zinc-400">
                                {{ variant.tax_rate !== null && variant.tax_rate !== undefined ? variant.tax_rate + '%' : '-' }}
                            </td>

                            <!-- Stock -->
                            <td class="px-4 py-2.5 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full font-bold text-[10px]"
                                      :class="(variant.stock_qty > 0 || variant.stock_quantity > 0) ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10' : 'bg-rose-50 text-rose-600 dark:bg-rose-500/10'">
                                    {{ variant.stock_qty !== undefined ? variant.stock_qty : (variant.stock_quantity !== undefined ? variant.stock_quantity : 0) }}
                                </span>
                            </td>

                            <!-- Min Stock Alert -->
                            <td class="px-4 py-2.5 text-center font-medium text-slate-500 dark:text-zinc-400">
                                {{ variant.min_stock_alert !== null && variant.min_stock_alert !== undefined ? variant.min_stock_alert : '-' }}
                            </td>

                            <!-- Unit of Measure -->
                            <td class="px-4 py-2.5 text-center font-medium text-slate-600 dark:text-zinc-400">
                                {{ variant.unit_of_measure || '-' }}
                            </td>

                            <!-- Batch Number -->
                            <td class="px-4 py-2.5 text-slate-600 dark:text-zinc-400 font-mono">{{ variant.batch_number || '-' }}</td>

                            <!-- Expiry Date -->
                            <td class="px-4 py-2.5 text-slate-600 dark:text-zinc-400">{{ variant.expiry_date || '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="button" @click="showVariationsModal = false" class="px-4 py-1.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 dark:bg-[#252525] dark:text-zinc-200 rounded-xl transition-all">Close View</button>
            </div>
        </div>
    </div>

    <!-- Success Toast -->
    <transition
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="toast.show" class="fixed top-20 right-5 max-w-sm w-full bg-[#0f172a] text-slate-50 shadow-2xl rounded-2xl pointer-events-auto overflow-hidden z-[100] border border-white/5">
        <div class="px-5 py-4">
          <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 flex-1 min-w-0">
              <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <p v-if="toast.title" class="text-xs font-bold leading-normal text-white dark:text-white" style="color: #ffffff !important;">{{ toast.title }}</p>
                <p class="text-[11px] font-semibold leading-relaxed text-slate-300 dark:text-slate-300" style="color: #cbd5e1 !important;">{{ toast.message }}</p>
              </div>
            </div>
            <button type="button" @click="toast.show = false" class="flex-shrink-0 p-1 rounded-md text-slate-400 hover:text-white hover:bg-white/10 transition-all focus:outline-none cursor-pointer">
              <span class="sr-only">Close</span>
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Table Item Image Lightbox Gallery Modal -->
    <div 
      v-if="showLightbox" 
      class="fixed inset-0 z-[99999] flex flex-col items-center justify-center bg-black/85 backdrop-blur-md p-4 animate-in fade-in duration-150 select-none"
      @click.self="closeLightbox"
    >
      <div class="relative max-w-4xl max-h-[90vh] flex flex-col items-center justify-center group/lightbox w-full">
        <!-- Top Header Bar / Badge & Close -->
        <div class="w-full flex justify-between items-center mb-3 px-1">
          <div class="flex items-center gap-2 bg-slate-900/90 backdrop-blur-md px-3.5 py-1.5 rounded-full text-white text-xs font-bold shadow-lg border border-white/10">
            <span class="truncate max-w-[260px]">{{ lightboxTitle }}</span>
            <span v-if="lightboxImages.length > 1" class="text-slate-400 font-medium">| {{ lightboxIndex + 1 }} of {{ lightboxImages.length }}</span>
          </div>
          <button 
            type="button" 
            @click="closeLightbox" 
            class="p-2 text-white/80 hover:text-white bg-slate-900/90 hover:bg-slate-900 rounded-full transition-all backdrop-blur-md shadow-lg border border-white/10 cursor-pointer"
            title="Close Gallery (Esc)"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <!-- Main Image Canvas -->
        <div class="relative flex items-center justify-center max-h-[80vh] max-w-[90vw] overflow-hidden rounded-2xl shadow-2xl border border-white/10 bg-slate-950/90 w-full">
          <img 
            :src="lightboxImages[lightboxIndex]" 
            :alt="lightboxTitle"
            class="max-h-[80vh] max-w-[90vw] object-contain select-none transition-all duration-200"
          />

          <!-- Left Navigation Arrow -->
          <button 
            v-if="lightboxImages.length > 1"
            type="button" 
            @click.stop="prevLightboxImage" 
            class="absolute left-3 top-1/2 -translate-y-1/2 p-3 text-white bg-slate-900/80 hover:bg-slate-900 hover:scale-110 rounded-full transition-all backdrop-blur-md shadow-xl border border-white/10 cursor-pointer"
            title="Previous Image (Left Arrow)"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
          </button>

          <!-- Right Navigation Arrow -->
          <button 
            v-if="lightboxImages.length > 1"
            type="button" 
            @click.stop="nextLightboxImage" 
            class="absolute right-3 top-1/2 -translate-y-1/2 p-3 text-white bg-slate-900/80 hover:bg-slate-900 hover:scale-110 rounded-full transition-all backdrop-blur-md shadow-xl border border-white/10 cursor-pointer"
            title="Next Image (Right Arrow)"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
          </button>
        </div>

        <!-- Bottom Thumbnail Dots / Strip -->
        <div v-if="lightboxImages.length > 1" class="flex items-center gap-2 mt-4 px-3 py-1.5 bg-slate-900/80 backdrop-blur-md rounded-2xl border border-white/10 max-w-full overflow-x-auto scrollbar-hidden">
          <div 
            v-for="(img, idx) in lightboxImages" 
            :key="idx"
            @click="lightboxIndex = idx"
            class="w-10 h-10 rounded-xl overflow-hidden cursor-pointer border-2 transition-all shrink-0"
            :class="idx === lightboxIndex ? 'border-emerald-400 scale-110' : 'border-transparent opacity-60 hover:opacity-100'"
          >
            <img :src="img" class="w-full h-full object-cover">
          </div>
        </div>
      </div>
    </div>

    <!-- Slide-over Filter Drawer Panel -->
    <teleport to="body">
      <!-- Backdrop Overlay -->
      <transition
        enter-active-class="transition-opacity ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="showFilterDrawer"
          class="fixed inset-0 bg-slate-900/40 dark:bg-black/60 backdrop-blur-xs z-[9990]"
          @click="showFilterDrawer = false"
        ></div>
      </transition>

      <!-- Slide-over Panel -->
      <transition
        enter-active-class="transform transition ease-out duration-300"
        enter-from-class="translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transform transition ease-in duration-200"
        leave-from-class="translate-x-0"
        leave-to-class="translate-x-full"
      >
        <div
          v-if="showFilterDrawer"
          class="fixed inset-y-0 right-0 z-[9995] w-full max-w-md bg-white dark:bg-zinc-900 shadow-2xl flex flex-col border-l border-slate-200 dark:border-zinc-800"
          @click.stop
        >
          <!-- Drawer Header -->
          <div class="p-5 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between">
            <div class="flex items-center space-x-2">
              <h2 class="text-base font-bold text-slate-900 dark:text-zinc-100">Filter Products</h2>
              <span v-if="totalActiveFilterCount > 0" class="text-xs font-extrabold bg-emerald-600 text-white px-2 py-0.5 rounded-full">
                {{ totalActiveFilterCount }}
              </span>
            </div>
            <button
              @click="showFilterDrawer = false"
              class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Active Filter Counter Banner -->
          <div v-if="totalActiveFilterCount > 0" class="px-5 py-2.5 bg-slate-50 dark:bg-zinc-800/40 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between text-xs">
            <span class="text-slate-600 dark:text-zinc-300 font-medium">
              <strong class="text-slate-900 dark:text-zinc-100 font-bold">{{ totalActiveFilterCount }}</strong> active filter{{ totalActiveFilterCount > 1 ? 's' : '' }} applied
            </span>
            <button
              @click="clearFilters"
              class="text-rose-600 dark:text-rose-400 hover:underline font-semibold cursor-pointer"
            >
              Clear All
            </button>
          </div>

          <!-- Drawer Body -->
          <div class="flex-1 overflow-y-auto p-5 space-y-5 custom-scrollbar">

            <!-- Item Type Filter (Multi-Select Floating Dropdown) -->
            <div class="space-y-1.5 relative" @click.stop>
              <div class="flex items-center justify-between">
                <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
                  Item Type
                </label>
                <button
                  v-if="Array.isArray(tableFilters.item_type) && tableFilters.item_type.length > 0"
                  type="button"
                  @click="clearItemTypes"
                  class="text-[11px] font-semibold text-rose-600 dark:text-rose-400 hover:underline cursor-pointer"
                >
                  Clear
                </button>
              </div>

              <div class="relative">
                <button
                  type="button"
                  @click="activeFilterPopover = activeFilterPopover === 'item_type' ? null : 'item_type'"
                  class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-800 dark:text-zinc-100 flex items-center justify-between focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all cursor-pointer"
                  :class="{ 'border-emerald-500 ring-2 ring-emerald-500/20 bg-white dark:bg-zinc-800': activeFilterPopover === 'item_type' }"
                >
                  <span class="truncate pr-2" :class="{ 'text-slate-400 dark:text-zinc-500': !tableFilters.item_type || tableFilters.item_type.length === 0 }">
                    {{ getItemTypeDropdownLabel() }}
                  </span>
                  <div class="flex items-center gap-1.5 shrink-0">
                    <span 
                      v-if="Array.isArray(tableFilters.item_type) && tableFilters.item_type.length > 0"
                      class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300"
                    >
                      {{ tableFilters.item_type.length }}
                    </span>
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                  </div>
                </button>

                <!-- Floating Popover Menu with Multi-Select Checkboxes -->
                <div
                  v-if="activeFilterPopover === 'item_type'"
                  class="absolute left-0 right-0 top-full mt-1 z-[9999] bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-2xl p-1.5 flex flex-col gap-0.5 animate-fade-in"
                  @click.stop
                >
                  <button
                    type="button"
                    @click="clearItemTypes"
                    class="w-full px-2.5 py-1.5 text-left hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center justify-between text-xs rounded-lg cursor-pointer"
                    :class="{ 'bg-emerald-50/70 dark:bg-zinc-800 font-bold text-emerald-700 dark:text-emerald-400': !tableFilters.item_type || tableFilters.item_type.length === 0 }"
                  >
                    <span>All Item Types</span>
                    <svg v-if="!tableFilters.item_type || tableFilters.item_type.length === 0" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                  </button>

                  <div class="h-px bg-slate-100 dark:bg-zinc-800 my-0.5"></div>

                  <label
                    v-for="tOpt in itemTypeOptions"
                    :key="tOpt.value"
                    class="flex items-center justify-between gap-2 px-2.5 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-zinc-800/80 cursor-pointer transition-colors text-xs select-none"
                  >
                    <div class="flex items-center gap-2 min-w-0">
                      <input
                        type="checkbox"
                        :checked="isItemTypeSelected(tOpt.value)"
                        @change="toggleItemType(tOpt.value)"
                        class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 dark:border-zinc-600 dark:bg-zinc-700 cursor-pointer shrink-0"
                      />
                      <span class="font-medium text-slate-800 dark:text-zinc-200 truncate">{{ tOpt.label }}</span>
                    </div>
                    <span
                      class="px-1.5 py-0.5 rounded text-[9px] font-bold border uppercase shrink-0 ml-1"
                      :class="getItemTypeBadgeClass(tOpt.value)"
                    >
                      {{ getItemTypeName(tOpt.value, true) }}
                    </span>
                  </label>
                </div>
              </div>
            </div>

            <!-- 1. Category (Floating Searchable Dropdown) -->
            <div class="space-y-1.5 relative" @click.stop>
              <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
                Category
              </label>
              <div class="relative">
                <button
                  type="button"
                  @click="activeFilterPopover = activeFilterPopover === 'category' ? null : 'category'"
                  class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-800 dark:text-zinc-100 flex items-center justify-between focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all cursor-pointer"
                  :class="{ 'border-emerald-500 ring-2 ring-emerald-500/20 bg-white dark:bg-zinc-800': activeFilterPopover === 'category' }"
                >
                  <span class="truncate pr-2" :class="{ 'text-slate-400 dark:text-zinc-500': !tableFilters.category_id }">
                    {{ getCategoryName(tableFilters.category_id) || 'All Categories' }}
                  </span>
                  <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <!-- Floating Popover Menu -->
                <div
                  v-if="activeFilterPopover === 'category'"
                  class="absolute left-0 right-0 top-full mt-1 z-[9999] bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-2xl py-1 flex flex-col animate-fade-in"
                >
                  <!-- Search Header -->
                  <div class="p-2 border-b border-slate-100 dark:border-zinc-800 shrink-0">
                    <div class="relative">
                      <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                      </span>
                      <input
                        v-model="categorySearchQuery"
                        type="text"
                        placeholder="Search categories..."
                        class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-emerald-500 transition-all placeholder:text-slate-400"
                        @click.stop
                      />
                    </div>
                  </div>

                  <!-- Options List -->
                  <div class="overflow-y-auto max-h-48 custom-scrollbar">
                    <button
                      type="button"
                      @click="selectCategory(''); activeFilterPopover = null"
                      class="w-full px-3 py-2 text-left hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center justify-between text-xs border-b border-slate-50 dark:border-zinc-800/40 cursor-pointer"
                      :class="{ 'bg-emerald-50/70 dark:bg-zinc-800 font-bold text-emerald-700 dark:text-emerald-400': tableFilters.category_id === '' }"
                    >
                      <span>All Categories</span>
                    </button>

                    <div v-if="filteredCategories.length === 0" class="py-3 text-center text-slate-400 text-xs italic">
                      No matching categories found.
                    </div>

                    <button
                      v-for="cat in filteredCategories"
                      :key="cat.id"
                      type="button"
                      @click="selectCategory(cat.id); activeFilterPopover = null"
                      class="w-full px-3 py-2 text-left hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center justify-between text-xs border-b border-slate-50 dark:border-zinc-800/40 cursor-pointer"
                      :class="{ 'bg-emerald-50/70 dark:bg-zinc-800 font-bold text-emerald-700 dark:text-emerald-400': tableFilters.category_id === cat.id }"
                    >
                      <span class="truncate text-slate-800 dark:text-zinc-200">{{ cat.name }}</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- 2. Brand (Floating Searchable Dropdown) -->
            <div class="space-y-1.5 relative" @click.stop>
              <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
                Brand
              </label>
              <div class="relative">
                <button
                  type="button"
                  @click="activeFilterPopover = activeFilterPopover === 'brand' ? null : 'brand'"
                  class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-800 dark:text-zinc-100 flex items-center justify-between focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all cursor-pointer"
                  :class="{ 'border-emerald-500 ring-2 ring-emerald-500/20 bg-white dark:bg-zinc-800': activeFilterPopover === 'brand' }"
                >
                  <span class="truncate pr-2" :class="{ 'text-slate-400 dark:text-zinc-500': !tableFilters.brand_id }">
                    {{ getBrandName(tableFilters.brand_id) || 'All Brands' }}
                  </span>
                  <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <!-- Floating Popover Menu -->
                <div
                  v-if="activeFilterPopover === 'brand'"
                  class="absolute left-0 right-0 top-full mt-1 z-[9999] bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-2xl py-1 flex flex-col animate-fade-in"
                >
                  <!-- Search Header -->
                  <div class="p-2 border-b border-slate-100 dark:border-zinc-800 shrink-0">
                    <div class="relative">
                      <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                      </span>
                      <input
                        v-model="brandSearchQuery"
                        type="text"
                        placeholder="Search brands..."
                        class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-emerald-500 transition-all placeholder:text-slate-400"
                        @click.stop
                      />
                    </div>
                  </div>

                  <!-- Options List -->
                  <div class="overflow-y-auto max-h-48 custom-scrollbar">
                    <button
                      type="button"
                      @click="selectBrand(''); activeFilterPopover = null"
                      class="w-full px-3 py-2 text-left hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center justify-between text-xs border-b border-slate-50 dark:border-zinc-800/40 cursor-pointer"
                      :class="{ 'bg-emerald-50/70 dark:bg-zinc-800 font-bold text-emerald-700 dark:text-emerald-400': tableFilters.brand_id === '' }"
                    >
                      <span>All Brands</span>
                    </button>

                    <div v-if="filteredBrands.length === 0" class="py-3 text-center text-slate-400 text-xs italic">
                      No matching brands found.
                    </div>

                    <button
                      v-for="b in filteredBrands"
                      :key="b.id"
                      type="button"
                      @click="selectBrand(b.id); activeFilterPopover = null"
                      class="w-full px-3 py-2 text-left hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center justify-between text-xs border-b border-slate-50 dark:border-zinc-800/40 cursor-pointer"
                      :class="{ 'bg-emerald-50/70 dark:bg-zinc-800 font-bold text-emerald-700 dark:text-emerald-400': tableFilters.brand_id === b.id }"
                    >
                      <span class="truncate text-slate-800 dark:text-zinc-200">{{ b.name }}</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- 3. Sort by Price (Floating Dropdown) -->
            <div class="space-y-1.5 relative" @click.stop>
              <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
                Price Sorting
              </label>
              <div class="relative">
                <button
                  type="button"
                  @click="activeFilterPopover = activeFilterPopover === 'price' ? null : 'price'"
                  class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-800 dark:text-zinc-100 flex items-center justify-between focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all cursor-pointer"
                  :class="{ 'border-emerald-500 ring-2 ring-emerald-500/20 bg-white dark:bg-zinc-800': activeFilterPopover === 'price' }"
                >
                  <span class="truncate pr-2">
                    {{ getPriceSortName(tableFilters.price_sort) }}
                  </span>
                  <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <!-- Floating Popover Menu -->
                <div
                  v-if="activeFilterPopover === 'price'"
                  class="absolute left-0 right-0 top-full mt-1 z-[9999] bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-2xl py-1 flex flex-col animate-fade-in"
                >
                  <button
                    type="button"
                    @click="selectPriceSort(''); activeFilterPopover = null"
                    class="w-full px-3 py-2 text-left hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center justify-between text-xs border-b border-slate-50 dark:border-zinc-800/40 cursor-pointer"
                    :class="{ 'bg-emerald-50/70 dark:bg-zinc-800 font-bold text-emerald-700 dark:text-emerald-400': tableFilters.price_sort === '' }"
                  >
                    <span>Sort by Price (Default)</span>
                  </button>

                  <button
                    type="button"
                    @click="selectPriceSort('asc'); activeFilterPopover = null"
                    class="w-full px-3 py-2 text-left hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center justify-between text-xs border-b border-slate-50 dark:border-zinc-800/40 cursor-pointer"
                    :class="{ 'bg-emerald-50/70 dark:bg-zinc-800 font-bold text-emerald-700 dark:text-emerald-400': tableFilters.price_sort === 'asc' }"
                  >
                    <span>Price: Low to High</span>
                  </button>

                  <button
                    type="button"
                    @click="selectPriceSort('desc'); activeFilterPopover = null"
                    class="w-full px-3 py-2 text-left hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center justify-between text-xs cursor-pointer"
                    :class="{ 'bg-emerald-50/70 dark:bg-zinc-800 font-bold text-emerald-700 dark:text-emerald-400': tableFilters.price_sort === 'desc' }"
                  >
                    <span>Price: High to Low</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- 4. On Sale Only -->
            <div class="pt-2 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between cursor-pointer" @click="toggleOnSaleFilter">
              <div>
                <label class="block text-xs font-bold text-slate-800 dark:text-zinc-200 cursor-pointer">
                  On Sale Only
                </label>
                <p class="text-[11px] text-slate-400 dark:text-zinc-500">Show only items with discount values</p>
              </div>
              <button
                type="button"
                :class="tableFilters.on_sale ? 'bg-emerald-600' : 'bg-slate-200 dark:bg-zinc-700'"
                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
              >
                <span
                  :class="tableFilters.on_sale ? 'translate-x-4' : 'translate-x-0'"
                  class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out"
                />
              </button>
            </div>

            <!-- 5. Inactive Items -->
            <div class="pt-2 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between cursor-pointer" @click="toggleInactiveFilter">
              <div>
                <label class="block text-xs font-bold text-slate-800 dark:text-zinc-200 cursor-pointer">
                  Inactive Items
                </label>
                <p class="text-[11px] text-slate-400 dark:text-zinc-500">Include disabled / inactive products in list</p>
              </div>
              <button
                type="button"
                :class="tableFilters.show_inactive ? 'bg-amber-500' : 'bg-slate-200 dark:bg-zinc-700'"
                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
              >
                <span
                  :class="tableFilters.show_inactive ? 'translate-x-4' : 'translate-x-0'"
                  class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out"
                />
              </button>
            </div>

          </div>

          <!-- Drawer Footer -->
          <div class="p-5 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between gap-3 bg-slate-50/50 dark:bg-zinc-900">
            <button
              @click="clearFilters"
              class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-100 border border-slate-200 dark:border-zinc-700 rounded-xl transition-all cursor-pointer"
            >
              Reset
            </button>
            <button
              @click="showFilterDrawer = false"
              class="px-5 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 rounded-xl shadow-xs transition-all cursor-pointer"
            >
              Apply Filters
            </button>
          </div>
        </div>
      </transition>
    </teleport>
  </div>
</div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { debounce } from '@/utils/debounce';
import BarcodePrinter from '@/components/common/BarcodePrinter.vue';
import ProductViewModal from './ProductViewModal.vue';
import ProductDraftsModal from './ProductDraftsModal.vue';
import { useCurrencyStore } from '@/stores/currency';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const router = useRouter();

const authStore = useAuthStore();
const viewMode = ref(localStorage.getItem('pos_products_view_mode') || 'table');
watch(viewMode, (newVal) => {
  localStorage.setItem('pos_products_view_mode', newVal);
});
const currencyStore = useCurrencyStore();
const { showToast: triggerToast } = useToast();
const { confirm } = useConfirm();

const showToast = (typeOrMsg, msgOrType) => {
  if (typeOrMsg === 'error' || typeOrMsg === 'success' || typeOrMsg === 'warning' || typeOrMsg === 'info') {
    triggerToast(msgOrType, typeOrMsg);
  } else {
    triggerToast(typeOrMsg, msgOrType || 'info');
  }
};

// New Reactive states for Dropdowns, Favoriting and Filter Drawer
const isFavorite = ref(false);
const showSalesPurchaseDropdown = ref(false);
const showOptionsDropdown = ref(false);
const showFilterDrawer = ref(false);
const showRowsDropdown = ref(false);
const showBottomRowsDropdown = ref(false);
const activeFilterPopover = ref(null);
const categorySearchQuery = ref('');
const brandSearchQuery = ref('');

const itemTypeOptions = [
  { label: 'Standard Product', value: 'standard' },
  { label: 'Raw Material', value: 'raw_material' },
  { label: 'Finished Good (Manufactured)', value: 'finished_good' },
  { label: 'Fixed Asset', value: 'fixed_asset' },
  { label: 'Service', value: 'service' }
];

const totalActiveFilterCount = computed(() => {
  let count = 0;
  if (Array.isArray(tableFilters.value.item_type)) {
    count += tableFilters.value.item_type.length;
  } else if (tableFilters.value.item_type) {
    count++;
  }
  if (tableFilters.value.category_id) count++;
  if (tableFilters.value.brand_id) count++;
  if (tableFilters.value.price_sort) count++;
  if (tableFilters.value.on_sale) count++;
  if (tableFilters.value.show_inactive) count++;
  return count;
});

// Reactive data
const loading = ref(false);
const products = ref([]);
const categories = ref([]);
const brands = ref([]);

const filteredCategories = computed(() => {
  if (!categorySearchQuery.value) return categories.value;
  const q = categorySearchQuery.value.toLowerCase();
  return categories.value.filter(c => c.name && c.name.toLowerCase().includes(q));
});

const filteredBrands = computed(() => {
  if (!brandSearchQuery.value) return brands.value;
  const q = brandSearchQuery.value.toLowerCase();
  return brands.value.filter(b => b.name && b.name.toLowerCase().includes(q));
});
// Lightbox State
const showLightbox = ref(false);
const lightboxImages = ref([]);
const lightboxIndex = ref(0);
const lightboxTitle = ref('');

const getImageUrl = (url) => {
  if (!url || typeof url !== 'string') return '';
  if (url.includes('Temp') || url.includes('.tmp')) return '';
  return url.startsWith('/') || url.startsWith('http') ? url : '/' + url;
};

const getProductDisplayImage = (item) => {
  if (!item) return '';
  if (item.image && typeof item.image === 'string' && !item.image.includes('Temp') && !item.image.includes('.tmp')) {
    return getImageUrl(item.image);
  }
  if (item.images) {
    let imagesArr = item.images;
    if (typeof imagesArr === 'string') {
      try {
        imagesArr = JSON.parse(imagesArr);
      } catch (e) {}
    }
    if (Array.isArray(imagesArr) && imagesArr.length > 0) {
      const first = imagesArr[0];
      const src = typeof first === 'string' ? first : (first.url || first.preview || first.path || first.image || '');
      if (src && !src.includes('Temp') && !src.includes('.tmp')) {
        return getImageUrl(src);
      }
    }
  }
  return '';
};

const openLightbox = (item) => {
  console.log('[openLightbox] CALLED WITH ITEM:', item);
  if (!item) {
    console.error('[openLightbox] No item provided!');
    return;
  }
  const imgs = [];
  
  const processImageValue = (val) => {
    if (!val) return;
    if (typeof val === 'string') {
      const trimmed = val.trim();
      if (!trimmed || trimmed === 'null' || trimmed === 'undefined' || trimmed === '[]') return;
      try {
        const parsed = JSON.parse(trimmed);
        if (Array.isArray(parsed)) {
          parsed.forEach(p => processImageValue(p));
          return;
        }
      } catch (e) {}
      const formatted = getImageUrl(trimmed);
      if (formatted && !imgs.includes(formatted)) {
        imgs.push(formatted);
      }
    } else if (Array.isArray(val)) {
      val.forEach(v => processImageValue(v));
    } else if (typeof val === 'object') {
      const src = val.url || val.path || val.preview || val.image || val.src || '';
      if (src) processImageValue(src);
    }
  };

  if (item.images) {
    processImageValue(item.images);
  }
  if (item.image) {
    processImageValue(item.image);
  }
  
  const displayImg = getProductDisplayImage(item);
  if (displayImg && !imgs.includes(displayImg)) {
    imgs.unshift(displayImg);
  }
  
  console.log('[openLightbox] Extracted imgs array:', imgs);

  if (imgs.length === 0) {
    console.warn('[openLightbox] No valid images found for item:', item.name);
    return;
  }
  
  lightboxImages.value = imgs;
  lightboxIndex.value = 0;
  lightboxTitle.value = item.name || 'Product Image';
  showLightbox.value = true;
  console.log('[openLightbox] showLightbox set to TRUE. Title:', lightboxTitle.value, 'Images:', lightboxImages.value);
};

const closeLightbox = () => {
  showLightbox.value = false;
};

const nextLightboxImage = () => {
  if (lightboxImages.value.length <= 1) return;
  lightboxIndex.value = (lightboxIndex.value + 1) % lightboxImages.value.length;
};

const prevLightboxImage = () => {
  if (lightboxImages.value.length <= 1) return;
  lightboxIndex.value = (lightboxIndex.value - 1 + lightboxImages.value.length) % lightboxImages.value.length;
};

const handleLightboxKeydown = (e) => {
  if (!showLightbox.value) return;
  if (e.key === 'Escape') closeLightbox();
  if (e.key === 'ArrowRight') nextLightboxImage();
  if (e.key === 'ArrowLeft') prevLightboxImage();
};

watch(showLightbox, (val) => {
  if (val) {
    window.addEventListener('keydown', handleLightboxKeydown);
  } else {
    window.removeEventListener('keydown', handleLightboxKeydown);
  }
});

const showViewModal = ref(false);
const showCategoryModal = ref(false);
const showImportModal = ref(false);
const viewingProduct = ref(null);

const isDraftsModalOpen = ref(false);
const draftProducts = ref([]);
const draftsCount = ref(0);
const isLoadingDrafts = ref(false);
const selectedDraftIds = ref([]);

const fetchDraftsData = async () => {
  isLoadingDrafts.value = true;
  try {
    const response = await axios.get('/api/products/drafts-summary');
    if (response.data && response.data.success) {
      draftProducts.value = response.data.drafts || [];
      draftsCount.value = draftProducts.value.length;
    }
  } catch (error) {
    console.error('Error fetching drafts:', error);
  } finally {
    isLoadingDrafts.value = false;
  }
};

const openDraftsModal = async () => {
  isDraftsModalOpen.value = true;
  selectedDraftIds.value = [];
  await fetchDraftsData();
};

const toggleSelectAllDrafts = () => {
  if (selectedDraftIds.value.length === draftProducts.value.length) {
    selectedDraftIds.value = [];
  } else {
    selectedDraftIds.value = draftProducts.value.map(d => d.id);
  }
};

const deleteSelectedDrafts = async () => {
  if (selectedDraftIds.value.length === 0) return;
  const confirmed = await confirm({
    title: 'Delete Draft Items?',
    message: `Are you absolutely sure you want to permanently delete the ${selectedDraftIds.value.length} selected draft item(s)?`,
    confirmText: 'Yes, Delete',
    cancelText: 'Cancel',
    type: 'danger'
  });
  if (!confirmed) return;

  try {
    const response = await axios.post('/api/products/drafts/bulk-destroy', {
      ids: selectedDraftIds.value
    });
    if (response.data && response.data.success) {
      selectedDraftIds.value = [];
      showToast('success', 'Draft items deleted successfully');
      await fetchDraftsData();
      await fetchProductsForTable();
    }
  } catch (error) {
    showToast('error', error.response?.data?.message || 'Failed to delete selected drafts');
  }
};
const printingProduct = ref(null);
const showBarcodeModal = ref(false);

const showVariationsModal = ref(false);
const selectedVariationsProduct = ref(null);
const openVariationsModal = (item) => {
    selectedVariationsProduct.value = item;
    showVariationsModal.value = true;
};

const selectedProducts = ref([]);
const showBulkSaleModal = ref(false);
const applyingBulkSale = ref(false);
const bulkSaleForm = ref({
  apply_to: 'all',
  discount_type: 'percentage',
  discount_value: 0
});

const toast = ref({
  show: false,
  title: '',
  message: ''
});

const showToastNotification = (title, message) => {
  toast.value = { show: true, title, message };
  setTimeout(() => {
    toast.value.show = false;
  }, 4000);
};

// Import/Export related
const importing = ref(false);
const selectedFile = ref(null);
const importResults = ref(null);
const fileInput = ref(null);

// Category management
const loadingCategories = ref(false);
const creatingCategory = ref(false);
const editingCategory = ref(false);
const editingCategoryData = ref(null);
const categoryForm = ref({
  name: '',
  description: '',
  parent_id: ''
});

// Table pagination
const tablePagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
});

// Table columns configuration
const tableColumns = ref([
  {
    key: 'product',
    label: 'Product',
    sortable: true,
    align: 'left'
  },
  {
    key: 'category',
    label: 'Category',
    sortable: true,
    align: 'left'
  },
  {
    key: 'tags',
    label: 'Tags',
    sortable: false,
    align: 'center'
  },
  {
    key: 'prices',
    label: 'Prices (W / R)',
    sortable: false,
    align: 'center'
  },
  {
    key: 'stock',
    label: 'Stock',
    sortable: true,
    align: 'center'
  },
  {
    key: 'status',
    label: 'Status',
    sortable: true,
    align: 'center'
  },
  {
    key: 'actions',
    label: 'Actions',
    sortable: false,
    align: 'center'
  }
]);

// Table filters
const tableFilters = ref({
  search: '',
  sort_field: '',
  sort_order: '',
  item_type: [],
  category_id: '',
  brand_id: '',
  price_sort: '',
  on_sale: false,
  show_inactive: false
});

// Methods

const dropdownOpen = ref({
  category: false,
  brand: false,
  price: false
});

const toggleDropdown = (type) => {
  dropdownOpen.value[type] = !dropdownOpen.value[type];
  if (type === 'category') {
    dropdownOpen.value.brand = false;
    dropdownOpen.value.price = false;
  } else if (type === 'brand') {
    dropdownOpen.value.category = false;
    dropdownOpen.value.price = false;
  } else if (type === 'price') {
    dropdownOpen.value.category = false;
    dropdownOpen.value.brand = false;
  }
};

const closeDropdowns = () => {
  dropdownOpen.value.category = false;
  dropdownOpen.value.brand = false;
  dropdownOpen.value.price = false;
  showSalesPurchaseDropdown.value = false;
  showOptionsDropdown.value = false;
  showRowsDropdown.value = false;
  showBottomRowsDropdown.value = false;
  activeFilterPopover.value = null;
};

const toggleItemType = (val) => {
  if (!Array.isArray(tableFilters.value.item_type)) {
    tableFilters.value.item_type = tableFilters.value.item_type ? [tableFilters.value.item_type] : [];
  }
  const index = tableFilters.value.item_type.indexOf(val);
  if (index > -1) {
    tableFilters.value.item_type.splice(index, 1);
  } else {
    tableFilters.value.item_type.push(val);
  }
  fetchProductsForTable(1);
};

const isItemTypeSelected = (val) => {
  if (Array.isArray(tableFilters.value.item_type)) {
    return tableFilters.value.item_type.includes(val);
  }
  return tableFilters.value.item_type === val;
};

const clearItemTypes = () => {
  tableFilters.value.item_type = [];
  fetchProductsForTable(1);
};

const getItemTypeDropdownLabel = () => {
  if (!tableFilters.value.item_type || !Array.isArray(tableFilters.value.item_type) || tableFilters.value.item_type.length === 0) {
    return 'All Item Types';
  }
  if (tableFilters.value.item_type.length === 1) {
    return getItemTypeName(tableFilters.value.item_type[0]);
  }
  return `${tableFilters.value.item_type.length} Item Types Selected`;
};

const selectCategory = (id) => {
  tableFilters.value.category_id = id;
  dropdownOpen.value.category = false;
  fetchProductsForTable(1);
};

const selectBrand = (id) => {
  tableFilters.value.brand_id = id;
  dropdownOpen.value.brand = false;
  fetchProductsForTable(1);
};

const selectPriceSort = (sort) => {
  tableFilters.value.price_sort = sort;
  dropdownOpen.value.price = false;
  handlePriceSortChange();
};

const getItemTypeName = (val, short = false) => {
  if (!val) val = 'standard';
  switch (val) {
    case 'raw_material': return 'Raw Material';
    case 'finished_good': return short ? 'Finished Good' : 'Finished Good (Manufactured)';
    case 'fixed_asset': return 'Fixed Asset';
    case 'service': return 'Service';
    case 'standard': default: return short ? 'Standard' : 'Standard Product';
  }
};

const getItemTypeBadgeClass = (itemType) => {
  switch (itemType) {
    case 'raw_material':
      return 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-900/40';
    case 'finished_good':
      return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-900/40';
    case 'fixed_asset':
      return 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200 dark:border-purple-900/40';
    case 'service':
      return 'bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300 border-sky-200 dark:border-sky-900/40';
    default:
      return 'bg-slate-100 text-slate-700 dark:bg-[#252525] dark:text-slate-300 border-slate-200/60 dark:border-[#2E2E2E]';
  }
};

const getCategoryName = (id) => {
  if (!id) return '';
  const category = categories.value.find(c => c.id === id);
  return category ? category.name : '';
};

const getBrandName = (id) => {
  if (!id) return '';
  const brand = brands.value.find(b => b.id === id);
  return brand ? brand.name : '';
};

const getPriceSortName = (sort) => {
  if (sort === 'asc') return 'Low to High';
  if (sort === 'desc') return 'High to Low';
  return 'Sort by Price';
};

const handleFilterChange = () => {
  fetchProductsForTable(1);
};

const toggleOnSaleFilter = () => {
  tableFilters.value.on_sale = !tableFilters.value.on_sale;
  fetchProductsForTable(1);
};

const toggleInactiveFilter = () => {
  tableFilters.value.show_inactive = !tableFilters.value.show_inactive;
  fetchProductsForTable(1);
};

const handlePriceSortChange = () => {
  if (tableFilters.value.price_sort) {
    tableFilters.value.sort_field = 'selling_price';
    tableFilters.value.sort_order = tableFilters.value.price_sort;
  } else {
    tableFilters.value.sort_field = '';
    tableFilters.value.sort_order = '';
  }
  fetchProductsForTable(1);
};

const hasActiveFilters = computed(() => {
  const hasItemType = Array.isArray(tableFilters.value.item_type)
    ? tableFilters.value.item_type.length > 0
    : tableFilters.value.item_type !== '';
  return tableFilters.value.search !== '' ||
         hasItemType ||
         tableFilters.value.category_id !== '' ||
         tableFilters.value.brand_id !== '' ||
         tableFilters.value.price_sort !== '' ||
         tableFilters.value.on_sale ||
         tableFilters.value.show_inactive;
});

const clearFilters = () => {
  tableFilters.value.search = '';
  tableFilters.value.item_type = [];
  tableFilters.value.category_id = '';
  tableFilters.value.brand_id = '';
  tableFilters.value.price_sort = '';
  tableFilters.value.sort_field = '';
  tableFilters.value.sort_order = '';
  tableFilters.value.on_sale = false;
  tableFilters.value.show_inactive = false;
  fetchProductsForTable(1);
};

const fetchCategories = async () => {
  try {
    const response = await axios.get('/api/categories');
    categories.value = response.data;
  } catch (error) {
    console.error('Error fetching categories:', error);
  }
};

const fetchBrands = async () => {
  try {
    const response = await axios.get('/api/brands');
    brands.value = response.data;
  } catch (error) {
    console.error('Error fetching brands:', error);
  }
};

const togglingStatusId = ref(null);
const activeActionDropdownId = ref(null);

const toggleActionDropdown = (productId) => {
  if (activeActionDropdownId.value === productId) {
    activeActionDropdownId.value = null;
  } else {
    activeActionDropdownId.value = productId;
  }
};

const handleWindowClick = () => {
  activeActionDropdownId.value = null;
};

onMounted(() => {
  window.addEventListener('click', handleWindowClick);
});

onUnmounted(() => {
  window.removeEventListener('click', handleWindowClick);
});

const stripHtmlTags = (str) => {
  if (!str) return '';
  return str.replace(/<[^>]*>?/gm, '').replace(/&nbsp;/g, ' ').trim();
};

const getCategoryHierarchy = (category) => {
  if (!category) return [];
  const chain = [];
  let current = category;
  while (current) {
    chain.unshift(current.name);
    current = current.parent;
  }
  return chain;
};

const toggleProductStatus = async (item) => {
  if (!item || !item.id) return;
  if (item.status === 'draft') {
    showToast('warning', `"${item.name}" is a draft item. Please edit it to publish or change its status.`);
    return;
  }
  togglingStatusId.value = item.id;
  const originalActive = item.is_active;
  const originalStatus = item.status;

  // Optimistic UI update
  item.is_active = !originalActive;
  item.status = item.is_active ? 'active' : 'inactive';

  try {
    const response = await axios.patch(`/api/products/${item.id}/toggle-status`);
    if (response.data && response.data.success) {
      item.is_active = response.data.is_active;
      item.status = response.data.status;
      showToast('success', `"${item.name}" status updated to ${item.is_active ? 'Active' : 'Inactive'}`);
    }
  } catch (error) {
    item.is_active = originalActive;
    item.status = originalStatus;
    showToast('error', error.response?.data?.message || 'Failed to toggle product status');
  } finally {
    togglingStatusId.value = null;
  }
};

const getParsedTags = (item) => {
  if (!item || !item.tags) return [];
  
  let tagsArray = [];
  if (Array.isArray(item.tags)) {
    tagsArray = item.tags;
  } else if (typeof item.tags === 'string') {
    try {
      const parsed = JSON.parse(item.tags);
      if (Array.isArray(parsed)) {
        tagsArray = parsed;
      } else {
        tagsArray = [item.tags];
      }
    } catch (e) {
      tagsArray = item.tags.split(',').map(t => t.trim()).filter(Boolean);
    }
  }
  
  return tagsArray.map(tag => {
    if (tag && typeof tag === 'object') {
      return tag.name || tag.label || '';
    }
    return String(tag);
  }).filter(Boolean);
};

const editProduct = (product) => {
  router.push({ name: 'EditProduct', params: { id: product.id } });
};

const isLoadingViewProduct = ref(false);

const getItemImages = (product) => {
  if (!product) return [];
  let imgs = [];
  if (product.images) {
    if (Array.isArray(product.images)) {
      imgs = product.images;
    } else if (typeof product.images === 'string') {
      try {
        const parsed = JSON.parse(product.images);
        if (Array.isArray(parsed)) imgs = parsed;
      } catch (e) {
        imgs = [product.images];
      }
    }
  }
  if (imgs.length === 0) {
    const single = product.image_path || product.image || product.thumbnail;
    if (single) imgs.push(single);
  }
  return imgs;
};

const viewProduct = async (product) => {
  if (!product || !product.id) return;
  viewingProduct.value = product;
  showViewModal.value = true;
  isLoadingViewProduct.value = true;

  try {
    const response = await axios.get(`/api/products/${product.id}`);
    if (response.data) {
      viewingProduct.value = response.data.product || response.data.data || response.data;
    }
  } catch (error) {
    console.error('Error fetching product details:', error);
  } finally {
    isLoadingViewProduct.value = false;
  }
};

const openPricesModal = (item) => {
  const variants = (item.variations && item.variations.length > 0)
    ? item.variations
    : [{
        variation_name_string: 'Regular / Single Product',
        sku: item.sku || '-',
        cost_price: item.cost_price,
        retail_price: item.selling_price || item.retail_price,
        wholesale_price: item.wholesale_price,
        tax_rate: item.tax_rate
      }];

  // Broadcast custom window event containing targeted pricing parameters from product_variations
  window.dispatchEvent(new CustomEvent('open-variations-modal', {
    detail: {
      name: item.name,
      variants: variants
    }
  }));
};

const printBarcode = (product) => {
  printingProduct.value = product;
  showBarcodeModal.value = true;
};

const deleteProduct = async (product) => {
  const confirmed = await confirm({
    title: 'Delete Product?',
    message: `Are you sure you want to delete "${product.name}"? This action cannot be undone.`,
    confirmText: 'Yes, Delete',
    cancelText: 'Cancel',
    type: 'danger'
  });
  if (!confirmed) return;

  try {
    await axios.delete(`/api/products/${product.id}`);
    showToast('success', 'Product deleted successfully');
    fetchProductsForTable();
    fetchDraftsData();
  } catch (error) {
    showToast('error', error.response?.data?.message || 'An error occurred while deleting product');
  }
};



// DataTable and Custom Table Search event handlers
const debouncedFetch = debounce(() => {
  fetchProductsForTable(1);
}, 300);

const handleSearchInput = () => {
  debouncedFetch();
};

const handleTableSearch = (searchQuery) => {
  tableFilters.value.search = searchQuery;
  fetchProductsForTable(1);
};

const handleSort = (sortData) => {
  tableFilters.value.sort_field = sortData.field;
  tableFilters.value.sort_order = sortData.order;
  fetchProductsForTable(1);
};

const handlePageChange = (page) => {
  fetchProductsForTable(page);
};

const handlePerPageChange = (perPage) => {
  tablePagination.value.per_page = perPage;
  fetchProductsForTable(1);
};

// Computed properties and methods for Custom Table Selection & Pagination
const isAllSelected = computed(() => {
  if (!products.value || products.value.length === 0) return false;
  return products.value.every(item => selectedProducts.value.includes(item.id));
});

const toggleSelectAll = (event) => {
  if (event.target.checked) {
    const newSelected = [...selectedProducts.value];
    products.value.forEach(item => {
      if (!newSelected.includes(item.id)) {
        newSelected.push(item.id);
      }
    });
    selectedProducts.value = newSelected;
  } else {
    selectedProducts.value = selectedProducts.value.filter(
      id => !products.value.some(item => item.id === id)
    );
  }
};

const visiblePages = computed(() => {
  if (!tablePagination.value) return [];
  const current = tablePagination.value.current_page;
  const last = tablePagination.value.last_page;
  const pages = [];
  const start = Math.max(1, current - 2);
  const end = Math.min(last, current + 2);
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  return pages;
});

const goToPage = (page) => {
  if (page >= 1 && page <= tablePagination.value.last_page) {
    fetchProductsForTable(page);
  }
};

// Watch selectedProducts to auto-update bulkSaleForm apply_to
watch(selectedProducts, (newVal) => {
  if (newVal.length > 0) {
    bulkSaleForm.value.apply_to = 'selected';
  } else {
    bulkSaleForm.value.apply_to = 'all';
  }
});

// Separate fetch method for table view
const fetchProductsForTable = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      per_page: tablePagination.value?.per_page || 15,
      ...tableFilters.value,
    };

    if (tableFilters.value.show_inactive) {
      params.is_active = 0;
    } else {
      delete params.is_active;
    }

    if (Array.isArray(params.item_type)) {
      if (params.item_type.length > 0) {
        params.item_type = params.item_type.join(',');
      } else {
        delete params.item_type;
      }
    }

    delete params.show_inactive;
    delete params.price_sort;

    // Remove empty parameters
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null || params[key] === false) {
        delete params[key];
      }
    });

    const response = await axios.get('/api/products', { params });
    console.log('[Items Index] Products API response:', response.data);
    products.value = response.data.data || [];

    // Update table pagination
    tablePagination.value = {
      current_page: response.data.current_page || 1,
      last_page: response.data.last_page || 1,
      per_page: response.data.per_page || 15,
      total: response.data.total || 0,
      from: response.data.from || 0,
      to: response.data.to || 0
    };
  } catch (error) {
    console.error('Error fetching products for table:', error);
    products.value = [];
  } finally {
    loading.value = false;
  }
};









// Category management functions
const openCategoryModal = async () => {
  console.log('Opening category modal...');
  try {
    showCategoryModal.value = true;
    await fetchCategories(); // Refresh categories when opening modal
    console.log('Category modal opened successfully');
  } catch (error) {
    console.error('Error opening category modal:', error);
    alert('Error opening category modal. Please check the console for details.');
  }
};

const closeCategoryModal = () => {
  showCategoryModal.value = false;
  categoryForm.value = { name: '', description: '', parent_id: '' };
  editingCategoryData.value = null;
};

const createCategory = async () => {
  creatingCategory.value = true;
  try {
    const response = await axios.post('/api/categories', categoryForm.value);
    categories.value.push(response.data.category);
    categoryForm.value = { name: '', description: '', parent_id: '' };
    showToast('success', 'Category created successfully!');
  } catch (error) {
    console.error('Error creating category:', error);
    showToast('error', 'Failed to create category');
  } finally {
    creatingCategory.value = false;
  }
};

const editCategory = (category) => {
  editingCategoryData.value = category;
  categoryForm.value = {
    name: category.name,
    description: category.description || '',
    parent_id: category.parent_id || ''
  };
  // Scroll to the form
  setTimeout(() => {
    const form = document.querySelector('.bg-gray-50');
    if (form) {
      form.scrollIntoView({ behavior: 'smooth' });
    }
  }, 100);
};

const updateCategory = async () => {
  editingCategory.value = true;
  try {
    const response = await axios.put(`/api/categories/${editingCategoryData.value.id}`, categoryForm.value);
    const index = categories.value.findIndex(c => c.id === editingCategoryData.value.id);
    if (index !== -1) {
      categories.value[index] = response.data.category;
    }
    categoryForm.value = { name: '', description: '', parent_id: '' };
    editingCategoryData.value = null;
    showToast('success', 'Category updated successfully!');
  } catch (error) {
    console.error('Error updating category:', error);
    showToast('error', 'Failed to update category');
  } finally {
    editingCategory.value = false;
  }
};

const cancelEdit = () => {
  editingCategoryData.value = null;
  categoryForm.value = { name: '', description: '', parent_id: '' };
};

const deleteCategory = async (category) => {
  const confirmed = await confirm({
    title: 'Delete Category?',
    message: `Are you sure you want to delete the category "${category.name}"?`,
    confirmText: 'Yes, Delete',
    cancelText: 'Cancel',
    type: 'danger'
  });
  if (!confirmed) return;

  try {
    await axios.delete(`/api/categories/${category.id}`);
    categories.value = categories.value.filter(c => c.id !== category.id);
    showToast('success', 'Category deleted successfully!');
  } catch (error) {
    console.error('Error deleting category:', error);
    showToast('error', 'Failed to delete category');
  }
};

const parseItemTags = (item) => {
  if (!item || !item.tags) return [];
  if (Array.isArray(item.tags)) return item.tags;
  if (typeof item.tags === 'string') {
    try {
      const parsed = JSON.parse(item.tags);
      if (Array.isArray(parsed)) return parsed;
    } catch (e) {
      return item.tags.split(',').map(t => t.trim()).filter(Boolean);
    }
  }
  return [];
};

// Import/Export methods
const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (file) {
    selectedFile.value = file;
    importResults.value = null;
  }
};

const downloadTemplate = async () => {
  try {
    const response = await axios.get('/api/products/download-template', {
      responseType: 'blob'
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'product_import_template.csv');
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error downloading template:', error);
    alert('Failed to download template');
  }
};

const handleSelectionChange = (selectedIds) => {
  selectedProducts.value = selectedIds;
  if (selectedIds.length > 0) {
    bulkSaleForm.value.apply_to = 'selected';
  } else {
    bulkSaleForm.value.apply_to = 'all';
  }
};

const closeBulkSaleModal = () => {
  showBulkSaleModal.value = false;
  bulkSaleForm.value.discount_value = 0;
};

const submitBulkSale = async () => {
  applyingBulkSale.value = true;
  try {
    const payload = { ...bulkSaleForm.value };
    if (payload.apply_to === 'selected') {
      payload.product_ids = selectedProducts.value;
    }
    
    await axios.post('/api/products/bulk-sale', payload);
    showToastNotification('Sale Applied', 'Your bulk sale has been successfully applied to the inventory!');
    closeBulkSaleModal();
    fetchProductsForTable(tablePagination.value.current_page);
  } catch (error) {
    console.error('Error applying bulk sale:', error);
    showToastNotification('Error', error.response?.data?.message || 'Failed to apply sale');
  } finally {
    applyingBulkSale.value = false;
  }
};

const importProducts = async () => {
  if (!selectedFile.value) {
    alert('Please select a file to import');
    return;
  }

  importing.value = true;
  importResults.value = null;

  try {
    const formData = new FormData();
    formData.append('file', selectedFile.value);

    const response = await axios.post('/api/products/import', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    importResults.value = response.data;

    // Refresh products list
    await fetchProductsForTable();

    // Clear file selection
    selectedFile.value = null;
    if (fileInput.value) {
      fileInput.value.value = '';
    }
  } catch (error) {
    console.error('Error importing products:', error);
    alert(error.response?.data?.message || 'Failed to import products');
  } finally {
    importing.value = false;
  }
};

const exportProducts = async () => {
  try {
    // Export all products (DataTable search filters are not applied to export)
    const response = await axios.get('/api/products/export', {
      responseType: 'blob'
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;

    // Extract filename from response headers or use default
    const contentDisposition = response.headers['content-disposition'];
    let filename = 'products_export.csv';
    if (contentDisposition) {
      const filenameMatch = contentDisposition.match(/filename="(.+)"/);
      if (filenameMatch) {
        filename = filenameMatch[1];
      }
    }

    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error exporting products:', error);
    alert('Failed to export products');
  }
};

const closeImportModal = () => {
  showImportModal.value = false;
  selectedFile.value = null;
  importResults.value = null;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const getItemSellingPrice = (item) => {
  if (item.variations && item.variations.length > 0) {
    const v = item.variations[0];
    return v.selling_price || v.retail_price || v.price || item.selling_price || item.retail_price || item.price || 0;
  }
  return item.selling_price || item.retail_price || item.price || 0;
};

const getItemWholesalePrice = (item) => {
  let val = null;
  if (item.variations && item.variations.length > 0) {
    val = item.variations[0].wholesale_price;
  }
  if (val === null || val === undefined || Number(val) <= 0) {
    val = item.wholesale_price;
  }
  if (val !== null && val !== undefined && Number(val) > 0) {
    return Number(val);
  }
  return null;
};

// Lifecycle
watch(
  () => authStore.currentCompanyId || authStore.user?.current_company_id,
  (newCompanyId, oldCompanyId) => {
    if (newCompanyId && newCompanyId !== oldCompanyId) {
      console.log('[Products] Company context changed, re-fetching products:', newCompanyId);
      fetchCategories();
      fetchBrands();
      fetchProductsForTable(1);
      fetchDraftsData();
    }
  }
);

onMounted(() => {
  fetchCategories();
  fetchBrands();
  fetchProductsForTable();
  fetchDraftsData();
  document.addEventListener('click', closeDropdowns);
  window.addEventListener('keydown', handleLightboxKeydown);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdowns);
  window.removeEventListener('keydown', handleLightboxKeydown);
});
</script>
