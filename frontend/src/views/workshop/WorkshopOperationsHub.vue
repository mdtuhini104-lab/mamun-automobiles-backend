<template>
  <div class="space-y-8 p-6 bg-slate-900 min-h-screen text-slate-100">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 border-b border-slate-800 pb-6">
      <div>
        <h1 class="text-3xl font-black text-white tracking-tight flex items-center gap-3">
          Workshop Operations Hub
          <span class="flex h-3.5 w-3.5 relative">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-indigo-500"></span>
          </span>
        </h1>
        <p class="text-sm text-slate-400 mt-1">Real-time daily workshop execution cockpit. Monitor vehicle flow from frontdesk intake to delivery handover.</p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <!-- Realtime Websocket State Badge -->
        <div class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl border bg-slate-950/80 transition-all duration-300" :class="getConnectionBorderClass(connectionState)">
          <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" :class="getConnectionBgClass(connectionState)"></span>
            <span class="relative inline-flex rounded-full h-2 w-2" :class="getConnectionBgClass(connectionState)"></span>
          </span>
          <span class="text-[9px] font-black uppercase tracking-widest text-slate-300">
            WS: {{ connectionState }}
          </span>
        </div>

        <router-link
          :to="{ name: 'workshop.intake' }"
          class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition duration-200 shadow-md font-bold text-xs flex items-center gap-2"
        >
          <span class="text-base font-extrabold">+</span> Frontdesk Reception Intake
        </router-link>
        <button 
          @click="fetchData" 
          class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl transition duration-200 border border-slate-700 shadow-md font-bold text-xs flex items-center gap-2 focus:outline-none"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
          Sync Queues
        </button>
      </div>
    </div>

    <!-- Live Queue Horizon (Horizontal Scrolling Kanban Board) -->
    <div class="flex overflow-x-auto gap-6 pb-6 scrollbar-thin scrollbar-thumb-slate-800 scrollbar-track-slate-950 min-h-[70vh]">
      
      <!-- Queue Column Template -->
      <div 
        v-for="queue in queues" 
        :key="queue.id"
        class="flex-shrink-0 w-80 bg-slate-950/40 border border-slate-800/80 rounded-3xl p-4 flex flex-col justify-between gap-4 h-[650px]"
      >
        <!-- Column Header -->
        <div class="flex items-center justify-between border-b border-slate-850 pb-3">
          <div class="flex items-center gap-2.5">
            <span :class="queue.indicatorColor" class="w-2.5 h-2.5 rounded-full shrink-0"></span>
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-350">{{ queue.name }}</h3>
          </div>
          <span class="bg-slate-800 text-slate-300 text-[10px] font-black px-2.5 py-0.5 rounded-full">
            {{ queue.items.length }}
          </span>
        </div>

        <!-- Column Body (Scrollable items list) -->
        <div class="flex-1 overflow-y-auto space-y-3.5 pr-1 scrollbar-thin">
          <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-2">
            <div class="w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
            <span class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Loading...</span>
          </div>

          <div v-else-if="queue.items.length === 0" class="flex flex-col items-center justify-center py-24 text-center border border-dashed border-slate-850 rounded-2xl p-4">
            <span class="text-2xl mb-2">✔</span>
            <p class="text-[10px] text-slate-650 font-bold uppercase tracking-wider">Queue Empty</p>
          </div>

          <!-- Queue Cards -->
          <div 
            v-else
            v-for="item in queue.items" 
            :key="item.id"
            class="bg-slate-900/90 border border-slate-800/60 rounded-2xl p-4 hover:border-slate-700/80 transition-all flex flex-col justify-between gap-4 shadow-sm"
          >
            <div>
              <div class="flex justify-between items-start gap-2">
                <span class="text-[9px] font-extrabold text-slate-500 font-mono">
                  ID: #{{ item.idCode }}
                </span>
                <span 
                  v-if="item.priority" 
                  :class="getPriorityClass(item.priority)" 
                  class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest"
                >
                  {{ item.priority }}
                </span>
              </div>

              <h4 class="font-extrabold text-white text-sm mt-2">
                {{ item.title }}
              </h4>
              <p class="text-[10px] text-slate-400 font-mono mt-0.5">
                Plate: {{ item.plate }}
              </p>

              <!-- Secondary Details -->
              <div class="mt-3 space-y-1.5 border-t border-slate-850 pt-3 text-[10px] text-slate-400">
                <div class="flex justify-between">
                  <span>Customer:</span>
                  <span class="font-semibold text-slate-350 truncate max-w-[150px]">{{ item.customerName }}</span>
                </div>
                <div v-if="item.bayName" class="flex justify-between">
                  <span>Bay:</span>
                  <span class="font-semibold text-emerald-450">{{ item.bayName }}</span>
                </div>
                <div v-if="item.assignedTo" class="flex justify-between">
                  <span>Technician:</span>
                  <span class="font-semibold text-indigo-400">{{ item.assignedTo }}</span>
                </div>
                <div v-if="item.cost" class="flex justify-between">
                  <span>Grand Total:</span>
                  <span class="font-mono font-bold text-slate-200">৳{{ item.cost }}</span>
                </div>
              </div>
            </div>

            <!-- Action Button -->
            <router-link
              :to="queue.actionLink(item)"
              class="w-full py-2 bg-slate-800 hover:bg-slate-755 text-center text-white rounded-xl font-black text-[10px] uppercase tracking-wider transition-colors border border-slate-700/40"
            >
              {{ queue.actionLabel }}
            </router-link>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';
