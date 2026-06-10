<template>
  <div class="space-y-6 max-w-4xl mx-auto">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Edit Company</h1>
        <p class="text-sm text-slate-500 mt-1">Update your company's profile and details.</p>
      </div>
      <router-link to="/companies" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
        Cancel & Return
      </router-link>
    </div>

    <!-- Success Toast -->
    <transition enter-active-class="transform ease-out duration-300 transition" enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2" enter-to-class="translate-y-0 opacity-100 sm:translate-x-0" leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="successMessage" class="fixed bottom-4 right-4 z-50 p-4 rounded-lg bg-slate-900 border border-slate-700 text-white shadow-2xl flex items-center gap-3 w-80">
        <svg class="h-6 w-6 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-sm font-semibold">{{ successMessage }}</span>
      </div>
    </transition>

    <div v-if="loading" class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 flex items-center justify-center">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
    </div>

    <form v-else @submit.prevent="updateCompany" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
      <div class="p-8 space-y-8">
        
        <!-- Logo Upload Section -->
        <div class="flex flex-col sm:flex-row items-start gap-6 pb-8 border-b border-slate-100">
          <div class="flex-shrink-0 relative">
            <div class="h-24 w-24 rounded-2xl bg-gradient-to-br from-indigo-50 to-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center text-slate-400 overflow-hidden group">
              <img v-if="logoPreview || form.company_logo_url" :src="logoPreview || form.company_logo_url" class="h-full w-full object-cover" alt="Company Logo" />
              <svg v-else class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <div class="absolute inset-0 bg-slate-900/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer" @click="$refs.logoInput.click()">
                <span class="text-white text-xs font-semibold">Change</span>
              </div>
            </div>
            <input type="file" ref="logoInput" class="hidden" accept="image/*" @change="handleLogoUpload">
          </div>
          <div class="space-y-1 pt-2">
            <h3 class="text-base font-semibold text-slate-900">Company Logo</h3>
            <p class="text-sm text-slate-500">Update your company logo. Max size 2MB (JPG, PNG).</p>
            <button type="button" @click="$refs.logoInput.click()" class="mt-2 text-sm font-semibold text-indigo-600 hover:text-indigo-700">
              Upload new image
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Company Name -->
          <div class="space-y-1">
            <label class="text-sm font-semibold text-slate-700">Company Name <span class="text-rose-500">*</span></label>
            <input 
              v-model="form.company_name" 
              type="text" 
              required
              class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow"
              placeholder="Enter company name"
            >
          </div>

          <!-- Email (Disabled) -->
          <div class="space-y-1">
            <label class="text-sm font-semibold text-slate-700">Email Address</label>
            <input 
              v-model="form.company_email" 
              type="email" 
              disabled
              class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-500 cursor-not-allowed"
              placeholder="Company email"
            >
            <p class="text-[11px] text-slate-400 mt-1">Email address cannot be changed.</p>
          </div>

          <!-- Phone Number -->
          <div class="space-y-1">
            <label class="text-sm font-semibold text-slate-700">Phone Number</label>
            <input 
              v-model="form.company_phone" 
              type="text" 
              class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow"
              placeholder="Enter phone number"
            >
          </div>

          <!-- Registration Number -->
          <div class="space-y-1">
            <label class="text-sm font-semibold text-slate-700">Registration Number</label>
            <input 
              v-model="form.registration_number" 
              type="text" 
              class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow"
              placeholder="Enter registration number"
            >
          </div>
        </div>
      </div>

      <!-- Footer Actions -->
      <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
        <button 
          type="submit" 
          :disabled="saving"
          class="px-6 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:opacity-70 shadow-md shadow-indigo-500/20 rounded-xl transition-all flex items-center gap-2"
        >
          <svg v-if="saving" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
          {{ saving ? 'Saving Changes...' : 'Save Changes' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const saving = ref(false);
const successMessage = ref('');
const logoInput = ref(null);
const logoPreview = ref(null);
const logoFile = ref(null);

const form = ref({
  company_name: '',
  company_email: '',
  company_phone: '',
  registration_number: '',
  company_logo_url: null,
});

const companyId = ref(null);

onMounted(async () => {
  companyId.value = route.query.id;
  if (!companyId.value) {
    router.push('/companies');
    return;
  }
  await fetchCompany();
});

const fetchCompany = async () => {
  try {
    const response = await axios.get(`/api/companies/${companyId.value}`);
    const data = response.data;
    form.value = {
      company_name: data.company_name || '',
      company_email: data.company_email || '',
      company_phone: data.company_phone || '',
      registration_number: data.registration_number || '',
      company_logo_url: data.company_logo ? `/storage/${data.company_logo}` : null,
    };
  } catch (error) {
    console.error('Error fetching company:', error);
    router.push('/companies');
  } finally {
    loading.value = false;
  }
};

const handleLogoUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    logoFile.value = file;
    logoPreview.value = URL.createObjectURL(file);
  }
};

const updateCompany = async () => {
  saving.value = true;
  successMessage.value = '';
  
  const formData = new FormData();
  formData.append('company_name', form.value.company_name);
  formData.append('company_phone', form.value.company_phone || '');
  formData.append('registration_number', form.value.registration_number || '');
  
  if (logoFile.value) {
    formData.append('company_logo', logoFile.value);
  }

  try {
    const response = await axios.post(`/api/companies/${companyId.value}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    successMessage.value = response.data.message;
    
    setTimeout(() => {
      successMessage.value = '';
      router.push('/companies');
    }, 2000);
  } catch (error) {
    console.error('Error updating company:', error);
    alert('Failed to update company. Please check your inputs.');
  } finally {
    saving.value = false;
  }
};
</script>
