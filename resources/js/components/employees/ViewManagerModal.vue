<template>
  <Teleport to="body">
    <div 
      v-if="show" 
      class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
      @click.self="closeModal"
    >
      <div 
        class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-3xl max-w-4xl w-full shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150 my-8"
        @click.stop
      >
        <!-- Modal Top Navigation / Header bar -->
        <div class="px-6 py-4 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between bg-slate-50/50 dark:bg-zinc-800/40">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-200/60 dark:border-indigo-800/50">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </div>
            <div>
              <h2 class="text-sm font-bold text-slate-900 dark:text-white">Manager Profile & Direct Reports</h2>
              <p class="text-[11px] text-slate-500 dark:text-slate-400">View manager details and assigned team staff</p>
            </div>
          </div>

          <button 
            type="button"
            @click="closeModal" 
            class="p-2 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="p-16 text-center flex flex-col items-center justify-center">
          <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600 dark:border-indigo-400 mb-3"></div>
          <p class="text-xs font-semibold text-slate-600 dark:text-zinc-400">Fetching manager profile & direct reports...</p>
        </div>

        <div v-else-if="manager" class="p-6 space-y-6 max-h-[82vh] overflow-y-auto">
          <!-- TOP SECTION: MANAGER PROFILE CARD -->
          <div class="bg-slate-50/70 dark:bg-zinc-800/50 border border-slate-200/80 dark:border-zinc-700/60 rounded-2xl p-5 shadow-2xs">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
              
              <!-- Left Profile Info -->
              <div class="flex items-center gap-4">
                <!-- Avatar with preview click -->
                <div 
                  @click="openImagePreview(manager)"
                  class="relative w-16 h-16 rounded-full overflow-hidden border-2 border-indigo-500/30 dark:border-indigo-400/40 p-0.5 bg-white dark:bg-zinc-800 cursor-pointer hover:scale-105 transition-transform flex-shrink-0"
                  title="Click to view full avatar image"
                >
                  <img 
                    v-if="getAvatarUrl(manager)" 
                    :src="getAvatarUrl(manager)" 
                    :alt="manager.full_name" 
                    class="w-full h-full object-cover rounded-full"
                  />
                  <div v-else-if="manager.gender === 'female'" class="w-full h-full rounded-full bg-gradient-to-tr from-pink-100 to-rose-200 dark:from-pink-950 dark:to-rose-900/60 flex items-center justify-center text-pink-600 dark:text-pink-400 font-bold text-sm">
                    {{ getInitials(manager) }}
                  </div>
                  <div v-else class="w-full h-full rounded-full bg-gradient-to-tr from-indigo-100 to-blue-200 dark:from-indigo-950 dark:to-blue-900/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm">
                    {{ getInitials(manager) }}
                  </div>
                </div>

                <div class="space-y-1">
                  <div class="flex items-center flex-wrap gap-2">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ managerDisplayName }}</h3>
                    <span 
                      v-if="manager.position?.level"
                      :class="getPositionBadgeClass(manager.position.level)"
                      class="text-[9px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider border"
                    >
                      {{ manager.position.level }}
                    </span>
                    <span 
                      :class="manager.is_active ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 border border-slate-900 dark:border-white' : 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-slate-400'"
                      class="text-[9px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider"
                    >
                      {{ manager.employment_status || 'Active' }}
                    </span>
                  </div>

                  <div class="text-xs text-slate-600 dark:text-zinc-400 flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span class="flex items-center gap-1">
                      <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                      {{ manager.email }}
                    </span>
                    <span v-if="manager.phone" class="flex items-center gap-1 font-mono text-[11px]">
                      <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                      {{ manager.phone }}
                    </span>
                  </div>

                  <div class="text-xs text-slate-500 dark:text-zinc-400 pt-0.5">
                    <span class="font-medium text-slate-700 dark:text-zinc-300">Position:</span> {{ manager.position?.title || 'Manager' }} &bull; 
                    <span class="font-medium text-slate-700 dark:text-zinc-300">Departments:</span> 
                    <span class="text-indigo-600 dark:text-indigo-400 font-semibold ml-1">
                      {{ managerDepartmentName }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Right Subordinates Counter Pill -->
              <div class="bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-200/60 dark:border-indigo-900/50 rounded-xl px-4 py-2.5 flex items-center gap-3 text-right">
                <div class="w-10 h-10 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-xs">
                  {{ subordinatesList.length }}
                </div>
                <div class="text-left">
                  <div class="text-xs font-bold text-indigo-950 dark:text-indigo-200">Direct Reports</div>
                  <div class="text-[10px] text-indigo-600 dark:text-indigo-400 font-medium">Assigned Employees</div>
                </div>
              </div>

            </div>
          </div>

          <!-- BOTTOM SECTION: ASSIGNED EMPLOYEES / SUBORDINATES TABLE -->
          <div class="space-y-3">
            <div class="flex items-center justify-between px-1">
              <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                <span>ASSIGNED EMPLOYEES / SUBORDINATES</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-400 font-mono">
                  {{ subordinatesList.length }}
                </span>
              </h3>
            </div>

            <!-- Empty State -->
            <div v-if="!subordinatesList.length" class="bg-slate-50/50 dark:bg-zinc-800/30 border border-slate-200/60 dark:border-zinc-800 rounded-2xl p-10 text-center">
              <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-500 dark:text-indigo-400 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <h4 class="text-xs font-bold text-slate-800 dark:text-zinc-200 mb-1">No employees assigned to this manager yet</h4>
              <p class="text-[11px] text-slate-400 dark:text-zinc-500 max-w-sm mx-auto">Employees assigned to report to this manager will automatically be listed here.</p>
            </div>

            <!-- Subordinates Data Table -->
            <div v-else class="border border-slate-200/80 dark:border-zinc-800 rounded-2xl overflow-hidden bg-white dark:bg-zinc-900 shadow-2xs">
              <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                  <thead>
                    <tr class="bg-slate-50 dark:bg-zinc-800/80 border-b border-slate-100 dark:border-zinc-800 text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">
                      <th class="py-2.5 px-4">Photo / Name</th>
                      <th class="py-2.5 px-4">Employee ID</th>
                      <th class="py-2.5 px-4">Department</th>
                      <th class="py-2.5 px-4">Position</th>
                      <th class="py-2.5 px-4 text-right">Status</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-zinc-800 text-xs">
                    <tr 
                      v-for="sub in subordinatesList" 
                      :key="sub.id"
                      class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition-colors"
                    >
                      <!-- Photo / Name -->
                      <td class="py-2.5 px-4 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                          <div 
                            @click="openImagePreview(sub)"
                            class="w-8 h-8 rounded-full overflow-hidden bg-slate-100 dark:bg-zinc-800 flex-shrink-0 flex items-center justify-center font-bold text-slate-600 dark:text-zinc-300 text-xs border border-slate-200 dark:border-zinc-700 cursor-pointer hover:scale-105 transition-transform"
                            title="Click to preview image"
                          >
                            <img v-if="getAvatarUrl(sub)" :src="getAvatarUrl(sub)" :alt="sub.full_name" class="w-full h-full object-cover" />
                            <span v-else>{{ getInitials(sub) }}</span>
                          </div>
                          <div>
                            <div class="font-bold text-slate-900 dark:text-white">{{ sub.first_name }} {{ sub.last_name }}</div>
                            <div class="text-[10px] text-slate-400 font-normal">{{ sub.email }}</div>
                          </div>
                        </div>
                      </td>

                      <!-- Employee ID -->
                      <td class="py-2.5 px-4 whitespace-nowrap font-mono text-[11px] text-slate-600 dark:text-zinc-400">
                        #{{ sub.employee_number || sub.id }}
                      </td>

                      <!-- Department -->
                      <td class="py-2.5 px-4 whitespace-nowrap text-slate-700 dark:text-zinc-300">
                        {{ sub.department?.name || '-' }}
                      </td>

                      <!-- Position -->
                      <td class="py-2.5 px-4 whitespace-nowrap text-slate-700 dark:text-zinc-300">
                        {{ sub.position?.title || '-' }}
                      </td>

                      <!-- Status Badge -->
                      <td class="py-2.5 px-4 whitespace-nowrap text-right">
                        <span 
                          :class="sub.is_active ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 border border-slate-900 dark:border-white' : 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-slate-400'"
                          class="text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider"
                        >
                          {{ sub.employment_status || 'Active' }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-3.5 bg-slate-50/80 dark:bg-zinc-800/40 border-t border-slate-100 dark:border-zinc-800 flex justify-end">
          <button 
            type="button" 
            @click="closeModal" 
            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl transition-colors cursor-pointer"
          >
            Close
          </button>
        </div>

      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  managerId: {
    type: [Number, String],
    default: null
  },
  managerData: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'preview-image']);

