<template>
  <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-800 overflow-hidden">
    <div class="p-6 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center bg-zinc-50/50 dark:bg-zinc-900/50">
      <h3 class="text-lg font-black text-zinc-950 dark:text-white tracking-tight">System Roles & Permissions</h3>
      <router-link :to="{ name: 'admin.roles.create' }" class="bg-black hover:bg-zinc-800 text-white dark:bg-white dark:hover:bg-zinc-200 dark:text-black font-extrabold px-4 py-2 rounded-xl text-xs shadow-xs transition-all flex items-center cursor-pointer">
        <i class="fas fa-plus mr-2 text-[10px]"></i> Add New Role
      </router-link>
    </div>
    
    <DataTable ref="dataTable" endpoint="/admin/api/roles-data" :columns="columns">
      <template #cell(name)="{ item }">
        <span class="font-black text-zinc-950 dark:text-white tracking-tight text-xs">{{ item.name }}</span>
      </template>

      <template #cell(guard_name)="{ value }">
        <span class="inline-block bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100 text-[10px] font-extrabold px-2.5 py-0.5 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-xs uppercase tracking-wider">
          {{ value }}
        </span>
      </template>
      
      <template #cell(permissions_count)="{ value }">
        <span class="font-extrabold text-zinc-900 dark:text-white text-xs">
          {{ value }} <span class="text-[10px] text-zinc-400 font-bold uppercase tracking-wider ml-1">permissions</span>
        </span>
      </template>
      
      <template #cell(actions)="{ item }">
        <div class="flex space-x-2">
          <router-link :to="{ name: 'admin.roles.edit', params: { id: item.id } }" class="w-8 h-8 rounded-xl bg-zinc-100 text-zinc-800 hover:bg-black hover:text-white dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-white dark:hover:text-black flex items-center justify-center transition-all shadow-xs" title="Edit Role">
            <i class="fas fa-edit text-xs"></i>
          </router-link>
          <button @click="deleteRole(item.id)" class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white dark:bg-rose-950/40 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white flex items-center justify-center transition-all shadow-xs cursor-pointer" title="Delete Role">
            <i class="fas fa-trash text-xs"></i>
          </button>
        </div>
      </template>
    </DataTable>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import DataTable from '../../components/DataTable.vue';

const dataTable = ref(null);

const columns = [
  { key: 'id', label: 'ID' },
  { key: 'name', label: 'Role Name' },
  { key: 'guard_name', label: 'Guard' },
  { key: 'permissions_count', label: 'Assigned Permissions' },
  { key: 'actions', label: 'Actions' }
];

const deleteRole = async (id) => {
  if (confirm('Are you sure you want to delete this role? Admins assigned to this role will lose its permissions.')) {
    try {
      await axios.delete(`/admin/api/roles/${id}`);
      dataTable.value.fetchData();
    } catch (e) {
      alert(e.response?.data?.message || 'Failed to delete role');
    }
  }
};
</script>
