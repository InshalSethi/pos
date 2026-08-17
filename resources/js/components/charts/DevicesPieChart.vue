<template>
  <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-xs rounded-2xl border border-zinc-200 dark:border-zinc-800">
    <div class="p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-bold text-zinc-950 dark:text-white uppercase tracking-wider">Financial Breakdown</h3>
        <span class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-widest">Operations</span>
      </div>
      
      <div class="h-64 flex items-center justify-center">
        <div class="relative w-52 h-52">
          <canvas ref="chartCanvas"></canvas>
        </div>
      </div>
      
      <!-- Legend with Amounts and Percentages -->
      <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2.5">
        <div 
          v-for="item in formattedData" 
          :key="item.name"
          class="flex items-center justify-between p-2.5 bg-zinc-50 dark:bg-zinc-950/60 rounded-xl border border-zinc-200/70 dark:border-zinc-800"
        >
          <div class="flex items-center min-w-0 mr-2">
            <div 
              class="w-3 h-3 rounded-full mr-2.5 shrink-0"
              :style="{ backgroundColor: item.color }"
            ></div>
            <span class="text-xs font-semibold text-zinc-800 dark:text-zinc-200 truncate">{{ item.name }}</span>
          </div>
          <div class="flex items-center space-x-1.5 shrink-0 text-right">
            <span class="text-xs font-bold text-zinc-950 dark:text-white">{{ currencyStore.formatPrice(item.value || 0) }}</span>
            <span class="text-[10px] font-medium text-zinc-400">({{ item.percentage }}%)</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed, nextTick } from 'vue';
import { useCurrencyStore } from '@/stores/currency';
import {
  Chart,
  ArcElement,
  Tooltip,
  Legend,
  DoughnutController
} from 'chart.js';

// Register Chart.js components
Chart.register(ArcElement, DoughnutController, Tooltip, Legend);

const currencyStore = useCurrencyStore();

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

const formattedData = computed(() => {
  const total = props.data.reduce((acc, curr) => acc + (parseFloat(curr.value) || 0), 0);
  return props.data.map((item) => {
    const val = parseFloat(item.value) || 0;
    const percentage = total > 0 ? ((val / total) * 100).toFixed(1) : '0.0';
    return {
      ...item,
      value: val,
      percentage
    };
  });
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
  const colors = formattedData.value.map(item => item.color || '#3f3f46');

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
      cutout: '68%',
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
              const label = context.label || '';
              const val = context.parsed || 0;
              return `${label}: ${currencyStore.formatPrice(val)}`;
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
