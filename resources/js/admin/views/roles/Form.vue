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
              <div class="flex justify-between items-center mb-3">
                  <label class="block text-sm font-bold text-gray-700">Assign Permissions</label>
                  <button type="button" @click="selectAll" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                      {{ form.permissions.length === permissions.length && permissions.length > 0 ? 'Deselect All' : 'Select All' }}
                  </button>
              </div>
              
              <div v-if="permissions.length === 0" class="text-sm text-gray-500 italic p-4 bg-gray-50 rounded border border-gray-100">
                  No permissions are registered in the system.
              </div>
              <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                  <label v-for="permission in permissions" :key="permission.id" class="flex items-start p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-indigo-50 transition-colors" :class="{'bg-indigo-50 border-indigo-200': form.permissions.includes(permission.name)}">
                      <input type="checkbox" :value="permission.name" v-model="form.permissions" class="w-4 h-4 mt-0.5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                      <span class="ml-3 text-sm font-medium text-gray-700">{{ permission.name }}</span>
                  </label>
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
