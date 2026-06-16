<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden max-w-3xl mx-auto">
      <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
          <h3 class="text-lg font-bold text-gray-800">{{ isEditing ? 'Edit Role' : 'Add New Role' }}</h3>
          <router-link :to="{ name: 'admin.roles.index' }" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition-colors">
              <i class="fas fa-arrow-left mr-2"></i> Back
          </router-link>
      </div>
      
      <div v-if="loading" class="p-12 text-center text-indigo-500">
          <i class="fas fa-circle-notch fa-spin text-4xl"></i>
          <p class="mt-4 font-medium text-gray-500">Loading role data...</p>
      </div>

      <form v-else @submit.prevent="submitForm" class="p-6 space-y-6">
          <div v-if="errorMessage" class="bg-red-50 text-red-600 p-4 rounded-lg text-sm font-medium border border-red-100 flex items-start">
              <i class="fas fa-exclamation-circle mt-0.5 mr-3"></i>
              <div>
                  <p>{{ errorMessage }}</p>
                  <ul v-if="validationErrors && Object.keys(validationErrors).length > 0" class="mt-2 list-disc list-inside space-y-1">
                      <li v-for="(errors, field) in validationErrors" :key="field">
                          {{ errors[0] }}
                      </li>
                  </ul>
              </div>
          </div>

          <!-- Role Name -->
          <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Role Name <span class="text-red-500">*</span></label>
              <input type="text" v-model="form.name" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-sm outline-none bg-white text-gray-800" placeholder="e.g. Editor">
              <p class="text-xs text-gray-500 mt-1">Provide a unique name for this role.</p>
          </div>

          <!-- Permissions Selection -->
          <div class="border-t border-gray-100 pt-6">
              <div class="flex justify-between items-center mb-4">
                  <div>
                      <label class="block text-sm font-bold text-gray-700">Assign Permissions</label>
                      <p class="text-xs text-gray-500 mt-0.5">Organized by module. Select modules or individual action permissions.</p>
                  </div>
                  <button type="button" @click="selectAll" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                      {{ form.permissions.length === permissions.length && permissions.length > 0 ? 'Deselect All' : 'Select All' }}
                  </button>
              </div>
              
              <div v-if="permissions.length === 0" class="text-sm text-gray-500 italic p-4 bg-gray-50 rounded border border-gray-100">
                  No permissions are registered in the system.
              </div>
              
              <div v-else class="space-y-3 bg-gray-50 border border-gray-200 rounded-xl p-4 md:p-6 shadow-sm">
                  <!-- Custom Treeview Component (Vuetify-style) -->
                  <div class="treeview select-none">
                      <!-- Root Nodes (Modules) -->
                      <div v-for="(modulePerms, moduleName) in groupedPermissions" :key="moduleName" class="treeview-node mb-3">
                          <!-- Parent Item Row -->
                          <div class="flex items-center py-2 px-3 hover:bg-gray-200/50 rounded-lg transition-colors duration-150">
                              <!-- Expand/Collapse Chevron -->
                              <button 
                                  type="button" 
                                  @click="toggleModuleCollapse(moduleName)" 
                                  class="w-6 h-6 flex items-center justify-center text-gray-400 hover:text-gray-600 rounded transition-transform duration-200 focus:outline-none"
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
                                  class="w-4 h-4 ml-1 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer"
                              >

                              <!-- Parent Icon & Label -->
                              <div @click="toggleModuleCollapse(moduleName)" class="flex items-center ml-2.5 cursor-pointer flex-1 py-1">
                                  <i 
                                      :class="[isModuleCollapsed(moduleName) ? 'far fa-folder text-amber-500' : 'far fa-folder-open text-amber-500']" 
                                      class="text-base mr-2"
                                  ></i>
                                  <span class="font-bold text-gray-800 text-sm">{{ moduleName }} Module</span>
                                  <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700">
                                      {{ getSelectedCountForModule(modulePerms) }} / {{ modulePerms.length }} selected
                                  </span>
                              </div>
                          </div>

                          <!-- Child Nodes (Permissions) -->
                          <div 
                              v-show="!isModuleCollapsed(moduleName)" 
                              class="ml-6 pl-6 border-l border-gray-200 space-y-1.5 mt-1 transition-all duration-300"
                          >
                              <div 
                                  v-for="permission in modulePerms" 
                                  :key="permission.id" 
                                  class="flex items-center py-1.5 px-3 hover:bg-indigo-50/40 rounded-md transition-colors"
                              >
                                  <!-- Child Checkbox -->
                                  <input 
                                      type="checkbox" 
                                      :id="'permission-' + permission.id"
                                      :value="permission.name" 
                                      v-model="form.permissions" 
                                      class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer"
                                  >

                                  <!-- Child Icon & Label -->
                                  <label 
                                      :for="'permission-' + permission.id" 
                                      class="flex items-center ml-3 cursor-pointer text-sm font-semibold text-gray-600 flex-1 py-0.5 select-none"
                                  >
                                      <i class="fas fa-key text-indigo-400 text-xs mr-2"></i>
                                      <span>{{ permission.actionLabel }} <span class="text-xs text-gray-400 font-normal ml-1">({{ permission.name }})</span></span>
                                  </label>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
          </div>

          <!-- Submit Button -->
          <div class="border-t border-gray-100 pt-6 flex justify-end">
              <button type="submit" :disabled="submitting" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-md transition-colors disabled:opacity-50 flex items-center">
                  <i v-if="submitting" class="fas fa-spinner fa-spin mr-2"></i>
                  <i v-else class="fas fa-save mr-2"></i>
                  {{ isEditing ? 'Update Role' : 'Create Role' }}
              </button>
          </div>
      </form>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const isEditing = computed(() => !!route.params.id);
