<template>
  <div class="space-y-6 p-6 bg-slate-50 min-h-screen">
    <!-- Header -->
    <div class="flex justify-between items-center border-b border-slate-200 pb-4">
      <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
          AI Recommendation Inbox
          <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-700/10">
            Governance Queue
          </span>
        </h1>
        <p class="text-sm text-slate-500 mt-1">Review explainable predictive suggestions, labor markups, and resolve safety anomalies with human overrides.</p>
      </div>

      <div class="flex gap-4 text-xs font-bold">
        <button 
          @click="fetchRecommendations"
          class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm transition"
        >
          Sync AI Queue
        </button>
      </div>
    </div>

    <!-- Counters -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 bg-amber-500/10 text-amber-600 rounded-2xl flex items-center justify-center font-bold text-lg">
          {{ pendingList.length }}
        </div>
        <div>
          <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pending Review</h4>
          <p class="text-sm font-black text-slate-800">Recommendations</p>
        </div>
      </div>
      <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 bg-emerald-500/10 text-emerald-600 rounded-2xl flex items-center justify-center font-bold text-lg">
          {{ approvedCount }}
        </div>
        <div>
          <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Actioned & Signed</h4>
          <p class="text-sm font-black text-slate-800">Human Approvals</p>
        </div>
      </div>
      <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 bg-purple-500/10 text-purple-600 rounded-2xl flex items-center justify-center font-bold text-lg">
          {{ averageConfidence }}%
        </div>
        <div>
          <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">AI Mean Rating</h4>
          <p class="text-sm font-black text-slate-800">Confidence Index</p>
        </div>
      </div>
    </div>

    <!-- Filter Pills -->
    <div class="flex gap-2.5 overflow-x-auto pb-1">
      <button 
        v-for="filter in filters" 
        :key="filter.id"
        @click="activeFilter = filter.id"
        class="px-4 py-1.5 rounded-full text-xs font-bold transition focus:outline-none"
        :class="activeFilter === filter.id ? 'bg-indigo-600 text-white shadow' : 'bg-white border border-slate-200 text-slate-500 hover:text-slate-700'"
      >
        {{ filter.name }}
      </button>
    </div>

    <!-- Recommendations Queue -->
    <div class="space-y-4">
      <div v-if="loading" class="flex flex-col items-center justify-center py-24 gap-3">
        <div class="w-10 h-10 border-3 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
        <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Analyzing Queue...</p>
      </div>

      <div v-else-if="filteredList.length === 0" class="bg-white border border-slate-200 rounded-3xl py-20 text-center shadow-sm">
        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-4 text-sm font-bold text-slate-900">AI Recommendation Inbox is Vacant</h3>
        <p class="mt-2 text-xs text-slate-500 max-w-sm mx-auto">There are no pending recommendations waiting for your sign-off under this filter.</p>
      </div>

      <div v-else class="grid grid-cols-1 gap-5">
        <div 
          v-for="rec in filteredList" 
          :key="rec.id"
          class="bg-white border border-slate-200 hover:border-slate-300 rounded-3xl p-6 shadow-sm hover:shadow-md transition-all flex flex-col md:flex-row justify-between gap-6"
        >
          <!-- Recommendation Details -->
          <div class="flex-1 space-y-3">
            <div class="flex items-center gap-3">
              <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-0.5 text-[10px] font-bold text-purple-800 ring-1 ring-inset ring-purple-700/10 uppercase tracking-widest">
                {{ rec.recommendation_type.replace('_', ' ') }}
              </span>
              <span class="text-xs font-mono font-bold text-slate-400">#REC-{{ rec.id }}</span>
              <span 
                class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold ring-1 ring-inset tracking-wider"
                :class="getConfidenceClass(rec.confidence_score)"
              >
                AI Confidence: {{ rec.confidence_score }}%
              </span>
            </div>

            <div>
              <h3 class="text-sm font-black text-slate-900 leading-tight">
                {{ getTitle(rec) }}
              </h3>
              <p class="text-xs text-slate-500 mt-2 bg-slate-50 border border-slate-100 p-3.5 rounded-2xl italic leading-relaxed">
                "{{ rec.explanation }}"
              </p>
            </div>

            <!-- Suggestion metrics -->
            <div class="grid grid-cols-2 max-w-sm gap-4 text-xs pt-1">
              <div v-if="rec.recommendation_type === 'pricing_anomaly'" class="bg-slate-50/50 p-2.5 rounded-xl border border-slate-100">
                <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Quotation Discount</span>
                <span class="text-sm font-black text-rose-500">{{ rec.suggestion_data?.original_discount }}% discount</span>
              </div>
              <div v-if="rec.recommendation_type === 'inventory_reorder'" class="bg-slate-50/50 p-2.5 rounded-xl border border-slate-100">
                <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Suggested Order</span>
                <span class="text-sm font-black text-emerald-600">{{ rec.suggestion_data?.suggested_qty }} items</span>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex flex-row md:flex-col justify-end items-stretch gap-2.5 min-w-[160px] self-end md:self-center">
            <!-- Pricing anomaly override requires supervisor/manager details -->
            <button 
              v-if="rec.recommendation_type === 'pricing_anomaly'"
              @click="openOverrideModal(rec)"
              class="flex-1 py-3 bg-rose-600 hover:bg-rose-500 text-white rounded-2xl font-bold text-xs shadow-md transition active:scale-98"
            >
              Process Override
            </button>
            <button 
              v-else
              @click="approveRec(rec.id)"
              class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-2xl font-bold text-xs shadow-md transition active:scale-98"
            >
              Approve Suggestion
            </button>
            <button 
              @click="rejectRec(rec.id)"
              class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-slate-700 rounded-2xl font-bold text-xs transition active:scale-98"
            >
              Dismiss recommendation
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Override Anomaly Modal -->
    <div v-if="showOverrideModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm animate-in fade-in duration-150">
      <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full p-6 animate-in zoom-in-95 duration-200 space-y-5">
        <div>
          <h3 class="text-lg font-black text-slate-950">Manager/Admin Anomaly Override</h3>
          <p class="text-xs text-slate-500 mt-0.5">Authorizing discount approvals for Quotation #{{ selectedRec?.source_id }}</p>
        </div>

        <form @submit.prevent="submitOverride" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5">Override Justification Notes *</label>
            <textarea 
              v-model="overrideNotes" 
              required
              rows="4" 
              placeholder="Provide explainable reasons (e.g. corporate client negotiation, insurance adjustments, bulk client relationship sign-off)..."
              class="w-full text-xs rounded-2xl border-slate-200 p-3 border focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
            ></textarea>
          </div>

          <div class="flex gap-3">
            <button 
              type="button" 
              @click="showOverrideModal = false"
              class="flex-1 py-3 border border-slate-300 rounded-2xl text-xs font-bold text-slate-700 bg-white hover:bg-slate-50"
            >
              Cancel
            </button>
            <button 
              type="submit"
              :disabled="saving"
              class="flex-1 py-3 bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-white rounded-2xl font-bold text-xs shadow-md"
            >
              Authorize Override
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';

