<template>
  <div v-if="show" class="fixed inset-0 bg-black/75 backdrop-blur-xs flex items-center justify-center p-4 z-50 animate-in fade-in duration-200" @click.self="close">
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-2xl w-full max-w-5xl max-h-[92vh] flex flex-col overflow-hidden transition-all text-left">
      
      <!-- Header -->
      <div class="p-6 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center bg-zinc-50/50 dark:bg-zinc-900/50 shrink-0">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-2xl bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-black text-base shadow-xs shrink-0">
            <i class="fas fa-cubes-stacked"></i>
          </div>
          <div>
            <h3 class="text-lg font-black text-zinc-950 dark:text-white tracking-tight">
              {{ isEditing ? 'Edit Custom Form Layout' : 'Create Custom Dynamic Form' }}
            </h3>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold">
              Build dynamic custom fields for specific business types and operational areas.
            </p>
          </div>
        </div>

        <div class="flex items-center space-x-3">
          <!-- View Toggle: Builder vs Live Preview -->
          <div class="bg-zinc-200 dark:bg-zinc-800 p-1 rounded-xl flex items-center text-xs font-bold">
            <button
              type="button"
              @click="activeTab = 'builder'"
              class="px-3 py-1.5 rounded-lg transition-all cursor-pointer"
              :class="activeTab === 'builder' ? 'bg-white dark:bg-zinc-900 text-zinc-950 dark:text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-400'"
            >
              <i class="fas fa-sliders mr-1.5"></i> Form Builder
            </button>
            <button
              type="button"
              @click="activeTab = 'preview'"
              class="px-3 py-1.5 rounded-lg transition-all cursor-pointer"
              :class="activeTab === 'preview' ? 'bg-white dark:bg-zinc-900 text-zinc-950 dark:text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-400'"
            >
              <i class="fas fa-eye mr-1.5"></i> Live Preview
            </button>
          </div>

          <button @click="close" class="w-8 h-8 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 flex items-center justify-center transition-all cursor-pointer">
            <i class="fas fa-times text-xs"></i>
          </button>
        </div>
      </div>

      <!-- Main Body -->
      <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-6">
        
        <!-- Loading State -->
        <div v-if="loading" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
          <i class="fas fa-circle-notch fa-spin text-3xl mb-3 text-black dark:text-white"></i>
          <p class="text-xs font-bold uppercase tracking-wider">Loading Form Layout Details...</p>
        </div>

        <template v-else>
          <!-- Error Alert -->
          <div v-if="errorMessage" class="bg-rose-50 border border-rose-200 text-rose-700 dark:bg-rose-950/40 dark:border-rose-900 dark:text-rose-400 p-4 rounded-2xl text-xs font-bold flex items-start space-x-3">
            <i class="fas fa-exclamation-circle text-base shrink-0 mt-0.5"></i>
            <div>
              <p>{{ errorMessage }}</p>
              <ul v-if="validationErrors && Object.keys(validationErrors).length > 0" class="mt-2 list-disc list-inside space-y-1">
                <li v-for="(errors, field) in validationErrors" :key="field">
                  {{ errors[0] }}
                </li>
              </ul>
            </div>
          </div>

          <!-- Metadata Header Section -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-zinc-50 dark:bg-zinc-950/60 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800">
            
            <!-- Form Name -->
            <div class="md:col-span-1">
              <label class="block text-[11px] font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1.5">
                Form Title <span class="text-rose-500">*</span>
              </label>
              <input 
                type="text" 
                v-model="form.name" 
                required 
                class="w-full px-3.5 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold" 
                placeholder="e.g. Restaurant Sale Invoice Extra Fields"
              >
            </div>

            <!-- Business Type Selection -->
            <div>
              <label class="block text-[11px] font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1.5">
                Business Type
              </label>
              <select 
                v-model="form.business_type_id" 
                class="w-full px-3.5 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold cursor-pointer"
              >
                <option :value="null">Global Default (All Businesses)</option>
                <option v-for="type in metaOptions.business_types" :key="type.id" :value="type.id">
                  {{ type.name }}
                </option>
              </select>
            </div>

            <!-- Area of Use Selection -->
            <div>
              <label class="block text-[11px] font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1.5">
                Area of Use <span class="text-rose-500">*</span>
              </label>
              <select 
                v-model="form.area_of_use" 
                required
                class="w-full px-3.5 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold cursor-pointer"
              >
                <option v-for="area in metaOptions.areas_of_use" :key="area.value" :value="area.value">
                  {{ area.label }}
                </option>
              </select>
            </div>

          </div>

          <!-- Quick Preset Templates Bar -->
          <div class="flex flex-wrap items-center justify-between gap-3 bg-black/5 dark:bg-white/5 p-3 rounded-2xl border border-dashed border-zinc-300 dark:border-zinc-700">
            <div class="flex items-center space-x-2">
              <i class="fas fa-wand-magic-sparkles text-xs text-zinc-600 dark:text-zinc-400"></i>
              <span class="text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">Quick Templates:</span>
            </div>

            <div class="flex flex-wrap gap-2">
              <button 
                type="button" 
                @click="loadPreset('restaurant')"
                class="px-3 py-1.5 bg-zinc-900 hover:bg-black text-white dark:bg-white dark:hover:bg-zinc-200 dark:text-black text-xs font-extrabold rounded-xl transition-all shadow-xs cursor-pointer flex items-center"
              >
                <i class="fas fa-utensils mr-1.5 text-[10px]"></i> Restaurant Sale Invoice Preset
              </button>

              <button 
                type="button" 
                @click="loadPreset('commission_shop')"
                class="px-3 py-1.5 bg-zinc-900 hover:bg-black text-white dark:bg-white dark:hover:bg-zinc-200 dark:text-black text-xs font-extrabold rounded-xl transition-all shadow-xs cursor-pointer flex items-center"
              >
                <i class="fas fa-calculator mr-1.5 text-[10px]"></i> Commission Shop Preset
              </button>
            </div>
          </div>

          <!-- TAB 1: FORM BUILDER CANVAS -->
          <div v-if="activeTab === 'builder'" class="space-y-4">
            
            <!-- Toolbar Palette -->
            <div class="flex flex-wrap items-center justify-between gap-3 p-3 bg-zinc-100/80 dark:bg-zinc-800/60 rounded-2xl">
              <span class="text-xs font-black uppercase tracking-wider text-zinc-700 dark:text-zinc-300">Add Field to Form:</span>
              <div class="flex flex-wrap gap-1.5">
                <button
                  type="button"
                  v-for="fieldType in fieldTypesList"
                  :key="fieldType.type"
                  @click="addField(fieldType.type)"
                  class="px-3 py-1.5 bg-white hover:bg-zinc-200 dark:bg-zinc-900 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-white rounded-xl text-xs font-bold transition-all shadow-2xs flex items-center cursor-pointer"
                >
                  <i :class="fieldType.icon + ' mr-1.5 text-zinc-500'"></i> {{ fieldType.label }}
                </button>
              </div>
            </div>

            <!-- Empty Fields Canvas -->
            <div v-if="form.fields.length === 0" class="py-12 border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-3xl text-center text-zinc-400">
              <i class="fas fa-cube text-4xl mb-3 text-zinc-300 dark:text-zinc-700"></i>
              <p class="text-xs font-extrabold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">No fields added yet</p>
              <p class="text-[11px] text-zinc-400 mt-1">Click any field type above or use Quick Templates to populate fields.</p>
            </div>

            <!-- Fields Cards List -->
            <div v-else class="space-y-3">
              <div 
                v-for="(field, index) in form.fields" 
                :key="field.id || index"
                class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs hover:border-zinc-400 dark:hover:border-zinc-600 transition-all space-y-3"
              >
                <!-- Field Card Header -->
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                  <div class="flex items-center space-x-2">
                    <span class="w-6 h-6 rounded-lg bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-black text-[11px]">
                      {{ index + 1 }}
                    </span>
                    <span class="font-extrabold text-xs text-zinc-900 dark:text-white uppercase tracking-wider">
                      {{ field.label || 'Untitled Field' }}
                    </span>
                    <span class="px-2 py-0.5 bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 rounded-md text-[10px] font-mono">
                      {{ field.type }}
                    </span>
                  </div>

                  <!-- Field Action Controls -->
                  <div class="flex items-center space-x-1.5">
                    <button 
                      type="button" 
                      @click="moveField(index, -1)" 
                      :disabled="index === 0"
                      class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 disabled:opacity-30 transition-all flex items-center justify-center cursor-pointer"
                      title="Move Up"
                    >
                      <i class="fas fa-arrow-up text-[10px]"></i>
                    </button>
                    <button 
                      type="button" 
                      @click="moveField(index, 1)" 
                      :disabled="index === form.fields.length - 1"
                      class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 disabled:opacity-30 transition-all flex items-center justify-center cursor-pointer"
                      title="Move Down"
                    >
                      <i class="fas fa-arrow-down text-[10px]"></i>
                    </button>
                    <button 
                      type="button" 
                      @click="removeField(index)" 
                      class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900 text-rose-600 dark:text-rose-400 transition-all flex items-center justify-center cursor-pointer"
                      title="Remove Field"
                    >
                      <i class="fas fa-trash-alt text-[10px]"></i>
                    </button>
                  </div>
                </div>

                <!-- Field Properties Form -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
                  <!-- Label -->
                  <div>
                    <label class="block text-[10px] font-extrabold uppercase text-zinc-500 mb-1">Field Label</label>
                    <input 
                      type="text" 
                      v-model="field.label" 
                      @input="onLabelChange(field)"
                      class="w-full px-3 py-1.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:border-black dark:focus:border-white outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white font-bold text-xs" 
                      placeholder="e.g. Table Number"
                    >
                  </div>

                  <!-- Key / System Name -->
                  <div>
                    <label class="block text-[10px] font-extrabold uppercase text-zinc-500 mb-1">Field Key (System ID)</label>
                    <input 
                      type="text" 
                      v-model="field.name" 
                      class="w-full px-3 py-1.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:border-black dark:focus:border-white outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white font-mono text-[11px]" 
                      placeholder="table_number"
                    >
                  </div>

                  <!-- Type Selector -->
                  <div>
                    <label class="block text-[10px] font-extrabold uppercase text-zinc-500 mb-1">Input Type</label>
                    <select 
                      v-model="field.type" 
                      class="w-full px-3 py-1.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:border-black dark:focus:border-white outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white font-bold text-xs cursor-pointer"
                    >
                      <option value="text">Short Text</option>
                      <option value="number">Number</option>
                      <option value="select">Dropdown Select</option>
                      <option value="toggle">Toggle Switch</option>
                      <option value="textarea">Textarea</option>
                      <option value="date">Date</option>
                    </select>
                  </div>

                  <!-- Width Selector -->
                  <div>
                    <label class="block text-[10px] font-extrabold uppercase text-zinc-500 mb-1">Layout Width</label>
                    <select 
                      v-model="field.width" 
                      class="w-full px-3 py-1.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:border-black dark:focus:border-white outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white font-bold text-xs cursor-pointer"
                    >
                      <option value="full">Full Width (100%)</option>
                      <option value="half">Half Width (50%)</option>
                    </select>
                  </div>
                </div>

                <!-- Secondary Properties -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-2">
                  <!-- Help Text -->
                  <div class="md:col-span-2">
                    <label class="block text-[10px] font-extrabold uppercase text-zinc-500 mb-1">Help Subtitle / Hint</label>
                    <input 
                      type="text" 
                      v-model="field.help_text" 
                      class="w-full px-3 py-1.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:border-black dark:focus:border-white outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-semibold" 
                      placeholder="e.g. Enter guest count for seating"
                    >
                  </div>

                  <!-- Required Checkbox -->
                  <div class="flex items-center pt-4">
                    <label class="flex items-center space-x-2 cursor-pointer select-none">
                      <input 
                        type="checkbox" 
                        v-model="field.required" 
                        class="w-4 h-4 text-black rounded border-zinc-300 focus:ring-0 cursor-pointer"
                      >
                      <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Required Field</span>
                    </label>
                  </div>
                </div>

                <!-- Dropdown Options Manager (Only for select fields) -->
                <div v-if="field.type === 'select'" class="bg-zinc-50 dark:bg-zinc-950/60 p-3 rounded-xl border border-zinc-200/80 dark:border-zinc-800 space-y-2 mt-2">
                  <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">Dropdown Options:</span>
                    <button 
                      type="button" 
                      @click="addOption(field)" 
                      class="px-2.5 py-1 bg-black text-white dark:bg-white dark:text-black rounded-lg text-[10px] font-extrabold cursor-pointer"
                    >
                      + Add Option
                    </button>
                  </div>

                  <div class="space-y-1.5">
                    <div 
                      v-for="(opt, optIdx) in field.options" 
                      :key="optIdx"
                      class="flex items-center space-x-2"
                    >
                      <input 
                        type="text" 
                        v-model="opt.label" 
                        @input="opt.value = opt.label"
                        class="flex-1 px-3 py-1 border border-zinc-200 dark:border-zinc-800 rounded-lg text-xs font-bold bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white outline-none" 
                        placeholder="Option Label"
                      >
                      <button 
                        type="button" 
                        @click="removeOption(field, optIdx)" 
                        class="w-6 h-6 text-rose-500 hover:text-rose-700 flex items-center justify-center cursor-pointer"
                      >
                        <i class="fas fa-times text-xs"></i>
                      </button>
                    </div>
                  </div>
                </div>

              </div>
            </div>

          </div>

          <!-- TAB 2: LIVE PREVIEW TAB -->
          <div v-else-if="activeTab === 'preview'" class="bg-zinc-50 dark:bg-zinc-950/60 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-800 space-y-6">
            <div class="border-b border-zinc-200 dark:border-zinc-800 pb-4">
              <h4 class="text-sm font-black text-zinc-950 dark:text-white uppercase tracking-wider">
                Form Live Preview
              </h4>
              <p class="text-xs text-zinc-500 dark:text-zinc-400">
                This is how the custom form fields will render in {{ metaOptions.areas_of_use.find(a => a.value === form.area_of_use)?.label || 'Invoice' }} screens.
              </p>
            </div>

            <div v-if="form.fields.length === 0" class="py-12 text-center text-zinc-400">
              <p class="text-xs font-bold uppercase tracking-wider">No fields to preview</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div 
                v-for="field in form.fields" 
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
        </template>

      </div>

      <!-- Footer -->
      <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 flex justify-end space-x-3 shrink-0">
        <button
          type="button"
          @click="close"
          class="px-5 py-2.5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 font-extrabold text-xs rounded-xl transition-all cursor-pointer"
        >
          Cancel
        </button>

        <button
          type="button"
          @click="submitForm"
          :disabled="submitting || loading"
          class="px-6 py-2.5 bg-black hover:bg-zinc-800 text-white dark:bg-white dark:hover:bg-zinc-200 dark:text-black font-extrabold text-xs rounded-xl shadow-xs transition-all disabled:opacity-50 flex items-center cursor-pointer"
        >
          <i v-if="submitting" class="fas fa-spinner fa-spin mr-2"></i>
          <i v-else class="fas fa-save mr-2"></i>
          {{ isEditing ? 'Save Changes' : 'Save Custom Form' }}
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  formId: {
    type: [Number, String],
    default: null
  }
});

