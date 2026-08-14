<template>
  <div class="space-y-6 max-w-full">
    <!-- VIEW 1: MY BOARDS GRID (when viewMode === 'grid') -->
    <div v-if="currentView === 'grid'" class="space-y-6 animate-in fade-in duration-200">
      <!-- Grid Header Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-5 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-xs">
        <div class="flex items-center gap-3">
          <div class="p-3 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-xl shadow-xs shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
          </div>
          <div>
            <h1 class="text-xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
              My Task Boards
              <span class="px-2.5 py-0.5 text-xs font-bold bg-slate-100 text-slate-800 border border-slate-300 dark:bg-zinc-800 dark:text-zinc-200 dark:border-zinc-700 rounded-full">
                {{ taskStore.boards.length }} {{ taskStore.boards.length === 1 ? 'Board' : 'Boards' }}
              </span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Select a Kanban board or create a new workspace board for your company team.</p>
          </div>
        </div>

        <button
          @click="showCreateBoardModal = true"
          class="px-4 py-2.5 bg-slate-900 hover:bg-black active:bg-slate-800 text-white dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-extrabold text-xs tracking-wider uppercase rounded-xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer shrink-0"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
          </svg>
          <span>CREATE NEW BOARD</span>
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="taskStore.loading" class="py-12 text-center text-slate-500 dark:text-slate-400">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-2 border-slate-900 dark:border-white border-t-transparent"></div>
        <p class="mt-2 text-xs font-bold uppercase tracking-wider">Loading boards...</p>
      </div>

      <!-- Boards Cards Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="board in taskStore.boards"
          :key="board.id"
          class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/90 dark:border-zinc-800 p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between space-y-4 group relative"
        >
          <div>
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-center gap-2">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white capitalize">
                  {{ board.name }}
                </h3>
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-100 text-slate-800 border border-slate-300 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700">
                  Active
                </span>
              </div>

              <button
                @click="handleDeleteBoard(board.id)"
                class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-all cursor-pointer opacity-70 group-hover:opacity-100"
                title="Delete Board"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>

            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-2 line-clamp-2">
              {{ board.description || 'No description provided' }}
            </p>
          </div>

          <div class="flex items-center gap-4 text-xs font-semibold text-slate-500 dark:text-zinc-400 pt-2 border-t border-slate-100 dark:border-zinc-800">
            <div class="flex items-center gap-1.5">
              <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              <span>{{ board.tasks_count || 0 }} tasks</span>
            </div>
            <div class="flex items-center gap-1.5">
              <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span>{{ formatDateShort(board.created_at) }}</span>
            </div>
          </div>

          <button
            @click="openBoard(board.id)"
            class="w-full py-2.5 bg-slate-900 hover:bg-black active:bg-slate-800 text-white dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-extrabold text-xs tracking-wider uppercase rounded-xl shadow-xs transition-all flex items-center justify-center gap-2 cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
            </svg>
            <span>OPEN BOARD</span>
          </button>
        </div>
      </div>
    </div>

    <!-- VIEW 2: ACTIVE KANBAN TASKBOARD WORKSPACE (when currentView === 'board') -->
    <div v-else class="space-y-5 animate-in fade-in duration-200">
      <!-- TOP CONTROL NAVIGATION BAR -->
      <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/90 dark:border-zinc-800 p-3 shadow-xs flex flex-wrap items-center justify-between gap-3">
        <!-- Tab Views Selector Buttons -->
        <div class="flex items-center bg-slate-100/80 dark:bg-zinc-800/80 p-1 rounded-xl border border-slate-200 dark:border-zinc-700/60 text-xs font-bold text-slate-600 dark:text-zinc-300">
          <!-- Board Tab -->
          <button
            @click="activeViewTab = 'board'"
            :class="[
              'px-3.5 py-1.5 rounded-lg flex items-center gap-1.5 transition-all cursor-pointer',
              activeViewTab === 'board' ? 'bg-purple-100 text-purple-600 font-medium' : 'hover:text-slate-900 dark:hover:text-white'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
            <span>Board</span>
          </button>

          <!-- List Tab -->
          <button
            @click="activeViewTab = 'list'"
            :class="[
              'px-3.5 py-1.5 rounded-lg flex items-center gap-1.5 transition-all cursor-pointer',
              activeViewTab === 'list' ? 'bg-purple-100 text-purple-600 font-medium' : 'hover:text-slate-900 dark:hover:text-white'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            <span>List</span>
          </button>

          <!-- Calendar Tab -->
          <button
            @click="activeViewTab = 'calendar'"
            :class="[
              'px-3.5 py-1.5 rounded-lg flex items-center gap-1.5 transition-all cursor-pointer',
              activeViewTab === 'calendar' ? 'bg-purple-100 text-purple-600 font-medium' : 'hover:text-slate-900 dark:hover:text-white'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span>Calendar</span>
          </button>

          <!-- Table Tab -->
          <button
            @click="activeViewTab = 'table'"
            :class="[
              'px-3.5 py-1.5 rounded-lg flex items-center gap-1.5 transition-all cursor-pointer',
              activeViewTab === 'table' ? 'bg-purple-100 text-purple-600 font-medium' : 'hover:text-slate-900 dark:hover:text-white'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span>Table</span>
          </button>

          <!-- Gantt Tab -->
          <button
            @click="activeViewTab = 'gantt'"
            :class="[
              'px-3.5 py-1.5 rounded-lg flex items-center gap-1.5 transition-all cursor-pointer',
              activeViewTab === 'gantt' ? 'bg-purple-100 text-purple-600 font-medium' : 'hover:text-slate-900 dark:hover:text-white'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <span>Gantt</span>
          </button>
        </div>

        <!-- Top Bar Actions (Right Controls) -->
        <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
          <!-- My Tasks Filter Button -->
          <button
            @click="filters.myTasksOnly = !filters.myTasksOnly"
            :class="[
              'px-3 py-2 rounded-xl flex items-center gap-1.5 border transition-all cursor-pointer',
              filters.myTasksOnly
                ? 'bg-slate-900 text-white border-slate-900 dark:bg-white dark:text-slate-900 dark:border-white'
                : 'bg-slate-50 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border-slate-200 dark:border-zinc-700 hover:bg-slate-100'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span>My Tasks</span>
          </button>

          <!-- Create Task Primary Button -->
          <button
            @click="openCreateTaskModal()"
            class="px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white font-medium rounded-xl transition-all flex items-center gap-1.5 cursor-pointer"
          >
            <span class="text-base font-medium">+</span>
            <span>Create Task</span>
          </button>

          <!-- Filter Modal Button -->
          <button
            @click="showFilterModal = true"
            :class="[
              'px-3 py-2 rounded-xl flex items-center gap-1.5 border transition-all cursor-pointer',
              isFilterActive
                ? 'bg-slate-900 text-white border-slate-900 dark:bg-white dark:text-slate-900 dark:border-white shadow-xs'
                : 'bg-slate-50 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border-slate-200 dark:border-zinc-700 hover:bg-slate-100'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            <span>Filter</span>
            <span v-if="isFilterActive" class="w-2 h-2 rounded-full bg-slate-400"></span>
          </button>

          <!-- Back to My Boards -->
          <button
            @click="switchToGrid"
            class="px-3 py-2 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-300 rounded-xl border border-slate-200 dark:border-zinc-700 transition-all cursor-pointer"
          >
            Back to Boards
          </button>
        </div>
      </div>

      <!-- MAIN CONTENT VIEW DISPLAY -->

      <!-- TAB 1: KANBAN BOARD VIEW -->
      <div v-if="activeViewTab === 'board'" class="space-y-4">
        <!-- Loading State -->
        <div v-if="taskStore.loading" class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 p-12 text-center text-slate-500 space-x-3">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-2 border-purple-500 border-t-transparent"></div>
          <span class="text-xs font-bold uppercase">Loading Tasks...</span>
        </div>

        <div v-else class="flex gap-5 overflow-x-auto min-h-[560px] items-start pb-4 custom-scrollbar px-1">
          <div
            v-for="col in taskStore.columns"
            :key="col.id"
            @dragover.prevent="handleDragOver($event, col.id)"
            @dragenter.prevent="handleDragEnter($event, col.id)"
            @dragleave="handleDragLeave($event, col.id)"
            @drop.prevent="handleDrop($event, col.id)"
            :class="[
              'flex flex-col transition-all duration-300 shrink-0 relative rounded-xl',
              collapsedColumnIds.includes(col.id) ? 'w-12 items-center bg-slate-50/80 dark:bg-zinc-800/40 border border-slate-200/80 py-4 h-[540px]' : 'w-[320px] min-h-[540px] border-0 bg-transparent',
              dragOverColumnId === col.id && !collapsedColumnIds.includes(col.id) ? 'border-2 border-dashed border-purple-300 bg-purple-50/50 dark:bg-purple-900/10 scale-[1.01]' : ''
            ]"
          >
            <!-- COLLAPSED VIEW -->
            <div v-if="collapsedColumnIds.includes(col.id)" class="flex flex-col items-center justify-between h-full w-full cursor-pointer hover:bg-slate-100 dark:hover:bg-zinc-800/80 transition-colors rounded-lg" @click="toggleColumnCollapse(col.id)">
              <button class="p-1 hover:text-purple-600 transition-colors text-slate-400 rotate-180" title="Expand Column">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
              </button>
              <div class="flex-1 flex items-center justify-center">
                <span class="text-xs font-black tracking-widest uppercase text-slate-500 whitespace-nowrap -rotate-90">{{ col.name }}</span>
              </div>
              <span class="w-6 h-6 rounded-full bg-slate-200 dark:bg-zinc-700 text-[10px] font-bold text-slate-600 dark:text-zinc-300 flex items-center justify-center shrink-0">
                {{ getFilteredTasksForColumn(col.id).length }}
              </span>
            </div>

            <!-- EXPANDED VIEW -->
            <template v-else>
              <!-- Column Header (Dot, Name, Count Badge, Quick actions) -->
              <div class="flex items-center justify-between pb-3 mb-3 px-1">
                <div class="flex items-center gap-2">
                  <span class="text-slate-300 font-bold cursor-grab">⋮⋮</span>
                  <span :class="['w-2.5 h-2.5 rounded-full', getColumnDotClass(col)]"></span>
                  <h3 class="text-[15px] font-bold text-slate-800 dark:text-slate-200">
                    {{ col.name }}
                  </h3>
                  <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-500 dark:text-zinc-400">
                    {{ getFilteredTasksForColumn(col.id).length }}
                  </span>
                </div>

                <div class="flex items-center gap-1.5 text-slate-400">
                  <button @click="toggleColumnCollapse(col.id)" class="p-1 hover:text-slate-600 transition-colors cursor-pointer" title="Collapse Column">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                  </button>
                  <button
                    @click="openCreateTaskModal(col.id)"
                    class="p-1 hover:text-slate-600 transition-colors cursor-pointer"
                    title="Add Task"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                  </button>
                </div>
              </div>

            <!-- Task Cards -->
            <div class="space-y-3 flex-1 overflow-y-auto custom-scrollbar pr-0.5 min-h-[460px]">
              <!-- MOVING TARGET DASHED PLACEHOLDER SLOT -->
              <div
                v-if="draggedTask && dragOverColumnId === col.id && draggedTask.task_column_id !== col.id"
                class="p-4 rounded-2xl border-2 border-dashed border-purple-300 dark:border-purple-800 bg-purple-50/40 dark:bg-purple-900/10 text-purple-600 dark:text-purple-400 animate-pulse flex flex-col justify-center items-center space-y-1.5 transition-all shadow-inner"
              >
                <div class="flex items-center gap-1.5 text-[10px] font-bold tracking-widest uppercase bg-purple-100 text-purple-600 dark:bg-purple-900/50 dark:text-purple-300 px-2 py-0.5 rounded-md">
                  <span>⋮⋮</span>
                  <span>MOVING</span>
                </div>
                <span class="text-xs font-medium truncate max-w-[220px] opacity-80">
                  {{ draggedTask.title }}
                </span>
              </div>

              <div
                v-for="task in getFilteredTasksForColumn(col.id)"
                :key="task.id"
                draggable="true"
                @dragstart="handleDragStart($event, task)"
                @dragend="handleDragEnd"
                @click="openTaskDrawer(task)"
                :class="[
                  'bg-white dark:bg-zinc-900 rounded-xl border p-3.5 shadow-sm hover:shadow-md transition-all group relative space-y-2 cursor-grab active:cursor-grabbing select-none',
                  draggedTask?.id === task.id ? 'opacity-30 scale-95 border-2 border-dashed border-purple-400 bg-purple-50 dark:bg-purple-900/20' : (selectedTaskIds.includes(task.id) ? 'border-purple-400 ring-2 ring-purple-500/10 dark:ring-purple-500/10' : 'border-slate-200/90 dark:border-zinc-800')
                ]"
              >
                <!-- Title Row: Checkbox, ID, Title, Star -->
                <div class="flex gap-2 items-start relative">
                  <div class="flex items-center pt-0.5 shrink-0 absolute -left-[28px] group-hover:translate-x-[28px] transition-transform duration-200" :class="selectedTaskIds.includes(task.id) ? 'translate-x-[28px]' : ''">
                    <input
                      v-if="!draggedTask || draggedTask.id !== task.id"
                      type="checkbox"
                      :checked="selectedTaskIds.includes(task.id)"
                      @change="toggleTaskSelection(task.id)"
                      :class="[
                        'rounded border-slate-300 text-purple-600 focus:ring-purple-500 cursor-pointer transition-opacity duration-200',
                        selectedTaskIds.includes(task.id) ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'
                      ]"
                    />
                  </div>
                  <div class="flex-1 min-w-0 transition-transform duration-200" :class="selectedTaskIds.includes(task.id) || 'group-hover:translate-x-5'">
                    <span v-if="draggedTask?.id === task.id" class="px-2 py-0.5 mb-1 text-[9px] font-bold uppercase tracking-wider bg-purple-500 text-white dark:bg-purple-600 rounded-md inline-flex items-center gap-1">
                      ⋮⋮ MOVING
                    </span>
                    <h4 class="text-[13px] font-medium text-slate-700 dark:text-white leading-snug cursor-pointer group-hover:text-purple-600 transition-colors">
                      <span class="text-slate-300 font-medium mr-1 select-text transition-opacity" :class="selectedTaskIds.includes(task.id) || 'group-hover:opacity-0'">#{{ task.id }}</span>
                      {{ task.title }}
                    </h4>
                    <p v-if="task.description" class="text-[11px] text-slate-400 dark:text-zinc-500 mt-1 line-clamp-2 leading-relaxed">
                      {{ stripHtml(task.description) }}
                    </p>
                  </div>

                  <!-- STAR THIS TASK BUTTON -->
                  <button
                    @click.stop="handleToggleStar(task)"
                    :class="[
                      'p-1 shrink-0 transition-all cursor-pointer text-slate-300 hover:text-amber-400',
                      task.is_starred ? 'opacity-100 text-amber-400' : 'opacity-0 group-hover:opacity-100'
                    ]"
                    :title="task.is_starred ? 'Unstar this task' : 'Star this task'"
                  >
                    <svg v-if="task.is_starred" class="w-4 h-4 text-amber-400 fill-current" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                  </button>
                </div>

                <!-- Badges Row: Priority, Due Date, Attachments -->
                <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-100 dark:border-zinc-800/80 text-[10px]">
                  <div class="flex items-center gap-1.5">
                    <span :class="['px-2 py-0.5 font-extrabold uppercase rounded-md border', getPriorityBadgeClass(task.priority)]">
                      {{ task.priority }}
                    </span>

                    <span v-if="task.due_date" :class="['font-bold flex items-center gap-1', isOverdue(task.due_date) ? 'text-rose-600 font-extrabold' : 'text-slate-500']">
                      🕒 {{ formatDateShort(task.due_date) }}
                    </span>

                    <span v-if="task.attachments && task.attachments.length > 0" class="font-bold text-slate-500 flex items-center gap-0.5">
                      📎 {{ task.attachments.length }}
                    </span>
                  </div>

                  <!-- Stacked Assignee Avatars -->
                  <div class="flex -space-x-1.5 overflow-hidden items-center">
                    <div
                      v-for="u in getTaskAssignees(task).slice(0, 3)"
                      :key="u.id"
                      class="w-5 h-5 rounded-full bg-slate-900 dark:bg-slate-700 text-white font-black text-[9px] flex items-center justify-center ring-1 ring-white dark:ring-zinc-900 shrink-0"
                      :title="u.name"
                    >
                      {{ getInitials(u.name) }}
                    </div>
                  </div>
                </div>

                <!-- Tag Pills -->
                <div v-if="task.tags && task.tags.length > 0" class="flex flex-wrap gap-1 pt-0.5">
                  <span
                    v-for="tg in task.tags"
                    :key="tg"
                    class="px-2 py-0.5 text-[9px] font-extrabold rounded-md border bg-slate-100 text-slate-800 border-slate-300 dark:bg-zinc-800 dark:text-zinc-200 dark:border-zinc-700"
                  >
                    {{ tg }}
                  </span>
                </div>
              </div>

              <!-- Empty Column Dropzone Hint -->
              <div v-if="getFilteredTasksForColumn(col.id).length === 0" class="py-12 text-center text-slate-400 dark:text-zinc-600 border-2 border-dashed border-slate-200 dark:border-zinc-800 rounded-2xl">
                <span class="text-xs font-extrabold italic text-slate-400">+ Drop tasks here or click + New</span>
              </div>
            </div>
            </template>
          </div>
          <!-- Add Column Block -->
          <div v-if="!isAddingColumn" class="flex flex-col min-h-[540px] shrink-0 w-[320px] rounded-xl border-2 border-dashed border-slate-200 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50 hover:bg-slate-100/50 dark:hover:bg-zinc-800/80 transition-colors cursor-pointer flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" @click="isAddingColumn = true">
            <span class="text-sm font-medium flex items-center gap-1.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add Column</span>
          </div>

          <div v-else class="flex flex-col shrink-0 w-[320px]">
            <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-3 shadow-sm space-y-3 mt-1">
              <input 
                v-model="newColumnForm.name" 
                type="text" 
                placeholder="Column name..." 
                class="w-full border border-slate-200 dark:border-zinc-700 bg-transparent rounded-md px-3 py-2 text-sm focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 text-slate-800 dark:text-white transition-colors" 
                @keyup.enter="submitNewColumn" 
                autofocus 
              />
              
              <div 
                @click="cycleColumnColor"
                :class="['w-full h-7 rounded-sm cursor-pointer transition-colors hover:opacity-90', getBgClass(newColumnForm.color)]"
                title="Click to change color"
              ></div>

              <div class="flex items-center gap-3 pt-1">
                <button @click="submitNewColumn" class="bg-[#a855f7] hover:bg-purple-600 text-white text-[13px] font-bold px-4 py-1.5 rounded-md transition-colors">Add</button>
                <button @click="cancelAddColumn" class="text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 text-[13px] font-medium transition-colors">Cancel</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 2: LIST VIEW -->
      <div v-else-if="activeViewTab === 'list'" class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/90 dark:border-zinc-800 p-6 space-y-6">
        <div v-for="col in taskStore.columns" :key="col.id" class="space-y-3">
          <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-800 pb-2">
            <div class="flex items-center gap-2">
              <span :class="['w-3 h-3 rounded-full', getColumnDotClass(col)]"></span>
              <h3 class="text-sm font-black uppercase text-slate-800 dark:text-white">{{ col.name }}</h3>
              <span class="px-2 py-0.5 text-xs font-extrabold rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300">
                {{ getFilteredTasksForColumn(col.id).length }}
              </span>
            </div>
          </div>

          <div class="space-y-2">
            <div
              v-for="task in getFilteredTasksForColumn(col.id)"
              :key="task.id"
              @click="openTaskDrawer(task)"
              class="flex items-center justify-between p-3.5 bg-slate-50 dark:bg-zinc-800/60 rounded-xl border border-slate-200/80 dark:border-zinc-700/80 hover:border-purple-500 transition-all text-xs cursor-pointer"
            >
              <div class="flex items-center gap-3 truncate">
                <input
                  type="checkbox"
                  :checked="selectedTaskIds.includes(task.id)"
                  @change.stop="toggleTaskSelection(task.id)"
                  class="rounded border-slate-300 text-purple-600 focus:ring-purple-500 cursor-pointer"
                />
                <span class="font-extrabold text-slate-400">#{{ task.id }}</span>
                <span class="font-bold text-purple-600 dark:text-purple-400 truncate">{{ task.title }}</span>
              </div>

              <div class="flex items-center gap-4 shrink-0">
                <button @click.stop="handleToggleStar(task)" class="text-amber-400 font-bold text-sm">
                  {{ task.is_starred ? '⭐' : '☆' }}
                </button>
                <span :class="['px-2.5 py-0.5 font-bold uppercase rounded-md border', getPriorityBadgeClass(task.priority)]">
                  {{ task.priority }}
                </span>
                <span v-if="task.due_date" class="font-semibold text-slate-500">📅 {{ formatDateShort(task.due_date) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 3: CALENDAR VIEW -->
      <div v-else-if="activeViewTab === 'calendar'" class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/90 dark:border-zinc-800 p-6 space-y-6">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-black text-slate-900 dark:text-white">August 2026</h2>
          <div class="flex items-center gap-2 text-xs font-bold">
            <button @click="calendarMonth--" class="p-2 border border-slate-200 dark:border-zinc-700 rounded-xl hover:bg-slate-100 dark:hover:bg-zinc-800 cursor-pointer">‹</button>
            <button class="px-4 py-2 border border-slate-200 dark:border-zinc-700 rounded-xl hover:bg-slate-100 dark:hover:bg-zinc-800 cursor-pointer">Today</button>
            <button @click="calendarMonth++" class="p-2 border border-slate-200 dark:border-zinc-700 rounded-xl hover:bg-slate-100 dark:hover:bg-zinc-800 cursor-pointer">›</button>
          </div>
        </div>

        <div class="grid grid-cols-7 gap-px bg-slate-200 dark:bg-zinc-800 rounded-2xl overflow-hidden text-center text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-zinc-400 py-2 bg-slate-50 dark:bg-zinc-900">
          <div>SUN</div><div>MON</div><div>TUE</div><div>WED</div><div>THU</div><div>FRI</div><div>SAT</div>
        </div>

        <div class="grid grid-cols-7 gap-px bg-slate-200 dark:bg-zinc-800 rounded-2xl overflow-hidden border border-slate-200 dark:border-zinc-800">
          <div
            v-for="day in 31"
            :key="day"
            class="bg-white dark:bg-zinc-900 min-h-[110px] p-2 hover:bg-slate-50/80 dark:hover:bg-zinc-800/40 transition-colors flex flex-col justify-between cursor-pointer"
            @click="openCreateTaskForDate(day)"
          >
            <div class="flex justify-between items-center">
              <span :class="['w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs', day === 14 ? 'bg-purple-600 text-white font-black' : 'text-slate-700 dark:text-zinc-300']">
                {{ day }}
              </span>
            </div>

            <div class="space-y-1 mt-1">
              <div
                v-for="task in getTasksForDate(day)"
                :key="task.id"
                @click.stop="openTaskDrawer(task)"
                class="px-2 py-1 bg-purple-50 text-purple-800 dark:bg-purple-950 dark:text-purple-200 rounded-lg text-[10px] font-bold truncate border border-purple-200 dark:border-purple-800 cursor-pointer"
              >
                #{{ task.id }} {{ task.title }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 4: HIGH-DENSITY TABLE VIEW (EXACT MATCH FOR IMAGE 4) -->
      <div v-else-if="activeViewTab === 'table'" class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/90 dark:border-zinc-800 overflow-hidden shadow-xs">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 dark:bg-zinc-800/60 uppercase tracking-wider text-[10px] font-extrabold text-slate-500 dark:text-zinc-400 border-b border-slate-200 dark:border-zinc-800">
            <tr>
              <th class="p-3.5 w-10 text-center">
                <input type="checkbox" @change="selectAllTasks" class="rounded border-slate-300 text-purple-600 focus:ring-purple-500" />
              </th>
              <th class="p-3.5">ID</th>
              <th class="p-3.5">TASK</th>
              <th class="p-3.5">STATUS</th>
              <th class="p-3.5">PRIORITY</th>
              <th class="p-3.5">ASSIGNEE</th>
              <th class="p-3.5">DUE DATE</th>
              <th class="p-3.5">TAGS</th>
              <th class="p-3.5">CREATED</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-zinc-800 font-medium text-slate-800 dark:text-zinc-200">
            <tr
              v-for="task in allFilteredTasks"
              :key="task.id"
              class="hover:bg-slate-50/80 dark:hover:bg-zinc-800/50 transition-colors cursor-pointer"
              @click="openTaskDrawer(task)"
            >
              <td class="p-3.5 text-center" @click.stop>
                <input
                  type="checkbox"
                  :checked="selectedTaskIds.includes(task.id)"
                  @change="toggleTaskSelection(task.id)"
                  class="rounded border-slate-300 text-purple-600 focus:ring-purple-500"
                />
              </td>
              <td class="p-3.5 font-bold text-slate-400">#{{ task.id }}</td>
              <td class="p-3.5 max-w-xs">
                <div class="font-extrabold text-purple-600 dark:text-purple-400 truncate">{{ task.title }}</div>
                <div v-if="task.description" class="text-[11px] text-slate-400 truncate">{{ stripHtml(task.description) }}</div>
              </td>
              <td class="p-3.5">
                <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border border-slate-200 dark:border-zinc-700">
                  {{ getColumnName(task.task_column_id) }}
                </span>
              </td>
              <td class="p-3.5">
                <span :class="['px-2.5 py-1 text-[10px] font-extrabold uppercase rounded-lg border', getPriorityBadgeClass(task.priority)]">
                  {{ task.priority }}
                </span>
              </td>
              <td class="p-3.5">
                <div v-if="getTaskAssignees(task).length > 0" class="flex items-center gap-1.5">
                  <div class="w-6 h-6 rounded-full bg-purple-600 text-white font-bold text-[10px] flex items-center justify-center">
                    {{ getInitials(getTaskAssignees(task)[0].name) }}
                  </div>
                  <span class="truncate">{{ getTaskAssignees(task)[0].name }}</span>
                </div>
                <span v-else class="text-slate-400">—</span>
              </td>
              <td class="p-3.5 font-bold text-slate-600 dark:text-zinc-400">
                {{ task.due_date ? formatDateShort(task.due_date) : '—' }}
              </td>
              <td class="p-3.5">
                <div v-if="task.tags && task.tags.length > 0" class="flex flex-wrap gap-1">
                  <span v-for="tg in task.tags" :key="tg" class="px-2 py-0.5 text-[9px] font-bold bg-purple-50 text-purple-700 dark:bg-purple-950 dark:text-purple-300 rounded border border-purple-200 dark:border-purple-800">
                    {{ tg }}
                  </span>
                </div>
                <span v-else class="text-slate-400">—</span>
              </td>
              <td class="p-3.5 text-slate-400 font-semibold">
                {{ formatDateShort(task.created_at) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- TAB 5: GANTT CHART VIEW -->
      <div v-else-if="activeViewTab === 'gantt'" class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/90 dark:border-zinc-800 p-6 space-y-4">
        <h3 class="text-sm font-black uppercase text-slate-800 dark:text-white">Timeline & Gantt Schedule</h3>
        <div class="space-y-3">
          <div v-for="task in allFilteredTasks" :key="task.id" class="flex items-center gap-4 cursor-pointer" @click="openTaskDrawer(task)">
            <div class="w-48 font-bold text-xs truncate text-purple-600 dark:text-purple-400">#{{ task.id }} {{ task.title }}</div>
            <div class="flex-1 bg-slate-100 dark:bg-zinc-800 h-8 rounded-xl relative overflow-hidden">
              <div
                class="absolute h-full bg-purple-600 text-white text-[10px] font-bold px-3 flex items-center rounded-xl shadow-xs"
                :style="{ left: '10%', width: '40%' }"
              >
                {{ formatDateShort(task.created_at) }} → {{ task.due_date ? formatDateShort(task.due_date) : 'Ongoing' }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- FLOATING BATCH ACTIONS TOOLBAR -->
      <div
        v-if="selectedTaskIds.length > 0"
        class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-slate-900 text-white rounded-2xl p-2.5 px-4 shadow-2xl border border-slate-800 flex items-center gap-3 animate-in slide-in-from-bottom duration-200"
      >
        <div class="flex items-center gap-2 px-3 py-1 bg-slate-800 rounded-xl font-extrabold text-xs text-emerald-400">
          <span>✔</span>
          <span>{{ selectedTaskIds.length }} selected</span>
        </div>

        <div class="h-4 w-px bg-slate-800"></div>

        <button
          @click="showBatchStatusModal = true"
          class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-xs font-bold rounded-xl transition-colors cursor-pointer flex items-center gap-1.5"
        >
          <span>🥞 Status</span>
        </button>

        <button
          @click="showBatchPriorityModal = true"
          class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-xs font-bold rounded-xl transition-colors cursor-pointer flex items-center gap-1.5"
        >
          <span>🚩 Priority</span>
        </button>

        <button
          @click="handleBatchDelete"
          class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-extrabold rounded-xl transition-colors cursor-pointer flex items-center gap-1.5"
        >
          <span>🗑️ Delete</span>
        </button>

        <button @click="selectedTaskIds = []" class="p-1.5 text-slate-400 hover:text-white rounded-xl">✕</button>
      </div>
    </div>

    <!-- TASK DETAIL SLIDE-OVER DRAWER (EXACT MATCH FOR IMAGES 2 & 3) -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-x-0"
        leave-to-class="translate-x-full"
      >
        <div v-if="showTaskDrawer && activeDrawerTask" class="fixed inset-0 z-[100] flex justify-end">
          <!-- Backdrop -->
          <div class="fixed inset-0 bg-slate-900/50 dark:bg-black/70 backdrop-blur-xs" @click="closeTaskDrawer"></div>

          <!-- Slide-Over Drawer Container -->
          <div class="relative w-full max-w-5xl bg-white dark:bg-zinc-900 shadow-2xl h-full z-10 flex flex-col overflow-hidden border-l border-slate-200 dark:border-zinc-800">
            <!-- Drawer Header (Title + Star + Close) -->
            <div class="px-8 py-5 border-b border-slate-200 dark:border-zinc-800 flex items-center justify-between bg-white dark:bg-zinc-900 shrink-0">
              <div class="flex items-center gap-3">
                <h2 class="text-xl font-black text-slate-900 dark:text-white">
                  {{ activeDrawerTask.title }}
                </h2>
                <button
                  @click="handleToggleStar(activeDrawerTask)"
                  class="text-xl leading-none cursor-pointer text-amber-400 hover:scale-110 transition-transform"
                >
                  {{ activeDrawerTask.is_starred ? '⭐' : '☆' }}
                </button>
              </div>

              <div class="flex items-center gap-3">
                <button @click="editTaskModal(activeDrawerTask)" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-xs font-bold rounded-xl">
                  ✏️ Edit Task
                </button>
                <button @click="closeTaskDrawer" class="p-2 text-slate-400 hover:text-slate-900 dark:hover:text-white rounded-xl text-lg">
                  ✕
                </button>
              </div>
            </div>

            <!-- Drawer Body Split View (Left Main Content | Right Sidebar) -->
            <div class="flex-1 overflow-y-auto custom-scrollbar grid grid-cols-1 lg:grid-cols-12 divide-y lg:divide-y-0 lg:divide-x divide-slate-200 dark:divide-zinc-800">
              <!-- LEFT MAIN CONTENT (8 Cols) -->
              <div class="lg:col-span-8 p-8 space-y-6">
                <!-- Badges Row: Status, Priority, Due Date -->
                <div class="flex flex-wrap items-center gap-2 text-xs">
                  <span class="px-3 py-1 bg-slate-100 dark:bg-zinc-800 font-extrabold text-slate-700 dark:text-zinc-300 rounded-lg border border-slate-200 dark:border-zinc-700">
                    {{ getColumnName(activeDrawerTask.task_column_id) }}
                  </span>
                  <span :class="['px-3 py-1 font-black uppercase rounded-lg border', getPriorityBadgeClass(activeDrawerTask.priority)]">
                    🚩 {{ activeDrawerTask.priority }} Priority
                  </span>
                  <span v-if="activeDrawerTask.due_date" class="px-3 py-1 font-bold text-slate-600 dark:text-zinc-400 bg-slate-50 dark:bg-zinc-800/60 rounded-lg border">
                    📅 Due {{ formatDateLong(activeDrawerTask.due_date) }}
                  </span>
                </div>

                <!-- Description Block -->
                <div class="space-y-2">
                  <h4 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider">Description</h4>
                  <div class="p-4 bg-slate-50/80 dark:bg-zinc-800/40 border border-slate-200 dark:border-zinc-800 rounded-2xl text-xs text-slate-800 dark:text-zinc-200 leading-relaxed min-h-[80px]">
                    <div v-if="activeDrawerTask.description" v-html="activeDrawerTask.description"></div>
                    <span v-else class="text-slate-400 italic">No description added yet.</span>
                  </div>
                </div>

                <!-- CREATED BY SECTION (DYNAMIC UPDATE MATCHING REQUEST) -->
                <div class="p-4 bg-slate-50/60 dark:bg-zinc-800/50 rounded-2xl border border-slate-200/80 dark:border-zinc-800 space-y-2.5">
                  <div class="text-[11px] font-black tracking-wider uppercase text-slate-400 flex items-center gap-1.5">
                    <span>⚙️ CREATED BY</span>
                  </div>
                  <div class="space-y-1.5 text-xs text-slate-700 dark:text-zinc-300 font-semibold">
                    <div class="flex items-center gap-2">
                      <span class="text-slate-400">👤</span>
                      <span class="font-extrabold text-slate-900 dark:text-white">{{ activeDrawerTask.created_by?.name || getTaskAssignees(activeDrawerTask)[0]?.name || 'Admin User' }}</span>
                      <a v-if="activeDrawerTask.created_by?.email || getTaskAssignees(activeDrawerTask)[0]?.email" :href="`mailto:${activeDrawerTask.created_by?.email || getTaskAssignees(activeDrawerTask)[0]?.email}`" class="text-slate-900 dark:text-white hover:underline flex items-center gap-1 font-bold">
                        ✉️ {{ activeDrawerTask.created_by?.email || getTaskAssignees(activeDrawerTask)[0]?.email }}
                      </a>
                    </div>
                    <div class="flex items-center gap-2 text-slate-400 text-[11px]">
                      <span>💻 Chrome 151.0.0.0 · Windows 10/11</span>
                      <span>• Created {{ formatDateShort(activeDrawerTask.created_at) }}</span>
                    </div>
                  </div>
                </div>

                <!-- Checklists Section -->
                <div class="space-y-3">
                  <div class="flex items-center justify-between">
                    <h4 class="text-xs font-black text-slate-800 dark:text-white uppercase flex items-center gap-1.5">
                      <span>☑ Checklists</span>
                    </h4>
                  </div>
                  <div class="flex gap-2">
                    <input v-model="newChecklistItem" @keyup.enter="addChecklistItem" type="text" placeholder="Add checklist item..." class="flex-1 bg-slate-50 dark:bg-zinc-800 border rounded-xl px-3 py-1.5 text-xs" />
                    <button @click="addChecklistItem" class="px-3 py-1.5 bg-slate-900 hover:bg-black text-white dark:bg-white dark:text-slate-900 font-bold rounded-xl text-xs">+ Add</button>
                  </div>
                  <div class="space-y-1.5">
                    <div v-for="(item, idx) in (activeDrawerTask.checklists || [])" :key="idx" class="flex items-center gap-2 text-xs font-semibold">
                      <input type="checkbox" v-model="item.completed" class="rounded border-slate-300 text-slate-900 focus:ring-slate-900" />
                      <span :class="item.completed ? 'line-through text-slate-400' : 'text-slate-800 dark:text-zinc-200'">{{ item.text }}</span>
                    </div>
                  </div>
                </div>

                <!-- Activity & Comments Section -->
                <div class="space-y-4 pt-4 border-t border-slate-200 dark:border-zinc-800">
                  <h4 class="text-xs font-black text-slate-800 dark:text-white uppercase flex items-center gap-1.5">
                    <span>💬 Activity & Comments</span>
                  </h4>

                  <div class="space-y-2">
                    <textarea
                      v-model="commentText"
                      placeholder="Write a comment... (Ctrl+Enter to send) Type @ to mention"
                      class="w-full bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 rounded-2xl p-4 text-xs font-medium text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-slate-900/20"
                      rows="3"
                      @keydown.ctrl.enter="submitComment"
                    ></textarea>

                    <div class="flex justify-end">
                      <button @click="submitComment" class="px-5 py-2 bg-slate-900 hover:bg-black text-white dark:bg-white dark:text-slate-900 font-extrabold text-xs rounded-xl shadow-xs cursor-pointer">
                        Send Comment
                      </button>
                    </div>
                  </div>

                  <!-- Comments Timeline -->
                  <div class="space-y-3 pt-2">
                    <div v-for="c in (activeDrawerTask.comments || [])" :key="c.id" class="p-3 bg-slate-50 dark:bg-zinc-800/50 rounded-xl space-y-1 border border-slate-100 dark:border-zinc-800">
                      <div class="flex items-center justify-between text-[11px]">
                        <span class="font-extrabold text-slate-900 dark:text-white">{{ c.user?.name || 'User' }}</span>
                        <span class="text-slate-400">{{ formatDateShort(c.created_at) }}</span>
                      </div>
                      <p class="text-xs text-slate-700 dark:text-zinc-300 leading-relaxed">{{ c.comment }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- RIGHT SIDEBAR (4 Cols) -->
              <div class="lg:col-span-4 p-6 bg-slate-50/40 dark:bg-zinc-900/60 space-y-5 text-xs font-semibold">
                <!-- Status -->
                <div class="space-y-1">
                  <label class="text-[10px] font-black uppercase text-slate-400">STATUS</label>
                  <select v-model="activeDrawerTask.task_column_id" @change="updateDrawerTaskField" class="w-full bg-white dark:bg-zinc-800 border rounded-xl px-3 py-2 text-xs font-bold">
                    <option v-for="col in taskStore.columns" :key="col.id" :value="col.id">{{ col.name }}</option>
                  </select>
                </div>

                <!-- Priority -->
                <div class="space-y-1">
                  <label class="text-[10px] font-black uppercase text-slate-400">PRIORITY</label>
                  <select v-model="activeDrawerTask.priority" @change="updateDrawerTaskField" class="w-full bg-white dark:bg-zinc-800 border rounded-xl px-3 py-2 text-xs font-bold">
                    <option value="low">Low</option>
                    <option value="medium">Normal</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                  </select>
                </div>

                <!-- Story Points -->
                <div class="space-y-1">
                  <label class="text-[10px] font-black uppercase text-slate-400">STORY POINTS</label>
                  <select v-model="activeDrawerTask.story_points" @change="updateDrawerTaskField" class="w-full bg-white dark:bg-zinc-800 border rounded-xl px-3 py-2 text-xs font-bold">
                    <option :value="null">Unestimated</option>
                    <option value="1">1 Point</option>
                    <option value="2">2 Points</option>
                    <option value="3">3 Points</option>
                    <option value="5">5 Points</option>
                    <option value="8">8 Points</option>
                  </select>
                </div>

                <!-- Assignees -->
                <div class="space-y-1">
                  <label class="text-[10px] font-black uppercase text-slate-400">ASSIGNEES</label>
                  <div class="flex flex-wrap gap-1.5">
                    <div v-for="u in getTaskAssignees(activeDrawerTask)" :key="u.id" class="px-2 py-1 bg-slate-100 dark:bg-zinc-800 text-slate-800 dark:text-zinc-200 border border-slate-300 dark:border-zinc-700 rounded-lg text-[10px] font-bold flex items-center gap-1">
                      <span>{{ u.name }}</span>
                    </div>
                  </div>
                </div>

                <!-- Due Date -->
                <div class="space-y-1">
                  <label class="text-[10px] font-black uppercase text-slate-400">DUE DATE</label>
                  <input type="date" v-model="drawerDueDate" @change="updateDrawerDueDate" class="w-full bg-white dark:bg-zinc-800 border rounded-xl px-3 py-2 text-xs font-bold" />
                </div>

                <!-- Time Tracked -->
                <div class="space-y-1">
                  <label class="text-[10px] font-black uppercase text-slate-400">TIME TRACKED</label>
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-extrabold text-slate-800 dark:text-white">{{ activeDrawerTask.time_tracked_minutes || 0 }}m</span>
                    <button
                      @click="toggleTimer"
                      :class="['px-3 py-1 font-bold text-white rounded-lg text-xs cursor-pointer', isTimerRunning ? 'bg-slate-700' : 'bg-slate-900 dark:bg-slate-700']"
                    >
                      {{ isTimerRunning ? '⏸ Stop' : '▶ Start' }}
                    </button>
                  </div>
                </div>

                <!-- Attachments -->
                <div class="space-y-2 pt-2 border-t border-slate-200 dark:border-zinc-800">
                  <label class="text-[10px] font-black uppercase text-slate-400">ATTACHMENTS</label>
                  <div class="space-y-1">
                    <div v-for="att in (activeDrawerTask.attachments || [])" :key="att.id" class="p-2 bg-white dark:bg-zinc-800 rounded-lg border text-[11px] truncate flex justify-between">
                      <span class="truncate">📎 {{ att.file_name }}</span>
                    </div>
                  </div>
                </div>

                <!-- Delete Task Button -->
                <div class="pt-4 border-t border-slate-200 dark:border-zinc-800">
                  <button @click="handleDeleteTaskInDrawer" class="text-rose-600 hover:text-rose-700 text-xs font-black flex items-center gap-1.5 cursor-pointer">
                    <span>🗑️ Delete Task</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- MODAL: ADVANCED FILTERS -->
    <Teleport to="body">
      <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
        <div v-if="showFilterModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
          <div class="fixed inset-0 bg-slate-900/60 dark:bg-black/75 backdrop-blur-xs" @click="showFilterModal = false"></div>
          <div class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-zinc-800 overflow-hidden z-10 flex flex-col max-h-[90vh]">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-zinc-800">
              <h3 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                <span>Filters & Sort</span>
              </h3>
              <button @click="showFilterModal = false" class="text-slate-400 hover:text-slate-900 dark:hover:text-white p-1">✕</button>
            </div>

            <div class="p-6 space-y-6 overflow-y-auto custom-scrollbar text-xs">
              <div class="bg-slate-50/50 dark:bg-zinc-800/30 rounded-2xl p-4 space-y-4 border border-slate-100 dark:border-zinc-800">
                <label class="font-bold text-slate-800 dark:text-white flex items-center gap-1.5 text-[13px]">
                  <svg class="w-4 h-4 text-[#a855f7]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg> Sort By
                </label>
                <select v-model="filters.sortBy" class="w-full bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl px-3.5 py-2.5 text-[13px] font-medium text-slate-800 dark:text-zinc-200 focus:border-purple-500 outline-none">
                  <option value="board_order">Board Order</option>
                  <option value="title">Title</option>
                  <option value="priority">Priority</option>
                  <option value="due_date">Due Date</option>
                  <option value="created_at">Created Date</option>
                </select>

                <div class="grid grid-cols-2 gap-3">
                  <button
                    type="button"
                    @click="filters.sortDirection = 'asc'"
                    :class="['py-2.5 rounded-xl border font-semibold text-[13px] cursor-pointer transition-colors flex items-center justify-center gap-1.5', filters.sortDirection === 'asc' ? 'bg-[#a855f7] text-white border-[#a855f7] shadow-sm' : 'bg-white dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-700']"
                  >
                    ↑ Ascending
                  </button>
                  <button
                    type="button"
                    @click="filters.sortDirection = 'desc'"
                    :class="['py-2.5 rounded-xl border font-semibold text-[13px] cursor-pointer transition-colors flex items-center justify-center gap-1.5', filters.sortDirection === 'desc' ? 'bg-[#a855f7] text-white border-[#a855f7] shadow-sm' : 'bg-white dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-700']"
                  >
                    ↓ Descending
                  </button>
                </div>
              </div>

              <div class="space-y-4">
                <h4 class="font-bold text-slate-800 dark:text-white text-[13px] flex items-center gap-1.5">
                  <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                  Filters
                </h4>
                
                <div class="space-y-1">
                  <label class="block font-semibold text-slate-700 dark:text-zinc-300 text-[13px]">Search</label>
                  <input v-model="filters.search" type="text" placeholder="Search tasks..." class="w-full bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl px-3.5 py-2.5 text-[13px] font-medium text-slate-900 dark:text-white focus:border-purple-500 outline-none" />
                </div>
                
                <div class="space-y-1">
                  <label class="block font-semibold text-slate-700 dark:text-zinc-300 text-[13px] flex items-center gap-1.5"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg> Status</label>
                  <select v-model="filters.statusId" class="w-full bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl px-3.5 py-2.5 text-[13px] font-medium text-slate-800 dark:text-zinc-200 focus:border-purple-500 outline-none">
                    <option :value="null">All statuses</option>
                    <option v-for="col in taskStore.columns" :key="col.id" :value="col.id">{{ col.name }}</option>
                  </select>
                </div>

                <div class="space-y-1">
                  <label class="block font-semibold text-slate-700 dark:text-zinc-300 text-[13px] flex items-center gap-1.5"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path></svg> Priority</label>
                  <select v-model="filters.priority" class="w-full bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl px-3.5 py-2.5 text-[13px] font-medium text-slate-800 dark:text-zinc-200 focus:border-purple-500 outline-none">
                    <option value="all">All priorities</option>
                    <option value="urgent">Urgent</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                  </select>
                </div>

                <div class="space-y-1">
                  <label class="block font-semibold text-slate-700 dark:text-zinc-300 text-[13px] flex items-center gap-1.5"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> Assignee</label>
                  <select v-model="filters.assigneeId" class="w-full bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl px-3.5 py-2.5 text-[13px] font-medium text-slate-800 dark:text-zinc-200 focus:border-purple-500 outline-none">
                    <option value="all">All assignees</option>
                    <option v-for="user in taskStore.assignees" :key="user.id" :value="user.id">{{ user.name }}</option>
                  </select>
                </div>

                <div class="space-y-1">
                  <label class="block font-semibold text-slate-700 dark:text-zinc-300 text-[13px] flex items-center gap-1.5"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg> Tag</label>
                  <select v-model="filters.tag" class="w-full bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl px-3.5 py-2.5 text-[13px] font-medium text-slate-800 dark:text-zinc-200 focus:border-purple-500 outline-none">
                    <option value="">All tags</option>
                    <option v-for="tag in allAvailableTags" :key="tag" :value="tag">{{ tag }}</option>
                  </select>
                </div>

                <div class="space-y-1">
                  <label class="block font-semibold text-slate-700 dark:text-zinc-300 text-[13px] flex items-center gap-1.5"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> Due Date Range</label>
                  <div class="grid grid-cols-2 gap-2">
                    <input v-model="filters.dueDateFrom" type="date" class="w-full bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl px-3 py-2.5 text-[13px] font-medium text-slate-600 dark:text-zinc-300 focus:border-purple-500 outline-none" />
                    <input v-model="filters.dueDateTo" type="date" class="w-full bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl px-3 py-2.5 text-[13px] font-medium text-slate-600 dark:text-zinc-300 focus:border-purple-500 outline-none" />
                  </div>
                </div>

                <div class="space-y-1">
                  <label class="block font-semibold text-slate-700 dark:text-zinc-300 text-[13px] flex items-center gap-1.5"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> Created Date Range</label>
                  <div class="grid grid-cols-2 gap-2">
                    <input v-model="filters.createdDateFrom" type="date" class="w-full bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl px-3 py-2.5 text-[13px] font-medium text-slate-600 dark:text-zinc-300 focus:border-purple-500 outline-none" />
                    <input v-model="filters.createdDateTo" type="date" class="w-full bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl px-3 py-2.5 text-[13px] font-medium text-slate-600 dark:text-zinc-300 focus:border-purple-500 outline-none" />
                  </div>
                </div>

              </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between bg-white dark:bg-zinc-900">
              <div class="flex items-center gap-3">
                <button type="button" class="text-[13px] font-semibold text-slate-500 hover:text-slate-900 dark:hover:text-white flex items-center gap-1 cursor-pointer">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg> Saved
                </button>
                <button type="button" @click="resetFilters" class="text-[13px] font-semibold text-slate-500 hover:text-slate-900 dark:hover:text-white cursor-pointer pl-2">Clear All</button>
              </div>
              <div class="flex items-center gap-3">
                <button type="button" class="text-[13px] font-semibold text-[#a855f7] hover:text-purple-700 flex items-center gap-1 cursor-pointer">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg> Save
                </button>
                <button type="button" @click="showFilterModal = false" class="px-5 py-2 bg-[#a855f7] hover:bg-purple-600 text-white font-semibold rounded-xl text-[13px] shadow-sm cursor-pointer transition-colors">Apply</button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- CREATE BOARD MODAL -->
    <Teleport to="body">
      <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
        <div v-if="showCreateBoardModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
          <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showCreateBoardModal = false"></div>
          <div class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-3xl p-6 shadow-2xl border border-slate-200 dark:border-zinc-800 z-10 space-y-4">
            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Create New Task Board</h3>
            <input v-model="boardForm.name" type="text" placeholder="Board Name..." class="w-full bg-slate-50 dark:bg-zinc-800 border rounded-xl p-3 text-xs" />
            <textarea v-model="boardForm.description" placeholder="Description..." class="w-full bg-slate-50 dark:bg-zinc-800 border rounded-xl p-3 text-xs" rows="2"></textarea>
            <div class="flex justify-end gap-2">
              <button @click="showCreateBoardModal = false" class="px-4 py-2 border rounded-xl text-xs font-bold">Cancel</button>
              <button @click="handleSaveBoard" class="px-5 py-2 bg-slate-900 hover:bg-black text-white dark:bg-white dark:text-slate-900 font-bold rounded-xl text-xs">Create Board</button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
    <!-- FULL RICH CREATE / EDIT TASK POPUP MODAL (EXACT MATCH FOR IMAGES 1 & 2) -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div v-if="showCreateTaskModal" class="fixed inset-0 z-[120] flex items-center justify-center p-4 overflow-y-auto">
          <!-- Backdrop -->
          <div class="fixed inset-0 bg-slate-900/60 dark:bg-black/80 backdrop-blur-xs" @click="showCreateTaskModal = false"></div>

          <!-- Modal Dialog Container -->
          <div class="relative w-full max-w-2xl bg-white dark:bg-zinc-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-zinc-800 z-10 flex flex-col max-h-[90vh] overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between">
              <div class="flex items-center gap-2 text-slate-900 dark:text-white font-extrabold text-lg">
                <span class="text-xl leading-none">+</span>
                <span>{{ isEditingTask ? 'Edit Task' : 'Create Task' }}</span>
              </div>
              <button
                @click="showCreateTaskModal = false"
                class="p-2 text-slate-400 hover:text-slate-700 dark:hover:text-white rounded-xl transition-colors cursor-pointer"
              >
                ✕
              </button>
            </div>

            <!-- Body Content -->
            <div class="p-6 overflow-y-auto custom-scrollbar space-y-5">
              <!-- Task Title Input -->
              <div>
                <input
                  type="text"
                  v-model="taskForm.title"
                  placeholder="Task name..."
                  class="w-full px-4 py-3 rounded-2xl border border-slate-300 focus:border-slate-900 focus:ring-2 focus:ring-slate-900/20 text-slate-900 dark:text-white dark:bg-zinc-800 font-medium text-base outline-none transition-all placeholder:text-slate-400"
                  autofocus
                />
              </div>

              <!-- Description Rich Toolbar & Textarea -->
              <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700 dark:text-zinc-300 flex items-center gap-1.5">
                  📄 <span>Description</span>
                </label>
                <div class="border border-slate-200 dark:border-zinc-700 rounded-2xl overflow-hidden bg-white dark:bg-zinc-800 focus-within:border-slate-900 transition-all">
                  <!-- Formatting Toolbar (Matching Image 1) -->
                  <div class="flex flex-wrap items-center gap-1 p-2 bg-slate-50 dark:bg-zinc-800/80 border-b border-slate-200 dark:border-zinc-700 text-slate-600 dark:text-zinc-300 text-xs font-extrabold select-none">
                    <button type="button" class="px-2 py-1 hover:bg-slate-200 dark:hover:bg-zinc-700 rounded font-serif">B</button>
                    <button type="button" class="px-2 py-1 hover:bg-slate-200 dark:hover:bg-zinc-700 rounded italic">I</button>
                    <button type="button" class="px-2 py-1 hover:bg-slate-200 dark:hover:bg-zinc-700 rounded underline">U</button>
                    <button type="button" class="px-2 py-1 hover:bg-slate-200 dark:hover:bg-zinc-700 rounded line-through">S</button>
                    <span class="w-px h-4 bg-slate-300 dark:bg-zinc-700 mx-1"></span>
                    <button type="button" class="px-2 py-1 hover:bg-slate-200 dark:hover:bg-zinc-700 rounded font-mono text-[11px]">&lt;/&gt;</button>
                    <button type="button" class="px-2 py-1 hover:bg-slate-200 dark:hover:bg-zinc-700 rounded">H1</button>
                    <button type="button" class="px-2 py-1 hover:bg-slate-200 dark:hover:bg-zinc-700 rounded">H2</button>
                    <span class="w-px h-4 bg-slate-300 dark:bg-zinc-700 mx-1"></span>
                    <button type="button" class="px-2 py-1 hover:bg-slate-200 dark:hover:bg-zinc-700 rounded">:=</button>
                    <button type="button" class="px-2 py-1 hover:bg-slate-200 dark:hover:bg-zinc-700 rounded">1.</button>
                    <button type="button" class="px-2 py-1 hover:bg-slate-200 dark:hover:bg-zinc-700 rounded">”</button>
                    <span class="w-px h-4 bg-slate-300 dark:bg-zinc-700 mx-1"></span>
                    <button type="button" class="px-2 py-1 hover:bg-slate-200 dark:hover:bg-zinc-700 rounded">🔗</button>
                    <button type="button" class="px-2 py-1 hover:bg-slate-200 dark:hover:bg-zinc-700 rounded">🖼️</button>
                    <span class="w-px h-4 bg-slate-300 dark:bg-zinc-700 mx-1"></span>
                    <button type="button" class="px-2 py-1 hover:bg-slate-200 dark:hover:bg-zinc-700 rounded">↶</button>
                    <button type="button" class="px-2 py-1 hover:bg-slate-200 dark:hover:bg-zinc-700 rounded">↷</button>
                  </div>
                  <textarea
                    v-model="taskForm.description"
                    rows="4"
                    placeholder="Describe the task... (paste images with Ctrl+V)"
                    class="w-full p-3 bg-transparent text-xs text-slate-800 dark:text-white outline-none resize-none placeholder:text-slate-400"
                  ></textarea>
                </div>
              </div>

              <!-- Status, Priority, Due Date Row -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <!-- Status -->
                <div class="space-y-1">
                  <label class="text-xs font-bold text-slate-600 dark:text-zinc-400 flex items-center gap-1">
                    🥞 <span>Status</span>
                  </label>
                  <select
                    v-model="taskForm.task_column_id"
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 text-xs font-bold text-slate-800 dark:text-white outline-none focus:border-slate-900"
                  >
                    <option v-for="col in taskStore.columns" :key="col.id" :value="col.id">
                      {{ col.name }}
                    </option>
                  </select>
                </div>

                <!-- Priority -->
                <div class="space-y-1">
                  <label class="text-xs font-bold text-slate-600 dark:text-zinc-400 flex items-center gap-1">
                    🚩 <span>Priority</span>
                  </label>
                  <select
                    v-model="taskForm.priority"
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 text-xs font-bold text-slate-800 dark:text-white outline-none focus:border-slate-900"
                  >
                    <option value="low">🔵 Low</option>
                    <option value="medium">🔵 Normal</option>
                    <option value="high">🟠 High</option>
                    <option value="urgent">🔴 Urgent</option>
                  </select>
                </div>

                <!-- Due Date -->
                <div class="space-y-1">
                  <label class="text-xs font-bold text-slate-600 dark:text-zinc-400 flex items-center gap-1">
                    📅 <span>Due Date</span>
                  </label>
                  <input
                    type="date"
                    v-model="taskForm.due_date"
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 text-xs font-bold text-slate-800 dark:text-white outline-none focus:border-slate-900"
                  />
                </div>
              </div>

              <!-- Assignees -->
              <div class="space-y-1 relative" ref="assigneeDropdownRef">
                <label class="text-xs font-bold text-slate-600 dark:text-zinc-400 flex items-center gap-1">
                  👥 <span>Assignees</span>
                </label>
                
                <!-- Dropdown Trigger -->
                <div 
                  @click="showAssigneeDropdown = !showAssigneeDropdown"
                  class="w-full min-h-[42px] px-3.5 py-2 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 text-xs font-medium cursor-pointer flex items-center justify-between transition-colors hover:border-slate-300 dark:hover:border-zinc-600"
                >
                  <div class="flex flex-wrap gap-1.5 flex-1">
                    <span v-if="taskForm.assignee_ids && taskForm.assignee_ids.length === 0" class="text-slate-400 dark:text-zinc-500 my-0.5">Select assignees...</span>
                    <div v-else class="flex flex-wrap gap-1.5">
                      <span v-for="aId in taskForm.assignee_ids" :key="aId" class="flex items-center gap-1.5 bg-white dark:bg-zinc-700 px-2 py-0.5 rounded-md border border-slate-200 dark:border-zinc-600 text-[11px] text-slate-700 dark:text-zinc-200 font-bold shadow-sm">
                        <div class="w-4 h-4 rounded-full bg-slate-200 dark:bg-zinc-600 flex items-center justify-center text-[8px] font-black text-slate-600 dark:text-zinc-300 shrink-0">
                          {{ getInitials(getAssigneeName(aId)) }}
                        </div>
                        {{ getAssigneeName(aId) }}
                        <span @click.stop="toggleAssignee(aId)" class="cursor-pointer text-slate-400 hover:text-rose-500 pl-0.5">×</span>
                      </span>
                    </div>
                  </div>
                  <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>

                <!-- Dropdown Menu -->
                <div v-if="showAssigneeDropdown" class="absolute z-[110] w-full mt-1 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-lg max-h-56 overflow-y-auto custom-scrollbar p-1">
                  <div 
                    v-for="user in taskStore.assignees" 
                    :key="user.id"
                    @click="toggleAssignee(user.id)"
                    class="flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 dark:hover:bg-zinc-700/50 rounded-lg cursor-pointer transition-colors"
                  >
                    <div class="w-4 h-4 rounded border flex items-center justify-center shrink-0" :class="taskForm.assignee_ids.includes(user.id) ? 'bg-purple-500 border-purple-500' : 'border-slate-300 dark:border-zinc-600'">
                      <svg v-if="taskForm.assignee_ids.includes(user.id)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    
                    <div class="w-7 h-7 rounded-full bg-slate-200 dark:bg-zinc-600 flex items-center justify-center text-[11px] font-bold text-slate-600 dark:text-zinc-300 shrink-0 shadow-sm">
                      {{ getInitials(user.name) }}
                    </div>
                    
                    <span class="text-[13px] font-semibold text-slate-700 dark:text-zinc-200">{{ user.name }}</span>
                  </div>
                  <div v-if="taskStore.assignees.length === 0" class="px-3 py-4 text-[13px] text-slate-400 text-center font-medium">No users available</div>
                </div>
              </div>

              <!-- Tags -->
              <div class="space-y-1">
                <label class="text-xs font-bold text-slate-600 dark:text-zinc-400 flex items-center gap-1">
                  🏷️ <span>Tags</span>
                </label>
                <input
                  type="text"
                  v-model="tagsInput"
                  placeholder="Select tags... (comma separated)"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 text-xs font-bold text-slate-800 dark:text-white outline-none focus:border-slate-900 placeholder:text-slate-400"
                />
              </div>

              <!-- Attachments Dropzone -->
              <div class="space-y-2">
                <label class="text-xs font-bold text-slate-600 dark:text-zinc-400 flex items-center gap-1">
                  📎 <span>Attachments</span>
                </label>
                <div 
                  @click="$refs.fileInput.click()" 
                  @dragover.prevent 
                  @drop.prevent="handleFileDrop"
                  class="border-2 border-dashed border-slate-300 dark:border-zinc-700 bg-slate-50/50 dark:bg-zinc-800/40 rounded-2xl p-6 text-center text-slate-500 hover:text-slate-900 dark:hover:text-white hover:border-purple-400 transition-colors cursor-pointer space-y-2 relative"
                >
                  <input type="file" multiple ref="fileInput" class="hidden" @change="handleFileSelect" />
                  <div class="w-10 h-10 mx-auto rounded-full bg-slate-200 dark:bg-zinc-700 text-slate-800 dark:text-white flex items-center justify-center text-lg font-bold">
                    ↑
                  </div>
                  <p class="text-xs font-semibold">Drop files here, click to browse, or paste screenshots (Ctrl+V)</p>
                </div>
                <!-- Selected Files Preview -->
                <div v-if="selectedFiles.length > 0" class="flex flex-col gap-2 mt-2">
                  <div v-for="(file, index) in selectedFiles" :key="index" class="flex items-center justify-between p-2 rounded-lg border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 text-xs">
                    <span class="truncate font-medium text-slate-700 dark:text-zinc-300">{{ file.name }}</span>
                    <button @click.stop="removeFile(index)" class="text-rose-500 hover:text-rose-600 font-bold px-2 cursor-pointer">×</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Footer Actions -->
            <div class="px-6 pb-6 pt-4 bg-white dark:bg-zinc-900 flex items-center justify-end gap-3">
              <button
                type="button"
                @click="showCreateTaskModal = false"
                class="px-4 py-2 text-[13px] font-medium text-slate-600 hover:text-slate-900 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors cursor-pointer"
              >
                Cancel
              </button>
              <button
                type="button"
                @click="submitTaskModal"
                class="px-5 py-2 bg-purple-500 hover:bg-purple-600 text-white text-[13px] font-medium rounded-lg transition-colors cursor-pointer flex items-center gap-1.5"
              >
                <span>+</span>
                <span>{{ isEditingTask ? 'Update Task' : 'Create Task' }}</span>
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { useTaskStore } from '@/stores/task';

const taskStore = useTaskStore();

const currentView = ref('grid');
const activeViewTab = ref('board');

const showCreateBoardModal = ref(false);
const showCreateTaskModal = ref(false);
const showFilterModal = ref(false);
const showBatchStatusModal = ref(false);
const showBatchPriorityModal = ref(false);

const tagsInput = ref('');
const taskForm = reactive({
  title: '',
  description: '',
  task_column_id: null,
  priority: 'medium',
  due_date: null,
  assignee_ids: [],
});

const showAssigneeDropdown = ref(false);

const toggleAssignee = (userId) => {
  const idx = taskForm.assignee_ids.indexOf(userId);
  if (idx > -1) {
    taskForm.assignee_ids.splice(idx, 1);
  } else {
    taskForm.assignee_ids.push(userId);
  }
};

const getAssigneeName = (userId) => {
  const user = taskStore.assignees.find(u => u.id === userId);
  return user ? user.name : 'Unknown';
};

// Close dropdown on click outside
const assigneeDropdownRef = ref(null);
const handleClickOutside = (e) => {
  if (assigneeDropdownRef.value && !assigneeDropdownRef.value.contains(e.target)) {
    showAssigneeDropdown.value = false;
  }
};
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

const showTaskDrawer = ref(false);
const activeDrawerTask = ref(null);
const commentText = ref('');
const newChecklistItem = ref('');
const isTimerRunning = ref(false);
const drawerDueDate = ref(null);

const isEditingTask = ref(false);
const editingTaskId = ref(null);
const isSaving = ref(false);

const calendarMonth = ref(8);
const selectedTaskIds = ref([]);

const draggedTask = ref(null);
const dragOverColumnId = ref(null);

const collapsedColumnIds = ref([]);

const toggleColumnCollapse = (id) => {
  if (collapsedColumnIds.value.includes(id)) {
    collapsedColumnIds.value = collapsedColumnIds.value.filter(colId => colId !== id);
  } else {
    collapsedColumnIds.value.push(id);
  }
};

const isAddingColumn = ref(false);
const newColumnForm = reactive({ name: '', color: 'blue' });
const availableColors = ['blue', 'purple', 'rose', 'amber', 'emerald', 'slate'];

const getBgClass = (color) => {
  const map = {
    blue: 'bg-blue-500',
    purple: 'bg-purple-500',
    rose: 'bg-rose-500',
    amber: 'bg-amber-500',
    emerald: 'bg-emerald-500',
    slate: 'bg-slate-500'
  };
  return map[color] || 'bg-blue-500';
};

const cycleColumnColor = () => {
  const idx = availableColors.indexOf(newColumnForm.color);
  newColumnForm.color = availableColors[(idx + 1) % availableColors.length];
};

const submitNewColumn = async () => {
  if (!newColumnForm.name.trim()) return;
  if (taskStore.activeBoard) {
    await taskStore.createColumn(taskStore.activeBoard.id, { 
      name: newColumnForm.name.trim(), 
      color: newColumnForm.color,
      order: taskStore.columns.length 
    });
    newColumnForm.name = '';
    newColumnForm.color = 'blue';
    isAddingColumn.value = false;
  }
};

const cancelAddColumn = () => {
  newColumnForm.name = '';
  isAddingColumn.value = false;
};

const boardForm = reactive({ name: '', description: '' });

const filters = reactive({
  search: '',
  statusId: null,
  priority: 'all',
  assigneeId: 'all',
  tag: '',
  dueDateFrom: '',
  dueDateTo: '',
  createdDateFrom: '',
  createdDateTo: '',
  myTasksOnly: false,
  sortBy: 'board_order',
  sortDirection: 'asc',
});

const isFilterActive = computed(() => {
  return filters.search !== '' || filters.statusId !== null || filters.priority !== 'all' || filters.assigneeId !== 'all' || filters.tag !== '' || filters.myTasksOnly || filters.dueDateFrom !== '' || filters.dueDateTo !== '' || filters.createdDateFrom !== '' || filters.createdDateTo !== '';
});

const resetFilters = () => {
  filters.search = '';
  filters.statusId = null;
  filters.priority = 'all';
  filters.assigneeId = 'all';
  filters.tag = '';
  filters.dueDateFrom = '';
  filters.dueDateTo = '';
  filters.createdDateFrom = '';
  filters.createdDateTo = '';
  filters.myTasksOnly = false;
};

const allAvailableTags = computed(() => {
  const tags = new Set();
  (taskStore.tasks || []).forEach(t => {
    if (t.tags) t.tags.forEach(tg => tags.add(tg));
  });
  return Array.from(tags).sort();
});

// Filtered Tasks
const allFilteredTasks = computed(() => {
  let list = [...(taskStore.tasks || [])];

  if (filters.search) {
    const q = filters.search.toLowerCase();
    list = list.filter(t => t.title?.toLowerCase().includes(q) || t.description?.toLowerCase().includes(q));
  }
  
  if (filters.statusId) {
    list = list.filter(t => t.task_column_id === filters.statusId);
  }
  
  if (filters.priority !== 'all') {
    list = list.filter(t => t.priority === filters.priority);
  }
  
  if (filters.assigneeId !== 'all') {
    list = list.filter(t => {
      if (t.assignees && t.assignees.some(a => a.id === filters.assigneeId)) return true;
      if (t.assigned_to_id === filters.assigneeId) return true;
      return false;
    });
  }
  
  if (filters.tag) {
    list = list.filter(t => t.tags && t.tags.includes(filters.tag));
  }
  
  if (filters.dueDateFrom) {
    const fromDate = new Date(filters.dueDateFrom).getTime();
    list = list.filter(t => t.due_date && new Date(t.due_date).getTime() >= fromDate);
  }
  
  if (filters.dueDateTo) {
    const toDate = new Date(filters.dueDateTo).getTime();
    list = list.filter(t => t.due_date && new Date(t.due_date).getTime() <= toDate);
  }
  
  if (filters.createdDateFrom) {
    const fromDate = new Date(filters.createdDateFrom).getTime();
    list = list.filter(t => t.created_at && new Date(t.created_at).getTime() >= fromDate);
  }
  
  if (filters.createdDateTo) {
    const toDate = new Date(filters.createdDateTo).getTime();
    list = list.filter(t => t.created_at && new Date(t.created_at).getTime() <= toDate);
  }

  list.sort((a, b) => {
    let valA = a[filters.sortBy] || '';
    let valB = b[filters.sortBy] || '';
    if (valA < valB) return filters.sortDirection === 'asc' ? -1 : 1;
    if (valA > valB) return filters.sortDirection === 'asc' ? 1 : -1;
    return 0;
  });

  return list;
});

const getFilteredTasksForColumn = (colId) => {
  return allFilteredTasks.value.filter(t => t.task_column_id === colId);
};

// Drag & Drop
const handleDragStart = (event, task) => {
  draggedTask.value = task;
  event.dataTransfer.effectAllowed = 'move';
  event.dataTransfer.setData('text/plain', String(task.id));
};

const handleDragOver = (event, columnId) => {
  event.preventDefault();
  event.dataTransfer.dropEffect = 'move';
  if (dragOverColumnId.value !== columnId) {
    dragOverColumnId.value = columnId;
  }
};

const handleDragEnter = (event, columnId) => {
  event.preventDefault();
  dragOverColumnId.value = columnId;
};

const handleDragLeave = (event, columnId) => {
  if (event.currentTarget && !event.currentTarget.contains(event.relatedTarget)) {
    if (dragOverColumnId.value === columnId) {
      dragOverColumnId.value = null;
    }
  }
};

const handleDrop = async (event, columnId) => {
  event.preventDefault();
  dragOverColumnId.value = null;

  if (!draggedTask.value) return;
  const taskToMove = draggedTask.value;
  draggedTask.value = null;

  if (taskToMove.task_column_id === columnId) return;

  const originalColId = taskToMove.task_column_id;
  taskToMove.task_column_id = columnId;

  try {
    await taskStore.moveTask(taskToMove.id, columnId);
  } catch (error) {
    taskToMove.task_column_id = originalColId;
  }
};

const handleDragEnd = () => {
  draggedTask.value = null;
  dragOverColumnId.value = null;
};

// Drawer Actions
const openTaskDrawer = (task) => {
  activeDrawerTask.value = task;
  drawerDueDate.value = task.due_date ? task.due_date.substring(0, 10) : null;
  showTaskDrawer.value = true;
};

const closeTaskDrawer = () => {
  showTaskDrawer.value = false;
  activeDrawerTask.value = null;
};

const handleToggleStar = async (task) => {
  await taskStore.toggleStarTask(task.id);
};

const submitComment = async () => {
  if (!commentText.value.trim() || !activeDrawerTask.value) return;
  await taskStore.addTaskComment(activeDrawerTask.value.id, commentText.value);
  commentText.value = '';
};

const addChecklistItem = () => {
  if (!newChecklistItem.value.trim() || !activeDrawerTask.value) return;
  if (!activeDrawerTask.value.checklists) activeDrawerTask.value.checklists = [];
  activeDrawerTask.value.checklists.push({ text: newChecklistItem.value, completed: false });
  newChecklistItem.value = '';
  updateDrawerTaskField();
};

const toggleTimer = () => {
  isTimerRunning.value = !isTimerRunning.value;
};

const updateDrawerTaskField = async () => {
  if (!activeDrawerTask.value) return;
  await taskStore.updateTask(activeDrawerTask.value.id, {
    task_column_id: activeDrawerTask.value.task_column_id,
    priority: activeDrawerTask.value.priority,
    story_points: activeDrawerTask.value.story_points,
    checklists: activeDrawerTask.value.checklists,
  });
};

const updateDrawerDueDate = async () => {
  if (!activeDrawerTask.value) return;
  await taskStore.updateTask(activeDrawerTask.value.id, {
    due_date: drawerDueDate.value,
  });
};

const handleDeleteTaskInDrawer = async () => {
  if (activeDrawerTask.value && confirm('Delete this task?')) {
    await taskStore.deleteTask(activeDrawerTask.value.id);
    closeTaskDrawer();
  }
};

// Bulk Actions
const toggleTaskSelection = (taskId) => {
  const idx = selectedTaskIds.value.indexOf(taskId);
  if (idx > -1) {
    selectedTaskIds.value.splice(idx, 1);
  } else {
    selectedTaskIds.value.push(taskId);
  }
};

const selectAllTasks = (e) => {
  if (e.target.checked) {
    selectedTaskIds.value = allFilteredTasks.value.map(t => t.id);
  } else {
    selectedTaskIds.value = [];
  }
};

const handleBatchDelete = async () => {
  if (confirm(`Delete ${selectedTaskIds.value.length} selected tasks?`)) {
    await taskStore.performBulkAction({ action: 'delete', task_ids: selectedTaskIds.value });
    selectedTaskIds.value = [];
  }
};

// General Navigation
const openBoard = async (boardId) => {
  await taskStore.selectBoard(boardId);
  currentView.value = 'board';
};

const switchToGrid = () => {
  currentView.value = 'grid';
};

// File Attachment Logic
const fileInput = ref(null);
const selectedFiles = ref([]);

const handleFileSelect = (event) => {
  if (event.target.files) {
    for (let i = 0; i < event.target.files.length; i++) {
      selectedFiles.value.push(event.target.files[i]);
    }
  }
  if (fileInput.value) fileInput.value.value = '';
};

const handleFileDrop = (event) => {
  if (event.dataTransfer.files) {
    for (let i = 0; i < event.dataTransfer.files.length; i++) {
      selectedFiles.value.push(event.dataTransfer.files[i]);
    }
  }
};

const removeFile = (index) => {
  selectedFiles.value.splice(index, 1);
};

const openCreateTaskModal = (defaultColumnId = null) => {
  isEditingTask.value = false;
  editingTaskId.value = null;
  taskForm.title = '';
  taskForm.description = '';
  taskForm.task_column_id = defaultColumnId || (taskStore.columns[0] ? taskStore.columns[0].id : null);
  taskForm.priority = 'medium';
  taskForm.due_date = null;
  taskForm.assignee_ids = [];
  tagsInput.value = '';
  selectedFiles.value = [];
  showCreateTaskModal.value = true;
};

const editTaskModal = (task) => {
  isEditingTask.value = true;
  editingTaskId.value = task.id;
  taskForm.title = task.title;
  taskForm.description = task.description || '';
  taskForm.task_column_id = task.task_column_id;
  taskForm.priority = task.priority || 'medium';
  taskForm.due_date = task.due_date ? task.due_date.substring(0, 10) : null;
  taskForm.assignee_ids = task.assignees ? task.assignees.map(a => a.id) : (task.assigned_to_id ? [task.assigned_to_id] : []);
  tagsInput.value = task.tags ? task.tags.join(', ') : '';
  selectedFiles.value = [];
  showCreateTaskModal.value = true;
};

const submitTaskModal = async () => {
  if (!taskForm.title || !taskForm.title.trim()) return;

  const tagList = tagsInput.value ? tagsInput.value.split(',').map(t => t.trim()).filter(Boolean) : [];

  const payload = {
    title: taskForm.title.trim(),
    description: taskForm.description,
    task_column_id: taskForm.task_column_id,
    priority: taskForm.priority,
    due_date: taskForm.due_date,
    assignee_ids: taskForm.assignee_ids,
    tags: tagList,
    attachments: selectedFiles.value,
  };

  if (isEditingTask.value && editingTaskId.value) {
    await taskStore.updateTask(editingTaskId.value, payload);
    if (activeDrawerTask.value && activeDrawerTask.value.id === editingTaskId.value) {
      Object.assign(activeDrawerTask.value, payload);
    }
  } else {
    await taskStore.createTask(payload);
  }

  showCreateTaskModal.value = false;
};

const openCreateTaskForDate = (day) => {
  const colId = taskStore.columns[0] ? taskStore.columns[0].id : null;
  const title = prompt(`Enter Task Title for Aug ${day}:`);
  if (title) {
    taskStore.createTask({
      title: title,
      task_column_id: colId,
      priority: 'medium',
      due_date: `2026-08-${String(day).padStart(2, '0')}`,
    });
  }
};

const getTasksForDate = (day) => {
  const dateStr = `2026-08-${String(day).padStart(2, '0')}`;
  return allFilteredTasks.value.filter(t => t.due_date && t.due_date.includes(dateStr));
};

const handleSaveBoard = async () => {
  if (!boardForm.name.trim()) return;
  await taskStore.createBoard(boardForm);
  showCreateBoardModal.value = false;
};

const handleDeleteBoard = async (boardId) => {
  if (confirm('Delete board?')) {
    await taskStore.deleteBoard(boardId);
  }
};

const getColumnDotClass = (col) => {
  if (!col) return 'bg-purple-500';
  
  if (typeof col === 'object' && col.color) {
    const map = {
      blue: 'bg-blue-500',
      purple: 'bg-purple-500',
      rose: 'bg-rose-500',
      amber: 'bg-amber-500',
      emerald: 'bg-emerald-500',
      slate: 'bg-slate-500'
    };
    if (map[col.color]) return map[col.color];
  }

  const name = (typeof col === 'string' ? col : (col.name || '')).toLowerCase();
  if (name.includes('new') || name.includes('backlog')) return 'bg-purple-500';
  if (name.includes('ready')) return 'bg-rose-500';
  if (name.includes('progress')) return 'bg-amber-500';
  if (name.includes('done') || name.includes('completed')) return 'bg-emerald-500';
  return 'bg-purple-500';
};

const getColumnName = (colId) => {
  const col = taskStore.columns.find(c => c.id === colId);
  return col ? col.name : 'New';
};

const getPriorityBadgeClass = (priority) => {
  const classes = {
    urgent: 'bg-rose-50 text-rose-600 border-rose-100 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-900',
    high: 'bg-amber-50 text-amber-600 border-amber-100 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-900',
    medium: 'bg-blue-50 text-blue-600 border-blue-100 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-900',
    normal: 'bg-blue-50 text-blue-600 border-blue-100 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-900',
    low: 'bg-slate-50 text-slate-600 border-slate-100 dark:bg-zinc-800/50 dark:text-zinc-400 dark:border-zinc-700',
  };
  return classes[priority.toLowerCase()] || 'bg-slate-50 text-slate-600 border-slate-100';
};

const getTaskAssignees = (task) => {
  if (task.assignees && task.assignees.length > 0) return task.assignees;
  return task.assigned_to ? [task.assigned_to] : [];
};

const getInitials = (name) => {
  if (!name) return 'U';
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const formatDateShort = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const formatDateLong = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
};

const isOverdue = (dateStr) => {
  if (!dateStr) return false;
  const d = new Date(dateStr);
  const now = new Date();
  now.setHours(0, 0, 0, 0);
  return d < now;
};

const stripHtml = (html) => {
  if (!html) return '';
  return html.replace(/<[^>]*>?/gm, '');
};

onMounted(async () => {
  await taskStore.fetchBoards();
  await taskStore.fetchTasks();
  await taskStore.fetchAssignees();
});
</script>
