<template>
  <div class="h-screen w-screen overflow-hidden grid grid-cols-1 lg:grid-cols-2 bg-gray-50">
    <!-- Left Panel (Branding) -->
    <div class="hidden lg:flex w-full h-full relative bg-[#232526] text-white flex-col justify-between p-12 overflow-hidden">
      <!-- Top Bar -->
      <div class="flex justify-between items-center relative z-10">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm">
            <svg class="w-6 h-6 text-[#544CF6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <span class="text-2xl font-black tracking-tight">Aura</span>
        </div>
        <!-- Login button relocated to the right form container -->
      </div>

      <!-- Center Content -->
      <div class="relative z-10 max-w-lg mt-12">
        <div class="w-12 h-12 bg-white/10 backdrop-blur-sm rounded-xl flex items-center justify-center mb-6 border border-white/20">
          <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </div>
        <h1 class="text-4xl font-bold mb-4 tracking-tight leading-tight">Let's setup your business identity</h1>
        <p class="text-white/80 text-[15px] leading-relaxed pr-8">
          Provide your primary enterprise registration metrics, brand assets, and operational role parameters to structure your unique isolated root tenant profile safely.
        </p>
      </div>

      <!-- Footer -->
      <div class="relative z-10 flex items-center space-x-2 text-white/60 text-[11px] font-bold uppercase tracking-wider mt-auto">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7a4 4 0 00-8 0v4h8z" />
        </svg>
        <span>Secured by Aura Multi-Tenant Cloud Architecture Layer</span>
      </div>
      
      <!-- 3D Card Asset Background -->
      <div class="absolute inset-0 w-full h-full flex items-center justify-center overflow-hidden pointer-events-none z-0">
        <spline-viewer 
          url="https://prod.spline.design/0P82uUM5-3-4R85v/scene.splinecode" 
          loading-type="eager"
          hint="false"
          class="absolute -bottom-12 w-full h-[108%] scale-95 lg:scale-100 origin-center"
          background-color="#232526"
        ></spline-viewer>
      </div>
    </div>

    <!-- Right Panel (Form) -->
    <div class="w-full h-full flex flex-col justify-between bg-[#F8FAFC] relative overflow-hidden">
      


      <!-- Mobile Header (Visible only on small screens) -->
      <div class="lg:hidden flex items-center justify-between p-6 mb-4">
        <div class="flex items-center space-x-2">
          <div class="w-8 h-8 bg-[#544CF6] rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <span class="text-xl font-bold text-gray-900">Aura</span>
        </div>
        <router-link to="/login" class="text-sm font-medium text-[#544CF6]">Sign in</router-link>
      </div>

      <div class="max-w-md w-full mx-auto px-6 lg:px-12 my-auto py-12">
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Create Account</h2>
          <p class="text-gray-500 mt-1 text-[14px]">Please enter your accurate details to sign up.</p>
        </div>

        <!-- Success Message -->
        <div v-if="success" class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-start shadow-sm">
          <svg class="w-5 h-5 text-green-500 mr-3 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <div>
            <h3 class="text-[13px] font-semibold text-green-800">Registration successful!</h3>
            <p class="text-[13px] text-green-700 mt-0.5">{{ successMessage }}</p>
          </div>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start shadow-sm">
          <svg class="w-5 h-5 text-red-500 mr-3 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <div>
            <h3 class="text-[13px] font-semibold text-red-800">Registration failed</h3>
            <p class="text-[13px] text-red-700 mt-0.5">{{ error }}</p>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleRegister" class="max-w-md w-full mx-auto flex flex-col">
          <div class="w-full space-y-3.5">
            <!-- Name -->
            <div>
              <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Full Name</label>
              <input v-model="form.name" type="text" required class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-600/20 layout-field transition-all shadow-sm outline-none placeholder-gray-400 bg-white" :class="{ 'border-red-300': errors.name }" placeholder="e.g. Azlan Hassan" />
              <p v-if="errors.name" class="mt-1.5 text-xs text-red-600">{{ errors.name[0] }}</p>
            </div>
            
            <!-- Email -->
            <div>
              <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Company Email</label>
              <input v-model="form.email" type="email" required class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-600/20 layout-field transition-all shadow-sm outline-none placeholder-gray-400 bg-white" :class="{ 'border-red-300': errors.email }" placeholder="azlanhassan@gmail.com" />
              <p v-if="errors.email" class="mt-1.5 text-xs text-red-600">{{ errors.email[0] }}</p>
            </div>
            
            <!-- Password -->
            <div>
              <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Password</label>
              <div class="relative">
                <input v-model="form.password" :type="showPassword ? 'text' : 'password'" required class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-600/20 layout-field transition-all shadow-sm outline-none placeholder-gray-400 bg-white" :class="{ 'border-red-300': errors.password }" placeholder="••••••••" />
                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                  <svg v-if="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" /></svg>
                </button>
              </div>
              <p v-if="errors.password" class="mt-1.5 text-xs text-red-600">{{ errors.password[0] }}</p>
            </div>
            
            <!-- Confirm Password -->
            <div>
              <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Confirm Password</label>
              <div class="relative">
                <input v-model="form.password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" required class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-600/20 layout-field transition-all shadow-sm outline-none placeholder-gray-400 bg-white" placeholder="••••••••" />
                <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                  <svg v-if="showConfirmPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" /></svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Terms -->
          <div class="flex items-start pt-3 pb-1 w-full">
            <input id="terms" v-model="form.terms" type="checkbox" required class="mt-0.5 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-600" />
            <label for="terms" class="ml-2 text-[13px] text-gray-600">
              I agree to the <a href="#" class="text-blue-600 hover:underline font-medium">Terms of Service</a> and <a href="#" class="text-blue-600 hover:underline font-medium">Privacy Policy</a>
            </label>
          </div>

          <!-- Primary Action -->
          <button type="submit" :disabled="loading" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-xl shadow-sm transition-all mt-2 disabled:opacity-70 disabled:cursor-not-allowed">
            <span v-if="loading" class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              Creating...
            </span>
            <span v-else class="flex items-center justify-center">
              Create Account
            </span>
          </button>
          
          <!-- Google button below Create Account -->
          <button type="button" @click="loginWithGoogle" class="w-full flex items-center justify-center py-2.5 mt-3 border border-gray-200 rounded-xl shadow-sm bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all">
            <img class="h-4 w-4 mr-2" src="https://www.gstatic.com/images/branding/googleg/1x/googleg_standard_color_128dp.png" alt="Google">
            Google Sign Up
          </button>
        </form>

        <p class="text-sm text-slate-500 text-center mt-6">
          Already have an account? <router-link to="/login" class="text-blue-600 hover:underline font-medium">Sign in</router-link>
        </p>
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
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false
});

