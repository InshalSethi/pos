<template>
  <div class="space-y-4 max-w-7xl mx-auto font-sans">
    <!-- Top Header Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 rounded-xl shadow-xs">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white font-sans">Account Profile</h1>
          <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5 font-sans">Manage your full employee profile details, credentials, and documents</p>
        </div>
      </div>
    </div>

    <!-- Main Profile Card Container -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs p-4 sm:p-5 space-y-5 font-sans">
      
      <!-- Profile Header / Avatar Hero Section -->
      <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 pb-4 border-b border-slate-100 dark:border-zinc-800">
        <div class="relative shrink-0">
          <div class="h-20 w-20 rounded-full overflow-hidden bg-slate-100 dark:bg-zinc-800 border-2 border-slate-200 dark:border-zinc-700 shadow-sm flex items-center justify-center">
            <img 
              v-if="photoPreview || profileImage" 
              :src="photoPreview || getProfileImageUrl(profileImage)" 
              alt="Profile Avatar" 
              class="h-full w-full object-cover"
            />
            <div 
              v-else 
              class="h-full w-full bg-slate-900 dark:bg-zinc-100 flex items-center justify-center"
            >
              <span class="text-white dark:text-zinc-900 font-bold text-xl font-sans">
                {{ (profileForm.first_name || authStore.user?.name || 'U').charAt(0).toUpperCase() }}
              </span>
            </div>
          </div>

          <!-- Camera Upload Button -->
          <button
            type="button"
            @click="triggerFileInput"
            class="absolute bottom-0 right-0 bg-slate-900 hover:bg-black text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white rounded-full p-1.5 shadow-md hover:scale-105 transition-all cursor-pointer"
            title="Update Profile Picture"
          >
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </button>

          <input
            ref="fileInput"
            type="file"
            accept="image/*"
            @change="handleFileSelect"
            class="hidden"
          />
        </div>

        <div class="text-center sm:text-left space-y-1">
          <div class="flex items-center gap-2 justify-center sm:justify-start flex-wrap">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white font-sans">
              {{ fullDisplayName }}
            </h2>
            <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-sans">
              {{ profileForm.employment_status || 'Account Active' }}
            </span>
          </div>
          <p class="text-xs font-sans font-medium text-slate-500 dark:text-slate-400">
            {{ profileForm.email }} {{ employeeCode ? '• #' + employeeCode : '' }}
          </p>
          <p class="text-xs font-sans font-semibold text-slate-400 dark:text-zinc-500 pt-0.5">
            Member since {{ formatDate(authStore.user?.created_at) || 'N/A' }}
          </p>
        </div>
      </div>

      <!-- Tabbed Navigation Bar (Matches Employee Creation Modal Tabs) -->
      <div class="flex border-b border-slate-200 dark:border-zinc-800 gap-1 text-[11px] shrink-0 bg-slate-50/50 dark:bg-zinc-900/40 p-1 rounded-xl overflow-x-auto custom-scrollbar">
        <button
          type="button"
          :class="['px-4 py-2 font-bold rounded-lg transition-all focus:outline-none cursor-pointer', activeTab === 'basic' ? 'text-indigo-600 dark:text-indigo-400 bg-white dark:bg-zinc-800 shadow-xs' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300']"
          @click="activeTab = 'basic'"
        >
          Basic Info
        </button>
        <button
          type="button"
          :class="['px-4 py-2 font-bold rounded-lg transition-all focus:outline-none cursor-pointer', activeTab === 'contact' ? 'text-indigo-600 dark:text-indigo-400 bg-white dark:bg-zinc-800 shadow-xs' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300']"
          @click="activeTab = 'contact'"
        >
          Contact
        </button>
        <button
          type="button"
          :class="['px-4 py-2 font-bold rounded-lg transition-all focus:outline-none cursor-pointer', activeTab === 'employment' ? 'text-indigo-600 dark:text-indigo-400 bg-white dark:bg-zinc-800 shadow-xs' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300']"
          @click="activeTab = 'employment'"
        >
          Employment
        </button>
        <button
          type="button"
          :class="['px-4 py-2 font-bold rounded-lg transition-all focus:outline-none cursor-pointer', activeTab === 'salary' ? 'text-indigo-600 dark:text-indigo-400 bg-white dark:bg-zinc-800 shadow-xs' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300']"
          @click="activeTab = 'salary'"
        >
          Salary & System
        </button>
        <button
          type="button"
          :class="['px-4 py-2 font-bold rounded-lg transition-all focus:outline-none cursor-pointer', activeTab === 'media' ? 'text-indigo-600 dark:text-indigo-400 bg-white dark:bg-zinc-800 shadow-xs' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300']"
          @click="activeTab = 'media'"
        >
          Media
        </button>
      </div>

      <!-- Profile Form -->
      <form @submit.prevent="updateProfile" class="space-y-6">

        <!-- TAB 1: BASIC INFORMATION -->
        <div v-if="activeTab === 'basic'" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label for="first_name" class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">First Name *</label>
              <input
                id="first_name"
                v-model="profileForm.first_name"
                type="text"
                required
                placeholder="Enter first name"
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
              />
            </div>
            <div>
              <label for="middle_name" class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Middle Name</label>
              <input
                id="middle_name"
                v-model="profileForm.middle_name"
                type="text"
                placeholder="Enter middle name"
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
              />
            </div>
            <div>
              <label for="last_name" class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Last Name *</label>
              <input
                id="last_name"
                v-model="profileForm.last_name"
                type="text"
                required
                placeholder="Enter last name"
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label for="date_of_birth" class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Date of Birth</label>
              <input
                id="date_of_birth"
                v-model="profileForm.date_of_birth"
                type="date"
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
              />
            </div>
            <div>
              <FloatingSelect
                v-model="profileForm.gender"
                label="Gender"
                placeholder="Select Gender"
                :options="genderOptions"
              />
            </div>
            <div>
              <FloatingSelect
                v-model="profileForm.marital_status"
                label="Marital Status"
                placeholder="Select Status"
                :options="maritalStatusOptions"
              />
            </div>
          </div>
        </div>

        <!-- TAB 2: CONTACT INFORMATION -->
        <div v-if="activeTab === 'contact'" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label for="email" class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Email Address *</label>
              <input
                id="email"
                v-model="profileForm.email"
                type="email"
                required
                placeholder="e.g. employee@example.com"
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
              />
            </div>
            <div>
              <CustomPhoneInput
                label="Mobile Number"
                v-model="profileForm.mobile"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <CustomPhoneInput
                label="Phone Number"
                v-model="profileForm.phone"
              />
            </div>
            <div>
              <CustomPhoneInput
                label="Fax Number"
                v-model="profileForm.fax"
              />
            </div>
          </div>

          <!-- Address Details Section -->
          <div class="pt-2 border-t border-slate-100 dark:border-zinc-800 space-y-3">
            <h4 class="text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">Address Details</h4>
            <div>
              <label for="address" class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Street Address</label>
              <input
                id="address"
                v-model="profileForm.address"
                type="text"
                placeholder="Street address..."
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
              />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="city" class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">City</label>
                <input
                  id="city"
                  v-model="profileForm.city"
                  type="text"
                  placeholder="City"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                />
              </div>
              <div>
                <label for="state" class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">State / Province</label>
                <input
                  id="state"
                  v-model="profileForm.state"
                  type="text"
                  placeholder="State"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                />
              </div>
            </div>
          </div>

          <!-- Emergency Contact Section -->
          <div class="pt-2 border-t border-slate-100 dark:border-zinc-800 space-y-3">
            <h4 class="text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">Emergency Contact</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="emergency_contact_name" class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Contact Person Name</label>
                <input
                  id="emergency_contact_name"
                  v-model="profileForm.emergency_contact_name"
                  type="text"
                  placeholder="e.g. Jane Doe"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                />
              </div>
              <div>
                <label for="emergency_contact_relationship" class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Relationship</label>
                <input
                  id="emergency_contact_relationship"
                  v-model="profileForm.emergency_contact_relationship"
                  type="text"
                  placeholder="e.g. Spouse / Parent"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                />
              </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <CustomPhoneInput
                  label="Emergency Phone"
                  v-model="profileForm.emergency_contact_phone"
                />
              </div>
              <div>
                <label for="emergency_contact_email" class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Emergency Email</label>
                <input
                  id="emergency_contact_email"
                  v-model="profileForm.emergency_contact_email"
                  type="email"
                  placeholder="e.g. emergency@example.com"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 3: EMPLOYMENT INFORMATION -->
        <div v-if="activeTab === 'employment'" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <FloatingSelect
                v-model="profileForm.company_id"
                label="Assign Company"
                placeholder="Select Company"
                :options="companySelectOptions"
                :disabled="!isAdmin"
              />
            </div>

            <div>
              <label for="hire_date" class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Hire Date</label>
              <input
                id="hire_date"
                v-model="profileForm.hire_date"
                type="date"
                :disabled="!isAdmin"
                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all disabled:opacity-60 disabled:cursor-not-allowed"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <FloatingSelect
                v-model="profileForm.department_id"
                label="Department"
                placeholder="Select Department"
                :options="departmentSelectOptions"
                :disabled="!isAdmin"
              />
            </div>

            <div>
              <FloatingSelect
                v-model="profileForm.position_id"
                label="Position"
                placeholder="Select Position"
                :options="positionSelectOptions"
                :disabled="!isAdmin"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <FloatingSelect
                v-model="profileForm.employment_type"
                label="Employment Type"
                placeholder="Select Type"
                :options="employmentTypeOptions"
                :disabled="!isAdmin"
              />
            </div>

            <div>
              <FloatingSelect
                v-model="profileForm.employment_status"
                label="Employment Status"
                placeholder="Select Status"
                :options="employmentStatusOptions"
                :disabled="!isAdmin"
              />
            </div>
          </div>
        </div>

        <!-- TAB 4: SALARY & SYSTEM LOGIN INFORMATION -->
        <div v-if="activeTab === 'salary'" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">
                Basic Salary ({{ currencySymbol }})
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400 dark:text-zinc-500 pointer-events-none">
                  {{ currencySymbol }}
                </span>
                <input
                  v-model="profileForm.basic_salary"
                  type="number"
                  step="0.01"
                  min="0"
                  :disabled="!isAdmin"
                  placeholder="0.00"
                  class="w-full pl-8 pr-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all disabled:opacity-60 disabled:cursor-not-allowed"
                />
              </div>
            </div>

            <div>
              <FloatingSelect
                v-model="profileForm.salary_type"
                label="Salary Type"
                placeholder="Select Type"
                :options="salaryTypeOptions"
                :disabled="!isAdmin"
              />
            </div>
          </div>

          <div v-if="profileForm.salary_type === 'hourly'">
            <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">
              Hourly Rate ({{ currencySymbol }})
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400 dark:text-zinc-500 pointer-events-none">
                {{ currencySymbol }}
              </span>
              <input
                v-model="profileForm.hourly_rate"
                type="number"
                step="0.01"
                min="0"
                :disabled="!isAdmin"
                placeholder="0.00"
                class="w-full pl-8 pr-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all disabled:opacity-60 disabled:cursor-not-allowed"
              />
            </div>
          </div>

          <!-- Bank Details -->
          <div class="pt-2 border-t border-slate-100 dark:border-zinc-800 space-y-3">
            <h4 class="text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">Bank Details</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label for="bank_name" class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Bank Name</label>
                <input
                  id="bank_name"
                  v-model="profileForm.bank_name"
                  type="text"
                  placeholder="e.g. Chase / HBL"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                />
              </div>
              <div>
                <label for="bank_account_number" class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Account Number / IBAN</label>
                <input
                  id="bank_account_number"
                  v-model="profileForm.bank_account_number"
                  type="text"
                  placeholder="Account Number"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                />
              </div>
              <div>
                <label for="bank_branch" class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Branch Code / Name</label>
                <input
                  id="bank_branch"
                  v-model="profileForm.bank_branch"
                  type="text"
                  placeholder="Branch"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                />
              </div>
            </div>
          </div>

          <!-- Password & Security Change Section -->
          <div class="pt-2 border-t border-slate-100 dark:border-zinc-800 space-y-3">
            <h4 class="text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">Security & Password Change</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label for="current_password" class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Current Password</label>
                <input
                  id="current_password"
                  v-model="profileForm.current_password"
                  type="password"
                  placeholder="••••••••"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                />
              </div>
              <div>
                <label for="new_password" class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">New Password</label>
                <input
                  id="new_password"
                  v-model="profileForm.new_password"
                  type="password"
                  placeholder="••••••••"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                />
              </div>
              <div>
                <label for="new_password_confirmation" class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Confirm New Password</label>
                <input
                  id="new_password_confirmation"
                  v-model="profileForm.new_password_confirmation"
                  type="password"
                  placeholder="••••••••"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 5: MEDIA INFORMATION -->
        <div v-if="activeTab === 'media'" class="space-y-6">
          <!-- Profile Photo Section -->
          <div>
            <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Profile Photo</label>
            <div 
              @dragover.prevent="isDragging = true" 
              @dragleave.prevent="isDragging = false" 
              @drop.prevent="handleDrop"
              :class="[
                'relative flex items-center gap-4 p-4 rounded-xl border-2 border-dashed transition-all duration-200',
                isDragging ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20' : 'border-slate-200 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-950/40'
              ]"
            >
              <div class="flex-shrink-0 relative">
                <div class="w-14 h-14 rounded-xl overflow-hidden bg-slate-100 dark:bg-zinc-800 flex items-center justify-center border border-slate-200 dark:border-zinc-700 shadow-xs">
                  <img v-if="photoPreview || profileImage" :src="photoPreview || getProfileImageUrl(profileImage)" alt="Profile preview" class="w-full h-full object-cover" />
                  <svg v-else class="w-7 h-7 text-slate-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
              </div>

              <div class="flex-1 min-w-0">
                <p class="text-xs font-bold text-slate-800 dark:text-zinc-200">Drag & drop profile picture here</p>
                <p class="text-[10px] text-slate-500 dark:text-zinc-400 mt-0.5">JPEG, PNG, GIF up to 10MB</p>
                <div class="flex items-center gap-2 mt-2">
                  <label class="px-2.5 py-1 bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-semibold text-[10px] rounded-lg cursor-pointer hover:opacity-90 transition-opacity">
                    Browse Image
                    <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handleFileSelect" />
                  </label>
                  <button v-if="photoPreview || profileImage" type="button" @click="clearPhoto" class="px-2 py-1 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 font-semibold text-[10px] rounded-lg hover:bg-rose-100 transition-colors cursor-pointer">
                    Remove
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Attachments Section -->
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">
                Attachments <span class="text-slate-400 font-normal lowercase">(images or documents, max 5MB each, max 5 files)</span>
              </label>
              <span v-if="existingAttachments.length > 0 || attachmentFiles.length > 0" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 px-2 py-0.5 rounded-md border border-indigo-100 dark:border-indigo-900/50">
                {{ existingAttachments.length + attachmentFiles.length }} / 5 file(s) selected
              </span>
            </div>

            <div
              @dragover.prevent="isDraggingAttachment = true"
              @dragleave.prevent="isDraggingAttachment = false"
              @drop.prevent="handleAttachmentDrop"
              @click="triggerAttachmentInput"
              :class="[
                'relative border-2 border-dashed rounded-xl p-4 transition-all duration-200 cursor-pointer text-center group flex flex-col items-center justify-center gap-1.5',
                isDraggingAttachment
                  ? 'border-indigo-500 bg-indigo-50/70 dark:bg-indigo-950/30 scale-[1.01]'
                  : 'border-slate-200 dark:border-zinc-800 bg-slate-50/50 hover:bg-slate-100/70 dark:bg-zinc-950/40 dark:hover:bg-zinc-900/80 hover:border-indigo-300 dark:hover:border-indigo-700'
              ]"
            >
              <input
                ref="attachmentInputRef"
                type="file"
                accept=".png,.jpg,.jpeg,.webp,.pdf,image/png,image/jpeg,image/webp,application/pdf"
                multiple
                @change="handleAttachmentChange"
                class="hidden"
              />

              <div class="w-8 h-8 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200/80 dark:border-zinc-700 shadow-xs flex items-center justify-center group-hover:scale-105 transition-transform text-indigo-600 dark:text-indigo-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
              </div>

              <div class="space-y-0.5">
                <p class="text-xs font-semibold text-slate-700 dark:text-zinc-200">
                  <span class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Click to upload</span> or drag and drop
                </p>
                <p class="text-[10px] font-medium text-slate-400 dark:text-zinc-500">
                  PNG, JPG, WEBP, PDF (max 5MB each, max 5 files)
                </p>
              </div>
            </div>

            <div v-if="existingAttachments.length > 0 || attachmentFiles.length > 0" class="flex flex-wrap gap-2 pt-2.5">
              <!-- Existing Attachments -->
              <div
                v-for="(item, index) in existingAttachments"
                :key="'exist-' + index"
                class="flex items-center gap-2 bg-indigo-50/80 dark:bg-zinc-800 px-3 py-1.5 rounded-xl border border-indigo-100 dark:border-zinc-700 text-xs shadow-2xs"
              >
                <a
                  :href="item.url"
                  target="_blank"
                  class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline flex items-center gap-1 min-w-0"
                  title="View File"
                >
                  <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                  </svg>
                  <span class="truncate max-w-[140px]">{{ item.filename }}</span>
                </a>
                <button type="button" @click.stop="removeExistingAttachment(index)" class="text-slate-400 hover:text-rose-500 p-0.5 rounded-md transition-all cursor-pointer" title="Remove File">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <!-- New Attachments -->
              <div
                v-for="(file, index) in attachmentFiles"
                :key="'new-' + index"
                class="flex items-center gap-2 bg-slate-100/90 dark:bg-zinc-800 px-3 py-1.5 rounded-xl border border-slate-200/80 dark:border-zinc-700 text-xs shadow-2xs"
              >
                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="truncate font-semibold text-slate-800 dark:text-slate-200 max-w-[150px]">{{ file.name }}</span>
                <span class="text-[10px] text-slate-400 font-medium">({{ (file.size / 1024 / 1024).toFixed(2) }} MB)</span>
                <button type="button" @click.stop="removeAttachmentFile(index)" class="text-slate-400 hover:text-rose-500 p-0.5 rounded-md transition-all cursor-pointer">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Submit Button Footer -->
        <div class="flex items-center justify-end pt-4 border-t border-slate-100 dark:border-zinc-800">
          <button
            type="submit"
            :disabled="submitting"
            class="bg-slate-900 hover:bg-black active:scale-[0.98] text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white font-bold rounded-xl text-xs px-6 py-2.5 transition-all shadow-xs inline-flex items-center gap-2 cursor-pointer disabled:opacity-50 font-sans"
          >
            <div v-if="submitting" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-current"></div>
            <span>{{ submitting ? 'Updating Profile...' : 'Update Profile' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useToast } from '@/composables/useToast';
import FloatingSelect from '@/components/common/FloatingSelect.vue';
import CustomPhoneInput from '@/components/common/CustomPhoneInput.vue';
import axios from 'axios';

const authStore = useAuthStore();
const { showToast } = useToast();

const activeTab = ref('basic');
const submitting = ref(false);
const currencySymbol = ref('$');

// File upload state
const fileInput = ref(null);
const attachmentInputRef = ref(null);
const selectedFile = ref(null);
const photoPreview = ref(null);
const profileImage = ref(null);
const isDragging = ref(false);
const isDraggingAttachment = ref(false);
const attachmentFiles = ref([]);
const existingAttachments = ref([]);

// Lookup Data Arrays
const companies = ref([]);
const departments = ref([]);
const positions = ref([]);

// Form State
const profileForm = ref({
  first_name: '',
  middle_name: '',
  last_name: '',
  date_of_birth: '',
  gender: '',
  marital_status: '',
  email: '',
  mobile: '',
  phone: '',
  fax: '',
  address: '',
  city: '',
  state: '',
  postal_code: '',
  country: '',
  emergency_contact_name: '',
  emergency_contact_relationship: '',
  emergency_contact_phone: '',
  emergency_contact_email: '',
  company_id: '',
  hire_date: '',
  department_id: '',
  position_id: '',
  employment_type: 'full_time',
  employment_status: 'active',
  basic_salary: '',
  salary_type: 'monthly',
  hourly_rate: '',
  bank_account_number: '',
  bank_name: '',
  bank_branch: '',
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
});

// Dropdown Options
const genderOptions = [
  { value: 'male', label: 'Male' },
  { value: 'female', label: 'Female' },
  { value: 'other', label: 'Other' }
];

const maritalStatusOptions = [
  { value: 'single', label: 'Single' },
  { value: 'married', label: 'Married' },
  { value: 'divorced', label: 'Divorced' },
  { value: 'widowed', label: 'Widowed' }
];

const employmentTypeOptions = [
  { value: 'full_time', label: 'Full Time' },
  { value: 'part_time', label: 'Part Time' },
  { value: 'contract', label: 'Contract' },
  { value: 'intern', label: 'Intern' }
];

const employmentStatusOptions = [
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
  { value: 'on_leave', label: 'On Leave' },
  { value: 'terminated', label: 'Terminated' }
];

const salaryTypeOptions = [
  { value: 'monthly', label: 'Monthly' },
  { value: 'hourly', label: 'Hourly' },
  { value: 'daily', label: 'Daily' }
];

// Date formatting helper for HTML <input type="date">
const formatInputDate = (dateVal) => {
  if (!dateVal) return '';
  if (typeof dateVal === 'string') {
    const match = dateVal.match(/^\d{4}-\d{2}-\d{2}/);
    if (match) return match[0];
  }
  try {
    const d = new Date(dateVal);
    if (!isNaN(d.getTime())) {
      return d.toISOString().split('T')[0];
    }
  } catch (e) {}
  return '';
};

// Computed Select Options
const companySelectOptions = computed(() => {
  const opts = [{ value: '', label: 'Select Company' }];
  const list = Array.isArray(companies.value) ? companies.value : [];
  
  const emp = authStore.user?.employee;
  const companyId = emp?.company_id || authStore.user?.current_company_id;
  const companyObj = emp?.company || authStore.user?.currentCompany;
  const companyName = companyObj?.company_name || companyObj?.name || (companyId ? `Company #${companyId}` : '');

  if (companyId && !list.some(c => c.id == companyId)) {
    opts.push({ value: companyId, label: companyName });
  }

  list.forEach(c => opts.push({ value: c.id, label: c.company_name || c.name || `Company #${c.id}` }));
  return opts;
});

const departmentSelectOptions = computed(() => {
  const opts = [{ value: '', label: 'Select Department' }];
  const empDept = authStore.user?.employee?.department;
  const list = Array.isArray(departments.value) ? departments.value : [];
  if (empDept && !list.some(d => d.id == empDept.id)) {
    opts.push({ value: empDept.id, label: empDept.name });
  }
  list.forEach(d => opts.push({ value: d.id, label: d.name }));
  return opts;
});

const positionSelectOptions = computed(() => {
  const opts = [{ value: '', label: 'Select Position' }];
  const empPos = authStore.user?.employee?.position;
  const list = Array.isArray(positions.value) ? positions.value : [];
  if (empPos && !list.some(p => p.id == empPos.id)) {
    opts.push({ value: empPos.id, label: empPos.title });
  }
  list.forEach(p => opts.push({ value: p.id, label: p.title }));
  return opts;
});

const isAdmin = computed(() => {
  const roles = authStore.user?.roles || [];
  return roles.some(r => ['admin', 'owner', 'super-admin'].includes(typeof r === 'string' ? r.toLowerCase() : r.name?.toLowerCase()));
});

const fullDisplayName = computed(() => {
  const parts = [profileForm.value.first_name, profileForm.value.middle_name, profileForm.value.last_name].filter(Boolean);
  return parts.join(' ') || authStore.user?.name || 'User Profile';
});

const employeeCode = computed(() => authStore.user?.employee?.employee_number || '');

// Methods
const initializeForm = () => {
  const user = authStore.user;
  const emp = user?.employee;

  let fName = emp?.first_name || '';
  let mName = emp?.middle_name || '';
  let lName = emp?.last_name || '';

  if (!fName && user?.name) {
    const parts = user.name.trim().split(' ');
    fName = parts.shift() || '';
    lName = parts.length > 0 ? parts.pop() : '';
    mName = parts.join(' ');
  }

  profileForm.value.first_name = fName;
  profileForm.value.middle_name = mName;
  profileForm.value.last_name = lName;
  profileForm.value.date_of_birth = formatInputDate(emp?.date_of_birth);
  profileForm.value.gender = emp?.gender || '';
  profileForm.value.marital_status = emp?.marital_status || '';

  profileForm.value.email = user?.email || emp?.email || '';
  profileForm.value.mobile = emp?.mobile || user?.phone || '';
  profileForm.value.phone = emp?.phone || user?.phone || '';
  profileForm.value.fax = emp?.fax || '';
  profileForm.value.address = emp?.address || user?.address || '';
  profileForm.value.city = emp?.city || '';
  profileForm.value.state = emp?.state || '';
  profileForm.value.postal_code = emp?.postal_code || '';
  profileForm.value.country = emp?.country || '';

  profileForm.value.emergency_contact_name = emp?.emergency_contact_name || '';
  profileForm.value.emergency_contact_relationship = emp?.emergency_contact_relationship || '';
  profileForm.value.emergency_contact_phone = emp?.emergency_contact_phone || '';
  profileForm.value.emergency_contact_email = emp?.emergency_contact_email || '';

  profileForm.value.company_id = emp?.company_id || user?.current_company_id || '';
  profileForm.value.hire_date = formatInputDate(emp?.hire_date);
  profileForm.value.department_id = emp?.department_id || '';
  profileForm.value.position_id = emp?.position_id || '';
  profileForm.value.employment_type = emp?.employment_type || 'full_time';
  profileForm.value.department_id = emp?.department_id || '';
  profileForm.value.position_id = emp?.position_id || '';
  profileForm.value.employment_type = emp?.employment_type || 'full_time';
  profileForm.value.employment_status = emp?.employment_status || emp?.status || 'active';

  profileForm.value.basic_salary = emp?.basic_salary || '';
  profileForm.value.salary_type = emp?.salary_type || 'monthly';
  profileForm.value.hourly_rate = emp?.hourly_rate || '';
  profileForm.value.bank_account_number = emp?.bank_account_number || '';
  profileForm.value.bank_name = emp?.bank_name || '';
  profileForm.value.bank_branch = emp?.bank_branch || '';

  profileImage.value = user?.profile_image || emp?.profile_image || null;
  existingAttachments.value = Array.isArray(emp?.attachments) ? emp.attachments : [];
};

const getProfileImageUrl = (imagePath) => {
  if (!imagePath) return null;
  if (imagePath.startsWith('http')) return imagePath;
  return `/storage/${imagePath}`;
};

const extractArrayData = (res) => {
  if (!res || !res.data) return [];
  if (Array.isArray(res.data)) return res.data;
  if (Array.isArray(res.data.data)) return res.data.data;
  return [];
};

const fetchDropdownData = async () => {
  try {
    const [compRes, deptRes, posRes] = await Promise.allSettled([
      axios.get('/api/companies'),
      axios.get('/api/departments'),
      axios.get('/api/positions')
    ]);
    if (compRes.status === 'fulfilled') companies.value = extractArrayData(compRes.value);
    if (deptRes.status === 'fulfilled') departments.value = extractArrayData(deptRes.value);
    if (posRes.status === 'fulfilled') positions.value = extractArrayData(posRes.value);
  } catch (err) {
    console.error('Error fetching dropdown data:', err);
    companies.value = [];
    departments.value = [];
    positions.value = [];
  }
};

const triggerFileInput = () => fileInput.value?.click();
const triggerAttachmentInput = () => attachmentInputRef.value?.click();

const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (!file) return;
  if (!file.type.startsWith('image/')) {
    showToast('Please select a valid image file', 'error');
    return;
  }
  selectedFile.value = file;
  photoPreview.value = URL.createObjectURL(file);
};

