<template>
  <div class="employee-list">
    <!-- Top Action & Filter Controls -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-3 sm:p-3.5 rounded-2xl shadow-xs mb-3">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
          <h2 class="text-base font-bold text-slate-900 dark:text-white">Workforce Directory</h2>
          <p class="text-xs text-slate-500 dark:text-slate-400">View and manage all company employees</p>
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

          <!-- Filter Drawer Trigger Button -->
          <button
            type="button"
            @click="isFilterDrawerOpen = true"
            class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 border border-slate-200/80 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-zinc-800 shadow-xs"
            :class="{ 'border-slate-900 text-slate-900 bg-slate-100/80 dark:bg-zinc-800 dark:border-white dark:text-white': activeFilterCount > 0 }"
          >
            <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" :class="{ 'text-slate-900 dark:text-white': activeFilterCount > 0 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 8.293A1 1 0 013 7.586V4z"/>
            </svg>
            <span>Filter</span>
            <span v-if="activeFilterCount > 0" class="ml-0.5 px-1.5 py-0.2 text-[10px] font-extrabold bg-slate-900 text-white dark:bg-white dark:text-slate-900 rounded-full">
              {{ activeFilterCount }}
            </span>
          </button>

          <!-- Add Employee Action Button -->
          <button
            type="button"
            @click="$emit('add-employee')"
            class="bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-bold px-4 py-2 rounded-xl text-xs flex items-center gap-1.5 transition-all duration-200 cursor-pointer shadow-xs"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Employee
          </button>
        </div>
      </div>

      <!-- Active Filters Pill Bar -->
      <div v-if="activeFilterCount > 0" class="mt-4 pt-4 border-t border-slate-100 dark:border-zinc-800 flex flex-wrap items-center gap-2">
        <span class="text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mr-1">Active Filters:</span>

        <span v-if="filters.search" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-zinc-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-zinc-700">
          Search: {{ filters.search }}
          <button @click="removeSingleFilter('search')" class="ml-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-white cursor-pointer"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </span>

        <span v-if="filters.employment_status" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-zinc-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-zinc-700">
          Status: {{ getStatusLabel(filters.employment_status) }}
          <button @click="removeSingleFilter('employment_status')" class="ml-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-white cursor-pointer"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </span>

        <span v-if="filters.department_id" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-zinc-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-zinc-700">
          Department: {{ getDepartmentLabel(filters.department_id) }}
          <button @click="removeSingleFilter('department_id')" class="ml-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-white cursor-pointer"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </span>

        <span v-if="filters.employment_type" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-zinc-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-zinc-700">
          Type: {{ getTypeLabel(filters.employment_type) }}
          <button @click="removeSingleFilter('employment_type')" class="ml-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-white cursor-pointer"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </span>

        <button 
          @click="clearFilters"
          class="ml-auto text-xs font-bold text-rose-600 dark:text-rose-400 hover:underline cursor-pointer flex items-center gap-1"
        >
          Clear All
        </button>
      </div>
    </div>

    <!-- GRID VIEW -->
    <div v-if="viewMode === 'grid'" class="space-y-6">
      <!-- Loading Skeleton -->
      <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5">
        <div v-for="i in 10" :key="i" class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-5 animate-pulse flex flex-col items-center">
          <div class="w-20 h-20 rounded-full bg-slate-200 dark:bg-zinc-800 mb-4"></div>
          <div class="h-4 bg-slate-200 dark:bg-zinc-800 rounded w-3/4 mb-2"></div>
          <div class="h-3 bg-slate-200 dark:bg-zinc-800 rounded w-1/2 mb-3"></div>
          <div class="h-8 bg-slate-200 dark:bg-zinc-800 rounded w-full mt-auto"></div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="employeeList.length === 0" class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-12 text-center">
        <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4 text-slate-400">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
        </div>
        <h3 class="text-base font-bold text-slate-900 dark:text-white">No employees found</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Try adjusting your search or filters.</p>
      </div>

      <!-- Employee Grid Cards -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3.5">
        <div
          v-for="item in employeeList"
          :key="item.id"
          class="group relative bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 hover:border-indigo-500/50 dark:hover:border-indigo-500/50 rounded-2xl p-4 shadow-xs hover:shadow-lg transition-all duration-200 flex flex-col justify-between"
        >
          <!-- Card Header: Checkbox & Status -->
          <div class="flex items-center justify-between w-full mb-2">
            <input
              type="checkbox"
              :value="item.id"
              v-model="selectedEmployees"
              class="h-4 w-4 text-indigo-600 border-slate-300 dark:border-zinc-700 rounded focus:ring-0 cursor-pointer"
            />
            <span
              :class="getStatusBadgeClass(item.employment_status)"
              class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full uppercase tracking-wider"
            >
              {{ getStatusText(item.employment_status) }}
            </span>
          </div>

          <div class="flex flex-col items-center my-3 text-center">
            <div 
              @click.stop="openImagePreview(item)"
              class="relative w-20 h-20 rounded-full overflow-hidden border-2 border-slate-100 dark:border-zinc-800 shadow-xs mb-3 group-hover:scale-105 transition-all duration-200 flex items-center justify-center bg-slate-50 dark:bg-zinc-800 cursor-pointer hover:ring-4 hover:ring-indigo-500/30"
              title="Click to preview profile image"
            >
              <!-- Uploaded Profile Photo -->
              <img
                v-if="item.avatar_url || item.profile_image"
                :src="item.avatar_url || (item.profile_image.startsWith('http') ? item.profile_image : `/storage/${item.profile_image}`)"
                :alt="item.full_name"
                class="w-full h-full object-cover"
              />
              <!-- Female Illustration Avatar SVG -->
              <div v-else-if="item.gender === 'female'" class="w-full h-full bg-gradient-to-br from-pink-100 to-rose-200 dark:from-rose-950/60 dark:to-pink-900/60 flex items-center justify-center">
                <svg class="w-13 h-13 text-rose-500 dark:text-rose-300" viewBox="0 0 64 64" fill="currentColor">
                  <path d="M32 12c-5.523 0-10 4.477-10 10 0 3.75 2.07 7.02 5.14 8.74C21.84 32.8 17.5 37.85 17.5 44h29c0-6.15-4.34-11.2-9.64-13.26A9.97 9.97 0 0042 22c0-5.523-4.477-10-10-10z"/>
                </svg>
              </div>
              <!-- Male Illustration Avatar SVG -->
              <div v-else class="w-full h-full bg-gradient-to-br from-blue-100 to-indigo-200 dark:from-indigo-950/60 dark:to-blue-900/60 flex items-center justify-center">
                <svg class="w-13 h-13 text-indigo-600 dark:text-indigo-300" viewBox="0 0 64 64" fill="currentColor">
                  <path d="M32 12c-5.523 0-10 4.477-10 10 0 3.75 2.07 7.02 5.14 8.74C21.84 32.8 17.5 37.85 17.5 44h29c0-6.15-4.34-11.2-9.64-13.26A9.97 9.97 0 0042 22c0-5.523-4.477-10-10-10z"/>
                </svg>
              </div>
            </div>

            <!-- Employee Info Centered Below Photo -->
            <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate w-full group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
              {{ item.full_name }}
            </h3>
            <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 truncate w-full mt-0.5">
              {{ item.email }}
            </p>
            <p class="text-[11px] font-mono text-slate-400 dark:text-slate-500 mt-0.5">
              {{ item.phone || item.mobile || '—' }}
            </p>
            <div class="mt-2.5 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-slate-100 dark:bg-zinc-800 text-[10px] font-mono font-bold text-slate-600 dark:text-slate-300 border border-slate-200/50 dark:border-zinc-700/50">
              ID: #{{ item.employee_number || item.id }}
            </div>

            <!-- Manager Badge in Grid View -->
            <div class="mt-2 flex items-center justify-center gap-1.5 text-[11px]">
              <span class="text-slate-400 dark:text-zinc-500 font-medium">Manager:</span>
              <span v-if="item.manager" class="font-bold text-slate-800 dark:text-zinc-200 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 px-2 py-0.5 rounded-full border border-indigo-200/60 dark:border-indigo-800/50">
                {{ item.manager.first_name }} {{ item.manager.last_name }}
              </span>
              <span v-else class="text-slate-400 dark:text-zinc-500 italic text-[10px]">
                Unassigned
              </span>
            </div>
          </div>

          <!-- Card Action Buttons -->
          <div class="mt-3 pt-3 border-t border-slate-100 dark:border-zinc-800/80 flex items-center justify-center gap-2">
            <button
              @click="$emit('view-employee', item)"
              class="px-3.5 py-1.5 bg-slate-900 text-white dark:bg-white dark:text-slate-900 hover:opacity-90 font-semibold text-xs rounded-xl shadow-xs transition-all cursor-pointer flex items-center gap-1.5"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              View Profile
            </button>
            <button
              @click="$emit('open-ledger', item)"
              class="p-1.5 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-950/30 transition-colors cursor-pointer"
              title="General Ledger"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </button>
            <button
              v-if="canEdit"
              @click="$emit('edit-employee', item)"
              class="p-1.5 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
              title="Edit Employee"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </button>
            <button
              v-if="canDelete"
              @click="deleteEmployee(item)"
              class="p-1.5 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors cursor-pointer"
              title="Delete Employee"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- TABLE VIEW -->
    <DataTable
      v-else
      title="Employees"
      subtitle="Manage your workforce and employee information"
      :columns="tableColumns"
      :data="employeeList"
      :loading="loading"
      :pagination="pagination"
      :initial-search="filters.search"
      :initial-per-page="25"
      :default-per-page="25"
      storage-key="employees-table-state"
      empty-message="No employees found"
      empty-sub-message="Get started by adding your first employee."
      @search="handleTableSearch"
      @sort="handleSort"
      @page-change="handlePageChange"
      @per-page-change="handlePerPageChange"
    >
      <!-- Custom column content -->
      <template #column-employee="{ item }">
        <div class="flex items-center">
          <div 
            @click.stop="openImagePreview(item)"
            class="flex-shrink-0 h-10 w-10 relative rounded-full overflow-hidden border border-slate-200 dark:border-zinc-800 cursor-pointer hover:opacity-85 transition-opacity hover:ring-2 hover:ring-indigo-500/30"
            title="Click to preview profile image"
          >
            <img
              v-if="item.avatar_url || item.profile_image"
              :src="item.avatar_url || (item.profile_image.startsWith('http') ? item.profile_image : `/storage/${item.profile_image}`)"
              :alt="item.full_name"
              class="h-10 w-10 rounded-full object-cover"
            />
            <div v-else-if="item.gender === 'female'" class="h-10 w-10 bg-gradient-to-br from-pink-100 to-rose-200 dark:from-rose-950 dark:to-pink-900 flex items-center justify-center text-rose-600 dark:text-rose-300">
              <svg class="w-6 h-6" viewBox="0 0 64 64" fill="currentColor"><path d="M32 12c-5.523 0-10 4.477-10 10 0 3.75 2.07 7.02 5.14 8.74C21.84 32.8 17.5 37.85 17.5 44h29c0-6.15-4.34-11.2-9.64-13.26A9.97 9.97 0 0042 22c0-5.523-4.477-10-10-10z"/></svg>
            </div>
            <div v-else class="h-10 w-10 bg-gradient-to-br from-blue-100 to-indigo-200 dark:from-indigo-950 dark:to-blue-900 flex items-center justify-center text-indigo-600 dark:text-indigo-300">
              <svg class="w-6 h-6" viewBox="0 0 64 64" fill="currentColor"><path d="M32 12c-5.523 0-10 4.477-10 10 0 3.75 2.07 7.02 5.14 8.74C21.84 32.8 17.5 37.85 17.5 44h29c0-6.15-4.34-11.2-9.64-13.26A9.97 9.97 0 0042 22c0-5.523-4.477-10-10-10z"/></svg>
            </div>
          </div>
          <div class="ml-3 min-w-0">
            <div class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ item.full_name }}</div>
            <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400 truncate">{{ item.email }}</div>
          </div>
        </div>
      </template>

      <template #column-department="{ item }">
        <span class="text-xs text-slate-700 dark:text-zinc-300 font-medium">{{ item.department?.name || '-' }}</span>
      </template>

      <template #column-position="{ item }">
        <div class="flex items-center gap-2">
          <span class="text-xs text-slate-700 dark:text-zinc-300 font-medium">{{ item.position?.title || '-' }}</span>
          <span 
            v-if="item.position?.level"
            :class="getPositionLevelBadgeClass(item.position.level)"
            class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider border shadow-xs"
          >
            {{ item.position.level }}
          </span>
        </div>
      </template>

      <template #column-manager="{ item }">
        <div v-if="item.manager" class="flex items-center gap-2">
          <div class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-300 font-bold text-[10px] flex items-center justify-center flex-shrink-0 border border-indigo-200/50 dark:border-indigo-800/50">
            {{ item.manager.first_name?.[0] }}{{ item.manager.last_name?.[0] }}
          </div>
          <span class="text-xs font-semibold text-slate-800 dark:text-zinc-200">
            {{ item.manager.first_name }} {{ item.manager.last_name }}
          </span>
        </div>
        <span v-else class="text-xs text-slate-400 dark:text-zinc-500 italic">-</span>
      </template>

      <template #column-status="{ item }">
        <span :class="getStatusBadgeClass(item.employment_status)" class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full uppercase tracking-wider">
          {{ getStatusText(item.employment_status) }}
        </span>
      </template>

      <template #column-salary="{ item }">
        <div>
          <div class="text-xs font-bold text-slate-900 dark:text-white">${{ parseFloat(item.basic_salary).toFixed(2) }}</div>
          <div class="text-[10px] text-slate-500 dark:text-slate-400 capitalize">{{ item.salary_type }}</div>
        </div>
      </template>

      <template #column-actions="{ item }">
        <div class="flex justify-end items-center space-x-2">
          <button
            @click="$emit('view-employee', item)"
            class="p-1 text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400"
            title="View Profile"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
          </button>
          <button
            @click="$emit('open-ledger', item)"
            class="p-1 text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400"
            title="General Ledger"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </button>
          <button
            v-if="canEdit"
            @click="$emit('edit-employee', item)"
            class="p-1 text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400"
            title="Edit"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          </button>
          <button
            v-if="canDelete"
            @click="deleteEmployee(item)"
            class="p-1 text-slate-500 hover:text-rose-600 dark:hover:text-rose-400"
            title="Delete"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
          </button>
        </div>
      </template>
    </DataTable>

    <!-- Image Preview Lightbox Modal -->
    <ImagePreviewModal
      :show="showImagePreview"
      :image-url="previewImageUrl"
      :title="previewImageTitle"
      :subtitle="previewImageSubtitle"
      @close="showImagePreview = false"
    />

    <!-- Slide-Over Filter Drawer Panel -->
    <Teleport to="body">
      <div v-if="isFilterDrawerOpen" class="fixed inset-0 z-[99999] overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div 
          class="fixed inset-0 bg-slate-900/50 dark:bg-zinc-950/80 backdrop-blur-xs transition-opacity duration-300"
          @click="isFilterDrawerOpen = false"
        ></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
          <div class="w-screen max-w-md bg-white dark:bg-zinc-900 border-l border-slate-200 dark:border-zinc-800 shadow-2xl flex flex-col justify-between">
            
            <!-- Drawer Header -->
            <div class="px-6 py-5 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between">
              <div class="flex items-center space-x-2.5">
                <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center text-slate-900 dark:text-white">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 8.293A1 1 0 013 7.586V4z"/>
                  </svg>
                </div>
                <div>
                  <h3 class="text-base font-bold text-slate-900 dark:text-white" id="slide-over-title">Filter Employees</h3>
                  <p class="text-xs text-slate-500 dark:text-slate-400">Refine workforce search parameters</p>
                </div>
              </div>
              <button 
                @click="isFilterDrawerOpen = false"
                class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Drawer Body -->
            <div class="p-6 space-y-6 flex-1 overflow-y-auto">
              <!-- Search -->
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-2">Search</label>
                <input
                  v-model="filters.search"
                  type="text"
                  placeholder="Search employees..."
                  class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-0 focus:border-slate-300 dark:focus:border-zinc-600 transition-all"
                  @input="debouncedSearch"
                />
              </div>

              <!-- Status -->
              <div>
                <FloatingSelect
                  v-model="filters.employment_status"
                  label="Status"
                  placeholder="All Statuses"
                  :options="statusOptions"
                  @update:modelValue="fetchEmployees"
                />
              </div>

              <!-- Department -->
              <div>
                <FloatingSelect
                  v-model="filters.department_id"
                  label="Department"
                  placeholder="All Departments"
                  :options="departmentOptions"
                  @update:modelValue="fetchEmployees"
                />
              </div>

              <!-- Employment Type -->
              <div>
                <FloatingSelect
                  v-model="filters.employment_type"
                  label="Employment Type"
                  placeholder="All Types"
                  :options="typeOptions"
                  @update:modelValue="fetchEmployees"
                />
              </div>
            </div>

            <!-- Drawer Footer -->
            <div class="p-6 border-t border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50 flex items-center gap-3">
              <button
                type="button"
                @click="clearFilters"
                class="flex-1 py-2.5 px-4 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer text-center"
              >
                Clear Filters
              </button>
              <button
                type="button"
                @click="isFilterDrawerOpen = false"
                class="flex-1 py-2.5 px-4 bg-slate-900 text-white dark:bg-white dark:text-slate-900 rounded-xl text-xs font-bold hover:bg-slate-800 dark:hover:bg-slate-100 transition-all cursor-pointer shadow-xs text-center"
              >
                Apply Filters
              </button>
            </div>

          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { debounce } from '@/utils/debounce';
