<template>
  <teleport to="body">
    <!-- Backdrop -->
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
        @click="close"
        class="fixed inset-0 z-[9999] bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200"
        style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);"
      ></div>
    </transition>

    <!-- Slide-over Drawer -->
    <transition
      enter-active-class="transition transform ease-out duration-300"
      enter-from-class="translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transition transform ease-in duration-200"
      leave-from-class="translate-x-0"
      leave-to-class="translate-x-full"
    >
      <div
        v-if="isOpen"
        class="fixed inset-y-0 right-0 z-[9999] w-full max-w-md bg-white dark:bg-zinc-900 shadow-2xl border-l border-slate-200 dark:border-zinc-800 flex flex-col justify-between overflow-hidden"
      >
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between bg-slate-50/50 dark:bg-zinc-900/50">
          <div>
            <h2 class="text-base font-bold text-slate-900 dark:text-zinc-100 tracking-tight">Advanced Filter</h2>
          </div>
          <button
            @click="close"
            class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 px-2 py-1 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors focus:outline-none cursor-pointer font-bold text-base leading-none"
          >
            ✕
          </button>
        </div>

        <!-- Body Form -->
        <div class="flex-1 overflow-y-auto p-6 space-y-5 custom-scrollbar" @click="closeAllPopovers">
          <!-- Active Filters Count Badge -->
          <div v-if="activeFilterCount > 0" class="flex items-center justify-between px-3 py-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/40 rounded-lg text-xs">
            <span class="text-blue-700 dark:text-blue-300 font-medium">
              <strong class="font-bold">{{ activeFilterCount }}</strong> active filter{{ activeFilterCount > 1 ? 's' : '' }} applied
            </span>
            <button
              @click="resetFilters"
              class="text-blue-600 dark:text-blue-400 hover:underline font-semibold cursor-pointer"
            >
              Reset All
            </button>
          </div>

          <!-- 1. Product / Line Item (Top 20) -->
          <div class="space-y-1.5 relative" @click.stop>
            <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
              Product / Line Item (Top 20)
            </label>
            <div class="relative">
              <button
                type="button"
                @click="togglePopover('product')"
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer"
              >
                <span class="truncate pr-2" :class="{ 'text-slate-400 dark:text-zinc-500': !selectedProductLabel }">
                  {{ selectedProductLabel || 'Select a Top Selling Product' }}
                </span>
                <div class="flex items-center space-x-1">
                  <button
                    v-if="localFilters.product_id || localFilters.product_search"
                    @click.stop="clearProduct"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 px-1 font-bold text-xs"
                    title="Clear selected product"
                  >
                    ✕
                  </button>
                </div>
              </button>

              <!-- Floating Product Popover Dropdown -->
              <div
                v-if="activePopover === 'product'"
                class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-xl py-1 max-h-64 overflow-y-auto custom-scrollbar animate-fade-in"
              >
                <div class="px-3 py-1.5 border-b border-slate-100 dark:border-zinc-800 text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider flex justify-between items-center">
                  <span>Top 20 Frequently Sold Items</span>
                  <span v-if="topProducts.length > 0" class="text-blue-600 dark:text-blue-400">{{ topProducts.length }} available</span>
                </div>

                <div v-if="loadingTopProducts" class="py-6 text-center text-slate-400 text-xs">
                  <div class="animate-spin rounded-full h-5 w-5 border-2 border-slate-300 border-t-blue-600 mx-auto mb-1"></div>
                  Loading top products...
                </div>
                <div v-else-if="topProducts.length === 0" class="py-4 text-center text-slate-400 text-xs italic">
                  No products available.
                </div>
                <div v-else>
                  <button
                    type="button"
                    @click="selectProduct(null)"
                    class="w-full px-3 py-2 text-left text-xs font-semibold hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center justify-between text-slate-500 dark:text-zinc-400"
                    :class="{ 'bg-blue-50/50 dark:bg-blue-900/30 text-blue-600': !localFilters.product_id && !localFilters.product_search }"
                  >
                    <span>All Products / No Filter</span>
                  </button>
                  <button
                    v-for="p in topProducts"
                    :key="p.id"
                    type="button"
                    @click="selectProduct(p)"
                    class="w-full px-3 py-2 text-left text-xs hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center justify-between space-x-2 border-t border-slate-50 dark:border-zinc-800/60 cursor-pointer"
                    :class="{ 'bg-blue-50/80 dark:bg-blue-900/40 font-semibold text-blue-700 dark:text-blue-300': String(localFilters.product_id) === String(p.id) }"
                  >
                    <div class="truncate">
                      <div class="font-medium text-slate-800 dark:text-zinc-100 truncate">{{ p.name }}</div>
                      <div class="text-[10px] text-slate-400 dark:text-zinc-500">SKU: {{ p.sku || 'N/A' }}</div>
                    </div>
                    <span v-if="p.selling_price" class="text-[11px] font-bold text-slate-600 dark:text-zinc-300 shrink-0">
                      ${{ parseFloat(p.selling_price).toFixed(2) }}
                    </span>
                  </button>
                </div>
              </div>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-zinc-500">Select from the top 20 items to isolate invoices containing that product.</p>
          </div>

          <!-- 2. Date Range Section: Single Unified Date Range Picker Popover -->
          <div class="space-y-1.5 relative" @click.stop>
            <div class="flex items-center justify-between">
              <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
                Date Range
              </label>
              <div class="flex gap-1.5">
                <button
                  type="button"
                  @click="setQuickDate('today')"
                  class="px-2 py-0.5 text-[10px] font-semibold bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-600 dark:text-zinc-300 rounded transition-colors cursor-pointer"
                >
                  Today
                </button>
                <button
                  type="button"
                  @click="setQuickDate('this_month')"
                  class="px-2 py-0.5 text-[10px] font-semibold bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-600 dark:text-zinc-300 rounded transition-colors cursor-pointer"
                >
                  This Month
                </button>
              </div>
            </div>

            <div class="relative">
              <button
                type="button"
                @click="togglePopover('date_range')"
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer"
              >
                <span class="truncate pr-2" :class="{ 'text-slate-400 dark:text-zinc-500': !dateRangeSummaryLabel }">
                  {{ dateRangeSummaryLabel || 'Select Date Range (Start Date - End Date)' }}
                </span>
                <div class="flex items-center space-x-1">
                  <button
                    v-if="localFilters.date_from || localFilters.date_to"
                    @click.stop="clearDateRange"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 px-1 font-bold text-xs cursor-pointer"
                    title="Clear date range"
                  >
                    ✕
                  </button>
                </div>
              </button>

              <!-- Floating Single Calendar Date Range Popover -->
              <div
                v-if="activePopover === 'date_range'"
                class="absolute left-0 right-0 top-full mt-1.5 z-50 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-2xl p-2.5 animate-fade-in text-xs w-full select-none"
              >
                <!-- Quick Preset Pills inside Calendar -->
                <div class="flex flex-wrap gap-1 pb-1.5 mb-1.5 border-b border-slate-100 dark:border-zinc-800">
                  <button
                    type="button"
                    @click="setQuickDate('today')"
                    class="px-1.5 py-0.5 text-[10px] font-medium bg-slate-100 dark:bg-zinc-800 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 rounded transition-colors cursor-pointer"
                  >
                    Today
                  </button>
                  <button
                    type="button"
                    @click="setQuickDate('yesterday')"
                    class="px-1.5 py-0.5 text-[10px] font-medium bg-slate-100 dark:bg-zinc-800 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 rounded transition-colors cursor-pointer"
                  >
                    Yesterday
                  </button>
                  <button
                    type="button"
                    @click="setQuickDate('this_week')"
                    class="px-1.5 py-0.5 text-[10px] font-medium bg-slate-100 dark:bg-zinc-800 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 rounded transition-colors cursor-pointer"
                  >
                    This Week
                  </button>
                  <button
                    type="button"
                    @click="setQuickDate('this_month')"
                    class="px-1.5 py-0.5 text-[10px] font-medium bg-slate-100 dark:bg-zinc-800 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 rounded transition-colors cursor-pointer"
                  >
                    This Month
                  </button>
                  <button
                    type="button"
                    @click="setQuickDate('last_month')"
                    class="px-1.5 py-0.5 text-[10px] font-medium bg-slate-100 dark:bg-zinc-800 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 rounded transition-colors cursor-pointer"
                  >
                    Last Month
                  </button>
                  <button
                    type="button"
                    @click="setQuickDate('this_year')"
                    class="px-1.5 py-0.5 text-[10px] font-medium bg-slate-100 dark:bg-zinc-800 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 rounded transition-colors cursor-pointer"
                  >
                    This Year
                  </button>
                  <button
                    type="button"
                    @click="setQuickDate('last_year')"
                    class="px-1.5 py-0.5 text-[10px] font-medium bg-slate-100 dark:bg-zinc-800 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 rounded transition-colors cursor-pointer"
                  >
                    Last Year
                  </button>
                  <button
                    type="button"
                    @click="clearDateRange"
                    class="px-1.5 py-0.5 text-[10px] font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded transition-colors ml-auto cursor-pointer"
                  >
                    Clear
                  </button>
                </div>

                <!-- Calendar Header: Prev / Interactive Month & Year Buttons / Next -->
                <div class="flex items-center justify-between pb-1 mb-0.5 px-0.5 relative">
                  <button
                    type="button"
                    @click="prevMonth"
                    class="w-6 h-6 flex items-center justify-center text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-100 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded font-bold cursor-pointer transition-colors"
                    title="Previous Month"
                  >
                    &lt;
                  </button>

                  <div class="flex items-center space-x-1 relative">
                    <!-- Month Sub-Picker Toggle Button -->
                    <button
                      type="button"
                      @click.stop="toggleSubPicker('month')"
                      class="px-2 py-0.5 font-bold text-slate-800 dark:text-zinc-100 text-xs hover:bg-slate-100 dark:hover:bg-zinc-800 rounded transition-colors flex items-center gap-1 cursor-pointer"
                      :class="{ 'bg-slate-100 dark:bg-zinc-800 text-blue-600 dark:text-blue-400': subPickerOpen === 'month' }"
                    >
                      <span>{{ monthNames[calendarMonth] }}</span>
                      <span class="text-[8px] text-slate-400">▲</span>
                    </button>

                    <!-- Year Sub-Picker Toggle Button -->
                    <button
                      type="button"
                      @click.stop="toggleSubPicker('year')"
                      class="px-2 py-0.5 font-bold text-slate-800 dark:text-zinc-100 text-xs hover:bg-slate-100 dark:hover:bg-zinc-800 rounded transition-colors flex items-center gap-1 cursor-pointer"
                      :class="{ 'bg-slate-100 dark:bg-zinc-800 text-blue-600 dark:text-blue-400': subPickerOpen === 'year' }"
                    >
                      <span>{{ calendarYear }}</span>
                      <span class="text-[8px] text-slate-400">▲</span>
                    </button>
                  </div>

                  <button
                    type="button"
                    @click="nextMonth"
                    class="w-6 h-6 flex items-center justify-center text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-100 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded font-bold cursor-pointer transition-colors"
                    title="Next Month"
                  >
                    &gt;
                  </button>

                  <!-- Upward Floating Month Grid Menu Popup -->
                  <div
                    v-if="subPickerOpen === 'month'"
                    class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 z-60 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-2xl p-2 w-56 grid grid-cols-3 gap-1 animate-fade-in"
                  >
                    <button
                      v-for="(mName, mIdx) in shortMonthNames"
                      :key="mIdx"
                      type="button"
                      @click.stop="selectMonth(mIdx)"
                      class="py-1 text-xs font-semibold rounded text-center transition-colors cursor-pointer"
                      :class="calendarMonth === mIdx ? 'bg-blue-600 text-white font-bold shadow-xs' : 'hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-700 dark:text-zinc-200'"
                    >
                      {{ mName }}
                    </button>
                  </div>

                  <!-- Upward Floating Year Grid Menu Popup -->
                  <div
                    v-if="subPickerOpen === 'year'"
                    class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 z-60 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-2xl p-2 w-52 max-h-44 overflow-y-auto custom-scrollbar grid grid-cols-3 gap-1 animate-fade-in"
                  >
                    <button
                      v-for="y in availableYears"
                      :key="y"
                      type="button"
                      @click.stop="selectYear(y)"
                      class="py-1 text-xs font-semibold rounded text-center transition-colors cursor-pointer"
                      :class="calendarYear === y ? 'bg-blue-600 text-white font-bold shadow-xs' : 'hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-700 dark:text-zinc-200'"
                    >
                      {{ y }}
                    </button>
                  </div>
                </div>

                <!-- Day Headers (Sun - Sat) -->
                <div class="grid grid-cols-7 text-center text-[10px] font-bold text-slate-400 dark:text-zinc-500 mb-0.5">
                  <span>Su</span>
                  <span>Mo</span>
                  <span>Tu</span>
                  <span>We</span>
                  <span>Th</span>
                  <span>Fr</span>
                  <span>Sa</span>
                </div>

                <!-- Calendar Days Grid -->
                <div class="grid grid-cols-7 gap-y-0.5 text-center" @mouseleave="hoverDate = null">
                  <div
                    v-for="(day, idx) in calendarDays"
                    :key="idx"
                    class="relative py-0.5 flex items-center justify-center cursor-pointer"
                    @click="handleDateClick(day.dateStr)"
                    @mouseenter="hoverDate = day.dateStr"
                  >
                    <!-- Highlight range background overlay -->
                    <div
                      v-if="isDateInRange(day.dateStr)"
                      class="absolute inset-y-0.5 inset-x-0 bg-blue-50 dark:bg-blue-900/40"
                      :class="{
                        'rounded-l-md': isDateStart(day.dateStr),
                        'rounded-r-md': isDateEnd(day.dateStr) || isDateHoverEnd(day.dateStr)
                      }"
                    ></div>

                    <!-- Day Button Number -->
                    <button
                      type="button"
                      class="relative z-10 w-6 h-6 text-xs flex items-center justify-center rounded-md font-medium transition-all cursor-pointer"
                      :class="{
                        'opacity-30': !day.isCurrentMonth,
                        'bg-blue-600 text-white font-bold shadow-xs': isDateStart(day.dateStr) || isDateEnd(day.dateStr),
                        'text-blue-700 dark:text-blue-300 font-bold': isDateInRange(day.dateStr) && !isDateStart(day.dateStr) && !isDateEnd(day.dateStr),
                        'hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-700 dark:text-zinc-200': !isDateStart(day.dateStr) && !isDateEnd(day.dateStr) && !isDateInRange(day.dateStr),
                        'ring-1 ring-blue-500': isToday(day.dateStr) && !isDateStart(day.dateStr) && !isDateEnd(day.dateStr)
                      }"
                    >
                      {{ day.dayNum }}
                    </button>
                  </div>
                </div>

                <!-- Selected Range Footer Info -->
                <div class="mt-1.5 pt-1.5 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between text-[10px] text-slate-500 dark:text-zinc-400">
                  <div>
                    <span v-if="localFilters.date_from && !localFilters.date_to" class="text-blue-600 dark:text-blue-400 font-medium">
                      Select end date...
                    </span>
                    <span v-else-if="localFilters.date_from && localFilters.date_to" class="font-medium text-slate-700 dark:text-zinc-200">
                      Range selected
                    </span>
                    <span v-else class="italic text-slate-400">
                      Click start date
                    </span>
                  </div>
                  <button
                    type="button"
                    @click="togglePopover('date_range')"
                    class="px-2 py-0.5 font-bold text-white bg-blue-600 hover:bg-blue-700 rounded transition-colors text-[10px] cursor-pointer"
                  >
                    Done
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- 3. Multi-Select Floating Dropdown: Invoice Status -->
          <div class="space-y-1.5 relative" @click.stop>
            <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
              Invoice Status (Multi-Select)
            </label>
            <div class="relative">
              <button
                type="button"
                @click="togglePopover('status')"
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer"
              >
                <span class="truncate pr-2" :class="{ 'text-slate-400 dark:text-zinc-500': localFilters.statuses.length === 0 }">
                  {{ statusSummaryLabel }}
                </span>
                <span v-if="localFilters.statuses.length > 0" class="text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 px-1.5 py-0.2 rounded-full shrink-0">
                  {{ localFilters.statuses.length }}
                </span>
              </button>

              <!-- Floating Multi-Select Popover -->
              <div
                v-if="activePopover === 'status'"
                class="absolute left-0 right-0 top-full mt-1 z-50 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-xl py-1 max-h-60 overflow-y-auto custom-scrollbar animate-fade-in"
              >
                <div class="px-3 py-1.5 border-b border-slate-100 dark:border-zinc-800 flex justify-between items-center text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase">
                  <span>Select Invoice Statuses</span>
                  <button @click="localFilters.statuses = []" class="text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">Clear</button>
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
                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-4 h-4"
                  />
                  <span class="font-semibold" :class="st.colorClass">
                    {{ st.label }}
                  </span>
                </label>
              </div>
            </div>
          </div>

          <!-- 4. Multi-Select Floating Dropdown: Warehouse / Shop Location -->
          <div class="space-y-1.5 relative" @click.stop>
            <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
              Warehouse / Shop Location (Multi-Select)
            </label>
            <div class="relative">
              <button
                type="button"
                @click="togglePopover('warehouse')"
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer"
              >
                <span class="truncate pr-2" :class="{ 'text-slate-400 dark:text-zinc-500': localFilters.warehouse_ids.length === 0 }">
                  {{ warehouseSummaryLabel }}
                </span>
                <span v-if="localFilters.warehouse_ids.length > 0" class="text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 px-1.5 py-0.2 rounded-full shrink-0">
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
                  <button @click="localFilters.warehouse_ids = []" class="text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">Clear</button>
                </div>
                <div v-if="warehouses.length === 0" class="py-4 text-center text-slate-400 text-xs italic">
                  No warehouses found.
                </div>
                <label
                  v-for="wh in warehouses"
                  :key="wh.id"
                  class="px-3 py-2 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center space-x-2.5 cursor-pointer text-xs select-none border-b border-slate-50 dark:border-zinc-800/40"
                >
                  <input
                    type="checkbox"
                    :value="wh.id"
                    v-model="localFilters.warehouse_ids"
                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-4 h-4"
                  />
                  <span class="font-medium text-slate-800 dark:text-zinc-200">
                    {{ wh.name }} {{ wh.code ? `(${wh.code})` : '' }} {{ wh.is_default ? '★' : '' }}
                  </span>
                </label>
              </div>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-zinc-500">Multi-warehouse isolation; filters line items and counter options below.</p>
          </div>

          <!-- 5. Multi-Select Floating Dropdown: POS Counter (Cascading) -->
          <div class="space-y-1.5 relative" @click.stop>
            <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
              POS Counter (Multi-Select)
            </label>
            <div class="relative">
              <button
                type="button"
                @click="togglePopover('counter')"
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer"
              >
                <span class="truncate pr-2" :class="{ 'text-slate-400 dark:text-zinc-500': localFilters.counter_ids.length === 0 }">
                  {{ counterSummaryLabel }}
                </span>
                <span v-if="localFilters.counter_ids.length > 0" class="text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 px-1.5 py-0.2 rounded-full shrink-0">
                  {{ localFilters.counter_ids.length }}
                </span>
              </button>

              <!-- Floating Multi-Select Popover -->
              <div
                v-if="activePopover === 'counter'"
                class="absolute left-0 right-0 bottom-full mb-1 z-50 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-xl py-1 max-h-60 overflow-y-auto custom-scrollbar animate-fade-in"
              >
                <div class="px-3 py-1.5 border-b border-slate-100 dark:border-zinc-800 flex justify-between items-center text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase">
                  <span>
                    {{ localFilters.warehouse_ids.length > 0 ? 'Filtered Counters' : 'All Counters' }}
                  </span>
                  <button @click="localFilters.counter_ids = []" class="text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">Clear</button>
                </div>
                <div v-if="filteredCounters.length === 0" class="py-4 text-center text-slate-400 text-xs italic px-3">
                  No counters available for the selected warehouse(s).
                </div>
                <label
                  v-for="c in filteredCounters"
                  :key="c.id"
                  class="px-3 py-2 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center space-x-2.5 cursor-pointer text-xs select-none border-b border-slate-50 dark:border-zinc-800/40"
                >
                  <input
                    type="checkbox"
                    :value="c.id"
                    v-model="localFilters.counter_ids"
                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-4 h-4"
                  />
                  <span class="font-medium text-slate-800 dark:text-zinc-200">
                    {{ c.name }} {{ c.counter_number ? `(#${c.counter_number})` : '' }}
                    <span v-if="c.warehouse?.name" class="text-slate-400 text-[10px]">({{ c.warehouse.name }})</span>
                  </span>
                </label>
              </div>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-zinc-500">Cascades based on selected warehouse location above.</p>
          </div>

          <!-- 6. Multi-Select Floating Dropdown: Sales Representative / Salesman -->
          <div class="space-y-1.5 relative" @click.stop>
            <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">
              Sales Representative / Salesman (Multi-Select)
            </label>
            <div class="relative">
              <button
                type="button"
                @click="togglePopover('salesman')"
                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-lg text-slate-800 dark:text-zinc-100 flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer"
              >
                <span class="truncate pr-2" :class="{ 'text-slate-400 dark:text-zinc-500': localFilters.salesman_ids.length === 0 }">
                  {{ salesmanSummaryLabel }}
                </span>
                <span v-if="localFilters.salesman_ids.length > 0" class="text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 px-1.5 py-0.2 rounded-full shrink-0">
                  {{ localFilters.salesman_ids.length }}
                </span>
              </button>

              <!-- Floating Multi-Select Popover -->
              <div
                v-if="activePopover === 'salesman'"
                class="absolute left-0 right-0 bottom-full mb-1 z-50 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-xl py-1 max-h-60 overflow-y-auto custom-scrollbar animate-fade-in"
              >
                <div class="px-3 py-1.5 border-b border-slate-100 dark:border-zinc-800 flex justify-between items-center text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase">
                  <span>Select Sales Representatives</span>
                  <button @click="localFilters.salesman_ids = []" class="text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">Clear</button>
                </div>
                <div v-if="salesmen.length === 0" class="py-4 text-center text-slate-400 text-xs italic">
                  No sales reps found.
                </div>
                <label
                  v-for="emp in salesmen"
                  :key="emp.id"
                  class="px-3 py-2 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center space-x-2.5 cursor-pointer text-xs select-none border-b border-slate-50 dark:border-zinc-800/40"
                >
                  <input
                    type="checkbox"
                    :value="emp.id"
                    v-model="localFilters.salesman_ids"
                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-4 h-4"
                  />
                  <span class="font-medium text-slate-800 dark:text-zinc-200">
                    {{ emp.full_name }}
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
              class="px-5 py-2.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 active:scale-95 rounded-lg shadow-sm transition-all flex items-center cursor-pointer"
            >
              <span>Apply Filters</span>
            </button>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
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

