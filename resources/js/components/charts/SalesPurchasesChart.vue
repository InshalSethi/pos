<template>
  <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-xs rounded-2xl border border-zinc-200 dark:border-zinc-800">
    <div class="p-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-xs font-bold text-slate-800 dark:text-zinc-100 uppercase tracking-wider">Sales & Purchases</h3>
          <p class="text-[11px] text-zinc-500 dark:text-zinc-400 font-medium mt-0.5">Real revenue vs procurement trend</p>
        </div>
        <div class="flex items-center space-x-3">
          <select 
            v-model="selectedPeriod" 
            @change="emitPeriodChange"
            class="text-xs font-bold border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-white rounded-xl px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-slate-300 dark:focus:ring-zinc-700 uppercase tracking-tight cursor-pointer shadow-xs"
          >
            <option value="7_days">7 Days</option>
            <option value="1_month">1 Month</option>
            <option value="3_months">3 Months</option>
            <option value="6_months">6 Months</option>
            <option value="1_year">1 Year</option>
          </select>
        </div>
      </div>
      
      <div class="h-80">
        <canvas ref="chartCanvas"></canvas>
      </div>
      
      <!-- Clean Legend (Only Sales & Purchases) -->
      <div class="flex items-center justify-center space-x-8 mt-6">
        <div class="flex items-center">
          <div class="w-3 h-3 bg-black dark:bg-white rounded-full mr-2"></div>
          <span class="text-xs font-bold text-zinc-900 dark:text-white">Real Sales</span>
        </div>
        <div class="flex items-center">
          <div class="w-3 h-3 bg-zinc-600 dark:bg-zinc-400 rounded-full mr-2"></div>
          <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300">Purchases</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import { useCurrencyStore } from '@/stores/currency';
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

Chart.register(CategoryScale, LinearScale, BarElement, BarController, Title, Tooltip, Legend);

const currencyStore = useCurrencyStore();

const props = defineProps({
  data: {
    type: Array,
    default: () => []
  },
  period: {
    type: String,
    default: '6_months'
  }
});

const emit = defineEmits(['period-change']);

const selectedPeriod = ref(props.period || '6_months');
const chartCanvas = ref(null);
let chartInstance = null;

const emitPeriodChange = () => {
  emit('period-change', selectedPeriod.value);
};

const createChart = () => {
  if (!chartCanvas.value) return;

  const ctx = chartCanvas.value.getContext('2d');
  const isDark = document.documentElement.classList.contains('dark');
  
  if (chartInstance) {
    chartInstance.destroy();
  }

  const labels = props.data.map(item => item.date);
  const salesData = props.data.map(item => item.sales);
  const purchasesData = props.data.map(item => item.purchases);

  const salesColor = isDark ? '#ffffff' : '#000000';
  const purchasesColor = isDark ? '#a1a1aa' : '#52525b';

  chartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Real Sales',
          data: salesData,
          backgroundColor: salesColor,
          borderColor: salesColor,
          borderWidth: 1,
          borderRadius: 6,
          borderSkipped: false,
          barPercentage: 0.6,
          categoryPercentage: 0.6
        },
        {
          label: 'Purchases',
          data: purchasesData,
          backgroundColor: purchasesColor,
          borderColor: purchasesColor,
          borderWidth: 1,
          borderRadius: 6,
          borderSkipped: false,
          barPercentage: 0.6,
          categoryPercentage: 0.6
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
          borderColor: isDark ? '#27272a' : '#27272a',
          borderWidth: 1,
          cornerRadius: 10,
          padding: 12,
          callbacks: {
            label: function(context) {
              return `${context.dataset.label}: ${currencyStore.formatPrice(context.parsed.y)}`;
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
              return (currencyStore.activeCurrency?.symbol || '$') + value.toLocaleString();
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

watch(() => props.data, () => {
  nextTick(() => {
    createChart();
  });
}, { deep: true });

watch(() => props.period, (newVal) => {
  if (newVal) selectedPeriod.value = newVal;
});

onMounted(() => {
  nextTick(() => {
    createChart();
  });
});
</script>
