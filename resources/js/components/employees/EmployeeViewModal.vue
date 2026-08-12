<template>
  <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200" style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);">
    <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-3xl shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 p-6 transition-all duration-300 z-10 max-h-[90vh] overflow-y-auto my-auto">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <div class="flex items-center">
          <div class="flex-shrink-0 h-16 w-16 mr-4">
            <img 
              v-if="employee.profile_image" 
              :src="`/storage/${employee.profile_image}`" 
              :alt="employee.full_name"
              class="h-16 w-16 rounded-full object-cover border border-slate-200 dark:border-zinc-700"
            />
            <div v-else class="h-16 w-16 rounded-full bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 flex items-center justify-center">
              <span class="text-lg font-bold text-slate-700 dark:text-slate-200">
                {{ getInitials(employee.first_name, employee.last_name) }}
              </span>
            </div>
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ employee.full_name }}</h3>
            <p class="text-xs font-mono text-slate-500 dark:text-slate-400 mt-0.5">{{ employee.employee_number }}</p>
            <span :class="getStatusClass(employee.employment_status)" class="px-2 py-0.5 mt-1 inline-flex text-[10px] font-bold rounded-full uppercase tracking-wider">
              {{ getStatusText(employee.employment_status) }}
            </span>
          </div>
        </div>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Employee Information -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Personal Information -->
        <div class="space-y-4">
          <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider border-b border-slate-100 dark:border-zinc-800 pb-2">Personal Information</h4>
          <div class="space-y-2.5">
            <div>
              <label class="block text-[11px] font-semibold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Email</label>
              <p class="text-xs font-medium text-slate-900 dark:text-slate-100 mt-0.5">{{ employee.email }}</p>
            </div>
            <div v-if="employee.phone">
              <label class="block text-[11px] font-semibold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Phone</label>
              <p class="text-xs font-medium text-slate-900 dark:text-slate-100 mt-0.5">{{ employee.phone }}</p>
            </div>
            <div v-if="employee.mobile">
              <label class="block text-[11px] font-semibold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Mobile</label>
              <p class="text-xs font-medium text-slate-900 dark:text-slate-100 mt-0.5">{{ employee.mobile }}</p>
            </div>
            <div v-if="employee.date_of_birth">
              <label class="block text-[11px] font-semibold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Date of Birth</label>
              <p class="text-xs font-mono text-slate-900 dark:text-slate-100 mt-0.5">{{ formatDate(employee.date_of_birth) }}</p>
            </div>
            <div v-if="employee.gender">
              <label class="block text-[11px] font-semibold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Gender</label>
              <p class="text-xs font-medium text-slate-900 dark:text-slate-100 mt-0.5">{{ capitalizeFirst(employee.gender) }}</p>
            </div>
            <div v-if="employee.marital_status">
              <label class="block text-[11px] font-semibold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Marital Status</label>
              <p class="text-xs font-medium text-slate-900 dark:text-slate-100 mt-0.5">{{ capitalizeFirst(employee.marital_status) }}</p>
            </div>
          </div>
        </div>

        <!-- Employment Information -->
        <div class="space-y-4">
          <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider border-b border-slate-100 dark:border-zinc-800 pb-2">Employment Information</h4>
          <div class="space-y-2.5">
            <div>
              <label class="block text-[11px] font-semibold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Department</label>
              <p class="text-xs font-medium text-slate-900 dark:text-slate-100 mt-0.5">{{ employee.department?.name || '-' }}</p>
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Position</label>
              <p class="text-xs font-medium text-slate-900 dark:text-slate-100 mt-0.5">{{ employee.position?.title || '-' }}</p>
            </div>
            <div v-if="employee.manager">
              <label class="block text-[11px] font-semibold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Manager</label>
              <p class="text-xs font-medium text-slate-900 dark:text-slate-100 mt-0.5">{{ employee.manager.full_name }}</p>
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Hire Date</label>
              <p class="text-xs font-mono text-slate-900 dark:text-slate-100 mt-0.5">{{ formatDate(employee.hire_date) }}</p>
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Employment Type</label>
              <p class="text-xs font-medium text-slate-900 dark:text-slate-100 mt-0.5">{{ getEmploymentTypeText(employee.employment_type) }}</p>
            </div>
            <div v-if="employee.probation_end_date">
              <label class="block text-[11px] font-semibold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Probation End Date</label>
              <p class="text-xs font-mono text-slate-900 dark:text-slate-100 mt-0.5">{{ formatDate(employee.probation_end_date) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Salary Information -->
      <div class="bg-slate-50 dark:bg-zinc-800/40 border border-slate-200/80 dark:border-zinc-800 p-4 rounded-2xl mb-6">
        <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-3">Salary Information</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-[11px] font-semibold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Basic Salary</label>
            <p class="text-xs font-mono font-bold text-slate-900 dark:text-slate-100 mt-0.5">${{ parseFloat(employee.basic_salary).toFixed(2) }}</p>
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Salary Type</label>
            <p class="text-xs font-medium text-slate-900 dark:text-slate-100 mt-0.5">{{ capitalizeFirst(employee.salary_type) }}</p>
          </div>
          <div v-if="employee.hourly_rate">
            <label class="block text-[11px] font-semibold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Hourly Rate</label>
            <p class="text-xs font-mono font-bold text-slate-900 dark:text-slate-100 mt-0.5">${{ parseFloat(employee.hourly_rate).toFixed(2) }}</p>
          </div>
        </div>
      </div>

      <!-- Emergency Contact -->
      <div v-if="employee.emergency_contact_name" class="bg-slate-50 dark:bg-zinc-800/40 border border-slate-200/80 dark:border-zinc-800 p-4 rounded-2xl mb-6">
        <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-3">Emergency Contact</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-[11px] font-semibold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Name</label>
            <p class="text-xs font-medium text-slate-900 dark:text-slate-100 mt-0.5">{{ employee.emergency_contact_name }}</p>
          </div>
          <div v-if="employee.emergency_contact_relationship">
            <label class="block text-[11px] font-semibold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Relationship</label>
            <p class="text-xs font-medium text-slate-900 dark:text-slate-100 mt-0.5">{{ employee.emergency_contact_relationship }}</p>
          </div>
          <div v-if="employee.emergency_contact_phone">
            <label class="block text-[11px] font-semibold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Phone</label>
            <p class="text-xs font-medium text-slate-900 dark:text-slate-100 mt-0.5">{{ employee.emergency_contact_phone }}</p>
          </div>
          <div v-if="employee.emergency_contact_email">
            <label class="block text-[11px] font-semibold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Email</label>
            <p class="text-xs font-medium text-slate-900 dark:text-slate-100 mt-0.5">{{ employee.emergency_contact_email }}</p>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="employee.notes" class="mb-6">
        <label class="block text-[11px] font-semibold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Notes</label>
        <p class="text-xs text-slate-900 dark:text-slate-100 bg-slate-50 dark:bg-zinc-800/40 border border-slate-200/80 dark:border-zinc-800 p-3 rounded-xl">{{ employee.notes }}</p>
      </div>

      <!-- User Account Section -->
      <div v-if="canEdit" class="bg-slate-50 dark:bg-zinc-800/40 border border-slate-200/80 dark:border-zinc-800 p-4 rounded-2xl mb-6">
        <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-3">User Account</h4>
        <div v-if="employee.user" class="space-y-2">
          <p class="text-xs text-slate-600 dark:text-slate-300">
            <span class="font-bold">Status:</span>
            <span class="text-slate-900 dark:text-white font-bold ml-1">Has User Account</span>
          </p>
          <p class="text-xs text-slate-600 dark:text-slate-300">
            <span class="font-bold">Login Email:</span> {{ employee.user.email }}
          </p>
          <div class="flex space-x-2 mt-3">
            <button
              @click="syncUserAccount"
              class="px-3 py-1.5 bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-semibold text-xs rounded-xl hover:opacity-90 cursor-pointer"
            >
              Sync Account
            </button>
            <button
              @click="resetPassword"
              class="px-3 py-1.5 bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-semibold text-xs rounded-xl hover:opacity-90 cursor-pointer"
            >
              Reset Password
            </button>
            <button
              @click="deactivateUserAccount"
              class="px-3 py-1.5 bg-red-600 text-white font-semibold text-xs rounded-xl hover:bg-red-700 cursor-pointer"
            >
              Deactivate Account
            </button>
          </div>
        </div>
        <div v-else class="space-y-2">
          <p class="text-xs text-slate-600 dark:text-slate-300">
            <span class="font-bold">Status:</span>
            <span class="text-red-500 font-bold ml-1">No User Account</span>
          </p>
          <button
            @click="createUserAccount"
            class="px-3 py-1.5 bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-semibold text-xs rounded-xl hover:opacity-90 cursor-pointer"
          >
            Create User Account
          </button>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-between items-center pt-6 border-t border-slate-100 dark:border-zinc-800">
        <div class="flex space-x-3">
          <button
            v-if="canEdit"
            @click="$emit('edit')"
            class="bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-semibold px-4 py-2 rounded-xl text-xs transition-all cursor-pointer shadow-xs"
          >
            Edit
          </button>
          <button
            v-if="canTerminate"
            @click="showTerminationModal = true"
            class="px-4 py-2 bg-red-600 text-white font-semibold rounded-xl text-xs hover:bg-red-700 cursor-pointer transition-all shadow-xs"
          >
            Terminate
          </button>
          <button
            v-if="canReactivate"
            @click="reactivateEmployee"
            class="bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-semibold px-4 py-2 rounded-xl text-xs transition-all cursor-pointer shadow-xs"
          >
            Reactivate
          </button>
        </div>

        <button
          @click="$emit('close')"
          class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
        >
          Close
        </button>
      </div>
    </div>

    <!-- Termination Modal -->
    <div v-if="showTerminationModal" class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md" style="backdrop-filter: blur(6px);">
      <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-96 shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 p-5">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Terminate Employee</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Termination Date *</label>
            <input
              v-model="terminationData.termination_date"
              type="date"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Termination Reason *</label>
            <textarea
              v-model="terminationData.termination_reason"
              rows="3"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Please provide a reason for termination..."
            ></textarea>
          </div>
        </div>
        <div class="flex justify-end space-x-3 mt-6">
          <button
            @click="showTerminationModal = false"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="terminateEmployee"
            :disabled="!terminationData.termination_reason.trim() || !terminationData.termination_date"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50"
          >
            Terminate
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const authStore = useAuthStore();

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
    active: 'bg-green-100 text-green-800',
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
