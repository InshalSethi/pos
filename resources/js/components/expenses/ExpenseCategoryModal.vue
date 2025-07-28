<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
      <!-- Header -->
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-900">
          {{ isEditing ? 'Edit Category' : 'Create New Category' }}
        </h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="saveCategory" class="space-y-4">
        <!-- Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Enter category name"
          />
          <span v-if="errors.name" class="text-red-500 text-sm">{{ errors.name[0] }}</span>
        </div>

        <!-- Code -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
          <input
            v-model="form.code"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Enter category code"
          />
          <span v-if="errors.code" class="text-red-500 text-sm">{{ errors.code[0] }}</span>
        </div>

        <!-- Parent Category -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Parent Category</label>
          <select
            v-model="form.parent_category_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">No Parent (Top Level)</option>
            <option v-for="category in availableParents" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
          <span v-if="errors.parent_category_id" class="text-red-500 text-sm">{{ errors.parent_category_id[0] }}</span>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea
            v-model="form.description"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Enter category description"
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

// Computed
const isEditing = computed(() => !!props.category);

const availableParents = computed(() => {
  if (!isEditing.value) {
    return categories.value;
  }
  
  // Exclude current category and its children from parent options
  return categories.value.filter(cat => {
    if (cat.id === props.category.id) return false;
    // Add logic to exclude children if needed
    return true;
  });
});

// Methods
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
    let response;
    if (isEditing.value) {
      response = await axios.put(`/api/expense-categories/${props.category.id}`, form.value);
    } else {
      response = await axios.post('/api/expense-categories', form.value);
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

// Initialize form if editing
const initializeForm = () => {
  if (props.category) {
    Object.keys(form.value).forEach(key => {
      if (props.category[key] !== undefined) {
        form.value[key] = props.category[key];
      }
    });
  }
};

// Lifecycle
onMounted(() => {
  fetchCategories();
  initializeForm();
});
</script>
