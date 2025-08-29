<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Chart of Accounts</h1>
          <div class="flex space-x-3">
            <button
              @click="activeView = 'tree'"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium',
                activeView === 'tree'
                  ? 'bg-indigo-600 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50'
              ]"
            >
              Tree View
            </button>
            <button
              @click="activeView = 'list'"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium',
                activeView === 'list'
                  ? 'bg-indigo-600 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50'
              ]"
            >
              List View
            </button>
            <button
              @click="activeView = 'balances'"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium',
                activeView === 'balances'
                  ? 'bg-indigo-600 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50'
              ]"
            >
              Account Balances
            </button>
            <button
              @click="activeView = 'journal-entries'"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium',
                activeView === 'journal-entries'
                  ? 'bg-indigo-600 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50'
              ]"
            >
              Journal Entries
            </button>
            <button
              @click="activeView = 'banking'"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium',
                activeView === 'banking'
                  ? 'bg-indigo-600 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50'
              ]"
            >
              Banking
            </button>
            <button
              @click="showAccountModal = true"
              class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium"
            >
              New Account
            </button>
          </div>
        </div>

        <!-- Account Type Filter -->
        <div class="mb-6">
          <div class="flex space-x-2 overflow-x-auto pb-2">
            <button
              @click="selectedAccountType = ''"
              :class="[
                'px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap',
                selectedAccountType === ''
                  ? 'bg-indigo-600 text-white'
                  : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
              ]"
            >
              All Types
            </button>
            <button
              v-for="type in accountTypes"
              :key="type.value"
              @click="selectedAccountType = type.value"
              :class="[
                'px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap',
                selectedAccountType === type.value
                  ? 'bg-indigo-600 text-white'
                  : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
              ]"
            >
              {{ type.label }}
            </button>
          </div>
        </div>

        <!-- Content Area -->
        <div class="bg-white shadow rounded-lg">
          <!-- Tree View -->
          <div v-if="activeView === 'tree'" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Account Hierarchy</h2>

            <div v-if="loadingTree" class="text-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
              <p class="mt-2 text-gray-600">Loading account tree...</p>
            </div>

            <div v-else class="space-y-2">
              <div v-for="account in accountTree" :key="account.id">
                <AccountTreeNode
                  :account="account"
                  :level="0"
                  @edit="editAccount"
                  @delete="deleteAccount"
                />
              </div>
            </div>
          </div>

          <!-- List View -->
          <div v-if="activeView === 'list'" class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-lg font-medium text-gray-900">Account List</h2>
              <div class="flex space-x-3">
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search accounts..."
                  class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                  @input="debouncedSearch"
                />
              </div>
            </div>

            <div v-if="loadingAccounts" class="text-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
              <p class="mt-2 text-gray-600">Loading accounts...</p>
            </div>

            <div v-else-if="accounts.length === 0" class="text-center py-8 text-gray-500">
              No accounts found.
            </div>

            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Code
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Account Name
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Type
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Subtype
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Balance
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Status
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="account in accounts" :key="account.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ account.account_code }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ account.account_name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        :class="[
                          'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                          getAccountTypeColor(account.account_type)
                        ]"
                      >
                        {{ formatAccountType(account.account_type) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatAccountSubtype(account.account_subtype) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      ${{ formatNumber(account.current_balance) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        :class="[
                          'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                          account.is_active
                            ? 'bg-green-100 text-green-800'
                            : 'bg-red-100 text-red-800'
                        ]"
                      >
                        {{ account.is_active ? 'Active' : 'Inactive' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <div class="flex justify-end space-x-2">
                        <button
                          @click="editAccount(account)"
                          class="text-indigo-600 hover:text-indigo-900"
                        >
                          Edit
                        </button>
                        <button
                          v-if="!account.is_system_account"
                          @click="deleteAccount(account)"
                          class="text-red-600 hover:text-red-900"
                        >
                          Delete
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Account Balances View -->
          <div v-if="activeView === 'balances'" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Account Balances Summary</h2>

            <div v-if="loadingBalances" class="text-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
              <p class="mt-2 text-gray-600">Loading balances...</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <!-- Assets -->
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                      </svg>
                    </div>
                  </div>
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-sm font-medium text-blue-600 truncate">Total Assets</dt>
                      <dd class="text-lg font-medium text-blue-900">${{ formatNumber(balances.assets) }}</dd>
                    </dl>
                  </div>
                </div>
              </div>

              <!-- Liabilities -->
              <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                      </svg>
                    </div>
                  </div>
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-sm font-medium text-red-600 truncate">Total Liabilities</dt>
                      <dd class="text-lg font-medium text-red-900">${{ formatNumber(balances.liabilities) }}</dd>
                    </dl>
                  </div>
                </div>
              </div>

              <!-- Equity -->
              <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                      </svg>
                    </div>
                  </div>
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-sm font-medium text-green-600 truncate">Total Equity</dt>
                      <dd class="text-lg font-medium text-green-900">${{ formatNumber(balances.equity) }}</dd>
                    </dl>
                  </div>
                </div>
              </div>

              <!-- Revenue -->
              <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                      </svg>
                    </div>
                  </div>
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-sm font-medium text-purple-600 truncate">Total Revenue</dt>
                      <dd class="text-lg font-medium text-purple-900">${{ formatNumber(balances.revenue) }}</dd>
                    </dl>
                  </div>
                </div>
              </div>

              <!-- Expenses -->
              <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                      </svg>
                    </div>
                  </div>
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-sm font-medium text-yellow-600 truncate">Total Expenses</dt>
                      <dd class="text-lg font-medium text-yellow-900">${{ formatNumber(balances.expenses) }}</dd>
                    </dl>
                  </div>
                </div>
              </div>

              <!-- Net Income -->
              <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-indigo-500 rounded-md flex items-center justify-center">
                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                      </svg>
                    </div>
                  </div>
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-sm font-medium text-indigo-600 truncate">Net Income</dt>
                      <dd class="text-lg font-medium text-indigo-900">${{ formatNumber(balances.net_income) }}</dd>
                    </dl>
                  </div>
                </div>
              </div>
            </div>

            <!-- Accounting Equation Check -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
              <h3 class="text-sm font-medium text-gray-900 mb-2">Accounting Equation Check</h3>
              <div class="text-sm text-gray-600">
                <p>Assets: ${{ formatNumber(balances.equation_check?.assets || 0) }}</p>
                <p>Liabilities + Equity + Net Income: ${{ formatNumber(balances.equation_check?.liabilities_equity_income || 0) }}</p>
                <p class="mt-2">
                  <span
                    :class="[
                      'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                      balances.equation_check?.balanced
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800'
                    ]"
                  >
                    {{ balances.equation_check?.balanced ? 'Balanced' : 'Not Balanced' }}
                  </span>
                </p>
              </div>
            </div>
          </div>

          <!-- Journal Entries View -->
          <div v-if="activeView === 'journal-entries'" class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-lg font-medium text-gray-900">Journal Entries</h2>
              <div class="flex space-x-3">
                <select
                  v-model="selectedStatus"
                  class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                  @change="fetchJournalEntries"
                >
                  <option value="">All Status</option>
                  <option value="draft">Draft</option>
                  <option value="posted">Posted</option>
                  <option value="reversed">Reversed</option>
                </select>
                <input
                  v-model="journalSearchQuery"
                  type="text"
                  placeholder="Search entries..."
                  class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                  @input="debouncedJournalSearch"
                />
                <button
                  @click="showJournalModal = true"
                  class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                >
                  New Entry
                </button>
              </div>
            </div>

            <div v-if="loadingJournalEntries" class="text-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
              <p class="mt-2 text-gray-600">Loading journal entries...</p>
            </div>

            <div v-else-if="journalEntries.length === 0" class="text-center py-8 text-gray-500">
              No journal entries found.
            </div>

            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Entry #
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Date
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Description
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Type
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Amount
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Status
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="entry in journalEntries" :key="entry.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ entry.entry_number }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatDate(entry.entry_date) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                      <div class="max-w-xs truncate">{{ entry.description }}</div>
                      <div v-if="entry.reference" class="text-xs text-gray-500">Ref: {{ entry.reference }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        :class="[
                          'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                          getEntryTypeColor(entry.entry_type)
                        ]"
                      >
                        {{ formatEntryType(entry.entry_type) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      ${{ formatNumber(entry.total_debit) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        :class="[
                          'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                          getStatusColor(entry.status)
                        ]"
                      >
                        {{ formatStatus(entry.status) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <div class="flex justify-end space-x-2">
                        <button
                          @click="viewJournalEntry(entry)"
                          class="text-indigo-600 hover:text-indigo-900"
                        >
                          View
                        </button>
                        <button
                          v-if="entry.status === 'draft'"
                          @click="editJournalEntry(entry)"
                          class="text-blue-600 hover:text-blue-900"
                        >
                          Edit
                        </button>
                        <button
                          v-if="entry.status === 'draft'"
                          @click="postJournalEntry(entry)"
                          class="text-green-600 hover:text-green-900"
                        >
                          Post
                        </button>
                        <button
                          v-if="entry.status === 'posted'"
                          @click="reverseJournalEntry(entry)"
                          class="text-red-600 hover:text-red-900"
                        >
                          Reverse
                        </button>
                        <button
                          v-if="entry.status === 'draft'"
                          @click="deleteJournalEntry(entry)"
                          class="text-red-600 hover:text-red-900"
                        >
                          Delete
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Banking View -->
          <div v-if="activeView === 'banking'" class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-lg font-medium text-gray-900">Bank Accounts</h2>
              <button
                @click="showBankAccountModal = true"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium"
              >
                New Bank Account
              </button>
            </div>

            <div v-if="loadingBankAccounts" class="text-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
              <p class="mt-2 text-gray-600">Loading bank accounts...</p>
            </div>

            <div v-else-if="bankAccounts.length === 0" class="text-center py-8 text-gray-500">
              No bank accounts found.
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div
                v-for="account in bankAccounts"
                :key="account.id"
                class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow"
              >
                <div class="flex justify-between items-start mb-4">
                  <div>
                    <h3 class="text-lg font-medium text-gray-900">{{ account.account_name }}</h3>
                    <p class="text-sm text-gray-600">{{ account.bank_name }}</p>
                    <p class="text-xs text-gray-500">{{ account.formatted_account_number }}</p>
                  </div>
                  <span
                    :class="[
                      'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                      account.is_active
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800'
                    ]"
                  >
                    {{ account.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </div>

                <div class="mb-4">
                  <div class="text-2xl font-bold text-gray-900">
                    ${{ formatNumber(account.current_balance) }}
                  </div>
                  <div class="text-sm text-gray-600">Current Balance</div>
                </div>

                <div class="mb-4">
                  <span
                    :class="[
                      'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                      getBankAccountTypeColor(account.account_type)
                    ]"
                  >
                    {{ formatBankAccountType(account.account_type) }}
                  </span>
                </div>

                <div class="grid grid-cols-2 gap-2">
                  <button
                    @click="viewBankAccount(account)"
                    class="bg-indigo-600 text-white py-2 px-3 rounded text-sm hover:bg-indigo-700"
                  >
                    View
                  </button>
                  <button
                    @click="editBankAccount(account)"
                    class="bg-blue-600 text-white py-2 px-3 rounded text-sm hover:bg-blue-700"
                  >
                    Edit
                  </button>
                  <button
                    @click="reconcileBankAccount(account)"
                    class="bg-green-600 text-white py-2 px-3 rounded text-sm hover:bg-green-700"
                  >
                    Reconcile
                  </button>
                  <button
                    @click="deleteBankAccount(account)"
                    class="bg-red-600 text-white py-2 px-3 rounded text-sm hover:bg-red-700"
                  >
                    Delete
                  </button>
                </div>

                <div v-if="account.unreconciled_transactions_count > 0" class="mt-2">
                  <div class="text-xs text-yellow-600 bg-yellow-100 px-2 py-1 rounded">
                    {{ account.unreconciled_transactions_count }} unreconciled transactions
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
    </div>

    <!-- Chart of Account Modal -->
    <div v-if="showAccountModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">
              {{ editingAccount ? 'Edit Account' : 'Create New Account' }}
            </h3>
            <button @click="closeAccountModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <form @submit.prevent="saveAccount" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Account Code *</label>
                <input
                  v-model="accountForm.account_code"
                  type="text"
                  required
                  :disabled="editingAccount"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:bg-gray-100"
                  placeholder="e.g., 1000"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Account Name *</label>
                <input
                  v-model="accountForm.account_name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  placeholder="e.g., Cash"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Account Type *</label>
                <select
                  v-model="accountForm.account_type"
                  required
                  @change="updateSubtypeOptions"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                  <option value="">Select Type</option>
                  <option value="asset">Asset</option>
                  <option value="liability">Liability</option>
                  <option value="equity">Equity</option>
                  <option value="revenue">Revenue</option>
                  <option value="expense">Expense</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Account Subtype *</label>
                <select
                  v-model="accountForm.account_subtype"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                  <option value="">Select Subtype</option>
                  <option v-for="subtype in availableSubtypes" :key="subtype.value" :value="subtype.value">
                    {{ subtype.label }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Parent Account</label>
                <select
                  v-model="accountForm.parent_account_id"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                  <option value="">No Parent (Top Level)</option>
                  <option v-for="account in parentAccountOptions" :key="account.id" :value="account.id">
                    {{ account.account_code }} - {{ account.account_name }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Opening Balance</label>
                <input
                  v-model="accountForm.opening_balance"
                  type="number"
                  step="0.01"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  placeholder="0.00"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea
                v-model="accountForm.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="Optional description for this account"
              ></textarea>
            </div>

            <div class="flex items-center space-x-4">
              <div class="flex items-center">
                <input
                  v-model="accountForm.is_active"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 block text-sm text-gray-900">Active</label>
              </div>

              <div class="flex items-center">
                <input
                  v-model="accountForm.is_system_account"
                  type="checkbox"
                  :disabled="editingAccount"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded disabled:bg-gray-100"
                />
                <label class="ml-2 block text-sm text-gray-900">System Account</label>
              </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
              <button
                type="button"
                @click="closeAccountModal"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="savingAccount"
                class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ savingAccount ? 'Saving...' : (editingAccount ? 'Update Account' : 'Create Account') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Bank Account Modal -->
    <div v-if="showBankAccountModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">
              {{ editingBankAccount ? 'Edit Bank Account' : 'New Bank Account' }}
            </h3>
            <button
              @click="closeBankAccountModal"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <form @submit.prevent="saveBankAccount" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Account Name *</label>
                <input
                  v-model="bankAccountForm.account_name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name *</label>
                <input
                  v-model="bankAccountForm.bank_name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Account Number *</label>
                <input
                  v-model="bankAccountForm.account_number"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Account Type *</label>
                <select
                  v-model="bankAccountForm.account_type"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                  <option value="">Select Type</option>
                  <option value="checking">Checking</option>
                  <option value="savings">Savings</option>
                  <option value="credit_card">Credit Card</option>
                  <option value="line_of_credit">Line of Credit</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Routing Number</label>
                <input
                  v-model="bankAccountForm.routing_number"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Opening Balance</label>
                <input
                  v-model="bankAccountForm.opening_balance"
                  type="number"
                  step="0.01"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Opening Date</label>
                <input
                  v-model="bankAccountForm.opening_date"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Chart Account *</label>
                <select
                  v-model="bankAccountForm.chart_account_id"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                  <option value="">Select Chart Account</option>
                  <option v-for="account in assetAccounts" :key="account.id" :value="account.id">
                    {{ account.account_code }} - {{ account.account_name }} ({{ formatAccountSubtype(account.account_subtype) }})
                  </option>
                </select>
                <p class="text-xs text-gray-500 mt-1">
                  Select the chart of accounts entry this bank account should be linked to.<br>
                  <span class="font-medium">Asset accounts</span> for checking/savings, <span class="font-medium">Liability accounts</span> for credit cards/lines of credit.
                </p>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea
                v-model="bankAccountForm.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
              ></textarea>
            </div>

            <div class="flex items-center">
              <input
                v-model="bankAccountForm.is_active"
                type="checkbox"
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
              />
              <label class="ml-2 block text-sm text-gray-900">Active</label>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
              <button
                type="button"
                @click="closeBankAccountModal"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="savingBankAccount"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ savingBankAccount ? 'Saving...' : (editingBankAccount ? 'Update' : 'Create') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Bank Account View Modal -->
    <div v-if="showBankAccountViewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Bank Account Details</h3>
            <button
              @click="closeBankAccountViewModal"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <div v-if="selectedBankAccount" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Account Name</label>
                <p class="mt-1 text-sm text-gray-900">{{ selectedBankAccount.account_name }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Bank Name</label>
                <p class="mt-1 text-sm text-gray-900">{{ selectedBankAccount.bank_name }}</p>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Account Number</label>
                <p class="mt-1 text-sm text-gray-900">{{ selectedBankAccount.account_number }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Account Type</label>
                <p class="mt-1 text-sm text-gray-900">{{ selectedBankAccount.account_type }}</p>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Current Balance</label>
                <p class="mt-1 text-sm text-gray-900">{{ selectedBankAccount.formatted_balance }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Opening Date</label>
                <p class="mt-1 text-sm text-gray-900">{{ selectedBankAccount.opening_date || 'Not set' }}</p>
              </div>
            </div>

            <div v-if="selectedBankAccount.description">
              <label class="block text-sm font-medium text-gray-700">Description</label>
              <p class="mt-1 text-sm text-gray-900">{{ selectedBankAccount.description }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Status</label>
              <span
                :class="[
                  'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                  selectedBankAccount.is_active
                    ? 'bg-green-100 text-green-800'
                    : 'bg-red-100 text-red-800'
                ]"
              >
                {{ selectedBankAccount.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reconcile Modal -->
    <div v-if="showReconcileModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-10 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">
              Reconcile {{ selectedBankAccount?.account_name }}
            </h3>
            <button
              @click="closeReconcileModal"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Statement Date</label>
                <input
                  v-model="reconcileForm.statement_date"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  required
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Statement Balance</label>
                <input
                  v-model="reconcileForm.statement_balance"
                  type="number"
                  step="0.01"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  placeholder="0.00"
                  required
                />
              </div>
            </div>

            <div>
              <h4 class="text-md font-medium text-gray-900 mb-2">Select Transactions to Reconcile</h4>

              <div v-if="loadingTransactions" class="text-center py-4">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600 mx-auto"></div>
                <p class="mt-2 text-sm text-gray-600">Loading transactions...</p>
              </div>

              <div v-else-if="bankTransactions.length === 0" class="text-center py-4 text-gray-500">
                No unreconciled transactions found.
              </div>

              <div v-else class="max-h-64 overflow-y-auto border border-gray-200 rounded">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                        <input
                          type="checkbox"
                          @change="toggleAllTransactions"
                          class="rounded border-gray-300"
                        />
                      </th>
                      <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                      <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                      <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                      <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="transaction in bankTransactions.filter(t => !t.is_reconciled)" :key="transaction.id">
                      <td class="px-3 py-2">
                        <input
                          type="checkbox"
                          :value="transaction.id"
                          v-model="reconcileForm.selected_transactions"
                          class="rounded border-gray-300"
                        />
                      </td>
                      <td class="px-3 py-2 text-sm text-gray-900">{{ transaction.transaction_date }}</td>
                      <td class="px-3 py-2 text-sm text-gray-900">{{ transaction.description }}</td>
                      <td class="px-3 py-2 text-sm text-gray-900">
                        <span :class="transaction.transaction_type === 'credit' ? 'text-green-600' : 'text-red-600'">
                          {{ transaction.transaction_type === 'credit' ? '+' : '-' }}${{ Number(transaction.amount).toFixed(2) }}
                        </span>
                      </td>
                      <td class="px-3 py-2 text-sm text-gray-900">{{ transaction.transaction_type }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
              <button
                @click="closeReconcileModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200"
              >
                Cancel
              </button>
              <button
                @click="submitReconciliation"
                :disabled="!reconcileForm.statement_date || !reconcileForm.statement_balance || reconcileForm.selected_transactions.length === 0"
                class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Reconcile
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Journal Entry Modal -->
    <div v-if="showJournalModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-10 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">
              {{ editingJournalEntry ? 'Edit Journal Entry' : 'New Journal Entry' }}
            </h3>
            <button
              @click="closeJournalModal"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <form @submit.prevent="saveJournalEntry" class="space-y-6">
            <!-- Header Information -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Entry Date *</label>
                <input
                  v-model="journalEntryForm.entry_date"
                  type="date"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
                <input
                  v-model="journalEntryForm.reference"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Entry Type</label>
                <select
                  v-model="journalEntryForm.entry_type"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                  <option value="manual">Manual Entry</option>
                  <option value="adjustment">Adjustment Entry</option>
                  <option value="closing">Closing Entry</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
              <textarea
                v-model="journalEntryForm.description"
                rows="2"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
              ></textarea>
            </div>

            <!-- Journal Entry Lines -->
            <div>
              <div class="flex justify-between items-center mb-3">
                <h4 class="text-md font-medium text-gray-900">Journal Entry Lines</h4>
                <button
                  type="button"
                  @click="addJournalLine"
                  class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700"
                >
                  Add Line
                </button>
              </div>

              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
                      <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                      <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Debit</th>
                      <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Credit</th>
                      <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(line, index) in journalEntryForm.lines" :key="index">
                      <td class="px-3 py-2">
                        <select
                          v-model="line.account_id"
                          required
                          class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                          <option value="">Select Account</option>
                          <option v-for="account in accounts" :key="account.id" :value="account.id">
                            {{ account.account_code }} - {{ account.account_name }}
                          </option>
                        </select>
                      </td>
                      <td class="px-3 py-2">
                        <input
                          v-model="line.description"
                          type="text"
                          class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                      </td>
                      <td class="px-3 py-2">
                        <input
                          v-model="line.debit_amount"
                          type="number"
                          step="0.01"
                          min="0"
                          @input="updateLineCredit(index)"
                          class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                      </td>
                      <td class="px-3 py-2">
                        <input
                          v-model="line.credit_amount"
                          type="number"
                          step="0.01"
                          min="0"
                          @input="updateLineDebit(index)"
                          class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                      </td>
                      <td class="px-3 py-2">
                        <button
                          type="button"
                          @click="removeJournalLine(index)"
                          class="text-red-600 hover:text-red-900"
                        >
                          Remove
                        </button>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot class="bg-gray-50">
                    <tr>
                      <td colspan="2" class="px-3 py-2 text-right font-medium">Totals:</td>
                      <td class="px-3 py-2 font-medium">${{ totalDebits.toFixed(2) }}</td>
                      <td class="px-3 py-2 font-medium">${{ totalCredits.toFixed(2) }}</td>
                      <td class="px-3 py-2">
                        <span :class="[
                          'text-xs px-2 py-1 rounded',
                          isBalanced ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        ]">
                          {{ isBalanced ? 'Balanced' : 'Out of Balance' }}
                        </span>
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
              <button
                type="button"
                @click="closeJournalModal"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="savingJournalEntry || !isBalanced"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ savingJournalEntry ? 'Saving...' : (editingJournalEntry ? 'Update' : 'Create') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import AccountTreeNode from './AccountTreeNode.vue';

// Reactive data
const activeView = ref('tree');
const selectedAccountType = ref('');
const searchQuery = ref('');
const accounts = ref([]);
const accountTree = ref([]);
const balances = ref({});
const loadingAccounts = ref(false);
const loadingTree = ref(false);
const loadingBalances = ref(false);
const showAccountModal = ref(false);
const editingAccount = ref(null);
const savingAccount = ref(false);
const journalEntries = ref([]);
const loadingJournalEntries = ref(false);
const showJournalModal = ref(false);
const selectedStatus = ref('');
const journalSearchQuery = ref('');
const bankAccounts = ref([]);
const loadingBankAccounts = ref(false);
const showBankAccountModal = ref(false);
const showBankAccountViewModal = ref(false);
const showReconcileModal = ref(false);
const editingBankAccount = ref(null);
const selectedBankAccount = ref(null);
const savingBankAccount = ref(false);
const editingJournalEntry = ref(null);
const savingJournalEntry = ref(false);
const assetAccounts = ref([]); // Contains both asset and liability accounts for bank account linking
const bankTransactions = ref([]);
const loadingTransactions = ref(false);
const reconcileForm = ref({
  statement_date: '',
  statement_balance: '',
  selected_transactions: []
});

// Form data
const accountForm = ref({
  account_code: '',
  account_name: '',
  account_type: '',
  account_subtype: '',
  parent_account_id: '',
  opening_balance: 0,
  description: '',
  is_active: true,
  is_system_account: false
});

const bankAccountForm = ref({
  account_name: '',
  bank_name: '',
  account_number: '',
  account_type: '',
  chart_account_id: '',
  routing_number: '',
  swift_code: '',
  iban: '',
  opening_balance: 0,
  opening_date: '',
  description: '',
  is_active: true
});

// Account subtype options
const accountSubtypes = {
  asset: [
    { value: 'current_asset', label: 'Current Asset' },
    { value: 'fixed_asset', label: 'Fixed Asset' },
    { value: 'other_asset', label: 'Other Asset' }
  ],
  liability: [
    { value: 'current_liability', label: 'Current Liability' },
    { value: 'long_term_liability', label: 'Long-term Liability' }
  ],
  equity: [
    { value: 'equity', label: 'Equity' }
  ],
  revenue: [
    { value: 'revenue', label: 'Revenue' }
  ],
  expense: [
    { value: 'expense', label: 'Expense' },
    { value: 'cost_of_goods_sold', label: 'Cost of Goods Sold' }
  ]
};

const availableSubtypes = ref([]);
const parentAccountOptions = computed(() => {
  return accounts.value.filter(account =>
    account.account_type === accountForm.value.account_type &&
    account.id !== editingAccount.value?.id
  );
});

const journalEntryForm = ref({
  entry_date: '',
  reference: '',
  description: '',
  entry_type: 'manual',
  lines: []
});

// Computed properties
const totalDebits = computed(() => {
  return journalEntryForm.value.lines.reduce((total, line) => {
    return total + (parseFloat(line.debit_amount) || 0);
  }, 0);
});

const totalCredits = computed(() => {
  return journalEntryForm.value.lines.reduce((total, line) => {
    return total + (parseFloat(line.credit_amount) || 0);
  }, 0);
});

const isBalanced = computed(() => {
  return Math.abs(totalDebits.value - totalCredits.value) < 0.01;
});

// Account types
const accountTypes = [
  { value: 'asset', label: 'Assets' },
  { value: 'liability', label: 'Liabilities' },
  { value: 'equity', label: 'Equity' },
  { value: 'revenue', label: 'Revenue' },
  { value: 'expense', label: 'Expenses' }
];

// Methods
const fetchAccounts = async () => {
  loadingAccounts.value = true;
  try {
    const params = {
      flat: true,
      per_page: 100
    };

    if (selectedAccountType.value) {
      params.account_type = selectedAccountType.value;
    }

    if (searchQuery.value) {
      params.search = searchQuery.value;
    }

    const response = await axios.get('/api/accounts', { params });
    accounts.value = response.data.data;
  } catch (error) {
    console.error('Error fetching accounts:', error);
  } finally {
    loadingAccounts.value = false;
  }
};

const fetchAccountTree = async () => {
  loadingTree.value = true;
  try {
    const params = {};

    if (selectedAccountType.value) {
      params.account_type = selectedAccountType.value;
    }

    const response = await axios.get('/api/accounts/tree/structure', { params });
    accountTree.value = response.data;
  } catch (error) {
    console.error('Error fetching account tree:', error);
  } finally {
    loadingTree.value = false;
  }
};

const fetchBalances = async () => {
  loadingBalances.value = true;
  try {
    const response = await axios.get('/api/accounts/balances/summary');
    balances.value = response.data;
  } catch (error) {
    console.error('Error fetching balances:', error);
  } finally {
    loadingBalances.value = false;
  }
};

const fetchJournalEntries = async () => {
  loadingJournalEntries.value = true;
  try {
    const params = {
      per_page: 50
    };

    if (selectedStatus.value) {
      params.status = selectedStatus.value;
    }

    if (journalSearchQuery.value) {
      params.search = journalSearchQuery.value;
    }

    const response = await axios.get('/api/journal-entries', { params });
    journalEntries.value = response.data.data;
  } catch (error) {
    console.error('Error fetching journal entries:', error);
  } finally {
    loadingJournalEntries.value = false;
  }
};

const fetchBankAccounts = async () => {
  loadingBankAccounts.value = true;
  try {
    const response = await axios.get('/api/bank-accounts');
    bankAccounts.value = response.data;
  } catch (error) {
    console.error('Error fetching bank accounts:', error);
  } finally {
    loadingBankAccounts.value = false;
  }
};

const fetchAssetAccounts = async () => {
  try {
    // Fetch both asset and liability accounts since bank accounts can be linked to both
    // Asset accounts: for checking, savings accounts
    // Liability accounts: for credit cards, lines of credit
    const [assetResponse, liabilityResponse] = await Promise.all([
      axios.get('/api/accounts', { params: { account_type: 'asset' } }),
      axios.get('/api/accounts', { params: { account_type: 'liability' } })
    ]);

    const assets = assetResponse.data.data || assetResponse.data;
    const liabilities = liabilityResponse.data.data || liabilityResponse.data;

    // Combine and sort by account code
    assetAccounts.value = [...assets, ...liabilities].sort((a, b) =>
      a.account_code.localeCompare(b.account_code)
    );
  } catch (error) {
    console.error('Error fetching accounts for bank accounts:', error);
  }
};

const editAccount = (account) => {
  editingAccount.value = account;
  accountForm.value = {
    account_code: account.account_code,
    account_name: account.account_name,
    account_type: account.account_type,
    account_subtype: account.account_subtype,
    parent_account_id: account.parent_account_id || '',
    opening_balance: account.opening_balance || 0,
    description: account.description || '',
    is_active: account.is_active,
    is_system_account: account.is_system_account || false
  };

  // Update available subtypes
  updateSubtypeOptions();

  showAccountModal.value = true;
};

const deleteAccount = async (account) => {
  if (confirm(`Are you sure you want to delete the account "${account.account_name}"?`)) {
    try {
      await axios.delete(`/api/accounts/${account.id}`);

      // Refresh data
      if (activeView.value === 'tree') {
        fetchAccountTree();
      } else {
        fetchAccounts();
      }

      fetchBalances();
    } catch (error) {
      console.error('Error deleting account:', error);
      alert(error.response?.data?.message || 'Failed to delete account');
    }
  }
};

// Account form methods
const saveAccount = async () => {
  savingAccount.value = true;
  try {
    if (editingAccount.value) {
      await axios.put(`/api/accounts/${editingAccount.value.id}`, accountForm.value);
    } else {
      await axios.post('/api/accounts', accountForm.value);
    }

    // Refresh data
    if (activeView.value === 'tree') {
      fetchAccountTree();
    } else {
      fetchAccounts();
    }

    fetchBalances();
    closeAccountModal();
  } catch (error) {
    console.error('Error saving account:', error);
    alert(error.response?.data?.message || 'Failed to save account');
  } finally {
    savingAccount.value = false;
  }
};

const closeAccountModal = () => {
  showAccountModal.value = false;
  editingAccount.value = null;
  accountForm.value = {
    account_code: '',
    account_name: '',
    account_type: '',
    account_subtype: '',
    parent_account_id: '',
    opening_balance: 0,
    description: '',
    is_active: true,
    is_system_account: false
  };
  availableSubtypes.value = [];
};

const updateSubtypeOptions = () => {
  const accountType = accountForm.value.account_type;
  if (accountType && accountSubtypes[accountType]) {
    availableSubtypes.value = accountSubtypes[accountType];
  } else {
    availableSubtypes.value = [];
  }

  // Reset subtype if it's not valid for the new type
  if (!availableSubtypes.value.find(st => st.value === accountForm.value.account_subtype)) {
    accountForm.value.account_subtype = '';
  }
};

const getAccountTypeColor = (type) => {
  const colors = {
    asset: 'bg-blue-100 text-blue-800',
    liability: 'bg-red-100 text-red-800',
    equity: 'bg-green-100 text-green-800',
    revenue: 'bg-purple-100 text-purple-800',
    expense: 'bg-yellow-100 text-yellow-800'
  };
  return colors[type] || 'bg-gray-100 text-gray-800';
};

const formatAccountType = (type) => {
  const types = {
    asset: 'Asset',
    liability: 'Liability',
    equity: 'Equity',
    revenue: 'Revenue',
    expense: 'Expense'
  };
  return types[type] || type;
};



const formatNumber = (number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(number || 0);
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString();
};

const viewJournalEntry = (entry) => {
  // TODO: Implement view journal entry functionality
  console.log('View journal entry:', entry);
};

const editJournalEntry = async (entry) => {
  try {
    const response = await axios.get(`/api/journal-entries/${entry.id}`);
    const journalEntry = response.data;

    editingJournalEntry.value = journalEntry;
    journalEntryForm.value = {
      entry_date: journalEntry.entry_date,
      reference: journalEntry.reference || '',
      description: journalEntry.description,
      entry_type: journalEntry.entry_type,
      lines: journalEntry.journal_entry_lines.map(line => ({
        id: line.id,
        account_id: line.account_id,
        description: line.description || '',
        debit_amount: line.debit_amount || 0,
        credit_amount: line.credit_amount || 0
      }))
    };
    showJournalModal.value = true;
  } catch (error) {
    console.error('Error loading journal entry:', error);
    alert('Failed to load journal entry');
  }
};

const postJournalEntry = async (entry) => {
  if (confirm(`Are you sure you want to post journal entry "${entry.entry_number}"?`)) {
    try {
      await axios.post(`/api/journal-entries/${entry.id}/post`);
      fetchJournalEntries();
      fetchBalances();
    } catch (error) {
      console.error('Error posting journal entry:', error);
      alert(error.response?.data?.message || 'Failed to post journal entry');
    }
  }
};

const reverseJournalEntry = async (entry) => {
  const reason = prompt('Enter reason for reversal:');
  if (reason) {
    try {
      await axios.post(`/api/journal-entries/${entry.id}/reverse`, {
        reason: reason,
        reversal_date: new Date().toISOString().split('T')[0]
      });
      fetchJournalEntries();
      fetchBalances();
    } catch (error) {
      console.error('Error reversing journal entry:', error);
      alert(error.response?.data?.message || 'Failed to reverse journal entry');
    }
  }
};

const deleteJournalEntry = async (entry) => {
  if (confirm(`Are you sure you want to delete journal entry "${entry.entry_number}"?`)) {
    try {
      await axios.delete(`/api/journal-entries/${entry.id}`);
      fetchJournalEntries();
    } catch (error) {
      console.error('Error deleting journal entry:', error);
      alert(error.response?.data?.message || 'Failed to delete journal entry');
    }
  }
};

const getEntryTypeColor = (type) => {
  const colors = {
    manual: 'bg-blue-100 text-blue-800',
    automatic: 'bg-green-100 text-green-800',
    adjustment: 'bg-yellow-100 text-yellow-800',
    closing: 'bg-purple-100 text-purple-800'
  };
  return colors[type] || 'bg-gray-100 text-gray-800';
};

const getStatusColor = (status) => {
  const colors = {
    draft: 'bg-gray-100 text-gray-800',
    posted: 'bg-green-100 text-green-800',
    reversed: 'bg-red-100 text-red-800'
  };
  return colors[status] || 'bg-gray-100 text-gray-800';
};

const formatEntryType = (type) => {
  const types = {
    manual: 'Manual',
    automatic: 'Automatic',
    adjustment: 'Adjustment',
    closing: 'Closing'
  };
  return types[type] || type;
};

const formatStatus = (status) => {
  const statuses = {
    draft: 'Draft',
    posted: 'Posted',
    reversed: 'Reversed'
  };
  return statuses[status] || status;
};

const viewBankAccount = (account) => {
  showBankAccountViewModal.value = true;
  selectedBankAccount.value = account;
};

const editBankAccount = async (account) => {
  editingBankAccount.value = account;

  console.log('Edit bank account called with:', account);
  console.log('Opening date from account:', account.opening_date);

  // Format opening_date for HTML date input (YYYY-MM-DD)
  let formattedOpeningDate = '';
  if (account.opening_date && account.opening_date !== null && account.opening_date !== '') {
    // Handle different date formats that might come from the API
    try {
      // If it's already in YYYY-MM-DD format, use it directly
      if (typeof account.opening_date === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(account.opening_date)) {
        formattedOpeningDate = account.opening_date;
      } else {
        // Otherwise, try to parse and format it
        const date = new Date(account.opening_date);
        if (!isNaN(date.getTime())) {
          formattedOpeningDate = date.toISOString().split('T')[0];
        }
      }
    } catch (error) {
      console.warn('Error formatting opening date:', error);
      formattedOpeningDate = '';
    }
  }

  console.log('Formatted opening date:', formattedOpeningDate);

  bankAccountForm.value = {
    account_name: account.account_name,
    bank_name: account.bank_name,
    account_number: account.account_number,
    account_type: account.account_type,
    chart_account_id: account.chart_account_id,
    routing_number: account.routing_number || '',
    swift_code: account.swift_code || '',
    iban: account.iban || '',
    opening_balance: account.opening_balance,
    opening_date: formattedOpeningDate,
    description: account.description || '',
    is_active: account.is_active
  };

  console.log('Bank account form set to:', bankAccountForm.value);
  console.log('Opening date in form:', bankAccountForm.value.opening_date);

  showBankAccountModal.value = true;
};

const deleteBankAccount = async (account) => {
  if (confirm(`Are you sure you want to delete bank account "${account.account_name}"?`)) {
    try {
      await axios.delete(`/api/bank-accounts/${account.id}`);
      fetchBankAccounts();
    } catch (error) {
      console.error('Error deleting bank account:', error);
      alert(error.response?.data?.message || 'Failed to delete bank account');
    }
  }
};

const fetchBankTransactions = async (bankAccountId) => {
  loadingTransactions.value = true;
  try {
    const response = await axios.get(`/api/bank-accounts/${bankAccountId}/transactions`);
    bankTransactions.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error fetching bank transactions:', error);
    bankTransactions.value = [];
  } finally {
    loadingTransactions.value = false;
  }
};

const reconcileBankAccount = (account) => {
  showReconcileModal.value = true;
  selectedBankAccount.value = account;
  reconcileForm.value = {
    statement_date: '',
    statement_balance: '',
    selected_transactions: []
  };
  fetchBankTransactions(account.id);
};

const submitReconciliation = async () => {
  if (!selectedBankAccount.value) return;

  try {
    const response = await axios.post(`/api/bank-accounts/${selectedBankAccount.value.id}/reconcile`, {
      statement_date: reconcileForm.value.statement_date,
      statement_balance: reconcileForm.value.statement_balance,
      transaction_ids: reconcileForm.value.selected_transactions
    });

    alert('Bank account reconciled successfully!');
    showReconcileModal.value = false;
    fetchBankAccounts(); // Refresh the bank accounts list
  } catch (error) {
    console.error('Error reconciling bank account:', error);
    alert(error.response?.data?.message || 'Failed to reconcile bank account');
  }
};

const toggleAllTransactions = (event) => {
  const unreconciledTransactions = bankTransactions.value.filter(t => !t.is_reconciled);
  if (event.target.checked) {
    reconcileForm.value.selected_transactions = unreconciledTransactions.map(t => t.id);
  } else {
    reconcileForm.value.selected_transactions = [];
  }
};

const getBankAccountTypeColor = (type) => {
  const colors = {
    checking: 'bg-blue-100 text-blue-800',
    savings: 'bg-green-100 text-green-800',
    credit_card: 'bg-red-100 text-red-800',
    line_of_credit: 'bg-yellow-100 text-yellow-800',
    other: 'bg-gray-100 text-gray-800'
  };
  return colors[type] || 'bg-gray-100 text-gray-800';
};

const formatBankAccountType = (type) => {
  const types = {
    checking: 'Checking',
    savings: 'Savings',
    credit_card: 'Credit Card',
    line_of_credit: 'Line of Credit',
    other: 'Other'
  };
  return types[type] || type;
};

const formatAccountSubtype = (subtype) => {
  const subtypes = {
    current_asset: 'Current Asset',
    fixed_asset: 'Fixed Asset',
    other_asset: 'Other Asset',
    current_liability: 'Current Liability',
    long_term_liability: 'Long-term Liability',
    other_liability: 'Other Liability',
    equity: 'Equity',
    operating_revenue: 'Operating Revenue',
    other_revenue: 'Other Revenue',
    cost_of_goods_sold: 'Cost of Goods Sold',
    operating_expense: 'Operating Expense',
    other_expense: 'Other Expense'
  };
  return subtypes[subtype] || subtype?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) || '';
};

// Debounced search
let searchTimeout;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchAccounts();
  }, 300);
};

let journalSearchTimeout;
const debouncedJournalSearch = () => {
  clearTimeout(journalSearchTimeout);
  journalSearchTimeout = setTimeout(() => {
    fetchJournalEntries();
  }, 300);
};

// Watch for view changes
const refreshData = () => {
  if (activeView.value === 'tree') {
    fetchAccountTree();
  } else if (activeView.value === 'list') {
    fetchAccounts();
  } else if (activeView.value === 'balances') {
    fetchBalances();
  } else if (activeView.value === 'journal-entries') {
    fetchJournalEntries();
  } else if (activeView.value === 'banking') {
    fetchBankAccounts();
  }
};

// Modal management methods
const closeBankAccountModal = () => {
  showBankAccountModal.value = false;
  editingBankAccount.value = null;
  bankAccountForm.value = {
    account_name: '',
    bank_name: '',
    account_number: '',
    account_type: '',
    chart_account_id: '',
    routing_number: '',
    swift_code: '',
    iban: '',
    opening_balance: 0,
    opening_date: '',
    description: '',
    is_active: true
  };
};

const closeJournalModal = () => {
  showJournalModal.value = false;
  editingJournalEntry.value = null;
  journalEntryForm.value = {
    entry_date: '',
    reference: '',
    description: '',
    entry_type: 'manual',
    lines: []
  };
};

const closeBankAccountViewModal = () => {
  showBankAccountViewModal.value = false;
  selectedBankAccount.value = null;
};

const closeReconcileModal = () => {
  showReconcileModal.value = false;
  selectedBankAccount.value = null;
  bankTransactions.value = [];
  reconcileForm.value = {
    statement_date: '',
    statement_balance: '',
    selected_transactions: []
  };
};

const saveBankAccount = async () => {
  savingBankAccount.value = true;
  try {
    if (editingBankAccount.value) {
      await axios.put(`/api/bank-accounts/${editingBankAccount.value.id}`, bankAccountForm.value);
    } else {
      await axios.post('/api/bank-accounts', bankAccountForm.value);
    }
    fetchBankAccounts();
    closeBankAccountModal();
  } catch (error) {
    console.error('Error saving bank account:', error);
    alert(error.response?.data?.message || 'Failed to save bank account');
  } finally {
    savingBankAccount.value = false;
  }
};

const saveJournalEntry = async () => {
  savingJournalEntry.value = true;
  try {
    const data = {
      ...journalEntryForm.value,
      total_debit: totalDebits.value,
      total_credit: totalCredits.value
    };

    if (editingJournalEntry.value) {
      await axios.put(`/api/journal-entries/${editingJournalEntry.value.id}`, data);
    } else {
      await axios.post('/api/journal-entries', data);
    }
    fetchJournalEntries();
    closeJournalModal();
  } catch (error) {
    console.error('Error saving journal entry:', error);
    alert(error.response?.data?.message || 'Failed to save journal entry');
  } finally {
    savingJournalEntry.value = false;
  }
};

const addJournalLine = () => {
  journalEntryForm.value.lines.push({
    account_id: '',
    description: '',
    debit_amount: 0,
    credit_amount: 0
  });
};

const removeJournalLine = (index) => {
  journalEntryForm.value.lines.splice(index, 1);
};

const updateLineCredit = (index) => {
  const line = journalEntryForm.value.lines[index];
  if (line.debit_amount > 0) {
    line.credit_amount = 0;
  }
};

const updateLineDebit = (index) => {
  const line = journalEntryForm.value.lines[index];
  if (line.credit_amount > 0) {
    line.debit_amount = 0;
  }
};

// Watch for account type changes
const onAccountTypeChange = () => {
  if (activeView.value === 'tree') {
    fetchAccountTree();
  } else if (activeView.value === 'list') {
    fetchAccounts();
  }
};

// Initialize forms with default values
const initializeForms = () => {
  const today = new Date().toISOString().split('T')[0];
  journalEntryForm.value.entry_date = today;
  bankAccountForm.value.opening_date = today;

  // Add initial journal entry lines
  if (journalEntryForm.value.lines.length === 0) {
    addJournalLine();
    addJournalLine();
  }
};

// Lifecycle
onMounted(() => {
  console.log('Accounting component mounted');
  try {
    fetchAccountTree();
    fetchBalances();
    fetchAssetAccounts();
    initializeForms();
  } catch (error) {
    console.error('Error in onMounted:', error);
  }
});

// Watchers
watch(activeView, () => {
  refreshData();
});

watch(selectedAccountType, () => {
  onAccountTypeChange();
});
</script>
