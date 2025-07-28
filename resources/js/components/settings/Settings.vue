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
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Theme</label>
                <select
                  v-model="settings.theme"
                  @change="updateSetting('theme', settings.theme)"
                  class="w-full max-w-xs px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="light">Light</option>
                  <option value="dark">Dark</option>
                  <option value="auto">Auto</option>
                </select>
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

          <!-- Save Button -->
          <div class="border-t border-gray-200 pt-8">
            <div class="flex justify-end">
              <button
                @click="saveAllSettings"
                :disabled="saving"
                class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="saving">Saving...</span>
                <span v-else>Save Settings</span>
              </button>
            </div>
          </div>
          </div>
        </div>

        <!-- Users Tab -->
        <div v-else-if="activeTab === 'users'" class="p-6">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-gray-900">User Management</h3>
            <button
              @click="showUserModal = true"
              class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium"
            >
              Add User
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="user in users" :key="user.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ user.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.email }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span v-for="role in user.roles" :key="role.id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1">
                      {{ role.name }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                      Active
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button @click="editUser(user)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                    <button @click="deleteUser(user.id)" class="text-red-600 hover:text-red-900">Delete</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Roles Tab -->
        <div v-else-if="activeTab === 'roles'" class="p-6">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-gray-900">Role Management</h3>
            <button
              @click="showRoleModal = true"
              class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium"
            >
              Add Role
            </button>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="role in roles" :key="role.id" class="bg-white border border-gray-200 rounded-lg p-6">
              <div class="flex justify-between items-start mb-4">
                <h4 class="text-lg font-medium text-gray-900">{{ role.name }}</h4>
                <div class="flex space-x-2">
                  <button @click="editRole(role)" class="text-indigo-600 hover:text-indigo-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  <button @click="deleteRole(role.id)" class="text-red-600 hover:text-red-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
              </div>
              <p class="text-sm text-gray-600 mb-4">{{ role.description || 'No description' }}</p>
              <div class="space-y-2">
                <h5 class="text-sm font-medium text-gray-900">Permissions:</h5>
                <div class="flex flex-wrap gap-1">
                  <span v-for="permission in role.permissions" :key="permission.id" class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                    {{ permission.name }}
                  </span>
                </div>
              </div>
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
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const authStore = useAuthStore();

// Reactive data
const activeTab = ref('general');
const saving = ref(false);
const settings = ref({
  email_notifications: true,
  sales_alerts: true,
  low_stock_alerts: true,
  theme: 'light',
  items_per_page: '15',
  default_payment_method: 'cash',
  auto_print_receipts: false,
  sound_effects: true,
  session_timeout: '60',
  two_factor_auth: false
});

// Users tab data
const users = ref([]);
const roles = ref([]);
const showUserModal = ref(false);
const showRoleModal = ref(false);
const editingUser = ref(null);
const editingRole = ref(null);

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

  if (theme === 'dark') {
    html.classList.add('dark');
  } else if (theme === 'light') {
    html.classList.remove('dark');
  } else if (theme === 'auto') {
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
    // Show success message or notification
  } catch (error) {
    console.error('Error saving settings:', error);
  } finally {
    saving.value = false;
  }
};

// Watch for theme changes
watch(() => settings.value.theme, (newTheme) => {
  applyTheme(newTheme);
});

// Lifecycle
onMounted(() => {
  loadSettings();

  // Apply initial theme
  applyTheme(settings.value.theme);

  // Load users and roles if user has permission
  if (authStore.hasPermission('users.view')) {
    loadUsers();
    loadRoles();
  }

  // Load payment settings if user has permission
  if (authStore.hasPermission('settings.manage')) {
    loadPaymentSettings();
  }
});

// Users management methods
const loadUsers = async () => {
  try {
    const response = await axios.get('/api/users');
    users.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error loading users:', error);
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

// User management methods
const editUser = (user) => {
  editingUser.value = { ...user };
  showUserModal.value = true;
};

const deleteUser = async (userId) => {
  if (confirm('Are you sure you want to delete this user?')) {
    try {
      await axios.delete(`/api/users/${userId}`);
      await loadUsers();
    } catch (error) {
      console.error('Error deleting user:', error);
    }
  }
};

// Role management methods
const editRole = (role) => {
  editingRole.value = { ...role };
  showRoleModal.value = true;
};

const deleteRole = async (roleId) => {
  if (confirm('Are you sure you want to delete this role?')) {
    try {
      await axios.delete(`/api/roles/${roleId}`);
      await loadRoles();
    } catch (error) {
      console.error('Error deleting role:', error);
    }
  }
};
</script>
