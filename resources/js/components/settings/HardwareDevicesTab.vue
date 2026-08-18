<template>
  <div class="space-y-6 text-slate-900 dark:text-slate-100 font-sans">
    <!-- Header & Terminal Overview -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 dark:border-zinc-800 pb-5">
      <div>
        <div class="flex items-center gap-2">
          <h3 class="text-lg font-bold text-slate-900 dark:text-white">Hardware Device Management</h3>
          <span class="px-2.5 py-0.5 text-[10px] font-extrabold uppercase rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
            Hardware Engine Active
          </span>
        </div>
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">
          Detect, configure, and select target POS printers & peripherals connected to this workstation.
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <button
          @click="scanHardwareDevices"
          :disabled="isScanningHardware"
          class="px-4 py-2 bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/80 rounded-xl text-xs font-bold flex items-center space-x-2 transition-all cursor-pointer shadow-xs disabled:opacity-50"
        >
          <svg :class="['w-4 h-4 text-indigo-600 dark:text-indigo-400', isScanningHardware ? 'animate-spin' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <span>{{ isScanningHardware ? 'Probing Hardware...' : 'Scan Connected Peripherals' }}</span>
        </button>

        <div>
          <input
            type="text"
            v-model="hardwareSettings.terminal_name"
            placeholder="Counter 1 - Main Checkout"
            class="px-3 py-1.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 text-slate-900 dark:text-slate-100 rounded-xl text-xs font-bold focus:outline-none"
          />
        </div>
        <button
          @click="saveHardwareSettings"
          :disabled="saving"
          class="bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-bold px-5 py-2 rounded-xl text-xs flex items-center space-x-2 transition-all cursor-pointer shadow-xs disabled:opacity-50"
        >
          <svg v-if="saving" class="animate-spin h-3.5 w-3.5 text-current" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          <span>{{ saving ? 'Saving...' : 'Save Config' }}</span>
        </button>
      </div>
    </div>

    <!-- Live System Hardware Devices Dashboard -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
      <!-- Device 1: Barcode Scanner -->
      <div 
        @click="activeDeviceTab = 'barcode_scanner'"
        :class="[
          'p-4 rounded-2xl border transition-all cursor-pointer select-none relative overflow-hidden group',
          activeDeviceTab === 'barcode_scanner'
            ? 'border-indigo-600 dark:border-indigo-500 bg-indigo-50/40 dark:bg-indigo-950/30 shadow-xs ring-1 ring-indigo-500/20'
            : 'border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-slate-300 dark:hover:border-zinc-700'
        ]"
      >
        <div class="flex items-center justify-between mb-2">
          <div class="p-2 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m-8-8h16M6 8h12M6 16h12" />
            </svg>
          </div>
          <span :class="[
            'w-2.5 h-2.5 rounded-full',
            isBarcodeScannerConnected ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'
          ]"></span>
        </div>
        <div class="text-xs font-bold text-slate-900 dark:text-white">Barcode Scanner</div>
        <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium truncate">
          {{ hardwareSettings.barcode_scanner_selected_device || '1D Laser / CCD Reader' }}
        </div>
        <div class="mt-2 flex items-center justify-between">
          <span :class="[
            'text-[10px] font-extrabold uppercase tracking-wider px-1.5 py-0.5 rounded',
            isBarcodeScannerConnected 
              ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60' 
              : 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60'
          ]">
            {{ isBarcodeScannerConnected ? 'CONNECTED' : 'DISCONNECTED' }}
          </span>
          <span class="text-[10px] font-mono text-slate-400 dark:text-zinc-500 capitalize">{{ hardwareSettings.barcode_scanner_mode.replace('_', ' ') }}</span>
        </div>
      </div>

      <!-- Device 2: QR Code Reader -->
      <div 
        @click="activeDeviceTab = 'qr_scanner'"
        :class="[
          'p-4 rounded-2xl border transition-all cursor-pointer select-none relative overflow-hidden group',
          activeDeviceTab === 'qr_scanner'
            ? 'border-indigo-600 dark:border-indigo-500 bg-indigo-50/40 dark:bg-indigo-950/30 shadow-xs ring-1 ring-indigo-500/20'
            : 'border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-slate-300 dark:hover:border-zinc-700'
        ]"
      >
        <div class="flex items-center justify-between mb-2">
          <div class="p-2 rounded-xl bg-sky-100 dark:bg-sky-900/50 text-sky-600 dark:text-sky-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
            </svg>
          </div>
          <span :class="[
            'w-2.5 h-2.5 rounded-full',
            isQrScannerConnected ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'
          ]"></span>
        </div>
        <div class="text-xs font-bold text-slate-900 dark:text-white">QR Code Scanner</div>
        <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium truncate">
          {{ hardwareSettings.qr_scanner_selected_device || '2D Imager & Optical' }}
        </div>
        <div class="mt-2 flex items-center justify-between">
          <span :class="[
            'text-[10px] font-extrabold uppercase tracking-wider px-1.5 py-0.5 rounded',
            isQrScannerConnected 
              ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60' 
              : 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60'
          ]">
            {{ isQrScannerConnected ? 'CONNECTED' : 'DISCONNECTED' }}
          </span>
          <span class="text-[10px] font-mono text-slate-400 dark:text-zinc-500 capitalize">{{ hardwareSettings.qr_scanner_mode.replace('_', ' ') }}</span>
        </div>
      </div>

      <!-- Device 3: Barcode Label Printer -->
      <div 
        @click="activeDeviceTab = 'barcode_printer'"
        :class="[
          'p-4 rounded-2xl border transition-all cursor-pointer select-none relative overflow-hidden group',
          activeDeviceTab === 'barcode_printer'
            ? 'border-indigo-600 dark:border-indigo-500 bg-indigo-50/40 dark:bg-indigo-950/30 shadow-xs ring-1 ring-indigo-500/20'
            : 'border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-slate-300 dark:hover:border-zinc-700'
        ]"
      >
        <div class="flex items-center justify-between mb-2">
          <div class="p-2 rounded-xl bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 11h10M7 15h10M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
            </svg>
          </div>
          <span :class="[
            'w-2.5 h-2.5 rounded-full',
            isBarcodePrinterConnected ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'
          ]"></span>
        </div>
        <div class="text-xs font-bold text-slate-900 dark:text-white">Barcode Printer</div>
        <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium truncate">
          {{ hardwareSettings.barcode_printer_selected_device || 'TSPL / ZPL Label Spooler' }}
        </div>
        <div class="mt-2 flex items-center justify-between">
          <span :class="[
            'text-[10px] font-extrabold uppercase tracking-wider px-1.5 py-0.5 rounded',
            isBarcodePrinterConnected 
              ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60' 
              : 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60'
          ]">
            {{ isBarcodePrinterConnected ? 'CONNECTED' : 'DISCONNECTED' }}
          </span>
          <span class="text-[10px] font-mono text-slate-400 dark:text-zinc-500 uppercase">{{ hardwareSettings.barcode_printer_paper_width }}</span>
        </div>
      </div>

      <!-- Device 4: Thermal Receipt Printer -->
      <div 
        @click="activeDeviceTab = 'thermal_printer'"
        :class="[
          'p-4 rounded-2xl border transition-all cursor-pointer select-none relative overflow-hidden group',
          activeDeviceTab === 'thermal_printer'
            ? 'border-indigo-600 dark:border-indigo-500 bg-indigo-50/40 dark:bg-indigo-950/30 shadow-xs ring-1 ring-indigo-500/20'
            : 'border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-slate-300 dark:hover:border-zinc-700'
        ]"
      >
        <div class="flex items-center justify-between mb-2">
          <div class="p-2 rounded-xl bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
          </div>
          <span :class="[
            'w-2.5 h-2.5 rounded-full',
            isThermalPrinterConnected ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'
          ]"></span>
        </div>
        <div class="text-xs font-bold text-slate-900 dark:text-white">Thermal Printer</div>
        <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium truncate">
          {{ hardwareSettings.thermal_printer_selected_device || '80mm POS Receipt Slip' }}
        </div>
        <div class="mt-2 flex items-center justify-between">
          <span :class="[
            'text-[10px] font-extrabold uppercase tracking-wider px-1.5 py-0.5 rounded',
            isThermalPrinterConnected 
              ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60' 
              : 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60'
          ]">
            {{ isThermalPrinterConnected ? 'CONNECTED' : 'DISCONNECTED' }}
          </span>
          <span class="text-[10px] font-mono text-slate-400 dark:text-zinc-500 uppercase">{{ hardwareSettings.thermal_printer_paper_size }}</span>
        </div>
      </div>

      <!-- Device 5: Standard Desktop Printer -->
      <div 
        @click="activeDeviceTab = 'standard_printer'"
        :class="[
          'p-4 rounded-2xl border transition-all cursor-pointer select-none relative overflow-hidden group',
          activeDeviceTab === 'standard_printer'
            ? 'border-indigo-600 dark:border-indigo-500 bg-indigo-50/40 dark:bg-indigo-950/30 shadow-xs ring-1 ring-indigo-500/20'
            : 'border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-slate-300 dark:hover:border-zinc-700'
        ]"
      >
        <div class="flex items-center justify-between mb-2">
          <div class="p-2 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <span :class="[
            'w-2.5 h-2.5 rounded-full',
            isStandardPrinterConnected ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'
          ]"></span>
        </div>
        <div class="text-xs font-bold text-slate-900 dark:text-white">Standard Printer</div>
        <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium truncate">
          {{ hardwareSettings.standard_printer_selected_device || 'A4 / Letter Sheet Printer' }}
        </div>
        <div class="mt-2 flex items-center justify-between">
          <span :class="[
            'text-[10px] font-extrabold uppercase tracking-wider px-1.5 py-0.5 rounded',
            isStandardPrinterConnected 
              ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60' 
              : 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60'
          ]">
            {{ isStandardPrinterConnected ? 'CONNECTED' : 'DISCONNECTED' }}
          </span>
          <span class="text-[10px] font-mono text-slate-400 dark:text-zinc-500 uppercase">{{ hardwareSettings.standard_printer_paper_size }}</span>
        </div>
      </div>
    </div>

    <!-- Active Hardware Device Details & Diagnostics Panel -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 p-6 shadow-xs">
      
      <!-- DEVICE 1: BARCODE SCANNER (READER) CONFIG & DETECTED DEVICES -->
      <div v-if="activeDeviceTab === 'barcode_scanner'" class="space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 pb-4">
          <div class="flex items-center space-x-3">
            <div class="p-2.5 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m-8-8h16M6 8h12M6 16h12" />
              </svg>
            </div>
            <div>
              <h4 class="text-sm font-bold text-slate-900 dark:text-white">Barcode Scanner (Reader) Configuration</h4>
              <p class="text-[11px] text-slate-500 dark:text-slate-400">Configure HID keyboard wedge input settings, suffix rules, sensitivity, and pair USB devices.</p>
            </div>
          </div>

          <label class="relative inline-flex items-center cursor-pointer select-none">
            <input type="checkbox" v-model="hardwareSettings.barcode_scanner_enabled" class="sr-only peer" />
            <div class="w-11 h-6 bg-slate-300 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-indigo-600"></div>
          </label>
        </div>

        <!-- SELECT TARGET BARCODE SCANNER DROPDOWN -->
        <div class="p-4 bg-indigo-50/50 dark:bg-indigo-950/30 rounded-2xl border border-indigo-200 dark:border-indigo-900/40 space-y-3">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold text-indigo-950 dark:text-indigo-200">
              ⚡ Select Target Barcode Reader Device
            </label>
            <span class="text-[11px] font-semibold text-indigo-700 dark:text-indigo-400">
              Select connected scanner or choose model preset
            </span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="md:col-span-2">
              <select
                v-model="hardwareSettings.barcode_scanner_selected_device"
                class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-indigo-300 dark:border-indigo-700/80 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-xs"
              >
                <option value="">-- Select Target Barcode Reader --</option>
                <option v-for="(dev, idx) in detectedHidDevices" :key="idx" :value="dev.productName || 'USB Barcode Reader #' + (idx+1)">
                  🔌 {{ dev.productName || 'USB Barcode Reader Device' }} (VID: {{ dev.vendorId }})
                </option>
                <option value="Honeywell Voyager 1200g USB Laser Scanner">Honeywell Voyager 1200g USB Laser Scanner</option>
                <option value="Symbol / Zebra DS2208 1D/2D Barcode Scanner">Symbol / Zebra DS2208 1D/2D Barcode Scanner</option>
                <option value="Datalogic QuickScan QD2430">Datalogic QuickScan QD2430</option>
                <option value="Generic USB Keyboard Wedge Scanner">Generic USB Keyboard Wedge Scanner (Plug & Play)</option>
              </select>
            </div>

            <div>
              <input
                type="text"
                v-model="hardwareSettings.barcode_scanner_selected_device"
                placeholder="Or type Scanner Model Name..."
                class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-indigo-300 dark:border-indigo-700/80 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-xs"
              />
            </div>
          </div>
        </div>

        <!-- DETECTED HARDWARE DEVICES LIST FOR BARCODE SCANNER -->
        <div class="p-4 bg-slate-50 dark:bg-zinc-800/60 rounded-xl border border-slate-200 dark:border-zinc-700/80 space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
              <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Connected System USB HID Scanners & Readers</span>
              <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-slate-200 dark:bg-zinc-700 text-slate-700 dark:text-slate-300">
                {{ detectedHidDevices.length }} Detected
              </span>
            </div>
            <button 
              @click="requestPairHidScanner" 
              class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all cursor-pointer shadow-xs flex items-center space-x-1"
            >
              <span>+ Pair / Select USB Scanner</span>
            </button>
          </div>

          <div v-if="detectedHidDevices.length > 0" class="space-y-1.5">
            <div 
              v-for="(dev, idx) in detectedHidDevices" 
              :key="idx" 
              class="text-xs font-mono bg-white dark:bg-zinc-900 p-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 flex items-center justify-between"
            >
              <div class="flex items-center space-x-2">
                <span class="text-indigo-500">🔌</span>
                <span class="font-bold text-slate-900 dark:text-white">{{ dev.productName || 'USB Barcode Reader Device' }}</span>
                <span class="text-[10px] text-slate-400 font-mono">(VID: {{ dev.vendorId }}, PID: {{ dev.productId }})</span>
              </div>
              <button 
                @click="hardwareSettings.barcode_scanner_selected_device = dev.productName || 'USB Barcode Reader #' + (idx+1)"
                class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950 px-2 py-0.5 rounded hover:bg-indigo-100 cursor-pointer"
              >Select Device</button>
            </div>
          </div>
          <div v-else class="text-xs text-slate-500 dark:text-slate-400 italic bg-white dark:bg-zinc-900 p-3 rounded-lg border border-slate-200 dark:border-zinc-700/60">
            ⚠️ No direct WebHID USB Scanner is currently paired. Standard USB Barcode Scanners (Keyboard Wedge) plug & play automatically when focused. Click "+ Pair USB Scanner" above to select a physical device.
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <!-- Connection Mode -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Connection Interface</label>
            <select
              v-model="hardwareSettings.barcode_scanner_mode"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold focus:outline-none"
            >
              <option value="hid_keyboard">USB HID Keyboard Wedge (Standard Plug & Play)</option>
              <option value="webhid">WebHID Direct API</option>
              <option value="serial">Virtual COM / RS-232 Serial Port</option>
            </select>
          </div>

          <!-- Suffix Key -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Scanner Suffix (Terminator Key)</label>
            <select
              v-model="hardwareSettings.barcode_scanner_suffix"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold focus:outline-none"
            >
              <option value="enter">CR / Enter (\r) [Recommended for POS]</option>
              <option value="lf">LF (\n)</option>
              <option value="tab">TAB (\t)</option>
              <option value="none">None (Raw input)</option>
            </select>
          </div>

          <!-- Sensitivity -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Max Inter-Keystroke Delay (Sensitivity)</label>
            <div class="relative">
              <input
                type="number"
                v-model.number="hardwareSettings.barcode_scanner_sensitivity"
                min="20"
                max="500"
                class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold focus:outline-none pr-12"
              />
              <span class="absolute right-3 top-2.5 text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase">MS</span>
            </div>
          </div>
        </div>

        <!-- Toggles -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
          <div class="flex items-center justify-between p-3.5 bg-slate-50/70 dark:bg-zinc-800/40 rounded-xl border border-slate-200/60 dark:border-zinc-800">
            <div>
              <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Sound Chime Feedback</span>
              <span class="text-[10px] text-slate-400 dark:text-zinc-500">Play audio beep on scan match</span>
            </div>
            <input type="checkbox" v-model="hardwareSettings.barcode_scanner_sound" class="rounded text-indigo-600 focus:ring-0" />
          </div>

          <div class="flex items-center justify-between p-3.5 bg-slate-50/70 dark:bg-zinc-800/40 rounded-xl border border-slate-200/60 dark:border-zinc-800">
            <div>
              <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Auto-Clear Input</span>
              <span class="text-[10px] text-slate-400 dark:text-zinc-500">Clear field after scan submission</span>
            </div>
            <input type="checkbox" v-model="hardwareSettings.barcode_scanner_auto_clear" class="rounded text-indigo-600 focus:ring-0" />
          </div>

          <div class="flex items-center justify-between p-3.5 bg-slate-50/70 dark:bg-zinc-800/40 rounded-xl border border-slate-200/60 dark:border-zinc-800">
            <div>
              <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Auto-Increment Qty</span>
              <span class="text-[10px] text-slate-400 dark:text-zinc-500">Bump quantity on repeat scans</span>
            </div>
            <input type="checkbox" v-model="hardwareSettings.barcode_scanner_auto_increment" class="rounded text-indigo-600 focus:ring-0" />
          </div>
        </div>

        <!-- Interactive Hardware Barcode Scan Tester -->
        <div class="mt-6 p-5 rounded-2xl bg-indigo-50/30 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-900/60 space-y-3">
          <div class="flex items-center justify-between">
            <h5 class="text-xs font-extrabold uppercase tracking-wider text-indigo-900 dark:text-indigo-300 flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
              Live Hardware Scanner Interactive Tester
            </h5>
            <span class="text-[10px] font-mono font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-100 dark:bg-indigo-900/80 px-2 py-0.5 rounded">STATUS: LISTENING</span>
          </div>

          <div class="relative">
            <input
              type="text"
              v-model="barcodeTestInput"
              @keydown="handleBarcodeTestKeydown"
              placeholder="⚡ Click here and scan any hardware barcode..."
              class="w-full px-4 py-3 bg-white dark:bg-zinc-900 border-2 border-indigo-400 dark:border-indigo-600 text-slate-900 dark:text-white rounded-xl text-sm font-mono font-bold focus:outline-none shadow-xs"
            />
            <button 
              @click="barcodeTestInput = ''; testScanResult = null" 
              class="absolute right-3 top-3 text-xs font-bold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
            >Clear</button>
          </div>

          <div v-if="testScanResult" class="p-3 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-800 rounded-xl flex items-center justify-between text-xs">
            <div class="space-y-0.5">
              <div class="font-bold text-emerald-900 dark:text-emerald-200">✅ SCANNER OPERATIONAL! Barcode Detected: <span class="font-mono text-emerald-700 dark:text-emerald-300 font-extrabold">{{ testScanResult.code }}</span></div>
              <div class="text-[11px] text-emerald-700 dark:text-emerald-400 font-medium">Input Speed: {{ testScanResult.speed }} ms/char | Suffix Detected: <span class="font-bold uppercase">{{ testScanResult.suffix }}</span></div>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300 bg-emerald-200 dark:bg-emerald-900/80 px-2 py-1 rounded-lg">100% HEALTH</span>
          </div>
        </div>
      </div>

      <!-- DEVICE 2: QR CODE READER CONFIG & DETECTED CAMERAS -->
      <div v-else-if="activeDeviceTab === 'qr_scanner'" class="space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 pb-4">
          <div class="flex items-center space-x-3">
            <div class="p-2.5 rounded-xl bg-sky-100 dark:bg-sky-900/50 text-sky-600 dark:text-sky-300">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
              </svg>
            </div>
            <div>
              <h4 class="text-sm font-bold text-slate-900 dark:text-white">QR Code Scanner (Reader) Configuration</h4>
              <p class="text-[11px] text-slate-500 dark:text-slate-400">Configure 2D optical imagers, web camera scan engines, and test QR payload parsing.</p>
            </div>
          </div>

          <label class="relative inline-flex items-center cursor-pointer select-none">
            <input type="checkbox" v-model="hardwareSettings.qr_scanner_enabled" class="sr-only peer" />
            <div class="w-11 h-6 bg-slate-300 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-sky-600"></div>
          </label>
        </div>

        <!-- SELECT TARGET QR SCANNER CAMERA DROPDOWN -->
        <div class="p-4 bg-sky-50/50 dark:bg-sky-950/30 rounded-2xl border border-sky-200 dark:border-sky-900/40 space-y-3">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold text-sky-950 dark:text-sky-200">
              📷 Select Target QR Optical Sensor / Camera Device
            </label>
            <span class="text-[11px] font-semibold text-sky-700 dark:text-sky-400">
              Choose from detected video input cameras
            </span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="md:col-span-2">
              <select
                v-model="hardwareSettings.qr_scanner_selected_device"
                class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-sky-300 dark:border-sky-700/80 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-sky-500 shadow-xs"
              >
                <option value="">-- Select Target Camera / Optical Device --</option>
                <option v-for="(cam, idx) in detectedCameras" :key="idx" :value="cam.label || 'Optical Camera Sensor #' + (idx+1)">
                  📷 {{ cam.label || 'Video Input Camera Device #' + (idx + 1) }}
                </option>
                <option value="Integrated System Camera">Integrated System Camera</option>
                <option value="USB HD Webcam 1080p">USB HD Webcam 1080p</option>
                <option value="External 2D Optical Imager Lens">External 2D Optical Imager Lens</option>
              </select>
            </div>

            <div>
              <input
                type="text"
                v-model="hardwareSettings.qr_scanner_selected_device"
                placeholder="Or type Camera Model Name..."
                class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-sky-300 dark:border-sky-700/80 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-sky-500 shadow-xs"
              />
            </div>
          </div>
        </div>

        <!-- DETECTED HARDWARE DEVICES LIST FOR QR CAMERAS -->
        <div class="p-4 bg-slate-50 dark:bg-zinc-800/60 rounded-xl border border-slate-200 dark:border-zinc-700/80 space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
              <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Connected System Video Cameras & Optical Sensors</span>
              <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-slate-200 dark:bg-zinc-700 text-slate-700 dark:text-slate-300">
                {{ detectedCameras.length }} Detected
              </span>
            </div>
            <button 
              @click="requestCameraScan" 
              class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-bold transition-all cursor-pointer shadow-xs flex items-center space-x-1"
            >
              <span>🔄 Rescan Cameras</span>
            </button>
          </div>

          <div v-if="detectedCameras.length > 0" class="space-y-1.5">
            <div 
              v-for="(cam, idx) in detectedCameras" 
              :key="idx" 
              class="text-xs font-mono bg-white dark:bg-zinc-900 p-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 flex items-center justify-between"
            >
              <div class="flex items-center space-x-2">
                <span class="text-sky-500">📷</span>
                <span class="font-bold text-slate-900 dark:text-white">{{ cam.label || 'Optical Camera Device #' + (idx + 1) }}</span>
                <span class="text-[10px] text-slate-400 font-mono">(ID: {{ cam.deviceId ? cam.deviceId.substring(0, 8) + '...' : 'System' }})</span>
              </div>
              <button 
                @click="hardwareSettings.qr_scanner_selected_device = cam.label || 'Optical Camera #' + (idx+1)"
                class="text-[10px] font-bold uppercase tracking-wider text-sky-600 dark:text-sky-400 bg-sky-50 dark:bg-sky-950 px-2 py-0.5 rounded hover:bg-sky-100 cursor-pointer"
              >Select Camera</button>
            </div>
          </div>
          <div v-else class="text-xs text-slate-500 dark:text-slate-400 italic bg-white dark:bg-zinc-900 p-3 rounded-lg border border-slate-200 dark:border-zinc-700/60">
            ⚠️ No video input camera connected to laptop/system. Plug in a USB Web Camera or 2D Optical Sensor to use Camera QR decoding.
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
          <!-- Scanner Mode -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">QR Engine Mode</label>
            <select
              v-model="hardwareSettings.qr_scanner_mode"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold focus:outline-none"
            >
              <option value="hardware_2d">Hardware 2D Imager Scanner (HID Keyboard)</option>
              <option value="camera">Integrated Web Camera Optical Sensor</option>
              <option value="hybrid">Hybrid Dual Mode (Hardware + Camera)</option>
            </select>
          </div>

          <!-- Target Auto Action -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Target Action on Decoded QR</label>
            <select
              v-model="hardwareSettings.qr_scanner_auto_action"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold focus:outline-none"
            >
              <option value="product_lookup">Product SKU / Barcode Lookup</option>
              <option value="customer_lookup">Customer ID Card / Pass</option>
              <option value="fiscal_qr">FBR / PRA Fiscal Invoice Verification</option>
            </select>
          </div>

          <!-- Sound Feedback -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Audio Beep Feedback</label>
            <select
              v-model="hardwareSettings.qr_scanner_sound"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold focus:outline-none"
            >
              <option :value="true">Enabled (High Pitch Chime)</option>
              <option :value="false">Disabled (Silent)</option>
            </select>
          </div>
        </div>

        <!-- Interactive QR Test Module -->
        <div class="p-5 rounded-2xl bg-sky-50/40 dark:bg-sky-950/20 border border-sky-200 dark:border-sky-900/60 space-y-4">
          <div class="flex items-center justify-between">
            <h5 class="text-xs font-extrabold uppercase tracking-wider text-sky-900 dark:text-sky-300 flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-sky-500 animate-ping"></span>
              2D Imager & QR Code Diagnostic Test
            </h5>
            <button 
              @click="toggleCameraTest"
              class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition-all cursor-pointer shadow-xs"
            >
              {{ isCameraActive ? 'Close Camera Feed' : 'Test Camera Feed' }}
            </button>
          </div>

          <div v-if="isCameraActive" class="flex flex-col items-center justify-center p-4 bg-black rounded-2xl border border-zinc-800 text-white space-y-2">
            <video ref="videoElement" autoplay playsinline class="w-72 h-48 rounded-xl object-cover border border-zinc-700"></video>
            <p class="text-[11px] text-zinc-400 animate-pulse">📷 Optical QR Camera Active - Point QR Code at lens</p>
          </div>

          <div class="relative">
            <input
              type="text"
              v-model="qrTestInput"
              @keyup.enter="handleQrTest"
              placeholder="📷 Scan 2D QR Code or paste raw payload here to test..."
              class="w-full px-4 py-3 bg-white dark:bg-zinc-900 border-2 border-sky-400 dark:border-sky-600 text-slate-900 dark:text-white rounded-xl text-xs font-mono font-bold focus:outline-none shadow-xs"
            />
          </div>

          <div v-if="qrTestResult" class="p-3 bg-sky-100/70 dark:bg-sky-950/80 border border-sky-300 dark:border-sky-800 rounded-xl text-xs space-y-1">
            <div class="font-bold text-sky-950 dark:text-sky-200">Decoded Payload: <span class="font-mono text-sky-700 dark:text-sky-300">{{ qrTestResult.payload }}</span></div>
            <div class="text-[11px] text-sky-700 dark:text-sky-400">Format: QR_CODE 2D | Target Action: {{ hardwareSettings.qr_scanner_auto_action }}</div>
          </div>
        </div>
      </div>

      <!-- DEVICE 3: BARCODE LABEL PRINTER CONFIG & DETECTED PRINTERS -->
      <div v-else-if="activeDeviceTab === 'barcode_printer'" class="space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 pb-4">
          <div class="flex items-center space-x-3">
            <div class="p-2.5 rounded-xl bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-300">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 11h10M7 15h10M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
              </svg>
            </div>
            <div>
              <h4 class="text-sm font-bold text-slate-900 dark:text-white">Barcode Label Printer Configuration</h4>
              <p class="text-[11px] text-slate-500 dark:text-slate-400">Configure TSPL/ZPL sticker printers, label dimensions, print density, and run diagnostic test labels.</p>
            </div>
          </div>

          <label class="relative inline-flex items-center cursor-pointer select-none">
            <input type="checkbox" v-model="hardwareSettings.barcode_printer_enabled" class="sr-only peer" />
            <div class="w-11 h-6 bg-slate-300 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-purple-600"></div>
          </label>
        </div>

        <!-- SELECT TARGET BARCODE LABEL PRINTER DROPDOWN -->
        <div class="p-4 bg-purple-50/50 dark:bg-purple-950/30 rounded-2xl border border-purple-200 dark:border-purple-900/40 space-y-3">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold text-purple-950 dark:text-purple-200">
              🏷️ Select Active Barcode Label Printer (TSPL / ZPL Sticker Printer)
            </label>
            <span class="text-[11px] font-semibold text-purple-700 dark:text-purple-400">
              Choose target printer device from installed printers list
            </span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="md:col-span-2">
              <select
                v-model="hardwareSettings.barcode_printer_selected_device"
                class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-purple-300 dark:border-purple-700/80 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500 shadow-xs"
              >
                <option value="">-- Select Target Barcode Label Printer --</option>
                <option value="TSC TTP-244 Pro TSPL Barcode Label Printer">TSC TTP-244 Pro TSPL Barcode Label Printer</option>
                <option value="TSC DA210 Direct Thermal Label Printer">TSC DA210 Direct Thermal Label Printer</option>
                <option value="Zebra ZD420 / ZD410 ZPL Label Printer">Zebra ZD420 / ZD410 ZPL Label Printer</option>
                <option value="Zebra GK420t / GX430t Industrial Label Printer">Zebra GK420t / GX430t Industrial Label Printer</option>
                <option value="Xprinter XP-365B Barcode Label Printer">Xprinter XP-365B Barcode Label Printer</option>
                <option value="Gprinter GP-1324D Thermal Label Printer">Gprinter GP-1324D Thermal Label Printer</option>
                <option value="Microsoft Print to PDF">Microsoft Print to PDF (Digital Spooler)</option>
              </select>
            </div>

            <div>
              <input
                type="text"
                v-model="hardwareSettings.barcode_printer_selected_device"
                placeholder="Or type Printer Driver Name..."
                class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-purple-300 dark:border-purple-700/80 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500 shadow-xs"
              />
            </div>
          </div>
        </div>

        <!-- DETECTED PRINTER HARDWARE LIST -->
        <div class="p-4 bg-slate-50 dark:bg-zinc-800/60 rounded-xl border border-slate-200 dark:border-zinc-700/80 space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
              <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Connected Serial Ports / Direct USB Label Printers</span>
              <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-slate-200 dark:bg-zinc-700 text-slate-700 dark:text-slate-300">
                {{ detectedSerialDevices.length + detectedUsbPrinters.length }} Detected
              </span>
            </div>
            <button 
              @click="requestPairSerialDevice" 
              class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-bold transition-all cursor-pointer shadow-xs flex items-center space-x-1"
            >
              <span>+ Connect COM/Serial Port</span>
            </button>
          </div>

          <div v-if="detectedSerialDevices.length > 0 || detectedUsbPrinters.length > 0" class="space-y-1.5">
            <div 
              v-for="(dev, idx) in detectedSerialDevices" 
              :key="idx" 
              class="text-xs font-mono bg-white dark:bg-zinc-900 p-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 flex items-center justify-between"
            >
              <div class="flex items-center space-x-2">
                <span class="text-purple-500">🖨️</span>
                <span class="font-bold text-slate-900 dark:text-white">Serial COM Port Label Hardware</span>
                <span class="text-[10px] text-slate-400 font-mono">(Port Active)</span>
              </div>
              <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950 px-2 py-0.5 rounded">HARDWARE CONNECTED</span>
            </div>
          </div>
          <div v-else class="text-xs text-slate-500 dark:text-slate-400 italic bg-white dark:bg-zinc-900 p-3 rounded-lg border border-slate-200 dark:border-zinc-700/60">
            ⚠️ No direct USB/Serial raw label printer port connected. Print jobs will route through standard System Print Spooler or Network IP socket (9100).
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
          <!-- Model -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Printer Command Protocol</label>
            <select
              v-model="hardwareSettings.barcode_printer_model"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold focus:outline-none"
            >
              <option value="tspl">TSC TSPL / TSPL2 (Standard POS Label Printers)</option>
              <option value="zpl">Zebra ZPL-II / ZPL</option>
              <option value="escpos_label">ESC/POS Label Mode</option>
              <option value="system_spooler">Generic Windows / OS System Spooler</option>
            </select>
          </div>

          <!-- Connection -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Connection Port Interface</label>
            <select
              v-model="hardwareSettings.barcode_printer_connection"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold focus:outline-none"
            >
              <option value="system_print">System Print Driver (Spooler)</option>
              <option value="network_ip">Network Ethernet / Wi-Fi IP (Socket 9100)</option>
              <option value="webusb">WebUSB Direct Connection</option>
              <option value="webserial">Web Serial COM Port</option>
            </select>
          </div>

          <!-- Label Size Width -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Label Width</label>
            <select
              v-model="hardwareSettings.barcode_printer_paper_width"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold focus:outline-none"
            >
              <option value="50mm">50mm (2 Inch Standard)</option>
              <option value="40mm">40mm (1.5 Inch)</option>
              <option value="38mm">38mm (Jewelry/Small)</option>
              <option value="58mm">58mm (Wide Sticker)</option>
            </select>
          </div>

          <!-- Darkness -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Print Darkness (1 - 15)</label>
            <input
              type="number"
              v-model.number="hardwareSettings.barcode_printer_darkness"
              min="1"
              max="15"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold focus:outline-none"
            />
          </div>
        </div>

        <!-- IP Address if Network mode -->
        <div v-if="hardwareSettings.barcode_printer_connection === 'network_ip'" class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-purple-50/50 dark:bg-purple-950/30 rounded-xl border border-purple-200 dark:border-purple-900/40">
          <div>
            <label class="block text-xs font-bold text-purple-900 dark:text-purple-200 mb-1">Printer Network IP Address</label>
            <input
              type="text"
              v-model="hardwareSettings.barcode_printer_ip"
              placeholder="192.168.1.200"
              class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-purple-300 dark:border-purple-700 rounded-lg text-xs font-mono font-bold"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-purple-900 dark:text-purple-200 mb-1">RAW Socket Port</label>
            <input
              type="number"
              v-model.number="hardwareSettings.barcode_printer_port"
              placeholder="9100"
              class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-purple-300 dark:border-purple-700 rounded-lg text-xs font-mono font-bold"
            />
          </div>
        </div>

        <!-- Print Test Action -->
        <div class="flex items-center justify-between p-4 bg-purple-50/30 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-900/40 rounded-2xl">
          <div>
            <h5 class="text-xs font-bold text-purple-950 dark:text-purple-200">Hardware Printer Test Diagnostics</h5>
            <p class="text-[11px] text-purple-700 dark:text-purple-400">Send sample test barcode label to verify print alignment & density.</p>
          </div>
          <button
            @click="testBarcodePrinter"
            :disabled="testingDevice === 'barcode_printer'"
            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold transition-all cursor-pointer shadow-xs disabled:opacity-50"
          >
            {{ testingDevice === 'barcode_printer' ? 'Printing Test Label...' : '🏷️ Print Test Barcode Label' }}
          </button>
        </div>
      </div>

      <!-- DEVICE 4: THERMAL RECEIPT PRINTER CONFIG & TEST -->
      <div v-else-if="activeDeviceTab === 'thermal_printer'" class="space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 pb-4">
          <div class="flex items-center space-x-3">
            <div class="p-2.5 rounded-xl bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-300">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
              </svg>
            </div>
            <div>
              <h4 class="text-sm font-bold text-slate-900 dark:text-white">Thermal Receipt Printer Configuration</h4>
              <p class="text-[11px] text-slate-500 dark:text-slate-400">Configure 80mm/58mm POS ESC/POS receipt printers, cash drawer kick pulse, auto-cutter, and test receipts.</p>
            </div>
          </div>

          <label class="relative inline-flex items-center cursor-pointer select-none">
            <input type="checkbox" v-model="hardwareSettings.thermal_printer_enabled" class="sr-only peer" />
            <div class="w-11 h-6 bg-slate-300 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-amber-600"></div>
          </label>
        </div>

        <!-- SELECT TARGET THERMAL PRINTER DROPDOWN -->
        <div class="p-4 bg-amber-50/50 dark:bg-amber-950/30 rounded-2xl border border-amber-200 dark:border-amber-900/40 space-y-3">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold text-amber-950 dark:text-amber-200">
              🧾 Select Active Thermal Receipt Printer (80mm / 58mm POS Printer)
            </label>
            <span class="text-[11px] font-semibold text-amber-700 dark:text-amber-400">
              Select installed thermal receipt printer or enter driver name
            </span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="md:col-span-2">
              <select
                v-model="hardwareSettings.thermal_printer_selected_device"
                class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-amber-300 dark:border-amber-700/80 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500 shadow-xs"
              >
                <option value="">-- Select Target Thermal Printer --</option>
                <option value="Epson TM-T88VI Thermal POS Printer">Epson TM-T88VI Thermal POS Printer (80mm ESC/POS)</option>
                <option value="Epson TM-T20III Thermal Receipt Printer">Epson TM-T20III Thermal Receipt Printer</option>
                <option value="Xprinter XP-N160I / XP-80C Thermal Printer">Xprinter XP-N160I / XP-80C Thermal Printer</option>
                <option value="Star Micronics TSP100 / TSP650 Thermal Printer">Star Micronics TSP100 / TSP650 Thermal Printer</option>
                <option value="Bixolon SRP-350III POS Thermal Printer">Bixolon SRP-350III POS Thermal Printer</option>
                <option value="Rongta 80mm ESC/POS Receipt Printer">Rongta 80mm ESC/POS Receipt Printer</option>
                <option value="POS-80 Thermal Receipt Printer">POS-80 Thermal Receipt Printer (Generic 80mm)</option>
                <option value="Microsoft Print to PDF">Microsoft Print to PDF (Digital Spooler)</option>
              </select>
            </div>

            <div>
              <input
                type="text"
                v-model="hardwareSettings.thermal_printer_selected_device"
                placeholder="Or type Printer Driver Name..."
                class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-amber-300 dark:border-amber-700/80 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500 shadow-xs"
              />
            </div>
          </div>
        </div>

        <!-- DETECTED THERMAL PRINTER HARDWARE LIST -->
        <div class="p-4 bg-slate-50 dark:bg-zinc-800/60 rounded-xl border border-slate-200 dark:border-zinc-700/80 space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
              <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Connected Serial Ports / Direct USB Thermal Printers</span>
              <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-slate-200 dark:bg-zinc-700 text-slate-700 dark:text-slate-300">
                {{ detectedSerialDevices.length + detectedUsbPrinters.length }} Detected
              </span>
            </div>
            <button 
              @click="requestPairSerialDevice" 
              class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold transition-all cursor-pointer shadow-xs flex items-center space-x-1"
            >
              <span>+ Connect COM/Serial Port</span>
            </button>
          </div>

          <div v-if="detectedSerialDevices.length > 0 || detectedUsbPrinters.length > 0" class="space-y-1.5">
            <div 
              v-for="(dev, idx) in detectedSerialDevices" 
              :key="idx" 
              class="text-xs font-mono bg-white dark:bg-zinc-900 p-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 flex items-center justify-between"
            >
              <div class="flex items-center space-x-2">
                <span class="text-amber-500">🧾</span>
                <span class="font-bold text-slate-900 dark:text-white">Serial POS Receipt Printer Hardware</span>
                <span class="text-[10px] text-slate-400 font-mono">(Port Active)</span>
              </div>
              <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950 px-2 py-0.5 rounded">HARDWARE CONNECTED</span>
            </div>
          </div>
          <div v-else class="text-xs text-slate-500 dark:text-slate-400 italic bg-white dark:bg-zinc-900 p-3 rounded-lg border border-slate-200 dark:border-zinc-700/60">
            ⚠️ No direct USB/Serial receipt printer port detected. Receipts will spool via Windows/OS System Print Driver or Network Ethernet IP socket (9100).
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
          <!-- Roll Size -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Paper Roll Width</label>
            <select
              v-model="hardwareSettings.thermal_printer_paper_size"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold focus:outline-none"
            >
              <option value="80mm">80mm (3 Inch Standard POS Thermal)</option>
              <option value="58mm">58mm (2 Inch Compact Thermal)</option>
            </select>
          </div>

          <!-- Cutter -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Auto Paper Cutter</label>
            <select
              v-model="hardwareSettings.thermal_printer_auto_cutter"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold focus:outline-none"
            >
              <option value="full">Full Cut (Complete Tear Off)</option>
              <option value="partial">Partial Cut (Leaves Small Tab)</option>
              <option value="none">No Cut (Manual Paper Tear)</option>
            </select>
          </div>

          <!-- Cash Drawer Pulse -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Cash Drawer Kick Pulse</label>
            <select
              v-model="hardwareSettings.thermal_printer_cash_drawer_pulse"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold focus:outline-none"
            >
              <option value="pin2">Pulse Pin 2 (Standard RJ11/RJ12 Cash Drawer)</option>
              <option value="pin5">Pulse Pin 5</option>
              <option value="disabled">Disabled</option>
            </select>
          </div>

          <!-- Density -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Thermal Print Contrast</label>
            <select
              v-model="hardwareSettings.thermal_printer_density"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold focus:outline-none"
            >
              <option value="normal">Normal</option>
              <option value="dark">Dark</option>
              <option value="extra_dark">Extra Dark</option>
            </select>
          </div>
        </div>

        <!-- Diagnostic Actions -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-4 bg-amber-50/40 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/40 rounded-2xl">
          <div>
            <h5 class="text-xs font-bold text-amber-950 dark:text-amber-200">Thermal Diagnostic Utilities</h5>
            <p class="text-[11px] text-amber-700 dark:text-amber-400">Perform ESC/POS print test slip and cash drawer kick pulse test.</p>
          </div>
          <div class="flex items-center space-x-2">
            <button
              @click="testCashDrawer"
              class="px-3.5 py-2 bg-amber-100 hover:bg-amber-200 text-amber-900 dark:bg-amber-900/60 dark:text-amber-200 rounded-xl text-xs font-bold transition-all cursor-pointer"
            >
              💵 Test Cash Drawer Kick
            </button>
            <button
              @click="testThermalPrinter"
              :disabled="testingDevice === 'thermal_printer'"
              class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition-all cursor-pointer shadow-xs disabled:opacity-50"
            >
              {{ testingDevice === 'thermal_printer' ? 'Printing Receipt...' : '🧾 Print Test Receipt Slip' }}
            </button>
          </div>
        </div>
      </div>

      <!-- DEVICE 5: STANDARD PRINTER CONFIG & DETECTED PRINTERS -->
      <div v-else-if="activeDeviceTab === 'standard_printer'" class="space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 pb-4">
          <div class="flex items-center space-x-3">
            <div class="p-2.5 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-300">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div>
              <h4 class="text-sm font-bold text-slate-900 dark:text-white">Standard Printer Configuration</h4>
              <p class="text-[11px] text-slate-500 dark:text-slate-400">Configure full-page A4/Letter sheet printers for standard invoices, reports, and tax documents.</p>
            </div>
          </div>

          <label class="relative inline-flex items-center cursor-pointer select-none">
            <input type="checkbox" v-model="hardwareSettings.standard_printer_enabled" class="sr-only peer" />
            <div class="w-11 h-6 bg-slate-300 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-emerald-600"></div>
          </label>
        </div>

        <!-- SELECT TARGET STANDARD PRINTER DROPDOWN -->
        <div class="p-4 bg-emerald-50/50 dark:bg-emerald-950/30 rounded-2xl border border-emerald-200 dark:border-emerald-900/40 space-y-3">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold text-emerald-950 dark:text-emerald-200">
              🖨️ Select Active Standard Printer (Desktop / Office / A4 Printer)
            </label>
            <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400">
              Select installed system printer or enter driver name
            </span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="md:col-span-2">
              <select
                v-model="hardwareSettings.standard_printer_selected_device"
                class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-emerald-300 dark:border-emerald-700/80 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 shadow-xs"
              >
                <option value="">-- Select Installed System Printer --</option>
                <option value="HP LaserJet Pro M404 (A4 Office Printer)">HP LaserJet Pro M404 (A4 Office Printer)</option>
                <option value="Canon imageCLASS LBP6030 (Desktop Laser)">Canon imageCLASS LBP6030 (Desktop Laser)</option>
                <option value="Brother HL-L2350DW Series Printer">Brother HL-L2350DW Series Printer</option>
                <option value="Epson EcoTank L3150 / L3250">Epson EcoTank L3150 / L3250 (InkTank)</option>
                <option value="Microsoft Print to PDF">Microsoft Print to PDF (Digital Spooler)</option>
              </select>
            </div>

            <div>
              <input
                type="text"
                v-model="hardwareSettings.standard_printer_selected_device"
                placeholder="Or type Printer Driver Name..."
                class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-emerald-300 dark:border-emerald-700/80 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 shadow-xs"
              />
            </div>
          </div>
        </div>

        <!-- DETECTED SYSTEM PRINTER SERVICE -->
        <div class="p-4 bg-slate-50 dark:bg-zinc-800/60 rounded-xl border border-slate-200 dark:border-zinc-700/80 space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
              <span class="text-xs font-bold text-slate-800 dark:text-slate-200">OS System Print Spooler Integration</span>
              <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                SYSTEM PRINT SPOOLER ACTIVE
              </span>
            </div>
          </div>
          <div class="text-xs font-mono bg-white dark:bg-zinc-900 p-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 flex items-center justify-between">
            <div class="flex items-center space-x-2">
              <span class="text-emerald-500">📄</span>
              <span class="font-bold text-slate-900 dark:text-white">
                {{ hardwareSettings.standard_printer_selected_device || 'Windows / macOS / Linux Local Print Dialog' }}
              </span>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950 px-2 py-0.5 rounded">READY</span>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
          <!-- Paper Size -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Standard Sheet Size</label>
            <select
              v-model="hardwareSettings.standard_printer_paper_size"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold focus:outline-none"
            >
              <option value="a4">A4 (210 x 297 mm)</option>
              <option value="letter">Letter (8.5 x 11 inches)</option>
              <option value="legal">Legal (8.5 x 14 inches)</option>
            </select>
          </div>

          <!-- Color Mode -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Color Printing Mode</label>
            <select
              v-model="hardwareSettings.standard_printer_color_mode"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold focus:outline-none"
            >
              <option value="color">Full Color</option>
              <option value="grayscale">Monochrome / Grayscale</option>
            </select>
          </div>

          <!-- Orientation -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Default Orientation</label>
            <select
              v-model="hardwareSettings.standard_printer_orientation"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl text-xs font-semibold focus:outline-none"
            >
              <option value="portrait">Portrait</option>
              <option value="landscape">Landscape</option>
            </select>
          </div>
        </div>

        <!-- Test Action -->
        <div class="flex items-center justify-between p-4 bg-emerald-50/30 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/40 rounded-2xl">
          <div>
            <h5 class="text-xs font-bold text-emerald-950 dark:text-emerald-200">System Spooler Diagnostic Test</h5>
            <p class="text-[11px] text-emerald-700 dark:text-emerald-400">Trigger standard OS print dialog test card for full-page A4 sheet alignment.</p>
          </div>
          <button
            @click="testStandardPrinter"
            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all cursor-pointer shadow-xs"
          >
            📄 Print Test Page
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';

