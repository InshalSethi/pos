<template>
  <div ref="rootRef" class="relative inline-block w-full">
    <!-- Top Row Label & Quick Triggers -->
    <div class="flex items-center justify-between mb-1">
      <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">
        {{ label }}
      </label>
      <div v-if="showTopPresets" class="flex items-center space-x-2 text-[11px]">
        <button
          type="button"
          @click="applyPreset('today')"
          class="font-semibold text-slate-700 dark:text-zinc-300 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer"
        >
          Today
        </button>
        <button
          type="button"
          @click="applyPreset('this_month')"
          class="font-semibold text-slate-700 dark:text-zinc-300 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer"
        >
          This Month
        </button>
      </div>
    </div>

    <!-- Trigger Input Field -->
    <div class="relative">
      <button
        type="button"
        @click="togglePopover"
        class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-left flex items-center justify-between shadow-xs hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all cursor-pointer"
        :class="{ 'ring-2 ring-slate-900/10 border-slate-900 bg-white dark:bg-zinc-800': isOpen }"
      >
        <span
          class="text-xs font-semibold truncate"
          :class="displayDateRangeText ? 'text-slate-900 dark:text-slate-100' : 'text-slate-400 dark:text-zinc-500'"
        >
          {{ displayDateRangeText || placeholder }}
        </span>

        <div class="flex items-center gap-1.5 shrink-0 ml-2">
          <!-- Clear X button when dates are selected -->
          <span
            v-if="hasSelection"
            @click.stop="clearDates"
            class="p-0.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-full hover:bg-slate-200/60 dark:hover:bg-zinc-700 transition-colors cursor-pointer"
            title="Clear date range"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </span>
          <svg v-else class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
      </button>

      <!-- Floating Date Range Picker Dropdown Popover (Compact w-72, p-3, text-xs) -->
      <div
        v-if="isOpen"
        class="absolute right-0 top-full mt-1.5 z-50 w-72 p-3 text-xs shadow-xl rounded-2xl border border-slate-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 space-y-2 animate-fade-in"
      >
        <!-- Quick Filter Preset Pills Section -->
        <div class="space-y-1">
          <div class="flex flex-wrap items-center gap-1">
            <button
              v-for="p in presetsRow1"
              :key="p.id"
              type="button"
              @click="applyPreset(p.id)"
              class="px-2 py-1 text-[11px] font-medium rounded-md transition-all cursor-pointer"
              :class="activePreset === p.id 
                ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-semibold' 
                : 'bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 hover:bg-slate-200 dark:hover:bg-zinc-700'"
            >
              {{ p.label }}
            </button>
          </div>
          <div class="flex flex-wrap items-center justify-between gap-1">
            <div class="flex flex-wrap items-center gap-1">
              <button
                v-for="p in presetsRow2"
                :key="p.id"
                type="button"
                @click="applyPreset(p.id)"
                class="px-2 py-1 text-[11px] font-medium rounded-md transition-all cursor-pointer"
                :class="activePreset === p.id 
                  ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-semibold' 
                  : 'bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 hover:bg-slate-200 dark:hover:bg-zinc-700'"
              >
                {{ p.label }}
              </button>
            </div>
            <!-- Red Clear Action Button -->
            <button
              type="button"
              @click="clearDates"
              class="px-2 py-1 text-[11px] font-semibold text-rose-500 hover:text-rose-600 dark:text-rose-400 dark:hover:text-rose-300 transition-colors cursor-pointer ml-auto"
            >
              Clear
            </button>
          </div>
        </div>

        <div class="border-t border-slate-100 dark:border-zinc-800"></div>

        <!-- Month & Year Navigation Header -->
        <div class="flex items-center justify-between px-0.5 py-0.5">
          <button
            type="button"
            @click="prevMonth"
            class="p-1 rounded text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:hover:bg-zinc-800 dark:text-zinc-400 transition-colors cursor-pointer"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>

          <div class="flex items-center space-x-1 relative">
            <!-- Month Trigger -->
            <button
              type="button"
              @click="toggleSubPicker('month')"
              class="text-xs font-bold text-slate-900 dark:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 px-1.5 py-0.5 rounded transition-colors flex items-center space-x-1 cursor-pointer"
            >
              <span>{{ monthNames[currentCalendarMonth] }}</span>
              <span class="text-[8px] text-slate-400 dark:text-zinc-500">▲</span>
            </button>

            <!-- Year Trigger -->
            <button
              type="button"
              @click="toggleSubPicker('year')"
              class="text-xs font-bold text-slate-900 dark:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 px-1.5 py-0.5 rounded transition-colors flex items-center space-x-1 cursor-pointer"
            >
              <span>{{ currentCalendarYear }}</span>
              <span class="text-[8px] text-slate-400 dark:text-zinc-500">▲</span>
            </button>
          </div>

          <button
            type="button"
            @click="nextMonth"
            class="p-1 rounded text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:hover:bg-zinc-800 dark:text-zinc-400 transition-colors cursor-pointer"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>

        <!-- Sub-picker overlay for Months -->
        <div v-if="subPickerOpen === 'month'" class="grid grid-cols-3 gap-1 bg-slate-50 dark:bg-zinc-800 p-2 rounded-lg border border-slate-200 dark:border-zinc-700">
          <button
            v-for="(mName, mIdx) in monthNames"
            :key="mIdx"
            type="button"
            @click="selectMonth(mIdx)"
            class="py-1 text-xs font-semibold rounded text-center transition-colors cursor-pointer"
            :class="currentCalendarMonth === mIdx 
              ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-bold' 
              : 'hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-300'"
          >
            {{ mName.slice(0, 3) }}
          </button>
        </div>

        <!-- Sub-picker overlay for Years -->
        <div v-if="subPickerOpen === 'year'" class="grid grid-cols-4 gap-1 bg-slate-50 dark:bg-zinc-800 p-2 rounded-lg border border-slate-200 dark:border-zinc-700 max-h-32 overflow-y-auto">
          <button
            v-for="y in availableYears"
            :key="y"
            type="button"
            @click="selectYear(y)"
            class="py-1 text-xs font-semibold rounded text-center transition-colors cursor-pointer"
            :class="currentCalendarYear === y 
              ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-bold' 
              : 'hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-300'"
          >
            {{ y }}
          </button>
        </div>

        <!-- Main Calendar View -->
        <div v-if="!subPickerOpen" class="space-y-0.5">
          <!-- Weekday Headers -->
          <div class="grid grid-cols-7 text-center">
            <span v-for="dayName in weekDayNames" :key="dayName" class="text-[10px] py-0.5 text-slate-400 dark:text-zinc-500 font-bold">
              {{ dayName }}
            </span>
          </div>

          <!-- Calendar Days Grid (Compact h-6 w-6 text-[11px]) -->
          <div class="grid grid-cols-7 gap-y-0.5 gap-x-0">
            <div
              v-for="(day, idx) in calendarDays"
              :key="idx"
              class="relative flex items-center justify-center h-6 my-0.5"
              @mouseenter="hoverDate = day.dateStr"
              @mouseleave="hoverDate = ''"
            >
              <!-- Range connection background -->
              <div
                v-if="day.isCurrentMonth && isDayInRange(day.dateStr)"
                class="absolute inset-0 bg-slate-100 dark:bg-zinc-800"
                :class="{
                  'rounded-l-full': day.dateStr === tempStartDate,
                  'rounded-r-full': day.dateStr === tempEndDate
                }"
              ></div>

              <!-- Day Button -->
              <button
                type="button"
                @click="handleDayClick(day)"
                class="relative z-10 h-6 w-6 text-[11px] font-medium flex items-center justify-center rounded-full transition-all cursor-pointer"
                :class="getDayClass(day)"
              >
                {{ day.dayNum }}
              </button>
            </div>
          </div>
        </div>

        <!-- Sleek Footer Bar -->
        <div class="pt-2 mt-2 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between text-[11px] text-slate-500 dark:text-zinc-400">
          <span>
            {{ footerRangeText }}
          </span>
          <button
            type="button"
            @click="handleDone"
            class="px-3 py-1 text-xs font-semibold rounded-lg bg-slate-900 text-white hover:bg-slate-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white transition-all cursor-pointer"
          >
            Done
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  startDate: {
    type: String,
    default: ''
  },
  endDate: {
    type: String,
    default: ''
  },
  modelValue: {
    type: Object,
    default: () => null
  },
  label: {
    type: String,
    default: 'DATE RANGE'
  },
  placeholder: {
    type: String,
    default: 'Select Date Range'
  },
  showTopPresets: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits([
  'update:startDate',
  'update:endDate',
  'update:modelValue',
  'change',
  'apply',
  'clear'
]);

