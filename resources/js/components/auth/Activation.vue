<template>
  <!-- Expired State (Advanced Modal) -->
  <div v-if="isExpired" class="min-h-screen bg-slate-900 flex items-center justify-center p-4 relative overflow-hidden">
    <div class="absolute inset-0 bg-slate-900/90 backdrop-blur-md"></div>
    
    <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-rose-200 dark:border-rose-900/50 shadow-2xl w-full max-w-xl relative z-10 overflow-hidden transform transition-all flex flex-col max-h-[90vh]">
      
      <!-- Header -->
      <div class="px-8 py-6 border-b border-rose-100 dark:border-rose-900/30 flex items-center justify-between gap-4 bg-rose-50/50 dark:bg-rose-900/10 shrink-0">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-900/50 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
          </div>
          <div>
            <h3 class="text-xl font-black text-rose-800 dark:text-rose-400">Subscription Expired</h3>
            <p class="text-xs text-rose-600/80 dark:text-rose-400/80 font-medium mt-1">Your system access has been suspended.</p>
          </div>
        </div>
        
        <!-- Current User Info -->
        <div class="hidden sm:flex items-center gap-3" v-if="authStore.user">
          <div class="text-right">
            <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ authStore.user?.name }}</p>
            <p class="text-[10px] font-medium text-slate-500 dark:text-slate-400">{{ authStore.user?.email }}</p>
          </div>
          <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center border border-indigo-200 dark:border-indigo-800">
            <span class="text-indigo-600 dark:text-indigo-400 font-bold text-sm">{{ authStore.user?.name ? authStore.user.name.charAt(0).toUpperCase() : 'U' }}</span>
          </div>
        </div>
      </div>
      
      <!-- Scrollable Content -->
      <div class="p-8 overflow-y-auto space-y-6">
        
        <template v-if="authStore.hasRole('admin') || authStore.hasRole('owner')">
          <div v-if="!showGlobalChangePlan">
          <!-- Expired Plan Summary -->
          <div class="bg-rose-50 dark:bg-rose-900/10 p-5 rounded-2xl border border-rose-100 dark:border-rose-900/30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
              <p class="text-[10px] font-bold text-rose-500 dark:text-rose-400 uppercase tracking-wider mb-1">Expired Plan</p>
              <p class="text-2xl font-black text-rose-700 dark:text-rose-300 capitalize">{{ licenseStore.licenseData?.plan || 'Unknown' }}</p>
            </div>
            <div class="flex flex-col gap-1 text-left sm:text-right">
              <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Started: <span class="font-bold text-slate-700 dark:text-slate-300 ml-1">{{ formatDate(licenseStore.licenseData?.start_date) || 'N/A' }}</span></p>
              <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Expired: <span class="font-bold text-rose-600 dark:text-rose-400 ml-1">{{ formatDate(licenseStore.licenseData?.expires_at) || 'N/A' }}</span></p>
            </div>
          </div>

          <!-- Renewal Options -->
          <div class="space-y-4">
            <div class="flex justify-between items-center">
              <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Payment Method</label>
              <button @click="showGlobalChangePlan = true" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 text-xs font-bold transition-colors">
                Change Plan Instead?
              </button>
            </div>
            
            <!-- Existing Card Option -->
            <label class="flex items-center justify-between p-4 border rounded-xl cursor-pointer transition-all" :class="globalPaymentMethod === 'existing' ? 'border-indigo-500 bg-indigo-50/30 dark:bg-indigo-900/10' : 'border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800'">
              <div class="flex items-center gap-4">
                <input type="radio" v-model="globalPaymentMethod" value="existing" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500" />
                <div class="flex items-center gap-2">
                  <div class="bg-slate-800 text-white dark:bg-zinc-700 rounded px-2.5 py-1 text-xs font-bold shadow-sm">VISA</div>
                  <span class="text-sm font-bold text-slate-700 dark:text-slate-300">•••• 4242</span>
                </div>
              </div>
              <span class="text-xs font-medium text-slate-500">Exp 12/28</span>
            </label>

            <!-- New Card Option -->
            <label class="flex items-center gap-4 p-4 border rounded-xl cursor-pointer transition-all" :class="globalPaymentMethod === 'new' ? 'border-indigo-500 bg-indigo-50/30 dark:bg-indigo-900/10' : 'border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800'">
              <input type="radio" v-model="globalPaymentMethod" value="new" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500" />
              <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Use a new credit card</span>
            </label>

            <!-- New Card Form -->
            <div v-if="globalPaymentMethod === 'new'" class="space-y-3 pt-2 animate-fade-in p-4 bg-slate-50 dark:bg-zinc-800/30 rounded-xl border border-slate-100 dark:border-zinc-800">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Card Number</label>
                <input type="text" placeholder="0000 0000 0000 0000" class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm bg-white dark:bg-zinc-950 text-slate-900 dark:text-white" />
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Expiry (MM/YY)</label>
                  <input type="text" placeholder="MM/YY" class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm bg-white dark:bg-zinc-950 text-slate-900 dark:text-white" />
                </div>
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">CVC</label>
                  <input type="text" placeholder="123" class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm bg-white dark:bg-zinc-950 text-slate-900 dark:text-white" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Change Plan Interface -->
        <div v-else class="animate-fade-in space-y-4">
          <div class="flex justify-between items-center mb-2">
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Select a New Plan</label>
            <button @click="showGlobalChangePlan = false" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 text-xs font-bold transition-colors">
              Back to Renewal
            </button>
          </div>
          
          <div class="flex justify-center mb-4">
            <div class="bg-slate-100 dark:bg-zinc-800 p-1 rounded-lg inline-flex items-center">
              <button @click="globalBillingCycle = 'monthly'" :class="globalBillingCycle === 'monthly' ? 'bg-white dark:bg-zinc-700 shadow text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'" class="px-4 py-1.5 text-xs font-bold rounded-md transition-all">Monthly</button>
              <button @click="globalBillingCycle = 'annual'" :class="globalBillingCycle === 'annual' ? 'bg-white dark:bg-zinc-700 shadow text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'" class="px-4 py-1.5 text-xs font-bold rounded-md transition-all">Annual (Save 20%)</button>
            </div>
          </div>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div 
              v-for="plan in globalAvailablePlans" 
              :key="plan.id"
              @click="globalSelectedPlan = plan"
              class="p-4 rounded-xl border-2 cursor-pointer transition-all flex flex-col"
              :class="globalSelectedPlan?.id === plan.id ? 'border-indigo-600 bg-indigo-50/50 dark:bg-indigo-900/20' : 'border-slate-200 dark:border-zinc-700 hover:border-indigo-300 dark:hover:border-indigo-700'"
            >
              <div class="flex justify-between items-start mb-2">
                <h4 class="font-black text-slate-900 dark:text-white">{{ plan.name }}</h4>
                <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-100 dark:bg-indigo-900/50 px-2 py-0.5 rounded">{{ globalBillingCycle === 'monthly' ? plan.priceMonthly : plan.priceAnnual }}/{{ globalBillingCycle === 'monthly' ? 'mo' : 'yr' }}</span>
              </div>
              <div class="mt-auto pt-3 flex flex-wrap gap-1">
                <span v-for="feat in plan.features" :key="feat" class="text-[9px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded">{{ feat }}</span>
              </div>
            </div>
          </div>
        </div>
        </template>
        
        <template v-else>
          <!-- Restricted Message for Employees -->
          <div class="bg-rose-50 dark:bg-rose-900/10 p-6 rounded-2xl border border-rose-100 dark:border-rose-900/30 text-center">
            <svg class="w-12 h-12 text-rose-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <h4 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Your subscription plan has expired</h4>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Ask your admin user to renew the plan.</p>
            
            <div class="inline-flex flex-col items-center p-4 bg-white dark:bg-zinc-800 rounded-xl border border-slate-100 dark:border-zinc-700 w-full sm:w-auto min-w-[250px] shadow-sm">
              <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center mb-3">
                <span class="text-indigo-600 dark:text-indigo-400 font-bold text-lg">{{ licenseStore.adminName ? licenseStore.adminName.charAt(0).toUpperCase() : 'A' }}</span>
              </div>
              <p class="text-sm font-bold text-slate-900 dark:text-white">{{ licenseStore.adminName }}</p>
              <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ licenseStore.adminEmail }}</p>
              <span class="mt-2 text-[9px] font-bold uppercase tracking-wider text-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-1 rounded">Admin User</span>
            </div>
          </div>
        </template>

      </div>

      <!-- Footer Actions -->
      <div class="p-6 border-t border-slate-100 dark:border-zinc-800 flex justify-between items-center bg-slate-50/50 dark:bg-zinc-800/30 shrink-0">
        <button @click="logout" class="px-4 py-2 text-sm font-bold text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400 transition-colors">
          Logout
        </button>
        
        <button v-if="!showGlobalChangePlan && (authStore.hasRole('admin') || authStore.hasRole('owner'))" @click="processGlobalRenewal" :disabled="isProcessingGlobalRenew" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-3 px-8 rounded-xl transition-all shadow-md flex items-center gap-2 disabled:opacity-70">
          <div v-if="isProcessingGlobalRenew" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
          <span>{{ isProcessingGlobalRenew ? 'Processing...' : 'Pay & Restore Access' }}</span>
        </button>
        
        <button v-else-if="showGlobalChangePlan && (authStore.hasRole('admin') || authStore.hasRole('owner'))" @click="processGlobalChangePlan" :disabled="!globalSelectedPlan || isProcessingGlobalRenew" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-3 px-8 rounded-xl transition-all shadow-md flex items-center gap-2 disabled:opacity-70">
          <div v-if="isProcessingGlobalRenew" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
          <span>{{ isProcessingGlobalRenew ? 'Processing...' : 'Change Plan & Restore' }}</span>
        </button>
        
        <button v-else-if="!authStore.hasRole('admin') && !authStore.hasRole('owner')" @click="checkRefreshStatus" :disabled="isCheckingStatus" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-3 px-8 rounded-xl transition-all shadow-md flex items-center gap-2 disabled:opacity-70">
          <svg :class="{'animate-spin': isCheckingStatus}" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
          <span>{{ isCheckingStatus ? 'Checking...' : 'Refresh Status' }}</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Normal Activation State -->
  <div v-else class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="flex justify-center">
        <div class="w-16 h-16 bg-primary-600 rounded-xl flex items-center justify-center text-white shadow-lg">
          <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
        </div>
      </div>
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        Activate Software
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600 max-w">
        Please enter your License Key to bind this device and unlock the POS.
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 border border-gray-200">
        <form class="space-y-6" @submit.prevent="submitActivation">
          <div>
            <label for="licenseKey" class="block text-sm font-medium text-gray-700">
              License Key
            </label>
            <div class="mt-1">
              <input id="licenseKey" v-model="form.licenseKey" type="text" required
                placeholder="XXXX-XXXX-XXXX-XXXX"
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">
              Hardware ID
            </label>
            <div class="mt-1 flex items-center bg-gray-50 border border-gray-200 rounded-md px-3 py-2">
              <span class="text-xs text-gray-500 font-mono flex-grow">{{ form.deviceId || 'Loading...' }}</span>
            </div>
            <p class="text-xs text-gray-400 mt-1">This device's unique fingerprint.</p>
          </div>

          <div v-if="error" class="text-sm text-red-600 bg-red-50 p-3 rounded-md border border-red-100">
            {{ error }}
          </div>

          <div v-if="successMsg" class="text-sm text-green-600 bg-green-50 p-3 rounded-md border border-green-100">
            {{ successMsg }}
          </div>

          <div>
            <button type="submit" :disabled="loading || !form.deviceId"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50">
              <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Activate
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useLicenseStore } from '@/stores/license';
import { useAuthStore } from '@/stores/auth';
import { useToast } from '@/composables/useToast';
import axios from 'axios';

