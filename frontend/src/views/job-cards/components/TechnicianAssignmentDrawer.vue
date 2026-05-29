<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex justify-end bg-black/60 backdrop-blur-sm">
    <!-- Sliding Content Drawer -->
    <div class="bg-gray-900 border-l border-gray-800 w-full max-w-md shadow-2xl h-full flex flex-col animate-slide-in">
      <!-- Drawer Header -->
      <div class="flex items-center justify-between bg-gray-950 border-b border-gray-800 px-5 py-4">
        <div>
          <h3 class="text-base font-bold text-white flex items-center gap-2">
            Assign Staff
            <span class="bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-[10px] font-extrabold px-2 py-0.5 rounded uppercase tracking-wider">
              Workforce
            </span>
          </h3>
          <p class="text-[10px] text-gray-500 mt-0.5">Quick technician workload allocation drawer</p>
        </div>
        <button @click="close" class="p-2 text-gray-400 hover:text-gray-200 focus:outline-none">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Drawer Body -->
      <div class="p-5 overflow-y-auto flex-1 divide-y divide-gray-850 space-y-4">
        <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-3">
          <div class="w-8 h-8 border-3 border-rose-500 border-t-transparent rounded-full animate-spin"></div>
          <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Querying Staff...</p>
        </div>

        <div v-else-if="technicians.length === 0" class="py-20 text-center text-gray-500 text-xs">
          No active technicians available for allocation.
        </div>

        <div v-else class="space-y-3 pt-4">
          <div 
            v-for="tech in technicians" 
            :key="tech.id"
            @click="selectTech(tech)"
            class="flex items-center justify-between p-4 bg-gray-950/60 border border-gray-800 rounded-2xl cursor-pointer hover:border-indigo-500/30 hover:bg-indigo-950/10 active:scale-98 transition duration-200 group"
          >
            <!-- Details -->
            <div class="flex items-center gap-3 min-w-0">
              <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 text-white font-extrabold flex items-center justify-center text-xs uppercase shadow-md">
                {{ getInitials(tech.name) }}
              </div>
              <div class="min-w-0">
                <h4 class="text-xs font-bold text-white group-hover:text-indigo-400 transition-colors">{{ tech.name }}</h4>
                <p class="text-[9px] text-gray-500 mt-0.5 truncate">{{ tech.email }}</p>
              </div>
            </div>

            <!-- Workload status badge -->
            <div class="flex flex-col items-end">
              <span class="text-[9px] font-extrabold uppercase tracking-widest px-2 py-0.5 rounded border" :class="getLoadBadgeClass(tech.workload)">
                {{ tech.workload || 'Idle' }}
              </span>
              <span class="text-[9px] text-gray-500 mt-1">
                {{ tech.active_jobs_count || 0 }} active jobs
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Footer -->
      <div class="border-t border-gray-800 bg-gray-950/80 px-5 py-4">
        <button 
          @click="close" 
          class="w-full py-3 bg-gray-800 hover:bg-gray-750 text-gray-400 rounded-2xl font-extrabold text-xs transition focus:outline-none"
        >
          Close Drawer
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../../services/api';

const props = defineProps({
  isOpen: Boolean
});

const emit = defineEmits(['close', 'selected']);

const loading = ref(false);
const technicians = ref([]);

const close = () => {
  emit('close');
};

const getInitials = (name) => {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).slice(0, 2).join('');
};

const selectTech = (tech) => {
  emit('selected', tech);
  close();
};

const fetchTechnicians = async () => {
  loading.value = true;
  try {
    const response = await api.get('/workforce/employees', {
      params: { role: 'Technician' }
    });
    // Dynamically query workload mapping if available
    technicians.value = (response.data.data || response.data || []).map(tech => ({
      ...tech,
      name: tech.first_name ? `${tech.first_name} ${tech.last_name}` : tech.name,
      workload: tech.active_jobs_count > 3 ? 'busy' : (tech.active_jobs_count > 0 ? 'active' : 'idle'),
      active_jobs_count: tech.active_jobs_count ?? 0
    }));
  } catch (error) {
    console.error('Failed to fetch technicians', error);
  } finally {
    loading.value = false;
  }
};

const getLoadBadgeClass = (load) => {
  switch (load) {
    case 'busy':
      return 'bg-rose-950/40 border-rose-800/30 text-rose-400';
    case 'active':
      return 'bg-blue-950/40 border-blue-800/30 text-blue-400';
    default:
      return 'bg-emerald-950/40 border-emerald-800/30 text-emerald-400';
  }
};

onMounted(() => {
  fetchTechnicians();
});
</script>

<style scoped>
.animate-slide-in {
  animation: slideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
  }
  to {
    transform: translateX(0);
  }
}
</style>