// Active Popover Name: 'product' | 'warehouse' | 'counter' | 'salesman' | 'status' | 'date_range' | null
const activePopover = ref(null);
const subPickerOpen = ref(null);

const togglePopover = (name) => {
  if (activePopover.value === name) {
    activePopover.value = null;
  } else {
    activePopover.value = name;
  }
  subPickerOpen.value = null;
};

const closeAllPopovers = () => {
  activePopover.value = null;
  subPickerOpen.value = null;
};

// Local state for multi-select & popover filters
const localFilters = ref({
  product_id: '',
  product_name: '',
  product_search: '',
  warehouse_ids: [],
  counter_ids: [],
  salesman_ids: [],
  statuses: [],
  date_from: '',
  date_to: ''
});

// Lookups Data
const topProducts = ref([]);
const warehouses = ref([]);
const salesmen = ref([]);
const counters = ref([]);
const loadingTopProducts = ref(false);
const loadingDropdowns = ref(false);

const availableStatuses = [
  { id: 'completed', label: 'Paid / Completed', colorClass: 'text-emerald-600 dark:text-emerald-400' },
  { id: 'pending', label: 'Due / Pending', colorClass: 'text-orange-600 dark:text-orange-400' },
  { id: 'overdue', label: 'Overdue', colorClass: 'text-rose-600 dark:text-rose-400' },
  { id: 'draft', label: 'Draft', colorClass: 'text-slate-600 dark:text-zinc-400' },
  { id: 'recurring', label: 'Recurring', colorClass: 'text-blue-600 dark:text-blue-400' },
  { id: 'void', label: 'Void / Cancelled', colorClass: 'text-slate-500 dark:text-zinc-500' },
];

