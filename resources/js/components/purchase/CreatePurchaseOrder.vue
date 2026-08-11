<template>
  <div class="w-full bg-slate-50 dark:bg-zinc-950 min-h-screen">

    <!-- Header bar -->
    <div class="bg-white dark:bg-[#1E1E1E] border-b border-slate-200 dark:border-[#2E2E2E] px-6 py-4 shadow-sm">
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
          <button
            @click="goBack"
            class="text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 font-bold text-xs transition-colors duration-200 flex items-center space-x-1.5 focus:outline-none cursor-pointer border-0 bg-transparent"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back</span>
          </button>
          <span class="text-slate-300 dark:text-slate-600 select-none">|</span>
          <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Create Purchase Order</h1>
        </div>
        <div class="text-right text-xs">
          <div class="text-slate-700 dark:text-slate-300 font-bold">{{ authStore.user?.name }}</div>
          <div class="text-slate-400 dark:text-slate-500">{{ currentTime }} &nbsp;|&nbsp; {{ currentDate }}</div>
        </div>
      </div>
    </div>

    <!-- Main Workspace Layout: Unified Master Card Container -->
    <div class="w-full bg-white dark:bg-[#1E1E1E] flex flex-col md:flex-row min-h-[calc(100vh-66px)] border-t border-slate-200 dark:border-[#2E2E2E]">
      
      <!-- Left Panel: Purchase Order Form (3/4 width) -->
      <div class="w-full md:w-3/4 p-8 flex flex-col relative">

          <!-- Catalog Search & Selection Section -->
          <div class="pb-6 mb-4 space-y-3">
            <h3 class="text-xs font-extrabold uppercase text-slate-400 dark:text-zinc-500 tracking-wider text-left">Catalog Search & Selection</h3>
            
            <div class="flex items-center gap-3 relative w-full">
              <!-- Search items input (takes full width minus gold category icon button) -->
              <div class="relative flex-1" id="product-search-container">
                <input
                  v-model="productSearch"
                  type="text"
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
                    :key="product.id"
                    :ref="el => setProductItemRef(el, idx)"
                    @click="selectProductFromDropdown(product)"
                    @mouseenter="highlightedProductIndex = idx"
                    class="px-4 py-2.5 cursor-pointer flex justify-between items-center text-xs border-b border-slate-100 dark:border-zinc-800/60 last:border-0 text-left transition-colors"
                    :class="{
                      'bg-indigo-50/90 dark:bg-zinc-800/90 text-indigo-900 dark:text-indigo-200 border-l-4 border-l-indigo-600 dark:border-l-indigo-400 font-bold': highlightedProductIndex === idx,
                      'hover:bg-slate-50 dark:hover:bg-zinc-800/80': highlightedProductIndex !== idx
                    }"
                  >
                    <div class="min-w-0 pr-4">
                      <div class="font-bold text-slate-800 dark:text-zinc-200 truncate">{{ product.name }}</div>
                      <div class="text-[10px] text-slate-400 dark:text-zinc-500 font-mono">SKU: {{ product.sku }}</div>
                    </div>
                    <div class="text-right flex-shrink-0">
                      <span class="font-black text-indigo-600 dark:text-indigo-400 text-sm block">{{ currencySymbol }}{{ product.cost_price || product.selling_price }}</span>
                      <span class="text-[10px] text-slate-500 dark:text-zinc-400">{{ getProductStock(product) }} in stock</span>
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
          </div>

          <!-- Line Items Section Card -->
          <div class="border border-slate-200 dark:border-zinc-800 rounded-xl mt-2 overflow-hidden bg-white dark:bg-zinc-900 shadow-sm">
            <!-- 1. Scrollable Line Items Table (Header & Body ONLY) -->
            <div class="overflow-x-auto overflow-y-auto max-h-[380px] relative custom-scrollbar">
              <table class="w-full text-xs text-left border-collapse">
                <thead class="sticky top-0 z-10 shadow-xs">
                  <tr class="bg-slate-50 dark:bg-zinc-900 border-b border-slate-200 dark:border-zinc-800 text-slate-400 dark:text-zinc-400 uppercase font-extrabold tracking-wider">
                    <th class="py-3 px-3 w-5/12 bg-slate-50 dark:bg-zinc-900">Item Details / SKU</th>
                    <th class="py-3 px-2 w-2/12 text-center bg-slate-50 dark:bg-zinc-900">Qty</th>
                    <th class="py-3 px-2 w-2.5/12 text-right bg-slate-50 dark:bg-zinc-900">Purchase Price</th>
                    <th class="py-3 px-2 w-2.5/12 text-right bg-slate-50 dark:bg-zinc-900">Total Cost</th>
                    <th class="py-3 px-1 w-[40px] text-center bg-slate-50 dark:bg-zinc-900"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-zinc-800">
                  <tr v-if="orderItems.length === 0">
                    <td colspan="5" class="py-16 text-center text-slate-400 dark:text-zinc-500 italic">
                      <svg class="mx-auto h-10 w-10 text-slate-300 dark:text-zinc-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                      </svg>
                      <span>No products added. Use search above to select items.</span>
                    </td>
                  </tr>

                  <template v-for="(item, index) in orderItems" :key="index">
                    <!-- Main Row: Name, SKU, Qty, Unit Cost, Total Cost -->
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/20 group align-top border-t border-slate-100 dark:border-zinc-800/60 first:border-0">
                      <!-- Name and SKU -->
                      <td class="pt-3 pb-1 px-3">
                        <div class="font-bold text-slate-800 dark:text-zinc-100 text-sm mb-0.5">{{ item.product.name }}</div>
                        <div class="text-[10px] text-slate-500 dark:text-zinc-400 font-mono">
                          <span class="whitespace-nowrap">SKU: {{ item.product.sku }}</span>
                        </div>
                      </td>

                      <!-- Qty -->
                      <td class="pt-3 pb-1 px-2 text-center">
                        <input
                          v-model.number="item.quantity_ordered"
                          type="number"
                          min="1"
                          class="w-16 px-1.5 py-1 text-center border rounded focus:outline-none focus:ring-1 font-bold text-xs transition-all duration-200"
                          :class="[
                            isItemStockExceeded(item)
                              ? 'border-rose-500 bg-rose-500/10 text-rose-600 dark:text-rose-400 focus:ring-rose-500 ring-1 ring-rose-500/50'
                              : 'border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200 focus:ring-indigo-500'
                          ]"
                          @input="onItemQtyChange(index)"
                        />
                        <div
                          class="text-[9px] font-bold mt-1 tracking-tight"
                          :class="[
                            isItemStockExceeded(item)
                              ? 'text-rose-600 dark:text-rose-400 font-extrabold flex items-center justify-center gap-0.5'
                              : 'text-slate-400 dark:text-zinc-500'
                          ]"
                        >
                          <span v-if="isItemStockExceeded(item)" class="inline-block w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                          Stock: {{ getItemAvailableStock(item) }}
                        </div>
                      </td>

                      <!-- Unit Cost -->
                      <td class="pt-3 pb-1 px-2 text-right">
                        <input
                          v-model.number="item.unit_cost"
                          type="number"
                          step="0.01"
                          min="0"
                          class="w-24 px-1.5 py-1 text-right border border-slate-300 dark:border-zinc-700 rounded focus:outline-none focus:ring-1 focus:ring-indigo-500 font-semibold bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200"
                          @input="updateItemTotal(index)"
                        />
                      </td>

                      <!-- Total Cost -->
                      <td class="pt-3 pb-1 px-2 text-right font-bold text-slate-800 dark:text-zinc-200 text-sm align-middle">
                        {{ currencySymbol }}{{ item.total_cost.toFixed(2) }}
                      </td>

                      <!-- Remove Button -->
                      <td class="pt-3 pb-1 px-1 text-center align-middle">
                        <button
                          @click="removeFromOrder(index)"
                          class="text-slate-400 dark:text-zinc-500 hover:text-rose-600 dark:hover:text-rose-450 p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-all cursor-pointer border-0 bg-transparent"
                        >
                          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                          </svg>
                        </button>
                      </td>
                    </tr>

                    <!-- Full-Width Sub-Row: Expanded Description Box & Inline Warehouse Dropdown -->
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/20 group">
                      <td colspan="5" class="pt-1 pb-3 px-3">
                        <div class="flex flex-row items-center gap-3 w-full">
                          <!-- Description Box (expands horizontally across the whole row space) -->
                          <textarea
                            v-model="item.notes"
                            placeholder="Add line item description / details..."
                            rows="1"
                            class="flex-1 min-w-0 h-[38px] bg-slate-50/50 dark:bg-zinc-900/60 hover:bg-slate-100/80 dark:hover:bg-zinc-800/80 focus:bg-white dark:focus:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-lg px-2.5 py-2 text-slate-600 dark:text-zinc-300 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-[11px] leading-tight resize-y"
                          ></textarea>
                          <!-- Warehouse Allocation Field (Inline Multi-Warehouse Popover Button) -->
                          <div v-if="warehouses.length > 0" class="shrink-0 flex items-center gap-1.5 relative text-left" :id="`item-wh-dropdown-${index}`" @click.stop>
                            <span class="text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider shrink-0">Allocation:</span>
                            
                            <button
                              :id="`item-wh-btn-${index}`"
                              type="button"
                              @click.stop="toggleItemWarehouseDropdown(index, $event)"
                              class="h-[38px] px-2.5 border rounded-lg text-[10px] font-bold bg-white dark:bg-zinc-900 focus:outline-none focus:ring-1 cursor-pointer flex items-center justify-between gap-1.5 min-w-[180px] max-w-[250px] shadow-xs transition-all select-none"
                              :class="!isItemAllocationValid(item) ? 'border-rose-500 text-rose-600 dark:text-rose-400 focus:ring-rose-500 ring-1 ring-rose-500/50' : 'border-slate-300 dark:border-zinc-700 text-slate-700 dark:text-zinc-300 focus:ring-indigo-500 hover:border-slate-400'"
                            >
                              <span class="truncate">{{ getItemAllocationSummary(item) }}</span>
                              <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200" :class="{ 'rotate-180': openWarehouseItemIndex === index }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                              </svg>
                            </button>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>

            <!-- 2. Static Summary Totals & Payment Section (Pinned below table, outside overflow scroll) -->
            <table v-if="orderItems.length > 0" class="w-full text-xs text-left border-collapse border-t border-slate-200 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/40">
              <tfoot class="bg-slate-50 dark:bg-zinc-900/40">
                <!-- 1. Subtotal -->
                <tr>
                  <td colspan="3" class="py-2 px-3 text-right font-semibold text-slate-500 dark:text-zinc-400">Subtotal</td>
                  <td class="py-2 px-2 text-right font-bold text-slate-800 dark:text-zinc-200">{{ currencySymbol }}{{ orderSubtotal.toFixed(2) }}</td>
                  <td class="w-[40px]"></td>
                </tr>

                <!-- 2. Shipping / Additional Fee -->
                <tr>
                  <td colspan="3" class="py-2 px-3 text-right font-semibold text-slate-500 dark:text-zinc-400">Shipping / Additional Fee</td>
                  <td class="py-1.5 px-2 text-right">
                    <input
                      v-model.number="orderForm.shipping_cost"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-24 px-2 py-1 text-right border border-slate-300 dark:border-zinc-700 rounded-lg text-xs font-bold bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                      placeholder="0.00"
                    />
                  </td>
                  <td class="w-[40px]"></td>
                </tr>

                <!-- 3. Auto Applied Required Taxes (With Professional Override Toggle) -->
                <template v-if="autoRequiredTaxesList.length > 0">
                  <tr
                    v-for="reqTax in autoRequiredTaxesList"
                    :key="'req-tax-' + reqTax.id"
                    :class="[
                      'transition-colors duration-200',
                      reqTax.enabled 
                        ? 'bg-emerald-50/50 dark:bg-emerald-950/30 text-emerald-900 dark:text-emerald-200 font-semibold' 
                        : 'bg-slate-50/80 dark:bg-zinc-900/40 text-slate-400 dark:text-zinc-500 font-medium'
                    ]"
                  >
                    <td colspan="3" class="py-2.5 px-3 text-right text-xs">
                      <div class="inline-flex items-center gap-2.5 justify-end">
                        <button
                          type="button"
                          @click="toggleRequiredTax(reqTax.id)"
                          :class="[
                            'relative inline-flex items-center h-4 w-7 shrink-0 cursor-pointer rounded-full p-0.5 transition-all duration-200 ease-in-out focus:outline-none border',
                            reqTax.enabled 
                              ? 'bg-emerald-500 border-emerald-600/30' 
                              : 'bg-slate-300 dark:bg-zinc-700 border-slate-400/30 dark:border-zinc-600/30'
                          ]"
                          role="switch"
                          :aria-checked="reqTax.enabled"
                          :title="reqTax.enabled ? 'Click to exempt/disable this required tax' : 'Click to apply this required tax'"
                        >
                          <span
                            :class="[
                              'pointer-events-none inline-block h-3 w-3 rounded-full bg-white shadow-xs ring-0 transition-transform duration-200 ease-in-out transform',
                              reqTax.enabled ? 'translate-x-3' : 'translate-x-0'
                            ]"
                          />
                        </button>
                        <span :class="{ 'line-through opacity-60': !reqTax.enabled }" class="font-bold text-xs">
                          {{ reqTax.name }} <span class="text-[11px] font-extrabold opacity-80">({{ reqTax.rate }}{{ reqTax.type === 'percentage' ? '%' : '' }})</span>
                        </span>
                        <span
                          :class="[
                            'px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded-md border transition-all',
                            reqTax.enabled 
                              ? 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border-emerald-300/80 dark:border-emerald-800' 
                              : 'bg-slate-200/80 dark:bg-zinc-800 text-slate-500 dark:text-zinc-400 border-slate-300 dark:border-zinc-700'
                          ]"
                        >
                          {{ reqTax.enabled ? 'Applied' : 'Exempted' }}
                        </span>
                      </div>
                    </td>
                    <td class="py-2.5 px-2 text-right text-xs" :class="reqTax.enabled ? 'font-black text-emerald-600 dark:text-emerald-400' : 'font-semibold text-slate-400 dark:text-zinc-500 line-through'">
                      {{ reqTax.enabled ? '+' + currencySymbol + reqTax.amount.toFixed(2) : currencySymbol + '0.00' }}
                    </td>
                    <td class="w-[40px]"></td>
                  </tr>
                </template>

                <!-- 3. Overall Order Tax -->
                <tr>
                  <td colspan="3" class="py-2 px-3 text-right font-semibold text-slate-500 dark:text-zinc-400">Order Tax</td>
                  <td class="py-1.5 px-2 text-right">
                    <div class="flex items-center justify-end space-x-1">
                      <button
                        type="button"
                        @click="orderForm.tax_type = orderForm.tax_type === 'fixed' ? 'percentage' : 'fixed'"
                        class="h-7 px-2 text-[10px] font-black rounded-lg border border-slate-300 dark:border-zinc-700 bg-slate-100 dark:bg-zinc-800 text-indigo-600 dark:text-indigo-400 hover:bg-slate-200 dark:hover:bg-zinc-700 transition-all shrink-0 cursor-pointer"
                        :title="orderForm.tax_type === 'fixed' ? 'Click to switch to Percentage (%)' : 'Click to switch to Flat Amount'"
                      >
                        {{ orderForm.tax_type === 'fixed' ? currencySymbol : '%' }}
                      </button>
                      <input
                        v-model.number="orderForm.tax_amount"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-24 px-2 py-1 text-right border border-slate-300 dark:border-zinc-700 rounded-lg text-xs font-bold bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        :placeholder="orderForm.tax_type === 'fixed' ? '0.00' : '0%'"
                      />
                    </div>
                  </td>
                  <td class="w-[40px]"></td>
                </tr>

                <!-- 4. Discount (manual field) -->
                <tr>
                  <td colspan="3" class="py-2 px-3 text-right font-semibold text-slate-500 dark:text-zinc-400">Discount (Manual)</td>
                  <td class="py-1.5 px-2 text-right">
                    <div class="flex items-center justify-end space-x-1">
                      <button
                        type="button"
                        @click="orderForm.discount_type = orderForm.discount_type === 'fixed' ? 'percentage' : 'fixed'"
                        class="h-7 px-2 text-[10px] font-black rounded-lg border border-slate-300 dark:border-zinc-700 bg-slate-100 dark:bg-zinc-800 text-indigo-600 dark:text-indigo-400 hover:bg-slate-200 dark:hover:bg-zinc-700 transition-all shrink-0 cursor-pointer"
                        :title="orderForm.discount_type === 'fixed' ? 'Click to switch to Percentage (%)' : 'Click to switch to Flat Amount'"
                      >
                        {{ orderForm.discount_type === 'fixed' ? currencySymbol : '%' }}
                      </button>
                      <input
                        v-model.number="orderForm.discount_amount"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-24 px-2 py-1 text-right border border-slate-300 dark:border-zinc-700 rounded-lg text-xs font-bold bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        :placeholder="orderForm.discount_type === 'fixed' ? '0.00' : '0%'"
                      />
                    </div>
                  </td>
                  <td class="w-[40px]"></td>
                </tr>

                <!-- 5. Grand Total -->
                <tr class="border-t border-b border-slate-300 dark:border-zinc-700 bg-indigo-50/40 dark:bg-indigo-950/20 font-black">
                  <td colspan="3" class="py-3 px-3 text-right text-slate-900 dark:text-zinc-100 text-xs uppercase tracking-wider">Grand Total</td>
                  <td class="py-2.5 px-2 text-right text-indigo-600 dark:text-indigo-400 text-base font-black">{{ currencySymbol }}{{ grandTotal.toFixed(2) }}</td>
                  <td class="w-[40px]"></td>
                </tr>

                <!-- 6. Multi-Select Payment Details & Dynamic Pay Amount Inputs -->
                <tr class="bg-slate-50/90 dark:bg-zinc-900/60 border-b border-slate-200 dark:border-zinc-800">
                  <td colspan="5" class="p-4">
                    <div class="space-y-3 text-left h-auto">
                      <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-zinc-800 pb-2">
                        <h4 class="text-xs font-extrabold uppercase text-slate-500 dark:text-zinc-400 tracking-wider">Payment Details</h4>
                        <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 px-2 py-0.5 rounded-full border border-indigo-200 dark:border-indigo-900/60">
                          {{ selectedPaymentMethods.length }} Method(s) Selected
                        </span>
                      </div>

                      <div class="grid grid-cols-1 md:grid-cols-12 gap-x-4 gap-y-3 items-start h-auto">
                        <!-- Left Col: Payment Method Multi-Select Dropdown & Bank Sub-Dropdown -->
                        <div class="md:col-span-4 space-y-3">
                          <label class="block text-slate-500 dark:text-zinc-400 font-bold text-xs">Payment Method(s):</label>
                          
                          <!-- Row 1: Payment Method Selector -->
                          <div class="relative w-full" id="payment-method-dropdown-container">
                            <button
                              type="button"
                              @click.stop="isPaymentDropdownOpen = !isPaymentDropdownOpen"
                              class="w-full px-3 border border-slate-300 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer text-left flex justify-between items-center text-xs font-bold shadow-xs hover:border-slate-400 dark:hover:border-zinc-600 transition-all select-none h-10"
                            >
                              <span class="truncate">{{ getSelectedPaymentMethodsLabel() }}</span>
                              <svg class="h-4 w-4 text-slate-400 shrink-0 transition-transform duration-200" :class="{ 'rotate-180': isPaymentDropdownOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                              </svg>
                            </button>

                            <!-- Payment Methods Dropup List -->
                            <div
                              v-if="isPaymentDropdownOpen"
                              class="absolute bottom-full mb-2 left-0 w-full bg-white dark:bg-zinc-900 shadow-2xl rounded-xl border border-slate-200 dark:border-zinc-700/80 py-1 text-xs overflow-hidden z-[9999]"
                            >
                              <div class="px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-zinc-400 border-b border-slate-100 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-950 flex justify-between items-center select-none">
                                <span>Select Payment Method(s)</span>
                                <span class="text-indigo-600 dark:text-indigo-400 font-black">
                                  {{ selectedPaymentMethods.length }} Selected
                                </span>
                              </div>
                              <div
                                v-for="pm in availablePaymentMethods"
                                :key="pm.id"
                                @click.stop="togglePaymentMethod(pm.id)"
                                class="px-3 py-2.5 cursor-pointer flex items-center justify-between transition-colors border-b border-slate-100 dark:border-zinc-800/60 last:border-0 select-none"
                                :class="selectedPaymentMethods.includes(pm.id) ? 'bg-indigo-50/80 dark:bg-zinc-800 text-indigo-700 dark:text-indigo-400 font-extrabold' : 'bg-white dark:bg-zinc-900 text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800/60'"
                              >
                                <div class="flex items-center gap-2.5 truncate">
                                  <input
                                    type="checkbox"
                                    :checked="selectedPaymentMethods.includes(pm.id)"
                                    class="w-4 h-4 rounded border-slate-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500 cursor-pointer pointer-events-none"
                                  />
                                  <span class="truncate font-semibold">{{ pm.label }}</span>
                                </div>
                                <span v-if="selectedPaymentMethods.includes(pm.id)" class="text-[9px] font-black uppercase tracking-wider text-indigo-700 dark:text-indigo-300 bg-indigo-100/80 dark:bg-zinc-950 px-2 py-0.5 rounded-md border border-indigo-300 dark:border-indigo-500/40 shadow-2xs shrink-0 ml-2">
                                  Active
                                </span>
                              </div>
                            </div>
                          </div>

                          <!-- Row 2: Sub-dropdown for Bank Accounts Selection -->
                          <div v-if="selectedPaymentMethods.includes('card') || selectedPaymentMethods.includes('bank_transfer')" class="relative w-full" id="bank-dropdown-container">
                            <button
                              type="button"
                              @click.stop="isBankDropdownOpen = !isBankDropdownOpen"
                              class="w-full px-3 border border-slate-300 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer text-left flex justify-between items-center text-xs font-bold shadow-xs hover:border-slate-400 dark:hover:border-zinc-600 transition-all select-none h-10 min-w-0"
                              :title="getSelectedBanksLabel()"
                            >
                              <span class="truncate min-w-0 pr-1 flex-1">{{ getSelectedBanksLabel() }}</span>
                              <svg class="h-4 w-4 text-slate-400 shrink-0 transition-transform duration-200 ml-1" :class="{ 'rotate-180': isBankDropdownOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                              </svg>
                            </button>

                            <!-- Bank Accounts Dropup List -->
                            <div
                              v-if="isBankDropdownOpen"
                              class="absolute bottom-full mb-2 left-0 w-full bg-white dark:bg-zinc-900 shadow-2xl rounded-xl border border-slate-200 dark:border-zinc-700/80 py-1 text-xs overflow-hidden z-[9999]"
                            >
                              <div class="px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-zinc-400 border-b border-slate-100 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-950 flex justify-between items-center select-none">
                                <span>Select Bank Account(s)</span>
                                <span class="text-indigo-600 dark:text-indigo-400 font-black">
                                  {{ selectedBankIds.length }} Selected
                                </span>
                              </div>
                              <div v-if="activeBankAccounts.length === 0" class="px-3 py-3 text-slate-400 text-center text-xs">
                                No bank accounts available
                              </div>
                              <div
                                v-for="bank in activeBankAccounts"
                                :key="bank.id"
                                @click.stop="isBankAccountInactive(bank) ? null : toggleBankSelection(bank.id)"
                                class="px-3 py-2.5 flex items-center justify-between transition-colors border-b border-slate-100 dark:border-zinc-800/60 last:border-0 select-none"
                                :class="[
                                  isBankAccountInactive(bank)
                                    ? 'opacity-50 cursor-not-allowed bg-slate-100/60 dark:bg-zinc-800/40 text-slate-400 dark:text-zinc-500'
                                    : (selectedBankIds.includes(bank.id)
                                        ? 'bg-indigo-50/80 dark:bg-zinc-800 text-indigo-700 dark:text-indigo-400 font-extrabold cursor-pointer'
                                        : 'bg-white dark:bg-zinc-900 text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800/60 cursor-pointer')
                                ]"
                              >
                                <div class="flex items-center gap-2.5 truncate min-w-0">
                                  <input
                                    type="checkbox"
                                    :checked="selectedBankIds.includes(bank.id)"
                                    :disabled="isBankAccountInactive(bank)"
                                    class="w-4 h-4 rounded border-slate-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500 cursor-pointer pointer-events-none shrink-0"
                                  />
                                  <div class="truncate min-w-0">
                                    <span class="truncate font-semibold block">
                                      {{ formatBankAccountLabel(bank) }}
                                      <span v-if="isBankAccountInactive(bank)" class="text-rose-500 font-bold ml-1 text-[11px]">(Inactive)</span>
                                    </span>
                                    <span class="text-[10px] text-slate-400 block font-normal truncate">{{ bank.bank_name || bank.account_name }}{{ (bank.masked_account_number || getMaskedAccountNumber(bank.account_number)) ? ' ' + (bank.masked_account_number || getMaskedAccountNumber(bank.account_number)) : '' }}</span>
                                  </div>
                                </div>
                                <span v-if="isBankAccountInactive(bank)" class="text-[9px] font-black uppercase tracking-wider text-slate-500 bg-slate-200 dark:bg-zinc-800 px-2 py-0.5 rounded-md border border-slate-300 dark:border-zinc-700 shrink-0 ml-2">
                                  Inactive
                                </span>
                                <span v-else-if="selectedBankIds.includes(bank.id)" class="text-[9px] font-black uppercase tracking-wider text-indigo-700 dark:text-indigo-300 bg-indigo-100/80 dark:bg-zinc-950 px-2 py-0.5 rounded-md border border-indigo-300 dark:border-indigo-500/40 shadow-2xs shrink-0 ml-2">
                                  Selected
                                </span>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- Middle Col: Dynamic Pay Amount Input Fields -->
                        <div class="md:col-span-5 space-y-3">
                          <label class="block text-slate-500 dark:text-zinc-400 font-bold text-xs">Pay Amount(s):</label>
                          <div class="space-y-3">
                            <!-- Cash Amount Input -->
                            <div v-if="selectedPaymentMethods.includes('cash')" class="space-y-1">
                              <div
                                class="flex items-center justify-between gap-3 h-10 bg-white dark:bg-zinc-900 px-3.5 rounded-xl border shadow-2xs shrink-0 transition-colors"
                                :class="isCashBalanceExceeded ? 'border-rose-500 ring-1 ring-rose-500/30 dark:border-rose-500' : 'border-slate-200 dark:border-zinc-750'"
                              >
                                <label class="text-xs font-bold text-slate-700 dark:text-zinc-300 truncate min-w-0 flex-1 text-left">
                                  Cash
                                </label>
                                <div class="relative w-24 shrink-0">
                                  <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">{{ currencySymbol }}</span>
                                  <input
                                    v-model.number="paymentAmounts.cash"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full pl-6 pr-2 py-1 text-right border border-slate-300 dark:border-zinc-700 rounded-lg text-xs font-black text-emerald-600 dark:text-emerald-400 bg-slate-50/50 dark:bg-zinc-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 h-7"
                                    :class="{ 'border-rose-500 text-rose-600 dark:text-rose-400 focus:ring-rose-500': isCashBalanceExceeded }"
                                  />
                                </div>
                              </div>
                              <!-- Available Balance & Insufficient Error Message -->
                              <div class="text-[11px] font-semibold text-left px-1">
                                <span v-if="cashAvailableBalance !== null" :class="isCashBalanceExceeded ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-slate-500 dark:text-zinc-400'">
                                  Available Balance: {{ currencySymbol }}{{ cashAvailableBalance.toFixed(2) }}
                                </span>
                                <div v-if="isCashBalanceExceeded" class="text-rose-600 dark:text-rose-400 font-extrabold text-[11px] mt-0.5 animate-pulse flex items-center gap-1">
                                  <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                  </svg>
                                  <span>Insufficient balance! Available: {{ currencySymbol }}{{ cashAvailableBalance.toFixed(2) }}, Attempted: {{ currencySymbol }}{{ (paymentAmounts.cash || 0).toFixed(2) }}</span>
                                </div>
                              </div>
                            </div>

                            <!-- Bank Amount Inputs -->
                            <template v-if="selectedPaymentMethods.includes('card') || selectedPaymentMethods.includes('bank_transfer')">
                              <div
                                v-for="bankId in selectedBankIds"
                                :key="bankId"
                                class="space-y-1"
                              >
                                <div
                                  class="flex items-center justify-between gap-3 h-10 bg-white dark:bg-zinc-900 px-3.5 rounded-xl border shadow-2xs shrink-0 transition-colors"
                                  :class="isBankBalanceExceeded(bankId) ? 'border-rose-500 ring-1 ring-rose-500/30 dark:border-rose-500' : 'border-slate-200 dark:border-zinc-750'"
                                >
                                  <label class="text-xs font-bold text-slate-700 dark:text-zinc-300 truncate min-w-0 flex-1 text-left flex items-center gap-1.5" :title="formatBankAccountLabel(allAccounts.find(b => b.id == bankId))">
                                    <span>{{ formatBankAccountLabel(allAccounts.find(b => b.id == bankId)) }}</span>
                                    <span v-if="isBankAccountInactive(bankId)" class="px-1.5 py-0.5 text-[9px] font-bold uppercase rounded bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
                                      (Inactive)
                                    </span>
                                  </label>
                                  <div class="relative w-24 shrink-0">
                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">{{ currencySymbol }}</span>
                                    <input
                                      v-model.number="bankPaymentAmounts[bankId]"
                                      type="number"
                                      step="0.01"
                                      min="0"
                                      :disabled="isBankAccountInactive(bankId)"
                                      :readonly="isBankAccountInactive(bankId)"
                                      :tabindex="isBankAccountInactive(bankId) ? -1 : 0"
                                      class="w-full pl-6 pr-2 py-1 text-right border border-slate-300 dark:border-zinc-700 rounded-lg text-xs font-black text-emerald-600 dark:text-emerald-400 bg-slate-50/50 dark:bg-zinc-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 h-7 disabled:opacity-60 disabled:bg-slate-200/50 dark:disabled:bg-zinc-800/50 disabled:cursor-not-allowed disabled:text-slate-400 select-none"
                                      :class="{ 'border-rose-500 text-rose-600 dark:text-rose-400 focus:ring-rose-500': isBankBalanceExceeded(bankId) }"
                                    />
                                  </div>
                                </div>
                                <!-- Available Balance & Insufficient Error Message -->
                                <div class="text-[11px] font-semibold text-left px-1">
                                  <span :class="isBankBalanceExceeded(bankId) ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-slate-500 dark:text-zinc-400'">
                                    Available Balance: {{ currencySymbol }}{{ getBankAccountBalance(bankId).toFixed(2) }}
                                  </span>
                                  <div v-if="isBankBalanceExceeded(bankId)" class="text-rose-600 dark:text-rose-400 font-extrabold text-[11px] mt-0.5 animate-pulse flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>Insufficient balance! Available: {{ currencySymbol }}{{ getBankAccountBalance(bankId).toFixed(2) }}, Attempted: {{ currencySymbol }}{{ (bankPaymentAmounts[bankId] || 0).toFixed(2) }}</span>
                                  </div>
                                </div>
                              </div>
                            </template>
                          </div>
                        </div>

                        <!-- Right Col: Total Paid Summary Cards -->
                        <div class="md:col-span-3 space-y-3">
                          <label class="block text-slate-500 dark:text-zinc-400 font-bold text-xs">Payment Summary:</label>
                          <div class="space-y-3">
                            <!-- Row 1 Card: Total Paid -->
                            <div class="flex items-center justify-between h-10 px-3.5 rounded-xl bg-slate-100/90 dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 text-xs font-bold text-slate-700 dark:text-zinc-300 shadow-2xs">
                              <span>Total Paid:</span>
                              <span class="text-sm font-extrabold text-indigo-600 dark:text-indigo-400">{{ currencySymbol }}{{ totalPaidAmount.toFixed(2) }}</span>
                            </div>

                            <!-- Row 2 Card: Change / Excess or Remaining Due -->
                            <div v-if="totalPaidAmount >= grandTotal" class="flex items-center justify-between h-10 px-3.5 rounded-xl bg-emerald-50/80 dark:bg-zinc-900 border border-emerald-200/80 dark:border-zinc-800 text-xs font-semibold text-emerald-600 dark:text-emerald-400 shadow-2xs">
                              <span>Change / Excess:</span>
                              <span class="font-extrabold">{{ currencySymbol }}{{ (totalPaidAmount - grandTotal).toFixed(2) }}</span>
                            </div>
                            <div v-else class="flex items-center justify-between h-10 px-3.5 rounded-xl bg-amber-50/80 dark:bg-zinc-900 border border-amber-200/80 dark:border-zinc-800 text-xs font-semibold text-amber-600 dark:text-amber-400 shadow-2xs">
                              <span>Remaining Due:</span>
                              <span class="font-extrabold">{{ currencySymbol }}{{ (grandTotal - totalPaidAmount).toFixed(2) }}</span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- Notes & Footer Layout -->
          <div class="border-t border-slate-200 dark:border-zinc-800 pt-6 mt-8 grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
            <div>
              <label class="block text-xs font-bold uppercase text-slate-400 dark:text-zinc-500 tracking-wider mb-2">Notes to Supplier</label>
              <textarea
                v-model="orderForm.notes"
                rows="3"
                class="w-full px-3 py-2 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500 placeholder-slate-400 dark:placeholder-zinc-600 bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200"
                placeholder="Include custom instructions for delivery or packaging..."
              ></textarea>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase text-slate-400 dark:text-zinc-500 tracking-wider mb-2">Terms & Conditions</label>
              <textarea
                v-model="orderForm.terms_and_conditions"
                rows="3"
                class="w-full px-3 py-2 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500 placeholder-slate-400 dark:placeholder-zinc-600 bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200"
                placeholder="Include payment terms, legal declarations, etc..."
              ></textarea>
            </div>
          </div>
      </div>

      <!-- Right Panel: Sidebar for Document Metadata, Supplier & Actions (1/4 width) -->
      <div class="w-full md:w-1/4 p-6 space-y-6 flex flex-col border-l border-slate-200 dark:border-zinc-800 bg-white dark:bg-[#1E1E1E]">
          
          <!-- Section 0: Purchase Order Metadata Details -->
          <div class="space-y-3 pb-4 border-b border-slate-100 dark:border-zinc-800 text-left">
            <div class="flex items-center justify-between">
              <h2 class="text-xl font-black uppercase tracking-wider transition-all duration-300" :style="{ color: accentColor }">PURCHASE ORDER</h2>
            </div>

            <!-- Metadata Form Fields -->
            <div class="space-y-2.5 text-xs">
              <!-- PO Number -->
              <div>
                <div class="flex items-center justify-between mb-1">
                  <label class="block text-slate-500 dark:text-zinc-400 font-semibold text-xs">PO Number:</label>
                  <label
                    v-if="canEditPoNumber"
                    class="flex items-center gap-1.5 cursor-pointer select-none text-[11px] font-semibold text-slate-600 dark:text-zinc-300"
                  >
                    <span>Manual</span>
                    <div class="relative inline-flex items-center cursor-pointer">
                      <input
                        type="checkbox"
                        v-model="isManualPoNumber"
                        @change="toggleManualPoNumber"
                        class="sr-only peer"
                      />
                      <div class="w-7 h-4 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all dark:after:border-zinc-600 peer-checked:bg-indigo-600"></div>
                    </div>
                  </label>
                </div>
                <input
                  v-model="orderForm.po_number"
                  type="text"
                  :disabled="!isManualPoNumber"
                  :readonly="!isManualPoNumber"
                  placeholder="Auto-generating..."
                  class="w-full px-3 py-1.5 border border-slate-300 dark:border-zinc-700 rounded-lg font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs transition-colors"
                  :class="!isManualPoNumber ? 'bg-slate-100 dark:bg-zinc-800/60 text-slate-500 dark:text-zinc-400 cursor-not-allowed select-none' : 'bg-white dark:bg-zinc-900 text-slate-700 dark:text-zinc-200'"
                />
              </div>

              <!-- BILL TO / SUPPLIER DETAILS SECTION (Moved under Order Number) -->
              <div class="space-y-2 pt-1 pb-1 border-t border-b border-slate-100 dark:border-zinc-800/60">
                <div class="flex items-center justify-between">
                  <h3 class="text-[11px] font-extrabold uppercase text-slate-500 dark:text-zinc-400 tracking-wider">Bill To</h3>
                </div>

                <!-- Supplier Search & Selected Card -->
                <div class="space-y-2">
                  <!-- Attached Supplier Search & Add Supplier Input Group -->
                  <div class="relative w-full" id="supplier-search-container">
                    <div class="flex items-center w-full p-0.5 rounded-xl border border-slate-300/80 dark:border-zinc-700/80 focus-within:ring-2 focus-within:ring-emerald-500/20 focus-within:border-emerald-500 bg-slate-50/50 dark:bg-zinc-900/90 shadow-sm transition-all duration-200 hover:border-slate-300 dark:hover:border-zinc-700">
                      <div class="pl-2.5 pr-1 text-slate-400 dark:text-zinc-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                      </div>
                      <input
                        v-model="supplierSearch"
                        type="text"
                        placeholder="Search supplier name or phone..."
                        class="flex-1 min-w-0 pl-1.5 pr-2 py-1.5 text-xs border-0 focus:outline-none focus:ring-0 bg-transparent text-slate-800 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 font-medium"
                        @input="debouncedSupplierSearch"
                        @focus="searchSuppliers(supplierSearch)"
                      />
                      <button
                        type="button"
                        @click="showSupplierModal = true"
                        title="Add New Supplier"
                        class="h-7 px-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 active:scale-95 text-white rounded-lg text-xs font-bold shadow-sm transition-all duration-200 flex items-center justify-center space-x-1 shrink-0 cursor-pointer border-0"
                      >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                      </button>
                    </div>
                    
                    <!-- Supplier Search Dropdown Results -->
                    <div v-if="supplierSearchResults.length > 0" class="absolute z-50 mt-1.5 w-full bg-white dark:bg-zinc-900 shadow-2xl max-h-[220px] rounded-xl border border-slate-200 dark:border-zinc-800 py-1 text-xs overflow-y-auto custom-scrollbar">
                      <div
                        v-for="supplier in supplierSearchResults"
                        :key="supplier.id"
                        @click="selectSupplier(supplier)"
                        class="cursor-pointer py-2 px-3 hover:bg-emerald-50/60 dark:hover:bg-zinc-800/80 flex justify-between items-center transition-colors border-b border-slate-50 dark:border-zinc-850 last:border-0"
                      >
                        <div class="flex items-center space-x-2.5 min-w-0">
                          <div class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 font-bold text-[10px] flex items-center justify-center shrink-0">
                            {{ supplier.name.charAt(0).toUpperCase() }}
                          </div>
                          <div class="min-w-0">
                            <span class="font-bold text-slate-800 dark:text-zinc-200 truncate block">{{ supplier.name }}</span>
                            <p class="text-[10px] text-slate-500 dark:text-zinc-400 truncate">{{ supplier.phone || supplier.email }}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Selected Supplier Details Card -->
                  <div v-if="selectedSupplier" class="p-3 bg-emerald-50/40 dark:bg-emerald-950/20 rounded-xl border border-emerald-200/80 dark:border-emerald-900/40 text-xs space-y-1 relative w-full text-left transition-all">
                    <button @click="clearSupplier" class="absolute top-2.5 right-2.5 text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-350 font-bold text-[10px] flex items-center gap-0.5 transition-colors border-0 bg-transparent cursor-pointer">
                      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                      Remove
                    </button>
                    <div class="flex items-center space-x-2">
                      <div class="w-7 h-7 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-sm">
                        {{ selectedSupplier.name.charAt(0).toUpperCase() }}
                      </div>
                      <div class="min-w-0">
                        <p class="font-bold text-slate-800 dark:text-zinc-100 text-sm truncate">
                          <span class="font-medium text-slate-800 dark:text-zinc-100">{{ selectedSupplier.name }}</span> <span v-if="selectedSupplier.phone" class="text-slate-500 text-xs ml-1">({{ selectedSupplier.phone }})</span>
                        </p>
                      </div>
                    </div>
                  </div>
                  <div v-else class="text-slate-400 dark:text-zinc-500 text-[11px] italic text-left">
                    No supplier selected. Search above to assign.
                  </div>
                </div>
              </div>

              <!-- Order Date & Expected Delivery Date -->
              <div class="grid grid-cols-2 gap-2">
                <div>
                  <label class="block text-slate-500 dark:text-zinc-400 font-semibold mb-1">Order Date:</label>
                  <input
                    v-model="orderForm.order_date"
                    type="date"
                    class="w-full px-2 py-1.5 border border-slate-300 dark:border-zinc-700 rounded-lg text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs"
                  />
                </div>
                <div>
                  <label class="block text-slate-500 dark:text-zinc-400 font-semibold mb-1">Expected Delivery:</label>
                  <input
                    v-model="orderForm.expected_delivery_date"
                    type="date"
                    class="w-full px-2 py-1.5 border border-slate-300 dark:border-zinc-700 rounded-lg text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs"
                  />
                </div>
              </div>

              <!-- Global Floating Multi-Select Warehouse Field -->
              <div class="space-y-1 relative pt-2 text-left" @click.stop id="global-warehouse-dropdown-container">
                <label class="block text-slate-500 dark:text-zinc-400 font-semibold text-xs mb-1">Destination Warehouse(s) *</label>
                <div class="relative">
                  <button
                    type="button"
                    @click="isGlobalWarehouseDropdownOpen = !isGlobalWarehouseDropdownOpen"
                    class="w-full px-3 py-1.5 border border-slate-300 dark:border-zinc-700 rounded-lg text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-900 focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-100 text-xs font-medium cursor-pointer flex justify-between items-center h-[34px] shadow-xs hover:border-slate-400 transition-all select-none"
                  >
                    <span class="truncate pr-2 font-semibold" :class="{ 'text-slate-400 dark:text-zinc-500': selectedGlobalWarehouseIds.length === 0 }">
                      {{ globalWarehouseSummaryLabel }}
                    </span>
                    <span v-if="selectedGlobalWarehouseIds.length > 0" class="text-[10px] font-bold bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 px-1.5 py-0.5 rounded-full shrink-0">
                      {{ selectedGlobalWarehouseIds.length }}
                    </span>
                    <svg class="h-3.5 w-3.5 text-slate-400 shrink-0 ml-1 transition-transform" :class="{ 'rotate-180': isGlobalWarehouseDropdownOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>

                  <div
                    v-if="isGlobalWarehouseDropdownOpen"
                    class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-2xl py-1 max-h-60 overflow-y-auto custom-scrollbar"
                  >
                    <div class="p-2 border-b border-slate-100 dark:border-zinc-800 shrink-0">
                      <input
                        v-model="globalWarehouseSearch"
                        type="text"
                        placeholder="Search warehouse..."
                        class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-100"
                      />
                    </div>
                    <div class="overflow-y-auto max-h-44 custom-scrollbar">
                      <label
                        v-for="w in searchableGlobalWarehouses"
                        :key="w.id"
                        class="px-3 py-2 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center space-x-2.5 cursor-pointer text-xs select-none border-b border-slate-50 dark:border-zinc-800/40"
                      >
                        <input
                          type="checkbox"
                          :value="w.id"
                          v-model="selectedGlobalWarehouseIds"
                          @change="onGlobalWarehouseChange"
                          class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer w-4 h-4"
                        />
                        <span class="font-medium text-slate-800 dark:text-zinc-200">{{ w.name }}</span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Section 3: Summary Totals & Calculations -->
          <div class="space-y-4">
            <h3 class="text-xs font-extrabold uppercase text-slate-500 dark:text-zinc-400 tracking-wider border-b border-slate-100 dark:border-zinc-800 pb-2 text-left">Summary & Details</h3>

            <div class="bg-slate-50 dark:bg-zinc-900/60 rounded-2xl p-4 border border-slate-200/80 dark:border-zinc-800/80 text-xs space-y-2.5">
              <div class="flex justify-between font-medium text-slate-600 dark:text-zinc-400">
                <span>Subtotal:</span>
                <span class="font-bold text-slate-800 dark:text-zinc-200">{{ currencySymbol }}{{ orderSubtotal.toFixed(2) }}</span>
              </div>
              <div class="flex justify-between font-medium text-slate-600 dark:text-zinc-400">
                <span>Total Amount:</span>
                <span class="font-bold text-slate-800 dark:text-zinc-200">{{ currencySymbol }}{{ orderSubtotal.toFixed(2) }}</span>
              </div>
              <template v-if="autoRequiredTaxesList.length > 0">
                <div
                  v-for="reqTax in autoRequiredTaxesList"
                  :key="'sidebar-req-tax-' + reqTax.id"
                  class="flex justify-between items-center text-xs font-bold transition-all py-0.5"
                  :class="reqTax.enabled ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-zinc-500'"
                >
                  <div class="flex items-center gap-2">
                    <button
                      type="button"
                      @click="toggleRequiredTax(reqTax.id)"
                      :class="[
                        'relative inline-flex items-center h-4 w-7 shrink-0 cursor-pointer rounded-full p-0.5 transition-all duration-200 ease-in-out focus:outline-none border',
                        reqTax.enabled 
                          ? 'bg-emerald-500 border-emerald-600/30' 
                          : 'bg-slate-300 dark:bg-zinc-700 border-slate-400/30 dark:border-zinc-600/30'
                      ]"
                      role="switch"
                      :aria-checked="reqTax.enabled"
                      :title="reqTax.enabled ? 'Click to exempt tax' : 'Click to enable tax'"
                    >
                      <span
                        :class="[
                          'pointer-events-none inline-block h-3 w-3 rounded-full bg-white shadow-xs ring-0 transition-transform duration-200 ease-in-out transform',
                          reqTax.enabled ? 'translate-x-3' : 'translate-x-0'
                        ]"
                      />
                    </button>
                    <span :class="{ 'line-through opacity-60': !reqTax.enabled }">
                      {{ reqTax.name }} ({{ reqTax.rate }}{{ reqTax.type === 'percentage' ? '%' : '' }}):
                    </span>
                  </div>
                  <span :class="reqTax.enabled ? 'font-extrabold' : 'font-semibold line-through opacity-60'">
                    {{ reqTax.enabled ? '+' + currencySymbol + reqTax.amount.toFixed(2) : currencySymbol + '0.00' }}
                  </span>
                </div>
              </template>
              <div class="flex justify-between font-medium text-slate-600 dark:text-zinc-400">
                <span>Taxes (Manual):</span>
                <span class="font-bold text-slate-800 dark:text-zinc-200">
                  +{{ currencySymbol }}{{ calculatedManualTax.toFixed(2) }}
                  <span v-if="orderForm.tax_type === 'percentage'" class="text-[10px] text-indigo-500 dark:text-indigo-400 font-extrabold">({{ orderForm.tax_amount || 0 }}%)</span>
                </span>
              </div>
              <div class="flex justify-between font-medium text-slate-600 dark:text-zinc-400">
                <span>Discount (Manual):</span>
                <span class="font-bold text-slate-800 dark:text-zinc-200">
                  -{{ currencySymbol }}{{ calculatedManualDiscount.toFixed(2) }}
                  <span v-if="orderForm.discount_type === 'percentage'" class="text-[10px] text-indigo-500 dark:text-indigo-400 font-extrabold">({{ orderForm.discount_amount || 0 }}%)</span>
                </span>
              </div>
              <div class="flex justify-between items-center text-sm font-extrabold text-slate-900 dark:text-zinc-100 border-t border-slate-200 dark:border-zinc-800 pt-2.5 mt-1">
                <span>Grand Total:</span>
                <span class="text-lg transition-all duration-300 font-black" :style="{ color: accentColor }">{{ currencySymbol }}{{ grandTotal.toFixed(2) }}</span>
              </div>
            </div>

            <!-- Advance Balance Option -->
            <div v-if="selectedSupplier && parseFloat(selectedSupplier.advance_balance || 0) > 0" class="bg-amber-50 dark:bg-amber-950/20 rounded-xl px-3 py-2.5 border border-amber-200 dark:border-amber-900/60 text-xs">
              <label class="flex items-center justify-between cursor-pointer">
                <div class="flex items-center space-x-2">
                  <input
                    type="checkbox"
                    v-model="useAdvanceBalance"
                    class="rounded border-amber-400 text-amber-600 focus:ring-amber-500 w-3.5 h-3.5 cursor-pointer"
                  />
                  <span class="font-bold text-amber-800 dark:text-amber-300">Use Advance Balance</span>
                </div>
                <span class="font-extrabold text-amber-700 dark:text-amber-400">{{ currencySymbol }}{{ parseFloat(selectedSupplier.advance_balance || 0).toFixed(2) }}</span>
              </label>
              <div v-if="useAdvanceBalance" class="mt-1.5 text-[10px] text-amber-600 dark:text-amber-400 font-medium">
                Applying {{ currencySymbol }}{{ advanceToApply.toFixed(2) }} from advance → New effective due: {{ currencySymbol }}{{ effectiveDueAmount.toFixed(2) }}
              </div>
            </div>


          </div>

        <!-- Sidebar Sticky Footer Actions -->
        <div class="p-5 border-t border-slate-200 dark:border-zinc-800 bg-slate-50/80 dark:bg-zinc-900/40 mt-auto">
          <div class="space-y-3">
            <!-- Row 1: Primary Action (Save Purchase Order) -->
            <button
              @click="saveOrder"
              :disabled="orderItems.length === 0 || saving || !selectedSupplier || hasInsufficientPaymentBalance"
              class="w-full h-10 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm shadow-sm transition-all flex items-center justify-center space-x-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed border-0"
              :title="hasInsufficientPaymentBalance ? 'Cannot save: Insufficient balance in selected payment account(s)' : ''"
            >
              <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>{{ saving ? 'Saving PO...' : 'Save Purchase Order' }}</span>
            </button>

            <!-- Row 2: Secondary Actions (Save Draft, Clear All) -->
            <div class="grid grid-cols-1 gap-3">
              <button
                @click="clearOrder"
                :disabled="orderItems.length === 0"
                class="w-full h-10 border border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800 text-rose-600 dark:text-rose-400 rounded-lg font-semibold text-sm transition-all flex items-center justify-center cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed bg-transparent"
              >
                <span>Clear All</span>
              </button>
            </div>
          </div>
        </div>

      </div>

    </div>

    <!-- Quick Supplier Creation Modal -->
    <div v-if="showSupplierModal" @click.self.prevent class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-hidden h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200" style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);">
      <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-2xl shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 p-6 transition-all duration-300 z-10 max-h-[85vh] overflow-y-auto custom-scrollbar my-auto text-left">
        
        <!-- Sleek Close Icon Button -->
        <button
          type="button"
          @click="closeSupplierModal"
          class="absolute top-4 right-4 text-slate-400 dark:text-zinc-500 hover:text-slate-600 dark:hover:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 p-1.5 rounded-lg transition-all bg-transparent border-0 cursor-pointer"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <div class="mb-4 pb-2 border-b border-slate-100 dark:border-zinc-800">
          <h3 class="text-xs font-bold text-slate-800 dark:text-zinc-100 uppercase tracking-wider">Add New Supplier</h3>
        </div>

        <!-- Tab Navigation -->
        <div class="flex border-b border-slate-200 dark:border-zinc-800 mb-6 gap-1 text-[11px]">
          <button
            type="button"
            class="flex items-center gap-1.5 px-3 py-1.5 font-bold rounded-t-lg transition-all focus:outline-none border-b-2 cursor-pointer"
            :class="activeSupplierTab === 'basic' ? 'text-indigo-600 border-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/20' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300 border-transparent bg-transparent'"
            @click="activeSupplierTab = 'basic'"
          >
            Basic Info
          </button>
          <button
            type="button"
            class="flex items-center gap-1.5 px-3 py-1.5 font-bold rounded-t-lg transition-all focus:outline-none border-b-2 cursor-pointer relative"
            :class="activeSupplierTab === 'contact' ? 'text-indigo-600 border-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/20' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300 border-transparent bg-transparent'"
            @click="activeSupplierTab = 'contact'"
          >
            <span>Contact</span>
            <span v-if="supplierErrors.phone" class="w-2 h-2 rounded-full bg-red-500 absolute top-1 right-1"></span>
          </button>
          <button
            type="button"
            class="flex items-center gap-1.5 px-3 py-1.5 font-bold rounded-t-lg transition-all focus:outline-none border-b-2 cursor-pointer"
            :class="activeSupplierTab === 'address' ? 'text-indigo-600 border-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/20' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300 border-transparent bg-transparent'"
            @click="activeSupplierTab = 'address'"
          >
            Address
          </button>
          <button
            type="button"
            class="flex items-center gap-1.5 px-3 py-1.5 font-bold rounded-t-lg transition-all focus:outline-none border-b-2 cursor-pointer"
            :class="activeSupplierTab === 'business' ? 'text-indigo-600 border-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/20' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300 border-transparent bg-transparent'"
            @click="activeSupplierTab = 'business'"
          >
            Business Info
          </button>
        </div>

        <form @submit.prevent="createSupplier" class="space-y-4">
          <!-- Tab 1: Basic Info -->
          <div v-show="activeSupplierTab === 'basic'" class="space-y-4 animate-in fade-in duration-200">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Supplier Name *</label>
                <input
                  v-model="newSupplier.name"
                  @input="supplierErrors.name = ''"
                  type="text"
                  placeholder="e.g. Acme Corporation"
                  :class="supplierErrors.name ? 'border-red-500 focus:ring-red-200 dark:border-red-500' : 'border-slate-200 dark:border-zinc-700'"
                  class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-955 transition-all"
                />
                <span v-if="supplierErrors.name" class="text-xs text-red-500 mt-1 block font-medium">{{ supplierErrors.name }}</span>
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Company Name</label>
                <input
                  v-model="newSupplier.company_name"
                  type="text"
                  placeholder="e.g. Acme Corp Industries"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-955 transition-all"
                />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Tax Number</label>
                <input
                  v-model="newSupplier.tax_number"
                  type="text"
                  placeholder="e.g. TAX-12345"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-955 transition-all"
                />
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Status</label>
                <select
                  v-model="newSupplier.is_active"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 bg-white dark:bg-zinc-955 transition-all"
                >
                  <option :value="true">Active</option>
                  <option :value="false">Inactive</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Notes</label>
              <textarea
                v-model="newSupplier.notes"
                rows="3"
                placeholder="Include custom terms, wired instruction details, or reference codes..."
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-955 transition-all"
              ></textarea>
            </div>
          </div>

          <!-- Tab 2: Contact -->
          <div v-show="activeSupplierTab === 'contact'" class="space-y-4 animate-in fade-in duration-200">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Email</label>
                <input
                  v-model="newSupplier.email"
                  type="email"
                  placeholder="e.g. sales@acme.com"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-955 transition-all"
                />
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Phone *</label>
                <input
                  v-model="newSupplier.phone"
                  @input="supplierErrors.phone = ''"
                  type="text"
                  placeholder="e.g. +1 555 1234"
                  :class="supplierErrors.phone ? 'border-red-500 focus:ring-red-200 dark:border-red-500' : 'border-slate-200 dark:border-zinc-700'"
                  class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-955 transition-all"
                />
                <span v-if="supplierErrors.phone" class="text-xs text-red-500 mt-1 block font-medium">{{ supplierErrors.phone }}</span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Mobile</label>
                <input
                  v-model="newSupplier.mobile"
                  type="text"
                  placeholder="e.g. +1 555 5678"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-955 transition-all"
                />
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Website</label>
                <input
                  v-model="newSupplier.website"
                  type="url"
                  placeholder="e.g. https://www.acme.com"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-955 transition-all"
                />
              </div>
            </div>
          </div>

          <!-- Tab 3: Address -->
          <div v-show="activeSupplierTab === 'address'" class="space-y-4 animate-in fade-in duration-200">
            <div>
              <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Street Address</label>
              <textarea
                v-model="newSupplier.address"
                rows="2"
                placeholder="Street address, suite, apartment..."
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-955 transition-all"
              ></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">City</label>
                <input
                  v-model="newSupplier.city"
                  type="text"
                  placeholder="e.g. New York"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-955 transition-all"
                />
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">State</label>
                <input
                  v-model="newSupplier.state"
                  type="text"
                  placeholder="e.g. NY"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-955 transition-all"
                />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Postal Code</label>
                <input
                  v-model="newSupplier.postal_code"
                  type="text"
                  placeholder="e.g. 10001"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-955 transition-all"
                />
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Country</label>
                <input
                  v-model="newSupplier.country"
                  type="text"
                  placeholder="e.g. United States"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-955 transition-all"
                />
              </div>
            </div>
          </div>

          <!-- Tab 4: Business Info -->
          <div v-show="activeSupplierTab === 'business'" class="space-y-4 animate-in fade-in duration-200">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Credit Limit ({{ currencySymbol }})</label>
                <input
                  v-model="newSupplier.credit_limit"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0.00"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-955 transition-all"
                />
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Payment Terms (days)</label>
                <input
                  v-model="newSupplier.payment_terms_days"
                  type="number"
                  min="0"
                  placeholder="30"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-955 transition-all"
                />
              </div>
            </div>
          </div>

          <div class="flex justify-end space-x-3 pt-3.5 border-t border-slate-100 dark:border-zinc-800 mt-2">
            <button
              type="button"
              @click="closeSupplierModal"
              class="px-4 h-9 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 rounded-lg text-xs font-semibold transition-all cursor-pointer border-0"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="creatingSupplier"
              class="px-4 h-9 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed border-0"
            >
              {{ creatingSupplier ? 'Creating...' : 'Add Supplier' }}
            </button>
          </div>
        </form>
      </div>
    </div>

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
                v-model="advanceFilters.query"
                type="text"
                placeholder="Search by Name or Description"
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
                    class="flex-1 px-3 py-2 bg-slate-50 dark:bg-[#12161b] border border-slate-300 dark:border-[#2E2E2E] focus:border-sky-500 dark:focus:border-sky-400 focus:ring-0 focus-visible:ring-0 focus:outline-none shadow-none rounded-xl text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-zinc-500"
                  />
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

                <!-- Search by Categories -->
                <div class="flex items-center gap-3">
                  <label class="w-32 shrink-0 text-slate-500 dark:text-zinc-400 font-medium">Search by Categories</label>
                  <div class="flex-1 relative">
                    <div
                      @click="focusCategoryInput"
                      class="min-h-[38px] px-2.5 py-1 bg-slate-50 dark:bg-[#12161b] border border-slate-300 dark:border-[#2E2E2E] rounded-xl flex items-center justify-between cursor-text flex-wrap gap-1 focus-within:border-sky-500 dark:focus-within:border-sky-400 focus-within:ring-0 focus-within:outline-none transition-all"
                    >
                      <div class="flex flex-wrap items-center gap-1 flex-1 min-w-0">
                        <span
                          v-for="cId in advanceFilters.categories"
                          :key="cId"
                          class="bg-slate-200 dark:bg-zinc-800 text-slate-800 dark:text-zinc-200 text-[10px] font-semibold px-2 py-0.5 rounded-md border border-slate-300 dark:border-[#2E2E2E] flex items-center gap-1 shrink-0"
                        >
                          {{ getCategoryNameById(cId) }}
                          <span @click.stop="removeAdvanceCategory(cId)" class="hover:text-rose-500 dark:hover:text-rose-400 cursor-pointer font-bold">×</span>
                        </span>

                        <input
                          ref="categoryInputRef"
                          v-model="categorySearchQuery"
                          type="text"
                          placeholder="Search by Categories"
                          @focus="openCategoryDropdown"
                          @keydown.down.prevent="navigateCategoryOptions(1)"
                          @keydown.up.prevent="navigateCategoryOptions(-1)"
                          @keydown.enter.prevent="selectHighlightedCategory"
                          @keydown.esc.prevent="isCategorySelectModalOpen = false"
                          @keydown.delete="handleCategoryDeleteKey"
                          class="flex-1 min-w-[100px] bg-transparent border-0 outline-none focus:outline-none focus:ring-0 focus-visible:ring-0 focus:border-transparent ring-0 shadow-none text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-zinc-500 py-0.5"
                          style="background: transparent !important; border: none !important; outline: none !important; box-shadow: none !important;"
                        />
                      </div>
                      <svg class="w-3.5 h-3.5 text-slate-400 dark:text-zinc-400 shrink-0 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </div>
                    <!-- Category Options Menu -->
                    <div v-show="isCategorySelectModalOpen" class="absolute left-0 right-0 mt-1 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-xl shadow-2xl z-50 max-h-48 overflow-y-auto p-1 text-xs custom-scrollbar">
                      <div v-if="filteredAvailableCategories.length === 0" class="px-3 py-2 text-slate-400 dark:text-zinc-500 text-xs italic text-center">
                        No categories found
                      </div>
                      <div
                        v-for="(cat, idx) in filteredAvailableCategories"
                        :key="cat.id"
                        @click="toggleAdvanceCategory(cat.id)"
                        @mouseenter="categoryHighlightedIndex = idx"
                        class="px-2.5 py-1.5 rounded-lg cursor-pointer flex items-center justify-between transition-colors"
                        :class="[
                          advanceFilters.categories.includes(cat.id) ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-700 dark:text-zinc-200',
                          categoryHighlightedIndex === idx ? 'bg-slate-100 dark:bg-zinc-800' : 'hover:bg-slate-100 dark:hover:bg-zinc-800/60'
                        ]"
                      >
                        <span>{{ cat.name }}</span>
                        <span v-if="advanceFilters.categories.includes(cat.id)" class="text-indigo-600 dark:text-indigo-400 font-bold">✓</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Search by Price -->
                <div class="flex items-center gap-3 md:col-span-2">
                  <label class="w-32 shrink-0 text-slate-500 dark:text-zinc-400 font-medium">Search by Price</label>
                  <div class="flex-1 flex items-center gap-3">
                    <span class="text-slate-500 dark:text-zinc-400 font-medium">min</span>
                    <div class="relative w-32">
                      <span class="absolute inset-y-0 left-2.5 flex items-center text-slate-400 dark:text-zinc-500 text-xs">{{ currencySymbol }}</span>
                      <input
                        v-model="advanceFilters.minPrice"
                        type="number"
                        placeholder="0"
                        class="w-full pl-6 pr-2 py-1.5 bg-slate-50 dark:bg-[#12161b] border border-slate-300 dark:border-[#2E2E2E] rounded-xl text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none focus:border-sky-500 dark:focus:border-sky-400 focus:ring-0 focus-visible:ring-0 shadow-none"
                      />
                    </div>
                    <span class="text-slate-500 dark:text-zinc-400 font-medium">- max</span>
                    <div class="relative w-32">
                      <span class="absolute inset-y-0 left-2.5 flex items-center text-slate-400 dark:text-zinc-500 text-xs">{{ currencySymbol }}</span>
                      <input
                        v-model="advanceFilters.maxPrice"
                        type="number"
                        placeholder="9999"
                        class="w-full pl-6 pr-2 py-1.5 bg-slate-50 dark:bg-[#12161b] border border-slate-300 dark:border-[#2E2E2E] rounded-xl text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none focus:border-sky-500 dark:focus:border-sky-400 focus:ring-0 focus-visible:ring-0 shadow-none"
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
                      <th class="py-2.5 px-3">SKU</th>
                      <th class="py-2.5 px-3">Item Details / Description</th>
                      <th class="py-2.5 px-3">Category</th>
                      <th class="py-2.5 px-3">Tags</th>
                      <th class="py-2.5 px-3 text-right">Cost / Price</th>
                      <th class="py-2.5 px-3 text-center">Action</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-200 dark:divide-[#2E2E2E] text-slate-800 dark:text-slate-200">
                    <tr v-if="!hasActiveAdvanceFilters">
                      <td colspan="6" class="py-12 text-center text-slate-400 dark:text-zinc-500 italic">
                        <svg class="mx-auto h-7 w-7 text-slate-400 dark:text-zinc-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Start typing in search box or select a filter criteria above to search items...</span>
                      </td>
                    </tr>
                    <tr v-else-if="advanceFilteredProducts.length === 0">
                      <td colspan="6" class="py-10 text-center text-slate-400 dark:text-zinc-500 italic">
                        No products match the selected advance search criteria.
                      </td>
                    </tr>
                    <tr
                      v-for="product in advanceFilteredProducts.slice(0, 100)"
                      :key="product.id"
                      class="hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                    >
                      <td class="py-2.5 px-3 font-mono text-[11px] text-slate-500 dark:text-zinc-400">{{ product.sku }}</td>
                      <td class="py-2.5 px-3 font-bold text-slate-900 dark:text-slate-100">{{ product.name }}</td>
                      <td class="py-2.5 px-3 text-slate-600 dark:text-zinc-300">{{ getCategoryNameById(product.category_id) }}</td>
                      <td class="py-2.5 px-3 text-slate-500 dark:text-zinc-400">
                        <span v-if="product.tags && product.tags.length">{{ Array.isArray(product.tags) ? product.tags.join(', ') : product.tags }}</span>
                        <span v-else class="text-slate-400 dark:text-zinc-600">—</span>
                      </td>
                      <td class="py-2.5 px-3 text-right font-extrabold text-emerald-600 dark:text-emerald-400">{{ currencySymbol }}{{ product.cost_price || product.selling_price || product.price }}</td>
                      <td class="py-2.5 px-3 text-center">
                        <button
                          type="button"
                          @click="addAdvanceProductToOrder(product)"
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
                <span class="text-slate-400 dark:text-zinc-500">Click "+ Add" to append items directly to purchase order</span>
              </div>
              <div v-else class="px-4 py-2 bg-slate-50 dark:bg-[#252525] border-t border-slate-200 dark:border-[#2E2E2E] text-[10px] text-slate-400 dark:text-zinc-500 font-semibold text-center">
                Enter search query or select any filter above to view items
              </div>
            </div>

          </div>
        </div>
      </div>
    </transition>

    <!-- Success/Error Notifications -->
    <div v-if="notifications.length > 0" class="fixed top-20 right-4 z-50 space-y-2 max-w-sm w-full">
      <div
        v-for="notification in notifications"
        :key="notification.id"
        class="bg-[#0f172a] text-slate-50 border border-white/5 px-5 py-4 rounded-2xl shadow-2xl flex items-center justify-between gap-3 text-xs font-semibold"
      >
        <div class="flex items-center gap-3 flex-1 min-w-0">
          <div class="flex-shrink-0">
            <svg v-if="notification.type === 'success'" class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg v-else class="w-5 h-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <span class="break-words leading-relaxed select-none text-white dark:text-white" style="color: #ffffff !important;">{{ notification.message }}</span>
        </div>
        <button @click="removeNotification(notification.id)" class="flex-shrink-0 p-1 rounded-md text-slate-400 hover:text-white hover:bg-white/10 transition-all focus:outline-none cursor-pointer bg-transparent border-0">
          <span class="sr-only">Close</span>
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
    <!-- Teleported Floating Line Item Warehouse Allocation Popover (High Z-Index, Floating Above Side Panel) -->
    <teleport to="body">
      <div
        id="teleport-wh-popover"
        v-if="openWarehouseItemIndex !== null && orderItems[openWarehouseItemIndex]"
        @click.stop
        :style="{ top: warehousePopPos.top, left: warehousePopPos.left }"
        class="fixed z-[99999] w-72 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-2xl p-3 animate-fade-in text-left backdrop-blur-md"
      >
        <div class="flex justify-between items-center pb-2 mb-2 border-b border-slate-100 dark:border-zinc-800">
          <div>
            <h4 class="text-xs font-bold text-slate-800 dark:text-zinc-100">Warehouse Allocation</h4>
            <p class="text-[10px] text-slate-400">Total Item Qty: {{ orderItems[openWarehouseItemIndex].quantity_ordered }}</p>
          </div>
          <button type="button" @click="openWarehouseItemIndex = null" class="text-slate-400 hover:text-slate-600 text-xs font-bold border-0 bg-transparent cursor-pointer">✕</button>
        </div>

        <div class="space-y-2 max-h-48 overflow-y-auto custom-scrollbar pr-1">
          <div
            v-for="alloc in orderItems[openWarehouseItemIndex].allocations"
            :key="alloc.warehouse_id"
            class="flex items-center justify-between gap-2"
          >
            <span class="text-xs font-medium text-slate-700 dark:text-zinc-300 truncate min-w-0">
              {{ warehouses.find(w => w.id == alloc.warehouse_id)?.name || 'Warehouse' }}
            </span>
            <input
              v-model.number="alloc.quantity"
              type="number"
              min="0"
              :max="orderItems[openWarehouseItemIndex].quantity_ordered"
              class="w-20 px-2 py-1 text-right text-xs font-bold border border-slate-300 dark:border-zinc-700 rounded-md bg-slate-50 dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
            />
          </div>
        </div>

        <!-- Allocation Validation Status -->
        <div class="mt-2 pt-2 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between text-[11px] font-bold">
          <span>Total Allocated:</span>
          <span :class="isItemAllocationValid(orderItems[openWarehouseItemIndex]) ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
            {{ getItemAllocatedSum(orderItems[openWarehouseItemIndex]) }} / {{ orderItems[openWarehouseItemIndex].quantity_ordered }} Qty
          </span>
        </div>
        <p v-if="!isItemAllocationValid(orderItems[openWarehouseItemIndex])" class="text-[10px] text-rose-500 font-semibold mt-1">
          ⚠️ Allocated quantity must equal {{ orderItems[openWarehouseItemIndex].quantity_ordered }}.
        </p>
      </div>
    </teleport>
  </div>

  <!-- Floating Grand Total Badge -->
  <div class="fixed bottom-[10px] right-6 z-50 animate-fade-in-down">
    <div class="relative bg-slate-900 dark:bg-zinc-800 text-white pl-4 pr-5 py-1.5 min-w-[300px] rounded-xl shadow-xl flex flex-col items-end border border-slate-700 dark:border-zinc-700 cursor-default">
      <span class="absolute top-1.5 left-4 text-[9px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-400">Grand Total</span>
      <span class="text-2xl font-black leading-tight text-emerald-400 pt-3">{{ currencySymbol }}{{ grandTotal.toFixed(2) }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useCurrencyStore } from '@/stores/currency';
import api from '@/services/api';

const router = useRouter();
const authStore = useAuthStore();
const currencyStore = useCurrencyStore();
const activeCompany = ref(null);

const currencySymbol = computed(() => {
  return currencyStore.symbol || 'PKR';
});

// Accent colors
const accentColor = ref('#4f46e5');

// Reactive data
const products = ref([]);
const categories = ref([]);
const suppliers = ref([]);
const taxes = ref([]);
const orderItems = ref([]);
const selectedSupplier = ref(null);
const useAdvanceBalance = ref(false);
const isProductDropdownOpen = ref(false);
const productSearch = ref('');
const supplierSearch = ref('');
const supplierSearchResults = ref([]);
const loadingProducts = ref(false);
const saving = ref(false);
const creatingSupplier = ref(false);
const showSupplierModal = ref(false);
const notifications = ref([]);

const isPaymentDropdownOpen = ref(false);

const availablePaymentMethods = [
  { id: 'cash', label: 'Cash' },
  { id: 'card', label: 'Card' },
  { id: 'bank_transfer', label: 'Bank Transfer' },
  { id: 'other', label: 'Other' }
];

const selectedPaymentMethods = ref(['cash']);
const paymentAmounts = ref({
  cash: 0
});

const allAccounts = ref([]);
const activeBankAccounts = computed(() => {
  return (allAccounts.value || []).filter(account => {
    const type = (account.type || account.account_type || '').toLowerCase();
    const name = (account.account_name || account.bank_name || '').toLowerCase();
    if (type === 'cash' || name.includes('cash') || name.includes('vault')) {
      return false;
    }
    return true;
  });
});

const selectedBankIds = ref([]);
const bankPaymentAmounts = ref({});
const isBankDropdownOpen = ref(false);

const cashAccount = computed(() => {
  return (allAccounts.value || []).find(acc => {
    const type = (acc.type || acc.account_type || '').toLowerCase();
    const name = (acc.account_name || acc.bank_name || '').toLowerCase();
    return type === 'cash' || name.includes('cash') || name.includes('vault');
  });
});

const cashAvailableBalance = computed(() => {
  if (cashAccount.value && cashAccount.value.current_balance !== undefined) {
    return parseFloat(cashAccount.value.current_balance || 0);
  }
  return null;
});

const isCashBalanceExceeded = computed(() => {
  if (!selectedPaymentMethods.value.includes('cash')) return false;
  if (cashAvailableBalance.value === null) return false;
  const payAmt = parseFloat(paymentAmounts.value.cash) || 0;
  return payAmt > cashAvailableBalance.value;
});

const getBankAccountBalance = (bankId) => {
  const bank = (allAccounts.value || []).find(b => b.id == bankId);
  if (!bank) return 0;
  return parseFloat(bank.current_balance || 0);
};

const isBankBalanceExceeded = (bankId) => {
  if (!selectedPaymentMethods.value.includes('card') && !selectedPaymentMethods.value.includes('bank_transfer')) return false;
  const bal = getBankAccountBalance(bankId);
  const payAmt = parseFloat(bankPaymentAmounts.value[bankId]) || 0;
  return payAmt > bal;
};

const hasInsufficientPaymentBalance = computed(() => {
  if (isCashBalanceExceeded.value) return true;
  if (selectedPaymentMethods.value.includes('card') || selectedPaymentMethods.value.includes('bank_transfer')) {
    return selectedBankIds.value.some(bankId => isBankBalanceExceeded(bankId));
  }
  return false;
});

const isBankAccountInactive = (bankOrId) => {
  if (!bankOrId) return false;
  const bank = typeof bankOrId === 'object'
    ? bankOrId
    : (allAccounts.value || []).find(b => b.id == bankOrId);
  if (!bank) return false;
  return bank.is_active === false || bank.is_active === 0 || bank.is_active === '0';
};

const loadBankAccounts = async () => {
  try {
    const response = await api.get('/bank-accounts');
    const rawData = Array.isArray(response.data) ? response.data : (response.data?.data || []);
    allAccounts.value = rawData;
    const defaultActiveBank = activeBankAccounts.value.find(b => b.is_active !== false && b.is_active !== 0);
    if (defaultActiveBank && selectedBankIds.value.length === 0) {
      selectedBankIds.value = [defaultActiveBank.id];
      bankPaymentAmounts.value[defaultActiveBank.id] = 0;
    }
  } catch (error) {
    console.error('Error loading bank accounts:', error);
  }
};

const getPaymentMethodLabel = (methodId) => {
  const pm = availablePaymentMethods.find(m => m.id === methodId);
  return pm ? pm.label : methodId;
};

const getSelectedPaymentMethodsLabel = () => {
  if (selectedPaymentMethods.value.length === 0) return 'Select Payment Method(s)';
  return selectedPaymentMethods.value.map(id => getPaymentMethodLabel(id)).join(', ');
};

const togglePaymentMethod = (methodId) => {
  const idx = selectedPaymentMethods.value.indexOf(methodId);
  
  if (idx > -1) {
    if (selectedPaymentMethods.value.length === 1) return;
    selectedPaymentMethods.value.splice(idx, 1);
    if (methodId === 'cash') {
      paymentAmounts.value.cash = 0;
    }
  } else {
    selectedPaymentMethods.value.push(methodId);

    const defaultActiveBank = activeBankAccounts.value.find(b => b.is_active !== false && b.is_active !== 0);
    if ((methodId === 'card' || methodId === 'bank_transfer') && selectedBankIds.value.length === 0 && defaultActiveBank) {
      selectedBankIds.value = [defaultActiveBank.id];
      if (bankPaymentAmounts.value[defaultActiveBank.id] === undefined) {
        bankPaymentAmounts.value[defaultActiveBank.id] = 0;
      }
    }
    
    const existingSum = totalPaidAmount.value;
    const targetTotal = grandTotal.value || 0;
    const remaining = Math.max(0, targetTotal - existingSum);

    if (methodId === 'cash') {
      paymentAmounts.value.cash = parseFloat(remaining.toFixed(2));
    } else if (selectedBankIds.value.length > 0) {
      const firstBankId = selectedBankIds.value[0];
      bankPaymentAmounts.value[firstBankId] = parseFloat(((bankPaymentAmounts.value[firstBankId] || 0) + remaining).toFixed(2));
    }
  }
};

const toggleBankSelection = (bankId) => {
  if (isBankAccountInactive(bankId)) {
    return; // Cannot select or unselect inactive bank account
  }
  const idx = selectedBankIds.value.indexOf(bankId);
  if (idx > -1) {
    if (selectedBankIds.value.length === 1 && (selectedPaymentMethods.value.includes('card') || selectedPaymentMethods.value.includes('bank_transfer')) && !selectedPaymentMethods.value.includes('cash')) {
      return;
    }
    selectedBankIds.value.splice(idx, 1);
    delete bankPaymentAmounts.value[bankId];
  } else {
    selectedBankIds.value.push(bankId);
    
    const existingSum = totalPaidAmount.value;
    const targetTotal = grandTotal.value || 0;
    const remaining = Math.max(0, targetTotal - existingSum);
    bankPaymentAmounts.value[bankId] = parseFloat(remaining.toFixed(2));
  }
};

const getMaskedAccountNumber = (accNumber) => {
  if (!accNumber) return '';
  const str = String(accNumber).trim();
  if (!str) return '';
  const last4 = str.length > 4 ? str.slice(-4) : str;
  return '****' + last4;
};

const formatBankAccountLabel = (bank) => {
  if (!bank) return 'Bank';
  const bankName = (bank.bank_name || '').trim();
  const accountName = (bank.account_name || '').trim();
  const maskedAcc = bank.masked_account_number || getMaskedAccountNumber(bank.account_number);

  let baseLabel = '';
  if (bankName && accountName && bankName.toLowerCase() !== accountName.toLowerCase()) {
    baseLabel = `${bankName} (${accountName})`;
  } else {
    baseLabel = accountName || bankName || 'Bank';
  }

  if (maskedAcc) {
    return `${baseLabel} ${maskedAcc}`;
  }
  return baseLabel;
};

const getSelectedBanksLabel = () => {
  if (selectedBankIds.value.length === 0) return 'Select Bank Account(s)';
  const selected = activeBankAccounts.value.filter(b => selectedBankIds.value.includes(b.id));
  if (selected.length === 0) return 'Select Bank Account(s)';
  if (selected.length === 1) {
    return formatBankAccountLabel(selected[0]);
  }
  return `${selected.length} Bank Accounts Selected`;
};

const totalPaidAmount = computed(() => {
  let sum = 0;
  if (selectedPaymentMethods.value.includes('cash')) {
    sum += parseFloat(paymentAmounts.value.cash) || 0;
  }
  if (selectedPaymentMethods.value.includes('card') || selectedPaymentMethods.value.includes('bank_transfer')) {
    selectedBankIds.value.forEach(bankId => {
      sum += parseFloat(bankPaymentAmounts.value[bankId]) || 0;
    });
  }
  if (useAdvanceBalance.value) {
    sum += parseFloat(advanceToApply.value) || 0;
  }
  return sum;
});



const togglePaymentDropdown = (event) => {
  if (isPaymentDropdownOpen.value) {
    isPaymentDropdownOpen.value = false;
    return;
  }
  openWarehouseItemIndex.value = null;
  isPaymentDropdownOpen.value = true;

  nextTick(() => {
    const btn = event?.currentTarget;
    if (!btn) return;
    const rect = btn.getBoundingClientRect();
    const spaceAbove = rect.top;
    const spaceBelow = window.innerHeight - rect.bottom;
    const menuHeight = 180;

    const leftVal = Math.max(10, Math.min(window.innerWidth - rect.width - 10, rect.left));

    if (spaceBelow < menuHeight && spaceAbove > spaceBelow) {
      const bottomVal = Math.max(10, window.innerHeight - rect.top + 2);
      paymentDropdownPos.value = {
        top: 'auto',
        bottom: `${bottomVal}px`,
        left: `${leftVal}px`,
        width: `${rect.width}px`
      };
    } else {
      const topVal = rect.bottom + 2;
      paymentDropdownPos.value = {
        top: `${topVal}px`,
        bottom: 'auto',
        left: `${leftVal}px`,
        width: `${rect.width}px`
      };
    }
  });
};

const selectItemWarehouse = (index, whId) => {
  if (orderItems.value[index]) {
    orderItems.value[index].warehouse_id = whId;
    onItemWarehouseChange(index);
  }
  openWarehouseItemIndex.value = null;
};

const selectPaymentMethod = (val) => {
  orderForm.value.payment_method = val;
  isPaymentDropdownOpen.value = false;
};

// Current date time
const currentDateTime = ref('');

// Advance Search Modal State
const isAdvanceSearchModalOpen = ref(false);
const isTagDropdownOpen = ref(false);
const isCategorySelectModalOpen = ref(false);
const isTaxDropdownOpen = ref(false);

const advanceFilters = ref({
  query: '',
  sku: '',
  categories: [],
  tags: [],
  taxes: [],
  minPrice: null,
  maxPrice: null
});

// Combobox Search Queries & Options Navigation States
const tagSearchQuery = ref('');
const tagHighlightedIndex = ref(0);
const tagInputRef = ref(null);

const categorySearchQuery = ref('');
const categoryHighlightedIndex = ref(0);
const categoryInputRef = ref(null);

const isManualPoNumber = ref(false);
const autoGeneratedPoNumber = ref('');

const canEditPoNumber = computed(() => {
  return authStore.hasPermission('edit_po_number') ||
    authStore.hasPermission('purchases.edit_po_number') ||
    authStore.hasRole('admin') ||
    authStore.hasRole('owner');
});

const toggleManualPoNumber = () => {
  if (!isManualPoNumber.value) {
    orderForm.value.po_number = autoGeneratedPoNumber.value;
  }
};

const orderForm = ref({
  supplier_id: '',
  is_walkin_supplier: false,
  supplier_name: '',
  supplier_phone: '',
  supplier_email: '',
  po_number: '',
  order_date: new Date().toISOString().split('T')[0],
  expected_delivery_date: '',
  status: 'draft',
  payment_method: 'cash',
  tax_type: 'percentage',
  tax_amount: 0,
  discount_type: 'percentage',
  discount_amount: 0,
  shipping_cost: 0,
  amount_paid: 0,
  notes: '',
  terms_and_conditions: 'Standard purchase order conditions apply.'
});

const activeSupplierTab = ref('basic');

const newSupplier = ref({
  name: '',
  company_name: '',
  email: '',
  phone: '',
  mobile: '',
  address: '',
  city: '',
  state: '',
  postal_code: '',
  country: '',
  tax_number: '',
  website: '',
  notes: '',
  credit_limit: 0,
  payment_terms_days: 30,
  is_active: true
});

const supplierErrors = ref({
  name: '',
  phone: ''
});

watch(showSupplierModal, (isOpen) => {
  if (isOpen) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }
});

