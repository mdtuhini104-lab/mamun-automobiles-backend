<template>
  <div class="space-y-4">
    <div class="flex justify-between items-end">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Reports & Analytics</h1>
        <p class="text-sm text-slate-500 mt-1">Key metrics and detailed reports for your business.</p>
      </div>
      <div class="space-x-2 flex items-center" v-if="authStore.hasPermission('report.export')">
        <button 
          @click="reportStore.exportReport('csv')"
          :disabled="reportStore.exporting"
          class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-colors disabled:opacity-50 flex items-center gap-2"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
          CSV
        </button>
        <button 
          @click="reportStore.exportReport('pdf')"
          :disabled="reportStore.exporting"
          class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-colors disabled:opacity-50 flex items-center gap-2"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
          PDF
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col sm:flex-row gap-4 items-end">
      <div class="w-full sm:w-64">
        <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wide">Date Range</label>
        <select 
          v-model="dateRange"
          @change="updateFilter('dateRange', dateRange)"
          class="block w-full rounded-lg border-0 bg-slate-50 py-2 pl-3 pr-10 text-slate-900 shadow-inner ring-1 ring-inset ring-slate-200 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 font-medium transition-all"
        >
          <option value="today">Today</option>
          <option value="week">This Week</option>
          <option value="month">This Month</option>
          <option value="year">This Year</option>
        </select>
      </div>
      <div class="w-full sm:w-64">
        <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wide">Report Type</label>
        <select 
          v-model="reportType"
          @change="updateFilter('type', reportType)"
          class="block w-full rounded-lg border-0 bg-slate-50 py-2 pl-3 pr-10 text-slate-900 shadow-inner ring-1 ring-inset ring-slate-200 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 font-medium transition-all"
        >
          <option value="sales">Sales & Revenue</option>
          <option value="purchases">Purchases & Expenses</option>
          <option value="inventory">Inventory Valuation</option>
        </select>
      </div>
    </div>

    <!-- Analytics State -->
    <div v-if="reportStore.loading" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="i in 4" :key="i" class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 animate-pulse">
          <div class="h-4 bg-slate-200 rounded w-24 mb-3"></div>
          <div class="h-8 bg-slate-200 rounded w-32"></div>
        </div>
      </div>
      <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 animate-pulse h-80">
        <div class="h-5 bg-slate-200 rounded w-48 mb-6"></div>
        <div class="w-full h-56 bg-slate-100 rounded"></div>
      </div>
    </div>

    <div v-else class="space-y-6">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 border-l-4 border-l-indigo-500 relative overflow-hidden group">
          <h3 class="text-slate-500 text-sm font-semibold uppercase tracking-wide">Total Revenue</h3>
          <p class="text-3xl font-bold text-slate-900 mt-2 tracking-tight tabular-nums">${{ reportStore.analytics.revenue }}</p>
          <div class="absolute -right-4 -bottom-4 text-indigo-50 opacity-50 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-24 h-24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" /></svg>
          </div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 border-l-4 border-l-rose-500 relative overflow-hidden group">
          <h3 class="text-slate-500 text-sm font-semibold uppercase tracking-wide">Total Expenses</h3>
          <p class="text-3xl font-bold text-slate-900 mt-2 tracking-tight tabular-nums">${{ reportStore.analytics.expenses }}</p>
          <div class="absolute -right-4 -bottom-4 text-rose-50 opacity-50 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-24 h-24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181" /></svg>
          </div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 border-l-4 border-l-emerald-500 relative overflow-hidden group">
          <h3 class="text-slate-500 text-sm font-semibold uppercase tracking-wide">Net Profit</h3>
          <p class="text-3xl font-bold text-slate-900 mt-2 tracking-tight tabular-nums">${{ reportStore.analytics.profit }}</p>
          <div class="absolute -right-4 -bottom-4 text-emerald-50 opacity-50 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-24 h-24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          </div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 border-l-4 border-l-sky-500 relative overflow-hidden group">
          <h3 class="text-slate-500 text-sm font-semibold uppercase tracking-wide">Transactions</h3>
          <p class="text-3xl font-bold text-slate-900 mt-2 tracking-tight tabular-nums">{{ reportStore.analytics.salesCount }}</p>
          <div class="absolute -right-4 -bottom-4 text-sky-50 opacity-50 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-24 h-24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
          </div>
        </div>
      </div>

      <!-- Chart Visualization (Safe Non-Reactive HTML Implementation to prevent loops) -->
      <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <h3 class="text-base font-bold tracking-tight text-slate-900 mb-6">Trend Analysis</h3>
        <div class="h-64 flex items-end justify-between space-x-3 border-b border-slate-100 pb-2 relative">
          <!-- Chart Grid Lines -->
          <div class="absolute inset-0 flex flex-col justify-between pointer-events-none">
            <div class="border-b border-slate-100 w-full h-0"></div>
            <div class="border-b border-slate-100 w-full h-0"></div>
            <div class="border-b border-slate-100 w-full h-0"></div>
            <div class="border-b border-slate-100 w-full h-0"></div>
          </div>
          <!-- Mockup bars based on data if exists, otherwise fallback to static view -->
          <div v-for="(item, i) in (reportStore.chartData.length ? reportStore.chartData : [20,45,30,80,55,90,65])" :key="i" class="w-full flex flex-col justify-end items-center h-full group relative z-10">
            <div class="w-full bg-indigo-500 hover:bg-indigo-400 transition-all rounded-t-sm shadow-sm" :style="{ height: (item.value || item) + '%' }"></div>
            <div class="text-[10px] font-semibold text-slate-400 mt-2 opacity-0 group-hover:opacity-100 transition-opacity absolute -bottom-6">{{ item.label || 'Day '+(i+1) }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useReportStore } from '../../stores/reports';
import { useDashboardSocket } from '../../composables/useDashboardSocket';
import { useAuthStore } from '../../stores/auth';

const reportStore = useReportStore();
const authStore = useAuthStore();

const dateRange = ref(reportStore.filters.dateRange);
const reportType = ref(reportStore.filters.type);

const updateFilter = (key, value) => {
  reportStore.setFilter(key, value);
};

onMounted(() => {
  reportStore.fetchAnalytics();
});

// Safely patch analytics without full reload
useDashboardSocket((data) => {
  reportStore.patchAnalytics(data);
});
</script>
