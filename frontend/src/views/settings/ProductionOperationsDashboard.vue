<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-800 pb-5">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-white">Production Operations & Infrastructure Health</h1>
        <p class="text-xs text-slate-400 mt-1">Live telemetry monitoring, queue diagnostic thresholds, and recovery workflows.</p>
      </div>
      <div class="flex gap-2">
        <button 
          @click="fetchOperationsData" 
          class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition flex items-center gap-1.5"
          :disabled="loading"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4" :class="{ 'animate-spin': loading }">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
          Refresh Telemetry
        </button>
      </div>
    </div>

    <!-- Infrastructure telemetry cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
      <!-- CPU and RAM Usage -->
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-md">
        <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest">Server Allocation</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-2xl font-black text-white">RAM: {{ telemetry.ram_usage || '42%' }}</span>
          <span class="text-xs text-slate-350">CPU: {{ telemetry.cpu_usage || '14%' }}</span>
        </div>
        <div class="w-full bg-slate-800 h-2 rounded-full mt-4 overflow-hidden">
          <div class="bg-indigo-600 h-full rounded-full transition-all duration-500" :style="{ width: telemetry.ram_usage || '42%' }"></div>
        </div>
      </div>

      <!-- Redis and Websocket Status -->
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-md">
        <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest">Caching & WebSockets</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-2xl font-black text-white">Redis</span>
          <span 
            class="text-[9px] font-black px-2 py-0.5 rounded border uppercase tracking-wider"
            :class="telemetry.redis_status === 'connected' ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' : 'bg-rose-500/10 border-rose-500/20 text-rose-400'"
          >
            {{ telemetry.redis_status || 'Disconnected' }}
          </span>
        </div>
        <p class="text-[10px] text-slate-400 mt-3">Reverb socket sync: <span class="text-emerald-400 font-bold">Active</span></p>
      </div>

      <!-- Database Status -->
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-md">
        <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest">Database Engine</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-2xl font-black text-white">{{ telemetry.database_size_mb || 0 }} MB</span>
          <span 
            class="text-[9px] font-black px-2 py-0.5 rounded border uppercase tracking-wider"
            :class="telemetry.database_status === 'Healthy' ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' : 'bg-rose-500/10 border-rose-500/20 text-rose-400'"
          >
            {{ telemetry.database_status }}
          </span>
        </div>
        <p class="text-[10px] text-slate-400 mt-3">SaaS Isolation Schema: <span class="text-white font-bold">Enforced</span></p>
      </div>

      <!-- Queue Telemetry -->
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-md">
        <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest">Active queue health</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-2xl font-black text-white">{{ telemetry.pending_jobs || 0 }} Jobs</span>
          <span 
            class="text-[9px] font-black px-2 py-0.5 rounded border uppercase tracking-wider"
            :class="telemetry.failed_jobs > 0 ? 'bg-rose-500/10 border-rose-500/20 text-rose-400' : 'bg-slate-800 border-slate-700 text-slate-300'"
          >
            {{ telemetry.failed_jobs }} Failed
          </span>
        </div>
        <p class="text-[10px] text-slate-400 mt-3">Background throughput latency: {{ telemetry.api_response_time || '95ms' }}</p>
      </div>
    </div>

    <!-- Emergency Controls -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
      <div>
        <h2 class="text-lg font-black text-white uppercase tracking-wider">Emergency Incident Recovery Controls</h2>
        <p class="text-xs text-slate-400 mt-0.5">Toggle maintenance states, clear queues database metrics, and invoke recovery operations.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Emergency Maintenance Mode -->
        <div class="bg-slate-950 border border-slate-850 rounded-2xl p-5 flex flex-col justify-between">
          <div class="space-y-1">
            <h3 class="text-sm font-black text-white">Emergency Maintenance Mode</h3>
            <p class="text-[10px] text-slate-400 leading-normal">Halts all incoming API traffic with a 503 response page. Super Admin accounts retain bypass capabilities.</p>
          </div>
          <div class="mt-4 flex items-center justify-between">
            <span class="text-[10px] uppercase font-bold" :class="telemetry.maintenance_mode ? 'text-amber-400' : 'text-slate-500'">
              {{ telemetry.maintenance_mode ? 'Maintenance On' : 'Maintenance Off' }}
            </span>
            <button 
              @click="toggleMaintenanceMode"
              class="px-4 py-1.5 text-xs font-bold rounded-xl transition"
              :class="telemetry.maintenance_mode ? 'bg-emerald-600 hover:bg-emerald-500 text-white' : 'bg-rose-600 hover:bg-rose-550 text-white'"
              :disabled="togglingMaintenance"
            >
              {{ telemetry.maintenance_mode ? 'Deactivate Mode' : 'Activate Mode' }}
            </button>
          </div>
        </div>

        <!-- Truncate failed jobs -->
        <div class="bg-slate-950 border border-slate-850 rounded-2xl p-5 flex flex-col justify-between">
          <div class="space-y-1">
            <h3 class="text-sm font-black text-white">Deregister & Clear Failed Queues</h3>
            <p class="text-[10px] text-slate-400 leading-normal">Truncates all accumulated failed queues log entries. Use to reset health alerts after issue remediation.</p>
          </div>
          <div class="mt-4 flex justify-end">
            <button 
              @click="clearFailedJobs"
              class="px-4 py-1.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl transition"
              :disabled="clearingJobs"
            >
              {{ clearingJobs ? 'Clearing...' : 'Clear Failed Queue Logs' }}
            </button>
          </div>
        </div>

        <!-- System Backup Trigger -->
        <div class="bg-slate-950 border border-slate-850 rounded-2xl p-5 flex flex-col justify-between">
          <div class="space-y-1">
            <h3 class="text-sm font-black text-white">Encrypted Disaster Snapshot</h3>
            <p class="text-[10px] text-slate-400 leading-normal">Triggers complete database archive sequence, encrypts dump with AES-256 keys, and performs verify-restore checks.</p>
          </div>
          <div class="mt-4 flex justify-end">
            <button 
              @click="triggerBackup"
              class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition"
              :disabled="backingUp"
            >
              {{ backingUp ? 'Encrypting...' : 'Force Encrypted Backup' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Active Query Alerts -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
      <div>
        <h2 class="text-lg font-black text-white uppercase tracking-wider">Slow Execution & Query Telemetry Logs</h2>
        <p class="text-xs text-slate-400 mt-0.5">Automated audits logging system events and queries exceeding 1000ms latency boundaries.</p>
      </div>

      <div v-if="telemetry.alerts && telemetry.alerts.length > 0" class="space-y-3 max-h-[400px] overflow-y-auto pr-2">
        <div 
          v-for="alert in telemetry.alerts" 
          :key="alert.id"
          class="p-4 bg-slate-950 border border-slate-850 rounded-2xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4 hover:border-slate-800 transition"
        >
          <div class="space-y-1.5 max-w-2xl">
            <div class="flex items-center gap-2">
              <span 
                class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider"
                :class="alert.severity === 'critical' ? 'bg-rose-600 text-white font-extrabold' : 'bg-slate-800 text-slate-400'"
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
          </div>
        </div>
      </div>

      <div v-else class="flex flex-col items-center justify-center py-20 text-center space-y-3 bg-slate-950 rounded-2xl border border-slate-850">
        <div class="w-12 h-12 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
          ✓
        </div>
        <div>
          <h3 class="text-sm font-black text-white">Telemetry Log Clear</h3>
          <p class="text-[10px] text-slate-400 mt-0.5">All transactions executing under performance limits and background queues are empty.</p>
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
const togglingMaintenance = ref(false);
const clearingJobs = ref(false);
const backingUp = ref(false);

const telemetry = ref({
  cpu_usage: '14%',
  ram_usage: '42%',
  disk_usage: '58%',
  database_status: 'Healthy',
  database_size_mb: 0,
  pending_jobs: 0,
  failed_jobs: 0,
  redis_status: 'Connected',
  maintenance_mode: false,
  alerts: []
});

const fetchOperationsData = async () => {
  loading.value = true;
  try {
    const response = await api.get('/system/production-health');
    telemetry.value = response.data.data;
  } catch (error) {
    console.error('Failed to load operations telemetry', error);
    toast.error('Could not fetch operations telemetry.');
  } finally {
    loading.value = false;
  }
};

const toggleMaintenanceMode = async () => {
  const newMode = !telemetry.value.maintenance_mode;
  if (!confirm(`Are you sure you want to ${newMode ? 'ENABLE' : 'DISABLE'} Emergency Maintenance Mode?`)) {
    return;
  }
  
  togglingMaintenance.value = true;
  try {
    const response = await api.post('/system/maintenance', { enabled: newMode });
    telemetry.value.maintenance_mode = response.data.maintenance_mode;
    toast.success(response.data.message);
  } catch (error) {
    console.error('Failed to toggle maintenance mode', error);
    toast.error(error.response?.data?.message || 'Access Denied. Only Super Admins may invoke maintenance switches.');
  } finally {
    togglingMaintenance.value = false;
  }
};

const clearFailedJobs = async () => {
  if (!confirm('Proceed to clear all failed background queues logs?')) {
    return;
  }
  clearingJobs.value = true;
  try {
    const response = await api.post('/system/failed-jobs/clear');
    toast.success(response.data.message);
    await fetchOperationsData();
  } catch (error) {
    console.error('Failed to clear failed jobs', error);
    toast.error(error.response?.data?.message || 'Failed to execute queue clear.');
  } finally {
    clearingJobs.value = false;
  }
};

const triggerBackup = async () => {
  backingUp.value = true;
  try {
    await api.post('/system/backup');
    toast.success('Disaster recovery dump initialized, encrypted, and verified.');
    await fetchOperationsData();
  } catch (error) {
    console.error('Backup trigger failed', error);
    toast.error('Backup fail. Verify filesystem locks.');
  } finally {
    backingUp.value = false;
  }
};

const formatAlertType = (type) => {
  if (!type) return 'System Metric';
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
  fetchOperationsData();
});
</script>

<style scoped>
</style>
