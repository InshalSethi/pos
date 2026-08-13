<template>
  <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200" style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);">
    <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-2xl shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 p-6 transition-all duration-300 z-10 max-h-[90vh] overflow-y-auto my-auto">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-base font-bold text-slate-900 dark:text-white">
          {{ isEditing ? 'Edit Position' : 'Create New Position' }}
        </h3>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="savePosition" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Title -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Title *</label>
            <input
              v-model="form.title"
              type="text"
              required
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
              placeholder="Enter position title"
            />
            <span v-if="errors.title" class="text-red-500 text-xs mt-1 block">{{ errors.title[0] }}</span>
          </div>

          <!-- Code -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Code *</label>
            <input
              v-model="form.code"
              type="text"
              required
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
              placeholder="Enter position code"
            />
            <span v-if="errors.code" class="text-red-500 text-xs mt-1 block">{{ errors.code[0] }}</span>
          </div>

          <!-- Department -->
          <div>
            <FloatingSelect
              v-model="form.department_id"
              label="Department"
              placeholder="Select Department"
              :options="departmentSelectOptions"
              :error="!!errors.department_id"
            />
            <span v-if="errors.department_id" class="text-red-500 text-xs mt-1 block">{{ errors.department_id[0] }}</span>
          </div>

          <!-- Level -->
          <div>
            <FloatingSelect
              v-model="form.level"
              label="Level"
              placeholder="Select Level"
              :options="levelOptions"
              required
              :error="!!errors.level"
            />
            <span v-if="errors.level" class="text-red-500 text-xs mt-1 block">{{ errors.level[0] }}</span>
          </div>

          <!-- Min Salary -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Minimum Salary ({{ currencySymbol }})</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400 dark:text-zinc-500 pointer-events-none">
                {{ currencySymbol }}
              </span>
              <input
                v-model="form.min_salary"
                type="number"
                step="0.01"
                min="0"
                class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
                placeholder="0.00"
              />
            </div>
            <span v-if="errors.min_salary" class="text-red-500 text-xs mt-1 block">{{ errors.min_salary[0] }}</span>
          </div>

          <!-- Max Salary -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Maximum Salary ({{ currencySymbol }})</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400 dark:text-zinc-500 pointer-events-none">
                {{ currencySymbol }}
              </span>
              <input
                v-model="form.max_salary"
                type="number"
                step="0.01"
                min="0"
                class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
                placeholder="0.00"
              />
            </div>
            <span v-if="errors.max_salary" class="text-red-500 text-xs mt-1 block">{{ errors.max_salary[0] }}</span>
          </div>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Description</label>
          <textarea
            v-model="form.description"
            rows="3"
            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
            placeholder="Enter position description"
          ></textarea>
          <span v-if="errors.description" class="text-red-500 text-xs mt-1 block">{{ errors.description[0] }}</span>
        </div>

        <!-- Requirements -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Requirements</label>
          <textarea
            v-model="form.requirements"
            rows="3"
            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
            placeholder="Enter position requirements"
          ></textarea>
          <span v-if="errors.requirements" class="text-red-500 text-xs mt-1 block">{{ errors.requirements[0] }}</span>
        </div>

        <!-- Responsibilities -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Responsibilities</label>
          <textarea
            v-model="form.responsibilities"
            rows="3"
            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
            placeholder="Enter position responsibilities"
          ></textarea>
          <span v-if="errors.responsibilities" class="text-red-500 text-xs mt-1 block">{{ errors.responsibilities[0] }}</span>
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
import { ref, onMounted, computed } from 'vue';
import FloatingSelect from '@/components/common/FloatingSelect.vue';
import { useCurrencyStore } from '@/stores/currency';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const currencyStore = useCurrencyStore();
const authStore = useAuthStore();

const currencySymbol = computed(() => {
  return currencyStore.symbol || authStore.user?.company?.currency_symbol || authStore.user?.company?.currency || 'Rs.';
});

const levelOptions = [
  { value: '', label: 'Select Level' },
  { value: 'entry', label: 'Entry' },
  { value: 'junior', label: 'Junior' },
  { value: 'mid', label: 'Mid' },
  { value: 'senior', label: 'Senior' },
  { value: 'lead', label: 'Lead' },
  { value: 'manager', label: 'Manager' },
  { value: 'director', label: 'Director' },
  { value: 'executive', label: 'Executive' }
];

// Props and Emits
const props = defineProps({
  position: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'saved']);

// Reactive data
const form = ref({
  title: '',
  code: '',
  description: '',
  department_id: '',
  level: '',
  min_salary: '',
  max_salary: '',
  requirements: '',
  responsibilities: '',
  is_active: true
});

const departments = ref([]);
const errors = ref({});
const saving = ref(false);

// Computed
const isEditing = computed(() => !!props.position);

const departmentSelectOptions = computed(() => [
  { value: '', label: 'Select Department' },
  ...departments.value.map(d => ({ value: d.id, label: d.name }))
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

const savePosition = async () => {
  saving.value = true;
  errors.value = {};

  try {
    let response;
    if (isEditing.value) {
      response = await axios.put(`/api/positions/${props.position.id}`, form.value);
    } else {
      response = await axios.post('/api/positions', form.value);
    }

    emit('saved');
    window.dispatchEvent(new CustomEvent('position-saved', { detail: response.data.position }));
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      console.error('Error saving position:', error);
    }
  } finally {
    saving.value = false;
  }
};

// Initialize form if editing
const initializeForm = () => {
  if (props.position) {
    Object.keys(form.value).forEach(key => {
      if (props.position[key] !== undefined) {
        form.value[key] = props.position[key];
      }
    });
  }
};

// Lifecycle
onMounted(async () => {
  currencyStore.fetchCurrencies();
  await fetchDepartments();
  initializeForm();
});
</script>
