<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200" style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);">
      <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-2xl shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 text-left transition-all duration-300 flex flex-col max-h-[90vh] overflow-y-auto my-auto z-10" @click.stop>
        
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
            {{ isEdit ? 'Edit Manager / Admin' : 'Add New Manager / Admin' }}
          </h3>
        </div>

        <!-- Tab Navigation (Clean text tabs, matching Customer, Supplier & Employee Modals) -->
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
            :class="['px-4 py-2 font-bold rounded-t-lg transition-all focus:outline-none border-b-2 cursor-pointer', activeTab === 'account' ? 'text-indigo-600 dark:text-indigo-400 border-indigo-600 bg-white dark:bg-zinc-900' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300 border-transparent']"
            @click="activeTab = 'account'"
          >
            Role & Access
          </button>
          <button
            type="button"
            :class="['px-4 py-2 font-bold rounded-t-lg transition-all focus:outline-none border-b-2 cursor-pointer', activeTab === 'details' ? 'text-indigo-600 dark:text-indigo-400 border-indigo-600 bg-white dark:bg-zinc-900' : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300 border-transparent']"
            @click="activeTab = 'details'"
          >
            Address & Notes
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
        <form @submit.prevent="saveSubAdmin" class="flex flex-col flex-1 min-h-0">
          <div class="flex-1 overflow-y-auto p-6 space-y-4 pr-4 custom-scrollbar">
            
            <!-- Tab 1: Basic Information -->
            <div v-if="activeTab === 'basic'" class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Full Name *</label>
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    placeholder="e.g. Alex Morgan"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.name }"
                  />
                  <p v-if="errors.name" class="mt-1 text-[10px] text-red-500">{{ errors.name[0] }}</p>
                </div>

                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Email Address *</label>
                  <input
                    v-model="form.email"
                    type="email"
                    required
                    placeholder="e.g. manager@example.com"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                    :class="{ 'border-red-300 dark:border-red-700': errors.email }"
                  />
                  <p v-if="errors.email" class="mt-1 text-[10px] text-red-500">{{ errors.email[0] }}</p>
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

              <div v-if="!isEdit" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Password *</label>
                  <div class="relative">
                    <input
                      v-model="form.password"
                      :type="showPassword ? 'text' : 'password'"
                      required
                      placeholder="Password"
                      class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all pr-8"
                      :class="{ 'border-red-300 dark:border-red-700': errors.password }"
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
                  <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Confirm Password *</label>
                  <div class="relative">
                    <input
                      v-model="form.password_confirmation"
                      :type="showConfirmPassword ? 'text' : 'password'"
                      required
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
                </div>
              </div>
            </div>

            <!-- Tab 2: Role & Access -->
            <div v-if="activeTab === 'account'" class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <CustomFloatingSelect
                    label="System Role"
                    v-model="form.role"
                    :options="[
                      { label: 'Manager', value: 'manager' },
                      { label: 'Sub-Admin', value: 'sub-admin' },
                      { label: 'Admin', value: 'admin' },
                      { label: 'Cashier', value: 'cashier' },
                      { label: 'Employee', value: 'employee' },
                      { label: 'User', value: 'user' }
                    ]"
                  />
                </div>

                <div>
                  <CustomFloatingSelect
                    label="Account Status"
                    v-model="form.is_active"
                    :options="[
                      { label: 'Active', value: true },
                      { label: 'Inactive', value: false }
                    ]"
                  />
                  <p v-if="errors.is_active" class="mt-1 text-[10px] text-red-500">{{ errors.is_active[0] }}</p>
                </div>
              </div>
            </div>

            <!-- Tab 3: Address & Notes -->
            <div v-if="activeTab === 'details'" class="space-y-4">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Street Address</label>
                <textarea
                  v-model="form.address"
                  rows="2"
                  placeholder="Enter address details..."
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                  :class="{ 'border-red-300 dark:border-red-700': errors.address }"
                ></textarea>
                <p v-if="errors.address" class="mt-1 text-[10px] text-red-500">{{ errors.address[0] }}</p>
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Internal Notes</label>
                <textarea
                  v-model="form.notes"
                  rows="3"
                  placeholder="Internal notes about this user..."
                  class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 text-xs text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-950 transition-all"
                  :class="{ 'border-red-300 dark:border-red-700': errors.notes }"
                ></textarea>
                <p v-if="errors.notes" class="mt-1 text-[10px] text-red-500">{{ errors.notes[0] }}</p>
              </div>
            </div>

            <!-- Tab 4: Media Information -->
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
              {{ saving ? 'Saving...' : (isEdit ? 'Update Admin' : 'Create Admin') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script>
import { ref, reactive, watch } from 'vue';
import { useToast } from '@/composables/useToast';
import CustomPhoneInput from '@/components/common/CustomPhoneInput.vue';
import CustomFloatingSelect from '@/components/common/CustomFloatingSelect.vue';
import api from '@/services/api';

export default {
  name: 'SubAdminCreateForm',
  components: {
    CustomPhoneInput,
    CustomFloatingSelect
  },
  props: {
    show: {
      type: Boolean,
      default: false
    },
    subAdmin: {
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

    const form = reactive({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      role: 'manager',
      phone: '',
      mobile: '',
      address: '',
      notes: '',
      is_active: true
    });
    const showPassword = ref(false);
    const showConfirmPassword = ref(false);

    const resetForm = () => {
      activeTab.value = 'basic';
      Object.keys(form).forEach(key => {
        if (key === 'is_active') {
          form[key] = true;
        } else if (key === 'role') {
          form[key] = 'manager';
        } else {
          form[key] = '';
        }
      });
      errors.value = {};
      photoFile.value = null;
      photoPreview.value = null;
      attachmentFiles.value = [];
      existingAttachments.value = [];
    };

    const loadSubAdminData = () => {
      if (props.subAdmin && props.isEdit) {
        Object.keys(form).forEach(key => {
          if (props.subAdmin[key] !== undefined) {
            form[key] = props.subAdmin[key];
          }
        });
        
        // Load role from Spatie relationship if available
        if (props.subAdmin.roles && props.subAdmin.roles.length > 0) {
          form.role = props.subAdmin.roles[0].name;
        } else if (props.subAdmin.role_name) {
          form.role = props.subAdmin.role_name;
        }

        if (props.subAdmin.profile_image) {
          photoPreview.value = props.subAdmin.profile_image.startsWith('http') ? props.subAdmin.profile_image : `/storage/${props.subAdmin.profile_image}`;
        }

        if (props.subAdmin.attachments_urls && Array.isArray(props.subAdmin.attachments_urls)) {
          existingAttachments.value = [...props.subAdmin.attachments_urls];
        } else {
          existingAttachments.value = [];
        }
      }
    };

    const saveSubAdmin = async () => {
      saving.value = true;
      errors.value = {};

      try {
        const formData = new FormData();
        Object.keys(form).forEach(key => {
          if (typeof form[key] === 'boolean') {
            formData.append(key, form[key] ? '1' : '0');
          } else if (key === 'password' || key === 'password_confirmation') {
            if (form[key]) formData.append(key, form[key]);
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
        if (props.isEdit && props.subAdmin?.id) {
          formData.append('_method', 'PUT');
          response = await api.post(`/sub-admins/${props.subAdmin.id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
          });
        } else {
          response = await api.post('/sub-admins', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
          });
        }

        showToast(
          props.isEdit ? 'Admin updated successfully' : 'Admin created successfully',
          'success'
        );

        emit('saved', response.data.user || response.data);
        emit('close');
      } catch (error) {
        if (error.response?.status === 422) {
          errors.value = error.response.data.errors;
          const firstKey = Object.keys(errors.value)[0];
          if (['name', 'email', 'phone', 'mobile', 'password'].includes(firstKey)) {
            activeTab.value = 'basic';
          } else if (['role', 'is_active'].includes(firstKey)) {
            activeTab.value = 'account';
          } else {
            activeTab.value = 'details';
          }
        } else {
          showToast(error.response?.data?.message || 'Error saving admin', 'error');
        }
      } finally {
        saving.value = false;
      }
    };

    watch(() => props.show, (newVal) => {
      if (newVal) {
        resetForm();
        loadSubAdminData();
      }
    });

    return {
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
      form,
      errors,
      saving,
      showPassword,
      showConfirmPassword,
      saveSubAdmin
    };
  }
};
</script>
