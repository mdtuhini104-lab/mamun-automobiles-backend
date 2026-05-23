<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <!-- Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
      <div>
        <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Activity Logs 🕵️‍♂️</h1>
        <p class="mt-1 text-sm text-slate-500">Monitor all system actions, authentication events, and critical modifications.</p>
      </div>
      <div class="mt-4 sm:mt-0 flex gap-2">
        <button @click="exportLogs" class="btn bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-lg px-4 py-2 font-medium flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
          Export CSV
        </button>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
        <h3 class="text-sm font-semibold text-slate-500 mb-1">Total Activities</h3>
        <p class="text-3xl font-bold text-slate-800">{{ stats.total }}</p>
      </div>
      <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
        <h3 class="text-sm font-semibold text-slate-500 mb-1">Today's Logs</h3>
        <p class="text-3xl font-bold text-indigo-600">{{ stats.today }}</p>
      </div>
      <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
        <h3 class="text-sm font-semibold text-slate-500 mb-1">High Severity Alerts</h3>
        <p class="text-3xl font-bold text-rose-600">{{ stats.danger }}</p>
      </div>
      <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
        <h3 class="text-sm font-semibold text-slate-500 mb-1">Warnings</h3>
        <p class="text-3xl font-bold text-amber-500">{{ stats.warning }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6 flex flex-wrap gap-4 items-center">
      <div class="relative flex-1 min-w-[200px]">
        <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        <input type="text" v-model="filters.search" @input="fetchLogs" placeholder="Search logs..." class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 text-sm">
      </div>
      <select v-model="filters.severity" @change="fetchLogs" class="border border-slate-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 text-slate-700">
        <option value="">All Severities</option>
        <option value="info">Info</option>
        <option value="warning">Warning</option>
        <option value="danger">Danger</option>
      </select>
      <select v-model="filters.module" @change="fetchLogs" class="border border-slate-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 text-slate-700">
        <option value="">All Modules</option>
        <option value="Auth">Auth & Login</option>
        <option value="Invoice">Invoices</option>
        <option value="JobCard">Job Cards</option>
        <option value="Settings">Settings</option>
      </select>
    </div>

    <!-- Activity Log Table -->
    <div class="bg-white shadow-sm border border-slate-200 rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
          <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold">
            <tr>
              <th class="px-6 py-4">Timestamp</th>
              <th class="px-6 py-4">Severity</th>
              <th class="px-6 py-4">User</th>
              <th class="px-6 py-4">Module</th>
              <th class="px-6 py-4">Description</th>
              <th class="px-6 py-4">IP Address</th>
              <th class="px-6 py-4 text-center">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-50 transition-colors">
              <td class="px-6 py-4 text-slate-500">{{ new Date(log.created_at).toLocaleString() }}</td>
              <td class="px-6 py-4">
                <span :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  log.severity === 'danger' ? 'bg-rose-100 text-rose-800' : 
                  (log.severity === 'warning' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-800')
                ]">
                  {{ log.severity.toUpperCase() }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="font-medium text-slate-900">{{ log.user?.name || 'System' }}</div>
                <div class="text-xs text-slate-500">{{ log.user?.email || 'N/A' }}</div>
              </td>
              <td class="px-6 py-4">
                <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                  {{ log.module }}
                </span>
              </td>
              <td class="px-6 py-4 text-slate-700 truncate max-w-[250px]" :title="log.description">{{ log.description }}</td>
              <td class="px-6 py-4 text-slate-500 font-mono text-xs">{{ log.ip_address }}</td>
              <td class="px-6 py-4 text-center">
                <button @click="viewDetails(log)" class="text-indigo-600 hover:text-indigo-900 font-medium text-sm">View Payload</button>
              </td>
            </tr>
            <tr v-if="!logs.data?.length">
              <td colspan="7" class="px-6 py-12 text-center text-slate-500">No activity logs found matching the filters.</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination (Basic Visual) -->
      <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-between">
        <div class="text-sm text-slate-500">
          Showing <span class="font-medium">{{ logs.from || 0 }}</span> to <span class="font-medium">{{ logs.to || 0 }}</span> of <span class="font-medium">{{ logs.total || 0 }}</span> entries
        </div>
        <div class="flex gap-2">
          <button @click="fetchLogs(logs.current_page - 1)" :disabled="!logs.prev_page_url" class="px-3 py-1 border border-slate-300 rounded text-sm disabled:opacity-50">Previous</button>
          <button @click="fetchLogs(logs.current_page + 1)" :disabled="!logs.next_page_url" class="px-3 py-1 border border-slate-300 rounded text-sm disabled:opacity-50">Next</button>
        </div>
      </div>
    </div>

    <!-- Modal for JSON Payload -->
    <div v-if="selectedLog" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" @click="selectedLog = null"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="flex justify-between items-start mb-5">
              <h3 class="text-lg leading-6 font-semibold text-slate-900">Audit Trail Details</h3>
              <button @click="selectedLog = null" class="text-slate-400 hover:text-slate-500">
                <span class="sr-only">Close</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
              </button>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-6">
              <div class="bg-slate-50 p-3 rounded border border-slate-200">
                <div class="text-xs text-slate-500 font-semibold mb-1">EXECUTED BY</div>
                <div class="text-sm text-slate-800">{{ selectedLog.user?.name || 'System' }} ({{ selectedLog.ip_address }})</div>
              </div>
              <div class="bg-slate-50 p-3 rounded border border-slate-200">
                <div class="text-xs text-slate-500 font-semibold mb-1">HTTP METHOD / ROUTE</div>
                <div class="text-sm font-mono text-indigo-600">{{ selectedLog.method || 'CLI' }} {{ selectedLog.url }}</div>
              </div>
            </div>

            <div v-if="selectedLog.old_values || selectedLog.new_values" class="grid grid-cols-2 gap-6">
              <div>
                <h4 class="text-sm font-semibold text-slate-700 mb-2">Old Values (Before)</h4>
                <div class="bg-slate-900 rounded-lg p-4 overflow-x-auto">
                  <pre class="text-xs text-rose-300 font-mono">{{ formatJson(selectedLog.old_values) }}</pre>
                </div>
              </div>
              <div>
                <h4 class="text-sm font-semibold text-slate-700 mb-2">New Values (After)</h4>
                <div class="bg-slate-900 rounded-lg p-4 overflow-x-auto">
                  <pre class="text-xs text-green-300 font-mono">{{ formatJson(selectedLog.new_values) }}</pre>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-slate-500">
              No JSON payload recorded for this event.
            </div>
          </div>
          <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-200">
            <button type="button" @click="selectedLog = null" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
              Close Viewer
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';

const logs = ref({});
const stats = ref({ total: 0, today: 0, danger: 0, warning: 0 });
const selectedLog = ref(null);

const filters = ref({
  search: '',
  severity: '',
  module: '',
});

const fetchStats = async () => {
  try {
    const res = await api.get('/activity-logs/stats');
    stats.value = res.data.data;
  } catch (err) {
    console.error('Error fetching stats', err);
  }
};

const fetchLogs = async (page = 1) => {
  try {
    const res = await api.get('/activity-logs', {
      params: { ...filters.value, page }
    });
    logs.value = res.data.data;
  } catch (err) {
    console.error('Error fetching logs', err);
  }
};

const formatJson = (data) => {
  if (!data) return 'null';
  if (typeof data === 'string') {
    try { data = JSON.parse(data); } catch(e) { return data; }
  }
  return JSON.stringify(data, null, 2);
};

const viewDetails = (log) => {
  selectedLog.value = log;
};

const exportLogs = async () => {
  try {
    const res = await api.get('/activity-logs/export');
    const data = res.data.data;
    
    // Quick CSV generator
    const headers = ['Timestamp', 'Severity', 'User', 'Module', 'Action', 'Description', 'IP'];
    const csvRows = [headers.join(',')];
    
    data.forEach(row => {
      csvRows.push([
        row.created_at,
        row.severity,
        row.user?.name || 'System',
        row.module,
        row.action,
        `"${row.description.replace(/"/g, '""')}"`,
        row.ip_address
      ].join(','));
    });
    
    const blob = new Blob([csvRows.join('\n')], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `audit-logs-${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    
  } catch(err) {
    console.error('Export failed', err);
  }
};

onMounted(() => {
  fetchStats();
  fetchLogs();
});
</script>
