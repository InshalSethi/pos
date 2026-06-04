<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">Settings</h1>

      <!-- Tab Navigation -->
      <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
          <button
            @click="activeTab = 'general'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === 'general'
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            General Settings
          </button>
          <button
            v-if="authStore.hasPermission('users.view')"
            @click="activeTab = 'users'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === 'users'
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Users
          </button>
          <button
            v-if="authStore.hasPermission('roles.view')"
            @click="activeTab = 'roles'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === 'roles'
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Roles
          </button>
          <button
            v-if="authStore.hasPermission('settings.payment_gateways')"
            @click="activeTab = 'payments'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === 'payments'
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Payment Gateways
          </button>
          <button
            @click="activeTab = 'accounting'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === 'accounting'
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Accounting
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div class="bg-white shadow rounded-lg">
        <!-- General Settings Tab -->
        <div v-if="activeTab === 'general'" class="p-6">

          <div class="space-y-8">
            <!-- Header -->
            <div class="border-b border-gray-200 pb-4">
              <h3 class="text-lg font-medium text-gray-900">General Settings</h3>
              <p class="text-sm text-gray-600">Manage your application preferences and account settings</p>
            </div>

            <!-- Notification Settings -->
            <div>
              <h4 class="text-md font-medium text-gray-900 mb-4">Notifications</h4>
              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <div>
                    <label class="text-sm font-medium text-gray-700">Email Notifications</label>
                    <p class="text-sm text-gray-500">Receive email notifications for important updates</p>
                  </div>
                  <button
                    @click="toggleSetting('email_notifications')"
                    :class="[
                      'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                      settings.email_notifications ? 'bg-indigo-600' : 'bg-gray-200'
                    ]"
                  >
                    <span
                      :class="[
                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                        settings.email_notifications ? 'translate-x-5' : 'translate-x-0'
                      ]"
                    ></span>
                  </button>
                </div>

              <div class="flex items-center justify-between">
                <div>
                  <label class="text-sm font-medium text-gray-700">Sales Alerts</label>
                  <p class="text-sm text-gray-500">Get notified when sales are completed</p>
                </div>
                <button
                  @click="toggleSetting('sales_alerts')"
                  :class="[
                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                    settings.sales_alerts ? 'bg-indigo-600' : 'bg-gray-200'
                  ]"
                >
                  <span
                    :class="[
                      'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                      settings.sales_alerts ? 'translate-x-5' : 'translate-x-0'
                    ]"
                  ></span>
                </button>
              </div>

              <div class="flex items-center justify-between">
                <div>
                  <label class="text-sm font-medium text-gray-700">Low Stock Alerts</label>
                  <p class="text-sm text-gray-500">Receive alerts when products are running low</p>
                </div>
                <button
                  @click="toggleSetting('low_stock_alerts')"
                  :class="[
                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                    settings.low_stock_alerts ? 'bg-indigo-600' : 'bg-gray-200'
                  ]"
                >
                  <span
                    :class="[
                      'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                      settings.low_stock_alerts ? 'translate-x-5' : 'translate-x-0'
                    ]"
                  ></span>
                </button>
              </div>
            </div>
          </div>

          <!-- Display Settings -->
          <div class="border-t border-gray-200 pt-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Display</h3>
            <div class="space-y-4">
              <div class="relative max-w-xs">
                <label class="block text-sm font-medium text-gray-700 mb-2">Theme</label>
                <Listbox
                  v-model="settings.theme"
                  @update:modelValue="updateSetting('theme', settings.theme)"
                >
                  <div class="relative mt-1">
                    <ListboxButton
                      class="relative w-full cursor-default rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-left focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-gray-900"
                    >
                      <span class="block truncate capitalize">{{ settings.theme }}</span>
                      <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                          <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                      </span>
                    </ListboxButton>
                    <transition
                      leave-active-class="transition duration-100 ease-in"
                      leave-from-class="opacity-100"
                      leave-to-class="opacity-0"
                    >
                      <ListboxOptions
                        class="absolute bottom-full mb-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm z-50"
                      >
                        <ListboxOption
                          v-slot="{ active, selected }"
                          v-for="themeOption in ['light', 'dark', 'match system']"
                          :key="themeOption"
                          :value="themeOption"
                          as="template"
                        >
                          <li
                            :class="[
                              active ? 'bg-indigo-100 text-indigo-900' : 'text-gray-900',
                              'relative cursor-default select-none py-2 pl-10 pr-4'
                            ]"
                          >
                            <span
                              :class="[
                                selected ? 'font-medium' : 'font-normal',
                                'block truncate capitalize'
                              ]"
                            >
                              {{ themeOption }}
                            </span>
                            <span
                              v-if="selected"
                              :class="[
                                active ? 'text-indigo-600' : 'text-indigo-600',
                                'absolute inset-y-0 left-0 flex items-center pl-3'
                              ]"
                            >
                              <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                              </svg>
                            </span>
                          </li>
                        </ListboxOption>
                      </ListboxOptions>
                    </transition>
                  </div>
                </Listbox>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Items per page</label>
                <select
                  v-model="settings.items_per_page"
                  @change="updateSetting('items_per_page', settings.items_per_page)"
                  class="w-full max-w-xs px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="10">10</option>
                  <option value="15">15</option>
                  <option value="25">25</option>
                  <option value="50">50</option>
                </select>
              </div>
            </div>
          </div>

          <!-- POS Settings -->
          <div class="border-t border-gray-200 pt-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Point of Sale</h3>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Default Payment Method</label>
                <select
                  v-model="settings.default_payment_method"
                  @change="updateSetting('default_payment_method', settings.default_payment_method)"
                  class="w-full max-w-xs px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="cash">Cash</option>
                  <option value="card">Card</option>
                  <option value="digital">Digital Wallet</option>
                </select>
              </div>

              <div class="flex items-center justify-between">
                <div>
                  <label class="text-sm font-medium text-gray-700">Auto-print receipts</label>
                  <p class="text-sm text-gray-500">Automatically print receipts after each sale</p>
                </div>
                <button
                  @click="toggleSetting('auto_print_receipts')"
                  :class="[
                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                    settings.auto_print_receipts ? 'bg-indigo-600' : 'bg-gray-200'
                  ]"
                >
                  <span
                    :class="[
                      'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                      settings.auto_print_receipts ? 'translate-x-5' : 'translate-x-0'
                    ]"
                  ></span>
                </button>
              </div>

              <div class="flex items-center justify-between">
                <div>
                  <label class="text-sm font-medium text-gray-700">Sound Effects</label>
                  <p class="text-sm text-gray-500">Play sounds for POS actions</p>
                </div>
                <button
                  @click="toggleSetting('sound_effects')"
                  :class="[
                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                    settings.sound_effects ? 'bg-indigo-600' : 'bg-gray-200'
                  ]"
                >
                  <span
                    :class="[
                      'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                      settings.sound_effects ? 'translate-x-5' : 'translate-x-0'
                    ]"
                  ></span>
                </button>
              </div>
            </div>
          </div>

          <!-- Security Settings -->
          <div class="border-t border-gray-200 pt-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Security</h3>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Session Timeout (minutes)</label>
                <select
                  v-model="settings.session_timeout"
                  @change="updateSetting('session_timeout', settings.session_timeout)"
                  class="w-full max-w-xs px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="15">15 minutes</option>
                  <option value="30">30 minutes</option>
                  <option value="60">1 hour</option>
                  <option value="120">2 hours</option>
                  <option value="480">8 hours</option>
                </select>
              </div>

              <div class="flex items-center justify-between">
                <div>
                  <label class="text-sm font-medium text-gray-700">Two-Factor Authentication</label>
                  <p class="text-sm text-gray-500">Add an extra layer of security to your account</p>
                </div>
                <button
                  @click="toggleSetting('two_factor_auth')"
                  :class="[
                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                    settings.two_factor_auth ? 'bg-indigo-600' : 'bg-gray-200'
                  ]"
                >
                  <span
                    :class="[
                      'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                      settings.two_factor_auth ? 'translate-x-5' : 'translate-x-0'
                    ]"
                  ></span>
                </button>
              </div>
            </div>
          </div>

          <!-- Currency Settings -->
          <div class="border-t border-gray-200 pt-8">
            <h3 class="text-lg font-medium text-gray-900 mb-1">System Currency</h3>
            <p class="text-sm text-gray-500 mb-4">Set the display currency for all prices across the platform. Exchange rates are relative to USD.</p>

            <div v-if="currencyStore.loading" class="flex items-center space-x-2 text-gray-500 text-sm">
              <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              <span>Loading currencies...</span>
            </div>

            <div v-else class="space-y-4">
              <!-- Currency Selector Card -->
              <div class="max-w-sm">
                <div class="relative">
                  <select
                    id="system-currency-select"
                    v-model="selectedCurrencyId"
                    @change="handleCurrencyChange"
                    class="w-full pl-4 pr-10 py-3 bg-gradient-to-r from-indigo-50 to-purple-50 border-2 border-indigo-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-semibold text-gray-800 appearance-none cursor-pointer shadow-sm transition-all hover:border-indigo-300"
                  >
                    <option value="" disabled>Select a currency...</option>
                    <option
                      v-for="currency in currencyStore.currencies"
                      :key="currency.id"
                      :value="currency.id"
                    >
                      {{ currency.symbol }} {{ currency.code }} — {{ currency.name }}
                    </option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                  </div>
                </div>
              </div>

              <!-- Active Currency Preview Card -->
              <div v-if="currencyStore.activeCurrency" class="max-w-sm bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl p-5 text-white shadow-lg">
                <div class="flex items-center justify-between mb-3">
                  <span class="text-indigo-200 text-xs font-bold uppercase tracking-widest">Active Currency</span>
                  <span class="bg-white/20 text-white text-xs font-bold px-2 py-1 rounded-full">LIVE</span>
                </div>
                <div class="flex items-end space-x-3">
                  <div class="text-5xl font-black tracking-tight">{{ currencyStore.activeCurrency.symbol }}</div>
                  <div>
                    <div class="text-xl font-bold">{{ currencyStore.activeCurrency.code }}</div>
                    <div class="text-indigo-200 text-sm">{{ currencyStore.activeCurrency.name }}</div>
                  </div>
                </div>
                <div class="mt-4 pt-4 border-t border-white/20 text-sm text-indigo-200">
                  <span class="font-medium">Exchange Rate:</span>
                  1 USD = {{ currencyStore.activeCurrency.exchange_rate }} {{ currencyStore.activeCurrency.code }}
                </div>
                <!-- Live preview -->
                <div class="mt-3 text-xs text-indigo-300">
                  Preview: $100 USD → {{ currencyStore.formatPrice(100) }}
                </div>
              </div>

              <!-- Save feedback -->
              <div v-if="currencySaveStatus === 'saving'" class="flex items-center space-x-2 text-indigo-600 text-sm">
                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <span>Saving...</span>
              </div>
              <div v-else-if="currencySaveStatus === 'saved'" class="flex items-center space-x-2 text-green-600 text-sm font-medium">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Currency updated globally!</span>
              </div>
              <div v-else-if="currencySaveStatus === 'error'" class="flex items-center space-x-2 text-red-600 text-sm font-medium">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>Failed to update currency. Please try again.</span>
              </div>
            </div>
          </div>

          <!-- Timezone Settings -->
          <div class="border-t border-gray-200 pt-8">
            <h3 class="text-lg font-medium text-gray-900 mb-1">System Timezone</h3>
            <p class="text-sm text-gray-500 mb-4">Set the global system timezone configuration for all dynamic date and time displays.</p>

            <div class="space-y-4">
              <!-- Timezone Selector Card -->
              <div class="max-w-sm">
                <div class="relative">
                  <select
                    id="system-timezone-select"
                    v-model="selectedTimezone"
                    @change="handleTimezoneChange"
                    class="w-full pl-4 pr-10 py-3 bg-gradient-to-r from-indigo-50 to-purple-50 border-2 border-indigo-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-semibold text-gray-800 appearance-none cursor-pointer shadow-sm transition-all hover:border-indigo-300"
                  >
                    <option value="" disabled>Select a timezone...</option>
                    <option
                      v-for="tz in timezones"
                      :key="tz"
                      :value="tz"
                    >
                      {{ tz }}
                    </option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                  </div>
                </div>
              </div>

              <!-- Save feedback -->
              <div v-if="timezoneSaveStatus === 'saving'" class="flex items-center space-x-2 text-indigo-600 text-sm">
                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <span>Saving timezone...</span>
              </div>
              <div v-else-if="timezoneSaveStatus === 'saved'" class="flex items-center space-x-2 text-green-600 text-sm font-medium">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Timezone updated globally!</span>
              </div>
              <div v-else-if="timezoneSaveStatus === 'error'" class="flex items-center space-x-2 text-red-600 text-sm font-medium">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>Failed to update timezone. Please try again.</span>
              </div>
            </div>
          </div>

          <!-- Save Button -->
          <div class="border-t border-gray-200 pt-8">
            <div class="flex justify-end">
              <button
                id="save-settings-btn"
                @click="saveAllSettings"
                :disabled="saving"
                class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center space-x-2"
              >
                <svg v-if="saving" class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span>{{ saving ? 'Saving...' : 'Save Settings' }}</span>
              </button>
            </div>
          </div>
          </div>
        </div>

        <!-- Users Tab -->
        <div v-else-if="activeTab === 'users'" class="p-6 bg-gray-50 min-h-screen">
          <!-- Statistics Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Total Users</p>
                  <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(userStatistics.total_users || 0) }}</p>
                </div>
              </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Active Users</p>
                  <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(userStatistics.active_users || 0) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Filters and Search -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search Users</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                  </div>
                  <input
                    v-model="userSearchQuery"
                    type="text"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white"
                    placeholder="Search by name, email..."
                    @input="debouncedUserSearch"
                  />
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                <select
                  v-model="userRoleFilter"
                  @change="loadUsers(1)"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white"
                >
                  <option value="">All Roles</option>
                  <option v-for="role in roles" :key="role.id" :value="role.name">
                    {{ role.name }}
                  </option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select
                  v-model="userStatusFilter"
                  @change="loadUsers(1)"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white"
                >
                  <option value="">All Status</option>
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Per Page</label>
                <select
                  v-model="userPerPage"
                  @change="handleUserPerPageChange"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white"
                >
                  <option :value="15">15 per page</option>
                  <option :value="25">25 per page</option>
                  <option :value="50">50 per page</option>
                </select>
              </div>
            </div>
          </div>

          <!-- User Cards/Table -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <!-- Desktop Table View -->
            <div class="hidden lg:block">
              <DataTable
                title="Users"
                subtitle="Manage your team and application users"
                :columns="userTableColumns"
                :data="users.data || []"
                :loading="loadingUsers"
                :pagination="userPagination"
                :initial-search="userSearchQuery"
                :initial-per-page="userPerPage"
                :default-per-page="15"
                storage-key="users-table-state"
                empty-message="No users found"
                empty-sub-message="Get started by creating your first user."
                @search="handleTableSearch"
                @sort="handleSort"
                @page-change="handlePageChange"
                @per-page-change="handleUserPerPageChange"
              >
                <!-- Custom column content -->
                <template #column-user="{ item }">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                        <span class="text-sm font-medium text-indigo-600">{{ getInitials(item.name) }}</span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">
                        {{ item.name }}
                      </div>
                      <div class="text-sm text-gray-500">ID: #{{ item.id }}</div>
                    </div>
                  </div>
                </template>

                <template #column-contact="{ item }">
                  <div>
                    <div class="text-sm text-gray-900">{{ item.email || '-' }}</div>
                    <div class="text-sm text-gray-500">{{ item.phone || '-' }}</div>
                  </div>
                </template>

                <template #column-role="{ item }">
                  <div class="flex flex-wrap gap-1">
                    <span v-for="r in item.roles" :key="r.id" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                      {{ r.name }}
                    </span>
                  </div>
                </template>

                <template #column-status="{ item }">
                  <span :class="item.is_active
                    ? 'inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800'
                    : 'inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800'">
                    {{ item.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </template>

                <template #column-actions="{ item }">
                  <div class="flex items-center justify-end space-x-2">
                    <button
                      @click="viewUser(item)"
                      class="text-blue-600 hover:text-blue-900 p-1 rounded-md hover:bg-blue-50"
                      title="View Details"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                    <button
                      @click="editUser(item)"
                      class="text-yellow-600 hover:text-yellow-900 p-1 rounded-md hover:bg-yellow-50"
                      title="Edit User"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button
                      @click="deleteUser(item.id)"
                      class="text-red-600 hover:text-red-900 p-1 rounded-md hover:bg-red-50"
                      title="Delete User"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </template>

                <!-- Action buttons in header -->
                <template #actions>
                  <button
                    @click="handleCreateUser"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200"
                  >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add User
                  </button>
                </template>
              </DataTable>
            </div>

            <!-- Mobile Card View -->
            <div class="lg:hidden">
              <div class="space-y-4 p-4">
                <div
                  v-for="item in users.data"
                  :key="item.id"
                  class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm"
                >
                  <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3">
                      <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                          <span class="text-sm font-medium text-indigo-600">{{ getInitials(item.name) }}</span>
                        </div>
                      </div>
                      <div>
                        <h3 class="text-sm font-medium text-gray-900">{{ item.name }}</h3>
                        <p class="text-sm text-gray-500">{{ item.email || 'No email' }}</p>
                      </div>
                    </div>
                    <span :class="item.is_active
                      ? 'inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800'
                      : 'inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800'">
                      {{ item.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </div>

                  <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                    <div>
                      <span class="text-gray-500">Phone:</span>
                      <span class="ml-1 text-gray-900">{{ item.phone || '-' }}</span>
                    </div>
                    <div>
                      <span class="text-gray-500">Role:</span>
                      <span v-for="r in item.roles" :key="r.id" class="ml-1 text-gray-900 capitalize">{{ r.name }}</span>
                    </div>
                  </div>

                  <div class="mt-4 flex justify-end space-x-2">
                    <button
                      @click="viewUser(item)"
                      class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100"
                    >
                      View
                    </button>
                    <button
                      @click="editUser(item)"
                      class="inline-flex items-center px-3 py-1 text-xs font-medium text-yellow-600 bg-yellow-50 rounded-md hover:bg-yellow-100"
                    >
                      Edit
                    </button>
                    <button
                      @click="deleteUser(item.id)"
                      class="inline-flex items-center px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100"
                    >
                      Delete
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination (Mobile/Fallback) -->
            <div v-if="users.last_page > 1" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 lg:hidden">
              <div class="flex items-center justify-between">
                <div class="flex-1 flex justify-between">
                  <button
                    @click="changeUserPage(users.current_page - 1)"
                    :disabled="users.current_page === 1"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Previous
                  </button>
                  <button
                    @click="changeUserPage(users.current_page + 1)"
                    :disabled="users.current_page === users.last_page"
                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Next
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Roles Tab -->
        <div v-else-if="activeTab === 'roles'" class="p-6 bg-gray-50 min-h-screen">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-gray-900">Role Management</h3>
            <button
              @click="handleCreateRole"
              class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Add Role
            </button>
          </div>

          <!-- Search -->
          <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 p-4 max-w-md">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input
                v-model="roleSearchQuery"
                type="text"
                placeholder="Search roles..."
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white text-gray-900"
              />
            </div>
          </div>

          <!-- Roles List -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <ul class="divide-y divide-gray-200">
              <li v-for="role in filteredSettingsRoles" :key="role.id" class="px-6 py-4 hover:bg-gray-50 transition-colors duration-150">
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <div class="flex items-center">
                      <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                          <span class="text-indigo-600 font-bold text-lg">{{ role.name.charAt(0).toUpperCase() }}</span>
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-semibold text-gray-900 capitalize">{{ role.name }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">
                          {{ role.permissions ? role.permissions.length : 0 }} permissions assigned
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="flex items-center space-x-3">
                    <button
                      @click="editRole(role)"
                      class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors"
                    >
                      Edit
                    </button>
                    <button
                      @click="deleteRole(role.id, role.name)"
                      class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-red-600 bg-red-50 hover:bg-red-100 transition-colors"
                    >
                      Delete
                    </button>
                  </div>
                </div>
              </li>
            </ul>
            <!-- Empty State -->
            <div v-if="filteredSettingsRoles.length === 0" class="text-center py-8">
              <p class="text-gray-500">No roles found.</p>
            </div>
          </div>
        </div>

        <!-- Payment Gateways Tab -->
        <div v-else-if="activeTab === 'payments'" class="p-6">
          <div class="space-y-8">
            <div class="border-b border-gray-200 pb-4">
              <h3 class="text-lg font-medium text-gray-900">Payment Gateway Settings</h3>
              <p class="text-sm text-gray-600">Configure payment gateways for your POS system</p>
            </div>

            <!-- Stripe Settings -->
            <div class="bg-gray-50 rounded-lg p-6">
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                  <img src="/images/stripe-logo.png" alt="Stripe" class="h-8 w-auto mr-3" />
                  <div>
                    <h4 class="text-md font-medium text-gray-900">Stripe</h4>
                    <p class="text-sm text-gray-600">Accept credit cards and digital payments</p>
                  </div>
                </div>
                <button
                  @click="paymentSettings.stripe_enabled = !paymentSettings.stripe_enabled; updatePaymentSetting('stripe_enabled', paymentSettings.stripe_enabled)"
                  :class="[
                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                    paymentSettings.stripe_enabled ? 'bg-indigo-600' : 'bg-gray-200'
                  ]"
                >
                  <span
                    :class="[
                      'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                      paymentSettings.stripe_enabled ? 'translate-x-5' : 'translate-x-0'
                    ]"
                  ></span>
                </button>
              </div>

              <div v-if="paymentSettings.stripe_enabled" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Publishable Key</label>
                  <input
                    v-model="paymentSettings.stripe_public_key"
                    @blur="updatePaymentSetting('stripe_public_key', paymentSettings.stripe_public_key)"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="pk_test_..."
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Secret Key</label>
                  <input
                    v-model="paymentSettings.stripe_secret_key"
                    @blur="updatePaymentSetting('stripe_secret_key', paymentSettings.stripe_secret_key)"
                    type="password"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="sk_test_..."
                  />
                </div>
              </div>
            </div>

            <!-- Square Settings -->
            <div class="bg-gray-50 rounded-lg p-6">
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                  <div class="h-8 w-8 bg-black rounded mr-3 flex items-center justify-center">
                    <span class="text-white text-xs font-bold">SQ</span>
                  </div>
                  <div>
                    <h4 class="text-md font-medium text-gray-900">Square</h4>
                    <p class="text-sm text-gray-600">Accept payments with Square</p>
                  </div>
                </div>
                <button
                  @click="paymentSettings.square_enabled = !paymentSettings.square_enabled; updatePaymentSetting('square_enabled', paymentSettings.square_enabled)"
                  :class="[
                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                    paymentSettings.square_enabled ? 'bg-indigo-600' : 'bg-gray-200'
                  ]"
                >
                  <span
                    :class="[
                      'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                      paymentSettings.square_enabled ? 'translate-x-5' : 'translate-x-0'
                    ]"
                  ></span>
                </button>
              </div>

              <div v-if="paymentSettings.square_enabled" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Application ID</label>
                  <input
                    v-model="paymentSettings.square_application_id"
                    @blur="updatePaymentSetting('square_application_id', paymentSettings.square_application_id)"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="sandbox-sq0idb-..."
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Access Token</label>
                  <input
                    v-model="paymentSettings.square_access_token"
                    @blur="updatePaymentSetting('square_access_token', paymentSettings.square_access_token)"
                    type="password"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="EAAAEOuLQOGg..."
                  />
                </div>
              </div>
            </div>

            <!-- Google Pay Settings -->
            <div class="bg-gray-50 rounded-lg p-6">
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                  <div class="h-8 w-8 bg-blue-500 rounded mr-3 flex items-center justify-center">
                    <span class="text-white text-xs font-bold">GP</span>
                  </div>
                  <div>
                    <h4 class="text-md font-medium text-gray-900">Google Pay</h4>
                    <p class="text-sm text-gray-600">Accept payments with Google Pay</p>
                  </div>
                </div>
                <button
                  @click="paymentSettings.googlepay_enabled = !paymentSettings.googlepay_enabled; updatePaymentSetting('googlepay_enabled', paymentSettings.googlepay_enabled)"
                  :class="[
                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                    paymentSettings.googlepay_enabled ? 'bg-indigo-600' : 'bg-gray-200'
                  ]"
                >
                  <span
                    :class="[
                      'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                      paymentSettings.googlepay_enabled ? 'translate-x-5' : 'translate-x-0'
                    ]"
                  ></span>
                </button>
              </div>

              <div v-if="paymentSettings.googlepay_enabled" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Merchant ID</label>
                  <input
                    v-model="paymentSettings.googlepay_merchant_id"
                    @blur="updatePaymentSetting('googlepay_merchant_id', paymentSettings.googlepay_merchant_id)"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="BCR2DN4T..."
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Accounting Tab -->
        <div v-else-if="activeTab === 'accounting'" class="p-6">
          <div class="space-y-8">
            <div class="border-b border-gray-200 pb-4">
              <h3 class="text-lg font-medium text-gray-900">Accounting Settings</h3>
              <p class="text-sm text-gray-600">Configure chart of accounts for double entry accounting</p>
              <p class="text-xs text-blue-600 mt-2">Debug: Tab is active, loading={{ loadingAccounting }}</p>
            </div>

            <div v-if="loadingAccounting" class="text-center py-8">
              <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
              <p class="mt-2 text-gray-600">Loading accounting settings...</p>
            </div>

            <div v-else class="space-y-8">
              <!-- Sales Invoice Accounts -->
              <div class="bg-green-50 p-6 rounded-lg">
                <h4 class="text-lg font-medium text-green-900 mb-4">Sales Invoice Accounts</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Revenue Account</label>
                    <select
                      v-model="accountingSettings.sales_invoice_revenue_account_id"
                      @change="updateAccountingSetting"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                      <option value="">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="account.id">
                          {{ account.display_name }}
                        </option>
                      </optgroup>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Accounts Receivable</label>
                    <select
                      v-model="accountingSettings.sales_invoice_receivable_account_id"
                      @change="updateAccountingSetting"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                      <option value="">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="account.id">
                          {{ account.display_name }}
                        </option>
                      </optgroup>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tax Account</label>
                    <select
                      v-model="accountingSettings.sales_invoice_tax_account_id"
                      @change="updateAccountingSetting"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                      <option value="">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="account.id">
                          {{ account.display_name }}
                        </option>
                      </optgroup>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Sales Return Accounts -->
              <div class="bg-red-50 p-6 rounded-lg">
                <h4 class="text-lg font-medium text-red-900 mb-4">Sales Return Accounts</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Revenue Return Account</label>
                    <select
                      v-model="accountingSettings.sales_return_revenue_account_id"
                      @change="updateAccountingSetting"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                      <option value="">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="account.id">
                          {{ account.display_name }}
                        </option>
                      </optgroup>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Accounts Receivable</label>
                    <select
                      v-model="accountingSettings.sales_return_receivable_account_id"
                      @change="updateAccountingSetting"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                      <option value="">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="account.id">
                          {{ account.display_name }}
                        </option>
                      </optgroup>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tax Account</label>
                    <select
                      v-model="accountingSettings.sales_return_tax_account_id"
                      @change="updateAccountingSetting"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                      <option value="">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="account.id">
                          {{ account.display_name }}
                        </option>
                      </optgroup>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Purchase Invoice Accounts -->
              <div class="bg-orange-50 p-6 rounded-lg">
                <h4 class="text-lg font-medium text-orange-900 mb-4">Purchase Invoice Accounts</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Expense Account</label>
                    <select
                      v-model="accountingSettings.purchase_invoice_expense_account_id"
                      @change="updateAccountingSetting"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                      <option value="">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="account.id">
                          {{ account.display_name }}
                        </option>
                      </optgroup>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Accounts Payable</label>
                    <select
                      v-model="accountingSettings.purchase_invoice_payable_account_id"
                      @change="updateAccountingSetting"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                      <option value="">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="account.id">
                          {{ account.display_name }}
                        </option>
                      </optgroup>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tax Account</label>
                    <select
                      v-model="accountingSettings.purchase_invoice_tax_account_id"
                      @change="updateAccountingSetting"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                      <option value="">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="account.id">
                          {{ account.display_name }}
                        </option>
                      </optgroup>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Purchase Return Accounts -->
              <div class="bg-purple-50 p-6 rounded-lg">
                <h4 class="text-lg font-medium text-purple-900 mb-4">Purchase Return Accounts</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Expense Return Account</label>
                    <select
                      v-model="accountingSettings.purchase_return_expense_account_id"
                      @change="updateAccountingSetting"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                      <option value="">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="account.id">
                          {{ account.display_name }}
                        </option>
                      </optgroup>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Accounts Payable</label>
                    <select
                      v-model="accountingSettings.purchase_return_payable_account_id"
                      @change="updateAccountingSetting"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                      <option value="">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="account.id">
                          {{ account.display_name }}
                        </option>
                      </optgroup>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tax Account</label>
                    <select
                      v-model="accountingSettings.purchase_return_tax_account_id"
                      @change="updateAccountingSetting"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                      <option value="">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="account.id">
                          {{ account.display_name }}
                        </option>
                      </optgroup>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Expense Accounts -->
              <div class="bg-yellow-50 p-6 rounded-lg">
                <h4 class="text-lg font-medium text-yellow-900 mb-4">Expense Accounts</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Default Expense Account</label>
                    <select
                      v-model="accountingSettings.expense_default_account_id"
                      @change="updateAccountingSetting"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                      <option value="">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="account.id">
                          {{ account.display_name }}
                        </option>
                      </optgroup>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Expense Payable Account</label>
                    <select
                      v-model="accountingSettings.expense_payable_account_id"
                      @change="updateAccountingSetting"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                      <option value="">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="account.id">
                          {{ account.display_name }}
                        </option>
                      </optgroup>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Save Button -->
              <div class="flex justify-end">
                <button
                  @click="saveAccountingSettings"
                  :disabled="savingAccounting"
                  class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 disabled:opacity-50"
                >
                  {{ savingAccounting ? 'Saving...' : 'Save Accounting Settings' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- User Create/Edit Modal -->
  <UserCreateForm
    :show="showUserCreateModal || showUserEditModal"
    :user="editingUser"
    :is-edit="showUserEditModal"
    :roles-list="roles"
    @close="closeUserModal"
    @saved="handleUserSaved"
  />
  
  <!-- User View Modal -->
  <UserViewModal
    :show="showUserViewModal"
    :user="editingUser"
    @close="closeUserModal"
  />

  <!-- Role Create/Edit Modal -->
  <RoleCreateForm
    :show="showRoleModal"
    :role="editingRole"
    :is-edit="!!editingRole"
    @close="closeRoleModal"
    @saved="handleRoleSaved"
  />
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { Listbox, ListboxButton, ListboxOptions, ListboxOption } from '@headlessui/vue';
import { useAuthStore } from '@/stores/auth';
import { useCurrencyStore } from '@/stores/currency';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import { debounce } from '@/utils/debounce';
import DataTable from '@/components/common/DataTable.vue';
import UserCreateForm from './UserCreateForm.vue';
import UserViewModal from './UserViewModal.vue';
import RoleCreateForm from './RoleCreateForm.vue';

const authStore = useAuthStore();
const currencyStore = useCurrencyStore();
const { showToast } = useToast();

// Reactive data
const activeTab = ref('general');
const saving = ref(false);

// Currency selector state
const selectedCurrencyId = ref(currencyStore.activeCurrencyId);
const currencySaveStatus = ref(''); // 'saving' | 'saved' | 'error' | ''

// Timezone selector state
const timezones = ref(window.systemTimezones || []);
const selectedTimezone = ref('Asia/Karachi');
const timezoneSaveStatus = ref(''); // 'saving' | 'saved' | 'error' | ''
const settings = ref({
  email_notifications: true,
  sales_alerts: true,
  low_stock_alerts: true,
  theme: localStorage.getItem('theme') || 'light',
  items_per_page: '15',
  default_payment_method: 'cash',
  auto_print_receipts: false,
  sound_effects: true,
  session_timeout: '60',
  two_factor_auth: false
});

// Users tab data and states
const users = ref({ data: [], current_page: 1, last_page: 1, total: 0 });
const roles = ref([]);
const loadingUsers = ref(false);

const userSearchQuery = ref('');
const userStatusFilter = ref('');
const userRoleFilter = ref('');
const userPerPage = ref(15);
const userFilters = ref({
  sort_field: 'name',
  sort_order: 'asc'
});

const userPagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
});

