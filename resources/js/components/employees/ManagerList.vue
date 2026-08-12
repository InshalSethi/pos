<template>
  <div class="manager-list">
    <!-- Top Action & Filter Controls -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-4 rounded-2xl shadow-xs mb-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
        <div>
          <h2 class="text-base font-bold text-slate-900 dark:text-white">Manager Directory</h2>
          <p class="text-xs text-slate-500 dark:text-slate-400">View and manage qualified company managers and supervisors</p>
        </div>
        
        <div class="flex items-center gap-3 self-start md:self-auto flex-wrap">
          <!-- View Mode Switcher Buttons -->
          <div class="flex items-center gap-1.5 bg-slate-100 dark:bg-zinc-800/80 p-1 rounded-xl border border-slate-200/50 dark:border-zinc-700/50">
            <button
              type="button"
              @click="viewMode = 'grid'"
              :class="[
                'px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5',
                viewMode === 'grid'
                  ? 'bg-white dark:bg-zinc-900 text-slate-900 dark:text-white shadow-xs'
                  : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'
              ]"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
              </svg>
              Grid View
            </button>
            <button
              type="button"
              @click="viewMode = 'table'"
              :class="[
                'px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5',
                viewMode === 'table'
                  ? 'bg-white dark:bg-zinc-900 text-slate-900 dark:text-white shadow-xs'
                  : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'
              ]"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
              </svg>
              Table View
            </button>
          </div>

          <!-- Add Manager Action Button -->
          <button
            type="button"
            @click="$emit('add-manager')"
            class="bg-indigo-600 text-white hover:bg-indigo-700 font-bold px-4 py-2 rounded-xl text-xs flex items-center gap-1.5 transition-all duration-200 cursor-pointer shadow-xs"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Manager
          </button>
        </div>
      </div>

      <!-- Search & Filters -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">Search</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search managers..."
            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
            @input="debouncedSearch"
          />
        </div>
        <div>
          <FloatingSelect
            v-model="filters.department_id"
            label="Department"
            placeholder="All Departments"
            :options="departmentSelectOptions"
            @update:modelValue="fetchManagers"
          />
        </div>
        <div>
          <FloatingSelect
            v-model="filters.employment_status"
            label="Status"
            placeholder="All Statuses"
            :options="statusOptions"
            @update:modelValue="fetchManagers"
          />
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-16">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!managers.length" class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-12 text-center shadow-xs">
      <div class="w-16 h-16 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
      </div>
      <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-1">No Managers Found</h3>
      <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">No qualified manager profiles are currently registered.</p>
    </div>

    <!-- GRID VIEW MODE -->
    <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5">
      <div 
        v-for="item in managers" 
        :key="item.id"
        class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all duration-200 flex flex-col items-center text-center relative group"
      >
        <!-- Badge Header -->
        <div class="w-full flex items-center justify-between mb-3">
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-900/40">
            Manager
          </span>
          <span :class="getStatusBadgeClass(item.employment_status)" class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider">
            {{ item.employment_status || 'Active' }}
          </span>
        </div>

        <!-- Big Profile Picture Container -->
        <div 
          @click.stop="openImagePreview(item)"
          class="relative w-20 h-20 mb-3 rounded-full overflow-hidden border-2 border-indigo-500/20 dark:border-indigo-400/30 p-1 bg-slate-50 dark:bg-zinc-800 cursor-pointer hover:scale-105 hover:ring-4 hover:ring-indigo-500/30 transition-all duration-200"
          title="Click to preview profile image"
        >
          <img 
            v-if="getAvatarUrl(item)" 
            :src="getAvatarUrl(item)" 
            :alt="item.full_name"
            class="w-full h-full object-cover rounded-full"
          />
          <div v-else-if="item.gender === 'female'" class="w-full h-full rounded-full bg-gradient-to-tr from-pink-100 to-rose-200 dark:from-pink-950 dark:to-rose-900/60 flex items-center justify-center p-1.5 shadow-inner">
            <svg class="w-full h-full text-pink-600 dark:text-pink-400" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="32" cy="22" r="14" fill="currentColor" fill-opacity="0.2"/>
              <path d="M32 8C23.163 8 16 15.163 16 24C16 29.5 18.7 34.4 22.8 37.3C15.8 40.8 11 48 11 56C11 57.1 11.9 58 13 58H51C52.1 58 53 57.1 53 56C53 48 48.2 40.8 41.2 37.3C45.3 34.4 48 29.5 48 24C48 15.163 40.837 8 32 8Z" fill="currentColor"/>
            </svg>
          </div>
          <div v-else class="w-full h-full rounded-full bg-gradient-to-tr from-blue-100 to-indigo-200 dark:from-blue-950 dark:to-indigo-900/60 flex items-center justify-center p-1.5 shadow-inner">
            <svg class="w-full h-full text-indigo-600 dark:text-indigo-400" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="32" cy="22" r="14" fill="currentColor" fill-opacity="0.2"/>
              <path d="M32 8C23.163 8 16 15.163 16 24C16 29.5 18.7 34.4 22.8 37.3C15.8 40.8 11 48 11 56C11 57.1 11.9 58 13 58H51C52.1 58 53 57.1 53 56C53 48 48.2 40.8 41.2 37.3C45.3 34.4 48 29.5 48 24C48 15.163 40.837 8 32 8Z" fill="currentColor"/>
            </svg>
          </div>
        </div>

        <!-- Manager Details -->
        <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate w-full" :title="item.full_name">
          {{ item.full_name }}
        </h3>
        <div class="flex items-center justify-center gap-1.5 mt-0.5 truncate w-full">
          <span class="text-xs font-medium text-slate-500 dark:text-slate-400 truncate">{{ item.position?.title || 'Manager' }}</span>
          <span 
            v-if="item.position?.level"
            :class="getPositionLevelBadgeClass(item.position.level)"
            class="text-[9px] font-bold px-1.5 py-0.2 rounded-full uppercase tracking-wider border shadow-xs"
          >
            {{ item.position.level }}
          </span>
        </div>
        <p class="text-[11px] text-slate-400 dark:text-zinc-500 truncate w-full mt-0.5">
          <span v-if="item.managed_departments && item.managed_departments.length">
            {{ item.managed_departments.map(d => d.name).join(', ') }}
          </span>
          <span v-else>
            {{ item.department?.name || 'Management' }}
          </span>
        </p>
        
        <span class="mt-2 text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-400">
          ID: #{{ item.employee_number || item.id }}
        </span>

        <!-- Card Footer Actions -->
        <div class="w-full pt-3 mt-3 border-t border-slate-100 dark:border-zinc-800 flex flex-col gap-2">
          <button 
            type="button"
            @click="openTeamModal(item)"
            class="w-full px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 border border-indigo-200/60 dark:border-indigo-800/40 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center justify-center gap-1.5 shadow-2xs"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>View Team ({{ item.subordinates_count ?? item.subordinates?.length ?? 0 }})</span>
          </button>
          <div class="flex items-center justify-between gap-1.5 w-full">
            <button 
              type="button"
              @click="$emit('view-employee', item)"
              class="flex-1 px-2.5 py-1.5 bg-slate-900 text-white dark:bg-white dark:text-slate-900 hover:opacity-90 rounded-xl text-xs font-semibold transition-all cursor-pointer text-center"
            >
              Profile
            </button>
            <button 
              type="button"
              @click="$emit('edit-employee', item)"
              class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800 cursor-pointer"
              title="Edit Manager"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>
            <button 
              v-if="item.employment_status === 'active' || !item.employment_status"
              type="button"
              @click="quickTerminate(item)"
              class="p-1.5 text-rose-500 hover:text-rose-700 dark:hover:text-rose-300 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer transition-colors"
              title="Terminate Manager"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            </button>
            <button 
              v-else
              type="button"
              @click="quickReactivate(item)"
              class="p-1.5 text-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-300 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-950/40 cursor-pointer transition-colors"
              title="Reactivate Manager"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- TABLE VIEW MODE -->
    <div v-else class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-xs">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-200/80 dark:border-zinc-800 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
              <th class="py-3 px-4">Manager</th>
              <th class="py-3 px-4">Department</th>
              <th class="py-3 px-4">Position</th>
              <th class="py-3 px-4">Level</th>
              <th class="py-3 px-4">Email / Phone</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-zinc-800 text-xs">
            <tr v-for="item in managers" :key="item.id" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/40 transition-colors">
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  <div 
                    @click.stop="openImagePreview(item)"
                    class="w-8 h-8 rounded-full overflow-hidden bg-slate-100 dark:bg-zinc-800 flex-shrink-0 cursor-pointer hover:opacity-80 transition-opacity hover:ring-2 hover:ring-indigo-500/30"
                    title="Click to preview profile image"
                  >
                    <img v-if="getAvatarUrl(item)" :src="getAvatarUrl(item)" :alt="item.full_name" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center font-bold text-slate-500 text-xs">
                      {{ item.first_name?.[0] }}{{ item.last_name?.[0] }}
                    </div>
                  </div>
                  <div>
                    <div class="font-bold text-slate-900 dark:text-white">{{ item.full_name }}</div>
                    <div class="text-[10px] text-slate-400 font-mono">#{{ item.employee_number }}</div>
                  </div>
                </div>
              </td>
              <td class="py-3 px-4 font-medium text-slate-700 dark:text-zinc-300">
                <span v-if="item.managed_departments && item.managed_departments.length">
                  {{ item.managed_departments.map(d => d.name).join(', ') }}
                </span>
                <span v-else>
                  {{ item.department?.name || '-' }}
                </span>
              </td>
              <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">
                {{ item.position?.title || 'Manager' }}
              </td>
              <td class="py-3 px-4">
                <span 
                  :class="getPositionLevelBadgeClass(item.position?.level || (item.is_manager ? 'manager' : 'mid'))"
                  class="text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider border shadow-xs inline-block"
                >
                  {{ item.position?.level || (item.is_manager ? 'manager' : 'mid') }}
                </span>
              </td>
              <td class="py-3 px-4">
                <div class="text-slate-800 dark:text-zinc-200 font-medium">{{ item.email }}</div>
                <div class="text-slate-400 text-[10px]">{{ item.phone || item.mobile || '-' }}</div>
              </td>
              <td class="py-3 px-4">
                <span :class="getStatusBadgeClass(item.employment_status)" class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider">
                  {{ item.employment_status || 'Active' }}
                </span>
              </td>
              <td class="py-3 px-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button 
                    @click="openTeamModal(item)"
                    class="px-2.5 py-1 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 border border-indigo-200/60 dark:border-indigo-800/40 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 shadow-2xs"
                    title="View Team & Direct Reports"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Team ({{ item.subordinates_count ?? item.subordinates?.length ?? 0 }})</span>
                  </button>
                  <button @click="$emit('view-employee', item)" class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800" title="View Profile">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  </button>
                  <button @click="$emit('edit-employee', item)" class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800" title="Edit Manager">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  </button>
                  <button 
                    v-if="item.employment_status === 'active' || !item.employment_status"
                    @click="quickTerminate(item)" 
                    class="p-1.5 text-rose-400 hover:text-rose-600 dark:hover:text-rose-300 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer transition-colors" 
                    title="Terminate Manager"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                  </button>
                  <button 
                    v-else
                    @click="quickReactivate(item)" 
                    class="p-1.5 text-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-300 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-950/40 cursor-pointer transition-colors" 
                    title="Reactivate Manager"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- SUBORDINATES / TEAM VIEW MODAL -->
    <Teleport to="body">
      <div v-if="showTeamModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-3xl max-w-2xl w-full shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-150">
          <!-- Modal Header -->
          <div class="px-6 py-5 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between bg-slate-50/50 dark:bg-zinc-800/40">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-300 font-bold flex items-center justify-center text-sm border border-indigo-200/60 dark:border-indigo-800/60">
                {{ selectedTeamManager?.first_name?.[0] }}{{ selectedTeamManager?.last_name?.[0] }}
              </div>
              <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                  <span>{{ selectedTeamManager?.full_name }}'s Team</span>
                  <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200/50 dark:border-indigo-800/50">
                    {{ activeSubordinates.length }} Direct Reports
                  </span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                  {{ selectedTeamManager?.position?.title || 'Manager' }} &bull; {{ selectedTeamManager?.department?.name || 'Management' }}
                </p>
              </div>
            </div>
            <button 
              @click="closeTeamModal"
              class="p-2 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <!-- Modal Content: List of Direct Reports -->
          <div class="p-6 max-h-[60vh] overflow-y-auto">
            <div v-if="!activeSubordinates.length" class="text-center py-8">
              <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-400 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
              </div>
              <p class="text-sm font-semibold text-slate-700 dark:text-zinc-300">No direct reports found</p>
              <p class="text-xs text-slate-400 dark:text-zinc-500 mt-0.5">No employees are currently assigned to report to this manager.</p>
            </div>

            <div v-else class="divide-y divide-slate-100 dark:divide-zinc-800">
              <div 
                v-for="sub in activeSubordinates" 
                :key="sub.id" 
                class="py-3.5 flex items-center justify-between first:pt-0 last:pb-0"
              >
                <div class="flex items-center gap-3">
                  <div 
                    @click.stop="openImagePreview(sub)"
                    class="w-9 h-9 rounded-full overflow-hidden bg-slate-100 dark:bg-zinc-800 flex-shrink-0 flex items-center justify-center font-bold text-slate-600 dark:text-zinc-300 text-xs border border-slate-200/60 dark:border-zinc-700/60 cursor-pointer hover:opacity-80 transition-opacity hover:ring-2 hover:ring-indigo-500/30"
                    title="Click to preview profile image"
                  >
                    <img v-if="getAvatarUrl(sub)" :src="getAvatarUrl(sub)" :alt="sub.full_name" class="w-full h-full object-cover" />
                    <span v-else>{{ sub.first_name?.[0] }}{{ sub.last_name?.[0] }}</span>
                  </div>
                  <div>
                    <div class="font-bold text-slate-900 dark:text-white text-xs">{{ sub.first_name }} {{ sub.last_name }}</div>
                    <div class="text-[10px] text-slate-400 font-mono">#{{ sub.employee_number || sub.id }} &bull; {{ sub.email }}</div>
                  </div>
                </div>

                <div class="flex items-center gap-3">
                  <div class="text-right">
                    <div class="text-xs font-semibold text-slate-700 dark:text-zinc-300">{{ sub.position?.title || 'Employee' }}</div>
                    <div class="text-[10px] text-slate-400 dark:text-zinc-500">{{ sub.department?.name || '-' }}</div>
                  </div>
                  <span :class="getStatusBadgeClass(sub.employment_status)" class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider">
                    {{ sub.employment_status || 'Active' }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="px-6 py-4 bg-slate-50 dark:bg-zinc-800/50 border-t border-slate-100 dark:border-zinc-800 flex justify-end">
            <button 
              @click="closeTeamModal" 
              class="px-4 py-2 bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-semibold text-xs rounded-xl hover:opacity-90 transition-opacity cursor-pointer"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Image Preview Lightbox Modal -->
    <ImagePreviewModal
      :show="showImagePreview"
      :image-url="previewImageUrl"
      :title="previewImageTitle"
      :subtitle="previewImageSubtitle"
      @close="showImagePreview = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import FloatingSelect from '@/components/common/FloatingSelect.vue';
import ImagePreviewModal from '@/components/common/ImagePreviewModal.vue';
import axios from 'axios';

const emit = defineEmits(['add-manager', 'edit-employee', 'view-employee', 'refresh']);

const viewMode = ref('table');
const loading = ref(false);
const managers = ref([]);
const departments = ref([]);

// Image Preview Modal State
const showImagePreview = ref(false);
const previewImageUrl = ref('');
const previewImageTitle = ref('');
const previewImageSubtitle = ref('');

const openImagePreview = (item) => {
  const url = getAvatarUrl(item);
  previewImageUrl.value = url;
  previewImageTitle.value = item.full_name || 'Manager Profile';
  previewImageSubtitle.value = `${item.employee_number ? '#' + item.employee_number + ' • ' : ''}${item.position?.title || 'Manager'}`;
  showImagePreview.value = true;
};

const showTeamModal = ref(false);
const selectedTeamManager = ref(null);

const activeSubordinates = computed(() => {
  if (!selectedTeamManager.value) return [];
  return selectedTeamManager.value.subordinates || [];
});

const openTeamModal = (managerItem) => {
  selectedTeamManager.value = managerItem;
  showTeamModal.value = true;
};

const closeTeamModal = () => {
  showTeamModal.value = false;
  selectedTeamManager.value = null;
};

const filters = ref({
  search: '',
  department_id: '',
  employment_status: ''
});

const statusOptions = [
  { value: '', label: 'All Statuses' },
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
  { value: 'on_leave', label: 'On Leave' }
];

const departmentSelectOptions = computed(() => [
  { value: '', label: 'All Departments' },
  ...departments.value.map(d => ({ value: d.id, label: d.name }))
]);

let searchTimeout = null;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchManagers();
  }, 350);
};

