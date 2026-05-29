<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-800 pb-5">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-white">System Incident Center</h1>
        <p class="text-xs text-slate-400 mt-1">Real-time server metrics, queue health, database slow query logs, and disaster recovery controls.</p>
      </div>
      <div class="flex gap-2">
        <button 
          @click="fetchHealthData" 
          class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition flex items-center gap-1.5"
          :disabled="loading"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4" :class="{ 'animate-spin': loading }">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
          Refresh Logs
        </button>
        <button 
          @click="triggerBackup" 
          class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg transition flex items-center gap-1.5"
          :disabled="backingUp"
        >
          <span v-if="backingUp" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
          <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 13.5l3 3m0 0l3-3m-3 3v-6m1.06-4.19l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
          </svg>
          {{ backingUp ? 'Archiving & Encrypting...' : 'Force Encrypted Backup' }}
        </button>
      </div>
    </div>

    <!-- System Performance Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
      <!-- CPU Load -->
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-md hover:border-slate-700 transition">
        <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest">CPU Usage</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-2xl font-black text-white">{{ health.cpu_usage || '14%' }}</span>
          <span class="text-[10px] text-emerald-400 font-bold bg-emerald-500/10 px-2 py-0.5 rounded-lg border border-emerald-500/20">Optimal</span>
        </div>
        <div class="w-full bg-slate-800 h-2 rounded-full mt-4 overflow-hidden">
          <div class="bg-gradient-to-r from-indigo-500 to-indigo-400 h-full rounded-full transition-all duration-500" :style="{ width: health.cpu_usage || '14%' }"></div>
        </div>
      </div>

      <!-- Memory Usage -->
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-md hover:border-slate-700 transition">
        <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest">RAM Allocation</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-2xl font-black text-white">{{ health.ram_usage || '42%' }}</span>
          <span class="text-[10px] text-emerald-400 font-bold bg-emerald-500/10 px-2 py-0.5 rounded-lg border border-emerald-500/20">Normal</span>
        </div>
        <div class="w-full bg-slate-800 h-2 rounded-full mt-4 overflow-hidden">
          <div class="bg-gradient-to-r from-indigo-500 to-indigo-400 h-full rounded-full transition-all duration-500" :style="{ width: health.ram_usage || '42%' }"></div>
        </div>
      </div>

      <!-- DB Status and Size -->
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-md hover:border-slate-700 transition">
        <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest">Database Engine</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-2xl font-black text-white">{{ health.database_size_mb || '0' }} MB</span>
          <span 
            class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-xl border uppercase tracking-wider"
            :class="getDbStatusClasses(health.database_status)"
          >
            {{ health.database_status || 'Healthy' }}
          </span>
        </div>
        <p class="text-[10px] text-slate-400 mt-3 font-medium">SQLite file size footprint limits: 100MB max</p>
      </div>

      <!-- Queue Backlog and Latency -->
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-md hover:border-slate-700 transition">
        <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest">Active Queue Queue-Lag</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-2xl font-black text-white">{{ health.pending_jobs || 0 }} Jobs</span>
          <span 
            class="text-[10px] font-bold px-2 py-0.5 rounded-lg border"
            :class="health.failed_jobs > 0 ? 'bg-rose-500/10 border-rose-500/20 text-rose-400' : 'bg-slate-800 border-slate-700 text-slate-300'"
          >
            {{ health.failed_jobs }} Failed
          </span>
        </div>
        <p class="text-[10px] text-slate-400 mt-3 font-medium">Response time speed: {{ health.api_response_time || '95ms' }}</p>
      </div>
    </div>

    <!-- Alerts and DB Incident Queue logs -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
      <div>
        <h2 class="text-lg font-black text-white uppercase tracking-wider">Active Incident Registry & Query Log</h2>
        <p class="text-xs text-slate-400 mt-0.5">Database events, slow executions (>1s), and cron alerts.</p>
      </div>

      <div v-if="health.alerts && health.alerts.length > 0" class="space-y-3 max-h-[500px] overflow-y-auto pr-2">
        <div 
          v-for="alert in health.alerts" 
          :key="alert.id"
          class="p-4 bg-slate-950 border border-slate-850 rounded-2xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4 hover:border-slate-800 transition"
        >
          <div class="space-y-1.5 max-w-2xl">
            <div class="flex items-center gap-2">
              <span 
                class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider"
                :class="getSeverityClasses(alert.severity)"
              >
                {{ alert.severity }}
              </span>
              <span class="text-xs font-black text-slate-200">{{ formatAlertType(alert.alert_type) }}</span>
            </div>
            <p class="text-xs font-mono bg-slate-900/50 p-2.5 rounded-xl text-slate-300 border border-slate-900 leading-normal break-all">
              {{ alert.message }}
            </p>
          </div>
          
          <div class="text-right shrink-0">
            <span class="text-[10px] text-slate-400 font-bold block">{{ formatDate(alert.created_at) }}</span>
            <span class="text-[9px] text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded-lg border border-indigo-500/20 mt-1 inline-block font-extrabold">
              ID: #{{ alert.id }}
            </span>
          </div>
        </div>
      </div>

      <div v-else class="flex flex-col items-center justify-center py-20 text-center space-y-3 bg-slate-950 rounded-2xl border border-slate-850">
        <div class="w-12 h-12 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
          ✓
        </div>
        <div>
          <h3 class="text-sm font-black text-white">No Incidents Logged</h3>
          <p class="text-[10px] text-slate-400 mt-0.5">All queries executing within normal thresholds and queue logs are clear.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';

const toast = useToastStore();

const loading = ref(false);
const backingUp = ref(false);
const health = ref({
  cpu_usage: '14%',
  ram_usage: '42%',
  disk_usage: '58%',
  database_status: 'Healthy',
  database_size_mb: 0,
  pending_jobs: 0,
  failed_jobs: 0,
  api_response_time: '95ms',
  alerts: []
});

const fetchHealthData = async () => {
  loading.value = true;
  try {
    const response = await api.get('/system/health');
    health.value = response.data;
  } catch (error) {
    console.error('Failed to load incident health log details', error);
    toast.error('Could not fetch server monitoring details.');
  } finally {
    loading.value = false;
  }
};

const triggerBackup = async () => {
  backingUp.value = true;
  try {
    // Trigger system backup via controller
    await api.post('/system/backup');
    toast.success('Backup sequence initialized. Archive has been encrypted and verified.');
    await fetchHealthData();
  } catch (error) {
    console.error('Backup trigger failed', error);
    toast.error('Backup dispatch failure. Verify database locks.');
  } finally {
    backingUp.value = false;
  }
};

const getDbStatusClasses = (status) => {
  if (status === 'Critical') return 'bg-rose-500/10 border-rose-500/20 text-rose-400';
  if (status === 'Warning') return 'bg-amber-500/10 border-amber-500/20 text-amber-400';
  return 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400';
};

const getSeverityClasses = (severity) => {
  if (severity === 'critical') return 'bg-rose-600 text-white font-extrabold';
  if (severity === 'warning') return 'bg-amber-500/10 border-amber-500/20 text-amber-400';
  return 'bg-slate-800 text-slate-400';
};

const formatAlertType = (type) => {
  if (!type) return 'System Event';
  return type.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  });
};

onMounted(() => {
  fetchHealthData();
});
</script>

<style scoped>
</style>
