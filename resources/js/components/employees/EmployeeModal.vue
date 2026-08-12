<template>
  <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200" style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);">
    <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-4xl shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 p-6 transition-all duration-300 z-10 max-h-[90vh] overflow-y-auto my-auto">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-base font-bold text-slate-900 dark:text-white">
          {{ isEditing ? 'Edit Employee' : 'Create New Employee' }}
        </h3>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="saveEmployee" class="space-y-6">
        <!-- Personal Information Section -->
        <div class="bg-slate-50 dark:bg-zinc-800/40 border border-slate-200/80 dark:border-zinc-800 p-4 rounded-2xl">
          <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-4">Personal Information</h4>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">First Name *</label>
              <input
                v-model="form.first_name"
                type="text"
                required
                class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
              />
              <span v-if="errors.first_name" class="text-red-500 text-xs mt-1 block">{{ errors.first_name[0] }}</span>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Middle Name</label>
              <input
                v-model="form.middle_name"
                type="text"
                class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
              />
              <span v-if="errors.middle_name" class="text-red-500 text-xs mt-1 block">{{ errors.middle_name[0] }}</span>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Last Name *</label>
              <input
                v-model="form.last_name"
                type="text"
                required
                class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
              />
              <span v-if="errors.last_name" class="text-red-500 text-xs mt-1 block">{{ errors.last_name[0] }}</span>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Email *</label>
              <input
                v-model="form.email"
                type="email"
                required
                class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
              />
              <span v-if="errors.email" class="text-red-500 text-xs mt-1 block">{{ errors.email[0] }}</span>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Phone</label>
              <input
                v-model="form.phone"
                type="tel"
                class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
              />
              <span v-if="errors.phone" class="text-red-500 text-xs mt-1 block">{{ errors.phone[0] }}</span>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Mobile</label>
              <input
                v-model="form.mobile"
                type="tel"
                class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
              />
              <span v-if="errors.mobile" class="text-red-500 text-xs mt-1 block">{{ errors.mobile[0] }}</span>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Date of Birth</label>
              <input
                v-model="form.date_of_birth"
                type="date"
                class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
              />
              <span v-if="errors.date_of_birth" class="text-red-500 text-xs mt-1 block">{{ errors.date_of_birth[0] }}</span>
            </div>
            <div>
              <FloatingSelect
                v-model="form.gender"
                label="Gender"
                placeholder="Select Gender"
                :options="genderOptions"
                :error="!!errors.gender"
              />
              <span v-if="errors.gender" class="text-red-500 text-xs mt-1 block">{{ errors.gender[0] }}</span>
            </div>
            <div>
              <FloatingSelect
                v-model="form.marital_status"
                label="Marital Status"
                placeholder="Select Status"
                :options="maritalStatusOptions"
                :error="!!errors.marital_status"
              />
              <span v-if="errors.marital_status" class="text-red-500 text-xs mt-1 block">{{ errors.marital_status[0] }}</span>
            </div>
          </div>
        </div>

        <!-- Employment Information Section -->
        <div class="bg-slate-50 dark:bg-zinc-800/40 border border-slate-200/80 dark:border-zinc-800 p-4 rounded-2xl">
          <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-4">Employment Information</h4>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <FloatingSelect
                v-model="form.department_id"
                label="Department"
                placeholder="Select Department"
                :options="departmentSelectOptions"
                :error="!!errors.department_id"
              />
              <span v-if="errors.department_id" class="text-red-500 text-xs mt-1 block">{{ errors.department_id[0] }}</span>
            </div>
            <div>
              <FloatingSelect
                v-model="form.position_id"
                label="Position"
                placeholder="Select Position"
                :options="positionSelectOptions"
                :error="!!errors.position_id"
              />
              <span v-if="errors.position_id" class="text-red-500 text-xs mt-1 block">{{ errors.position_id[0] }}</span>
            </div>
            <div>
              <FloatingSelect
                v-model="form.manager_id"
                label="Manager"
                placeholder="Select Manager"
                :options="managerSelectOptions"
                :error="!!errors.manager_id"
              />
              <span v-if="errors.manager_id" class="text-red-500 text-xs mt-1 block">{{ errors.manager_id[0] }}</span>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Hire Date *</label>
              <input
                v-model="form.hire_date"
                type="date"
                required
                class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
              />
              <span v-if="errors.hire_date" class="text-red-500 text-xs mt-1 block">{{ errors.hire_date[0] }}</span>
            </div>
            <div>
              <FloatingSelect
                v-model="form.employment_type"
                label="Employment Type"
                placeholder="Select Type"
                :options="employmentTypeOptions"
                required
                :error="!!errors.employment_type"
              />
              <span v-if="errors.employment_type" class="text-red-500 text-xs mt-1 block">{{ errors.employment_type[0] }}</span>
            </div>
            <div v-if="isEditing">
              <FloatingSelect
                v-model="form.employment_status"
                label="Employment Status"
                placeholder="Select Status"
                :options="employmentStatusOptions"
                :error="!!errors.employment_status"
              />
              <span v-if="errors.employment_status" class="text-red-500 text-xs mt-1 block">{{ errors.employment_status[0] }}</span>
            </div>
          </div>
        </div>

        <!-- Salary Information Section -->
        <div class="bg-slate-50 dark:bg-zinc-800/40 border border-slate-200/80 dark:border-zinc-800 p-4 rounded-2xl">
          <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-4">Salary Information</h4>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Basic Salary *</label>
              <input
                v-model="form.basic_salary"
                type="number"
                step="0.01"
                min="0"
                required
                class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
              />
              <span v-if="errors.basic_salary" class="text-red-500 text-xs mt-1 block">{{ errors.basic_salary[0] }}</span>
            </div>
            <div>
              <FloatingSelect
                v-model="form.salary_type"
                label="Salary Type"
                placeholder="Select Type"
                :options="salaryTypeOptions"
                required
                :error="!!errors.salary_type"
              />
              <span v-if="errors.salary_type" class="text-red-500 text-xs mt-1 block">{{ errors.salary_type[0] }}</span>
            </div>
            <div v-if="form.salary_type === 'hourly'">
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Hourly Rate</label>
              <input
                v-model="form.hourly_rate"
                type="number"
                step="0.01"
                min="0"
                class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
              />
              <span v-if="errors.hourly_rate" class="text-red-500 text-xs mt-1 block">{{ errors.hourly_rate[0] }}</span>
            </div>
          </div>
        </div>

        <!-- Profile Image -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Profile Image</label>
          <input
            ref="fileInput"
            type="file"
            accept="image/*"
            @change="handleFileUpload"
            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-0 focus:border-transparent"
          />
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Upload profile image (JPEG, PNG, GIF, max 2MB)</p>
          <span v-if="errors.profile_image" class="text-red-500 text-xs mt-1 block">{{ errors.profile_image[0] }}</span>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3 pt-4 border-t border-slate-100 dark:border-zinc-800">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-800 cursor-pointer"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="saving"
            class="bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-semibold px-4 py-2 rounded-xl text-xs transition-all cursor-pointer shadow-xs disabled:opacity-50"
          >
            {{ saving ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import FloatingSelect from '@/components/common/FloatingSelect.vue';
import axios from 'axios';

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
  }
});