const loading = ref(false);
const submitting = ref(false);
const errorMessage = ref('');
const validationErrors = ref({});

const permissions = ref([]);

const form = ref({
    name: '',
    permissions: []
});

const collapsedModules = ref({});

const toggleModuleCollapse = (moduleName) => {
    collapsedModules.value[moduleName] = !collapsedModules.value[moduleName];
};

const isModuleCollapsed = (moduleName) => {
    return !!collapsedModules.value[moduleName];
};

const groupedPermissions = computed(() => {
    const groups = {};
    permissions.value.forEach(p => {
        const name = p.name;
        let moduleName = 'Other';
        let actionLabel = name;
        
        // Match standard format "[action] [module]" (e.g., "create users", "view dashboard")
        const parts = name.split(' ');
        if (parts.length >= 2) {
            const action = parts[0];
            const modulePart = parts.slice(1).join(' ');
            
            // Capitalize modulePart for display
            moduleName = modulePart.charAt(0).toUpperCase() + modulePart.slice(1);
            actionLabel = action.charAt(0).toUpperCase() + action.slice(1);
        }
        
        if (!groups[moduleName]) {
            groups[moduleName] = [];
        }
        groups[moduleName].push({
            ...p,
            actionLabel
        });
    });
    return groups;
});

const isModuleFullySelected = (modulePerms) => {
    return modulePerms.every(p => form.value.permissions.includes(p.name));
};

const isModulePartiallySelected = (modulePerms) => {
    const selectedCount = getSelectedCountForModule(modulePerms);
    return selectedCount > 0 && selectedCount < modulePerms.length;
};

const getSelectedCountForModule = (modulePerms) => {
    return modulePerms.filter(p => form.value.permissions.includes(p.name)).length;
};

const toggleModuleSelection = (modulePerms, event) => {
    const checked = event.target.checked;
    const names = modulePerms.map(p => p.name);
    
    if (checked) {
        names.forEach(name => {
            if (!form.value.permissions.includes(name)) {
                form.value.permissions.push(name);
            }
        });
    } else {
        form.value.permissions = form.value.permissions.filter(name => !names.includes(name));
    }
};

const loadPermissions = async () => {
    try {
        const { data } = await axios.get('/admin/api/options/permissions');
        permissions.value = data;
    } catch (e) {
        console.error('Failed to load permissions', e);
    }
};

const loadRole = async () => {
    loading.value = true;
    try {
        const { data } = await axios.get(`/admin/api/roles/${route.params.id}`);
        const role = data.data;
        form.value.name = role.name;
        form.value.permissions = role.permissions_list || [];
    } catch (e) {
        errorMessage.value = 'Failed to load role data. It may have been deleted.';
    } finally {
        loading.value = false;
    }
};

onMounted(async () => {
    await loadPermissions();
    if (isEditing.value) {
        await loadRole();
    }
});

const selectAll = () => {
    if (form.value.permissions.length === permissions.value.length) {
        form.value.permissions = [];
    } else {
        form.value.permissions = permissions.value.map(p => p.name);
    }
};

const submitForm = async () => {
    submitting.value = true;
    errorMessage.value = '';
    validationErrors.value = {};

    try {
        if (isEditing.value) {
            await axios.put(`/admin/api/roles/${route.params.id}`, form.value);
        } else {
            await axios.post('/admin/api/roles', form.value);
        }
        router.push({ name: 'admin.roles.index' });
    } catch (e) {
        if (e.response && e.response.status === 422) {
            errorMessage.value = 'Please correct the errors below.';
            validationErrors.value = e.response.data.errors;
        } else {
            errorMessage.value = e.response?.data?.message || 'An unexpected error occurred.';
        }
    } finally {
        submitting.value = false;
    }
};
</script>
