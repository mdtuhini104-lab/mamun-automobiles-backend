<template>
  <div class="vehicle-tracking-view p-6 bg-gray-900 text-white min-h-screen">
    <div class="max-w-3xl mx-auto">
      <h1 class="text-3xl font-bold mb-2">Live Vehicle Tracking</h1>
      <p class="text-gray-400 mb-8">Job Card: {{ jobCardNo }} | {{ vehicleReg }}</p>

      <div class="bg-gray-800 rounded-xl p-6 shadow-xl mb-8 border border-gray-700">
        <div class="flex justify-between items-center mb-6">
          <div>
            <h2 class="text-2xl font-bold text-blue-400">{{ currentStatus }}</h2>
            <p class="text-sm text-gray-400 mt-1">Estimated Completion: {{ estCompletion }}</p>
          </div>
          <div class="text-right">
            <span class="text-3xl font-bold text-white">{{ progress }}%</span>
          </div>
        </div>

        <div class="w-full bg-gray-700 rounded-full h-4 mb-4">
          <div class="bg-blue-500 h-4 rounded-full transition-all duration-1000 relative overflow-hidden" :style="{ width: progress + '%' }">
            <div class="absolute top-0 right-0 bottom-0 left-0 bg-white opacity-20" style="animation: pulse 2s infinite;"></div>
          </div>
        </div>
      </div>

      <div class="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-gray-600 before:to-transparent">
        <div v-for="(step, index) in timeline" :key="index" class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
          <div :class="['flex items-center justify-center w-10 h-10 rounded-full border-4 border-gray-900 bg-gray-700 shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow', step.completed ? 'bg-green-500 border-green-200' : '']">
            <svg v-if="step.completed" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
          </div>
          
          <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] bg-gray-800 p-4 rounded border border-gray-700 shadow">
            <div class="flex items-center justify-between space-x-2 mb-1">
              <div class="font-bold text-gray-200">{{ step.status }}</div>
              <time class="text-xs font-medium text-gray-400">{{ step.time }}</time>
            </div>
            <div class="text-sm text-gray-400">
                <span v-if="step.completed">Completed.</span>
                <span v-else>Pending...</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const jobCardNo = ref('JC-2023-089');
const vehicleReg = ref('DHA-11-2233');
const currentStatus = ref('Repair Running');
const estCompletion = ref('Tomorrow, 5:00 PM');
const progress = ref(65);
const timeline = ref([
    { status: 'Vehicle Received', time: '10:00 AM', completed: true },
    { status: 'Inspection Running', time: '11:00 AM', completed: true },
    { status: 'Repair Running', time: '1:00 PM', completed: false },
    { status: 'Quality Check', time: '-', completed: false },
    { status: 'Ready For Delivery', time: '-', completed: false }
]);
</script>

<style scoped>
@keyframes pulse {
  0%, 100% { opacity: 0; }
  50% { opacity: 0.5; }
}
</style>
