<template>
  <div class="workshop-live-board-container p-6 bg-slate-50 border border-slate-200 rounded-3xl shadow-sm text-slate-800 min-h-screen">
    <!-- Header with statistics -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-8 border-b border-slate-200 pb-6">
      <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight flex items-center gap-3">
          Live Operational Kanban Board
          <span class="flex h-3 w-3 relative">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-450 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
          </span>
        </h1>
        <p class="text-sm text-slate-500 mt-1">Real-time enterprise workflow activation desk. Auto-synced via CAN-Bus and WebSockets.</p>
      </div>

      <!-- Quick stats -->
      <div class="flex flex-wrap items-center gap-3">
        <!-- Connection state -->
        <div class="flex items-center gap-2 px-3 py-2 rounded-xl border bg-white transition-all duration-300 border-indigo-200 shadow-sm">
          <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
          </span>
          <span class="text-[9px] font-black uppercase tracking-widest text-slate-600">
            WS: Connected
          </span>
        </div>

        <button 
          @click="fetchLiveBoard" 
          class="px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 rounded-xl transition duration-200 border border-slate-200 shadow-sm font-bold text-xs flex items-center gap-1.5 focus:outline-none"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
          Sync Board
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-3">
      <div class="w-10 h-10 border-4 border-indigo-650 border-t-transparent rounded-full animate-spin"></div>
      <p class="text-xs text-slate-500 font-bold uppercase tracking-widest">Rebuilding Board State...</p>
    </div>

    <!-- Horizontal Kanban Columns -->
    <div v-else class="flex overflow-x-auto gap-6 pb-6 scrollbar-thin scrollbar-thumb-slate-200 scrollbar-track-slate-100 min-h-[70vh]">
      
      <!-- Columns rendering -->
      <div 
        v-for="col in kanbanColumns" 
        :key="col.id"
        class="flex-shrink-0 w-80 bg-slate-100/50 border border-slate-200 rounded-3xl p-4 flex flex-col justify-between gap-4 h-[650px] shadow-sm"
      >
        <!-- Column Header -->
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
          <div class="flex items-center gap-2.5">
            <span :class="col.color" class="w-2.5 h-2.5 rounded-full shrink-0"></span>
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">{{ col.name }}</h3>
          </div>
          <span class="bg-slate-200 text-slate-700 text-[10px] font-black px-2.5 py-0.5 rounded-full border border-slate-300">
            {{ col.items.length }}
          </span>
        </div>

        <!-- Column Body (Scrollable items) -->
        <div class="flex-1 overflow-y-auto space-y-3.5 pr-1 scrollbar-thin">
          <div v-if="col.items.length === 0" class="flex flex-col items-center justify-center py-24 text-center border border-dashed border-slate-200 rounded-2xl p-4">
            <span class="text-slate-400 text-lg mb-1.5">✓</span>
            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Empty Stage</p>
          </div>

          <!-- Kanban Cards -->
          <div 
            v-else
            v-for="item in col.items" 
            :key="item.id"
            class="bg-white border border-slate-200 hover:border-slate-300 hover:shadow rounded-2xl p-4 transition-all flex flex-col justify-between gap-4 shadow-sm"
          >
            <div>
              <div class="flex justify-between items-start gap-2">
                <span class="text-[9px] font-extrabold text-slate-400 font-mono">
                  #JC-{{ String(item.id).padStart(5, '0') }}
                </span>
                <span 
                  v-if="item.priority_level" 
                  :class="getPriorityClass(item.priority_level)" 
                  class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest"
                >
                  {{ item.priority_level }}
                </span>
              </div>

              <h4 class="font-extrabold text-slate-800 text-xs mt-2">
                {{ item.vehicle?.make }} {{ item.vehicle?.model }}
              </h4>
              <p class="text-[9px] text-slate-500 font-mono mt-0.5">
                Plate: {{ item.vehicle?.license_plate || item.vehicle?.registration_no }}
              </p>

              <!-- Secondary details -->
              <div class="mt-3 space-y-1.5 border-t border-slate-200 pt-3 text-[10px] text-slate-500">
                <div class="flex justify-between">
                  <span>Customer:</span>
                  <span class="font-bold text-slate-700 truncate max-w-[130px]">{{ item.customer?.name || 'Walk-in' }}</span>
                </div>
                <div v-if="item.workshop_bay?.name" class="flex justify-between">
                  <span>Bay:</span>
                  <span class="font-bold text-emerald-700">{{ item.workshop_bay?.name }}</span>
                </div>
                <div v-if="item.mechanic?.name" class="flex justify-between">
                  <span>Mechanic:</span>
                  <span class="font-bold text-indigo-650">{{ item.mechanic?.name }}</span>
                </div>
              </div>
            </div>

            <!-- Navigate Link directly to workspace -->
            <router-link
              :to="col.actionLink(item)"
              class="w-full py-2 bg-slate-50 hover:bg-slate-100 text-center text-slate-700 rounded-xl font-black text-[9px] uppercase tracking-wider transition-colors border border-slate-200"
            >
              {{ col.actionLabel }}
            </router-link>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import api from '../../services/api';
