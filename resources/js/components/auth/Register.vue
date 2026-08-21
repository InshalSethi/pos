<template>
  <div class="min-h-screen w-full bg-slate-50 text-slate-900 font-sans selection:bg-slate-900 selection:text-white relative overflow-x-hidden flex flex-col">
    
    <!-- Ambient Background Radial Glows -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[500px] bg-gradient-to-tr from-slate-200/50 via-gray-100/30 to-transparent blur-[120px] rounded-full pointer-events-none"></div>

    <!-- Header / Top Navbar (Shared Component — Identical to Landing) -->
    <Navbar />

    <!-- Main Content Container: Akaunting Two-Column Layout (Compact Single Viewport Fit) -->
    <main class="grow flex items-center max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 z-10 relative">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10 items-center w-full">

        <!-- Left Column: Hero Text Copy -->
        <div class="hidden sm:block lg:col-span-6 space-y-3.5 text-left">
          <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm text-xs font-semibold text-slate-700">
            <span>✦ Get Started with POS</span>
          </div>
          <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-950 leading-tight tracking-tight">
            Create your account to start managing your POS business.
          </h1>
          <p class="text-slate-600 text-xs sm:text-sm leading-relaxed max-w-lg">
            Join thousands of retail stores and enterprises streamlining their sales, inventory, and automated ledgers.
          </p>

          <div class="pt-3 border-t border-slate-200/80 grid grid-cols-2 gap-4 max-w-md">
            <div>
              <div class="text-base font-bold text-slate-950">Multi-Company</div>
              <div class="text-xs text-slate-500">Flexible Retail Architecture</div>
            </div>
            <div>
              <div class="text-base font-bold text-slate-950">Instant Setup</div>
              <div class="text-xs text-slate-500">Zero Technical Overhead</div>
            </div>
          </div>
        </div>

        <!-- Right Column: Auth Card Form (Compact Viewport Fit) -->
        <div class="lg:col-span-6 flex justify-center lg:justify-end">
          <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/90 shadow-2xl max-w-md w-full space-y-3 relative z-10">
            
            <!-- Card Header -->
            <div class="text-center">
              <h2 class="text-xl font-extrabold text-slate-950 tracking-tight">Create Account</h2>
              <p class="text-[11px] text-slate-500 mt-0.5">Start your free account setup in seconds</p>
            </div>

            <!-- Google Single Sign-On at Top -->
            <button 
              type="button"
              @click="loginWithGoogle"
              class="w-full flex justify-center items-center py-2 px-4 border border-slate-200 rounded-xl shadow-sm bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-all"
            >
              <svg class="h-3.5 w-3.5 mr-2" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
              </svg>
              <span>Sign up with Google</span>
            </button>

            <!-- Separator Line -->
            <div class="relative my-1.5">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-200"></div>
              </div>
              <div class="relative flex justify-center text-[10px]">
                <span class="px-2.5 bg-white text-slate-400 font-bold uppercase tracking-wider">Or register with email</span>
              </div>
            </div>

            <!-- Alerts -->
            <div v-if="success" class="bg-emerald-50 border border-emerald-200 rounded-xl p-2 shadow-sm text-xs font-semibold text-emerald-800 text-center">
              {{ successMessage }}
            </div>

            <div v-if="error" class="bg-red-50 border border-red-200 rounded-xl p-2 shadow-sm text-xs font-semibold text-red-700 text-center">
              {{ error }}
            </div>

            <!-- Form Content (Compact space-y-2.5) -->
            <form class="space-y-2.5" @submit.prevent="handleRegister">
              
              <!-- Full Name -->
              <div>
                <label for="name" class="block text-[11px] font-bold text-slate-700 mb-0.5">Full Name</label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full px-3 py-1.5 border border-slate-200 rounded-xl text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all duration-200 ease-in-out bg-white"
                  placeholder="e.g. John Doe"
                />
                <p v-if="errors.name" class="mt-0.5 text-[10px] text-red-600 font-medium">{{ errors.name[0] }}</p>
              </div>

              <!-- Email Address -->
              <div>
                <label for="reg-email" class="block text-[11px] font-bold text-slate-700 mb-0.5">Company Email</label>
                <input
                  id="reg-email"
                  v-model="form.email"
                  type="email"
                  required
                  class="w-full px-3 py-1.5 border border-slate-200 rounded-xl text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all duration-200 ease-in-out bg-white"
                  placeholder="john@example.com"
                />
                <p v-if="errors.email" class="mt-0.5 text-[10px] text-red-600 font-medium">{{ errors.email[0] }}</p>
              </div>

              <!-- Password & Confirm Password Grid -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                <div>
                  <label for="reg-password" class="block text-[11px] font-bold text-slate-700 mb-0.5">Password</label>
                  <div class="relative">
                    <input
                      id="reg-password"
                      v-model="form.password"
                      :type="showPassword ? 'text' : 'password'"
                      required
                      class="w-full px-3 py-1.5 pr-8 border border-slate-200 rounded-xl text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all duration-200 ease-in-out bg-white"
                      placeholder="••••••••"
                    />
                    <button
                      type="button"
                      @click="showPassword = !showPassword"
                      class="absolute inset-y-0 right-2.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                    >
                      <svg v-if="showPassword" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                      <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" /></svg>
                    </button>
                  </div>
                </div>

                <div>
                  <label for="confirm-password" class="block text-[11px] font-bold text-slate-700 mb-0.5">Confirm</label>
                  <div class="relative">
                    <input
                      id="confirm-password"
                      v-model="form.password_confirmation"
                      :type="showConfirmPassword ? 'text' : 'password'"
                      required
                      class="w-full px-3 py-1.5 pr-8 border border-slate-200 rounded-xl text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all duration-200 ease-in-out bg-white"
                      placeholder="••••••••"
                    />
                    <button
                      type="button"
                      @click="showConfirmPassword = !showConfirmPassword"
                      class="absolute inset-y-0 right-2.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                    >
                      <svg v-if="showConfirmPassword" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                      <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" /></svg>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Selected Plan Info -->
              <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 flex justify-between items-center mt-4">
                <div>
                  <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mb-0.5">Selected Plan</div>
                  <div class="text-sm font-black text-slate-900">{{ selectedPlanName }}</div>
                </div>
                <div class="text-right">
                  <div class="text-sm font-black text-slate-900">{{ selectedPlanPrice }}</div>
                  <router-link to="/plans" class="text-[10px] text-primary-600 hover:underline font-bold">Change Plan</router-link>
                </div>
              </div>

              <!-- Payment Details (Mock Test Cards) -->
              <div v-if="form.plan !== 'starter'" class="pt-2 border-t border-slate-100 space-y-2.5 mt-2">
                <div class="flex items-center gap-2 mb-1">
                  <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                  <span class="text-[11px] font-bold text-slate-700">Payment Details (Test Mode)</span>
                </div>
                
                <div>
                  <input
                    v-model="form.cardNumber"
                    type="text"
                    required
                    class="w-full px-3 py-1.5 border border-slate-200 rounded-xl text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 bg-white"
                    placeholder="Card Number (e.g. 4242 4242 4242 4242)"
                  />
                </div>
                <div class="grid grid-cols-2 gap-2.5">
                  <input
                    v-model="form.cardExpiry"
                    type="text"
                    required
                    class="w-full px-3 py-1.5 border border-slate-200 rounded-xl text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 bg-white"
                    placeholder="MM/YY"
                  />
                  <input
                    v-model="form.cardCvc"
                    type="text"
                    required
                    class="w-full px-3 py-1.5 border border-slate-200 rounded-xl text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 bg-white"
                    placeholder="CVC"
                  />
                </div>
              </div>

              <!-- Terms Checkbox -->
              <div class="flex items-start pt-0.5">
                <input id="terms" v-model="form.terms" type="checkbox" required class="mt-0.5 h-3.5 w-3.5 text-slate-950 border-slate-300 rounded focus:ring-2 focus:ring-slate-200/60 focus:ring-offset-0" />
                <label for="terms" class="ml-1.5 text-[11px] text-slate-600 font-medium">
                  I agree to <a href="#" class="text-slate-950 hover:underline font-bold">Terms</a> & <a href="#" class="text-slate-950 hover:underline font-bold">Privacy Policy</a>
                </label>
              </div>

              <!-- Submit Action Button -->
              <button
                type="submit"
                :disabled="loading"
                class="w-full flex justify-center py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-slate-950 hover:bg-slate-800 disabled:opacity-70 transition-all shadow-md mt-1"
              >
                <span v-if="loading" class="flex items-center justify-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                  Creating account...
                </span>
                <span v-else>
                  Create Account
                </span>
              </button>

              <!-- Footer Link -->
              <div class="text-center pt-0.5">
                <p class="text-[11px] text-slate-500">
                  Already have an account?
                  <router-link to="/login" class="font-bold text-slate-950 hover:underline">Log in</router-link>
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
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import Navbar from '@/components/shared/Navbar.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false,
  plan: 'starter',
  cycle: 'monthly',
  cardNumber: '',
  cardExpiry: '',
  cardCvc: ''
});

