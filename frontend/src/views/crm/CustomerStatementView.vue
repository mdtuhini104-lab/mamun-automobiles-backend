<template>
  <div class="space-y-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header Actions -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <router-link to="/crm/customer-ledger" class="p-2 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
          <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </router-link>
        <div>
          <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Customer Statement</h1>
          <p class="text-sm text-slate-500 mt-1">Detailed transaction history</p>
        </div>
      </div>
      <button @click="printStatement" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl font-semibold shadow-sm hover:bg-indigo-700 transition-colors">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
        Print PDF
      </button>
    </div>

    <div v-if="loading" class="flex justify-center p-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
    </div>

    <div v-else class="space-y-6">
      <!-- Customer Info Card -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div class="flex items-center gap-4">
          <div class="h-16 w-16 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl font-bold uppercase">
            {{ customer?.name?.charAt(0) || 'C' }}
          </div>
          <div>
            <h2 class="text-xl font-bold text-slate-900">{{ customer?.name }}</h2>
            <div class="text-sm text-slate-500 mt-1 flex flex-col sm:flex-row gap-2 sm:gap-4">
              <span class="flex items-center gap-1.5"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg> {{ customer?.phone }}</span>
              <span class="flex items-center gap-1.5"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg> {{ customer?.email }}</span>
            </div>
          </div>
        </div>
        <div class="text-left md:text-right p-4 bg-slate-50 rounded-xl border border-slate-100">
          <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Due Balance</p>
          <p class="text-3xl font-extrabold" :class="customer?.balance > 0 ? 'text-rose-600' : 'text-emerald-600'">
            ${{ formatCurrency(customer?.balance) }}
          </p>
        </div>
      </div>

      <!-- Statement Timeline / Table -->
      <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden" id="printable-statement">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
          <h3 class="text-lg font-bold text-slate-900">Transaction History</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-100">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Description</th>
                <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Debit (Charge)</th>
                <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Credit (Payment)</th>
                <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Running Balance</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="t in statement" :key="t.id" class="hover:bg-slate-50/50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 font-medium">{{ formatDate(t.date) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-2">
                    <span :class="['w-2 h-2 rounded-full', t.type === 'invoice' ? 'bg-amber-400' : 'bg-emerald-400']"></span>
                    <span class="text-sm font-semibold text-slate-900">{{ t.description }}</span>
                    <router-link v-if="t.invoice_id" :to="`/invoices/${t.invoice_id}`" class="text-indigo-600 hover:text-indigo-800 text-xs bg-indigo-50 px-2 py-0.5 rounded font-medium">View</router-link>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900 text-right">
                  {{ t.debit > 0 ? '$' + formatCurrency(t.debit) : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-emerald-600 text-right">
                  {{ t.credit > 0 ? '$' + formatCurrency(t.credit) : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-900 text-right">
                  ${{ formatCurrency(t.balance) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../services/api';

const route = useRoute();
const loading = ref(true);
const customer = ref(null);
const statement = ref([]);

const formatCurrency = (val) => Number(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

const fetchStatement = async () => {
  loading.value = true;
  try {
    const id = route.params.id;
    
    const [custRes, txsRes] = await Promise.all([
      api.get(`/customer-ledgers/${id}`),
      api.get(`/customer-ledgers/${id}/statement`)
    ]);

    const custInfo = custRes.data?.customer || {};
    const ledgerInfo = custRes.data?.ledger || {};
    
    customer.value = {
      id: custInfo.id,
      name: custInfo.name || 'Unknown Customer',
      email: custInfo.email || '',
      phone: custInfo.phone || '',
      balance: Number(ledgerInfo.current_balance || 0)
    };

    const rawTxs = txsRes.data || [];
    statement.value = rawTxs.map(t => ({
      id: t.id,
      date: t.created_at || t.updated_at,
      description: t.note || `${t.transaction_type.toUpperCase()} transaction`,
      debit: Number(t.debit || 0),
      credit: Number(t.credit || 0),
      balance: Number(t.balance || 0),
      type: t.transaction_type,
      invoice_id: t.invoice_id
    }));
  } catch (error) {
    console.error('Failed to fetch statement:', error);
  } finally {
    loading.value = false;
  }
};

const printStatement = () => {
  window.print();
};

onMounted(() => {
  fetchStatement();
});
</script>

<style scoped>
@media print {
  body * {
    visibility: hidden;
  }
  #printable-statement, #printable-statement * {
    visibility: visible;
  }
  #printable-statement {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
</style>