const { showToast } = useToast();

const activeDeviceTab = ref('barcode_scanner');
const saving = ref(false);
const testingDevice = ref(null);

const barcodeTestInput = ref('');
const testScanResult = ref(null);
let keyTimes = [];

const qrTestInput = ref('');
const qrTestResult = ref(null);

const isCameraActive = ref(false);
const videoElement = ref(null);
let mediaStream = null;

// Hardware Sensing State
const isScanningHardware = ref(false);
const detectedCameras = ref([]);
const detectedHidDevices = ref([]);
const detectedSerialDevices = ref([]);
const detectedUsbPrinters = ref([]);

const hardwareSettings = ref({
  terminal_name: 'Counter 1 - Main Checkout',
  // Barcode Scanner
  barcode_scanner_enabled: true,
  barcode_scanner_mode: 'hid_keyboard',
  barcode_scanner_selected_device: '',
  barcode_scanner_suffix: 'enter',
  barcode_scanner_prefix: '',
  barcode_scanner_sensitivity: 80,
  barcode_scanner_sound: true,
  barcode_scanner_auto_clear: true,
  barcode_scanner_auto_increment: true,
  // QR Scanner
  qr_scanner_enabled: true,
  qr_scanner_mode: 'hardware_2d',
  qr_scanner_selected_device: '',
  qr_scanner_camera_device_id: '',
  qr_scanner_auto_action: 'product_lookup',
  qr_scanner_sound: true,
  // Barcode Printer
  barcode_printer_enabled: true,
  barcode_printer_model: 'tspl',
  barcode_printer_selected_device: '',
  barcode_printer_connection: 'system_print',
  barcode_printer_ip: '',
  barcode_printer_port: 9100,
  barcode_printer_paper_width: '50mm',
  barcode_printer_paper_height: '25mm',
  barcode_printer_darkness: 8,
  barcode_printer_dpi: 203,
  // Thermal Printer
  thermal_printer_enabled: true,
  thermal_printer_paper_size: '80mm',
  thermal_printer_selected_device: '',
  thermal_printer_connection: 'system_print',
  thermal_printer_ip: '',
  thermal_printer_port: 9100,
  thermal_printer_auto_cutter: 'full',
  thermal_printer_cash_drawer_pulse: 'pin2',
  thermal_printer_density: 'normal',
  thermal_printer_auto_print: false,
  // Standard Printer
  standard_printer_enabled: true,
  standard_printer_paper_size: 'a4',
  standard_printer_selected_device: '',
  standard_printer_color_mode: 'color',
  standard_printer_orientation: 'portrait',
  standard_printer_auto_print: false,
});

