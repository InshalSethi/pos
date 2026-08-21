<template>
  <div id="app">
    <router-view />
    <Toast />
    <ConfirmModal />
    <ModalsContainer />
    <AccountDeactivatedModal />
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useCurrencyStore } from '@/stores/currency';
import { ModalsContainer } from 'vue-final-modal';
import Toast from '@/components/common/Toast.vue';
import ConfirmModal from '@/components/common/ConfirmModal.vue';
import AccountDeactivatedModal from '@/components/common/AccountDeactivatedModal.vue';
import axios from 'axios';

const authStore = useAuthStore();
const currencyStore = useCurrencyStore();
let heartbeatInterval = null;

const checkStatusHeartbeat = async () => {
  if (!authStore.isAuthenticated || authStore.isDeactivated) return;
  // If company setup is not yet completed (user is pending setup on owner hub), skip employee deactivation polling
  if (authStore.user?.company_id === null || !authStore.user?.is_setup_completed) return;
  try {
    const res = await axios.get('/api/user/status-check');
    if (res.data?.error === 'ACCOUNT_INACTIVE') {
      authStore.triggerDeactivation();
    }
  } catch (err) {
    if (err.response?.data?.error === 'ACCOUNT_INACTIVE') {
      authStore.triggerDeactivation();
    }
  }
};

const setupEchoListener = () => {
  if (window.Echo && authStore.user?.id) {
    window.Echo.private(`user.${authStore.user.id}`)
      .listen('EmployeeDeactivatedEvent', () => {
        authStore.triggerDeactivation();
      });
    window.Echo.channel(`public-user-status.${authStore.user.id}`)
      .listen('EmployeeDeactivatedEvent', () => {
        authStore.triggerDeactivation();
      });
  }
};

const applyTheme = (theme) => {
  const html = document.documentElement;
  // Normalize theme settings (e.g. backend 'auto' to 'system')
  const normalizedTheme = (theme === 'auto' || theme === 'match system') ? 'system' : theme;
  localStorage.setItem('theme', normalizedTheme);
  document.cookie = `theme=${normalizedTheme}; path=/; max-age=31536000`; // 1 year

  if (normalizedTheme === 'dark') {
    html.classList.add('dark');
  } else if (normalizedTheme === 'light') {
    html.classList.remove('dark');
  } else if (normalizedTheme === 'system') {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (prefersDark) {
      html.classList.add('dark');
    } else {
      html.classList.remove('dark');
    }
  }
};

onMounted(async () => {
  // Initialize auth state on app load
  console.log('App mounted, initializing auth...');
  try {
    await authStore.initializeAuth();
    console.log('Auth initialized:', authStore.isAuthenticated);

    // Initialize stores after auth so the API calls are authenticated
    if (authStore.isAuthenticated) {
      setupEchoListener();
      await checkStatusHeartbeat();
      window.addEventListener('focus', checkStatusHeartbeat);
      // Fast 2-second heartbeat poll for real-time deactivation detection
      heartbeatInterval = setInterval(checkStatusHeartbeat, 2000);

      try {
        await currencyStore.fetchCurrencies();
      } catch (error) {
        console.warn('Currency store initialization failed:', error);
      }
      
      try {
        // Fetch and apply settings (including theme)
        const settingsResponse = await axios.get('/api/user/settings');
        if (settingsResponse.data && settingsResponse.data.theme) {
            applyTheme(settingsResponse.data.theme);
        }
      } catch (error) {
        console.warn('Settings initialization failed:', error);
      }
    }
  } catch (error) {
    console.error('Auth initialization error:', error);
  }
});

onUnmounted(() => {
  window.removeEventListener('focus', checkStatusHeartbeat);
  if (heartbeatInterval) clearInterval(heartbeatInterval);
});
</script>

<style>
/* Global styles will be added here */
</style>
