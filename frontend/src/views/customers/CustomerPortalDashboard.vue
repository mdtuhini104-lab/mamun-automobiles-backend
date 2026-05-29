<template>
  <div class="min-h-screen bg-slate-900 text-slate-100 flex flex-col font-sans">
    <!-- Header -->
    <header class="bg-slate-950 border-b border-slate-800 p-4 sticky top-0 z-30 shadow-md">
      <div class="max-w-4xl mx-auto flex justify-between items-center">
        <div class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center font-black">
            MA
          </div>
          <div>
            <h1 class="text-sm font-black text-white tracking-tight">Mamun Automobiles</h1>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Customer Portal</p>
          </div>
        </div>
        <span class="text-xs bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-extrabold px-3 py-1 rounded-xl">
          ✓ Portal Connected
        </span>
      </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1 max-w-4xl w-full mx-auto p-4 md:p-6 space-y-6">
      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-24 gap-3">
        <div class="w-10 h-10 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
        <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Loading secure portal data...</p>
      </div>

      <div v-else class="space-y-6">
        <!-- Customer & Vehicle Info -->
        <div class="bg-slate-950 border border-slate-800 rounded-3xl p-5 shadow-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div>
            <h2 class="text-xs font-bold text-indigo-400 uppercase tracking-widest">Welcome Back</h2>
            <p class="text-lg font-black text-white mt-1">{{ customer.name }}</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ customer.phone }} | {{ customer.email }}</p>
          </div>
          
          <div class="bg-slate-900 border border-slate-800 p-3.5 rounded-2xl flex items-center gap-3">
            <!-- Mock QR code vehicle lookup -->
            <div class="w-12 h-12 bg-white p-1 rounded-xl shrink-0 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-full h-full text-slate-900">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 15h.008v.008H15V15zm0 2.25h.008v.008H15v-.008zM17.25 15h.008v.008H17.25V15zm0 2.25h.008v.008H17.25v-.008zM15 19.5h.008v.008H15V19.5zm0-4.5h.008v.008H15v-.008zm2.25 4.5h.008v.008H17.25V19.5zm2.25-2.25h.008v.008H19.5v-.008zM19.5 15h.008v.008H19.5V15zm0 4.5h.008v.008H19.5V19.5z" />
              </svg>
            </div>
            <div>
              <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Active Vehicle</span>
              <span class="text-xs font-black text-white leading-tight block mt-0.5">{{ vehicleName }}</span>
            </div>
          </div>
        </div>

        <!-- Section 1: Live Repair Progress Tracking -->
        <div v-if="repairStatus" class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
          <div class="border-b border-slate-800 pb-4 flex justify-between items-center">
            <div>
              <h3 class="text-sm font-black text-white uppercase tracking-wider">Live Repair Status Tracker</h3>
              <p class="text-[10px] text-slate-400 mt-0.5">Realtime updates from the workshop bay</p>
            </div>
            <span class="px-2.5 py-0.5 rounded bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 text-[10px] font-extrabold uppercase tracking-widest">
              {{ repairStatus.service_status }}
            </span>
          </div>

          <!-- Dynamic Timeline -->
          <div class="grid grid-cols-5 gap-2 text-center text-[10px] font-bold">
            <div 
              v-for="(stage, idx) in stagesList" 
              :key="idx" 
              class="space-y-2 flex flex-col items-center"
            >
              <div 
                class="w-8 h-8 rounded-full flex items-center justify-center border-2 transition-all duration-300"
                :class="getStageCircleClass(idx)"
              >
                {{ idx + 1 }}
              </div>
              <span :class="getStageTextClass(idx)">{{ stage }}</span>
            </div>
          </div>

          <!-- Tasks list -->
          <div class="space-y-2 bg-slate-900 border border-slate-850 p-4 rounded-2xl">
            <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Repair Checklist Progression</span>
            <div 
              v-for="task in repairStatus.tasks" 
              :key="task.name"
              class="flex items-center justify-between text-xs py-1.5 border-b border-slate-800 last:border-b-0"
            >
              <span class="text-slate-300 font-medium">{{ task.name }}</span>
              <span 
                class="px-2 py-0.5 rounded font-extrabold text-[9px] uppercase tracking-wider"
                :class="task.is_completed ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400'"
              >
                {{ task.status }}
              </span>
            </div>
          </div>
        </div>

        <!-- Section 2: Quotation Sheet & Items Approvals -->
        <div v-if="quotation && quotation.status !== 'approved'" class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
          <div class="border-b border-slate-800 pb-4">
            <h3 class="text-sm font-black text-white uppercase tracking-wider">Quotation Sheet Approval Center</h3>
            <p class="text-[10px] text-slate-400 mt-0.5">Please review estimate costs and approve line items to begin work</p>
          </div>

          <div class="space-y-4">
            <div 
              v-for="item in quotation.items" 
              :key="item.id"
              class="flex items-center justify-between p-4 bg-slate-900 border border-slate-850 rounded-2xl gap-4 hover:border-slate-800 transition"
            >
              <div class="flex items-center gap-3">
                <input 
                  type="checkbox" 
                  :value="item.id" 
                  v-model="approvedItems"
                  class="w-4 h-4 rounded text-indigo-500 focus:ring-indigo-500 border-slate-700 bg-slate-800"
                />
                <div>
                  <span class="block text-xs font-black text-white">
                    {{ item.item_type === 'product' ? (item.part?.name || 'Part') : item.service_name }}
                  </span>
                  <span class="text-[10px] text-slate-400 mt-0.5 block">
                    {{ item.item_type === 'product' ? 'Workshop Supplied Part' : 'Service Labor' }}
                  </span>
                </div>
              </div>
              <span class="text-xs font-black text-slate-200">
                ৳{{ item.item_type === 'product' ? item.quantity * item.unit_price : item.labor_cost }}
              </span>
            </div>
          </div>

          <!-- Summary and Approval action -->
          <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4 bg-slate-900 border border-slate-850 p-4 rounded-2xl">
            <div>
              <span class="block text-[10px] text-slate-400 uppercase tracking-widest">Total Cost (Selected Items)</span>
              <span class="text-lg font-black text-white mt-1">৳{{ selectedTotalCost }}</span>
            </div>
            
            <div class="flex gap-2">
              <button 
                @click="submitApproval('approved')"
                class="px-5 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-md transition"
              >
                Approve Selected Lines
              </button>
              <button 
                @click="submitApproval('rejected')"
                class="px-4 py-3 bg-slate-800 hover:bg-slate-750 text-slate-300 font-bold text-xs rounded-xl transition"
              >
                Reject All
              </button>
            </div>
          </div>
        </div>

        <!-- Section 3: Billing & Invoice Download -->
        <div v-if="invoice" class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-6">
          <div class="space-y-1">
            <div class="flex items-center gap-2">
              <h3 class="text-sm font-black text-white uppercase tracking-wider">Invoice: {{ invoice.invoice_number }}</h3>
              <span 
                class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider"
                :class="invoice.payment_status === 'paid' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400'"
              >
                {{ invoice.payment_status }}
              </span>
            </div>
            <p class="text-xs text-slate-400">Total Billed: ৳{{ invoice.grand_total }} | Remaining Due: ৳{{ invoice.due_amount }}</p>
          </div>

          <a 
            :href="`/api/v1/print/invoice/${invoice.id}`" 
            target="_blank"
            class="px-5 py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow-md transition text-center"
          >
            Download Invoice PDF
          </a>
        </div>

        <!-- Section 4: Vehicle Service History Timeline -->
        <div v-if="timelineData.length > 0" class="bg-slate-950 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
          <div>
            <h3 class="text-sm font-black text-white uppercase tracking-wider">Vehicle Service History Timeline</h3>
            <p class="text-[10px] text-slate-400 mt-0.5">Chronological interactive timeline records</p>
          </div>

          <div class="relative border-l-2 border-slate-800 pl-6 space-y-6">
            <div 
              v-for="(item, idx) in timelineData" 
              :key="idx"
              class="relative"
            >
              <!-- Timeline node circle -->
              <span class="absolute -left-[31px] top-1.5 w-3 h-3 rounded-full border-2 border-slate-800 bg-slate-900"></span>

              <div class="bg-slate-900 border border-slate-850 p-4 rounded-2xl space-y-2 hover:border-slate-800 transition">
                <div class="flex justify-between items-center">
                  <span class="text-xs font-black text-white">{{ item.title }}</span>
                  <span class="text-[10px] text-slate-400">{{ item.date }}</span>
                </div>
                <p class="text-[11px] text-slate-400 leading-relaxed">{{ item.description }}</p>
                <div v-if="item.mileage" class="text-[9px] bg-slate-950 px-2 py-0.5 rounded w-max text-slate-400 font-mono">
                  Odometer: {{ item.mileage }} KM
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';

