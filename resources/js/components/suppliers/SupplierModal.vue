<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200" style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);">
      <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-2xl shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 text-left transition-all duration-300 flex flex-col max-h-[90vh] overflow-y-auto my-auto z-10" @click.stop>
        
        <!-- Header -->
        <div class="p-6 pb-4 border-b border-slate-100 dark:border-zinc-800 shrink-0 relative">
          <!-- Sleek Close Icon Button -->
          <button
            type="button"
            @click="closeModal"
            class="absolute top-5 right-5 text-slate-400 dark:text-zinc-500 hover:text-slate-600 dark:hover:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 p-1.5 rounded-lg transition-all cursor-pointer"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <h3 class="text-xs font-bold text-slate-800 dark:text-zinc-100 uppercase tracking-wider">{{ isEdit ? 'Edit Supplier' : 'Add New Supplier' }}</h3>
        </div>

        <!-- Tab Navigation (Clean text tabs, matching Customer Modal pattern) -->
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
            :class="['px-4 py-2 font-bold rounded-t-lg transition-all focus:outline-none border-b-2 cursor-pointer', activeTab === 'address' ? 'text-indigo-600 dark:text-indigo-400 border-indigo-600 bg-white dark:bg-zinc-900' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300 border-transparent']"
            @click="activeTab = 'address'"
          >
            Address
          </button>
          <button
            type="button"
            :class="['px-4 py-2 font-bold rounded-t-lg transition-all focus:outline-none border-b-2 cursor-pointer', activeTab === 'business' ? 'text-indigo-600 dark:text-indigo-400 border-indigo-600 bg-white dark:bg-zinc-900' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300 border-transparent']"
            @click="activeTab = 'business'"
          >
            Business
          </button>
          <button
            type="button"
            :class="['px-4 py-2 font-bold rounded-t-lg transition-all focus:outline-none border-b-2 cursor-pointer', activeTab === 'contact' ? 'text-indigo-600 dark:text-indigo-400 border-indigo-600 bg-white dark:bg-zinc-900' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300 border-transparent']"
            @click="activeTab = 'contact'"
          >
            Contact
          </button>
          <button
            type="button"
            :class="['px-4 py-2 font-bold rounded-t-lg transition-all focus:outline-none border-b-2 cursor-pointer', activeTab === 'media' ? 'text-indigo-600 dark:text-indigo-400 border-indigo-600 bg-white dark:bg-zinc-900' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300 border-transparent']"
            @click="activeTab = 'media'"
          >
            Media
          </button>
        </div>

        <form @submit.prevent="saveSupplier" class="flex flex-col flex-1 min-h-0">
          <div class="flex-1 overflow-y-auto p-6 space-y-4 pr-4 custom-scrollbar">
            
            <!-- Tab 1: Basic Information -->
            <div v-if="activeTab === 'basic'" class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Supplier Name *</label>
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    placeholder="e.g. Acme Supplies"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.name }"
                  />
                  <p v-if="errors.name" class="mt-1 text-[10px] text-red-500">{{ errors.name[0] }}</p>
                </div>

                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Company Name</label>
                  <input
                    v-model="form.company_name"
                    type="text"
                    placeholder="e.g. Acme Corporation"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.company_name }"
                  />
                  <p v-if="errors.company_name" class="mt-1 text-[10px] text-red-500">{{ errors.company_name[0] }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Email Address</label>
                  <input
                    v-model="form.email"
                    type="email"
                    placeholder="e.g. supplier@example.com"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.email }"
                  />
                  <p v-if="errors.email" class="mt-1 text-[10px] text-red-500">{{ errors.email[0] }}</p>
                </div>

                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Date of Birth</label>
                  <div class="relative">
                    <div v-if="showCalendar" class="fixed inset-0 z-40" @click.stop="showCalendar = false"></div>
                    <button
                      type="button"
                      @click="showCalendar = !showCalendar"
                      class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs bg-white dark:bg-zinc-950 transition-all flex items-center gap-2 text-left cursor-pointer"
                      :class="[form.date_of_birth ? 'text-slate-800 dark:text-zinc-200' : 'text-slate-400 dark:text-zinc-500', { 'border-red-300 dark:border-red-700': errors.date_of_birth }]"
                    >
                      <svg class="h-3.5 w-3.5 text-slate-400 dark:text-zinc-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      <span class="font-medium">{{ form.date_of_birth ? formatDisplayDate(form.date_of_birth) : 'Select date' }}</span>
                    </button>

                    <!-- Custom Calendar Popover -->
                    <div v-if="showCalendar" class="absolute z-50 left-0 top-full mt-1.5 w-[225px] rounded-xl shadow-none dark:shadow-none dark:[box-shadow:none] bg-white text-slate-900 border border-slate-200 dark:bg-[#1E1E2D] dark:text-slate-100 dark:border-slate-800 p-2 select-none">
                      <!-- Month/Year Nav -->
                      <div class="flex items-center justify-between mb-1">
                        <button type="button" @click="calPrevMonth" class="p-0.5 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 dark:text-zinc-400 transition-colors cursor-pointer">
                          <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        
                        <div class="flex items-center space-x-1">
                          <!-- Month Dropdown -->
                          <div class="relative">
                            <div v-if="showMonthList" class="fixed inset-0 z-40" @click.stop="showMonthList = false"></div>
                            <button
                              type="button"
                              @click="showMonthList = !showMonthList"
                              class="flex items-center space-x-0.5 px-1 py-0.5 text-xs font-semibold text-slate-700 dark:text-zinc-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded transition-colors cursor-pointer focus:outline-none"
                            >
                              <span>{{ calMonthName.slice(0, 3) }}</span>
                              <svg class="h-2.5 w-2.5 text-slate-400 dark:text-zinc-500 transition-transform duration-200" :class="{ 'rotate-180': showMonthList }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                              </svg>
                            </button>
                            <!-- Month Floating List -->
                            <div
                              v-if="showMonthList"
                              class="absolute z-[60] left-0 top-full mt-1 w-20 max-h-36 overflow-y-auto rounded-lg shadow-none dark:shadow-none dark:[box-shadow:none] bg-white text-slate-900 border border-slate-200 dark:bg-[#1E1E2D] dark:text-slate-100 dark:border-slate-800 py-0.5 custom-scrollbar-thin text-left animate-in fade-in duration-100"
                            >
                              <button
                                v-for="(name, idx) in monthNames"
                                :key="idx"
                                type="button"
                                @click="selectCalMonth(idx)"
                                class="w-full text-left px-2 py-0.5 text-[10px] font-medium transition-colors cursor-pointer"
                                :class="calMonth === idx ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800'"
                              >
                                {{ name.slice(0, 3) }}
                              </button>
                            </div>
                          </div>

                          <!-- Year Dropdown -->
                          <div class="relative">
                            <div v-if="showYearList" class="fixed inset-0 z-40" @click.stop="showYearList = false"></div>
                            <button
                              type="button"
                              @click="showYearList = !showYearList"
                              class="flex items-center space-x-0.5 px-1 py-0.5 text-xs font-semibold text-slate-700 dark:text-zinc-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded transition-colors cursor-pointer focus:outline-none"
                            >
                              <span>{{ calYear }}</span>
                              <svg class="h-2.5 w-2.5 text-slate-400 dark:text-zinc-500 transition-transform duration-200" :class="{ 'rotate-180': showYearList }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                              </svg>
                            </button>
                            <!-- Year Floating List -->
                            <div
                              v-if="showYearList"
                              class="absolute z-[60] left-1/2 -translate-x-1/2 top-full mt-1 w-20 max-h-36 overflow-y-auto rounded-lg shadow-none dark:shadow-none dark:[box-shadow:none] bg-white text-slate-900 border border-slate-200 dark:bg-[#1E1E2D] dark:text-slate-100 dark:border-slate-800 py-0.5 custom-scrollbar-thin text-left animate-in fade-in duration-100"
                            >
                              <button
                                v-for="y in yearOptions"
                                :key="y"
                                type="button"
                                @click="selectCalYear(y)"
                                class="w-full text-left px-2 py-0.5 text-[10px] font-medium transition-colors cursor-pointer"
                                :class="calYear === y ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800'"
                              >
                                {{ y }}
                              </button>
                            </div>
                          </div>
                        </div>

                        <button type="button" @click="calNextMonth" class="p-0.5 rounded-md hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-500 dark:text-zinc-400 transition-colors cursor-pointer">
                          <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </button>
                      </div>
                      <!-- Day Headers -->
                      <div class="grid grid-cols-7 mb-0.5 gap-y-0.5">
                        <span v-for="d in ['Su','Mo','Tu','We','Th','Fr','Sa']" :key="d" class="text-center text-[9px] font-semibold text-slate-400 dark:text-zinc-500 uppercase py-0.5">{{ d }}</span>
                      </div>
                      <!-- Day Grid -->
                      <div class="grid grid-cols-7 gap-y-0.5">
                        <button
                          v-for="(day, i) in calDays" :key="i"
                          type="button"
                          @click="day.val && selectCalDay(day.val)"
                          :disabled="!day.val"
                          class="h-6 w-6 mx-auto rounded-md text-[10px] font-medium transition-all cursor-pointer disabled:cursor-default disabled:opacity-0 flex items-center justify-center"
                          :class="day.val && isSelectedDay(day.val) ? 'bg-indigo-600 text-white font-bold' : day.val && isTodayDay(day.val) ? 'text-indigo-600 dark:text-indigo-400 font-bold hover:bg-indigo-50 dark:hover:bg-indigo-900/20' : day.val ? 'text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800' : ''"
                        >{{ day.val || '' }}</button>
                      </div>
                      <!-- Quick Actions -->
                      <div class="flex items-center justify-between mt-1 pt-1 border-t border-slate-100 dark:border-zinc-800">
                        <button type="button" @click="clearCalDate" class="text-[10px] font-semibold text-slate-400 dark:text-zinc-500 hover:text-slate-600 dark:hover:text-zinc-300 transition-colors cursor-pointer py-0.5">Clear</button>
                        <button type="button" @click="selectToday" class="text-[10px] font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors cursor-pointer py-0.5">Today</button>
                      </div>
                    </div>
                  </div>
                  <p v-if="errors.date_of_birth" class="mt-1 text-[10px] text-red-500">{{ errors.date_of_birth[0] }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <CustomPhoneInput
                    label="Mobile Number"
                    v-model="form.mobile"
                    :error="errors.mobile"
                  />
                </div>

                <div>
                  <CustomPhoneInput
                    label="Phone Number"
                    v-model="form.phone"
                    :error="errors.phone"
                  />
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <CustomFloatingSelect
                    label="Gender"
                    v-model="form.gender"
                    :options="genderOptions"
                    placeholder="Select Gender"
                  />
                  <p v-if="errors.gender" class="mt-1 text-[10px] text-red-500">{{ errors.gender[0] }}</p>
                </div>

                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Status</label>
                  <div class="flex items-center gap-3 h-[38px] px-3 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-950 transition-all">
                    <button
                      type="button"
                      role="switch"
                      :aria-checked="form.is_active"
                      @click="form.is_active = !form.is_active"
                      class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-slate-900/20 dark:focus:ring-emerald-500/20"
                      :class="form.is_active ? 'bg-slate-900 dark:bg-emerald-500' : 'bg-slate-200 dark:bg-zinc-800'"
                    >
                      <span
                        class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out"
                        :class="form.is_active ? 'translate-x-4' : 'translate-x-0'"
                      />
                    </button>
                    <span class="text-xs font-semibold" :class="form.is_active ? 'text-slate-900 dark:text-emerald-400' : 'text-slate-400 dark:text-zinc-500'">
                      {{ form.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </div>
                  <p v-if="errors.is_active" class="mt-1 text-[10px] text-red-500">{{ errors.is_active[0] }}</p>
                </div>
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Notes</label>
                <textarea
                  v-model="form.notes"
                  rows="2"
                  placeholder="Add any additional notes about this supplier..."
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                  :class="{ 'border-red-300 dark:border-red-700': errors.notes }"
                ></textarea>
                <p v-if="errors.notes" class="mt-1 text-[10px] text-red-500">{{ errors.notes[0] }}</p>
              </div>
            </div>

            <!-- Tab 2: Address Information (2nd Tab right after Basic Info) -->
            <div v-if="activeTab === 'address'" class="space-y-4">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Street Address</label>
                <textarea
                  v-model="form.address"
                  rows="2"
                  placeholder="Enter full street address..."
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                  :class="{ 'border-red-300 dark:border-red-700': errors.address }"
                ></textarea>
                <p v-if="errors.address" class="mt-1 text-[10px] text-red-500">{{ errors.address[0] }}</p>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">City</label>
                  <input
                    v-model="form.city"
                    type="text"
                    placeholder="Enter city"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.city }"
                  />
                  <p v-if="errors.city" class="mt-1 text-[10px] text-red-500">{{ errors.city[0] }}</p>
                </div>

                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">State / Province</label>
                  <input
                    v-model="form.state"
                    type="text"
                    placeholder="Enter state or province"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.state }"
                  />
                  <p v-if="errors.state" class="mt-1 text-[10px] text-red-500">{{ errors.state[0] }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Postal Code</label>
                  <input
                    v-model="form.postal_code"
                    type="text"
                    placeholder="Enter postal code"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.postal_code }"
                  />
                  <p v-if="errors.postal_code" class="mt-1 text-[10px] text-red-500">{{ errors.postal_code[0] }}</p>
                </div>

                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Country</label>
                  <input
                    v-model="form.country"
                    type="text"
                    placeholder="Enter country"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.country }"
                  />
                  <p v-if="errors.country" class="mt-1 text-[10px] text-red-500">{{ errors.country[0] }}</p>
                </div>
              </div>
            </div>

            <!-- Tab 3: Contact Information -->
            <div v-if="activeTab === 'contact'" class="space-y-4">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Website</label>
                <input
                  v-model="form.website"
                  type="url"
                  placeholder="https://www.supplier.com"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                  :class="{ 'border-red-300 dark:border-red-700': errors.website }"
                />
                <p v-if="errors.website" class="mt-1 text-[10px] text-red-500">{{ errors.website[0] }}</p>
              </div>
            </div>

            <!-- Tab 4: Business Information -->
            <div v-if="activeTab === 'business'" class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Tax Number / GSTIN</label>
                  <input
                    v-model="form.tax_number"
                    type="text"
                    placeholder="Enter tax number"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.tax_number }"
                  />
                  <p v-if="errors.tax_number" class="mt-1 text-[10px] text-red-500">{{ errors.tax_number[0] }}</p>
                </div>

                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Credit Limit ({{ currencySymbol }})</label>
                  <input
                    v-model="form.credit_limit"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.credit_limit }"
                  />
                  <p v-if="errors.credit_limit" class="mt-1 text-[10px] text-red-500">{{ errors.credit_limit[0] }}</p>
                </div>
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Payment Terms (Days)</label>
                <input
                  v-model="form.payment_terms_days"
                  type="number"
                  min="0"
                  placeholder="30"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                  :class="{ 'border-red-300 dark:border-red-700': errors.payment_terms_days }"
                />
                <p v-if="errors.payment_terms_days" class="mt-1 text-[10px] text-red-500">{{ errors.payment_terms_days[0] }}</p>
              </div>
            </div>

            <!-- Tab 5: Media Information -->
            <div v-if="activeTab === 'media'" class="space-y-6">
              <!-- Profile Photo Section -->
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Profile Photo</label>
                <div 
                  @dragover.prevent="isDraggingPhoto = true" 
                  @dragleave.prevent="isDraggingPhoto = false" 
                  @drop.prevent="handlePhotoDrop"
                  :class="[
                    'relative flex items-center gap-4 p-4 rounded-xl border-2 border-dashed transition-all duration-200',
                    isDraggingPhoto ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20' : 'border-slate-200 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-950/40'
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
                    <p class="text-[10px] text-slate-500 dark:text-zinc-400 mt-0.5">JPEG, PNG, WEBP, GIF up to 10MB</p>
                    <div class="flex items-center gap-2 mt-2">
                      <label class="px-2.5 py-1 bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-semibold text-[10px] rounded-lg cursor-pointer hover:opacity-90 transition-opacity">
                        Browse Image
                        <input ref="photoInputRef" type="file" accept="image/*" class="hidden" @change="handlePhotoSelect" />
                      </label>
                      <button v-if="photoPreview || photoFile" type="button" @click="clearPhoto" class="px-2 py-1 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 font-semibold text-[10px] rounded-lg hover:bg-rose-100 transition-colors cursor-pointer">
                        Remove
                      </button>
                    </div>
                  </div>
                </div>
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
                    accept="image/*,.pdf,.doc,.docx,.xls,.xlsx"
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
                      PNG, JPG, PDF, DOCX, XLSX (max 5MB each, max 5 files)
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
                      class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline flex items-center gap-1"
                      title="View File"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                      </svg>
                      <span class="truncate max-w-[150px]">{{ item.filename }}</span>
                    </a>
                    <button type="button" @click.stop="removeExistingAttachment(index)" class="text-slate-400 hover:text-rose-500 p-0.5 rounded-md transition-all cursor-pointer">
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
              @click="closeModal"
              class="px-4 h-9 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 rounded-lg text-xs font-semibold transition-all cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving || !form.name"
              class="px-4 h-9 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-xs transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ saving ? 'Saving...' : (isEdit ? 'Update Supplier' : 'Create Supplier') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script>
