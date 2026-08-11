<template>
  <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans">
    <div class="sm:mx-auto sm:w-full sm:max-w-2xl">
      <!-- Card Container -->
      <div class="bg-white py-8 px-6 shadow-xl rounded-2xl sm:px-10 border border-slate-100">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-6 border-b border-slate-100 mb-6">
          <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Enterprise Metrics</h2>
            <p class="text-xs text-slate-500 mt-1">Please enter your accurate legal entity details.</p>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full">Step 1 of 4</span>
            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitStep" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- Company Name -->
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1.5">Company Name</label>
              <input
                type="text"
                v-model="form.company_name"
                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm placeholder-slate-400 outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all shadow-sm bg-white"
                placeholder="Enter Company Name"
              />
              <span v-if="errors.company_name" class="text-red-500 text-xs mt-1 font-medium block">{{ errors.company_name }}</span>
            </div>

            <!-- Registration Number -->
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1.5">Registration Number</label>
              <input
                type="text"
                v-model="form.registration_number"
                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm placeholder-slate-400 outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all shadow-sm bg-white"
                placeholder="e.g. 123456789"
              />
              <span v-if="errors.registration_number" class="text-red-500 text-xs mt-1 font-medium block">{{ errors.registration_number }}</span>
            </div>

            <!-- Company Email -->
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1.5">Company Email</label>
              <input
                type="email"
                v-model="form.company_email"
                readonly
                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm text-slate-500 cursor-not-allowed bg-slate-50 outline-none shadow-sm"
                placeholder="contact@company.com"
              />
              <span class="text-[11px] text-slate-400 mt-1 block">Tied to your registered account email.</span>
              <span v-if="errors.company_email" class="text-red-500 text-xs mt-1 font-medium block">{{ errors.company_email }}</span>
            </div>

            <!-- International Company Phone Input -->
            <div>
              <CompanyPhoneInput
                v-model="form.company_phone"
                label="Company Phone"
                :error="errors.company_phone"
                defaultCountry="PK"
              />
            </div>

            <!-- Owner Role -->
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1.5">Owner Role</label>
              <select
                v-model="form.owner_role"
                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm text-slate-700 outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all shadow-sm bg-white cursor-pointer"
              >
                <option value="Owner/CEO">Owner/CEO</option>
                <option value="Managing Director">Managing Director</option>
                <option value="Store Manager">Store Manager</option>
                <option value="Accountant/Financial Officer">Accountant/Financial Officer</option>
              </select>
              <span v-if="errors.owner_role" class="text-red-500 text-xs mt-1 font-medium block">{{ errors.owner_role }}</span>
            </div>

            <!-- Team Size -->
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1.5">Team Size</label>
              <select
                v-model="form.team_size"
                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm text-slate-700 outline-none focus:ring-2 focus:ring-slate-200/60 focus:border-slate-400 transition-all shadow-sm bg-white cursor-pointer"
              >
                <option value="Just Me">Just Me</option>
                <option value="2-5 People">2-5 People</option>
                <option value="6-20 People">6-20 People</option>
                <option value="21-50 People">21-50 People</option>
                <option value="51+ People">51+ People</option>
              </select>
              <span v-if="errors.team_size" class="text-red-500 text-xs mt-1 font-medium block">{{ errors.team_size }}</span>
            </div>

          </div>

          <!-- Buttons -->
          <div class="flex items-center justify-between pt-6 border-t border-slate-100 mt-6">
            <button
              type="button"
              @click="cancelSetup"
              class="px-5 py-2.5 border border-slate-200 text-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-50 transition-colors shadow-sm"
            >
              Cancel Setup
            </button>

            <button
              type="submit"
              class="px-6 py-2.5 bg-slate-900 text-white rounded-lg text-sm font-semibold hover:bg-slate-800 transition-colors shadow-md flex items-center gap-2"
            >
              <span>Continue</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import CompanyPhoneInput from './CompanyPhoneInput.vue';

const form = ref({
  company_name: '',
  registration_number: '',
  company_email: '',
  company_phone: '',
  owner_role: 'Owner/CEO',
  team_size: 'Just Me'
});

const errors = ref({});

const submitStep = () => {
  errors.value = {};
  if (!form.value.company_name || !form.value.company_name.trim()) {
    errors.value.company_name = 'Company Name is required to create or save a setup draft.';
  }
  if (!form.value.company_phone) {
    errors.value.company_phone = 'Company Phone is required.';
  }
  if (Object.keys(errors.value).length > 0) return;

  console.log('Submitted Enterprise Metrics:', form.value);
};

const cancelSetup = () => {
  window.location.href = '/dashboard';
};
</script>
