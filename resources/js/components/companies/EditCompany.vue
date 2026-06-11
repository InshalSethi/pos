<template>
  <div class="space-y-6 max-w-6xl mx-auto">
    <!-- Header Section -->
    <div class="flex items-center justify-between pb-4 border-b border-gray-200">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Edit Workspace</h1>
        <p class="text-sm text-gray-500 mt-1">Manage your active production assets and corporate settings metadata.</p>
      </div>
      <router-link to="/companies" class="px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 shadow-sm transition-colors">
        Cancel & Return
      </router-link>
    </div>

    <!-- Success Toast -->
    <transition enter-active-class="transform ease-out duration-300 transition" enter-from-class="trangray-y-2 opacity-0 sm:trangray-y-0 sm:trangray-x-2" enter-to-class="trangray-y-0 opacity-100 sm:trangray-x-0" leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="successMessage" class="fixed bottom-4 right-4 z-50 p-4 rounded-xl bg-gray-900 border border-gray-700 text-white shadow-2xl flex items-center gap-3 w-80">
        <svg class="h-6 w-6 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-sm font-semibold">{{ successMessage }}</span>
      </div>
    </transition>

    <div v-if="loading" class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 flex items-center justify-center">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
    </div>

    <form v-else @submit.prevent="updateCompany" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <!-- 2 Column Layout -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 p-8 lg:p-10">
        
        <!-- Left Column -->
        <div class="lg:col-span-4 space-y-8">
            <div>
              <h3 class="text-sm font-bold text-gray-900 mb-5 border-b border-gray-100 pb-2">Account Management</h3>
              <div class="rounded-2xl border border-gray-200 overflow-hidden mb-5">
                  <!-- Logo Area -->
                  <div class="bg-[#f0d8c8] relative h-64 flex items-center justify-center">
                    <img v-if="logoPreview || form.company_logo_url" :src="logoPreview || form.company_logo_url" class="h-full w-full object-cover" alt="Company Logo" />
                    <div v-else class="text-[#a57053] text-6xl font-black uppercase">
                        {{ form.company_name ? form.company_name.charAt(0) : 'W' }}
                    </div>
                    
                    <button type="button" v-if="logoPreview || form.company_logo_url" class="absolute top-4 right-4 h-8 w-8 bg-gray-900/40 hover:bg-gray-900/60 rounded-full flex items-center justify-center text-white backdrop-blur-sm transition-all" @click.stop="clearLogo" title="Remove Logo">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                  </div>
              </div>
              <input type="file" ref="logoInput" class="hidden" accept="image/*" @change="handleLogoUpload">
              <button type="button" @click="$refs.logoInput.click()" class="w-full py-2.5 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500">
                Upload Photo
              </button>
              <p class="text-[10px] text-gray-400 mt-2 text-center uppercase tracking-wide">Step 2: Media Files (Max 2MB)</p>
            </div>
        </div>

        <!-- Right Column -->
        <div class="lg:col-span-8 space-y-10">
            
            <!-- Profile Info -->
            <div class="space-y-5">
              <h3 class="text-sm font-bold text-gray-900 mb-2 border-b border-gray-100 pb-2">Profile Information</h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-gray-600">Company Name</label>
                  <input v-model="form.company_name" type="text" required class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-md text-sm text-gray-900 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400 transition-colors">
                </div>
                
                <div class="relative space-y-1.5" @click.stop>
                  <label class="text-[11px] font-bold text-gray-600">Owner Role</label>
                  <button type="button" @click="toggleDropdown('owner_role')" class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-md text-sm flex justify-between items-center cursor-pointer text-gray-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    <span class="truncate">{{ form.owner_role ? ownerRoles[form.owner_role] : 'Select role...' }}</span>
                    <svg :class="dropdownOpen.owner_role ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                  </button>
                  <transition enter-active-class="transition ease-out duration-100" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                    <div v-show="dropdownOpen.owner_role" class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-100 rounded-md shadow-xl max-h-60 overflow-y-auto py-1">
                      <div v-for="(label, val) in ownerRoles" :key="val" @click="form.owner_role = val; dropdownOpen.owner_role = false" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 hover:text-indigo-600 font-medium cursor-pointer transition-colors flex items-center" :class="form.owner_role === val ? 'bg-indigo-50/50 text-indigo-700' : 'text-gray-700'">
                        {{ label }}
                      </div>
                    </div>
                  </transition>
                </div>

                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-gray-600">Registration Number</label>
                  <input v-model="form.registration_number" type="text" class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-md text-sm text-gray-900 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400 transition-colors" placeholder="Registration ID">
                </div>
                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-gray-600 flex items-center justify-between">
                    <span>Tax Number / STRN</span>
                  </label>
                  <input v-model="form.tax_number" type="text" class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-md text-sm text-gray-900 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400 transition-colors" placeholder="Tax Registration Number">
                </div>

                <div class="relative space-y-1.5" @click.stop>
                  <label class="text-[11px] font-bold text-gray-600">Team Size</label>
                  <button type="button" @click="toggleDropdown('team_size')" class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-md text-sm flex justify-between items-center cursor-pointer text-gray-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    <span class="truncate">{{ form.team_size ? teamSizes[form.team_size] : 'Select team size...' }}</span>
                    <svg :class="dropdownOpen.team_size ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                  </button>
                  <transition enter-active-class="transition ease-out duration-100" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                    <div v-show="dropdownOpen.team_size" class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-100 rounded-md shadow-xl max-h-60 overflow-y-auto py-1">
                      <div v-for="(label, val) in teamSizes" :key="val" @click="form.team_size = val; dropdownOpen.team_size = false" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 hover:text-indigo-600 font-medium cursor-pointer transition-colors flex items-center" :class="form.team_size === val ? 'bg-indigo-50/50 text-indigo-700' : 'text-gray-700'">
                        {{ label }}
                      </div>
                    </div>
                  </transition>
                </div>
              </div>
            </div>

            <!-- Contact Info -->
            <div class="space-y-5">
              <h3 class="text-sm font-bold text-gray-900 mb-2 border-b border-gray-100 pb-2">Contact Info</h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-gray-600">Email (required)</label>
                  <input v-model="form.company_email" type="email" required disabled class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-md text-sm text-gray-500 cursor-not-allowed">
                </div>
                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-gray-600">WhatsApp</label>
                  <input v-model="form.company_phone" type="text" class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-md text-sm text-gray-900 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400 transition-colors" placeholder="+92 300...">
                </div>
                <div class="space-y-1.5 sm:col-span-2">
                  <label class="text-[11px] font-bold text-gray-600">Website / Address</label>
                  <input v-model="form.business_address" type="text" class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-md text-sm text-gray-900 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400 transition-colors" placeholder="Company address or URL">
                </div>
              </div>
            </div>

            <!-- Framework Configuration -->
            <div class="space-y-5">
              <div class="border-b border-gray-100 pb-2">
                <h3 class="text-sm font-bold text-gray-900">Framework Configuration</h3>
                <p class="text-[11px] text-gray-500 mt-0.5">Establish the baseline regional settings for your ledger.</p>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                
                <div class="relative space-y-1.5" @click.stop>
                  <label class="text-[11px] font-bold text-gray-600">What does your company do?</label>
                  <button type="button" @click="toggleDropdown('business_type')" class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-md text-sm flex justify-between items-center cursor-pointer text-gray-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    <span class="truncate">{{ form.business_type ? businessTypes[form.business_type] : 'Select industry...' }}</span>
                    <svg :class="dropdownOpen.business_type ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                  </button>
                  <transition enter-active-class="transition ease-out duration-100" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                    <div v-show="dropdownOpen.business_type" class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-100 rounded-md shadow-xl max-h-60 overflow-y-auto py-1">
                      <div v-for="(label, val) in businessTypes" :key="val" @click="form.business_type = val; dropdownOpen.business_type = false" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 hover:text-indigo-600 font-medium cursor-pointer transition-colors flex items-center" :class="form.business_type === val ? 'bg-indigo-50/50 text-indigo-700' : 'text-gray-700'">
                        {{ label }}
                      </div>
                    </div>
                  </transition>
                </div>

                <div class="relative space-y-1.5" @click.stop>
                  <label class="text-[11px] font-bold text-gray-600">Business Scale</label>
                  <button type="button" @click="toggleDropdown('business_scale')" class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-md text-sm flex justify-between items-center cursor-pointer text-gray-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    <span class="truncate">{{ form.business_scale ? businessScales[form.business_scale] : 'Select scale...' }}</span>
                    <svg :class="dropdownOpen.business_scale ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                  </button>
                  <transition enter-active-class="transition ease-out duration-100" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                    <div v-show="dropdownOpen.business_scale" class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-100 rounded-md shadow-xl max-h-60 overflow-y-auto py-1">
                      <div v-for="(label, val) in businessScales" :key="val" @click="form.business_scale = val; dropdownOpen.business_scale = false" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 hover:text-indigo-600 font-medium cursor-pointer transition-colors flex items-center" :class="form.business_scale === val ? 'bg-indigo-50/50 text-indigo-700' : 'text-gray-700'">
                        {{ label }}
                      </div>
                    </div>
                  </transition>
                </div>

                <div class="relative space-y-1.5" @click.stop>
                  <label class="text-[11px] font-bold text-gray-600">Country Jurisdiction</label>
                  <button type="button" @click="toggleDropdown('country')" class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-md text-sm flex justify-between items-center cursor-pointer text-gray-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    <span class="truncate">{{ form.country ? countries[form.country] : 'Select country...' }}</span>
                    <svg :class="dropdownOpen.country ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                  </button>
                  <transition enter-active-class="transition ease-out duration-100" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                    <div v-show="dropdownOpen.country" class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-100 rounded-md shadow-xl max-h-60 overflow-y-auto py-1">
                      <div v-for="(label, val) in countries" :key="val" @click="form.country = val; dropdownOpen.country = false" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 hover:text-indigo-600 font-medium cursor-pointer transition-colors flex items-center" :class="form.country === val ? 'bg-indigo-50/50 text-indigo-700' : 'text-gray-700'">
                        {{ label }}
                      </div>
                    </div>
                  </transition>
                </div>

                <div class="relative space-y-1.5" @click.stop>
                  <label class="text-[11px] font-bold text-gray-600">System Language</label>
                  <button type="button" @click="toggleDropdown('system_language')" class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-md text-sm flex justify-between items-center cursor-pointer text-gray-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    <span class="truncate">{{ form.system_language ? systemLanguages[form.system_language] : 'Select language...' }}</span>
                    <svg :class="dropdownOpen.system_language ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                  </button>
                  <transition enter-active-class="transition ease-out duration-100" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                    <div v-show="dropdownOpen.system_language" class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-100 rounded-md shadow-xl max-h-60 overflow-y-auto py-1">
                      <div v-for="(label, val) in systemLanguages" :key="val" @click="form.system_language = val; dropdownOpen.system_language = false" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 hover:text-indigo-600 font-medium cursor-pointer transition-colors flex items-center" :class="form.system_language === val ? 'bg-indigo-50/50 text-indigo-700' : 'text-gray-700'">
                        {{ label }}
                      </div>
                    </div>
                  </transition>
                </div>

                <div class="relative space-y-1.5" @click.stop>
                  <label class="text-[11px] font-bold text-gray-600">Base Ledger Currency</label>
                  <button type="button" @click="toggleDropdown('base_currency')" class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-md text-sm flex justify-between items-center cursor-pointer text-gray-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    <span class="truncate">{{ form.base_currency ? baseCurrencies[form.base_currency] : 'Select currency...' }}</span>
                    <svg :class="dropdownOpen.base_currency ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                  </button>
                  <transition enter-active-class="transition ease-out duration-100" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                    <div v-show="dropdownOpen.base_currency" class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-100 rounded-md shadow-xl max-h-60 overflow-y-auto py-1">
                      <div v-for="(label, val) in baseCurrencies" :key="val" @click="form.base_currency = val; dropdownOpen.base_currency = false" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 hover:text-indigo-600 font-medium cursor-pointer transition-colors flex items-center" :class="form.base_currency === val ? 'bg-indigo-50/50 text-indigo-700' : 'text-gray-700'">
                        {{ label }}
                      </div>
                    </div>
                  </transition>
                </div>

                <div class="relative space-y-1.5" @click.stop>
                  <label class="text-[11px] font-bold text-gray-600">Timezone Anchor</label>
                  <button type="button" @click="toggleDropdown('timezone_offset')" class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-md text-sm flex justify-between items-center cursor-pointer text-gray-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    <span class="truncate">{{ form.timezone_offset ? timezones[form.timezone_offset] : 'Select timezone...' }}</span>
                    <svg :class="dropdownOpen.timezone_offset ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                  </button>
                  <transition enter-active-class="transition ease-out duration-100" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                    <div v-show="dropdownOpen.timezone_offset" class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-100 rounded-md shadow-xl max-h-60 overflow-y-auto py-1">
                      <div v-for="(label, val) in timezones" :key="val" @click="form.timezone_offset = val; dropdownOpen.timezone_offset = false" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 hover:text-indigo-600 font-medium cursor-pointer transition-colors flex items-center" :class="form.timezone_offset === val ? 'bg-indigo-50/50 text-indigo-700' : 'text-gray-700'">
                        {{ label }}
                      </div>
                    </div>
                  </transition>
                </div>

                <div class="space-y-1.5">
                  <label class="text-[11px] font-bold text-gray-600">Fiscal Year Start</label>
                  <input v-model="form.fiscal_year_start" type="date" class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-md text-sm text-gray-900 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                </div>
              </div>
            </div>

        </div>

      </div>

      <!-- Footer Actions -->
      <div class="px-10 py-5 bg-white border-t border-gray-100 flex items-center justify-end gap-3">
        <button type="submit" :disabled="saving" class="px-8 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:opacity-70 rounded-lg shadow-sm transition-all flex items-center gap-2">
          <svg v-if="saving" class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ saving ? 'Updating Data...' : 'Save Changes' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue';
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
  business_address: '',
  company_logo_url: null,
  owner_role: '',
  team_size: '',
  business_scale: '',
  tax_number: '',
  business_type: '',
  country: '',
  system_language: '',
  base_currency: '',
  timezone_offset: '',
  fiscal_year_start: '',
});

