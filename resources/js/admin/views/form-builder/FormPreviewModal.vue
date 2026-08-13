<template>
  <div v-if="show" class="fixed inset-0 bg-black/75 backdrop-blur-xs flex items-center justify-center p-4 z-50 animate-in fade-in duration-200" @click.self="close">
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden transition-all text-left">
      
      <!-- Modal Header -->
      <div class="p-6 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center bg-zinc-50/50 dark:bg-zinc-900/50 shrink-0">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-2xl bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-black text-base shadow-xs shrink-0">
            <i class="fas fa-eye"></i>
          </div>
          <div>
            <h3 class="text-lg font-black text-zinc-950 dark:text-white tracking-tight">
              Form Render Preview
            </h3>
            <div class="flex flex-wrap items-center gap-2 mt-0.5">
              <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300">
                {{ formItem?.name || 'Custom Form' }}
              </span>
              <span v-if="formItem?.business_type" class="px-2 py-0.5 bg-zinc-200 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 rounded-md text-[10px] font-extrabold">
                <i :class="formItem.business_type.icon || 'fas fa-briefcase'" class="mr-1"></i>
                {{ formItem.business_type.name }}
              </span>
              <span class="px-2 py-0.5 bg-black text-white dark:bg-white dark:text-black rounded-md text-[10px] font-black uppercase">
                {{ formatAreaLabel(formItem?.area_of_use) }}
              </span>
            </div>
          </div>
        </div>

        <button @click="close" class="w-8 h-8 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 flex items-center justify-center transition-all cursor-pointer">
          <i class="fas fa-times text-xs"></i>
        </button>
      </div>

      <!-- Modal Body - Rendered Form Fields -->
      <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-6">
        
        <p v-if="formItem?.description" class="text-xs text-zinc-500 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-950/60 p-3.5 rounded-2xl border border-zinc-200 dark:border-zinc-800 font-semibold">
          <i class="fas fa-info-circle mr-1.5 text-zinc-400"></i>
          {{ formItem.description }}
        </p>

        <div v-if="!formFields || formFields.length === 0" class="py-12 text-center text-zinc-400">
          <i class="fas fa-cube text-3xl mb-2 text-zinc-300 dark:text-zinc-700"></i>
          <p class="text-xs font-bold uppercase tracking-wider">No fields configured for this form layout.</p>
        </div>

        <div v-else class="bg-zinc-50/50 dark:bg-zinc-950/40 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-800">
          <div class="mb-4 pb-3 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
            <span class="text-xs font-black text-zinc-900 dark:text-white uppercase tracking-wider">
              Rendered Transaction Fields ({{ formFields.length }})
            </span>
            <span class="text-[11px] text-zinc-400 font-medium">Interactive Preview</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div 
              v-for="field in formFields" 
              :key="field.id"
              :class="field.width === 'full' ? 'md:col-span-2' : 'md:col-span-1'"
            >
              <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1.5">
                {{ field.label }} <span v-if="field.required" class="text-rose-500">*</span>
              </label>

              <!-- Text Input -->
              <input 
                v-if="field.type === 'text'"
                type="text" 
                :placeholder="field.placeholder || 'Enter ' + field.label"
                class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold shadow-xs outline-none"
              >

              <!-- Number Input -->
              <input 
                v-else-if="field.type === 'number'"
                type="number" 
                :placeholder="field.placeholder || '0.00'"
                class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold shadow-xs outline-none"
              >

              <!-- Select Dropdown -->
              <select 
                v-else-if="field.type === 'select'"
                class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold shadow-xs outline-none cursor-pointer"
              >
                <option value="">Select {{ field.label }}...</option>
                <option v-for="opt in field.options" :key="opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>

              <!-- Toggle Switch -->
              <div v-else-if="field.type === 'toggle'" class="p-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl flex items-center justify-between">
                <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300">Enable {{ field.label }}</span>
                <div class="relative w-10 h-6 bg-black dark:bg-white rounded-full">
                  <div class="absolute left-1 top-1 w-4 h-4 bg-white dark:bg-zinc-900 rounded-full translate-x-4"></div>
                </div>
              </div>

              <!-- Textarea -->
              <textarea 
                v-else-if="field.type === 'textarea'"
                rows="2"
                :placeholder="field.placeholder"
                class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold shadow-xs outline-none"
              ></textarea>

              <span v-if="field.help_text" class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 mt-1 block">
                {{ field.help_text }}
              </span>
            </div>
          </div>
        </div>

      </div>

      <!-- Modal Footer -->
      <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 flex justify-end shrink-0">
        <button
          type="button"
          @click="close"
          class="px-6 py-2.5 bg-black hover:bg-zinc-800 text-white dark:bg-white dark:hover:bg-zinc-200 dark:text-black font-extrabold text-xs rounded-xl shadow-xs transition-all cursor-pointer"
        >
          Close Preview
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  formItem: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close']);

const formFields = computed(() => {
  if (!props.formItem) return [];
  return Array.isArray(props.formItem.fields) ? props.formItem.fields : [];
});

const formatAreaLabel = (areaVal) => {
  const labels = {
    sale_invoice: 'Sale invoice',
    sale_return: 'Sale Return',
    purchase_invoice: 'Purchase Invoice',
    purchase_return: 'Purchase Return',
    items: 'Items',
    expenses: 'Expenses',
    payment_out: 'Payment Out',
    payment_receipt: 'Payment Receipt'
  };
  return labels[areaVal] || areaVal;
};

const close = () => {
  emit('close');
};
</script>
