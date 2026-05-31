<template>
  <div class="max-w-6xl mx-auto space-y-6 p-6 bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl text-slate-100 min-h-screen">
    
    <!-- Fallback Stage Selector -->
    <WorkspaceJobSelector 
      v-if="!route.params.id" 
      stage="parts" 
      title="Select Work Order for Parts Consumption" 
      @selected="handleJobSelected"
    />

    <div v-else-if="loading" class="animate-pulse space-y-6">
      <div class="h-8 bg-slate-800 rounded w-1/4"></div>
      <div class="h-96 bg-slate-800 rounded"></div>
    </div>
    <JobDetailsLayout v-else-if="workOrder" :jobCard="workOrder?.job_card || null" :activeStage="6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Active consumptions (Left Column) -->
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-slate-950/20 border border-slate-850 rounded-2xl p-5 shadow-xl">
          <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-3">Logged Material & Service Consumptions</h3>
          
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead>
                <tr class="border-b border-slate-850 text-slate-500 font-bold uppercase text-[9px] tracking-wider">
                  <th class="pb-3 pl-2">Item Description</th>
                  <th class="pb-3">Source / Ownership</th>
                  <th class="pb-3 text-right">Qty Billed</th>
                  <th class="pb-3 text-right">Unit BDT Rate</th>
                  <th class="pb-3 text-right">Billed Amount</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-850">
                <tr v-for="item in workOrder.consumptions" :key="item.id" class="text-slate-350">
                  <td class="py-3.5 pl-2 font-bold text-white">
                    {{ item.item_type === 'product' ? (item.part?.name || 'Product Line') : item.service_name }}
                  </td>
                  <td class="py-3.5">
                    <span :class="item.source_type === 'workshop_supplied' ? 'bg-indigo-500/10 text-indigo-400' : 'bg-orange-500/10 text-orange-400'" class="px-2.5 py-0.5 rounded text-[8px] font-black uppercase tracking-wider">
                      {{ item.source_type === 'workshop_supplied' ? 'Workshop Stock' : 'Customer Supplied (৳0.00)' }}
                    </span>
                  </td>
                  <td class="py-3.5 text-right font-mono font-bold">{{ item.actual_consumed_quantity || item.quantity }}</td>
                  <td class="py-3.5 text-right font-mono">
                    {{ formatCurrency(item.source_type === 'workshop_supplied' ? item.unit_price : 0) }}
                  </td>
                  <td class="py-3.5 text-right font-mono font-black text-slate-200">
                    {{ formatCurrency(item.source_type === 'workshop_supplied' ? (item.actual_consumed_quantity || item.quantity) * item.unit_price : 0) }}
                  </td>
                </tr>
                <tr v-if="!workOrder.consumptions || workOrder.consumptions.length === 0">
                  <td colspan="5" class="py-6 text-center text-slate-500 italic">No parts or services logged on this work order.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="flex justify-between items-center bg-slate-950/20 border border-slate-850 rounded-2xl p-5 mt-4">
          <p class="text-xs text-slate-400">All parts logged? You can proceed to check the vehicle's Quality Control status.</p>
          <router-link
            :to="{ name: 'workshop.qc-delivery' }"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold uppercase tracking-wider transition"
          >
            Proceed to QC & Delivery
          </router-link>
        </div>
      </div>

      <!-- Add Mid-repair consumption (Right Column) -->
      <div class="lg:col-span-1 space-y-6">
        <div class="bg-slate-950/40 border border-slate-850 rounded-2xl p-5 shadow-xl space-y-4">
          <h3 class="text-xs font-black uppercase tracking-wider text-indigo-400">Log Extra Mid-Repair Item</h3>
          <p class="text-[10px] text-slate-400">Add parts or labor services identified during repair. Real-time stock reservation is applied.</p>
          
          <form @submit.prevent="submitConsumption" class="space-y-4">
            <div>
              <label class="block text-[10px] text-slate-450 mb-1">Item Type *</label>
              <select v-model="form.item_type" class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white">
                <option value="product">Product (Part)</option>
                <option value="service">Service (Labor)</option>
              </select>
            </div>

            <div v-if="form.item_type === 'product'">
              <label class="block text-[10px] text-slate-450 mb-1">Select Inventory Part *</label>
              <select v-model="form.part_id" required @change="calculateRate" class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white">
                <option value="">Select Part...</option>
                <option v-for="part in partsList" :key="part.id" :value="part.id">
                  {{ part.name }} (Stock: {{ part.stock_quantity }})
                </option>
              </select>
            </div>

            <div v-else>
              <label class="block text-[10px] text-slate-450 mb-1">Service Task Description *</label>
              <input
                v-model="form.service_name"
                type="text"
                required
                @blur="calculateRate"
                placeholder="e.g. Extra Denting Panel"
                class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white"
              />
            </div>

            <div>
              <label class="block text-[10px] text-slate-450 mb-1">Ownership / Source *</label>
              <select v-model="form.source_type" @change="onSourceChange" class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white">
                <option value="workshop_supplied">Workshop Supplied Stock</option>
                <option value="customer_supplied">Customer Supplied (Zero Billed)</option>
              </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] text-slate-450 mb-1">Quantity *</label>
                <input
                  v-model.number="form.quantity"
                  type="number"
                  required
                  class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white font-mono"
                />
              </div>
              <div>
                <label class="block text-[10px] text-slate-450 mb-1">Rate (BDT)</label>
                <input
                  v-model.number="form.unit_price"
                  type="number"
                  required
                  :disabled="form.source_type === 'customer_supplied'"
                  class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white font-mono disabled:opacity-60"
                />
              </div>
            </div>

            <div>
              <label class="block text-[10px] text-slate-450 mb-1">Internal Supervisor Notes</label>
              <textarea
                v-model="form.notes"
                rows="2"
                placeholder="Audit description for adding item mid-job..."
                class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white"
              ></textarea>
            </div>

            <button
              type="submit"
              :disabled="saving"
              class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition disabled:opacity-50"
            >
              {{ saving ? 'Logging consumption...' : 'Commit Consumption Log' }}
            </button>
          </form>
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
const workOrder = ref(null);
const partsList = ref([]);

