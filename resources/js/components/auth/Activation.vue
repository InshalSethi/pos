<template>
  <!-- Expired State (Advanced Modal) -->
  <div v-if="isExpired" class="min-h-screen bg-slate-900 flex items-center justify-center p-4 relative overflow-hidden">
    <div class="absolute inset-0 bg-slate-900/90 backdrop-blur-md"></div>
    
    <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-rose-200 dark:border-rose-900/50 shadow-2xl w-full max-w-xl relative z-10 overflow-hidden transform transition-all flex flex-col max-h-[90vh]">
      
      <!-- Header -->
      <div class="px-8 py-6 border-b border-rose-100 dark:border-rose-900/30 flex items-center justify-between gap-4 bg-rose-50/50 dark:bg-rose-900/10 shrink-0">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-900/50 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
          </div>
          <div>
            <h3 class="text-xl font-black text-rose-800 dark:text-rose-400">Subscription Expired</h3>
            <p class="text-xs text-rose-600/80 dark:text-rose-400/80 font-medium mt-1">Your system access has been suspended.</p>
          </div>
        </div>
        
        <!-- Current User Info -->
        <div class="hidden sm:flex items-center gap-3" v-if="authStore.user">
          <div class="text-right">
            <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ authStore.user?.name }}</p>
            <p class="text-[10px] font-medium text-slate-500 dark:text-slate-400">{{ authStore.user?.email }}</p>
          </div>
          <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center border border-indigo-200 dark:border-indigo-800">
            <span class="text-indigo-600 dark:text-indigo-400 font-bold text-sm">{{ authStore.user?.name ? authStore.user.name.charAt(0).toUpperCase() : 'U' }}</span>
          </div>
        </div>
      </div>
      
      <!-- Scrollable Content -->
      <div class="p-8 overflow-y-auto space-y-6">
        
        <template v-if="authStore.hasRole('admin') || authStore.hasRole('owner')">
          <div v-if="!showGlobalChangePlan">
            <!-- Expired Plan Summary -->
            <div class="bg-rose-50 dark:bg-rose-900/10 p-5 rounded-2xl border border-rose-100 dark:border-rose-900/30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
              <div>
                <p class="text-[10px] font-bold text-rose-500 dark:text-rose-400 uppercase tracking-wider mb-1">
                  {{ globalSelectedPlan ? 'Selected Plan' : 'Expired Plan' }}
                </p>
                <p class="text-2xl font-black text-rose-700 dark:text-rose-300 capitalize">
                  {{ globalSelectedPlan ? globalSelectedPlan.name : (licenseStore.licenseData?.plan || 'Enterprise') }}
                </p>
              </div>
              <div class="flex flex-col gap-1 text-left sm:text-right">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Started: <span class="font-bold text-slate-700 dark:text-slate-300 ml-1">{{ formatDate(licenseStore.licenseData?.start_date) || 'N/A' }}</span></p>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Expired: <span class="font-bold text-rose-600 dark:text-rose-400 ml-1">{{ formatDate(licenseStore.licenseData?.expires_at) || 'N/A' }}</span></p>
              </div>
            </div>

            <!-- Duration Toggle Switch (Monthly / Yearly) -->
            <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-zinc-800/40 rounded-2xl border border-slate-100 dark:border-zinc-800 mb-5">
              <div>
                <p class="text-xs font-bold text-slate-800 dark:text-zinc-200">Billing Duration</p>
                <p class="text-[11px] text-slate-500 dark:text-zinc-400">Select payment cycle duration</p>
              </div>
              <div class="bg-slate-200 dark:bg-zinc-700 p-1 rounded-xl flex items-center gap-1">
                <button
                  type="button"
                  @click="globalBillingCycle = 'monthly'"
                  :class="globalBillingCycle === 'monthly' ? 'bg-white dark:bg-zinc-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900'"
                  class="px-3 py-1.5 text-xs font-extrabold rounded-lg transition-all"
                >
                  Monthly
                </button>
                <button
                  type="button"
                  @click="globalBillingCycle = 'yearly'"
                  :class="(globalBillingCycle === 'yearly' || globalBillingCycle === 'annual') ? 'bg-white dark:bg-zinc-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900'"
                  class="px-3 py-1.5 text-xs font-extrabold rounded-lg transition-all flex items-center gap-1"
                >
                  <span>Yearly</span>
                  <span class="bg-emerald-500 text-white text-[9px] px-1.5 py-0.5 rounded-full uppercase">Save 20%</span>
                </button>
              </div>
            </div>

            <!-- Renewal Options -->
            <div class="space-y-4">
              <div class="flex justify-between items-center">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Payment Method</label>
                <button @click="showGlobalChangePlan = true" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 text-xs font-bold transition-colors">
                  Change Plan Instead?
                </button>
              </div>
              
              <!-- Existing Card Option -->
              <label class="flex items-center justify-between p-4 border rounded-xl cursor-pointer transition-all" :class="globalPaymentMethod === 'existing' ? 'border-indigo-500 bg-indigo-50/30 dark:bg-indigo-900/10' : 'border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800'">
                <div class="flex items-center gap-4">
                  <input type="radio" v-model="globalPaymentMethod" value="existing" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500" />
                  <div class="flex items-center gap-2">
                    <div class="bg-slate-800 text-white dark:bg-zinc-700 rounded px-2.5 py-1 text-xs font-bold shadow-sm">VISA</div>
                    <span class="text-sm font-bold text-slate-700 dark:text-slate-300">•••• 4242</span>
                  </div>
                </div>
                <span class="text-xs font-medium text-slate-500">Exp 12/28</span>
              </label>

              <!-- New Card Option -->
              <label class="flex items-center gap-4 p-4 border rounded-xl cursor-pointer transition-all" :class="globalPaymentMethod === 'new' ? 'border-indigo-500 bg-indigo-50/30 dark:bg-indigo-900/10' : 'border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800'">
                <input type="radio" v-model="globalPaymentMethod" value="new" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500" />
                <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Use a new credit card</span>
              </label>

              <!-- New Card Form -->
              <div v-if="globalPaymentMethod === 'new'" class="space-y-3 pt-2 animate-fade-in p-4 bg-slate-50 dark:bg-zinc-800/30 rounded-xl border border-slate-100 dark:border-zinc-800">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Card Number</label>
                  <input
                    :value="newCardNumber"
                    @input="handleCardNumberChange"
                    maxlength="19"
                    type="text"
                    placeholder="0000 0000 0000 0000"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm bg-white dark:bg-zinc-950 text-slate-900 dark:text-white font-mono tracking-widest"
                    :class="cardErrors.cardNumber ? 'border-rose-500 focus:ring-rose-500/20 bg-rose-50/10' : 'border-slate-200 dark:border-zinc-700'"
                  />
                  <p v-if="cardErrors.cardNumber" class="mt-1 text-[11px] text-rose-500 font-bold flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span>{{ cardErrors.cardNumber }}</span>
                  </p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Expiry (MM/YY)</label>
                    <input
                      :value="newCardExpiry"
                      @input="handleCardExpiryChange"
                      maxlength="5"
                      type="text"
                      placeholder="MM/YY"
                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm bg-white dark:bg-zinc-950 text-slate-900 dark:text-white font-mono tracking-widest"
                      :class="cardErrors.cardExpiry ? 'border-rose-500 focus:ring-rose-500/20 bg-rose-50/10' : 'border-slate-200 dark:border-zinc-700'"
                    />
                    <p v-if="cardErrors.cardExpiry" class="mt-1 text-[11px] text-rose-500 font-bold flex items-center gap-1">
                      <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                      <span>{{ cardErrors.cardExpiry }}</span>
                    </p>
                  </div>
                  <div>
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">CVC</label>
                    <input
                      :value="newCardCvc"
                      @input="handleCardCvcChange"
                      maxlength="4"
                      type="text"
                      placeholder="123"
                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm bg-white dark:bg-zinc-950 text-slate-900 dark:text-white font-mono tracking-widest"
                      :class="cardErrors.cardCvc ? 'border-rose-500 focus:ring-rose-500/20 bg-rose-50/10' : 'border-slate-200 dark:border-zinc-700'"
                    />
                    <p v-if="cardErrors.cardCvc" class="mt-1 text-[11px] text-rose-500 font-bold flex items-center gap-1">
                      <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                      <span>{{ cardErrors.cardCvc }}</span>
                    </p>
                  </div>
                </div>
              </div>

              <!-- Coupon Code Section -->
              <div class="p-4 bg-slate-50 dark:bg-zinc-800/30 rounded-xl border border-slate-100 dark:border-zinc-800 space-y-2 mt-3">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Have a Coupon Code?</label>
                <div class="flex gap-2">
                  <input
                    v-model="globalCouponInput"
                    type="text"
                    placeholder="Enter Code (e.g. SAVE20)"
                    class="grow px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg text-xs uppercase font-mono tracking-wider focus:outline-none focus:ring-2 focus:ring-indigo-500/20 bg-white dark:bg-zinc-950 text-slate-900 dark:text-white"
                  />
                  <button
                    type="button"
                    @click="applyGlobalCoupon"
                    :disabled="globalCouponLoading || !globalCouponInput.trim()"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg transition-all disabled:opacity-50 cursor-pointer shrink-0"
                  >
                    <span v-if="globalCouponLoading">...</span>
                    <span v-else>Apply</span>
                  </button>
                </div>
                <p v-if="globalCouponMessage" class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center">
                  ✓ {{ globalCouponMessage }}
                </p>
                <p v-if="globalCouponError" class="text-[11px] font-bold text-rose-500 flex items-center">
                  ✕ {{ globalCouponError }}
                </p>
              </div>

            </div>
          </div>

          <!-- Change Plan Interface (No Pay Button here!) -->
          <div v-else class="animate-fade-in space-y-4">
            <div class="flex justify-between items-center mb-2">
              <div>
                <h4 class="text-sm font-bold text-slate-900 dark:text-white">Select a New Plan</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400">Click any plan below to select it and return to checkout.</p>
              </div>
              <button @click="showGlobalChangePlan = false" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg transition-colors">
                Back to Renewal
              </button>
            </div>
            
            <div class="flex justify-center mb-4">
              <div class="bg-slate-100 dark:bg-zinc-800 p-1 rounded-lg inline-flex items-center">
                <button @click="globalBillingCycle = 'monthly'" :class="globalBillingCycle === 'monthly' ? 'bg-white dark:bg-zinc-700 shadow text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'" class="px-4 py-1.5 text-xs font-bold rounded-md transition-all">Monthly</button>
                <button @click="globalBillingCycle = 'yearly'" :class="(globalBillingCycle === 'yearly' || globalBillingCycle === 'annual') ? 'bg-white dark:bg-zinc-700 shadow text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'" class="px-4 py-1.5 text-xs font-bold rounded-md transition-all">Annual (Save 20%)</button>
              </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div 
                v-for="plan in displayPlans" 
                :key="plan.id"
                @click="selectPlanAndReturn(plan)"
                class="p-4 rounded-xl border-2 cursor-pointer transition-all flex flex-col group hover:shadow-md"
                :class="globalSelectedPlan?.id === plan.id ? 'border-indigo-600 bg-indigo-50/50 dark:bg-indigo-900/20' : 'border-slate-200 dark:border-zinc-700 hover:border-indigo-400 dark:hover:border-indigo-600'"
              >
                <div class="flex justify-between items-start mb-2">
                  <div>
                    <h4 class="font-black text-slate-900 dark:text-white group-hover:text-indigo-600 transition-colors">{{ plan.name }}</h4>
                    <span v-if="globalSelectedPlan?.id === plan.id" class="inline-block text-[9px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-100 px-1.5 py-0.5 rounded mt-1">Selected</span>
                  </div>
                  <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-100 dark:bg-indigo-900/50 px-2.5 py-1 rounded-lg">
                    {{ (globalBillingCycle === 'yearly' || globalBillingCycle === 'annual') ? plan.priceAnnualStr : plan.priceMonthlyStr }}/{{ (globalBillingCycle === 'yearly' || globalBillingCycle === 'annual') ? 'yr' : 'mo' }}
                  </span>
                </div>
                <div class="mt-auto pt-3 flex flex-wrap gap-1.5">
                  <span v-for="feat in plan.features" :key="feat" class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-zinc-800 px-2 py-0.5 rounded-md">{{ feat }}</span>
                </div>
              </div>
            </div>
          </div>
        </template>
        
        <template v-else>
          <!-- Restricted Message for Employees -->
          <div class="bg-rose-50 dark:bg-rose-900/10 p-6 rounded-2xl border border-rose-100 dark:border-rose-900/30 text-center">
            <svg class="w-12 h-12 text-rose-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <h4 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Your subscription plan has expired</h4>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Ask your admin user to renew the plan.</p>
            
            <div class="inline-flex flex-col items-center p-4 bg-white dark:bg-zinc-800 rounded-xl border border-slate-100 dark:border-zinc-700 w-full sm:w-auto min-w-[250px] shadow-sm">
              <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center mb-3">
                <span class="text-indigo-600 dark:text-indigo-400 font-bold text-lg">{{ licenseStore.adminName ? licenseStore.adminName.charAt(0).toUpperCase() : 'A' }}</span>
              </div>
              <p class="text-sm font-bold text-slate-900 dark:text-white">{{ licenseStore.adminName }}</p>
              <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ licenseStore.adminEmail }}</p>
              <span class="mt-2 text-[9px] font-bold uppercase tracking-wider text-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-1 rounded">Admin User</span>
            </div>
          </div>
        </template>

      </div>

      <!-- Footer Actions -->
      <div class="p-6 border-t border-slate-100 dark:border-zinc-800 flex justify-between items-center bg-slate-50/50 dark:bg-zinc-800/30 shrink-0">
        <button @click="logout" class="px-4 py-2 text-sm font-bold text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400 transition-colors">
          Logout
        </button>
        
        <!-- Pay Button ONLY shown on the main Renewal screen (where card details are shown) -->
        <button
          v-if="!showGlobalChangePlan && (authStore.hasRole('admin') || authStore.hasRole('owner'))"
          @click="processGlobalRenewal"
          :disabled="isProcessingGlobalRenew"
          class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-3 px-8 rounded-xl transition-all shadow-md flex items-center gap-2 disabled:opacity-70"
        >
          <div v-if="isProcessingGlobalRenew" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
          <span>{{ isProcessingGlobalRenew ? 'Processing...' : `Pay ${currentPlanPriceText} & Restore Access` }}</span>
        </button>

        <!-- On Plan Selection Screen, show Back to Renewal button in footer -->
        <button
          v-else-if="showGlobalChangePlan && (authStore.hasRole('admin') || authStore.hasRole('owner'))"
          @click="showGlobalChangePlan = false"
          class="bg-slate-200 hover:bg-slate-300 dark:bg-zinc-700 text-slate-800 dark:text-white text-sm font-bold py-2.5 px-6 rounded-xl transition-all"
        >
          Back to Renewal
        </button>
        
        <button v-else-if="!authStore.hasRole('admin') && !authStore.hasRole('owner')" @click="checkRefreshStatus" :disabled="isCheckingStatus" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-3 px-8 rounded-xl transition-all shadow-md flex items-center gap-2 disabled:opacity-70">
          <svg :class="{'animate-spin': isCheckingStatus}" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
          <span>{{ isCheckingStatus ? 'Checking...' : 'Refresh Status' }}</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Normal Activation State -->
  <div v-else class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="flex justify-center">
        <div class="w-16 h-16 bg-primary-600 rounded-xl flex items-center justify-center text-white shadow-lg">
          <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
        </div>
      </div>
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        Activate Software
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600 max-w">
        Please enter your License Key to bind this device and unlock the POS.
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 border border-gray-200">
        <form class="space-y-6" @submit.prevent="submitActivation">
          <div>
            <label for="licenseKey" class="block text-sm font-medium text-gray-700">
              License Key
            </label>
            <div class="mt-1">
              <input id="licenseKey" v-model="form.licenseKey" type="text" required
                placeholder="XXXX-XXXX-XXXX-XXXX"
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">
              Hardware ID
            </label>
            <div class="mt-1 flex items-center bg-gray-50 border border-gray-200 rounded-md px-3 py-2">
              <span class="text-xs text-gray-500 font-mono flex-grow">{{ form.deviceId || 'Loading...' }}</span>
            </div>
            <p class="text-xs text-gray-400 mt-1">This device's unique fingerprint.</p>
          </div>

          <div v-if="error" class="text-sm text-red-600 bg-red-50 p-3 rounded-md border border-red-100">
            {{ error }}
          </div>

          <div v-if="successMsg" class="text-sm text-green-600 bg-green-50 p-3 rounded-md border border-green-100">
            {{ successMsg }}
          </div>

          <div>
            <button type="submit" :disabled="loading || !form.deviceId"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50">
              <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Activate
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useLicenseStore } from '@/stores/license';
import { useAuthStore } from '@/stores/auth';
import { useToast } from '@/composables/useToast';
import { validateCardNumber, validateCardExpiry, validateCardCvc } from '@/composables/useCardValidation';
import axios from 'axios';

