<template>
  <div class="space-y-4">
    <div class="flex justify-between items-end">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Inventory & Parts</h1>
        <p class="text-sm text-slate-500 mt-1">Manage parts, track stock levels, and adjust quantities.</p>
      </div>
      <button @click="inventoryStore.openModal()" v-if="authStore.hasPermission('stock.adjust')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-colors flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Add Part
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
          placeholder="Search parts by name, SKU or Barcode..." 
          class="block w-full pl-9 rounded-lg border-0 bg-slate-50 py-2 text-slate-900 shadow-inner ring-1 ring-inset ring-slate-200 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition-all"
        >
      </div>
      <div class="w-full sm:w-56">
        <select 
          v-model="categoryInput"
          @change="applyCategory"
          class="block w-full rounded-lg border-0 py-2 pl-3 pr-10 text-slate-900 ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6 bg-slate-50 focus:bg-white transition-all"
        >
          <option value="">All Categories</option>
          <option v-for="cat in inventoryStore.categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Item Details</th>
              <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Stock</th>
              <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Pricing</th>
              <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-900 uppercase tracking-wider">Location</th>
              <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-900 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 bg-white">
            <tr v-if="inventoryStore.loading" v-for="i in 5" :key="i" class="animate-pulse">
              <td class="px-6 py-4"><div class="h-4 bg-slate-200 rounded w-3/4 mb-2"></div><div class="h-3 bg-slate-100 rounded w-1/2"></div></td>
              <td class="px-6 py-4"><div class="h-4 bg-slate-200 rounded w-1/2"></div></td>
              <td class="px-6 py-4"><div class="h-4 bg-slate-200 rounded w-1/2"></div></td>
              <td class="px-6 py-4"><div class="h-4 bg-slate-200 rounded w-1/2"></div></td>
              <td class="px-6 py-4"></td>
            </tr>
            <tr v-else-if="inventoryStore.parts.length === 0">
              <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                <div class="flex flex-col items-center justify-center">
                  <svg class="h-12 w-12 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                  <p class="text-base font-medium">No inventory items found</p>
                  <p class="text-sm">Add items or adjust search criteria.</p>
                </div>
              </td>
            </tr>
            <tr v-for="part in inventoryStore.parts" :key="part.id" class="hover:bg-slate-50 transition-colors group">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="h-10 w-10 flex-shrink-0 bg-slate-100 rounded-lg flex items-center justify-center border border-slate-200 text-slate-500">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-semibold text-slate-900">{{ part.name }}</div>
                    <div class="text-xs text-slate-500 mt-0.5 font-mono">SKU: {{ part.sku }}</div>
                    <div class="text-xs text-slate-400 mt-0.5" v-if="part.category">{{ part.category.name }} {{ part.brand ? ' - ' + part.brand : '' }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium ring-1 ring-inset" 
                        :class="[
                          part.stock_quantity <= 0 ? 'bg-red-50 text-red-700 ring-red-600/10' :
                          part.stock_quantity <= part.low_stock_threshold ? 'bg-amber-50 text-amber-700 ring-amber-600/20' :
                          'bg-emerald-50 text-emerald-700 ring-emerald-600/20'
                        ]">
                    {{ part.stock_quantity }} {{ part.unit_type }}
                  </span>
                  <span v-if="part.stock_quantity <= part.low_stock_threshold" class="text-xs text-amber-600 font-medium">Low</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-slate-900">?{{ Number(part.sale_price).toLocaleString() }} <span class="text-xs text-slate-500">(Sale)</span></div>
                <div class="text-xs text-slate-500">?{{ Number(part.cost_price).toLocaleString() }} (Cost)</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                {{ part.rack_location || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button v-if="authStore.hasPermission('stock.adjust')" @click="inventoryStore.openModal(part)" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-md transition-colors mr-2">Edit</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Pagination -->
      <div v-if="inventoryStore.pagination.total > 0" class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-4 py-3 sm:px-6">
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-slate-700">
              Showing <span class="font-medium">{{ ((inventoryStore.pagination.current_page - 1) * inventoryStore.pagination.per_page) + 1 }}</span>
              to <span class="font-medium">{{ Math.min(inventoryStore.pagination.current_page * inventoryStore.pagination.per_page, inventoryStore.pagination.total) }}</span>
              of <span class="font-medium">{{ inventoryStore.pagination.total }}</span> results
            </p>
          </div>
          <div>
            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
              <button 
                @click="changePage(inventoryStore.pagination.current_page - 1)"
                :disabled="inventoryStore.pagination.current_page === 1"
                class="relative inline-flex items-center rounded-l-md px-2 py-2 text-slate-400 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50"
              >
                <span class="sr-only">Previous</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" /></svg>
              </button>
              <button 
                @click="changePage(inventoryStore.pagination.current_page + 1)"
                :disabled="inventoryStore.pagination.current_page === inventoryStore.pagination.last_page"
                class="relative inline-flex items-center rounded-r-md px-2 py-2 text-slate-400 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50"
              >
                <span class="sr-only">Next</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="inventoryStore.isModalOpen" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
      <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b border-slate-100">
              <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                  <h3 class="text-xl font-semibold leading-6 text-slate-900" id="modal-title">{{ inventoryStore.selectedPart.id ? 'Edit Part' : 'Add New Part' }}</h3>
                </div>
              </div>
            </div>
            <div class="px-4 py-5 sm:p-6 bg-slate-50/50">
              <form @submit.prevent="handleSave" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Part Name *</label>
                    <input v-model="inventoryStore.selectedPart.name" type="text" required class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">SKU / Code *</label>
                    <input v-model="inventoryStore.selectedPart.sku" type="text" required class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                  </div>
                </div>
                
                <div class="grid grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Barcode</label>
                    <input v-model="inventoryStore.selectedPart.barcode" type="text" class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Brand</label>
                    <input v-model="inventoryStore.selectedPart.brand" type="text" class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                    <select v-model="inventoryStore.selectedPart.category_id" class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                      <option value="">Select Category</option>
                      <option v-for="cat in inventoryStore.categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Cost Price *</label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-slate-500 sm:text-sm">?</span></div>
                      <input v-model="inventoryStore.selectedPart.cost_price" type="number" step="0.01" required class="block w-full pl-7 rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Selling Price *</label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-slate-500 sm:text-sm">?</span></div>
                      <input v-model="inventoryStore.selectedPart.sale_price" type="number" step="0.01" required class="block w-full pl-7 rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                    </div>
                  </div>
                </div>
                
                <div class="grid grid-cols-4 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Stock *</label>
                    <input v-model="inventoryStore.selectedPart.stock_quantity" type="number" required class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Min Stock *</label>
                    <input v-model="inventoryStore.selectedPart.low_stock_threshold" type="number" required class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Unit</label>
                    <select v-model="inventoryStore.selectedPart.unit_type" class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                      <option value="pcs">Pcs</option>
                      <option value="ltr">Liter</option>
                      <option value="kg">Kg</option>
                      <option value="set">Set</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Rack</label>
                    <input v-model="inventoryStore.selectedPart.rack_location" type="text" class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                  </div>
                </div>
              </form>
            </div>
            <div class="bg-white px-4 py-4 sm:flex sm:flex-row-reverse sm:px-6 border-t border-slate-100">
              <button 
                @click="handleSave" 
                :disabled="inventoryStore.saving"
                type="button" 
                class="inline-flex w-full justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto disabled:opacity-50 transition-colors">
                <svg v-if="inventoryStore.saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                {{ inventoryStore.selectedPart.id ? 'Update Part' : 'Create Part' }}
              </button>
              <button @click="inventoryStore.closeModal()" type="button" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">
                Cancel
              </button>
              <button v-if="inventoryStore.selectedPart.id && authStore.hasPermission('stock.adjust')" @click="handleDelete(inventoryStore.selectedPart.id)" type="button" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-red-600 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-red-50 sm:mt-0 sm:w-auto sm:mr-auto transition-colors">
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useInventoryStore } from '../../stores/inventory';
import { useAuthStore } from '../../stores/auth';


const inventoryStore = useInventoryStore();
const authStore = useAuthStore();


const searchInput = ref('');
const categoryInput = ref('');

onMounted(async () => {
  await inventoryStore.fetchCategories();
  await inventoryStore.fetchParts();
});

const applySearch = () => {
  inventoryStore.setFilter('search', searchInput.value);
};

const applyCategory = () => {
  inventoryStore.setFilter('category', categoryInput.value);
};

const changePage = (page) => {
  inventoryStore.fetchParts(page);
};

const handleSave = async () => {
  try {
    await inventoryStore.savePart();
    alert('Part saved successfully');
  } catch (e) {
    alert('Failed to save part');
  }
};

const handleDelete = async (id) => {
  if(confirm('Are you sure you want to delete this part?')) {
    try {
      await inventoryStore.deletePart(id);
      inventoryStore.closeModal();
      alert('Part deleted successfully');
    } catch (e) {
      alert('Failed to delete part');
    }
  }
};
</script>

