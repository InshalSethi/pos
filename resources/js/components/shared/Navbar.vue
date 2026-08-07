<template>
  <!-- Header / Top Navbar (Shared across Landing, Login, Register, Plans) -->
  <header class="sticky top-0 z-50 pt-4 px-4 sm:px-6 lg:px-8 transition-all">
    <div class="max-w-6xl mx-auto bg-white/80 backdrop-blur-xl border border-slate-200/80 shadow-xl shadow-slate-200/40 rounded-full px-6 py-3 flex items-center justify-between">
      
      <!-- Logo -->
      <router-link to="/" class="flex items-center gap-3 group">
        <div class="w-9 h-9 rounded-full bg-slate-950 flex items-center justify-between p-2 shadow-md group-hover:scale-105 transition-transform">
          <div class="w-2.5 h-2.5 rounded-full bg-white"></div>
          <div class="w-1.5 h-full rounded-full bg-white/40"></div>
        </div>
        <span class="text-xl font-black tracking-tight text-slate-950 group-hover:text-slate-700 transition-colors">POS</span>
      </router-link>

      <!-- Nav Links -->
      <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
        <router-link to="/" class="hover:text-slate-950 transition-colors">Home</router-link>
        <div class="relative group cursor-pointer flex items-center gap-1.5 hover:text-slate-950 transition-colors">
          <span>Features</span>
          <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-950 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </div>
        <router-link to="/plans" class="hover:text-slate-950 transition-colors">Pricing</router-link>
        <a href="/#hosted" class="hover:text-slate-950 transition-colors">Hosted</a>
        <a href="/#resources" class="hover:text-slate-950 transition-colors">Resources</a>
      </nav>

      <!-- Top Right Navbar Auth Links -->
      <div class="flex items-center gap-3">
        <template v-if="authStore.isAuthenticated">
          <router-link
            to="/dashboard"
            class="bg-slate-950 hover:bg-slate-800 text-white text-xs sm:text-sm font-bold px-5 py-2.5 rounded-full shadow-md transition-all flex items-center gap-2"
          >
            <span>Go to Dashboard</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
          </router-link>
        </template>

        <template v-else>
          <router-link
            to="/login"
            class="text-xs sm:text-sm font-semibold text-slate-700 hover:text-slate-950 px-4 py-2 rounded-full hover:bg-slate-100 transition-all"
          >
            Log In
          </router-link>
          <router-link
            to="/register"
            class="bg-slate-950 hover:bg-slate-800 text-white text-xs sm:text-sm font-bold px-5 py-2.5 rounded-full shadow-md transition-all"
          >
            Sign Up
          </router-link>
        </template>
      </div>

    </div>
  </header>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
</script>
