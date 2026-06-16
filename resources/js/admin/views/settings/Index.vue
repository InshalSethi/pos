<template>
  <div class="max-w-4xl mx-auto">
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
          <div class="p-6 border-b border-gray-100 bg-gray-50/50">
              <h3 class="text-lg font-bold text-gray-800">System Settings</h3>
              <p class="text-sm text-gray-500">Configure global application preferences</p>
          </div>
          
          <div class="p-6">
              <div v-if="successMessage" class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center">
                  <i class="fas fa-check-circle mr-2"></i> {{ successMessage }}
              </div>
              <div v-if="errorMessage" class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center">
                  <i class="fas fa-exclamation-circle mr-2"></i> {{ errorMessage }}
              </div>

              <div v-if="fetching" class="py-12 flex justify-center text-indigo-500">
                  <i class="fas fa-spinner fa-spin text-3xl"></i>
              </div>

              <form v-else @submit.prevent="saveSettings" class="space-y-6 max-w-2xl">
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <!-- System Name -->
                      <div class="md:col-span-2">
                          <label class="block text-sm font-bold text-gray-700 mb-2">System Name</label>
                          <input type="text" v-model="form.system_name" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-sm outline-none bg-white text-gray-800">
                      </div>

                      <!-- Time Zone -->
                      <div>
                          <label class="block text-sm font-bold text-gray-700 mb-2">Time Zone</label>
                          <select v-model="form.timezone" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-sm outline-none bg-white text-gray-800">
                              <option value="UTC">UTC (Universal Time)</option>
                              <option value="America/New_York">Eastern Time (US & Canada)</option>
                              <option value="America/Chicago">Central Time (US & Canada)</option>
                              <option value="America/Denver">Mountain Time (US & Canada)</option>
                              <option value="America/Los_Angeles">Pacific Time (US & Canada)</option>
                              <option value="Europe/London">London (GMT)</option>
                              <option value="Asia/Dubai">Dubai</option>
                              <option value="Asia/Karachi">Karachi (PKT)</option>
                          </select>
                      </div>

                      <!-- Date Format -->
                      <div>
                          <label class="block text-sm font-bold text-gray-700 mb-2">Date Format</label>
                          <select v-model="form.date_format" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-sm outline-none bg-white text-gray-800">
                              <option value="Y-m-d">YYYY-MM-DD (2026-12-31)</option>
                              <option value="m/d/Y">MM/DD/YYYY (12/31/2026)</option>
                              <option value="d/m/Y">DD/MM/YYYY (31/12/2026)</option>
                              <option value="M d, Y">Mon DD, YYYY (Dec 31, 2026)</option>
                          </select>
                      </div>

                      <!-- Time Format -->
                      <div>
                          <label class="block text-sm font-bold text-gray-700 mb-2">Time Format</label>
                          <select v-model="form.time_format" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-sm outline-none bg-white text-gray-800">
                              <option value="H:i:s">24-hour (14:30:00)</option>
                              <option value="h:i A">12-hour (02:30 PM)</option>
                          </select>
                      </div>

                      <!-- Session Timeout -->
                      <div>
                          <label class="block text-sm font-bold text-gray-700 mb-2">Session Timeout (minutes)</label>
                          <input type="number" min="5" max="1440" v-model="form.session_timeout" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-sm outline-none bg-white text-gray-800">
                      </div>
                  </div>

                  <!-- Submit -->
                  <div class="pt-6 border-t border-gray-100 flex justify-end">
                      <button type="submit" :disabled="loading" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-md transition-colors disabled:opacity-50 flex items-center">
                          <i v-if="loading" class="fas fa-spinner fa-spin mr-2"></i>
                          <i v-else class="fas fa-save mr-2"></i>
                          Save Settings
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
    system_name: 'Admin System',
    timezone: 'UTC',
    date_format: 'Y-m-d',
    time_format: 'H:i:s',
    session_timeout: 120
});

const fetching = ref(true);
const loading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const loadSettings = async () => {
    fetching.value = true;
    try {
        const { data } = await axios.get('/admin/api/settings');
        // Map data to form
        Object.keys(form.value).forEach(key => {
            if (data[key] !== undefined) form.value[key] = data[key];
        });
    } catch (e) {
        errorMessage.value = 'Failed to load system settings.';
    } finally {
        fetching.value = false;
    }
};

const saveSettings = async () => {
    loading.value = true;
    successMessage.value = '';
    errorMessage.value = '';

    try {
        await axios.put('/admin/api/settings', form.value);
        successMessage.value = 'System settings updated successfully.';
    } catch (e) {
        errorMessage.value = e.response?.data?.message || 'Failed to update settings.';
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadSettings();
});
</script>
