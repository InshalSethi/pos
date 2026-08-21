<template>
  <div class="min-h-screen w-full bg-slate-50 text-slate-900 font-sans selection:bg-slate-900 selection:text-white relative overflow-x-hidden flex flex-col">
    
    <!-- Ambient Background Radial Glows -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[500px] bg-gradient-to-tr from-slate-200/50 via-gray-100/30 to-transparent blur-[120px] rounded-full pointer-events-none"></div>

    <!-- Header / Top Navbar -->
    <header class="sticky top-0 z-40 pt-4 px-4 sm:px-6 lg:px-8 transition-all w-full">
      <div class="max-w-6xl mx-auto bg-white/80 backdrop-blur-xl border border-slate-200/80 shadow-xl shadow-slate-200/40 rounded-full px-6 py-3 flex items-center justify-between">
        
        <!-- Logo -->
        <router-link to="/" class="flex items-center gap-3 group">
          <div class="w-9 h-9 rounded-full bg-slate-950 flex items-center justify-between p-2 shadow-md group-hover:scale-105 transition-transform">
            <div class="w-2.5 h-2.5 rounded-full bg-white"></div>
            <div class="w-1.5 h-full rounded-full bg-white/40"></div>
          </div>
          <span class="text-xl font-black text-slate-900 tracking-tight leading-none group-hover:text-slate-800 transition-colors">POS Hub</span>
        </router-link>

        <!-- Nav Links -->
        <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
          <router-link to="/owner/companies" class="text-slate-950 font-bold">Workspaces</router-link>
          <router-link to="/plans" class="hover:text-slate-950 transition-colors">Pricing &amp; Plans</router-link>
        </nav>

        <!-- Right User / Logout Menu -->
        <div class="flex items-center gap-3">
          <button
            @click="handleLogout"
            type="button"
            class="text-xs font-bold text-slate-600 hover:text-rose-600 px-3.5 py-1.5 rounded-full hover:bg-rose-50 transition-all flex items-center gap-1.5"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <span>Log Out</span>
          </button>
        </div>

      </div>
    </header>

    <!-- Main Workspace Container -->
    <main class="grow max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 z-10 relative">
      
      <!-- Top Alert Messages -->
      <div v-if="successMessage" class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-2xl p-4 flex items-center justify-between shadow-sm animate-in fade-in">
        <div class="flex items-center gap-2.5">
          <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
          </svg>
          <span>{{ successMessage }}</span>
        </div>
        <button @click="successMessage = ''" class="text-emerald-500 hover:text-emerald-700 text-xs font-bold">✕</button>
      </div>

      <div v-if="errorMessage" class="mb-6 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold rounded-2xl p-4 flex items-center justify-between shadow-sm animate-in fade-in">
        <div class="flex items-center gap-2.5">
          <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span>{{ errorMessage }}</span>
        </div>
        <button @click="errorMessage = ''" class="text-rose-500 hover:text-rose-700 text-xs font-bold">✕</button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-24 space-y-4">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-slate-950"></div>
        <p class="text-xs text-slate-500 font-medium">Loading your workspace hub...</p>
      </div>

      <!-- Two-Column Workspace Layout (Matching Screenshot 1) -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <!-- LEFT COLUMN: User Profile & Plan Details Box                        -->
        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <div class="lg:col-span-4 space-y-5">
          
          <!-- Box 1: User Profile Card -->
          <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xl p-6 relative overflow-hidden">
            <div class="flex items-center gap-4">
              
              <!-- Avatar: Image or Initials Badge -->
              <div class="relative shrink-0">
                <div class="w-16 h-16 rounded-2xl bg-slate-900 text-white flex items-center justify-center font-black text-xl tracking-wider shadow-md overflow-hidden border-2 border-slate-100">
                  <img
                    v-if="user.avatar_url"
                    :src="user.avatar_url"
                    :alt="user.name"
                    class="w-full h-full object-cover"
                  />
                  <span v-else>{{ userInitials }}</span>
                </div>
              </div>

              <!-- User Info -->
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                  <h3 class="text-base font-extrabold text-slate-950 truncate tracking-tight">
                    {{ user.name || 'Account Owner' }}
                  </h3>
                </div>
                <p class="text-xs text-slate-500 truncate mt-0.5" :title="user.email">
                  {{ user.email }}
                </p>
                <div class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                  <span>Owner Account</span>
                </div>
              </div>

            </div>
          </div>

          <!-- Box 2: Plan & Subscription Details Box -->
          <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xl p-6 space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
              <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                Plan &amp; Subscription Details
              </h4>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wide bg-emerald-50 text-emerald-700 border border-emerald-200">
                {{ subscription.status || 'Active' }}
              </span>
            </div>

            <!-- Active Plan Name -->
            <div class="space-y-1">
              <div class="text-[11px] text-slate-500 font-semibold">Active Plan</div>
              <div class="text-lg font-black text-slate-950 tracking-tight flex items-center justify-between">
                <span>{{ subscription.plan_name }}</span>
                <span class="text-xs font-bold text-slate-500 font-mono">{{ subscription.billing_cycle }}</span>
              </div>
            </div>

            <!-- Quota Meter: X / Y Companies Used -->
            <div class="space-y-2 pt-2 border-t border-slate-100">
              <div class="flex justify-between items-center text-xs">
                <span class="font-bold text-slate-700">Company Allowance:</span>
                <span class="font-extrabold text-slate-950 font-mono">
                  {{ quota.used_companies }} / {{ quota.max_companies }} {{ quota.max_companies === 1 ? 'Company' : 'Companies' }} Used
                </span>
              </div>
              
              <!-- Visual Progress Meter -->
              <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                <div 
                  class="h-full rounded-full transition-all duration-500"
                  :class="quota.is_limit_reached ? 'bg-amber-500' : 'bg-slate-900'"
                  :style="{ width: quotaPercentage + '%' }"
                ></div>
              </div>

              <p v-if="quota.is_limit_reached" class="text-[11px] font-semibold text-amber-600 flex items-center gap-1 mt-1">
                <span>⚠️</span>
                <span>Plan company quota limit reached</span>
              </p>
              <p v-else class="text-[11px] text-slate-500 font-medium">
                {{ quota.remaining }} more {{ quota.remaining === 1 ? 'company' : 'companies' }} available on your current plan.
              </p>
            </div>

            <!-- Expiry / Renewal Date -->
            <div class="pt-2 border-t border-slate-100 text-xs flex justify-between items-center">
              <span class="text-slate-500 font-medium">Renewal / Expiry:</span>
              <span class="font-bold text-slate-800">{{ subscription.expires_at || '14 Days Trial' }}</span>
            </div>

            <!-- Upgrade Plan Button -->
            <div class="pt-2">
              <router-link
                to="/plans"
                class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 border border-slate-200/80 text-slate-900 font-bold text-xs rounded-xl transition-all flex items-center justify-center gap-2 group shadow-xs"
              >
                <span>Upgrade Plan</span>
                <svg class="w-3.5 h-3.5 text-slate-500 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
              </router-link>
            </div>

          </div>

        </div>

        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <!-- RIGHT COLUMN: Company List & Action Bar                             -->
        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <div class="lg:col-span-8 space-y-5">
          
          <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xl p-6 sm:p-8 space-y-6">
            
            <!-- Header Action Row: ADD NEW COMPANY Button (Top-Right) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
              <div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-950 tracking-tight">
                  Company Workspaces
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                  Access your active retail companies or continue setting up drafts.
                </p>
              </div>

              <!-- ADD NEW COMPANY Button -->
              <button
                type="button"
                @click="handleAddNewCompany"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-slate-950 hover:bg-slate-800 text-white text-xs font-extrabold uppercase tracking-wider rounded-xl shadow-md transition-all active:scale-95 cursor-pointer shrink-0"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                <span>Add New Company</span>
              </button>
            </div>

            <!-- Company Records List (Drafts + Active Companies) -->
            <div class="space-y-3">
              
              <!-- 1. Incomplete / Draft Companies -->
              <div
                v-for="draft in drafts"
                :key="'draft-' + draft.id"
                class="bg-amber-50/40 border border-amber-200/70 hover:border-amber-300 rounded-2xl p-4 sm:p-5 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 group"
              >
                <div class="flex items-start sm:items-center gap-3.5">
                  <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 font-black text-xs flex items-center justify-center shrink-0">
                    {{ draft.order }}.
                  </div>
                  <div>
                    <div class="flex items-center gap-2 flex-wrap">
                      <h4 class="text-sm font-extrabold text-slate-900">
                        {{ draft.company_name }}
                      </h4>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                        {{ draft.step_label }}
                      </span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                      Setup in progress • Last edited {{ draft.updated_at || 'recently' }}
                    </p>
                  </div>
                </div>

                <!-- Action Links -->
                <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
                  <button
                    type="button"
                    @click="confirmDiscardDraft(draft)"
                    class="px-3 py-1.5 text-xs font-semibold text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                    title="Discard Draft"
                  >
                    Discard
                  </button>

                  <button
                    type="button"
                    @click="handleResumeDraft(draft)"
                    class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-xs transition-all cursor-pointer flex items-center gap-1.5"
                  >
                    <span>Resume setup</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                  </button>
                </div>
              </div>

              <!-- 2. Active / Launched Companies -->
              <div
                v-for="company in companies"
                :key="'company-' + company.id"
                class="bg-slate-50/70 border border-slate-200/80 hover:border-slate-300 rounded-2xl p-4 sm:p-5 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 group"
              >
                <div class="flex items-start sm:items-center gap-3.5">
                  <div class="w-8 h-8 rounded-xl bg-slate-900 text-white font-black text-xs flex items-center justify-center shrink-0">
                    {{ company.order }}.
                  </div>
                  <div>
                    <div class="flex items-center gap-2 flex-wrap">
                      <h4 class="text-sm font-extrabold text-slate-950">
                        {{ company.company_name }}
                      </h4>
                      <span v-if="company.is_current" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        Current Active Workspace
                      </span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                      {{ company.business_type || 'Retail & POS' }} • Created {{ company.created_at || 'recently' }}
                    </p>
                  </div>
                </div>

                <!-- Action Button: Go to Dashboard -->
                <div class="self-end sm:self-auto shrink-0">
                  <button
                    type="button"
                    @click="handleGoToDashboard(company)"
                    :disabled="switchingCompanyId === company.id"
                    class="px-4 py-2 bg-slate-950 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xs transition-all cursor-pointer flex items-center gap-2 disabled:opacity-50"
                  >
                    <span v-if="switchingCompanyId === company.id">Opening...</span>
                    <span v-else>Go to Dashboard</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Empty State: When zero active companies and zero drafts -->
              <div v-if="companies.length === 0 && drafts.length === 0" class="text-center py-12 px-4 border border-dashed border-slate-300 rounded-2xl bg-slate-50/50 space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 shadow-sm mx-auto flex items-center justify-center text-slate-400">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                  </svg>
                </div>
                <div>
                  <h4 class="text-sm font-bold text-slate-900">No workspaces yet</h4>
                  <p class="text-xs text-slate-500 max-w-sm mx-auto mt-0.5">
                    Click the "Add New Company" button above to launch the setup wizard for your first POS company.
                  </p>
                </div>
                <button
                  type="button"
                  @click="handleAddNewCompany"
                  class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-950 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-sm transition-all"
                >
                  <span>Launch First Company</span>
                </button>
              </div>

            </div>

          </div>

        </div>

      </div>

    </main>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- MODAL 1: Plan Quota Upgrade Alert (Screenshot 3 Logic)              -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div 
      v-if="showUpgradeModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm animate-in fade-in"
      @click.self="showUpgradeModal = false"
    >
      <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full p-6 text-center space-y-4">
        
        <!-- Warning Icon -->
        <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center mx-auto shadow-xs">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
        </div>

        <div class="space-y-2">
          <h3 class="text-lg font-black text-slate-950 tracking-tight">
            Company Limit Reached
          </h3>
          <p class="text-xs text-slate-600 leading-relaxed px-2">
            Your current plan (<strong class="text-slate-900">{{ subscription.plan_name }}</strong>) allows up to 
            <strong class="text-slate-900">{{ quota.max_companies }} {{ quota.max_companies === 1 ? 'company' : 'companies' }}</strong>. 
            To create additional companies, please upgrade your subscription plan.
          </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-2.5 pt-2">
          <button
            type="button"
            @click="showUpgradeModal = false"
            class="flex-1 py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all"
          >
            Cancel
          </button>
          <router-link
            to="/plans"
            class="flex-1 py-2.5 px-4 bg-slate-950 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-md transition-all flex items-center justify-center gap-1.5"
          >
            <span>View Plans / Upgrade</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
          </router-link>
        </div>

      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- MODAL 2: Discard Draft Confirmation                                 -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div 
      v-if="draftToDiscard"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm animate-in fade-in"
      @click.self="draftToDiscard = null"
    >
      <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-sm w-full p-6 text-center space-y-4">
        
        <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center mx-auto shadow-xs">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
          </svg>
        </div>

        <div class="space-y-1.5">
          <h3 class="text-base font-extrabold text-slate-950">
            Discard Workspace Draft?
          </h3>
          <p class="text-xs text-slate-500">
            Are you sure you want to delete the draft for <strong>{{ draftToDiscard.company_name }}</strong>? This action cannot be undone.
          </p>
        </div>

        <div class="flex gap-2.5 pt-2">
          <button
            type="button"
            @click="draftToDiscard = null"
            class="flex-1 py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all"
          >
            Keep Draft
          </button>
          <button
            type="button"
            @click="executeDiscardDraft"
            :disabled="discardingDraft"
            class="flex-1 py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-md transition-all disabled:opacity-50"
          >
            <span v-if="discardingDraft">Discarding...</span>
            <span v-else>Discard</span>
          </button>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const loading = ref(true);