const companyId = ref(null);

const dropdownOpen = reactive({
    owner_role: false,
    team_size: false,
    business_type: false,
    business_scale: false,
    country: false,
    system_language: false,
    base_currency: false,
    timezone_offset: false
});

const toggleDropdown = (key) => {
    Object.keys(dropdownOpen).forEach(k => {
        if (k !== key) dropdownOpen[k] = false;
    });
    dropdownOpen[key] = !dropdownOpen[key];
};

const closeDropdowns = () => {
    Object.keys(dropdownOpen).forEach(k => dropdownOpen[k] = false);
};

const ownerRoles = {
    'Owner/CEO': 'Owner/CEO',
    'Managing Director': 'Managing Director',
    'Store Manager': 'Store Manager',
    'Accountant/Financial Officer': 'Accountant/Financial Officer',
};

const teamSizes = {
    'Just Me': 'Just Me',
    '2-5 People': '2-5 People',
    '6-20 People': '6-20 People',
    '21-50 People': '21-50 People',
    '51+ People': '51+ People',
};

const businessTypes = {
    'agriculture': 'Agriculture',
    'art_design': 'Art and Design',
    'construction_trades': 'Construction, Trades and Home Services',
    'development_programming': 'Development & Programming',
    'education_training': 'Education and Training',
    'financial_insurance': 'Financial services & insurance',
    'food_services': 'Food Services',
    'health_wellness': 'Health and Wellness',
    'hospitality_tourism': 'Hospitality, Travel and Tourism',
    'hr_staffing': 'Human Resources and Staffing',
    'it': 'Information Technology',
    'manufacturing': 'Manufacturing',
    'non_profit': 'Non-Profit',
    'professional_services': 'Professional Services (e.g. Legal, Accounting, Marketing, Consulting)',
    'real_estate': 'Real Estate and Property Management',
    'retail': 'Retail (E-Commerce and Offline)',
    'software_development': 'Software Development',
    'wholesale_trade': 'Wholesale Trade',
    'other': 'Other',
};

