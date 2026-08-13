<template>
  <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200" style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);">
    <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-lg shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 p-6 transition-all duration-300 z-10 my-auto">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-base font-bold text-slate-900 dark:text-white">
          {{ isEditing ? 'Edit Department' : 'Create New Department' }}
        </h3>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="saveDepartment" class="space-y-4">
        <!-- Name -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Name *</label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
            placeholder="Enter department name"
          />
          <span v-if="errors.name" class="text-red-500 text-xs mt-1 block">{{ errors.name[0] }}</span>
        </div>

        <!-- Code -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Code *</label>
          <input
            v-model="form.code"
            type="text"
            required
            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
            placeholder="Enter department code"
          />
          <span v-if="errors.code" class="text-red-500 text-xs mt-1 block">{{ errors.code[0] }}</span>
        </div>

        <!-- Parent Department -->
        <div>
          <FloatingSelect
            v-model="form.parent_department_id"
            label="Parent Department"
            placeholder="No Parent (Top Level)"
            :options="parentDepartmentOptions"
            :error="!!errors.parent_department_id"
          />
          <span v-if="errors.parent_department_id" class="text-red-500 text-xs mt-1 block">{{ errors.parent_department_id[0] }}</span>
        </div>

        <!-- Manager -->
        <div>
          <FloatingSelect
            v-model="form.manager_id"
            label="Manager"
            placeholder="Select Manager"
            :options="managerSelectOptions"
            :error="!!errors.manager_id"
          />
          <span v-if="errors.manager_id" class="text-red-500 text-xs mt-1 block">{{ errors.manager_id[0] }}</span>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Description</label>
          <textarea
            v-model="form.description"
            rows="3"
            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
            placeholder="Enter department description"
          ></textarea>
          <span v-if="errors.description" class="text-red-500 text-xs mt-1 block">{{ errors.description[0] }}</span>
        </div>

        <!-- Active Status -->
        <div class="flex items-center">
          <input
            v-model="form.is_active"
            type="checkbox"
            class="h-4 w-4 text-slate-900 focus:ring-0 border-slate-300 dark:border-zinc-700 rounded"
          />
          <label class="ml-2 block text-xs font-semibold text-slate-900 dark:text-slate-100">Active</label>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3 pt-4 border-t border-slate-100 dark:border-zinc-800">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-800 cursor-pointer"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="saving"
            class="bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-semibold px-4 py-2 rounded-xl text-xs transition-all cursor-pointer shadow-xs disabled:opacity-50"
          >
            {{ saving ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import FloatingSelect from '@/components/common/FloatingSelect.vue';
import axios from 'axios';

// Props and Emits
const props = defineProps({
  department: {
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
  parent_department_id: '',
  manager_id: '',
  is_active: true
});

const departments = ref([]);
const employees = ref([]);
const errors = ref({});
const saving = ref(false);

// Computed
const isEditing = computed(() => !!props.department);

const availableParents = computed(() => {
  if (!isEditing.value) {
    return departments.value;
  }
  
  // Exclude current department and its children from parent options
  return departments.value.filter(dept => {
    if (dept.id === props.department.id) return false;
    // Add logic to exclude children if needed
    return true;
  });
});

const parentDepartmentOptions = computed(() => [
  { value: '', label: 'No Parent (Top Level)' },
  ...availableParents.value.map(d => ({ value: d.id, label: d.name }))
]);

const managerSelectOptions = computed(() => [
  { value: '', label: 'Select Manager' },
  ...employees.value.map(e => ({ value: e.id, label: e.full_name }))
]);

// Methods
const fetchDepartments = async () => {
  try {
    const response = await axios.get('/api/departments');
    departments.value = response.data;
  } catch (error) {
    console.error('Error fetching departments:', error);
  }
};

const fetchEmployees = async () => {
  try {
    const response = await axios.get('/api/employees/for-dropdown');
    employees.value = response.data;
  } catch (error) {
    console.error('Error fetching employees:', error);
    employees.value = [];
  }
};

const saveDepartment = async () => {
  saving.value = true;
  errors.value = {};

  try {
    let response;
    if (isEditing.value) {
      response = await axios.put(`/api/departments/${props.department.id}`, form.value);
    } else {
      response = await axios.post('/api/departments', form.value);
    }

    emit('saved');
    window.dispatchEvent(new CustomEvent('department-saved', { detail: response.data.department }));
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      console.error('Error saving department:', error);
    }
  } finally {
    saving.value = false;
  }
};

// Initialize form if editing
const initializeForm = () => {
  if (props.department) {
    Object.keys(form.value).forEach(key => {
      if (props.department[key] !== undefined) {
        form.value[key] = props.department[key];
      }
    });
  }
};

// Lifecycle
onMounted(async () => {
  await Promise.all([fetchDepartments(), fetchEmployees()]);
  initializeForm();
});
</script>
