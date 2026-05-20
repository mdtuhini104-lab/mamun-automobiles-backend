<template>
  <div class="space-y-6 relative">
    <div class="flex justify-between items-center print:hidden">
      <h1 class="text-2xl font-bold text-gray-800">Invoice Management</h1>
      <button class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
        New Invoice
      </button>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-lg shadow flex flex-col sm:flex-row gap-4 print:hidden">
      <div class="flex-1">
        <input 
          v-model="searchInput"
          @keyup.enter="applySearch"
          type="text" 
          placeholder="Search by Invoice No or Customer..." 
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
          <option value="paid">Paid</option>
          <option value="partial">Partial</option>
          <option value="pending">Pending</option>
        </select>
      </div>
    </div>
    
    <!-- Invoices Table -->
    <div class="bg-slate-800 rounded-lg shadow-lg border border-slate-700 overflow-x-auto relative print:hidden">
      <div v-if="invoiceStore.loading" class="absolute inset-0 bg-slate-900 bg-opacity-50 flex items-center justify-center z-10">
        <span class="text-white">Loading...</span>
      </div>
      
      <table class="min-w-full divide-y divide-slate-700">
        <thead class="bg-slate-700">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider cursor-pointer hover:text-white" @click="toggleSort('invoice_number')">Invoice No</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider cursor-pointer hover:text-white" @click="toggleSort('customer_id')">Customer</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider cursor-pointer hover:text-white" @click="toggleSort('total_amount')">Amount</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-slate-300 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-700">
          <tr v-for="invoice in invoiceStore.invoices" :key="invoice.id" class="hover:bg-slate-750 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-white font-medium">{{ invoice.invoice_number }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">
              <div>{{ invoice.customer?.name || 'Walk-in' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">
              <div>Total: ${{ invoice.total_amount }}</div>
              <div class="text-xs mt-1" :class="invoice.due_amount > 0 ? 'text-red-400' : 'text-green-400'">
                Due: ${{ invoice.due_amount || (invoice.total_amount - (invoice.paid_amount || 0)).toFixed(2) }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <span class="px-2 py-1 rounded-full text-xs font-medium" :class="{
                'bg-green-900 border border-green-500 text-green-400': invoice.status === 'paid',
                'bg-yellow-900 border border-yellow-500 text-yellow-400': invoice.status === 'partial' || invoice.status === 'pending',
                'bg-red-900 border border-red-500 text-red-400': invoice.status === 'overdue' || invoice.status === 'cancelled'
              }">
                {{ invoice.status.charAt(0).toUpperCase() + invoice.status.slice(1) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-right space-x-2">
              <button 
                @click="printInvoice(invoice)"
                class="text-indigo-400 hover:text-indigo-300"
              >
                Print
              </button>
              <button 
                v-if="invoice.status !== 'paid' && invoice.status !== 'cancelled'"
                @click="invoiceStore.openPaymentModal(invoice)"
                class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-500 transition"
              >
                Pay
              </button>
            </td>
          </tr>
          <tr v-if="!invoiceStore.loading && invoiceStore.invoices.length === 0">
            <td colspan="5" class="px-6 py-8 text-center text-slate-400">
              No invoices found matching your criteria.
            </td>
          </tr>
        </tbody>
      </table>
      
      <!-- Pagination -->
      <div v-if="invoiceStore.pagination.last_page > 1" class="px-6 py-3 bg-slate-800 flex items-center justify-between border-t border-slate-700">
        <div class="text-sm text-slate-400">
          Showing page {{ invoiceStore.pagination.current_page }} of {{ invoiceStore.pagination.last_page }} ({{ invoiceStore.pagination.total }} total)
        </div>
        <div class="flex space-x-2">
          <button 
            @click="goToPage(invoiceStore.pagination.current_page - 1)"
            :disabled="invoiceStore.pagination.current_page === 1"
            class="px-3 py-1 bg-slate-700 text-white rounded hover:bg-slate-600 disabled:opacity-50"
          >
            Prev
          </button>
          <button 
            @click="goToPage(invoiceStore.pagination.current_page + 1)"
            :disabled="invoiceStore.pagination.current_page === invoiceStore.pagination.last_page"
            class="px-3 py-1 bg-slate-700 text-white rounded hover:bg-slate-600 disabled:opacity-50"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Payment Modal Overlay -->
    <div v-if="invoiceStore.paymentModalOpen" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 print:hidden">
      <div class="bg-slate-800 p-6 rounded-lg border border-slate-600 w-full max-w-md shadow-2xl">
        <h2 class="text-xl font-bold text-white mb-4">Record Payment</h2>
        
        <div class="mb-4 bg-slate-700 p-3 rounded">
          <div class="flex justify-between text-sm text-slate-300">
            <span>Invoice:</span>
            <span class="text-white font-medium">{{ invoiceStore.selectedInvoice?.invoice_number }}</span>
          </div>
          <div class="flex justify-between text-sm text-slate-300 mt-1">
            <span>Due Amount:</span>
            <span class="text-red-400 font-bold">${{ invoiceStore.selectedInvoice?.due_amount || (invoiceStore.selectedInvoice?.total_amount - (invoiceStore.selectedInvoice?.paid_amount || 0)).toFixed(2) }}</span>
          </div>
        </div>

        <form @submit.prevent="handlePaymentSubmit">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-slate-300 mb-1">Payment Amount</label>
              <input 
                type="number" 
                v-model.number="invoiceStore.paymentAmount" 
                step="0.01"
                min="0.01"
                :max="invoiceStore.selectedInvoice?.due_amount"
                required
                class="w-full bg-slate-700 border border-slate-600 rounded px-3 py-2 text-white focus:ring-2 focus:ring-indigo-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-slate-300 mb-1">Payment Method</label>
              <select 
                v-model="invoiceStore.paymentMethod"
                class="w-full bg-slate-700 border border-slate-600 rounded px-3 py-2 text-white focus:ring-2 focus:ring-indigo-500"
              >
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="bank_transfer">Bank Transfer</option>
              </select>
            </div>
          </div>
          
          <div class="mt-6 flex justify-end space-x-3">
            <button 
              type="button" 
              @click="invoiceStore.closePaymentModal()"
              class="px-4 py-2 bg-slate-600 text-white rounded hover:bg-slate-500 transition"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="invoiceStore.payingIds.includes(invoiceStore.selectedInvoice?.id) || invoiceStore.paymentAmount <= 0"
              class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-500 transition disabled:opacity-50"
            >
              {{ invoiceStore.payingIds.includes(invoiceStore.selectedInvoice?.id) ? 'Processing...' : 'Record Payment' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Hidden PDF/Print Preview Template -->
    <div class="hidden print:block w-[80mm] p-4 text-black bg-white absolute top-0 left-0 z-50" id="invoice-print">
      <div v-if="printData">
        <h2 class="text-center font-bold text-lg">Mamun Automobiles</h2>
        <p class="text-center text-xs">Tax Invoice</p>
        <p class="text-center text-xs font-bold my-1">{{ printData.invoice_number }}</p>
        <p class="text-center text-xs">{{ new Date().toLocaleString() }}</p>
        <hr class="my-2 border-black border-dashed" />
        <p class="text-xs">Customer: {{ printData.customer?.name || 'Walk-in' }}</p>
        <hr class="my-2 border-black border-dashed" />
        
        <div class="flex justify-between font-bold text-sm">
          <span>Total:</span>
          <span>${{ printData.total_amount }}</span>
        </div>
        <div class="flex justify-between text-xs mt-1">
          <span>Paid:</span>
          <span>${{ printData.paid_amount || '0.00' }}</span>
        </div>
        <div class="flex justify-between text-sm font-bold text-red-600 mt-1 border-t border-black pt-1">
          <span>Due:</span>
          <span>${{ printData.due_amount || (printData.total_amount - (printData.paid_amount || 0)).toFixed(2) }}</span>
        </div>
        <p class="text-center text-xs mt-6">Thank you for your business!</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useInvoiceStore } from '../../stores/invoices';
import { useInvoiceSocket } from '../../composables/useInvoiceSocket';

const invoiceStore = useInvoiceStore();

const searchInput = ref(invoiceStore.filters.search);
const statusInput = ref(invoiceStore.filters.status);
const printData = ref(null);

const applySearch = () => {
  invoiceStore.setFilter('search', searchInput.value);
};

const applyStatus = () => {
  invoiceStore.setFilter('status', statusInput.value);
};

const toggleSort = (field) => {
  const newDirection = invoiceStore.filters.sort === field && invoiceStore.filters.direction === 'asc' ? 'desc' : 'asc';
  invoiceStore.setFilter('sort', field);
  invoiceStore.setFilter('direction', newDirection);
};

const goToPage = (page) => {
  invoiceStore.fetchInvoices(page);
};

const handlePaymentSubmit = async () => {
  try {
    await invoiceStore.submitPayment();
  } catch (err) {
    // Error handled in store
  }
};

const printInvoice = (invoice) => {
  // Pull latest verified data before printing
  printData.value = { ...invoice };
  setTimeout(() => {
    window.print();
  }, 100);
};

onMounted(() => {
  invoiceStore.fetchInvoices();
});

// Realtime safety patching for external payments
useInvoiceSocket((data) => {
  invoiceStore.patchInvoicePayment(data);
});
</script>

<style>
@media print {
  body * {
    visibility: hidden;
  }
  #invoice-print, #invoice-print * {
    visibility: visible;
  }
  #invoice-print {
    position: absolute;
    left: 0;
    top: 0;
    width: 80mm !important;
    background: white !important;
    color: black !important;
    padding: 0 !important;
    margin: 0 !important;
  }
}
</style>
