<template>
  <div class="space-y-4 max-w-full">
    <!-- Top Header & Metrics Bar (Black & White Theme) -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Payments Out</h1>
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">Manage all outgoing supplier and operational payment transactions</p>
      </div>

      <!-- Quick Metrics Summary Cards -->
      <div class="flex flex-wrap items-center gap-2.5">
        <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl px-3.5 py-2 shadow-xs flex items-center gap-2.5">
          <div class="p-1.5 bg-slate-900 text-white rounded-lg">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Payments</div>
            <div class="text-xs font-bold text-slate-900 dark:text-slate-100">{{ pagination.total || 0 }}</div>
          </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl px-3.5 py-2 shadow-xs flex items-center gap-2.5">
          <div class="p-1.5 bg-slate-100 dark:bg-zinc-800 text-slate-900 dark:text-white rounded-lg">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Completed Paid</div>
            <div class="text-xs font-bold text-slate-900 dark:text-slate-100">{{ paidCount }}</div>
          </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl px-3.5 py-2 shadow-xs flex items-center gap-2.5">
          <div class="p-1.5 bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-400 rounded-lg">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pending / Draft</div>
            <div class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ pendingCount }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- DataTable Container (Black and White System Theme) -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-sm overflow-hidden">
      <DataTable
        title="Payments Out Transactions"
        subtitle="Manage all outgoing supplier and operational payment entries"
        :columns="tableColumns"
        :data="payments"
        :loading="loading"
        :pagination="pagination"
        :initial-search="searchQuery"
        :initial-per-page="perPage"
        :default-per-page="15"
        storage-key="payments-table-state"
        empty-message="No payments found"
        empty-sub-message="Get started by creating your first outgoing payment."
        @search="handleTableSearch"
        @sort="handleSort"
        @page-change="handlePageChange"
        @per-page-change="handlePerPageChange"
      >
        <!-- Primary Action Buttons (+ New Payment & Filter Drawer) -->
        <template #actions>
          <div class="flex items-center space-x-2">
            <button
              @click="showFilterDrawer = true"
              class="px-3.5 py-2.5 border border-slate-200 dark:border-zinc-700 bg-slate-50 hover:bg-slate-100 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-800 dark:text-zinc-200 font-semibold rounded-xl text-xs shadow-xs transition-all flex items-center gap-2 cursor-pointer relative"
              title="Open Filters Drawer"
            >
              <svg class="w-4 h-4 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
              </svg>
              <span>Filter</span>
              <span v-if="activeFilterCount > 0" class="w-5 h-5 bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 rounded-full text-[10px] font-bold flex items-center justify-center">
                {{ activeFilterCount }}
              </span>
            </button>

            <button
              v-if="authStore.hasPermission('payments.create')"
              @click="showCreateModal = true"
              class="bg-slate-900 hover:bg-black active:scale-[0.98] text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white font-semibold rounded-xl text-xs px-4 py-2.5 transition-all shadow-sm cursor-pointer"
            >
              <span>+ New Payment</span>
            </button>
          </div>
        </template>

        <!-- Column: Payment Number -->
        <template #column-payment_number="{ item }">
          <button
            @click="viewPayment(item)"
            class="text-xs font-semibold text-slate-900 dark:text-slate-100 hover:text-black dark:hover:text-white hover:underline transition-colors inline-flex items-center gap-1.5 text-left cursor-pointer"
          >
            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            <span>{{ item.payment_number }}</span>
          </button>
        </template>

        <!-- Column: Payment Date -->
        <template #column-payment_date="{ item }">
          <div class="text-xs text-slate-700 dark:text-slate-300 font-medium inline-flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>{{ formatDate(item.payment_date) }}</span>
          </div>
        </template>

        <!-- Column: Payment Type (Black & White Monochromatic Badges) -->
        <template #column-payment_type="{ item }">
          <span :class="getPaymentTypeBadgeClass(item.payment_type)" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold rounded-lg border">
            <span class="w-1.5 h-1.5 rounded-full" :class="getPaymentTypeDotClass(item.payment_type)"></span>
            {{ getPaymentTypeDisplay(item.payment_type) }}
          </span>
        </template>

        <!-- Column: Payee -->
        <template #column-payee_name="{ item }">
          <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-full bg-slate-900 text-white flex items-center justify-center text-[10px] font-bold shrink-0 shadow-xs">
              {{ (item.payee_name || 'P').charAt(0).toUpperCase() }}
            </div>
            <span class="text-xs font-semibold text-slate-900 dark:text-slate-100 truncate max-w-[200px]">{{ item.payee_name || 'N/A' }}</span>
          </div>
        </template>

        <!-- Column: Amount -->
        <template #column-amount="{ item }">
          <span class="text-xs font-semibold text-slate-900 dark:text-slate-100 text-right block">
            {{ currencySymbol }}{{ formatAmount(item.amount) }}
          </span>
        </template>

        <!-- Column: Status (Black & White High-Contrast Status Badges) -->
        <template #column-status="{ item }">
          <span :class="getStatusBadgeClass(item.status)" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold rounded-full border">
            <span class="w-1.5 h-1.5 rounded-full" :class="getStatusDotClass(item.status)"></span>
            {{ formatStatusText(item.status) }}
          </span>
        </template>

        <!-- Column: Actions -->
        <template #column-actions="{ item }">
          <div class="flex items-center justify-center gap-1.5">
            <!-- 1. DRAFT STATUS -->
            <template v-if="item.status === 'draft'">
              <!-- Eye Icon (View Only) -->
              <button
                @click="viewPayment(item)"
                class="p-1.5 text-slate-500 hover:text-black dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-all cursor-pointer"
                title="View Payment Details"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>

              <!-- Edit Icon (Pencil) -->
              <button
                @click="editPayment(item)"
                class="p-1.5 text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-all cursor-pointer"
                title="Edit Payment"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>

              <!-- Cancel Icon (Screenshot 1: Circle with Cross) -->
              <button
                @click="openTransitionModal(item, 'cancelled')"
                class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-all cursor-pointer"
                title="Cancel Payment"
              >
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                  <circle cx="12" cy="12" r="9" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 9l-6 6M9 9l6 6" />
                </svg>
              </button>

              <!-- Processing Icon (Hourglass with circular arrows) -->
              <button
                @click="openTransitionModal(item, 'process')"
                class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 rounded-lg transition-all cursor-pointer"
                title="Move to Processing"
              >
                <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M18.5 7A9 9 0 0 0 7 4.5" />
                  <polyline points="18.5 3.5 18.5 7.5 14.5 7.5" />
                  <path d="M5.5 17A9 9 0 0 0 17 19.5" />
                  <polyline points="5.5 20.5 5.5 16.5 9.5 16.5" />
                  <path d="M9 8h6" />
                  <path d="M9 16h6" />
                  <path d="M9.5 8v2.2l2.5 1.8-2.5 1.8V16" />
                  <path d="M14.5 8v2.2l-2.5 1.8 2.5 1.8V16" />
                </svg>
              </button>
            </template>

            <!-- 2. PROCESS / PROCESSING STATUS -->
            <template v-else-if="item.status === 'process' || item.status === 'processing'">
              <!-- Eye Icon (View Only) -->
              <button
                @click="viewPayment(item)"
                class="p-1.5 text-slate-500 hover:text-black dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-all cursor-pointer"
                title="View Payment Details"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>

              <!-- Pending Icon (Screenshot 3: Clock Timer with Arrow) -->
              <button
                @click="openTransitionModal(item, 'pending')"
                class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-lg transition-all cursor-pointer"
                title="Move to Pending Review"
              >
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.5a9.5 9.5 0 1 0 9.5 9.5" stroke-dasharray="4 2" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5.5l3.5 2" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21.5 8l-2-2.5 3-1" />
                </svg>
              </button>
            </template>

            <!-- 3. PENDING REVIEW STATUS -->
            <template v-else-if="item.status === 'pending'">
              <!-- Eye Icon (View Only) -->
              <button
                @click="viewPayment(item)"
                class="p-1.5 text-slate-500 hover:text-black dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-all cursor-pointer"
                title="View Payment Details"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>

              <!-- Rejected Icon (Screenshot 4: Prohibited / Slash Circle) -->
              <button
                @click="openTransitionModal(item, 'rejected')"
                class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-all cursor-pointer"
                title="Reject Payment"
              >
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                  <circle cx="12" cy="12" r="9" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5.6 5.6l12.8 12.8" />
                </svg>
              </button>

              <!-- Completed Icon (Screenshot 5: Checkmark Circle) -->
              <button
                @click="openTransitionModal(item, 'completed')"
                class="p-1.5 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 rounded-lg transition-all cursor-pointer"
                title="Mark as Paid"
              >
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                  <circle cx="12" cy="12" r="9" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 12.5l2.5 2.5 5-5" />
                </svg>
              </button>
            </template>

            <!-- 4. FINAL STATES (REJECTED, COMPLETED, CANCELLED, PAID, VOID) -->
            <template v-else>
              <!-- Eye Icon (View Only) -->
              <button
                @click="viewPayment(item)"
                class="p-1.5 text-slate-500 hover:text-black dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-all cursor-pointer"
                title="View Payment Details"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
            </template>
          </div>
        </template>
      </DataTable>
    </div>

    <!-- Modals -->
    <PaymentFormModal
      v-if="showCreateModal"
      :show="showCreateModal"
      @close="showCreateModal = false"
      @saved="handlePaymentSaved"
    />

    <PaymentFormModal
      v-if="showEditModal && selectedPayment"
      :show="showEditModal"
      :payment="selectedPayment"
      @close="showEditModal = false"
      @saved="handlePaymentSaved"
    />

    <PaymentViewModal
      v-if="showViewModal && selectedPayment"
      :show="showViewModal"
      :payment="selectedPayment"
      @close="showViewModal = false"
      @edit="editPayment"
      @approve="approvePayment"
      @mark-as-paid="markAsPaid"
      @delete="deletePayment"
    />

    <!-- State Machine Transition Confirmation Modal -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div v-if="showConfirmModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
          <!-- Backdrop -->
          <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showConfirmModal = false"></div>

          <!-- Dialog Box -->
          <div class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-slate-100 dark:border-zinc-800 overflow-hidden p-6 z-10 space-y-4">
            <div class="flex items-start gap-4">
              <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-xs', confirmModalData.iconBgClass]">
                <!-- Process Icon (Hourglass with circular arrows) -->
                <svg v-if="confirmModalData.type === 'process'" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M18.5 7A9 9 0 0 0 7 4.5" />
                  <polyline points="18.5 3.5 18.5 7.5 14.5 7.5" />
                  <path d="M5.5 17A9 9 0 0 0 17 19.5" />
                  <polyline points="5.5 20.5 5.5 16.5 9.5 16.5" />
                  <path d="M9 8h6" />
                  <path d="M9 16h6" />
                  <path d="M9.5 8v2.2l2.5 1.8-2.5 1.8V16" />
                  <path d="M14.5 8v2.2l-2.5 1.8 2.5 1.8V16" />
                </svg>

                <!-- Pending Icon -->
                <svg v-else-if="confirmModalData.type === 'pending'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.5a9.5 9.5 0 1 0 9.5 9.5" stroke-dasharray="4 2" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5.5l3.5 2" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21.5 8l-2-2.5 3-1" />
                </svg>

                <!-- Rejected Icon -->
                <svg v-else-if="confirmModalData.type === 'rejected'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                  <circle cx="12" cy="12" r="9" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5.6 5.6l12.8 12.8" />
                </svg>

                <!-- Completed Icon -->
                <svg v-else-if="confirmModalData.type === 'completed'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                  <circle cx="12" cy="12" r="9" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 12.5l2.5 2.5 5-5" />
                </svg>

                <!-- Cancelled Icon -->
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                  <circle cx="12" cy="12" r="9" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 9l-6 6M9 9l6 6" />
                </svg>
              </div>
              <div class="space-y-1">
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
                  {{ confirmModalData.title }}
                </h3>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">
                  {{ confirmModalData.message }}
                </p>
                <p class="text-xs text-slate-600 dark:text-zinc-300 leading-relaxed font-medium">
                  {{ confirmModalData.subtext }}
                </p>
              </div>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-zinc-800/80 flex items-center justify-end gap-2.5">
              <button
                type="button"
                @click="showConfirmModal = false"
                :disabled="isUpdatingStatus"
                class="px-4 py-2 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all cursor-pointer"
              >
                Cancel
              </button>
              <button
                type="button"
                @click="confirmStatusTransition"
                :disabled="isUpdatingStatus"
                :class="['px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-xs cursor-pointer inline-flex items-center gap-1.5', confirmModalData.confirmButtonClass]"
              >
                <svg v-if="isUpdatingStatus" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span>{{ confirmModalData.confirmButtonText }}</span>
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Top-Right Success Toast Notification -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-[-10px] translate-x-4"
        enter-to-class="opacity-100 translate-y-0 translate-x-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0 translate-x-0"
        leave-to-class="opacity-0 translate-y-[-10px] translate-x-4"
      >
        <div v-if="toastNotification.show" class="fixed top-5 right-5 z-[99999] max-w-md bg-slate-900 dark:bg-zinc-900 text-white rounded-2xl p-4 shadow-2xl border border-slate-700/80 dark:border-zinc-700 flex items-center gap-3">
          <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">System Notification</p>
            <p class="text-xs text-white font-semibold truncate">{{ toastNotification.message }}</p>
          </div>
          <button @click="toastNotification.show = false" class="text-slate-400 hover:text-white p-1 rounded-lg cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </Transition>
    </Teleport>

    <!-- Slide-over Filter Drawer -->
    <Teleport to="body">
      <div v-if="showFilterDrawer" class="fixed inset-0 z-50 overflow-hidden">
        <!-- Backdrop -->
        <div 
          @click="showFilterDrawer = false" 
          class="fixed inset-0 bg-slate-900/60 dark:bg-black/70 backdrop-blur-xs transition-opacity duration-300"
        ></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
          <div class="w-screen max-w-md bg-white dark:bg-zinc-900 shadow-2xl flex flex-col justify-between border-l border-slate-200 dark:border-zinc-800 transform transition-all duration-300">
            <!-- Drawer Header -->
            <div class="p-5 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between bg-slate-50/50 dark:bg-zinc-900/50">
              <div class="flex items-center gap-2.5">
                <div class="p-2 bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900 rounded-xl shadow-xs">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                  </svg>
                </div>
                <div>
                  <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Filter Payments</h2>
                  <p class="text-[11px] font-medium text-slate-500 dark:text-zinc-400">Refine payment out results</p>
                </div>
              </div>

              <button
                @click="showFilterDrawer = false"
                class="p-2 text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-xl transition-all cursor-pointer"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Drawer Content (Filters) -->
            <div class="p-6 space-y-5 overflow-y-auto flex-1">
              <!-- Date Range Filter -->
              <div class="space-y-1.5">
                <FloatingDateRangePicker
                  v-model:start-date="filters.start_date"
                  v-model:end-date="filters.end_date"
                  @change="fetchPayments(1)"
                />
              </div>

              <!-- Payment Type Filter -->
              <div class="space-y-1.5">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">Payment Type</label>
                <div class="relative">
                  <select
                    v-model="filters.payment_type"
                    @change="fetchPayments(1)"
                    class="w-full appearance-none bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl px-3.5 py-2.5 pr-9 text-xs font-medium text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs cursor-pointer"
                  >
                    <option value="" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">All Payment Types</option>
                    <option value="supplier_payment">Supplier Payment</option>
                    <option value="expense_payment">Expense Payment</option>
                    <option value="salary_payment">Salary Payment</option>
                    <option value="sale_return_payment">Sale Return Payment</option>
                    <option value="purchase_invoice_payment">Purchase Invoice Payment</option>
                    <option value="other_payment">Other Payment</option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Bank Account Filter -->
              <div class="space-y-1.5">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">Account</label>
                <div class="relative">
                  <select
                    v-model="filters.bank_account_id"
                    @change="fetchPayments(1)"
                    class="w-full appearance-none bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl px-3.5 py-2.5 pr-9 text-xs font-medium text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs cursor-pointer"
                  >
                    <option value="" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">All Accounts</option>
                    <option v-for="acc in bankAccountOptions" :key="acc.id" :value="acc.id">
                      {{ formatBankAccountLabel(acc) }}{{ formatAccountBalanceText(acc) }}
                    </option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Status Filter -->
              <div class="space-y-1.5">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">Status</label>
                <div class="relative">
                  <select
                    v-model="filters.status"
                    @change="fetchPayments(1)"
                    class="w-full appearance-none bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700/80 rounded-xl px-3.5 py-2.5 pr-9 text-xs font-medium text-slate-900 dark:text-slate-100 hover:bg-white dark:hover:bg-zinc-800 focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all shadow-xs cursor-pointer"
                  >
                    <option value="" class="bg-white text-slate-900 dark:bg-zinc-900 dark:text-slate-100">All Statuses</option>
                    <option value="draft">Draft</option>
                    <option value="pending">Pending</option>
                    <option value="process">Process</option>
                    <option value="rejected">Rejected</option>
                    <option value="completed">Paid</option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </div>
                </div>
              </div>
            </div>

            <!-- Drawer Footer -->
            <div class="p-4 border-t border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50 flex items-center justify-between gap-3">
              <button
                @click="resetFilters"
                :disabled="!hasActiveFilters"
                class="px-4 py-2.5 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-bold text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all disabled:opacity-50 cursor-pointer"
              >
                Reset Filters
              </button>
              <button
                @click="showFilterDrawer = false"
                class="px-5 py-2.5 bg-slate-900 hover:bg-black active:scale-[0.98] text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white font-bold rounded-xl text-xs shadow-xs transition-all cursor-pointer"
              >
                Apply & Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useCurrencyStore } from '@/stores/currency';
