<template>
  <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm rounded-2xl border border-zinc-200 dark:border-zinc-800">
    <div class="p-6">
      <h3 class="text-sm font-semibold text-slate-800 dark:text-zinc-100 uppercase tracking-wider mb-4">Devices Breakdown</h3>
      
      <div class="h-72 flex items-center justify-center">
        <div class="relative w-56 h-56">
          <canvas ref="chartCanvas"></canvas>
        </div>
      </div>
      
      <!-- Legend -->
      <div class="mt-4 space-y-2.5">
        <div 
          v-for="(device, index) in formattedData" 
          :key="device.name"
          class="flex items-center justify-between p-2.5 bg-zinc-50 dark:bg-zinc-950/60 rounded-xl border border-zinc-200/70 dark:border-zinc-800"
        >
          <div class="flex items-center">
            <div 
              class="w-3 h-3 rounded-full mr-3"
              :style="{ backgroundColor: device.monoColor }"
            ></div>
            <span class="text-xs font-medium text-zinc-800 dark:text-zinc-200">{{ device.name }}</span>
          </div>
          <div class="flex items-center space-x-2">
            <span class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">{{ device.value }}%</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed, nextTick } from 'vue';
import {
  Chart,
  ArcElement,
  Tooltip,
  Legend,
  DoughnutController
} from 'chart.js';

// Register Chart.js components
Chart.register(ArcElement, DoughnutController, Tooltip, Legend);

// Props
const props = defineProps({
  data: {
    type: Array,
    default: () => []
  }
});

// Refs
const chartCanvas = ref(null);
let chartInstance = null;

const monoPaletteLight = ['#000000', '#3f3f46', '#71717a', '#a1a1aa', '#d4d4d8'];
const monoPaletteDark = ['#ffffff', '#e4e4e7', '#a1a1aa', '#71717a', '#3f3f46'];

const formattedData = computed(() => {
  const isDark = document.documentElement.classList.contains('dark');
  const palette = isDark ? monoPaletteDark : monoPaletteLight;
  return props.data.map((item, index) => ({
    ...item,
    monoColor: palette[index % palette.length]
  }));
});

// Methods
const createChart = () => {
  if (!chartCanvas.value || !props.data.length) return;

  const ctx = chartCanvas.value.getContext('2d');
  const isDark = document.documentElement.classList.contains('dark');
  
  // Destroy existing chart if it exists
  if (chartInstance) {
    chartInstance.destroy();
  }

  const labels = formattedData.value.map(item => item.name);
  const values = formattedData.value.map(item => item.value);
  const colors = formattedData.value.map(item => item.monoColor);

  chartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: labels,
      datasets: [{
        data: values,
        backgroundColor: colors,
        borderColor: isDark ? '#18181b' : '#ffffff',
        borderWidth: 3,
        hoverBorderWidth: 4,
        hoverOffset: 4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '65%',
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          backgroundColor: isDark ? '#18181b' : '#000000',
          titleColor: '#ffffff',
          bodyColor: '#ffffff',
          borderColor: '#27272a',
          borderWidth: 1,
          cornerRadius: 10,
          padding: 12,
          callbacks: {
            label: function(context) {
              return `${context.label}: ${context.parsed}%`;
            }
          }
        }
      },
      elements: {
        arc: {
          borderRadius: 4
        }
      },
      interaction: {
        intersect: false
      }
    }
  });
};

// Watchers
watch(() => props.data, () => {
  nextTick(() => {
    createChart();
  });
}, { deep: true });

// Lifecycle
onMounted(() => {
  nextTick(() => {
    createChart();
  });
});
</script>