const toast = useToastStore();
const loading = ref(false);
const saving = ref(false);

const pendingList = ref([]);
const approvedCount = ref(18); // mock historically actioned items
const activeFilter = ref('all');

const showOverrideModal = ref(false);
const selectedRec = ref(null);
const overrideNotes = ref('');

const filters = [
  { id: 'all', name: 'All Queue' },
  { id: 'pricing_anomaly', name: 'Pricing Anomalies' },
  { id: 'inventory_reorder', name: 'Inventory Reorders' },
  { id: 'price_markup', name: 'Markup Recommendations' },
];

const filteredList = computed(() => {
  if (activeFilter.value === 'all') return pendingList.value;
  return pendingList.value.filter(r => r.recommendation_type === activeFilter.value);
});

const averageConfidence = computed(() => {
  if (pendingList.value.length === 0) return 90;
  const sum = pendingList.value.reduce((acc, r) => acc + (r.confidence_score || 0), 0);
  return Math.round(sum / pendingList.value.length);
});

const getTitle = (rec) => {
  switch (rec.recommendation_type) {
    case 'pricing_anomaly':
      return `Suspicious discount rate on Quotation #${rec.source_id}`;
    case 'inventory_reorder':
      return `Critical low-stock warning: ${rec.suggestion_data?.part_name || 'Spare Parts'}`;
    case 'price_markup':
      return `Labor cost adjustment suggestion for ${rec.suggestion_data?.part_name || 'Spare Parts'}`;
    default:
      return `Centralized operational recommendation`;
  }
};

const getConfidenceClass = (score) => {
  if (score >= 90) return 'bg-emerald-50 text-emerald-800 ring-emerald-600/20';
  if (score >= 80) return 'bg-blue-50 text-blue-800 ring-blue-600/20';
  return 'bg-amber-50 text-amber-800 ring-amber-600/20';
};

const fetchRecommendations = async () => {
  loading.value = true;
  try {
    const response = await api.get('/ai/recommendations');
    pendingList.value = response.data?.data || response.data || [];
  } catch (error) {
    console.error('Failed to query AI Recommendations', error);
  } finally {
    loading.value = false;
  }
};

const approveRec = async (id) => {
  try {
    await api.post(`/ai/recommendations/${id}/action`, { action: 'approve' });
    toast.success('Recommendation signed off and implemented.');
    fetchRecommendations();
  } catch (error) {
    console.error('Failed to approve recommendation', error);
  }
};

const rejectRec = async (id) => {
  try {
    await api.post(`/ai/recommendations/${id}/action`, { action: 'reject' });
    toast.success('Recommendation dismissed.');
    fetchRecommendations();
  } catch (error) {
    console.error('Failed to reject recommendation', error);
  }
};

const openOverrideModal = (rec) => {
  selectedRec.value = rec;
  overrideNotes.value = '';
  showOverrideModal.value = true;
};

const submitOverride = async () => {
  if (overrideNotes.value.length < 5) {
    toast.warning('Justification notes must hold at least 5 characters.');
    return;
  }
  saving.value = true;
  try {
    await api.post(`/ai/anomalies/${selectedRec.value.source_id}/override`, {
      override_notes: overrideNotes.value
    });
    toast.success('Quotation discount override authorized and logged in audit trails.');
    showOverrideModal.value = false;
    fetchRecommendations();
  } catch (error) {
    console.error('Failed to process anomaly override', error);
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchRecommendations();
});
</script>

<style scoped>
/* Scoped styles */
</style>
