<template>
  <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200" style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);">
    <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-lg shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 p-6 transition-all duration-300 z-10 my-auto">
      
      <!-- Header -->
      <div class="flex justify-between items-center pb-4 mb-5 border-b border-slate-100 dark:border-zinc-800">
        <div>
          <h3 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">
            {{ isEditing ? 'Edit Category' : 'Create New Category' }}
          </h3>
          <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
            {{ isEditing ? 'Update category details below' : 'Add a new expense category to categorize spending' }}
          </p>
        </div>
        <button
          @click="$emit('close')"
          class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="saveCategory" class="space-y-4">
        <!-- Name -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
            Category Name <span class="text-rose-500">*</span>
          </label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
            placeholder="Enter category name"
          />
          <span v-if="errors.name" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.name[0] }}</span>
        </div>

        <!-- Code -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
            Category Code
          </label>
          <input
            v-model="form.code"
            type="text"
            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
            placeholder="e.g., EXP-UTIL, EXP-OFFICE"
          />
          <span v-if="errors.code" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.code[0] }}</span>
        </div>

        <!-- Parent Category (Floating Dropdown) -->
        <div>
          <CustomFloatingSelect
            v-model="form.parent_category_id"
            label="Parent Category"
            placeholder="No Parent (Top Level)"
            :options="parentCategoryOptions"
            :searchable="true"
          />
          <span v-if="errors.parent_category_id" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.parent_category_id[0] }}</span>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
            Description
          </label>
          <textarea
            v-model="form.description"
            rows="3"
            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs"
            placeholder="Enter category description"
          ></textarea>
          <span v-if="errors.description" class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ errors.description[0] }}</span>
        </div>

        <!-- Active Toggle (Black/White in Light, Emerald/Zinc in Dark) -->
        <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 dark:bg-zinc-800/50 border border-slate-200/80 dark:border-zinc-700/80">
          <div>
            <span class="text-xs font-bold text-slate-900 dark:text-white">Active Category</span>
            <p class="text-[11px] text-slate-500 dark:text-zinc-400">Categories marked active can be assigned to new expenses.</p>
          </div>
          <button
            type="button"
            @click="form.is_active = !form.is_active"
            :class="[
              'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none',
              form.is_active ? 'bg-slate-900 dark:bg-emerald-500' : 'bg-slate-200 dark:bg-zinc-700'
            ]"
          >
            <span
              :class="[
                'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out',
                form.is_active ? 'translate-x-5' : 'translate-x-0'
              ]"
            />
          </button>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-2.5 pt-4 border-t border-slate-100 dark:border-zinc-800">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2.5 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="saving"
            class="px-5 py-2.5 bg-slate-900 hover:bg-black text-white dark:bg-white dark:hover:bg-slate-100 dark:text-slate-900 rounded-xl text-xs font-extrabold shadow-sm transition-all cursor-pointer disabled:opacity-50 flex items-center gap-2"
          >
            <span v-if="saving">Saving...</span>
            <span v-else>{{ isEditing ? 'Update Category' : 'Create Category' }}</span>
          </button>
        </div>
      </form>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import CustomFloatingSelect from '@/components/common/CustomFloatingSelect.vue';

// Props and Emits
const props = defineProps({
  category: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'saved']);

// Reactive data
const form = ref({
  name: '',
  code: '',
  description: '',
  parent_category_id: '',
  is_active: true
});

const categories = ref([]);
const errors = ref({});
const saving = ref(false);

const isEditing = computed(() => !!props.category);

const parentCategoryOptions = computed(() => {
  const list = categories.value.filter(cat => {
    if (isEditing.value && cat.id === props.category.id) return false;
    return true;
  });
  return [
    { value: '', label: 'No Parent (Top Level)' },
    ...list.map(cat => ({ value: cat.id, label: cat.name }))
  ];
});

const fetchCategories = async () => {
  try {
    const response = await axios.get('/api/expense-categories');
    categories.value = response.data;
  } catch (error) {
    console.error('Error fetching categories:', error);
  }
};

const saveCategory = async () => {
  saving.value = true;
  errors.value = {};

  try {
    if (isEditing.value) {
      await axios.put(`/api/expense-categories/${props.category.id}`, form.value);
    } else {
      await axios.post('/api/expense-categories', form.value);
    }

    emit('saved');
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      console.error('Error saving category:', error);
    }
  } finally {
    saving.value = false;
  }
};

const initializeForm = () => {
  if (props.category) {
    Object.keys(form.value).forEach(key => {
      if (props.category[key] !== undefined) {
        form.value[key] = props.category[key];
      }
    });
  }
};

onMounted(() => {
  fetchCategories();
  initializeForm();
});
</script>
