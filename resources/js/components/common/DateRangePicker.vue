<template>
  <div class="relative">
    <label v-if="label" class="block text-sm font-medium text-gray-700 mb-1">{{ label }}</label>
    <div class="relative">
      <input
        ref="dateInput"
        type="text"
        :value="displayValue"
        @click="togglePicker"
        readonly
        :placeholder="placeholder"
        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer"
      />
      <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
      </div>
    </div>

    <!-- Date Range Picker Dropdown -->
    <div v-if="showPicker" class="absolute z-50 mt-1 bg-white border border-gray-300 rounded-md shadow-lg p-4 w-80">
      <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">From Date</label>
          <input
            v-model="tempStartDate"
            type="date"
            class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">To Date</label>
          <input
            v-model="tempEndDate"
            type="date"
            class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
          />
        </div>
      </div>

      <!-- Quick Select Options -->
      <div class="mb-4">
        <label class="block text-xs font-medium text-gray-700 mb-2">Quick Select</label>
        <div class="grid grid-cols-2 gap-2">
          <button
            v-for="option in quickOptions"
            :key="option.label"
            @click="selectQuickOption(option)"
            type="button"
            class="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded text-gray-700"
          >
            {{ option.label }}
          </button>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end space-x-2">
        <button
          @click="clearDates"
          type="button"
          class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800"
        >
          Clear
        </button>
        <button
          @click="cancelSelection"
          type="button"
          class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50"
        >
          Cancel
        </button>
        <button
          @click="applySelection"
          type="button"
          class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
        >
          Apply
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ start_date: '', end_date: '' })
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Select date range'
  }
});

const emit = defineEmits(['update:modelValue', 'change']);

// Reactive data
const showPicker = ref(false);
const tempStartDate = ref('');
const tempEndDate = ref('');
const dateInput = ref(null);

// Quick select options
const quickOptions = [
  { label: 'Today', days: 0 },
  { label: 'Yesterday', days: 1, single: true },
  { label: 'Last 7 days', days: 7 },
  { label: 'Last 30 days', days: 30 },
  { label: 'This Month', type: 'month' },
  { label: 'Last Month', type: 'lastMonth' }
];

// Computed
const displayValue = computed(() => {
  const { start_date, end_date } = props.modelValue;
  if (!start_date && !end_date) return '';
  if (start_date && !end_date) return formatDate(start_date);
  if (!start_date && end_date) return `Until ${formatDate(end_date)}`;
  if (start_date === end_date) return formatDate(start_date);
  return `${formatDate(start_date)} - ${formatDate(end_date)}`;
});

// Methods
const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric' 
  });
};

const togglePicker = () => {
  showPicker.value = !showPicker.value;
  if (showPicker.value) {
    tempStartDate.value = props.modelValue.start_date || '';
    tempEndDate.value = props.modelValue.end_date || '';
  }
};

const selectQuickOption = (option) => {
  const today = new Date();
  let startDate, endDate;

  if (option.type === 'month') {
    startDate = new Date(today.getFullYear(), today.getMonth(), 1);
    endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
  } else if (option.type === 'lastMonth') {
    startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
    endDate = new Date(today.getFullYear(), today.getMonth(), 0);
  } else if (option.single) {
    startDate = new Date(today);
    startDate.setDate(today.getDate() - option.days);
    endDate = startDate;
  } else {
    startDate = new Date(today);
    if (option.days > 0) {
      startDate.setDate(today.getDate() - option.days + 1);
    }
    endDate = today;
  }

  tempStartDate.value = startDate.toISOString().split('T')[0];
  tempEndDate.value = endDate.toISOString().split('T')[0];
};

const clearDates = () => {
  tempStartDate.value = '';
  tempEndDate.value = '';
};

const cancelSelection = () => {
  showPicker.value = false;
  tempStartDate.value = props.modelValue.start_date || '';
  tempEndDate.value = props.modelValue.end_date || '';
};

const applySelection = () => {
  const newValue = {
    start_date: tempStartDate.value,
    end_date: tempEndDate.value
  };
  
  emit('update:modelValue', newValue);
  emit('change', newValue);
  showPicker.value = false;
};

// Click outside to close
const handleClickOutside = (event) => {
  if (dateInput.value && !dateInput.value.contains(event.target) && 
      !event.target.closest('.absolute.z-50')) {
    showPicker.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
  if (!showPicker.value) {
    tempStartDate.value = newValue.start_date || '';
    tempEndDate.value = newValue.end_date || '';
  }
}, { deep: true });
</script>