const rootRef = ref(null);
const isOpen = ref(false);
const subPickerOpen = ref(null); // 'month' | 'year' | null

const tempStartDate = ref('');
const tempEndDate = ref('');
const hoverDate = ref('');
const activePreset = ref('');

const currentCalendarYear = ref(new Date().getFullYear());
const currentCalendarMonth = ref(new Date().getMonth());

// Standard 12 Months starting from January
const monthNames = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December'
];

const weekDayNames = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

// Quick Presets Split into 2 Rows
const presetsRow1 = [
  { id: 'today', label: 'Today' },
  { id: 'yesterday', label: 'Yesterday' },
  { id: 'this_week', label: 'This Week' },
  { id: 'this_month', label: 'This Month' }
];

const presetsRow2 = [
  { id: 'last_month', label: 'Last Month' },
  { id: 'this_year', label: 'This Year' },
  { id: 'last_year', label: 'Last Year' }
];

// Effective start and end values from props
const effectiveStartDate = computed(() => {
  if (props.startDate !== undefined && props.startDate !== '') return props.startDate;
  if (props.modelValue && props.modelValue.start_date !== undefined) return props.modelValue.start_date || '';
  return '';
});

const effectiveEndDate = computed(() => {
  if (props.endDate !== undefined && props.endDate !== '') return props.endDate;
  if (props.modelValue && props.modelValue.end_date !== undefined) return props.modelValue.end_date || '';
  return '';
});

