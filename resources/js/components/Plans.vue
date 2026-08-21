<template>
  <div class="min-h-screen bg-slate-50 text-slate-900 font-sans selection:bg-slate-900 selection:text-white relative overflow-x-hidden">
    
    <!-- Ambient Background Radial Glows -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[600px] bg-gradient-to-tr from-slate-200/60 via-gray-100/40 to-transparent blur-[120px] rounded-full pointer-events-none"></div>

    <!-- Header / Top Navbar (Shared Component) -->
    <Navbar />

    <!-- Plans & Pricing Section -->
    <section class="py-16 sm:py-24 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative z-10">
      <div class="text-center space-y-4 max-w-2xl mx-auto mb-16">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-slate-200 border border-slate-300 text-slate-900 text-xs font-extrabold uppercase tracking-wider">
          <span>SUBSCRIPTION TIERS</span>
        </div>
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-950 tracking-tight">
          Flexible Plans for Every Business
        </h1>
        <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
          Select the tailored subscription plan designed for your store or multi-company enterprise operations.
        </p>
      </div>

      <!-- Billing Toggle Switch -->
      <div class="flex justify-center items-center gap-3 mb-12 text-sm font-semibold max-w-sm mx-auto">
        <span :class="billingCycle === 'monthly' ? 'text-slate-950 font-bold' : 'text-slate-500'">Monthly</span>
        <button 
          @click="billingCycle = billingCycle === 'monthly' ? 'yearly' : 'monthly'"
          class="relative w-14 h-7 rounded-full bg-slate-300 transition-colors duration-300 outline-none focus:outline-none focus:ring-2 focus:ring-slate-950 focus:ring-offset-2"
          :class="{'bg-slate-950': billingCycle === 'yearly'}"
        >
          <span 
            class="absolute top-1 left-1 w-5 h-5 rounded-full bg-white transition-transform duration-300 shadow-sm"
            :class="{'translate-x-7': billingCycle === 'yearly'}"
          ></span>
        </button>
        <span class="flex items-center gap-1.5" :class="billingCycle === 'yearly' ? 'text-slate-950 font-bold' : 'text-slate-500'">
          Yearly
          <span class="text-[9px] bg-emerald-100 border border-emerald-200 text-emerald-700 px-1.5 py-0.5 rounded-full font-black uppercase tracking-wider">Save 20%</span>
        </span>
      </div>

      <!-- Plans Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 items-stretch max-w-full mx-auto">
        
        <div 
          v-for="plan in displayPlans" 
          :key="plan.id || plan.slug"
          class="rounded-3xl p-6 flex flex-col justify-between shadow-lg transform hover:scale-[1.015] transition-transform duration-300 relative group"
          :class="[
            plan.is_popular 
              ? 'bg-slate-950 text-white border-2 border-slate-800 shadow-2xl lg:-translate-y-2' 
              : plan.is_custom 
                ? 'bg-slate-100 text-slate-900 border border-slate-300' 
                : 'bg-white text-slate-900 border border-slate-200/90'
          ]"
        >
          <!-- Most Popular Badge -->
          <div 
            v-if="plan.is_popular" 
            class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gradient-to-r from-amber-400 to-orange-500 text-slate-950 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-lg"
          >
            Most Popular
          </div>

          <div>
            <!-- Header Tag -->
            <div class="flex items-center justify-between mb-4" :class="plan.is_popular ? 'mt-2' : ''">
              <span 
                class="text-[10px] font-extrabold uppercase tracking-wider px-2 py-1 rounded-full"
                :class="[
                  plan.is_popular 
                    ? 'text-white bg-white/10 border border-white/20' 
                    : plan.is_custom 
                      ? 'text-slate-700 bg-slate-200 border border-slate-300' 
                      : 'text-slate-900 bg-slate-100 border border-slate-300'
                ]"
              >
                {{ plan.name }}
              </span>
            </div>

            <!-- Price -->
            <div class="mb-4">
              <template v-if="plan.is_custom">
                <span class="text-2xl font-black" :class="plan.is_popular ? 'text-white' : 'text-slate-950'">Contact Sales</span>
                <span class="text-xs font-medium block mt-1" :class="plan.is_popular ? 'text-slate-400' : 'text-slate-500'">Custom Pricing</span>
              </template>
              <template v-else-if="plan.monthly_price == 0">
                <span class="text-3xl font-black" :class="plan.is_popular ? 'text-white' : 'text-slate-950'">Free</span>
                <span class="text-xs font-medium block mt-1" :class="plan.is_popular ? 'text-slate-400' : 'text-slate-500'">{{ plan.trial_days || 14 }}-Day Free Trial</span>
              </template>
              <template v-else>
                <span class="text-3xl font-black" :class="plan.is_popular ? 'text-white' : 'text-slate-950'">
                  ${{ billingCycle === 'monthly' ? plan.monthly_price : plan.yearly_price }}
                </span>
                <span class="text-xs font-medium block mt-1" :class="plan.is_popular ? 'text-slate-400' : 'text-slate-500'">
                  / {{ billingCycle === 'monthly' ? 'month' : 'year' }}
                </span>
              </template>
            </div>

            <!-- Description -->
            <p class="text-xs leading-relaxed mb-6 h-12" :class="plan.is_popular ? 'text-slate-300' : 'text-slate-600'">
              {{ plan.description }}
            </p>

            <!-- Features Bullet List -->
            <div class="space-y-3 mb-8 text-xs" :class="plan.is_popular ? 'text-slate-100' : 'text-slate-800'">
              <!-- Company Limit -->
              <div class="flex items-start gap-2">
                <div 
                  class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5"
                  :class="plan.is_popular ? 'bg-white/10 border border-white/20 text-white' : 'bg-slate-100 border border-slate-300 text-slate-950'"
                >✓</div>
                <span>
                  <strong>{{ plan.max_companies }}</strong> {{ plan.max_companies === 1 ? 'Company' : 'Companies' }} Allowed
                </span>
              </div>

              <!-- User Limit -->
              <div class="flex items-start gap-2">
                <div 
                  class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5"
                  :class="plan.is_popular ? 'bg-white/10 border border-white/20 text-white' : 'bg-slate-100 border border-slate-300 text-slate-950'"
                >✓</div>
                <span>
                  <strong>{{ plan.max_users_per_company }}</strong> {{ plan.max_users_per_company === 1 ? 'User' : 'Users' }} per Company
                </span>
              </div>

              <!-- Custom Bullet Features -->
              <div 
                v-for="(feature, idx) in plan.features" 
                :key="idx"
                class="flex items-start gap-2"
              >
                <div 
                  class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5"
                  :class="plan.is_popular ? 'bg-white/10 border border-white/20 text-white' : 'bg-slate-100 border border-slate-300 text-slate-950'"
                >✓</div>
                <span>{{ feature }}</span>
              </div>
            </div>
          </div>

          <!-- CTA Button -->
          <router-link 
            :to="`/register?plan=${plan.slug}&cycle=${billingCycle}`" 
            class="w-full block text-center font-extrabold py-3 rounded-full transition-all text-xs tracking-wide uppercase shadow-md cursor-pointer"
            :class="[
              plan.is_popular 
                ? 'bg-white text-slate-950 hover:bg-slate-100 shadow-[0_0_15px_rgba(255,255,255,0.3)]' 
                : plan.is_custom 
                  ? 'bg-slate-800 text-white hover:bg-slate-950' 
                  : 'bg-slate-100 hover:bg-slate-200 text-slate-900 border border-slate-300'
            ]"
          >
            {{ plan.is_custom ? 'Contact Sales' : (plan.monthly_price == 0 ? 'Start Free Trial' : `Select ${plan.name}`) }}
          </router-link>
        </div>

      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-950 text-slate-400 py-10 px-4 border-t border-slate-800 text-xs text-center mt-12">
      <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2">
          <div class="w-6 h-6 rounded-full bg-white text-slate-950 flex items-center justify-center font-black text-[10px]">P</div>
          <span class="text-white font-bold text-sm">POS & Accounting</span>
        </div>
        <p>© 2026 AcuteBills. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import Navbar from '@/components/shared/Navbar.vue';

