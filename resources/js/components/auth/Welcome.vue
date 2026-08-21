<template>
  <div class="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-4 selection:bg-slate-900 selection:text-white relative overflow-hidden">
    <!-- Ambient Background -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-gradient-to-tr from-slate-200/60 via-gray-100/40 to-transparent blur-[120px] rounded-full pointer-events-none"></div>

    <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl shadow-slate-200/50 border border-slate-200 p-8 sm:p-10 relative z-10 text-center">
      
      <!-- Icon/Logo -->
      <div class="w-16 h-16 bg-slate-950 rounded-2xl mx-auto flex items-center justify-center shadow-lg shadow-slate-950/20 mb-8">
        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </svg>
      </div>

      <!-- Headers -->
      <h1 class="text-3xl font-black tracking-tight text-slate-950 mb-3">AcuteBills Desktop</h1>
      <p class="text-slate-500 text-sm font-medium mb-10 px-4 leading-relaxed">
        Connect to your cloud account to synchronize your catalog, customers, and licenses for offline use.
      </p>

      <!-- Action Button -->
      <button 
        @click="loginViaCloud" 
        :disabled="isAuthenticating"
        class="w-full bg-slate-950 hover:bg-slate-800 text-white font-bold py-3.5 px-6 rounded-xl transition-all shadow-md flex items-center justify-center gap-3 disabled:opacity-70 disabled:cursor-not-allowed group"
      >
        <span v-if="!isAuthenticating">Login via Cloud</span>
        <span v-else>Waiting for browser...</span>
        
        <svg v-if="!isAuthenticating" class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
        </svg>
        <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </button>

      <div class="mt-6 text-[11px] text-slate-400 font-semibold tracking-wide uppercase">
        Secure Web-to-Desktop Sync
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const router = useRouter();
const authStore = useAuthStore();
const isAuthenticating = ref(false);

const loginViaCloud = () => {
  isAuthenticating.value = true;
  const cloudUrl = 'http://127.0.0.1:8000/desktop-login';
  if (window.electronAPI && window.electronAPI.openExternalAuth) {
    window.electronAPI.openExternalAuth(cloudUrl);
  } else {
    window.open(cloudUrl, '_blank');
  }
};

onMounted(() => {
  if (window.electronAPI && window.electronAPI.onWebAuth) {
    window.electronAPI.onWebAuth(async ({ token, email, name, license_key, plan, start_date, expires_at }) => {
      console.log('Received auth token from deep link for:', email, name);
      if (!email) return;

      try {
        isAuthenticating.value = true;

        // Sync cloud authentication & license details with local desktop backend & database
        const syncResponse = await axios.post('/api/cloud-auth-sync', {
          email,
          token,
          name,
          license_key,
          plan,
          start_date,
          expires_at
        });

        if (syncResponse.data && syncResponse.data.token) {
          const localToken = syncResponse.data.token;
          axios.defaults.headers.common['Authorization'] = `Bearer ${localToken}`;

          const success = await authStore.setToken(localToken);

          if (success || authStore.isAuthenticated) {
            router.push('/dashboard');
          } else {
            isAuthenticating.value = false;
            alert('Failed to authenticate profile token. Please try logging in again.');
          }
        } else {
          isAuthenticating.value = false;
          alert('Failed to sync cloud session with local database.');
        }
      } catch (error) {
        console.error('Authentication sync failed', error);
        isAuthenticating.value = false;
        alert('Failed to synchronize with cloud account. Please try again.');
      }
    });
  }
});


</script>
