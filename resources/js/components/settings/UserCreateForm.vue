<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200" style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);">
      <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-4xl shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 overflow-hidden transition-all duration-300 z-10 max-h-[90vh] overflow-y-auto my-auto">
          <!-- Header -->
          <div class="bg-slate-900 dark:bg-zinc-950 px-6 py-4 border-b border-slate-800 dark:border-zinc-800">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                  <div class="w-10 h-10 bg-white/10 dark:bg-white/10 rounded-xl flex items-center justify-center border border-white/10">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                </div>
                <div>
                  <h3 class="text-lg font-bold text-white">{{ isEdit ? 'Edit User' : 'Add New User' }}</h3>
                  <p class="text-slate-400 text-xs font-medium">{{ isEdit ? 'Update user information' : 'Create a new user profile' }}</p>
                </div>
              </div>
              <button @click="$emit('close')" class="text-slate-400 hover:text-white transition-colors p-1 rounded-lg hover:bg-white/10 cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Content -->
          <div class="max-h-[75vh] overflow-y-auto">
            <form novalidate @submit.prevent="saveUser" class="p-6">
              <div class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                  <!-- Basic Information -->
                  <div class="space-y-4">
                    <h4 class="text-base font-bold text-slate-900 dark:text-zinc-100 border-b border-slate-200 dark:border-zinc-800 pb-2">Basic Information</h4>
                    
                    <div>
                      <label for="name" class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1">Name *</label>
                      <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="block w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800/80 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-medium text-slate-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 shadow-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        :class="{ 'border-red-400 focus:ring-red-500 focus:border-red-500': errors.name }"
                        required
                      />
                      <p v-if="errors.name" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ errors.name[0] }}</p>
                    </div>

                    <div>
                      <label for="email" class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1">Email *</label>
                      <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="block w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800/80 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-medium text-slate-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 shadow-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        :class="{ 'border-red-400 focus:ring-red-500 focus:border-red-500': errors.email }"
                        required
                      />
                      <p v-if="errors.email" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ errors.email[0] }}</p>
                    </div>

                    <div>
                      <label for="phone" class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1">Phone</label>
                      <input
                        id="phone"
                        v-model="form.phone"
                        type="text"
                        class="block w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800/80 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-medium text-slate-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 shadow-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        :class="{ 'border-red-400 focus:ring-red-500 focus:border-red-500': errors.phone }"
                      />
                      <p v-if="errors.phone" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ errors.phone[0] }}</p>
                    </div>

                    <div class="space-y-4">
                      <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1">
                          {{ isEdit ? 'New Password' : 'Password *' }}
                        </label>
                        <div class="relative">
                          <input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            class="block w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800/80 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-medium text-slate-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 shadow-xs focus:ring-2 focus:ring-indigo-500 pr-10"
                            :class="{ 'border-red-400': errors.password }"
                            placeholder="••••••••"
                          />
                          <button 
                            type="button" 
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 cursor-pointer"
                            tabindex="-1"
                            title="Toggle password visibility"
                          >
                            <svg v-if="showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.076m1.406-1.407A10.014 10.014 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.059 10.059 0 01-4.293 5.774M6.228 6.228L17.772 17.772M9 9l6 6" />
                            </svg>
                          </button>
                        </div>
                        <p v-if="isEdit" class="mt-1 text-[10px] font-medium text-slate-400 dark:text-zinc-500">Leave blank to keep existing password</p>
                        <p v-if="errors.password" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ errors.password[0] }}</p>
                      </div>

                      <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1">
                          {{ isEdit ? 'Confirm New Password' : 'Confirm Password *' }}
                        </label>
                        <div class="relative">
                          <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :type="showConfirmPassword ? 'text' : 'password'"
                            class="block w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800/80 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-medium text-slate-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 shadow-xs focus:ring-2 focus:ring-indigo-500 pr-10"
                            :class="{ 'border-red-400': errors.password_confirmation }"
                            placeholder="••••••••"
                          />
                          <button 
                            type="button" 
                            @click="showConfirmPassword = !showConfirmPassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 cursor-pointer"
                            tabindex="-1"
                            title="Toggle password visibility"
                          >
                            <svg v-if="showConfirmPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.076m1.406-1.407A10.014 10.014 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.059 10.059 0 01-4.293 5.774M6.228 6.228L17.772 17.772M9 9l6 6" />
                            </svg>
                          </button>
                        </div>
                        <p v-if="errors.password_confirmation" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ errors.password_confirmation[0] }}</p>
                      </div>
                    </div>
                  </div>

                  <!-- Account Settings -->
                  <div class="space-y-4">
                    <h4 class="text-base font-bold text-slate-900 dark:text-zinc-100 border-b border-slate-200 dark:border-zinc-800 pb-2">Account Settings</h4>
                    
                    <!-- Profile Photo Drag & Drop Upload Zone -->
                    <div>
                      <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1.5">Profile Photo</label>
                      <div 
                        @dragover.prevent="isDragging = true" 
                        @dragleave.prevent="isDragging = false" 
                        @drop.prevent="handleDrop"
                        :class="[
                          'relative flex items-center gap-4 p-3.5 rounded-2xl border-2 border-dashed transition-all duration-200',
                          isDragging ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20' : 'border-slate-200 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-800/40'
                        ]"
                      >
                        <div class="flex-shrink-0 relative">
                          <div class="w-14 h-14 rounded-2xl overflow-hidden bg-slate-200 dark:bg-zinc-800 flex items-center justify-center border border-slate-300 dark:border-zinc-700 shadow-sm">
                            <img v-if="photoPreview" :src="photoPreview" alt="Avatar preview" class="w-full h-full object-cover" />
                            <svg v-else class="w-7 h-7 text-slate-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                          </div>
                        </div>

                        <div class="flex-1 min-w-0">
                          <p class="text-xs font-bold text-slate-800 dark:text-zinc-200">Drag & drop photo here</p>
                          <p class="text-[10px] text-slate-500 dark:text-zinc-400 mt-0.5">JPEG, PNG, GIF up to 10MB</p>
                          <div class="flex items-center gap-2 mt-2">
                            <label class="px-3 py-1 bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-semibold text-[10px] rounded-lg cursor-pointer hover:opacity-90 transition-opacity">
                              Browse File
                              <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handleFileSelect" />
                            </label>
                            <button v-if="photoPreview || selectedFile" type="button" @click="clearPhoto" class="px-2.5 py-1 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 font-semibold text-[10px] rounded-lg hover:bg-rose-100 transition-colors cursor-pointer">
                              Remove
                            </button>
                          </div>
                        </div>
                      </div>
                      <p v-if="errors.profile_image || errors.avatar" class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ errors.profile_image?.[0] || errors.avatar?.[0] }}</p>
                    </div>

                    <div>
                      <label for="company_id" class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1">Assign Company *</label>
                      <select
                        id="company_id"
                        v-model="form.company_id"
                        class="block w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800/80 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-zinc-100 shadow-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        required
                      >
                        <option value="" disabled>Select Company</option>
                        <option v-for="comp in companiesList" :key="comp.id" :value="comp.id">
                          {{ comp.company_name || comp.name }}
                        </option>
                      </select>
                      <p v-if="errors.company_id" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ errors.company_id[0] }}</p>
                    </div>

                    <div>
                      <label for="role" class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1">Role *</label>
                      <select
                        id="role"
                        v-model="form.role"
                        class="block w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800/80 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-zinc-100 shadow-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        required
                      >
                        <option v-for="role in rolesList" :key="role.id" :value="role.name">
                          {{ role.name }}
                        </option>
                      </select>
                    </div>

                    <div>
                      <label for="is_active" class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1">Status</label>
                      <select
                        id="is_active"
                        v-model="form.is_active"
                        class="block w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800/80 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-zinc-100 shadow-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                      >
                        <option :value="true">Active</option>
                        <option :value="false">Inactive</option>
                      </select>
                      <p v-if="errors.is_active" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ errors.is_active[0] }}</p>
                    </div>

                    <div>
                      <label for="address" class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1">Address</label>
                      <textarea
                        id="address"
                        v-model="form.address"
                        rows="3"
                        class="block w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800/80 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-medium text-slate-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 shadow-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                      ></textarea>
                      <p v-if="errors.address" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ errors.address[0] }}</p>
                    </div>
                  </div>
                </div>

                <!-- Notes -->
                <div>
                  <label for="notes" class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1">Notes</label>
                  <textarea
                    id="notes"
                    v-model="form.notes"
                    rows="3"
                    class="block w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800/80 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-medium text-slate-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 shadow-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Internal notes about this user..."
                  ></textarea>
                  <p v-if="errors.notes" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ errors.notes[0] }}</p>
                </div>
              </div>
              
              <!-- Footer -->
              <div class="flex justify-end space-x-3 pt-6 border-t border-slate-200 dark:border-zinc-800 mt-6">
                <button 
                  type="button" 
                  @click="$emit('close')" 
                  class="px-4 py-2 text-xs font-semibold text-slate-700 dark:text-zinc-300 bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors cursor-pointer"
                >
                  Cancel
                </button>
                <button 
                  type="submit" 
                  :disabled="saving" 
                  class="inline-flex items-center px-5 py-2 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 rounded-xl shadow-xs transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                >
                  <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ saving ? 'Saving...' : (isEdit ? 'Update User' : 'Create User') }}
                </button>
              </div>
            </form>
          </div>
        </div>
    </div>
  </Teleport>
