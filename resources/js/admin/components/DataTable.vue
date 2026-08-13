<template>
  <div class="w-full">
    <div class="flex justify-between mb-4 items-center px-6 pt-4">
      <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 dark:text-zinc-500">
          <i class="fas fa-search text-xs"></i>
        </div>
        <input 
          type="text" 
          v-model="searchQuery" 
          @input="debouncedFetch" 
          placeholder="Search records..." 
          class="w-64 pl-10 pr-4 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs text-xs font-bold outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white placeholder-zinc-400 dark:placeholder-zinc-600"
        >
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-zinc-100 dark:bg-zinc-800/80 border-y border-zinc-200 dark:border-zinc-800">
            <th v-for="col in columns" :key="col.key" class="p-4 text-[11px] font-extrabold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider whitespace-nowrap">
              {{ col.label }}
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
          <tr v-if="loading">
            <td :colspan="columns?.length || 1" class="p-8 text-center text-zinc-500 dark:text-zinc-400">
              <i class="fas fa-circle-notch fa-spin text-2xl mb-2 text-black dark:text-white"></i>
              <p class="text-xs font-bold uppercase tracking-wider">Loading data...</p>
            </td>
          </tr>
          <tr v-else-if="!data || data.length === 0">
            <td :colspan="columns?.length || 1" class="p-8 text-center text-zinc-400 dark:text-zinc-500">
              <i class="fas fa-inbox text-3xl mb-2"></i>
              <p class="text-xs font-bold uppercase tracking-wider">No records found matching your criteria.</p>
            </td>
          </tr>
          <tr v-else v-for="row in data" :key="row.id" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors group">
            <td v-for="col in columns" :key="col.key" class="p-4 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
              <slot :name="'cell(' + col.key + ')'" :item="row" :value="row[col.key]">
                {{ row[col.key] }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 flex justify-between items-center bg-zinc-50/50 dark:bg-zinc-900/50">
      <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
        Showing <span class="font-bold text-zinc-900 dark:text-white">{{ totalRecords === 0 ? 0 : start + 1 }}</span> to <span class="font-bold text-zinc-900 dark:text-white">{{ Math.min(start + limit, totalRecords) }}</span> of <span class="font-bold text-zinc-900 dark:text-white">{{ totalRecords }}</span> entries
      </span>
      <div class="flex space-x-1">
        <button 
          @click="goToPage(currentPage - 1)" 
          :disabled="currentPage === 1 || loading" 
          class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-300 flex items-center justify-center hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-black dark:hover:text-white transition-colors disabled:opacity-40 shadow-xs cursor-pointer"
        >
          <i class="fas fa-chevron-left text-[10px]"></i>
        </button>

        <button 
          v-for="page in visiblePages" 
          :key="page" 
          @click="goToPage(page)" 
          :disabled="loading" 
          :class="[
            'w-8 h-8 rounded-xl border text-xs font-black flex items-center justify-center transition-all shadow-xs cursor-pointer', 
            currentPage === page 
              ? 'bg-black border-black text-white dark:bg-white dark:border-white dark:text-black' 
              : 'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-black dark:hover:text-white'
          ]"
        >
          {{ page }}
        </button>

        <button 
          @click="goToPage(currentPage + 1)" 
          :disabled="currentPage === totalPages || loading || totalPages === 0" 
          class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-300 flex items-center justify-center hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-black dark:hover:text-white transition-colors disabled:opacity-40 shadow-xs cursor-pointer"
        >
          <i class="fas fa-chevron-right text-[10px]"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  endpoint: String,
  columns: Array
});

const data = ref([]);
const totalRecords = ref(0);
const start = ref(0);
const limit = ref(10);
const searchQuery = ref('');
const loading = ref(true);
let timeout = null;

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get(props.endpoint, {
      params: {
        start: start.value,
        length: limit.value,
        'search[value]': searchQuery.value,
        draw: 1,
      }
    });
    data.value = response.data.data;
    totalRecords.value = response.data.recordsFiltered;
  } catch (e) {
    console.error("Failed to load datatable", e);
  } finally {
    loading.value = false;
  }
};

const debouncedFetch = () => {
  clearTimeout(timeout);
  start.value = 0;
  timeout = setTimeout(fetchData, 400);
};

const currentPage = computed(() => Math.floor(start.value / limit.value) + 1);
const totalPages = computed(() => Math.ceil(totalRecords.value / limit.value));

const visiblePages = computed(() => {
  const pages = [];
  if (totalPages.value === 0) return pages;
  
  let startPage = Math.max(1, currentPage.value - 2);
  let endPage = Math.min(totalPages.value, startPage + 4);
  
  if (endPage - startPage < 4) {
    startPage = Math.max(1, endPage - 4);
  }
  
  for (let i = startPage; i <= endPage; i++) {
    pages.push(i);
  }
  return pages;
});

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    start.value = (page - 1) * limit.value;
    fetchData();
  }
};

onMounted(fetchData);

watch(() => props.endpoint, (newVal) => {
  if (newVal && !newVal.includes('/null/') && !newVal.includes('/undefined/')) {
    start.value = 0;
    fetchData();
  }
});

defineExpose({ fetchData });
</script>

