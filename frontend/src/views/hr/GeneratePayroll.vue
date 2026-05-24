<script setup>
import { ref } from 'vue';

const selectedMonth = ref('2023-10');
const isGenerating = ref(false);
const employees = ref([
  { id: 1, name: 'Rahim Uddin', role: 'Mechanic', basic: 15000, allowance: 2000, deduction: 500, net: 16500, status: 'pending' },
  { id: 2, name: 'Karim Ali', role: 'Sales', basic: 20000, allowance: 3000, deduction: 0, net: 23000, status: 'pending' },
]);

const generateBulk = () => {
  isGenerating.value = true;
  setTimeout(() => {
    employees.value.forEach(emp => emp.status = 'processed');
    isGenerating.value = false;
  }, 1500);
};

</script>

<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Generate Payroll</h1>
        <p class="text-gray-500 text-sm mt-1">Process monthly salary for employees</p>
      </div>
      <div class="flex items-center gap-4">
        <input type="month" v-model="selectedMonth" class="border border-gray-300 rounded-lg px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-500" />
        <button @click="generateBulk" :disabled="isGenerating" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition disabled:opacity-50 flex items-center gap-2">
          <svg v-if="isGenerating" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ isGenerating ? 'Processing...' : 'Bulk Generate' }}
        </button>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Basic</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Allowance</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Deduction</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net Pay</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="emp in employees" :key="emp.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ emp.name }}</div>
              <div class="text-sm text-gray-500">{{ emp.role }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">৳ {{ emp.basic }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-green-600">+ ৳ {{ emp.allowance }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-red-600">- ৳ {{ emp.deduction }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-gray-900">৳ {{ emp.net }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
              <span v-if="emp.status === 'processed'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Processed</span>
              <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <router-link to="/dashboard/payslip-preview" class="text-blue-600 hover:text-blue-900 mr-3">View Slip</router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
