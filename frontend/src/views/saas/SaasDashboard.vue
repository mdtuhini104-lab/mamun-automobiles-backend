<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">SaaS Super Admin</h1>
      <p class="text-sm text-slate-500 mt-1">Global tenant management and multi-branch monitoring.</p>
    </div>

    <!-- Stats Grid -->
    <div v-if="saasStore.stats" class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <div class="text-sm font-medium text-slate-500">Total Tenants</div>
        <div class="mt-2 text-3xl font-bold text-slate-900">{{ saasStore.stats.total_tenants }}</div>
      </div>
      <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <div class="text-sm font-medium text-slate-500">Active Branches</div>
        <div class="mt-2 text-3xl font-bold text-slate-900">{{ saasStore.stats.total_branches }}</div>
      </div>
      <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <div class="text-sm font-medium text-slate-500">Total Users</div>
        <div class="mt-2 text-3xl font-bold text-slate-900">{{ saasStore.stats.total_users }}</div>
      </div>
      <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <div class="text-sm font-medium text-slate-500">Global Invoices</div>
        <div class="mt-2 text-3xl font-bold text-slate-900">{{ saasStore.stats.total_invoices }}</div>
      </div>
    </div>

    <!-- Tenants Table -->
    <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
      <div class="p-4 border-b bg-slate-50 flex justify-between items-center">
        <h3 class="font-semibold text-slate-900">Registered Companies (Tenants)</h3>
        <button class="text-sm bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700">Add Tenant</button>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-white">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase">Company</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase">Domain</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase">Plan</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            <tr v-for="tenant in saasStore.tenants" :key="tenant.id">
              <td class="px-6 py-4 whitespace-nowrap font-medium text-slate-900">{{ tenant.company_name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ tenant.domain }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs font-medium rounded-md bg-purple-100 text-purple-800 uppercase">{{ tenant.subscription_plan }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs font-medium rounded-md"
                  :class="{
                    'bg-emerald-100 text-emerald-800': tenant.status === 'active',
                    'bg-amber-100 text-amber-800': tenant.status === 'trial'
                  }">
                  {{ tenant.status.toUpperCase() }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useSaasStore } from '../../stores/saas';

const saasStore = useSaasStore();

onMounted(() => {
  saasStore.fetchStats();
  saasStore.fetchTenants();
});
</script>