const handleDrop = (event) => {
  isDragging.value = false;
  const file = event.dataTransfer.files[0];
  if (!file) return;
  if (!file.type.startsWith('image/')) {
    showToast('Please select a valid image file', 'error');
    return;
  }
  selectedFile.value = file;
  photoPreview.value = URL.createObjectURL(file);
};

const clearPhoto = () => {
  selectedFile.value = null;
  photoPreview.value = null;
  profileImage.value = null;
  if (fileInput.value) fileInput.value.value = '';
};

const handleAttachmentChange = (event) => {
  const files = Array.from(event.target.files);
  addAttachmentFiles(files);
};

const handleAttachmentDrop = (event) => {
  isDraggingAttachment.value = false;
  const files = Array.from(event.dataTransfer.files);
  addAttachmentFiles(files);
};

const addAttachmentFiles = (files) => {
  const currentTotal = existingAttachments.value.length + attachmentFiles.value.length;
  if (currentTotal + files.length > 5) {
    showToast('Maximum 5 attachment files allowed', 'error');
    return;
  }
  files.forEach(file => {
    if (file.size > 5 * 1024 * 1024) {
      showToast(`File ${file.name} exceeds 5MB size limit`, 'error');
      return;
    }
    attachmentFiles.value.push(file);
  });
};

