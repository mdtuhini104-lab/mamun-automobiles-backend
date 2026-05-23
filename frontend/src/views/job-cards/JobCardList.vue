<template>
  <div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Service Jobs (Job Cards)</h1>
        <p class="text-sm text-slate-500 mt-1">Manage automotive service workflows, repairs, and diagnostics</p>
      </div>
      <router-link
        :to="{ name: 'job-cards.create' }"
        class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
      >
        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Create Job Card
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
            placeholder="Search Job Cards by ID, Customer, Vehicle..."
          />
        </div>
      </div>
      <div class="w-full sm:w-48">
        <select v-model="statusFilter" @change="fetchJobCards" class="block w-full pl-3 pr-10 py-2 text-base border-slate-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md border">
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
          <option value="delivered">Delivered</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Job Info</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Customer & Vehicle</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Dates</th>
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
            <tr v-else-if="jobCards.length === 0">
              <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                <svg class="mx-auto h-12 w-12 text-slate-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="text-lg font-medium">No service jobs found</p>
                <p class="text-sm mt-1 text-slate-400">Try adjusting your filters or create a new job card.</p>
              </td>
            </tr>
            <tr v-else v-for="job in jobCards" :key="job.id" class="hover:bg-slate-50">
              <td class="px-6 py-4">
                <div class="text-sm font-bold text-indigo-600">JOB-{{ String(job.id).padStart(5, '0') }}</div>
                <div class="text-xs text-slate-500 truncate max-w-xs mt-1" :title="job.complaint">{{ job.complaint || 'No description' }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-semibold text-slate-900">{{ job.customer?.name || 'Unknown' }}</div>
                <div class="text-xs text-slate-500 mt-1">
                  {{ job.vehicle?.make }} {{ job.vehicle?.model }} ({{ job.vehicle?.license_plate }})
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusBadgeClass(job.service_status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                  {{ job.service_status?.replace('_', ' ') || 'Pending' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-xs text-slate-900"><span class="text-slate-500">In:</span> {{ formatDate(job.service_date) }}</div>
                <div class="text-xs text-slate-900 mt-1"><span class="text-slate-500">Out:</span> {{ formatDate(job.delivery_date) || 'TBD' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <router-link :to="{ name: 'job-cards.show', params: { id: job.id } }" class="text-indigo-600 hover:text-indigo-900 mr-4">Manage</router-link>
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
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
              </button>
              <button v-for="page in Math.min(5, meta.last_page)" :key="page" @click="changePage(page)" :class="[meta.current_page === page ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-slate-300 text-slate-500 hover:bg-slate-50', 'relative inline-flex items-center px-4 py-2 border text-sm font-medium']">
                {{ page }}
              </button>
              <button @click="changePage(meta.current_page + 1)" :disabled="meta.current_page === meta.last_page" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-slate-300 bg-white text-sm font-medium text-slate-500 hover:bg-slate-50 disabled:opacity-50">
                <span class="sr-only">Next</span>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
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
const jobCards = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const statusFilter = ref('');
const meta = ref(null);
const currentPage = ref(1);

let debounceTimeout = null;

const fetchJobCards = async () => {
  loading.value = true;
  try {
    // Backend API handles global job cards list? Let's check api.php later. I'll assume /api/v1/job-cards works, wait, the original api.php didn't have global list for job cards either. I will need to add it!
    const response = await api.get('/job-cards', {
      params: {
        page: currentPage.value,
        search: searchQuery.value,
        status: statusFilter.value,
        per_page: 15
      }
    });
    jobCards.value = response.data.data || [];
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
    fetchJobCards();
  }, 300);
};

const changePage = (page) => {
  if (page < 1 || (meta.value && page > meta.value.last_page)) return;
  currentPage.value = page;
  fetchJobCards();
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

const getStatusBadgeClass = (status) => {
  const map = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'in_progress': 'bg-blue-100 text-blue-800',
    'completed': 'bg-green-100 text-green-800',
    'delivered': 'bg-indigo-100 text-indigo-800',
    'cancelled': 'bg-red-100 text-red-800'
  };
  return map[status] || 'bg-slate-100 text-slate-800';
};

onMounted(() => {
  fetchJobCards();
});
</script>
