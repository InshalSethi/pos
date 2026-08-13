<template>
  <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm rounded-2xl border border-zinc-200 dark:border-zinc-800">
    <div class="p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-zinc-100 uppercase tracking-wider">Sales & Purchases</h3>
        <div class="flex items-center space-x-4">
          <select class="text-xs font-semibold border border-zinc-300 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white rounded-xl px-3 py-1.5 focus:border-slate-300 focus:ring-2 focus:ring-slate-100 outline-none dark:focus:border-zinc-600 uppercase tracking-tight">
            <option>6 Months</option>
            <option>3 Months</option>
            <option>1 Month</option>
          </select>
        </div>
      </div>
      
      <div class="h-80">
        <canvas ref="chartCanvas"></canvas>
      </div>
      
      <!-- Legend -->
      <div class="flex items-center justify-center space-x-6 mt-6">
        <div class="flex items-center">
          <div class="w-3 h-3 bg-zinc-300 dark:bg-zinc-700 rounded-full mr-2"></div>
          <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Sales Target</span>
        </div>
        <div class="flex items-center">
          <div class="w-3 h-3 bg-black dark:bg-white rounded-full mr-2"></div>
          <span class="text-xs font-medium text-zinc-900 dark:text-white">Sales</span>
        </div>
        <div class="flex items-center">
          <div class="w-3 h-3 bg-zinc-500 dark:bg-zinc-400 rounded-full mr-2"></div>
          <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Purchases</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import {
  Chart,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  BarController
} from 'chart.js';

// Register Chart.js components
Chart.register(CategoryScale, LinearScale, BarElement, BarController, Title, Tooltip, Legend);

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

// Methods
const createChart = () => {
  if (!chartCanvas.value || !props.data.length) return;

  const ctx = chartCanvas.value.getContext('2d');
  const isDark = document.documentElement.classList.contains('dark');
  
  // Destroy existing chart if it exists
  if (chartInstance) {
    chartInstance.destroy();
  }

  const labels = props.data.map(item => item.date);
  const salesTargetData = props.data.map(item => item.sales_target);
  const salesData = props.data.map(item => item.sales);
  const purchasesData = props.data.map(item => item.purchases);

  chartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Sales Target',
          data: salesTargetData,
          backgroundColor: isDark ? '#27272a' : '#e4e4e7',
          borderColor: isDark ? '#3f3f46' : '#d4d4d8',
          borderWidth: 1,
          borderRadius: 6,
          borderSkipped: false,
        },
        {
          label: 'Sales',
          data: salesData,
          backgroundColor: isDark ? '#ffffff' : '#000000',
          borderColor: isDark ? '#ffffff' : '#000000',
          borderWidth: 1,
          borderRadius: 6,
          borderSkipped: false,
        },
        {
          label: 'Purchases',
          data: purchasesData,
          backgroundColor: isDark ? '#71717a' : '#52525b',
          borderColor: isDark ? '#a1a1aa' : '#3f3f46',
          borderWidth: 1,
          borderRadius: 6,
          borderSkipped: false,
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          mode: 'index',
          intersect: false,
          backgroundColor: isDark ? '#18181b' : '#000000',
          titleColor: '#ffffff',
          bodyColor: '#ffffff',
          borderColor: '#27272a',
          borderWidth: 1,
          cornerRadius: 10,
          padding: 12,
          callbacks: {
            label: function(context) {
              return `${context.dataset.label}: $${context.parsed.y.toLocaleString()}`;
            }
          }
        }
      },
      scales: {
        x: {
          grid: {
            display: false
          },
          ticks: {
            color: isDark ? '#a1a1aa' : '#71717a',
            font: {
              size: 11,
              weight: 'bold'
            }
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            color: isDark ? '#27272a' : '#e4e4e7',
            borderDash: [3, 3]
          },
          ticks: {
            color: isDark ? '#a1a1aa' : '#71717a',
            font: {
              size: 11
            },
            callback: function(value) {
              return '$' + value.toLocaleString();
            }
          }
        }
      },
      interaction: {
        mode: 'index',
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
