<template>
  <div class="product-search-module pb-6 mb-4 space-y-3">
    <h3 class="text-xs font-extrabold uppercase text-slate-400 dark:text-zinc-500 tracking-wider text-left">Catalog Search & Selection</h3>
            <div class="flex items-center gap-3 relative w-full">
              <!-- Search items input (takes full width minus gold category icon button) -->
              <div class="relative flex-1" id="product-search-container">
                <input
                  ref="searchInputRef"
                  v-model="productSearch"
                  type="text"
                  autofocus
                  placeholder="Search products by title, code or barcode..."
                  class="w-full pl-5 pr-11 py-2.5 bg-white dark:bg-[#12161b]/90 border border-slate-300 dark:border-sky-500/40 focus:border-sky-400 rounded-full text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-zinc-500 text-xs font-medium shadow-[0_0_15px_rgba(56,189,248,0.15)] focus:shadow-[0_0_20px_rgba(56,189,248,0.3)] focus:outline-none transition-all duration-300"
                  @focus="isProductDropdownOpen = true"
                  @keydown="handleProductSearchKeydown"
                />
                <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400 dark:text-sky-300">
                  <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
                
                <!-- Search Results Dropdown List -->
                <div
                  v-show="isProductDropdownOpen && filteredProducts.length > 0"
                  class="absolute left-0 right-0 mt-2 bg-white dark:bg-[#181e24] border border-slate-200 dark:border-slate-700/80 rounded-2xl shadow-2xl z-50 max-h-60 overflow-y-auto py-2 custom-scrollbar backdrop-blur-md"
                >
                  <div
                    v-for="(product, idx) in displayedProducts"
                    :key="product.key"
                    :ref="el => setProductItemRef(el, idx)"
                    @click="selectProductFromDropdown(product)"
                    @mouseenter="highlightedProductIndex = idx"
                    class="px-4 py-2.5 flex justify-between items-center text-xs border-b border-slate-100 dark:border-zinc-800/60 last:border-0 text-left transition-colors"
                    :class="[
                      isProductOutOfStock(product) && !isPurchaseContext
                        ? 'opacity-50 cursor-not-allowed bg-slate-100/50 dark:bg-zinc-800/40 select-none'
                        : (highlightedProductIndex === idx
                            ? 'bg-indigo-50/90 dark:bg-zinc-800/90 text-indigo-900 dark:text-indigo-200 border-l-4 border-l-indigo-600 dark:border-l-indigo-400 font-bold cursor-pointer'
                            : 'hover:bg-slate-50 dark:hover:bg-zinc-800/80 cursor-pointer')
                    ]"
                  >
                    <div class="min-w-0 pr-4">
                      <div class="font-bold text-slate-800 dark:text-zinc-200 truncate flex items-center gap-2">
                        <span>{{ product.name }}</span>
                        <span
                          v-if="product.brand_name || (product.brand && typeof product.brand === 'string') || (product.brand && product.brand.name)"
                          class="inline-block px-1.5 py-0.5 text-[9px] font-bold tracking-wide uppercase border border-slate-300 dark:border-zinc-700 text-slate-600 dark:text-zinc-400 rounded bg-slate-50 dark:bg-zinc-800/80 shrink-0 leading-none"
                        >
                          {{ product.brand_name || (typeof product.brand === 'string' ? product.brand : product.brand.name) }}
                        </span>
                      </div>
                      <div class="text-[10px] text-slate-400 dark:text-zinc-500 font-mono">SKU: {{ product.sku }}</div>
                      <div
                        v-if="product.category_path || (product.category && typeof product.category === 'string') || (product.category && product.category.name)"
                        class="text-[9.5px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider truncate mt-0.5"
                      >
                        {{ product.category_path || (typeof product.category === 'string' ? product.category : product.category.name) }}
                      </div>
                    </div>
                    <div class="text-right flex-shrink-0 space-y-0.5">
                      <span class="font-black text-emerald-600 dark:text-emerald-400 text-sm block">{{ currencySymbol }}{{ getDisplayPrice(product) }}</span>
                      
                      <span
                        v-if="isProductOutOfStock(product)"
                        class="inline-block px-2 py-0.5 text-[9px] font-extrabold bg-rose-100 text-rose-700 dark:bg-rose-950/80 dark:text-rose-300 rounded-full border border-rose-200 dark:border-rose-800"
                      >
                        Out of Stock
                      </span>
                      <span v-else class="text-[10px] text-slate-500 dark:text-zinc-400">
                        {{ getProductStock(product) }} in stock
                      </span>
                    </div>
                  </div>

                  <!-- Footer note when more than 50 items match -->
                  <div
                    v-if="filteredProducts.length > 50"
                    class="px-4 py-2 text-center text-[10px] font-semibold text-slate-400 dark:text-zinc-500 bg-slate-50/80 dark:bg-zinc-900/80 border-t border-slate-100 dark:border-zinc-800/60 sticky bottom-0 backdrop-blur-sm select-none"
                  >
                    Showing top 50 of {{ filteredProducts.length }} items — Type to search more...
                  </div>
                </div>
              </div>

              <!-- Gold Metallic Advance Search Button with Tooltip -->
              <div class="relative shrink-0 group">
                <button
                  type="button"
                  @click="openAdvanceSearchModal"
                  class="relative flex items-center justify-center w-10 h-10 rounded-full shrink-0 shadow-lg shadow-amber-950/30 hover:shadow-amber-500/20 active:scale-95 transition-all duration-200 cursor-pointer border border-amber-300/40 bg-gradient-to-b from-[#fbe396] via-[#dcae42] to-[#b38728] hover:from-[#fff0ad] hover:via-[#e2b74b] hover:to-[#be9130]"
                >
                  <!-- Filter Icon -->
                  <svg class="w-4.5 h-4.5 text-[#1e1708]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M6 10h12M8 14h8M10 18h4" />
                  </svg>
                  
                  <!-- Active Filter Indicator Badge -->
                  <span
                    v-if="hasActiveAdvanceFilters"
                    class="absolute -top-1 -right-1 w-4 h-4 bg-emerald-500 text-white rounded-full text-[9px] font-black flex items-center justify-center border border-white dark:border-zinc-900 shadow-sm"
                  >
                    !
                  </span>
                </button>

                <!-- Tooltip: Advance Searching -->
                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:flex flex-col items-center pointer-events-none z-50">
                  <div class="bg-slate-900/95 dark:bg-[#1e252d] text-slate-100 text-[10px] font-extrabold px-2.5 py-1 rounded-lg shadow-xl whitespace-nowrap border border-slate-700/80 tracking-wide">
                    Advance Searching
                  </div>
                  <div class="w-2 h-2 bg-slate-900 dark:bg-[#1e252d] rotate-45 -mt-1 border-r border-b border-slate-700/80"></div>
                </div>
              </div>
            </div>
    <teleport to="body">
    <!-- Advance Searching Modal -->
    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div v-if="isAdvanceSearchModalOpen" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200" style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);">
        <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-5xl shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 overflow-hidden flex flex-col max-h-[90vh] my-auto">
          
          <!-- Modal Header -->
          <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-[#2E2E2E] bg-slate-50 dark:bg-[#252525]">
            <h3 class="text-base font-extrabold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2">
              <span>Advanced Item Search</span>
            </h3>
            <button
              type="button"
              @click="closeAdvanceSearchModal"
              class="p-1.5 rounded-lg text-slate-400 dark:text-zinc-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-zinc-800 transition-all cursor-pointer border-0 bg-transparent"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-6 overflow-y-auto space-y-5 custom-scrollbar flex-1 text-left">
            
            <!-- 1. Main Search Bar (Top) -->
            <div class="relative w-full">
              <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-slate-400 dark:text-zinc-400">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input
                ref="advanceSearchInputRef"
                v-model="advanceFilters.query"
                type="text"
                placeholder="Search by Name, SKU, Barcode or Description"
                @keydown.enter.prevent="handleAdvanceSearchEnter"
                @keydown.esc="closeAdvanceSearchModal"
                class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-[#12161b] border border-slate-300 dark:border-[#2E2E2E] focus:border-sky-500 dark:focus:border-sky-400 focus:ring-0 focus-visible:ring-0 focus:outline-none shadow-none rounded-xl text-xs font-medium text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-zinc-500 transition-all"
              />
            </div>

            <!-- 2. Additional Search Criteria Section -->
            <div class="space-y-3 pt-1">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 dark:text-zinc-200">Additional Search Criteria</span>
                <button
                  type="button"
                  v-if="hasActiveAdvanceFilters"
                  @click="clearAdvanceFilters"
                  class="text-[10px] font-bold text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 underline cursor-pointer bg-transparent border-0"
                >
                  Reset Filters
                </button>
              </div>

              <!-- Multi-Criteria Grid -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                
                <!-- Search by SKU -->
                <div class="flex items-center gap-3">
                  <label class="w-32 shrink-0 text-slate-500 dark:text-zinc-400 font-medium">Search by SKU</label>
                  <input
                    v-model="advanceFilters.sku"
                    type="text"
                    placeholder="Search by SKU"
                    @keydown.enter.prevent="handleAdvanceSearchEnter"
                    class="flex-1 px-3 py-2 bg-slate-50 dark:bg-[#12161b] border border-slate-300 dark:border-[#2E2E2E] focus:border-sky-500 dark:focus:border-sky-400 focus:ring-0 focus-visible:ring-0 focus:outline-none shadow-none rounded-xl text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-zinc-500"
                  />
                </div>

                <!-- Search by Brands -->
                <div class="flex items-center gap-3 relative">
                  <label class="w-32 shrink-0 text-slate-500 dark:text-zinc-400 font-medium">Search by Brands</label>
                  <div class="flex-1 relative">
                    <button
                      type="button"
                      @click.stop="toggleDropdown('brand')"
                      class="w-full px-3 py-2 bg-slate-50 dark:bg-[#12161b] border border-slate-300 dark:border-[#2E2E2E] rounded-xl flex items-center justify-between text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:border-sky-500 dark:focus:border-sky-400"
                    >
                      <span class="truncate" :class="{ 'text-slate-400 dark:text-zinc-500': !advanceFilters.brand_id }">
                        {{ getSelectedBrandName() || 'All Brands' }}
                      </span>
                      <svg class="w-3.5 h-3.5 text-slate-400 dark:text-zinc-400 shrink-0 ml-1 transition-transform" :class="{ 'rotate-180': activeDropdown === 'brand' }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>

                    <div
                      v-show="activeDropdown === 'brand'"
                      @click.stop
                      class="absolute left-0 right-0 mt-1 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl shadow-2xl z-[100] p-1.5 text-xs custom-scrollbar"
                    >
                      <div class="p-1 mb-1 border-b border-slate-100 dark:border-[#2E2E2E]">
                        <input
                          ref="brandSearchInputRef"
                          v-model="brandSearchQuery"
                          type="text"
                          placeholder="Search brands..."
                          class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-[#12161b] border border-slate-200 dark:border-[#2E2E2E] rounded-lg text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:border-sky-500"
                        />
                      </div>
                      <div class="max-h-44 overflow-y-auto custom-scrollbar space-y-0.5">
                        <div
                          @click="selectBrand('')"
                          class="px-2.5 py-1.5 rounded-lg cursor-pointer flex items-center justify-between text-slate-500 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                          :class="{ 'font-bold text-sky-600 dark:text-sky-400 bg-sky-50/50 dark:bg-sky-950/30': !advanceFilters.brand_id }"
                        >
                          <span>All Brands</span>
                          <span v-if="!advanceFilters.brand_id" class="font-bold text-sky-600 dark:text-sky-400">✓</span>
                        </div>
                        <div
                          v-for="b in filteredBrands"
                          :key="b.id"
                          @click="selectBrand(b.id)"
                          class="px-2.5 py-1.5 rounded-lg cursor-pointer flex items-center justify-between text-slate-700 dark:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                          :class="{ 'font-bold text-sky-600 dark:text-sky-400 bg-sky-50/50 dark:bg-sky-950/30': String(advanceFilters.brand_id) === String(b.id) }"
                        >
                          <span>{{ b.name }}</span>
                          <span v-if="String(advanceFilters.brand_id) === String(b.id)" class="font-bold text-sky-600 dark:text-sky-400">✓</span>
                        </div>
                        <div v-if="filteredBrands.length === 0" class="px-2.5 py-2 text-center text-slate-400 dark:text-zinc-500 italic text-[11px]">
                          No brands found
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Search by Category (Main Category) -->
                <div class="flex items-center gap-3 relative">
                  <label class="w-32 shrink-0 text-slate-500 dark:text-zinc-400 font-medium">Category</label>
                  <div class="flex-1 relative">
                    <button
                      type="button"
                      @click.stop="toggleDropdown('mainCat')"
                      class="w-full px-3 py-2 bg-slate-50 dark:bg-[#12161b] border border-slate-300 dark:border-[#2E2E2E] rounded-xl flex items-center justify-between text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:border-sky-500 dark:focus:border-sky-400"
                    >
                      <span class="truncate" :class="{ 'text-slate-400 dark:text-zinc-500': !advanceFilters.category_id }">
                        {{ getSelectedMainCategoryName() || 'All Main Categories' }}
                      </span>
                      <svg class="w-3.5 h-3.5 text-slate-400 dark:text-zinc-400 shrink-0 ml-1 transition-transform" :class="{ 'rotate-180': activeDropdown === 'mainCat' }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>

                    <div
                      v-show="activeDropdown === 'mainCat'"
                      @click.stop
                      class="absolute left-0 right-0 mt-1 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl shadow-2xl z-[100] p-1.5 text-xs custom-scrollbar"
                    >
                      <div class="p-1 mb-1 border-b border-slate-100 dark:border-[#2E2E2E]">
                        <input
                          ref="mainCatSearchInputRef"
                          v-model="mainCategorySearchQuery"
                          type="text"
                          placeholder="Search categories..."
                          class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-[#12161b] border border-slate-200 dark:border-[#2E2E2E] rounded-lg text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:border-sky-500"
                        />
                      </div>
                      <div class="max-h-44 overflow-y-auto custom-scrollbar space-y-0.5">
                        <div
                          @click="selectMainCategory('')"
                          class="px-2.5 py-1.5 rounded-lg cursor-pointer flex items-center justify-between text-slate-500 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                          :class="{ 'font-bold text-sky-600 dark:text-sky-400 bg-sky-50/50 dark:bg-sky-950/30': !advanceFilters.category_id }"
                        >
                          <span>All Main Categories</span>
                          <span v-if="!advanceFilters.category_id" class="font-bold text-sky-600 dark:text-sky-400">✓</span>
                        </div>
                        <div
                          v-for="cat in filteredMainCategories"
                          :key="cat.id"
                          @click="selectMainCategory(cat.id)"
                          class="px-2.5 py-1.5 rounded-lg cursor-pointer flex items-center justify-between text-slate-700 dark:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                          :class="{ 'font-bold text-sky-600 dark:text-sky-400 bg-sky-50/50 dark:bg-sky-950/30': String(advanceFilters.category_id) === String(cat.id) }"
                        >
                          <span>{{ cat.name }}</span>
                          <span v-if="String(advanceFilters.category_id) === String(cat.id)" class="font-bold text-sky-600 dark:text-sky-400">✓</span>
                        </div>
                        <div v-if="filteredMainCategories.length === 0" class="px-2.5 py-2 text-center text-slate-400 dark:text-zinc-500 italic text-[11px]">
                          No categories found
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Search by Sub Category -->
                <div class="flex items-center gap-3 relative">
                  <label class="w-32 shrink-0 text-slate-500 dark:text-zinc-400 font-medium">Sub Category</label>
                  <div class="flex-1 relative">
                    <button
                      type="button"
                      @click.stop="toggleDropdown('subCat')"
                      class="w-full px-3 py-2 bg-slate-50 dark:bg-[#12161b] border border-slate-300 dark:border-[#2E2E2E] rounded-xl flex items-center justify-between text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:border-sky-500 dark:focus:border-sky-400"
                    >
                      <span class="truncate" :class="{ 'text-slate-400 dark:text-zinc-500': !advanceFilters.subcategory_id }">
                        {{ getSelectedSubCategoryName() || 'All Sub Categories' }}
                      </span>
                      <svg class="w-3.5 h-3.5 text-slate-400 dark:text-zinc-400 shrink-0 ml-1 transition-transform" :class="{ 'rotate-180': activeDropdown === 'subCat' }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>

                    <div
                      v-show="activeDropdown === 'subCat'"
                      @click.stop
                      class="absolute left-0 right-0 mt-1 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl shadow-2xl z-[100] p-1.5 text-xs custom-scrollbar"
                    >
                      <div class="p-1 mb-1 border-b border-slate-100 dark:border-[#2E2E2E]">
                        <input
                          ref="subCatSearchInputRef"
                          v-model="subCategorySearchQuery"
                          type="text"
                          placeholder="Search subcategories..."
                          class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-[#12161b] border border-slate-200 dark:border-[#2E2E2E] rounded-lg text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:border-sky-500"
                        />
                      </div>
                      <div class="max-h-44 overflow-y-auto custom-scrollbar space-y-0.5">
                        <div
                          @click="selectSubCategory('')"
                          class="px-2.5 py-1.5 rounded-lg cursor-pointer flex items-center justify-between text-slate-500 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                          :class="{ 'font-bold text-sky-600 dark:text-sky-400 bg-sky-50/50 dark:bg-sky-950/30': !advanceFilters.subcategory_id }"
                        >
                          <span>All Sub Categories</span>
                          <span v-if="!advanceFilters.subcategory_id" class="font-bold text-sky-600 dark:text-sky-400">✓</span>
                        </div>
                        <div
                          v-for="cat in filteredSubCategories"
                          :key="cat.id"
                          @click="selectSubCategory(cat.id)"
                          class="px-2.5 py-1.5 rounded-lg cursor-pointer flex items-center justify-between text-slate-700 dark:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                          :class="{ 'font-bold text-sky-600 dark:text-sky-400 bg-sky-50/50 dark:bg-sky-950/30': String(advanceFilters.subcategory_id) === String(cat.id) }"
                        >
                          <span>{{ cat.name }}</span>
                          <span v-if="String(advanceFilters.subcategory_id) === String(cat.id)" class="font-bold text-sky-600 dark:text-sky-400">✓</span>
                        </div>
                        <div v-if="filteredSubCategories.length === 0" class="px-2.5 py-2 text-center text-slate-400 dark:text-zinc-500 italic text-[11px]">
                          No subcategories found
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Search by Child Category -->
                <div class="flex items-center gap-3 relative">
                  <label class="w-32 shrink-0 text-slate-500 dark:text-zinc-400 font-medium">Child Category</label>
                  <div class="flex-1 relative">
                    <button
                      type="button"
                      @click.stop="toggleDropdown('childCat')"
                      class="w-full px-3 py-2 bg-slate-50 dark:bg-[#12161b] border border-slate-300 dark:border-[#2E2E2E] rounded-xl flex items-center justify-between text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:border-sky-500 dark:focus:border-sky-400"
                    >
                      <span class="truncate" :class="{ 'text-slate-400 dark:text-zinc-500': !advanceFilters.child_category_id }">
                        {{ getSelectedChildCategoryName() || 'All Child Categories' }}
                      </span>
                      <svg class="w-3.5 h-3.5 text-slate-400 dark:text-zinc-400 shrink-0 ml-1 transition-transform" :class="{ 'rotate-180': activeDropdown === 'childCat' }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>

                    <div
                      v-show="activeDropdown === 'childCat'"
                      @click.stop
                      class="absolute left-0 right-0 mt-1 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl shadow-2xl z-[100] p-1.5 text-xs custom-scrollbar"
                    >
                      <div class="p-1 mb-1 border-b border-slate-100 dark:border-[#2E2E2E]">
                        <input
                          ref="childCatSearchInputRef"
                          v-model="childCategorySearchQuery"
                          type="text"
                          placeholder="Search child categories..."
                          class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-[#12161b] border border-slate-200 dark:border-[#2E2E2E] rounded-lg text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:border-sky-500"
                        />
                      </div>
                      <div class="max-h-44 overflow-y-auto custom-scrollbar space-y-0.5">
                        <div
                          @click="selectChildCategory('')"
                          class="px-2.5 py-1.5 rounded-lg cursor-pointer flex items-center justify-between text-slate-500 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                          :class="{ 'font-bold text-sky-600 dark:text-sky-400 bg-sky-50/50 dark:bg-sky-950/30': !advanceFilters.child_category_id }"
                        >
                          <span>All Child Categories</span>
                          <span v-if="!advanceFilters.child_category_id" class="font-bold text-sky-600 dark:text-sky-400">✓</span>
                        </div>
                        <div
                          v-for="cat in filteredChildCategories"
                          :key="cat.id"
                          @click="selectChildCategory(cat.id)"
                          class="px-2.5 py-1.5 rounded-lg cursor-pointer flex items-center justify-between text-slate-700 dark:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                          :class="{ 'font-bold text-sky-600 dark:text-sky-400 bg-sky-50/50 dark:bg-sky-950/30': String(advanceFilters.child_category_id) === String(cat.id) }"
                        >
                          <span>{{ cat.name }}</span>
                          <span v-if="String(advanceFilters.child_category_id) === String(cat.id)" class="font-bold text-sky-600 dark:text-sky-400">✓</span>
                        </div>
                        <div v-if="filteredChildCategories.length === 0" class="px-2.5 py-2 text-center text-slate-400 dark:text-zinc-500 italic text-[11px]">
                          No child categories found
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Search by Tags -->
                <div class="flex items-center gap-3">
                  <label class="w-32 shrink-0 text-slate-500 dark:text-zinc-400 font-medium">Search by Tags</label>
                  <div class="flex-1 relative">
                    <div
                      @click="focusTagInput"
                      class="min-h-[38px] px-2.5 py-1 bg-slate-50 dark:bg-[#12161b] border border-slate-300 dark:border-[#2E2E2E] rounded-xl flex items-center justify-between cursor-text flex-wrap gap-1 focus-within:border-sky-500 dark:focus-within:border-sky-400 focus-within:ring-0 focus-within:outline-none transition-all"
                    >
                      <div class="flex flex-wrap items-center gap-1 flex-1 min-w-0">
                        <span
                          v-for="t in advanceFilters.tags"
                          :key="t"
                          class="bg-slate-200 dark:bg-zinc-800 text-slate-800 dark:text-zinc-200 text-[10px] font-semibold px-2 py-0.5 rounded-md border border-slate-300 dark:border-[#2E2E2E] flex items-center gap-1 shrink-0"
                        >
                          {{ t }}
                          <span @click.stop="removeAdvanceTag(t)" class="hover:text-rose-500 dark:hover:text-rose-400 cursor-pointer font-bold">×</span>
                        </span>

                        <input
                          ref="tagInputRef"
                          v-model="tagSearchQuery"
                          type="text"
                          placeholder="Search by Tags"
                          @focus="openTagDropdown"
                          @keydown.down.prevent="navigateTagOptions(1)"
                          @keydown.up.prevent="navigateTagOptions(-1)"
                          @keydown.enter.prevent="selectHighlightedTag"
                          @keydown.esc.prevent="isTagDropdownOpen = false"
                          @keydown.delete="handleTagDeleteKey"
                          class="flex-1 min-w-[80px] bg-transparent border-0 outline-none focus:outline-none focus:ring-0 focus-visible:ring-0 focus:border-transparent ring-0 shadow-none text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-zinc-500 py-0.5"
                          style="background: transparent !important; border: none !important; outline: none !important; box-shadow: none !important;"
                        />
                      </div>
                      <svg class="w-3.5 h-3.5 text-slate-400 dark:text-zinc-400 shrink-0 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </div>
                    <!-- Tag Options Menu -->
                    <div v-show="isTagDropdownOpen" class="absolute left-0 right-0 mt-1 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl shadow-2xl z-50 max-h-48 overflow-y-auto p-1 text-xs custom-scrollbar">
                      <div v-if="filteredAvailableTags.length === 0" class="px-3 py-2 text-slate-400 dark:text-zinc-500 text-xs italic text-center">
                        No tags found
                      </div>
                      <div
                        v-for="(t, idx) in filteredAvailableTags"
                        :key="t"
                        @click="toggleAdvanceTag(t)"
                        @mouseenter="tagHighlightedIndex = idx"
                        class="px-2.5 py-1.5 rounded-lg cursor-pointer flex items-center justify-between transition-colors"
                        :class="[
                          advanceFilters.tags.includes(t) ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-700 dark:text-zinc-200',
                          tagHighlightedIndex === idx ? 'bg-slate-100 dark:bg-zinc-800' : 'hover:bg-slate-100 dark:hover:bg-zinc-800/60'
                        ]"
                      >
                        <span>{{ t }}</span>
                        <span v-if="advanceFilters.tags.includes(t)" class="text-indigo-600 dark:text-indigo-400 font-bold">✓</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Search by Tax -->
                <div class="flex items-center gap-3">
                  <label class="w-32 shrink-0 text-slate-500 dark:text-zinc-400 font-medium">Search by Tax</label>
                  <div class="flex-1 relative">
                    <div
                      @click="focusTaxInput"
                      class="min-h-[38px] px-2.5 py-1 bg-slate-50 dark:bg-[#12161b] border border-slate-300 dark:border-[#2E2E2E] rounded-xl flex items-center justify-between cursor-text flex-wrap gap-1 focus-within:border-sky-500 dark:focus-within:border-sky-400 focus-within:ring-0 focus-within:outline-none transition-all"
                    >
                      <div class="flex flex-wrap items-center gap-1 flex-1 min-w-0">
                        <span
                          v-for="tx in advanceFilters.taxes"
                          :key="tx"
                          class="bg-slate-200 dark:bg-zinc-800 text-slate-800 dark:text-zinc-200 text-[10px] font-semibold px-2 py-0.5 rounded-md border border-slate-300 dark:border-[#2E2E2E] flex items-center gap-1 shrink-0"
                        >
                          {{ getTaxLabel(tx) }}
                          <span @click.stop="removeAdvanceTaxItem(tx)" class="hover:text-rose-500 dark:hover:text-rose-400 cursor-pointer font-bold">×</span>
                        </span>

                        <input
                          ref="taxInputRef"
                          v-model="taxSearchQuery"
                          type="text"
                          placeholder="Search by Tax"
                          @focus="openTaxDropdown"
                          @keydown.down.prevent="navigateTaxOptions(1)"
                          @keydown.up.prevent="navigateTaxOptions(-1)"
                          @keydown.enter.prevent="selectHighlightedTax"
                          @keydown.esc.prevent="isTaxDropdownOpen = false"
                          @keydown.delete="handleTaxDeleteKey"
                          class="flex-1 min-w-[80px] bg-transparent border-0 outline-none focus:outline-none focus:ring-0 focus-visible:ring-0 focus:border-transparent ring-0 shadow-none text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-zinc-500 py-0.5"
                          style="background: transparent !important; border: none !important; outline: none !important; box-shadow: none !important;"
                        />
                      </div>
                      <svg class="w-3.5 h-3.5 text-slate-400 dark:text-zinc-400 shrink-0 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </div>
                    <!-- Tax Options Menu -->
                    <div v-show="isTaxDropdownOpen" class="absolute left-0 right-0 mt-1 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl shadow-2xl z-50 max-h-48 overflow-y-auto p-1 text-xs custom-scrollbar">
                      <div v-if="filteredAvailableTaxes.length === 0" class="px-3 py-2 text-slate-400 dark:text-zinc-500 text-xs italic text-center">
                        No taxes found
                      </div>
                      <div
                        v-for="(tax, idx) in filteredAvailableTaxes"
                        :key="tax.id"
                        @click="toggleAdvanceTax(tax.id)"
                        @mouseenter="taxHighlightedIndex = idx"
                        class="px-2.5 py-1.5 rounded-lg cursor-pointer flex items-center justify-between transition-colors"
                        :class="[
                          advanceFilters.taxes.includes(tax.id) ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-700 dark:text-zinc-200',
                          taxHighlightedIndex === idx ? 'bg-slate-100 dark:bg-zinc-800' : 'hover:bg-slate-100 dark:hover:bg-zinc-800/60'
                        ]"
                      >
                        <span>{{ tax.name }} ({{ tax.value }}%)</span>
                        <span v-if="advanceFilters.taxes.includes(tax.id)" class="text-indigo-600 dark:text-indigo-400 font-bold">✓</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Search by Price -->
                <div class="flex items-center gap-3">
                  <label class="w-32 shrink-0 text-slate-500 dark:text-zinc-400 font-medium">Search by Price</label>
                  <div class="flex-1 flex items-center gap-1.5 min-w-0">
                    <span class="text-slate-500 dark:text-zinc-400 font-medium text-[11px]">min</span>
                    <div class="relative flex-1 min-w-0">
                      <span class="absolute inset-y-0 left-2 flex items-center text-slate-400 dark:text-zinc-500 text-[11px] pointer-events-none">{{ currencySymbol }}</span>
                      <input
                        v-model="advanceFilters.minPrice"
                        type="number"
                        placeholder="0"
                        class="w-full pl-5 pr-2 py-1.5 bg-slate-50 dark:bg-[#12161b] border border-slate-300 dark:border-[#2E2E2E] rounded-xl text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none focus:border-sky-500 dark:focus:border-sky-400 focus:ring-0 focus-visible:ring-0 shadow-none"
                      />
                    </div>
                    <span class="text-slate-500 dark:text-zinc-400 font-medium text-[11px]">- max</span>
                    <div class="relative flex-1 min-w-0">
                      <span class="absolute inset-y-0 left-2 flex items-center text-slate-400 dark:text-zinc-500 text-[11px] pointer-events-none">{{ currencySymbol }}</span>
                      <input
                        v-model="advanceFilters.maxPrice"
                        type="number"
                        placeholder="9999"
                        class="w-full pl-5 pr-2 py-1.5 bg-slate-50 dark:bg-[#12161b] border border-slate-300 dark:border-[#2E2E2E] rounded-xl text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none focus:border-sky-500 dark:focus:border-sky-400 focus:ring-0 focus-visible:ring-0 shadow-none"
                      />
                    </div>
                  </div>
                </div>

              </div>
            </div>

            <!-- 3. Search Results Table -->
            <div class="border border-slate-200 dark:border-[#2E2E2E] rounded-xl overflow-hidden bg-white dark:bg-[#12161b]">
              <div class="max-h-64 overflow-y-auto custom-scrollbar">
                <table class="w-full text-xs text-left">
                  <thead class="bg-slate-100 dark:bg-[#252525] text-slate-500 dark:text-zinc-400 font-extrabold uppercase text-[10px] tracking-wider sticky top-0 z-10 border-b border-slate-200 dark:border-[#2E2E2E]">
                    <tr>
                      <th class="py-2.5 px-3">Item Details / Description</th>
                      <th class="py-2.5 px-3">Tags</th>
                      <th class="py-2.5 px-3">Tax</th>
                      <th class="py-2.5 px-3 text-right">Price</th>
                      <th class="py-2.5 px-3 text-center">Action</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-200 dark:divide-[#2E2E2E] text-slate-800 dark:text-slate-200">
                    <tr v-if="!hasActiveAdvanceFilters">
                      <td colspan="5" class="py-12 text-center text-slate-400 dark:text-zinc-500 italic">
                        <svg class="mx-auto h-7 w-7 text-slate-400 dark:text-zinc-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Start typing in search box or select a filter criteria above to search items...</span>
                      </td>
                    </tr>
                    <tr v-else-if="advanceFilteredProducts.length === 0">
                      <td colspan="5" class="py-10 text-center text-slate-400 dark:text-zinc-500 italic">
                        No products match the selected advance search criteria.
                      </td>
                    </tr>
                    <tr
                      v-for="product in advanceFilteredProducts.slice(0, 100)"
                      :key="product.key"
                      class="hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                    >
                      <td class="py-2.5 px-3">
                        <div class="flex items-center gap-2 flex-wrap">
                          <span class="font-bold text-slate-900 dark:text-slate-100 text-xs">{{ product.name }}</span>
                          <span
                            v-if="product.brand_name || product.brand"
                            class="inline-block px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 rounded border border-slate-300 dark:border-zinc-700 leading-none"
                          >
                            {{ product.brand_name || (typeof product.brand === 'string' ? product.brand : product.brand?.name) }}
                          </span>
                        </div>
                        <div v-if="product.sku" class="text-[10px] text-slate-400 dark:text-zinc-500 font-mono mt-0.5">
                          SKU: {{ product.sku }}
                        </div>
                        <div
                          v-if="product.category_path || product.category"
                          class="text-[9.5px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider truncate mt-0.5"
                        >
                          {{ product.category_path || (typeof product.category === 'string' ? product.category : product.category?.name) }}
                        </div>
                      </td>
                      <td class="py-2.5 px-3 text-slate-500 dark:text-zinc-400">
                        <span v-if="product.tags && product.tags.length">{{ Array.isArray(product.tags) ? product.tags.join(', ') : product.tags }}</span>
                        <span v-else class="text-slate-400 dark:text-zinc-600">—</span>
                      </td>
                      <td class="py-2.5 px-3 text-slate-500 dark:text-zinc-400">{{ product.tax_rate ? product.tax_rate + '%' : 'No Tax' }}</td>
                      <td class="py-2.5 px-3 text-right font-extrabold text-emerald-600 dark:text-emerald-400">{{ currencySymbol }}{{ getDisplayPrice(product) }}</td>
                      <td class="py-2.5 px-3 text-center">
                        <button
                          type="button"
                          @click="addAdvanceProductToInvoice(product)"
                          class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white font-extrabold rounded-lg text-[11px] shadow-sm transition-all cursor-pointer border-0"
                        >
                          + Add
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-if="hasActiveAdvanceFilters" class="px-4 py-2 bg-slate-50 dark:bg-[#252525] border-t border-slate-200 dark:border-[#2E2E2E] text-[10px] text-slate-500 dark:text-zinc-400 font-semibold flex items-center justify-between">
                <span>Showing {{ Math.min(advanceFilteredProducts.length, 100) }} of {{ advanceFilteredProducts.length }} items</span>
                <span class="text-slate-400 dark:text-zinc-500">Click "+ Add" to append items directly to invoice</span>
              </div>
              <div v-else class="px-4 py-2 bg-slate-50 dark:bg-[#252525] border-t border-slate-200 dark:border-[#2E2E2E] text-[10px] text-slate-400 dark:text-zinc-500 font-semibold text-center">
                Enter search query or select any filter above to view items
              </div>
            </div>

          </div>
        </div>
      </div>
    </transition>
    </teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import api from '@/services/api';
import soundService from '@/services/SoundService';

const props = defineProps({
  products: { type: Array, required: true },
  categories: { type: Array, default: () => [] },
  taxes: { type: Array, default: () => [] },
  currencySymbol: { type: String, default: 'PKR' },
  targetWarehouseId: { type: [String, Number], default: 'all' },
  priceType: { type: String, default: 'selling' },
  useCostPrice: { type: Boolean, default: false },
  mode: { type: String, default: '' },
  isPurchaseOrder: { type: Boolean, default: false },
  allowOutOfStockSelection: { type: Boolean, default: false }
});

const isPurchaseContext = computed(() => {
  return props.priceType === 'purchase' || 
         props.priceType === 'cost' || 
         props.mode === 'purchase' || 
         props.isPurchaseOrder === true || 
         props.allowOutOfStockSelection === true;
});

const emit = defineEmits(['product-selected']);

// Basic Search State
const searchInputRef = ref(null);
const isProductDropdownOpen = ref(false);
const productSearch = ref('');
const highlightedProductIndex = ref(-1);
const productItemRefs = ref({});

// Product search and filtering
const filteredProducts = computed(() => {
  let filtered = Array.isArray(props.products) ? props.products : [];
  if (productSearch.value) {
    const search = productSearch.value.toLowerCase();
    filtered = filtered.filter(product => {
      const nameMatch = product.name && product.name.toLowerCase().includes(search);
      const skuMatch = product.sku && product.sku.toLowerCase().includes(search);
      const barcodeMatch = product.barcode && product.barcode.toLowerCase().includes(search);
      const brandMatch = (product.brand_name && product.brand_name.toLowerCase().includes(search)) ||
                         (typeof product.brand === 'string' && product.brand.toLowerCase().includes(search));
      const catMatch = (product.category_path && product.category_path.toLowerCase().includes(search)) ||
                       (typeof product.category === 'string' && product.category.toLowerCase().includes(search));
      return nameMatch || skuMatch || barcodeMatch || brandMatch || catMatch;
    });
  }
  return filtered;
});

const displayedProducts = computed(() => {
  return filteredProducts.value.slice(0, 50);
});

watch(displayedProducts, (newProducts) => {
  if (newProducts.length > 0 && productSearch.value.trim() !== '') {
    highlightedProductIndex.value = 0;
  } else {
    highlightedProductIndex.value = -1;
  }
}, { immediate: true });




function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Advance Search Modal State
const isAdvanceSearchModalOpen = ref(false);
const advanceSearchInputRef = ref(null);
const isTagDropdownOpen = ref(false);
const isCategorySelectModalOpen = ref(false);
const isTaxDropdownOpen = ref(false);

const advanceFilters = ref({
  query: '',
  sku: '',
  brand_id: '',
  category_id: '',
  subcategory_id: '',
  child_category_id: '',
  tags: [],
  taxes: [],
  minPrice: null,
  maxPrice: null
});

// Category and Brand loading
const dbCategories = ref([]);
const dbBrands = ref([]);

const loadCategoriesAndBrands = async () => {
  try {
    const [catRes, brandRes] = await Promise.all([
      api.get('/categories'),
      api.get('/brands')
    ]);
    dbCategories.value = catRes.data.data || catRes.data || [];
    dbBrands.value = brandRes.data.data || brandRes.data || [];
  } catch (err) {
    console.error('Error loading categories/brands:', err);
  }
};

const allCategories = computed(() => {
  return dbCategories.value.length > 0 ? dbCategories.value : (props.categories || []);
});

const allBrands = computed(() => {
  return dbBrands.value.length > 0 ? dbBrands.value : [];
});

const categoryMap = computed(() => {
  const map = new Map();
  if (Array.isArray(allCategories.value)) {
    allCategories.value.forEach(c => map.set(String(c.id), c));
  }
  return map;
});

// Main Categories (no parent_id)
const mainCategories = computed(() => {
  if (!Array.isArray(allCategories.value)) return [];
  return allCategories.value.filter(c => !c.parent_id);
});

// Sub Categories
const availableSubCategories = computed(() => {
  if (!Array.isArray(allCategories.value)) return [];
  let subs = allCategories.value.filter(c => {
    if (!c.parent_id) return false;
    const p = categoryMap.value.get(String(c.parent_id));
    return !p || !p.parent_id;
  });

  if (advanceFilters.value.category_id) {
    subs = subs.filter(c => String(c.parent_id) === String(advanceFilters.value.category_id));
  }
  return subs;
});

// Child Categories
const availableChildCategories = computed(() => {
  if (!Array.isArray(allCategories.value)) return [];
  let children = allCategories.value.filter(c => {
    if (!c.parent_id) return false;
    const p = categoryMap.value.get(String(c.parent_id));
    return p && !!p.parent_id;
  });

  if (advanceFilters.value.subcategory_id) {
    children = children.filter(c => String(c.parent_id) === String(advanceFilters.value.subcategory_id));
  } else if (advanceFilters.value.category_id) {
    const validSubIds = new Set(availableSubCategories.value.map(s => String(s.id)));
    children = children.filter(c => validSubIds.has(String(c.parent_id)));
  }
  return children;
});

const onMainCategoryChange = () => {
  const selectedSub = categoryMap.value.get(String(advanceFilters.value.subcategory_id));
  if (selectedSub && String(selectedSub.parent_id) !== String(advanceFilters.value.category_id)) {
    advanceFilters.value.subcategory_id = '';
    advanceFilters.value.child_category_id = '';
  }
};

const onSubCategoryChange = () => {
  if (advanceFilters.value.subcategory_id) {
    const subCat = categoryMap.value.get(String(advanceFilters.value.subcategory_id));
    if (subCat && subCat.parent_id) {
      advanceFilters.value.category_id = subCat.parent_id;
    }
  }
  const selectedChild = categoryMap.value.get(String(advanceFilters.value.child_category_id));
  if (selectedChild && String(selectedChild.parent_id) !== String(advanceFilters.value.subcategory_id)) {
    advanceFilters.value.child_category_id = '';
  }
};

const onChildCategoryChange = () => {
  if (advanceFilters.value.child_category_id) {
    const childCat = categoryMap.value.get(String(advanceFilters.value.child_category_id));
    if (childCat && childCat.parent_id) {
      advanceFilters.value.subcategory_id = childCat.parent_id;
      const subCat = categoryMap.value.get(String(childCat.parent_id));
      if (subCat && subCat.parent_id) {
        advanceFilters.value.category_id = subCat.parent_id;
      }
    }
  }
};

// Floating Dropdown Searchable Select States & Helpers
const activeDropdown = ref(null); // 'brand', 'mainCat', 'subCat', 'childCat'

const brandSearchQuery = ref('');
const brandSearchInputRef = ref(null);

const mainCategorySearchQuery = ref('');
const mainCatSearchInputRef = ref(null);

const subCategorySearchQuery = ref('');
const subCatSearchInputRef = ref(null);

const childCategorySearchQuery = ref('');
const childCatSearchInputRef = ref(null);

const toggleDropdown = (name) => {
  if (activeDropdown.value === name) {
    activeDropdown.value = null;
  } else {
    activeDropdown.value = name;
    isTagDropdownOpen.value = false;
    isTaxDropdownOpen.value = false;
    nextTick(() => {
      if (name === 'brand' && brandSearchInputRef.value) brandSearchInputRef.value.focus();
      if (name === 'mainCat' && mainCatSearchInputRef.value) mainCatSearchInputRef.value.focus();
      if (name === 'subCat' && subCatSearchInputRef.value) subCatSearchInputRef.value.focus();
      if (name === 'childCat' && childCatSearchInputRef.value) childCatSearchInputRef.value.focus();
    });
  }
};

// Filtered options
const filteredBrands = computed(() => {
  const q = brandSearchQuery.value.trim().toLowerCase();
  if (!q) return allBrands.value;
  return allBrands.value.filter(b => b.name && b.name.toLowerCase().includes(q));
});

const filteredMainCategories = computed(() => {
  const q = mainCategorySearchQuery.value.trim().toLowerCase();
  if (!q) return mainCategories.value;
  return mainCategories.value.filter(c => c.name && c.name.toLowerCase().includes(q));
});

const filteredSubCategories = computed(() => {
  const q = subCategorySearchQuery.value.trim().toLowerCase();
  if (!q) return availableSubCategories.value;
  return availableSubCategories.value.filter(c => c.name && c.name.toLowerCase().includes(q));
});

const filteredChildCategories = computed(() => {
  const q = childCategorySearchQuery.value.trim().toLowerCase();
  if (!q) return availableChildCategories.value;
  return availableChildCategories.value.filter(c => c.name && c.name.toLowerCase().includes(q));
});

// Selection actions
const selectBrand = (id) => {
  advanceFilters.value.brand_id = id;
  activeDropdown.value = null;
  brandSearchQuery.value = '';
};

const selectMainCategory = (id) => {
  advanceFilters.value.category_id = id;
  onMainCategoryChange();
  activeDropdown.value = null;
  mainCategorySearchQuery.value = '';
};

const selectSubCategory = (id) => {
  advanceFilters.value.subcategory_id = id;
  onSubCategoryChange();
  activeDropdown.value = null;
  subCategorySearchQuery.value = '';
};

const selectChildCategory = (id) => {
  advanceFilters.value.child_category_id = id;
  onChildCategoryChange();
  activeDropdown.value = null;
  childCategorySearchQuery.value = '';
};

// Label getters
const getSelectedBrandName = () => {
  if (!advanceFilters.value.brand_id) return '';
  const b = allBrands.value.find(brand => String(brand.id) === String(advanceFilters.value.brand_id));
  return b ? b.name : '';
};

const getSelectedMainCategoryName = () => {
  if (!advanceFilters.value.category_id) return '';
  const c = mainCategories.value.find(cat => String(cat.id) === String(advanceFilters.value.category_id));
  return c ? c.name : '';
};

const getSelectedSubCategoryName = () => {
  if (!advanceFilters.value.subcategory_id) return '';
  const c = availableSubCategories.value.find(cat => String(cat.id) === String(advanceFilters.value.subcategory_id));
  return c ? c.name : '';
};

const getSelectedChildCategoryName = () => {
  if (!advanceFilters.value.child_category_id) return '';
  const c = availableChildCategories.value.find(cat => String(cat.id) === String(advanceFilters.value.child_category_id));
  return c ? c.name : '';
};

// Combobox Search Queries & Options Navigation States
const tagSearchQuery = ref('');
const tagHighlightedIndex = ref(0);
const tagInputRef = ref(null);

const taxSearchQuery = ref('');
const taxHighlightedIndex = ref(0);
const taxInputRef = ref(null);

const openAdvanceSearchModal = () => {
  isAdvanceSearchModalOpen.value = true;
  nextTick(() => {
    if (advanceSearchInputRef.value) {
      advanceSearchInputRef.value.focus();
    }
  });
  setTimeout(() => {
    if (advanceSearchInputRef.value) {
      advanceSearchInputRef.value.focus();
    }
  }, 100);
};

const closeAdvanceSearchModal = () => {
  isAdvanceSearchModalOpen.value = false;
  isTagDropdownOpen.value = false;
  isTaxDropdownOpen.value = false;
  activeDropdown.value = null;
  tagSearchQuery.value = '';
  taxSearchQuery.value = '';
  brandSearchQuery.value = '';
  mainCategorySearchQuery.value = '';
  subCategorySearchQuery.value = '';
  childCategorySearchQuery.value = '';

  // Multi-stage focus restoration back to main product search bar
  nextTick(() => focusSearchInput());
  setTimeout(() => focusSearchInput(), 50);
  setTimeout(() => focusSearchInput(), 150);
  setTimeout(() => focusSearchInput(), 300);
};

watch(isAdvanceSearchModalOpen, (isOpen) => {
  if (isOpen) {
    nextTick(() => {
      if (advanceSearchInputRef.value) {
        advanceSearchInputRef.value.focus();
      }
    });
    setTimeout(() => {
      if (advanceSearchInputRef.value) {
        advanceSearchInputRef.value.focus();
      }
    }, 100);
  } else {
    nextTick(() => focusSearchInput());
    setTimeout(() => focusSearchInput(), 50);
    setTimeout(() => focusSearchInput(), 150);
    setTimeout(() => focusSearchInput(), 300);
  }
});

const clearAdvanceFilters = () => {
  advanceFilters.value = {
    query: '',
    sku: '',
    brand_id: '',
    category_id: '',
    subcategory_id: '',
    child_category_id: '',
    tags: [],
    taxes: [],
    minPrice: null,
    maxPrice: null
  };
  activeDropdown.value = null;
  tagSearchQuery.value = '';
  taxSearchQuery.value = '';
  brandSearchQuery.value = '';
  mainCategorySearchQuery.value = '';
  subCategorySearchQuery.value = '';
  childCategorySearchQuery.value = '';
};

const hasActiveAdvanceFilters = computed(() => {
  const f = advanceFilters.value;
  return !!(
    (f.query && f.query.trim()) ||
    (f.sku && f.sku.trim()) ||
    f.brand_id ||
    f.category_id ||
    f.subcategory_id ||
    f.child_category_id ||
    f.tags.length > 0 ||
    f.taxes.length > 0 ||
    (f.minPrice !== null && f.minPrice !== '' && !isNaN(f.minPrice)) ||
    (f.maxPrice !== null && f.maxPrice !== '' && !isNaN(f.maxPrice))
  );
});

const getProductUniqueKey = (p) => {
  if (p.key) return String(p.key);
  const prodId = p.product_id || p.id;
  const varId = p.product_variation_id || p.variation_id || null;
  return varId ? `var-${varId}` : `prod-${prodId}`;
};

const isSearchingAdvance = ref(false);

const searchItemsFromBackend = debounce(async () => {
  if (!hasActiveAdvanceFilters.value) return;
  try {
    isSearchingAdvance.value = true;
    const f = advanceFilters.value;
    const params = {};
    if (f.query && f.query.trim()) {
      params.search_term = f.query.trim();
      params.barcode = f.query.trim();
    }
    if (f.sku && f.sku.trim()) params.sku = f.sku.trim();
    if (f.brand_id) params.brand_id = f.brand_id;
    if (f.child_category_id) params.child_category_id = f.child_category_id;
    else if (f.subcategory_id) params.subcategory_id = f.subcategory_id;
    else if (f.category_id) params.category_id = f.category_id;

    if (f.tags.length > 0) params.tag_id = f.tags.join(',');
    if (f.minPrice !== null && f.minPrice !== '' && !isNaN(f.minPrice)) params.min_price = f.minPrice;
    if (f.maxPrice !== null && f.maxPrice !== '' && !isNaN(f.maxPrice)) params.max_price = f.maxPrice;

    const res = await api.get('/items/advanced-search', { params });
    const remoteItems = res.data.items || res.data.data || [];
    if (remoteItems.length > 0) {
      const existingKeys = new Set(props.products.map(p => getProductUniqueKey(p)));
      remoteItems.forEach(item => {
        const itemKey = getProductUniqueKey(item);
        if (!existingKeys.has(itemKey)) {
          emit('products-fetched', [item]);
        }
      });
    }
  } catch (err) {
    console.error('Advanced search API error:', err);
  } finally {
    isSearchingAdvance.value = false;
  }
}, 300);

const advanceFilteredProducts = computed(() => {
  if (!hasActiveAdvanceFilters.value) {
    return [];
  }

  let list = Array.isArray(props.products) ? props.products : [];

  const f = advanceFilters.value;

  if (f.query && f.query.trim()) {
    const q = f.query.trim().toLowerCase();
    list = list.filter(p =>
      (p.name && p.name.toLowerCase().includes(q)) ||
      (p.description && p.description.toLowerCase().includes(q)) ||
      (p.sku && p.sku.toLowerCase().includes(q)) ||
      (p.barcode && p.barcode.toLowerCase().includes(q))
    );
  }

  if (f.sku && f.sku.trim()) {
    const s = f.sku.trim().toLowerCase();
    list = list.filter(p => (p.sku && p.sku.toLowerCase().includes(s)) || (p.barcode && p.barcode.toLowerCase().includes(s)));
  }

  if (f.brand_id) {
    const selectedBrandObj = allBrands.value.find(b => String(b.id) === String(f.brand_id));
    const brandName = selectedBrandObj ? selectedBrandObj.name.toLowerCase() : null;

    list = list.filter(p => 
      String(p.brand_id) === String(f.brand_id) || 
      String(p.brand?.id) === String(f.brand_id) ||
      (brandName && (
        (p.brand_name && p.brand_name.toLowerCase() === brandName) ||
        (typeof p.brand === 'string' && p.brand.toLowerCase() === brandName)
      ))
    );
  }

  if (f.child_category_id) {
    list = list.filter(p => String(p.category_id) === String(f.child_category_id));
  } else if (f.subcategory_id) {
    const subId = String(f.subcategory_id);
    const validChildIds = new Set(allCategories.value.filter(c => String(c.parent_id) === subId).map(c => String(c.id)));
    validChildIds.add(subId);
    list = list.filter(p => validChildIds.has(String(p.category_id)));
  } else if (f.category_id) {
    const mainId = String(f.category_id);
    const subCatIds = allCategories.value.filter(c => String(c.parent_id) === mainId).map(c => String(c.id));
    const childCatIds = allCategories.value.filter(c => subCatIds.includes(String(c.parent_id))).map(c => String(c.id));
    const allMatchingCatIds = new Set([mainId, ...subCatIds, ...childCatIds]);
    list = list.filter(p => allMatchingCatIds.has(String(p.category_id)));
  }

  if (f.tags.length > 0) {
    list = list.filter(p => {
      const pTags = Array.isArray(p.tags) ? p.tags : [];
      return f.tags.some(t => pTags.includes(t));
    });
  }

  if (f.taxes.length > 0) {
    list = list.filter(p => {
      return f.taxes.some(taxId => p.tax_ids && p.tax_ids.includes(taxId));
    });
  }

  if (f.minPrice !== null && f.minPrice !== '' && !isNaN(f.minPrice)) {
    list = list.filter(p => (p.price || p.selling_price || 0) >= parseFloat(f.minPrice));
  }

  if (f.maxPrice !== null && f.maxPrice !== '' && !isNaN(f.maxPrice)) {
    list = list.filter(p => (p.price || p.selling_price || 0) <= parseFloat(f.maxPrice));
  }

  const seenKeys = new Set();
  const uniqueList = [];
  for (const item of list) {
    const k = getProductUniqueKey(item);
    if (!seenKeys.has(k)) {
      seenKeys.add(k);
      uniqueList.push(item);
    }
  }

  return uniqueList;
});

watch(advanceFilters, () => {
  searchItemsFromBackend();
}, { deep: true });


const dbTags = ref([]);

const loadTags = async () => {
  try {
    const response = await api.get('/tags');
    dbTags.value = response.data.data || response.data || [];
  } catch (error) {
    console.error('Error loading tags:', error);
  }
};

const availableTags = computed(() => {
  const set = new Set();
  if (Array.isArray(dbTags.value)) {
    dbTags.value.forEach(t => {
      if (t && t.name) set.add(t.name);
      else if (typeof t === 'string') set.add(t);
    });
  }
  props.products.forEach(p => {
    if (Array.isArray(p.tags)) {
      p.tags.forEach(t => set.add(t));
    }
  });
  return Array.from(set);
});

const filteredAvailableTags = computed(() => {
  const query = tagSearchQuery.value.trim().toLowerCase();
  if (!query) return availableTags.value;
  return availableTags.value.filter(t => t.toLowerCase().includes(query));
});

const filteredAvailableCategories = computed(() => {
  const query = categorySearchQuery.value.trim().toLowerCase();
  if (!query) return props.categories;
  return props.categories.filter(cat => cat.name && cat.name.toLowerCase().includes(query));
});

const filteredAvailableTaxes = computed(() => {
  const query = taxSearchQuery.value.trim().toLowerCase();
  if (!query) return props.taxes;
  return props.taxes.filter(t => 
    (t.name && t.name.toLowerCase().includes(query)) ||
    String(t.value).includes(query)
  );
});

const getCategoryNameById = (id) => {
  const cat = props.categories.find(c => String(c.id) === String(id));
  return cat ? cat.name : id;
};

const getTaxLabel = (taxId) => {
  const tx = props.taxes.find(t => String(t.id) === String(taxId));
  return tx ? `${tx.name} (${tx.value}%)` : `Tax ${taxId}`;
};

// Tag Combobox Helpers
const openTagDropdown = () => {
  isTagDropdownOpen.value = true;
  isCategorySelectModalOpen.value = false;
  isTaxDropdownOpen.value = false;
  tagHighlightedIndex.value = 0;
};

const focusTagInput = () => {
  if (tagInputRef.value) tagInputRef.value.focus();
  openTagDropdown();
};

const navigateTagOptions = (direction) => {
  if (!isTagDropdownOpen.value) { openTagDropdown(); return; }
  const count = filteredAvailableTags.value.length;
  if (count === 0) return;
  tagHighlightedIndex.value = (tagHighlightedIndex.value + direction + count) % count;
};

const selectHighlightedTag = () => {
  if (!isTagDropdownOpen.value) return;
  const count = filteredAvailableTags.value.length;
  if (count > 0 && tagHighlightedIndex.value >= 0 && tagHighlightedIndex.value < count) {
    const selectedTag = filteredAvailableTags.value[tagHighlightedIndex.value];
    toggleAdvanceTag(selectedTag);
    tagSearchQuery.value = '';
  }
};

const handleTagDeleteKey = () => {
  if (tagSearchQuery.value === '' && advanceFilters.value.tags.length > 0) {
    advanceFilters.value.tags.pop();
  }
};

const toggleAdvanceTag = (tag) => {
  const idx = advanceFilters.value.tags.indexOf(tag);
  if (idx > -1) {
    advanceFilters.value.tags.splice(idx, 1);
  } else {
    advanceFilters.value.tags.push(tag);
  }
  tagSearchQuery.value = '';
};

const removeAdvanceTag = (tag) => {
  const idx = advanceFilters.value.tags.indexOf(tag);
  if (idx > -1) {
    advanceFilters.value.tags.splice(idx, 1);
  }
};

// Category Combobox Helpers
const openCategoryDropdown = () => {
  isCategorySelectModalOpen.value = true;
  isTagDropdownOpen.value = false;
  isTaxDropdownOpen.value = false;
  categoryHighlightedIndex.value = 0;
};

const focusCategoryInput = () => {
  if (categoryInputRef.value) categoryInputRef.value.focus();
  openCategoryDropdown();
};

const navigateCategoryOptions = (direction) => {
  if (!isCategorySelectModalOpen.value) { openCategoryDropdown(); return; }
  const count = filteredAvailableCategories.value.length;
  if (count === 0) return;
  categoryHighlightedIndex.value = (categoryHighlightedIndex.value + direction + count) % count;
};

const selectHighlightedCategory = () => {
  if (!isCategorySelectModalOpen.value) return;
  const count = filteredAvailableCategories.value.length;
  if (count > 0 && categoryHighlightedIndex.value >= 0 && categoryHighlightedIndex.value < count) {
    const selectedCat = filteredAvailableCategories.value[categoryHighlightedIndex.value];
    toggleAdvanceCategory(selectedCat.id);
    categorySearchQuery.value = '';
  }
};

const handleCategoryDeleteKey = () => {
  if (categorySearchQuery.value === '' && advanceFilters.value.categories.length > 0) {
    advanceFilters.value.categories.pop();
  }
};

const toggleAdvanceCategory = (catId) => {
  const idx = advanceFilters.value.categories.indexOf(catId);
  if (idx > -1) {
    advanceFilters.value.categories.splice(idx, 1);
  } else {
    advanceFilters.value.categories.push(catId);
  }
  categorySearchQuery.value = '';
};

const removeAdvanceCategory = (catId) => {
  const idx = advanceFilters.value.categories.indexOf(catId);
  if (idx > -1) {
    advanceFilters.value.categories.splice(idx, 1);
  }
};

// Tax Combobox Helpers
const openTaxDropdown = () => {
  isTaxDropdownOpen.value = true;
  isTagDropdownOpen.value = false;
  isCategorySelectModalOpen.value = false;
  taxHighlightedIndex.value = 0;
};

const focusTaxInput = () => {
  if (taxInputRef.value) taxInputRef.value.focus();
  openTaxDropdown();
};

const navigateTaxOptions = (direction) => {
  if (!isTaxDropdownOpen.value) { openTaxDropdown(); return; }
  const count = filteredAvailableTaxes.value.length;
  if (count === 0) return;
  taxHighlightedIndex.value = (taxHighlightedIndex.value + direction + count) % count;
};

const selectHighlightedTax = () => {
  if (!isTaxDropdownOpen.value) return;
  const count = filteredAvailableTaxes.value.length;
  if (count > 0 && taxHighlightedIndex.value >= 0 && taxHighlightedIndex.value < count) {
    const selectedTax = filteredAvailableTaxes.value[taxHighlightedIndex.value];
    toggleAdvanceTax(selectedTax.id);
    taxSearchQuery.value = '';
  }
};

const handleTaxDeleteKey = () => {
  if (taxSearchQuery.value === '' && advanceFilters.value.taxes.length > 0) {
    advanceFilters.value.taxes.pop();
  }
};

const toggleAdvanceTax = (taxId) => {
  const idx = advanceFilters.value.taxes.indexOf(taxId);
  if (idx > -1) {
    advanceFilters.value.taxes.splice(idx, 1);
  } else {
    advanceFilters.value.taxes.push(taxId);
  }
  taxSearchQuery.value = '';
};

const removeAdvanceTaxItem = (taxId) => {
  const idx = advanceFilters.value.taxes.indexOf(taxId);
  if (idx > -1) {
    advanceFilters.value.taxes.splice(idx, 1);
  }
};
const setProductItemRef = (el, idx) => {
  if (el) productItemRefs.value[idx] = el;
};

const scrollToHighlightedItem = () => {
  nextTick(() => {
    const el = productItemRefs.value[highlightedProductIndex.value];
    if (el && typeof el.scrollIntoView === 'function') {
      el.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }
  });
};

const handleProductSearchKeydown = (event) => {
  if (event.key === 'ArrowDown') {
    event.preventDefault();
    if (!isProductDropdownOpen.value) isProductDropdownOpen.value = true;
    if (displayedProducts.value.length === 0) return;
    if (highlightedProductIndex.value < displayedProducts.value.length - 1) {
      highlightedProductIndex.value++;
    } else {
      highlightedProductIndex.value = 0;
    }
    scrollToHighlightedItem();
  } else if (event.key === 'ArrowUp') {
    event.preventDefault();
    if (!isProductDropdownOpen.value) isProductDropdownOpen.value = true;
    if (displayedProducts.value.length === 0) return;
    if (highlightedProductIndex.value > 0) {
      highlightedProductIndex.value--;
    } else {
      highlightedProductIndex.value = displayedProducts.value.length - 1;
    }
    scrollToHighlightedItem();
  } else if (event.key === 'Enter') {
    event.preventDefault();
    if (isProductDropdownOpen.value && displayedProducts.value.length > 0) {
      const targetIndex = (highlightedProductIndex.value >= 0 && highlightedProductIndex.value < displayedProducts.value.length)
        ? highlightedProductIndex.value
        : 0;
      const selectedProduct = displayedProducts.value[targetIndex];
      selectProductFromDropdown(selectedProduct);
    } else {
      handleProductSearchEnter();
    }
  } else if (event.key === 'Escape') {
    event.preventDefault();
    isProductDropdownOpen.value = false;
    highlightedProductIndex.value = -1;
  }
};

const isProductOutOfStock = (product) => {
  if (!product) return false;
  if (!product.track_inventory) return false;
  const targetWhId = props.targetWarehouseId;
  if (!targetWhId || targetWhId === 'all') {
    return (Number(product.total_stock) || 0) <= 0;
  }
  let whStock = 0;
  if (product.warehouse_stocks) {
    const stockVal = product.warehouse_stocks[targetWhId]
      ?? product.warehouse_stocks[String(targetWhId)]
      ?? product.warehouse_stocks[Number(targetWhId)];
    if (stockVal !== undefined && stockVal !== null) {
      whStock = Number(stockVal);
    }
  }
  return whStock <= 0 && (Number(product.total_stock) || 0) <= 0;
};

const getProductStock = (product) => {
  if (!product) return '0';
  if (!product.track_inventory) return '∞';
  const targetWhId = props.targetWarehouseId;
  if (!targetWhId || targetWhId === 'all') {
    return product.total_stock ?? 0;
  }
  let whStock = 0;
  if (product.warehouse_stocks) {
    const stockVal = product.warehouse_stocks[targetWhId]
      ?? product.warehouse_stocks[String(targetWhId)]
      ?? product.warehouse_stocks[Number(targetWhId)];
    if (stockVal !== undefined && stockVal !== null) {
      whStock = Number(stockVal);
    }
  }
  if (whStock === 0 && (product.total_stock ?? 0) > 0) {
    return `0 (${product.total_stock} Total)`;
  }
  return whStock;
};

const getDisplayPrice = (product) => {
  if (!product) return '0.00';
  if (props.priceType === 'cost' || props.priceType === 'purchase' || props.useCostPrice) {
    const cost = (product.cost_price !== undefined && product.cost_price !== null) 
      ? product.cost_price 
      : (product.purchase_price ?? product.unit_cost ?? product.price ?? 0);
    return Number(cost).toFixed(2);
  }
  const price = product.price ?? product.selling_price ?? 0;
  return Number(price).toFixed(2);
};

const selectProductFromDropdown = (product) => {
  if (isProductOutOfStock(product) && !isPurchaseContext.value) {
    soundService.playWarning();
    emit('product-selected', { product, error: 'Out of Stock' });
    return;
  }
  soundService.playSuccess();
  emit('product-selected', { product });
  productSearch.value = '';
  isProductDropdownOpen.value = false;
  highlightedProductIndex.value = -1;
  productItemRefs.value = {};
};

const handleProductSearchEnter = () => {
  const query = productSearch.value.trim().toLowerCase();
  if (!query) return;
  const matchedProduct = props.products.find(p => 
    (p.barcode && p.barcode.toLowerCase() === query) || 
    (p.sku && p.sku.toLowerCase() === query)
  ) || filteredProducts.value[0];
  if (matchedProduct) {
    selectProductFromDropdown(matchedProduct);
  } else {
    soundService.playWarning();
    emit('product-selected', { error: 'Not Found', query: productSearch.value });
  }
};

const addAdvanceProductToInvoice = (product) => {
  if (isProductOutOfStock(product) && !isPurchaseContext.value) {
    soundService.playWarning();
    emit('product-selected', { product, error: 'Out of Stock' });
    return;
  }
  soundService.playSuccess();
  emit('product-selected', { product, isAdvance: true });
};

const handleAdvanceSearchEnter = async () => {
  const query = advanceFilters.value.query ? advanceFilters.value.query.trim().toLowerCase() : '';
  const skuQuery = advanceFilters.value.sku ? advanceFilters.value.sku.trim().toLowerCase() : '';

  if (!query && !skuQuery && advanceFilteredProducts.value.length === 0) return;

  // 1. Try to find exact barcode or SKU match locally first
  let matchedProduct = null;
  if (query) {
    matchedProduct = advanceFilteredProducts.value.find(p =>
      (p.barcode && p.barcode.toLowerCase() === query) ||
      (p.sku && p.sku.toLowerCase() === query)
    );
  }
  if (!matchedProduct && skuQuery) {
    matchedProduct = advanceFilteredProducts.value.find(p =>
      (p.sku && p.sku.toLowerCase() === skuQuery) ||
      (p.barcode && p.barcode.toLowerCase() === skuQuery)
    );
  }

  // 2. Fallback to first filtered product if available
  if (!matchedProduct && advanceFilteredProducts.value.length > 0) {
    matchedProduct = advanceFilteredProducts.value[0];
  }

  // 3. If still no local match, search backend API for barcode / SKU
  if (!matchedProduct && (query || skuQuery)) {
    try {
      const searchTerm = query || skuQuery;
      const res = await api.get('/items/advanced-search', {
        params: { search_term: searchTerm, barcode: searchTerm, sku: searchTerm }
      });
      const remoteItems = res.data.items || res.data.data || [];
      if (remoteItems.length > 0) {
        matchedProduct = remoteItems.find(p =>
          (p.barcode && p.barcode.toLowerCase() === searchTerm) ||
          (p.sku && p.sku.toLowerCase() === searchTerm)
        ) || remoteItems[0];
        
        emit('products-fetched', [matchedProduct]);
      }
    } catch (err) {
      console.error('Advanced search barcode fetch error:', err);
    }
  }

  // 4. If product found, add to cart & reset query field for next scan
  if (matchedProduct) {
    addAdvanceProductToInvoice(matchedProduct);
    advanceFilters.value.query = '';
    advanceFilters.value.sku = '';
  } else {
    soundService.playWarning();
    emit('product-selected', { error: 'Not Found', query: query || skuQuery });
  }
};

const handleClickOutside = (event) => {
  const productContainer = document.getElementById('product-search-container');
  if (productContainer && !productContainer.contains(event.target)) {
    isProductDropdownOpen.value = false;
  }
  activeDropdown.value = null;
};

let focusTimer = null;

const focusSearchInput = () => {
  if (searchInputRef.value) {
    try {
      searchInputRef.value.focus();
    } catch (e) {}
  }
};

const handleGlobalKeydown = (e) => {
  if (e.key === 'F2') {
    e.preventDefault();
    focusSearchInput();
  }
};

watch(() => props.products, () => {
  focusSearchInput();
}, { deep: false });

onMounted(() => {
  loadTags();
  loadCategoriesAndBrands();
  document.addEventListener('click', handleClickOutside);
  window.addEventListener('keydown', handleGlobalKeydown);

  // Auto-focus product search input on component mount across all transaction pages
  focusSearchInput();
  let attempts = 0;
  focusTimer = setInterval(() => {
    attempts++;
    const active = document.activeElement;
    const isSearchFocused = active === searchInputRef.value;
    const isOtherInputFocused = active && active !== document.body && active !== document.documentElement && active !== searchInputRef.value && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT');

    if (isSearchFocused || isOtherInputFocused || attempts > 20) {
      if (focusTimer) clearInterval(focusTimer);
      return;
    }

    focusSearchInput();
  }, 100);
});

onUnmounted(() => {
  if (focusTimer) clearInterval(focusTimer);
  document.removeEventListener('click', handleClickOutside);
  window.removeEventListener('keydown', handleGlobalKeydown);
});

defineExpose({
  focusSearchInput,
  searchInputRef
});
</script>