import axios from 'axios';
import DataTable from '@/components/common/DataTable.vue';
import FloatingDateRangePicker from '@/components/common/FloatingDateRangePicker.vue';
import PaymentFormModal from './PaymentFormModal.vue';
import PaymentViewModal from './PaymentViewModal.vue';
import { downloadAttachmentFile } from '@/utils/downloadAttachment';

const handleDownloadAttachment = (paymentId, index = 0, fileName = 'attachment', directUrl = '') => {
  const url = directUrl || `/api/payments/${paymentId}/download-attachment?index=${index}`;
  downloadAttachmentFile(url, fileName);
};

const authStore = useAuthStore();
const currencyStore = useCurrencyStore();

const currencySymbol = computed(() => {
  return currencyStore.symbol || authStore.user?.company?.currency_symbol || authStore.user?.company?.currency || '$';
});

// Reactive data
const loading = ref(false);
const payments = ref([]);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
});
const searchQuery = ref('');
const perPage = ref(15);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showViewModal = ref(false);
const showFilterDrawer = ref(false);
const selectedPayment = ref(null);

const bankAccountOptions = ref([]);

// State Machine Transition Modal & Toast state
const showConfirmModal = ref(false);
const isUpdatingStatus = ref(false);
const confirmModalData = reactive({
  paymentId: null,
  targetStatus: '',
  title: '',
  message: 'ARE YOU SURE YOU WANT TO DO THIS?',
  subtext: '',
  confirmButtonText: 'Confirm',
  confirmButtonClass: 'bg-slate-900 hover:bg-black text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white',
  iconBgClass: 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900',
  type: 'process',
});

