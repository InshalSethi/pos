<template>
  <div class="space-y-4 max-w-5xl mx-auto font-sans">
    <!-- Top Header Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-indigo-600 text-white rounded-xl shadow-xs">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white font-sans">Subscription & Plan</h1>
          <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5 font-sans">Manage your current billing cycle and system access</p>
        </div>
      </div>
    </div>

    <!-- Main License Card -->
    <div v-if="!loading" class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs p-6 space-y-6">
      
      <!-- Current Plan Status -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 bg-slate-50 dark:bg-zinc-800/50 rounded-xl border border-slate-100 dark:border-zinc-800">
        <div>
          <h2 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Current Plan</h2>
          <div class="flex items-center gap-3">
            <span class="text-3xl font-black text-slate-900 dark:text-white capitalize">{{ licenseData?.plan || 'Unknown' }}</span>
            <span 
              class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-full"
              :class="{
                'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400': licenseData?.status === 'active',
                'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400': licenseData?.status !== 'active'
              }"
            >
              {{ licenseData?.status || 'Inactive' }}
            </span>
          </div>
          <div class="mt-5">
            <span class="text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider block mb-2">License Key</span>
            <div class="flex items-center gap-4">
              <div class="px-4 py-2 bg-slate-100 dark:bg-zinc-800/80 text-slate-800 dark:text-slate-200 text-sm font-mono font-bold rounded-lg border border-slate-200 dark:border-zinc-700 min-w-[240px] flex items-center justify-center shadow-inner relative overflow-hidden group">
                <div v-if="!showLicenseKey" class="absolute inset-0 backdrop-blur-md bg-slate-100/40 dark:bg-zinc-800/40 z-10"></div>
                <span :class="{'blur-sm select-none': !showLicenseKey, 'tracking-widest opacity-30': !showLicenseKey, 'relative z-20': true}">
                  {{ showLicenseKey ? (licenseData?.license_key || 'N/A') : '••••••••••••••••••••' }}
                </span>
              </div>
              
              <div class="flex items-center gap-3">
                <button @click="showLicenseKey = !showLicenseKey" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 text-xs font-bold flex items-center gap-1.5 transition-colors focus:outline-none">
                  <svg v-if="!showLicenseKey" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                  <span>{{ showLicenseKey ? 'Hide' : 'Show' }}</span>
                </button>
                
                <button @click="copyLicenseKey" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 text-xs font-bold flex items-center gap-1.5 transition-colors focus:outline-none">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                  <span>Copy</span>
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <div class="flex flex-col gap-2 min-w-[140px]">
          <button v-if="daysRemaining <= 0 || licenseData?.status !== 'active'" @click="showRenewModal = true" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2.5 px-4 rounded-lg transition-all shadow-sm flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
            Renew Plan
          </button>
          
          <button v-if="licenseData?.status === 'active'" @click="cancelSubscription" class="w-full bg-white dark:bg-zinc-800 hover:bg-rose-50 dark:hover:bg-rose-900/20 text-rose-600 dark:text-rose-400 border border-slate-200 dark:border-zinc-700 text-xs font-bold py-2 px-4 rounded-lg transition-all shadow-sm">
            Cancel Subscription
          </button>
        </div>
      </div>

      <!-- Billing Cycle Dates -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div class="p-5 border border-slate-200 dark:border-zinc-800 rounded-xl relative overflow-hidden">
          <div class="absolute top-0 right-0 p-4 opacity-5">
            <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" /></svg>
          </div>
          <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Start Date</p>
          <p class="text-xl font-bold text-slate-800 dark:text-slate-200">{{ formatDate(licenseData?.start_date) || 'N/A' }}</p>
        </div>
        
        <div class="p-5 border border-slate-200 dark:border-zinc-800 rounded-xl relative overflow-hidden">
          <div class="absolute top-0 right-0 p-4 opacity-5">
            <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" /></svg>
          </div>
          <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Expiration Date</p>
          <p class="text-xl font-bold text-slate-800 dark:text-slate-200" :class="{'text-rose-600 dark:text-rose-400': isExpiringSoon}">
            {{ formatDate(licenseData?.expires_at) || 'N/A' }}
          </p>
          <p v-if="daysRemaining !== null" class="text-xs font-semibold mt-2" :class="isExpiringSoon ? 'text-rose-500' : 'text-emerald-500'">
            {{ daysRemaining }} days remaining
          </p>
        </div>
      </div>

    </div>

    <!-- Loading State -->
    <div v-else class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
    </div>

    <!-- Renew Plan Modal -->
    <div v-if="showRenewModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showRenewModal = false"></div>
      
      <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-2xl w-full max-w-md relative z-10 overflow-hidden transform transition-all">
        <div class="px-6 py-5 border-b border-slate-100 dark:border-zinc-800 flex justify-between items-center bg-slate-50/50 dark:bg-zinc-800/30">
          <h3 class="text-lg font-bold text-slate-900 dark:text-white">Renew Subscription</h3>
          <button @click="showRenewModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
        
        <div class="p-6 space-y-6">
          <div class="bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-xl border border-indigo-100 dark:border-indigo-800/30">
            <p class="text-sm text-indigo-800 dark:text-indigo-300 font-medium">You are renewing your <strong>{{ licenseData?.plan || 'Plan' }}</strong> subscription.</p>
          </div>

          <div class="space-y-3">
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Payment Method</label>
            
            <!-- Existing Card Option -->
            <label class="flex items-center justify-between p-3 border rounded-xl cursor-pointer transition-all" :class="paymentMethod === 'existing' ? 'border-indigo-500 bg-indigo-50/30 dark:bg-indigo-900/10' : 'border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800'">
              <div class="flex items-center gap-3">
                <input type="radio" v-model="paymentMethod" value="existing" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500" />
                <div class="flex items-center gap-2">
                  <div class="bg-slate-200 dark:bg-zinc-700 rounded px-2 py-1 text-xs font-bold text-slate-700 dark:text-slate-300">VISA</div>
                  <span class="text-sm font-medium text-slate-700 dark:text-slate-300">•••• 4242</span>
                </div>
              </div>
              <span class="text-xs text-slate-500">Expires 12/28</span>
            </label>

            <!-- New Card Option -->
            <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-all" :class="paymentMethod === 'new' ? 'border-indigo-500 bg-indigo-50/30 dark:bg-indigo-900/10' : 'border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800'">
              <input type="radio" v-model="paymentMethod" value="new" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500" />
              <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Use a new credit card</span>
            </label>
          </div>

          <!-- New Card Form (Shows only if new is selected) -->
          <div v-if="paymentMethod === 'new'" class="space-y-3 pt-3 animate-fade-in">
            <div>
              <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Card Number</label>
              <input type="text" placeholder="0000 0000 0000 0000" class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm bg-white dark:bg-zinc-950 text-slate-900 dark:text-white" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Expiry (MM/YY)</label>
                <input type="text" placeholder="MM/YY" class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm bg-white dark:bg-zinc-950 text-slate-900 dark:text-white" />
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">CVC</label>
                <input type="text" placeholder="123" class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm bg-white dark:bg-zinc-950 text-slate-900 dark:text-white" />
              </div>
            </div>
          </div>
        </div>

        <div class="p-6 pt-0 border-t border-slate-100 dark:border-zinc-800 mt-4 flex items-center justify-end gap-3 bg-slate-50/50 dark:bg-zinc-800/30">
          <button @click="showRenewModal = false" class="px-4 py-2 text-sm font-bold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-colors">
            Cancel
          </button>
          <button @click="processRenewal" :disabled="isProcessingRenew" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2.5 px-6 rounded-lg transition-all shadow-md flex items-center gap-2 disabled:opacity-70">
            <div v-if="isProcessingRenew" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
            <span>{{ isProcessingRenew ? 'Processing...' : 'Pay & Renew' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Cancel Subscription Modal -->
    <div v-if="showCancelModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showCancelModal = false"></div>
      
      <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-2xl w-full max-w-md relative z-10 overflow-hidden transform transition-all">
        <div class="px-6 py-5 border-b border-slate-100 dark:border-zinc-800 flex justify-between items-center bg-rose-50/50 dark:bg-rose-900/10">
          <h3 class="text-lg font-bold text-rose-700 dark:text-rose-400">Cancel Subscription</h3>
          <button @click="showCancelModal = false" class="text-slate-400 hover:text-rose-600 dark:hover:text-rose-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
        
        <div class="p-6 space-y-4">
          <p class="text-sm font-medium text-slate-700 dark:text-slate-300">We're sorry to see you go! Your premium access will continue until the end of your current billing cycle.</p>
          
          <div class="space-y-3">
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Please tell us why you are leaving</label>
            <select v-model="cancelReason" class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 text-sm bg-white dark:bg-zinc-950 text-slate-900 dark:text-white">
              <option value="" disabled>Select a reason...</option>
              <option value="too_expensive">Too expensive</option>
              <option value="missing_features">Missing features I need</option>
              <option value="hard_to_use">Too hard to use</option>
              <option value="closing_business">Closing business</option>
              <option value="other">Other reason</option>
            </select>
          </div>
          
          <div v-if="cancelReason === 'other'" class="space-y-3 pt-2 animate-fade-in">
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Additional details</label>
            <textarea v-model="cancelDetails" rows="3" placeholder="Please provide more details..." class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 text-sm bg-white dark:bg-zinc-950 text-slate-900 dark:text-white resize-none"></textarea>
          </div>
        </div>

        <div class="p-6 pt-0 border-t border-slate-100 dark:border-zinc-800 mt-4 flex justify-between items-center bg-slate-50/50 dark:bg-zinc-800/30">
          <button @click="showCancelModal = false" class="px-4 py-2 text-sm font-bold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-colors">
            Nevermind
          </button>
          <button @click="confirmCancelSubscription" :disabled="!cancelReason || isProcessingCancel" class="bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold py-2.5 px-6 rounded-lg transition-all shadow-md flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
            <div v-if="isProcessingCancel" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
            <span>{{ isProcessingCancel ? 'Processing...' : 'Confirm Cancellation' }}</span>
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useLicenseStore } from '@/stores/license';
import { useToast } from '@/composables/useToast';
import axios from 'axios';

const licenseStore = useLicenseStore();
const { showToast } = useToast();
const loading = ref(true);

const showRenewModal = ref(false);
const paymentMethod = ref('existing');
const isProcessingRenew = ref(false);

const showChangePlanModal = ref(false);
const changePlanStep = ref(1);
const changePlanCycle = ref('monthly');
const selectedNewPlan = ref(null);
const isProcessingChange = ref(false);

const showCancelModal = ref(false);
const cancelReason = ref('');
const cancelDetails = ref('');
const isProcessingCancel = ref(false);

const showLicenseKey = ref(false);

const licenseData = computed(() => licenseStore.licenseData);

const allPlans = [
  { id: 'basic', name: 'Basic Plan', monthlyPrice: '$80', yearlyPrice: '$768', description: 'Essential POS solution for single-store setups.', features: ['1 Device Limit', 'Inventory & Sales'], colorClass: 'text-blue-600 bg-blue-100' },
  { id: 'master', name: 'Master Plan', monthlyPrice: '$200', yearlyPrice: '$1,920', description: 'Advanced accounting and multi-company features.', features: ['3 Devices Limit', 'Advanced Accounting', 'Multi-Company'], colorClass: 'text-amber-600 bg-amber-100' },
  { id: 'elite', name: 'Elite Plan', monthlyPrice: '$650', yearlyPrice: '$6,240', description: 'For high-volume retail chains and large enterprises.', features: ['10 Devices Limit', 'Priority Support'], colorClass: 'text-purple-600 bg-purple-100' },
  { id: 'custom', name: 'Custom Plan', monthlyPrice: 'Starts $1,500', yearlyPrice: 'Starts $14,400', description: 'Bespoke deployment for massive scale.', features: ['20-50+ Devices', 'Dedicated Account Manager'], colorClass: 'text-slate-700 bg-slate-200' },
];

const availablePlans = computed(() => {
  const current = licenseData.value?.plan?.toLowerCase() || 'starter';
  return allPlans.filter(p => p.id !== current);
});

const formatDate = (dateString) => {
  if (!dateString) return null;
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

const daysRemaining = computed(() => {
  if (!licenseData.value?.expires_at) return null;
  const end = new Date(licenseData.value.expires_at);
  const now = new Date();
  const diffTime = end - now;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays > 0 ? diffDays : 0;
});

const isExpiringSoon = computed(() => {
  return daysRemaining.value !== null && daysRemaining.value <= 14;
});

onMounted(async () => {
  loading.value = true;
  await licenseStore.checkLicenseStatus();
  loading.value = false;
});

const processRenewal = async () => {
  isProcessingRenew.value = true;
  try {
    const res = await axios.post('/api/license/renew');
    if (res.data.license && licenseStore.licenseData) {
      licenseStore.licenseData.status = res.data.license.status;
      licenseStore.licenseData.expires_at = res.data.license.expires_at;
    }
    showRenewModal.value = false;
    showToast('Subscription renewed successfully!', 'success');
  } catch (error) {
    showToast('Failed to renew subscription', 'error');
  } finally {
    isProcessingRenew.value = false;
  }
};

const openChangePlanModal = () => {
  changePlanStep.value = 1;
  selectedNewPlan.value = null;
  changePlanCycle.value = 'monthly';
  paymentMethod.value = 'existing';
  showChangePlanModal.value = true;
};

const closeChangePlanModal = () => {
  showChangePlanModal.value = false;
};

const selectNewPlan = (plan) => {
  selectedNewPlan.value = plan;
};

const proceedToCheckout = () => {
  if (selectedNewPlan.value) {
    changePlanStep.value = 2;
  }
};

const processPlanChange = () => {
  isProcessingChange.value = true;
  setTimeout(() => {
    isProcessingChange.value = false;
    showChangePlanModal.value = false;
    
    // Optimistic UI Update (Mocking the Live Server change)
    if (licenseStore.licenseData) {
      licenseStore.licenseData.plan = selectedNewPlan.value.id;
    }
    
    showToast(`Successfully changed plan to ${selectedNewPlan.value.name}!`, 'success');
  }, 2000);
};

const cancelSubscription = () => {
  cancelReason.value = '';
  cancelDetails.value = '';
  showCancelModal.value = true;
};

const confirmCancelSubscription = () => {
  isProcessingCancel.value = true;
  setTimeout(() => {
    isProcessingCancel.value = false;
    showCancelModal.value = false;
    
    if (licenseStore.licenseData) {
      licenseStore.licenseData.status = 'cancelled';
    }
    
    showToast('Your subscription has been cancelled.', 'info');
  }, 1500);
};

const copyLicenseKey = () => {
  if (licenseData.value?.license_key) {
    navigator.clipboard.writeText(licenseData.value.license_key)
      .then(() => {
        showToast('License key copied to clipboard!', 'success');
      })
      .catch(() => {
        showToast('Failed to copy license key.', 'error');
      });
  }
};
</script>