// Active Filter Count
const activeFilterCount = computed(() => {
  let count = 0;
  if (localFilters.value.product_id || localFilters.value.product_search) count++;
  if (localFilters.value.warehouse_ids && localFilters.value.warehouse_ids.length > 0) count++;
  if (localFilters.value.counter_ids && localFilters.value.counter_ids.length > 0) count++;
  if (localFilters.value.salesman_ids && localFilters.value.salesman_ids.length > 0) count++;
  if (localFilters.value.statuses && localFilters.value.statuses.length > 0) count++;
  if (localFilters.value.date_from || localFilters.value.date_to) count++;
  return count;
});

// Cascading Warehouses -> Counters filter
const filteredCounters = computed(() => {
  const list = Array.isArray(counters.value) ? counters.value : [];
  if (!localFilters.value.warehouse_ids || localFilters.value.warehouse_ids.length === 0) {
    return list;
  }
  const selectedWhs = localFilters.value.warehouse_ids.map(id => String(id));
  return list.filter(c => c.warehouse_id && selectedWhs.includes(String(c.warehouse_id)));
});

const fetchCounters = async (whIds = []) => {
  try {
    const params = {};
    if (whIds && whIds.length > 0) {
      params.warehouse_ids = whIds;
      params.warehouse_id = whIds.join(',');
    }
    const cntRes = await axios.get('/api/counters', { params });
    counters.value = extractArray(cntRes.data);
  } catch (err) {
    console.error('Failed to fetch counters for selected warehouses:', err);
  }
};

