<template>
  <div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Vehicles</h1>
        <p class="text-sm text-slate-500 mt-1">Manage all registered vehicles in the system</p>
      </div>
      <router-link
        :to="{ name: 'vehicles.create' }"
        class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
      >
        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Vehicle
      </router-link>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-5 flex flex-col sm:flex-row gap-4">
      <div class="flex-1">
        <label class="sr-only">Search</label>
        <div class="relative rounded-md shadow-sm">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input
            v-model="searchQuery"
            @input="debounceSearch"
            type="text"
            class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-slate-300 rounded-md py-2 px-3 border"
            placeholder="Search by Plate, Make, Model, VIN..."
          />
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Vehicle Info</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">License Plate</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Owner</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Added On</th>
              <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-slate-200">
            <tr v-if="loading" v-for="i in 5" :key="'skel'+i" class="animate-pulse">
              <td class="px-6 py-4 whitespace-nowrap"><div class="h-4 bg-slate-200 rounded w-3/4"></div></td>
              <td class="px-6 py-4 whitespace-nowrap"><div class="h-4 bg-slate-200 rounded w-1/2"></div></td>
              <td class="px-6 py-4 whitespace-nowrap"><div class="h-4 bg-slate-200 rounded w-2/3"></div></td>
              <td class="px-6 py-4 whitespace-nowrap"><div class="h-4 bg-slate-200 rounded w-1/3"></div></td>
              <td class="px-6 py-4 whitespace-nowrap"><div class="h-4 bg-slate-200 rounded w-8 ml-auto"></div></td>
            </tr>
            <tr v-else-if="vehicles.length === 0">
              <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                <svg class="mx-auto h-12 w-12 text-slate-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <p class="text-lg font-medium">No vehicles found</p>
                <p class="text-sm mt-1 text-slate-400">Try adjusting your search or add a new vehicle.</p>
              </td>
            </tr>
            <tr v-else v-for="vehicle in vehicles" :key="vehicle.id" class="hover:bg-slate-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-semibold text-slate-900">{{ vehicle.make }} {{ vehicle.model }}</div>
                <div class="text-xs text-slate-500">{{ vehicle.year }} | {{ vehicle.color || 'No color' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-bold bg-slate-100 text-slate-800 border border-slate-200">
                  {{ vehicle.license_plate }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-slate-900">{{ vehicle.customer?.name || 'Unknown' }}</div>
                <div class="text-xs text-slate-500">{{ vehicle.customer?.phone || '' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                {{ formatDate(vehicle.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <router-link :to="{ name: 'vehicles.show', params: { id: vehicle.id } }" class="text-indigo-600 hover:text-indigo-900 mr-4">View</router-link>
                <router-link :to="{ name: 'vehicles.edit', params: { id: vehicle.id } }" class="text-slate-600 hover:text-slate-900">Edit</router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="meta && meta.last_page > 1" class="bg-white px-4 py-3 border-t border-slate-200 flex items-center justify-between sm:px-6">
        <div class="flex-1 flex justify-between sm:hidden">
          <button @click="changePage(meta.current_page - 1)" :disabled="meta.current_page === 1" class="relative inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50 disabled:opacity-50">Previous</button>
          <button @click="changePage(meta.current_page + 1)" :disabled="meta.current_page === meta.last_page" class="ml-3 relative inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50 disabled:opacity-50">Next</button>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-slate-700">
              Showing <span class="font-medium">{{ (meta.current_page - 1) * meta.per_page + 1 }}</span> to <span class="font-medium">{{ Math.min(meta.current_page * meta.per_page, meta.total) }}</span> of <span class="font-medium">{{ meta.total }}</span> results
            </p>
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
              <button @click="changePage(meta.current_page - 1)" :disabled="meta.current_page === 1" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-slate-300 bg-white text-sm font-medium text-slate-500 hover:bg-slate-50 disabled:opacity-50">
                <span class="sr-only">Previous</span>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </button>
              <button v-for="page in Math.min(5, meta.last_page)" :key="page" @click="changePage(page)" :class="[meta.current_page === page ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-slate-300 text-slate-500 hover:bg-slate-50', 'relative inline-flex items-center px-4 py-2 border text-sm font-medium']">
                {{ page }}
              </button>
              <button @click="changePage(meta.current_page + 1)" :disabled="meta.current_page === meta.last_page" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-slate-300 bg-white text-sm font-medium text-slate-500 hover:bg-slate-50 disabled:opacity-50">
                <span class="sr-only">Next</span>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
              </button>
            </nav>
          </div>
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
const vehicles = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const meta = ref(null);
const currentPage = ref(1);

let debounceTimeout = null;

const fetchVehicles = async () => {
  loading.value = true;
  try {
    const response = await api.get('/vehicles', {
      params: {
        page: currentPage.value,
        search: searchQuery.value,
        per_page: 15
      }
    });
    vehicles.value = response.data.data;
    meta.value = response.data.meta;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const debounceSearch = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    currentPage.value = 1;
    fetchVehicles();
  }, 300);
};

const changePage = (page) => {
  if (page < 1 || (meta.value && page > meta.value.last_page)) return;
  currentPage.value = page;
  fetchVehicles();
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

onMounted(() => {
  fetchVehicles();
});
</script>
