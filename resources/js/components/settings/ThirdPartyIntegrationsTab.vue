<template>
  <div class="space-y-8 font-sans">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 p-6 rounded-2xl text-white shadow-md relative overflow-hidden">
      <div class="absolute right-0 top-0 translate-x-4 -translate-y-4 opacity-10 pointer-events-none">
        <svg class="w-72 h-72 text-indigo-400" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
        </svg>
      </div>

      <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center space-x-3 mb-1">
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-widest bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
              Tax & Fiscal Services
            </span>
            <span class="text-xs text-slate-300">Global & Regional Tax Integrations</span>
          </div>
          <h2 class="text-xl font-bold tracking-tight">Third Party Integrations</h2>
          <p class="text-xs text-slate-300 max-w-2xl mt-1">
            Connect your company to local and international tax authorities, digital invoicing portals, and fiscal POS compliance systems.
          </p>
        </div>

        <!-- Target Company Selector -->
        <div class="bg-white/10 backdrop-blur-md border border-white/20 p-3 rounded-xl min-w-[220px]">
          <label class="block text-[11px] font-semibold text-slate-200 mb-1">Target Company</label>
          <select
            v-model="selectedCompanyId"
            @change="loadCompanySettings"
            class="w-full bg-slate-900/80 border border-slate-700 text-white text-xs font-bold rounded-lg px-2.5 py-1.5 focus:outline-none"
          >
            <option v-for="c in companies" :key="c.id" :value="c.id">
              {{ c.name }} (ID: {{ c.id }})
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Filter Category Tabs -->
    <div class="flex items-center space-x-2 border-b border-slate-200 dark:border-zinc-800 pb-2">
      <button
        v-for="cat in categories"
        :key="cat.id"
        @click="activeCategory = cat.id"
        :class="[
          'px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer',
          activeCategory === cat.id
            ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-xs'
            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-zinc-800'
        ]"
      >
        {{ cat.label }}
      </button>
    </div>

    <!-- Integrations Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

      <!-- PAKISTAN INTEGRATIONS CARDS (FBR, PRA, SRB, KPRA, BRA) -->
      <template v-if="activeCategory === 'all' || activeCategory === 'pakistan'">
        <div
          v-for="(meta, key) in pakistanAuthorities"
          :key="key"
          class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all flex flex-col justify-between relative group"
        >
          <div class="space-y-4">
            <div class="flex items-start justify-between">
              <div class="flex items-center space-x-3">
                <div :class="['w-12 h-12 rounded-xl flex items-center justify-center font-black text-sm border', meta.iconBg]">
                  {{ meta.badgeText || '🇵🇰' }}
                </div>
                <div>
                  <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                    {{ meta.name }}
                  </h3>
                  <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">{{ meta.fullName }}</span>
                </div>
              </div>

              <!-- Status Badge -->
              <span v-if="getAuthSetting(key).is_enabled" class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse mr-1.5"></span>
                Enabled
              </span>
              <span v-else class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400 border border-slate-200 dark:border-zinc-700">
                Disabled
              </span>
            </div>

            <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2">
              {{ meta.description }}
            </p>

            <div v-if="getAuthSetting(key).is_enabled" class="pt-2 flex flex-wrap gap-2 text-[10px] font-semibold text-slate-500 dark:text-slate-400">
              <span class="bg-slate-100 dark:bg-zinc-800 px-2 py-0.5 rounded-md">ID: {{ getAuthSetting(key).pos_id || 'N/A' }}</span>
              <span class="bg-slate-100 dark:bg-zinc-800 px-2 py-0.5 rounded-md capitalize">Mode: {{ getAuthSetting(key).environment }}</span>
            </div>
          </div>

          <div class="mt-6 pt-4 border-t border-slate-100 dark:border-zinc-800/80 flex items-center justify-between">
            <div class="flex items-center space-x-2">
              <button
                @click="toggleAuthStatus(key)"
                :class="[
                  'relative inline-flex h-5 w-10 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none',
                  getAuthSetting(key).is_enabled ? 'bg-emerald-600' : 'bg-slate-300 dark:bg-zinc-700'
                ]"
              >
                <span
                  :class="[
                    'pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out',
                    getAuthSetting(key).is_enabled ? 'translate-x-5' : 'translate-x-0'
                  ]"
                ></span>
              </button>
              <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">
                {{ getAuthSetting(key).is_enabled ? 'Active' : 'Off' }}
              </span>
            </div>

            <button
              @click="openModal(key)"
              class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 text-xs font-bold rounded-xl transition-all shadow-xs cursor-pointer flex items-center space-x-1"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <span>Configure</span>
            </button>
          </div>
        </div>
      </template>

      <!-- INTERNATIONAL ROADMAP CARDS -->
      <template v-if="activeCategory === 'all' || activeCategory === 'international'">
        <!-- ZATCA SAUDI ARABIA CARD -->
        <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-5 shadow-xs flex flex-col justify-between opacity-80">
          <div class="space-y-4">
            <div class="flex items-start justify-between">
              <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 flex items-center justify-center font-black text-sm border border-emerald-300/40">
                  🇸🇦
                </div>
                <div>
                  <h3 class="text-sm font-bold text-slate-900 dark:text-white">ZATCA (KSA)</h3>
                  <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Saudi Arabia Fatoora Phase 2</span>
                </div>
              </div>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                Coming Soon
              </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              ZATCA E-Invoicing Phase 1 & Phase 2 integration with XML generation, UBL 2.1 compliance, and Cryptographic Stamp (ECDSA).
            </p>
          </div>
          <div class="mt-6 pt-4 border-t border-slate-100 dark:border-zinc-800 flex justify-end">
            <button disabled class="px-3.5 py-1.5 bg-slate-100 text-slate-400 dark:bg-zinc-800 dark:text-zinc-500 text-xs font-bold rounded-xl cursor-not-allowed">
              Configure
            </button>
          </div>
        </div>

        <!-- FTA UAE CARD -->
        <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-5 shadow-xs flex flex-col justify-between opacity-80">
          <div class="space-y-4">
            <div class="flex items-start justify-between">
              <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-950/60 text-red-700 dark:text-red-400 flex items-center justify-center font-black text-sm border border-red-300/40">
                  🇦🇪
                </div>
                <div>
                  <h3 class="text-sm font-bold text-slate-900 dark:text-white">FTA (UAE)</h3>
                  <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">UAE Federal Tax Authority</span>
                </div>
              </div>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                Coming Soon
              </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              UAE 5% VAT Tax invoice formatting, TRN validation, and automated e-invoicing compliance.
            </p>
          </div>
          <div class="mt-6 pt-4 border-t border-slate-100 dark:border-zinc-800 flex justify-end">
            <button disabled class="px-3.5 py-1.5 bg-slate-100 text-slate-400 dark:bg-zinc-800 dark:text-zinc-500 text-xs font-bold rounded-xl cursor-not-allowed">
              Configure
            </button>
          </div>
        </div>

        <!-- HMRC UK CARD -->
        <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-5 shadow-xs flex flex-col justify-between opacity-80">
          <div class="space-y-4">
            <div class="flex items-start justify-between">
              <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 flex items-center justify-center font-black text-sm border border-blue-300/40">
                  🇬🇧
                </div>
                <div>
                  <h3 class="text-sm font-bold text-slate-900 dark:text-white">HMRC MTD (UK)</h3>
                  <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Making Tax Digital</span>
                </div>
              </div>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                Coming Soon
              </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              UK Making Tax Digital for VAT compliance, digital links, and direct API filing with HMRC.
            </p>
          </div>
          <div class="mt-6 pt-4 border-t border-slate-100 dark:border-zinc-800 flex justify-end">
            <button disabled class="px-3.5 py-1.5 bg-slate-100 text-slate-400 dark:bg-zinc-800 dark:text-zinc-500 text-xs font-bold rounded-xl cursor-not-allowed">
              Configure
            </button>
          </div>
        </div>

        <!-- IRS USA CARD -->
        <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-5 shadow-xs flex flex-col justify-between opacity-80">
          <div class="space-y-4">
            <div class="flex items-start justify-between">
              <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 flex items-center justify-center font-black text-sm border border-indigo-300/40">
                  🇺🇸
                </div>
                <div>
                  <h3 class="text-sm font-bold text-slate-900 dark:text-white">IRS & State Tax (USA)</h3>
                  <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">US Sales Tax Integration</span>
                </div>
              </div>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                Coming Soon
              </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Real-time state and local sales tax calculations, nexus tracking, and 1099 compliance.
            </p>
          </div>
          <div class="mt-6 pt-4 border-t border-slate-100 dark:border-zinc-800 flex justify-end">
            <button disabled class="px-3.5 py-1.5 bg-slate-100 text-slate-400 dark:bg-zinc-800 dark:text-zinc-500 text-xs font-bold rounded-xl cursor-not-allowed">
              Configure
            </button>
          </div>
        </div>

        <!-- EU VAT CARD -->
        <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-5 shadow-xs flex flex-col justify-between opacity-80">
          <div class="space-y-4">
            <div class="flex items-start justify-between">
              <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 flex items-center justify-center font-black text-sm border border-blue-300/40">
                  🇪🇺
                </div>
                <div>
                  <h3 class="text-sm font-bold text-slate-900 dark:text-white">EU VAT & ViDA</h3>
                  <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">European Union Fiscalization</span>
                </div>
              </div>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                Coming Soon
              </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              EU One Stop Shop (OSS) VAT, VIES VAT ID validation, and VAT in the Digital Age (ViDA) standard.
            </p>
          </div>
          <div class="mt-6 pt-4 border-t border-slate-100 dark:border-zinc-800 flex justify-end">
            <button disabled class="px-3.5 py-1.5 bg-slate-100 text-slate-400 dark:bg-zinc-800 dark:text-zinc-500 text-xs font-bold rounded-xl cursor-not-allowed">
              Configure
            </button>
          </div>
        </div>
      </template>

    </div>

    <!-- ==================== DYNAMIC CONFIGURATION & AUDIT POPUP MODAL ==================== -->
    <Teleport to="body" v-if="showModal">
      <div class="fixed inset-0 z-[99999] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-3xl max-w-5xl w-full my-6 p-6 space-y-6 shadow-2xl relative max-h-[92vh] overflow-y-auto">
          
          <!-- Modal Header -->
          <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-800 pb-4">
            <div class="flex items-center space-x-3">
              <div :class="['w-10 h-10 rounded-xl flex items-center justify-center font-black text-base border', activeAuthorityMeta.iconBg]">
                {{ activeAuthorityMeta.badgeText || '🇵🇰' }}
              </div>
              <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                  {{ activeAuthorityMeta.name }} Integration Configuration
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                  Configure credentials, transaction scopes, and audit fiscalized logs for {{ activeAuthorityMeta.fullName }} (Company ID: {{ selectedCompanyId }})
                </p>
              </div>
            </div>

            <button
              @click="showModal = false"
              class="w-8 h-8 rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-400 hover:text-slate-700 dark:hover:text-white flex items-center justify-center font-bold text-lg cursor-pointer"
            >
              &times;
            </button>
          </div>

          <!-- Modal Sub-Navigation Tabs -->
          <div class="flex items-center space-x-2 border-b border-slate-100 dark:border-zinc-800 pb-2">
            <button
              @click="modalTab = 'settings'"
              :class="[
                'px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer',
                modalTab === 'settings'
                  ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-xs'
                  : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-zinc-800'
              ]"
            >
              Configuration & Credentials
            </button>

            <button
              @click="modalTab = 'logs'"
              :class="[
                'px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center space-x-1.5',
                modalTab === 'logs'
                  ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-xs'
                  : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-zinc-800'
              ]"
            >
              <span>Fiscal Audit Logs</span>
              <span class="px-1.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-600 dark:text-emerald-400">
                {{ entrySummary.total || 0 }}
              </span>
            </button>
          </div>

          <!-- TAB 1: CONFIGURATION & CREDENTIALS -->
          <div v-if="modalTab === 'settings'" class="space-y-6">

            <!-- Master Enable Card inside Modal -->
            <div class="bg-slate-50 dark:bg-zinc-800/50 border border-slate-200/80 dark:border-zinc-700/60 rounded-2xl p-5 flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <div :class="[
                  'w-11 h-11 rounded-xl flex items-center justify-center transition-colors',
                  activeSetting.is_enabled ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-slate-200 text-slate-500 dark:bg-zinc-700 dark:text-zinc-400'
                ]">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                  </svg>
                </div>
                <div>
                  <h4 class="text-sm font-bold text-slate-900 dark:text-white">Enable {{ activeAuthorityMeta.name }} Fiscalization</h4>
                  <p class="text-xs text-slate-500 dark:text-slate-400">
                    When enabled, transaction records for this company will transmit to {{ activeAuthorityMeta.fullName }}.
                  </p>
                </div>
              </div>

              <button
                @click="activeSetting.is_enabled = !activeSetting.is_enabled"
                :class="[
                  'relative inline-flex h-7 w-14 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none',
                  activeSetting.is_enabled ? 'bg-emerald-600' : 'bg-slate-300 dark:bg-zinc-700'
                ]"
              >
                <span
                  :class="[
                    'pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out',
                    activeSetting.is_enabled ? 'translate-x-7' : 'translate-x-0'
                  ]"
                ></span>
              </button>
            </div>

            <!-- Scope Toggles -->
            <div v-if="activeSetting.is_enabled" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <div class="p-3 bg-white dark:bg-zinc-800 rounded-xl border border-slate-200 dark:border-zinc-700 flex items-center justify-between">
                <div>
                  <span class="text-xs font-bold text-slate-900 dark:text-slate-100 block">Sales Invoices</span>
                  <span class="text-[10px] text-slate-500">Record Sales & Refunds</span>
                </div>
                <input type="checkbox" v-model="activeSetting.sync_sales" class="w-4 h-4 text-emerald-600 rounded cursor-pointer" />
              </div>

              <div class="p-3 bg-white dark:bg-zinc-800 rounded-xl border border-slate-200 dark:border-zinc-700 flex items-center justify-between">
                <div>
                  <span class="text-xs font-bold text-slate-900 dark:text-slate-100 block">Purchases</span>
                  <span class="text-[10px] text-slate-500">Record Purchase Orders</span>
                </div>
                <input type="checkbox" v-model="activeSetting.sync_purchases" class="w-4 h-4 text-emerald-600 rounded cursor-pointer" />
              </div>

              <div class="p-3 bg-white dark:bg-zinc-800 rounded-xl border border-slate-200 dark:border-zinc-700 flex items-center justify-between">
                <div>
                  <span class="text-xs font-bold text-slate-900 dark:text-slate-100 block">Transactions</span>
                  <span class="text-[10px] text-slate-500">General & Bank Entries</span>
                </div>
                <input type="checkbox" v-model="activeSetting.sync_transactions" class="w-4 h-4 text-emerald-600 rounded cursor-pointer" />
              </div>

              <div class="p-3 bg-white dark:bg-zinc-800 rounded-xl border border-slate-200 dark:border-zinc-700 flex items-center justify-between">
                <div>
                  <span class="text-xs font-bold text-slate-900 dark:text-slate-100 block">Payments</span>
                  <span class="text-[10px] text-slate-500">Payment Receipts</span>
                </div>
                <input type="checkbox" v-model="activeSetting.sync_payments" class="w-4 h-4 text-emerald-600 rounded cursor-pointer" />
              </div>
            </div>

            <!-- Credentials Fields -->
            <div class="space-y-4">
              <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-800 pb-2">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">API Credentials & Merchant Info</h4>
                <button
                  @click="testConnection"
                  :disabled="testingConnection"
                  class="px-3 py-1.5 bg-slate-100 dark:bg-zinc-800 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-lg hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors flex items-center space-x-1.5 cursor-pointer disabled:opacity-50"
                >
                  <svg v-if="testingConnection" class="animate-spin h-3.5 w-3.5 text-current" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  <span>{{ testingConnection ? 'Testing...' : 'Test API Connection' }}</span>
                </button>
              </div>

              <!-- Test Connection Result Alert -->
              <div v-if="testResult" :class="[
                'p-3.5 rounded-xl border text-xs font-semibold flex items-center justify-between',
                testResult.success ? 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300'
              ]">
                <span>{{ testResult.message }}</span>
                <button @click="testResult = null" class="text-current font-bold">&times;</button>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Environment Mode</label>
                  <select
                    v-model="activeSetting.environment"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-900 dark:text-slate-100 rounded-xl text-xs font-semibold focus:outline-none"
                  >
                    <option value="sandbox">Sandbox (Testing Environment)</option>
                    <option value="production">Production (Live Portal)</option>
                  </select>
                </div>

                <div>
                  <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">{{ activeAuthorityMeta.posLabel }}</label>
                  <input
                    type="text"
                    v-model="activeSetting.pos_id"
                    :placeholder="activeAuthorityMeta.posPlaceholder"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-900 dark:text-slate-100 rounded-xl text-xs font-semibold focus:outline-none"
                  />
                </div>

                <div>
                  <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Business NTN</label>
                  <input
                    type="text"
                    v-model="activeSetting.ntn"
                    placeholder="e.g. 1234567-8"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-900 dark:text-slate-100 rounded-xl text-xs font-semibold focus:outline-none"
                  />
                </div>

                <div>
                  <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">STRN (Sales Tax Reg. No.)</label>
                  <input
                    type="text"
                    v-model="activeSetting.strn"
                    placeholder="e.g. 3277876543210"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-900 dark:text-slate-100 rounded-xl text-xs font-semibold focus:outline-none"
                  />
                </div>

                <div>
                  <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Registered Business Name</label>
                  <input
                    type="text"
                    v-model="activeSetting.business_name"
                    placeholder="Company Legal Name"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-900 dark:text-slate-100 rounded-xl text-xs font-semibold focus:outline-none"
                  />
                </div>

                <div>
                  <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Branch / Outlet Name</label>
                  <input
                    type="text"
                    v-model="activeSetting.branch_name"
                    placeholder="Main Branch"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-900 dark:text-slate-100 rounded-xl text-xs font-semibold focus:outline-none"
                  />
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">API Bearer Token / Key</label>
                  <div class="relative">
                    <input
                      :type="showToken ? 'text' : 'password'"
                      v-model="activeSetting.api_token"
                      placeholder="Bearer Token / Secret Key"
                      class="w-full px-3 py-2 pr-10 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-900 dark:text-slate-100 rounded-xl text-xs font-semibold focus:outline-none"
                    />
                    <button
                      type="button"
                      @click="showToken = !showToken"
                      class="absolute right-3 top-2 text-slate-400 hover:text-slate-600 text-xs font-bold cursor-pointer"
                    >
                      {{ showToken ? 'Hide' : 'Show' }}
                    </button>
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Base API Endpoint URL</label>
                  <input
                    type="text"
                    v-model="activeSetting.base_url"
                    :placeholder="activeAuthorityMeta.defaultUrl"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-900 dark:text-slate-100 rounded-xl text-xs font-semibold focus:outline-none"
                  />
                </div>
              </div>
            </div>

            <!-- Save Footer -->
            <div class="pt-4 border-t border-slate-200 dark:border-zinc-800 flex items-center justify-between">
              <div class="flex items-center space-x-2">
                <input type="checkbox" id="auto_sync" v-model="activeSetting.auto_sync" class="w-4 h-4 text-emerald-600 rounded cursor-pointer" />
                <label for="auto_sync" class="text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer">
                  Automatically post entries to {{ activeAuthorityMeta.name }} on transaction creation
                </label>
              </div>

              <button
                @click="saveSettings"
                :disabled="saving"
                class="bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-bold px-6 py-2.5 rounded-xl text-xs flex items-center space-x-2 transition-all cursor-pointer shadow-xs disabled:opacity-50"
              >
                <svg v-if="saving" class="animate-spin h-4 w-4 text-current" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span>{{ saving ? 'Saving Settings...' : 'Save ' + activeAuthorityMeta.name + ' Settings' }}</span>
              </button>
            </div>
          </div>

          <!-- TAB 2: FISCAL AUDIT LOGS -->
          <div v-if="modalTab === 'logs'" class="space-y-4">
            
            <!-- Summary Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
              <div class="p-3 bg-slate-50 dark:bg-zinc-800/40 rounded-xl border border-slate-200 dark:border-zinc-800">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Total Recorded</span>
                <span class="text-lg font-bold text-slate-900 dark:text-white">{{ entrySummary.total || 0 }}</span>
              </div>
              <div class="p-3 bg-emerald-50/60 dark:bg-emerald-950/20 rounded-xl border border-emerald-200/60 dark:border-emerald-800/40">
                <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider block">Synced</span>
                <span class="text-lg font-bold text-emerald-700 dark:text-emerald-300">{{ entrySummary.synced || 0 }}</span>
              </div>
              <div class="p-3 bg-amber-50/60 dark:bg-amber-950/20 rounded-xl border border-amber-200/60 dark:border-amber-800/40">
                <span class="text-[10px] font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider block">Pending Queue</span>
                <span class="text-lg font-bold text-amber-700 dark:text-amber-300">{{ entrySummary.pending || 0 }}</span>
              </div>
              <div class="p-3 bg-rose-50/60 dark:bg-rose-950/20 rounded-xl border border-rose-200/60 dark:border-rose-800/40">
                <span class="text-[10px] font-bold text-rose-700 dark:text-rose-400 uppercase tracking-wider block">Failed</span>
                <span class="text-lg font-bold text-rose-700 dark:text-rose-300">{{ entrySummary.failed || 0 }}</span>
              </div>
            </div>

            <!-- Log Filters -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 flex-1">
                <input
                  type="text"
                  v-model="filters.search"
                  @input="loadEntries(1)"
                  placeholder="Search Ref #, Buyer, IRN..."
                  class="px-3 py-1.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-semibold focus:outline-none"
                />

                <select
                  v-model="filters.type"
                  @change="loadEntries(1)"
                  class="px-3 py-1.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-semibold focus:outline-none"
                >
                  <option value="">All Data Types</option>
                  <option value="sale">Sale Invoices</option>
                  <option value="purchase">Purchase Orders</option>
                  <option value="transaction">Transactions</option>
                  <option value="payment">Payment Receipts</option>
                </select>

                <select
                  v-model="filters.status"
                  @change="loadEntries(1)"
                  class="px-3 py-1.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-semibold focus:outline-none"
                >
                  <option value="">All Statuses</option>
                  <option value="synced">Synced</option>
                  <option value="pending">Pending</option>
                  <option value="failed">Failed</option>
                </select>
              </div>

              <button
                @click="syncAllPending"
                :disabled="syncingAll"
                class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-colors flex items-center space-x-1 shadow-xs cursor-pointer disabled:opacity-50 whitespace-nowrap"
              >
                <svg v-if="syncingAll" class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span>{{ syncingAll ? 'Syncing...' : 'Sync All Pending' }}</span>
              </button>
            </div>

            <!-- Entries Table -->
            <div class="overflow-x-auto border border-slate-200 dark:border-zinc-800 rounded-xl">
              <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 font-bold uppercase tracking-wider">
                  <tr>
                    <th class="px-3 py-2.5">Ref #</th>
                    <th class="px-3 py-2.5">Type</th>
                    <th class="px-3 py-2.5">Party / Customer</th>
                    <th class="px-3 py-2.5">Amount</th>
                    <th class="px-3 py-2.5">Invoice IRN</th>
                    <th class="px-3 py-2.5">Status</th>
                    <th class="px-3 py-2.5 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                  <tr v-for="e in entries.data" :key="e.id" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/40 font-medium">
                    <td class="px-3 py-2.5 font-bold text-slate-900 dark:text-white">
                      {{ e.reference_number }}
                    </td>
                    <td class="px-3 py-2.5 capitalize">
                      <span :class="[
                        'px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase',
                        e.type === 'sale' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' :
                        e.type === 'purchase' ? 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300' :
                        e.type === 'transaction' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' :
                        'bg-teal-100 text-teal-800 dark:bg-teal-950 dark:text-teal-300'
                      ]">
                        {{ e.type }}
                      </span>
                    </td>
                    <td class="px-3 py-2.5 text-slate-700 dark:text-slate-300">
                      {{ e.buyer_name || 'N/A' }}
                      <span v-if="e.buyer_ntn" class="block text-[10px] text-slate-400">NTN: {{ e.buyer_ntn }}</span>
                    </td>
                    <td class="px-3 py-2.5 font-bold text-slate-900 dark:text-white">
                      PKR {{ formatMoney(e.total_amount) }}
                    </td>
                    <td class="px-3 py-2.5 font-mono text-[11px] text-slate-600 dark:text-slate-300">
                      {{ e.fbr_invoice_number || '-' }}
                    </td>
                    <td class="px-3 py-2.5">
                      <span :class="[
                        'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase',
                        e.status === 'synced' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' :
                        e.status === 'pending' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' :
                        'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300'
                      ]">
                        {{ e.status }}
                      </span>
                    </td>
                    <td class="px-3 py-2.5 text-right space-x-1">
                      <button
                        @click="selectedEntry = e"
                        class="px-2 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 text-slate-700 dark:text-slate-200 font-semibold text-[11px] rounded-lg cursor-pointer"
                      >
                        Details
                      </button>
                      <button
                        @click="syncSingleEntry(e)"
                        :disabled="e.syncing"
                        class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 font-semibold text-[11px] rounded-lg cursor-pointer disabled:opacity-50"
                      >
                        {{ e.syncing ? '...' : (e.status === 'synced' ? 'Re-Sync' : 'Sync') }}
                      </button>
                    </td>
                  </tr>
                  <tr v-if="!entries.data || entries.data.length === 0">
                    <td colspan="7" class="text-center py-6 text-slate-400 text-xs font-medium">
                      No recorded entries found for {{ activeAuthorityMeta.name }}.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

          </div>

        </div>
      </div>
    </Teleport>

    <!-- ENTRY PAYLOAD DETAILS SUB-MODAL -->
    <Teleport to="body" v-if="selectedEntry">
      <div class="fixed inset-0 z-[100000] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl max-w-xl w-full p-5 space-y-4 shadow-xl max-h-[85vh] overflow-y-auto">
          <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-800 pb-2">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">
              Entry Details: {{ selectedEntry.reference_number }}
            </h3>
            <button @click="selectedEntry = null" class="text-slate-400 hover:text-slate-600 font-bold cursor-pointer">&times;</button>
          </div>

          <div class="grid grid-cols-2 gap-3 text-xs">
            <div>
              <span class="text-slate-400 block font-medium">Invoice IRN</span>
              <span class="font-bold text-slate-900 dark:text-white font-mono">{{ selectedEntry.fbr_invoice_number || 'N/A' }}</span>
            </div>
            <div>
              <span class="text-slate-400 block font-medium">Synced At</span>
              <span class="font-bold text-slate-900 dark:text-white">{{ selectedEntry.synced_at || 'Pending' }}</span>
            </div>
          </div>

          <div v-if="selectedEntry.error_message" class="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs">
            <strong>Error:</strong> {{ selectedEntry.error_message }}
          </div>

          <div>
            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 block mb-1">Payload Sent</span>
            <pre class="bg-slate-900 text-emerald-400 p-3 rounded-xl text-[10px] overflow-x-auto font-mono max-h-40">{{ JSON.stringify(selectedEntry.payload, null, 2) }}</pre>
          </div>

          <div>
            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 block mb-1">Response Received</span>
            <pre class="bg-slate-900 text-slate-200 p-3 rounded-xl text-[10px] overflow-x-auto font-mono max-h-40">{{ JSON.stringify(selectedEntry.response_payload, null, 2) }}</pre>
          </div>

          <div class="flex justify-end pt-2">
            <button @click="selectedEntry = null" class="px-4 py-1.5 bg-slate-900 text-white dark:bg-white dark:text-slate-900 text-xs font-bold rounded-xl cursor-pointer">
              Close
            </button>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from '@/composables/useToast';

