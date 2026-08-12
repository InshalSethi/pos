<template>
  <div class="space-y-4 max-w-full font-sans">
    <!-- Header Section (Clean No-Icon High Contrast Theme) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Attendance & Shifts</h1>
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">Track daily employee attendance, shift schedules, and working hours</p>
      </div>
      <div class="flex items-center gap-2.5">
        <button
          @click="openClockModal"
          class="bg-slate-900 hover:bg-slate-800 text-white dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-semibold px-4 py-2 rounded-xl text-xs transition-all shadow-xs cursor-pointer"
        >
          Record Attendance
        </button>
      </div>
    </div>

    <!-- Quick Stats Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
      <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-4 shadow-xs">
        <div class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Present Today</div>
        <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">42 / 48</div>
      </div>
      <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-4 shadow-xs">
        <div class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">On Leave</div>
        <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">4</div>
      </div>
      <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-4 shadow-xs">
        <div class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Late Arrivals</div>
        <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">2</div>
      </div>
      <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-4 shadow-xs">
        <div class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Active Shifts</div>
        <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">Day & Night</div>
      </div>
    </div>

    <!-- Filter & Table Card -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl shadow-xs p-4 sm:p-5 space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <input
          v-model="search"
          type="text"
          placeholder="Search by employee name or ID..."
          class="w-full sm:w-72 bg-slate-50 dark:bg-slate-800/50 border-0 rounded-xl py-1.5 px-3 text-xs font-medium text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-transparent transition-all"
        />
        <div class="flex items-center gap-2">
          <input
            v-model="selectedDate"
            type="date"
            class="bg-slate-50 dark:bg-slate-800/50 border-0 rounded-xl py-1.5 px-3 text-xs font-medium text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-0 focus:border-transparent"
          />
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-100 dark:border-zinc-800">
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Employee</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Shift</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Check In</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Check Out</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Total Hours</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-xs">
            <tr v-for="log in filteredLogs" :key="log.id" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/40 transition-colors">
              <td class="py-2.5 px-3 font-semibold text-slate-900 dark:text-slate-100">{{ log.name }}</td>
              <td class="py-2.5 px-3 text-slate-600 dark:text-slate-300">{{ log.shift }}</td>
              <td class="py-2.5 px-3 font-mono text-slate-700 dark:text-slate-300">{{ log.checkIn }}</td>
              <td class="py-2.5 px-3 font-mono text-slate-700 dark:text-slate-300">{{ log.checkOut }}</td>
              <td class="py-2.5 px-3 font-mono text-slate-700 dark:text-slate-300">{{ log.hours }}</td>
              <td class="py-2.5 px-3">
                <span :class="[
                  'px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider',
                  log.status === 'On Time' ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-slate-400'
                ]">
                  {{ log.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const search = ref('');
const selectedDate = ref(new Date().toISOString().substring(0, 10));

const attendanceLogs = ref([
  { id: 1, name: 'John Doe', shift: 'General Morning (09:00 - 18:00)', checkIn: '08:55 AM', checkOut: '06:05 PM', hours: '9.1 hrs', status: 'On Time' },
  { id: 2, name: 'Sarah Smith', shift: 'General Morning (09:00 - 18:00)', checkIn: '09:15 AM', checkOut: '06:00 PM', hours: '8.7 hrs', status: 'Late' },
  { id: 3, name: 'Michael Brown', shift: 'Night Shift (18:00 - 02:00)', checkIn: '05:58 PM', checkOut: '02:02 AM', hours: '8.0 hrs', status: 'On Time' },
]);

const filteredLogs = computed(() => {
  if (!search.value) return attendanceLogs.value;
  const q = search.value.toLowerCase();
  return attendanceLogs.value.filter(l => l.name.toLowerCase().includes(q));
});

const openClockModal = () => {
  alert('Attendance clocking feature initialized');
};
</script>
