<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200" style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);">
      <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-3xl shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 text-left transition-all duration-300 flex flex-col max-h-[90vh] overflow-y-auto my-auto z-10" @click.stop>
        
        <!-- Header -->
        <div class="p-6 pb-4 border-b border-slate-100 dark:border-zinc-800 shrink-0 relative">
          <!-- Sleek Close Icon Button -->
          <button
            type="button"
            @click="$emit('close')"
            class="absolute top-5 right-5 text-slate-400 dark:text-zinc-500 hover:text-slate-600 dark:hover:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 p-1.5 rounded-lg transition-all cursor-pointer"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <h3 class="text-xs font-bold text-slate-800 dark:text-zinc-100 uppercase tracking-wider">
            {{ isManagerMode ? (isEditing ? 'Edit Manager' : 'Add New Manager') : (isEditing ? 'Edit Employee' : 'Add New Employee') }}
          </h3>
        </div>

        <!-- Tab Navigation (Clean text tabs, matching Customer & Supplier Modals) -->
        <div class="flex border-b border-slate-200 dark:border-zinc-800 px-6 pt-3 gap-1 text-[11px] shrink-0 bg-slate-50/50 dark:bg-zinc-900/40">
          <button
            type="button"
            :class="['px-4 py-2 font-bold rounded-t-lg transition-all focus:outline-none border-b-2 cursor-pointer', activeTab === 'basic' ? 'text-indigo-600 dark:text-indigo-400 border-indigo-600 bg-white dark:bg-zinc-900' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300 border-transparent']"
            @click="activeTab = 'basic'"
          >
            Basic Info
          </button>
          <button
            type="button"
            :class="['px-4 py-2 font-bold rounded-t-lg transition-all focus:outline-none border-b-2 cursor-pointer', activeTab === 'employment' ? 'text-indigo-600 dark:text-indigo-400 border-indigo-600 bg-white dark:bg-zinc-900' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300 border-transparent']"
            @click="activeTab = 'employment'"
          >
            Employment
          </button>
          <button
            type="button"
            :class="['px-4 py-2 font-bold rounded-t-lg transition-all focus:outline-none border-b-2 cursor-pointer', activeTab === 'salary' ? 'text-indigo-600 dark:text-indigo-400 border-indigo-600 bg-white dark:bg-zinc-900' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300 border-transparent']"
            @click="activeTab = 'salary'"
          >
            Salary & System
          </button>
          <button
            type="button"
            :class="['px-4 py-2 font-bold rounded-t-lg transition-all focus:outline-none border-b-2 cursor-pointer', activeTab === 'media' ? 'text-indigo-600 dark:text-indigo-400 border-indigo-600 bg-white dark:bg-zinc-900' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300 border-transparent']"
            @click="activeTab = 'media'"
          >
            Media
          </button>
        </div>

        <!-- Form Area -->
        <form novalidate @submit.prevent="saveEmployee" class="flex flex-col flex-1 min-h-0">
          <div class="flex-1 overflow-y-auto p-6 space-y-4 pr-4 custom-scrollbar">
            
            <!-- Tab 1: Basic Information -->
            <div v-if="activeTab === 'basic'" class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">First Name *</label>
                  <input
                    v-model="form.first_name"
                    type="text"
                    required
                    placeholder="Enter first name"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.first_name }"
                  />
                  <p v-if="errors.first_name" class="mt-1 text-[10px] text-red-500">{{ errors.first_name[0] }}</p>
                </div>
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Middle Name</label>
                  <input
                    v-model="form.middle_name"
                    type="text"
                    placeholder="Enter middle name"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.middle_name }"
                  />
                  <p v-if="errors.middle_name" class="mt-1 text-[10px] text-red-500">{{ errors.middle_name[0] }}</p>
                </div>
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Last Name *</label>
                  <input
                    v-model="form.last_name"
                    type="text"
                    required
                    placeholder="Enter last name"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.last_name }"
                  />
                  <p v-if="errors.last_name" class="mt-1 text-[10px] text-red-500">{{ errors.last_name[0] }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Email Address *</label>
                  <input
                    v-model="form.email"
                    type="email"
                    required
                    placeholder="e.g. employee@example.com"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.email }"
                  />
                  <p v-if="errors.email" class="mt-1 text-[10px] text-red-500">{{ errors.email[0] }}</p>
                </div>
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Date of Birth</label>
                  <input
                    v-model="form.date_of_birth"
                    type="date"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.date_of_birth }"
                  />
                  <p v-if="errors.date_of_birth" class="mt-1 text-[10px] text-red-500">{{ errors.date_of_birth[0] }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <CustomPhoneInput
                    label="Phone"
                    v-model="form.phone"
                    :error="errors.phone"
                  />
                </div>
                <div>
                  <CustomPhoneInput
                    label="Mobile"
                    v-model="form.mobile"
                    :error="errors.mobile"
                  />
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <FloatingSelect
                    v-model="form.gender"
                    label="Gender *"
                    placeholder="Select Gender"
                    :options="genderOptions"
                    required
                    :error="!!errors.gender"
                  />
                  <p v-if="errors.gender" class="mt-1 text-[10px] text-red-500">{{ errors.gender[0] }}</p>
                </div>
                <div>
                  <FloatingSelect
                    v-model="form.marital_status"
                    label="Marital Status"
                    placeholder="Select Status"
                    :options="maritalStatusOptions"
                    :error="!!errors.marital_status"
                  />
                  <p v-if="errors.marital_status" class="mt-1 text-[10px] text-red-500">{{ errors.marital_status[0] }}</p>
                </div>
              </div>
            </div>

            <!-- Tab 2: Employment Information -->
            <div v-if="activeTab === 'employment'" class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <FloatingSelect
                    v-model="form.company_id"
                    label="Assign Company *"
                    placeholder="Select Company"
                    :options="companySelectOptions"
                    required
                    :error="!!errors.company_id"
                  />
                  <p v-if="errors.company_id" class="mt-1 text-[10px] text-red-500">{{ errors.company_id[0] }}</p>
                </div>

                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Hire Date *</label>
                  <input
                    v-model="form.hire_date"
                    type="date"
                    required
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.hire_date }"
                  />
                  <p v-if="errors.hire_date" class="mt-1 text-[10px] text-red-500">{{ errors.hire_date[0] }}</p>
                </div>
              </div>

              <!-- Managed Departments (For Manager mode) or Department Select (For Employee mode) -->
              <div v-if="isManagerMode">
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">
                  Managed Departments (Multi-Select)
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 bg-white dark:bg-zinc-950 p-3 border border-slate-200 dark:border-zinc-700 rounded-lg max-h-44 overflow-y-auto custom-scrollbar">
                  <label 
                    v-for="dept in departments" 
                    :key="dept.id"
                    class="flex items-center gap-2 p-2 rounded-lg border border-slate-100 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-900 cursor-pointer transition-colors text-xs font-medium text-slate-800 dark:text-zinc-200"
                  >
                    <input 
                      type="checkbox" 
                      :value="String(dept.id)" 
                      v-model="form.department_ids"
                      class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4"
                    />
                    <span class="truncate">{{ dept.name }}</span>
                  </label>
                </div>
                <p class="text-[10px] text-slate-400 dark:text-zinc-500 mt-1">Select all departments overseen by this manager.</p>
              </div>
              <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <FloatingSelect
                    v-model="form.department_id"
                    label="Department"
                    placeholder="Select Department"
                    :options="departmentSelectOptions"
                    :error="!!errors.department_id"
                  />
                  <p v-if="errors.department_id" class="mt-1 text-[10px] text-red-500">{{ errors.department_id[0] }}</p>
                </div>

                <div>
                  <FloatingSelect
                    v-model="form.manager_id"
                    label="Manager"
                    placeholder="Select Manager"
                    :options="managerSelectOptions"
                    :error="!!errors.manager_id"
                  />
                  <div class="flex items-center justify-between mt-1">
                    <p v-if="errors.manager_id" class="text-[10px] text-red-500">{{ errors.manager_id[0] }}</p>
                    <button
                      type="button"
                      @click="emit('add-manager')"
                      class="text-[10px] font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 cursor-pointer transition-colors flex items-center gap-0.5 ml-auto"
                    >
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                      Add New Manager
                    </button>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <FloatingSelect
                    v-model="form.position_id"
                    :label="isManagerMode ? 'Managerial Position *' : 'Position'"
                    placeholder="Select Position"
                    :options="positionSelectOptions"
                    :error="!!errors.position_id"
                  />
                  <p v-if="errors.position_id" class="mt-1 text-[10px] text-red-500">{{ errors.position_id[0] }}</p>

                  <!-- Position Level Indicator -->
                  <div class="mt-2 flex items-center gap-1.5 px-0.5">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Position Level:</span>
                    <span 
                      v-if="selectedPositionDetails" 
                      :class="getPositionLevelBadgeClass(selectedPositionDetails.level)"
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider border shadow-xs"
                    >
                      {{ selectedPositionDetails.level || 'Standard' }}
                    </span>
                    <span v-else class="text-[10px] italic text-slate-400 dark:text-zinc-500">
                      Select a position
                    </span>
                  </div>
                </div>

                <div>
                  <FloatingSelect
                    v-model="form.employment_type"
                    label="Employment Type *"
                    placeholder="Select Type"
                    :options="employmentTypeOptions"
                    required
                    :error="!!errors.employment_type"
                  />
                  <p v-if="errors.employment_type" class="mt-1 text-[10px] text-red-500">{{ errors.employment_type[0] }}</p>
                </div>
              </div>

              <div v-if="isEditing">
                <FloatingSelect
                  v-model="form.employment_status"
                  label="Employment Status"
                  placeholder="Select Status"
                  :options="employmentStatusOptions"
                  :error="!!errors.employment_status"
                />
                <p v-if="errors.employment_status" class="mt-1 text-[10px] text-red-500">{{ errors.employment_status[0] }}</p>
              </div>
            </div>

            <!-- Tab 3: Salary & System Login Information -->
            <div v-if="activeTab === 'salary'" class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">
                    Basic Salary ({{ currencySymbol }}) *
                  </label>
                  <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400 dark:text-zinc-500 pointer-events-none">
                      {{ currencySymbol }}
                    </span>
                    <input
                      v-model="form.basic_salary"
                      type="number"
                      step="0.01"
                      min="0"
                      required
                      placeholder="0.00"
                      class="w-full pl-8 pr-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                      :class="{ 'border-red-300 dark:border-red-700': errors.basic_salary }"
                    />
                  </div>
                  <p v-if="errors.basic_salary" class="mt-1 text-[10px] text-red-500">{{ errors.basic_salary[0] }}</p>
                </div>

                <div>
                  <FloatingSelect
                    v-model="form.salary_type"
                    label="Salary Type *"
                    placeholder="Select Type"
                    :options="salaryTypeOptions"
                    required
                    :error="!!errors.salary_type"
                  />
                  <p v-if="errors.salary_type" class="mt-1 text-[10px] text-red-500">{{ errors.salary_type[0] }}</p>
                </div>
              </div>

              <div v-if="form.salary_type === 'hourly'">
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">
                  Hourly Rate ({{ currencySymbol }})
                </label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400 dark:text-zinc-500 pointer-events-none">
                    {{ currencySymbol }}
                  </span>
                  <input
                    v-model="form.hourly_rate"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    class="w-full pl-8 pr-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.hourly_rate }"
                  />
                </div>
                <p v-if="errors.hourly_rate" class="mt-1 text-[10px] text-red-500">{{ errors.hourly_rate[0] }}</p>
              </div>

              <!-- System Login Access & User Account Integration Section -->
              <div class="bg-indigo-50/40 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/30 p-4 rounded-xl space-y-3">
                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="text-xs font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider">System Login Access</h4>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Grant direct portal login access to this employee</p>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" v-model="form.create_user_account" class="sr-only peer" />
                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:peer-focus:ring-indigo-800 peer-checked:bg-indigo-600"></div>
                  </label>
                </div>

                <div v-if="form.create_user_account || (isEditing && employee?.user_id)" class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-3 border-t border-indigo-100 dark:border-indigo-900/30">
                  <div>
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">
                      {{ isEditing && employee?.user_id ? 'New Password' : 'Password *' }}
                    </label>
                    <div class="relative">
                      <input
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        autocomplete="new-password"
                        :required="!isEditing || !employee?.user_id"
                        placeholder="Password"
                        class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all pr-8"
                      />
                      <button 
                        type="button" 
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 cursor-pointer"
                        tabindex="-1"
                      >
                        <svg v-if="showPassword" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg v-else class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.076m1.406-1.407A10.014 10.014 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.059 10.059 0 01-4.293 5.774M6.228 6.228L17.772 17.772M9 9l6 6" />
                        </svg>
                      </button>
                    </div>
                    <p v-if="errors.password" class="mt-1 text-[10px] text-red-500">{{ errors.password[0] }}</p>
                  </div>

                  <div>
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Confirm Password</label>
                    <div class="relative">
                      <input
                        v-model="form.password_confirmation"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        autocomplete="new-password"
                        placeholder="Confirm password"
                        class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all pr-8"
                      />
                      <button 
                        type="button" 
                        @click="showConfirmPassword = !showConfirmPassword"
                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 cursor-pointer"
                        tabindex="-1"
                      >
                        <svg v-if="showConfirmPassword" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg v-else class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.076m1.406-1.407A10.014 10.014 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.059 10.059 0 01-4.293 5.774M6.228 6.228L17.772 17.772M9 9l6 6" />
                        </svg>
                      </button>
                    </div>
                    <p v-if="errors.password_confirmation" class="mt-1 text-[10px] text-red-500">{{ errors.password_confirmation[0] }}</p>
                  </div>

                  <div>
                    <FloatingSelect
                      v-model="form.role"
                      label="System Role"
                      placeholder="Select Role"
                      :options="roleOptions"
                      :error="!!errors.role"
                    />
                    <p v-if="errors.role" class="mt-1 text-[10px] text-red-500">{{ errors.role[0] }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tab 4: Media Information -->
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
                      <img v-if="photoPreview" :src="photoPreview" alt="Profile preview" class="w-full h-full object-cover" />
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
                      <button v-if="photoPreview || selectedFile" type="button" @click="clearPhoto" class="px-2 py-1 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 font-semibold text-[10px] rounded-lg hover:bg-rose-100 transition-colors cursor-pointer">
                        Remove
                      </button>
                    </div>
                  </div>
                </div>
                <p v-if="errors.profile_image || errors.avatar" class="mt-1 text-[10px] text-red-500">{{ errors.profile_image?.[0] || errors.avatar?.[0] }}</p>
              </div>

              <!-- Attachments Section (Reference: Payment Receipts / Payments) -->
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
                    <a
                      :href="item.url"
                      :download="item.filename"
                      target="_blank"
                      @click.stop
                      class="text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 p-0.5 rounded-md transition-all cursor-pointer"
                      title="Download File"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                      </svg>
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

          </div>

          <!-- Footer Buttons -->
          <div class="flex justify-end space-x-3 p-6 border-t border-slate-100 dark:border-zinc-800 shrink-0 bg-slate-50/50 dark:bg-zinc-900/50">
            <button
              type="button"
              @click="$emit('close')"
              class="px-4 h-9 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 rounded-lg text-xs font-semibold transition-all cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-4 h-9 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-xs transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ saving ? 'Saving...' : (isEditing ? (isManagerMode ? 'Update Manager' : 'Update Employee') : (isManagerMode ? 'Create Manager' : 'Create Employee')) }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from 'vue';
import FloatingSelect from '@/components/common/FloatingSelect.vue';
import CustomPhoneInput from '@/components/common/CustomPhoneInput.vue';
import { useToast } from '@/composables/useToast';
import { useCurrencyStore } from '@/stores/currency';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const { showToast } = useToast();
const currencyStore = useCurrencyStore();
const authStore = useAuthStore();

const currencySymbol = computed(() => {
  return currencyStore.symbol || authStore.user?.company?.currency_symbol || authStore.user?.company?.currency || 'Rs.';
});

// Select options
const genderOptions = [
  { value: '', label: 'Select Gender' },
  { value: 'male', label: 'Male' },
  { value: 'female', label: 'Female' },
  { value: 'other', label: 'Other' }
];

const maritalStatusOptions = [
  { value: '', label: 'Select Status' },
  { value: 'single', label: 'Single' },
  { value: 'married', label: 'Married' },
  { value: 'divorced', label: 'Divorced' },
  { value: 'widowed', label: 'Widowed' }
];

const employmentTypeOptions = [
  { value: '', label: 'Select Type' },
  { value: 'full_time', label: 'Full Time' },
  { value: 'part_time', label: 'Part Time' },
  { value: 'contract', label: 'Contract' },
  { value: 'intern', label: 'Intern' }
];

const employmentStatusOptions = [
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
  { value: 'terminated', label: 'Terminated' },
  { value: 'on_leave', label: 'On Leave' }
];

const salaryTypeOptions = [
  { value: '', label: 'Select Type' },
  { value: 'monthly', label: 'Monthly' },
  { value: 'hourly', label: 'Hourly' },
  { value: 'daily', label: 'Daily' }
];

// Props and Emits
const props = defineProps({
  employee: {
    type: Object,
    default: null
  },
  isManagerMode: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close', 'saved', 'add-manager']);

const activeTab = ref('basic');

// Reactive data
const form = ref({
  first_name: '',
  middle_name: '',
  last_name: '',
  email: '',
  phone: '',
  mobile: '',
  date_of_birth: '',
  gender: '',
  marital_status: '',
  company_id: '',
  department_id: '',
  department_ids: [],
  position_id: '',
  manager_id: '',
  hire_date: '',
  employment_type: '',
  employment_status: 'active',
  basic_salary: '',
  salary_type: '',
  hourly_rate: '',
  is_manager: false,
  create_user_account: false,
  password: '',
  password_confirmation: '',
  role: 'employee'
});

const departments = ref([]);
const positions = ref([]);
const employees = ref([]);
const roles = ref([]);
const companies = ref([]);
const errors = ref({});
const saving = ref(false);
const isInitializing = ref(false);
const fileInput = ref(null);
const selectedFile = ref(null);
const photoPreview = ref(null);
const isDragging = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);

const attachmentInputRef = ref(null);
const attachmentFiles = ref([]);
const existingAttachments = ref([]);
const isDraggingAttachment = ref(false);

const triggerAttachmentInput = () => {
  if (attachmentInputRef.value) attachmentInputRef.value.click();
};
const handleAttachmentChange = (e) => {
  const files = Array.from(e.target.files);
  addAttachmentFiles(files);
};
const handleAttachmentDrop = (e) => {
  isDraggingAttachment.value = false;
  const files = Array.from(e.dataTransfer.files);
  addAttachmentFiles(files);
};
const addAttachmentFiles = (files) => {
  if (existingAttachments.value.length + attachmentFiles.value.length + files.length > 5) {
    showToast('Maximum 5 attachments allowed in total!', 'error');
    return;
  }
  const allowedExts = ['png', 'jpg', 'jpeg', 'webp', 'pdf'];
  for (const file of files) {
    const ext = file.name.split('.').pop().toLowerCase();
    if (!allowedExts.includes(ext)) {
      showToast(`File "${file.name}" is not supported. Allowed formats: PNG, JPG, WEBP, PDF.`, 'error');
      continue;
    }
    if (file.size > 5 * 1024 * 1024) {
      showToast(`File "${file.name}" exceeds 5MB limit.`, 'error');
      continue;
    }
    attachmentFiles.value.push(file);
  }
};
const removeAttachmentFile = (index) => {
  attachmentFiles.value.splice(index, 1);
};
const removeExistingAttachment = (index) => {
  existingAttachments.value.splice(index, 1);
};

// Computed
const isEditing = computed(() => !!props.employee);

const filteredPositions = computed(() => {
  return positions.value || [];
});

const selectedPositionDetails = computed(() => {
  if (!form.value.position_id) return null;
  return positions.value.find(p => p.id == form.value.position_id) || null;
});

const getPositionLevelBadgeClass = (level) => {
  switch (level?.toLowerCase()) {
    case 'executive':
    case 'director':
      return 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200/60 dark:border-purple-800/50';
    case 'manager':
    case 'lead':
      return 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-200/60 dark:border-indigo-800/50';
    case 'senior':
      return 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border-blue-200/60 dark:border-blue-800/50';
    case 'mid':
      return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200/60 dark:border-emerald-800/50';
    default:
      return 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-slate-300 border-slate-200 dark:border-zinc-700';
  }
};

const availableManagers = computed(() => {
  if (!employees.value) {
    return [];
  }
  return employees.value.filter(employee => {
    if (props.employee && employee.id === props.employee.id) {
      return false;
    }
    return true;
  });
});

const companySelectOptions = computed(() => [
  { value: '', label: 'Select Company' },
  ...companies.value.map(c => ({ value: c.id, label: c.company_name || c.name }))
]);

// Filtered departments for employee mode based on selected manager
const filteredDepartmentList = computed(() => {
  if (props.isManagerMode || !form.value.manager_id) {
    return departments.value;
  }
  const selectedMgr = employees.value.find(m => String(m.id) === String(form.value.manager_id));
  if (!selectedMgr) return departments.value;

  const managedIds = new Set();
  if (selectedMgr.department_id) managedIds.add(String(selectedMgr.department_id));
  if (selectedMgr.managed_departments?.length) {
    selectedMgr.managed_departments.forEach(d => managedIds.add(String(d.id)));
  }
  departments.value.forEach(d => {
    if (String(d.manager_id) === String(selectedMgr.id) || String(d.manager?.id) === String(selectedMgr.id)) {
      managedIds.add(String(d.id));
    }
  });

  if (managedIds.size === 0) return departments.value;
  return departments.value.filter(d => managedIds.has(String(d.id)));
});

// Filtered managers for employee mode based on selected department
const filteredManagerList = computed(() => {
  if (!form.value.department_id) {
    return availableManagers.value;
  }
  const selectedDeptId = String(form.value.department_id);
  const dept = departments.value.find(d => String(d.id) === selectedDeptId);

  const matchingManagers = availableManagers.value.filter(m => {
    if (dept && (String(dept.manager_id) === String(m.id) || String(dept.manager?.id) === String(m.id))) {
      return true;
    }
    if (String(m.department_id) === selectedDeptId) {
      return true;
    }
    if (m.managed_departments?.some(md => String(md.id) === selectedDeptId)) {
      return true;
    }
    return false;
  });

  return matchingManagers.length > 0 ? matchingManagers : availableManagers.value;
});

const departmentSelectOptions = computed(() => [
  { value: '', label: 'Select Department' },
  ...filteredDepartmentList.value.map(d => ({ value: d.id, label: d.name }))
]);

const positionSelectOptions = computed(() => [
  { value: '', label: 'Select Position' },
  ...filteredPositions.value.map(p => ({ value: p.id, label: p.title }))
]);

const managerSelectOptions = computed(() => [
  { value: '', label: 'Select Manager' },
  ...filteredManagerList.value.map(m => ({ value: m.id, label: m.full_name }))
]);

const roleOptions = computed(() => {
  const map = new Map();
  if (Array.isArray(roles.value) && roles.value.length > 0) {
    roles.value.forEach(r => {
      if (r && r.name) {
        const labelName = r.name.charAt(0).toUpperCase() + r.name.slice(1).replace(/_/g, ' ');
        map.set(r.name, { value: r.name, label: labelName });
      }
    });
  }
  if (!map.has('employee')) map.set('employee', { value: 'employee', label: 'Employee' });
  if (!map.has('manager')) map.set('manager', { value: 'manager', label: 'Manager' });
  if (!map.has('admin')) map.set('admin', { value: 'Company Admin', label: 'Company Admin' });
  return Array.from(map.values());
});

// Two-way Watcher 1: Department Selection -> Preserve Manager & Position if valid
watch(() => form.value.department_id, (newDeptId) => {
  if (isInitializing.value || !newDeptId || props.isManagerMode) return;

  const matchingMgrs = filteredManagerList.value;
  const currentMgrId = String(form.value.manager_id || '');

  if (currentMgrId && matchingMgrs.some(m => String(m.id) === currentMgrId)) {
    // Keep currently selected manager!
  } else if (matchingMgrs.length === 1) {
    form.value.manager_id = String(matchingMgrs[0].id);
  } else {
    const dept = departments.value.find(d => String(d.id) === String(newDeptId));
    if (dept && (dept.manager_id || dept.manager?.id)) {
      form.value.manager_id = String(dept.manager_id || dept.manager.id);
    } else {
      form.value.manager_id = '';
    }
  }
});

// Two-way Watcher 2: Manager Selection -> Preserve Department if valid
watch(() => form.value.manager_id, (newMgrId) => {
  if (isInitializing.value || !newMgrId || props.isManagerMode) return;

  const managedDepts = filteredDepartmentList.value;
  const currentDeptId = String(form.value.department_id || '');

  if (currentDeptId && managedDepts.some(d => String(d.id) === currentDeptId)) {
    // Keep currently selected department!
  } else if (managedDepts.length === 1) {
    form.value.department_id = String(managedDepts[0].id);
  }
});

// Auto-select managerial position in Manager Mode if empty
watch(filteredPositions, (newPositions) => {
  if (props.isManagerMode && !form.value.position_id && newPositions.length > 0) {
    const managerPos = newPositions.find(p => p.level === 'manager' || /manager/i.test(p.title)) || newPositions[0];
    if (managerPos) {
      form.value.position_id = managerPos.id;
    }
  }
}, { immediate: true });

// Methods
const handleFileSelect = (e) => {
  const file = e.target.files[0];
  if (file) {
    setPhotoFile(file);
  }
};

const handleDrop = (e) => {
  isDragging.value = false;
  const file = e.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    setPhotoFile(file);
  }
};

const setPhotoFile = (file) => {
  if (file.size > 10 * 1024 * 1024) {
    showToast('Profile image size must not exceed 10MB.', 'error');
    return;
  }
  selectedFile.value = file;
  photoPreview.value = URL.createObjectURL(file);
};

const clearPhoto = () => {
  selectedFile.value = null;
  photoPreview.value = null;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const fetchCompanies = async () => {
  try {
    const response = await axios.get('/api/companies/my-companies');
    companies.value = response.data.companies || [];
    if (!form.value.company_id && companies.value.length > 0) {
      form.value.company_id = response.data.active_company_id || companies.value[0].id;
    }
  } catch (error) {
    console.error('Error fetching companies:', error);
  }
};

const fetchRoles = async () => {
  try {
    const response = await axios.get('/api/roles');
    roles.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error fetching roles:', error);
  }
};

const fetchDepartments = async () => {
  try {
    const response = await axios.get('/api/departments');
    departments.value = response.data;
  } catch (error) {
    console.error('Error fetching departments:', error);
  }
};

const fetchPositions = async () => {
  try {
    const response = await axios.get('/api/positions');
    positions.value = response.data;
  } catch (error) {
    console.error('Error fetching positions:', error);
  }
};

const fetchEmployees = async () => {
  try {
    const response = await axios.get('/api/employees/for-dropdown');
    employees.value = response.data;
  } catch (error) {
    console.error('Error fetching employees:', error);
    employees.value = [];
  }
};

const validateForm = () => {
  const errs = {};

  if (!form.value.first_name || !form.value.first_name.trim()) {
    errs.first_name = ['First name is required.'];
  }
  if (!form.value.last_name || !form.value.last_name.trim()) {
    errs.last_name = ['Last name is required.'];
  }
  if (!form.value.email || !form.value.email.trim()) {
    errs.email = ['Email address is required.'];
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email.trim())) {
    errs.email = ['Please enter a valid email address.'];
  }
  if (!form.value.gender) {
    errs.gender = ['Gender selection is required.'];
  }
  if (!form.value.company_id) {
    errs.company_id = ['Company assignment is required.'];
  }
  if (!form.value.hire_date) {
    errs.hire_date = ['Hire date is required.'];
  }
  if (!form.value.employment_type) {
    errs.employment_type = ['Employment type is required.'];
  }
  if (form.value.basic_salary === '' || form.value.basic_salary === null || form.value.basic_salary === undefined) {
    errs.basic_salary = ['Basic salary is required.'];
  } else if (isNaN(form.value.basic_salary) || Number(form.value.basic_salary) < 0) {
    errs.basic_salary = ['Basic salary must be a valid non-negative number.'];
  }
  if (!form.value.salary_type) {
    errs.salary_type = ['Salary type is required.'];
  }

  // System Login Access Validation
  if (form.value.create_user_account || (!isEditing.value && form.value.password)) {
    if (!isEditing.value || form.value.password) {
      if (!form.value.password) {
        errs.password = ['Password is required for system login access.'];
      } else if (form.value.password.length < 8) {
        errs.password = ['Password must be at least 8 characters.'];
      }
      if (form.value.password !== form.value.password_confirmation) {
        errs.password_confirmation = ['Password confirmation does not match.'];
      }
    }
    if (!form.value.role) {
      errs.role = ['System role selection is required.'];
    }
  }

  return errs;
};

const saveEmployee = async () => {
  errors.value = {};

  if (props.isManagerMode) {
    form.value.is_manager = true;
    if (form.value.create_user_account) {
      form.value.role = 'manager';
    }
  }

  // Client-side validation check
  const validationErrors = validateForm();
  if (Object.keys(validationErrors).length > 0) {
    errors.value = validationErrors;
    const firstKey = Object.keys(validationErrors)[0];
    const basicFields = ['first_name', 'last_name', 'email', 'gender', 'phone', 'mobile', 'date_of_birth'];
    const empFields = ['company_id', 'hire_date', 'employment_type', 'position_id', 'department_id'];
    if (basicFields.includes(firstKey)) {
      activeTab.value = 'basic';
    } else if (empFields.includes(firstKey)) {
      activeTab.value = 'employment';
    } else {
      activeTab.value = 'salary';
    }
    const firstMsg = Object.values(validationErrors)[0]?.[0] || 'Please fill in all required fields.';
    showToast(firstMsg, 'error');
    return;
  }

  saving.value = true;

  try {
    const formData = new FormData();
    
    if (props.isManagerMode && form.value.department_ids?.length > 0) {
      form.value.department_id = form.value.department_ids[0];
    }

    // Append form data
    Object.keys(form.value).forEach(key => {
      if (Array.isArray(form.value[key])) {
        form.value[key].forEach(val => {
          formData.append(`${key}[]`, val);
        });
      } else if (typeof form.value[key] === 'boolean') {
        formData.append(key, form.value[key] ? '1' : '0');
      } else if (form.value[key] !== null && form.value[key] !== undefined && form.value[key] !== '') {
        formData.append(key, form.value[key]);
      }
    });

    if (props.isManagerMode) {
      formData.set('is_manager', '1');
      if (form.value.create_user_account) {
        formData.set('role', 'manager');
      }
    }

    // Append photo
    if (selectedFile.value) {
      formData.append('profile_image', selectedFile.value);
    }

    // Append newly added attachment files
    if (attachmentFiles.value.length > 0) {
      attachmentFiles.value.forEach(file => {
        formData.append('attachments[]', file);
      });
    }

    // Append retained existing attachment paths
    if (existingAttachments.value.length > 0) {
      existingAttachments.value.forEach(att => {
        formData.append('existing_attachments[]', att.path || att.filename || att);
      });
    }

    let response;
    if (isEditing.value) {
      formData.append('_method', 'PUT');
      response = await axios.post(`/api/employees/${props.employee.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    } else {
      response = await axios.post('/api/employees', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    }

    showToast(
      isEditing.value ? (props.isManagerMode ? 'Manager updated successfully' : 'Employee updated successfully') : (props.isManagerMode ? 'Manager created successfully' : 'Employee created successfully'),
      'success'
    );
    emit('saved', response.data);
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {};
      const firstBackendError = Object.values(errors.value)[0]?.[0] || 'Validation failed. Please check required fields.';
      showToast(firstBackendError, 'error');
    } else {
      console.error('Error saving employee:', error);
      showToast(error.response?.data?.message || 'Error saving employee', 'error');
    }
  } finally {
    saving.value = false;
  }
};

const formatDateForInput = (val) => {
  if (!val) return '';
  if (typeof val === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(val)) {
    return val;
  }
  const d = new Date(val);
  if (isNaN(d.getTime())) return '';
  return d.toISOString().split('T')[0];
};

// Initialize form if editing or set smart defaults
const initializeForm = () => {
  isInitializing.value = true;
  attachmentFiles.value = [];

  if (props.employee) {
    Object.keys(form.value).forEach(key => {
      if (props.employee[key] !== undefined && props.employee[key] !== null) {
        form.value[key] = props.employee[key];
      }
    });

    // 1. DATE FIELD POPULATION (YYYY-MM-DD)
    if (props.employee.date_of_birth) {
      form.value.date_of_birth = formatDateForInput(props.employee.date_of_birth);
    }
    if (props.employee.hire_date) {
      form.value.hire_date = formatDateForInput(props.employee.hire_date);
    }

    // 2. DEPARTMENT & DROPDOWN PRE-SELECTIONS (string-casted IDs)
    if (props.employee.company_id) {
      form.value.company_id = String(props.employee.company_id);
    }
    if (props.employee.department_id) {
      form.value.department_id = String(props.employee.department_id);
    }
    if (props.employee.managed_departments && props.employee.managed_departments.length) {
      form.value.department_ids = props.employee.managed_departments.map(d => String(d.id));
    } else if (props.employee.department_id) {
      form.value.department_ids = [String(props.employee.department_id)];
    } else {
      form.value.department_ids = [];
    }
    if (props.employee.position_id) {
      form.value.position_id = String(props.employee.position_id);
    }
    if (props.employee.manager_id) {
      form.value.manager_id = String(props.employee.manager_id);
    }

    // Is Manager flag
    if (props.isManagerMode || props.employee.is_manager) {
      form.value.is_manager = true;
    }

    // 3. SYSTEM ROLE PRE-SELECTION
    if (props.employee.user_id || props.employee.user) {
      form.value.create_user_account = true;
      if (props.employee.user?.roles?.[0]?.name) {
        form.value.role = props.employee.user.roles[0].name;
      } else if (props.isManagerMode || props.employee.is_manager) {
        form.value.role = 'manager';
      } else {
        form.value.role = 'employee';
      }
    } else {
      if (props.isManagerMode || props.employee.is_manager) {
        form.value.role = 'manager';
      } else {
        form.value.role = 'employee';
      }
    }

    // 4. BLANK PASSWORD FIELDS ON EDIT
    form.value.password = '';
    form.value.password_confirmation = '';

    if (props.employee.avatar_url) {
      photoPreview.value = props.employee.avatar_url;
    } else if (props.employee.profile_image) {
      photoPreview.value = props.employee.profile_image.startsWith('http') ? props.employee.profile_image : `/storage/${props.employee.profile_image}`;
    } else if (props.employee.user?.profile_image) {
      photoPreview.value = props.employee.user.profile_image.startsWith('http') ? props.employee.user.profile_image : `/storage/${props.employee.user.profile_image}`;
    }

    // 5. ATTACHMENTS PRE-SELECTION
    if (props.employee.attachments_urls && Array.isArray(props.employee.attachments_urls)) {
      existingAttachments.value = [...props.employee.attachments_urls];
    } else if (props.employee.user?.attachments_urls && Array.isArray(props.employee.user.attachments_urls)) {
      existingAttachments.value = [...props.employee.user.attachments_urls];
    } else {
      existingAttachments.value = [];
    }
  } else {
    existingAttachments.value = [];
    if (!form.value.hire_date) {
      form.value.hire_date = formatDateForInput(new Date());
    }
    if (!form.value.employment_type) {
      form.value.employment_type = 'full_time';
    }
    if (!form.value.salary_type) {
      form.value.salary_type = 'monthly';
    }
    if (props.isManagerMode) {
      form.value.is_manager = true;
      form.value.role = 'manager';
    } else {
      form.value.is_manager = false;
      if (!form.value.role) {
        form.value.role = 'employee';
      }
    }
    form.value.password = '';
    form.value.password_confirmation = '';
  }

  nextTick(() => {
    isInitializing.value = false;
  });
};

const refreshDropdowns = async () => {
  try {
    await Promise.all([
      fetchCompanies(),
      fetchRoles(),
      fetchDepartments(),
      fetchPositions(),
      fetchEmployees()
    ]);
  } catch (err) {
    console.error('Error refreshing modal dropdowns:', err);
  }
};

watch([() => props.employee, () => props.isManagerMode], async () => {
  await refreshDropdowns();
  initializeForm();
}, { immediate: true, deep: true });

// Lifecycle
onMounted(async () => {
  await refreshDropdowns();
  initializeForm();

  window.addEventListener('department-saved', refreshDropdowns);
  window.addEventListener('position-saved', refreshDropdowns);
  window.addEventListener('manager-saved', refreshDropdowns);
});

onUnmounted(() => {
  window.removeEventListener('department-saved', refreshDropdowns);
  window.removeEventListener('position-saved', refreshDropdowns);
  window.removeEventListener('manager-saved', refreshDropdowns);
});
</script>
