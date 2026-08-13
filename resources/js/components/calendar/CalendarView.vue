<template>
  <div class="space-y-5 max-w-full">
    <!-- Top Header Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
      <div class="flex items-center gap-3.5">
        <div class="p-2.5 bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 rounded-2xl shadow-sm shrink-0">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
        <div>
          <div class="flex items-center gap-2.5">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Full Calendar</h1>
            <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full border border-slate-200 dark:border-zinc-800 bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300">
              Synced Overview
            </span>
          </div>
          <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">
            Manage transactions, Google Calendar events, invoices, payments, and expenses in one view
          </p>
        </div>
      </div>

      <!-- Header Controls: Google Sync status, Prev/Today/Next & View Switcher -->
      <div class="flex flex-wrap items-center gap-3">
        <!-- Google Calendar Sync Status Pill Button -->
        <button
          @click="showGoogleSettingsModal = true"
          :class="[
            'px-3.5 py-2 rounded-xl border text-xs font-semibold flex items-center gap-2.5 transition-all shadow-xs cursor-pointer',
            googleSettings.is_synced 
              ? 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800/60 hover:bg-purple-100' 
              : 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-zinc-800 dark:text-slate-300 dark:border-zinc-700 hover:bg-slate-100'
          ]"
          title="Google Calendar Integration Settings"
        >
          <span class="relative flex h-2.5 w-2.5">
            <span v-if="googleSettings.is_synced" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75"></span>
            <span :class="['relative inline-flex rounded-full h-2.5 w-2.5', googleSettings.is_synced ? 'bg-purple-500' : 'bg-slate-400']"></span>
          </span>
          <div class="flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400 shrink-0" viewBox="0 0 24 24" fill="currentColor">
              <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zm-7 5h5v5h-5z"/>
            </svg>
            <span>Google Sync: <strong>{{ googleSettings.is_synced ? 'Active' : 'Disabled' }}</strong></span>
          </div>
        </button>

        <!-- Month Navigation Controls -->
        <div class="flex items-center bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-1 shadow-xs">
          <button
            @click="navigateMonth(-1)"
            class="p-1.5 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-all cursor-pointer"
            title="Previous Month"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <button
            @click="goToToday"
            class="px-3 py-1.5 text-xs font-bold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-all cursor-pointer"
          >
            Today
          </button>
          <button
            @click="navigateMonth(1)"
            class="p-1.5 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-all cursor-pointer"
            title="Next Month"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>

        <!-- View Mode Switcher -->
        <div class="flex items-center bg-slate-100 dark:bg-zinc-800/80 p-1 rounded-xl border border-slate-200/80 dark:border-zinc-700/80">
          <button
            v-for="vMode in ['month', 'week', 'list']"
            :key="vMode"
            @click="currentViewMode = vMode"
            :class="[
              'px-3 py-1.5 text-xs font-bold capitalize rounded-lg transition-all cursor-pointer',
              currentViewMode === vMode 
                ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 shadow-xs' 
                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
            ]"
          >
            {{ vMode }}
          </button>
        </div>
      </div>
    </div>

    <!-- Active Filters Bar & Summary Banner -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 p-3.5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
      <div class="flex items-center gap-2 overflow-x-auto custom-scrollbar pb-1 md:pb-0">
        <span class="text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider shrink-0 mr-1">Filter Events:</span>
        <button
          v-for="filter in eventTypeFilters"
          :key="filter.key"
          @click="toggleFilter(filter.key)"
          :class="[
            'px-2.5 py-1 rounded-lg text-xs font-semibold flex items-center gap-1.5 border transition-all shrink-0 cursor-pointer',
            activeFilters.includes(filter.key)
              ? filter.activeClass
              : 'bg-slate-50 text-slate-400 border-slate-200 dark:bg-zinc-800 dark:text-zinc-500 dark:border-zinc-700 opacity-60'
          ]"
        >
          <span :class="['w-2 h-2 rounded-full', filter.dotClass]"></span>
          <span>{{ filter.label }}</span>
        </button>
      </div>

      <!-- Current Period Label -->
      <div class="text-right shrink-0">
        <h2 class="text-lg font-extrabold text-slate-900 dark:text-white tracking-wide">
          {{ formattedMonthYear }}
        </h2>
      </div>
    </div>

    <!-- MAIN CALENDAR CONTAINER -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-sm overflow-hidden min-h-[650px] flex flex-col">
      <!-- Loading Skeleton Overlay -->
      <div v-if="loading" class="p-8 flex items-center justify-center space-x-3 text-slate-500 my-auto">
        <div class="w-6 h-6 border-2 border-slate-900 dark:border-white border-t-transparent rounded-full animate-spin"></div>
        <span class="text-xs font-bold uppercase tracking-wider">Loading Calendar Events...</span>
      </div>

      <!-- 1. MONTH VIEW -->
      <div v-else-if="currentViewMode === 'month'" class="flex-1 flex flex-col">
        <!-- Weekday Headers -->
        <div class="grid grid-cols-7 border-b border-slate-200 dark:border-zinc-800 bg-slate-50/70 dark:bg-zinc-900/70 text-center py-2.5 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">
          <div v-for="dayName in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="dayName" class="py-1">
            {{ dayName }}
          </div>
        </div>

        <!-- Month Grid Cells -->
        <div class="grid grid-cols-7 flex-1 auto-rows-fr divide-x divide-y divide-slate-100 dark:divide-zinc-800/80">
          <div
            v-for="(dayObj, index) in calendarDays"
            :key="index"
            @click="handleDateCellClick(dayObj)"
            :class="[
              'p-1.5 md:p-2 min-h-[100px] md:min-h-[120px] transition-all relative group flex flex-col justify-between cursor-pointer hover:bg-slate-50/80 dark:hover:bg-zinc-800/40',
              !dayObj.isCurrentMonth ? 'bg-slate-50/40 dark:bg-zinc-900/30 text-slate-300 dark:text-zinc-600' : 'bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100',
              dayObj.isToday ? 'bg-indigo-50/20 dark:bg-indigo-950/20 ring-2 ring-indigo-500/40 z-10' : ''
            ]"
          >
            <!-- Date Number & Quick Add Button -->
            <div class="flex items-center justify-between">
              <span
                :class="[
                  'text-xs font-bold w-6 h-6 flex items-center justify-center rounded-full',
                  dayObj.isToday 
                    ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-extrabold shadow-xs' 
                    : (!dayObj.isCurrentMonth ? 'text-slate-400 dark:text-zinc-600' : 'text-slate-700 dark:text-slate-200')
                ]"
              >
                {{ dayObj.dayNumber }}
              </span>

              <!-- Hover "+ Add" Pill -->
              <span 
                class="opacity-0 group-hover:opacity-100 transition-opacity px-1.5 py-0.5 text-[10px] font-bold rounded-md bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 shadow-2xs inline-flex items-center gap-0.5"
                title="Click to add transaction on this date"
              >
                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>Add</span>
              </span>
            </div>

            <!-- Event Chips List -->
            <div class="mt-1 space-y-1 overflow-hidden flex-1">
              <div
                v-for="evt in getFilteredEventsForDate(dayObj.dateString).slice(0, 3)"
                :key="evt.id"
                @click.stop="openEventDetails(evt)"
                :class="[
                  'px-2 py-1 rounded-md text-[11px] font-semibold border flex items-center justify-between gap-1 transition-transform hover:scale-[1.02] shadow-2xs truncate cursor-pointer',
                  getEventBadgeClass(evt)
                ]"
              >
                <div class="flex items-center gap-1 min-w-0 truncate">
                  <span :class="['w-1.5 h-1.5 rounded-full shrink-0', getEventDotClass(evt)]"></span>
                  <span class="truncate font-medium">{{ evt.title }}</span>
                </div>
                <span v-if="evt.amount > 0" class="text-[10px] font-bold shrink-0 opacity-90">{{ evt.formatted_amount }}</span>
                <span v-else-if="evt.status" class="text-[9px] font-bold uppercase tracking-wider px-1 py-0.2 rounded bg-black/10 dark:bg-white/10 shrink-0">{{ evt.status }}</span>
              </div>

              <!-- +N More Badge -->
              <div 
                v-if="getFilteredEventsForDate(dayObj.dateString).length > 3" 
                class="text-[10px] font-bold text-slate-500 dark:text-zinc-400 pl-1 hover:underline cursor-pointer"
                @click.stop="handleDateCellClick(dayObj)"
              >
                +{{ getFilteredEventsForDate(dayObj.dateString).length - 3 }} more...
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. WEEK VIEW -->
      <div v-else-if="currentViewMode === 'week'" class="p-4 flex-1 flex flex-col">
        <div class="grid grid-cols-7 gap-3 flex-1">
          <div
            v-for="dayObj in currentWeekDays"
            :key="dayObj.dateString"
            @click="handleDateCellClick(dayObj)"
            :class="[
              'bg-slate-50/60 dark:bg-zinc-800/40 rounded-xl border border-slate-200 dark:border-zinc-800 p-3 flex flex-col justify-between cursor-pointer hover:border-slate-400 transition-all',
              dayObj.isToday ? 'ring-2 ring-indigo-500/40 bg-indigo-50/10' : ''
            ]"
          >
            <div>
              <div class="flex items-center justify-between pb-2 border-b border-slate-200/60 dark:border-zinc-700/60 mb-2">
                <span class="text-xs font-bold text-slate-500 uppercase">{{ dayObj.dayName }}</span>
                <span :class="['text-xs font-extrabold px-2 py-0.5 rounded-full', dayObj.isToday ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-slate-700 dark:text-slate-200']">
                  {{ dayObj.dayNumber }}
                </span>
              </div>

              <div class="space-y-1.5">
                <div
                  v-for="evt in getFilteredEventsForDate(dayObj.dateString)"
                  :key="evt.id"
                  @click.stop="openEventDetails(evt)"
                  :class="['p-2 rounded-lg text-xs border transition-all cursor-pointer hover:shadow-sm', getEventBadgeClass(evt)]"
                >
                  <div class="font-bold truncate">{{ evt.title }}</div>
                  <div class="flex items-center justify-between text-[10px] mt-1 opacity-80 font-medium">
                    <span>{{ evt.subtitle }}</span>
                    <span>{{ evt.formatted_amount }}</span>
                  </div>
                </div>

                <div v-if="getFilteredEventsForDate(dayObj.dateString).length === 0" class="text-[11px] text-slate-400 dark:text-zinc-500 italic py-4 text-center">
                  No events scheduled
                </div>
              </div>
            </div>

            <button 
              class="w-full mt-3 py-1.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-700 font-semibold rounded-lg text-[11px] transition-all"
            >
              + Add Transaction
            </button>
          </div>
        </div>
      </div>

      <!-- 3. AGENDA LIST VIEW -->
      <div v-else-if="currentViewMode === 'list'" class="p-6 flex-1">
        <div class="space-y-4 max-w-4xl mx-auto">
          <div v-if="filteredEventsList.length === 0" class="py-12 text-center text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="text-sm font-bold text-slate-600 dark:text-zinc-400">No events found for this filter selection.</p>
          </div>

          <div
            v-for="evt in filteredEventsList"
            :key="evt.id"
            @click="openEventDetails(evt)"
            class="bg-slate-50/80 dark:bg-zinc-800/60 border border-slate-200/80 dark:border-zinc-700/80 rounded-xl p-4 flex items-center justify-between gap-4 transition-all hover:bg-white dark:hover:bg-zinc-800 shadow-2xs cursor-pointer"
          >
            <div class="flex items-center gap-3.5 min-w-0">
              <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-white font-bold text-xs shadow-xs', getEventBgColor(evt)]">
                {{ getEventIconInitial(evt) }}
              </div>
              <div class="min-w-0">
                <div class="flex items-center gap-2">
                  <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ evt.title }}</h4>
                  <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider', getEventBadgeClass(evt)]">
                    {{ evt.type_label }}
                  </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5 truncate">{{ evt.subtitle }} • Date: {{ evt.date }}</p>
              </div>
            </div>

            <div class="text-right shrink-0">
              <div class="text-sm font-extrabold text-slate-900 dark:text-white">{{ evt.formatted_amount }}</div>
              <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">{{ evt.status || 'Active' }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 1: DATE-CLICK "ADD TRANSACTION" POPUP MODAL (CRITICAL REQUIREMENT) -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div v-if="showDateAddModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
          <!-- Backdrop -->
          <div class="fixed inset-0 bg-slate-900/60 dark:bg-black/75 backdrop-blur-xs" @click="showDateAddModal = false"></div>

          <!-- Popup Dialog Box -->
          <div class="relative w-full max-w-lg bg-white dark:bg-zinc-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-zinc-800 overflow-hidden p-6 z-10 space-y-5">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-zinc-800">
              <div class="flex items-center gap-3">
                <div class="p-2.5 bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 rounded-2xl shadow-xs">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Add New Transaction Entry</h3>
                  <p class="text-xs text-slate-500 dark:text-zinc-400 font-medium">
                    Selected Date: <strong class="text-slate-900 dark:text-white">{{ selectedDateFormatted }}</strong>
                  </p>
                </div>
              </div>

              <button
                @click="showDateAddModal = false"
                class="p-2 text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-xl transition-all cursor-pointer"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- List of 5 Options (Sale, Purchase, Payments Out, Payment Receipts, Expenses) -->
            <div class="space-y-2.5">
              <div
                v-for="opt in dateAddOptions"
                :key="opt.key"
                @click="selectAddOption(opt)"
                class="group p-3.5 bg-slate-50/80 hover:bg-slate-900 hover:text-white dark:bg-zinc-800/60 dark:hover:bg-zinc-100 dark:hover:text-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-700/80 transition-all duration-200 flex items-center justify-between cursor-pointer shadow-2xs"
              >
                <div class="flex items-center gap-3.5">
                  <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-colors', opt.iconBg]">
                    <span class="text-lg">{{ opt.icon }}</span>
                  </div>
                  <div>
                    <h4 class="text-xs font-extrabold tracking-wide uppercase group-hover:text-white dark:group-hover:text-zinc-900">{{ opt.title }}</h4>
                    <p class="text-xs opacity-75 group-hover:text-slate-300 dark:group-hover:text-zinc-600 font-medium">{{ opt.description }}</p>
                  </div>
                </div>

                <svg class="w-5 h-5 text-slate-400 group-hover:text-white dark:group-hover:text-zinc-900 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </div>
            </div>

            <!-- Footer Cancel -->
            <div class="pt-2 border-t border-slate-100 dark:border-zinc-800 flex justify-end">
              <button
                @click="showDateAddModal = false"
                class="px-4 py-2 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-bold text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- MODAL 2: EVENT DETAILS MODAL -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div v-if="showEventDetailModal && activeEvent" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
          <!-- Backdrop -->
          <div class="fixed inset-0 bg-slate-900/60 dark:bg-black/75 backdrop-blur-xs" @click="showEventDetailModal = false"></div>

          <!-- Dialog Box -->
          <div class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-zinc-800 overflow-hidden p-6 z-10 space-y-4">
            <div class="flex items-start justify-between">
              <div class="flex items-center gap-3">
                <div :class="['w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 text-white font-bold shadow-xs', getEventBgColor(activeEvent)]">
                  {{ getEventIconInitial(activeEvent) }}
                </div>
                <div>
                  <span :class="['px-2 py-0.5 text-[10px] font-bold rounded-full border uppercase tracking-wider', getEventBadgeClass(activeEvent)]">
                    {{ activeEvent.type_label }}
                  </span>
                  <h3 class="text-base font-extrabold text-slate-900 dark:text-white mt-0.5">{{ activeEvent.title }}</h3>
                </div>
              </div>
              <button @click="showEventDetailModal = false" class="text-slate-400 hover:text-slate-900 dark:hover:text-white p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Details List -->
            <div class="bg-slate-50 dark:bg-zinc-800/60 rounded-2xl p-4 space-y-2.5 text-xs">
              <div class="flex justify-between pb-2 border-b border-slate-200/60 dark:border-zinc-700/60">
                <span class="text-slate-500 font-medium">Date & Time</span>
                <span class="font-bold text-slate-900 dark:text-white">{{ activeEvent.date }} {{ activeEvent.time ? `(${activeEvent.time})` : '' }}</span>
              </div>
              <div v-if="activeEvent.amount > 0" class="flex justify-between pb-2 border-b border-slate-200/60 dark:border-zinc-700/60">
                <span class="text-slate-500 font-medium">Total Amount</span>
                <span class="font-extrabold text-slate-900 dark:text-white text-sm">{{ activeEvent.formatted_amount }}</span>
              </div>
              <div class="flex justify-between pb-2 border-b border-slate-200/60 dark:border-zinc-700/60">
                <span class="text-slate-500 font-medium">Status</span>
                <span class="font-bold text-slate-900 dark:text-white uppercase">{{ activeEvent.status }}</span>
              </div>
              <div v-if="activeEvent.subtitle" class="flex justify-between">
                <span class="text-slate-500 font-medium">Entity / Reference</span>
                <span class="font-bold text-slate-900 dark:text-white truncate max-w-[200px]">{{ activeEvent.subtitle }}</span>
              </div>
            </div>

            <!-- Action Link Button -->
            <div class="pt-2 flex items-center justify-end gap-2">
              <button
                @click="showEventDetailModal = false"
                class="px-4 py-2 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-bold text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer"
              >
                Close
              </button>
              <button
                @click="navigateToEventUrl(activeEvent)"
                class="px-5 py-2 bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 hover:bg-black dark:hover:bg-white font-bold rounded-xl text-xs shadow-xs transition-all cursor-pointer"
              >
                View Details Page →
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- MODAL 3: GOOGLE CALENDAR SYNC SETTINGS MODAL -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div v-if="showGoogleSettingsModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
          <!-- Backdrop -->
          <div class="fixed inset-0 bg-slate-900/60 dark:bg-black/75 backdrop-blur-xs" @click="showGoogleSettingsModal = false"></div>

          <!-- Dialog Box -->
          <div class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-zinc-800 overflow-hidden p-6 z-10 space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-zinc-800">
              <div class="flex items-center gap-3">
                <div class="p-2.5 bg-purple-600 text-white rounded-2xl shadow-xs">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zm-7 5h5v5h-5z"/>
                  </svg>
                </div>
                <div>
                  <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Google Calendar Sync</h3>
                  <p class="text-xs text-slate-500 dark:text-zinc-400 font-medium">Configure Google Calendar Integration</p>
                </div>
              </div>
              <button @click="showGoogleSettingsModal = false" class="text-slate-400 hover:text-slate-900 dark:hover:text-white p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Settings Content -->
            <div class="space-y-4">
              <!-- Sync Status Card -->
              <div class="p-4 bg-slate-50 dark:bg-zinc-800/60 rounded-2xl border border-slate-200/80 dark:border-zinc-700/80 space-y-3">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="text-xs font-bold text-slate-900 dark:text-white">Google Calendar Status</div>
                    <div class="text-[11px] text-slate-500 dark:text-zinc-400">
                      {{ googleSettings.is_synced ? 'Connected to Google Account' : 'Not connected' }}
                    </div>
                  </div>
                  <span
                    :class="[
                      'px-2.5 py-0.5 text-[10px] font-extrabold rounded-full uppercase tracking-wider',
                      googleSettings.is_synced 
                        ? 'bg-purple-100 text-purple-700 border border-purple-300 dark:bg-purple-950 dark:text-purple-300 dark:border-purple-800' 
                        : 'bg-slate-200 text-slate-600 dark:bg-zinc-700 dark:text-zinc-400'
                    ]"
                  >
                    {{ googleSettings.is_synced ? 'Connected' : 'Disconnected' }}
                  </span>
                </div>

                <!-- Account info if connected -->
                <div v-if="googleSettings.is_synced && googleSettings.google_account_email" class="pt-2 border-t border-slate-200/60 dark:border-zinc-700/60 text-xs">
                  <span class="text-slate-500 font-medium">Account: </span>
                  <strong class="text-slate-900 dark:text-white">{{ googleSettings.google_account_email }}</strong>
                </div>

                <!-- Last Synced Date Info -->
                <div v-if="googleSettings.last_synced_at" class="text-[11px] text-slate-500 dark:text-zinc-400 flex items-center gap-1.5 pt-1">
                  <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <span>Last Synced: {{ formatDate(googleSettings.last_synced_at) }}</span>
                </div>
              </div>

              <!-- OAuth Connect Button when Disconnected -->
              <div v-if="!googleSettings.is_synced" class="text-center pt-1">
                <button
                  @click="connectGoogleCalendar"
                  class="w-full py-3 px-4 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-extrabold rounded-2xl text-xs shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer"
                >
                  <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zm-7 5h5v5h-5z"/>
                  </svg>
                  <span>Connect with Google Calendar</span>
                </button>
                <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-2">
                  Authorizes direct access to your Google Calendar events.
                </p>
              </div>

              <!-- Disconnect Button when Connected -->
              <div v-else class="pt-1">
                <button
                  @click="disconnectGoogleCalendar"
                  :disabled="isUpdatingGoogleSettings"
                  class="w-full py-2.5 px-4 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 font-bold rounded-2xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer"
                >
                  <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                  </svg>
                  <span>Disconnect Google Calendar</span>
                </button>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-3 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between gap-2">
              <button
                v-if="googleSettings.is_synced"
                @click="triggerGoogleSyncNow"
                :disabled="isUpdatingGoogleSettings"
                class="px-3.5 py-2 border border-purple-200 text-purple-700 dark:border-purple-800 dark:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-950/40 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5"
              >
                <svg :class="['w-3.5 h-3.5', isUpdatingGoogleSettings ? 'animate-spin' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Sync Now</span>
              </button>

              <button
                @click="showGoogleSettingsModal = false"
                class="ml-auto px-5 py-2 bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 hover:bg-black dark:hover:bg-white font-bold rounded-xl text-xs shadow-xs transition-all cursor-pointer"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const route = useRoute();