// Advance Search Helpers
const openAdvanceSearchModal = () => {
  isAdvanceSearchModalOpen.value = true;
};

const closeAdvanceSearchModal = () => {
  isAdvanceSearchModalOpen.value = false;
  isTagDropdownOpen.value = false;
  isCategorySelectModalOpen.value = false;
  tagSearchQuery.value = '';
  categorySearchQuery.value = '';
};

const clearAdvanceFilters = () => {
  advanceFilters.value = {
    query: '',
    sku: '',
    categories: [],
    tags: [],
    taxes: [],
    minPrice: null,
    maxPrice: null
  };
  tagSearchQuery.value = '';
  categorySearchQuery.value = '';
};

const hasActiveAdvanceFilters = computed(() => {
  const f = advanceFilters.value;
  return !!(
    (f.query && f.query.trim()) ||
    (f.sku && f.sku.trim()) ||
    f.categories.length > 0 ||
    f.tags.length > 0 ||
    (f.minPrice !== null && f.minPrice !== '' && !isNaN(f.minPrice)) ||
    (f.maxPrice !== null && f.maxPrice !== '' && !isNaN(f.maxPrice))
  );
});

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
  products.value.forEach(p => {
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
  if (!query) return categories.value;
  return categories.value.filter(cat => cat.name && cat.name.toLowerCase().includes(query));
});

