<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-800 pb-5">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-white">Fleet Management Portal</h1>
        <p class="text-xs text-slate-400 mt-1">Manage bulk corporate contracts, custom commercial discount schemes, and bulk repair authorizations.</p>
      </div>
      <div class="flex gap-2">
        <button 
          @click="fetchFleetData" 
          class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition flex items-center gap-1.5"
          :disabled="loading"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4" :class="{ 'animate-spin': loading }">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
          Refresh Portal
        </button>
      </div>
    </div>

    <!-- Fleet Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-md hover:border-slate-700 transition">
        <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest">Active Corporate Accounts</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-2xl font-black text-white">{{ metrics.active_contracts || 0 }}</span>
          <span class="text-[10px] text-indigo-400 font-bold bg-indigo-500/10 px-2 py-0.5 rounded-lg border border-indigo-500/20">Operational</span>
        </div>
      </div>

      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-md hover:border-slate-700 transition">
        <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest">Total Registered Contracts</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-2xl font-black text-white">{{ metrics.total_contracts || 0 }}</span>
          <span class="text-[10px] text-slate-400 font-bold bg-slate-800 px-2 py-0.5 rounded-lg border border-slate-700">Archived Included</span>
        </div>
      </div>

      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-md hover:border-slate-700 transition">
        <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest">Pending Repair Approvals</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-2xl font-black text-rose-400">{{ metrics.pending_authorizations || 0 }}</span>
          <span 
            class="text-[10px] font-extrabold px-2 py-0.5 rounded-lg border"
            :class="metrics.pending_authorizations > 0 ? 'bg-rose-500/10 border-rose-500/20 text-rose-400' : 'bg-slate-800 border-slate-700 text-slate-350'"
          >
            {{ metrics.pending_authorizations > 0 ? 'Requires Action' : 'Cleared' }}
          </span>
        </div>
      </div>
    </div>

    <!-- Active Contracts & Add Contract -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Register Fleet Contract -->
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
        <div>
          <h2 class="text-base font-black text-white uppercase tracking-wider">Register Corporate Fleet</h2>
          <p class="text-xs text-slate-400 mt-0.5">Setup billing structures and discount percentages for commercial fleets.</p>
        </div>

        <form @submit.prevent="createFleetContract" class="space-y-4">
          <div>
            <label class="block text-[10px] text-slate-400 font-extrabold uppercase mb-1.5">Company Name</label>
            <input 
              v-model="contractForm.company_name" 
              type="text" 
              placeholder="e.g. Mamun Logistics Ltd" 
              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500" 
              required
            />
          </div>

          <div>
            <label class="block text-[10px] text-slate-400 font-extrabold uppercase mb-1.5">Primary Contact Email</label>
            <input 
              v-model="contractForm.contact_email" 
              type="email" 
              placeholder="billing@company.com" 
              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500" 
              required
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] text-slate-400 font-extrabold uppercase mb-1.5">Billing Terms</label>
              <select v-model="contractForm.billing_terms" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                <option value="Due on Receipt">Due on Receipt</option>
                <option value="Net 15">Net 15</option>
                <option value="Net 30">Net 30</option>
                <option value="Net 60">Net 60</option>
              </select>
            </div>
            <div>
              <label class="block text-[10px] text-slate-400 font-extrabold uppercase mb-1.5">Discount %</label>
              <input 
                v-model="contractForm.discount_percent" 
                type="number" 
                step="0.01" 
                min="0" 
                max="100" 
                placeholder="5.00" 
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500"
              />
            </div>
          </div>

          <button 
            type="submit" 
            class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition shadow-lg"
            :disabled="submitting"
          >
            {{ submitting ? 'Registering Contract...' : 'Create Fleet Contract' }}
          </button>
        </form>
      </div>

      <!-- Contracts List -->
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4 lg:col-span-2">
        <div>
          <h2 class="text-base font-black text-white uppercase tracking-wider">Active Corporate Contracts</h2>
          <p class="text-xs text-slate-400 mt-0.5">Current registered commercial client configurations.</p>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-350">
            <thead class="border-b border-slate-800 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
              <tr>
                <th class="pb-3">Company</th>
                <th class="pb-3">Email</th>
                <th class="pb-3">Billing Terms</th>
                <th class="pb-3">Discount Rate</th>
                <th class="pb-3">Status</th>
                <th class="pb-3 text-right">Created</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-850">
              <tr v-for="contract in contracts" :key="contract.id" class="hover:bg-slate-850/30 transition">
                <td class="py-3 font-semibold text-white">{{ contract.company_name }}</td>
                <td class="py-3 font-mono text-slate-400">{{ contract.contact_email }}</td>
                <td class="py-3">{{ contract.billing_terms }}</td>
                <td class="py-3 text-indigo-400 font-bold">{{ contract.discount_percent }}%</td>
                <td class="py-3">
                  <span 
                    class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider"
                    :class="contract.status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-400'"
                  >
                    {{ contract.status }}
                  </span>
                </td>
                <td class="py-3 text-right text-slate-400">{{ formatDate(contract.created_at) }}</td>
              </tr>
              <tr v-if="contracts.length === 0">
                <td colspan="6" class="py-10 text-center text-slate-500">
                  No corporate fleet accounts registered yet.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Bulk Authorizations & Quotations -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h2 class="text-base font-black text-white uppercase tracking-wider">Bulk Quotation Repair Approvals</h2>
          <p class="text-xs text-slate-400 mt-0.5">Select and approve multiple vehicle estimates concurrently to initiate work orders immediately.</p>
        </div>
        <button 
          @click="bulkApproveSelected" 
          class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg transition"
          :disabled="selectedQuotationIds.length === 0 || bulkApproving"
        >
          {{ bulkApproving ? 'Approving repair pipeline...' : `Bulk Approve Selected (${selectedQuotationIds.length})` }}
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-350">
          <thead class="border-b border-slate-800 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
            <tr>
              <th class="pb-3 w-10">
                <input type="checkbox" @change="toggleSelectAllQuotations" class="rounded border-slate-800 bg-slate-950 text-indigo-600 focus:ring-0 focus:ring-offset-0" />
              </th>
              <th class="pb-3">Quotation ID</th>
              <th class="pb-3">Vehicle Details</th>
              <th class="pb-3">Grand Total</th>
              <th class="pb-3">Proposed Discount</th>
              <th class="pb-3">Date Drafted</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-850">
            <tr v-for="quota in pendingQuotations" :key="quota.id" class="hover:bg-slate-850/30 transition">
              <td class="py-3">
                <input type="checkbox" v-model="selectedQuotationIds" :value="quota.id" class="rounded border-slate-800 bg-slate-950 text-indigo-600 focus:ring-0 focus:ring-offset-0" />
              </td>
              <td class="py-3 font-mono text-indigo-400">#QUO-{{ quota.id }}</td>
              <td class="py-3 text-white font-semibold">{{ quota.vehicle_details || 'Vehicle info' }}</td>
              <td class="py-3 font-mono">${{ quota.grand_total }}</td>
              <td class="py-3 text-amber-400 font-bold">{{ quota.discount_percent }}%</td>
              <td class="py-3 text-slate-400">{{ formatDate(quota.created_at) }}</td>
            </tr>
            <tr v-if="pendingQuotations.length === 0">
              <td colspan="6" class="py-10 text-center text-slate-500">
                No pending fleet vehicle repair quotations found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';

