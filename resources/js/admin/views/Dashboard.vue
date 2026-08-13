<template>
<div>
  <!-- Date Filters Row Container (Matching User Dashboard) -->
  <div class="flex flex-col md:flex-row justify-end items-start md:items-center mb-6 gap-4">
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

      <!-- Date Input Button & Popover -->
      <div ref="pickerRef" class="relative">
        <button 
          @click="showPicker = !showPicker" 
          class="w-full md:w-80 flex items-center justify-between bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-3.5 py-2 text-xs font-bold text-zinc-800 dark:text-white shadow-xs hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-all cursor-pointer focus:outline-none"
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
            <i class="far fa-calendar-alt text-zinc-400 text-xs"></i>
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
                  :class="{ 'bg-zinc-100 dark:bg-zinc-800 text-black dark:text-white': subPickerOpen === 'month' }"
                >
                  <span>{{ monthNames[calendarMonth] }}</span>
                  <span class="text-[8px] text-zinc-400">▲</span>
                </button>

                <!-- Year Selector Button -->
                <button
                  type="button"
                  @click.stop="toggleSubPicker('year')"
                  class="px-2.5 py-1 font-bold text-zinc-800 dark:text-zinc-100 text-xs hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-xl transition-colors flex items-center gap-1 cursor-pointer"
                  :class="{ 'bg-zinc-100 dark:bg-zinc-800 text-black dark:text-white': subPickerOpen === 'year' }"
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
                  :class="calendarMonth === mIdx ? 'bg-black text-white dark:bg-white dark:text-black font-bold shadow-xs' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-200'"
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
                  :class="calendarYear === y ? 'bg-black text-white dark:bg-white dark:text-black font-bold shadow-xs' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-200'"
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
                  class="absolute inset-y-0.5 inset-x-0 bg-zinc-100 dark:bg-zinc-800"
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
                    'bg-black text-white dark:bg-white dark:text-black font-bold shadow-xs': isDateStart(day.dateStr) || isDateEnd(day.dateStr),
                    'text-black dark:text-white font-bold': isDateInRange(day.dateStr) && !isDateStart(day.dateStr) && !isDateEnd(day.dateStr),
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
                <span v-if="tempRange.from && !tempRange.to" class="text-black dark:text-white font-bold animate-pulse">
                  Select end date...
                </span>
                <span v-else-if="tempRange.from && tempRange.to" class="font-bold text-zinc-700 dark:text-zinc-200">
                  Range selected
                </span>
                <span v-else class="italic text-zinc-400">
                  Click start date
                </span>
              </div>
              <button
                type="button"
                @click="applyCurrentTempRange"
                class="px-3 py-1.5 font-bold text-white bg-black dark:bg-white dark:text-black rounded-xl hover:opacity-90 transition-all text-[10px] cursor-pointer shadow-xs"
              >
                Done
              </button>
            </div>
          </div>
        </transition>
      </div>
    </div>
  </div>

  <!-- Skeleton Lazy Loading State -->
  <div v-if="loading" class="space-y-6 animate-pulse">
    <!-- Primary Stat Cards Skeleton (4 Cards) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div v-for="i in 4" :key="'admin-stat-skel-' + i" class="bg-white dark:bg-zinc-900 rounded-2xl p-6 shadow-sm border border-zinc-200 dark:border-zinc-800 flex flex-col justify-between h-[134px]">
        <div class="flex items-start mb-3 justify-between">
          <div class="w-11 h-11 rounded-xl bg-zinc-200 dark:bg-zinc-800 shrink-0"></div>
          <div class="flex flex-col items-end space-y-2 flex-1 ml-4">
            <div class="w-20 h-3 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
            <div class="w-12 h-7 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
          </div>
        </div>
        <div class="flex items-center justify-between border-t border-zinc-100 dark:border-zinc-800/80 pt-3 mt-1">
          <div class="w-24 h-3 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
          <div class="w-14 h-5 bg-zinc-200 dark:bg-zinc-800 rounded-full"></div>
        </div>
      </div>
    </div>

    <!-- Overview Banner Skeleton (3 Cards) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div v-for="i in 3" :key="'admin-banner-skel-' + i" class="rounded-2xl p-6 shadow-sm border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 flex justify-between items-center h-[106px]">
        <div class="space-y-2">
          <div class="w-28 h-3 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
          <div class="w-14 h-8 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
        </div>
        <div class="w-12 h-12 rounded-xl bg-zinc-200 dark:bg-zinc-800 shrink-0"></div>
      </div>
    </div>
  </div>

  <!-- Loaded Content State -->
  <template v-else>
    <!-- Primary Stat Cards Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 shadow-sm border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white transition-all flex flex-col justify-between">
        <div class="flex items-start mb-3">
          <div class="w-11 h-11 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-black dark:text-white flex items-center justify-center mr-4 shrink-0 shadow-xs">
            <i class="fas fa-user-shield text-base"></i>
          </div>
          <div>
            <p class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Total Admins</p>
            <h3 class="text-2xl font-black text-zinc-950 dark:text-white tracking-tight mt-0.5">{{ stats.total_admins }}</h3>
          </div>
        </div>
        <div class="flex items-center justify-between border-t border-zinc-100 dark:border-zinc-800/80 pt-3 mt-1">
          <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">System Security</span>
          <span class="inline-block bg-black text-white dark:bg-white dark:text-black font-extrabold text-[10px] px-2.5 py-0.5 rounded-full uppercase tracking-wider">Active</span>
        </div>
      </div>

      <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 shadow-sm border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white transition-all flex flex-col justify-between">
        <div class="flex items-start mb-3">
          <div class="w-11 h-11 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-black dark:text-white flex items-center justify-center mr-4 shrink-0 shadow-xs">
            <i class="fas fa-users text-base"></i>
          </div>
          <div>
            <p class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Total Users</p>
            <h3 class="text-2xl font-black text-zinc-950 dark:text-white tracking-tight mt-0.5">{{ stats.total_users }}</h3>
          </div>
        </div>
        <div class="flex items-center justify-between border-t border-zinc-100 dark:border-zinc-800/80 pt-3 mt-1">
          <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Monthly Growth</span>
          <span class="inline-block bg-black text-white dark:bg-white dark:text-black font-extrabold text-[10px] px-2.5 py-0.5 rounded-full uppercase tracking-wider">+{{ stats.new_users_month }}</span>
        </div>
      </div>
      
      <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 shadow-sm border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white transition-all flex flex-col justify-between">
        <div class="flex items-start mb-3">
          <div class="w-11 h-11 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-black dark:text-white flex items-center justify-center mr-4 shrink-0 shadow-xs">
            <i class="fas fa-user-check text-base"></i>
          </div>
          <div>
            <p class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Active Users</p>
            <h3 class="text-2xl font-black text-zinc-950 dark:text-white tracking-tight mt-0.5">{{ stats.active_users }}</h3>
          </div>
        </div>
        <div class="flex items-center justify-between border-t border-zinc-100 dark:border-zinc-800/80 pt-3 mt-1">
          <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Active Accounts</span>
          <span class="inline-block bg-black text-white dark:bg-white dark:text-black font-extrabold text-[10px] px-2.5 py-0.5 rounded-full uppercase tracking-wider">Operational</span>
        </div>
      </div>
      
      <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 shadow-sm border border-zinc-200 dark:border-zinc-800 hover:border-black dark:hover:border-white transition-all flex flex-col justify-between">
        <div class="flex items-start mb-3">
          <div class="w-11 h-11 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-black dark:text-white flex items-center justify-center mr-4 shrink-0 shadow-xs">
            <i class="fas fa-user-plus text-base"></i>
          </div>
          <div>
            <p class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">New This Month</p>
            <h3 class="text-2xl font-black text-zinc-950 dark:text-white tracking-tight mt-0.5">{{ stats.new_users_month }}</h3>
          </div>
        </div>
        <div class="flex items-center justify-between border-t border-zinc-100 dark:border-zinc-800/80 pt-3 mt-1">
          <router-link :to="{ name: 'admin.users.index' }" class="text-xs font-extrabold text-black dark:text-white hover:underline flex items-center">
            View Users List <i class="fas fa-arrow-right ml-1.5 text-[10px]"></i>
          </router-link>
        </div>
      </div>
    </div>

    <!-- Monochrome Overview Banner Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <div class="rounded-2xl p-6 shadow-sm border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-black dark:hover:border-white transition-all flex justify-between items-center">
        <div>
          <p class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-1">Total Active Roles</p>
          <h3 class="text-3xl font-black text-zinc-950 dark:text-white">2</h3>
        </div>
        <div class="w-12 h-12 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-black dark:text-white flex items-center justify-center font-bold text-lg shadow-xs">
          <i class="fas fa-shield-alt text-xl"></i>
        </div>
      </div>

      <div class="rounded-2xl p-6 shadow-sm bg-black text-white dark:bg-white dark:text-black border border-black dark:border-white transition-all flex justify-between items-center">
        <div>
          <p class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-600 mb-1">System Health</p>
          <h3 class="text-3xl font-black">100%</h3>
        </div>
        <div class="w-12 h-12 rounded-xl bg-zinc-800 text-white dark:bg-zinc-100 dark:text-black flex items-center justify-center font-bold text-lg shadow-xs">
          <i class="fas fa-server text-xl"></i>
        </div>
      </div>

      <div class="rounded-2xl p-6 shadow-sm border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-black dark:hover:border-white transition-all flex justify-between items-center">
        <div>
          <p class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-1">Active Sessions</p>
          <h3 class="text-3xl font-black text-zinc-950 dark:text-white">1</h3>
        </div>
        <div class="w-12 h-12 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-black dark:text-white flex items-center justify-center font-bold text-lg shadow-xs">
          <i class="fas fa-chart-line text-xl"></i>
        </div>
      </div>
    </div>
  </template>
</div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { onClickOutside } from '@vueuse/core';

const stats = ref({
  total_admins: 0,
  total_users: 0,
  active_users: 0,
  new_users_month: 0
});
const loading = ref(true);

const todayStr = new Date().toISOString().split('T')[0];
const dateRange = ref({
  from: todayStr,
  to: todayStr
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
  showPicker.value = false;
  loadStats();
};

const clearDateRange = () => {
  tempRange.value.from = '';
  tempRange.value.to = '';
  dateRange.value.from = '';
  dateRange.value.to = '';
  showPicker.value = false;
  loadStats();
};

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

const loadStats = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/admin/api/dashboard', {
      params: {
        from: dateRange.value.from,
        to: dateRange.value.to
      }
    });
    stats.value = response.data.stats;
  } catch (e) {
    console.error('Failed to load dashboard stats', e);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadStats();
});
</script>