const emit = defineEmits(['close', 'saved']);

const isEditing = computed(() => !!props.formId);
const loading = ref(false);
const submitting = ref(false);
const errorMessage = ref('');
const validationErrors = ref({});
const activeTab = ref('builder');

const metaOptions = ref({
  areas_of_use: [
    { value: 'sale_invoice', label: 'Sale invoice' },
    { value: 'sale_return', label: 'Sale Return' },
    { value: 'purchase_invoice', label: 'Purchase Invoice' },
    { value: 'purchase_return', label: 'Purchase Return' },
    { value: 'items', label: 'Items' },
    { value: 'expenses', label: 'Expenses' },
    { value: 'payment_out', label: 'Payment Out' },
    { value: 'payment_receipt', label: 'Payment Receipt' }
  ],
  business_types: []
});

const fieldTypesList = [
  { type: 'text', label: 'Short Text', icon: 'fas fa-font' },
  { type: 'number', label: 'Number', icon: 'fas fa-hashtag' },
  { type: 'select', label: 'Dropdown Select', icon: 'fas fa-list-ul' },
  { type: 'toggle', label: 'Toggle Switch', icon: 'fas fa-toggle-on' },
  { type: 'textarea', label: 'Long Text', icon: 'fas fa-align-left' },
  { type: 'date', label: 'Date', icon: 'fas fa-calendar-alt' }
];