// Watch warehouse selection to query counters and purge non-matching counter IDs
watch(() => [...localFilters.value.warehouse_ids], async (newWhs) => {
  await fetchCounters(newWhs);
  if (localFilters.value.counter_ids && localFilters.value.counter_ids.length > 0) {
    const validCounterIds = counters.value.map(c => String(c.id));
    localFilters.value.counter_ids = localFilters.value.counter_ids.filter(id => validCounterIds.includes(String(id)));
  }
}, { deep: true, immediate: true });

// UI Labels for Button Triggers
const selectedProductLabel = computed(() => {
  if (localFilters.value.product_name) return localFilters.value.product_name;
  if (localFilters.value.product_search) return localFilters.value.product_search;
  if (localFilters.value.product_id) {
    const p = topProducts.value.find(item => String(item.id) === String(localFilters.value.product_id));
    return p ? p.name : `Product #${localFilters.value.product_id}`;
  }
  return '';
});

const warehouseSummaryLabel = computed(() => {
  const ids = localFilters.value.warehouse_ids || [];
  if (ids.length === 0) return 'All Warehouses / Shops';
  if (ids.length === 1) {
    const wh = warehouses.value.find(w => String(w.id) === String(ids[0]));
    return wh ? wh.name : `Warehouse #${ids[0]}`;
  }
  const firstWh = warehouses.value.find(w => String(w.id) === String(ids[0]));
  const firstName = firstWh ? firstWh.name : `Shop #${ids[0]}`;
  return `${firstName} (+${ids.length - 1} more)`;
});

