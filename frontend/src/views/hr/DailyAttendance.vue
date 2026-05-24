<script setup>
import { ref, computed } from 'vue';

const date = ref(new Date().toISOString().split('T')[0]);
const searchQuery = ref('');

const employees = ref([
  { id: 1, name: 'Rahim Uddin', role: 'Mechanic', status: 'present', timeIn: '08:50 AM', timeOut: null },
  { id: 2, name: 'Karim Ali', role: 'Sales', status: 'absent', timeIn: null, timeOut: null },
  { id: 3, name: 'Abdul Jabbar', role: 'Manager', status: 'present', timeIn: '09:05 AM', timeOut: null },
  { id: 4, name: 'Sumon Miah', role: 'Cleaner', status: 'leave', timeIn: null, timeOut: null },
]);

const filteredEmployees = computed(() => {
  return employees.value.filter(emp => emp.name.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

const markAttendance = (id, status) => {
  const emp = employees.value.find(e => e.id === id);
  if (emp) {
    emp.status = status;
    if (status === 'present') emp.timeIn = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    else emp.timeIn = null;
  }
};
</script>

<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center flex-wrap gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Daily Attendance</h1>
        <p class="text-gray-500 text-sm mt-1">Manage daily attendance records</p>
      </div>
      <div class="flex items-center gap-3">
        <input type="date" v-model="date" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" />
        <button class="flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
          QR Scan
        </button>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="p-4 border-b border-gray-100 flex justify-between items-center">
        <div class="relative w-64">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
          </div>
          <input type="text" v-model="searchQuery" placeholder="Search employee..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
        </div>
        <button class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition text-sm font-medium">Bulk Mark Present</button>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time In</th>
              <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="emp in filteredEmployees" :key="emp.id" class="hover:bg-gray-50 transition">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold">
                    {{ emp.name.charAt(0) }}
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ emp.name }}</div>
                    <div class="text-sm text-gray-500">{{ emp.role }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span v-if="emp.status === 'present'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Present</span>
                <span v-else-if="emp.status === 'absent'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Absent</span>
                <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Leave</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ emp.timeIn || '--:--' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end gap-2">
                  <button @click="markAttendance(emp.id, 'present')" :class="['px-3 py-1 rounded text-xs transition', emp.status === 'present' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']">Present</button>
                  <button @click="markAttendance(emp.id, 'absent')" :class="['px-3 py-1 rounded text-xs transition', emp.status === 'absent' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']">Absent</button>
                  <button @click="markAttendance(emp.id, 'leave')" :class="['px-3 py-1 rounded text-xs transition', emp.status === 'leave' ? 'bg-yellow-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']">Leave</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