const route = useRoute();
const toast = useToastStore();

const loading = ref(true);
const customer = ref({});
const invoice = ref(null);
const repairStatus = ref(null);
const quotation = ref(null);
const timelineData = ref([]);
const approvedItems = ref([]);

const stagesList = ['Job Intake', 'Diagnostics', 'In Progress', 'QC Review', 'Ready'];

const vehicleName = computed(() => {
  return repairStatus.value?.vehicle || 'Premium Vehicle';
});

const getStageIndex = () => {
  if (!repairStatus.value) return 0;
  const status = repairStatus.value.service_status;
  if (status === 'completed' || status === 'delivered') return 4;
  if (status === 'qc') return 3;
  if (status === 'in_progress') return 2;
  if (status === 'diagnosed') return 1;
  return 0; // pending/intake
};

const getStageCircleClass = (idx) => {
  const activeIdx = getStageIndex();
  if (idx === activeIdx) return 'bg-indigo-500 border-indigo-500 text-white animate-pulse';
  if (idx < activeIdx) return 'bg-emerald-500/10 border-emerald-500 text-emerald-400';
  return 'bg-slate-900 border-slate-800 text-slate-500';
};

const getStageTextClass = (idx) => {
  const activeIdx = getStageIndex();
  if (idx === activeIdx) return 'text-indigo-400 font-extrabold';
  if (idx < activeIdx) return 'text-emerald-500';
  return 'text-slate-500';
};

