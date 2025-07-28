<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
      <!-- Header -->
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-900">
          {{ isEditing ? 'Edit Position' : 'Create New Position' }}
        </h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="savePosition" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Title -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
            <input
              v-model="form.title"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Enter position title"
            />
            <span v-if="errors.title" class="text-red-500 text-sm">{{ errors.title[0] }}</span>
          </div>

          <!-- Code -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Code *</label>
            <input
              v-model="form.code"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Enter position code"
            />
            <span v-if="errors.code" class="text-red-500 text-sm">{{ errors.code[0] }}</span>
          </div>

          <!-- Department -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
            <select
              v-model="form.department_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Select Department</option>
              <option v-for="department in departments" :key="department.id" :value="department.id">
                {{ department.name }}
              </option>
            </select>
            <span v-if="errors.department_id" class="text-red-500 text-sm">{{ errors.department_id[0] }}</span>
          </div>

          <!-- Level -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Level *</label>
            <select
              v-model="form.level"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Select Level</option>
              <option value="entry">Entry</option>
              <option value="junior">Junior</option>
              <option value="mid">Mid</option>
              <option value="senior">Senior</option>
              <option value="lead">Lead</option>
              <option value="manager">Manager</option>
              <option value="director">Director</option>
              <option value="executive">Executive</option>
            </select>
            <span v-if="errors.level" class="text-red-500 text-sm">{{ errors.level[0] }}</span>
          </div>

          <!-- Min Salary -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Salary</label>
            <input
              v-model="form.min_salary"
              type="number"
              step="0.01"
              min="0"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="0.00"
            />
            <span v-if="errors.min_salary" class="text-red-500 text-sm">{{ errors.min_salary[0] }}</span>
          </div>

          <!-- Max Salary -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Maximum Salary</label>
            <input
              v-model="form.max_salary"
              type="number"
              step="0.01"
              min="0"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="0.00"
            />
            <span v-if="errors.max_salary" class="text-red-500 text-sm">{{ errors.max_salary[0] }}</span>
          </div>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea
            v-model="form.description"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Enter position description"
          ></textarea>
          <span v-if="errors.description" class="text-red-500 text-sm">{{ errors.description[0] }}</span>
        </div>

        <!-- Requirements -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Requirements</label>
          <textarea
            v-model="form.requirements"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Enter position requirements"
          ></textarea>
          <span v-if="errors.requirements" class="text-red-500 text-sm">{{ errors.requirements[0] }}</span>
        </div>

        <!-- Responsibilities -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Responsibilities</label>
          <textarea
            v-model="form.responsibilities"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Enter position responsibilities"
          ></textarea>
          <span v-if="errors.responsibilities" class="text-red-500 text-sm">{{ errors.responsibilities[0] }}</span>
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
onMounted(() => {
  fetchDepartments();
  initializeForm();
});
</script>