const removeExistingAttachment = (index) => {
  existingAttachments.value.splice(index, 1);
};

const removeAttachmentFile = (index) => {
  attachmentFiles.value.splice(index, 1);
};

const updateProfile = async () => {
  submitting.value = true;

  try {
    const formData = new FormData();
    
    // Append all form fields
    Object.keys(profileForm.value).forEach(key => {
      const val = profileForm.value[key];
      if (val !== null && val !== undefined) {
        formData.append(key, val);
      }
    });

    // Append profile photo if selected
    if (selectedFile.value) {
      formData.append('profile_image', selectedFile.value);
    }

    // Append new attachments
    attachmentFiles.value.forEach(file => {
      formData.append('attachments[]', file);
    });

    // Append existing attachments array as JSON
    formData.append('existing_attachments', JSON.stringify(existingAttachments.value));

    // Submit request via POST (with _method PUT for Laravel multipart handling)
    formData.append('_method', 'PUT');

    await axios.post('/api/user/profile', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    // Refresh reactive auth user state
    await authStore.fetchUser();
    initializeForm();
    attachmentFiles.value = [];

    showToast('Profile updated successfully!', 'success');
  } catch (error) {
    if (error.response?.data?.errors) {
      const msgs = Object.values(error.response.data.errors).flat();
      msgs.forEach(msg => showToast(msg, 'error'));
    } else {
      showToast(error.response?.data?.message || 'Failed to update profile', 'error');
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
  fetchDropdownData();
});
</script>