import { ref, reactive, watch, nextTick, computed, onUnmounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useCurrencyStore } from '@/stores/currency';
import { useToast } from '@/composables/useToast';
import CustomFloatingSelect from '@/components/common/CustomFloatingSelect.vue';
import CustomPhoneInput from '@/components/common/CustomPhoneInput.vue';
import api from '@/services/api';

export default {
  name: 'SupplierModal',
  components: {
    CustomFloatingSelect,
    CustomPhoneInput
  },
  props: {
    show: {
      type: Boolean,
      default: false
    },
    supplier: {
      type: Object,
      default: null
    },
    isEdit: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const { showToast } = useToast();
    const authStore = useAuthStore();
    const currencyStore = useCurrencyStore();

    const currencySymbol = computed(() => {
      return currencyStore.symbol || authStore.user?.company?.currency_symbol || authStore.user?.company?.currency || '$';
    });

    const genderOptions = [
      { value: 'male', label: 'Male' },
      { value: 'female', label: 'Female' },
      { value: 'other', label: 'Other' }
    ];

    const statusOptions = [
      { value: true, label: 'Active' },
      { value: false, label: 'Inactive' }
    ];

    const saving = ref(false);
    const errors = ref({});
    const activeTab = ref('basic');

    const photoInputRef = ref(null);
    const photoFile = ref(null);
    const photoPreview = ref(null);
    const isDraggingPhoto = ref(false);

    const attachmentInputRef = ref(null);
    const attachmentFiles = ref([]);
    const existingAttachments = ref([]);
    const isDraggingAttachment = ref(false);

    const handlePhotoSelect = (e) => {
      const file = e.target.files[0];
      if (file) setPhotoFile(file);
    };
    const handlePhotoDrop = (e) => {
      isDraggingPhoto.value = false;
      const file = e.dataTransfer.files[0];
      if (file && file.type.startsWith('image/')) setPhotoFile(file);
    };
    const setPhotoFile = (file) => {
      if (file.size > 10 * 1024 * 1024) {
        showToast('Photo size must not exceed 10MB.', 'error');
        return;
      }
      photoFile.value = file;
      photoPreview.value = URL.createObjectURL(file);
    };
    const clearPhoto = () => {
      photoFile.value = null;
      photoPreview.value = null;
      if (photoInputRef.value) photoInputRef.value.value = '';
    };

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
      for (const file of files) {
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

    const form = reactive({
      name: '',
      company_name: '',
      email: '',
      phone: '',
      mobile: '',
      address: '',
      city: '',
      state: '',
      postal_code: '',
      country: '',
      date_of_birth: '',
      gender: '',
      tax_number: '',
      website: '',
      notes: '',
      credit_limit: 0,
      payment_terms_days: 30,
      is_active: true
    });

    // Calendar Picker State
    const showCalendar = ref(false);
    const calMonth = ref(new Date().getMonth());
    const calYear = ref(new Date().getFullYear());
    const showMonthList = ref(false);
    const showYearList = ref(false);

    const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    const selectCalMonth = (idx) => {
      calMonth.value = idx;
      showMonthList.value = false;
    };

    const selectCalYear = (y) => {
      calYear.value = y;
      showYearList.value = false;
    };

    const formatDisplayDate = (dateStr) => {
      if (!dateStr) return '';
      const onlyDate = dateStr.split(' ')[0].split('T')[0];
      const d = new Date(onlyDate + 'T00:00:00');
      if (isNaN(d.getTime())) return dateStr;
      const day = String(d.getDate()).padStart(2, '0');
      const mon = monthNames[d.getMonth()].slice(0, 3);
      const yr = d.getFullYear();
      return `${day} ${mon} ${yr}`;
    };

    const yearOptions = computed(() => {
      const current = new Date().getFullYear();
      const years = [];
      for (let y = current; y >= current - 100; y--) {
        years.push(y);
      }
      return years;
    });

    const calMonthName = computed(() => monthNames[calMonth.value]);

    const calDays = computed(() => {
      const days = [];
      const firstDay = new Date(calYear.value, calMonth.value, 1).getDay();
      const totalDays = new Date(calYear.value, calMonth.value + 1, 0).getDate();
      for (let i = 0; i < firstDay; i++) {
        days.push({ val: null });
      }
      for (let d = 1; d <= totalDays; d++) {
        days.push({ val: d });
      }
      return days;
    });

    const calPrevMonth = () => {
      if (calMonth.value === 0) {
        calMonth.value = 11;
        calYear.value--;
      } else {
        calMonth.value--;
      }
    };

    const calNextMonth = () => {
      if (calMonth.value === 11) {
        calMonth.value = 0;
        calYear.value++;
      } else {
        calMonth.value++;
      }
    };

    const selectCalDay = (day) => {
      const mm = String(calMonth.value + 1).padStart(2, '0');
      const dd = String(day).padStart(2, '0');
      form.date_of_birth = `${calYear.value}-${mm}-${dd}`;
      showCalendar.value = false;
    };

    const clearCalDate = () => {
      form.date_of_birth = '';
      showCalendar.value = false;
    };

    const selectToday = () => {
      const today = new Date();
      calMonth.value = today.getMonth();
      calYear.value = today.getFullYear();
      selectCalDay(today.getDate());
    };

    const isSelectedDay = (day) => {
      if (!form.date_of_birth) return false;
      const parts = form.date_of_birth.split(' ')[0].split('T')[0].split('-');
      if (parts.length !== 3) return false;
      return (
        parseInt(parts[0]) === calYear.value &&
        parseInt(parts[1]) - 1 === calMonth.value &&
        parseInt(parts[2]) === day
      );
    };

    const isTodayDay = (day) => {
      const today = new Date();
      return (
        today.getFullYear() === calYear.value &&
        today.getMonth() === calMonth.value &&
        today.getDate() === day
      );
    };

    const resetForm = () => {
      Object.keys(form).forEach(key => {
        if (key === 'is_active') {
          form[key] = true;
        } else if (key === 'credit_limit') {
          form[key] = 0;
        } else if (key === 'payment_terms_days') {
          form[key] = 30;
        } else {
          form[key] = '';
        }
      });
      errors.value = {};
      showCalendar.value = false;
      photoFile.value = null;
      photoPreview.value = null;
      attachmentFiles.value = [];
      existingAttachments.value = [];
    };

    const loadSupplierData = () => {
      if (!props.supplier || !props.isEdit) {
        return;
      }

      try {
        const rawDob = props.supplier.date_of_birth || '';
        const cleanDob = rawDob ? rawDob.split(' ')[0].split('T')[0] : '';

        const supplierData = {
          name: props.supplier.name || '',
          company_name: props.supplier.company_name || '',
          email: props.supplier.email || '',
          phone: props.supplier.phone || '',
          mobile: props.supplier.mobile || '',
          address: props.supplier.address || '',
          city: props.supplier.city || '',
          state: props.supplier.state || '',
          postal_code: props.supplier.postal_code || '',
          country: props.supplier.country || '',
          date_of_birth: cleanDob,
          gender: props.supplier.gender || '',
          tax_number: props.supplier.tax_number || '',
          website: props.supplier.website || '',
          notes: props.supplier.notes || '',
          credit_limit: parseFloat(props.supplier.credit_limit) || 0,
          payment_terms_days: parseInt(props.supplier.payment_terms_days) || 30,
          is_active: Boolean(props.supplier.is_active)
        };

        Object.assign(form, supplierData);

        if (cleanDob) {
          const parts = cleanDob.split('-');
          if (parts.length === 3) {
            calYear.value = parseInt(parts[0]);
            calMonth.value = parseInt(parts[1]) - 1;
          }
        }

        if (props.supplier.profile_image) {
          photoPreview.value = props.supplier.profile_image.startsWith('http') ? props.supplier.profile_image : `/storage/${props.supplier.profile_image}`;
        }

        if (props.supplier.attachments_urls && Array.isArray(props.supplier.attachments_urls)) {
          existingAttachments.value = [...props.supplier.attachments_urls];
        } else {
          existingAttachments.value = [];
        }
      } catch (error) {
        console.error('Error loading supplier data:', error);
      }
    };

    const handleKeyDown = (e) => {
      if (e.key === 'Escape' && props.show) {
        closeModal();
      }
    };

    watch(() => props.show, (newVal) => {
      if (newVal) {
        activeTab.value = 'basic';
        if (props.isEdit && props.supplier) {
          nextTick(() => {
            loadSupplierData();
          });
        } else {
          resetForm();
        }
        document.body.style.overflow = 'hidden';
        window.addEventListener('keydown', handleKeyDown);
      } else {
        document.body.style.overflow = '';
        window.removeEventListener('keydown', handleKeyDown);
      }
    }, { immediate: true });

    onUnmounted(() => {
      document.body.style.overflow = '';
      window.removeEventListener('keydown', handleKeyDown);
    });

    watch(() => props.supplier, (newVal) => {
      if (props.show && props.isEdit && newVal) {
        nextTick(() => {
          loadSupplierData();
        });
      }
    }, { deep: true, immediate: true });

    const closeModal = () => {
      resetForm();
      emit('close');
    };

    const saveSupplier = async () => {
      saving.value = true;
      errors.value = {};

      try {
        const formData = new FormData();
        Object.keys(form).forEach(key => {
          if (typeof form[key] === 'boolean') {
            formData.append(key, form[key] ? '1' : '0');
          } else if (form[key] !== null && form[key] !== undefined && form[key] !== '') {
            formData.append(key, form[key]);
          }
        });

        if (photoFile.value) {
          formData.append('profile_image', photoFile.value);
        }

        if (attachmentFiles.value.length > 0) {
          attachmentFiles.value.forEach(file => {
            formData.append('attachments[]', file);
          });
        }

        if (existingAttachments.value.length > 0) {
          existingAttachments.value.forEach(att => {
            formData.append('existing_attachments[]', att.path || att.filename || att);
          });
        }

        let response;
        if (props.isEdit && props.supplier?.id) {
          formData.append('_method', 'PUT');
          response = await api.post(`/suppliers/${props.supplier.id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
          });
          showToast('Supplier updated successfully!', 'success');
        } else {
          response = await api.post('/suppliers', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
          });
          showToast('Supplier created successfully!', 'success');
        }

        emit('saved', response.data.supplier || response.data.data || response.data);
        closeModal();
      } catch (error) {
        console.error('Error saving supplier:', error);
        if (error.response && error.response.status === 422) {
          errors.value = error.response.data.errors || {};
          showToast('Please fix the validation errors below.', 'error');
        } else {
          const message = error.response?.data?.message || 'Failed to save supplier';
          showToast(message, 'error');
        }
      } finally {
        saving.value = false;
      }
    };

    return {
      currencySymbol,
      genderOptions,
      statusOptions,
      saving,
      errors,
      activeTab,
      photoInputRef,
      photoFile,
      photoPreview,
      isDraggingPhoto,
      handlePhotoSelect,
      handlePhotoDrop,
      clearPhoto,
      attachmentInputRef,
      attachmentFiles,
      existingAttachments,
      isDraggingAttachment,
      triggerAttachmentInput,
      handleAttachmentChange,
      handleAttachmentDrop,
      removeAttachmentFile,
      removeExistingAttachment,
      handleAttachmentDrop,
      removeAttachmentFile,
      form,
      showCalendar,
      calMonth,
      calYear,
      showMonthList,
      showYearList,
      monthNames,
      selectCalMonth,
      selectCalYear,
      formatDisplayDate,
      yearOptions,
      calMonthName,
      calDays,
      calPrevMonth,
      calNextMonth,
      selectCalDay,
      clearCalDate,
      selectToday,
      isSelectedDay,
      isTodayDay,
      closeModal,
      saveSupplier
    };
  }
};
</script>
