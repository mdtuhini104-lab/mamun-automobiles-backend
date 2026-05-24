<template>
  <div class="notification-dashboard p-6 bg-gray-50 min-h-screen">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Notification Dashboard</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white p-6 rounded-xl shadow border-l-4 border-blue-500">
        <h3 class="text-gray-500 text-sm font-medium">Total SMS Sent</h3>
        <p class="text-3xl font-bold text-gray-800 mt-2">1,245</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow border-l-4 border-green-500">
        <h3 class="text-gray-500 text-sm font-medium">Total WhatsApp Sent</h3>
        <p class="text-3xl font-bold text-gray-800 mt-2">3,892</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow border-l-4 border-red-500">
        <h3 class="text-gray-500 text-sm font-medium">Failed Notifications</h3>
        <p class="text-3xl font-bold text-gray-800 mt-2">12</p>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Recent Logs</h2>
        <button class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700">Refresh</button>
      </div>
      <table class="min-w-full divide-y divide-gray-200">
        <thead>
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Channel</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipient</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="log in logs" :key="log.id">
            <td class="px-6 py-4 whitespace-nowrap">{{ log.date }}</td>
            <td class="px-6 py-4 whitespace-nowrap uppercase font-bold">{{ log.channel }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ log.recipient }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                {{ log.status }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const logs = ref([
  { id: 1, date: '2023-10-10 10:00:00', channel: 'sms', recipient: '+8801700000000', status: 'success' },
  { id: 2, date: '2023-10-10 10:05:00', channel: 'whatsapp', recipient: '+8801800000000', status: 'success' },
]);
</script>
