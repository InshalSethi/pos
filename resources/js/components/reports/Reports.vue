<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 pb-4 border-b border-slate-200 dark:border-zinc-800">
        <div>
          <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Financial & Managerial Reports</h1>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shadow-xs">
              <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Senior CA Approved Standard
            </span>
          </div>
          <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
            IFRS & GAAP Compliant Financial Statements, Ledger Reports & Inventory Analytics
          </p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-3">
          <div class="w-64">
            <FloatingDateRangePicker
              v-model:startDate="filterStartDate"
              v-model:endDate="filterEndDate"
              @change="onDateRangePickerChange"
              label="GLOBAL DATE FILTER"
              placeholder="Select Date Range"
            />
          </div>
        </div>
      </div>

      <!-- Main Financial Reports Hub Cards -->
      <div class="mb-10">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center">
          <span class="w-2 h-5 bg-indigo-600 rounded-full mr-2.5"></span>
          Core Financial Statements
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
          <!-- Profit & Loss Card -->
          <div
            @click="openFinancialReport('profit-loss')"
            class="group bg-gradient-to-br from-white to-slate-50/80 dark:from-zinc-900 dark:to-zinc-900/90 border border-slate-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm hover:shadow-xl hover:border-indigo-500/50 transition-all duration-300 cursor-pointer relative overflow-hidden"
          >
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-500/10 dark:bg-emerald-500/5 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center font-bold text-xl group-hover:scale-110 transition-transform">
                📈
              </div>
              <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">Income Statement</span>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
              Profit & Loss Statement
            </h3>
            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
              Gross Profit, COGS, EBITDA & Net Income breakdown.
            </p>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-zinc-800/80 flex items-center justify-between text-xs font-semibold text-indigo-600 dark:text-indigo-400">
              <span>View Statement</span>
              <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </div>
          </div>

          <!-- Balance Sheet Card -->
          <div
            @click="openFinancialReport('balance-sheet')"
            class="group bg-gradient-to-br from-white to-slate-50/80 dark:from-zinc-900 dark:to-zinc-900/90 border border-slate-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm hover:shadow-xl hover:border-indigo-500/50 transition-all duration-300 cursor-pointer relative overflow-hidden"
          >
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-blue-500/10 dark:bg-blue-500/5 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center font-bold text-xl group-hover:scale-110 transition-transform">
                ⚖️
              </div>
              <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300">Financial Position</span>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
              Balance Sheet
            </h3>
            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
              Assets = Liabilities + Owner Equity verification.
            </p>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-zinc-800/80 flex items-center justify-between text-xs font-semibold text-indigo-600 dark:text-indigo-400">
              <span>View Statement</span>
              <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </div>
          </div>

          <!-- Cash Flow Card -->
          <div
            @click="openFinancialReport('cash-flow')"
            class="group bg-gradient-to-br from-white to-slate-50/80 dark:from-zinc-900 dark:to-zinc-900/90 border border-slate-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm hover:shadow-xl hover:border-indigo-500/50 transition-all duration-300 cursor-pointer relative overflow-hidden"
          >
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-purple-500/10 dark:bg-purple-500/5 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-purple-500/10 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400 rounded-xl flex items-center justify-center font-bold text-xl group-hover:scale-110 transition-transform">
                💸
              </div>
              <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300">IAS 7 Indirect</span>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
              Cash Flow Statement
            </h3>
            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
              Operating, Investing & Financing cash activity.
            </p>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-zinc-800/80 flex items-center justify-between text-xs font-semibold text-indigo-600 dark:text-indigo-400">
              <span>View Statement</span>
              <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </div>
          </div>

          <!-- Trial Balance Card -->
          <div
            @click="openFinancialReport('trial-balance')"
            class="group bg-gradient-to-br from-white to-slate-50/80 dark:from-zinc-900 dark:to-zinc-900/90 border border-slate-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm hover:shadow-xl hover:border-indigo-500/50 transition-all duration-300 cursor-pointer relative overflow-hidden"
          >
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-amber-500/10 dark:bg-amber-500/5 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 rounded-xl flex items-center justify-center font-bold text-xl group-hover:scale-110 transition-transform">
                📋
              </div>
              <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300">General Ledger</span>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
              Trial Balance
            </h3>
            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
              Comprehensive Debit & Credit account audit ledger.
            </p>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-zinc-800/80 flex items-center justify-between text-xs font-semibold text-indigo-600 dark:text-indigo-400">
              <span>View Ledger</span>
              <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Sales & Inventory Secondary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Sales Analytics -->
        <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl p-6 shadow-sm">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
              <div class="w-9 h-9 bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-lg flex items-center justify-center font-bold">
                🛍️
              </div>
              <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Sales & Commercial Analytics</h3>
                <p class="text-xs text-slate-500 dark:text-zinc-400">Revenue, top products, customer velocity</p>
              </div>
            </div>
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
            <button @click="generateReport('sales-summary')" class="text-left p-3 rounded-xl bg-slate-50 dark:bg-zinc-800/50 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all border border-slate-100 dark:border-zinc-800">
              📊 Sales Summary
            </button>
            <button @click="generateReport('monthly-revenue')" class="text-left p-3 rounded-xl bg-slate-50 dark:bg-zinc-800/50 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all border border-slate-100 dark:border-zinc-800">
              📅 Monthly Revenue
            </button>
            <button @click="generateReport('top-products')" class="text-left p-3 rounded-xl bg-slate-50 dark:bg-zinc-800/50 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all border border-slate-100 dark:border-zinc-800">
              🏆 Top Selling Items
            </button>
            <button @click="generateReport('customer-analysis')" class="text-left p-3 rounded-xl bg-slate-50 dark:bg-zinc-800/50 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all border border-slate-100 dark:border-zinc-800">
              👥 Customer Analysis
            </button>
            <button @click="generateReport('inactive-customers')" class="text-left p-3 rounded-xl bg-slate-50 dark:bg-zinc-800/50 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all border border-slate-100 dark:border-zinc-800">
              🚫 Inactive Customers
            </button>
          </div>
        </div>

        <!-- Inventory Analytics -->
        <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl p-6 shadow-sm">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
              <div class="w-9 h-9 bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 rounded-lg flex items-center justify-center font-bold">
                📦
              </div>
              <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Inventory & Stock Analytics</h3>
                <p class="text-xs text-slate-500 dark:text-zinc-400">Stock valuation, alerts, movement</p>
              </div>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-2.5">
            <button @click="generateReport('inventory-summary')" class="text-left p-3 rounded-xl bg-slate-50 dark:bg-zinc-800/50 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all border border-slate-100 dark:border-zinc-800">
              📦 Current Stock Levels
            </button>
            <button @click="generateReport('low-stock')" class="text-left p-3 rounded-xl bg-slate-50 dark:bg-zinc-800/50 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all border border-slate-100 dark:border-zinc-800">
              ⚠️ Low Stock Alert
            </button>
            <button @click="generateReport('inventory-valuation')" class="text-left p-3 rounded-xl bg-slate-50 dark:bg-zinc-800/50 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all border border-slate-100 dark:border-zinc-800">
              💰 Inventory Valuation
            </button>
            <button @click="generateReport('stock-movement')" class="text-left p-3 rounded-xl bg-slate-50 dark:bg-zinc-800/50 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all border border-slate-100 dark:border-zinc-800">
              🔄 Stock Movements
            </button>
          </div>
        </div>
      </div>

      <!-- Financial Statements Modal -->
      <div v-if="showReportModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-3 sm:p-6 overflow-y-auto bg-slate-950/60 backdrop-blur-md transition-all duration-300">
        <div class="relative w-full max-w-5xl shadow-2xl rounded-3xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 text-slate-800 dark:text-slate-100 overflow-hidden flex flex-col max-h-[92vh] my-auto">

          <!-- Modal Header with Tab Selector -->
          <div class="px-6 py-4 border-b border-slate-200 dark:border-zinc-800 bg-slate-50/80 dark:bg-zinc-900/90 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <div class="flex items-center space-x-2">
                <span class="text-xl font-black text-indigo-600 dark:text-indigo-400">FINANCIAL</span>
                <span class="text-xs font-bold px-2 py-0.5 rounded bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 uppercase tracking-widest">GAAP Audit Mode</span>
              </div>
              <h2 class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">{{ currentReportTitle }}</h2>
            </div>

            <!-- Tab Switcher for Financial Statements -->
            <div v-if="isFinancialReport(currentReportType)" class="flex items-center p-1 bg-slate-200/70 dark:bg-zinc-800 rounded-xl space-x-1 text-xs font-semibold">
              <button
                @click="switchFinancialTab('profit-loss')"
                :class="currentReportType === 'profit-loss' ? 'bg-white dark:bg-zinc-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900'"
                class="px-3 py-1.5 rounded-lg transition-all"
              >
                Profit & Loss
              </button>
              <button
                @click="switchFinancialTab('balance-sheet')"
                :class="currentReportType === 'balance-sheet' ? 'bg-white dark:bg-zinc-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900'"
                class="px-3 py-1.5 rounded-lg transition-all"
              >
                Balance Sheet
              </button>
              <button
                @click="switchFinancialTab('trial-balance')"
                :class="currentReportType === 'trial-balance' ? 'bg-white dark:bg-zinc-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900'"
                class="px-3 py-1.5 rounded-lg transition-all"
              >
                Trial Balance
              </button>
              <button
                @click="switchFinancialTab('cash-flow')"
                :class="currentReportType === 'cash-flow' ? 'bg-white dark:bg-zinc-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900'"
                class="px-3 py-1.5 rounded-lg transition-all"
              >
                Cash Flow
              </button>
            </div>

            <!-- Close Button -->
            <button @click="closeReportModal" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-xl hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Date Filters Bar inside Modal -->
          <div class="px-6 py-3 border-b border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 flex items-center justify-between gap-3 text-xs">
            <div class="flex items-center space-x-2">
              <span class="text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">Report Filter Period:</span>
            </div>

            <!-- Floating Date Range Picker Component -->
            <div class="w-72">
              <FloatingDateRangePicker
                v-model:startDate="filterStartDate"
                v-model:endDate="filterEndDate"
                @change="onDateRangePickerChange"
                label=""
                placeholder="Select Date Range"
              />
            </div>
          </div>

          <!-- Modal Body Content -->
          <div class="p-6 overflow-y-auto flex-1 space-y-6">
            <!-- Loading Spinner -->
            <div v-if="loadingReport" class="flex flex-col items-center justify-center py-16 space-y-3">
              <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
              <p class="text-sm font-semibold text-slate-600 dark:text-zinc-400">Compiling financial ledger records...</p>
            </div>

            <!-- Error Banner -->
            <div v-else-if="reportError" class="p-4 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 flex items-center space-x-3">
              <svg class="w-6 h-6 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <div>
                <h4 class="font-bold text-sm">Failed to generate report</h4>
                <p class="text-xs mt-0.5">{{ reportError }}</p>
              </div>
            </div>

            <!-- Reports Render Engine -->
            <div v-else-if="reportData" class="printable-report">
              <!-- 1. PROFIT & LOSS STATEMENT -->
              <div v-if="currentReportType === 'profit-loss'" class="space-y-6">
                <!-- KPI Executive Summary Banner -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                  <div class="p-4 rounded-2xl bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700">
                    <span class="text-xs font-semibold text-slate-500 dark:text-zinc-400">Net Sales Revenue</span>
                    <div class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ formatCurrency(reportData.net_revenue) }}</div>
                    <span class="text-[10px] text-slate-400">Gross: {{ formatCurrency(reportData.total_gross_revenue) }}</span>
                  </div>
                  <div class="p-4 rounded-2xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800">
                    <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">Gross Profit</span>
                    <div class="text-xl font-black text-emerald-800 dark:text-emerald-200 mt-1">{{ formatCurrency(reportData.gross_profit) }}</div>
                    <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">{{ reportData.gross_profit_margin }}% Margin</span>
                  </div>
                  <div class="p-4 rounded-2xl bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800">
                    <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Operating Profit (EBITDA)</span>
                    <div class="text-xl font-black text-blue-800 dark:text-blue-200 mt-1">{{ formatCurrency(reportData.operating_profit) }}</div>
                    <span class="text-[11px] font-bold text-blue-600 dark:text-blue-400">{{ reportData.operating_profit_margin }}% Margin</span>
                  </div>
                  <div :class="reportData.net_income >= 0 ? 'bg-indigo-50/80 dark:bg-indigo-950/40 border-indigo-200 dark:border-indigo-800' : 'bg-rose-50/80 dark:bg-rose-950/40 border-rose-200 dark:border-rose-800'" class="p-4 rounded-2xl border">
                    <span :class="reportData.net_income >= 0 ? 'text-indigo-700 dark:text-indigo-300' : 'text-rose-700 dark:text-rose-300'" class="text-xs font-semibold">Net Income (Bottom Line)</span>
                    <div :class="reportData.net_income >= 0 ? 'text-indigo-900 dark:text-indigo-100' : 'text-rose-900 dark:text-rose-100'" class="text-xl font-black mt-1">{{ formatCurrency(reportData.net_income) }}</div>
                    <span :class="reportData.net_income >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-rose-600 dark:text-rose-400'" class="text-[11px] font-bold">{{ reportData.net_income_margin }}% Net Margin</span>
                  </div>
                </div>

                <!-- Structured P&L Table -->
                <div class="border border-slate-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-sm bg-white dark:bg-zinc-900">
                  <div class="px-5 py-3.5 bg-slate-100 dark:bg-zinc-800 font-bold text-xs text-slate-700 dark:text-zinc-200 flex justify-between uppercase tracking-wider">
                    <span>Account Description</span>
                    <span>Amount</span>
                  </div>

                  <!-- I. Operating Revenue -->
                  <div class="p-4 space-y-2 border-b border-slate-100 dark:border-zinc-800">
                    <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">I. Operating Revenue</div>
                    <div v-for="item in reportData.gross_revenue" :key="item.account_code" class="flex justify-between text-xs py-1 hover:bg-slate-50 dark:hover:bg-zinc-800/50 rounded px-2">
                      <span class="text-slate-700 dark:text-zinc-300"><span class="font-mono text-slate-400 mr-2">{{ item.account_code }}</span>{{ item.account_name }}</span>
                      <span class="font-semibold text-slate-900 dark:text-white">{{ formatCurrency(item.amount) }}</span>
                    </div>
                    <div v-if="!reportData.gross_revenue.length" class="text-xs text-slate-400 italic px-2">No direct sales revenue recorded in period</div>

                    <template v-if="reportData.discounts_returns && reportData.discounts_returns.length">
                      <div class="text-[11px] font-semibold text-slate-500 mt-2 px-2">Less: Sales Discounts & Allowances</div>
                      <div v-for="item in reportData.discounts_returns" :key="item.account_code" class="flex justify-between text-xs py-1 hover:bg-slate-50 dark:hover:bg-zinc-800/50 rounded px-2 text-rose-600">
                        <span><span class="font-mono text-rose-400 mr-2">{{ item.account_code }}</span>{{ item.account_name }}</span>
                        <span class="font-semibold">({{ formatCurrency(item.amount) }})</span>
                      </div>
                    </template>

                    <div class="flex justify-between font-bold text-xs pt-2 border-t border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-white px-2">
                      <span>Net Sales Revenue</span>
                      <span>{{ formatCurrency(reportData.net_revenue) }}</span>
                    </div>
                  </div>

                  <!-- II. Cost of Goods Sold -->
                  <div class="p-4 space-y-2 border-b border-slate-100 dark:border-zinc-800">
                    <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">II. Cost of Goods Sold (COGS)</div>
                    <div v-for="item in reportData.cogs" :key="item.account_code" class="flex justify-between text-xs py-1 hover:bg-slate-50 dark:hover:bg-zinc-800/50 rounded px-2">
                      <span class="text-slate-700 dark:text-zinc-300"><span class="font-mono text-slate-400 mr-2">{{ item.account_code }}</span>{{ item.account_name }}</span>
                      <span class="font-semibold text-slate-900 dark:text-white">{{ formatCurrency(item.amount) }}</span>
                    </div>
                    <div v-if="!reportData.cogs.length" class="text-xs text-slate-400 italic px-2">No COGS expenses posted</div>

                    <div class="flex justify-between font-bold text-xs pt-2 border-t border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-white px-2">
                      <span>Total Cost of Goods Sold</span>
                      <span>{{ formatCurrency(reportData.total_cogs) }}</span>
                    </div>
                  </div>

                  <!-- GROSS PROFIT HIGHLIGHT ROW -->
                  <div class="px-5 py-3.5 bg-emerald-500/10 dark:bg-emerald-950/40 border-b border-slate-200 dark:border-zinc-800 flex justify-between font-extrabold text-sm text-emerald-900 dark:text-emerald-200">
                    <span>GROSS PROFIT</span>
                    <span>{{ formatCurrency(reportData.gross_profit) }}</span>
                  </div>

                  <!-- III. Operating Expenses -->
                  <div class="p-4 space-y-2 border-b border-slate-100 dark:border-zinc-800">
                    <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">III. Operating Expenses (OpEx)</div>
                    <div v-for="item in reportData.operating_expenses" :key="item.account_code" class="flex justify-between text-xs py-1 hover:bg-slate-50 dark:hover:bg-zinc-800/50 rounded px-2">
                      <span class="text-slate-700 dark:text-zinc-300"><span class="font-mono text-slate-400 mr-2">{{ item.account_code }}</span>{{ item.account_name }}</span>
                      <span class="font-semibold text-slate-900 dark:text-white">{{ formatCurrency(item.amount) }}</span>
                    </div>
                    <div v-if="!reportData.operating_expenses.length" class="text-xs text-slate-400 italic px-2">No operating expenses recorded</div>

                    <div class="flex justify-between font-bold text-xs pt-2 border-t border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-white px-2">
                      <span>Total Operating Expenses</span>
                      <span>{{ formatCurrency(reportData.total_operating_expenses) }}</span>
                    </div>
                  </div>

                  <!-- OPERATING PROFIT HIGHLIGHT ROW -->
                  <div class="px-5 py-3.5 bg-blue-500/10 dark:bg-blue-950/40 border-b border-slate-200 dark:border-zinc-800 flex justify-between font-extrabold text-sm text-blue-900 dark:text-blue-200">
                    <span>OPERATING PROFIT (EBITDA)</span>
                    <span>{{ formatCurrency(reportData.operating_profit) }}</span>
                  </div>

                  <!-- IV. Non-Operating Income & Expenses -->
                  <div class="p-4 space-y-2 border-b border-slate-100 dark:border-zinc-800">
                    <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">IV. Non-Operating Income & Expenses</div>
                    <div v-for="item in reportData.other_income" :key="item.account_code" class="flex justify-between text-xs py-1 text-emerald-600 px-2">
                      <span><span class="font-mono mr-2">{{ item.account_code }}</span>{{ item.account_name }}</span>
                      <span class="font-semibold">+ {{ formatCurrency(item.amount) }}</span>
                    </div>
                    <div v-for="item in reportData.other_expenses" :key="item.account_code" class="flex justify-between text-xs py-1 text-rose-600 px-2">
                      <span><span class="font-mono mr-2">{{ item.account_code }}</span>{{ item.account_name }}</span>
                      <span class="font-semibold">- {{ formatCurrency(item.amount) }}</span>
                    </div>
                    <div v-if="!reportData.other_income.length && !reportData.other_expenses.length" class="text-xs text-slate-400 italic px-2">None</div>

                    <div class="flex justify-between font-bold text-xs pt-2 border-t border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-white px-2">
                      <span>Net Non-Operating Income</span>
                      <span>{{ formatCurrency(reportData.net_non_operating) }}</span>
                    </div>
                  </div>

                  <!-- FINAL NET INCOME GRAND TOTAL ROW -->
                  <div :class="reportData.net_income >= 0 ? 'bg-indigo-600 text-white' : 'bg-rose-600 text-white'" class="px-5 py-4 flex justify-between font-black text-base tracking-wide">
                    <span>NET INCOME / (NET PROFIT AFTER TAX)</span>
                    <span>{{ formatCurrency(reportData.net_income) }}</span>
                  </div>
                </div>
              </div>

              <!-- 2. BALANCE SHEET (STATEMENT OF FINANCIAL POSITION) -->
              <div v-else-if="currentReportType === 'balance-sheet'" class="space-y-6">
                <!-- KPI Executive Summary Banner -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                  <div class="p-4 rounded-2xl bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800">
                    <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Total Assets</span>
                    <div class="text-xl font-black text-blue-900 dark:text-blue-100 mt-1">{{ formatCurrency(reportData.total_assets) }}</div>
                    <span class="text-[10px] text-blue-500">As of {{ reportData.as_of_date }}</span>
                  </div>
                  <div class="p-4 rounded-2xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800">
                    <span class="text-xs font-semibold text-amber-700 dark:text-amber-300">Total Liabilities</span>
                    <div class="text-xl font-black text-amber-900 dark:text-amber-100 mt-1">{{ formatCurrency(reportData.total_liabilities) }}</div>
                    <span class="text-[10px] text-amber-500">Current & Long-Term</span>
                  </div>
                  <div class="p-4 rounded-2xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800">
                    <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">Total Owner's Equity</span>
                    <div class="text-xl font-black text-emerald-900 dark:text-emerald-100 mt-1">{{ formatCurrency(reportData.total_equity) }}</div>
                    <span class="text-[10px] text-emerald-500">Incl. Net Profit</span>
                  </div>
                  <div :class="reportData.is_balanced ? 'bg-emerald-50/80 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800' : 'bg-rose-50/80 dark:bg-rose-950/40 border-rose-200 dark:border-rose-800'" class="p-4 rounded-2xl border">
                    <span :class="reportData.is_balanced ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300'" class="text-xs font-semibold">Accounting Equation</span>
                    <div :class="reportData.is_balanced ? 'text-emerald-900 dark:text-emerald-100' : 'text-rose-900 dark:text-rose-100'" class="text-lg font-black mt-1">
                      {{ reportData.is_balanced ? '✓ BALANCED' : '⚠️ UNBALANCED' }}
                    </div>
                    <span class="text-[10px] font-mono">{{ reportData.is_balanced ? 'Assets = Liab + Equity' : `Var: ${formatCurrency(reportData.variance)}` }}</span>
                  </div>
                </div>

                <!-- Two-Column GAAP Balance Sheet Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                  <!-- LEFT COLUMN: ASSETS -->
                  <div class="border border-slate-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-sm bg-white dark:bg-zinc-900 flex flex-col justify-between">
                    <div>
                      <div class="px-5 py-3.5 bg-blue-600 text-white font-bold text-xs uppercase tracking-wider flex justify-between">
                        <span>ASSETS</span>
                        <span>AMOUNT</span>
                      </div>

                      <!-- Current Assets -->
                      <div class="p-4 space-y-2 border-b border-slate-100 dark:border-zinc-800">
                        <div class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Current Assets</div>
                        <div v-for="asset in reportData.current_assets" :key="asset.account_code" class="flex justify-between text-xs py-1 hover:bg-slate-50 dark:hover:bg-zinc-800/50 rounded px-2">
                          <span class="text-slate-700 dark:text-zinc-300"><span class="font-mono text-slate-400 mr-2">{{ asset.account_code }}</span>{{ asset.account_name }}</span>
                          <span class="font-semibold text-slate-900 dark:text-white">{{ formatCurrency(asset.amount) }}</span>
                        </div>
                        <div v-if="!reportData.current_assets.length" class="text-xs text-slate-400 italic px-2">No current asset balances</div>
                        <div class="flex justify-between font-bold text-xs pt-2 border-t border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-white px-2">
                          <span>Total Current Assets</span>
                          <span>{{ formatCurrency(reportData.total_current_assets) }}</span>
                        </div>
                      </div>

                      <!-- Fixed Assets -->
                      <div class="p-4 space-y-2 border-b border-slate-100 dark:border-zinc-800">
                        <div class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Fixed & Non-Current Assets</div>
                        <div v-for="asset in reportData.fixed_assets" :key="asset.account_code" class="flex justify-between text-xs py-1 hover:bg-slate-50 dark:hover:bg-zinc-800/50 rounded px-2">
                          <span class="text-slate-700 dark:text-zinc-300"><span class="font-mono text-slate-400 mr-2">{{ asset.account_code }}</span>{{ asset.account_name }}</span>
                          <span class="font-semibold text-slate-900 dark:text-white">{{ formatCurrency(asset.amount) }}</span>
                        </div>
                        <div v-if="!reportData.fixed_assets.length" class="text-xs text-slate-400 italic px-2">No fixed assets recorded</div>
                        <div class="flex justify-between font-bold text-xs pt-2 border-t border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-white px-2">
                          <span>Total Fixed Assets</span>
                          <span>{{ formatCurrency(reportData.total_fixed_assets) }}</span>
                        </div>
                      </div>

                      <!-- Other Assets -->
                      <div v-if="reportData.other_assets && reportData.other_assets.length" class="p-4 space-y-2 border-b border-slate-100 dark:border-zinc-800">
                        <div class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Other Assets</div>
                        <div v-for="asset in reportData.other_assets" :key="asset.account_code" class="flex justify-between text-xs py-1 hover:bg-slate-50 dark:hover:bg-zinc-800/50 rounded px-2">
                          <span class="text-slate-700 dark:text-zinc-300"><span class="font-mono text-slate-400 mr-2">{{ asset.account_code }}</span>{{ asset.account_name }}</span>
                          <span class="font-semibold text-slate-900 dark:text-white">{{ formatCurrency(asset.amount) }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-xs pt-2 border-t border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-white px-2">
                          <span>Total Other Assets</span>
                          <span>{{ formatCurrency(reportData.total_other_assets) }}</span>
                        </div>
                      </div>
                    </div>

                    <!-- TOTAL ASSETS FOOTER -->
                    <div class="px-5 py-4 bg-blue-700 text-white flex justify-between font-black text-sm uppercase tracking-wide">
                      <span>TOTAL ASSETS</span>
                      <span>{{ formatCurrency(reportData.total_assets) }}</span>
                    </div>
                  </div>

                  <!-- RIGHT COLUMN: LIABILITIES & EQUITY -->
                  <div class="border border-slate-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-sm bg-white dark:bg-zinc-900 flex flex-col justify-between">
                    <div>
                      <div class="px-5 py-3.5 bg-slate-800 text-white font-bold text-xs uppercase tracking-wider flex justify-between">
                        <span>LIABILITIES & EQUITY</span>
                        <span>AMOUNT</span>
                      </div>

                      <!-- Current Liabilities -->
                      <div class="p-4 space-y-2 border-b border-slate-100 dark:border-zinc-800">
                        <div class="text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">Current Liabilities</div>
                        <div v-for="item in reportData.current_liabilities" :key="item.account_code" class="flex justify-between text-xs py-1 hover:bg-slate-50 dark:hover:bg-zinc-800/50 rounded px-2">
                          <span class="text-slate-700 dark:text-zinc-300"><span class="font-mono text-slate-400 mr-2">{{ item.account_code }}</span>{{ item.account_name }}</span>
                          <span class="font-semibold text-slate-900 dark:text-white">{{ formatCurrency(item.amount) }}</span>
                        </div>
                        <div v-if="!reportData.current_liabilities.length" class="text-xs text-slate-400 italic px-2">No current liabilities</div>
                        <div class="flex justify-between font-bold text-xs pt-2 border-t border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-white px-2">
                          <span>Total Current Liabilities</span>
                          <span>{{ formatCurrency(reportData.total_current_liabilities) }}</span>
                        </div>
                      </div>

                      <!-- Long-term Liabilities -->
                      <div v-if="reportData.long_term_liabilities && reportData.long_term_liabilities.length" class="p-4 space-y-2 border-b border-slate-100 dark:border-zinc-800">
                        <div class="text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">Long-Term Liabilities</div>
                        <div v-for="item in reportData.long_term_liabilities" :key="item.account_code" class="flex justify-between text-xs py-1 hover:bg-slate-50 dark:hover:bg-zinc-800/50 rounded px-2">
                          <span class="text-slate-700 dark:text-zinc-300"><span class="font-mono text-slate-400 mr-2">{{ item.account_code }}</span>{{ item.account_name }}</span>
                          <span class="font-semibold text-slate-900 dark:text-white">{{ formatCurrency(item.amount) }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-xs pt-2 border-t border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-white px-2">
                          <span>Total Long-Term Liabilities</span>
                          <span>{{ formatCurrency(reportData.total_long_term_liabilities) }}</span>
                        </div>
                      </div>

                      <!-- Owner's Equity -->
                      <div class="p-4 space-y-2 border-b border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-800/30">
                        <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Owner's Equity</div>
                        <div v-for="item in reportData.equity_accounts" :key="item.account_code" class="flex justify-between text-xs py-1 hover:bg-slate-50 dark:hover:bg-zinc-800/50 rounded px-2">
                          <span class="text-slate-700 dark:text-zinc-300"><span class="font-mono text-slate-400 mr-2">{{ item.account_code }}</span>{{ item.account_name }}</span>
                          <span class="font-semibold text-slate-900 dark:text-white">{{ formatCurrency(item.amount) }}</span>
                        </div>
                        <!-- Current Period Net Income -->
                        <div class="flex justify-between text-xs py-1 rounded px-2 bg-indigo-50 dark:bg-indigo-950/60 font-bold text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-900">
                          <span>Current Period Net Income (From P&L)</span>
                          <span>{{ formatCurrency(reportData.current_period_net_income) }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-xs pt-2 border-t border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-white px-2">
                          <span>Total Equity</span>
                          <span>{{ formatCurrency(reportData.total_equity) }}</span>
                        </div>
                      </div>
                    </div>

                    <!-- TOTAL LIABILITIES & EQUITY FOOTER -->
                    <div class="px-5 py-4 bg-slate-900 text-white flex justify-between font-black text-sm uppercase tracking-wide">
                      <span>TOTAL LIABILITIES & EQUITY</span>
                      <span>{{ formatCurrency(reportData.total_liabilities_equity) }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- 3. TRIAL BALANCE -->
              <div v-else-if="currentReportType === 'trial-balance'" class="space-y-5">
                <!-- Header Banner -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700">
                  <div>
                    <span class="text-xs font-semibold text-slate-500">Total Double-Entry Audit</span>
                    <div class="flex items-center space-x-4 mt-1">
                      <div><span class="text-xs text-slate-400">Total Debits:</span> <span class="text-base font-bold text-slate-900 dark:text-white">{{ formatCurrency(reportData.total_debits) }}</span></div>
                      <div><span class="text-xs text-slate-400">Total Credits:</span> <span class="text-base font-bold text-slate-900 dark:text-white">{{ formatCurrency(reportData.total_credits) }}</span></div>
                    </div>
                  </div>
                  <div class="flex items-center space-x-3">
                    <span :class="reportData.is_balanced ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-rose-100 text-rose-800 border-rose-300'" class="px-3 py-1.5 rounded-xl border text-xs font-bold">
                      {{ reportData.is_balanced ? '✓ LEDGER BALANCED ($0.00 Variance)' : `⚠️ UNBALANCED (Variance: ${formatCurrency(reportData.variance)})` }}
                    </span>
                  </div>
                </div>

                <!-- Filters -->
                <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                  <div class="w-full md:w-72">
                    <input type="text" v-model="tbSearchQuery" placeholder="Search account code or name..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500" />
                  </div>
                  <div class="flex items-center space-x-2 text-xs">
                    <span class="text-slate-400">Filter Type:</span>
                    <select v-model="tbAccountTypeFilter" class="px-3 py-1.5 rounded-xl border border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 text-slate-800 dark:text-slate-100">
                      <option value="">All Account Types</option>
                      <option value="asset">Assets</option>
                      <option value="liability">Liabilities</option>
                      <option value="equity">Equity</option>
                      <option value="revenue">Revenue</option>
                      <option value="expense">Expense</option>
                    </select>
                  </div>
                </div>

                <!-- Trial Balance Table -->
                <div class="border border-slate-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-sm bg-white dark:bg-zinc-900">
                  <table class="min-w-full divide-y divide-slate-200 dark:divide-zinc-800 text-xs">
                    <thead class="bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 font-bold uppercase tracking-wider">
                      <tr>
                        <th class="px-4 py-3 text-left">Code</th>
                        <th class="px-4 py-3 text-left">Account Name</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-right">Debit Balance ($)</th>
                        <th class="px-4 py-3 text-right">Credit Balance ($)</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/80">
                      <tr v-for="account in filteredTrialBalanceAccounts" :key="account.account_code" class="hover:bg-slate-50 dark:hover:bg-zinc-800/50">
                        <td class="px-4 py-2.5 font-mono font-semibold text-indigo-600 dark:text-indigo-400">{{ account.account_code }}</td>
                        <td class="px-4 py-2.5 font-medium text-slate-900 dark:text-slate-100">{{ account.account_name }}</td>
                        <td class="px-4 py-2.5 capitalize text-slate-400">{{ account.account_type }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold text-slate-900 dark:text-white">{{ account.debit_balance > 0 ? formatCurrency(account.debit_balance) : '-' }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold text-slate-900 dark:text-white">{{ account.credit_balance > 0 ? formatCurrency(account.credit_balance) : '-' }}</td>
                      </tr>
                      <tr v-if="!filteredTrialBalanceAccounts.length">
                        <td colspan="5" class="px-4 py-6 text-center text-slate-400 italic">No matching accounts found</td>
                      </tr>
                    </tbody>
                    <tfoot class="bg-slate-900 text-white font-extrabold">
                      <tr>
                        <td colspan="3" class="px-4 py-3 uppercase tracking-wider">TOTAL GENERAL LEDGER BALANCES</td>
                        <td class="px-4 py-3 text-right">{{ formatCurrency(reportData.total_debits) }}</td>
                        <td class="px-4 py-3 text-right">{{ formatCurrency(reportData.total_credits) }}</td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>

              <!-- 4. CASH FLOW STATEMENT (IAS 7) -->
              <div v-else-if="currentReportType === 'cash-flow'" class="space-y-6">
                <!-- KPI Executive Summary Banner -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                  <div class="p-3.5 rounded-2xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800">
                    <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-300">Operating Cash</span>
                    <div class="text-base font-black text-emerald-900 dark:text-emerald-100 mt-0.5">{{ formatCurrency(reportData.net_operating_cash) }}</div>
                  </div>
                  <div class="p-3.5 rounded-2xl bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800">
                    <span class="text-[11px] font-semibold text-blue-700 dark:text-blue-300">Investing Cash</span>
                    <div class="text-base font-black text-blue-900 dark:text-blue-100 mt-0.5">{{ formatCurrency(reportData.net_investing_cash) }}</div>
                  </div>
                  <div class="p-3.5 rounded-2xl bg-purple-50/80 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800">
                    <span class="text-[11px] font-semibold text-purple-700 dark:text-purple-300">Financing Cash</span>
                    <div class="text-base font-black text-purple-900 dark:text-purple-100 mt-0.5">{{ formatCurrency(reportData.net_financing_cash) }}</div>
                  </div>
                  <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700">
                    <span class="text-[11px] font-semibold text-slate-500">Net Change in Cash</span>
                    <div class="text-base font-black text-slate-900 dark:text-white mt-0.5">{{ formatCurrency(reportData.net_change_in_cash) }}</div>
                  </div>
                  <div class="p-3.5 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800 col-span-2 md:col-span-1">
                    <span class="text-[11px] font-semibold text-indigo-700 dark:text-indigo-300">Ending Cash Balance</span>
                    <div class="text-base font-black text-indigo-900 dark:text-indigo-100 mt-0.5">{{ formatCurrency(reportData.ending_cash) }}</div>
                  </div>
                </div>

                <!-- Structured Cash Flow Table -->
                <div class="border border-slate-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-sm bg-white dark:bg-zinc-900">
                  <!-- 1. Operating Activities -->
                  <div class="p-4 space-y-2 border-b border-slate-100 dark:border-zinc-800">
                    <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">1. Cash Flows From Operating Activities</div>
                    <div v-for="item in reportData.operating_activities" :key="item.description" class="flex justify-between text-xs py-1 hover:bg-slate-50 dark:hover:bg-zinc-800/50 rounded px-2">
                      <span class="text-slate-700 dark:text-zinc-300">{{ item.description }}</span>
                      <span :class="item.amount >= 0 ? 'text-slate-900 dark:text-white font-semibold' : 'text-rose-600 font-semibold'">{{ formatCurrency(item.amount) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-xs pt-2 border-t border-slate-200 dark:border-zinc-800 text-emerald-700 dark:text-emerald-300 px-2">
                      <span>Net Cash Provided By / (Used In) Operating Activities</span>
                      <span>{{ formatCurrency(reportData.net_operating_cash) }}</span>
                    </div>
                  </div>

                  <!-- 2. Investing Activities -->
                  <div class="p-4 space-y-2 border-b border-slate-100 dark:border-zinc-800">
                    <div class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">2. Cash Flows From Investing Activities</div>
                    <div v-for="item in reportData.investing_activities" :key="item.description" class="flex justify-between text-xs py-1 hover:bg-slate-50 dark:hover:bg-zinc-800/50 rounded px-2">
                      <span class="text-slate-700 dark:text-zinc-300">{{ item.description }}</span>
                      <span :class="item.amount >= 0 ? 'text-slate-900 dark:text-white font-semibold' : 'text-rose-600 font-semibold'">{{ formatCurrency(item.amount) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-xs pt-2 border-t border-slate-200 dark:border-zinc-800 text-blue-700 dark:text-blue-300 px-2">
                      <span>Net Cash Provided By / (Used In) Investing Activities</span>
                      <span>{{ formatCurrency(reportData.net_investing_cash) }}</span>
                    </div>
                  </div>

                  <!-- 3. Financing Activities -->
                  <div class="p-4 space-y-2 border-b border-slate-100 dark:border-zinc-800">
                    <div class="text-xs font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider">3. Cash Flows From Financing Activities</div>
                    <div v-for="item in reportData.financing_activities" :key="item.description" class="flex justify-between text-xs py-1 hover:bg-slate-50 dark:hover:bg-zinc-800/50 rounded px-2">
                      <span class="text-slate-700 dark:text-zinc-300">{{ item.description }}</span>
                      <span :class="item.amount >= 0 ? 'text-slate-900 dark:text-white font-semibold' : 'text-rose-600 font-semibold'">{{ formatCurrency(item.amount) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-xs pt-2 border-t border-slate-200 dark:border-zinc-800 text-purple-700 dark:text-purple-300 px-2">
                      <span>Net Cash Provided By / (Used In) Financing Activities</span>
                      <span>{{ formatCurrency(reportData.net_financing_cash) }}</span>
                    </div>
                  </div>

                  <!-- RECONCILIATION SUMMARY ROW -->
                  <div class="p-4 bg-slate-50 dark:bg-zinc-800/60 space-y-2 border-b border-slate-200 dark:border-zinc-800 text-xs">
                    <div class="flex justify-between text-slate-600 dark:text-zinc-400 font-medium">
                      <span>Net Increase / (Decrease) in Cash & Cash Equivalents</span>
                      <span class="font-bold text-slate-900 dark:text-white">{{ formatCurrency(reportData.net_change_in_cash) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600 dark:text-zinc-400 font-medium">
                      <span>Cash & Cash Equivalents at Beginning of Period</span>
                      <span>{{ formatCurrency(reportData.beginning_cash) }}</span>
                    </div>
                  </div>

                  <!-- ENDING CASH GRAND TOTAL ROW -->
                  <div class="px-5 py-4 bg-indigo-600 text-white flex justify-between font-black text-sm uppercase tracking-wide">
                    <span>CASH & CASH EQUIVALENTS AT END OF PERIOD</span>
                    <span>{{ formatCurrency(reportData.ending_cash) }}</span>
                  </div>
                </div>
              </div>

              <!-- 5. OPERATIONAL REPORTS (Sales & Inventory Analytics) -->
              <div v-else class="space-y-6">

                <!-- 5A. SALES SUMMARY REPORT -->
                <div v-if="currentReportType === 'sales-summary'" class="space-y-4">
                  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-indigo-50 dark:bg-indigo-950/40 p-4 rounded-xl border border-indigo-100 dark:border-indigo-900/60">
                      <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Total Orders</span>
                      <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ reportData.total_sales || 0 }}</div>
                    </div>
                    <div class="bg-emerald-50 dark:bg-emerald-950/40 p-4 rounded-xl border border-emerald-100 dark:border-emerald-900/60">
                      <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Gross Revenue</span>
                      <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ formatCurrency(reportData.total_revenue) }}</div>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-950/40 p-4 rounded-xl border border-blue-100 dark:border-blue-900/60">
                      <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Total Collected</span>
                      <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ formatCurrency(reportData.total_paid) }}</div>
                    </div>
                    <div class="bg-rose-50 dark:bg-rose-950/40 p-4 rounded-xl border border-rose-100 dark:border-rose-900/60">
                      <span class="text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Outstanding Due</span>
                      <div class="text-2xl font-bold text-rose-700 dark:text-rose-400 mt-1">{{ formatCurrency(reportData.total_due) }}</div>
                    </div>
                  </div>

                  <!-- Daily Breakdown Table -->
                  <div class="border border-slate-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-xs bg-white dark:bg-zinc-900">
                    <div class="px-4 py-3 bg-slate-50 dark:bg-zinc-800/80 border-b border-slate-200 dark:border-zinc-800 font-bold text-xs uppercase tracking-wider text-slate-700 dark:text-zinc-300 flex justify-between items-center">
                      <span>Daily Revenue & Collection History</span>
                      <span class="text-slate-400 font-normal">AOV: {{ formatCurrency(reportData.average_sale) }}</span>
                    </div>
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-zinc-800 text-xs">
                      <thead class="bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 font-bold">
                        <tr>
                          <th class="px-4 py-2.5 text-left">Date</th>
                          <th class="px-4 py-2.5 text-center">Orders</th>
                          <th class="px-4 py-2.5 text-right">Gross Revenue</th>
                          <th class="px-4 py-2.5 text-right">Collected Paid</th>
                          <th class="px-4 py-2.5 text-right">Outstanding Due</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        <tr v-for="row in (reportData.daily_breakdown || [])" :key="row.date" class="hover:bg-slate-50 dark:hover:bg-zinc-800/50">
                          <td class="px-4 py-2.5 font-mono text-slate-700 dark:text-zinc-300">{{ row.date }}</td>
                          <td class="px-4 py-2.5 text-center font-bold text-slate-900 dark:text-white">{{ row.total_sales }}</td>
                          <td class="px-4 py-2.5 text-right font-mono font-semibold text-slate-900 dark:text-white">{{ formatCurrency(row.total_revenue) }}</td>
                          <td class="px-4 py-2.5 text-right font-mono text-emerald-600 dark:text-emerald-400">{{ formatCurrency(row.total_paid) }}</td>
                          <td class="px-4 py-2.5 text-right font-mono text-rose-600 dark:text-rose-400">{{ formatCurrency(row.total_due) }}</td>
                        </tr>
                        <tr v-if="!(reportData.daily_breakdown || []).length">
                          <td colspan="5" class="px-4 py-6 text-center text-slate-400 italic">No sales records found for selected period</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- 5B. MONTHLY REVENUE REPORT -->
                <div v-else-if="currentReportType === 'monthly-revenue'" class="space-y-4">
                  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-indigo-50 dark:bg-indigo-950/40 p-4 rounded-xl border border-indigo-100 dark:border-indigo-900/60">
                      <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Annual Orders</span>
                      <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ reportData.total_sales || 0 }}</div>
                    </div>
                    <div class="bg-emerald-50 dark:bg-emerald-950/40 p-4 rounded-xl border border-emerald-100 dark:border-emerald-900/60">
                      <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Annual Revenue</span>
                      <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ formatCurrency(reportData.total_revenue) }}</div>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-950/40 p-4 rounded-xl border border-blue-100 dark:border-blue-900/60">
                      <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Annual Collected</span>
                      <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ formatCurrency(reportData.total_paid) }}</div>
                    </div>
                    <div class="bg-rose-50 dark:bg-rose-950/40 p-4 rounded-xl border border-rose-100 dark:border-rose-900/60">
                      <span class="text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Annual Receivables</span>
                      <div class="text-2xl font-bold text-rose-700 dark:text-rose-400 mt-1">{{ formatCurrency(reportData.total_due) }}</div>
                    </div>
                  </div>

                  <div class="border border-slate-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-xs bg-white dark:bg-zinc-900">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-zinc-800 text-xs">
                      <thead class="bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 font-bold">
                        <tr>
                          <th class="px-4 py-2.5 text-left">Month</th>
                          <th class="px-4 py-2.5 text-center">Orders</th>
                          <th class="px-4 py-2.5 text-right">Gross Revenue</th>
                          <th class="px-4 py-2.5 text-right">Collected Paid</th>
                          <th class="px-4 py-2.5 text-right">Outstanding Due</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        <tr v-for="row in (reportData.monthly_breakdown || [])" :key="row.month" class="hover:bg-slate-50 dark:hover:bg-zinc-800/50">
                          <td class="px-4 py-2.5 font-bold text-slate-800 dark:text-zinc-200">{{ row.month_name }}</td>
                          <td class="px-4 py-2.5 text-center font-bold text-slate-900 dark:text-white">{{ row.total_sales }}</td>
                          <td class="px-4 py-2.5 text-right font-mono font-semibold text-slate-900 dark:text-white">{{ formatCurrency(row.total_revenue) }}</td>
                          <td class="px-4 py-2.5 text-right font-mono text-emerald-600 dark:text-emerald-400">{{ formatCurrency(row.total_paid) }}</td>
                          <td class="px-4 py-2.5 text-right font-mono text-rose-600 dark:text-rose-400">{{ formatCurrency(row.total_due) }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- 5C. TOP SELLING PRODUCTS -->
                <div v-else-if="currentReportType === 'top-products'" class="space-y-4">
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-indigo-50 dark:bg-indigo-950/40 p-4 rounded-xl border border-indigo-100 dark:border-indigo-900/60">
                      <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Total Units Sold</span>
                      <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ reportData.total_units_sold || 0 }}</div>
                    </div>
                    <div class="bg-emerald-50 dark:bg-emerald-950/40 p-4 rounded-xl border border-emerald-100 dark:border-emerald-900/60">
                      <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Total Generated Revenue</span>
                      <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ formatCurrency(reportData.total_revenue) }}</div>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-950/40 p-4 rounded-xl border border-purple-100 dark:border-purple-900/60">
                      <span class="text-xs font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-wider">Total Net Margin Profit</span>
                      <div class="text-2xl font-bold text-purple-950 dark:text-purple-100 mt-1">{{ formatCurrency(reportData.total_profit) }}</div>
                    </div>
                  </div>

                  <div class="border border-slate-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-xs bg-white dark:bg-zinc-900">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-zinc-800 text-xs">
                      <thead class="bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 font-bold">
                        <tr>
                          <th class="px-4 py-2.5 text-left">SKU</th>
                          <th class="px-4 py-2.5 text-left">Product Name</th>
                          <th class="px-4 py-2.5 text-left">Category</th>
                          <th class="px-4 py-2.5 text-center">Units Sold</th>
                          <th class="px-4 py-2.5 text-right">Revenue</th>
                          <th class="px-4 py-2.5 text-right">Net Profit</th>
                          <th class="px-4 py-2.5 text-center">Margin %</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        <tr v-for="p in (reportData.products || [])" :key="p.id" class="hover:bg-slate-50 dark:hover:bg-zinc-800/50">
                          <td class="px-4 py-2.5 font-mono text-slate-600 dark:text-zinc-400">{{ p.sku }}</td>
                          <td class="px-4 py-2.5 font-bold text-slate-900 dark:text-white">{{ p.product_name }}</td>
                          <td class="px-4 py-2.5 text-slate-500 dark:text-zinc-400">{{ p.category_name }}</td>
                          <td class="px-4 py-2.5 text-center font-bold text-slate-900 dark:text-white">{{ p.total_quantity }}</td>
                          <td class="px-4 py-2.5 text-right font-mono font-semibold text-slate-900 dark:text-white">{{ formatCurrency(p.total_revenue) }}</td>
                          <td class="px-4 py-2.5 text-right font-mono text-emerald-600 dark:text-emerald-400 font-semibold">{{ formatCurrency(p.net_profit) }}</td>
                          <td class="px-4 py-2.5 text-center font-bold text-indigo-600 dark:text-indigo-400">{{ p.profit_margin_percent }}%</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- 5D. CUSTOMER SALES ANALYSIS -->
                <div v-else-if="currentReportType === 'customer-analysis'" class="space-y-4">
                  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-indigo-50 dark:bg-indigo-950/40 p-4 rounded-xl border border-indigo-100 dark:border-indigo-900/60">
                      <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Active Customers</span>
                      <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ reportData.active_customers || 0 }}</div>
                    </div>
                    <div class="bg-emerald-50 dark:bg-emerald-950/40 p-4 rounded-xl border border-emerald-100 dark:border-emerald-900/60">
                      <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Total Sales</span>
                      <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ formatCurrency(reportData.total_spent) }}</div>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-950/40 p-4 rounded-xl border border-blue-100 dark:border-blue-900/60">
                      <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Total Paid</span>
                      <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ formatCurrency(reportData.total_paid) }}</div>
                    </div>
                    <div class="bg-rose-50 dark:bg-rose-950/40 p-4 rounded-xl border border-rose-100 dark:border-rose-900/60">
                      <span class="text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Total Receivables</span>
                      <div class="text-2xl font-bold text-rose-700 dark:text-rose-400 mt-1">{{ formatCurrency(reportData.total_due) }}</div>
                    </div>
                  </div>

                  <div class="border border-slate-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-xs bg-white dark:bg-zinc-900">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-zinc-800 text-xs">
                      <thead class="bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 font-bold">
                        <tr>
                          <th class="px-4 py-2.5 text-left">Code</th>
                          <th class="px-4 py-2.5 text-left">Customer Name</th>
                          <th class="px-4 py-2.5 text-center">Orders</th>
                          <th class="px-4 py-2.5 text-right">Total Spent</th>
                          <th class="px-4 py-2.5 text-right">Total Paid</th>
                          <th class="px-4 py-2.5 text-right">Due Balance</th>
                          <th class="px-4 py-2.5 text-right">Avg Order</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        <tr v-for="c in (reportData.customers || [])" :key="c.id" class="hover:bg-slate-50 dark:hover:bg-zinc-800/50">
                          <td class="px-4 py-2.5 font-mono text-slate-600 dark:text-zinc-400">{{ c.customer_code || 'CUST-' + c.id }}</td>
                          <td class="px-4 py-2.5 font-bold text-slate-900 dark:text-white">{{ c.customer_name }}</td>
                          <td class="px-4 py-2.5 text-center font-bold text-slate-900 dark:text-white">{{ c.total_purchases }}</td>
                          <td class="px-4 py-2.5 text-right font-mono font-semibold text-slate-900 dark:text-white">{{ formatCurrency(c.total_spent) }}</td>
                          <td class="px-4 py-2.5 text-right font-mono text-emerald-600 dark:text-emerald-400">{{ formatCurrency(c.total_paid) }}</td>
                          <td class="px-4 py-2.5 text-right font-mono text-rose-600 dark:text-rose-400 font-semibold">{{ formatCurrency(c.total_due) }}</td>
                          <td class="px-4 py-2.5 text-right font-mono text-slate-600 dark:text-zinc-400">{{ formatCurrency(c.average_purchase) }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- 5D-2. INACTIVE CUSTOMERS REPORT -->
                <div v-else-if="currentReportType === 'inactive-customers'" class="space-y-4">
                  <!-- Custom Filter Controls Bar -->
                  <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-3 bg-slate-50 dark:bg-zinc-800/60 rounded-2xl border border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center space-x-3 w-full sm:w-auto">
                      <div class="relative w-full sm:w-64">
                        <input
                          v-model="inactiveCustomerSearch"
                          @input="refreshCurrentReport"
                          type="text"
                          placeholder="Search customer, phone..."
                          class="w-full pl-9 pr-3 py-1.5 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-slate-200"
                        />
                        <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                      </div>
                      <select
                        v-model="inactiveCustomerStatus"
                        @change="refreshCurrentReport"
                        class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs py-1.5 px-3 focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-slate-200"
                      >
                        <option value="all">All Accounts</option>
                        <option value="active">Active System Accounts</option>
                        <option value="inactive">Disabled System Accounts</option>
                      </select>
                    </div>

                    <!-- Pending Payments Checkbox Filter -->
                    <label class="inline-flex items-center space-x-2 cursor-pointer bg-white dark:bg-zinc-900 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-zinc-700 shadow-2xs hover:border-amber-400 transition-all select-none">
                      <input
                        type="checkbox"
                        v-model="pendingPaymentsOnlyFilter"
                        @change="refreshCurrentReport"
                        class="w-4 h-4 text-amber-600 rounded border-slate-300 dark:border-zinc-700 focus:ring-amber-500 accent-amber-600 cursor-pointer"
                      />
                      <span class="text-xs font-bold text-slate-700 dark:text-zinc-200 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        Pending Payments Only (Due > 0)
                      </span>
                    </label>
                  </div>

                  <!-- KPI Cards -->
                  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-indigo-50 dark:bg-indigo-950/40 p-4 rounded-xl border border-indigo-100 dark:border-indigo-900/60">
                      <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Inactive Customers</span>
                      <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ reportData.total_inactive_customers || 0 }}</div>
                    </div>
                    <div class="bg-rose-50 dark:bg-rose-950/40 p-4 rounded-xl border border-rose-100 dark:border-rose-900/60">
                      <span class="text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Total Pending Payments</span>
                      <div class="text-2xl font-bold text-rose-700 dark:text-rose-400 mt-1">{{ formatCurrency(reportData.total_pending_payments) }}</div>
                    </div>
                    <div class="bg-amber-50 dark:bg-amber-950/40 p-4 rounded-xl border border-amber-100 dark:border-amber-900/60">
                      <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">With Pending Balance</span>
                      <div class="text-2xl font-bold text-amber-950 dark:text-amber-100 mt-1">{{ reportData.customers_with_pending || 0 }} Customers</div>
                    </div>
                    <div class="bg-slate-100 dark:bg-zinc-800/60 p-4 rounded-xl border border-slate-200 dark:border-zinc-700">
                      <span class="text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">Disabled System Accounts</span>
                      <div class="text-2xl font-bold text-slate-700 dark:text-zinc-200 mt-1">{{ reportData.system_inactive_count || 0 }} Accounts</div>
                    </div>
                  </div>

                  <!-- Data Table -->
                  <div class="border border-slate-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-xs bg-white dark:bg-zinc-900">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-zinc-800 text-xs">
                      <thead class="bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 font-bold">
                        <tr>
                          <th class="px-4 py-2.5 text-left">Code</th>
                          <th class="px-4 py-2.5 text-left">Customer Name</th>
                          <th class="px-4 py-2.5 text-left">Contact Info</th>
                          <th class="px-4 py-2.5 text-center">Account Status</th>
                          <th class="px-4 py-2.5 text-center">Last Invoice Date</th>
                          <th class="px-4 py-2.5 text-right">Last Invoice Amount</th>
                          <th class="px-4 py-2.5 text-right">Total Lifetime Spent</th>
                          <th class="px-4 py-2.5 text-right">Pending Due Balance</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        <tr v-for="c in (reportData.customers || [])" :key="c.id" class="hover:bg-slate-50 dark:hover:bg-zinc-800/50">
                          <td class="px-4 py-2.5 font-mono text-slate-600 dark:text-zinc-400">{{ c.customer_code || 'CUST-' + c.id }}</td>
                          <td class="px-4 py-2.5 font-bold text-slate-900 dark:text-white">{{ c.customer_name }}</td>
                          <td class="px-4 py-2.5 text-slate-500 dark:text-zinc-400">{{ c.phone || c.email || '-' }}</td>
                          <td class="px-4 py-2.5 text-center">
                            <span
                              :class="{
                                'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300': c.is_active,
                                'bg-slate-100 text-slate-800 dark:bg-zinc-800 dark:text-zinc-300': !c.is_active
                              }"
                              class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider inline-block"
                            >
                              {{ c.is_active ? 'Active' : 'Inactive' }}
                            </span>
                          </td>
                          <td class="px-4 py-2.5 text-center font-mono text-slate-600 dark:text-zinc-400">
                            {{ c.last_sale_date || 'Never' }}
                          </td>
                          <td class="px-4 py-2.5 text-right font-mono text-slate-700 dark:text-zinc-300">{{ formatCurrency(c.last_sale_amount) }}</td>
                          <td class="px-4 py-2.5 text-right font-mono font-semibold text-slate-900 dark:text-white">{{ formatCurrency(c.total_spent) }}</td>
                          <td class="px-4 py-2.5 text-right font-mono font-bold" :class="c.due_amount > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-zinc-400'">
                            {{ formatCurrency(c.due_amount) }}
                          </td>
                        </tr>
                        <tr v-if="!(reportData.customers || []).length">
                          <td colspan="8" class="px-4 py-8 text-center text-slate-500 dark:text-zinc-400 font-medium italic">
                            No inactive customers found matching the selected criteria.
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- 5E. CURRENT STOCK LEVELS (INVENTORY SUMMARY) -->
                <div v-else-if="currentReportType === 'inventory-summary'" class="space-y-4">
                  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-indigo-50 dark:bg-indigo-950/40 p-4 rounded-xl border border-indigo-100 dark:border-indigo-900/60">
                      <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Total Products</span>
                      <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ reportData.total_products || 0 }} ({{ reportData.total_units || 0 }} units)</div>
                    </div>
                    <div class="bg-emerald-50 dark:bg-emerald-950/40 p-4 rounded-xl border border-emerald-100 dark:border-emerald-900/60">
                      <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Asset Cost Value</span>
                      <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ formatCurrency(reportData.total_inventory_cost_value) }}</div>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-950/40 p-4 rounded-xl border border-purple-100 dark:border-purple-900/60">
                      <span class="text-xs font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-wider">Retail Valuation</span>
                      <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ formatCurrency(reportData.total_inventory_retail_value) }}</div>
                    </div>
                    <div class="bg-amber-50 dark:bg-amber-950/40 p-4 rounded-xl border border-amber-100 dark:border-amber-900/60">
                      <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Low/Out Stock Items</span>
                      <div class="text-2xl font-bold text-amber-700 dark:text-amber-400 mt-1">{{ (reportData.low_stock_items || 0) + (reportData.out_of_stock_items || 0) }}</div>
                    </div>
                  </div>

                  <div class="border border-slate-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-xs bg-white dark:bg-zinc-900">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-zinc-800 text-xs">
                      <thead class="bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 font-bold">
                        <tr>
                          <th class="px-4 py-2.5 text-left">SKU</th>
                          <th class="px-4 py-2.5 text-left">Product Name</th>
                          <th class="px-4 py-2.5 text-left">Category</th>
                          <th class="px-4 py-2.5 text-center">Stock Level</th>
                          <th class="px-4 py-2.5 text-center">Min Buffer</th>
                          <th class="px-4 py-2.5 text-right">Cost Price</th>
                          <th class="px-4 py-2.5 text-right">Cost Value</th>
                          <th class="px-4 py-2.5 text-center">Status</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        <tr v-for="p in (reportData.products || [])" :key="p.id" class="hover:bg-slate-50 dark:hover:bg-zinc-800/50">
                          <td class="px-4 py-2.5 font-mono text-slate-600 dark:text-zinc-400">{{ p.sku }}</td>
                          <td class="px-4 py-2.5 font-bold text-slate-900 dark:text-white">{{ p.product_name }}</td>
                          <td class="px-4 py-2.5 text-slate-500 dark:text-zinc-400">{{ p.category_name }}</td>
                          <td class="px-4 py-2.5 text-center font-bold text-slate-900 dark:text-white">{{ p.stock_quantity }}</td>
                          <td class="px-4 py-2.5 text-center text-slate-400">{{ p.min_stock_level }}</td>
                          <td class="px-4 py-2.5 text-right font-mono text-slate-700 dark:text-zinc-300">{{ formatCurrency(p.cost_price) }}</td>
                          <td class="px-4 py-2.5 text-right font-mono font-semibold text-slate-900 dark:text-white">{{ formatCurrency(p.inventory_cost_value) }}</td>
                          <td class="px-4 py-2.5 text-center">
                            <span
                              :class="{
                                'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300': p.stock_status === 'In Stock',
                                'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300': p.stock_status === 'Low Stock',
                                'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300': p.stock_status === 'Out of Stock'
                              }"
                              class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider inline-block"
                            >
                              {{ p.stock_status }}
                            </span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- 5F. LOW STOCK ALERT -->
                <div v-else-if="currentReportType === 'low-stock'" class="space-y-4">
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-rose-50 dark:bg-rose-950/40 p-4 rounded-xl border border-rose-100 dark:border-rose-900/60">
                      <span class="text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Alert SKUs Count</span>
                      <div class="text-2xl font-bold text-rose-700 dark:text-rose-400 mt-1">{{ reportData.alert_count || 0 }} Items</div>
                    </div>
                    <div class="bg-amber-50 dark:bg-amber-950/40 p-4 rounded-xl border border-amber-100 dark:border-amber-900/60">
                      <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Total Deficit Units</span>
                      <div class="text-2xl font-bold text-amber-950 dark:text-amber-100 mt-1">{{ reportData.total_deficit_units || 0 }} Units</div>
                    </div>
                    <div class="bg-indigo-50 dark:bg-indigo-950/40 p-4 rounded-xl border border-indigo-100 dark:border-indigo-900/60">
                      <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Estimated Reorder Cost</span>
                      <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ formatCurrency(reportData.total_reorder_cost) }}</div>
                    </div>
                  </div>

                  <div class="border border-slate-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-xs bg-white dark:bg-zinc-900">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-zinc-800 text-xs">
                      <thead class="bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 font-bold">
                        <tr>
                          <th class="px-4 py-2.5 text-left">SKU</th>
                          <th class="px-4 py-2.5 text-left">Product Name</th>
                          <th class="px-4 py-2.5 text-left">Category</th>
                          <th class="px-4 py-2.5 text-center">Current Qty</th>
                          <th class="px-4 py-2.5 text-center">Min Level</th>
                          <th class="px-4 py-2.5 text-center">Deficit Qty</th>
                          <th class="px-4 py-2.5 text-right">Unit Cost</th>
                          <th class="px-4 py-2.5 text-right">Reorder Budget</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        <tr v-for="p in (reportData.products || [])" :key="p.id" class="hover:bg-slate-50 dark:hover:bg-zinc-800/50">
                          <td class="px-4 py-2.5 font-mono text-slate-600 dark:text-zinc-400">{{ p.sku }}</td>
                          <td class="px-4 py-2.5 font-bold text-slate-900 dark:text-white">{{ p.product_name }}</td>
                          <td class="px-4 py-2.5 text-slate-500 dark:text-zinc-400">{{ p.category_name }}</td>
                          <td class="px-4 py-2.5 text-center font-bold text-rose-600 dark:text-rose-400">{{ p.stock_quantity }}</td>
                          <td class="px-4 py-2.5 text-center text-slate-400">{{ p.min_stock_level }}</td>
                          <td class="px-4 py-2.5 text-center font-bold text-amber-600 dark:text-amber-400">+{{ p.deficit_quantity }}</td>
                          <td class="px-4 py-2.5 text-right font-mono text-slate-700 dark:text-zinc-300">{{ formatCurrency(p.unit_cost) }}</td>
                          <td class="px-4 py-2.5 text-right font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ formatCurrency(p.reorder_cost) }}</td>
                        </tr>
                        <tr v-if="!(reportData.products || []).length">
                          <td colspan="8" class="px-4 py-6 text-center text-emerald-600 dark:text-emerald-400 font-bold italic">All stock levels are optimal. No low stock alerts!</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- 5G. INVENTORY VALUATION -->
                <div v-else-if="currentReportType === 'inventory-valuation'" class="space-y-4">
                  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-indigo-50 dark:bg-indigo-950/40 p-4 rounded-xl border border-indigo-100 dark:border-indigo-900/60">
                      <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Asset Cost Value</span>
                      <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ formatCurrency(reportData.total_cost_value) }}</div>
                    </div>
                    <div class="bg-emerald-50 dark:bg-emerald-950/40 p-4 rounded-xl border border-emerald-100 dark:border-emerald-900/60">
                      <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Retail Potential</span>
                      <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ formatCurrency(reportData.total_retail_value) }}</div>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-950/40 p-4 rounded-xl border border-purple-100 dark:border-purple-900/60">
                      <span class="text-xs font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-wider">Potential Gross Profit</span>
                      <div class="text-2xl font-bold text-purple-950 dark:text-purple-100 mt-1">{{ formatCurrency(reportData.potential_profit) }}</div>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-950/40 p-4 rounded-xl border border-blue-100 dark:border-blue-900/60">
                      <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Margin Potential</span>
                      <div class="text-2xl font-bold text-blue-700 dark:text-blue-300 mt-1">{{ reportData.overall_margin_percent || 0 }}%</div>
                    </div>
                  </div>

                  <div class="border border-slate-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-xs bg-white dark:bg-zinc-900">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-zinc-800 text-xs">
                      <thead class="bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 font-bold">
                        <tr>
                          <th class="px-4 py-2.5 text-left">Category</th>
                          <th class="px-4 py-2.5 text-center">SKU Count</th>
                          <th class="px-4 py-2.5 text-center">Stock Units</th>
                          <th class="px-4 py-2.5 text-right">Cost Value</th>
                          <th class="px-4 py-2.5 text-right">Retail Potential</th>
                          <th class="px-4 py-2.5 text-right">Potential Profit</th>
                          <th class="px-4 py-2.5 text-center">Margin %</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        <tr v-for="c in (reportData.categories || [])" :key="c.category_name" class="hover:bg-slate-50 dark:hover:bg-zinc-800/50">
                          <td class="px-4 py-2.5 font-bold text-slate-900 dark:text-white">{{ c.category_name }}</td>
                          <td class="px-4 py-2.5 text-center text-slate-600 dark:text-zinc-400">{{ c.product_count }}</td>
                          <td class="px-4 py-2.5 text-center font-bold text-slate-900 dark:text-white">{{ c.total_quantity }}</td>
                          <td class="px-4 py-2.5 text-right font-mono font-semibold text-slate-900 dark:text-white">{{ formatCurrency(c.cost_value) }}</td>
                          <td class="px-4 py-2.5 text-right font-mono text-emerald-600 dark:text-emerald-400 font-semibold">{{ formatCurrency(c.retail_value) }}</td>
                          <td class="px-4 py-2.5 text-right font-mono text-purple-600 dark:text-purple-400 font-semibold">{{ formatCurrency(c.potential_profit) }}</td>
                          <td class="px-4 py-2.5 text-center font-bold text-indigo-600 dark:text-indigo-400">{{ c.margin_percent }}%</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- 5H. STOCK MOVEMENTS -->
                <div v-else-if="currentReportType === 'stock-movement'" class="space-y-4">
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-indigo-50 dark:bg-indigo-950/40 p-4 rounded-xl border border-indigo-100 dark:border-indigo-900/60">
                      <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Total Movements</span>
                      <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ reportData.total_movements || 0 }} Logged</div>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-950/40 p-4 rounded-xl border border-blue-100 dark:border-blue-900/60">
                      <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Total Units Moved</span>
                      <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ reportData.total_units_moved || 0 }} Units</div>
                    </div>
                    <div class="bg-emerald-50 dark:bg-emerald-950/40 p-4 rounded-xl border border-emerald-100 dark:border-emerald-900/60">
                      <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Total Value Moved</span>
                      <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ formatCurrency(reportData.total_value_moved) }}</div>
                    </div>
                  </div>

                  <div class="border border-slate-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-xs bg-white dark:bg-zinc-900">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-zinc-800 text-xs">
                      <thead class="bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 font-bold">
                        <tr>
                          <th class="px-4 py-2.5 text-left">Date & Time</th>
                          <th class="px-4 py-2.5 text-left">Ref / Invoice</th>
                          <th class="px-4 py-2.5 text-left">Product Name</th>
                          <th class="px-4 py-2.5 text-left">SKU</th>
                          <th class="px-4 py-2.5 text-center">Movement Type</th>
                          <th class="px-4 py-2.5 text-center">Qty</th>
                          <th class="px-4 py-2.5 text-right">Unit Price</th>
                          <th class="px-4 py-2.5 text-right">Total Value</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        <tr v-for="(m, idx) in (reportData.movements || [])" :key="idx" class="hover:bg-slate-50 dark:hover:bg-zinc-800/50">
                          <td class="px-4 py-2.5 font-mono text-slate-500 dark:text-zinc-400">{{ new Date(m.created_at).toLocaleString() }}</td>
                          <td class="px-4 py-2.5 font-mono font-bold text-slate-900 dark:text-white">{{ m.reference_no }}</td>
                          <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-zinc-200">{{ m.product_name }}</td>
                          <td class="px-4 py-2.5 font-mono text-slate-600 dark:text-zinc-400">{{ m.sku }}</td>
                          <td class="px-4 py-2.5 text-center">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-300">
                              {{ m.movement_type }}
                            </span>
                          </td>
                          <td class="px-4 py-2.5 text-center font-bold text-slate-900 dark:text-white">{{ m.quantity }}</td>
                          <td class="px-4 py-2.5 text-right font-mono text-slate-700 dark:text-zinc-300">{{ formatCurrency(m.unit_price) }}</td>
                          <td class="px-4 py-2.5 text-right font-mono font-semibold text-slate-900 dark:text-white">{{ formatCurrency(m.total_value) }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

              </div>
            </div>
          </div>

          <!-- Modal Footer with Actions -->
          <div class="px-6 py-4 border-t border-slate-200 dark:border-zinc-800 bg-slate-50/80 dark:bg-zinc-900/90 flex items-center justify-between">
            <div class="text-xs text-slate-400">
              Generated: <span class="font-mono text-slate-600 dark:text-zinc-300">{{ new Date().toLocaleString() }}</span>
            </div>
            <div class="flex items-center space-x-3">
              <button
                @click="downloadPdfReport"
                :disabled="downloadingPdf"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-xs transition-all flex items-center space-x-1.5"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>{{ downloadingPdf ? 'Generating PDF...' : 'Download PDF' }}</span>
              </button>
              <button
                @click="exportReport"
                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all flex items-center space-x-1.5"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Export CSV</span>
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useCurrencyStore } from '@/stores/currency';
import FloatingDateRangePicker from '@/components/common/FloatingDateRangePicker.vue';
import axios from 'axios';

