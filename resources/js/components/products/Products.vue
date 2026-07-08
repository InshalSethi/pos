<template>
  <div class="w-full mx-auto py-4 px-4 sm:px-6 lg:px-8 bg-slate-50/50 dark:bg-zinc-950 min-h-screen">
    <div class="w-full max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex items-center gap-3">
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-slate-100 tracking-tight">Items</h1>
            <button @click="isFavorite = !isFavorite" class="transition-colors duration-200 focus:outline-none cursor-pointer">
              <svg xmlns="http://www.w3.org/2000/svg" :fill="isFavorite ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" :class="isFavorite ? 'text-amber-400 w-6 h-6' : 'text-gray-300 dark:text-slate-600 w-6 h-6 hover:text-amber-400'">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499c.172-.468.83-.468 1.002 0l2.378 6.441a1 1 0 00.95.69h6.467c.502 0 .709.65.312.962l-5.23 4.125a1 1 0 00-.363 1.118l2.378 6.441c.172.468-.37.86-.787.562l-5.23-4.125a1 1 0 00-1.18 0l-5.23 4.125c-.417.298-.959-.094-.788-.562l2.378-6.441a1 1 0 00-.363-1.118L2.25 11.592c-.398-.312-.19-.962.312-.962h6.467a1 1 0 00.95-.69L11.48 3.5z" />
              </svg>
            </button>
          </div>
          
          <div class="flex items-center gap-3 self-end sm:self-auto">
            <!-- Sales/Purchase Orders Pill Button -->
            <div class="relative">
              <button
                @click.stop="showSalesPurchaseDropdown = !showSalesPurchaseDropdown"
                class="inline-flex items-center px-4 py-2 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 hover:bg-gray-50 dark:hover:bg-slate-800 text-gray-700 dark:text-slate-300 font-medium rounded-full shadow-xs transition-all duration-200 text-sm cursor-pointer"
              >
                <span>Sales/Purchase Orders</span>
                <svg class="w-4 h-4 ml-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <transition enter-active-class="transition ease-out duration-100" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-75" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
                <div v-show="showSalesPurchaseDropdown" class="absolute right-0 mt-1.5 w-48 bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-2xl shadow-xl py-1.5 z-50">
                  <router-link :to="{ name: 'SalesInvoices' }" class="block px-4 py-2 text-xs text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors font-medium">Sales Invoices</router-link>
                  <router-link :to="{ name: 'PurchaseOrders' }" class="block px-4 py-2 text-xs text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors font-medium">Purchase Orders</router-link>
                </div>
              </transition>
            </div>

            <!-- New Item Button -->
            <router-link
              v-if="authStore.hasPermission('products.create')"
              :to="{ name: 'CreateProduct' }"
              class="inline-flex items-center px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-full shadow-sm transition-all duration-200 text-sm"
            >
              + New Item
            </router-link>

            <!-- Muted Triple-Dot Action Trigger -->
            <div class="relative">
              <button
                @click.stop="showOptionsDropdown = !showOptionsDropdown"
                class="inline-flex items-center justify-center w-9 h-9 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 hover:bg-gray-50 dark:hover:bg-slate-800 text-gray-500 dark:text-slate-400 rounded-full shadow-xs transition-all duration-200 cursor-pointer focus:outline-none"
              >
                <span class="text-sm font-bold tracking-widest leading-none mt-[-4px]">•••</span>
              </button>
              <transition enter-active-class="transition ease-out duration-100" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-75" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
                <div v-show="showOptionsDropdown" class="absolute right-0 mt-1.5 w-56 bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-2xl shadow-xl py-2 z-50">
                  <button
                    v-if="selectedProducts.length > 0 || products.length > 0"
                    @click="showBulkSaleModal = true; showOptionsDropdown = false"
                    class="w-full text-left px-4 py-2 text-xs text-rose-600 dark:text-rose-450 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-colors flex items-center gap-2 cursor-pointer font-semibold"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM12 8V7m0 1v1m0-1c0-1.657-1.343-3-3-3h0M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    Apply Sale
                  </button>
                  <button
                    v-if="authStore.hasPermission('products.import')"
                    @click="showImportModal = true; showOptionsDropdown = false"
                    class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors flex items-center gap-2 cursor-pointer font-medium"
                  >
                    <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Import Products
                  </button>
                  <button
                    v-if="authStore.hasPermission('products.export')"
                    @click="exportProducts(); showOptionsDropdown = false"
                    class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors flex items-center gap-2 cursor-pointer font-medium"
                  >
                    <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Export Products
                  </button>
                  <button
                    @click="openCategoryModal(); showOptionsDropdown = false"
                    class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors flex items-center gap-2 cursor-pointer font-medium"
                  >
                    <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Manage Categories
                  </button>
                  <button
                    @click="openDraftsModal(); showOptionsDropdown = false"
                    class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors flex items-center gap-2 cursor-pointer font-medium"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-amber-500">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                    Drafts Workbench ({{ draftsCount }})
                  </button>
                </div>
              </transition>
            </div>
          </div>
        </div>

        <!-- Search block -->
        <div class="mb-3">
          <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input
              type="text"
              v-model="tableFilters.search"
              @input="handleSearchInput"
              placeholder="Search or filter results.."
              class="w-full pl-11 pr-4 py-3 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all placeholder-gray-400 dark:placeholder-slate-500 text-gray-900 dark:text-slate-300 shadow-sm"
            />
          </div>
        </div>

        <!-- Filter Chips Row -->
        <div class="flex flex-wrap items-center gap-2 mb-6 px-1">
          <!-- Category dropdown chip -->
          <div class="relative">
            <button
              @click.stop="toggleDropdown('category')"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 hover:bg-gray-50 dark:hover:bg-slate-800 text-gray-600 dark:text-slate-300 rounded-full text-xs font-semibold cursor-pointer transition-colors shadow-xs"
            >
              <span>Category: {{ getCategoryName(tableFilters.category_id) || 'All' }}</span>
              <svg class="w-3.5 h-3.5 text-gray-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <transition enter-active-class="transition ease-out duration-100" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-75" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
              <div v-show="dropdownOpen.category" class="absolute left-0 mt-1.5 w-48 bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-2xl shadow-xl py-1 z-50 max-h-60 overflow-y-auto">
                <div @click="selectCategory('')" class="px-4 py-2 text-xs text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer font-medium">All Categories</div>
                <div v-for="category in categories" :key="category.id" @click="selectCategory(category.id)" class="px-4 py-2 text-xs text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer font-medium">
                  {{ category.name }}
                </div>
              </div>
            </transition>
          </div>

          <!-- Price sort chip -->
          <div class="relative">
            <button
              @click.stop="toggleDropdown('price')"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 hover:bg-gray-50 dark:hover:bg-slate-800 text-gray-600 dark:text-slate-300 rounded-full text-xs font-semibold cursor-pointer transition-colors shadow-xs"
            >
              <span>Sort: {{ getPriceSortName(tableFilters.price_sort) }}</span>
              <svg class="w-3.5 h-3.5 text-gray-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <transition enter-active-class="transition ease-out duration-100" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-75" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
              <div v-show="dropdownOpen.price" class="absolute left-0 mt-1.5 w-44 bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-2xl shadow-xl py-1 z-50">
                <div @click="selectPriceSort('')" class="px-4 py-2 text-xs text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer font-medium">Default Sort</div>
                <div @click="selectPriceSort('asc')" class="px-4 py-2 text-xs text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer font-semibold">Price: Low to High</div>
                <div @click="selectPriceSort('desc')" class="px-4 py-2 text-xs text-gray-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer font-semibold">Price: High to Low</div>
              </div>
            </transition>
          </div>

          <!-- On sale toggle chip -->
          <button
            @click="toggleOnSaleFilter"
            :class="tableFilters.on_sale ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/40 hover:bg-emerald-100/50 dark:hover:bg-emerald-950/50' : 'bg-white dark:bg-slate-900 border-gray-200 dark:border-slate-800 text-gray-600 dark:text-slate-350 hover:bg-gray-50 dark:hover:bg-slate-800'"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 border rounded-full text-xs font-semibold cursor-pointer transition-colors shadow-xs"
          >
            <span class="w-1.5 h-1.5 rounded-full" :class="tableFilters.on_sale ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-slate-700'"></span>
            <span>On Sale Only</span>
          </button>

        </div>

        <!-- Custom Products Data Table -->
        <div class="bg-white dark:bg-slate-900/40 rounded-2xl border border-gray-100 dark:border-slate-800 overflow-hidden shadow-sm mb-8">
          <div class="w-full overflow-x-auto">
            <table class="w-full min-w-max table-auto align-middle">
              <thead>
                <tr class="border-b border-gray-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/20 text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-400">
                  <th class="px-6 py-3.5 w-12 text-center">
                    <input
                      type="checkbox"
                      :checked="isAllSelected"
                      @change="toggleSelectAll"
                      class="w-4 h-4 text-emerald-600 border border-gray-300 dark:border-slate-800 dark:bg-slate-900 focus:ring-0 rounded-none cursor-pointer"
                    />
                  </th>
                  <th class="px-6 py-3.5 text-left font-bold">Item Name &amp; Description</th>
                  <th class="px-6 py-3.5 text-center font-bold">SKU</th>
                  <th class="px-6 py-3.5 text-center font-bold">Category</th>
                  <th class="px-6 py-3.5 text-center font-bold">Stock Status</th>
                  <th class="px-6 py-3.5 text-center font-bold">Price Matrix</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-slate-800/50">
                <!-- Loading State -->
                <tr v-if="loading" class="dark:bg-slate-900/40">
                  <td colspan="6" class="px-6 py-16 text-center text-gray-400">
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
                <tr v-else-if="products.length === 0" class="dark:bg-slate-900/40">
                  <td colspan="6" class="px-6 py-20 text-center text-gray-500">
                    <div class="flex flex-col items-center max-w-sm mx-auto">
                      <div class="w-12 h-12 rounded-2xl bg-gray-50 dark:bg-slate-800 flex items-center justify-center mb-4 text-gray-400 dark:text-slate-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                      </div>
                      <p class="text-base font-bold text-gray-900 dark:text-slate-200 mb-1">No products found</p>
                      <p class="text-xs text-gray-400 dark:text-slate-500 font-medium">Get started by adding your first product, or adjusting your filters.</p>
                    </div>
                  </td>
                </tr>

                <!-- Data Rows -->
                <tr v-else v-for="item in products" :key="item.id" class="group hover:bg-slate-50/20 dark:hover:bg-slate-800/40 dark:bg-slate-900/60 transition-colors relative">
                  <!-- Checkbox -->
                  <td class="px-6 py-4.5 sm:py-5 text-center align-middle">
                    <input
                      type="checkbox"
                      :value="item.id"
                      v-model="selectedProducts"
                      class="w-4 h-4 text-emerald-600 border border-gray-300 dark:border-slate-800 dark:bg-slate-900 focus:ring-0 rounded-none cursor-pointer"
                    />
                  </td>

                  <!-- Name & Description -->
                  <td class="px-6 py-4.5 sm:py-5 align-middle">
                    <div class="flex items-center gap-3">
                      <!-- Product Image Thumbnail -->
                      <div class="relative h-10 w-10 shrink-0 rounded-xl border border-gray-100 dark:border-slate-800 overflow-hidden bg-slate-50 dark:bg-slate-900 flex items-center justify-center">
                        <div v-if="Number(item.discount_value) > 0" class="absolute top-0 right-0 z-10 pointer-events-none select-none">
                          <div class="absolute transform rotate-45 bg-rose-600 text-white text-[6px] font-black uppercase text-center tracking-widest py-0.5 w-[50px] -right-[15px] top-[4px] shadow-xs border-b border-white/20">Sale</div>
                        </div>
                        <img
                          v-if="item.image && !item.image.includes('Temp') && !item.image.includes('.tmp')"
                          :src="item.image.startsWith('/') ? item.image : '/' + item.image"
                          :alt="item.name"
                          class="h-full w-full object-cover"
                        />
                        <span v-else class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase">
                          {{ item.name ? item.name.substring(0, 1) : 'P' }}
                        </span>
                      </div>

                      <div class="flex flex-col min-w-0">
                        <div class="flex items-center gap-2">
                          <span class="font-extrabold text-gray-950 dark:text-slate-200 text-sm truncate max-w-sm sm:max-w-md">{{ item.name }}</span>
                          <span 
                            v-if="item.variations_count > 0 || (item.variations && item.variations.length > 0)"
                            class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-black tracking-wide bg-indigo-50 text-indigo-700 border border-indigo-100 dark:bg-indigo-950/50 dark:text-indigo-300 dark:ring-1 dark:ring-indigo-500/20 dark:border-indigo-500/10 uppercase shrink-0"
                          >
                            Variants
                          </span>
                        </div>
                        <span class="text-xs text-gray-400 dark:text-slate-500 mt-0.5 font-medium truncate max-w-xs sm:max-w-sm">{{ item.description || item.sku || 'No description' }}</span>
                      </div>
                    </div>
                  </td>

                  <!-- SKU -->
                  <td class="px-6 py-4.5 sm:py-5 align-middle text-center">
                    <div v-if="item.variations && item.variations.length > 0" class="flex flex-col items-center gap-0.5">
                      <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-900/60 text-slate-650 dark:text-slate-400 border border-slate-200/60 dark:border-slate-800 uppercase">
                        {{ item.variations[0].sku || '-' }}
                      </span>
                      <span 
                        v-if="item.variations.length > 1" 
                        class="text-[9px] font-semibold text-slate-400 dark:text-slate-500 mt-0.5"
                      >
                        +{{ item.variations.length - 1 }} more
                      </span>
                    </div>
                    <span v-else class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-900/60 text-slate-650 dark:text-slate-400 border border-slate-200/60 dark:border-slate-800 uppercase">
                      {{ item.sku || '-' }}
                    </span>
                  </td>

                  <!-- Category Badge -->
                  <td class="px-6 py-4.5 sm:py-5 align-middle text-center">
                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-100/50 dark:border-blue-900/30">
                      <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5 shrink-0"></span>
                      <span>{{ item.category ? item.category.name : 'General' }}</span>
                    </span>
                  </td>

                  <!-- Stock Status -->
                  <td class="px-6 py-4.5 sm:py-5 align-middle text-center">
                    <span class="text-sm font-extrabold text-gray-900 dark:text-slate-200" v-if="item.stock_quantity !== null && item.stock_quantity !== undefined">
                      {{ item.stock_quantity }}
                    </span>
                    <span class="text-sm font-semibold text-gray-300 dark:text-slate-600" v-else>N/A</span>
                  </td>

                  <!-- Price Matrix (Center Aligned) -->
                  <td class="px-6 py-4.5 sm:py-5 text-center align-middle">
                    <div class="flex flex-col items-center justify-center text-center">
                      <span class="text-sm font-extrabold text-gray-950 dark:text-slate-200">
                        {{ currencyStore.formatPrice((item.variations && item.variations.length > 0) ? (item.variations[0].retail_price || item.variations[0].selling_price || 0) : (item.selling_price || item.retail_price || 0)) }}
                      </span>
                      <span class="text-xs text-gray-400 dark:text-slate-500 mt-0.5 font-semibold">
                        {{ currencyStore.formatPrice((item.variations && item.variations.length > 0) ? (item.variations[0].cost_price || 0) : (item.cost_price || 0)) }}
                      </span>
                    </div>

                    <!-- Floating Actions (Revealed on Row Hover) -->
                    <div class="absolute invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200 bg-white/95 dark:bg-slate-800/95 shadow-md dark:shadow-slate-950/50 border border-gray-100 dark:border-slate-700 rounded-lg p-1.5 z-30"
                         style="right: 18%; top: 50%; transform: translateY(-55%);">
                      <div class="flex items-center space-x-1.5">
                        <button
                          @click="viewProduct(item)"
                          class="h-7 w-7 flex items-center justify-center rounded-lg bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-200 dark:hover:border-indigo-500/30 hover:shadow-xs transition-all cursor-pointer focus:outline-none"
                          title="View"
                        >
                          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        </button>
                        <button
                          @click="editProduct(item)"
                          class="h-7 w-7 flex items-center justify-center rounded-lg bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:border-emerald-200 dark:hover:border-emerald-500/30 hover:shadow-xs transition-all cursor-pointer focus:outline-none"
                          title="Update"
                        >
                          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </button>
                        <button
                          @click="deleteProduct(item)"
                          class="h-7 w-7 flex items-center justify-center rounded-lg bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:border-red-200 dark:hover:border-red-500/30 hover:shadow-xs transition-all cursor-pointer focus:outline-none"
                          title="Delete"
                        >
                          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination Footer -->
          <div v-if="tablePagination && !loading" class="px-6 py-4 bg-white dark:bg-slate-900/40 border-t border-gray-100 dark:border-slate-800 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div class="text-xs text-gray-500 dark:text-slate-400 font-semibold">
              Showing {{ tablePagination.from || 0 }} to {{ tablePagination.to || 0 }} of {{ tablePagination.total || 0 }} results
            </div>

            <!-- Page Selection Controls -->
            <div class="flex items-center gap-1.5 self-center sm:self-auto">
              <button
                @click="goToPage(1)"
                :disabled="tablePagination.current_page === 1"
                class="px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-slate-800 text-xs font-semibold text-gray-500 dark:text-slate-450 bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer"
              >
                First
              </button>
              <button
                @click="goToPage(tablePagination.current_page - 1)"
                :disabled="tablePagination.current_page === 1"
                class="px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-slate-800 text-xs font-semibold text-gray-500 dark:text-slate-450 bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer"
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
                    : 'border border-gray-200 dark:border-slate-800 text-gray-600 dark:text-slate-400 bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800'
                ]"
              >
                {{ page }}
              </button>
              
              <button
                @click="goToPage(tablePagination.current_page + 1)"
                :disabled="tablePagination.current_page === tablePagination.last_page"
                class="px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-slate-800 text-xs font-semibold text-gray-500 dark:text-slate-450 bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer"
              >
                Next
              </button>
              <button
                @click="goToPage(tablePagination.last_page)"
                :disabled="tablePagination.current_page === tablePagination.last_page"
                class="px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-slate-800 text-xs font-semibold text-gray-500 dark:text-slate-450 bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer"
              >
                Last
              </button>
            </div>
          </div>
        </div>
    </div>
  </div>


    <!-- Drafts Workbench Modal -->
    <div v-if="isDraftsModalOpen"
         @click.self="isDraftsModalOpen = false"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-md">
        
        <div class="w-full max-w-5xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800/80 rounded-[28px] p-6 shadow-2xl flex flex-col max-h-[85vh] transition-all">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800/60 pb-4 mb-5 px-1">
                <div class="flex flex-col space-y-0.5">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-zinc-500">
                        Incomplete Items Workbench
                    </span>
                    <h3 class="text-base font-bold text-slate-900 dark:text-zinc-100 tracking-wide">
                        Product Drafts Workbench
                    </h3>
                </div>

                <div class="flex items-center gap-5">
                    <button v-show="selectedDraftIds.length > 0" 
                            @click="deleteSelectedDrafts" 
                            type="button" 
                            class="inline-flex items-center gap-1.5 text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300 bg-transparent border-0 p-1 rounded-lg transition-colors focus:outline-none group cursor-pointer"
                            title="Permanently remove selected slots">
                        
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" 
                             class="w-4 h-4 transition-transform group-hover:scale-105">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        <span class="text-xs font-black tracking-wide">{{ selectedDraftIds.length }}</span>
                    </button>

                    <span v-show="selectedDraftIds.length > 0" class="w-[1px] h-4 bg-slate-200 dark:bg-zinc-800"></span>

                    <button type="button" 
                            @click="isDraftsModalOpen = false" 
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 font-medium text-xl transition-all focus:outline-none hover:rotate-90 duration-200 p-1 rounded-lg leading-none select-none cursor-pointer">
                        &times;
                    </button>
                </div>
            </div>

            <div class="w-full overflow-y-auto border border-slate-200/70 dark:border-zinc-800/80 rounded-2xl overflow-x-auto shadow-inner bg-slate-50/20 dark:bg-zinc-950/20 custom-scrollbar">
                <table class="w-full min-w-max table-auto align-middle divide-y divide-slate-100 dark:divide-zinc-800/60 text-xs">
                    
                    <thead class="bg-slate-50/80 dark:bg-zinc-800/50 text-[10px] font-bold uppercase tracking-wider text-slate-500 sticky top-0 z-10 backdrop-blur-xs">
                        <tr>
                            <th class="px-4 py-3.5 text-center w-12">
                                <input type="checkbox" 
                                       @click="toggleSelectAllDrafts" 
                                       :checked="draftProducts.length > 0 && selectedDraftIds.length === draftProducts.length"
                                       class="rounded-md border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer w-3.5 h-3.5 transition-all">
                            </th>
                            <th class="px-4 py-3.5 text-left">Product Title</th>
                            <th class="px-4 py-3.5 text-left">Category</th>
                            <th class="px-4 py-3.5 text-center">Tags</th>
                            <th class="px-4 py-3.5 text-center">Product Type</th>
                            <th class="px-4 py-3.5 text-center">Prices (W / R)</th>
                            <th class="px-4 py-3.5 text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/40 text-[11px] bg-white dark:bg-zinc-900">
                        <tr v-if="isLoadingDrafts">
                            <td colspan="7" class="text-center py-6 text-xs text-slate-400 dark:text-zinc-500 italic">Fetching compiled draft lots...</td>
                        </tr>
                        
                        <tr v-else-if="draftProducts.length === 0">
                            <td colspan="7" class="text-center py-6 text-xs text-slate-400 dark:text-zinc-500 italic">No drafted items found in your workbench.</td>
                        </tr>

                        <tr v-else v-for="draft in draftProducts" :key="draft.id"
                            :class="selectedDraftIds.includes(draft.id) ? 'bg-indigo-50/30 dark:bg-indigo-500/5' : 'hover:bg-slate-50/50 dark:hover:bg-zinc-800/30'" 
                            class="transition-colors duration-150">
                            
                            <td class="px-4 py-3.5 align-middle text-center">
                                <input type="checkbox" 
                                       :value="draft.id" 
                                       v-model="selectedDraftIds"
                                       class="rounded-md border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer w-3.5 h-3.5 transition-all">
                            </td>

                            <td class="px-4 py-3.5 align-middle text-xs">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-50 dark:bg-zinc-950 flex-shrink-0 flex items-center justify-center border border-slate-200/60 dark:border-zinc-800 overflow-hidden shadow-xs">
                                        <img v-if="draft.image || draft.image_path || draft.thumbnail || draft.logo" :src="draft.image || draft.image_path || draft.thumbnail || draft.logo" class="w-full h-full object-cover">
                                        <div v-else class="w-full h-full flex items-center justify-center text-slate-400 font-bold text-[11px]">
                                            <span>{{ draft.name ? draft.name.charAt(0).toUpperCase() : 'P' }}</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-col text-left">
                                        <span class="font-extrabold text-slate-900 dark:text-zinc-100 tracking-tight">{{ draft.name }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono mt-0.5">{{ draft.sku || draft.id }}</span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-3.5 align-middle text-slate-500 text-left font-medium">{{ draft.category ? draft.category.name : 'No Category' }}</td>

                            <td class="px-4 py-3.5 align-middle text-center">
                                <div class="flex justify-center items-center gap-1 flex-wrap max-w-[130px] mx-auto">
                                    <template v-if="draft.tags && draft.tags.length > 0">
                                        <span v-for="(tag, i) in draft.tags" :key="i" class="text-[9px] font-bold bg-slate-100 text-slate-500 dark:bg-zinc-800 dark:text-zinc-400 px-1.5 py-0.5 rounded-md">
                                            #{{ typeof tag === 'object' ? tag.name : tag }}
                                        </span>
                                    </template>
                                    <span v-else class="text-slate-300 dark:text-zinc-700 font-black">-</span>
                                </div>
                            </td>

                            <td class="px-4 py-3.5 align-middle text-center">
                                <span :class="draft.variations_count > 0 ? 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400' : 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400'" 
                                      class="inline-flex items-center px-2.5 py-0.5 rounded-md font-bold text-[10px]">
                                    {{ draft.variations_count > 0 ? 'Variant Product' : 'Simple Product' }}
                                </span>
                            </td>

                            <td class="px-4 py-3.5 align-middle text-center">
                                <template v-if="draft.variations_count > 0">
                                    <span class="text-indigo-600 dark:text-indigo-400 font-bold bg-indigo-50 dark:bg-indigo-500/10 px-2 py-0.5 rounded-md text-[10px]">{{ draft.variations_count }} Matrix Prices</span>
                                </template>
                                <template v-else>
                                    <div class="inline-flex flex-col text-center justify-center items-center mx-auto space-y-0.5">
                                        <span class="text-[10px] text-slate-400 font-medium">W: <strong class="text-indigo-600 dark:text-indigo-400">${{ parseFloat(draft.wholesale_price || 0).toFixed(2) }}</strong></span>
                                        <span class="text-[10px] text-slate-400 font-medium">R: <strong class="text-emerald-600 dark:text-emerald-400">${{ parseFloat(draft.retail_price || draft.selling_price || 0).toFixed(2) }}</strong></span>
                                    </div>
                                </template>
                            </td>

                            <td class="px-4 py-3.5 align-middle text-center">
                                <button type="button"
                                        @click="editProduct(draft); isDraftsModalOpen = false"
                                        class="inline-flex items-center px-3 py-1.5 text-[10px] font-black text-indigo-700 bg-indigo-50 border border-indigo-100 rounded-xl hover:bg-indigo-100 transition-all focus:outline-none shadow-xs dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20">
                                    Resume Setup
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-5 flex justify-end px-1">
                <button type="button" 
                        @click="isDraftsModalOpen = false" 
                        class="px-4 py-2 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700 rounded-xl transition-all shadow-xs focus:outline-none cursor-pointer">
                    Dismiss Workbench
                </button>
            </div>
        </div>
    </div>

    <!-- Import Products Modal -->
    <div v-if="showImportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
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
    <div v-if="showBulkSaleModal" class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm overflow-y-auto h-full w-full z-50 transition-all">
      <div class="relative top-20 mx-auto p-8 border-0 w-full max-w-md shadow-2xl rounded-2xl bg-white transform transition-all">
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

    <!-- View Product Modal -->
    <div v-if="showViewModal && viewingProduct" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
      <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden">

        <!-- Header -->
        <div class="flex items-center justify-between px-6 pt-6 pb-5">
          <div class="flex items-center gap-4">
            <!-- Product Avatar -->
            <div class="relative h-14 w-14 rounded-xl border border-slate-200 overflow-hidden bg-slate-50 flex-shrink-0">
              <div v-if="Number(viewingProduct.discount_value) > 0" class="absolute top-0 right-0 z-10">
                <div class="absolute transform rotate-45 bg-rose-600 text-white text-[6px] font-black uppercase text-center tracking-widest py-0.5 w-[50px] -right-[15px] top-[4px]">Sale</div>
              </div>
              <img
                v-if="viewingProduct.image && !viewingProduct.image.includes('Temp') && !viewingProduct.image.includes('.tmp')"
                :src="viewingProduct.image.startsWith('/') ? viewingProduct.image : '/' + viewingProduct.image"
                :alt="viewingProduct.name"
                class="h-full w-full object-cover"
              >
              <div v-else class="h-full w-full flex items-center justify-center bg-indigo-100">
                <span class="text-xl font-black text-indigo-500 uppercase">{{ viewingProduct.name ? viewingProduct.name.substring(0, 1) : 'P' }}</span>
              </div>
            </div>
            <!-- Name & SKU -->
            <div>
              <h2 class="text-base font-bold text-gray-900 leading-tight">{{ viewingProduct.name }}</h2>
              <p class="text-xs text-gray-400 mt-0.5">SKU: #{{ viewingProduct.sku || 'N/A' }}</p>
            </div>
          </div>
          <!-- Close X -->
          <button @click="showViewModal = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Divider -->
        <div class="border-t border-gray-100 mx-6"></div>

        <!-- Body — two columns -->
        <div class="grid grid-cols-2 gap-0 px-6 py-5 max-h-[60vh] overflow-y-auto">

          <!-- Left: Product Info -->
          <div class="pr-6 border-r border-gray-100 space-y-5">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Product Info</p>

            <div>
              <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-0.5">Category</p>
              <p class="text-sm font-medium text-gray-800">{{ viewingProduct.category?.name || 'No Category' }}</p>
            </div>

            <div>
              <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-0.5">Barcode</p>
              <p class="text-sm font-medium text-gray-800">{{ viewingProduct.barcode || 'N/A' }}</p>
            </div>

            <div>
              <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-0.5">Status</p>
              <span :class="['inline-flex px-2 py-0.5 rounded-lg text-[11px] font-semibold', viewingProduct.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700']">
                {{ viewingProduct.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>

            <div>
              <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Tags</p>
              <div v-if="getParsedTags(viewingProduct).length > 0" class="flex flex-wrap gap-1">
                <span v-for="(tag, i) in getParsedTags(viewingProduct)" :key="i" class="inline-flex px-1.5 py-0.5 text-[10px] font-semibold rounded-md bg-slate-100 text-slate-600">#{{ tag }}</span>
              </div>
              <p v-else class="text-sm font-medium text-gray-800">N/A</p>
            </div>

            <div>
              <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-0.5">Description</p>
              <p class="text-sm text-gray-600 leading-relaxed">{{ viewingProduct.description || 'No description provided.' }}</p>
            </div>
          </div>

          <!-- Right: Pricing & Stock -->
          <div class="pl-6 space-y-5">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Inventory &amp; Pricing</p>

            <div>
              <button type="button" 
                      @click="openPricesModal(viewingProduct)" 
                      class="px-3 py-1.5 text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 shadow-sm transition-all focus:outline-none flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                  </svg>
                  View Variations
              </button>
            </div>

            <div v-if="Number(viewingProduct.discount_value) > 0">
              <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-0.5">Discount</p>
              <p class="text-sm font-semibold text-rose-600">
                {{ viewingProduct.discount_type === 'percentage' ? viewingProduct.discount_value + '%' : currencyStore.formatPrice(viewingProduct.discount_value) }}
              </p>
            </div>

            <div>
              <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-0.5">Stock Quantity</p>
              <p class="text-sm font-medium text-gray-800">{{ viewingProduct.stock_quantity }} <span class="text-gray-400 text-xs">{{ viewingProduct.unit_of_measure }}</span></p>
            </div>

            <div>
              <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-0.5">Min Stock Level</p>
              <p class="text-sm font-medium text-gray-800">{{ viewingProduct.low_stock_threshold || viewingProduct.min_stock_level || '-' }}</p>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-100 px-6 py-4 flex items-center justify-end gap-3">
          <button
            @click="showViewModal = false"
            class="px-5 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-all"
          >
            Close
          </button>
          <button
            @click="editProduct(viewingProduct); showViewModal = false"
            class="px-5 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-all flex items-center gap-2 shadow-sm"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit Details
          </button>
        </div>

      </div>
    </div>


    <!-- Category Management Modal -->
    <div v-if="showCategoryModal" class="fixed inset-0 overflow-y-auto h-full w-full" style="z-index: 9999; background-color: rgba(0, 0, 0, 0.5);">
      <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
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
    </div>

    <!-- Barcode Printer Modal -->
    <BarcodePrinter v-if="showBarcodeModal" :product="printingProduct" @close="showBarcodeModal = false" />

    <!-- Variations Modal -->
    <div v-if="showVariationsModal && selectedVariationsProduct" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="absolute inset-0" @click="showVariationsModal = false"></div>
        <div class="relative w-full max-w-6xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-3xl p-5 shadow-2xl flex flex-col max-h-[85vh]">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 pb-3 mb-4">
                <div>
                    <span class="text-[10px] font-black uppercase text-indigo-600 tracking-wider">Product Variations View</span>
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">{{ selectedVariationsProduct.name }}</h3>
                </div>
                <button type="button" @click="showVariationsModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 font-bold text-lg focus:outline-none">&times;</button>
            </div>

            <div class="w-full overflow-y-auto border border-slate-200/60 dark:border-zinc-800 rounded-xl overflow-x-auto shadow-inner custom-scrollbar">
                <table class="w-full min-w-max table-auto align-middle divide-y divide-slate-100 dark:divide-zinc-800 text-xs">
                    <thead class="bg-slate-50 dark:bg-zinc-800/40 text-[10px] font-bold uppercase tracking-wider text-slate-500 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-2.5 text-left bg-slate-50 dark:bg-zinc-800/40">Variant Combination</th>
                            <th class="px-4 py-2.5 text-left bg-slate-50 dark:bg-zinc-800/40">SKU Code</th>
                            <th class="px-4 py-2.5 text-left bg-slate-50 dark:bg-zinc-800/40">Barcode</th>
                            <th class="px-4 py-2.5 text-left bg-slate-50 dark:bg-zinc-800/40">Cost Price</th>
                            <th class="px-4 py-2.5 text-left bg-slate-50 dark:bg-zinc-800/40">Retail Price</th>
                            <th class="px-4 py-2.5 text-left bg-slate-50 dark:bg-zinc-800/40">Wholesale Price</th>
                            <th class="px-4 py-2.5 text-left bg-slate-50 dark:bg-zinc-800/40">Sale Price</th>
                            <th class="px-4 py-2.5 text-center bg-slate-50 dark:bg-zinc-800/40">Tax Rate</th>
                            <th class="px-4 py-2.5 text-center bg-slate-50 dark:bg-zinc-800/40">Current Stock</th>
                            <th class="px-4 py-2.5 text-center bg-slate-50 dark:bg-zinc-800/40">Min Alert</th>
                            <th class="px-4 py-2.5 text-center bg-slate-50 dark:bg-zinc-800/40">Unit</th>
                            <th class="px-4 py-2.5 text-left bg-slate-50 dark:bg-zinc-800/40">Batch Number</th>
                            <th class="px-4 py-2.5 text-left bg-slate-50 dark:bg-zinc-800/40">Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-[11px]">
                        <tr v-if="!selectedVariationsProduct.variations || selectedVariationsProduct.variations.length === 0">
                            <td colspan="13" class="text-center py-6 text-xs text-slate-400 dark:text-zinc-500 italic">No variations found.</td>
                        </tr>
                        <tr v-else v-for="(variant, idx) in selectedVariationsProduct.variations" :key="idx" class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/30 transition-colors">
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
                <button type="button" @click="showVariationsModal = false" class="px-4 py-1.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-200 rounded-xl transition-all">Close View</button>
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
      <div v-if="toast.show" class="fixed top-5 right-5 max-w-sm w-full bg-white shadow-2xl rounded-2xl pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden z-[100] border-l-4 border-emerald-500">
        <div class="p-4">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <div class="p-2 bg-emerald-100 rounded-full">
                 <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                 </svg>
              </div>
            </div>
            <div class="ml-3 w-0 flex-1 pt-0.5">
              <p class="text-sm font-bold text-gray-900">{{ toast.title }}</p>
              <p class="mt-1 text-xs text-gray-500 font-medium">{{ toast.message }}</p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
              <button @click="toast.show = false" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                <span class="sr-only">Close</span>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { debounce } from '@/utils/debounce';
import BarcodePrinter from '@/components/common/BarcodePrinter.vue';
import { useCurrencyStore } from '@/stores/currency';
import axios from 'axios';

const router = useRouter();

const authStore = useAuthStore();
const currencyStore = useCurrencyStore();

// New Reactive states for Dropdowns and Favoriting
const isFavorite = ref(false);
const showSalesPurchaseDropdown = ref(false);
const showOptionsDropdown = ref(false);

// Reactive data
const products = ref([]);
const categories = ref([]);
const loading = ref(false);
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
  if (!confirm('Are you absolutely sure you want to permanently delete the ' + selectedDraftIds.value.length + ' selected draft item(s)?')) return;

  try {
    const response = await axios.post('/api/products/drafts/bulk-destroy', {
      ids: selectedDraftIds.value
    });
    if (response.data && response.data.success) {
      selectedDraftIds.value = [];
      await fetchDraftsData();
      await fetchProductsForTable();
    }
  } catch (error) {
    console.error('Error deleting drafts:', error);
    alert(error.response?.data?.message || 'Failed to delete selected drafts');
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
  per_page: 25,
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
  category_id: '',
  price_sort: '',
  on_sale: false
});

// Methods

const dropdownOpen = ref({
  category: false,
  price: false
});

const toggleDropdown = (type) => {
  dropdownOpen.value[type] = !dropdownOpen.value[type];
  if (type === 'category') dropdownOpen.value.price = false;
  else if (type === 'price') dropdownOpen.value.category = false;
};

const closeDropdowns = () => {
  dropdownOpen.value.category = false;
  dropdownOpen.value.price = false;
  showSalesPurchaseDropdown.value = false;
  showOptionsDropdown.value = false;
};

const selectCategory = (id) => {
  tableFilters.value.category_id = id;
  dropdownOpen.value.category = false;
  fetchProductsForTable(1);
};

const selectPriceSort = (sort) => {
  tableFilters.value.price_sort = sort;
  dropdownOpen.value.price = false;
  handlePriceSortChange();
};

const getCategoryName = (id) => {
  if (!id) return '';
  const category = categories.value.find(c => c.id === id);
  return category ? category.name : '';
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

const fetchCategories = async () => {
  try {
    const response = await axios.get('/api/categories');
    categories.value = response.data;
  } catch (error) {
    console.error('Error fetching categories:', error);
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

const viewProduct = (product) => {
  viewingProduct.value = product;
  showViewModal.value = true;
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
  if (!confirm(`Are you sure you want to delete ${product.name}?`)) {
    return;
  }

  try {
    await axios.delete(`/api/products/${product.id}`);
    fetchProductsForTable();
    fetchDraftsData();
  } catch (error) {
    alert(error.response?.data?.message || 'An error occurred');
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
      per_page: tablePagination.value.per_page,
      ...tableFilters.value
    };

    // Remove empty parameters
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null) {
        delete params[key];
      }
    });

    const response = await axios.get('/api/products', { params });
    products.value = response.data.data;

    // Update table pagination
    tablePagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
      from: response.data.from,
      to: response.data.to
    };
  } catch (error) {
    console.error('Error fetching products for table:', error);
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
    alert('Category created successfully!');
  } catch (error) {
    console.error('Error creating category:', error);
    alert('Failed to create category');
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
    alert('Category updated successfully!');
  } catch (error) {
    console.error('Error updating category:', error);
    alert('Failed to update category');
  } finally {
    editingCategory.value = false;
  }
};

const cancelEdit = () => {
  editingCategoryData.value = null;
  categoryForm.value = { name: '', description: '', parent_id: '' };
};

const deleteCategory = async (category) => {
  if (!confirm(`Are you sure you want to delete the category "${category.name}"?`)) {
    return;
  }

  try {
    await axios.delete(`/api/categories/${category.id}`);
    categories.value = categories.value.filter(c => c.id !== category.id);
    alert('Category deleted successfully!');
  } catch (error) {
    console.error('Error deleting category:', error);
    alert('Failed to delete category');
  }
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



// Lifecycle
onMounted(() => {
  fetchCategories();
  fetchProductsForTable();
  fetchDraftsData();
  document.addEventListener('click', closeDropdowns);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdowns);
});
</script>