import DataTable from '@/components/common/DataTable.vue';
import FloatingSelect from '@/components/common/FloatingSelect.vue';
import ImagePreviewModal from '@/components/common/ImagePreviewModal.vue';
import axios from 'axios';

const authStore = useAuthStore();

// Props and Emits
const emit = defineEmits(['add-employee', 'edit-employee', 'view-employee', 'open-ledger', 'refresh']);

// Filter options for FloatingSelect
const statusOptions = [
  { value: '', label: 'All Statuses' },
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
  { value: 'terminated', label: 'Terminated' },
  { value: 'on_leave', label: 'On Leave' },
];

const typeOptions = [
  { value: '', label: 'All Types' },
  { value: 'full_time', label: 'Full Time' },
  { value: 'part_time', label: 'Part Time' },
  { value: 'contract', label: 'Contract' },
  { value: 'intern', label: 'Intern' },
];

// Reactive data
const viewMode = ref('table');
const selectedEmployees = ref([]);
const employees = ref({ data: [], total: 0, current_page: 1, last_page: 1 });
const departments = ref([]);
const departmentOptions = computed(() => [
  { value: '', label: 'All Departments' },
  ...departments.value.map(d => ({ value: d.id, label: d.name }))
]);

const employeeList = computed(() => {
  if (Array.isArray(employees.value.data)) {
    return employees.value.data;
  }
  if (Array.isArray(employees.value)) {
    return employees.value;
  }
  return [];
});