const currencyStore = useCurrencyStore();

// Reactive Stats
const totalProducts = ref(7);
const lowStockCount = ref(2);
const totalCustomers = ref(6);

// Modal & Loading State
const showReportModal = ref(false);
const loadingReport = ref(false);
const generatingReport = ref(false);
const reportData = ref(null);
const reportError = ref(null);
const currentReportType = ref('');
const currentReportTitle = ref('');

// Date Filtering State
const todayStr = new Date().toISOString().split('T')[0];
const firstOfMonthStr = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];

const filterStartDate = ref(firstOfMonthStr);
const filterEndDate = ref(todayStr);
const filterAsOfDate = ref(todayStr);
const activePreset = ref('this-month');

// Trial Balance Search & Filter
const tbSearchQuery = ref('');
const tbAccountTypeFilter = ref('');

// Inactive Customer Filter State
const pendingPaymentsOnlyFilter = ref(false);
const inactiveCustomerSearch = ref('');
const inactiveCustomerStatus = ref('all');

const isFinancialReport = (type) => {
  return ['profit-loss', 'balance-sheet', 'cash-flow', 'trial-balance'].includes(type);
};

const onDateRangePickerChange = (range) => {
  if (range) {
    filterStartDate.value = range.start_date || '';
    filterEndDate.value = range.end_date || '';
    filterAsOfDate.value = range.end_date || range.start_date || todayStr;
  }
  if (currentReportType.value) {
    fetchReportData(currentReportType.value);
  }
};