</template>

<script>
import { ref, reactive, watch } from 'vue';
import { useToast } from '@/composables/useToast';
import axios from 'axios';

export default {
  name: 'UserCreateForm',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    user: {
      type: Object,
      default: null
    },
    isEdit: {
      type: Boolean,
      default: false
    },
    rolesList: {
      type: Array,
      default: () => []
    }
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const { showToast } = useToast();

    const saving = ref(false);
    const errors = ref({});
    const fileInput = ref(null);
    const selectedFile = ref(null);
    const photoPreview = ref(null);
    const isDragging = ref(false);
    const companiesList = ref([]);

    const form = reactive({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      role: 'user',
      company_id: '',
      phone: '',
      address: '',
      notes: '',
      is_active: true
    });
    
    const showPassword = ref(false);
    const showConfirmPassword = ref(false);

    const fetchCompanies = async () => {
      try {
        const response = await axios.get('/api/companies/my-companies');
        companiesList.value = response.data.companies || [];
        if (!form.company_id && companiesList.value.length > 0) {
          form.company_id = response.data.active_company_id || companiesList.value[0].id;
        }
      } catch (error) {
        console.error('Error fetching companies:', error);
      }
    };

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

    const resetForm = () => {
      Object.keys(form).forEach(key => {
        if (key === 'is_active') {
          form[key] = true;
        } else if (key === 'role') {
          form[key] = props.rolesList.find(r => r.name === 'user') ? 'user' : (props.rolesList[0]?.name || '');
        } else if (key === 'company_id') {
          form[key] = companiesList.value.length > 0 ? companiesList.value[0].id : '';
        } else {
          form[key] = '';
        }
      });
      clearPhoto();
      errors.value = {};
    };

    const loadUserData = () => {
      if (props.user && props.isEdit) {
        Object.keys(form).forEach(key => {
          if (props.user[key] !== undefined && props.user[key] !== null) {
            form[key] = props.user[key];
          }
        });

        if (props.user.current_company_id) {
          form.company_id = props.user.current_company_id;
        }
        
        // Load role
        if (props.user.roles && props.user.roles.length > 0) {
          form.role = props.user.roles[0].name;
        } else if (props.user.role_name) {
          form.role = props.user.role_name;
        }

        // Load avatar preview
        if (props.user.avatar_url) {
          photoPreview.value = props.user.avatar_url;
        } else if (props.user.profile_image) {
          photoPreview.value = props.user.profile_image.startsWith('http') ? props.user.profile_image : `/storage/${props.user.profile_image}`;
        }
      }
    };

    const validateUserForm = () => {
      const errs = {};

      if (!form.name || !form.name.trim()) {
        errs.name = ['Full name is required.'];
      }
      if (!form.email || !form.email.trim()) {
        errs.email = ['Email address is required.'];
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.trim())) {
        errs.email = ['Please enter a valid email address.'];
      }
      if (!form.company_id) {
        errs.company_id = ['Company assignment is required.'];
      }
      if (!form.role) {
        errs.role = ['Role is required.'];
      }

      if (!props.isEdit || form.password) {
        if (!form.password) {
          errs.password = ['Password is required.'];
        } else if (form.password.length < 8) {
          errs.password = ['Password must be at least 8 characters.'];
        }
        if (form.password !== form.password_confirmation) {
          errs.password_confirmation = ['Password confirmation does not match.'];
        }
      }

      return errs;
    };

    const saveUser = async () => {
      errors.value = {};

      const validationErrors = validateUserForm();
      if (Object.keys(validationErrors).length > 0) {
        errors.value = validationErrors;
        const firstMsg = Object.values(validationErrors)[0]?.[0] || 'Please fill in all required fields.';
        showToast(firstMsg, 'error');
        return;
      }

      saving.value = true;

      try {
        const formData = new FormData();
        Object.keys(form).forEach(key => {
          if (props.isEdit && key === 'password' && !form[key]) {
            return;
          }
          if (form[key] !== null && form[key] !== undefined) {
            formData.append(key, form[key]);
          }
        });

        if (selectedFile.value) {
          formData.append('profile_image', selectedFile.value);
        }

        if (props.isEdit) {
          formData.append('_method', 'PUT');
          await axios.post(`/api/users/${props.user.id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
          });
        } else {
          await axios.post('/api/users', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
          });
        }

        showToast(
          props.isEdit ? 'User updated successfully' : 'User created successfully',
          'success'
        );

        emit('saved');
        emit('close');
      } catch (error) {
        if (error.response?.status === 422) {
          errors.value = error.response.data.errors || {};
          const firstBackendError = Object.values(errors.value)[0]?.[0] || 'Validation failed. Please check required fields.';
          showToast(firstBackendError, 'error');
        } else {
          showToast(error.response?.data?.message || 'Error saving user', 'error');
        }
      } finally {
        saving.value = false;
      }
    };

    watch(() => props.show, async (newVal) => {
      if (newVal) {
        await fetchCompanies();
        resetForm();
        loadUserData();
      }
    });

    return {
      form,
      errors,
      saving,
      fileInput,
      selectedFile,
      photoPreview,
      isDragging,
      companiesList,
      showPassword,
      showConfirmPassword,
      handleFileSelect,
      handleDrop,
      clearPhoto,
      saveUser
    };
  }
};
</script>

<style scoped>
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>