const billingCycle = ref('monthly');
const apiPlans = ref([]);

const defaultFallbackPlans = [
  {
    id: 1,
    name: 'Standard',
    slug: 'standard',
    description: 'Free trial 14 days. 1 user & 1 company allowed.',
    monthly_price: 0,
    yearly_price: 0,
    trial_days: 14,
    max_companies: 1,
    max_users_per_company: 1,
    is_popular: false,
    is_custom: false,
    features: ['Essential POS Features']
  },
  {
    id: 2,
    name: 'Basic',
    slug: 'basic',
    description: '$20/month. 1 user & 1 company allowed.',
    monthly_price: 20,
    yearly_price: 192,
    trial_days: 0,
    max_companies: 1,
    max_users_per_company: 1,
    is_popular: false,
    is_custom: false,
    features: ['Inventory & Sales', 'Standard Support']
  },
  {
    id: 3,
    name: 'Advance',
    slug: 'advance',
    description: '$50/month. 2 companies allowed (20 users each).',
    monthly_price: 50,
    yearly_price: 480,
    trial_days: 0,
    max_companies: 2,
    max_users_per_company: 20,
    is_popular: true,
    is_custom: false,
    features: ['Advanced Accounting', 'Multi-Warehouse']
  },
  {
    id: 4,
    name: 'Enterprise',
    slug: 'enterprise',
    description: '$100/month. 10 companies each allowing 100 users.',
    monthly_price: 100,
    yearly_price: 960,
    trial_days: 0,
    max_companies: 10,
    max_users_per_company: 100,
    is_popular: false,
    is_custom: false,
    features: ['Priority Support & SLA', 'Full System Access']
  },
  {
    id: 5,
    name: 'Custom',
    slug: 'custom',
    description: 'Contact sales team for better pricing.',
    monthly_price: 0,
    yearly_price: 0,
    trial_days: 0,
    max_companies: 999,
    max_users_per_company: 999,
    is_popular: false,
    is_custom: true,
    features: ['Dedicated Account Manager', 'Bespoke Integrations']
  }
];

const displayPlans = computed(() => {
  return apiPlans.value.length > 0 ? apiPlans.value : defaultFallbackPlans;
});

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/subscription-plans');
    if (Array.isArray(data) && data.length > 0) {
      apiPlans.value = data;
    }
  } catch (e) {
    console.error("Failed to load dynamic subscription plans", e);
  }
});
</script>
