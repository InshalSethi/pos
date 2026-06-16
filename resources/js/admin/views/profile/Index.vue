<template>
  <div class="max-w-4xl mx-auto">
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
          <div class="p-6 border-b border-gray-100 bg-gray-50/50">
              <h3 class="text-lg font-bold text-gray-800">Account Profile</h3>
              <p class="text-sm text-gray-500">Manage your personal account details</p>
          </div>
          
          <div class="p-6">
              <div v-if="successMessage" class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center">
                  <i class="fas fa-check-circle mr-2"></i> {{ successMessage }}
              </div>
              <div v-if="errorMessage" class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center">
                  <i class="fas fa-exclamation-circle mr-2"></i> {{ errorMessage }}
              </div>

              <form @submit.prevent="updateProfile" class="space-y-6 max-w-2xl">
                  <!-- Name -->
                  <div>
                      <label class="block text-sm font-bold text-gray-700 mb-2">Full Name</label>
                      <input type="text" v-model="form.name" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-sm outline-none bg-white text-gray-800">
                  </div>

                  <!-- Email (Disabled) -->
                  <div>
                      <label class="block text-sm font-bold text-gray-700 mb-2">Email Address</label>
                      <input type="email" :value="form.email" disabled class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed outline-none">
                      <p class="text-xs text-gray-400 mt-1">Email address cannot be changed. Contact super-admin for assistance.</p>
                  </div>

                  <!-- Password Change -->
                  <div class="pt-6 border-t border-gray-100">
                      <h4 class="text-md font-bold text-gray-800 mb-4">Change Password</h4>
                      <div class="space-y-4">
                          <div>
                              <label class="block text-sm font-bold text-gray-700 mb-2">Current Password</label>
                              <input type="password" v-model="form.current_password" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-sm outline-none bg-white text-gray-800" placeholder="••••••••">
                          </div>
                          <div>
                              <label class="block text-sm font-bold text-gray-700 mb-2">New Password</label>
                              <input type="password" v-model="form.password" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-sm outline-none bg-white text-gray-800" placeholder="Leave blank to keep current password">
                          </div>
                          <div>
                              <label class="block text-sm font-bold text-gray-700 mb-2">Confirm New Password</label>
                              <input type="password" v-model="form.password_confirmation" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-sm outline-none bg-white text-gray-800" placeholder="Confirm new password">
                          </div>
                      </div>
                  </div>

                  <!-- Submit -->
                  <div class="pt-4 flex justify-end">
                      <button type="submit" :disabled="loading" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-md transition-colors disabled:opacity-50 flex items-center">
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
    name: '',
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
        form.value.name = data.name;
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
