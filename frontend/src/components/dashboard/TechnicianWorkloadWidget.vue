<template>
  <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-2xl flex flex-col gap-5">
    <div class="flex items-center justify-between border-b border-gray-800 pb-4">
      <div>
        <h2 class="text-lg font-bold text-white">Technician Workloads</h2>
        <p class="text-[11px] text-gray-500 mt-0.5">Technician active queue and workload stress levels</p>
      </div>
      <span class="text-[10px] text-gray-400 font-extrabold uppercase bg-gray-800 border border-gray-700 px-2 py-0.5 rounded-md">
        {{ technicians.length }} techs
      </span>
    </div>

    <!-- Tech Workload Feed -->
    <div class="space-y-4 max-h-[350px] overflow-y-auto pr-1 custom-scrollbar">
      <div 
        v-for="tech in sortedTechnicians" 
        :key="tech.id"
        class="flex flex-col gap-2 p-3.5 bg-gray-950/40 border border-gray-800 rounded-2xl hover:border-gray-750 transition"
      >
        <div class="flex justify-between items-center">
          <!-- Name & Details -->
          <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-rose-500 to-rose-600 text-white font-bold flex items-center justify-center text-xs uppercase shadow-lg">
              {{ getInitials(tech.name) }}
            </div>
            <div class="min-w-0">
              <h3 class="text-xs font-bold text-white truncate max-w-[120px]">{{ tech.name }}</h3>
              <p class="text-[9px] text-gray-500 mt-0.5 truncate">{{ tech.email }}</p>
            </div>
          </div>

          <!-- Active Load Badge -->
          <div class="flex flex-col items-end">
            <span class="text-[10px] font-extrabold" :class="getLoadColorClass(tech.assigned_jobs?.length || 0)">
              {{ tech.assigned_jobs?.length || 0 }} Active Jobs
            </span>
            <span class="text-[9px] text-gray-500 uppercase font-bold tracking-widest mt-0.5">
              {{ getLoadLevel(tech.assigned_jobs?.length || 0) }}
            </span>
          </div>
        </div>

        <!-- Visual Bar Indicator -->
        <div class="mt-1">
          <div class="w-full h-1.5 bg-gray-850 rounded-full overflow-hidden">
            <div 
              class="h-full rounded-full transition-all duration-500"
              :class="getLoadBarClass(tech.assigned_jobs?.length || 0)"
              :style="{ width: `${Math.min((tech.assigned_jobs?.length || 0) * 25, 100)}%` }"
            ></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  technicians: {
    type: Array,
    required: true
  }
});

const sortedTechnicians = computed(() => {
  // Sort technicians by workload (highest load first)
  return [...props.technicians].sort((a, b) => {
    return (b.assigned_jobs?.length || 0) - (a.assigned_jobs?.length || 0);
  });
});

const getInitials = (name) => {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).slice(0, 2).join('');
};

const getLoadLevel = (count) => {
  if (count === 0) return 'Idle';
  if (count <= 2) return 'Normal';
  if (count <= 4) return 'Busy';
  return 'Overloaded';
};

const getLoadColorClass = (count) => {
  if (count === 0) return 'text-gray-400';
  if (count <= 2) return 'text-emerald-400';
  if (count <= 4) return 'text-amber-400';
  return 'text-rose-500';
};

const getLoadBarClass = (count) => {
  if (count === 0) return 'bg-gray-700';
  if (count <= 2) return 'bg-emerald-500';
  if (count <= 4) return 'bg-amber-500';
  return 'bg-rose-600';
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #374151;
  border-radius: 9999px;
}
</style>
