<template>
  <div v-if="show" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200" style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);">
    <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-4xl shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 overflow-hidden transition-all duration-300 z-10 max-h-[90vh] overflow-y-auto my-auto">
        <div class="bg-white dark:bg-zinc-900">
          <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 dark:from-indigo-700 dark:to-indigo-900 px-6 py-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                </div>
                <div v-if="userData">
                  <h3 class="text-xl font-bold text-white">{{ userData.name }}</h3>
                  <p class="text-indigo-100 text-xs font-semibold">ID: #{{ userData.id }}</p>
                </div>
              </div>
              <div class="flex items-center space-x-3">
                <span v-if="userData" :class="userData.is_active 
                  ? 'px-3 py-1 text-xs font-bold rounded-full bg-green-400 text-green-950' 
                  : 'px-3 py-1 text-xs font-bold rounded-full bg-red-400 text-red-950'">
                  {{ userData.is_active ? 'ACTIVE' : 'INACTIVE' }}
                </span>
                <button @click="closeModal" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 cursor-pointer">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
              </div>
            </div>
          </div>

          <div v-if="loading" class="py-20 text-center">
            <div class="animate-spin inline-block w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full mb-2"></div>
            <p class="text-xs font-bold text-slate-500 dark:text-zinc-400">Fetching details...</p>
          </div>

          <div v-else-if="userData" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
              <div class="p-4 bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 rounded-xl">
                <p class="text-xs text-indigo-600 dark:text-indigo-400 font-bold uppercase">Email</p>
                <p class="text-sm font-bold text-slate-900 dark:text-zinc-100 truncate">{{ userData.email }}</p>
              </div>
              <div class="p-4 bg-purple-50/80 dark:bg-purple-950/40 border border-purple-100 dark:border-purple-900/50 rounded-xl">
                <p class="text-xs text-purple-600 dark:text-purple-400 font-bold uppercase">Role</p>
                <p class="text-sm font-bold text-slate-900 dark:text-zinc-100 capitalize">{{ userData.roles?.[0]?.name || 'No Role' }}</p>
              </div>
              <div class="p-4 bg-amber-50/80 dark:bg-amber-950/40 border border-amber-100 dark:border-amber-900/50 rounded-xl">
                <p class="text-xs text-amber-600 dark:text-amber-400 font-bold uppercase">Registered On</p>
                <p class="text-sm font-bold text-slate-900 dark:text-zinc-100">{{ formatDate(userData.created_at) }}</p>
              </div>
            </div>

            <div class="bg-slate-50 dark:bg-zinc-800/50 rounded-xl p-6 border border-slate-200/60 dark:border-zinc-700/60">
              <h4 class="text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-4 border-b border-slate-200 dark:border-zinc-700/60 pb-2">Full Profile Details</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-10">
                <div>
                  <label class="text-xs font-semibold text-slate-500 dark:text-zinc-400">Official Name</label>
                  <p class="text-slate-900 dark:text-zinc-100 font-bold border-b border-slate-200 dark:border-zinc-700 pb-1 text-sm">{{ userData.name }}</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-slate-500 dark:text-zinc-400">Contact Number</label>
                  <p class="text-slate-900 dark:text-zinc-100 font-bold border-b border-slate-200 dark:border-zinc-700 pb-1 text-sm">{{ userData.phone || 'Not Provided' }}</p>
                </div>
                <div class="md:col-span-2">
                  <label class="text-xs font-semibold text-slate-500 dark:text-zinc-400">Residential Address</label>
                  <p class="text-slate-900 dark:text-zinc-100 font-bold border-b border-slate-200 dark:border-zinc-700 pb-1 text-sm">{{ userData.address || 'No address on file' }}</p>
                </div>
                <div class="md:col-span-2">
                  <label class="text-xs font-semibold text-slate-500 dark:text-zinc-400">Internal Notes</label>
                  <div class="mt-2 text-xs font-medium text-slate-700 dark:text-zinc-300 bg-white dark:bg-zinc-900 p-4 rounded-xl border border-slate-200 dark:border-zinc-700 italic">
                    "{{ userData.notes || 'No additional notes.' }}"
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
