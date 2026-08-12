<template>
  <Teleport to="body">
    <div 
      class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6 overflow-y-auto bg-slate-900/50 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200 select-none" 
      style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);" 
      @click.self="$emit('close')"
    >
      <!-- Main Modal Card Container -->
      <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-4xl shadow-2xl rounded-3xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 p-6 sm:p-8 transition-all duration-300 my-auto max-h-[90vh] overflow-y-auto flex flex-col gap-6">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-6 border-b border-slate-100 dark:border-zinc-800">
          <div class="flex items-center gap-4">
            <div 
              @click="openImagePreview"
              class="flex-shrink-0 h-16 w-16 relative rounded-full overflow-hidden border-2 border-indigo-500/20 dark:border-indigo-400/30 shadow-xs flex items-center justify-center bg-slate-50 dark:bg-zinc-800 cursor-pointer hover:opacity-90 hover:scale-105 hover:ring-4 hover:ring-indigo-500/20 transition-all"
              title="Click to preview full image"
            >
              <img 
                v-if="employee.avatar_url || employee.profile_image" 
                :src="employee.avatar_url || (employee.profile_image.startsWith('http') ? employee.profile_image : `/storage/${employee.profile_image}`)" 
                :alt="employee.full_name"
                class="h-16 w-16 rounded-full object-cover"
              />
              <div v-else-if="employee.gender === 'female'" class="w-full h-full bg-gradient-to-br from-pink-100 to-rose-200 dark:from-rose-950 dark:to-pink-900 flex items-center justify-center text-rose-600 dark:text-rose-300">
                <svg class="w-10 h-10" viewBox="0 0 64 64" fill="currentColor"><path d="M32 12c-5.523 0-10 4.477-10 10 0 3.75 2.07 7.02 5.14 8.74C21.84 32.8 17.5 37.85 17.5 44h29c0-6.15-4.34-11.2-9.64-13.26A9.97 9.97 0 0042 22c0-5.523-4.477-10-10-10z"/></svg>
              </div>
              <div v-else class="w-full h-full bg-gradient-to-br from-blue-100 to-indigo-200 dark:from-indigo-950 dark:to-blue-900 flex items-center justify-center text-indigo-600 dark:text-indigo-300">
                <svg class="w-10 h-10" viewBox="0 0 64 64" fill="currentColor"><path d="M32 12c-5.523 0-10 4.477-10 10 0 3.75 2.07 7.02 5.14 8.74C21.84 32.8 17.5 37.85 17.5 44h29c0-6.15-4.34-11.2-9.64-13.26A9.97 9.97 0 0042 22c0-5.523-4.477-10-10-10z"/></svg>
              </div>
            </div>
            <div>
              <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ employee.full_name }}</h3>
              <div class="flex items-center gap-2 mt-1">
                <span class="text-xs font-mono font-semibold text-slate-500 dark:text-slate-400">#{{ employee.employee_number }}</span>
                <span :class="getStatusClass(employee.employment_status)" class="px-2.5 py-0.5 inline-flex text-[10px] font-extrabold rounded-full uppercase tracking-wider">
                  {{ getStatusText(employee.employment_status) }}
                </span>
              </div>
            </div>
          </div>
          <button @click="$emit('close')" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-xl transition-colors cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- 2-Column Responsive Information Layout -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Personal Information Card -->
          <div class="bg-slate-50 dark:bg-zinc-800/40 p-5 rounded-2xl border border-slate-200/80 dark:border-zinc-800 space-y-4">
            <h4 class="text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider border-b border-slate-200/60 dark:border-zinc-700/60 pb-2 flex items-center gap-2">
              <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              Personal Information
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div class="sm:col-span-2">
                <label class="block text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Email</label>
                <p class="text-xs font-semibold text-slate-900 dark:text-slate-100 truncate">{{ employee.email || '-' }}</p>
              </div>
              <div v-if="employee.phone">
                <label class="block text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Phone</label>
                <p class="text-xs font-semibold text-slate-900 dark:text-slate-100">{{ employee.phone }}</p>
              </div>
              <div v-if="employee.mobile">
                <label class="block text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Mobile</label>
                <p class="text-xs font-semibold text-slate-900 dark:text-slate-100">{{ employee.mobile }}</p>
              </div>
              <div v-if="employee.date_of_birth">
                <label class="block text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Date of Birth</label>
                <p class="text-xs font-mono font-semibold text-slate-900 dark:text-slate-100">{{ formatDate(employee.date_of_birth) }}</p>
              </div>
              <div v-if="employee.gender">
                <label class="block text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Gender</label>
                <p class="text-xs font-semibold text-slate-900 dark:text-slate-100">{{ capitalizeFirst(employee.gender) }}</p>
              </div>
              <div v-if="employee.marital_status">
                <label class="block text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Marital Status</label>
                <p class="text-xs font-semibold text-slate-900 dark:text-slate-100">{{ capitalizeFirst(employee.marital_status) }}</p>
              </div>
            </div>
          </div>

          <!-- Employment Information Card -->
          <div class="bg-slate-50 dark:bg-zinc-800/40 p-5 rounded-2xl border border-slate-200/80 dark:border-zinc-800 space-y-4">
            <h4 class="text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider border-b border-slate-200/60 dark:border-zinc-700/60 pb-2 flex items-center gap-2">
              <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              Employment Information
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Department</label>
                <p class="text-xs font-semibold text-slate-900 dark:text-slate-100">{{ employee.department?.name || '-' }}</p>
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Position</label>
                <p class="text-xs font-semibold text-slate-900 dark:text-slate-100">{{ employee.position?.title || '-' }}</p>
              </div>
              <div v-if="employee.manager">
                <label class="block text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Manager</label>
                <p class="text-xs font-semibold text-slate-900 dark:text-slate-100">{{ employee.manager.full_name }}</p>
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Hire Date</label>
                <p class="text-xs font-mono font-semibold text-slate-900 dark:text-slate-100">{{ formatDate(employee.hire_date) }}</p>
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Employment Type</label>
                <p class="text-xs font-semibold text-slate-900 dark:text-slate-100">{{ getEmploymentTypeText(employee.employment_type) }}</p>
              </div>
              <div v-if="employee.probation_end_date">
                <label class="block text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Probation End</label>
                <p class="text-xs font-mono font-semibold text-slate-900 dark:text-slate-100">{{ formatDate(employee.probation_end_date) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Salary Information Card -->
        <div class="bg-slate-50 dark:bg-zinc-800/40 p-5 rounded-2xl border border-slate-200/80 dark:border-zinc-800">
          <h4 class="text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Salary Information
          </h4>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Basic Salary</label>
              <p class="text-sm font-mono font-bold text-emerald-600 dark:text-emerald-400">${{ parseFloat(employee.basic_salary).toFixed(2) }}</p>
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Salary Type</label>
              <p class="text-xs font-semibold text-slate-900 dark:text-slate-100">{{ capitalizeFirst(employee.salary_type) }}</p>
            </div>
            <div v-if="employee.hourly_rate">
              <label class="block text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Hourly Rate</label>
              <p class="text-xs font-mono font-bold text-slate-900 dark:text-slate-100">${{ parseFloat(employee.hourly_rate).toFixed(2) }}</p>
            </div>
          </div>
        </div>

        <!-- Emergency Contact Card (If present) -->
        <div v-if="employee.emergency_contact_name" class="bg-slate-50 dark:bg-zinc-800/40 p-5 rounded-2xl border border-slate-200/80 dark:border-zinc-800">
          <h4 class="text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Emergency Contact
          </h4>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Name</label>
              <p class="text-xs font-semibold text-slate-900 dark:text-slate-100">{{ employee.emergency_contact_name }}</p>
            </div>
            <div v-if="employee.emergency_contact_relationship">
              <label class="block text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Relationship</label>
              <p class="text-xs font-semibold text-slate-900 dark:text-slate-100">{{ employee.emergency_contact_relationship }}</p>
            </div>
            <div v-if="employee.emergency_contact_phone">
              <label class="block text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Phone</label>
              <p class="text-xs font-semibold text-slate-900 dark:text-slate-100">{{ employee.emergency_contact_phone }}</p>
            </div>
            <div v-if="employee.emergency_contact_email">
              <label class="block text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Email</label>
              <p class="text-xs font-semibold text-slate-900 dark:text-slate-100 truncate">{{ employee.emergency_contact_email }}</p>
            </div>
          </div>
        </div>

        <!-- Notes Section (If present) -->
        <div v-if="employee.notes" class="bg-slate-50 dark:bg-zinc-800/40 p-5 rounded-2xl border border-slate-200/80 dark:border-zinc-800">
          <label class="block text-[11px] font-extrabold text-slate-900 dark:text-white uppercase tracking-wider mb-2">Notes</label>
          <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">{{ employee.notes }}</p>
        </div>

        <!-- User Account Card -->
        <div v-if="canEdit" class="bg-slate-50 dark:bg-zinc-800/40 p-5 rounded-2xl border border-slate-200/80 dark:border-zinc-800">
          <h4 class="text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            User Account
          </h4>
          <div v-if="employee.user" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
              <p class="text-xs text-slate-600 dark:text-slate-300">
                <span class="font-bold">Status:</span>
                <span class="text-emerald-600 dark:text-emerald-400 font-bold ml-1">Has User Account</span>
              </p>
              <p class="text-xs text-slate-600 dark:text-slate-300">
                <span class="font-bold">Login Email:</span> {{ employee.user.email }}
              </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <button
                @click="syncUserAccount"
                class="px-3.5 py-2 bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-bold text-xs rounded-xl hover:opacity-90 transition-opacity cursor-pointer shadow-xs"
              >
                Sync Account
              </button>
              <button
                @click="resetPassword"
                class="px-3.5 py-2 bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-bold text-xs rounded-xl hover:opacity-90 transition-opacity cursor-pointer shadow-xs"
              >
                Reset Password
              </button>
              <button
                @click="deactivateUserAccount"
                class="px-3.5 py-2 bg-rose-600 text-white font-bold text-xs rounded-xl hover:bg-rose-700 transition-colors cursor-pointer shadow-xs"
              >
                Deactivate Account
              </button>
            </div>
          </div>
          <div v-else class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
              <p class="text-xs text-slate-600 dark:text-slate-300">
                <span class="font-bold">Status:</span>
                <span class="text-rose-500 font-bold ml-1">No User Account</span>
              </p>
              <p class="text-xs text-slate-500 dark:text-zinc-400">User account can be created for system login access.</p>
            </div>
            <button
              @click="createUserAccount"
              class="px-4 py-2 bg-indigo-600 text-white font-bold text-xs rounded-xl hover:bg-indigo-700 transition-colors cursor-pointer shadow-xs"
            >
              Create User Account
            </button>
          </div>
        </div>

        <!-- Footer Actions Bar -->
        <div class="flex items-center justify-between pt-6 border-t border-slate-100 dark:border-zinc-800">
          <div class="flex items-center gap-2 sm:gap-3">
            <button
              v-if="canEdit"
              @click="$emit('edit')"
              class="bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-bold px-4 py-2.5 rounded-xl text-xs transition-all cursor-pointer shadow-xs"
            >
              Edit
            </button>
            <button
              v-if="canTerminate"
              @click="showTerminationModal = true"
              class="px-4 py-2.5 bg-rose-600 text-white font-bold rounded-xl text-xs hover:bg-rose-700 cursor-pointer transition-all shadow-xs"
            >
              Terminate
            </button>
            <button
              v-if="canReactivate"
              @click="reactivateEmployee"
              class="bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-bold px-4 py-2.5 rounded-xl text-xs transition-all cursor-pointer shadow-xs"
            >
              Reactivate
            </button>
          </div>

          <button
            @click="$emit('close')"
            class="px-5 py-2.5 border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-xl hover:bg-slate-50 dark:hover:bg-zinc-700/80 transition-all cursor-pointer shadow-xs"
          >
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- Termination Modal Overlay -->
    <div v-if="showTerminationModal" class="fixed inset-0 z-[100000] flex items-center justify-center p-4 bg-slate-900/60 dark:bg-zinc-950/80 backdrop-blur-md" style="backdrop-filter: blur(6px);">
      <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-md shadow-2xl rounded-3xl bg-white dark:bg-zinc-900 p-6 text-slate-800 dark:text-slate-100">
        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Terminate Employee</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Termination Date *</label>
            <input
              v-model="terminationData.termination_date"
              type="date"
              required
              class="w-full px-3 py-2 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs bg-white dark:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Termination Reason *</label>
            <textarea
              v-model="terminationData.termination_reason"
              rows="3"
              required
              class="w-full px-3 py-2 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs bg-white dark:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              placeholder="Please provide a reason for termination..."
            ></textarea>
          </div>
        </div>
        <div class="flex justify-end gap-3 mt-6">
          <button
            @click="showTerminationModal = false"
            class="px-4 py-2 border border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-xl hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
          >
            Cancel
          </button>
          <button
            @click="terminateEmployee"
            :disabled="!terminationData.termination_reason.trim() || !terminationData.termination_date"
            class="px-4 py-2 bg-rose-600 text-white font-bold text-xs rounded-xl hover:bg-rose-700 disabled:opacity-50 transition-colors cursor-pointer"
          >
            Terminate
          </button>
        </div>
      </div>
    </div>

    <!-- Image Preview Lightbox Modal -->
    <ImagePreviewModal
      :show="showImagePreview"
      :image-url="getEmployeeAvatarUrl"
      :title="employee?.full_name || 'Employee Profile'"
      :subtitle="`${employee?.employee_number ? '#' + employee.employee_number + ' • ' : ''}${employee?.position?.title || 'Employee'}`"
      @close="showImagePreview = false"
    />
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import ImagePreviewModal from '@/components/common/ImagePreviewModal.vue';
import axios from 'axios';

const authStore = useAuthStore();
const showImagePreview = ref(false);

const openImagePreview = () => {
  showImagePreview.value = true;
};

const getEmployeeAvatarUrl = computed(() => {
  if (!props.employee) return '';
  if (props.employee.avatar_url) return props.employee.avatar_url;
  if (props.employee.profile_image) {
    return props.employee.profile_image.startsWith('http') ? props.employee.profile_image : `/storage/${props.employee.profile_image}`;
  }
  return '';
});

// Props and Emits
const props = defineProps({
  employee: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'edit', 'terminate', 'reactivate']);

// Reactive data
const showTerminationModal = ref(false);
const terminationData = ref({
  termination_date: new Date().toISOString().split('T')[0],
  termination_reason: ''
});

// Computed
const canEdit = computed(() => authStore.hasPermission('employees.edit'));
const canTerminate = computed(() => {
  return authStore.hasPermission('employees.edit') && 
         props.employee.employment_status === 'active' && 
         props.employee.is_active;
});
const canReactivate = computed(() => {
  return authStore.hasPermission('employees.edit') && 
         (props.employee.employment_status === 'terminated' || !props.employee.is_active);
});

// Methods
const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const getStatusClass = (status) => {
  const classes = {
    active: 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 border border-slate-900 dark:border-white font-bold',
    inactive: 'bg-gray-100 text-gray-800',
    terminated: 'bg-red-100 text-red-800',
    on_leave: 'bg-yellow-100 text-yellow-800'
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusText = (status) => {
  const texts = {
    active: 'Active',
    inactive: 'Inactive',
    terminated: 'Terminated',
    on_leave: 'On Leave'
  };
  return texts[status] || status;
};

const getEmploymentTypeText = (type) => {
  const texts = {
    full_time: 'Full Time',
    part_time: 'Part Time',
    contract: 'Contract',
    intern: 'Intern'
  };
  return texts[type] || type;
};

const capitalizeFirst = (str) => {
  return str.charAt(0).toUpperCase() + str.slice(1).replace('_', ' ');
};

const getInitials = (firstName, lastName) => {
  return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase();
};

const terminateEmployee = async () => {
  try {
    await axios.post(`/api/employees/${props.employee.id}/terminate`, terminationData.value);
    showTerminationModal.value = false;
    emit('terminate');
  } catch (error) {
    console.error('Error terminating employee:', error);
    if (error.response?.data?.message) {
      alert(error.response.data.message);
    }
  }
};

const reactivateEmployee = async () => {
  if (!confirm('Are you sure you want to reactivate this employee?')) {
    return;
  }

  try {
    await axios.post(`/api/employees/${props.employee.id}/reactivate`);
    emit('reactivate');
  } catch (error) {
    console.error('Error reactivating employee:', error);
    if (error.response?.data?.message) {
      alert(error.response.data.message);
    }
  }
};

// User Account Management Methods
const createUserAccount = async () => {
  if (!confirm(`Create user account for ${props.employee.full_name}?`)) {
    return;
  }

  try {
    await axios.post(`/api/employees/${props.employee.id}/create-user-account`);
    alert('User account created successfully!');
    emit('reactivate'); // Refresh the employee data
  } catch (error) {
    console.error('Error creating user account:', error);
    if (error.response?.data?.message) {
      alert(error.response.data.message);
    }
  }
};

const syncUserAccount = async () => {
  try {
    await axios.post(`/api/employees/${props.employee.id}/sync-user-account`);
    alert('User account synced successfully!');
    emit('reactivate'); // Refresh the employee data
  } catch (error) {
    console.error('Error syncing user account:', error);
    if (error.response?.data?.message) {
      alert(error.response.data.message);
    }
  }
};

const resetPassword = async () => {
  if (!confirm('Reset password for this user? A new password will be generated.')) {
    return;
  }

  try {
    const response = await axios.post(`/api/employees/${props.employee.id}/reset-password`);
    alert(`Password reset successfully! New password: ${response.data.new_password}`);
  } catch (error) {
    console.error('Error resetting password:', error);
    if (error.response?.data?.message) {
      alert(error.response.data.message);
    }
  }
};

const deactivateUserAccount = async () => {
  if (!confirm('Deactivate user account? This will revoke system access.')) {
    return;
  }

  try {
    await axios.post(`/api/employees/${props.employee.id}/deactivate-user-account`);
    alert('User account deactivated successfully!');
    emit('reactivate'); // Refresh the employee data
  } catch (error) {
    console.error('Error deactivating user account:', error);
    if (error.response?.data?.message) {
      alert(error.response.data.message);
    }
  }
};
</script>