const router = useRouter();
const licenseStore = useLicenseStore();
const authStore = useAuthStore();
const { showToast } = useToast();

const form = ref({
  licenseKey: '',
  deviceId: ''
});

const loading = ref(false);
const error = ref('');
const successMsg = ref('');

const isExpired = computed(() => {
  return licenseStore.licenseData && licenseStore.licenseData.status !== 'active';
});

// Advanced Modal State
const isProcessingGlobalRenew = ref(false);
const globalBillingCycle = ref('monthly');
const globalPaymentMethod = ref('existing');
const showGlobalChangePlan = ref(false);
const globalSelectedPlan = ref(null);

const newCardNumber = ref('');
const newCardExpiry = ref('');
const newCardCvc = ref('');
const cardErrors = ref({});

const globalCouponInput = ref('');
const globalCouponLoading = ref(false);
const globalCouponMessage = ref('');
const globalCouponError = ref('');
const globalAppliedCoupon = ref(null);

const applyGlobalCoupon = async () => {
  if (!globalCouponInput.value.trim()) return;
  globalCouponLoading.value = true;
  globalCouponError.value = '';
  globalCouponMessage.value = '';
  try {
    const selectedPlanSlug = globalSelectedPlan.value ? (globalSelectedPlan.value.slug || globalSelectedPlan.value.id) : (licenseStore.licenseData?.plan || 'enterprise');
    const { data } = await axios.post('/api/coupons/validate', {
      code: globalCouponInput.value,
      plan: selectedPlanSlug,
      billing_cycle: globalBillingCycle.value,
    });
    if (data.valid) {
      globalAppliedCoupon.value = data;
      globalCouponMessage.value = `Coupon "${data.coupon.code}" applied! Discount: $${data.discount_amount.toFixed(2)}`;
    }
  } catch (e) {
    globalAppliedCoupon.value = null;
    globalCouponError.value = e.response?.data?.message || 'Invalid coupon code';
  } finally {
    globalCouponLoading.value = false;
  }
};

