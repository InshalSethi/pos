<template>
  <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-800 overflow-hidden max-w-3xl mx-auto">
      <div class="p-6 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center bg-zinc-50/50 dark:bg-zinc-900/50">
          <h3 class="text-lg font-black text-zinc-950 dark:text-white tracking-tight">{{ isEditing ? 'Edit Role' : 'Add New Role' }}</h3>
          <router-link :to="{ name: 'admin.roles.index' }" class="bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 font-bold px-4 py-2 rounded-xl text-xs transition-all flex items-center cursor-pointer">
              <i class="fas fa-arrow-left mr-2 text-[10px]"></i> Back
          </router-link>
      </div>
      
      <div v-if="loading" class="p-12 text-center text-zinc-900 dark:text-white">
          <i class="fas fa-circle-notch fa-spin text-3xl"></i>
          <p class="mt-3 font-bold text-xs uppercase tracking-wider text-zinc-400">Loading role data...</p>
      </div>

      <form v-else @submit.prevent="submitForm" class="p-6 space-y-6">
          <div v-if="errorMessage" class="bg-rose-50 border border-rose-200 text-rose-700 dark:bg-rose-950/40 dark:border-rose-900 dark:text-rose-400 p-4 rounded-xl text-xs font-bold flex items-start">
              <i class="fas fa-exclamation-circle mt-0.5 mr-3 text-sm"></i>
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
              <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">Role Name <span class="text-rose-500">*</span></label>
              <input type="text" v-model="form.name" required class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold placeholder-zinc-400 dark:placeholder-zinc-600" placeholder="e.g. Editor">
              <p class="text-[11px] font-medium text-zinc-400 mt-1">Provide a unique name for this role.</p>
          </div>

          <!-- Permissions Selection -->
          <div class="border-t border-zinc-100 dark:border-zinc-800 pt-6">
              <div class="flex justify-between items-center mb-4">
                  <div>
                      <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">Assign Permissions</label>
                      <p class="text-[11px] font-medium text-zinc-400 mt-0.5">Organized by module. Select modules or individual action permissions.</p>
                  </div>
                  <button type="button" @click="selectAll" class="text-xs font-extrabold text-black dark:text-white hover:underline cursor-pointer transition-all">
                      {{ form.permissions.length === permissions.length && permissions.length > 0 ? 'Deselect All' : 'Select All' }}
                  </button>
              </div>
              
              <div v-if="permissions.length === 0" class="text-xs text-zinc-400 italic p-4 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800">
                  No permissions are registered in the system.
              </div>
              
              <div v-else class="space-y-3 bg-zinc-50/50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-4 md:p-6 shadow-xs">
                  <!-- Custom Treeview Component -->
                  <div class="treeview select-none">
                      <!-- Root Nodes (Modules) -->
                      <div v-for="(modulePerms, moduleName) in groupedPermissions" :key="moduleName" class="treeview-node mb-3">
                          <!-- Parent Item Row -->
                          <div class="flex items-center py-2 px-3 hover:bg-zinc-200/50 dark:hover:bg-zinc-800/60 rounded-xl transition-colors duration-150">
                              <!-- Expand/Collapse Chevron -->
                              <button 
                                  type="button" 
                                  @click="toggleModuleCollapse(moduleName)" 
                                  class="w-6 h-6 flex items-center justify-center text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 rounded transition-transform duration-200 focus:outline-none cursor-pointer"
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
                                  class="w-4 h-4 ml-1 text-black dark:text-white border-zinc-300 dark:border-zinc-700 rounded focus:ring-black/10 cursor-pointer"
                              >

                              <!-- Parent Icon & Label -->
                              <div @click="toggleModuleCollapse(moduleName)" class="flex items-center ml-2.5 cursor-pointer flex-1 py-1">
                                  <i 
                                      :class="[isModuleCollapsed(moduleName) ? 'far fa-folder text-zinc-400 dark:text-zinc-500' : 'far fa-folder-open text-zinc-900 dark:text-white']" 
                                      class="text-base mr-2"
                                  ></i>
                                  <span class="font-extrabold text-zinc-900 dark:text-white text-xs">{{ moduleName }} Module</span>
                                  <span class="ml-2.5 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-zinc-200 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 uppercase tracking-wider">
                                      {{ getSelectedCountForModule(modulePerms) }} / {{ modulePerms.length }} selected
                                  </span>
                              </div>
                          </div>

                          <!-- Child Nodes (Permissions) -->
                          <div 
                              v-show="!isModuleCollapsed(moduleName)" 
                              class="ml-6 pl-6 border-l border-zinc-200 dark:border-zinc-800 space-y-1 mt-1 transition-all duration-300"
                          >
                              <div 
                                  v-for="permission in modulePerms" 
                                  :key="permission.id" 
                                  class="flex items-center py-1.5 px-3 hover:bg-zinc-200/40 dark:hover:bg-zinc-800/40 rounded-lg transition-colors"
                              >
                                  <!-- Child Checkbox -->
                                  <input 
                                      type="checkbox" 
                                      :id="'permission-' + permission.id"
                                      :value="permission.name" 
                                      v-model="form.permissions" 
                                      class="w-4 h-4 text-black dark:text-white border-zinc-300 dark:border-zinc-700 rounded focus:ring-black/10 cursor-pointer"
                                  >

                                  <!-- Child Icon & Label -->
                                  <label 
                                      :for="'permission-' + permission.id" 
                                      class="flex items-center ml-3 cursor-pointer text-xs font-bold text-zinc-700 dark:text-zinc-300 flex-1 py-0.5 select-none"
                                  >
                                      <i class="fas fa-key text-zinc-400 dark:text-zinc-500 text-[10px] mr-2"></i>
                                      <span>{{ permission.actionLabel }} <span class="text-[10px] text-zinc-400 dark:text-zinc-500 font-normal ml-1">({{ permission.name }})</span></span>
                                  </label>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
          </div>

          <!-- Submit Button -->
          <div class="border-t border-zinc-100 dark:border-zinc-800 pt-6 flex justify-end">
              <button type="submit" :disabled="submitting" class="bg-black hover:bg-zinc-800 text-white dark:bg-white dark:hover:bg-zinc-200 dark:text-black font-extrabold px-6 py-2.5 rounded-xl text-xs shadow-xs transition-all disabled:opacity-50 flex items-center cursor-pointer">
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

