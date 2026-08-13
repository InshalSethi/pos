<template>
  <div v-if="show" class="fixed inset-0 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 z-50 animate-in fade-in duration-200" @click.self="close">
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden transition-all text-left">
      
      <!-- Header section -->
      <div class="p-6 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center bg-zinc-50/50 dark:bg-zinc-900/50 shrink-0">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 rounded-2xl bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-black text-lg shadow-md shrink-0">
            {{ userDetail?.name ? userDetail.name.substring(0, 1).toUpperCase() : 'U' }}
          </div>
          <div>
            <div class="flex items-center space-x-2">
              <h3 class="text-xl font-black text-zinc-950 dark:text-white tracking-tight">{{ userDetail?.name || 'User Details' }}</h3>
              <span v-if="userDetail?.is_active" class="inline-block bg-black text-white dark:bg-white dark:text-black text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow-xs">
                Active
              </span>
              <span v-else-if="userDetail" class="inline-block bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider border border-zinc-200 dark:border-zinc-700">
                Inactive
              </span>
            </div>
            <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mt-0.5">{{ userDetail?.email || 'Loading...' }}</p>
          </div>
        </div>

        <button @click="close" class="w-9 h-9 rounded-2xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 flex items-center justify-center transition-all cursor-pointer">
          <i class="fas fa-times text-sm"></i>
        </button>
      </div>

      <!-- Navigation Tabs -->
      <div class="px-6 pt-4 bg-zinc-50/30 dark:bg-zinc-900/30 border-b border-zinc-200 dark:border-zinc-800 flex space-x-2 shrink-0">
        <button
          @click="activeTab = 'basic'"
          class="px-5 py-2.5 rounded-t-2xl text-xs font-extrabold transition-all flex items-center gap-2 cursor-pointer"
          :class="activeTab === 'basic' 
            ? 'bg-white dark:bg-zinc-900 text-black dark:text-white border-t-2 border-x border-b-0 border-zinc-200 dark:border-zinc-800 shadow-xs' 
            : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800/50'"
        >
          <i class="fas fa-user-circle text-sm"></i>
          <span>Basic Information</span>
        </button>

        <button
          @click="switchTab('business')"
          class="px-5 py-2.5 rounded-t-2xl text-xs font-extrabold transition-all flex items-center gap-2 cursor-pointer"
          :class="activeTab === 'business' 
            ? 'bg-white dark:bg-zinc-900 text-black dark:text-white border-t-2 border-x border-b-0 border-zinc-200 dark:border-zinc-800 shadow-xs' 
            : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800/50'"
        >
          <i class="fas fa-building text-sm"></i>
          <span>Business Information</span>
          <span v-if="userDetail?.all_companies" class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-zinc-200 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 font-extrabold">
            {{ userDetail.all_companies.length }}
          </span>
        </button>
      </div>

      <!-- Body / Tab Content -->
      <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
        
        <!-- Loading Spinner for User detail -->
        <div v-if="loadingUser" class="py-16 text-center text-zinc-500 dark:text-zinc-400">
          <i class="fas fa-circle-notch fa-spin text-3xl mb-3 text-black dark:text-white"></i>
          <p class="text-xs font-bold uppercase tracking-wider">Fetching User Profile & Company Information...</p>
        </div>

        <template v-else-if="userDetail">
          <!-- TAB 1: BASIC INFORMATION -->
          <div v-if="activeTab === 'basic'" class="space-y-6">
            <!-- User Basic Information Header -->
            <div class="border-b border-zinc-200 dark:border-zinc-800 pb-2">
              <h4 class="text-xs font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">User Account Information</h4>
            </div>

            <!-- Account Overview Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Full Name -->
              <div class="p-4 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-1">Full Name</span>
                <p class="text-sm font-black text-zinc-900 dark:text-white">{{ userDetail.name }}</p>
              </div>

              <!-- Email Address -->
              <div class="p-4 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-1">Email Address</span>
                <p class="text-sm font-black text-zinc-900 dark:text-white">{{ userDetail.email }}</p>
              </div>

              <!-- Phone Number -->
              <div class="p-4 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-1">Phone Number</span>
                <p class="text-sm font-black text-zinc-900 dark:text-white">{{ userDetail.phone || 'Not Provided' }}</p>
              </div>

              <!-- Account Status -->
              <div class="p-4 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-1">Account Status</span>
                <p class="text-sm font-black text-zinc-900 dark:text-white">
                  {{ userDetail.is_active ? 'Active & Operational' : 'Inactive / Suspended' }}
                </p>
              </div>

              <!-- Address -->
              <div class="p-4 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-1">Address</span>
                <p class="text-xs font-bold text-zinc-800 dark:text-zinc-200">{{ userDetail.address || 'No address registered' }}</p>
              </div>

              <!-- Onboarding Status -->
              <div class="p-4 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-1">Onboarding Setup</span>
                <p class="text-xs font-bold text-zinc-800 dark:text-zinc-200">
                  {{ userDetail.is_setup_completed ? 'Completed' : 'Pending Onboarding' }}
                </p>
              </div>
            </div>

            <!-- COMPANY INFORMATION SUMMARY CARD -->
            <div class="pt-4">
              <div class="border-b border-zinc-200 dark:border-zinc-800 pb-2 mb-4 flex items-center justify-between">
                <h4 class="text-xs font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Company Information</h4>
                <span v-if="primaryCompany" class="text-xs font-extrabold px-2.5 py-0.5 rounded-full bg-black text-white dark:bg-white dark:text-black">
                  {{ primaryCompany.company_name }}
                </span>
              </div>

              <!-- IF USER HAS AT LEAST 1 COMPANY -->
              <div v-if="primaryCompany" class="bg-zinc-50 dark:bg-zinc-950/80 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 space-y-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-black text-base shadow-xs shrink-0">
                      {{ primaryCompany.company_name.substring(0, 1).toUpperCase() }}
                    </div>
                    <div>
                      <h5 class="text-base font-black text-zinc-950 dark:text-white">{{ primaryCompany.company_name }}</h5>
                      <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">ID: #{{ primaryCompany.id }} &bull; Active Workspace</p>
                    </div>
                  </div>
                  <button 
                    @click="fetchAndShowCompany(primaryCompany.id)"
                    class="px-3 py-1.5 rounded-xl bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-black dark:hover:bg-zinc-200 font-extrabold text-xs transition-all shadow-xs cursor-pointer flex items-center gap-1.5"
                  >
                    <i class="fas fa-eye text-[10px]"></i> View Full Details
                  </button>
                </div>

                <!-- Company Key Data Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-left">
                  <div class="p-3 bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-xl">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Email Address</span>
                    <p class="text-xs font-black text-zinc-900 dark:text-white truncate">{{ primaryCompany.company_email }}</p>
                  </div>

                  <div class="p-3 bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-xl">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Phone Number</span>
                    <p class="text-xs font-black text-zinc-900 dark:text-white">{{ primaryCompany.company_phone || 'Not Provided' }}</p>
                  </div>

                  <div class="p-3 bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-xl">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Tax Number (TRN/VAT)</span>
                    <p class="text-xs font-black text-zinc-900 dark:text-white">{{ primaryCompany.tax_number || 'Not Provided' }}</p>
                  </div>

                  <div class="p-3 bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-xl">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Registration Number</span>
                    <p class="text-xs font-black text-zinc-900 dark:text-white">{{ primaryCompany.registration_number || 'Not Provided' }}</p>
                  </div>

                  <div class="p-3 bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-xl">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Country / Region</span>
                    <p class="text-xs font-black text-zinc-900 dark:text-white">{{ primaryCompany.country || 'Not Provided' }}</p>
                  </div>

                  <div class="p-3 bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-xl">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Base Currency</span>
                    <p class="text-xs font-black text-zinc-900 dark:text-white">{{ primaryCompany.base_currency || 'USD' }}</p>
                  </div>

                  <div class="p-3 bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-xl md:col-span-3">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Business Address</span>
                    <p class="text-xs font-bold text-zinc-900 dark:text-white">{{ primaryCompany.business_address || 'Not Provided' }}</p>
                  </div>
                </div>
              </div>

              <!-- IF USER HAS NO COMPANIES (like testuser@example.com) -->
              <div v-else class="p-6 bg-zinc-50 dark:bg-zinc-950/60 border border-dashed border-zinc-300 dark:border-zinc-800 rounded-2xl text-center">
                <i class="fas fa-building text-2xl text-zinc-400 dark:text-zinc-600 mb-2"></i>
                <p class="text-xs font-bold text-zinc-700 dark:text-zinc-300">No company registered for this user in the database.</p>
                <p class="text-[11px] text-zinc-400 dark:text-zinc-500 mt-1">This user account has not set up or completed company registration yet.</p>
              </div>
            </div>

            <!-- Notes if present -->
            <div v-if="userDetail.notes" class="p-4 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-1">Additional Notes</span>
              <p class="text-xs text-zinc-700 dark:text-zinc-300 font-medium whitespace-pre-wrap">{{ userDetail.notes }}</p>
            </div>
          </div>

          <!-- TAB 2: BUSINESS INFORMATION (COMPANIES DATATABLE) -->
          <div v-else-if="activeTab === 'business'">
            <div class="mb-4 flex items-center justify-between">
              <div>
                <h4 class="text-sm font-black text-zinc-950 dark:text-white tracking-tight">Registered Companies</h4>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Ajax server-side datatable showing all business companies created or owned by this user.</p>
              </div>
            </div>

            <!-- Companies Server-Side Datatable -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-sm">
              <DataTable
                ref="companiesDataTable"
                :endpoint="`/admin/api/users/${userId}/companies-data`"
                :columns="companyColumns"
              >
                <!-- Company Name Slot -->
                <template #cell(company_name)="{ item }">
                  <div class="flex items-center">
                    <div class="w-8 h-8 rounded-xl bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-black text-xs mr-3 shrink-0 shadow-xs">
                      {{ item.company_name ? item.company_name.substring(0, 1).toUpperCase() : 'C' }}
                    </div>
                    <div>
                      <span class="font-bold text-zinc-900 dark:text-white block text-xs">{{ item.company_name }}</span>
                      <span class="text-[10px] text-zinc-400">{{ item.country }}</span>
                    </div>
                  </div>
                </template>

                <!-- Status Slot -->
                <template #cell(status)="{ value }">
                  <span class="inline-block bg-black text-white dark:bg-white dark:text-black text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow-xs">
                    {{ value || 'Active' }}
                  </span>
                </template>

                <!-- Action Button Slot -->
                <template #cell(actions)="{ item }">
                  <button
                    @click="fetchAndShowCompany(item.id)"
                    class="px-3 py-1.5 rounded-xl bg-black text-white hover:bg-zinc-800 dark:bg-white dark:text-black dark:hover:bg-zinc-200 font-extrabold text-xs transition-all shadow-xs flex items-center space-x-1.5 cursor-pointer"
                    title="View Company Basic Information"
                  >
                    <i class="fas fa-eye text-[10px]"></i>
                    <span>View Info</span>
                  </button>
                </template>
              </DataTable>
            </div>
          </div>
        </template>
      </div>

      <!-- Footer -->
      <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 flex justify-end shrink-0">
        <button
          @click="close"
          class="px-5 py-2 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 font-extrabold text-xs rounded-xl transition-all cursor-pointer"
        >
          Close
        </button>
      </div>
    </div>

    <!-- NESTED COMPANY BASIC INFORMATION MODAL -->
    <div v-if="selectedCompany" class="fixed inset-0 bg-black/80 backdrop-blur-xs flex items-center justify-center p-4 z-[60] animate-in fade-in duration-150" @click.self="selectedCompany = null">
      <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden text-left">
        
        <!-- Company Modal Header -->
        <div class="p-6 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center bg-zinc-50/50 dark:bg-zinc-900/50 shrink-0">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-2xl bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-black text-base shadow-xs shrink-0">
              <i class="fas fa-building"></i>
            </div>
            <div>
              <h4 class="text-lg font-black text-zinc-950 dark:text-white tracking-tight">{{ selectedCompany.company_name }}</h4>
              <p class="text-xs text-zinc-500 dark:text-zinc-400 font-bold">Company Basic Information (Saved during registration)</p>
            </div>
          </div>
          <button @click="selectedCompany = null" class="w-8 h-8 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 flex items-center justify-center transition-all cursor-pointer">
            <i class="fas fa-times text-xs"></i>
          </button>
        </div>

        <!-- Company Modal Content -->
        <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-6">
          
          <!-- Key Details Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-3.5 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/70 dark:border-zinc-800 rounded-xl">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Company Name</span>
              <p class="text-xs font-black text-zinc-950 dark:text-white">{{ selectedCompany.company_name }}</p>
            </div>

            <div class="p-3.5 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/70 dark:border-zinc-800 rounded-xl">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Company Email</span>
              <p class="text-xs font-black text-zinc-950 dark:text-white">{{ selectedCompany.company_email }}</p>
            </div>

            <div class="p-3.5 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/70 dark:border-zinc-800 rounded-xl">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Company Phone</span>
              <p class="text-xs font-black text-zinc-950 dark:text-white">{{ selectedCompany.company_phone || 'N/A' }}</p>
            </div>

            <div class="p-3.5 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/70 dark:border-zinc-800 rounded-xl">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Country</span>
              <p class="text-xs font-black text-zinc-950 dark:text-white">{{ selectedCompany.country || 'N/A' }}</p>
            </div>

            <div class="p-3.5 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/70 dark:border-zinc-800 rounded-xl">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Registration Number</span>
              <p class="text-xs font-black text-zinc-950 dark:text-white">{{ selectedCompany.registration_number || 'N/A' }}</p>
            </div>

            <div class="p-3.5 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/70 dark:border-zinc-800 rounded-xl">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Tax Number</span>
              <p class="text-xs font-black text-zinc-950 dark:text-white">{{ selectedCompany.tax_number || 'N/A' }}</p>
            </div>

            <div class="p-3.5 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/70 dark:border-zinc-800 rounded-xl">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Business Type</span>
              <p class="text-xs font-black text-zinc-950 dark:text-white">{{ selectedCompany.business_type || 'N/A' }}</p>
            </div>

            <div class="p-3.5 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/70 dark:border-zinc-800 rounded-xl">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Business Scale</span>
              <p class="text-xs font-black text-zinc-950 dark:text-white">{{ selectedCompany.business_scale || 'N/A' }}</p>
            </div>

            <div class="p-3.5 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/70 dark:border-zinc-800 rounded-xl">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Owner Role</span>
              <p class="text-xs font-black text-zinc-950 dark:text-white">{{ selectedCompany.owner_role || 'N/A' }}</p>
            </div>

            <div class="p-3.5 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/70 dark:border-zinc-800 rounded-xl">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Team Size</span>
              <p class="text-xs font-black text-zinc-950 dark:text-white">{{ selectedCompany.team_size || 'N/A' }}</p>
            </div>

            <div class="p-3.5 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/70 dark:border-zinc-800 rounded-xl">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Base Currency</span>
              <p class="text-xs font-black text-zinc-950 dark:text-white">{{ selectedCompany.base_currency || 'USD' }}</p>
            </div>

            <div class="p-3.5 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/70 dark:border-zinc-800 rounded-xl">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">System Language</span>
              <p class="text-xs font-black text-zinc-950 dark:text-white">{{ selectedCompany.system_language || 'EN' }}</p>
            </div>

            <div class="p-3.5 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/70 dark:border-zinc-800 rounded-xl md:col-span-2">
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Business Address</span>
              <p class="text-xs font-bold text-zinc-900 dark:text-white">{{ selectedCompany.business_address || 'N/A' }}</p>
            </div>
          </div>

          <!-- Intended Tasks / Usage Tags -->
          <div v-if="selectedCompany.intended_tasks && selectedCompany.intended_tasks.length" class="p-4 bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/70 dark:border-zinc-800 rounded-xl">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-2">Intended Modules & Tasks</span>
            <div class="flex flex-wrap gap-2">
              <span 
                v-for="(task, tIdx) in selectedCompany.intended_tasks" 
                :key="tIdx"
                class="px-2.5 py-1 text-xs font-extrabold rounded-lg bg-black text-white dark:bg-white dark:text-black shadow-xs"
              >
                {{ task }}
              </span>
            </div>
          </div>
        </div>

        <!-- Company Modal Footer -->
        <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 flex justify-end shrink-0">
          <button @click="selectedCompany = null" class="px-5 py-2 bg-black text-white dark:bg-white dark:text-black font-extrabold text-xs rounded-xl transition-all cursor-pointer">
            Close Company Details
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import axios from 'axios';
import DataTable from '../../components/DataTable.vue';

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

