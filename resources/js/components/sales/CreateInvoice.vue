<template>
  <div class="w-full bg-slate-50 dark:bg-zinc-950 min-h-screen">

    <!-- Header bar -->
    <div class="bg-white dark:bg-[#1E1E1E] border-b border-slate-200 dark:border-[#2E2E2E] px-6 py-4 shadow-sm">
      <div class="flex justify-between items-center flex-wrap gap-3">
        <div class="flex items-center space-x-4">
          <button
            @click="goBack"
            class="text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 font-bold text-xs transition-colors duration-200 flex items-center space-x-1.5 focus:outline-none cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back</span>
          </button>
          <span class="text-slate-300 dark:text-slate-600 select-none">|</span>
          <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Create Sales Invoice</h1>

          <!-- Global Pricing Mode Toggle (Retail vs Wholesale) -->
          <div class="flex items-center bg-slate-100 dark:bg-zinc-800/80 p-1 rounded-xl border border-slate-200 dark:border-zinc-700/80 ml-2 shadow-inner select-none">
            <button
              type="button"
              @click="setGlobalPricingMode('retail')"
              class="px-3 py-1 rounded-lg text-xs font-bold transition-all duration-200 cursor-pointer flex items-center gap-1.5"
              :class="!isGlobalWholesale ? 'bg-white dark:bg-zinc-900 text-indigo-600 dark:text-indigo-400 shadow-sm border border-slate-200/60 dark:border-zinc-700' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-800 dark:hover:text-zinc-200'"
            >
              <span class="w-2 h-2 rounded-full" :class="!isGlobalWholesale ? 'bg-indigo-600 dark:bg-indigo-400' : 'bg-slate-300 dark:bg-zinc-600'"></span>
              Retail Mode
            </button>
            <button
              type="button"
              @click="setGlobalPricingMode('wholesale')"
              class="px-3 py-1 rounded-lg text-xs font-bold transition-all duration-200 cursor-pointer flex items-center gap-1.5"
              :class="isGlobalWholesale ? 'bg-indigo-600 text-white shadow-sm font-extrabold' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-800 dark:hover:text-zinc-200'"
            >
              <span class="w-2 h-2 rounded-full" :class="isGlobalWholesale ? 'bg-white animate-pulse' : 'bg-slate-300 dark:bg-zinc-600'"></span>
              Wholesale Mode
            </button>
          </div>
        </div>
        <div class="text-right text-xs">
          <div class="text-slate-700 dark:text-slate-300 font-bold">{{ authStore.user?.name }}</div>
          <div class="text-slate-400 dark:text-slate-500">{{ currentTime }} &nbsp;|&nbsp; {{ currentDate }}</div>
        </div>
      </div>
    </div>

    <!-- Main Workspace Layout: Unified Master Card Container -->
    <div class="w-full bg-white dark:bg-[#1E1E1E] flex flex-col md:flex-row min-h-[calc(100vh-66px)] border-t border-slate-200 dark:border-[#2E2E2E]">
      
      <!-- Left Panel: Invoice Form (3/4 width) -->
      <div class="w-full md:w-3/4 p-8 flex flex-col relative">

          <!-- Catalog Search & Selection Section -->
          <ProductSearch :products="products" :categories="categories" :taxes="taxes" :currencySymbol="currencySymbol" :targetWarehouseId="invoiceForm.warehouse_id === 'all' ? counterWarehouseFilterId : invoiceForm.warehouse_id" @product-selected="onProductSelected" @products-fetched="onProductsFetched" />
          <!-- Line Items Section Card -->
          <div class="border border-slate-200 dark:border-zinc-800 rounded-xl mt-2 overflow-hidden bg-white dark:bg-zinc-900 shadow-sm">
            <!-- 1. Scrollable Line Items Table (Header & Body ONLY) -->
            <div class="overflow-x-auto overflow-y-auto max-h-[380px] relative custom-scrollbar">
              <table class="w-full text-xs text-left border-collapse">
                <thead class="sticky top-0 z-10 shadow-xs">
                  <tr class="bg-slate-50 dark:bg-zinc-900 border-b border-slate-200 dark:border-zinc-800 text-slate-400 dark:text-zinc-400 uppercase font-extrabold tracking-wider">
                    <th class="py-3 px-3 w-4/12 bg-slate-50 dark:bg-zinc-900">Item Details / Description</th>
                    <th class="py-3 px-2 w-1/12 text-center bg-slate-50 dark:bg-zinc-900">Qty</th>
                    <th class="py-3 px-2 w-2/12 text-right bg-slate-50 dark:bg-zinc-900">
                      <div class="flex items-center justify-end gap-1.5">
                        <span>Price</span>
                        <span v-if="isGlobalWholesale" class="text-[9px] px-1.5 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 font-extrabold normal-case tracking-normal">W.S</span>
                      </div>
                    </th>
                    <th class="py-3 px-2 w-1.5/12 text-center bg-slate-50 dark:bg-zinc-900">Tax</th>
                    <th class="py-3 px-2 w-1.5/12 text-right bg-slate-50 dark:bg-zinc-900">Discount</th>
                    <th class="py-3 px-2 w-2/12 text-right bg-slate-50 dark:bg-zinc-900">Amount</th>
                    <th class="py-3 px-1 w-[40px] text-center bg-slate-50 dark:bg-zinc-900"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-zinc-800">
                  <tr v-if="invoiceItems.length === 0">
                    <td colspan="7" class="py-16 text-center text-slate-400 dark:text-zinc-500 italic">
                      <svg class="mx-auto h-10 w-10 text-slate-300 dark:text-zinc-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                      </svg>
                      <span>No products added. Use the filters & search list on the right to select items.</span>
                    </td>
                  </tr>

                  <template v-for="(item, index) in invoiceItems" :key="index">
                    <!-- Main Row: Product Title, SKU & Numerical Values -->
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/20 group align-top border-t border-slate-100 dark:border-zinc-800/60 first:border-0">
                      <!-- Name and SKU -->
                      <td class="pt-3 pb-1 px-3">
                        <div class="flex items-center justify-between mb-0.5 gap-2">
                          <div class="flex items-center gap-2 min-w-0">
                            <span class="font-bold text-slate-800 dark:text-zinc-100 text-sm truncate">{{ item.name }}</span>
                            <!-- Brand Name Badge -->
                            <span
                              v-if="item.brand_name || (item.product && item.product.brand_name) || (item.product && typeof item.product.brand === 'string') || (item.product && item.product.brand && item.product.brand.name)"
                              class="inline-block px-1.5 py-0.5 text-[9px] font-bold tracking-wide uppercase border border-slate-300 dark:border-zinc-700 text-slate-600 dark:text-zinc-400 rounded bg-slate-50 dark:bg-zinc-800/80 shrink-0 leading-none"
                            >
                              {{ item.brand_name || item.product.brand_name || (typeof item.product.brand === 'string' ? item.product.brand : item.product.brand.name) }}
                            </span>
                            <!-- W.S Applied Badge for individual row override in Retail Mode -->
                            <span v-if="!isGlobalWholesale && item.is_wholesale" class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border border-amber-300/60 dark:border-amber-700/60 flex items-center gap-1 shrink-0">
                              <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                              W.S Applied
                            </span>
                          </div>
                          
                          <!-- W.S Toggle Switch (Visible in Retail Mode only) -->
                          <label v-if="!isGlobalWholesale && companyInvoiceSettings.show_item_wholesale_toggle !== false" class="inline-flex items-center cursor-pointer select-none shrink-0" title="Toggle Wholesale Price for this item">
                            <span class="text-[9px] font-extrabold uppercase text-slate-500 dark:text-zinc-400 mr-1.5 tracking-wider">W.S</span>
                            <div class="relative">
                              <input
                                v-model="item.is_wholesale"
                                type="checkbox"
                                class="sr-only peer"
                                @change="updateItemTotal(index)"
                              />
                              <div class="w-7 h-4 bg-slate-200 dark:bg-zinc-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 dark:after:border-zinc-650 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-indigo-600 relative"></div>
                            </div>
                          </label>
                        </div>
                        <div class="text-[10px] text-slate-500 dark:text-zinc-400 font-mono">
                          <span class="whitespace-nowrap">SKU: {{ item.sku }}</span>
                        </div>
                        <div
                          v-if="item.category_path || (item.product && item.product.category_path) || (item.product && typeof item.product.category === 'string') || (item.product && item.product.category && item.product.category.name)"
                          class="text-[9.5px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider truncate mt-0.5"
                        >
                          {{ item.category_path || item.product.category_path || (typeof item.product.category === 'string' ? item.product.category : item.product.category.name) }}
                        </div>
                      </td>

                      <!-- Qty -->
                      <td class="pt-3 pb-1 px-2 text-center">
                        <input
                          v-model.number="item.quantity"
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

                      <!-- Price Field -->
                      <td class="pt-3 pb-1 px-2 text-right">
                        <input
                          v-if="isGlobalWholesale || item.is_wholesale"
                          v-model.number="item.wholesale_price"
                          type="number"
                          step="0.01"
                          min="0"
                          class="w-20 px-1.5 py-1 text-right border rounded focus:outline-none focus:ring-1 focus:ring-indigo-500 font-semibold transition-all duration-200"
                          :class="item.is_wholesale && !isGlobalWholesale ? 'border-amber-400 dark:border-amber-600 bg-amber-50/60 dark:bg-amber-950/30 text-amber-900 dark:text-amber-200' : 'border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200'"
                          @input="updateItemTotal(index)"
                        />
                        <input
                          v-else
                          v-model.number="item.unit_price"
                          type="number"
                          step="0.01"
                          min="0"
                          class="w-20 px-1.5 py-1 text-right border border-slate-300 dark:border-zinc-700 rounded focus:outline-none focus:ring-1 focus:ring-indigo-500 font-semibold transition-all duration-200 bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200"
                          @input="updateItemTotal(index)"
                        />
                      </td>

                      <!-- Line Tax selector -->
                      <td class="pt-3 pb-1 px-2 text-center">
                        <select
                          v-model="item.tax_id"
                          class="w-22 px-1 py-1 border border-slate-300 dark:border-zinc-700 rounded text-[11px] font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200 text-center"
                          @change="updateItemTax(item)"
                        >
                          <option :value="null">No Tax (0%)</option>
                          <option v-for="tax in taxes" :key="tax.id" :value="tax.id">
                            {{ tax.name ? `${tax.name} (${tax.value}%)` : `${tax.value}%` }}
                          </option>
                        </select>
                      </td>

                      <!-- Line Discount -->
                      <td class="pt-3 pb-1 px-2 text-right">
                        <div class="flex items-center justify-end space-x-1">
                          <button
                            type="button"
                            @click="toggleLineDiscountType(item, index)"
                            class="h-7 px-1.5 text-[10px] font-black rounded border border-slate-300 dark:border-zinc-700 bg-slate-100 dark:bg-zinc-800 text-indigo-600 dark:text-indigo-400 hover:bg-slate-200 dark:hover:bg-zinc-700 transition-all shrink-0 cursor-pointer"
                            :title="(item.discount_type || 'percentage') === 'fixed' ? 'Click to switch to Percentage (%)' : 'Click to switch to Flat Amount'"
                          >
                            {{ (item.discount_type || 'percentage') === 'fixed' ? currencySymbol : '%' }}
                          </button>
                          <input
                            v-model.number="item.discount_amount"
                            type="number"
                            step="0.01"
                            min="0"
                            class="w-16 px-1.5 py-1 text-right border border-slate-300 dark:border-zinc-700 rounded text-xs font-bold focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200"
                            :placeholder="(item.discount_type || 'percentage') === 'fixed' ? '0' : '0%'"
                            @input="updateItemTotal(index)"
                          />
                        </div>
                      </td>

                      <!-- Total Line Price -->
                      <td class="pt-3 pb-1 px-2 text-right font-bold text-slate-800 dark:text-zinc-200 text-sm">
                        {{ currencySymbol }}{{ item.total.toFixed(2) }}
                      </td>

                      <!-- Remove Button -->
                      <td class="pt-3 pb-1 px-1 text-center">
                        <button
                          @click="removeFromInvoice(index)"
                          class="text-slate-400 dark:text-zinc-500 hover:text-rose-600 dark:hover:text-rose-450 p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-all cursor-pointer"
                        >
                          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                          </svg>
                        </button>
                      </td>
                    </tr>

                    <!-- Full-Width Sub-Row: Expanded Description Box & Inline Warehouse Dropdown -->
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/20 group">
                      <td colspan="8" class="pt-1 pb-3 px-3">
                        <div class="flex flex-row items-center gap-3 w-full">
                          <!-- Description Box (expands horizontally across the whole row space) -->
                          <textarea
                            v-model="item.description"
                            placeholder="Add line item description / details..."
                            rows="1"
                            class="flex-1 min-w-0 h-[38px] bg-slate-50/50 dark:bg-zinc-900/60 hover:bg-slate-100/80 dark:hover:bg-zinc-800/80 focus:bg-white dark:focus:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-lg px-2.5 py-2 text-slate-600 dark:text-zinc-300 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-[11px] leading-tight resize-y"
                          ></textarea>                          <!-- Warehouse Dropdown (Custom Floating Dropup Inline Right) -->
                          <div v-if="warehouses.length > 0" class="shrink-0 flex items-center gap-1.5 relative" :id="`item-wh-dropdown-${index}`">
                            <span class="text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider shrink-0">WH:</span>
                            
                            <button
                              type="button"
                              @click.stop="toggleItemWarehouseDropdown(index, $event)"
                              class="h-[38px] px-2.5 border border-slate-300 dark:border-zinc-700 rounded-lg text-[10px] font-bold bg-white dark:bg-zinc-900 text-slate-700 dark:text-zinc-300 focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer flex items-center justify-between gap-1.5 min-w-[170px] max-w-[220px] shadow-xs hover:border-slate-400 dark:hover:border-zinc-600 transition-all select-none"
                            >
                              <span class="truncate">{{ getSelectedWarehouseLabel(item) }}</span>
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
            <table v-if="invoiceItems.length > 0" class="w-full text-xs text-left border-collapse border-t border-slate-200 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/40">
              <tfoot class="bg-slate-50 dark:bg-zinc-900/40">
                <!-- 1. Subtotal -->
                <tr>
                  <td colspan="5" class="py-2 px-3 text-right font-semibold text-slate-500 dark:text-zinc-400">Subtotal</td>
                  <td colspan="2" class="py-2 px-2 text-right font-bold text-slate-800 dark:text-zinc-200">{{ currencySymbol }}{{ invoiceSubtotal.toFixed(2) }}</td>
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
                    <td colspan="5" class="py-2.5 px-3 text-right text-xs">
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
                    <td colspan="2" class="py-2.5 px-2 text-right text-xs" :class="reqTax.enabled ? 'font-black text-emerald-600 dark:text-emerald-400' : 'font-semibold text-slate-400 dark:text-zinc-500 line-through'">
                      {{ reqTax.enabled ? '+' + currencySymbol + reqTax.amount.toFixed(2) : currencySymbol + '0.00' }}
                    </td>
                    <td class="w-[40px]"></td>
                  </tr>
                </template>

                <!-- 4. Taxes (manual field) -->
                <tr v-if="companyInvoiceSettings.allow_manual_taxes_discounts !== false">
                  <td colspan="5" class="py-2 px-3 text-right font-semibold text-slate-500 dark:text-zinc-400">Taxes (Manual)</td>
                  <td colspan="2" class="py-1.5 px-2 text-right">
                    <div class="flex items-center justify-end space-x-1">
                      <button
                        type="button"
                        @click="toggleManualTaxType"
                        class="h-7 px-2 text-[10px] font-black rounded-lg border border-slate-300 dark:border-zinc-700 bg-slate-100 dark:bg-zinc-800 text-indigo-600 dark:text-indigo-400 hover:bg-slate-200 dark:hover:bg-zinc-700 transition-all shrink-0 cursor-pointer"
                        :title="invoiceForm.tax_type === 'fixed' ? 'Click to switch to Percentage (%)' : 'Click to switch to Flat Amount'"
                      >
                        {{ invoiceForm.tax_type === 'fixed' ? currencySymbol : '%' }}
                      </button>
                      <input
                        v-model.number="invoiceForm.tax_amount"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-24 px-2 py-1 text-right border border-slate-300 dark:border-zinc-700 rounded-lg text-xs font-bold bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        :placeholder="invoiceForm.tax_type === 'fixed' ? '0.00' : '0%'"
                      />
                    </div>
                  </td>
                  <td class="w-[40px]"></td>
                </tr>

                <!-- 4. Discount (manual field) -->
                <tr v-if="companyInvoiceSettings.allow_manual_taxes_discounts !== false">
                  <td colspan="5" class="py-2 px-3 text-right font-semibold text-slate-500 dark:text-zinc-400">Discount (Manual)</td>
                  <td colspan="2" class="py-1.5 px-2 text-right">
                    <div class="flex items-center justify-end space-x-1">
                      <button
                        type="button"
                        @click="toggleManualDiscountType"
                        class="h-7 px-2 text-[10px] font-black rounded-lg border border-slate-300 dark:border-zinc-700 bg-slate-100 dark:bg-zinc-800 text-indigo-600 dark:text-indigo-400 hover:bg-slate-200 dark:hover:bg-zinc-700 transition-all shrink-0 cursor-pointer"
                        :title="invoiceForm.discount_type === 'fixed' ? 'Click to switch to Percentage (%)' : 'Click to switch to Flat Amount'"
                      >
                        {{ invoiceForm.discount_type === 'fixed' ? currencySymbol : '%' }}
                      </button>
                      <input
                        v-model.number="invoiceForm.discount_amount"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-24 px-2 py-1 text-right border border-slate-300 dark:border-zinc-700 rounded-lg text-xs font-bold bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        :placeholder="invoiceForm.discount_type === 'fixed' ? '0.00' : '0%'"
                      />
                    </div>
                  </td>
                  <td class="w-[40px]"></td>
                </tr>

                <!-- 5. Grand Total -->
                <tr class="border-t border-b border-slate-300 dark:border-zinc-700 bg-indigo-50/40 dark:bg-indigo-950/20 font-black">
                  <td colspan="5" class="py-3 px-3 text-right text-slate-900 dark:text-zinc-100 text-xs uppercase tracking-wider">Grand Total</td>
                  <td colspan="2" class="py-3 px-2 text-right text-indigo-600 dark:text-indigo-400 text-base font-black">{{ currencySymbol }}{{ grandTotal.toFixed(2) }}</td>
                  <td class="w-[40px]"></td>
                </tr>

                <!-- 6. Multi-Select Payment Details & Dynamic Receiving Amount Inputs -->
                <tr class="bg-slate-50/90 dark:bg-zinc-900/60 border-b border-slate-200 dark:border-zinc-800">
                  <td colspan="8" class="p-4">
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

                        <!-- Middle Col: Dynamic Receiving Amount Input Fields -->
                        <div class="md:col-span-5 space-y-3">
                          <label class="block text-slate-500 dark:text-zinc-400 font-bold text-xs">Receiving Amount(s):</label>
                          <div class="space-y-3">
                            <!-- Cash Amount Input -->
                            <div
                              v-if="selectedPaymentMethods.includes('cash')"
                              class="flex items-center justify-between gap-3 h-10 bg-white dark:bg-zinc-900 px-3.5 rounded-xl border border-slate-200 dark:border-zinc-750 shadow-2xs shrink-0"
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
                                />
                              </div>
                            </div>

                            <!-- Bank Amount Inputs -->
                            <template v-if="selectedPaymentMethods.includes('card') || selectedPaymentMethods.includes('bank_transfer')">
                              <div
                                v-for="bankId in selectedBankIds"
                                :key="bankId"
                                class="flex items-center justify-between gap-3 h-10 bg-white dark:bg-zinc-900 px-3.5 rounded-xl border border-slate-200 dark:border-zinc-750 shadow-2xs shrink-0"
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
                                  />
                                </div>
                              </div>
                            </template>
                          </div>
                        </div>

                        <!-- Right Col: Total Received Summary Cards -->
                        <div class="md:col-span-3 space-y-3">
                          <label class="block text-slate-500 dark:text-zinc-400 font-bold text-xs">Payment Summary:</label>
                          <div class="space-y-3">
                            <!-- Row 1 Card: Total Received -->
                            <div class="flex items-center justify-between h-10 px-3.5 rounded-xl bg-slate-100/90 dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 text-xs font-bold text-slate-700 dark:text-zinc-300 shadow-2xs">
                              <span>Total Received:</span>
                              <span class="text-sm font-extrabold text-indigo-600 dark:text-indigo-400">{{ currencySymbol }}{{ totalReceivedAmount.toFixed(2) }}</span>
                            </div>

                            <!-- Row 2 Card: Change / Excess or Remaining Due -->
                            <div v-if="totalReceivedAmount >= (useWalletCredit && effectiveDueAmount !== undefined ? effectiveDueAmount : grandTotal)" class="flex items-center justify-between h-10 px-3.5 rounded-xl bg-emerald-50/80 dark:bg-zinc-900 border border-emerald-200/80 dark:border-zinc-800 text-xs font-semibold text-emerald-600 dark:text-emerald-400 shadow-2xs">
                              <span>Change / Excess:</span>
                              <span class="font-extrabold">{{ currencySymbol }}{{ (totalReceivedAmount - (useWalletCredit && effectiveDueAmount !== undefined ? effectiveDueAmount : grandTotal)).toFixed(2) }}</span>
                            </div>
                            <div v-else class="flex items-center justify-between h-10 px-3.5 rounded-xl bg-amber-50/80 dark:bg-zinc-900 border border-amber-200/80 dark:border-zinc-800 text-xs font-semibold text-amber-600 dark:text-amber-400 shadow-2xs">
                              <span>Remaining Due:</span>
                              <span class="font-extrabold">{{ currencySymbol }}{{ ((useWalletCredit && effectiveDueAmount !== undefined ? effectiveDueAmount : grandTotal) - totalReceivedAmount).toFixed(2) }}</span>
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
              <label class="block text-xs font-bold uppercase text-slate-400 dark:text-zinc-500 tracking-wider mb-2">Notes to Customer</label>
              <textarea
                v-model="invoiceForm.notes"
                rows="3"
                class="w-full px-3 py-2 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500 placeholder-slate-400 dark:placeholder-zinc-600 bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200"
                placeholder="Include custom terms, wiring instructions or customer messages..."
              ></textarea>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase text-slate-400 dark:text-zinc-500 tracking-wider mb-2">Footer / Terms & Conditions</label>
              <textarea
                v-model="invoiceForm.footer"
                rows="3"
                class="w-full px-3 py-2 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500 placeholder-slate-400 dark:placeholder-zinc-600 bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200"
                placeholder="Include payment terms, legal declarations, or thank you note..."
              ></textarea>
            </div>
          </div>
      </div>

      <!-- Right Panel: Sidebar for Product Catalog Search & Metadata (1/4 width) -->
      <div class="w-full md:w-1/4 p-6 space-y-6 flex flex-col border-l border-slate-200 dark:border-zinc-800 bg-white dark:bg-[#1E1E1E]">
          
          <!-- Section 0: Invoice Metadata Details -->
          <div class="space-y-3 pb-4 border-b border-slate-100 dark:border-zinc-800 text-left">
            <div class="flex items-center justify-between">
              <h2 class="text-xl font-black uppercase tracking-wider transition-all duration-300" :style="{ color: accentColor }">INVOICE</h2>
            </div>

            <!-- Metadata Form Fields -->
            <div class="space-y-2.5 text-xs">
              <!-- Invoice Number -->
              <div>
                <div class="flex items-center justify-between mb-1">
                  <label class="block text-slate-500 dark:text-zinc-400 font-semibold text-xs">Invoice Number:</label>
                  <label
                    v-if="canEditInvoiceNumber"
                    class="flex items-center gap-1.5 cursor-pointer select-none text-[11px] font-semibold text-slate-600 dark:text-zinc-300"
                  >
                    <span>Manual</span>
                    <div class="relative inline-flex items-center cursor-pointer">
                      <input
                        type="checkbox"
                        v-model="isManualInvoiceNumber"
                        @change="toggleManualInvoiceNumber"
                        class="sr-only peer"
                      />
                      <div class="w-7 h-4 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all dark:after:border-zinc-600 peer-checked:bg-indigo-600"></div>
                    </div>
                  </label>
                </div>
                <input
                  v-model="invoiceForm.sale_number"
                  type="text"
                  :disabled="!isManualInvoiceNumber"
                  :readonly="!isManualInvoiceNumber"
                  placeholder="Auto-generating..."
                  class="w-full px-3 py-1.5 border border-slate-300 dark:border-zinc-700 rounded-lg font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs transition-colors"
                  :class="!isManualInvoiceNumber ? 'bg-slate-100 dark:bg-zinc-800/60 text-slate-500 dark:text-zinc-400 cursor-not-allowed select-none' : 'bg-white dark:bg-zinc-900 text-slate-700 dark:text-zinc-200'"
                />
              </div>

              <!-- Order Number -->
              <div>
                <label class="block text-slate-500 dark:text-zinc-400 font-semibold mb-1">Order Number:</label>
                <input
                  v-model="invoiceForm.order_number"
                  type="text"
                  placeholder="Enter order reference"
                  class="w-full px-3 py-1.5 border border-slate-300 dark:border-zinc-700 rounded-lg text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs"
                />
              </div>

              <!-- Counter -->
              <div>
                <label class="block text-slate-500 dark:text-zinc-400 font-semibold mb-1">Counter:</label>
                <div class="relative w-full" id="counter-dropdown-container">
                  <button
                    type="button"
                    @click.stop="showWarehouseSwitcherModal = true"
                    class="absolute left-2 top-1/2 -translate-y-1/2 z-10 p-1 text-slate-400 dark:text-zinc-400 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-md hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer"
                    title="Filter counters by Warehouse"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                  </button>
                  <button
                    type="button"
                    @click.stop="toggleCounterDropdown($event)"
                    class="w-full pl-9 pr-3 py-1.5 border border-slate-300 dark:border-zinc-700 rounded-lg text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs font-medium cursor-pointer flex justify-between items-center h-[34px] shadow-xs hover:border-slate-400 dark:hover:border-zinc-600 transition-all select-none"
                  >
                    <span class="truncate">{{ getSelectedCounterLabel() }}</span>
                    <svg class="h-3.5 w-3.5 text-slate-400 shrink-0 transition-transform duration-200" :class="{ 'rotate-180': isCounterDropdownOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>

                  <!-- Absolute Positioned Floating Counter Dropdown Menu -->
                  <transition
                    enter-active-class="transition duration-150 ease-out"
                    enter-from-class="transform opacity-0 scale-95"
                    enter-to-class="transform opacity-100 scale-100"
                    leave-active-class="transition duration-100 ease-in"
                    leave-from-class="transform opacity-100 scale-100"
                    leave-to-class="transform opacity-0 scale-95"
                  >
                    <div
                      v-if="isCounterDropdownOpen"
                      class="absolute top-[calc(100%+4px)] left-0 right-0 w-full z-50 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700/80 rounded-xl shadow-2xl overflow-hidden p-1 max-h-56 overflow-y-auto custom-scrollbar backdrop-blur-md"
                    >
                      <div
                        v-for="counter in availableCounters"
                        :key="counter.id"
                        @click.stop="selectCounter(counter.id)"
                        class="px-3 py-2 rounded-lg cursor-pointer flex items-center justify-between text-xs transition-colors border-b border-slate-50/50 dark:border-zinc-800/20 last:border-0"
                        :class="invoiceForm.counter_id == counter.id ? 'bg-indigo-50 dark:bg-zinc-800 text-indigo-700 dark:text-indigo-300 font-bold border-l-2 border-indigo-500' : 'hover:bg-slate-100/80 dark:hover:bg-zinc-800/60 text-slate-700 dark:text-zinc-200 font-medium'"
                      >
                        <div class="flex items-center space-x-2 truncate">
                          <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                          </svg>
                          <span class="truncate">{{ counter.name }} {{ counter.counter_number ? `(#${counter.counter_number})` : '' }}</span>
                        </div>
                        <svg v-if="invoiceForm.counter_id == counter.id" class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                      </div>
                      <div v-if="availableCounters.length === 0" class="px-3 py-3 text-center text-xs text-slate-400 dark:text-zinc-500 italic">
                        No counters available for this warehouse
                      </div>
                    </div>
                  </transition>
                </div>
              </div>

              <!-- Salesman -->
              <div>
                <label class="block text-slate-500 dark:text-zinc-400 font-semibold mb-1">Salesman:</label>
                <div class="relative w-full" id="salesman-dropdown-container">
                  <span class="absolute left-2.5 top-1/2 -translate-y-1/2 z-10 text-slate-400 dark:text-zinc-400 pointer-events-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </span>
                  <button
                    type="button"
                    @click.stop="toggleSalesmanDropdown($event)"
                    class="w-full pl-9 pr-3 py-1.5 border border-slate-300 dark:border-zinc-700 rounded-lg text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs font-medium cursor-pointer flex justify-between items-center h-[34px] shadow-xs hover:border-slate-400 dark:hover:border-zinc-600 transition-all select-none"
                  >
                    <span class="truncate">{{ getSelectedSalesmanLabel() }}</span>
                    <svg class="h-3.5 w-3.5 text-slate-400 shrink-0 transition-transform duration-200" :class="{ 'rotate-180': isSalesmanDropdownOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>

                  <!-- Absolute Positioned Floating Salesman Dropdown Menu -->
                  <transition
                    enter-active-class="transition duration-150 ease-out"
                    enter-from-class="transform opacity-0 scale-95"
                    enter-to-class="transform opacity-100 scale-100"
                    leave-active-class="transition duration-100 ease-in"
                    leave-from-class="transform opacity-100 scale-100"
                    leave-to-class="transform opacity-0 scale-95"
                  >
                    <div
                      v-if="isSalesmanDropdownOpen"
                      class="absolute top-[calc(100%+4px)] left-0 right-0 w-full z-50 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700/80 rounded-xl shadow-2xl overflow-hidden p-1 max-h-56 overflow-y-auto custom-scrollbar backdrop-blur-md"
                    >
                      <div
                        v-for="emp in filteredSalesmen"
                        :key="emp.id"
                        @click.stop="selectSalesman(emp.id)"
                        class="px-3 py-2 rounded-lg cursor-pointer flex items-center justify-between text-xs transition-colors border-b border-slate-50/50 dark:border-zinc-800/20 last:border-0"
                        :class="invoiceForm.salesman_id == emp.id ? 'bg-indigo-50 dark:bg-zinc-800 text-indigo-700 dark:text-indigo-300 font-bold border-l-2 border-indigo-500' : 'hover:bg-slate-100/80 dark:hover:bg-zinc-800/60 text-slate-700 dark:text-zinc-200 font-medium'"
                      >
                        <div class="flex items-center space-x-2.5 truncate">
                          <div class="w-5.5 h-5.5 rounded-full bg-indigo-100 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-300 font-bold text-[10px] flex items-center justify-center shrink-0 border border-indigo-200 dark:border-indigo-800/50">
                            {{ (emp.full_name || emp.first_name || 'S').charAt(0).toUpperCase() }}
                          </div>
                          <span class="truncate">{{ emp.full_name || `${emp.first_name || ''} ${emp.last_name || ''}`.trim() }} {{ emp.employee_number ? `(${emp.employee_number})` : '' }}</span>
                        </div>
                        <svg v-if="invoiceForm.salesman_id == emp.id" class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                      </div>
                      <div v-if="filteredSalesmen.length === 0" class="px-3 py-3 text-center text-xs text-slate-400 dark:text-zinc-500 italic">
                        No sales representatives available
                      </div>
                    </div>
                  </transition>
                </div>
              </div>

              <!-- BILL TO SECTION (Moved directly under Order Number) -->
              <div class="space-y-2 pt-1 pb-1 border-t border-b border-slate-100 dark:border-zinc-800/60">
                <h3 class="text-[11px] font-extrabold uppercase text-slate-500 dark:text-zinc-400 tracking-wider">Bill To</h3>
                
                <!-- Premium Attached Customer Search & Add Customer Input Group -->
                <div class="relative w-full" id="customer-search-container">
                  <div class="flex items-center w-full p-0.5 rounded-xl border border-slate-300/80 dark:border-zinc-700/80 focus-within:ring-2 focus-within:ring-emerald-500/20 focus-within:border-emerald-500 bg-slate-50/50 dark:bg-zinc-900/90 shadow-sm transition-all duration-200 hover:border-slate-300 dark:hover:border-zinc-700">
                    <div class="pl-2.5 pr-1 text-slate-400 dark:text-zinc-500 shrink-0">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                    </div>
                    <input
                      v-model="customerSearch"
                      type="text"
                      placeholder="Search customer name or phone..."
                      class="flex-1 min-w-0 pl-1.5 pr-2 py-1.5 text-xs border-0 focus:outline-none focus:ring-0 bg-transparent text-slate-800 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 font-medium"
                      @input="debouncedCustomerSearch"
                      @focus="searchCustomers(customerSearch)"
                    />
                    <button
                      type="button"
                      @click="showCustomerModal = true"
                      title="Add New Customer"
                      class="h-7 px-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 active:scale-95 text-white rounded-lg text-xs font-bold shadow-sm transition-all duration-200 flex items-center justify-center space-x-1 shrink-0 cursor-pointer"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                      </svg>
                    </button>
                  </div>
                  
                  <!-- Customer Search Dropdown Results -->
                  <div v-if="customerSearchResults.length > 0" class="absolute z-50 mt-1.5 w-full bg-white dark:bg-zinc-900 shadow-2xl max-h-[220px] rounded-xl border border-slate-200 dark:border-zinc-800 py-1 text-xs overflow-y-auto custom-scrollbar">
                    <div
                      v-for="customer in customerSearchResults"
                      :key="customer.id"
                      @click="selectCustomer(customer)"
                      class="cursor-pointer py-2 px-3 hover:bg-emerald-50/60 dark:hover:bg-zinc-800/80 flex justify-between items-center transition-colors border-b border-slate-50 dark:border-zinc-850 last:border-0"
                    >
                      <div class="flex items-center space-x-2.5 min-w-0">
                        <div class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 font-bold text-[10px] flex items-center justify-center shrink-0">
                          {{ customer.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="min-w-0">
                          <span class="font-bold text-slate-800 dark:text-zinc-200 truncate block">{{ customer.name }}</span>
                          <p class="text-[10px] text-slate-500 dark:text-zinc-400 truncate">{{ customer.phone || customer.email }}</p>
                        </div>
                      </div>
                      <span v-if="customer.tax_number" class="text-[9px] bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-400 px-1.5 py-0.5 rounded font-mono shrink-0 ml-2">TAX: {{ customer.tax_number }}</span>
                    </div>
                  </div>
                </div>

                <!-- Selected Customer Details Card -->
                <div v-if="selectedCustomer" class="p-3 bg-emerald-50/40 dark:bg-emerald-950/20 rounded-xl border border-emerald-200/80 dark:border-emerald-900/40 text-xs space-y-1 relative w-full text-left transition-all">
                  <button @click="clearCustomer" class="absolute top-2.5 right-2.5 text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-350 font-bold text-[10px] flex items-center gap-0.5 transition-colors border-0 bg-transparent cursor-pointer">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Remove
                  </button>
                  <div class="flex items-center space-x-2">
                    <div class="w-7 h-7 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-sm">
                      {{ selectedCustomer.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="min-w-0">
                      <p class="font-bold text-slate-800 dark:text-zinc-100 text-sm truncate">{{ selectedCustomer.name }}</p>
                      <p v-if="selectedCustomer.phone" class="text-[11px] text-slate-500 dark:text-zinc-400">{{ selectedCustomer.phone }}</p>
                    </div>
                  </div>
                </div>
                <div v-else-if="customerSearch && customerSearch.trim()" class="p-2.5 bg-blue-50/60 dark:bg-blue-950/30 rounded-xl border border-blue-200/80 dark:border-blue-900/40 text-xs flex items-center justify-between transition-all">
                  <div class="flex items-center space-x-2 min-w-0">
                    <div class="w-6 h-6 rounded-full bg-blue-500 text-white font-bold text-[10px] flex items-center justify-center shrink-0 shadow-xs">
                      {{ customerSearch.trim().charAt(0).toUpperCase() }}
                    </div>
                    <div class="min-w-0 text-left">
                      <p class="font-bold text-blue-900 dark:text-blue-200 text-xs truncate">{{ customerSearch.trim() }}</p>
                      <p class="text-[10px] text-blue-600 dark:text-blue-400 font-medium">Custom Customer (Auto-saved on print)</p>
                    </div>
                  </div>
                  <button @click="clearCustomer" type="button" class="text-slate-400 hover:text-rose-500 p-1 border-0 bg-transparent cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
                <div v-else class="text-slate-400 dark:text-zinc-500 text-[11px] italic text-left">
                  No customer selected. Type custom name or search above.
                </div>

                <!-- Customer Contact Fields (Phone & Email - Vertical Stack) -->
                <div class="flex flex-col gap-2 pt-1 text-left w-full">
                  <div class="w-full">
                    <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-zinc-400 mb-1">Phone Number</label>
                    <input
                      v-model="customerPhone"
                      type="tel"
                      placeholder="Enter phone number"
                      class="w-full px-2.5 py-1.5 text-xs border border-slate-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 font-medium focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    />
                  </div>
                  <div class="w-full">
                    <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-zinc-400 mb-1">Email Address</label>
                    <input
                      v-model="customerEmail"
                      type="email"
                      placeholder="Enter email address"
                      class="w-full px-2.5 py-1.5 text-xs border border-slate-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 font-medium focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    />
                  </div>
                </div>
              </div>

              <!-- Invoice Date & Due Date -->
              <div class="grid grid-cols-2 gap-2">
                <div>
                  <label class="block text-slate-500 dark:text-zinc-400 font-semibold mb-1">Invoice Date:</label>
                  <input
                    v-model="invoiceForm.sale_date"
                    type="date"
                    class="w-full px-2 py-1.5 border border-slate-300 dark:border-zinc-700 rounded-lg text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs"
                  />
                </div>
                <div>
                  <label class="block text-slate-500 dark:text-zinc-400 font-semibold mb-1">Due Date:</label>
                  <input
                    v-model="invoiceForm.due_date"
                    type="date"
                    class="w-full px-2 py-1.5 border border-slate-300 dark:border-zinc-700 rounded-lg text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs"
                  />
                </div>
              </div>

              <!-- Warehouse -->
              <div>
                <label class="block text-slate-500 dark:text-zinc-400 font-semibold mb-1">Warehouse:</label>
                <div class="relative" id="warehouse-dropdown-container">
                  <button
                    type="button"
                    @click.stop="isWarehouseDropdownOpen = !isWarehouseDropdownOpen"
                    class="w-full px-3 py-1.5 border border-slate-300 dark:border-zinc-700 rounded-lg text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer text-left flex justify-between items-center text-xs"
                  >
                    <span class="truncate">{{ selectedWarehouseName }}</span>
                    <svg class="h-3.5 w-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>

                  <!-- Warehouse Custom Dropdown List -->
                  <div
                    v-if="isWarehouseDropdownOpen"
                    class="absolute z-50 mt-1 w-full bg-white dark:bg-zinc-900 shadow-xl max-h-[185px] rounded-lg border border-slate-200 dark:border-zinc-800 py-1 text-xs overflow-y-auto custom-scrollbar"
                  >
                    <div
                      @click="selectWarehouse('all')"
                      class="cursor-pointer py-2 px-3 hover:bg-slate-100 dark:hover:bg-zinc-800 flex justify-between items-center"
                      :class="{ 'bg-slate-50 dark:bg-zinc-800 font-semibold text-indigo-600 dark:text-indigo-400': invoiceForm.warehouse_id === 'all' }"
                    >
                      <span>All Warehouses</span>
                    </div>
                    <div
                      v-for="wh in warehouses"
                      :key="wh.id"
                      @click="selectWarehouse(wh.id)"
                      class="cursor-pointer py-2 px-3 hover:bg-slate-100 dark:hover:bg-zinc-800 flex justify-between items-center border-t border-slate-50 dark:border-zinc-800"
                      :class="{ 'bg-slate-50 dark:bg-zinc-800 font-semibold text-indigo-600 dark:text-indigo-400': invoiceForm.warehouse_id === wh.id }"
                    >
                      <span>{{ wh.name }}</span>
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
                <span class="font-bold text-slate-800 dark:text-zinc-200">{{ currencySymbol }}{{ invoiceSubtotal.toFixed(2) }}</span>
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
                  <span v-if="invoiceForm.tax_type === 'percentage'" class="text-[10px] text-indigo-500 dark:text-indigo-400 font-extrabold">({{ invoiceForm.tax_amount || 0 }}%)</span>
                </span>
              </div>
              <div class="flex justify-between font-medium text-slate-600 dark:text-zinc-400">
                <span>Discount (Manual):</span>
                <span class="font-bold text-slate-800 dark:text-zinc-200">
                  -{{ currencySymbol }}{{ calculatedManualDiscount.toFixed(2) }}
                  <span v-if="invoiceForm.discount_type === 'percentage'" class="text-[10px] text-indigo-500 dark:text-indigo-400 font-extrabold">({{ invoiceForm.discount_amount || 0 }}%)</span>
                </span>
              </div>
              <div class="flex justify-between items-center text-sm font-extrabold text-slate-900 dark:text-zinc-100 border-t border-slate-200 dark:border-zinc-800 pt-2.5 mt-1">
                <span>Grand Total:</span>
                <span class="text-lg transition-all duration-300 font-black" :style="{ color: accentColor }">{{ currencySymbol }}{{ grandTotal.toFixed(2) }}</span>
              </div>
            </div>

            <!-- Wallet Credit Option -->
            <div v-if="selectedCustomer && parseFloat(selectedCustomer.wallet_balance || 0) > 0" class="bg-amber-50 dark:bg-amber-950/20 rounded-xl px-3 py-2.5 border border-amber-200 dark:border-amber-900/60 text-xs">
              <label class="flex items-center justify-between cursor-pointer">
                <div class="flex items-center space-x-2">
                  <input
                    type="checkbox"
                    v-model="useWalletCredit"
                    class="rounded border-amber-400 text-amber-600 focus:ring-amber-500 w-3.5 h-3.5 cursor-pointer"
                  />
                  <span class="font-bold text-amber-800 dark:text-amber-300">Use Wallet Credit</span>
                </div>
                <span class="font-extrabold text-amber-700 dark:text-amber-400">{{ currencySymbol }}{{ parseFloat(selectedCustomer.wallet_balance || 0).toFixed(2) }}</span>
              </label>
              <div v-if="useWalletCredit" class="mt-1.5 text-[10px] text-amber-600 dark:text-amber-400 font-medium">
                Applying {{ currencySymbol }}{{ walletCreditToApply.toFixed(2) }} from wallet → New effective due: {{ currencySymbol }}{{ effectiveDueAmount.toFixed(2) }}
              </div>
            </div>
          </div>

        <!-- Sidebar Sticky Footer Actions -->
        <div class="p-5 border-t border-slate-200 dark:border-zinc-800 bg-slate-50/80 dark:bg-zinc-900/40">
          <div class="space-y-3">
            <!-- Row 1: Primary Action (Save & Print) -->
            <button
              @click="saveInvoice(true)"
              :disabled="invoiceItems.length === 0 || saving"
              class="w-full h-10 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm shadow-sm transition-all flex items-center justify-center space-x-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="saving && printAfterSave" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
              </svg>
              <span>{{ saving && printAfterSave ? 'Saving...' : 'Save & Print' }}</span>
            </button>

            <!-- Row 2: Secondary Actions (Save Invoice, Save Draft) -->
            <div class="grid grid-cols-2 gap-3">
              <button
                @click="saveInvoice(false)"
                :disabled="invoiceItems.length === 0 || saving"
                class="w-full h-10 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 rounded-lg font-semibold text-sm transition-all flex items-center justify-center space-x-1.5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <svg v-if="saving && !printAfterSave" class="animate-spin -ml-1 mr-2 h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ saving && !printAfterSave ? 'Saving...' : 'Save Invoice' }}</span>
              </button>
              <button
                @click="saveAsDraft"
                :disabled="invoiceItems.length === 0 || saving"
                class="w-full h-10 border border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800 text-slate-600 dark:text-zinc-300 rounded-lg font-medium text-sm transition-all flex items-center justify-center cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Save Draft
              </button>
            </div>

            <!-- Row 3: Danger / Clear All Action -->
            <button
              @click="clearInvoice"
              :disabled="invoiceItems.length === 0"
              class="w-full h-9 text-rose-600 dark:text-rose-450 hover:bg-rose-50 dark:hover:bg-rose-950/20 border border-transparent hover:border-rose-200 dark:hover:border-rose-900/60 rounded-lg font-semibold text-xs transition-all flex items-center justify-center space-x-1.5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              <span>Clear All</span>
            </button>
          </div>
      </div>
    </div>

    <!-- Quick Customer Creation Modal -->
    <CustomerModalSimple
      v-if="showCustomerModal"
      :show="showCustomerModal"
      @close="showCustomerModal = false"
      @saved="handleCustomerSaved"
    />

    





    <!-- Quick Warehouse Switcher Modal -->
    <div
      v-if="showWarehouseSwitcherModal"
      class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200" style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);"
      @click.self="showWarehouseSwitcherModal = false"
    >
      <div class="bg-white dark:bg-zinc-900 rounded-2xl max-w-sm w-full p-5 shadow-2xl border border-slate-200 dark:border-zinc-800 animate-in fade-in zoom-in duration-150">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-zinc-800">
          <div class="flex items-center space-x-2.5">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-300 flex items-center justify-center font-bold">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5" />
              </svg>
            </div>
            <div>
              <h3 class="font-extrabold text-sm text-slate-800 dark:text-zinc-100">Select Warehouse</h3>
              <p class="text-[11px] text-slate-400 dark:text-zinc-400 font-medium">Filter available counters & POS terminals</p>
            </div>
          </div>
          <button
            type="button"
            @click="showWarehouseSwitcherModal = false"
            class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="space-y-2 max-h-60 overflow-y-auto custom-scrollbar">
          <div
            v-for="wh in warehouses"
            :key="wh.id"
            @click="selectActiveWarehouse(wh)"
            class="p-3 rounded-xl transition-all cursor-pointer flex items-center justify-between"
            :class="[
              counterWarehouseFilterId === wh.id 
                ? 'border-2 border-indigo-500 bg-indigo-50 dark:bg-zinc-800 shadow-md font-bold' 
                : 'border border-slate-200 dark:border-zinc-800/80 hover:bg-slate-50 dark:hover:bg-zinc-800/60'
            ]"
          >
            <div class="flex items-center space-x-3 min-w-0">
              <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 transition-colors" :class="counterWarehouseFilterId === wh.id ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-zinc-800 text-slate-500 dark:text-zinc-400'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5" />
                </svg>
              </div>
              <div class="min-w-0 text-left">
                <span class="text-xs font-bold block truncate" :class="counterWarehouseFilterId === wh.id ? 'text-indigo-950 dark:text-zinc-100 font-extrabold' : 'text-slate-800 dark:text-zinc-300'">{{ wh.name }}</span>
                <span class="text-[10px] block truncate font-medium" :class="counterWarehouseFilterId === wh.id ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-zinc-500'">{{ wh.counters_count ?? (wh.counters || []).length }} counter(s) available</span>
              </div>
            </div>
            <span v-if="wh.is_default" class="px-2 py-0.5 text-[9px] font-extrabold uppercase rounded tracking-wider" :class="counterWarehouseFilterId === wh.id ? 'bg-indigo-600 text-white' : 'bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300'">
              Default
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

    
    <!-- Floating Grand Total Badge -->
    <div class="fixed bottom-[10px] right-6 z-50 animate-fade-in-down">
      <div class="bg-slate-900 dark:bg-zinc-800 text-white px-10 py-2.5 min-w-[300px] rounded-xl shadow-xl flex items-center justify-between border border-slate-700 dark:border-zinc-700 cursor-default">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-400">Grand Total</span>
        <span class="text-2xl font-black leading-tight text-emerald-400">{{ currencySymbol }}{{ grandTotal.toFixed(2) }}</span>
      </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useCurrencyStore } from '@/stores/currency';
