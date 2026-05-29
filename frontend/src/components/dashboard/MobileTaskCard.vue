<template>
  <div 
    class="bg-gray-900 border border-gray-800 hover:border-gray-700 rounded-3xl p-5 shadow-lg transition-all duration-300 flex flex-col gap-4"
    :class="{ 'ring-1 ring-rose-500/25 border-rose-500/30': isDelayed }"
  >
    <!-- Job details -->
    <div class="flex justify-between items-start">
      <div>
        <span class="text-[10px] font-mono font-bold tracking-widest text-gray-500">
          Task #{{ task.id }}
        </span>
        <h3 class="text-sm font-extrabold text-white mt-0.5">
          {{ task.task_name || 'Standard Repair' }}
        </h3>
        <p class="text-[11px] text-gray-400 mt-1">
          Vehicle: {{ task.work_order?.job_card?.vehicle?.registration_no || 'DHA-55-9988' }}
        </p>
      </div>
      <span 
        class="px-2.5 py-1 rounded-lg text-[9px] font-extrabold tracking-wider uppercase"
        :class="getStatusClass(task.status)"
      >
        {{ task.status }}
      </span>
    </div>

    <!-- Duration comparison -->
    <div class="bg-gray-950 p-3.5 rounded-2xl flex items-center justify-between border border-gray-850">
      <div class="text-center flex-1">
        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Estimated</p>
        <p class="text-sm font-extrabold text-gray-300 mt-0.5">{{ task.estimated_minutes }}m</p>
      </div>
      <div class="h-6 w-px bg-gray-800"></div>
      <div class="text-center flex-1">
        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Actual</p>
        <p 
          class="text-sm font-extrabold mt-0.5"
          :class="task.actual_minutes > task.estimated_minutes ? 'text-rose-400' : 'text-emerald-400'"
        >
          {{ task.actual_minutes || 0 }}m
        </p>
      </div>
    </div>

    <!-- Interactive action buttons -->
    <div class="grid grid-cols-3 gap-2">
      <button 
        v-if="task.status !== 'in_progress'"
        @click="updateStatus('in_progress')" 
        :disabled="updating"
        class="py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-2xl font-extrabold text-xs shadow-md active:scale-95 transition-all flex items-center justify-center gap-1 focus:outline-none"
      >
        Start
      </button>
      
      <button 
        v-if="task.status === 'in_progress'"
        @click="updateStatus('paused')" 
        :disabled="updating"
        class="py-3 bg-amber-600 hover:bg-amber-500 text-white rounded-2xl font-extrabold text-xs shadow-md active:scale-95 transition-all flex items-center justify-center gap-1 focus:outline-none"
      >
        Pause
      </button>

      <button 
        v-if="task.status !== 'completed'"
        @click="updateStatus('completed')" 
        :disabled="updating"
        class="py-3 bg-rose-600 hover:bg-rose-500 text-white rounded-2xl font-extrabold text-xs shadow-md active:scale-95 transition-all flex items-center justify-center gap-1 focus:outline-none"
      >
        Done
      </button>

      <!-- Consumption Modal trigger -->
      <button 
        @click="$emit('add-consumption', task)"
        class="py-3 bg-gray-800 hover:bg-gray-700 text-gray-300 border border-gray-700 rounded-2xl font-extrabold text-xs active:scale-95 transition-all flex items-center justify-center gap-1 focus:outline-none"
      >
        + Parts
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';

const props = defineProps({
  task: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['status-updated', 'add-consumption']);

const updating = ref(false);
const toast = useToastStore();

const isDelayed = computed(() => {
  return props.task.status === 'in_progress' && props.task.actual_minutes > props.task.estimated_minutes;
});

const updateStatus = async (status) => {
  updating.value = true;
  try {
    const response = await api.put(`/mobile/tasks/${props.task.id}/status`, { status });
    toast.success(`Task status updated to ${status}`);
    emit('status-updated', response.data);
  } catch (error) {
    console.error('Failed to update task status', error);
  } finally {
    updating.value = false;
  }
};

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
