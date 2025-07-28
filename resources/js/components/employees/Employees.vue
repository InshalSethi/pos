<template>
  <div class="employees-container">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Employee Management</h1>
        <p class="text-gray-600">Manage employees, departments, and positions</p>
      </div>
      <div class="flex space-x-3">
        <button
          @click="showDepartmentModal = true"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Add Department
        </button>
        <button
          @click="showPositionModal = true"
          class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Add Position
        </button>
        <button
          @click="showEmployeeModal = true"
          class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Add Employee
        </button>
      </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-6">
      <nav class="-mb-px flex space-x-8">
        <button
          @click="activeTab = 'employees'"
          :class="[
            'py-2 px-1 border-b-2 font-medium text-sm',
            activeTab === 'employees'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Employees
        </button>
        <button
          @click="activeTab = 'departments'"
          :class="[
            'py-2 px-1 border-b-2 font-medium text-sm',
            activeTab === 'departments'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Departments
        </button>
        <button
          @click="activeTab = 'positions'"
          :class="[
            'py-2 px-1 border-b-2 font-medium text-sm',
            activeTab === 'positions'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Positions
        </button>
        <button
          @click="activeTab = 'reports'"
          :class="[
            'py-2 px-1 border-b-2 font-medium text-sm',
            activeTab === 'reports'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Reports
        </button>
        <button
          @click="activeTab = 'user-management'"
          :class="[
            'py-2 px-1 border-b-2 font-medium text-sm',
            activeTab === 'user-management'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          User Management
        </button>
      </nav>
    </div>

    <!-- Tab Content -->
    <div v-if="activeTab === 'employees'">
      <EmployeeList 
        @edit-employee="editEmployee"
        @view-employee="viewEmployee"
        @refresh="fetchEmployees"
      />
    </div>

    <div v-if="activeTab === 'departments'">
      <DepartmentList 
        @edit-department="editDepartment"
        @refresh="fetchDepartments"
      />
    </div>

    <div v-if="activeTab === 'positions'">
      <PositionList 
        @edit-position="editPosition"
        @refresh="fetchPositions"
      />
    </div>

    <div v-if="activeTab === 'reports'">
      <EmployeeReports />
    </div>

    <div v-if="activeTab === 'user-management'">
      <EmployeeUserManagement />
    </div>

    <!-- Employee Modal -->
    <EmployeeModal
      v-if="showEmployeeModal"
      :employee="selectedEmployee"
      @close="closeEmployeeModal"
      @saved="handleEmployeeSaved"
    />

    <!-- Department Modal -->
    <DepartmentModal
      v-if="showDepartmentModal"
      :department="selectedDepartment"
      @close="closeDepartmentModal"
      @saved="handleDepartmentSaved"
    />

    <!-- Position Modal -->
    <PositionModal
      v-if="showPositionModal"
      :position="selectedPosition"
      @close="closePositionModal"
      @saved="handlePositionSaved"
    />

    <!-- Employee View Modal -->
    <EmployeeViewModal
      v-if="showEmployeeViewModal"
      :employee="selectedEmployee"
      @close="showEmployeeViewModal = false"
      @edit="editEmployeeFromView"
      @terminate="handleEmployeeTerminate"
      @reactivate="handleEmployeeReactivate"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import EmployeeList from './EmployeeList.vue';
import DepartmentList from './DepartmentList.vue';
import PositionList from './PositionList.vue';
import EmployeeReports from './EmployeeReports.vue';
import EmployeeUserManagement from './EmployeeUserManagement.vue';
import EmployeeModal from './EmployeeModal.vue';
import DepartmentModal from './DepartmentModal.vue';
import PositionModal from './PositionModal.vue';
import EmployeeViewModal from './EmployeeViewModal.vue';

// Reactive data
const activeTab = ref('employees');
const showEmployeeModal = ref(false);
const showDepartmentModal = ref(false);
const showPositionModal = ref(false);
const showEmployeeViewModal = ref(false);
const selectedEmployee = ref(null);
const selectedDepartment = ref(null);
const selectedPosition = ref(null);

// Methods
const editEmployee = (employee) => {
  selectedEmployee.value = employee;
  showEmployeeModal.value = true;
};

const viewEmployee = (employee) => {
  selectedEmployee.value = employee;
  showEmployeeViewModal.value = true;
};

const editDepartment = (department) => {
  selectedDepartment.value = department;
  showDepartmentModal.value = true;
};

const editPosition = (position) => {
  selectedPosition.value = position;
  showPositionModal.value = true;
};

const closeEmployeeModal = () => {
  showEmployeeModal.value = false;
  selectedEmployee.value = null;
};

const closeDepartmentModal = () => {
  showDepartmentModal.value = false;
  selectedDepartment.value = null;
};

const closePositionModal = () => {
  showPositionModal.value = false;
  selectedPosition.value = null;
};

const editEmployeeFromView = () => {
  showEmployeeViewModal.value = false;
  showEmployeeModal.value = true;
};

const handleEmployeeSaved = () => {
  closeEmployeeModal();
  fetchEmployees();
};

const handleDepartmentSaved = () => {
  closeDepartmentModal();
  fetchDepartments();
};

const handlePositionSaved = () => {
  closePositionModal();
  fetchPositions();
};

const handleEmployeeTerminate = () => {
  showEmployeeViewModal.value = false;
  fetchEmployees();
};

const handleEmployeeReactivate = () => {
  showEmployeeViewModal.value = false;
  fetchEmployees();
};

const fetchEmployees = () => {
  // This will be handled by the EmployeeList component
};

const fetchDepartments = () => {
  // This will be handled by the DepartmentList component
};

const fetchPositions = () => {
  // This will be handled by the PositionList component
};

onMounted(() => {
  // Component initialization
});
</script>

<style scoped>
.employees-container {
  padding: 1.5rem;
}
</style>