const businessScales = {
    'Single Outlet': 'Single Outlet',
    'Multi-Branch/Chain': 'Multi-Branch/Chain',
    'Wholesale Only': 'Wholesale Only',
};

const countries = {
    'United States': 'United States',
    'United Kingdom': 'United Kingdom',
    'Canada': 'Canada',
    'Australia': 'Australia',
    'Pakistan': 'Pakistan',
    'India': 'India',
    'United Arab Emirates': 'United Arab Emirates',
};

const systemLanguages = {
    'en': 'English',
    'ur': 'Urdu (اُردو)',
    'ar': 'Arabic (العربية)',
    'es': 'Spanish (Español)',
    'fr': 'French (Français)',
    'de': 'German (Deutsch)',
    'zh': 'Chinese (中文)',
    'hi': 'Hindi (हिन्दी)',
    'tr': 'Turkish (Türkçe)',
    'fa': 'Persian (فارسی)',
    'pt': 'Portuguese (Português)',
    'ru': 'Russian (Русский)',
    'ja': 'Japanese (日本語)',
    'id': 'Indonesian (Bahasa Indonesia)',
    'bn': 'Bengali (বাংলা)',
    'pa': 'Punjabi (پنجابی)',
    'it': 'Italian (Italiano)',
    'nl': 'Dutch (Nederlands)',
    'vi': 'Vietnamese (Tiếng Việt)',
    'sw': 'Swahili (Kiswahili)',
};