const toast = useToastStore();
const loading = ref(false);
const submitting = ref(false);
const bulkApproving = ref(false);

const metrics = ref({
  total_contracts: 0,
  active_contracts: 0,
  pending_authorizations: 0
});

const contracts = ref([]);
const pendingQuotations = ref([]);
const selectedQuotationIds = ref([]);

const contractForm = ref({
  company_name: '',
  contact_email: '',
  billing_terms: 'Net 30',
  discount_percent: 0.00
});

const fetchFleetData = async () => {
  loading.value = true;
  try {
    const metricsResponse = await api.get('/fleet/metrics');
    metrics.value = metricsResponse.data.data;

    const contractsResponse = await api.get('/fleet/contracts');
    contracts.value = contractsResponse.data.data;

    // Fetch pending quotations list for bulk approvals from quotations endpoint
    const quotationsResponse = await api.get('/quotations');
    pendingQuotations.value = (quotationsResponse.data.data || []).filter(q => q.status === 'pending');
  } catch (error) {
    console.error('Failed to load fleet dashboard details', error);
    toast.error('Could not fetch corporate accounts information.');
  } finally {
    loading.value = false;
  }
};

const createFleetContract = async () => {
  submitting.value = true;
  try {
    await api.post('/fleet/contracts', contractForm.value);
    toast.success('Corporate fleet contract successfully registered.');
    contractForm.value = {
      company_name: '',
      contact_email: '',
      billing_terms: 'Net 30',
      discount_percent: 0.00
    };
    await fetchFleetData();
  } catch (error) {
    console.error('Failed to create contract', error);
    toast.error('Registration failed. Ensure email is unique and fields are correct.');
  } finally {
    submitting.value = false;
  }
};

const toggleSelectAllQuotations = (e) => {
  if (e.target.checked) {
    selectedQuotationIds.value = pendingQuotations.value.map(q => q.id);
  } else {
    selectedQuotationIds.value = [];
  }
};

const bulkApproveSelected = async () => {
  if (selectedQuotationIds.value.length === 0) return;
  bulkApproving.value = true;
  try {
    await api.post('/fleet/quotations/bulk-approve', {
      quotation_ids: selectedQuotationIds.value
    });
    toast.success(`Successfully approved ${selectedQuotationIds.value.length} quotations.`);
    selectedQuotationIds.value = [];
    await fetchFleetData();
  } catch (error) {
    console.error('Bulk approval error', error);
    toast.error('Failed to process bulk approvals. Verify quotation states.');
  } finally {
    bulkApproving.value = false;
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

onMounted(() => {
  fetchFleetData();
});
</script>

<style scoped>
</style>
