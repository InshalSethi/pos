<template>
 <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
 <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
 <h3 class="text-lg font-bold text-gray-800">System Roles & Permissions</h3>
 <router-link :to="{ name:'admin.roles.create' }" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition-colors">
 <i class="fas fa-plus mr-2"></i> Add New Role
 </router-link>
 </div>
 
 <DataTable ref="dataTable" endpoint="/admin/api/roles-data" :columns="columns">
 <template #cell(name)="{ value }">
 <strong class="text-gray-800">{{ value }}</strong>
 </template>

 <template #cell(permissions)="{ value }">
 <div class="flex flex-wrap gap-1 max-w-xl">
 <span v-for="permission in value.slice(0, 5)" :key="permission" class="inline-block bg-emerald-50 border border-emerald-200 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-md shadow-sm">
 {{ permission }}
 </span>
 <span v-if="value.length > 5" class="inline-block bg-gray-100 text-gray-600 text-[10px] font-bold px-2 py-0.5 rounded-md shadow-sm border border-gray-200">
 +{{ value.length - 5 }} more
 </span>
 <span v-if="value.length === 0" class="inline-block bg-red-50 text-red-600 text-[10px] font-bold px-2 py-0.5 rounded-md shadow-sm border border-red-200">
 No Permissions Assigned
 </span>
 </div>
 </template>
 
 <template #cell(actions)="{ item }">
 <div class="flex space-x-2">
 <router-link :to="{ name:'admin.roles.edit', params: { id: item.id } }" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-colors shadow-sm">
 <i class="fas fa-edit text-xs"></i>
 </router-link>
 <button v-if="!item.is_core" @click="deleteRole(item.id)" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-colors shadow-sm">
 <i class="fas fa-trash text-xs"></i>
 </button>
 <div v-else class="w-8 h-8 flex items-center justify-center text-gray-300" title="Core System Role">
 <i class="fas fa-lock text-xs"></i>
 </div>
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
 { key:'name', label:'Role Name' },
 { key:'permissions', label:'Assigned Permissions' },
 { key:'actions', label:'Actions' }
];

const deleteRole = async (id) => {
 if (confirm('Are you sure you want to delete this role? Users assigned to this role will lose permissions.')) {
 try {
 await axios.delete(`/admin/api/roles/${id}`);
 dataTable.value.fetchData();
 } catch (e) {
 alert(e.response?.data?.message ||'Failed to delete role');
 }
 }
};
</script>