const { showToast } = useToast();

const selectedCompanyId = ref(null);
const companies = ref([]);
const activeCategory = ref('all');

const categories = [
  { id: 'all', label: 'All Integrations' },
  { id: 'pakistan', label: 'Pakistan Fiscal' },
  { id: 'international', label: 'International & Regional' },
];

const pakistanAuthorities = {
  fbr: {
    name: 'FBR Pakistan',
    fullName: 'Federal Board of Revenue',
    badgeText: '🇵🇰',
    iconBg: 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-300/40',
    description: 'Federal POS fiscalization & digital invoicing for Sales, Purchases, Transactions, and Payments with FBR IRIS verification.',
    posLabel: 'FBR POS Registration ID (POSID)',
    posPlaceholder: 'e.g. 100001',
    defaultUrl: 'https://sandbox.fbr.gov.pk/api/v1',
  },
  pra: {
    name: 'PRA Punjab',
    fullName: 'Punjab Revenue Authority',
    badgeText: 'PRA',
    iconBg: 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border-amber-300/40',
    description: 'Services Sales Tax (PST) digital invoicing integration for restaurants, hotels, and service sector compliance in Punjab.',
    posLabel: 'PNTN / PRA Registration ID',
    posPlaceholder: 'e.g. PRA-100001',
    defaultUrl: 'https://e.pra.punjab.gov.pk/api/v1',
  },
  srb: {
    name: 'SRB Sindh',
    fullName: 'Sindh Revenue Board',
    badgeText: 'SRB',
    iconBg: 'bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 border-blue-300/40',
    description: 'Sindh Sales Tax on Services (SST) real-time fiscal record transmission and automated verification portal.',
    posLabel: 'SST / SRB Registration ID',
    posPlaceholder: 'e.g. SRB-100001',
    defaultUrl: 'https://e.srb.gos.pk/api/v1',
  },
  kpra: {
    name: 'KPRA KP',
    fullName: 'Khyber Pakhtunkhwa Revenue Authority',
    badgeText: 'KPRA',
    iconBg: 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-300/40',
    description: 'KP digital tax portal integration for automated transaction logging and service tax invoices.',
    posLabel: 'KPRA Service Reg. ID',
    posPlaceholder: 'e.g. KPRA-100001',
    defaultUrl: 'https://kpra.kp.gov.pk/api/v1',
  },
  bra: {
    name: 'BRA Balochistan',
    fullName: 'Balochistan Revenue Authority',
    badgeText: 'BRA',
    iconBg: 'bg-teal-100 dark:bg-teal-950/60 text-teal-700 dark:text-teal-400 border-teal-300/40',
    description: 'Balochistan sales tax compliance and automated fiscal record reporting integration.',
    posLabel: 'BRA Vendor Reg. ID',
    posPlaceholder: 'e.g. BRA-100001',
    defaultUrl: 'https://bra.gob.pk/api/v1',
  },
};