const setPreset = (preset) => {
  activePreset.value = preset;
  const now = new Date();

  if (preset === 'this-month') {
    filterStartDate.value = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
    filterEndDate.value = todayStr;
    filterAsOfDate.value = todayStr;
  } else if (preset === 'this-quarter') {
    const qMonth = Math.floor(now.getMonth() / 3) * 3;
    filterStartDate.value = new Date(now.getFullYear(), qMonth, 1).toISOString().split('T')[0];
    filterEndDate.value = todayStr;
    filterAsOfDate.value = todayStr;
  } else if (preset === 'this-year') {
    filterStartDate.value = `${now.getFullYear()}-01-01`;
    filterEndDate.value = todayStr;
    filterAsOfDate.value = todayStr;
  } else if (preset === 'all-time') {
    filterStartDate.value = '2000-01-01';
    filterEndDate.value = todayStr;
    filterAsOfDate.value = todayStr;
  }

  fetchReportData(currentReportType.value);
};

const switchFinancialTab = (type) => {
  currentReportType.value = type;
  currentReportTitle.value = getReportTitle(type);
  fetchReportData(type);
};

const openFinancialReport = (type) => {
  currentReportType.value = type;
  currentReportTitle.value = getReportTitle(type);
  showReportModal.value = true;
  fetchReportData(type);
};

