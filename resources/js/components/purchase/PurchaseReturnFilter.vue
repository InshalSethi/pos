<template>
  <teleport to="body">
    <!-- Backdrop Overlay -->
    <transition
      enter-active-class="transition-opacity ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen"
        class="fixed inset-0 bg-slate-900/40 dark:bg-black/60 backdrop-blur-xs z-[9990]"
        @click="close"
      ></div>
    </transition>

    <!-- Slide-over Filter Drawer Panel -->
    <transition
      enter-active-class="transform transition ease-out duration-300"
      enter-from-class="translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transform transition ease-in duration-200"
      leave-from-class="translate-x-0"
      leave-to-class="translate-x-full"
    >
      <div
        v-if="isOpen"
        class="fixed inset-y-0 right-0 z-[9995] w-full max-w-md bg-white dark:bg-zinc-900 shadow-2xl flex flex-col border-l border-slate-200 dark:border-zinc-800"
        @click.stop
      >
        <!-- Header -->
        <div class="p-6 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between">
          <div class="flex items-center space-x-2">
            <h2 class="text-lg font-bold text-slate-900 dark:text-zinc-100">Filter</h2>
            <span v-if="activeFilterCount > 0" class="text-xs font-extrabold bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 px-2 py-0.5 rounded-full">
              {{ activeFilterCount }}
            </span>
          </div>
          <button
            @click="close"
            class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Active Filter Counter Banner -->
        <div v-if="activeFilterCount > 0" class="px-6 py-2.5 bg-slate-50 dark:bg-zinc-800/40 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between text-xs">
          <span class="text-slate-600 dark:text-zinc-300 font-medium">
            <strong class="text-slate-900 dark:text-zinc-100 font-bold">{{ activeFilterCount }}</strong> active filter{{ activeFilterCount > 1 ? 's' : '' }} applied
          </span>
          <button
            @click="resetFilters"
            class="text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-100 font-semibold cursor-pointer underline hover:no-underline"
          >
            Reset All
          </button>
        </div>

        <!-- Drawer Form Body -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6 custom-scrollbar">

          <!-- 1. Product / Line Item (Top 20) Search Dropdown -->
          <div class="space-y-1.5 relative" @click.stop>
            <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
              Product / Line Item (Top 20)
            </label>
            <div class="relative">
              <button
                type="button"
                @click="togglePopover('product')"
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 flex items-center justify-between focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-100 dark:focus:border-zinc-600 dark:focus:ring-zinc-800 transition-all cursor-pointer"
                :class="{ 'border-slate-300 dark:border-zinc-600 ring-2 ring-slate-100 dark:ring-zinc-800 bg-white dark:bg-zinc-800': activePopover === 'product' }"
              >
                <span class="truncate pr-2" :class="{ 'text-slate-400 dark:text-zinc-500': !productSummaryLabel }">
                  {{ productSummaryLabel || 'Select a Top Returned Product' }}
                </span>
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
              </button>

              <!-- Floating Top Products Popover -->
              <div
                v-if="activePopover === 'product'"
                class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-xl py-1 max-h-72 flex flex-col animate-fade-in"
              >
                <div class="px-3 py-1.5 border-b border-slate-100 dark:border-zinc-800 text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider flex justify-between items-center shrink-0">
                  <span>Top Returned Products</span>
                  <button @click="clearProductFilter" class="text-slate-600 dark:text-zinc-300 hover:underline cursor-pointer">Clear</button>
                </div>

                <!-- Search Bar Header -->
                <div class="p-2 border-b border-slate-100 dark:border-zinc-800 shrink-0">
                  <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-slate-400">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input
                      v-model="productSearchQuery"
                      type="text"
                      placeholder="Search product by name or SKU..."
                      class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-100 dark:focus:border-zinc-600 transition-all placeholder:text-slate-400"
                    />
                  </div>
                </div>

                <div class="overflow-y-auto max-h-48 custom-scrollbar">
                  <div v-if="loadingTopProducts" class="py-4 text-center text-slate-400 text-xs italic">
                    Loading top products...
                  </div>
                  <div v-else-if="filteredProducts.length === 0" class="py-4 text-center text-slate-400 text-xs italic">
                    No matching products found.
                  </div>
                  <template v-else>
                    <button
                      v-for="p in filteredProducts"
                      :key="p.id"
                      type="button"
                      @click="selectProduct(p)"
                      class="w-full px-3 py-2 text-left hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center justify-between text-xs border-b border-slate-50 dark:border-zinc-800/40 cursor-pointer"
                      :class="{ 'bg-slate-100/70 dark:bg-zinc-800 font-bold text-slate-900 dark:text-zinc-100': localFilters.product_id === p.id }"
                    >
                      <span class="truncate pr-2 text-slate-800 dark:text-zinc-200">{{ p.name }}</span>
                      <span v-if="p.code || p.sku" class="text-[10px] text-slate-400 dark:text-zinc-500 font-mono shrink-0">{{ p.code || p.sku }}</span>
                    </button>
                  </template>
                </div>
              </div>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-zinc-500">
              Select from top items to isolate returns containing that product.
            </p>
          </div>

          <!-- 2. Date Range Picker Field with Calendar & Presets -->
          <div class="space-y-1.5 relative" @click.stop>
            <div class="flex items-center justify-between">
              <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
                Return Date Range
              </label>
              <!-- Quick Action Preset Pills -->
              <div class="flex items-center space-x-1">
                <button
                  type="button"
                  @click="applyPreset('today')"
                  class="px-2 py-0.5 text-[10px] font-semibold rounded bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors cursor-pointer"
                >
                  Today
                </button>
                <button
                  type="button"
                  @click="applyPreset('this_month')"
                  class="px-2 py-0.5 text-[10px] font-semibold rounded bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors cursor-pointer"
                >
                  This Month
                </button>
              </div>
            </div>

            <!-- Date Range Trigger Input Button -->
            <div class="relative">
              <button
                type="button"
                @click="togglePopover('date')"
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 flex items-center justify-between focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-100 dark:focus:border-zinc-600 dark:focus:ring-zinc-800 transition-all cursor-pointer"
                :class="{ 'border-slate-300 dark:border-zinc-600 ring-2 ring-slate-100 dark:ring-zinc-800 bg-white dark:bg-zinc-800': activePopover === 'date' }"
              >
                <span class="truncate" :class="{ 'text-slate-400 dark:text-zinc-500': !formattedDateRangeDisplay }">
                  {{ formattedDateRangeDisplay || 'Select Date Range (Start Date - End Date)' }}
                </span>
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
              </button>

              <!-- Unified Date Range Picker Popover Dropdown -->
              <div
                v-if="activePopover === 'date'"
                class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-2xl shadow-2xl p-4 w-full sm:w-80 animate-fade-in space-y-3"
              >
                <!-- Quick Preset Options Grid -->
                <div class="grid grid-cols-4 gap-1 border-b border-slate-100 dark:border-zinc-800 pb-2.5">
                  <button
                    v-for="p in datePresets"
                    :key="p.id"
                    type="button"
                    @click="applyPreset(p.id)"
                    class="py-1 px-1.5 text-[10px] font-semibold rounded text-center transition-colors cursor-pointer"
                    :class="activePresetId === p.id 
                      ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-bold' 
                      : 'bg-slate-50 dark:bg-zinc-800/80 text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-700'"
                  >
                    {{ p.label }}
                  </button>
                </div>

                <!-- Calendar Header Month/Year Selector -->
                <div class="flex items-center justify-between pt-1">
                  <div class="flex items-center space-x-1">
                    <button
                      type="button"
                      @click="toggleSubPicker('month')"
                      class="text-xs font-bold text-slate-800 dark:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 px-2 py-1 rounded transition-colors flex items-center space-x-1 cursor-pointer"
                    >
                      <span>{{ monthNames[calendarMonth] }}</span>
                      <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <button
                      type="button"
                      @click="toggleSubPicker('year')"
                      class="text-xs font-bold text-slate-800 dark:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 px-2 py-1 rounded transition-colors flex items-center space-x-1 cursor-pointer"
                    >
                      <span>{{ calendarYear }}</span>
                      <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                  </div>

                  <div class="flex items-center space-x-0.5">
                    <button @click="prevMonth" type="button" class="p-1 rounded hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-500 cursor-pointer">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button @click="nextMonth" type="button" class="p-1 rounded hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-500 cursor-pointer">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                  </div>
                </div>

                <!-- Sub-picker overlay for Months -->
                <div v-if="subPickerOpen === 'month'" class="grid grid-cols-3 gap-1 bg-slate-50 dark:bg-zinc-800 p-2 rounded-xl border border-slate-200 dark:border-zinc-700">
                  <button
                    v-for="(mName, mIdx) in monthNames"
                    :key="mIdx"
                    type="button"
                    @click="selectMonth(mIdx)"
                    class="py-1 text-xs font-semibold rounded text-center transition-colors cursor-pointer"
                    :class="calendarMonth === mIdx ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-bold' : 'hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-300'"
                  >
                    {{ shortMonthNames[mIdx] }}
                  </button>
                </div>

                <!-- Sub-picker overlay for Years -->
                <div v-if="subPickerOpen === 'year'" class="grid grid-cols-4 gap-1 bg-slate-50 dark:bg-zinc-800 p-2 rounded-xl border border-slate-200 dark:border-zinc-700 max-h-36 overflow-y-auto custom-scrollbar">
                  <button
                    v-for="y in availableYears"
                    :key="y"
                    type="button"
                    @click="selectYear(y)"
                    class="py-1 text-xs font-semibold rounded text-center transition-colors cursor-pointer"
                    :class="calendarYear === y ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-bold' : 'hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-300'"
                  >
                    {{ y }}
                  </button>
                </div>

                <!-- Days Grid -->
                <div v-if="!subPickerOpen" class="space-y-1">
                  <div class="grid grid-cols-7 text-center text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">
                    <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                  </div>
                  <div class="grid grid-cols-7 gap-0.5">
                    <button
                      v-for="(day, idx) in calendarDays"
                      :key="idx"
                      type="button"
                      :disabled="!day.inMonth"
                      @click="handleDayClick(day.dateStr)"
                      @mouseenter="hoverDate = day.dateStr"
                      class="h-7 w-full rounded text-xs font-medium transition-all flex items-center justify-center cursor-pointer"
                      :class="getDayClass(day)"
                    >
                      {{ day.dayNum }}
                    </button>
                  </div>
                </div>

                <!-- Footer Info & Actions -->
                <div class="pt-2 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between">
                  <div class="text-[11px] font-semibold text-slate-500 dark:text-zinc-400">
                    <span v-if="localFilters.date_from && !localFilters.date_to">Select end date</span>
                    <span v-else-if="localFilters.date_from && localFilters.date_to">Range selected</span>
                    <span v-else>Click to select start date</span>
                  </div>
                  <div class="flex items-center space-x-2">
                    <button
                      type="button"
                      @click="clearDateRange"
                      class="text-xs text-rose-600 dark:text-rose-400 hover:underline font-bold cursor-pointer"
                    >
                      Clear
                    </button>
                    <button
                      type="button"
                      @click="closeAllPopovers"
                      class="px-2.5 py-1 text-xs bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 rounded font-bold hover:bg-black transition-colors cursor-pointer"
                    >
                      Done
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 3. Original PO / Bill Number Search Input -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
              Original PO / Bill Number
            </label>
            <div class="relative">
              <input
                type="text"
                v-model="localFilters.po_number"
                placeholder="Search PO / Bill # (e.g. PO-0001)..."
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-100 dark:focus:border-zinc-600 dark:focus:ring-zinc-800 transition-all placeholder:text-slate-400 dark:placeholder:text-zinc-500"
              />
              <button
                v-if="localFilters.po_number"
                @click="localFilters.po_number = ''"
                class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 font-bold text-xs"
              >
                ✕
              </button>
            </div>
          </div>

          <!-- 4. Supplier Name (Multi-Select) -->
          <div class="space-y-1.5 relative" @click.stop>
            <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
              Supplier Name (Multi-Select)
            </label>
            <div class="relative">
              <button
                type="button"
                @click="togglePopover('supplier')"
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 flex items-center justify-between focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-100 dark:focus:border-zinc-600 dark:focus:ring-zinc-800 transition-all cursor-pointer"
                :class="{ 'border-slate-300 dark:border-zinc-600 ring-2 ring-slate-100 dark:ring-zinc-800 bg-white dark:bg-zinc-800': activePopover === 'supplier' }"
              >
                <span class="truncate pr-2" :class="{ 'text-slate-400 dark:text-zinc-500': localFilters.supplier_ids.length === 0 }">
                  {{ supplierSummaryLabel }}
                </span>
                <span v-if="localFilters.supplier_ids.length > 0" class="text-[10px] font-bold bg-slate-200 text-slate-800 dark:bg-zinc-700 dark:text-zinc-200 px-1.5 py-0.2 rounded-full shrink-0">
                  {{ localFilters.supplier_ids.length }}
                </span>
              </button>

              <!-- Floating Multi-Select Popover -->
              <div
                v-if="activePopover === 'supplier'"
                class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-xl py-1 max-h-72 flex flex-col animate-fade-in"
              >
                <div class="px-3 py-1.5 border-b border-slate-100 dark:border-zinc-800 flex justify-between items-center text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase shrink-0">
                  <span>Select Suppliers (Top 20)</span>
                  <button @click="localFilters.supplier_ids = []" class="text-slate-600 dark:text-zinc-300 hover:underline cursor-pointer">Clear</button>
                </div>

                <!-- Dropdown Search Input Header -->
                <div class="p-2 border-b border-slate-100 dark:border-zinc-800 shrink-0">
                  <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-slate-400">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input
                      v-model="supplierSearchQuery"
                      type="text"
                      placeholder="Search supplier by name, phone..."
                      class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-100 dark:focus:border-zinc-600 transition-all placeholder:text-slate-400"
                    />
                  </div>
                </div>

                <div class="overflow-y-auto max-h-48 custom-scrollbar">
                  <div v-if="filteredSuppliers.length === 0" class="py-4 text-center text-slate-400 text-xs italic">
                    No suppliers found matching "{{ supplierSearchQuery }}".
                  </div>
                  <label
                    v-for="s in filteredSuppliers"
                    :key="s.id"
                    class="px-3 py-2 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center space-x-2.5 cursor-pointer text-xs select-none border-b border-slate-50 dark:border-zinc-800/40"
                  >
                    <input
                      type="checkbox"
                      :value="String(s.id)"
                      v-model="localFilters.supplier_ids"
                      class="rounded border-slate-300 text-slate-900 focus:ring-slate-300 cursor-pointer w-4 h-4"
                    />
                    <span class="font-medium text-slate-800 dark:text-zinc-200">
                      {{ s.name }} {{ s.company_name ? `(${s.company_name})` : '' }}
                    </span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- 5. Return Warehouse / Location (Multi-Select) -->
          <div class="space-y-1.5 relative" @click.stop>
            <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
              Return Warehouse / Location (Multi-Select)
            </label>
            <div class="relative">
              <button
                type="button"
                @click="togglePopover('warehouse')"
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 flex items-center justify-between focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-100 dark:focus:border-zinc-600 dark:focus:ring-zinc-800 transition-all cursor-pointer"
                :class="{ 'border-slate-300 dark:border-zinc-600 ring-2 ring-slate-100 dark:ring-zinc-800 bg-white dark:bg-zinc-800': activePopover === 'warehouse' }"
              >
                <span class="truncate pr-2" :class="{ 'text-slate-400 dark:text-zinc-500': localFilters.warehouse_ids.length === 0 }">
                  {{ warehouseSummaryLabel }}
                </span>
                <span v-if="localFilters.warehouse_ids.length > 0" class="text-[10px] font-bold bg-slate-200 text-slate-800 dark:bg-zinc-700 dark:text-zinc-200 px-1.5 py-0.2 rounded-full shrink-0">
                  {{ localFilters.warehouse_ids.length }}
                </span>
              </button>

              <!-- Floating Multi-Select Popover -->
              <div
                v-if="activePopover === 'warehouse'"
                class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-xl py-1 max-h-60 overflow-y-auto custom-scrollbar animate-fade-in"
              >
                <div class="px-3 py-1.5 border-b border-slate-100 dark:border-zinc-800 flex justify-between items-center text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase">
                  <span>Select Warehouses</span>
                  <button @click="localFilters.warehouse_ids = []" class="text-slate-600 dark:text-zinc-300 hover:underline cursor-pointer">Clear</button>
                </div>
                <div v-if="warehouses.length === 0" class="py-4 text-center text-slate-400 text-xs italic">
                  No warehouses found.
                </div>
                <label
                  v-for="w in warehouses"
                  :key="w.id"
                  class="px-3 py-2 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center space-x-2.5 cursor-pointer text-xs select-none border-b border-slate-50 dark:border-zinc-800/40"
                >
                  <input
                    type="checkbox"
                    :value="String(w.id)"
                    v-model="localFilters.warehouse_ids"
                    class="rounded border-slate-300 text-slate-900 focus:ring-slate-300 cursor-pointer w-4 h-4"
                  />
                  <span class="font-medium text-slate-800 dark:text-zinc-200">
                    {{ w.name }}
                  </span>
                </label>
              </div>
            </div>
          </div>

          <!-- 6. Return Status (Multi-Select) -->
          <div class="space-y-1.5 relative" @click.stop>
            <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
              Return Status (Multi-Select)
            </label>
            <div class="relative">
              <button
                type="button"
                @click="togglePopover('status')"
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 flex items-center justify-between focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-100 dark:focus:border-zinc-600 dark:focus:ring-zinc-800 transition-all cursor-pointer"
                :class="{ 'border-slate-300 dark:border-zinc-600 ring-2 ring-slate-100 dark:ring-zinc-800 bg-white dark:bg-zinc-800': activePopover === 'status' }"
              >
                <span class="truncate pr-2" :class="{ 'text-slate-400 dark:text-zinc-500': localFilters.statuses.length === 0 }">
                  {{ statusSummaryLabel }}
                </span>
                <span v-if="localFilters.statuses.length > 0" class="text-[10px] font-bold bg-slate-200 text-slate-800 dark:bg-zinc-700 dark:text-zinc-200 px-1.5 py-0.2 rounded-full shrink-0">
                  {{ localFilters.statuses.length }}
                </span>
              </button>

              <!-- Floating Multi-Select Popover -->
              <div
                v-if="activePopover === 'status'"
                class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-xl py-1 max-h-60 overflow-y-auto custom-scrollbar animate-fade-in"
              >
                <div class="px-3 py-1.5 border-b border-slate-100 dark:border-zinc-800 flex justify-between items-center text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase">
                  <span>Select Statuses</span>
                  <button @click="localFilters.statuses = []" class="text-slate-600 dark:text-zinc-300 hover:underline cursor-pointer">Clear</button>
                </div>
                <label
                  v-for="st in availableStatuses"
                  :key="st.id"
                  class="px-3 py-2 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center space-x-2.5 cursor-pointer text-xs select-none border-b border-slate-50 dark:border-zinc-800/40"
                >
                  <input
                    type="checkbox"
                    :value="st.id"
                    v-model="localFilters.statuses"
                    class="rounded border-slate-300 text-slate-900 focus:ring-slate-300 cursor-pointer w-4 h-4"
                  />
                  <span class="font-semibold" :class="st.colorClass">
                    {{ st.label }}
                  </span>
                </label>
              </div>
            </div>
          </div>

          <!-- 7. Return Reason (Multi-Select) -->
          <div class="space-y-1.5 relative" @click.stop>
            <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
              Return Reason (Multi-Select)
            </label>
            <div class="relative">
              <button
                type="button"
                @click="togglePopover('reason')"
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 flex items-center justify-between focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-100 dark:focus:border-zinc-600 dark:focus:ring-zinc-800 transition-all cursor-pointer"
                :class="{ 'border-slate-300 dark:border-zinc-600 ring-2 ring-slate-100 dark:ring-zinc-800 bg-white dark:bg-zinc-800': activePopover === 'reason' }"
              >
                <span class="truncate pr-2" :class="{ 'text-slate-400 dark:text-zinc-500': localFilters.return_reasons.length === 0 }">
                  {{ reasonSummaryLabel }}
                </span>
                <span v-if="localFilters.return_reasons.length > 0" class="text-[10px] font-bold bg-slate-200 text-slate-800 dark:bg-zinc-700 dark:text-zinc-200 px-1.5 py-0.2 rounded-full shrink-0">
                  {{ localFilters.return_reasons.length }}
                </span>
              </button>

              <!-- Floating Multi-Select Popover -->
              <div
                v-if="activePopover === 'reason'"
                class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-xl py-1 max-h-60 overflow-y-auto custom-scrollbar animate-fade-in"
              >
                <div class="px-3 py-1.5 border-b border-slate-100 dark:border-zinc-800 flex justify-between items-center text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase">
                  <span>Select Return Reasons</span>
                  <button @click="localFilters.return_reasons = []" class="text-slate-600 dark:text-zinc-300 hover:underline cursor-pointer">Clear</button>
                </div>
                <label
                  v-for="r in availableReasons"
                  :key="r"
                  class="px-3 py-2 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center space-x-2.5 cursor-pointer text-xs select-none border-b border-slate-50 dark:border-zinc-800/40"
                >
                  <input
                    type="checkbox"
                    :value="r"
                    v-model="localFilters.return_reasons"
                    class="rounded border-slate-300 text-slate-900 focus:ring-slate-300 cursor-pointer w-4 h-4"
                  />
                  <span class="font-medium text-slate-800 dark:text-zinc-200">
                    {{ r }}
                  </span>
                </label>
              </div>
            </div>
          </div>

        </div>

        <!-- Footer Actions -->
        <div class="p-6 border-t border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50 flex items-center justify-between space-x-3">
          <button
            type="button"
            @click="resetFilters"
            class="px-4 py-2.5 text-xs font-semibold text-slate-600 dark:text-zinc-400 hover:text-slate-800 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
          >
            Reset Filters
          </button>
          <div class="flex items-center space-x-2">
            <button
              type="button"
              @click="close"
              class="px-4 py-2.5 text-xs font-semibold text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="button"
              @click="applyFilters"
              class="px-5 py-2.5 text-xs font-bold text-white bg-slate-900 hover:bg-black active:scale-95 rounded-lg shadow-sm transition-all flex items-center cursor-pointer"
            >
              Apply Filters
            </button>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  filters: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['update:isOpen', 'apply', 'reset']);