const hasSelection = computed(() => {
  return !!(effectiveStartDate.value || effectiveEndDate.value);
});

// Format ISO YYYY-MM-DD
const formatDateISO = (d) => {
  if (!d) return '';
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

// Formatted Display Text for Input Trigger
const displayDateRangeText = computed(() => {
  const start = effectiveStartDate.value;
  const end = effectiveEndDate.value;

  if (!start && !end) return '';
  if (start && !end) return formatDateFormatted(start);
  if (!start && end) return `Until ${formatDateFormatted(end)}`;
  if (start === end) return formatDateFormatted(start);
  return `${formatDateFormatted(start)} - ${formatDateFormatted(end)}`;
});

const formatDateFormatted = (dateStr) => {
  if (!dateStr) return '';
  const parts = dateStr.split('-');
  if (parts.length !== 3) return dateStr;
  const [y, m, d] = parts.map(Number);
  const date = new Date(y, m - 1, d);
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
};

const footerRangeText = computed(() => {
  if (tempStartDate.value || tempEndDate.value) {
    return 'Range selected';
  }
  return 'Select date range';
});

// Available Years range for subpicker
const availableYears = computed(() => {
  const currentY = new Date().getFullYear();
  const years = [];
  for (let y = currentY - 5; y <= currentY + 5; y++) {
    years.push(y);
  }
  return years;
});

// Calendar grid calculations (42 cells: 6 rows of 7 days)
const calendarDays = computed(() => {
  const year = currentCalendarYear.value;
  const month = currentCalendarMonth.value;

  const firstDayOfMonth = new Date(year, month, 1);
  const startingDayOfWeek = firstDayOfMonth.getDay(); // 0 = Sun

  const totalDaysInMonth = new Date(year, month + 1, 0).getDate();
  const prevMonthTotalDays = new Date(year, month, 0).getDate();

  const days = [];

  // Prev Month trailing days
  for (let i = startingDayOfWeek - 1; i >= 0; i--) {
    const prevDay = prevMonthTotalDays - i;
    const prevDate = new Date(year, month - 1, prevDay);
    days.push({
      dateStr: formatDateISO(prevDate),
      dayNum: prevDay,
      isCurrentMonth: false,
      isPrevMonth: true,
    });
  }

  // Current Month days
  for (let d = 1; d <= totalDaysInMonth; d++) {
    const dateObj = new Date(year, month, d);
    days.push({
      dateStr: formatDateISO(dateObj),
      dayNum: d,
      isCurrentMonth: true,
    });
  }

  // Next Month leading days
  const remainingCells = 42 - days.length;
  for (let n = 1; n <= remainingCells; n++) {
    const nextDate = new Date(year, month + 1, n);
    days.push({
      dateStr: formatDateISO(nextDate),
      dayNum: n,
      isCurrentMonth: false,
      isNextMonth: true,
    });
  }

  return days;
});

// Sync internal temp dates from props
const syncFromProps = () => {
  tempStartDate.value = effectiveStartDate.value;
  tempEndDate.value = effectiveEndDate.value;

  if (tempStartDate.value) {
    const parts = tempStartDate.value.split('-').map(Number);
    if (parts.length === 3) {
      currentCalendarYear.value = parts[0];
      currentCalendarMonth.value = parts[1] - 1;
    }
  }
};

watch([effectiveStartDate, effectiveEndDate], () => {
  if (!isOpen.value) {
    syncFromProps();
  }
}, { immediate: true });

// Popover toggle
const togglePopover = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    syncFromProps();
    subPickerOpen.value = null;
  }
};

// Quick Presets Calculation
const getPresetDates = (presetId) => {
  const today = new Date();
  let start = null;
  let end = null;

  if (presetId === 'today') {
    start = new Date(today);
    end = new Date(today);
  } else if (presetId === 'yesterday') {
    start = new Date(today);
    start.setDate(today.getDate() - 1);
    end = new Date(start);
  } else if (presetId === 'this_week') {
    const dayOfWeek = today.getDay(); // 0 = Sun
    start = new Date(today);
    start.setDate(today.getDate() - dayOfWeek);
    end = new Date(start);
    end.setDate(start.getDate() + 6);
  } else if (presetId === 'this_month') {
    start = new Date(today.getFullYear(), today.getMonth(), 1);
    end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
  } else if (presetId === 'last_month') {
    start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
    end = new Date(today.getFullYear(), today.getMonth(), 0);
  } else if (presetId === 'this_year') {
    start = new Date(today.getFullYear(), 0, 1);
    end = new Date(today.getFullYear(), 11, 31);
  } else if (presetId === 'last_year') {
    start = new Date(today.getFullYear() - 1, 0, 1);
    end = new Date(today.getFullYear() - 1, 11, 31);
  }

  return {
    start: formatDateISO(start),
    end: formatDateISO(end)
  };
};