const userTableColumns = ref([
  {
    key: 'user',
    label: 'User',
    sortable: true,
    align: 'left'
  },
  {
    key: 'contact',
    label: 'Contact',
    sortable: false,
    align: 'left'
  },
  {
    key: 'role',
    label: 'Role',
    sortable: true,
    align: 'left'
  },
  {
    key: 'status',
    label: 'Status',
    sortable: true,
    align: 'center'
  },
  {
    key: 'actions',
    label: 'Actions',
    sortable: false,
    align: 'right'
  }
]);

const userStatistics = ref({
  total_users: 0,
  active_users: 0
});

const showUserCreateModal = ref(false);
const showUserEditModal = ref(false);
const showUserViewModal = ref(false);
const editingUser = ref(null);
const showRoleModal = ref(false);
const editingRole = ref(null);
const roleSearchQuery = ref('');

const systemRoles = ['admin', 'manager', 'cashier', 'user'];
const isSystemRole = (roleName) => {
  return systemRoles.includes(roleName?.toLowerCase());
};

const filteredSettingsRoles = computed(() => {
  if (!roleSearchQuery.value) return roles.value;
  return roles.value.filter(r => 
    r.name?.toLowerCase().includes(roleSearchQuery.value.toLowerCase())
  );
});

// Payment gateway settings
const paymentSettings = ref({
  stripe_enabled: false,
  stripe_public_key: '',
  stripe_secret_key: '',
  square_enabled: false,
  square_application_id: '',
  square_access_token: '',
  googlepay_enabled: false,
  googlepay_merchant_id: ''
});

