<template>
  <div class="relative pl-8 pb-6 last:pb-0 group">
    <!-- Timeline Line Connector -->
    <div class="absolute left-[11px] top-2 bottom-0 w-0.5 bg-gray-800 group-last:hidden"></div>

    <!-- Timeline Dot / Icon -->
    <div 
      class="absolute left-0 top-1.5 w-6 h-6 rounded-full border-2 border-gray-950 flex items-center justify-center shadow-md transition-all duration-300 group-hover:scale-110"
      :class="getIconBgClass(event.to_state)"
    >
      <div class="w-2 h-2 rounded-full bg-white"></div>
    </div>

    <!-- Event details card -->
    <div class="bg-gray-900 border border-gray-850 hover:border-gray-800 rounded-2xl p-4 transition-all duration-200 hover:shadow-xl flex flex-col gap-1.5">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-1">
        <h4 class="text-xs font-black text-white uppercase tracking-wider">
          {{ getFormattedStateName(event.to_state) }}
        </h4>
        <span class="text-[10px] text-gray-500 font-mono">
          {{ formatDateTime(event.created_at) }}
        </span>
      </div>

      <p class="text-xs text-gray-400 leading-relaxed">
        {{ event.notes || 'Status transitioned successfully.' }}
      </p>

      <div class="flex items-center gap-1.5 mt-1 border-t border-gray-850 pt-2 text-[10px] text-gray-500 font-bold">
        <span>Triggered By:</span>
        <span class="text-gray-300">{{ event.user?.name || 'System Auto' }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  event: {
    type: Object,
    required: true
  }
});

const getFormattedStateName = (state) => {
  if (!state) return 'Intake';
  return state.replace(/_/g, ' ');
};

const getIconBgClass = (state) => {
  const s = state ? state.toLowerCase() : '';
  if (s.includes('fail') || s.includes('reject') || s.includes('cancel')) {
    return 'bg-rose-500';
  }
  if (s.includes('complete') || s.includes('approved') || s.includes('closed') || s.includes('delivery')) {
    return 'bg-emerald-500';
  }
  if (s.includes('active') || s.includes('progress') || s.includes('diagnose')) {
    return 'bg-blue-500';
  }
  return 'bg-gray-600';
};

const formatDateTime = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleString(undefined, { 
    month: 'short', 
    day: 'numeric', 
    hour: '2-digit', 
    minute: '2-digit' 
  });
};
</script>

<style scoped>
/* Scoped styles */
</style>
