<template>
  <div class="space-y-4">
    <div class="flex justify-between items-end">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Transactions</h1>
        <p class="text-sm text-slate-500 mt-1">Manage expenses, income and view ledger.</p>
      </div>
      <div class="flex gap-2">
        <button @click="txStore.openModal(null, 'expense')" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-colors flex items-center gap-2">New Expense</button>
        <button @click="txStore.openModal(null, 'income')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-colors flex items-center gap-2">New Income</button>
      </div>
    </div>
    
    <div class="bg-white p-3 rounded-xl shadow-sm border border-slate-200 flex flex-col sm:flex-row gap-3 items-center">
      <div class="relative flex-1 w-full">
        <select v-model="typeInput" @change="applyType" class="block w-full rounded-lg border-0 py-2 pl-3 pr-10 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
          <option value="">All Transactions</option>
          <option value="expense">Expenses</option>
          <option value="income">Income</option>
          <option value="transfer">Transfers</option>
        </select>
      </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Account</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Category/Desc</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-slate-900 uppercase tracking-wider">Amount</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 bg-white">
            <tr v-if="txStore.loading">
              <td colspan="5" class="px-6 py-4 text-center text-sm text-slate-500">Loading...</td>
            </tr>
            <tr v-else-if="txStore.transactions.length === 0">
              <td colspan="5" class="px-6 py-4 text-center text-sm text-slate-500">No transactions found.</td>
            </tr>
            <tr v-else v-for="tx in txStore.transactions" :key="tx.id" class="hover:bg-slate-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">{{ tx.date }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">{{ tx.account ? tx.account.name : '-' }}</td>
              <td class="px-6 py-4 text-sm text-slate-900">
                <div class="font-medium" v-if="tx.category">{{ tx.category.name }}</div>
                <div class="text-slate-500 text-xs">{{ tx.description }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="['inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium ring-1 ring-inset', tx.type==='income'?'bg-emerald-50 text-emerald-700 ring-emerald-600/20':'bg-red-50 text-red-700 ring-red-600/20']">{{ tx.type }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" :class="tx.type==='income'?'text-emerald-600':'text-red-600'">
                {{ tx.type === 'expense' ? '-' : '+' }}{{ Number(tx.amount).toLocaleString() }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="txStore.isModalOpen" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
      <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
              <h3 class="text-xl font-semibold leading-6 text-slate-900">New {{ txStore.selectedTransaction.type }}</h3>
              <div class="mt-4 space-y-4">
                <div>
                  <label class="block text-sm font-medium leading-6 text-slate-900">Date</label>
                  <input v-model="txStore.selectedTransaction.date" type="date" class="mt-2 block w-full rounded-lg border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                </div>
                <div>
                  <label class="block text-sm font-medium leading-6 text-slate-900">Account</label>
                  <select v-model="txStore.selectedTransaction.account_id" class="mt-2 block w-full rounded-lg border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    <option v-for="acc in txStore.accounts" :key="acc.id" :value="acc.id">{{ acc.name }} ({{ Number(acc.balance).toLocaleString() }})</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium leading-6 text-slate-900">Category</label>
                  <select v-model="txStore.selectedTransaction.category_id" class="mt-2 block w-full rounded-lg border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    <option v-for="cat in txStore.categories.filter(c => c.type === txStore.selectedTransaction.type)" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium leading-6 text-slate-900">Amount</label>
                  <input v-model="txStore.selectedTransaction.amount" type="number" class="mt-2 block w-full rounded-lg border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                </div>
                <div>
                  <label class="block text-sm font-medium leading-6 text-slate-900">Description</label>
                  <textarea v-model="txStore.selectedTransaction.description" rows="2" class="mt-2 block w-full rounded-lg border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                </div>
              </div>
            </div>
            <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
              <button @click="handleSave" type="button" class="inline-flex w-full justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">Save</button>
              <button @click="txStore.closeModal()" type="button" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">Cancel</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useTransactionStore } from '../../stores/transaction';

const txStore = useTransactionStore();
const typeInput = ref('');

onMounted(() => {
  txStore.fetchTransactions();
});

const applyType = () => {
  txStore.setFilter('type', typeInput.value);
};

const handleSave = async () => {
  try {
    await txStore.saveTransaction();
  } catch (e) {
    alert('Failed to save transaction');
  }
};
</script>