const createDefaultSetting = (authKey) => ({
  authority_type: authKey,
  is_enabled: false,
  environment: 'sandbox',
  pos_id: '100001',
  ntn: '1234567-8',
  strn: '3277876543210',
  business_name: '',
  branch_name: 'Main Branch',
  api_token: '',
  base_url: pakistanAuthorities[authKey]?.defaultUrl || '',
  auto_sync: true,
  sync_sales: true,
  sync_purchases: true,
  sync_transactions: true,
  sync_payments: true,
});

const authoritySettings = ref({
  fbr: createDefaultSetting('fbr'),
  pra: createDefaultSetting('pra'),
  srb: createDefaultSetting('srb'),
  kpra: createDefaultSetting('kpra'),
  bra: createDefaultSetting('bra'),
});

const activeModalAuthority = ref('fbr');
const showModal = ref(false);
const modalTab = ref('settings');
const showToken = ref(false);
const saving = ref(false);
const testingConnection = ref(false);
const testResult = ref(null);

const entries = ref({ data: [] });
const entrySummary = ref({ total: 0, synced: 0, pending: 0, failed: 0 });
const filters = ref({ search: '', type: '', status: '' });
const syncingAll = ref(false);
const selectedEntry = ref(null);

const activeAuthorityMeta = computed(() => {
  return pakistanAuthorities[activeModalAuthority.value] || pakistanAuthorities.fbr;
});

