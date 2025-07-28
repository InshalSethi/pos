<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-[9999] overflow-y-auto">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-black bg-opacity-50" @click="$emit('close')"></div>
      
      <!-- Modal -->
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-6xl bg-white rounded-lg shadow-xl">
          <!-- Header -->
          <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-lg">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                </div>
                <div v-if="customer">
                  <h3 class="text-xl font-semibold text-white">{{ customer.name }}</h3>
                  <p class="text-blue-100">Customer ID: #{{ customer.id }}</p>
                </div>
                <div v-else>
                  <h3 class="text-xl font-semibold text-white">Customer Details</h3>
                  <p class="text-blue-100">Loading customer information...</p>
                </div>
              </div>
              <div class="flex items-center space-x-3">
                <span v-if="customer" :class="customer.is_active 
                  ? 'inline-flex px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800' 
                  : 'inline-flex px-3 py-1 text-sm font-medium rounded-full bg-red-100 text-red-800'">
                  {{ customer.is_active ? 'Active' : 'Inactive' }}
                </span>
                <button @click="$emit('close')" class="text-white hover:text-gray-200 transition-colors">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Content -->
          <div class="max-h-[70vh] overflow-y-auto">
            <div v-if="customer" class="p-6">
              <!-- Customer Stats -->
              <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                      </div>
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-green-600">Total Purchases</p>
                      <p class="text-lg font-semibold text-green-900">${{ formatNumber(customer.total_purchases) }}</p>
                    </div>
                  </div>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                      </div>
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-blue-600">Credit Limit</p>
                      <p class="text-lg font-semibold text-blue-900">${{ formatNumber(customer.credit_limit) }}</p>
                    </div>
                  </div>
                </div>

                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                      </div>
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-purple-600">Loyalty Points</p>
                      <p class="text-lg font-semibold text-purple-900">{{ formatNumber(customer.loyalty_points) }}</p>
                    </div>
                  </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-4 border border-yellow-200">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                      </div>
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-yellow-600">Member Since</p>
                      <p class="text-lg font-semibold text-yellow-900">{{ formatDate(customer.created_at) }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Customer Information -->
              <div class="space-y-6">
                <!-- Personal Information -->
                <div class="bg-gray-50 rounded-lg p-6">
                  <h4 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Full Name</label>
                      <p class="mt-1 text-sm text-gray-900">{{ customer.name || '-' }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Email Address</label>
                      <p class="mt-1 text-sm text-gray-900">{{ customer.email || '-' }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                      <p class="mt-1 text-sm text-gray-900">{{ customer.phone || '-' }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Mobile Number</label>
                      <p class="mt-1 text-sm text-gray-900">{{ customer.mobile || '-' }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Date of Birth</label>
                      <p class="mt-1 text-sm text-gray-900">{{ customer.date_of_birth ? formatDate(customer.date_of_birth) : '-' }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Gender</label>
                      <p class="mt-1 text-sm text-gray-900">{{ customer.gender ? customer.gender.charAt(0).toUpperCase() + customer.gender.slice(1) : '-' }}</p>
                    </div>
                  </div>
                </div>

                <!-- Address Information -->
                <div class="bg-gray-50 rounded-lg p-6">
                  <h4 class="text-lg font-medium text-gray-900 mb-4">Address Information</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                      <label class="block text-sm font-medium text-gray-700">Address</label>
                      <p class="mt-1 text-sm text-gray-900">{{ customer.address || '-' }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">City</label>
                      <p class="mt-1 text-sm text-gray-900">{{ customer.city || '-' }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">State</label>
                      <p class="mt-1 text-sm text-gray-900">{{ customer.state || '-' }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Postal Code</label>
                      <p class="mt-1 text-sm text-gray-900">{{ customer.postal_code || '-' }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Country</label>
                      <p class="mt-1 text-sm text-gray-900">{{ customer.country || '-' }}</p>
                    </div>
                  </div>
                </div>

                <!-- Business Information -->
                <div class="bg-gray-50 rounded-lg p-6">
                  <h4 class="text-lg font-medium text-gray-900 mb-4">Business Information</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Tax Number</label>
                      <p class="mt-1 text-sm text-gray-900">{{ customer.tax_number || '-' }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Credit Limit</label>
                      <p class="mt-1 text-sm text-gray-900">${{ formatNumber(customer.credit_limit) }}</p>
                    </div>
                  </div>
                </div>

                <!-- Notes -->
                <div v-if="customer.notes" class="bg-gray-50 rounded-lg p-6">
                  <h4 class="text-lg font-medium text-gray-900 mb-4">Notes</h4>
                  <p class="text-sm text-gray-900">{{ customer.notes }}</p>
                </div>
              </div>
            </div>

            <!-- Loading State -->
            <div v-else class="flex items-center justify-center py-12">
              <div class="text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No customer data</h3>
                <p class="mt-1 text-sm text-gray-500">Unable to load customer details.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script>
export default {
  name: 'CustomerViewModalSimple',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    customer: {
      type: Object,
      default: null
    }
  },
  emits: ['close'],
  setup() {
    const formatNumber = (value) => {
      return new Intl.NumberFormat().format(value || 0);
    };

    const formatDate = (dateString) => {
      if (!dateString) return '-';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    };

    return {
      formatNumber,
      formatDate
    };
  }
};
</script>

<style scoped>
/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
