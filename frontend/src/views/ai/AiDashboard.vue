<template>
  <div class="space-y-6">
    <div class="flex justify-between items-end">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
          <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
          AI Automation & Live Operations
        </h1>
        <p class="text-sm text-slate-500 mt-1">Intelligent monitoring, workflow engines, and predictive alerts.</p>
      </div>
      <button @click="aiStore.triggerAutomation('manual_sync')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 shadow-sm flex items-center gap-2">
        Run AI Diagnostics
      </button>
    </div>

    <div v-if="aiStore.loading" class="flex justify-center p-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
    </div>
    
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- AI Insights & Predictive Alerts -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-200 bg-slate-50">
          <h2 class="text-base font-semibold text-slate-900">Predictive Insights & Alerts</h2>
        </div>
        <div class="divide-y divide-slate-100">
          <div v-for="insight in aiStore.insights" :key="insight.id" class="p-4 hover:bg-slate-50 transition-colors">
            <div class="flex items-start gap-3">
              <div class="mt-0.5 rounded-full p-1.5" :class="{
                'bg-rose-100 text-rose-600': insight.severity === 'critical',
                'bg-amber-100 text-amber-600': insight.severity === 'warning',
                'bg-blue-100 text-blue-600': insight.severity === 'info'
              }">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div>
                <h3 class="text-sm font-semibold text-slate-900">{{ insight.title }}</h3>
                <p class="text-sm text-slate-600 mt-1">{{ insight.description }}</p>
                <div class="text-xs text-slate-400 mt-2">{{ new Date(insight.created_at).toLocaleString() }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Live Workshop Board -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
          <h2 class="text-base font-semibold text-slate-900 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Live Workshop Board
          </h2>
        </div>
        <div class="p-4 space-y-4">
          <div v-for="act in aiStore.workshopActivities" :key="act.id" class="bg-slate-50 rounded-lg p-4 border border-slate-100">
            <div class="flex justify-between items-center mb-2">
              <span class="font-medium text-slate-900 text-sm">{{ act.mechanic }}</span>
              <span class="text-xs px-2 py-1 rounded-full font-medium" :class="{
                'bg-blue-100 text-blue-800': act.status === 'Working',
                'bg-slate-200 text-slate-600': act.status === 'Idle',
                'bg-purple-100 text-purple-800': act.status === 'Testing'
              }">{{ act.status }}</span>
            </div>
            <p class="text-sm text-slate-600 mb-3">{{ act.task }}</p>
            <div class="w-full bg-slate-200 rounded-full h-1.5">
              <div class="bg-indigo-600 h-1.5 rounded-full transition-all duration-500" :style="{ width: act.progress + '%' }"></div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Automations Logic Engine -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden lg:col-span-2">
        <div class="p-4 border-b border-slate-200 bg-slate-50">
          <h2 class="text-base font-semibold text-slate-900">Active Workflows & Automations</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-white">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase">Workflow Name</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase">Trigger Event</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              <tr v-for="rule in aiStore.automations" :key="rule.id">
                <td class="px-6 py-4 whitespace-nowrap font-medium text-slate-900 text-sm">{{ rule.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 font-mono text-xs">{{ rule.event_trigger }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="rule.is_active" class="text-emerald-600 flex items-center gap-1.5 text-sm font-medium">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> Active
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAiStore } from '../../stores/ai';

const aiStore = useAiStore();

onMounted(() => {
  aiStore.fetchDashboardData();
});
</script>

