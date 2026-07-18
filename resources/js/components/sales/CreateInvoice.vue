<template>
  <div class="w-full bg-slate-50 dark:bg-zinc-950 min-h-screen">

    <!-- Header bar -->
    <div class="bg-white dark:bg-[#1E1E1E] border-b border-slate-200 dark:border-[#2E2E2E] px-6 py-4 shadow-sm">
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
          <button
            @click="goBack"
            class="text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 font-bold text-xs transition-colors duration-200 flex items-center space-x-1.5 focus:outline-none cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Invoices</span>
          </button>
          <span class="text-slate-300 dark:text-slate-600 select-none">|</span>
          <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Create Sales Invoice</h1>
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

          <!-- Invoice Paper Header -->
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
                <p class="font-bold text-slate-700 dark:text-zinc-200 text-sm">{{ activeCompany?.company_name || 'Sethi Enterprises' }}</p>
                <p>{{ activeCompany?.business_address || 'Enterprise Workspace Inc.' }}</p>
                <p v-if="activeCompany?.company_phone">{{ activeCompany.company_phone }}</p>
                <p>{{ activeCompany?.company_email || 'sethiasad1@gmail.com' }}</p>
              </div>
            </div>

            <div class="text-right">
              <h2 class="text-2xl font-black uppercase tracking-wider transition-all duration-300" :style="{ color: accentColor }">Invoice</h2>
              
              <!-- Invoice Details Fields -->
              <div class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2 text-xs text-left max-w-sm ml-auto">
                <div class="text-slate-500 dark:text-zinc-400 font-medium flex items-center">Invoice Number:</div>
                <div>
                  <input
                    v-model="invoiceForm.sale_number"
                    type="text"
                    placeholder="Auto-generating..."
                    class="w-full px-2 py-1 border border-slate-300 dark:border-zinc-700 rounded text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-900 font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  />
                </div>

                <div class="text-slate-500 dark:text-zinc-400 font-medium flex items-center">Order Number:</div>
                <div>
                  <input
                    v-model="invoiceForm.order_number"
                    type="text"
                    placeholder="Enter order reference"
                    class="w-full px-2 py-1 border border-slate-300 dark:border-zinc-700 rounded text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  />
                </div>

                <div class="text-slate-500 dark:text-zinc-400 font-medium flex items-center">Invoice Date:</div>
                <div>
                  <input
                    v-model="invoiceForm.sale_date"
                    type="date"
                    class="w-full px-2 py-1 border border-slate-300 dark:border-zinc-700 rounded text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  />
                </div>

                <div class="text-slate-500 dark:text-zinc-400 font-medium flex items-center">Due Date:</div>
                <div>
                  <input
                    v-model="invoiceForm.due_date"
                    type="date"
                    class="w-full px-2 py-1 border border-slate-300 dark:border-zinc-700 rounded text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  />
                </div>

                <div class="text-slate-500 dark:text-zinc-400 font-medium flex items-center">Warehouse:</div>
                <div>
                  <div class="relative" id="warehouse-dropdown-container">
                    <button
                      type="button"
                      @click.stop="isWarehouseDropdownOpen = !isWarehouseDropdownOpen"
                      class="w-full px-2 py-1 border border-slate-300 dark:border-zinc-700 rounded text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer text-left flex justify-between items-center text-xs"
                    >
                      <span class="truncate">{{ selectedWarehouseName }}</span>
                      <svg class="h-3.5 w-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                      </svg>
                    </button>

                    <!-- Warehouse Custom Dropdown List -->
                    <div
                      v-if="isWarehouseDropdownOpen"
                      class="absolute z-50 bottom-full mb-1 w-full bg-white dark:bg-zinc-900 shadow-xl max-h-[185px] rounded-lg border border-slate-200 dark:border-zinc-800 py-1 text-xs overflow-y-auto custom-scrollbar"
                    >
                      <div
                        @click="selectWarehouse('all')"
                        class="cursor-pointer py-2 px-3 hover:bg-slate-100 dark:hover:bg-zinc-800 flex justify-between items-center"
                        :class="{ 'bg-slate-50 dark:bg-zinc-800 font-semibold text-indigo-600 dark:text-indigo-400': selectedWarehouseId === 'all' }"
                      >
                        <span>All Warehouses</span>
                      </div>
                      <div
                        v-for="wh in warehouses"
                        :key="wh.id"
                        @click="selectWarehouse(wh.id)"
                        class="cursor-pointer py-2 px-3 hover:bg-slate-100 dark:hover:bg-zinc-800 flex justify-between items-center border-t border-slate-50 dark:border-zinc-800"
                        :class="{ 'bg-slate-50 dark:bg-zinc-800 font-semibold text-indigo-600 dark:text-indigo-400': selectedWarehouseId === wh.id }"
                      >
                        <span>{{ wh.name }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Bill To Section -->
          <div class="border-t border-slate-200 dark:border-zinc-800 py-6 mb-4 flex justify-between items-start">
            <div class="w-1/2 text-left">
              <h3 class="text-xs font-extrabold uppercase text-slate-400 dark:text-zinc-500 tracking-wider mb-2">Bill To</h3>
              
              <!-- Customer Input Selector -->
              <div class="relative max-w-sm mb-3" id="customer-search-container">
                <input
                  v-model="customerSearch"
                  type="text"
                  placeholder="Type name to search customer..."
                  class="w-full px-3 py-1.5 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200"
                  @input="debouncedCustomerSearch"
                  @focus="searchCustomers(customerSearch)"
                />
                
                <!-- Customer Search Dropdown Results -->
                <div v-if="customerSearchResults.length > 0" class="absolute z-50 bottom-full mb-1 w-full bg-white dark:bg-zinc-900 shadow-xl max-h-[185px] rounded-lg border border-slate-200 dark:border-zinc-800 py-1 text-xs overflow-y-auto custom-scrollbar">
                  <div
                    v-for="customer in customerSearchResults"
                    :key="customer.id"
                    @click="selectCustomer(customer)"
                    class="cursor-pointer py-2 px-3 hover:bg-slate-100 dark:hover:bg-zinc-800 flex justify-between items-center"
                  >
                    <div>
                      <span class="font-bold text-slate-800 dark:text-zinc-200">{{ customer.name }}</span>
                      <p class="text-[10px] text-slate-500 dark:text-zinc-400">{{ customer.phone || customer.email }}</p>
                    </div>
                    <span v-if="customer.tax_number" class="text-[9px] bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-400 px-1.5 py-0.5 rounded font-mono">TAX: {{ customer.tax_number }}</span>
                  </div>
                </div>
              </div>

              <!-- Selected Customer Details -->
              <div v-if="selectedCustomer" class="p-3 bg-slate-50 dark:bg-zinc-900/60 rounded-xl border border-slate-200 dark:border-zinc-800 text-xs space-y-1 relative max-w-sm">
                <button @click="clearCustomer" class="absolute top-2 right-2 text-rose-600 dark:text-rose-450 hover:text-rose-800 dark:hover:text-rose-350 font-semibold text-[10px] hover:underline">Remove</button>
                <p class="font-bold text-slate-800 dark:text-zinc-100 text-sm">{{ selectedCustomer.name }}</p>
                <p v-if="selectedCustomer.phone" class="text-slate-600 dark:text-zinc-300"><span class="font-semibold text-slate-400 dark:text-zinc-500">Phone:</span> {{ selectedCustomer.phone }}</p>
                <p v-if="selectedCustomer.email" class="text-slate-600 dark:text-zinc-300"><span class="font-semibold text-slate-400 dark:text-zinc-500">Email:</span> {{ selectedCustomer.email }}</p>
                <p v-if="selectedCustomer.address" class="text-slate-600 dark:text-zinc-300 leading-relaxed"><span class="font-semibold text-slate-400 dark:text-zinc-500">Address:</span> {{ selectedCustomer.address }} {{ selectedCustomer.city ? ', ' + selectedCustomer.city : '' }}</p>
                <p v-if="selectedCustomer.tax_number" class="text-slate-600 dark:text-zinc-300"><span class="font-semibold text-slate-400 dark:text-zinc-500">Tax ID:</span> {{ selectedCustomer.tax_number }}</p>
              </div>
              <div v-else class="text-slate-400 dark:text-zinc-500 text-xs italic">
                No customer selected. Type above to assign a recipient.
              </div>
            </div>

            <div class="text-right">
              <button
                @click="showCustomerModal = true"
                class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-all flex items-center space-x-1"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Add Customer</span>
              </button>
            </div>
          </div>

          <!-- Line Items Table -->
          <div class="overflow-x-auto overflow-y-auto max-h-[60vh] border border-slate-200 dark:border-zinc-800 rounded-xl mt-2 relative custom-scrollbar">
            <table class="w-full text-xs text-left border-collapse">
              <thead class="sticky top-0 z-10 shadow-sm">
                <tr class="bg-slate-50 dark:bg-zinc-900 border-b border-slate-200 dark:border-zinc-800 text-slate-400 dark:text-zinc-400 uppercase font-extrabold tracking-wider">
                  <th class="py-3 px-3 w-4/12 bg-slate-50 dark:bg-zinc-900">Item Details / Description</th>
                  <th class="py-3 px-2 w-1/12 text-center bg-slate-50 dark:bg-zinc-900">Qty</th>
                  <th class="py-3 px-2 w-1.5/12 text-right bg-slate-50 dark:bg-zinc-900">Price</th>
                  <th class="py-3 px-2 w-1.5/12 text-center bg-slate-50 dark:bg-zinc-900">W.S Price</th>
                  <th class="py-3 px-2 w-1.5/12 text-center bg-slate-50 dark:bg-zinc-900">Tax</th>
                  <th class="py-3 px-2 w-1.5/12 text-right bg-slate-50 dark:bg-zinc-900">Discount</th>
                  <th class="py-3 px-2 w-1.5/12 text-right bg-slate-50 dark:bg-zinc-900">Amount</th>
                  <th class="py-3 px-1 w-[40px] text-center bg-slate-50 dark:bg-zinc-900"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 dark:divide-zinc-800">
                <tr v-if="invoiceItems.length === 0">
                  <td colspan="8" class="py-16 text-center text-slate-400 dark:text-zinc-500 italic">
                    <svg class="mx-auto h-10 w-10 text-slate-300 dark:text-zinc-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <span>No products added. Use the filters & search list on the right to select items.</span>
                  </td>
                </tr>

                <tr v-for="(item, index) in invoiceItems" :key="index" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/20 group align-top">
                  <!-- Name and Description -->
                  <td class="py-3 px-2">
                    <div class="flex items-center justify-between mb-1">
                      <div class="font-bold text-slate-800 dark:text-zinc-100 text-sm">{{ item.name }}</div>
                      
                      <!-- W.S Toggle Switch -->
                      <label class="inline-flex items-center cursor-pointer select-none">
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
                    <div class="text-[10px] text-slate-500 dark:text-zinc-400 font-mono mb-1.5">SKU: {{ item.sku }}</div>
                    <textarea
                      v-model="item.description"
                      placeholder="Add line item description / details..."
                      rows="2"
                      class="w-full bg-slate-50/50 dark:bg-zinc-900/60 hover:bg-slate-100/80 dark:hover:bg-zinc-800/80 focus:bg-white dark:focus:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded px-2 py-1 text-slate-600 dark:text-zinc-300 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-[10px]"
                    ></textarea>
                  </td>

                  <!-- Qty -->
                  <td class="py-3 px-2 text-center">
                    <input
                      v-model.number="item.quantity"
                      type="number"
                      min="1"
                      :max="getProductStock(item.product)"
                      class="w-14 px-1.5 py-1 text-center border border-slate-300 dark:border-zinc-700 rounded focus:outline-none focus:ring-1 focus:ring-indigo-500 font-bold bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200"
                      @input="updateItemTotal(index)"
                    />
                    <div class="text-[9px] text-slate-400 dark:text-zinc-500 mt-1">Stock: {{ getProductStock(item.product) }}</div>
                  </td>

                  <!-- Unit Price -->
                  <td class="py-3 px-2 text-right">
                    <input
                      v-model.number="item.unit_price"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-20 px-1.5 py-1 text-right border border-slate-300 dark:border-zinc-700 rounded focus:outline-none focus:ring-1 focus:ring-indigo-500 font-semibold transition-all duration-200"
                      :class="item.is_wholesale ? 'bg-slate-100 dark:bg-zinc-800 text-slate-400 dark:text-zinc-500 opacity-60' : 'bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200'"
                      :readonly="item.is_wholesale"
                      @input="updateItemTotal(index)"
                    />
                  </td>

                  <!-- W.S Price -->
                  <td class="py-3 px-2 text-right">
                    <input
                      v-model.number="item.wholesale_price"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-20 px-1.5 py-1 text-right border border-slate-300 dark:border-zinc-700 rounded focus:outline-none focus:ring-1 focus:ring-indigo-500 font-semibold transition-all duration-200"
                      :class="!item.is_wholesale ? 'bg-slate-100 dark:bg-zinc-800 text-slate-400 dark:text-zinc-500 opacity-60' : 'bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200'"
                      :readonly="!item.is_wholesale"
                      @input="updateItemTotal(index)"
                    />
                  </td>

                  <!-- Line Tax selector -->
                  <td class="py-3 px-2 text-center">
                    <select
                      v-model="item.tax_id"
                      class="w-20 px-1 py-1 border border-slate-300 dark:border-zinc-700 rounded text-[11px] focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200"
                      @change="updateItemTax(item)"
                    >
                      <option :value="null">No Tax</option>
                      <option v-for="tax in taxes" :key="tax.id" :value="tax.id">
                        {{ tax.name }} ({{ tax.value }}%)
                      </option>
                    </select>
                    <div v-if="item.tax_rate" class="text-[9px] text-slate-500 dark:text-zinc-400 mt-1">Rate: {{ item.tax_rate }}%</div>
                  </td>

                  <!-- Line Discount -->
                  <td class="py-3 px-2 text-right">
                    <input
                      v-model.number="item.discount_amount"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-16 px-1.5 py-1 text-right border border-slate-300 dark:border-zinc-700 rounded focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200"
                      @input="updateItemTotal(index)"
                    />
                  </td>

                  <!-- Total Line Price -->
                  <td class="py-3 px-2 text-right font-bold text-slate-800 dark:text-zinc-200 text-sm">
                    ${{ item.total.toFixed(2) }}
                  </td>

                  <!-- Remove Button -->
                  <td class="py-3 px-1 text-center">
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
              </tbody>
              <tfoot v-if="invoiceItems.length > 0" class="bg-slate-50 dark:bg-zinc-900/40 border-t border-slate-200 dark:border-zinc-800">
                <tr>
                  <td colspan="5" class="py-2 px-3 text-right font-semibold text-slate-500 dark:text-zinc-400">Subtotal</td>
                  <td colspan="2" class="py-2 px-2 text-right font-bold text-slate-800 dark:text-zinc-200">${{ invoiceSubtotal.toFixed(2) }}</td>
                  <td></td>
                </tr>
                <tr v-if="totalDiscount > 0">
                  <td colspan="5" class="py-2 px-3 text-right font-semibold text-slate-500 dark:text-zinc-400">Discount</td>
                  <td colspan="2" class="py-2 px-2 text-right font-bold text-rose-600 dark:text-rose-400">-${{ totalDiscount.toFixed(2) }}</td>
                  <td></td>
                </tr>
                <tr v-if="totalTax > 0">
                  <td colspan="5" class="py-2 px-3 text-right font-semibold text-slate-500 dark:text-zinc-400">Tax</td>
                  <td colspan="2" class="py-2 px-2 text-right font-bold text-slate-800 dark:text-zinc-200">+${{ totalTax.toFixed(2) }}</td>
                  <td></td>
                </tr>
                <tr class="border-t border-slate-300 dark:border-zinc-800 font-bold bg-slate-100/50 dark:bg-zinc-800/30">
                  <td colspan="5" class="py-2.5 px-3 text-right text-slate-800 dark:text-zinc-200 text-xs">Total Amount</td>
                  <td colspan="2" class="py-2.5 px-2 text-right text-slate-900 dark:text-zinc-100 text-sm font-black">${{ invoiceTotal.toFixed(2) }}</td>
                  <td></td>
                </tr>
                <tr>
                  <td colspan="5" class="py-2 px-3 text-right font-semibold text-slate-500 dark:text-zinc-400">Amount Paid</td>
                  <td colspan="2" class="py-2 px-2 text-right font-bold text-emerald-600 dark:text-emerald-400">${{ (invoiceForm.paid_amount || 0).toFixed(2) }}</td>
                  <td></td>
                </tr>
                <tr v-if="dueAmount > 0">
                  <td colspan="5" class="py-2 px-3 text-right font-extrabold text-rose-600 dark:text-rose-400">Remaining Due Amount</td>
                  <td colspan="2" class="py-2 px-2 text-right font-extrabold text-rose-700 dark:text-rose-300 bg-rose-50/80 dark:bg-rose-950/20">${{ dueAmount.toFixed(2) }}</td>
                  <td></td>
                </tr>
                <tr v-if="changeAmount > 0">
                  <td colspan="5" class="py-2 px-3 text-right font-extrabold text-emerald-600 dark:text-emerald-450">Change / Refund</td>
                  <td colspan="2" class="py-2 px-2 text-right font-extrabold text-emerald-700 dark:text-emerald-300 bg-emerald-50/80 dark:bg-emerald-950/20">${{ changeAmount.toFixed(2) }}</td>
                  <td></td>
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

      <!-- Right Panel: Sidebar for Product Catalog Search (1/4 width) -->
      <div class="w-full md:w-1/4 p-6 space-y-6 flex flex-col border-l border-slate-200 dark:border-zinc-800 bg-white dark:bg-[#1E1E1E]">
          
          <!-- Section 1: Product Selection & Catalog Filters -->
          <div class="space-y-4">
            <h3 class="text-xs font-extrabold uppercase text-slate-500 dark:text-zinc-400 tracking-wider border-b border-slate-100 dark:border-zinc-800 pb-2 text-left">Catalog Search & Selection</h3>
            
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
                placeholder="Search products by title, code..."
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
                  :key="product.key"
                  @click="selectProductFromDropdown(product)"
                  class="px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-zinc-800 cursor-pointer flex justify-between items-center text-xs border-b border-slate-50 dark:border-zinc-800 last:border-0 text-left"
                >
                  <div class="min-w-0 pr-4">
                    <div class="font-bold text-slate-800 dark:text-zinc-200 truncate">{{ product.name }}</div>
                    <div class="text-[10px] text-slate-400 dark:text-zinc-500">SKU: {{ product.sku }}</div>
                  </div>
                  <div class="text-right flex-shrink-0">
                    <span class="font-black text-emerald-600 dark:text-emerald-400 text-sm block">${{ product.price }}</span>
                    <span class="text-[10px] text-slate-500 dark:text-zinc-400">{{ getProductStock(product) }} in stock</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Custom Category Selection -->
            <div class="relative" id="category-dropdown-container">
              <button
                type="button"
                @click="isCategoryDropdownOpen = !isCategoryDropdownOpen"
                class="w-full flex items-center justify-between px-3 py-2 bg-slate-50 dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs font-semibold text-slate-700 dark:text-zinc-300 cursor-pointer text-left"
              >
                <span>{{ categoryDropdownLabel }}</span>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': isCategoryDropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div
                v-show="isCategoryDropdownOpen"
                class="absolute left-0 right-0 mt-1 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl shadow-xl z-50 max-h-60 overflow-y-auto p-2 custom-scrollbar"
              >
                <label class="flex items-center space-x-2 px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-zinc-800 rounded-lg cursor-pointer text-xs">
                  <input
                    type="checkbox"
                    :checked="selectedCategories.length === 0"
                    @change="clearSelectedCategories"
                    class="rounded text-indigo-600 focus:ring-indigo-500 dark:bg-zinc-950 dark:border-zinc-700"
                  />
                  <span class="font-medium text-slate-700 dark:text-zinc-300">All Categories</span>
                </label>
                <div class="border-t border-slate-100 dark:border-zinc-800 my-1"></div>
                <label
                  v-for="category in categories"
                  :key="category.id"
                  class="flex items-center space-x-2 px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-zinc-800 rounded-lg cursor-pointer text-xs"
                >
                  <input
                    type="checkbox"
                    :checked="isCategorySelected(category.id)"
                    @change="toggleCategorySelection(category.id)"
                    class="rounded text-indigo-600 focus:ring-indigo-500 dark:bg-zinc-950 dark:border-zinc-700"
                  />
                  <span class="font-medium text-slate-700 dark:text-zinc-300">{{ category.name }}</span>
                </label>
              </div>
            </div>
          </div>



          <!-- Section 3: Summary Totals & Calculations -->
          <div class="space-y-4">
            <h3 class="text-xs font-extrabold uppercase text-slate-500 dark:text-zinc-400 tracking-wider border-b border-slate-100 dark:border-zinc-800 pb-2 text-left">Summary & Details</h3>

            <div class="bg-slate-50 dark:bg-zinc-900/60 rounded-2xl p-4 border border-slate-200/80 dark:border-zinc-800/80 text-xs space-y-2.5">
              <div class="flex justify-between font-medium text-slate-600 dark:text-zinc-400">
                <span>Subtotal:</span>
                <span class="font-bold text-slate-800 dark:text-zinc-200">${{ invoiceSubtotal.toFixed(2) }}</span>
              </div>
              <div class="flex justify-between font-medium text-slate-600 dark:text-zinc-400">
                <span>Line Discounts:</span>
                <span class="font-bold text-slate-800 dark:text-zinc-200">-${{ totalDiscount.toFixed(2) }}</span>
              </div>
              <div class="flex justify-between font-medium text-slate-600 dark:text-zinc-400">
                <span>Line Taxes:</span>
                <span class="font-bold text-slate-800 dark:text-zinc-200">+${{ totalTax.toFixed(2) }}</span>
              </div>
              <div class="flex justify-between items-center text-sm font-extrabold text-slate-900 dark:text-zinc-100 border-t border-slate-200 dark:border-zinc-800 pt-2.5 mt-1">
                <span>Total Amount:</span>
                <span class="text-lg transition-all duration-300" :style="{ color: accentColor }">${{ invoiceTotal.toFixed(2) }}</span>
              </div>
            </div>

            <!-- Payment configurations -->
            <div class="grid grid-cols-2 gap-3 text-left">
              <div>
                <label class="text-[10px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wide mb-1.5 block">Payment Method</label>
                <select
                  v-model="invoiceForm.payment_method"
                  class="w-full px-2.5 py-2 border border-slate-200/80 dark:border-zinc-700 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs cursor-pointer bg-white dark:bg-zinc-900 text-slate-800 dark:text-zinc-200"
                >
                  <option value="cash">Cash</option>
                  <option value="card">Card</option>
                  <option value="bank_transfer">Bank Transfer</option>
                  <option value="mobile_payment">Mobile Payment</option>
                  <option value="mixed">Mixed</option>
                </select>
              </div>

              <div>
                <label class="text-[10px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wide mb-1.5 block">Amount Paid</label>
                <input
                  v-model.number="invoiceForm.paid_amount"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-2.5 py-1.5 border border-slate-200/80 dark:border-zinc-700 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs font-bold text-slate-800 dark:text-zinc-200 bg-white dark:bg-zinc-900"
                  :placeholder="invoiceTotal.toFixed(2)"
                />
              </div>
            </div>

            <div v-if="changeAmount > 0" class="bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 rounded-xl px-3 py-2 border border-emerald-250 dark:border-emerald-900/60 text-xs font-bold text-left flex justify-between">
              <span>Change / Refund:</span>
              <span>${{ changeAmount.toFixed(2) }}</span>
            </div>

            <div v-if="dueAmount > 0" class="bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-450 rounded-xl px-3 py-2 border border-rose-250 dark:border-rose-900/60 text-xs font-bold text-left flex justify-between">
              <span>Remaining Due Amount:</span>
              <span>${{ dueAmount.toFixed(2) }}</span>
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

    </div>

    <!-- Quick Customer Creation Modal -->
    <div v-if="showCustomerModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-md overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4 transition-all duration-300">
      <div class="relative mx-auto p-6 border border-slate-100 dark:border-zinc-800 w-full max-w-lg shadow-2xl rounded-xl bg-white dark:bg-zinc-900 text-left transition-all duration-300">
        
        <!-- Sleek Close Icon Button -->
        <button
          type="button"
          @click="closeCustomerModal"
          class="absolute top-4 right-4 text-slate-400 dark:text-zinc-500 hover:text-slate-600 dark:hover:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 p-1.5 rounded-lg transition-all"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <div class="mb-5 pb-3 border-b border-slate-100 dark:border-zinc-800">
          <h3 class="text-xs font-bold text-slate-800 dark:text-zinc-100 uppercase tracking-wider">Add New Customer</h3>
        </div>

        <form @submit.prevent="createCustomer" class="space-y-4">
          <div>
            <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Full Name *</label>
            <input
              v-model="newCustomer.name"
              type="text"
              required
              placeholder="e.g. John Doe"
              class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Email</label>
              <input
                v-model="newCustomer.email"
                type="email"
                placeholder="e.g. john@example.com"
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
              />
            </div>
            <div>
              <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Phone</label>
              <input
                v-model="newCustomer.phone"
                type="text"
                placeholder="e.g. +1 555 1234"
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Mobile</label>
              <input
                v-model="newCustomer.mobile"
                type="text"
                placeholder="e.g. +1 555 5678"
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
              />
            </div>
            <div>
              <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Tax Number / GSTIN</label>
              <input
                v-model="newCustomer.tax_number"
                type="text"
                placeholder="e.g. GSTIN12345"
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
              />
            </div>
          </div>

          <div>
            <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Address</label>
            <textarea
              v-model="newCustomer.address"
              rows="2"
              placeholder="Street address, suite, apartment..."
              class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
            ></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">City</label>
              <input
                v-model="newCustomer.city"
                type="text"
                placeholder="e.g. New York"
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
              />
            </div>
            <div>
              <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">State</label>
              <input
                v-model="newCustomer.state"
                type="text"
                placeholder="e.g. California"
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Credit Limit ($)</label>
              <input
                v-model="newCustomer.credit_limit"
                type="number"
                step="0.01"
                min="0"
                placeholder="0.00"
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
              />
            </div>
            <div>
              <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Status</label>
              <select
                v-model="newCustomer.is_active"
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 bg-white dark:bg-zinc-950 transition-all"
              >
                <option :value="true">Active</option>
                <option :value="false">Inactive</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Notes</label>
            <textarea
              v-model="newCustomer.notes"
              rows="2"
              placeholder="Additional notes about the customer..."
              class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
            ></textarea>
          </div>

          <div class="flex justify-end space-x-3 pt-3.5 border-t border-slate-100 dark:border-zinc-800 mt-2">
            <button
              type="button"
              @click="closeCustomerModal"
              class="px-4 h-9 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 rounded-lg text-xs font-semibold transition-all cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="!newCustomer.name || creatingCustomer"
              class="px-4 h-9 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ creatingCustomer ? 'Creating...' : 'Add Customer' }}
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
        <button @click="removeNotification(notification.id)" class="flex-shrink-0 p-1 rounded-md text-slate-400 hover:text-white hover:bg-white/10 transition-all focus:outline-none cursor-pointer">
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
import { useToast } from '@/composables/useToast';
import api from '@/services/api';

const router = useRouter();
const authStore = useAuthStore();
const { showToast } = useToast();

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
const selectedWarehouseId = ref('all');
const isWarehouseDropdownOpen = ref(false);
const isBarcodeActive = ref(false);
const productSearch = ref('');
const customerSearch = ref('');
const customerSearchResults = ref([]);
const loadingProducts = ref(false);
const saving = ref(false);
const printAfterSave = ref(false);
const creatingCustomer = ref(false);
const showCustomerModal = ref(false);
const notifications = ref([]);

const invoiceForm = ref({
  customer_id: '',
  sale_number: '',
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
  if (selectedWarehouseId.value === 'all') {
    return 'All Warehouses';
  }
  const wh = warehouses.value.find(w => w.id === selectedWarehouseId.value);
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
    const basePrice = item.is_wholesale ? (item.wholesale_price || 0) : (item.unit_price || 0);
    return sum + (item.quantity * basePrice);
  }, 0);
});

