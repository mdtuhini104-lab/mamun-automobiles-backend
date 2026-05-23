<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">Advanced Analytics & BI</h1>
      <p class="text-sm text-slate-500 mt-1">Enterprise business intelligence and AI insights.</p>
    </div>

    <!-- AI Insights Section -->
    <div v-if="analyticsStore.dashboard?.insights?.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="(insight, index) in analyticsStore.dashboard.insights" :key="index"
           :class="{
             'bg-emerald-50 border-emerald-200 text-emerald-800': insight.type === 'success',
             'bg-rose-50 border-rose-200 text-rose-800': insight.type === 'danger',
             'bg-amber-50 border-amber-200 text-amber-800': insight.type === 'warning',
             'bg-blue-50 border-blue-200 text-blue-800': insight.type === 'info'
           }" class="p-4 border rounded-xl flex items-start gap-3 shadow-sm">
        <svg v-if="insight.type === 'success'" class="w-6 h-6 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
        <svg v-if="insight.type === 'danger'" class="w-6 h-6 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <svg v-if="insight.type === 'info'" class="w-6 h-6 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <p class="text-sm font-semibold">{{ insight.message }}</p>
      </div>
    </div>

    <div v-if="analyticsStore.loading" class="flex justify-center p-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
    </div>

    <div v-else-if="analyticsStore.dashboard" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Revenue & Expense Trends -->
      <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">6-Month Revenue vs Expense</h3>
        <apexchart type="area" height="300" :options="trendOptions" :series="trendSeries"></apexchart>
      </div>

      <!-- Mechanic Productivity -->
      <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Mechanic Productivity (Jobs)</h3>
        <apexchart type="donut" height="300" :options="mechanicOptions" :series="mechanicSeries"></apexchart>
      </div>
      
      <!-- Low Stock Forecast -->
      <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm col-span-1 lg:col-span-2">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-slate-900">Low Stock Alert / Forecast</h3>
          <button @click="exportPDF" class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-1">
             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
             Export Report
          </button>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase">Item Name</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-900 uppercase">Current Stock</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-900 uppercase">Min Level</th>
                <th class="px-6 py-3 text-center text-xs font-semibold text-slate-900 uppercase">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              <tr v-for="item in analyticsStore.dashboard.low_stock" :key="item.name">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">{{ item.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-rose-600 font-bold text-right">{{ item.current_stock }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 text-right">{{ item.min_stock_level }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-1 text-xs font-medium text-rose-700 ring-1 ring-inset ring-rose-600/20">Critical</span>
                </td>
              </tr>
              <tr v-if="!analyticsStore.dashboard.low_stock.length">
                <td colspan="4" class="px-6 py-4 text-center text-sm text-slate-500">Inventory levels are healthy.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue';
import { useAnalyticsStore } from '../../stores/analytics';
import VueApexCharts from 'vue3-apexcharts';

const analyticsStore = useAnalyticsStore();

onMounted(() => {
  analyticsStore.fetchDashboard();
});

const trendSeries = computed(() => {
  if (!analyticsStore.dashboard) return [];
  return [
    { name: 'Revenue', data: analyticsStore.dashboard.trends.revenue },
    { name: 'Expense', data: analyticsStore.dashboard.trends.expense }
  ];
});

const trendOptions = computed(() => {
  if (!analyticsStore.dashboard) return {};
  return {
    chart: { type: 'area', fontFamily: 'inherit', toolbar: { show: false } },
    colors: ['#4f46e5', '#f43f5e'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: { categories: analyticsStore.dashboard.trends.categories },
    yaxis: { labels: { formatter: (value) => '$' + value } },
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } }
  };
});

const mechanicSeries = computed(() => {
  if (!analyticsStore.dashboard) return [];
  return analyticsStore.dashboard.mechanics.series;
});

const mechanicOptions = computed(() => {
  if (!analyticsStore.dashboard) return {};
  return {
    chart: { type: 'donut', fontFamily: 'inherit' },
    labels: analyticsStore.dashboard.mechanics.labels,
    colors: ['#3b82f6', '#10b981', '#f59e0b', '#6366f1', '#ec4899'],
    legend: { position: 'bottom' }
  };
});

const exportPDF = () => {
  alert('Report PDF Generation triggered! In a real app this downloads the analytical PDF.');
};
</script>

<script>
export default {
  components: {
    apexchart: VueApexCharts,
  },
}
</script>

