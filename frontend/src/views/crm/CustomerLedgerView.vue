<template>
  <div class="space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Customer Ledger</h1>
        <p class="text-sm text-slate-500 mt-1">Manage customer accounts and track outstanding balances.</p>
      </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/60 shadow-sm flex flex-col sm:flex-row gap-4">
      <div class="relative flex-1">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
        <input v-model="searchQuery" type="text" placeholder="Search customers by name or phone..." class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors">
      </div>
      <div class="flex gap-2">
        <select v-model="filterStatus" class="block w-full pl-3 pr-10 py-2 text-base border-slate-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl">
          <option value="all">All Customers</option>
          <option value="due">With Dues</option>
          <option value="clear">Clear Balance</option>
        </select>
      </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
      <div v-if="loading" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
        <p class="mt-4 text-sm text-slate-500">Loading ledger data...</p>
      </div>

      <table v-else class="min-w-full divide-y divide-slate-100 text-left">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Customer</th>
            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Contact</th>
            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Total Invoiced</th>
            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Total Paid</th>
            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Current Balance</th>
            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="filteredCustomers.length === 0">
            <td colspan="6" class="px-6 py-8 text-center">
              <div class="flex flex-col items-center">
                <svg class="h-12 w-12 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <h3 class="text-sm font-medium text-slate-900">No customers found</h3>
                <p class="mt-1 text-sm text-slate-500">Try adjusting your search or filter.</p>
              </div>
            </td>
          </tr>
          <tr v-for="customer in filteredCustomers" :key="customer.id" class="hover:bg-slate-50/50 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-bold text-slate-900">{{ customer.name }}</div>
              <div class="text-xs text-slate-500">{{ customer.email || 'N/A' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">{{ customer.phone }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900 text-right">${{ formatCurrency(customer.total_invoiced) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-emerald-600 text-right">${{ formatCurrency(customer.total_paid) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-right">
              <span :class="['inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold', customer.balance > 0 ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-700']">
                ${{ formatCurrency(customer.balance) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <router-link :to="`/crm/customer-statement/${customer.id}`" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Statement
              </router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import api from '../../services/api';

const loading = ref(true);
const customers = ref([]);
const searchQuery = ref('');
const filterStatus = ref('all');
let searchTimeout = null;

const fetchLedger = async () => {
  loading.value = true;
  try {
    const response = await api.get('/customer-ledgers', {
      params: {
        search: searchQuery.value || undefined
      }
    });
    // The controller returns a paginated structure
    const rawData = response.data?.data || response.data || [];
    const items = Array.isArray(rawData) ? rawData : (rawData.data || []);
    customers.value = items.map(item => ({
      id: item.customer_id,
      name: item.name || 'Unknown',
      email: item.email || '',
      phone: item.phone || '',
      total_invoiced: Number(item.total_debit || 0),
      total_paid: Number(item.total_credit || 0),
      balance: Number(item.current_balance || 0)
    }));
  } catch (error) {
    console.error('Failed to fetch customer ledger:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchLedger();
});

watch(searchQuery, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchLedger();
  }, 400);
});

const filteredCustomers = computed(() => {
  return customers.value.filter(c => {
    const matchesFilter = filterStatus.value === 'all' || 
                          (filterStatus.value === 'due' && c.balance > 0) || 
                          (filterStatus.value === 'clear' && c.balance <= 0);
    return matchesFilter;
  });
});

const formatCurrency = (val) => Number(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
</script>
