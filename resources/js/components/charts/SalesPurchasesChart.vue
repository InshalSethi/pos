<template>
  <div class="bg-white overflow-hidden shadow-lg rounded-lg">
    <div class="p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900">Sales & Purchases</h3>
        <div class="flex items-center space-x-4">
          <select class="text-sm border border-gray-300 rounded-md px-3 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500">
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
      <div class="flex items-center justify-center space-x-6 mt-4">
        <div class="flex items-center">
          <div class="w-3 h-3 bg-gray-300 rounded mr-2"></div>
          <span class="text-sm text-gray-600">Sales Target</span>
        </div>
        <div class="flex items-center">
          <div class="w-3 h-3 bg-red-500 rounded mr-2"></div>
          <span class="text-sm text-gray-600">Sales</span>
        </div>
        <div class="flex items-center">
          <div class="w-3 h-3 bg-blue-500 rounded mr-2"></div>
          <span class="text-sm text-gray-600">Purchases</span>
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
  Legend
} from 'chart.js';

// Register Chart.js components
Chart.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

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
          backgroundColor: '#E5E7EB',
          borderColor: '#D1D5DB',
          borderWidth: 1,
          borderRadius: 4,
          borderSkipped: false,
        },
        {
          label: 'Sales',
          data: salesData,
          backgroundColor: '#EF4444',
          borderColor: '#DC2626',
          borderWidth: 1,
          borderRadius: 4,
          borderSkipped: false,
        },
        {
          label: 'Purchases',
          data: purchasesData,
          backgroundColor: '#3B82F6',
          borderColor: '#2563EB',
          borderWidth: 1,
          borderRadius: 4,
          borderSkipped: false,
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false // We'll use custom legend
        },
        tooltip: {
          mode: 'index',
          intersect: false,
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          titleColor: '#fff',
          bodyColor: '#fff',
          borderColor: '#374151',
          borderWidth: 1,
          cornerRadius: 8,
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
            color: '#6B7280',
            font: {
              size: 12
            }
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            color: '#F3F4F6',
            borderDash: [2, 2]
          },
          ticks: {
            color: '#6B7280',
            font: {
              size: 12
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
      },
      elements: {
        bar: {
          borderRadius: 4
        }
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
