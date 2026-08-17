<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 font-sans">
    <div class="px-4 py-6 sm:px-0">
      <!-- Header Bar -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4 w-full">
        <div class="w-full sm:w-auto">
          <h1 class="text-3xl font-bold text-zinc-950 dark:text-white tracking-tight">Dashboard</h1>
          <p class="text-xs text-zinc-500 dark:text-zinc-400 font-medium mt-1">Real-time database performance and inventory metrics</p>
        </div>

        <!-- Right Controls: Customize Cards + Date Range Filter -->
        <div class="flex flex-wrap sm:flex-nowrap items-center gap-3 w-full sm:w-auto">
          <!-- Customize Cards Button -->
          <button 
            @click="showCustomizeModal = true" 
            class="flex items-center gap-2 px-3.5 py-2 text-xs font-bold text-zinc-800 dark:text-zinc-200 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-xs hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-all cursor-pointer shrink-0"
            title="Show or hide dashboard cards"
          >
            <svg class="w-4 h-4 text-zinc-500 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            <span>Customize Cards</span>
          </button>

          <!-- Date Range Filter Container -->
          <div class="flex flex-col gap-1 w-full sm:w-auto">
            <!-- Top Row with Label and Presets -->
            <div class="flex items-center justify-between px-0.5 gap-2">
              <span class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Date Filter</span>
              <div class="flex gap-1">
                <button
                  type="button"
                  @click="clearDateRange"
                  class="px-2 py-0.5 text-[10px] font-bold rounded transition-colors cursor-pointer"
                  :class="(!dateRange.from && !dateRange.to) ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300'"
                >
                  All Time
                </button>
                <button
                  type="button"
                  @click="setQuickDate('today'); applyCurrentTempRange();"
                  class="px-2 py-0.5 text-[10px] font-bold bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 rounded transition-colors cursor-pointer"
                >
                  Today
                </button>
                <button
                  type="button"
                  @click="setQuickDate('this_month'); applyCurrentTempRange();"
                  class="px-2 py-0.5 text-[10px] font-bold bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 rounded transition-colors cursor-pointer"
                >
                  This Month
                </button>
              </div>
            </div>

            <!-- Date Input Button -->
            <div ref="pickerRef" class="relative w-full">
              <button 
                @click="showPicker = !showPicker" 
                class="w-full sm:w-72 flex items-center justify-between bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-3.5 py-2 text-xs font-bold text-zinc-800 dark:text-white shadow-xs hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-all cursor-pointer focus:outline-none"
              >
                <span class="truncate pr-2" :class="{ 'text-zinc-400 dark:text-zinc-500': !formattedDateRangeLabel }">
                  {{ formattedDateRangeLabel || 'All Time / Select Custom Range' }}
                </span>
                <div class="flex items-center space-x-1.5">
                  <button
                    v-if="dateRange.from || dateRange.to"
                    @click.stop="clearDateRange"
                    class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 px-1 font-bold text-xs cursor-pointer focus:outline-none"
                    title="Clear date range"
                  >
                    ✕
                  </button>
                </div>
              </button>

              <!-- Dropdown Calendar Card -->
              <transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="opacity-0 scale-95 translate-y-[-10px]"
                enter-to-class="opacity-100 scale-100 translate-y-0"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="opacity-100 scale-100 translate-y-0"
                leave-to-class="opacity-0 scale-95 translate-y-[-10px]"
              >
                <div 
                  v-show="showPicker" 
                  class="absolute right-0 mt-1.5 z-[100] w-80 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-xl p-3 flex flex-col gap-3 text-left font-sans select-none"
                >
                  <!-- Quick Preset Pills inside Calendar -->
                  <div class="flex flex-wrap gap-1.5 pb-2 border-b border-zinc-100 dark:border-zinc-800">
                    <button
                      v-for="p in presets"
                      :key="p.id"
                      type="button"
                      @click="setQuickDate(p.id); applyCurrentTempRange();"
                      class="px-2 py-1 text-[10px] font-bold bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 rounded-lg transition-colors cursor-pointer"
                    >
                      {{ p.label }}
                    </button>
                    <button
                      type="button"
                      @click="clearDateRange"
                      class="px-2 py-1 text-[10px] font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors ml-auto cursor-pointer"
                    >
                      Clear (All Time)
                    </button>
                  </div>

                  <!-- Calendar Header: Month & Year Selector -->
                  <div class="flex items-center justify-between pb-1.5 relative">
                    <button
                      type="button"
                      @click="prevMonth"
                      class="w-7 h-7 flex items-center justify-center text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-xl font-bold cursor-pointer transition-colors"
                    >
                      &lt;
                    </button>

                    <div class="flex items-center space-x-1.5 relative">
                      <button
                        type="button"
                        @click.stop="toggleSubPicker('month')"
                        class="px-2.5 py-1 font-bold text-zinc-800 dark:text-zinc-100 text-xs hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-xl transition-colors flex items-center gap-1 cursor-pointer"
                        :class="{ 'bg-zinc-100 dark:bg-zinc-800 text-blue-600 dark:text-blue-400': subPickerOpen === 'month' }"
                      >
                        <span>{{ monthNames[calendarMonth] }}</span>
                        <span class="text-[8px] text-zinc-400">▲</span>
                      </button>

                      <button
                        type="button"
                        @click.stop="toggleSubPicker('year')"
                        class="px-2.5 py-1 font-bold text-zinc-800 dark:text-zinc-100 text-xs hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-xl transition-colors flex items-center gap-1 cursor-pointer"
                        :class="{ 'bg-zinc-100 dark:bg-zinc-800 text-blue-600 dark:text-blue-400': subPickerOpen === 'year' }"
                      >
                        <span>{{ calendarYear }}</span>
                        <span class="text-[8px] text-zinc-400">▲</span>
                      </button>
                    </div>

                    <button
                      type="button"
                      @click="nextMonth"
                      class="w-7 h-7 flex items-center justify-center text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-xl font-bold cursor-pointer transition-colors"
                    >
                      &gt;
                    </button>
                  </div>

                  <!-- Days Grid -->
                  <div class="grid grid-cols-7 text-center text-[10px] font-bold text-zinc-400 dark:text-zinc-500 mb-1">
                    <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                  </div>

                  <div class="grid grid-cols-7 gap-y-1 text-center" @mouseleave="hoverDate = null">
                    <div
                      v-for="(day, idx) in calendarDays"
                      :key="idx"
                      class="relative py-0.5 flex items-center justify-center cursor-pointer"
                      @click="handleDateClick(day.dateStr)"
                      @mouseenter="hoverDate = day.dateStr"
                    >
                      <button
                        type="button"
                        class="relative z-10 w-7 h-7 text-xs flex items-center justify-center rounded-xl font-bold transition-all cursor-pointer"
                        :class="{
                          'opacity-30': !day.isCurrentMonth,
                          'bg-black text-white dark:bg-white dark:text-black font-bold shadow-xs': isDateStart(day.dateStr) || isDateEnd(day.dateStr),
                          'text-blue-600 dark:text-blue-400 font-bold': isDateInRange(day.dateStr) && !isDateStart(day.dateStr) && !isDateEnd(day.dateStr),
                          'hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-200': !isDateStart(day.dateStr) && !isDateEnd(day.dateStr) && !isDateInRange(day.dateStr)
                        }"
                      >
                        {{ day.dayNum }}
                      </button>
                    </div>
                  </div>

                  <!-- Footer -->
                  <div class="mt-2 pt-2 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-[10px]">
                    <span class="italic text-zinc-400">Click to select range</span>
                    <button
                      type="button"
                      @click="applyCurrentTempRange"
                      class="px-3 py-1.5 font-bold text-white bg-black dark:bg-white dark:text-black rounded-xl hover:opacity-90 transition-all text-[10px] cursor-pointer shadow-xs"
                    >
                      Apply Filter
                    </button>
                  </div>
                </div>
              </transition>
            </div>
          </div>
        </div>
      </div>

      <!-- Skeleton Loading State -->
      <div v-if="loading" class="space-y-8 animate-pulse">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div v-for="i in 4" :key="'stat-skel-' + i" class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-xs">
            <div class="flex items-center justify-between">
              <div class="w-11 h-11 bg-zinc-200 dark:bg-zinc-800 rounded-xl"></div>
              <div class="w-16 h-6 bg-zinc-200 dark:bg-zinc-800 rounded-full"></div>
            </div>
            <div class="mt-4 space-y-2">
              <div class="w-24 h-3 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
              <div class="w-36 h-7 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Dashboard Content -->
      <div v-else class="w-full overflow-hidden">
        <!-- 1. Primary Financial & Operational Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8 w-full">
          <!-- Total Real Sales Card -->
          <div v-if="cardVisibility.total_sales" class="bg-white dark:bg-zinc-900 overflow-hidden shadow-xs hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div class="w-full">
                  <div class="flex items-center justify-between">
                    <div class="w-11 h-11 bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 rounded-xl flex items-center justify-center shadow-xs">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                      </svg>
                    </div>
                    <span class="text-[11px] font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 px-2.5 py-1 rounded-full border border-emerald-200/60 dark:border-emerald-800/50">
                      {{ dashboardData.sales?.count || 0 }} Sales
                    </span>
                  </div>
                  <div class="mt-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">Real Sales</p>
                    <p class="text-2xl font-bold text-slate-800 dark:text-zinc-100 mt-0.5 tracking-tight">{{ formatAmount(dashboardData.sales?.total_amount || 0) }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-1">Avg: {{ formatAmount(dashboardData.sales?.average_sale || 0) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sale Returns Card -->
          <div v-if="cardVisibility.sale_returns" class="bg-white dark:bg-zinc-900 overflow-hidden shadow-xs hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div class="w-full">
                  <div class="flex items-center justify-between">
                    <div class="w-11 h-11 bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 rounded-xl flex items-center justify-center shadow-xs">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 15v-1a4 4 0 00-4-4H4m0 0l4-4m-4 4l4 4"></path>
                      </svg>
                    </div>
                    <span class="text-[11px] font-medium bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 px-2.5 py-1 rounded-full border border-rose-200/60 dark:border-rose-800/50">
                      {{ dashboardData.returns?.count || 0 }} Returns
                    </span>
                  </div>
                  <div class="mt-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">Sale Returns</p>
                    <p class="text-2xl font-bold text-slate-800 dark:text-zinc-100 mt-0.5 tracking-tight">{{ formatAmount(dashboardData.returns?.total_amount || 0) }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-1">Refunded to customers</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Total Real Purchases Card -->
          <div v-if="cardVisibility.total_purchases" class="bg-white dark:bg-zinc-900 overflow-hidden shadow-xs hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div class="w-full">
                  <div class="flex items-center justify-between">
                    <div class="w-11 h-11 bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400 rounded-xl flex items-center justify-center shadow-xs">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                      </svg>
                    </div>
                    <span class="text-[11px] font-medium bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 px-2.5 py-1 rounded-full border border-blue-200/60 dark:border-blue-800/50">
                      {{ dashboardData.purchases?.count || 0 }} Orders
                    </span>
                  </div>
                  <div class="mt-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">Purchase Orders</p>
                    <p class="text-2xl font-bold text-slate-800 dark:text-zinc-100 mt-0.5 tracking-tight">{{ formatAmount(dashboardData.purchases?.total_amount || 0) }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-1">Total inventory procurement</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Purchase Returns Card -->
          <div v-if="cardVisibility.purchase_returns" class="bg-white dark:bg-zinc-900 overflow-hidden shadow-xs hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div class="w-full">
                  <div class="flex items-center justify-between">
                    <div class="w-11 h-11 bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 rounded-xl flex items-center justify-center shadow-xs">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                      </svg>
                    </div>
                    <span class="text-[11px] font-medium bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 px-2.5 py-1 rounded-full border border-amber-200/60 dark:border-amber-800/50">
                      {{ dashboardData.purchase_returns?.count || 0 }} Returns
                    </span>
                  </div>
                  <div class="mt-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">Purchase Returns</p>
                    <p class="text-2xl font-bold text-slate-800 dark:text-zinc-100 mt-0.5 tracking-tight">{{ formatAmount(dashboardData.purchase_returns?.total_amount || 0) }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-1">Returned to suppliers</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Total Expenses Card -->
          <div v-if="cardVisibility.total_expenses" class="bg-white dark:bg-zinc-900 overflow-hidden shadow-xs hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div class="w-full">
                  <div class="flex items-center justify-between">
                    <div class="w-11 h-11 bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 rounded-xl flex items-center justify-center shadow-xs">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                      </svg>
                    </div>
                    <span class="text-[11px] font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 px-2.5 py-1 rounded-full border border-indigo-200/60 dark:border-indigo-800/50">
                      {{ dashboardData.expenses?.count || 0 }} Items
                    </span>
                  </div>
                  <div class="mt-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">Total Expenses</p>
                    <p class="text-2xl font-bold text-slate-800 dark:text-zinc-100 mt-0.5 tracking-tight">{{ formatAmount(dashboardData.expenses?.total_amount || 0) }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-1">Operational overhead</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Real Payments In Card -->
          <div v-if="cardVisibility.payments_in" class="bg-white dark:bg-zinc-900 overflow-hidden shadow-xs hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div class="w-full">
                  <div class="flex items-center justify-between">
                    <div class="w-11 h-11 bg-teal-50 text-teal-600 dark:bg-teal-950/40 dark:text-teal-400 rounded-xl flex items-center justify-center shadow-xs">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                      </svg>
                    </div>
                    <span class="text-[11px] font-medium bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300 px-2.5 py-1 rounded-full border border-teal-200/60 dark:border-teal-800/50">
                      INFLOW
                    </span>
                  </div>
                  <div class="mt-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">Payments In</p>
                    <p class="text-2xl font-bold text-slate-800 dark:text-zinc-100 mt-0.5 tracking-tight">{{ formatAmount(dashboardData.payments?.payment_received?.total_amount || 0) }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-1">Total cash / bank receipts</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Real Payments Out Card -->
          <div v-if="cardVisibility.payments_out" class="bg-white dark:bg-zinc-900 overflow-hidden shadow-xs hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div class="w-full">
                  <div class="flex items-center justify-between">
                    <div class="w-11 h-11 bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-400 rounded-xl flex items-center justify-center shadow-xs">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                      </svg>
                    </div>
                    <span class="text-[11px] font-medium bg-violet-50 text-violet-700 dark:bg-violet-950/60 dark:text-violet-300 px-2.5 py-1 rounded-full border border-violet-200/60 dark:border-violet-800/50">
                      OUTFLOW
                    </span>
                  </div>
                  <div class="mt-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">Payments Out</p>
                    <p class="text-2xl font-bold text-slate-800 dark:text-zinc-100 mt-0.5 tracking-tight">{{ formatAmount(dashboardData.payments?.payment_sent?.total_amount || 0) }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-1">Total disbursements</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Pending Payments Card -->
          <div v-if="cardVisibility.pending_payments" class="bg-white dark:bg-zinc-900 overflow-hidden shadow-xs hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div class="w-full">
                  <div class="flex items-center justify-between">
                    <div class="w-11 h-11 bg-orange-50 text-orange-600 dark:bg-orange-950/40 dark:text-orange-400 rounded-xl flex items-center justify-center shadow-xs">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </div>
                    <span class="text-[11px] font-medium bg-orange-50 text-orange-700 dark:bg-orange-950/60 dark:text-orange-300 px-2.5 py-1 rounded-full border border-orange-200/60 dark:border-orange-800/50">
                      {{ dashboardData.payments?.pending_payments || 0 }} PENDING
                    </span>
                  </div>
                  <div class="mt-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">Pending Receivables / Payables</p>
                    <p class="text-2xl font-bold text-slate-800 dark:text-zinc-100 mt-0.5 tracking-tight">{{ formatAmount(dashboardData.payments?.pending_amount || 0) }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-1">Outstanding dues</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. Inventory Valuation Cards -->
        <div v-if="cardVisibility.inventory_valuation_cost || cardVisibility.inventory_valuation_retail || cardVisibility.potential_profit" class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-8 w-full">
          <!-- Total Cost Value -->
          <div v-if="cardVisibility.inventory_valuation_cost" class="bg-white dark:bg-zinc-900 overflow-hidden shadow-xs hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">Total Inventory Value (Cost)</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-zinc-100 mt-2 tracking-tight">{{ formatAmount(dashboardData.inventory_valuation?.total_cost_value || 0) }}</p>
              </div>
              <div class="w-11 h-11 bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-300 rounded-xl flex items-center justify-center shadow-xs shrink-0 ml-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
              </div>
            </div>
          </div>

          <!-- Total Retail Value -->
          <div v-if="cardVisibility.inventory_valuation_retail" class="bg-white dark:bg-zinc-900 overflow-hidden shadow-xs hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">Total Inventory Value (Retail)</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-zinc-100 mt-2 tracking-tight">{{ formatAmount(dashboardData.inventory_valuation?.total_retail_value || 0) }}</p>
              </div>
              <div class="w-11 h-11 bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-300 rounded-xl flex items-center justify-center shadow-xs shrink-0 ml-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path></svg>
              </div>
            </div>
          </div>

          <!-- Potential Profit -->
          <div v-if="cardVisibility.potential_profit" class="bg-white dark:bg-zinc-900 overflow-hidden shadow-xs hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-zinc-500">Potential Profit Margin</p>
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-2 tracking-tight">{{ formatAmount(dashboardData.inventory_valuation?.potential_profit || 0) }}</p>
              </div>
              <div class="w-11 h-11 bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 rounded-xl flex items-center justify-center shadow-xs shrink-0 ml-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
              </div>
            </div>
          </div>
        </div>

        <!-- 3. Product Intelligence & Expiry alerts with Brand Badges and Category Tree -->
        <div v-if="cardVisibility.fast_moving || cardVisibility.slow_moving || cardVisibility.expiring_soon" class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          <!-- Fast Moving Items -->
          <div v-if="cardVisibility.fast_moving" class="bg-white dark:bg-zinc-900 shadow-xs rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800">
            <div class="px-6 py-4 bg-zinc-100 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
              <h3 class="font-bold text-slate-800 dark:text-zinc-100 uppercase tracking-wider text-xs flex items-center">
                <svg class="w-4 h-4 mr-2 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                Fast-Moving Items
              </h3>
              <span class="text-[10px] font-bold bg-slate-900 text-white dark:bg-white dark:text-slate-900 px-2.5 py-0.5 rounded-full">TOP SALES</span>
            </div>
            <div class="p-4 space-y-3">
              <div v-for="item in dashboardData.product_intelligence?.fast_moving" :key="item.name" class="p-3 bg-zinc-50 dark:bg-zinc-950/60 rounded-xl border border-zinc-200/80 dark:border-zinc-800 space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-bold text-slate-800 dark:text-zinc-100 truncate mr-2">{{ item.name }}</span>
                  <span class="text-[11px] font-bold text-white bg-slate-900 dark:bg-white dark:text-slate-900 px-2.5 py-0.5 rounded-lg shadow-xs shrink-0">{{ item.total_sold }} sold</span>
                </div>
                <!-- Brand Badge & Category Hierarchy -->
                <div class="flex flex-wrap items-center gap-1.5 text-[10px]">
                  <span v-if="item.brand_name" class="px-2 py-0.5 font-bold rounded-md bg-zinc-200 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700">
                    Brand: {{ item.brand_name }}
                  </span>
                  <span class="px-2 py-0.5 font-bold rounded-md bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                    {{ item.main_category }}
                  </span>
                  <span v-if="item.sub_category" class="px-2 py-0.5 font-bold rounded-md bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                    {{ item.sub_category }}
                  </span>
                  <span v-if="item.child_category" class="px-2 py-0.5 font-bold rounded-md bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                    {{ item.child_category }}
                  </span>
                </div>
              </div>
              <div v-if="!dashboardData.product_intelligence?.fast_moving?.length" class="text-center py-6 text-zinc-400 text-xs italic">
                No sales recorded in selected period.
              </div>
            </div>
          </div>

          <!-- Slow Moving Items -->
          <div v-if="cardVisibility.slow_moving" class="bg-white dark:bg-zinc-900 shadow-xs rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800">
            <div class="px-6 py-4 bg-zinc-100 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
              <h3 class="font-bold text-slate-800 dark:text-zinc-100 uppercase tracking-wider text-xs flex items-center">
                <svg class="w-4 h-4 mr-2 text-slate-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V7h2v10z"></path></svg>
                Slow-Moving Items
              </h3>
              <span class="text-[10px] font-bold bg-slate-900 text-white dark:bg-white dark:text-slate-900 px-2.5 py-0.5 rounded-full">IN STOCK</span>
            </div>
            <div class="p-4 space-y-3">
              <div v-for="item in dashboardData.product_intelligence?.slow_moving" :key="item.name" class="p-3 bg-zinc-50 dark:bg-zinc-950/60 rounded-xl border border-zinc-200/80 dark:border-zinc-800 space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-bold text-slate-800 dark:text-zinc-100 truncate mr-2">{{ item.name }}</span>
                  <span class="text-[11px] font-semibold text-slate-800 bg-zinc-200 dark:bg-zinc-800 dark:text-white px-2.5 py-0.5 rounded-lg border border-zinc-300 dark:border-zinc-700 shrink-0">{{ item.stock_quantity }} in stock</span>
                </div>
                <!-- Brand Badge & Category Hierarchy -->
                <div class="flex flex-wrap items-center gap-1.5 text-[10px]">
                  <span v-if="item.brand_name" class="px-2 py-0.5 font-bold rounded-md bg-zinc-200 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700">
                    Brand: {{ item.brand_name }}
                  </span>
                  <span class="px-2 py-0.5 font-bold rounded-md bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                    {{ item.main_category }}
                  </span>
                  <span v-if="item.sub_category" class="px-2 py-0.5 font-bold rounded-md bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                    {{ item.sub_category }}
                  </span>
                  <span v-if="item.child_category" class="px-2 py-0.5 font-bold rounded-md bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                    {{ item.child_category }}
                  </span>
                </div>
              </div>
              <div v-if="!dashboardData.product_intelligence?.slow_moving?.length" class="text-center py-6 text-zinc-400 text-xs italic">
                No slow moving items found.
              </div>
            </div>
          </div>

          <!-- Expiry Management -->
          <div v-if="cardVisibility.expiring_soon" class="bg-white dark:bg-zinc-900 shadow-xs rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800">
            <div class="px-6 py-4 bg-zinc-100 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
              <h3 class="font-bold text-slate-800 dark:text-zinc-100 uppercase tracking-wider text-xs flex items-center">
                <svg class="w-4 h-4 mr-2 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V7h2v10z"></path></svg>
                Expiring Soon
              </h3>
              <span class="text-[10px] font-bold bg-slate-900 text-white dark:bg-white dark:text-slate-900 px-2.5 py-0.5 rounded-full">{{ dashboardData.expiry_alerts?.count || 0 }} ALERTS</span>
            </div>
            <div class="p-4 space-y-3">
              <div v-for="item in dashboardData.expiry_alerts?.items" :key="item.name" class="p-3 bg-zinc-50 dark:bg-zinc-950/60 rounded-xl border border-zinc-200/80 dark:border-zinc-800 space-y-2 flex flex-col">
                <div class="flex justify-between items-center">
                  <span class="text-xs font-bold text-slate-800 dark:text-zinc-100 truncate mr-2">{{ item.name }}</span>
                  <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase bg-slate-900 text-white dark:bg-white dark:text-slate-900 shrink-0">{{ item.status }}</span>
                </div>
                <!-- Brand Badge & Category Hierarchy -->
                <div class="flex flex-wrap items-center gap-1.5 text-[10px]">
                  <span v-if="item.brand_name" class="px-2 py-0.5 font-bold rounded-md bg-zinc-200 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700">
                    Brand: {{ item.brand_name }}
                  </span>
                  <span class="px-2 py-0.5 font-bold rounded-md bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                    {{ item.main_category }}
                  </span>
                  <span v-if="item.sub_category" class="px-2 py-0.5 font-bold rounded-md bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                    {{ item.sub_category }}
                  </span>
                  <span v-if="item.child_category" class="px-2 py-0.5 font-bold rounded-md bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                    {{ item.child_category }}
                  </span>
                </div>
                <div class="flex justify-between text-[11px] font-medium text-zinc-500 italic pt-1">
                  <span>Expires: {{ item.expiry_date }}</span>
                  <span class="text-slate-800 dark:text-white font-bold">{{ item.days_to_expire < 0 ? 'Expired' : item.days_to_expire + ' days' }}</span>
                </div>
              </div>
              <div v-if="!dashboardData.expiry_alerts?.items?.length" class="text-center py-6 text-zinc-400 text-xs italic">
                No items nearing expiry.
              </div>
            </div>
          </div>
        </div>

        <!-- 4. Charts Section: Line Chart + Operations Circle Chart -->
        <div v-if="cardVisibility.sales_purchases_chart || cardVisibility.financial_distribution" class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-8 w-full">
          <!-- Sales & Purchases Line Chart -->
          <div v-if="cardVisibility.sales_purchases_chart" class="w-full overflow-hidden rounded-2xl">
            <SalesPurchasesChart 
              :data="dashboardData.sales_purchases_chart || []" 
              :period="selectedChartPeriod"
              @period-change="handleChartPeriodChange"
            />
          </div>

          <!-- Operations Financial Distribution Circle Chart (Replaces Devices Breakdown) -->
          <div v-if="cardVisibility.financial_distribution" class="w-full overflow-hidden rounded-2xl">
            <DevicesPieChart :data="dashboardData.financial_distribution || []" />
          </div>
        </div>

        <!-- 5. Recent Invoices and Stock History -->
        <div v-if="cardVisibility.recent_invoices || cardVisibility.stock_history" class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          <!-- Recent Invoices Table -->
          <div v-if="cardVisibility.recent_invoices" class="lg:col-span-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
              <h3 class="text-sm font-bold text-zinc-950 dark:text-white uppercase tracking-wider">Recent Invoices & Transactions</h3>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="bg-zinc-100 dark:bg-zinc-800/80">
                  <tr>
                    <th class="px-6 py-3 text-left text-[11px] font-bold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Invoice / Ref #</th>
                    <th class="px-6 py-3 text-left text-[11px] font-bold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Party</th>
                    <th class="px-6 py-3 text-left text-[11px] font-bold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-[11px] font-bold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-[11px] font-bold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Status</th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-800">
                  <tr v-for="invoice in dashboardData.recent_invoices" :key="invoice.invoice_id" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-zinc-950 dark:text-white">
                      {{ invoice.invoice_id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold text-zinc-800 dark:text-zinc-200">
                      {{ invoice.customer }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-xs text-zinc-500 dark:text-zinc-400">
                      {{ formatDate(invoice.sales_date) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-zinc-950 dark:text-white">
                      {{ formatAmount(invoice.paid_amount) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="inline-flex px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-slate-900 text-white dark:bg-white dark:text-slate-900 uppercase tracking-wider">
                        {{ invoice.sales_status }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="!dashboardData.recent_invoices?.length">
                    <td colspan="5" class="text-center py-6 text-xs text-zinc-400 italic">No recent transactions found.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Stock History Card -->
          <div v-if="cardVisibility.stock_history" class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
              <h3 class="text-sm font-bold text-zinc-950 dark:text-white uppercase tracking-wider">Real Stock History</h3>
            </div>
            <div class="p-6 space-y-6">
              <!-- Total Sales Items -->
              <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                <div>
                  <p class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Sales Items Sold</p>
                  <p class="text-2xl font-bold text-zinc-950 dark:text-white mt-0.5 tracking-tight">{{ dashboardData.stock_history?.total_sales_items?.count || 0 }}</p>
                </div>
                <div class="flex items-center">
                  <span class="text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 px-2.5 py-1 rounded-full border border-emerald-200 dark:border-emerald-800">
                    ITEMS
                  </span>
                </div>
              </div>

              <!-- Total Purchase Items -->
              <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                <div>
                  <p class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Purchase Items Received</p>
                  <p class="text-2xl font-bold text-zinc-950 dark:text-white mt-0.5 tracking-tight">{{ dashboardData.stock_history?.total_purchase_items?.count || 0 }}</p>
                </div>
                <div class="flex items-center">
                  <span class="text-xs font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 px-2.5 py-1 rounded-full border border-blue-200 dark:border-blue-800">
                    ITEMS
                  </span>
                </div>
              </div>

              <!-- Total Return Items -->
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Returned Items</p>
                  <p class="text-2xl font-bold text-zinc-950 dark:text-white mt-0.5 tracking-tight">{{ dashboardData.stock_history?.total_return_items?.count || 0 }}</p>
                </div>
                <div class="flex items-center">
                  <span class="text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 px-2.5 py-1 rounded-full border border-amber-200 dark:border-amber-800">
                    ITEMS
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 6. Payment Trends and Low Stock Alert with Brand & Category Hierarchy -->
        <div v-if="cardVisibility.payment_telemetry || cardVisibility.stock_alerts" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <!-- Payment Trends Chart -->
          <div v-if="cardVisibility.payment_telemetry" class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
              <h3 class="text-sm font-bold text-zinc-950 dark:text-white uppercase tracking-wider">Payment Flow Telemetry</h3>
            </div>
            <div class="p-6">
              <div class="h-64">
                <canvas ref="paymentTrendsCanvas"></canvas>
              </div>
              <div class="flex items-center justify-center space-x-8 mt-6">
                <div class="flex items-center">
                  <div class="w-3 h-3 bg-black dark:bg-white rounded-full mr-2"></div>
                  <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Payment Out (Sent)</span>
                </div>
                <div class="flex items-center">
                  <div class="w-3 h-3 bg-zinc-400 dark:bg-zinc-500 rounded-full mr-2"></div>
                  <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Payment In (Received)</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Stock Alerts with Brand & Category Badges -->
          <div v-if="cardVisibility.stock_alerts" class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800">
              <h3 class="text-sm font-bold text-zinc-950 dark:text-white uppercase tracking-wider">Low Stock Threshold Alerts</h3>
            </div>
            <div class="p-6">
              <div class="space-y-3 max-h-64 overflow-y-auto custom-scrollbar">
                <div v-for="alert in dashboardData.stock_alerts" :key="alert.name" class="p-3 bg-zinc-50 dark:bg-zinc-950/60 rounded-xl border border-zinc-200/80 dark:border-zinc-800 space-y-2">
                  <div class="flex items-center justify-between">
                    <p class="text-xs font-bold text-zinc-900 dark:text-zinc-100">{{ alert.name }}</p>
                    <span class="text-xs font-bold bg-rose-600 text-white dark:bg-rose-500 dark:text-white px-2.5 py-0.5 rounded-lg shrink-0">
                      {{ alert.quantity }} LEFT
                    </span>
                  </div>
                  <!-- Brand Badge & Category Hierarchy -->
                  <div class="flex flex-wrap items-center gap-1.5 text-[10px]">
                    <span v-if="alert.brand_name" class="px-2 py-0.5 font-bold rounded-md bg-zinc-200 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700">
                      Brand: {{ alert.brand_name }}
                    </span>
                    <span class="px-2 py-0.5 font-bold rounded-md bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                      {{ alert.main_category }}
                    </span>
                    <span v-if="alert.sub_category" class="px-2 py-0.5 font-bold rounded-md bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                      {{ alert.sub_category }}
                    </span>
                    <span v-if="alert.child_category" class="px-2 py-0.5 font-bold rounded-md bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                      {{ alert.child_category }}
                    </span>
                  </div>
                </div>
                <div v-if="!dashboardData.stock_alerts?.length" class="text-center py-8 text-zinc-400 text-xs italic">
                  All inventory items are within healthy operational thresholds.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Customize Cards Modal / Slide-Over -->
    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showCustomizeModal" class="fixed inset-0 z-[200] bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden animate-in zoom-in-95">
          <!-- Modal Header -->
          <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50/50 dark:bg-zinc-950/50">
            <div>
              <h2 class="text-base font-bold text-zinc-950 dark:text-white">Customize Dashboard Layout</h2>
              <p class="text-xs text-zinc-500 dark:text-zinc-400">Select which cards and widgets to show or hide on your dashboard</p>
            </div>
            <button @click="showCustomizeModal = false" class="p-2 text-zinc-400 hover:text-zinc-700 dark:hover:text-white rounded-xl hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer">
              ✕
            </button>
          </div>

          <!-- Content Body -->
          <div class="p-6 overflow-y-auto space-y-6 custom-scrollbar">
            <!-- 1. Primary Stat Cards -->
            <div>
              <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-3">Primary Financial & Operational Stat Cards</h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label v-for="card in cardDefinitions.stats" :key="card.key" class="flex items-center justify-between p-3.5 bg-zinc-50 dark:bg-zinc-950/60 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800/60 transition-colors">
                  <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">{{ card.label }}</span>
                  <input type="checkbox" v-model="cardVisibility[card.key]" class="w-4 h-4 accent-slate-900 dark:accent-white rounded cursor-pointer" />
                </label>
              </div>
            </div>

            <!-- 2. Inventory Metrics -->
            <div>
              <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-3">Inventory Valuation & Intelligence</h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label v-for="card in cardDefinitions.inventory" :key="card.key" class="flex items-center justify-between p-3.5 bg-zinc-50 dark:bg-zinc-950/60 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800/60 transition-colors">
                  <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">{{ card.label }}</span>
                  <input type="checkbox" v-model="cardVisibility[card.key]" class="w-4 h-4 accent-slate-900 dark:accent-white rounded cursor-pointer" />
                </label>
              </div>
            </div>

            <!-- 3. Analytics & Charts -->
            <div>
              <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-3">Charts, History & Tables</h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label v-for="card in cardDefinitions.analytics" :key="card.key" class="flex items-center justify-between p-3.5 bg-zinc-50 dark:bg-zinc-950/60 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800/60 transition-colors">
                  <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">{{ card.label }}</span>
                  <input type="checkbox" v-model="cardVisibility[card.key]" class="w-4 h-4 accent-slate-900 dark:accent-white rounded cursor-pointer" />
                </label>
              </div>
            </div>
          </div>

          <!-- Footer Actions -->
          <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-950/50 flex items-center justify-between">
            <div class="flex gap-2">
              <button @click="showAllCards" class="px-3.5 py-1.5 text-xs font-bold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-800 rounded-xl transition-all cursor-pointer">
                Select All
              </button>
              <button @click="resetCardDefaults" class="px-3.5 py-1.5 text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-all cursor-pointer">
                Reset
              </button>
            </div>
            <button @click="showCustomizeModal = false" class="px-5 py-2 bg-slate-900 text-white dark:bg-white dark:text-slate-900 rounded-xl font-bold text-xs hover:opacity-90 transition-all cursor-pointer shadow-xs">
              Apply & Close
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick, watch } from 'vue';
import axios from 'axios';
import { onClickOutside } from '@vueuse/core';
import SalesPurchasesChart from '@/components/charts/SalesPurchasesChart.vue';
import DevicesPieChart from '@/components/charts/DevicesPieChart.vue';
import { useCurrencyStore } from '@/stores/currency';

const currencyStore = useCurrencyStore();

import {
  Chart,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  LineController
} from 'chart.js';

Chart.register(CategoryScale, LinearScale, PointElement, LineElement, LineController, Title, Tooltip, Legend);

// Card Visibility State
const showCustomizeModal = ref(false);

const defaultCardVisibility = {
  total_sales: true,
  sale_returns: true,
  total_purchases: true,
  purchase_returns: true,
  total_expenses: true,
  payments_in: true,
  payments_out: true,
  pending_payments: true,
  inventory_valuation_cost: true,
  inventory_valuation_retail: true,
  potential_profit: true,
  fast_moving: true,
  slow_moving: true,
  expiring_soon: true,
  sales_purchases_chart: true,
  financial_distribution: true,
  recent_invoices: true,
  stock_history: true,
  payment_telemetry: true,
  stock_alerts: true,
};

const cardVisibility = ref({ ...defaultCardVisibility });

const cardDefinitions = {
  stats: [
    { key: 'total_sales', label: 'Real Sales Card' },
    { key: 'sale_returns', label: 'Sale Returns Card' },
    { key: 'total_purchases', label: 'Purchase Orders Card' },
    { key: 'purchase_returns', label: 'Purchase Returns Card' },
    { key: 'total_expenses', label: 'Total Expenses Card' },
    { key: 'payments_in', label: 'Payments In Card' },
    { key: 'payments_out', label: 'Payments Out Card' },
    { key: 'pending_payments', label: 'Pending Payments Card' },
  ],
  inventory: [
    { key: 'inventory_valuation_cost', label: 'Inventory Cost Value' },
    { key: 'inventory_valuation_retail', label: 'Inventory Retail Value' },
    { key: 'potential_profit', label: 'Potential Profit Margin' },
    { key: 'fast_moving', label: 'Fast-Moving Items Widget' },
    { key: 'slow_moving', label: 'Slow-Moving Items Widget' },
    { key: 'expiring_soon', label: 'Expiring Soon Alerts Widget' },
  ],
  analytics: [
    { key: 'sales_purchases_chart', label: 'Sales vs Purchases Chart' },
    { key: 'financial_distribution', label: 'Sales / Returns / Purchases Circle Chart' },
    { key: 'recent_invoices', label: 'Recent Invoices Table' },
    { key: 'stock_history', label: 'Real Stock History Card' },
    { key: 'payment_telemetry', label: 'Payment Flow Telemetry Chart' },
    { key: 'stock_alerts', label: 'Low Stock Alerts Widget' },
  ]
};

const loadCardVisibility = () => {
  try {
    const saved = localStorage.getItem('pos_dashboard_card_visibility');
    if (saved) {
      cardVisibility.value = { ...defaultCardVisibility, ...JSON.parse(saved) };
    }
  } catch (e) {
    console.error('Error loading dashboard visibility settings:', e);
  }
};

watch(cardVisibility, (newVal) => {
  try {
    localStorage.setItem('pos_dashboard_card_visibility', JSON.stringify(newVal));
  } catch (e) {}
}, { deep: true });

const showAllCards = () => {
  Object.keys(cardVisibility.value).forEach(k => {
    cardVisibility.value[k] = true;
  });
};

const resetCardDefaults = () => {
  cardVisibility.value = { ...defaultCardVisibility };
};

// Reactive data
const loading = ref(false);
const dateRange = ref({
  from: '',
  to: ''
});
const showPicker = ref(false);
const pickerRef = ref(null);
const subPickerOpen = ref(null);

const tempRange = ref({
  from: dateRange.value.from,
  to: dateRange.value.to
});

const calendarYear = ref(new Date().getFullYear());
const calendarMonth = ref(new Date().getMonth());
const hoverDate = ref(null);

const monthNames = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December'
];

const presets = [
  { id: 'today', label: 'Today' },
  { id: 'yesterday', label: 'Yesterday' },
  { id: 'this_week', label: 'This Week' },
  { id: 'this_month', label: 'This Month' },
  { id: 'this_year', label: 'This Year' }
];

const toggleSubPicker = (type) => {
  subPickerOpen.value = subPickerOpen.value === type ? null : type;
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

const isDateStart = (dateStr) => tempRange.value.from === dateStr;
const isDateEnd = (dateStr) => tempRange.value.to === dateStr;

const isDateInRange = (dateStr) => {
  const from = tempRange.value.from;
  const to = tempRange.value.to || (from && hoverDate.value && hoverDate.value >= from ? hoverDate.value : null);
  if (!from || !to) return false;
  return dateStr >= from && dateStr <= to;
};

const handleDateClick = (dateStr) => {
  const from = tempRange.value.from;
  const to = tempRange.value.to;

  if (!from || (from && to)) {
    tempRange.value.from = dateStr;
    tempRange.value.to = '';
  } else if (from && !to) {
    if (dateStr < from) {
      tempRange.value.from = dateStr;
      tempRange.value.to = '';
    } else {
      tempRange.value.to = dateStr;
    }
  }
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
  } else if (preset === 'this_year') {
    const firstDay = new Date(now.getFullYear(), 0, 1);
    const lastDay = new Date(now.getFullYear(), 11, 31);
    from = format(firstDay);
    to = format(lastDay);
  }

  tempRange.value.from = from;
  tempRange.value.to = to;
};

const applyCurrentTempRange = () => {
  dateRange.value.from = tempRange.value.from;
  dateRange.value.to = tempRange.value.to;
  showPicker.value = false;
  loadDashboardData();
};

const clearDateRange = () => {
  tempRange.value.from = '';
  tempRange.value.to = '';
  dateRange.value.from = '';
  dateRange.value.to = '';
  showPicker.value = false;
  loadDashboardData();
};

onClickOutside(pickerRef, () => {
  showPicker.value = false;
});

const formattedDateRangeLabel = computed(() => {
  if (!dateRange.value.from || !dateRange.value.to) return 'All Time';
  const [fromYear, fromMonth, fromDay] = dateRange.value.from.split('-').map(Number);
  const [toYear, toMonth, toDay] = dateRange.value.to.split('-').map(Number);
  
  const fromDate = new Date(fromYear, fromMonth - 1, fromDay);
  const toDate = new Date(toYear, toMonth - 1, toDay);
  
  const options = { month: 'short', day: 'numeric', year: 'numeric' };
  
  if (dateRange.value.from === dateRange.value.to) {
    return fromDate.toLocaleDateString('en-US', options);
  }
  
  return `${fromDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${toDate.toLocaleDateString('en-US', options)}`;
});

const paymentTrendsCanvas = ref(null);
let paymentTrendsChart = null;

const dashboardData = ref({
  sales: { total_amount: 0, count: 0, average_sale: 0 },
  returns: { total_amount: 0, count: 0 },
  purchases: { total_amount: 0, count: 0 },
  purchase_returns: { total_amount: 0, count: 0 },
  expenses: { total_amount: 0, count: 0 },
  payments: {
    total_payments: 0,
    total_amount: 0,
    pending_payments: 0,
    pending_amount: 0,
    payment_sent: { total_amount: 0, change_percentage: 0 },
    payment_received: { total_amount: 0, change_percentage: 0 }
  },
  low_stock: { count: 0 },
  sales_trend: [],
  sales_purchases_chart: [],
  financial_distribution: [],
  recent_invoices: [],
  stock_history: {},
  payment_trends: [],
  stock_alerts: [],
  inventory_valuation: { total_cost_value: 0, total_retail_value: 0, potential_profit: 0 },
  product_intelligence: { fast_moving: [], slow_moving: [] },
  expiry_alerts: { count: 0, items: [] }
});

const selectedChartPeriod = ref('6_months');

const handleChartPeriodChange = (newPeriod) => {
  selectedChartPeriod.value = newPeriod;
  loadDashboardData();
};

const loadDashboardData = async () => {
  loading.value = true;
  try {
    const params = {
      chart_period: selectedChartPeriod.value
    };
    if (dateRange.value.from) params.date_from = dateRange.value.from;
    if (dateRange.value.to) params.date_to = dateRange.value.to;

    const response = await axios.get('/api/dashboard/statistics', { params });
    dashboardData.value = response.data;

    nextTick(() => {
      createPaymentTrendsChart();
    });
  } catch (error) {
    console.error('Error loading dashboard data:', error);
  } finally {
    loading.value = false;
  }
};

const formatAmount = (amount) => {
  return currencyStore.formatPrice(amount || 0);
};

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
};

const createPaymentTrendsChart = () => {
  if (!paymentTrendsCanvas.value || !dashboardData.value.payment_trends?.length) return;

  const ctx = paymentTrendsCanvas.value.getContext('2d');
  const isDark = document.documentElement.classList.contains('dark');

  if (paymentTrendsChart) {
    paymentTrendsChart.destroy();
  }

  const labels = dashboardData.value.payment_trends.map(item => item.date);
  const paymentSentData = dashboardData.value.payment_trends.map(item => item.payment_sent);
  const paymentReceivedData = dashboardData.value.payment_trends.map(item => item.payment_received);

  const mainColor = isDark ? '#ffffff' : '#000000';
  const secondaryColor = isDark ? '#a1a1aa' : '#71717a';

  paymentTrendsChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Payment Out (Sent)',
          data: paymentSentData,
          borderColor: mainColor,
          backgroundColor: isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)',
          borderWidth: 2.5,
          fill: true,
          tension: 0.35,
          pointBackgroundColor: mainColor,
          pointBorderColor: isDark ? '#09090b' : '#ffffff',
          pointBorderWidth: 2,
          pointRadius: 4
        },
        {
          label: 'Payment In (Received)',
          data: paymentReceivedData,
          borderColor: secondaryColor,
          backgroundColor: 'transparent',
          borderWidth: 2,
          borderDash: [4, 4],
          fill: false,
          tension: 0.35,
          pointBackgroundColor: secondaryColor,
          pointBorderColor: isDark ? '#09090b' : '#ffffff',
          pointBorderWidth: 2,
          pointRadius: 4
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          mode: 'index',
          intersect: false,
          backgroundColor: isDark ? '#18181b' : '#000000',
          titleColor: '#ffffff',
          bodyColor: '#ffffff',
          borderColor: isDark ? '#27272a' : '#27272a',
          borderWidth: 1,
          cornerRadius: 10,
          padding: 12,
          callbacks: {
            label: function(context) {
              return `${context.dataset.label}: ${currencyStore.formatPrice(context.parsed.y)}`;
            }
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: {
            color: isDark ? '#a1a1aa' : '#71717a',
            font: { size: 11, weight: 'bold' }
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            color: isDark ? '#27272a' : '#e4e4e7',
            borderDash: [3, 3]
          },
          ticks: {
            color: isDark ? '#a1a1aa' : '#71717a',
            font: { size: 11 },
            callback: function(value) {
              return (currencyStore.activeCurrency?.symbol || '$') + value.toLocaleString();
            }
          }
        }
      }
    }
  });
};

onMounted(() => {
  loadCardVisibility();
  loadDashboardData();
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
  border-radius: 9999px;
}
</style>
