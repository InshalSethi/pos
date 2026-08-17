<template>
  <div class="space-y-8 font-sans">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 p-6 rounded-2xl text-white shadow-md relative overflow-hidden">
      <div class="absolute right-0 top-0 translate-x-4 -translate-y-4 opacity-10 pointer-events-none">
        <svg class="w-72 h-72 text-emerald-400" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
        </svg>
      </div>

      <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center space-x-3 mb-1">
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-widest bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
              FBR Pakistan Integration
            </span>
            <span v-if="setting.is_enabled" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500 text-white shadow-xs">
              <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse mr-1.5"></span>
              Active & Recording
            </span>
            <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-700 text-slate-300">
              Disabled
            </span>
          </div>
          <h2 class="text-xl font-bold tracking-tight">Federal Board of Revenue (FBR) Digital Invoicing</h2>
          <p class="text-xs text-slate-300 max-w-2xl mt-1">
            Configure company-based FBR POS & Digital Invoicing integration. When enabled, sales, purchases, transactions, and payment receipts are fiscalized and recorded to FBR Pakistan in real-time.
          </p>
        </div>

        <!-- Company Switcher inside FBR Tab -->
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

    <!-- Master Toggle Card -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-6 shadow-xs transition-all">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div :class="[
            'w-12 h-12 rounded-xl flex items-center justify-center transition-colors',
            setting.is_enabled ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-zinc-800 dark:text-zinc-400'
          ]">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Enable FBR Pakistan Data Recording</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              When toggled ON, entries for this company will be recorded and queued/submitted to FBR. If OFF, no entries will be created or sent to FBR.
            </p>
          </div>
        </div>

        <button
          @click="setting.is_enabled = !setting.is_enabled"
          :class="[
            'relative inline-flex h-7 w-14 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none',
            setting.is_enabled ? 'bg-emerald-600' : 'bg-slate-300 dark:bg-zinc-700'
          ]"
        >
          <span
            :class="[
              'pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out',
              setting.is_enabled ? 'translate-x-7' : 'translate-x-0'
            ]"
          ></span>
        </button>
      </div>

      <!-- Scope Toggles Grid -->
      <div v-if="setting.is_enabled" class="mt-6 pt-6 border-t border-slate-100 dark:border-zinc-800 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-3.5 bg-slate-50 dark:bg-zinc-800/60 rounded-xl border border-slate-200/60 dark:border-zinc-700/60 flex items-center justify-between">
          <div>
            <span class="text-xs font-bold text-slate-900 dark:text-slate-100 block">Sales Invoices</span>
            <span class="text-[11px] text-slate-500 dark:text-slate-400">Record Sales & Refunds</span>
          </div>
          <input type="checkbox" v-model="setting.sync_sales" class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500 cursor-pointer" />
        </div>

        <div class="p-3.5 bg-slate-50 dark:bg-zinc-800/60 rounded-xl border border-slate-200/60 dark:border-zinc-700/60 flex items-center justify-between">
          <div>
            <span class="text-xs font-bold text-slate-900 dark:text-slate-100 block">Purchases</span>
            <span class="text-[11px] text-slate-500 dark:text-slate-400">Record Purchase Orders</span>
          </div>
          <input type="checkbox" v-model="setting.sync_purchases" class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500 cursor-pointer" />
        </div>

        <div class="p-3.5 bg-slate-50 dark:bg-zinc-800/60 rounded-xl border border-slate-200/60 dark:border-zinc-700/60 flex items-center justify-between">
          <div>
            <span class="text-xs font-bold text-slate-900 dark:text-slate-100 block">Transactions</span>
            <span class="text-[11px] text-slate-500 dark:text-slate-400">General & Bank Entries</span>
          </div>
          <input type="checkbox" v-model="setting.sync_transactions" class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500 cursor-pointer" />
        </div>

        <div class="p-3.5 bg-slate-50 dark:bg-zinc-800/60 rounded-xl border border-slate-200/60 dark:border-zinc-700/60 flex items-center justify-between">
          <div>
            <span class="text-xs font-bold text-slate-900 dark:text-slate-100 block">Payments</span>
            <span class="text-[11px] text-slate-500 dark:text-slate-400">Payment Receipts</span>
          </div>
          <input type="checkbox" v-model="setting.sync_payments" class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500 cursor-pointer" />
        </div>
      </div>
    </div>

    <!-- Credentials & Technical Settings -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-6 shadow-xs space-y-6">
      <div class="border-b border-slate-200/80 dark:border-zinc-800 pb-3 flex items-center justify-between">
        <div>
          <h3 class="text-sm font-bold text-slate-900 dark:text-white">FBR API Credentials & Environment</h3>
          <p class="text-xs text-slate-500 dark:text-slate-400">Provide your official FBR IRIS POS registration credentials</p>
        </div>
        <div class="flex items-center space-x-2">
          <button
            @click="testConnection"
            :disabled="testingConnection"
            class="px-3.5 py-2 bg-slate-100 dark:bg-zinc-800 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors flex items-center space-x-1.5 cursor-pointer disabled:opacity-50"
          >
            <svg v-if="testingConnection" class="animate-spin h-3.5 w-3.5 text-current" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span>{{ testingConnection ? 'Testing...' : 'Test Connection' }}</span>
          </button>
        </div>
      </div>

      <!-- Test Connection Result Alert -->
      <div v-if="testResult" :class="[
        'p-4 rounded-xl border text-xs font-semibold flex items-center justify-between',
        testResult.success ? 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800'
      ]">
        <span>{{ testResult.message }}</span>
        <button @click="testResult = null" class="text-current opacity-70 hover:opacity-100 font-bold">&times;</button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Environment Mode</label>
          <select
            v-model="setting.environment"
            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 text-slate-900 dark:text-slate-100 rounded-xl text-xs font-semibold focus:outline-none"
          >
            <option value="sandbox">Sandbox (Testing Environment)</option>
            <option value="production">Production (Live FBR Portal)</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">FBR POS Registration ID (POSID)</label>
          <input
            type="text"
            v-model="setting.pos_id"
            placeholder="e.g. 100001"
            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 text-slate-900 dark:text-slate-100 rounded-xl text-xs font-semibold focus:outline-none"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Business NTN</label>
          <input
            type="text"
            v-model="setting.ntn"
            placeholder="e.g. 1234567-8"
            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 text-slate-900 dark:text-slate-100 rounded-xl text-xs font-semibold focus:outline-none"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Sales Tax Reg. Number (STRN)</label>
          <input
            type="text"
            v-model="setting.strn"
            placeholder="e.g. 3277876543210"
            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 text-slate-900 dark:text-slate-100 rounded-xl text-xs font-semibold focus:outline-none"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Registered Business Name</label>
          <input
            type="text"
            v-model="setting.business_name"
            placeholder="Company Legal Name"
            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 text-slate-900 dark:text-slate-100 rounded-xl text-xs font-semibold focus:outline-none"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Branch / Outlet Name</label>
          <input
            type="text"
            v-model="setting.branch_name"
            placeholder="Main Branch"
            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 text-slate-900 dark:text-slate-100 rounded-xl text-xs font-semibold focus:outline-none"
          />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">API Bearer Token / Key</label>
          <div class="relative">
            <input
              :type="showToken ? 'text' : 'password'"
              v-model="setting.api_token"
              placeholder="Bearer Token from FBR IRIS Portal"
              class="w-full px-3.5 py-2.5 pr-10 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 text-slate-900 dark:text-slate-100 rounded-xl text-xs font-semibold focus:outline-none"
            />
            <button
              type="button"
              @click="showToken = !showToken"
              class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs"
            >
              {{ showToken ? 'Hide' : 'Show' }}
            </button>
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Base API URL</label>
          <input
            type="text"
            v-model="setting.base_url"
            placeholder="https://sandbox.fbr.gov.pk/api/v1"
            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 text-slate-900 dark:text-slate-100 rounded-xl text-xs font-semibold focus:outline-none"
          />
        </div>
      </div>

      <!-- Action Footer -->
      <div class="pt-4 border-t border-slate-200/80 dark:border-zinc-800 flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <input type="checkbox" id="auto_sync" v-model="setting.auto_sync" class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500 cursor-pointer" />
          <label for="auto_sync" class="text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer">
            Automatically post entries to FBR on creation
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
          <span>{{ saving ? 'Saving Settings...' : 'Save FBR Settings' }}</span>
        </button>
      </div>
    </div>

    <!-- FBR Audit Logs & Activity Section -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-6 shadow-xs space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/80 dark:border-zinc-800 pb-4">
        <div>
          <h3 class="text-sm font-bold text-slate-900 dark:text-white">FBR Recorded Entries Audit Log</h3>
          <p class="text-xs text-slate-500 dark:text-slate-400">View real-time records of sales, purchases, transactions, and payments fiscalized to FBR</p>
        </div>

        <div class="flex items-center space-x-2">
          <button
            @click="syncAllPending"
            :disabled="syncingAll"
            class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-colors flex items-center space-x-1.5 shadow-xs cursor-pointer disabled:opacity-50"
          >
            <svg v-if="syncingAll" class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span>{{ syncingAll ? 'Syncing...' : 'Sync All Pending' }}</span>
          </button>
        </div>
      </div>

      <!-- Stats Summary -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 bg-slate-50 dark:bg-zinc-800/40 rounded-xl border border-slate-200/60 dark:border-zinc-800">
          <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Recorded</span>
          <span class="text-xl font-bold text-slate-900 dark:text-white">{{ entrySummary.total || 0 }}</span>
        </div>
        <div class="p-4 bg-emerald-50/60 dark:bg-emerald-950/20 rounded-xl border border-emerald-200/60 dark:border-emerald-800/40">
          <span class="text-[11px] font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider block">Synced to FBR</span>
          <span class="text-xl font-bold text-emerald-700 dark:text-emerald-300">{{ entrySummary.synced || 0 }}</span>
        </div>
        <div class="p-4 bg-amber-50/60 dark:bg-amber-950/20 rounded-xl border border-amber-200/60 dark:border-amber-800/40">
          <span class="text-[11px] font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider block">Pending Queue</span>
          <span class="text-xl font-bold text-amber-700 dark:text-amber-300">{{ entrySummary.pending || 0 }}</span>
        </div>
        <div class="p-4 bg-rose-50/60 dark:bg-rose-950/20 rounded-xl border border-rose-200/60 dark:border-rose-800/40">
          <span class="text-[11px] font-bold text-rose-700 dark:text-rose-400 uppercase tracking-wider block">Failed</span>
          <span class="text-xl font-bold text-rose-700 dark:text-rose-300">{{ entrySummary.failed || 0 }}</span>
        </div>
      </div>

      <!-- Filters -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <input
          type="text"
          v-model="filters.search"
          @input="loadEntries(1)"
          placeholder="Search by Ref #, Name, NTN, IRN..."
          class="px-3 py-2 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold focus:outline-none"
        />

        <select
          v-model="filters.type"
          @change="loadEntries(1)"
          class="px-3 py-2 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold focus:outline-none"
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
          class="px-3 py-2 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold focus:outline-none"
        >
          <option value="">All Statuses</option>
          <option value="synced">Synced</option>
          <option value="pending">Pending</option>
          <option value="failed">Failed</option>
        </select>
      </div>

      <!-- Log Table -->
      <div class="overflow-x-auto border border-slate-200/80 dark:border-zinc-800 rounded-xl">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 dark:bg-zinc-800/60 text-slate-600 dark:text-slate-300 font-bold uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3">Ref #</th>
              <th class="px-4 py-3">Type</th>
              <th class="px-4 py-3">Party / Customer</th>
              <th class="px-4 py-3">Amount</th>
              <th class="px-4 py-3">FBR Invoice IRN</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
            <tr v-for="e in entries.data" :key="e.id" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/40 font-medium">
              <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">
                {{ e.reference_number }}
              </td>
              <td class="px-4 py-3 capitalize">
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
              <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                {{ e.buyer_name || 'N/A' }}
                <span v-if="e.buyer_ntn" class="block text-[10px] text-slate-400">NTN: {{ e.buyer_ntn }}</span>
              </td>
              <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">
                PKR {{ formatMoney(e.total_amount) }}
              </td>
              <td class="px-4 py-3 font-mono text-[11px] text-slate-600 dark:text-slate-300">
                {{ e.fbr_invoice_number || '-' }}
              </td>
              <td class="px-4 py-3">
                <span :class="[
                  'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase',
                  e.status === 'synced' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' :
                  e.status === 'pending' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' :
                  'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300'
                ]">
                  {{ e.status }}
                </span>
              </td>
              <td class="px-4 py-3 text-right space-x-2">
                <button
                  @click="openDetailsModal(e)"
                  class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 rounded-lg text-slate-700 dark:text-slate-200 font-semibold text-[11px]"
                >
                  Details
                </button>
                <button
                  @click="syncSingleEntry(e)"
                  :disabled="e.syncing"
                  class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/60 rounded-lg font-semibold text-[11px] disabled:opacity-50"
                >
                  {{ e.syncing ? 'Syncing...' : (e.status === 'synced' ? 'Re-Sync' : 'Sync Now') }}
                </button>
              </td>
            </tr>
            <tr v-if="!entries.data || entries.data.length === 0">
              <td colspan="7" class="text-center py-8 text-slate-400 text-xs font-medium">
                No FBR entries recorded yet for this company.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Details Modal -->
    <div v-if="selectedEntry" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl max-w-2xl w-full p-6 space-y-4 shadow-xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-800 pb-3">
          <h3 class="text-sm font-bold text-slate-900 dark:text-white">
            FBR Entry Details: {{ selectedEntry.reference_number }}
          </h3>
          <button @click="selectedEntry = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg font-bold">&times;</button>
        </div>

        <div class="grid grid-cols-2 gap-4 text-xs">
          <div>
            <span class="text-slate-400 block font-medium">FBR Invoice IRN</span>
            <span class="font-bold text-slate-900 dark:text-white font-mono">{{ selectedEntry.fbr_invoice_number || 'Not Generated' }}</span>
          </div>
          <div>
            <span class="text-slate-400 block font-medium">Synced At</span>
            <span class="font-bold text-slate-900 dark:text-white">{{ selectedEntry.synced_at || 'Pending' }}</span>
          </div>
        </div>

        <div v-if="selectedEntry.error_message" class="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs">
          <strong class="block mb-0.5">Error Details:</strong>
          {{ selectedEntry.error_message }}
        </div>

        <div>
          <span class="text-xs font-bold text-slate-700 dark:text-slate-300 block mb-1">Payload Sent to FBR</span>
          <pre class="bg-slate-900 text-emerald-400 p-3 rounded-xl text-[11px] overflow-x-auto font-mono max-h-48">{{ JSON.stringify(selectedEntry.payload, null, 2) }}</pre>
        </div>

        <div>
          <span class="text-xs font-bold text-slate-700 dark:text-slate-300 block mb-1">FBR API Response</span>
          <pre class="bg-slate-900 text-slate-200 p-3 rounded-xl text-[11px] overflow-x-auto font-mono max-h-48">{{ JSON.stringify(selectedEntry.response_payload, null, 2) }}</pre>
        </div>

        <div class="flex justify-end pt-2">
          <button @click="selectedEntry = null" class="px-4 py-2 bg-slate-900 text-white dark:bg-white dark:text-slate-900 text-xs font-bold rounded-xl">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from '@/composables/useToast';

