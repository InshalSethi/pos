<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
      <!-- Header -->
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-900">
          {{ isEditing ? 'Edit Employee' : 'Create New Employee' }}
        </h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="saveEmployee" class="space-y-6">
        <!-- Personal Information Section -->
        <div class="bg-gray-50 p-4 rounded-lg">
          <h4 class="text-md font-medium text-gray-900 mb-4">Personal Information</h4>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
              <input
                v-model="form.first_name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <span v-if="errors.first_name" class="text-red-500 text-sm">{{ errors.first_name[0] }}</span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
              <input
                v-model="form.middle_name"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <span v-if="errors.middle_name" class="text-red-500 text-sm">{{ errors.middle_name[0] }}</span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
              <input
                v-model="form.last_name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <span v-if="errors.last_name" class="text-red-500 text-sm">{{ errors.last_name[0] }}</span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
              <input
                v-model="form.email"
                type="email"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <span v-if="errors.email" class="text-red-500 text-sm">{{ errors.email[0] }}</span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
              <input
                v-model="form.phone"
                type="tel"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <span v-if="errors.phone" class="text-red-500 text-sm">{{ errors.phone[0] }}</span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Mobile</label>
              <input
                v-model="form.mobile"
                type="tel"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <span v-if="errors.mobile" class="text-red-500 text-sm">{{ errors.mobile[0] }}</span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
              <input
                v-model="form.date_of_birth"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <span v-if="errors.date_of_birth" class="text-red-500 text-sm">{{ errors.date_of_birth[0] }}</span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
              <select
                v-model="form.gender"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
              <span v-if="errors.gender" class="text-red-500 text-sm">{{ errors.gender[0] }}</span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Marital Status</label>
              <select
                v-model="form.marital_status"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Status</option>
                <option value="single">Single</option>
                <option value="married">Married</option>
                <option value="divorced">Divorced</option>
                <option value="widowed">Widowed</option>
              </select>
              <span v-if="errors.marital_status" class="text-red-500 text-sm">{{ errors.marital_status[0] }}</span>
            </div>
          </div>
        </div>

        <!-- Employment Information Section -->
        <div class="bg-gray-50 p-4 rounded-lg">
          <h4 class="text-md font-medium text-gray-900 mb-4">Employment Information</h4>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
              <select
                v-model="form.department_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Department</option>
                <option v-for="department in departments" :key="department.id" :value="department.id">
                  {{ department.name }}
                </option>
              </select>
              <span v-if="errors.department_id" class="text-red-500 text-sm">{{ errors.department_id[0] }}</span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
              <select
                v-model="form.position_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Position</option>
                <option v-for="position in filteredPositions" :key="position.id" :value="position.id">
                  {{ position.title }}
                </option>
              </select>
              <span v-if="errors.position_id" class="text-red-500 text-sm">{{ errors.position_id[0] }}</span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Manager</label>
              <select
                v-model="form.manager_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Manager</option>
                <option v-for="employee in availableManagers" :key="employee.id" :value="employee.id">
                  {{ employee.full_name }}
                </option>
              </select>
              <span v-if="errors.manager_id" class="text-red-500 text-sm">{{ errors.manager_id[0] }}</span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Hire Date *</label>
              <input
                v-model="form.hire_date"
                type="date"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <span v-if="errors.hire_date" class="text-red-500 text-sm">{{ errors.hire_date[0] }}</span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Employment Type *</label>
              <select
                v-model="form.employment_type"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Type</option>
                <option value="full_time">Full Time</option>
                <option value="part_time">Part Time</option>
                <option value="contract">Contract</option>
                <option value="intern">Intern</option>
              </select>
              <span v-if="errors.employment_type" class="text-red-500 text-sm">{{ errors.employment_type[0] }}</span>
            </div>
            <div v-if="isEditing">
              <label class="block text-sm font-medium text-gray-700 mb-1">Employment Status</label>
              <select
                v-model="form.employment_status"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="terminated">Terminated</option>
                <option value="on_leave">On Leave</option>
              </select>
              <span v-if="errors.employment_status" class="text-red-500 text-sm">{{ errors.employment_status[0] }}</span>
            </div>
          </div>
        </div>

        <!-- Salary Information Section -->
        <div class="bg-gray-50 p-4 rounded-lg">
          <h4 class="text-md font-medium text-gray-900 mb-4">Salary Information</h4>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Basic Salary *</label>
              <input
                v-model="form.basic_salary"
                type="number"
                step="0.01"
                min="0"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <span v-if="errors.basic_salary" class="text-red-500 text-sm">{{ errors.basic_salary[0] }}</span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Salary Type *</label>
              <select
                v-model="form.salary_type"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Type</option>
                <option value="monthly">Monthly</option>
                <option value="hourly">Hourly</option>
                <option value="daily">Daily</option>
              </select>
              <span v-if="errors.salary_type" class="text-red-500 text-sm">{{ errors.salary_type[0] }}</span>
            </div>
            <div v-if="form.salary_type === 'hourly'">
              <label class="block text-sm font-medium text-gray-700 mb-1">Hourly Rate</label>
              <input
                v-model="form.hourly_rate"
                type="number"
                step="0.01"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <span v-if="errors.hourly_rate" class="text-red-500 text-sm">{{ errors.hourly_rate[0] }}</span>
            </div>
          </div>
        </div>

        <!-- Profile Image -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Profile Image</label>
          <input
            ref="fileInput"
            type="file"
            accept="image/*"
            @change="handleFileUpload"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <p class="text-sm text-gray-500 mt-1">Upload profile image (JPEG, PNG, GIF, max 2MB)</p>
          <span v-if="errors.profile_image" class="text-red-500 text-sm">{{ errors.profile_image[0] }}</span>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3 pt-4 border-t">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="saving"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
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
import axios from 'axios';

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