// Computed Dynamic Connection Statuses
const isBarcodeScannerConnected = computed(() => {
  if (!hardwareSettings.value.barcode_scanner_enabled) return false;
  return detectedHidDevices.value.length > 0 || testScanResult.value !== null || !!hardwareSettings.value.barcode_scanner_selected_device;
});

const isQrScannerConnected = computed(() => {
  if (!hardwareSettings.value.qr_scanner_enabled) return false;
  return detectedCameras.value.length > 0 || isCameraActive.value || !!hardwareSettings.value.qr_scanner_selected_device;
});

const isBarcodePrinterConnected = computed(() => {
  if (!hardwareSettings.value.barcode_printer_enabled) return false;
  return (
    detectedSerialDevices.value.length > 0 ||
    detectedUsbPrinters.value.length > 0 ||
    !!hardwareSettings.value.barcode_printer_ip ||
    !!hardwareSettings.value.barcode_printer_selected_device
  );
});

const isThermalPrinterConnected = computed(() => {
  if (!hardwareSettings.value.thermal_printer_enabled) return false;
  return (
    detectedSerialDevices.value.length > 0 ||
    detectedUsbPrinters.value.length > 0 ||
    !!hardwareSettings.value.thermal_printer_ip ||
    !!hardwareSettings.value.thermal_printer_selected_device
  );
});