const toastNotification = reactive({
  show: false,
  message: '',
});

let toastTimer = null;
const triggerToast = (msg) => {
  if (toastTimer) clearTimeout(toastTimer);
  toastNotification.message = msg;
  toastNotification.show = true;
  toastTimer = setTimeout(() => {
    toastNotification.show = false;
  }, 4000);
};

// Filters
const filters = reactive({
  payment_type: '',
  bank_account_id: '',
  status: '',
  start_date: '',
  end_date: '',
});

// Computed Helper Flags
const hasActiveFilters = computed(() => {
  return filters.payment_type !== '' || filters.bank_account_id !== '' || filters.status !== '' || filters.start_date !== '' || filters.end_date !== '';
});

const activeFilterCount = computed(() => {
  let count = 0;
  if (filters.payment_type) count++;
  if (filters.bank_account_id) count++;
  if (filters.status) count++;
  if (filters.start_date || filters.end_date) count++;
  return count;
});

const paidCount = computed(() => {
  return payments.value.filter(p => p.status === 'paid').length;
});

const pendingCount = computed(() => {
  return payments.value.filter(p => p.status === 'pending' || p.status === 'draft').length;
});

// Table columns configuration
const tableColumns = computed(() => [
  {
    key: 'payment_number',
    label: 'Payment Number',
    sortable: true,
    align: 'left',
  },
  {
    key: 'payment_date',
    label: 'Date',
    sortable: true,
    align: 'left'
  },
  {
    key: 'payment_type',
    label: 'Payment Type',
    sortable: true,
    align: 'left'
  },
  {
    key: 'payee_name',
    label: 'Payee / Recipient',
    sortable: true,
    align: 'left'
  },
  {
    key: 'amount',
    label: `Amount (${currencySymbol.value})`,
    sortable: true,
    align: 'right'
  },
  {
    key: 'status',
    label: 'Status',
    sortable: true,
    align: 'center'
  },
  {
    key: 'actions',
    label: 'Actions',
    sortable: false,
    align: 'center'
  }
]);

