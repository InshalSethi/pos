<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Users</h1>
          <button
            @click="showCreateModal = true"
            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium"
          >
            Add User
          </button>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search users..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                @input="debouncedSearch"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
              <select
                v-model="selectedRole"
                @change="fetchUsers"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="">All Roles</option>
                <option v-for="role in roles" :key="role.id" :value="role.name">
                  {{ role.name }}
                </option>
              </select>
            </div>
            <div class="flex items-end">
              <button
                @click="clearFilters"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium"
              >
                Clear Filters
              </button>
            </div>
          </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div v-if="loading" class="p-6 text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
            <p class="mt-2 text-gray-600">Loading users...</p>
          </div>

          <div v-else-if="users.length === 0" class="p-6 text-center text-gray-500">
            No users found.
          </div>

          <table v-else class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  User
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Role
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Created
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in users" :key="user.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center">
                        <span class="text-white font-medium">{{ user.name.charAt(0).toUpperCase() }}</span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                      <div class="text-sm text-gray-500">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    v-for="role in user.roles"
                    :key="role.id"
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 mr-1"
                  >
                    {{ role.name }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(user.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button
                    @click="editUser(user)"
                    class="text-indigo-600 hover:text-indigo-900 mr-3"
                  >
                    Edit
                  </button>
                  <button
                    @click="deleteUser(user)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div v-if="pagination.last_page > 1" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="flex-1 flex justify-between sm:hidden">
                <button
                  @click="changePage(pagination.current_page - 1)"
                  :disabled="pagination.current_page === 1"
                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                >
                  Previous
                </button>
                <button
                  @click="changePage(pagination.current_page + 1)"
                  :disabled="pagination.current_page === pagination.last_page"
                  class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                >
                  Next
                </button>
              </div>
              <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm text-gray-700">
                    Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
                  </p>
                </div>
                <div>
                  <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <button
                      v-for="page in visiblePages"
                      :key="page"
                      @click="changePage(page)"
                      :class="[
                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                        page === pagination.current_page
                          ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                          : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                      ]"
                    >
                      {{ page }}
                    </button>
                  </nav>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit User Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ showCreateModal ? 'Create User' : 'Edit User' }}
          </h3>

          <form @submit.prevent="showCreateModal ? createUser() : updateUser()">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
              <input
                v-model="userForm.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
              <input
                v-model="userForm.email"
                type="email"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
              <select
                v-model="userForm.role"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="">Select Role</option>
                <option v-for="role in roles" :key="role.id" :value="role.name">
                  {{ role.name }}
                </option>
              </select>
            </div>

            <div v-if="showCreateModal || userForm.password" class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Password {{ showEditModal ? '(leave blank to keep current)' : '' }}
              </label>
              <input
                v-model="userForm.password"
                type="password"
                :required="showCreateModal"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>

            <div v-if="userForm.password" class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
              <input
                v-model="userForm.password_confirmation"
                type="password"
                :required="!!userForm.password"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>

            <div v-if="formErrors.length > 0" class="mb-4">
              <div class="bg-red-50 border border-red-200 rounded-md p-3">
                <ul class="text-sm text-red-600">
                  <li v-for="error in formErrors" :key="error">{{ error }}</li>
                </ul>
              </div>
            </div>

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
                class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ submitting ? 'Saving...' : (showCreateModal ? 'Create' : 'Update') }}
              </button>
            </div>
          </form>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

// Reactive data
const users = ref([]);
const roles = ref([]);
const loading = ref(false);
const submitting = ref(false);
const searchQuery = ref('');
const selectedRole = ref('');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingUser = ref(null);
const formErrors = ref([]);

// Pagination
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
});

// User form
const userForm = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: ''
});

// Computed
const visiblePages = computed(() => {
  const pages = [];
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;

  // Show up to 5 pages around current page
  const start = Math.max(1, current - 2);
  const end = Math.min(last, current + 2);

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }

  return pages;
});

// Methods
const fetchUsers = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      per_page: pagination.value.per_page
    };

    if (searchQuery.value) {
      params.search = searchQuery.value;
    }

    if (selectedRole.value) {
      params.role = selectedRole.value;
    }

    const response = await axios.get('/api/users', { params });
    users.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
      from: response.data.from,
      to: response.data.to
    };
  } catch (error) {
    console.error('Error fetching users:', error);
  } finally {
    loading.value = false;
  }
};

const fetchRoles = async () => {
  try {
    const response = await axios.get('/api/roles');
    roles.value = response.data;
  } catch (error) {
    console.error('Error fetching roles:', error);
  }
};

const createUser = async () => {
  submitting.value = true;
  formErrors.value = [];

  try {
    await axios.post('/api/users', userForm.value);
    closeModal();
    fetchUsers();
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

const editUser = (user) => {
  editingUser.value = user;
  userForm.value = {
    name: user.name,
    email: user.email,
    password: '',
    password_confirmation: '',
    role: user.roles[0]?.name || ''
  };
  showEditModal.value = true;
};

const updateUser = async () => {
  submitting.value = true;
  formErrors.value = [];

  try {
    await axios.put(`/api/users/${editingUser.value.id}`, userForm.value);
    closeModal();
    fetchUsers();
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

const deleteUser = async (user) => {
  if (!confirm(`Are you sure you want to delete ${user.name}?`)) {
    return;
  }

  try {
    await axios.delete(`/api/users/${user.id}`);
    fetchUsers();
  } catch (error) {
    alert(error.response?.data?.message || 'An error occurred');
  }
};

const closeModal = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  editingUser.value = null;
  userForm.value = {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: ''
  };
  formErrors.value = [];
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchUsers(page);
  }
};

const clearFilters = () => {
  searchQuery.value = '';
  selectedRole.value = '';
  fetchUsers();
};

// Debounced search
let searchTimeout;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchUsers();
  }, 500);
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString();
};

// Lifecycle
onMounted(() => {
  fetchUsers();
  fetchRoles();
});
</script>
