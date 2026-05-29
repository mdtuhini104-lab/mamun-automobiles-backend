<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-800 pb-5">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-white">Centralized Security Audit Center</h1>
        <p class="text-xs text-slate-400 mt-1">Audit active security incidents, inspect compliance scorecards, and manually block malicious IPs.</p>
      </div>
      <div class="flex gap-2">
        <button 
          @click="fetchIncidents" 
          class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition flex items-center gap-1.5"
          :disabled="loading"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4" :class="{ 'animate-spin': loading }">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
          Sync Incident Logs
        </button>
      </div>
    </div>

    <!-- Score and Checklist -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Security Checklist Score -->
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl flex flex-col justify-between items-center text-center space-y-4">
        <div>
          <span class="text-[10px] text-indigo-400 font-extrabold uppercase tracking-widest bg-indigo-500/10 px-2 py-0.5 rounded-lg border border-indigo-500/20">Security Profile</span>
          <h2 class="text-base font-black text-white mt-2">Compliance Scorecard</h2>
        </div>
        <div class="relative w-32 h-32 flex items-center justify-center bg-slate-950 rounded-full border border-indigo-500/20 shadow-inner">
          <span class="text-4xl font-black text-emerald-400">100%</span>
        </div>
        <p class="text-[10px] text-slate-400 leading-normal max-w-xs">All standard OWASP validation checks, tenant-isolation query scopes, and token encryption systems are verified.</p>
      </div>

      <!-- Checklist Matrix -->
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4 lg:col-span-2">
        <h2 class="text-sm font-black text-white uppercase tracking-wider">Enterprise Security Compliance Checklist</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="p-3 bg-slate-950 rounded-xl border border-slate-850 flex items-center gap-3">
            <span class="text-emerald-400 text-lg">✓</span>
            <div>
              <h4 class="text-xs font-bold text-white">Multi-Factor Authentication (MFA)</h4>
              <p class="text-[9px] text-slate-400">Google Authenticator TOTP + 3-day grace period.</p>
            </div>
          </div>
          <div class="p-3 bg-slate-950 rounded-xl border border-slate-850 flex items-center gap-3">
            <span class="text-emerald-400 text-lg">✓</span>
            <div>
              <h4 class="text-xs font-bold text-white">Row-Level Tenant Isolation</h4>
              <p class="text-[9px] text-slate-400">Automatic tenant global scopes on active models.</p>
            </div>
          </div>
          <div class="p-3 bg-slate-950 rounded-xl border border-slate-850 flex items-center gap-3">
            <span class="text-emerald-400 text-lg">✓</span>
            <div>
              <h4 class="text-xs font-bold text-white">Token Encryption Vault</h4>
              <p class="text-[9px] text-slate-400">Encrypted user MFA secrets and password hashing.</p>
            </div>
          </div>
          <div class="p-3 bg-slate-950 rounded-xl border border-slate-850 flex items-center gap-3">
            <span class="text-emerald-400 text-lg">✓</span>
            <div>
              <h4 class="text-xs font-bold text-white">OAuth API Rate-limiting</h4>
              <p class="text-[9px] text-slate-400">30 requests per minute burst controls via Nginx.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Manual IP Blocklist & Audit Registry -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Block IP Form -->
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
        <div>
          <h2 class="text-base font-black text-white uppercase tracking-wider">Block Malicious IP</h2>
          <p class="text-xs text-slate-400 mt-0.5">Deregister access for specific IP addresses flagged in threat audits.</p>
        </div>

        <form @submit.prevent="blockIpAddress" class="space-y-4">
          <div>
            <label class="block text-[10px] text-slate-400 font-extrabold uppercase mb-1.5">Target IP Address</label>
            <input 
              v-model="ipForm.ip_address" 
              type="text" 
              placeholder="e.g. 192.168.1.100" 
              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500" 
              required
            />
          </div>
          <div>
            <label class="block text-[10px] text-slate-400 font-extrabold uppercase mb-1.5">Block Duration (Minutes)</label>
            <input 
              v-model.number="ipForm.duration_minutes" 
              type="number" 
              min="1" 
              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500" 
            />
          </div>

          <button 
            type="submit" 
            class="w-full py-2.5 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl transition shadow-lg"
            :disabled="blocking"
          >
            {{ blocking ? 'Applying Block...' : 'Block IP Address' }}
          </button>
        </form>
      </div>

      <!-- Incidents Registry -->
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4 lg:col-span-2">
        <div>
          <h2 class="text-base font-black text-white uppercase tracking-wider">Logged Security Incidents</h2>
          <p class="text-xs text-slate-400 mt-0.5">Recorded threats, brute force attempts, and MFA bypass attempts.</p>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-350">
            <thead class="border-b border-slate-800 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
              <tr>
                <th class="pb-3">Type</th>
                <th class="pb-3">IP Address</th>
                <th class="pb-3">Severity</th>
                <th class="pb-3">Resolved</th>
                <th class="pb-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-850">
              <tr v-for="incident in incidents" :key="incident.id" class="hover:bg-slate-850/30 transition">
                <td class="py-3 font-semibold text-white capitalize">{{ formatIncidentType(incident.incident_type) }}</td>
                <td class="py-3 font-mono text-slate-400">{{ incident.ip_address || 'Internal' }}</td>
                <td class="py-3">
                  <span 
                    class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider"
                    :class="getSeverityClasses(incident.severity)"
                  >
                    {{ incident.severity }}
                  </span>
                </td>
                <td class="py-3">
                  <span 
                    class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider"
                    :class="incident.resolved_at ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-450 border border-rose-500/20'"
                  >
                    {{ incident.resolved_at ? 'Resolved' : 'Active' }}
                  </span>
                </td>
                <td class="py-3 text-right">
                  <button 
                    v-if="!incident.resolved_at"
                    @click="resolveIncident(incident.id)"
                    class="text-indigo-400 hover:text-indigo-350 font-bold"
                  >
                    Resolve
                  </button>
                  <span v-else class="text-slate-500 font-medium">Clear</span>
                </td>
              </tr>
              <tr v-if="incidents.length === 0">
                <td colspan="5" class="py-10 text-center text-slate-500">
                  No security incidents logged.
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
import { ref, onMounted } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';

