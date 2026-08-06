<template>
  <div class="relative w-full" ref="containerRef">
    <label v-if="label" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
      {{ label }}
    </label>

    <!-- Trigger Button -->
    <button
      type="button"
      @click="toggleOpen"
      :class="[
        'w-full text-left text-xs py-2 px-3 border rounded-xl bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 text-slate-800 dark:text-zinc-200 flex justify-between items-center shadow-sm hover:border-slate-400 dark:hover:border-zinc-600 focus:outline-none focus:ring-1 focus:ring-slate-400 transition-all cursor-pointer font-normal',
        buttonClass
      ]"
    >
      <span :class="hasValue ? 'font-normal text-slate-800 dark:text-slate-100' : 'font-normal text-slate-400 dark:text-slate-500'">
        {{ buttonDisplayText }}
      </span>
      <div class="flex items-center gap-1.5 shrink-0 ml-2">
        <span
          v-if="multiple && selectedCount > 0"
          class="px-1.5 py-0.5 text-[9px] font-black bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 rounded-full"
        >
          {{ selectedCount }}
        </span>
        <svg
          :class="{ 'rotate-180 text-slate-700 dark:text-slate-300': isOpen }"
          class="w-4 h-4 text-slate-400 transition-transform duration-200"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </button>

    <!-- Teleported Floating Options Menu (Bypasses parent table/modal overflow clipping) -->
    <Teleport to="body">
      <div
        v-if="isOpen"
        ref="dropdownMenuRef"
        :style="floatingStyle"
        class="bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#2E2E2E] rounded-2xl shadow-2xl overflow-hidden max-h-64 overflow-y-auto divide-y divide-slate-100 dark:divide-[#2E2E2E]/60 transition-all animate-in fade-in zoom-in-95 z-[99999]"
      >
        <!-- Optional Multi-select controls bar -->
        <div v-if="multiple" class="p-2 bg-slate-50 dark:bg-zinc-950 border-b border-slate-100 dark:border-[#2E2E2E] flex items-center justify-between sticky top-0 z-20">
          <button
            type="button"
            @click.stop="selectAllOptions"
            class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer"
          >
            Select All
          </button>
          <button
            type="button"
            @click.stop="clearAllOptions"
            class="text-[10px] font-bold text-slate-400 dark:text-slate-400 hover:text-rose-500 cursor-pointer"
          >
            Clear All
          </button>
        </div>

        <!-- Optional Search Input -->
        <div v-if="searchable" class="p-2 border-b border-slate-100 dark:border-[#2E2E2E] sticky bg-white dark:bg-[#1E1E1E] z-10" :class="multiple ? 'top-8' : 'top-0'">
          <input
            ref="searchInputRef"
            v-model="searchQuery"
            type="text"
            placeholder="Type to search..."
            class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-[#2E2E2E] rounded-lg text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-slate-400 dark:focus:border-slate-600 font-normal"
            @click.stop
          />
        </div>

        <!-- Options List -->
        <div v-if="filteredOptions.length === 0" class="p-3 text-xs text-slate-400 dark:text-slate-500 text-center font-normal">
          No matching options found
        </div>

        <div
          v-for="option in filteredOptions"
          :key="option.value"
          @click="!option.disabled && selectOption(option.value)"
          :class="[
            'p-2.5 px-3.5 text-xs transition-colors flex items-center justify-between select-none',
            option.disabled
              ? 'cursor-not-allowed opacity-50 text-slate-400 dark:text-zinc-500 bg-slate-50/50 dark:bg-zinc-900/50'
              : isOptionSelected(option.value)
                ? 'bg-slate-900 text-white dark:bg-zinc-800 dark:text-slate-100 font-semibold cursor-pointer'
                : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-zinc-800/60 hover:text-slate-900 dark:hover:text-white font-normal cursor-pointer'
          ]"
        >
          <div class="flex items-center gap-2">
            <input
              v-if="multiple"
              type="checkbox"
              :disabled="option.disabled"
              :checked="isOptionSelected(option.value)"
              class="rounded text-indigo-600 pointer-events-none cursor-pointer"
            />
            <span>{{ option.label }}</span>
          </div>
          <svg
            v-if="isOptionSelected(option.value)"
            class="w-4 h-4 text-white dark:text-slate-100 shrink-0 ml-2"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
          </svg>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  label: { type: String, default: '' },
  options: { type: Array, default: () => [] },
  modelValue: { type: [String, Number, Boolean, Object, Array], default: '' },
  placeholder: { type: String, default: 'Select...' },
  searchable: { type: Boolean, default: false },
  multiple: { type: Boolean, default: false },
  placement: { type: String, default: 'auto' }, // 'auto', 'top', 'bottom'
  buttonClass: { type: String, default: '' }
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const containerRef = ref(null);
const dropdownMenuRef = ref(null);
const searchInputRef = ref(null);
const searchQuery = ref('');

