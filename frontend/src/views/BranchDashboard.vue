<template>
  <div class="branch-dashboard p-6 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Branch Management</h1>
      <button class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition">
        + Add New Branch
      </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="branch in branches" :key="branch.id" class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h2 class="text-xl font-bold text-gray-800">{{ branch.name }}</h2>
            <p class="text-gray-500 text-sm">{{ branch.location }}</p>
          </div>
          <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full">Active</span>
        </div>
        
        <div class="space-y-2 mb-6">
          <div class="flex justify-between">
            <span class="text-gray-600">Today's Revenue:</span>
            <span class="font-bold">৳ {{ branch.revenue?.toLocaleString() || '0' }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Pending Jobs:</span>
            <span class="font-bold">{{ branch.pending_jobs || '0' }}</span>
          </div>
        </div>

        <div class="flex space-x-2">
          <button class="flex-1 bg-gray-100 text-gray-700 py-2 rounded hover:bg-gray-200 transition text-sm font-medium">
            View Analytics
          </button>
          <button class="flex-1 bg-blue-50 text-blue-600 py-2 rounded hover:bg-blue-100 transition text-sm font-medium border border-blue-200">
            Switch to Branch
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const branches = ref([
    { id: 1, name: 'Dhaka HQ', location: 'Mirpur, Dhaka', revenue: 150000, pending_jobs: 12 },
    { id: 2, name: 'Chittagong Outlet', location: 'Agrabad, CG', revenue: 85000, pending_jobs: 5 }
]);

const fetchBranches = async () => {
    // In a real scenario, uncomment below
    // const res = await axios.get('/api/v1/branches');
    // branches.value = res.data;
};

onMounted(() => {
    fetchBranches();
});
</script>
