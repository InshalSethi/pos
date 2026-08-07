<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
      <!-- Header Bar -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-extrabold text-zinc-950 dark:text-white tracking-tight">Dashboard</h1>
          <p class="text-xs text-zinc-500 dark:text-zinc-400 font-medium mt-1">Real-time overview of performance and key metrics</p>
        </div>

        <!-- Date Range Filter Container -->
        <div class="flex flex-col gap-1 w-full md:w-auto">
          <!-- Top Row with Label and Top Presets -->
          <div class="flex items-center justify-between px-0.5">
            <span class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Date Range</span>
            <div class="flex gap-1.5">
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
          <div ref="pickerRef" class="relative">
            <button 
              @click="showPicker = !showPicker" 
              class="w-full md:w-80 flex items-center justify-between bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-3.5 py-2 text-xs font-bold text-zinc-800 dark:text-white shadow-sm hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-all cursor-pointer focus:outline-none"
            >
              <span class="truncate pr-2" :class="{ 'text-zinc-400 dark:text-zinc-500': !formattedDateRangeLabel }">
                {{ formattedDateRangeLabel || 'Select Date Range' }}
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
                    class="px-2 py-1 text-[10px] font-bold bg-zinc-100 hover:bg-zinc-205 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 rounded-lg transition-colors cursor-pointer"
                  >
                    {{ p.label }}
                  </button>
                  <button
                    type="button"
                    @click="clearDateRange"
                    class="px-2 py-1 text-[10px] font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors ml-auto cursor-pointer"
                  >
                    Clear
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
                    <!-- Month Selector Button -->
                    <button
                      type="button"
                      @click.stop="toggleSubPicker('month')"
                      class="px-2.5 py-1 font-bold text-zinc-800 dark:text-zinc-100 text-xs hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-xl transition-colors flex items-center gap-1 cursor-pointer"
                      :class="{ 'bg-zinc-100 dark:bg-zinc-800 text-blue-600 dark:text-blue-400': subPickerOpen === 'month' }"
                    >
                      <span>{{ monthNames[calendarMonth] }}</span>
                      <span class="text-[8px] text-zinc-400">▲</span>
                    </button>

                    <!-- Year Selector Button -->
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

                  <!-- Month dropdown list popup -->
                  <div
                    v-if="subPickerOpen === 'month'"
                    class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 z-[110] bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-xl p-2 w-56 grid grid-cols-3 gap-1 animate-in fade-in zoom-in-95 duration-100"
                  >
                    <button
                      v-for="(mName, mIdx) in shortMonthNames"
                      :key="mIdx"
                      type="button"
                      @click.stop="selectMonth(mIdx)"
                      class="py-1.5 text-xs font-semibold rounded-xl text-center transition-colors cursor-pointer"
                      :class="calendarMonth === mIdx ? 'bg-black text-white dark:bg-white dark:text-black font-bold shadow-sm' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-200'"
                    >
                      {{ mName }}
                    </button>
                  </div>

                  <!-- Year dropdown list popup -->
                  <div
                    v-if="subPickerOpen === 'year'"
                    class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 z-[110] bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-xl p-2 w-52 max-h-48 overflow-y-auto grid grid-cols-3 gap-1 animate-in fade-in zoom-in-95 duration-100 custom-scrollbar"
                  >
                    <button
                      v-for="y in availableYears"
                      :key="y"
                      type="button"
                      @click.stop="selectYear(y)"
                      class="py-1.5 text-xs font-semibold rounded-xl text-center transition-colors cursor-pointer"
                      :class="calendarYear === y ? 'bg-black text-white dark:bg-white dark:text-black font-bold shadow-sm' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-200'"
                    >
                      {{ y }}
                    </button>
                  </div>
                </div>

                <!-- Calendar Week Header -->
                <div class="grid grid-cols-7 text-center text-[10px] font-bold text-zinc-400 dark:text-zinc-500 mb-1">
                  <span>Su</span>
                  <span>Mo</span>
                  <span>Tu</span>
                  <span>We</span>
                  <span>Th</span>
                  <span>Fr</span>
                  <span>Sa</span>
                </div>

                <!-- Days Grid -->
                <div class="grid grid-cols-7 gap-y-1 text-center" @mouseleave="hoverDate = null">
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
                      class="absolute inset-y-0.5 inset-x-0 bg-blue-50 dark:bg-blue-900/20"
                      :class="{
                        'rounded-l-lg': isDateStart(day.dateStr),
                        'rounded-r-lg': isDateEnd(day.dateStr) || isDateHoverEnd(day.dateStr)
                      }"
                    ></div>

                    <!-- Day Number Button -->
                    <button
                      type="button"
                      class="relative z-10 w-7 h-7 text-xs flex items-center justify-center rounded-xl font-bold transition-all cursor-pointer"
                      :class="{
                        'opacity-30': !day.isCurrentMonth,
                        'bg-black text-white dark:bg-white dark:text-black font-bold shadow-sm': isDateStart(day.dateStr) || isDateEnd(day.dateStr),
                        'text-blue-600 dark:text-blue-400 font-bold': isDateInRange(day.dateStr) && !isDateStart(day.dateStr) && !isDateEnd(day.dateStr),
                        'hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-200': !isDateStart(day.dateStr) && !isDateEnd(day.dateStr) && !isDateInRange(day.dateStr),
                        'border border-zinc-300 dark:border-zinc-700': isToday(day.dateStr) && !isDateStart(day.dateStr) && !isDateEnd(day.dateStr)
                      }"
                    >
                      {{ day.dayNum }}
                    </button>
                  </div>
                </div>

                <!-- Footer details and Done button -->
                <div class="mt-2 pt-2 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-[10px] text-zinc-500 dark:text-zinc-400">
                  <div>
                    <span v-if="tempRange.from && !tempRange.to" class="text-blue-600 dark:text-blue-400 font-medium animate-pulse">
                      Select end date...
                    </span>
                    <span v-else-if="tempRange.from && tempRange.to" class="font-bold text-zinc-700 dark:text-zinc-250">
                      Range selected
                    </span>
                    <span v-else class="italic text-zinc-400">
                      Click start date
                    </span>
                  </div>
                  <button
                    type="button"
                    @click="applyCurrentTempRange"
                    class="px-3 py-1.5 font-bold text-white bg-black dark:bg-white dark:text-black rounded-xl hover:opacity-90 transition-all text-[10px] cursor-pointer shadow-sm"
                  >
                    Done
                  </button>
                </div>
              </div>
            </transition>
          </div>
        </div>
      </div>

      <!-- Skeleton Loading State -->
      <div v-if="loading" class="space-y-8 animate-pulse">
        <!-- 4 Stat Cards Skeleton -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div v-for="i in 4" :key="'stat-skel-' + i" class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm">
            <div class="flex items-center justify-between">
              <div class="w-11 h-11 bg-zinc-200 dark:bg-zinc-800 rounded-xl"></div>
              <div class="w-16 h-6 bg-zinc-200 dark:bg-zinc-800 rounded-full"></div>
            </div>
            <div class="mt-4 space-y-2">
              <div class="w-24 h-3 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
              <div class="w-36 h-7 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
              <div class="w-28 h-3 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
            </div>
          </div>
        </div>

        <!-- 3 Inventory Overview Cards Skeleton -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div v-for="i in 3" :key="'inv-skel-' + i" class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm">
            <div class="flex items-center justify-between">
              <div class="space-y-2">
                <div class="w-32 h-3 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
                <div class="w-40 h-8 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
              </div>
              <div class="w-12 h-12 bg-zinc-200 dark:bg-zinc-800 rounded-xl"></div>
            </div>
          </div>
        </div>

        <!-- 2 Analytics Chart Cards Skeleton -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-2 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm space-y-6">
            <div class="flex items-center justify-between">
              <div class="w-40 h-5 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
              <div class="w-24 h-8 bg-zinc-200 dark:bg-zinc-800 rounded-xl"></div>
            </div>
            <div class="h-64 bg-zinc-100 dark:bg-zinc-800/50 rounded-xl flex items-end justify-between p-4 gap-2">
              <div v-for="bar in 12" :key="'bar-' + bar" class="w-full bg-zinc-200 dark:bg-zinc-800 rounded-t" :style="{ height: (20 + (bar * 7) % 70) + '%' }"></div>
            </div>
          </div>

          <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm space-y-6">
            <div class="w-36 h-5 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
            <div class="flex justify-center py-4">
              <div class="w-44 h-44 rounded-full border-8 border-zinc-200 dark:border-zinc-800"></div>
            </div>
            <div class="space-y-2">
              <div v-for="leg in 4" :key="'leg-' + leg" class="flex justify-between items-center">
                <div class="w-20 h-3 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
                <div class="w-10 h-3 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Table & Stock History Skeleton -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-2 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm space-y-4">
            <div class="flex justify-between items-center">
              <div class="w-36 h-5 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
              <div class="w-28 h-8 bg-zinc-200 dark:bg-zinc-800 rounded-xl"></div>
            </div>
            <div class="space-y-3 pt-2">
              <div v-for="row in 4" :key="'tbl-row-' + row" class="h-12 bg-zinc-100 dark:bg-zinc-800/40 rounded-xl flex items-center justify-between px-4">
                <div class="w-24 h-4 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
                <div class="w-20 h-4 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
                <div class="w-16 h-4 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
                <div class="w-16 h-6 bg-zinc-200 dark:bg-zinc-800 rounded-full"></div>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm space-y-6">
            <div class="flex justify-between items-center">
              <div class="w-32 h-5 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
              <div class="w-20 h-8 bg-zinc-200 dark:bg-zinc-800 rounded-xl"></div>
            </div>
            <div class="space-y-4">
              <div class="w-28 h-4 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
              <div class="w-20 h-8 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
              <div class="w-full h-24 bg-zinc-100 dark:bg-zinc-800/40 rounded-xl"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Dashboard Stats -->
      <div v-else>
        <!-- Primary Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <!-- Total Sales Card -->
          <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div class="w-full">
                  <div class="flex items-center justify-between">
                    <div class="w-11 h-11 bg-zinc-100 text-black dark:bg-zinc-800 dark:text-white rounded-xl flex items-center justify-center shadow-sm">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                      </svg>
                    </div>
                    <span class="text-[11px] font-extrabold bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100 px-2.5 py-1 rounded-full border border-zinc-200 dark:border-zinc-700">+2.5%</span>
                  </div>
                  <div class="mt-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Total Sales</p>
                    <p class="text-2xl font-black text-zinc-950 dark:text-white mt-0.5 tracking-tight">{{ formatAmount(dashboardData.sales?.total_amount || 0) }}</p>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1">Vs previous period</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Total Expenses Card -->
          <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div class="w-full">
                  <div class="flex items-center justify-between">
                    <div class="w-11 h-11 bg-zinc-100 text-black dark:bg-zinc-800 dark:text-white rounded-xl flex items-center justify-center shadow-sm">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                      </svg>
                    </div>
                    <span class="text-[11px] font-extrabold bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100 px-2.5 py-1 rounded-full border border-zinc-200 dark:border-zinc-700">-2.1%</span>
                  </div>
                  <div class="mt-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Total Expenses</p>
                    <p class="text-2xl font-black text-zinc-950 dark:text-white mt-0.5 tracking-tight">{{ formatAmount(dashboardData.expenses?.total_amount || 0) }}</p>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1">Vs previous period</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Total Payments Card -->
          <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div class="w-full">
                  <div class="flex items-center justify-between">
                    <div class="w-11 h-11 bg-zinc-100 text-black dark:bg-zinc-800 dark:text-white rounded-xl flex items-center justify-center shadow-sm">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                      </svg>
                    </div>
                    <span class="text-[11px] font-extrabold bg-black text-white dark:bg-white dark:text-black px-2.5 py-1 rounded-full">
                      {{ dashboardData.payments?.total_payments || 0 }} TXNS
                    </span>
                  </div>
                  <div class="mt-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Total Payments</p>
                    <p class="text-2xl font-black text-zinc-950 dark:text-white mt-0.5 tracking-tight">{{ formatAmount(dashboardData.payments?.total_amount || 0) }}</p>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1">
                      {{ formatPercentage(dashboardData.payments?.payment_sent?.change_percentage || 0) }}% change
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Pending Payments Card -->
          <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div class="w-full">
                  <div class="flex items-center justify-between">
                    <div class="w-11 h-11 bg-zinc-100 text-black dark:bg-zinc-800 dark:text-white rounded-xl flex items-center justify-center shadow-sm">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </div>
                    <span class="text-[11px] font-extrabold bg-zinc-200 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100 px-2.5 py-1 rounded-full border border-zinc-300 dark:border-zinc-700">
                      {{ dashboardData.payments?.pending_payments || 0 }} PENDING
                    </span>
                  </div>
                  <div class="mt-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Pending Payments</p>
                    <p class="text-2xl font-black text-zinc-950 dark:text-white mt-0.5 tracking-tight">{{ formatAmount(dashboardData.payments?.pending_amount || 0) }}</p>
                    <div class="mt-2">
                      <router-link
                        to="/payments?status=pending"
                        class="inline-flex items-center text-xs text-black dark:text-white font-extrabold hover:underline group"
                      >
                        View Pending
                        <svg class="w-3.5 h-3.5 ml-1 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                      </router-link>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Inventory Valuation Cards (Clean Light Cards in Light Mode, Dark Obsidian in Dark Mode) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <!-- Total Cost Value -->
          <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Total Inventory Value (Cost)</p>
                <p class="text-3xl font-black text-zinc-950 dark:text-white mt-2 tracking-tight">{{ formatAmount(dashboardData.inventory_valuation?.total_cost_value || 0) }}</p>
              </div>
              <div class="w-12 h-12 bg-zinc-100 text-black dark:bg-zinc-800 dark:text-white rounded-xl flex items-center justify-center shadow-sm shrink-0 ml-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
              </div>
            </div>
          </div>

          <!-- Total Retail Value -->
          <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Total Inventory Value (Retail)</p>
                <p class="text-3xl font-black text-zinc-950 dark:text-white mt-2 tracking-tight">{{ formatAmount(dashboardData.inventory_valuation?.total_retail_value || 0) }}</p>
              </div>
              <div class="w-12 h-12 bg-zinc-100 text-black dark:bg-zinc-800 dark:text-white rounded-xl flex items-center justify-center shadow-sm shrink-0 ml-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path></svg>
              </div>
            </div>
          </div>

          <!-- Potential Profit -->
          <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm hover:shadow-md transition-all rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Potential Profit</p>
                <p class="text-3xl font-black text-zinc-950 dark:text-white mt-2 tracking-tight">{{ formatAmount(dashboardData.inventory_valuation?.potential_profit || 0) }}</p>
              </div>
              <div class="w-12 h-12 bg-zinc-100 text-black dark:bg-zinc-800 dark:text-white rounded-xl flex items-center justify-center shadow-sm shrink-0 ml-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Product Intelligence & Expiry alerts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          <!-- Fast Moving Items -->
          <div class="bg-white dark:bg-zinc-900 shadow-sm rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800">
            <div class="px-6 py-4 bg-zinc-100 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
              <h3 class="font-extrabold text-zinc-900 dark:text-white uppercase tracking-wider text-xs flex items-center">
                <svg class="w-4 h-4 mr-2 text-black dark:text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                Fast-Moving Items
              </h3>
              <span class="text-[10px] font-extrabold bg-black text-white dark:bg-white dark:text-black px-2.5 py-0.5 rounded-full">TOP 5</span>
            </div>
            <div class="p-4 space-y-2.5">
              <div v-for="item in dashboardData.product_intelligence?.fast_moving" :key="item.name" class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-950/60 rounded-xl border border-zinc-200/80 dark:border-zinc-800">
                <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 truncate mr-2">{{ item.name }}</span>
                <span class="text-[11px] font-black text-white bg-black dark:bg-white dark:text-black px-2.5 py-1 rounded-lg shadow-xs">{{ item.total_sold }} sold</span>
              </div>
              <div v-if="!dashboardData.product_intelligence?.fast_moving?.length" class="text-center py-6 text-zinc-400 text-xs italic">
                No sales data available.
              </div>
            </div>
          </div>

          <!-- Slow Moving Items -->
          <div class="bg-white dark:bg-zinc-900 shadow-sm rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800">
            <div class="px-6 py-4 bg-zinc-100 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
              <h3 class="font-extrabold text-zinc-900 dark:text-white uppercase tracking-wider text-xs flex items-center">
                <svg class="w-4 h-4 mr-2 text-black dark:text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V7h2v10z"></path></svg>
                Slow-Moving Items
              </h3>
              <span class="text-[10px] font-extrabold bg-black text-white dark:bg-white dark:text-black px-2.5 py-0.5 rounded-full">IN STOCK</span>
            </div>
            <div class="p-4 space-y-2.5">
              <div v-for="item in dashboardData.product_intelligence?.slow_moving" :key="item.name" class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-950/60 rounded-xl border border-zinc-200/80 dark:border-zinc-800">
                <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 truncate mr-2">{{ item.name }}</span>
                <span class="text-[11px] font-black text-black bg-zinc-200 dark:bg-zinc-800 dark:text-white px-2.5 py-1 rounded-lg border border-zinc-300 dark:border-zinc-700">{{ item.stock_quantity }} left</span>
              </div>
              <div v-if="!dashboardData.product_intelligence?.slow_moving?.length" class="text-center py-6 text-zinc-400 text-xs italic">
                No slow moving items found.
              </div>
            </div>
          </div>

          <!-- Expiry Management -->
          <div class="bg-white dark:bg-zinc-900 shadow-sm rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800">
            <div class="px-6 py-4 bg-zinc-100 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
              <h3 class="font-extrabold text-zinc-900 dark:text-white uppercase tracking-wider text-xs flex items-center">
                <svg class="w-4 h-4 mr-2 text-black dark:text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V7h2v10z"></path></svg>
                Expiring Soon
              </h3>
              <span class="text-[10px] font-extrabold bg-black text-white dark:bg-white dark:text-black px-2.5 py-0.5 rounded-full">{{ dashboardData.expiry_alerts?.count || 0 }} ALERTS</span>
            </div>
            <div class="p-4 space-y-2.5">
              <div v-for="item in dashboardData.expiry_alerts?.items" :key="item.name" class="p-3 bg-zinc-50 dark:bg-zinc-950/60 rounded-xl border border-zinc-200/80 dark:border-zinc-800 flex flex-col">
                <div class="flex justify-between items-center mb-1">
                  <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 truncate mr-2">{{ item.name }}</span>
                  <span class="text-[10px] font-black px-2 py-0.5 rounded-full uppercase bg-black text-white dark:bg-white dark:text-black">{{ item.status }}</span>
                </div>
                <div class="flex justify-between text-[11px] font-medium text-zinc-500 italic">
                  <span>Expires: {{ item.expiry_date }}</span>
                  <span class="text-black dark:text-white font-extrabold">{{ item.days_to_expire < 0 ? 'Expired' : item.days_to_expire + ' days' }}</span>
                </div>
              </div>
              <div v-if="!dashboardData.expiry_alerts?.items?.length" class="text-center py-6 text-zinc-400 text-xs italic">
                No items nearing expiry.
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <!-- Sales & Purchases Chart -->
          <SalesPurchasesChart :data="dashboardData.sales_purchases_chart || []" />

          <!-- Devices Breakdown Chart -->
          <DevicesPieChart :data="dashboardData.devices_breakdown || []" />
        </div>

        <!-- Recent Invoices and Stock History -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          <!-- Recent Invoices Table -->
          <div class="lg:col-span-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-sm rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
              <h3 class="text-sm font-extrabold text-zinc-950 dark:text-white uppercase tracking-wider">Recent Invoices</h3>
              <div class="flex items-center space-x-2">
                <div class="relative group">
                  <select class="pl-3 pr-8 py-1.5 bg-zinc-100 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-xl focus:border-slate-300 focus:ring-2 focus:ring-slate-100 outline-none dark:focus:border-zinc-600 transition-all font-bold text-xs text-zinc-900 dark:text-white appearance-none cursor-pointer uppercase tracking-tight">
                    <option>Sales Invoice</option>
                    <option>Purchase Invoice</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none">
                    <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/></svg>
                  </div>
                </div>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="bg-zinc-100 dark:bg-zinc-800/80">
                  <tr>
                    <th class="px-6 py-3 text-left text-[11px] font-extrabold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Invoice ID</th>
                    <th class="px-6 py-3 text-left text-[11px] font-extrabold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-[11px] font-extrabold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Sales Date</th>
                    <th class="px-6 py-3 text-left text-[11px] font-extrabold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Paid Amount</th>
                    <th class="px-6 py-3 text-left text-[11px] font-extrabold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">Sales Status</th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-800">
                  <tr v-for="invoice in dashboardData.recent_invoices" :key="invoice.invoice_id" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-xs font-extrabold text-zinc-950 dark:text-white">
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
                      <span class="inline-flex px-2.5 py-0.5 text-[10px] font-extrabold rounded-full bg-black text-white dark:bg-white dark:text-black uppercase tracking-wider">
                        {{ invoice.sales_status }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Stock History Card -->
          <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-sm rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
              <h3 class="text-sm font-extrabold text-zinc-950 dark:text-white uppercase tracking-wider">Stock History</h3>
              <div class="relative group">
                <select class="pl-3 pr-8 py-1.5 bg-zinc-100 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-xl focus:border-slate-300 focus:ring-2 focus:ring-slate-100 outline-none dark:focus:border-zinc-600 transition-all font-bold text-xs text-zinc-900 dark:text-white appearance-none cursor-pointer uppercase tracking-tight">
                  <option>7 Days</option>
                  <option>30 Days</option>
                  <option>90 Days</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none">
                  <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/></svg>
                </div>
              </div>
            </div>
            <div class="p-6 space-y-6">
              <!-- Total Sales Items -->
              <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                <div>
                  <p class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Total Sales Items</p>
                  <p class="text-2xl font-black text-zinc-950 dark:text-white mt-0.5 tracking-tight">{{ dashboardData.stock_history?.total_sales_items?.count || 0 }}</p>
                </div>
                <div class="flex items-center">
                  <span class="text-xs font-extrabold bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100 px-2.5 py-1 rounded-full border border-zinc-300 dark:border-zinc-700">
                    {{ formatPercentage(dashboardData.stock_history?.total_sales_items?.change_percentage || 0) }}%
                  </span>
                </div>
              </div>

              <!-- Total Purchase Items -->
              <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                <div>
                  <p class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Purchase Items</p>
                  <p class="text-2xl font-black text-zinc-950 dark:text-white mt-0.5 tracking-tight">{{ dashboardData.stock_history?.total_purchase_items?.count || 0 }}</p>
                </div>
                <div class="flex items-center">
                  <span class="text-xs font-extrabold bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100 px-2.5 py-1 rounded-full border border-zinc-300 dark:border-zinc-700">
                    {{ formatPercentage(dashboardData.stock_history?.total_purchase_items?.change_percentage || 0) }}%
                  </span>
                </div>
              </div>

              <!-- Total Return Items -->
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Purchase Returns Items</p>
                  <p class="text-2xl font-black text-zinc-950 dark:text-white mt-0.5 tracking-tight">{{ dashboardData.stock_history?.total_return_items?.count || 0 }}</p>
                </div>
                <div class="flex items-center">
                  <span class="text-xs font-extrabold bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100 px-2.5 py-1 rounded-full border border-zinc-300 dark:border-zinc-700">
                    {{ formatPercentage(dashboardData.stock_history?.total_return_items?.change_percentage || 0) }}%
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment Trends and Stock Alert -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Payment Trends Chart -->
          <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-sm rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
              <h3 class="text-sm font-extrabold text-zinc-950 dark:text-white uppercase tracking-wider">Payment Telemetry</h3>
              <div class="relative group">
                <select class="pl-3 pr-8 py-1.5 bg-zinc-100 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-xl focus:border-slate-300 focus:ring-2 focus:ring-slate-100 outline-none dark:focus:border-zinc-600 transition-all font-bold text-xs text-zinc-900 dark:text-white appearance-none cursor-pointer uppercase tracking-tight">
                  <option>15 Days</option>
                  <option>30 Days</option>
                  <option>90 Days</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none">
                  <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/></svg>
                </div>
              </div>
            </div>
            <div class="p-6">
              <div class="h-64">
                <canvas ref="paymentTrendsCanvas"></canvas>
              </div>
              <!-- Legend -->
              <div class="flex items-center justify-center space-x-8 mt-6">
                <div class="flex items-center">
                  <div class="w-3 h-3 bg-black dark:bg-white rounded-full mr-2"></div>
                  <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300">Payment Sent</span>
                </div>
                <div class="flex items-center">
                  <div class="w-3 h-3 bg-zinc-400 dark:bg-zinc-500 rounded-full mr-2"></div>
                  <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300">Payment Received</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Stock Alert -->
          <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-sm rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800">
              <h3 class="text-sm font-extrabold text-zinc-950 dark:text-white uppercase tracking-wider">Stock Alert Thresholds</h3>
            </div>
            <div class="p-6">
              <div class="space-y-3 max-h-64 overflow-y-auto custom-scrollbar">
                <div v-for="alert in dashboardData.stock_alerts" :key="alert.product" class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-950/60 rounded-xl border border-zinc-200/80 dark:border-zinc-800">
                  <div>
                    <p class="text-xs font-bold text-zinc-900 dark:text-zinc-100">{{ alert.product }}</p>
                  </div>
                  <div class="text-right">
                    <span class="text-xs font-black bg-black text-white dark:bg-white dark:text-black px-2.5 py-1 rounded-lg">
                      {{ alert.quantity }} LEFT
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

        <!-- Payment Breakdown Section -->
        <div v-if="dashboardData.payments?.by_type?.length > 0" class="mt-8">
          <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-sm rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
              <h3 class="text-sm font-extrabold text-zinc-950 dark:text-white uppercase tracking-wider">Payment Breakdown</h3>
              <router-link
                to="/payments"
                class="text-xs text-black dark:text-white font-extrabold hover:underline"
              >
                View All →
              </router-link>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- By Type -->
                <div>
                  <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-4">By Type</h4>
                  <div class="space-y-3">
                    <div v-for="type in dashboardData.payments.by_type" :key="type.payment_type" class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-950/60 rounded-xl border border-zinc-200/70 dark:border-zinc-800">
                      <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">{{ getPaymentTypeDisplay(type.payment_type) }}</span>
                      <div class="text-right">
                        <span class="text-xs font-extrabold text-zinc-950 dark:text-white">{{ formatAmount(type.total_amount) }}</span>
                        <span class="text-[10px] text-zinc-400 ml-1">({{ type.count }})</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- By Status -->
                <div>
                  <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-4">By Status</h4>
                  <div class="space-y-3">
                    <div v-for="status in dashboardData.payments.by_status" :key="status.status" class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-950/60 rounded-xl border border-zinc-200/70 dark:border-zinc-800">
                      <div class="flex items-center">
                        <span class="inline-flex px-2.5 py-0.5 text-[10px] font-extrabold rounded-full uppercase tracking-wider bg-black text-white dark:bg-white dark:text-black">
                          {{ status.status }}
                        </span>
                      </div>
                      <div class="text-right">
                        <span class="text-xs font-extrabold text-zinc-950 dark:text-white">{{ formatAmount(status.total_amount) }}</span>
                        <span class="text-[10px] text-zinc-400 ml-1">({{ status.count }})</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
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

// Register Chart.js components
Chart.register(CategoryScale, LinearScale, PointElement, LineElement, LineController, Title, Tooltip, Legend);

// Reactive data
const loading = ref(false);
const activePreset = ref('today');
const dateRange = ref({
  from: new Date().toISOString().split('T')[0],
  to: new Date().toISOString().split('T')[0]
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

const shortMonthNames = [
  'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
  'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
];

const presets = [
  { id: 'today', label: 'Today' },
  { id: 'yesterday', label: 'Yesterday' },
  { id: 'this_week', label: 'This Week' },
  { id: 'this_month', label: 'This Month' },
  { id: 'last_month', label: 'Last Month' },
  { id: 'this_year', label: 'This Year' },
  { id: 'last_year', label: 'Last Year' }
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
const isDateStart = (dateStr) => tempRange.value.from === dateStr;
const isDateEnd = (dateStr) => tempRange.value.to === dateStr;

const isDateHoverEnd = (dateStr) => {
  return !!(tempRange.value.from && !tempRange.value.to && hoverDate.value === dateStr);
};

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

  tempRange.value.from = from;
  tempRange.value.to = to;

  if (from) {
    const parts = from.split('-');
    if (parts.length === 3) {
      calendarYear.value = parseInt(parts[0]);
      calendarMonth.value = parseInt(parts[1]) - 1;
    }
  }
};

const applyCurrentTempRange = () => {
  dateRange.value.from = tempRange.value.from;
  dateRange.value.to = tempRange.value.to;
  activePreset.value = 'custom';
  showPicker.value = false;
  loadDashboardData();
};

const clearDateRange = () => {
  tempRange.value.from = '';
  tempRange.value.to = '';
  dateRange.value.from = '';
  dateRange.value.to = '';
  activePreset.value = 'custom';
  showPicker.value = false;
  loadDashboardData();
};

watch(dateRange, (newRange) => {
  tempRange.value.from = newRange.from;
  tempRange.value.to = newRange.to;
}, { deep: true });

watch(showPicker, (newVal) => {
  if (newVal) {
    tempRange.value.from = dateRange.value.from;
    tempRange.value.to = dateRange.value.to;
    subPickerOpen.value = null;
    if (dateRange.value.from) {
      const parts = dateRange.value.from.split('-');
      if (parts.length === 3) {
        calendarYear.value = parseInt(parts[0]);
        calendarMonth.value = parseInt(parts[1]) - 1;
      }
    }
  }
});

onClickOutside(pickerRef, () => {
  showPicker.value = false;
});

const formattedDateRangeLabel = computed(() => {
  if (!dateRange.value.from || !dateRange.value.to) return 'Select Date Range';
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
  sales: { total_amount: 0, count: 0 },
  returns: { total_amount: 0, count: 0 },
  purchases: { total_amount: 0, count: 0 },
  expenses: { total_amount: 0, count: 0 },
  payments: {
    payment_sent: { total_amount: 0, change_percentage: 0 },
    payment_received: { total_amount: 0, change_percentage: 0 }
  },
  low_stock: { count: 0 },
  sales_trend: [],
  sales_purchases_chart: [],
  devices_breakdown: [],
  recent_invoices: [],
  stock_history: {},
  payment_trends: [],
  stock_alerts: [],
  expense_categories: [],
  recent_transactions: []
});

// Computed properties
const maxSalesAmount = computed(() => {
  if (!dashboardData.value.sales_trend?.length) return 1;
  return Math.max(...dashboardData.value.sales_trend.map(day => day.amount));
});

// Methods
const loadDashboardData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/dashboard/statistics', {
      params: {
        date_from: dateRange.value.from,
        date_to: dateRange.value.to
      }
    });
    dashboardData.value = response.data;

    // Create payment trends chart after data is loaded
    nextTick(() => {
      createPaymentTrendsChart();
    });
  } catch (error) {
    console.error('Error loading dashboard data:', error);
  } finally {
    loading.value = false;
  }
};

