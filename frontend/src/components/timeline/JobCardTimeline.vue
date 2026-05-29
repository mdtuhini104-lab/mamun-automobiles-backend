<template>
  <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-2xl flex flex-col gap-6">
    <div>
      <h3 class="text-base font-bold text-white flex items-center gap-2">
        Operational Timeline Logs
        <span class="bg-blue-500/10 border border-blue-500/20 text-blue-400 text-[10px] font-extrabold px-2 py-0.5 rounded uppercase tracking-wider">
          Audit
        </span>
      </h3>
      <p class="text-xs text-gray-500 mt-0.5">Chronological audit ledger of this vehicle's service lifecycle stages</p>
    </div>

    <!-- Timeline Event Chain -->
    <div v-if="history.length > 0" class="flex flex-col">
      <TimelineEventCard 
        v-for="event in history" 
        :key="event.id" 
        :event="event"
      />
    </div>

    <!-- Empty State -->
    <div v-else class="py-12 text-center flex flex-col items-center justify-center">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-gray-750 mb-3">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <h4 class="text-xs font-bold text-gray-400">Intake log initiated</h4>
      <p class="text-[10px] text-gray-550 mt-1 max-w-xs">Vehicle arrived. Waiting for initial inspection stage transition.</p>
    </div>
  </div>
</template>

<script setup>
import TimelineEventCard from './TimelineEventCard.vue';

defineProps({
  history: {
    type: Array,
    required: true,
    default: () => []
  }
});
</script>

<style scoped>
/* Scoped styles */
</style>
