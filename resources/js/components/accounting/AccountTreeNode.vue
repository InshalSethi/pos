<template>
  <div class="select-none">
    <!-- Node Row Card with group for hover -->
    <div
      :class="[
        'group flex items-center justify-between p-3.5 my-1.5 rounded-2xl transition-all border shadow-sm',
        'bg-white dark:bg-zinc-900 border-slate-200/80 dark:border-zinc-800',
        'hover:border-indigo-400 dark:hover:border-indigo-700 hover:shadow-md'
      ]"
      :style="{ marginLeft: `${level * 16}px` }"
    >
      <!-- Left Column: Expand Arrow + Code + Title + Badges -->
      <div class="flex items-center gap-3 min-w-0 flex-1 pr-4">
        <!-- Expand Chevron / Spacer -->
        <button
          v-if="hasChildren"
          @click="toggleExpanded"
          type="button"
          class="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer shrink-0"
        >
          <svg
            :class="{ 'rotate-90 text-indigo-600 dark:text-indigo-400': expanded }"
            class="w-4 h-4 transition-transform duration-200"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
        <span v-else class="w-6 shrink-0 inline-block" />

        <!-- Account Code -->
        <span class="font-mono text-xs font-bold text-slate-400 dark:text-zinc-500 shrink-0">
          {{ account.code || account.account_code }}
        </span>

        <!-- Account Name -->
        <span class="text-sm font-semibold text-slate-800 dark:text-zinc-100 truncate">
          {{ account.name || account.account_name }}
        </span>

        <!-- Badges -->
        <span
          :class="getAccountTypeBadgeClass(account.account_type || account.type)"
          class="px-2.5 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider shrink-0"
        >
          {{ formatAccountType(account.account_type || account.type) }}
        </span>

        <!-- Plain Text-Only System Badge -->
        <span
          v-if="isLocked(account)"
          class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900/50 shrink-0"
        >
          System
        </span>
      </div>

      <!-- Right Column: Balance + Status + Right Action Container -->
      <div class="flex items-center gap-4 shrink-0">
        <div class="text-right">
          <span class="text-xs sm:text-sm font-extrabold block text-slate-900 dark:text-zinc-100 font-mono">
            {{ formatBalance(account) }}
          </span>
          <span
            :class="account.is_active !== false ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400'"
            class="text-[10px] font-semibold"
          >
            {{ account.is_active !== false ? 'Active' : 'Inactive' }}
          </span>
        </div>

        <!-- RIGHT ACTION CONTAINER -->
        <div class="flex items-center gap-1.5 shrink-0">
          <!-- HOVER-ONLY EDIT / DELETE BUTTONS -->
          <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-150 inline-flex items-center bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden shrink-0">
            <!-- Edit Button (Shown on hover for ALL accounts) -->
            <button
              @click="$emit('edit', account)"
              type="button"
              title="Edit Account"
              class="p-1.5 px-2.5 text-slate-500 dark:text-zinc-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
              </svg>
            </button>

            <!-- Delete Button (ONLY for NON-SYSTEM accounts on hover) -->
            <button
              v-if="!isLocked(account)"
              @click="$emit('delete', account)"
              type="button"
              title="Delete Account"
              class="p-1.5 px-2.5 border-l border-slate-200 dark:border-zinc-800 text-slate-500 dark:text-zinc-400 hover:text-rose-600 dark:hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>

          <!-- ALWAYS VISIBLE LOCK ICON FOR SYSTEM ACCOUNTS -->
          <span
            v-if="isLocked(account)"
            title="System Account Locked"
            class="p-1.5 px-2 rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-400 dark:text-zinc-500 border border-slate-200/80 dark:border-zinc-700/80 shrink-0 inline-flex items-center justify-center"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
          </span>
        </div>
      </div>
    </div>

    <!-- Children Sub-Tree with Dashed Connection Line -->
    <div
      v-if="hasChildren && expanded"
      class="border-l-2 border-dashed border-slate-200 dark:border-zinc-800 ml-4 pl-2 space-y-1.5 mt-1"
    >
      <AccountTreeNode
        v-for="child in account.children"
        :key="child.id"
        :account="child"
        :level="level + 1"
        @edit="$emit('edit', $event)"
        @delete="$emit('delete', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  account: {
    type: Object,
    required: true
  },
  level: {
    type: Number,
    default: 0
  }
});

const emit = defineEmits(['edit', 'delete']);

const expanded = ref(true);

const hasChildren = computed(() => {
  return props.account.children && props.account.children.length > 0;
});

const toggleExpanded = () => {
  expanded.value = !expanded.value;
};

const isLocked = (acc) => {
  if (!acc) return false;
  if (acc.is_system || acc.is_system_account) return true;
  const name = (acc.name || acc.account_name || '').toLowerCase();
  const code = (acc.code || acc.account_code || '');
  return name === 'cash' || name === 'cash on hand' || name === 'bank account' || code === '1010' || code === '1020';
};

const formatBalance = (acc) => {
  if (acc.formatted_balance) return acc.formatted_balance;
  const num = Number(acc.current_balance ?? acc.balance ?? acc.opening_balance ?? 0);
  return '$' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const getAccountTypeBadgeClass = (type) => {
  const t = (type || '').toLowerCase();
  const classes = {
    asset: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/40',
    liability: 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40',
    equity: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40',
    revenue: 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-400 border border-purple-200 dark:border-purple-800/40',
    expense: 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40'
  };
  return classes[t] || 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300';
};

const formatAccountType = (type) => {
  if (!type) return 'Account';
  return type.charAt(0).toUpperCase() + type.slice(1);
};
</script>
