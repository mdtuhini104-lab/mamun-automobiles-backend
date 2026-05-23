<template>
  <div class="space-y-4">
    <div class="flex justify-between items-end">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Purchases</h1>
        <p class="text-sm text-slate-500 mt-1">Manage purchase orders and vendor approvals.</p>
      </div>
      <button @click="purchaseStore.openModal()" v-if="authStore.hasPermission('purchase.create')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-colors flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        New Purchase
      </button>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-3 rounded-xl shadow-sm border border-slate-200 flex flex-col sm:flex-row gap-3 items-center">
      <div class="relative flex-1 w-full">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
        </div>
        <input 
          v-model="searchInput"
          @keyup.enter="applySearch"
          type="text" 
          placeholder="Search by PO Number or Supplier..." 
          class="block w-full pl-9 rounded-lg border-0 bg-slate-50 py-2 text-slate-900 shadow-inner ring-1 ring-inset ring-slate-200 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition-all"
        >
      </div>
      <div class="w-full sm:w-56">
        <select 
          v-model="statusInput"
          @change="applyStatus"
          class="block w-full rounded-lg border-0 bg-slate-50 py-2 pl-3 pr-10 text-slate-900 shadow-inner ring-1 ring-inset ring-slate-200 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition-all"
        >
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>
    </div>
    
    <!-- Purchases Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden relative">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
              <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider cursor-pointer hover:text-indigo-600 group" @click="toggleSort('po_number')">
                <div class="flex items-center gap-1">PO Number <span class="text-slate-400 group-hover:text-indigo-500">↕</span></div>
              </th>
              <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider cursor-pointer hover:text-indigo-600 group" @click="toggleSort('supplier_id')">
                <div class="flex items-center gap-1">Supplier <span class="text-slate-400 group-hover:text-indigo-500">↕</span></div>
              </th>
              <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider cursor-pointer hover:text-indigo-600 group" @click="toggleSort('total_amount')">
                <div class="flex items-center gap-1">Total <span class="text-slate-400 group-hover:text-indigo-500">↕</span></div>
              </th>
              <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
              <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Workflow</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            
            <!-- Loading Skeleton -->
            <template v-if="purchaseStore.loading">
              <tr v-for="i in 5" :key="'skeleton-'+i" class="animate-pulse">
                <td class="px-4 py-3"><div class="h-4 bg-slate-200 rounded w-24"></div></td>
                <td class="px-4 py-3"><div class="h-4 bg-slate-200 rounded w-32 mb-1"></div><div class="h-3 bg-slate-100 rounded w-20"></div></td>
                <td class="px-4 py-3"><div class="h-4 bg-slate-200 rounded w-16"></div></td>
                <td class="px-4 py-3"><div class="h-6 bg-slate-200 rounded-full w-20"></div></td>
                <td class="px-4 py-3 text-right"><div class="h-8 bg-slate-200 rounded w-20 inline-block"></div></td>
              </tr>
            </template>

            <template v-else>
              <tr v-for="purchase in purchaseStore.purchases" :key="purchase.id" class="hover:bg-slate-50/80 transition-colors group">
                <td class="px-4 py-3 whitespace-nowrap">
                  <div class="text-sm font-bold text-slate-900">{{ purchase.po_number }}</div>
                  <div class="text-[11px] text-slate-400 mt-0.5">{{ new Date(purchase.created_at || Date.now()).toLocaleDateString() }}</div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <div class="text-sm font-medium text-slate-700">{{ purchase.supplier?.name || 'Unknown Supplier' }}</div>
                  <div class="text-[11px] font-semibold mt-0.5" :class="purchase.supplier?.due_amount > 0 ? 'text-rose-600' : 'text-slate-400'">
                    {{ purchase.supplier?.due_amount > 0 ? 'Balance Due: $' + Number(purchase.supplier.due_amount).toFixed(2) : 'No Balance Due' }}
                  </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <div class="text-sm font-bold text-slate-900 tabular-nums">${{ Number(purchase.total_amount).toFixed(2) }}</div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border" :class="{
                    'bg-emerald-50 border-emerald-200 text-emerald-700': purchase.status === 'approved',
                    'bg-amber-50 border-amber-200 text-amber-700': purchase.status === 'pending',
                    'bg-red-50 border-red-200 text-red-700': purchase.status === 'rejected'
                  }">
                    <svg v-if="purchase.status === 'approved'" class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    <svg v-else-if="purchase.status === 'pending'" class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <svg v-else class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    {{ purchase.status.charAt(0).toUpperCase() + purchase.status.slice(1) }}
                  </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-right">
                  <div v-if="purchase.status === 'pending' && authStore.hasPermission('purchase.approve')" class="flex justify-end gap-2">
                    <button 
                      @click="handleApprove(purchase.id)"
                      :disabled="purchaseStore.approvingIds.includes(purchase.id)"
                      class="inline-flex items-center justify-center bg-indigo-600 text-white px-3 py-1.5 rounded-md text-xs hover:bg-indigo-700 disabled:opacity-50 transition-colors font-semibold shadow-sm w-24"
                    >
                      <svg v-if="purchaseStore.approvingIds.includes(purchase.id)" class="animate-spin -ml-1 mr-1.5 h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                      <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 mr-1"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                      {{ purchaseStore.approvingIds.includes(purchase.id) ? 'Approving' : 'Approve' }}
                    </button>
                  </div>
                  <div v-else-if="purchase.status === 'approved'" class="text-xs font-medium text-emerald-600 flex items-center justify-end gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Processed
                  </div>
                </td>
              </tr>
              
              <!-- Empty State -->
              <tr v-if="purchaseStore.purchases.length === 0">
                <td colspan="5" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                    </div>
                    <h3 class="text-base font-semibold text-slate-900 mb-1">No purchases found</h3>
                    <p class="text-sm text-slate-500 max-w-sm">We couldn't find any purchase orders matching your filters.</p>
                    <button @click="searchInput = ''; statusInput = ''; applySearch(); applyStatus()" class="mt-4 text-indigo-600 hover:text-indigo-700 text-sm font-medium">Clear all filters</button>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div v-if="purchaseStore.pagination.last_page > 1" class="px-4 py-3 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-0">
        <div class="text-sm text-slate-500">
          Showing <span class="font-medium text-slate-900">{{ purchaseStore.pagination.current_page }}</span> of <span class="font-medium text-slate-900">{{ purchaseStore.pagination.last_page }}</span> pages
          <span class="text-slate-400 mx-1">&bull;</span>
          <span class="font-medium text-slate-900">{{ purchaseStore.pagination.total }}</span> total items
        </div>
        <div class="flex space-x-2">
          <button 
            @click="goToPage(purchaseStore.pagination.current_page - 1)"
            :disabled="purchaseStore.pagination.current_page === 1"
            class="px-3 py-1.5 bg-white text-slate-700 border border-slate-300 rounded-md hover:bg-slate-50 hover:text-slate-900 disabled:opacity-50 disabled:hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 text-sm font-medium transition-colors shadow-sm"
          >
            Previous
          </button>
          <button 
            @click="goToPage(purchaseStore.pagination.current_page + 1)"
            :disabled="purchaseStore.pagination.current_page === purchaseStore.pagination.last_page"
            class="px-3 py-1.5 bg-white text-slate-700 border border-slate-300 rounded-md hover:bg-slate-50 hover:text-slate-900 disabled:opacity-50 disabled:hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 text-sm font-medium transition-colors shadow-sm"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Create Modal -->
    <div v-if="purchaseStore.isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
          <h2 class="text-lg font-bold text-slate-900">Create Purchase Order</h2>
          <button @click="purchaseStore.closeModal()" class="text-slate-400 hover:text-slate-600">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
        <div class="p-6 overflow-y-auto">
          <form @submit.prevent="handleSave" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Supplier ID</label>
              <input v-model="purchaseStore.form.supplier_id" type="text" required class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
              <textarea v-model="purchaseStore.form.notes" rows="2" class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
            </div>
            
            <div class="border-t border-slate-200 pt-4">
              <h3 class="text-sm font-medium text-slate-700 mb-2">Items (Add logic in real app)</h3>
              <!-- Simple input to satisfy requirements since we don't have a product selector -->
              <div class="bg-slate-50 p-3 rounded flex items-center justify-between">
                <span class="text-xs text-slate-500">Requires product search component</span>
                <button type="button" @click="purchaseStore.addItemToForm({id: 1, name: 'Test Part', cost: 10})" class="px-2 py-1 text-xs bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200">+ Add Mock Item</button>
              </div>
              <ul class="mt-2 space-y-2">
                <li v-for="(item, i) in purchaseStore.form.items" :key="i" class="flex items-center justify-between text-sm bg-white border border-slate-200 p-2 rounded">
                  <span>{{ item.name }}</span>
                  <div class="flex items-center gap-2">
                    <input type="number" v-model="item.quantity" class="w-16 p-1 border rounded text-xs" />
                    <span>x ${{ item.cost }}</span>
                    <button type="button" @click="purchaseStore.removeItemFromForm(i)" class="text-red-500 ml-2">x</button>
                  </div>
                </li>
              </ul>
              <div class="mt-2 text-right font-bold text-slate-900 text-sm">
                Total: ${{ purchaseStore.formTotal.toFixed(2) }}
              </div>
            </div>
            
            <div class="pt-4 flex justify-end gap-3 border-t border-slate-200 mt-4">
              <button type="button" @click="purchaseStore.closeModal()" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-md shadow-sm hover:bg-slate-50">Cancel</button>
              <button type="submit" :disabled="purchaseStore.saving || purchaseStore.form.items.length === 0" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 disabled:opacity-50 flex items-center justify-center">
                <svg v-if="purchaseStore.saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Create
              </button>
            </div>
          </form>
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
import { useAuthStore } from '../../stores/auth';

const purchaseStore = usePurchaseStore();
const inventoryStore = useInventoryStore();
const authStore = useAuthStore();

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

const handleSave = async () => {
  await purchaseStore.savePurchase();
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
