<template>
  <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-2xl flex flex-col gap-5">
    <div class="flex items-center justify-between border-b border-gray-800 pb-4">
      <div>
        <h2 class="text-lg font-bold text-white flex items-center gap-2">
          Live Bay Monitoring
          <span class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-extrabold px-2.5 py-0.5 rounded-md uppercase tracking-wider">
            Live
          </span>
        </h2>
        <p class="text-[11px] text-gray-500 mt-0.5">Realtime workshop bay occupancy levels</p>
      </div>
      <div class="flex items-center gap-2 text-xs font-bold text-gray-400">
        <span class="flex items-center gap-1">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
          {{ idleBays }} Idle
        </span>
        <span class="flex items-center gap-1">
          <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
          {{ occupiedBays }} Occupied
        </span>
      </div>
    </div>

    <!-- Bays Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div 
        v-for="bay in bays" 
        :key="bay.id"
        class="p-4 rounded-2xl border transition-all duration-300 flex flex-col gap-3 group"
        :class="getBayCardClass(bay)"
      >
        <!-- Bay Header -->
        <div class="flex justify-between items-center">
          <span class="text-xs font-extrabold text-gray-400 uppercase tracking-widest">
            {{ bay.name }}
          </span>
          <span 
            class="px-2 py-0.5 rounded-lg text-[9px] font-extrabold tracking-wider uppercase border"
            :class="getBayBadgeClass(bay)"
          >
            {{ bay.status }}
          </span>
        </div>

        <!-- Occupied Details -->
        <div v-if="bay.status === 'occupied' && bay.current_job_card" class="flex flex-col gap-2">
          <div>
            <p class="text-xs font-bold text-white group-hover:text-rose-400 transition-colors">
              {{ bay.current_job_card.vehicle?.registration_no || 'DHA-55-9988' }}
            </p>
            <p class="text-[10px] text-gray-500 mt-0.5">
              {{ bay.current_job_card.vehicle?.make }} {{ bay.current_job_card.vehicle?.model }}
            </p>
          </div>

          <!-- Time Indicator -->
          <div class="flex items-center justify-between border-t border-gray-800/80 pt-2 text-[10px] text-gray-500 font-bold mt-1">
            <span>Est. Done:</span>
            <span class="text-gray-300 font-mono">
              {{ formatCompletionTime(bay.current_job_card.created_at) }}
            </span>
          </div>
        </div>

        <!-- Idle Status Placeholder -->
        <div v-else class="py-4 flex flex-col items-center justify-center text-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-gray-700 mb-1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Ready for allocation</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  bays: {
    type: Array,
    required: true
  }
});

const occupiedBays = computed(() => {
  return props.bays.filter(b => b.status === 'occupied').length;
});

const idleBays = computed(() => {
  return props.bays.filter(b => b.status === 'idle').length;
});

const getBayCardClass = (bay) => {
  if (bay.status === 'occupied') {
    return 'bg-gray-950/40 border-gray-800 hover:border-gray-750';
  }
  return 'bg-gray-900 border-gray-800 border-dashed hover:border-gray-700';
};

const getBayBadgeClass = (bay) => {
  if (bay.status === 'occupied') {
    return 'bg-rose-950/40 border-rose-800/30 text-rose-400';
  }
  return 'bg-emerald-950/40 border-emerald-800/30 text-emerald-400';
};

const formatCompletionTime = (createdAt) => {
  if (!createdAt) return '2h remaining';
  // Simulate standard 3-hour service window from creation
  const createdDate = new Date(createdAt);
  const doneDate = new Date(createdDate.getTime() + 3 * 60 * 60 * 1000);
  return doneDate.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
};
</script>

<style scoped>
/* Scoped styles */
</style>