const loading = ref(false);
const isFilterDrawerOpen = ref(false);

const filters = ref({
  search: '',
  employment_status: '',
  department_id: '',
  employment_type: '',
  page: 1,
  sort_field: '',
  sort_order: ''
});

const activeFilterCount = computed(() => {
  let count = 0;
  if (filters.value.search && filters.value.search.trim() !== '') count++;
  if (filters.value.employment_status) count++;
  if (filters.value.department_id) count++;
  if (filters.value.employment_type) count++;
  return count;
});

const clearFilters = () => {
  filters.value.search = '';
  filters.value.employment_status = '';
  filters.value.department_id = '';
  filters.value.employment_type = '';
  filters.value.page = 1;
  fetchEmployees();
};

const removeSingleFilter = (key) => {
  if (key === 'search') filters.value.search = '';
  if (key === 'employment_status') filters.value.employment_status = '';
  if (key === 'department_id') filters.value.department_id = '';
  if (key === 'employment_type') filters.value.employment_type = '';
  filters.value.page = 1;
  fetchEmployees();
};

const getDepartmentLabel = (id) => {
  const d = departments.value.find(item => item.id == id);
  return d ? d.name : id;
};

const getStatusLabel = (val) => {
  const opt = statusOptions.find(o => o.value === val);
  return opt ? opt.label : val;
};

