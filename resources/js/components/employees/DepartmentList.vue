<template>
  <div class="department-list">
    <!-- Department Table -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-zinc-800 text-xs">
          <thead class="bg-slate-50 dark:bg-zinc-800/60">
            <tr>
              <th class="px-6 py-3 text-left text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">
                Name
              </th>
              <th class="px-6 py-3 text-left text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">
                Code
              </th>
              <th class="px-6 py-3 text-left text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">
                Manager
              </th>
              <th class="px-6 py-3 text-left text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">
                Parent Department
              </th>
              <th class="px-6 py-3 text-left text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">
                Employees
              </th>
              <th class="px-6 py-3 text-left text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-right text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-zinc-900 divide-y divide-slate-100 dark:divide-zinc-800/60">
            <tr v-for="department in departments" :key="department.id" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/40 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-xs font-semibold text-slate-900 dark:text-slate-100">{{ department.name }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400" v-if="department.description">{{ department.description }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-xs font-mono text-slate-700 dark:text-slate-300">
                {{ department.code }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-700 dark:text-slate-300">
                {{ department.manager?.full_name || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-700 dark:text-slate-300">
                {{ department.parent?.name || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-xs font-mono text-slate-700 dark:text-slate-300">
                {{ department.employees?.length || 0 }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="department.is_active ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-slate-400'" 
                      class="px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider">
                  {{ department.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-medium">
                <div class="flex justify-end space-x-2">
                  <button
                    @click="$emit('edit-department', department)"
                    class="text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white"
                    title="Edit"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  <button
                    @click="deleteDepartment(department)"
                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
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
      <div v-if="!loading && departments.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-slate-400 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-slate-900 dark:text-slate-100">No departments found</h3>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Get started by creating a new department.</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-slate-900 dark:border-white"></div>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Loading departments...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Props and Emits
const emit = defineEmits(['edit-department', 'refresh']);

// Reactive data
const departments = ref([]);
const loading = ref(false);

// Methods
const fetchDepartments = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/departments');
    departments.value = response.data;
  } catch (error) {
    console.error('Error fetching departments:', error);
  } finally {
    loading.value = false;
  }
};

const deleteDepartment = async (department) => {
  if (!confirm('Are you sure you want to delete this department?')) {
    return;
  }

  try {
    await axios.delete(`/api/departments/${department.id}`);
    await fetchDepartments();
  } catch (error) {
    console.error('Error deleting department:', error);
    if (error.response?.data?.message) {
      alert(error.response.data.message);
    }
  }
};

defineExpose({
  fetchDepartments
});

// Lifecycle
onMounted(() => {
  fetchDepartments();
});
</script>