const activeSetting = computed({
  get: () => authoritySettings.value[activeModalAuthority.value] || createDefaultSetting(activeModalAuthority.value),
  set: (val) => {
    authoritySettings.value[activeModalAuthority.value] = val;
  }
});

const getAuthSetting = (authKey) => {
  return authoritySettings.value[authKey] || createDefaultSetting(authKey);
};

const loadCompanySettings = async () => {
  try {
    const res = await axios.get('/api/fbr-settings', {
      params: { company_id: selectedCompanyId.value }
    });
    if (res.data?.settings) {
      Object.keys(res.data.settings).forEach((key) => {
        if (authoritySettings.value[key]) {
          authoritySettings.value[key] = {
            ...authoritySettings.value[key],
            ...res.data.settings[key],
          };
        }
      });
    } else if (res.data?.setting) {
      authoritySettings.value.fbr = { ...authoritySettings.value.fbr, ...res.data.setting };
    }
    if (res.data?.companies) {
      companies.value = res.data.companies;
    }
    if (res.data?.company_id && !selectedCompanyId.value) {
      selectedCompanyId.value = res.data.company_id;
    }
    await loadEntries(1);
  } catch (e) {
    console.error('Error loading settings:', e);
    showToast('Failed to load integration settings', 'error');
  }
};