const baseCurrencies = {
    'PKR': 'PKR (₨) - Pakistani Rupee',
    'USD': 'USD ($) - United States Dollar',
    'GBP': 'GBP (£) - British Pound Sterling',
    'EUR': 'EUR (€) - Euro',
    'AED': 'AED (د.إ) - UAE Dirham',
    'SAR': 'SAR (ر.س) - Saudi Riyal',
    'CAD': 'CAD ($) - Canadian Dollar',
    'AUD': 'AUD ($) - Australian Dollar',
    'INR': 'INR (₹) - Indian Rupee',
    'CNY': 'CNY (¥) - Chinese Yuan',
    'TRY': 'TRY (₺) - Turkish Lira',
    'KWD': 'KWD (د.ك) - Kuwaiti Dinar',
    'QAR': 'QAR (ر.ق) - Qatari Riyal',
    'OMR': 'OMR (ر.ع.) - Omani Rial',
    'BHD': 'BHD (.د.ب) - Bahraini Dinar',
    'JPY': 'JPY (¥) - Japanese Yen',
    'SGD': 'SGD ($) - Singapore Dollar',
    'NZD': 'NZD ($) - New Zealand Dollar',
    'CHF': 'CHF (Fr) - Swiss Franc',
    'MYR': 'MYR (RM) - Malaysian Ringgit',
};

