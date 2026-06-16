<template>
<div class="w-full h-full flex items-center justify-center bg-gray-50">
 <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
 <div class="px-8 pt-8 pb-6 text-center border-b border-gray-100 bg-gray-50/50">
 <div class="w-16 h-16 mx-auto bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-200 mb-4 transform rotate-3">
 <i class="fas fa-user-shield text-2xl text-white"></i>
 </div>
 <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Admin System</h2>
 <p class="text-sm text-gray-500 mt-1">Sign in to manage the POS backend</p>
 </div>
 
 <div class="p-8">
 <div v-if="error" class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center">
 <i class="fas fa-exclamation-circle mr-2"></i> {{ error }}
 </div>

 <form @submit.prevent="login" class="space-y-5">
 <div>
 <label class="block text-sm font-bold text-gray-700 mb-1">Email Address</label>
 <div class="relative">
 <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
 <i class="fas fa-envelope"></i>
 </div>
 <input type="email" v-model="form.email" required autofocus placeholder="admin@example.com" class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-sm text-sm outline-none">
 </div>
 </div>

 <div>
 <label class="block text-sm font-bold text-gray-700 mb-1">Password</label>
 <div class="relative">
 <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
 <i class="fas fa-lock"></i>
 </div>
 <input type="password" v-model="form.password" required placeholder="••••••••" class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-sm text-sm outline-none">
 </div>
 </div>

 <div class="flex items-center justify-between mt-4">
 <label class="flex items-center">
 <input type="checkbox" v-model="form.remember" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4 border-gray-300">
 <span class="ml-2 text-sm font-medium text-gray-600">Remember Me</span>
 </label>
 </div>

 <div class="pt-2">
 <button type="submit" :disabled="loading" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-md shadow-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all disabled:opacity-50">
 <span v-if="loading"><i class="fas fa-spinner fa-spin mr-2"></i> Authenticating...</span>
 <span v-else><i class="fas fa-sign-in-alt mr-2"></i> Secure Sign In</span>
 </button>
 </div>
 </form>
 </div>
 </div>
</div>
</template>

<script setup>
import { ref } from'vue';
import { useRouter } from'vue-router';
import axios from'axios';

const router = useRouter();
const form = ref({ email:'', password:'', remember: false });
const error = ref('');
const loading = ref(false);

const login = async () => {
 loading.value = true;
 error.value ='';
 
 try {
 await axios.get('/sanctum/csrf-cookie');
 const response = await axios.post('/admin/api/login', form.value);
 
 sessionStorage.setItem('admin_logged_in','true');
 localStorage.setItem('admin_name', response.data.user?.name ||'Admin');
 localStorage.setItem('admin_email', response.data.user?.email || form.value.email);
 
 router.push({ name:'admin.dashboard' });
 } catch (e) {
 if (e.response && e.response.data.errors) {
 error.value = Object.values(e.response.data.errors)[0][0];
 } else if (e.response && e.response.data.message) {
 error.value = e.response.data.message;
 } else {
 error.value ='An error occurred during authentication.';
 }
 } finally {
 loading.value = false;
 }
};
</script>
