<template>
 <div class="w-full">
 <div class="flex justify-between mb-4 items-center px-6 pt-4">
 <div class="relative">
 <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
 <i class="fas fa-search"></i>
 </div>
 <input type="text" v-model="searchQuery" @input="debouncedFetch" placeholder="Search records..." class="w-64 pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-sm text-sm outline-none bg-gray-50">
 </div>
 </div>
 
 <div class="overflow-x-auto">
 <table class="w-full text-left border-collapse">
 <thead>
 <tr class="bg-gray-50/80 border-y border-gray-200">
 <th v-for="col in columns" :key="col.key" class="p-4 text-xs font-extrabold text-gray-500 uppercase tracking-wider whitespace-nowrap">
 {{ col.label }}
 </th>
 </tr>
 </thead>
 <tbody>
 <tr v-if="loading" class="border-b border-gray-50">
 <td :colspan="columns.length" class="p-8 text-center text-indigo-500">
 <i class="fas fa-circle-notch fa-spin text-2xl mb-2"></i>
 <p class="text-sm font-medium">Loading data...</p>
 </td>
 </tr>
 <tr v-else-if="data.length === 0" class="border-b border-gray-50">
 <td :colspan="columns.length" class="p-8 text-center text-gray-500">
 <i class="fas fa-inbox text-3xl mb-2 text-gray-300"></i>
 <p class="text-sm font-medium">No records found matching your criteria.</p>
 </td>
 </tr>
 <tr v-else v-for="row in data" :key="row.id" class="border-b border-gray-50 hover:bg-indigo-50/30 transition-colors group">
 <td v-for="col in columns" :key="col.key" class="p-4 text-sm text-gray-700">
 <slot :name="'cell(' + col.key +')'" :item="row" :value="row[col.key]">
 {{ row[col.key] }}
 </slot>
 </td>
 </tr>
 </tbody>
 </table>
 </div>
 
 <div class="p-4 border-t border-gray-100 flex justify-between items-center bg-gray-50/50">
 <span class="text-xs font-medium text-gray-500">
 Showing <span class="font-bold text-gray-700">{{ totalRecords === 0 ? 0 : start + 1 }}</span> to <span class="font-bold text-gray-700">{{ Math.min(start + limit, totalRecords) }}</span> of <span class="font-bold text-gray-700">{{ totalRecords }}</span> entries
 </span>
 <div class="flex space-x-1">
 <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1 || loading" class="w-8 h-8 rounded bg-white border border-gray-200 text-gray-600 flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 transition-colors disabled:opacity-40 shadow-sm">
 <i class="fas fa-chevron-left text-xs"></i>
 </button>
 
 <button v-for="page in visiblePages" :key="page" @click="goToPage(page)" :disabled="loading" :class="['w-8 h-8 rounded border text-xs font-bold flex items-center justify-center transition-colors shadow-sm', currentPage === page ?'bg-indigo-600 border-indigo-600 text-white' :'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-indigo-600']">
 {{ page }}
 </button>
 
 <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages || loading || totalPages === 0" class="w-8 h-8 rounded bg-white border border-gray-200 text-gray-600 flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 transition-colors disabled:opacity-40 shadow-sm">
 <i class="fas fa-chevron-right text-xs"></i>
 </button>
 </div>
 </div>
 </div>
</template>

<script setup>
import { ref, onMounted, computed } from'vue';
import axios from'axios';

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

defineExpose({ fetchData });
</script>