const activePopover = ref(null);
const subPickerOpen = ref(null);

const togglePopover = (popoverName) => {
  if (activePopover.value === popoverName) {
    activePopover.value = null;
  } else {
    activePopover.value = popoverName;
  }
  subPickerOpen.value = null;
};

const toggleSubPicker = (type) => {
  if (subPickerOpen.value === type) {
    subPickerOpen.value = null;
  } else {
    subPickerOpen.value = type;
  }
};

const closeAllPopovers = () => {
  activePopover.value = null;
  subPickerOpen.value = null;
};

const close = () => {
  closeAllPopovers();
  emit('update:isOpen', false);
};

// Local state for multi-select & popover filters
const localFilters = ref({
  product_id: '',
  product_name: '',
  product_search: '',
  po_number: '',
  supplier_ids: [],
  warehouse_ids: [],
  statuses: [],
  return_reasons: [],
  date_from: '',
  date_to: ''
});

// Lookups Data
const topProducts = ref([]);
const suppliers = ref([]);
const warehouses = ref([]);
const loadingTopProducts = ref(false);
const loadingDropdowns = ref(false);

const supplierSearchQuery = ref('');
const productSearchQuery = ref('');

const filteredProducts = computed(() => {
  const list = Array.isArray(topProducts.value) ? topProducts.value : [];
  if (!productSearchQuery.value.trim()) return list;
  const q = productSearchQuery.value.toLowerCase().trim();
  return list.filter(p => 
    (p.name && p.name.toLowerCase().includes(q)) ||
    (p.code && p.code.toLowerCase().includes(q)) ||
    (p.sku && p.sku.toLowerCase().includes(q))
  );
});

