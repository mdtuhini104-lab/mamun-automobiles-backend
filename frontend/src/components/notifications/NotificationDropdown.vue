<template>
  <div class="relative dropdown-container">
    <!-- Trigger Button -->
    <button 
      @click="toggleDropdown" 
      class="relative p-2.5 text-gray-400 hover:text-gray-100 bg-gray-800 hover:bg-gray-700 rounded-xl transition-all duration-300 border border-gray-700 shadow-md flex items-center justify-center focus:outline-none"
      aria-label="Notifications"
    >
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
      </svg>
      <!-- Unread Badge -->
      <span 
        v-if="store.unreadCount > 0" 
        class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-[10px] font-extrabold text-white animate-bounce shadow-lg ring-2 ring-gray-900"
      >
        {{ store.unreadCount }}
      </span>
    </button>

    <!-- Dropdown Menu -->
    <transition name="fade-slide">
      <div 
        v-if="isOpen" 
        class="absolute right-0 mt-3 w-[380px] origin-top-right rounded-2xl border border-gray-700 bg-gray-900 shadow-2xl ring-1 ring-black ring-opacity-5 focus:outline-none z-50 overflow-hidden"
      >
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-800 bg-gray-950 px-5 py-4">
          <h2 class="text-sm font-bold text-gray-100 flex items-center gap-2">
            Notifications
            <span v-if="store.unreadCount > 0" class="bg-gray-800 text-gray-400 text-xs px-2 py-0.5 rounded-md">
              {{ store.unreadCount }} new
            </span>
          </h2>
          <button 
            @click="store.clearReadNotifications()" 
            class="text-xs font-semibold text-rose-400 hover:text-rose-300 transition-colors"
          >
            Clear Read
          </button>
        </div>

        <!-- Notification List -->
        <div class="max-h-[360px] overflow-y-auto divide-y divide-gray-800 bg-gray-900 custom-scrollbar">
          <div v-if="store.loading" class="flex flex-col items-center justify-center py-10 space-y-3">
            <div class="w-8 h-8 border-4 border-rose-500 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-xs text-gray-500">Loading alerts...</p>
          </div>
          
          <div v-else-if="store.notifications.length === 0" class="flex flex-col items-center justify-center py-12 text-center px-6">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-gray-600 mb-3">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            <h3 class="text-xs font-bold text-gray-400">All caught up!</h3>
            <p class="text-[11px] text-gray-500 mt-1">No pending alerts on your dashboard.</p>
          </div>

          <div 
            v-else
            v-for="notification in store.notifications" 
            :key="notification.id"
            class="group relative flex gap-3.5 p-4 hover:bg-gray-850 transition-colors duration-200"
            :class="{ 'bg-gray-950/40 border-l-4 border-rose-500': !notification.read_at }"
          >
            <!-- Severity/Type Icon Indicator -->
            <div class="flex-shrink-0 mt-0.5">
              <div 
                class="w-9 h-9 rounded-xl flex items-center justify-center"
                :class="getIndicatorClass(notification.data?.type ?? '')"
              >
                <svg v-if="isWarningAlert(notification.data?.type ?? '')" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-amber-400">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-emerald-400">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                </svg>
              </div>
            </div>

            <!-- Message Details -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between">
                <p class="text-xs font-bold text-gray-100 truncate">
                  {{ notification.data?.title || 'System Notification' }}
                </p>
                <span class="text-[10px] text-gray-500 whitespace-nowrap">
                  {{ formatTime(notification.created_at) }}
                </span>
              </div>
              <p class="text-[11px] text-gray-400 mt-1 leading-relaxed line-clamp-2">
                {{ notification.data?.message }}
              </p>
              
              <!-- Action Button -->
              <div class="flex gap-2 mt-2">
                <button 
                  v-if="!notification.read_at"
                  @click.stop="store.markAsRead(notification.id)" 
                  class="text-[10px] text-rose-400 hover:text-rose-300 font-bold tracking-wide transition-colors"
                >
                  Mark as read
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-800 bg-gray-950/80 px-5 py-3 text-center">
          <router-link 
            :to="{ name: 'activity-logs.index' }"
            @click="isOpen = false"
            class="text-[11px] font-extrabold text-gray-400 hover:text-gray-100 transition-colors tracking-widest uppercase flex items-center justify-center gap-1"
          >
            View all logs
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
          </router-link>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useNotificationStore } from '../../stores/notification';

const store = useNotificationStore();
const isOpen = ref(false);

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    store.fetchNotifications();
  }
};

const getIndicatorClass = (type) => {
  if (isWarningAlert(type)) {
    return 'bg-amber-950/40 border border-amber-800/30';
  }
  return 'bg-emerald-950/40 border border-emerald-800/30';
};

const isWarningAlert = (type) => {
  const t = type.toLowerCase();
  return t.includes('fail') || t.includes('shortage') || t.includes('delay') || t.includes('warn') || t.includes('critical');
};

const formatTime = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  const diffMs = Date.now() - date.getTime();
  const diffMins = Math.floor(diffMs / 60000);
  
  if (diffMins < 1) return 'Just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  
  const diffHours = Math.floor(diffMins / 60);
  if (diffHours < 24) return `${diffHours}h ago`;
  
  return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
};

// Handle closing when clicking outside dropdown
const closeOnOutsideClick = (e) => {
  if (isOpen.value && !e.target.closest('.dropdown-container')) {
    isOpen.value = false;
  }
};

onMounted(() => {
  store.fetchNotifications();
  store.setupRealtimeListener();
  window.addEventListener('click', closeOnOutsideClick);
});

onUnmounted(() => {
  window.removeEventListener('click', closeOnOutsideClick);
});
</script>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(-8px) scale(0.97);
}
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.97);
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #374151;
  border-radius: 9999px;
}
</style>