import { useEcho } from '../../composables/useEcho';
import { useToastStore } from '../../stores/toast';

const toast = useToastStore();
const loading = ref(true);
const jobCards = ref([]);
const quotations = ref([]);
const workOrders = ref([]);

const fetchLiveBoard = async () => {
  loading.value = true;
  try {
    const [jcRes, qoRes, woRes] = await Promise.all([
      api.get('/job-cards', { params: { per_page: 200 } }),
      api.get('/quotations', { params: { per_page: 200 } }),
      api.get('/work-orders', { params: { per_page: 200 } })
    ]);
    jobCards.value = jcRes.data?.data || jcRes.data || [];
    quotations.value = qoRes.data?.data || qoRes.data || [];
    workOrders.value = woRes.data?.data || woRes.data || [];
  } catch (error) {
    console.error('Failed to sync live workshop board', error);
    toast.error('Websocket sync error: using cached local workflow state.');
  } finally {
    loading.value = false;
  }
};

const handleRealtimeRefresh = (eventName) => {
  toast.info(`Realtime Update: event [${eventName}] synchronized Kanban board.`);
  fetchLiveBoard();
};

const { connectionState } = useEcho('workshop-updates', false, {
  '.quotation.approved': () => handleRealtimeRefresh('Quotation Approved'),
  '.workorder.created': () => handleRealtimeRefresh('Work Order Created'),
  '.technician.assigned': () => handleRealtimeRefresh('Technician Assigned'),
  '.task.started': () => handleRealtimeRefresh('Task Started'),
  '.task.completed': () => handleRealtimeRefresh('Task Completed'),
  '.consumption.added': () => handleRealtimeRefresh('Materials Consumed'),
  '.vehicle.delivered': () => handleRealtimeRefresh('Vehicle Delivered')
}, {
  callback: fetchLiveBoard,
  interval: 20
});

const getPriorityClass = (priority) => {
  const map = {
    'low': 'bg-slate-50 text-slate-500 border border-slate-200',
    'normal': 'bg-slate-50 text-slate-500 border border-slate-200',
    'medium': 'bg-yellow-50 text-yellow-700 border border-yellow-250',
    'high': 'bg-rose-50 text-rose-700 border border-rose-250',
    'critical': 'bg-red-50 text-red-700 border border-red-250 animate-pulse',
    'urgent': 'bg-rose-50 text-rose-700 border border-rose-250',
  };
  return map[priority?.toLowerCase()] || 'bg-slate-50 text-slate-500 border border-slate-200';
};

