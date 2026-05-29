<template>
  <div class="space-y-6 p-6 bg-slate-50 min-h-screen">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-slate-200 pb-4 gap-4">
      <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
          Executive Cockpit & Business Intelligence
          <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
            Realtime Analytics
          </span>
        </h1>
        <p class="text-sm text-slate-500 mt-1">Weighted forecasting, workshop utilization ratios, quality efficiency aggregates, and risk management.</p>
      </div>

      <!-- Controls -->
      <div class="flex items-center gap-2.5">
        <button 
          v-for="mode in modes" 
          :key="mode.id"
          @click="activeMode = mode.id"
          class="px-4 py-2 text-xs font-bold rounded-xl transition duration-150 border focus:outline-none"
          :class="activeMode === mode.id ? 'bg-indigo-600 border-indigo-600 text-white shadow' : 'bg-white border-slate-200 text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
        >
          {{ mode.name }}
        </button>

        <button 
          @click="fetchDashboardData"
          class="p-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-500 rounded-xl transition shadow-sm ml-2"
          title="Refresh Data"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Quick stats overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-2">
        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Monthly Intake</h4>
        <p class="text-2xl font-black text-slate-800">142 Vehicles</p>
        <div class="flex items-center gap-1.5 text-xs text-emerald-600 font-bold">
          <span>↑ 12.4% MoM</span>
          <span class="text-slate-400 font-normal">vs. historical avg</span>
        </div>
      </div>
      <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-2">
        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Net Revenue (Est.)</h4>
        <p class="text-2xl font-black text-slate-800">৳3,450,000</p>
        <div class="flex items-center gap-1.5 text-xs text-emerald-600 font-bold">
          <span>↑ 18.5% YoY</span>
          <span class="text-slate-400 font-normal">Eid & AC seasons</span>
        </div>
      </div>
      <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-2">
        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Active Anomalies</h4>
        <p class="text-2xl font-black text-rose-600">{{ anomalies.length }} Alerts</p>
        <div class="flex items-center gap-1.5 text-xs text-rose-500 font-bold">
          <span>⚠ Action Required</span>
          <span class="text-slate-400 font-normal">in AI inbox</span>
        </div>
      </div>
      <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-2">
        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Risk Index</h4>
        <p class="text-2xl font-black text-slate-800">Very Low</p>
        <div class="flex items-center gap-1.5 text-xs text-emerald-600 font-bold">
          <span>✓ Stable Operations</span>
          <span class="text-slate-400 font-normal">0 critical leaks</span>
        </div>
      </div>
    </div>

    <!-- Active Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Revenue Analytics Column (Left, wider) -->
      <div class="lg:col-span-2 space-y-8">
        <RevenueAnalyticsWidget />
        
        <!-- Live Anomalies Alert Panel -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-4">
          <div class="flex justify-between items-center border-b border-slate-100 pb-4">
            <div>
              <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Active Operational Alerts & Audits</h3>
              <p class="text-[10px] text-slate-400 mt-0.5">Realtime risk alerts requiring manager/super-admin overrides</p>
            </div>
            <router-link 
              to="/ai-inbox" 
              class="text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1"
            >
              Go to Inbox
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
              </svg>
            </router-link>
          </div>

          <div v-if="loadingAnomalies" class="flex justify-center py-6">
            <div class="w-6 h-6 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
          </div>

          <div v-else-if="anomalies.length === 0" class="flex flex-col items-center justify-center py-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-slate-350 mb-2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-xs font-bold text-slate-500">Zero active discount anomalies or high-risk flags detected.</p>
          </div>

          <div v-else class="space-y-3">
            <div 
              v-for="item in anomalies" 
              :key="item.id"
              class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-4 rounded-2xl border text-xs gap-3 transition"
              :class="item.severity === 'critical' ? 'bg-rose-50 border-rose-100 text-rose-950' : 'bg-amber-50 border-amber-100 text-amber-950'"
            >
              <div class="space-y-1">
                <div class="flex items-center gap-2">
                  <span 
                    class="font-black px-2 py-0.5 rounded text-[9px] uppercase tracking-wider"
                    :class="item.severity === 'critical' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800'"
                  >
                    {{ item.severity }}
                  </span>
                  <span class="font-extrabold text-slate-900">Quotation Anomaly detected</span>
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">{{ item.description }}</p>
              </div>

              <router-link 
                to="/ai-inbox" 
                class="px-3.5 py-1.5 bg-white border rounded-xl font-bold text-xs shadow-sm text-slate-700 hover:bg-slate-50 self-end sm:self-center transition"
                :class="item.severity === 'critical' ? 'border-rose-200 text-rose-700 hover:bg-rose-50/50' : 'border-amber-200 text-amber-700 hover:bg-amber-50/50'"
              >
                Resolve Override
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Efficiency & KPI columns (Right) -->
      <div class="flex flex-col gap-8">
        <OperationalKpiWidget />
        <WorkshopEfficiencyHeatmap />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';
import RevenueAnalyticsWidget from '../../components/dashboard/RevenueAnalyticsWidget.vue';
import WorkshopEfficiencyHeatmap from '../../components/dashboard/WorkshopEfficiencyHeatmap.vue';
import OperationalKpiWidget from '../../components/dashboard/OperationalKpiWidget.vue';

const activeMode = ref('monthly');
const anomalies = ref([]);
const loadingAnomalies = ref(false);

const modes = [
  { id: 'daily', name: 'Daily View' },
  { id: 'monthly', name: 'Monthly View' },
  { id: 'ytd', name: 'YTD Aggregates' },
];

const fetchDashboardData = async () => {
  fetchAnomalies();
};

const fetchAnomalies = async () => {
  loadingAnomalies.value = true;
  try {
    const response = await api.get('/ai/recommendations?type=pricing_anomaly&status=pending');
    // Extract actual anomalies mapped from recommendations suggestion_data
    const data = response.data?.data || response.data || [];
    const parsedAnomalies = [];
    
    data.forEach(rec => {
      if (rec.suggestion_data?.anomalies) {
        rec.suggestion_data.anomalies.forEach((a, idx) => {
          parsedAnomalies.push({
            id: `${rec.id}-${idx}`,
            severity: a.severity || 'medium',
            description: a.description || 'Pricing threshold breached.',
            source_id: rec.source_id
          });
        });
      }
    });
    
    anomalies.value = parsedAnomalies;
  } catch (error) {
    console.error('Failed to load active anomalies', error);
  } finally {
    loadingAnomalies.value = false;
  }
};

onMounted(() => {
  fetchDashboardData();
});
</script>

<style scoped>
</style>