import { useToast } from '@/composables/useToast';
import CustomerModalSimple from '@/components/customers/CustomerModalSimple.vue';
import api from '@/services/api';
import ProductSearch from '@/components/shared/ProductSearch.vue';
import soundService from '@/services/SoundService';

const router = useRouter();
const authStore = useAuthStore();
const currencyStore = useCurrencyStore();
const { showToast } = useToast();

const currencySymbol = computed(() => {
  return currencyStore.symbol || activeCompany.value?.currency_symbol || activeCompany.value?.currency || activeCompany.value?.base_currency || 'PKR';
});

const formatCurrency = (amount, decimals = 2) => {
  const num = parseFloat(amount) || 0;
  return `${currencySymbol.value}${num.toFixed(decimals)}`;
};

// Accent colors palette presets
const presetColors = [
  { name: 'Indigo', hex: '#4f46e5' },
  { name: 'Emerald', hex: '#10b981' },
  { name: 'Amber', hex: '#f59e0b' },
  { name: 'Rose', hex: '#f43f5e' },
  { name: 'Slate', hex: '#475569' },
  { name: 'Blue', hex: '#3b82f6' }
];

// Reactive customisations
const accentColor = ref('#4f46e5');
const logoUrl = ref('');
const activeCompany = ref(null);
const attachmentsList = ref([]);