watch([globalBillingCycle, globalSelectedPlan], () => {
  if (globalAppliedCoupon.value && globalCouponInput.value) {
    applyGlobalCoupon();
  }
});

const dbPlans = ref([]);

const fetchDatabasePlans = async () => {
  try {
    const res = await axios.get('/api/subscription-plans');
    if (Array.isArray(res.data)) {
      dbPlans.value = res.data;
    } else if (res.data && Array.isArray(res.data.data)) {
      dbPlans.value = res.data.data;
    }
  } catch (e) {
    console.error('Failed to load database subscription plans', e);
  }
};

const displayPlans = computed(() => {
  if (dbPlans.value.length > 0) {
    return dbPlans.value.map(p => ({
      id: p.slug || String(p.id),
      slug: p.slug || p.name.toLowerCase(),
      name: p.name,
      monthlyPrice: Number(p.monthly_price || 0),
      yearlyPrice: Number(p.yearly_price || 0),
      priceMonthlyStr: `$${p.monthly_price}`,
      priceAnnualStr: `$${p.yearly_price}`,
      features: [
        p.trial_days ? `${p.trial_days} Days Trial` : null,
        `${p.max_users_per_company} ${p.max_users_per_company === 1 ? 'User' : 'Users'}`,
        `${p.max_companies} ${p.max_companies === 1 ? 'Company' : 'Companies'}`,
      ].filter(Boolean),
      isCustom: p.is_custom
    }));
  }

  return [
    { id: 'standard', slug: 'standard', name: 'Standard (Free trial 14 days)', monthlyPrice: 0, yearlyPrice: 0, priceMonthlyStr: '$0', priceAnnualStr: '$0', features: ['1 User', '1 Company', '14 Days Trial'] },
    { id: 'basic', slug: 'basic', name: 'Basic ($20/month)', monthlyPrice: 20, yearlyPrice: 192, priceMonthlyStr: '$20', priceAnnualStr: '$192', features: ['1 User', '1 Company'] },
    { id: 'advance', slug: 'advance', name: 'Advance ($50/month)', monthlyPrice: 50, yearlyPrice: 480, priceMonthlyStr: '$50', priceAnnualStr: '$480', features: ['20 Users', '2 Companies'] },
    { id: 'enterprise', slug: 'enterprise', name: 'Enterprise ($100/month)', monthlyPrice: 100, yearlyPrice: 960, priceMonthlyStr: '$100', priceAnnualStr: '$960', features: ['100 Users/Co', '10 Companies'] },
    { id: 'custom', slug: 'custom', name: 'Custom', monthlyPrice: 1500, yearlyPrice: 14400, priceMonthlyStr: '$1,500+', priceAnnualStr: '$14,400+', features: ['Contact Sales'] }
  ];
});

