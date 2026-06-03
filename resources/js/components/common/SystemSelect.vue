<template>
  <Listbox v-model="selected" :disabled="disabled" as="div" class="relative">
    <ListboxLabel v-if="label" class="block text-xs font-semibold mb-2 ml-1" :class="labelColor">
      {{ label }}
    </ListboxLabel>
    
    <div class="relative group">
      <ListboxButton
        class="relative w-full pl-5 pr-12 py-4 text-left bg-gray-50/50 border border-gray-100 rounded-2xl cursor-pointer focus:outline-none focus:ring-2 transition-all duration-300 font-medium text-[14px] text-gray-700 shadow-inner group-hover:bg-white"
        :class="[
          focusColor || 'focus:ring-indigo-500', 
          error ? 'border-red-300 ring-red-500' : '',
          disabled ? 'opacity-50 cursor-not-allowed' : ''
        ]"
      >
        <span class="block whitespace-nowrap overflow-hidden text-ellipsis pr-2">
          {{ selectedOption?.label || placeholder || 'Select option' }}
        </span>
        <span class="absolute inset-y-0 right-0 flex items-center pr-5 pointer-events-none">
          <svg 
            class="w-5 h-5 text-gray-400 transition-all duration-300 group-hover:scale-110" 
            :class="iconColor || 'group-hover:text-indigo-500'"
            fill="none" stroke="currentColor" viewBox="0 0 24 24"
          >
            <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/>
          </svg>
        </span>
      </ListboxButton>

      <transition
        leave-active-class="transition duration-100 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 scale-95 translate-y-[-10px]"
        enter-to-class="opacity-100 scale-100 translate-y-0"
      >
        <ListboxOptions
          class="absolute z-[100] mt-3 min-w-full w-max max-h-64 overflow-auto rounded-[24px] bg-white/95 backdrop-blur-xl py-2 shadow-[0_20px_50px_rgba(0,0,0,0.15)] ring-1 ring-black/5 focus:outline-none custom-scrollbar border border-white/40"
        >
          <ListboxOption
            v-slot="{ active, selected: isSelected }"
            v-for="option in options"
            :key="option.value"
            :value="option.value"
            as="template"
          >
            <li
              :class="[
                active ? 'bg-indigo-50/80 text-indigo-900 border-l-4 border-indigo-600' : 'text-gray-700 border-l-4 border-transparent',
                'relative cursor-pointer select-none py-3 px-6 transition-all duration-200 font-medium text-[14px]'
              ]"
            >
              <div class="flex items-center justify-between gap-4">
                <span :class="[isSelected ? 'text-indigo-600 translate-x-1' : '', 'block whitespace-nowrap transition-transform duration-300']">
                  {{ option.label }}
                </span>
                <span v-if="isSelected" :class="[active ? 'text-indigo-600' : 'text-indigo-500', 'flex items-center shrink-0']">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                  </svg>
                </span>
              </div>
            </li>
          </ListboxOption>
        </ListboxOptions>
      </transition>
    </div>
  </Listbox>
</template>

<script setup>
import { computed } from 'vue';
import {
  Listbox,
  ListboxLabel,
  ListboxButton,
  ListboxOptions,
  ListboxOption,
} from '@headlessui/vue';

const props = defineProps({
  modelValue: [String, Number, Boolean],
  options: {
    type: Array,
    required: true,
    // Expected: [{ label: 'Option 1', value: 'v1' }]
  },
  label: String,
  labelColor: {
    type: String,
    default: 'text-indigo-600'
  },
  placeholder: String,
  disabled: Boolean,
  error: Boolean,
  focusColor: String,
  iconColor: String
});

const emit = defineEmits(['update:modelValue']);

const selected = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
});

const selectedOption = computed(() => {
  return props.options.find(opt => opt.value === props.modelValue);
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}

/* Glass effect animations */
.animate-in {
  animation: slideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px) scale(0.98);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}
</style>
