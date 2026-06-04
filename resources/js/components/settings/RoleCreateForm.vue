<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-[9999] overflow-y-auto">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-black bg-opacity-50" @click="$emit('close')"></div>
      
      <!-- Modal -->
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-4xl bg-white rounded-lg shadow-xl">
          <!-- Header -->
          <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4 rounded-t-lg">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                  <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                  </div>
                </div>
                <div>
                  <h3 class="text-xl font-semibold text-white">{{ isEdit ? 'Edit Role' : 'Add New Role' }}</h3>
                  <p class="text-indigo-100">{{ isEdit ? 'Update role settings and permissions' : 'Create a new user access role' }}</p>
                </div>
              </div>
              <button @click="$emit('close')" class="text-white hover:text-gray-200 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Content -->
          <div class="max-h-[70vh] overflow-y-auto">
            <form @submit.prevent="saveRole" class="p-6">
              <div class="space-y-6">
                <!-- Role Name -->
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Role Name *</label>
                  <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white text-gray-900"
                    :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.name }"
                    required
                    placeholder="e.g. inventory-manager"
                  />
                  <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
                </div>

                <!-- Description -->
                <div>
                  <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                  <input
                    id="description"
                    v-model="form.description"
                    type="text"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white text-gray-900"
                    placeholder="Short description of this role's purpose"
                  />
                </div>

                <!-- Permissions Selection -->
                <div>
                  <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-semibold text-gray-800">Permissions Access</label>
                    <div class="flex space-x-2 text-xs">
                      <button type="button" @click="selectAllPermissions" class="text-indigo-600 hover:text-indigo-800 font-medium">Select All</button>
                      <span class="text-gray-300">|</span>
                      <button type="button" @click="deselectAllPermissions" class="text-gray-600 hover:text-gray-800 font-medium">Clear All</button>
                    </div>
                  </div>
                  
                  <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 max-h-[35vh] overflow-y-auto space-y-6">
                    <div v-for="(groupPermissions, groupName) in permissions" :key="groupName" class="border-b border-gray-200 pb-4 last:border-b-0 last:pb-0">
                      <div class="flex justify-between items-center mb-2">
                        <h4 class="text-sm font-bold text-gray-900 capitalize">{{ groupName }}</h4>
                        <button type="button" @click="toggleGroupPermissions(groupName, groupPermissions)" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                          Toggle Group
                        </button>
                      </div>
                      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <label v-for="permission in groupPermissions" :key="permission.id" class="relative flex items-start cursor-pointer select-none">
                          <div class="flex h-5 items-center">
                            <input
                              type="checkbox"
                              :value="permission.name"
                              v-model="form.permissions"
                              class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            />
                          </div>
                          <div class="ml-3 text-sm">
                            <span class="font-medium text-gray-700">{{ permission.name }}</span>
                          </div>
                        </label>
                      </div>
                    </div>
                  </div>
                  <p v-if="errors.permissions" class="mt-1 text-sm text-red-600">{{ errors.permissions[0] }}</p>
                </div>
              </div>

              <!-- Footer -->
              <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 mt-6">
                <button 
                  type="button" 
                  @click="$emit('close')" 
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  Cancel
                </button>
                <button 
                  type="submit" 
                  :disabled="saving" 
                  class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
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
    </div>
  </Teleport>
</template>

<script>
import { ref, reactive, watch, onMounted } from 'vue';
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

    const resetForm = () => {
      form.name = '';
      form.description = '';
      form.permissions = [];
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

    const selectAllPermissions = () => {
      const allList = [];
      Object.values(permissions.value).forEach(perms => {
        perms.forEach(p => {
          allList.push(p.name);
        });
      });
      form.permissions = allList;
    };

    const deselectAllPermissions = () => {
      form.permissions = [];
    };

    const toggleGroupPermissions = (groupName, groupPermissions) => {
      const groupNames = groupPermissions.map(p => p.name);
      const allSelected = groupNames.every(name => form.permissions.includes(name));

      if (allSelected) {
        // Deselect all in group
        form.permissions = form.permissions.filter(name => !groupNames.includes(name));
      } else {
        // Select all in group, avoiding duplicates
        const current = new Set(form.permissions);
        groupNames.forEach(name => current.add(name));
        form.permissions = Array.from(current);
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
      isSystemRole,
      saveRole,
      selectAllPermissions,
      deselectAllPermissions,
      toggleGroupPermissions
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
