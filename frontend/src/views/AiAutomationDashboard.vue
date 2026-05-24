<template>
  <div class="ai-dashboard p-6 bg-gray-900 text-white min-h-screen">
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-3xl font-bold text-white flex items-center">
          <svg class="w-8 h-8 mr-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
          AI Automation & Intelligence
        </h1>
        <p class="text-gray-400 mt-1">Smart predictions, anomalies, and auto-generated tasks</p>
      </div>
      <button @click="runAutomations" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded shadow-lg flex items-center transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
        Run AI Engine
      </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <!-- AI Prediction Card -->
      <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 right-0 p-4 opacity-10">
          <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path></svg>
        </div>
        <h3 class="text-gray-400 font-medium mb-2 uppercase tracking-wider text-xs">AI Revenue Forecast</h3>
        <p class="text-3xl font-bold text-white mb-2">৳ {{ predictions.forecast?.toLocaleString() || '...' }}</p>
        <p class="text-sm flex items-center" :class="predictions.trend === 'up' ? 'text-green-400' : 'text-red-400'">
          Confidence: {{ predictions.confidence }}%
        </p>
      </div>

      <!-- Anomaly Alert Card -->
      <div class="bg-gray-800 p-6 rounded-xl border border-red-900/50 shadow-xl">
        <h3 class="text-gray-400 font-medium mb-2 uppercase tracking-wider text-xs">Anomalies Detected</h3>
        <p class="text-3xl font-bold text-red-400 mb-2">{{ anomalies }}</p>
        <p class="text-sm text-gray-500">Unusual expense spikes detected</p>
      </div>

      <!-- AI Recommendations Card -->
      <div class="bg-gray-800 p-6 rounded-xl border border-indigo-900/50 shadow-xl">
        <h3 class="text-gray-400 font-medium mb-2 uppercase tracking-wider text-xs">Smart Recommendations</h3>
        <p class="text-3xl font-bold text-indigo-400 mb-2">{{ recommendations }}</p>
        <p class="text-sm text-gray-500">Service reminders & reorder alerts</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <h2 class="text-lg font-bold text-white mb-4">Auto-Generated Tasks</h2>
        <div class="space-y-4">
          <div class="p-4 bg-gray-900 rounded border border-gray-700 flex justify-between items-center">
            <div>
              <p class="text-sm text-indigo-400 font-medium">Follow-up Call</p>
              <p class="text-white font-bold">Mr. Rahim - Engine Oil Change</p>
            </div>
            <button class="bg-gray-700 hover:bg-gray-600 px-3 py-1 rounded text-sm text-white">Execute</button>
          </div>
          <div class="p-4 bg-gray-900 rounded border border-gray-700 flex justify-between items-center">
            <div>
              <p class="text-sm text-yellow-400 font-medium">Stock Alert</p>
              <p class="text-white font-bold">Toyota Brake Pads critically low</p>
            </div>
            <button class="bg-gray-700 hover:bg-gray-600 px-3 py-1 rounded text-sm text-white">Reorder</button>
          </div>
        </div>
      </div>

      <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <h2 class="text-lg font-bold text-white mb-4">Live Insights Feed</h2>
        <div class="space-y-4 relative before:absolute before:inset-y-0 before:left-4 before:w-0.5 before:bg-gray-700">
          <div class="relative pl-10">
            <div class="absolute left-3 top-1 w-2.5 h-2.5 bg-indigo-500 rounded-full ring-4 ring-gray-800"></div>
            <p class="text-sm text-gray-400 mb-1">10 mins ago</p>
            <p class="text-white">AI sent 45 automated service reminders via WhatsApp.</p>
          </div>
          <div class="relative pl-10">
            <div class="absolute left-3 top-1 w-2.5 h-2.5 bg-red-500 rounded-full ring-4 ring-gray-800"></div>
            <p class="text-sm text-gray-400 mb-1">1 hour ago</p>
            <p class="text-white">Anomaly detected: Daily expenses 40% higher than average.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const predictions = ref({});
const anomalies = ref(0);
const recommendations = ref(0);

const fetchDashboard = async () => {
  try {
    const res = await axios.get('/api/v1/ai/dashboard');
    predictions.value = res.data.predictions;
    anomalies.value = res.data.anomalies;
    recommendations.value = res.data.recommendations;
  } catch (error) {
    console.error('Failed to load AI dashboard', error);
  }
};

const runAutomations = async () => {
  try {
    await axios.post('/api/v1/ai/run-automation');
    alert('AI Automations executed successfully!');
  } catch (error) {
    alert('Failed to run AI automations.');
  }
};

onMounted(() => {
  fetchDashboard();
});
</script>
