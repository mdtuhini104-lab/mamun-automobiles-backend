<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-800 pb-5">
      <div>
        <div class="flex items-center gap-2">
          <h1 class="text-2xl font-black tracking-tight text-white">Customer Success & Tenant Health</h1>
          <span class="px-2 py-0.5 rounded text-[8px] bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 font-extrabold uppercase">
            Super Admin View
          </span>
        </div>
        <p class="text-xs text-slate-400 mt-1">Audit active tenant health scores, onboarding completion rates, and churn risk indexes.</p>
      </div>
      <div class="flex gap-2">
        <button 
          @click="fetchGlobalSuccessData" 
          class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition flex items-center gap-1.5"
          :disabled="loading"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4" :class="{ 'animate-spin': loading }">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
          Sync Success Metrics
        </button>
      </div>
    </div>

    <!-- Overview stats cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-md">
        <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest">Active Tenants Scored</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-2xl font-black text-white">{{ tenantStats.total || 0 }}</span>
        </div>
      </div>

      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-md">
        <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest">Average Health Score</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-2xl font-black text-emerald-450">{{ tenantStats.avgHealth }}%</span>
        </div>
      </div>

      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-md">
        <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest">Churn Warning Risks</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-2xl font-black text-rose-400">{{ tenantStats.churnRisks }}</span>
        </div>
      </div>

      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-md">
        <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest">Onboarding Completions</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-2xl font-black text-indigo-400">{{ tenantStats.avgOnboarding }}%</span>
        </div>
      </div>
    </div>

    <!-- Tenants Health scoring registry table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
      <div>
        <h2 class="text-lg font-black text-white uppercase tracking-wider">Tenant Health Registry</h2>
        <p class="text-xs text-slate-400 mt-0.5">Calculated success weights: 30% Activity, 25% Invoices, 20% Workflows, 15% Plugins, 10% Logins.</p>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-350">
          <thead class="border-b border-slate-800 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
            <tr>
              <th class="pb-3">Tenant / Domain</th>
              <th class="pb-3">Plan</th>
              <th class="pb-3">Health Score</th>
              <th class="pb-3">Onboarding Completion</th>
              <th class="pb-3">Inactivity Status</th>
              <th class="pb-3 text-right">Risk Assessment</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-850">
            <tr v-for="profile in tenantProfiles" :key="profile.tenant_id" class="hover:bg-slate-850/30 transition">
              <td class="py-3">
                <div class="font-semibold text-white">{{ profile.company_name }}</div>
                <div class="text-[10px] text-slate-500 font-mono">{{ profile.domain }}</div>
              </td>
              <td class="py-3 capitalize font-semibold">{{ profile.plan }}</td>
              <td class="py-3 font-mono font-bold" :class="getHealthColor(profile.health.health_score)">
                {{ profile.health.health_score }}%
              </td>
              <td class="py-3">
                <div class="flex items-center gap-2">
                  <div class="w-24 bg-slate-800 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-indigo-500 h-full" :style="{ width: profile.onboarding_rate + '%' }"></div>
                  </div>
                  <span class="font-mono text-[10px] text-slate-400">{{ profile.onboarding_rate }}%</span>
                </div>
              </td>
              <td class="py-3 text-slate-400 font-medium">
                {{ profile.health.days_inactive }} {{ profile.health.days_inactive === 1 ? 'day' : 'days' }} inactive
              </td>
              <td class="py-3 text-right">
                <span 
                  class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider"
                  :class="getRiskClasses(profile.health.risk_level)"
                >
                  {{ profile.health.risk_level }}
                </span>
              </td>
            </tr>
            <tr v-if="tenantProfiles.length === 0">
              <td colspan="6" class="py-10 text-center text-slate-500">
                No active tenants registered in SaaS database.
              </td>
            </tr>
          </tbody>
        </table>
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
const tenantProfiles = ref([]);

const fetchGlobalSuccessData = async () => {
  loading.value = true;
  try {
    const response = await api.get('/saas/global-success');
    tenantProfiles.value = response.data.data;
  } catch (error) {
    console.error('Failed to load global success dashboard data', error);
    toast.error('Could not query tenant metrics.');
  } finally {
    loading.value = false;
  }
};

const tenantStats = computed(() => {
  const list = tenantProfiles.value;
  if (list.length === 0) {
    return { total: 0, avgHealth: 0, churnRisks: 0, avgOnboarding: 0 };
  }

  const total = list.length;
  const avgHealth = Math.round(list.reduce((acc, curr) => acc + curr.health.health_score, 0) / total);
  const avgOnboarding = Math.round(list.reduce((acc, curr) => acc + curr.onboarding_rate, 0) / total);
  const churnRisks = list.filter(item => item.health.risk_level === 'Churn Risk').length;

  return { total, avgHealth, churnRisks, avgOnboarding };
});

const getHealthColor = (score) => {
  if (score >= 80) return 'text-emerald-400';
  if (score >= 50) return 'text-amber-400';
  return 'text-rose-400';
};

const getRiskClasses = (level) => {
  if (level === 'Churn Risk') return 'bg-rose-600 text-white font-extrabold';
  if (level === 'High Risk') return 'bg-rose-500/10 border border-rose-500/20 text-rose-400';
  if (level === 'Inactive') return 'bg-amber-500/10 border border-amber-500/20 text-amber-450';
  if (level === 'Warning') return 'bg-slate-800 border border-slate-700 text-slate-350';
  return 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400';
};

onMounted(() => {
  fetchGlobalSuccessData();
});
</script>

<style scoped>
</style>