import { useEcho } from '../../composables/useEcho';

const toast = useToastStore();
const loading = ref(false);

const handleRealtimeRefresh = (eventName) => {
  toast.info(`Realtime Sync: operational event [${eventName}] triggered update`);
  fetchData();
};

const { connectionState } = useEcho('workshop-updates', false, {
  '.quotation.approved': () => handleRealtimeRefresh('Quotation Approved'),
  '.workorder.created': () => handleRealtimeRefresh('Work Order Created'),
  '.technician.assigned': () => handleRealtimeRefresh('Technician Assigned'),
  '.task.started': () => handleRealtimeRefresh('Task Started'),
  '.task.completed': () => handleRealtimeRefresh('Task Completed'),
  '.consumption.added': () => handleRealtimeRefresh('Materials Consumed'),
  '.vehicle.delivered': () => handleRealtimeRefresh('Vehicle Delivered'),
});

const getConnectionBorderClass = (state) => {
  if (state === 'Connected') return 'border-emerald-500/30';
  if (state === 'Reconnecting') return 'border-amber-500/30';
  if (state === 'Degraded') return 'border-orange-500/30';
  if (state === 'Recovering') return 'border-blue-500/30';
  return 'border-rose-500/30';
};

const getConnectionBgClass = (state) => {
  if (state === 'Connected') return 'bg-emerald-500';
  if (state === 'Reconnecting') return 'bg-amber-500';
  if (state === 'Degraded') return 'bg-orange-500';
  if (state === 'Recovering') return 'bg-blue-500';
  return 'bg-rose-500';
};

const jobCards = ref([]);
const quotations = ref([]);
const workOrders = ref([]);
const comebacks = ref([]);