const selectedTotalCost = computed(() => {
  if (!quotation.value || !quotation.value.items) return 0;
  return quotation.value.items
    .filter(item => approvedItems.value.includes(item.id))
    .reduce((sum, item) => sum + (item.item_type === 'product' ? item.quantity * item.unit_price : item.labor_cost), 0);
});

const loadPortalData = async () => {
  loading.value = true;
  try {
    const uuid = route.params.uuid;
    // 1. Authenticate with secure public UUID
    const response = await api.get(`/portal/access/${uuid}`);
    const data = response.data;
    
    // Set customer and invoice
    customer.value = data.customer;
    invoice.value = data.invoice;
    
    // Save token to localStorage for subsequent API auth headers
    if (data.token) {
      localStorage.setItem('token', data.token);
    }

    // 2. Load repair tracking details
    if (invoice.value.job_card_id) {
      const repairResp = await api.get(`/portal/repair-status/${invoice.value.job_card_id}`);
      repairStatus.value = repairResp.data;
    }

    // 3. Load active quotation
    const quoteResp = await api.get(`/quotations?job_card_id=${invoice.value.job_card_id}`);
    if (quoteResp.data?.data?.length > 0) {
      const qId = quoteResp.data.data[0].id;
      const quoteDetails = await api.get(`/portal/quotations/${qId}`);
      quotation.value = quoteDetails.data;
      approvedItems.value = quotation.value.items.map(i => i.id);
    }

    // 4. Load vehicle history timeline
    if (repairStatus.value) {
      const historyResp = await api.get(`/portal/vehicles/${quotation.value?.job_card?.vehicle_id || 1}/history`);
      timelineData.value = historyResp.data?.timeline || [];
    }

  } catch (error) {
    console.error('Portal failed to fetch details', error);
    toast.error('Portal link is invalid or expired. Check communication log.');
  } finally {
    loading.value = false;
  }
};

const submitApproval = async (status) => {
  try {
    await api.post(`/portal/quotations/${quotation.value.id}/approve`, {
      status: status,
      approved_items: status === 'approved' ? approvedItems.value : [],
      approved_by: customer.value.name,
      notes: 'Customer submitted action choices via self-service portal.',
    });
    
    toast.success('Your decisions have been submitted and logged to repair shop.');
    loadPortalData();
  } catch (error) {
    console.error('Failed to submit approval decisions', error);
  }
};

onMounted(() => {
  loadPortalData();
});
</script>

<style scoped>
</style>
