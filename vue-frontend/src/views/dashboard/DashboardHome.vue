<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">Dashboard Overview</h1>
    
    <div v-if="loading" class="text-gray-500">Loading dashboard data...</div>
    
    <div v-else>
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <div class="bg-slate-800 p-6 rounded-lg shadow-lg border border-slate-700">
          <p class="text-sm font-medium text-slate-400 truncate">Total Revenue</p>
          <p class="mt-1 text-3xl font-semibold text-white">${{ data.total_revenue || 0 }}</p>
        </div>
        <div class="bg-slate-800 p-6 rounded-lg shadow-lg border border-slate-700">
          <p class="text-sm font-medium text-slate-400 truncate">Total Invoices</p>
          <p class="mt-1 text-3xl font-semibold text-white">{{ data.total_invoices || 0 }}</p>
        </div>
        <div class="bg-slate-800 p-6 rounded-lg shadow-lg border border-slate-700">
          <p class="text-sm font-medium text-slate-400 truncate">Total Job Cards</p>
          <p class="mt-1 text-3xl font-semibold text-white">{{ data.total_job_cards || 0 }}</p>
        </div>
        <div class="bg-slate-800 p-6 rounded-lg shadow-lg border border-slate-700">
          <p class="text-sm font-medium text-slate-400 truncate">Low Stock Items</p>
          <p class="mt-1 text-3xl font-semibold text-white">{{ data.low_stock_items || 0 }}</p>
        </div>
      </div>

      <!-- Charts Placeholder -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-slate-800 p-6 rounded-lg shadow-lg border border-slate-700 h-80 flex items-center justify-center">
          <span class="text-slate-400">Monthly Revenue Chart Placeholder</span>
        </div>
        <div class="bg-slate-800 p-6 rounded-lg shadow-lg border border-slate-700 h-80 flex items-center justify-center">
          <span class="text-slate-400">Job Card Status Chart Placeholder</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';
import { useDashboardSocket } from '../../composables/useDashboardSocket';

const data = ref({});
const loading = ref(true);

const fetchDashboardData = async () => {
  try {
    const res = await api.get('/dashboard');
    data.value = res.data.data;
  } catch (error) {
    console.error('Failed to fetch dashboard data', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchDashboardData();
});

// Setup websocket connection that automatically cleans up on unmount
useDashboardSocket((newData) => {
  // Merge live updates into existing state
  data.value = { ...data.value, ...newData };
});
</script>
