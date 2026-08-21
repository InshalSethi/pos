<template>
  <div class="min-h-screen w-full bg-slate-50 text-slate-900 font-sans selection:bg-slate-900 selection:text-white relative overflow-x-hidden flex flex-col">
    
    <!-- Ambient Background Radial Glows -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[500px] bg-gradient-to-tr from-slate-200/50 via-gray-100/30 to-transparent blur-[120px] rounded-full pointer-events-none"></div>

    <!-- Header / Top Navbar (Shared Component — Identical to Landing) -->
    <Navbar />

    <!-- Main Content Container: Akaunting Two-Column Layout -->
    <main class="grow flex items-center max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 z-10 relative">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center w-full">

        <!-- Left Column: Hero Text Copy -->
        <div class="hidden sm:block lg:col-span-6 space-y-5 text-left">
          <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm text-xs font-semibold text-slate-700">
            <span>✦ Welcome Back to POS</span>
          </div>
          <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-950 leading-tight tracking-tight">
            Log in to track your business & finances efficiently.
          </h1>
          <p class="text-slate-600 text-sm leading-relaxed max-w-lg">
            Manage sales, inventory, double-entry accounting, and real-time financial reporting without any hassle.
          </p>

          <div class="pt-4 border-t border-slate-200/80 grid grid-cols-2 gap-6 max-w-md">
            <div>
              <div class="text-lg font-bold text-slate-950">Double-Entry</div>
              <div class="text-xs text-slate-500">Automated Financial Ledgers</div>
            </div>
            <div>
              <div class="text-lg font-bold text-slate-950">Real-Time</div>
              <div class="text-xs text-slate-500">Stock & Cash Management</div>
            </div>
          </div>
        </div>

        <!-- Right Column: Auth Card Form -->
        <div class="lg:col-span-6 flex justify-center lg:justify-end">
          <div class="bg-white p-6 rounded-3xl border border-slate-200/90 shadow-2xl max-w-md w-full space-y-4 relative z-10">
            
            <!-- Card Header -->
            <div class="text-center">
              <h2 class="text-xl font-extrabold text-slate-950 tracking-tight">Sign In</h2>
              <p class="text-xs text-slate-500 mt-0.5">Access your account dashboard and store metrics</p>
            </div>

            <!-- Google Single Sign-On at Top -->
            <button 
              type="button"
              @click="loginWithGoogle"
              class="w-full flex justify-center items-center py-2.5 px-4 border border-slate-200 rounded-xl shadow-sm bg-white text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-all"
            >
              <svg class="h-4 w-4 mr-2" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
              </svg>
              <span>Sign in with Google</span>
            </button>

            <!-- Separator Line -->
            <div class="relative my-2">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-200"></div>
              </div>
              <div class="relative flex justify-center text-[10px]">
                <span class="px-2.5 bg-white text-slate-400 font-bold uppercase tracking-wider">Or sign in with email</span>
              </div>
            </div>

            <!-- Form Content -->
            <form class="space-y-3" @submit.prevent="handleLogin">
              <div>
                <label for="email" class="block text-xs font-bold text-slate-700 mb-1">
                  Email Address
                </label>
                <input
                  id="email"
                  name="email"
                  v-model="form.email"
                  type="email"
                  autocomplete="email"
                  required
                  class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm placeholder-slate-400 outline-none focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all duration-200 ease-in-out shadow-sm bg-white"
                  placeholder="user@example.com"
                />
              </div>

              <div>
                <label for="password" class="block text-xs font-bold text-slate-700 mb-1">
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
                    class="w-full px-3.5 py-2.5 pr-10 border border-slate-200 rounded-xl text-sm placeholder-slate-400 outline-none focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all duration-200 ease-in-out shadow-sm bg-white"
                    placeholder="••••••••••"
                  />
                  <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
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

              <div class="flex items-center justify-between text-xs pt-0.5">
                <div class="flex items-center">
                  <input
                    id="remember-me"
                    v-model="form.rememberMe"
                    type="checkbox"
                    class="h-3.5 w-3.5 text-slate-950 border-slate-300 rounded focus:ring-2 focus:ring-slate-200/60 focus:ring-offset-0"
                  />
                  <label for="remember-me" class="ml-1.5 block text-slate-600 font-medium">Remember me</label>
                </div>
                <router-link to="/forgot-password" class="font-bold text-slate-900 hover:underline">Forgot password?</router-link>
              </div>

              <!-- Error Banner -->
              <div v-if="error" class="bg-red-50 border border-red-200 rounded-xl p-2.5 shadow-sm">
                <p class="text-xs font-semibold text-red-700 text-center">{{ error }}</p>
              </div>

              <!-- Submit Action -->
              <button
                type="submit"
                :disabled="loading"
                class="w-full flex justify-center py-3 px-4 rounded-xl text-sm font-bold text-white bg-slate-950 hover:bg-slate-800 disabled:opacity-70 transition-all shadow-md"
              >
                <span v-if="loading" class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                  Signing in...
                </span>
                <span v-else>Log In</span>
              </button>

              <!-- Footer Redirection -->
              <div class="text-center pt-1">
                <p class="text-xs text-slate-500">
                  Don't have an account?
                  <router-link to="/register" class="font-bold text-slate-950 hover:underline">Sign up</router-link>
                </p>
              </div>
            </form>

          </div>
        </div>

      </div>
    </main>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import Navbar from '@/components/shared/Navbar.vue';

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
  const redirectParam = route.query.redirect;

  if (token) {
    loading.value = true;
    try {
      await authStore.setToken(token);
      window.location.href = redirectParam || '/dashboard';
    } catch (err) {
      error.value = 'Failed to sync Google session';
    } finally {
      loading.value = false;
    }
  }

  if (errorParam) {
    error.value = errorParam;
    const query = { ...route.query };
    delete query.error;
    router.replace({ query });
  }
});

const loginWithGoogle = () => {
  window.location.href = "/auth/google/redirect?flow=login";
};

const handleLogin = async () => {
  loading.value = true;
  error.value = '';
  try {
    const result = await authStore.login(form.value);
    if (result.success) {
      if (result.is_owner) {
        window.location.href = result.redirect_url || '/owner/companies';
      } else {
        // Staff / Employee user
        if (result.assigned_company_id) {
          authStore.setCurrentCompany(result.assigned_company_id);
        }
        window.location.href = result.redirect_url || '/dashboard';
      }
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