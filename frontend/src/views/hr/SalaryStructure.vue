<script setup>
import { ref } from 'vue';

const structures = ref([
  { id: 1, role: 'Mechanic', basic: 15000, medical: 500, transport: 500, pf: 500 },
  { id: 2, role: 'Sales', basic: 20000, medical: 1000, transport: 1000, pf: 1000 },
]);
</script>

<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Salary Structure</h1>
        <p class="text-gray-500 text-sm mt-1">Manage basic salary and allowances per role</p>
      </div>
      <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Add Structure
      </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="struct in structures" :key="struct.id" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
          <h3 class="text-lg font-bold text-gray-900">{{ struct.role }}</h3>
          <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">Edit</button>
        </div>
        <div class="p-6 space-y-4">
          <div class="flex justify-between items-center pb-3 border-b border-gray-100">
            <span class="text-gray-600">Basic Salary</span>
            <span class="font-bold text-gray-900">৳ {{ struct.basic }}</span>
          </div>
          <div class="flex justify-between items-center pb-3 border-b border-gray-100">
            <span class="text-gray-600">Medical Allowance</span>
            <span class="font-semibold text-green-600">৳ {{ struct.medical }}</span>
          </div>
          <div class="flex justify-between items-center pb-3 border-b border-gray-100">
            <span class="text-gray-600">Transport</span>
            <span class="font-semibold text-green-600">৳ {{ struct.transport }}</span>
          </div>
          <div class="flex justify-between items-center pb-3 border-b border-gray-100">
            <span class="text-gray-600">Provident Fund (PF)</span>
            <span class="font-semibold text-red-600">৳ {{ struct.pf }}</span>
          </div>
          <div class="pt-2 flex justify-between items-center">
            <span class="font-bold text-gray-900 uppercase text-sm">Total Gross</span>
            <span class="text-xl font-black text-gray-900">৳ {{ struct.basic + struct.medical + struct.transport }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