// Reactive data
const products = ref([]);
const categories = ref([]);
const customers = ref([]);
const taxes = ref([]);
const invoiceItems = ref([]);
const selectedCustomer = ref(null);
const selectedCategories = ref([]);
const isProductDropdownOpen = ref(false);
const isCategoryDropdownOpen = ref(false);
const barcodeInput = ref('');
const warehouses = ref([]);
const salesmen = ref([]);

const isAdminOrOwner = computed(() => {
  const user = authStore.user;
  if (!user) return false;
  const userRoles = (authStore.roles || []).map(r => String(r).toLowerCase());
  if (userRoles.includes('admin') || userRoles.includes('owner') || (typeof authStore.hasRole === 'function' && (authStore.hasRole('admin') || authStore.hasRole('owner')))) {
    return true;
  }
  if (user.id === 1 || (activeCompany.value?.user_id && Number(user.id) === Number(activeCompany.value.user_id))) {
    return true;
  }
  return false;
});

const filteredSalesmen = computed(() => {
  const list = (salesmen.value || []).filter(s => 
    s && 
    s.id && 
    s.full_name !== 'Select Sales Representative' && 
    !String(s.full_name || '').startsWith('Select Sales Representative')
  );
  if (isAdminOrOwner.value) {
    return list;
  }
  return list.filter(s => !s.is_owner && !String(s.full_name || '').toLowerCase().includes('(owner)'));
});