const generateReport = (type) => {
  if (isFinancialReport(type)) {
    openFinancialReport(type);
  } else {
    currentReportType.value = type;
    currentReportTitle.value = getReportTitle(type);
    showReportModal.value = true;
    fetchReportData(type);
  }
};

const fetchReportData = async (reportType) => {
  loadingReport.value = true;
  reportData.value = null;
  reportError.value = null;

  try {
    let endpoint = '';
    const params = {};

    if (['profit-loss', 'cash-flow', 'sales-summary', 'monthly-revenue', 'top-products', 'customer-analysis', 'inactive-customers', 'stock-movement'].includes(reportType)) {
      params.start_date = filterStartDate.value;
      params.end_date = filterEndDate.value;
    } else if (reportType === 'balance-sheet' || reportType === 'trial-balance') {
      params.as_of_date = filterAsOfDate.value;
    }

    switch (reportType) {
      case 'profit-loss':
        endpoint = '/api/reports/financial/profit-loss';
        break;
      case 'balance-sheet':
        endpoint = '/api/reports/financial/balance-sheet';
        break;
      case 'cash-flow':
        endpoint = '/api/reports/financial/cash-flow';
        break;
      case 'trial-balance':
        endpoint = '/api/reports/financial/trial-balance';
        break;
      case 'sales-summary':
        endpoint = '/api/reports/sales/summary';
        break;
      case 'monthly-revenue':
        endpoint = '/api/reports/sales/monthly-revenue';
        break;
      case 'top-products':
        endpoint = '/api/reports/sales/top-products';
        break;
      case 'customer-analysis':
        endpoint = '/api/reports/sales/customer-analysis';
        break;
      case 'inactive-customers':
        endpoint = '/api/reports/sales/inactive-customers';
        params.pending_payments_only = pendingPaymentsOnlyFilter.value ? 1 : 0;
        params.search = inactiveCustomerSearch.value || '';
        params.status = inactiveCustomerStatus.value || 'all';
        break;
      case 'inventory-summary':
        endpoint = '/api/reports/inventory/summary';
        break;
      case 'low-stock':
        endpoint = '/api/reports/inventory/low-stock';
        break;
      case 'inventory-valuation':
        endpoint = '/api/reports/inventory/valuation';
        break;
      case 'stock-movement':
        endpoint = '/api/reports/inventory/stock-movement';
        break;
      default:
        throw new Error('Invalid report requested');
    }

    const response = await axios.get(endpoint, { params });
    reportData.value = response.data;
  } catch (error) {
    console.error('Error fetching report:', error);
    reportError.value = error.response?.data?.message || error.message || 'Failed to fetch financial report';
  } finally {
    loadingReport.value = false;
  }
};