// Accounting settings
const loadingAccounting = ref(false);
const savingAccounting = ref(false);
const accountingSettings = ref({});
const accountsForDropdown = ref({});

// Methods
const loadSettings = async () => {
  try {
    const response = await axios.get('/api/user/settings');
    settings.value = { ...settings.value, ...response.data };
  } catch (error) {
    console.error('Error loading settings:', error);
  }
};

const toggleSetting = async (key) => {
  settings.value[key] = !settings.value[key];
  await updateSetting(key, settings.value[key]);
};

const updateSetting = async (key, value) => {
  try {
    await axios.put('/api/user/settings', {
      [key]: value
    });

    // Apply theme change immediately
    if (key === 'theme') {
      applyTheme(value);
    }
  } catch (error) {
    console.error('Error updating setting:', error);
    // Revert the change if it failed
    if (typeof settings.value[key] === 'boolean') {
      settings.value[key] = !settings.value[key];
    }
  }
};

// Theme functionality
const applyTheme = (theme) => {
  const html = document.documentElement;
  localStorage.setItem('theme', theme);
  document.cookie = `theme=${theme}; path=/; max-age=31536000`; // 1 year

  if (theme === 'dark') {
    html.classList.add('dark');
  } else if (theme === 'light') {
    html.classList.remove('dark');
  } else if (theme === 'match system') {
    // Auto theme based on system preference
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (prefersDark) {
      html.classList.add('dark');
    } else {
      html.classList.remove('dark');
    }
  }
};

