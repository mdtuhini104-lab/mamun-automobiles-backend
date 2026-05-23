<template>
  <div class="space-y-4">
    <div class="flex justify-between items-end">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Attendance System</h1>
        <p class="text-sm text-slate-500 mt-1">Monitor daily employee attendance.</p>
      </div>
    </div>
    
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Employee</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Check In</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Check Out</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Late (Mins)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 bg-white">
            <tr v-if="hrStore.loading">
              <td colspan="6" class="px-6 py-4 text-center text-sm text-slate-500">Loading...</td>
            </tr>
            <tr v-else-if="hrStore.attendances.length === 0">
              <td colspan="6" class="px-6 py-4 text-center text-sm text-slate-500">No attendance records found.</td>
            </tr>
            <tr v-else v-for="record in hrStore.attendances" :key="record.id" class="hover:bg-slate-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">{{ record.date }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-slate-900">{{ record.user?.name }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ record.check_in || '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ record.check_out || '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="{
                  'bg-emerald-50 text-emerald-700 ring-emerald-600/20': record.status === 'present',
                  'bg-rose-50 text-rose-700 ring-rose-600/20': record.status === 'absent',
                  'bg-amber-50 text-amber-700 ring-amber-600/20': record.status === 'late' || record.status === 'half_day',
                  'bg-blue-50 text-blue-700 ring-blue-600/20': record.status === 'on_leave'
                }" class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset capitalize">
                  {{ record.status.replace('_', ' ') }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-rose-600 font-medium">{{ record.late_minutes > 0 ? record.late_minutes : '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useHrStore } from '../../stores/hr';

const hrStore = useHrStore();

onMounted(() => {
  hrStore.fetchAttendances();
});
</script>

