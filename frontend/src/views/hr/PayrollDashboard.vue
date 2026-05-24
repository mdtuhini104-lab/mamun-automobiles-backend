<script setup>
import { ref } from 'vue';

const metrics = ref([
  { title: 'Total Payroll (This Month)', value: '৳ 450,000', change: '+5%', isPositive: true },
  { title: 'Employees Processed', value: '38/45', change: '7 pending', isPositive: false },
  { title: 'Average Salary', value: '৳ 15,500', change: '+2%', isPositive: true },
]);

const recentPayrolls = ref([
  { month: 'September 2023', amount: '৳ 445,000', status: 'Paid', date: 'Oct 01, 2023' },
  { month: 'August 2023', amount: '৳ 440,000', status: 'Paid', date: 'Sep 02, 2023' },
]);
</script>

<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Payroll Dashboard</h1>
        <p class="text-gray-500 text-sm mt-1">Overview of company payroll and salaries</p>
      </div>
      <div class="flex gap-2">
        <router-link to="/dashboard/generate-payroll" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Generate Payroll</router-link>
        <router-link to="/dashboard/salary-structure" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">Salary Structure</router-link>
      </div>
    </div>

    <!-- Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div v-for="metric in metrics" :key="metric.title" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <p class="text-sm font-medium text-gray-500">{{ metric.title }}</p>
        <div class="mt-2 flex items-baseline gap-2">
          <p class="text-3xl font-bold text-gray-900">{{ metric.value }}</p>
          <p :class="['text-sm font-medium', metric.isPositive ? 'text-green-600' : 'text-yellow-600']">
            {{ metric.change }}
          </p>
        </div>
      </div>
    </div>

    <!-- Recent Payrolls -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-lg font-semibold text-gray-900">Recent Payrolls</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Date</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="pr in recentPayrolls" :key="pr.month" class="hover:bg-gray-50 transition">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ pr.month }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ pr.amount }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ pr.date }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                  {{ pr.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button class="text-blue-600 hover:text-blue-900">View Report</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
