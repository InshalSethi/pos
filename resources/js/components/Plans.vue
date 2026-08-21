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
          Select the tailored subscription plan designed for your retail store or multi-company enterprise operations.
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
        
        <!-- STARTER PLAN -->
        <div class="bg-white border border-slate-200/90 rounded-3xl p-6 flex flex-col justify-between shadow-lg transform hover:scale-[1.015] transition-transform duration-300 relative group">
          <div>
            <div class="flex items-center justify-between mb-4">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-900 bg-slate-100 border border-slate-300 px-2 py-1 rounded-full">STARTER</span>
            </div>
            <div class="mb-4">
              <span class="text-3xl font-black text-slate-950">Free</span>
              <span class="text-slate-500 text-xs font-medium block mt-1">14-Day Trial</span>
            </div>
            <p class="text-slate-600 text-xs leading-relaxed mb-6 h-12">
              Try the platform with no payment method required.
            </p>
            <div class="space-y-3 mb-8 text-xs text-slate-800">
              <div class="flex items-start gap-2">
                <div class="w-4 h-4 rounded-full bg-slate-100 border border-slate-300 text-slate-950 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">✓</div>
                <span><strong>1 Device</strong> Limit</span>
              </div>
              <div class="flex items-start gap-2">
                <div class="w-4 h-4 rounded-full bg-slate-100 border border-slate-300 text-slate-950 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">✓</div>
                <span>Full Feature Access</span>
              </div>
            </div>
          </div>
          <router-link to="/register?plan=starter" class="w-full block text-center bg-slate-100 hover:bg-slate-200 text-slate-900 font-extrabold py-3 rounded-full border border-slate-300 transition-all text-xs tracking-wide uppercase">
            Start Free Trial
          </router-link>
        </div>

        <!-- BASIC PLAN -->
        <div class="bg-white border border-slate-200/90 rounded-3xl p-6 flex flex-col justify-between shadow-lg transform hover:scale-[1.015] transition-transform duration-300 relative group">
          <div>
            <div class="flex items-center justify-between mb-4">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-blue-900 bg-blue-100 border border-blue-300 px-2 py-1 rounded-full">BASIC</span>
            </div>
            <div class="mb-4">
              <span class="text-3xl font-black text-slate-950">{{ billingCycle === 'monthly' ? '$80' : '$768' }}</span>
              <span class="text-slate-500 text-xs font-medium block mt-1">/ {{ billingCycle === 'monthly' ? 'month' : 'year' }}</span>
            </div>
            <p class="text-slate-600 text-xs leading-relaxed mb-6 h-12">
              Essential POS solution for single-store setups.
            </p>
            <div class="space-y-3 mb-8 text-xs text-slate-800">
              <div class="flex items-start gap-2">
                <div class="w-4 h-4 rounded-full bg-blue-50 border border-blue-200 text-blue-700 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">✓</div>
                <span><strong>1 Device</strong> Limit</span>
              </div>
              <div class="flex items-start gap-2">
                <div class="w-4 h-4 rounded-full bg-blue-50 border border-blue-200 text-blue-700 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">✓</div>
                <span>Inventory & Sales</span>
              </div>
            </div>
          </div>
          <router-link :to="`/register?plan=basic&cycle=${billingCycle}`" class="w-full block text-center bg-white text-blue-700 hover:bg-blue-50 border-2 border-blue-600 font-extrabold py-3 rounded-full transition-all text-xs tracking-wide uppercase">
            Select Basic
          </router-link>
        </div>

        <!-- MASTER PLAN -->
        <div class="bg-slate-950 text-white border-2 border-slate-800 rounded-3xl p-6 flex flex-col justify-between shadow-2xl transform hover:scale-[1.015] transition-transform duration-300 relative group lg:-translate-y-2">
          <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gradient-to-r from-amber-400 to-orange-500 text-slate-950 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-lg">
            Most Popular
          </div>
          <div>
            <div class="flex items-center justify-between mb-4 mt-2">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-white bg-white/10 border border-white/20 px-2 py-1 rounded-full">MASTER</span>
            </div>
            <div class="mb-4">
              <span class="text-3xl font-black text-white">{{ billingCycle === 'monthly' ? '$200' : '$1,920' }}</span>
              <span class="text-slate-400 text-xs font-medium block mt-1">/ {{ billingCycle === 'monthly' ? 'month' : 'year' }}</span>
            </div>
            <p class="text-slate-300 text-xs leading-relaxed mb-6 h-12">
              Advanced accounting and multi-company features.
            </p>
            <div class="space-y-3 mb-8 text-xs text-slate-100">
              <div class="flex items-start gap-2">
                <div class="w-4 h-4 rounded-full bg-white/10 border border-white/20 text-white flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">✓</div>
                <span><strong>3 Devices</strong> Limit</span>
              </div>
              <div class="flex items-start gap-2">
                <div class="w-4 h-4 rounded-full bg-white/10 border border-white/20 text-white flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">✓</div>
                <span>Advanced Accounting</span>
              </div>
              <div class="flex items-start gap-2">
                <div class="w-4 h-4 rounded-full bg-white/10 border border-white/20 text-white flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">✓</div>
                <span>Multi-Company</span>
              </div>
            </div>
          </div>
          <router-link :to="`/register?plan=master&cycle=${billingCycle}`" class="w-full block text-center bg-white text-slate-950 hover:bg-slate-100 font-extrabold py-3 rounded-full transition-all text-xs tracking-wide uppercase shadow-[0_0_15px_rgba(255,255,255,0.3)]">
            Select Master
          </router-link>
        </div>

        <!-- ELITE PLAN -->
        <div class="bg-white border border-slate-200/90 rounded-3xl p-6 flex flex-col justify-between shadow-lg transform hover:scale-[1.015] transition-transform duration-300 relative group">
          <div>
            <div class="flex items-center justify-between mb-4">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-900 bg-purple-100 border border-purple-300 px-2 py-1 rounded-full">ELITE</span>
            </div>
            <div class="mb-4">
              <span class="text-3xl font-black text-slate-950">{{ billingCycle === 'monthly' ? '$650' : '$6,240' }}</span>
              <span class="text-slate-500 text-xs font-medium block mt-1">/ {{ billingCycle === 'monthly' ? 'month' : 'year' }}</span>
            </div>
            <p class="text-slate-600 text-xs leading-relaxed mb-6 h-12">
              For high-volume retail chains and large enterprises.
            </p>
            <div class="space-y-3 mb-8 text-xs text-slate-800">
              <div class="flex items-start gap-2">
                <div class="w-4 h-4 rounded-full bg-purple-50 border border-purple-200 text-purple-700 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">✓</div>
                <span><strong>10 Devices</strong> Limit</span>
              </div>
              <div class="flex items-start gap-2">
                <div class="w-4 h-4 rounded-full bg-purple-50 border border-purple-200 text-purple-700 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">✓</div>
                <span>Priority Support</span>
              </div>
            </div>
          </div>
          <router-link :to="`/register?plan=elite&cycle=${billingCycle}`" class="w-full block text-center bg-white text-purple-700 hover:bg-purple-50 border-2 border-purple-600 font-extrabold py-3 rounded-full transition-all text-xs tracking-wide uppercase">
            Select Elite
          </router-link>
        </div>

        <!-- CUSTOM PLAN -->
        <div class="bg-slate-100 border border-slate-300 rounded-3xl p-6 flex flex-col justify-between shadow-inner transform hover:scale-[1.015] transition-transform duration-300 relative group">
          <div>
            <div class="flex items-center justify-between mb-4">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-700 bg-slate-200 border border-slate-300 px-2 py-1 rounded-full">CUSTOM</span>
            </div>
            <div class="mb-4">
              <span class="text-2xl font-black text-slate-950">Starts $1,500</span>
              <span class="text-slate-500 text-xs font-medium block mt-1">/ {{ billingCycle === 'monthly' ? 'month' : 'year' }}</span>
            </div>
            <p class="text-slate-600 text-xs leading-relaxed mb-6 h-12">
              Bespoke deployment for massive scale (20 to 50+ devices).
            </p>
            <div class="space-y-3 mb-8 text-xs text-slate-800">
              <div class="flex items-start gap-2">
                <div class="w-4 h-4 rounded-full bg-slate-200 border border-slate-300 text-slate-700 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">✓</div>
                <span><strong>20-50 Devices</strong></span>
              </div>
              <div class="flex items-start gap-2">
                <div class="w-4 h-4 rounded-full bg-slate-200 border border-slate-300 text-slate-700 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">✓</div>
                <span>Dedicated Account Manager</span>
              </div>
            </div>
          </div>
          <router-link :to="`/register?plan=custom&cycle=${billingCycle}`" class="w-full block text-center bg-slate-800 text-white hover:bg-slate-950 font-extrabold py-3 rounded-full transition-all text-xs tracking-wide uppercase shadow-md">
            Contact Sales
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
import { ref } from 'vue';
import Navbar from '@/components/shared/Navbar.vue';

const billingCycle = ref('monthly');
</script>