const fetchDepartments = async () => {
  try {
    const response = await axios.get('/api/departments');
    departments.value = response.data;
  } catch (error) {
    console.error('Error fetching departments:', error);
  }
};

const fetchManagers = async () => {
  loading.value = true;
  try {
    const params = {
      tab: 'managers',
      is_manager: 1,
      ...filters.value
    };

    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null || params[key] === undefined) {
        delete params[key];
      }
    });

    const response = await axios.get('/api/employees', { params });
    managers.value = Array.isArray(response.data) ? response.data : (response.data.data || []);
  } catch (error) {
    console.error('Error fetching managers:', error);
  } finally {
    loading.value = false;
  }
};

const getAvatarUrl = (item) => {
  if (item.avatar_url) return item.avatar_url;
  if (item.profile_image) {
    return item.profile_image.startsWith('http') ? item.profile_image : `/storage/${item.profile_image}`;
  }
  return null;
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'active':
      return 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-900/40';
    case 'inactive':
      return 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-slate-400';
    case 'on_leave':
      return 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200/50 dark:border-amber-900/40';
    default:
      return 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-slate-400';
  }
};

const getPositionLevelBadgeClass = (level) => {
  switch (level?.toLowerCase()) {
    case 'executive':
    case 'director':
      return 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200/60 dark:border-purple-800/50';
    case 'manager':
    case 'lead':
      return 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-200/60 dark:border-indigo-800/50';
    case 'senior':
      return 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border-blue-200/60 dark:border-blue-800/50';
    case 'mid':
      return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200/60 dark:border-emerald-800/50';
    default:
      return 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-slate-300 border-slate-200 dark:border-zinc-700';
  }
};

