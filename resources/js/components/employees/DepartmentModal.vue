<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
      <!-- Header -->
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-900">
          {{ isEditing ? 'Edit Department' : 'Create New Department' }}
        </h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="saveDepartment" class="space-y-4">
        <!-- Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Enter department name"
          />
          <span v-if="errors.name" class="text-red-500 text-sm">{{ errors.name[0] }}</span>
        </div>

        <!-- Code -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Code *</label>
          <input
            v-model="form.code"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Enter department code"
          />
          <span v-if="errors.code" class="text-red-500 text-sm">{{ errors.code[0] }}</span>
        </div>

        <!-- Parent Department -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Parent Department</label>
          <select
            v-model="form.parent_department_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">No Parent (Top Level)</option>
            <option v-for="department in availableParents" :key="department.id" :value="department.id">
              {{ department.name }}
            </option>
          </select>
          <span v-if="errors.parent_department_id" class="text-red-500 text-sm">{{ errors.parent_department_id[0] }}</span>
        </div>

        <!-- Manager -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Manager</label>
          <select
            v-model="form.manager_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Select Manager</option>
            <option v-for="employee in employees" :key="employee.id" :value="employee.id">
              {{ employee.full_name }}
            </option>
          </select>
          <span v-if="errors.manager_id" class="text-red-500 text-sm">{{ errors.manager_id[0] }}</span>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea
            v-model="form.description"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Enter department description"
          ></textarea>
          <span v-if="errors.description" class="text-red-500 text-sm">{{ errors.description[0] }}</span>
        </div>

        <!-- Active Status -->
        <div class="flex items-center">
          <input
            v-model="form.is_active"
            type="checkbox"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          />
          <label class="ml-2 block text-sm text-gray-900">Active</label>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3 pt-4 border-t">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="saving"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
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

    // Fallback to test endpoint if auth fails
    try {
      const fallbackResponse = await axios.get('/api/test-dropdown');
      employees.value = fallbackResponse.data;
    } catch (fallbackError) {
      console.error('Fallback also failed:', fallbackError);
      employees.value = [];
    }
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
onMounted(() => {
  fetchDepartments();
  fetchEmployees();
  initializeForm();
});
</script>
