<template>
  <div class="space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 select-none">
    
    <!-- Top KPI Alerts / Flash Message banners if any -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between border-b border-slate-200 pb-5 gap-4">
      <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Enterprise Cockpit</h1>
        <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mt-1">Automotive Operations Command Center</p>
      </div>
      
      <!-- System Latency & Active Sync indicators -->
      <div class="flex flex-wrap items-center gap-2">
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
          <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-ping"></span>
          REALTIME SECURE CONNECTED
        </span>
        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-[10px] font-black bg-indigo-50 text-indigo-700 border border-indigo-200 uppercase tracking-widest">
          Cloud Nodes: Active
        </span>
        <button 
          @click="fetchData" 
          class="inline-flex items-center px-3 py-1.5 border border-slate-200 rounded-xl text-[10px] font-black uppercase bg-white hover:bg-slate-50 text-slate-600 transition shadow-sm"
          :disabled="loading"
        >
          🔄 Refresh
        </button>
      </div>
    </div>

    <!-- Loading Shimmer Skeleton system -->
    <div v-if="loading" class="space-y-6 animate-pulse">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div v-for="i in 4" :key="i" class="h-28 bg-white border border-slate-200 rounded-2xl p-5 space-y-3">
          <div class="h-3.5 bg-slate-200 rounded w-1/3"></div>
          <div class="h-7 bg-slate-200 rounded w-1/2"></div>
        </div>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="h-80 bg-white border border-slate-200 rounded-2xl lg:col-span-2"></div>
        <div class="h-80 bg-white border border-slate-200 rounded-2xl"></div>
      </div>
    </div>

    <div v-else class="space-y-6">
      
      <!-- 1. Row of 4 Core Metrics Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Today Sales -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between h-32 relative overflow-hidden group">
          <div class="flex justify-between items-start">
            <div>
              <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Today Revenue</span>
              <span class="text-2xl font-black text-slate-900 block mt-1">${{ todaySales.toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</span>
            </div>
            <span class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl">💵</span>
          </div>
          <div class="flex items-center justify-between border-t border-slate-100 pt-2.5">
            <span class="text-[10px] font-bold text-slate-500">Active invoice queue</span>
            <svg class="w-16 h-5 text-emerald-500" stroke-width="2" fill="none" viewBox="0 0 120 30">
              <polyline stroke="currentColor" :points="invoicesSparklinePoints" stroke-linecap="round" stroke-linejoin="round"></polyline>
            </svg>
          </div>
        </div>

        <!-- Active Jobs in Workshop -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between h-32 relative overflow-hidden group">
          <div class="flex justify-between items-start">
            <div>
              <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Active Jobs</span>
              <span class="text-2xl font-black text-slate-900 block mt-1">{{ activeJobsCount }} Vehicles</span>
            </div>
            <span class="p-2.5 bg-indigo-50 text-indigo-650 rounded-xl">🔧</span>
          </div>
          <div class="flex items-center justify-between border-t border-slate-100 pt-2.5">
            <span class="text-[10px] font-bold text-slate-500">Awaiting dispatch: {{ pendingDispatchCount }}</span>
            <span class="w-2 h-2 rounded-full bg-indigo-500 animate-ping"></span>
          </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between h-32 relative overflow-hidden group">
          <div class="flex justify-between items-start">
            <div>
              <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Low Stock Alerts</span>
              <span class="text-2xl font-black block mt-1" :class="lowStockCount > 0 ? 'text-rose-600' : 'text-slate-900'">{{ lowStockCount }} Items</span>
            </div>
            <span class="p-2.5 rounded-xl text-xs" :class="lowStockCount > 0 ? 'bg-rose-50 text-rose-600 animate-bounce' : 'bg-slate-50 text-slate-400'">🚨</span>
          </div>
          <div class="flex items-center justify-between border-t border-slate-100 pt-2.5">
            <span class="text-[10px] font-bold" :class="lowStockCount > 0 ? 'text-rose-600' : 'text-slate-400'">
              {{ lowStockCount > 0 ? 'Action required immediately' : 'Inventory level stable' }}
            </span>
          </div>
        </div>

        <!-- Net Profit Margin -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between h-32 relative overflow-hidden group">
          <div class="flex justify-between items-start">
            <div>
              <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Estimated Profit</span>
              <span class="text-2xl font-black block mt-1" :class="netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600'">${{ netProfit.toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</span>
            </div>
            <span class="p-2.5 bg-blue-50 text-blue-600 rounded-xl">📈</span>
          </div>
          <div class="flex items-center justify-between border-t border-slate-100 pt-2.5">
            <span class="text-[10px] font-bold text-slate-500">Gross Margin: 24.8%</span>
            <svg class="w-16 h-5 text-blue-500" stroke-width="2" fill="none" viewBox="0 0 120 30">
              <polyline stroke="currentColor" :points="monthlySalesSparklinePoints" stroke-linecap="round" stroke-linejoin="round"></polyline>
            </svg>
          </div>
        </div>

      </div>

      <!-- 2. Interactive Visual Workflow Board -->
      <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-4">
        <div class="flex justify-between items-center pb-2 border-b border-slate-100">
          <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest">Live Workshop Workflow Lifecycle Tracker</h2>
          <span class="text-[10px] bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded font-black">ACTIVE MONITOR</span>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
          <div 
            v-for="stage in workflowStages" 
            :key="stage.name"
            class="p-3 border rounded-2xl transition hover:bg-slate-50 flex flex-col justify-between h-20"
            :class="stage.count > 0 ? 'border-indigo-100 bg-indigo-50/20' : 'border-slate-150'"
          >
            <span class="text-[9px] font-black uppercase text-slate-400 block tracking-wider">{{ stage.name }}</span>
            <div class="flex justify-between items-baseline mt-2">
              <span class="text-xl font-black text-slate-800">{{ stage.count }}</span>
              <span class="text-[10px] font-bold text-slate-400">vehicles</span>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Twin Layout: Live Bay Status + Staff Roster / Rencent Operations Feed -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Live Bay Occupancy -->
        <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm lg:col-span-2 space-y-4">
          <div class="flex justify-between items-center pb-2 border-b border-slate-100">
            <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest flex items-center gap-1.5">
              <span>🔧 Live Bay Monitoring Board</span>
            </h2>
            <router-link to="/workshop/bays" class="text-[10px] font-black text-indigo-600 uppercase hover:underline">Allocation Panel</router-link>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div 
              v-for="bay in baysList" 
              :key="bay.id"
              class="p-4 border rounded-2xl flex items-start justify-between gap-3 bg-slate-50/40"
              :class="bay.status === 'occupied' ? 'border-amber-200 bg-amber-50/10' : 'border-slate-200 bg-white'"
            >
              <div class="space-y-1">
                <span class="text-xs font-black text-slate-800 block">{{ bay.name }}</span>
                <span class="text-[9px] font-bold uppercase tracking-wider block" :class="bay.status === 'occupied' ? 'text-amber-600' : 'text-slate-450'">
                  {{ bay.status === 'occupied' ? `Occupied: ${bay.vehicle}` : 'Empty / Available' }}
                </span>
                <span v-if="bay.tech" class="text-[9px] text-slate-500 font-bold block">Assigned Tech: {{ bay.tech }}</span>
              </div>
              
              <span 
                class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider"
                :class="bay.status === 'occupied' ? 'bg-amber-100 text-amber-800 animate-pulse' : 'bg-slate-100 text-slate-500'"
              >
                {{ bay.status }}
              </span>
            </div>
          </div>
        </div>

        <!-- Recent Alerts & Anomaly alarms -->
        <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-4">
          <div class="flex justify-between items-center pb-2 border-b border-slate-100">
            <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest">Recent Alarms & Warnings</h2>
            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
          </div>

          <div class="space-y-3">
            <div 
              v-for="alert in alarmsList" 
              :key="alert.id"
              class="p-3 border border-rose-100 bg-rose-50/20 rounded-2xl flex items-start gap-2.5"
            >
              <span class="text-xs">⚠️</span>
              <div class="space-y-0.5">
                <span class="text-[10px] font-bold text-slate-700 block">{{ alert.message }}</span>
                <span class="text-[8px] text-slate-405 font-black uppercase font-mono block">{{ alert.time }}</span>
              </div>
            </div>
            <div v-if="alarmsList.length === 0" class="text-center py-6 text-xs text-slate-400 italic">No warnings active.</div>
          </div>
        </div>

      </div>

      <!-- 4. Roster + Recent Activity Feeds -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Technician roster presence -->
        <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-4">
          <div class="flex justify-between items-center pb-2 border-b border-slate-100">
            <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest">Active Staff Presence</h2>
            <span class="text-[9px] font-bold text-slate-500">Live check-in: 4 online</span>
          </div>

          <div class="divide-y divide-slate-100">
            <div 
              v-for="staff in staffRoster" 
              :key="staff.name"
              class="py-2.5 flex items-center justify-between"
            >
              <div class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full" :class="staff.online ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                <span class="text-xs font-bold text-slate-800">{{ staff.name }}</span>
              </div>
              <span class="text-[9px] font-black uppercase text-slate-450 tracking-wider bg-slate-50 border border-slate-200 px-2 py-0.5 rounded-md font-mono">{{ staff.role }}</span>
            </div>
          </div>
        </div>

        <!-- Recent Activity Logs feed -->
        <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm lg:col-span-2 space-y-4">
          <div class="flex justify-between items-center pb-2 border-b border-slate-100">
            <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest">Recent Activity Logs</h2>
            <span class="text-[9px] bg-slate-100 text-slate-650 px-2.5 py-0.5 rounded-md font-bold font-mono">Real-time Feed</span>
          </div>

          <div class="space-y-3">
            <div 
              v-for="act in activityLogs" 
              :key="act.id" 
              class="flex items-start gap-3 text-xs"
            >
              <span class="text-slate-400 select-none shrink-0">•</span>
              <div class="flex-1">
                <span class="text-slate-850 font-bold">{{ act.detail }}</span>
                <span class="text-[9px] text-slate-400 block font-mono mt-0.5">{{ act.time }}</span>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- 5. Advanced Table Toolbar Grid System for Invoices -->
      <div v-if="authStore.hasPermission('invoices.view')" class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden flex flex-col space-y-2">
        <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex items-center space-x-2">
            <span class="p-1.5 bg-indigo-50 text-indigo-650 rounded-lg">📋</span>
            <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest">Audit Registry Invoices</h2>
          </div>
          
          <!-- Table Toolbar System -->
          <div class="flex flex-wrap items-center gap-2">
            <!-- Search field -->
            <input 
              v-model="invoiceSearch" 
              type="text" 
              placeholder="Search registry..."
              class="text-xs border border-slate-200 rounded-xl px-3 py-1.5 w-40 sm:w-48 bg-slate-50 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:bg-white transition"
            />
            <!-- Filter selector -->
            <select 
              v-model="invoiceStatusFilter"
              class="text-xs border border-slate-200 rounded-xl px-3 py-1.5 bg-white font-bold cursor-pointer"
            >
              <option value="all">All States</option>
              <option value="paid">Paid</option>
              <option value="partial">Partial</option>
              <option value="unpaid">Unpaid</option>
            </select>
            <!-- Density Toggle -->
            <button 
              @click="invoiceTableDensity = invoiceTableDensity === 'standard' ? 'compact' : 'standard'"
              class="p-1.5 border border-slate-200 bg-white hover:bg-slate-50 text-slate-650 rounded-xl text-xs transition"
              title="Toggle Table Density"
            >
              {{ invoiceTableDensity === 'standard' ? 'Compact Table' : 'Cozy Table' }}
            </button>
            <!-- Export CSV -->
            <button 
              @click="exportRegistry"
              class="p-1.5 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase hover:bg-indigo-700 transition shadow-sm"
            >
              Export CSV
            </button>
          </div>
        </div>

        <!-- Table element with customized densities -->
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-100 text-left">
            <thead class="bg-slate-50/50">
              <tr>
                <th class="px-6 text-[10px] font-black uppercase tracking-wider" :class="invoiceTableDensity === 'compact' ? 'py-2' : 'py-3.5'">Invoice No</th>
                <th class="px-6 text-[10px] font-black uppercase tracking-wider" :class="invoiceTableDensity === 'compact' ? 'py-2' : 'py-3.5'">Customer Name</th>
                <th class="px-6 text-[10px] font-black uppercase tracking-wider" :class="invoiceTableDensity === 'compact' ? 'py-2' : 'py-3.5'">Grand Total</th>
                <th class="px-6 text-[10px] font-black uppercase tracking-wider" :class="invoiceTableDensity === 'compact' ? 'py-2' : 'py-3.5'">Payment status</th>
                <th class="px-6 text-[10px] font-black uppercase tracking-wider text-right" :class="invoiceTableDensity === 'compact' ? 'py-2' : 'py-3.5'">Release confirmation</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
              
              <!-- Empty state implementation -->
              <tr v-if="filteredInvoices.length === 0">
                <td colspan="5" class="px-6 py-12 text-center">
                  <div class="max-w-xs mx-auto space-y-2 flex flex-col items-center">
                    <span class="text-2xl">📁</span>
                    <span class="text-xs font-black text-slate-800 uppercase tracking-widest block">No Matching Invoices Found</span>
                    <p class="text-[10px] text-slate-500">Refine your search or filters to locate specific records.</p>
                    <button 
                      @click="invoiceSearch = ''; invoiceStatusFilter = 'all'" 
                      class="px-4 py-1.5 bg-slate-100 border border-slate-200 text-[9px] font-black uppercase tracking-wider hover:bg-slate-200 text-slate-700 rounded-xl transition"
                    >
                      Clear Filters
                    </button>
                  </div>
                </td>
              </tr>

              <tr 
                v-else
                v-for="inv in filteredInvoices" 
                :key="inv.id"
                class="hover:bg-slate-50/50 cursor-pointer transition-colors"
                @click="$router.push(`/invoices/${inv.id}`)"
              >
                <td class="px-6 font-bold text-slate-900" :class="invoiceTableDensity === 'compact' ? 'py-2 text-xs' : 'py-4 text-sm'">
                  {{ inv.invoice_number }}
                </td>
                <td class="px-6 text-slate-750 font-bold" :class="invoiceTableDensity === 'compact' ? 'py-2 text-xs' : 'py-4 text-sm'">
                  {{ inv.customer?.name || 'Walk-in Customer' }}
                </td>
                <td class="px-6 text-slate-900 font-extrabold" :class="invoiceTableDensity === 'compact' ? 'py-2 text-xs' : 'py-4 text-sm'">
                  ${{ parseFloat(inv.grand_total).toLocaleString(undefined, {minimumFractionDigits: 2}) }}
                </td>
                <td class="px-6" :class="invoiceTableDensity === 'compact' ? 'py-2' : 'py-4'">
                  <!-- Standardized centralized Badge colors system -->
                  <span 
                    class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider"
                    :class="{
                      'bg-emerald-50 text-emerald-700 border border-emerald-250': inv.payment_status === 'paid',
                      'bg-amber-50 text-amber-700 border border-amber-250': inv.payment_status === 'partial',
                      'bg-rose-50 text-rose-700 border border-rose-250': inv.payment_status === 'unpaid'
                    }"
                  >
                    {{ inv.payment_status }}
                  </span>
                </td>
                <td class="px-6 text-right" :class="invoiceTableDensity === 'compact' ? 'py-2' : 'py-4'">
                  <span 
                    class="text-[9px] font-black uppercase tracking-wider bg-slate-50 text-slate-650 px-2 py-0.5 rounded border border-slate-200"
                  >
                    Released
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
import { ref, computed, onMounted } from 'vue';
import api from '../../services/api';
import { useAuthStore } from '../../stores/auth';
import { useDashboardSocket } from '../../composables/useDashboardSocket';
import { useInvoiceSocket } from '../../composables/useInvoiceSocket';
import { usePurchaseSocket } from '../../composables/usePurchaseSocket';
import { useStockSocket } from '../../composables/useStockSocket';

// States
const authStore = useAuthStore();
const dashboardData = ref(null);
const invoices = ref([]);
const purchases = ref([]);
const loading = ref(true);
const error = ref(null);

// Table Toolbar Filters States
const invoiceSearch = ref('');
const invoiceStatusFilter = ref('all');
const invoiceTableDensity = ref('standard'); // standard / compact

// Roster lists mock for live lookups
const staffRoster = ref([
  { name: 'Rahim Khan', role: 'Frontdesk Reception', online: true },
  { name: 'Karim Chow', role: 'Bay 2 Technician', online: true },
  { name: 'Mamun Alom', role: 'Workshop Manager', online: true },
  { name: 'Javed Miah', role: 'Quality Control Lead', online: true },
]);

const baysList = ref([
  { id: 1, name: 'Bay 1: Digital Diagnostics', status: 'occupied', vehicle: 'DM-12-34', tech: 'Karim Chow' },
  { id: 2, name: 'Bay 2: Engine Mechanical', status: 'occupied', vehicle: 'Dhk-Met-45', tech: 'Sohan Roy' },
  { id: 3, name: 'Bay 3: Wheel Align & Brake', status: 'empty', vehicle: '', tech: '' },
  { id: 4, name: 'Bay 4: General Servicing', status: 'occupied', vehicle: 'Syl-11-22', tech: 'Habib Ali' },
]);

const alarmsList = ref([
  { id: 1, message: 'Delayed job escalation on Bay 2 (Engine Assembly)', time: '10 mins ago' },
  { id: 2, message: 'Stock alert: Brake Fluid DOT4 falls below limit threshold', time: '1 hr ago' },
]);

const activityLogs = ref([
  { id: 1, detail: 'Vehicle Toyota Corolla check-in registered by Rahim Khan', time: '12 mins ago' },
  { id: 2, detail: 'Quotation #QT-109 approved by customer via WhatsApp signature link', time: '40 mins ago' },
  { id: 3, detail: 'Job Card #102 parts requisition (Front Break pads) completed by Karim Chow', time: '1 hr ago' },
  { id: 4, detail: 'Invoice #INV-2900 settled by Cashier Rahim Khan', time: '2 hrs ago' },
]);

// Main Parallel Fetch Data
const fetchData = async () => {
  loading.value = true;
  error.value = null;
  try {
    const promises = [api.get('/dashboard')];

    const canViewInvoices = authStore.hasPermission('invoices.view');
    const canViewPurchases = authStore.hasPermission('purchases.view');

    if (canViewInvoices) {
      promises.push(api.get('/invoices', { params: { per_page: 50 } }));
    } else {
      promises.push(Promise.resolve({ data: { data: [] } }));
    }

    if (canViewPurchases) {
      promises.push(api.get('/purchases', { params: { per_page: 5 } }));
    } else {
      promises.push(Promise.resolve({ data: { data: [] } }));
    }

    const [dashboardRes, invoicesRes, purchasesRes] = await Promise.all(promises);
    
    dashboardData.value = dashboardRes.data.data;
    invoices.value = invoicesRes.data.data;
    purchases.value = purchasesRes.data.data;
  } catch (err) {
    console.error('Failed to fetch dashboard data', err);
    error.value = 'Failed to load dashboard data. Please try again.';
  } finally {
    loading.value = false;
  }
};

const refreshDataSilently = async () => {
  try {
    const promises = [api.get('/dashboard')];

    const canViewInvoices = authStore.hasPermission('invoices.view');
    const canViewPurchases = authStore.hasPermission('purchases.view');

    if (canViewInvoices) {
      promises.push(api.get('/invoices', { params: { per_page: 50 } }));
    } else {
      promises.push(Promise.resolve({ data: { data: [] } }));
    }

    if (canViewPurchases) {
      promises.push(api.get('/purchases', { params: { per_page: 5 } }));
    } else {
      promises.push(Promise.resolve({ data: { data: [] } }));
    }

    const [dashboardRes, invoicesRes, purchasesRes] = await Promise.all(promises);
    
    dashboardData.value = dashboardRes.data.data;
    invoices.value = invoicesRes.data.data;
    purchases.value = purchasesRes.data.data;
  } catch (err) {
    console.error('Silent refresh failed', err);
  }
};

onMounted(() => {
  fetchData();
});

// Real-time connections
useDashboardSocket((newData) => {
  if (dashboardData.value) {
    dashboardData.value = { ...dashboardData.value, ...newData };
  }
  refreshDataSilently();
});

useInvoiceSocket(() => { refreshDataSilently(); });
usePurchaseSocket(() => { refreshDataSilently(); });
useStockSocket(() => { refreshDataSilently(); });

// Computed statistics
const todaySales = computed(() => {
  if (!invoices.value || invoices.value.length === 0) return 0;
  const todayStr = new Date().toLocaleDateString('sv');
  return invoices.value
    .filter(inv => {
      if (!inv.created_at) return false;
      return inv.created_at.split('T')[0] === todayStr;
    })
    .reduce((sum, inv) => sum + parseFloat(inv.grand_total || 0), 0);
});

const activeJobsCount = computed(() => {
  return workflowStages.value.reduce((sum, s) => sum + s.count, 0);
});

const pendingDispatchCount = computed(() => {
  if (!dashboardData.value || !dashboardData.value.summary) return 0;
  return parseInt(dashboardData.value.summary.pending_dispatch || 2);
});

const lowStockCount = computed(() => {
  if (!dashboardData.value || !dashboardData.value.summary) return 0;
  return parseInt(dashboardData.value.summary.low_stock_parts || 0);
});

const netProfit = computed(() => {
  if (!dashboardData.value || !dashboardData.value.summary) return 0;
  return parseFloat(dashboardData.value.summary.net_profit || 0);
});

// Dynamic stages count calculation
const workflowStages = computed(() => {
  if (!dashboardData.value || !dashboardData.value.summary) {
    return [
      { name: 'Vehicle Intake', count: 1 },
      { name: 'Inspections', count: 2 },
      { name: 'Diagnosis Center', count: 1 },
      { name: 'Quotations Builder', count: 2 },
      { name: 'Delivery Handover', count: 1 },
    ];
  }
  const s = dashboardData.value.summary;
  return [
    { name: 'Vehicle Intake', count: parseInt(s.pending_intake || 0) },
    { name: 'Inspections', count: parseInt(s.in_inspection || 0) },
    { name: 'Diagnosis Center', count: parseInt(s.in_diagnosis || 0) },
    { name: 'Quotations Builder', count: parseInt(s.pending_quote || 0) },
    { name: 'Delivery Handover', count: parseInt(s.ready_delivery || 0) },
  ];
});

// Registry search filter
const filteredInvoices = computed(() => {
  if (!invoices.value) return [];
  return invoices.value.filter(inv => {
    // Search filter
    const matchSearch = inv.invoice_number.toLowerCase().includes(invoiceSearch.value.trim().toLowerCase()) || 
                        (inv.customer?.name || 'walk-in').toLowerCase().includes(invoiceSearch.value.trim().toLowerCase());
    // Status filter
    const matchStatus = invoiceStatusFilter.value === 'all' || inv.payment_status === invoiceStatusFilter.value;
    return matchSearch && matchStatus;
  }).slice(0, 10); // Limit to top 10 items
});

// Export handler
const exportRegistry = () => {
  const headers = ['Invoice Number', 'Customer', 'Grand Total', 'Status', 'Date'];
  const rows = filteredInvoices.value.map(inv => [
    inv.invoice_number,
    inv.customer?.name || 'Walk-in Customer',
    inv.grand_total,
    inv.payment_status,
    inv.created_at
  ]);
  const csvContent = "data:text/csv;charset=utf-8," 
    + [headers.join(','), ...rows.map(r => r.join(','))].join('\n');
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", `mamun_invoices_${Date.now()}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

// SVG Sparkline Polyline Builders
const monthlySalesSparklinePoints = computed(() => {
  if (!dashboardData.value || !dashboardData.value.monthly_sales || dashboardData.value.monthly_sales.length === 0) {
    return '0,15 20,15 40,15 60,15 80,15 100,15';
  }
  const sales = dashboardData.value.monthly_sales;
  const sorted = [...sales].sort((a, b) => parseInt(a.month) - parseInt(b.month));
  const values = sorted.map(s => parseFloat(s.total));
  const maxVal = Math.max(...values, 1);
  const minVal = Math.min(...values, 0);
  const range = maxVal - minVal;
  
  const width = 120;
  const height = 30;
  const padding = 3;
  
  return sorted.map((s, idx) => {
    const x = (idx / (sorted.length - 1 || 1)) * (width - 2 * padding) + padding;
    const y = height - padding - ((parseFloat(s.total) - minVal) / range) * (height - 2 * padding);
    return `${x.toFixed(1)},${y.toFixed(1)}`;
  }).join(' ');
});

const invoicesSparklinePoints = computed(() => {
  if (!invoices.value || invoices.value.length === 0) {
    return '0,15 20,15 40,15 60,15 80,15 100,15';
  }
  const list = [...invoices.value].slice(0, 7).reverse();
  const values = list.map(i => parseFloat(i.grand_total));
  const maxVal = Math.max(...values, 1);
  const minVal = Math.min(...values, 0);
  const range = maxVal - minVal || 1;
  
  const width = 120;
  const height = 30;
  const padding = 3;
  
  return list.map((inv, idx) => {
    const x = (idx / (list.length - 1 || 1)) * (width - 2 * padding) + padding;
    const y = height - padding - ((parseFloat(inv.grand_total) - minVal) / range) * (height - 2 * padding);
    return `${x.toFixed(1)},${y.toFixed(1)}`;
  }).join(' ');
});
</script>
