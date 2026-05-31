<template>
  <div class="max-w-4xl mx-auto space-y-6 p-6 bg-slate-50 border border-slate-200 rounded-3xl shadow-sm text-slate-800 min-h-screen">
    
    <!-- Fallback Stage Selector if no route ID is present -->
    <WorkspaceJobSelector 
      v-if="!route.params.id" 
      stage="all" 
      title="Select Vehicle to Open Customer Tracker" 
      @selected="handleJobSelected"
    />

    <div v-else-if="loading" class="animate-pulse space-y-6">
      <div class="h-8 bg-slate-200 rounded w-1/4"></div>
      <div class="h-96 bg-slate-200 rounded"></div>
    </div>

    <div v-else-if="jobCard" class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-200 pb-5">
        <div>
          <h1 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Customer Workflow Tracker</h1>
          <p class="text-xs text-slate-500 mt-1">Live customer-facing repair progression tracker for: <strong>{{ jobCard.vehicle?.make }} {{ jobCard.vehicle?.model }}</strong></p>
        </div>
        <div class="flex gap-2">
          <router-link
            :to="{ name: 'workshop.live-board' }"
            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-bold transition border border-slate-200"
          >
            ← Live Board
          </router-link>
          
          <button 
            @click="copyPortalLink"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-black uppercase tracking-wider transition"
          >
            🔗 Copy Customer Portal URL
          </button>
        </div>
      </div>

      <!-- Stepper Tracking Panel -->
      <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-sm relative overflow-hidden">
        <div class="absolute -right-20 -top-20 w-60 h-60 bg-indigo-500/2 rounded-full blur-3xl pointer-events-none"></div>
        
        <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 mb-6 font-mono text-center">Active Service Milestones</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 text-center">
          <div 
            v-for="(stage, idx) in trackerStages" 
            :key="idx"
            class="flex flex-col items-center gap-3"
          >
            <!-- Progress bubble -->
            <div 
              class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300 font-bold"
              :class="getStageBubbleClass(idx)"
            >
              <span v-if="idx < activeStageIndex">✔</span>
              <span v-else>{{ idx + 1 }}</span>
            </div>

            <!-- Stage text -->
            <div>
              <span :class="getStageTextClass(idx)" class="block text-[11px] font-black uppercase tracking-wider">{{ stage.name }}</span>
              <span class="text-[9px] text-slate-500 mt-0.5 block leading-tight px-2">{{ stage.description }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Vehicle Profile & Live Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Column left: Profile -->
        <div class="md:col-span-1 bg-white border border-slate-200 p-5 rounded-2xl space-y-4 shadow-sm">
          <h4 class="text-xs font-black uppercase tracking-wider text-indigo-650 font-mono">Vehicle Details</h4>
          
          <dl class="space-y-2 text-xs text-slate-650">
            <div>
              <dt class="text-[9px] font-bold uppercase text-slate-500">License Plate / Reg</dt>
              <dd class="font-extrabold text-slate-800 font-mono uppercase mt-0.5">{{ jobCard.vehicle?.license_plate || jobCard.vehicle?.registration_no || 'Pending' }}</dd>
            </div>
            <div>
              <dt class="text-[9px] font-bold uppercase text-slate-500">Customer Name</dt>
              <dd class="font-bold text-slate-700 mt-0.5">{{ jobCard.customer?.name || 'Walk-in Customer' }}</dd>
            </div>
            <div>
              <dt class="text-[9px] font-bold uppercase text-slate-500">Service Bay Location</dt>
              <dd class="font-bold text-emerald-700 mt-0.5">{{ jobCard.workshop_bay?.name || 'Awaiting bay allocation' }}</dd>
            </div>
            <div>
              <dt class="text-[9px] font-bold uppercase text-slate-500">Lead Mechanic Assigned</dt>
              <dd class="font-bold text-indigo-650 mt-0.5">{{ jobCard.mechanic?.name || 'Unassigned' }}</dd>
            </div>
          </dl>
        </div>

        <!-- Column middle/right: Task completion status -->
        <div class="md:col-span-2 bg-white border border-slate-200 p-5 rounded-2xl space-y-4 shadow-sm">
          <h4 class="text-xs font-black uppercase tracking-wider text-indigo-650 font-mono">Repair Progression Checklist</h4>
          
          <!-- Tasks list from work order -->
          <div v-if="workOrder && workOrder.tasks && workOrder.tasks.length > 0" class="space-y-3.5">
            <div 
              v-for="task in workOrder.tasks" 
              :key="task.id"
              class="flex justify-between items-center bg-slate-50 p-3 rounded-xl border border-slate-200"
            >
              <div>
                <span class="text-xs font-bold text-slate-800 block">{{ task.task_name }}</span>
                <span class="text-[9px] text-slate-400 font-mono mt-0.5 block" v-if="task.assigned_technician">Technician: {{ task.assigned_technician?.name }}</span>
              </div>

              <span :class="getTaskStatusClass(task.status)" class="px-2.5 py-0.5 rounded text-[8px] font-black uppercase tracking-wider">
                {{ task.status }}
              </span>
            </div>
          </div>

          <div v-else class="text-center py-10 border border-dashed border-slate-200 rounded-xl text-slate-400 text-xs italic">
            No work order tasks registered yet. Quotation approved is required.
          </div>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';
import WorkspaceJobSelector from '../../components/workshop/WorkspaceJobSelector.vue';

const route = useRoute();
const router = useRouter();
const toast = useToastStore();

const loading = ref(true);
const jobCard = ref(null);
const workOrder = ref(null);
const quotation = ref(null);
const invoice = ref(null);

const trackerStages = [
  { name: 'Approval Pending', description: 'Estimate sheet awaiting approval' },
  { name: 'Work Started', description: 'Technicians assigned to tasks' },
  { name: 'QC Running', description: 'Quality inspection checklist validation' },
  { name: 'Invoice Ready', description: 'Receipt Compiled' },
  { name: 'Ready for Delivery', description: 'Keys handover and checkout' }
];

const handleJobSelected = (id) => {
  router.push({ name: 'workshop.tracker', params: { id } });
};

const fetchTrackerDetails = async () => {
  if (!route.params.id) {
    jobCard.value = null;
    workOrder.value = null;
    quotation.value = null;
    invoice.value = null;
    loading.value = false;
    return;
  }
  loading.value = true;
  try {
    const response = await api.get(`/job-cards/${route.params.id}`);
    jobCard.value = response.data.data;

    // Fetch quotation
    const qoRes = await api.get('/quotations', { params: { job_card_id: jobCard.value.id } });
    const quotationsList = qoRes.data?.data || qoRes.data || [];
    if (quotationsList.length > 0) {
      quotation.value = quotationsList[0];
    }

    // Fetch work order
    const woRes = await api.get('/work-orders', { params: { job_card_id: jobCard.value.id } });
    const workOrdersList = woRes.data?.data || woRes.data || [];
    if (workOrdersList.length > 0) {
      const details = await api.get(`/work-orders/${workOrdersList[0].id}`);
      workOrder.value = details.data?.data || details.data;
    }

    // Fetch invoice
    const invRes = await api.get('/invoices', { params: { job_card_id: jobCard.value.id } });
    const invoicesList = invRes.data?.data || invRes.data || [];
    if (invoicesList.length > 0) {
      invoice.value = invoicesList[0];
    }

  } catch (error) {
    console.error('Failed to sync tracker details', error);
    toast.error('Failed to fetch tracking data.');
  } finally {
    loading.value = false;
  }
};

const activeStageIndex = computed(() => {
  if (!jobCard.value) return 0;
  
  const status = jobCard.value.service_status;

  if (status === 'delivered') return 5;
  if (status === 'completed' || status === 'settled') return 4;
  if (status === 'qc') return 2;
  
  if (workOrder.value && (workOrder.value.status === 'in_progress' || workOrder.value.status === 'paused')) {
    return 1; // Work Started
  }

  if (quotation.value && (quotation.value.status === 'draft' || quotation.value.status === 'revised')) {
    return 0; // Approval Pending
  }

  // Fallback default
  return 0;
});

const getStageBubbleClass = (idx) => {
  const activeIdx = activeStageIndex.value;
  if (idx === activeIdx) return 'bg-indigo-650 border-indigo-600 text-white animate-pulse shadow-sm';
  if (idx < activeIdx) return 'bg-emerald-50 border-emerald-500 text-emerald-700';
  return 'bg-slate-50 border-slate-200 text-slate-400';
};

const getStageTextClass = (idx) => {
  const activeIdx = activeStageIndex.value;
  if (idx === activeIdx) return 'text-indigo-650 font-extrabold';
  if (idx < activeIdx) return 'text-emerald-700';
  return 'text-slate-500';
};

const getTaskStatusClass = (status) => {
  const map = {
    'pending': 'bg-slate-100 text-slate-500 border border-slate-200',
    'in_progress': 'bg-indigo-50 text-indigo-650 border border-indigo-200 animate-pulse',
    'paused': 'bg-rose-50 text-rose-700 border border-rose-200',
    'completed': 'bg-emerald-50 text-emerald-700 border border-emerald-250',
  };
  return map[status?.toLowerCase()] || 'bg-slate-100 text-slate-500 border border-slate-200';
};

const copyPortalLink = () => {
  if (invoice.value?.uuid) {
    const portalUrl = `${window.location.origin}/portal/${invoice.value.uuid}`;
    navigator.clipboard.writeText(portalUrl);
    toast.success('Customer Portal URL copied to clipboard.');
  } else {
    toast.warning('No invoice generated for this job card. Generate invoice to obtain Portal UUID.');
  }
};

watch(() => route.params.id, (newId) => {
  if (newId) {
    fetchTrackerDetails();
  } else {
    jobCard.value = null;
    workOrder.value = null;
    quotation.value = null;
    invoice.value = null;
    loading.value = false;
  }
});

onMounted(() => {
  if (route.params.id) {
    fetchTrackerDetails();
  } else {
    loading.value = false;
  }
});
</script>

<style scoped>
</style>
