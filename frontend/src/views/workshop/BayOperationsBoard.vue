<template>
  <div class="max-w-7xl mx-auto space-y-6 p-6 bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl text-slate-100 min-h-screen">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-slate-850 pb-5">
      <div class="flex items-center space-x-4">
        <router-link :to="{ name: 'workshop.hub' }" class="text-slate-400 hover:text-slate-200 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
        </router-link>
        <div>
          <h1 class="text-2xl font-black tracking-tight text-white uppercase">Bay Operations Board</h1>
          <p class="text-xs text-slate-400 mt-1">Live workshop bay utilization grid. Reallocate vehicle bays and review stalled bay loads to optimize workshop flow.</p>
        </div>
      </div>
    </div>

    <!-- Stats Grid Panel -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
      <div class="bg-slate-950/40 border border-slate-800 p-4.5 rounded-2xl">
        <span class="text-[9px] font-black uppercase text-slate-500 block tracking-widest font-mono">Busy Bays</span>
        <div class="text-2xl font-black text-indigo-400 font-mono mt-1">{{ busyBaysCount }}</div>
      </div>
      <div class="bg-slate-950/40 border border-slate-800 p-4.5 rounded-2xl">
        <span class="text-[9px] font-black uppercase text-slate-500 block tracking-widest font-mono">Idle Bays</span>
        <div class="text-2xl font-black text-slate-350 font-mono mt-1">{{ idleBaysCount }}</div>
      </div>
      <div class="bg-slate-950/40 border border-slate-800 p-4.5 rounded-2xl">
        <span class="text-[9px] font-black uppercase text-slate-500 block tracking-widest font-mono">Waiting Vehicles</span>
        <div class="text-2xl font-black text-yellow-500 font-mono mt-1">{{ waitingVehiclesCount }}</div>
      </div>
      <div class="bg-slate-950/40 border border-slate-800 p-4.5 rounded-2xl">
        <span class="text-[9px] font-black uppercase text-slate-500 block tracking-widest font-mono">QC Occupied Bays</span>
        <div class="text-2xl font-black text-purple-400 font-mono mt-1">{{ qcBaysCount }}</div>
      </div>
      <div class="bg-slate-950/40 border border-slate-800 p-4.5 rounded-2xl">
        <span class="text-[9px] font-black uppercase text-slate-500 block tracking-widest font-mono">Delayed Vehicles</span>
        <div class="text-2xl font-black text-rose-500 font-mono mt-1">{{ delayedVehiclesCount }}</div>
      </div>
    </div>

    <!-- Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      
      <!-- Left side: Unallocated Job Cards Queue -->
      <div class="lg:col-span-1 bg-slate-950/20 border border-slate-850 rounded-2xl p-4 space-y-4 flex flex-col justify-between h-[600px]">
        <div>
          <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-3 flex items-center justify-between">
            <span>Awaiting Bay Allocation</span>
            <span class="bg-indigo-500/10 text-indigo-400 text-[10px] px-2 py-0.5 rounded-full">
              {{ unallocatedJobs.length }}
            </span>
          </h3>

          <div v-if="loading" class="flex justify-center py-12">
            <div class="w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
          </div>

          <div v-else-if="unallocatedJobs.length === 0" class="text-center py-16 text-slate-550 text-xs italic">
            All active vehicles are currently allocated to bays.
          </div>

          <div v-else class="space-y-3 overflow-y-auto max-h-[480px] pr-1">
            <div 
              v-for="job in unallocatedJobs" 
              :key="job.id"
              class="bg-slate-900 border border-slate-800 rounded-xl p-3.5 space-y-3 hover:border-slate-700 transition flex flex-col justify-between"
            >
              <div>
                <span class="text-[9px] font-mono text-slate-500">#JC-{{ String(job.id).padStart(5, '0') }}</span>
                <h4 class="font-bold text-white text-xs mt-1">{{ job.vehicle?.make }} {{ job.vehicle?.model }}</h4>
                <p class="text-[9px] text-slate-450 font-mono mt-0.5">Plate: {{ job.vehicle?.license_plate || job.vehicle?.registration_no }}</p>
                <p class="text-[10px] text-rose-400 italic mt-2">"{{ job.complaint }}"</p>
              </div>

              <!-- Bay select dropdown -->
              <div class="pt-2 border-t border-slate-850 flex gap-2">
                <select 
                  v-model="job.tempBayId"
                  class="flex-1 bg-slate-850 border border-slate-750 rounded p-1 text-[10px] text-slate-200"
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
                  class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded text-[10px] font-black uppercase tracking-wider"
                >
                  Set
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right side: Visual Bay Grid -->
      <div class="lg:col-span-3 bg-slate-950/20 border border-slate-850 rounded-3xl p-5 shadow-xl min-h-[500px]">
        <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-4">Workshop Bay Status Grid</h3>

        <div v-if="loading" class="flex justify-center py-20">
          <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
          <div 
            v-for="bay in baysList" 
            :key="bay.id"
            :class="getBayOccupancyClass(bay)"
            class="border rounded-2xl p-5 bg-slate-900/40 flex flex-col justify-between gap-5 transition hover:shadow-md"
          >
            <div>
              <!-- Title -->
              <div class="flex justify-between items-start">
                <div>
                  <h4 class="text-sm font-black text-white">{{ bay.name }}</h4>
                  <span class="text-[9px] text-slate-500 font-mono">{{ bay.code }}</span>
                </div>
                <span :class="getUtilizationBadgeClass(bay)" class="px-2 py-0.5 rounded text-[9px] font-black uppercase font-mono tracking-wider">
                  {{ bay.current_load }} / {{ bay.max_vehicle_capacity }} vehicles
                </span>
              </div>

              <!-- Utilization Progress Bar -->
              <div class="w-full bg-slate-850 rounded-full h-1.5 mt-3">
                <div 
                  :class="getProgressBarClass(bay)"
                  class="h-1.5 rounded-full transition-all duration-300"
                  :style="{ width: getUtilizationPercentage(bay) + '%' }"
                ></div>
              </div>

              <!-- Vehicles list inside this bay -->
              <div class="mt-4 space-y-2 border-t border-slate-850/60 pt-4">
                <span class="text-[9px] font-black uppercase tracking-wider text-slate-500 block">Occupying Vehicles</span>
                <div v-if="getVehiclesInBay(bay.id).length === 0" class="text-[10px] text-slate-500 italic py-1">Bay is currently vacant.</div>
                <div 
                  v-else
                  v-for="v in getVehiclesInBay(bay.id)" 
                  :key="v.id"
                  class="bg-slate-950/40 border border-slate-850 p-2.5 rounded-lg text-xs flex justify-between items-center text-slate-350"
                >
                  <div>
                    <span class="font-bold text-white block">{{ v.vehicle?.make }} {{ v.vehicle?.model }}</span>
                    <span class="text-[9px] text-slate-450 font-mono">Plate: {{ v.vehicle?.license_plate || v.vehicle?.registration_no }}</span>
                  </div>
                  <button 
                    @click="releaseBay(v.id)"
                    class="text-rose-400 hover:text-rose-350 text-[9px] uppercase font-black tracking-wide"
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
  if (pct >= 100) return 'border-red-900/60 bg-red-950/5';
  if (pct >= 75) return 'border-amber-900/60 bg-amber-950/5';
  return 'border-slate-800/80 bg-slate-900/10';
};

const getUtilizationBadgeClass = (bay) => {
  const pct = getUtilizationPercentage(bay);
  if (pct >= 100) return 'bg-rose-500/10 text-rose-400 border border-rose-500/20';
  if (pct >= 75) return 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
  return 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
};

const getProgressBarClass = (bay) => {
  const pct = getUtilizationPercentage(bay);
  if (pct >= 100) return 'bg-rose-500';
  if (pct >= 75) return 'bg-amber-500';
  return 'bg-indigo-500';
};

onMounted(() => {
  fetchData();
});
</script>
