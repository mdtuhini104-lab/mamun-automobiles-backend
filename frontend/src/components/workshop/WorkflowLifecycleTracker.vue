<template>
  <div class="bg-slate-950/60 backdrop-blur-md border border-slate-800/80 rounded-3xl p-5 shadow-2xl relative overflow-hidden">
    <!-- Glow effects -->
    <div class="absolute -top-10 -left-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative">
      <div class="flex justify-between items-center mb-4">
        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 font-mono">
          Workflow Lifecycle Pipeline
        </span>
        <span class="text-[10px] bg-indigo-500/10 text-indigo-400 font-extrabold px-3 py-0.5 rounded-full border border-indigo-500/20 uppercase">
          Stage {{ activeIndex }} of 10: {{ stages[activeIndex - 1]?.name }}
        </span>
      </div>

      <!-- Stepper Container -->
      <div class="relative flex items-center justify-between mt-6 w-full px-2 overflow-x-auto pb-4 scrollbar-thin">
        
        <!-- Connecting Progress Bar Line -->
        <div class="absolute left-6 right-6 h-0.5 bg-slate-800 top-[18px] -z-10"></div>
        <div 
          class="absolute left-6 h-0.5 bg-gradient-to-r from-indigo-500 to-emerald-500 top-[18px] -z-10 transition-all duration-550"
          :style="{ width: progressBarWidth }"
        ></div>

        <!-- Step items -->
        <div 
          v-for="(stage, idx) in stages" 
          :key="stage.key"
          class="flex flex-col items-center shrink-0 min-w-[70px] text-center group cursor-help"
          :title="stage.description"
        >
          <!-- Circle Bullet -->
          <div 
            class="w-9_5 h-9_5 rounded-full flex items-center justify-center font-mono text-[10px] font-black border-2 transition-all duration-300 relative"
            :class="[
              idx + 1 < activeIndex ? 'bg-gradient-to-br from-indigo-500 to-indigo-650 border-indigo-400 text-white shadow-[0_0_12px_rgba(99,102,241,0.3)]' : '',
              idx + 1 === activeIndex ? 'bg-slate-900 border-indigo-500 text-indigo-400 ring-4 ring-indigo-500/20 shadow-[0_0_20px_rgba(99,102,241,0.5)] animate-pulse' : '',
              idx + 1 > activeIndex ? 'bg-slate-950 border-slate-800 text-slate-650' : ''
            ]"
          >
            <!-- Tick mark for completed steps -->
            <span v-if="idx + 1 < activeIndex">✔</span>
            <!-- Current active index number -->
            <span v-else>{{ idx + 1 }}</span>

            <!-- Aura ring indicator for active stage -->
            <span 
              v-if="idx + 1 === activeIndex"
              class="absolute -inset-1 rounded-full border border-indigo-400/40 animate-ping opacity-60 pointer-events-none"
            ></span>
          </div>

          <!-- Label -->
          <span 
            class="text-[9px] font-extrabold mt-2.5 uppercase tracking-wide transition-colors"
            :class="[
              idx + 1 < activeIndex ? 'text-indigo-350' : '',
              idx + 1 === activeIndex ? 'text-white font-black' : '',
              idx + 1 > activeIndex ? 'text-slate-600' : ''
            ]"
          >
            {{ stage.name }}
          </span>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  currentStage: {
    type: Number,
    default: null
  },
  jobCard: {
    type: Object,
    default: null
  }
});

const stages = [
  { key: 'intake', name: 'Intake', description: 'Customer check-in & complaint logging' },
  { key: 'inspection', name: 'Inspection', description: 'Technician diagnosis & visual scan check-off' },
  { key: 'quotation', name: 'Quotation', description: 'Spare parts pricing & labor estimation' },
  { key: 'approval', name: 'Approval', description: 'Customer approval sign-off' },
  { key: 'work_order', name: 'Work Order', description: 'Bay allocation & workforce delegation' },
  { key: 'execution', name: 'Execution', description: 'Repair execution & active tasks' },
  { key: 'qc', name: 'QC', description: 'Supervisor visual & road test validation' },
  { key: 'invoice', name: 'Invoice', description: 'Compilation of final invoice billing' },
  { key: 'delivery', name: 'Delivery', description: 'Settlement checkout & key release' },
  { key: 'closed', name: 'Closed', description: 'Departed and handover completed' }
];

const activeIndex = computed(() => {
  if (props.currentStage !== null) {
    return Math.max(1, Math.min(10, props.currentStage));
  }

  if (!props.jobCard) return 1;

  const status = props.jobCard.service_status;
  const diag = props.jobCard.diagnosis;

  // Let's deduce index from jobCard state
  if (status === 'delivered') return 10;
  
  if (status === 'completed') {
    // If complete, check if invoice exists and is unpaid
    return 9; // Delivery Handover
  }

  if (status === 'in_progress') {
    return 6; // Execution
  }

  // If pending
  if (status === 'pending') {
    if (!diag) return 1; // Intake
    return 2; // Inspection
  }

  return 1;
});

const progressBarWidth = computed(() => {
  if (activeIndex.value <= 1) return '0%';
  if (activeIndex.value >= 10) return '100%';
  // Steps ranges: 1 to 10. Fraction calculations: (active - 1) / (10 - 1)
  return Math.round(((activeIndex.value - 1) / 9) * 100) + '%';
});
</script>

<style scoped>
/* Custom local modifications */
.w-9_5 {
  width: 2.375rem;
}
.h-9_5 {
  height: 2.375rem;
}
.text-indigo-350 {
  color: #c7d2fe;
}
</style>
