<template>
  <div class="daily-closing-view p-6">
    <h1 class="text-2xl font-bold mb-6">Daily Closing</h1>
    
    <div class="bg-white rounded-xl shadow p-6 mb-6">
      <h2 class="text-xl font-bold mb-4">Close the Day</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700">Select Cashbook</label>
          <select v-model="form.cashbook_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md border">
            <option value="" disabled>Select a cashbook</option>
            <option v-for="cb in cashbooks" :key="cb.id" :value="cb.id">{{ cb.name }} (৳{{ cb.current_balance }})</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700">Manual Adjustment (Optional)</label>
          <input type="number" v-model="form.manual_adjustment" class="mt-1 block w-full pl-3 py-2 border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md border" placeholder="Enter amount">
        </div>
      </div>

      <div class="mt-4">
        <label class="block text-sm font-medium text-gray-700">Closing Notes</label>
        <textarea v-model="form.closing_notes" rows="3" class="mt-1 block w-full pl-3 py-2 border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md border" placeholder="Any notes regarding the closing..."></textarea>
      </div>
      
      <div class="mt-6 flex justify-end">
        <button @click="submitClosing" :disabled="loading" class="bg-blue-600 text-white px-6 py-2 rounded shadow hover:bg-blue-700 disabled:opacity-50">
          {{ loading ? 'Closing...' : 'Close Day' }}
        </button>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
      <h2 class="text-xl font-bold mb-4">Past Closings</h2>
      <table class="min-w-full divide-y divide-gray-200">
        <thead>
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cashbook</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Closing Balance</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Difference</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="pastClosings.length === 0">
            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No past closings found</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const cashbooks = ref([]);
const pastClosings = ref([]);
const loading = ref(false);

const form = ref({
  cashbook_id: '',
  manual_adjustment: 0,
  closing_notes: ''
});

const submitClosing = async () => {
  // Call API to close day
  console.log("Submitting closing...", form.value);
};

onMounted(() => {
  // Fetch cashbooks and past closings
});
</script>