const showWarehouseSwitcherModal = ref(false);
const counterWarehouseFilterId = ref('all');
const isWarehouseDropdownOpen = ref(false);

const availableCounters = computed(() => {
  if (!counterWarehouseFilterId.value || counterWarehouseFilterId.value === 'all') {
    return warehouses.value.flatMap(w => w.counters || []);
  }
  const wh = warehouses.value.find(w => String(w.id) === String(counterWarehouseFilterId.value));
  return wh ? (wh.counters || []) : [];
});

const getSelectedWarehouseNameLabel = () => {
  if (!counterWarehouseFilterId.value || counterWarehouseFilterId.value === 'all') return 'All Warehouses';
  const wh = warehouses.value.find(w => String(w.id) === String(counterWarehouseFilterId.value));
  return wh ? wh.name : 'Select Warehouse';
};

const selectActiveWarehouse = (wh) => {
  counterWarehouseFilterId.value = wh ? wh.id : 'all';
  showWarehouseSwitcherModal.value = false;
  
  const counters = wh ? (wh.counters || []) : warehouses.value.flatMap(w => w.counters || []);
  if (counters.length > 0) {
    invoiceForm.value.counter_id = counters[0].id;
  } else {
    invoiceForm.value.counter_id = '';
  }
};

const openWarehouseItemIndex = ref(null);
const isPaymentDropdownOpen = ref(false);

