<template>
  <div v-if="show" class="fixed inset-0 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 z-50 animate-in fade-in duration-200" @click.self="close">
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden transition-all text-left">
      
      <!-- Header -->
      <div class="p-6 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center bg-zinc-50/50 dark:bg-zinc-900/50 shrink-0">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-2xl bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-black text-base shadow-xs shrink-0">
            <i :class="isEditing ? 'fas fa-user-edit' : 'fas fa-user-plus'"></i>
          </div>
          <div>
            <h3 class="text-lg font-black text-zinc-950 dark:text-white tracking-tight">
              {{ isEditing ? 'Edit User Details' : 'Add New User & Business Workspace' }}
            </h3>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold">
              {{ isEditing ? 'Update user account details and operational status' : `Step ${currentStep} of 5: ${stepTitles[currentStep - 1]}` }}
            </p>
          </div>
        </div>

        <button @click="close" class="w-8 h-8 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 flex items-center justify-center transition-all cursor-pointer">
          <i class="fas fa-times text-xs"></i>
        </button>
      </div>

      <!-- Step Indicator Bar (Only for Create User Wizard) -->
      <div v-if="!isEditing" class="bg-zinc-100 dark:bg-zinc-950 px-6 py-3 border-b border-zinc-200 dark:border-zinc-800 shrink-0">
        <div class="flex items-center justify-between space-x-2">
          <div 
            v-for="(title, idx) in stepTitles" 
            :key="idx"
            @click="goToStep(idx + 1)"
            class="flex-1 text-center cursor-pointer transition-all"
          >
            <div 
              class="h-1.5 rounded-full mb-1.5 transition-all"
              :class="currentStep >= (idx + 1) ? 'bg-black dark:bg-white' : 'bg-zinc-200 dark:bg-zinc-800'"
            ></div>
            <span 
              class="text-[10px] font-extrabold uppercase tracking-wider block truncate"
              :class="currentStep === (idx + 1) ? 'text-black dark:text-white' : 'text-zinc-400 dark:text-zinc-600'"
            >
              {{ idx + 1 }}. {{ stepShortTitles[idx] }}
            </span>
          </div>
        </div>
      </div>

      <!-- Content / Form -->
      <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
        <!-- Loading State -->
        <div v-if="loading" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
          <i class="fas fa-circle-notch fa-spin text-3xl mb-3 text-black dark:text-white"></i>
          <p class="text-xs font-bold uppercase tracking-wider">Fetching User Information...</p>
        </div>

        <form v-else @submit.prevent="handleFormSubmit" class="space-y-5">
          <!-- Error Alert -->
          <div v-if="errorMessage" class="bg-rose-50 border border-rose-200 text-rose-700 dark:bg-rose-950/40 dark:border-rose-900 dark:text-rose-400 p-4 rounded-2xl text-xs font-bold flex items-start space-x-3">
            <i class="fas fa-exclamation-circle text-base shrink-0 mt-0.5"></i>
            <div>
              <p>{{ errorMessage }}</p>
              <ul v-if="validationErrors && Object.keys(validationErrors).length > 0" class="mt-2 list-disc list-inside space-y-1">
                <li v-for="(errors, field) in validationErrors" :key="field">
                  {{ errors[0] }}
                </li>
              </ul>
            </div>
          </div>

          <!-- ================= EDIT MODE / STEP 1: USER DETAILS ================= -->
          <div v-if="isEditing || currentStep === 1" class="space-y-5">
            <!-- Full Name -->
            <div>
              <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
                Full Name <span class="text-rose-500">*</span>
              </label>
              <input 
                type="text" 
                v-model="form.name" 
                required 
                class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold placeholder-zinc-400 dark:placeholder-zinc-600" 
                placeholder="e.g. John Doe"
              >
            </div>

            <!-- Email Address -->
            <div>
              <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
                Email Address <span class="text-rose-500">*</span>
              </label>
              <input 
                type="email" 
                v-model="form.email" 
                required 
                class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold placeholder-zinc-400 dark:placeholder-zinc-600" 
                placeholder="user@example.com"
              >
            </div>

            <!-- Phone Number -->
            <div>
              <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
                Phone Number
              </label>
              <input 
                type="text" 
                v-model="form.phone" 
                class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold placeholder-zinc-400 dark:placeholder-zinc-600" 
                placeholder="+1 (555) 000-0000"
              >
            </div>

            <!-- Password -->
            <div>
              <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
                Password <span v-if="!isEditing" class="text-rose-500">*</span>
              </label>
              <input 
                type="password" 
                v-model="form.password" 
                :required="!isEditing" 
                class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold placeholder-zinc-400 dark:placeholder-zinc-600" 
                :placeholder="isEditing ? 'Leave blank to keep current password' : 'Create a secure password'"
              >
              <p v-if="isEditing" class="text-[11px] font-semibold text-zinc-400 mt-1">Leave blank if you do not wish to change password.</p>
            </div>

            <!-- Active Status Toggle -->
            <div class="p-4 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl">
              <label class="flex items-center justify-between cursor-pointer select-none">
                <div>
                  <span class="text-xs font-extrabold uppercase tracking-wider text-zinc-900 dark:text-white block">User Active Status</span>
                  <span class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 block mt-0.5">
                    {{ form.is_active ? 'Account is Active and permitted to log into POS.' : 'Account is Inactive / Disabled from accessing POS.' }}
                  </span>
                </div>
                <div class="relative shrink-0 ml-4">
                  <input type="checkbox" v-model="form.is_active" class="sr-only">
                  <div 
                    class="block w-12 h-7 rounded-full transition-colors cursor-pointer" 
                    :class="form.is_active ? 'bg-black dark:bg-white' : 'bg-zinc-300 dark:bg-zinc-700'"
                  ></div>
                  <div 
                    class="dot absolute left-1 top-1 w-5 h-5 rounded-full transition-transform shadow-xs pointer-events-none" 
                    :class="form.is_active ? 'translate-x-5 bg-white dark:bg-zinc-900' : 'translate-x-0 bg-white dark:bg-zinc-900'"
                  ></div>
                </div>
              </label>
            </div>
          </div>

          <!-- ================= STEP 2: BUSINESS IDENTITY & TEAM ================= -->
          <div v-if="!isEditing && currentStep === 2" class="space-y-5">
            <div>
              <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
                Company Name <span class="text-rose-500">*</span>
              </label>
              <input 
                type="text" 
                v-model="form.company_name" 
                required 
                class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold" 
                placeholder="e.g. Acme Enterprise"
              >
            </div>

            <div>
              <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
                Registration Number
              </label>
              <input 
                type="text" 
                v-model="form.registration_number" 
                class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold" 
                placeholder="e.g. REG-987654"
              >
            </div>

            <div>
              <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
                Company Phone Number
              </label>
              <input 
                type="text" 
                v-model="form.company_phone" 
                class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold" 
                placeholder="+1 (555) 123-4567"
              >
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
                  Owner Role
                </label>
                <select 
                  v-model="form.owner_role" 
                  class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold cursor-pointer"
                >
                  <option value="Owner/CEO">Owner/CEO</option>
                  <option value="Managing Director">Managing Director</option>
                  <option value="Store Manager">Store Manager</option>
                  <option value="Accountant/Financial Officer">Accountant/Financial Officer</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
                  Team Size
                </label>
                <select 
                  v-model="form.team_size" 
                  class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold cursor-pointer"
                >
                  <option value="Just Me">Just Me</option>
                  <option value="2-5 People">2-5 People</option>
                  <option value="6-20 People">6-20 People</option>
                  <option value="21-50 People">21-50 People</option>
                  <option value="51+ People">51+ People</option>
                </select>
              </div>
            </div>
          </div>

          <!-- ================= STEP 3: TAX & ADDRESS ================= -->
          <div v-if="!isEditing && currentStep === 3" class="space-y-5">
            <div>
              <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
                Tax / VAT Number
              </label>
              <input 
                type="text" 
                v-model="form.tax_number" 
                class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold" 
                placeholder="e.g. TAX-123456789"
              >
            </div>

            <div>
              <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
                Business Address
              </label>
              <textarea 
                v-model="form.business_address" 
                rows="3"
                class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold" 
                placeholder="100 Commercial Avenue, Suite 500..."
              ></textarea>
            </div>
          </div>

          <!-- ================= STEP 4: INTENDED USAGE & TASKS ================= -->
          <div v-if="!isEditing && currentStep === 4" class="space-y-4">
            <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">
              Select Intended Tasks / Modules
            </label>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold mb-3">
              Select the primary features this business workspace will utilize.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <label 
                v-for="task in availableTasks" 
                :key="task.id" 
                class="p-4 border rounded-2xl cursor-pointer flex items-center space-x-3 transition-all"
                :class="form.intended_tasks.includes(task.id) ? 'border-black bg-zinc-50 dark:border-white dark:bg-zinc-800/60' : 'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900'"
              >
                <input 
                  type="checkbox" 
                  :value="task.id" 
                  v-model="form.intended_tasks"
                  class="w-4 h-4 text-black dark:text-white rounded border-zinc-300 focus:ring-0 cursor-pointer"
                >
                <div>
                  <span class="text-xs font-black text-zinc-900 dark:text-white block">{{ task.name }}</span>
                  <span class="text-[11px] text-zinc-500 dark:text-zinc-400 block font-medium">{{ task.desc }}</span>
                </div>
              </label>
            </div>
          </div>

          <!-- ================= STEP 5: REGIONAL LOCALIZATION & FINANCE ================= -->
          <div v-if="!isEditing && currentStep === 5" class="space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
                  Business Type
                </label>
                <select 
                  v-model="form.business_type" 
                  class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold cursor-pointer"
                >
                  <option v-for="type in businessTypes" :key="type.id || type.name" :value="type.name">
                    {{ type.name }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
                  Business Scale
                </label>
                <select 
                  v-model="form.business_scale" 
                  class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold cursor-pointer"
                >
                  <option value="Single Outlet">Single Outlet</option>
                  <option value="Multi-Branch">Multi-Branch</option>
                  <option value="Franchise">Franchise</option>
                  <option value="Enterprise">Enterprise</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
                  Country
                </label>
                <select 
                  v-model="form.country" 
                  class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold cursor-pointer"
                >
                  <option value="United States">United States</option>
                  <option value="Pakistan">Pakistan</option>
                  <option value="United Kingdom">United Kingdom</option>
                  <option value="Canada">Canada</option>
                  <option value="United Arab Emirates">United Arab Emirates</option>
                  <option value="Saudi Arabia">Saudi Arabia</option>
                  <option value="Australia">Australia</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
                  Base Currency
                </label>
                <select 
                  v-model="form.base_currency" 
                  class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold cursor-pointer"
                >
                  <option value="USD">USD ($)</option>
                  <option value="PKR">PKR (Rs)</option>
                  <option value="EUR">EUR (€)</option>
                  <option value="GBP">GBP (£)</option>
                  <option value="AED">AED (AED)</option>
                  <option value="SAR">SAR (SR)</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
                  System Language
                </label>
                <select 
                  v-model="form.system_language" 
                  class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold cursor-pointer"
                >
                  <option value="en">English</option>
                  <option value="ur">Urdu</option>
                  <option value="ar">Arabic</option>
                  <option value="es">Spanish</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
                  Timezone
                </label>
                <select 
                  v-model="form.timezone_offset" 
                  class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold cursor-pointer"
                >
                  <option value="UTC">UTC (Universal Coordinated Time)</option>
                  <option value="PKT">PKT (Pakistan Standard Time GMT+5)</option>
                  <option value="EST">EST (Eastern Standard Time GMT-5)</option>
                  <option value="PST">PST (Pacific Standard Time GMT-8)</option>
                  <option value="GMT">GMT (Greenwich Mean Time GMT+0)</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-extrabold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">
                  Fiscal Year Start Date
                </label>
                <input 
                  type="date" 
                  v-model="form.fiscal_year_start" 
                  class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold"
                >
              </div>
            </div>
          </div>

          <!-- Hidden Submit Button for Enter Key -->
          <button type="submit" class="hidden"></button>
        </form>
      </div>

      <!-- Footer -->
      <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 flex justify-between items-center shrink-0">
        <div>
          <button
            v-if="!isEditing && currentStep > 1"
            type="button"
            @click="prevStep"
            class="px-5 py-2.5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 font-extrabold text-xs rounded-xl transition-all cursor-pointer flex items-center"
          >
            <i class="fas fa-arrow-left mr-2"></i> Previous
          </button>
        </div>

        <div class="flex space-x-3">
          <button
            type="button"
            @click="close"
            class="px-5 py-2.5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 font-extrabold text-xs rounded-xl transition-all cursor-pointer"
          >
            Cancel
          </button>

          <!-- Next Step Button (Wizard mode steps 1-4) -->
          <button
            v-if="!isEditing && currentStep < 5"
            type="button"
            @click="nextStep"
            class="px-6 py-2.5 bg-black hover:bg-zinc-800 text-white dark:bg-white dark:hover:bg-zinc-200 dark:text-black font-extrabold text-xs rounded-xl shadow-xs transition-all flex items-center cursor-pointer"
          >
            Next Step <i class="fas fa-arrow-right ml-2"></i>
          </button>

          <!-- Final Save / Submit Button -->
          <button
            v-if="isEditing || currentStep === 5"
            type="button"
            @click="submitForm"
            :disabled="submitting || loading"
            class="px-6 py-2.5 bg-black hover:bg-zinc-800 text-white dark:bg-white dark:hover:bg-zinc-200 dark:text-black font-extrabold text-xs rounded-xl shadow-xs transition-all disabled:opacity-50 flex items-center cursor-pointer"
          >
            <i v-if="submitting" class="fas fa-spinner fa-spin mr-2"></i>
            <i v-else class="fas fa-save mr-2"></i>
            {{ isEditing ? 'Save Changes' : 'Create User & Setup Company' }}
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  userId: {
    type: [Number, String],
    default: null
  }
});