const setToday = () => {
  activePreset.value = 'today';
  const today = new Date().toISOString().split('T')[0];
  dateRange.value.from = today;
  dateRange.value.to = today;
  loadDashboardData();
};

const setThisWeek = () => {
  activePreset.value = 'week';
  const today = new Date();
  const firstDayOfWeek = new Date(today.setDate(today.getDate() - today.getDay()));
  const lastDayOfWeek = new Date(today.setDate(today.getDate() - today.getDay() + 6));

  dateRange.value.from = firstDayOfWeek.toISOString().split('T')[0];
  dateRange.value.to = lastDayOfWeek.toISOString().split('T')[0];
  loadDashboardData();
};

const setThisMonth = () => {
  activePreset.value = 'month';
  const today = new Date();
  const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
  const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

  dateRange.value.from = firstDayOfMonth.toISOString().split('T')[0];
  dateRange.value.to = lastDayOfMonth.toISOString().split('T')[0];
  loadDashboardData();
};

const formatAmount = (amount) => {
  return currencyStore.formatPrice(amount || 0);
};

const getPaymentTypeDisplay = (type) => {
  const types = {
    supplier_payment: 'Supplier Payment',
    expense_payment: 'Expense Payment',
    salary_payment: 'Salary Payment',
    sale_return_payment: 'Sale Return Payment',
    purchase_invoice_payment: 'Purchase Invoice Payment',
    other_payment: 'Other Payment',
  };
  return types[type] || type;
};

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  });
};

