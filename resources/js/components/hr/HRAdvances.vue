<template>
  <div class="space-y-4 max-w-full font-sans">
    <!-- Header Section (No-Icon High Contrast System Theme) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Employee Advances & Loans</h1>
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">Track employee salary advances, loan deductions, and repayments</p>
      </div>
      <div class="flex items-center gap-2.5">
        <button
          @click="openAdvanceModal"
          class="bg-slate-900 hover:bg-slate-800 text-white dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-semibold px-4 py-2 rounded-xl text-xs transition-all shadow-xs cursor-pointer"
        >
          Issue Advance
        </button>
      </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl shadow-xs p-4 sm:p-5 space-y-4">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-100 dark:border-zinc-800">
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Employee</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Advance Date</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Amount Issued</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Repaid Amount</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Balance Outstanding</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-xs">
            <tr v-for="adv in advances" :key="adv.id" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/40 transition-colors">
              <td class="py-2.5 px-3 font-semibold text-slate-900 dark:text-slate-100">{{ adv.name }}</td>
              <td class="py-2.5 px-3 font-mono text-slate-700 dark:text-slate-300">{{ adv.date }}</td>
              <td class="py-2.5 px-3 font-mono text-slate-900 dark:text-slate-100">${{ adv.amount.toFixed(2) }}</td>
              <td class="py-2.5 px-3 font-mono text-slate-700 dark:text-slate-300">${{ adv.repaid.toFixed(2) }}</td>
              <td class="py-2.5 px-3 font-mono font-bold text-slate-900 dark:text-slate-100">${{ (adv.amount - adv.repaid).toFixed(2) }}</td>
              <td class="py-2.5 px-3">
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900">
                  {{ adv.status }}
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
import { ref } from 'vue';

const advances = ref([
  { id: 1, name: 'John Doe', date: '2026-08-01', amount: 500, repaid: 250, status: 'Active' },
  { id: 2, name: 'Sarah Smith', date: '2026-07-15', amount: 300, repaid: 300, status: 'Fully Repaid' },
]);

const openAdvanceModal = () => {
  alert('Issue employee advance form opened');
};
</script>
