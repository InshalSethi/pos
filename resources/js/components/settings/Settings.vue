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

          <button
            @click="activeTab = 'invoice-purchase'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm whitespace-nowrap',
              activeTab === 'invoice-purchase'
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Invoice & Purchase Settings
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
              <div class="max-w-md w-full">
                <SystemSelect
                  label="Items per page"
                  v-model="settings.items_per_page"
                  :options="[
                    { value: '10', label: '10' },
                    { value: '15', label: '15' },
                    { value: '25', label: '25' },
                    { value: '50', label: '50' }
                  ]"
                  @update:modelValue="updateSetting('items_per_page', $event)"
                />
              </div>
            </div>
          </div>

          <!-- POS Settings -->
          <div class="border-t border-gray-200 pt-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Point of Sale</h3>
            <div class="space-y-4">
              <div class="max-w-md w-full">
                <SystemSelect
                  label="Default Payment Method"
                  v-model="settings.default_payment_method"
                  :options="[
                    { value: 'cash', label: 'Cash' },
                    { value: 'card', label: 'Card' },
                    { value: 'digital', label: 'Digital Wallet' }
                  ]"
                  @update:modelValue="updateSetting('default_payment_method', $event)"
                />
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
              <div class="max-w-md w-full">
                <SystemSelect
                  label="Session Timeout (minutes)"
                  v-model="settings.session_timeout"
                  :options="[
                    { value: '15', label: '15 minutes' },
                    { value: '30', label: '30 minutes' },
                    { value: '60', label: '1 hour' },
                    { value: '120', label: '2 hours' },
                    { value: '480', label: '8 hours' }
                  ]"
                  @update:modelValue="updateSetting('session_timeout', $event)"
                />
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
                      v-if="!isMainOwner(item)"
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
                      v-if="!isMainOwner(item)"
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
                      <option :value="null">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="Number(account.id)">
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
                      <option :value="null">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="Number(account.id)">
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
                      <option :value="null">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="Number(account.id)">
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
                      <option :value="null">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="Number(account.id)">
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
                      <option :value="null">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="Number(account.id)">
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
                      <option :value="null">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="Number(account.id)">
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
                      <option :value="null">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="Number(account.id)">
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
                      <option :value="null">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="Number(account.id)">
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
                      <option :value="null">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="Number(account.id)">
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
                      <option :value="null">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="Number(account.id)">
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
                      <option :value="null">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="Number(account.id)">
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
                      <option :value="null">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="Number(account.id)">
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
                      <option :value="null">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="Number(account.id)">
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
                      <option :value="null">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="Number(account.id)">
                          {{ account.display_name }}
                        </option>
                      </optgroup>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Liquid Asset & Banking Accounts -->
              <div class="bg-blue-50 p-6 rounded-lg">
                <h4 class="text-lg font-medium text-blue-900 mb-4">Liquid Assets & Banking Accounts</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Default Cash Account</label>
                    <select
                      v-model="accountingSettings.cash_account_id"
                      @change="updateAccountingSetting"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                      <option :value="null">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="Number(account.id)">
                          {{ account.display_name }}
                        </option>
                      </optgroup>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Default Bank Account</label>
                    <select
                      v-model="accountingSettings.bank_account_id"
                      @change="updateAccountingSetting"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                      <option :value="null">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="Number(account.id)">
                          {{ account.display_name }}
                        </option>
                      </optgroup>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Inventory & Cost of Goods Sold Accounts -->
              <div class="bg-teal-50 p-6 rounded-lg">
                <h4 class="text-lg font-medium text-teal-900 mb-4">Inventory & Cost of Goods Sold Accounts</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Inventory Asset Account</label>
                    <select
                      v-model="accountingSettings.inventory_asset_account_id"
                      @change="updateAccountingSetting"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                      <option :value="null">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="Number(account.id)">
                          {{ account.display_name }}
                        </option>
                      </optgroup>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cost of Goods Sold (COGS) Account</label>
                    <select
                      v-model="accountingSettings.cost_of_goods_sold_account_id"
                      @change="updateAccountingSetting"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                      <option :value="null">Select Account</option>
                      <optgroup v-for="(accounts, type) in accountsForDropdown" :key="type" :label="type.charAt(0).toUpperCase() + type.slice(1)">
                        <option v-for="account in accounts" :key="account.id" :value="Number(account.id)">
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



        <!-- Invoice & Purchase Settings Tab -->
        <div v-else-if="activeTab === 'invoice-purchase'" class="p-6 bg-slate-50 dark:bg-zinc-950/50 min-h-screen space-y-8">
          
          <!-- Loading state -->
          <div v-if="loadingInvoicePurchaseSettings" class="flex flex-col justify-center items-center h-64 space-y-3">
            <div class="animate-spin rounded-full h-10 w-10 border-4 border-indigo-600 border-t-transparent"></div>
            <p class="text-xs font-semibold text-slate-500 dark:text-zinc-400">Loading Invoice & Purchase Settings...</p>
          </div>

          <template v-else>
            <!-- Header -->
            <div class="border-b border-slate-200 dark:border-zinc-800 pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
              <div>
                <h3 class="text-xl font-extrabold text-slate-900 dark:text-zinc-100">Invoice & Purchase Settings</h3>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">Configure default preferences, prefix rules, tax behaviors, and default terms for Sales Invoices and Purchase Orders.</p>
              </div>

              <!-- Top Save Action Button -->
              <button
                @click="saveInvoicePurchaseSettings"
                :disabled="savingInvoicePurchaseSettings"
                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-sm transition-all flex items-center space-x-2 disabled:opacity-50 cursor-pointer self-start sm:self-auto"
              >
                <svg v-if="savingInvoicePurchaseSettings" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ savingInvoicePurchaseSettings ? 'Saving...' : 'Save All Settings' }}</span>
              </button>
            </div>

            <!-- SECTION A: Invoice Settings (Sales) -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 p-6 shadow-xs space-y-6">
              <div class="flex items-center space-x-2 border-b border-slate-100 dark:border-zinc-800/80 pb-3">
                <span class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 font-black text-xs">A</span>
                <div>
                  <h4 class="text-sm font-bold text-slate-900 dark:text-zinc-100">Section A: Invoice Settings (Sales)</h4>
                  <p class="text-[11px] text-slate-500 dark:text-zinc-400">Default numbering, pricing mode, due periods, and terms for Sales Invoices.</p>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- 1. Invoice Numbering Prefix -->
                <div>
                  <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1.5">
                    Invoice Numbering Prefix
                  </label>
                  <input
                    v-model="invoicePurchaseSettings.invoice_prefix"
                    type="text"
                    placeholder="e.g. INV- or SLS-"
                    class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-zinc-200"
                  />
                  <p class="text-[10px] text-slate-400 dark:text-zinc-500 mt-1">Applied to automatically generated sales invoice numbers.</p>
                </div>

                <!-- 2. Default Pricing Mode -->
                <div>
                  <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1.5">
                    Default Pricing Mode
                  </label>
                  <div class="flex items-center space-x-4 pt-1">
                    <label class="inline-flex items-center cursor-pointer space-x-2">
                      <input
                        type="radio"
                        value="retail"
                        v-model="invoicePurchaseSettings.default_pricing_mode"
                        class="text-indigo-600 focus:ring-indigo-500 h-4 w-4"
                      />
                      <span class="text-xs font-extrabold text-slate-800 dark:text-zinc-200">Retail Mode</span>
                    </label>

                    <label class="inline-flex items-center cursor-pointer space-x-2">
                      <input
                        type="radio"
                        value="wholesale"
                        v-model="invoicePurchaseSettings.default_pricing_mode"
                        class="text-indigo-600 focus:ring-indigo-500 h-4 w-4"
                      />
                      <span class="text-xs font-extrabold text-indigo-600 dark:text-indigo-400">Wholesale Mode</span>
                    </label>
                  </div>
                  <p class="text-[10px] text-slate-400 dark:text-zinc-500 mt-1">Default active mode when initializing Create Invoice screen.</p>
                </div>

                <!-- 3. Default Due Period (Days) -->
                <div>
                  <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1.5">
                    Default Due Period (Days)
                  </label>
                  <input
                    v-model.number="invoicePurchaseSettings.default_due_period_days"
                    type="number"
                    min="0"
                    max="365"
                    placeholder="e.g. 30"
                    class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-zinc-200"
                  />
                  <p class="text-[10px] text-slate-400 dark:text-zinc-500 mt-1">Number of days added to creation date to calculate default due date.</p>
                </div>

                <!-- 5. Show/Hide Item Wholesale Toggle -->
                <div class="flex items-center justify-between p-3.5 bg-slate-50 dark:bg-zinc-800/50 rounded-xl border border-slate-200/60 dark:border-zinc-700/60">
                  <div>
                    <label class="text-xs font-bold text-slate-800 dark:text-zinc-200 block">Show Row-Level W.S Toggle</label>
                    <p class="text-[10px] text-slate-400 dark:text-zinc-400">Enable/disable row-level Wholesale toggle switch on invoice items.</p>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer select-none">
                    <input
                      type="checkbox"
                      v-model="invoicePurchaseSettings.show_item_wholesale_toggle"
                      class="sr-only peer"
                    />
                    <div class="w-9 h-5 bg-slate-300 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-zinc-600 peer-checked:bg-indigo-600"></div>
                  </label>
                </div>

                <!-- 4. Default Invoice Terms & Conditions -->
                <div class="md:col-span-2">
                  <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1.5">
                    Default Invoice Terms & Conditions
                  </label>
                  <textarea
                    v-model="invoicePurchaseSettings.default_terms_conditions"
                    rows="3"
                    placeholder="Enter default terms and conditions text for sales invoices..."
                    class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-zinc-200"
                  ></textarea>
                  <p class="text-[10px] text-slate-400 dark:text-zinc-500 mt-1">Appears in footer notes section on printed and digital invoices.</p>
                </div>

              </div>
            </div>

            <!-- SECTION B: Purchase Order Settings -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 p-6 shadow-xs space-y-6">
              <div class="flex items-center space-x-2 border-b border-slate-100 dark:border-zinc-800/80 pb-3">
                <span class="p-2 rounded-xl bg-purple-50 dark:bg-purple-950 text-purple-600 dark:text-purple-400 font-black text-xs">B</span>
                <div>
                  <h4 class="text-sm font-bold text-slate-900 dark:text-zinc-100">Section B: Purchase Order Settings</h4>
                  <p class="text-[11px] text-slate-500 dark:text-zinc-400">Prefix rules, default receiving warehouses, and cost update behavior for Purchases.</p>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- 1. PO Numbering Prefix -->
                <div>
                  <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1.5">
                    PO Numbering Prefix
                  </label>
                  <input
                    v-model="invoicePurchaseSettings.po_prefix"
                    type="text"
                    placeholder="e.g. PO- or PUR-"
                    class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-zinc-200"
                  />
                  <p class="text-[10px] text-slate-400 dark:text-zinc-500 mt-1">Applied to automatically generated Purchase Order numbers.</p>
                </div>

                <!-- 2. Default Purchase Warehouse -->
                <div>
                  <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1.5">
                    Default Purchase Receiving Warehouse
                  </label>
                  <select
                    v-model="invoicePurchaseSettings.default_purchase_warehouse_id"
                    class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-zinc-200"
                  >
                    <option :value="null">-- Select Default Warehouse --</option>
                    <option v-for="wh in warehousesList" :key="wh.id" :value="wh.id">
                      {{ wh.name }} {{ wh.code ? `(${wh.code})` : '' }}
                    </option>
                  </select>
                  <p class="text-[10px] text-slate-400 dark:text-zinc-500 mt-1">Pre-selected receiving location on new Purchase Order screen.</p>
                </div>

                <!-- 3. Auto-Update Product Cost Price -->
                <div class="md:col-span-2 flex items-center justify-between p-3.5 bg-slate-50 dark:bg-zinc-800/50 rounded-xl border border-slate-200/60 dark:border-zinc-700/60">
                  <div>
                    <label class="text-xs font-bold text-slate-800 dark:text-zinc-200 block">Auto-Update Product Master Cost Price</label>
                    <p class="text-[10px] text-slate-400 dark:text-zinc-400">Automatically update product master cost price when stock is received via Purchase Order.</p>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer select-none">
                    <input
                      type="checkbox"
                      v-model="invoicePurchaseSettings.auto_update_product_cost"
                      class="sr-only peer"
                    />
                    <div class="w-9 h-5 bg-slate-300 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-zinc-600 peer-checked:bg-indigo-600"></div>
                  </label>
                </div>

              </div>
            </div>

            <!-- SECTION C: Taxes & Discount Defaults -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 p-6 shadow-xs space-y-6">
              <div class="flex items-center space-x-2 border-b border-slate-100 dark:border-zinc-800/80 pb-3">
                <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 font-black text-xs">C</span>
                <div>
                  <h4 class="text-sm font-bold text-slate-900 dark:text-zinc-100">Section C: Taxes & Discount Defaults</h4>
                  <p class="text-[11px] text-slate-500 dark:text-zinc-400">Default included system taxes and manual tax/discount overrides control.</p>
                </div>
              </div>

              <div class="space-y-6">
                
                <!-- 1. Default Included System Taxes -->
                <div>
                  <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-2">
                    Default Included System Taxes
                  </label>
                  <div v-if="taxes.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    <div
                      v-for="tax in taxes"
                      :key="tax.id"
                      @click="toggleDefaultSystemTax(tax.id)"
                      :class="[
                        'p-3 rounded-xl border flex items-center justify-between cursor-pointer transition-all',
                        (invoicePurchaseSettings.default_system_tax_ids || []).includes(Number(tax.id))
                          ? 'bg-emerald-50 dark:bg-emerald-950/60 border-emerald-300 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200'
                          : 'bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 text-slate-600 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800'
                      ]"
                    >
                      <div class="flex items-center space-x-2.5">
                        <input
                          type="checkbox"
                          :checked="(invoicePurchaseSettings.default_system_tax_ids || []).includes(Number(tax.id))"
                          class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4 pointer-events-none"
                        />
                        <span class="text-xs font-bold">{{ tax.name }} ({{ tax.value }}{{ tax.type === 'percentage' ? '%' : '' }})</span>
                      </div>
                      <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded bg-slate-100 dark:bg-zinc-800 text-slate-500">
                        {{ tax.sale_invoice_required ? 'Required' : 'Optional' }}
                      </span>
                    </div>
                  </div>
                  <p v-else class="text-xs text-slate-400 dark:text-zinc-500 italic">No system taxes configured in Tax Module.</p>
                  <p class="text-[10px] text-slate-400 dark:text-zinc-500 mt-1.5">Select which system taxes are enabled by default on new invoice creation.</p>
                </div>

                <!-- 2. Allow Manual Taxes & Discounts -->
                <div class="flex items-center justify-between p-3.5 bg-slate-50 dark:bg-zinc-800/50 rounded-xl border border-slate-200/60 dark:border-zinc-700/60">
                  <div>
                    <label class="text-xs font-bold text-slate-800 dark:text-zinc-200 block">Allow Manual Taxes & Discounts</label>
                    <p class="text-[10px] text-slate-400 dark:text-zinc-400">Allow cashiers and sales representatives to enter manual tax and discount amounts during POS/Invoice creation.</p>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer select-none">
                    <input
                      type="checkbox"
                      v-model="invoicePurchaseSettings.allow_manual_taxes_discounts"
                      class="sr-only peer"
                    />
                    <div class="w-9 h-5 bg-slate-300 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-zinc-600 peer-checked:bg-indigo-600"></div>
                  </label>
                </div>

              </div>
            </div>

            <!-- Bottom Save Action Bar -->
            <div class="flex justify-end pt-4">
              <button
                @click="saveInvoicePurchaseSettings"
                :disabled="savingInvoicePurchaseSettings"
                class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl text-xs shadow-md transition-all flex items-center space-x-2 disabled:opacity-50 cursor-pointer"
              >
                <svg v-if="savingInvoicePurchaseSettings" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ savingInvoicePurchaseSettings ? 'Saving Settings...' : 'Save All Settings' }}</span>
              </button>
            </div>

          </template>
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

