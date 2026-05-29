<template>
  <div class="bg-gray-900 border border-gray-800 rounded-3xl shadow-2xl p-6 overflow-hidden">
    <div class="flex items-center justify-between border-b border-gray-800 pb-5 mb-6">
      <div>
        <h1 class="text-2xl font-extrabold text-white tracking-tight flex items-center gap-3">
          Alert Notification Center
          <span 
            v-if="store.unreadCount > 0" 
            class="bg-rose-500/20 text-rose-400 text-xs px-3 py-1 rounded-full font-bold border border-rose-500/30 animate-pulse"
          >
            {{ store.unreadCount }} Pending
          </span>
        </h1>
        <p class="text-xs text-gray-500 mt-1">Realtime operations and queue monitoring ledger</p>
      </div>
      <div class="flex items-center gap-3">
        <button 
          @click="store.fetchNotifications()" 
          class="p-2.5 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-xl transition duration-200 border border-gray-700 hover:shadow-lg focus:outline-none"
          title="Refresh Feed"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
        </button>
        <button 
          @click="store.clearReadNotifications()" 
          class="px-4 py-2.5 bg-rose-600/20 text-rose-400 hover:bg-rose-600 hover:text-white rounded-xl transition-all duration-200 border border-rose-500/30 text-xs font-bold shadow-md focus:outline-none"
        >
          Clear Read
        </button>
      </div>
    </div>

    <!-- Active Filters -->
    <div class="flex flex-wrap gap-2 mb-6">
      <button 
        v-for="filter in ['all', 'unread', 'critical', 'info']" 
        :key="filter"
        @click="activeFilter = filter"
        class="px-3.5 py-1.5 rounded-lg text-xs font-bold border transition-all duration-200"
        :class="getFilterButtonClass(filter)"
      >
        {{ filter.toUpperCase() }}
      </button>
    </div>

    <!-- Notifications List -->
    <div class="space-y-4">
      <div v-if="store.loading" class="flex flex-col items-center justify-center py-20 space-y-4">
        <div class="w-12 h-12 border-4 border-rose-500 border-t-transparent rounded-full animate-spin"></div>
        <p class="text-sm text-gray-400 font-bold">Synchronizing reporting feed...</p>
      </div>

      <div v-else-if="filteredNotifications.length === 0" class="flex flex-col items-center justify-center py-24 text-center border border-dashed border-gray-800 rounded-2xl">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-14 h-14 text-gray-750 mb-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
        </svg>
        <h3 class="text-sm font-extrabold text-gray-400">No alert logs found</h3>
        <p class="text-xs text-gray-500 mt-2 max-w-sm px-6">There are no operational anomalies matching the active filters recorded in your database.</p>
      </div>

      <div 
        v-else
        v-for="item in filteredNotifications" 
        :key="item.id"
        class="group relative flex gap-4 p-5 rounded-2xl border transition-all duration-300 hover:shadow-xl"
        :class="getItemBorderClass(item)"
      >
        <!-- Custom Indicator -->
        <div class="flex-shrink-0">
          <div 
            class="w-11 h-11 rounded-2xl flex items-center justify-center shadow-inner"
            :class="getIndicatorClass(item.data?.type ?? '')"
          >
            <svg v-if="isWarningAlert(item.data?.type ?? '')" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-amber-400">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-emerald-400">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
            </svg>
          </div>
        </div>

        <!-- Contents -->
        <div class="flex-1 min-w-0">
          <div class="flex items-start justify-between gap-4">
            <div>
              <h3 class="text-sm font-bold text-white tracking-wide">
                {{ item.data?.title || 'System Notification' }}
              </h3>
              <p class="text-[10px] text-gray-500 mt-0.5">
                Received {{ formatDateTime(item.created_at) }}
              </p>
            </div>
            
            <div class="flex items-center gap-2">
              <span 
                v-if="!item.read_at" 
                class="px-2 py-0.5 rounded bg-rose-500/10 text-rose-400 text-[10px] font-extrabold border border-rose-500/20 tracking-wider uppercase animate-pulse"
              >
                UNREAD
              </span>
              <button 
                v-if="!item.read_at"
                @click="store.markAsRead(item.id)" 
                class="p-2 text-gray-500 hover:text-gray-200 hover:bg-gray-800 rounded-lg transition"
                title="Mark as read"
              >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
              </button>
            </div>
          </div>
          
          <p class="text-xs text-gray-400 mt-2.5 leading-relaxed pr-6">
            {{ item.data?.message }}
          </p>

          <!-- Attachment Metadata Details -->
          <div v-if="item.data?.quotation_no || item.data?.work_order_no || item.data?.invoice_no" class="flex flex-wrap gap-2 mt-4 bg-gray-950/60 p-3 rounded-xl border border-gray-800">
            <span v-if="item.data?.quotation_no" class="bg-gray-900 border border-gray-800 text-[10px] font-bold text-gray-400 px-2.5 py-1 rounded-md">
              Quote ID: {{ item.data?.quotation_no }}
            </span>
            <span v-if="item.data?.work_order_no" class="bg-gray-900 border border-gray-800 text-[10px] font-bold text-gray-400 px-2.5 py-1 rounded-md">
              Work Order: {{ item.data?.work_order_no }}
            </span>
            <span v-if="item.data?.invoice_no" class="bg-gray-900 border border-gray-800 text-[10px] font-bold text-gray-400 px-2.5 py-1 rounded-md">
              Invoice: {{ item.data?.invoice_no }}
            </span>
            <span v-if="item.data?.amount" class="bg-emerald-950/40 border border-emerald-800/30 text-[10px] font-bold text-emerald-400 px-2.5 py-1 rounded-md">
              Grand Total: ${{ parseFloat(item.data.amount).toFixed(2) }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useNotificationStore } from '../../stores/notification';

const store = useNotificationStore();
const activeFilter = ref('all');

const filteredNotifications = computed(() => {
  if (!store.notifications) return [];
  
  return store.notifications.filter(item => {
    if (activeFilter.value === 'unread') {
      return !item.read_at;
    }
    if (activeFilter.value === 'critical') {
      return isWarningAlert(item.data?.type ?? '');
    }
    if (activeFilter.value === 'info') {
      return !isWarningAlert(item.data?.type ?? '');
    }
    return true;
  });
});

const getFilterButtonClass = (filter) => {
  if (activeFilter.value === filter) {
    return 'bg-rose-600 text-white border-rose-600 shadow-lg shadow-rose-900/20';
  }
  return 'bg-gray-800 hover:bg-gray-700 text-gray-400 border-gray-750 hover:text-white';
};

const getItemBorderClass = (item) => {
  if (!item.read_at) {
    return 'bg-gray-850 border-rose-500/20 shadow-rose-950/5';
  }
  return 'bg-gray-900 border-gray-800 hover:bg-gray-850';
};

const getIndicatorClass = (type) => {
  if (isWarningAlert(type)) {
    return 'bg-amber-950/40 border border-amber-800/30 shadow-inner';
  }
  return 'bg-emerald-950/40 border border-emerald-800/30 shadow-inner';
};

const isWarningAlert = (type) => {
  const t = type.toLowerCase();
  return t.includes('fail') || t.includes('shortage') || t.includes('delay') || t.includes('warn') || t.includes('critical');
};

const formatDateTime = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleString(undefined, { 
    month: 'short', 
    day: 'numeric', 
    hour: '2-digit', 
    minute: '2-digit' 
  });
};

onMounted(() => {
  store.fetchNotifications();
});
</script>

<style scoped>
/* Optional custom CSS */
</style>