const loading = ref(false);
const error = ref('');
const errors = ref({});
const success = ref(false);
const successMessage = ref('');
const showPassword = ref(false);
const showConfirmPassword = ref(false);

const plans = {
  starter: { name: 'Starter Plan', monthlyPrice: 'Free / 14 Days', yearlyPrice: 'Free / 14 Days' },
  basic: { name: 'Basic Plan', monthlyPrice: '$80 / month', yearlyPrice: '$768 / year' },
  master: { name: 'Master Plan', monthlyPrice: '$200 / month', yearlyPrice: '$1,920 / year' },
  elite: { name: 'Elite Plan', monthlyPrice: '$650 / month', yearlyPrice: '$6,240 / year' },
  custom: { name: 'Custom Plan', monthlyPrice: '$1,500+ / month', yearlyPrice: '$14,400+ / year' }
};

const selectedPlanName = computed(() => plans[form.value.plan]?.name || 'Starter Plan');
const selectedPlanPrice = computed(() => {
  const plan = plans[form.value.plan];
  if (!plan) return 'Free';
  return form.value.cycle === 'yearly' ? plan.yearlyPrice : plan.monthlyPrice;
});

// Google Callback Handling
onMounted(async () => {
  if (route.query.plan && plans[route.query.plan]) {
    form.value.plan = route.query.plan;
  }
  if (route.query.cycle && ['monthly', 'yearly'].includes(route.query.cycle)) {
    form.value.cycle = route.query.cycle;
  }
  const token = route.query.token;
  const errorParam = route.query.error;
  const redirectParam = route.query.redirect;

  if (token) {
    loading.value = true;
    try {
      await authStore.setToken(token);
      window.location.href = redirectParam || '/';
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
  window.location.href = "/auth/google/redirect?flow=signup";
};

const handleRegister = async () => {
  loading.value = true;
  error.value = '';
  errors.value = {};

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