const openModal = (authKey) => {
  activeModalAuthority.value = authKey;
  testResult.value = null;
  showModal.value = true;
  loadEntries(1);
};

const toggleAuthStatus = async (authKey) => {
  const current = getAuthSetting(authKey);
  current.is_enabled = !current.is_enabled;
  activeModalAuthority.value = authKey;
  await saveSettings();
};

const saveSettings = async () => {
  saving.value = true;
  try {
    const settingToSave = activeSetting.value;
    const payload = {
      ...settingToSave,
      company_id: selectedCompanyId.value,
      authority_type: activeModalAuthority.value,
    };
    const res = await axios.put('/api/fbr-settings', payload);
    if (res.data?.setting) {
      authoritySettings.value[activeModalAuthority.value] = {
        ...authoritySettings.value[activeModalAuthority.value],
        ...res.data.setting,
      };
    }
    showToast(res.data?.message || `${activeAuthorityMeta.value.name} settings saved successfully!`, 'success');
  } catch (e) {
    showToast(e.response?.data?.message || 'Failed to save settings', 'error');
  } finally {
    saving.value = false;
  }
};

const testConnection = async () => {
  testingConnection.value = true;
  testResult.value = null;
  try {
    const res = await axios.post('/api/fbr-settings/test-connection', {
      company_id: selectedCompanyId.value,
      authority_type: activeModalAuthority.value,
      pos_id: activeSetting.value.pos_id,
      environment: activeSetting.value.environment,
      api_token: activeSetting.value.api_token,
      base_url: activeSetting.value.base_url,
    });
    testResult.value = res.data;
  } catch (e) {
    testResult.value = {
      success: false,
      message: e.response?.data?.message || 'Test connection request failed',
    };
  } finally {
    testingConnection.value = false;
  }
};

