<template>
  <div class="max-w-7xl mx-auto space-y-6 p-6 bg-slate-50 border border-slate-200 rounded-3xl shadow-sm text-slate-800 min-h-screen">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-200 pb-5">
      <div class="flex items-center space-x-4">
        <router-link :to="{ name: 'workshop.hub' }" class="text-slate-500 hover:text-slate-700 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
        </router-link>
        <div>
          <h1 class="text-2xl font-black tracking-tight text-slate-800 uppercase">Bay Operations Board</h1>
          <p class="text-xs text-slate-500 mt-1">Live workshop bay utilization grid. Reallocate vehicle bays and review stalled bay loads to optimize workshop flow.</p>
        </div>
      </div>
    </div>

    <!-- Stats Grid Panel -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
      <div class="bg-white border border-slate-200 p-4.5 rounded-2xl shadow-sm">
        <span class="text-[9px] font-black uppercase text-slate-400 block tracking-widest font-mono">Busy Bays</span>
        <div class="text-2xl font-black text-indigo-600 font-mono mt-1">{{ busyBaysCount }}</div>
      </div>
      <div class="bg-white border border-slate-200 p-4.5 rounded-2xl shadow-sm">
        <span class="text-[9px] font-black uppercase text-slate-400 block tracking-widest font-mono">Idle Bays</span>
        <div class="text-2xl font-black text-slate-500 font-mono mt-1">{{ idleBaysCount }}</div>
      </div>
      <div class="bg-white border border-slate-200 p-4.5 rounded-2xl shadow-sm">
        <span class="text-[9px] font-black uppercase text-slate-400 block tracking-widest font-mono">Waiting Vehicles</span>
        <div class="text-2xl font-black text-amber-600 font-mono mt-1">{{ waitingVehiclesCount }}</div>
      </div>
      <div class="bg-white border border-slate-200 p-4.5 rounded-2xl shadow-sm">
        <span class="text-[9px] font-black uppercase text-slate-400 block tracking-widest font-mono">QC Occupied Bays</span>
        <div class="text-2xl font-black text-purple-600 font-mono mt-1">{{ qcBaysCount }}</div>
      </div>
      <div class="bg-white border border-slate-200 p-4.5 rounded-2xl shadow-sm">
        <span class="text-[9px] font-black uppercase text-slate-400 block tracking-widest font-mono">Delayed Vehicles</span>
        <div class="text-2xl font-black text-rose-600 font-mono mt-1">{{ delayedVehiclesCount }}</div>
      </div>
    </div>

    <!-- Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      
      <!-- Left side: Unallocated Job Cards Queue -->
      <div class="lg:col-span-1 bg-white border border-slate-200 rounded-2xl p-4 space-y-4 flex flex-col justify-between h-[600px] shadow-sm">
        <div>
          <h3 class="text-xs font-black uppercase tracking-wider text-slate-500 mb-3 flex items-center justify-between">
            <span>Awaiting Bay Allocation</span>
            <span class="bg-indigo-50 text-indigo-600 border border-indigo-100 text-[10px] px-2 py-0.5 rounded-full font-bold">
              {{ unallocatedJobs.length }}
            </span>
          </h3>

          <div v-if="loading" class="flex justify-center py-12">
            <div class="w-6 h-6 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
          </div>

          <div v-else-if="unallocatedJobs.length === 0" class="text-center py-16 text-slate-400 text-xs italic">
            All active vehicles are currently allocated to bays.
          </div>

          <div v-else class="space-y-3 overflow-y-auto max-h-[480px] pr-1">
            <div 
              v-for="job in unallocatedJobs" 
              :key="job.id"
              class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 space-y-3 hover:border-indigo-300 hover:shadow-sm transition flex flex-col justify-between"
            >
              <div>
                <span class="text-[9px] font-mono text-slate-400">#JC-{{ String(job.id).padStart(5, '0') }}</span>
                <h4 class="font-bold text-slate-800 text-xs mt-1">{{ job.vehicle?.make }} {{ job.vehicle?.model }}</h4>
                <p class="text-[9px] text-slate-500 font-mono mt-0.5">Plate: {{ job.vehicle?.license_plate || job.vehicle?.registration_no }}</p>
                <p class="text-[10px] text-rose-600 italic mt-2">"{{ job.complaint }}"</p>
              </div>

              <!-- Bay select dropdown -->
              <div class="pt-2 border-t border-slate-200 flex gap-2">
                <select 
                  v-model="job.tempBayId"
                  class="flex-1 bg-white border border-slate-200 rounded p-1 text-[10px] text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="" disabled>Select Bay...</option>
                  <option 
                    v-for="b in baysList" 
                    :key="b.id" 
                    :value="b.id"
                    :disabled="b.current_load >= b.max_vehicle_capacity"
                  >
                    {{ b.name }} ({{ b.current_load }}/{{ b.max_vehicle_capacity }})
                  </option>
                </select>
                <button 
                  @click="allocateBay(job.id, job.tempBayId)"
                  :disabled="!job.tempBayId"
                  class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded text-[10px] font-black uppercase tracking-wider transition"
                >
                  Set
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right side: Visual Bay Grid -->
      <div class="lg:col-span-3 bg-white border border-slate-200 rounded-3xl p-5 shadow-sm min-h-[500px]">
        <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 mb-4">Workshop Bay Status Grid</h3>

        <div v-if="loading" class="flex justify-center py-20">
          <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
          <div 
            v-for="bay in baysList" 
            :key="bay.id"
            :class="getBayOccupancyClass(bay)"
            class="border rounded-2xl p-5 flex flex-col justify-between gap-5 transition hover:shadow-md"
          >
            <div>
              <!-- Title -->
              <div class="flex justify-between items-start">
                <div>
                  <h4 class="text-sm font-black text-slate-800">{{ bay.name }}</h4>
                  <span class="text-[9px] text-slate-400 font-mono">{{ bay.code }}</span>
                </div>
                <span :class="getUtilizationBadgeClass(bay)" class="px-2 py-0.5 rounded text-[9px] font-black uppercase font-mono tracking-wider">
                  {{ bay.current_load }} / {{ bay.max_vehicle_capacity }} vehicles
                </span>
              </div>

              <!-- Utilization Progress Bar -->
              <div class="w-full bg-slate-200 rounded-full h-1.5 mt-3">
                <div 
                  :class="getProgressBarClass(bay)"
                  class="h-1.5 rounded-full transition-all duration-300"
                  :style="{ width: getUtilizationPercentage(bay) + '%' }"
                ></div>
              </div>

              <!-- Vehicles list inside this bay -->
              <div class="mt-4 space-y-2 border-t border-slate-200 pt-4">
                <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block">Occupying Vehicles</span>
                <div v-if="getVehiclesInBay(bay.id).length === 0" class="text-[10px] text-slate-400 italic py-1">Bay is currently vacant.</div>
                <div 
                  v-else
                  v-for="v in getVehiclesInBay(bay.id)" 
                  :key="v.id"
                  class="bg-slate-50 border border-slate-200 p-2.5 rounded-lg text-xs flex justify-between items-center text-slate-600"
                >
                  <div>
                    <span class="font-bold text-slate-800 block">{{ v.vehicle?.make }} {{ v.vehicle?.model }}</span>
                    <span class="text-[9px] text-slate-500 font-mono">Plate: {{ v.vehicle?.license_plate || v.vehicle?.registration_no }}</span>
                  </div>
                  <button 
                    @click="releaseBay(v.id)"
                    class="text-rose-600 hover:text-rose-700 text-[9px] uppercase font-black tracking-wide"
                  >
                    Release
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';

