<template>
 <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
 <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
 <h3 class="text-lg font-bold text-gray-800">Website Users</h3>
 <router-link :to="{ name:'admin.users.create' }" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition-colors">
 <i class="fas fa-plus mr-2"></i> Add New User
 </router-link>
 </div>
 
 <DataTable ref="dataTable" endpoint="/admin/api/users-data" :columns="columns">
 <template #cell(name)="{ item }">
 <div class="flex items-center">
 <div class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center font-bold text-xs mr-3 shrink-0 border border-gray-200">
 {{ item.name.substring(0, 1).toUpperCase() }}
 </div>
 <span class="font-bold text-gray-800">{{ item.name }}</span>
 </div>
 </template>
 
 <template #cell(is_active)="{ value }">
 <span v-if="value" class="inline-block bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-md shadow-sm border border-emerald-200">
 <i class="fas fa-circle text-[8px] mr-1"></i> Active
 </span>
 <span v-else class="inline-block bg-red-100 text-red-800 text-[10px] font-bold px-2 py-0.5 rounded-md shadow-sm border border-red-200">
 <i class="fas fa-circle text-[8px] mr-1"></i> Inactive
 </span>
 </template>
 
 <template #cell(actions)="{ item }">
 <div class="flex space-x-2">
 <router-link :to="{ name:'admin.users.edit', params: { id: item.id } }" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-colors shadow-sm">
 <i class="fas fa-edit text-xs"></i>
 </router-link>
 <button @click="deleteUser(item.id)" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-colors shadow-sm">
 <i class="fas fa-trash text-xs"></i>
 </button>
 </div>
 </template>
 </DataTable>
 </div>
</template>

<script setup>
import { ref } from'vue';
import axios from'axios';
import DataTable from'../../components/DataTable.vue';

const dataTable = ref(null);

const columns = [
 { key:'id', label:'ID' },
 { key:'name', label:'User Details' },
 { key:'email', label:'Email Address' },
 { key:'phone', label:'Phone Number' },
 { key:'is_active', label:'Status' },
 { key:'actions', label:'Actions' }
];

const deleteUser = async (id) => {
 if (confirm('Are you sure you want to permanently delete this user?')) {
 try {
 await axios.delete(`/admin/api/users/${id}`);
 dataTable.value.fetchData();
 } catch (e) {
 alert(e.response?.data?.message ||'Failed to delete user');
 }
 }
};
</script>
