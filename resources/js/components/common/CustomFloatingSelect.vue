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
      <span :class="selectedOption ? 'font-semibold text-slate-900 dark:text-zinc-100' : 'text-slate-400'">
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

    <!-- Floating Options Menu -->
    <div
      v-if="isOpen"
      class="absolute left-0 top-full mt-1.5 w-full bg-white dark:bg-zinc-900 border border-slate-200/90 dark:border-zinc-800 rounded-2xl shadow-2xl z-50 overflow-hidden max-h-56 overflow-y-auto divide-y divide-slate-100 dark:divide-zinc-800/60 transition-all animate-in fade-in zoom-in-95"
    >
      <div
        v-for="option in options"
        :key="option.value"
        @click="selectOption(option.value)"
        :class="[
          'p-2.5 px-3.5 text-xs cursor-pointer transition-all flex items-center justify-between',
          String(modelValue) === String(option.value)
            ? 'border-l-4 border-indigo-600 bg-indigo-50/70 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold'
            : 'text-slate-700 dark:text-zinc-300 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/30 hover:text-indigo-600 dark:hover:text-indigo-400'
        ]"
      >
        <span>{{ option.label }}</span>
        <svg
          v-if="String(modelValue) === String(option.value)"
          class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0 ml-2"
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
        </svg>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  label: { type: String, default: '' },
  options: { type: Array, default: () => [] },
  modelValue: { type: [String, Number, Boolean, Object], default: '' },
  placeholder: { type: String, default: 'Select...' }
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const containerRef = ref(null);

const selectedOption = computed(() => {
  return props.options.find(opt => String(opt.value) === String(props.modelValue));
});

const toggleOpen = () => {
  isOpen.value = !isOpen.value;
};

const selectOption = (val) => {
  emit('update:modelValue', val);
  emit('change', val);
  isOpen.value = false;
};

const handleClickOutside = (e) => {
  if (containerRef.value && !containerRef.value.contains(e.target)) {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleClickOutside);
});
</script>