const getTypeLabel = (val) => {
  const opt = typeOptions.find(o => o.value === val);
  return opt ? opt.label : val;
};

// DataTable pagination
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 25,
  total: 0,
  from: 0,
  to: 0
});

// Table columns configuration
const tableColumns = ref([
  {
    key: 'employee',
    label: 'Employee',
    sortable: true,
    align: 'left'
  },
  {
    key: 'employee_number',
    label: 'Employee #',
    sortable: true,
    align: 'left',
    class: 'text-gray-500 font-mono text-xs'
  },
  {
    key: 'department',
    label: 'Department',
    sortable: true,
    align: 'left'
  },
  {
    key: 'position',
    label: 'Position',
    sortable: true,
    align: 'left'
  },
  {
    key: 'manager',
    label: 'Manager',
    sortable: false,
    align: 'left'
  },
  {
    key: 'hire_date',
    label: 'Hire Date',
    sortable: true,
    type: 'date',
    align: 'left'
  },
  {
    key: 'status',
    label: 'Status',
    sortable: true,
    align: 'center'
  },
  {
    key: 'salary',
    label: 'Salary',
    sortable: true,
    align: 'left'
  },
  {
    key: 'actions',
    label: 'Actions',
    sortable: false,
    align: 'right'
  }
]);

