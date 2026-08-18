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
      class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border rounded-xl text-left flex items-center justify-between shadow-xs transition-all cursor-pointer focus:outline-none"
      :class="[
        error ? 'border-rose-400 dark:border-rose-600 focus:ring-2 focus:ring-rose-500/20' : 'border-slate-200 dark:border-zinc-700/80 hover:border-slate-300 dark:hover:border-zinc-600',
        isOpen ? 'border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-800' : 'hover:bg-white dark:hover:bg-zinc-800',
        disabled ? 'opacity-50 cursor-not-allowed bg-slate-100 dark:bg-zinc-900' : ''
      ]"
    >
      <div class="flex flex-col truncate pr-2">
        <span
          class="text-xs font-semibold truncate"
          :class="selectedOption ? 'text-slate-900 dark:text-slate-100' : 'text-slate-400 dark:text-zinc-500'"
        >
          {{ selectedOption ? selectedOption.label : placeholder }}
        </span>
        <span v-if="selectedOption && selectedOption.sublabel" class="text-[10px] font-normal text-slate-500 dark:text-slate-400 truncate">
          {{ selectedOption.sublabel }}
        </span>
      </div>

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

    <!-- Teleported Floating Dropdown Popover Card -->
    <Teleport to="body">
      <div
        v-if="isOpen"
        ref="dropdownMenuRef"
        :style="floatingStyle"
        class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl shadow-2xl p-1.5 space-y-0.5 max-h-56 overflow-y-auto animate-fade-in z-[99999]"
      >
        <!-- Search Bar if searchable or > 4 options -->
        <div v-if="searchable || normalizedOptions.length > 4" class="p-1 mb-1 sticky top-0 bg-white dark:bg-zinc-900 z-10 border-b border-slate-100 dark:border-zinc-800">
          <input
            ref="searchInputRef"
            v-model="searchQuery"
            type="text"
            placeholder="Search..."
            class="w-full px-2.5 py-1.5 text-xs font-medium bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-0 focus:border-slate-300 dark:focus:border-zinc-600 text-slate-800 dark:text-zinc-200"
            @click.stop
          />
        </div>

        <button
          v-for="(opt, idx) in filteredOptions"
          :key="'opt-' + idx + '-' + opt.value"
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
    </Teleport>

    <!-- Error Message -->
    <span v-if="error" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ error }}</span>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue';

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
  },
  dropUp: {
    type: Boolean,
    default: false
  },
  placement: {
    type: String,
    default: 'down'
  },
  searchable: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['update:modelValue', 'change']);

const rootRef = ref(null);
const dropdownMenuRef = ref(null);
const searchInputRef = ref(null);
const isOpen = ref(false);
const searchQuery = ref('');

const floatingStyle = ref({
  position: 'fixed',
  top: '0px',
  left: '0px',
  width: '0px',
  zIndex: '99999'
});

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

const updatePosition = () => {
  if (!rootRef.value) return;
  const rect = rootRef.value.getBoundingClientRect();
  
  const menuEl = dropdownMenuRef.value;
  const actualHeight = (menuEl && menuEl.offsetHeight)
    ? menuEl.offsetHeight
    : Math.min(224, (normalizedOptions.value.length * 36 + 45));
  
  const spaceBelow = window.innerHeight - rect.bottom;
  
  let top = rect.bottom + 4;
  if (props.dropUp || props.placement === 'up' || (props.placement === 'auto' && spaceBelow < (actualHeight + 10) && rect.top > actualHeight)) {
    top = rect.top - actualHeight - 4;
  }

  const calculatedWidth = rect.width > 50 ? rect.width : (rootRef.value?.offsetWidth || 260);

  floatingStyle.value = {
    position: 'fixed',
    top: `${Math.max(4, top)}px`,
    left: `${rect.left}px`,
    width: `${calculatedWidth}px`,
    minWidth: '220px',
    zIndex: '999999'
  };
};

const toggle = () => {
  if (props.disabled) return;
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    searchQuery.value = '';
    nextTick(() => {
      updatePosition();
      searchInputRef.value?.focus();
      setTimeout(updatePosition, 50);
    });
  }
};

const selectOption = (opt) => {
  emit('update:modelValue', opt.value);
  emit('change', opt.value);
  isOpen.value = false;
  searchQuery.value = '';
};

const handleClickOutside = (event) => {
  if (
    rootRef.value &&
    !rootRef.value.contains(event.target) &&
    dropdownMenuRef.value &&
    !dropdownMenuRef.value.contains(event.target)
  ) {
    isOpen.value = false;
  }
};

const handleScrollOrResize = () => {
  if (isOpen.value) {
    updatePosition();
  }
};

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside);
  window.addEventListener('scroll', handleScrollOrResize, true);
  window.addEventListener('resize', handleScrollOrResize);
});

onUnmounted(() => {
  document.removeEventListener('mousedown', handleClickOutside);
  window.removeEventListener('scroll', handleScrollOrResize, true);
  window.removeEventListener('resize', handleScrollOrResize);
});
</script>