const applyPreset = (presetId) => {
  activePreset.value = presetId;
  const { start, end } = getPresetDates(presetId);
  tempStartDate.value = start;
  tempEndDate.value = end;

  if (start) {
    const parts = start.split('-').map(Number);
    currentCalendarYear.value = parts[0];
    currentCalendarMonth.value = parts[1] - 1;
  }

  commitSelection();
  isOpen.value = false;
};

const clearDates = () => {
  activePreset.value = '';
  tempStartDate.value = '';
  tempEndDate.value = '';
  commitSelection();
  emit('clear');
  isOpen.value = false;
};

const commitSelection = () => {
  const start = tempStartDate.value;
  const end = tempEndDate.value;

  emit('update:startDate', start);
  emit('update:endDate', end);
  emit('update:modelValue', { start_date: start, end_date: end });
  emit('change', { start_date: start, end_date: end });
  emit('apply', { start_date: start, end_date: end });
};

const handleDone = () => {
  commitSelection();
  isOpen.value = false;
};

// Calendar day clicks
const handleDayClick = (day) => {
  activePreset.value = '';
  const dateStr = day.dateStr;

  if (!day.isCurrentMonth) {
    const parts = dateStr.split('-').map(Number);
    currentCalendarYear.value = parts[0];
    currentCalendarMonth.value = parts[1] - 1;
  }

  if (!tempStartDate.value || (tempStartDate.value && tempEndDate.value)) {
    tempStartDate.value = dateStr;
    tempEndDate.value = '';
  } else if (tempStartDate.value && !tempEndDate.value) {
    if (dateStr < tempStartDate.value) {
      tempEndDate.value = tempStartDate.value;
      tempStartDate.value = dateStr;
    } else {
      tempEndDate.value = dateStr;
    }
  }
};

const isDayInRange = (dateStr) => {
  const start = tempStartDate.value;
  const end = tempEndDate.value;
  const hover = hoverDate.value;

  if (start && end) {
    return dateStr >= start && dateStr <= end;
  }

  if (start && !end && hover) {
    if (hover >= start) {
      return dateStr >= start && dateStr <= hover;
    } else {
      return dateStr >= hover && dateStr <= start;
    }
  }

  return false;
};

const getDayClass = (day) => {
  const dateStr = day.dateStr;
  const todayStr = formatDateISO(new Date());
  const start = tempStartDate.value;
  const end = tempEndDate.value;

  if (!day.isCurrentMonth) {
    return 'text-slate-300 dark:text-zinc-600 hover:bg-slate-100 dark:hover:bg-zinc-800/50';
  }

  const isStart = dateStr === start;
  const isEnd = dateStr === end;
  const isToday = dateStr === todayStr;

  if (isStart || isEnd) {
    return 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-bold shadow-xs';
  }

  if (isToday) {
    return 'border border-slate-300 dark:border-zinc-600 text-slate-900 dark:text-white font-bold hover:bg-indigo-50 dark:hover:bg-zinc-800';
  }

  return 'text-slate-800 dark:text-zinc-200 hover:bg-indigo-50 dark:hover:bg-zinc-800';
};

// Month / Year Navigation
const prevMonth = () => {
  if (currentCalendarMonth.value === 0) {
    currentCalendarMonth.value = 11;
    currentCalendarYear.value--;
  } else {
    currentCalendarMonth.value--;
  }
};

const nextMonth = () => {
  if (currentCalendarMonth.value === 11) {
    currentCalendarMonth.value = 0;
    currentCalendarYear.value++;
  } else {
    currentCalendarMonth.value++;
  }
};

const toggleSubPicker = (type) => {
  if (subPickerOpen.value === type) {
    subPickerOpen.value = null;
  } else {
    subPickerOpen.value = type;
  }
};

const selectMonth = (mIdx) => {
  currentCalendarMonth.value = mIdx;
  subPickerOpen.value = null;
};

const selectYear = (y) => {
  currentCalendarYear.value = y;
  subPickerOpen.value = null;
};

// Click Outside Handler
const handleClickOutside = (event) => {
  if (rootRef.value && !rootRef.value.contains(event.target)) {
    isOpen.value = false;
    subPickerOpen.value = null;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
