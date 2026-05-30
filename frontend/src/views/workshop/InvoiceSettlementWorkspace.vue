<template>
  <div class="max-w-4xl mx-auto space-y-6 p-6 bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl text-slate-100 min-h-screen">
    <JobDetailsLayout :jobCard="jobCard" :activeStage="8">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-slate-850 pb-5">
        <div class="flex items-center space-x-4">
          <div v-if="invoice">
            <h1 class="text-2xl font-black tracking-tight text-white uppercase">Invoice Cashier Settlement</h1>
            <p class="text-xs text-slate-400 mt-1">Invoice: {{ invoice.invoice_number }}</p>
          </div>
        </div>
      </div>

    <div v-if="loading" class="animate-pulse space-y-6">
      <div class="h-8 bg-slate-800 rounded w-1/4"></div>
      <div class="h-96 bg-slate-800 rounded"></div>
    </div>

    <div v-else-if="invoice" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      
      <!-- Invoice Summary Deck (Left Column) -->
      <div class="bg-slate-950/20 border border-slate-850 p-5 rounded-2xl space-y-4 flex flex-col justify-between">
        <div>
          <h3 class="text-xs font-black uppercase tracking-wider text-slate-450 mb-3 border-b border-slate-850 pb-2">Billed Details</h3>
          
          <dl class="space-y-3 text-xs text-slate-400">
            <div class="flex justify-between">
              <dt>Subtotal (Parts & Labor):</dt>
              <dd class="font-bold text-white font-mono">৳{{ invoice.parts_total + invoice.service_total }}</dd>
            </div>
            <div class="flex justify-between" v-if="invoice.discount > 0">
              <dt>Applied Discount:</dt>
              <dd class="font-bold text-rose-400 font-mono">-৳{{ invoice.discount }}</dd>
            </div>
            <div class="flex justify-between" v-if="invoice.vat > 0">
              <dt>VAT (15%):</dt>
              <dd class="font-bold text-white font-mono">+৳{{ invoice.vat }}</dd>
            </div>
            <div class="flex justify-between border-t border-slate-850 pt-2.5 text-sm">
              <dt class="font-extrabold text-white">Grand Billed Total:</dt>
              <dd class="font-black text-indigo-400 font-mono">৳{{ invoice.grand_total }}</dd>
            </div>
          </dl>
        </div>

        <!-- Pre-paid ledger credit offset details -->
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl space-y-3 text-xs">
          <div class="flex justify-between items-center text-[10px] font-black uppercase text-indigo-400">
            <span>Advance Credit Ledger Balance</span>
            <span v-if="ledgerBalance < 0" class="bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded text-[8px]">Surplus Deposit Available</span>
          </div>

          <div class="flex justify-between text-slate-400">
            <span>Customer Ledger Balance:</span>
            <span :class="ledgerBalance < 0 ? 'text-emerald-450' : 'text-rose-400'" class="font-bold font-mono">
              ৳{{ formatCurrency(Math.abs(ledgerBalance)) }} {{ ledgerBalance < 0 ? 'Surplus Credit' : 'Owed' }}
            </span>
          </div>

          <div class="flex justify-between text-slate-400 border-t border-slate-850 pt-2">
            <span>Already Adjusted from Credit:</span>
            <span class="font-bold font-mono text-slate-200">৳{{ invoice.paid_amount }}</span>
          </div>

          <div class="flex justify-between items-center text-sm font-extrabold border-t border-slate-850 pt-2.5 bg-slate-950/20 p-2 rounded">
            <span class="text-white">Remaining Balance Due:</span>
            <span class="text-rose-500 font-mono font-black">৳{{ invoice.due_amount }}</span>
          </div>
        </div>
      </div>

      <!-- Payment form (Right Column) -->
      <div class="bg-slate-950/40 border border-slate-850 p-5 rounded-2xl shadow-xl flex flex-col justify-between">
        <div>
          <h3 class="text-xs font-black uppercase tracking-wider text-indigo-400 mb-4">Record Receipt Payment</h3>
          
          <div v-if="invoice.due_amount <= 0" class="text-center py-12 space-y-3">
            <span class="text-3xl">✔</span>
            <h4 class="font-bold text-emerald-400">Invoice Fully Settled</h4>
            <p class="text-xs text-slate-500">No balance is due on this invoice. Settle keys handover and dispatch delivery details.</p>
          </div>

          <form v-else @submit.prevent="recordPayment" class="space-y-4 text-xs">
            <div>
              <label class="block text-[10px] text-slate-400 mb-1">Receipt Payment Amount (BDT) *</label>
              <input
                v-model.number="paymentForm.amount"
                type="number"
                step="0.01"
                required
                :max="invoice.due_amount"
                class="w-full text-sm bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white font-mono"
              />
            </div>

            <div>
              <label class="block text-[10px] text-slate-400 mb-1">Payment Method Option *</label>
              <div class="grid grid-cols-2 gap-2">
                <button
                  type="button"
                  v-for="method in ['cash', 'card', 'bkash', 'nagad']"
                  :key="method"
                  @click="paymentForm.method = method"
                  class="py-2.5 rounded-lg border font-bold text-[10px] uppercase tracking-wider transition"
                  :class="paymentForm.method === method ? 'bg-indigo-600/10 border-indigo-500 text-indigo-400' : 'bg-slate-900 border-slate-800 text-slate-400'"
                >
                  {{ method }}
                </button>
              </div>
            </div>

            <div>
              <label class="block text-[10px] text-slate-400 mb-1">Payment Notes / Txn ID</label>
              <input
                v-model="paymentForm.notes"
                type="text"
                placeholder="e.g. Card transaction receipt ID, bKash TRX ID..."
                class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white"
              />
            </div>

            <button
              type="submit"
              :disabled="saving"
              class="w-full py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-black uppercase tracking-wider transition disabled:opacity-50"
            >
              {{ saving ? 'Processing Receipt...' : 'Record Cash Payment & Settle Billed Amount' }}
            </button>
          </form>
        </div>

        <!-- Footer link back -->
        <div class="pt-4 border-t border-slate-850 flex justify-end">
          <router-link
            :to="{ name: 'workshop.hub' }"
            class="px-4 py-2 border border-slate-700 rounded-lg text-xs font-bold text-slate-450 hover:bg-slate-850 transition"
          >
            Back to Hub
          </router-link>
        </div>
      </div>

    </div>
    </JobDetailsLayout>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';
