<template>
  <div class="w-full min-h-screen grid grid-cols-1 lg:grid-cols-2 bg-[#fafafa] overflow-hidden">
    
    <!-- Left Column: 3D Spline Canvas -->
    <div class="relative w-full h-full min-h-[100vh] hidden lg:flex items-center justify-center bg-[#0f111a] overflow-hidden">
      <div class="absolute inset-0 w-full h-full flex items-center justify-center overflow-hidden pointer-events-none z-0">
        <spline-viewer 
          url="https://prod.spline.design/0P82uUM5-3-4R85v/scene.splinecode" 
          loading-type="eager"
          hint="false"
          class="w-full h-full"
        ></spline-viewer>
      </div>
    </div>

    <!-- Right Column: Login Form -->
    <div class="w-full h-full flex items-center justify-center p-8 lg:p-12 bg-white z-10 shadow-[-4px_0_24px_rgba(0,0,0,0.02)] relative">
      <div class="w-full max-w-md space-y-6">
        
        <!-- Form Content Header -->
        <div class="text-center">
          <div class="flex items-center justify-center mb-6">
            <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center shadow-sm">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <span class="ml-2 text-2xl font-black tracking-tight text-gray-900">Aura</span>
          </div>
          <h1 class="text-2xl font-bold text-gray-900 tracking-tight mb-2">Welcome back</h1>
          <p class="text-gray-500 text-[14px]">Please enter your details to sign in.</p>
        </div>

        <form class="space-y-5" @submit.prevent="handleLogin">
          <div>
            <label for="email" class="block text-[13px] font-semibold text-gray-700 mb-1.5">
              Your Email Address
            </label>
            <input
              id="email"
              name="email"
              v-model="form.email"
              type="email"
              autocomplete="email"
              required
              class="w-full px-4 py-3 border border-gray-200 rounded-xl text-[14px] placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm"
              placeholder="user@example.com"
            />
          </div>

          <div>
            <label for="password" class="block text-[13px] font-semibold text-gray-700 mb-1.5">
              Password
            </label>
            <div class="relative">
              <input
                id="password"
                name="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                required
                class="w-full px-4 py-3 pr-10 border border-gray-200 rounded-xl text-[14px] placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm"
                placeholder="••••••••••"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
              >
                <svg v-if="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
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
                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <label for="remember-me" class="ml-2 block text-[13px] text-gray-600">Remember me</label>
            </div>
            <div class="text-[13px]">
              <router-link to="/forgot-password" class="font-medium text-blue-600 hover:text-blue-500 hover:underline">Forgot password?</router-link>
            </div>
          </div>

          <div v-if="error" class="bg-red-50 border border-red-200 rounded-xl p-3 shadow-sm">
            <p class="text-[13px] font-medium text-red-800 text-center">{{ error }}</p>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full flex justify-center py-3 px-4 rounded-xl text-[14px] font-semibold text-white bg-[#0f111a] hover:bg-gray-800 disabled:opacity-70 transition-colors shadow-md hover:shadow-lg"
          >
            <span v-if="loading" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              Signing in...
            </span>
            <span v-else>Sign in</span>
          </button>

          <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-200"></div>
            </div>
            <div class="relative flex justify-center text-[13px]">
              <span class="px-3 bg-white text-gray-500">Or continue with</span>
            </div>
          </div>

          <button 
            type="button"
            @click="loginWithGoogle"
            class="w-full flex justify-center items-center py-2.5 px-4 border border-gray-200 rounded-xl shadow-sm bg-white text-[13px] font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all"
          >
            <img class="h-4 w-4 mr-2" src="https://www.gstatic.com/images/branding/googleg/1x/googleg_standard_color_128dp.png" alt="Google">
            Sign in with Google
          </button>

          <div class="text-center mt-6">
            <p class="text-[13px] text-gray-600">
              Don't have an account?
              <router-link to="/register" class="font-medium text-blue-600 hover:text-blue-500 hover:underline">Sign up</router-link>
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
      window.location.href = '/'; // Trigger backend middleware check
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
      window.location.href = result.redirect_url || '/';
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