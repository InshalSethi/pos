<template>
  <div class="p-6 space-y-6 max-w-[1200px] mx-auto font-sans">
    <!-- Top Header Bar (Black & White System Theme) -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="p-2.5 bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 rounded-xl shadow-xs">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
        </div>
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white font-sans">Account Profile</h1>
          <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5 font-sans">Manage your personal account details, credentials, and preferences</p>
        </div>
      </div>
    </div>

    <!-- Main Profile Card Container (White and Black High-Contrast Style) -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs p-6 sm:p-8 space-y-8 font-sans">
      
      <!-- Profile Header / Avatar Hero Section -->
      <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 pb-6 border-b border-slate-100 dark:border-zinc-800">
        <div class="relative shrink-0">
          <div class="h-24 w-24 rounded-full overflow-hidden bg-slate-100 dark:bg-zinc-800 border-2 border-slate-200 dark:border-zinc-700 shadow-sm flex items-center justify-center">
            <img 
              v-if="profileImage" 
              :src="getProfileImageUrl(profileImage)" 
              alt="Profile Avatar" 
              class="h-full w-full object-cover"
            />
            <div 
              v-else 
              class="h-full w-full bg-slate-900 dark:bg-zinc-100 flex items-center justify-center"
            >
              <span class="text-white dark:text-zinc-900 font-bold text-2xl font-sans">
                {{ authStore.user?.name?.charAt(0).toUpperCase() }}
              </span>
            </div>
          </div>

          <!-- Camera Upload Button -->
          <button
            type="button"
            @click="triggerFileInput"
            class="absolute bottom-0 right-0 bg-slate-900 hover:bg-black text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white rounded-full p-2 shadow-md hover:scale-105 transition-all cursor-pointer"
            title="Update Profile Picture"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
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

        <div class="text-center sm:text-left space-y-1">
          <div class="flex items-center gap-2 justify-center sm:justify-start">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white font-sans">{{ authStore.user?.name || 'User' }}</h2>
            <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-sans">
              Account Active
            </span>
          </div>
          <p class="text-xs font-sans font-medium text-slate-500 dark:text-slate-400">{{ authStore.user?.email }}</p>
          <p class="text-xs font-sans font-semibold text-slate-400 dark:text-zinc-500 pt-1">
            Member since {{ formatDate(authStore.user?.created_at) || 'N/A' }}
          </p>
        </div>
      </div>

      <!-- Profile Form -->
      <form @submit.prevent="updateProfile" class="space-y-6">
        
        <!-- Error Alerts -->
        <div v-if="formErrors.length > 0" class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-xl p-4 transition-all">
          <div class="flex items-start gap-3">
            <svg class="h-5 w-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <h3 class="text-xs font-bold text-rose-800 dark:text-rose-300 uppercase tracking-wider font-sans">Form Submission Issues</h3>
              <ul class="mt-1.5 list-disc pl-4 text-xs font-medium text-rose-700 dark:text-rose-300 space-y-1 font-sans">
                <li v-for="error in formErrors" :key="error">{{ error }}</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Success Alert -->
        <div v-if="showSuccessMessage" class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4 transition-all">
          <div class="flex items-center gap-3">
            <svg class="h-5 w-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <p class="text-xs font-bold text-emerald-800 dark:text-emerald-300 font-sans">Profile updated successfully!</p>
          </div>
        </div>

        <!-- Basic Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 border-b border-slate-100 dark:border-zinc-800 pb-2">
            <svg class="w-4 h-4 text-slate-900 dark:text-slate-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white font-sans">Basic Information</h3>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label for="name" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5 font-sans">
                Full Name <span class="text-rose-500">*</span>
              </label>
              <input
                id="name"
                v-model="profileForm.name"
                type="text"
                required
                class="w-full bg-slate-50 dark:bg-slate-800/50 border-0 rounded-xl py-2.5 px-3.5 text-xs font-sans font-medium text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
                placeholder="Enter full name"
              />
            </div>

            <div>
              <label for="email" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5 font-sans">
                Email Address <span class="text-rose-500">*</span>
              </label>
              <input
                id="email"
                v-model="profileForm.email"
                type="email"
                required
                class="w-full bg-slate-50 dark:bg-slate-800/50 border-0 rounded-xl py-2.5 px-3.5 text-xs font-sans font-medium text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
                placeholder="Enter email address"
              />
            </div>
          </div>
        </div>

        <!-- Security & Password Change Section -->
        <div class="space-y-4 pt-4">
          <div class="flex items-center gap-2 border-b border-slate-100 dark:border-zinc-800 pb-2">
            <svg class="w-4 h-4 text-slate-900 dark:text-slate-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white font-sans">Security & Password</h3>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
              <label for="current_password" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5 font-sans">
                Current Password
              </label>
              <input
                id="current_password"
                v-model="profileForm.current_password"
                type="password"
                class="w-full bg-slate-50 dark:bg-slate-800/50 border-0 rounded-xl py-2.5 px-3.5 text-xs font-sans font-medium text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
                placeholder="••••••••"
              />
            </div>

            <div>
              <label for="new_password" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5 font-sans">
                New Password
              </label>
              <input
                id="new_password"
                v-model="profileForm.new_password"
                type="password"
                class="w-full bg-slate-50 dark:bg-slate-800/50 border-0 rounded-xl py-2.5 px-3.5 text-xs font-sans font-medium text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
                placeholder="••••••••"
              />
            </div>

            <div>
              <label for="new_password_confirmation" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5 font-sans">
                Confirm Password
              </label>
              <input
                id="new_password_confirmation"
                v-model="profileForm.new_password_confirmation"
                type="password"
                class="w-full bg-slate-50 dark:bg-slate-800/50 border-0 rounded-xl py-2.5 px-3.5 text-xs font-sans font-medium text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
                placeholder="••••••••"
              />
            </div>
          </div>
        </div>

        <!-- Submit Button Footer -->
        <div class="flex items-center justify-end pt-5 border-t border-slate-100 dark:border-zinc-800">
          <button
            type="submit"
            :disabled="submitting"
            class="bg-slate-900 hover:bg-black active:scale-[0.98] text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white font-bold rounded-xl text-xs px-6 py-2.5 transition-all shadow-xs inline-flex items-center gap-2 cursor-pointer disabled:opacity-50 font-sans"
          >
            <div v-if="submitting" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-current"></div>
            <span>{{ submitting ? 'Updating...' : 'Update Profile' }}</span>
          </button>
        </div>
      </form>
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

const getProfileImageUrl = (imagePath) => {
  if (!imagePath) return null;
  if (imagePath.startsWith('http')) return imagePath;
  return `/storage/${imagePath}`;
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