const applyDateFilter = () => {
  activePreset.value = 'custom';
  fetchReportData(currentReportType.value);
};

const refreshCurrentReport = () => {
  fetchReportData(currentReportType.value);
};

const filteredTrialBalanceAccounts = computed(() => {
  if (!reportData.value || !reportData.value.accounts) return [];
  return reportData.value.accounts.filter(acc => {
    const matchesSearch = !tbSearchQuery.value ||
      acc.account_name.toLowerCase().includes(tbSearchQuery.value.toLowerCase()) ||
      acc.account_code.includes(tbSearchQuery.value);

    const matchesType = !tbAccountTypeFilter.value || acc.account_type === tbAccountTypeFilter.value;

    return matchesSearch && matchesType;
  });
});

const closeReportModal = () => {
  showReportModal.value = false;
  reportData.value = null;
  reportError.value = null;
};

const getReportTitle = (type) => {
  const titles = {
    'profit-loss': 'Profit & Loss Statement (Income Statement)',
    'balance-sheet': 'Balance Sheet (Statement of Financial Position)',
    'trial-balance': 'Trial Balance (General Ledger Audit)',
    'cash-flow': 'Cash Flow Statement (IAS 7 Indirect)',
    'sales-summary': 'Daily Sales Summary',
    'monthly-revenue': 'Monthly Revenue Report',
    'top-products': 'Top Selling Products',
    'customer-analysis': 'Customer Sales Analysis',
    'inactive-customers': 'Inactive Customer Sales & Due Payments Report',
    'inventory-summary': 'Current Stock Levels',
    'low-stock': 'Low Stock Alert Report',
    'inventory-valuation': 'Inventory Valuation',
    'stock-movement': 'Stock Movement History'
  };
  return titles[type] || 'Report';
};