// Methods
const fetchPayments = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      search: searchQuery.value,
      ...filters,
    };

    // Remove empty filters
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null || params[key] === undefined) {
        delete params[key];
      }
    });

    const response = await axios.get('/api/payments', { params });
    payments.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
      from: response.data.from,
      to: response.data.to,
    };
  } catch (error) {
    console.error('Error loading payments:', error);
  } finally {
    loading.value = false;
  }
};

const resetFilters = () => {
  filters.payment_type = '';
  filters.bank_account_id = '';
  filters.status = '';
  filters.start_date = '';
  filters.end_date = '';
  fetchPayments(1);
};

// DataTable event handlers
const handleTableSearch = (query) => {
  searchQuery.value = query;
  fetchPayments(1);
};

const handleSort = () => {
  fetchPayments(1);
};

const handlePageChange = (page) => {
  fetchPayments(page);
};

const handlePerPageChange = (newPerPage) => {
  pagination.value.per_page = newPerPage;
  perPage.value = newPerPage;
  fetchPayments(1);
};

// Payment actions
const viewPayment = async (payment) => {
  selectedPayment.value = payment;
  showViewModal.value = true;
  try {
    const res = await axios.get(`/api/payments/${payment.id}`);
    if (res.data) {
      selectedPayment.value = res.data;
    }
  } catch (e) {
    console.error('Failed to fetch full payment details', e);
  }
};

