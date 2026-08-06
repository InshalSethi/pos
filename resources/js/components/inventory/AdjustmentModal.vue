<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm" role="dialog" @click.self="close">
    <div class="relative bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-[28px] max-w-4xl w-full overflow-hidden shadow-2xl flex flex-col max-h-[92vh] animate-in zoom-in-95 duration-200">
      
      <!-- Modal Header -->
      <div class="p-6 pb-4 border-b border-slate-200 dark:border-[#2E2E2E] flex justify-between items-center shrink-0">
        <div>
          <h2 class="text-base font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
            <span>⚙️</span>
            Create Inventory Adjustment
          </h2>
          <p class="text-[10px] text-slate-400 uppercase tracking-widest mt-0.5 dark:text-slate-400">
            Multi-warehouse stock recounts, bulk price updates, and tax assignments
          </p>
        </div>
        <button
          type="button"
          @click="close"
          class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#2D2D2D]/80 rounded-xl transition-all cursor-pointer"
        >
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Tab Switcher Navigation (Categories & Brands style) -->
      <div class="flex border-b border-slate-200 dark:border-[#2E2E2E] px-6 bg-slate-50/50 dark:bg-zinc-950/40 shrink-0">
        <button
          type="button"
          @click="activeTab = 'stock'"
          :class="[
            'pb-3 pt-3.5 px-5 text-xs font-extrabold border-b-2 transition-all duration-150 cursor-pointer flex items-center gap-2 uppercase tracking-wider',
            activeTab === 'stock'
              ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400'
              : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'
          ]"
        >
          <span>📦</span> Stock Adjustment
        </button>
        <button
          type="button"
          @click="activeTab = 'price'"
          :class="[
            'pb-3 pt-3.5 px-5 text-xs font-extrabold border-b-2 transition-all duration-150 cursor-pointer flex items-center gap-2 uppercase tracking-wider',
            activeTab === 'price'
              ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400'
              : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'
          ]"
        >
          <span>🏷️</span> Price Adjustment
        </button>
        <button
          type="button"
          @click="activeTab = 'tax'"
          :class="[
            'pb-3 pt-3.5 px-5 text-xs font-extrabold border-b-2 transition-all duration-150 cursor-pointer flex items-center gap-2 uppercase tracking-wider',
            activeTab === 'tax'
              ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400'
              : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'
          ]"
        >
          <span>⚡</span> Tax Adjustment
        </button>
      </div>

      <!-- Tab Contents (Scrollable Container) -->
      <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-6">

        <!-- ==================== TAB 1: STOCK ADJUSTMENT ==================== -->
        <form v-if="activeTab === 'stock'" @submit.prevent="submitStockAdjustment" class="space-y-6">
          <!-- 1. Select Item Section -->
          <div class="space-y-3">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest dark:text-slate-400">
              1. Select Item to Adjust
            </h3>
            
            <div class="relative">
              <input
                v-model="productSearchQuery"
                @input="searchProducts"
                type="text"
                placeholder="Search product by name, SKU, or barcode..."
                class="w-full pl-9 pr-24 py-2.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] focus:border-indigo-500 rounded-xl outline-none transition-all dark:text-slate-200"
              />
              <span class="absolute left-3 top-3 text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
              </span>
              <button
                v-if="selectedProduct"
                type="button"
                @click="clearSelectedProduct"
                class="absolute right-3 top-2.5 text-[11px] font-bold text-rose-500 hover:text-rose-700 cursor-pointer"
              >
                × Clear Selection
              </button>
            </div>

            <!-- Search Dropdown Suggestions -->
            <div v-if="searchResults.length > 0 && !selectedProduct" class="bg-white dark:bg-[#252525] border border-slate-200 dark:border-[#2E2E2E] rounded-xl max-h-48 overflow-y-auto divide-y divide-slate-100 dark:divide-[#2E2E2E] shadow-lg">
              <div
                v-for="p in searchResults"
                :key="p.id"
                @click="selectProduct(p)"
                class="p-2.5 px-4 text-xs hover:bg-indigo-50 dark:hover:bg-zinc-800/80 cursor-pointer flex items-center justify-between"
              >
                <div>
                  <span class="font-bold text-slate-800 dark:text-slate-200">{{ p.name }}</span>
                  <span class="text-[10px] text-slate-400 ml-2 font-mono">SKU: {{ p.sku }}</span>
                </div>
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Stock: {{ p.stock_quantity }}</span>
              </div>
            </div>

            <!-- Selected Product Detail Card -->
            <div v-if="selectedProduct" class="p-4 bg-slate-50 dark:bg-zinc-950 rounded-2xl border border-slate-200 dark:border-[#2E2E2E] flex items-center gap-4">
              <div class="w-14 h-14 rounded-xl bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] flex items-center justify-center shrink-0 overflow-hidden">
                <img v-if="selectedProduct.image" :src="selectedProduct.image" class="w-full h-full object-contain p-1" />
                <span v-else class="text-xl">📦</span>
              </div>
              <div class="flex-1 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                <div>
                  <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Product</span>
                  <span class="font-extrabold text-slate-800 dark:text-slate-100 truncate block">{{ selectedProduct.name }}</span>
                </div>
                <div>
                  <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">SKU</span>
                  <span class="font-mono text-slate-600 dark:text-slate-400 block">{{ selectedProduct.sku }}</span>
                </div>
                <div>
                  <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Base Price</span>
                  <span class="font-bold text-slate-800 dark:text-slate-200 block">${{ parseFloat(selectedProduct.selling_price || 0).toFixed(2) }}</span>
                </div>
                <div>
                  <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Current Total Stock</span>
                  <span class="font-black text-indigo-600 dark:text-indigo-400 text-sm block">{{ selectedProduct.stock_quantity }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 2. Warehouse Allocation Table -->
          <div v-if="selectedProduct" class="space-y-3">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest dark:text-slate-400">
              2. Warehouse Allocation & Adjustments
            </h3>

            <!-- Checkbox toggles for active warehouses -->
            <div class="p-3 bg-slate-50 dark:bg-zinc-950 rounded-xl border border-slate-200 dark:border-[#2E2E2E] flex flex-wrap gap-4">
              <span class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider self-center">Adjust Stock In:</span>
              <label v-for="wh in warehouses" :key="wh.id" class="flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
                <input type="checkbox" v-model="stockForm.selectedWarehouses" :value="wh.id" class="rounded text-indigo-600 cursor-pointer" />
                <span>{{ wh.name }}</span>
                <span v-if="wh.is_default" class="text-[9px] bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400 px-1.5 py-0.2 rounded font-bold">DEFAULT</span>
              </label>
            </div>

            <!-- Warehouse Table -->
            <div class="border border-slate-200 dark:border-[#2E2E2E] rounded-2xl overflow-hidden">
              <table class="min-w-full divide-y divide-slate-100 dark:divide-[#2E2E2E]">
                <thead class="bg-slate-50 dark:bg-[#252525] text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                  <tr>
                    <th class="px-4 py-3 text-left">Warehouse Location</th>
                    <th class="px-4 py-3 text-center">Current Qty</th>
                    <th class="px-4 py-3 text-center w-40">Adjustment Type</th>
                    <th class="px-4 py-3 text-center w-32">Quantity</th>
                    <th class="px-4 py-3 text-center">Resulting Qty</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-[#2E2E2E] bg-white dark:bg-[#1E1E1E] text-xs">
                  <tr v-for="whId in stockForm.selectedWarehouses" :key="whId">
                    <td class="px-4 py-3 font-bold text-slate-800 dark:text-slate-200">
                      {{ getWarehouseName(whId) }}
                    </td>
                    <td class="px-4 py-3 text-center font-bold text-slate-500 dark:text-slate-400">
                      {{ getWarehouseStock(whId) }}
                    </td>
                    <td class="px-4 py-3 text-center">
                      <CustomFloatingSelect
                        v-model="getWarehouseRow(whId).adjustment_type"
                        :options="stockAdjustmentTypes"
                        buttonClass="!py-1 !text-xs !bg-slate-50 dark:!bg-zinc-950"
                      />
                    </td>
                    <td class="px-4 py-3 text-center">
                      <input
                        type="number"
                        min="0"
                        v-model.number="getWarehouseRow(whId).quantity_adjusted"
                        class="w-24 text-center px-2 py-1 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-lg font-extrabold outline-none"
                      />
                    </td>
                    <td class="px-4 py-3 text-center font-black text-indigo-600 dark:text-indigo-400">
                      {{ getResultingStock(whId) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- 3. Attributes & Documentation -->
          <div v-if="selectedProduct" class="space-y-4">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest dark:text-slate-400">
              3. Adjustment Documentation & Reason
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-450">Adjustment Reason *</label>
                <CustomFloatingSelect
                  v-model="stockForm.reason"
                  :options="reasonOptions"
                  placeholder="Select Reason..."
                  buttonClass="!py-2 !bg-slate-50 dark:!bg-zinc-950"
                />
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-450">Reference Attachment (PDF/IMAGE)</label>
                <input
                  type="file"
                  @change="handleAttachmentChange"
                  accept="image/*,application/pdf"
                  class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 dark:file:bg-zinc-800 file:text-slate-700 dark:file:text-slate-300 cursor-pointer"
                />
              </div>
            </div>

            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-450">Notes / Internal Explanation</label>
              <textarea
                v-model="stockForm.notes"
                rows="2"
                placeholder="Optional detailed notes..."
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-200 resize-none"
              ></textarea>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-[#2E2E2E]">
            <button type="button" @click="close" class="px-4 py-2 border border-slate-200 dark:border-[#2E2E2E] hover:bg-slate-50 dark:hover:bg-[#2D2D2D]/80 text-slate-500 dark:text-slate-400 font-bold rounded-xl text-xs uppercase tracking-wider cursor-pointer">
              Cancel
            </button>
            <button
              type="submit"
              :disabled="savingStock || !selectedProduct"
              class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-md transition-all cursor-pointer flex items-center gap-1.5"
            >
              <span v-if="savingStock" class="w-3 h-3 rounded-full border-2 border-white border-t-transparent animate-spin"></span>
              <span>{{ savingStock ? 'Saving Stock...' : 'Save Stock Adjustment' }}</span>
            </button>
          </div>
        </form>

        <!-- ==================== TAB 2: PRICE ADJUSTMENT ==================== -->
        <form v-if="activeTab === 'price'" @submit.prevent="submitPriceAdjustment" class="space-y-6">
          <div class="p-5 bg-slate-50/70 dark:bg-zinc-950 rounded-2xl border border-slate-200 dark:border-[#2E2E2E] space-y-4">
            <h3 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest flex items-center gap-1.5">
              <span>⚡</span> Bulk Pricing Action Configuration
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <!-- Target Filter -->
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Target Filter</label>
                <CustomFloatingSelect
                  v-model="priceForm.target_type"
                  :options="priceTargetOptions"
                  @change="loadPreviewProducts"
                  buttonClass="!py-2 !bg-white dark:!bg-zinc-900"
                />
              </div>

              <!-- Target Category/Brand Selector -->
              <div v-if="priceForm.target_type === 'category'">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Select Category</label>
                <CustomFloatingSelect
                  v-model="priceForm.target_id"
                  :options="categoryOptions"
                  :searchable="true"
                  placeholder="Choose Category..."
                  @change="loadPreviewProducts"
                  buttonClass="!py-2 !bg-white dark:!bg-zinc-900"
                />
              </div>
              <div v-else-if="priceForm.target_type === 'brand'">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Select Brand</label>
                <CustomFloatingSelect
                  v-model="priceForm.target_id"
                  :options="brandOptions"
                  :searchable="true"
                  placeholder="Choose Brand..."
                  @change="loadPreviewProducts"
                  buttonClass="!py-2 !bg-white dark:!bg-zinc-900"
                />
              </div>
              <div v-else-if="priceForm.target_type === 'warehouse'">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Select Warehouse</label>
                <CustomFloatingSelect
                  v-model="priceForm.target_id"
                  :options="warehouseOptions"
                  placeholder="Choose Warehouse..."
                  @change="loadPreviewProducts"
                  buttonClass="!py-2 !bg-white dark:!bg-zinc-900"
                />
              </div>

              <!-- Action Type -->
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Action Type</label>
                <CustomFloatingSelect
                  v-model="priceForm.action_type"
                  :options="priceActionTypes"
                  @change="loadPreviewProducts"
                  buttonClass="!py-2 !bg-white dark:!bg-zinc-900"
                />
              </div>
            </div>

            <!-- Value Input -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">
                  {{ priceForm.action_type === 'fixed_price_override' ? 'Fixed Price Override ($)' : 'Percentage Value (%)' }}
                </label>
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  v-model.number="priceForm.value"
                  @input="loadPreviewProducts"
                  placeholder="e.g. 15"
                  class="w-full px-3 py-2 text-xs bg-white dark:bg-zinc-900 border border-slate-200 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-100 font-bold"
                />
              </div>

              <div v-if="priceForm.target_type === 'category'" class="pt-4">
                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-600 dark:text-slate-300">
                  <input
                    type="checkbox"
                    v-model="priceForm.apply_to_subcategories"
                    @change="loadPreviewProducts"
                    class="rounded text-indigo-600 cursor-pointer"
                  />
                  Apply recursively to all nested child categories
                </label>
              </div>
            </div>
          </div>

          <!-- Product Lookup Preview Table -->
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest dark:text-slate-400">
                Products Lookup & Price Preview ({{ previewProductsList.length }} Items)
              </h3>
              <div class="relative w-48">
                <input
                  v-model="previewSearch"
                  type="text"
                  placeholder="Filter products..."
                  class="w-full px-2.5 py-1 text-[11px] bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-lg outline-none text-slate-800 dark:text-slate-200"
                />
              </div>
            </div>

            <div class="border border-slate-200 dark:border-[#2E2E2E] rounded-2xl overflow-hidden max-h-60 overflow-y-auto">
              <table class="min-w-full divide-y divide-slate-100 dark:divide-[#2E2E2E]">
                <thead class="bg-slate-50 dark:bg-[#252525] text-[9px] font-black text-slate-400 uppercase tracking-wider sticky top-0 bg-white dark:bg-[#252525] z-10">
                  <tr>
                    <th class="px-4 py-2.5 text-left">SKU</th>
                    <th class="px-4 py-2.5 text-left">Name</th>
                    <th class="px-4 py-2.5 text-left">Category / Brand</th>
                    <th class="px-4 py-2.5 text-right">Purchase Price</th>
                    <th class="px-4 py-2.5 text-right">Current Price</th>
                    <th class="px-4 py-2.5 text-right">New Price</th>
                    <th class="px-4 py-2.5 text-center">Stock</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-[#2E2E2E] bg-white dark:bg-[#1E1E1E] text-xs">
                  <tr v-for="p in filteredPreviewProducts" :key="p.id">
                    <td class="px-4 py-2 font-mono text-slate-500 dark:text-slate-400">{{ p.sku }}</td>
                    <td class="px-4 py-2 font-bold text-slate-800 dark:text-slate-200">{{ p.name }}</td>
                    <td class="px-4 py-2 text-slate-500 dark:text-slate-400">{{ p.category_name }} / {{ p.brand_name }}</td>
                    <td class="px-4 py-2 text-right text-slate-500 dark:text-slate-400">${{ p.cost_price.toFixed(2) }}</td>
                    <td class="px-4 py-2 text-right font-semibold text-slate-700 dark:text-slate-300">${{ p.current_price.toFixed(2) }}</td>
                    <td class="px-4 py-2 text-right font-black text-emerald-600 dark:text-emerald-400">${{ p.new_price.toFixed(2) }}</td>
                    <td class="px-4 py-2 text-center font-bold text-slate-700 dark:text-slate-300">{{ p.stock_quantity }}</td>
                  </tr>
                  <tr v-if="filteredPreviewProducts.length === 0">
                    <td colspan="7" class="px-4 py-8 text-center text-slate-400 font-medium">No matching products found.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-[#2E2E2E]">
            <button type="button" @click="close" class="px-4 py-2 border border-slate-200 dark:border-[#2E2E2E] hover:bg-slate-50 dark:hover:bg-[#2D2D2D]/80 text-slate-500 dark:text-slate-400 font-bold rounded-xl text-xs uppercase tracking-wider cursor-pointer">
              Cancel
            </button>
            <button
              type="submit"
              :disabled="savingPrice || previewProductsList.length === 0"
              class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-md transition-all cursor-pointer flex items-center gap-1.5"
            >
              <span v-if="savingPrice" class="w-3 h-3 rounded-full border-2 border-white border-t-transparent animate-spin"></span>
              <span>{{ savingPrice ? 'Executing Rules...' : 'Apply Price Adjustments' }}</span>
            </button>
          </div>
        </form>

        <!-- ==================== TAB 3: TAX ADJUSTMENT ==================== -->
        <form v-if="activeTab === 'tax'" @submit.prevent="submitTaxAdjustment" class="space-y-6">
          <div class="p-5 bg-slate-50/70 dark:bg-zinc-950 rounded-2xl border border-slate-200 dark:border-[#2E2E2E] space-y-4">
            <h3 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest flex items-center gap-1.5">
              <span>⚡</span> Bulk Tax Assignment Configuration
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <!-- Target Filter -->
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Target Filter</label>
                <CustomFloatingSelect
                  v-model="taxForm.target_type"
                  :options="taxTargetOptions"
                  @change="loadPreviewProducts"
                  buttonClass="!py-2 !bg-white dark:!bg-zinc-900"
                />
              </div>

              <!-- Target Category/Brand Selector -->
              <div v-if="taxForm.target_type === 'category'">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Select Category</label>
                <CustomFloatingSelect
                  v-model="taxForm.target_id"
                  :options="categoryOptions"
                  :searchable="true"
                  placeholder="Choose Category..."
                  @change="loadPreviewProducts"
                  buttonClass="!py-2 !bg-white dark:!bg-zinc-900"
                />
              </div>
              <div v-else-if="taxForm.target_type === 'brand'">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Select Brand</label>
                <CustomFloatingSelect
                  v-model="taxForm.target_id"
                  :options="brandOptions"
                  :searchable="true"
                  placeholder="Choose Brand..."
                  @change="loadPreviewProducts"
                  buttonClass="!py-2 !bg-white dark:!bg-zinc-900"
                />
              </div>

              <!-- Tax Rate Selector -->
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 dark:text-slate-400">Assign Tax Group</label>
                <CustomFloatingSelect
                  v-model="taxForm.tax_id"
                  :options="taxGroupOptions"
                  placeholder="Select Tax Group..."
                  @change="loadPreviewProducts"
                  buttonClass="!py-2 !bg-white dark:!bg-zinc-900"
                />
              </div>
            </div>

            <div v-if="taxForm.target_type === 'category'" class="pt-2">
              <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-600 dark:text-slate-300">
                <input
                  type="checkbox"
                  v-model="taxForm.apply_to_subcategories"
                  @change="loadPreviewProducts"
                  class="rounded text-indigo-600 cursor-pointer"
                />
                Apply recursively to all nested child categories
              </label>
            </div>
          </div>

          <!-- Product Lookup Preview Table -->
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest dark:text-slate-400">
                Products Lookup & Tax Assignment Preview ({{ previewProductsList.length }} Items)
              </h3>
              <div class="relative w-48">
                <input
                  v-model="previewSearch"
                  type="text"
                  placeholder="Filter products..."
                  class="w-full px-2.5 py-1 text-[11px] bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-lg outline-none text-slate-800 dark:text-slate-200"
                />
              </div>
            </div>

            <div class="border border-slate-200 dark:border-[#2E2E2E] rounded-2xl overflow-hidden max-h-60 overflow-y-auto">
              <table class="min-w-full divide-y divide-slate-100 dark:divide-[#2E2E2E]">
                <thead class="bg-slate-50 dark:bg-[#252525] text-[9px] font-black text-slate-400 uppercase tracking-wider sticky top-0 bg-white dark:bg-[#252525] z-10">
                  <tr>
                    <th class="px-4 py-2.5 text-left">SKU</th>
                    <th class="px-4 py-2.5 text-left">Name</th>
                    <th class="px-4 py-2.5 text-left">Category</th>
                    <th class="px-4 py-2.5 text-center">Current Tax Rate</th>
                    <th class="px-4 py-2.5 text-center">New Assigned Tax</th>
                    <th class="px-4 py-2.5 text-center">Stock</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-[#2E2E2E] bg-white dark:bg-[#1E1E1E] text-xs">
                  <tr v-for="p in filteredPreviewProducts" :key="p.id">
                    <td class="px-4 py-2 font-mono text-slate-500 dark:text-slate-400">{{ p.sku }}</td>
                    <td class="px-4 py-2 font-bold text-slate-800 dark:text-slate-200">{{ p.name }}</td>
                    <td class="px-4 py-2 text-slate-500 dark:text-slate-400">{{ p.category_name }}</td>
                    <td class="px-4 py-2 text-center text-slate-500 dark:text-slate-400">{{ p.current_tax_name }} ({{ p.current_tax_rate }}%)</td>
                    <td class="px-4 py-2 text-center font-black text-emerald-600 dark:text-emerald-400">{{ p.new_tax_name }} ({{ p.new_tax_rate }}%)</td>
                    <td class="px-4 py-2 text-center font-bold text-slate-700 dark:text-slate-300">{{ p.stock_quantity }}</td>
                  </tr>
                  <tr v-if="filteredPreviewProducts.length === 0">
                    <td colspan="6" class="px-4 py-8 text-center text-slate-400 font-medium">No matching products found.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-[#2E2E2E]">
            <button type="button" @click="close" class="px-4 py-2 border border-slate-200 dark:border-[#2E2E2E] hover:bg-slate-50 dark:hover:bg-[#2D2D2D]/80 text-slate-500 dark:text-slate-400 font-bold rounded-xl text-xs uppercase tracking-wider cursor-pointer">
              Cancel
            </button>
            <button
              type="submit"
              :disabled="savingTax || previewProductsList.length === 0"
              class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-md transition-all cursor-pointer flex items-center gap-1.5"
            >
              <span v-if="savingTax" class="w-3 h-3 rounded-full border-2 border-white border-t-transparent animate-spin"></span>
              <span>{{ savingTax ? 'Executing Rules...' : 'Apply Tax Adjustments' }}</span>
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import CustomFloatingSelect from '@/components/common/CustomFloatingSelect.vue';

const props = defineProps({
  show: { type: Boolean, default: false }
});

const emit = defineEmits(['close', 'saved']);

const { showToast } = useToast();
const { confirm } = useConfirm();

const activeTab = ref('stock');

// Shared collections
const warehouses = ref([]);
const categories = ref([]);
const brands = ref([]);
const taxes = ref([]);

// Tab 1: Stock Adjustment States
const productSearchQuery = ref('');
const searchResults = ref([]);
const selectedProduct = ref(null);
const stockForm = ref({
  selectedWarehouses: [],
  warehouseRows: {},
  reason: 'Discrepancy / Audit',
  notes: '',
  attachment: null
});
const savingStock = ref(false);

const stockAdjustmentTypes = [
  { label: 'Stock Increase', value: 'increase' },
  { label: 'Stock Decrease', value: 'decrease' },
  { label: 'Recount Override', value: 'recount' }
];

const reasonOptions = [
  { label: 'Discrepancy / Audit', value: 'Discrepancy / Audit' },
  { label: 'Damaged Stock', value: 'Damaged Stock' },
  { label: 'Stolen / Lost', value: 'Stolen / Lost' },
  { label: 'Promotion Sample', value: 'Promotion Sample' },
  { label: 'Internal Use', value: 'Internal Use' },
  { label: 'Restock / Delivery', value: 'Restock / Delivery' }
];

// Tab 2: Price Adjustment States
const priceForm = ref({
  target_type: 'all',
  target_id: null,
  action_type: 'markup_percentage',
  value: 0,
  apply_to_subcategories: false
});
const savingPrice = ref(false);

const priceTargetOptions = [
  { label: 'All Active Products', value: 'all' },
  { label: 'By Category', value: 'category' },
  { label: 'By Brand', value: 'brand' },
  { label: 'By Warehouse', value: 'warehouse' }
];

const priceActionTypes = [
  { label: 'Apply Markup Percentage (%)', value: 'markup_percentage' },
  { label: 'Apply Discount Percentage (%)', value: 'discount_percentage' },
  { label: 'Fixed Price Override ($)', value: 'fixed_price_override' }
];

// Tab 3: Tax Adjustment States
const taxForm = ref({
  target_type: 'all',
  target_id: null,
  tax_id: null,
  apply_to_subcategories: false
});
const savingTax = ref(false);

const taxTargetOptions = [
  { label: 'All Active Products', value: 'all' },
  { label: 'By Category', value: 'category' },
  { label: 'By Brand', value: 'brand' }
];

// Preview Table States
const previewProductsList = ref([]);
const previewSearch = ref('');

// Computed Floating Dropdown Options
const categoryOptions = computed(() => categories.value.map(c => ({ label: c.name, value: c.id })));
const brandOptions = computed(() => brands.value.map(b => ({ label: b.name, value: b.id })));
const warehouseOptions = computed(() => warehouses.value.map(w => ({ label: w.name, value: w.id })));
const taxGroupOptions = computed(() => [
  { label: 'Unassigned (No Tax)', value: null },
  ...taxes.value.map(t => ({ label: `${t.name} (${parseFloat(t.value)}%)`, value: t.id }))
]);

const filteredPreviewProducts = computed(() => {
  if (!previewSearch.value.trim()) return previewProductsList.value;
  const q = previewSearch.value.toLowerCase();
  return previewProductsList.value.filter(p =>
    p.name.toLowerCase().includes(q) ||
    p.sku.toLowerCase().includes(q) ||
    p.category_name.toLowerCase().includes(q)
  );
});

// Load reference data
const loadRefData = async () => {
  try {
    const [whRes, catRes, brRes, taxRes] = await Promise.all([
      axios.get('/api/warehouses'),
      axios.get('/api/categories'),
      axios.get('/api/brands'),
      axios.get('/api/taxes')
    ]);
    warehouses.value = whRes.data.data || whRes.data || [];
    categories.value = catRes.data || [];
    brands.value = brRes.data || [];
    taxes.value = taxRes.data || [];

    // Pre-select default warehouse
    const def = warehouses.value.find(w => w.is_default) || warehouses.value[0];
    if (def && stockForm.value.selectedWarehouses.length === 0) {
      stockForm.value.selectedWarehouses = [def.id];
    }
  } catch (err) {
    showToast('Failed to load system reference data.', 'error');
  }
};

// Search products for stock adjustment tab
let searchTimeout = null;
const searchProducts = () => {
  clearTimeout(searchTimeout);
  if (!productSearchQuery.value.trim()) {
    searchResults.value = [];
    return;
  }
  searchTimeout = setTimeout(async () => {
    try {
      const res = await axios.get('/api/inventory/low-stock', {
        params: { search: productSearchQuery.value, per_page: 10 }
      });
      searchResults.value = res.data.data || res.data || [];
    } catch (err) {
      searchResults.value = [];
    }
  }, 300);
};

const selectProduct = (prod) => {
  selectedProduct.value = prod;
  productSearchQuery.value = prod.name;
  searchResults.value = [];

  // Reset warehouse rows
  stockForm.value.warehouseRows = {};
  warehouses.value.forEach(w => {
    stockForm.value.warehouseRows[w.id] = {
      adjustment_type: 'increase',
      quantity_adjusted: 0,
      current_qty: getWarehouseStock(w.id)
    };
  });
};

const clearSelectedProduct = () => {
  selectedProduct.value = null;
  productSearchQuery.value = '';
  searchResults.value = [];
};

const getWarehouseName = (whId) => {
  const w = warehouses.value.find(item => item.id === whId);
  return w ? w.name : 'Warehouse';
};

const getWarehouseStock = (whId) => {
  if (!selectedProduct.value) return 0;
  if (selectedProduct.value.warehouse_id === whId) {
    return selectedProduct.value.stock_quantity || 0;
  }
  return selectedProduct.value.stock_quantity || 0;
};

const getWarehouseRow = (whId) => {
  if (!stockForm.value.warehouseRows[whId]) {
    stockForm.value.warehouseRows[whId] = {
      adjustment_type: 'increase',
      quantity_adjusted: 0,
      current_qty: getWarehouseStock(whId)
    };
  }
  return stockForm.value.warehouseRows[whId];
};

const getResultingStock = (whId) => {
  const current = getWarehouseStock(whId);
  const row = getWarehouseRow(whId);
  const qty = parseInt(row.quantity_adjusted || 0);

  if (row.adjustment_type === 'increase') return current + qty;
  if (row.adjustment_type === 'decrease') return Math.max(0, current - qty);
  if (row.adjustment_type === 'recount') return qty;
  return current;
};

const handleAttachmentChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    stockForm.value.attachment = file;
  }
};

// Stock Adjustment Submit
const submitStockAdjustment = async () => {
  if (!selectedProduct.value) {
    showToast('Please select a product first.', 'error');
    return;
  }
  if (stockForm.value.selectedWarehouses.length === 0) {
    showToast('Please select at least one warehouse to adjust.', 'error');
    return;
  }

  savingStock.value = true;
  const formData = new FormData();
  formData.append('product_id', selectedProduct.value.id || selectedProduct.value.product_id);
  formData.append('reason', stockForm.value.reason);
  if (stockForm.value.notes) formData.append('notes', stockForm.value.notes);
  if (stockForm.value.attachment) formData.append('attachment', stockForm.value.attachment);

  const adjustments = stockForm.value.selectedWarehouses.map(whId => {
    const row = getWarehouseRow(whId);
    return {
      warehouse_id: whId,
      adjustment_type: row.adjustment_type,
      quantity_adjusted: row.quantity_adjusted
    };
  });

  adjustments.forEach((adj, idx) => {
    formData.append(`adjustments[${idx}][warehouse_id]`, adj.warehouse_id);
    formData.append(`adjustments[${idx}][adjustment_type]`, adj.adjustment_type);
    formData.append(`adjustments[${idx}][quantity_adjusted]`, adj.quantity_adjusted);
  });

  try {
    await axios.post('/api/inventory-adjustments', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    showToast('Stock adjustment recorded successfully.', 'success');
    emit('saved');
    close();
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to submit stock adjustment.';
    showToast(msg, 'error');
  } finally {
    savingStock.value = false;
  }
};

// Preview product loader for Tab 2 & 3
const loadPreviewProducts = async () => {
  const activeForm = activeTab.value === 'price' ? priceForm.value : taxForm.value;
  try {
    const params = {
      target_type: activeForm.target_type,
      target_id: activeForm.target_id,
      action_type: priceForm.value.action_type,
      value: priceForm.value.value,
      tax_id: taxForm.value.tax_id,
      apply_to_subcategories: activeForm.apply_to_subcategories ? '1' : '0'
    };
    const res = await axios.get('/api/inventory-adjustments/preview-products', { params });
    previewProductsList.value = res.data.products || [];
  } catch (err) {
    previewProductsList.value = [];
  }
};

// Price Adjustment Submit
const submitPriceAdjustment = async () => {
  const confirmed = await confirm({
    title: 'Confirm Bulk Price Adjustment',
    message: `Execute bulk price updates for ${previewProductsList.value.length} matching products? This action modifies selling prices in your active catalog.`,
    confirmText: 'Apply Prices',
    cancelText: 'Cancel',
    type: 'warning'
  });
  if (!confirmed) return;

  savingPrice.value = true;
  try {
    const res = await axios.post('/api/inventory-adjustments/bulk-price', priceForm.value);
    showToast(res.data.message || 'Price adjustment applied successfully.', 'success');
    emit('saved');
    close();
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to apply price adjustments.';
    showToast(msg, 'error');
  } finally {
    savingPrice.value = false;
  }
};

// Tax Adjustment Submit
const submitTaxAdjustment = async () => {
  const confirmed = await confirm({
    title: 'Confirm Bulk Tax Assignment',
    message: `Apply tax group rule updates to ${previewProductsList.value.length} matching products?`,
    confirmText: 'Apply Tax Rules',
    cancelText: 'Cancel',
    type: 'warning'
  });
  if (!confirmed) return;

  savingTax.value = true;
  try {
    const res = await axios.post('/api/inventory-adjustments/bulk-tax', taxForm.value);
    showToast(res.data.message || 'Tax assignment applied successfully.', 'success');
    emit('saved');
    close();
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to apply tax rules.';
    showToast(msg, 'error');
  } finally {
    savingTax.value = false;
  }
};

watch(() => props.show, (val) => {
  if (val) {
    loadRefData();
    loadPreviewProducts();
  }
});

watch(activeTab, () => {
  loadPreviewProducts();
});

const close = () => {
  emit('close');
};

onMounted(() => {
  if (props.show) {
    loadRefData();
    loadPreviewProducts();
  }
});
</script>