const currentPlanPriceText = computed(() => {
  const isYearly = globalBillingCycle.value === 'yearly' || globalBillingCycle.value === 'annual';

  let targetPlanObj = null;

  if (globalSelectedPlan.value) {
    targetPlanObj = globalSelectedPlan.value;
  } else {
    const currentSlug = (licenseStore.licenseData?.plan || 'enterprise').toLowerCase();
    targetPlanObj = displayPlans.value.find(p => (p.slug || p.id || '').toLowerCase() === currentSlug);
  }

  let originalPrice = targetPlanObj ? (isYearly ? targetPlanObj.yearlyPrice : targetPlanObj.monthlyPrice) : (isYearly ? 960 : 100);

  if (globalAppliedCoupon.value) {
    return `$${globalAppliedCoupon.value.final_amount}`;
  }

  return `$${originalPrice}`;
});

const selectPlanAndReturn = (plan) => {
  globalSelectedPlan.value = plan;
  showGlobalChangePlan.value = false;
  showToast(`Selected ${plan.name}. Click Pay to complete renewal.`, 'info');
};

const handleCardNumberChange = (e) => {
  let raw = e.target.value.replace(/\D/g, '').slice(0, 16);
  let formatted = raw.replace(/(\d{4})(?=\d)/g, '$1 ').trim();
  newCardNumber.value = formatted;
  e.target.value = formatted;

  if (raw.length > 0 && raw.length < 13) {
    cardErrors.value.cardNumber = 'Card number must be 13 to 16 digits';
  } else {
    const res = validateCardNumber(formatted);
    if (res.valid) {
      delete cardErrors.value.cardNumber;
    } else if (raw.length >= 13) {
      cardErrors.value.cardNumber = res.message;
    }
  }
};