const editPayment = (payment) => {
  if (payment.status !== 'draft') {
    viewPayment(payment);
    return;
  }
  selectedPayment.value = payment;
  showEditModal.value = true;
  showViewModal.value = false;
};

const openTransitionModal = (item, targetStatus) => {
  confirmModalData.paymentId = item.id;
  confirmModalData.targetStatus = targetStatus;
  confirmModalData.message = 'Are you sure you want to do this?';
  confirmModalData.confirmButtonClass = 'bg-slate-900 hover:bg-black text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white';
  confirmModalData.iconBgClass = 'bg-slate-900 text-white dark:bg-zinc-100 dark:text-zinc-900';

  if (targetStatus === 'process') {
    confirmModalData.title = 'Move to Processing';
    confirmModalData.subtext = 'Once moved to Processing, this voucher can no longer be edited or cancelled.';
    confirmModalData.confirmButtonText = 'Move to Processing';
    confirmModalData.type = 'process';
  } else if (targetStatus === 'pending') {
    confirmModalData.title = 'Move to Pending Review';
    confirmModalData.subtext = 'Moving to Pending review. This action cannot be undone.';
    confirmModalData.confirmButtonText = 'Move to Pending';
    confirmModalData.type = 'pending';
  } else if (targetStatus === 'rejected') {
    confirmModalData.title = 'Reject Payment';
    confirmModalData.subtext = 'Rejecting this payment. Once rejected, status cannot be changed.';
    confirmModalData.confirmButtonText = 'Reject Payment';
    confirmModalData.type = 'rejected';
  } else if (targetStatus === 'completed') {
    confirmModalData.title = 'Mark as Paid';
    confirmModalData.subtext = 'Marking payment as Paid. This transaction will be finalized and locked.';
    confirmModalData.confirmButtonText = 'Mark as Paid';
    confirmModalData.type = 'completed';
  } else if (targetStatus === 'cancelled') {
    confirmModalData.title = 'Cancel Payment Voucher';
    confirmModalData.subtext = 'Cancelling this payment voucher. Once cancelled, this action cannot be undone.';
    confirmModalData.confirmButtonText = 'Cancel Payment';
    confirmModalData.type = 'cancelled';
  }

  showConfirmModal.value = true;
};