const emit = defineEmits(['close', 'saved']);

const isEditing = computed(() => !!props.userId);
const loading = ref(false);
const submitting = ref(false);
const errorMessage = ref('');
const validationErrors = ref({});
const currentStep = ref(1);

const stepTitles = [
  'User Account Credentials',
  'Business Identity & Team',
  'Tax & Location Address',
  'Intended System Usage',
  'Regional Localization & Finance'
];

const stepShortTitles = [
  'Account',
  'Company',
  'Tax/Address',
  'Usage',
  'Regional'
];

const availableTasks = [
  { id: 'pos', name: 'Point of Sale (POS)', desc: 'Fast checkout counter & register operations' },
  { id: 'inventory', name: 'Inventory Management', desc: 'Track stock, variations, and warehouses' },
  { id: 'invoicing', name: 'Invoicing & Sales', desc: 'Generate quotes, invoices, and payment receipts' },
  { id: 'accounting', name: 'Accounting & Reports', desc: 'General ledger, journal entries, and balance sheets' },
  { id: 'hr', name: 'HR & Payroll', desc: 'Employee records, attendance, and payroll processing' }
];

const businessTypes = ref([
  { id: 1, name: 'Retail Store' },
  { id: 2, name: 'Supermarket / Grocery' },
  { id: 3, name: 'Pharmacy / Medical' },
  { id: 4, name: 'Restaurant / Cafe' },
  { id: 5, name: 'Wholesale / Distributor' },
  { id: 6, name: 'Service / Repair Shop' },
  { id: 7, name: 'Hardware & Electronics' },
  { id: 8, name: 'Apparel & Fashion' }
]);

