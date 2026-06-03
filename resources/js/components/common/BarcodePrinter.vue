<template>
  <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all scale-100 border border-white/20">
      <!-- Header -->
      <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white text-center relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
        <h3 class="text-xl font-extrabold uppercase tracking-widest relative z-10">Generate Barcode</h3>
        <p class="text-indigo-100 text-[10px] font-bold uppercase tracking-widest opacity-70 mt-1">Ready for instant printing</p>
      </div>

      <div class="p-8 space-y-6">
        <!-- Label Preview Area -->
        <div id="barcode-label" class="bg-white border-2 border-gray-100 rounded-2xl p-6 flex flex-col items-center justify-center shadow-inner min-h-[200px]">
          <h4 class="text-lg font-black text-gray-800 mb-1 uppercase tracking-tighter">{{ product.name }}</h4>
          <p class="text-xs font-bold text-gray-400 mb-4 uppercase tracking-widest">{{ product.sku }}</p>
          
          <!-- Mock Barcode using simple bars -->
          <div class="flex items-end space-x-[2px] h-16 mb-4">
            <div v-for="i in 30" :key="i" 
                 :class="[
                   'bg-black rounded-full',
                   Math.random() > 0.5 ? 'w-[2px]' : 'w-[4px]',
                   Math.random() > 0.3 ? 'h-full' : 'h-3/4'
                 ]">
            </div>
          </div>
          
          <p class="text-sm font-black text-gray-900 tracking-[0.4em] uppercase">{{ product.barcode || 'NO-BARCODE' }}</p>
          <p class="text-xl font-bold text-indigo-600 mt-2">${{ product.selling_price }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <button @click="close" class="px-6 py-3 bg-gray-50 text-gray-500 font-bold rounded-xl hover:bg-gray-100 transition-all border border-gray-200 text-xs tracking-widest uppercase">
            Cancel
          </button>
          <button @click="print" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl shadow-lg hover:shadow-indigo-200 hover:-translate-y-0.5 transition-all flex items-center justify-center text-xs tracking-widest uppercase group">
            <svg class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
            Print Label
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  show: Boolean,
  product: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close']);

const close = () => {
  emit('close');
};

const print = () => {
  const label = document.getElementById('barcode-label');
  const printWindow = window.open('', '_blank', 'width=600,height=600');
  
  if (printWindow) {
    printWindow.document.write(`
      <html>
        <head>
          <title>Print Barcode - ${props.product.name}</title>
          <script src="https://cdn.tailwindcss.com"><\/script>
          <style>
            @media print {
              body { margin: 0; padding: 20px; }
              #print-area { width: 100%; border: none !important; box-shadow: none !important; }
            }
          </style>
        </head>
        <body class="bg-white flex items-center justify-center min-h-screen">
          <div id="print-area" class="border-2 border-black p-8 text-center flex flex-col items-center">
            ${label.innerHTML}
          </div>
          <script>
            setTimeout(() => {
              window.print();
              window.close();
            }, 500);
          <\/script>
        </body>
      </html>
    `);
    printWindow.document.close();
  }
};
</script>
