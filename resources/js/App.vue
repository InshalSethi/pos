<template>
  <div id="app">
    <router-view />
    <Toast />
    <ModalsContainer />
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useCurrencyStore } from '@/stores/currency';
import { ModalsContainer } from 'vue-final-modal';
import Toast from '@/components/common/Toast.vue';

const authStore = useAuthStore();
const currencyStore = useCurrencyStore();

onMounted(async () => {
  // Initialize auth state on app load
  console.log('App mounted, initializing auth...');
  try {
    await authStore.initializeAuth();
    console.log('Auth initialized:', authStore.isAuthenticated);

    // Initialize currency store after auth so the API call is authenticated
    if (authStore.isAuthenticated) {
      try {
        await currencyStore.fetchCurrencies();
      } catch (error) {
        console.warn('Currency store initialization failed:', error);
      }
    }
  } catch (error) {
    console.error('Auth initialization error:', error);
  }
});
</script>

<style>
/* Global styles will be added here */
</style>
