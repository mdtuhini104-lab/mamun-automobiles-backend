<template>
  <div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Customer Contract Pricing Engine</h1>
        <p class="text-sm text-slate-500 mt-1">Manage dynamic corporate negotiated rates and flat labor contract agreements.</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Form Panel -->
      <div class="lg:col-span-1 bg-white shadow-sm rounded-xl border border-slate-200 p-6 space-y-4">
        <h3 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3">New Contract Rate</h3>
        
        <form @submit.prevent="saveContractRate" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Customer *</label>
            <select v-model="form.customer_id" required @change="fetchCustomerPricing" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
              <option value="">Select Customer...</option>
              <option v-for="cust in customers" :key="cust.id" :value="cust.id">
                {{ cust.name }} ({{ cust.phone }})
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Pricing Target *</label>
            <select v-model="targetType" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
              <option value="product">Product (Specific Part Discount)</option>
              <option value="labor">Labor Service (Flat Labor Rate)</option>
            </select>
          </div>

          <div v-if="targetType === 'product'">
            <label class="block text-sm font-medium text-slate-700 mb-1">Product (Part) *</label>
            <select v-model="form.part_id" required class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
              <option value="">Select Part...</option>
              <option v-for="part in parts" :key="part.id" :value="part.id">
                {{ part.name }} (List Price: {{ formatCurrency(part.sale_price) }})
              </option>
            </select>
          </div>

          <div v-else>
            <label class="block text-sm font-medium text-slate-700 mb-1">Labor Service Name *</label>
            <input
              v-model="form.labor_service_name"
              type="text"
              required
              placeholder="e.g. Engine Diagnostics Scan"
              class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Negotiated Rate *</label>
            <input
              v-model.number="ratePrice"
              type="number"
              required
              step="0.01"
              placeholder="0.00"
              class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Effective Date *</label>
            <input
              v-model="form.effective_date"
              type="date"
              required
              class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Agreement Notes</label>
            <textarea
              v-model="form.notes"
              rows="3"
              placeholder="e.g. Agreed to a flat 10% off list price during negotiations..."
              class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            ></textarea>
          </div>

          <button
            type="submit"
            :disabled="saving || !form.customer_id"
            class="w-full inline-flex justify-center py-2.5 px-4 border border-transparent shadow-sm text-sm font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 transition-all"
          >
            {{ saving ? 'Saving Price Rate...' : 'Save Price Agreement' }}
          </button>
        </form>
      </div>

      <!-- Active Price Contracts Table Panel -->
      <div class="lg:col-span-2 bg-white shadow-sm rounded-xl border border-slate-200 p-6 space-y-4">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
          <h3 class="text-lg font-bold text-slate-900">Corporate Price Agreements</h3>
          <span v-if="loadingRates" class="text-xs text-indigo-600 font-semibold">Loading rates...</span>
        </div>

        <div v-if="!form.customer_id" class="text-center py-12 text-slate-500 text-sm">
          Please select a customer to view or edit corporate price agreements.
        </div>
        <div v-else-if="rates.length === 0" class="text-center py-12 text-slate-500 text-sm">
          No corporate contract rates configured for this customer.
        </div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500">Target</th>
                <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500">Contract Rate</th>
                <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500">Effective Date</th>
                <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500">Agreement Description</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              <tr v-for="rate in rates" :key="rate.id">
                <td class="px-4 py-3 text-sm text-slate-900 font-semibold">
                  <span v-if="rate.part_id" class="flex flex-col">
                    <span>{{ rate.part?.name }}</span>
                    <span class="text-[10px] text-slate-400 font-normal">SKU: {{ rate.part?.sku }}</span>
                  </span>
                  <span v-else>{{ rate.labor_service_name }}</span>
                </td>
                <td class="px-4 py-3 text-sm font-bold text-indigo-600">
                  {{ formatCurrency(rate.part_id ? rate.custom_price : rate.custom_labor_rate) }}
                </td>
                <td class="px-4 py-3 text-sm text-slate-500">{{ rate.effective_date }}</td>
                <td class="px-4 py-3 text-sm text-slate-600 font-medium italic">"{{ rate.notes || 'None' }}"</td>
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
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';

const toast = useToastStore();

const customers = ref([]);
const parts = ref([]);
const rates = ref([]);

const loadingRates = ref(false);
const saving = ref(false);
const targetType = ref('product');
const ratePrice = ref('');

const form = ref({
  customer_id: '',
  part_id: '',
  labor_service_name: '',
  custom_price: null,
  custom_labor_rate: null,
  effective_date: new Date().toISOString().slice(0, 10),
  notes: '',
});

const fetchInitData = async () => {
  try {
    const custRes = await api.get('/customers');
    customers.value = custRes.data.data;
    
    const partsRes = await api.get('/parts');
    parts.value = partsRes.data.data;
  } catch (err) {
    toast.error('Failed to load initial corporate data');
  }
};

const fetchCustomerPricing = async () => {
  if (!form.value.customer_id) return;
  loadingRates.value = true;
  try {
    const response = await api.get(`/customers/${form.value.customer_id}/pricing`);
    rates.value = response.data.data;
  } catch (err) {
    toast.error('Failed to fetch pricing rates');
  } finally {
    loadingRates.value = false;
  }
};

onMounted(() => {
  fetchInitData();
});

const saveContractRate = async () => {
  saving.value = true;
  
  if (targetType.value === 'product') {
    form.value.custom_price = ratePrice.value;
    form.value.custom_labor_rate = null;
    form.value.labor_service_name = '';
  } else {
    form.value.custom_labor_rate = ratePrice.value;
    form.value.custom_price = null;
    form.value.part_id = '';
  }

  try {
    await api.post('/customers/pricing', form.value);
    toast.success('Corporate negotiated pricing rule stored successfully!');
    await fetchCustomerPricing();
    
    // Reset inputs
    form.value.part_id = '';
    form.value.labor_service_name = '';
    ratePrice.value = '';
    form.value.notes = '';
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to record pricing agreement');
  } finally {
    saving.value = false;
  }
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-BD', { style: 'currency', currency: 'BDT' }).format(value || 0);
};
</script>