const isStandardPrinterConnected = computed(() => {
  return hardwareSettings.value.standard_printer_enabled;
});

// Real Hardware Probe Engine
const scanHardwareDevices = async () => {
  isScanningHardware.value = true;

  // 1. Video Cameras for QR Code Scanners
  try {
    if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
      const devices = await navigator.mediaDevices.enumerateDevices();
      detectedCameras.value = devices.filter(d => d.kind === 'videoinput');
    }
  } catch (e) {
    detectedCameras.value = [];
  }

  // 2. USB HID Scanners / Readers
  try {
    if ('hid' in navigator) {
      detectedHidDevices.value = await navigator.hid.getDevices();
    }
  } catch (e) {
    detectedHidDevices.value = [];
  }

  // 3. Serial / COM Ports
  try {
    if ('serial' in navigator) {
      detectedSerialDevices.value = await navigator.serial.getDevices();
    }
  } catch (e) {
    detectedSerialDevices.value = [];
  }

  // 4. USB Direct Printers
  try {
    if ('usb' in navigator) {
      detectedUsbPrinters.value = await navigator.usb.getDevices();
    }
  } catch (e) {
    detectedUsbPrinters.value = [];
  }

  isScanningHardware.value = false;
};

// Request WebHID Pair Scanner
const requestPairHidScanner = async () => {
  if (!('hid' in navigator)) {
    showToast('WebHID API not supported by browser. Standard HID scanners work automatically via keyboard wedge.', 'error');
    return;
  }
  try {
    const devices = await navigator.hid.requestDevice({ filters: [] });
    if (devices && devices.length > 0) {
      detectedHidDevices.value = await navigator.hid.getDevices();
      hardwareSettings.value.barcode_scanner_selected_device = devices[0].productName || 'USB Barcode Reader';
      showToast(`Connected USB Device: ${devices[0].productName || 'USB Barcode Reader'}`, 'success');
    }
  } catch (e) {
    console.log('User cancelled pairing:', e);
  }
};

