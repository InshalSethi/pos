<template>
  <div class="p-6 max-w-6xl mx-auto space-y-8 min-h-screen text-slate-800 dark:text-slate-100">
    <!-- Top Header & Breadcrumb -->
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <router-link
          to="/banking/manual-journals"
          class="p-2 rounded-xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 text-slate-500 hover:text-slate-800 dark:hover:text-zinc-200 transition-all shadow-sm"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
        </router-link>
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-zinc-100">New Journal Entry</h1>
          <p class="text-xs text-slate-400 mt-0.5">Create a manual double-entry journal transaction for your general ledger.</p>
        </div>
      </div>
    </div>

    <form @submit.prevent="saveJournal" class="space-y-8">
      <!-- Section 1: General Details -->
      <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm space-y-4">
        <div>
          <h2 class="text-base font-bold text-slate-900 dark:text-zinc-100">General</h2>
          <p class="text-xs text-slate-400 mt-0.5">Here you can enter the general information of the journal entry, such as date, currency, and description.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1">Date *</label>
            <input
              v-model="form.entry_date"
              type="date"
              required
              class="w-full text-sm p-2.5 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
            />
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1">Currency *</label>
            <select
              v-model="form.currency"
              required
              class="w-full text-sm p-2.5 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
            >
              <option value="PKR">Pakistan Rupee (PKR)</option>
              <option value="USD">US Dollar (USD)</option>
              <option value="EUR">Euro (EUR)</option>
              <option value="GBP">British Pound (GBP)</option>
              <option value="AED">UAE Dirham (AED)</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1">Description *</label>
          <textarea
            v-model="form.description"
            required
            placeholder="Enter Description"
            rows="3"
            class="w-full text-sm p-2.5 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
          ></textarea>
        </div>
      </div>

      <!-- Section 2: Lines (Dynamic Table Grid) -->
      <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm space-y-4">
        <div>
          <h2 class="text-base font-bold text-slate-900 dark:text-zinc-100">Lines</h2>
          <p class="text-xs text-slate-400 mt-0.5">Here you can enter the lines of the journal entry such as account, debit, credit, etc.</p>
        </div>

        <div class="overflow-x-auto border border-slate-200 dark:border-zinc-800 rounded-xl">
          <table class="w-full text-sm text-left border-collapse">
            <thead class="bg-slate-50 dark:bg-zinc-950 text-slate-500 dark:text-zinc-400 font-bold border-b border-slate-200 dark:border-zinc-800 text-[11px] uppercase tracking-wider">
              <tr>
                <th class="p-3 w-2/5">ACCOUNT *</th>
                <th class="p-3">NOTE</th>
                <th class="p-3 w-32 text-right">DEBIT</th>
                <th class="p-3 w-32 text-right">CREDIT</th>
                <th class="p-3 w-12 text-center"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-xs">
              <tr v-for="(line, idx) in form.lines" :key="idx" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/30">
                <td class="p-2.5">
                  <select
                    v-model="line.account_id"
                    required
                    class="w-full p-2 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg text-slate-800 dark:text-zinc-200 focus:outline-none"
                  >
                    <option :value="null">- Select Account -</option>
                    <option v-for="acc in coaAccounts" :key="acc.id" :value="acc.id">
                      {{ acc.account_code }} - {{ acc.account_name }} ({{ acc.account_type }})
                    </option>
                  </select>
                </td>
                <td class="p-2.5">
                  <input
                    v-model="line.description"
                    type="text"
                    placeholder="Enter note..."
                    class="w-full p-2 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg text-slate-800 dark:text-zinc-200 focus:outline-none"
                  />
                </td>
                <td class="p-2.5">
                  <input
                    v-model.number="line.debit_amount"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    class="w-full p-2 text-right bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg text-slate-800 dark:text-zinc-200 font-mono font-semibold focus:outline-none"
                    @input="onDebitInput(line)"
                  />
                </td>
                <td class="p-2.5">
                  <input
                    v-model.number="line.credit_amount"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    class="w-full p-2 text-right bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg text-slate-800 dark:text-zinc-200 font-mono font-semibold focus:outline-none"
                    @input="onCreditInput(line)"
                  />
                </td>
                <td class="p-2.5 text-center">
                  <button
                    type="button"
                    @click="removeLine(idx)"
                    :disabled="form.lines.length <= 1"
                    class="p-1.5 text-slate-400 hover:text-rose-500 disabled:opacity-30 transition-colors"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <button
          type="button"
          @click="addLine"
          class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 flex items-center gap-1 cursor-pointer pt-1"
        >
          + Add a Line
        </button>

        <!-- Totals Summary Block -->
        <div class="flex justify-end pt-4">
          <div class="w-72 space-y-3">
            <div class="flex justify-between text-xs text-slate-500 dark:text-zinc-400 font-medium">
              <span>Subtotal Debits / Credits:</span>
              <span class="font-mono font-bold text-slate-800 dark:text-zinc-200">
                {{ form.currency }} {{ formatNumber(totalDebit) }} / {{ formatNumber(totalCredit) }}
              </span>
            </div>

            <div
              :class="isBalanced ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-800'"
              class="flex justify-between items-center p-3.5 font-bold rounded-xl border shadow-sm transition-all"
            >
              <span class="text-xs uppercase tracking-wider">Total</span>
              <span class="text-base font-extrabold font-mono">{{ form.currency }} {{ formatNumber(totalDebit) }}</span>
            </div>

            <p v-if="!isBalanced" class="text-[11px] text-rose-500 font-semibold text-right">
              Debits ({{ formatNumber(totalDebit) }}) &amp; Credits ({{ formatNumber(totalCredit) }}) must match!
            </p>
          </div>
        </div>
      </div>

      <!-- Section 3: Other Metadata -->
      <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm space-y-4">
        <div>
          <h2 class="text-base font-bold text-slate-900 dark:text-zinc-100">Other</h2>
          <p class="text-xs text-slate-400 mt-0.5">Enter a number and reference to keep the journal entry linked to your records.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1">Number *</label>
            <input
              v-model="form.entry_number"
              type="text"
              required
              class="w-full text-sm p-2.5 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
            />
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1">Basis *</label>
            <select
              v-model="form.basis"
              class="w-full text-sm p-2.5 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
            >
              <option value="accrual">Accrual</option>
              <option value="cash">Cash</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1">Reference</label>
          <input
            v-model="form.reference"
            type="text"
            placeholder="Enter Reference (e.g. PO # or Invoice Ref)"
            class="w-full text-sm p-2.5 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl text-slate-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
          />
        </div>

        <!-- File Attachment Box -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1">Attachments</label>
          <div class="border-2 border-dashed border-slate-200 dark:border-zinc-800 rounded-2xl p-6 text-center bg-slate-50/50 dark:bg-zinc-950/50 hover:bg-slate-100/50 dark:hover:bg-zinc-800/30 transition-all cursor-pointer">
            <svg class="w-8 h-8 text-slate-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <p class="text-xs font-semibold text-slate-600 dark:text-zinc-400">Drop files here to upload</p>
            <p class="text-[10px] text-slate-400 mt-0.5">Supports PDF, PNG, JPG, DOCX (Max 10MB)</p>
          </div>
        </div>
      </div>

      <!-- Footer Actions -->
      <div class="flex justify-end gap-3 pt-4 pb-12">
        <router-link
          to="/banking/manual-journals"
          class="px-5 py-2.5 text-xs font-semibold rounded-xl border border-slate-300 dark:border-zinc-800 text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all"
        >
          Cancel
        </router-link>
        <button
          type="submit"
          :disabled="submitting || !isBalanced"
          class="px-6 py-2.5 text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-xl shadow-sm transition-all cursor-pointer"
        >
          {{ submitting ? 'Saving...' : 'Save' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';

export default {
  name: 'CreateManualJournal',
  setup() {
    const router = useRouter();
    const { showToast } = useToast();
    const coaAccounts = ref([]);
    const submitting = ref(false);

    const generateNumber = () => {
      return 'MJE-' + String(Math.floor(10000 + Math.random() * 90000));
    };

    const form = ref({
      entry_date: new Date().toISOString().split('T')[0],
      currency: 'PKR',
      description: '',
      entry_number: generateNumber(),
      basis: 'accrual',
      reference: '',
      lines: [
        { account_id: null, description: '', debit_amount: 0, credit_amount: 0 },
        { account_id: null, description: '', debit_amount: 0, credit_amount: 0 },
      ]
    });

    const fetchCoaAccounts = async () => {
      try {
        const res = await api.accounting.accounts();
        coaAccounts.value = res.data || [];
      } catch (e) {
        showToast('Failed to load Chart of Accounts', 'error');
      }
    };

    const addLine = () => {
      form.value.lines.push({ account_id: null, description: '', debit_amount: 0, credit_amount: 0 });
    };

    const removeLine = (index) => {
      if (form.value.lines.length > 1) {
        form.value.lines.splice(index, 1);
      }
    };

    const onDebitInput = (line) => {
      if (line.debit_amount > 0) {
        line.credit_amount = 0;
      }
    };

    const onCreditInput = (line) => {
      if (line.credit_amount > 0) {
        line.debit_amount = 0;
      }
    };

    const totalDebit = computed(() => {
      return form.value.lines.reduce((sum, l) => sum + (parseFloat(l.debit_amount) || 0), 0);
    });

    const totalCredit = computed(() => {
      return form.value.lines.reduce((sum, l) => sum + (parseFloat(l.credit_amount) || 0), 0);
    });

    const isBalanced = computed(() => {
      return Math.abs(totalDebit.value - totalCredit.value) < 0.01 && totalDebit.value > 0;
    });

    const saveJournal = async () => {
      if (!isBalanced.value) {
        showToast('Total debits and credits must be equal and greater than 0', 'error');
        return;
      }

      submitting.value = true;
      try {
        const payload = {
          entry_number: form.value.entry_number,
          entry_date: form.value.entry_date,
          reference: form.value.reference,
          description: form.value.description,
          entry_type: 'manual',
          status: 'posted',
          total_debit: totalDebit.value,
          total_credit: totalCredit.value,
          lines: form.value.lines.map(l => ({
            account_id: l.account_id,
            description: l.description,
            debit_amount: l.debit_amount,
            credit_amount: l.credit_amount
          }))
        };

        await axios.post('/api/journal-entries', payload);
        showToast('Journal Entry saved & posted successfully');
        router.push('/banking/manual-journals');
      } catch (err) {
        const msg = err.response?.data?.message || 'Failed to save journal entry';
        showToast(msg, 'error');
      } finally {
        submitting.value = false;
      }
    };

    const formatNumber = (val) => {
      return Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    onMounted(() => {
      fetchCoaAccounts();
    });

    return {
      coaAccounts,
      submitting,
      form,
      addLine,
      removeLine,
      onDebitInput,
      onCreditInput,
      totalDebit,
      totalCredit,
      isBalanced,
      saveJournal,
      formatNumber,
    };
  },
};
</script>
