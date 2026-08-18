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
            <ProductSearch priceType="purchase" :products="products" :categories="categories" :taxes="taxes" :currencySymbol="currencySymbol" :targetWarehouseId="selectedGlobalWarehouseIds.length > 0 ? selectedGlobalWarehouseIds[0] : null" @product-selected="onProductSelected" @products-fetched="onProductsFetched" />
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
                          <div class="font-bold text-slate-800 dark:text-zinc-100 text-sm mb-0.5 flex items-center gap-2">
                            <span>{{ item.product ? item.product.name : 'Product' }}</span>
                            <span
                              v-if="item.product && (item.product.brand_name || (typeof item.product.brand === 'string' ? item.product.brand : item.product.brand?.name))"
                              class="inline-block px-1.5 py-0.5 text-[9px] font-bold tracking-wide uppercase border border-slate-300 dark:border-zinc-700 text-slate-600 dark:text-zinc-400 rounded bg-slate-50 dark:bg-zinc-800/80 shrink-0 leading-none"
                            >
                              {{ item.product.brand_name || (typeof item.product.brand === 'string' ? item.product.brand : item.product.brand.name) }}
                            </span>
                          </div>
                          <div class="text-[10px] text-slate-500 dark:text-zinc-400 font-mono">
                            <span class="whitespace-nowrap">SKU: {{ item.product ? item.product.sku : '' }}</span>
                          </div>
                          <div
                            v-if="item.product && (item.product.category_path || (typeof item.product.category === 'string' ? item.product.category : item.product.category?.name))"
                            class="text-[9.5px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider truncate mt-0.5"
                          >
                            {{ item.product.category_path || (typeof item.product.category === 'string' ? item.product.category : item.product.category.name) }}
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
                            @input="onUnitCostInput(index)"
                          />
                          <div class="text-[9.5px] font-bold mt-1 tracking-tight flex items-center justify-end gap-1 flex-wrap">
                            <span class="text-slate-400 dark:text-zinc-500 whitespace-nowrap">
                              Cost: {{ currencySymbol }}{{ getItemOriginalCost(item).toFixed(2) }}
                            </span>
                            <span class="text-slate-500 dark:text-zinc-400 whitespace-nowrap">
                              Sale: {{ currencySymbol }}{{ getItemSellingPrice(item).toFixed(2) }}
                            </span>
                            <span v-if="getItemWholesalePrice(item) > 0" class="text-indigo-600 dark:text-indigo-400 whitespace-nowrap">
                              WS: {{ currencySymbol }}{{ getItemWholesalePrice(item).toFixed(2) }}
                            </span>
                            <span 
                              v-if="Math.abs(getItemCostDiff(item)) > 0.001"
                              :class="[
                                getItemCostDiff(item) > 0 
                                  ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' 
                                  : 'text-amber-600 bg-amber-50 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800',
                                'px-1 py-0.2 text-[8.5px] font-extrabold rounded-md whitespace-nowrap'
                              ]"
                            >
                              {{ getItemCostDiff(item) > 0 ? '+' : '' }}{{ currencySymbol }}{{ getItemCostDiff(item).toFixed(2) }}
                            </span>
                          </div>
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

                  <!-- 2. Additional Fee / Shipping -->
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

                  <!-- Auto Applied Required Taxes (With Professional Override Toggle) -->
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
                                'pointer-events-none inline-flex items-center justify-center h-3 w-3 rounded-full bg-white shadow-xs ring-0 transition-transform duration-200 ease-in-out transform',
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

                  <!-- 6. Payment Details & Split Multi-Payment Methods -->
                  <tr class="bg-slate-50/90 dark:bg-zinc-900/60 border-b border-slate-200 dark:border-zinc-800">
                    <td colspan="5" class="p-4">
                      <div class="grid grid-cols-1 md:grid-cols-12 gap-4 text-left">
                        <!-- Left Col: Multi-Select Payment Method Dropdowns -->
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

                          <!-- Row 2: Sub-dropdown for Card Accounts Selection (ONLY for Card) -->
                          <div v-if="selectedPaymentMethods.includes('card')" class="relative w-full" id="card-dropdown-container">
                            <button
                              type="button"
                              @click.stop="isCardDropdownOpen = !isCardDropdownOpen"
                              class="w-full px-3 border border-slate-300 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer text-left flex justify-between items-center text-xs font-bold shadow-xs hover:border-slate-400 dark:hover:border-zinc-600 transition-all select-none h-10 min-w-0"
                              :title="getSelectedCardsLabel()"
                            >
                              <span class="truncate min-w-0 pr-1 flex-1">{{ getSelectedCardsLabel() }}</span>
                              <svg class="h-4 w-4 text-slate-400 shrink-0 transition-transform duration-200 ml-1" :class="{ 'rotate-180': isCardDropdownOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                              </svg>
                            </button>

                            <!-- Card Accounts Dropup List -->
                            <div
                              v-if="isCardDropdownOpen"
                              class="absolute bottom-full mb-2 left-0 w-full bg-white dark:bg-zinc-900 shadow-2xl rounded-xl border border-slate-200 dark:border-zinc-700/80 py-1 text-xs overflow-hidden z-[9999]"
                            >
                              <div class="px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-zinc-400 border-b border-slate-100 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-950 flex justify-between items-center select-none">
                                <span>Select Card Account(s)</span>
                                <span class="text-indigo-600 dark:text-indigo-400 font-black">
                                  {{ selectedCardIds.length }} Selected
                                </span>
                              </div>
                              <div v-if="activeCardAccounts.length === 0" class="px-3 py-3 text-slate-400 text-center text-xs">
                                No card accounts available
                              </div>
                              <div
                                v-for="card in activeCardAccounts"
                                :key="card.id"
                                @click.stop="isBankAccountInactive(card) ? null : toggleCardSelection(card.id)"
                                class="px-3 py-2.5 flex items-center justify-between transition-colors border-b border-slate-100 dark:border-zinc-800/60 last:border-0 select-none"
                                :class="[
                                  isBankAccountInactive(card)
                                    ? 'opacity-50 cursor-not-allowed bg-slate-100/60 dark:bg-zinc-800/40 text-slate-400 dark:text-zinc-500'
                                    : (selectedCardIds.includes(card.id)
                                        ? 'bg-indigo-50/80 dark:bg-zinc-800 text-indigo-700 dark:text-indigo-400 font-extrabold cursor-pointer'
                                        : 'bg-white dark:bg-zinc-900 text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800/60 cursor-pointer')
                                ]"
                              >
                                <div class="flex items-center gap-2.5 truncate min-w-0">
                                  <input
                                    type="checkbox"
                                    :checked="selectedCardIds.includes(card.id)"
                                    :disabled="isBankAccountInactive(card)"
                                    class="w-4 h-4 rounded border-slate-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500 cursor-pointer pointer-events-none shrink-0"
                                  />
                                  <div class="truncate min-w-0">
                                    <span class="truncate font-semibold block">
                                      {{ formatBankAccountLabel(card) }}
                                      <span v-if="isBankAccountInactive(card)" class="text-rose-500 font-bold ml-1 text-[11px]">(Inactive)</span>
                                    </span>
                                    <span class="text-[10px] text-slate-400 block font-normal truncate">{{ card.bank_name || card.account_name }}{{ (card.masked_account_number || getMaskedAccountNumber(card.account_number)) ? ' ' + (card.masked_account_number || getMaskedAccountNumber(card.account_number)) : '' }}</span>
                                  </div>
                                </div>
                                <span v-if="isBankAccountInactive(card)" class="text-[9px] font-black uppercase tracking-wider text-slate-500 bg-slate-200 dark:bg-zinc-800 px-2 py-0.5 rounded-md border border-slate-300 dark:border-zinc-700 shrink-0 ml-2">
                                  Inactive
                                </span>
                                <span v-else-if="selectedCardIds.includes(card.id)" class="text-[9px] font-black uppercase tracking-wider text-indigo-700 dark:text-indigo-300 bg-indigo-100/80 dark:bg-zinc-950 px-2 py-0.5 rounded-md border border-indigo-300 dark:border-indigo-500/40 shadow-2xs shrink-0 ml-2">
                                  Selected
                                </span>
                              </div>
                            </div>
                          </div>

                          <!-- Row 3: Sub-dropdown for Bank Accounts Selection (ONLY for Bank Transfer) -->
                          <div v-if="selectedPaymentMethods.includes('bank_transfer')" class="relative w-full" id="bank-dropdown-container">
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

                            <!-- Card Amount Inputs -->
                            <template v-if="selectedPaymentMethods.includes('card')">
                              <div
                                v-for="cardId in selectedCardIds"
                                :key="cardId"
                                class="space-y-1"
                              >
                                <div
                                  class="flex items-center justify-between gap-3 h-10 bg-white dark:bg-zinc-900 px-3.5 rounded-xl border shadow-2xs shrink-0 transition-colors"
                                  :class="isBankBalanceExceeded(cardId) ? 'border-rose-500 ring-1 ring-rose-500/30 dark:border-rose-500' : 'border-slate-200 dark:border-zinc-750'"
                                >
                                  <label class="text-xs font-bold text-slate-700 dark:text-zinc-300 truncate min-w-0 flex-1 text-left flex items-center gap-1.5" :title="formatBankAccountLabel(allAccounts.find(b => b.id == cardId))">
                                    <span>{{ formatBankAccountLabel(allAccounts.find(b => b.id == cardId)) }}</span>
                                    <span v-if="isBankAccountInactive(cardId)" class="px-1.5 py-0.5 text-[9px] font-bold uppercase rounded bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
                                      (Inactive)
                                    </span>
                                  </label>
                                  <div class="relative w-24 shrink-0">
                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">{{ currencySymbol }}</span>
                                    <input
                                      v-model.number="cardPaymentAmounts[cardId]"
                                      type="number"
                                      step="0.01"
                                      min="0"
                                      :disabled="isBankAccountInactive(cardId)"
                                      :readonly="isBankAccountInactive(cardId)"
                                      :tabindex="isBankAccountInactive(cardId) ? -1 : 0"
                                      class="w-full pl-6 pr-2 py-1 text-right border border-slate-300 dark:border-zinc-700 rounded-lg text-xs font-black text-emerald-600 dark:text-emerald-400 bg-slate-50/50 dark:bg-zinc-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 h-7 disabled:opacity-60 disabled:bg-slate-200/50 dark:disabled:bg-zinc-800/50 disabled:cursor-not-allowed disabled:text-slate-400 select-none"
                                      :class="{ 'border-rose-500 text-rose-600 dark:text-rose-400 focus:ring-rose-500': isBankBalanceExceeded(cardId) }"
                                    />
                                  </div>
                                </div>
                                <!-- Available Balance & Insufficient Error Message -->
                                <div class="text-[11px] font-semibold text-left px-1">
                                  <span :class="isBankBalanceExceeded(cardId) ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-slate-500 dark:text-zinc-400'">
                                    Available Balance: {{ currencySymbol }}{{ getBankAccountBalance(cardId).toFixed(2) }}
                                  </span>
                                  <div v-if="isBankBalanceExceeded(cardId)" class="text-rose-600 dark:text-rose-400 font-extrabold text-[11px] mt-0.5 animate-pulse flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>Insufficient balance! Available: {{ currencySymbol }}{{ getBankAccountBalance(cardId).toFixed(2) }}, Attempted: {{ currencySymbol }}{{ (cardPaymentAmounts[cardId] || 0).toFixed(2) }}</span>
                                  </div>
                                </div>
                              </div>
                            </template>

                            <!-- Bank Amount Inputs -->
                            <template v-if="selectedPaymentMethods.includes('bank_transfer')">
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
                          <div class="flex items-center space-x-2.5 min-w-0 flex-1">
                            <div class="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 font-bold text-xs flex items-center justify-center shrink-0">
                              {{ supplier.name ? supplier.name.charAt(0).toUpperCase() : 'S' }}
                            </div>
                            <div class="min-w-0 flex-1">
                              <span class="font-bold text-slate-800 dark:text-zinc-200 text-xs truncate block">
                                {{ [supplier.name, supplier.phone, supplier.city].filter(Boolean).join(' • ') }}
                              </span>
                              <p v-if="supplier.company_name || supplier.email" class="text-[10px] text-slate-400 dark:text-zinc-500 truncate mt-0.5">
                                {{ [supplier.company_name, supplier.email].filter(Boolean).join(' | ') }}
                              </p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Selected Supplier Details Card -->
                    <div v-if="selectedSupplier" class="p-3 bg-emerald-50/40 dark:bg-emerald-950/20 rounded-xl border border-emerald-200/80 dark:border-emerald-900/40 text-xs space-y-1 relative w-full text-left transition-all">
                      <button @click="clearSupplier" class="absolute top-2.5 right-2.5 text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-350 font-bold text-[10px] flex items-center gap-0.5 transition-colors border-0 bg-transparent cursor-pointer z-10">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Remove
                      </button>
                      <div class="flex items-start space-x-2.5 pr-14">
                        <div class="w-7 h-7 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-sm mt-0.5">
                          {{ selectedSupplier.name ? selectedSupplier.name.charAt(0).toUpperCase() : 'S' }}
                        </div>
                        <div class="min-w-0 flex-1">
                          <p class="font-bold text-slate-800 dark:text-zinc-100 text-xs truncate">
                            {{ selectedSupplier.name }}
                          </p>
                          <p v-if="selectedSupplier.phone || selectedSupplier.city" class="text-[11px] text-slate-500 dark:text-zinc-400 font-medium truncate mt-0.5 flex items-center gap-1.5 flex-wrap">
                            <span v-if="selectedSupplier.phone" class="inline-flex items-center gap-0.5">
                              📞 {{ selectedSupplier.phone }}
                            </span>
                            <span v-if="selectedSupplier.phone && selectedSupplier.city" class="text-slate-300 dark:text-zinc-600">•</span>
                            <span v-if="selectedSupplier.city" class="inline-flex items-center gap-0.5">
                              📍 {{ selectedSupplier.city }}
                            </span>
                          </p>
                          <p v-else-if="selectedSupplier.email" class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium truncate mt-0.5">
                            {{ selectedSupplier.email }}
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
              <!-- Row 1: Primary Action (Update Purchase Order) -->
              <button
                @click="updateOrder"
                :disabled="orderItems.length === 0 || saving || !selectedSupplier || isEditPaymentBalanceExceeded"
                class="w-full h-10 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm shadow-sm transition-all flex items-center justify-center space-x-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed border-0"
                :title="isEditPaymentBalanceExceeded ? 'Cannot update: Insufficient balance in selected payment account' : ''"
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
                  class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
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

    
  </div>

  <!-- COST OVERRUN WARNING & SELLING PRICE ADJUSTMENT MODAL -->
    <div
      v-if="isCostOverrunModalOpen && activeOverrunItem"
      class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-900/60 dark:bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in duration-200"
    >
      <div class="relative bg-white dark:bg-zinc-900 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-amber-200 dark:border-amber-900/50 space-y-4 font-sans">
        <!-- Close Button -->
        <button
          type="button"
          @click="cancelCostOverrun"
          class="absolute top-4 right-4 text-slate-400 dark:text-zinc-500 hover:text-slate-600 dark:hover:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 p-1.5 rounded-lg transition-all bg-transparent border-0 cursor-pointer"
          title="Cancel"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <!-- Header -->
        <div class="flex items-start gap-3">
          <div class="p-2.5 bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-xl shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Cost Increase / Overrun Warning</h3>
            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
              The entered purchase unit cost ({{ currencySymbol }}{{ activeOverrunItem.unit_cost.toFixed(2) }}) for <strong class="text-slate-800 dark:text-zinc-200">{{ activeOverrunItem.product.name }}</strong> exceeds the current cost or selling prices.
            </p>
          </div>
        </div>

        <!-- Warning Info Card -->
        <div class="p-3 bg-amber-50 dark:bg-amber-950/30 rounded-xl border border-amber-200/80 dark:border-amber-900/40 text-xs space-y-1">
          <p class="font-bold text-amber-800 dark:text-amber-300">
            Purchase Cost: {{ currencySymbol }}{{ activeOverrunItem.unit_cost.toFixed(2) }}
          </p>
          <p v-if="activeOverrunItem.current_cost > 0 && activeOverrunItem.unit_cost > activeOverrunItem.current_cost" class="text-amber-700 dark:text-amber-400 font-semibold">
            • Exceeds Previous Cost ({{ currencySymbol }}{{ activeOverrunItem.current_cost.toFixed(2) }}) by {{ currencySymbol }}{{ (activeOverrunItem.unit_cost - activeOverrunItem.current_cost).toFixed(2) }}
          </p>
          <p v-if="activeOverrunItem.current_sale_price > 0 && activeOverrunItem.unit_cost > activeOverrunItem.current_sale_price" class="text-rose-600 dark:text-rose-400 font-semibold">
            • Exceeds Sale Price ({{ currencySymbol }}{{ activeOverrunItem.current_sale_price.toFixed(2) }}) by {{ currencySymbol }}{{ (activeOverrunItem.unit_cost - activeOverrunItem.current_sale_price).toFixed(2) }}
          </p>
          <p v-if="activeOverrunItem.current_wholesale_price > 0 && activeOverrunItem.unit_cost > activeOverrunItem.current_wholesale_price" class="text-amber-700 dark:text-amber-400 font-semibold">
            • Exceeds Wholesale Price ({{ currencySymbol }}{{ activeOverrunItem.current_wholesale_price.toFixed(2) }}) by {{ currencySymbol }}{{ (activeOverrunItem.unit_cost - activeOverrunItem.current_wholesale_price).toFixed(2) }}
          </p>
        </div>

        <!-- Editable Selling Prices -->
        <div class="space-y-3 pt-1">
          <div>
            <label class="block text-[11px] font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1">
              New Sale Price ({{ currencySymbol }}) *
            </label>
            <input
              v-model="activeOverrunItem.new_sale_price"
              type="number"
              step="0.01"
              min="0"
              class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-bold bg-white dark:bg-zinc-950 text-slate-900 dark:text-zinc-100 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"
            />
          </div>

          <div v-if="activeOverrunItem.has_wholesale">
            <label class="block text-[11px] font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1">
              New Wholesale Price ({{ currencySymbol }})
            </label>
            <input
              v-model="activeOverrunItem.new_wholesale_price"
              type="number"
              step="0.01"
              min="0"
              class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-bold bg-white dark:bg-zinc-950 text-slate-900 dark:text-zinc-100 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"
            />
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-zinc-800">
          <button
            type="button"
            @click="cancelCostOverrun"
            class="px-4 py-2 bg-rose-50 dark:bg-rose-950/30 hover:bg-rose-100 dark:hover:bg-rose-900/50 text-rose-700 dark:text-rose-300 rounded-xl text-xs font-bold transition-all cursor-pointer border border-rose-200 dark:border-rose-800/60"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="isCostOverrunModalOpen = false"
            class="px-4 py-2 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-300 rounded-xl text-xs font-bold transition-all cursor-pointer border-0"
          >
            Keep Current Prices
          </button>
          <button
            type="button"
            @click="applyCostOverrunAdjustment"
            :disabled="savingOverrun"
            class="px-5 py-2 bg-amber-600 hover:bg-amber-700 active:scale-[0.98] text-white rounded-xl text-xs font-bold shadow-md transition-all cursor-pointer inline-flex items-center gap-2 disabled:opacity-50 border-0"
          >
            <div v-if="savingOverrun" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-current"></div>
            <span>{{ savingOverrun ? 'Updating...' : 'Update & Apply' }}</span>
          </button>
        </div>
      </div>
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
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useCurrencyStore } from '@/stores/currency';
import api from '@/services/api';
import ProductSearch from '@/components/shared/ProductSearch.vue';

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

