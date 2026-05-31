<template>
  <div class="max-w-4xl mx-auto space-y-6 p-6 bg-slate-50 border border-slate-200 rounded-3xl shadow-sm text-slate-800 min-h-screen">
    
    <!-- Fallback Stage Selector -->
    <WorkspaceJobSelector 
      v-if="!route.params.id" 
      stage="settlement" 
      title="Select Invoice for Cashier Settlement" 
      @selected="handleJobSelected"
    />

    <div v-else-if="loading" class="animate-pulse space-y-6">
      <div class="h-8 bg-slate-200 rounded w-1/4"></div>
      <div class="h-96 bg-slate-200 rounded"></div>
    </div>

    <JobDetailsLayout v-else-if="jobCard" :jobCard="jobCard" :activeStage="8">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-slate-200 pb-5">
        <div class="flex items-center space-x-4">
          <div v-if="invoice">
            <h1 class="text-2xl font-black tracking-tight text-slate-800 uppercase">Invoice Cashier Settlement</h1>
            <p class="text-xs text-slate-500 mt-1">Invoice: {{ invoice.invoice_number }}</p>
          </div>
        </div>
      </div>

    <div v-if="loading" class="animate-pulse space-y-6">
      <div class="h-8 bg-slate-200 rounded w-1/4"></div>
      <div class="h-96 bg-slate-200 rounded"></div>
    </div>

    <div v-else-if="invoice" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      
      <!-- Invoice Summary Deck (Left Column) -->
      <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm space-y-4 flex flex-col justify-between">
        <div>
          <h3 class="text-xs font-black uppercase tracking-wider text-slate-500 mb-3 border-b border-slate-200 pb-2">Billed Details</h3>
          
          <dl class="space-y-3 text-xs text-slate-600">
            <div class="flex justify-between">
              <dt>Subtotal (Parts & Labor):</dt>
              <dd class="font-bold text-slate-850 font-mono">৳{{ invoice.parts_total + invoice.service_total }}</dd>
            </div>
            <div class="flex justify-between" v-if="invoice.discount > 0">
              <dt>Applied Discount:</dt>
              <dd class="font-bold text-rose-650 font-mono">-৳{{ invoice.discount }}</dd>
            </div>
            <div class="flex justify-between" v-if="invoice.vat > 0">
              <dt>VAT (15%):</dt>
              <dd class="font-bold text-slate-800 font-mono">+৳{{ invoice.vat }}</dd>
            </div>
            <div class="flex justify-between border-t border-slate-200 pt-2.5 text-sm">
              <dt class="font-extrabold text-slate-900">Grand Billed Total:</dt>
              <dd class="font-black text-indigo-650 font-mono">৳{{ invoice.grand_total }}</dd>
            </div>
          </dl>
        </div>

        <!-- Pre-paid ledger credit offset details -->
        <div class="bg-slate-50 border border-slate-200 p-4 rounded-xl space-y-3 text-xs">
          <div class="flex justify-between items-center text-[10px] font-black uppercase text-indigo-650">
            <span>Advance Credit Ledger Balance</span>
            <span v-if="ledgerBalance < 0" class="bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded text-[8px]">Surplus Deposit Available</span>
          </div>

          <div class="flex justify-between text-slate-600">
            <span>Customer Ledger Balance:</span>
            <span :class="ledgerBalance < 0 ? 'text-emerald-650' : 'text-rose-650'" class="font-bold font-mono">
              ৳{{ formatCurrency(Math.abs(ledgerBalance)) }} {{ ledgerBalance < 0 ? 'Surplus Credit' : 'Owed' }}
            </span>
          </div>

          <div class="flex justify-between text-slate-600 border-t border-slate-200 pt-2">
            <span>Already Adjusted from Credit:</span>
            <span class="font-bold font-mono text-slate-800">৳{{ invoice.paid_amount }}</span>
          </div>

          <div class="flex justify-between items-center text-sm font-extrabold border-t border-slate-200 pt-2.5 bg-slate-100 p-2 rounded">
            <span class="text-slate-800">Remaining Balance Due:</span>
            <span class="text-rose-650 font-mono font-black">৳{{ invoice.due_amount }}</span>
          </div>
        </div>
      </div>

      <!-- Payment form (Right Column) -->
      <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm flex flex-col justify-between">
        <div>
          <h3 class="text-xs font-black uppercase tracking-wider text-indigo-650 mb-4">Record Receipt Payment</h3>
          
          <div v-if="invoice.due_amount <= 0" class="text-center py-12 space-y-3">
            <span class="text-3xl text-emerald-600">✔</span>
            <h4 class="font-bold text-emerald-700">Invoice Fully Settled</h4>
            <p class="text-xs text-slate-500">No balance is due on this invoice. Settle keys handover and dispatch delivery details.</p>
            <router-link
              :to="{ name: 'workshop.qc-delivery' }"
              class="mt-4 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition block text-center"
            >
              Proceed to Handover Checkout
            </router-link>
          </div>

          <form v-else @submit.prevent="recordPayment" class="space-y-4 text-xs">
            <div>
              <label class="block text-[10px] text-slate-500 mb-1">Receipt Payment Amount (BDT) *</label>
              <input
                v-model.number="paymentForm.amount"
                type="number"
                step="0.01"
                required
                :max="invoice.due_amount"
                class="w-full text-sm bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-slate-800 focus:bg-white font-mono"
              />
            </div>

            <div>
              <label class="block text-[10px] text-slate-500 mb-1">Payment Method Option *</label>
              <div class="grid grid-cols-2 gap-2">
                <button
                  type="button"
                  v-for="method in ['cash', 'card', 'bkash', 'nagad']"
                  :key="method"
                  @click="paymentForm.method = method"
                  class="py-2.5 rounded-lg border font-bold text-[10px] uppercase tracking-wider transition"
                  :class="paymentForm.method === method ? 'bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-slate-50 border-slate-200 text-slate-500'"
                >
                  {{ method }}
                </button>
              </div>
            </div>

            <div>
              <label class="block text-[10px] text-slate-500 mb-1">Payment Notes / Txn ID</label>
              <input
                v-model="paymentForm.notes"
                type="text"
                placeholder="e.g. Card transaction receipt ID, bKash TRX ID..."
                class="w-full text-xs bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-slate-800 focus:bg-white"
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
        <div class="pt-4 border-t border-slate-200 flex justify-end">
          <router-link
            :to="{ name: 'workshop.hub' }"
            class="px-4 py-2 border border-slate-200 rounded-lg text-xs font-bold text-slate-650 hover:bg-slate-100 transition"
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
import { ref, reactive, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';
import JobDetailsLayout from '../../components/workshop/JobDetailsLayout.vue';
import WorkspaceJobSelector from '../../components/workshop/WorkspaceJobSelector.vue';

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

const handleJobSelected = (id) => {
  router.push({ name: 'workshop.settlement', params: { id } });
};

const fetchDetails = async () => {
  if (!route.params.id) {
    invoice.value = null;
    jobCard.value = null;
    loading.value = false;
    return;
  }
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
    router.push({ name: 'workshop.settlement' });
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

watch(() => route.params.id, (newId) => {
  if (newId) {
    fetchDetails();
  } else {
    invoice.value = null;
    jobCard.value = null;
    loading.value = false;
  }
});

onMounted(() => {
  if (route.params.id) {
    fetchDetails();
  } else {
    loading.value = false;
  }
});
</script>
