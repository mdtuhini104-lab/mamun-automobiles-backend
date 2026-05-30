<template>
  <div class="max-w-7xl mx-auto space-y-6 p-6 bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl text-slate-100 min-h-screen">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-slate-850 pb-5">
      <div class="flex items-center space-x-4">
        <router-link :to="{ name: 'workshop.hub' }" class="text-slate-400 hover:text-slate-200 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
        </router-link>
        <div>
          <h1 class="text-2xl font-black tracking-tight text-white uppercase">Warranty & Repeat Repair (Comeback) Deck</h1>
          <p class="text-xs text-slate-400 mt-1">Audit active service warranties, log return repeat repairs (comeback jobs), and monitor technician quality fault indexes.</p>
        </div>
      </div>
      <button 
        @click="showComebackModal = true" 
        class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-black text-xs uppercase tracking-wider transition"
      >
        File Repeat Comeback Log
      </button>
    </div>

    <!-- Mode Selector Tabs -->
    <div class="flex border-b border-slate-800 gap-6">
      <button 
        @click="activeTab = 'warranties'"
        class="pb-4 text-xs font-black uppercase tracking-wider transition-all focus:outline-none relative"
        :class="activeTab === 'warranties' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-slate-400 hover:text-slate-200'"
      >
        Active Service Warranties ({{ warrantiesList.length }})
      </button>
      <button 
        @click="activeTab = 'comebacks'"
        class="pb-4 text-xs font-black uppercase tracking-wider transition-all focus:outline-none relative"
        :class="activeTab === 'comebacks' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-slate-400 hover:text-slate-200'"
      >
        Repeat Repairs (Comeback Jobs) ({{ comebacksList.length }})
      </button>
    </div>

    <!-- Tab Contents -->
    <div class="grid grid-cols-1 gap-6">
      <div v-if="loading" class="flex justify-center py-20">
        <div class="w-8 h-8 border-3 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
      </div>

      <!-- 1. Warranties List -->
      <div v-else-if="activeTab === 'warranties'" class="bg-slate-950/20 border border-slate-850 rounded-2xl p-5 shadow-xl">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="border-b border-slate-850 text-slate-500 font-bold uppercase text-[9px] tracking-wider">
                <th class="pb-3 pl-2">Job Card</th>
                <th class="pb-3">Invoice Ref</th>
                <th class="pb-3">Customer</th>
                <th class="pb-3">Vehicle</th>
                <th class="pb-3">Warranty Expiry</th>
                <th class="pb-3">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-850">
              <tr v-for="w in warrantiesList" :key="w.id" class="text-slate-350">
                <td class="py-3.5 pl-2 font-bold text-white font-mono">
                  #JC-{{ String(w.job_card_id).padStart(5, '0') }}
                </td>
                <td class="py-3.5 font-mono text-indigo-400">{{ w.invoice?.invoice_number || '-' }}</td>
                <td class="py-3.5">{{ w.job_card?.customer?.name }}</td>
                <td class="py-3.5">
                  {{ w.job_card?.vehicle?.make }} {{ w.job_card?.vehicle?.model }} 
                  <span class="text-[9px] font-mono text-slate-500">({{ w.job_card?.vehicle?.license_plate || w.job_card?.vehicle?.registration_no }})</span>
                </td>
                <td class="py-3.5 font-mono">{{ formatDate(w.warranty_expiry_date) }}</td>
                <td class="py-3.5">
                  <span :class="getWarrantyStatusClass(w.status)" class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider">
                    {{ w.status }}
                  </span>
                </td>
              </tr>
              <tr v-if="warrantiesList.length === 0">
                <td colspan="6" class="py-6 text-center text-slate-500 italic">No service warranties active.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 2. Comebacks List -->
      <div v-else-if="activeTab === 'comebacks'" class="bg-slate-950/20 border border-slate-850 rounded-2xl p-5 shadow-xl">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="border-b border-slate-850 text-slate-500 font-bold uppercase text-[9px] tracking-wider">
                <th class="pb-3 pl-2">Log Date</th>
                <th class="pb-3">Original JC</th>
                <th class="pb-3">Repeat JC</th>
                <th class="pb-3">Returning Reason</th>
                <th class="pb-3">Technician At Fault</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-850">
              <tr v-for="cb in comebacksList" :key="cb.id" class="text-slate-350">
                <td class="py-3.5 pl-2 font-mono text-[10px] text-slate-500">
                  {{ formatDate(cb.created_at) }}
                </td>
                <td class="py-3.5 font-bold font-mono text-white">
                  #JC-{{ String(cb.original_job_card_id).padStart(5, '0') }}
                </td>
                <td class="py-3.5 font-bold font-mono text-indigo-400">
                  #JC-{{ String(cb.comeback_job_card_id).padStart(5, '0') }}
                </td>
                <td class="py-3.5 max-w-xs truncate italic text-slate-300">
                  "{{ cb.reason }}"
                </td>
                <td class="py-3.5 text-rose-400 font-bold">
                  {{ cb.technician_at_fault?.name || 'Workshop Team / Shared' }}
                </td>
              </tr>
              <tr v-if="comebacksList.length === 0">
                <td colspan="5" class="py-6 text-center text-slate-500 italic">No repeat repair (comeback) logs registered.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- File Repeat Comeback Modal -->
    <div v-if="showComebackModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm animate-in fade-in">
      <div class="bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl max-w-lg w-full p-6 space-y-4 text-slate-100">
        <div class="flex justify-between items-center border-b border-slate-850 pb-3">
          <h3 class="text-sm font-black uppercase tracking-widest text-rose-400">File Repeat Comeback Log</h3>
          <button @click="showComebackModal = false" class="text-slate-400 hover:text-slate-200">✕</button>
        </div>

        <form @submit.prevent="submitComeback" class="space-y-4 text-xs">
          <!-- Original Job Card -->
          <div>
            <label class="block text-[10px] text-slate-400 mb-1">Select Original Job Card (Completed) *</label>
            <select v-model="comebackForm.original_job_card_id" required class="w-full text-xs bg-slate-850 border border-slate-750 rounded-lg p-2.5 text-white">
              <option value="">Select Completed Job Card...</option>
              <option v-for="jc in completedJobs" :key="jc.id" :value="jc.id">
                #JC-{{ String(jc.id).padStart(5, '0') }} - {{ jc.customer?.name }} ({{ jc.vehicle?.make }} {{ jc.vehicle?.model }} - {{ jc.vehicle?.license_plate }})
              </option>
            </select>
          </div>

          <!-- Comeback Job Card -->
          <div>
            <label class="block text-[10px] text-slate-400 mb-1">Select New Comeback Job Card (Pending) *</label>
            <select v-model="comebackForm.comeback_job_card_id" required class="w-full text-xs bg-slate-850 border border-slate-750 rounded-lg p-2.5 text-white">
              <option value="">Select New Pending Check-in...</option>
              <option v-for="jc in pendingJobs" :key="jc.id" :value="jc.id">
                #JC-{{ String(jc.id).padStart(5, '0') }} - {{ jc.customer?.name }} ({{ jc.vehicle?.make }} {{ jc.vehicle?.model }} - {{ jc.vehicle?.license_plate }})
              </option>
            </select>
          </div>

          <!-- Faulty Technician -->
          <div>
            <label class="block text-[10px] text-slate-400 mb-1">Technician At Fault (For QA statistics) *</label>
            <select v-model="comebackForm.technician_at_fault_id" required class="w-full text-xs bg-slate-850 border border-slate-750 rounded-lg p-2.5 text-white">
              <option value="">Select Original Technician...</option>
              <option v-for="t in techniciansList" :key="t.id" :value="t.id">
                {{ t.name }}
              </option>
            </select>
          </div>

          <!-- Reason -->
          <div>
            <label class="block text-[10px] text-slate-400 mb-1">Comeback/Return Reason * (Min 10 characters)</label>
            <textarea
              v-model="comebackForm.reason"
              required
              rows="3"
              placeholder="e.g. Front brake squeaking has returned after 5 days of service. Original brake caliper overhaul failed."
              class="w-full text-xs bg-slate-850 border border-slate-750 rounded-lg p-2.5 text-white"
            ></textarea>
            <p v-if="comebackForm.reason.length > 0 && comebackForm.reason.length < 10" class="text-[9px] text-rose-500 font-bold">Reason is too short! Supply at least 10 characters.</p>
          </div>

          <div class="flex justify-end gap-3 pt-3 border-t border-slate-850">
            <button type="button" @click="showComebackModal = false" class="px-4 py-2 border border-slate-700 rounded-xl text-xs font-bold text-slate-400 hover:bg-slate-805">Cancel</button>
            <button 
              type="submit" 
              :disabled="saving || comebackForm.reason.length < 10"
              class="px-5 py-2 bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-white rounded-xl text-xs font-black uppercase tracking-wider"
            >
              {{ saving ? 'Submitting...' : 'Log Repeat Repair Comeback' }}
            </button>
          </div>

        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';

