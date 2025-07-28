<template>
  <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
      <div class="bg-white shadow rounded-lg">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
          <h1 class="text-2xl font-bold text-gray-900">User Profile</h1>
          <p class="text-sm text-gray-600">Manage your account information and preferences</p>
        </div>

        <!-- Profile Content -->
        <div class="p-6">
          <!-- Profile Image Section -->
          <div class="flex items-center space-x-6 mb-8">
            <div class="relative">
              <div class="h-24 w-24 rounded-full overflow-hidden bg-gray-100 border-2 border-gray-200">
                <img 
                  v-if="profileImage" 
                  :src="profileImage" 
                  alt="Profile" 
                  class="h-full w-full object-cover"
                />
                <div 
                  v-else 
                  class="h-full w-full bg-indigo-500 flex items-center justify-center"
                >
                  <span class="text-white font-bold text-2xl">
                    {{ authStore.user?.name?.charAt(0).toUpperCase() }}
                  </span>
                </div>
              </div>
              <button
                @click="triggerFileInput"
                class="absolute bottom-0 right-0 bg-indigo-600 text-white rounded-full p-2 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
              </button>
              <input
                ref="fileInput"
                type="file"
                accept="image/*"
                @change="handleImageUpload"
                class="hidden"
              />
            </div>
            <div>
              <h2 class="text-xl font-semibold text-gray-900">{{ authStore.user?.name }}</h2>
              <p class="text-gray-600">{{ authStore.user?.email }}</p>
              <p class="text-sm text-gray-500 mt-1">
                Member since {{ formatDate(authStore.user?.created_at) }}
              </p>
            </div>
          </div>

          <!-- Profile Form -->
          <form @submit.prevent="updateProfile" class="space-y-6">
            <!-- Error Messages -->
            <div v-if="formErrors.length > 0" class="bg-red-50 border border-red-200 rounded-md p-4">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                  <div class="mt-2 text-sm text-red-700">
                    <ul class="list-disc pl-5 space-y-1">
                      <li v-for="error in formErrors" :key="error">{{ error }}</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <!-- Success Message -->
            <div v-if="showSuccessMessage" class="bg-green-50 border border-green-200 rounded-md p-4">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-green-800">Profile updated successfully!</p>
                </div>
              </div>
            </div>

            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                  Full Name
                </label>
                <input
                  id="name"
                  v-model="profileForm.name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>

              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                  Email Address
                </label>
                <input
                  id="email"
                  v-model="profileForm.email"
                  type="email"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>
            </div>

            <!-- Password Change Section -->
            <div class="border-t border-gray-200 pt-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                    Current Password
                  </label>
                  <input
                    id="current_password"
                    v-model="profileForm.current_password"
                    type="password"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>

                <div>
                  <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                    New Password
                  </label>
                  <input
                    id="new_password"
                    v-model="profileForm.new_password"
                    type="password"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>

                <div class="md:col-span-2">
                  <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                    Confirm New Password
                  </label>
                  <input
                    id="new_password_confirmation"
                    v-model="profileForm.new_password_confirmation"
                    type="password"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-6 border-t border-gray-200">
              <button
                type="submit"
                :disabled="submitting"
                class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="submitting">Updating...</span>
                <span v-else>Update Profile</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const authStore = useAuthStore();

// Reactive data
const profileImage = ref(null);
const submitting = ref(false);
const formErrors = ref([]);
const showSuccessMessage = ref(false);
const fileInput = ref(null);

// Profile form
const profileForm = ref({
  name: '',
  email: '',
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
});

// Methods
const initializeForm = () => {
  profileForm.value.name = authStore.user?.name || '';
  profileForm.value.email = authStore.user?.email || '';
  profileImage.value = authStore.user?.profile_image || null;
};

const triggerFileInput = () => {
  fileInput.value?.click();
};

const handleImageUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  // Validate file type
  if (!file.type.startsWith('image/')) {
    formErrors.value = ['Please select a valid image file'];
    return;
  }

  // Validate file size (max 2MB)
  if (file.size > 2 * 1024 * 1024) {
    formErrors.value = ['Image size must be less than 2MB'];
    return;
  }

  const formData = new FormData();
  formData.append('profile_image', file);

  try {
    submitting.value = true;
    const response = await axios.post('/api/user/profile-image', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    profileImage.value = response.data.profile_image_url;
    showSuccessMessage.value = true;
    setTimeout(() => {
      showSuccessMessage.value = false;
    }, 3000);

  } catch (error) {
    formErrors.value = [error.response?.data?.message || 'Failed to upload image'];
  } finally {
    submitting.value = false;
  }
};

const updateProfile = async () => {
  submitting.value = true;
  formErrors.value = [];
  showSuccessMessage.value = false;

  try {
    const response = await axios.put('/api/user/profile', profileForm.value);
    
    // Update auth store with new user data
    await authStore.fetchUser();
    
    showSuccessMessage.value = true;
    
    // Clear password fields
    profileForm.value.current_password = '';
    profileForm.value.new_password = '';
    profileForm.value.new_password_confirmation = '';
    
    setTimeout(() => {
      showSuccessMessage.value = false;
    }, 3000);

  } catch (error) {
    if (error.response?.data?.errors) {
      formErrors.value = Object.values(error.response.data.errors).flat();
    } else {
      formErrors.value = [error.response?.data?.message || 'An error occurred'];
    }
  } finally {
    submitting.value = false;
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

// Lifecycle
onMounted(() => {
  initializeForm();
});
</script>