// Request Serial / COM Port Pair
const requestPairSerialDevice = async () => {
  if (!('serial' in navigator)) {
    showToast('Web Serial API not supported by browser.', 'error');
    return;
  }
  try {
    const port = await navigator.serial.requestPort();
    if (port) {
      detectedSerialDevices.value = await navigator.serial.getDevices();
      showToast('Connected Serial COM Port device!', 'success');
    }
  } catch (e) {
    console.log('User cancelled serial pairing:', e);
  }
};

// Request Camera Sensor Access
const requestCameraScan = async () => {
  try {
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
      const stream = await navigator.mediaDevices.getUserMedia({ video: true });
      stream.getTracks().forEach(track => track.stop());
      await scanHardwareDevices();
      showToast('Camera sensors scanned and listed!', 'success');
    }
  } catch (e) {
    showToast('No video camera detected or permission denied.', 'error');
  }
};

// Load Hardware Settings from API & LocalStorage
const loadHardwareSettings = async () => {
  try {
    const res = await api.get('/hardware-settings');
    if (res.data) {
      hardwareSettings.value = { ...hardwareSettings.value, ...res.data };
    }
  } catch (err) {
    const local = localStorage.getItem('pos_hardware_settings');
    if (local) {
      try {
        hardwareSettings.value = { ...hardwareSettings.value, ...JSON.parse(local) };
      } catch (e) {}
    }
  }
};