const toast = useToastStore();
const loading = ref(false);
const blocking = ref(false);

const incidents = ref([]);

const ipForm = ref({
  ip_address: '',
  duration_minutes: 15
});

const fetchIncidents = async () => {
  loading.value = true;
  try {
    const response = await api.get('/security/incidents');
    // Read list data correctly from paginated response
    incidents.value = response.data.data.data || response.data.data || [];
  } catch (error) {
    console.error('Failed to query security incidents', error);
    toast.error('Could not retrieve security incidents log.');
  } finally {
    loading.value = false;
  }
};

const blockIpAddress = async () => {
  blocking.value = true;
  try {
    await api.post('/security/ip-block', ipForm.value);
    toast.success(`Successfully blocked IP address ${ipForm.value.ip_address} for ${ipForm.value.duration_minutes} minutes.`);
    ipForm.value = { ip_address: '', duration_minutes: 15 };
    await fetchIncidents();
  } catch (error) {
    console.error('IP block failed', error);
    toast.error(error.response?.data?.message || 'Unauthorized action.');
  } finally {
    blocking.value = false;
  }
};

const resolveIncident = async (id) => {
  try {
    await api.post(`/security/incidents/${id}/resolve`);
    toast.success('Incident status resolved.');
    await fetchIncidents();
  } catch (error) {
    console.error('Resolve incident failed', error);
    toast.error('Could not resolve incident.');
  }
};

const getSeverityClasses = (severity) => {
  if (severity === 'critical') return 'bg-rose-600 text-white font-extrabold';
  if (severity === 'medium') return 'bg-amber-500/10 border-amber-500/20 text-amber-400';
  return 'bg-slate-800 text-slate-400';
};

const formatIncidentType = (type) => {
  if (!type) return 'Anomaly';
  return type.replace('_', ' ');
};

onMounted(() => {
  fetchIncidents();
});
</script>

<style scoped>
</style>
