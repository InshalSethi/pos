<template>
<div class="w-full h-full min-h-screen flex items-center justify-center bg-slate-50 dark:bg-zinc-950 p-4 transition-colors">
 <div class="w-full max-w-md bg-white dark:bg-zinc-900 rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
 <div class="px-8 pt-8 pb-6 text-center border-b border-zinc-100 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50">
 <div class="w-14 h-14 mx-auto bg-black text-white dark:bg-white dark:text-black rounded-2xl flex items-center justify-center shadow-lg mb-4 transform -rotate-2 font-black text-2xl">
 <i class="fas fa-user-shield text-xl"></i>
 </div>
 <h2 class="text-2xl font-black text-zinc-950 dark:text-white tracking-tight">Admin System</h2>
 <p class="text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mt-1">Sign in to control panel</p>
 </div>
 
 <div class="p-8">
 <div v-if="error" class="mb-6 bg-rose-50 border border-rose-200 text-rose-700 dark:bg-rose-950/40 dark:border-rose-900 dark:text-rose-400 px-4 py-3 rounded-xl flex items-center text-xs font-bold">
 <i class="fas fa-exclamation-circle mr-2 text-sm"></i> {{ error }}
 </div>

 <form @submit.prevent="login" class="space-y-5">
 <div>
 <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1.5">Email Address</label>
 <div class="relative">
 <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 dark:text-zinc-500">
 <i class="fas fa-envelope text-xs"></i>
 </div>
 <input type="email" v-model="form.email" required autofocus placeholder="admin@example.com" class="w-full pl-10 pr-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs text-xs font-bold outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white placeholder-zinc-400 dark:placeholder-zinc-600">
 </div>
 </div>

 <div>
 <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1.5">Password</label>
 <div class="relative">
 <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 dark:text-zinc-500">
 <i class="fas fa-lock text-xs"></i>
 </div>
 <input type="password" v-model="form.password" required placeholder="••••••••" class="w-full pl-10 pr-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs text-xs font-bold outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white placeholder-zinc-400 dark:placeholder-zinc-600">
 </div>
 </div>

 <div class="flex items-center justify-between mt-4">
 <label class="flex items-center cursor-pointer">
 <input type="checkbox" v-model="form.remember" class="rounded border-zinc-300 dark:border-zinc-700 text-black dark:text-white focus:ring-black/10 h-4 w-4">
 <span class="ml-2 text-xs font-bold text-zinc-600 dark:text-zinc-400">Remember Me</span>
 </label>
 </div>

 <div class="pt-2">
 <button type="submit" :disabled="loading" class="w-full bg-black text-white hover:bg-zinc-800 dark:bg-white dark:text-black dark:hover:bg-zinc-200 font-extrabold py-3 px-4 rounded-xl shadow-sm transition-all disabled:opacity-50 text-xs tracking-wider uppercase cursor-pointer flex items-center justify-center">
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
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const form = ref({ email: '', password: '', remember: false });
const error = ref('');
const loading = ref(false);

const login = async () => {
 loading.value = true;
 error.value = '';
 
 try {
 await axios.get('/sanctum/csrf-cookie');
 const response = await axios.post('/admin/api/login', form.value);
 
 sessionStorage.setItem('admin_logged_in', 'true');
 localStorage.setItem('admin_name', response.data.user?.name || 'Admin');
 localStorage.setItem('admin_email', response.data.user?.email || form.value.email);
 
 router.push({ name: 'admin.dashboard' });
 } catch (e) {
 if (e.response && e.response.data.errors) {
 error.value = Object.values(e.response.data.errors)[0][0];
 } else if (e.response && e.response.data.message) {
 error.value = e.response.data.message;
 } else {
 error.value = 'An error occurred during authentication.';
 }
 } finally {
 loading.value = false;
 }
};
</script>

