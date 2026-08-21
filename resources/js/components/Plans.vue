<template>
  <div class="min-h-screen bg-slate-50 text-slate-900 font-sans selection:bg-slate-900 selection:text-white relative overflow-x-hidden flex flex-col">
    
    <!-- Ambient Background Radial Glows -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[600px] bg-gradient-to-tr from-slate-200/60 via-gray-100/40 to-transparent blur-[120px] rounded-full pointer-events-none"></div>

    <!-- Header / Top Navbar (Shared Component) -->
    <Navbar />

    <!-- Toast Notifications -->
    <div v-if="toastMessage" class="fixed top-20 right-6 z-50 animate-in fade-in slide-in-from-top-4 duration-300">
      <div 
        class="px-5 py-3 rounded-2xl shadow-xl flex items-center gap-3 text-xs font-bold border"
        :class="toastType === 'success' ? 'bg-emerald-950 text-white border-emerald-800' : 'bg-rose-950 text-white border-rose-800'"
      >
        <span v-if="toastType === 'success'" class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        <span v-else class="w-2 h-2 rounded-full bg-rose-400"></span>
        <span>{{ toastMessage }}</span>
      </div>
    </div>

    <!-- Plans & Pricing Section -->
    <main class="grow py-12 sm:py-16 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative z-10 w-full">
      
      <!-- Top Title & Subtitle -->
      <div class="text-center space-y-4 max-w-2xl mx-auto mb-12">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white border border-slate-200/90 text-slate-900 text-xs font-extrabold uppercase tracking-wider shadow-xs">
          <span>SUBSCRIPTION TIERS</span>
        </div>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-950 tracking-tight">
          Flexible Plans for Every Business
        </h1>
        <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
          Select the tailored subscription plan designed for your store or multi-company enterprise operations.
        </p>

        <!-- Current Plan Info Banner for Authenticated Users -->
        <div 
          v-if="isAuthenticated && userSubscription" 
          class="inline-flex items-center gap-2.5 px-4 py-2 rounded-2xl bg-white border border-slate-200/90 shadow-sm text-xs font-semibold text-slate-800"
        >
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
          <span>You are currently subscribed to: <strong class="text-slate-950">{{ userSubscription.plan_name }}</strong> ({{ userSubscription.billing_cycle }})</span>
        </div>
      </div>

      <!-- Billing Toggle Switch -->
      <div class="flex justify-center items-center gap-3 mb-10 text-xs sm:text-sm font-semibold max-w-sm mx-auto">
        <span :class="billingCycle === 'monthly' ? 'text-slate-950 font-extrabold' : 'text-slate-500 font-medium'">Monthly</span>
        <button 
          @click="billingCycle = billingCycle === 'monthly' ? 'yearly' : 'monthly'"
          class="relative w-12 h-6.5 rounded-full bg-slate-200 transition-colors duration-300 outline-none focus:outline-none focus:ring-2 focus:ring-slate-950 focus:ring-offset-2 cursor-pointer"
          :class="{'bg-slate-950': billingCycle === 'yearly'}"
          type="button"
        >
          <span 
            class="absolute top-1 left-1 w-4.5 h-4.5 rounded-full bg-white transition-transform duration-300 shadow-sm"
            :class="{'translate-x-5.5': billingCycle === 'yearly'}"
          ></span>
        </button>
        <span class="flex items-center gap-1.5" :class="billingCycle === 'yearly' ? 'text-slate-950 font-extrabold' : 'text-slate-500 font-medium'">
          <span>Yearly</span>
          <span class="text-[9px] bg-emerald-100 border border-emerald-200 text-emerald-800 px-1.5 py-0.5 rounded-full font-black uppercase tracking-wider">Save 20%</span>
        </span>
      </div>

      <!-- Plans Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 items-stretch max-w-full mx-auto">
        
        <div 
          v-for="plan in displayPlans" 
          :key="plan.id || plan.slug"
          class="rounded-3xl p-6 flex flex-col justify-between shadow-xl transition-all duration-300 relative group"
          :class="[
            isCurrentPlan(plan)
              ? 'bg-white text-slate-900 border-2 border-emerald-500 shadow-emerald-500/10 ring-4 ring-emerald-50'
              : isDarkCard(plan) 
                ? 'bg-slate-950 text-white border-2 border-slate-800 shadow-2xl lg:-translate-y-2' 
                : plan.is_custom 
                  ? 'bg-slate-100 text-slate-900 border border-slate-300' 
                  : 'bg-white text-slate-900 border border-slate-200/90'
          ]"
        >
          <!-- Current Plan Badge (Green Top Pill) -->
          <div 
            v-if="isCurrentPlan(plan)" 
            class="absolute -top-3 left-1/2 -translate-x-1/2 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-md flex items-center gap-1.5 z-20"
          >
            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
            <span>Current Plan</span>
          </div>

          <!-- Most Popular Badge (Orange Top Pill) -->
          <div 
            v-else-if="plan.is_popular" 
            class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gradient-to-r from-amber-400 to-orange-500 text-slate-950 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-lg z-20"
          >
            Most Popular
          </div>

          <div>
            <!-- Header Tag -->
            <div class="flex items-center justify-between mb-4" :class="(plan.is_popular || isCurrentPlan(plan)) ? 'mt-2' : ''">
              <span 
                class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full"
                :class="[
                  isCurrentPlan(plan)
                    ? 'text-emerald-800 bg-emerald-50 border border-emerald-200'
                    : isDarkCard(plan) 
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
                <span class="text-2xl font-black" :class="isDarkCard(plan) ? 'text-white' : 'text-slate-950'">Contact Sales</span>
                <span class="text-xs font-medium block mt-1" :class="isDarkCard(plan) ? 'text-slate-400' : 'text-slate-500'">Custom Pricing</span>
              </template>
              <template v-else-if="plan.monthly_price == 0">
                <span class="text-3xl font-black" :class="isDarkCard(plan) ? 'text-white' : 'text-slate-950'">Free</span>
                <span class="text-xs font-medium block mt-1" :class="isDarkCard(plan) ? 'text-slate-400' : 'text-slate-500'">{{ plan.trial_days || 14 }}-Day Free Trial</span>
              </template>
              <template v-else>
                <span class="text-3xl font-black" :class="isDarkCard(plan) ? 'text-white' : 'text-slate-950'">
                  ${{ billingCycle === 'yearly' ? plan.yearly_price : plan.monthly_price }}
                </span>
                <span class="text-xs font-medium block mt-1" :class="isDarkCard(plan) ? 'text-slate-400' : 'text-slate-500'">
                  / {{ billingCycle === 'yearly' ? 'year' : 'month' }}
                </span>
              </template>
            </div>

            <!-- Description -->
            <p class="text-xs leading-relaxed mb-6 min-h-[3rem]" :class="isDarkCard(plan) ? 'text-slate-300' : 'text-slate-600'">
              {{ plan.description }}
            </p>

            <!-- Features Bullet List -->
            <div class="space-y-3 mb-8 text-xs" :class="isDarkCard(plan) ? 'text-slate-100' : 'text-slate-800'">
              <!-- Company Limit -->
              <div class="flex items-start gap-2">
                <div 
                  class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5"
                  :class="[
                    isCurrentPlan(plan)
                      ? 'bg-emerald-100 border border-emerald-300 text-emerald-800'
                      : isDarkCard(plan) 
                        ? 'bg-white/10 border border-white/20 text-white' 
                        : 'bg-slate-100 border border-slate-300 text-slate-950'
                  ]"
                >✓</div>
                <span>
                  <strong>{{ plan.max_companies }}</strong> {{ plan.max_companies === 1 ? 'Company' : 'Companies' }} Allowed
                </span>
              </div>

              <!-- User Limit -->
              <div class="flex items-start gap-2">
                <div 
                  class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5"
                  :class="[
                    isCurrentPlan(plan)
                      ? 'bg-emerald-100 border border-emerald-300 text-emerald-800'
                      : isDarkCard(plan) 
                        ? 'bg-white/10 border border-white/20 text-white' 
                        : 'bg-slate-100 border border-slate-300 text-slate-950'
                  ]"
                >✓</div>
                <span>
                  <strong>{{ plan.max_users_per_company }}</strong> {{ plan.max_users_per_company === 1 ? 'User' : 'Users' }} per Company
                </span>
              </div>

              <!-- Custom Features -->
              <div 
                v-for="(feature, idx) in plan.features" 
                :key="idx"
                class="flex items-start gap-2"
              >
                <div 
                  class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5"
                  :class="[
                    isCurrentPlan(plan)
                      ? 'bg-emerald-100 border border-emerald-300 text-emerald-800'
                      : isDarkCard(plan) 
                        ? 'bg-white/10 border border-white/20 text-white' 
                        : 'bg-slate-100 border border-slate-300 text-slate-950'
                  ]"
                >✓</div>
                <span>{{ feature }}</span>
              </div>
            </div>
          </div>

          <!-- CTA Action Button (Conditional based on Auth & Plan Tier) -->
          <div>
            <!-- Case 1: Authenticated User -->
            <template v-if="isAuthenticated">
              
              <!-- 1A: Current Active Plan -->
              <button 
                v-if="isCurrentPlan(plan)"
                disabled
                type="button"
                class="w-full text-center font-bold py-3 rounded-full text-xs tracking-wide uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 cursor-default"
              >
                Current Active Plan
              </button>

              <!-- 1B: Free Trial (Standard) when user already has account -->
              <button 
                v-else-if="isTrialPlan(plan)"
                disabled
                type="button"
                class="w-full text-center font-bold py-3 rounded-full text-xs tracking-wide uppercase bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed opacity-60"
                title="Free trial has already been used on your account."
              >
                Trial Not Available
              </button>

              <!-- 1C: Lower Tier Plan (Downgrade lock) -->
              <button 
                v-else-if="isLowerTier(plan)"
                disabled
                type="button"
                class="w-full text-center font-bold py-3 rounded-full text-xs tracking-wide uppercase bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed opacity-60"
              >
                Lower Tier
              </button>

              <!-- 1D: Higher Tier Plan (Upgrade Button) -->
              <button 
                v-else
                type="button"
                @click="openUpgradeModal(plan)"
                class="w-full text-center font-extrabold py-3 rounded-full transition-all text-xs tracking-wide uppercase shadow-md cursor-pointer active:scale-95"
                :class="[
                  isDarkCard(plan)
                    ? 'bg-white text-slate-950 hover:bg-slate-100 shadow-[0_0_15px_rgba(255,255,255,0.3)]' 
                    : plan.is_custom 
                      ? 'bg-slate-800 text-white hover:bg-slate-950' 
                      : 'bg-slate-950 hover:bg-slate-800 text-white'
                ]"
              >
                {{ plan.is_custom ? 'Contact Sales' : `Upgrade to ${plan.name}` }}
              </button>

            </template>

            <!-- Case 2: Guest / Unauthenticated Visitor -->
            <template v-else>
              <router-link 
                :to="`/register?plan=${plan.slug}&cycle=${billingCycle}`" 
                class="w-full block text-center font-extrabold py-3 rounded-full transition-all text-xs tracking-wide uppercase shadow-md cursor-pointer hover:scale-[1.02]"
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
            </template>
          </div>

        </div>

      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-950 text-slate-400 py-10 px-4 border-t border-slate-800 text-xs text-center mt-12">
      <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2">
          <div class="w-6 h-6 rounded-full bg-white text-slate-950 flex items-center justify-center font-black text-[10px]">P</div>
          <span class="text-white font-bold text-sm">POS &amp; Accounting</span>
        </div>
        <p>© 2026 AcuteBills. All rights reserved.</p>
      </div>
    </footer>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- PAYMENT & UPGRADE CHECKOUT MODAL                                    -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div 
      v-if="showCheckoutModal && selectedUpgradePlan"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-md animate-in fade-in"
      @click.self="showCheckoutModal = false"
    >
      <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 sm:p-8 relative overflow-hidden transform transition-all max-h-[92vh] overflow-y-auto">
        
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-slate-950 text-white flex items-center justify-center font-bold text-base shadow-sm">
              ⚡
            </div>
            <div>
              <h3 class="text-base font-black text-slate-950">Upgrade Subscription</h3>
              <p class="text-xs text-slate-500 mt-0.5">
                Upgrading to <strong class="text-slate-900">{{ selectedUpgradePlan.name }} Plan</strong>
              </p>
            </div>
          </div>
          <button 
            @click="showCheckoutModal = false" 
            type="button" 
            class="text-slate-400 hover:text-slate-700 text-lg font-bold p-1 cursor-pointer"
          >
            ✕
          </button>
        </div>

        <!-- Plan Summary Box -->
        <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 mb-5 space-y-3">
          <div class="flex items-center justify-between">
            <div>
              <span class="text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded bg-slate-200 text-slate-800">
                {{ selectedUpgradePlan.name }}
              </span>
              <h4 class="text-lg font-black text-slate-950 mt-1">
                {{ (checkoutCycle === 'yearly' || checkoutCycle === 'annual') ? `$${selectedUpgradePlan.yearly_price} / year` : `$${selectedUpgradePlan.monthly_price} / month` }}
              </h4>
            </div>
            <div class="text-right">
              <span class="text-xs font-extrabold text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-full border border-emerald-200">
                {{ selectedUpgradePlan.max_companies }} Companies Allowed
              </span>
            </div>
          </div>

          <!-- Billing Toggle in Modal -->
          <div class="flex items-center justify-between pt-2 border-t border-slate-200 text-xs">
            <span class="text-slate-600 font-semibold">Billing Cycle:</span>
            <div class="flex items-center gap-1.5 bg-slate-200 p-1 rounded-xl">
              <button 
                type="button"
                @click="checkoutCycle = 'monthly'"
                class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer"
                :class="checkoutCycle === 'monthly' ? 'bg-white text-slate-950 shadow-xs' : 'text-slate-600 hover:text-slate-950'"
              >
                Monthly
              </button>
              <button 
                type="button"
                @click="checkoutCycle = 'yearly'"
                class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1 cursor-pointer"
                :class="(checkoutCycle === 'yearly' || checkoutCycle === 'annual') ? 'bg-white text-slate-950 shadow-xs' : 'text-slate-600 hover:text-slate-950'"
              >
                <span>Yearly</span>
                <span class="text-[9px] bg-emerald-500 text-white px-1 py-0.2 rounded font-black">-20%</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Payment Method Selector -->
        <div class="space-y-3 mb-5">
          <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider">
            Payment Method
          </label>

          <!-- Saved Card Option -->
          <label class="flex items-center justify-between p-3.5 border rounded-2xl cursor-pointer transition-all" :class="paymentMethod === 'existing' ? 'border-slate-950 bg-slate-50' : 'border-slate-200 hover:bg-slate-50/50'">
            <div class="flex items-center gap-3">
              <input type="radio" v-model="paymentMethod" value="existing" class="w-4 h-4 text-slate-950 focus:ring-slate-950" />
              <div class="flex items-center gap-2">
                <div class="bg-slate-950 text-white rounded px-2 py-0.5 text-[10px] font-black tracking-wider">VISA</div>
                <span class="text-xs font-bold text-slate-900">•••• 4242</span>
              </div>
            </div>
            <span class="text-[11px] font-semibold text-slate-500">Exp 12/28</span>
          </label>

          <!-- New Card Option -->
          <label class="flex items-center gap-3 p-3.5 border rounded-2xl cursor-pointer transition-all" :class="paymentMethod === 'new' ? 'border-slate-950 bg-slate-50' : 'border-slate-200 hover:bg-slate-50/50'">
            <input type="radio" v-model="paymentMethod" value="new" class="w-4 h-4 text-slate-950 focus:ring-slate-950" />
            <span class="text-xs font-bold text-slate-900">Use a new credit card</span>
          </label>

          <!-- New Card Form -->
          <div v-if="paymentMethod === 'new'" class="space-y-3 p-4 bg-slate-50 rounded-2xl border border-slate-200 animate-in fade-in">
            <div>
              <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Card Number</label>
              <input 
                :value="cardForm.cardNumber" 
                @input="handleCardNumberChange"
                maxlength="19" 
                type="text" 
                placeholder="0000 0000 0000 0000" 
                class="w-full px-3 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-950/20 text-xs bg-white font-mono tracking-widest"
                :class="cardErrors.cardNumber ? 'border-rose-500' : 'border-slate-200'"
              />
              <p v-if="cardErrors.cardNumber" class="mt-1 text-[10px] text-rose-500 font-bold">{{ cardErrors.cardNumber }}</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Expiry (MM/YY)</label>
                <input 
                  :value="cardForm.cardExpiry" 
                  @input="handleCardExpiryChange"
                  maxlength="5" 
                  type="text" 
                  placeholder="MM/YY" 
                  class="w-full px-3 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-950/20 text-xs bg-white font-mono tracking-widest"
                  :class="cardErrors.cardExpiry ? 'border-rose-500' : 'border-slate-200'"
                />
                <p v-if="cardErrors.cardExpiry" class="mt-1 text-[10px] text-rose-500 font-bold">{{ cardErrors.cardExpiry }}</p>
              </div>

              <div>
                <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">CVC</label>
                <input 
                  :value="cardForm.cardCvc" 
                  @input="handleCardCvcChange"
                  maxlength="4" 
                  type="text" 
                  placeholder="123" 
                  class="w-full px-3 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-950/20 text-xs bg-white font-mono tracking-widest"
                  :class="cardErrors.cardCvc ? 'border-rose-500' : 'border-slate-200'"
                />
                <p v-if="cardErrors.cardCvc" class="mt-1 text-[10px] text-rose-500 font-bold">{{ cardErrors.cardCvc }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Coupon Code Box -->
        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 mb-5 space-y-2">
          <label class="block text-[11px] font-extrabold text-slate-700">Have a Promo or Coupon Code?</label>
          <div class="flex gap-2">
            <input 
              v-model="couponCode" 
              type="text" 
              placeholder="e.g. SAVE20" 
              class="grow px-3 py-2 border border-slate-200 rounded-xl text-xs uppercase font-mono tracking-wider focus:outline-none bg-white"
            />
            <button 
              type="button" 
              @click="applyCoupon" 
              :disabled="couponLoading || !couponCode.trim()"
              class="px-4 py-2 bg-slate-950 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition-all disabled:opacity-50 cursor-pointer shrink-0"
            >
              <span v-if="couponLoading">...</span>
              <span v-else>Apply</span>
            </button>
          </div>
          <p v-if="couponMessage" class="text-[11px] font-bold text-emerald-600">✓ {{ couponMessage }}</p>
          <p v-if="couponError" class="text-[11px] font-bold text-rose-500">✕ {{ couponError }}</p>
        </div>

        <!-- Total Summary & Submit Action -->
        <div class="pt-3 border-t border-slate-100 flex flex-col gap-3">
          <div class="flex justify-between items-center text-xs">
            <span class="text-slate-500 font-medium">Total Billed Today:</span>
            <span class="text-xl font-black text-slate-950">
              ${{ finalCalculatedPrice }}
            </span>
          </div>

          <div class="flex gap-2.5">
            <button 
              type="button" 
              @click="showCheckoutModal = false"
              class="flex-1 py-3 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all cursor-pointer"
            >
              Cancel
            </button>
            <button 
              type="button" 
              @click="processUpgrade" 
              :disabled="isProcessingUpgrade"
              class="flex-2 py-3 px-4 bg-slate-950 hover:bg-slate-800 text-white text-xs font-extrabold rounded-xl shadow-md transition-all flex items-center justify-center gap-2 disabled:opacity-60 cursor-pointer"
            >
              <div v-if="isProcessingUpgrade" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
              <span>{{ isProcessingUpgrade ? 'Processing Upgrade...' : `Pay $${finalCalculatedPrice} & Upgrade Now` }}</span>
            </button>
          </div>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Navbar from '@/components/shared/Navbar.vue';
import { useAuthStore } from '@/stores/auth';
import { validateCardNumber, validateCardExpiry, validateCardCvc } from '@/composables/useCardValidation';

const router = useRouter();
const authStore = useAuthStore();

const billingCycle = ref('monthly');
const apiPlans = ref([]);
const isAuthenticated = ref(false);
const userSubscription = ref(null);

// Modal state
const showCheckoutModal = ref(false);
const selectedUpgradePlan = ref(null);
const checkoutCycle = ref('monthly');
const paymentMethod = ref('existing');
const isProcessingUpgrade = ref(false);

const cardForm = ref({
  cardNumber: '',
  cardExpiry: '',
  cardCvc: '',
});
const cardErrors = ref({});

const couponCode = ref('');
const couponLoading = ref(false);
const couponMessage = ref('');
const couponError = ref('');
const appliedCoupon = ref(null);

const toastMessage = ref('');
const toastType = ref('success');

const showToast = (message, type = 'success') => {
  toastMessage.value = message;
  toastType.value = type;
  setTimeout(() => {
    toastMessage.value = '';
  }, 4000);
};

const tierRanks = {
  standard: 0,
  starter: 0,
  free: 0,
  basic: 1,
  advance: 2,
  master: 2,
  enterprise: 3,
  elite: 3,
  custom: 4,
};

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
    monthly_price: 1500,
    yearly_price: 14400,
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

const getPlanRank = (plan) => {
  const slug = (plan.slug || plan.name || '').toLowerCase();
  return tierRanks[slug] ?? 1;
};

const currentTierRank = computed(() => {
  if (!userSubscription.value) return -1;
  const slug = (userSubscription.value.plan_slug || '').toLowerCase();
  return tierRanks[slug] ?? 0;
});

const isCurrentPlan = (plan) => {
  if (!isAuthenticated.value || !userSubscription.value) return false;
  const currentSlug = (userSubscription.value.plan_slug || '').toLowerCase();
  const planSlug = (plan.slug || plan.name || '').toLowerCase();
  return currentSlug === planSlug;
};

const isDarkCard = (plan) => {
  return plan.is_popular && !isCurrentPlan(plan);
};

const isTrialPlan = (plan) => {
  const rank = getPlanRank(plan);
  return rank === 0;
};

const isLowerTier = (plan) => {
  if (!isAuthenticated.value || !userSubscription.value) return false;
  const planRank = getPlanRank(plan);
  return planRank < currentTierRank.value;
};

const finalCalculatedPrice = computed(() => {
  if (!selectedUpgradePlan.value) return 0;
  const isYearly = checkoutCycle.value === 'yearly' || checkoutCycle.value === 'annual';
  let basePrice = isYearly ? selectedUpgradePlan.value.yearly_price : selectedUpgradePlan.value.monthly_price;
  basePrice = Number(basePrice) || 0;

  if (appliedCoupon.value) {
    return appliedCoupon.value.final_amount ?? basePrice;
  }
  return basePrice;
});

const handleCardNumberChange = (e) => {
  let raw = e.target.value.replace(/\D/g, '').slice(0, 16);
  let formatted = raw.replace(/(\d{4})(?=\d)/g, '$1 ').trim();
  cardForm.value.cardNumber = formatted;
  e.target.value = formatted;

  if (raw.length > 0 && raw.length < 13) {
    cardErrors.value.cardNumber = 'Card number must be 13 to 16 digits';
  } else {
    const res = validateCardNumber(formatted);
    if (res.valid) {
      delete cardErrors.value.cardNumber;
    } else if (raw.length >= 13) {
      cardErrors.value.cardNumber = res.message;
    }
  }
};

const handleCardExpiryChange = (e) => {
  let raw = e.target.value.replace(/\D/g, '').slice(0, 4);
  let formatted = raw;
  if (raw.length >= 3) {
    formatted = raw.slice(0, 2) + '/' + raw.slice(2);
  }
  cardForm.value.cardExpiry = formatted;
  e.target.value = formatted;

  if (formatted.length === 5) {
    const res = validateCardExpiry(formatted);
    if (res.valid) delete cardErrors.value.cardExpiry;
    else cardErrors.value.cardExpiry = res.message;
  } else if (formatted.length > 0 && formatted.length < 5) {
    cardErrors.value.cardExpiry = 'Expiry must be in MM/YY format';
  }
};

const handleCardCvcChange = (e) => {
  let raw = e.target.value.replace(/\D/g, '').slice(0, 4);
  cardForm.value.cardCvc = raw;
  e.target.value = raw;

  if (raw.length >= 3) {
    const res = validateCardCvc(raw);
    if (res.valid) delete cardErrors.value.cardCvc;
    else cardErrors.value.cardCvc = res.message;
  } else if (raw.length > 0 && raw.length < 3) {
    cardErrors.value.cardCvc = 'CVC must be 3 or 4 digits';
  }
};

const openUpgradeModal = (plan) => {
  selectedUpgradePlan.value = plan;
  checkoutCycle.value = billingCycle.value;
  cardErrors.value = {};
  couponCode.value = '';
  couponMessage.value = '';
  couponError.value = '';
  appliedCoupon.value = null;
  showCheckoutModal.value = true;
};

const applyCoupon = async () => {
  if (!couponCode.value.trim() || !selectedUpgradePlan.value) return;
  couponLoading.value = true;
  couponError.value = '';
  couponMessage.value = '';
  try {
    const { data } = await axios.post('/api/coupons/validate', {
      code: couponCode.value,
      plan: selectedUpgradePlan.value.slug,
      billing_cycle: checkoutCycle.value,
    });
    if (data.valid) {
      appliedCoupon.value = data;
      couponMessage.value = `Coupon "${data.coupon.code}" applied! Saved $${data.discount_amount.toFixed(2)}`;
    }
  } catch (e) {
    appliedCoupon.value = null;
    couponError.value = e.response?.data?.message || 'Invalid coupon code';
  } finally {
    couponLoading.value = false;
  }
};

watch(checkoutCycle, () => {
  if (appliedCoupon.value && couponCode.value) {
    applyCoupon();
  }
});

const validateCheckoutForm = () => {
  cardErrors.value = {};
  if (paymentMethod.value === 'new') {
    const numRes = validateCardNumber(cardForm.value.cardNumber);
    if (!numRes.valid) cardErrors.value.cardNumber = numRes.message;

    const expRes = validateCardExpiry(cardForm.value.cardExpiry);
    if (!expRes.valid) cardErrors.value.cardExpiry = expRes.message;

    const cvcRes = validateCardCvc(cardForm.value.cardCvc);
    if (!cvcRes.valid) cardErrors.value.cardCvc = cvcRes.message;

    if (Object.keys(cardErrors.value).length > 0) {
      showToast(cardErrors.value.cardNumber || cardErrors.value.cardExpiry || cardErrors.value.cardCvc, 'error');
      return false;
    }
  }
  return true;
};

const processUpgrade = async () => {
  if (!selectedUpgradePlan.value) return;
  if (!validateCheckoutForm()) return;

  isProcessingUpgrade.value = true;
  try {
    const payload = {
      plan: selectedUpgradePlan.value.slug,
      billing_cycle: checkoutCycle.value,
      payment_method: paymentMethod.value,
    };

    if (appliedCoupon.value) {
      payload.coupon_code = appliedCoupon.value.coupon.code;
    }

    if (paymentMethod.value === 'new') {
      payload.cardNumber = cardForm.value.cardNumber;
      payload.cardExpiry = cardForm.value.cardExpiry;
      payload.cardCvc = cardForm.value.cardCvc;
    }

    const { data } = await axios.post('/api/subscription/upgrade', payload);

    showCheckoutModal.value = false;
    showToast(data.message || `Subscription successfully upgraded to ${selectedUpgradePlan.value.name}!`, 'success');

    // Smooth redirect back to owner hub so quota meter immediately unlocks
    setTimeout(() => {
      window.location.href = '/owner/companies';
    }, 600);

  } catch (err) {
    console.error('Upgrade error:', err);
    const msg = err.response?.data?.message || 'Failed to complete upgrade. Please check payment details.';
    showToast(msg, 'error');
  } finally {
    isProcessingUpgrade.value = false;
  }
};

const fetchSubscriptionData = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    if (token) {
      const { data } = await axios.get('/api/subscription/current');
      if (data.authenticated && data.subscription) {
        isAuthenticated.value = true;
        userSubscription.value = data.subscription;
        if (data.subscription.billing_cycle) {
          billingCycle.value = data.subscription.billing_cycle.toLowerCase();
        }
      }
    }
  } catch (e) {
    isAuthenticated.value = false;
  }
};

onMounted(async () => {
  await fetchSubscriptionData();
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
