<template>
  <div :class="['print-root min-h-screen bg-slate-100 dark:bg-zinc-950 py-6 px-4 print:bg-white print:py-0 print:px-0', isThermalMode ? 'thermal-mode' : 'standard-mode']">
    
    <!-- Top Action & Template Selector Bar (Hidden when printing) -->
    <div class="w-full mb-6 bg-white dark:bg-zinc-900 rounded-2xl p-4 border border-slate-200 dark:border-zinc-800 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4 no-print">
      
      <!-- Back & Info -->
      <div class="flex items-center space-x-3">
        <button 
          @click="goBack" 
          class="p-2 rounded-xl text-slate-500 hover:text-slate-900 dark:text-zinc-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer"
          title="Back to Invoices"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
        </button>
        <div>
          <h2 class="text-sm font-bold text-slate-900 dark:text-white">Print Sales Invoice</h2>
          <p class="text-[11px] text-slate-500 dark:text-zinc-400">
            Active Layout: <strong class="capitalize text-slate-800 dark:text-zinc-200">{{ activeTemplateName }}</strong> 
            ({{ isThermalMode ? 'Thermal 80mm' : 'Standard A4/Letter' }})
          </p>
        </div>
      </div>

      <!-- Quick Template Selector Dropdown / Mode Switcher -->
      <div class="flex items-center space-x-3">
        <!-- Print Mode Toggle (Standard / Thermal) -->
        <div class="flex bg-slate-100 dark:bg-zinc-800 p-1 rounded-xl">
          <button
            @click="isThermalMode = false"
            :class="[
              'px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer',
              !isThermalMode ? 'bg-white dark:bg-zinc-900 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 dark:text-zinc-400'
            ]"
          >
            A4 / Standard
          </button>
          <button
            @click="isThermalMode = true"
            :class="[
              'px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer',
              isThermalMode ? 'bg-white dark:bg-zinc-900 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 dark:text-zinc-400'
            ]"
          >
            Thermal 80mm
          </button>
        </div>

        <!-- Template Selector Switcher -->
        <select
          v-if="!isThermalMode"
          v-model="selectedStandardTemplate"
          class="px-3 py-1.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-zinc-200 focus:outline-none cursor-pointer"
        >
          <option value="default">Default Template</option>
          <option value="classic">Classic Template</option>
          <option value="modern">Modern Template</option>
        </select>

        <select
          v-else
          v-model="selectedThermalTemplate"
          class="px-3 py-1.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-zinc-200 focus:outline-none cursor-pointer"
        >
          <option value="classic">Classic Thermal</option>
          <option value="modern">Modern Thermal</option>
        </select>

        <!-- Download PDF Button -->
        <button
          @click="downloadPdf"
          :disabled="isGeneratingPdf"
          class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-xs transition-all flex items-center space-x-1.5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
          title="Download PDF"
        >
          <svg v-if="!isGeneratingPdf" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ isGeneratingPdf ? 'Generating...' : 'Download PDF' }}</span>
        </button>

        <!-- Print Action Button -->
        <button
          @click="triggerPrint"
          class="px-4 py-2 bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-bold rounded-xl text-xs shadow-xs transition-all flex items-center space-x-1.5 cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
          </svg>
          <span>Print Now</span>
        </button>
      </div>

    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col justify-center items-center h-64 space-y-3 no-print">
      <div class="animate-spin rounded-full h-10 w-10 border-4 border-slate-900 dark:border-white border-t-transparent"></div>
      <p class="text-xs font-semibold text-slate-500 dark:text-zinc-400">Preparing invoice print view...</p>
    </div>

    <!-- Printable Invoice Main Container -->
    <div v-else-if="invoice" class="invoice-wrapper flex justify-center">

      <!-- ========================================================================= -->
      <!-- A4 / STANDARD PRINT TEMPLATES (3 Options: Default, Classic, Modern)       -->
      <!-- ========================================================================= -->
      <template v-if="!isThermalMode">

        <!-- 1. STANDARD DEFAULT TEMPLATE (Matching Image 1 Left) -->
        <div v-if="selectedStandardTemplate === 'default'" class="standard-sheet template-default bg-white text-slate-900 p-8 shadow-lg print:shadow-none w-full max-w-[8.5in] font-sans">
          <!-- Top Header -->
          <div class="flex justify-between items-start pb-4">
            <div>
              <h1 class="text-2xl font-black text-slate-800 tracking-tight">{{ activeTitle }}</h1>
              <p v-if="activeSubheading" class="text-xs text-slate-500 mt-0.5">{{ activeSubheading }}</p>
              
              <!-- Company Logo -->
              <div class="mt-3">
                <img 
                  v-if="companyLogoUrl" 
                  :src="companyLogoUrl" 
                  crossorigin="anonymous"
                  alt="Logo" 
                  :style="{ width: (settings.logo_width || 128) + 'px', height: (settings.logo_height || 128) + 'px', objectFit: 'contain' }"
                />
                <div 
                  v-else 
                  class="bg-slate-200 rounded-full flex items-center justify-center font-bold text-slate-600"
                  :style="{ width: Math.min(settings.logo_width || 80, 80) + 'px', height: Math.min(settings.logo_height || 80, 80) + 'px' }"
                >
                  LOGO
                </div>
              </div>
            </div>

            <!-- Company Contact Info Right -->
            <div class="text-right text-xs text-slate-600 space-y-0.5">
              <h2 class="font-bold text-slate-900 text-sm">{{ companyInfo.company_name || companyInfo.name || 'Company Name' }}</h2>
              <p v-if="companyInfo.tax_number">Tax Number: {{ companyInfo.tax_number }}</p>
              <p v-if="companyInfo.company_phone || companyInfo.phone">{{ companyInfo.company_phone || companyInfo.phone }}</p>
              <p v-if="companyInfo.company_email || companyInfo.email">{{ companyInfo.company_email || companyInfo.email }}</p>
              <p v-if="companyInfo.business_address || companyInfo.address">{{ companyInfo.business_address || companyInfo.address }}</p>
            </div>
          </div>

          <div class="border-b border-slate-300 my-4"></div>

          <!-- Customer & Order Meta Grid -->
          <div class="flex justify-between items-start text-xs text-slate-700 mb-6">
            <div>
              <h3 class="font-bold text-slate-900 text-sm mb-1">Bill To ({{ invoice.customer?.name || invoice.customer_name || 'Customer Name' }})</h3>
              <p v-if="invoice.customer?.address || invoice.customer_address">{{ invoice.customer?.address || invoice.customer_address }}</p>
              <p v-if="invoice.customer?.tax_number">Tax Number: {{ invoice.customer.tax_number }}</p>
              <p v-if="invoice.customer_email || invoice.customer?.email">{{ invoice.customer_email || invoice.customer?.email }}</p>
            </div>
            <div class="text-right space-y-1">
              <p v-if="invoice.order_number"><span class="text-slate-500">Order Number:</span> <strong>{{ invoice.order_number }}</strong></p>
              <p><span class="text-slate-500">Invoice Number:</span> <strong>{{ invoice.sale_number }}</strong></p>
              <p><span class="text-slate-500">Invoice Date:</span> <strong>{{ formatDate(invoice.sale_date) }}</strong></p>
              <p v-if="invoice.due_date"><span class="text-slate-500">Due Date:</span> <strong>{{ formatDate(invoice.due_date) }}</strong></p>
            </div>
          </div>

          <!-- Items Table -->
          <div class="mb-6 border border-slate-200 rounded-lg overflow-hidden">
            <table class="w-full text-xs text-left">
              <thead>
                <tr class="text-white uppercase text-[11px] font-bold tracking-wider transition-colors duration-200" :style="{ backgroundColor: getHeaderColorHex(settings.template_color) }">
                  <th class="py-2.5 px-4">{{ activeItemCol }}</th>
                  <th class="py-2.5 px-4 text-center">{{ activeQtyCol }}</th>
                  <th class="py-2.5 px-4 text-right">{{ activePriceCol }}</th>
                  <th v-if="!isAmountHidden" class="py-2.5 px-4 text-right">Amount</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="(item, idx) in invoice.sale_items" :key="item.id || idx" class="hover:bg-slate-50">
                  <td class="py-3 px-4">
                    <div class="font-bold text-slate-900">{{ item.product?.name || item.name || 'Item name' }}</div>
                    <div v-if="!isItemDescHidden && (item.product?.description || item.description)" class="text-[11px] text-slate-500">
                      {{ item.product?.description || item.description }}
                    </div>
                  </td>
                  <td class="py-3 px-4 text-center font-semibold text-slate-800">{{ item.quantity }}</td>
                  <td class="py-3 px-4 text-right text-slate-700 font-medium">{{ formatCurrency(item.unit_price) }}</td>
                  <td v-if="!isAmountHidden" class="py-3 px-4 text-right font-bold text-slate-900">{{ formatCurrency(item.total_price || item.total_amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Summary & Totals -->
          <div class="flex justify-end mb-8">
            <div class="w-72 space-y-2 text-xs">
              <div class="flex justify-between py-1 border-b border-slate-200 text-slate-600">
                <span>Subtotal:</span>
                <span class="font-bold text-slate-900">{{ formatCurrency(invoice.subtotal) }}</span>
              </div>
              <div v-if="parseFloat(invoice.tax_amount) > 0" class="flex justify-between py-1 border-b border-slate-200 text-slate-600">
                <span>Tax:</span>
                <span class="font-bold text-slate-900">+{{ formatCurrency(invoice.tax_amount) }}</span>
              </div>
              <div v-if="parseFloat(invoice.paid_amount) > 0" class="flex justify-between py-1 border-b border-slate-200 text-slate-600">
                <span>Paid:</span>
                <span class="font-bold text-emerald-700">-{{ formatCurrency(invoice.paid_amount) }}</span>
              </div>
              <div class="flex justify-between py-2 border-t-2 border-slate-900 font-black text-sm text-slate-900">
                <span>Total:</span>
                <span>{{ formatCurrency(invoice.total_amount) }}</span>
              </div>
            </div>
          </div>

          <!-- Notes & Footer -->
          <div class="border-t border-slate-200 pt-4 text-xs space-y-2 text-slate-600">
            <div v-if="invoice.notes || activeNotes"><strong class="text-slate-800">Notes:</strong> {{ invoice.notes || activeNotes }}</div>
            <div v-if="invoice.footer || activeFooter || settings.default_terms_conditions"><strong class="text-slate-800">Footer:</strong> {{ invoice.footer || activeFooter || settings.default_terms_conditions }}</div>
          </div>
        </div>

        <!-- 2. STANDARD CLASSIC TEMPLATE (Matching Image 1 Middle) -->
        <div v-else-if="selectedStandardTemplate === 'classic'" class="standard-sheet template-classic bg-white text-slate-900 p-8 shadow-lg print:shadow-none w-full max-w-[8.5in] font-sans">
          <!-- Top Header -->
          <div class="flex justify-between items-start pb-2">
            <div class="flex items-center space-x-3">
              <img 
                v-if="companyLogoUrl" 
                :src="companyLogoUrl" 
                crossorigin="anonymous"
                alt="Logo" 
                :style="{ width: (settings.logo_width || 128) + 'px', height: (settings.logo_height || 128) + 'px', objectFit: 'contain' }"
              />
              <div 
                v-else 
                class="bg-slate-200 rounded-full flex items-center justify-center font-bold text-slate-600"
                :style="{ width: Math.min(settings.logo_width || 60, 60) + 'px', height: Math.min(settings.logo_height || 60, 60) + 'px' }"
              >
                LOGO
              </div>
              <div>
                <h1 class="text-xl font-bold text-slate-800 tracking-tight">{{ activeTitle }}</h1>
                <p v-if="activeSubheading" class="text-xs text-slate-500">{{ activeSubheading }}</p>
              </div>
            </div>

            <div class="text-right text-xs text-slate-600 space-y-0.5">
              <h2 class="font-bold text-slate-900 text-sm">{{ companyInfo.company_name || companyInfo.name || 'Company Name' }}</h2>
              <p v-if="companyInfo.tax_number">Tax Number: {{ companyInfo.tax_number }}</p>
              <p v-if="companyInfo.company_phone || companyInfo.phone">{{ companyInfo.company_phone || companyInfo.phone }}</p>
              <p v-if="companyInfo.company_email || companyInfo.email">{{ companyInfo.company_email || companyInfo.email }}</p>
              <p v-if="companyInfo.business_address || companyInfo.address">{{ companyInfo.business_address || companyInfo.address }}</p>
            </div>
          </div>

          <!-- Centered Boxed Badge -->
          <div class="relative my-4 flex items-center justify-center">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-300"></div></div>
            <div class="relative bg-white px-4 py-1.5 border border-slate-300 rounded-lg text-center shadow-xs">
              <div class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Invoice Number</div>
              <div class="font-extrabold text-slate-900 text-sm">{{ invoice.sale_number }}</div>
            </div>
          </div>

          <!-- Customer Info & Metadata -->
          <div class="flex justify-between items-start text-xs text-slate-700 mb-6">
            <div>
              <h3 class="font-bold text-slate-900 text-sm mb-1">Bill To ({{ invoice.customer?.name || invoice.customer_name || 'Customer Name' }})</h3>
              <p v-if="invoice.customer?.address || invoice.customer_address">{{ invoice.customer?.address || invoice.customer_address }}</p>
              <p v-if="invoice.customer?.tax_number">Tax Number: {{ invoice.customer.tax_number }}</p>
              <p v-if="invoice.customer_email || invoice.customer?.email">{{ invoice.customer_email || invoice.customer?.email }}</p>
            </div>
            <div class="text-right space-y-1">
              <p v-if="invoice.order_number"><span class="text-slate-500">Order Number:</span> <strong>{{ invoice.order_number }}</strong></p>
              <p><span class="text-slate-500">Invoice Date:</span> <strong>{{ formatDate(invoice.sale_date) }}</strong></p>
              <p v-if="invoice.due_date"><span class="text-slate-500">Due Date:</span> <strong>{{ formatDate(invoice.due_date) }}</strong></p>
              <p><span class="text-slate-500">Total:</span> <strong>{{ formatCurrency(invoice.total_amount) }}</strong></p>
            </div>
          </div>

          <!-- Table with Dashed Lines -->
          <div class="mb-6">
            <table class="w-full text-xs text-left border-collapse">
              <thead>
                <tr class="border-b-2 border-dashed uppercase text-[11px] font-bold text-slate-800 transition-colors duration-200" :style="{ borderColor: getHeaderColorHex(settings.template_color) }">
                  <th class="py-2 px-3">{{ activeItemCol }}</th>
                  <th class="py-2 px-3 text-center">{{ activeQtyCol }}</th>
                  <th class="py-2 px-3 text-right">{{ activePriceCol }}</th>
                  <th v-if="!isAmountHidden" class="py-2 px-3 text-right">Amount</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-dashed divide-slate-200">
                <tr v-for="(item, idx) in invoice.sale_items" :key="item.id || idx">
                  <td class="py-3 px-3">
                    <div class="font-bold text-slate-900">{{ item.product?.name || item.name || 'Item name' }}</div>
                    <div v-if="!isItemDescHidden && (item.product?.description || item.description)" class="text-[11px] text-slate-500">
                      {{ item.product?.description || item.description }}
                    </div>
                  </td>
                  <td class="py-3 px-3 text-center font-semibold">{{ item.quantity }}</td>
                  <td class="py-3 px-3 text-right font-medium">{{ formatCurrency(item.unit_price) }}</td>
                  <td v-if="!isAmountHidden" class="py-3 px-3 text-right font-bold text-slate-900">{{ formatCurrency(item.total_price || item.total_amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Summary & Totals -->
          <div class="flex justify-end mb-8">
            <div class="w-72 space-y-2 text-xs">
              <div class="flex justify-between py-1 border-b border-dashed border-slate-300 text-slate-600">
                <span>Subtotal:</span>
                <span class="font-bold text-slate-900">{{ formatCurrency(invoice.subtotal) }}</span>
              </div>
              <div v-if="parseFloat(invoice.tax_amount) > 0" class="flex justify-between py-1 border-b border-dashed border-slate-300 text-slate-600">
                <span>Tax:</span>
                <span class="font-bold text-slate-900">+{{ formatCurrency(invoice.tax_amount) }}</span>
              </div>
              <div v-if="parseFloat(invoice.paid_amount) > 0" class="flex justify-between py-1 border-b border-dashed border-slate-300 text-slate-600">
                <span>Paid:</span>
                <span class="font-bold text-emerald-700">-{{ formatCurrency(invoice.paid_amount) }}</span>
              </div>
              <div class="flex justify-between py-2 border-t-2 border-dashed border-slate-800 font-black text-sm text-slate-900">
                <span>Total:</span>
                <span>{{ formatCurrency(invoice.total_amount) }}</span>
              </div>
            </div>
          </div>

          <!-- Notes & Footer -->
          <div class="border-t border-slate-200 pt-4 text-xs space-y-2 text-slate-600">
            <div v-if="invoice.notes || activeNotes"><strong class="text-slate-800">Notes:</strong> {{ invoice.notes || activeNotes }}</div>
            <div v-if="invoice.footer || activeFooter || settings.default_terms_conditions"><strong class="text-slate-800">Footer:</strong> {{ invoice.footer || activeFooter || settings.default_terms_conditions }}</div>
          </div>
        </div>

        <!-- 3. STANDARD MODERN TEMPLATE (Matching Image 1 Right) -->
        <div v-else-if="selectedStandardTemplate === 'modern'" class="standard-sheet template-modern bg-white text-slate-900 shadow-lg print:shadow-none w-full max-w-[8.5in] font-sans rounded-2xl overflow-hidden">
          <!-- Full Header Banner -->
          <div 
            class="text-white p-8 flex justify-between items-start transition-colors"
            :style="{ backgroundColor: getHeaderColorHex(settings.template_color) }"
          >
            <div class="flex items-center space-x-4">
              <img 
                v-if="companyLogoUrl" 
                :src="companyLogoUrl" 
                crossorigin="anonymous"
                alt="Logo" 
                :style="{ width: (settings.logo_width || 128) + 'px', height: (settings.logo_height || 128) + 'px', objectFit: 'contain' }"
                class="bg-white/10 rounded-lg p-1"
              />
              <div 
                v-else 
                class="bg-white/20 rounded-full flex items-center justify-center font-bold text-white"
                :style="{ width: Math.min(settings.logo_width || 64, 64) + 'px', height: Math.min(settings.logo_height || 64, 64) + 'px' }"
              >
                LOGO
              </div>
              <div>
                <h1 class="text-2xl font-black tracking-tight text-white">{{ companyInfo.company_name || companyInfo.name || 'Company Name' }}</h1>
                <p v-if="activeSubheading" class="text-xs text-slate-200 mt-0.5">{{ activeSubheading }}</p>
                <p v-else-if="companyInfo.business_address || companyInfo.address" class="text-xs text-slate-200 mt-0.5">{{ companyInfo.business_address || companyInfo.address }}</p>
              </div>
            </div>

            <div class="text-right text-xs text-slate-200 space-y-0.5">
              <p v-if="companyInfo.tax_number">Tax #: {{ companyInfo.tax_number }}</p>
              <p v-if="companyInfo.company_phone || companyInfo.phone">{{ companyInfo.company_phone || companyInfo.phone }}</p>
              <p v-if="companyInfo.company_email || companyInfo.email">{{ companyInfo.company_email || companyInfo.email }}</p>
            </div>
          </div>

          <div class="p-8 space-y-6">
            <!-- Customer Info & Metadata Grid -->
            <div class="flex justify-between items-start text-xs text-slate-700">
              <div>
                <h3 class="font-bold text-slate-900 text-sm mb-1">Bill To ({{ invoice.customer?.name || invoice.customer_name || 'Customer Name' }})</h3>
                <p v-if="invoice.customer?.address || invoice.customer_address">{{ invoice.customer?.address || invoice.customer_address }}</p>
                <p v-if="invoice.customer?.tax_number">Tax #: {{ invoice.customer.tax_number }}</p>
                <p v-if="invoice.customer_email || invoice.customer?.email">{{ invoice.customer_email || invoice.customer?.email }}</p>
              </div>
              <div class="text-right space-y-1">
                <p v-if="invoice.order_number"><span class="text-slate-500">Order Number:</span> <strong>{{ invoice.order_number }}</strong></p>
                <p><span class="text-slate-500">Invoice Number:</span> <strong>{{ invoice.sale_number }}</strong></p>
                <p><span class="text-slate-500">Invoice Date:</span> <strong>{{ formatDate(invoice.sale_date) }}</strong></p>
                <p v-if="invoice.due_date"><span class="text-slate-500">Due Date:</span> <strong>{{ formatDate(invoice.due_date) }}</strong></p>
              </div>
            </div>

            <!-- Items Table (Themed Header) -->
            <div class="border border-slate-200 rounded-xl overflow-hidden">
              <table class="w-full text-xs text-left">
                <thead 
                  class="text-white uppercase text-[11px] font-bold tracking-wider transition-colors"
                  :style="{ backgroundColor: getHeaderColorHex(settings.template_color) }"
                >
                  <tr>
                    <th class="py-3 px-4">{{ activeItemCol }}</th>
                    <th class="py-3 px-4 text-center">{{ activeQtyCol }}</th>
                    <th class="py-3 px-4 text-right">{{ activePriceCol }}</th>
                    <th v-if="!isAmountHidden" class="py-3 px-4 text-right">Amount</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="(item, idx) in invoice.sale_items" :key="item.id || idx" class="hover:bg-slate-50">
                    <td class="py-3 px-4">
                      <div class="font-bold text-slate-900">{{ item.product?.name || item.name || 'Item name' }}</div>
                      <div v-if="!isItemDescHidden && (item.product?.description || item.description)" class="text-[11px] text-slate-500">
                        {{ item.product?.description || item.description }}
                      </div>
                    </td>
                    <td class="py-3 px-4 text-center font-semibold text-slate-800">{{ item.quantity }}</td>
                    <td class="py-3 px-4 text-right text-slate-700 font-medium">{{ formatCurrency(item.unit_price) }}</td>
                    <td v-if="!isAmountHidden" class="py-3 px-4 text-right font-bold text-slate-900">{{ formatCurrency(item.total_price || item.total_amount) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Summary & Totals -->
            <div class="flex justify-end">
              <div class="w-72 space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-200 text-slate-600">
                  <span>Subtotal:</span>
                  <span class="font-bold text-slate-900">{{ formatCurrency(invoice.subtotal) }}</span>
                </div>
                <div v-if="parseFloat(invoice.tax_amount) > 0" class="flex justify-between py-1 border-b border-slate-200 text-slate-600">
                  <span>Tax:</span>
                  <span class="font-bold text-slate-900">+{{ formatCurrency(invoice.tax_amount) }}</span>
                </div>
                <div v-if="parseFloat(invoice.paid_amount) > 0" class="flex justify-between py-1 border-b border-slate-200 text-slate-600">
                  <span>Paid:</span>
                  <span class="font-bold text-emerald-700">-{{ formatCurrency(invoice.paid_amount) }}</span>
                </div>
                <div class="flex justify-between py-2 border-t-2 border-slate-900 font-black text-sm text-slate-900">
                  <span>Total:</span>
                  <span>{{ formatCurrency(invoice.total_amount) }}</span>
                </div>
              </div>
            </div>

            <!-- Notes & Footer -->
            <div class="border-t border-slate-200 pt-4 text-xs space-y-2 text-slate-600">
              <div v-if="invoice.notes || activeNotes"><strong class="text-slate-800">Notes:</strong> {{ invoice.notes || activeNotes }}</div>
              <div v-if="invoice.footer || activeFooter || settings.default_terms_conditions"><strong class="text-slate-800">Footer:</strong> {{ invoice.footer || activeFooter || settings.default_terms_conditions }}</div>
            </div>
          </div>
        </div>

      </template>

      <!-- ========================================================================= -->
      <!-- THERMAL RECEIPT PRINT TEMPLATES (2 Options: Classic, Modern)              -->
      <!-- ========================================================================= -->
      <template v-else>

        <!-- 1. CLASSIC THERMAL RECEIPT TEMPLATE (80mm) -->
        <div v-if="selectedThermalTemplate === 'classic'" class="thermal-sheet template-classic-thermal bg-white text-slate-900 p-4 shadow-lg print:shadow-none w-[80mm] font-mono text-xs">
          <!-- Header -->
          <div class="text-center space-y-1 pb-2 border-b border-dashed border-slate-400 mb-3">
            <img v-if="companyLogoUrl" :src="companyLogoUrl" crossorigin="anonymous" alt="Logo" class="mx-auto mb-1 object-contain" :style="{ width: (settings.thermal_logo_width || 64) + 'px', height: (settings.thermal_logo_height || 64) + 'px' }" />
            <h1 class="text-base font-extrabold uppercase tracking-tight text-slate-900">{{ companyInfo.company_name || companyInfo.name || 'STORE NAME' }}</h1>
            <p v-if="activeSubheading" class="text-[10px] text-slate-500 leading-tight">{{ activeSubheading }}</p>
            <p v-if="companyInfo.business_address || companyInfo.address" class="text-[10px] text-slate-600 leading-tight">{{ companyInfo.business_address || companyInfo.address }}</p>
            <p v-if="companyInfo.company_phone || companyInfo.phone" class="text-[10px] text-slate-600">TEL: {{ companyInfo.company_phone || companyInfo.phone }}</p>
          </div>

          <!-- Metadata -->
          <div class="text-[11px] space-y-1 mb-3">
            <div class="flex justify-between">
              <span>RCPT #:</span>
              <strong>{{ invoice.sale_number }}</strong>
            </div>
            <div class="flex justify-between">
              <span>DATE:</span>
              <span>{{ formatDate(invoice.sale_date) }}</span>
            </div>
            <div class="flex justify-between">
              <span>CUST:</span>
              <span class="font-bold">{{ invoice.customer?.name || invoice.customer_name || 'Walk-in' }}</span>
            </div>
          </div>

          <div class="border-b border-dashed border-slate-400 my-2"></div>

          <!-- Items Table -->
          <div class="space-y-2 text-[11px] mb-3">
            <div v-for="(item, idx) in invoice.sale_items" :key="item.id || idx" class="space-y-0.5">
              <div class="font-bold text-slate-900">{{ item.product?.name || item.name || 'Item' }}</div>
              <div v-if="!isItemDescHidden && (item.product?.description || item.description)" class="text-[9px] text-slate-500">
                {{ item.product?.description || item.description }}
              </div>
              <div class="flex justify-between text-slate-600 text-[10px]">
                <span>{{ item.quantity }}x @ {{ formatCurrency(item.unit_price) }}</span>
                <span v-if="!isAmountHidden" class="font-extrabold text-slate-900">{{ formatCurrency(item.total_price || item.total_amount) }}</span>
              </div>
            </div>
          </div>

          <div class="border-b border-dashed border-slate-400 my-2"></div>

          <!-- Totals -->
          <div class="space-y-1 text-[11px] mb-4">
            <div class="flex justify-between text-slate-600">
              <span>SUBTOTAL:</span>
              <span>{{ formatCurrency(invoice.subtotal) }}</span>
            </div>
            <div v-if="parseFloat(invoice.tax_amount) > 0" class="flex justify-between text-slate-600">
              <span>TAX:</span>
              <span>+{{ formatCurrency(invoice.tax_amount) }}</span>
            </div>
            <div class="flex justify-between font-black text-sm pt-1 border-t border-slate-900">
              <span>TOTAL:</span>
              <span>{{ formatCurrency(invoice.total_amount) }}</span>
            </div>
          </div>

          <!-- Footer -->
          <div class="text-center text-[10px] space-y-1 text-slate-700">
            <p v-if="activeNotes" class="italic">{{ activeNotes }}</p>
            <p>{{ invoice.footer || activeFooter || settings.default_terms_conditions }}</p>
            <p class="font-bold">THANK YOU FOR YOUR VISIT!</p>
          </div>
        </div>

        <!-- 2. MODERN THERMAL RECEIPT TEMPLATE (80mm) -->
        <div v-else-if="selectedThermalTemplate === 'modern'" class="thermal-sheet template-modern-thermal bg-white text-slate-900 p-4 shadow-lg print:shadow-none w-[80mm] font-sans text-xs rounded-xl">
          <!-- Header -->
          <div class="text-center space-y-1 pb-2 border-b-2 border-slate-900 mb-3">
            <img v-if="companyLogoUrl" :src="companyLogoUrl" crossorigin="anonymous" alt="Logo" class="mx-auto mb-1 object-contain" :style="{ width: (settings.thermal_logo_width || 64) + 'px', height: (settings.thermal_logo_height || 64) + 'px' }" />
            <div v-else class="inline-block p-1.5 bg-slate-900 text-white font-black text-xs rounded-lg mb-1">
              {{ ((companyInfo.company_name || companyInfo.name || 'POS')[0]) }}
            </div>
            <h1 class="text-sm font-black uppercase tracking-tight text-slate-900">{{ companyInfo.company_name || companyInfo.name || 'STORE NAME' }}</h1>
            <p v-if="activeSubheading" class="text-[10px] text-slate-500 leading-tight">{{ activeSubheading }}</p>
            <p v-if="companyInfo.business_address || companyInfo.address" class="text-[10px] text-slate-500 leading-tight">{{ companyInfo.business_address || companyInfo.address }}</p>
          </div>

          <!-- Receipt Details Card -->
          <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200 text-[11px] space-y-1 mb-3">
            <div class="flex justify-between">
              <span class="text-slate-500">Receipt #:</span>
              <strong class="text-slate-900">{{ invoice.sale_number }}</strong>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-500">Date:</span>
              <span class="font-semibold">{{ formatDate(invoice.sale_date) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-500">Customer:</span>
              <span class="font-bold text-slate-800">{{ invoice.customer?.name || invoice.customer_name || 'Walk-in' }}</span>
            </div>
          </div>

          <!-- Items -->
          <div class="space-y-2 text-[11px] mb-3">
            <div v-for="(item, idx) in invoice.sale_items" :key="item.id || idx" class="p-2 bg-slate-50 rounded-lg border border-slate-100 space-y-1">
              <div class="font-bold text-slate-900">{{ item.product?.name || item.name || 'Item' }}</div>
              <div v-if="!isItemDescHidden && (item.product?.description || item.description)" class="text-[9px] text-slate-500">
                {{ item.product?.description || item.description }}
              </div>
              <div class="flex justify-between text-slate-600 text-[10px]">
                <span>Qty: {{ item.quantity }} × {{ formatCurrency(item.unit_price) }}</span>
                <span v-if="!isAmountHidden" class="font-extrabold text-slate-900">{{ formatCurrency(item.total_price || item.total_amount) }}</span>
              </div>
            </div>
          </div>

          <!-- Totals Banner -->
          <div class="bg-slate-900 text-white p-3 rounded-lg space-y-1 text-[11px] mb-3">
            <div class="flex justify-between text-slate-300">
              <span>Subtotal:</span>
              <span>{{ formatCurrency(invoice.subtotal) }}</span>
            </div>
            <div v-if="parseFloat(invoice.discount_amount) > 0" class="flex justify-between text-rose-300">
              <span>Discount:</span>
              <span>-{{ formatCurrency(invoice.discount_amount) }}</span>
            </div>
            <div class="flex justify-between items-center font-black text-sm pt-1 border-t border-slate-700">
              <span>TOTAL DUE:</span>
              <span class="text-indigo-300">{{ formatCurrency(invoice.total_amount) }}</span>
            </div>
          </div>

          <!-- Footer Barcode Placeholder -->
          <div class="text-center space-y-1">
            <div class="w-32 h-6 bg-slate-200 rounded mx-auto flex items-center justify-center text-[9px] font-mono text-slate-500">
              ||| | |||| | |||| ||
            </div>
            <p v-if="activeNotes" class="text-[10px] text-slate-500 pt-1 italic">{{ activeNotes }}</p>
            <p class="text-[10px] text-slate-500 pt-1">{{ invoice.footer || activeFooter || settings.default_terms_conditions }}</p>
          </div>
        </div>

      </template>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from '@/composables/useToast';
import { useCurrencyStore } from '@/stores/currency';
import api from '@/services/api';
import html2pdf from 'html2pdf.js';

const route = useRoute();
const router = useRouter();
const { showToast } = useToast();
const currencyStore = useCurrencyStore();

const invoice = ref(null);
const companyInfo = ref({});
const isGeneratingPdf = ref(false);
const settings = ref({
  sale_invoice_template: 'default',
  template_color: 'slate-400',
  thermal_receipt_template: 'classic',
  default_terms_conditions: 'Thank you for your business!',
  invoice_title: 'Invoice',
  invoice_subheading: '',
  logo_width: 128,
  logo_height: 128,
  default_notes: '',
  default_footer: '',
  column_item_name: 'Items',
  column_price_name: 'Price',
  column_quantity_name: 'Quantity',
  hide_item_description: false,
  hide_amount: false,
  thermal_title: 'Receipt',
  thermal_subheading: '',
  thermal_logo_width: 64,
  thermal_logo_height: 64,
  thermal_notes: '',
  thermal_footer: '',
  thermal_column_item_name: 'Items',
  thermal_column_price_name: 'Price',
  thermal_column_quantity_name: 'Quantity',
  thermal_hide_item_description: false,
  thermal_hide_amount: false
});

const getHeaderColorHex = (colorName) => {
  if (!colorName) return '#94a3b8';
  if (colorName.startsWith('#')) return colorName;
  const map = {
    'slate-100': '#f1f5f9', 'slate-200': '#e2e8f0', 'slate-300': '#cbd5e1', 'slate-400': '#94a3b8', 'slate-500': '#64748b', 'slate-600': '#475569', 'slate-700': '#334155', 'slate-800': '#1e293b', 'slate-900': '#0f172a', 'slate-950': '#020617',
    'red-100': '#fee2e2', 'red-200': '#fecaca', 'red-300': '#fca5a5', 'red-400': '#f87171', 'red-500': '#ef4444', 'red-600': '#dc2626', 'red-700': '#b91c1c', 'red-800': '#991b1b', 'red-900': '#7f1d1d', 'red-950': '#450a0a',
    'amber-100': '#fef3c7', 'amber-200': '#fde68a', 'amber-300': '#fcd34d', 'amber-400': '#fbbf24', 'amber-500': '#f59e0b', 'amber-600': '#d97706', 'amber-700': '#b45309', 'amber-800': '#92400e', 'amber-900': '#78350f', 'amber-950': '#451a03',
    'emerald-100': '#d1fae5', 'emerald-200': '#a7f3d0', 'emerald-300': '#6ee7b7', 'emerald-400': '#34d399', 'emerald-500': '#10b981', 'emerald-600': '#059669', 'emerald-700': '#047857', 'emerald-800': '#065f46', 'emerald-900': '#064e3b', 'emerald-950': '#022c22',
    'sky-100': '#e0f2fe', 'sky-200': '#bae6fd', 'sky-300': '#7dd3fc', 'sky-400': '#38bdf8', 'sky-500': '#0ea5e9', 'sky-600': '#0284c7', 'sky-700': '#0369a1', 'sky-800': '#075985', 'sky-900': '#0c4a6e', 'sky-950': '#082f49',
    'indigo-100': '#e0e7ff', 'indigo-200': '#c7d2fe', 'indigo-300': '#a5b4fc', 'indigo-400': '#818cf8', 'indigo-500': '#6366f1', 'indigo-600': '#4f46e5', 'indigo-700': '#4338ca', 'indigo-800': '#3730a3', 'indigo-900': '#312e81', 'indigo-950': '#1e1b4b',
    'purple-100': '#f3e8ff', 'purple-200': '#e9d5ff', 'purple-300': '#d8b4fe', 'purple-400': '#c084fc', 'purple-500': '#4c4b7c', 'purple-600': '#9333ea', 'purple-700': '#7e22ce', 'purple-800': '#6b21a8', 'purple-900': '#581c87', 'purple-950': '#3b0764',
    'rose-100': '#ffe4e6', 'rose-200': '#fecdd3', 'rose-300': '#fda4af', 'rose-400': '#fb7185', 'rose-500': '#f43f5e', 'rose-600': '#e11d48', 'rose-700': '#be123c', 'rose-800': '#9f1239', 'rose-900': '#881337', 'rose-950': '#4c0519'
  };
  return map[colorName] || '#94a3b8';
};

const loading = ref(true);

// Mode & Template selections
const isThermalMode = ref(route.query.type === 'thermal' || route.query.thermal === '1');
const selectedStandardTemplate = ref('default');
const selectedThermalTemplate = ref('classic');

const companyLogoUrl = computed(() => {
  if (companyInfo.value?.logo_url) return companyInfo.value.logo_url;
  if (companyInfo.value?.company_logo) {
    if (companyInfo.value.company_logo.startsWith('http') || companyInfo.value.company_logo.startsWith('/')) {
      return companyInfo.value.company_logo;
    }
    return `/storage/${companyInfo.value.company_logo}`;
  }
  if (companyInfo.value?.logo) {
    if (companyInfo.value.logo.startsWith('http') || companyInfo.value.logo.startsWith('/')) {
      return companyInfo.value.logo;
    }
    return `/storage/${companyInfo.value.logo}`;
  }
  return null;
});

const activeTitle = computed(() => isThermalMode.value ? (settings.value.thermal_title || 'Receipt') : (settings.value.invoice_title || 'Invoice'));
const activeSubheading = computed(() => isThermalMode.value ? settings.value.thermal_subheading : settings.value.invoice_subheading);
const activeNotes = computed(() => isThermalMode.value ? (settings.value.thermal_notes || settings.value.default_notes) : settings.value.default_notes);
const activeFooter = computed(() => isThermalMode.value ? (settings.value.thermal_footer || settings.value.default_footer) : settings.value.default_footer);
const activeItemCol = computed(() => isThermalMode.value ? (settings.value.thermal_column_item_name || 'Items') : (settings.value.column_item_name || 'Items'));
const activePriceCol = computed(() => isThermalMode.value ? (settings.value.thermal_column_price_name || 'Price') : (settings.value.column_price_name || 'Price'));
const activeQtyCol = computed(() => isThermalMode.value ? (settings.value.thermal_column_quantity_name || 'Quantity') : (settings.value.column_quantity_name || 'Quantity'));
const isItemDescHidden = computed(() => isThermalMode.value ? settings.value.thermal_hide_item_description : settings.value.hide_item_description);
const isAmountHidden = computed(() => isThermalMode.value ? settings.value.thermal_hide_amount : settings.value.hide_amount);

const activeTemplateName = computed(() => {
  if (isThermalMode.value) {
    return `${selectedThermalTemplate.value} thermal`;
  }
  return `${selectedStandardTemplate.value} standard`;
});

const fetchCompanyInfo = async () => {
  try {
    const res = await api.get('/companies/active');
    companyInfo.value = res.data?.company || res.data || {};
  } catch (e) {
    console.error('Error loading active company:', e);
  }
};

const fetchSettings = async () => {
  try {
    const res = await api.get('/invoice-purchase-settings');
    if (res.data) {
      settings.value = { ...settings.value, ...res.data };
      if (res.data.sale_invoice_template) {
        selectedStandardTemplate.value = res.data.sale_invoice_template;
      }
      if (res.data.thermal_receipt_template) {
        selectedThermalTemplate.value = res.data.thermal_receipt_template;
      }
      // Apply default printer setting if no explicit query parameter is set
      const hasQueryOverride = route.query.type !== undefined || route.query.thermal !== undefined;
      if (!hasQueryOverride && res.data.default_printer) {
        isThermalMode.value = res.data.default_printer === 'thermal';
      }
    }
  } catch (e) {
    console.error('Error loading settings:', e);
  }
};

const fetchInvoice = async () => {
  try {
    loading.value = true;
    const response = await api.get(`/sales/${route.params.id}`);
    invoice.value = response.data?.sale || response.data;
  } catch (err) {
    showToast('Error loading invoice data', 'error');
    router.push('/sales/invoices');
  } finally {
    loading.value = false;
  }
};

const isWholesaleInvoice = computed(() => {
  if (!invoice.value) return false;
  return invoice.value.sales_mode === 'wholesale' || invoice.value.pricing_mode === 'wholesale' || invoice.value.is_wholesale === true;
});

const dueAmount = computed(() => {
  if (!invoice.value) return 0;
  const total = parseFloat(invoice.value.total_amount || 0);
  const paid = parseFloat(invoice.value.paid_amount || 0);
  return Math.max(0, total - paid);
});

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatCurrency = (amount) => {
  return currencyStore.formatPrice(amount || 0);
};

const formatPaymentMethod = (method) => {
  const methods = {
    cash: 'Cash',
    card: 'Card',
    bank_transfer: 'Bank Transfer',
    mobile_payment: 'Mobile Payment',
    mixed: 'Mixed Payment'
  };
  return methods[method] || method || 'Cash';
};

const triggerPrint = () => {
  window.print();
};

const downloadPdf = async () => {
  try {
    isGeneratingPdf.value = true;
    const element = document.querySelector('.standard-sheet') || document.querySelector('.thermal-sheet');
    if (!element) {
      showToast('Print layout element not found', 'error');
      return;
    }

    const html2pdfLib = typeof html2pdf === 'function' ? html2pdf : (html2pdf?.default || window?.html2pdf);
    if (!html2pdfLib) {
      showToast('PDF generator library not loaded', 'error');
      return;
    }

    const filename = `Invoice_${invoice.value?.sale_number || route.params.id}.pdf`;
    const opt = {
      margin: isThermalMode.value ? [2, 2, 2, 2] : [6, 6, 6, 6],
      filename: filename,
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { 
        scale: 2, 
        useCORS: true, 
        allowTaint: true,
        logging: false,
        backgroundColor: '#ffffff',
        onclone: (clonedDoc) => {
          // 1. Sanitize all <style> elements to remove unsupported oklch color functions
          clonedDoc.querySelectorAll('style').forEach(styleTag => {
            if (styleTag.textContent && styleTag.textContent.includes('oklch')) {
              styleTag.textContent = styleTag.textContent.replace(/oklch\([^)]+\)/gi, '#64748b');
            }
          });

          // 2. Sanitize external link stylesheets with oklch rules
          clonedDoc.querySelectorAll('link[rel="stylesheet"]').forEach(link => {
            try {
              if (link.sheet) {
                const rules = Array.from(link.sheet.cssRules || []);
                let cssText = rules.map(r => r.cssText).join('\n');
                if (cssText.includes('oklch')) {
                  cssText = cssText.replace(/oklch\([^)]+\)/gi, '#64748b');
                  const newStyle = clonedDoc.createElement('style');
                  newStyle.textContent = cssText;
                  clonedDoc.head.appendChild(newStyle);
                  link.remove();
                }
              }
            } catch (e) {
              console.warn('Could not sanitize link stylesheet:', e);
            }
          });

          // 3. Sanitize inline styles on elements
          clonedDoc.querySelectorAll('*').forEach(el => {
            const styleAttr = el.getAttribute('style');
            if (styleAttr && styleAttr.includes('oklch')) {
              el.setAttribute('style', styleAttr.replace(/oklch\([^)]+\)/gi, '#64748b'));
            }
          });
        }
      },
      jsPDF: {
        unit: 'mm',
        format: isThermalMode.value ? [80, 297] : 'a4',
        orientation: 'portrait'
      }
    };

    await html2pdfLib().set(opt).from(element).save();
    showToast('PDF downloaded successfully!', 'success');
  } catch (error) {
    console.error('Error generating PDF:', error);
    showToast('Failed to download PDF', 'error');
  } finally {
    isGeneratingPdf.value = false;
  }
};

const goBack = () => {
  router.push('/sales/invoices');
};

onMounted(async () => {
  await fetchCompanyInfo();
  await fetchSettings();
  await fetchInvoice();
});
</script>

<style scoped>
@media print {
  /* Hide top navbar header, sidebars, navigation elements, and non-print action bar */
  :global(nav), 
  :global(aside), 
  :global(header), 
  :global([role="navigation"]),
  :global(div.fixed.z-\[60\]),
  .no-print {
    display: none !important;
  }
  
  :global(html),
  :global(body), 
  :global(#app), 
  :global(#app > div),
  :global(main) {
    background: white !important;
    color: black !important;
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
    min-height: auto !important;
    box-shadow: none !important;
  }

  /* Reset main container left margin from sidebar offset */
  :global(main) {
    margin-left: 0 !important;
    padding: 0 !important;
  }

  .print-root {
    position: relative !important;
    background: white !important;
    padding: 0 !important;
    margin: 0 !important;
    width: 100% !important;
    min-height: auto !important;
    display: block !important;
    visibility: visible !important;
  }

  body {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    background: white !important;
  }

  .standard-sheet {
    box-shadow: none !important;
    max-width: 100% !important;
    width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  .thermal-sheet {
    box-shadow: none !important;
    width: 80mm !important;
    padding: 0 !important;
    margin: 0 auto !important;
  }
}

@page {
  margin: 0.4in;
  size: auto;
}
</style>
