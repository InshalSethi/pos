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
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                  </div>
                </div>
                <div>
                  <h3 class="text-lg font-bold text-white">{{ isEdit ? 'Edit Role' : 'Add New Role' }}</h3>
                  <p class="text-slate-400 text-xs font-medium">{{ isEdit ? 'Update role settings and permissions' : 'Create a new user access role' }}</p>
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
            <form @submit.prevent="saveRole" class="p-6">
              <div class="space-y-6">
                <!-- Role Name -->
                <div>
                  <label for="name" class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1">Role Name *</label>
                  <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="block w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none"
                    :class="{ 'border-rose-400 focus:ring-rose-500': errors.name }"
                    required
                    placeholder="e.g. inventory-manager"
                  />
                  <p v-if="errors.name" class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ errors.name[0] }}</p>
                </div>

                <!-- Description -->
                <div>
                  <label for="description" class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1">Description</label>
                  <input
                    id="description"
                    v-model="form.description"
                    type="text"
                    class="block w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-medium text-slate-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none"
                    placeholder="Short description of this role's purpose"
                  />
                </div>

                <!-- Tree View Permissions Selection -->
                <div>
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
                    <!-- Treeview Component -->
                    <div class="treeview select-none">
                      <!-- Root Nodes (Modules) -->
                      <div v-for="(modulePerms, moduleName) in groupedPermissions" :key="moduleName" class="treeview-node mb-2.5 last:mb-0">
                        <!-- Parent Item Row -->
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
                            :id="'parent-' + moduleName" 
                            :checked="isModuleFullySelected(modulePerms)" 
                            :ref="el => { if (el) el.indeterminate = isModulePartiallySelected(modulePerms); }"
                            @change="toggleModuleSelection(modulePerms, $event)"
                            class="w-4 h-4 ml-1 text-indigo-600 dark:text-indigo-500 border-slate-300 dark:border-zinc-700 rounded focus:ring-indigo-500 cursor-pointer"
                          >

                          <!-- Parent Icon & Label -->
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

                        <!-- Child Nodes (Permissions) -->
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
                              :id="'permission-' + (permission.id || permission.name)"
                              :value="permission.name" 
                              v-model="form.permissions" 
                              class="w-4 h-4 text-indigo-600 dark:text-indigo-500 border-slate-300 dark:border-zinc-700 rounded focus:ring-indigo-500 cursor-pointer"
                            >

                            <!-- Child Icon & Label -->
                            <label 
                              :for="'permission-' + (permission.id || permission.name)" 
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
                  <p v-if="errors.permissions" class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ errors.permissions[0] }}</p>
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
                  {{ saving ? 'Saving...' : (isEdit ? 'Update Role' : 'Create Role') }}
                </button>
              </div>
            </form>
          </div>
        </div>
    </div>
  </Teleport>
</template>

<script>
import { ref, reactive, watch, computed, onMounted } from 'vue';
import { useToast } from '@/composables/useToast';
import axios from 'axios';

export default {
  name: 'RoleCreateForm',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    role: {
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
    const permissions = ref({});
    const collapsedModules = ref({});

    const form = reactive({
      name: '',
      description: '',
      permissions: []
    });

    const systemRoles = ['admin', 'manager', 'cashier', 'user'];
    const isSystemRole = (roleName) => {
      return systemRoles.includes(roleName?.toLowerCase());
    };

    const fetchPermissions = async () => {
      try {
        const response = await axios.get('/api/permissions');
        permissions.value = response.data;
      } catch (error) {
        console.error('Error fetching permissions:', error);
      }
    };

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
      return modulePerms.every(p => form.permissions.includes(p.name));
    };

    const isModulePartiallySelected = (modulePerms) => {
      const selectedCount = getSelectedCountForModule(modulePerms);
      return selectedCount > 0 && selectedCount < modulePerms.length;
    };

    const getSelectedCountForModule = (modulePerms) => {
      if (!modulePerms) return 0;
      return modulePerms.filter(p => form.permissions.includes(p.name)).length;
    };

    const toggleModuleSelection = (modulePerms, event) => {
      const checked = event.target.checked;
      const names = modulePerms.map(p => p.name);

      if (checked) {
        const current = new Set(form.permissions);
        names.forEach(name => current.add(name));
        form.permissions = Array.from(current);
      } else {
        form.permissions = form.permissions.filter(name => !names.includes(name));
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
      return total > 0 && form.permissions.length === total;
    });

    const toggleSelectAll = () => {
      if (isAllSelected.value) {
        form.permissions = [];
      } else {
        const allNames = [];
        Object.values(groupedPermissions.value).forEach(list => {
          list.forEach(p => allNames.push(p.name));
        });
        form.permissions = allNames;
      }
    };

    const resetForm = () => {
      form.name = '';
      form.description = '';
      form.permissions = [];
      collapsedModules.value = {};
      errors.value = {};
    };

    const loadRoleData = () => {
      if (props.role && props.isEdit) {
        form.name = props.role.name || '';
        form.description = props.role.description || '';
        if (props.role.permissions) {
          form.permissions = props.role.permissions.map(p => p.name);
        } else {
          form.permissions = [];
        }
      }
    };

    const saveRole = async () => {
      saving.value = true;
      errors.value = {};

      try {
        const url = props.isEdit ? `/api/roles/${props.role.id}` : '/api/roles';
        const method = props.isEdit ? 'put' : 'post';

        await axios[method](url, form);

        showToast(
          props.isEdit ? 'Role updated successfully' : 'Role created successfully',
          'success'
        );

        emit('saved');
        emit('close');
      } catch (error) {
        if (error.response?.status === 422) {
          errors.value = error.response.data.errors;
        } else {
          showToast(error.response?.data?.message || 'Error saving role', 'error');
        }
      } finally {
        saving.value = false;
      }
    };

    watch(() => props.show, (newVal) => {
      if (newVal) {
        resetForm();
        loadRoleData();
      }
    });

    onMounted(() => {
      fetchPermissions();
    });

    return {
      form,
      errors,
      saving,
      permissions,
      groupedPermissions,
      collapsedModules,
      isModuleCollapsed,
      toggleModuleCollapse,
      isModuleFullySelected,
      isModulePartiallySelected,
      getSelectedCountForModule,
      toggleModuleSelection,
      isAllSelected,
      toggleSelectAll,
      isSystemRole,
      saveRole
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
