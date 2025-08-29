<template>
  <div class="bg-white overflow-hidden shadow-lg rounded-lg">
    <div class="p-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Devices</h3>
      
      <div class="h-80 flex items-center justify-center">
        <div class="relative w-64 h-64">
          <canvas ref="chartCanvas"></canvas>
        </div>
      </div>
      
      <!-- Legend -->
      <div class="mt-6 space-y-2">
        <div 
          v-for="device in data" 
          :key="device.name"
          class="flex items-center justify-between"
        >
          <div class="flex items-center">
            <div 
              class="w-3 h-3 rounded mr-3"
              :style="{ backgroundColor: device.color }"
            ></div>
            <span class="text-sm text-gray-700">{{ device.name }}</span>
          </div>
          <div class="flex items-center space-x-2">
            <span class="text-sm font-medium text-gray-900">{{ device.value }}%</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import {
  Chart,
  ArcElement,
  Tooltip,
  Legend
} from 'chart.js';

// Register Chart.js components
Chart.register(ArcElement, Tooltip, Legend);

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

  const labels = props.data.map(item => item.name);
  const values = props.data.map(item => item.value);
  const colors = props.data.map(item => item.color);

  chartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: labels,
      datasets: [{
        data: values,
        backgroundColor: colors,
        borderColor: '#ffffff',
        borderWidth: 2,
        hoverBorderWidth: 3,
        hoverOffset: 4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '60%',
      plugins: {
        legend: {
          display: false // We'll use custom legend
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          titleColor: '#fff',
          bodyColor: '#fff',
          borderColor: '#374151',
          borderWidth: 1,
          cornerRadius: 8,
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