// Computed
const canEdit = computed(() => authStore.hasPermission('employees.edit'));
const canDelete = computed(() => authStore.hasPermission('employees.delete'));

const hasEmployees = computed(() => {
  // Handle both paginated response (employees.data) and simple array (employees)
  const employeeList = employees.value.data || employees.value;
  return Array.isArray(employeeList) && employeeList.length > 0;
});

// Methods
const fetchEmployees = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      tab: 'employees',
      is_manager: 0,
      ...filters.value
    };

    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null) {
        delete params[key];
      }
    });

    const response = await axios.get('/api/employees', { params });
    employees.value = response.data;

    // Update pagination
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
      from: response.data.from,
      to: response.data.to
    };
  } catch (error) {
    console.error('Error fetching employees:', error);

    // Fallback to test endpoint if auth fails
    try {
      console.log('Trying fallback endpoint...');
      const fallbackResponse = await axios.get('/api/test-employee-list');
      employees.value = fallbackResponse.data;
      console.log('Used fallback endpoint, fetched employees:', employees.value);
      console.log('Fallback employees.data array:', employees.value.data);
      console.log('Fallback employees.data length:', employees.value.data?.length);
    } catch (fallbackError) {
      console.error('Fallback also failed:', fallbackError);
      // Set empty structure to prevent template errors
      employees.value = { data: [], total: 0, current_page: 1, last_page: 1 };
    }
  } finally {
    loading.value = false;
  }
};

