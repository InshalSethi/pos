<template>
  <div class="relative w-full" ref="containerRef">
    <label v-if="label" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">
      {{ label }}
    </label>

    <!-- Trigger Button -->
    <button
      type="button"
      @click="toggleOpen"
      class="w-full text-left text-xs p-2.5 px-3 border rounded-xl bg-slate-50 dark:bg-zinc-950 border-slate-300 dark:border-zinc-800 text-slate-900 dark:text-zinc-200 flex justify-between items-center shadow-sm hover:border-indigo-400 dark:hover:border-indigo-600 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-all cursor-pointer"
    >
      <span :class="selectedOption ? 'font-normal text-slate-800 dark:text-slate-100' : 'font-normal text-slate-400 dark:text-slate-500'">
        {{ selectedOption ? selectedOption.label : placeholder }}
      </span>
      <svg
        :class="{ 'rotate-180 text-indigo-600 dark:text-indigo-400': isOpen }"
        class="w-4 h-4 text-slate-400 transition-transform duration-200 shrink-0 ml-2"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <!-- Teleported Floating Options Menu (Bypasses parent table/modal overflow clipping) -->
    <Teleport to="body">
      <div
        v-if="isOpen"
        ref="dropdownMenuRef"
        :style="floatingStyle"
        class="bg-white dark:bg-[#12141a] border border-slate-200 dark:border-zinc-800 rounded-2xl shadow-2xl overflow-hidden max-h-60 overflow-y-auto divide-y divide-slate-100 dark:divide-zinc-800/60 transition-all animate-in fade-in zoom-in-95"
      >
        <!-- Optional Search Input -->
        <div v-if="searchable" class="p-2 border-b border-slate-100 dark:border-zinc-800 sticky top-0 bg-white dark:bg-[#12141a] z-10">
          <input
            ref="searchInputRef"
            v-model="searchQuery"
            type="text"
            placeholder="Type to search..."
            class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg text-slate-800 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 font-normal"
            @click.stop
          />
        </div>

        <!-- Options List -->
        <div v-if="filteredOptions.length === 0" class="p-3 text-xs text-slate-400 text-center font-normal">
          No matching options found
        </div>

        <div
          v-for="option in filteredOptions"
          :key="option.value"
          @click="selectOption(option.value)"
          :class="[
            'p-2.5 px-3.5 text-xs font-normal cursor-pointer transition-colors flex items-center justify-between',
            String(modelValue) === String(option.value)
              ? 'bg-indigo-600 text-white font-normal'
              : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-normal'
          ]"
        >
          <span>{{ option.label }}</span>
          <svg
            v-if="String(modelValue) === String(option.value)"
            class="w-4 h-4 text-white shrink-0 ml-2"
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
  modelValue: { type: [String, Number, Boolean, Object], default: '' },
  placeholder: { type: String, default: 'Select...' },
  searchable: { type: Boolean, default: false }
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

const selectedOption = computed(() => {
  return props.options.find(opt => String(opt.value) === String(props.modelValue));
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
  
  const spaceBelow = window.innerHeight - rect.bottom;
  const dropdownHeight = 240; // max-h-60 approx
  
  let top = rect.bottom + 4;
  if (spaceBelow < dropdownHeight && rect.top > dropdownHeight) {
    top = rect.top - dropdownHeight - 4;
  }

  floatingStyle.value = {
    position: 'fixed',
    top: `${top}px`,
    left: `${rect.left}px`,
    width: `${rect.width}px`,
    zIndex: '99999'
  };
};

const toggleOpen = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    searchQuery.value = '';
    updatePosition();
    if (props.searchable) {
      nextTick(() => {
        searchInputRef.value?.focus();
      });
    }
  }
};

const selectOption = (val) => {
  emit('update:modelValue', val);
  emit('change', val);
  isOpen.value = false;
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
