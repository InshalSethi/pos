<template>
  <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-75 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
      <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-indigo-50">
        <h3 class="text-xl font-bold text-indigo-900 flex items-center">
          <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h2M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z"></path>
          </svg>
          Barcode Scanner
        </h3>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <div class="p-6">
        <div id="reader" class="overflow-hidden rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 aspect-square flex items-center justify-center">
          <div class="text-center p-4">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <p class="text-sm text-gray-500 font-medium">Initializing camera...</p>
          </div>
        </div>

        <div class="mt-6 space-y-3">
          <div class="flex items-center text-xs text-gray-500 bg-blue-50 p-3 rounded-lg border border-blue-100 italic">
            <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Point your camera at a barcode to scan automatically.
          </div>
          
          <button
            @click="$emit('close')"
            class="w-full py-3 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-all duration-200 uppercase tracking-wider text-sm shadow-sm"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import { Html5QrcodeScanner } from 'html5-qrcode';

const emit = defineEmits(['scan', 'close']);

let html5QrcodeScanner = null;

onMounted(() => {
  try {
    html5QrcodeScanner = new Html5QrcodeScanner(
      "reader",
      { 
        fps: 10, 
        qrbox: { width: 250, height: 250 },
        showZoomSliderIfSupported: true,
        defaultZoomValueIfSupported: 2
      },
      /* verbose= */ false
    );

    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
  } catch (error) {
    console.error("Failed to initialize html5QrcodeScanner:", error);
  }
});

function onScanSuccess(decodedText, decodedResult) {
  emit('scan', decodedText);
  if (html5QrcodeScanner) {
    try {
      html5QrcodeScanner.clear().catch(error => {
        console.error("Failed to clear html5QrcodeScanner", error);
      });
    } catch (err) {
      console.error("Failed to clear html5QrcodeScanner synchronously", err);
    }
  }
}

function onScanFailure(error) {
  // Silent failure is okay for continuous scanning
}

onUnmounted(() => {
  if (html5QrcodeScanner) {
    try {
      html5QrcodeScanner.clear().catch(error => {
        console.error("Failed to clear html5QrcodeScanner", error);
      });
    } catch (err) {
      console.error("Failed to clear html5QrcodeScanner synchronously", err);
    }
  }
});
</script>

<style>
#reader {
  border: none !important;
}
#reader __dashboard_section_csr_button {
  background-color: #4f46e5 !important;
  color: white !important;
  padding: 8px 16px !important;
  border-radius: 8px !important;
  font-weight: 500 !important;
}
#reader __dashboard_section_csr_button:hover {
  background-color: #4338ca !important;
}
#reader img {
  display: none !important;
}
/* Professional styling for the scanner internal elements */
#reader button {
  background-color: #4f46e5 !important;
  color: white !important;
  border: none !important;
  padding: 8px 16px !important;
  border-radius: 8px !important;
  font-weight: 500 !important;
  cursor: pointer !important;
  transition: all 0.2s !important;
}
#reader button:hover {
  background-color: #4338ca !important;
}
#reader select {
  padding: 8px !important;
  border-radius: 8px !important;
  border: 1px solid #e5e7eb !important;
  margin-bottom: 10px !important;
}
</style>
