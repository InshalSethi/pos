<template>
  <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-800 overflow-hidden">
    <div class="p-6 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center bg-zinc-50/50 dark:bg-zinc-900/50">
      <h3 class="text-lg font-black text-zinc-950 dark:text-white tracking-tight">Website Users</h3>
      <router-link :to="{ name: 'admin.users.create' }" class="bg-black hover:bg-zinc-800 text-white dark:bg-white dark:hover:bg-zinc-200 dark:text-black font-extrabold px-4 py-2 rounded-xl text-xs shadow-xs transition-all flex items-center cursor-pointer">
        <i class="fas fa-plus mr-2 text-[10px]"></i> Add New User
      </router-link>
    </div>
    
    <DataTable ref="dataTable" endpoint="/admin/api/users-data" :columns="columns">
      <template #cell(name)="{ item }">
        <div class="flex items-center">
          <div class="w-8 h-8 rounded-full bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-extrabold text-xs mr-3 shrink-0 shadow-xs">
            {{ item.name.substring(0, 1).toUpperCase() }}
          </div>
          <span class="font-bold text-zinc-900 dark:text-white">{{ item.name }}</span>
        </div>
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
          <router-link :to="{ name: 'admin.users.edit', params: { id: item.id } }" class="w-8 h-8 rounded-xl bg-zinc-100 text-zinc-800 hover:bg-black hover:text-white dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-white dark:hover:text-black flex items-center justify-center transition-all shadow-xs" title="Edit User">
            <i class="fas fa-edit text-xs"></i>
          </router-link>
          <button @click="deleteUser(item.id)" class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white dark:bg-rose-950/40 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white flex items-center justify-center transition-all shadow-xs cursor-pointer" title="Delete User">
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
  { key: 'name', label: 'User Details' },
  { key: 'email', label: 'Email Address' },
  { key: 'phone', label: 'Phone Number' },
  { key: 'is_active', label: 'Status' },
  { key: 'actions', label: 'Actions' }
];

const deleteUser = async (id) => {
  if (confirm('Are you sure you want to permanently delete this user?')) {
    try {
      await axios.delete(`/admin/api/users/${id}`);
      dataTable.value.fetchData();
    } catch (e) {
      alert(e.response?.data?.message || 'Failed to delete user');
    }
  }
};
</script>

