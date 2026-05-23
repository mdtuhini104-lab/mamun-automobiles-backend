<template>
  <div class="space-y-4 max-w-2xl mx-auto">
    <div class="flex items-center gap-3">
      <router-link :to="{ name: 'customers.index' }" class="text-slate-400 hover:text-indigo-600 transition-colors p-2 -ml-2 rounded-lg hover:bg-indigo-50">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
      </router-link>
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Add Customer</h1>
        <p class="text-sm text-slate-500 mt-1">Create a new customer profile.</p>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
      <form @submit.prevent="handleSubmit" class="p-6 space-y-5">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
          <input v-model="form.name" type="text" required class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
            <input v-model="form.phone" type="text" required class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
            <input v-model="form.email" type="email" class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
          <textarea v-model="form.address" rows="3" class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
        </div>
        
        <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
          <router-link :to="{ name: 'customers.index' }" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-md shadow-sm hover:bg-slate-50">Cancel</router-link>
          <button type="submit" :disabled="customerStore.saving" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none flex items-center justify-center">
            <svg v-if="customerStore.saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            Save Customer
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useCustomerStore } from '../../stores/customer';
import { useRouter } from 'vue-router';

const customerStore = useCustomerStore();
const router = useRouter();

const form = ref({
  name: '',
  phone: '',
  email: '',
  address: '',
  balance: 0
});

const handleSubmit = async () => {
  try {
    const customer = await customerStore.createCustomer(form.value);
    router.push({ name: 'customers.show', params: { id: customer.id } });
  } catch (error) {
    // Error is handled by global interceptor toast
  }
};
</script>
