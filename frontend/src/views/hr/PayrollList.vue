<template>
  <div class="space-y-4">
    <div class="flex justify-between items-end">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Payroll Management</h1>
        <p class="text-sm text-slate-500 mt-1">Manage monthly salaries, overtime and bonuses.</p>
      </div>
    </div>
    
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Period</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Employee</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-slate-900 uppercase tracking-wider">Basic</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-slate-900 uppercase tracking-wider">Overtime/Bonus</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-slate-900 uppercase tracking-wider">Deductions</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-slate-900 uppercase tracking-wider">Net Salary</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 bg-white">
            <tr v-if="hrStore.loading">
              <td colspan="7" class="px-6 py-4 text-center text-sm text-slate-500">Loading...</td>
            </tr>
            <tr v-else-if="hrStore.payrolls.length === 0">
              <td colspan="7" class="px-6 py-4 text-center text-sm text-slate-500">No payroll records found.</td>
            </tr>
            <tr v-else v-for="record in hrStore.payrolls" :key="record.id" class="hover:bg-slate-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 font-medium">{{ record.month }}/{{ record.year }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-slate-900">{{ record.user?.name }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 text-right">{{ Number(record.basic_salary).toLocaleString() }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-emerald-600 text-right">+{{ Number(Number(record.overtime_amount) + Number(record.bonus)).toLocaleString() }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-rose-600 text-right">-{{ Number(record.deductions).toLocaleString() }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 font-bold text-right">{{ Number(record.net_salary).toLocaleString() }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="{
                  'bg-emerald-50 text-emerald-700 ring-emerald-600/20': record.status === 'paid',
                  'bg-blue-50 text-blue-700 ring-blue-600/20': record.status === 'approved',
                  'bg-slate-50 text-slate-700 ring-slate-600/20': record.status === 'draft'
                }" class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset capitalize">
                  {{ record.status }}
                </span>
              </td>
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
  hrStore.fetchPayrolls();
});
</script>