const fetchDepartments = async () => {
  try {
    const response = await axios.get('/api/departments');
    departments.value = response.data;
    console.log('Fetched departments:', departments.value);
  } catch (error) {
    console.error('Error fetching departments:', error);

    // Fallback to test endpoint if auth fails
    try {
      const fallbackResponse = await axios.get('/api/test-departments');
      departments.value = fallbackResponse.data;
      console.log('Used fallback for departments:', departments.value);
    } catch (fallbackError) {
      console.error('Department fallback also failed:', fallbackError);
      departments.value = [];
    }
  }
};

const deleteEmployee = async (employee) => {
  if (!confirm('Are you sure you want to delete this employee?')) {
    return;
  }

  try {
    await axios.delete(`/api/employees/${employee.id}`);
    await fetchEmployees();
  } catch (error) {
    console.error('Error deleting employee:', error);
    if (error.response?.data?.message) {
      alert(error.response.data.message);
    }
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= employees.value.last_page) {
    filters.value.page = page;
    fetchEmployees();
  }
};

const debouncedSearch = debounce(() => {
  filters.value.page = 1;
  fetchEmployees();
}, 300);

// DataTable event handlers
const handleTableSearch = (searchQuery) => {
  filters.value.search = searchQuery;
  fetchEmployees(1);
};

