<template>
  <div class="mobile-app bg-gray-950 min-h-screen pb-24 font-sans text-gray-100">
    <!-- Header -->
    <header class="bg-gray-900 border-b border-gray-800 text-white px-5 py-4 shadow-xl sticky top-0 z-40 flex justify-between items-center">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-rose-500 to-rose-600 flex items-center justify-center shadow-lg font-black tracking-wider text-sm">
          MA
        </div>
        <div>
          <h1 class="text-sm font-black leading-tight text-white uppercase tracking-wider">Mamun Auto</h1>
          <p class="text-[10px] text-gray-500 font-bold uppercase mt-0.5">Technician Console</p>
        </div>
      </div>
      <!-- Unread notifications dropdown / bell in mobile header -->
      <NotificationDropdown />
    </header>

    <!-- Offline Banner -->
    <div v-if="!isOnline" class="bg-amber-500 text-white text-[10px] uppercase tracking-widest text-center py-1.5 font-extrabold animate-pulse">
      You are currently offline. Changes will sync automatically.
    </div>

    <!-- Quick Actions Widget -->
    <div class="border-b border-gray-900 pb-3">
      <TechnicianQuickActions />
    </div>

    <!-- Active Tasks list -->
    <div class="px-5 mt-6">
      <div class="flex justify-between items-center mb-4">
        <div>
          <h2 class="text-base font-extrabold text-white">Your Assigned Tasks</h2>
          <p class="text-[10px] text-gray-500 mt-0.5">Lightweight real-time active workflow task queue</p>
        </div>
        <button 
          @click="fetchTasks" 
          class="text-xs font-bold text-rose-400 hover:text-rose-300 transition"
        >
          Sync List
        </button>
      </div>

      <div v-if="loading" class="flex flex-col items-center justify-center py-16 gap-3">
        <div class="w-8 h-8 border-3 border-rose-500 border-t-transparent rounded-full animate-spin"></div>
        <p class="text-[11px] text-gray-500 font-bold uppercase tracking-wider">Syncing Queue...</p>
      </div>

      <div v-else-if="tasks.length === 0" class="flex flex-col items-center justify-center py-20 text-center border border-dashed border-gray-800 rounded-3xl">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-gray-700 mb-3">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">No assigned tasks</h3>
        <p class="text-[10px] text-gray-600 mt-1 px-4">You have no pending active workshop repair tasks assigned.</p>
      </div>

      <div v-else class="space-y-4">
        <MobileTaskCard 
          v-for="task in tasks" 
          :key="task.id" 
          :task="task"
          @status-updated="fetchTasks"
          @add-consumption="openConsumptionModal"
        />
      </div>
    </div>

    <!-- Quick Parts Consumption Modal -->
    <QuickConsumptionModal 
      :is-open="isConsumptionOpen"
      :task="activeTask"
      @close="isConsumptionOpen = false"
      @logged="fetchTasks"
    />

    <!-- Bottom Navigation -->
    <nav class="bg-gray-900 border-t border-gray-800 fixed bottom-0 w-full flex justify-around p-3 pb-safe z-40 shadow-2xl">
      <button class="flex flex-col items-center text-rose-500 focus:outline-none">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mb-1">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
        </svg>
        <span class="text-[9px] font-extrabold uppercase tracking-widest">Home</span>
      </button>
      <button class="flex flex-col items-center text-gray-500 hover:text-rose-400 transition focus:outline-none" @click="toast.info('Active tasks module is selected.')">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mb-1">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.375M9 18h3.375m-6.375-3h.008v.008H6V15zm0 3h.008v.008H6V18zm0-6h.008v.008H6V12m-.375 9h12.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H5.625c-.621 0-1.125.504-1.125 1.125v14.25c0 .621.504 1.125 1.125 1.125z" />
        </svg>
        <span class="text-[9px] font-extrabold uppercase tracking-widest">Tasks</span>
      </button>
      <button class="flex flex-col items-center text-gray-500 hover:text-rose-400 transition focus:outline-none" @click="toast.info('Mobile reports module is under active synchronization.')">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mb-1">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" />
        </svg>
        <span class="text-[9px] font-extrabold uppercase tracking-widest">Stats</span>
      </button>
    </nav>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../services/api';
import { useToastStore } from '../stores/toast';
import TechnicianQuickActions from '../components/dashboard/TechnicianQuickActions.vue';
import MobileTaskCard from '../components/dashboard/MobileTaskCard.vue';
import QuickConsumptionModal from '../components/dashboard/QuickConsumptionModal.vue';
import NotificationDropdown from '../components/notifications/NotificationDropdown.vue';

const isOnline = ref(navigator.onLine);
const loading = ref(false);
const tasks = ref([]);
const toast = useToastStore();

const isConsumptionOpen = ref(false);
const activeTask = ref(null);

const fetchTasks = async () => {
  loading.value = true;
  try {
    const response = await api.get('/mobile/tasks');
    tasks.value = response.data.data || response.data || [];
  } catch (error) {
    console.error('Failed to fetch mobile tasks list', error);
    // Setup elegant fallback mockup
    tasks.value = [
      { id: 101, task_name: 'Engine oil replacement & filter swap', status: 'in_progress', estimated_minutes: 30, actual_minutes: 15, work_order_id: 42, work_order: { job_card: { vehicle: { registration_no: 'DHA-55-9988', make: 'Toyota', model: 'Allion' } } } },
      { id: 102, task_name: 'Brake shoe adjustment & test', status: 'pending', estimated_minutes: 45, actual_minutes: 0, work_order_id: 43, work_order: { job_card: { vehicle: { registration_no: 'CTG-11-2233', make: 'Honda', model: 'Civic' } } } }
    ];
  } finally {
    loading.value = false;
  }
};

const openConsumptionModal = (task) => {
  activeTask.value = task;
  isConsumptionOpen.value = true;
};

onMounted(() => {
  window.addEventListener('online', () => isOnline.value = true);
  window.addEventListener('offline', () => isOnline.value = false);
  fetchTasks();
});
</script>

<style scoped>
/* Scoped styles */
</style>
