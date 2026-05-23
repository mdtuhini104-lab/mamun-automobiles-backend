<template>
  <div class="max-w-7xl mx-auto space-y-6">
    <div v-if="loading" class="animate-pulse space-y-6">
      <div class="h-8 bg-slate-200 rounded w-1/4"></div>
      <div class="bg-white shadow-sm rounded-xl border border-slate-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-4">
            <div class="h-4 bg-slate-200 rounded w-1/2"></div>
            <div class="h-4 bg-slate-200 rounded w-1/3"></div>
          </div>
          <div class="space-y-4">
            <div class="h-4 bg-slate-200 rounded w-1/2"></div>
            <div class="h-4 bg-slate-200 rounded w-1/3"></div>
          </div>
        </div>
      </div>
    </div>
    
    <div v-else-if="vehicle" class="space-y-6">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
        <div class="flex items-center space-x-4">
          <router-link :to="{ name: 'vehicles.index' }" class="text-slate-400 hover:text-slate-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
          </router-link>
          <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ vehicle.make }} {{ vehicle.model }} ({{ vehicle.year }})</h1>
            <p class="text-sm text-slate-500 mt-1">License Plate: <span class="font-bold text-slate-700">{{ vehicle.license_plate }}</span></p>
          </div>
        </div>
        <div class="flex space-x-3">
          <router-link
            :to="{ name: 'vehicles.edit', params: { id: vehicle.id } }"
            class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50"
          >
            <svg class="-ml-1 mr-2 h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
          </router-link>
          <button
            @click="deleteVehicle"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700"
          >
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Delete
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Vehicle Information -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
              <h3 class="text-lg leading-6 font-medium text-slate-900">Vehicle Details</h3>
            </div>
            <div class="px-6 py-5">
              <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                <div class="sm:col-span-1">
                  <dt class="text-sm font-medium text-slate-500">License Plate</dt>
                  <dd class="mt-1 text-sm text-slate-900 font-semibold">{{ vehicle.license_plate }}</dd>
                </div>
                <div class="sm:col-span-1">
                  <dt class="text-sm font-medium text-slate-500">Make & Model</dt>
                  <dd class="mt-1 text-sm text-slate-900">{{ vehicle.make }} {{ vehicle.model }}</dd>
                </div>
                <div class="sm:col-span-1">
                  <dt class="text-sm font-medium text-slate-500">Year</dt>
                  <dd class="mt-1 text-sm text-slate-900">{{ vehicle.year || 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-1">
                  <dt class="text-sm font-medium text-slate-500">Color</dt>
                  <dd class="mt-1 text-sm text-slate-900">{{ vehicle.color || 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-1">
                  <dt class="text-sm font-medium text-slate-500">Chassis No. (VIN)</dt>
                  <dd class="mt-1 text-sm text-slate-900">{{ vehicle.vin || 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-1">
                  <dt class="text-sm font-medium text-slate-500">Engine No.</dt>
                  <dd class="mt-1 text-sm text-slate-900">{{ vehicle.engine_number || 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-1">
                  <dt class="text-sm font-medium text-slate-500">Fuel Type</dt>
                  <dd class="mt-1 text-sm text-slate-900">
                    <span class="px-2 py-1 bg-slate-100 text-slate-700 rounded-md text-xs font-semibold">{{ vehicle.fuel_type || 'N/A' }}</span>
                  </dd>
                </div>
                <div class="sm:col-span-1">
                  <dt class="text-sm font-medium text-slate-500">Registered On</dt>
                  <dd class="mt-1 text-sm text-slate-900">{{ formatDate(vehicle.created_at) }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Service History (Job Cards) placeholder -->
          <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
              <h3 class="text-lg leading-6 font-medium text-slate-900">Service History</h3>
            </div>
            <div class="px-6 py-5">
              <div v-if="loadingJobCards" class="animate-pulse space-y-3">
                <div class="h-4 bg-slate-200 rounded w-full"></div>
                <div class="h-4 bg-slate-200 rounded w-full"></div>
              </div>
              <div v-else-if="jobCards.length === 0" class="text-center py-6">
                <p class="text-sm text-slate-500">No service history found for this vehicle.</p>
              </div>
              <ul v-else class="divide-y divide-slate-200">
                <li v-for="job in jobCards" :key="job.id" class="py-4 flex justify-between items-center">
                  <div>
                    <p class="text-sm font-medium text-indigo-600">{{ job.job_number }}</p>
                    <p class="text-xs text-slate-500">{{ formatDate(job.created_at) }}</p>
                  </div>
                  <div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                      {{ job.status }}
                    </span>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Owner Information Sidebar -->
        <div class="lg:col-span-1 space-y-6">
          <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
              <h3 class="text-lg leading-6 font-medium text-slate-900">Owner Details</h3>
            </div>
            <div class="px-6 py-5" v-if="vehicle.customer">
              <div class="flex items-center space-x-3 mb-4">
                <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xl">
                  {{ vehicle.customer.name.charAt(0) }}
                </div>
                <div>
                  <h4 class="text-md font-bold text-slate-900">{{ vehicle.customer.name }}</h4>
                  <router-link :to="{ name: 'customers.show', params: { id: vehicle.customer.id } }" class="text-xs text-indigo-600 hover:text-indigo-800">View Profile</router-link>
                </div>
              </div>
              <dl class="space-y-4">
                <div>
                  <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Phone</dt>
                  <dd class="mt-1 text-sm text-slate-900">{{ vehicle.customer.phone || 'N/A' }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Email</dt>
                  <dd class="mt-1 text-sm text-slate-900">{{ vehicle.customer.email || 'N/A' }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Address</dt>
                  <dd class="mt-1 text-sm text-slate-900">{{ vehicle.customer.address || 'N/A' }}</dd>
                </div>
              </dl>
            </div>
            <div v-else class="px-6 py-5 text-center text-slate-500">
              No owner details available.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';

const route = useRoute();
const router = useRouter();
const toast = useToastStore();

const vehicle = ref(null);
const jobCards = ref([]);
const loading = ref(true);
const loadingJobCards = ref(true);

const fetchVehicle = async () => {
  try {
    const response = await api.get(`/vehicles/${route.params.id}`);
    vehicle.value = response.data.data;
  } catch (error) {
    toast.error('Failed to load vehicle details');
    router.push({ name: 'vehicles.index' });
  } finally {
    loading.value = false;
  }
};

const fetchJobCards = async () => {
  try {
    // Assuming this endpoint exists based on api.php
    const response = await api.get(`/vehicles/${route.params.id}/job-cards`);
    jobCards.value = response.data.data || [];
  } catch (error) {
    console.error('Failed to load service history', error);
  } finally {
    loadingJobCards.value = false;
  }
};

const deleteVehicle = async () => {
  if (!confirm('Are you sure you want to delete this vehicle?')) return;
  
  try {
    await api.delete(`/vehicles/${route.params.id}`);
    toast.success('Vehicle deleted successfully');
    router.push({ name: 'vehicles.index' });
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to delete vehicle');
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

onMounted(() => {
  fetchVehicle();
  fetchJobCards();
});
</script>