const loading = ref(false);
const error = ref('');
const errors = ref({});
const success = ref(false);
const successMessage = ref('');
const showPassword = ref(false);
const showConfirmPassword = ref(false);

// Google Callback Handling
onMounted(async () => {
  const token = route.query.token;
  const errorParam = route.query.error;
  const redirectParam = route.query.redirect;

  if (token) {
    loading.value = true;
    try {
      await authStore.setToken(token); // Store mein token save karega
      window.location.href = redirectParam || '/'; // Trigger backend middleware check
    } catch (err) {
      error.value = 'Failed to sync Google session';
    } finally {
      loading.value = false;
    }
  }

  if (errorParam) {
    error.value = errorParam;
    // Clear error from URL query to prevent persistence on refresh
    const query = { ...route.query };
    delete query.error;
    router.replace({ query });
  }
});

const loginWithGoogle = () => {
  // Laravel Backend Redirect
  window.location.href = "/auth/google/redirect?flow=signup";
};

const handleRegister = async () => {
  loading.value = true;
  error.value = '';
  errors.value = {};

  // Client-side validation
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Passwords do not match';
    loading.value = false;
    return;
  }

  if (!form.value.terms) {
    error.value = 'You must agree to the Terms of Service and Privacy Policy';
    loading.value = false;
    return;
  }

  try {
    const result = await authStore.register(form.value);

    if (result.success) {
      success.value = true;
      successMessage.value = 'Account created successfully! Redirecting to setup...';
      
      // Redirect to welcome gate
      setTimeout(() => {
        window.location.href = result.redirect_url || '/';
      }, 1000);
    } else {
      error.value = result.message;
      errors.value = result.errors || {};
    }
  } catch (err) {
    error.value = 'An unexpected error occurred';
  } finally {
    loading.value = false;
  }
};
</script>
