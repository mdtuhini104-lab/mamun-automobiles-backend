<template>
  <div class="min-h-screen bg-gray-900 text-white flex overflow-hidden">
    <!-- Mobile Sidebar Overlay -->
    <div 
      v-if="isMobileMenuOpen" 
      class="fixed inset-0 bg-black/50 z-40 md:hidden"
      @click="isMobileMenuOpen = false"
    ></div>

    <!-- Sidebar -->
    <aside 
      class="w-64 bg-gray-800 border-r border-gray-700 flex-shrink-0 flex flex-col fixed md:relative z-50 h-full transition-transform duration-300"
      :class="isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    >
      <div class="p-6 border-b border-gray-700 flex justify-between items-center">
        <h2 class="text-xl font-bold tracking-tight text-indigo-400">ERP System</h2>
        <button class="md:hidden text-gray-400 hover:text-white" @click="isMobileMenuOpen = false">✕</button>
      </div>
      <nav class="p-4 space-y-1 flex-1 overflow-y-auto">
        <router-link :to="{ name: 'dashboard-home' }" class="block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">Overview</router-link>
        <router-link :to="{ name: 'inventory-list' }" class="block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">Inventory</router-link>
        <router-link :to="{ name: 'pos-home' }" class="block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">Point of Sale</router-link>
        <router-link :to="{ name: 'purchases-list' }" class="block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">Purchases</router-link>
        <router-link :to="{ name: 'invoices-list' }" class="block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">Invoices</router-link>
        <router-link :to="{ name: 'reports-home' }" class="block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">Reports</router-link>
      </nav>
      <div class="p-4 border-t border-gray-700">
        <button @click="handleLogout" class="w-full flex items-center justify-center px-4 py-2 bg-red-900/50 text-red-400 rounded-lg text-sm font-medium hover:bg-red-900 hover:text-red-300 transition-colors">
          Logout
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden">
      <!-- Mobile Header -->
      <header class="bg-gray-800 border-b border-gray-700 p-4 flex items-center md:hidden shrink-0">
        <button class="text-gray-400 hover:text-white mr-4" @click="isMobileMenuOpen = true">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
        <h1 class="text-lg font-bold text-indigo-400 truncate">Mamun Automobiles</h1>
      </header>
      
      <div class="flex-1 overflow-y-auto p-4 md:p-8 relative">
        <router-view></router-view>
      </div>
    </main>

    <!-- Global Components -->
    <ToastContainer />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import ToastContainer from '../../components/ui/ToastContainer.vue';

const router = useRouter();
const authStore = useAuthStore();
const isMobileMenuOpen = ref(false);

const handleLogout = async () => {
  await authStore.logout();
  router.push({ name: 'login' });
};
</script>

<style scoped>
/* Optional active link styling */
.router-link-active {
  background-color: #374151; /* gray-700 */
  color: white;
}
</style>