const saveAllSettings = async () => {
  saving.value = true;
  try {
    await axios.put('/api/user/settings', settings.value);
    showToast('Settings saved successfully!', 'success');
  } catch (error) {
    console.error('Error saving settings:', error);
    showToast('Failed to save settings. Please try again.', 'error');
  } finally {
    saving.value = false;
  }
};

// Watch for theme changes
watch(() => settings.value.theme, (newTheme) => {
  applyTheme(newTheme);
});

// Watch for accounting tab activation
watch(() => activeTab.value, (newTab) => {
  if (newTab === 'accounting') {
    loadAccountingSettings();
  }
});

// Keep currency selector in sync with store
watch(() => currencyStore.activeCurrencyId, (newId) => {
  selectedCurrencyId.value = newId;
});

// Lifecycle
onMounted(() => {
  loadSettings();
  loadTimezone();

  // Apply initial theme
  applyTheme(settings.value.theme);

  // Ensure currencies are loaded
  if (!currencyStore.currencies.length) {
    currencyStore.fetchCurrencies();
  }
  selectedCurrencyId.value = currencyStore.activeCurrencyId;

  // Load users and roles if user has permission
  if (authStore.hasPermission('users.view')) {
    loadUsers();
    loadUserStatistics();
    loadRoles();
  }

  // Load payment settings if user has permission
  if (authStore.hasPermission('settings.manage')) {
    loadPaymentSettings();
  }
});