const successMessage = ref('');
const errorMessage = ref('');

const user = ref({
  id: null,
  name: '',
  email: '',
  avatar_url: null,
});

const subscription = ref({
  plan_name: 'Standard (Free Trial)',
  plan_slug: 'standard',
  max_companies: 1,
  billing_cycle: 'Monthly',
  expires_at: '',
  status: 'active',
});

const quota = ref({
  used_companies: 0,
  max_companies: 1,
  remaining: 1,
  is_limit_reached: false,
});

const companies = ref([]);
const drafts = ref([]);

const showUpgradeModal = ref(false);
const draftToDiscard = ref(null);
const discardingDraft = ref(false);
const switchingCompanyId = ref(null);

const userInitials = computed(() => {
  if (!user.value?.name) return 'U';
  const parts = user.value.name.trim().split(/\s+/);
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  }
  return parts[0].substring(0, 2).toUpperCase();
});

const quotaPercentage = computed(() => {
  if (!quota.value.max_companies || quota.value.max_companies <= 0) return 0;
  return Math.min(100, Math.round((quota.value.used_companies / quota.value.max_companies) * 100));
});

const fetchHubData = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/owner/companies/hub-data');
    user.value = data.user || {};
    subscription.value = data.subscription || {};
    quota.value = data.quota || {};
    companies.value = data.active_companies || [];
    drafts.value = data.draft_companies || [];

    if (route.query.limit_reached) {
      showUpgradeModal.value = true;
    }
  } catch (err) {
    console.error('Failed to load hub data:', err);
    errorMessage.value = 'Failed to load workspaces. Please refresh the page.';
  } finally {
    loading.value = false;
  }
};

