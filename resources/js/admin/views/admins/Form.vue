<template>
  <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-800 overflow-hidden max-w-3xl mx-auto">
      <div class="p-6 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center bg-zinc-50/50 dark:bg-zinc-900/50">
          <h3 class="text-lg font-black text-zinc-950 dark:text-white tracking-tight">{{ isEditing ? 'Edit Admin' : 'Add New Admin' }}</h3>
          <router-link :to="{ name: 'admin.admins.index' }" class="bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 font-bold px-4 py-2 rounded-xl text-xs transition-all flex items-center cursor-pointer">
              <i class="fas fa-arrow-left mr-2 text-[10px]"></i> Back
          </router-link>
      </div>
      
      <div v-if="loading" class="p-12 text-center text-zinc-900 dark:text-white">
          <i class="fas fa-circle-notch fa-spin text-3xl"></i>
          <p class="mt-3 font-bold text-xs uppercase tracking-wider text-zinc-400">Loading admin data...</p>
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

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Name -->
              <div>
                  <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">Full Name <span class="text-rose-500">*</span></label>
                  <input type="text" v-model="form.name" required class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold placeholder-zinc-400 dark:placeholder-zinc-600" placeholder="e.g. John Doe">
              </div>

              <!-- Email -->
              <div>
                  <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">Email Address <span class="text-rose-500">*</span></label>
                  <input type="email" v-model="form.email" required class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold placeholder-zinc-400 dark:placeholder-zinc-600" placeholder="admin@example.com">
              </div>

              <!-- Phone -->
              <div>
                  <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">Phone Number</label>
                  <input type="text" v-model="form.phone" class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold placeholder-zinc-400 dark:placeholder-zinc-600" placeholder="+1 (555) 000-0000">
              </div>

              <!-- Password -->
              <div>
                  <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-2">Password <span v-if="!isEditing" class="text-rose-500">*</span></label>
                  <input type="password" v-model="form.password" :required="!isEditing" class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-800 rounded-xl focus:ring-2 focus:ring-black/10 dark:focus:ring-white/10 focus:border-black dark:focus:border-white transition-all shadow-xs outline-none bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white text-xs font-bold placeholder-zinc-400 dark:placeholder-zinc-600" :placeholder="isEditing ? 'Leave blank to keep current password' : 'Create a secure password'">
                  <p v-if="isEditing" class="text-[11px] font-medium text-zinc-400 mt-1">Leave blank to keep the current password.</p>
              </div>
          </div>

          <!-- Roles Selection -->
          <div class="border-t border-zinc-100 dark:border-zinc-800 pt-6">
              <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-3">Assign Roles <span class="text-rose-500">*</span></label>
              <div v-if="roles.length === 0" class="text-xs text-zinc-400 italic">No roles available. Please create a role first.</div>
              <div v-else class="grid grid-cols-2 md:grid-cols-3 gap-3">
                  <label v-for="role in roles" :key="role.id" class="flex items-center p-3 border rounded-xl cursor-pointer transition-all" :class="[form.roles.includes(role.name) ? 'bg-zinc-100 dark:bg-zinc-800 border-black dark:border-white text-zinc-950 dark:text-white font-extrabold' : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800/40']">
                      <input type="checkbox" :value="role.name" v-model="form.roles" class="w-4 h-4 text-black dark:text-white border-zinc-300 dark:border-zinc-700 rounded focus:ring-black/10 cursor-pointer">
                      <span class="ml-3 text-xs font-bold">{{ role.name }}</span>
                  </label>
              </div>
          </div>

          <!-- Active Status -->
          <div class="border-t border-zinc-100 dark:border-zinc-800 pt-6">
              <label class="flex items-center cursor-pointer">
                  <div class="relative">
                      <input type="checkbox" v-model="form.is_active" class="sr-only">
                      <div class="block bg-zinc-200 dark:bg-zinc-800 w-10 h-6 rounded-full transition-colors" :class="{'bg-black dark:bg-white': form.is_active}"></div>
                      <div class="dot absolute left-1 top-1 bg-white dark:bg-zinc-900 w-4 h-4 rounded-full transition-transform" :class="{'transform translate-x-4': form.is_active}"></div>
                  </div>
                  <div class="ml-3 text-xs font-extrabold uppercase tracking-wider text-zinc-800 dark:text-zinc-200">
                      Account Active Status
                  </div>
              </label>
              <p class="text-[11px] font-medium text-zinc-400 mt-1 ml-14">Inactive accounts cannot log into the admin panel.</p>
          </div>

          <!-- Submit Button -->
          <div class="border-t border-zinc-100 dark:border-zinc-800 pt-6 flex justify-end">
              <button type="submit" :disabled="submitting" class="bg-black hover:bg-zinc-800 text-white dark:bg-white dark:hover:bg-zinc-200 dark:text-black font-extrabold px-6 py-2.5 rounded-xl text-xs shadow-xs transition-all disabled:opacity-50 flex items-center cursor-pointer">
                  <i v-if="submitting" class="fas fa-spinner fa-spin mr-2"></i>
                  <i v-else class="fas fa-save mr-2"></i>
                  {{ isEditing ? 'Update Admin' : 'Create Admin' }}
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

const roles = ref([]);

const form = ref({
    name: '',
    email: '',
    phone: '',
    password: '',
    roles: [],
    is_active: true
});

const loadDropdowns = async () => {
    try {
        const { data } = await axios.get('/admin/api/options/roles');
        roles.value = data;
    } catch (e) {
        console.error('Failed to load roles', e);
    }
};

const loadAdmin = async () => {
    loading.value = true;
    try {
        const { data } = await axios.get(`/admin/api/admins/${route.params.id}`);
        const admin = data.data;
        form.value.name = admin.name;
        form.value.email = admin.email;
        form.value.phone = admin.phone;
        form.value.is_active = admin.is_active;
        form.value.roles = admin.roles_list || [];
    } catch (e) {
        errorMessage.value = 'Failed to load admin data. They may have been deleted.';
    } finally {
        loading.value = false;
    }
};

onMounted(async () => {
    await loadDropdowns();
    if (isEditing.value) {
        await loadAdmin();
    }
});

const submitForm = async () => {
    submitting.value = true;
    errorMessage.value = '';
    validationErrors.value = {};

    try {
        if (isEditing.value) {
            await axios.put(`/admin/api/admins/${route.params.id}`, form.value);
        } else {
            await axios.post('/admin/api/admins', form.value);
        }
        router.push({ name: 'admin.admins.index' });
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

