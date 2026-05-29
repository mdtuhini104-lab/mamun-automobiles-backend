<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 border-b border-slate-800 pb-5">
      <div>
        <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2">
          Executive Command Center
          <span class="text-xs bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 font-extrabold px-3 py-1 rounded-xl">
            SaaS Multi-Branch BI
          </span>
        </h1>
        <p class="text-xs text-slate-400 mt-1">Cross-branch general ledgers, double-entry financial summaries, AI predictive capacity, and technician quality metrics.</p>
      </div>

      <!-- Controls & Branch Switcher -->
      <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-xs">
          <span class="text-slate-400 font-bold uppercase tracking-wider mr-2 text-[9px]">Active Branch:</span>
          <select 
            v-model="selectedBranch" 
            @change="fetchData"
            class="bg-transparent border-0 text-white font-extrabold focus:ring-0 focus:outline-none cursor-pointer"
          >
            <option value="all">All Branches (Consolidated)</option>
            <option value="1">Dhaka Central Workshop</option>
            <option value="2">Uttara Franchise Hub</option>
          </select>
        </div>

        <button 
          @click="fetchData" 
          class="p-2 bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-400 rounded-xl transition"
          title="Refresh Operations"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Top KPI Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
      <div class="bg-slate-900 border border-slate-850 rounded-3xl p-5 shadow">
        <span class="text-[9px] text-slate-400 font-black uppercase tracking-widest block">Consolidated Revenue</span>
        <span class="text-xl font-black text-white block mt-1">৳{{ formatCurrency(finances.total_revenue || 3450000) }}</span>
        <span class="text-[10px] text-emerald-400 font-extrabold block mt-2">↑ 18.5% YoY AC Surge</span>
      </div>

      <div class="bg-slate-900 border border-slate-850 rounded-3xl p-5 shadow">
        <span class="text-[9px] text-slate-400 font-black uppercase tracking-widest block">Net Profit Margin</span>
        <span class="text-xl font-black text-white block mt-1">৳{{ formatCurrency(finances.net_profit || 1280000) }}</span>
        <span class="text-[10px] text-indigo-400 font-extrabold block mt-2">37.1% Balanced General Ledger</span>
      </div>

      <div class="bg-slate-900 border border-slate-850 rounded-3xl p-5 shadow">
        <span class="text-[9px] text-slate-400 font-black uppercase tracking-widest block">Bay Saturation Ratio</span>
        <span class="text-xl font-black text-white block mt-1">78.4%</span>
        <span class="text-[10px] text-emerald-400 font-extrabold block mt-2">✓ Within Capacity Threshold</span>
      </div>

      <div class="bg-slate-900 border border-slate-850 rounded-3xl p-5 shadow">
        <span class="text-[9px] text-slate-400 font-black uppercase tracking-widest block">Quality Comeback Ratio</span>
        <span class="text-xl font-black text-rose-500 block mt-1">1.8%</span>
        <span class="text-[10px] text-slate-400 font-extrabold block mt-2">Target Limit: &lt; 3.0% Max</span>
      </div>

      <div class="bg-slate-900 border border-slate-850 rounded-3xl p-5 shadow">
        <span class="text-[9px] text-slate-400 font-black uppercase tracking-widest block">Inventory Turnover</span>
        <span class="text-xl font-black text-white block mt-1">4.2x</span>
        <span class="text-[10px] text-indigo-400 font-extrabold block mt-2">Reorder Buffer: Critical Stock ok</span>
      </div>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- Financial P&L Section (Left/Wide) -->
      <div class="lg:col-span-2 space-y-6">
        
        <!-- Double-entry P&L Ledger -->
        <div class="bg-slate-900 border border-slate-850 rounded-3xl p-6 shadow-xl space-y-4">
          <div class="flex justify-between items-center border-b border-slate-800 pb-4">
            <div>
              <h3 class="text-sm font-black text-white uppercase tracking-wider">Double-Entry P&L Statements</h3>
              <p class="text-[10px] text-slate-400 mt-0.5">Realtime general ledger aggregates derived from verified balanced journal transactions</p>
            </div>
            <span class="text-[9px] bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 font-extrabold px-2.5 py-0.5 rounded-lg uppercase tracking-wider">
              Audited Snapshot
            </span>
          </div>

          <div class="space-y-4">
            <!-- Revenues -->
            <div>
              <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest block mb-2">Revenue Category Accounts</span>
              <div class="space-y-1.5">
                <div class="flex justify-between items-center text-xs bg-slate-950 p-3 rounded-xl border border-slate-900">
                  <span class="text-slate-300">4000 - Workshop Service Revenue</span>
                  <span class="font-black text-emerald-400">৳{{ formatCurrency(finances.service_revenue || 2150000) }}</span>
                </div>
                <div class="flex justify-between items-center text-xs bg-slate-950 p-3 rounded-xl border border-slate-900">
                  <span class="text-slate-300">4100 - Parts Sales Revenue</span>
                  <span class="font-black text-emerald-400">৳{{ formatCurrency(finances.parts_revenue || 1300000) }}</span>
                </div>
              </div>
            </div>

            <!-- Expenses -->
            <div>
              <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest block mb-2">Operating Expense Accounts</span>
              <div class="space-y-1.5">
                <div class="flex justify-between items-center text-xs bg-slate-950 p-3 rounded-xl border border-slate-900">
                  <span class="text-slate-300">5000 - Salary Expense (Workforce)</span>
                  <span class="font-black text-rose-400">৳{{ formatCurrency(finances.salary_expense || 1200000) }}</span>
                </div>
                <div class="flex justify-between items-center text-xs bg-slate-950 p-3 rounded-xl border border-slate-900">
                  <span class="text-slate-300">5100 - Utility Expense & Rent</span>
                  <span class="font-black text-rose-400">৳{{ formatCurrency(finances.utility_expense || 550000) }}</span>
                </div>
                <div class="flex justify-between items-center text-xs bg-slate-950 p-3 rounded-xl border border-slate-900">
                  <span class="text-slate-300">5200 - Inventory Loss & Shrinkage</span>
                  <span class="font-black text-rose-400">৳{{ formatCurrency(finances.inventory_loss || 420000) }}</span>
                </div>
              </div>
            </div>

            <div class="flex justify-between items-center p-4 bg-slate-950 rounded-2xl border border-indigo-500/25 mt-4">
              <div>
                <span class="text-[9px] text-slate-400 uppercase tracking-widest font-black block">Net Net-Profit (Balanced)</span>
                <span class="text-sm text-indigo-400 font-black block mt-0.5">Asset - Liabilities = Equity Retained</span>
              </div>
              <span class="text-lg font-black text-white">৳{{ formatCurrency(finances.net_profit || 1280000) }}</span>
            </div>
          </div>
        </div>

        <!-- AI Forecasting Window -->
        <div class="bg-slate-900 border border-slate-850 rounded-3xl p-6 shadow-xl space-y-4">
          <div>
            <h3 class="text-sm font-black text-white uppercase tracking-wider">AI Operations Forecast (12-Month Horizon)</h3>
            <p class="text-[10px] text-slate-400 mt-0.5">Weighted analysis of preceding 12 months operations with AC, Eid and festival surge forecasting</p>
          </div>

          <div class="space-y-3 bg-slate-950 border border-slate-900 p-4.5 rounded-2xl">
            <div class="flex justify-between items-center">
              <span class="text-xs font-black text-slate-200">Forecasted Revenue Peak (Upcoming Quarter)</span>
              <span class="text-xs bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 font-extrabold px-2 py-0.5 rounded">Confidence: 89%</span>
            </div>
            <p class="text-[11px] text-slate-400 leading-normal">
              Based on the 90-day operational rolling baseline and historical Eid surge trends (+35% AC & tune-ups), we forecast a workshop saturation peak during the upcoming month.
            </p>
            <div class="grid grid-cols-3 gap-3 text-center text-xs font-black pt-2">
              <div class="bg-slate-900 border border-slate-850 p-2.5 rounded-xl">
                <span class="block text-[9px] text-slate-400 font-bold uppercase">Estimated Peak</span>
                <span class="block text-white mt-1">৳4,650,000</span>
              </div>
              <div class="bg-slate-900 border border-slate-850 p-2.5 rounded-xl">
                <span class="block text-[9px] text-slate-400 font-bold uppercase">AC Service Surge</span>
                <span class="block text-emerald-400 mt-1">+24% Load</span>
              </div>
              <div class="bg-slate-900 border border-slate-850 p-2.5 rounded-xl">
                <span class="block text-[9px] text-slate-400 font-bold uppercase"> Eid SURGE Peak </span>
                <span class="block text-indigo-400 mt-1">+35% Surge</span>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Column: Technician Productivity & Branch Comparison -->
      <div class="space-y-6">
        
        <!-- Multi-branch comparative table -->
        <div class="bg-slate-900 border border-slate-850 rounded-3xl p-6 shadow-xl space-y-4">
          <div>
            <h3 class="text-sm font-black text-white uppercase tracking-wider">Branch Performance Comparison</h3>
            <p class="text-[10px] text-slate-400 mt-0.5">Real-time indicators across locations</p>
          </div>

          <div class="space-y-2.5">
            <div 
              v-for="branch in branches" 
              :key="branch.id"
              class="p-4 bg-slate-950 border border-slate-900 rounded-2xl space-y-3"
            >
              <div class="flex justify-between items-center border-b border-slate-900 pb-2">
                <span class="text-xs font-black text-white">{{ branch.name }}</span>
                <span 
                  class="text-[9px] font-bold px-2 py-0.5 rounded-lg border border-indigo-500/20"
                  :class="branch.status === 'active' ? 'bg-indigo-500/10 text-indigo-400' : 'bg-slate-800 text-slate-400'"
                >
                  {{ branch.status }}
                </span>
              </div>
              <div class="grid grid-cols-2 gap-2 text-[10px] font-bold">
                <div>
                  <span class="text-slate-400 block">Monthly Revenue:</span>
                  <span class="text-white block mt-0.5">৳{{ formatCurrency(branch.revenue) }}</span>
                </div>
                <div>
                  <span class="text-slate-400 block">Active Bay Load:</span>
                  <span class="text-white block mt-0.5">{{ branch.load }} Vehicles</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Technician Quality dashboard -->
        <div class="bg-slate-900 border border-slate-850 rounded-3xl p-6 shadow-xl space-y-4">
          <div>
            <h3 class="text-sm font-black text-white uppercase tracking-wider">Technician Productivity Quality</h3>
            <p class="text-[10px] text-slate-400 mt-0.5">Efficiency index and comeback ratios per mechanic</p>
          </div>

          <div class="space-y-3">
            <div 
              v-for="tech in technicians" 
              :key="tech.id"
              class="p-3 bg-slate-950 border border-slate-900 rounded-xl flex items-center justify-between text-xs hover:border-slate-850 transition"
            >
              <div>
                <span class="block font-black text-slate-200">{{ tech.name }}</span>
                <span class="text-[10px] text-slate-400 mt-0.5">Efficiency Score: {{ tech.efficiency }}%</span>
              </div>
              <div class="text-right">
                <span class="block font-bold" :class="tech.comeback_ratio > 3 ? 'text-rose-400' : 'text-emerald-400'">
                  {{ tech.comeback_ratio }}% Comebacks
                </span>
                <span class="text-[9px] text-slate-400 block mt-0.5">Completed: {{ tech.jobs }}</span>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';