const toast = useToastStore();
const loading = ref(true);
const saving = ref(false);
const activeTab = ref('warranties');

const warrantiesList = ref([]);
const comebacksList = ref([]);

// Form sources
const completedJobs = ref([]);
const pendingJobs = ref([]);
const techniciansList = ref([]);

// Comeback Modal
const showComebackModal = ref(false);
const comebackForm = reactive({
  original_job_card_id: '',
  comeback_job_card_id: '',
  technician_at_fault_id: '',
  reason: ''
});

const fetchData = async () => {
  loading.value = true;
  try {
    const [warrRes, combRes, jcRes, techRes] = await Promise.all([
      api.get('/warranties'),
      api.get('/comebacks'),
      api.get('/job-cards', { params: { per_page: 200 } }),
      api.get('/users', { params: { role: 'Technician' } })
    ]);

    warrantiesList.value = warrRes.data?.data || warrRes.data || [];
    comebacksList.value = combRes.data?.data || combRes.data || [];
    
    const rawJobs = jcRes.data?.data || jcRes.data || [];
    
    // Sort jobs
    completedJobs.value = rawJobs.filter(jc => jc.service_status === 'completed' || jc.service_status === 'delivered');
    pendingJobs.value = rawJobs.filter(jc => jc.service_status === 'pending');
    
    // Technicians list
    techniciansList.value = techRes.data?.data || techRes.data || [];

  } catch (err) {
    console.error('Failed to sync warranties / comebacks', err);
    toast.error('Failed to load warranty operations database.');
  } finally {
    loading.value = false;
  }
};

const submitComeback = async () => {
  saving.value = true;
  try {
    await api.post('/comebacks', { ...comebackForm });
    toast.success('Repeat repair comeback logged successfully. System alerts triggered.');
    showComebackModal.value = false;
    
    // Reset form
    comebackForm.original_job_card_id = '';
    comebackForm.comeback_job_card_id = '';
    comebackForm.technician_at_fault_id = '';
    comebackForm.reason = '';

    await fetchData();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Comeback logging failed.');
  } finally {
    saving.value = false;
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

const getWarrantyStatusClass = (status) => {
  const map = {
    'active': 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
    'expired': 'bg-slate-800 text-slate-500 border border-slate-700/50',
    'claimed': 'bg-rose-500/10 text-rose-400 border border-rose-500/20 animate-pulse',
  };
  return map[status?.toLowerCase()] || 'bg-slate-850 text-slate-400';
};

onMounted(() => {
  fetchData();
});
</script>
