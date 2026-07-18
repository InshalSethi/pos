<template>
  <div v-if="show" class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <!-- Top Accent Color Line -->
      <div class="h-1.5 transition-all duration-300" :style="{ backgroundColor: accentColor }"></div>

      <!-- Enhanced Modal Header -->
      <div class="bg-white border-b border-slate-200 px-6 py-4 shadow-sm flex justify-between items-center relative">
        <div class="flex items-center space-x-3">
          <div class="p-2.5 rounded-xl text-white transition-all duration-300" :style="{ backgroundColor: accentColor }">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-bold text-slate-800">{{ isEdit ? 'Edit Sales Invoice' : 'Create Sales Invoice' }}</h2>
            <p class="text-xs text-slate-450">{{ isEdit ? `Modify details for Invoice #${invoiceForm.sale_number || ''}` : 'Generate a new invoice entry' }}</p>
          </div>
        </div>

        <button @click="closeModal" class="bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-slate-700 p-2 rounded-xl border border-slate-200 transition-all cursor-pointer" type="button">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Main Workspace Layout inside Modal Body -->
      <div class="modal-body-container flex flex-col lg:flex-row items-start bg-slate-50 overflow-y-auto">
        
        <!-- Left Panel: Simulated Physical Invoice Document Sheet (2/3 width) -->
        <div class="w-full lg:w-2/3 p-6 flex flex-col self-stretch">
          <div class="w-full bg-white shadow-lg rounded-2xl border border-slate-200 overflow-hidden flex flex-col p-8 relative flex-1 mb-0">
            <!-- Colored Accent Line on Sheet -->
            <div class="absolute top-0 left-0 right-0 h-1.5 transition-all duration-300" :style="{ backgroundColor: accentColor }"></div>

            <!-- Invoice Paper Header -->
            <div class="flex justify-between items-start mb-8">
              <div class="space-y-3 text-left">
                <!-- Company Logo -->
                <div class="w-20 h-20 bg-slate-50 hover:bg-slate-100 rounded-xl flex items-center justify-center border border-slate-200 relative group overflow-hidden cursor-pointer transition-all">
                  <img v-if="logoUrl" :src="logoUrl" class="w-full h-full object-cover" />
                  <div v-else class="text-slate-400 text-center p-2">
                    <svg class="mx-auto h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-[8px] font-bold uppercase mt-1 block">Add Logo</span>
                  </div>
                  <input type="file" @change="onLogoChange" class="absolute inset-0 opacity-0 cursor-pointer" />
                </div>

                <!-- Company Details -->
                <div class="text-left text-xs text-slate-500 space-y-0.5">
                  <p class="font-bold text-slate-700 text-sm">{{ activeCompany?.company_name || 'Sethi Enterprises' }}</p>
                  <p>{{ activeCompany?.business_address || 'Enterprise Workspace Inc.' }}</p>
                  <p v-if="activeCompany?.company_phone">{{ activeCompany.company_phone }}</p>
                  <p>{{ activeCompany?.company_email || 'sethiasad1@gmail.com' }}</p>
                </div>
              </div>

              <div class="text-right">
                <h2 class="text-2xl font-black uppercase tracking-wider transition-all duration-300" :style="{ color: accentColor }">Invoice</h2>
                
                <!-- Invoice Details Grid -->
                <div class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2 text-xs text-left max-w-xs ml-auto">
                  <div class="text-slate-500 font-medium flex items-center">Invoice Number:</div>
                  <div>
                    <input
                      v-model="invoiceForm.sale_number"
                      type="text"
                      placeholder="Auto-generating..."
                      class="w-full px-2 py-1 border border-slate-350 rounded text-slate-700 font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white"
                    />
                  </div>

                  <div class="text-slate-500 font-medium flex items-center">Order Number:</div>
                  <div>
                    <input
                      v-model="invoiceForm.order_number"
                      type="text"
                      placeholder="Enter order reference"
                      class="w-full px-2 py-1 border border-slate-350 rounded text-slate-700 focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white"
                    />
                  </div>

                  <div class="text-slate-500 font-medium flex items-center">Invoice Date:</div>
                  <div>
                    <input
                      v-model="invoiceForm.sale_date"
                      type="date"
                      class="w-full px-2 py-1 border border-slate-350 rounded text-slate-750 focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white"
                    />
                  </div>

                  <div class="text-slate-500 font-medium flex items-center">Due Date:</div>
                  <div>
                    <input
                      v-model="invoiceForm.due_date"
                      type="date"
                      class="w-full px-2 py-1 border border-slate-350 rounded text-slate-750 focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white"
                    />
                  </div>

                  <div class="text-slate-500 font-medium flex items-center">Warehouse:</div>
                  <div>
                    <select
                      v-model="selectedWarehouseId"
                      class="w-full px-2 py-1 border border-slate-350 rounded text-slate-700 focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer bg-white"
                    >
                      <option value="all">All Warehouses</option>
                      <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">
                        {{ wh.name }}
                      </option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <!-- Bill To Section -->
            <div class="border-t border-slate-150 py-5 mb-3 flex justify-between items-start">
              <div class="w-1/2 text-left">
                <h3 class="text-xs font-extrabold uppercase text-slate-450 tracking-wider mb-2">Bill To</h3>
                
                <!-- Customer Search input -->
                <div class="relative max-w-xs mb-3">
                  <input
                    v-model="customerSearch"
                    type="text"
                    placeholder="Type name to search customer..."
                    class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white"
                    @input="debouncedCustomerSearch"
                  />
                  
                  <!-- Customer Search Dropdown Results -->
                  <div v-if="customerSearchResults.length > 0" class="absolute z-50 mt-1 w-full bg-white shadow-xl max-h-48 rounded-lg border border-slate-200 py-1 text-xs overflow-auto">
                    <div
                      v-for="customer in customerSearchResults"
                      :key="customer.id"
                      @click="selectCustomer(customer)"
                      class="cursor-pointer py-2 px-3 hover:bg-slate-50 flex justify-between items-center text-left"
                    >
                      <div>
                        <span class="font-bold text-slate-800">{{ customer.name }}</span>
                        <p class="text-[10px] text-slate-500">{{ customer.phone || customer.email }}</p>
                      </div>
                      <span v-if="customer.tax_number" class="text-[9px] bg-slate-100 text-slate-650 px-1.5 py-0.5 rounded font-mono">TAX: {{ customer.tax_number }}</span>
                    </div>
                  </div>
                </div>

                <!-- Selected Customer Card -->
                <div v-if="selectedCustomer" class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-1 relative max-w-xs">
                  <button type="button" @click="clearCustomer" class="absolute top-2 right-2 text-rose-600 hover:text-rose-800 font-semibold text-[10px] hover:underline">Remove</button>
                  <p class="font-bold text-slate-800 text-sm">{{ selectedCustomer.name }}</p>
                  <p v-if="selectedCustomer.phone" class="text-slate-600"><span class="font-semibold text-slate-450">Phone:</span> {{ selectedCustomer.phone }}</p>
                  <p v-if="selectedCustomer.email" class="text-slate-600"><span class="font-semibold text-slate-450">Email:</span> {{ selectedCustomer.email }}</p>
                  <p v-if="selectedCustomer.address" class="text-slate-600 leading-relaxed"><span class="font-semibold text-slate-450">Address:</span> {{ selectedCustomer.address }} {{ selectedCustomer.city ? ', ' + selectedCustomer.city : '' }}</p>
                </div>
                <div v-else class="text-slate-400 text-xs italic">
                  No customer selected. Search and assign a recipient.
                </div>
              </div>

              <div class="text-right">
                <button
                  type="button"
                  @click="showCustomerModal = true"
                  class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-all flex items-center space-x-1 cursor-pointer"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  <span>Add Customer</span>
                </button>
              </div>
            </div>

            <!-- Line Items Table -->
            <div class="overflow-x-auto overflow-y-auto max-h-[45vh] border border-slate-200 rounded-xl mt-2 relative">
              <table class="w-full text-xs text-left border-collapse">
                <thead class="sticky top-0 z-10 shadow-xs bg-slate-50 border-b border-slate-200">
                  <tr class="text-slate-450 uppercase font-extrabold tracking-wider">
                    <th class="py-3 px-3 w-4/12 bg-slate-50">Item Details / Description</th>
                    <th class="py-3 px-2 w-1/12 text-center bg-slate-50">Qty</th>
                    <th class="py-3 px-2 w-1.5/12 text-right bg-slate-50">Price</th>
                    <th class="py-3 px-2 w-1.5/12 text-center bg-slate-50">W.S Price</th>
                    <th class="py-3 px-2 w-1.5/12 text-center bg-slate-50">Tax</th>
                    <th class="py-3 px-2 w-1.5/12 text-right bg-slate-50">Discount</th>
                    <th class="py-3 px-2 w-1.5/12 text-right bg-slate-50">Amount</th>
                    <th class="py-3 px-1 w-[40px] text-center bg-slate-50"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-if="invoiceItems.length === 0">
                    <td colspan="8" class="py-12 text-center text-slate-400 italic">
                      <svg class="mx-auto h-8 w-8 text-slate-350 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                      </svg>
                      <span>No products added. Search and select products on the right catalog sidebar.</span>
                    </td>
                  </tr>

                  <tr v-for="(item, index) in invoiceItems" :key="index" class="hover:bg-slate-50/40 group align-top">
                    <!-- Name & Description -->
                    <td class="py-3 px-2 text-left">
                      <div class="flex items-center justify-between mb-1.5">
                        <div class="font-bold text-slate-800 text-xs">{{ item.name }}</div>
                        
                        <!-- Wholesale toggle switch -->
                        <label class="inline-flex items-center cursor-pointer select-none">
                          <span class="text-[9px] font-extrabold uppercase text-slate-500 mr-1.5 tracking-wider">W.S</span>
                          <div class="relative">
                            <input
                              v-model="item.is_wholesale"
                              type="checkbox"
                              class="sr-only peer"
                              @change="updateItemTotal(index)"
                            />
                            <div class="w-7 h-4 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-indigo-650 relative"></div>
                          </div>
                        </label>
                      </div>
                      <div class="text-[9px] text-slate-400 font-mono mb-1">SKU: {{ item.sku }}</div>
                      <textarea
                        v-model="item.description"
                        placeholder="Add line item details / description..."
                        rows="2"
                        class="w-full bg-slate-50/50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 rounded px-2 py-1 text-slate-650 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-[10px]"
                      ></textarea>
                    </td>

                    <!-- Quantity -->
                    <td class="py-3 px-2 text-center">
                      <input
                        v-model.number="item.quantity"
                        type="number"
                        min="1"
                        :max="getProductStock(item.product)"
                        class="w-12 px-1.5 py-1 text-center border border-slate-350 rounded focus:outline-none focus:ring-1 focus:ring-indigo-500 font-bold bg-white"
                        @input="updateItemTotal(index)"
                      />
                      <div class="text-[9px] text-slate-400 mt-1">Stock: {{ getProductStock(item.product) }}</div>
                    </td>

                    <!-- Price -->
                    <td class="py-3 px-2 text-right">
                      <input
                        v-model.number="item.unit_price"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-20 px-1.5 py-1 text-right border border-slate-350 rounded focus:outline-none focus:ring-1 focus:ring-indigo-500 font-semibold transition-all"
                        :class="item.is_wholesale ? 'bg-slate-100 text-slate-400 opacity-60' : 'bg-white text-slate-800'"
                        :readonly="item.is_wholesale"
                        @input="updateItemTotal(index)"
                      />
                    </td>

                    <!-- Wholesale Price -->
                    <td class="py-3 px-2 text-right">
                      <input
                        v-model.number="item.wholesale_price"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-20 px-1.5 py-1 text-right border border-slate-350 rounded focus:outline-none focus:ring-1 focus:ring-indigo-500 font-semibold transition-all"
                        :class="!item.is_wholesale ? 'bg-slate-100 text-slate-400 opacity-60' : 'bg-white text-slate-800'"
                        :readonly="!item.is_wholesale"
                        @input="updateItemTotal(index)"
                      />
                    </td>

                    <!-- Tax -->
                    <td class="py-3 px-2 text-center">
                      <select
                        v-model="item.tax_id"
                        class="w-18 px-1 py-1 border border-slate-350 rounded text-[10px] focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer bg-white"
                        @change="updateItemTax(item)"
                      >
                        <option :value="null">No Tax</option>
                        <option v-for="tax in taxes" :key="tax.id" :value="tax.id">
                          {{ tax.name }} ({{ tax.value }}%)
                        </option>
                      </select>
                      <div v-if="item.tax_rate" class="text-[9px] text-slate-450 mt-1">{{ item.tax_rate }}%</div>
                    </td>

                    <!-- Discount -->
                    <td class="py-3 px-2 text-right">
                      <input
                        v-model.number="item.discount_amount"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-14 px-1.5 py-1 text-right border border-slate-350 rounded focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white"
                        @input="updateItemTotal(index)"
                      />
                    </td>

                    <!-- Total Amount -->
                    <td class="py-3 px-2 text-right font-bold text-slate-700">
                      ${{ item.total.toFixed(2) }}
                    </td>

                    <!-- Delete Button -->
                    <td class="py-3 px-1 text-center">
                      <button
                        type="button"
                        @click="removeFromInvoice(index)"
                        class="text-slate-400 hover:text-rose-600 p-1 rounded-lg hover:bg-rose-50 transition-all cursor-pointer"
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

            <!-- Notes & Footer columns -->
            <div class="border-t border-slate-150 pt-5 mt-6 grid grid-cols-1 md:grid-cols-2 gap-5 text-left">
              <div>
                <label class="block text-xs font-bold uppercase text-slate-450 tracking-wider mb-2">Notes to Customer</label>
                <textarea
                  v-model="invoiceForm.notes"
                  rows="3"
                  class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500 placeholder-slate-400 bg-white"
                  placeholder="Wiring directions, term clarifications, details..."
                ></textarea>
              </div>
              <div>
                <label class="block text-xs font-bold uppercase text-slate-450 tracking-wider mb-2">Footer / Terms & Conditions</label>
                <textarea
                  v-model="invoiceForm.footer"
                  rows="3"
                  class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500 placeholder-slate-400 bg-white"
                  placeholder="Thank you for your business, payment policy rules..."
                ></textarea>
              </div>
            </div>

          </div>
        </div>

        <!-- Right Panel: Sidebar Catalog Search & checkout summary (1/3 width) -->
        <div class="w-full lg:w-1/3 bg-white border-l border-slate-200 p-5 space-y-6 flex flex-col shadow-xs self-stretch">
          
          <!-- Product Catalog Selection -->
          <div class="space-y-4 text-left">
            <h3 class="text-xs font-extrabold uppercase text-slate-500 tracking-wider border-b border-slate-100 pb-2">Catalog Selection</h3>
            
            <div class="flex justify-between items-center bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-150">
              <span class="text-[9px] text-slate-500 font-extrabold uppercase tracking-wider">Barcode Scanner</span>
              <span class="text-[9px] text-emerald-600 font-black flex items-center gap-1 uppercase">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Active
              </span>
            </div>

            <!-- Scan barcode -->
            <div class="flex space-x-2">
              <input
                v-model="barcodeInput"
                type="text"
                placeholder="Scan barcode or SKU..."
                class="flex-1 pl-3 pr-2 py-1.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs bg-white"
                @keydown.enter.prevent="addByBarcode"
              />
              <button
                type="button"
                @click="addByBarcode"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-xl text-xs font-semibold shadow-xs transition-all cursor-pointer"
              >
                Find
              </button>
            </div>

            <!-- Search products autocomplete input -->
            <div class="relative" id="product-search-container-modal">
              <input
                v-model="productSearch"
                type="text"
                placeholder="Search products by title, SKU..."
                class="w-full pl-3 pr-8 py-1.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs bg-white"
                @focus="isProductDropdownOpen = true"
              />
              <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>

              <!-- Search dropdown results -->
              <div
                v-show="isProductDropdownOpen && filteredProducts.length > 0"
                class="absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-xl z-50 max-h-48 overflow-y-auto py-1.5"
              >
                <div
                  v-for="product in filteredProducts"
                  :key="product.key"
                  @click="selectProductFromDropdown(product)"
                  class="px-4 py-2 hover:bg-slate-50 cursor-pointer flex justify-between items-center text-xs border-b border-slate-50 last:border-0"
                >
                  <div class="min-w-0 pr-4 text-left">
                    <div class="font-bold text-slate-800 truncate">{{ product.name }}</div>
                    <div class="text-[9px] text-slate-400 font-mono">SKU: {{ product.sku }}</div>
                  </div>
                  <div class="text-right flex-shrink-0">
                    <span class="font-bold text-emerald-600 block">${{ product.price }}</span>
                    <span class="text-[9px] text-slate-500">{{ getProductStock(product) }} Stock</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Category selector filter -->
            <div class="relative" id="category-dropdown-container-modal">
              <button
                type="button"
                @click="isCategoryDropdownOpen = !isCategoryDropdownOpen"
                class="w-full flex items-center justify-between px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs font-semibold text-slate-700 cursor-pointer"
              >
                <span>{{ categoryDropdownLabel }}</span>
                <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': isCategoryDropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div
                v-show="isCategoryDropdownOpen"
                class="absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-xl z-50 max-h-48 overflow-y-auto p-2"
              >
                <label class="flex items-center space-x-2 px-2 py-1.5 hover:bg-slate-50 rounded-lg cursor-pointer text-xs">
                  <input
                    type="checkbox"
                    :checked="selectedCategories.length === 0"
                    @change="clearSelectedCategories"
                    class="rounded text-indigo-650 focus:ring-indigo-500"
                  />
                  <span class="font-medium text-slate-700">All Categories</span>
                </label>
                <div class="border-t border-slate-100 my-1"></div>
                <label
                  v-for="category in categories"
                  :key="category.id"
                  class="flex items-center space-x-2 px-2 py-1.5 hover:bg-slate-50 rounded-lg cursor-pointer text-xs"
                >
                  <input
                    type="checkbox"
                    :value="category.id"
                    v-model="selectedCategories"
                    class="rounded text-indigo-650 focus:ring-indigo-500"
                  />
                  <span class="font-medium text-slate-700">{{ category.name }}</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Advanced Customization -->
          <div class="space-y-4 text-left">
            <h3 class="text-xs font-extrabold uppercase text-slate-500 tracking-wider border-b border-slate-100 pb-2">Customization</h3>

            <!-- Accent Brand Color Picker -->
            <div class="space-y-2">
              <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Brand Theme Color</label>
              <div class="flex flex-wrap gap-2 items-center">
                <button
                  v-for="color in presetColors"
                  :key="color.hex"
                  @click="accentColor = color.hex"
                  class="w-5.5 h-5.5 rounded-full border border-slate-250 transition-all flex items-center justify-center shadow-xs hover:scale-115 cursor-pointer"
                  :style="{ backgroundColor: color.hex }"
                  :title="color.name"
                  type="button"
                >
                  <span v-if="accentColor === color.hex" class="w-1.5 h-1.5 rounded-full bg-white shadow-xs"></span>
                </button>
                <div class="flex items-center space-x-1 border border-slate-200 rounded-lg px-1.5 py-0.5 bg-slate-50 hover:bg-slate-100 transition-all cursor-pointer relative overflow-hidden">
                  <span class="text-[9px] font-semibold text-slate-650">Custom</span>
                  <input
                    v-model="accentColor"
                    type="color"
                    class="w-5 h-5 border-0 p-0 cursor-pointer bg-transparent rounded"
                  />
                </div>
              </div>
            </div>

            <!-- Invoice Category -->
            <div class="space-y-1.5">
              <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Invoice Category</label>
              <select
                v-model="invoiceForm.category_id"
                class="w-full px-3 py-1.5 border border-slate-350 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs cursor-pointer bg-white"
              >
                <option :value="null">Uncategorized</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>
          </div>

          <!-- Checkout Summary -->
          <div class="space-y-4">
            <h3 class="text-xs font-extrabold uppercase text-slate-500 tracking-wider border-b border-slate-100 pb-2 text-left">Summary & Payment</h3>

            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-150 text-xs space-y-2.5 text-left">
              <div class="flex justify-between font-medium text-slate-600">
                <span>Subtotal:</span>
                <span class="font-bold text-slate-800">${{ invoiceSubtotal.toFixed(2) }}</span>
              </div>
              <div class="flex justify-between font-medium text-slate-600">
                <span>Line Discounts:</span>
                <span class="font-bold text-slate-800">-${{ totalDiscount.toFixed(2) }}</span>
              </div>
              <div class="flex justify-between font-medium text-slate-600">
                <span>Line Taxes:</span>
                <span class="font-bold text-slate-800">+${{ totalTax.toFixed(2) }}</span>
              </div>
              <div class="flex justify-between items-center text-sm font-extrabold text-slate-900 border-t border-slate-200 pt-2.5 mt-1">
                <span>Total Amount:</span>
                <span class="text-lg transition-all duration-300 font-black" :style="{ color: accentColor }">${{ invoiceTotal.toFixed(2) }}</span>
              </div>
            </div>

            <!-- Payment Configuration inputs -->
            <div class="grid grid-cols-2 gap-3 text-left">
              <div>
                <label class="text-[9px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Method</label>
                <select
                  v-model="invoiceForm.payment_method"
                  class="w-full px-2 py-1.5 border border-slate-350 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs cursor-pointer bg-white"
                >
                  <option value="cash">Cash</option>
                  <option value="card">Card</option>
                  <option value="bank_transfer">Bank Transfer</option>
                  <option value="mobile_payment">Mobile Payment</option>
                  <option value="mixed">Mixed</option>
                </select>
              </div>

              <div>
                <label class="text-[9px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Paid</label>
                <input
                  v-model.number="invoiceForm.paid_amount"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-2 py-1 border border-slate-350 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs font-bold text-slate-800 bg-white"
                  :placeholder="invoiceTotal.toFixed(2)"
                />
              </div>
            </div>

            <!-- Refund/Change -->
            <div v-if="changeAmount > 0" class="bg-emerald-50 text-emerald-700 rounded-xl px-3 py-2 border border-emerald-250 text-xs font-bold text-left flex justify-between">
              <span>Change / Refund:</span>
              <span>${{ changeAmount.toFixed(2) }}</span>
            </div>
          </div>

          <!-- Checkout actions -->
          <div class="pt-4 border-t border-slate-250 bg-slate-50/50 p-4 rounded-xl space-y-3">
            <div class="grid grid-cols-2 gap-2">
              <button
                type="button"
                @click="saveSalesInvoice"
                :disabled="invoiceItems.length === 0 || saving"
                class="w-full text-white py-2.5 px-3 rounded-xl font-bold hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed text-xs flex items-center justify-center space-x-1 transition-all cursor-pointer"
                :style="{ backgroundColor: accentColor }"
              >
                <svg v-if="saving" class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ saving ? 'Saving...' : 'Update Invoice' }}</span>
              </button>

              <button
                type="button"
                @click="saveAsDraft"
                :disabled="invoiceItems.length === 0 || saving"
                class="w-full bg-slate-100 border border-slate-300 text-slate-700 py-2.5 px-3 rounded-xl font-bold hover:bg-slate-200 transition-all text-xs cursor-pointer disabled:opacity-50"
              >
                Save Draft
              </button>
            </div>

            <button
              type="button"
              @click="closeModal"
              class="w-full bg-rose-600 hover:bg-rose-700 text-white py-2.5 px-3 rounded-xl font-bold transition-all text-xs cursor-pointer flex items-center justify-center space-x-1"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              <span>Cancel / Close</span>
            </button>
          </div>

        </div>

      </div>
    </div>

    <!-- Quick Customer Creation Modal -->
    <div v-if="showCustomerModal" class="fixed inset-0 bg-slate-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
      <div class="relative mx-auto p-6 border w-full max-w-md shadow-2xl rounded-2xl bg-white text-left">
        <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-100">
          <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Add New Customer</h3>
          <button @click="closeCustomerModal" class="text-slate-400 hover:text-slate-600 text-xs font-bold">Close</button>
        </div>

        <form @submit.prevent="createCustomer" class="space-y-4">
          <div>
            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Full Name *</label>
            <input
              v-model="newCustomer.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-slate-350 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Phone</label>
              <input
                v-model="newCustomer.phone"
                type="text"
                class="w-full px-3 py-2 border border-slate-350 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs"
              />
            </div>
            <div>
              <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Email</label>
              <input
                v-model="newCustomer.email"
                type="email"
                class="w-full px-3 py-2 border border-slate-350 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs"
              />
            </div>
          </div>

          <div>
            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Address</label>
            <textarea
              v-model="newCustomer.address"
              rows="2"
              class="w-full px-3 py-2 border border-slate-350 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs"
            ></textarea>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">City</label>
              <input
                v-model="newCustomer.city"
                type="text"
                class="w-full px-3 py-2 border border-slate-350 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs"
              />
            </div>
            <div>
              <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tax Number / ID</label>
              <input
                v-model="newCustomer.tax_number"
                type="text"
                class="w-full px-3 py-2 border border-slate-350 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 text-xs"
              />
            </div>
          </div>

          <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
            <button
              type="button"
              @click="closeCustomerModal"
              class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="!newCustomer.name || creatingCustomer"
              class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-xs transition-all cursor-pointer"
            >
              {{ creatingCustomer ? 'Creating...' : 'Create Customer' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useToast } from '@/composables/useToast';
import { useCurrencyStore } from '@/stores/currency';
import api from '@/services/api';

export default {
  name: 'SalesInvoiceModal',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    invoice: {
      type: Object,
      default: null
    },
    isEdit: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const { showToast } = useToast();
    const currencyStore = useCurrencyStore();
    
    // Preset brand accent colors
    const presetColors = [
      { name: 'Indigo', hex: '#4f46e5' },
      { name: 'Emerald', hex: '#10b981' },
      { name: 'Amber', hex: '#f59e0b' },
      { name: 'Rose', hex: '#f43f5e' },
      { name: 'Slate', hex: '#475569' },
      { name: 'Blue', hex: '#3b82f6' }
    ];

    const accentColor = ref('#4f46e5');
    const logoUrl = ref('');
    const activeCompany = ref(null);
    const saving = ref(false);
    const errors = ref({});

    // Products / Catalog / Customers arrays
    const products = ref([]);
    const categories = ref([]);
    const customers = ref([]);
    const taxes = ref([]);
    const warehouses = ref([]);

    const invoiceItems = ref([]);
    const selectedCustomer = ref(null);
    const selectedCategories = ref([]);

    // UI state toggles
    const isProductDropdownOpen = ref(false);
    const isCategoryDropdownOpen = ref(false);
    const showCustomerModal = ref(false);
    const creatingCustomer = ref(false);

    // Filter & search fields
    const productSearch = ref('');
    const barcodeInput = ref('');
    const customerSearch = ref('');
    const customerSearchResults = ref([]);
    const selectedWarehouseId = ref('all');

    const invoiceForm = ref({
      customer_id: '',
      sale_number: '',
      category_id: null,
      sale_date: new Date().toISOString().split('T')[0],
      due_date: '',
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
      phone: '',
      email: '',
      address: '',
      city: '',
      tax_number: ''
    });

    const resetForm = () => {
      invoiceForm.value = {
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
      };
      invoiceItems.value = [];
      selectedCustomer.value = null;
      customerSearch.value = '';
      customerSearchResults.value = [];
      productSearch.value = '';
      barcodeInput.value = '';
      accentColor.value = '#4f46e5';
      logoUrl.value = '';
      selectedWarehouseId.value = 'all';
      errors.value = {};
    };

    function getDefaultDueDate(invoiceDateStr) {
      const d = new Date(invoiceDateStr);
      d.setDate(d.getDate() + 30);
      return d.toISOString().split('T')[0];
    }

    const loadInvoiceData = () => {
      if (props.isEdit && props.invoice) {
        invoiceForm.value.customer_id = props.invoice.customer_id || '';
        invoiceForm.value.sale_number = props.invoice.sale_number || '';
        invoiceForm.value.category_id = props.invoice.category_id || null;
        invoiceForm.value.sale_date = props.invoice.sale_date || '';
        invoiceForm.value.due_date = props.invoice.due_date || '';
        invoiceForm.value.order_number = props.invoice.order_number || '';
        invoiceForm.value.payment_method = props.invoice.payment_method || 'cash';
        invoiceForm.value.status = props.invoice.status || 'completed';
        invoiceForm.value.tax_amount = parseFloat(props.invoice.tax_amount) || 0;
        invoiceForm.value.paid_amount = parseFloat(props.invoice.paid_amount) || 0;
        invoiceForm.value.notes = props.invoice.notes || '';
        invoiceForm.value.footer = props.invoice.footer || 'Thank you for your business!';
        
        if (props.invoice.color) {
          accentColor.value = props.invoice.color;
        }

        if (props.invoice.customer) {
          selectedCustomer.value = props.invoice.customer;
          customerSearch.value = props.invoice.customer.name;
        }

        if (props.invoice.sale_items && props.invoice.sale_items.length > 0) {
          invoiceItems.value = props.invoice.sale_items.map(item => {
            const productFlat = products.value.find(p => 
              p.product_id === item.product_id && 
              p.product_variation_id === item.product_variation_id
            ) || {
              product_id: item.product_id,
              product_variation_id: item.product_variation_id,
              name: item.product?.name || 'Product',
              sku: item.product?.sku || '',
              price: parseFloat(item.unit_price) || 0,
              wholesale_price: parseFloat(item.product?.wholesale_price) || 0,
              track_inventory: false,
              total_stock: 0,
              warehouse_stocks: {}
            };

            const wholesaleVal = parseFloat(item.product?.wholesale_price || 0);
            const isWholesale = Math.abs(parseFloat(item.unit_price) - wholesaleVal) < 0.01;

            return {
              product_id: item.product_id,
              product_variation_id: item.product_variation_id,
              warehouse_id: item.warehouse_id,
              name: item.product?.name || 'Product',
              sku: item.product?.sku || '',
              price: parseFloat(item.unit_price) || 0,
              unit_price: parseFloat(item.unit_price) || 0,
              wholesale_price: wholesaleVal || 0,
              is_wholesale: isWholesale,
              discount_amount: parseFloat(item.discount_amount) || 0,
              quantity: parseInt(item.quantity) || 1,
              tax_id: item.tax_id || null,
              tax_rate: parseFloat(item.tax_rate) || 0,
              description: item.description || '',
              total: parseFloat(item.total_amount) || 0,
              product: productFlat
            };
          });
        }
      }
    };

    // Fetch active company
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
        console.error('Error fetching company details:', error);
      }
    };

    // Load catalog items & warehouses & taxes
    const loadProducts = async () => {
      try {
        const response = await api.get('/sales/products-with-stock');
        if (response.data && response.data.success) {
          products.value = response.data.items || [];
          warehouses.value = response.data.warehouses || [];
          taxes.value = response.data.taxes || [];
        }
      } catch (error) {
        console.error('Error loading products list:', error);
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

    const searchCustomers = async (query) => {
      if (query.length < 2) {
        customerSearchResults.value = [];
        return;
      }
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
        selectCustomer(response.data);
        closeCustomerModal();
        showToast('Customer created successfully', 'success');
      } catch (error) {
        showToast('Error creating customer', 'error');
      } finally {
        creatingCustomer.value = false;
      }
    };

    const closeCustomerModal = () => {
      showCustomerModal.value = false;
      newCustomer.value = { name: '', phone: '', email: '', address: '', city: '', tax_number: '' };
    };

    const getProductStock = (product) => {
      if (!product || !product.track_inventory) return '∞';
      if (!selectedWarehouseId.value || selectedWarehouseId.value === 'all') return product.total_stock;
      return product.warehouse_stocks[selectedWarehouseId.value] ?? 0;
    };

    const addByBarcode = () => {
      const code = barcodeInput.value.trim();
      if (!code) return;

      const matched = products.value.find(p => 
        (p.barcode && p.barcode.toLowerCase() === code.toLowerCase()) || 
        (p.sku && p.sku.toLowerCase() === code.toLowerCase())
      );

      if (matched) {
        addToInvoice(matched);
        barcodeInput.value = '';
        showToast(`Added "${matched.name}"`, 'success');
      } else {
        showToast(`No product found with code: ${code}`, 'error');
      }
    };

    const selectProductFromDropdown = (product) => {
      addToInvoice(product);
      productSearch.value = '';
      isProductDropdownOpen.value = false;
    };

    const addToInvoice = (product) => {
      let targetWarehouseId = selectedWarehouseId.value;
      if (!targetWarehouseId || targetWarehouseId === 'all') {
        const whWithStock = Object.keys(product.warehouse_stocks || {}).find(
          id => (product.warehouse_stocks[id] || 0) > 0
        );
        if (whWithStock) {
          targetWarehouseId = whWithStock;
        } else {
          const defaultWh = warehouses.value.find(w => w.is_default) || warehouses.value[0];
          targetWarehouseId = defaultWh ? defaultWh.id : '';
        }
      }

      if (!targetWarehouseId) {
        showToast('Select a warehouse first', 'error');
        return;
      }

      const existing = invoiceItems.value.find(item => 
        item.product_id === product.product_id && 
        item.product_variation_id === product.product_variation_id &&
        item.warehouse_id === targetWarehouseId
      );

      const stockLimit = product.track_inventory 
        ? (product.warehouse_stocks?.[targetWarehouseId] ?? 0)
        : Infinity;

      if (existing) {
        if (product.track_inventory && existing.quantity >= stockLimit) {
          showToast(`Only ${stockLimit} items in stock`, 'error');
          return;
        }
        existing.quantity += 1;
        updateItemTotal(invoiceItems.value.indexOf(existing));
      } else {
        if (product.track_inventory && stockLimit <= 0) {
          showToast('Out of stock in selected warehouse', 'error');
          return;
        }

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
      invoiceForm.value.paid_amount = parseFloat(invoiceTotal.value.toFixed(2));
    };

    const onLogoChange = (event) => {
      const file = event.target.files[0];
      if (file) {
        logoUrl.value = URL.createObjectURL(file);
        showToast('Logo updated', 'success');
      }
    };

    // Computed total prices
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

    const filteredProducts = computed(() => {
      let filtered = products.value;
      if (selectedCategories.value.length > 0) {
        filtered = filtered.filter(product => selectedCategories.value.includes(product.category_id));
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
        const cat = categories.value.find(c => c.id === id);
        return cat ? cat.name : '';
      }).filter(Boolean);
      return 'Categories: ' + names.join(', ');
    });

    const clearSelectedCategories = () => {
      selectedCategories.value = [];
    };

    // Save/Update Sales Invoice
    const saveSalesInvoice = async () => {
      if (invoiceItems.value.length === 0) {
        showToast('At least one item is required', 'error');
        return;
      }

      saving.value = true;
      errors.value = {};

      try {
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

        const url = props.isEdit ? `/sales/${props.invoice.id}` : '/sales';
        const method = props.isEdit ? 'put' : 'post';

        const response = await api[method](url, invoiceData);

        showToast(
          props.isEdit ? 'Invoice updated successfully' : 'Invoice created successfully',
          'success'
        );

        emit('saved', response.data);
        closeModal();
      } catch (error) {
        console.error('Error saving invoice:', error);
        if (error.response?.status === 422) {
          errors.value = error.response.data.errors || {};
          showToast('Validation failed. Check details.', 'error');
        } else {
          showToast(
            error.response?.data?.message || 'Error saving invoice. Please try again.',
            'error'
          );
        }
      } finally {
        saving.value = false;
      }
    };

    const saveAsDraft = async () => {
      const originalStatus = invoiceForm.value.status;
      invoiceForm.value.status = 'draft';
      await saveSalesInvoice();
      invoiceForm.value.status = originalStatus;
    };

    const closeModal = () => {
      resetForm();
      emit('close');
    };

    const handleClickOutside = (event) => {
      const productContainer = document.getElementById('product-search-container-modal');
      if (productContainer && !productContainer.contains(event.target)) {
        isProductDropdownOpen.value = false;
      }
      const categoryContainer = document.getElementById('category-dropdown-container-modal');
      if (categoryContainer && !categoryContainer.contains(event.target)) {
        isCategoryDropdownOpen.value = false;
      }
    };

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

    watch(() => props.show, async (newVal) => {
      if (newVal) {
        resetForm();
        await loadProducts();
        await loadCategories();
        await fetchActiveCompany();
        loadInvoiceData();
      }
    });

    onMounted(async () => {
      await loadProducts();
      await loadCategories();
      await fetchActiveCompany();
      document.addEventListener('click', handleClickOutside);
    });

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside);
    });

    return {
      presetColors,
      accentColor,
      logoUrl,
      activeCompany,
      saving,
      errors,
      products,
      categories,
      customers,
      taxes,
      warehouses,
      invoiceItems,
      selectedCustomer,
      selectedCategories,
      isProductDropdownOpen,
      isCategoryDropdownOpen,
      showCustomerModal,
      creatingCustomer,
      productSearch,
      barcodeInput,
      customerSearch,
      customerSearchResults,
      selectedWarehouseId,
      invoiceForm,
      newCustomer,
      resetForm,
      loadInvoiceData,
      fetchActiveCompany,
      loadProducts,
      loadCategories,
      searchCustomers,
      debouncedCustomerSearch,
      selectCustomer,
      clearCustomer,
      createCustomer,
      closeCustomerModal,
      getProductStock,
      addByBarcode,
      selectProductFromDropdown,
      addToInvoice,
      removeFromInvoice,
      updateItemTotal,
      updateItemTax,
      calculateTotal,
      onLogoChange,
      invoiceSubtotal,
      totalDiscount,
      totalTax,
      invoiceTotal,
      changeAmount,
      filteredProducts,
      categoryDropdownLabel,
      clearSelectedCategories,
      saveSalesInvoice,
      saveAsDraft,
      closeModal
    };
  }
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(15, 23, 42, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(8px);
  padding: 16px;
}

.modal-content {
  background: white;
  border-radius: 20px;
  width: 96%;
  max-width: 1380px;
  height: 94vh;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-direction: column;
}

.modal-body-container {
  flex: 1;
  min-height: 0;
}

/* Custom scrollbars */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
::-webkit-scrollbar-track {
  background: #f8fafc;
}
::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}
::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Transitions */
.transition-all {
  transition: all 0.2s ease-in-out;
}

/* Input focus effect overrides */
input:focus,
select:focus,
textarea:focus {
  border-color: #6366f1 !important;
  box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1) !important;
}
</style>
