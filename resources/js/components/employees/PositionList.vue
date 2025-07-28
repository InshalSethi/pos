<template>
  <div class="position-list">
    <!-- Position Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Title
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Code
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Department
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Level
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Salary Range
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Employees
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="position in positions" :key="position.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ position.title }}</div>
                <div class="text-sm text-gray-500" v-if="position.description">{{ position.description }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ position.code }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ position.department?.name || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getLevelClass(position.level)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ getLevelText(position.level) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ getSalaryRange(position) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ position.employees?.length || 0 }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="position.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ position.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end space-x-2">
                  <button
                    @click="$emit('edit-position', position)"
                    class="text-indigo-600 hover:text-indigo-900"
                    title="Edit"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  <button
                    @click="deletePosition(position)"
                    class="text-red-600 hover:text-red-900"
                    title="Delete"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && positions.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No positions found</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating a new position.</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-sm text-gray-500">Loading positions...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Props and Emits
const emit = defineEmits(['edit-position', 'refresh']);

// Reactive data
const positions = ref([]);
const loading = ref(false);

// Methods
const fetchPositions = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/positions');
    positions.value = response.data;
  } catch (error) {
    console.error('Error fetching positions:', error);
  } finally {
    loading.value = false;
  }
};

const deletePosition = async (position) => {
  if (!confirm('Are you sure you want to delete this position?')) {
    return;
  }

  try {
    await axios.delete(`/api/positions/${position.id}`);
    await fetchPositions();
  } catch (error) {
    console.error('Error deleting position:', error);
    if (error.response?.data?.message) {
      alert(error.response.data.message);
    }
  }
};

const getLevelClass = (level) => {
  const classes = {
    entry: 'bg-gray-100 text-gray-800',
    junior: 'bg-blue-100 text-blue-800',
    mid: 'bg-green-100 text-green-800',
    senior: 'bg-yellow-100 text-yellow-800',
    lead: 'bg-orange-100 text-orange-800',
    manager: 'bg-purple-100 text-purple-800',
    director: 'bg-red-100 text-red-800',
    executive: 'bg-indigo-100 text-indigo-800'
  };
  return classes[level] || 'bg-gray-100 text-gray-800';
};

const getLevelText = (level) => {
  const texts = {
    entry: 'Entry',
    junior: 'Junior',
    mid: 'Mid',
    senior: 'Senior',
    lead: 'Lead',
    manager: 'Manager',
    director: 'Director',
    executive: 'Executive'
  };
  return texts[level] || level;
};

const getSalaryRange = (position) => {
  if (position.min_salary && position.max_salary) {
    return `$${parseFloat(position.min_salary).toFixed(0)} - $${parseFloat(position.max_salary).toFixed(0)}`;
  } else if (position.min_salary) {
    return `From $${parseFloat(position.min_salary).toFixed(0)}`;
  } else if (position.max_salary) {
    return `Up to $${parseFloat(position.max_salary).toFixed(0)}`;
  }
  return 'Not specified';
};

// Lifecycle
onMounted(() => {
  fetchPositions();
});
</script>