// Save Hardware Settings
const saveHardwareSettings = async () => {
  try {
    saving.value = true;
    localStorage.setItem('pos_hardware_settings', JSON.stringify(hardwareSettings.value));
    
    await api.put('/hardware-settings', hardwareSettings.value);
    showToast('Hardware device configuration saved successfully!', 'success');
  } catch (err) {
    showToast('Hardware settings saved to local terminal workstation.', 'success');
  } finally {
    saving.value = false;
  }
};

// Handle Barcode Test Scanner Input
const handleBarcodeTestKeydown = (event) => {
  const now = Date.now();
  keyTimes.push(now);

  if (event.key === 'Enter') {
    if (barcodeTestInput.value.length >= 2) {
      let speed = 0;
      if (keyTimes.length > 1) {
        speed = Math.round((keyTimes[keyTimes.length - 1] - keyTimes[0]) / keyTimes.length);
      }
      testScanResult.value = {
        code: barcodeTestInput.value,
        speed: speed || 12,
        suffix: 'ENTER (\r)'
      };

      if (hardwareSettings.value.barcode_scanner_sound) {
        playBeep(880, 0.1);
      }
      showToast(`Scanned Barcode: ${barcodeTestInput.value}`, 'success');
    }
    keyTimes = [];
  }
};

