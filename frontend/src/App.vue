<template>
  <div>
    <!-- Route Transition Progress Bar -->
    <div 
      class="fixed top-0 left-0 h-1 bg-indigo-500 z-[200] transition-all duration-300 ease-out"
      :class="isRouteLoading ? 'w-full opacity-100' : 'w-0 opacity-0'"
    ></div>

    <!-- Offline Banner -->
    <div v-if="isOffline" class="bg-red-600 text-white text-center py-2 px-4 text-sm font-medium sticky top-0 z-[100] shadow-md">
      You are currently offline. Some features may not be available until your connection is restored.
    </div>
    <router-view v-slot="{ Component }">
      <template v-if="Component">
        <Suspense>
          <template #default>
            <component :is="Component" />
          </template>
          <template #fallback>
            <div class="fixed inset-0 bg-white flex flex-col items-center justify-center z-[999]">
              <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-indigo-600 mb-4"></div>
              <p class="text-slate-500 font-medium">Loading Application...</p>
            </div>
          </template>
        </Suspense>
      </template>
    </router-view>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';

const isOffline = ref(!navigator.onLine);
const isRouteLoading = ref(false);
const router = useRouter();

router.beforeEach((to, from, next) => {
  if (to.name !== from.name) {
    isRouteLoading.value = true;
  }
  next();
});

router.afterEach(() => {
  setTimeout(() => {
    isRouteLoading.value = false;
  }, 300); // small delay to let transitions finish smoothly
});

const updateOnlineStatus = () => {
  isOffline.value = !navigator.onLine;
};

onMounted(() => {
  window.addEventListener('online', updateOnlineStatus);
  window.addEventListener('offline', updateOnlineStatus);
});

onUnmounted(() => {
  window.removeEventListener('online', updateOnlineStatus);
  window.removeEventListener('offline', updateOnlineStatus);
});
</script>