// Users management methods
const loadUserStatistics = async () => {
  try {
    const response = await axios.get('/api/users/statistics');
    userStatistics.value = response.data;
  } catch (error) {
    console.error('Error loading user statistics:', error);
  }
};

const loadUsers = async (page = 1) => {
  loadingUsers.value = true;
  try {
    const params = {
      page,
      per_page: userPerPage.value,
      search: userSearchQuery.value,
      is_active: userStatusFilter.value,
      role: userRoleFilter.value,
      sort_field: userFilters.value.sort_field,
      sort_order: userFilters.value.sort_order
    };

    // Remove empty parameters
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null || params[key] === undefined) {
        delete params[key];
      }
    });

    const response = await axios.get('/api/users', { params });
    users.value = response.data;

    // Update pagination
    userPagination.value = {
      current_page: response.data.current_page || 1,
      last_page: response.data.last_page || 1,
      per_page: response.data.per_page || 15,
      total: response.data.total || 0,
      from: response.data.from || 0,
      to: response.data.to || 0
    };
  } catch (error) {
    console.error('Error loading users:', error);
  } finally {
    loadingUsers.value = false;
  }
};

const debouncedUserSearch = debounce(() => {
  loadUsers(1);
}, 300);

const handleUserPerPageChange = (size) => {
  userPerPage.value = size;
  loadUsers(1);
};

