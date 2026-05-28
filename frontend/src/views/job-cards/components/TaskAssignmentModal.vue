<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-2xl w-full max-h-[85vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
        <div>
          <h3 class="text-base font-bold text-slate-950">Assign Task Technician</h3>
          <p class="text-xs text-slate-500 mt-0.5">Task: {{ taskName }}</p>
        </div>
        <button @click="close" class="text-slate-400 hover:text-slate-600 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="p-6 overflow-y-auto flex-1">
        <TechnicianSelector @select="handleSelect" />
      </div>

      <!-- Footer -->
      <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end space-x-3">
        <button
          @click="close"
          class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useJobCardTasksStore } from '../../../stores/jobCardTasks';
import TechnicianSelector from './TechnicianSelector.vue';

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  taskId: {
    type: Number,
    required: true
  },
  taskName: {
    type: String,
    required: true
  },
  jobCardId: {
    type: Number,
    required: true
  }
});

const emit = defineEmits(['close', 'assigned']);
const tasksStore = useJobCardTasksStore();

const close = () => {
  emit('close');
};

const handleSelect = async (tech) => {
  try {
    await tasksStore.assignTask(props.jobCardId, props.taskId, tech.id);
    emit('assigned');
    close();
  } catch (error) {
    // Handled by global interceptor toast
  }
};
</script>