const filteredSuppliers = computed(() => {
  const list = Array.isArray(suppliers.value) ? suppliers.value : [];
  const selectedIds = (localFilters.value.supplier_ids || []).map(id => String(id));

  if (!supplierSearchQuery.value.trim()) {
    const selectedList = list.filter(s => selectedIds.includes(String(s.id)));
    const unselectedList = list.filter(s => !selectedIds.includes(String(s.id))).slice(0, Math.max(0, 20 - selectedList.length));
    return [...selectedList, ...unselectedList];
  }

  const q = supplierSearchQuery.value.toLowerCase().trim();
  const matched = list.filter(s => {
    const name = (s.name || '').toLowerCase();
    const company = (s.company_name || '').toLowerCase();
    const phone = (s.phone || '').toLowerCase();
    return name.includes(q) || company.includes(q) || phone.includes(q);
  });
  return matched.slice(0, 20);
});

// Date Range Picker State & Helpers
const calendarYear = ref(new Date().getFullYear());
const calendarMonth = ref(new Date().getMonth());
const hoverDate = ref(null);

const monthNames = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December'
];

const shortMonthNames = [
  'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
  'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
];

const availableStatuses = [
  { id: 'draft', label: 'Draft', colorClass: 'text-slate-600 dark:text-zinc-400' },
  { id: 'pending', label: 'Pending Approval', colorClass: 'text-orange-600 dark:text-orange-400' },
  { id: 'approved', label: 'Approved', colorClass: 'text-blue-600 dark:text-blue-400' },
  { id: 'completed', label: 'Completed', colorClass: 'text-emerald-600 dark:text-emerald-400' },
  { id: 'cancelled', label: 'Cancelled', colorClass: 'text-rose-600 dark:text-rose-400' },
];