const emit = defineEmits(['close', 'saved']);

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
  department_id: '',
  position_id: '',
  manager_id: '',
  hire_date: '',
  employment_type: '',
  employment_status: 'active',
  basic_salary: '',
  salary_type: '',
  hourly_rate: ''
});

const departments = ref([]);
const positions = ref([]);
const employees = ref([]);
const errors = ref({});
const saving = ref(false);
const selectedFile = ref(null);

// Computed
const isEditing = computed(() => !!props.employee);

const filteredPositions = computed(() => {
  if (!form.value.department_id) {
    return positions.value;
  }
  return positions.value.filter(position => position.department_id == form.value.department_id);
});

const availableManagers = computed(() => {
  if (!employees.value) {
    console.log('No employees data available');
    return [];
  }

  // Filter out the current employee (if editing) to prevent self-assignment
  const filtered = employees.value.filter(employee => {
    if (props.employee && employee.id === props.employee.id) {
      return false;
    }
    // Since we're getting data from the dropdown endpoint, employees should already be active
    return true;
  });

  console.log('Available managers:', filtered);
  return filtered;
});

const departmentSelectOptions = computed(() => [
  { value: '', label: 'Select Department' },
  ...departments.value.map(d => ({ value: d.id, label: d.name }))
]);

const positionSelectOptions = computed(() => [
  { value: '', label: 'Select Position' },
  ...filteredPositions.value.map(p => ({ value: p.id, label: p.title }))
]);

const managerSelectOptions = computed(() => [
  { value: '', label: 'Select Manager' },
  ...availableManagers.value.map(m => ({ value: m.id, label: m.full_name }))
]);

// Watch for department changes to reset position
watch(() => form.value.department_id, () => {
  form.value.position_id = '';
});

// Methods
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
    console.log('Fetched employees for dropdown:', employees.value);
  } catch (error) {
    console.error('Error fetching employees:', error);
    console.error('Error details:', error.response);

    // Fallback to test endpoint if auth fails
    try {
      const fallbackResponse = await axios.get('/api/test-dropdown');
      employees.value = fallbackResponse.data;
      console.log('Used fallback endpoint, fetched employees:', employees.value);
    } catch (fallbackError) {
      console.error('Fallback also failed:', fallbackError);
      employees.value = [];
    }
  }
};

const handleFileUpload = (event) => {
  selectedFile.value = event.target.files[0];
};

const saveEmployee = async () => {
  saving.value = true;
  errors.value = {};

  try {
    const formData = new FormData();
    
    // Append form data
    Object.keys(form.value).forEach(key => {
      if (form.value[key] !== null && form.value[key] !== '') {
        formData.append(key, form.value[key]);
      }
    });

    // Append file
    if (selectedFile.value) {
      formData.append('profile_image', selectedFile.value);
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

    emit('saved');
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      console.error('Error saving employee:', error);
    }
  } finally {
    saving.value = false;
  }
};

// Initialize form if editing
const initializeForm = () => {
  if (props.employee) {
    Object.keys(form.value).forEach(key => {
      if (props.employee[key] !== undefined) {
        form.value[key] = props.employee[key];
      }
    });
  }
};

// Lifecycle
onMounted(() => {
  fetchDepartments();
  fetchPositions();
  fetchEmployees();
  initializeForm();
});
</script>