const loading = ref(false);
const manager = ref(null);
const subordinatesList = ref([]);

const managerDisplayName = computed(() => {
  if (!manager.value) return '';
  if (manager.value.full_name) return manager.value.full_name;
  return `${manager.value.first_name || ''} ${manager.value.last_name || ''}`.trim() || 'Manager Profile';
});

const managerDepartmentName = computed(() => {
  if (!manager.value) return 'General Management';
  if (manager.value.managed_departments && manager.value.managed_departments.length) {
    return manager.value.managed_departments.map(d => d.name).join(', ');
  }
  return manager.value.department?.name || 'General Management';
});

const fetchManagerDetails = async () => {
  if (!props.managerId) {
    if (props.managerData) {
      manager.value = props.managerData;
      subordinatesList.value = props.managerData.subordinates || [];
    }
    return;
  }

  loading.value = true;
  try {
    const response = await axios.get(`/api/managers/${props.managerId}/subordinates`);
    console.log('Subordinates Response:', response.data);
    if (response.data && response.data.manager) {
      manager.value = response.data.manager;
    } else {
      manager.value = response.data;
    }
    subordinatesList.value = response.data.subordinates || [];
  } catch (error) {
    console.error('Error fetching manager subordinates:', error);
    try {
      const fallback = await axios.get(`/api/employees/${props.managerId}`);
      manager.value = fallback.data;
      subordinatesList.value = fallback.data.subordinates || [];
    } catch (fbErr) {
      console.error('Fallback endpoint failed:', fbErr);
    }
  } finally {
    loading.value = false;
  }
};

