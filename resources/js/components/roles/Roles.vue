<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Role Management</h1>
        <button
          @click="showCreateModal = true"
          class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium"
        >
          Add Role
        </button>
      </div>

      <!-- Search -->
      <div class="mb-6">
        <div class="max-w-md">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search roles..."
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          />
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Loading roles...</p>
      </div>

      <!-- Roles Table -->
      <div v-else class="bg-white dark:bg-zinc-900 shadow overflow-hidden sm:rounded-md border border-gray-200 dark:border-zinc-800">
        <ul class="divide-y divide-gray-200 dark:divide-zinc-800">
          <li v-for="role in filteredRoles" :key="role.id" class="px-6 py-4">
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center">
                      <span class="text-white font-medium">{{ role.name.charAt(0).toUpperCase() }}</span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ role.name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                      {{ role.permissions ? role.permissions.length : 0 }} permissions assigned
                    </div>
                  </div>
                </div>
              </div>
              <div class="flex space-x-2">
                <button
                  @click="editRole(role)"
                  class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 text-sm font-medium"
                >
                  Edit
                </button>
                <button
                  @click="deleteRole(role)"
                  :disabled="isSystemRole(role.name)"
                  :class="[
                    'text-sm font-medium',
                    isSystemRole(role.name) 
                      ? 'text-gray-400 cursor-not-allowed' 
                      : 'text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300'
                  ]"
                >
                  Delete
                </button>
              </div>
            </div>
          </li>
        </ul>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && filteredRoles.length === 0" class="text-center py-8">
        <p class="text-gray-500 dark:text-gray-400">No roles found.</p>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto h-full w-full bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-md transition-all duration-200" style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);">
      <div class="relative mx-auto border border-slate-200 dark:border-zinc-800 w-full max-w-3xl shadow-2xl rounded-2xl bg-white dark:bg-zinc-900 text-slate-800 dark:text-slate-100 p-6 transition-all duration-300 z-10 max-h-[90vh] overflow-y-auto my-auto">
        <div class="mt-1">
          <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-4 mb-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
              {{ showCreateModal ? 'Create New Role' : 'Edit Role' }}
            </h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Error Messages -->
          <div v-if="formErrors.length > 0" class="mb-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-md p-3">
            <ul class="text-xs text-red-600 dark:text-red-400 space-y-1">
              <li v-for="error in formErrors" :key="error">{{ error }}</li>
            </ul>
          </div>

          <form @submit.prevent="showCreateModal ? createRole() : updateRole()">
            <!-- Role Name -->
            <div class="mb-5">
              <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1.5">Role Name *</label>
              <input
                v-model="roleForm.name"
                type="text"
                required
                class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-zinc-100 focus:outline-none"
                placeholder="Enter role name"
              />
            </div>

            <!-- Tree View Permissions Selection -->
            <div class="mb-6">
              <div class="flex justify-between items-center mb-3">
                <div>
                  <label class="block text-xs font-bold text-slate-800 dark:text-zinc-200">Assign Permissions</label>
                  <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5">Organized by module. Select modules or individual action permissions.</p>
                </div>
                <button
                  type="button"
                  @click="toggleSelectAll"
                  class="text-xs font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors cursor-pointer"
                >
                  {{ isAllSelected ? 'Deselect All' : 'Select All' }}
                </button>
              </div>

              <div v-if="!permissions || Object.keys(groupedPermissions).length === 0" class="text-xs text-slate-500 dark:text-slate-400 italic p-4 bg-slate-50 dark:bg-zinc-800/40 rounded-xl border border-slate-200 dark:border-zinc-800">
                No permissions are registered in the system.
              </div>

              <div v-else class="border border-slate-200 dark:border-zinc-800 rounded-xl p-4 md:p-5 bg-slate-50/70 dark:bg-zinc-800/40 max-h-[45vh] overflow-y-auto space-y-3 shadow-inner">
                <!-- Treeview -->
                <div class="treeview select-none">
                  <!-- Root Nodes (Modules) -->
                  <div v-for="(modulePerms, moduleName) in groupedPermissions" :key="moduleName" class="treeview-node mb-2.5 last:mb-0">
                    <!-- Module Row -->
                    <div class="flex items-center py-2 px-3 hover:bg-slate-200/50 dark:hover:bg-zinc-700/50 rounded-lg transition-colors duration-150">
                      <!-- Expand/Collapse Chevron -->
                      <button 
                        type="button" 
                        @click="toggleModuleCollapse(moduleName)" 
                        class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded transition-transform duration-200 focus:outline-none"
                        :class="{'rotate-90': !isModuleCollapsed(moduleName)}"
                      >
                        <i class="fas fa-chevron-right text-[10px]"></i>
                      </button>

                      <!-- Parent Checkbox -->
                      <input 
                        type="checkbox" 
                        :id="'roles-parent-' + moduleName" 
                        :checked="isModuleFullySelected(modulePerms)" 
                        :ref="el => { if (el) el.indeterminate = isModulePartiallySelected(modulePerms); }"
                        @change="toggleModuleSelection(modulePerms, $event)"
                        class="w-4 h-4 ml-1 text-indigo-600 dark:text-indigo-500 border-slate-300 dark:border-zinc-700 rounded focus:ring-indigo-500 cursor-pointer"
                      >

                      <!-- Module Title & Count -->
                      <div @click="toggleModuleCollapse(moduleName)" class="flex items-center ml-2.5 cursor-pointer flex-1 py-1">
                        <i 
                          :class="[isModuleCollapsed(moduleName) ? 'far fa-folder text-amber-500' : 'far fa-folder-open text-amber-500']" 
                          class="text-base mr-2"
                        ></i>
                        <span class="font-bold text-slate-800 dark:text-slate-100 text-xs sm:text-sm">{{ moduleName }} Module</span>
                        <span class="ml-2.5 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/50">
                          {{ getSelectedCountForModule(modulePerms) }} / {{ modulePerms.length }} selected
                        </span>
                      </div>
                    </div>

                    <!-- Child Nodes -->
                    <div 
                      v-show="!isModuleCollapsed(moduleName)" 
                      class="ml-6 pl-6 border-l border-slate-200 dark:border-zinc-700 space-y-1 mt-1 transition-all duration-300"
                    >
                      <div 
                        v-for="permission in modulePerms" 
                        :key="permission.id || permission.name" 
                        class="flex items-center py-1.5 px-3 hover:bg-indigo-50/50 dark:hover:bg-zinc-800/80 rounded-md transition-colors"
                      >
                        <!-- Child Checkbox -->
                        <input 
                          type="checkbox" 
                          :id="'roles-permission-' + (permission.id || permission.name)"
                          :value="permission.name" 
                          v-model="roleForm.permissions" 
                          class="w-4 h-4 text-indigo-600 dark:text-indigo-500 border-slate-300 dark:border-zinc-700 rounded focus:ring-indigo-500 cursor-pointer"
                        >

                        <!-- Child Label -->
                        <label 
                          :for="'roles-permission-' + (permission.id || permission.name)" 
                          class="flex items-center ml-3 cursor-pointer text-xs font-semibold text-slate-700 dark:text-zinc-300 flex-1 py-0.5 select-none"
                        >
                          <i class="fas fa-key text-indigo-400 dark:text-indigo-400 text-xs mr-2"></i>
                          <span>{{ permission.actionLabel }} <span class="text-[11px] text-slate-400 dark:text-zinc-500 font-normal ml-1">({{ permission.name }})</span></span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-slate-200 dark:border-zinc-800">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 text-xs font-semibold text-slate-700 dark:text-zinc-300 bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors cursor-pointer"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="submitting"
                class="inline-flex items-center px-5 py-2 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 rounded-xl shadow-xs transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
              >
                {{ submitting ? 'Saving...' : (showCreateModal ? 'Create Role' : 'Update Role') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

// Reactive data
const roles = ref([]);
const permissions = ref({});
const collapsedModules = ref({});
const loading = ref(false);
const submitting = ref(false);
const searchQuery = ref('');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingRole = ref(null);
const formErrors = ref([]);

// Role form
const roleForm = ref({
  name: '',
  permissions: []
});

// System roles that cannot be deleted
const systemRoles = ['admin', 'manager', 'cashier', 'user'];

// Computed properties
const filteredRoles = computed(() => {
  if (!searchQuery.value) return roles.value;
  return roles.value.filter(role =>
    role.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const formatModuleName = (rawName) => {
  if (!rawName) return 'Other';
  return rawName
    .replace(/[._]/g, ' ')
    .split(' ')
    .map(w => w.charAt(0).toUpperCase() + w.slice(1))
    .join(' ');
};

const formatActionLabel = (permName, groupKey) => {
  if (!permName) return '';
  if (permName.includes('.')) {
    const parts = permName.split('.');
    const action = parts.slice(1).join(' ').replace(/_/g, ' ');
    return action.charAt(0).toUpperCase() + action.slice(1);
  }
  if (permName.includes(' ')) {
    const parts = permName.split(' ');
    const action = parts[0];
    return action.charAt(0).toUpperCase() + action.slice(1);
  }
  const action = permName.replace(/_/g, ' ');
  return action.charAt(0).toUpperCase() + action.slice(1);
};

const groupedPermissions = computed(() => {
  const groups = {};

  if (!permissions.value) return groups;

  if (Array.isArray(permissions.value)) {
    permissions.value.forEach(p => {
      const item = typeof p === 'string' ? { id: p, name: p } : p;
      const name = item.name || '';
      
      let rawModule = 'Other';
      if (name.includes('.')) {
        rawModule = name.split('.')[0];
      } else if (name.includes(' ')) {
        const parts = name.split(' ');
        rawModule = parts.slice(1).join(' ');
      }
      
      const moduleName = formatModuleName(rawModule);
      const actionLabel = formatActionLabel(name, rawModule);

      if (!groups[moduleName]) {
        groups[moduleName] = [];
      }
      groups[moduleName].push({
        ...item,
        actionLabel
      });
    });
    return groups;
  }

  Object.keys(permissions.value).forEach(rawGroupKey => {
    const permList = permissions.value[rawGroupKey] || [];
    const moduleName = formatModuleName(rawGroupKey);

    if (!groups[moduleName]) {
      groups[moduleName] = [];
    }

    permList.forEach(p => {
      const item = typeof p === 'string' ? { id: p, name: p } : p;
      const actionLabel = formatActionLabel(item.name || '', rawGroupKey);
      groups[moduleName].push({
        ...item,
        actionLabel
      });
    });
  });

  return groups;
});

const toggleModuleCollapse = (moduleName) => {
  const currentlyCollapsed = isModuleCollapsed(moduleName);
  collapsedModules.value[moduleName] = !currentlyCollapsed;
};

const isModuleCollapsed = (moduleName) => {
  return collapsedModules.value[moduleName] !== false;
};

const isModuleFullySelected = (modulePerms) => {
  if (!modulePerms || modulePerms.length === 0) return false;
  return modulePerms.every(p => roleForm.value.permissions.includes(p.name));
};

const isModulePartiallySelected = (modulePerms) => {
  const selectedCount = getSelectedCountForModule(modulePerms);
  return selectedCount > 0 && selectedCount < modulePerms.length;
};

const getSelectedCountForModule = (modulePerms) => {
  if (!modulePerms) return 0;
  return modulePerms.filter(p => roleForm.value.permissions.includes(p.name)).length;
};

const toggleModuleSelection = (modulePerms, event) => {
  const checked = event.target.checked;
  const names = modulePerms.map(p => p.name);

  if (checked) {
    const current = new Set(roleForm.value.permissions);
    names.forEach(name => current.add(name));
    roleForm.value.permissions = Array.from(current);
  } else {
    roleForm.value.permissions = roleForm.value.permissions.filter(name => !names.includes(name));
  }
};

const totalPermissionsCount = computed(() => {
  let total = 0;
  Object.values(groupedPermissions.value).forEach(list => {
    total += list.length;
  });
  return total;
});

const isAllSelected = computed(() => {
  const total = totalPermissionsCount.value;
  return total > 0 && roleForm.value.permissions.length === total;
});

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    roleForm.value.permissions = [];
  } else {
    const allNames = [];
    Object.values(groupedPermissions.value).forEach(list => {
      list.forEach(p => allNames.push(p.name));
    });
    roleForm.value.permissions = allNames;
  }
};

// Methods
const fetchRoles = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/roles');
    roles.value = response.data;
  } catch (error) {
    console.error('Error fetching roles:', error);
  } finally {
    loading.value = false;
  }
};

const fetchPermissions = async () => {
  try {
    const response = await axios.get('/api/permissions');
    permissions.value = response.data;
  } catch (error) {
    console.error('Error fetching permissions:', error);
  }
};

const createRole = async () => {
  submitting.value = true;
  formErrors.value = [];

  try {
    await axios.post('/api/roles', roleForm.value);
    closeModal();
    fetchRoles();
  } catch (error) {
    if (error.response?.data?.errors) {
      formErrors.value = Object.values(error.response.data.errors).flat();
    } else {
      formErrors.value = [error.response?.data?.message || 'An error occurred'];
    }
  } finally {
    submitting.value = false;
  }
};

const editRole = (role) => {
  editingRole.value = role;
  roleForm.value = {
    name: role.name,
    permissions: role.permissions ? role.permissions.map(p => p.name) : []
  };
  showEditModal.value = true;
};

const updateRole = async () => {
  submitting.value = true;
  formErrors.value = [];

  try {
    await axios.put(`/api/roles/${editingRole.value.id}`, roleForm.value);
    closeModal();
    fetchRoles();
  } catch (error) {
    if (error.response?.data?.errors) {
      formErrors.value = Object.values(error.response.data.errors).flat();
    } else {
      formErrors.value = [error.response?.data?.message || 'An error occurred'];
    }
  } finally {
    submitting.value = false;
  }
};

const deleteRole = async (role) => {
  if (isSystemRole(role.name)) {
    alert('Cannot delete system role');
    return;
  }

  if (!confirm(`Are you sure you want to delete the role "${role.name}"?`)) {
    return;
  }

  try {
    await axios.delete(`/api/roles/${role.id}`);
    fetchRoles();
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to delete role');
  }
};

const isSystemRole = (roleName) => {
  return systemRoles.includes(roleName?.toLowerCase());
};

const closeModal = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  editingRole.value = null;
  roleForm.value = { name: '', permissions: [] };
  collapsedModules.value = {};
  formErrors.value = [];
};

// Lifecycle
onMounted(() => {
  fetchRoles();
  fetchPermissions();
});
</script>
