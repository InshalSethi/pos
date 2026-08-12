<template>
  <div class="space-y-4 max-w-full font-sans">
    <!-- Header Section (No-Icon High Contrast System Theme) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Leave Management</h1>
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">Manage employee leave applications, quota balances, and approval workflows</p>
      </div>
      <div class="flex items-center gap-2.5">
        <button
          @click="openLeaveModal"
          class="bg-slate-900 hover:bg-slate-800 text-white dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-semibold px-4 py-2 rounded-xl text-xs transition-all shadow-xs cursor-pointer"
        >
          Request Leave
        </button>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
      <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-4 shadow-xs">
        <div class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Pending Requests</div>
        <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">3</div>
      </div>
      <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-4 shadow-xs">
        <div class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Approved This Month</div>
        <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">12</div>
      </div>
      <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-4 shadow-xs">
        <div class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Paid Leaves Taken</div>
        <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">24 Days</div>
      </div>
      <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-4 shadow-xs">
        <div class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Remaining Quotas</div>
        <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">18 Avg Days</div>
      </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl shadow-xs p-4 sm:p-5 space-y-4">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-100 dark:border-zinc-800">
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Employee</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Leave Type</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Dates</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Duration</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Status</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-xs">
            <tr v-for="leave in leaveRequests" :key="leave.id" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/40 transition-colors">
              <td class="py-2.5 px-3 font-semibold text-slate-900 dark:text-slate-100">{{ leave.name }}</td>
              <td class="py-2.5 px-3 text-slate-600 dark:text-slate-300">{{ leave.type }}</td>
              <td class="py-2.5 px-3 font-mono text-slate-700 dark:text-slate-300">{{ leave.dates }}</td>
              <td class="py-2.5 px-3 font-mono text-slate-700 dark:text-slate-300">{{ leave.days }} Days</td>
              <td class="py-2.5 px-3">
                <span :class="[
                  'px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider',
                  leave.status === 'Approved' ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-slate-400'
                ]">
                  {{ leave.status }}
                </span>
              </td>
              <td class="py-2.5 px-3 text-right">
                <button v-if="leave.status === 'Pending'" @click="approveLeave(leave)" class="text-xs font-bold text-slate-900 dark:text-slate-100 hover:underline cursor-pointer">
                  Approve
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const leaveRequests = ref([
  { id: 1, name: 'Alice Cooper', type: 'Annual Leave', dates: '2026-08-15 to 2026-08-18', days: 3, status: 'Approved' },
  { id: 2, name: 'Robert Paulson', type: 'Medical Leave', dates: '2026-08-13 to 2026-08-14', days: 2, status: 'Pending' },
]);

const openLeaveModal = () => {
  alert('Leave request application modal opened');
};

const approveLeave = (leave) => {
  leave.status = 'Approved';
};
</script>