const totalDiscount = computed(() => {
  return invoiceItems.value.reduce((sum, item) => sum + (item.discount_amount || 0), 0);
});

const totalTax = computed(() => {
  return invoiceItems.value.reduce((sum, item) => {
    const basePrice = item.is_wholesale ? (item.wholesale_price || 0) : (item.unit_price || 0);
    const itemSubtotal = item.quantity * basePrice;
    const itemDiscount = item.discount_amount || 0;
    const taxRate = item.tax_rate || 0;
    return sum + ((itemSubtotal - itemDiscount) * (taxRate / 100));
  }, 0);
});

const invoiceTotal = computed(() => {
  return invoiceSubtotal.value - totalDiscount.value + totalTax.value;
});

const changeAmount = computed(() => {
  return Math.max(0, (invoiceForm.value.paid_amount || 0) - invoiceTotal.value);
});

const dueAmount = computed(() => {
  return Math.max(0, invoiceTotal.value - (invoiceForm.value.paid_amount || 0));
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

watch(() => invoiceTotal.value, (newTotal) => {
  if (!invoiceForm.value.paid_amount || invoiceForm.value.paid_amount === 0) {
    invoiceForm.value.paid_amount = parseFloat(newTotal.toFixed(2));
  }
});

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

const getProductStock = (product) => {
  if (!product || !product.track_inventory) return '∞';
  if (!selectedWarehouseId.value || selectedWarehouseId.value === 'all') return product.total_stock;
  return product.warehouse_stocks[selectedWarehouseId.value] ?? 0;
};

const loadProducts = async () => {
  try {
    loadingProducts.value = true;
    const response = await api.get('/sales/products-with-stock');
    if (response.data && response.data.success) {
      products.value = response.data.items || [];
      warehouses.value = response.data.warehouses || [];
      taxes.value = response.data.taxes || [];
      
      // Select default warehouse
      selectedWarehouseId.value = 'all';
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

const searchCustomers = async (query = '') => {
  try {
    const response = await api.get('/customers', {
      params: { search: query, per_page: 10 }
    });
    customerSearchResults.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error searching customers:', error);
  }
};

const debouncedCustomerSearch = debounce(() => {
  searchCustomers(customerSearch.value);
}, 300);

const selectProductFromDropdown = (product) => {
  addToInvoice(product);
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
    addToInvoice(matchedProduct);
    barcodeInput.value = '';
    showNotification(`Added "${matchedProduct.name}" to invoice`, 'success');
  } else {
    showNotification(`No product found with barcode/SKU: ${code}`, 'error');
  }
};

const addToInvoice = (product) => {
  let targetWarehouseId = selectedWarehouseId.value;
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
    
    // Set default tax ID and tax rate
    const defaultTaxId = product.tax_ids && product.tax_ids.length > 0 ? product.tax_ids[0] : null;
    let defaultTaxRate = parseFloat(product.tax_rate || 0);

    invoiceItems.value.push({
      product_id: product.product_id,
      product_variation_id: product.product_variation_id,
      warehouse_id: targetWarehouseId,
      name: product.name,
      sku: product.sku,
      price: product.price,
      unit_price: parseFloat(product.price),
      wholesale_price: product.wholesale_price || 0,
      is_wholesale: false,
      discount_amount: 0,
      quantity: 1,
      tax_id: defaultTaxId,
      tax_rate: defaultTaxRate,
      description: '',
      total: parseFloat(product.price),
      product: product
    });
  }

  calculateTotal();
};

const removeFromInvoice = (index) => {
  invoiceItems.value.splice(index, 1);
  calculateTotal();
};

const updateItemTotal = (index) => {
  const item = invoiceItems.value[index];
  const basePrice = item.is_wholesale ? (item.wholesale_price || 0) : (item.unit_price || 0);
  const itemSubtotal = item.quantity * basePrice;
  const itemDiscount = item.discount_amount || 0;
  const taxRate = item.tax_rate || 0;
  
  item.total = itemSubtotal - itemDiscount + ((itemSubtotal - itemDiscount) * (taxRate / 100));
  calculateTotal();
};

const updateItemTax = (item) => {
  if (item.tax_id === null) {
    item.tax_rate = 0;
  } else {
    const selectedTax = taxes.value.find(t => t.id === item.tax_id);
    item.tax_rate = selectedTax ? parseFloat(selectedTax.value) : 0;
  }
  updateItemTotal(invoiceItems.value.indexOf(item));
};

const calculateTotal = () => {
  // Reset paid amount to total if unchanged
  invoiceForm.value.paid_amount = parseFloat(invoiceTotal.value.toFixed(2));
};

const selectCustomer = (customer) => {
  selectedCustomer.value = customer;
  invoiceForm.value.customer_id = customer.id;
  customerSearch.value = customer.name;
  customerSearchResults.value = [];
};

const clearCustomer = () => {
  selectedCustomer.value = null;
  invoiceForm.value.customer_id = '';
  customerSearch.value = '';
  customerSearchResults.value = [];
};

const createCustomer = async () => {
  try {
    creatingCustomer.value = true;
    const response = await api.post('/customers', newCustomer.value);

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

    const invoiceData = {
      customer_id: invoiceForm.value.customer_id || null,
      sale_number: invoiceForm.value.sale_number || null,
      category_id: invoiceForm.value.category_id || null,
      warehouse_id: selectedWarehouseId.value === 'all' ? null : selectedWarehouseId.value,
      sale_date: invoiceForm.value.sale_date,
      due_date: invoiceForm.value.due_date || null,
      order_number: invoiceForm.value.order_number || null,
      status: invoiceForm.value.status,
      color: accentColor.value,
      subtotal: invoiceSubtotal.value,
      tax_amount: totalTax.value,
      discount_amount: totalDiscount.value,
      total_amount: invoiceTotal.value,
      paid_amount: invoiceForm.value.paid_amount || invoiceTotal.value,
      payment_method: invoiceForm.value.payment_method,
      notes: invoiceForm.value.notes,
      footer: invoiceForm.value.footer,
      attachments: attachmentsList.value.length > 0 ? attachmentsList.value : null,
      items: invoiceItems.value.map(item => ({
        product_id: item.product_id,
        product_variation_id: item.product_variation_id,
        warehouse_id: item.warehouse_id,
        quantity: item.quantity,
        unit_price: item.is_wholesale ? item.wholesale_price : item.unit_price,
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
};

const selectWarehouse = (id) => {
  selectedWarehouseId.value = id;
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
      if (activeCompany.value.company_logo) {
        logoUrl.value = `/storage/${activeCompany.value.company_logo}`;
      }
    }
  } catch (error) {
    console.error('Error fetching active company:', error);
  }
};

const fetchNextInvoiceNumber = async () => {
  try {
    const response = await api.get('/sales/next-number');
    if (response.data && response.data.success) {
      invoiceForm.value.sale_number = response.data.sale_number;
    }
  } catch (error) {
    console.error('Error fetching next invoice number:', error);
  }
};

// Lifecycle
onMounted(() => {
  updateDateTime();
  setInterval(updateDateTime, 1000);
  loadProducts();
  loadCategories();
  fetchNextInvoiceNumber();
  fetchActiveCompany();
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