const confirmStatusTransition = async () => {
  if (!confirmModalData.paymentId || !confirmModalData.targetStatus) return;
  isUpdatingStatus.value = true;
  try {
    const response = await axios.patch(`/api/payments-out/${confirmModalData.paymentId}/status`, {
      status: confirmModalData.targetStatus
    });

    const successMessage = response.data.message || `Status updated to ${confirmModalData.targetStatus} successfully!`;
    showConfirmModal.value = false;
    triggerToast(successMessage);
    await fetchPayments();
  } catch (error) {
    console.error('Error updating payment status:', error);
    const errMessage = error.response?.data?.message || 'Failed to update payment status';
    alert(errMessage);
  } finally {
    isUpdatingStatus.value = false;
  }
};

const approvePayment = async (payment) => {
  if (!confirm('Are you sure you want to approve this payment?')) {
    return;
  }

  try {
    const response = await axios.post(`/api/payments/${payment.id}/approve`);
    const index = payments.value.findIndex(p => p.id === payment.id);
    if (index !== -1) {
      payments.value[index] = response.data.payment;
    }
  } catch (error) {
    console.error('Error approving payment:', error);
    alert('Failed to approve payment');
  }
};

const markAsPaid = async (payment) => {
  if (!confirm('Are you sure you want to mark/process this payment as paid?')) {
    return;
  }

  try {
    const response = await axios.post(`/api/payments/${payment.id}/mark-as-paid`);
    const index = payments.value.findIndex(p => p.id === payment.id);
    if (index !== -1) {
      payments.value[index] = response.data.payment;
    } else {
      fetchPayments(pagination.value.current_page);
    }
  } catch (error) {
    console.error('Error marking payment as paid:', error);
    alert(error.response?.data?.message || 'Failed to mark payment as paid');
  }
};

