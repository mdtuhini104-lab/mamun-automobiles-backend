<template>
  <div class="cashbook-dashboard p-6">
    <h1 class="text-2xl font-bold mb-6">Cashbook Dashboard</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white p-6 rounded-xl shadow border-l-4 border-blue-500">
        <h3 class="text-gray-500 text-sm font-medium">Total Cash in Hand</h3>
        <p class="text-3xl font-bold text-gray-800 mt-2">৳ {{ stats.cashInHand }}</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow border-l-4 border-green-500">
        <h3 class="text-gray-500 text-sm font-medium">Total Bank Balance</h3>
        <p class="text-3xl font-bold text-gray-800 mt-2">৳ {{ stats.bankBalance }}</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow border-l-4 border-purple-500">
        <h3 class="text-gray-500 text-sm font-medium">Today's Income</h3>
        <p class="text-3xl font-bold text-gray-800 mt-2">৳ {{ stats.todayIncome }}</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow border-l-4 border-red-500">
        <h3 class="text-gray-500 text-sm font-medium">Today's Expense</h3>
        <p class="text-3xl font-bold text-gray-800 mt-2">৳ {{ stats.todayExpense }}</p>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Recent Transactions</h2>
        <button class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700">View All</button>
      </div>
      <table class="min-w-full divide-y divide-gray-200">
        <thead>
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="transactions.length === 0">
            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No transactions found</td>
          </tr>
          <tr v-for="tx in transactions" :key="tx.id">
            <td class="px-6 py-4 whitespace-nowrap">{{ tx.created_at }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="{'text-green-600 bg-green-100': tx.transaction_type === 'income', 'text-red-600 bg-red-100': tx.transaction_type === 'expense'}" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                {{ tx.transaction_type }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">৳ {{ tx.amount }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ tx.category }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const stats = ref({
  cashInHand: '0.00',
  bankBalance: '0.00',
  todayIncome: '0.00',
  todayExpense: '0.00'
});

const transactions = ref([]);

onMounted(async () => {
  // Logic to fetch stats and transactions
});
</script>
