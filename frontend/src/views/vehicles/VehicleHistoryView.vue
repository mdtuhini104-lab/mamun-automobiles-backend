<template>
  <div class="space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Vehicle Service History</h1>
        <p class="text-sm text-slate-500 mt-1">Track vehicle repairs, maintenance schedules, and job cards.</p>
      </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/60 shadow-sm flex flex-col sm:flex-row gap-4">
      <div class="relative flex-1">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
        <input v-model="searchQuery" type="text" placeholder="Search by Registration No, Make, or Model..." class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors">
      </div>
    </div>

    <!-- Data Grid -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 6" :key="i" class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm animate-pulse h-40">
         <div class="flex justify-between items-start">
           <div class="w-2/3 h-5 bg-slate-200 rounded"></div>
           <div class="w-10 h-6 bg-slate-200 rounded-full"></div>
         </div>
         <div class="mt-4 w-1/2 h-4 bg-slate-200 rounded"></div>
         <div class="mt-2 w-3/4 h-4 bg-slate-200 rounded"></div>
      </div>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-if="filteredVehicles.length === 0" class="col-span-full bg-white p-12 rounded-2xl border border-slate-200/60 text-center">
        <svg class="h-12 w-12 text-slate-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        <h3 class="text-lg font-medium text-slate-900">No vehicles found</h3>
        <p class="mt-1 text-sm text-slate-500">Try a different search query.</p>
      </div>

      <div v-for="vehicle in filteredVehicles" :key="vehicle.id" class="bg-white rounded-2xl border border-slate-200/60 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col">
        <div class="p-6 border-b border-slate-100 flex-1">
          <div class="flex justify-between items-start mb-4">
            <div>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-slate-100 text-slate-800 tracking-wider font-mono border border-slate-200">
                {{ vehicle.registration_number }}
              </span>
            </div>
            <div class="bg-indigo-50 text-indigo-600 p-2 rounded-xl">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
            </div>
          </div>
          <h3 class="text-lg font-bold text-slate-900">{{ vehicle.make }} {{ vehicle.model }}</h3>
          <p class="text-sm text-slate-500 mt-1 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            {{ vehicle.customer_name }}
          </p>
          <div class="mt-4 flex items-center gap-2 text-xs font-semibold text-slate-600 bg-slate-50 p-2 rounded-lg border border-slate-100">
            <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            Last Visit: {{ formatDate(vehicle.last_visit) }}
          </div>
        </div>
        <div class="p-4 bg-slate-50/80 border-t border-slate-100 flex justify-end">
          <router-link :to="`/vehicles/history/${vehicle.id}`" class="text-sm font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 bg-white border border-indigo-100 px-3 py-1.5 rounded-lg shadow-sm transition-colors hover:border-indigo-300">
            View Full History <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const loading = ref(true);
const vehicles = ref([]);
const searchQuery = ref('');

const fetchVehicles = async () => {
  loading.value = true;
  try {
    // Mock data
    await new Promise(resolve => setTimeout(resolve, 500));
    vehicles.value = [
      { id: 1, registration_number: 'XYZ-1234', make: 'Toyota', model: 'Corolla', customer_name: 'John Doe', last_visit: '2023-10-15' },
      { id: 2, registration_number: 'ABC-9876', make: 'Honda', model: 'Civic', customer_name: 'Jane Smith', last_visit: '2023-09-20' },
      { id: 3, registration_number: 'DEF-5555', make: 'Ford', model: 'Mustang', customer_name: 'Acme Corp', last_visit: '2023-10-01' },
    ];
  } catch (error) {
    console.error('Error fetching vehicles:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => fetchVehicles());

const filteredVehicles = computed(() => {
  const query = searchQuery.value.toLowerCase();
  return vehicles.value.filter(v => 
    v.registration_number.toLowerCase().includes(query) ||
    v.make.toLowerCase().includes(query) ||
    v.model.toLowerCase().includes(query)
  );
});

const formatDate = (dateStr) => new Date(dateStr).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
</script>
