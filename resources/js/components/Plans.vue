<template>
  <div class="min-h-screen bg-slate-50 text-slate-900 font-sans selection:bg-slate-900 selection:text-white relative overflow-x-hidden">
    
    <!-- Ambient Background Radial Glows -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[600px] bg-gradient-to-tr from-slate-200/60 via-gray-100/40 to-transparent blur-[120px] rounded-full pointer-events-none"></div>

    <!-- Header / Top Navbar (Shared Component) -->
    <Navbar />

    <!-- Plans & Pricing Section -->
    <section class="py-16 sm:py-24 px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto relative z-10">
      <div class="text-center space-y-4 max-w-2xl mx-auto mb-16">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-slate-200 border border-slate-300 text-slate-900 text-xs font-extrabold uppercase tracking-wider">
          <span>SUBSCRIPTION TIERS</span>
        </div>
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-950 tracking-tight">
          Flexible Plans for Every Business
        </h1>
        <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
          Select the tailored subscription plan designed for your retail store or multi-company enterprise accounting operations.
        </p>
      </div>

      <!-- Billing Toggle Switch -->
      <div class="flex justify-center items-center gap-3 mb-10 text-sm font-semibold max-w-sm mx-auto">
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
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 items-stretch max-w-4xl mx-auto">
        
        <!-- BASIC PLAN CARD -->
        <div class="bg-white border border-slate-200/90 rounded-3xl p-8 flex flex-col justify-between shadow-xl transform hover:scale-[1.015] transition-transform duration-300 ease-out outline-none focus:outline-none relative group">
          <div>
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-extrabold uppercase tracking-wider text-slate-900 bg-slate-100 border border-slate-300 px-3 py-1 rounded-full">
                BASIC PLAN
              </span>
              <span class="text-xs text-slate-500 font-semibold">Standard Retail</span>
            </div>

            <!-- Price -->
            <div class="mb-6">
              <span class="text-4xl font-black text-slate-950">{{ billingCycle === 'monthly' ? '$29' : '$279' }}</span>
              <span class="text-slate-500 text-sm font-medium"> / {{ billingCycle === 'monthly' ? 'month' : 'year' }}</span>
            </div>

            <!-- Description -->
            <p class="text-slate-600 text-xs leading-relaxed mb-6">
              Essential POS solution for single-store setups with complete inventory & return management.
            </p>

            <!-- Features List -->
            <div class="space-y-3.5 mb-8 text-xs sm:text-sm text-slate-800">
              <div class="flex items-start gap-3">
                <div class="w-5 h-5 rounded-full bg-slate-100 border border-slate-300 text-slate-950 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</div>
                <span><strong>Feature 1:</strong> Single Company Registration</span>
              </div>
              <div class="flex items-start gap-3">
                <div class="w-5 h-5 rounded-full bg-slate-100 border border-slate-300 text-slate-950 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</div>
                <span><strong>Feature 2:</strong> Complete Inventory Management</span>
              </div>
              <div class="flex items-start gap-3">
                <div class="w-5 h-5 rounded-full bg-slate-100 border border-slate-300 text-slate-950 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</div>
                <span><strong>Feature 3:</strong> Sales & Purchase Management</span>
              </div>
              <div class="flex items-start gap-3">
                <div class="w-5 h-5 rounded-full bg-slate-100 border border-slate-300 text-slate-950 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</div>
                <span><strong>Feature 4:</strong> Sales & Purchase Returns</span>
              </div>
            </div>
          </div>

          <!-- Disabled CTA Button -->
          <button
            type="button"
            disabled
            class="w-full text-center bg-slate-100 text-slate-400 font-extrabold py-3.5 rounded-full border border-slate-200 transition-all text-xs tracking-wide uppercase disabled cursor-not-allowed opacity-70 shadow-none disabled:cursor-not-allowed disabled:opacity-70 outline-none focus:outline-none"
          >
            SELECT BASIC PLAN
          </button>
        </div>

        <!-- MASTER PLAN CARD (RECOMMENDED) -->
        <div class="bg-slate-950 text-white border-2 border-slate-900 rounded-3xl p-8 flex flex-col justify-between shadow-2xl transform hover:scale-[1.015] transition-transform duration-300 ease-out outline-none focus:outline-none relative group md:-translate-y-2">
          
          <!-- Recommended Badge -->
          <div class="absolute -top-4 right-8 bg-white text-slate-950 text-[10px] font-black uppercase tracking-widest px-4 py-1.5 rounded-full shadow-lg border border-slate-200">
            ★ RECOMMENDED
          </div>

          <div>
            <!-- Header -->
            <div class="flex items-center justify-between mb-4 pt-1">
              <span class="text-xs font-extrabold uppercase tracking-wider text-white bg-white/10 border border-white/20 px-3 py-1 rounded-full">
                MASTER PLAN
              </span>
              <span class="text-xs text-slate-400 font-semibold">Enterprise Accounting</span>
            </div>

            <!-- Price -->
            <div class="mb-6">
              <span class="text-4xl font-black text-white">{{ billingCycle === 'monthly' ? '$79' : '$759' }}</span>
              <span class="text-slate-400 text-sm font-medium"> / {{ billingCycle === 'monthly' ? 'month' : 'year' }}</span>
            </div>

            <!-- Description -->
            <p class="text-slate-300 text-xs leading-relaxed mb-6">
              Full-featured multi-company POS & Double-Entry Accounting platform with advanced financial ledgers.
            </p>

            <!-- Features List -->
            <div class="space-y-3.5 mb-8 text-xs sm:text-sm text-slate-100">
              <div class="flex items-start gap-3">
                <div class="w-5 h-5 rounded-full bg-white/10 border border-white/20 text-white flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</div>
                <span><strong>Feature 1:</strong> Multi-Company Support</span>
              </div>
              <div class="flex items-start gap-3">
                <div class="w-5 h-5 rounded-full bg-white/10 border border-white/20 text-white flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</div>
                <span><strong>Feature 2:</strong> Everything in Basic Plan</span>
              </div>
              <div class="flex items-start gap-3">
                <div class="w-5 h-5 rounded-full bg-white/10 border border-white/20 text-white flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</div>
                <span><strong>Feature 3:</strong> Advanced Accounting & Finance Module (General Ledger, Chart of Accounts, Bank Subledgers)</span>
              </div>
              <div class="flex items-start gap-3">
                <div class="w-5 h-5 rounded-full bg-white/10 border border-white/20 text-white flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</div>
                <span><strong>Feature 4:</strong> Priority Multi-Branch Support</span>
              </div>
            </div>
          </div>

          <!-- Disabled CTA Button -->
          <button
            type="button"
            disabled
            class="w-full text-center bg-slate-800 text-slate-400 font-extrabold py-3.5 rounded-full shadow-md transition-all text-xs tracking-wide uppercase disabled cursor-not-allowed opacity-70 border border-slate-700 disabled:cursor-not-allowed disabled:opacity-70 outline-none focus:outline-none"
          >
            SELECT MASTER PLAN
          </button>
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
        <p>© 2026 POS System. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import Navbar from '@/components/shared/Navbar.vue';

const authStore = useAuthStore();
const billingCycle = ref('monthly');
</script>