const form = ref({
  name: '',
  business_type_id: null,
  area_of_use: 'sale_invoice',
  description: '',
  is_active: true,
  sort_order: 0,
  fields: []
});

const resetForm = () => {
  form.value = {
    name: '',
    business_type_id: null,
    area_of_use: 'sale_invoice',
    description: '',
    is_active: true,
    sort_order: 0,
    fields: []
  };
  errorMessage.value = '';
  validationErrors.value = {};
  activeTab.value = 'builder';
};

const fetchMetaOptions = async () => {
  try {
    const { data } = await axios.get('/admin/api/custom-forms/meta-options');
    if (data.areas_of_use) metaOptions.value.areas_of_use = data.areas_of_use;
    if (data.business_types) metaOptions.value.business_types = data.business_types;
  } catch (e) {
    console.error("Failed to load meta options", e);
  }
};

const loadForm = async () => {
  if (!props.formId) return;
  loading.value = true;
  errorMessage.value = '';
  try {
    const { data } = await axios.get(`/admin/api/custom-forms/${props.formId}`);
    const item = data.data;
    form.value.name = item.name || '';
    form.value.business_type_id = item.business_type_id || null;
    form.value.area_of_use = item.area_of_use || 'sale_invoice';
    form.value.description = item.description || '';
    form.value.is_active = Boolean(item.is_active);
    form.value.sort_order = item.sort_order ?? 0;
    form.value.fields = Array.isArray(item.fields) ? item.fields : [];
  } catch (e) {
    console.error("Failed to load custom form", e);
    errorMessage.value = 'Failed to load custom form layout.';
  } finally {
    loading.value = false;
  }
};

