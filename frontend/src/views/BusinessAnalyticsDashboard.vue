<template>
  <div class="analytics-dashboard p-6 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Business Analytics</h1>
      <div class="flex space-x-4">
        <select class="border border-gray-300 rounded px-4 py-2">
          <option>Last 30 Days</option>
          <option>This Month</option>
          <option>This Year</option>
        </select>
        <button class="bg-blue-600 text-white px-4 py-2 rounded shadow flex items-center">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
          Export PDF
        </button>
      </div>
    </div>

    <!-- KPI Widgets -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white p-6 rounded-xl shadow border-t-4 border-blue-500">
        <p class="text-sm font-medium text-gray-500 mb-1">Monthly Revenue</p>
        <h3 class="text-3xl font-bold text-gray-800">৳ {{ kpi.monthly_revenue?.toLocaleString() || '0' }}</h3>
        <p class="text-xs text-green-500 mt-2 flex items-center">
          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
          12.5% from last month
        </p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow border-t-4 border-red-500">
        <p class="text-sm font-medium text-gray-500 mb-1">Total Expenses</p>
        <h3 class="text-3xl font-bold text-gray-800">৳ {{ kpi.total_expenses?.toLocaleString() || '0' }}</h3>
        <p class="text-xs text-red-500 mt-2 flex items-center">
          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
          5.2% from last month
        </p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow border-t-4 border-green-500">
        <p class="text-sm font-medium text-gray-500 mb-1">Net Profit</p>
        <h3 class="text-3xl font-bold text-gray-800">৳ {{ kpi.net_profit?.toLocaleString() || '0' }}</h3>
      </div>
      <div class="bg-white p-6 rounded-xl shadow border-t-4 border-yellow-500">
        <p class="text-sm font-medium text-gray-500 mb-1">Pending Invoices</p>
        <h3 class="text-3xl font-bold text-gray-800">{{ kpi.pending_invoices || '0' }}</h3>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-lg font-bold mb-4">Sales vs Expenses Trend</h2>
        <div class="h-64 bg-gray-100 rounded flex items-center justify-center text-gray-400">
          [ApexCharts Line Chart Area]
        </div>
      </div>
      <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-lg font-bold mb-4">Revenue by Category</h2>
        <div class="h-64 bg-gray-100 rounded flex items-center justify-center text-gray-400">
          [ApexCharts Pie Chart Area]
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../services/api';

const kpi = ref({});

const fetchKpi = async () => {
  try {
    const response = await api.get('/analytics/summary');
    kpi.value = response.data;
  } catch (error) {
    console.error("Failed to fetch KPI", error);
  }
};

onMounted(() => {
  fetchKpi();
});
</script>
