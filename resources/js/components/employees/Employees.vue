<template>
  <div class="employees-container max-w-full font-sans">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
      <div>
        <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Employee Management</h1>
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">Manage employees, departments, and positions</p>
      </div>
      <div class="flex items-center gap-2.5 flex-wrap">
        <button
          @click="showDepartmentModal = true"
          class="bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-semibold px-4 py-2 rounded-xl text-xs flex items-center transition-all duration-200 cursor-pointer shadow-xs"
        >
          Add Department
        </button>
        <button
          @click="showPositionModal = true"
          class="bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-semibold px-4 py-2 rounded-xl text-xs flex items-center transition-all duration-200 cursor-pointer shadow-xs"
        >
          Add Position
        </button>
        <button
          @click="openAddManagerModal"
          class="bg-indigo-600 text-white hover:bg-indigo-700 font-semibold px-4 py-2 rounded-xl text-xs flex items-center transition-all duration-200 cursor-pointer shadow-xs"
        >
          Add Manager
        </button>
        <button
          @click="openAddEmployeeModal"
          class="bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-semibold px-4 py-2 rounded-xl text-xs flex items-center transition-all duration-200 cursor-pointer shadow-xs"
        >
          Add Employee
        </button>
      </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-slate-200 dark:border-zinc-800 mb-6">
      <nav class="-mb-px flex space-x-8">
        <button
          @click="activeTab = 'employees'"
          :class="[
            'py-2.5 px-1 border-b-2 font-medium text-xs transition-all cursor-pointer',
            activeTab === 'employees'
              ? 'border-slate-900 text-slate-900 dark:border-white dark:text-white font-bold'
              : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:border-slate-300 dark:hover:border-zinc-700'
          ]"
        >
          Employees
        </button>
        <button
          @click="activeTab = 'managers'"
          :class="[
            'py-2.5 px-1 border-b-2 font-medium text-xs transition-all cursor-pointer',
            activeTab === 'managers'
              ? 'border-slate-900 text-slate-900 dark:border-white dark:text-white font-bold'
              : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:border-slate-300 dark:hover:border-zinc-700'
          ]"
        >
          Managers
        </button>
        <button
          @click="activeTab = 'departments'"
          :class="[
            'py-2.5 px-1 border-b-2 font-medium text-xs transition-all cursor-pointer',
            activeTab === 'departments'
              ? 'border-slate-900 text-slate-900 dark:border-white dark:text-white font-bold'
              : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:border-slate-300 dark:hover:border-zinc-700'
          ]"
        >
          Departments
        </button>
        <button
          @click="activeTab = 'positions'"
          :class="[
            'py-2.5 px-1 border-b-2 font-medium text-xs transition-all cursor-pointer',
            activeTab === 'positions'
              ? 'border-slate-900 text-slate-900 dark:border-white dark:text-white font-bold'
              : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:border-slate-300 dark:hover:border-zinc-700'
          ]"
        >
          Positions
        </button>
        <button
          @click="activeTab = 'reports'"
          :class="[
            'py-2.5 px-1 border-b-2 font-medium text-xs transition-all cursor-pointer',
            activeTab === 'reports'
              ? 'border-slate-900 text-slate-900 dark:border-white dark:text-white font-bold'
              : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:border-slate-300 dark:hover:border-zinc-700'
          ]"
        >
          Reports
        </button>
        <button
          @click="activeTab = 'user-management'"
          :class="[
            'py-2.5 px-1 border-b-2 font-medium text-xs transition-all cursor-pointer',
            activeTab === 'user-management'
              ? 'border-slate-900 text-slate-900 dark:border-white dark:text-white font-bold'
              : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:border-slate-300 dark:hover:border-zinc-700'
          ]"
        >
          User Management
        </button>
      </nav>
    </div>

    <!-- Tab Content -->
    <div v-show="activeTab === 'employees'">
      <EmployeeList 
        ref="employeeListRef"
        @edit-employee="editEmployee"
        @view-employee="viewEmployee"
        @refresh="fetchEmployees"
      />
    </div>

    <div v-show="activeTab === 'managers'">
      <ManagerList 
        ref="managerListRef"
        @edit-employee="editManager"
        @view-employee="viewEmployee"
        @refresh="fetchEmployees"
      />
    </div>

    <div v-show="activeTab === 'departments'">
      <DepartmentList 
        ref="departmentListRef"
        @edit-department="editDepartment"
        @refresh="fetchDepartments"
      />
    </div>

    <div v-show="activeTab === 'positions'">
      <PositionList 
        ref="positionListRef"
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

    <!-- Employee / Manager Modal -->
    <EmployeeModal
      v-if="showEmployeeModal"
      :employee="selectedEmployee"
      :is-manager-mode="isManagerModalMode"
      @close="closeEmployeeModal"
      @saved="handleEmployeeSaved"
      @add-manager="handleAddManager"
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
import { ref, onMounted, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import EmployeeList from './EmployeeList.vue';
import ManagerList from './ManagerList.vue';
import DepartmentList from './DepartmentList.vue';
import PositionList from './PositionList.vue';
import EmployeeReports from './EmployeeReports.vue';
import EmployeeUserManagement from './EmployeeUserManagement.vue';
import EmployeeModal from './EmployeeModal.vue';
import DepartmentModal from './DepartmentModal.vue';
import PositionModal from './PositionModal.vue';
import EmployeeViewModal from './EmployeeViewModal.vue';

const route = useRoute();
const router = useRouter();

// Reactive data
const activeTab = ref('employees');
const showEmployeeModal = ref(false);
const isManagerModalMode = ref(false);
const pendingEmployeeCreation = ref(false);
const showDepartmentModal = ref(false);
const showPositionModal = ref(false);
const showEmployeeViewModal = ref(false);
const selectedEmployee = ref(null);
const selectedDepartment = ref(null);
const selectedPosition = ref(null);

const employeeListRef = ref(null);
const managerListRef = ref(null);
const departmentListRef = ref(null);
const positionListRef = ref(null);

const checkAutoOpenCreate = () => {
  if (route.path.endsWith('/create') || route.query.create === 'true' || route.query.action === 'create') {
    selectedEmployee.value = null;
    isManagerModalMode.value = false;
    pendingEmployeeCreation.value = false;
    showEmployeeModal.value = true;
  }
};

// Methods
const openAddEmployeeModal = () => {
  selectedEmployee.value = null;
  isManagerModalMode.value = false;
  pendingEmployeeCreation.value = false;
  showEmployeeModal.value = true;
};

const openAddManagerModal = () => {
  selectedEmployee.value = null;
  isManagerModalMode.value = true;
  pendingEmployeeCreation.value = false;
  showEmployeeModal.value = true;
};

const editEmployee = (employee) => {
  selectedEmployee.value = employee;
  isManagerModalMode.value = false;
  pendingEmployeeCreation.value = false;
  showEmployeeModal.value = true;
};

const editManager = (employee) => {
  selectedEmployee.value = employee;
  isManagerModalMode.value = true;
  pendingEmployeeCreation.value = false;
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
  if (route.path.endsWith('/create')) {
    router.replace('/employees');
  }
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

const handleEmployeeSaved = (savedPayload) => {
  const isManagerCreated = isManagerModalMode.value || savedPayload?.employee?.is_manager;
  const reOpenEmployeeForm = pendingEmployeeCreation.value;
  
  closeEmployeeModal();

  if (isManagerCreated && !reOpenEmployeeForm) {
    activeTab.value = 'managers';
  }

  fetchEmployees();

  if (reOpenEmployeeForm) {
    nextTick(() => {
      selectedEmployee.value = null;
      isManagerModalMode.value = false;
      pendingEmployeeCreation.value = false;
      showEmployeeModal.value = true;
    });
  }
};

const handleAddManager = () => {
  closeEmployeeModal();
  nextTick(() => {
    selectedEmployee.value = null;
    isManagerModalMode.value = true;
    pendingEmployeeCreation.value = true;
    showEmployeeModal.value = true;
  });
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
  nextTick(() => {
    if (managerListRef.value?.fetchManagers) {
      managerListRef.value.fetchManagers();
    }
    if (employeeListRef.value?.fetchEmployees) {
      employeeListRef.value.fetchEmployees();
    }
  });
};

const fetchDepartments = () => {
  nextTick(() => {
    if (departmentListRef.value?.fetchDepartments) {
      departmentListRef.value.fetchDepartments();
    }
  });
};

const fetchPositions = () => {
  nextTick(() => {
    if (positionListRef.value?.fetchPositions) {
      positionListRef.value.fetchPositions();
    }
  });
};

onMounted(() => {
  checkAutoOpenCreate();
});

watch(activeTab, () => {
  fetchEmployees();
  fetchDepartments();
  fetchPositions();
});

watch(() => route.path, () => {
  checkAutoOpenCreate();
});

watch(() => route.query, () => {
  checkAutoOpenCreate();
});
</script>

<style scoped>
.employees-container {
  padding: 1.5rem;
}
</style>