const form = reactive({
  item_type: 'product',
  part_id: '',
  service_name: '',
  source_type: 'workshop_supplied',
  quantity: 1,
  unit_price: 0,
  notes: ''
});

const handleJobSelected = (id) => {
  router.push({ name: 'workshop.parts-consumption', params: { id } });
};

const fetchWorkOrderDetails = async () => {
  if (!route.params.id) {
    workOrder.value = null;
    loading.value = false;
    return;
  }
  loading.value = true;
  try {
    const res = await api.get(`/work-orders/${route.params.id}`);
    workOrder.value = res.data?.data || res.data;
  } catch (err) {
    toast.error('Failed to load Work Order details');
    router.push({ name: 'workshop.parts-consumption' });
  } finally {
    loading.value = false;
  }
};

const fetchParts = async () => {
  try {
    const res = await api.get('/parts');
    partsList.value = res.data?.data || res.data || [];
  } catch (err) {
    console.error(err);
  }
};

const onSourceChange = () => {
  if (form.source_type === 'customer_supplied') {
    form.unit_price = 0;
  } else {
    calculateRate();
  }
};

const calculateRate = async () => {
  if (form.source_type === 'customer_supplied') {
    form.unit_price = 0;
    return;
  }
  if (form.item_type === 'product' && !form.part_id) return;
  if (form.item_type === 'service' && !form.service_name) return;

  try {
    const response = await api.get(`/customers/${workOrder.value.job_card?.customer_id}/pricing/calculate`, {
      params: {
        part_id: form.part_id || undefined,
        labor_service_name: form.service_name || undefined,
      }
    });
    form.unit_price = response.data?.data?.price || 0;
  } catch (err) {
    console.error('Pricing engine calculation failed', err);
    form.unit_price = 0;
  }
};

const submitConsumption = async () => {
  saving.value = true;
  try {
    const payload = {
      item_type: form.item_type,
      part_id: form.item_type === 'product' ? form.part_id : undefined,
      service_name: form.item_type === 'service' ? form.service_name : undefined,
      source_type: form.source_type,
      quantity: form.quantity,
      unit_price: form.unit_price,
      notes: form.notes,
      is_approved: true
    };

    await api.post(`/work-orders/${workOrder.value.id}/additional-consumption`, payload);
    toast.success('Consumption registered and synchronized successfully.');
    
    // Reset form
    form.part_id = '';
    form.service_name = '';
    form.unit_price = 0;
    form.quantity = 1;
    form.notes = '';

    await fetchWorkOrderDetails();
  } catch (err) {
    console.error('Parts consumption log failed', err);
    toast.error(err.response?.data?.message || 'Failed to record parts consumption.');
  } finally {
    saving.value = false;
  }
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-BD', { style: 'currency', currency: 'BDT' }).format(value || 0);
};

watch(() => route.params.id, (newId) => {
  if (newId) {
    fetchWorkOrderDetails();
  } else {
    workOrder.value = null;
    loading.value = false;
  }
});

onMounted(() => {
  if (route.params.id) {
    fetchWorkOrderDetails();
  } else {
    loading.value = false;
  }
  fetchParts();
});
</script>
