<template>
  <div class="w-full bg-slate-50 dark:bg-zinc-950 min-h-screen">
    <!-- Header bar -->
    <div class="bg-white dark:bg-[#1E1E1E] border-b border-slate-200 dark:border-[#2E2E2E] px-6 py-4 shadow-sm">
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
          <router-link
            to="/purchase/returns"
            class="text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 font-bold text-xs transition-colors flex items-center space-x-1.5"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Returns</span>
          </router-link>
          <span class="text-slate-300 dark:text-slate-600">|</span>
          <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Create Purchase Return</h1>
        </div>
      </div>
    </div>

    <!-- Main Workspace Layout -->
    <div class="w-full bg-white dark:bg-[#1E1E1E] flex flex-col lg:flex-row min-h-[calc(100vh-66px)] border-t border-slate-200 dark:border-[#2E2E2E]">
      
      <!-- Left Panel: Return Form & Line Items (3/4 width) -->
      <div class="w-full lg:w-3/4 p-6 sm:p-8 flex flex-col">

        <!-- Top Setup Grid (Supplier, PO Link, Warehouse, Date, Reason) -->
        <div class="bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-800 rounded-2xl p-5 mb-6 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
          
          <!-- Return Number -->
          <div>
            <label class="block text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Return #</label>
            <input
              v-model="form.return_number"
              type="text"
              readonly
              class="w-full px-3 py-2 bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs font-mono font-bold text-slate-700 dark:text-zinc-300 cursor-not-allowed"
            />
          </div>

          <!-- Supplier (Required Floating Searchable Dropdown) -->
          <div class="space-y-1 relative" @click.stop>
            <label class="block text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Supplier *</label>
            <div class="relative">
              <button
                type="button"
                @click="toggleDropdown('supplier')"
                class="w-full px-3 py-2 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs flex items-center justify-between text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-100 dark:focus:border-zinc-600 transition-all cursor-pointer"
                :class="{ 'border-slate-300 ring-2 ring-slate-100 dark:ring-zinc-800': activeDropdown === 'supplier' }"
              >
                <span class="truncate font-semibold" :class="{ 'text-slate-400 dark:text-zinc-500': !selectedSupplierName }">
                  {{ selectedSupplierName || 'Select Supplier' }}
                </span>
                <div class="flex items-center">
                  <button
                    v-if="form.supplier_id"
                    type="button"
                    @click.stop="selectSupplier('')"
                    class="mr-1 text-slate-400 hover:text-rose-500 transition-colors p-0.5 rounded-full hover:bg-slate-100 dark:hover:bg-zinc-700"
                    title="Clear supplier selection"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                  </button>
                  <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
              </button>

              <div
                v-if="activeDropdown === 'supplier'"
                class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-xl py-1 max-h-60 flex flex-col animate-fade-in"
              >
                <div class="p-2 border-b border-slate-100 dark:border-zinc-800 shrink-0">
                  <input
                    v-model="supplierSearch"
                    type="text"
                    placeholder="Search supplier..."
                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-100 dark:focus:border-zinc-600"
                  />
                </div>
                <div class="overflow-y-auto max-h-44 custom-scrollbar">
                  <div v-if="searchableSuppliers.length === 0" class="py-3 px-3 text-center text-slate-400 text-xs italic">
                    No suppliers found.
                  </div>
                  <button
                    v-for="sup in searchableSuppliers"
                    :key="sup.id"
                    type="button"
                    @click="selectSupplier(sup.id)"
                    class="w-full px-3 py-2 text-left hover:bg-slate-50 dark:hover:bg-zinc-800 text-xs text-slate-800 dark:text-zinc-200 border-b border-slate-50 dark:border-zinc-800/40 cursor-pointer flex justify-between items-center"
                    :class="{ 'bg-slate-100 dark:bg-zinc-800 font-bold': form.supplier_id == sup.id }"
                  >
                    <span>{{ sup.name }}</span>
                    <span v-if="sup.phone" class="text-[10px] text-slate-400 font-mono">{{ sup.phone }}</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- PO Reference (Optional Floating Searchable Dropdown) -->
          <div class="space-y-1 relative" @click.stop>
            <label class="block text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">PO Reference</label>
            <div class="relative">
              <button
                type="button"
                @click="toggleDropdown('po')"
                class="w-full px-3 py-2 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs flex items-center justify-between text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-100 dark:focus:border-zinc-600 transition-all cursor-pointer"
                :class="{ 'border-slate-300 ring-2 ring-slate-100 dark:ring-zinc-800': activeDropdown === 'po' }"
              >
                <span class="truncate font-semibold" :class="{ 'text-slate-400 dark:text-zinc-500': !selectedPoLabel }">
                  {{ selectedPoLabel || 'Standalone / None' }}
                </span>
                <div class="flex items-center">
                  <button v-if="form.purchase_order_id" @click.stop="selectPo('')" class="mr-1 text-slate-400 hover:text-rose-500 transition-colors p-0.5 rounded-full hover:bg-slate-100 dark:hover:bg-zinc-700">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                  </button>
                  <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
              </button>

              <div
                v-if="activeDropdown === 'po'"
                class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-xl py-1 max-h-60 flex flex-col animate-fade-in"
              >
                <div class="p-2 border-b border-slate-100 dark:border-zinc-800 shrink-0">
                  <input
                    v-model="poSearch"
                    type="text"
                    placeholder="Search PO number..."
                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-100 dark:focus:border-zinc-600"
                  />
                </div>
                <div class="overflow-y-auto max-h-44 custom-scrollbar">
                  <button
                    type="button"
                    @click="selectPo('')"
                    class="w-full px-3 py-2 text-left hover:bg-slate-50 dark:hover:bg-zinc-800 text-xs text-slate-500 border-b border-slate-50 dark:border-zinc-800/40 cursor-pointer"
                    :class="{ 'bg-slate-100 dark:bg-zinc-800 font-bold': !form.purchase_order_id }"
                  >
                    Standalone / None
                  </button>
                  <div v-if="searchablePurchaseOrders.length === 0" class="py-3 px-3 text-center text-slate-400 text-xs italic">
                    No matching POs found.
                  </div>
                  <button
                    v-for="po in searchablePurchaseOrders"
                    :key="po.id"
                    type="button"
                    @click="selectPo(po.id)"
                    class="w-full px-3 py-2 text-left hover:bg-slate-50 dark:hover:bg-zinc-800 text-xs text-slate-800 dark:text-zinc-200 border-b border-slate-50 dark:border-zinc-800/40 cursor-pointer flex justify-between items-center"
                    :class="{ 'bg-blue-50 dark:bg-blue-900/20 font-bold text-blue-700 dark:text-blue-400': form.purchase_order_id == po.id }"
                  >
                    <span class="flex items-center gap-2">
                      <span class="font-medium">{{ po.po_number }}</span>
                        <span v-if="!form.supplier_id" class="text-[10px] text-slate-500 ml-2 bg-slate-200 dark:bg-zinc-700 px-1.5 py-0.5 rounded truncate max-w-[120px]">{{ getSupplierName(po.supplier_id) }}</span>
                      <svg v-if="form.purchase_order_id == po.id" class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <span class="text-[10px] text-slate-400">{{ formatDate(po.order_date) }}</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Warehouse (Floating Searchable Dropdown) -->
          <div class="space-y-1 relative" @click.stop>
            <label class="block text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Warehouse</label>
            <div class="relative">
              <button
                type="button"
                @click="toggleDropdown('warehouse')"
                class="w-full px-3 py-2 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs flex items-center justify-between text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-100 dark:focus:border-zinc-600 transition-all cursor-pointer"
                :class="{ 'border-slate-300 ring-2 ring-slate-100 dark:ring-zinc-800': activeDropdown === 'warehouse' }"
              >
                <span class="truncate font-semibold" :class="{ 'text-slate-400 dark:text-zinc-500': !selectedWarehouseName }">
                  {{ selectedWarehouseName || 'Select Warehouse' }}
                </span>
                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
              </button>

              <div
                v-if="activeDropdown === 'warehouse'"
                class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-xl py-1 max-h-60 flex flex-col animate-fade-in"
              >
                <div class="p-2 border-b border-slate-100 dark:border-zinc-800 shrink-0">
                  <input
                    v-model="warehouseSearch"
                    type="text"
                    placeholder="Search warehouse..."
                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-100 dark:focus:border-zinc-600"
                  />
                </div>
                <div class="overflow-y-auto max-h-44 custom-scrollbar">
                  <div v-if="searchableWarehouses.length === 0" class="py-3 px-3 text-center text-slate-400 text-xs italic">
                    No warehouses found.
                  </div>
                  <button
                    v-for="wh in searchableWarehouses"
                    :key="wh.id"
                    type="button"
                    @click="selectWarehouse(wh.id)"
                    class="w-full px-3 py-2 text-left hover:bg-slate-50 dark:hover:bg-zinc-800 text-xs text-slate-800 dark:text-zinc-200 border-b border-slate-50 dark:border-zinc-800/40 cursor-pointer"
                    :class="{ 'bg-slate-100 dark:bg-zinc-800 font-bold': form.warehouse_id == wh.id }"
                  >
                    {{ wh.name }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Return Date -->
          <div>
            <label class="block text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Return Date *</label>
            <input
              v-model="form.return_date"
              type="date"
              required
              class="w-full px-3 py-2 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs focus:border-slate-300 focus:ring-2 focus:ring-slate-100 outline-none text-slate-800 dark:text-zinc-100"
            />
          </div>
        </div>

        <!-- Catalog Search & Product Selection -->
        <div class="mb-6">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-xs font-extrabold uppercase text-slate-400 dark:text-zinc-500 tracking-wider">Product Search & Catalog</h3>
            <span v-if="selectedPoNumber" class="text-xs text-blue-600 dark:text-blue-400 font-semibold">
              Items preloaded from {{ selectedPoNumber }}
            </span>
          </div>

          <div class="relative w-full">
            <input
              v-model="productSearch"
              type="text"
              placeholder="Search products by title, SKU or code to add items..."
              class="w-full pl-4 pr-10 py-2.5 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-medium text-slate-800 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-blue-500/50 shadow-sm"
              @focus="isProductDropdownOpen = true"
            />
            <div
              v-show="isProductDropdownOpen && filteredProducts.length > 0"
              class="absolute left-0 right-0 mt-1 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-xl z-50 max-h-60 overflow-y-auto py-2"
            >
              <div
                v-for="product in filteredProducts"
                :key="product.id"
                @click="addProductToReturn(product)"
                class="px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-zinc-800 cursor-pointer flex justify-between items-center text-xs border-b border-slate-100 dark:border-zinc-800/60 last:border-0"
              >
                <div>
                  <div class="font-bold text-slate-800 dark:text-zinc-200">{{ product.name }}</div>
                  <div class="text-[10px] text-slate-400">SKU: {{ product.sku }}</div>
                </div>
                <div class="text-right">
                  <div class="font-black text-blue-600 dark:text-blue-400">{{ currencySymbol }}{{ formatCurrency(product.cost_price || product.selling_price) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Dynamic Items Table -->
        <div class="grow bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-sm flex flex-col mb-6">
          <div class="p-4 border-b border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-800/30 flex justify-between items-center">
            <h3 class="text-sm font-bold text-slate-800 dark:text-zinc-200">Returned Items Breakdown</h3>
            <span class="text-xs text-slate-400">{{ form.items.length }} {{ form.items.length === 1 ? 'Item' : 'Items' }}</span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead>
                <tr class="bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-200 dark:border-zinc-700 text-slate-400 dark:text-zinc-500 uppercase font-bold tracking-wider">
                  <th class="py-3 px-4 w-12 text-center">#</th>
                  <th class="py-3 px-4">Item Description</th>
                  <th class="py-3 px-4 text-center w-28">Unit Cost</th>
                  <th class="py-3 px-4 text-center w-28">Qty Returned</th>
                  <th class="py-3 px-4 text-center w-24">Tax</th>
                  <th class="py-3 px-4 text-center w-24">Discount</th>
                  <th class="py-3 px-4 text-right w-32">Total</th>
                  <th class="py-3 px-4 text-center w-12"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                <tr v-if="form.items.length === 0">
                  <td colspan="8" class="py-12 text-center text-slate-400 dark:text-zinc-500">
                    No return items added yet. Select a Purchase Order above or search products to add line items.
                  </td>
                </tr>

                <tr v-for="(item, idx) in form.items" :key="idx" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/30">
                  <td class="py-3 px-4 text-center text-slate-400 font-bold">{{ idx + 1 }}</td>
                  <td class="py-3 px-4">
                    <div class="font-bold text-slate-800 dark:text-zinc-200">{{ item.product_name }}</div>
                    <div class="text-[10px] text-slate-400 font-mono" v-if="item.product_sku">SKU: {{ item.product_sku }}</div>
                    <div v-if="item.max_returnable !== undefined" class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold mt-0.5">
                      Max Returnable from PO: {{ item.max_returnable }}
                    </div>
                  </td>
                  <td class="py-3 px-4 text-center">
                    <input
                      v-model.number="item.unit_cost"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-24 text-center px-2 py-1 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded text-xs focus:ring-1 focus:ring-blue-500"
                    />
                  </td>
                  <td class="py-3 px-4 text-center">
                    <input
                      v-model.number="item.quantity"
                      type="number"
                      min="1"
                      :max="item.max_returnable !== undefined ? item.max_returnable : undefined"
                      @input="validateItemQty(item)"
                      class="w-20 text-center px-2 py-1 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded text-xs font-bold focus:ring-1 focus:ring-blue-500"
                    />
                  </td>
                  <td class="py-3 px-4 text-center">
                    <input
                      v-model.number="item.tax_amount"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-20 text-center px-2 py-1 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded text-xs focus:ring-1 focus:ring-blue-500"
                    />
                  </td>
                  <td class="py-3 px-4 text-center">
                    <input
                      v-model.number="item.discount_amount"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-20 text-center px-2 py-1 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded text-xs focus:ring-1 focus:ring-blue-500"
                    />
                  </td>
                  <td class="py-3 px-4 text-right font-black text-slate-900 dark:text-zinc-100">
                    {{ currencySymbol }}{{ formatCurrency(calculateLineTotal(item)) }}
                  </td>
                  <td class="py-3 px-4 text-center">
                    <button
                      @click="removeItem(idx)"
                      class="text-rose-500 hover:text-rose-700 p-1 rounded hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors"
                      title="Remove Item"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Right Panel: Summary, Status & Actions (1/4 width) -->
      <div class="w-full lg:w-1/4 p-6 sm:p-8 bg-slate-50/50 dark:bg-zinc-900/40 border-l border-slate-200 dark:border-[#2E2E2E] flex flex-col justify-between">
        
        <div class="space-y-6">
          <h3 class="text-sm font-extrabold uppercase text-slate-400 dark:text-zinc-500 tracking-wider">Return Summary</h3>

          <!-- Subtotal / Tax / Discount breakdown -->
          <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-4 space-y-3">
            <div class="flex justify-between text-xs text-slate-600 dark:text-zinc-400">
              <span>Subtotal</span>
              <span class="font-bold">{{ currencySymbol }}{{ formatCurrency(computedSubtotal) }}</span>
            </div>
            <div class="flex justify-between text-xs text-slate-600 dark:text-zinc-400">
              <span>Tax Total</span>
              <span class="font-bold text-amber-600">+{{ currencySymbol }}{{ formatCurrency(computedTax) }}</span>
            </div>
            <div class="flex justify-between text-xs text-slate-600 dark:text-zinc-400">
              <span>Discount Total</span>
              <span class="font-bold text-emerald-600">-{{ currencySymbol }}{{ formatCurrency(computedDiscount) }}</span>
            </div>
            <div class="border-t border-slate-200 dark:border-zinc-700 mt-4 pt-4 flex justify-between items-center font-black">
              <span class="text-xs text-slate-500 dark:text-zinc-400 uppercase tracking-widest">Grand Total</span>
              <span class="text-2xl text-emerald-500 dark:text-emerald-400 leading-none">{{ currencySymbol }}{{ formatCurrency(computedGrandTotal) }}</span>
            </div>
          </div>

          <!-- Return Reason Dropdown -->
          <div>
            <label class="block text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Reason for Return *</label>
            <select
              v-model="form.reason"
              required
              class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-zinc-100 font-semibold"
            >
              <option value="Damaged Goods">Damaged Goods</option>
              <option value="Defective / Quality Issue">Defective / Quality Issue</option>
              <option value="Wrong Item Shipped">Wrong Item Shipped</option>
              <option value="Overstocked / Excess">Overstocked / Excess</option>
              <option value="Expired Product">Expired Product</option>
              <option value="Other">Other Reason</option>
            </select>
          </div>

          <!-- Return Status -->
          <div>
            <label class="block text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Return Status</label>
            <select
              v-model="form.status"
              class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-zinc-100 font-semibold"
            >
              <option value="draft">Draft</option>
              <option value="pending">Pending Approval</option>
              <option value="approved">Approved (Deduct Inventory &amp; Post Ledger)</option>
              <option value="completed">Completed</option>
            </select>
          </div>

          <!-- Refund Status -->
          <div>
            <label class="block text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Refund Status</label>
            <select
              v-model="form.refund_status"
              class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-zinc-100 font-semibold"
            >
              <option value="pending">Pending Refund</option>
              <option value="partial">Partial Refund</option>
              <option value="refunded">Refunded / Credited</option>
            </select>
          </div>

          <!-- REFUND COLLECTION DETAILS (Directly below Refund Status) -->
          <div class="space-y-3 pt-2">
            <div class="flex items-center justify-between">
              <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">REFUND COLLECTION DETAILS</label>
              <span class="bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-400 px-2 py-0.5 rounded-full text-[10px] font-bold">Balanced</span>
            </div>

            <!-- Refund Method Tabs (Cash, Bank, AP Credit, Split / Mixed) -->
            <div class="bg-slate-100 dark:bg-zinc-800 p-1 rounded-xl grid grid-cols-4 gap-1 text-center text-xs">
              <button
                type="button"
                @click="!isUnpaidPo && (form.payment_method = 'cash')"
                :disabled="isUnpaidPo"
                class="py-1.5 px-2 rounded-lg font-semibold transition-all cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                :class="form.payment_method === 'cash' ? 'bg-white dark:bg-zinc-700 text-slate-900 dark:text-zinc-100 shadow-sm font-bold' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-800'"
                :title="isUnpaidPo ? 'This PO is unpaid. Returns must be processed as AP Credit.' : 'Refund via Cash'"
              >
                Cash
              </button>
              <button
                type="button"
                @click="!isUnpaidPo && (form.payment_method = 'bank')"
                :disabled="isUnpaidPo"
                class="py-1.5 px-2 rounded-lg font-semibold transition-all cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                :class="form.payment_method === 'bank' ? 'bg-white dark:bg-zinc-700 text-slate-900 dark:text-zinc-100 shadow-sm font-bold' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-800'"
                :title="isUnpaidPo ? 'This PO is unpaid. Returns must be processed as AP Credit.' : 'Refund via Bank'"
              >
                Bank
              </button>
              <button
                type="button"
                @click="form.payment_method = 'ap_credit'"
                class="py-1.5 px-2 rounded-lg font-semibold transition-all cursor-pointer"
                :class="form.payment_method === 'ap_credit' ? 'bg-white dark:bg-zinc-700 text-slate-900 dark:text-zinc-100 shadow-sm font-bold' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-800'"
                title="Reduce Accounts Payable balance"
              >
                AP Credit
              </button>
              <button
                type="button"
                @click="form.payment_method = 'mixed'"
                class="py-1.5 px-2 rounded-lg font-semibold transition-all cursor-pointer"
                :class="form.payment_method === 'mixed' ? 'bg-white dark:bg-zinc-700 text-slate-900 dark:text-zinc-100 shadow-sm font-bold' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-800'"
                title="Split allocation between AP Credit & Cash/Bank"
              >
                Split / Mixed
              </button>
            </div>

            <!-- UNPAID PO TOOLTIP / HINT -->
            <div v-if="isUnpaidPo" class="p-2.5 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-lg text-[11px] text-amber-700 dark:text-amber-300 flex items-start gap-2">
              <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span>This PO is unpaid. Returns must be processed as AP Credit.</span>
            </div>

            <!-- FULLY PAID PO HINT -->
            <div v-if="isFullyPaidPo" class="p-2.5 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 rounded-lg text-[11px] text-blue-700 dark:text-blue-300 flex items-start gap-2">
              <svg class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span>This PO is fully paid. AP Credit is capped at $0.00. Refund can be collected as Cash, Bank, or Vendor Credit.</span>
            </div>

            <!-- PARTIALLY PAID PO HINT & CAP ALLOCATION -->
            <div v-if="isPartiallyPaidPo" class="p-2.5 bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/60 rounded-lg text-[11px] text-indigo-700 dark:text-indigo-300 flex items-start gap-2">
              <svg class="w-4 h-4 text-indigo-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span>This PO is partially paid. AP Credit is capped at <strong>${{ formatCurrency(poDueAmount) }}</strong> (pending due amount). Any excess return amount must be allocated via Cash, Bank, or Vendor Credit.</span>
            </div>

            <!-- Split Allocation Breakdown for Partially Paid / Mixed -->
            <div v-if="form.payment_method === 'mixed' || (isPartiallyPaidPo && returnGrandTotal > poDueAmount)" class="bg-slate-50 dark:bg-zinc-800/80 p-3 rounded-lg border border-slate-200 dark:border-zinc-700 space-y-2 text-xs">
              <div class="font-bold text-slate-700 dark:text-zinc-300 text-[11px] uppercase tracking-wider">Split Allocation Breakdown</div>
              <div class="flex justify-between text-slate-600 dark:text-zinc-400">
                <span>AP Credit (Bill Reduction):</span>
                <span class="font-bold text-slate-800 dark:text-zinc-200">${{ formatCurrency(apCreditAllocation) }}</span>
              </div>
              <div class="flex justify-between text-slate-600 dark:text-zinc-400">
                <span>Cash / Bank Refund (Excess):</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400">${{ formatCurrency(cashBankAllocation) }}</span>
              </div>
            </div>

            <!-- Account Field -->
            <div>
              <label class="block text-xs font-semibold text-slate-500 mb-1">Account:</label>
              <select
                v-model="form.bank_account_id"
                class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-zinc-100 font-semibold"
              >
                <option value="">Select Account / Default Cash</option>
                <option v-for="acc in bankAccounts" :key="acc.id" :value="acc.id">
                  {{ acc.name || acc.account_name }}
                </option>
              </select>
            </div>

            <!-- Amount Received Field -->
            <div>
              <label class="block text-xs font-semibold text-slate-500 mb-1">Amount Received:</label>
              <input
                v-model.number="form.amount_received"
                type="number"
                step="0.01"
                placeholder="0.00"
                class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-zinc-100 font-bold"
              />
            </div>
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Notes &amp; Remarks</label>
            <textarea
              v-model="form.notes"
              rows="3"
              placeholder="Additional internal notes..."
              class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-lg text-xs focus:ring-1 focus:ring-blue-500 text-slate-800 dark:text-zinc-100"
            ></textarea>
          </div>
        </div>

        <!-- Submit Buttons -->
        <div class="pt-6 border-t border-slate-200 dark:border-zinc-800 space-y-2 mt-6">
          <button
            @click="submitReturn('approved')"
            :disabled="submitting || form.items.length === 0"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl text-xs shadow-md transition-all disabled:opacity-50 flex items-center justify-center space-x-2 cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>Submit &amp; Approve Return</span>
          </button>

          <button
            @click="submitReturn('draft')"
            :disabled="submitting || form.items.length === 0"
            class="w-full bg-slate-200 dark:bg-zinc-800 hover:bg-slate-300 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 font-bold py-2.5 px-4 rounded-xl text-xs transition-all disabled:opacity-50 flex items-center justify-center space-x-2 cursor-pointer"
          >
            <span>Save as Draft</span>
          </button>
        </div>

      </div>

    </div>

    <!-- Floating Grand Total Bar -->
    <div class="fixed bottom-4 right-6 bg-slate-900 text-white px-5 py-3 rounded-xl shadow-2xl flex items-center gap-4 z-40">
      <div class="flex flex-col text-right">
        <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">GRAND TOTAL</span>
        <span class="text-2xl font-bold text-emerald-400">${{ formatCurrency(returnGrandTotal) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const router = useRouter();
const authStore = useAuthStore();
const currencySymbol = computed(() => authStore.currencySymbol || '$');

const suppliers = ref([]);
const purchaseOrders = ref([]);
const warehouses = ref([]);
const products = ref([]);
const bankAccounts = ref([]);

const productSearch = ref('');
const isProductDropdownOpen = ref(false);
const submitting = ref(false);
const selectedPoNumber = ref('');
const selectedPoData = ref(null);

const isPoLinked = computed(() => !!form.purchase_order_id && !!selectedPoData.value);

const poTotalAmount = computed(() => parseFloat(selectedPoData.value?.total_amount || 0));
const poAmountPaid = computed(() => parseFloat(selectedPoData.value?.amount_paid || 0));
const poDueAmount = computed(() => {
  if (!selectedPoData.value) return 0;
  if (selectedPoData.value.due_amount !== undefined && selectedPoData.value.due_amount !== null) {
    return parseFloat(selectedPoData.value.due_amount);
  }
  return Math.max(0, poTotalAmount.value - poAmountPaid.value);
});

// 1. UNPAID PO (100% Unpaid / Due)
const isUnpaidPo = computed(() => {
  if (!isPoLinked.value) return false;
  return poDueAmount.value > 0 && poAmountPaid.value <= 0;
});

// 2. FULLY PAID PO
const isFullyPaidPo = computed(() => {
  if (!isPoLinked.value) return false;
  return poDueAmount.value <= 0 || (poAmountPaid.value >= poTotalAmount.value && poTotalAmount.value > 0);
});

// 3. PARTIALLY PAID PO
const isPartiallyPaidPo = computed(() => {
  if (!isPoLinked.value) return false;
  return poAmountPaid.value > 0 && poDueAmount.value > 0;
});

const apCreditAllocation = computed(() => {
  if (!isPoLinked.value) return returnGrandTotal.value;
  if (isUnpaidPo.value) return returnGrandTotal.value;
  if (isFullyPaidPo.value) return 0;
  return Math.min(returnGrandTotal.value, poDueAmount.value);
});

const cashBankAllocation = computed(() => {
  return Math.max(0, returnGrandTotal.value - apCreditAllocation.value);
});

const activeDropdown = ref(null);
const supplierSearch = ref('');
const poSearch = ref('');
const warehouseSearch = ref('');

const toggleDropdown = (name) => {
  if (activeDropdown.value === name) {
    activeDropdown.value = null;
  } else {
    activeDropdown.value = name;
  }
};

const closeAllDropdowns = () => {
  activeDropdown.value = null;
};

const form = reactive({
  return_number: '',
  supplier_id: '',
  purchase_order_id: '',
  warehouse_id: 1,
  return_date: new Date().toISOString().split('T')[0],
  reason: 'Damaged Goods',
  status: 'approved',
  refund_status: 'pending',
  payment_method: 'cash',
  bank_account_id: '',
  amount_received: 0,
  notes: '',
  items: [],
});

const filteredPurchaseOrders = computed(() => {
  if (!form.supplier_id) return purchaseOrders.value;
  return purchaseOrders.value.filter(po => po.supplier_id == form.supplier_id);
});

const searchableSuppliers = computed(() => {
  if (!supplierSearch.value) return suppliers.value;
  const q = supplierSearch.value.toLowerCase();
  return suppliers.value.filter(s =>
    (s.name && s.name.toLowerCase().includes(q)) ||
    (s.phone && s.phone.includes(q)) ||
    (s.email && s.email.toLowerCase().includes(q))
  );
});

const searchablePurchaseOrders = computed(() => {
  const list = filteredPurchaseOrders.value;
  if (!poSearch.value) return list;
  const q = poSearch.value.toLowerCase();
  return list.filter(po =>
    po.po_number && po.po_number.toLowerCase().includes(q)
  );
});

const searchableWarehouses = computed(() => {
  if (!warehouseSearch.value) return warehouses.value;
  const q = warehouseSearch.value.toLowerCase();
  return warehouses.value.filter(w =>
    w.name && w.name.toLowerCase().includes(q)
  );
});

const selectedSupplierName = computed(() => {
  if (!form.supplier_id) return '';
  const found = suppliers.value.find(s => s.id == form.supplier_id);
  return found ? found.name : '';
});

const selectedPoLabel = computed(() => {
  if (!form.purchase_order_id) return '';
  const found = purchaseOrders.value.find(po => po.id == form.purchase_order_id);
  return found ? `${found.po_number}` : '';
});

const selectedWarehouseName = computed(() => {
  if (!form.warehouse_id) return '';
  const found = warehouses.value.find(w => w.id == form.warehouse_id);
  return found ? found.name : '';
});

const selectSupplier = (id) => {
  form.supplier_id = id;
  onSupplierChange();
  activeDropdown.value = null;
  supplierSearch.value = '';
};

const selectPo = (id) => {
  form.purchase_order_id = id;
  onPoChange();
  activeDropdown.value = null;
  poSearch.value = '';
};

const selectWarehouse = (id) => {
  form.warehouse_id = id;
  activeDropdown.value = null;
  warehouseSearch.value = '';
};

const filteredProducts = computed(() => {
  if (!productSearch.value) return [];
  const q = productSearch.value.toLowerCase();
  return products.value.filter(p =>
    (p.name && p.name.toLowerCase().includes(q)) ||
    (p.sku && p.sku.toLowerCase().includes(q)) ||
    (p.barcode && p.barcode.toLowerCase().includes(q))
  ).slice(0, 30);
});

const computedSubtotal = computed(() => {
  return form.items.reduce((sum, item) => sum + ((item.unit_cost || 0) * (item.quantity || 0)), 0);
});

const computedTax = computed(() => {
  return form.items.reduce((sum, item) => sum + (parseFloat(item.tax_amount) || 0), 0);
});

const computedDiscount = computed(() => {
  return form.items.reduce((sum, item) => sum + (parseFloat(item.discount_amount) || 0), 0);
});

const returnGrandTotal = computed(() => {
  return Math.max(0, (computedSubtotal.value + computedTax.value) - computedDiscount.value);
});

const computedGrandTotal = returnGrandTotal;

const fetchNextNumber = async () => {
  try {
    const res = await axios.get('/api/purchase-returns/next-number');
    form.return_number = res.data.next_number;
  } catch (err) {
    console.error('Error fetching next return number:', err);
  }
};

const fetchSuppliers = async () => {
  try {
    const res = await axios.get('/api/suppliers');
    suppliers.value = res.data.data || res.data || [];
  } catch (err) {
    console.error('Error fetching suppliers:', err);
  }
};

const fetchPurchaseOrders = async () => {
  try {
    const res = await axios.get('/api/purchase-orders?per_page=100');
    purchaseOrders.value = res.data.purchase_orders?.data || res.data.data || [];
  } catch (err) {
    console.error('Error fetching POs:', err);
  }
};

const fetchWarehouses = async () => {
  try {
    const res = await axios.get('/api/warehouses');
    warehouses.value = res.data.data || res.data || [];
    if (warehouses.value.length > 0) {
      form.warehouse_id = warehouses.value[0].id;
    }
  } catch (err) {
    console.error('Error fetching warehouses:', err);
  }
};

const fetchProducts = async () => {
  try {
    const res = await axios.get('/api/products?per_page=500');
    products.value = res.data.data || res.data || [];
  } catch (err) {
    console.error('Error fetching products:', err);
  }
};


const getSupplierName = (id) => {
  const sup = suppliers.value.find(s => s.id == id);
  return sup ? sup.name : '';
};

const onSupplierChange = () => {
  if (form.purchase_order_id) {
    const selectedPo = purchaseOrders.value.find(po => po.id == form.purchase_order_id);
    if (selectedPo && selectedPo.supplier_id != form.supplier_id) {
      form.purchase_order_id = '';
      selectedPoNumber.value = '';
      selectedPoData.value = null;
    }
  }
};

const onPoChange = async (poIdOverride = null) => {
  const poId = poIdOverride || form.purchase_order_id;
  if (!poId) {
    selectedPoNumber.value = '';
    selectedPoData.value = null;
    form.items = [];
    return;
  }

  try {
    let poData = null;
    let rawItems = [];

    try {
      const res = await axios.get(`/api/purchase-orders/${poId}`);
      poData = res.data;
      rawItems = poData.purchase_order_items || poData.purchaseOrderItems || poData.items || [];
    } catch (e) {
      const res = await axios.get(`/api/purchase-returns/po-items/${poId}`);
      poData = res.data.purchase_order || res.data;
      rawItems = res.data.items || poData.purchase_order_items || [];
    }

    if (poData) {
      selectedPoData.value = poData;
      if (!form.supplier_id && poData.supplier_id) {
        form.supplier_id = poData.supplier_id;
      }
      selectedPoNumber.value = poData.po_number || '';

      const due = poData.due_amount !== undefined ? parseFloat(poData.due_amount) : Math.max(0, parseFloat(poData.total_amount || 0) - parseFloat(poData.amount_paid || 0));
      const paid = parseFloat(poData.amount_paid || 0);

      if (due > 0 && paid <= 0) {
        form.payment_method = 'ap_credit';
      } else if (paid > 0 && due > 0) {
        form.payment_method = 'mixed';
      } else if (due <= 0 && form.payment_method === 'ap_credit') {
        form.payment_method = 'cash';
      }
    }

    if (rawItems.length > 0) {
      form.items = rawItems.map(i => {
        const qtyPurchased = Number(
          i.qty_purchased ?? i.quantity_received ?? i.quantity_ordered ?? i.quantity ?? 0
        );
        return {
          product_id: i.product_id,
          product_name: i.product?.name || i.product_name || 'Unknown Product',
          product_sku: i.product?.sku || i.product_sku || '',
          unit_cost: parseFloat(i.unit_cost || i.unit_price || 0),
          quantity: qtyPurchased > 0 ? qtyPurchased : 1,
          qty_returned: qtyPurchased > 0 ? qtyPurchased : 1,
          max_returnable: qtyPurchased > 0 ? qtyPurchased : undefined,
          tax_amount: parseFloat(i.tax_amount || 0),
          discount_amount: parseFloat(i.discount_amount || 0),
        };
      });
    }

  } catch (err) {
    console.error('Error loading PO items:', err);
  }
};

watch(
  () => form.purchase_order_id,
  (newPoId, oldPoId) => {
    if (newPoId && newPoId !== oldPoId) {
      onPoChange(newPoId);
    }
  }
);

const addProductToReturn = (product) => {
  const existing = form.items.find(i => i.product_id === product.id);
  if (existing) {
    existing.quantity += 1;
  } else {
    form.items.push({
      product_id: product.id,
      product_name: product.name,
      product_sku: product.sku,
      unit_cost: parseFloat(product.cost_price || product.selling_price || 0),
      quantity: 1,
      tax_amount: 0,
      discount_amount: 0,
    });
  }
  productSearch.value = '';
  isProductDropdownOpen.value = false;
};

const validateItemQty = (item) => {
  if (item.max_returnable !== undefined && item.quantity > item.max_returnable) {
    alert(`Quantity cannot exceed the maximum returnable quantity (${item.max_returnable}) for this PO.`);
    item.quantity = item.max_returnable;
  }
};

const calculateLineTotal = (item) => {
  const sub = (parseFloat(item.unit_cost) || 0) * (parseFloat(item.quantity) || 0);
  const tax = parseFloat(item.tax_amount) || 0;
  const disc = parseFloat(item.discount_amount) || 0;
  return Math.max(0, (sub + tax) - disc);
};

const removeItem = (idx) => {
  form.items.splice(idx, 1);
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString();
};

const formatCurrency = (val) => {
  return parseFloat(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const submitReturn = async (overrideStatus = null) => {
  if (!form.supplier_id) {
    alert('Please select a supplier.');
    return;
  }
  if (form.items.length === 0) {
    alert('Please add at least one line item to return.');
    return;
  }

  if (overrideStatus) {
    form.status = overrideStatus;
  }

  submitting.value = true;
  try {
    await axios.post('/api/purchase-returns', form);
    router.push('/purchase/returns');
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to submit purchase return');
  } finally {
    submitting.value = false;
  }
};

const fetchBankAccounts = async () => {
  try {
    const res = await axios.get('/api/bank-accounts');
    bankAccounts.value = res.data.data || res.data || [];
  } catch (err) {
    console.error('Error fetching bank accounts:', err);
  }
};

onMounted(() => {
  fetchNextNumber();
  fetchSuppliers();
  fetchPurchaseOrders();
  fetchWarehouses();
  fetchProducts();
  fetchBankAccounts();
  document.addEventListener('click', closeAllDropdowns);
});

onUnmounted(() => {
  document.removeEventListener('click', closeAllDropdowns);
});
</script>