const isPaymentDropdownOpen = ref(false);

const paymentMethodsList = [
  { value: 'cash', label: 'Cash' },
  { value: 'card', label: 'Card' },
  { value: 'bank_transfer', label: 'Bank Transfer' },
  { value: 'mobile_payment', label: 'Mobile Payment' },
  { value: 'mixed', label: 'Mixed' }
];

const getSelectedWarehouseName = (product, id) => {
  if (!id) return 'Select Warehouse';
  const wh = warehouses.value.find(w => w.id == id);
  return wh ? wh.name : 'Select Warehouse';
};

const getSelectedPaymentMethodLabel = (val) => {
  const found = paymentMethodsList.find(p => p.value === val);
  return found ? found.label : (val || 'Cash');
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

const taxSearchQuery = ref('');
const taxHighlightedIndex = ref(0);
const taxInputRef = ref(null);

const isManualPoNumber = ref(false);
const originalPoNumber = ref('');

const canEditPoNumber = computed(() => {
  return authStore.hasPermission('edit_po_number') ||
    authStore.hasPermission('purchases.edit_po_number') ||
    authStore.hasRole('admin') ||
    authStore.hasRole('owner');
});

const toggleManualPoNumber = () => {
  if (!isManualPoNumber.value) {
    orderForm.value.po_number = originalPoNumber.value;
  }
};

const orderForm = ref({
  supplier_id: '',
  is_walkin_supplier: false,
  supplier_name: '',
  supplier_phone: '',
  supplier_email: '',
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

// Multi-Payment Split State
const selectedPaymentMethods = ref(['cash']);
const isBankDropdownOpen = ref(false);
const selectedBankIds = ref([]);
const paymentAmounts = ref({ cash: 0 });
const bankPaymentAmounts = ref({});

const availablePaymentMethods = [
  { id: 'cash', label: 'Cash' },
  { id: 'card', label: 'Card' },
  { id: 'bank_transfer', label: 'Bank Transfer' }
];

const togglePaymentMethod = (methodId) => {
  if (selectedPaymentMethods.value.includes(methodId)) {
    if (selectedPaymentMethods.value.length === 1) return;
    selectedPaymentMethods.value = selectedPaymentMethods.value.filter(m => m !== methodId);
    if (methodId === 'cash') {
      paymentAmounts.value.cash = 0;
    } else if (methodId === 'card') {
      paymentAmounts.value.card = 0;
    } else if (methodId === 'bank_transfer') {
      selectedBankIds.value = [];
      bankPaymentAmounts.value = {};
    }
  } else {
    selectedPaymentMethods.value.push(methodId);
    if (methodId === 'bank_transfer') {
      if (selectedBankIds.value.length === 0 && activeBankAccounts.value.length > 0) {
        const defaultBank = activeBankAccounts.value.find(b => b.is_default) || activeBankAccounts.value[0];
        if (defaultBank && !isBankAccountInactive(defaultBank)) {
          selectedBankIds.value.push(defaultBank.id);
        }
      }
    } else if (methodId === 'card') {
      if (paymentAmounts.value.card === undefined) {
        paymentAmounts.value.card = 0;
      }
    }
  }
};

const toggleBankSelection = (bankId) => {
  const bank = (allAccounts.value || []).find(b => b.id == bankId);
  if (bank && isBankAccountInactive(bank)) return;

  if (selectedBankIds.value.includes(bankId)) {
    selectedBankIds.value = selectedBankIds.value.filter(id => id !== bankId);
    delete bankPaymentAmounts.value[bankId];
  } else {
    selectedBankIds.value.push(bankId);
  }
};

const getSelectedPaymentMethodsLabel = () => {
  if (selectedPaymentMethods.value.length === 0) return 'Select Payment Method(s)';
  const labels = selectedPaymentMethods.value.map(m => {
    const found = availablePaymentMethods.find(p => p.id === m);
    return found ? found.label : m;
  });
  return labels.join(', ');
};

const getSelectedBanksLabel = () => {
  const selected = (allAccounts.value || []).filter(b => selectedBankIds.value.includes(b.id));
  if (selected.length === 0) return 'Select Bank Account(s)';
  if (selected.length === 1) {
    const acc = selected[0];
    return formatBankAccountLabel(acc);
  }
  return `${selected.length} Bank Accounts Selected`;
};

const formatBankAccountLabel = (acc) => {
  if (!acc) return 'Bank Account';
  const namePart = acc.account_name || acc.bank_name || 'Bank';
  const numPart = acc.masked_account_number || getMaskedAccountNumber(acc.account_number);
  return numPart ? `${namePart} ${numPart}` : namePart;
};

const getMaskedAccountNumber = (accNum) => {
  if (!accNum) return '';
  const str = String(accNum);
  if (str.length <= 4) return `****${str}`;
  return `****${str.slice(-4)}`;
};

const activeBankAccounts = computed(() => {
  const banks = (allAccounts.value || []).filter(account => {
    const type = (account.type || account.account_type || '').toLowerCase();
    const name = (account.account_name || account.bank_name || '').toLowerCase();
    if (type === 'cash' || name.includes('cash') || name.includes('vault')) return false;
    if (isBankAccountInactive(account)) return false;
    return type !== 'credit_card' && type !== 'card';
  });
  if (banks.length > 0) return banks;
  return (allAccounts.value || []).filter(account => {
    const type = (account.type || account.account_type || '').toLowerCase();
    const name = (account.account_name || account.bank_name || '').toLowerCase();
    if (type === 'cash' || name.includes('cash') || name.includes('vault')) return false;
    if (isBankAccountInactive(account)) return false;
    return true;
  });
});

const activeCardAccounts = computed(() => {
  const cards = (allAccounts.value || []).filter(account => {
    const type = (account.type || account.account_type || '').toLowerCase();
    const name = (account.account_name || account.bank_name || '').toLowerCase();
    if (type === 'cash' || name.includes('cash') || name.includes('vault')) return false;
    if (isBankAccountInactive(account)) return false;
    return type === 'credit_card' || type === 'card' || name.includes('card') || name.includes('credit');
  });
  if (cards.length > 0) return cards;
  return (allAccounts.value || []).filter(account => {
    const type = (account.type || account.account_type || '').toLowerCase();
    const name = (account.account_name || account.bank_name || '').toLowerCase();
    if (type === 'cash' || name.includes('cash') || name.includes('vault')) return false;
    if (isBankAccountInactive(account)) return false;
    return true;
  });
});

const selectedCardIds = ref([]);
const cardPaymentAmounts = ref({});
const isCardDropdownOpen = ref(false);

const isBankAccountInactive = (bank) => {
  if (!bank) return false;
  if (typeof bank === 'object') {
    return bank.is_active === false || bank.is_active === 0 || bank.status === 'inactive' || bank.status === 0;
  }
  const found = (allAccounts.value || []).find(b => b.id == bank);
  if (found) {
    return found.is_active === false || found.is_active === 0 || found.status === 'inactive' || found.status === 0;
  }
  return false;
};

const toggleCardSelection = (cardId) => {
  const bank = (allAccounts.value || []).find(b => b.id == cardId);
  if (bank && isBankAccountInactive(bank)) return;

  if (selectedCardIds.value.includes(cardId)) {
    selectedCardIds.value = selectedCardIds.value.filter(id => id !== cardId);
    delete cardPaymentAmounts.value[cardId];
  } else {
    selectedCardIds.value.push(cardId);
  }
};

const getSelectedCardsLabel = () => {
  const selected = (allAccounts.value || []).filter(b => selectedCardIds.value.includes(b.id));
  if (selected.length === 0) return 'Select Card Account(s)';
  if (selected.length === 1) {
    const acc = selected[0];
    return formatBankAccountLabel(acc);
  }
  return `${selected.length} Card Accounts Selected`;
};

const getBankAccountBalance = (bankId) => {
  const bank = (allAccounts.value || []).find(b => b.id == bankId);
  if (!bank) return 0;
  return parseFloat(bank.current_balance !== undefined && bank.current_balance !== null ? bank.current_balance : (bank.opening_balance || 0));
};

const cashAccount = computed(() => {
  return (allAccounts.value || []).find(acc => {
    const type = (acc.type || acc.account_type || '').toLowerCase();
    const name = (acc.account_name || acc.bank_name || '').toLowerCase();
    return type === 'cash' || name.includes('cash') || name.includes('vault');
  });
});

const cashAvailableBalance = computed(() => {
  if (!cashAccount.value) return 0;
  return parseFloat(cashAccount.value.current_balance !== undefined && cashAccount.value.current_balance !== null ? cashAccount.value.current_balance : (cashAccount.value.opening_balance || 0));
});

const isCashBalanceExceeded = computed(() => {
  if (!selectedPaymentMethods.value.includes('cash')) return false;
  const cashAmt = parseFloat(paymentAmounts.value.cash) || 0;
  if (cashAmt <= 0) return false;
  return cashAmt > cashAvailableBalance.value;
});

const isBankBalanceExceeded = (bankId) => {
  if (!selectedPaymentMethods.value.includes('card') && !selectedPaymentMethods.value.includes('bank_transfer')) return false;
  const bankAmt = parseFloat(bankPaymentAmounts.value[bankId] !== undefined ? bankPaymentAmounts.value[bankId] : cardPaymentAmounts.value[bankId]) || 0;
  if (bankAmt <= 0) return false;
  const avail = getBankAccountBalance(bankId);
  return bankAmt > avail;
};

const directPaymentsSum = computed(() => {
  let sum = 0;
  if (selectedPaymentMethods.value.includes('cash')) {
    sum += parseFloat(paymentAmounts.value.cash) || 0;
  }
  if (selectedPaymentMethods.value.includes('card')) {
    selectedCardIds.value.forEach(cardId => {
      sum += parseFloat(cardPaymentAmounts.value[cardId]) || 0;
    });
  }
  if (selectedPaymentMethods.value.includes('bank_transfer')) {
    selectedBankIds.value.forEach(bankId => {
      sum += parseFloat(bankPaymentAmounts.value[bankId]) || 0;
    });
  }
  return sum;
});

const totalPaidAmount = computed(() => {
  return directPaymentsSum.value;
});

const remainingBillDue = computed(() => {
  return Math.max(0, grandTotal.value - directPaymentsSum.value);
});

const dueAmount = computed(() => {
  return remainingBillDue.value;
});

const advanceToApply = computed(() => {
  if (!useAdvanceBalance.value || !selectedSupplier.value) return 0;
  const advanceBal = parseFloat(selectedSupplier.value.advance_balance || 0);
  return Math.min(advanceBal, remainingBillDue.value);
});

const effectiveDueAmount = computed(() => {
  return Math.max(0, remainingBillDue.value - advanceToApply.value);
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

const warehouses = ref([]);
const selectedGlobalWarehouseIds = ref([]);
const isGlobalWarehouseDropdownOpen = ref(false);
const openWarehouseItemIndex = ref(null);
const warehousePopPos = ref({ top: '0px', left: '0px' });
const globalWarehouseSearch = ref('');

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

  item.allocations = item.allocations.filter(a => activeWhIds.includes(a.warehouse_id));

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
    orderItems.value.forEach(item => syncItemAllocations(item));
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

const fetchPurchaseOrder = async () => {
  try {
    loading.value = true;
    const response = await api.get(`/purchase-orders/${route.params.id}`);
    purchaseOrder.value = response.data.data || response.data;

    const po = purchaseOrder.value;

    // Populate form data
    orderForm.value = {
      supplier_id: po.supplier_id,
      is_walkin_supplier: Boolean(po.is_walkin_supplier),
      supplier_name: po.supplier_name || po.supplier?.name || '',
      supplier_phone: po.supplier_phone || po.supplier?.phone || '',
      supplier_email: po.supplier_email || po.supplier?.email || '',
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

    originalPoNumber.value = po.po_number || '';

    // Set global destination warehouse(s)
    if (Array.isArray(po.warehouse_ids) && po.warehouse_ids.length > 0) {
      selectedGlobalWarehouseIds.value = po.warehouse_ids;
    } else if (po.warehouse_id) {
      selectedGlobalWarehouseIds.value = [po.warehouse_id];
    }

    // Set selected supplier
    if (po.supplier && !po.is_walkin_supplier) {
      selectedSupplier.value = po.supplier;
      supplierSearch.value = po.supplier.name || '';
    }

    // Populate order items
    const rawItems = po.purchase_order_items || po.items || [];
    if (Array.isArray(rawItems)) {
      orderItems.value = rawItems.map(item => {
        const mappedItem = {
          product: item.product || { id: item.product_id, name: item.product_name || 'Product', sku: item.product_sku || '' },
          product_id: item.product_id,
          product_variation_id: item.product_variation_id || null,
          quantity_ordered: parseFloat(item.quantity_ordered) || 1,
          unit_cost: parseFloat(item.unit_cost) || 0,
          total_cost: parseFloat(item.total_cost) || (parseFloat(item.quantity_ordered || 1) * parseFloat(item.unit_cost || 0)),
          notes: item.notes || '',
          allocations: Array.isArray(item.warehouse_allocations) ? item.warehouse_allocations : []
        };
        syncItemAllocations(mappedItem);
        return mappedItem;
      });
    }

    // Hydrate payments & split payment methods
    const paymentsList = po.payments || [];
    if (Array.isArray(paymentsList) && paymentsList.length > 0) {
      const methods = new Set();
      const bankIds = new Set();
      const cardIds = new Set();

      paymentsList.forEach(p => {
        const method = (p.payment_method || 'cash').toLowerCase();
        const amt = parseFloat(p.amount) || 0;

        if (method === 'cash') {
          methods.add('cash');
          paymentAmounts.value.cash = amt;
        } else if (method === 'card') {
          methods.add('card');
          if (p.bank_account_id) {
            cardIds.add(p.bank_account_id);
            cardPaymentAmounts.value[p.bank_account_id] = amt;
          }
        } else if (method === 'bank_transfer') {
          methods.add('bank_transfer');
          if (p.bank_account_id) {
            bankIds.add(p.bank_account_id);
            bankPaymentAmounts.value[p.bank_account_id] = amt;
          }
        }
      });

      if (methods.size > 0) {
        selectedPaymentMethods.value = Array.from(methods);
      }
      if (bankIds.size > 0) {
        selectedBankIds.value = Array.from(bankIds);
      }
      if (cardIds.size > 0) {
        selectedCardIds.value = Array.from(cardIds);
      }
    } else {
      const amtPaid = parseFloat(po.amount_paid) || 0;
      if (amtPaid > 0) {
        selectedPaymentMethods.value = ['cash'];
        paymentAmounts.value.cash = amtPaid;
      }
    }

    // Hydrate useAdvanceBalance
    if (po.use_advance_balance || (parseFloat(po.advance_applied) || 0) > 0) {
      useAdvanceBalance.value = true;
    } else {
      useAdvanceBalance.value = false;
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
    const response = await api.get('/sales/products-with-stock');
    products.value = response.data.items || response.data.products || [];
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
  const matchId = product.product_id || product.id;
  const matchVarId = product.product_variation_id || null;

  const existingItem = orderItems.value.find(item => 
    item.product_id === matchId && 
    (item.product_variation_id || null) === matchVarId
  );

  if (existingItem) {
    existingItem.quantity_ordered += 1;
    updateItemTotal(orderItems.value.indexOf(existingItem));
  } else {
    const defaultWh = warehouses.value.find(w => w.is_default) || warehouses.value[0];
    const defaultWarehouseIds = defaultWh ? [defaultWh.id] : [];
    const newItem = {
      product: product,
      product_id: matchId,
      product_variation_id: matchVarId,
      warehouse_id: defaultWarehouseIds[0] || null,
      warehouse_ids: defaultWarehouseIds,
      warehouses: defaultWarehouseIds.map(whId => ({ warehouse_id: whId, stock: getProductWarehouseStock(product, whId) })),
      quantity_ordered: 1,
      unit_cost: parseFloat(product.cost_price || product.selling_price || 0),
      total_cost: parseFloat(product.cost_price || product.selling_price || 0),
      notes: ''
    };
    newItem.combined_stock = getItemAvailableStock(newItem);
    orderItems.value.push(newItem);
  }
};

const removeFromOrder = (index) => {
  orderItems.value.splice(index, 1);
};

const isCostOverrunModalOpen = ref(false);
const activeOverrunItem = ref(null);
const savingOverrun = ref(false);

const updateItemTotal = (index) => {
  const item = orderItems.value[index];
  const qty = parseFloat(item.quantity_ordered) || 0;
  const cost = parseFloat(item.unit_cost) || 0;
  item.total_cost = qty * cost;
};

const getItemOriginalCost = (item) => {
  const prod = item.product || {};
  const cost = (prod.purchase_price !== undefined && prod.purchase_price !== null) 
    ? prod.purchase_price 
    : (prod.cost_price ?? prod.unit_cost ?? 0);
  return parseFloat(cost) || 0;
};

const getItemSellingPrice = (item) => {
  if (!item) return 0;
  if (item.sale_price !== undefined && item.sale_price !== null) return parseFloat(item.sale_price) || 0;
  const prod = item.product || {};
  const price = prod.selling_price ?? prod.price ?? 0;
  return parseFloat(price) || 0;
};

const getItemWholesalePrice = (item) => {
  if (!item) return 0;
  if (item.wholesale_price !== undefined && item.wholesale_price !== null) return parseFloat(item.wholesale_price) || 0;
  const prod = item.product || {};
  const price = prod.wholesale_price ?? 0;
  return parseFloat(price) || 0;
};

const getItemCostDiff = (item) => {
  const currentCost = getItemOriginalCost(item);
  const enteredCost = parseFloat(item.unit_cost) || 0;
  return enteredCost - currentCost;
};

const onUnitCostInput = (index) => {
  updateItemTotal(index);
  const item = orderItems.value[index];
  if (!item) return;

  const cost = parseFloat(item.unit_cost) || 0;
  const currentCost = getItemOriginalCost(item);
  const sale = getItemSellingPrice(item);
  const wholesale = getItemWholesalePrice(item);

  const isCostIncreased = currentCost > 0 && cost > currentCost;
  const isSaleExceeded = sale > 0 && cost > sale;
  const isWholesaleExceeded = wholesale > 0 && cost > wholesale;

  if (isCostIncreased || isSaleExceeded || isWholesaleExceeded) {
    activeOverrunItem.value = {
      index,
      item,
      product: item.product || {},
      unit_cost: cost,
      current_cost: currentCost,
      current_sale_price: sale,
      current_wholesale_price: wholesale,
      new_sale_price: sale.toFixed(2),
      new_wholesale_price: wholesale.toFixed(2),
      has_wholesale: wholesale > 0 || !!(item.product && item.product.has_wholesale)
    };
    isCostOverrunModalOpen.value = true;
  }
};

const cancelCostOverrun = () => {
  if (activeOverrunItem.value && activeOverrunItem.value.item) {
    const item = activeOverrunItem.value.item;
    const origCost = getItemOriginalCost(item);
    item.unit_cost = origCost;
    updateItemTotal(activeOverrunItem.value.index);
  }
  isCostOverrunModalOpen.value = false;
  activeOverrunItem.value = null;
};

const applyCostOverrunAdjustment = async () => {
  if (!activeOverrunItem.value) return;
  savingOverrun.value = true;

  try {
    const prodId = activeOverrunItem.value.product?.id || activeOverrunItem.value.item?.product_id || activeOverrunItem.value.product?.product_id;
    const varId = activeOverrunItem.value.item?.product_variation_id || activeOverrunItem.value.product?.product_variation_id;

    const newCost = parseFloat(activeOverrunItem.value.unit_cost) || 0;
    const newSale = parseFloat(activeOverrunItem.value.new_sale_price) || 0;
    const newWholesale = parseFloat(activeOverrunItem.value.new_wholesale_price) || 0;

    const payload = {
      cost_price: newCost,
      selling_price: newSale,
      wholesale_price: newWholesale,
      product_variation_id: varId,
      purchase_order_id: purchaseOrder.value?.id || null
    };

    // 1. Send API request to update product sale price and purchase cost in backend
    await api.post(`/products/${prodId}/update-prices`, payload);

    // 2. Update local line item properties
    const item = activeOverrunItem.value.item || orderItems.value[activeOverrunItem.value.index];
    if (item) {
      item.unit_cost = newCost;
      item.sale_price = newSale;
      item.wholesale_price = newWholesale;
      if (!item.product) item.product = {};
      item.product.cost_price = newCost;
      item.product.purchase_price = newCost;
      item.product.selling_price = newSale;
      item.product.price = newSale;
      item.product.wholesale_price = newWholesale;

      if (typeof updateItemTotal === 'function') {
        updateItemTotal(activeOverrunItem.value.index);
      }
    }

    // 3. Update products list in scope
    if (typeof products !== 'undefined' && products.value) {
      products.value.forEach(p => {
        const matchId = p.product_id || p.id;
        if (matchId == prodId) {
          if (!varId || p.product_variation_id == varId) {
            p.cost_price = newCost;
            p.purchase_price = newCost;
            p.selling_price = newSale;
            p.price = newSale;
            p.wholesale_price = newWholesale;
          }
        }
      });
    }

    // 4. Close the modal and clear active overrun context
    isCostOverrunModalOpen.value = false;
    activeOverrunItem.value = null;

    // 5. Toast notification
    if (typeof showNotification === 'function') {
      showNotification('Purchase cost and sale price updated successfully.', 'success');
    }
  } catch (err) {
    console.error('Failed to update product price:', err);
    const msg = err.response?.data?.message || err.message || 'Failed to update product price. Please try again.';
    if (typeof showNotification === 'function') {
      showNotification(msg, 'error');
    }
  } finally {
    savingOverrun.value = false;
  }
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
  if (!newSupplier.value.name?.trim()) {
    showNotification('Supplier name is required', 'error');
    activeSupplierTab.value = 'basic';
    return;
  }

  const hasPhone = (newSupplier.value.phone && newSupplier.value.phone.trim()) || 
                   (newSupplier.value.mobile && newSupplier.value.mobile.trim());
  if (!hasPhone) {
    showNotification('Phone number is required to add a supplier', 'error');
    activeSupplierTab.value = 'contact';
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
    const errorMsg = error.response?.data?.errors?.phone?.[0] || 
                     error.response?.data?.message || 
                     'Error creating supplier';
    showNotification(errorMsg, 'error');
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
    // Hydrate payments & split payment methods
    const paymentsList = purchaseOrder.value?.payments || [];
    if (Array.isArray(paymentsList) && paymentsList.length > 0) {
      const methods = new Set();
      const bankIds = new Set();
      const cardIds = new Set();

      paymentsList.forEach(p => {
        const method = (p.payment_method || 'cash').toLowerCase();
        const amt = parseFloat(p.amount) || 0;

        if (method === 'cash') {
          methods.add('cash');
          paymentAmounts.value.cash = amt;
        } else if (method === 'card') {
          methods.add('card');
          if (p.bank_account_id) {
            cardIds.add(p.bank_account_id);
            cardPaymentAmounts.value[p.bank_account_id] = amt;
          }
        } else if (method === 'bank_transfer') {
          methods.add('bank_transfer');
          if (p.bank_account_id) {
            bankIds.add(p.bank_account_id);
            bankPaymentAmounts.value[p.bank_account_id] = amt;
          }
        }
      });

      if (methods.size > 0) {
        selectedPaymentMethods.value = Array.from(methods);
      }
      if (bankIds.size > 0) {
        selectedBankIds.value = Array.from(bankIds);
      }
      if (cardIds.size > 0) {
        selectedCardIds.value = Array.from(cardIds);
      }
    } else {
      const amtPaid = parseFloat(purchaseOrder.value?.amount_paid) || 0;
      if (amtPaid > 0) {
        selectedPaymentMethods.value = ['cash'];
        paymentAmounts.value.cash = amtPaid;
      }
    }

    // Hydrate useAdvanceBalance
    if (purchaseOrder.value?.use_advance_balance || (parseFloat(purchaseOrder.value?.advance_applied) || 0) > 0) {
      useAdvanceBalance.value = true;
    } else {
      useAdvanceBalance.value = false;
    }

  if (!selectedSupplier.value) {
    showNotification('Please select a supplier', 'error');
    return;
  }

  if (orderItems.value.length === 0) {
    showNotification('Please add at least one item', 'error');
    return;
  }

  if (hasInsufficientPaymentBalance.value) {
    showNotification('Cannot update purchase order: Insufficient balance in selected payment account', 'error');
    return;
  }

  saving.value = true;

  try {
    const orderData = {
      supplier_id: orderForm.value.supplier_id,
      po_number: orderForm.value.po_number || purchaseOrder.value?.po_number || null,
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
        ...(selectedPaymentMethods.value.includes('card') ? selectedCardIds.value.map(cardId => {
          const card = (allAccounts.value || []).find(b => b.id == cardId);
          return {
            payment_method: 'card',
            bank_account_id: cardId,
            account_name: card ? (card.account_name || card.bank_name) : `Card #${cardId}`,
            amount: parseFloat(cardPaymentAmounts.value[cardId]) || 0
          };
        }).filter(p => p.amount > 0) : []),
        ...(selectedPaymentMethods.value.includes('bank_transfer') ? selectedBankIds.value.map(bankId => {
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
        product_variation_id: item.product_variation_id,
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

  const paymentMethodContainer = document.getElementById('payment-method-dropdown-container');
  if (paymentMethodContainer && !paymentMethodContainer.contains(event.target)) {
    isPaymentDropdownOpen.value = false;
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

const allAccounts = ref([]);

const loadBankAccounts = async () => {
  try {
    const response = await api.get('/bank-accounts');
    allAccounts.value = Array.isArray(response.data) ? response.data : (response.data?.data || []);
  } catch (err) {
    console.error('Error loading bank accounts:', err);
  }
};

const selectedAccountAvailableBalance = computed(() => {
  if (!allAccounts.value || allAccounts.value.length === 0) return null;
  const method = (orderForm.value.payment_method || 'cash').toLowerCase();
  
  if (method === 'cash') {
    const cashAcc = allAccounts.value.find(acc => {
      const type = (acc.type || acc.account_type || '').toLowerCase();
      const name = (acc.account_name || acc.bank_name || '').toLowerCase();
      return type === 'cash' || name.includes('cash') || name.includes('vault');
    });
    return cashAcc ? parseFloat(cashAcc.current_balance || 0) : null;
  } else {
    const bankId = orderForm.value.bank_account_id;
    let bank = bankId ? allAccounts.value.find(b => b.id == bankId) : null;
    if (!bank) {
      bank = allAccounts.value.find(b => b.is_active !== false && b.is_active !== 0);
    }
    return bank ? parseFloat(bank.current_balance || 0) : null;
  }
});

const isEditPaymentBalanceExceeded = computed(() => {
  if (selectedAccountAvailableBalance.value === null) return false;
  const payAmt = parseFloat(orderForm.value.amount_paid) || 0;
  return payAmt > selectedAccountAvailableBalance.value;
});

// Lifecycle

const onProductSelected = ({ product, error, query }) => {
  if (error) {
    // some modules use showNotification, some use errorMessage.value
    if (typeof showNotification === 'function') {
      showNotification(error === 'Out of Stock' ? `Product "${product.name}" is currently Out of Stock.` : `No product found matching: ${query}`, 'error');
    } else if (typeof errorMessage !== 'undefined') {
      errorMessage.value = error === 'Out of Stock' ? `Product "${product.name}" is currently Out of Stock.` : `No product found matching: ${query}`;
    }
  } else if (product) {
    addToOrder(product);
  }
};

const onProductsFetched = (newItems) => {
  // If products is ref
  if (typeof products !== 'undefined' && products.value) {
    const existingKeys = new Set(products.value.map(p => p.id));
    newItems.forEach(item => {
      if (!existingKeys.has(item.id)) {
        products.value.push(item);
        existingKeys.add(item.id);
      }
    });
  } else if (typeof availableProducts !== 'undefined' && availableProducts.value) {
    const existingKeys = new Set(availableProducts.value.map(p => p.id));
    newItems.forEach(item => {
      if (!existingKeys.has(item.id)) {
        availableProducts.value.push(item);
        existingKeys.add(item.id);
      }
    });
  }
};


onMounted(() => {
  updateDateTime();
  setInterval(updateDateTime, 1000);
  fetchActiveCompany();
  loadWarehouses();
  fetchPurchaseOrder();
  loadProducts();
  loadCategories();
  loadTags();
  loadSuppliers();
  loadTaxes();
  loadBankAccounts();
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