const handleTableSearch = (query) => {
  userSearchQuery.value = query;
  loadUsers(1);
};

const handleSort = (sortData) => {
  userFilters.value.sort_field = sortData.field;
  userFilters.value.sort_order = sortData.order;
  loadUsers(1);
};

const handlePageChange = (page) => {
  loadUsers(page);
};

const changeUserPage = (page) => {
  if (page >= 1 && page <= users.value.last_page) {
    loadUsers(page);
  }
};

const formatNumber = (value) => {
  return new Intl.NumberFormat().format(value || 0);
};

const getInitials = (name) => {
  if (!name) return '?';
  return name
    .split(' ')
    .map(word => word.charAt(0).toUpperCase())
    .slice(0, 2)
    .join('');
};

const handleCreateUser = () => {
  editingUser.value = null;
  showUserCreateModal.value = true;
};

const viewUser = (user) => {
  editingUser.value = user;
  showUserViewModal.value = true;
};

const closeUserModal = () => {
  showUserCreateModal.value = false;
  showUserEditModal.value = false;
  showUserViewModal.value = false;
  editingUser.value = null;
};

const handleUserSaved = async () => {
  await loadUsers(userPagination.value.current_page);
  await loadUserStatistics();
  closeUserModal();
};

// Currency change handler
const handleCurrencyChange = async () => {
  if (!selectedCurrencyId.value) return;
  currencySaveStatus.value = 'saving';
  try {
    await currencyStore.setActiveCurrency(selectedCurrencyId.value);
    currencySaveStatus.value = 'saved';
    // Auto-clear success status after 3 seconds
    setTimeout(() => {
      currencySaveStatus.value = '';
    }, 3000);
  } catch (error) {
    console.error('Error saving currency:', error);
    currencySaveStatus.value = 'error';
    setTimeout(() => {
      currencySaveStatus.value = '';
    }, 5000);
  }
};

