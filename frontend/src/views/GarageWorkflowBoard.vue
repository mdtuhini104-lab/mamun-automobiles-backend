<template>
  <div class="kanban-board-container p-6 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Workshop Kanban Board</h1>
      <div class="flex space-x-4">
        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition">
          + New Job Card
        </button>
      </div>
    </div>

    <!-- Kanban Columns -->
    <div class="flex overflow-x-auto space-x-6 pb-8">
      <div v-for="stage in stages" :key="stage.id" class="flex-shrink-0 w-80 bg-gray-200 rounded-lg p-4 flex flex-col">
        <div class="flex justify-between items-center mb-4">
          <h2 class="font-bold text-gray-700 uppercase">{{ stage.name }}</h2>
          <span class="bg-gray-300 text-gray-700 text-xs font-bold px-2 py-1 rounded-full">
            {{ getJobsForStage(stage.id).length }}
          </span>
        </div>

        <div class="flex-1 overflow-y-auto space-y-4 min-h-[200px]" @dragover.prevent @drop="drop($event, stage.id)">
          <div 
            v-for="job in getJobsForStage(stage.id)" 
            :key="job.id" 
            class="bg-white p-4 rounded-lg shadow cursor-grab active:cursor-grabbing border-l-4"
            :style="{ borderColor: job.priorityColor }"
            draggable="true" 
            @dragstart="dragStart($event, job)"
          >
            <div class="flex justify-between items-start mb-2">
              <span class="text-xs font-semibold text-gray-500">#{{ job.job_no }}</span>
              <span class="text-xs px-2 py-1 rounded-full text-white" :style="{ backgroundColor: job.priorityColor }">
                {{ job.priority }}
              </span>
            </div>
            <h3 class="font-bold text-gray-800">{{ job.vehicle_reg }}</h3>
            <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ job.issue_summary }}</p>
            
            <div class="flex justify-between items-center">
              <div class="flex -space-x-2">
                <img v-for="mech in job.mechanics" :key="mech.id" :src="mech.avatar" class="w-6 h-6 rounded-full border-2 border-white" :title="mech.name">
              </div>
              <span v-if="job.due_date" class="text-xs text-red-500 font-medium">Due: {{ job.due_date }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const stages = ref([
  { id: 1, name: 'Pending' },
  { id: 2, name: 'Diagnosing' },
  { id: 3, name: 'Waiting Parts' },
  { id: 4, name: 'In Progress' },
  { id: 5, name: 'Quality Check' },
  { id: 6, name: 'Ready for Delivery' }
]);

const jobs = ref([
  { id: 101, job_no: 'JC-2023-001', stage_id: 1, vehicle_reg: 'DHA-11-2233', priority: 'High', priorityColor: '#EF4444', issue_summary: 'Engine making weird noise on startup', mechanics: [] },
  { id: 102, job_no: 'JC-2023-002', stage_id: 4, vehicle_reg: 'CTG-55-9988', priority: 'Medium', priorityColor: '#F59E0B', issue_summary: 'Full suspension rebuild and alignment', mechanics: [{id: 1, name: 'Rahim', avatar: 'https://i.pravatar.cc/150?u=1'}] },
  { id: 103, job_no: 'JC-2023-003', stage_id: 3, vehicle_reg: 'SYL-88-1122', priority: 'Urgent', priorityColor: '#991B1B', issue_summary: 'Waiting for Toyota Genuine Brake Pads', mechanics: [{id: 2, name: 'Karim', avatar: 'https://i.pravatar.cc/150?u=2'}] }
]);

const getJobsForStage = (stageId) => {
  return jobs.value.filter(job => job.stage_id === stageId);
};

const dragStart = (event, job) => {
  event.dataTransfer.setData('jobId', job.id);
};

const drop = (event, stageId) => {
  const jobId = parseInt(event.dataTransfer.getData('jobId'));
  const job = jobs.value.find(j => j.id === jobId);
  if (job && job.stage_id !== stageId) {
    job.stage_id = stageId;
    // API call to update backend would go here
    console.log(`Moved Job ${jobId} to Stage ${stageId}`);
  }
};
</script>

<style scoped>
/* Optional styling */
</style>