const paymentMethodsList = [
  { value: 'cash', label: 'Cash' },
  { value: 'card', label: 'Card' },
  { value: 'bank_transfer', label: 'Bank Transfer' },
  { value: 'mobile_payment', label: 'Mobile Payment' },
  { value: 'mixed', label: 'Mixed' }
];

const warehouseDropdownPos = ref({ top: 'auto', bottom: 'auto', left: '0px' });
const paymentDropdownPos = ref({ top: 'auto', bottom: 'auto', left: '0px', width: '200px' });
const counterDropdownPos = ref({ top: 'auto', bottom: 'auto', left: '0px', width: '200px' });
const isCounterDropdownOpen = ref(false);

const salesmanDropdownPos = ref({ top: 'auto', bottom: 'auto', left: '0px', width: '200px' });
const isSalesmanDropdownOpen = ref(false);

const toggleCounterDropdown = (event) => {
  isSalesmanDropdownOpen.value = false;
  isPaymentDropdownOpen.value = false;
  openWarehouseItemIndex.value = null;
  isCounterDropdownOpen.value = !isCounterDropdownOpen.value;
};

const toggleSalesmanDropdown = (event) => {
  isCounterDropdownOpen.value = false;
  isPaymentDropdownOpen.value = false;
  openWarehouseItemIndex.value = null;
  isSalesmanDropdownOpen.value = !isSalesmanDropdownOpen.value;
};

const selectCounter = (id) => {
  invoiceForm.value.counter_id = id;
  isCounterDropdownOpen.value = false;
};

const selectSalesman = (id) => {
  invoiceForm.value.salesman_id = id;
  isSalesmanDropdownOpen.value = false;
};

const getSelectedCounterLabel = () => {
  if (!invoiceForm.value.counter_id) return 'Select Counter / Terminal';
  const allCounters = warehouses.value.flatMap(w => w.counters || []);
  const c = allCounters.find(item => String(item.id) === String(invoiceForm.value.counter_id));
  return c ? `${c.name} ${c.counter_number ? `(#${c.counter_number})` : ''}` : 'Select Counter / Terminal';
};

const getSelectedSalesmanLabel = () => {
  if (!invoiceForm.value.salesman_id) return 'Select Sales Representative';
  const s = salesmen.value.find(item => item.id == invoiceForm.value.salesman_id);
  if (!s) return 'Select Sales Representative';
  const name = s.full_name || `${s.first_name || ''} ${s.last_name || ''}`.trim();
  return s.employee_number ? `${name} (${s.employee_number})` : name;
};

const getSelectedWarehouseName = (product, id) => {
  if (!id) return 'Select Warehouse';
  const wh = warehouses.value.find(w => w.id == id);
  return wh ? wh.name : 'Select Warehouse';
};

const getSelectedPaymentMethodLabel = (val) => {
  const found = paymentMethodsList.find(p => p.value === val);
  return found ? found.label : (val || 'Cash');
};

const toggleItemWarehouseDropdown = (index, event) => {
  if (openWarehouseItemIndex.value === index) {
    openWarehouseItemIndex.value = null;
    return;
  }
  
  isPaymentDropdownOpen.value = false;
  openWarehouseItemIndex.value = index;

  nextTick(() => {
    const btn = event?.currentTarget;
    if (!btn) return;
    const rect = btn.getBoundingClientRect();
    const bottomVal = Math.max(10, window.innerHeight - rect.top + 2);
    const leftVal = Math.max(10, Math.min(window.innerWidth - 266, rect.right - 256));

    warehouseDropdownPos.value = {
      top: 'auto',
      bottom: `${bottomVal}px`,
      left: `${leftVal}px`
    };
  });
};

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
  if (invoiceItems.value[index]) {
    invoiceItems.value[index].warehouse_id = whId;
    onItemWarehouseChange(index);
  }
  openWarehouseItemIndex.value = null;
};

const selectPaymentMethod = (val) => {
  invoiceForm.value.payment_method = val;
  isPaymentDropdownOpen.value = false;
};
const getInitialInvoiceSettings = () => {
  try {
    const cached = localStorage.getItem('company_invoice_settings');
    if (cached) {
      return JSON.parse(cached);
    }
  } catch (e) {}
  return null;
};

const cachedInvoiceSettings = getInitialInvoiceSettings();

const isBarcodeActive = ref(true);
const isGlobalWholesale = ref(cachedInvoiceSettings?.default_pricing_mode === 'wholesale');

const companyInvoiceSettings = ref({
  show_item_wholesale_toggle: cachedInvoiceSettings?.show_item_wholesale_toggle ?? true,
  allow_manual_taxes_discounts: cachedInvoiceSettings?.allow_manual_taxes_discounts ?? true,
  ...(cachedInvoiceSettings || {})
});

const loadCompanyInvoiceSettings = async () => {
  try {
    const res = await api.get('/invoice-purchase-settings');
    if (res.data) {
      try {
        localStorage.setItem('company_invoice_settings', JSON.stringify(res.data));
      } catch (e) {}

      companyInvoiceSettings.value = {
        ...companyInvoiceSettings.value,
        ...res.data
      };

      if (res.data.default_pricing_mode === 'wholesale') {
        isGlobalWholesale.value = true;
      } else if (res.data.default_pricing_mode === 'retail') {
        isGlobalWholesale.value = false;
      }

      if (res.data.default_due_period_days !== undefined && res.data.default_due_period_days !== null) {
        const days = parseInt(res.data.default_due_period_days) || 0;
        const d = new Date();
        d.setDate(d.getDate() + days);
        invoiceForm.value.due_date = d.toISOString().split('T')[0];
      }

      if (res.data.default_terms_conditions && !invoiceForm.value.footer) {
        invoiceForm.value.footer = res.data.default_terms_conditions;
      }
    }
  } catch (e) {
    console.error('Error loading company invoice settings:', e);
  }
};

const setGlobalPricingMode = (mode) => {
  isGlobalWholesale.value = (mode === 'wholesale');
  invoiceItems.value.forEach((_, index) => {
    updateItemTotal(index);
  });
  calculateTotal();
};

const getEffectiveUnitPrice = (item, isWholesaleGlobal = isGlobalWholesale.value) => {
  if (!item) return 0;
  if (isWholesaleGlobal || item.is_wholesale) {
    return parseFloat(item.wholesale_price ?? item.unit_price ?? item.price) || 0;
  }
  return parseFloat(item.unit_price ?? item.price ?? item.wholesale_price) || 0;
};

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