const router = useRouter();
const licenseStore = useLicenseStore();
const authStore = useAuthStore();
const { showToast } = useToast();

const form = ref({
  licenseKey: '',
  deviceId: ''
});

const loading = ref(false);
const error = ref('');
const successMsg = ref('');

const isExpired = computed(() => {
  return licenseStore.licenseData && licenseStore.licenseData.status !== 'active';
});

// Advanced Modal State
const isProcessingGlobalRenew = ref(false);
const globalBillingCycle = ref('monthly');
const globalPaymentMethod = ref('existing');
const showGlobalChangePlan = ref(false);
const globalSelectedPlan = ref(null);

const globalAllPlans = [
  { id: 'basic', name: 'Basic Plan', priceMonthly: '$80', priceAnnual: '$768', features: ['1 Device', 'Inventory'] },
  { id: 'master', name: 'Master Plan', priceMonthly: '$200', priceAnnual: '$1,920', features: ['3 Devices', 'Accounting'] },
  { id: 'elite', name: 'Elite Plan', priceMonthly: '$650', priceAnnual: '$6,240', features: ['10 Devices', 'Priority Support'] },
  { id: 'custom', name: 'Custom Plan', priceMonthly: '$1500+', priceAnnual: '$1,500+', features: ['Unlimited Devices', 'Dedicated Account Manager'] },
];