import axios from 'axios';
import { useToast } from '@/composables/useToast';
import { debounce } from '@/utils/debounce';
import DataTable from '@/components/common/DataTable.vue';
import SystemSelect from '@/components/common/SystemSelect.vue';
import UserCreateForm from './UserCreateForm.vue';
import UserViewModal from './UserViewModal.vue';
import RoleCreateForm from './RoleCreateForm.vue';

const authStore = useAuthStore();

const { showToast } = useToast();

// Reactive data
const activeTab = ref('general');
const saving = ref(false);

const companyData = ref({});
const loadingCompany = ref(false);
const savingCompany = ref(false);

const businessTypes = [
  { value: 'agriculture', label: 'Agriculture' },
  { value: 'art_design', label: 'Art and Design' },
  { value: 'construction_trades', label: 'Construction, Trades and Home Services' },
  { value: 'development_programming', label: 'Development & Programming' },
  { value: 'education_training', label: 'Education and Training' },
  { value: 'financial_insurance', label: 'Financial services & insurance' },
  { value: 'food_services', label: 'Food Services' },
  { value: 'health_wellness', label: 'Health and Wellness' },
  { value: 'hospitality_tourism', label: 'Hospitality, Travel and Tourism' },
  { value: 'hr_staffing', label: 'Human Resources and Staffing' },
  { value: 'it', label: 'Information Technology' },
  { value: 'manufacturing', label: 'Manufacturing' },
  { value: 'non_profit', label: 'Non-Profit' },
  { value: 'professional_services', label: 'Professional Services (e.g. Legal, Accounting, Marketing, Consulting)' },
  { value: 'real_estate', label: 'Real Estate and Property Management' },
  { value: 'retail', label: 'Retail (E-Commerce and Offline)' },
  { value: 'software_development', label: 'Software Development' },
  { value: 'wholesale_trade', label: 'Wholesale Trade' },
  { value: 'other', label: 'Other' }
];

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

