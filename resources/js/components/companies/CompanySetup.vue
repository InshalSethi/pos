<template>
  <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans overflow-x-hidden">
    <div class="sm:mx-auto sm:w-full sm:max-w-3xl px-4 sm:px-0">
      <!-- Card Container -->
      <div class="bg-white py-8 px-5 sm:px-10 shadow-xl rounded-2xl border border-slate-100 w-full max-w-full">
        
        <!-- Header & Stepper -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 mb-6 gap-4">
          <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Company Setup</h2>
            <p class="text-xs text-slate-500 mt-1">Please enter your accurate legal entity details.</p>
          </div>
          <!-- Stepper -->
          <div class="flex items-center gap-2 overflow-x-auto whitespace-nowrap pb-1 sm:pb-0 hide-scrollbar max-w-full">
            <div class="flex items-center gap-2 transition-opacity duration-300" :class="{'opacity-50': currentStep !== 1}">
              <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold"
                   :class="currentStep === 1 ? 'bg-slate-950 text-white' : 'bg-slate-200 text-slate-600'">1</div>
              <span class="text-xs font-bold text-slate-700">Basic Info</span>
            </div>
            <div class="w-6 sm:w-8 h-px bg-slate-200 shrink-0"></div>
            <div class="flex items-center gap-2 transition-opacity duration-300" :class="{'opacity-50': currentStep !== 2}">
              <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold"
                   :class="currentStep === 2 ? 'bg-slate-950 text-white' : 'bg-slate-200 text-slate-600'">2</div>
              <span class="text-xs font-bold text-slate-700">Settings</span>
            </div>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitStep" class="space-y-6 max-w-full overflow-hidden">
          
          <!-- Step 1: Basic Info -->
          <div v-show="currentStep === 1" class="grid grid-cols-1 md:grid-cols-2 gap-5 w-full">
            <!-- Company Name -->
            <div class="w-full">
              <label class="block text-xs font-bold text-slate-700 mb-1.5">Company Name <span class="text-rose-500">*</span></label>
              <input type="text" v-model="form.company_name" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm placeholder-slate-400 outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900 transition-all shadow-sm bg-white" placeholder="Enter Company Name" />
              <span v-if="errors.company_name" class="text-red-500 text-[10px] mt-1 font-medium block">{{ errors.company_name }}</span>
            </div>
            <!-- Registration Number -->
            <div class="w-full">
              <label class="block text-xs font-bold text-slate-700 mb-1.5">Registration Number <span class="text-rose-500">*</span></label>
              <input type="text" v-model="form.registration_number" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm placeholder-slate-400 outline-none focus:ring-2 focus:ring-slate-900 transition-all shadow-sm bg-white" placeholder="e.g. 123456789" />
              <span v-if="errors.registration_number" class="text-red-500 text-[10px] mt-1 font-medium block">{{ errors.registration_number }}</span>
            </div>
            <!-- Company Email -->
            <div class="w-full">
              <label class="block text-xs font-bold text-slate-700 mb-1.5">Company Email</label>
              <input type="email" v-model="form.company_email" readonly class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm text-slate-500 cursor-not-allowed bg-slate-50 outline-none shadow-sm" placeholder="contact@company.com" />
            </div>
            <!-- International Company Phone Input -->
            <div class="w-full">
              <CompanyPhoneInput v-model="form.company_phone" label="Company Phone" :error="errors.company_phone" defaultCountry="PK" />
            </div>
            <!-- Owner Role -->
            <div class="w-full">
              <label class="block text-xs font-bold text-slate-700 mb-1.5">Owner Role</label>
              <select v-model="form.owner_role" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm text-slate-700 outline-none focus:ring-2 focus:ring-slate-900 transition-all shadow-sm bg-white cursor-pointer">
                <option value="Owner/CEO">Owner/CEO</option>
                <option value="Managing Director">Managing Director</option>
                <option value="Store Manager">Store Manager</option>
              </select>
            </div>
            <!-- Team Size -->
            <div class="w-full">
              <label class="block text-xs font-bold text-slate-700 mb-1.5">Team Size</label>
              <select v-model="form.team_size" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm text-slate-700 outline-none focus:ring-2 focus:ring-slate-900 transition-all shadow-sm bg-white cursor-pointer">
                <option value="Just Me">Just Me</option>
                <option value="2-5 People">2-5 People</option>
                <option value="6-20 People">6-20 People</option>
                <option value="21+ People">21+ People</option>
              </select>
            </div>
          </div>

          <!-- Step 2: Settings -->
          <div v-show="currentStep === 2" class="space-y-5 w-full">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 w-full">
              <!-- Currency -->
              <div class="w-full">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Default Currency</label>
                <select v-model="form.currency" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm text-slate-700 outline-none focus:ring-2 focus:ring-slate-900 transition-all shadow-sm bg-white cursor-pointer">
                  <option value="USD">USD ($)</option>
                  <option value="EUR">EUR (€)</option>
                  <option value="PKR">PKR (Rs)</option>
                </select>
              </div>
              <!-- Tax/VAT Number -->
              <div class="w-full">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Tax/VAT Number</label>
                <input type="text" v-model="form.tax_number" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm placeholder-slate-400 outline-none focus:ring-2 focus:ring-slate-900 transition-all shadow-sm bg-white" placeholder="Enter Tax/VAT Number" />
              </div>
            </div>
            <!-- Address -->
            <div class="w-full">
              <label class="block text-xs font-bold text-slate-700 mb-1.5">Company Address</label>
              <textarea v-model="form.address" rows="2" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm placeholder-slate-400 outline-none focus:ring-2 focus:ring-slate-900 transition-all shadow-sm bg-white resize-none" placeholder="123 Business Avenue, Suite 100..."></textarea>
            </div>
            <!-- Company Logo Upload -->
            <div class="w-full">
              <label class="block text-xs font-bold text-slate-700 mb-1.5">Company Logo</label>
              <div class="w-full border-2 border-dashed border-slate-300 rounded-xl p-6 flex flex-col items-center justify-center text-center hover:bg-slate-50 transition-colors cursor-pointer bg-white">
                <svg class="w-8 h-8 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <p class="text-sm font-semibold text-slate-700">Click or drag & drop</p>
                <p class="text-xs text-slate-500 mt-1">SVG, PNG, JPG (max. 800x400px)</p>
              </div>
            </div>
          </div>

          <!-- Buttons -->
          <div class="flex flex-col-reverse sm:flex-row items-center justify-between pt-6 border-t border-slate-100 gap-3 w-full">
            <div>
              <button v-if="currentStep === 2" type="button" @click="currentStep = 1" class="w-full sm:w-auto px-5 py-2.5 border border-slate-200 text-slate-700 rounded-lg text-sm font-bold hover:bg-slate-50 transition-colors shadow-sm">
                Back
              </button>
            </div>
            
            <button v-if="currentStep === 1" type="button" @click="nextStep" class="w-full sm:w-auto px-6 py-2.5 bg-slate-950 text-white rounded-lg text-sm font-bold hover:bg-slate-800 transition-colors shadow-md flex justify-center items-center gap-2">
              <span>Next Step</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
            
            <button v-if="currentStep === 2" type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-slate-950 text-white rounded-lg text-sm font-bold hover:bg-slate-800 transition-colors shadow-md flex justify-center items-center gap-2">
              <span>Complete Setup</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
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

const currentStep = ref(1);

const form = ref({
  company_name: '',
  registration_number: '',
  company_email: '',
  company_phone: '',
  owner_role: 'Owner/CEO',
  team_size: 'Just Me',
  currency: 'USD',
  tax_number: '',
  address: '',
  logo: null
});

const errors = ref({});

const nextStep = () => {
  errors.value = {};
  if (!form.value.company_name || !form.value.company_name.trim()) {
    errors.value.company_name = 'Company Name is required.';
  }
  if (!form.value.registration_number || !form.value.registration_number.trim()) {
    errors.value.registration_number = 'Registration Number is required.';
  }
  if (Object.keys(errors.value).length === 0) {
    currentStep.value = 2;
  }
};

const submitStep = () => {
  console.log('Setup Complete:', form.value);
  window.location.href = '/dashboard';
};

const cancelSetup = () => {
  window.location.href = '/dashboard';
};
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
