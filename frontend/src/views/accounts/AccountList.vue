<template>
  <div class="space-y-4">
    <div class="flex justify-between items-end">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Accounts</h1>
        <p class="text-sm text-slate-500 mt-1">Manage cash and bank accounts.</p>
      </div>
      <button @click="accountStore.openModal()" v-if="authStore.hasPermission('account.create')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-colors flex items-center gap-2">
        New Account
      </button>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Name</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Balance</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-slate-900 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 bg-white">
            <tr v-if="accountStore.loading">
              <td colspan="4" class="px-6 py-4 text-center text-sm text-slate-500">Loading...</td>
            </tr>
            <tr v-else-if="accountStore.accounts.length === 0">
              <td colspan="4" class="px-6 py-4 text-center text-sm text-slate-500">No accounts found.</td>
            </tr>
            <tr v-else v-for="account in accountStore.accounts" :key="account.id" class="hover:bg-slate-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">{{ account.name }}<br><span class="text-xs text-slate-500 font-normal">{{ account.account_no }}</span></td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 capitalize">{{ account.type }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 font-medium">{{ Number(account.balance).toLocaleString() }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button @click="accountStore.openModal(account)" class="text-indigo-600 hover:text-indigo-900">Edit</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="accountStore.isModalOpen" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
      <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
              <h3 class="text-xl font-semibold leading-6 text-slate-900">{{ accountStore.selectedAccount.id ? 'Edit Account' : 'New Account' }}</h3>
              <div class="mt-4 space-y-4">
                <div>
                  <label class="block text-sm font-medium leading-6 text-slate-900">Account Name</label>
                  <input v-model="accountStore.selectedAccount.name" type="text" class="mt-2 block w-full rounded-lg border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                </div>
                <div>
                  <label class="block text-sm font-medium leading-6 text-slate-900">Type</label>
                  <select v-model="accountStore.selectedAccount.type" class="mt-2 block w-full rounded-lg border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    <option value="cash">Cash</option>
                    <option value="bank">Bank</option>
                  </select>
                </div>
                <div v-if="accountStore.selectedAccount.type === 'bank'">
                  <label class="block text-sm font-medium leading-6 text-slate-900">Account Number</label>
                  <input v-model="accountStore.selectedAccount.account_no" type="text" class="mt-2 block w-full rounded-lg border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                </div>
              </div>
            </div>
            <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
              <button @click="handleSave" :disabled="accountStore.saving" type="button" class="inline-flex w-full justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto disabled:opacity-50">
                {{ accountStore.saving ? 'Saving...' : 'Save' }}
              </button>
              <button @click="accountStore.closeModal()" type="button" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">Cancel</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAccountStore } from '../../stores/account';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';

const accountStore = useAccountStore();
const authStore = useAuthStore();
const toastStore = useToastStore();

onMounted(() => {
  accountStore.fetchAccounts();
});

const handleSave = async () => {
  try {
    await accountStore.saveAccount();
    toastStore.success('Account saved successfully.');
  } catch (e) {
    // Handled by global Axios interceptor toast
  }
};
</script>