const timezones = {
    'UTC': 'UTC (Standard)',
    'EST': 'EST (Eastern Time)',
    'PST': 'PST (Pacific Time)',
    'GMT': 'GMT (Greenwich Time)',
    'PKT': 'PKT (Pakistan Time)',
};


onMounted(async () => {
  window.addEventListener('click', closeDropdowns);
  
  companyId.value = route.query.id;
  if (!companyId.value) {
    router.push('/companies');
    return;
  }
  await fetchCompany();
});

onUnmounted(() => {
  window.removeEventListener('click', closeDropdowns);
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
      business_address: data.business_address || '',
      owner_role: data.owner_role || '',
      team_size: data.team_size || '',
      business_scale: data.business_scale || '',
      tax_number: data.tax_number || '',
      business_type: data.business_type || '',
      country: data.country || '',
      system_language: data.system_language || '',
      base_currency: data.base_currency || '',
      timezone_offset: data.timezone_offset || '',
      fiscal_year_start: data.fiscal_year_start || '',
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

const clearLogo = () => {
  logoFile.value = null;
  logoPreview.value = null;
  form.value.company_logo_url = null;
  if (logoInput.value) {
    logoInput.value.value = '';
  }
};

const updateCompany = async () => {
  saving.value = true;
  successMessage.value = '';
  
  const formData = new FormData();
  formData.append('company_name', form.value.company_name);
  formData.append('company_phone', form.value.company_phone || '');
  formData.append('registration_number', form.value.registration_number || '');
  formData.append('tax_number', form.value.tax_number || '');
  formData.append('business_address', form.value.business_address || '');
  formData.append('owner_role', form.value.owner_role || '');
  formData.append('team_size', form.value.team_size || '');
  formData.append('business_scale', form.value.business_scale || '');
  formData.append('business_type', form.value.business_type || '');
  formData.append('country', form.value.country || '');
  formData.append('system_language', form.value.system_language || '');
  formData.append('base_currency', form.value.base_currency || '');
  formData.append('timezone_offset', form.value.timezone_offset || '');
  formData.append('fiscal_year_start', form.value.fiscal_year_start || '');
  
  if (logoFile.value) {
    formData.append('company_logo', logoFile.value);
  } else if (!form.value.company_logo_url) {
    formData.append('remove_logo', '1');
  }

  try {
    const response = await axios.post(`/api/companies/${companyId.value}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    successMessage.value = response.data.message;
    
    // Dispatch event to quickly update the sidebar panel without a refresh
    window.dispatchEvent(new CustomEvent('company-switched-globally'));
    
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
