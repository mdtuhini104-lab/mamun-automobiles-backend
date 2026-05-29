<template>
  <div class="workshop-live-board-container p-6 bg-gray-950 min-h-screen text-gray-100">
    <!-- Header with statistics -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-8 border-b border-gray-800 pb-6">
      <div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
          Live Workshop Board
          <span class="flex h-3 w-3 relative">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
          </span>
        </h1>
        <p class="text-sm text-gray-400 mt-1">Realtime event-driven automotive workshop operations coordinates.</p>
      </div>

      <!-- Quick stats -->
      <div class="flex flex-wrap items-center gap-4">
        <div class="px-4 py-2 bg-gray-900 border border-gray-800 rounded-xl shadow-lg flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
          <span class="text-xs font-bold text-gray-400">Delayed:</span>
          <span class="text-xs font-extrabold text-rose-400">{{ data.overdue_count || 0 }}</span>
        </div>
        
        <button 
          @click="fetchLiveBoard" 
          class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-xl transition duration-200 border border-gray-700 shadow-md font-bold text-xs flex items-center gap-1.5 focus:outline-none"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
          Sync Board
        </button>
      </div>
    </div>

    <!-- Active Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
      <!-- Bays Status and Technician workload column (Left) -->
      <div class="flex flex-col gap-8">
        <BayStatusWidget :bays="data.bays || []" />
        <TechnicianWorkloadWidget :technicians="data.technicians || []" />
      </div>

      <!-- Active Work Orders column (Middle) -->
      <div class="lg:col-span-2 flex flex-col gap-6">
        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-2xl">
          <div class="flex items-center justify-between border-b border-gray-800 pb-4 mb-6">
            <div>
              <h2 class="text-lg font-bold text-white">Active Repairs</h2>
              <p class="text-[11px] text-gray-500 mt-0.5">Active vehicles in workshop workflow</p>
            </div>
            <span class="text-xs font-bold text-gray-400 bg-gray-800 px-2.5 py-0.5 rounded-lg border border-gray-750">
              {{ data.active_work_orders?.length || 0 }} in progress
            </span>
          </div>

          <!-- Loading state -->
          <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-3">
            <div class="w-10 h-10 border-4 border-rose-500 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-xs text-gray-400 font-bold">Synchronizing board state...</p>
          </div>

          <!-- Empty state -->
          <div v-else-if="!data.active_work_orders || data.active_work_orders.length === 0" class="flex flex-col items-center justify-center py-24 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-gray-700 mb-3">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124l-.371-5.99c-.043-.699-.623-1.242-1.325-1.242H3.75" />
            </svg>
            <h3 class="text-xs font-bold text-gray-450 uppercase tracking-widest">No active repair orders</h3>
            <p class="text-[11px] text-gray-500 mt-1 px-4">Vehicles awaiting assignment or QC completion will show here.</p>
          </div>

          <!-- Jobs list grid -->
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <RealtimeJobStatusCard 
              v-for="wo in data.active_work_orders" 
              :key="wo.id"
              :work-order="wo"
            />
          </div>
        </div>

        <!-- Overdue alerts and shortages logs (Bottom) -->
        <div v-if="data.delayed_tasks && data.delayed_tasks.length > 0" class="bg-gray-900 border border-rose-950/40 rounded-3xl p-6 shadow-2xl">
          <h2 class="text-sm font-extrabold text-rose-400 uppercase tracking-widest flex items-center gap-2 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            Live Operational Warnings (Delayed Tasks)
          </h2>
          <div class="space-y-3">
            <div 
              v-for="task in data.delayed_tasks" 
              :key="task.task_id"
              class="flex items-center justify-between p-3.5 bg-rose-950/20 border border-rose-900/30 rounded-xl text-xs"
            >
              <div>
                <span class="font-extrabold text-white">{{ task.vehicle }}</span>
                <span class="text-gray-400 mx-2">—</span>
                <span class="text-gray-300">{{ task.task_name || 'Standard diagnostics' }}</span>
              </div>
              <span class="text-rose-400 font-extrabold font-mono">
                Over by {{ task.actual - task.estimated }} mins
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import api from '../../services/api';
import { getEcho } from '../../services/echo';
import BayStatusWidget from '../../components/dashboard/BayStatusWidget.vue';
import TechnicianWorkloadWidget from '../../components/dashboard/TechnicianWorkloadWidget.vue';
import RealtimeJobStatusCard from '../../components/dashboard/RealtimeJobStatusCard.vue';
import { useToastStore } from '../../stores/toast';

const data = ref({});
const loading = ref(false);
const toast = useToastStore();

const fetchLiveBoard = async () => {
  loading.value = true;
  try {
    const response = await api.get('/live-workshop-board');
    data.value = response.data;
  } catch (error) {
    console.error('Failed to fetch live workshop board', error);
  } finally {
    loading.value = false;
  }
};

let echoInstance = null;

const setupEchoListener = () => {
  try {
    echoInstance = getEcho();
    if (!echoInstance) return;

    // Listen for critical workshop state transitions to auto-refresh the operational board!
    echoInstance.channel('workshop-updates')
      .listen('.quotation.approved', () => handleRealtimeRefresh('Quotation Approved'))
      .listen('.workorder.created', () => handleRealtimeRefresh('Work Order Created'))
      .listen('.technician.assigned', () => handleRealtimeRefresh('Technician Assigned'))
      .listen('.task.started', () => handleRealtimeRefresh('Task Started'))
      .listen('.task.completed', () => handleRealtimeRefresh('Task Completed'))
      .listen('.consumption.added', () => handleRealtimeRefresh('Materials Consumed'))
      .listen('.vehicle.delivered', () => handleRealtimeRefresh('Vehicle Delivered'));

  } catch (err) {
    console.error('Failed to bind reverb websocket channels:', err);
  }
};

const handleRealtimeRefresh = (eventName) => {
  toast.info(`Realtime Sync: operational event [${eventName}] triggered update`);
  fetchLiveBoard();
};

onMounted(() => {
  fetchLiveBoard();
  setupEchoListener();
});

onUnmounted(() => {
  try {
    if (echoInstance) {
      echoInstance.leaveChannel('workshop-updates');
    }
  } catch (err) {
    console.error('Failed to leave reverb channels on unmount:', err);
  }
});
</script>

<style scoped>
/* Optional custom CSS */
</style>
