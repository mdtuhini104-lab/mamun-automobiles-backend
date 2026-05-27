<template>
  <div class="system-dashboard p-6 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800">System Monitoring & Backups</h1>
      <div class="flex space-x-3">
        <button class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700 transition">
          Generate Full Backup
        </button>
        <button class="bg-red-600 text-white px-4 py-2 rounded shadow hover:bg-red-700 transition">
          Emergency Restore
        </button>
      </div>
    </div>

    <!-- Health Widgets -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
      <div class="bg-white p-5 rounded-xl shadow border border-gray-100 flex items-center justify-between">
        <div>
          <p class="text-gray-500 text-sm font-medium">CPU Usage</p>
          <p class="text-2xl font-bold text-gray-800">{{ health.cpu_usage || '15%' }}</p>
        </div>
        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
        </div>
      </div>
      <div class="bg-white p-5 rounded-xl shadow border border-gray-100 flex items-center justify-between">
        <div>
          <p class="text-gray-500 text-sm font-medium">RAM Usage</p>
          <p class="text-2xl font-bold text-gray-800">{{ health.ram_usage || '45%' }}</p>
        </div>
        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
        </div>
      </div>
      <div class="bg-white p-5 rounded-xl shadow border border-gray-100 flex items-center justify-between">
        <div>
          <p class="text-gray-500 text-sm font-medium">Disk Space</p>
          <p class="text-2xl font-bold text-gray-800">{{ health.disk_usage || '62%' }}</p>
        </div>
        <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
        </div>
      </div>
      <div class="bg-white p-5 rounded-xl shadow border border-gray-100 flex items-center justify-between">
        <div>
          <p class="text-gray-500 text-sm font-medium">API Latency</p>
          <p class="text-2xl font-bold text-gray-800">{{ health.api_response_time || '120ms' }}</p>
        </div>
        <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
        </div>
      </div>
    </div>

    <!-- Backup History & Security Logs -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
        <div class="p-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
          <h2 class="font-bold text-gray-800">Recent Backups</h2>
          <span class="text-xs text-indigo-600 font-medium">Auto-Backup Active</span>
        </div>
        <div class="p-0">
          <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-500">
              <tr>
                <th class="p-4 font-medium">Date</th>
                <th class="p-4 font-medium">Size</th>
                <th class="p-4 font-medium">Type</th>
                <th class="p-4 font-medium text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr class="border-t border-gray-100 hover:bg-gray-50">
                <td class="p-4">Today, 02:00 AM</td>
                <td class="p-4">250 MB</td>
                <td class="p-4"><span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded text-xs">Full</span></td>
                <td class="p-4 text-right space-x-2">
                  <button class="text-blue-600 hover:underline">Download</button>
                  <button class="text-red-600 hover:underline">Restore</button>
                </td>
              </tr>
              <tr class="border-t border-gray-100 hover:bg-gray-50">
                <td class="p-4">Yesterday, 02:00 AM</td>
                <td class="p-4">245 MB</td>
                <td class="p-4"><span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded text-xs">Full</span></td>
                <td class="p-4 text-right space-x-2">
                  <button class="text-blue-600 hover:underline">Download</button>
                  <button class="text-red-600 hover:underline">Restore</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
        <div class="p-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
          <h2 class="font-bold text-gray-800">Security & Anomaly Logs</h2>
          <span class="text-xs text-green-600 font-medium flex items-center">
            <span class="w-2 h-2 rounded-full bg-green-500 mr-1"></span> Protected
          </span>
        </div>
        <div class="p-4 space-y-4">
          <div class="flex space-x-3 items-start">
            <div class="mt-1 w-2 h-2 rounded-full bg-red-500 shrink-0"></div>
            <div>
              <p class="text-sm font-bold text-gray-800">Failed Login Spikes</p>
              <p class="text-xs text-gray-500">15 failed attempts from IP 192.168.1.55 (Blocked)</p>
              <p class="text-xs text-gray-400 mt-1">2 hours ago</p>
            </div>
          </div>
          <div class="flex space-x-3 items-start">
            <div class="mt-1 w-2 h-2 rounded-full bg-yellow-500 shrink-0"></div>
            <div>
              <p class="text-sm font-bold text-gray-800">High CPU Usage Warning</p>
              <p class="text-xs text-gray-500">CPU spiked to 92% during report generation.</p>
              <p class="text-xs text-gray-400 mt-1">5 hours ago</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../services/api';

const health = ref({});

const fetchHealth = async () => {
    try {
        const res = await api.get('/system/health');
        health.value = res.data;
    } catch (error) {
        console.error('Error fetching system health', error);
    }
};

onMounted(() => {
    fetchHealth();
});
</script>