const fetchBusinessTypes = async () => {
  try {
    const { data } = await axios.get('/admin/api/options/business-types');
    if (Array.isArray(data) && data.length > 0) {
      businessTypes.value = data;
    }
  } catch (e) {
    console.error("Failed to load business types options", e);
  }
};

const form = ref({
  // User Credentials
  name: '',
  email: '',
  phone: '',
  password: '',
  is_active: true,

  // Company Details
  company_name: '',
  registration_number: '',
  company_phone: '',
  owner_role: 'Owner/CEO',
  team_size: 'Just Me',

  // Tax & Address
  tax_number: '',
  business_address: '',

  // Usage & Tasks
  intended_tasks: ['pos', 'inventory'],

  // Regional & Finance
  business_type: 'Retail Store',
  business_scale: 'Single Outlet',
  country: 'United States',
  system_language: 'en',
  base_currency: 'USD',
  timezone_offset: 'UTC',
  fiscal_year_start: '2026-01-01'
});

const resetForm = () => {
  currentStep.value = 1;
  form.value = {
    name: '',
    email: '',
    phone: '',
    password: '',
    is_active: true,

    company_name: '',
    registration_number: '',
    company_phone: '',
    owner_role: 'Owner/CEO',
    team_size: 'Just Me',

    tax_number: '',
    business_address: '',

    intended_tasks: ['pos', 'inventory'],

    business_type: 'Retail Store',
    business_scale: 'Single Outlet',
    country: 'United States',
    system_language: 'en',
    base_currency: 'USD',
    timezone_offset: 'UTC',
    fiscal_year_start: '2026-01-01'
  };
  errorMessage.value = '';
  validationErrors.value = {};
};