const cancelPayment = async (payment) => {
  if (!confirm(`Are you sure you want to cancel payment ${payment.payment_number || ''}?`)) {
    return;
  }

  try {
    const response = await axios.post(`/api/payments/${payment.id}/cancel`);
    const index = payments.value.findIndex(p => p.id === payment.id);
    if (index !== -1) {
      payments.value[index] = response.data.payment;
    } else {
      fetchPayments(pagination.value.current_page);
    }
  } catch (error) {
    console.error('Error cancelling payment:', error);
    alert(error.response?.data?.message || 'Failed to cancel payment');
  }
};

const deletePayment = async (payment) => {
  if (!confirm('Are you sure you want to delete this payment? This action cannot be undone.')) {
    return;
  }

  try {
    await axios.delete(`/api/payments/${payment.id}`);
    payments.value = payments.value.filter(p => p.id !== payment.id);
  } catch (error) {
    console.error('Error deleting payment:', error);
    alert('Failed to delete payment');
  }
};

const handlePaymentSaved = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  fetchPayments(1);
};

// Utility functions
const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatAmount = (amount) => {
  return parseFloat(amount || 0).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const getPaymentTypeDisplay = (type) => {
  const types = {
    supplier_payment: 'Supplier Payment',
    expense_payment: 'Expense Payment',
    salary_payment: 'Salary Payment',
    sale_return_payment: 'Sale Return Payment',
    purchase_invoice_payment: 'Purchase Invoice Payment',
    other_payment: 'Other Payment',
  };
  return types[type] || type || 'General Payment';
};

const getPaymentTypeBadgeClass = (type) => {
  return 'bg-white text-slate-900 border border-slate-200 dark:bg-black dark:text-white dark:border-zinc-800 shadow-2xs';
};

const getPaymentTypeDotClass = (type) => {
  const dots = {
    supplier_payment: 'bg-emerald-400',
    expense_payment: 'bg-sky-400',
    salary_payment: 'bg-indigo-400',
    sale_return_payment: 'bg-rose-400',
    purchase_invoice_payment: 'bg-violet-400',
  };
  return dots[type] || 'bg-slate-400';
};

const formatStatusText = (status) => {
  if (!status) return 'Unknown';
  const s = String(status).toLowerCase();
  if (s === 'completed' || s === 'paid') return 'Paid';
  if (s === 'process' || s === 'processing') return 'Process';
  if (s === 'rejected' || s === 'void') return 'Rejected';
  if (s === 'cancelled') return 'Cancelled';
  return s.charAt(0).toUpperCase() + s.slice(1);
};

const getStatusBadgeClass = (status) => {
  return 'bg-white text-slate-900 border border-slate-200 dark:bg-black dark:text-white dark:border-zinc-800 shadow-2xs';
};

const getStatusDotClass = (status) => {
  const s = String(status || '').toLowerCase();
  const dots = {
    paid: 'bg-emerald-400',
    completed: 'bg-emerald-400',
    approved: 'bg-blue-400',
    process: 'bg-indigo-500',
    processing: 'bg-indigo-500',
    pending: 'bg-amber-400',
    draft: 'bg-slate-400',
    cancelled: 'bg-rose-500',
  };
  return dots[s] || 'bg-slate-400';
};

const formatBankAccountLabel = (acc) => {
  if (!acc) return '';
  if (acc.account_type === 'credit_card') {
    let bankTitle = acc.bank_name || 'Credit Card';
    if (!bankTitle.toLowerCase().includes('credit card')) {
      bankTitle += '-Credit Card';
    }
    return `${acc.account_name || 'Card Holder'} (${bankTitle})`;
  }
  return acc.bank_name && acc.bank_name !== acc.account_name
    ? `${acc.account_name} (${acc.bank_name})`
    : (acc.account_name || acc.bank_name || 'Bank Account');
};

const formatAccountBalanceText = (acc) => {
  if (!acc) return '';
  const rawBal = acc.current_balance !== undefined && acc.current_balance !== null
    ? Number(acc.current_balance)
    : Number(acc.opening_balance || 0);
  const absBal = Math.abs(rawBal).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const formattedBal = rawBal < 0 ? `-${currencySymbol.value}${absBal}` : `${currencySymbol.value}${absBal}`;
  return ` (${formattedBal})`;
};

const fetchPaymentOptions = async () => {
  try {
    const response = await axios.get('/api/payment-options');
    bankAccountOptions.value = response.data.bank_accounts || [];
  } catch (error) {
    console.error('Error loading payment options:', error);
  }
};

// Initialize
onMounted(() => {
  fetchPaymentOptions();
  fetchPayments();

  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('create') === 'true') {
    showCreateModal.value = true;
  }

  if (urlParams.get('status')) {
    filters.status = urlParams.get('status');
  }
});
</script>
