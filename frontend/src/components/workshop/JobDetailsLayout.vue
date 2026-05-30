<template>
  <div class="space-y-6">
    <!-- Visual Lifecycle Tracker at the very top of every workspace -->
    <WorkflowLifecycleTracker :currentStage="activeStage" :jobCard="jobCard" />

    <!-- Unified Job Info Card Panel -->
    <div class="bg-slate-950/40 border border-slate-800 rounded-3xl p-5 shadow-lg relative overflow-hidden">
      <!-- Backing lights -->
      <div class="absolute -right-20 -top-20 w-60 h-60 bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>

      <div class="flex flex-col xl:flex-row justify-between gap-6 relative">
        <!-- Metadata grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 flex-1">
          <!-- Customer Profile -->
          <div class="space-y-1">
            <span class="text-[9px] font-black uppercase tracking-widest text-slate-500 font-mono">Customer Name</span>
            <div class="text-xs font-extrabold text-white truncate">{{ jobCard?.customer?.name || 'Walk-in Customer' }}</div>
            <div class="text-[10px] text-slate-400 font-mono">{{ jobCard?.customer?.phone || 'No phone logged' }}</div>
          </div>

          <!-- Vehicle Profile -->
          <div class="space-y-1">
            <span class="text-[9px] font-black uppercase tracking-widest text-slate-500 font-mono">Vehicle Number</span>
            <div class="text-xs font-black text-indigo-400 font-mono uppercase">
              {{ jobCard?.vehicle?.license_plate || jobCard?.vehicle?.registration_no || 'Pending Reg' }}
            </div>
            <div class="text-[10px] text-slate-400">
              {{ jobCard?.vehicle?.make }} {{ jobCard?.vehicle?.model }}
            </div>
          </div>

          <!-- Bay Allocation -->
          <div class="space-y-1">
            <span class="text-[9px] font-black uppercase tracking-widest text-slate-500 font-mono">Workshop Bay</span>
            <div class="text-xs font-extrabold flex items-center gap-1.5" :class="jobCard?.workshop_bay?.name ? 'text-emerald-450' : 'text-slate-500'">
              <span class="w-1.5 h-1.5 rounded-full" :class="jobCard?.workshop_bay?.name ? 'bg-emerald-500 animate-pulse' : 'bg-slate-700'"></span>
              {{ jobCard?.workshop_bay?.name || 'Awaiting Allocation' }}
            </div>
            <div class="text-[10px] text-slate-400 font-mono" v-if="jobCard?.workshop_bay?.code">{{ jobCard?.workshop_bay?.code }}</div>
          </div>

          <!-- Technician Allocation -->
          <div class="space-y-1">
            <span class="text-[9px] font-black uppercase tracking-widest text-slate-500 font-mono">Assigned Lead Mechanic</span>
            <div class="text-xs font-extrabold flex items-center gap-1.5" :class="jobCard?.mechanic?.name ? 'text-indigo-400' : 'text-slate-500'">
              <span class="w-1.5 h-1.5 rounded-full" :class="jobCard?.mechanic?.name ? 'bg-indigo-500' : 'bg-slate-700'"></span>
              {{ jobCard?.mechanic?.name || 'Unassigned' }}
            </div>
          </div>
        </div>

        <!-- Next Action / Stage Controls -->
        <div class="flex flex-wrap items-center gap-3 shrink-0 self-center border-t xl:border-t-0 xl:border-l border-slate-800 pt-4 xl:pt-0 xl:pl-6">
          <router-link
            :to="{ name: 'workshop.hub' }"
            class="px-4 py-2 border border-slate-700 hover:bg-slate-900 text-slate-400 hover:text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5"
          >
            ← Operations Hub
          </router-link>

          <router-link
            v-if="nextStepRoute"
            :to="nextStepRoute"
            class="px-5 py-2 bg-gradient-to-r from-indigo-650 to-indigo-550 hover:from-indigo-600 hover:to-indigo-500 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-[0_4px_12px_rgba(99,102,241,0.2)] flex items-center gap-2"
          >
            <span>Proceed to Next Stage</span>
            <span>→</span>
          </router-link>
        </div>
      </div>
    </div>

    <!-- Main Workspace Content Slot -->
    <div class="relative">
      <slot></slot>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import WorkflowLifecycleTracker from './WorkflowLifecycleTracker.vue';

const props = defineProps({
  jobCard: {
    type: Object,
    default: null
  },
  activeStage: {
    type: Number,
    required: true
  }
});

const nextStepRoute = computed(() => {
  if (!props.jobCard?.id) return null;

  switch (props.activeStage) {
    case 1: // Intake -> Inspection
      return { name: 'workshop.inspection', params: { id: props.jobCard.id } };
    case 2: // Inspection -> Quotation
      return { name: 'workshop.quotation', params: { id: props.jobCard.id } };
    case 3: // Quotation -> Approvals
      return { name: 'workshop.approvals' };
    case 4: // Approvals -> Work Order allocation
      return { name: 'workshop.work-orders' };
    case 5: // Work Order -> Parts
      return { name: 'workshop.parts-consumption', params: { id: props.jobCard.id } };
    case 6: // Parts -> QC
      return { name: 'workshop.qc', params: { id: props.jobCard.id } };
    case 7: // QC -> Settlement
      return { name: 'workshop.settlement', params: { id: props.jobCard.id } };
    case 8: // Settlement -> Delivery
      return { name: 'workshop.delivery', params: { id: props.jobCard.id } };
    case 9: // Delivery -> Closed (Hub)
      return { name: 'workshop.hub' };
    default:
      return null;
  }
});
</script>

<style scoped>
.text-emerald-450 {
  color: #34d399;
}
</style>