watch(() => props.show, (newShow) => {
  if (newShow) {
    fetchManagerDetails();
  } else {
    manager.value = null;
    subordinatesList.value = [];
  }
}, { immediate: true });

watch(() => props.managerId, (newId) => {
  if (props.show && newId) {
    fetchManagerDetails();
  }
});

const closeModal = () => {
  emit('close');
};

const openImagePreview = (person) => {
  const url = getAvatarUrl(person);
  if (url) {
    emit('preview-image', {
      url: url,
      name: person.full_name || `${person.first_name} ${person.last_name}`
    });
  }
};

const getAvatarUrl = (person) => {
  if (!person) return null;
  if (person.avatar_url) return person.avatar_url;
  if (person.profile_photo_path) return `/storage/${person.profile_photo_path}`;
  if (person.profile_image) {
    return person.profile_image.startsWith('http') ? person.profile_image : `/storage/${person.profile_image}`;
  }
  return null;
};

const getInitials = (person) => {
  if (!person) return 'M';
  const first = person.first_name ? person.first_name[0] : '';
  const last = person.last_name ? person.last_name[0] : '';
  return (first + last).toUpperCase() || 'M';
};

const getPositionBadgeClass = (level) => {
  switch (level?.toLowerCase()) {
    case 'executive':
    case 'director':
      return 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200/60 dark:border-purple-800/50';
    case 'manager':
    case 'lead':
      return 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-200/60 dark:border-indigo-800/50';
    default:
      return 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-slate-300 border-slate-200 dark:border-zinc-700';
  }
};
</script>