const fetchData = async () => {
  loading.value = true;
  try {
    const [jcRes, qoRes, woRes, cbRes] = await Promise.all([
      api.get('/job-cards', { params: { per_page: 200 } }),
      api.get('/quotations', { params: { per_page: 200 } }),
      api.get('/work-orders', { params: { per_page: 200 } }),
      api.get('/comebacks')
    ]);

    jobCards.value = jcRes.data?.data || jcRes.data || [];
    quotations.value = qoRes.data?.data || qoRes.data || [];
    workOrders.value = woRes.data?.data || woRes.data || [];
    comebacks.value = cbRes.data?.data || cbRes.data || [];
  } catch (error) {
    console.error('Failed to sync workflow hub queues', error);
    toast.error('Network synchronization failed. Using offline cached states.');
  } finally {
    loading.value = false;
  }
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

// Queue Definitions & Sorting Calculations
const queues = computed(() => {
  // 1. Waiting Inspection
  const waitingInspectionItems = jobCards.value.filter(
    jc => jc.service_status === 'pending' && !jc.diagnosis
  ).map(jc => ({
    id: jc.id,
    idCode: `JC-${String(jc.id).padStart(5, '0')}`,
    title: `${jc.vehicle?.make || 'Toyota'} ${jc.vehicle?.model || 'Allion'}`,
    plate: jc.vehicle?.license_plate || jc.vehicle?.registration_no || 'Pending License',
    customerName: jc.customer?.name || 'Walk-in Client',
    priority: jc.priority_level || 'normal'
  }));

  // 2. Waiting Quotation
  const waitingQuotationItems = jobCards.value.filter(
    jc => jc.service_status === 'pending' && jc.diagnosis && !quotations.value.some(q => q.job_card_id === jc.id)
  ).map(jc => ({
    id: jc.id,
    idCode: `JC-${String(jc.id).padStart(5, '0')}`,
    title: `${jc.vehicle?.make || 'Toyota'} ${jc.vehicle?.model || 'Allion'}`,
    plate: jc.vehicle?.license_plate || jc.vehicle?.registration_no || 'Pending License',
    customerName: jc.customer?.name || 'Walk-in Client',
    priority: jc.priority_level || 'normal'
  }));

  // 3. Waiting Approval
  const waitingApprovalItems = quotations.value.filter(
    q => q.status === 'draft' || q.status === 'revised'
  ).map(q => ({
    id: q.id,
    idCode: q.quotation_number,
    title: `${q.job_card?.vehicle?.make || 'Vehicle'} ${q.job_card?.vehicle?.model || ''}`,
    plate: q.job_card?.vehicle?.license_plate || q.job_card?.vehicle?.registration_no || 'N/A',
    customerName: q.job_card?.customer?.name || 'Customer',
    cost: q.grand_total,
    priority: q.job_card?.priority_level || 'normal'
  }));

  // 4. Waiting Bay
  const waitingBayItems = workOrders.value.filter(
    wo => wo.status === 'pending' && !wo.job_card?.workshop_bay_id
  ).map(wo => ({
    id: wo.id,
    idCode: wo.work_order_number,
    title: `${wo.job_card?.vehicle?.make || 'Vehicle'} ${wo.job_card?.vehicle?.model || ''}`,
    plate: wo.job_card?.vehicle?.license_plate || wo.job_card?.vehicle?.registration_no || 'N/A',
    customerName: wo.job_card?.customer?.name || 'Customer',
    priority: wo.job_card?.priority_level || 'normal'
  }));

  // 5. Waiting Parts
  const waitingPartsItems = workOrders.value.filter(
    wo => wo.status === 'paused' || (wo.status === 'pending' && wo.notes?.toLowerCase().includes('parts'))
  ).map(wo => ({
    id: wo.id,
    idCode: wo.work_order_number,
    title: `${wo.job_card?.vehicle?.make || 'Vehicle'} ${wo.job_card?.vehicle?.model || ''}`,
    plate: wo.job_card?.vehicle?.license_plate || wo.job_card?.vehicle?.registration_no || 'N/A',
    customerName: wo.job_card?.customer?.name || 'Customer',
    priority: wo.job_card?.priority_level || 'normal'
  }));

  // 6. In Progress
  const inProgressItems = workOrders.value.filter(
    wo => wo.status === 'in_progress'
  ).map(wo => ({
    id: wo.id,
    idCode: wo.work_order_number,
    title: `${wo.job_card?.vehicle?.make || 'Vehicle'} ${wo.job_card?.vehicle?.model || ''}`,
    plate: wo.job_card?.vehicle?.license_plate || wo.job_card?.vehicle?.registration_no || 'N/A',
    customerName: wo.job_card?.customer?.name || 'Customer',
    bayName: wo.job_card?.workshop_bay?.name,
    assignedTo: wo.job_card?.mechanic?.name,
    priority: wo.job_card?.priority_level || 'normal'
  }));

  // 7. Waiting QC
  const waitingQcItems = workOrders.value.filter(
    wo => wo.status === 'completed' && wo.job_card?.service_status !== 'completed' && wo.job_card?.service_status !== 'delivered'
  ).map(wo => ({
    id: wo.id,
    idCode: wo.work_order_number,
    title: `${wo.job_card?.vehicle?.make || 'Vehicle'} ${wo.job_card?.vehicle?.model || ''}`,
    plate: wo.job_card?.vehicle?.license_plate || wo.job_card?.vehicle?.registration_no || 'N/A',
    customerName: wo.job_card?.customer?.name || 'Customer',
    priority: wo.job_card?.priority_level || 'normal'
  }));

  // 8. Ready Delivery
  const readyDeliveryItems = jobCards.value.filter(
    jc => jc.service_status === 'completed'
  ).map(jc => ({
    id: jc.id,
    idCode: `JC-${String(jc.id).padStart(5, '0')}`,
    title: `${jc.vehicle?.make || 'Toyota'} ${jc.vehicle?.model || 'Allion'}`,
    plate: jc.vehicle?.license_plate || jc.vehicle?.registration_no || 'Pending License',
    customerName: jc.customer?.name || 'Customer',
    priority: jc.priority_level || 'normal'
  }));

  // 9. Delivered
  const deliveredItems = jobCards.value.filter(
    jc => jc.service_status === 'delivered'
  ).map(jc => ({
    id: jc.id,
    idCode: `JC-${String(jc.id).padStart(5, '0')}`,
    title: `${jc.vehicle?.make || 'Toyota'} ${jc.vehicle?.model || 'Allion'}`,
    plate: jc.vehicle?.license_plate || jc.vehicle?.registration_no || 'Pending License',
    customerName: jc.customer?.name || 'Customer',
    priority: jc.priority_level || 'normal'
  }));

  // 10. Comeback Vehicles
  const comebackItems = comebacks.value.map(cb => ({
    id: cb.id,
    idCode: `CB-${String(cb.id).padStart(4, '0')}`,
    title: `Repeat Fault: ${cb.original_job_card?.vehicle?.make || 'Vehicle'} ${cb.original_job_card?.vehicle?.model || ''}`,
    plate: cb.original_job_card?.vehicle?.license_plate || cb.original_job_card?.vehicle?.registration_no || 'N/A',
    customerName: cb.original_job_card?.customer?.name || 'Customer',
    assignedTo: cb.technician_at_fault?.name || 'Fault Technician'
  }));

  return [
    {
      id: 'waiting_inspection',
      name: 'Waiting Inspection',
      indicatorColor: 'bg-yellow-500',
      actionLabel: 'Perform Inspection',
      items: waitingInspectionItems,
      actionLink: (item) => ({ name: 'workshop.inspection', params: { id: item.id } })
    },
    {
      id: 'waiting_quotation',
      name: 'Waiting Quotation',
      indicatorColor: 'bg-orange-500',
      actionLabel: 'Draft Quotation',
      items: waitingQuotationItems,
      actionLink: (item) => ({ name: 'workshop.quotation', params: { id: item.id } })
    },
    {
      id: 'waiting_approval',
      name: 'Waiting Approval',
      indicatorColor: 'bg-indigo-500',
      actionLabel: 'Track Approval',
      items: waitingApprovalItems,
      actionLink: (item) => ({ name: 'workshop.approvals' })
    },
    {
      id: 'waiting_bay',
      name: 'Waiting Bay',
      indicatorColor: 'bg-amber-600',
      actionLabel: 'Allocate Bay',
      items: waitingBayItems,
      actionLink: (item) => ({ name: 'workshop.bays' })
    },
    {
      id: 'waiting_parts',
      name: 'Waiting Parts',
      indicatorColor: 'bg-rose-500',
      actionLabel: 'Log Consumption',
      items: waitingPartsItems,
      actionLink: (item) => ({ name: 'workshop.parts-consumption', params: { id: item.id } })
    },
    {
      id: 'in_progress',
      name: 'In Progress',
      indicatorColor: 'bg-blue-500',
      actionLabel: 'Manage Tasks',
      items: inProgressItems,
      actionLink: (item) => ({ name: 'workshop.work-orders' })
    },
    {
      id: 'waiting_qc',
      name: 'Waiting QC',
      indicatorColor: 'bg-purple-500',
      actionLabel: 'Perform QC',
      items: waitingQcItems,
      actionLink: (item) => ({ name: 'workshop.qc', params: { id: item.id } })
    },
    {
      id: 'ready_delivery',
      name: 'Ready Delivery',
      indicatorColor: 'bg-emerald-500',
      actionLabel: 'Deliver & Settle',
      items: readyDeliveryItems,
      actionLink: (item) => ({ name: 'workshop.delivery', params: { id: item.id } })
    },
    {
      id: 'delivered',
      name: 'Delivered',
      indicatorColor: 'bg-slate-500',
      actionLabel: 'View Handover',
      items: deliveredItems,
      actionLink: (item) => ({ name: 'job-cards.show', params: { id: item.id } })
    },
    {
      id: 'comeback_vehicles',
      name: 'Comeback Vehicles',
      indicatorColor: 'bg-red-500 animate-pulse',
      actionLabel: 'View Comebacks',
      items: comebackItems,
      actionLink: (item) => ({ name: 'workshop.warranty-comeback' })
    }
  ];
});

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
/* Modern visual custom styling */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
::-webkit-scrollbar-track {
  background: #020617;
}
::-webkit-scrollbar-thumb {
  background: #1e293b;
  border-radius: 9999px;
}
::-webkit-scrollbar-thumb:hover {
  background: #334155;
}
</style>