const handleAddNewCompany = () => {
  // Plan quota enforcement guard
  if (quota.value.is_limit_reached) {
    showUpgradeModal.value = true;
    return;
  }

  const token = localStorage.getItem('auth_token');
  let targetUrl = '/company-setup?mode=create_new&start_fresh_flow=true';
  if (token) {
    targetUrl += `&token=${encodeURIComponent(token)}`;
  }
  window.location.href = targetUrl;
};

const handleResumeDraft = (draft) => {
  // If user already has maximum active companies allowed, warn before resuming to finalize
  if (quota.value.is_limit_reached) {
    showUpgradeModal.value = true;
    return;
  }

  const token = localStorage.getItem('auth_token');
  let targetUrl = `/company-setup?continue_draft_id=${draft.id}`;
  if (token) {
    targetUrl += `&token=${encodeURIComponent(token)}`;
  }
  window.location.href = targetUrl;
};

const handleGoToDashboard = async (company) => {
  switchingCompanyId.value = company.id;
  try {
    await axios.post(`/api/companies/switch/${company.id}`);
    await authStore.fetchUser();
    window.location.href = '/dashboard';
  } catch (err) {
    console.error('Failed to switch workspace:', err);
    errorMessage.value = 'Failed to switch workspace. Please try again.';
    switchingCompanyId.value = null;
  }
};

const confirmDiscardDraft = (draft) => {
  draftToDiscard.value = draft;
};

const executeDiscardDraft = async () => {
  if (!draftToDiscard.value) return;
  discardingDraft.value = true;
  try {
    await axios.delete(`/api/owner/companies/draft/${draftToDiscard.value.id}`);
    successMessage.value = 'Draft workspace discarded successfully.';
    draftToDiscard.value = null;
    await fetchHubData();
  } catch (err) {
    console.error('Failed to discard draft:', err);
    errorMessage.value = 'Failed to discard draft workspace.';
  } finally {
    discardingDraft.value = false;
  }
};

const handleLogout = async () => {
  try {
    await authStore.logout();
    window.location.href = '/login';
  } catch (err) {
    window.location.href = '/login';
  }
};

onMounted(() => {
  fetchHubData();
});
</script>
