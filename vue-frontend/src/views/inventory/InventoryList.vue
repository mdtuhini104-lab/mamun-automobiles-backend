<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-800">Inventory Management</h1>
      <button class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
        Add Part
      </button>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-lg shadow flex flex-col sm:flex-row gap-4">
      <div class="flex-1">
        <input 
          v-model="searchInput"
          @keyup.enter="applySearch"
          type="text" 
          placeholder="Search parts by name or SKU..." 
          class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-indigo-500"
        >
      </div>
      <div class="w-full sm:w-48">
        <select 
          v-model="categoryInput"
          @change="applyCategory"
          class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-indigo-500"
        >
          <option value="">All Categories</option>
          <option value="engine">Engine</option>
          <option value="brakes">Brakes</option>
          <option value="suspension">Suspension</option>
          <option value="electrical">Electrical</option>
        </select>
      </div>
    </div>
    
    <!-- Parts Table -->
    <div class="bg-slate-800 rounded-lg shadow-lg border border-slate-700 overflow-x-auto relative">
      <div v-if="inventoryStore.loading" class="absolute inset-0 bg-slate-900 bg-opacity-50 flex items-center justify-center z-10">
        <span class="text-white">Loading...</span>
      </div>
      
      <table class="min-w-full divide-y divide-slate-700">
        <thead class="bg-slate-700">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Image/QR</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider cursor-pointer hover:text-white" @click="toggleSort('name')">
              Name/SKU
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider cursor-pointer hover:text-white" @click="toggleSort('stock')">
              Stock Status
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider cursor-pointer hover:text-white" @click="toggleSort('price')">
              Price
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-700">
          <tr v-for="part in inventoryStore.parts" :key="part.id" class="hover:bg-slate-750 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-slate-600 rounded overflow-hidden flex-shrink-0">
                  <img v-if="part.image_url" :src="part.image_url" :alt="part.name" class="w-full h-full object-cover" />
                  <div v-else class="w-full h-full flex items-center justify-center text-slate-400 text-xs">No Img</div>
                </div>
                <div v-if="part.barcode" class="w-8 h-8 bg-white p-1 rounded">
                  <!-- Barcode icon placeholder -->
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-full h-full text-black"><path d="M3 4h2v16H3V4zm4 0h1v16H7V4zm3 0h2v16h-2V4zm4 0h1v16h-1V4zm3 0h4v16h-4V4z"/></svg>
                </div>
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="text-sm font-medium text-white">{{ part.name }}</div>
              <div class="text-xs text-slate-400">{{ part.sku }}</div>
              <div v-if="part.category" class="inline-block mt-1 px-2 py-0.5 bg-slate-600 text-slate-200 rounded text-xs">
                {{ part.category }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <span class="text-lg font-bold mr-2" :class="part.stock <= (part.low_stock_threshold || 5) ? 'text-red-400' : 'text-green-400'">
                  {{ part.stock }}
                </span>
                <span v-if="part.stock <= (part.low_stock_threshold || 5)" class="px-2 py-1 bg-red-900 text-red-200 text-xs rounded-full animate-pulse">Low Stock</span>
                <span v-else class="px-2 py-1 bg-green-900 text-green-200 text-xs rounded-full">In Stock</span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">
              ${{ part.price }}
            </td>
          </tr>
          <tr v-if="!inventoryStore.loading && inventoryStore.parts.length === 0">
            <td colspan="4" class="px-6 py-8 text-center text-slate-400">
              No parts found matching your criteria.
            </td>
          </tr>
        </tbody>
      </table>
      
      <!-- Pagination -->
      <div v-if="inventoryStore.pagination.last_page > 1" class="px-6 py-3 bg-slate-800 flex items-center justify-between border-t border-slate-700">
        <div class="text-sm text-slate-400">
          Showing page {{ inventoryStore.pagination.current_page }} of {{ inventoryStore.pagination.last_page }} ({{ inventoryStore.pagination.total }} total)
        </div>
        <div class="flex space-x-2">
          <button 
            @click="goToPage(inventoryStore.pagination.current_page - 1)"
            :disabled="inventoryStore.pagination.current_page === 1"
            class="px-3 py-1 bg-slate-700 text-white rounded hover:bg-slate-600 disabled:opacity-50"
          >
            Prev
          </button>
          <button 
            @click="goToPage(inventoryStore.pagination.current_page + 1)"
            :disabled="inventoryStore.pagination.current_page === inventoryStore.pagination.last_page"
            class="px-3 py-1 bg-slate-700 text-white rounded hover:bg-slate-600 disabled:opacity-50"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useInventoryStore } from '../../stores/inventory';
import { useStockSocket } from '../../composables/useStockSocket';

const inventoryStore = useInventoryStore();

const searchInput = ref(inventoryStore.filters.search);
const categoryInput = ref(inventoryStore.filters.category);

const applySearch = () => {
  inventoryStore.setFilter('search', searchInput.value);
};

const applyCategory = () => {
  inventoryStore.setFilter('category', categoryInput.value);
};

const toggleSort = (field) => {
  const newDirection = inventoryStore.filters.sort === field && inventoryStore.filters.direction === 'asc' ? 'desc' : 'asc';
  inventoryStore.setFilter('sort', field);
  inventoryStore.setFilter('direction', newDirection);
};

const goToPage = (page) => {
  inventoryStore.fetchParts(page);
};

onMounted(() => {
  inventoryStore.fetchParts();
});

// Websocket sync patching state without reloading
useStockSocket((data) => {
  inventoryStore.patchStock(data);
});
</script>
