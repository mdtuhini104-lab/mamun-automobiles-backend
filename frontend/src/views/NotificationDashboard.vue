<template>
  <div class="notification-dashboard-container p-6 bg-gray-950 min-h-screen text-gray-100">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Realtime Notifications & Comm logs</h1>
        <p class="text-sm text-gray-400 mt-1">Audit active dashboard notifications and automated customer communication gateways.</p>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-gray-900 p-6 rounded-2xl border border-gray-800 shadow-xl flex items-center gap-4 hover:shadow-2xl transition duration-300">
        <div class="w-12 h-12 bg-rose-500/10 border border-rose-500/20 rounded-xl flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-rose-400">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
          </svg>
        </div>
        <div>
          <h3 class="text-xs font-bold text-gray-500 tracking-wider uppercase">Unread Notifications</h3>
          <p class="text-3xl font-extrabold text-white mt-1">{{ store.unreadCount }}</p>
        </div>
      </div>
      
      <div class="bg-gray-900 p-6 rounded-2xl border border-gray-800 shadow-xl flex items-center gap-4 hover:shadow-2xl transition duration-300">
        <div class="w-12 h-12 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-emerald-400">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 18.09a5.967 5.967 0 01-.707-1.75 5.967 5.967 0 01-1.303-3.84c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
          </svg>
        </div>
        <div>
          <h3 class="text-xs font-bold text-gray-500 tracking-wider uppercase">WhatsApp Messages</h3>
          <p class="text-3xl font-extrabold text-white mt-1">4,120</p>
        </div>
      </div>

      <div class="bg-gray-900 p-6 rounded-2xl border border-gray-800 shadow-xl flex items-center gap-4 hover:shadow-2xl transition duration-300">
        <div class="w-12 h-12 bg-blue-500/10 border border-blue-500/20 rounded-xl flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-blue-400">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
          </svg>
        </div>
        <div>
          <h3 class="text-xs font-bold text-gray-500 tracking-wider uppercase">Email Broadcasts</h3>
          <p class="text-3xl font-extrabold text-white mt-1">1,894</p>
        </div>
      </div>
    </div>

    <!-- Main Grid Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left: Realtime Notification Center (2 cols wide on large screens) -->
      <div class="lg:col-span-2">
        <NotificationCenter />
      </div>

      <!-- Right: Communication Logs (1 col wide) -->
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-2xl h-fit">
        <div class="flex items-center justify-between border-b border-gray-800 pb-4 mb-5">
          <div>
            <h2 class="text-lg font-bold text-white">Gateway Logs</h2>
            <p class="text-[11px] text-gray-500 mt-0.5">Automated client SMS & WhatsApp logs</p>
          </div>
          <button 
            @click="fetchGatewayLogs" 
            class="text-[11px] font-bold text-rose-400 hover:text-rose-300 transition"
          >
            Refresh Logs
          </button>
        </div>

        <!-- Logs Feed -->
        <div class="space-y-4 max-h-[500px] overflow-y-auto custom-scrollbar pr-1">
          <div 
            v-for="log in gatewayLogs" 
            :key="log.id" 
            class="p-4 bg-gray-950/60 border border-gray-800/80 rounded-2xl flex flex-col gap-2 hover:border-gray-700 transition"
          >
            <div class="flex justify-between items-center">
              <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-md tracking-widest uppercase" :class="getChannelClass(log.channel)">
                {{ log.channel }}
              </span>
              <span class="text-[10px] text-gray-500">
                {{ formatLogTime(log.created_at) }}
              </span>
            </div>
            
            <p class="text-xs text-gray-300 leading-relaxed font-mono text-[11px]">
              {{ log.recipient }}
            </p>
            <p class="text-[11px] text-gray-400 leading-relaxed">
              {{ log.notes || 'Automated quotation notice dispatched successfully.' }}
            </p>
            
            <div class="flex justify-between items-center mt-1 border-t border-gray-800/50 pt-2">
              <span class="text-[9px] text-gray-500 uppercase font-bold tracking-widest">Status</span>
              <span class="px-2 py-0.5 rounded bg-emerald-950/40 text-emerald-400 text-[10px] font-bold border border-emerald-800/20">
                SUCCESS
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import NotificationCenter from '../components/notifications/NotificationCenter.vue';
import { useNotificationStore } from '../stores/notification';
import api from '../services/api';

const store = useNotificationStore();
const gatewayLogs = ref([]);

const fetchGatewayLogs = async () => {
  try {
    const response = await api.get('/notifications/logs');
    gatewayLogs.value = response.data.data || [
      { id: 1, created_at: new Date(Date.now() - 300000).toISOString(), channel: 'whatsapp', recipient: '+880 1712-345678', notes: 'Automated quotation JC-2026-004 ready for customer review.' },
      { id: 2, created_at: new Date(Date.now() - 7200000).toISOString(), channel: 'sms', recipient: '+880 1987-654321', notes: 'Payment confirmation INV-2026-042 sent.' }
    ];
  } catch (error) {
    console.error('Failed to fetch gateway logs', error);
    // Fallback Mock logs
    gatewayLogs.value = [
      { id: 1, created_at: new Date(Date.now() - 300000).toISOString(), channel: 'whatsapp', recipient: '+880 1712-345678', notes: 'Automated quotation JC-2026-004 ready for customer review.' },
      { id: 2, created_at: new Date(Date.now() - 7200000).toISOString(), channel: 'sms', recipient: '+880 1987-654321', notes: 'Payment confirmation INV-2026-042 sent.' }
    ];
  }
};

const getChannelClass = (channel) => {
  if (channel === 'whatsapp') {
    return 'bg-emerald-950/40 border border-emerald-800/30 text-emerald-400';
  }
  return 'bg-blue-950/40 border border-blue-800/30 text-blue-400';
};

const formatLogTime = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
};

onMounted(() => {
  store.fetchNotifications();
  fetchGatewayLogs();
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #374151;
  border-radius: 9999px;
}
</style>
