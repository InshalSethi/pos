<template>
  <div class="min-h-screen bg-slate-50 text-slate-900 font-sans selection:bg-slate-900 selection:text-white relative">
    
    <!-- Soft Ambient Monochrome Background Radial Glows -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[600px] bg-gradient-to-tr from-slate-200/60 via-gray-100/40 to-transparent blur-[120px] rounded-full pointer-events-none"></div>
    <div class="absolute top-[300px] right-0 w-[500px] h-[500px] bg-slate-300/30 blur-[140px] rounded-full pointer-events-none"></div>

    <!-- Header / Top Navbar (Shared Component) -->
    <Navbar />


    <!-- Main Hero Section -->
    <section class="relative pt-12 pb-20 lg:pt-16 lg:pb-28 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
        
        <!-- Hero Left Content -->
        <div class="lg:col-span-6 space-y-6 text-center lg:text-left z-10">
          
          <!-- Audience Badge -->
          <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-slate-200/90 shadow-sm">
            <span class="text-slate-950 font-bold">✦</span>
            <span class="text-xs font-semibold text-slate-700 tracking-wide">The Choice For 5,600+ Websites Worldwide</span>
          </div>

          <!-- Main Title -->
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight text-slate-950 leading-[1.1]">
            Our Advanced POS <br class="hidden sm:inline" />
            <span class="bg-gradient-to-r from-slate-950 via-slate-800 to-slate-600 bg-clip-text text-transparent">Software Solutions</span>
          </h1>

          <!-- Subtitle -->
          <p class="text-slate-600 text-sm sm:text-base leading-relaxed max-w-xl mx-auto lg:mx-0 font-normal">
            Our state-of-the-art POS software is designed to test & streamline your business operations, database & returns with automated double-entry accounting.
          </p>

          <!-- CTAs Side-by-Side (Solid Black Pill + Translucent Play Video Button) -->
          <div class="pt-2 flex flex-wrap items-center justify-center lg:justify-start gap-4">
            <router-link
              to="/plans"
              class="bg-slate-950 hover:bg-slate-800 text-white font-semibold px-6 py-3 rounded-full shadow-lg shadow-slate-950/20 transition-all flex items-center gap-2.5 text-sm group"
            >
              <span>Get Started Now</span>
              <svg class="w-4 h-4 text-white fill-none stroke-current transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"/>
              </svg>
            </router-link>

            <button
              type="button"
              @click="showDemoVideo = true"
              class="bg-white hover:bg-slate-100 text-slate-950 font-semibold px-6 py-3 rounded-full border border-slate-300 shadow-sm transition-all flex items-center gap-2.5 text-sm group"
            >
              <svg class="w-4 h-4 fill-slate-950 text-slate-950 transition-transform group-hover:scale-110" viewBox="0 0 24 24">
                <polygon points="5 3 19 12 5 21 5 3"></polygon>
              </svg>
              <span>Play Video</span>
            </button>
          </div>

          <!-- Feature Bullets -->
          <div class="pt-6 border-t border-slate-200 grid grid-cols-3 gap-4 max-w-md mx-auto lg:mx-0">
            <div>
              <div class="text-xl font-extrabold text-slate-950">99.9%</div>
              <div class="text-xs text-slate-500 font-medium">Uptime Guarantee</div>
            </div>
            <div>
              <div class="text-xl font-extrabold text-slate-950">10x</div>
              <div class="text-xs text-slate-500 font-medium">Faster Checkout</div>
            </div>
            <div>
              <div class="text-xl font-extrabold text-slate-950">24/7</div>
              <div class="text-xs text-slate-500 font-medium">Live Support</div>
            </div>
          </div>

        </div>

        <!-- Hero Right Visual Mockup -->
        <div class="lg:col-span-6 relative flex justify-center items-center">
          
          <!-- Outer Arch Backing -->
          <div class="absolute inset-0 bg-gradient-to-b from-slate-200/50 to-slate-100/30 rounded-t-full border border-slate-300/60 transform scale-95 pointer-events-none"></div>

          <!-- Main Terminal & Hand Card Swipe Illustration Container -->
          <div class="relative z-10 py-6 px-4">
            
            <!-- Floating Widget 1: Paid Earnings (Top Left) -->
            <div class="absolute -top-4 -left-4 sm:left-0 z-30 bg-white/95 backdrop-blur-xl border border-slate-200 p-4 rounded-2xl shadow-xl shadow-slate-300/40 w-44 sm:w-48 transform -rotate-2 hover:rotate-0 transition-transform">
              <div class="flex items-center justify-between text-[11px] text-slate-500 font-semibold mb-1">
                <span>Paid Earnings</span>
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
              </div>
              <div class="text-xl sm:text-2xl font-black text-slate-950">$682.5</div>
              <div class="text-[10px] text-emerald-600 font-bold flex items-center gap-1 mt-0.5">
                <span>↑ +24.8%</span>
                <span class="text-slate-500 font-normal">from last week</span>
              </div>
              <!-- Sparkline Graphic -->
              <div class="mt-2.5 h-7 w-full flex items-end gap-1">
                <div class="h-3 w-full bg-slate-200 rounded-t"></div>
                <div class="h-5 w-full bg-slate-300 rounded-t"></div>
                <div class="h-4 w-full bg-slate-300 rounded-t"></div>
                <div class="h-7 w-full bg-slate-950 rounded-t shadow-sm"></div>
                <div class="h-5 w-full bg-slate-400 rounded-t"></div>
              </div>
            </div>

            <!-- Floating Widget 2: Quick Transfer (Top Right) -->
            <div class="absolute -top-6 -right-4 sm:right-0 z-30 bg-white/95 backdrop-blur-xl border border-slate-200 p-4 rounded-2xl shadow-xl shadow-slate-300/40 w-48 sm:w-52 transform rotate-3 hover:rotate-0 transition-transform">
              <div class="text-xs font-bold text-slate-900 mb-2">Quick Transfer</div>
              <div class="space-y-2">
                <div class="flex items-center justify-between text-[11px] bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5">
                  <div class="flex items-center gap-2">
                    <div class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-[10px]">👤</div>
                    <span class="text-slate-700 font-semibold">David</span>
                  </div>
                  <span class="text-slate-950 font-bold">$5.25</span>
                </div>
                <router-link to="/plans" class="block w-full text-center bg-slate-950 hover:bg-slate-800 text-white text-[10px] font-extrabold py-2 rounded-xl transition-all shadow-md">
                  GET STARTED NOW
                </router-link>
              </div>
            </div>

            <!-- Central POS Terminal Graphic Mockup (Sleek Obsidian Dark Contrast) -->
            <div class="w-[280px] sm:w-[320px] bg-slate-950 p-5 rounded-3xl border border-slate-800 shadow-[0_20px_50px_rgba(0,0,0,0.25)] relative text-white">
              
              <!-- Screen Display -->
              <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 mb-4 text-center shadow-inner">
                <div class="flex items-center justify-between text-[10px] text-slate-400 mb-2">
                  <span>POS SYSTEM v3.4</span>
                  <span class="bg-emerald-500/20 text-emerald-400 px-1.5 py-0.5 rounded text-[9px] font-bold">ONLINE</span>
                </div>
                <div class="text-xs text-slate-400 font-medium">Total Amount</div>
                <div class="text-2xl font-black text-white tracking-tight my-1">$142.80</div>
                <div class="inline-flex items-center gap-1.5 text-[10px] bg-white/10 border border-white/10 px-2.5 py-1 rounded-full text-slate-200">
                  <span>Tap or Swipe Card</span>
                </div>
              </div>

              <!-- Terminal Keypad Graphic -->
              <div class="grid grid-cols-3 gap-2 px-2 py-1 mb-3">
                <div v-for="n in 9" :key="n" class="bg-white/5 border border-white/10 rounded-lg py-1.5 text-center text-xs font-bold text-slate-300">
                  {{ n }}
                </div>
              </div>

              <!-- Card Swiping Hand Illustration Mockup -->
              <div class="relative mt-2 flex justify-center">
                <!-- Dark Card -->
                <div class="w-36 h-22 bg-gradient-to-tr from-slate-800 to-zinc-900 rounded-xl p-3 shadow-xl border border-white/20 transform -rotate-12 translate-x-4 -translate-y-2 z-20">
                  <div class="w-7 h-5 bg-amber-400/90 rounded mb-2"></div>
                  <div class="text-[9px] font-mono text-white tracking-widest">•••• 8842</div>
                  <div class="text-[8px] text-slate-300 font-bold mt-1 uppercase">VISA PLATINUM</div>
                </div>
              </div>

            </div>

            <!-- Floating Badges (Right side live updates) -->
            <div class="absolute bottom-4 -right-6 z-30 bg-white border border-slate-200 px-3 py-2 rounded-xl shadow-xl flex items-center gap-2.5 transform translate-y-2">
              <div class="w-7 h-7 rounded-full bg-slate-950 text-white flex items-center justify-center font-bold text-xs">
                ✓
              </div>
              <div>
                <div class="text-[11px] font-bold text-slate-950">Successful Payment</div>
                <div class="text-[9px] text-slate-500">Invoice #POS-2026-88</div>
              </div>
            </div>

          </div>

        </div>

      </div>
    </section>

    <!-- Lower Section: Feature Highlights -->
    <section class="bg-white text-slate-900 py-20 px-4 sm:px-6 lg:px-8 relative border-t border-slate-200">
      <div class="max-w-6xl mx-auto space-y-12">
        
        <!-- Section Header -->
        <div class="text-center space-y-3 max-w-2xl mx-auto">
          <div class="inline-block bg-slate-100 border border-slate-300 text-slate-900 text-xs font-black px-4 py-1.5 rounded-full uppercase tracking-wider">
            POS SOFTWARE
          </div>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-950 tracking-tight">
            Business With Our Cutting Edge POS Software
          </h2>
          <p class="text-slate-600 text-sm leading-relaxed">
            All-in-one Point of Sale, Inventory Management, and Double-Entry Accounting platform built for growth.
          </p>
        </div>

        <!-- Grid Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          
          <!-- Fast POS Checkout -->
          <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 hover:shadow-xl transition-all duration-300 group">
            <svg class="w-8 h-8 text-slate-950 mb-4 group-hover:scale-110 transition-transform stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121 0 2.05-.826 2.19-1.942l1.1-8.8A1.125 1.125 0 0019.126 2.25H5.106M7.5 14.25L5.106 5.27M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
            </svg>
            <h3 class="text-base font-bold text-slate-950 mb-2">Fast POS Checkout</h3>
            <p class="text-slate-600 text-xs leading-relaxed">
              Seamless barcode scanning, variation pricing, thermal receipts, and instant cash/bank payment handling.
            </p>
          </div>

          <!-- Double-Entry Ledger -->
          <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 hover:shadow-xl transition-all duration-300 group">
            <svg class="w-8 h-8 text-slate-950 mb-4 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/>
              <path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/>
              <path d="M7 21h10"/>
              <path d="M12 3v18"/>
              <path d="M3 7h18"/>
            </svg>
            <h3 class="text-base font-bold text-slate-950 mb-2">Double-Entry Ledger</h3>
            <p class="text-slate-600 text-xs leading-relaxed">
              Automated journal posting for sales, purchases, bank accounts, expenses, and real-time trial balance.
            </p>
          </div>

          <!-- Inventory Control -->
          <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 hover:shadow-xl transition-all duration-300 group">
            <svg class="w-8 h-8 text-slate-950 mb-4 group-hover:scale-110 transition-transform stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
            </svg>
            <h3 class="text-base font-bold text-slate-950 mb-2">Inventory Control</h3>
            <p class="text-slate-600 text-xs leading-relaxed">
              Multi-warehouse inventory stock tracking, transfers, purchase orders, and variation combinations.
            </p>
          </div>

          <!-- Financial Reports -->
          <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 hover:shadow-xl transition-all duration-300 group">
            <svg class="w-8 h-8 text-slate-950 mb-4 group-hover:scale-110 transition-transform stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
            <h3 class="text-base font-bold text-slate-950 mb-2">Financial Reports</h3>
            <p class="text-slate-600 text-xs leading-relaxed">
              Instant Profit & Loss, Balance Sheet, Cash Flow, tax tag summaries, and exportable PDF/Excel reports.
            </p>
          </div>

        </div>

        <!-- Banner CTA -->
        <div class="bg-slate-950 rounded-3xl p-8 sm:p-12 text-white flex flex-col sm:flex-row items-center justify-between gap-6 shadow-2xl">
          <div class="space-y-2 text-center sm:text-left">
            <h3 class="text-2xl sm:text-3xl font-extrabold">Ready to transform your POS business?</h3>
            <p class="text-slate-400 text-sm">Join over 5,600+ businesses world-wide using POS System.</p>
          </div>
          <router-link
            to="/plans"
            class="bg-white text-slate-950 font-extrabold px-8 py-3.5 rounded-full shadow-lg hover:bg-slate-100 transition-all whitespace-nowrap text-sm"
          >
            Get Started Now
          </router-link>
        </div>

      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-950 text-slate-400 py-10 px-4 border-t border-slate-800 text-xs text-center">
      <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2">
          <div class="w-6 h-6 rounded-full bg-white text-slate-950 flex items-center justify-center font-black text-[10px]">P</div>
          <span class="text-white font-bold text-sm">POS & Accounting</span>
        </div>
        <p>© 2026 POS System. All rights reserved.</p>
      </div>
    </footer>

    <!-- Video Modal -->
    <div
      v-if="showDemoVideo"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
      @click.self="showDemoVideo = false"
    >
      <div class="bg-white border border-slate-200 rounded-2xl p-6 max-w-2xl w-full shadow-2xl text-slate-950 relative">
        <button
          type="button"
          @click="showDemoVideo = false"
          class="absolute top-4 right-4 text-slate-400 hover:text-slate-950 font-bold text-xl"
        >
          ✕
        </button>
        <h3 class="text-lg font-bold mb-4">POS & Accounting Walkthrough</h3>
        <div class="aspect-video bg-slate-950 rounded-xl border border-slate-800 flex items-center justify-center text-slate-400">
          <div class="text-center p-6 space-y-2">
            <div class="text-4xl">🎬</div>
            <div class="font-semibold text-white">Product Overview Video</div>
            <div class="text-xs text-slate-400">Demonstrating Real-time Sales, Stock Control & Journal Postings</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import Navbar from '@/components/shared/Navbar.vue';

const authStore = useAuthStore();
const showDemoVideo = ref(false);
</script>
