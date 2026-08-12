<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div 
        v-if="show" 
        class="fixed inset-0 z-[99999] flex items-center justify-center p-4 sm:p-6 overflow-y-auto bg-slate-900/40 dark:bg-zinc-950/85 backdrop-blur-md select-none transition-all duration-200"
        style="backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);"
        @click.self="close"
        tabindex="-1"
      >
        <div class="relative max-w-4xl w-full flex flex-col items-center justify-center my-auto">
          
          <!-- Top Control Header -->
          <div class="w-full flex items-center justify-between bg-white/95 dark:bg-zinc-900/95 border border-slate-200/80 dark:border-zinc-800 backdrop-blur-md rounded-2xl px-5 py-3.5 mb-4 shadow-xl text-slate-900 dark:text-white transition-all">
            <div class="flex items-center gap-3 min-w-0 pr-4">
              <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-zinc-800 border border-slate-200/80 dark:border-zinc-700 flex items-center justify-center text-slate-600 dark:text-zinc-300 font-bold text-xs shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <div class="min-w-0">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ title || 'Profile Image Preview' }}</h3>
                <p v-if="subtitle" class="text-[11px] font-medium text-slate-500 dark:text-zinc-400 truncate mt-0.5">{{ subtitle }}</p>
              </div>
            </div>

            <!-- Toolbar Controls -->
            <div class="flex items-center gap-2">
              <button 
                type="button" 
                @click="zoomOut" 
                title="Zoom Out"
                class="p-2 text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-zinc-800/90 hover:bg-slate-200 dark:hover:bg-zinc-700 border border-slate-200/60 dark:border-zinc-700/60 rounded-xl transition-all cursor-pointer shadow-xs"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
                </svg>
              </button>

              <button 
                type="button" 
                @click="resetTransform" 
                title="Reset Zoom & Rotation"
                class="px-2.5 py-1.5 text-xs font-mono font-bold text-slate-700 dark:text-zinc-300 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-zinc-800/90 hover:bg-slate-200 dark:hover:bg-zinc-700 border border-slate-200/60 dark:border-zinc-700/60 rounded-xl transition-all cursor-pointer shadow-xs"
              >
                {{ Math.round(zoom * 100) }}%
              </button>

              <button 
                type="button" 
                @click="zoomIn" 
                title="Zoom In"
                class="p-2 text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-zinc-800/90 hover:bg-slate-200 dark:hover:bg-zinc-700 border border-slate-200/60 dark:border-zinc-700/60 rounded-xl transition-all cursor-pointer shadow-xs"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                </svg>
              </button>

              <div class="w-px h-5 bg-slate-200 dark:bg-zinc-800 mx-1"></div>

              <button 
                type="button" 
                @click="rotateRight" 
                title="Rotate Right"
                class="p-2 text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-zinc-800/90 hover:bg-slate-200 dark:hover:bg-zinc-700 border border-slate-200/60 dark:border-zinc-700/60 rounded-xl transition-all cursor-pointer shadow-xs"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
              </button>

              <button 
                type="button" 
                @click="close" 
                title="Close Modal"
                class="p-2 text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 bg-slate-100 dark:bg-zinc-800/90 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-slate-200/60 dark:border-zinc-700/60 rounded-xl transition-all cursor-pointer ml-1 shadow-xs"
              >
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Main Image Display Container -->
          <div 
            class="relative w-full max-h-[75vh] min-h-[300px] flex items-center justify-center overflow-hidden rounded-2xl bg-white/90 dark:bg-zinc-900/90 border border-slate-200/80 dark:border-zinc-800/80 shadow-2xl p-4 cursor-grab active:cursor-grabbing transition-all"
            @click.self="close"
          >
            <img 
              v-if="imageUrl"
              :src="imageUrl" 
              :alt="title || 'Profile Image'"
              class="max-w-full max-h-[68vh] object-contain rounded-xl shadow-2xl transition-transform duration-200"
              :style="{ transform: `scale(${zoom}) rotate(${rotation}deg)` }"
            />
            <div v-else class="text-center py-16 text-slate-400 dark:text-zinc-500">
              <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <p class="text-xs font-medium">No image available to display</p>
            </div>
          </div>

          <p class="text-[11px] font-medium text-slate-600 dark:text-zinc-400 mt-3 text-center">
            Click outside or press <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-zinc-300 rounded text-[10px] font-mono shadow-xs">Esc</kbd> to close
          </p>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  imageUrl: {
    type: String,
    default: ''
  },
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['close']);

const zoom = ref(1);
const rotation = ref(0);

const zoomIn = () => {
  if (zoom.value < 3) zoom.value = +(zoom.value + 0.25).toFixed(2);
};

const zoomOut = () => {
  if (zoom.value > 0.5) zoom.value = +(zoom.value - 0.25).toFixed(2);
};

const rotateRight = () => {
  rotation.value = (rotation.value + 90) % 360;
};

const resetTransform = () => {
  zoom.value = 1;
  rotation.value = 0;
};

const close = () => {
  resetTransform();
  emit('close');
};

const handleKeydown = (e) => {
  if (!props.show) return;
  if (e.key === 'Escape') {
    close();
  }
};

watch(() => props.show, (newVal) => {
  if (newVal) {
    resetTransform();
  }
});

onMounted(() => {
  window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown);
});
</script>
