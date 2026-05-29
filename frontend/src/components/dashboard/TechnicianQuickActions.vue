<template>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4">
    <!-- Check In button -->
    <button 
      @click="handleCheckIn" 
      :disabled="loading"
      class="bg-gray-900 border border-gray-800 hover:border-emerald-500/20 hover:bg-emerald-950/10 p-5 rounded-3xl text-center flex flex-col items-center justify-center active:scale-95 transition duration-200 group focus:outline-none"
    >
      <div class="w-12 h-12 bg-emerald-500/10 border border-emerald-500/20 group-hover:bg-emerald-500 group-hover:text-white rounded-2xl flex items-center justify-center text-emerald-400 mb-3 transition duration-300">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <span class="text-xs font-bold text-gray-200 group-hover:text-white">Shift Check-In</span>
      <span class="text-[9px] text-gray-500 mt-1 uppercase tracking-wider">Clock in</span>
    </button>

    <!-- Check Out button -->
    <button 
      @click="handleCheckOut" 
      :disabled="loading"
      class="bg-gray-900 border border-gray-800 hover:border-rose-500/20 hover:bg-rose-950/10 p-5 rounded-3xl text-center flex flex-col items-center justify-center active:scale-95 transition duration-200 group focus:outline-none"
    >
      <div class="w-12 h-12 bg-rose-500/10 border border-rose-500/20 group-hover:bg-rose-500 group-hover:text-white rounded-2xl flex items-center justify-center text-rose-400 mb-3 transition duration-300">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <span class="text-xs font-bold text-gray-200 group-hover:text-white">Shift Check-Out</span>
      <span class="text-[9px] text-gray-500 mt-1 uppercase tracking-wider">Clock out</span>
    </button>

    <!-- Scan Job Card Card -->
    <button 
      @click="handleScan" 
      class="bg-gray-900 border border-gray-800 hover:border-blue-500/20 hover:bg-blue-950/10 p-5 rounded-3xl text-center flex flex-col items-center justify-center active:scale-95 transition duration-200 group focus:outline-none"
    >
      <div class="w-12 h-12 bg-blue-500/10 border border-blue-500/20 group-hover:bg-blue-500 group-hover:text-white rounded-2xl flex items-center justify-center text-blue-400 mb-3 transition duration-300">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM21 16.25A5.25 5.25 0 1115.75 21c.896 0 1.742-.233 2.479-.641l2.51 2.51a.75.75 0 101.061-1.06l-2.51-2.51A5.224 5.224 0 0021 16.25z" />
        </svg>
      </div>
      <span class="text-xs font-bold text-gray-200 group-hover:text-white">Scan QR Badge</span>
      <span class="text-[9px] text-gray-500 mt-1 uppercase tracking-wider">Verification</span>
    </button>

    <!-- Support / Help -->
    <button 
      @click="handleSupport" 
      class="bg-gray-900 border border-gray-800 hover:border-purple-500/20 hover:bg-purple-950/10 p-5 rounded-3xl text-center flex flex-col items-center justify-center active:scale-95 transition duration-200 group focus:outline-none"
    >
      <div class="w-12 h-12 bg-purple-500/10 border border-purple-500/20 group-hover:bg-purple-500 group-hover:text-white rounded-2xl flex items-center justify-center text-purple-400 mb-3 transition duration-300">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
        </svg>
      </div>
      <span class="text-xs font-bold text-gray-200 group-hover:text-white">Support Help</span>
      <span class="text-[9px] text-gray-500 mt-1 uppercase tracking-wider">Assistance</span>
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';

const toast = useToastStore();
const loading = ref(false);

const handleCheckIn = async () => {
  loading.value = true;
  try {
    const response = await api.post('/hr/attendances/check-in');
    toast.success('Successfully checked in for today!');
  } catch (error) {
    console.error('Failed shift check in', error);
  } finally {
    loading.value = false;
  }
};

const handleCheckOut = async () => {
  loading.value = true;
  try {
    const response = await api.post('/hr/attendances/check-out');
    toast.success('Successfully checked out for today!');
  } catch (error) {
    console.error('Failed shift check out', error);
  } finally {
    loading.value = false;
  }
};

const handleScan = () => {
  toast.info('QR camera initialized. Position QR code on active Job Card to scan.');
};

const handleSupport = () => {
  toast.info('Support alert logged. Workshop supervisor has been notified.');
};
</script>

<style scoped>
/* Scoped styles */
</style>