// Timezone change handler
const loadTimezone = async () => {
  try {
    const response = await axios.get('/api/system-timezone');
    selectedTimezone.value = response.data.timezone;
  } catch (error) {
    console.error('Error loading timezone:', error);
  }
};

const handleTimezoneChange = async () => {
  if (!selectedTimezone.value) return;
  timezoneSaveStatus.value = 'saving';
  try {
    const response = await axios.post('/api/system-timezone', { timezone: selectedTimezone.value });
    selectedTimezone.value = response.data.timezone;
    timezoneSaveStatus.value = 'saved';
    setTimeout(() => {
      timezoneSaveStatus.value = '';
    }, 3000);
  } catch (error) {
    console.error('Error saving timezone:', error);
    timezoneSaveStatus.value = 'error';
    setTimeout(() => {
      timezoneSaveStatus.value = '';
    }, 5000);
  }
};

const loadRoles = async () => {
  try {
    const response = await axios.get('/api/roles');
    roles.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error loading roles:', error);
  }
};

const loadPaymentSettings = async () => {
  try {
    const response = await axios.get('/api/payment-settings');
    paymentSettings.value = { ...paymentSettings.value, ...response.data };
  } catch (error) {
    console.error('Error loading payment settings:', error);
  }
};

const updatePaymentSetting = async (key, value) => {
  try {
    await axios.put('/api/payment-settings', {
      [key]: value
    });
  } catch (error) {
    console.error('Error updating payment setting:', error);
  }
};