const availableReasons = [
  'Damaged',
  'Defective',
  'Wrong Item Received',
  'Overstocked',
  'Other'
];

const availableYears = computed(() => {
  const currentY = new Date().getFullYear();
  const startY = Math.min(currentY - 15, 2015);
  const endY = Math.max(currentY + 15, 2040);
  const years = [];
  for (let y = startY; y <= endY; y++) {
    years.push(y);
  }
  return years;
});

const datePresets = [
  { id: 'today', label: 'Today' },
  { id: 'yesterday', label: 'Yesterday' },
  { id: 'this_week', label: 'This Week' },
  { id: 'this_month', label: 'This Month' },
  { id: 'last_month', label: 'Last Month' },
  { id: 'this_year', label: 'This Year' },
  { id: 'last_year', label: 'Last Year' },
  { id: 'clear', label: 'Clear' }
];

const activePresetId = ref('');

const formatDisplayDate = (dateStr) => {
  if (!dateStr) return '';
  const [year, month, day] = dateStr.split('-');
  if (!year || !month || !day) return dateStr;
  const monthIdx = parseInt(month, 10) - 1;
  const monthAbbr = shortMonthNames[monthIdx] || month;
  return `${monthAbbr} ${parseInt(day, 10)}, ${year}`;
};

const formattedDateRangeDisplay = computed(() => {
  const from = localFilters.value.date_from;
  const to = localFilters.value.date_to;

  if (from && to) {
    return `${formatDisplayDate(from)} - ${formatDisplayDate(to)}`;
  } else if (from) {
    return `From ${formatDisplayDate(from)}`;
  } else if (to) {
    return `Until ${formatDisplayDate(to)}`;
  }
  return '';
});