const formatCurrency = (amount) => {
  return currencyStore.formatPrice(amount || 0);
};

const downloadingPdf = ref(false);

const downloadPdfReport = async () => {
  if (!currentReportType.value) return;
  downloadingPdf.value = true;
  try {
    const params = new URLSearchParams({
      type: currentReportType.value,
      start_date: filterStartDate.value || '',
      end_date: filterEndDate.value || '',
      as_of_date: filterAsOfDate.value || filterEndDate.value || '',
      pending_payments_only: pendingPaymentsOnlyFilter.value ? '1' : '0',
      search: inactiveCustomerSearch.value || '',
      status: inactiveCustomerStatus.value || 'all',
    });

    const url = `/api/reports/pdf/export?${params.toString()}`;
    const link = document.createElement('a');
    link.href = url;
    link.target = '_blank';
    link.setAttribute('download', `${currentReportType.value}_report_${filterEndDate.value || 'statement'}.pdf`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (err) {
    console.error('PDF generation error:', err);
  } finally {
    setTimeout(() => {
      downloadingPdf.value = false;
    }, 1200);
  }
};

const exportReport = () => {
  if (!reportData.value) return;

  let csvContent = '';
  if (currentReportType.value === 'inactive-customers' && reportData.value?.customers) {
    const headers = ['Customer Code', 'Customer Name', 'Phone', 'Email', 'Status', 'Last Invoice Date', 'Last Invoice Amount', 'Total Lifetime Spent', 'Pending Due Balance'];
    csvContent = headers.join(',') + '\n';
    reportData.value.customers.forEach(c => {
      csvContent += `"${c.customer_code}","${c.customer_name}","${c.phone || ''}","${c.email || ''}","${c.is_active ? 'Active' : 'Inactive'}","${c.last_sale_date || 'Never'}","${c.last_sale_amount}","${c.total_spent}","${c.due_amount}"\n`;
    });
  } else if (Array.isArray(reportData.value)) {
    if (reportData.value.length > 0) {
      const headers = Object.keys(reportData.value[0]);
      csvContent = headers.join(',') + '\n';
      reportData.value.forEach(row => {
        const values = headers.map(h => typeof row[h] === 'string' ? `"${row[h]}"` : row[h]);
        csvContent += values.join(',') + '\n';
      });
    }
  } else {
    csvContent = 'Metric,Value\n';
    Object.entries(reportData.value).forEach(([k, v]) => {
      if (typeof v !== 'object') {
        csvContent += `"${k}","${v}"\n`;
      }
    });
  }

  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = `${currentReportType.value}-${filterEndDate.value}.csv`;
  link.click();
};

onMounted(() => {
  // Initial setup if needed
});
</script>