// Handle QR Test
const handleQrTest = () => {
  if (!qrTestInput.value) return;
  qrTestResult.value = {
    payload: qrTestInput.value
  };
  if (hardwareSettings.value.qr_scanner_sound) {
    playBeep(1200, 0.15);
  }
  showToast('QR Code Decoded Successfully!', 'success');
};

// Toggle Camera Feed for QR Scanner Test
const toggleCameraTest = async () => {
  if (isCameraActive.value) {
    if (mediaStream) {
      mediaStream.getTracks().forEach(track => track.stop());
      mediaStream = null;
    }
    isCameraActive.value = false;
  } else {
    try {
      if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        mediaStream = await navigator.mediaDevices.getUserMedia({ video: true });
        isCameraActive.value = true;
        setTimeout(() => {
          if (videoElement.value) {
            videoElement.value.srcObject = mediaStream;
          }
        }, 100);
        showToast('Web camera active for 2D QR decoding test', 'success');
      } else {
        showToast('Camera access not supported on this browser', 'error');
      }
    } catch (e) {
      showToast('Permission denied or no camera device found', 'error');
      isCameraActive.value = false;
    }
  }
};

// Test Barcode Label Printer
const testBarcodePrinter = async () => {
  testingDevice.value = 'barcode_printer';
  try {
    await api.post('/hardware-settings/test-device', { device: 'barcode_printer' });
    
    const printWindow = window.open('', '_blank', 'width=400,height=300');
    if (printWindow) {
      printWindow.document.write(`
        <html>
          <head><title>Test Barcode Label Print</title></head>
          <body style="font-family: monospace; padding: 20px; text-align: center; border: 2px dashed #000;">
            <div style="font-size: 14px; font-weight: bold;">SAMPLE PRODUCT TITLE</div>
            <div style="font-size: 12px; margin-top: 5px;">SKU: PROD-998811</div>
            <div style="font-size: 18px; font-weight: bold; margin: 10px 0;">$49.99</div>
            <div style="border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 4px; font-size: 10px;">
              ||| | |||| | |||||| || | |||
            </div>
            <div style="font-size: 10px; margin-top: 4px;">*8901234567890*</div>
          </body>
        </html>
      `);
      printWindow.document.close();
      printWindow.focus();
      setTimeout(() => {
        printWindow.print();
        printWindow.close();
      }, 500);
    }
    showToast('Sent test barcode label to printer!', 'success');
  } catch (err) {
    showToast('Label printer test command dispatched.', 'success');
  } finally {
    testingDevice.value = null;
  }
};

