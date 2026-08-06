<template>
  <div class="w-full mx-auto py-8 px-4 sm:px-6 lg:px-8 bg-slate-50/50 dark:bg-zinc-950 min-h-screen font-sans">
    <div class="w-full max-w-7xl mx-auto space-y-6">
      
      <!-- HEADER SECTION WITH BACK LINK -->
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <router-link
            to="/inventory/adjustments"
            class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 mb-2 transition-colors group cursor-pointer"
          >
            <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Adjustments
          </router-link>
          
          <h1 class="text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            Create Inventory Adjustment
          </h1>
          <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">
            Multi-warehouse stock recounts, bulk price updates, and tax assignments
          </p>
        </div>

        <div class="flex items-center gap-3">
          <button
            v-if="isMatrixFilterActive || activeInputCount > 0"
            type="button"
            @click="clearAllMatrixInputs"
            class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-rose-600 dark:hover:text-rose-400 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-[#2E2E2E] hover:border-rose-300 dark:hover:border-rose-800 rounded-xl transition-all cursor-pointer shadow-xs"
          >
            Clear Inputs
          </button>
          <button
            type="button"
            @click="submitUnifiedMatrix"
            :disabled="savingMatrix || activeInputCount === 0"
            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-md transition-all cursor-pointer flex items-center gap-2"
          >
            <span v-if="savingMatrix" class="w-3.5 h-3.5 rounded-full border-2 border-white border-t-transparent animate-spin"></span>
            <span>{{ savingMatrix ? 'Saving Adjustments...' : 'Save Unified Adjustments (' + activeInputCount + ')' }}</span>
          </button>
        </div>
      </div>

      <!-- MAIN MATRIX CARD & FILTERS -->
      <div class="bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-3xl shadow-sm overflow-hidden space-y-6 p-6">
        
        <!-- MULTI-FILTER BAR -->
        <div class="p-5 bg-slate-50/80 dark:bg-zinc-950/60 rounded-2xl border border-slate-200 dark:border-[#2E2E2E] space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">
              Live Catalog Filter & Location Target
            </h3>
            <span v-if="isMatrixFilterActive" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400">
              Showing {{ filteredMatrixRows.length }} matching matrix items
            </span>
            <span v-else class="text-[11px] font-semibold text-slate-400">
              Select items from search or apply filters below to populate matrix
            </span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
            
            <!-- Smart Live Search Input with Floating Top 50 Autocomplete Dropdown -->
            <div class="lg:col-span-2 relative">
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 dark:text-slate-400">
                Smart Search (Top 50 Autocomplete)
              </label>
              <div class="relative">
                <input
                  ref="searchInputRef"
                  v-model="searchQuery"
                  @focus="onSearchFocus"
                  @click="onSearchFocus"
                  @input="onSearchInput"
                  @keydown.down.prevent="navigateSuggestions(1)"
                  @keydown.up.prevent="navigateSuggestions(-1)"
                  @keydown.enter.prevent="selectHighlightedSuggestion"
                  @keydown.escape="showSearchDropdown = false"
                  type="text"
                  placeholder="Type name, SKU, or barcode (Arrow keys + Enter to select)..."
                  class="w-full pl-9 pr-8 py-2 text-xs bg-white dark:bg-zinc-900 border border-slate-200 dark:border-[#2E2E2E] focus:border-indigo-500 rounded-xl outline-none transition-all dark:text-slate-200 font-semibold shadow-xs"
                />
                <span class="absolute left-3 top-2.5 text-slate-400">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <button
                  v-if="searchQuery"
                  type="button"
                  @click="clearSearch"
                  class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 text-xs font-bold"
                >
                  ×
                </button>

                <!-- FLOATING SEARCH SUGGESTIONS DROPDOWN (TOP 50) -->
                <div
                  v-if="showSearchDropdown && searchSuggestions.length > 0"
                  class="absolute left-0 right-0 top-full mt-1.5 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-2xl max-h-72 overflow-y-auto divide-y divide-slate-100 dark:divide-[#2E2E2E] shadow-2xl z-50 transition-all"
                >
                  <div
                    v-for="(item, idx) in searchSuggestions"
                    :key="item.unique_id"
                    :ref="el => suggestionRefs[idx] = el"
                    @mousedown.prevent="selectSuggestionItem(item)"
                    @mouseenter="highlightedIndex = idx"
                    :class="[
                      'p-3 px-4 text-xs cursor-pointer flex items-center justify-between transition-colors',
                      highlightedIndex === idx
                        ? 'bg-indigo-50 dark:bg-zinc-800 border-l-4 border-indigo-600 text-slate-900 dark:text-slate-100'
                        : 'hover:bg-slate-50 dark:hover:bg-zinc-800/60 text-slate-800 dark:text-slate-200'
                    ]"
                  >
                    <div class="flex items-center gap-3">
                      <div
                        :class="[
                          'w-8 h-8 rounded-lg flex items-center justify-center shrink-0 font-extrabold text-[10px] border',
                          highlightedIndex === idx
                            ? 'bg-indigo-200/80 text-indigo-900 border-indigo-300 dark:bg-indigo-950 dark:text-indigo-300 dark:border-indigo-800'
                            : 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-zinc-900 dark:text-slate-300 dark:border-zinc-800'
                        ]"
                      >
                        {{ item.is_variation ? 'VAR' : 'PRD' }}
                      </div>
                      <div>
                        <div class="font-extrabold text-slate-800 dark:text-slate-100 flex items-center gap-1.5">
                          <span>{{ item.name }}</span>
                          <span v-if="item.is_variation" class="text-[9px] bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300 px-1 py-0.2 rounded font-bold">Variant</span>
                        </div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">SKU: {{ item.sku }} {{ item.barcode ? '| ' + item.barcode : '' }}</div>
                      </div>
                    </div>
                    <div class="text-right shrink-0">
                      <span class="text-xs font-black text-indigo-600 dark:text-indigo-400 block">{{ item.stock_quantity ?? 0 }} pcs</span>
                      <span class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold block">${{ parseFloat(item.selling_price || 0).toFixed(2) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Target Warehouse -->
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 dark:text-slate-400">Target Warehouse</label>
              <CustomFloatingSelect
                v-model="selectedWarehouse"
                :options="warehouseOptions"
                placeholder="Select Warehouse..."
                buttonClass="!py-2 !bg-white dark:!bg-zinc-900"
              />
            </div>

            <!-- Multi-Select Categories -->
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 dark:text-slate-400">Categories</label>
              <CustomFloatingSelect
                v-model="selectedCategories"
                :options="categoryOptions"
                :searchable="true"
                :multiple="true"
                placeholder="All Categories"
                buttonClass="!py-2 !bg-white dark:!bg-zinc-900"
              />
            </div>

            <!-- Multi-Select Brands -->
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 dark:text-slate-400">Brands</label>
              <CustomFloatingSelect
                v-model="selectedBrands"
                :options="brandOptions"
                :searchable="true"
                :multiple="true"
                placeholder="All Brands"
                buttonClass="!py-2 !bg-white dark:!bg-zinc-900"
              />
            </div>

            <!-- Multi-Select Tags Filter -->
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 dark:text-slate-400">Tags</label>
              <CustomFloatingSelect
                v-model="selectedTags"
                :options="tagOptions"
                :searchable="true"
                :multiple="true"
                placeholder="All Tags"
                buttonClass="!py-2 !bg-white dark:!bg-zinc-900"
              />
            </div>

          </div>

          <!-- Documentation & Audit Reason Row -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-200/60 dark:border-[#2E2E2E]">
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 dark:text-slate-400">Adjustment Reason *</label>
              <CustomFloatingSelect
                v-model="auditReason"
                :options="reasonOptions"
                placeholder="Select Reason..."
                buttonClass="!py-2 !bg-white dark:!bg-zinc-900"
              />
            </div>
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 dark:text-slate-400">Audit Notes / Reference</label>
              <input
                v-model="auditNotes"
                type="text"
                placeholder="e.g. Annual Audit recount & bulk price update"
                class="w-full px-3 py-2 text-xs bg-white dark:bg-zinc-900 border border-slate-200 dark:border-[#2E2E2E] rounded-xl outline-none focus:border-indigo-500 dark:text-slate-200 font-semibold"
              />
            </div>
          </div>
        </div>

        <!-- UNIFIED MATRIX DATA GRID TABLE -->
        <div class="border border-slate-200 dark:border-[#2E2E2E] rounded-2xl overflow-hidden shadow-xs">
          <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-[#2E2E2E]">
              <thead class="bg-slate-100/80 dark:bg-[#252525] text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider sticky top-0 z-20 backdrop-blur-md">
                <tr>
                  <th class="px-4 py-3.5 text-left min-w-[220px]">Item & Identifiers</th>
                  <th class="px-3 py-3.5 text-center min-w-[280px]">
                    📦 Stock Adjustment & Min Limit
                  </th>
                  <th class="px-3 py-3.5 text-center min-w-[320px]">
                    🏷️ Multi-Pricing (Selling / Wholesale / Purchase Price)
                  </th>
                  <th class="px-3 py-3.5 text-center min-w-[180px]">
                    ⚡ Tax Assignment
                  </th>
                  <th class="px-3 py-3.5 text-center min-w-[70px]">
                    Remove
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-[#2E2E2E] bg-white dark:bg-[#1E1E1E] text-xs">
                <template v-for="row in filteredMatrixRows" :key="row.id">
                  
                  <!-- Main Product Row -->
                  <tr class="hover:bg-slate-50/80 dark:hover:bg-zinc-900/50 transition-colors">
                    <!-- Item Identifier Column -->
                    <td class="px-4 py-3.5 align-top">
                      <div class="flex items-start gap-2.5">
                        <button
                          v-if="row.has_variations && row.variations.length > 0"
                          type="button"
                          @click="row.expanded = !row.expanded"
                          class="mt-0.5 px-1.5 py-0.5 text-[9px] font-black bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-400 rounded-md hover:bg-indigo-200 cursor-pointer shrink-0"
                        >
                          {{ row.expanded ? '▼ Collapse' : '▶ Expand (' + row.variations.length + ')' }}
                        </button>
                        <div>
                          <div class="font-extrabold text-slate-800 dark:text-slate-100 flex items-center gap-1.5">
                            <span>{{ row.name }}</span>
                          </div>
                          <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                            SKU: {{ row.sku }} {{ row.barcode ? '| ' + row.barcode : '' }}
                          </div>
                          <div class="flex flex-wrap gap-1 mt-1">
                            <span v-if="row.category_name" class="px-1.5 py-0.2 text-[9px] font-bold bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 rounded">
                              {{ row.category_name }}
                            </span>
                            <span v-if="row.brand_name" class="px-1.5 py-0.2 text-[9px] font-bold bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 rounded">
                              {{ row.brand_name }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </td>

                    <!-- Stock Adjustment Group Column -->
                    <td class="px-3 py-3.5 align-top">
                      <div class="space-y-2">
                        <div class="flex items-center justify-between text-[11px]">
                          <span class="text-slate-500 font-bold">Current Stock:</span>
                          <span class="font-black text-slate-800 dark:text-slate-100">{{ row.current_stock }} pcs</span>
                        </div>

                        <div class="grid grid-cols-2 gap-1.5">
                          <CustomFloatingSelect
                            v-model="row.stock_type"
                            :options="stockTypeOptions"
                            buttonClass="!py-1 !text-[11px] !bg-white dark:!bg-zinc-900"
                          />
                          <input
                            type="number"
                            min="0"
                            v-model.number="row.quantity_adjusted"
                            placeholder="Qty (+/-)"
                            class="px-2 py-1 text-xs bg-white dark:bg-zinc-900 border border-slate-200 dark:border-[#2E2E2E] rounded-xl font-bold outline-none text-center"
                          />
                        </div>

                        <div class="flex items-center justify-between text-[10px] pt-1">
                          <span class="text-slate-400 font-semibold">Resulting:</span>
                          <span class="font-extrabold text-indigo-600 dark:text-indigo-400">{{ getResultingStock(row) }} pcs</span>
                        </div>

                        <div class="pt-1 border-t border-slate-200/40 dark:border-[#2E2E2E] flex items-center justify-between gap-1 text-[10px]">
                          <span class="text-slate-400">Min Alert: {{ row.current_min_stock }}</span>
                          <input
                            type="number"
                            min="0"
                            v-model.number="row.min_stock_level"
                            placeholder="New Min"
                            class="w-20 px-1.5 py-0.5 text-[10px] bg-white dark:bg-zinc-900 border border-slate-200 dark:border-[#2E2E2E] rounded-lg text-center outline-none"
                          />
                        </div>
                      </div>
                    </td>

                    <!-- Multi-Pricing Group Column -->
                    <td class="px-3 py-3.5 align-top">
                      <div class="space-y-2 text-xs">
                        <!-- Selling Price -->
                        <div class="flex items-center justify-between gap-2">
                          <span class="text-[10px] font-bold text-slate-400 w-20">Sale Price:</span>
                          <span class="font-semibold text-slate-500 text-[11px]">${{ parseFloat(row.current_price || 0).toFixed(2) }}</span>
                          <input
                            type="number"
                            step="0.01"
                            min="0"
                            v-model.number="row.selling_price"
                            placeholder="New Sale"
                            class="w-24 px-2 py-1 text-xs bg-white dark:bg-zinc-900 border border-slate-200 dark:border-[#2E2E2E] rounded-xl font-bold text-right outline-none"
                          />
                        </div>

                        <!-- Wholesale Price -->
                        <div class="flex items-center justify-between gap-2">
                          <span class="text-[10px] font-bold text-slate-400 w-20">Wholesale:</span>
                          <span class="font-semibold text-slate-500 text-[11px]">${{ parseFloat(row.current_wholesale_price || 0).toFixed(2) }}</span>
                          <input
                            type="number"
                            step="0.01"
                            min="0"
                            v-model.number="row.wholesale_price"
                            placeholder="New Wholesale"
                            class="w-24 px-2 py-1 text-xs bg-white dark:bg-zinc-900 border border-slate-200 dark:border-[#2E2E2E] rounded-xl font-bold text-right outline-none"
                          />
                        </div>

                        <!-- Purchase Price -->
                        <div class="flex items-center justify-between gap-2">
                          <span class="text-[10px] font-bold text-slate-400 w-24">Purchase Price:</span>
                          <span class="font-semibold text-slate-500 text-[11px]">${{ parseFloat(row.current_cost_price || 0).toFixed(2) }}</span>
                          <input
                            type="number"
                            step="0.01"
                            min="0"
                            v-model.number="row.cost_price"
                            placeholder="New Purchase Price"
                            class="w-28 px-2 py-1 text-xs bg-white dark:bg-zinc-900 border border-slate-200 dark:border-[#2E2E2E] rounded-xl font-bold text-right outline-none"
                          />
                        </div>
                      </div>
                    </td>

                    <!-- Tax Adjustment Column -->
                    <td class="px-3 py-3.5 align-top">
                      <div class="space-y-2">
                        <div class="text-[10px] font-bold text-slate-400">Current Tax:</div>
                        <span class="px-2 py-0.5 text-[10px] font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 rounded-full inline-block">
                          {{ row.current_tax_name }} ({{ row.current_tax_rate }}%)
                        </span>

                        <div class="pt-1">
                          <label class="block text-[9px] font-bold text-slate-400 mb-0.5">Assign New Tax</label>
                          <CustomFloatingSelect
                            v-model="row.tax_id"
                            :options="taxGroupOptions"
                            buttonClass="!py-1 !text-xs !bg-white dark:!bg-zinc-900"
                          />
                        </div>
                      </div>
                    </td>

                    <!-- Row Action / Remove Column -->
                    <td class="px-3 py-3.5 text-center align-middle">
                      <button
                        type="button"
                        @click="removeMatrixRow(row)"
                        title="Remove item from matrix"
                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/60 rounded-lg transition-colors cursor-pointer"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </td>
                  </tr>

                  <!-- Nested Child Variation Rows (If Expanded) -->
                  <template v-if="row.expanded && row.variations">
                    <tr
                      v-for="v in row.variations.filter(varItem => !varItem.removed)"
                      :key="'v-' + v.id"
                      class="hover:bg-slate-50/60 dark:hover:bg-zinc-900/40 border-l-4 border-indigo-500"
                    >
                      <!-- Variation Identifier -->
                      <td class="px-4 py-2.5 pl-8 align-top">
                        <div class="font-bold text-slate-700 dark:text-slate-200 text-xs">
                          ↳ {{ v.name || v.sku }}
                        </div>
                        <div class="text-[10px] text-slate-400 font-mono">
                          SKU: {{ v.sku }}
                        </div>
                      </td>

                      <!-- Variation Stock Adjustment -->
                      <td class="px-3 py-2.5 align-top">
                        <div class="space-y-1.5">
                          <div class="flex items-center justify-between text-[10px]">
                            <span class="text-slate-500 font-semibold">Stock:</span>
                            <span class="font-bold text-slate-700 dark:text-slate-200">{{ v.current_stock }}</span>
                          </div>
                          <div class="grid grid-cols-2 gap-1">
                            <CustomFloatingSelect
                              v-model="v.stock_type"
                              :options="stockTypeOptions"
                              buttonClass="!py-0.5 !text-[10px] !bg-white dark:!bg-zinc-900"
                            />
                            <input
                              type="number"
                              min="0"
                              v-model.number="v.quantity_adjusted"
                              placeholder="Qty"
                              class="px-1.5 py-0.5 text-[10px] bg-white dark:bg-zinc-900 border border-slate-200 dark:border-[#2E2E2E] rounded-lg font-bold outline-none text-center"
                            />
                          </div>
                        </div>
                      </td>

                      <!-- Variation Multi-Pricing -->
                      <td class="px-3 py-2.5 align-top">
                        <div class="space-y-1 text-[11px]">
                          <div class="flex items-center justify-between gap-1">
                            <span class="text-[9px] text-slate-400">Sale:</span>
                            <input
                              type="number"
                              step="0.01"
                              v-model.number="v.selling_price"
                              placeholder="Sale"
                              class="w-20 px-1 py-0.5 text-[10px] bg-white dark:bg-zinc-900 border border-slate-200 dark:border-[#2E2E2E] rounded-lg text-right font-bold outline-none"
                            />
                          </div>
                          <div class="flex items-center justify-between gap-1">
                            <span class="text-[9px] text-slate-400">Purchase:</span>
                            <input
                              type="number"
                              step="0.01"
                              v-model.number="v.cost_price"
                              placeholder="Purchase Price"
                              class="w-24 px-1 py-0.5 text-[10px] bg-white dark:bg-zinc-900 border border-slate-200 dark:border-[#2E2E2E] rounded-lg text-right font-bold outline-none"
                            />
                          </div>
                        </div>
                      </td>

                      <!-- Variation Tax Assignment -->
                      <td class="px-3 py-2.5 align-top">
                        <CustomFloatingSelect
                          v-model="v.tax_id"
                          :options="taxGroupOptions"
                          buttonClass="!py-0.5 !text-[10px] !bg-white dark:!bg-zinc-900"
                        />
                      </td>

                      <!-- Variation Remove Action -->
                      <td class="px-3 py-2.5 text-center align-middle">
                        <button
                          type="button"
                          @click="removeVariationRow(v)"
                          title="Remove variation"
                          class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/60 rounded-md transition-colors cursor-pointer"
                        >
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                          </svg>
                        </button>
                      </td>
                    </tr>
                  </template>

                </template>

                <!-- EMPTY STATE PLACEHOLDER -->
                <tr v-if="!isMatrixFilterActive">
                  <td colspan="5" class="px-6 py-16 text-center">
                    <div class="flex flex-col items-center justify-center space-y-3">
                      <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-zinc-900 border border-indigo-100 dark:border-zinc-800 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xl shadow-xs">
                        🔍
                      </div>
                      <div>
                        <p class="text-sm font-extrabold text-slate-800 dark:text-slate-200">
                          Search or apply filters above to load items for adjustment.
                        </p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 max-w-md mx-auto">
                          Focus Smart Search to select from Top 50 autocomplete, or select Categories, Brands, or Tags above.
                        </p>
                      </div>
                    </div>
                  </td>
                </tr>
                <tr v-else-if="filteredMatrixRows.length === 0">
                  <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-medium">
                    No matching catalog products found for the applied filter parameters.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- BOTTOM ACTION FOOTER -->
        <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-[#2E2E2E]">
          <router-link
            to="/inventory/adjustments"
            class="px-5 py-2.5 border border-slate-200 dark:border-[#2E2E2E] hover:bg-slate-50 dark:hover:bg-[#2D2D2D]/80 text-slate-500 dark:text-slate-400 font-bold rounded-xl text-xs uppercase tracking-wider cursor-pointer"
          >
            Cancel
          </router-link>
          
          <button
            type="button"
            @click="submitUnifiedMatrix"
            :disabled="savingMatrix || activeInputCount === 0"
            class="px-7 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-md transition-all cursor-pointer flex items-center gap-2"
          >
            <span v-if="savingMatrix" class="w-3.5 h-3.5 rounded-full border-2 border-white border-t-transparent animate-spin"></span>
            <span>{{ savingMatrix ? 'Executing Matrix Updates...' : 'Save Unified Adjustments (' + activeInputCount + ')' }}</span>
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import CustomFloatingSelect from '@/components/common/CustomFloatingSelect.vue';

const router = useRouter();
const { showToast } = useToast();
const { confirm } = useConfirm();

// State References
const warehouses = ref([]);
const categories = ref([]);
const brands = ref([]);
const tags = ref([]);
const taxes = ref([]);
const rawProducts = ref([]);
const savingMatrix = ref(false);

// Filter States
const searchQuery = ref('');
const selectedCategories = ref([]);
const selectedBrands = ref([]);
const selectedTags = ref([]);
const selectedWarehouse = ref(null);

// Autocomplete & Search Controls
const showSearchDropdown = ref(false);
const highlightedIndex = ref(0);
const searchInputRef = ref(null);
const suggestionRefs = ref({});

// Explicit Selection & Removal Tracking
const explicitlySelectedIds = ref(new Set());
const removedRowIds = ref(new Set());

// Audit Info States
const auditReason = ref('Audit / Recount');
const auditNotes = ref('');

const stockTypeOptions = [
  { label: '+ Add / Increase', value: 'increase' },
  { label: '- Reduce / Decrease', value: 'decrease' },
  { label: '= Set Fixed Recount', value: 'recount' }
];

const reasonOptions = [
  { label: 'Audit / Recount', value: 'Audit / Recount' },
  { label: 'Discrepancy Correction', value: 'Discrepancy Correction' },
  { label: 'Damaged / Expired Stock', value: 'Damaged / Expired Stock' },
  { label: 'Bulk Pricing Update', value: 'Bulk Pricing Update' },
  { label: 'Tax Re-assignment', value: 'Tax Re-assignment' },
];

// Computed Options
const warehouseOptions = computed(() => warehouses.value.map(w => ({ label: w.name, value: w.id })));
const categoryOptions = computed(() => categories.value.map(c => ({ label: c.name, value: c.id })));
const brandOptions = computed(() => brands.value.map(b => ({ label: b.name, value: b.id })));
const tagOptions = computed(() => tags.value.map(t => ({ label: t.name, value: t.id })));
const taxGroupOptions = computed(() => [
  { label: 'Unassigned', value: '' },
  ...taxes.value.map(t => ({ label: `${t.name} (${parseFloat(t.value)}%)`, value: t.id }))
]);

// Reactive Matrix Data Rows
const matrixRows = ref([]);

// TOP 50 SEARCH SUGGESTIONS
const searchSuggestions = computed(() => {
  const q = (searchQuery.value || '').trim().toLowerCase();
  const list = [];

  rawProducts.value.forEach(p => {
    // Check parent product match
    const pNameMatch = (p.name || '').toLowerCase().includes(q);
    const pSkuMatch = (p.sku || '').toLowerCase().includes(q);
    const pBarcodeMatch = (p.barcode || '').toLowerCase().includes(q);

    if (!q || pNameMatch || pSkuMatch || pBarcodeMatch) {
      list.push({
        unique_id: 'p-' + p.id,
        product_id: p.id,
        variation_id: null,
        is_variation: false,
        name: p.name,
        sku: p.sku,
        barcode: p.barcode,
        stock_quantity: p.stock_quantity ?? 0,
        selling_price: p.selling_price ?? 0,
      });
    }

    // Check variations
    if (p.variations && p.variations.length > 0) {
      p.variations.forEach(v => {
        const vNameMatch = (v.name || '').toLowerCase().includes(q);
        const vSkuMatch = (v.sku || '').toLowerCase().includes(q);
        if (!q || vNameMatch || vSkuMatch || pNameMatch) {
          list.push({
            unique_id: 'v-' + v.id,
            product_id: p.id,
            variation_id: v.id,
            is_variation: true,
            name: v.name ? `${p.name} (${v.name})` : `${p.name} - ${v.sku}`,
            sku: v.sku,
            barcode: p.barcode,
            stock_quantity: v.stock_quantity ?? 0,
            selling_price: v.retail_price ?? p.selling_price ?? 0,
          });
        }
      });
    }
  });

  return list.slice(0, 50);
});

// Search Events
const onSearchFocus = () => {
  showSearchDropdown.value = true;
  highlightedIndex.value = 0;
};

const onSearchInput = () => {
  showSearchDropdown.value = true;
  highlightedIndex.value = 0;
};

const clearSearch = () => {
  searchQuery.value = '';
  showSearchDropdown.value = false;
};

const navigateSuggestions = (direction) => {
  if (!showSearchDropdown.value || searchSuggestions.value.length === 0) return;
  const count = searchSuggestions.value.length;
  highlightedIndex.value = (highlightedIndex.value + direction + count) % count;
  
  nextTick(() => {
    const el = suggestionRefs.value[highlightedIndex.value];
    if (el) el.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
  });
};

const selectHighlightedSuggestion = () => {
  if (searchSuggestions.value.length > 0 && searchSuggestions.value[highlightedIndex.value]) {
    selectSuggestionItem(searchSuggestions.value[highlightedIndex.value]);
  }
};

const selectSuggestionItem = (item) => {
  explicitlySelectedIds.value.add(item.product_id);
  removedRowIds.value.delete(item.product_id);

  // If a variation was selected, ensure variation is expanded and un-removed
  const row = matrixRows.value.find(r => r.id === item.product_id);
  if (row) {
    row.expanded = true;
    if (item.variation_id && row.variations) {
      const v = row.variations.find(varItem => varItem.id === item.variation_id);
      if (v) v.removed = false;
    }
  }

  showSearchDropdown.value = false;
  searchQuery.value = '';
  showToast(`Added "${item.name}" to adjustment matrix table.`, 'info');
};

// Filter Active Check
const isMatrixFilterActive = computed(() => {
  return searchQuery.value.trim() !== '' ||
    explicitlySelectedIds.value.size > 0 ||
    selectedCategories.value.length > 0 ||
    selectedBrands.value.length > 0 ||
    selectedTags.value.length > 0;
});

// Matrix Rows Filtering
const filteredMatrixRows = computed(() => {
  if (!isMatrixFilterActive.value) {
    return [];
  }

  return matrixRows.value.filter(row => {
    // If user explicitly removed this item row, exclude it
    if (removedRowIds.value.has(row.id)) return false;

    // Explicit selection via search autocomplete
    if (explicitlySelectedIds.value.has(row.id)) return true;

    // Category filter
    if (selectedCategories.value.length > 0) {
      if (!selectedCategories.value.includes(row.category_id)) return false;
    }

    // Brand filter
    if (selectedBrands.value.length > 0) {
      if (!selectedBrands.value.includes(row.brand_id)) return false;
    }

    // Tags filter
    if (selectedTags.value.length > 0) {
      const rowTagIds = (row.tags || []).map(t => t.id);
      const hasMatchingTag = selectedTags.value.some(tagId => rowTagIds.includes(tagId));
      if (!hasMatchingTag) return false;
    }

    // Direct search match if typed
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase().trim();
      const matchName = (row.name || '').toLowerCase().includes(q);
      const matchSku = (row.sku || '').toLowerCase().includes(q);
      const matchBarcode = (row.barcode || '').toLowerCase().includes(q);
      if (!matchName && !matchSku && !matchBarcode) return false;
    }

    return true;
  });
});

// Matrix Row Removal Actions
const removeMatrixRow = (row) => {
  removedRowIds.value.add(row.id);
  explicitlySelectedIds.value.delete(row.id);
  showToast(`Removed "${row.name}" from adjustment matrix.`, 'info');
};

const removeVariationRow = (v) => {
  v.removed = true;
  showToast(`Removed variation "${v.sku}" from matrix.`, 'info');
};

// Input Calculations & Summaries
const activeInputCount = computed(() => {
  let count = 0;
  matrixRows.value.forEach(row => {
    if (!removedRowIds.value.has(row.id)) {
      if (isRowModified(row)) count++;
      if (row.variations) {
        row.variations.forEach(v => {
          if (!v.removed && isRowModified(v)) count++;
        });
      }
    }
  });
  return count;
});

const isRowModified = (r) => {
  return (r.quantity_adjusted !== '' && r.quantity_adjusted !== null && r.quantity_adjusted !== undefined) ||
    (r.min_stock_level !== '' && r.min_stock_level !== null && r.min_stock_level !== undefined) ||
    (r.selling_price !== '' && r.selling_price !== null && r.selling_price !== undefined) ||
    (r.wholesale_price !== '' && r.wholesale_price !== null && r.wholesale_price !== undefined) ||
    (r.cost_price !== '' && r.cost_price !== null && r.cost_price !== undefined) ||
    (r.tax_id !== '' && r.tax_id !== null && r.tax_id !== undefined);
};

const getResultingStock = (row) => {
  const current = (row.current_stock || 0);
  const qty = parseFloat(row.quantity_adjusted || 0);
  if (!qty) return current;
  if (row.stock_type === 'increase') return current + qty;
  if (row.stock_type === 'decrease') return Math.max(0, current - qty);
  if (row.stock_type === 'recount') return qty;
  return current;
};

const clearAllMatrixInputs = () => {
  explicitlySelectedIds.value.clear();
  removedRowIds.value.clear();
  selectedCategories.value = [];
  selectedBrands.value = [];
  selectedTags.value = [];
  searchQuery.value = '';

  matrixRows.value.forEach(row => {
    row.stock_type = 'increase';
    row.quantity_adjusted = '';
    row.min_stock_level = '';
    row.selling_price = '';
    row.wholesale_price = '';
    row.cost_price = '';
    row.tax_id = '';

    if (row.variations) {
      row.variations.forEach(v => {
        v.removed = false;
        v.stock_type = 'increase';
        v.quantity_adjusted = '';
        v.min_stock_level = '';
        v.selling_price = '';
        v.cost_price = '';
        v.tax_id = '';
      });
    }
  });
};

const loadRefData = async () => {
  try {
    const [whRes, catRes, brRes, tagRes, taxRes, prodRes] = await Promise.all([
      axios.get('/api/warehouses'),
      axios.get('/api/categories'),
      axios.get('/api/brands'),
      axios.get('/api/tags'),
      axios.get('/api/taxes'),
      axios.get('/api/inventory-adjustments/preview-products', { params: { target_type: 'all' } })
    ]);

    warehouses.value = whRes.data.data || whRes.data || [];
    categories.value = catRes.data || [];
    brands.value = brRes.data || [];
    tags.value = tagRes.data || [];
    taxes.value = taxRes.data || [];

    const def = warehouses.value.find(w => w.is_default) || warehouses.value[0];
    if (def) selectedWarehouse.value = def.id;

    rawProducts.value = prodRes.data.products || [];
    initializeMatrixRows();
  } catch (err) {
    showToast('Failed to load catalog reference data.', 'error');
  }
};

const initializeMatrixRows = () => {
  matrixRows.value = rawProducts.value.map(p => {
    const childVariations = (p.variations || []).map(v => ({
      id: v.id,
      product_id: p.id,
      name: v.name || `${p.name} - ${v.sku}`,
      sku: v.sku,
      current_stock: v.stock_quantity ?? 0,
      stock_type: 'increase',
      quantity_adjusted: '',
      min_stock_level: '',
      selling_price: '',
      cost_price: '',
      tax_id: '',
      removed: false,
    }));

    return {
      id: p.id,
      product_id: p.id,
      name: p.name,
      sku: p.sku,
      barcode: p.barcode,
      category_id: p.category_id,
      brand_id: p.brand_id,
      category_name: p.category_name,
      brand_name: p.brand_name,
      tags: p.tags || [],
      current_stock: p.stock_quantity ?? 0,
      current_min_stock: p.min_stock_level ?? 0,
      current_price: p.current_price ?? p.selling_price ?? 0,
      current_wholesale_price: p.wholesale_price ?? 0,
      current_cost_price: p.cost_price ?? 0,
      current_tax_rate: p.current_tax_rate ?? 0,
      current_tax_name: p.current_tax_name ?? 'None',
      expanded: false,
      has_variations: childVariations.length > 0,
      variations: childVariations,
      stock_type: 'increase',
      quantity_adjusted: '',
      min_stock_level: '',
      selling_price: '',
      wholesale_price: '',
      cost_price: '',
      tax_id: '',
    };
  });
};

const submitUnifiedMatrix = async () => {
  if (!selectedWarehouse.value) {
    showToast('Please select a target warehouse location.', 'error');
    return;
  }

  const payloadItems = [];

  filteredMatrixRows.value.forEach(row => {
    if (isRowModified(row)) {
      payloadItems.push({
        product_id: row.id,
        product_variation_id: null,
        warehouse_id: selectedWarehouse.value,
        adjustment_type: row.stock_type,
        quantity_adjusted: row.quantity_adjusted !== '' ? row.quantity_adjusted : null,
        min_stock_level: row.min_stock_level !== '' ? row.min_stock_level : null,
        selling_price: row.selling_price !== '' ? row.selling_price : null,
        wholesale_price: row.wholesale_price !== '' ? row.wholesale_price : null,
        cost_price: row.cost_price !== '' ? row.cost_price : null,
        tax_id: row.tax_id !== '' ? row.tax_id : null,
      });
    }

    if (row.variations) {
      row.variations.forEach(v => {
        if (!v.removed && isRowModified(v)) {
          payloadItems.push({
            product_id: row.id,
            product_variation_id: v.id,
            warehouse_id: selectedWarehouse.value,
            adjustment_type: v.stock_type,
            quantity_adjusted: v.quantity_adjusted !== '' ? v.quantity_adjusted : null,
            min_stock_level: v.min_stock_level !== '' ? v.min_stock_level : null,
            selling_price: v.selling_price !== '' ? v.selling_price : null,
            wholesale_price: null,
            cost_price: v.cost_price !== '' ? v.cost_price : null,
            tax_id: v.tax_id !== '' ? v.tax_id : null,
          });
        }
      });
    }
  });

  if (payloadItems.length === 0) {
    showToast('No adjustment inputs detected. Please enter at least one field to save.', 'warning');
    return;
  }

  const confirmed = await confirm({
    title: 'Confirm Unified Matrix Adjustments',
    message: `Save unified matrix updates for ${payloadItems.length} items/variations? This will update stock levels, prices, tax rates, min alert limits, and post double-entry accounting ledger entries.`,
    confirmText: 'Execute Adjustments',
    cancelText: 'Cancel',
    type: 'warning'
  });
  if (!confirmed) return;

  savingMatrix.value = true;
  try {
    const res = await axios.post('/api/inventory-adjustments/batch-matrix', {
      reason: auditReason.value,
      notes: auditNotes.value,
      items: payloadItems
    });

    showToast(res.data.message || 'Unified adjustments processed successfully.', 'success');
    router.push('/inventory/adjustments');
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to execute unified matrix adjustments.';
    showToast(msg, 'error');
  } finally {
    savingMatrix.value = false;
  }
};

onMounted(() => {
  loadRefData();
});
</script>