const toast = useToastStore();
const loading = ref(true);

const baysList = ref([]);
const activeJobsList = ref([]);

const busyBaysCount = computed(() => {
  return baysList.value.filter(b => b.current_load > 0).length;
});

const idleBaysCount = computed(() => {
  return baysList.value.filter(b => b.current_load === 0).length;
});

const waitingVehiclesCount = computed(() => {
  return unallocatedJobs.value.length;
});

const qcBaysCount = computed(() => {
  return baysList.value.filter(b => {
    const jobs = getVehiclesInBay(b.id);
    return jobs.some(j => j.service_status === 'qc' || j.service_status === 'completed');
  }).length;
});

const delayedVehiclesCount = computed(() => {
  return activeJobsList.value.filter(j => j.priority_level === 'critical' || j.priority_level === 'urgent' || j.priority_level === 'high').length;
});

const fetchData = async () => {
  loading.value = true;
  try {
    const [bayRes, jcRes] = await Promise.all([
      api.get('/workshop-bays'),
      api.get('/job-cards', { params: { per_page: 200 } })
    ]);

    baysList.value = bayRes.data?.data || bayRes.data || [];
    
    const rawJobs = jcRes.data?.data || jcRes.data || [];
    // Only map active/in-progress jobs
    activeJobsList.value = rawJobs.filter(
      jc => jc.service_status === 'pending' || jc.service_status === 'in_progress'
    ).map(jc => ({
      ...jc,
      tempBayId: ''
    }));

  } catch (err) {
    console.error('Failed to sync bays details', err);
    toast.error('Bays operations data synchronization failed.');
  } finally {
    loading.value = false;
  }
};

const unallocatedJobs = computed(() => {
  return activeJobsList.value.filter(j => !j.workshop_bay_id);
});

const getVehiclesInBay = (bayId) => {
  return activeJobsList.value.filter(j => j.workshop_bay_id === bayId);
};

const allocateBay = async (jobCardId, bayId) => {
  try {
    await api.post(`/job-cards/${jobCardId}/assign`, { workshop_bay_id: bayId });
    toast.success('Vehicle allocated to bay successfully.');
    fetchData();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Allocation failed.');
  }
};

const releaseBay = async (jobCardId) => {
  try {
    // Release is done by assigning bay id to null or empty
    await api.post(`/job-cards/${jobCardId}/assign`, { workshop_bay_id: null });
    toast.success('Vehicle released from bay.');
    fetchData();
  } catch (err) {
    toast.error('Release failed.');
  }
};

// Styling calculations
const getUtilizationPercentage = (bay) => {
  if (!bay || !bay.max_vehicle_capacity) return 0;
  return Math.min(100, Math.round((bay.current_load / bay.max_vehicle_capacity) * 100));
};

const getBayOccupancyClass = (bay) => {
  const pct = getUtilizationPercentage(bay);
  if (pct >= 100) return 'border-rose-200 bg-rose-50/30';
  if (pct >= 75) return 'border-amber-200 bg-amber-50/30';
  return 'border-slate-200 bg-slate-50/30';
};

const getUtilizationBadgeClass = (bay) => {
  const pct = getUtilizationPercentage(bay);
  if (pct >= 100) return 'bg-rose-50 text-rose-600 border border-rose-200';
  if (pct >= 75) return 'bg-amber-50 text-amber-600 border border-amber-200';
  return 'bg-emerald-50 text-emerald-600 border border-emerald-250';
};

const getProgressBarClass = (bay) => {
  const pct = getUtilizationPercentage(bay);
  if (pct >= 100) return 'bg-rose-500';
  if (pct >= 75) return 'bg-amber-500';
  return 'bg-indigo-650';
};

onMounted(() => {
  fetchData();
});
</script>
