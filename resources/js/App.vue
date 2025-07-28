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
import { ModalsContainer } from 'vue-final-modal';
import Toast from '@/components/common/Toast.vue';

const authStore = useAuthStore();

onMounted(async () => {
  // Initialize auth state on app load
  console.log('App mounted, initializing auth...');
  try {
    await authStore.initializeAuth();
    console.log('Auth initialized:', authStore.isAuthenticated);
  } catch (error) {
    console.error('Auth initialization error:', error);
  }
});
</script>

<style>
/* Global styles will be added here */
</style>
