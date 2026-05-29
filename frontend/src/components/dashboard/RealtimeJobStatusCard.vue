<template>
  <div 
    class="relative bg-gray-900 border border-gray-800 hover:border-gray-700 rounded-2xl p-5 shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden flex flex-col gap-4 group"
    :class="{ 'ring-1 ring-rose-500/20 border-rose-500/30': isOverdue }"
  >
    <!-- Card header -->
    <div class="flex justify-between items-start">
      <div>
        <span class="text-[10px] font-mono font-bold tracking-widest text-gray-500">
          {{ workOrder.work_order_no }}
        </span>
        <h3 class="text-base font-extrabold text-white mt-1 group-hover:text-rose-400 transition-colors">
          {{ workOrder.job_card?.vehicle?.registration_no || 'Unknown Vehicle' }}
        </h3>
        <p class="text-[11px] text-gray-400">
          {{ workOrder.job_card?.vehicle?.make }} {{ workOrder.job_card?.vehicle?.model }}
        </p>
      </div>
      <span 
        class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold tracking-wider uppercase"
        :class="getStatusClass(workOrder.status)"
      >
        {{ workOrder.status }}
      </span>
    </div>

    <!-- Progress Indicator -->
    <div>
      <div class="flex justify-between items-center text-[10px] text-gray-400 mb-1.5">
        <span class="font-bold">Execution Progress</span>
        <span class="font-bold text-gray-200">{{ Math.round(progressPercentage) }}%</span>
      </div>
      <div class="w-full h-1.5 bg-gray-800 rounded-full overflow-hidden">
        <div 
          class="h-full bg-gradient-to-r from-rose-500 to-rose-400 transition-all duration-500 rounded-full"
          :style="{ width: `${progressPercentage}%` }"
        ></div>
      </div>
    </div>

    <!-- Tasks breakdown snippet -->
    <div v-if="tasks.length > 0" class="space-y-2 border-t border-gray-800/60 pt-3">
      <div 
        v-for="task in tasks.slice(0, 2)" 
        :key="task.id"
        class="flex items-center justify-between text-xs"
      >
        <span class="text-gray-400 flex items-center gap-1.5 truncate">
          <span 
            class="w-1.5 h-1.5 rounded-full"
            :class="task.status === 'completed' ? 'bg-emerald-500' : (task.status === 'in_progress' ? 'bg-amber-500' : 'bg-gray-600')"
          ></span>
          {{ task.task_name }}
        </span>
        <span class="text-[10px] font-mono" :class="task.actual_minutes > task.estimated_minutes ? 'text-rose-400 font-bold' : 'text-gray-500'">
          {{ task.actual_minutes || 0 }}/{{ task.estimated_minutes }}m
        </span>
      </div>
      <p v-if="tasks.length > 2" class="text-[10px] text-gray-500 text-right">
        +{{ tasks.length - 2 }} more task(s)
      </p>
    </div>

    <!-- Card Footer -->
    <div class="mt-auto pt-3 border-t border-gray-800/80 flex items-center justify-between">
      <!-- Assigned Technician -->
      <div class="flex items-center gap-2">
        <div class="w-7 h-7 rounded-lg bg-gray-850 border border-gray-700 flex items-center justify-center text-xs font-bold text-gray-300 uppercase">
          {{ technicianInitials }}
        </div>
        <div class="min-w-0">
          <p class="text-[10px] text-gray-500 leading-none">Technician</p>
          <p class="text-[11px] font-bold text-gray-300 truncate mt-0.5 max-w-[120px]">
            {{ technicianName }}
          </p>
        </div>
      </div>

      <!-- Live timer / overdue warnings -->
      <div class="flex items-center gap-1" v-if="isOverdue">
        <span class="flex h-2 w-2 relative">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
        </span>
        <span class="text-[10px] font-extrabold text-rose-400 uppercase tracking-wider">
          Delayed
        </span>
      </div>
      <div class="text-[10px] text-gray-500 font-bold" v-else>
        On Schedule
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  workOrder: {
    type: Object,
    required: true
  }
});

const tasks = computed(() => {
  return props.workOrder.job_card?.tasks || [];
});

const progressPercentage = computed(() => {
  if (tasks.value.length === 0) return 0;
  const completed = tasks.value.filter(t => t.status === 'completed').length;
  return (completed / tasks.value.length) * 100;
});

const isOverdue = computed(() => {
  return tasks.value.some(t => t.status === 'in_progress' && t.actual_minutes > t.estimated_minutes);
});

const technicianName = computed(() => {
  // Try to find assigned tech in work order tasks assignments
  const firstTask = tasks.value.find(t => t.task_assignments && t.task_assignments.length > 0);
  if (firstTask && firstTask.task_assignments[0].employee) {
    return firstTask.task_assignments[0].employee.name;
  }
  return 'Unassigned';
});

const technicianInitials = computed(() => {
  const name = technicianName.value;
  if (name === 'Unassigned') return '?';
  return name.split(' ').map(n => n[0]).slice(0, 2).join('');
});

const getStatusClass = (status) => {
  switch (status.toLowerCase()) {
    case 'completed':
      return 'bg-emerald-950/40 border border-emerald-800/30 text-emerald-400';
    case 'in_progress':
      return 'bg-blue-950/40 border border-blue-800/30 text-blue-400';
    case 'paused':
      return 'bg-amber-950/40 border border-amber-800/30 text-amber-400';
    default:
      return 'bg-gray-800 border border-gray-700 text-gray-400';
  }
};
</script>

<style scoped>
/* Scoped styles */
</style>
