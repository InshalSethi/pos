<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Role Management</h1>
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
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          />
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        <p class="mt-2 text-gray-600">Loading roles...</p>
      </div>

      <!-- Roles Table -->
      <div v-else class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul class="divide-y divide-gray-200">
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
                    <div class="text-sm font-medium text-gray-900">{{ role.name }}</div>
                    <div class="text-sm text-gray-500">
                      {{ role.permissions.length }} permissions assigned
                    </div>
                  </div>
                </div>
              </div>
              <div class="flex space-x-2">
                <button
                  @click="editRole(role)"
                  class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
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
                      : 'text-red-600 hover:text-red-900'
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
        <p class="text-gray-500">No roles found.</p>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ showCreateModal ? 'Create New Role' : 'Edit Role' }}
          </h3>

          <!-- Error Messages -->
          <div v-if="formErrors.length > 0" class="mb-4 bg-red-50 border border-red-200 rounded-md p-3">
            <ul class="text-sm text-red-600">
              <li v-for="error in formErrors" :key="error">{{ error }}</li>
            </ul>
          </div>

          <form @submit.prevent="showCreateModal ? createRole() : updateRole()">
            <!-- Role Name -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Role Name</label>
              <input
                v-model="roleForm.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Enter role name"
              />
            </div>

            <!-- Permissions -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
              <div class="max-h-60 overflow-y-auto border border-gray-300 rounded-md p-3">
                <div v-for="(permissions, group) in groupedPermissions" :key="group" class="mb-4">
                  <h4 class="font-medium text-gray-900 mb-2 capitalize">{{ group }}</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <label v-for="permission in permissions" :key="permission.name" class="flex items-center">
                      <input
                        type="checkbox"
                        :value="permission.name"
                        v-model="roleForm.permissions"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                      />
                      <span class="ml-2 text-sm text-gray-700">{{ permission.name }}</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="submitting"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 disabled:opacity-50"
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

const groupedPermissions = computed(() => {
  return permissions.value;
});

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
    permissions: role.permissions.map(p => p.name)
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
  return systemRoles.includes(roleName);
};

const closeModal = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  editingRole.value = null;
  roleForm.value = { name: '', permissions: [] };
  formErrors.value = [];
};

// Lifecycle
onMounted(() => {
  fetchRoles();
  fetchPermissions();
});
</script>
