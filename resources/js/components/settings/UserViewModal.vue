<template>
  <div v-if="show" class="fixed inset-0 z-[9999] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 bg-black bg-opacity-80 transition-opacity" @click="closeModal"></div>
      
      <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full" @click.stop>
        <div class="bg-white">
          <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                </div>
                <div v-if="userData">
                  <h3 class="text-xl font-semibold text-white">{{ userData.name }}</h3>
                  <p class="text-indigo-100 text-sm">ID: #{{ userData.id }}</p>
                </div>
              </div>
              <div class="flex items-center space-x-3">
                <span v-if="userData" :class="userData.is_active 
                  ? 'px-3 py-1 text-xs font-bold rounded-full bg-green-400 text-green-900' 
                  : 'px-3 py-1 text-xs font-bold rounded-full bg-red-400 text-red-900'">
                  {{ userData.is_active ? 'ACTIVE' : 'INACTIVE' }}
                </span>
                <button @click="closeModal" class="text-white hover:text-gray-200">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
              </div>
            </div>
          </div>

          <div v-if="loading" class="py-20 text-center">
            <div class="animate-spin inline-block w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full mb-2"></div>
            <p class="text-gray-500">Fetching details...</p>
          </div>

          <div v-else-if="userData" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
              <div class="p-4 bg-indigo-50 border border-indigo-100 rounded-xl">
                <p class="text-xs text-indigo-500 font-bold uppercase">Email</p>
                <p class="text-sm font-semibold text-gray-800 truncate">{{ userData.email }}</p>
              </div>
              <div class="p-4 bg-purple-50 border border-purple-100 rounded-xl">
                <p class="text-xs text-purple-500 font-bold uppercase">Role</p>
                <p class="text-sm font-semibold text-gray-800 capitalize">{{ userData.roles?.[0]?.name || 'No Role' }}</p>
              </div>
              <div class="p-4 bg-yellow-50 border border-yellow-100 rounded-xl">
                <p class="text-xs text-yellow-600 font-bold uppercase">Registered On</p>
                <p class="text-sm font-semibold text-gray-800">{{ formatDate(userData.created_at) }}</p>
              </div>
            </div>

            <div class="bg-gray-50 rounded-xl p-6 border border-gray-100">
              <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4 border-b pb-2">Full Profile Details</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-10">
                <div>
                  <label class="text-xs font-semibold text-gray-500">Official Name</label>
                  <p class="text-gray-800 font-medium border-b border-gray-200 pb-1">{{ userData.name }}</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-500">Contact Number</label>
                  <p class="text-gray-800 font-medium border-b border-gray-200 pb-1">{{ userData.phone || 'Not Provided' }}</p>
                </div>
                <div class="md:col-span-2">
                  <label class="text-xs font-semibold text-gray-500">Residential Address</label>
                  <p class="text-gray-800 font-medium border-b border-gray-200 pb-1">{{ userData.address || 'No address on file' }}</p>
                </div>
                <div class="md:col-span-2">
                  <label class="text-xs font-semibold text-gray-500">Internal Notes</label>
                  <div class="mt-2 text-sm text-gray-600 bg-white p-4 rounded-lg border italic">
                    "{{ userData.notes || 'No additional notes.' }}"
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue';
import axios from 'axios';

export default {
  name: 'UserViewModal',
  props: {
    show: { type: Boolean, default: false },
    user: { type: Object, default: null } 
  },
  emits: ['close'],
  setup(props, { emit }) {
    const loading = ref(false);
    const userData = ref(null);

    const formatDate = (dateString) => {
      if (!dateString) return 'N/A';
      return new Date(dateString).toLocaleDateString('en-US', { 
        year: 'numeric', month: 'short', day: 'numeric' 
      });
    };

    const loadDetails = async () => {
      if (!props.user?.id) return;
      loading.value = true;
      try {
        const response = await axios.get(`/api/users/${props.user.id}`);
        userData.value = response.data;
      } catch (error) {
        console.error('Fetch Error:', error);
        userData.value = props.user;
      } finally {
        loading.value = false;
      }
    };

    const closeModal = () => {
      userData.value = null;
      emit('close');
    };

    watch(() => props.show, (newVal) => {
      if (newVal) loadDetails();
    });

    return { loading, userData, formatDate, closeModal };
  }
};
</script>