const addField = (type) => {
  const newId = 'field_' + Date.now();
  form.value.fields.push({
    id: newId,
    name: 'custom_field_' + (form.value.fields.length + 1),
    label: 'New ' + type.toUpperCase() + ' Field',
    type: type,
    width: 'half',
    required: false,
    placeholder: '',
    help_text: '',
    options: type === 'select' ? [{ label: 'Option 1', value: 'Option 1' }] : []
  });
};

const removeField = (index) => {
  form.value.fields.splice(index, 1);
};

const moveField = (index, delta) => {
  const targetIndex = index + delta;
  if (targetIndex < 0 || targetIndex >= form.value.fields.length) return;
  const temp = form.value.fields[index];
  form.value.fields[index] = form.value.fields[targetIndex];
  form.value.fields[targetIndex] = temp;
};

const onLabelChange = (field) => {
  if (field.label) {
    field.name = field.label.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
  }
};

const addOption = (field) => {
  if (!field.options) field.options = [];
  const nextNum = field.options.length + 1;
  field.options.push({ label: 'Option ' + nextNum, value: 'Option ' + nextNum });
};

const removeOption = (field, optIdx) => {
  field.options.splice(optIdx, 1);
};

// Preset Loaders
const loadPreset = (presetType) => {
  if (presetType === 'restaurant') {
    const rType = metaOptions.value.business_types.find(b => b.name.includes('Restaurant') || b.slug.includes('restaurant'));
    if (rType) form.value.business_type_id = rType.id;
    form.value.name = 'Restaurant Sale Invoice Dynamic Fields';
    form.value.area_of_use = 'sale_invoice';
    form.value.fields = [
      {
        id: 'table_assignment',
        name: 'table_assignment',
        label: 'Table Assignment (for Dine-in)',
        type: 'select',
        width: 'half',
        required: false,
        placeholder: 'Select Table',
        help_text: 'Required for dine-in seating tracking',
        options: [
          { label: 'Table 01', value: 'Table 01' },
          { label: 'Table 02', value: 'Table 02' },
          { label: 'Table 03', value: 'Table 03' },
          { label: 'VIP Table A', value: 'VIP Table A' }
        ]
      },
      {
        id: 'guest_count',
        name: 'guest_count',
        label: 'Guest Count',
        type: 'number',
        width: 'half',
        required: true,
        placeholder: 'Number of guests',
        help_text: 'Total number of guests at table'
      },
      {
        id: 'waiter_id',
        name: 'waiter_id',
        label: 'Waiter / Server Identification',
        type: 'text',
        width: 'half',
        required: true,
        placeholder: 'Server name or ID',
        help_text: 'Assigned server identification'
      },
      {
        id: 'order_type',
        name: 'order_type',
        label: 'Order Type',
        type: 'select',
        width: 'half',
        required: true,
        help_text: 'Type of order fulfillment',
        options: [
          { label: 'Dine-in', value: 'Dine-in' },
          { label: 'Takeaway', value: 'Takeaway' },
          { label: 'Delivery', value: 'Delivery' }
        ]
      },
      {
        id: 'order_status',
        name: 'order_status',
        label: 'Order Status Tracking',
        type: 'select',
        width: 'half',
        required: true,
        help_text: 'Current kitchen / service status',
        options: [
          { label: 'Taken', value: 'Taken' },
          { label: 'Ready', value: 'Ready' },
          { label: 'Served', value: 'Served' },
          { label: 'Re-order', value: 'Re-order' },
          { label: 'Completed', value: 'Completed' }
        ]
      },
      {
        id: 'kot_number',
        name: 'kot_number',
        label: 'KOT Numbering',
        type: 'text',
        width: 'half',
        required: false,
        placeholder: 'KOT ticket #',
        help_text: 'Kitchen order ticket reference'
      }
    ];
  } else if (presetType === 'commission_shop') {
    const cType = metaOptions.value.business_types.find(b => b.name.includes('Wholesale') || b.name.includes('Commission'));
    if (cType) form.value.business_type_id = cType.id;
    form.value.name = 'Commission Shop Sale Invoice Dynamic Fields';
    form.value.area_of_use = 'sale_invoice';
    form.value.fields = [
      {
        id: 'commission_type',
        name: 'commission_type',
        label: 'Commission Calculation Type',
        type: 'select',
        width: 'half',
        required: true,
        help_text: 'Choose percentage rate or fixed fee calculation',
        options: [
          { label: 'Percentage (%)', value: 'Percentage (%)' },
          { label: 'Fixed Amount', value: 'Fixed Amount' }
        ]
      },
      {
        id: 'commission_rate',
        name: 'commission_rate',
        label: 'Commission Rate / Amount',
        type: 'number',
        width: 'half',
        required: true,
        placeholder: '5.00',
        help_text: 'Commission percentage or fixed value'
      },
      {
        id: 'auto_fee_application',
        name: 'auto_fee_application',
        label: 'Automated Fee Application',
        type: 'toggle',
        width: 'full',
        required: false,
        help_text: 'Automatically apply commission & fees'
      },
      {
        id: 'loading_charge',
        name: 'loading_charge',
        label: 'Loading / Unloading Charge',
        type: 'number',
        width: 'half',
        required: false,
        placeholder: '0.00',
        help_text: 'Loading & unloading fees'
      },
      {
        id: 'labor_charge',
        name: 'labor_charge',
        label: 'Labor Charge',
        type: 'number',
        width: 'half',
        required: false,
        placeholder: '0.00',
        help_text: 'Labor handling charges'
      },
      {
        id: 'packing_charge',
        name: 'packing_charge',
        label: 'Packing Charge',
        type: 'number',
        width: 'half',
        required: false,
        placeholder: '0.00',
        help_text: 'Packaging and bagging costs'
      },
      {
        id: 'transport_charge',
        name: 'transport_charge',
        label: 'Transportation Charge',
        type: 'number',
        width: 'half',
        required: false,
        placeholder: '0.00',
        help_text: 'Carriage delivery fees'
      }
    ];
  }
};