const counterSummaryLabel = computed(() => {
  const ids = localFilters.value.counter_ids || [];
  if (ids.length === 0) return 'All POS Counters';
  if (ids.length === 1) {
    const c = counters.value.find(item => String(item.id) === String(ids[0]));
    return c ? c.name : `Counter #${ids[0]}`;
  }
  const firstC = counters.value.find(item => String(item.id) === String(ids[0]));
  const firstName = firstC ? firstC.name : `Counter #${ids[0]}`;
  return `${firstName} (+${ids.length - 1} more)`;
});

const salesmanSummaryLabel = computed(() => {
  const ids = localFilters.value.salesman_ids || [];
  if (ids.length === 0) return 'All Sales Representatives';
  if (ids.length === 1) {
    const emp = salesmen.value.find(item => String(item.id) === String(ids[0]));
    return emp ? emp.full_name : `Rep #${ids[0]}`;
  }
  const firstEmp = salesmen.value.find(item => String(item.id) === String(ids[0]));
  const firstName = firstEmp ? firstEmp.full_name : `Rep #${ids[0]}`;
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

const syncPropsToLocal = () => {
  const p = props.filters || {};
  
  // Convert incoming scalar / array parameters
  let whs = p.warehouse_ids || p.warehouse_id || [];
  if (!Array.isArray(whs)) whs = String(whs).split(',').filter(Boolean);
  
  let cnts = p.counter_ids || p.counter_id || [];
  if (!Array.isArray(cnts)) cnts = String(cnts).split(',').filter(Boolean);

  let sales = p.salesman_ids || p.salesman_id || [];
  if (!Array.isArray(sales)) sales = String(sales).split(',').filter(Boolean);

  let stList = p.statuses || p.status || [];
  if (!Array.isArray(stList)) stList = String(stList).split(',').filter(Boolean);

  localFilters.value = {
    product_id: p.product_id || '',
    product_name: p.product_name || '',
    product_search: p.product_search || '',
    warehouse_ids: whs.map(id => String(id)),
    counter_ids: cnts.map(id => String(id)),
    salesman_ids: sales.map(id => String(id)),
    statuses: stList,
    date_from: p.date_from || '',
    date_to: p.date_to || ''
  };
};

const toggleBodyScroll = (disable) => {
  if (disable) {
    document.body.classList.add('overflow-hidden');
    document.documentElement.classList.add('overflow-hidden');
  } else {
    document.body.classList.remove('overflow-hidden');
    document.documentElement.classList.remove('overflow-hidden');
  }
};

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    toggleBodyScroll(true);
    syncPropsToLocal();
    fetchDropdownData();
    fetchTopProducts();
  } else {
    toggleBodyScroll(false);
  }
}, { immediate: true });

