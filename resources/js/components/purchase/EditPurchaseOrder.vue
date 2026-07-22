<template>
  <div class="w-full bg-slate-50 dark:bg-zinc-950 min-h-screen">
    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col justify-center items-center h-64 space-y-3">
      <div class="animate-spin rounded-full h-10 w-10 border-2 border-slate-300 dark:border-zinc-700 border-t-indigo-600"></div>
      <span class="text-xs text-slate-500 dark:text-zinc-400 font-semibold">Loading purchase order details...</span>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-rose-50 border border-rose-250 rounded-xl p-5 m-6 flex items-start space-x-3 text-left">
      <div class="flex-shrink-0">
        <svg class="h-5 w-5 text-rose-550" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
      </div>
      <div>
        <h3 class="text-sm font-bold text-rose-900">Error loading purchase order</h3>
        <p class="mt-1 text-xs text-rose-700">{{ error }}</p>
        <button @click="goBack" class="mt-3 px-3 py-1.5 bg-rose-600 text-white font-bold text-[11px] rounded-lg hover:bg-rose-750 transition-all cursor-pointer border-0">Back to Orders</button>
      </div>
    </div>

    <!-- Edit Form -->
    <div v-else-if="purchaseOrder" class="w-full">
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
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Edit Purchase Order</h1>
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
                      <th class="py-3 px-2 w-2.5/12 text-right bg-slate-50 dark:bg-zinc-900">Unit Cost</th>
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

                    <tr v-for="(item, index) in orderItems" :key="index" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/20 group align-top">
                      <!-- Name and SKU -->
                      <td class="py-3 px-3">
                        <div class="font-bold text-slate-800 dark:text-zinc-100 text-sm mb-0.5">{{ item.product ? item.product.name : 'Product' }}</div>
                        <div class="text-[10px] text-slate-500 dark:text-zinc-400 font-mono mb-1">SKU: {{ item.product ? item.product.sku : '' }}</div>
                        <textarea
                          v-model="item.notes"
                          placeholder="Add line item description / details..."
                          rows="1"
                          class="w-full bg-slate-50/50 dark:bg-zinc-900/60 hover:bg-slate-100/80 dark:hover:bg-zinc-800/80 focus:bg-white dark:focus:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded px-2 py-1 text-slate-600 dark:text-zinc-300 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-[10px]"
                        ></textarea>
                      </td>

                      <!-- Qty -->
                      <td class="py-3 px-2 text-center">
                        <input
                          v-model.number="item.quantity_ordered"
                          type="number"
                          min="1"
                          class="w-16 px-1.5 py-1 text-center border border-slate-300 dark:border-zinc-700 rounded focus:outline-none focus:ring-1 focus:ring-indigo-500 font-bold bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200"
                          @input="updateItemTotal(index)"
                        />
                        <div class="text-[9px] text-slate-400 dark:text-zinc-500 mt-1">Stock: {{ getProductStock(item.product) }}</div>
                      </td>

                      <!-- Unit Cost -->
                      <td class="py-3 px-2 text-right">
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
                      <td class="py-3 px-2 text-right font-bold text-slate-800 dark:text-zinc-200 text-sm align-middle">
                        {{ currencySymbol }}{{ item.total_cost.toFixed(2) }}
                      </td>

                      <!-- Remove Button -->
                      <td class="py-3 px-1 text-center align-middle">
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

                  <!-- 2. Total Amount -->
                  <tr class="bg-slate-100/50 dark:bg-zinc-800/30 font-bold border-t border-slate-200 dark:border-zinc-800">
                    <td colspan="3" class="py-2 px-3 text-right text-slate-800 dark:text-zinc-200 text-xs">Total Amount</td>
                    <td class="py-2 px-2 text-right text-slate-900 dark:text-zinc-100 text-sm font-black">{{ currencySymbol }}{{ orderSubtotal.toFixed(2) }}</td>
                    <td class="w-[40px]"></td>
                  </tr>

                  <!-- 3. Taxes (manual field) -->
                  <tr>
                    <td colspan="3" class="py-2 px-3 text-right font-semibold text-slate-500 dark:text-zinc-400">Taxes (Manual)</td>
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

                  <!-- 6. Payment Method & Receiving Amount -->
                  <tr class="bg-slate-50/90 dark:bg-zinc-900/60 border-b border-slate-200 dark:border-zinc-800">
                    <td colspan="5" class="p-3">
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
                        <div>
                          <label class="block text-[10px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider mb-1">Payment Method</label>
                          <select
                            v-model="orderForm.payment_method"
                            class="w-full px-3 py-1.5 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs font-bold bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                          >
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mobile_payment">Mobile Payment</option>
                            <option value="mixed">Mixed</option>
                          </select>
                        </div>
                        <div>
                          <label class="block text-[10px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider mb-1">Receiving Amount</label>
                          <div class="relative">
                            <span class="absolute inset-y-0 left-2.5 flex items-center text-slate-400 dark:text-zinc-500 text-xs font-bold">{{ currencySymbol }}</span>
                            <input
                              v-model.number="orderForm.amount_paid"
                              type="number"
                              step="0.01"
                              min="0"
                              class="w-full pl-7 pr-3 py-1.5 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs font-black text-emerald-600 dark:text-emerald-400 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                              :placeholder="grandTotal.toFixed(2)"
                            />
                          </div>
                        </div>
                      </div>
                    </td>
                  </tr>

                  <!-- 7. Remaining Due -->
                  <tr v-if="effectiveDueAmount > 0">
                    <td colspan="3" class="py-2 px-3 text-right font-extrabold text-rose-600 dark:text-rose-400">Remaining Due Amount</td>
                    <td class="py-2 px-2 text-right font-extrabold text-rose-700 dark:text-rose-300 bg-rose-50/80 dark:bg-rose-950/20">{{ currencySymbol }}{{ effectiveDueAmount.toFixed(2) }}</td>
                    <td class="w-[40px]"></td>
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
                <h2 class="text-xl font-black uppercase tracking-wider transition-all duration-300" :style="{ color: accentColor }">EDIT PURCHASE ORDER</h2>
              </div>

              <!-- Metadata Form Fields -->
              <div class="space-y-2.5 text-xs">
                <!-- PO Number -->
                <div>
                  <label class="block text-slate-500 dark:text-zinc-400 font-semibold mb-1">PO Number:</label>
                  <input
                    v-model="orderForm.po_number"
                    type="text"
                    readonly
                    class="w-full px-3 py-1.5 border border-slate-300 dark:border-zinc-700 rounded-lg text-slate-700 dark:text-zinc-200 bg-slate-100 dark:bg-zinc-800/60 font-semibold text-xs"
                  />
                </div>

                <!-- BILL TO / SUPPLIER DETAILS SECTION (Moved under Order Number) -->
                <div class="space-y-2 pt-1 pb-1 border-t border-b border-slate-100 dark:border-zinc-800/60">
                  <h3 class="text-[11px] font-extrabold uppercase text-slate-500 dark:text-zinc-400 tracking-wider">Bill To</h3>
                  
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
                        <p class="font-bold text-slate-800 dark:text-zinc-100 text-sm truncate">{{ selectedSupplier.name }}</p>
                        <p v-if="selectedSupplier.phone" class="text-[11px] text-slate-500 dark:text-zinc-400">{{ selectedSupplier.phone }}</p>
                      </div>
                    </div>
                  </div>
                  <div v-else class="text-slate-400 dark:text-zinc-500 text-[11px] italic text-left">
                    No supplier selected. Search above to assign.
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

              <!-- Status configurations -->
              <div class="text-left">
                <label class="text-[10px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wide mb-1.5 block">Order Status</label>
                <select
                  v-model="orderForm.status"
                  class="w-full px-2.5 py-2 border border-slate-200/80 dark:border-zinc-700 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs cursor-pointer bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200"
                >
                  <option value="draft">Draft</option>
                  <option value="sent">Sent</option>
                  <option value="confirmed">Confirmed</option>
                  <option value="received">Received</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>
            </div>

          <!-- Sidebar Sticky Footer Actions -->
          <div class="p-5 border-t border-slate-200 dark:border-zinc-800 bg-slate-50/80 dark:bg-zinc-900/40 mt-auto">
            <div class="space-y-3">
              <!-- Row 1: Primary Action (Update Purchase Order) -->
              <button
                @click="updateOrder"
                :disabled="orderItems.length === 0 || saving || !selectedSupplier"
                class="w-full h-10 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm shadow-sm transition-all flex items-center justify-center space-x-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed border-0"
              >
                <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ saving ? 'Updating PO...' : 'Update Purchase Order' }}</span>
              </button>

              <!-- Row 2: Secondary Action (Clear All) -->
              <div class="grid grid-cols-1 gap-3">
                <button
                  @click="clearOrder"
                  :disabled="orderItems.length === 0"
                  class="w-full h-9 border border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800 text-rose-600 dark:text-rose-400 rounded-lg font-semibold text-xs transition-all flex items-center justify-center cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed bg-transparent"
                >
                  <span>Clear Items</span>
                </button>
              </div>
            </div>
        </div>
      </div>
    </div>

    <!-- Quick Supplier Creation Modal -->
    <div v-if="showSupplierModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-md overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4 transition-all duration-300">
      <div class="relative mx-auto p-6 border border-slate-100 dark:border-zinc-800 w-full max-w-2xl shadow-2xl rounded-xl bg-white dark:bg-zinc-900 text-left transition-all duration-300">
        
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
            class="flex items-center gap-1.5 px-3 py-1.5 font-bold rounded-t-lg transition-all focus:outline-none border-b-2 cursor-pointer"
            :class="activeSupplierTab === 'contact' ? 'text-indigo-600 border-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/20' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300 border-transparent bg-transparent'"
            @click="activeSupplierTab = 'contact'"
          >
            Contact
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
                  type="text"
                  required
                  placeholder="e.g. Acme Corporation"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                />
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
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Phone</label>
                <input
                  v-model="newSupplier.phone"
                  type="text"
                  placeholder="e.g. +1 555 1234"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-955 transition-all"
                />
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
              :disabled="!newSupplier.name || creatingSupplier"
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
      <div v-if="isAdvanceSearchModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-md overflow-y-auto">
        <div class="relative w-full max-w-5xl bg-white dark:bg-[#14181d] text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700/60 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
          
          <!-- Modal Header -->
          <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-800/80 bg-slate-50 dark:bg-[#161a20]">
            <h3 class="text-base font-extrabold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2">
              <span>Advanced Item Search</span>
            </h3>
            <button
              type="button"
              @click="closeAdvanceSearchModal"
              class="p-1.5 rounded-lg text-slate-400 dark:text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-800/80 transition-all cursor-pointer border-0 bg-transparent"
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
              <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-slate-400 dark:text-slate-400">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input
                v-model="advanceFilters.query"
                type="text"
                placeholder="Search by Name or Description"
                class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-[#111418] border border-slate-300 dark:border-slate-700/80 focus:border-sky-500 dark:focus:border-sky-400 focus:ring-0 focus-visible:ring-0 focus:outline-none shadow-none rounded-xl text-xs font-medium text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 transition-all"
              />
            </div>

            <!-- 2. Additional Search Criteria Section -->
            <div class="space-y-3 pt-1">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Additional Search Criteria</span>
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
                  <label class="w-32 shrink-0 text-slate-500 dark:text-slate-400 font-medium">Search by SKU</label>
                  <input
                    v-model="advanceFilters.sku"
                    type="text"
                    placeholder="Search by SKU"
                    class="flex-1 px-3 py-2 bg-slate-50 dark:bg-[#111418] border border-slate-300 dark:border-slate-700/80 focus:border-sky-500 dark:focus:border-sky-400 focus:ring-0 focus-visible:ring-0 focus:outline-none shadow-none rounded-xl text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500"
                  />
                </div>

                <!-- Search by Tags -->
                <div class="flex items-center gap-3">
                  <label class="w-32 shrink-0 text-slate-500 dark:text-slate-400 font-medium">Search by Tags</label>
                  <div class="flex-1 relative">
                    <div
                      @click="focusTagInput"
                      class="min-h-[38px] px-2.5 py-1 bg-slate-50 dark:bg-[#111418] border border-slate-300 dark:border-slate-700/80 rounded-xl flex items-center justify-between cursor-text flex-wrap gap-1 focus-within:border-sky-500 dark:focus-within:border-sky-400 focus-within:ring-0 focus-within:outline-none transition-all"
                    >
                      <div class="flex flex-wrap items-center gap-1 flex-1 min-w-0">
                        <span
                          v-for="t in advanceFilters.tags"
                          :key="t"
                          class="bg-slate-200 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-[10px] font-semibold px-2 py-0.5 rounded-md border border-slate-300 dark:border-slate-700 flex items-center gap-1 shrink-0"
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
                          class="flex-1 min-w-[80px] bg-transparent border-0 outline-none focus:outline-none focus:ring-0 focus-visible:ring-0 focus:border-transparent ring-0 shadow-none text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 py-0.5"
                          style="background: transparent !important; border: none !important; outline: none !important; box-shadow: none !important;"
                        />
                      </div>
                      <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-400 shrink-0 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </div>
                    <!-- Tag Options Menu -->
                    <div v-show="isTagDropdownOpen" class="absolute left-0 right-0 mt-1 bg-white dark:bg-[#161a20] border border-slate-200 dark:border-slate-700/90 rounded-xl shadow-2xl z-50 max-h-48 overflow-y-auto p-1 text-xs custom-scrollbar">
                      <div v-if="filteredAvailableTags.length === 0" class="px-3 py-2 text-slate-400 dark:text-slate-500 text-xs italic text-center">
                        No tags found
                      </div>
                      <div
                        v-for="(t, idx) in filteredAvailableTags"
                        :key="t"
                        @click="toggleAdvanceTag(t)"
                        @mouseenter="tagHighlightedIndex = idx"
                        class="px-2.5 py-1.5 rounded-lg cursor-pointer flex items-center justify-between transition-colors"
                        :class="[
                          advanceFilters.tags.includes(t) ? 'bg-indigo-50 dark:bg-indigo-600/25 text-indigo-600 dark:text-indigo-300 font-semibold' : 'text-slate-700 dark:text-slate-200',
                          tagHighlightedIndex === idx ? 'bg-slate-100 dark:bg-slate-800' : 'hover:bg-slate-100 dark:hover:bg-slate-800/60'
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
                  <label class="w-32 shrink-0 text-slate-500 dark:text-slate-400 font-medium">Search by Categories</label>
                  <div class="flex-1 relative">
                    <div
                      @click="focusCategoryInput"
                      class="min-h-[38px] px-2.5 py-1 bg-slate-50 dark:bg-[#111418] border border-slate-300 dark:border-slate-700/80 rounded-xl flex items-center justify-between cursor-text flex-wrap gap-1 focus-within:border-sky-500 dark:focus-within:border-sky-400 focus-within:ring-0 focus-within:outline-none transition-all"
                    >
                      <div class="flex flex-wrap items-center gap-1 flex-1 min-w-0">
                        <span
                          v-for="cId in advanceFilters.categories"
                          :key="cId"
                          class="bg-slate-200 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-[10px] font-semibold px-2 py-0.5 rounded-md border border-slate-300 dark:border-slate-700 flex items-center gap-1 shrink-0"
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
                          class="flex-1 min-w-[100px] bg-transparent border-0 outline-none focus:outline-none focus:ring-0 focus-visible:ring-0 focus:border-transparent ring-0 shadow-none text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 py-0.5"
                          style="background: transparent !important; border: none !important; outline: none !important; box-shadow: none !important;"
                        />
                      </div>
                      <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-400 shrink-0 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </div>
                    <!-- Category Options Menu -->
                    <div v-show="isCategorySelectModalOpen" class="absolute left-0 right-0 mt-1 bg-white dark:bg-[#161a20] border border-slate-200 dark:border-slate-700/90 rounded-xl shadow-2xl z-50 max-h-48 overflow-y-auto p-1 text-xs custom-scrollbar">
                      <div v-if="filteredAvailableCategories.length === 0" class="px-3 py-2 text-slate-400 dark:text-slate-500 text-xs italic text-center">
                        No categories found
                      </div>
                      <div
                        v-for="(cat, idx) in filteredAvailableCategories"
                        :key="cat.id"
                        @click="toggleAdvanceCategory(cat.id)"
                        @mouseenter="categoryHighlightedIndex = idx"
                        class="px-2.5 py-1.5 rounded-lg cursor-pointer flex items-center justify-between transition-colors"
                        :class="[
                          advanceFilters.categories.includes(cat.id) ? 'bg-indigo-50 dark:bg-indigo-600/25 text-indigo-600 dark:text-indigo-300 font-semibold' : 'text-slate-700 dark:text-slate-200',
                          categoryHighlightedIndex === idx ? 'bg-slate-100 dark:bg-slate-800' : 'hover:bg-slate-100 dark:hover:bg-slate-800/60'
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
                  <label class="w-32 shrink-0 text-slate-500 dark:text-slate-400 font-medium">Search by Price</label>
                  <div class="flex-1 flex items-center gap-3">
                    <span class="text-slate-500 dark:text-slate-500 font-medium">min</span>
                    <div class="relative w-32">
                      <span class="absolute inset-y-0 left-2.5 flex items-center text-slate-400 dark:text-slate-500 text-xs">{{ currencySymbol }}</span>
                      <input
                        v-model="advanceFilters.minPrice"
                        type="number"
                        placeholder="0"
                        class="w-full pl-6 pr-2 py-1.5 bg-slate-50 dark:bg-[#111418] border border-slate-300 dark:border-slate-700/80 rounded-xl text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-sky-500 dark:focus:border-sky-400 focus:ring-0 focus-visible:ring-0 shadow-none"
                      />
                    </div>
                    <span class="text-slate-500 dark:text-slate-500 font-medium">- max</span>
                    <div class="relative w-32">
                      <span class="absolute inset-y-0 left-2.5 flex items-center text-slate-400 dark:text-slate-500 text-xs">{{ currencySymbol }}</span>
                      <input
                        v-model="advanceFilters.maxPrice"
                        type="number"
                        placeholder="9999"
                        class="w-full pl-6 pr-2 py-1.5 bg-slate-50 dark:bg-[#111418] border border-slate-300 dark:border-slate-700/80 rounded-xl text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-sky-500 dark:focus:border-sky-400 focus:ring-0 focus-visible:ring-0 shadow-none"
                      />
                    </div>
                  </div>
                </div>

              </div>
            </div>

            <!-- 3. Search Results Table -->
            <div class="border border-slate-200 dark:border-slate-800/80 rounded-xl overflow-hidden bg-white dark:bg-[#111418]">
              <div class="max-h-64 overflow-y-auto custom-scrollbar">
                <table class="w-full text-xs text-left">
                  <thead class="bg-slate-100 dark:bg-[#181d23] text-slate-500 dark:text-slate-400 font-extrabold uppercase text-[10px] tracking-wider sticky top-0 z-10 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                      <th class="py-2.5 px-3">SKU</th>
                      <th class="py-2.5 px-3">Item Details / Description</th>
                      <th class="py-2.5 px-3">Category</th>
                      <th class="py-2.5 px-3">Tags</th>
                      <th class="py-2.5 px-3 text-right">Cost / Price</th>
                      <th class="py-2.5 px-3 text-center">Action</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 text-slate-800 dark:text-slate-200">
                    <tr v-if="!hasActiveAdvanceFilters">
                      <td colspan="6" class="py-12 text-center text-slate-400 dark:text-slate-500 italic">
                        <svg class="mx-auto h-7 w-7 text-slate-400 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Start typing in search box or select a filter criteria above to search items...</span>
                      </td>
                    </tr>
                    <tr v-else-if="advanceFilteredProducts.length === 0">
                      <td colspan="6" class="py-10 text-center text-slate-400 dark:text-slate-500 italic">
                        No products match the selected advance search criteria.
                      </td>
                    </tr>
                    <tr
                      v-for="product in advanceFilteredProducts.slice(0, 100)"
                      :key="product.id"
                      class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                    >
                      <td class="py-2.5 px-3 font-mono text-[11px] text-slate-500 dark:text-slate-400">{{ product.sku }}</td>
                      <td class="py-2.5 px-3 font-bold text-slate-900 dark:text-slate-100">{{ product.name }}</td>
                      <td class="py-2.5 px-3 text-slate-600 dark:text-slate-300">{{ getCategoryNameById(product.category_id) }}</td>
                      <td class="py-2.5 px-3 text-slate-500 dark:text-slate-400">
                        <span v-if="product.tags && product.tags.length">{{ Array.isArray(product.tags) ? product.tags.join(', ') : product.tags }}</span>
                        <span v-else class="text-slate-400 dark:text-slate-600">—</span>
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
              <div v-if="hasActiveAdvanceFilters" class="px-4 py-2 bg-slate-50 dark:bg-[#181d23] border-t border-slate-200 dark:border-slate-800 text-[10px] text-slate-500 dark:text-slate-400 font-semibold flex items-center justify-between">
                <span>Showing {{ Math.min(advanceFilteredProducts.length, 100) }} of {{ advanceFilteredProducts.length }} items</span>
                <span class="text-slate-400 dark:text-slate-500">Click "+ Add" to append items directly to purchase order</span>
              </div>
              <div v-else class="px-4 py-2 bg-slate-50 dark:bg-[#181d23] border-t border-slate-200 dark:border-slate-800 text-[10px] text-slate-400 dark:text-slate-500 font-semibold text-center">
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useCurrencyStore } from '@/stores/currency';
import api from '@/services/api';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const currencyStore = useCurrencyStore();
const activeCompany = ref(null);

const currencySymbol = computed(() => {
  return currencyStore.symbol || activeCompany.value?.currency_symbol || activeCompany.value?.currency || activeCompany.value?.base_currency || 'PKR';
});

const formatCurrency = (amount, decimals = 2) => {
  const num = parseFloat(amount) || 0;
  return `${currencySymbol.value}${num.toFixed(decimals)}`;
};

// Accent colors
const accentColor = ref('#4f46e5');

// Reactive data
const purchaseOrder = ref(null);
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
const loading = ref(true);
const loadingProducts = ref(false);
const saving = ref(false);
const creatingSupplier = ref(false);
const showSupplierModal = ref(false);
const error = ref(null);
const notifications = ref([]);

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

const taxSearchQuery = ref('');
const taxHighlightedIndex = ref(0);
const taxInputRef = ref(null);

const orderForm = ref({
  supplier_id: '',
  po_number: '',
  order_date: '',
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
  terms_and_conditions: ''
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

// Advance Search Helpers
const openAdvanceSearchModal = () => {
  isAdvanceSearchModalOpen.value = true;
};

const closeAdvanceSearchModal = () => {
  isAdvanceSearchModalOpen.value = false;
  isTagDropdownOpen.value = false;
  isCategorySelectModalOpen.value = false;
  isTaxDropdownOpen.value = false;
  tagSearchQuery.value = '';
  categorySearchQuery.value = '';
  taxSearchQuery.value = '';
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
  taxSearchQuery.value = '';
};

const hasActiveAdvanceFilters = computed(() => {
  const f = advanceFilters.value;
  return !!(
    (f.query && f.query.trim()) ||
    (f.sku && f.sku.trim()) ||
    f.categories.length > 0 ||
    f.tags.length > 0 ||
    f.taxes.length > 0 ||
    (f.minPrice !== null && f.minPrice !== '' && !isNaN(f.minPrice)) ||
    (f.maxPrice !== null && f.maxPrice !== '' && !isNaN(f.maxPrice))
  );
});

const availableTags = computed(() => {
  const set = new Set();
  products.value.forEach(p => {
    if (Array.isArray(p.tags)) {
      p.tags.forEach(t => set.add(t));
    }
  });
  if (set.size === 0) {
    ['Apple', 'New', 'Featured', 'Best Seller', 'Sale', 'Trending', 'Clearance'].forEach(t => set.add(t));
  }
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

const filteredAvailableTaxes = computed(() => {
  const query = taxSearchQuery.value.trim().toLowerCase();
  if (!query) return taxes.value;
  return taxes.value.filter(t => 
    (t.name && t.name.toLowerCase().includes(query)) ||
    String(t.value).includes(query)
  );
});

const getCategoryNameById = (id) => {
  const cat = categories.value.find(c => String(c.id) === String(id));
  return cat ? cat.name : id;
};

const focusTagInput = () => {
  if (tagInputRef.value) tagInputRef.value.focus();
  openTagDropdown();
};

const openTagDropdown = () => {
  isTagDropdownOpen.value = true;
  isCategorySelectModalOpen.value = false;
  isTaxDropdownOpen.value = false;
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
  isTaxDropdownOpen.value = false;
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
      (p.sku && p.sku.toLowerCase().includes(q))
    );
  }

  if (f.sku && f.sku.trim()) {
    const s = f.sku.trim().toLowerCase();
    list = list.filter(p => p.sku && p.sku.toLowerCase().includes(s));
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

  if (f.minPrice !== null && f.minPrice !== '' && !isNaN(f.minPrice)) {
    list = list.filter(p => (p.cost_price || p.selling_price || p.price || 0) >= parseFloat(f.minPrice));
  }

  if (f.maxPrice !== null && f.maxPrice !== '' && !isNaN(f.maxPrice)) {
    list = list.filter(p => (p.cost_price || p.selling_price || p.price || 0) <= parseFloat(f.maxPrice));
  }

  return list;
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
  return Math.max(0, sub + calculatedManualTax.value + shipping - calculatedManualDiscount.value);
});

const orderTotal = computed(() => {
  return grandTotal.value;
});

const dueAmount = computed(() => {
  const total = grandTotal.value || 0;
  const paid = parseFloat(orderForm.value.amount_paid) || 0;
  return Math.max(0, total - paid);
});

const advanceToApply = computed(() => {
  if (!useAdvanceBalance.value || !selectedSupplier.value) return 0;
  const advanceBal = parseFloat(selectedSupplier.value.advance_balance || 0);
  return Math.min(advanceBal, Math.max(0, grandTotal.value));
});

const effectiveDueAmount = computed(() => {
  const baseDue = dueAmount.value;
  if (useAdvanceBalance.value) {
    return Math.max(0, baseDue - advanceToApply.value);
  }
  return baseDue;
});

watch(grandTotal, (newGrandTotal) => {
  orderForm.value.amount_paid = parseFloat(newGrandTotal.toFixed(2));
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

const getProductStock = (product) => {
  if (!product) return 0;
  return product.stock_quantity ?? product.quantity ?? 0;
};

const fetchPurchaseOrder = async () => {
  try {
    loading.value = true;
    const response = await api.get(`/purchase-orders/${route.params.id}`);
    purchaseOrder.value = response.data.data || response.data;

    const po = purchaseOrder.value;

    // Populate form data
    orderForm.value = {
      supplier_id: po.supplier_id,
      po_number: po.po_number,
      order_date: po.order_date ? po.order_date.split('T')[0] : '',
      expected_delivery_date: po.expected_delivery_date ? po.expected_delivery_date.split('T')[0] : '',
      status: po.status,
      tax_amount: parseFloat(po.tax_amount) || 0,
      shipping_cost: parseFloat(po.shipping_cost) || 0,
      amount_paid: parseFloat(po.amount_paid) || 0,
      notes: po.notes || '',
      terms_and_conditions: po.terms_and_conditions || 'Standard purchase order conditions apply.'
    };

    // Set selected supplier
    if (po.supplier) {
      selectedSupplier.value = po.supplier;
      supplierSearch.value = po.supplier.name || '';
    }

    // Populate order items
    const rawItems = po.purchase_order_items || po.items || [];
    if (Array.isArray(rawItems)) {
      orderItems.value = rawItems.map(item => ({
        product: item.product || { id: item.product_id, name: item.product_name || 'Product', sku: item.product_sku || '' },
        product_id: item.product_id,
        quantity_ordered: parseFloat(item.quantity_ordered) || 1,
        unit_cost: parseFloat(item.unit_cost) || 0,
        total_cost: parseFloat(item.total_cost) || (parseFloat(item.quantity_ordered || 1) * parseFloat(item.unit_cost || 0)),
        notes: item.notes || ''
      }));
    }

  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load purchase order';
  } finally {
    loading.value = false;
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
    updateItemTotal(orderItems.value.indexOf(existingItem));
  } else {
    orderItems.value.push({
      product: product,
      product_id: product.id,
      quantity_ordered: 1,
      unit_cost: parseFloat(product.cost_price || product.selling_price || 0),
      total_cost: parseFloat(product.cost_price || product.selling_price || 0),
      notes: ''
    });
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

const selectSupplier = (supplier) => {
  selectedSupplier.value = supplier;
  orderForm.value.supplier_id = supplier.id;
  supplierSearch.value = supplier.name;
  supplierSearchResults.value = [];
  useAdvanceBalance.value = false;
  // Fetch fresh advance_balance from API
  api.get(`/suppliers/${supplier.id}`).then(res => {
    if (res.data && res.data.advance_balance !== undefined) {
      selectedSupplier.value = { ...selectedSupplier.value, advance_balance: res.data.advance_balance };
    }
  }).catch(() => {});
};

const clearSupplier = () => {
  selectedSupplier.value = null;
  orderForm.value.supplier_id = '';
  supplierSearch.value = '';
  supplierSearchResults.value = [];
  useAdvanceBalance.value = false;
};

const createSupplier = async () => {
  if (!newSupplier.value.name) {
    showNotification('Supplier name is required', 'error');
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
    showNotification('Error creating supplier', 'error');
    console.error('Error:', error);
  } finally {
    creatingSupplier.value = false;
  }
};

const closeSupplierModal = () => {
  showSupplierModal.value = false;
  activeSupplierTab.value = 'basic';
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

const updateOrder = async () => {
  if (!selectedSupplier.value) {
    showNotification('Please select a supplier', 'error');
    return;
  }

  if (orderItems.value.length === 0) {
    showNotification('Please add at least one item', 'error');
    return;
  }

  saving.value = true;

  try {
    const orderData = {
      supplier_id: orderForm.value.supplier_id,
      po_number: orderForm.value.po_number || purchaseOrder.value?.po_number || null,
      order_date: orderForm.value.order_date,
      expected_delivery_date: orderForm.value.expected_delivery_date || null,
      status: orderForm.value.status,
      tax_amount: orderForm.value.tax_amount || 0,
      shipping_cost: orderForm.value.shipping_cost || 0,
      amount_paid: orderForm.value.amount_paid || 0,
      use_advance_balance: useAdvanceBalance.value,
      advance_applied: advanceToApply.value,
      notes: orderForm.value.notes || null,
      terms_and_conditions: orderForm.value.terms_and_conditions || null,
      items: orderItems.value.map(item => ({
        product_id: item.product_id,
        quantity_ordered: item.quantity_ordered,
        unit_cost: item.unit_cost,
        notes: item.notes || null
      }))
    };

    await api.put(`/purchase-orders/${route.params.id}`, orderData);

    showNotification('Purchase order updated successfully', 'success');

    setTimeout(() => {
      router.push('/purchase/orders');
    }, 1500);

  } catch (error) {
    showNotification(error.response?.data?.message || 'Error updating purchase order', 'error');
    console.error('Error:', error);
  } finally {
    saving.value = false;
  }
};

const clearOrder = () => {
  if (confirm('Are you sure you want to clear all purchase order items?')) {
    orderItems.value = [];
    orderForm.value.tax_amount = 0;
    orderForm.value.shipping_cost = 0;
    orderForm.value.amount_paid = 0;
    orderForm.value.notes = '';
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

// Lifecycle
onMounted(() => {
  updateDateTime();
  setInterval(updateDateTime, 1000);
  fetchActiveCompany();
  fetchPurchaseOrder();
  loadProducts();
  loadCategories();
  loadSuppliers();
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