const calendarDays = computed(() => {
  const year = calendarYear.value;
  const month = calendarMonth.value;
  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  const startingDayOfWeek = firstDay.getDay();
  const totalDays = lastDay.getDate();

  const days = [];
  const prevMonthLastDay = new Date(year, month, 0).getDate();
  for (let i = startingDayOfWeek - 1; i >= 0; i--) {
    days.push({
      dayNum: prevMonthLastDay - i,
      inMonth: false,
      dateStr: ''
    });
  }

  for (let d = 1; d <= totalDays; d++) {
    const mm = String(month + 1).padStart(2, '0');
    const dd = String(d).padStart(2, '0');
    const dateStr = `${year}-${mm}-${dd}`;
    days.push({
      dayNum: d,
      inMonth: true,
      dateStr
    });
  }

  const remaining = 42 - days.length;
  for (let i = 1; i <= remaining; i++) {
    days.push({
      dayNum: i,
      inMonth: false,
      dateStr: ''
    });
  }

  return days;
});

const handleDayClick = (dateStr) => {
  if (!dateStr) return;
  activePresetId.value = '';
  const from = localFilters.value.date_from;
  const to = localFilters.value.date_to;

  if (!from || (from && to)) {
    localFilters.value.date_from = dateStr;
    localFilters.value.date_to = '';
  } else if (from && !to) {
    if (dateStr < from) {
      localFilters.value.date_from = dateStr;
      localFilters.value.date_to = from;
    } else {
      localFilters.value.date_to = dateStr;
    }
  }
};