const handleSort = (sortData) => {
  filters.value.sort_field = sortData.field;
  filters.value.sort_order = sortData.order;
  fetchEmployees(1);
};

const handlePageChange = (page) => {
  fetchEmployees(page);
};

const handlePerPageChange = (perPage) => {
  pagination.value.per_page = perPage;
  fetchEmployees(1);
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 border border-slate-900 dark:border-white font-bold',
    inactive: 'bg-slate-100 text-slate-600 border border-slate-200/60 dark:bg-zinc-800 dark:text-slate-400 dark:border-zinc-700/50',
    terminated: 'bg-rose-50 text-rose-600 border border-rose-200/60 dark:bg-rose-950/50 dark:text-rose-400 dark:border-rose-800/50',
    on_leave: 'bg-amber-50 text-amber-600 border border-amber-200/60 dark:bg-amber-950/50 dark:text-amber-400 dark:border-amber-800/50'
  };
  return classes[status] || 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-slate-400';
};

const getStatusClass = (status) => {
  return getStatusBadgeClass(status);
};

const getStatusText = (status) => {
  const texts = {
    active: 'Active',
    inactive: 'Inactive',
    terminated: 'Terminated',
    on_leave: 'On Leave'
  };
  return texts[status] || status;
};

const getInitials = (firstName, lastName) => {
  return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase();
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



// Image Preview Modal State
const showImagePreview = ref(false);
const previewImageUrl = ref('');
const previewImageTitle = ref('');
const previewImageSubtitle = ref('');

const getAvatarUrl = (item) => {
  if (!item) return '';
  if (item.avatar_url) return item.avatar_url;
  if (item.profile_image) {
    return item.profile_image.startsWith('http') ? item.profile_image : `/storage/${item.profile_image}`;
  }
  return '';
};

const openImagePreview = (item) => {
  const url = getAvatarUrl(item);
  previewImageUrl.value = url;
  previewImageTitle.value = item.full_name || 'Employee Profile';
  previewImageSubtitle.value = `${item.employee_number ? '#' + item.employee_number + ' • ' : ''}${item.position?.title || 'Employee'}`;
  showImagePreview.value = true;
};

defineExpose({
  fetchEmployees
});

// Lifecycle
onMounted(async () => {
  // Wait a bit for auth to initialize if needed
  if (!authStore.isAuthenticated && localStorage.getItem('auth_token')) {
    await new Promise(resolve => setTimeout(resolve, 1000));
  }

  fetchEmployees();
  fetchDepartments();
});
</script>