// State Variables
const loading = ref(false);
const currentViewMode = ref('month'); // 'month', 'week', 'list'
const currentDate = ref(new Date());
const eventsList = ref([]);
const activeFilters = ref(['sales', 'purchases', 'payments_out', 'payment_receipts', 'expenses', 'google']);

// Google Settings State
const showGoogleSettingsModal = ref(false);
const isUpdatingGoogleSettings = ref(false);
const googleSettings = reactive({
  is_synced: false,
  calendar_id: 'primary',
  google_account_email: '',
  last_synced_at: null,
});

// Modals State
const showDateAddModal = ref(false);
const selectedDateObj = ref(null);
const showEventDetailModal = ref(false);
const activeEvent = ref(null);

// Event Filters Metadata
const eventTypeFilters = [
  { key: 'google', label: 'Google Calendar', dotClass: 'bg-purple-500', activeClass: 'bg-purple-100 text-purple-800 border-purple-300 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800' },
  { key: 'sales', label: 'Sale Invoices', dotClass: 'bg-indigo-500', activeClass: 'bg-indigo-100 text-indigo-800 border-indigo-300 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800' },
  { key: 'purchases', label: 'Purchase Invoices', dotClass: 'bg-emerald-500', activeClass: 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800' },
  { key: 'payments_out', label: 'Payments Out', dotClass: 'bg-amber-500', activeClass: 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800' },
  { key: 'payment_receipts', label: 'Payment Receipts', dotClass: 'bg-cyan-500', activeClass: 'bg-cyan-100 text-cyan-800 border-cyan-300 dark:bg-cyan-950/60 dark:text-cyan-300 dark:border-cyan-800' },
  { key: 'expenses', label: 'Expenses', dotClass: 'bg-rose-500', activeClass: 'bg-rose-100 text-rose-800 border-rose-300 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800' },
];

// Date Click Popup Options (CRITICAL REQUIREMENT)
const dateAddOptions = [
  { key: 'sale_invoice', title: 'Sale Invoice', description: 'Create a new customer sale invoice', icon: '🛒', iconBg: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' },
  { key: 'purchase_invoice', title: 'Purchase Invoice', description: 'Record a new purchase order or invoice', icon: '📦', iconBg: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' },
  { key: 'payments_out', title: 'Payments Out', description: 'Record an outgoing supplier/operational payment (with status)', icon: '💸', iconBg: 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' },
  { key: 'payment_receipts', title: 'Payment Receipts', description: 'Record an incoming customer payment receipt (with status)', icon: '📄', iconBg: 'bg-cyan-100 text-cyan-700 dark:bg-cyan-950 dark:text-cyan-300' },
  { key: 'expenses', title: 'Expenses', description: 'Log a new business or operational expense entry', icon: '🧾', iconBg: 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300' },
];

// Computed Properties
const formattedMonthYear = computed(() => {
  return currentDate.value.toLocaleString('default', { month: 'long', year: 'numeric' });
});

const selectedDateFormatted = computed(() => {
  if (!selectedDateObj.value) return '';
  const d = new Date(selectedDateObj.value.dateString + 'T00:00:00');
  return d.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric', year: 'numeric' });
});

// Calendar Month Days Generator
const calendarDays = computed(() => {
  const year = currentDate.value.getFullYear();
  const month = currentDate.value.getMonth();

  const firstDayOfMonth = new Date(year, month, 1);
  const lastDayOfMonth = new Date(year, month + 1, 0);

  const startingDayOfWeek = firstDayOfMonth.getDay();
  const daysInMonth = lastDayOfMonth.getDate();

  const days = [];

  // Previous month padded days
  const prevMonthLastDay = new Date(year, month, 0).getDate();
  for (let i = startingDayOfWeek - 1; i >= 0; i--) {
    const dayNum = prevMonthLastDay - i;
    const d = new Date(year, month - 1, dayNum);
    days.push({
      dateString: formatDateString(d),
      dayNumber: dayNum,
      isCurrentMonth: false,
      isToday: isSameDate(d, new Date()),
    });
  }

  // Current month days
  for (let dNum = 1; dNum <= daysInMonth; dNum++) {
    const d = new Date(year, month, dNum);
    days.push({
      dateString: formatDateString(d),
      dayNumber: dNum,
      isCurrentMonth: true,
      isToday: isSameDate(d, new Date()),
    });
  }

  // Next month padded days to complete 35 or 42 grid cells
  const remaining = 42 - days.length;
  for (let n = 1; n <= remaining; n++) {
    const d = new Date(year, month + 1, n);
    days.push({
      dateString: formatDateString(d),
      dayNumber: n,
      isCurrentMonth: false,
      isToday: isSameDate(d, new Date()),
    });
  }

  return days;
});

// Current Week Days
const currentWeekDays = computed(() => {
  const curr = new Date(currentDate.value);
  const firstDay = curr.getDate() - curr.getDay();

  const week = [];
  for (let i = 0; i < 7; i++) {
    const nextDay = new Date(curr.setDate(firstDay + i));
    week.push({
      dateString: formatDateString(nextDay),
      dayNumber: nextDay.getDate(),
      dayName: nextDay.toLocaleDateString('en-US', { weekday: 'short' }),
      isToday: isSameDate(nextDay, new Date()),
    });
  }
  return week;
});

// Filtered Events List
const filteredEventsList = computed(() => {
  return eventsList.value.filter(evt => {
    if (evt.type === 'google_event' && !activeFilters.value.includes('google')) return false;
    if (evt.type === 'sale_invoice' && !activeFilters.value.includes('sales')) return false;
    if (evt.type === 'purchase_invoice' && !activeFilters.value.includes('purchases')) return false;
    if (evt.type === 'payment_out' && !activeFilters.value.includes('payments_out')) return false;
    if (evt.type === 'payment_receipt' && !activeFilters.value.includes('payment_receipts')) return false;
    if (evt.type === 'expense' && !activeFilters.value.includes('expenses')) return false;
    return true;
  });
});

// Methods
const fetchEvents = async () => {
  loading.value = true;
  try {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();

    const start = new Date(year, month - 1, 1);
    const end = new Date(year, month + 2, 0);

    const response = await axios.get('/api/calendar/events', {
      params: {
        start_date: formatDateString(start),
        end_date: formatDateString(end),
        types: activeFilters.value,
      }
    });

    eventsList.value = response.data.events || [];
  } catch (error) {
    console.error('Error fetching calendar events:', error);
  } finally {
    loading.value = false;
  }
};

const fetchGoogleSettings = async () => {
  try {
    const response = await axios.get('/api/calendar/google/settings');
    if (response.data && response.data.settings) {
      Object.assign(googleSettings, response.data.settings);
    }
  } catch (error) {
    console.error('Error fetching Google settings:', error);
  }
};

const connectGoogleCalendar = () => {
  window.location.href = '/auth/google/redirect?flow=calendar_sync';
};

const disconnectGoogleCalendar = async () => {
  isUpdatingGoogleSettings.value = true;
  try {
    const response = await axios.post('/api/calendar/google/disconnect');
    if (response.data && response.data.settings) {
      Object.assign(googleSettings, response.data.settings);
    } else {
      googleSettings.is_synced = false;
      googleSettings.google_account_email = null;
      googleSettings.last_synced_at = null;
    }
    showGoogleSettingsModal.value = false;
    await fetchEvents();
  } catch (error) {
    console.error('Error disconnecting Google Calendar:', error);
  } finally {
    isUpdatingGoogleSettings.value = false;
  }
};

const saveGoogleSettings = async () => {
  isUpdatingGoogleSettings.value = true;
  try {
    const response = await axios.post('/api/calendar/google/toggle-sync', googleSettings);
    if (response.data && response.data.settings) {
      Object.assign(googleSettings, response.data.settings);
    }
    showGoogleSettingsModal.value = false;
    fetchEvents();
  } catch (error) {
    console.error('Error saving Google settings:', error);
  } finally {
    isUpdatingGoogleSettings.value = false;
  }
};

const toggleGoogleSync = () => {
  googleSettings.is_synced = !googleSettings.is_synced;
  saveGoogleSettings();
};

const triggerGoogleSyncNow = async () => {
  isUpdatingGoogleSettings.value = true;
  try {
    await axios.post('/api/calendar/google/sync-now');
    await fetchEvents();
    showGoogleSettingsModal.value = false;
  } catch (error) {
    console.error('Error syncing Google Calendar:', error);
  } finally {
    isUpdatingGoogleSettings.value = false;
  }
};

const toggleFilter = (key) => {
  const idx = activeFilters.value.indexOf(key);
  if (idx > -1) {
    activeFilters.value.splice(idx, 1);
  } else {
    activeFilters.value.push(key);
  }
  fetchEvents();
};

const getFilteredEventsForDate = (dateStr) => {
  return filteredEventsList.value.filter(e => e.date === dateStr);
};

const navigateMonth = (direction) => {
  const d = new Date(currentDate.value);
  d.setMonth(d.getMonth() + direction);
  currentDate.value = d;
  fetchEvents();
};

const goToToday = () => {
  currentDate.value = new Date();
  fetchEvents();
};

// Date Cell Click -> Opens Add Transaction Popup (REQUIREMENT 4)
const handleDateCellClick = (dayObj) => {
  selectedDateObj.value = dayObj;
  showDateAddModal.value = true;
};

// Option Selection inside Add Popup Modal -> Opens Add Component / Route
const selectAddOption = (opt) => {
  showDateAddModal.value = false;
  const dateStr = selectedDateObj.value ? selectedDateObj.value.dateString : formatDateString(new Date());

  if (opt.key === 'sale_invoice') {
    router.push(`/sales/invoices/create?date=${dateStr}`);
  } else if (opt.key === 'purchase_invoice') {
    router.push(`/purchase/orders/create?date=${dateStr}`);
  } else if (opt.key === 'payments_out') {
    router.push(`/payments?create=true&date=${dateStr}`);
  } else if (opt.key === 'payment_receipts') {
    router.push(`/payment-receipts?create=true&date=${dateStr}`);
  } else if (opt.key === 'expenses') {
    router.push(`/expenses/create?date=${dateStr}`);
  }
};

const openEventDetails = (evt) => {
  activeEvent.value = evt;
  showEventDetailModal.value = true;
};

const navigateToEventUrl = (evt) => {
  showEventDetailModal.value = false;
  if (evt.url && evt.url !== '#') {
    router.push(evt.url);
  }
};

// Formatting & Helper Utils
const formatDateString = (d) => {
  const yr = d.getFullYear();
  const mo = String(d.getMonth() + 1).padStart(2, '0');
  const da = String(d.getDate()).padStart(2, '0');
  return `${yr}-${mo}-${da}`;
};

const isSameDate = (d1, d2) => {
  return d1.getFullYear() === d2.getFullYear() &&
         d1.getMonth() === d2.getMonth() &&
         d1.getDate() === d2.getDate();
};

const formatDate = (dateVal) => {
  if (!dateVal) return '';
  return new Date(dateVal).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const getEventBadgeClass = (evt) => {
  const classes = {
    google_event: 'bg-purple-100 text-purple-900 border-purple-300 dark:bg-purple-950/80 dark:text-purple-200 dark:border-purple-800',
    sale_invoice: 'bg-indigo-100 text-indigo-900 border-indigo-300 dark:bg-indigo-950/80 dark:text-indigo-200 dark:border-indigo-800',
    purchase_invoice: 'bg-emerald-100 text-emerald-900 border-emerald-300 dark:bg-emerald-950/80 dark:text-emerald-200 dark:border-emerald-800',
    payment_out: 'bg-amber-100 text-amber-900 border-amber-300 dark:bg-amber-950/80 dark:text-amber-200 dark:border-amber-800',
    payment_receipt: 'bg-cyan-100 text-cyan-900 border-cyan-300 dark:bg-cyan-950/80 dark:text-cyan-200 dark:border-cyan-800',
    expense: 'bg-rose-100 text-rose-900 border-rose-300 dark:bg-rose-950/80 dark:text-rose-200 dark:border-rose-800',
  };
  return classes[evt.type] || 'bg-slate-100 text-slate-900 border-slate-300';
};

const getEventDotClass = (evt) => {
  const dots = {
    google_event: 'bg-purple-500',
    sale_invoice: 'bg-indigo-500',
    purchase_invoice: 'bg-emerald-500',
    payment_out: 'bg-amber-500',
    payment_receipt: 'bg-cyan-500',
    expense: 'bg-rose-500',
  };
  return dots[evt.type] || 'bg-slate-400';
};

const getEventBgColor = (evt) => {
  const bgs = {
    google_event: 'bg-purple-600',
    sale_invoice: 'bg-indigo-600',
    purchase_invoice: 'bg-emerald-600',
    payment_out: 'bg-amber-600',
    payment_receipt: 'bg-cyan-600',
    expense: 'bg-rose-600',
  };
  return bgs[evt.type] || 'bg-slate-800';
};

const getEventIconInitial = (evt) => {
  const initials = {
    google_event: 'G',
    sale_invoice: 'S',
    purchase_invoice: 'P',
    payment_out: 'PO',
    payment_receipt: 'PR',
    expense: 'E',
  };
  return initials[evt.type] || 'E';
};

onMounted(async () => {
  if (route.query.google_sync === 'success') {
    router.replace({ query: {} });
  }
  await fetchGoogleSettings();
  await fetchEvents();
});
</script>
