<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-800">Purchase Management</h1>
      <button class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
        New Purchase
      </button>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-lg shadow flex flex-col sm:flex-row gap-4">
      <div class="flex-1">
        <input 
          v-model="searchInput"
          @keyup.enter="applySearch"
          type="text" 
          placeholder="Search by PO Number or Supplier..." 
          class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-indigo-500"
        >
      </div>
      <div class="w-full sm:w-48">
        <select 
          v-model="statusInput"
          @change="applyStatus"
          class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-indigo-500"
        >
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>
    </div>
    
    <!-- Purchases Table -->
    <div class="bg-slate-800 rounded-lg shadow-lg border border-slate-700 overflow-x-auto relative">
      <div v-if="purchaseStore.loading" class="absolute inset-0 bg-slate-900 bg-opacity-50 flex items-center justify-center z-10">
        <span class="text-white">Loading...</span>
      </div>
      
      <table class="min-w-full divide-y divide-slate-700">
        <thead class="bg-slate-700">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider cursor-pointer hover:text-white" @click="toggleSort('po_number')">PO Number</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider cursor-pointer hover:text-white" @click="toggleSort('supplier_id')">Supplier</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider cursor-pointer hover:text-white" @click="toggleSort('total_amount')">Total</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-slate-300 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-700">
          <tr v-for="purchase in purchaseStore.purchases" :key="purchase.id" class="hover:bg-slate-750 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-white font-medium">{{ purchase.po_number }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">
              <div>{{ purchase.supplier?.name || 'Unknown Supplier' }}</div>
              <div class="text-xs text-slate-500" v-if="purchase.supplier?.due_amount > 0">Due: ${{ purchase.supplier.due_amount }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">${{ purchase.total_amount }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <span class="px-2 py-1 rounded-full text-xs font-medium" :class="{
                'bg-green-900 border border-green-500 text-green-400': purchase.status === 'approved',
                'bg-yellow-900 border border-yellow-500 text-yellow-400': purchase.status === 'pending',
                'bg-red-900 border border-red-500 text-red-400': purchase.status === 'rejected'
              }">
                {{ purchase.status.charAt(0).toUpperCase() + purchase.status.slice(1) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
              <button 
                v-if="purchase.status === 'pending'"
                @click="handleApprove(purchase.id)"
                :disabled="purchaseStore.approvingIds.includes(purchase.id)"
                class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-500 disabled:opacity-50 transition"
              >
                {{ purchaseStore.approvingIds.includes(purchase.id) ? 'Approving...' : 'Approve' }}
              </button>
            </td>
          </tr>
          <tr v-if="!purchaseStore.loading && purchaseStore.purchases.length === 0">
            <td colspan="5" class="px-6 py-8 text-center text-slate-400">
              No purchases found matching your criteria.
            </td>
          </tr>
        </tbody>
      </table>
      
      <!-- Pagination -->
      <div v-if="purchaseStore.pagination.last_page > 1" class="px-6 py-3 bg-slate-800 flex items-center justify-between border-t border-slate-700">
        <div class="text-sm text-slate-400">
          Showing page {{ purchaseStore.pagination.current_page }} of {{ purchaseStore.pagination.last_page }} ({{ purchaseStore.pagination.total }} total)
        </div>
        <div class="flex space-x-2">
          <button 
            @click="goToPage(purchaseStore.pagination.current_page - 1)"
            :disabled="purchaseStore.pagination.current_page === 1"
            class="px-3 py-1 bg-slate-700 text-white rounded hover:bg-slate-600 disabled:opacity-50"
          >
            Prev
          </button>
          <button 
            @click="goToPage(purchaseStore.pagination.current_page + 1)"
            :disabled="purchaseStore.pagination.current_page === purchaseStore.pagination.last_page"
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
import { usePurchaseStore } from '../../stores/purchases';
import { usePurchaseSocket } from '../../composables/usePurchaseSocket';
import { useStockSocket } from '../../composables/useStockSocket';
import { useInventoryStore } from '../../stores/inventory';

const purchaseStore = usePurchaseStore();
const inventoryStore = useInventoryStore();

const searchInput = ref(purchaseStore.filters.search);
const statusInput = ref(purchaseStore.filters.status);

const applySearch = () => {
  purchaseStore.setFilter('search', searchInput.value);
};

const applyStatus = () => {
  purchaseStore.setFilter('status', statusInput.value);
};

const toggleSort = (field) => {
  const newDirection = purchaseStore.filters.sort === field && purchaseStore.filters.direction === 'asc' ? 'desc' : 'asc';
  purchaseStore.setFilter('sort', field);
  purchaseStore.setFilter('direction', newDirection);
};

const goToPage = (page) => {
  purchaseStore.fetchPurchases(page);
};

const handleApprove = async (id) => {
  try {
    await purchaseStore.approvePurchase(id);
    // Note: Stock increments and audit logs are handled safely on the backend during the /approve endpoint.
    // The backend should then broadcast a StockSyncEvent to patch our local inventory safely,
    // which our useStockSocket listener catches automatically.
  } catch (err) {
    // Error handled in store
  }
};

onMounted(() => {
  purchaseStore.fetchPurchases();
});

// Realtime safety patching
usePurchaseSocket((data) => {
  // Patches purchase status locally if approved externally to prevent duplicate approval requests
  purchaseStore.patchPurchaseStatus(data);
});

// To fulfill "Realtime stock update propagation" rules safely
useStockSocket((data) => {
  inventoryStore.patchStock(data);
});
</script>