// Invoice & Purchase Settings state
const loadingInvoicePurchaseSettings = ref(false);
const savingInvoicePurchaseSettings = ref(false);
const warehousesList = ref([]);
const invoicePurchaseSettings = ref({
  invoice_prefix: 'INV-',
  default_pricing_mode: 'retail',
  default_due_period_days: 30,
  default_terms_conditions: 'Thank you for your business!',
  show_item_wholesale_toggle: true,
  po_prefix: 'PO-',
  default_purchase_warehouse_id: null,
  auto_update_product_cost: true,
  default_system_tax_ids: [],
  allow_manual_taxes_discounts: true
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

// Taxes state
const taxes = ref([]);
const taxSearchQuery = ref('');
const showTaxModal = ref(false);
const submittingTax = ref(false);
const taxModalError = ref('');
const editingTax = ref(null);
const taxForm = ref({
  name: '',
  value: '',
  type: 'percentage',
  is_active: true
});

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

const filteredTaxes = computed(() => {
  if (!taxSearchQuery.value) return taxes.value;
  return taxes.value.filter(t => 
    t.name?.toLowerCase().includes(taxSearchQuery.value.toLowerCase())
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
  const normalizedTheme = (theme === 'auto' || theme === 'match system') ? 'system' : theme;
  localStorage.setItem('theme', normalizedTheme);
  document.cookie = `theme=${normalizedTheme}; path=/; max-age=31536000`; // 1 year

  if (normalizedTheme === 'dark') {
    html.classList.add('dark');
  } else if (normalizedTheme === 'light') {
    html.classList.remove('dark');
  } else if (normalizedTheme === 'system') {
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
});// Watch for accounting, taxes, or invoice-purchase tab activation
watch(() => activeTab.value, (newTab) => {
  if (newTab === 'accounting') {
    loadAccountingSettings();
  } else if (newTab === 'taxes') {
    loadTaxes();
  } else if (newTab === 'invoice-purchase') {
    fetchInvoicePurchaseSettings();
  }
});

// Lifecycle
onMounted(() => {
  loadSettings();

  // Apply initial theme
  applyTheme(settings.value.theme);

  // Ensure currencies are loaded for other components that may need it

  // Load users and roles if user has permission
  if (authStore.hasPermission('users.view')) {
    loadUsers();
    loadUserStatistics();
    loadRoles();
  }

  // Load payment settings if user has permission
  if (authStore.hasPermission('settings.payment_gateways')) {
    loadPaymentSettings();
  }
  loadCompanyDetails();

  if (activeTab.value === 'taxes') {
    loadTaxes();
  }
});

// Taxes management methods
const loadTaxes = async () => {
  try {
    const response = await axios.get('/api/taxes');
    taxes.value = response.data;
  } catch (error) {
    console.error('Error loading taxes:', error);
    showToast('Failed to load taxes', 'error');
  }
};

const openTaxModal = (tax = null) => {
  taxModalError.value = '';
  if (tax) {
    editingTax.value = tax;
    taxForm.value = {
      name: tax.name,
      value: tax.value,
      type: tax.type,
      is_active: tax.is_active !== undefined ? !!tax.is_active : true,
      sale_invoice_required: !!tax.sale_invoice_required,
      purchase_order_required: !!tax.purchase_order_required
    };
  } else {
    editingTax.value = null;
    taxForm.value = {
      name: '',
      value: '',
      type: 'percentage',
      is_active: true,
      sale_invoice_required: false,
      purchase_order_required: false
    };
  }
  showTaxModal.value = true;
};

const closeTaxModal = () => {
  showTaxModal.value = false;
  editingTax.value = null;
};

const submitTax = async () => {
  if (!taxForm.value.name) {
    taxModalError.value = 'Tax name is required.';
    return;
  }
  if (taxForm.value.value === '' || taxForm.value.value === null || taxForm.value.value === undefined) {
    taxModalError.value = 'Tax value is required.';
    return;
  }
  submittingTax.value = true;
  taxModalError.value = '';
  try {
    if (editingTax.value) {
      await axios.put(`/api/taxes/${editingTax.value.id}`, taxForm.value);
      showToast('Tax rule updated successfully', 'success');
    } else {
      await axios.post('/api/taxes', taxForm.value);
      showToast('Tax rule created successfully', 'success');
    }
    await loadTaxes();
    closeTaxModal();
  } catch (error) {
    console.error('Error saving tax:', error);
    const message = error.response?.data?.message || 'Failed to save tax';
    const errors = error.response?.data?.errors;
    if (errors) {
      taxModalError.value = Object.values(errors).flat().join(' ');
    } else {
      taxModalError.value = message;
    }
  } finally {
    submittingTax.value = false;
  }
};

const deleteTax = async (taxId) => {
  if (confirm('Are you sure you want to delete this tax rule?')) {
    try {
      await axios.delete(`/api/taxes/${taxId}`);
      showToast('Tax rule deleted successfully', 'success');
      await loadTaxes();
    } catch (error) {
      console.error('Error deleting tax:', error);
      showToast(error.response?.data?.message || 'Error deleting tax', 'error');
    }
  }
};
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


// Timezone is now managed during company setup (Step 4).
// These stubs are kept to avoid any accidental reference errors.
const loadTimezone = async () => {};
const handleTimezoneChange = async () => {};


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
    const settingsResponse = await axios.get('/api/accounting-settings');
    const rawData = settingsResponse.data?.settings || settingsResponse.data || {};

    const parsedSettings = {};
    Object.keys(rawData).forEach(key => {
      const val = rawData[key];
      parsedSettings[key] = (val !== null && val !== undefined && key.endsWith('_id'))
        ? Number(val)
        : val;
    });

    accountingSettings.value = parsedSettings;

    const accountsResponse = await axios.get('/api/accounting-settings/accounts-for-dropdowns');
    accountsForDropdown.value = accountsResponse.data || {};

  } catch (error) {
    console.error('Error loading accounting settings:', error);
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

const loadCompanyDetails = async () => {
  loadingCompany.value = true;
  try {
    const response = await axios.get('/api/companies/active');
    companyData.value = response.data.company;
    if (authStore.user) {
      companyData.value.company_email = authStore.user.email;
    }
  } catch (error) {
    showToast('Failed to load company details', 'error');
  } finally {
    loadingCompany.value = false;
  }
};

const saveCompanyDetails = async () => {
  savingCompany.value = true;
  try {
    const payload = { ...companyData.value };
    if (authStore.user?.email) {
      payload.company_email = authStore.user.email;
    }
    await axios.put('/api/companies/active', payload);
    showToast('Company details updated successfully', 'success');
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Failed to update company details';
    const errors = error.response?.data?.errors;
    let fullMsg = errorMsg;
    if (errors) {
        fullMsg += ': ' + Object.values(errors).flat().join(', ');
    }
    showToast(fullMsg, 'error');
  } finally {
    savingCompany.value = false;
  }
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
const isMainOwner = (user) => {
  if (!user) return false;
  if (user.is_main_owner) return true;
  if (companyData.value && companyData.value.user_id && String(user.id) === String(companyData.value.user_id)) {
    return true;
  }
  if (String(user.id) === '1') return true;
  return false;
};

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

// Invoice & Purchase Settings management methods
const fetchInvoicePurchaseSettings = async () => {
  try {
    loadingInvoicePurchaseSettings.value = true;
    const [settingsRes, whRes, taxRes] = await Promise.all([
      axios.get('/api/invoice-purchase-settings'),
      axios.get('/api/warehouses'),
      axios.get('/api/taxes')
    ]);
    if (settingsRes.data) {
      try {
        localStorage.setItem('company_invoice_settings', JSON.stringify(settingsRes.data));
      } catch(e){}
      invoicePurchaseSettings.value = {
        ...invoicePurchaseSettings.value,
        ...settingsRes.data,
        default_system_tax_ids: Array.isArray(settingsRes.data.default_system_tax_ids)
          ? settingsRes.data.default_system_tax_ids.map(id => Number(id))
          : []
      };
    }
    warehousesList.value = Array.isArray(whRes.data) ? whRes.data : (whRes.data?.data || []);
    if (!taxes.value || taxes.value.length === 0) {
      taxes.value = Array.isArray(taxRes.data) ? taxRes.data : (taxRes.data?.data || []);
    }
  } catch (e) {
    console.error('Error fetching invoice purchase settings:', e);
    showToast('Failed to load Invoice & Purchase settings', 'error');
  } finally {
    loadingInvoicePurchaseSettings.value = false;
  }
};

const saveInvoicePurchaseSettings = async () => {
  try {
    savingInvoicePurchaseSettings.value = true;
    const res = await axios.put('/api/invoice-purchase-settings', invoicePurchaseSettings.value);
    if (res.data?.settings) {
      try {
        localStorage.setItem('company_invoice_settings', JSON.stringify(res.data.settings));
      } catch(e){}
    }
    showToast(res.data.message || 'Invoice & Purchase settings updated successfully', 'success');
  } catch (e) {
    console.error('Error saving invoice purchase settings:', e);
    showToast('Failed to save settings: ' + (e.response?.data?.message || e.message), 'error');
  } finally {
    savingInvoicePurchaseSettings.value = false;
  }
};

const toggleDefaultSystemTax = (taxId) => {
  const numId = Number(taxId);
  const current = invoicePurchaseSettings.value.default_system_tax_ids || [];
  if (current.includes(numId)) {
    invoicePurchaseSettings.value.default_system_tax_ids = current.filter(id => id !== numId);
  } else {
    invoicePurchaseSettings.value.default_system_tax_ids = [...current, numId];
  }
};
</script>