const showToast = (message, type = 'success') => {
  const toast = document.createElement('div');
  toast.className = `fixed bottom-5 right-5 z-[99999] px-4 py-3 rounded-xl text-xs font-bold text-white shadow-xl transition-all duration-300 transform translate-y-2 opacity-0 ${
    type === 'success' ? 'bg-emerald-600' : 'bg-rose-600'
  }`;
  toast.textContent = message;
  document.body.appendChild(toast);
  setTimeout(() => {
    toast.classList.remove('translate-y-2', 'opacity-0');
  }, 10);
  setTimeout(() => {
    toast.classList.add('opacity-0', 'translate-y-2');
    setTimeout(() => toast.remove(), 300);
  }, 3000);
};

const quickTerminate = async (item) => {
  if (!confirm(`Are you sure you want to terminate manager ${item.full_name}?`)) return;
  try {
    await axios.post(`/api/employees/${item.id}/terminate`, {
      termination_date: new Date().toISOString().split('T')[0],
      termination_reason: 'Terminated via Quick Actions'
    });
    showToast('Manager terminated successfully', 'success');
    fetchManagers();
    emit('refresh');
  } catch (err) {
    console.error('Error terminating manager:', err);
    showToast(err.response?.data?.message || 'Failed to terminate manager', 'error');
  }
};

const quickReactivate = async (item) => {
  if (!confirm(`Are you sure you want to reactivate manager ${item.full_name}?`)) return;
  try {
    await axios.post(`/api/employees/${item.id}/reactivate`);
    showToast('Manager reactivated successfully', 'success');
    fetchManagers();
    emit('refresh');
  } catch (err) {
    console.error('Error reactivating manager:', err);
    showToast(err.response?.data?.message || 'Failed to reactivate manager', 'error');
  }
};

defineExpose({
  fetchManagers
});

onMounted(() => {
  fetchDepartments();
  fetchManagers();
});
</script>