// Accounting settings methods
const loadAccountingSettings = async () => {
  loadingAccounting.value = true;
  try {
    console.log('Loading accounting settings...');

    // Load settings first
    const settingsResponse = await axios.get('/api/accounting-settings');
    console.log('Settings response:', settingsResponse.data);
    accountingSettings.value = settingsResponse.data || {};

    // Then load accounts
    const accountsResponse = await axios.get('/api/accounting-settings/accounts-for-dropdowns');
    console.log('Accounts response:', accountsResponse.data);
    accountsForDropdown.value = accountsResponse.data || {};

  } catch (error) {
    console.error('Error loading accounting settings:', error);
    console.error('Error details:', error.response?.data);

    // Initialize with empty objects if there's an error
    accountingSettings.value = {};
    accountsForDropdown.value = {};
  } finally {
    loadingAccounting.value = false;
  }
};

const updateAccountingSetting = () => {
  // Auto-save when dropdown changes
  saveAccountingSettings();
};

const saveAccountingSettings = async () => {
  savingAccounting.value = true;
  try {
    const response = await axios.put('/api/accounting-settings', accountingSettings.value);
    accountingSettings.value = response.data.settings;
    // Show success message
    console.log('Accounting settings saved successfully');
  } catch (error) {
    console.error('Error saving accounting settings:', error);
  } finally {
    savingAccounting.value = false;
  }
};

// User management actions
const editUser = (user) => {
  editingUser.value = { ...user };
  showUserEditModal.value = true;
};

const deleteUser = async (userId) => {
  if (confirm('Are you sure you want to delete this user?')) {
    try {
      await axios.delete(`/api/users/${userId}`);
      showToast('User deleted successfully', 'success');
      await loadUsers(userPagination.value.current_page);
      await loadUserStatistics();
    } catch (error) {
      showToast(error.response?.data?.message || 'Error deleting user', 'error');
    }
  }
};

// Role management actions
const handleCreateRole = () => {
  editingRole.value = null;
  showRoleModal.value = true;
};

const editRole = (role) => {
  editingRole.value = { ...role };
  showRoleModal.value = true;
};

const deleteRole = async (roleId, roleName) => {
  if (confirm(`Are you sure you want to delete the role "${roleName}"?`)) {
    try {
      await axios.delete(`/api/roles/${roleId}`);
      showToast('Role deleted successfully', 'success');
      await loadRoles();
    } catch (error) {
      showToast(error.response?.data?.message || 'Failed to delete role', 'error');
    }
  }
};

const closeRoleModal = () => {
  showRoleModal.value = false;
  editingRole.value = null;
};

const handleRoleSaved = async () => {
  await loadRoles();
  closeRoleModal();
};
</script>