const { showToast } = useToast();

const selectedCompanyId = ref(null);
const companies = ref([]);
const setting = ref({
  is_enabled: false,
  environment: 'sandbox',
  pos_id: '',
  ntn: '',
  strn: '',
  business_name: '',
  branch_name: '',
  api_token: '',
  base_url: '',
  auto_sync: true,
  sync_sales: true,
  sync_purchases: true,
  sync_transactions: true,
  sync_payments: true,
});

const showToken = ref(false);
const saving = ref(false);
const testingConnection = ref(false);
const testResult = ref(null);

const entries = ref({ data: [] });
const entrySummary = ref({ total: 0, synced: 0, pending: 0, failed: 0 });
const filters = ref({ search: '', type: '', status: '' });
const syncingAll = ref(false);
const selectedEntry = ref(null);

const loadCompanySettings = async () => {
  try {
    const res = await axios.get('/api/fbr-settings', {
      params: { company_id: selectedCompanyId.value }
    });
    if (res.data?.setting) {
      setting.value = { ...setting.value, ...res.data.setting };
    }
    if (res.data?.companies) {
      companies.value = res.data.companies;
    }
    if (res.data?.company_id && !selectedCompanyId.value) {
      selectedCompanyId.value = res.data.company_id;
    }
    await loadEntries(1);
  } catch (e) {
    console.error('Error loading FBR settings:', e);
    showToast('Failed to load FBR settings', 'error');
  }
};

