<template>
  <div ref="rootRef" class="relative w-full">
    <label v-if="label" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
      {{ label }} <span v-if="required" class="text-rose-500">*</span>
    </label>

    <!-- Floating Select Trigger Input -->
    <button
      type="button"
      @click="toggle"
      :disabled="disabled"
      class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border rounded-xl text-left flex items-center justify-between shadow-xs transition-all cursor-pointer"
      :class="[
        error ? 'border-rose-400 dark:border-rose-600 focus:ring-2 focus:ring-rose-500/20' : 'border-slate-200 dark:border-zinc-700/80 hover:border-slate-300 dark:hover:border-zinc-600',
        isOpen ? 'ring-2 ring-slate-900/10 border-slate-900 dark:border-zinc-500 bg-white dark:bg-zinc-800' : 'hover:bg-white dark:hover:bg-zinc-800',
        disabled ? 'opacity-50 cursor-not-allowed bg-slate-100 dark:bg-zinc-900' : ''
      ]"
    >
      <span
        class="text-xs font-semibold truncate"
        :class="selectedOption ? 'text-slate-900 dark:text-slate-100' : 'text-slate-400 dark:text-zinc-500'"
      >
        {{ selectedOption ? selectedOption.label : placeholder }}
      </span>

      <div class="flex items-center gap-1.5 shrink-0 ml-2">
        <svg
          class="w-4 h-4 text-slate-400 transition-transform duration-200"
          :class="{ 'rotate-180 text-slate-900 dark:text-white': isOpen }"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </button>

    <!-- Floating Dropdown Popover Card -->
    <div
      v-if="isOpen"
      class="absolute left-0 right-0 top-full mt-1.5 z-[100] max-h-56 overflow-y-auto bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl shadow-2xl p-1.5 space-y-0.5 animate-fade-in"
    >
      <!-- Optional Search Bar if > 6 options -->
      <div v-if="normalizedOptions.length > 6" class="p-1 mb-1 sticky top-0 bg-white dark:bg-zinc-900 z-10 border-b border-slate-100 dark:border-zinc-800">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search..."
          class="w-full px-2.5 py-1.5 text-xs font-medium bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-1 focus:ring-slate-900 dark:focus:ring-zinc-400"
          @click.stop
        />
      </div>

      <button
        v-for="opt in filteredOptions"
        :key="opt.value"
        type="button"
        @click="selectOption(opt)"
        class="w-full text-left px-3 py-2 text-xs font-semibold rounded-xl flex items-center justify-between transition-all cursor-pointer"
        :class="opt.value == modelValue
          ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-bold shadow-xs'
          : 'text-slate-700 dark:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800'"
      >
        <div class="flex flex-col truncate pr-2">
          <span class="truncate">{{ opt.label }}</span>
          <span v-if="opt.sublabel" class="text-[10px] font-normal opacity-70 truncate">{{ opt.sublabel }}</span>
        </div>
        <svg v-if="opt.value == modelValue" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
        </svg>
      </button>

      <div v-if="filteredOptions.length === 0" class="p-3 text-center text-xs text-slate-400">
        No options available
      </div>
    </div>

    <!-- Error Message -->
    <span v-if="error" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ error }}</span>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  options: {
    type: Array,
    default: () => []
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Select Option'
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:modelValue', 'change']);

const rootRef = ref(null);
const isOpen = ref(false);
const searchQuery = ref('');

const normalizedOptions = computed(() => {
  if (!Array.isArray(props.options)) return [];
  return props.options.map(opt => {
    if (typeof opt === 'object' && opt !== null) {
      return {
        value: opt.value !== undefined ? opt.value : opt.id,
        label: opt.label || opt.name || String(opt.value),
        sublabel: opt.sublabel || ''
      };
    }
    return {
      value: opt,
      label: String(opt),
      sublabel: ''
    };
  });
});

const selectedOption = computed(() => {
  if (props.modelValue === '' || props.modelValue === null || props.modelValue === undefined) {
    return null;
  }
  return normalizedOptions.value.find(opt => opt.value == props.modelValue) || null;
});

const filteredOptions = computed(() => {
  if (!searchQuery.value) return normalizedOptions.value;
  const q = searchQuery.value.toLowerCase();
  return normalizedOptions.value.filter(opt =>
    opt.label.toLowerCase().includes(q) || (opt.sublabel && opt.sublabel.toLowerCase().includes(q))
  );
});

const toggle = () => {
  if (props.disabled) return;
  isOpen.value = !isOpen.value;
  searchQuery.value = '';
};

const selectOption = (opt) => {
  emit('update:modelValue', opt.value);
  emit('change', opt.value);
  isOpen.value = false;
  searchQuery.value = '';
};

const handleClickOutside = (event) => {
  if (rootRef.value && !rootRef.value.contains(event.target)) {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
