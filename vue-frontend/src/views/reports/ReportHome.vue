<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-800">Reports & Analytics</h1>
      <div class="space-x-2">
        <button 
          @click="reportStore.exportReport('csv')"
          :disabled="reportStore.exporting"
          class="bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-md text-sm transition-colors disabled:opacity-50"
        >
          Export CSV
        </button>
        <button 
          @click="reportStore.exportReport('pdf')"
          :disabled="reportStore.exporting"
          class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors disabled:opacity-50"
        >
          Export PDF
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow flex flex-col sm:flex-row gap-4">
      <div class="w-full sm:w-64">
        <label class="block text-xs text-slate-500 mb-1 uppercase">Date Range</label>
        <select 
          v-model="dateRange"
          @change="updateFilter('dateRange', dateRange)"
          class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-indigo-500 text-sm"
        >
          <option value="today">Today</option>
          <option value="week">This Week</option>
          <option value="month">This Month</option>
          <option value="year">This Year</option>
        </select>
      </div>
      <div class="w-full sm:w-64">
        <label class="block text-xs text-slate-500 mb-1 uppercase">Report Type</label>
        <select 
          v-model="reportType"
          @change="updateFilter('type', reportType)"
          class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-indigo-500 text-sm"
        >
          <option value="sales">Sales & Revenue</option>
          <option value="purchases">Purchases & Expenses</option>
          <option value="inventory">Inventory Valuation</option>
        </select>
      </div>
    </div>

    <!-- Analytics Cache State -->
    <div v-if="reportStore.loading" class="text-slate-500 animate-pulse">Loading cached report data...</div>

    <div v-else class="space-y-6">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-slate-800 p-6 rounded-lg shadow-lg border-l-4 border-indigo-500">
          <h3 class="text-slate-400 text-sm">Total Revenue</h3>
          <p class="text-2xl font-bold text-white mt-2">${{ reportStore.analytics.revenue }}</p>
        </div>
        <div class="bg-slate-800 p-6 rounded-lg shadow-lg border-l-4 border-red-500">
          <h3 class="text-slate-400 text-sm">Total Expenses</h3>
          <p class="text-2xl font-bold text-white mt-2">${{ reportStore.analytics.expenses }}</p>
        </div>
        <div class="bg-slate-800 p-6 rounded-lg shadow-lg border-l-4 border-green-500">
          <h3 class="text-slate-400 text-sm">Net Profit</h3>
          <p class="text-2xl font-bold text-white mt-2">${{ reportStore.analytics.profit }}</p>
        </div>
        <div class="bg-slate-800 p-6 rounded-lg shadow-lg border-l-4 border-blue-500">
          <h3 class="text-slate-400 text-sm">Transactions</h3>
          <p class="text-2xl font-bold text-white mt-2">{{ reportStore.analytics.salesCount }}</p>
        </div>
      </div>

      <!-- Chart Visualization (Safe Non-Reactive HTML Implementation to prevent loops) -->
      <div class="bg-slate-800 p-6 rounded-lg shadow-lg border border-slate-700">
        <h3 class="text-lg font-semibold text-white mb-4">Trend Analysis</h3>
        <div class="h-64 flex items-end justify-between space-x-2 border-b border-slate-600 pb-2">
          <!-- Mockup bars based on data if exists, otherwise fallback to static view -->
          <div v-for="(item, i) in (reportStore.chartData.length ? reportStore.chartData : [10,40,30,70,50,90,60])" :key="i" class="w-full flex flex-col justify-end items-center h-full group">
            <div class="w-full bg-indigo-500 hover:bg-indigo-400 transition-all rounded-t" :style="{ height: (item.value || item) + '%' }"></div>
            <div class="text-[10px] text-slate-400 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">{{ item.label || 'Day '+i }}</div>
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

const reportStore = useReportStore();

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
