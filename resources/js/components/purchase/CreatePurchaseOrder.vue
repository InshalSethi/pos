<template>
  <div class="w-full bg-slate-50 dark:bg-zinc-950 min-h-screen">

    <!-- Header bar -->
    <div class="bg-white dark:bg-[#1E1E1E] border-b border-slate-200 dark:border-[#2E2E2E] px-6 py-4 shadow-sm">
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
          <button
            @click="goBack"
            class="text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 font-bold text-xs transition-colors duration-200 flex items-center space-x-1.5 focus:outline-none cursor-pointer bg-transparent border-0"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Orders</span>
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

          <!-- PO Paper Header -->
          <div class="flex justify-between items-start mb-8">
            <div class="space-y-3">
              <!-- Interactive Logo Upload -->
              <div class="w-24 h-24 bg-slate-50 dark:bg-zinc-900/60 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-xl flex items-center justify-center border-2 border-dashed border-slate-300 dark:border-zinc-700 relative group overflow-hidden cursor-pointer transition-all">
                <img v-if="logoUrl" :src="logoUrl" class="w-full h-full object-cover" />
                <div v-else class="text-slate-400 dark:text-zinc-500 text-center p-2">
                  <svg class="mx-auto h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                  <span class="text-[9px] font-bold uppercase mt-1 block">Add Logo</span>
                </div>
                <input type="file" @change="onLogoChange" class="absolute inset-0 opacity-0 cursor-pointer" />
              </div>

              <!-- Dynamic Company Metadata -->
              <div class="text-left text-xs text-slate-500 dark:text-zinc-400 space-y-0.5">
                <p class="font-bold text-slate-700 dark:text-zinc-200 text-sm mb-1.5">{{ activeCompany?.company_name || 'Sethi Enterprises' }}</p>
                <h4 class="text-[10px] font-extrabold uppercase text-slate-400 dark:text-zinc-500 tracking-wider pt-1.5 pb-0.5">Email, phone and Address</h4>
                <p v-if="activeCompany?.company_phone"><span class="font-semibold text-slate-400 dark:text-zinc-500">phone number:</span> {{ activeCompany.company_phone }}</p>
                <p><span class="font-semibold text-slate-400 dark:text-zinc-500">email:</span> {{ activeCompany?.company_email || 'sethiasad1@gmail.com' }}</p>
                <p><span class="font-semibold text-slate-400 dark:text-zinc-500">Address:</span> {{ activeCompany?.business_address || 'Enterprise Workspace Inc.' }}</p>
              </div>
            </div>

            <div class="text-right">
              <h2 class="text-2xl font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Purchase Order</h2>
              
              <!-- PO Details Fields -->
              <div class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2 text-xs text-left max-w-sm ml-auto">
                <div class="text-slate-500 dark:text-zinc-400 font-medium flex items-center">PO Number:</div>
                <div>
                  <input
                    v-model="orderForm.po_number"
                    type="text"
                    placeholder="Auto-generating..."
                    class="w-full px-2 py-1 border border-slate-300 dark:border-zinc-700 rounded text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-900 font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  />
                </div>

                <div class="text-slate-500 dark:text-zinc-400 font-medium flex items-center">Order Date:</div>
                <div>
                  <input
                    v-model="orderForm.order_date"
                    type="date"
                    class="w-full px-2 py-1 border border-slate-300 dark:border-zinc-700 rounded text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  />
                </div>

                <div class="text-slate-500 dark:text-zinc-400 font-medium flex items-center">Expected Delivery:</div>
                <div>
                  <input
                    v-model="orderForm.expected_delivery_date"
                    type="date"
                    class="w-full px-2 py-1 border border-slate-300 dark:border-zinc-700 rounded text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Bill To / Supplier Section -->
          <div class="border-t border-slate-200 dark:border-zinc-800 py-6 mb-4 flex justify-between items-start">
            <div class="w-1/2 text-left">
              <h3 class="text-xs font-extrabold uppercase text-slate-400 dark:text-zinc-500 tracking-wider mb-2">Supplier details</h3>
              
              <!-- Supplier Input Selector -->
              <div class="relative max-w-sm mb-3" id="supplier-search-container">
                <input
                  v-model="supplierSearch"
                  type="text"
                  placeholder="Type name to search supplier..."
                  class="w-full px-3 py-1.5 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200"
                  @input="debouncedSupplierSearch"
                  @focus="searchSuppliers(supplierSearch)"
                />
                
                <!-- Supplier Search Dropdown Results -->
                <div v-if="supplierSearchResults.length > 0" class="absolute z-50 bottom-full mb-1 w-full bg-white dark:bg-zinc-900 shadow-xl max-h-[185px] rounded-lg border border-slate-200 dark:border-zinc-800 py-1 text-xs overflow-y-auto custom-scrollbar">
                  <div
                    v-for="supplier in supplierSearchResults"
                    :key="supplier.id"
                    @click="selectSupplier(supplier)"
                    class="cursor-pointer py-2 px-3 hover:bg-slate-100 dark:hover:bg-zinc-800 flex justify-between items-center"
                  >
                    <div>
                      <span class="font-bold text-slate-800 dark:text-zinc-200">{{ supplier.name }}</span>
                      <p class="text-[10px] text-slate-500 dark:text-zinc-400">{{ supplier.phone || supplier.email }}</p>
                    </div>
                    <span v-if="supplier.company_name" class="text-[9px] bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-400 px-1.5 py-0.5 rounded font-mono">{{ supplier.company_name }}</span>
                  </div>
                </div>
              </div>

              <!-- Selected Supplier Details -->
              <div v-if="selectedSupplier" class="p-3 bg-slate-50 dark:bg-zinc-900/60 rounded-xl border border-slate-200 dark:border-zinc-800 text-xs space-y-1 relative max-w-sm">
                <button @click="clearSupplier" class="absolute top-2 right-2 text-rose-600 dark:text-rose-450 hover:text-rose-800 dark:hover:text-rose-350 font-semibold text-[10px] hover:underline bg-transparent border-0 cursor-pointer">Remove</button>
                <p class="font-bold text-slate-800 dark:text-zinc-100 text-sm">{{ selectedSupplier.name }}</p>
                <p v-if="selectedSupplier.company_name" class="text-slate-600 dark:text-zinc-300"><span class="font-semibold text-slate-400 dark:text-zinc-500">Company:</span> {{ selectedSupplier.company_name }}</p>
                <p v-if="selectedSupplier.phone" class="text-slate-600 dark:text-zinc-300"><span class="font-semibold text-slate-400 dark:text-zinc-500">Phone:</span> {{ selectedSupplier.phone }}</p>
                <p v-if="selectedSupplier.email" class="text-slate-600 dark:text-zinc-300"><span class="font-semibold text-slate-400 dark:text-zinc-500">Email:</span> {{ selectedSupplier.email }}</p>
              </div>
              <div v-else class="text-slate-400 dark:text-zinc-500 text-xs italic">
                No supplier selected. Type above to assign a vendor.
              </div>
            </div>

            <div class="text-right">
              <button
                @click="showSupplierModal = true"
                class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-all flex items-center space-x-1 border-0 cursor-pointer"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Add Supplier</span>
              </button>
            </div>
          </div>

          <!-- Line Items Table -->
          <div class="overflow-x-auto overflow-y-auto max-h-[60vh] border border-slate-200 dark:border-zinc-800 rounded-xl mt-2 relative custom-scrollbar">
            <table class="w-full text-xs text-left border-collapse">
              <thead class="sticky top-0 z-10 shadow-sm">
                <tr class="bg-slate-50 dark:bg-zinc-900 border-b border-slate-200 dark:border-zinc-800 text-slate-400 dark:text-zinc-400 uppercase font-extrabold tracking-wider">
                  <th class="py-3 px-3 w-6/12 bg-slate-50 dark:bg-zinc-900">Item Details / SKU</th>
                  <th class="py-3 px-2 w-2/12 text-center bg-slate-50 dark:bg-zinc-900">Qty</th>
                  <th class="py-3 px-2 w-2/12 text-right bg-slate-50 dark:bg-zinc-900">Unit Cost</th>
                  <th class="py-3 px-2 w-2/12 text-right bg-slate-50 dark:bg-zinc-900">Total Cost</th>
                  <th class="py-3 px-1 w-[40px] text-center bg-slate-50 dark:bg-zinc-900"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 dark:divide-zinc-800">
                <tr v-if="orderItems.length === 0">
                  <td colspan="5" class="py-16 text-center text-slate-400 dark:text-zinc-500 italic">
                    <svg class="mx-auto h-10 w-10 text-slate-300 dark:text-zinc-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <span>No products added. Use the filters & search list on the right to select items.</span>
                  </td>
                </tr>

                <tr v-for="(item, index) in orderItems" :key="index" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/20 group align-top">
                  <!-- Name and SKU -->
                  <td class="py-3 px-3">
                    <div class="font-bold text-slate-800 dark:text-zinc-100 text-sm">{{ item.product.name }}</div>
                    <div class="text-[10px] text-slate-500 dark:text-zinc-400 font-mono">SKU: {{ item.product.sku }}</div>
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
                    <div class="text-[9px] text-slate-400 dark:text-zinc-500 mt-1">Current Stock: {{ item.product.stock_quantity }}</div>
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

                  <!-- Total Line Cost -->
                  <td class="py-3 px-2 text-right font-bold text-slate-800 dark:text-zinc-200 text-sm align-middle">
                    ${{ item.total_cost.toFixed(2) }}
                  </td>

                  <!-- Remove Button -->
                  <td class="py-3 px-1 text-center align-middle">
                    <button
                      @click="removeFromOrder(index)"
                      class="text-slate-400 dark:text-zinc-500 hover:text-rose-600 dark:hover:text-rose-450 p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-all cursor-pointer bg-transparent border-0"
                    >
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                    </button>
                  </td>
                </tr>
              </tbody>
              <tfoot v-if="orderItems.length > 0" class="bg-slate-50 dark:bg-zinc-900/40 border-t border-slate-200 dark:border-zinc-800">
                <tr>
                  <td colspan="3" class="py-2 px-3 text-right font-semibold text-slate-500 dark:text-zinc-400">Subtotal</td>
                  <td class="py-2 px-2 text-right font-bold text-slate-800 dark:text-zinc-200">${{ orderSubtotal.toFixed(2) }}</td>
                  <td></td>
                </tr>
                <tr v-if="orderForm.tax_amount > 0">
                  <td colspan="3" class="py-2 px-3 text-right font-semibold text-slate-500 dark:text-zinc-400">Tax</td>
                  <td class="py-2 px-2 text-right font-bold text-slate-800 dark:text-zinc-200">+${{ orderForm.tax_amount.toFixed(2) }}</td>
                  <td></td>
                </tr>
                <tr v-if="orderForm.shipping_cost > 0">
                  <td colspan="3" class="py-2 px-3 text-right font-semibold text-slate-500 dark:text-zinc-400">Shipping Cost</td>
                  <td class="py-2 px-2 text-right font-bold text-slate-800 dark:text-zinc-200">+${{ orderForm.shipping_cost.toFixed(2) }}</td>
                  <td></td>
                </tr>
                <tr class="border-t border-slate-300 dark:border-zinc-800 font-bold bg-slate-100/50 dark:bg-zinc-800/30">
                  <td colspan="3" class="py-2.5 px-3 text-right text-slate-800 dark:text-zinc-200 text-xs">Total Amount</td>
                  <td class="py-2.5 px-2 text-right text-slate-900 dark:text-zinc-100 text-sm font-black">${{ orderTotal.toFixed(2) }}</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- Notes & Footer Layout -->
          <div class="border-t border-slate-200 dark:border-zinc-800 pt-6 mt-8 grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
            <div>
              <label class="block text-xs font-bold uppercase text-slate-400 dark:text-zinc-500 tracking-wider mb-2">Notes to supplier</label>
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

      <!-- Right Panel: Sidebar for Product Catalog Search (1/4 width) -->
      <div class="w-full md:w-1/4 p-6 space-y-6 flex flex-col border-l border-slate-200 dark:border-zinc-800 bg-white dark:bg-[#1E1E1E]">
          
          <!-- Section 1: Product Selection & Catalog Filters -->
          <div class="space-y-4 text-left">
            <h3 class="text-xs font-extrabold uppercase text-slate-500 dark:text-zinc-400 tracking-wider border-b border-slate-100 dark:border-zinc-800 pb-2">Catalog Search & Selection</h3>
            
            <div class="flex justify-between items-center bg-slate-50 dark:bg-zinc-900/60 px-3 py-2 rounded-xl border border-slate-100 dark:border-zinc-800">
              <div class="flex items-center space-x-2">
                <span class="text-[10px] text-slate-500 dark:text-zinc-400 font-bold uppercase">Barcode Scanner</span>
                <span 
                  class="text-[9px] font-extrabold px-1.5 py-0.5 rounded-full flex items-center gap-1 transition-all duration-200"
                  :class="isBarcodeActive ? 'text-emerald-700 dark:text-emerald-450 bg-emerald-50 dark:bg-emerald-950/40' : 'text-slate-500 dark:text-zinc-400 bg-slate-150 dark:bg-zinc-800'"
                >
                  <span class="w-1 h-1 rounded-full" :class="isBarcodeActive ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400 dark:bg-zinc-650'"></span>
                  {{ isBarcodeActive ? 'Active' : 'Inactive' }}
                </span>
              </div>
              <button
                type="button"
                @click="toggleBarcodeScanner"
                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                :class="isBarcodeActive ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-zinc-700'"
              >
                <span
                  class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                  :class="isBarcodeActive ? 'translate-x-4' : 'translate-x-0'"
                ></span>
              </button>
            </div>

            <div>
              <input
                v-model="barcodeInput"
                type="text"
                :disabled="!isBarcodeActive"
                :placeholder="isBarcodeActive ? 'Scan barcode or type SKU...' : 'Scanner inactive - Toggle ON to scan'"
                class="w-full pl-3 pr-2 py-2 border rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs transition-all duration-200"
                :class="isBarcodeActive ? 'border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200' : 'border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-800/60 text-slate-400 dark:text-slate-500 cursor-not-allowed'"
                @keydown.enter.prevent="addByBarcode"
              />
            </div>

            <!-- Search items input -->
            <div class="relative" id="product-search-container">
              <input
                v-model="productSearch"
                type="text"
                placeholder="Search products by title, SKU..."
                class="w-full pl-3 pr-8 py-2 border border-slate-300 dark:border-zinc-700 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200"
                @focus="isProductDropdownOpen = true"
              />
              <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              
              <!-- Search Results Dropdown List -->
              <div
                v-show="isProductDropdownOpen && filteredProducts.length > 0"
                class="absolute left-0 right-0 mt-1 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl shadow-xl z-50 max-h-60 overflow-y-auto py-1.5 custom-scrollbar"
              >
                <div
                  v-for="product in filteredProducts"
                  :key="product.id"
                  @click="selectProductFromDropdown(product)"
                  class="px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-zinc-800 cursor-pointer flex justify-between items-center text-xs border-b border-slate-50 dark:border-zinc-800 last:border-0 text-left"
                >
                  <div class="min-w-0 pr-4">
                    <div class="font-bold text-slate-800 dark:text-zinc-200 truncate">{{ product.name }}</div>
                    <div class="text-[10px] text-slate-400 dark:text-zinc-500">SKU: {{ product.sku }}</div>
                  </div>
                  <div class="text-right flex-shrink-0">
                    <span class="font-black text-indigo-600 dark:text-indigo-400 text-sm block">${{ product.cost_price || product.selling_price }}</span>
                    <span class="text-[10px] text-slate-500 dark:text-zinc-400">{{ product.stock_quantity }} in stock</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Section 2: Summary Totals & Calculations -->
          <div class="space-y-4 text-left">
            <h3 class="text-xs font-extrabold uppercase text-slate-500 dark:text-zinc-400 tracking-wider border-b border-slate-100 dark:border-zinc-800 pb-2">Summary & Cost details</h3>

            <div class="bg-slate-50 dark:bg-zinc-900/60 rounded-2xl p-4 border border-slate-200/80 dark:border-zinc-800/80 text-xs space-y-2.5">
              <div class="flex justify-between font-medium text-slate-600 dark:text-zinc-400">
                <span>Subtotal:</span>
                <span class="font-bold text-slate-800 dark:text-zinc-200">${{ orderSubtotal.toFixed(2) }}</span>
              </div>
              <div class="flex justify-between items-center font-medium text-slate-600 dark:text-zinc-400">
                <span>Tax Amount:</span>
                <input
                  v-model.number="orderForm.tax_amount"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-20 px-2 py-1 border border-slate-200 dark:border-zinc-700 rounded text-right focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200 text-xs"
                />
              </div>
              <div class="flex justify-between items-center font-medium text-slate-600 dark:text-zinc-400">
                <span>Shipping Cost:</span>
                <input
                  v-model.number="orderForm.shipping_cost"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-20 px-2 py-1 border border-slate-200 dark:border-zinc-700 rounded text-right focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200 text-xs"
                />
              </div>
              <div class="flex justify-between items-center text-sm font-extrabold text-slate-900 dark:text-zinc-100 border-t border-slate-200 dark:border-zinc-800 pt-2.5 mt-1">
                <span>Total Amount:</span>
                <span class="text-lg text-indigo-600 dark:text-indigo-400 font-black">${{ orderTotal.toFixed(2) }}</span>
              </div>
              <div class="flex justify-between items-center font-medium text-slate-600 dark:text-zinc-400 border-t border-slate-200 dark:border-zinc-800 pt-2.5">
                <span>Amount Paid:</span>
                <input
                  v-model.number="orderForm.amount_paid"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-20 px-2 py-1 border border-slate-200 dark:border-zinc-700 rounded text-right focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200 text-xs"
                />
              </div>
              <!-- Advance Balance Checkbox -->
              <div v-if="selectedSupplier && parseFloat(selectedSupplier.advance_balance || 0) > 0" class="bg-amber-50 dark:bg-amber-950/20 rounded-lg px-3 py-2 border border-amber-200 dark:border-amber-900/60 text-xs mt-1">
                <label class="flex items-center justify-between cursor-pointer">
                  <div class="flex items-center space-x-2">
                    <input
                      type="checkbox"
                      v-model="useAdvanceBalance"
                      class="rounded border-amber-400 text-amber-600 focus:ring-amber-500 w-3.5 h-3.5 cursor-pointer"
                    />
                    <span class="font-bold text-amber-800 dark:text-amber-300">Use Advance Balance</span>
                  </div>
                  <span class="font-extrabold text-amber-700 dark:text-amber-400">${{ parseFloat(selectedSupplier.advance_balance || 0).toFixed(2) }}</span>
                </label>
                <div v-if="useAdvanceBalance" class="mt-1.5 text-[10px] text-amber-600 dark:text-amber-400 font-medium">
                  Applying ${{ advanceToApply.toFixed(2) }} from advance → New effective due: ${{ effectiveDueAmount.toFixed(2) }}
                </div>
              </div>
              <div class="flex justify-between items-center text-sm font-extrabold text-slate-900 dark:text-zinc-100">
                <span>Due Amount:</span>
                <span 
                  class="text-base font-black transition-all"
                  :class="effectiveDueAmount > 0 ? 'text-rose-600 dark:text-rose-450' : 'text-emerald-600 dark:text-emerald-450'"
                >
                  ${{ effectiveDueAmount.toFixed(2) }}
                </span>
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
              </select>
            </div>
          </div>

        <!-- Sidebar Sticky Footer Actions -->
        <div class="p-5 border-t border-slate-200 dark:border-zinc-800 bg-slate-50/80 dark:bg-zinc-900/40 mt-auto">
          <div class="space-y-3">
            <!-- Row 1: Primary Action (Save Purchase Order) -->
            <button
              @click="saveOrder"
              :disabled="orderItems.length === 0 || saving || !selectedSupplier"
              class="w-full h-10 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm shadow-sm transition-all flex items-center justify-center space-x-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed border-0"
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
            <div class="grid grid-cols-2 gap-3">
              <button
                @click="saveAsDraft"
                :disabled="orderItems.length === 0 || saving || !selectedSupplier"
                class="w-full h-10 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 rounded-lg font-semibold text-sm transition-all flex items-center justify-center cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed border-0"
              >
                <span>Save Draft</span>
              </button>
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
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Credit Limit ($)</label>
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
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';