const emit = defineEmits(['close']);

const activeTab = ref('basic');
const loadingUser = ref(false);
const userDetail = ref(null);
const selectedCompany = ref(null);
const companiesDataTable = ref(null);

const companyColumns = [
  { key: 'id', label: 'ID' },
  { key: 'company_name', label: 'Company Details' },
  { key: 'company_email', label: 'Email' },
  { key: 'company_phone', label: 'Phone' },
  { key: 'business_type', label: 'Type' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: 'Actions' }
];

const primaryCompany = computed(() => {
  if (!userDetail.value) return null;
  if (userDetail.value.current_company) return userDetail.value.current_company;
  if (userDetail.value.all_companies && userDetail.value.all_companies.length > 0) {
    return userDetail.value.all_companies[0];
  }
  return null;
});

const fetchUserProfile = async () => {
  if (!props.userId) return;
  loadingUser.value = true;
  try {
    const response = await axios.get(`/admin/api/users/${props.userId}`);
    userDetail.value = response.data.data;
  } catch (e) {
    console.error("Failed to load user detail", e);
  } finally {
    loadingUser.value = false;
  }
};

const switchTab = (tab) => {
  activeTab.value = tab;
  if (tab === 'business') {
    nextTick(() => {
      companiesDataTable.value?.fetchData();
    });
  }
};

const fetchAndShowCompany = async (companyId) => {
  try {
    const response = await axios.get(`/admin/api/companies/${companyId}`);
    selectedCompany.value = response.data.data;
  } catch (e) {
    console.error("Failed to fetch company info", e);
    alert(e.response?.data?.message || 'Failed to load company details');
  }
};

const close = () => {
  selectedCompany.value = null;
  activeTab.value = 'basic';
  emit('close');
};

watch(() => props.show, (newVal) => {
  if (newVal && props.userId) {
    activeTab.value = 'basic';
    selectedCompany.value = null;
    fetchUserProfile();
  }
});

watch(() => props.userId, (newVal) => {
  if (props.show && newVal) {
    fetchUserProfile();
  }
});
</script>
