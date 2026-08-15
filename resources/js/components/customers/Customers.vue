<template>
  <div class="w-full max-w-full py-8 px-4 sm:px-6 lg:px-8 dark:bg-zinc-950 min-h-screen">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-zinc-100 tracking-tight">Customer Management</h1>
        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">Manage your customer database and relationships</p>
      </div>
      <button
        @click="handleCreateCustomer"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center space-x-1.5 active:scale-95 cursor-pointer"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
        <span>Add Customer</span>
      </button>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
      <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-5 shadow-soft">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Total Customers</p>
            <p class="text-2xl font-extrabold text-slate-800 dark:text-zinc-100 mt-1">{{ formatNumber(statistics.total_customers || 0) }}</p>
          </div>
          <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-5 shadow-soft">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Active Customers</p>
            <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">{{ formatNumber(statistics.active_customers || 0) }}</p>
          </div>
          <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-5 shadow-soft">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Inactive Customers</p>
            <p class="text-2xl font-extrabold text-rose-500 dark:text-rose-400 mt-1">{{ formatNumber(statistics.inactive_customers || 0) }}</p>
          </div>
          <div class="w-10 h-10 bg-rose-50 dark:bg-rose-900/30 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-rose-500 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-soft">
      <div class="flex flex-col sm:flex-row items-center justify-between p-4 gap-3 border-b border-slate-100 dark:border-zinc-800">
        <!-- Search & Toggle Group -->
        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
          <!-- Search -->
          <div class="relative w-full sm:w-80">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <svg class="w-4 h-4 text-slate-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by name, email, phone or location"
              class="w-full pl-9 pr-4 py-1.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs focus:outline-none focus:ring-0 focus:bg-white dark:focus:bg-zinc-800 transition-all text-slate-700 dark:text-zinc-200 dark:placeholder-zinc-500"
              @input="debouncedSearch"
            />
          </div>
          <!-- Filter Tabs Bar (All | Walk-In | Active | Inactive) -->
          <div class="flex items-center bg-slate-100 dark:bg-zinc-800 p-0.5 rounded-lg text-[11px] font-semibold">
            <button
              type="button"
              @click="setTab('all')"
              :class="activeTab === 'all' ? 'bg-white dark:bg-zinc-700 text-slate-800 dark:text-zinc-100 shadow-xs' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-700 dark:hover:text-zinc-300'"
              class="px-3 py-1 rounded-md transition-all cursor-pointer"
            >
              All
            </button>
            <button
              type="button"
              @click="setTab('walk_in')"
              :class="activeTab === 'walk_in' ? 'bg-white dark:bg-zinc-700 text-slate-800 dark:text-zinc-100 shadow-xs' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-700 dark:hover:text-zinc-300'"
              class="px-3 py-1 rounded-md transition-all cursor-pointer"
            >
              Walk-In
            </button>
            <button
              type="button"
              @click="setTab('active')"
              :class="activeTab === 'active' ? 'bg-white dark:bg-zinc-700 text-slate-800 dark:text-zinc-100 shadow-xs' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-700 dark:hover:text-zinc-300'"
              class="px-3 py-1 rounded-md transition-all cursor-pointer"
            >
              Active
            </button>
            <button
              type="button"
              @click="setTab('inactive')"
              :class="activeTab === 'inactive' ? 'bg-white dark:bg-zinc-700 text-slate-800 dark:text-zinc-100 shadow-xs' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-700 dark:hover:text-zinc-300'"
              class="px-3 py-1 rounded-md transition-all cursor-pointer"
            >
              Inactive
            </button>
          </div>

          <!-- View Mode Switcher (Table / Grid) -->
          <div class="flex items-center bg-slate-100 dark:bg-zinc-800 p-0.5 rounded-lg text-xs font-semibold">
            <button
              type="button"
              @click="viewMode = 'table'"
              :class="viewMode === 'table' ? 'bg-white dark:bg-zinc-700 text-slate-800 dark:text-zinc-100 shadow-xs' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-700 dark:hover:text-zinc-300'"
              class="px-2.5 py-1 rounded-md transition-all cursor-pointer flex items-center gap-1.5"
              title="Table View"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
              <span>Table</span>
            </button>
            <button
              type="button"
              @click="viewMode = 'grid'"
              :class="viewMode === 'grid' ? 'bg-white dark:bg-zinc-700 text-slate-800 dark:text-zinc-100 shadow-xs' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-700 dark:hover:text-zinc-300'"
              class="px-2.5 py-1 rounded-md transition-all cursor-pointer flex items-center gap-1.5"
              title="Grid View"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 14a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 14a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
              <span>Grid</span>
            </button>
          </div>
        </div>

        <!-- Showing -->
        <div class="flex items-center space-x-2 text-xs text-slate-500 dark:text-zinc-400">
          <span>Showing</span>
          <select
            v-model="perPage"
            @change="loadCustomers(1)"
            class="border border-slate-200 dark:border-zinc-700 rounded px-1.5 py-0.5 focus:outline-none focus:ring-1 focus:ring-blue-500 cursor-pointer bg-white dark:bg-zinc-800 dark:text-zinc-200"
          >
            <option :value="15">15</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
          <span>of {{ customers.total || 0 }} results</span>
        </div>
      </div>

      <!-- TABLE VIEW -->
      <div v-if="viewMode === 'table'" class="h-[400px] max-h-[400px] overflow-y-auto overflow-x-auto custom-scrollbar relative">
        <table class="w-full text-left text-xs border-collapse">
          <thead class="sticky top-0 z-10 bg-slate-50 dark:bg-zinc-800">
            <tr class="bg-slate-50 dark:bg-zinc-800 border-b border-slate-200 dark:border-zinc-700 text-slate-500 dark:text-zinc-400 uppercase font-bold tracking-wider">
              <th class="py-3 px-4 bg-slate-50 dark:bg-zinc-800/50">Customer</th>
              <th class="py-3 px-4 bg-slate-50 dark:bg-zinc-800/50">Contact</th>
              <th class="py-3 px-4 bg-slate-50 dark:bg-zinc-800/50">Location</th>
              <th class="py-3 px-4 text-right bg-slate-50 dark:bg-zinc-800/50">Credit Limit</th>
              <th class="py-3 px-4 text-right bg-slate-50 dark:bg-zinc-800/50">Wallet</th>
              <th class="py-3 px-4 text-right bg-slate-50 dark:bg-zinc-800/50">Due Amount</th>
              <th class="py-3 px-4 text-center bg-slate-50 dark:bg-zinc-800/50">Attachments</th>
              <th class="py-3 px-4 text-center bg-slate-50 dark:bg-zinc-800/50">Status</th>
              <th class="py-3 px-4 text-center bg-slate-50 dark:bg-zinc-800/50 w-[80px]">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100/70 dark:divide-zinc-800">
            <tr v-if="loading" class="bg-white dark:bg-zinc-900">
              <td colspan="9" class="py-12 text-center text-slate-400 dark:text-zinc-500">
                <div class="flex flex-col items-center justify-center space-y-2">
                  <div class="animate-spin rounded-full h-7 w-7 border-2 border-slate-300 dark:border-zinc-600 border-t-blue-600"></div>
                  <span class="text-xs font-semibold">Loading customers...</span>
                </div>
              </td>
            </tr>
            <tr v-else-if="!customers.data || customers.data.length === 0" class="bg-white dark:bg-zinc-900">
              <td colspan="9" class="py-16 text-center text-slate-400 dark:text-zinc-500 italic">
                <svg class="mx-auto h-10 w-10 text-slate-300 dark:text-zinc-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <span>No customers found. Get started by adding your first customer.</span>
              </td>
            </tr>
            <tr v-else v-for="item in customers.data" :key="item.id" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/50 transition-colors">
              <!-- Customer -->
              <td class="py-3.5 px-4 align-middle bg-white dark:bg-zinc-900">
                <div class="flex items-center space-x-3">
                  <!-- Avatar / Profile Picture -->
                  <div class="relative shrink-0">
                    <img
                      v-if="item.profile_image"
                      :src="getStorageUrl(item.profile_image)"
                      @click.stop="openLightbox(getStorageUrl(item.profile_image), item.name)"
                      class="w-9 h-9 rounded-full object-cover ring-2 ring-blue-500/20 hover:ring-blue-500 hover:scale-110 transition-all cursor-pointer shadow-xs"
                      title="Click to view photo"
                      alt="Customer Profile"
                    />
                    <div
                      v-else
                      class="w-9 h-9 rounded-full bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800/40 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-xs shadow-xs"
                    >
                      {{ getInitials(item.name) }}
                    </div>
                  </div>
                  <div>
                    <div class="flex items-center space-x-1.5">
                      <button @click="viewLedger(item)" class="font-bold text-slate-800 dark:text-zinc-100 text-sm hover:text-blue-600 dark:hover:text-blue-400 cursor-pointer text-left line-clamp-1">{{ item.name }}</button>
                      <span
                        v-if="item.type === 'walk_in'"
                        class="px-2 py-0.5 text-[10px] font-semibold bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 rounded-full shrink-0"
                      >
                        Walk-In
                      </span>
                    </div>
                    <div class="text-[10px] text-slate-400 dark:text-zinc-500 mt-0.5">ID: #{{ item.id }}</div>
                  </div>
                </div>
              </td>
              <!-- Contact -->
              <td class="py-3.5 px-4 align-middle bg-white dark:bg-zinc-900">
                <div class="text-slate-700 dark:text-zinc-200 text-xs">{{ item.email || '-' }}</div>
                <div class="text-[10px] text-slate-400 dark:text-zinc-500 mt-0.5">{{ item.phone || item.mobile || '-' }}</div>
              </td>
              <!-- Location -->
              <td class="py-3.5 px-4 align-middle bg-white dark:bg-zinc-900">
                <div class="text-slate-700 dark:text-zinc-200 text-xs">{{ item.city || '-' }}</div>
                <div class="text-[10px] text-slate-400 dark:text-zinc-500 mt-0.5">{{ item.state || '-' }}</div>
              </td>
              <!-- Credit Limit -->
              <td class="py-3.5 px-4 text-right font-semibold text-slate-700 dark:text-zinc-200 text-sm align-middle bg-white dark:bg-zinc-900">
                {{ currencySymbol }}{{ formatNumber(item.credit_limit || 0) }}
              </td>
              <!-- Wallet -->
              <td class="py-3.5 px-4 text-right font-bold text-sm align-middle bg-white dark:bg-zinc-900">
                <span
                  v-if="parseFloat(item.wallet_balance || 0) > 0"
                  class="text-amber-600 dark:text-amber-400"
                >
                  {{ currencySymbol }}{{ formatNumber(item.wallet_balance) }}
                </span>
                <span
                  v-else
                  class="text-slate-400 dark:text-zinc-500 font-medium"
                >
                  0.00
                </span>
              </td>
              <!-- Due Amount -->
              <td class="py-3.5 px-4 text-right font-bold text-sm align-middle bg-white dark:bg-zinc-900">
                <span
                  v-if="parseFloat(item.due_amount || 0) > 0"
                  class="text-rose-600 dark:text-rose-450"
                >
                  {{ currencySymbol }}{{ formatNumber(item.due_amount) }}
                </span>
                <span
                  v-else
                  class="text-slate-400 dark:text-zinc-500 font-medium"
                >
                  0.00
                </span>
              </td>
              <!-- Attachments Column -->
              <td class="py-3.5 px-4 text-center align-middle bg-white dark:bg-zinc-900">
                <div v-if="getAttachmentItems(item).length > 0" class="flex flex-wrap justify-center gap-1">
                  <button
                    v-for="att in getAttachmentItems(item)"
                    :key="att.url"
                    @click.stop="downloadFile(att.url, att.filename)"
                    class="inline-flex items-center space-x-1 px-2 py-0.5 bg-slate-100 dark:bg-zinc-800 hover:bg-blue-50 dark:hover:bg-blue-900/40 text-slate-700 dark:text-zinc-300 hover:text-blue-600 dark:hover:text-blue-400 rounded text-[10px] font-medium border border-slate-200 dark:border-zinc-700 cursor-pointer transition-colors"
                    :title="'Download ' + att.filename"
                  >
                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span class="truncate max-w-[80px]">{{ att.filename }}</span>
                  </button>
                </div>
                <span v-else class="text-slate-400 dark:text-zinc-500 text-[11px]">-</span>
              </td>
              <!-- Status -->
              <td class="py-3.5 px-4 text-center align-middle bg-white dark:bg-zinc-900">
                <span
                  :class="item.is_active ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 border border-slate-900 dark:border-white font-bold' : 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400'"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                >
                  {{ item.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <!-- Action -->
              <td class="py-3.5 px-4 text-center relative align-middle bg-white dark:bg-zinc-900">
                <button
                  @click.stop="toggleActionDropdown(item.id)"
                  class="text-slate-400 dark:text-zinc-500 hover:text-slate-600 dark:hover:text-zinc-300 p-1 rounded-full hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all focus:outline-none cursor-pointer"
                >
                  <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 5a2 2 0 100-4 2 2 0 000 4zm0 9a2 2 0 100-4 2 2 0 000 4zm0 9a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                </button>
                <div
                  v-if="openActionDropdown === item.id"
                  class="absolute right-4 mt-1 w-32 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-lg shadow-lg py-1 z-50 animate-fade-in text-left"
                >
                  <button @click="viewCustomer(item)" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>View</span>
                  </button>
                  <button @click="editCustomer(item)" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit</span>
                  </button>
                  <button @click="viewLedger(item)" class="w-full text-left px-3 py-1.5 text-xs text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Ledger</span>
                  </button>
                  <div class="border-t border-slate-100 dark:border-zinc-800 my-1"></div>
                  <button @click="deleteCustomer(item)" class="w-full text-left px-3 py-1.5 text-xs text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Delete</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- GRID VIEW -->
      <div v-else class="p-5">
        <div v-if="loading" class="py-12 text-center text-slate-400 dark:text-zinc-500">
          <div class="flex flex-col items-center justify-center space-y-2">
            <div class="animate-spin rounded-full h-7 w-7 border-2 border-slate-300 dark:border-zinc-600 border-t-blue-600"></div>
            <span class="text-xs font-semibold">Loading customers...</span>
          </div>
        </div>
        <div v-else-if="!customers.data || customers.data.length === 0" class="py-16 text-center text-slate-400 dark:text-zinc-500 italic">
          <svg class="mx-auto h-10 w-10 text-slate-300 dark:text-zinc-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
          <span>No customers found. Get started by adding your first customer.</span>
        </div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <div
            v-for="item in customers.data"
            :key="item.id"
            class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-5 shadow-soft hover:shadow-md transition-all flex flex-col justify-between"
          >
            <div>
              <!-- Card Top Header -->
              <div class="flex items-start justify-between mb-4">
                <div class="flex items-center space-x-3">
                  <!-- Avatar / Profile Picture -->
                  <div class="relative">
                    <img
                      v-if="item.profile_image"
                      :src="getStorageUrl(item.profile_image)"
                      @click.stop="openLightbox(getStorageUrl(item.profile_image), item.name)"
                      class="w-12 h-12 rounded-full object-cover ring-2 ring-blue-500/20 hover:scale-105 hover:ring-blue-500 transition-all cursor-pointer shadow-xs"
                      title="Click to view photo"
                      alt="Customer Profile"
                    />
                    <div
                      v-else
                      class="w-12 h-12 rounded-full bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800/40 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-sm shadow-xs"
                    >
                      {{ getInitials(item.name) }}
                    </div>
                  </div>
                  <div>
                    <div class="flex items-center space-x-1.5">
                      <button @click="viewLedger(item)" class="font-bold text-slate-800 dark:text-zinc-100 text-sm hover:text-blue-600 dark:hover:text-blue-400 cursor-pointer text-left line-clamp-1">
                        {{ item.name }}
                      </button>
                      <span
                        v-if="item.type === 'walk_in'"
                        class="px-2 py-0.5 text-[10px] font-semibold bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 rounded-full shrink-0"
                      >
                        Walk-In
                      </span>
                    </div>
                    <span class="text-[10px] text-slate-400 dark:text-zinc-500">ID: #{{ item.id }}</span>
                  </div>
                </div>

                <!-- Status Badge -->
                <span
                  :class="item.is_active ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 border border-slate-900 dark:border-white font-bold' : 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400'"
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold"
                >
                  {{ item.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>

              <!-- Contact Information -->
              <div class="space-y-1.5 py-3 border-y border-slate-100 dark:border-zinc-800 text-xs text-slate-600 dark:text-zinc-300">
                <div class="flex items-center space-x-2">
                  <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                  <span class="truncate">{{ item.email || 'No email' }}</span>
                </div>
                <div class="flex items-center space-x-2">
                  <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                  <span>{{ item.phone || item.mobile || 'No phone' }}</span>
                </div>
                <div class="flex items-center space-x-2">
                  <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                  <span class="truncate">{{ [item.city, item.state, item.country].filter(Boolean).join(', ') || 'No location' }}</span>
                </div>
              </div>

              <!-- Financial Stats Grid -->
              <div class="grid grid-cols-3 gap-2 py-3 text-center border-b border-slate-100 dark:border-zinc-800">
                <div class="bg-slate-50 dark:bg-zinc-800/40 p-2 rounded-lg">
                  <p class="text-[10px] text-slate-400 dark:text-zinc-500 font-bold uppercase">Credit</p>
                  <p class="text-xs font-extrabold text-slate-800 dark:text-zinc-100 mt-0.5">{{ currencySymbol }}{{ formatNumber(item.credit_limit || 0) }}</p>
                </div>
                <div class="bg-amber-50/60 dark:bg-amber-900/20 p-2 rounded-lg">
                  <p class="text-[10px] text-amber-600 dark:text-amber-400 font-bold uppercase">Wallet</p>
                  <p class="text-xs font-extrabold text-amber-700 dark:text-amber-300 mt-0.5">{{ currencySymbol }}{{ formatNumber(item.wallet_balance || 0) }}</p>
                </div>
                <div class="bg-rose-50/60 dark:bg-rose-900/20 p-2 rounded-lg">
                  <p class="text-[10px] text-rose-500 dark:text-rose-400 font-bold uppercase">Due</p>
                  <p class="text-xs font-extrabold text-rose-600 dark:text-rose-400 mt-0.5">{{ currencySymbol }}{{ formatNumber(item.due_amount || 0) }}</p>
                </div>
              </div>

              <!-- Attachments Badges Section -->
              <div v-if="getAttachmentItems(item).length > 0" class="pt-3 pb-1">
                <p class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                  <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                  <span>Attachments ({{ getAttachmentItems(item).length }})</span>
                </p>
                <div class="flex flex-wrap gap-1.5">
                  <button
                    v-for="att in getAttachmentItems(item)"
                    :key="att.url"
                    @click.stop="downloadFile(att.url, att.filename)"
                    class="inline-flex items-center space-x-1 px-2 py-1 bg-slate-100 dark:bg-zinc-800 hover:bg-blue-50 dark:hover:bg-blue-900/40 text-slate-700 dark:text-zinc-300 hover:text-blue-600 dark:hover:text-blue-400 rounded-md text-[11px] font-medium transition-colors border border-slate-200 dark:border-zinc-700 cursor-pointer max-w-full"
                    :title="'Click to download ' + att.filename"
                  >
                    <svg class="w-3 h-3 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span class="truncate max-w-[120px]">{{ att.filename }}</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Card Actions -->
            <div class="flex items-center justify-between pt-3 mt-3 border-t border-slate-100 dark:border-zinc-800">
              <button
                @click="viewLedger(item)"
                class="px-2.5 py-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors flex items-center space-x-1 cursor-pointer"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Ledger</span>
              </button>
              <div class="flex items-center space-x-1">
                <button
                  @click="viewCustomer(item)"
                  class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
                  title="View Details"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>
                <button
                  @click="editCustomer(item)"
                  class="p-1.5 text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors cursor-pointer"
                  title="Edit Customer"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button
                  @click="deleteCustomer(item)"
                  class="p-1.5 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition-colors cursor-pointer"
                  title="Delete Customer"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="flex items-center justify-between p-4 border-t border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900">
        <div class="text-xs text-slate-500 dark:text-zinc-400">
          Page {{ customers.current_page || 1 }} of {{ customers.last_page || 1 }}
        </div>
        <div class="flex items-center space-x-1">
          <button
            @click="changePage(customers.current_page - 1)"
            :disabled="customers.current_page === 1"
            class="relative inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-xs font-semibold text-slate-500 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800 disabled:opacity-50 cursor-pointer"
          >
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <template v-for="page in visiblePages" :key="page">
            <button
              @click="changePage(page)"
              class="relative inline-flex items-center px-3 py-1.5 border text-xs font-bold transition-all cursor-pointer rounded-lg"
              :class="page === customers.current_page ? 'z-10 bg-slate-100 dark:bg-zinc-800 border-slate-300 dark:border-zinc-600 text-slate-800 dark:text-zinc-100' : 'bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-700 text-slate-500 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800'"
            >
              {{ page }}
            </button>
          </template>
          <button
            @click="changePage(customers.current_page + 1)"
            :disabled="customers.current_page === customers.last_page"
            class="relative inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-xs font-semibold text-slate-500 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800 disabled:opacity-50 cursor-pointer"
          >
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Image Lightbox Modal -->
    <div
      v-if="showLightbox"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fade-in"
      @click="showLightbox = false"
    >
      <div class="relative max-w-3xl max-h-[90vh] bg-white dark:bg-zinc-900 rounded-2xl overflow-hidden shadow-2xl border border-slate-200 dark:border-zinc-800 flex flex-col" @click.stop>
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-800/50">
          <div class="flex items-center space-x-2">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span class="font-bold text-sm text-slate-800 dark:text-zinc-100">{{ lightboxTitle || 'Profile Photo' }}</span>
          </div>
          <div class="flex items-center space-x-2">
            <button
              @click="downloadFile(lightboxSrc, (lightboxTitle || 'customer_photo') + '.jpg')"
              class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold shadow-xs flex items-center space-x-1 cursor-pointer transition-all"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
              <span>Download</span>
            </button>
            <button
              @click="showLightbox = false"
              class="w-7 h-7 rounded-full bg-slate-200 dark:bg-zinc-700 text-slate-600 dark:text-zinc-300 hover:bg-slate-300 dark:hover:bg-zinc-600 flex items-center justify-center text-xs font-bold cursor-pointer transition-all"
            >
              ✕
            </button>
          </div>
        </div>
        <div class="p-4 flex items-center justify-center bg-slate-950 max-h-[75vh] overflow-auto">
          <img :src="lightboxSrc" class="max-w-full max-h-[70vh] object-contain rounded-lg shadow-lg" alt="Profile Picture" />
        </div>
      </div>
    </div>

    <!-- Modals -->
    <CustomerModalSimple
      v-if="showCreateModal || showEditModal"
      :show="showCreateModal || showEditModal"
      :customer="selectedCustomer"
      :is-edit="showEditModal"
      @close="closeModal"
      @saved="handleCustomerSaved"
    />

    <CustomerViewModalSimple
      v-if="showViewModal"
      :show="showViewModal"
      :customer="selectedCustomer"
      @close="closeModal"
    />

    <CustomerLedger
      v-if="showLedgerModal"
      :show="showLedgerModal"
      :customer="selectedCustomer"
      @close="closeModal"
    />
  </div>
</template>

<script>
import { ref, onMounted, computed, onUnmounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useCurrencyStore } from '@/stores/currency';
import { debounce } from '@/utils/debounce';
import CustomerModalSimple from './CustomerModalSimple.vue';
import CustomerViewModalSimple from './CustomerViewModalSimple.vue';
import CustomerLedger from './CustomerLedger.vue';
import { useToast } from '@/composables/useToast';
import { downloadAttachmentFile } from '@/utils/downloadAttachment';
import api from '@/services/api';

export default {
  name: 'Customers',
  components: {
    CustomerModalSimple,
    CustomerViewModalSimple,
    CustomerLedger
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const { showToast } = useToast();
    const authStore = useAuthStore();
    const currencyStore = useCurrencyStore();

    const currencySymbol = computed(() => {
      return currencyStore.symbol || authStore.user?.company?.currency_symbol || authStore.user?.company?.currency || '$';
    });

    const loading = ref(false);
    const customers = ref({ data: [], current_page: 1, last_page: 1, total: 0 });
    const statistics = ref({});
    const searchQuery = ref('');
    const perPage = ref(15);
    const activeTab = ref('all');
    const typeFilter = ref('');
    const statusFilter = ref('');
    const openActionDropdown = ref(null);
    const viewMode = ref('table');

    // Lightbox State
    const showLightbox = ref(false);
    const lightboxSrc = ref('');
    const lightboxTitle = ref('');

    const checkAutoOpenCreate = () => {
      if (route.path.endsWith('/create') || route.query.create === 'true' || route.query.action === 'create') {
        selectedCustomer.value = null;
        showCreateModal.value = true;
      }
    };

    const setTab = (tab) => {
      activeTab.value = tab;
      if (tab === 'registered') {
        typeFilter.value = 'registered';
        statusFilter.value = '';
      } else if (tab === 'walk_in') {
        typeFilter.value = 'walk_in';
        statusFilter.value = '';
      } else if (tab === 'active') {
        typeFilter.value = '';
        statusFilter.value = '1';
      } else if (tab === 'inactive') {
        typeFilter.value = '';
        statusFilter.value = '0';
      } else {
        typeFilter.value = '';
        statusFilter.value = '';
      }
      loadCustomers(1);
    };

    const selectedCustomer = ref(null);
    const showCreateModal = ref(false);
    const showEditModal = ref(false);
    const showViewModal = ref(false);
    const showLedgerModal = ref(false);

    const loadCustomers = async (page = 1) => {
      loading.value = true;
      try {
        const params = { page, per_page: perPage.value };
        if (searchQuery.value) params.search = searchQuery.value;
        if (typeFilter.value !== '') params.type = typeFilter.value;
        if (statusFilter.value !== '') params.is_active = statusFilter.value;

        const response = await api.get('/customers', { params });
        customers.value = response.data;
      } catch (error) {
        showToast('Error loading customers: ' + (error.response?.data?.message || error.message), 'error');
      } finally {
        loading.value = false;
      }
    };

    const loadStatistics = async () => {
      try {
        const response = await api.get('/customers/statistics');
        statistics.value = response.data;
      } catch (error) {
        console.error('Error loading statistics:', error);
      }
    };

    const debouncedSearch = debounce(() => { loadCustomers(1); }, 300);

    const changePage = (page) => {
      if (page >= 1 && page <= customers.value.last_page) {
        loadCustomers(page);
      }
    };

    const visiblePages = computed(() => {
      const current = customers.value.current_page;
      const last = customers.value.last_page;
      const pages = [];
      for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
        pages.push(i);
      }
      return pages;
    });

    const toggleActionDropdown = (id) => {
      openActionDropdown.value = openActionDropdown.value === id ? null : id;
    };

    const closeAllDropdowns = () => { openActionDropdown.value = null; };

    const handleCreateCustomer = () => {
      selectedCustomer.value = null;
      showCreateModal.value = true;
    };

    const viewCustomer = (customer) => {
      selectedCustomer.value = customer;
      showViewModal.value = true;
      openActionDropdown.value = null;
    };

    const editCustomer = (customer) => {
      selectedCustomer.value = { ...customer };
      showEditModal.value = true;
      openActionDropdown.value = null;
    };

    const viewLedger = (customer) => {
      selectedCustomer.value = customer;
      showLedgerModal.value = true;
      openActionDropdown.value = null;
    };

    const closeModal = () => {
      showCreateModal.value = false;
      showEditModal.value = false;
      showViewModal.value = false;
      showLedgerModal.value = false;
      selectedCustomer.value = null;
      if (route.path.endsWith('/create')) {
        router.replace('/customers');
      }
    };

    const handleCustomerSaved = () => {
      loadCustomers(customers.value.current_page);
      loadStatistics();
      closeModal();
    };

    const deleteCustomer = async (customer) => {
      openActionDropdown.value = null;
      if (confirm(`Are you sure you want to delete customer "${customer.name}"?`)) {
        try {
          await api.delete(`/customers/${customer.id}`);
          showToast('Customer deleted successfully', 'success');
          loadCustomers(customers.value.current_page);
          loadStatistics();
        } catch (error) {
          showToast(error.response?.data?.message || 'Error deleting customer', 'error');
        }
      }
    };

    const formatNumber = (value) => new Intl.NumberFormat().format(value || 0);

    const getInitials = (name) => {
      if (!name) return '?';
      return name.split(' ').map(w => w.charAt(0).toUpperCase()).slice(0, 2).join('');
    };

    const getStorageUrl = (path) => {
      if (!path) return '';
      if (path.startsWith('http') || path.startsWith('/storage/')) return path;
      return '/storage/' + path;
    };

    const openLightbox = (url, title) => {
      if (!url) return;
      lightboxSrc.value = url;
      lightboxTitle.value = title || 'Profile Photo';
      showLightbox.value = true;
    };

    const getAttachmentItems = (item) => {
      if (item.attachments_urls && item.attachments_urls.length > 0) {
        return item.attachments_urls;
      }
      if (!item.attachments || !Array.isArray(item.attachments)) return [];
      return item.attachments.map((path, idx) => ({
        index: idx,
        url: path.startsWith('http') || path.startsWith('/storage/') ? path : '/storage/' + path,
        path: path,
        filename: path.split('/').pop() || `Attachment ${idx + 1}`
      }));
    };

    const downloadFile = (url, filename) => {
      downloadAttachmentFile(url, filename || 'attachment');
    };

    onMounted(() => {
      loadCustomers();
      loadStatistics();
      document.addEventListener('click', closeAllDropdowns);
      checkAutoOpenCreate();
    });

    watch(() => route.path, () => {
      checkAutoOpenCreate();
    });

    watch(() => route.query, () => {
      checkAutoOpenCreate();
    });

    onUnmounted(() => {
      document.removeEventListener('click', closeAllDropdowns);
    });

    return {
      currencySymbol,
      loading, customers, statistics, searchQuery, perPage, activeTab, typeFilter, statusFilter, openActionDropdown,
      selectedCustomer, showCreateModal, showEditModal, showViewModal, showLedgerModal, viewMode,
      showLightbox, lightboxSrc, lightboxTitle,
      visiblePages, loadCustomers, setTab, debouncedSearch, changePage, toggleActionDropdown,
      handleCreateCustomer, viewCustomer, editCustomer, viewLedger, deleteCustomer,
      closeModal, handleCustomerSaved, formatNumber, getInitials,
      getStorageUrl, openLightbox, getAttachmentItems, downloadFile
    };
  }
};
</script>

<style scoped>
.shadow-soft {
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03);
}
.animate-fade-in {
  animation: fadeIn 0.15s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
</style>