const goToStep = (step) => {
  if (isEditing.value) return;
  currentStep.value = step;
};

const nextStep = () => {
  errorMessage.value = '';
  // Validate Step 1
  if (currentStep.value === 1) {
    if (!form.value.name || !form.value.name.trim()) {
      errorMessage.value = 'Full Name is required.';
      return;
    }
    if (!form.value.email || !form.value.email.trim()) {
      errorMessage.value = 'Email Address is required.';
      return;
    }
    if (!form.value.password) {
      errorMessage.value = 'Password is required for new user creation.';
      return;
    }
  }
  // Validate Step 2
  if (currentStep.value === 2) {
    if (!form.value.company_name || !form.value.company_name.trim()) {
      errorMessage.value = 'Company Name is required.';
      return;
    }
  }

  if (currentStep.value < 5) {
    currentStep.value++;
  }
};

const prevStep = () => {
  errorMessage.value = '';
  if (currentStep.value > 1) {
    currentStep.value--;
  }
};

const loadUser = async () => {
  if (!props.userId) return;
  loading.value = true;
  errorMessage.value = '';
  try {
    const { data } = await axios.get(`/admin/api/users/${props.userId}`);
    const user = data.data;
    form.value.name = user.name || '';
    form.value.email = user.email || '';
    form.value.phone = user.phone || '';
    form.value.password = '';
    form.value.is_active = Boolean(user.is_active);
  } catch (e) {
    console.error("Failed to load user", e);
    errorMessage.value = 'Failed to load user details.';
  } finally {
    loading.value = false;
  }
};

