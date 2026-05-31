<template>
  <div class="space-y-6 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-850 pb-5">
      <div>
        <h2 class="text-xl font-black text-white uppercase tracking-tight">{{ title }}</h2>
        <p class="text-xs text-slate-400 mt-1">Please select an active vehicle from the queue to open the workspace.</p>
      </div>
      
      <!-- Search Input -->
      <div class="relative w-full sm:w-72">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search plate, vehicle, client..."
          class="w-full text-xs bg-slate-950 border border-slate-800 rounded-xl py-2.5 pl-9 pr-4 text-white focus:outline-none focus:border-indigo-500 transition-colors"
        />
        <svg class="w-4 h-4 text-slate-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-3">
      <div class="w-8 h-8 border-3 border-indigo-650 border-t-transparent rounded-full animate-spin"></div>
      <span class="text-[10px] text-slate-550 font-black uppercase tracking-wider">Syncing queue state...</span>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredItems.length === 0" class="text-center py-24 border border-dashed border-slate-850 rounded-2xl p-6">
      <div class="w-12 h-12 rounded-full bg-slate-800/40 text-slate-550 flex items-center justify-center mx-auto mb-3 text-lg">
        ✓
      </div>
      <h3 class="text-xs font-bold text-slate-350 uppercase tracking-widest">No vehicles in queue</h3>
      <p class="text-[10px] text-slate-500 mt-1">There are currently no active job cards waiting at this operational stage.</p>
    </div>

    <!-- Searchable Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div 
        v-for="item in filteredItems" 
        :key="item.id"
        class="bg-slate-950/40 border border-slate-800 hover:border-slate-700 rounded-2xl p-4.5 transition flex flex-col justify-between gap-4.5 shadow-sm"
      >
        <div>
          <div class="flex justify-between items-start">
            <span class="text-[9px] font-mono font-bold text-slate-500">#JC-{{ String(item.id).padStart(5, '0') }}</span>
            <span :class="getPriorityClass(item.priority_level)" class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest">
              {{ item.priority_level || 'normal' }}
            </span>
          </div>

          <h3 class="font-extrabold text-white text-sm mt-2">
            {{ item.vehicle?.make || 'Vehicle' }} {{ item.vehicle?.model || '' }}
          </h3>
          <p class="text-[10px] text-indigo-400 font-mono mt-0.5 uppercase">
            Plate: {{ item.vehicle?.license_plate || item.vehicle?.registration_no || 'Pending Plate' }}
          </p>

          <div class="mt-3.5 space-y-1.5 border-t border-slate-850/60 pt-3.5 text-[10px] text-slate-400">
            <div class="flex justify-between">
              <span>Customer Name:</span>
              <span class="font-bold text-slate-300 truncate max-w-[150px]">{{ item.customer?.name || 'Walk-in Customer' }}</span>
            </div>
            <div v-if="item.workshop_bay?.name" class="flex justify-between">
              <span>Current Bay:</span>
              <span class="font-bold text-emerald-450">{{ item.workshop_bay.name }}</span>
            </div>
            <div v-if="item.mechanic?.name" class="flex justify-between">
              <span>Mechanic:</span>
              <span class="font-bold text-indigo-400">{{ item.mechanic.name }}</span>
            </div>
            <div v-if="item.complaint" class="pt-1.5 text-slate-400 italic leading-snug">
              Complaint: "{{ item.complaint }}"
            </div>
          </div>
        </div>

        <button 
          @click="selectJob(item)"
          class="w-full py-2.5 bg-indigo-650 hover:bg-indigo-600 active:scale-[0.99] text-center text-white rounded-xl font-black text-[10px] uppercase tracking-wider transition-all duration-200 border border-indigo-700/50"
        >
          Select & Open Workspace
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';

const props = defineProps({
  stage: {
    type: String,
    required: true
  },
  title: {
    type: String,
    default: 'Select Active Vehicle Job'
  }
});

const emit = defineEmits(['selected']);

const router = useRouter();
const toast = useToastStore();

const loading = ref(true);
const searchQuery = ref('');
const jobCards = ref([]);
const quotations = ref([]);
const workOrders = ref([]);
const invoicesList = ref([]);

