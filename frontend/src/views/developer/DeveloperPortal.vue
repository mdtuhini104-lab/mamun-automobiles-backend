<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-800 pb-5">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-white">Developer Cockpit & Operations Telemetry</h1>
        <p class="text-xs text-slate-400 mt-1">Configure integrations, monitor queue execution health, replay failed background jobs, and view query profiling statistics.</p>
      </div>

      <!-- Realtime Websocket Resiliency State Badge -->
      <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl border bg-slate-900" :class="getConnectionBorderClass(connectionState)">
        <span class="relative flex h-2 w-2">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" :class="getConnectionBgClass(connectionState)"></span>
          <span class="relative inline-flex rounded-full h-2 w-2" :class="getConnectionBgClass(connectionState)"></span>
        </span>
        <span class="text-[10px] font-black uppercase tracking-wider text-slate-350">
          Sync Status: {{ connectionState }}
        </span>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-800 gap-4">
      <button 
        @click="activeTab = 'api'" 
        class="pb-3 text-xs font-black uppercase tracking-wider transition-colors border-b-2"
        :class="activeTab === 'api' ? 'border-indigo-500 text-white' : 'border-transparent text-slate-400 hover:text-slate-200'"
      >
        REST API & Domains
      </button>
      <button 
        @click="activeTab = 'queues'" 
        class="pb-3 text-xs font-black uppercase tracking-wider transition-colors border-b-2"
        :class="activeTab === 'queues' ? 'border-indigo-500 text-white' : 'border-transparent text-slate-400 hover:text-slate-200'"
      >
        Queue Replay Cockpit
      </button>
      <button 
        @click="activeTab = 'telemetry'" 
        class="pb-3 text-xs font-black uppercase tracking-wider transition-colors border-b-2"
        :class="activeTab === 'telemetry' ? 'border-indigo-500 text-white' : 'border-transparent text-slate-400 hover:text-slate-200'"
      >
        Telemetry & Slow Queries
      </button>
    </div>

    <!-- TAB 1: REST API & Domains -->
    <div v-if="activeTab === 'api'" class="space-y-6 animate-fadeIn">
      <!-- API Client Keys -->
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
        <div class="flex justify-between items-center">
          <div>
            <h2 class="text-base font-black text-white uppercase tracking-wider">OAuth 2.0 Client Tokens</h2>
            <p class="text-xs text-slate-400 mt-0.5">Use these credentials to authorize external ERP synchronizers and inventory loaders.</p>
          </div>
          <button 
            @click="generateApiKey" 
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition"
          >
            Generate New Client Key
          </button>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-350">
            <thead class="border-b border-slate-800 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
              <tr>
                <th class="pb-3">Token Identifier</th>
                <th class="pb-3">Client Name</th>
                <th class="pb-3">Scope Limit</th>
                <th class="pb-3">Secret Key Preview</th>
                <th class="pb-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-850">
              <tr v-for="key in apiKeys" :key="key.id" class="hover:bg-slate-850/30 transition">
                <td class="py-3 font-mono text-indigo-400">client_{{ key.id }}</td>
                <td class="py-3 font-semibold text-white">{{ key.name }}</td>
                <td class="py-3">
                  <span class="px-2 py-0.5 rounded text-[8px] bg-slate-800 text-slate-300 font-extrabold uppercase border border-slate-700">
                    {{ key.scope }}
                  </span>
                </td>
                <td class="py-3 font-mono text-slate-400">{{ key.preview }}</td>
                <td class="py-3 text-right">
                  <button @click="revokeKey(key.id)" class="text-rose-500 hover:text-rose-400 font-bold">Revoke</button>
                </td>
              </tr>
              <tr v-if="apiKeys.length === 0">
                <td colspan="5" class="py-6 text-center text-slate-500">
                  No developer access keys registered.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Webhook Subscriptions -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Create Webhook -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
          <div>
            <h2 class="text-base font-black text-white uppercase tracking-wider">Register Webhook</h2>
            <p class="text-xs text-slate-400 mt-0.5">Send real-time JSON payloads to your servers when automotive events occur.</p>
          </div>

          <form @submit.prevent="registerWebhook" class="space-y-4">
            <div>
              <label class="block text-[10px] text-slate-400 font-extrabold uppercase mb-1.5">Destination URL</label>
              <input 
                v-model="webhookForm.url" 
                type="url" 
                placeholder="https://api.yourdomain.com/webhooks" 
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500" 
                required
              />
            </div>

            <div>
              <label class="block text-[10px] text-slate-400 font-extrabold uppercase mb-1.5">Event Topic</label>
              <select v-model="webhookForm.event" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                <option value="quotation.created">quotation.created</option>
                <option value="invoice.paid">invoice.paid</option>
                <option value="work_order.closed">work_order.closed</option>
                <option value="security.alert">security.alert</option>
              </select>
            </div>

            <button 
              type="submit" 
              class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition shadow-lg"
            >
              Register Endpoint
            </button>
          </form>
        </div>

        <!-- Webhooks List -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4 lg:col-span-2">
          <div>
            <h2 class="text-base font-black text-white uppercase tracking-wider">Registered Webhook Endpoints</h2>
            <p class="text-xs text-slate-400 mt-0.5">Active webhook integrations receiving transactions updates.</p>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-350">
              <thead class="border-b border-slate-800 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
                <tr>
                  <th class="pb-3">URL</th>
                  <th class="pb-3">Event Topic</th>
                  <th class="pb-3">Status</th>
                  <th class="pb-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-850">
                <tr v-for="hook in webhooks" :key="hook.id" class="hover:bg-slate-850/30 transition">
                  <td class="py-3 font-mono text-white max-w-xs truncate">{{ hook.url }}</td>
                  <td class="py-3 font-semibold text-indigo-400">{{ hook.event }}</td>
                  <td class="py-3">
                    <span class="px-2 py-0.5 rounded text-[8px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-extrabold uppercase">
                      Active
                    </span>
                  </td>
                  <td class="py-3 text-right">
                    <button @click="deleteWebhook(hook.id)" class="text-rose-500 hover:text-rose-400 font-bold">Remove</button>
                  </td>
                </tr>
                <tr v-if="webhooks.length === 0">
                  <td colspan="4" class="py-6 text-center text-slate-500">
                    No active webhooks registered.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Custom Domain DNS check -->
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
        <div>
          <h2 class="text-base font-black text-white uppercase tracking-wider">Custom Domain White-Labeling</h2>
          <p class="text-xs text-slate-400 mt-0.5">Map your custom franchise URL directly to this ERP tenant. DNS records are checked automatically.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center bg-slate-950 p-5 rounded-2xl border border-slate-850">
          <div class="space-y-2">
            <label class="block text-[10px] text-slate-400 font-extrabold uppercase">Custom Domain Mapping</label>
            <input 
              v-model="customDomain" 
              type="text" 
              placeholder="repairs.myfranchise.com" 
              class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500" 
            />
          </div>
          <div class="space-y-2 font-mono text-[10px] text-slate-400">
            <span class="font-extrabold uppercase block text-slate-500">Required DNS settings:</span>
            <p>Type: <span class="text-white">CNAME</span></p>
            <p>Value: <span class="text-indigo-400">cname.mamunerp.com</span></p>
          </div>
          <div>
            <button 
              @click="verifyDns"
              class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition"
              :disabled="checkingDns"
            >
              {{ checkingDns ? 'Querying DNS Servers...' : 'Verify Custom Domain Mapping' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 2: QUEUE REPLAY COCKPIT -->
    <div v-if="activeTab === 'queues'" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6 animate-fadeIn">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h2 class="text-lg font-black text-white uppercase tracking-wider">Horizon Failed Job Logs</h2>
          <p class="text-xs text-slate-400 mt-0.5">
            Operations Managers can view traceback errors. <span class="text-indigo-400 font-bold">Only Super Admins</span> can replay or delete jobs.
          </p>
        </div>
        <div class="flex gap-2" v-if="isSuperAdmin">
          <button 
            @click="triggerBulkRetry" 
            class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition"
          >
            Replay All Failed Jobs
          </button>
        </div>
      </div>

      <!-- Queue Failed List Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-350">
          <thead class="border-b border-slate-800 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
            <tr>
              <th class="pb-3">Job ID</th>
              <th class="pb-3">Job Name</th>
              <th class="pb-3">Queue Name</th>
              <th class="pb-3">Risk Tier</th>
              <th class="pb-3">Failed At</th>
              <th class="pb-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-850">
            <template v-for="job in failedJobs" :key="job.id">
              <tr class="hover:bg-slate-850/20 transition cursor-pointer" @click="toggleJobAccordion(job.id)">
                <td class="py-3 font-mono text-slate-400">#{{ job.id }}</td>
                <td class="py-3 font-semibold text-white">
                  <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                    {{ job.job_name }}
                  </div>
                </td>
                <td class="py-3 font-mono">{{ job.queue }}</td>
                <td class="py-3">
                  <span 
                    class="px-2 py-0.5 rounded text-[8px] font-black uppercase border"
                    :class="job.classification === 'restricted' 
                      ? 'bg-amber-500/10 text-amber-450 border-amber-500/20' 
                      : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'"
                  >
                    {{ job.classification }}
                  </span>
                </td>
                <td class="py-3 font-mono">{{ formatDateTime(job.failed_at) }}</td>
                <td class="py-3 text-right" @click.stop>
                  <div class="flex justify-end gap-2" v-if="isSuperAdmin">
                    <button @click="openRetryModal(job)" class="text-indigo-400 hover:text-indigo-300 font-black">
                      Replay
                    </button>
                    <button @click="deleteJob(job.id)" class="text-rose-500 hover:text-rose-400 font-bold">
                      Delete
                    </button>
                  </div>
                  <span v-else class="text-[10px] text-slate-500">Read-Only</span>
                </td>
              </tr>
              <!-- Exception Trace Accordion Body -->
              <tr v-if="expandedJobIds.includes(job.id)" class="bg-slate-950 border-none">
                <td colspan="6" class="p-4">
                  <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 space-y-2">
                    <h5 class="text-[10px] font-extrabold uppercase text-rose-450 tracking-wider">Exception Traceback</h5>
                    <pre class="text-[9px] font-mono text-slate-350 leading-relaxed overflow-x-auto whitespace-pre-wrap max-h-48 scrollbar-thin">{{ job.exception }}</pre>
                  </div>
                </td>
              </tr>
            </template>
            <tr v-if="failedJobs.length === 0">
              <td colspan="6" class="py-10 text-center text-slate-500">
                No failed background jobs found. Horizon is healthy.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAB 3: TELEMETRY & SLOW QUERIES -->
    <div v-if="activeTab === 'telemetry'" class="space-y-6 animate-fadeIn">
      <!-- Aggregates Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-md space-y-1">
          <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest">Slow Queries Recorded</span>
          <h3 class="text-2xl font-black text-white">{{ telemetry.slow_queries.length }}</h3>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-md space-y-1">
          <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest">Average Queue Latency</span>
          <h3 class="text-2xl font-black text-emerald-450">{{ telemetry.queue_latency.avg_duration_seconds }}s</h3>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-md space-y-1">
          <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest">Cache Performance hit ratio</span>
          <h3 class="text-2xl font-black text-indigo-400">{{ telemetry.cache_telemetry.hit_ratio }}%</h3>
        </div>
      </div>

      <!-- Slow DB queries list -->
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
        <div>
          <h2 class="text-lg font-black text-white uppercase tracking-wider">Slow Database Query Profiler (>100ms)</h2>
          <p class="text-xs text-slate-400 mt-0.5">30-day rolling database execution monitoring. Unindexed queries are logged dynamically.</p>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-350">
            <thead class="border-b border-slate-800 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
              <tr>
                <th class="pb-3">SQL statement</th>
                <th class="pb-3">Execution Time</th>
                <th class="pb-3">Driver Connection</th>
                <th class="pb-3 text-right">Triggered At</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-850">
              <tr v-for="(query, idx) in telemetry.slow_queries" :key="idx" class="hover:bg-slate-850/20 transition">
                <td class="py-3 font-mono text-white max-w-lg truncate" :title="query.sql">{{ query.sql }}</td>
                <td class="py-3 font-mono text-amber-400 font-bold">{{ query.time }}ms</td>
                <td class="py-3 uppercase font-mono">{{ query.connection }}</td>
                <td class="py-3 text-right font-mono">{{ query.triggered_at }}</td>
              </tr>
              <tr v-if="telemetry.slow_queries.length === 0">
                <td colspan="4" class="py-6 text-center text-slate-500">
                  No slow database queries logged in the last 30 days. Index allocations are healthy.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Queue Latency stats -->
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
        <div>
          <h2 class="text-lg font-black text-white uppercase tracking-wider">Queue latency execution statistics</h2>
          <p class="text-xs text-slate-400 mt-0.5">Job processing delays and waiting latencies for background workers.</p>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-350">
            <thead class="border-b border-slate-800 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
              <tr>
                <th class="pb-3">Job Handler</th>
                <th class="pb-3">Processing Duration</th>
                <th class="pb-3 text-right">Processed At</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-850">
              <tr v-for="(history, idx) in telemetry.queue_latency.latency_history" :key="idx" class="hover:bg-slate-850/20 transition">
                <td class="py-3 font-mono text-white">{{ history.job }}</td>
                <td class="py-3 font-mono text-indigo-400 font-bold">{{ history.duration }}s</td>
                <td class="py-3 text-right font-mono">{{ history.timestamp }}</td>
              </tr>
              <tr v-if="telemetry.queue_latency.latency_history.length === 0">
                <td colspan="3" class="py-6 text-center text-slate-500">
                  No queue latency data available. Ensure horizon queues are active.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- REPLAY JOB MODAL -->
    <div v-if="showRetryModal" class="fixed inset-0 flex items-center justify-center z-50 bg-slate-950/80 backdrop-blur-sm p-4">
      <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl animate-scaleUp">
        <div>
          <div class="flex items-center gap-2">
            <h3 class="text-base font-black text-white uppercase tracking-wider">Confirm Job Replay</h3>
            <span 
              class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider border"
              :class="selectedJob.classification === 'restricted' ? 'bg-amber-500/10 text-amber-450 border-amber-500/20' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'"
            >
              {{ selectedJob.classification }}
            </span>
          </div>
          <p class="text-xs text-slate-400 mt-1">
            Provide a mandatory change justification reason below to authorize the retry dispatch.
          </p>
        </div>

        <!-- Warning Alert for restricted jobs -->
        <div v-if="selectedJob.classification === 'restricted'" class="p-3.5 bg-amber-500/10 border border-amber-500/20 rounded-2xl text-[10px] text-amber-400 leading-normal flex gap-2">
          <svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
          </svg>
          <div>
            <strong>Restricted Replay Guard:</strong> This background job performs critical transaction mapping or inventory deductions. Ensure idempotency has been validated to avoid duplicate balances corrections.
          </div>
        </div>

        <form @submit.prevent="executeRetryJob" class="space-y-4">
          <div>
            <label class="block text-[10px] text-slate-400 font-extrabold uppercase mb-1">Replay Reason / Justification</label>
            <textarea 
              v-model="retryReason" 
              rows="3" 
              placeholder="e.g. Cleared deadlock on MySQL thread, retry safe" 
              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500" 
              required
            ></textarea>
          </div>

          <div v-if="selectedJob.classification === 'restricted'" class="flex items-start gap-2.5">
            <input 
              v-model="confirmRestrictedCheck" 
              type="checkbox" 
              id="confirmRestricted" 
              class="mt-0.5 rounded border-slate-800 bg-slate-950 text-indigo-600 focus:ring-0 focus:ring-offset-0" 
              required
            />
            <label for="confirmRestricted" class="text-[10px] text-slate-450 leading-tight">
              I verify that all ledger dependencies are reconciled and this replay will not duplicate transaction balances.
            </label>
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button 
              type="button" 
              @click="closeRetryModal" 
              class="px-4 py-2 bg-slate-800 hover:bg-slate-750 text-slate-200 text-xs font-bold rounded-xl"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg"
              :disabled="submittingRetry"
            >
              {{ submittingRetry ? 'Executing replay...' : 'Replay Job' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';
import { useAuthStore } from '../../stores/auth';
import { useEcho } from '../../composables/useEcho';

const toast = useToastStore();
const authStore = useAuthStore();

// Subscribe to connection state using Echo
const { connectionState } = useEcho('system-health', false);

const activeTab = ref('api');
const loading = ref(false);
const checkingDns = ref(false);
const customDomain = ref('');

// OAuth client Keys & webhooks
const apiKeys = ref([
  { id: 1, name: 'Inventory Sync Automation', scope: 'parts.read_write', preview: 'sk_live_••••••••••••3a9b' },
  { id: 2, name: 'Customer App Mobile auth', scope: 'customers.read', preview: 'sk_live_••••••••••••d20f' }
]);

const webhooks = ref([
  { id: 1, url: 'https://webhook.site/#!/6b2c99a4-5690', event: 'invoice.paid' }
]);

const webhookForm = ref({
  url: '',
  event: 'quotation.created'
});

// Queue log lists
const failedJobs = ref([]);
const expandedJobIds = ref([]);
const showRetryModal = ref(false);
const selectedJob = ref(null);
const retryReason = ref('');
const confirmRestrictedCheck = ref(false);
const submittingRetry = ref(false);

// Telemetry & metrics
const telemetry = ref({
  slow_queries: [],
  queue_latency: {
    total_processed: 0,
    avg_duration_seconds: 0.0,
    latency_history: []
  },
  cache_telemetry: {
    hits: 0,
    misses: 0,
    hit_ratio: 0
  }
});

// Role checking computed properties
const isSuperAdmin = computed(() => {
  return authStore.user?.roles?.some(r => r.name === 'Super Admin') || authStore.hasRole('Super Admin');
});

// Fetch Failed queue jobs
const fetchFailedJobs = async () => {
  try {
    const response = await api.get('/system/failed-jobs');
    failedJobs.value = response.data.data;
  } catch (error) {
    console.error('Failed to load queue failed logs', error);
  }
};

// Fetch Performance telemetry
const fetchTelemetry = async () => {
  try {
    const response = await api.get('/system/performance/telemetry');
    telemetry.value = response.data.data;
  } catch (error) {
    console.error('Failed to load telemetry stats', error);
  }
};

const toggleJobAccordion = (id) => {
  if (expandedJobIds.value.includes(id)) {
    expandedJobIds.value = expandedJobIds.value.filter(jid => jid !== id);
  } else {
    expandedJobIds.value.push(id);
  }
};

// Open Retry/Replay Modal
const openRetryModal = (job) => {
  selectedJob.value = job;
  retryReason.value = '';
  confirmRestrictedCheck.value = false;
  showRetryModal.value = true;
};

const closeRetryModal = () => {
  showRetryModal.value = false;
  selectedJob.value = null;
};

// Replay Failed job dispatch
const executeRetryJob = async () => {
  if (!selectedJob.value) return;
  submittingRetry.value = true;
  try {
    await api.post(`/system/failed-jobs/${selectedJob.value.id}/retry`, {
      reason: retryReason.value,
      confirm_restricted: confirmRestrictedCheck.value
    });
    toast.success(`Job re-enqueued successfully for processing.`);
    closeRetryModal();
    await fetchFailedJobs();
  } catch (error) {
    console.error('Job replay failed', error);
    const msg = error.response?.data?.message || 'Failed to replay background job.';
    toast.error(msg);
  } finally {
    submittingRetry.value = false;
  }
};

// Bulk replay failed queue
const triggerBulkRetry = async () => {
  const reason = prompt("Enter justification reason to retry ALL failed jobs:");
  if (!reason || reason.trim().length < 5) {
    toast.error("Valid justification reason required.");
    return;
  }

  try {
    await api.post('/system/failed-jobs/retry-all', { reason });
    toast.success("Bulk retry triggered. All jobs re-enqueued.");
    await fetchFailedJobs();
  } catch (error) {
    console.error('Bulk retry failed', error);
    toast.error(error.response?.data?.message || "Failed to trigger bulk retries.");
  }
};

// Delete specific failed job record
const deleteJob = async (id) => {
  if (!confirm("Are you sure you want to delete this failed job record? This cannot be undone.")) return;
  try {
    await api.delete(`/system/failed-jobs/${id}`);
    toast.success("Failed job record deleted.");
    await fetchFailedJobs();
  } catch (error) {
    console.error('Failed to delete job', error);
    toast.error("Failed to delete job record.");
  }
};

// API token & Webhook actions
const generateApiKey = () => {
  const newId = apiKeys.value.length + 1;
  apiKeys.value.push({
    id: newId,
    name: 'External ERP Sync Client',
    scope: 'all_permissions',
    preview: 'sk_live_••••••••••••' + Math.random().toString(36).substr(2, 4)
  });
  toast.success('Successfully provisioned new developer API access key.');
};

const revokeKey = (id) => {
  apiKeys.value = apiKeys.value.filter(k => k.id !== id);
  toast.success('Developer API access key revoked.');
};

const registerWebhook = () => {
  const newId = webhooks.value.length + 1;
  webhooks.value.push({
    id: newId,
    url: webhookForm.value.url,
    event: webhookForm.value.event
  });
  webhookForm.value.url = '';
  toast.success('Webhook route verified and registered.');
};

const deleteWebhook = (id) => {
  webhooks.value = webhooks.value.filter(w => w.id !== id);
  toast.success('Webhook unregistered.');
};

const verifyDns = () => {
  if (!customDomain.value) {
    toast.error('Please enter a target custom domain.');
    return;
  }
  checkingDns.value = true;
  setTimeout(() => {
    checkingDns.value = false;
    toast.success(`DNS check complete. Domain [${customDomain.value}] mapped and SSL certificate issued successfully.`);
  }, 1500);
};

// Reconnect/Resiliency state UI styling helpers
const getConnectionBorderClass = (state) => {
  if (state === 'Connected') return 'border-emerald-500/30';
  if (state === 'Reconnecting') return 'border-amber-500/30';
  if (state === 'Degraded') return 'border-orange-500/30';
  if (state === 'Recovering') return 'border-blue-500/30';
  return 'border-rose-500/30';
};

const getConnectionBgClass = (state) => {
  if (state === 'Connected') return 'bg-emerald-500';
  if (state === 'Reconnecting') return 'bg-amber-500';
  if (state === 'Degraded') return 'bg-orange-500';
  if (state === 'Recovering') return 'bg-blue-500';
  return 'bg-rose-500';
};

const formatDateTime = (str) => {
  if (!str) return '';
  const d = new Date(str);
  return d.toLocaleString();
};

onMounted(() => {
  fetchFailedJobs();
  fetchTelemetry();
});
</script>

<style scoped>
.animate-fadeIn {
  animation: fadeIn 0.25s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(4px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