const handleCardExpiryChange = (e) => {
  let raw = e.target.value.replace(/\D/g, '').slice(0, 4);
  let formatted = raw;
  if (raw.length >= 3) {
    formatted = raw.slice(0, 2) + '/' + raw.slice(2);
  }
  newCardExpiry.value = formatted;
  e.target.value = formatted;

  if (formatted.length === 5) {
    const res = validateCardExpiry(formatted);
    if (res.valid) delete cardErrors.value.cardExpiry;
    else cardErrors.value.cardExpiry = res.message;
  } else if (formatted.length > 0 && formatted.length < 5) {
    cardErrors.value.cardExpiry = 'Expiry must be in MM/YY format';
  }
};

const handleCardCvcChange = (e) => {
  let raw = e.target.value.replace(/\D/g, '').slice(0, 4);
  newCardCvc.value = raw;
  e.target.value = raw;

  if (raw.length >= 3) {
    const res = validateCardCvc(raw);
    if (res.valid) delete cardErrors.value.cardCvc;
    else cardErrors.value.cardCvc = res.message;
  } else if (raw.length > 0 && raw.length < 3) {
    cardErrors.value.cardCvc = 'CVC must be 3 or 4 digits';
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString();
};

const logout = async () => {
  await authStore.logout();
  router.push('/login');
};

const validateCardDetails = () => {
  cardErrors.value = {};
  if (globalPaymentMethod.value === 'new') {
    const numRes = validateCardNumber(newCardNumber.value);
    if (!numRes.valid) cardErrors.value.cardNumber = numRes.message;

    const expRes = validateCardExpiry(newCardExpiry.value);
    if (!expRes.valid) cardErrors.value.cardExpiry = expRes.message;

    const cvcRes = validateCardCvc(newCardCvc.value);
    if (!cvcRes.valid) cardErrors.value.cardCvc = cvcRes.message;

    if (Object.keys(cardErrors.value).length > 0) {
      const firstErr = cardErrors.value.cardNumber || cardErrors.value.cardExpiry || cardErrors.value.cardCvc;
      showToast(firstErr, 'error');
      return false;
    }
  }
  return true;
};

const processGlobalRenewal = async () => {
  if (!validateCardDetails()) return;
  isProcessingGlobalRenew.value = true;
  try {
    const selectedPlanSlug = globalSelectedPlan.value ? (globalSelectedPlan.value.slug || globalSelectedPlan.value.id) : (licenseStore.licenseData?.plan || 'enterprise');
    const payload = {
      payment_method: globalPaymentMethod.value,
      billing_cycle: globalBillingCycle.value,
      plan: selectedPlanSlug
    };
    if (globalAppliedCoupon.value) {
      payload.coupon_code = globalAppliedCoupon.value.coupon.code;
    }
    if (globalPaymentMethod.value === 'new') {
      payload.cardNumber = newCardNumber.value;
      payload.cardExpiry = newCardExpiry.value;
      payload.cardCvc = newCardCvc.value;
    }
    const res = await axios.post('/api/license/renew', payload);
    if (res.data.license && licenseStore.licenseData) {
      licenseStore.licenseData.plan = selectedPlanSlug;
      licenseStore.licenseData.status = res.data.license.status;
      licenseStore.licenseData.expires_at = res.data.license.expires_at;
    }
    showToast('Subscription renewed successfully! System access restored.', 'success');
    setTimeout(() => {
        window.location.href = '/dashboard';
    }, 500);
  } catch (err) {
    if (err.response?.data?.errors) {
      const errs = err.response.data.errors;
      if (errs.cardNumber) cardErrors.value.cardNumber = errs.cardNumber[0];
      if (errs.cardExpiry) cardErrors.value.cardExpiry = errs.cardExpiry[0];
      if (errs.cardCvc) cardErrors.value.cardCvc = errs.cardCvc[0];
      const firstMsg = Object.values(errs)[0][0];
      showToast(firstMsg, 'error');
    } else {
      showToast(err.response?.data?.message || 'Failed to renew subscription', 'error');
    }
  } finally {
    isProcessingGlobalRenew.value = false;
  }
};

onMounted(async () => {
  await fetchDatabasePlans();

  if (licenseStore.licenseData && licenseStore.licenseData.license_key) {
    form.value.licenseKey = licenseStore.licenseData.license_key;
  }
  
  try {
    if (window.electronAPI && window.electronAPI.getDeviceId) {
      form.value.deviceId = await window.electronAPI.getDeviceId();
    } else {
      form.value.deviceId = 'BROWSER-DEV-FINGERPRINT';
    }
  } catch (err) {
    console.error('Failed to fetch hardware ID', err);
    error.value = 'Failed to generate Hardware ID.';
  }
});

const submitActivation = async () => {
  loading.value = true;
  error.value = '';
  successMsg.value = '';

  const res = await licenseStore.activateLicense(form.value.licenseKey, form.value.deviceId);
  
  loading.value = false;

  if (res.success) {
    successMsg.value = 'Activation successful! Redirecting...';
    setTimeout(() => {
      window.location.href = '/dashboard';
    }, 1500);
  } else {
    error.value = res.message;
  }
};

const isCheckingStatus = ref(false);
const checkRefreshStatus = async () => {
  isCheckingStatus.value = true;
  await Promise.all([
    licenseStore.checkLicenseStatus(),
    new Promise(resolve => setTimeout(resolve, 1000))
  ]);

  if (licenseStore.isLicenseActive) {
    router.push('/');
  }
  isCheckingStatus.value = false;
};
</script>