const getCategoryNameById = (id) => {
  const cat = categories.value.find(c => String(c.id) === String(id));
  return cat ? cat.name : id;
};

const focusTagInput = () => {
  if (tagInputRef.value) tagInputRef.value.focus();
  isTagDropdownOpen.value = true;
  isCategorySelectModalOpen.value = false;
  tagHighlightedIndex.value = 0;
};

const openTagDropdown = () => {
  isTagDropdownOpen.value = true;
  isCategorySelectModalOpen.value = false;
  tagHighlightedIndex.value = 0;
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

const focusCategoryInput = () => {
  if (categoryInputRef.value) categoryInputRef.value.focus();
  openCategoryDropdown();
};

const openCategoryDropdown = () => {
  isCategorySelectModalOpen.value = true;
  isTagDropdownOpen.value = false;
  categoryHighlightedIndex.value = 0;
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
    if (f.query && f.query.trim()) params.search_term = f.query.trim();
    if (f.sku && f.sku.trim()) params.sku = f.sku.trim();
    if (f.categories.length > 0) params.category_id = f.categories.join(',');
    if (f.tags.length > 0) params.tag_id = f.tags.join(',');
    if (f.minPrice !== null && f.minPrice !== '' && !isNaN(f.minPrice)) params.min_price = f.minPrice;
    if (f.maxPrice !== null && f.maxPrice !== '' && !isNaN(f.maxPrice)) params.max_price = f.maxPrice;

    const res = await api.get('/items/advanced-search', { params });
    const remoteItems = res.data.items || res.data.data || [];
    if (remoteItems.length > 0) {
      const existingKeys = new Set(products.value.map(p => getProductUniqueKey(p)));
      remoteItems.forEach(item => {
        const itemKey = getProductUniqueKey(item);
        if (!existingKeys.has(itemKey)) {
          products.value.push(item);
          existingKeys.add(itemKey);
        }
      });
    }
  } catch (err) {
    console.error('Advanced search API error:', err);
  } finally {
    isSearchingAdvance.value = false;
  }
}, 300);

