<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl max-w-2xl w-full border border-slate-200 dark:border-zinc-800 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
      
      <!-- Header -->
      <div class="px-6 py-4 border-b border-slate-200 dark:border-zinc-800 flex items-center justify-between bg-slate-50/50 dark:bg-zinc-900/50">
        <div class="flex items-center space-x-3">
          <div class="w-9 h-9 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 flex items-center justify-center font-bold text-xs shadow-xs">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Activity Log Details</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Log #{{ log?.id }} — {{ formatDate(log?.created_at) }}</p>
          </div>
        </div>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Content Body -->
      <div class="p-6 space-y-6 overflow-y-auto">
        <!-- Overview summary -->
        <div class="bg-slate-50 dark:bg-zinc-800/50 rounded-xl p-4 border border-slate-200/80 dark:border-zinc-800 grid grid-cols-2 gap-4 text-xs">
          <div>
            <span class="text-slate-400 dark:text-zinc-500 font-medium block mb-0.5">Actor:</span>
            <span class="font-bold text-slate-900 dark:text-white">{{ log?.actor_name }} ({{ log?.actor_role }})</span>
          </div>
          <div>
            <span class="text-slate-400 dark:text-zinc-500 font-medium block mb-0.5">Category & Event:</span>
            <span class="font-bold text-slate-900 dark:text-white capitalize">{{ log?.log_type }} / {{ log?.event }}</span>
          </div>
          <div>
            <span class="text-slate-400 dark:text-zinc-500 font-medium block mb-0.5">Description:</span>
            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ log?.description }}</span>
          </div>
          <div>
            <span class="text-slate-400 dark:text-zinc-500 font-medium block mb-0.5">IP Address & Agent:</span>
            <span class="font-mono text-[11px] text-slate-700 dark:text-slate-300">{{ log?.ip_address || '127.0.0.1' }}</span>
          </div>
        </div>

        <!-- Diffs Comparison (Old vs New) -->
        <div v-if="log?.properties && (log.properties.old || log.properties.new)" class="space-y-3">
          <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Attribute Changes</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- Old Values -->
            <div class="bg-rose-50/50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/50 rounded-xl p-4">
              <span class="text-xs font-bold text-rose-700 dark:text-rose-400 uppercase tracking-wider block mb-2">Original Value (Before)</span>
              <div v-if="log.properties.old" class="space-y-1.5 text-xs font-mono">
                <div v-for="(val, key) in log.properties.old" :key="'old-'+key" class="flex justify-between border-b border-rose-100 dark:border-rose-900/40 pb-1">
                  <span class="font-semibold text-rose-900 dark:text-rose-300">{{ key }}:</span>
                  <span class="text-rose-700 dark:text-rose-400">{{ formatValue(val) }}</span>
                </div>
              </div>
              <p v-else class="text-xs text-rose-400 italic">No prior value recorded</p>
            </div>

            <!-- New Values -->
            <div class="bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/50 rounded-xl p-4">
              <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider block mb-2">Updated Value (After)</span>
              <div v-if="log.properties.new" class="space-y-1.5 text-xs font-mono">
                <div v-for="(val, key) in log.properties.new" :key="'new-'+key" class="flex justify-between border-b border-emerald-100 dark:border-emerald-900/40 pb-1">
                  <span class="font-semibold text-emerald-900 dark:text-emerald-300">{{ key }}:</span>
                  <span class="text-emerald-700 dark:text-emerald-400">{{ formatValue(val) }}</span>
                </div>
              </div>
              <p v-else class="text-xs text-emerald-400 italic">No new attributes modified</p>
            </div>

          </div>
        </div>

        <!-- Raw Properties JSON view if non-diff format -->
        <div v-else-if="log?.properties" class="space-y-2">
          <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Property Details</h4>
          <pre class="bg-slate-900 text-emerald-400 p-4 rounded-xl text-xs overflow-x-auto font-mono max-h-60">{{ JSON.stringify(log.properties, null, 2) }}</pre>
        </div>
      </div>

      <!-- Footer -->
      <div class="px-6 py-3 border-t border-slate-200 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50 flex justify-end">
        <button @click="$emit('close')" class="px-4 py-2 bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-semibold rounded-xl text-xs hover:bg-slate-800 dark:hover:bg-slate-100 transition-colors">
          Close
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
defineProps({
  show: Boolean,
  log: Object,
});

defineEmits(['close']);

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleString();
};

const formatValue = (val) => {
  if (val === null || val === undefined) return 'null';
  if (typeof val === 'boolean') return val ? 'true' : 'false';
  if (typeof val === 'object') return JSON.stringify(val);
  return String(val);
};
</script>
