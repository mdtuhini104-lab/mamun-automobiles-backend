<script setup>
import { ref } from 'vue';

const currentMonth = ref('2023-10');
const employees = ref([
  { id: 1, name: 'Rahim Uddin' },
  { id: 2, name: 'Karim Ali' }
]);
const daysInMonth = ref(30); // Mock data

const getStatusClass = (day) => {
  // Random mock status for demonstration
  const rand = Math.random();
  if (day % 7 === 0) return 'bg-gray-100 text-gray-400'; // Weekend
  if (rand > 0.9) return 'bg-red-100 text-red-700 font-bold'; // Absent
  if (rand > 0.8) return 'bg-yellow-100 text-yellow-700 font-bold'; // Leave
  return 'bg-green-100 text-green-700 font-bold'; // Present
};

const getStatusText = (day) => {
  if (day % 7 === 0) return 'W';
  const rand = Math.random();
  if (rand > 0.9) return 'A';
  if (rand > 0.8) return 'L';
  return 'P';
};
</script>

<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Monthly Attendance</h1>
        <p class="text-gray-500 text-sm mt-1">Timeline view of employee attendance</p>
      </div>
      <div>
        <input type="month" v-model="currentMonth" class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border-collapse">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 z-10 border-r border-gray-200">Employee</th>
              <th v-for="day in daysInMonth" :key="day" class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase border-r border-gray-200">
                {{ day }}
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="emp in employees" :key="emp.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 sticky left-0 bg-white z-10 border-r border-gray-200">
                {{ emp.name }}
              </td>
              <td v-for="day in daysInMonth" :key="day" class="px-1 py-2 text-center border-r border-gray-100">
                <div :class="['w-6 h-6 mx-auto rounded flex items-center justify-center text-xs', getStatusClass(day)]">
                  {{ getStatusText(day) }}
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="p-4 border-t border-gray-100 bg-gray-50 flex gap-4 text-sm">
        <div class="flex items-center gap-1"><span class="w-4 h-4 rounded bg-green-100 text-green-700 flex items-center justify-center text-[10px] font-bold">P</span> Present</div>
        <div class="flex items-center gap-1"><span class="w-4 h-4 rounded bg-red-100 text-red-700 flex items-center justify-center text-[10px] font-bold">A</span> Absent</div>
        <div class="flex items-center gap-1"><span class="w-4 h-4 rounded bg-yellow-100 text-yellow-700 flex items-center justify-center text-[10px] font-bold">L</span> Leave</div>
        <div class="flex items-center gap-1"><span class="w-4 h-4 rounded bg-gray-100 text-gray-400 flex items-center justify-center text-[10px] font-bold">W</span> Weekend</div>
      </div>
    </div>
  </div>
</template>