const globalAvailablePlans = computed(() => {
  const current = licenseStore.licenseData?.plan?.toLowerCase() || '';
  return globalAllPlans.filter(p => p.id !== current);
});

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString();
};

const logout = async () => {
  await authStore.logout();
  router.push('/login');
};

const processGlobalRenewal = async () => {
  isProcessingGlobalRenew.value = true;
  try {
    const res = await axios.post('/api/license/renew');
    if (res.data.license && licenseStore.licenseData) {
      licenseStore.licenseData.status = res.data.license.status;
      licenseStore.licenseData.expires_at = res.data.license.expires_at;
    }
    showToast('Subscription renewed! System access restored.', 'success');
    setTimeout(() => {
        window.location.href = '/dashboard';
    }, 500);
  } catch (err) {
    showToast('Failed to renew subscription', 'error');
  } finally {
    isProcessingGlobalRenew.value = false;
  }
};

const processGlobalChangePlan = async () => {
  isProcessingGlobalRenew.value = true;
  try {
    const res = await axios.post('/api/license/renew'); 
    if (res.data.license && licenseStore.licenseData && globalSelectedPlan.value) {
      licenseStore.licenseData.plan = globalSelectedPlan.value.id;
      licenseStore.licenseData.status = 'active';
      licenseStore.licenseData.expires_at = res.data.license.expires_at;
    }
    showToast(`Successfully changed plan to ${globalSelectedPlan.value?.name}! System access restored.`, 'success');
    showGlobalChangePlan.value = false;
    setTimeout(() => {
        window.location.href = '/dashboard';
    }, 500);
  } catch (err) {
    showToast('Failed to change plan', 'error');
  } finally {
    isProcessingGlobalRenew.value = false;
  }
};