import JobDetailsLayout from '../../components/workshop/JobDetailsLayout.vue';

const route = useRoute();
const router = useRouter();
const toast = useToastStore();

const loading = ref(true);
const saving = ref(false);

const invoice = ref(null);
const jobCard = ref(null);
const ledgerBalance = ref(0); // Credit surplus is negative

const paymentForm = reactive({
  amount: 0,
  method: 'cash',
  notes: ''
});

const fetchDetails = async () => {
  loading.value = true;
  try {
    const invRes = await api.get(`/invoices/${route.params.id}`);
    invoice.value = invRes.data?.data || invRes.data;
    
    paymentForm.amount = invoice.value.due_amount;

    // Fetch Job Card details using job_card_id
    if (invoice.value.job_card_id) {
      const jcRes = await api.get(`/job-cards/${invoice.value.job_card_id}`);
      jobCard.value = jcRes.data?.data || jcRes.data;
    }

    // Fetch customer ledger balance
    if (invoice.value.customer_id) {
      const ledgRes = await api.get(`/customer-ledgers/${invoice.value.customer_id}`);
      const ledger = ledgRes.data?.ledger || ledgRes.data || {};
      ledgerBalance.value = ledger.current_balance || 0;
    }
  } catch (err) {
    toast.error('Failed to load invoice payment details');
    router.push({ name: 'workshop.hub' });
  } finally {
    loading.value = false;
  }
};

const recordPayment = async () => {
  if (paymentForm.amount <= 0 || paymentForm.amount > invoice.value.due_amount) {
    toast.warning('Invalid payment amount recorded.');
    return;
  }

  saving.value = true;
  try {
    // Process invoice payment
    await api.post(`/invoices/${invoice.value.id}/pay`, {
      amount: paymentForm.amount,
      payment_method: paymentForm.method,
      notes: paymentForm.notes
    });

    toast.success('Cash payment recorded successfully. Invoice balance settled.');
    await fetchDetails();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to settle invoice payment.');
  } finally {
    saving.value = false;
  }
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-BD', { style: 'currency', currency: 'BDT' }).format(value || 0);
};

onMounted(() => {
  fetchDetails();
});
</script>