watch(() => props.filters, () => {
  syncPropsToLocal();
}, { deep: true });

const extractArray = (resData) => {
  if (Array.isArray(resData)) return resData;
  if (resData && Array.isArray(resData.data)) return resData.data;
  return [];
};

const fetchTopProducts = async () => {
  if (topProducts.value.length > 0) return;
  loadingTopProducts.value = true;
  try {
    const res = await axios.get('/api/products', { params: { per_page: 20 } });
    topProducts.value = extractArray(res.data);
  } catch (err) {
    console.error('Failed fetching top products:', err);
  } finally {
    loadingTopProducts.value = false;
  }
};

const fetchDropdownData = async () => {
  if (warehouses.value.length > 0 && salesmen.value.length > 0 && counters.value.length > 0) {
    return;
  }
  loadingDropdowns.value = true;
  try {
    const [whRes, salesRes] = await Promise.all([
      axios.get('/api/warehouses').catch(() => ({ data: [] })),
      axios.get('/api/employees/for-dropdown').catch(() => ({ data: [] })),
    ]);
    warehouses.value = extractArray(whRes.data);
    salesmen.value = extractArray(salesRes.data);
    await fetchCounters(localFilters.value.warehouse_ids);
  } catch (err) {
    console.error('Failed to load filter dropdown data:', err);
  } finally {
    loadingDropdowns.value = false;
  }
};