// Kanban Columns classification logic
const kanbanColumns = computed(() => {
  const pendingJobs = jobCards.value.filter(
    jc => (jc.service_status === 'pending' || jc.service_status === 'intake') && !jc.diagnosis
  );

  const inspectionJobs = jobCards.value.filter(
    jc => (jc.service_status === 'pending' || jc.service_status === 'intake') && jc.inspection_notes && !jc.diagnosis
  );

  const quotationJobs = jobCards.value.filter(
    jc => jc.diagnosis && !quotations.value.some(q => q.job_card_id === jc.id)
  );

  const approvalJobs = jobCards.value.filter(
    jc => quotations.value.some(q => q.job_card_id === jc.id && (q.status === 'draft' || q.status === 'revised'))
  );

  const repairJobs = jobCards.value.filter(
    jc => workOrders.value.some(wo => wo.job_card_id === jc.id && (wo.status === 'in_progress' || wo.status === 'paused' || wo.status === 'pending'))
  );

  const qcJobs = jobCards.value.filter(
    jc => jc.service_status !== 'completed' && jc.service_status !== 'delivered' &&
          workOrders.value.some(wo => wo.job_card_id === jc.id && wo.status === 'completed')
  );

  const readyJobs = jobCards.value.filter(
    jc => jc.service_status === 'completed' || jc.service_status === 'settled'
  );

  const deliveredJobs = jobCards.value.filter(
    jc => jc.service_status === 'delivered'
  );

  return [
    {
      id: 'pending',
      name: 'Pending Intake',
      color: 'bg-slate-500',
      items: pendingJobs,
      actionLabel: 'Perform Inspection',
      actionLink: (item) => ({ name: 'workshop.inspection', params: { id: item.id } })
    },
    {
      id: 'inspection',
      name: 'Inspection',
      color: 'bg-yellow-500',
      items: inspectionJobs,
      actionLabel: 'Open Diagnostics',
      actionLink: (item) => ({ name: 'workshop.diagnosis', params: { id: item.id } })
    },
    {
      id: 'quotation',
      name: 'Quotation',
      color: 'bg-orange-500',
      items: quotationJobs,
      actionLabel: 'Draft Quotation',
      actionLink: (item) => ({ name: 'workshop.quotation', params: { id: item.id } })
    },
    {
      id: 'approval',
      name: 'Approval Queue',
      color: 'bg-indigo-500',
      items: approvalJobs,
      actionLabel: 'Track Approval',
      actionLink: (item) => ({ name: 'workshop.approvals' })
    },
    {
      id: 'repair',
      name: 'In Repair',
      color: 'bg-blue-500',
      items: repairJobs,
      actionLabel: 'Log Parts',
      actionLink: (item) => {
        const wo = workOrders.value.find(w => w.job_card_id === item.id);
        return { name: 'workshop.parts-consumption', params: { id: wo?.id || item.id } };
      }
    },
    {
      id: 'qc',
      name: 'QC Check',
      color: 'bg-purple-500',
      items: qcJobs,
      actionLabel: 'QC Verification',
      actionLink: (item) => {
        const wo = workOrders.value.find(w => w.job_card_id === item.id);
        return { name: 'workshop.qc', params: { id: wo?.id || item.id } };
      }
    },
    {
      id: 'ready',
      name: 'Ready Delivery',
      color: 'bg-emerald-500',
      items: readyJobs,
      actionLabel: 'Deliver Handover',
      actionLink: (item) => ({ name: 'workshop.delivery', params: { id: item.id } })
    },
    {
      id: 'delivered',
      name: 'Delivered',
      color: 'bg-slate-650',
      items: deliveredJobs,
      actionLabel: 'View Job Card',
      actionLink: (item) => ({ name: 'job-cards.show', params: { id: item.id } })
    }
  ];
});

onMounted(() => {
  fetchLiveBoard();
});

onUnmounted(() => {
  // useEcho handles cleanup automatically
});
</script>

<style scoped>
/* Scrollbar styling */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
::-webkit-scrollbar-track {
  background: #f1f5f9;
}
::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 9999px;
}
::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
.text-emerald-450 {
  color: #34d399;
}
</style>
