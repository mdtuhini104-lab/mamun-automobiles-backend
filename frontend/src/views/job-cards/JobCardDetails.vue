<template>
  <div class="max-w-7xl mx-auto space-y-6">
    <div v-if="loading" class="animate-pulse space-y-6">
      <div class="h-8 bg-slate-200 rounded w-1/4"></div>
      <div class="bg-white shadow-sm rounded-xl border border-slate-200 p-6">
        <div class="h-32 bg-slate-200 rounded"></div>
      </div>
    </div>
    
    <div v-else-if="jobCard" class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
        <div class="flex items-center space-x-4">
          <router-link :to="{ name: 'job-cards.index' }" class="text-slate-400 hover:text-slate-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
          </router-link>
          <div>
            <div class="flex items-center space-x-3">
              <h1 class="text-2xl font-bold text-slate-900">JOB-{{ String(jobCard.id).padStart(5, '0') }}</h1>
              <span :class="getStatusBadgeClass(jobCard.service_status)" class="px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                {{ jobCard.service_status?.replace('_', ' ') || 'Pending' }}
              </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Service Job registered on {{ formatDate(jobCard.created_at) }}</p>
          </div>
        </div>
        <div class="flex space-x-3">
          <button
            v-if="jobCard.service_status === 'completed' || jobCard.service_status === 'delivered'"
            @click="generateInvoice"
            :disabled="generatingInvoice"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 disabled:opacity-50"
          >
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Generate Invoice
          </button>
          <router-link
            :to="{ name: 'job-cards.edit', params: { id: jobCard.id } }"
            class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50"
          >
            Edit Job
          </router-link>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Job Info -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
              <h3 class="text-lg leading-6 font-medium text-slate-900">Service Details</h3>
            </div>
            <div class="px-6 py-5 space-y-6">
              <!-- Complaint -->
              <div>
                <h4 class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-2">Customer Complaint / Issue</h4>
                <div class="bg-red-50 text-red-800 p-4 rounded-lg text-sm">
                  {{ jobCard.complaint }}
                </div>
              </div>
              
              <!-- Diagnosis -->
              <div v-if="jobCard.diagnosis">
                <h4 class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-2">Diagnosis / Findings</h4>
                <div class="bg-blue-50 text-blue-800 p-4 rounded-lg text-sm whitespace-pre-line">
                  {{ jobCard.diagnosis }}
                </div>
              </div>

              <!-- General Info Grid -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
                <div>
                  <dt class="text-sm font-medium text-slate-500">Service Date</dt>
                  <dd class="mt-1 text-sm text-slate-900">{{ formatDate(jobCard.service_date) || 'Not set' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-slate-500">Expected Delivery</dt>
                  <dd class="mt-1 text-sm text-slate-900">{{ formatDate(jobCard.delivery_date) || 'Not set' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-slate-500">Mechanic Assigned</dt>
                  <dd class="mt-1 text-sm text-slate-900 font-medium">{{ jobCard.mechanic?.name || 'Unassigned' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-slate-500">Estimated Cost</dt>
                  <dd class="mt-1 text-sm text-slate-900">{{ formatCurrency(jobCard.estimated_cost) }}</dd>
                </div>
              </div>

              <!-- Notes -->
              <div v-if="jobCard.notes" class="pt-4 border-t border-slate-100">
                <h4 class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-2">Internal Notes</h4>
                <p class="text-sm text-slate-700 italic">{{ jobCard.notes }}</p>
              </div>
            </div>
          </div>

          <!-- Required Parts / Items -->
          <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
              <h3 class="text-lg leading-6 font-medium text-slate-900">Required Parts & Services</h3>
              <!-- In a full implementation, you'd have an Add Item button here -->
            </div>
            <div class="px-6 py-5">
              <div v-if="!jobCard.items || jobCard.items.length === 0" class="text-center py-6">
                <p class="text-sm text-slate-500">No parts or service items added yet.</p>
              </div>
              <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                  <thead class="bg-slate-50">
                    <tr>
                      <th scope="col" class="px-4 py-2 text-left text-xs font-semibold text-slate-500">Item</th>
                      <th scope="col" class="px-4 py-2 text-right text-xs font-semibold text-slate-500">Qty</th>
                      <th scope="col" class="px-4 py-2 text-right text-xs font-semibold text-slate-500">Price</th>
                      <th scope="col" class="px-4 py-2 text-right text-xs font-semibold text-slate-500">Total</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-200">
                    <tr v-for="item in jobCard.items" :key="item.id">
                      <td class="px-4 py-3 text-sm text-slate-900">{{ item.part?.name || 'Service Task' }}</td>
                      <td class="px-4 py-3 text-sm text-slate-900 text-right">{{ item.quantity }}</td>
                      <td class="px-4 py-3 text-sm text-slate-900 text-right">{{ formatCurrency(item.unit_price) }}</td>
                      <td class="px-4 py-3 text-sm font-medium text-slate-900 text-right">{{ formatCurrency(item.quantity * item.unit_price) }}</td>
                    </tr>
                  </tbody>
                  <tfoot class="bg-slate-50">
                    <tr>
                      <td colspan="3" class="px-4 py-3 text-sm font-semibold text-slate-700 text-right">Parts Total:</td>
                      <td class="px-4 py-3 text-sm font-bold text-slate-900 text-right">{{ formatCurrency(calculatePartsTotal()) }}</td>
                    </tr>
                    <tr>
                      <td colspan="3" class="px-4 py-3 text-sm font-semibold text-slate-700 text-right">Service Cost:</td>
                      <td class="px-4 py-3 text-sm font-bold text-slate-900 text-right">{{ formatCurrency(jobCard.final_cost || 0) }}</td>
                    </tr>
                    <tr>
                      <td colspan="3" class="px-4 py-3 text-base font-bold text-slate-900 text-right">Grand Total:</td>
                      <td class="px-4 py-3 text-base font-bold text-indigo-600 text-right">{{ formatCurrency(calculatePartsTotal() + Number(jobCard.final_cost || 0)) }}</td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar Info -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Vehicle Details -->
          <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
              <h3 class="text-lg leading-6 font-medium text-slate-900">Vehicle</h3>
            </div>
            <div class="px-6 py-5" v-if="jobCard.vehicle">
              <div class="font-bold text-lg text-slate-900">{{ jobCard.vehicle.make }} {{ jobCard.vehicle.model }}</div>
              <div class="text-slate-500 mb-4">{{ jobCard.vehicle.year || 'Year N/A' }}</div>
              <dl class="space-y-3">
                <div class="flex justify-between">
                  <dt class="text-sm font-medium text-slate-500">Plate No.</dt>
                  <dd class="text-sm font-bold text-slate-900 bg-slate-100 px-2 rounded">{{ jobCard.vehicle.license_plate }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm font-medium text-slate-500">VIN</dt>
                  <dd class="text-sm text-slate-900">{{ jobCard.vehicle.vin || 'N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm font-medium text-slate-500">Mileage</dt>
                  <dd class="text-sm text-slate-900">12,500 km (Demo)</dd>
                </div>
              </dl>
              <div class="mt-4 pt-4 border-t border-slate-100">
                <router-link :to="{ name: 'vehicles.show', params: { id: jobCard.vehicle.id } }" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">View Full History</router-link>
              </div>
            </div>
          </div>

          <!-- Customer Details -->
          <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
              <h3 class="text-lg leading-6 font-medium text-slate-900">Customer</h3>
            </div>
            <div class="px-6 py-5" v-if="jobCard.customer">
              <div class="font-bold text-lg text-slate-900">{{ jobCard.customer.name }}</div>
              <div class="text-sm text-slate-500 mb-4">Customer since {{ formatDate(jobCard.customer.created_at) }}</div>
              <dl class="space-y-3">
                <div>
                  <dt class="text-xs font-medium text-slate-500 uppercase">Phone</dt>
                  <dd class="text-sm text-slate-900 mt-1">{{ jobCard.customer.phone || 'N/A' }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-slate-500 uppercase">Email</dt>
                  <dd class="text-sm text-slate-900 mt-1">{{ jobCard.customer.email || 'N/A' }}</dd>
                </div>
              </dl>
            </div>
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

const jobCard = ref(null);
const loading = ref(true);
const generatingInvoice = ref(false);

const fetchJobCard = async () => {
  try {
    const response = await api.get(`/job-cards/${route.params.id}`);
    jobCard.value = response.data.data;
  } catch (error) {
    toast.error('Failed to load job card details');
    router.push({ name: 'job-cards.index' });
  } finally {
    loading.value = false;
  }
};

const generateInvoice = async () => {
  if (!confirm('Are you sure you want to finalize this job card and generate an invoice? This will reduce parts stock.')) return;
  generatingInvoice.value = true;
  try {
    // We assume there's an endpoint to generate invoice. If not, we will need to add it in api.php
    const response = await api.post(`/invoices/generate/${jobCard.value.id}`);
    toast.success('Invoice generated successfully!');
    // Redirect to invoice details
    router.push({ name: 'invoices.show', params: { id: response.data.data.id } });
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to generate invoice');
  } finally {
    generatingInvoice.value = false;
  }
};

const calculatePartsTotal = () => {
  if (!jobCard.value || !jobCard.value.items) return 0;
  return jobCard.value.items.reduce((total, item) => total + (item.quantity * item.unit_price), 0);
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-BD', { style: 'currency', currency: 'BDT' }).format(value || 0);
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

const getStatusBadgeClass = (status) => {
  const map = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'in_progress': 'bg-blue-100 text-blue-800',
    'completed': 'bg-green-100 text-green-800',
    'delivered': 'bg-indigo-100 text-indigo-800',
    'cancelled': 'bg-red-100 text-red-800'
  };
  return map[status] || 'bg-slate-100 text-slate-800';
};

onMounted(() => {
  fetchJobCard();
});
</script>