const selectProduct = (p) => {
  if (!p) {
    localFilters.value.product_id = '';
    localFilters.value.product_name = '';
    localFilters.value.product_search = '';
  } else {
    localFilters.value.product_id = p.id;
    localFilters.value.product_name = p.name;
    localFilters.value.product_search = p.name;
  }
  activePopover.value = null;
};

const clearProduct = () => {
  localFilters.value.product_id = '';
  localFilters.value.product_name = '';
  localFilters.value.product_search = '';
};

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

const toggleSubPicker = (type) => {
  subPickerOpen.value = subPickerOpen.value === type ? null : type;
};

const selectMonth = (idx) => {
  calendarMonth.value = idx;
  subPickerOpen.value = null;
};

const selectYear = (y) => {
  calendarYear.value = y;
  subPickerOpen.value = null;
};

const calendarMonthTitle = computed(() => {
  return `${monthNames[calendarMonth.value]} ${calendarYear.value}`;
});

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

const calendarDays = computed(() => {
  const year = calendarYear.value;
  const month = calendarMonth.value;

  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const daysInPrevMonth = new Date(year, month, 0).getDate();

  const days = [];

  for (let i = firstDay - 1; i >= 0; i--) {
    const d = daysInPrevMonth - i;
    const prevM = month === 0 ? 11 : month - 1;
    const prevY = month === 0 ? year - 1 : year;
    const dateStr = `${prevY}-${String(prevM + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    days.push({ dayNum: d, dateStr, isCurrentMonth: false });
  }

  for (let d = 1; d <= daysInMonth; d++) {
    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    days.push({ dayNum: d, dateStr, isCurrentMonth: true });
  }

  const totalSlots = days.length > 35 ? 42 : 35;
  const remaining = totalSlots - days.length;
  for (let d = 1; d <= remaining; d++) {
    const nextM = month === 11 ? 0 : month + 1;
    const nextY = month === 11 ? year + 1 : year;
    const dateStr = `${nextY}-${String(nextM + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    days.push({ dayNum: d, dateStr, isCurrentMonth: false });
  }

  return days;
});

const todayDateStr = computed(() => {
  const now = new Date();
  const yyyy = now.getFullYear();
  const mm = String(now.getMonth() + 1).padStart(2, '0');
  const dd = String(now.getDate()).padStart(2, '0');
  return `${yyyy}-${mm}-${dd}`;
});

const isToday = (dateStr) => dateStr === todayDateStr.value;
const isDateStart = (dateStr) => localFilters.value.date_from === dateStr;
const isDateEnd = (dateStr) => localFilters.value.date_to === dateStr;

const isDateHoverEnd = (dateStr) => {
  return !!(localFilters.value.date_from && !localFilters.value.date_to && hoverDate.value === dateStr);
};

const isDateInRange = (dateStr) => {
  const from = localFilters.value.date_from;
  const to = localFilters.value.date_to || (from && hoverDate.value && hoverDate.value >= from ? hoverDate.value : null);
  if (!from || !to) return false;
  return dateStr >= from && dateStr <= to;
};

const handleDateClick = (dateStr) => {
  const from = localFilters.value.date_from;
  const to = localFilters.value.date_to;

  if (!from || (from && to)) {
    localFilters.value.date_from = dateStr;
    localFilters.value.date_to = '';
  } else if (from && !to) {
    if (dateStr < from) {
      localFilters.value.date_from = dateStr;
      localFilters.value.date_to = '';
    } else {
      localFilters.value.date_to = dateStr;
    }
  }
};

const formatDateLabel = (dateStr) => {
  if (!dateStr) return '';
  const parts = dateStr.split('-');
  if (parts.length !== 3) return dateStr;
  const year = parseInt(parts[0]);
  const month = parseInt(parts[1]) - 1;
  const day = parseInt(parts[2]);
  const d = new Date(year, month, day);
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const dateRangeSummaryLabel = computed(() => {
  const from = localFilters.value.date_from;
  const to = localFilters.value.date_to;
  if (from && to) {
    if (from === to) return formatDateLabel(from);
    return `${formatDateLabel(from)} - ${formatDateLabel(to)}`;
  }
  if (from) return `From ${formatDateLabel(from)}`;
  if (to) return `Until ${formatDateLabel(to)}`;
  return '';
});

const clearDateRange = () => {
  localFilters.value.date_from = '';
  localFilters.value.date_to = '';
};

const setQuickDate = (preset) => {
  const now = new Date();
  let from = '';
  let to = '';

  const format = (d) => {
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
  };

  if (preset === 'today') {
    from = format(now);
    to = format(now);
  } else if (preset === 'yesterday') {
    const y = new Date(now);
    y.setDate(now.getDate() - 1);
    from = format(y);
    to = format(y);
  } else if (preset === 'this_week') {
    const dayOfWeek = now.getDay();
    const distanceToMon = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
    const mon = new Date(now);
    mon.setDate(now.getDate() - distanceToMon);
    const sun = new Date(mon);
    sun.setDate(mon.getDate() + 6);
    from = format(mon);
    to = format(sun);
  } else if (preset === 'this_month') {
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    from = format(firstDay);
    to = format(lastDay);
  } else if (preset === 'last_month') {
    const firstDay = new Date(now.getFullYear(), now.getMonth() - 1, 1);
    const lastDay = new Date(now.getFullYear(), now.getMonth(), 0);
    from = format(firstDay);
    to = format(lastDay);
  } else if (preset === 'this_year') {
    const firstDay = new Date(now.getFullYear(), 0, 1);
    const lastDay = new Date(now.getFullYear(), 11, 31);
    from = format(firstDay);
    to = format(lastDay);
  } else if (preset === 'last_year') {
    const firstDay = new Date(now.getFullYear() - 1, 0, 1);
    const lastDay = new Date(now.getFullYear() - 1, 11, 31);
    from = format(firstDay);
    to = format(lastDay);
  }

  localFilters.value.date_from = from;
  localFilters.value.date_to = to;

  if (from) {
    const parts = from.split('-');
    if (parts.length === 3) {
      calendarYear.value = parseInt(parts[0]);
      calendarMonth.value = parseInt(parts[1]) - 1;
    }
  }
};

const close = () => {
  closeAllPopovers();
  emit('update:isOpen', false);
};

const applyFilters = () => {
  closeAllPopovers();
  emit('apply', { ...localFilters.value });
  close();
};

const resetFilters = () => {
  closeAllPopovers();
  localFilters.value = {
    product_id: '',
    product_name: '',
    product_search: '',
    warehouse_ids: [],
    counter_ids: [],
    salesman_ids: [],
    statuses: [],
    date_from: '',
    date_to: ''
  };
  emit('reset');
  close();
};

const handleDocumentClick = (e) => {
  if (!props.isOpen) return;
  activePopover.value = null;
};

onMounted(() => {
  fetchDropdownData();
  fetchTopProducts();
  document.addEventListener('click', handleDocumentClick);
});

onUnmounted(() => {
  toggleBodyScroll(false);
  document.removeEventListener('click', handleDocumentClick);
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
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
    transform: translateY(-4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
