<template>
  <div class="max-w-4xl mx-auto">
      <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-800 overflow-hidden mb-6">
          <div class="p-6 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50">
              <h3 class="text-lg font-black text-zinc-950 dark:text-white tracking-tight">Account Profile</h3>
              <p class="text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mt-0.5">Manage your personal admin account details</p>
          </div>
          
          <div class="p-6">
              <div v-if="successMessage" class="mb-6 bg-zinc-900 text-white dark:bg-white dark:text-black px-4 py-3 rounded-xl flex items-center text-xs font-bold shadow-xs">
                  <i class="fas fa-check-circle mr-2 text-sm"></i> {{ successMessage }}
              </div>
              <div v-if="errorMessage" class="mb-6 bg-rose-50 border border-rose-200 text-rose-700 dark:bg-rose-950/40 dark:border-rose-900 dark:text-rose-400 px-4 py-3 rounded-xl flex items-center text-xs font-bold">
                  <i class="fas fa-exclamation-circle mr-2 text-sm"></i> {{ errorMessage }}
              </div>

              <form @submit.prevent="updateProfile" class="space-y-6 max-w-2xl">
                  <!-- Name Fields -->
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                      <div>
                          <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">First Name <span class="text-rose-500">*</span></label>
                          <input type="text" v-model="form.first_name" required class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold placeholder-zinc-400 dark:placeholder-zinc-600" placeholder="First name">
                      </div>
                      <div>
                          <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">Middle Name</label>
                          <input type="text" v-model="form.middle_name" class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold placeholder-zinc-400 dark:placeholder-zinc-600" placeholder="Middle name">
                      </div>
                      <div>
                          <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">Last Name <span class="text-rose-500">*</span></label>
                          <input type="text" v-model="form.last_name" required class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold placeholder-zinc-400 dark:placeholder-zinc-600" placeholder="Last name">
                      </div>
                  </div>

                  <!-- Email (Disabled) -->
                  <div>
                      <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">Email Address</label>
                      <input type="email" :value="form.email" disabled class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl bg-zinc-100 dark:bg-zinc-800/60 text-zinc-400 dark:text-zinc-500 cursor-not-allowed text-xs font-bold outline-none">
                      <p class="text-[11px] font-medium text-zinc-400 mt-1">Email address cannot be changed. Contact system admin for assistance.</p>
                  </div>

                  <!-- Password Change -->
                  <div class="pt-6 border-t border-zinc-100 dark:border-zinc-800">
                      <h4 class="text-sm font-black text-zinc-950 dark:text-white uppercase tracking-wider mb-4">Change Password</h4>
                      <div class="space-y-4">
                          <div>
                              <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">Current Password</label>
                              <input type="password" v-model="form.current_password" class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold placeholder-zinc-400 dark:placeholder-zinc-600" placeholder="••••••••">
                          </div>
                          <div>
                              <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">New Password</label>
                              <input type="password" v-model="form.password" class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold placeholder-zinc-400 dark:placeholder-zinc-600" placeholder="Leave blank to keep current password">
                          </div>
                          <div>
                              <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">Confirm New Password</label>
                              <input type="password" v-model="form.password_confirmation" class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold placeholder-zinc-400 dark:placeholder-zinc-600" placeholder="Confirm new password">
                          </div>
                      </div>
                  </div>

                  <!-- Submit -->
                  <div class="pt-4 flex justify-end">
                      <button type="submit" :disabled="loading" class="bg-black hover:bg-zinc-800 text-white dark:bg-white dark:hover:bg-zinc-200 dark:text-black font-extrabold px-6 py-2.5 rounded-xl text-xs shadow-xs transition-all disabled:opacity-50 flex items-center cursor-pointer">
                          <i v-if="loading" class="fas fa-spinner fa-spin mr-2"></i>
                          <i v-else class="fas fa-save mr-2"></i>
                          Save Changes
                      </button>
                  </div>
              </form>
          </div>
      </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const form = ref({
    first_name: '',
    middle_name: '',
    last_name: '',
    email: '',
    current_password: '',
    password: '',
    password_confirmation: ''
});

const loading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const loadProfile = async () => {
    try {
        const { data } = await axios.get('/admin/api/profile');
        form.value.first_name = data.first_name || '';
        form.value.middle_name = data.middle_name || '';
        form.value.last_name = data.last_name || '';
        if (!form.value.first_name && data.name) {
            const parts = data.name.trim().split(' ');
            form.value.first_name = parts.shift() || '';
            form.value.last_name = parts.length > 0 ? parts.pop() : '';
            form.value.middle_name = parts.join(' ');
        }
        form.value.email = data.email;
    } catch (e) {
        errorMessage.value = 'Failed to load profile data.';
    }
};

const updateProfile = async () => {
    loading.value = true;
    successMessage.value = '';
    errorMessage.value = '';

    try {
        await axios.put('/admin/api/profile', form.value);
        successMessage.value = 'Profile updated successfully.';
        localStorage.setItem('admin_name', form.value.name); // Update local storage
        
        // Reset password fields
        form.value.current_password = '';
        form.value.password = '';
        form.value.password_confirmation = '';
    } catch (e) {
        errorMessage.value = e.response?.data?.message || 'Failed to update profile. Please verify your current password.';
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadProfile();
});
</script>

