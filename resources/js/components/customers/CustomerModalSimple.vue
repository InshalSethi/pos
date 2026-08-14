<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full transition-all duration-200" style="background-color: rgba(0, 0, 0, 0.6) !important; backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);">
      <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-2xl shadow-2xl rounded-2xl bg-white dark:bg-[#12141a] text-slate-800 dark:text-slate-100 text-left transition-all duration-300 flex flex-col z-10" @click.stop>
        
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

          <h3 class="text-xs font-bold text-slate-800 dark:text-zinc-100 uppercase tracking-wider">{{ isEdit ? 'Edit Customer' : 'Add New Customer' }}</h3>
        </div>

        <!-- Tab Navigation (Clean text tabs, matching Supplier Modal) -->
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
        </div>

        <!-- Form area -->
        <form @submit.prevent="saveCustomer" class="flex flex-col">
          <div class="p-6 pb-12 space-y-4">
            
            <!-- Tab 1: Basic Information -->
            <div v-if="activeTab === 'basic'" class="space-y-4">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Customer Name *</label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  placeholder="e.g. John Doe"
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                  :class="{ 'border-red-300 dark:border-red-700': errors.name }"
                />
                <p v-if="errors.name" class="mt-1 text-[10px] text-red-500">{{ errors.name[0] }}</p>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Email Address</label>
                  <input
                    v-model="form.email"
                    type="email"
                    placeholder="e.g. john@example.com"
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
                    label="Mobile"
                    v-model="form.mobile"
                    :error="errors.mobile"
                  />
                </div>
                <div>
                  <CustomPhoneInput
                    label="Phone"
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
                  placeholder="Additional notes about the customer..."
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                  :class="{ 'border-red-300 dark:border-red-700': errors.notes }"
                ></textarea>
                <p v-if="errors.notes" class="mt-1 text-[10px] text-red-500">{{ errors.notes[0] }}</p>
              </div>
            </div>

            <!-- Tab 2: Address -->
            <div v-if="activeTab === 'address'" class="space-y-4">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Street Address</label>
                <textarea
                  v-model="form.address"
                  rows="2"
                  placeholder="Street address, suite, apartment..."
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
                    placeholder="e.g. New York"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.city }"
                  />
                  <p v-if="errors.city" class="mt-1 text-[10px] text-red-500">{{ errors.city[0] }}</p>
                </div>
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">State</label>
                  <input
                    v-model="form.state"
                    type="text"
                    placeholder="e.g. California"
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
                    placeholder="e.g. 10001"
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
                    placeholder="e.g. USA"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.country }"
                  />
                  <p v-if="errors.country" class="mt-1 text-[10px] text-red-500">{{ errors.country[0] }}</p>
                </div>
              </div>
            </div>

            <!-- Tab 3: Business Information -->
            <div v-if="activeTab === 'business'" class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Tax Number / GSTIN</label>
                  <input
                    v-model="form.tax_number"
                    type="text"
                    placeholder="e.g. GSTIN12345"
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
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Notes</label>
                <textarea
                  v-model="form.notes"
                  rows="3"
                  placeholder="Additional notes about the customer..."
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                  :class="{ 'border-red-300 dark:border-red-700': errors.notes }"
                ></textarea>
                <p v-if="errors.notes" class="mt-1 text-[10px] text-red-500">{{ errors.notes[0] }}</p>
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
              :disabled="!form.name || saving"
              class="px-4 h-9 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-xs transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ saving ? 'Saving...' : (isEdit ? 'Update Customer' : 'Add Customer') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script>
import { ref, reactive, watch, computed, onUnmounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useCurrencyStore } from '@/stores/currency';
import { useToast } from '@/composables/useToast';
import CustomFloatingSelect from '@/components/common/CustomFloatingSelect.vue';
import CustomPhoneInput from '@/components/common/CustomPhoneInput.vue';
import api from '@/services/api';