const formatPercentage = (percentage) => {
  return Math.abs(percentage || 0).toFixed(1);
};

const createPaymentTrendsChart = () => {
  if (!paymentTrendsCanvas.value || !dashboardData.value.payment_trends?.length) return;

  const ctx = paymentTrendsCanvas.value.getContext('2d');
  const isDark = document.documentElement.classList.contains('dark');

  // Destroy existing chart if it exists
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
          label: 'Payment Sent',
          data: paymentSentData,
          borderColor: mainColor,
          backgroundColor: isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)',
          borderWidth: 2.5,
          fill: true,
          tension: 0.35,
          pointBackgroundColor: mainColor,
          pointBorderColor: isDark ? '#09090b' : '#ffffff',
          pointBorderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6
        },
        {
          label: 'Payment Received',
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
          pointRadius: 4,
          pointHoverRadius: 6
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
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
          grid: {
            display: false
          },
          ticks: {
            color: isDark ? '#a1a1aa' : '#71717a',
            font: {
              size: 11,
              weight: 'bold'
            }
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
            font: {
              size: 11
            },
            callback: function(value) {
              return (currencyStore.activeCurrency?.symbol || '$') + value.toLocaleString();
            }
          }
        }
      },
      interaction: {
        mode: 'index',
        intersect: false
      }
    }
  });
};

// Lifecycle
onMounted(() => {
  loadDashboardData();
});
</script>