const getTaxLabel = (taxId) => {
  const tx = taxes.value.find(t => String(t.id) === String(taxId));
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

const addAdvanceProductToInvoice = (product) => {
  addToInvoice(product);
  showNotification(`Added "${product.name}" to invoice`, 'success');
};

let barcodeBuffer = '';
let lastKeyTime = 0;

const handleGlobalBarcodeScan = (event) => {
  if (!isBarcodeActive.value) return;

  const isInputTarget = event.target && ['INPUT', 'TEXTAREA'].includes(event.target.tagName);

  const currentTime = Date.now();
  if (currentTime - lastKeyTime > 80) {
    barcodeBuffer = '';
  }
  lastKeyTime = currentTime;

  if (event.key === 'Enter') {
    if (barcodeBuffer.length >= 2) {
      const code = barcodeBuffer.trim();
      const matchedProduct = products.value.find(p => 
        (p.barcode && p.barcode.toLowerCase() === code.toLowerCase()) || 
        (p.sku && p.sku.toLowerCase() === code.toLowerCase())
      );

      if (matchedProduct) {
        addToInvoice(matchedProduct);
        showNotification(`Added "${matchedProduct.name}" to invoice`, 'success');
        barcodeBuffer = '';
        if (isInputTarget) event.target.blur();
        event.preventDefault();
      }
    }
    barcodeBuffer = '';
  } else if (event.key.length === 1) {
    barcodeBuffer += event.key;
  }
};

const handleProductSearchEnter = () => {
  const query = productSearch.value.trim().toLowerCase();
  if (!query) return;

  const matchedProduct = products.value.find(p => 
    (p.barcode && p.barcode.toLowerCase() === query) || 
    (p.sku && p.sku.toLowerCase() === query)
  ) || filteredProducts.value[0];

  if (matchedProduct) {
    addToInvoice(matchedProduct);
    productSearch.value = '';
    isProductDropdownOpen.value = false;
    showNotification(`Added "${matchedProduct.name}" to invoice`, 'success');
  } else {
    showNotification(`No product found matching: ${productSearch.value}`, 'error');
  }
};
const productSearch = ref('');
const customerSearch = ref('');
const customerSearchResults = ref([]);
const customerPhone = ref('');
const customerEmail = ref('');
const loadingProducts = ref(false);
const saving = ref(false);
const printAfterSave = ref(false);
const creatingCustomer = ref(false);
const showCustomerModal = ref(false);
const notifications = ref([]);
const useWalletCredit = ref(false);

const availablePaymentMethods = [
  { id: 'cash', label: 'Cash' },
  { id: 'card', label: 'Card' },
  { id: 'bank_transfer', label: 'Bank Transfer' }
];

const selectedPaymentMethods = ref(['cash']);
const paymentAmounts = ref({
  cash: 0
});

const allAccounts = ref([]);
const bankAccounts = allAccounts; // Reference alias

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
const selectedBankAccounts = selectedBankIds; // Reference alias
const bankPaymentAmounts = ref({});
const isBankDropdownOpen = ref(false);

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
    const defaultActiveBank = activeBankAccounts.value.find(b => (b.is_active !== false && b.is_active !== 0) && (b.is_default || b.is_default === 1 || b.is_default === '1')) || activeBankAccounts.value.find(b => b.is_active !== false && b.is_active !== 0);
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

    const defaultActiveBank = activeBankAccounts.value.find(b => (b.is_active !== false && b.is_active !== 0) && (b.is_default || b.is_default === 1 || b.is_default === '1')) || activeBankAccounts.value.find(b => b.is_active !== false && b.is_active !== 0);
    if ((methodId === 'card' || methodId === 'bank_transfer') && selectedBankIds.value.length === 0 && defaultActiveBank) {
      selectedBankIds.value = [defaultActiveBank.id];
      if (bankPaymentAmounts.value[defaultActiveBank.id] === undefined) {
        bankPaymentAmounts.value[defaultActiveBank.id] = 0;
      }
    }
    
    const existingSum = totalReceivedAmount.value;
    const targetTotal = (useWalletCredit.value && effectiveDueAmount.value !== undefined)
      ? effectiveDueAmount.value
      : (grandTotal.value || 0);

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
    
    const existingSum = totalReceivedAmount.value;
    const targetTotal = (useWalletCredit.value && effectiveDueAmount.value !== undefined)
      ? effectiveDueAmount.value
      : (grandTotal.value || 0);

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
  const isCreditCard = bank.account_type === 'credit_card';

  let baseLabel = '';
  if (isCreditCard) {
    let bankTitle = bankName || 'Credit Card';
    if (!bankTitle.toLowerCase().includes('credit card')) {
      bankTitle += '-Credit Card';
    }
    baseLabel = `${accountName || 'Card Holder'} (${bankTitle})`;
  } else if (bankName && accountName && bankName.toLowerCase() !== accountName.toLowerCase()) {
    baseLabel = `${accountName} (${bankName})`;
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

const totalReceivedAmount = computed(() => {
  let sum = 0;
  if (selectedPaymentMethods.value.includes('cash')) {
    sum += parseFloat(paymentAmounts.value.cash) || 0;
  }
  if (selectedPaymentMethods.value.includes('card') || selectedPaymentMethods.value.includes('bank_transfer')) {
    selectedBankIds.value.forEach(bankId => {
      sum += parseFloat(bankPaymentAmounts.value[bankId]) || 0;
    });
  }
  return sum;
});

const isManualInvoiceNumber = ref(false);
const autoGeneratedSaleNumber = ref('');

const canEditInvoiceNumber = computed(() => {
  return authStore.hasPermission('edit_invoice_number') ||
    authStore.hasPermission('sales.edit_invoice_number') ||
    authStore.hasRole('admin') ||
    authStore.hasRole('owner');
});

const toggleManualInvoiceNumber = () => {
  if (!isManualInvoiceNumber.value) {
    invoiceForm.value.sale_number = autoGeneratedSaleNumber.value;
  }
};

const invoiceForm = ref({
  customer_id: '',
  sale_number: '',
  category_id: null,
  warehouse_id: '',
  counter_id: '',
  salesman_id: '',
  sale_date: new Date().toISOString().split('T')[0],
  due_date: getDefaultDueDate(new Date()),
  order_number: '',
  payment_method: 'cash',
  status: 'completed',
  tax_type: 'percentage',
  tax_amount: 0,
  discount_type: 'percentage',
  discount_amount: 0,
  paid_amount: 0,
  notes: '',
  footer: 'Thank you for your business!'
});

const newCustomer = ref({
  name: '',
  email: '',
  phone: '',
  mobile: '',
  address: '',
  city: '',
  state: '',
  tax_number: '',
  credit_limit: 0,
  is_active: true,
  notes: ''
});

// Current date time
const currentDateTime = ref('');

// Computed properties
const selectedWarehouseName = computed(() => {
  if (!invoiceForm.value.warehouse_id || invoiceForm.value.warehouse_id === 'all') {
    return 'All Warehouses';
  }
  const wh = warehouses.value.find(w => String(w.id) === String(invoiceForm.value.warehouse_id));
  return wh ? wh.name : 'All Warehouses';
});
const filteredProducts = computed(() => {
  let filtered = products.value;
  
  if (selectedCategories.value.length > 0) {
    const selectedIds = selectedCategories.value.map(id => String(id));
    filtered = filtered.filter(product => selectedIds.includes(String(product.category_id)));
  }
  
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

const categoryDropdownLabel = computed(() => {
  if (selectedCategories.value.length === 0) {
    return 'Categories: All Categories';
  }
  const names = selectedCategories.value.map(id => {
    const cat = categories.value.find(c => String(c.id) === String(id));
    return cat ? cat.name : '';
  }).filter(Boolean);
  return 'Categories: ' + names.join(', ');
});

const invoiceSubtotal = computed(() => {
  return invoiceItems.value.reduce((sum, item) => {
    const basePrice = getEffectiveUnitPrice(item);
    const qty = Number(item.quantity) || 0;
    const lineVal = qty * basePrice;
    return sum + (isNaN(lineVal) ? 0 : lineVal);
  }, 0);
});

const calculatedManualTax = computed(() => {
  if (companyInvoiceSettings.value?.allow_manual_taxes_discounts === false) return 0;
  const taxVal = parseFloat(invoiceForm.value.tax_amount) || 0;
  if (invoiceForm.value.tax_type === 'percentage') {
    return (invoiceSubtotal.value * taxVal) / 100;
  }
  return taxVal;
});

const disabledRequiredTaxIds = ref([]);

const toggleRequiredTax = (taxId) => {
  const targetId = Number(taxId);
  const idx = disabledRequiredTaxIds.value.findIndex(id => Number(id) === targetId);
  if (idx > -1) {
    disabledRequiredTaxIds.value.splice(idx, 1);
  } else {
    disabledRequiredTaxIds.value.push(targetId);
  }
};

const requiredTaxes = computed(() => {
  return taxes.value.filter(t => (t.is_active || t.is_active === 1 || t.is_active === '1') && (t.sale_invoice_required || t.sale_invoice_required === 1 || t.sale_invoice_required === '1'));
});

const autoRequiredTaxesList = computed(() => {
  const sub = invoiceSubtotal.value || 0;
  return requiredTaxes.value.map(t => {
    const val = parseFloat(t.value) || 0;
    const isEnabled = !disabledRequiredTaxIds.value.some(id => Number(id) === Number(t.id));
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
    .reduce((sum, item) => sum + (Number(item.amount) || 0), 0);
});

const calculatedManualDiscount = computed(() => {
  if (companyInvoiceSettings.value?.allow_manual_taxes_discounts === false) return 0;
  const disVal = parseFloat(invoiceForm.value.discount_amount) || 0;
  if (invoiceForm.value.discount_type === 'percentage') {
    return (invoiceSubtotal.value * disVal) / 100;
  }
  return disVal;
});

const totalDiscount = computed(() => {
  const itemDiscountSum = invoiceItems.value.reduce((sum, item) => {
    const basePrice = getEffectiveUnitPrice(item);
    const qty = Number(item.quantity) || 0;
    const itemSubtotal = qty * basePrice;
    let rawDiscount = Number(item.discount_amount) || 0;
    if (item.discount_type === 'percentage' && rawDiscount > 100) {
      rawDiscount = 100;
    }
    const effDiscount = (item.discount_type === 'percentage')
      ? (itemSubtotal * rawDiscount) / 100
      : Math.min(itemSubtotal, rawDiscount);
    return sum + (isNaN(effDiscount) ? 0 : effDiscount);
  }, 0);
  return itemDiscountSum + calculatedManualDiscount.value;
});

const totalTax = computed(() => {
  const itemTaxSum = invoiceItems.value.reduce((sum, item) => {
    const basePrice = getEffectiveUnitPrice(item);
    const qty = Number(item.quantity) || 0;
    const itemSubtotal = qty * basePrice;
    let rawDiscount = Number(item.discount_amount) || 0;
    if (item.discount_type === 'percentage' && rawDiscount > 100) {
      rawDiscount = 100;
    }
    const effDiscount = (item.discount_type === 'percentage')
      ? (itemSubtotal * rawDiscount) / 100
      : Math.min(itemSubtotal, rawDiscount);
    const taxRate = Number(item.tax_rate) || 0;
    const taxable = Math.max(0, itemSubtotal - effDiscount);
    const taxAmt = (taxable * taxRate) / 100;
    return sum + (isNaN(taxAmt) ? 0 : taxAmt);
  }, 0);
  return itemTaxSum + totalAutoRequiredTax.value + calculatedManualTax.value;
});

const grandTotal = computed(() => {
  const netLineSubtotal = invoiceItems.value.reduce((sum, item) => {
    const basePrice = getEffectiveUnitPrice(item);
    const qty = Number(item.quantity) || 0;
    const itemSub = qty * basePrice;
    let rawDiscount = Number(item.discount_amount) || 0;
    if (item.discount_type === 'percentage' && rawDiscount > 100) {
      rawDiscount = 100;
    }
    const effDiscount = (item.discount_type === 'percentage')
      ? (itemSub * rawDiscount) / 100
      : Math.min(itemSub, rawDiscount);
    return sum + Math.max(0, itemSub - effDiscount);
  }, 0);

  const finalVal = netLineSubtotal + totalTax.value - calculatedManualDiscount.value;
  return Math.max(0, isNaN(finalVal) ? 0 : finalVal);
});

const invoiceTotal = computed(() => {
  return grandTotal.value;
});

const changeAmount = computed(() => {
  return Math.max(0, (invoiceForm.value.paid_amount || 0) - grandTotal.value);
});

const dueAmount = computed(() => {
  return Math.max(0, grandTotal.value - (invoiceForm.value.paid_amount || 0));
});

const walletCreditToApply = computed(() => {
  if (!useWalletCredit.value || !selectedCustomer.value) return 0;
  const walletBal = parseFloat(selectedCustomer.value.wallet_balance || 0);
  return Math.min(walletBal, Math.max(0, grandTotal.value));
});

const effectiveDueAmount = computed(() => {
  const baseDue = dueAmount.value;
  if (useWalletCredit.value) {
    return Math.max(0, baseDue - walletCreditToApply.value);
  }
  return baseDue;
});

const currentDate = computed(() => {
  return currentDateTime.value.split(',')[0] || '';
});

const currentTime = computed(() => {
  return currentDateTime.value.split(',')[1] || '';
});

// Watchers
watch(() => invoiceForm.value.sale_date, (newDate) => {
  if (newDate) {
    invoiceForm.value.due_date = getDefaultDueDate(newDate);
  }
});

watch(grandTotal, (newGrandTotal) => {
  invoiceForm.value.paid_amount = parseFloat(newGrandTotal.toFixed(2));
}, { immediate: true });

// Methods
function getDefaultDueDate(invoiceDateStr) {
  const d = new Date(invoiceDateStr);
  d.setDate(d.getDate() + 30); // Net 30 days fallback
  return d.toISOString().split('T')[0];
}

const updateDateTime = () => {
  const now = new Date();
  const date = now.toLocaleDateString();
  const time = now.toLocaleTimeString();
  currentDateTime.value = `${date}, ${time}`;
};

const isProductOutOfStock = (product) => {
  if (!product) return false;
  if (!product.track_inventory) return false;
  const targetWhId = invoiceForm.value.warehouse_id || counterWarehouseFilterId.value;
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
  
  const targetWhId = invoiceForm.value.warehouse_id || counterWarehouseFilterId.value;
  
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

const getProductWarehouseStock = (product, warehouseId) => {
  if (!product) return 0;
  if (!product.track_inventory) return '∞';
  if (!warehouseId || warehouseId === 'all') return product.total_stock ?? 0;
  
  if (product.warehouse_stocks) {
    const stockVal = product.warehouse_stocks[warehouseId]
      ?? product.warehouse_stocks[String(warehouseId)]
      ?? product.warehouse_stocks[Number(warehouseId)];
    if (stockVal !== undefined && stockVal !== null) {
      return stockVal;
    }
  }
  return product.total_stock ?? 0;
};

const getItemAvailableStock = (item) => {
  if (!item || !item.product) return '∞';
  if (!item.product.track_inventory) return '∞';
  const whIds = (Array.isArray(item.warehouse_ids) && item.warehouse_ids.length > 0)
    ? item.warehouse_ids
    : (item.warehouse_id ? [item.warehouse_id] : []);

  if (whIds.length === 0 || whIds.includes('all')) return item.product.total_stock ?? 0;

  let total = 0;
  let foundAny = false;
  for (const whId of whIds) {
    if (item.product.warehouse_stocks) {
      const stockVal = item.product.warehouse_stocks[whId]
        ?? item.product.warehouse_stocks[String(whId)]
        ?? item.product.warehouse_stocks[Number(whId)];
      if (stockVal !== undefined && stockVal !== null) {
        total += Number(stockVal);
        foundAny = true;
      }
    }
  }
  return foundAny ? total : (item.product.total_stock ?? 0);
};

const isItemStockExceeded = (item) => {
  if (!item || !item.product || !item.product.track_inventory) return false;
  const stock = getItemAvailableStock(item);
  if (typeof stock !== 'number') return false;
  return item.quantity > stock;
};

const validateItemStock = (item, notify = false) => {
  if (!item || !item.product || !item.product.track_inventory) return;
  const stock = getItemAvailableStock(item);
  if (typeof stock !== 'number') return;

  if (item.quantity > stock && notify) {
    showNotification(`Requested quantity ${item.quantity} exceeds combined available stock ${stock} across selected warehouses`, 'error');
  }
};

const toggleWarehouseSelection = (itemIndex, whId) => {
  const item = invoiceItems.value[itemIndex];
  if (!item) return;

  if (!Array.isArray(item.warehouse_ids)) {
    item.warehouse_ids = item.warehouse_id ? [item.warehouse_id] : [];
  }

  const strId = Number(whId);
  const existingIdx = item.warehouse_ids.findIndex(id => Number(id) === strId);
  if (existingIdx > -1) {
    item.warehouse_ids.splice(existingIdx, 1);
  } else {
    item.warehouse_ids.push(whId);
  }

  item.warehouse_id = item.warehouse_ids[0] || null;
  item.combined_stock = getItemAvailableStock(item);
  item.warehouses = item.warehouse_ids.map(id => ({
    warehouse_id: id,
    stock: getProductWarehouseStock(item.product, id)
  }));

  validateItemStock(item, true);
};

const isWarehouseSelected = (itemIndex, whId) => {
  const item = invoiceItems.value[itemIndex];
  if (!item) return false;
  if (!Array.isArray(item.warehouse_ids)) {
    return Number(item.warehouse_id) === Number(whId);
  }
  return item.warehouse_ids.some(id => Number(id) === Number(whId));
};

const getSelectedWarehouseLabel = (item) => {
  if (!item) return 'Select Warehouse(s)';
  const ids = (Array.isArray(item.warehouse_ids) && item.warehouse_ids.length > 0)
    ? item.warehouse_ids
    : (item.warehouse_id ? [item.warehouse_id] : []);

  if (ids.length === 0) return 'Select Warehouse(s) (Stock: 0)';

  const combinedStock = getItemAvailableStock(item);
  const firstWh = warehouses.value.find(w => Number(w.id) === Number(ids[0]));
  const firstName = firstWh ? firstWh.name : 'Warehouse';

  if (ids.length === 1) {
    return `${firstName} (Stock: ${combinedStock})`;
  }
  return `${firstName} +${ids.length - 1} (${ids.length} WH) (Stock: ${combinedStock})`;
};

const onItemQtyChange = (index) => {
  const item = invoiceItems.value[index];
  if (item) {
    validateItemStock(item, true);
    updateItemTotal(index);
  }
};

const onItemWarehouseChange = (index) => {
  const item = invoiceItems.value[index];
  if (item) {
    validateItemStock(item, true);
    updateItemTotal(index);
  }
};

const loadProducts = async () => {
  try {
    loadingProducts.value = true;
    const response = await api.get('/sales/products-with-stock');
    if (response.data && response.data.success) {
      products.value = response.data.items || [];
      warehouses.value = response.data.warehouses || [];
      taxes.value = response.data.taxes || [];
      
      // Auto-select default warehouse and its counter
      if (warehouses.value.length > 0) {
        const defaultWh = warehouses.value.find(w => w.is_default) || warehouses.value[0];
        if (defaultWh) {
          counterWarehouseFilterId.value = defaultWh.id;
          invoiceForm.value.warehouse_id = defaultWh.id;
          if (defaultWh.counters && defaultWh.counters.length > 0) {
            invoiceForm.value.counter_id = defaultWh.counters[0].id;
          }
        }
      }
    } else {
      products.value = response.data.data || response.data;
    }
  } catch (error) {
    showNotification('Error loading products', 'error');
    console.error(error);
  } finally {
    loadingProducts.value = false;
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

const loadSalesmen = async () => {
  try {
    const response = await api.get('/employees/for-dropdown');
    const rawData = response.data.data || response.data || [];
    salesmen.value = Array.isArray(rawData)
      ? rawData.filter(s => s && s.id && s.full_name !== 'Select Sales Representative' && !String(s.full_name || '').startsWith('Select Sales Representative'))
      : [];
  } catch (error) {
    console.error('Error loading salesmen:', error);
  }
};

const searchCustomers = async (query = '') => {
  try {
    const response = await api.get('/customers', {
      params: { search: query, per_page: 10, type: 'registered' }
    });
    customerSearchResults.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error searching customers:', error);
  }
};

const debouncedCustomerSearch = debounce(() => {
  searchCustomers(customerSearch.value);
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
    } else {
      addByBarcode();
    }
  } else if (event.key === 'Escape') {
    event.preventDefault();
    isProductDropdownOpen.value = false;
    highlightedProductIndex.value = -1;
  }
};

const selectProductFromDropdown = (product) => {
  if (isProductOutOfStock(product)) {
    showToast(`Product "${product.name}" is currently Out of Stock and cannot be added to the invoice.`, 'error');
    return;
  }
  addToInvoice(product);
  productSearch.value = '';
  isProductDropdownOpen.value = false;
  highlightedProductIndex.value = -1;
  productItemRefs.value = {};
};

const addByBarcode = () => {
  const code = barcodeInput.value.trim();
  if (!code) return;

  const matchedProduct = products.value.find(p => 
    (p.barcode && p.barcode.toLowerCase() === code.toLowerCase()) || 
    (p.sku && p.sku.toLowerCase() === code.toLowerCase())
  );

  if (matchedProduct) {
    addToInvoice(matchedProduct);
    barcodeInput.value = '';
    showNotification(`Added "${matchedProduct.name}" to invoice`, 'success');
  } else {
    showNotification(`No product found with barcode/SKU: ${code}`, 'error');
  }
};

const addToInvoice = (product) => {
  if (isProductOutOfStock(product)) {
    showToast(`Product "${product.name}" is currently Out of Stock and cannot be added to the invoice.`, 'error');
    return;
  }
  let targetWarehouseId = invoiceForm.value.warehouse_id;
  if (!targetWarehouseId || targetWarehouseId === 'all') {
    // Try to find a warehouse with stock > 0
    const whWithStock = Object.keys(product.warehouse_stocks || {}).find(
      id => (product.warehouse_stocks[id] || 0) > 0
    );
    if (whWithStock) {
      targetWarehouseId = whWithStock;
    } else {
      // Fallback to default warehouse or first warehouse
      const defaultWh = warehouses.value.find(w => w.is_default) || warehouses.value[0];
      targetWarehouseId = defaultWh ? defaultWh.id : '';
    }
  }

  if (!targetWarehouseId) {
    showNotification('Please select a warehouse first', 'error');
    return;
  }

  const existingItem = invoiceItems.value.find(item => 
    item.product_id === product.product_id && 
    item.product_variation_id === product.product_variation_id &&
    item.warehouse_id === targetWarehouseId
  );

  const availableStock = product.track_inventory
    ? (product.warehouse_stocks?.[targetWarehouseId] ?? 0)
    : Infinity;
  
  if (existingItem) {
    if (product.track_inventory && existingItem.quantity >= availableStock) {
      showNotification(`Cannot add more. Only ${availableStock} items in stock.`, 'error');
      return;
    }
    existingItem.quantity += 1;
    updateItemTotal(invoiceItems.value.indexOf(existingItem));
  } else {
    if (product.track_inventory && availableStock <= 0) {
      showNotification(`Product is out of stock in selected warehouse.`, 'error');
      return;
    }
    
    // Set default tax ID and tax rate cleanly matching taxes array
    let rawTaxId = product.tax_id || product.tax_rate_id || (product.tax_ids && product.tax_ids.length > 0 ? product.tax_ids[0] : null);
    let defaultTaxRate = parseFloat(product.tax_rate || product.tax_value || 0);

    let defaultTaxId = null;
    let matchingTax = null;

    if (rawTaxId !== null && rawTaxId !== undefined) {
      matchingTax = taxes.value.find(t => String(t.id) === String(rawTaxId));
    }
    if (!matchingTax && defaultTaxRate > 0) {
      matchingTax = taxes.value.find(t => Number(t.value) === Number(defaultTaxRate));
    }

    if (matchingTax) {
      defaultTaxId = matchingTax.id;
      defaultTaxRate = parseFloat(matchingTax.value || 0);
    }

    const basePrice = getEffectiveUnitPrice(product);
    const itemSubtotal = basePrice * 1;
    const itemTax = (itemSubtotal * (defaultTaxRate / 100));

    const targetWarehouseIds = targetWarehouseId ? [targetWarehouseId] : (warehouses.value.length > 0 ? [warehouses.value[0].id] : []);

    const newItem = {
      product_id: product.product_id,
      product_variation_id: product.product_variation_id,
      warehouse_id: targetWarehouseIds[0] || null,
      warehouse_ids: targetWarehouseIds,
      warehouses: targetWarehouseIds.map(whId => ({ warehouse_id: whId, stock: getProductWarehouseStock(product, whId) })),
      name: product.name,
      sku: product.sku,
      brand_name: product.brand_name || (typeof product.brand === 'string' ? product.brand : product.brand?.name),
      category_path: product.category_path || (typeof product.category === 'string' ? product.category : product.category?.name),
      price: product.price,
      unit_price: parseFloat(product.price || 0),
      wholesale_price: parseFloat(product.wholesale_price || 0),
      original_sale_price: parseFloat(product.price || 0),
      original_wholesale_price: parseFloat(product.wholesale_price || 0),
      is_wholesale: false,
      discount_type: 'percentage',
      discount_amount: 0,
      quantity: 1,
      tax_id: defaultTaxId,
      tax_rate: defaultTaxRate,
      description: '',
      total: itemSubtotal + itemTax,
      product: product
    };
    newItem.combined_stock = getItemAvailableStock(newItem);
    invoiceItems.value.push(newItem);
  }

  calculateTotal();
};

const removeFromInvoice = (index) => {
  invoiceItems.value.splice(index, 1);
  calculateTotal();
};

const toggleLineDiscountType = (item, index) => {
  const currentType = item.discount_type || 'percentage';
  const newType = currentType === 'fixed' ? 'percentage' : 'fixed';
  item.discount_type = newType;

  const basePrice = getEffectiveUnitPrice(item);
  const itemSubtotal = (Number(item.quantity) || 1) * basePrice;
  const currentVal = parseFloat(item.discount_amount) || 0;

  if (newType === 'percentage') {
    if (currentVal > 100) {
      if (itemSubtotal > 0) {
        item.discount_amount = parseFloat(((currentVal / itemSubtotal) * 100).toFixed(2));
      } else {
        item.discount_amount = 0;
      }
    }
  } else if (newType === 'fixed') {
    if (currentVal <= 100 && itemSubtotal > 0) {
      item.discount_amount = parseFloat(((itemSubtotal * currentVal) / 100).toFixed(2));
    }
  }

  updateItemTotal(index);
};

const updateItemTotal = (index) => {
  const item = invoiceItems.value[index];
  if (!item) return;

  const basePrice = getEffectiveUnitPrice(item);
  const qty = Math.max(0, Number(item.quantity) || 0);

  const itemSubtotal = qty * basePrice;
  let rawDiscount = Number(item.discount_amount) || 0;

  if (item.discount_type === 'percentage') {
    if (rawDiscount > 100) {
      rawDiscount = 100;
      item.discount_amount = 100;
    }
  }

  const effectiveDiscount = (item.discount_type === 'percentage')
    ? (itemSubtotal * rawDiscount) / 100
    : Math.min(itemSubtotal, rawDiscount);

  const taxableAmount = Math.max(0, itemSubtotal - effectiveDiscount);
  const taxRate = Number(item.tax_rate) || 0;
  const itemTax = (taxableAmount * taxRate) / 100;

  const finalRowTotal = taxableAmount + itemTax;
  item.total = isNaN(finalRowTotal) ? 0 : Math.max(0, finalRowTotal);

  calculateTotal();
};

const updateItemTax = (item) => {
  if (item.tax_id === null || item.tax_id === '' || item.tax_id === undefined) {
    item.tax_id = null;
    item.tax_rate = 0;
  } else {
    const selectedTax = taxes.value.find(t => String(t.id) === String(item.tax_id));
    if (selectedTax) {
      item.tax_id = selectedTax.id;
      item.tax_rate = parseFloat(selectedTax.value || 0);
    } else {
      item.tax_rate = 0;
    }
  }
  updateItemTotal(invoiceItems.value.indexOf(item));
};

const toggleManualTaxType = () => {
  const newType = invoiceForm.value.tax_type === 'fixed' ? 'percentage' : 'fixed';
  invoiceForm.value.tax_type = newType;
  if (newType === 'percentage' && (parseFloat(invoiceForm.value.tax_amount) > 100)) {
    invoiceForm.value.tax_amount = 0;
  }
  calculateTotal();
};

const toggleManualDiscountType = () => {
  const newType = invoiceForm.value.discount_type === 'fixed' ? 'percentage' : 'fixed';
  invoiceForm.value.discount_type = newType;
  if (newType === 'percentage' && (parseFloat(invoiceForm.value.discount_amount) > 100)) {
    invoiceForm.value.discount_amount = 0;
  }
  calculateTotal();
};

const calculateTotal = () => {
  // Reset paid amount to total if unchanged
  invoiceForm.value.paid_amount = parseFloat(invoiceTotal.value.toFixed(2));
};

const selectCustomer = (customer) => {
  selectedCustomer.value = customer;
  invoiceForm.value.customer_id = customer.id;
  customerSearch.value = customer.name;
  customerPhone.value = customer.phone || '';
  customerEmail.value = customer.email || '';
  customerSearchResults.value = [];
  useWalletCredit.value = false;
  api.get(`/customers/${customer.id}`).then(res => {
    if (res.data) {
      if (res.data.wallet_balance !== undefined) {
        selectedCustomer.value = { ...selectedCustomer.value, wallet_balance: res.data.wallet_balance };
      }
      if (res.data.phone && !customerPhone.value) customerPhone.value = res.data.phone;
      if (res.data.email && !customerEmail.value) customerEmail.value = res.data.email;
    }
  }).catch(() => {});
};

const clearCustomer = () => {
  selectedCustomer.value = null;
  invoiceForm.value.customer_id = '';
  customerSearch.value = '';
  customerPhone.value = '';
  customerEmail.value = '';
  customerSearchResults.value = [];
  useWalletCredit.value = false;
};

const createCustomer = async () => {
  try {
    creatingCustomer.value = true;
    const payload = {
      ...newCustomer.value,
      type: 'registered'
    };
    const response = await api.post('/customers', payload);

    const customer = response.data.customer || response.data;
    selectCustomer(customer);
    closeCustomerModal();
    showNotification('Customer created successfully', 'success');
  } catch (error) {
    showNotification('Error creating customer', 'error');
  } finally {
    creatingCustomer.value = false;
  }
};

const handleCustomerSaved = (savedCustomer) => {
  if (savedCustomer && savedCustomer.id) {
    selectCustomer(savedCustomer);
  }
  showCustomerModal.value = false;
};

const closeCustomerModal = () => {
  showCustomerModal.value = false;
  newCustomer.value = { name: '', email: '', phone: '', mobile: '', address: '', city: '', state: '', tax_number: '', credit_limit: 0, is_active: true, notes: '' };
};

// Logo upload simulation
const onLogoChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    logoUrl.value = URL.createObjectURL(file);
    showNotification('Brand logo uploaded successfully', 'success');
  }
};

// Attachment upload simulation
const onAttachmentUpload = (event) => {
  const files = Array.from(event.target.files);
  files.forEach(file => {
    attachmentsList.value.push({
      name: file.name,
      size: file.size,
      type: file.type
    });
  });
  showNotification(`${files.length} attachment(s) uploaded`, 'success');
};

const removeAttachment = (index) => {
  attachmentsList.value.splice(index, 1);
};

const saveInvoice = async (shouldPrint = false) => {
  try {
    saving.value = true;
    printAfterSave.value = shouldPrint;

    const calcSubtotal = invoiceSubtotal.value || 0;
    const calcTax = totalTax.value || 0;
    const calcDiscount = totalDiscount.value || 0;
    const calcGrandTotal = invoiceTotal.value || 0;
    const calcPaid = totalReceivedAmount.value || 0;
    const calcDue = Math.max(0, calcGrandTotal - calcPaid);

    const invoiceData = {
      customer_id: invoiceForm.value.customer_id || null,
      customer_name: customerSearch.value ? customerSearch.value.trim() : null,
      customer_phone: customerPhone.value ? customerPhone.value.trim() : null,
      customer_email: customerEmail.value ? customerEmail.value.trim() : null,
      sale_number: invoiceForm.value.sale_number || null,
      category_id: invoiceForm.value.category_id || null,
      warehouse_id: invoiceForm.value.warehouse_id === 'all' ? null : invoiceForm.value.warehouse_id,
      counter_id: invoiceForm.value.counter_id || null,
      salesman_id: invoiceForm.value.salesman_id || null,
      sale_date: invoiceForm.value.sale_date,
      due_date: invoiceForm.value.due_date || null,
      order_number: invoiceForm.value.order_number || null,
      sales_mode: isGlobalWholesale.value ? 'wholesale' : 'retail',
      tax_type: invoiceForm.value.tax_type || 'percentage',
      manual_tax_type: invoiceForm.value.tax_type || 'percentage',
      manual_tax_value: parseFloat(invoiceForm.value.tax_amount) || 0,
      manual_tax_amount: parseFloat(invoiceForm.value.tax_amount) || 0,
      tax_amount: calcTax,
      discount_type: invoiceForm.value.discount_type || 'percentage',
      manual_discount_type: invoiceForm.value.discount_type || 'percentage',
      manual_discount_value: parseFloat(invoiceForm.value.discount_amount) || 0,
      manual_discount_amount: parseFloat(invoiceForm.value.discount_amount) || 0,
      discount_amount: calcDiscount,
      status: invoiceForm.value.status === 'draft' ? 'draft' : (calcPaid >= calcGrandTotal ? 'completed' : 'pending'),
      color: accentColor.value,
      subtotal: calcSubtotal,
      total_amount: calcGrandTotal,
      grand_total: calcGrandTotal,
      paid_amount: calcPaid,
      due_amount: calcDue,
      disabled_tax_ids: disabledRequiredTaxIds.value.map(id => Number(id)),
      excluded_tax_ids: disabledRequiredTaxIds.value.map(id => Number(id)),
      applied_tax_ids: requiredTaxes.value
        .filter(t => !disabledRequiredTaxIds.value.some(id => Number(id) === Number(t.id)))
        .map(t => Number(t.id)),
      use_wallet_credit: useWalletCredit.value,
      wallet_credit_applied: walletCreditToApply.value,
      payment_method: selectedPaymentMethods.value.length === 1 ? selectedPaymentMethods.value[0] : 'mixed',
      payments: (() => {
        const list = [];
        if (selectedPaymentMethods.value.includes('cash')) {
          const cashAmt = parseFloat(paymentAmounts.value.cash) || 0;
          if (cashAmt > 0) {
            list.push({
              type: 'cash',
              method: 'cash',
              amount: cashAmt
            });
          }
        }
        if (selectedPaymentMethods.value.includes('card') || selectedPaymentMethods.value.includes('bank_transfer')) {
          selectedBankIds.value.forEach(bankId => {
            const bankAmt = parseFloat(bankPaymentAmounts.value[bankId]) || 0;
            if (bankAmt > 0) {
              list.push({
                type: 'bank',
                method: selectedPaymentMethods.value.includes('card') ? 'card' : 'bank_transfer',
                bank_id: Number(bankId),
                amount: bankAmt
              });
            }
          });
        }
        return list;
      })(),
      notes: invoiceForm.value.notes,
      footer: invoiceForm.value.footer,
      attachments: attachmentsList.value.length > 0 ? attachmentsList.value : null,
      items: invoiceItems.value.map(item => ({
        product_id: item.product_id,
        product_variation_id: item.product_variation_id,
        warehouse_id: item.warehouse_id,
        quantity: item.quantity,
        unit_price: getEffectiveUnitPrice(item),
        is_wholesale: item.is_wholesale || false,
        discount_type: item.discount_type || 'percentage',
        discount_amount: item.discount_amount || 0,
        tax_id: item.tax_id || null,
        tax_rate: item.tax_rate || 0,
        description: item.description || ''
      }))
    };

    const response = await api.sales.create(invoiceData);

    if (shouldPrint) {
      showNotification('Invoice created. Opening print dialog...', 'success');
      // Open invoice in a new tab for printing
      const invoiceUrl = `/sales/invoices/${response.data.sale.id}?print=1`;
      window.open(invoiceUrl, '_blank');
      setTimeout(() => {
        router.push('/sales/invoices');
      }, 1000);
    } else {
      showNotification('Invoice saved successfully', 'success');
      setTimeout(() => {
        router.push(`/sales/invoices/${response.data.sale.id}`);
      }, 1500);
    }

  } catch (error) {
    showNotification(error.response?.data?.message || 'Error creating invoice', 'error');
    console.error('Error:', error);
  } finally {
    saving.value = false;
    printAfterSave.value = false;
  }
};

const saveAsDraft = async () => {
  const originalStatus = invoiceForm.value.status;
  invoiceForm.value.status = 'pending';
  await saveInvoice(false);
  invoiceForm.value.status = originalStatus;
};

const clearInvoice = () => {
  if (confirm('Are you sure you want to clear all invoice inputs?')) {
    invoiceItems.value = [];
    clearCustomer();
    accentColor.value = '#4f46e5';
    logoUrl.value = '';
    attachmentsList.value = [];
    invoiceForm.value = {
      customer_id: '',
      category_id: null,
      sale_date: new Date().toISOString().split('T')[0],
      due_date: getDefaultDueDate(new Date()),
      order_number: '',
      payment_method: 'cash',
      status: 'completed',
      tax_amount: 0,
      paid_amount: 0,
      notes: '',
      footer: 'Thank you for your business!'
    };
  }
};

const goBack = () => {
  router.push('/sales/invoices');
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

const clearSelectedCategories = () => {
  selectedCategories.value = [];
};

const isCategorySelected = (categoryId) => {
  return selectedCategories.value.map(id => String(id)).includes(String(categoryId));
};

const toggleCategorySelection = (categoryId) => {
  const idStr = String(categoryId);
  const index = selectedCategories.value.findIndex(id => String(id) === idStr);
  if (index > -1) {
    selectedCategories.value.splice(index, 1);
  } else {
    selectedCategories.value.push(categoryId);
  }
};

const handleClickOutside = (event) => {
  const productContainer = document.getElementById('product-search-container');
  if (productContainer && !productContainer.contains(event.target)) {
    isProductDropdownOpen.value = false;
  }

  const categoryContainer = document.getElementById('category-dropdown-container');
  if (categoryContainer && !categoryContainer.contains(event.target)) {
    isCategoryDropdownOpen.value = false;
  }

  const customerContainer = document.getElementById('customer-search-container');
  if (customerContainer && !customerContainer.contains(event.target)) {
    customerSearchResults.value = [];
  }

  const warehouseContainer = document.getElementById('warehouse-dropdown-container');
  if (warehouseContainer && !warehouseContainer.contains(event.target)) {
    isWarehouseDropdownOpen.value = false;
  }

  const paymentMethodContainer = document.getElementById('payment-method-dropdown-container');
  if (paymentMethodContainer && !paymentMethodContainer.contains(event.target)) {
    isPaymentDropdownOpen.value = false;
  }

  const bankContainer = document.getElementById('bank-dropdown-container');
  if (bankContainer && !bankContainer.contains(event.target)) {
    isBankDropdownOpen.value = false;
  }

  const counterContainer = document.getElementById('counter-dropdown-container');
  if (counterContainer && !counterContainer.contains(event.target)) {
    isCounterDropdownOpen.value = false;
  }

  const salesmanContainer = document.getElementById('salesman-dropdown-container');
  if (salesmanContainer && !salesmanContainer.contains(event.target)) {
    isSalesmanDropdownOpen.value = false;
  }

  if (openWarehouseItemIndex.value !== null) {
    const itemWhContainer = document.getElementById(`item-wh-dropdown-${openWarehouseItemIndex.value}`);
    if (itemWhContainer && !itemWhContainer.contains(event.target)) {
      openWarehouseItemIndex.value = null;
    }
  }
};

const selectWarehouse = (id) => {
  invoiceForm.value.warehouse_id = id;
  isWarehouseDropdownOpen.value = false;
};

const toggleBarcodeScanner = async () => {
  if (!isBarcodeActive.value) {
    try {
      if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        stream.getTracks().forEach(track => track.stop());
        isBarcodeActive.value = true;
        showNotification('Barcode scanner active (Camera permission granted)', 'success');
      } else {
        isBarcodeActive.value = true;
        showNotification('Barcode scanner active (System permission auto-granted)', 'success');
      }
    } catch (err) {
      showNotification('Permission denied. Cannot activate barcode scanner without camera access.', 'error');
      isBarcodeActive.value = false;
    }
  } else {
    isBarcodeActive.value = false;
    showNotification('Barcode scanner deactivated', 'info');
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
      if (activeCompany.value.company_logo) {
        logoUrl.value = `/storage/${activeCompany.value.company_logo}`;
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

const fetchNextInvoiceNumber = async () => {
  try {
    const response = await api.get('/sales/next-number');
    if (response.data && response.data.success) {
      autoGeneratedSaleNumber.value = response.data.sale_number;
      if (!isManualInvoiceNumber.value) {
        invoiceForm.value.sale_number = response.data.sale_number;
      }
    }
  } catch (error) {
    console.error('Error fetching next invoice number:', error);
  }
};

const handleWindowScrollOrResize = () => {
  openWarehouseItemIndex.value = null;
  isPaymentDropdownOpen.value = false;
  isBankDropdownOpen.value = false;
  isCounterDropdownOpen.value = false;
  isSalesmanDropdownOpen.value = false;
};

// Lifecycle

const onProductSelected = ({ product, error, query }) => {
  if (error) {
    soundService.playWarning();
    showNotification(error === 'Out of Stock' ? `Product "${product?.name || 'Item'}" is currently Out of Stock.` : `No product found matching: ${query}`, 'error');
  } else if (product) {
    soundService.playSuccess();
    addToInvoice(product);
    showNotification(`Added "${product.name}" to invoice`, 'success');
  }
};

const onProductsFetched = (newItems) => {
  const existingKeys = new Set(products.value.map(p => getProductUniqueKey(p)));
  newItems.forEach(item => {
    const itemKey = getProductUniqueKey(item);
    if (!existingKeys.has(itemKey)) {
      products.value.push(item);
      existingKeys.add(itemKey);
    }
  });
};

onMounted(async () => {
  updateDateTime();
  setInterval(updateDateTime, 1000);
  await loadBankAccounts();
  await loadProducts();
  await loadCategories();
  await loadTags();
  await loadTaxes();
  await loadSalesmen();
  await fetchNextInvoiceNumber();
  await fetchActiveCompany();
  await loadCompanyInvoiceSettings();
  document.addEventListener('click', handleClickOutside);
  window.addEventListener('scroll', handleWindowScrollOrResize, true);
  window.addEventListener('resize', handleWindowScrollOrResize);
  window.addEventListener('keydown', handleGlobalBarcodeScan);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  window.removeEventListener('scroll', handleWindowScrollOrResize, true);
  window.removeEventListener('resize', handleWindowScrollOrResize);
  window.removeEventListener('keydown', handleGlobalBarcodeScan);
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

/* Notification animations */
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from,
.notification-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>