onMounted(async () => {
  // If we already have a license key saved but it expired, prefill it to make refresh easy
  if (licenseStore.licenseData && licenseStore.licenseData.license_key) {
    form.value.licenseKey = licenseStore.licenseData.license_key;
  }
  
  // Get Device ID via Electron IPC
  try {
    if (window.electronAPI && window.electronAPI.getDeviceId) {
      form.value.deviceId = await window.electronAPI.getDeviceId();
    } else {
      // Fallback for browser testing
      form.value.deviceId = 'BROWSER-DEV-FINGERPRINT';
    }
  } catch (err) {
    console.error('Failed to fetch hardware ID', err);
    error.value = 'Failed to generate Hardware ID.';
  }
});

const submitActivation = async () => {
  loading.value = true;
  error.value = '';
  successMsg.value = '';

  const res = await licenseStore.activateLicense(form.value.licenseKey, form.value.deviceId);
  
  loading.value = false;

  if (res.success) {
    successMsg.value = 'Activation successful! Redirecting...';
    setTimeout(() => {
      // Force reload to completely refresh the app state
      window.location.href = '/dashboard';
    }, 1500);
  } else {
    error.value = res.message;
  }
};

const isCheckingStatus = ref(false);
const checkRefreshStatus = async () => {
  isCheckingStatus.value = true;
  
  // Enforce a minimum 1s delay so the UI shows the 'Checking...' state clearly
  await Promise.all([
    licenseStore.checkLicenseStatus(),
    new Promise(resolve => setTimeout(resolve, 1000))
  ]);

  if (licenseStore.isLicenseActive) {
    router.push('/');
  }
  isCheckingStatus.value = false;
};
</script>