const getDayClass = (day) => {
  if (!day.inMonth) return 'text-slate-300 dark:text-zinc-700 pointer-events-none';

  const dateStr = day.dateStr;
  const from = localFilters.value.date_from;
  const to = localFilters.value.date_to;

  const isStart = dateStr === from;
  const isEnd = dateStr === to;
  const isSelected = isStart || isEnd;

  let isInRange = false;
  if (from && to && dateStr > from && dateStr < to) {
    isInRange = true;
  } else if (from && !to && hoverDate.value && dateStr > from && dateStr <= hoverDate.value) {
    isInRange = true;
  }

  if (isSelected) {
    return 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-bold shadow-xs';
  } else if (isInRange) {
    return 'bg-slate-100 text-slate-900 dark:bg-zinc-800 dark:text-zinc-100 rounded-none';
  }
  return 'text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800';
};

const prevMonth = () => {
  if (calendarMonth.value === 0) {
    calendarMonth.value = 11;
    calendarYear.value--;
  } else {
    calendarMonth.value--;
  }
};

const nextMonth = () => {
  if (calendarMonth.value === 11) {
    calendarMonth.value = 0;
    calendarYear.value++;
  } else {
    calendarMonth.value++;
  }
};

const selectMonth = (mIdx) => {
  calendarMonth.value = mIdx;
  subPickerOpen.value = null;
};