export default {
  name: 'CustomerModalSimple',
  components: {
    CustomFloatingSelect,
    CustomPhoneInput
  },
  props: {
    show: {
      type: Boolean,
      default: false
    },
    customer: {
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

    const saving = ref(false);
    const errors = ref({});
    const activeTab = ref('basic');

    const genderOptions = [
      { value: 'male', label: 'Male' },
      { value: 'female', label: 'Female' },
      { value: 'other', label: 'Other' }
    ];

    const statusOptions = [
      { value: true, label: 'Active' },
      { value: false, label: 'Inactive' }
    ];

    const showCalendar = ref(false);
    const calMonth = ref(new Date().getMonth());
    const calYear = ref(new Date().getFullYear());
    const showMonthList = ref(false);
    const showYearList = ref(false);

    const selectCalMonth = (idx) => {
      calMonth.value = idx;
      showMonthList.value = false;
    };

    const selectCalYear = (y) => {
      calYear.value = y;
      showYearList.value = false;
    };

    const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    const formatDisplayDate = (dateStr) => {
      if (!dateStr) return '';
      const onlyDate = dateStr.split(' ')[0].split('T')[0];
      const d = new Date(onlyDate + 'T00:00:00');
      if (isNaN(d.getTime())) {
        const fallbackD = new Date(dateStr);
        if (isNaN(fallbackD.getTime())) return 'Invalid Date';
        return fallbackD.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
      }
      return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    };

    const calMonthName = computed(() => monthNames[calMonth.value]);

    const yearOptions = computed(() => {
      const currentYear = new Date().getFullYear();
      const yearsList = [];
      for (let y = currentYear + 5; y >= currentYear - 100; y--) {
        yearsList.push(y);
      }
      return yearsList;
    });

    const calDays = computed(() => {
      const firstDay = new Date(calYear.value, calMonth.value, 1).getDay();
      const daysInMonth = new Date(calYear.value, calMonth.value + 1, 0).getDate();
      const days = [];
      for (let i = 0; i < firstDay; i++) days.push({ val: null });
      for (let d = 1; d <= daysInMonth; d++) days.push({ val: d });
      return days;
    });

    const calPrevMonth = () => {
      if (calMonth.value === 0) { calMonth.value = 11; calYear.value--; }
      else calMonth.value--;
    };
    const calNextMonth = () => {
      if (calMonth.value === 11) { calMonth.value = 0; calYear.value++; }
      else calMonth.value++;
    };

    const selectCalDay = (day) => {
      const m = String(calMonth.value + 1).padStart(2, '0');
      const d = String(day).padStart(2, '0');
      form.date_of_birth = `${calYear.value}-${m}-${d}`;
      showCalendar.value = false;
    };

    const isSelectedDay = (day) => {
      if (!form.date_of_birth) return false;
      const [sy, sm, sd] = form.date_of_birth.split('-').map(Number);
      return sy === calYear.value && sm === calMonth.value + 1 && sd === day;
    };

    const isTodayDay = (day) => {
      const t = new Date();
      return t.getFullYear() === calYear.value && t.getMonth() === calMonth.value && t.getDate() === day;
    };

    const clearCalDate = () => { form.date_of_birth = ''; showCalendar.value = false; };
    const selectToday = () => {
      const t = new Date();
      calMonth.value = t.getMonth(); calYear.value = t.getFullYear();
      selectCalDay(t.getDate());
    };

    const form = reactive({
      name: '',
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
      notes: '',
      credit_limit: 0,
      is_active: true
    });

    const resetForm = () => {
      Object.keys(form).forEach(key => {
        if (key === 'is_active') {
          form[key] = true;
        } else if (key === 'credit_limit') {
          form[key] = 0;
        } else {
          form[key] = '';
        }
      });
      errors.value = {};
    };

    const loadCustomerData = () => {
      if (props.customer && props.isEdit) {
        Object.keys(form).forEach(key => {
          if (props.customer[key] !== undefined) {
            form[key] = props.customer[key];
          }
        });

        if (form.date_of_birth) {
          const onlyDate = form.date_of_birth.split(' ')[0].split('T')[0];
          form.date_of_birth = onlyDate;
          
          const d = new Date(onlyDate + 'T00:00:00');
          if (!isNaN(d.getTime())) {
            calMonth.value = d.getMonth();
            calYear.value = d.getFullYear();
          }
        }
      }
    };

    const saveCustomer = async () => {
      saving.value = true;
      errors.value = {};

      try {
        const url = props.isEdit ? `/customers/${props.customer.id}` : '/customers';
        const method = props.isEdit ? 'put' : 'post';

        const payload = {
          ...form,
          type: form.type || 'registered'
        };

        const response = await api[method](url, payload);

        showToast(
          props.isEdit ? 'Customer updated successfully' : 'Customer created successfully',
          'success'
        );

        const savedCustomer = response.data.customer || response.data;
        emit('saved', savedCustomer);
        emit('close');
      } catch (error) {
        if (error.response?.status === 422) {
          errors.value = error.response.data.errors;
        } else {
          showToast(error.response?.data?.message || 'Error saving customer', 'error');
        }
      } finally {
        saving.value = false;
      }
    };

    const handleKeyDown = (e) => {
      if (e.key === 'Escape' && props.show) {
        emit('close');
      }
    };

    watch(() => props.show, (newVal) => {
      if (newVal) {
        activeTab.value = 'basic';
        resetForm();
        loadCustomerData();
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

    return {
      currencySymbol,
      activeTab,
      genderOptions,
      statusOptions,
      form,
      errors,
      saving,
      saveCustomer,
      showCalendar,
      calMonth,
      calYear,
      calMonthName,
      calDays,
      calPrevMonth,
      calNextMonth,
      selectCalDay,
      isSelectedDay,
      isTodayDay,
      clearCalDate,
      selectToday,
      formatDisplayDate,
      yearOptions,
      monthNames,
      showMonthList,
      showYearList,
      selectCalMonth,
      selectCalYear
    };
  }
};
</script>

<style scoped>
/* Thin scrollbar styling for floating lists */
.custom-scrollbar-thin::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar-thin::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar-thin::-webkit-scrollbar-thumb {
  background: #cbd5e1; /* slate-300 */
  border-radius: 2px;
}
.custom-scrollbar-thin::-webkit-scrollbar-thumb:hover {
  background: #94a3b8; /* slate-400 */
}
.dark .custom-scrollbar-thin::-webkit-scrollbar-thumb {
  background: #3f3f46; /* zinc-700 */
}
.dark .custom-scrollbar-thin::-webkit-scrollbar-thumb:hover {
  background: #52525b; /* zinc-600 */
}
</style>
