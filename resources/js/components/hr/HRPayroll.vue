<template>
  <div class="space-y-4 max-w-full font-sans">
    <!-- Header Section (No-Icon High Contrast System Theme) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Payroll & Payslips</h1>
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">Manage pay calendars, generate monthly salary runs, and post COA accounting journal entries</p>
      </div>
      <div class="flex items-center gap-2.5">
        <button
          @click="generatePayrollRun"
          class="bg-slate-900 hover:bg-slate-800 text-white dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-semibold px-4 py-2 rounded-xl text-xs transition-all shadow-xs cursor-pointer"
        >
          Run Payroll Batch
        </button>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
      <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-4 shadow-xs">
        <div class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Current Month Payroll</div>
        <div class="text-lg font-bold text-slate-900 dark:text-white font-mono mt-1">$48,500.00</div>
      </div>
      <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-4 shadow-xs">
        <div class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Salary Payable COA (2110)</div>
        <div class="text-lg font-bold text-slate-900 dark:text-white font-mono mt-1">$12,400.00</div>
      </div>
      <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-4 shadow-xs">
        <div class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Processed Payslips</div>
        <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">48 Payslips</div>
      </div>
      <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl p-4 shadow-xs">
        <div class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Payroll Status</div>
        <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">Calculated</div>
      </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl shadow-xs p-4 sm:p-5 space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Recent Payroll Runs</h2>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-100 dark:border-zinc-800">
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Pay Period</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Total Employees</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Total Gross Salary</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">COA Entry Status</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-xs">
            <tr v-for="run in payrollRuns" :key="run.id" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/40 transition-colors">
              <td class="py-2.5 px-3 font-semibold text-slate-900 dark:text-slate-100">{{ run.period }}</td>
              <td class="py-2.5 px-3 text-slate-700 dark:text-slate-300 font-mono">{{ run.employeeCount }}</td>
              <td class="py-2.5 px-3 font-mono font-bold text-slate-900 dark:text-slate-100">${{ run.totalGross.toLocaleString() }}</td>
              <td class="py-2.5 px-3">
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900">
                  {{ run.journalStatus }}
                </span>
              </td>
              <td class="py-2.5 px-3 text-right">
                <button @click="viewRun(run)" class="text-xs font-bold text-slate-900 dark:text-slate-100 hover:underline cursor-pointer">
                  View Payslips
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

const payrollRuns = ref([
  { id: 1, period: 'August 2026', employeeCount: 48, totalGross: 48500, journalStatus: 'Posted (JE20260801)' },
  { id: 2, period: 'July 2026', employeeCount: 46, totalGross: 46200, journalStatus: 'Posted (JE20260701)' },
]);

const generatePayrollRun = () => {
  alert('Payroll calculation and COA GL Journal Entry generated successfully!');
};

const viewRun = (run) => {
  alert(`Viewing payslips for ${run.period}`);
};
</script>
