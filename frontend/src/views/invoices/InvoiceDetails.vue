<template>
  <div class="max-w-4xl mx-auto space-y-6 pb-20">
    <div v-if="loading" class="animate-pulse space-y-6">
      <div class="h-8 bg-slate-200 rounded w-1/4"></div>
      <div class="bg-white shadow-sm rounded-xl border border-slate-200 p-6 h-96"></div>
    </div>
    
    <div v-else-if="invoice" class="space-y-6">
      <!-- Actions Header (Hidden in Print) -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0 print:hidden">
        <div class="flex items-center space-x-4">
          <router-link :to="{ name: 'invoices.index' }" class="text-slate-400 hover:text-slate-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
          </router-link>
          <div>
            <h1 class="text-2xl font-bold text-slate-900">Invoice {{ invoice.invoice_number }}</h1>
            <p class="text-sm text-slate-500 mt-1">Generated on {{ formatDate(invoice.created_at) }}</p>
          </div>
        </div>
        <div class="flex space-x-3">
          <button
            v-if="invoice.payment_status !== 'paid'"
            @click="showPaymentModal = true"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
          >
            Record Payment
          </button>
          <button
            @click="printInvoice"
            class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50"
          >
            <svg class="-ml-1 mr-2 h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Print / PDF
          </button>
        </div>
      </div>

      <!-- Invoice Printable Container -->
      <div id="printable-invoice" class="bg-white shadow-lg rounded-none sm:rounded-xl border border-slate-200 overflow-hidden print:shadow-none print:border-none print:m-0 print:p-0">
        <div class="p-8 sm:p-12 space-y-8">
          
          <!-- Header -->
          <div class="flex justify-between items-start border-b border-slate-200 pb-8">
            <div>
              <h2 class="text-3xl font-bold text-slate-900 tracking-tight">INVOICE</h2>
              <div class="mt-2 text-sm text-slate-500">#{{ invoice.invoice_number }}</div>
              <div class="mt-1 flex items-center space-x-2">
                <span class="text-sm font-medium text-slate-500">Status:</span>
                <span :class="getPaymentStatusClass(invoice.payment_status)" class="px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider rounded-md">
                  {{ invoice.payment_status }}
                </span>
              </div>
            </div>
            <div class="text-right">
              <h1 class="text-2xl font-black text-indigo-700 tracking-tighter">MAMUN AUTO</h1>
              <div class="mt-2 text-sm text-slate-600">
                <p>123 Service Center Road</p>
                <p>Dhaka, 1205, Bangladesh</p>
                <p>Phone: +880 1234-567890</p>
                <p>Email: service@mamunauto.com</p>
              </div>
            </div>
          </div>

          <!-- Billing Details -->
          <div class="grid grid-cols-2 gap-12">
            <div>
              <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Billed To</h3>
              <div v-if="invoice.customer" class="text-sm text-slate-900 space-y-1">
                <p class="font-bold text-base">{{ invoice.customer.name }}</p>
                <p>{{ invoice.customer.address || 'Address not provided' }}</p>
                <p>{{ invoice.customer.phone }}</p>
                <p>{{ invoice.customer.email }}</p>
              </div>
            </div>
            <div>
              <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Vehicle Info</h3>
              <div v-if="invoice.job_card && invoice.job_card.vehicle" class="text-sm text-slate-900 space-y-1">
                <p class="font-bold text-base">{{ invoice.job_card.vehicle.make }} {{ invoice.job_card.vehicle.model }}</p>
                <p><span class="text-slate-500">Plate No:</span> {{ invoice.job_card.vehicle.license_plate }}</p>
                <p><span class="text-slate-500">VIN:</span> {{ invoice.job_card.vehicle.vin || 'N/A' }}</p>
                <p><span class="text-slate-500">Job Card:</span> JOB-{{ String(invoice.job_card.id).padStart(5, '0') }}</p>
              </div>
            </div>
          </div>

          <!-- Items Table -->
          <div class="mt-8">
            <table class="w-full text-left text-sm">
              <thead>
                <tr class="border-b-2 border-slate-300 text-slate-700">
                  <th scope="col" class="pb-3 font-bold uppercase">Description</th>
                  <th scope="col" class="pb-3 font-bold uppercase text-right">Qty</th>
                  <th scope="col" class="pb-3 font-bold uppercase text-right">Unit Price</th>
                  <th scope="col" class="pb-3 font-bold uppercase text-right">Total</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200">
                <tr v-for="item in invoice.items" :key="item.id">
                  <td class="py-4 text-slate-900">{{ item.description }}</td>
                  <td class="py-4 text-slate-900 text-right">{{ item.quantity }}</td>
                  <td class="py-4 text-slate-900 text-right">{{ formatCurrency(item.unit_price) }}</td>
                  <td class="py-4 text-slate-900 font-medium text-right">{{ formatCurrency(item.total_price) }}</td>
                </tr>
                <tr v-if="invoice.service_total > 0">
                  <td class="py-4 text-slate-900">Labor / Service Charges</td>
                  <td class="py-4 text-slate-900 text-right">1</td>
                  <td class="py-4 text-slate-900 text-right">{{ formatCurrency(invoice.service_total) }}</td>
                  <td class="py-4 text-slate-900 font-medium text-right">{{ formatCurrency(invoice.service_total) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Totals -->
          <div class="border-t-2 border-slate-300 pt-8 flex justify-end">
            <div class="w-full max-w-sm space-y-3 text-sm">
              <div class="flex justify-between text-slate-500">
                <span>Subtotal (Parts & Service)</span>
                <span class="text-slate-900">{{ formatCurrency(invoice.parts_total + invoice.service_total) }}</span>
              </div>
              <div class="flex justify-between text-slate-500" v-if="invoice.discount > 0">
                <span>Discount</span>
                <span class="text-red-600">-{{ formatCurrency(invoice.discount) }}</span>
              </div>
              <div class="flex justify-between text-slate-500" v-if="invoice.vat > 0">
                <span>VAT (15%)</span>
                <span class="text-slate-900">{{ formatCurrency(invoice.vat) }}</span>
              </div>
              <div class="flex justify-between items-center border-t border-slate-200 pt-3">
                <span class="font-bold text-slate-900 text-lg">Grand Total</span>
                <span class="font-black text-indigo-700 text-xl">{{ formatCurrency(invoice.grand_total) }}</span>
              </div>
              <div class="flex justify-between text-slate-500 pt-2">
                <span>Amount Paid</span>
                <span class="text-green-600 font-medium">{{ formatCurrency(invoice.paid_amount) }}</span>
              </div>
              <div class="flex justify-between items-center border-t border-slate-200 pt-3 bg-slate-50 p-3 rounded-lg">
                <span class="font-bold text-slate-900">Balance Due</span>
                <span class="font-bold text-red-600 text-lg">{{ formatCurrency(invoice.due_amount) }}</span>
              </div>
            </div>
          </div>

          <!-- Footer Notes -->
          <div class="border-t border-slate-200 pt-8 mt-16 text-xs text-slate-500 text-center">
            <p>Thank you for your business!</p>
            <p class="mt-1">Terms: Payment is due upon receipt. Goods remain property of Mamun Auto until paid in full.</p>
          </div>
          
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';

const route = useRoute();
const router = useRouter();
const toast = useToastStore();

const invoice = ref(null);
const loading = ref(true);

const fetchInvoice = async () => {
  try {
    const response = await api.get(`/invoices/${route.params.id}`);
    invoice.value = response.data.data;
  } catch (error) {
    toast.error('Failed to load invoice details');
    router.push({ name: 'invoices.index' });
  } finally {
    loading.value = false;
  }
};

const printInvoice = () => {
  window.print();
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-BD', { style: 'currency', currency: 'BDT' }).format(value || 0);
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute:'2-digit' });
};

const getPaymentStatusClass = (status) => {
  const map = {
    'paid': 'bg-green-100 text-green-800',
    'partial': 'bg-yellow-100 text-yellow-800',
    'unpaid': 'bg-red-100 text-red-800'
  };
  return map[status] || 'bg-slate-100 text-slate-800';
};

onMounted(() => {
  fetchInvoice();
});
</script>

<style>
@media print {
  body {
    background-color: white !important;
  }
  #app {
    padding: 0 !important;
    margin: 0 !important;
  }
  /* Hide sidebar and navbar using their likely classes if needed, 
     but dashboard layout usually requires scoping. */
  .sidebar-container, .top-header {
    display: none !important;
  }
  .print\:hidden {
    display: none !important;
  }
}
</style>