const selectYear = (y) => {
  calendarYear.value = y;
  subPickerOpen.value = null;
};

const applyPreset = (presetId) => {
  activePresetId.value = presetId;
  const now = new Date();
  const year = now.getFullYear();
  const month = now.getMonth();

  const formatDateStr = (d) => {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
  };

  if (presetId === 'today') {
    const todayStr = formatDateStr(now);
    localFilters.value.date_from = todayStr;
    localFilters.value.date_to = todayStr;
  } else if (presetId === 'yesterday') {
    const yest = new Date(now);
    yest.setDate(now.getDate() - 1);
    const yestStr = formatDateStr(yest);
    localFilters.value.date_from = yestStr;
    localFilters.value.date_to = yestStr;
  } else if (presetId === 'this_week') {
    const dayOfWeek = now.getDay();
    const firstDayOfWeek = new Date(now);
    firstDayOfWeek.setDate(now.getDate() - dayOfWeek);
    localFilters.value.date_from = formatDateStr(firstDayOfWeek);
    localFilters.value.date_to = formatDateStr(now);
  } else if (presetId === 'this_month') {
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    localFilters.value.date_from = formatDateStr(firstDay);
    localFilters.value.date_to = formatDateStr(lastDay);
  } else if (presetId === 'last_month') {
    const firstDay = new Date(year, month - 1, 1);
    const lastDay = new Date(year, month, 0);
    localFilters.value.date_from = formatDateStr(firstDay);
    localFilters.value.date_to = formatDateStr(lastDay);
  } else if (presetId === 'this_year') {
    const firstDay = new Date(year, 0, 1);
    const lastDay = new Date(year, 11, 31);
    localFilters.value.date_from = formatDateStr(firstDay);
    localFilters.value.date_to = formatDateStr(lastDay);
  } else if (presetId === 'last_year') {
    const firstDay = new Date(year - 1, 0, 1);
    const lastDay = new Date(year - 1, 11, 31);
    localFilters.value.date_from = formatDateStr(firstDay);
    localFilters.value.date_to = formatDateStr(lastDay);
  } else if (presetId === 'clear') {
    clearDateRange();
  }
};

const clearDateRange = () => {
  localFilters.value.date_from = '';
  localFilters.value.date_to = '';
  activePresetId.value = '';
};

// Summary Labels
const productSummaryLabel = computed(() => {
  if (localFilters.value.product_search) return localFilters.value.product_search;
  if (localFilters.value.product_id) {
    const p = topProducts.value.find(item => String(item.id) === String(localFilters.value.product_id));
    return p ? p.name : `Product #${localFilters.value.product_id}`;
  }
  return '';
});

const supplierSummaryLabel = computed(() => {
  const ids = localFilters.value.supplier_ids || [];
  if (ids.length === 0) return 'All Suppliers';
  if (ids.length === 1) {
    const s = suppliers.value.find(item => String(item.id) === String(ids[0]));
    return s ? s.name : `Supplier #${ids[0]}`;
  }
  const firstS = suppliers.value.find(item => String(item.id) === String(ids[0]));
  const firstName = firstS ? firstS.name : `Supplier #${ids[0]}`;
  return `${firstName} (+${ids.length - 1} more)`;
});

const warehouseSummaryLabel = computed(() => {
  const ids = localFilters.value.warehouse_ids || [];
  if (ids.length === 0) return 'All Return Warehouses';
  if (ids.length === 1) {
    const wh = warehouses.value.find(w => String(w.id) === String(ids[0]));
    return wh ? wh.name : `Warehouse #${ids[0]}`;
  }
  const firstWh = warehouses.value.find(w => String(w.id) === String(ids[0]));
  const firstName = firstWh ? firstWh.name : `Warehouse #${ids[0]}`;
  return `${firstName} (+${ids.length - 1} more)`;
});

const statusSummaryLabel = computed(() => {
  const list = localFilters.value.statuses || [];
  if (list.length === 0) return 'All Statuses';
  if (list.length === 1) {
    const st = availableStatuses.find(item => item.id === list[0]);
    return st ? st.label : list[0];
  }
  const firstSt = availableStatuses.find(item => item.id === list[0]);
  const firstName = firstSt ? firstSt.label : list[0];
  return `${firstName} (+${list.length - 1} more)`;
});