const saveSettings = async () => {
  saving.value = true;
  try {
    const payload = {
      ...setting.value,
      company_id: selectedCompanyId.value,
    };
    const res = await axios.put('/api/fbr-settings', payload);
    showToast(res.data?.message || 'FBR Settings saved successfully!', 'success');
  } catch (e) {
    showToast(e.response?.data?.message || 'Failed to save FBR settings', 'error');
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
      pos_id: setting.value.pos_id,
      environment: setting.value.environment,
      api_token: setting.value.api_token,
      base_url: setting.value.base_url,
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
        search: filters.value.search,
        type: filters.value.type,
        status: filters.value.status,
      }
    });
    entries.value = res.data?.entries || { data: [] };
    entrySummary.value = res.data?.summary || { total: 0, synced: 0, pending: 0, failed: 0 };
  } catch (e) {
    console.error('Error loading FBR entries:', e);
  }
};

const syncSingleEntry = async (entry) => {
  entry.syncing = true;
  try {
    const res = await axios.post(`/api/fbr-entries/${entry.id}/sync`);
    if (res.data?.success) {
      showToast(res.data.message || 'Entry synced with FBR!', 'success');
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
    });
    showToast(res.data?.message || 'Batch sync completed', 'success');
    await loadEntries();
  } catch (e) {
    showToast('Failed to execute batch sync', 'error');
  } finally {
    syncingAll.value = false;
  }
};

const openDetailsModal = (entry) => {
  selectedEntry.value = entry;
};

const formatMoney = (val) => {
  return Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

onMounted(() => {
  loadCompanySettings();
});
</script>