const loadEntries = async (page = 1) => {
  try {
    const res = await axios.get('/api/fbr-entries', {
      params: {
        page,
        company_id: selectedCompanyId.value,
        authority_type: activeModalAuthority.value,
        search: filters.value.search,
        type: filters.value.type,
        status: filters.value.status,
      }
    });
    entries.value = res.data?.entries || { data: [] };
    entrySummary.value = res.data?.summary || { total: 0, synced: 0, pending: 0, failed: 0 };
  } catch (e) {
    console.error('Error loading entries:', e);
  }
};

const syncSingleEntry = async (entry) => {
  entry.syncing = true;
  try {
    const res = await axios.post(`/api/fbr-entries/${entry.id}/sync`);
    if (res.data?.success) {
      showToast(res.data.message || `Entry synced with ${activeAuthorityMeta.value.name}!`, 'success');
    } else {
      showToast(res.data.message || 'Sync failed', 'error');
    }
    await loadEntries();
  } catch (e) {
    showToast('Sync request failed', 'error');
  } finally {
    entry.syncing = false;
  }
};

const syncAllPending = async () => {
  syncingAll.value = true;
  try {
    const res = await axios.post('/api/fbr-entries/sync-all', {
      company_id: selectedCompanyId.value,
      authority_type: activeModalAuthority.value,
    });
    showToast(res.data?.message || 'Batch sync completed', 'success');
    await loadEntries();
  } catch (e) {
    showToast('Failed to execute batch sync', 'error');
  } finally {
    syncingAll.value = false;
  }
};

const formatMoney = (val) => {
  return Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

onMounted(() => {
  loadCompanySettings();
});
</script>
