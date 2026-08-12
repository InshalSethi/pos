<template>
  <div class="space-y-4 max-w-full font-sans">
    <!-- Header Section (No-Icon High Contrast System Theme) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Expense Claims</h1>
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">Manage employee out-of-pocket expense reimbursements integrated with Payments Out</p>
      </div>
      <div class="flex items-center gap-2.5">
        <button
          @click="openClaimModal"
          class="bg-slate-900 hover:bg-slate-800 text-white dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-semibold px-4 py-2 rounded-xl text-xs transition-all shadow-xs cursor-pointer"
        >
          Submit Expense Claim
        </button>
      </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl shadow-xs p-4 sm:p-5 space-y-4">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-100 dark:border-zinc-800">
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Claim #</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Employee</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Category</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Amount</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Date</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Status</th>
              <th class="py-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-xs">
            <tr v-for="claim in claims" :key="claim.id" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/40 transition-colors">
              <td class="py-2.5 px-3 font-mono font-bold text-slate-900 dark:text-slate-100">{{ claim.claimNo }}</td>
              <td class="py-2.5 px-3 font-semibold text-slate-900 dark:text-slate-100">{{ claim.name }}</td>
              <td class="py-2.5 px-3 text-slate-700 dark:text-slate-300">{{ claim.category }}</td>
              <td class="py-2.5 px-3 font-mono font-bold text-slate-900 dark:text-slate-100">${{ claim.amount.toFixed(2) }}</td>
              <td class="py-2.5 px-3 font-mono text-slate-700 dark:text-slate-300">{{ claim.date }}</td>
              <td class="py-2.5 px-3">
                <span :class="[
                  'px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider',
                  claim.status === 'Approved' ? 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-slate-400'
                ]">
                  {{ claim.status }}
                </span>
              </td>
              <td class="py-2.5 px-3 text-right">
                <button v-if="claim.status === 'Pending'" @click="approveClaim(claim)" class="text-xs font-bold text-slate-900 dark:text-slate-100 hover:underline cursor-pointer">
                  Approve & Reimburse
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

const claims = ref([
  { id: 1, claimNo: 'EX-2026-001', name: 'Michael Brown', category: 'Travel & Meals', amount: 145.50, date: '2026-08-10', status: 'Pending' },
  { id: 2, claimNo: 'EX-2026-002', name: 'Alice Cooper', category: 'Office Supplies', amount: 62.00, date: '2026-08-08', status: 'Approved' },
]);

const openClaimModal = () => {
  alert('Submit expense claim modal opened');
};

const approveClaim = (claim) => {
  claim.status = 'Approved';
};
</script>
