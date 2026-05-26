<template>
  <div class="space-y-4 relative">
    <div class="flex justify-between items-end print:hidden">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Invoices</h1>
        <p class="text-sm text-slate-500 mt-1">Manage billing, track payments, and generate receipts.</p>
      </div>
      <button v-if="authStore.hasPermission('invoice.create')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-colors flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        New Invoice
      </button>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-3 rounded-xl shadow-sm border border-slate-200 flex flex-col sm:flex-row gap-3 items-center print:hidden">
      <div class="relative flex-1 w-full">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
        </div>
        <input 
          v-model="searchInput"
          @keyup.enter="applySearch"
          type="text" 
          placeholder="Search by Invoice No or Customer..." 
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
          <option value="paid">Paid</option>
          <option value="partial">Partial</option>
          <option value="pending">Pending</option>
        </select>
      </div>
    </div>
    
    <!-- Invoices Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden relative print:hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
              <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider cursor-pointer hover:text-indigo-600 group" @click="toggleSort('invoice_number')">
                <div class="flex items-center gap-1">Invoice No <span class="text-slate-400 group-hover:text-indigo-500">↕</span></div>
              </th>
              <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider cursor-pointer hover:text-indigo-600 group" @click="toggleSort('customer_id')">
                <div class="flex items-center gap-1">Customer <span class="text-slate-400 group-hover:text-indigo-500">↕</span></div>
              </th>
              <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider cursor-pointer hover:text-indigo-600 group" @click="toggleSort('total_amount')">
                <div class="flex items-center gap-1">Amount <span class="text-slate-400 group-hover:text-indigo-500">↕</span></div>
              </th>
              <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
              <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            
            <!-- Loading Skeleton -->
            <template v-if="invoiceStore.loading">
              <tr v-for="i in 5" :key="'skeleton-'+i" class="animate-pulse">
                <td class="px-4 py-3"><div class="h-4 bg-slate-200 rounded w-20"></div></td>
                <td class="px-4 py-3"><div class="h-4 bg-slate-200 rounded w-32"></div></td>
                <td class="px-4 py-3"><div class="h-4 bg-slate-200 rounded w-24 mb-1"></div><div class="h-3 bg-slate-100 rounded w-16"></div></td>
                <td class="px-4 py-3"><div class="h-6 bg-slate-200 rounded-full w-16"></div></td>
                <td class="px-4 py-3 text-right"><div class="flex justify-end gap-2"><div class="h-6 bg-slate-200 rounded w-12"></div><div class="h-6 bg-slate-200 rounded w-10"></div></div></td>
              </tr>
            </template>

            <template v-else>
              <tr v-for="invoice in invoiceStore.invoices" :key="invoice.id" class="hover:bg-slate-50/80 transition-colors group">
                <td class="px-4 py-3 whitespace-nowrap">
                  <div class="text-sm font-bold text-slate-900">{{ invoice.invoice_number }}</div>
                  <div class="text-[11px] text-slate-400 mt-0.5">{{ new Date(invoice.created_at || Date.now()).toLocaleDateString() }}</div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <div class="text-sm font-medium text-slate-700">{{ invoice.customer?.name || 'Walk-in Customer' }}</div>
                  <div class="text-xs text-slate-500" v-if="invoice.customer?.phone">{{ invoice.customer.phone }}</div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <div class="text-sm font-bold text-slate-900 tabular-nums">${{ Number(invoice.total_amount).toFixed(2) }}</div>
                  <div class="text-xs font-semibold tabular-nums mt-0.5" :class="invoice.due_amount > 0 ? 'text-red-600' : 'text-emerald-600'">
                    {{ invoice.due_amount > 0 ? 'Due: $' + Number(invoice.due_amount).toFixed(2) : 'Fully Paid' }}
                  </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border" :class="{
                    'bg-emerald-50 border-emerald-200 text-emerald-700': invoice.payment_status === 'paid',
                    'bg-amber-50 border-amber-200 text-amber-700': invoice.payment_status === 'partial' || invoice.payment_status === 'pending',
                    'bg-red-50 border-red-200 text-red-700': invoice.payment_status === 'overdue' || invoice.payment_status === 'cancelled'
                  }">
                    <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="{
                      'bg-emerald-500': invoice.payment_status === 'paid',
                      'bg-amber-500': invoice.payment_status === 'partial' || invoice.payment_status === 'pending',
                      'bg-red-500': invoice.payment_status === 'overdue' || invoice.payment_status === 'cancelled'
                    }"></span>
                    {{ invoice.payment_status ? invoice.payment_status.charAt(0).toUpperCase() + invoice.payment_status.slice(1) : 'Unknown' }}
                  </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-right space-x-2">
                  <button 
                    @click="printInvoice(invoice.id, 'pdf')"
                    class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors bg-white hover:bg-indigo-50 border border-slate-200 px-2.5 py-1.5 rounded shadow-sm"
                    title="Print A4"
                  >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    A4
                  </button>
                  <button 
                    @click="printInvoice(invoice.id, 'thermal')"
                    class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-900 transition-colors bg-white hover:bg-slate-100 border border-slate-200 px-2.5 py-1.5 rounded shadow-sm"
                    title="Print Thermal"
                  >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Thermal
                  </button>
                  <button 
                    @click="printInvoice(invoice.id, 'download')"
                    class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-emerald-600 transition-colors bg-white hover:bg-emerald-50 border border-slate-200 px-2.5 py-1.5 rounded shadow-sm"
                    title="Download PDF"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                  </button>
                  <button 
                    v-if="invoice.payment_status !== 'paid' && invoice.payment_status !== 'cancelled' && authStore.hasPermission('manage_invoices')"
                    @click="invoiceStore.openPaymentModal(invoice)"
                    class="inline-flex items-center text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-500 transition-colors border border-transparent px-2.5 py-1.5 rounded shadow-sm"
                  >
                    Pay
                  </button>
                </td>
              </tr>
              
              <!-- Empty State -->
              <tr v-if="invoiceStore.invoices.length === 0">
                <td colspan="5" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                    </div>
                    <h3 class="text-base font-semibold text-slate-900 mb-1">No invoices found</h3>
                    <p class="text-sm text-slate-500 max-w-sm">We couldn't find any invoices matching your search filters.</p>
                    <button @click="searchInput = ''; statusInput = ''; applySearch(); applyStatus()" class="mt-4 text-indigo-600 hover:text-indigo-700 text-sm font-medium">Clear filters</button>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div v-if="invoiceStore.pagination.last_page > 1" class="px-4 py-3 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-0">
        <div class="text-sm text-slate-500">
          Showing <span class="font-medium text-slate-900">{{ invoiceStore.pagination.current_page }}</span> of <span class="font-medium text-slate-900">{{ invoiceStore.pagination.last_page }}</span> pages
          <span class="text-slate-400 mx-1">&bull;</span>
          <span class="font-medium text-slate-900">{{ invoiceStore.pagination.total }}</span> total items
        </div>
        <div class="flex space-x-2">
          <button 
            @click="goToPage(invoiceStore.pagination.current_page - 1)"
            :disabled="invoiceStore.pagination.current_page === 1"
            class="px-3 py-1.5 bg-white text-slate-700 border border-slate-300 rounded-md hover:bg-slate-50 hover:text-slate-900 disabled:opacity-50 disabled:hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 text-sm font-medium transition-colors shadow-sm"
          >
            Previous
          </button>
          <button 
            @click="goToPage(invoiceStore.pagination.current_page + 1)"
            :disabled="invoiceStore.pagination.current_page === invoiceStore.pagination.last_page"
            class="px-3 py-1.5 bg-white text-slate-700 border border-slate-300 rounded-md hover:bg-slate-50 hover:text-slate-900 disabled:opacity-50 disabled:hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 text-sm font-medium transition-colors shadow-sm"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Payment Modal Overlay -->
    <div v-if="invoiceStore.paymentModalOpen" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center z-50 print:hidden" @click.self="invoiceStore.closePaymentModal()">
      <div class="bg-white p-6 rounded-2xl border border-slate-200 w-full max-w-md shadow-2xl mx-4 flex flex-col max-h-[90vh] overflow-y-auto animate-in fade-in zoom-in duration-200">
        <h2 class="text-xl font-bold text-slate-900 mb-4">Record Payment</h2>
        
        <div class="mb-5 bg-slate-50 p-4 rounded-xl border border-slate-200">
          <div class="flex justify-between text-sm text-slate-600 mb-2">
            <span>Invoice:</span>
            <span class="text-slate-900 font-bold">{{ invoiceStore.selectedInvoice?.invoice_number }}</span>
          </div>
          <div class="flex justify-between text-sm text-slate-600 pt-2 border-t border-slate-200">
            <span>Due Amount:</span>
            <span class="text-red-600 font-bold text-base">${{ invoiceStore.selectedInvoice?.due_amount || (invoiceStore.selectedInvoice?.total_amount - (invoiceStore.selectedInvoice?.paid_amount || 0)).toFixed(2) }}</span>
          </div>
        </div>

        <form @submit.prevent="handlePaymentSubmit">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">Payment Amount</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-slate-500 font-medium">$</span>
                </div>
                <input 
                  type="number" 
                  v-model.number="invoiceStore.paymentAmount" 
                  step="0.01"
                  min="0.01"
                  :max="invoiceStore.selectedInvoice?.due_amount"
                  required
                  class="block w-full pl-7 rounded-lg border-0 bg-slate-50 py-2.5 text-slate-900 shadow-inner ring-1 ring-inset ring-slate-200 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 font-medium"
                />
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">Payment Method</label>
              <select 
                v-model="invoiceStore.paymentMethod"
                class="block w-full rounded-lg border-0 bg-slate-50 py-2.5 pl-3 pr-10 text-slate-900 shadow-inner ring-1 ring-inset ring-slate-200 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 font-medium"
              >
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="bank_transfer">Bank Transfer</option>
              </select>
            </div>
          </div>
          
          <div class="mt-6 flex justify-end gap-3">
            <button 
              type="button" 
              @click="invoiceStore.closePaymentModal()"
              class="px-4 py-2.5 bg-white text-slate-700 border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors text-sm font-semibold shadow-sm w-full sm:w-auto"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="invoiceStore.payingIds.includes(invoiceStore.selectedInvoice?.id) || invoiceStore.paymentAmount <= 0"
              class="px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-500 transition-colors disabled:opacity-50 disabled:hover:bg-indigo-600 text-sm font-semibold shadow-sm w-full sm:w-auto flex justify-center items-center gap-2"
            >
              <svg v-if="invoiceStore.payingIds.includes(invoiceStore.selectedInvoice?.id)" class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              {{ invoiceStore.payingIds.includes(invoiceStore.selectedInvoice?.id) ? 'Processing...' : 'Confirm Payment' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Hidden PDF/Print Preview Template -->
    <div class="hidden print:block w-[80mm] p-4 text-black bg-white absolute top-0 left-0 z-50" id="invoice-print">
      <div v-if="printData">
        <img src="/logo-dark.png" alt="Mamun Automobiles" class="h-10 mx-auto mb-2" />
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
import { useAuthStore } from '../../stores/auth';

const invoiceStore = useInvoiceStore();
const authStore = useAuthStore();

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

import axios from 'axios';

const printInvoice = async (id, type = 'pdf') => {
  const token = localStorage.getItem('token');
  const isProd = typeof window !== 'undefined' && window.location.hostname.includes('vercel.app');
  const baseUrl = isProd ? 'https://mamunerp.com' : (import.meta.env.VITE_API_URL || 'http://localhost:8000');
  
  try {
    if (type === 'thermal') {
      const response = await axios.get(`${baseUrl}/api/v1/print/invoice/${id}/thermal`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      const printWindow = window.open('', '_blank', 'width=400,height=600');
      printWindow.document.write(response.data);
      printWindow.document.close();
      printWindow.focus();
      setTimeout(() => printWindow.print(), 500);
    } else {
      const response = await axios.get(`${baseUrl}/api/v1/print/invoice/${id}${type==='download'?'?action=download':''}`, {
        headers: { Authorization: `Bearer ${token}` },
        responseType: 'blob'
      });
      const blob = new Blob([response.data], { type: 'application/pdf' });
      const url = window.URL.createObjectURL(blob);
      if (type === 'download') {
        const a = document.createElement('a');
        a.href = url;
        a.download = `invoice-${id}.pdf`;
        document.body.appendChild(a);
        a.click();
        a.remove();
      } else {
        window.open(url, '_blank');
      }
      setTimeout(() => window.URL.revokeObjectURL(url), 1000);
    }
  } catch (e) {
    console.error('Print failed', e);
    // Optionally show a toast error
  }
};

onMounted(() => {
  invoiceStore.fetchInvoices();
});

// Realtime safety patching for external payments
useInvoiceSocket((data) => {
  invoiceStore.patchInvoicePayment(data);
});
</script>

<style scoped>
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