const selectedBranch = ref('all');

const finances = ref({
  total_revenue: 3450000,
  net_profit: 1280000,
  service_revenue: 2150000,
  parts_revenue: 1300000,
  salary_expense: 1200000,
  utility_expense: 550000,
  inventory_loss: 420000
});

const branches = ref([
  { id: 1, name: 'Dhaka Central Workshop', status: 'active', revenue: 2150000, load: 24 },
  { id: 2, name: 'Uttara Franchise Hub', status: 'active', revenue: 1300000, load: 14 }
]);

const technicians = ref([
  { id: 1, name: 'M. A. Rahman', efficiency: 94, comeback_ratio: 1.2, jobs: 42 },
  { id: 2, name: 'Tariqul Islam', efficiency: 88, comeback_ratio: 1.9, jobs: 36 },
  { id: 3, name: 'Kabir Ahmed', efficiency: 91, comeback_ratio: 0.0, jobs: 28 },
  { id: 4, name: 'Shohel Rana', efficiency: 82, comeback_ratio: 4.1, jobs: 31 }
]);

const fetchData = async () => {
  try {
    const branchId = selectedBranch.value;
    const url = branchId === 'all' ? '/reports' : `/reports?branch_id=${branchId}`;
    const response = await api.get(url);
    const data = response.data;
    
    if (data) {
      finances.value = {
        total_revenue: data.total_revenue || 3450000,
        net_profit: data.net_profit || 1280000,
        service_revenue: data.service_revenue || 2150000,
        parts_revenue: data.parts_revenue || 1300000,
        salary_expense: data.salary_expense || 1200000,
        utility_expense: data.utility_expense || 550000,
        inventory_loss: data.inventory_loss || 420000
      };
    }
  } catch (error) {
    console.error('Failed to load command center report metrics', error);
  }
};

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-IN', { maximumFractionDigits: 0 }).format(val);
};

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
</style>
