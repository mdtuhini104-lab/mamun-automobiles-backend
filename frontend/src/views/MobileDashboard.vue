<template>
  <div class="mobile-app bg-gray-100 min-h-screen pb-20 font-sans">
    <!-- Header -->
    <header class="bg-indigo-600 text-white p-4 shadow-md sticky top-0 z-50 flex justify-between items-center">
      <div class="flex items-center">
        <div class="w-10 h-10 rounded-full bg-indigo-800 flex items-center justify-center mr-3">
          <span class="font-bold">MA</span>
        </div>
        <div>
          <h1 class="text-lg font-bold leading-tight">Mamun Auto</h1>
          <p class="text-xs text-indigo-200">Main Branch</p>
        </div>
      </div>
      <div class="relative">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
        <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
      </div>
    </header>

    <!-- Offline Banner -->
    <div v-if="!isOnline" class="bg-yellow-500 text-white text-xs text-center py-1 font-bold animate-pulse">
      You are currently offline. Changes will sync automatically.
    </div>

    <!-- Quick Actions -->
    <div class="p-4 grid grid-cols-4 gap-2">
      <button class="bg-white p-3 rounded-xl shadow text-center flex flex-col items-center justify-center active:bg-gray-50">
        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 mb-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        </div>
        <span class="text-xs font-medium">New Job</span>
      </button>
      <button class="bg-white p-3 rounded-xl shadow text-center flex flex-col items-center justify-center active:bg-gray-50">
        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600 mb-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <span class="text-xs font-medium">Invoice</span>
      </button>
      <button class="bg-white p-3 rounded-xl shadow text-center flex flex-col items-center justify-center active:bg-gray-50">
        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 mb-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <span class="text-xs font-medium">Attend</span>
      </button>
      <button class="bg-white p-3 rounded-xl shadow text-center flex flex-col items-center justify-center active:bg-gray-50">
        <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 mb-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
        </div>
        <span class="text-xs font-medium">Scan QR</span>
      </button>
    </div>

    <!-- Active Jobs -->
    <div class="px-4 mb-6">
      <div class="flex justify-between items-end mb-3">
        <h2 class="text-lg font-bold text-gray-800">Active Jobs</h2>
        <span class="text-indigo-600 text-sm font-medium">View All</span>
      </div>
      <div class="space-y-3">
        <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-yellow-500">
          <div class="flex justify-between items-start">
            <div>
              <span class="text-xs text-gray-500 font-medium block">JC-2023-091</span>
              <h3 class="font-bold text-gray-800">DHA-11-2233</h3>
            </div>
            <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded font-bold">Repairing</span>
          </div>
          <p class="text-sm text-gray-600 mt-2">Engine tuning and oil change</p>
        </div>
      </div>
    </div>

    <!-- Bottom Navigation -->
    <nav class="bg-white border-t border-gray-200 fixed bottom-0 w-full flex justify-around p-3 pb-safe z-50 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)]">
      <button class="flex flex-col items-center text-indigo-600">
        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
        <span class="text-[10px] font-medium">Home</span>
      </button>
      <button class="flex flex-col items-center text-gray-500 hover:text-indigo-600">
        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        <span class="text-[10px] font-medium">Jobs</span>
      </button>
      <button class="flex flex-col items-center text-gray-500 hover:text-indigo-600">
        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        <span class="text-[10px] font-medium">Reports</span>
      </button>
      <button class="flex flex-col items-center text-gray-500 hover:text-indigo-600">
        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
        <span class="text-[10px] font-medium">Profile</span>
      </button>
    </nav>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const isOnline = ref(navigator.onLine);

onMounted(() => {
  window.addEventListener('online', () => isOnline.value = true);
  window.addEventListener('offline', () => isOnline.value = false);
});
</script>
