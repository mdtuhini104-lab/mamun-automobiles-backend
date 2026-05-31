<template>
  <div class="space-y-4">
    <div class="flex justify-between items-end">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Customers & Service bookings</h1>
        <p class="text-sm text-slate-500 mt-1">Manage your customer base, vehicles, appointments, and service bookings.</p>
      </div>
      <router-link v-if="activeTab === 'directory' && authStore.hasPermission('customer.create')" :to="{ name: 'customers.create' }" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-colors flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Add Customer
      </router-link>
    </div>

    <!-- Tabs Header -->
    <div class="border-b border-slate-200">
      <nav class="flex -mb-px space-x-6" aria-label="Tabs">
        <button
          @click="activeTab = 'directory'"
          :class="[
            activeTab === 'directory'
              ? 'border-indigo-600 text-indigo-600 font-semibold'
              : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'
          ]"
          class="pb-4 px-1 border-b-2 font-medium text-sm transition-all focus:outline-none"
        >
          Customer Directory
        </button>
        <button
          @click="activeTab = 'bookings'"
          :class="[
            activeTab === 'bookings'
              ? 'border-indigo-600 text-indigo-600 font-semibold'
              : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'
          ]"
          class="pb-4 px-1 border-b-2 font-medium text-sm transition-all focus:outline-none"
        >
          Service Bookings
        </button>
      </nav>
    </div>

    <div v-if="activeTab === 'directory'" class="space-y-4">
      <!-- Filters & Search -->
      <div class="bg-white p-3 rounded-xl shadow-sm border border-slate-200 flex flex-col sm:flex-row gap-3 items-center">
        <div class="relative flex-1 w-full">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
          </div>
          <input 
            v-model="searchInput"
            @keyup.enter="applySearch"
            type="text" 
            placeholder="Search by name, phone, email or vehicle reg..." 
            class="block w-full pl-9 rounded-lg border-0 bg-slate-50 py-2 text-slate-900 shadow-inner ring-1 ring-inset ring-slate-200 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition-all"
          >
        </div>
        <button @click="applySearch" class="w-full sm:w-auto px-4 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-lg text-sm font-medium transition-colors border border-slate-200 shadow-sm whitespace-nowrap">
          Search
        </button>
      </div>
      
      <!-- Customers Table -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr>
                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider cursor-pointer hover:text-indigo-600 group" @click="toggleSort('name')">
                  <div class="flex items-center gap-1">Customer <span class="text-slate-400 group-hover:text-indigo-500">↕</span></div>
                </th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider cursor-pointer hover:text-indigo-600 group" @click="toggleSort('phone')">
                  <div class="flex items-center gap-1">Contact <span class="text-slate-400 group-hover:text-indigo-500">↕</span></div>
                </th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                  Stats
                </th>
                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
              
              <template v-if="customerStore.loading">
                <tr v-for="i in 5" :key="'skeleton-'+i" class="animate-pulse">
                  <td class="px-4 py-3"><div class="h-4 bg-slate-200 rounded w-3/4 mb-2"></div><div class="h-3 bg-slate-100 rounded w-1/2"></div></td>
                  <td class="px-4 py-3"><div class="h-4 bg-slate-200 rounded w-24 mb-2"></div><div class="h-3 bg-slate-100 rounded w-32"></div></td>
                  <td class="px-4 py-3"><div class="flex gap-2"><div class="h-6 w-12 bg-slate-200 rounded-full"></div><div class="h-6 w-12 bg-slate-200 rounded-full"></div></div></td>
                  <td class="px-4 py-3 text-right"><div class="h-8 bg-slate-200 rounded w-20 inline-block"></div></td>
                </tr>
              </template>
              
              <template v-else>
                <tr v-for="customer in customerStore.customers" :key="customer.id" class="hover:bg-slate-50/80 transition-colors group">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <div class="text-sm font-semibold text-slate-900 group-hover:text-indigo-700 transition-colors">{{ customer.name }}</div>
                    <div class="text-xs text-slate-500 mt-0.5 max-w-[200px] truncate" :title="customer.address">{{ customer.address || 'No address' }}</div>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <div class="text-sm text-slate-700">{{ customer.phone }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">{{ customer.email || '-' }}</div>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <span class="inline-flex items-center px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 text-xs font-medium rounded-full" title="Vehicles">
                        🚗 {{ customer.vehicles_count }}
                      </span>
                      <span class="inline-flex items-center px-2 py-0.5 bg-green-50 text-green-700 border border-green-200 text-xs font-medium rounded-full" title="Invoices">
                        📄 {{ customer.invoices_count }}
                      </span>
                    </div>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-right">
                    <div class="flex justify-end gap-1">
                      <router-link :to="{ name: 'customers.show', params: { id: customer.id } }" v-if="authStore.hasPermission('customer.view')" class="text-slate-400 hover:text-indigo-600 transition-colors p-1.5 rounded-md hover:bg-indigo-50">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                      </router-link>
                      <router-link :to="{ name: 'customers.edit', params: { id: customer.id } }" v-if="authStore.hasPermission('customer.update')" class="text-slate-400 hover:text-emerald-600 transition-colors p-1.5 rounded-md hover:bg-emerald-50">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                      </router-link>
                      <button @click="handleDelete(customer.id)" v-if="authStore.hasPermission('customer.delete')" class="text-slate-400 hover:text-red-600 transition-colors p-1.5 rounded-md hover:bg-red-50">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                      </button>
                    </div>
                  </td>
                </tr>
                
                <tr v-if="customerStore.customers.length === 0">
                  <td colspan="4" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center justify-center">
                      <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                      </div>
                      <h3 class="text-base font-semibold text-slate-900 mb-1">No customers found</h3>
                      <p class="text-sm text-slate-500 max-w-sm">Get started by creating a new customer or adjusting your search filters.</p>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div v-if="customerStore.pagination.last_page > 1" class="px-4 py-3 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-0">
          <div class="text-sm text-slate-500">
            Showing <span class="font-medium text-slate-900">{{ customerStore.pagination.current_page }}</span> of <span class="font-medium text-slate-900">{{ customerStore.pagination.last_page }}</span> pages
          </div>
          <div class="flex space-x-2">
            <button 
              @click="goToPage(customerStore.pagination.current_page - 1)"
              :disabled="customerStore.pagination.current_page === 1"
              class="px-3 py-1.5 bg-white text-slate-700 border border-slate-300 rounded-md hover:bg-slate-50 disabled:opacity-50 text-sm font-medium shadow-sm"
            >
              Previous
            </button>
            <button 
              @click="goToPage(customerStore.pagination.current_page + 1)"
              :disabled="customerStore.pagination.current_page === customerStore.pagination.last_page"
              class="px-3 py-1.5 bg-white text-slate-700 border border-slate-300 rounded-md hover:bg-slate-50 disabled:opacity-50 text-sm font-medium shadow-sm"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Bookings/Appointments Tab -->
    <div v-else-if="activeTab === 'bookings'" class="space-y-4">
      <div v-if="crmStore.loading" class="flex justify-center p-12 bg-white rounded-xl border border-slate-200 shadow-sm">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
      </div>
      <div v-else class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Date & Time</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Customer / Vehicle</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Mechanic</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Service Type</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
              <tr v-for="apt in crmStore.appointments" :key="apt.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-semibold text-slate-900">{{ apt.appointment_date }}</div>
                  <div class="text-xs text-slate-500 mt-0.5">{{ apt.appointment_time }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-slate-900">{{ apt.customer?.name }}</div>
                  <div class="text-xs text-slate-500 mt-0.5">{{ apt.customer?.phone }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-slate-900 font-medium">{{ apt.mechanic?.name || 'Unassigned' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-slate-700">{{ apt.service_type }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full border"
                    :class="{
                      'bg-amber-50 text-amber-700 border-amber-200': apt.status === 'pending',
                      'bg-blue-50 text-blue-700 border-blue-200': apt.status === 'confirmed',
                      'bg-emerald-50 text-emerald-700 border-emerald-200': apt.status === 'completed',
                      'bg-rose-50 text-rose-700 border-rose-200': apt.status === 'cancelled'
                    }">
                    {{ apt.status.toUpperCase() }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                  <button v-if="apt.status === 'pending'" @click="updateAptStatus(apt.id, 'confirmed')" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-2 py-1 rounded transition-colors text-xs font-semibold">Confirm</button>
                  <button v-if="apt.status === 'confirmed'" @click="updateAptStatus(apt.id, 'completed')" class="text-emerald-600 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 px-2 py-1 rounded transition-colors text-xs font-semibold">Complete</button>
                  <button v-if="apt.status !== 'completed' && apt.status !== 'cancelled'" @click="updateAptStatus(apt.id, 'cancelled')" class="text-rose-600 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 px-2 py-1 rounded transition-colors text-xs font-semibold">Cancel</button>
                </td>
              </tr>
              <tr v-if="!crmStore.appointments.length">
                <td colspan="6" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" /></svg>
                    </div>
                    <h3 class="text-base font-semibold text-slate-900 mb-1">No appointments booked</h3>
                    <p class="text-sm text-slate-500 max-w-sm">No scheduled customer appointments found for the workshop.</p>
                  </div>
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
import { ref, onMounted, watch } from 'vue';
import { useCustomerStore } from '../../stores/customer';
import { useAuthStore } from '../../stores/auth';
import { useCrmStore } from '../../stores/crm';

const customerStore = useCustomerStore();
const authStore = useAuthStore();
const crmStore = useCrmStore();

const activeTab = ref('directory');
const searchInput = ref(customerStore.filters.search);

const applySearch = () => {
  customerStore.setFilter('search', searchInput.value);
};

const toggleSort = (field) => {
  const newDirection = customerStore.filters.sort_by === field && customerStore.filters.sort_order === 'asc' ? 'desc' : 'asc';
  customerStore.filters.sort_by = field;
  customerStore.setFilter('sort_order', newDirection);
};

const goToPage = (page) => {
  customerStore.fetchCustomers(page);
};

const handleDelete = async (id) => {
  if (confirm('Are you sure you want to delete this customer?')) {
    await customerStore.deleteCustomer(id);
  }
};

const updateAptStatus = async (id, status) => {
  if (confirm("Are you sure you want to mark this appointment as " + status + "?")) {
    await crmStore.updateAppointmentStatus(id, status);
  }
};

// Handle tab switching
watch(activeTab, (newTab) => {
  if (newTab === 'bookings') {
    crmStore.fetchAppointments();
  } else {
    customerStore.fetchCustomers();
  }
});

onMounted(() => {
  if (activeTab.value === 'bookings') {
    crmStore.fetchAppointments();
  } else {
    customerStore.fetchCustomers();
  }
});
</script>