const fetchData = async () => {
  loading.value = true;
  try {
    const promises = [
      api.get('/job-cards', { params: { per_page: 200 } })
    ];

    const needsQuotations = props.stage === 'quotation';
    const needsWorkOrders = props.stage === 'parts' || props.stage === 'qc';
    const needsInvoices = props.stage === 'delivery' || props.stage === 'settlement';

    if (needsQuotations) {
      promises.push(api.get('/quotations', { params: { per_page: 200 } }));
    }
    if (needsWorkOrders) {
      promises.push(api.get('/work-orders', { params: { per_page: 200 } }));
    }
    if (needsInvoices) {
      promises.push(api.get('/invoices', { params: { per_page: 200 } }));
    }

    const results = await Promise.all(promises);

    jobCards.value = results[0].data?.data || results[0].data || [];

    let idx = 1;
    if (needsQuotations) {
      quotations.value = results[idx].data?.data || results[idx].data || [];
      idx++;
    }
    if (needsWorkOrders) {
      workOrders.value = results[idx].data?.data || results[idx].data || [];
      idx++;
    }
    if (needsInvoices) {
      invoicesList.value = results[idx].data?.data || results[idx].data || [];
      idx++;
    }
  } catch (err) {
    console.error('Selector sync failed', err);
    toast.error('Failed to sync queue list. Operating in display-only mode.');
  } finally {
    loading.value = false;
  }
};

const filteredItems = computed(() => {
  // Filter jobs based on active workflow stage
  let filtered = [];

  switch (props.stage) {
    case 'inspection':
      // Status pending, and no diagnosis logged
      filtered = jobCards.value.filter(
        jc => (jc.service_status === 'pending' || jc.service_status === 'intake') && !jc.diagnosis
      );
      break;
    case 'diagnosis':
      // Status pending, has OBD/walkaround findings, but needs detailed diagnostic codes
      filtered = jobCards.value.filter(
        jc => (jc.service_status === 'pending' || jc.service_status === 'intake')
      );
      break;
    case 'quotation':
      // Status pending, diagnosis logged, but no quotation generated
      filtered = jobCards.value.filter(
        jc => jc.diagnosis && !quotations.value.some(q => q.job_card_id === jc.id)
      );
      break;
    case 'parts':
      // Active or paused work orders requiring parts
      filtered = jobCards.value.filter(
        jc => workOrders.value.some(wo => wo.job_card_id === jc.id && (wo.status === 'in_progress' || wo.status === 'paused' || wo.status === 'pending'))
      );
      break;
    case 'qc':
      // Work orders marked completed, but Job Card service status is not completed/delivered yet
      filtered = jobCards.value.filter(
        jc => jc.service_status !== 'completed' && jc.service_status !== 'delivered' && 
              workOrders.value.some(wo => wo.job_card_id === jc.id && wo.status === 'completed')
      );
      break;
    case 'delivery':
    case 'settlement':
      // Completed repairs waiting invoicing, settlement and handover
      filtered = jobCards.value.filter(
        jc => jc.service_status === 'completed' || jc.service_status === 'settled'
      );
      break;
    default:
      // Fallback: list all active/pending job cards
      filtered = jobCards.value.filter(
        jc => jc.service_status !== 'delivered' && jc.service_status !== 'cancelled'
      );
  }

  // Filter based on search query
  if (!searchQuery.value) return filtered;
  const q = searchQuery.value.toLowerCase();
  return filtered.filter(jc => {
    const plate = (jc.vehicle?.license_plate || jc.vehicle?.registration_no || '').toLowerCase();
    const make = (jc.vehicle?.make || '').toLowerCase();
    const model = (jc.vehicle?.model || '').toLowerCase();
    const client = (jc.customer?.name || '').toLowerCase();
    const complaint = (jc.complaint || '').toLowerCase();
    return plate.includes(q) || make.includes(q) || model.includes(q) || client.includes(q) || complaint.includes(q);
  });
});

const selectJob = (item) => {
  if (props.stage === 'parts' || props.stage === 'qc') {
    const wo = workOrders.value.find(w => w.job_card_id === item.id);
    if (wo) {
      emit('selected', wo.id);
      return;
    }
  }
  if (props.stage === 'settlement') {
    const inv = invoicesList.value.find(i => i.job_card_id === item.id);
    if (inv) {
      emit('selected', inv.id);
      return;
    } else {
      toast.warning('No invoice generated for this job card yet. Generate invoice in Delivery Workspace first.');
      return;
    }
  }
  emit('selected', item.id);
};

const getPriorityClass = (priority) => {
  const map = {
    'low': 'bg-slate-800 text-slate-400',
    'normal': 'bg-slate-800 text-slate-400',
    'medium': 'bg-yellow-950 text-yellow-400 border border-yellow-800/40',
    'high': 'bg-rose-950 text-rose-400 border border-rose-800/40',
    'critical': 'bg-red-950 text-red-400 border border-red-500/40 animate-pulse',
    'urgent': 'bg-rose-950 text-rose-400 border border-rose-800/40',
  };
  return map[priority?.toLowerCase()] || 'bg-slate-800 text-slate-400';
};

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
.text-emerald-450 {
  color: #34d399;
}
</style>