watch(advanceFilters, () => {
  searchItemsFromBackend();
}, { deep: true });

const advanceFilteredProducts = computed(() => {
  if (!hasActiveAdvanceFilters.value) {
    return [];
  }

  let list = products.value;
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

  if (f.categories.length > 0) {
    const selectedCatIds = f.categories.map(id => String(id));
    list = list.filter(p => selectedCatIds.includes(String(p.category_id)));
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

const addAdvanceProductToOrder = (product) => {
  addToOrder(product);
  showNotification(`Added "${product.name}" to order`, 'success');
};

const handleProductSearchEnter = () => {
  if (displayedProducts.value.length > 0) {
    selectProductFromDropdown(displayedProducts.value[0]);
  }
};

// Computed properties
const filteredProducts = computed(() => {
  let filtered = products.value;

  if (productSearch.value) {
    const search = productSearch.value.toLowerCase();
    filtered = filtered.filter(product =>
      product.name.toLowerCase().includes(search) ||
      product.sku.toLowerCase().includes(search) ||
      (product.barcode && product.barcode.toLowerCase().includes(search))
    );
  }

  return filtered;
});

const displayedProducts = computed(() => {
  return filteredProducts.value.slice(0, 50);
});

const orderSubtotal = computed(() => {
  if (!orderItems.value || orderItems.value.length === 0) return 0;
  return orderItems.value.reduce((sum, item) => {
    const itemTotal = parseFloat(item.total_cost) || 0;
    return sum + itemTotal;
  }, 0);
});

const calculatedManualTax = computed(() => {
  const taxVal = parseFloat(orderForm.value.tax_amount) || 0;
  if (orderForm.value.tax_type === 'percentage') {
    return (orderSubtotal.value * taxVal) / 100;
  }
  return taxVal;
});

const disabledRequiredTaxIds = ref([]);

const toggleRequiredTax = (taxId) => {
  const idx = disabledRequiredTaxIds.value.indexOf(taxId);
  if (idx > -1) {
    disabledRequiredTaxIds.value.splice(idx, 1);
  } else {
    disabledRequiredTaxIds.value.push(taxId);
  }
};

const requiredTaxes = computed(() => {
  return taxes.value.filter(t => (t.is_active || t.is_active === 1) && (t.purchase_order_required || t.purchase_order_required === 1));
});

const autoRequiredTaxesList = computed(() => {
  const sub = orderSubtotal.value || 0;
  return requiredTaxes.value.map(t => {
    const val = parseFloat(t.value) || 0;
    const isEnabled = !disabledRequiredTaxIds.value.includes(t.id);
    const amt = isEnabled ? (t.type === 'percentage' ? (sub * val) / 100 : val) : 0;
    return {
      id: t.id,
      name: t.name,
      rate: val,
      type: t.type || 'percentage',
      amount: amt,
      enabled: isEnabled
    };
  });
});

const totalAutoRequiredTax = computed(() => {
  return autoRequiredTaxesList.value
    .filter(item => item.enabled)
    .reduce((sum, item) => sum + item.amount, 0);
});

const calculatedManualDiscount = computed(() => {
  const disVal = parseFloat(orderForm.value.discount_amount) || 0;
  if (orderForm.value.discount_type === 'percentage') {
    return (orderSubtotal.value * disVal) / 100;
  }
  return disVal;
});

const grandTotal = computed(() => {
  const sub = orderSubtotal.value || 0;
  const shipping = parseFloat(orderForm.value.shipping_cost) || 0;
  return Math.max(0, sub + totalAutoRequiredTax.value + calculatedManualTax.value + shipping - calculatedManualDiscount.value);
});

const orderTotal = computed(() => {
  return grandTotal.value;
});

const dueAmount = computed(() => {
  const total = grandTotal.value || 0;
  const paid = totalPaidAmount.value || 0;
  return Math.max(0, total - paid);
});

const advanceToApply = computed(() => {
  if (!useAdvanceBalance.value || !selectedSupplier.value) return 0;
  const advanceBal = parseFloat(selectedSupplier.value.advance_balance || 0);
  return Math.min(advanceBal, Math.max(0, grandTotal.value));
});

const effectiveDueAmount = computed(() => {
  if (useAdvanceBalance.value) {
    return Math.max(0, grandTotal.value - advanceToApply.value);
  }
  return grandTotal.value;
});

watch(grandTotal, (newGrandTotal) => {
  const val = parseFloat(newGrandTotal.toFixed(2));
  if (selectedPaymentMethods.value.includes('cash')) {
    paymentAmounts.value.cash = val;
  } else if (selectedBankIds.value.length > 0) {
    bankPaymentAmounts.value[selectedBankIds.value[0]] = val;
  }
}, { immediate: true });

const currentDate = computed(() => {
  return currentDateTime.value.split(',')[0] || '';
});

const currentTime = computed(() => {
  return currentDateTime.value.split(',')[1] || '';
});

// Methods
const updateDateTime = () => {
  const now = new Date();
  const date = now.toLocaleDateString();
  const time = now.toLocaleTimeString();
  currentDateTime.value = `${date}, ${time}`;
};

const warehouses = ref([]);
const selectedGlobalWarehouseIds = ref([]);
const isGlobalWarehouseDropdownOpen = ref(false);
const globalWarehouseSearch = ref('');
const openWarehouseItemIndex = ref(null);

const warehousePopPos = ref({ top: '0px', left: '0px' });

const toggleItemWarehouseDropdown = (index, event) => {
  if (event) event.stopPropagation();
  if (openWarehouseItemIndex.value === index) {
    openWarehouseItemIndex.value = null;
    return;
  }
  openWarehouseItemIndex.value = index;

  nextTick(() => {
    const btn = event?.currentTarget || document.getElementById(`item-wh-btn-${index}`);
    if (btn) {
      const rect = btn.getBoundingClientRect();
      const popoverWidth = 288;
      const popoverHeight = 220;
      
      let top = rect.top - popoverHeight - 8;
      if (top < 10) {
        top = rect.bottom + 8;
      }
      
      let left = rect.right - popoverWidth;
      if (left < 10) left = 10;
      if (left + popoverWidth > window.innerWidth - 10) {
        left = window.innerWidth - popoverWidth - 10;
      }

      warehousePopPos.value = {
        top: `${top}px`,
        left: `${left}px`
      };
    }
  });
};

const searchableGlobalWarehouses = computed(() => {
  if (!globalWarehouseSearch.value) return warehouses.value;
  const q = globalWarehouseSearch.value.toLowerCase();
  return warehouses.value.filter(w => w.name && w.name.toLowerCase().includes(q));
});

const globalWarehouseSummaryLabel = computed(() => {
  if (selectedGlobalWarehouseIds.value.length === 0) return 'Select Warehouse(s)';
  const firstWh = warehouses.value.find(w => selectedGlobalWarehouseIds.value.includes(w.id));
  const firstName = firstWh ? firstWh.name : 'Warehouse';
  if (selectedGlobalWarehouseIds.value.length === 1) return firstName;
  return `${firstName} +${selectedGlobalWarehouseIds.value.length - 1} (${selectedGlobalWarehouseIds.value.length} WHs)`;
});

const syncItemAllocations = (item) => {
  if (!item) return;
  const activeWhIds = selectedGlobalWarehouseIds.value.length > 0
    ? selectedGlobalWarehouseIds.value
    : (warehouses.value.length > 0 ? [warehouses.value[0].id] : [1]);

  if (!Array.isArray(item.allocations)) {
    item.allocations = [];
  }

  // Filter out removed warehouses
  item.allocations = item.allocations.filter(a => activeWhIds.includes(a.warehouse_id));

  // Add newly selected warehouses
  activeWhIds.forEach((whId, idx) => {
    const existing = item.allocations.find(a => a.warehouse_id == whId);
    if (!existing) {
      item.allocations.push({
        warehouse_id: whId,
        quantity: idx === 0 && item.allocations.length === 0 ? (item.quantity_ordered || 1) : 0
      });
    }
  });

  const sum = getItemAllocatedSum(item);
  if (sum === 0 && item.allocations.length > 0) {
    item.allocations[0].quantity = item.quantity_ordered || 1;
  }
};

const onGlobalWarehouseChange = () => {
  orderItems.value.forEach(item => {
    syncItemAllocations(item);
  });
};

const getItemAllocatedSum = (item) => {
  if (!item || !Array.isArray(item.allocations)) return 0;
  return item.allocations.reduce((s, a) => s + (parseInt(a.quantity) || 0), 0);
};

const isItemAllocationValid = (item) => {
  if (!item) return true;
  const target = parseInt(item.quantity_ordered) || 0;
  return getItemAllocatedSum(item) === target;
};

const getItemAllocationSummary = (item) => {
  if (!item || !Array.isArray(item.allocations) || item.allocations.length === 0) {
    return 'Select Warehouse';
  }

  const activeAllocations = item.allocations.filter(a => (parseInt(a.quantity) || 0) > 0);
  if (activeAllocations.length === 0) {
    const firstWh = warehouses.value.find(w => w.id == item.allocations[0].warehouse_id);
    return firstWh ? `${firstWh.name} (0 Qty)` : 'Allocate Qty';
  }

  if (activeAllocations.length === 1) {
    const wh = warehouses.value.find(w => w.id == activeAllocations[0].warehouse_id);
    const name = wh ? wh.name : 'Warehouse';
    return `${name} (${activeAllocations[0].quantity} Qty)`;
  }

  const sum = getItemAllocatedSum(item);
  return `${activeAllocations.length} WHs Split (${sum}/${item.quantity_ordered} Qty)`;
};

const loadWarehouses = async () => {
  try {
    const response = await api.get('/warehouses');
    warehouses.value = response.data.data || response.data || [];
    if (warehouses.value.length > 0 && selectedGlobalWarehouseIds.value.length === 0) {
      const defaultWh = warehouses.value.find(w => w.is_default) || warehouses.value[0];
      selectedGlobalWarehouseIds.value = [defaultWh.id];
    }
  } catch (error) {
    console.error('Error loading warehouses:', error);
  }
};

const getProductStock = (product) => {
  return getProductWarehouseStock(product, null);
};

const getProductWarehouseStock = (product, warehouseId) => {
  if (!product) return 0;
  if (!product.track_inventory && product.track_inventory !== undefined) return '∞';
  if (!warehouseId) return product.stock_quantity ?? product.total_stock ?? 0;
  return product.warehouse_stocks?.[warehouseId] ?? product.stock_quantity ?? 0;
};

const getItemAvailableStock = (item) => {
  if (!item || !item.product) return '∞';
  const whIds = (Array.isArray(item.warehouse_ids) && item.warehouse_ids.length > 0)
    ? item.warehouse_ids
    : (item.warehouse_id ? [item.warehouse_id] : []);

  if (whIds.length === 0) return item.product.stock_quantity ?? item.product.total_stock ?? 0;
  return whIds.reduce((sum, whId) => sum + (item.product.warehouse_stocks?.[whId] ?? item.product.stock_quantity ?? 0), 0);
};

const isItemStockExceeded = (item) => false;
const validateItemStock = (item, notify = false) => {};

const onItemQtyChange = (index) => {
  const item = orderItems.value[index];
  if (item) {
    if (Array.isArray(item.allocations) && item.allocations.length === 1) {
      item.allocations[0].quantity = item.quantity_ordered;
    }
    validateItemStock(item, true);
    updateItemTotal(index);
  }
};

const onItemWarehouseChange = (index) => {
  const item = orderItems.value[index];
  if (item) {
    validateItemStock(item, true);
    updateItemTotal(index);
  }
};

const loadProducts = async () => {
  try {
    loadingProducts.value = true;
    const response = await api.get('/products');
    products.value = response.data.data || response.data;
  } catch (error) {
    showNotification('Error loading products', 'error');
    console.error('Error:', error);
  } finally {
    loadingProducts.value = false;
  }
};

const loadSuppliers = async () => {
  try {
    const response = await api.get('/suppliers');
    suppliers.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error loading suppliers:', error);
  }
};

const searchSuppliers = async (query = '') => {
  if (!query) {
    supplierSearchResults.value = suppliers.value.slice(0, 5);
    return;
  }
  const search = query.toLowerCase();
  supplierSearchResults.value = suppliers.value.filter(supplier =>
    supplier.name.toLowerCase().includes(search) ||
    (supplier.phone && supplier.phone.includes(search)) ||
    (supplier.email && supplier.email.toLowerCase().includes(search))
  ).slice(0, 5);
};

const debouncedSupplierSearch = debounce(() => {
  searchSuppliers(supplierSearch.value);
}, 300);

const highlightedProductIndex = ref(-1);
const productItemRefs = ref({});

const setProductItemRef = (el, idx) => {
  if (el) {
    productItemRefs.value[idx] = el;
  }
};

const scrollToHighlightedItem = () => {
  nextTick(() => {
    const el = productItemRefs.value[highlightedProductIndex.value];
    if (el && typeof el.scrollIntoView === 'function') {
      el.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }
  });
};

watch(displayedProducts, (newProducts) => {
  if (newProducts.length > 0 && productSearch.value.trim() !== '') {
    highlightedProductIndex.value = 0;
  } else {
    highlightedProductIndex.value = -1;
  }
}, { immediate: true });

const handleProductSearchKeydown = (event) => {
  if (event.key === 'ArrowDown') {
    event.preventDefault();
    if (!isProductDropdownOpen.value) {
      isProductDropdownOpen.value = true;
    }
    if (displayedProducts.value.length === 0) return;
    if (highlightedProductIndex.value < displayedProducts.value.length - 1) {
      highlightedProductIndex.value++;
    } else {
      highlightedProductIndex.value = 0;
    }
    scrollToHighlightedItem();
  } else if (event.key === 'ArrowUp') {
    event.preventDefault();
    if (!isProductDropdownOpen.value) {
      isProductDropdownOpen.value = true;
    }
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
    }
  } else if (event.key === 'Escape') {
    event.preventDefault();
    isProductDropdownOpen.value = false;
    highlightedProductIndex.value = -1;
  }
};

const selectProductFromDropdown = (product) => {
  addToOrder(product);
  productSearch.value = '';
  isProductDropdownOpen.value = false;
  highlightedProductIndex.value = -1;
  productItemRefs.value = {};
};

const addToOrder = (product) => {
  const existingItem = orderItems.value.find(item => item.product.id === product.id);

  if (existingItem) {
    existingItem.quantity_ordered += 1;
    syncItemAllocations(existingItem);
    updateItemTotal(orderItems.value.indexOf(existingItem));
  } else {
    const newItem = {
      product: product,
      product_id: product.id,
      quantity_ordered: 1,
      unit_cost: parseFloat(product.cost_price || product.selling_price || 0),
      total_cost: parseFloat(product.cost_price || product.selling_price || 0),
      notes: '',
      allocations: []
    };
    syncItemAllocations(newItem);
    orderItems.value.push(newItem);
  }
};

const removeFromOrder = (index) => {
  orderItems.value.splice(index, 1);
};

const updateItemTotal = (index) => {
  const item = orderItems.value[index];
  const qty = parseFloat(item.quantity_ordered) || 0;
  const cost = parseFloat(item.unit_cost) || 0;
  item.total_cost = qty * cost;
};

const onWalkinToggle = () => {
  if (orderForm.value.is_walkin_supplier) {
    if (selectedSupplier.value) {
      orderForm.value.supplier_name = selectedSupplier.value.name || '';
      if (!orderForm.value.supplier_phone) orderForm.value.supplier_phone = selectedSupplier.value.phone || '';
      if (!orderForm.value.supplier_email) orderForm.value.supplier_email = selectedSupplier.value.email || '';
    }
    selectedSupplier.value = null;
    orderForm.value.supplier_id = '';
  } else {
    orderForm.value.supplier_name = '';
  }
};

const selectSupplier = (supplier) => {
  selectedSupplier.value = supplier;
  orderForm.value.supplier_id = supplier.id;
  orderForm.value.supplier_name = supplier.name || '';
  orderForm.value.supplier_phone = supplier.phone || supplier.mobile || '';
  orderForm.value.supplier_email = supplier.email || '';
  supplierSearch.value = supplier.name;
  supplierSearchResults.value = [];
  useAdvanceBalance.value = false;
  // Fetch fresh supplier details from API
  api.get(`/suppliers/${supplier.id}`).then(res => {
    if (res.data) {
      if (res.data.advance_balance !== undefined) {
        selectedSupplier.value = { ...selectedSupplier.value, advance_balance: res.data.advance_balance };
      }
      if (res.data.phone && !orderForm.value.supplier_phone) {
        orderForm.value.supplier_phone = res.data.phone;
      }
      if (res.data.email && !orderForm.value.supplier_email) {
        orderForm.value.supplier_email = res.data.email;
      }
    }
  }).catch(() => {});
};

const clearSupplier = () => {
  selectedSupplier.value = null;
  orderForm.value.supplier_id = '';
  supplierSearch.value = '';
  supplierSearchResults.value = [];
  useAdvanceBalance.value = false;
  if (!orderForm.value.is_walkin_supplier) {
    orderForm.value.supplier_name = '';
    orderForm.value.supplier_phone = '';
    orderForm.value.supplier_email = '';
  }
};

const createSupplier = async () => {
  supplierErrors.value = { name: '', phone: '' };

  let isValid = true;
  if (!newSupplier.value.name?.trim()) {
    supplierErrors.value.name = 'Supplier name is required';
    activeSupplierTab.value = 'basic';
    isValid = false;
  }

  const hasPhone = (newSupplier.value.phone && newSupplier.value.phone.trim()) || 
                   (newSupplier.value.mobile && newSupplier.value.mobile.trim());
  if (!hasPhone) {
    supplierErrors.value.phone = 'Phone number is required';
    if (isValid) {
      activeSupplierTab.value = 'contact';
    }
    isValid = false;
  }

  if (!isValid) {
    return;
  }

  creatingSupplier.value = true;

  try {
    const response = await api.post('/suppliers', newSupplier.value);
    const supplier = response.data.supplier || response.data;
    suppliers.value.push(supplier);
    selectSupplier(supplier);
    showNotification('Supplier created successfully', 'success');
    closeSupplierModal();
  } catch (error) {
    if (error.response?.data?.errors) {
      if (error.response.data.errors.name) {
        supplierErrors.value.name = error.response.data.errors.name[0];
        activeSupplierTab.value = 'basic';
      }
      if (error.response.data.errors.phone) {
        supplierErrors.value.phone = error.response.data.errors.phone[0];
        activeSupplierTab.value = 'contact';
      }
    } else {
      showNotification('Error creating supplier', 'error');
    }
    console.error('Error:', error);
  } finally {
    creatingSupplier.value = false;
  }
};

const closeSupplierModal = () => {
  showSupplierModal.value = false;
  activeSupplierTab.value = 'basic';
  supplierErrors.value = { name: '', phone: '' };
  newSupplier.value = {
    name: '',
    company_name: '',
    email: '',
    phone: '',
    mobile: '',
    address: '',
    city: '',
    state: '',
    postal_code: '',
    country: '',
    tax_number: '',
    website: '',
    notes: '',
    credit_limit: 0,
    payment_terms_days: 30,
    is_active: true
  };
};

const saveOrder = async () => {
  if (!selectedSupplier.value) {
    showNotification('Please select a supplier', 'error');
    return;
  }

  if (orderItems.value.length === 0) {
    showNotification('Please add at least one item', 'error');
    return;
  }

  if (hasInsufficientPaymentBalance.value) {
    showNotification('Cannot submit purchase order: Insufficient balance in selected payment account(s)', 'error');
    return;
  }

  const invalidItem = orderItems.value.find(item => !isItemAllocationValid(item));
  if (invalidItem) {
    showNotification(`Warehouse allocation total for "${invalidItem.product?.name || 'Item'}" (${getItemAllocatedSum(invalidItem)}) does not match ordered quantity (${invalidItem.quantity_ordered}). Please balance allocations.`, 'error');
    return;
  }

  saving.value = true;

  try {
    const orderData = {
      supplier_id: orderForm.value.supplier_id,
      po_number: orderForm.value.po_number || null,
      warehouse_id: selectedGlobalWarehouseIds.value[0] || 1,
      warehouse_ids: selectedGlobalWarehouseIds.value,
      order_date: orderForm.value.order_date,
      expected_delivery_date: orderForm.value.expected_delivery_date || null,
      tax_amount: orderForm.value.tax_amount || 0,
      shipping_cost: orderForm.value.shipping_cost || 0,
      amount_paid: totalPaidAmount.value || 0,
      use_advance_balance: useAdvanceBalance.value,
      advance_applied: advanceToApply.value,
      notes: orderForm.value.notes || null,
      terms_and_conditions: orderForm.value.terms_and_conditions || null,
      payment_details: [
        ...(useAdvanceBalance.value && advanceToApply.value > 0 ? [{
          payment_method: 'vendor_advance',
          account_id: 'COA_10500',
          amount: parseFloat(advanceToApply.value) || 0
        }] : []),
        ...(selectedPaymentMethods.value.includes('cash') && (paymentAmounts.value.cash || 0) > 0 ? [{
          payment_method: 'cash',
          bank_account_id: cashAccount.value?.id || null,
          account_name: cashAccount.value?.account_name || 'Cash Vault',
          amount: parseFloat(paymentAmounts.value.cash) || 0
        }] : []),
        ...(selectedPaymentMethods.value.includes('card') || selectedPaymentMethods.value.includes('bank_transfer') ? selectedBankIds.value.map(bankId => {
          const bank = (allAccounts.value || []).find(b => b.id == bankId);
          return {
            payment_method: 'bank_transfer',
            bank_account_id: bankId,
            account_name: bank ? (bank.account_name || bank.bank_name) : `Bank #${bankId}`,
            amount: parseFloat(bankPaymentAmounts.value[bankId]) || 0
          };
        }).filter(p => p.amount > 0) : [])
      ],
      items: orderItems.value.map(item => ({
        product_id: item.product_id,
        quantity_ordered: item.quantity_ordered,
        unit_cost: item.unit_cost,
        notes: item.notes || null,
        allocations: item.allocations || []
      }))
    };

    await api.post('/purchase-orders', orderData);

    showNotification('Purchase order created successfully and items added to inventory', 'success');

    setTimeout(() => {
      router.push('/purchase/orders');
    }, 1500);

  } catch (error) {
    showNotification(error.response?.data?.message || 'Error creating purchase order', 'error');
    console.error('Error:', error);
  } finally {
    saving.value = false;
  }
};

const clearOrder = () => {
  if (confirm('Are you sure you want to clear all purchase order inputs?')) {
    orderItems.value = [];
    clearSupplier();
    orderForm.value.supplier_name = '';
    orderForm.value.supplier_phone = '';
    orderForm.value.supplier_email = '';
    orderForm.value.tax_amount = 0;
    orderForm.value.shipping_cost = 0;
    orderForm.value.amount_paid = 0;
    paymentAmounts.value = { cash: 0 };
    bankPaymentAmounts.value = {};
    orderForm.value.notes = '';
    orderForm.value.expected_delivery_date = '';
    orderForm.value.terms_and_conditions = 'Standard purchase order conditions apply.';
  }
};

const goBack = () => {
  router.push('/purchase/orders');
};

const showNotification = (message, type = 'info') => {
  const notification = {
    id: Date.now(),
    message,
    type
  };
  notifications.value.push(notification);

  setTimeout(() => {
    removeNotification(notification.id);
  }, 5000);
};

const removeNotification = (id) => {
  const index = notifications.value.findIndex(n => n.id === id);
  if (index > -1) {
    notifications.value.splice(index, 1);
  }
};

const loadCategories = async () => {
  try {
    const response = await api.get('/categories');
    categories.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error loading categories:', error);
  }
};

const handleClickOutside = (event) => {
  const productContainer = document.getElementById('product-search-container');
  if (productContainer && !productContainer.contains(event.target)) {
    isProductDropdownOpen.value = false;
  }

  const supplierContainer = document.getElementById('supplier-search-container');
  if (supplierContainer && !supplierContainer.contains(event.target)) {
    supplierSearchResults.value = [];
  }

  const paymentMethodContainer = document.getElementById('payment-method-dropdown-container');
  if (paymentMethodContainer && !paymentMethodContainer.contains(event.target)) {
    isPaymentDropdownOpen.value = false;
  }

  const bankDropdownContainer = document.getElementById('bank-dropdown-container');
  if (bankDropdownContainer && !bankDropdownContainer.contains(event.target)) {
    isBankDropdownOpen.value = false;
  }

  if (openWarehouseItemIndex.value !== null) {
    const itemWhContainer = document.getElementById(`item-wh-dropdown-${openWarehouseItemIndex.value}`);
    const popoverContainer = document.getElementById('teleport-wh-popover');
    if (
      itemWhContainer && !itemWhContainer.contains(event.target) &&
      popoverContainer && !popoverContainer.contains(event.target)
    ) {
      openWarehouseItemIndex.value = null;
    }
  }
};

// Utility function
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

const fetchNextPONumber = async () => {
  try {
    const response = await api.get('/purchase-orders/next-number');
    if (response.data && response.data.success) {
      autoGeneratedPoNumber.value = response.data.po_number;
      if (!isManualPoNumber.value) {
        orderForm.value.po_number = response.data.po_number;
      }
    }
  } catch (error) {
    console.error('Error fetching next PO number:', error);
  }
};

const fetchActiveCompany = async () => {
  try {
    const response = await api.get('/companies/active');
    if (response.data && response.data.company) {
      activeCompany.value = response.data.company;
      const comp = response.data.company;
      const code = comp.currency_symbol || comp.currency || comp.base_currency;
      if (code) {
        currencyStore.seedFromCompany(code);
      }
    }
  } catch (error) {
    console.error('Error fetching active company:', error);
  }
};

const loadTaxes = async () => {
  try {
    const response = await api.get('/taxes');
    taxes.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error loading taxes:', error);
  }
};

// Lifecycle
onMounted(() => {
  updateDateTime();
  setInterval(updateDateTime, 1000);
  fetchActiveCompany();
  loadWarehouses();
  loadProducts();
  loadCategories();
  loadTags();
  loadSuppliers();
  loadTaxes();
  loadBankAccounts();
  fetchNextPONumber();
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
/* Custom scrollbar for webkit browsers */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  background: #f8fafc;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Smooth transitions */
.transition-all {
  transition: all 0.2s ease-in-out;
}

/* Button hover scale effect */
button {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

button:hover:not(:disabled) {
  transform: translateY(-0.5px);
}

button:active:not(:disabled) {
  transform: translateY(0.5px);
}

/* Input focus effect */
input:focus,
select:focus,
textarea:focus {
  border-color: #6366f1 !important;
  box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1) !important;
}

/* Loading animation */
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>