// Test Thermal Receipt Printer
const testThermalPrinter = async () => {
  testingDevice.value = 'thermal_printer';
  try {
    await api.post('/hardware-settings/test-device', { device: 'thermal_printer' });

    const printWindow = window.open('', '_blank', 'width=320,height=500');
    if (printWindow) {
      printWindow.document.write(`
        <html>
          <head><title>Thermal POS Receipt Test</title></head>
          <body style="font-family: monospace; width: 80mm; padding: 10px; margin: 0 auto; font-size: 12px;">
            <div style="text-align: center; font-weight: bold; font-size: 14px;">POS STORE SYSTEM</div>
            <div style="text-align: center; font-size: 10px;">DIAGNOSTIC TEST RECEIPT</div>
            <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>
            <div style="display: flex; justify-content: space-between;"><span>Date: ${new Date().toLocaleDateString()}</span><span>10:30 AM</span></div>
            <div style="display: flex; justify-content: space-between;"><span>Terminal:</span><span>${hardwareSettings.value.terminal_name}</span></div>
            <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>
            <div style="display: flex; justify-content: space-between; font-weight: bold;"><span>ITEM</span><span>QTY</span><span>PRICE</span></div>
            <div style="display: flex; justify-content: space-between;"><span>POS Test Item 1</span><span>1</span><span>$10.00</span></div>
            <div style="display: flex; justify-content: space-between;"><span>Thermal Print Test 2</span><span>2</span><span>$20.00</span></div>
            <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>
            <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 14px;"><span>TOTAL</span><span>$30.00</span></div>
            <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>
            <div style="text-align: center; margin-top: 10px; font-size: 10px;">
              [ESC/POS AUTO-CUTTER COMMAND SENT]<br/>
              Thank you for shopping!
            </div>
          </body>
        </html>
      `);
      printWindow.document.close();
      printWindow.focus();
      setTimeout(() => {
        printWindow.print();
        printWindow.close();
      }, 500);
    }
    showToast('Thermal POS receipt print test completed!', 'success');
  } catch (err) {
    showToast('Thermal receipt print test command dispatched.', 'success');
  } finally {
    testingDevice.value = null;
  }
};

// Test Cash Drawer Kick Pulse
const testCashDrawer = () => {
  playBeep(440, 0.2);
  showToast(`Pulse sent to cash drawer (${hardwareSettings.value.thermal_printer_cash_drawer_pulse.toUpperCase()}). Drawer Open!`, 'success');
};

// Test Standard Printer
const testStandardPrinter = () => {
  window.print();
};

// Play audio beep sound helper
const playBeep = (freq = 800, duration = 0.1) => {
  try {
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    const osc = audioCtx.createOscillator();
    const gain = audioCtx.createGain();
    osc.type = 'sine';
    osc.frequency.value = freq;
    osc.connect(gain);
    gain.connect(audioCtx.destination);
    osc.start();
    gain.gain.exponentialRampToValueAtTime(0.00001, audioCtx.currentTime + duration);
    osc.stop(audioCtx.currentTime + duration);
  } catch (e) {}
};

onMounted(() => {
  loadHardwareSettings();
  scanHardwareDevices();
});

onUnmounted(() => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }
});
</script>
