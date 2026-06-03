<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-purple-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
      <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-0 left-0 w-full h-full opacity-5">
          <div class="grid grid-cols-12 gap-4 h-full">
            <div v-for="i in 144" :key="i" class="border border-gray-300"></div>
          </div>
        </div>
      </div>

      <div class="relative bg-white rounded-2xl shadow-xl p-8 space-y-6">
        <div class="text-center">
          <div class="flex items-center justify-center mb-4">
            <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <span class="ml-2 text-xl font-bold text-gray-900">Aura</span>
          </div>
          <h1 class="text-2xl font-bold text-gray-900 mb-2">Welcome back</h1>
          <p class="text-gray-600 text-sm">Please enter your details to sign in</p>
        </div>

        <form class="space-y-4" @submit.prevent="handleLogin">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
              Your Email Address
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              autocomplete="email"
              required
              class="w-full px-3 py-3 border border-gray-300 rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
              placeholder="Your Email Address"
            />
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
              Password
            </label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                required
                class="w-full px-3 py-3 pr-10 border border-gray-300 rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                placeholder="••••••••••"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
              >
                <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input
                id="remember-me"
                v-model="form.rememberMe"
                type="checkbox"
                class="h-4 w-4 text-blue-600 border-gray-300 rounded"
              />
              <label for="remember-me" class="ml-2 block text-sm text-gray-700">Remember me</label>
            </div>
            <div class="text-sm">
              <router-link to="/forgot-password" class="font-medium text-blue-600 hover:text-blue-500">Forgot password?</router-link>
            </div>
          </div>

          <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-3">
            <p class="text-sm text-red-800 text-center">{{ error }}</p>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full flex justify-center py-3 px-4 rounded-lg text-sm font-medium text-white bg-gray-900 hover:bg-gray-800 disabled:opacity-50 transition-colors"
          >
            <span v-if="loading">Signing in...</span>
            <span v-else>Sign in</span>
          </button>

          <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-2 bg-white text-gray-500">Or continue with</span>
            </div>
          </div>

          <button 
            type="button"
            @click="loginWithGoogle"
            class="w-full flex justify-center items-center py-2.5 px-4 border border-gray-300 rounded-lg shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
          >
            <img class="h-5 w-5 mr-2" src="https://www.gstatic.com/images/branding/googleg/1x/googleg_standard_color_128dp.png" alt="Google">
            Sign in with Google
          </button>

          <div class="text-center mt-4">
            <p class="text-sm text-gray-600">
              Don't have an account?
              <router-link to="/register" class="font-medium text-blue-600 hover:text-blue-500">Sign up</router-link>
            </p>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const form = ref({
  email: '',
  password: '',
  rememberMe: false
});

const loading = ref(false);
const error = ref('');
const showPassword = ref(false);

// Google Callback Handling
onMounted(async () => {
  const token = route.query.token;
  const errorParam = route.query.error;

  if (token) {
    loading.value = true;
    try {
      await authStore.setToken(token); // Store mein token save karega
      router.push('/'); // Dashboard bhej dega
    } catch (err) {
      error.value = 'Failed to sync Google session';
    } finally {
      loading.value = false;
    }
  }

  if (errorParam) {
    error.value = errorParam;
  }
});

const loginWithGoogle = () => {
  // Laravel Backend Redirect
  window.location.href = "http://127.0.0.1:8001/api/auth/google/redirect";
};

const handleLogin = async () => {
  loading.value = true;
  error.value = '';
  try {
    const result = await authStore.login(form.value);
    if (result.success) {
      router.push('/');
    } else {
      error.value = result.message;
    }
  } catch (err) {
    error.value = 'An unexpected error occurred';
  } finally {
    loading.value = false;
  }
};
</script>