const reasonSummaryLabel = computed(() => {
  const list = localFilters.value.return_reasons || [];
  if (list.length === 0) return 'All Return Reasons';
  if (list.length === 1) return list[0];
  return `${list[0]} (+${list.length - 1} more)`;
});

const selectProduct = (prod) => {
  localFilters.value.product_id = prod.id;
  localFilters.value.product_name = prod.name;
  localFilters.value.product_search = prod.name;
  productSearchQuery.value = '';
  closeAllPopovers();
};

const clearProductFilter = () => {
  localFilters.value.product_id = '';
  localFilters.value.product_name = '';
  localFilters.value.product_search = '';
  productSearchQuery.value = '';
};

const activeFilterCount = computed(() => {
  let count = 0;
  if (localFilters.value.product_id || localFilters.value.product_search) count++;
  if (localFilters.value.po_number) count++;
  if (localFilters.value.supplier_ids?.length > 0) count++;
  if (localFilters.value.warehouse_ids?.length > 0) count++;
  if (localFilters.value.statuses?.length > 0) count++;
  if (localFilters.value.return_reasons?.length > 0) count++;
  if (localFilters.value.date_from || localFilters.value.date_to) count++;
  return count;
});

const fetchTopProducts = async () => {
  loadingTopProducts.value = true;
  try {
    const res = await axios.get('/api/products?per_page=20&sort_by=created_at&sort_order=desc&can_be_purchased=true');
    let list = [];
    if (Array.isArray(res.data)) list = res.data;
    else if (res.data && Array.isArray(res.data.data)) list = res.data.data;
    topProducts.value = list;
  } catch (e) {
    console.error('Error fetching top products:', e);
  } finally {
    loadingTopProducts.value = false;
  }
};

const extractArray = (resData) => {
  if (Array.isArray(resData)) return resData;
  if (resData && Array.isArray(resData.data)) return resData.data;
  return [];
};

const fetchDropdownLookups = async () => {
  loadingDropdowns.value = true;
  try {
    const [suppRes, whRes] = await Promise.all([
      axios.get('/api/suppliers').catch(() => ({ data: [] })),
      axios.get('/api/warehouses').catch(() => ({ data: [] }))
    ]);
    suppliers.value = extractArray(suppRes.data);
    warehouses.value = extractArray(whRes.data);
  } catch (e) {
    console.error('Error loading dropdown lookups:', e);
  } finally {
    loadingDropdowns.value = false;
  }
};

const applyFilters = () => {
  closeAllPopovers();
  emit('apply', { ...localFilters.value });
  emit('update:isOpen', false);
};

const resetFilters = () => {
  localFilters.value = {
    product_id: '',
    product_name: '',
    product_search: '',
    po_number: '',
    supplier_ids: [],
    warehouse_ids: [],
    statuses: [],
    return_reasons: [],
    date_from: '',
    date_to: ''
  };
  productSearchQuery.value = '';
  supplierSearchQuery.value = '';
  activePresetId.value = '';
  closeAllPopovers();
  emit('reset');
};

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    localFilters.value = {
      product_id: props.filters.product_id || '',
      product_name: props.filters.product_name || '',
      product_search: props.filters.product_search || '',
      po_number: props.filters.po_number || props.filters.original_po || '',
      supplier_ids: Array.isArray(props.filters.supplier_ids) ? [...props.filters.supplier_ids] : [],
      warehouse_ids: Array.isArray(props.filters.warehouse_ids) ? [...props.filters.warehouse_ids] : [],
      statuses: Array.isArray(props.filters.statuses) ? [...props.filters.statuses] : [],
      return_reasons: Array.isArray(props.filters.return_reasons) ? [...props.filters.return_reasons] : [],
      date_from: props.filters.date_from || '',
      date_to: props.filters.date_to || ''
    };
    if (topProducts.value.length === 0) fetchTopProducts();
    if (suppliers.value.length === 0) fetchDropdownLookups();
  } else {
    closeAllPopovers();
  }
});

watch(() => props.filters, () => {
  if (props.isOpen) {
    localFilters.value = {
      product_id: props.filters.product_id || '',
      product_name: props.filters.product_name || '',
      product_search: props.filters.product_search || '',
      po_number: props.filters.po_number || props.filters.original_po || '',
      supplier_ids: Array.isArray(props.filters.supplier_ids) ? [...props.filters.supplier_ids] : [],
      warehouse_ids: Array.isArray(props.filters.warehouse_ids) ? [...props.filters.warehouse_ids] : [],
      statuses: Array.isArray(props.filters.statuses) ? [...props.filters.statuses] : [],
      return_reasons: Array.isArray(props.filters.return_reasons) ? [...props.filters.return_reasons] : [],
      date_from: props.filters.date_from || '',
      date_to: props.filters.date_to || ''
    };
  }
}, { deep: true });

onMounted(() => {
  document.addEventListener('click', closeAllPopovers);
  fetchTopProducts();
  fetchDropdownLookups();
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
  height: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.4);
  border-radius: 4px;
}
.animate-fade-in {
  animation: fadeIn 0.15s ease-out;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.97);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>
