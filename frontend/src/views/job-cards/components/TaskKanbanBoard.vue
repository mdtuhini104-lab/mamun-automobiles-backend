<template>
  <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-2xl flex flex-col gap-6">
    <div class="flex items-center justify-between border-b border-gray-800 pb-4">
      <div>
        <h3 class="text-base font-bold text-white">Interactive Task Board</h3>
        <p class="text-xs text-gray-500 mt-0.5">Drag and drop repair tasks to update status in realtime</p>
      </div>
    </div>

    <!-- Kanban columns -->
    <div class="flex overflow-x-auto gap-4 pb-4 custom-scrollbar">
      <div 
        v-for="col in columns" 
        :key="col.id"
        class="flex-shrink-0 w-72 bg-gray-950 border border-gray-850 rounded-2xl p-4 flex flex-col min-h-[300px]"
      >
        <!-- Column Header -->
        <div class="flex justify-between items-center mb-4">
          <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest">{{ col.name }}</h4>
          <span class="bg-gray-900 text-gray-400 text-[10px] font-bold px-2 py-0.5 rounded-full">
            {{ getTasksByColumn(col.id).length }}
          </span>
        </div>

        <!-- Task Drop Target -->
        <div 
          class="flex-1 space-y-3 overflow-y-auto min-h-[220px]"
          @dragover.prevent
          @drop="handleDrop($event, col.id)"
        >
          <div 
            v-for="task in getTasksByColumn(col.id)" 
            :key="task.id"
            class="bg-gray-900 border border-gray-800 hover:border-gray-700 p-4 rounded-xl shadow-lg cursor-grab active:cursor-grabbing hover:shadow-xl transition flex flex-col gap-3 group"
            draggable="true"
            @dragstart="handleDragStart($event, task)"
          >
            <!-- Header details -->
            <div class="flex justify-between items-start">
              <span class="text-[9px] font-mono text-gray-500 font-bold">#{{ task.id }}</span>
              <span 
                v-if="isOverdue(task)" 
                class="px-2 py-0.5 rounded bg-rose-500/10 text-rose-400 text-[9px] font-bold border border-rose-500/20 uppercase tracking-wider animate-pulse"
              >
                Delayed
              </span>
            </div>
            
            <h5 class="text-xs font-bold text-white group-hover:text-indigo-400 transition-colors">
              {{ task.name }}
            </h5>

            <!-- Estimated vs Actual -->
            <div class="flex justify-between items-center text-[10px] text-gray-500 border-t border-gray-850 pt-2.5 mt-1 font-bold">
              <span>Duration:</span>
              <span :class="isOverdue(task) ? 'text-rose-400 font-bold' : 'text-gray-400'">
                {{ task.actual_minutes || 0 }}/{{ task.estimated_minutes || 0 }} mins
              </span>
            </div>

            <!-- Technician tag -->
            <div class="flex items-center justify-between border-t border-gray-850 pt-2.5 mt-0.5">
              <span class="text-[9px] text-gray-500 font-bold uppercase tracking-wider">Technician</span>
              <span class="text-[10px] font-bold text-gray-300">
                {{ getTechName(task) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../../../services/api';
import { useToastStore } from '../../../stores/toast';

const props = defineProps({
  tasks: {
    type: Array,
    required: true,
    default: () => []
  }
});

const emit = defineEmits(['updated']);

const toast = useToastStore();

const columns = [
  { id: 'pending', name: 'Pending' },
  { id: 'in_progress', name: 'In Progress' },
  { id: 'paused', name: 'Paused' },
  { id: 'completed', name: 'Completed' }
];

const getTasksByColumn = (colId) => {
  return props.tasks.filter(t => (t.status || 'pending').toLowerCase() === colId);
};

const isOverdue = (task) => {
  return task.status === 'in_progress' && task.actual_minutes > task.estimated_minutes;
};

const getTechName = (task) => {
  if (task.assignments && task.assignments.length > 0) {
    const active = task.assignments.find(a => a.status === 'active');
    if (active && active.employee) {
      return active.employee.name || `${active.employee.first_name} ${active.employee.last_name}`;
    }
    const first = task.assignments[0].employee;
    return first.name || `${first.first_name} ${first.last_name}`;
  }
  return 'Unassigned';
};

const handleDragStart = (e, task) => {
  e.dataTransfer.setData('taskId', task.id);
};

const handleDrop = async (e, colId) => {
  const taskId = parseInt(e.dataTransfer.getData('taskId'));
  const task = props.tasks.find(t => t.id === taskId);
  
  if (task && (task.status || 'pending').toLowerCase() !== colId) {
    try {
      // Call task status update endpoint
      await api.put(`/mobile/tasks/${taskId}/status`, { status: colId });
      toast.success(`Task status updated to ${colId}`);
      emit('updated');
    } catch (error) {
      console.error('Failed to drag and drop task', error);
    }
  }
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #374151;
  border-radius: 9999px;
}
</style>