const router = useRouter();
const authStore = useAuthStore();

// Reactive customisations
const logoUrl = ref('');
const activeCompany = ref(null);

// Reactive data
const products = ref([]);
const suppliers = ref([]);
const orderItems = ref([]);
const selectedSupplier = ref(null);
const useAdvanceBalance = ref(false);
const isProductDropdownOpen = ref(false);
const barcodeInput = ref('');
const isBarcodeActive = ref(false);
const productSearch = ref('');
const supplierSearch = ref('');
const supplierSearchResults = ref([]);
const loadingProducts = ref(false);
const saving = ref(false);
const creatingSupplier = ref(false);
const showSupplierModal = ref(false);
const notifications = ref([]);

// Current date time
const currentDateTime = ref('');

const orderForm = ref({
  supplier_id: '',
  po_number: '',
  order_date: new Date().toISOString().split('T')[0],
  expected_delivery_date: '',
  status: 'draft',
  tax_amount: 0,
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

// Computed properties
const filteredProducts = computed(() => {
  if (!productSearch.value) return products.value;

  const search = productSearch.value.toLowerCase();
  return products.value.filter(product =>
    product.name.toLowerCase().includes(search) ||
    product.sku.toLowerCase().includes(search) ||
    (product.barcode && product.barcode.toLowerCase().includes(search))
  );
});

const orderSubtotal = computed(() => {
  if (!orderItems.value || orderItems.value.length === 0) return 0;
  return orderItems.value.reduce((sum, item) => {
    const itemTotal = parseFloat(item.total_cost) || 0;
    return sum + itemTotal;
  }, 0);
});

const orderTotal = computed(() => {
  const subtotal = orderSubtotal.value || 0;
  const tax = parseFloat(orderForm.value.tax_amount) || 0;
  const shipping = parseFloat(orderForm.value.shipping_cost) || 0;
  return subtotal + tax + shipping;
});

const dueAmount = computed(() => {
  const total = orderTotal.value || 0;
  const paid = parseFloat(orderForm.value.amount_paid) || 0;
  return Math.max(0, total - paid);
});

const advanceToApply = computed(() => {
  if (!useAdvanceBalance.value || !selectedSupplier.value) return 0;
  const advanceBal = parseFloat(selectedSupplier.value.advance_balance || 0);
  return Math.min(advanceBal, Math.max(0, orderTotal.value));
});

const effectiveDueAmount = computed(() => {
  const baseDue = dueAmount.value;
  if (useAdvanceBalance.value) {
    return Math.max(0, baseDue - advanceToApply.value);
  }
  return baseDue;
});

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

const selectProductFromDropdown = (product) => {
  addToOrder(product);
  productSearch.value = '';
  isProductDropdownOpen.value = false;
};

const addByBarcode = () => {
  const code = barcodeInput.value.trim();
  if (!code) return;

  const matchedProduct = products.value.find(p => 
    (p.barcode && p.barcode.toLowerCase() === code.toLowerCase()) || 
    (p.sku && p.sku.toLowerCase() === code.toLowerCase())
  );

  if (matchedProduct) {
    addToOrder(matchedProduct);
    barcodeInput.value = '';
    showNotification(`Added "${matchedProduct.name}" to order`, 'success');
  } else {
    showNotification(`No product found with barcode/SKU: ${code}`, 'error');
  }
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
      total_cost: parseFloat(product.cost_price || product.selling_price || 0)
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

const saveOrder = async () => {
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
      po_number: orderForm.value.po_number || null,
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
        notes: null
      }))
    };

    await api.post('/purchase-orders', orderData);

    showNotification('Purchase order created successfully', 'success');

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

const saveAsDraft = async () => {
  const originalStatus = orderForm.value.status;
  orderForm.value.status = 'draft';
  await saveOrder();
  orderForm.value.status = originalStatus;
};

const clearOrder = () => {
  if (confirm('Are you sure you want to clear all purchase order inputs?')) {
    orderItems.value = [];
    clearSupplier();
    orderForm.value.tax_amount = 0;
    orderForm.value.shipping_cost = 0;
    orderForm.value.amount_paid = 0;
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
      if (activeCompany.value.company_logo) {
        logoUrl.value = `/storage/${activeCompany.value.company_logo}`;
      }
    }
  } catch (error) {
    console.error('Error fetching active company:', error);
  }
};

const fetchNextPONumber = async () => {
  try {
    const response = await api.get('/purchase-orders/next-number');
    if (response.data && response.data.success) {
      orderForm.value.po_number = response.data.po_number;
    }
  } catch (error) {
    console.error('Error fetching next PO number:', error);
  }
};

// Lifecycle
onMounted(() => {
  updateDateTime();
  setInterval(updateDateTime, 1000);
  loadProducts();
  loadSuppliers();
  fetchActiveCompany();
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