const close = () => {
  resetForm();
  emit('close');
};

const handleFormSubmit = () => {
  if (!isEditing.value && currentStep.value < 5) {
    nextStep();
  } else {
    submitForm();
  }
};

const submitForm = async () => {
  if (submitting.value) return;

  // Validation before submit
  if (!form.value.name || !form.value.name.trim()) {
    errorMessage.value = 'Full Name is required.';
    currentStep.value = 1;
    return;
  }
  if (!form.value.email || !form.value.email.trim()) {
    errorMessage.value = 'Email Address is required.';
    currentStep.value = 1;
    return;
  }
  if (!isEditing.value && !form.value.password) {
    errorMessage.value = 'Password is required.';
    currentStep.value = 1;
    return;
  }
  if (!isEditing.value && (!form.value.company_name || !form.value.company_name.trim())) {
    errorMessage.value = 'Company Name is required.';
    currentStep.value = 2;
    return;
  }

  submitting.value = true;
  errorMessage.value = '';
  validationErrors.value = {};

  try {
    if (isEditing.value) {
      const payload = {
        name: form.value.name,
        email: form.value.email,
        phone: form.value.phone,
        is_active: Boolean(form.value.is_active)
      };
      if (form.value.password) {
        payload.password = form.value.password;
      }
      await axios.put(`/admin/api/users/${props.userId}`, payload);
    } else {
      const payload = {
        ...form.value,
        is_active: Boolean(form.value.is_active)
      };
      await axios.post('/admin/api/users', payload);
    }

    emit('saved');
    close();
  } catch (e) {
    if (e.response && e.response.status === 422) {
      errorMessage.value = 'Please correct the validation errors below.';
      validationErrors.value = e.response.data.errors;
    } else {
      errorMessage.value = e.response?.data?.message || 'An error occurred while saving user.';
    }
  } finally {
    submitting.value = false;
  }
};

watch(() => props.show, (newVal) => {
  if (newVal) {
    resetForm();
    fetchBusinessTypes();
    if (props.userId) {
      loadUser();
    }
  }
});

watch(() => props.userId, (newVal) => {
  if (props.show && newVal) {
    loadUser();
  }
});
</script>