const floatingStyle = ref({
  position: 'fixed',
  top: '0px',
  left: '0px',
  width: '0px',
  zIndex: '99999'
});

const isSameValue = (val1, val2) => {
  if (val1 === val2) return true;
  if ((val1 === null || val1 === undefined) && (val2 === null || val2 === undefined)) return true;
  return String(val1) === String(val2);
};

const isOptionSelected = (val) => {
  if (props.multiple) {
    const arr = Array.isArray(props.modelValue) ? props.modelValue : [];
    return arr.some(item => isSameValue(item, val));
  }
  return isSameValue(props.modelValue, val);
};

const selectedCount = computed(() => {
  if (props.multiple) {
    return Array.isArray(props.modelValue) ? props.modelValue.length : 0;
  }
  return 0;
});

const hasValue = computed(() => {
  if (props.multiple) {
    return selectedCount.value > 0;
  }
  return props.modelValue !== '' && props.modelValue !== null && props.modelValue !== undefined;
});

const selectedOption = computed(() => {
  return props.options.find(opt => isSameValue(opt.value, props.modelValue));
});

const buttonDisplayText = computed(() => {
  if (props.multiple) {
    const arr = Array.isArray(props.modelValue) ? props.modelValue : [];
    if (arr.length === 0) return props.placeholder;
    if (arr.length === 1) {
      const match = props.options.find(opt => isSameValue(opt.value, arr[0]));
      return match ? match.label : props.placeholder;
    }
    return `${arr.length} Selected`;
  }
  return selectedOption.value ? selectedOption.value.label : props.placeholder;
});

const filteredOptions = computed(() => {
  if (!props.searchable || !searchQuery.value) return props.options;
  const q = searchQuery.value.toLowerCase().trim();
  return props.options.filter(opt =>
    (opt.label || '').toLowerCase().includes(q) ||
    String(opt.value || '').toLowerCase().includes(q)
  );
});

const updatePosition = () => {
  if (!containerRef.value) return;
  const rect = containerRef.value.getBoundingClientRect();
  
  const menuEl = dropdownMenuRef.value;
  const actualHeight = (menuEl && menuEl.offsetHeight)
    ? menuEl.offsetHeight
    : (props.options.length * 38 + (props.searchable ? 45 : 0));
  
  const spaceBelow = window.innerHeight - rect.bottom;
  
  let top = rect.bottom + 4;
  if (props.placement === 'top' || (props.placement === 'auto' && spaceBelow < (actualHeight + 10) && rect.top > actualHeight)) {
    top = rect.top - actualHeight - 4;
  }

  floatingStyle.value = {
    position: 'fixed',
    top: `${Math.max(4, top)}px`,
    left: `${rect.left}px`,
    width: `${rect.width}px`,
    zIndex: '99999'
  };
};

const toggleOpen = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    searchQuery.value = '';
    nextTick(() => {
      updatePosition();
      if (props.searchable) {
        searchInputRef.value?.focus();
      }
    });
  }
};

const selectOption = (val) => {
  const opt = props.options.find(o => isSameValue(o.value, val));
  if (opt && opt.disabled) return;

  if (props.multiple) {
    let currentArr = Array.isArray(props.modelValue) ? [...props.modelValue] : [];
    const idx = currentArr.findIndex(item => isSameValue(item, val));
    if (idx >= 0) {
      currentArr.splice(idx, 1);
    } else {
      currentArr.push(val);
    }
    emit('update:modelValue', currentArr);
    emit('change', currentArr);
    nextTick(() => updatePosition());
  } else {
    emit('update:modelValue', val);
    emit('change', val);
    isOpen.value = false;
  }
};

const selectAllOptions = () => {
  const allValues = filteredOptions.value.filter(o => !o.disabled).map(o => o.value);
  emit('update:modelValue', allValues);
  emit('change', allValues);
};

const clearAllOptions = () => {
  emit('update:modelValue', []);
  emit('change', []);
};

const handleClickOutside = (e) => {
  if (
    containerRef.value &&
    !containerRef.value.contains(e.target) &&
    dropdownMenuRef.value &&
    !dropdownMenuRef.value.contains(e.target)
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

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleClickOutside);
  window.removeEventListener('scroll', handleScrollOrResize, true);
  window.removeEventListener('resize', handleScrollOrResize);
});
</script>
