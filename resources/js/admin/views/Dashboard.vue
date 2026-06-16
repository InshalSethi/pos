<template>
<div>
 <!-- Date Filters Row (Simulated) -->
 <div class="flex items-center justify-end mb-6 space-x-3 text-xs font-semibold">
 <div class="flex items-center text-gray-500 bg-white border border-gray-200 rounded-lg px-3 py-2 custom-shadow">
 <span class="mr-2">From:</span>
 <span class="text-gray-800">16/06/2026</span>
 <i class="far fa-calendar-alt ml-3"></i>
 </div>
 <div class="flex items-center text-gray-500 bg-white border border-gray-200 rounded-lg px-3 py-2 custom-shadow">
 <span class="mr-2">To:</span>
 <span class="text-gray-800">16/06/2026</span>
 <i class="far fa-calendar-alt ml-3"></i>
 </div>
 <div class="flex space-x-2">
 <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg custom-shadow transition-colors">Today</button>
 <button class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg custom-shadow transition-colors">This Week</button>
 <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg custom-shadow transition-colors">This Month</button>
 </div>
 </div>

 <!-- White Stat Cards Row -->
 <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6" v-if="!loading">
 <div class="bg-white rounded-xl p-5 custom-shadow border-l-4 border-purple-500 flex flex-col justify-between">
 <div class="flex items-start mb-2">
 <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center mr-4">
 <i class="fas fa-user-shield text-lg"></i>
 </div>
 <div>
 <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Total Admins</p>
 <h3 class="text-2xl font-extrabold text-gray-800">{{ stats.total_admins }}</h3>
 </div>
 </div>
 <p class="text-xs font-semibold text-emerald-500 mt-2"><span class="font-bold">+100%</span> Secure Core</p>
 </div>

 <div class="bg-white rounded-xl p-5 custom-shadow border-l-4 border-orange-400 flex flex-col justify-between">
 <div class="flex items-start mb-2">
 <div class="w-10 h-10 rounded-lg bg-orange-100 text-orange-500 flex items-center justify-center mr-4">
 <i class="fas fa-users text-lg"></i>
 </div>
 <div>
 <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Total Users</p>
 <h3 class="text-2xl font-extrabold text-gray-800">{{ stats.total_users }}</h3>
 </div>
 </div>
 <p class="text-xs font-semibold text-emerald-500 mt-2"><span class="font-bold">+{{ stats.new_users_month }}</span> New Users This Month</p>
 </div>
 
 <div class="bg-white rounded-xl p-5 custom-shadow border-l-4 border-blue-400 flex flex-col justify-between">
 <div class="flex items-start mb-2">
 <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-500 flex items-center justify-center mr-4">
 <i class="fas fa-user-check text-lg"></i>
 </div>
 <div>
 <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Active Users</p>
 <h3 class="text-2xl font-extrabold text-gray-800">{{ stats.active_users }}</h3>
 </div>
 </div>
 <p class="text-xs font-semibold text-emerald-500 mt-2">Currently Active Users</p>
 </div>
 
 <div class="bg-white rounded-xl p-5 custom-shadow border-l-4 border-yellow-400 flex flex-col justify-between">
 <div class="flex items-start mb-2">
 <div class="w-10 h-10 rounded-lg bg-yellow-100 text-yellow-600 flex items-center justify-center mr-4">
 <i class="fas fa-user-plus text-lg"></i>
 </div>
 <div>
 <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">New This Month</p>
 <h3 class="text-2xl font-extrabold text-gray-800">{{ stats.new_users_month }}</h3>
 </div>
 </div>
 <router-link :to="{ name:'admin.users.index' }" class="text-xs font-semibold text-orange-500 hover:text-orange-600 mt-2">View Users &rarr;</router-link>
 </div>
 </div>
 
 <div v-else class="flex justify-center py-20 text-indigo-500">
 <i class="fas fa-spinner fa-spin text-4xl"></i>
 </div>

 <!-- Colored Gradient Cards Row -->
 <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
 <div class="rounded-2xl p-6 custom-shadow bg-gradient-to-r from-blue-500 to-indigo-600 text-white flex justify-between items-center">
 <div>
 <p class="text-[11px] font-bold uppercase tracking-wider text-blue-100 mb-1">Total Active Roles</p>
 <h3 class="text-3xl font-extrabold">2</h3>
 </div>
 <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
 <i class="fas fa-shield-alt text-xl"></i>
 </div>
 </div>

 <div class="rounded-2xl p-6 custom-shadow bg-gradient-to-r from-emerald-500 to-teal-500 text-white flex justify-between items-center">
 <div>
 <p class="text-[11px] font-bold uppercase tracking-wider text-teal-100 mb-1">System Health</p>
 <h3 class="text-3xl font-extrabold">100%</h3>
 </div>
 <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
 <i class="fas fa-server text-xl"></i>
 </div>
 </div>

 <div class="rounded-2xl p-6 custom-shadow bg-gradient-to-r from-purple-500 to-pink-500 text-white flex justify-between items-center">
 <div>
 <p class="text-[11px] font-bold uppercase tracking-wider text-pink-100 mb-1">Active Sessions</p>
 <h3 class="text-3xl font-extrabold">1</h3>
 </div>
 <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
 <i class="fas fa-chart-line text-xl"></i>
 </div>
 </div>
 </div>
</div>
</template>

<script setup>
import { ref, onMounted } from'vue';
import axios from'axios';

const stats = ref({
 total_admins: 0,
 total_users: 0,
 active_users: 0,
 new_users_month: 0
});
const loading = ref(true);

onMounted(async () => {
 try {
 const response = await axios.get('/admin/api/dashboard');
 stats.value = response.data.stats;
 } catch (e) {
 console.error('Failed to load dashboard stats', e);
 } finally {
 loading.value = false;
 }
});
</script>
