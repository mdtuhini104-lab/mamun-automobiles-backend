<script setup>
import { ref } from 'vue';

const leaveRequests = ref([
  { id: 1, employee: 'Rahim Uddin', type: 'Sick Leave', from: '2023-10-15', to: '2023-10-16', days: 2, status: 'pending', reason: 'Fever and cold' },
  { id: 2, employee: 'Karim Ali', type: 'Annual Leave', from: '2023-10-20', to: '2023-10-25', days: 6, status: 'approved', reason: 'Family vacation' },
  { id: 3, employee: 'Abdul Jabbar', type: 'Casual Leave', from: '2023-10-10', to: '2023-10-10', days: 1, status: 'rejected', reason: 'Personal work' },
]);

const updateStatus = (id, newStatus) => {
  const req = leaveRequests.value.find(r => r.id === id);
  if (req) req.status = newStatus;
};
</script>

<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Leave Management</h1>
        <p class="text-gray-500 text-sm mt-1">Review and manage employee leave requests</p>
      </div>
      <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">Apply Leave (Admin)</button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
      <ul class="divide-y divide-gray-100">
        <li v-for="req in leaveRequests" :key="req.id" class="p-6 hover:bg-gray-50 transition">
          <div class="flex justify-between items-start">
            <div class="flex gap-4">
              <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-lg">
                {{ req.employee.charAt(0) }}
              </div>
              <div>
                <h3 class="text-lg font-medium text-gray-900">{{ req.employee }}</h3>
                <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                  <span class="inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    {{ req.from }} to {{ req.to }}
                  </span>
                  <span class="bg-gray-100 px-2 py-0.5 rounded text-gray-700">{{ req.days }} days</span>
                  <span class="text-blue-600">{{ req.type }}</span>
                </div>
                <p class="mt-2 text-sm text-gray-600">Reason: {{ req.reason }}</p>
              </div>
            </div>
            
            <div class="flex flex-col items-end gap-2">
              <span v-if="req.status === 'approved'" class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Approved</span>
              <span v-else-if="req.status === 'rejected'" class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">Rejected</span>
              <span v-else class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">Pending</span>

              <div v-if="req.status === 'pending'" class="flex gap-2 mt-2">
                <button @click="updateStatus(req.id, 'approved')" class="px-3 py-1.5 bg-green-50 text-green-600 border border-green-200 hover:bg-green-100 rounded text-sm font-medium transition">Approve</button>
                <button @click="updateStatus(req.id, 'rejected')" class="px-3 py-1.5 bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 rounded text-sm font-medium transition">Reject</button>
              </div>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>
