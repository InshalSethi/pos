<template>
  <div class="position-list">
    <!-- Position Table -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-zinc-800 text-xs">
          <thead class="bg-slate-50 dark:bg-zinc-800/60">
            <tr>
              <th class="px-6 py-3 text-left text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">
                Title
              </th>
              <th class="px-6 py-3 text-left text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">
                Code
              </th>
              <th class="px-6 py-3 text-left text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">
                Department
              </th>
              <th class="px-6 py-3 text-left text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">
                Level
              </th>
              <th class="px-6 py-3 text-left text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">
                Salary Range
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
            <tr v-for="position in positions" :key="position.id" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/40 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-xs font-semibold text-slate-900 dark:text-slate-100">{{ position.title }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400" v-if="position.description">{{ position.description }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-xs font-mono text-slate-700 dark:text-slate-300">
                {{ position.code }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-700 dark:text-slate-300">
                {{ position.department?.name || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getLevelClass(position.level)" class="px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider">
                  {{ getLevelText(position.level) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-xs font-mono text-slate-700 dark:text-slate-300">
                {{ getSalaryRange(position) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-xs font-mono text-slate-700 dark:text-slate-300">
                {{ position.employees?.length || 0 }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="position.is_active ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-slate-400'" 
                      class="px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider">
                  {{ position.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-medium">
                <div class="flex justify-end space-x-2">
                  <button
                    @click="$emit('edit-position', position)"
                    class="text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white"
                    title="Edit"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  <button
                    @click="deletePosition(position)"
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
      <div v-if="!loading && positions.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-slate-400 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-slate-900 dark:text-slate-100">No positions found</h3>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Get started by creating a new position.</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-slate-900 dark:border-white"></div>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Loading positions...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useCurrencyStore } from '@/stores/currency';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

// Props and Emits
const emit = defineEmits(['edit-position', 'refresh']);

const currencyStore = useCurrencyStore();
const authStore = useAuthStore();

const currencySymbol = computed(() => {
  return currencyStore.symbol || authStore.user?.company?.currency_symbol || authStore.user?.company?.currency || 'Rs.';
});

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
    emit('refresh');
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
  const sym = currencySymbol.value;
  if (position.min_salary && position.max_salary) {
    return `${sym} ${parseFloat(position.min_salary).toFixed(0)} - ${sym} ${parseFloat(position.max_salary).toFixed(0)}`;
  } else if (position.min_salary) {
    return `From ${sym} ${parseFloat(position.min_salary).toFixed(0)}`;
  } else if (position.max_salary) {
    return `Up to ${sym} ${parseFloat(position.max_salary).toFixed(0)}`;
  }
  return 'Not specified';
};

defineExpose({
  fetchPositions
});

// Lifecycle
onMounted(() => {
  currencyStore.fetchCurrencies();
  fetchPositions();
});
</script>
