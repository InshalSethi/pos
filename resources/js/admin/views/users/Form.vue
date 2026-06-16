<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden max-w-3xl mx-auto">
      <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
          <h3 class="text-lg font-bold text-gray-800">{{ isEditing ? 'Edit User' : 'Add New User' }}</h3>
          <router-link :to="{ name: 'admin.users.index' }" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition-colors">
              <i class="fas fa-arrow-left mr-2"></i> Back
          </router-link>
      </div>
      
      <div v-if="loading" class="p-12 text-center text-indigo-500">
          <i class="fas fa-circle-notch fa-spin text-4xl"></i>
          <p class="mt-4 font-medium text-gray-500">Loading user data...</p>
      </div>

      <form v-else @submit.prevent="submitForm" class="p-6 space-y-6">
          <div v-if="errorMessage" class="bg-red-50 text-red-600 p-4 rounded-lg text-sm font-medium border border-red-100 flex items-start">
              <i class="fas fa-exclamation-circle mt-0.5 mr-3"></i>
              <div>
                  <p>{{ errorMessage }}</p>
                  <ul v-if="validationErrors && Object.keys(validationErrors).length > 0" class="mt-2 list-disc list-inside space-y-1">
                      <li v-for="(errors, field) in validationErrors" :key="field">
                          {{ errors[0] }}
                      </li>
                  </ul>
              </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Name -->
              <div>
                  <label class="block text-sm font-bold text-gray-700 mb-2">Full Name <span class="text-red-500">*</span></label>
                  <input type="text" v-model="form.name" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-sm outline-none bg-white text-gray-800" placeholder="e.g. Jane Doe">
              </div>

              <!-- Email -->
              <div>
                  <label class="block text-sm font-bold text-gray-700 mb-2">Email Address <span class="text-red-500">*</span></label>
                  <input type="email" v-model="form.email" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-sm outline-none bg-white text-gray-800" placeholder="user@example.com">
              </div>

              <!-- Phone -->
              <div>
                  <label class="block text-sm font-bold text-gray-700 mb-2">Phone Number</label>
                  <input type="text" v-model="form.phone" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-sm outline-none bg-white text-gray-800" placeholder="+1 (555) 000-0000">
              </div>

              <!-- Password -->
              <div>
                  <label class="block text-sm font-bold text-gray-700 mb-2">Password <span v-if="!isEditing" class="text-red-500">*</span></label>
                  <input type="password" v-model="form.password" :required="!isEditing" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-sm outline-none bg-white text-gray-800" :placeholder="isEditing ? 'Leave blank to keep current password' : 'Create a secure password'">
                  <p v-if="isEditing" class="text-xs text-gray-500 mt-1">Leave blank to keep the current password.</p>
              </div>
          </div>

          <!-- Active Status -->
          <div class="border-t border-gray-100 pt-6">
              <label class="flex items-center cursor-pointer">
                  <div class="relative">
                      <input type="checkbox" v-model="form.is_active" class="sr-only">
                      <div class="block bg-gray-200 w-10 h-6 rounded-full transition-colors" :class="{'bg-emerald-500': form.is_active}"></div>
                      <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform" :class="{'transform translate-x-4': form.is_active}"></div>
                  </div>
                  <div class="ml-3 text-sm font-bold text-gray-700">
                      Account Active Status
                  </div>
              </label>
              <p class="text-xs text-gray-500 mt-1 ml-14">Inactive users cannot log into the frontend application.</p>
          </div>

          <!-- Submit Button -->
          <div class="border-t border-gray-100 pt-6 flex justify-end">
              <button type="submit" :disabled="submitting" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-md transition-colors disabled:opacity-50 flex items-center">
                  <i v-if="submitting" class="fas fa-spinner fa-spin mr-2"></i>
                  <i v-else class="fas fa-save mr-2"></i>
                  {{ isEditing ? 'Update User' : 'Create User' }}
              </button>
          </div>
      </form>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const isEditing = computed(() => !!route.params.id);
const loading = ref(false);
const submitting = ref(false);
const errorMessage = ref('');
const validationErrors = ref({});

const form = ref({
    name: '',
    email: '',
    phone: '',
    password: '',
    is_active: true
});

const loadUser = async () => {
    loading.value = true;
    try {
        const { data } = await axios.get(`/admin/api/users/${route.params.id}`);
        const user = data.data;
        form.value.name = user.name;
        form.value.email = user.email;
        form.value.phone = user.phone;
        form.value.is_active = user.is_active;
    } catch (e) {
        errorMessage.value = 'Failed to load user data. They may have been deleted.';
    } finally {
        loading.value = false;
    }
};

onMounted(async () => {
    if (isEditing.value) {
        await loadUser();
    }
});

const submitForm = async () => {
    submitting.value = true;
    errorMessage.value = '';
    validationErrors.value = {};

    try {
        if (isEditing.value) {
            await axios.put(`/admin/api/users/${route.params.id}`, form.value);
        } else {
            await axios.post('/admin/api/users', form.value);
        }
        router.push({ name: 'admin.users.index' });
    } catch (e) {
        if (e.response && e.response.status === 422) {
            errorMessage.value = 'Please correct the errors below.';
            validationErrors.value = e.response.data.errors;
        } else {
            errorMessage.value = e.response?.data?.message || 'An unexpected error occurred.';
        }
    } finally {
        submitting.value = false;
    }
};
</script>