const close = () => {
  resetForm();
  emit('close');
};

const submitForm = async () => {
  if (submitting.value) return;

  if (!form.value.name || !form.value.name.trim()) {
    errorMessage.value = 'Form Title is required.';
    return;
  }

  if (form.value.fields.length === 0) {
    errorMessage.value = 'Please add at least one field to the custom form.';
    return;
  }

  submitting.value = true;
  errorMessage.value = '';
  validationErrors.value = {};

  try {
    const payload = {
      ...form.value,
      is_active: Boolean(form.value.is_active)
    };

    if (isEditing.value) {
      await axios.put(`/admin/api/custom-forms/${props.formId}`, payload);
    } else {
      await axios.post('/admin/api/custom-forms', payload);
    }

    emit('saved');
    close();
  } catch (e) {
    if (e.response && e.response.status === 422) {
      errorMessage.value = 'Please correct validation errors.';
      validationErrors.value = e.response.data.errors;
    } else {
      errorMessage.value = e.response?.data?.message || 'An error occurred while saving form.';
    }
  } finally {
    submitting.value = false;
  }
};

watch(() => props.show, (newVal) => {
  if (newVal) {
    resetForm();
    fetchMetaOptions();
    if (props.formId) {
      loadForm();
    }
  }
});

watch(() => props.formId, (newVal) => {
  if (props.show && newVal) {
    loadForm();
  }
});

onMounted(() => {
  fetchMetaOptions();
});
</script>
