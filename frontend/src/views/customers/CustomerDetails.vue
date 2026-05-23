<template>
  <div class="space-y-6">
    <div class="flex items-center gap-3">
      <router-link :to="{ name: 'customers.index' }" class="text-slate-400 hover:text-indigo-600 transition-colors p-2 -ml-2 rounded-lg hover:bg-indigo-50">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
      </router-link>
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Customer Details</h1>
        <p class="text-sm text-slate-500 mt-1">View profile, vehicles, and billing history.</p>
      </div>
      <div class="ml-auto flex gap-2">
        <router-link :to="{ name: 'customers.edit', params: { id: route.params.id } }" v-if="authStore.hasPermission('customer.update') && customerStore.currentCustomer" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-colors">
          Edit Profile
        </router-link>
      </div>
    </div>

    <div v-if="customerStore.loading" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 animate-pulse">
      <div class="h-6 bg-slate-200 rounded w-1/4 mb-4"></div>
      <div class="space-y-3">
        <div class="h-4 bg-slate-100 rounded w-1/2"></div>
        <div class="h-4 bg-slate-100 rounded w-1/3"></div>
      </div>
    </div>

    <div v-else-if="customer" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- Profile Card -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden lg:col-span-1">
        <div class="p-6">
          <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-xl font-bold mb-4">
            {{ customer.name.charAt(0).toUpperCase() }}
          </div>
          <h2 class="text-xl font-bold text-slate-900">{{ customer.name }}</h2>
          <p class="text-sm text-slate-500 mb-6">Customer since {{ new Date(customer.created_at).toLocaleDateString() }}</p>
          
          <div class="space-y-4">
            <div class="flex items-start gap-3 text-sm">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-slate-400 mt-0.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.896-1.596-5.25-3.95-6.847-6.847l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg>
              <div>
                <div class="font-medium text-slate-900">{{ customer.phone }}</div>
                <div class="text-slate-500">Mobile</div>
              </div>
            </div>
            
            <div v-if="customer.email" class="flex items-start gap-3 text-sm">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-slate-400 mt-0.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
              <div>
                <div class="font-medium text-slate-900">{{ customer.email }}</div>
                <div class="text-slate-500">Email</div>
              </div>
            </div>
            
            <div class="flex items-start gap-3 text-sm">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-slate-400 mt-0.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
              <div>
                <div class="font-medium text-slate-900">{{ customer.address || 'No address provided' }}</div>
                <div class="text-slate-500">Address</div>
              </div>
            </div>
          </div>
          
          <div class="mt-6 pt-6 border-t border-slate-100">
            <div class="text-sm text-slate-500 mb-1">Current Balance</div>
            <div class="text-2xl font-bold tabular-nums" :class="Number(customer.balance) > 0 ? 'text-red-600' : 'text-slate-900'">
              ${{ Number(customer.balance).toFixed(2) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content (Tabs: Vehicles, Invoices) -->
      <div class="lg:col-span-2 space-y-6">
        
        <!-- Vehicles Section -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
            <h3 class="text-base font-bold text-slate-900">Vehicles</h3>
            <button class="text-sm text-indigo-600 font-medium hover:text-indigo-700">Add Vehicle</button>
          </div>
          <div class="p-0">
            <ul v-if="customer.vehicles && customer.vehicles.length > 0" class="divide-y divide-slate-100">
              <li v-for="vehicle in customer.vehicles" :key="vehicle.id" class="p-4 hover:bg-slate-50 transition-colors">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="font-medium text-slate-900">{{ vehicle.make }} {{ vehicle.model }} ({{ vehicle.year }})</div>
                    <div class="text-sm text-slate-500 mt-1">Reg: <span class="font-mono font-medium text-slate-700">{{ vehicle.registration_number }}</span></div>
                  </div>
                  <button class="text-slate-400 hover:text-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                  </button>
                </div>
              </li>
            </ul>
            <div v-else class="p-8 text-center text-sm text-slate-500">
              No vehicles registered for this customer.
            </div>
          </div>
        </div>
        
        <!-- Invoices Section -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
            <h3 class="text-base font-bold text-slate-900">Recent Invoices</h3>
          </div>
          <div class="p-0">
            <table v-if="customer.invoices && customer.invoices.length > 0" class="min-w-full divide-y divide-slate-200">
              <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                  <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Invoice No</th>
                  <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Status</th>
                  <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Total</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 bg-white">
                <tr v-for="invoice in customer.invoices.slice(0, 5)" :key="invoice.id" class="hover:bg-slate-50">
                  <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-indigo-600">{{ invoice.invoice_number }}</td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" :class="invoice.payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                      {{ invoice.payment_status.toUpperCase() }}
                    </span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium text-slate-900">
                    ${{ Number(invoice.grand_total).toFixed(2) }}
                  </td>
                </tr>
              </tbody>
            </table>
            <div v-else class="p-8 text-center text-sm text-slate-500">
              No invoice history found.
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useCustomerStore } from '../../stores/customer';
import { useAuthStore } from '../../stores/auth';
import { useRoute } from 'vue-router';

const customerStore = useCustomerStore();
const authStore = useAuthStore();
const route = useRoute();

const customer = computed(() => customerStore.currentCustomer);

onMounted(() => {
  customerStore.fetchCustomer(route.params.id);
});
</script>
