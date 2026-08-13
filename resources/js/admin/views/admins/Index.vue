<template>
  <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-800 overflow-hidden">
    <div class="p-6 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center bg-zinc-50/50 dark:bg-zinc-900/50">
      <h3 class="text-lg font-black text-zinc-950 dark:text-white tracking-tight">System Admins</h3>
      <router-link :to="{ name: 'admin.admins.create' }" class="bg-black hover:bg-zinc-800 text-white dark:bg-white dark:hover:bg-zinc-200 dark:text-black font-extrabold px-4 py-2 rounded-xl text-xs shadow-xs transition-all flex items-center cursor-pointer">
        <i class="fas fa-plus mr-2 text-[10px]"></i> Add New Admin
      </router-link>
    </div>
    
    <DataTable ref="dataTable" endpoint="/admin/api/admins-data" :columns="columns">
      <template #cell(roles)="{ value }">
        <span v-for="role in value" :key="role" class="inline-block bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100 text-[10px] font-extrabold px-2.5 py-0.5 rounded-lg mr-1 border border-zinc-200 dark:border-zinc-700 shadow-xs">
          {{ role }}
        </span>
        <span v-if="!value || value.length === 0" class="text-xs text-zinc-400 dark:text-zinc-500 italic">No roles</span>
      </template>
      
      <template #cell(is_active)="{ value }">
        <span v-if="value" class="inline-block bg-black text-white dark:bg-white dark:text-black text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow-xs">
          Active
        </span>
        <span v-else class="inline-block bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider border border-zinc-200 dark:border-zinc-700">
          Inactive
        </span>
      </template>
      
      <template #cell(actions)="{ item }">
        <div class="flex space-x-2">
          <router-link :to="{ name: 'admin.admins.edit', params: { id: item.id } }" class="w-8 h-8 rounded-xl bg-zinc-100 text-zinc-800 hover:bg-black hover:text-white dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-white dark:hover:text-black flex items-center justify-center transition-all shadow-xs" title="Edit Admin">
            <i class="fas fa-edit text-xs"></i>
          </router-link>
          <button @click="deleteAdmin(item.id)" class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white dark:bg-rose-950/40 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white flex items-center justify-center transition-all shadow-xs cursor-pointer" title="Delete Admin">
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
  { key: 'name', label: 'Admin Name' },
  { key: 'email', label: 'Email Address' },
  { key: 'roles', label: 'Assigned Roles' },
  { key: 'is_active', label: 'Status' },
  { key: 'actions', label: 'Actions' }
];

const deleteAdmin = async (id) => {
  if (confirm('Are you sure you want to completely remove this admin?')) {
    try {
      await axios.delete(`/admin/api/admins/${id}`);
      dataTable.value.fetchData();
    } catch (e) {
      alert(e.response?.data?.message || 'Failed to delete admin');
    }
  }
};
</script>

